<?php
/**
 * SnakkaZ Chat - Health Check
 * GET /api/health.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../utils/Database.php';

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => API_VERSION ?? '1.0.0'
];

// Test database connection
try {
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT 1 as test");
    
    if ($result && $result['test'] == 1) {
        $health['database'] = 'connected';
    } else {
        $health['database'] = 'error';
        $health['status'] = 'degraded';
    }
} catch (Exception $e) {
    $health['database'] = 'disconnected';
    $health['status'] = 'error';
    $health['error'] = $e->getMessage();
}

// Check uploads directory
if (is_writable(__DIR__ . '/../../uploads')) {
    $health['uploads'] = 'writable';
} else {
    $health['uploads'] = 'not_writable';
    $health['status'] = 'degraded';
}

http_response_code($health['status'] === 'ok' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
