<?php
/**
 * SnakkaZ Chat - User Login
 * POST /api/auth/login.php
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/Response.php';
require_once __DIR__ . '/../../utils/Auth.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($input['email']) || empty($input['password'])) {
    Response::error('Email and password are required', 400);
}

$db = Database::getInstance();

// Find user by email
$user = $db->fetchOne(
    "SELECT id, username, email, password_hash, display_name, avatar_url, status 
     FROM users WHERE email = ?",
    [$input['email']]
);

if (!$user) {
    Response::error('Invalid email or password', 401);
}

// Verify password
if (!Auth::verifyPassword($input['password'], $user['password_hash'])) {
    Response::error('Invalid email or password', 401);
}

// Generate token
$auth = new Auth();
$token = $auth->generateToken($user['id']);

// Note: User status is updated in Auth::validateToken()

// Remove password hash from response
unset($user['password_hash']);

// Add user_id alias for frontend compatibility
$user['user_id'] = $user['id'];

Response::success([
    'token' => $token,
    'user' => $user
], 'Login successful');
