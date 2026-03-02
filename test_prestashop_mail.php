<?php
require_once '/var/www/html/config/config.inc.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST ENVOI EMAIL PRESTASHOP ===\n\n";

// Configuration mail
echo "Configuration actuelle:\n";
echo "PS_SHOP_EMAIL: " . Configuration::get('PS_SHOP_EMAIL') . "\n";
echo "PS_SHOP_NAME: " . Configuration::get('PS_SHOP_NAME') . "\n";
echo "PS_MAIL_METHOD: " . Configuration::get('PS_MAIL_METHOD') . "\n";
echo "PS_MAIL_SERVER: " . Configuration::get('PS_MAIL_SERVER') . "\n";
echo "PS_MAIL_USER: " . Configuration::get('PS_MAIL_USER') . "\n";
echo "PS_MAIL_SMTP_ENCRYPTION: " . Configuration::get('PS_MAIL_SMTP_ENCRYPTION') . "\n";
echo "PS_MAIL_SMTP_PORT: " . Configuration::get('PS_MAIL_SMTP_PORT') . "\n\n";

// Test d'envoi
echo "Test d'envoi d'email...\n";

$templateVars = [
    '{content}' => '<p>Ceci est un email de test.</p>',
    '{shop_name}' => Configuration::get('PS_SHOP_NAME')
];

$modulePath = _PS_MODULE_DIR_ . 'jca_locationdevis/mails/';
echo "Module path: $modulePath\n";
echo "Template FR existe: " . (file_exists($modulePath . 'fr/custom.html') ? 'OUI' : 'NON') . "\n\n";

try {
    $result = Mail::Send(
        (int)Configuration::get('PS_LANG_DEFAULT'),
        'custom',
        'Test email module devis',
        $templateVars,
        'jeremy@jcadev.fr',
        null,
        Configuration::get('PS_SHOP_EMAIL'),
        Configuration::get('PS_SHOP_NAME'),
        null,
        null,
        $modulePath,
        false,
        null
    );

    if ($result) {
        echo "✓ Email envoyé avec succès !\n";
    } else {
        echo "✗ Echec d'envoi (Mail::Send a retourné false)\n";
        echo "\nVérifiez:\n";
        echo "1. Configuration SMTP dans BO > Paramètres avancés > Email\n";
        echo "2. Les logs d'erreur PHP\n";
        echo "3. Que le serveur SMTP est accessible\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
