<?php
/**
 * SnakkaZ Chat - User Registration
 * POST /api/auth/register.php
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

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Auth.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$errors = [];

if (empty($input['username'])) {
    $errors['username'] = 'Username is required';
} elseif (strlen($input['username']) < 3) {
    $errors['username'] = 'Username must be at least 3 characters';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
    $errors['username'] = 'Username can only contain letters, numbers and underscores';
}

if (empty($input['email'])) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
}

if (empty($input['password'])) {
    $errors['password'] = 'Password is required';
} elseif (strlen($input['password']) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
}

if (!empty($errors)) {
    Response::validationError($errors);
}

$db = Database::getInstance();

// Check if username exists
$existing = $db->fetchOne(
    "SELECT id FROM users WHERE username = ? OR email = ?",
    [$input['username'], $input['email']]
);

if ($existing) {
    Response::error('Username or email already exists', 409);
}

// Hash password
$passwordHash = Auth::hashPassword($input['password']);

// Create user
try {
    $userId = $db->insert(
        "INSERT INTO users (username, email, password_hash, display_name, created_at) 
         VALUES (?, ?, ?, ?, NOW())",
        [
            $input['username'],
            $input['email'],
            $passwordHash,
            $input['display_name'] ?? $input['username']
        ]
    );
    
    // Generate token
    $auth = new Auth();
    $token = $auth->generateToken($userId);
    
    // Get user data
    $user = $db->fetchOne(
        "SELECT id, username, email, display_name, avatar_url, status, created_at 
         FROM users WHERE id = ?",
        [$userId]
    );
    
    Response::success([
        'token' => $token,
        'user' => $user
    ], 'Registration successful');
    
} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    Response::serverError('Registration failed');
}
