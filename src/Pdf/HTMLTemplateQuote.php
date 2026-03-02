<?php

namespace Jca\JcaLocationdevis\Pdf;

use HTMLTemplate;
use Configuration;

class HTMLTemplateQuote extends HTMLTemplate
{
    protected $quote;

    public function __construct($quote)
    {
        // ⚠ Ne jamais toucher à $this->smarty ou $this->context ici
        $this->quote = $quote;
    }

    public function getContent()
    {
        // On récupère Smarty depuis Context ici
        $smarty = \Context::getContext()->smarty;

        // On peut assigner les variables nécessaires pour le template
        $smarty->assign([
            'quote'         => $this->quote,
            'shop_name'     => Configuration::get('PS_SHOP_NAME'),
            'shop_address1' => Configuration::get('PS_SHOP_ADDR1'),
            'shop_address2' => Configuration::get('PS_SHOP_ADDR2'),
            'shop_postcode' => Configuration::get('PS_SHOP_CODE'),
            'shop_city'     => Configuration::get('PS_SHOP_CITY'),
            'shop_phone'    => Configuration::get('PS_SHOP_PHONE'),
            'shop_email'    => Configuration::get('PS_SHOP_EMAIL'),
        ]);

        return $smarty->fetch(_PS_MODULE_DIR_ . 'jca_locationdevis/views/templates/pdf/quote.tpl');
    }

    public function getFilename()
    {
        return 'Devis_' . ($this->quote['quote_number'] ?? $this->quote['id_quote']) . '.pdf';
    }

    public function getBulkFilename()
    {
        return 'quotes.pdf';
    }

    public function getHeader()
    {
        return '';
    }
    public function getFooter()
    {
        return '';
    }
}
