<?php
/**
 * Direct Database Connection Test
 * Tests database connection with detailed error reporting
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'steps' => []
];

// Step 1: Check config file path
$configPath = __DIR__ . '/../../config/database.php';
$result['steps']['config_path'] = [
    'path' => $configPath,
    'exists' => file_exists($configPath),
    'realpath' => realpath($configPath)
];

if (!file_exists($configPath)) {
    $result['error'] = 'Config file not found';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Step 2: Load config
try {
    require_once $configPath;
    $result['steps']['config_loaded'] = [
        'success' => true,
        'db_host' => DB_HOST ?? 'NOT DEFINED',
        'db_name' => DB_NAME ?? 'NOT DEFINED',
        'db_user' => DB_USER ?? 'NOT DEFINED',
        'db_pass_length' => isset(DB_PASS) ? strlen(DB_PASS) : 0
    ];
} catch (Exception $e) {
    $result['error'] = 'Failed to load config: ' . $e->getMessage();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Step 3: Check PDO availability
$result['steps']['pdo_check'] = [
    'pdo_loaded' => extension_loaded('pdo'),
    'pdo_mysql_loaded' => extension_loaded('pdo_mysql'),
    'available_drivers' => extension_loaded('pdo') ? PDO::getAvailableDrivers() : []
];

if (!extension_loaded('pdo_mysql')) {
    $result['error'] = 'PDO MySQL extension not loaded';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Step 4: Attempt connection with detailed error
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $result['steps']['connection_attempt'] = [
        'dsn' => $dsn,
        'user' => DB_USER
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $result['steps']['connection_success'] = [
        'success' => true,
        'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)
    ];
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    
    $result['steps']['test_query'] = [
        'success' => true,
        'user_count' => $count['count']
    ];
    
    $result['overall_status'] = 'SUCCESS ✅';
    
} catch (PDOException $e) {
    $result['error'] = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    $result['overall_status'] = 'FAILED ❌';
}

echo json_encode($result, JSON_PRETTY_PRINT);
