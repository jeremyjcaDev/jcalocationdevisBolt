<?php
// Test de la requête getQuoteSettings
require_once(__DIR__ . '/config/config.inc.php');

echo "=== TEST QUOTE SETTINGS QUERY ===\n\n";

$db = Db::getInstance();

// Test 1: Requête simple
echo "Test 1: Requête simple\n";
$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_settings`';
echo "SQL: " . $sql . "\n";
$result = $db->getRow($sql);
if ($result) {
    echo "✓ Résultat trouvé: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "✗ Aucun résultat\n";
    echo "Erreur: " . $db->getMsgError() . "\n";
}

echo "\n";

// Test 2: Vérifier que la table existe
echo "Test 2: Vérifier que la table existe\n";
$sql = 'SHOW TABLES LIKE "' . _DB_PREFIX_ . 'jca_quote_settings"';
echo "SQL: " . $sql . "\n";
$tableExists = $db->getValue($sql);
if ($tableExists) {
    echo "✓ Table existe\n";
} else {
    echo "✗ Table n'existe pas\n";
}

echo "\n";

// Test 3: Compter les lignes
echo "Test 3: Compter les lignes\n";
$sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'jca_quote_settings`';
echo "SQL: " . $sql . "\n";
$count = $db->getValue($sql);
echo "Nombre de lignes: " . $count . "\n";

echo "\n=== FIN DES TESTS ===\n";
