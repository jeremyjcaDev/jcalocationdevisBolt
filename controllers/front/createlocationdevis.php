<?php

class Jca_locationdevisCreatelocationdevisModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $customer = $this->context->customer;

        // Vérification si l'utilisateur est connecté
        if (!$customer->isLogged()) {
            $this->context->smarty->assign([
                'login_url' => $this->context->link->getPageLink('authentication', true),
            ]);

            $this->setTemplate('module:jca_locationdevis/views/templates/front/must_login.tpl');
            return;
        }

        // Sinon, tout est ok → afficher le devis

        // Récupérer le paramètre 'customization' depuis l'URL
        $customization = Tools::getValue('customization');
        $mode = Tools::getValue('mode');
        if ($customization) {
            // Décoder la valeur JSON si nécessaire
            $customizationData = json_decode($customization, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Assigner les données de personnalisation au template
                $this->context->smarty->assign('customizationData', $customizationData);

                // Vérifier si des données de personnalisation sont disponibles
                if (isset($customizationData['components']) && is_array($customizationData['components'])) {
                    $products = [];

                    foreach ($customizationData['components'] as $component) {
                        if (isset($component['id']) && isset($component['id_product_attribute'])) {
                            $product = new Product((int)$component['id'], true, $this->context->language->id);

                            if (Validate::isLoadedObject($product)) {
                                $products[] = [
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'price' => $product->price,
                                    'reference' => $product->reference,
                                    'id_product_attribute' => (int)$component['id_product_attribute'],
                                    'quantity' => (int)$component['qty'],
                                ];
                            }
                        }
                    }

                    // Assigner les produits récupérés au template
                    $this->context->smarty->assign('products', $products);

                    // Calculer le prix total HT des produits
                    $totalPriceHT = array_reduce($products, function ($sum, $product) {
                        return $sum + $product['price'] * $product['quantity'];
                    }, 0);

                    // Rechercher la plage correspondante dans la table ps_jca_rental_configurations
                    $sql = 'SELECT id_rental_configuration, price_min, price_max, sort_order, date_add, date_upd, 
                            IF(' . (int)$mode . ' = 36, duration_36_months, duration_60_months) AS selected_rate
                            FROM `' . _DB_PREFIX_ . 'jca_rental_configurations`
                            WHERE price_min <= ' . (float)$totalPriceHT . ' AND price_max >= ' . (float)$totalPriceHT . '
                            ORDER BY sort_order ASC';
                    $rentalConfiguration = Db::getInstance()->getRow($sql);
                    if ($rentalConfiguration) {
                        // Assigner la configuration récupérée au template
                        $this->context->smarty->assign('rentalConfiguration', $rentalConfiguration);

                        // Calculer le prix par mois
                        $monthlyPriceHT = round(($totalPriceHT / (int)$mode) * (1 + ($rentalConfiguration['selected_rate'] / 100)), 2);
                        $date = [];
                        $settings = $this->getQuoteSettings();
                        $date['created_at'] = date('d/m/Y');
                        $date['valid_until'] = date('d/m/Y', strtotime("+{$settings['validity_hours']} hours"));
                        // Assigner le prix par mois au template
                        $this->context->smarty->assign('date', $date);
                        $this->context->smarty->assign('monthlyPriceHT', $monthlyPriceHT);
                        $this->context->smarty->assign('mode', (int)$mode);
                    } else {
                        $this->context->smarty->assign('rentalConfigurationError', 'No matching rental configuration found.');
                    }
                    $cart = $this->context->cart;
                    $deliveryOptions = $cart->getDeliveryOptionList();
                    $this->context->smarty->assign('delivery_options', $deliveryOptions);
                } else {
                    $this->context->smarty->assign('customizationError', 'No valid components found in customization data.');
                }
            } else {
                // Gérer les erreurs de décodage JSON
                $this->context->smarty->assign('customizationError', 'Invalid customization data.');
            }
        } else {
            // Gérer le cas où le paramètre n'est pas présent
            $this->context->smarty->assign('customizationError', 'No customization data provided.');
        }

        $this->setTemplate('module:jca_locationdevis/views/templates/front/createlocationdevis.tpl');
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
