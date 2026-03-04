<?php
require_once '/var/www/html/config/config.inc.php';
require_once __DIR__ . '/vendor/autoload.php';

use Jca\JcaLocationdevis\Service\QuoteEmailService;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Email Statut Devis</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { background: #e8f4f8; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Test Email Statut Devis</h1>

<?php
// Récupérer le dernier devis
$db = Db::getInstance();
$quote = $db->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote` ORDER BY id_quote DESC LIMIT 1');

if (!$quote) {
    echo '<p class="error">Aucun devis trouvé dans la base de données.</p>';
    exit;
}

$items = $db->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_item` WHERE id_quote = ' . (int)$quote['id_quote']);

echo "<div class='info'>";
echo "<h2>Devis trouvé</h2>";
echo "<strong>ID:</strong> " . $quote['id_quote'] . "<br>";
echo "<strong>Numéro:</strong> " . $quote['quote_number'] . "<br>";
echo "<strong>Client:</strong> " . $quote['customer_firstname'] . ' ' . $quote['customer_lastname'] . "<br>";
echo "<strong>Email:</strong> " . $quote['customer_email'] . "<br>";
echo "<strong>Statut actuel:</strong> " . $quote['status'] . "<br>";
echo "<strong>Nombre d'articles:</strong> " . count($items) . "<br>";
echo "</div>";

// Test email validation
echo "<h2>Test Email VALIDATION</h2>";
$emailService = new QuoteEmailService();
$resultValidated = $emailService->sendQuoteStatusEmail($quote, $items, 'validated');

echo "<div class='info'>";
echo "<strong>Résultat:</strong><br>";
echo "<pre>" . print_r($resultValidated, true) . "</pre>";
echo "</div>";

// Test email refus
echo "<h2>Test Email REFUS</h2>";
$resultRefused = $emailService->sendQuoteStatusEmail($quote, $items, 'refused');

echo "<div class='info'>";
echo "<strong>Résultat:</strong><br>";
echo "<pre>" . print_r($resultRefused, true) . "</pre>";
echo "</div>";

echo "<h2>Conclusion</h2>";
echo "<div class='info'>";
if ($resultValidated['success'] && $resultRefused['success']) {
    echo '<p class="success">✓ Les deux emails ont été envoyés avec succès !</p>';
    echo '<p>Vérifiez la boîte mail: ' . $quote['customer_email'] . '</p>';
} else {
    echo '<p class="error">✗ Échec de l\'envoi</p>';
    echo '<p>Vérifiez la configuration email dans PrestaShop (Paramètres avancés > Email)</p>';
}
echo "</div>";
?>

</body>
</html>
