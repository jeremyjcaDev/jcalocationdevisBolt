<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== RECHERCHE DES LOGS PHP ===\n\n";

$logFiles = [
    '/var/log/apache2/error.log',
    '/var/log/php-fpm/error.log',
    '/var/log/php_errors.log',
    '/var/www/html/var/logs/error_log.txt',
    '/tmp/php_errors.log',
    ini_get('error_log')
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile) && is_readable($logFile)) {
        echo "=== FICHIER: $logFile ===\n";
        echo "Dernières 100 lignes:\n";
        echo shell_exec("tail -100 " . escapeshellarg($logFile));
        echo "\n\n";
    }
}

echo "=== INFO PHP ===\n";
echo "error_log configuré: " . ini_get('error_log') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "log_errors: " . ini_get('log_errors') . "\n";
