<?php

class Jca_locationdevisSavedevisModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $ajax = true;

    public function postProcess()
    {
        $customer = $this->context->customer;
        $cart = $this->context->cart;

        if (!$customer->isLogged()) {
            die(json_encode(['success' => false, 'message' => 'Client non connecté']));
        }

        $products = $cart->getProducts();
        if (empty($products)) {
            die(json_encode(['success' => false, 'message' => 'Panier vide']));
        }

        try {
            $settings = $this->getQuoteSettings();
            $quoteNumber = $this->generateQuoteNumber($settings);

            $input = json_decode(file_get_contents('php://input'), true);
            $quoteType = isset($input['quote_type']) ? pSQL($input['quote_type']) : 'standard';

            // Création du devis
            $now = date('Y-m-d H:i:s');
            $validUntil = date('Y-m-d H:i:s', strtotime("+{$settings['validity_hours']} hours"));

            Db::getInstance()->insert('jca_quotes', [
                'quote_number'    => pSQL($quoteNumber),
                'quote_type'      => $quoteType, // Utilisation de la valeur récupérée
                'customer_name'   => pSQL(strtoupper($customer->lastname) . ' ' . $customer->firstname),
                'customer_email'  => pSQL($customer->email),
                'customer_phone'  => pSQL(!empty($customer->phone_mobile) ? $customer->phone_mobile : ''),
                'status'          => 'pending',
                'valid_until'     => $validUntil,
                'date_add'        => $now,
                'date_upd'        => $now,
            ]);

            $idQuote = (int) Db::getInstance()->Insert_ID();

            $products = [];
            $rentalConfiguration = null;
            $durationMonths = null;

            if ($quoteType === 'standard') {
                // Récupérer les produits du panier
                $products = $cart->getProducts();
            } else {
                // Récupérer les produits à partir des id_products envoyés
                $productIds = isset($input['id_products']) ? $input['id_products'] : [];
                $durationMonths = isset($input['duration_month']) ? (int)$input['duration_month'] : null;

                $totalPriceHT = 0;
                foreach ($productIds as $productId) {
                    $product = new Product((int)$productId, false, $this->context->language->id);

                    if (Validate::isLoadedObject($product)) {
                        $priceHT = Product::getPriceStatic($product->id, false);
                        $totalPriceHT += $priceHT;

                        $products[] = [
                            'id_product' => $product->id,
                            'reference' => $product->reference,
                            'name' => $product->name,
                            'cart_quantity' => 1,
                            'price' => $priceHT,
                        ];
                    }
                }

                // Récupérer la configuration de location correspondante
                if ($durationMonths && $totalPriceHT > 0) {
                    $rateColumn = ($durationMonths == 36) ? 'duration_36_months' : 'duration_60_months';
                    $sql = 'SELECT id_rental_configuration, price_min, price_max, ' . $rateColumn . ' AS selected_rate
                            FROM ' . _DB_PREFIX_ . 'jca_rental_configurations
                            WHERE price_min <= ' . (float)$totalPriceHT . '
                            AND price_max >= ' . (float)$totalPriceHT . '
                            ORDER BY sort_order ASC
                            LIMIT 1';
                    $rentalConfiguration = Db::getInstance()->getRow($sql);
                }
            }

            // Items
            foreach ($products as $p) {
                $idRentalConfiguration = isset($p['id_rental_configuration']) ? (int)$p['id_rental_configuration'] : null;

                if ($idRentalConfiguration) {
                    $exists = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'jca_rental_configurations WHERE id_rental_configuration = ' . $idRentalConfiguration);

                    if ($exists == 0) {
                        throw new Exception('Invalid id_rental_configuration: ' . $idRentalConfiguration);
                    }
                }

                $data = [
                    'id_quote'             => $idQuote,
                    'item_type'            => 'product',
                    'id_product'           => (int)$p['id_product'],
                    'product_reference'    => pSQL($p['reference']),
                    'product_name'         => pSQL($p['name']),
                    'quantity'             => (int)$p['cart_quantity'],
                    'price'                => (float)$p['price'],
                    'is_rental'            => ($quoteType !== 'standard') ? 1 : 0,
                    'date_add'             => $now,
                ];

                // Pour les devis de location, ajouter les informations de location
                if ($quoteType !== 'standard' && $rentalConfiguration && $durationMonths) {
                    $data['id_rental_configuration'] = (int)$rentalConfiguration['id_rental_configuration'];
                    $data['duration_months'] = $durationMonths;
                    $data['rate_percentage'] = (float)$rentalConfiguration['selected_rate'];

                    // Calculer le prix mensuel pour ce produit
                    $monthlyPrice = round(((float)$p['price'] / $durationMonths) * (1 + ((float)$rentalConfiguration['selected_rate'] / 100)), 2);
                    $data['price'] = $monthlyPrice;
                } elseif (!empty($p['id_rental_configuration'])) {
                    $data['id_rental_configuration'] = (int)$p['id_rental_configuration'];
                }

                Db::getInstance()->insert('jca_quote_items', $data);
            }

            // Ajouter la livraison si sélectionnée
            if (isset($input['delivery']) && !empty($input['delivery'])) {
                $delivery = $input['delivery'];
                $deliveryData = [
                    'id_quote'             => $idQuote,
                    'item_type'            => 'delivery',
                    'id_product'           => (int)$delivery['id_carrier'],
                    'product_reference'    => 'CARRIER-' . (int)$delivery['id_carrier'],
                    'product_name'         => pSQL($delivery['carrier_name']),
                    'quantity'             => 1,
                    'price'                => (float)$delivery['price_without_tax'],
                    'is_rental'            => 0,
                    'date_add'             => $now,
                ];
                Db::getInstance()->insert('jca_quote_items', $deliveryData);
            }

            // Client - Vérifier si le client existe déjà
            $existingCustomer = Db::getInstance()->getRow(
                'SELECT id_customer FROM ' . _DB_PREFIX_ . 'jca_quote_customers WHERE email = "' . pSQL($customer->email) . '"'
            );

            if ($existingCustomer) {
                // Mettre à jour le client existant
                Db::getInstance()->update('jca_quote_customers', [
                    'id_customer_prestashop'  => (int)$customer->id,
                    'name'                    => pSQL($customer->firstname . ' ' . strtoupper($customer->lastname)),
                    'phone'                   => pSQL(!empty($customer->phone_mobile) ? $customer->phone_mobile : ''),
                    'date_upd'                => $now,
                ], 'email = "' . pSQL($customer->email) . '"');
            } else {
                // Insérer un nouveau client
                Db::getInstance()->insert('jca_quote_customers', [
                    'id_quote'                => $idQuote,
                    'id_customer_prestashop'  => (int)$customer->id,
                    'email'                   => pSQL($customer->email),
                    'name'                    => pSQL($customer->firstname . ' ' . strtoupper($customer->lastname)),
                    'phone'                   => pSQL(!empty($customer->phone_mobile) ? $customer->phone_mobile : ''),
                    'date_add'                => $now,
                    'date_upd'                => $now,
                ]);
            }

            // Mettre à jour le compteur dans settings
            $newCounter = $settings['quote_number_counter'] + 1;
            Db::getInstance()->update('jca_quote_settings', ['quote_number_counter' => $newCounter], 'id_quote_settings = ' . (int)$settings['id_quote_settings']);

            // Envoyer l'email de confirmation
            $emailSent = false;
            try {
                error_log('=== SAVEDEVIS: DEBUT ENVOI EMAIL ===');
                require_once _PS_MODULE_DIR_ . 'jca_locationdevis/src/Service/QuoteEmailService.php';
                $emailService = new Jca\JcaLocationDevis\Service\QuoteEmailService();
                error_log('Service email créé');

                // Récupérer les données complètes du devis pour l'email
                $quoteData = Db::getInstance()->getRow('
                    SELECT q.*, qc.name as customer_full_name
                    FROM ' . _DB_PREFIX_ . 'jca_quotes q
                    LEFT JOIN ' . _DB_PREFIX_ . 'jca_quote_customers qc ON qc.id_quote = q.id_quote
                    WHERE q.id_quote = ' . (int)$idQuote
                );

                // Récupérer les items du devis
                $itemsData = Db::getInstance()->executeS('
                    SELECT *
                    FROM ' . _DB_PREFIX_ . 'jca_quote_items
                    WHERE id_quote = ' . (int)$idQuote
                );

                // Préparer les données pour l'email
                $nameParts = explode(' ', $customer->firstname . ' ' . $customer->lastname);
                $quoteForEmail = [
                    'quote_number' => $quoteNumber,
                    'customer_email' => $customer->email,
                    'customer_firstname' => $customer->firstname,
                    'customer_lastname' => $customer->lastname,
                    'customer_name' => $customer->firstname . ' ' . strtoupper($customer->lastname),
                    'status' => 'pending',
                    'date_add' => $now,
                    'valid_until' => $validUntil
                ];

                $itemsForEmail = [];
                foreach ($itemsData as $item) {
                    $itemsForEmail[] = [
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'price' => isset($item['price']) ? $item['price'] : 0,
                        'is_rental' => isset($item['is_rental']) ? $item['is_rental'] : 0,
                        'duration_months' => isset($item['duration_months']) ? $item['duration_months'] : null,
                        'item_type' => isset($item['item_type']) ? $item['item_type'] : 'product'
                    ];
                }

                error_log('Email destinataire: ' . $quoteForEmail['customer_email']);
                error_log('Appel sendQuoteCreatedEmail...');
                $emailResult = $emailService->sendQuoteCreatedEmail($quoteForEmail, $itemsForEmail);
                error_log('Résultat email: ' . json_encode($emailResult));
                $emailSent = $emailResult['success'] ?? false;
            } catch (\Exception $e) {
                // Log l'erreur mais ne bloque pas la création du devis
                error_log('Erreur envoi email devis: ' . $e->getMessage());
            }

            die(json_encode([
                'success' => true,
                'message' => 'Devis créé avec succès',
                'quote_number' => $quoteNumber,
                'id_quote' => $idQuote,
                'email_sent' => $emailSent
            ]));
        } catch (\Exception $e) {
            die(json_encode([
                'success' => false,
                'message' => 'Erreur lors de la création du devis: ' . $e->getMessage()
            ]));
        }
    }

    private function getQuoteSettings()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_settings` WHERE 1';
        $settings = Db::getInstance()->getRow($sql);

        if (!$settings) {
            throw new Exception('Aucune configuration de devis trouvée');
        }

        return $settings;
    }

    private function generateQuoteNumber(array $settings)
    {
        $year = date('Y'); // Année actuelle

        // Vérifier si le compteur doit être réinitialisé annuellement
        if ($settings['quote_number_reset_yearly'] && $settings['quote_number_last_year'] != $year) {
            $settings['quote_number_counter'] = 1; // Réinitialiser le compteur
            Db::getInstance()->update('jca_quote_settings', [
                'quote_number_counter' => 1,
                'quote_number_last_year' => $year
            ], 'id_quote_settings = ' . (int)$settings['id_quote_settings']);
        }

        // Préfixe du numéro de devis
        $prefix = $settings['quote_number_prefix'];

        // Format de l'année (ex: YY pour 25, YYYY pour 2025)
        $yearFormat = $settings['quote_number_year_format'] === 'YY' ? substr($year, -2) : $year;

        // Séparateur
        $separator = $settings['quote_number_separator'];

        // Remplissage du compteur avec des zéros
        $counter = (int) str_pad($settings['quote_number_counter'], (int)$settings['quote_number_padding'], '0', STR_PAD_LEFT);
        $counter++;
        // Générer le numéro de devis
        $newQuoteNumber = $prefix . $separator . $yearFormat . $separator . $counter;

        // Incrémenter le compteur pour le prochain devis
        $settings['quote_number_counter']++;
        Db::getInstance()->update('jca_quote_settings', [
            'quote_number_counter' => $settings['quote_number_counter']
        ], 'id_quote_settings = ' . (int)$settings['id_quote_settings']);

        return $newQuoteNumber;
    }
}
