<?php
/**
 * SnakkaZ Chat - User Logout
 * POST /api/auth/logout.php
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Auth.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$auth = new Auth();
$user = $auth->getCurrentUser();

// Get token
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
}

if (!$token) {
    Response::error('No token provided', 400);
}

// Update user status to offline
$db = Database::getInstance();
$db->execute(
    "UPDATE users SET status = 'offline', last_seen = NOW() WHERE id = ?",
    [$user['user_id']]
);

// Delete session
$auth->logout($token);

Response::success([], 'Logout successful');
