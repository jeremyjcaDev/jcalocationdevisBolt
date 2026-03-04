<?php
/**
 * Script to manually install jca_locationdevis module database tables
 */

require_once dirname(__FILE__) . '/../../config/config.inc.php';

// Read and execute the install.sql file
$installSqlPath = dirname(__FILE__) . '/src/Install/install.sql';

if (!file_exists($installSqlPath)) {
    die("Error: install.sql file not found at: $installSqlPath\n");
}

$sql = file_get_contents($installSqlPath);

// Replace placeholders
$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
$sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

// Get database collation
$allowedCollations = ['utf8mb4_general_ci', 'utf8mb4_unicode_ci'];
$databaseCollation = Db::getInstance()->getValue('SELECT @@collation_database');
$collation = (empty($databaseCollation) || !in_array($databaseCollation, $allowedCollations)) ? '' : 'COLLATE ' . $databaseCollation;
$sql = str_replace('COLLATION', $collation, $sql);

// Split SQL statements
$statements = array_filter(
    array_map('trim', preg_split('/;[\s]*$/m', $sql)),
    function($statement) {
        return !empty($statement) && !preg_match('/^--/', $statement);
    }
);

echo "Starting installation of jca_locationdevis tables...\n\n";

$success = true;
foreach ($statements as $statement) {
    if (empty($statement)) {
        continue;
    }

    // Extract table name for display
    if (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
        $tableName = $matches[1];
        echo "Creating table: $tableName... ";
    } elseif (preg_match('/INSERT INTO.*?`([^`]+)`/i', $statement, $matches)) {
        $tableName = $matches[1];
        echo "Inserting default data into: $tableName... ";
    } else {
        echo "Executing statement... ";
    }

    try {
        $result = Db::getInstance()->execute($statement);
        if ($result) {
            echo "✓ OK\n";
        } else {
            echo "✗ FAILED\n";
            echo "Error: " . Db::getInstance()->getMsgError() . "\n";
            $success = false;
        }
    } catch (Exception $e) {
        echo "✗ EXCEPTION\n";
        echo "Error: " . $e->getMessage() . "\n";
        $success = false;
    }
}

echo "\n";
if ($success) {
    echo "✓ Installation completed successfully!\n";
} else {
    echo "✗ Installation completed with errors.\n";
}

// Verify tables exist
echo "\nVerifying tables:\n";
$tables = [
    _DB_PREFIX_ . 'jca_rental_configurations',
    _DB_PREFIX_ . 'jca_quote_settings',
    _DB_PREFIX_ . 'jca_quotes',
    _DB_PREFIX_ . 'jca_quote_items',
    _DB_PREFIX_ . 'jca_product_rental_availability',
    _DB_PREFIX_ . 'jca_quote_customers'
];

foreach ($tables as $table) {
    $exists = Db::getInstance()->executeS("SHOW TABLES LIKE '$table'");
    if ($exists) {
        echo "  ✓ $table exists\n";
    } else {
        echo "  ✗ $table NOT FOUND\n";
    }
}
