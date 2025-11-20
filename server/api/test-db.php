<?php
/**
 * Database Connection Test - Diagnostic Tool
 * Visit: https://snakkaz.com/api/test-db.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', '1');

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: Check if config file exists
$result['tests']['config_file'] = [
    'name' => 'Config file exists',
    'passed' => file_exists(__DIR__ . '/../../config/database.php'),
    'path' => realpath(__DIR__ . '/../../config/database.php')
];

// Test 2: Load config
if ($result['tests']['config_file']['passed']) {
    try {
        require_once __DIR__ . '/../../config/database.php';
        $result['tests']['config_loaded'] = [
            'name' => 'Config loaded',
            'passed' => true,
            'constants' => [
                'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'NOT DEFINED',
                'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'NOT DEFINED',
                'DB_USER' => defined('DB_USER') ? DB_USER : 'NOT DEFINED',
                'DB_PASS' => defined('DB_PASS') ? '***' . substr(DB_PASS, -4) : 'NOT DEFINED'
            ]
        ];
    } catch (Exception $e) {
        $result['tests']['config_loaded'] = [
            'name' => 'Config loaded',
            'passed' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Test 3: Check PDO extension
$result['tests']['pdo_extension'] = [
    'name' => 'PDO extension loaded',
    'passed' => extension_loaded('pdo'),
    'version' => extension_loaded('pdo') ? phpversion('pdo') : 'N/A'
];

// Test 4: Check PDO MySQL driver
$result['tests']['pdo_mysql'] = [
    'name' => 'PDO MySQL driver loaded',
    'passed' => extension_loaded('pdo_mysql'),
    'drivers' => extension_loaded('pdo') ? PDO::getAvailableDrivers() : []
];

// Test 5: Test database connection
if (defined('DB_HOST') && extension_loaded('pdo_mysql')) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        $result['tests']['db_connection'] = [
            'name' => 'Database connection',
            'passed' => true,
            'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
        ];
        
        // Test 6: Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $result['tests']['tables'] = [
            'name' => 'Tables exist',
            'passed' => count($tables) > 0,
            'count' => count($tables),
            'tables' => $tables
        ];
        
        // Test 7: Check users table structure
        if (in_array('users', $tables)) {
            $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
            $result['tests']['users_table'] = [
                'name' => 'Users table structure',
                'passed' => true,
                'columns' => array_column($columns, 'Field')
            ];
        }
        
    } catch (PDOException $e) {
        $result['tests']['db_connection'] = [
            'name' => 'Database connection',
            'passed' => false,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}

// Test 8: PHP version and environment
$result['environment'] = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'
];

// Calculate overall status
$allPassed = true;
foreach ($result['tests'] as $test) {
    if (!$test['passed']) {
        $allPassed = false;
        break;
    }
}

$result['overall_status'] = $allPassed ? 'ALL TESTS PASSED ✅' : 'SOME TESTS FAILED ❌';
$result['success'] = $allPassed;

echo json_encode($result, JSON_PRETTY_PRINT);
