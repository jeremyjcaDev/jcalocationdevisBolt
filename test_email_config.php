<?php
require_once '/var/www/html/config/config.inc.php';

$db = Db::getInstance();
$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_settings` WHERE 1';
$result = $db->getRow($sql);

echo "=== CONFIGURATION EMAIL ===\n";
echo "Résultat brut: " . print_r($result, true) . "\n\n";

if ($result) {
    echo "email_notifications_enabled: " . ($result['email_notifications_enabled'] ?? 'NULL') . "\n";
    echo "email_on_quote_created: " . ($result['email_on_quote_created'] ?? 'NULL') . "\n";
    echo "email_on_quote_validated: " . ($result['email_on_quote_validated'] ?? 'NULL') . "\n";
    echo "email_on_quote_refused: " . ($result['email_on_quote_refused'] ?? 'NULL') . "\n";
    echo "email_sender_name: " . ($result['email_sender_name'] ?? 'NULL') . "\n";
    echo "email_sender_email: " . ($result['email_sender_email'] ?? 'NULL') . "\n";
    echo "email_reply_to: " . ($result['email_reply_to'] ?? 'NULL') . "\n";
} else {
    echo "AUCUN PARAMETRE TROUVE !\n";
}
