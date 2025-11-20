<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== DB DEBUG " . date('H:i:s') . " ===\n\n";

require_once __DIR__ . '/../../config/database.php';

echo "Config loaded:\n";
echo "Host: " . DB_HOST . "\n";
echo "Name: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n\n";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    echo "✅ CONNECTION SUCCESS!\n";
    echo "Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
} catch (PDOException $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
}
