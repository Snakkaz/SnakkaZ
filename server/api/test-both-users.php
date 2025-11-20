<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$tests = [];

// Test 1: snakqsqe_SnakkaZ
try {
    $pdo1 = new PDO(
        "mysql:host=localhost;dbname=snakqsqe_SnakkaZ;charset=utf8mb4",
        "snakqsqe_SnakkaZ",
        "Snakkaz123!!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo1->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    $tests['user1'] = [
        'username' => 'snakqsqe_SnakkaZ',
        'status' => 'SUCCESS ✅',
        'users_count' => $count['count']
    ];
} catch (PDOException $e) {
    $tests['user1'] = [
        'username' => 'snakqsqe_SnakkaZ',
        'status' => 'FAILED ❌',
        'error' => $e->getMessage()
    ];
}

// Test 2: snakqsqe_roun765
try {
    $pdo2 = new PDO(
        "mysql:host=localhost;dbname=snakqsqe_SnakkaZ;charset=utf8mb4",
        "snakqsqe_roun765",
        "sNAKKAz2025!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo2->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    $tests['user2'] = [
        'username' => 'snakqsqe_roun765',
        'status' => 'SUCCESS ✅',
        'users_count' => $count['count']
    ];
} catch (PDOException $e) {
    $tests['user2'] = [
        'username' => 'snakqsqe_roun765',
        'status' => 'FAILED ❌',
        'error' => $e->getMessage()
    ];
}

echo json_encode($tests, JSON_PRETTY_PRINT);
