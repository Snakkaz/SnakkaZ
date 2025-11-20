<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../utils/Database.php';
    require_once __DIR__ . '/../../utils/Response.php';
    require_once __DIR__ . '/../../utils/Auth.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['email']) || empty($input['password'])) {
        echo json_encode(['error' => 'Email and password required']);
        exit;
    }
    
    $db = Database::getInstance();
    
    $user = $db->fetchOne(
        "SELECT id, username, email, password_hash, display_name, avatar_url, status 
         FROM users WHERE email = ?",
        [$input['email']]
    );
    
    if (!$user) {
        echo json_encode(['error' => 'User not found', 'email' => $input['email']]);
        exit;
    }
    
    if (!Auth::verifyPassword($input['password'], $user['password_hash'])) {
        echo json_encode(['error' => 'Password incorrect']);
        exit;
    }
    
    $auth = new Auth();
    $token = $auth->generateToken($user['id']);
    
    $db->execute(
        "UPDATE users SET status = 'online', last_seen = NOW() WHERE id = ?",
        [$user['id']]
    );
    
    unset($user['password_hash']);
    $user['user_id'] = $user['id'];
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
