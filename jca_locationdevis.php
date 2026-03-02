<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use Jca\JcaLocationdevis\Install\Installer;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use Jca\JcaLocationDevis\Repository\ProductRentalAvailabilityRepository;


class Jca_LocationDevis extends Module implements WidgetInterface
{

    protected $config_form = false;
    protected $supabase;

    public function __construct()
    {
        $this->name = 'jca_locationdevis';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.0';
        $this->author = 'Jcadev';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('JCA Location Devis');
        $this->description = $this->l('Module de gestion des offres de location avec calcul automatique des mensualités et de devis');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->ps_versions_compliancy = ['min' => '8.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        $installer = new Installer();
        if (!$installer->install($this)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        $installer = new Installer();
        if (!$installer->uninstall($this)) {
            return false;
        }

        return true;
    }

    /**
     * Hook pour ajouter les assets CSS/JS dans le back office
     */
    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('controller') == 'AdminJcaLocationdevis') {

            $this->context->controller->addJS('modules/' . $this->name . '/views/js/app.js');
            $this->context->controller->addJS('modules/' . $this->name . '/views/js/chunk-vendors.js');
            $this->context->controller->addCSS('modules/' . $this->name . '/views/css/style.css');
        }
    }

    public function hookActionFrontControllerSetMedia()
    {

        // $this->context->controller->registerStylesheet(
        //     'module-jca-locationdevis-style',
        //     'modules/' . $this->name . '/views/css/front.css',
        //     ['media' => 'all', 'priority' => 150]
        // );

        // $this->context->controller->registerJavascript(
        //     'module-jca-locationdevis-script',
        //     'modules/' . $this->name . '/views/js/front.js',
        //     ['position' => 'bottom', 'priority' => 150]
        // );

        // $this->context->controller->registerJavascript(
        //     'module-jca-locationdevis-script',
        //     'modules/' . $this->name . '/views/js/product_button.js',
        //     ['position' => 'bottom', 'priority' => 150]
        // );
    }

    public function hookDisplayProductPriceBlock($params) {}

    public function hookDisplayAdminProductsExtra($params) {}
    public function calculateMonthlyPayment($price, $duration) {}

    public function hookDisplayCustomerAccount($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/my_account_link.tpl');
    }

    public function renderWidget($hookName = '', array $configuration = [])
    {

        if ($hookName === 'displayPanierButton') {

            $this->smarty->assign('products', $configuration['cart']->getProducts());
            return $this->display(__FILE__, 'views/templates/hook/panier_button.tpl');
        }
        // $vars = $this->getWidgetVariables($hookName, $configuration);
        $vars = [];
        // Si aucune variable utile → ne rien afficher
        if (empty($vars)) {
            return '';
        };
        if (empty($vars)) {
            return '';
        }
        $this->smarty->assign($vars);
        return $this->display(__FILE__, 'views/templates/hook/product_button.tpl');
    }

    public function getWidgetVariables($hookName = '', array $configuration = [])
    {


        $productId = $this->getProductId($configuration);
        if (!$productId) {
            return [];
        }
        try {
            // Utiliser l'EntityManager directement au lieu du service
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $repository = $entityManager->getRepository('Jca\JcaLocationdevis\Entity\ProductRentalAvailability');
            $rentalInfo = $repository->findOneBy(['idProduct' => (int) $productId]);

            if (!$rentalInfo) {
                return [];
            }

            $repositoryRentalConfiguration = $entityManager->getRepository('Jca\JcaLocationdevis\Entity\RentalConfiguration');
            $rentalConfiguration = $repositoryRentalConfiguration->findRangeByPrice((float) $configuration['product']['price']);
            $rentalConfigurationArray = [
                'id' => $rentalConfiguration->getId(),
                'priceMin' => $rentalConfiguration->getPriceMin(),
                'priceMax' => $rentalConfiguration->getPriceMax(),
                'duration36Months' => $rentalConfiguration->getDuration36Months(),
                'duration60Months' => $rentalConfiguration->getDuration60Months(),
                'sortOrder' => $rentalConfiguration->getSortOrder(),
                'dateAdd' => $rentalConfiguration->getDateAdd()->format('Y-m-d H:i:s'),
                'dateUpd' => $rentalConfiguration->getDateUpd()->format('Y-m-d H:i:s'),
            ];

            return [
                'rentalEnabled' => $rentalInfo->getRentalEnabled(),
                'duration36Enabled' => $rentalInfo->getDuration36Enabled(),
                'duration60Enabled' => $rentalInfo->getDuration60Enabled(),
                'rentalConfiguration' => $rentalConfigurationArray,
            ];
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            PrestaShopLogger::addLog('Error in getWidgetVariables: ' . $e->getMessage(), 3);
            return [];
        }
        // Récupération des consommables liés au produit via le QueryBus
        // $query = new \Jca\JcaLocationdevis\Query\GetProductRentalAvailabilityQuery((int)$productId);
        // var_dump($query);
        // exit;
        // $handler = $this->get('jca_locationdevis.query_handler.get_product_rental_availability_handler');
        // $productRentalAvailability = $handler->handle($query);
        // var_dump($productRentalAvailability);
        // exit;
    }

    private function getProductId(array $configuration): ?int
    {
        return isset($configuration['product']) && $configuration['product']
            ? (int) $configuration['product']->getId()
            : null;
    }
}
