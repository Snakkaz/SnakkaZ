<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$result = ['timestamp' => date('Y-m-d H:i:s')];

// Load config
$configPath = __DIR__ . '/../../config/database.php';
$result['config_path'] = $configPath;
$result['config_exists'] = file_exists($configPath);

if (!file_exists($configPath)) {
    echo json_encode($result);
    exit;
}

require_once $configPath;

$result['db_host'] = DB_HOST;
$result['db_name'] = DB_NAME;
$result['db_user'] = DB_USER;
$result['pdo_mysql'] = extension_loaded('pdo_mysql');

if (!extension_loaded('pdo_mysql')) {
    echo json_encode($result);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $result['status'] = 'SUCCESS';
    $result['version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $result['user_count'] = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $result['status'] = 'FAILED';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
