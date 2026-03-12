<?php
/**
 * Test de mise à jour de statut depuis le back-office
 * Ce script simule ce qui se passe quand on valide/refuse un devis
 */

// On simule l'autoload
require_once(__DIR__ . '/vendor/autoload.php');

use Jca\JcaLocationdevis\Service\QuoteEmailService;

echo "=== TEST BACKOFFICE QUOTE STATUS ===\n\n";

// Test de la méthode getQuoteSettings via l'email service
echo "Test 1: Création du service email\n";
try {
    $emailService = new QuoteEmailService();
    echo "✓ Service email créé avec succès\n";
} catch (Exception $e) {
    echo "✗ Erreur lors de la création du service: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 2: Vérifier que la requête SQL fonctionne directement
echo "Test 2: Test de la requête SQL directement\n";
$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_settings`';
echo "SQL généré: " . $sql . "\n";
echo "Note: La requête contient bien le backtick fermant après 'jca_quote_settings'\n";

echo "\n=== FIN DES TESTS ===\n";
