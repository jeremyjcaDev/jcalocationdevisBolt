<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== LOGS D'ERREUR PHP ===\n\n";

// Fichiers de log possibles
$logFiles = [
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    '/var/log/php_errors.log',
    '/var/log/php/error.log',
    ini_get('error_log')
];

$found = false;

foreach ($logFiles as $logFile) {
    if (!empty($logFile) && file_exists($logFile) && is_readable($logFile)) {
        echo "Fichier trouvé: $logFile\n";
        echo str_repeat('=', 80) . "\n";

        // Lire les 100 dernières lignes
        $lines = file($logFile);
        $lastLines = array_slice($lines, -100);

        // Filtrer pour ne montrer que les logs EMAIL DEVIS DEBUG
        $filtered = array_filter($lastLines, function($line) {
            return strpos($line, 'EMAIL DEVIS DEBUG') !== false ||
                   strpos($line, 'Mail::Send') !== false ||
                   strpos($line, 'Template') !== false ||
                   strpos($line, 'Mail method') !== false;
        });

        if (count($filtered) > 0) {
            echo "Logs relatifs aux emails:\n\n";
            echo implode('', $filtered);
            $found = true;
        } else {
            echo "Aucun log d'email trouvé dans les 100 dernières lignes\n\n";
        }

        echo "\n" . str_repeat('=', 80) . "\n\n";
    }
}

if (!$found) {
    echo "Aucun fichier de log accessible trouvé.\n\n";
    echo "Essayez de vérifier manuellement:\n";
    echo "- /var/log/apache2/error.log\n";
    echo "- /var/log/nginx/error.log\n";
    echo "- Le fichier configuré dans php.ini: " . ini_get('error_log') . "\n";
}

echo "\n=== CONFIGURATION PHP ERROR LOG ===\n";
echo "error_log (php.ini): " . ini_get('error_log') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "log_errors: " . ini_get('log_errors') . "\n";
