<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$result = [
    'success' => true,
    'message' => 'Simple test works',
    'php_version' => phpversion(),
    'cwd' => getcwd(),
    'dir' => __DIR__
];

// Test database config
$configPath = __DIR__ . '/../../config/database.php';
$result['config_exists'] = file_exists($configPath);
$result['config_path'] = realpath($configPath);

if (file_exists($configPath)) {
    require_once $configPath;
    $result['db_constants'] = [
        'DB_HOST' => defined('DB_HOST') ? DB_HOST : 'NOT DEFINED',
        'DB_NAME' => defined('DB_NAME') ? DB_NAME : 'NOT DEFINED',
        'DB_USER' => defined('DB_USER') ? DB_USER : 'NOT DEFINED',
        'DB_PASS_SET' => defined('DB_PASS') && !empty(DB_PASS)
    ];
    
    // Test PDO
    $result['pdo_loaded'] = extension_loaded('pdo');
    $result['pdo_mysql_loaded'] = extension_loaded('pdo_mysql');
    
    if (extension_loaded('pdo_mysql')) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $result['db_connection'] = 'SUCCESS';
            $result['db_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (PDOException $e) {
            $result['db_connection'] = 'FAILED';
            $result['db_error'] = $e->getMessage();
            $result['db_error_code'] = $e->getCode();
        }
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
