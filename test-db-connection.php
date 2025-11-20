<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Test 1: Check if config file exists
$config_path = __DIR__ . '/config/database.php';
if (!file_exists($config_path)) {
    echo json_encode(['error' => 'Config file not found', 'path' => $config_path]);
    exit;
}

require_once $config_path;

// Test 2: Show config values (without password)
echo json_encode([
    'config_loaded' => true,
    'DB_HOST' => DB_HOST,
    'DB_NAME' => DB_NAME,
    'DB_USER' => DB_USER,
    'DB_PASS_LENGTH' => strlen(DB_PASS),
    'DB_CHARSET' => DB_CHARSET
]);

// Test 3: Try to connect
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "\n\nConnection successful!";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nUsers count: " . $result['count'];
    
} catch (PDOException $e) {
    echo "\n\nConnection failed: " . $e->getMessage();
}
