<?php
/**
 * Debug version of rooms.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/../../utils/Database.php';
    require_once __DIR__ . '/../../utils/Response.php';
    require_once __DIR__ . '/../../utils/Auth.php';
    
    $auth = new Auth();
    $user = $auth->authenticateRequest();
    
    if (!$user) {
        echo json_encode(['error' => 'Not authenticated', 'user' => $user]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'user_id_exists' => isset($user['id']),
        'user_id_value' => $user['id'] ?? 'NOT SET'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
