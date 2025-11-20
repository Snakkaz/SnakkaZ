<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$tests = [];

// Test 1: snakqsqe_SnakkaZ with NEW password
try {
    $pdo1 = new PDO(
        "mysql:host=localhost;dbname=snakqsqe_SnakkaZ;charset=utf8mb4",
        "snakqsqe_SnakkaZ",
        "SnakkaZ123!!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo1->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    $tests['user1_new_password'] = [
        'username' => 'snakqsqe_SnakkaZ',
        'password' => 'SnakkaZ123!!',
        'status' => 'SUCCESS ✅',
        'users_count' => $count['count']
    ];
} catch (PDOException $e) {
    $tests['user1_new_password'] = [
        'username' => 'snakqsqe_SnakkaZ',
        'password' => 'SnakkaZ123!!',
        'status' => 'FAILED ❌',
        'error' => $e->getMessage()
    ];
}

// Test 2: snakqsqe_roun765 (should work now with privileges)
try {
    $pdo2 = new PDO(
        "mysql:host=localhost;dbname=snakqsqe_SnakkaZ;charset=utf8mb4",
        "snakqsqe_roun765",
        "sNAKKAz2025!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo2->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    $tests['user2_with_privileges'] = [
        'username' => 'snakqsqe_roun765',
        'password' => 'sNAKKAz2025!',
        'status' => 'SUCCESS ✅',
        'users_count' => $count['count']
    ];
} catch (PDOException $e) {
    $tests['user2_with_privileges'] = [
        'username' => 'snakqsqe_roun765',
        'password' => 'sNAKKAz2025!',
        'status' => 'FAILED ❌',
        'error' => $e->getMessage()
    ];
}

echo json_encode($tests, JSON_PRETTY_PRINT);
