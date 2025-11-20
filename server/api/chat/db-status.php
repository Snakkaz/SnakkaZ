<?php
/**
 * Database Connection Status Test
 * Created: <?php echo date('Y-m-d H:i:s'); ?>
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Test 1: Check if Database.php exists
$databaseUtilPath = __DIR__ . '/../../utils/Database.php';
$configPath = __DIR__ . '/../../config/database.php';

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'database_util_exists' => file_exists($databaseUtilPath),
    'config_exists' => file_exists($configPath),
    'errors' => []
];

try {
    // Test 2: Include config
    require_once $configPath;
    $status['config_loaded'] = true;
    $status['db_host'] = DB_HOST ?? 'NOT SET';
    $status['db_name'] = DB_NAME ?? 'NOT SET';
    $status['db_user'] = DB_USER ?? 'NOT SET';
    $status['db_pass_set'] = isset(DB_PASS) && !empty(DB_PASS);
    
    // Test 3: Try direct PDO connection
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        $status['pdo_connection'] = 'SUCCESS';
        
        // Test 4: Simple query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        $status['users_count'] = $result['count'];
        
    } catch (PDOException $e) {
        $status['pdo_connection'] = 'FAILED';
        $status['pdo_error'] = $e->getMessage();
    }
    
    // Test 5: Try Database class
    require_once $databaseUtilPath;
    $db = Database::getInstance();
    $status['database_class'] = 'SUCCESS';
    
    // Test 6: Fetch rooms
    $rooms = $db->fetchAll("SELECT room_id, room_name FROM rooms LIMIT 3");
    $status['rooms_count'] = count($rooms);
    $status['sample_rooms'] = $rooms;
    
} catch (Exception $e) {
    $status['errors'][] = $e->getMessage();
    $status['exception_trace'] = $e->getTraceAsString();
}

echo json_encode($status, JSON_PRETTY_PRINT);
