<?php
/**
 * Database Test - Post Fix
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $count = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'database' => 'CONNECTED',
        'user' => DB_USER,
        'db_name' => DB_NAME,
        'user_count' => $count['user_count'],
        'message' => 'Database connection WORKS!'
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'user_tried' => DB_USER
    ], JSON_PRETTY_PRINT);
}
