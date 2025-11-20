<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load new config
require_once __DIR__ . '/../config/db-config.php';

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'config_file' => 'db-config.php',
    'db_user_defined' => defined('DB_USER') ? DB_USER : 'NOT DEFINED',
    'db_name' => defined('DB_NAME') ? DB_NAME : 'NOT DEFINED'
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    
    $result['status'] = 'SUCCESS';
    $result['users_count'] = $count['count'];
    
} catch (PDOException $e) {
    $result['status'] = 'FAILED';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
