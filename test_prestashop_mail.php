<?php
require_once '/var/www/html/config/config.inc.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Email PrestaShop</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { background: #e8f4f8; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .warning { background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Test Email PrestaShop - Module Devis</h1>

<?php
// Configuration
$shopEmail = Configuration::get('PS_SHOP_EMAIL');
$shopName = Configuration::get('PS_SHOP_NAME');
$mailMethod = Configuration::get('PS_MAIL_METHOD');

echo "<h2>1. Configuration PrestaShop</h2>";
echo "<div class='info'>";
echo "<strong>PS_SHOP_EMAIL:</strong> " . ($shopEmail ?: '<span class="error">NON CONFIGURÉ</span>') . "<br>";
echo "<strong>PS_SHOP_NAME:</strong> " . ($shopName ?: '<span class="error">NON CONFIGURÉ</span>') . "<br>";
echo "<strong>PS_MAIL_METHOD:</strong> " . $mailMethod . " ";

if ($mailMethod == 1) {
    echo "(PHP mail())";
} elseif ($mailMethod == 2) {
    echo "(SMTP)";
    echo "<br><strong>SMTP Server:</strong> " . Configuration::get('PS_MAIL_SERVER');
    echo "<br><strong>SMTP User:</strong> " . Configuration::get('PS_MAIL_USER');
    echo "<br><strong>SMTP Port:</strong> " . Configuration::get('PS_MAIL_SMTP_PORT');
} else {
    echo '<span class="error">(NON CONFIGURÉ)</span>';
}
echo "</div>";

// Vérification des templates
echo "<h2>2. Vérification des templates</h2>";
$modulePath = _PS_MODULE_DIR_ . 'jca_locationdevis/mails/';
$langIso = Language::getIsoById((int)Configuration::get('PS_LANG_DEFAULT'));

echo "<div class='info'>";
echo "<strong>Module path:</strong> $modulePath<br>";
echo "<strong>Langue par défaut:</strong> $langIso<br>";

$templateHtml = $modulePath . $langIso . '/custom.html';
$templateTxt = $modulePath . $langIso . '/custom.txt';

echo "<strong>Template HTML:</strong> " . $templateHtml . " - ";
if (file_exists($templateHtml)) {
    echo '<span class="success">✓ EXISTE</span>';
} else {
    echo '<span class="error">✗ MANQUANT</span>';
}

echo "<br><strong>Template TXT:</strong> " . $templateTxt . " - ";
if (file_exists($templateTxt)) {
    echo '<span class="success">✓ EXISTE</span>';
} else {
    echo '<span class="error">✗ MANQUANT</span>';
}
echo "</div>";

// Test d'envoi
echo "<h2>3. Test d'envoi</h2>";

if (empty($shopEmail)) {
    echo "<div class='warning'>";
    echo "<strong>⚠ IMPOSSIBLE DE TESTER</strong><br>";
    echo "PS_SHOP_EMAIL n'est pas configuré. Allez dans le back-office PrestaShop:<br>";
    echo "Paramètres de la boutique > Contact > Coordonnées de la boutique";
    echo "</div>";
} else {
    $templateVars = [
        '{content}' => '<p><strong>Ceci est un email de test.</strong></p><p>Si vous recevez cet email, la configuration fonctionne !</p>',
        '{shop_name}' => $shopName
    ];

    echo "<div class='info'>";
    echo "<strong>Envoi en cours vers:</strong> jeremy@jcadev.fr<br>";
    echo "<strong>Depuis:</strong> $shopEmail ($shopName)<br>";
    echo "</div>";

    try {
        $result = Mail::Send(
            (int)Configuration::get('PS_LANG_DEFAULT'),
            'custom',
            'Test Email - Module Devis',
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
            echo "<div class='info'>";
            echo '<p class="success">✓ EMAIL ENVOYÉ AVEC SUCCÈS !</p>';
            echo "<p>Vérifiez la boîte mail jeremy@jcadev.fr (et le dossier spam)</p>";
            echo "</div>";
        } else {
            echo "<div class='warning'>";
            echo '<p class="error">✗ ÉCHEC DE L\'ENVOI</p>';
            echo "<p><strong>Diagnostic:</strong></p>";

            if ($mailMethod == 1) {
                echo "<p>Vous utilisez <strong>PHP mail()</strong>. Problèmes possibles:</p>";
                echo "<ul>";
                echo "<li>Le serveur n'a pas sendmail ou un MTA configuré</li>";
                echo "<li>Les emails envoyés par PHP mail() sont souvent bloqués comme spam</li>";
                echo "</ul>";
                echo "<p><strong>SOLUTION RECOMMANDÉE:</strong></p>";
                echo "<ol>";
                echo "<li>Allez dans le back-office PrestaShop</li>";
                echo "<li>Paramètres avancés > Email</li>";
                echo "<li>Sélectionnez 'Définir mes propres paramètres SMTP'</li>";
                echo "<li>Configurez avec Gmail, SendGrid, ou votre hébergeur</li>";
                echo "</ol>";
            } elseif ($mailMethod == 2) {
                echo "<p>Vous utilisez <strong>SMTP</strong>. Problèmes possibles:</p>";
                echo "<ul>";
                echo "<li>Identifiants SMTP incorrects</li>";
                echo "<li>Port ou encryption incorrects</li>";
                echo "<li>Serveur SMTP bloqué par le firewall</li>";
                echo "</ul>";
                echo "<p><strong>SOLUTION:</strong></p>";
                echo "<ol>";
                echo "<li>Vérifiez les identifiants SMTP dans le back-office</li>";
                echo "<li>Utilisez le bouton 'Envoyer un email de test' dans Paramètres avancés > Email</li>";
                echo "<li>Consultez les logs d'erreur PHP</li>";
                echo "</ol>";
            } else {
                echo "<p class='error'>Aucune méthode d'envoi configurée !</p>";
                echo "<p>Allez dans: Paramètres avancés > Email et configurez l'envoi d'emails.</p>";
            }

            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='warning'>";
        echo '<p class="error">✗ EXCEPTION LEVÉE</p>';
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p><strong>Stack trace:</strong></p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
}

echo "<h2>4. Prochaines étapes</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li>Si l'email est envoyé avec succès, le module fonctionne !</li>";
echo "<li>Si l'envoi échoue, suivez les recommandations ci-dessus</li>";
echo "<li>Consultez les logs d'erreur PHP pour plus de détails</li>";
echo "<li>Testez d'abord l'envoi d'email depuis le back-office PrestaShop</li>";
echo "</ol>";
echo "</div>";
?>

</body>
</html>
