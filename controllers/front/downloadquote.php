<?php

class Jca_locationdevisDownloadquoteModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $context = Context::getContext();
        $smarty  = $context->smarty;

        $idQuote = (int) Tools::getValue('id_quote');
        if (!$idQuote) {
            die('ID devis invalide');
        }

        $quote = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quotes` WHERE id_quote = ' . $idQuote
        );

        if (!$quote) {
            die('Devis introuvable');
        }
        /* ================= CUSTOMER ================= */
        $customerRow = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_customers` WHERE id_quote = ' . $idQuote
        );

        $invoice = $delivery = [];
        $location = [];

        // Récupérer le client PrestaShop
        if ($customerRow && !empty($customerRow['id_customer_prestashop'])) {
            $customerObj = new Customer((int)$customerRow['id_customer_prestashop']);

            // Récupérer les adresses depuis la base
            $addresses = $customerObj->getAddresses((int)$context->language->id);

            // Trouver l'adresse par défaut ou la première disponible
            $defaultAddress = null;
            if (!empty($addresses)) {
                foreach ($addresses as $addr) {
                    if (!$defaultAddress) {
                        $defaultAddress = $addr;
                    }
                }
            }

            if ($defaultAddress) {
                $invoiceAddress = new Address((int)$defaultAddress['id_address']);
                $invoice = [
                    'name'     => trim($customerObj->firstname . ' ' . $customerObj->lastname),
                    'company'  => $invoiceAddress->company,
                    'address1' => $invoiceAddress->address1,
                    'postcode' => $invoiceAddress->postcode,
                    'city'     => $invoiceAddress->city,
                    'country'  => Country::getNameById($context->language->id, $invoiceAddress->id_country),
                    'phone'    => $invoiceAddress->phone,
                    'email'    => $customerObj->email,
                ];

                $deliveryAddress = $invoiceAddress;
                $delivery = [
                    'name'     => trim($customerObj->firstname . ' ' . $customerObj->lastname),
                    'company'  => $deliveryAddress->company,
                    'address1' => $deliveryAddress->address1,
                    'postcode' => $deliveryAddress->postcode,
                    'city'     => $deliveryAddress->city,
                    'country'  => Country::getNameById($context->language->id, $deliveryAddress->id_country),
                    'phone'    => $deliveryAddress->phone,
                    'email'    => $customerObj->email,
                ];
            } else {
                // Pas d'adresse trouvée
                $invoice = $delivery = [
                    'name'     => trim($customerObj->firstname . ' ' . $customerObj->lastname),
                    'company'  => '',
                    'address1' => '',
                    'postcode' => '',
                    'city'     => '',
                    'country'  => '',
                    'phone'    => '',
                    'email'    => $customerObj->email,
                ];
            }
        } else {
            // fallback si le client PrestaShop n'existe pas
            $invoice = $delivery = [
                'name'     => $customerRow['firstname'] ?? '',
                'company'  => $customerRow['company'] ?? '',
                'address1' => $customerRow['address_invoice'] ?? '',
                'postcode' => $customerRow['postcode_invoice'] ?? '',
                'city'     => $customerRow['city_invoice'] ?? '',
                'country'  => $customerRow['country_invoice'] ?? '',
                'phone'    => $customerRow['phone_invoice'] ?? '',
                'email'    => $customerRow['email_invoice'] ?? '',
            ];
        }

        /* ================= PRODUITS ET LIVRAISON ================= */
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_items` WHERE id_quote = ' . $idQuote
        );
        // Adresse temporaire pour le calcul de TVA (OBLIGATOIRE pour PrestaShop)
        $taxAddress = new Address();
        $taxAddress->id_country = (int) $context->country->id;
        $taxAddress->id_state = 0;
        $taxAddress->postcode = '';
        $taxAddress->vat_number = '';

        $items = [];
        $deliveryItem = null;
        $totalHt = 0;
        $totalTax = 0;

        foreach ($rows as $row) {
            $quantity = (int) $row['quantity'];
            $priceHt  = (float) $row['price'];
            $lineHt   = $priceHt * $quantity;

            // Vérifier si c'est un item de livraison
            $isDelivery = isset($row['item_type']) && $row['item_type'] === 'delivery';

            if ($isDelivery) {
                // Traiter la livraison séparément
                $deliveryItem = [
                    'name'            => $row['product_name'],
                    'price'           => Tools::displayPrice($priceHt, $context->currency),
                    'price_raw'       => $priceHt,
                ];
                $totalHt  += $lineHt;
                continue;
            }

            // Traiter les produits normaux
            $product = new Product((int)$row['id_product'], false, $context->language->id);

            /* ===== ÉCOTAXE ===== */
            $ecotax = (float) $product->ecotax;

            /* ===== TVA ===== */
            $taxManager = TaxManagerFactory::getManager(
                $taxAddress,
                (int) $product->id_tax_rules_group
            );

            $taxCalculator = $taxManager->getTaxCalculator();
            $taxRate = (float) $taxCalculator->getTotalRate();

            $lineTax = $lineHt * ($taxRate / 100);

            /* ===== LOCATION ===== */
            $isRental = (int)$row['is_rental'] === 1;
            $monthlyPayment = null;

            if ($isRental && $row['rate_percentage'] && $row['duration_months']) {
                // Pour les locations, le prix est déjà la mensualité
                $monthlyPayment = $priceHt;
            }

            $items[] = [
                'name'            => $row['product_name'],
                'reference'       => $row['product_reference'],
                'quantity'        => $quantity,
                'price'          => Tools::displayPrice($priceHt, $context->currency),
                'total'           => Tools::displayPrice($lineHt, $context->currency),
                'tax_rate'        => $taxRate,
                'ecotax'          => $ecotax,
                'is_rental'       => $isRental,
                'duration_months' => $row['duration_months'],
                'rate_percentage' => $row['rate_percentage'],
                'monthly_payment' => $monthlyPayment,
            ];

            // Pour les locations, ne pas ajouter au total HT classique
            if (!$isRental) {
                $totalHt  += $lineHt;
                $totalTax += $lineTax;
            }
        }
        /* LOCATION */
        $location['is_rental'] = $quote['quote_type'] === 'rental_only' ? true : false;

        if ($location['is_rental']) {
            $location['duration_months'] = $rows[0]['duration_months']; // le meme pour tous  
            $location['rate_percentage'] = $rows[0]['rate_percentage']; // le meme pour tous
            $location['monthly_total'] = 0;
            foreach ($items as $item) {
                $location['monthly_total'] += $item['monthly_payment'] ?? 0;
            }
            $location['monthly_total'] = Tools::displayPrice($location['monthly_total'], $context->currency);
        }


        /* ================= TOTAUX ================= */
        $totals = [
            'subtotal' => Tools::displayPrice($totalHt, $context->currency),
            'tax'   => Tools::displayPrice($totalTax, $context->currency),
            'total'   => Tools::displayPrice($totalHt + $totalTax, $context->currency),
        ];



        // $delivery = $invoice;
        $shopLogoPath = _PS_IMG_DIR_ . Configuration::get('PS_LOGO');

        if (!file_exists($shopLogoPath)) {
            $shopLogoPath = null;
        }
        /* ================= SHOP ================= */
        $smarty->assign([
            'quote'        => $quote,
            'products'     => $items,
            'delivery_item' => $deliveryItem,
            'totals'       => $totals,
            'invoice'      => $invoice,
            'delivery'     => $delivery,
            'location'     => $location,
            'shop_name'    => Configuration::get('PS_SHOP_NAME'),
            'shop_address1' => Configuration::get('PS_SHOP_ADDR1'),
            'shop_postcode' => Configuration::get('PS_SHOP_CODE'),
            'shop_city'    => Configuration::get('PS_SHOP_CITY'),
            'shop_phone'   => Configuration::get('PS_SHOP_PHONE'),
            'shop_email'   => Configuration::get('PS_SHOP_EMAIL'),
            'shop_rcs'     => Configuration::get('PS_SHOP_DETAILS'),
            'shop_vat'     => Configuration::get('VATNUMBER'),
            'shop_siret'   => Configuration::get('PS_SHOP_SIRET'),
            'logo_path'    => $shopLogoPath,
            'date'         => Tools::displayDate(null, null, null, false),
            'title'        => $quote['quote_number']   ?? $quote['id_quote'],
            'available_in_your_account' => ['value' => 'Ce devis est disponible dans votre compte.'],
            'shop_address' => Configuration::get('PS_SHOP_ADDR1') . ' ' . Configuration::get('PS_SHOP_CODE') . ' ' . Configuration::get('PS_SHOP_CITY'),
        ]);

        /* ================= PDF ================= */
        $html = $smarty->fetch(
            _PS_MODULE_DIR_ . 'jca_locationdevis/views/templates/pdf/quote.tpl'
        );

        $pdf = new PDFGenerator(false, 'P');

        // Désactive header et footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Marges : left, top, right
        $pdf->SetMargins(10, 5, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Ajouter une page
        $pdf->AddPage();

        // Choisir la font
        $pdf->setFontForLang($context->language->iso_code);

        // Injecter le contenu HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Générer le PDF
        $pdf->Output('Devis_' . ($quote['quote_number'] ?? $quote['id_quote']) . '.pdf', 'D');
        exit;
    }
}
