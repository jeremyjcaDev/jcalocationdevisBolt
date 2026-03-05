<?php

require_once __DIR__ . '/config/config.inc.php';

$db = Db::getInstance();

// Test 1: Vérifier la structure de la table quotes
echo "=== Structure de ps_jca_quotes ===\n";
$columns = $db->executeS("SHOW COLUMNS FROM " . _DB_PREFIX_ . "jca_quotes");
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n=== Structure de ps_jca_quote_items ===\n";
$columns = $db->executeS("SHOW COLUMNS FROM " . _DB_PREFIX_ . "jca_quote_items");
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

// Test 2: Tester l'insertion d'un devis
echo "\n=== Test d'insertion d'un devis ===\n";

$insertQuote = [
    'quote_number' => 'TEST-001',
    'quote_type' => 'standard',
    'customer_name' => 'Test User',
    'customer_email' => 'test@example.com',
    'customer_phone' => '0123456789',
    'status' => 'pending',
    'valid_until' => date('Y-m-d H:i:s', strtotime('+48 hours')),
    'date_add' => date('Y-m-d H:i:s'),
    'date_upd' => date('Y-m-d H:i:s')
];

try {
    $result = $db->insert('jca_quotes', $insertQuote);
    if ($result) {
        $idQuote = (int)$db->Insert_ID();
        echo "✓ Quote créé avec succès! ID: $idQuote\n";

        // Test 3: Tester l'insertion d'un item
        echo "\n=== Test d'insertion d'un item ===\n";

        $insertItem = [
            'id_quote' => $idQuote,
            'id_product' => 1,
            'product_name' => 'Produit Test',
            'product_reference' => 'REF-001',
            'quantity' => 1,
            'price' => 100.00,
            'original_price' => 100.00,
            'is_rental' => 0,
            'duration_months' => null,
            'rate_percentage' => null,
            'id_rental_configuration' => null,
            'date_add' => date('Y-m-d H:i:s')
        ];

        $result = $db->insert('jca_quote_items', $insertItem);
        if ($result) {
            echo "✓ Item créé avec succès!\n";
        } else {
            echo "✗ Erreur lors de l'insertion de l'item\n";
            echo "Erreur SQL: " . Db::getInstance()->getMsgError() . "\n";
        }

        // Nettoyage
        $db->delete('jca_quotes', 'id_quote = ' . $idQuote);
        echo "\n✓ Test terminé et nettoyé\n";
    } else {
        echo "✗ Erreur lors de l'insertion du devis\n";
        echo "Erreur SQL: " . Db::getInstance()->getMsgError() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}
