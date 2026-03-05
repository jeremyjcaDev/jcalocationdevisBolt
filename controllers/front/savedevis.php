<?php

use Jca\JcaLocationdevis\Service\QuoteEmailService;

class Jca_locationdevisSavedevisModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        header('Content-Type: application/json');

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

            // Générer le numéro de devis selon la configuration
            $prefix = !empty($settings['quote_number_prefix']) ? $settings['quote_number_prefix'] : 'DEVIS';
            $separator = !empty($settings['quote_number_separator']) ? $settings['quote_number_separator'] : '-';
            $yearFormat = isset($settings['quote_number_year_format']) ? $settings['quote_number_year_format'] : 'YYYY';
            $padding = isset($settings['quote_number_padding']) ? (int)$settings['quote_number_padding'] : 3;
            $counter = isset($settings['quote_number_counter']) ? (int)$settings['quote_number_counter'] : 0;
            $resetYearly = isset($settings['quote_number_reset_yearly']) ? (bool)$settings['quote_number_reset_yearly'] : true;
            $lastYear = isset($settings['quote_number_last_year']) ? (int)$settings['quote_number_last_year'] : 0;

            $currentYear = (int)date('Y');

            // Vérifier si on doit réinitialiser le compteur (changement d'année)
            if ($resetYearly && $lastYear > 0 && $currentYear != $lastYear) {
                $counter = 0;
                // Mettre à jour l'année dans les settings
                $db->update('jca_quote_settings', [
                    'quote_number_last_year' => $currentYear,
                    'quote_number_counter' => 0
                ], 'id_quote_settings = ' . (int)$settings['id_quote_settings']);
            }

            // Incrémenter le compteur
            $counter++;

            // Construire le numéro de devis
            $parts = [];

            if (!empty($prefix)) {
                $parts[] = $prefix;
            }

            if (!empty($yearFormat)) {
                if ($yearFormat === 'YY') {
                    $parts[] = substr((string)$currentYear, -2);
                } else if ($yearFormat === 'YYYY') {
                    $parts[] = (string)$currentYear;
                }
            }

            $parts[] = str_pad($counter, $padding, '0', STR_PAD_LEFT);

            $quoteNumber = implode($separator, $parts);

            // Mettre à jour le compteur dans les settings
            $db->update('jca_quote_settings', [
                'quote_number_counter' => $counter,
                'date_upd' => date('Y-m-d H:i:s')
            ], 'id_quote_settings = ' . (int)$settings['id_quote_settings']);

            // Calculer la date d'expiration
            $validityHours = !empty($settings['validity_hours']) ? (int)$settings['validity_hours'] : 720;
            $expiryDate = date('Y-m-d H:i:s', strtotime("+{$validityHours} hours"));

            // Démarrer la transaction
            $db->execute('START TRANSACTION');

            // Type de devis (location ou vente standard)
            // Support à la fois isRental (backend) et quote_type (frontend)
            $isRental = false;
            if (isset($data['isRental']) && $data['isRental']) {
                $isRental = true;
            } elseif (isset($data['quote_type']) && $data['quote_type'] === 'rental_only') {
                $isRental = true;
            }

            // Support à la fois durationMonths et duration_month
            $durationMonths = null;
            if (isset($data['durationMonths'])) {
                $durationMonths = (int)$data['durationMonths'];
            } elseif (isset($data['duration_month'])) {
                $durationMonths = (int)$data['duration_month'];
            }

            // Récupérer le taux de location envoyé depuis le frontend
            $ratePercentage = null;
            if (isset($data['rate_percentage'])) {
                $ratePercentage = (float)$data['rate_percentage'];
            }

            // Récupérer les produits depuis le panier ou depuis les données envoyées
            $products = [];
            if (isset($data['products']) && !empty($data['products'])) {
                // Format backend avec produits complets
                $products = $data['products'];
            } elseif (isset($data['id_products']) && !empty($data['id_products'])) {
                // Format frontend avec juste les IDs - récupérer depuis le panier
                $cart = $this->context->cart;
                $cartProducts = $cart->getProducts();

                error_log('=== DEBUG PRODUCTS ===');
                error_log('ID Products from request: ' . json_encode($data['id_products']));
                error_log('Cart products: ' . json_encode($cartProducts));

                foreach ($data['id_products'] as $productId) {
                    foreach ($cartProducts as $cartProduct) {
                        if ((int)$cartProduct['id_product'] == (int)$productId) {
                            $products[] = [
                                'id_product' => (int)$cartProduct['id_product'],
                                'name' => $cartProduct['name'],
                                'reference' => $cartProduct['reference'],
                                'price' => (float)$cartProduct['price'],
                                'quantity' => (int)$cartProduct['cart_quantity']
                            ];
                            break;
                        }
                    }
                }

                error_log('Final products array: ' . json_encode($products));
                error_log('=====================');
            }

            // Si aucun produit n'a été récupéré, lever une erreur
            if (empty($products)) {
                throw new Exception('Aucun produit trouvé pour créer le devis');
            }

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

            // Insérer le devis avec les infos client
            $phone = '';
            if (isset($customer->phone_mobile) && !empty($customer->phone_mobile)) {
                $phone = $customer->phone_mobile;
            } elseif (isset($customer->phone) && !empty($customer->phone)) {
                $phone = $customer->phone;
            }

            $insertQuote = [
                'quote_number' => pSQL($quoteNumber),
                'quote_type' => $isRental ? 'rental_only' : 'standard',
                'customer_name' => pSQL($customer->firstname . ' ' . $customer->lastname),
                'customer_email' => pSQL($customer->email),
                'customer_phone' => pSQL($phone),
                'status' => 'pending',
                'valid_until' => pSQL($expiryDate),
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s')
            ];

            $result = $db->insert('jca_quotes', $insertQuote);
            if (!$result) {
                throw new Exception('Erreur lors de l\'insertion du devis');
            }

            $idQuote = (int)$db->Insert_ID();

            // Items
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
                $price = isset($p['price']) ? (float)$p['price'] : 0;
                $originalPrice = $price;

                // Déterminer le taux à utiliser (priorité au taux envoyé depuis le frontend)
                $itemRate = null;
                if ($isRental) {
                    if ($ratePercentage !== null) {
                        $itemRate = $ratePercentage;
                    } elseif ($rentalConfiguration && isset($rentalConfiguration['selected_rate'])) {
                        $itemRate = (float)$rentalConfiguration['selected_rate'];
                    }
                }

                // Pour les locations, calculer le prix avec le taux
                if ($isRental && $itemRate !== null) {
                    $price = round($price * (1 + ($itemRate / 100)), 2);
                }

                $insertItem = [
                    'id_quote' => $idQuote,
                    'id_product' => isset($p['id_product']) ? (int)$p['id_product'] : 0,
                    'product_name' => pSQL($p['name']),
                    'product_reference' => isset($p['reference']) ? pSQL($p['reference']) : '',
                    'quantity' => $quantity,
                    'price' => $price,
                    'original_price' => isset($p['original_price']) ? (float)$p['original_price'] : $originalPrice,
                    'is_rental' => $isRental ? 1 : 0,
                    'duration_months' => $durationMonths,
                    'rate_percentage' => $itemRate,
                    'id_rental_configuration' => $idRentalConfiguration,
                    'date_add' => date('Y-m-d H:i:s')
                ];

                $result = $db->insert('jca_quote_items', $insertItem);
                if (!$result) {
                    throw new Exception('Erreur lors de l\'insertion des items');
                }
            }

            // Valider la transaction
            $db->execute('COMMIT');

            // Envoyer l'email de notification
            try {
                $quote = $db->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'jca_quotes WHERE id_quote = ' . $idQuote);
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

            $errorMsg = $e->getMessage();
            $sqlError = '';
            if (method_exists($db, 'getMsgError')) {
                $sqlError = $db->getMsgError();
            }

            error_log('=== ERREUR SAVEDEVIS ===');
            error_log('Message: ' . $errorMsg);
            error_log('SQL Error: ' . $sqlError);
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('========================');

            die(json_encode([
                'success' => false,
                'message' => 'Erreur lors de la création du devis: ' . $errorMsg,
                'sql_error' => $sqlError,
                'debug' => [
                    'error' => $errorMsg,
                    'sql_error' => $sqlError,
                    'trace' => $e->getTraceAsString()
                ]
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
}
