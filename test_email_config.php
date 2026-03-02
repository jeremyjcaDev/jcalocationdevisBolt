<?php
require_once '/var/www/html/config/config.inc.php';

header('Content-Type: text/plain; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== TEST CONFIGURATION EMAIL MODULE DEVIS ===\n\n";

echo "1. CONFIGURATION PRESTASHOP\n";
echo "   PS_SHOP_EMAIL: " . Configuration::get('PS_SHOP_EMAIL') . "\n";
echo "   PS_SHOP_NAME: " . Configuration::get('PS_SHOP_NAME') . "\n";
echo "   PS_MAIL_METHOD: " . Configuration::get('PS_MAIL_METHOD') . " (1=php mail, 2=SMTP)\n";
echo "   PS_MAIL_SERVER: " . Configuration::get('PS_MAIL_SERVER') . "\n";
echo "   PS_MAIL_USER: " . Configuration::get('PS_MAIL_USER') . "\n\n";

echo "2. CONFIGURATION MODULE DEVIS\n";
$settings = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'jca_quote_settings WHERE id_quote_settings = 1');
if ($settings) {
    echo "   email_notifications_enabled: " . $settings['email_notifications_enabled'] . "\n";
    echo "   email_on_quote_created: " . $settings['email_on_quote_created'] . "\n";
    echo "   email_sender_email: " . $settings['email_sender_email'] . "\n\n";
}

echo "3. TEMPLATES EMAIL\n";
$modulePath = _PS_MODULE_DIR_ . 'jca_locationdevis/mails/';
echo "   Template FR HTML: " . (file_exists($modulePath . 'fr/custom.html') ? 'OK' : 'MANQUANT') . "\n\n";

echo "4. TEST ENVOI EMAIL\n";
require_once _PS_MODULE_DIR_ . 'jca_locationdevis/src/Service/QuoteEmailService.php';

$quoteData = [
    'quote_number' => 'DEVIS-26-TEST',
    'customer_email' => 'jeremy@jcadev.fr',
    'customer_firstname' => 'Jeremy',
    'customer_lastname' => 'TEST',
    'status' => 'pending',
    'quote_date' => date('Y-m-d H:i:s'),
    'expiry_date' => date('Y-m-d H:i:s', strtotime('+48 hours'))
];

$items = [
    ['product_name' => 'Produit Test', 'quantity' => 2, 'unit_price' => 100.00]
];

try {
    $emailService = new Jca\JcaLocationDevis\Service\QuoteEmailService();
    $result = $emailService->sendQuoteCreatedEmail($quoteData, $items);
    echo "   Résultat: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    if (!empty($result['success'])) {
        echo "   SUCCESS - Email envoyé !\n";
    } else {
        echo "   FAILED - Erreur: " . ($result['error'] ?? 'inconnue') . "\n";
    }
} catch (Exception $e) {
    echo "   EXCEPTION: " . $e->getMessage() . "\n";
}
