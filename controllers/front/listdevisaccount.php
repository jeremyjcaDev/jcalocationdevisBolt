<?php

class Jca_locationdevisListdevisaccountModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $customer = $this->context->customer;

        if (!$customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication&back=my-account');
        }

        $quotes = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quotes` WHERE `customer_email` = \'' . pSQL($customer->email) . '\''
        );

        $this->context->smarty->assign([
            'quotes' => $quotes,
        ]);

        $this->setTemplate('module:jca_locationdevis/views/templates/front/listdevisaccount.tpl');
    }
}
