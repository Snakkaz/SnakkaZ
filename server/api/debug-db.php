<?php
/**
 * Show actual Database error
 */

header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== DATABASE CONNECTION DEBUG ===\n\n";

// Step 1: Check config
$configPath = __DIR__ . '/../../config/database.php';
echo "1. Config file: " . (file_exists($configPath) ? "✅ EXISTS" : "❌ NOT FOUND") . "\n";
echo "   Path: $configPath\n\n";

if (!file_exists($configPath)) {
    die("STOP: Config file not found\n");
}

// Step 2: Load config
require_once $configPath;
echo "2. Config loaded ✅\n";
echo "   DB_HOST: " . DB_HOST . "\n";
echo "   DB_NAME: " . DB_NAME . "\n";
echo "   DB_USER: " . DB_USER . "\n";
echo "   DB_PASS: " . substr(DB_PASS, 0, 4) . "..." . substr(DB_PASS, -4) . "\n\n";

// Step 3: Check PDO
echo "3. PDO Check:\n";
echo "   extension_loaded('pdo'): " . (extension_loaded('pdo') ? "✅ YES" : "❌ NO") . "\n";
echo "   extension_loaded('pdo_mysql'): " . (extension_loaded('pdo_mysql') ? "✅ YES" : "❌ NO") . "\n";

if (extension_loaded('pdo')) {
    echo "   Available drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
}
echo "\n";

// Step 4: Try connection
echo "4. Connection attempt:\n";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    echo "   DSN: $dsn\n";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "   ✅ CONNECTION SUCCESSFUL!\n";
    echo "   Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Test query
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM users")->fetch();
    echo "   Users in database: " . $result['cnt'] . "\n";
    
} catch (PDOException $e) {
    echo "   ❌ CONNECTION FAILED!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n=== END DEBUG ===\n";
