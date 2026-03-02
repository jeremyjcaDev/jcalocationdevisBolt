<?php

class Jca_locationdevisCreatedevisModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $customer = $this->context->customer;
        $cart = $this->context->cart;
        $products = $cart->getProducts();
        $date = [];
        // Vérification si l'utilisateur est connecté
        if (!$customer->isLogged()) {
            $this->context->smarty->assign([
                'login_url' => $this->context->link->getPageLink('authentication', true),
                'cart_url'  => $this->context->link->getPageLink('cart', true),
            ]);

            $this->setTemplate('module:jca_locationdevis/views/templates/front/must_login.tpl');
            return;
        }

        // Vérification si le panier est vide
        if (empty($products)) {
            $this->context->smarty->assign([
                'cart_url' => $this->context->link->getPageLink('cart', true),
            ]);

            $this->setTemplate('module:jca_locationdevis/views/templates/front/empty_cart.tpl');
            return;
        }
        $settings = $this->getQuoteSettings();
        $date['created_at'] = date('d/m/Y');
        $date['valid_until'] = date('d/m/Y', strtotime("+{$settings['validity_hours']} hours"));
        $deliveryOptions = $cart->getDeliveryOptionList();
        // Sinon, tout est ok → afficher le devis
        $this->context->smarty->assign([
            'products' => $products,
            'delivery_options' => $deliveryOptions,
            'date' => $date,
            'total_ht' => $cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING),
        ]);

        $this->setTemplate('module:jca_locationdevis/views/templates/front/createdevis.tpl');
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
