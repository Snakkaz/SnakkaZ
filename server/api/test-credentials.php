<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain');

echo "=== TESTING DATABASE CREDENTIALS ===\n\n";

$configs = [
    'Config 1 (phpMyAdmin user)' => [
        'host' => 'localhost',
        'db' => 'snakqsqe_SnakkaZ',
        'user' => 'cpses_sn151brm8f',
        'pass' => 'C1vTRVmuczB1HgiiFPC02aUI6RkwVCLq'
    ],
    'Config 2 (snakkaz_user)' => [
        'host' => 'localhost',
        'db' => 'snakqsqe_SnakkaZ',
        'user' => 'snakqsqe_snakkaz_user',
        'pass' => 'SnakkaZ2024!Secure'
    ]
];

foreach ($configs as $name => $config) {
    echo "Testing: $name\n";
    echo "User: {$config['user']}\n";
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']}",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "✅ CONNECTION SUCCESS!\n";
        echo "Server: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        echo "Users: $count\n";
        
    } catch (PDOException $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== END TEST ===\n";
