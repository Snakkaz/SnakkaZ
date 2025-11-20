<?php
/**
 * SnakkaZ Chat - Send Message
 * POST /api/chat/send.php
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

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/Response.php';
require_once __DIR__ . '/../../utils/Auth.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$auth = new Auth();
$user = $auth->getCurrentUser();
$db = Database::getInstance();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate
if (empty($input['room_id'])) {
    Response::error('room_id is required', 400);
}

if (empty($input['content'])) {
    Response::error('content is required', 400);
}

$roomId = $input['room_id'];
$content = trim($input['content']);
$messageType = $input['message_type'] ?? 'text';

// Check if user is member of room
$isMember = $db->fetchOne(
    "SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?",
    [$roomId, $user['user_id']]
);

if (!$isMember) {
    Response::forbidden('You are not a member of this room');
}

// Validate message type
if (!in_array($messageType, ['text', 'image', 'file', 'audio', 'video'])) {
    Response::error('Invalid message type', 400);
}

// Insert message
try {
    $messageId = $db->insert(
        "INSERT INTO messages (room_id, user_id, content, message_type, created_at) 
         VALUES (?, ?, ?, ?, NOW())",
        [$roomId, $user['user_id'], $content, $messageType]
    );
    
    // Update room updated_at
    $db->execute(
        "UPDATE rooms SET updated_at = NOW() WHERE room_id = ?",
        [$roomId]
    );
    
    // Get the created message with user info
    $message = $db->fetchOne(
        "SELECT 
            m.message_id,
            m.room_id,
            m.user_id,
            m.content,
            m.message_type,
            m.is_edited,
            m.created_at,
            m.updated_at,
            u.username,
            u.display_name,
            u.avatar_url
         FROM messages m
         INNER JOIN users u ON m.user_id = u.user_id
         WHERE m.message_id = ?",
        [$messageId]
    );
    
    Response::success($message, 'Message sent successfully');
    
} catch (Exception $e) {
    error_log('Send message error: ' . $e->getMessage());
    Response::serverError('Failed to send message');
}
