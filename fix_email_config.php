<?php
require_once '/var/www/html/config/config.inc.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== CONFIGURATION AUTOMATIQUE EMAIL PRESTASHOP ===\n\n";

// Vérifier la configuration actuelle
$currentMethod = Configuration::get('PS_MAIL_METHOD');
echo "Configuration actuelle:\n";
echo "PS_MAIL_METHOD: " . $currentMethod . " (1=PHP mail, 2=SMTP)\n\n";

// Si aucune méthode n'est configurée, on configure PHP mail par défaut
if (empty($currentMethod)) {
    echo "Aucune méthode configurée. Configuration de PHP mail()...\n";
    Configuration::updateValue('PS_MAIL_METHOD', 1);
    echo "✓ PS_MAIL_METHOD configuré à 1 (PHP mail)\n\n";
}

// Vérifier l'email de la boutique
$shopEmail = Configuration::get('PS_SHOP_EMAIL');
if (empty($shopEmail)) {
    echo "✗ ATTENTION: PS_SHOP_EMAIL n'est pas configuré\n";
    echo "  Veuillez le configurer dans le back-office PrestaShop\n";
    echo "  Paramètres de la boutique > Contact > Coordonnées de la boutique\n\n";
} else {
    echo "✓ PS_SHOP_EMAIL: $shopEmail\n\n";
}

// Vérifier le nom de la boutique
$shopName = Configuration::get('PS_SHOP_NAME');
if (empty($shopName)) {
    echo "✗ ATTENTION: PS_SHOP_NAME n'est pas configuré\n";
} else {
    echo "✓ PS_SHOP_NAME: $shopName\n\n";
}

// Configurer le type d'email (HTML + Texte)
Configuration::updateValue('PS_MAIL_TYPE', 3);
echo "✓ PS_MAIL_TYPE configuré à 3 (HTML + Texte)\n\n";

// Vérifier les templates
echo "Vérification des templates:\n";
$modulePath = _PS_MODULE_DIR_ . 'jca_locationdevis/mails/';
$templatesOk = true;

$requiredTemplates = [
    'fr/custom.html',
    'fr/custom.txt',
    'en/custom.html',
    'en/custom.txt'
];

foreach ($requiredTemplates as $template) {
    $path = $modulePath . $template;
    if (file_exists($path)) {
        echo "✓ $template existe\n";
    } else {
        echo "✗ $template MANQUANT\n";
        $templatesOk = false;
    }
}

echo "\n";

if ($templatesOk) {
    echo "✓ Tous les templates sont présents\n\n";
} else {
    echo "✗ Certains templates sont manquants\n\n";
}

// Test d'envoi
echo "=== TEST D'ENVOI ===\n";

if (empty($shopEmail)) {
    echo "✗ Impossible de tester: PS_SHOP_EMAIL n'est pas configuré\n";
    echo "  Configurez-le dans le back-office puis réessayez\n";
} else {
    $templateVars = [
        '{content}' => '<p>Ceci est un email de test automatique.</p><p>Si vous recevez cet email, la configuration fonctionne correctement.</p>',
        '{shop_name}' => $shopName
    ];

    try {
        $result = Mail::Send(
            (int)Configuration::get('PS_LANG_DEFAULT'),
            'custom',
            'Test configuration email - Module Devis',
            $templateVars,
            'jeremy@jcadev.fr',
            null,
            $shopEmail,
            $shopName,
            null,
            null,
            $modulePath,
            false,
            null
        );

        if ($result) {
            echo "✓ Email de test envoyé avec succès à jeremy@jcadev.fr\n";
            echo "  Vérifiez votre boîte mail (et les spams)\n";
        } else {
            echo "✗ Échec de l'envoi de l'email\n\n";
            echo "DIAGNOSTIC:\n";

            if (Configuration::get('PS_MAIL_METHOD') == 1) {
                echo "Vous utilisez PHP mail().\n";
                echo "Vérifiez que:\n";
                echo "1. Le serveur a sendmail ou un MTA configuré\n";
                echo "2. Le fichier php.ini contient:\n";
                echo "   sendmail_path = /usr/sbin/sendmail -t -i\n\n";
                echo "SOLUTION RECOMMANDÉE:\n";
                echo "Configurez SMTP dans PrestaShop:\n";
                echo "1. Allez dans BO > Paramètres avancés > Email\n";
                echo "2. Choisissez 'Définir mes propres paramètres SMTP'\n";
                echo "3. Configurez avec un serveur SMTP (Gmail, SendGrid, etc.)\n";
            } else {
                echo "Vous utilisez SMTP.\n";
                echo "Vérifiez:\n";
                echo "1. Les identifiants SMTP dans BO > Paramètres avancés > Email\n";
                echo "2. Que le serveur SMTP est accessible depuis votre serveur\n";
                echo "3. Utilisez le bouton 'Envoyer un email de test' du back-office\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
    }
}

echo "\n=== FIN ===\n";
