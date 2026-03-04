<?php
require_once '/var/www/html/config/config.inc.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTIC COMPLET EMAIL PRESTASHOP ===\n\n";

// 1. Configuration PrestaShop
echo "1. CONFIGURATION PRESTASHOP\n";
echo "   PS_SHOP_EMAIL: " . Configuration::get('PS_SHOP_EMAIL') . "\n";
echo "   PS_SHOP_NAME: " . Configuration::get('PS_SHOP_NAME') . "\n";
echo "   PS_MAIL_METHOD: " . Configuration::get('PS_MAIL_METHOD') . " (1=PHP mail, 2=SMTP)\n";
echo "   PS_MAIL_SERVER: " . Configuration::get('PS_MAIL_SERVER') . "\n";
echo "   PS_MAIL_USER: " . Configuration::get('PS_MAIL_USER') . "\n";
echo "   PS_MAIL_SMTP_ENCRYPTION: " . Configuration::get('PS_MAIL_SMTP_ENCRYPTION') . "\n";
echo "   PS_MAIL_SMTP_PORT: " . Configuration::get('PS_MAIL_SMTP_PORT') . "\n";
echo "   PS_MAIL_TYPE: " . Configuration::get('PS_MAIL_TYPE') . " (1=HTML, 2=Texte, 3=Les deux)\n\n";

// 2. Vérification des templates
echo "2. VERIFICATION DES TEMPLATES\n";
$modulePath = _PS_MODULE_DIR_ . 'jca_locationdevis/mails/';
echo "   Module path: $modulePath\n";
echo "   Dossier existe: " . (is_dir($modulePath) ? 'OUI' : 'NON') . "\n";
echo "   Dossier fr/ existe: " . (is_dir($modulePath . 'fr') ? 'OUI' : 'NON') . "\n";
echo "   Dossier en/ existe: " . (is_dir($modulePath . 'en') ? 'OUI' : 'NON') . "\n";
echo "   Template fr/custom.html existe: " . (file_exists($modulePath . 'fr/custom.html') ? 'OUI' : 'NON') . "\n";
echo "   Template fr/custom.txt existe: " . (file_exists($modulePath . 'fr/custom.txt') ? 'OUI' : 'NON') . "\n";
echo "   Template en/custom.html existe: " . (file_exists($modulePath . 'en/custom.html') ? 'OUI' : 'NON') . "\n";
echo "   Template en/custom.txt existe: " . (file_exists($modulePath . 'en/custom.txt') ? 'OUI' : 'NON') . "\n\n";

// 3. Vérification PHP mail
echo "3. VERIFICATION PHP MAIL\n";
echo "   sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "   SMTP (php.ini): " . ini_get('SMTP') . "\n";
echo "   smtp_port (php.ini): " . ini_get('smtp_port') . "\n\n";

// 4. Test avec php mail() directement
echo "4. TEST AVEC PHP mail() DIRECTEMENT\n";
$headers = "From: " . Configuration::get('PS_SHOP_EMAIL') . "\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$phpMailResult = mail(
    'jeremy@jcadev.fr',
    'Test PHP mail() direct',
    '<p>Test email envoyé directement avec php mail()</p>',
    $headers
);
echo "   Résultat php mail(): " . ($phpMailResult ? 'SUCCESS' : 'FAILED') . "\n\n";

// 5. Test avec PrestaShop Mail::Send
echo "5. TEST AVEC PRESTASHOP Mail::Send\n";

$templateVars = [
    '{content}' => '<p>Test email envoyé via PrestaShop Mail::Send</p>',
    '{shop_name}' => Configuration::get('PS_SHOP_NAME')
];

// Activer le mode debug
@ini_set('display_errors', 1);
@error_reporting(E_ALL);

try {
    echo "   Envoi en cours...\n";

    $result = Mail::Send(
        (int)Configuration::get('PS_LANG_DEFAULT'),
        'custom',
        'Test PrestaShop Mail::Send',
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

    echo "   Résultat Mail::Send: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";

    if (!$result) {
        echo "\n   DIAGNOSTIC:\n";
        echo "   - Si PS_MAIL_METHOD = 2 (SMTP): Vérifiez les identifiants SMTP\n";
        echo "   - Si PS_MAIL_METHOD = 1 (PHP mail): Vérifiez que sendmail est configuré\n";
        echo "   - Vérifiez les logs d'erreur PHP\n";
    }
} catch (Exception $e) {
    echo "   ✗ EXCEPTION: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo "   " . str_replace("\n", "\n   ", $e->getTraceAsString()) . "\n";
}

echo "\n6. RECOMMANDATIONS\n";
if (Configuration::get('PS_MAIL_METHOD') == 2) {
    echo "   Vous utilisez SMTP. Pour déboguer:\n";
    echo "   1. Connectez-vous au back-office PrestaShop\n";
    echo "   2. Allez dans Paramètres avancés > Email\n";
    echo "   3. Testez la configuration SMTP avec le bouton 'Envoyer un email de test'\n";
} else {
    echo "   Vous utilisez PHP mail(). Pour que ça marche:\n";
    echo "   1. Le serveur doit avoir sendmail ou un MTA installé\n";
    echo "   2. Ou configurez SMTP dans PrestaShop (plus fiable)\n";
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";
