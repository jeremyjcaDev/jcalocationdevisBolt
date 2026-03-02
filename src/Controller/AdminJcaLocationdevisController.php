<?php

namespace Jca\JcaLocationdevis\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Jca\JcaLocationdevis\Query\GetQuoteSettingQuery;
use Jca\JcaLocationdevis\Query\ListRentalConfigurationQuery;
use Jca\JcaLocationdevis\Query\ListProductRentalAvailabilityQuery;
use Jca\JcaLocationdevis\Command\CreateQuoteCommand;
use Jca\JcaLocationdevis\Command\UpdateQuoteSettingCommand;
use Jca\JcaLocationdevis\Command\CreateRentalConfigurationCommand;
use Jca\JcaLocationdevis\Command\UpdateQuoteCommand;
use Jca\JcaLocationdevis\Command\DeleteQuoteCommand;
use Jca\JcaLocationdevis\Command\CreateProductRentalAvailabilityCommand;
use Jca\JcaLocationdevis\Command\UpdateProductRentalAvailabilityCommand;
use Jca\JcaLocationdevis\Command\DeleteProductRentalAvailabilityCommand;
use Jca\JcaLocationdevis\Command\CreateQuoteCustomerCommand;
use Jca\JcaLocationdevis\Command\UpdateQuoteCustomerCommand;
use Jca\JcaLocationdevis\Command\DeleteQuoteCustomerCommand;
use Jca\JcaLocationdevis\Entity\QuoteCustomer;
use Jca\JcaLocationdevis\Pdf\HTMLTemplateQuote;
use Jca\JcaLocationdevis\Command\UpdateRentalConfigurationCommand;
use Jca\JcaLocationdevis\Command\DeleteRentalConfigurationCommand;
use Jca\JcaLocationdevis\Command\CreateQuoteSettingCommand;
use Jca\JcaLocationdevis\Command\DeleteQuoteSettingCommand;
use Jca\JcaLocationdevis\Service\QuoteEmailService;
use Media;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShop\PrestaShop\Core\Domain\Product\Query\SearchProductsForAssociation;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductConstraintException;
use PrestaShop\PrestaShop\Core\PDF\PDFGenerator;
use Context;

class AdminJcaLocationdevisController extends FrameworkBundleAdminController
{
    public function indexAction(): Response
    {
        $router = $this->get('router'); // dispo dans services du BO
        Media::addJsDef([
            'JCA_LOCATIONDEVIS_CONFIG' => [
                'apiBaseUrl' => $router->generate('jca_locationdevis_handle_action'),
                'token' => $this->get('security.csrf.token_manager')->getToken('jca_locationdevis')->getValue(),
            ],
        ]);
        return $this->render('@Modules/jca_locationdevis/views/templates/admin/config.html.twig');
    }

    public function handleAction(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['entity']) || !isset($data['data']) || !isset($data['action'])) {
            return $this->json(['success' => false, 'message' => 'Entité, action ou données manquantes'], 400);
        }

        $action = $data['action'];
        $entity = $data['entity'];
        $entityData = $data['data'];

        try {
            switch ($entity) {
                case 'Quote':
                    $result = $this->handleQuoteAction($action, $entityData);
                    if ($result) {
                        return $result;
                    }
                    break;

                case 'ProductRentalAvailability':
                    return $this->handleProductRentalAvailabilityAction($action, $entityData);

                case 'QuoteCustomer':
                    $result = $this->handleQuoteCustomerAction($action, $entityData);
                    if ($result) {
                        return $result;
                    }
                    break;

                case 'RentalConfiguration':
                    return $this->handleRentalConfigurationAction($action, $entityData);
                case 'QuoteSetting':
                    return $this->handleQuoteSettingAction($action, $entityData);
                case 'Product':
                    return $this->searchProducts($entityData);
                case 'Customer':
                    return $this->handleCustomerAction($action, $entityData);
                default:
                    return $this->json(['success' => false, 'message' => 'Entité inconnue'], 400);
            }
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return $this->json(['success' => true, 'message' => ucfirst($action) . ' effectué avec succès']);
    }

    private function handleQuoteAction(string $action, array $data): ?Response
    {
        switch ($action) {
            case 'list':
                $quotes = $this->get('prestashop.core.query_bus')->handle(
                    new \Jca\JcaLocationdevis\Query\ListQuotesQuery($data['status'] ?? null)
                );
                return $this->json([
                    'success' => true,
                    'data' => $quotes
                ]);

            case 'get':
            case 'getWithItems':
                $quote = $this->get('prestashop.core.query_bus')->handle(
                    new \Jca\JcaLocationdevis\Query\GetQuoteWithItemsQuery($data['id'])
                );
                return $this->json([
                    'success' => true,
                    'data' => $quote
                ]);

            case 'save':
                $quoteId = $this->get('prestashop.core.command_bus')->handle(new CreateQuoteCommand(
                    $data['quote_type'] ?? $data['quoteType'] ?? 'standard',
                    $data['customer_name'] ?? $data['customerName'] ?? null,
                    $data['customer_email'] ?? $data['customerEmail'] ?? null,
                    $data['customer_phone'] ?? $data['customerPhone'] ?? null,
                    $data['status'] ?? 'draft',
                    $data['valid_until'] ?? $data['validUntil'] ?? null,
                    $data['items'] ?? []
                ));

                $quote = $this->get('prestashop.core.query_bus')->handle(
                    new \Jca\JcaLocationdevis\Query\GetQuoteWithItemsQuery($quoteId)
                );

                $emailService = new QuoteEmailService();
                $emailResult = $emailService->sendQuoteCreatedEmail($quote, $quote['items'] ?? []);

                return $this->json([
                    'success' => true,
                    'message' => 'Devis créé avec succès',
                    'data' => $quote,
                    'email_sent' => $emailResult['success'] ?? false
                ]);

            case 'update':
                try {
                    $statusChanged = false;
                    $oldStatus = null;

                    if (isset($data['status'])) {
                        $oldQuote = $this->get('prestashop.core.query_bus')->handle(
                            new \Jca\JcaLocationdevis\Query\GetQuoteWithItemsQuery($data['id'])
                        );
                        $oldStatus = $oldQuote['status'] ?? null;

                        $this->get('prestashop.core.command_bus')->handle(new \Jca\JcaLocationdevis\Command\UpdateQuoteStatusCommand(
                            $data['id'],
                            $data['status']
                        ));

                        if ($oldStatus !== $data['status']) {
                            $statusChanged = true;
                        }
                    }

                    if (isset($data['customerName']) || isset($data['customerEmail']) || isset($data['customerPhone'])) {
                        $this->get('prestashop.core.command_bus')->handle(new UpdateQuoteCommand(
                            $data['id'],
                            $data['customerName'] ?? null,
                            $data['customerEmail'] ?? null,
                            $data['customerPhone'] ?? null
                        ));
                    }

                    $quote = $this->get('prestashop.core.query_bus')->handle(
                        new \Jca\JcaLocationdevis\Query\GetQuoteWithItemsQuery($data['id'])
                    );

                    $emailSent = false;
                    $emailError = null;
                    if ($statusChanged && in_array($data['status'], ['validated', 'refused'])) {
                        try {
                            $emailService = new QuoteEmailService();
                            $emailResult = $emailService->sendQuoteStatusEmail($quote, $quote['items'] ?? [], $data['status']);
                            $emailSent = $emailResult['success'] ?? false;
                            if (!$emailSent && isset($emailResult['error'])) {
                                $emailError = $emailResult['error'];
                            }
                        } catch (\Exception $emailException) {
                            $emailError = $emailException->getMessage();
                            error_log('Email error: ' . $emailError);
                        }
                    }

                    return $this->json([
                        'success' => true,
                        'message' => 'Devis mis à jour avec succès',
                        'data' => $quote,
                        'email_sent' => $emailSent,
                        'email_error' => $emailError
                    ]);
                } catch (\Exception $e) {
                    error_log('Update quote error: ' . $e->getMessage());
                    error_log('Stack trace: ' . $e->getTraceAsString());
                    return $this->json([
                        'success' => false,
                        'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ], 500);
                }

            case 'delete':
                $this->get('prestashop.core.command_bus')->handle(new DeleteQuoteCommand($data['id']));
                break;

            case 'generatePdf':
                return $this->generateQuotePdf($data['id']);
        }

        return null;
    }

    private function generateQuotePdf(int $quoteId): Response
    {
        $quote = $this->get('prestashop.core.query_bus')->handle(
            new \Jca\JcaLocationdevis\Query\GetQuoteWithItemsQuery($quoteId)
        );

        if (!$quote) {
            return $this->json(['success' => false, 'message' => 'Devis non trouvé'], 404);
        }

        $context = Context::getContext();
        $smarty = $context->smarty;

        $total_ht = 0;
        if (isset($quote['items']) && is_array($quote['items'])) {
            foreach ($quote['items'] as &$item) {
                $item['quantity'] = $item['quantity'] ?? 1;
                $item['unit_price'] = $item['price'];
                $line_total = $item['quantity'] * $item['unit_price'];
                $item['line_total'] = $line_total;

                if ($item['is_rental'] && isset($item['rate_percentage']) && isset($item['duration_months'])) {
                    $item['monthly_payment'] = ($item['unit_price'] * $item['rate_percentage'] / 100) / $item['duration_months'];
                }

                $total_ht += $line_total;
            }
        }

        $tax_rate = $quote['tax_rate'] ?? 20;
        $total_tax = $total_ht * ($tax_rate / 100);
        $total_ttc = $total_ht + $total_tax;

        $smarty->assign([
            'quote' => $quote,
            'total_ht' => $total_ht,
            'total_tax' => $total_tax,
            'total_ttc' => $total_ttc,
            'shop_name' => \Configuration::get('PS_SHOP_NAME'),
            'shop_address1' => \Configuration::get('PS_SHOP_ADDR1'),
            'shop_address2' => \Configuration::get('PS_SHOP_ADDR2'),
            'shop_postcode' => \Configuration::get('PS_SHOP_CODE'),
            'shop_city' => \Configuration::get('PS_SHOP_CITY'),
            'shop_phone' => \Configuration::get('PS_SHOP_PHONE'),
            'shop_email' => \Configuration::get('PS_SHOP_EMAIL'),
        ]);

        $templatePath = _PS_MODULE_DIR_ . 'jca_locationdevis/views/templates/pdf/quote.tpl';
        $html = $smarty->fetch($templatePath);

        // Créer l'objet PDF
        $pdf = new \PDFGenerator(false, 'P');
        $pdf->setFontForLang($context->language->iso_code);
        $pdf->createHeader('<h1>Devis</h1>');
        $pdf->createFooter('<p>Merci de votre confiance</p>');
        $pdf->createContent($html);
        $pdf->writePage();

        // Nom du fichier PDF
        $filename = 'Devis_' . ($quote['quote_number'] ?? $quote['id_quote']) . '.pdf';

        // Rendre le PDF
        $pdfContent = $pdf->render($filename, 'S');

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }



    private function handleProductRentalAvailabilityAction(string $action, array $data): Response
    {
        error_log('handleProductRentalAvailabilityAction called with action: ' . $action);

        switch ($action) {
            case 'save':
                $this->get('prestashop.core.command_bus')->handle(new CreateProductRentalAvailabilityCommand(
                    $data['idProduct'],
                    $data['productReference'],
                    $data['productName'],
                    $data['productPrice']
                ));
                return $this->json(['success' => true, 'message' => 'Produit ajouté avec succès']);

            case 'update':
                $this->get('prestashop.core.command_bus')->handle(new UpdateProductRentalAvailabilityCommand(
                    $data['id'],
                    $data['productReference'] ?? null,
                    $data['productName'] ?? null,
                    $data['productPrice'] ?? null,
                    $data['rental_enabled'] ?? null,
                    $data['duration_36_enabled'] ?? null,
                    $data['duration_60_enabled'] ?? null
                ));
                return $this->json(['success' => true, 'message' => 'Produit mis à jour avec succès']);

            case 'delete':
                $this->get('prestashop.core.command_bus')->handle(new DeleteProductRentalAvailabilityCommand($data['id']));
                return $this->json(['success' => true, 'message' => 'Produit supprimé avec succès']);

            case 'list':
                error_log('Executing list action for ProductRentalAvailability');
                $products = $this->get('prestashop.core.query_bus')->handle(new ListProductRentalAvailabilityQuery());
                error_log('Products retrieved: ' . print_r($products, true));
                return $this->json([
                    'success' => true,
                    'data' => $products,
                ]);
        }

        error_log('Unknown action: ' . $action);
        return $this->json(['success' => false, 'message' => 'Action inconnue'], 400);
    }

    private function handleQuoteCustomerAction(string $action, array $data): ?Response
    {
        switch ($action) {
            case 'save':
                $idCustomerPrestashop = $data['id_customer_prestashop'] ?? $data['idCustomerPrestashop'] ?? null;
                $idQuote = $data['id_quote'] ?? $data['idQuote'] ?? null;

                $command = new CreateQuoteCustomerCommand(
                    $data['email'],
                    $data['name'] ?? null,
                    $data['phone'] ?? null,
                    $idQuote,
                    $idCustomerPrestashop
                );

                $this->get('prestashop.core.command_bus')->handle($command);
                break;

            case 'update':
                $idCustomerPrestashop = $data['id_customer_prestashop'] ?? $data['idCustomerPrestashop'] ?? null;
                $idQuote = $data['id_quote'] ?? $data['idQuote'] ?? null;

                $this->get('prestashop.core.command_bus')->handle(new UpdateQuoteCustomerCommand(
                    $data['id'],
                    $data['email'] ?? null,
                    $data['name'] ?? null,
                    $data['phone'] ?? null,
                    $idQuote,
                    $idCustomerPrestashop
                ));
                break;

            case 'delete':
                $this->get('prestashop.core.command_bus')->handle(new DeleteQuoteCustomerCommand($data['id']));
                break;

            case 'search':
                return $this->searchQuoteCustomer($data);
        }

        return null;
    }

    private function searchQuoteCustomer(array $data): JsonResponse
    {
        if (!isset($data['email']) || empty($data['email'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email manquant'
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $repository = $entityManager->getRepository(QuoteCustomer::class);

        $customers = $repository->searchByEmail($email);

        $results = [];
        foreach ($customers as $customer) {
            $results[] = [
                'id' => $customer->getId(),
                'idQuote' => $customer->getIdQuote(),
                'idCustomerPrestashop' => $customer->getIdCustomerPrestashop(),
                'email' => $customer->getEmail(),
                'name' => $customer->getName(),
                'phone' => $customer->getPhone(),
                'dateAdd' => $customer->getDateAdd()->format('Y-m-d H:i:s'),
                'dateUpd' => $customer->getDateUpd()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $results
        ]);
    }

    private function handleRentalConfigurationAction(string $action, array $data): Response
    {

        switch ($action) {
            case 'save':
                $this->get('prestashop.core.command_bus')->handle(new CreateRentalConfigurationCommand(
                    $data['priceMin'],
                    $data['priceMax'],
                    $data['duration36Months'],
                    $data['duration60Months'],
                    $data['sortOrder']
                ));
                return $this->json(['success' => true, 'message' => 'Configuration créée avec succès']);

            case 'update':
                $this->get('prestashop.core.command_bus')->handle(new UpdateRentalConfigurationCommand(
                    $data['id'],
                    $data['priceMin'] ?? null,
                    $data['priceMax'] ?? null,
                    $data['duration36Months'] ?? null,
                    $data['duration60Months'] ?? null,
                    $data['sortOrder'] ?? null
                ));
                return $this->json(['success' => true, 'message' => 'Configuration mise à jour avec succès']);

            case 'delete':
                $this->get('prestashop.core.command_bus')->handle(new DeleteRentalConfigurationCommand($data['id']));
                return $this->json(['success' => true, 'message' => 'Configuration supprimée avec succès']);

            case 'list':
                $settings = $this->get('prestashop.core.query_bus')->handle(new ListRentalConfigurationQuery());
                return $this->json([
                    'success' => true,
                    'data' => $settings,
                ]);
        }

        return $this->json(['success' => false, 'message' => 'Action inconnue'], 400);
    }

    private function handleQuoteSettingAction(string $action, array $data): Response
    {

        switch ($action) {
            case 'save':
                $this->get('prestashop.core.command_bus')->handle(new CreateQuoteSettingCommand(
                    $data['validityHours'],
                    $data['quoteNumberPrefix'],
                    $data['quoteNumberYearFormat'],
                    $data['quoteNumberSeparator'],
                    $data['quoteNumberPadding']
                ));
                return $this->json(['success' => true, 'message' => 'Quote setting created successfully']);

            case 'update':
                if (!isset($data['id']) || empty($data['id'])) {
                    $this->get('prestashop.core.command_bus')->handle(new CreateQuoteSettingCommand(
                        $data['validity_hours'] ?? 48,
                        $data['quote_number_prefix'] ?? 'DEVIS',
                        $data['quote_number_year_format'] ?? 'YYYY',
                        $data['quote_number_separator'] ?? '-',
                        $data['quote_number_padding'] ?? 3
                    ));
                    return $this->json(['success' => true, 'message' => 'Quote setting created successfully']);
                }

                $this->get('prestashop.core.command_bus')->handle(new UpdateQuoteSettingCommand(
                    $data['id'],
                    $data['validity_hours'] ?? null,
                    $data['quote_number_prefix'] ?? null,
                    $data['quote_number_year_format'] ?? null,
                    $data['quote_number_separator'] ?? null,
                    $data['quote_number_padding'] ?? null,
                    $data['quote_number_counter'] ?? null,
                    $data['quote_number_reset_yearly'] ?? null,
                    $data['email_notifications_enabled'] ?? null,
                    $data['email_on_quote_created'] ?? null,
                    $data['email_on_quote_validated'] ?? null,
                    $data['email_on_quote_refused'] ?? null,
                    $data['email_on_quote_expiring'] ?? null,
                    $data['email_expiring_days_before'] ?? null,
                    $data['email_sender_name'] ?? null,
                    $data['email_sender_email'] ?? null,
                    $data['email_reply_to'] ?? null
                ));
                return $this->json(['success' => true, 'message' => 'Quote setting updated successfully']);

            case 'delete':
                $this->get('app.command_bus')->handle(new DeleteQuoteSettingCommand($data['id']));
                return $this->json(['success' => true, 'message' => 'Quote setting deleted successfully']);

            case 'get':
                $settings = $this->get('prestashop.core.query_bus')->handle(new GetQuoteSettingQuery());
                return $this->json([
                    'success' => true,
                    'data' => $settings,
                ]);
        }
    }

    /**
     * Recherche de produits avec données de test
     */
    private function searchProducts($data): JsonResponse
    {
        $legacyContext = $this->get('prestashop.adapter.legacy.context');
        $context = $legacyContext->getContext();

        // Récupérer la langue actuelle
        $langCode = $context->language->iso_code; // Code ISO de la langue (ex: "fr", "en")

        $langRepository = $this->get('prestashop.core.admin.lang.repository');
        $lang = $langRepository->getOneByLocaleOrIsoCode($langCode);
        if (null === $lang) {
            return $this->json([
                'message' => sprintf('Invalid language code "%s".', $langCode),
            ], Response::HTTP_BAD_REQUEST);
        }

        $shopId = $this->get('prestashop.adapter.shop.context')->getContextShopID();
        if (empty($shopId)) {
            $shopId = $this->get('prestashop.adapter.legacy.configuration')->getInt('PS_SHOP_DEFAULT');
        }

        // // Décoder les données JSON du corps de la requête
        // $data = json_decode($request->getContent(), true);

        // Vérifier si les données ont été correctement décodées
        if (!$data) {
            return $this->json([
                'message' => 'Invalid JSON payload.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer les paramètres nécessaires
        $query = $data['query'] ?? '';
        $limit = $data['limit'] ?? 50;


        try {
            $products = $this->get('prestashop.core.query_bus')->handle(new SearchProductsForAssociation(
                $query,
                $lang->getId(),
                (int) $shopId,
                (int) $limit
            ));
        } catch (ProductConstraintException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if (empty($products)) {
            // <-- Ici la correction !
            return $this->json([
                'results' => [],
                'prototype_template' => '' // ou tu peux mettre un template vide
            ]);
        }

        $formatted = [];
        foreach ($products as $product) {
            $productId = $product->getProductId();
            $productObj = new \Product($productId, false, $lang->getId());
            $combinations = $productObj->getAttributeCombinations($lang->getId());
            $combinationImages = $productObj->getCombinationImages($lang->getId());
            if (!empty($combinations)) {
                // Regrouper les attributs par id_product_attribute
                $grouped = [];
                foreach ($combinations as $comb) {
                    $idAttr = $comb['id_product_attribute'];
                    if (!isset($grouped[$idAttr])) {
                        $grouped[$idAttr] = [
                            'groups' => [],
                            'price' => $comb['price'],
                        ];
                    }
                    $grouped[$idAttr]['groups'][] = $comb['group_name'] . ': ' . $comb['attribute_name'];
                }

                foreach ($grouped as $idAttr => $data) {
                    $imageId = null;
                    if (!empty($combinationImages[$idAttr])) {
                        $imageId = $combinationImages[$idAttr][0]['id_image'];
                    }
                    $imageUrl = $imageId
                        ? \Context::getContext()->link->getImageLink($productObj->link_rewrite, $imageId, 'home_default')
                        : $product->getImageUrl();

                    $formatted[] = [
                        'id' => $productId,
                        'id_product_attribute' => $idAttr,
                        'name' => $product->getName() . ' - ' . implode(', ', $data['groups']),
                        'reference' => $productObj->reference ?: 'N/A',
                        'image' => $imageUrl,
                        'price' => $productObj->price,
                    ];
                }
            } else {
                $formatted[] = [
                    'id' => $productId,
                    'id_product_attribute' => null,
                    'name' => $product->getName(),
                    'reference' => $productObj->reference ?: 'N/A',
                    'image' => $product->getImageUrl(),
                    'price' => $productObj->price,
                ];
            }
        }

        return new JsonResponse([
            'success' => true,
            'data' => $formatted
        ]);
    }

    private function handleCustomerAction(string $action, array $data): JsonResponse
    {
        switch ($action) {
            case 'searchCustomer':
                return $this->searchCustomerByEmail($data);
            default:
                return $this->json(['success' => false, 'message' => 'Action inconnue pour Customer'], 400);
        }
    }

    private function searchCustomerByEmail(array $data): JsonResponse
    {
        if (!isset($data['email']) || empty($data['email'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email manquant'
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];

        try {
            $customerId = \Customer::customerExists($email, true, false);

            if (!$customerId) {
                return $this->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            $customer = new \Customer($customerId);

            if (!\Validate::isLoadedObject($customer)) {
                return $this->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => (int) $customer->id,
                    'email' => $customer->email,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'active' => (bool) $customer->active,
                    'date_add' => $customer->date_add,
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche du client: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
