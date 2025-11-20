<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$debug = [];

// Test 1: Basic PHP
$debug['php_version'] = phpversion();
$debug['working_directory'] = getcwd();
$debug['dir_constant'] = __DIR__;

// Test 2: Check if files exist
$debug['files_exist'] = [
    'database.php' => file_exists(__DIR__ . '/../config/database.php'),
    'Database.php' => file_exists(__DIR__ . '/../utils/Database.php'),
    'Response.php' => file_exists(__DIR__ . '/../utils/Response.php'),
    'Auth.php' => file_exists(__DIR__ . '/../utils/Auth.php')
];

// Test 3: Try to require them
try {
    require_once __DIR__ . '/../config/database.php';
    $debug['require_config'] = 'success';
    $debug['db_config'] = [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER
    ];
} catch (Exception $e) {
    $debug['require_config'] = 'error: ' . $e->getMessage();
}

try {
    require_once __DIR__ . '/../utils/Database.php';
    $debug['require_database'] = 'success';
} catch (Exception $e) {
    $debug['require_database'] = 'error: ' . $e->getMessage();
}

try {
    require_once __DIR__ . '/../utils/Response.php';
    $debug['require_response'] = 'success';
} catch (Exception $e) {
    $debug['require_response'] = 'error: ' . $e->getMessage();
}

try {
    require_once __DIR__ . '/../utils/Auth.php';
    $debug['require_auth'] = 'success';
} catch (Exception $e) {
    $debug['require_auth'] = 'error: ' . $e->getMessage();
}

// Test 4: Try to connect to database
try {
    $db = Database::getInstance();
    $debug['db_connection'] = 'success';
    
    // Test query
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['users_count'] = $result['count'];
} catch (Exception $e) {
    $debug['db_connection'] = 'error: ' . $e->getMessage();
}

echo json_encode($debug, JSON_PRETTY_PRINT);
