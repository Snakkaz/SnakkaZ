<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=snakqsqe_SnakkaZ;charset=utf8mb4',
        'snakqsqe_roun765',
        'sNAKKAz2025!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $count = $pdo->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'];
    echo json_encode(['success' => true, 'user_count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
