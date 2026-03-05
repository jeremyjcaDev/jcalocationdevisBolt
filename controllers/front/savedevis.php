<?php

use Jca\JcaLocationdevis\Service\QuoteEmailService;

class Jca_locationdevisSavedevisModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        header('Content-Type: application/json');

        if (!$this->ajax) {
            die(json_encode(['success' => false, 'message' => 'Cette action nécessite une requête AJAX']));
        }

        $customer = $this->context->customer;
        if (!$customer->isLogged()) {
            die(json_encode(['success' => false, 'message' => 'Vous devez être connecté']));
        }

        $data = json_decode(Tools::file_get_contents('php://input'), true);

        if (!$data) {
            die(json_encode(['success' => false, 'message' => 'Données invalides']));
        }

        try {
            $db = Db::getInstance();

            // Récupérer les settings
            $settings = $this->getQuoteSettings();

            // Générer le numéro de devis
            $prefix = !empty($settings['quote_number_prefix']) ? $settings['quote_number_prefix'] : 'Q';
            $sql = 'SELECT MAX(CAST(SUBSTRING(quote_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as max_num
                    FROM ' . _DB_PREFIX_ . 'jca_quotes
                    WHERE quote_number LIKE "' . pSQL($prefix) . '%"';
            $result = $db->getRow($sql);
            $nextNumber = ($result && $result['max_num']) ? (int)$result['max_num'] + 1 : 1;
            $quoteNumber = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Calculer la date d'expiration
            $validityHours = !empty($settings['validity_hours']) ? (int)$settings['validity_hours'] : 720;
            $expiryDate = date('Y-m-d H:i:s', strtotime("+{$validityHours} hours"));

            // Démarrer la transaction
            $db->execute('START TRANSACTION');

            // Type de devis (location ou vente standard)
            $isRental = isset($data['isRental']) && $data['isRental'];
            $durationMonths = isset($data['durationMonths']) ? (int)$data['durationMonths'] : null;
            $products = isset($data['products']) ? $data['products'] : [];

            // Calculer le total
            $totalPriceHT = 0;
            $rentalConfiguration = null;

            if ($isRental && $durationMonths) {
                // Pour les locations, calculer le total HT des produits
                foreach ($products as $p) {
                    $quantity = isset($p['quantity']) ? (int)$p['quantity'] : 1;
                    $price = isset($p['price']) ? (float)$p['price'] : 0;
                    $totalPriceHT += $price * $quantity;
                }

                // Récupérer la configuration de location correspondante
                if ($totalPriceHT > 0) {
                    $rateColumn = ($durationMonths == 36) ? 'duration_36_months' : 'duration_60_months';
                    $sql = 'SELECT id_rental_configuration, price_min, price_max, ' . $rateColumn . ' AS selected_rate
                            FROM ' . _DB_PREFIX_ . 'jca_rental_configurations
                            WHERE price_min <= ' . (float)$totalPriceHT . '
                            AND price_max >= ' . (float)$totalPriceHT . '
                            ORDER BY sort_order ASC';
                    $rentalConfiguration = $db->getRow($sql);
                }
            }

            // Créer le client quote si nécessaire
            $idQuoteCustomer = $this->createOrUpdateQuoteCustomer($customer);

            // Insérer le devis
            $insertQuote = [
                'id_quote_customer' => (int)$idQuoteCustomer,
                'quote_number' => pSQL($quoteNumber),
                'quote_date' => date('Y-m-d H:i:s'),
                'expiry_date' => pSQL($expiryDate),
                'status' => 'pending',
                'total_ht' => 0, // Sera calculé après l'ajout des items
                'total_ttc' => 0,
                'tax_amount' => 0,
                'is_rental' => $isRental ? 1 : 0,
                'duration_months' => $durationMonths,
                'id_rental_configuration' => ($rentalConfiguration ? (int)$rentalConfiguration['id_rental_configuration'] : null),
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s')
            ];

            $result = $db->insert('jca_quotes', $insertQuote);
            if (!$result) {
                throw new Exception('Erreur lors de l\'insertion du devis');
            }

            $idQuote = (int)$db->Insert_ID();

            // Items
            $totalHT = 0;
            foreach ($products as $p) {
                $idRentalConfiguration = isset($p['id_rental_configuration']) ? (int)$p['id_rental_configuration'] : null;

                if ($idRentalConfiguration) {
                    $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'jca_rental_configurations WHERE id_rental_configuration = ' . $idRentalConfiguration;
                    $config = $db->getRow($sql);
                    if ($config) {
                        $rentalConfiguration = $config;
                    }
                }

                $quantity = isset($p['quantity']) ? (int)$p['quantity'] : 1;
                $unitPrice = isset($p['price']) ? (float)$p['price'] : 0;
                $itemType = isset($p['item_type']) ? pSQL($p['item_type']) : 'product';

                // Pour les locations, calculer le prix mensuel
                if ($isRental && $rentalConfiguration && $itemType !== 'delivery') {
                    $totalProductHT = $unitPrice * $quantity;
                    $rate = (float)$rentalConfiguration['selected_rate'];
                    $monthlyPrice = round(($totalProductHT / $durationMonths) * (1 + ($rate / 100)), 2);
                    $unitPrice = $monthlyPrice / $quantity;
                    $totalHT += $monthlyPrice;
                } else {
                    $totalHT += $unitPrice * $quantity;
                }

                $insertItem = [
                    'id_quote' => $idQuote,
                    'id_product' => isset($p['id_product']) ? (int)$p['id_product'] : 0,
                    'id_product_attribute' => isset($p['id_product_attribute']) ? (int)$p['id_product_attribute'] : 0,
                    'product_name' => pSQL($p['name']),
                    'product_reference' => isset($p['reference']) ? pSQL($p['reference']) : '',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                    'tax_rate' => isset($p['tax_rate']) ? (float)$p['tax_rate'] : 0,
                    'is_rental' => $isRental ? 1 : 0,
                    'duration_months' => $durationMonths,
                    'id_rental_configuration' => $idRentalConfiguration,
                    'item_type' => $itemType,
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s')
                ];

                $result = $db->insert('jca_quote_items', $insertItem);
                if (!$result) {
                    throw new Exception('Erreur lors de l\'insertion des items');
                }
            }

            // Mettre à jour le total du devis
            $taxAmount = $totalHT * 0.20; // TVA 20%
            $totalTTC = $totalHT + $taxAmount;

            $updateQuote = [
                'total_ht' => $totalHT,
                'total_ttc' => $totalTTC,
                'tax_amount' => $taxAmount
            ];

            $result = $db->update('jca_quotes', $updateQuote, 'id_quote = ' . $idQuote);
            if (!$result) {
                throw new Exception('Erreur lors de la mise à jour du total');
            }

            // Valider la transaction
            $db->execute('COMMIT');

            // Envoyer l'email de notification
            try {
                $quote = $db->getRow('SELECT q.*, qc.email as customer_email, qc.firstname as customer_firstname, qc.lastname as customer_lastname
                                      FROM ' . _DB_PREFIX_ . 'jca_quotes q
                                      LEFT JOIN ' . _DB_PREFIX_ . 'jca_quote_customers qc ON q.id_quote_customer = qc.id_quote_customer
                                      WHERE q.id_quote = ' . $idQuote);

                $items = $db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'jca_quote_items WHERE id_quote = ' . $idQuote);

                $emailService = new QuoteEmailService();
                $emailResult = $emailService->sendQuoteCreatedEmail($quote, $items);

                if (!$emailResult['success']) {
                    error_log('Email notification failed: ' . ($emailResult['error'] ?? $emailResult['reason'] ?? 'Unknown error'));
                }
            } catch (Exception $e) {
                error_log('Email notification exception: ' . $e->getMessage());
            }

            die(json_encode([
                'success' => true,
                'message' => 'Devis créé avec succès',
                'quote_id' => $idQuote,
                'quote_number' => $quoteNumber
            ]));

        } catch (Exception $e) {
            if (isset($db)) {
                $db->execute('ROLLBACK');
            }

            error_log('Erreur savedevis: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            die(json_encode([
                'success' => false,
                'message' => 'Erreur lors de la création du devis: ' . $e->getMessage()
            ]));
        }
    }

    private function getQuoteSettings()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_settings`';
        $settings = Db::getInstance()->getRow($sql);

        if (!$settings) {
            throw new Exception('Aucune configuration de devis trouvée');
        }

        return $settings;
    }

    private function createOrUpdateQuoteCustomer($customer)
    {
        $db = Db::getInstance();

        // Vérifier si le client existe déjà
        $sql = 'SELECT id_quote_customer FROM ' . _DB_PREFIX_ . 'jca_quote_customers WHERE id_customer = ' . (int)$customer->id;
        $existing = $db->getRow($sql);

        if ($existing) {
            return (int)$existing['id_quote_customer'];
        }

        // Créer un nouveau client quote
        $insertCustomer = [
            'id_customer' => (int)$customer->id,
            'firstname' => pSQL($customer->firstname),
            'lastname' => pSQL($customer->lastname),
            'email' => pSQL($customer->email),
            'phone' => pSQL($customer->phone_mobile ?: ''),
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ];

        $result = $db->insert('jca_quote_customers', $insertCustomer);
        if (!$result) {
            throw new Exception('Erreur lors de la création du client');
        }

        return (int)$db->Insert_ID();
    }
}
