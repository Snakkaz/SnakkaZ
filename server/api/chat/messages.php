<?php
/**
 * SnakkaZ Chat - Get Messages
 * GET /api/chat/messages.php?room_id=1&limit=50&offset=0
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed', 405);
}

$auth = new Auth();
$user = $auth->getCurrentUser();
$db = Database::getInstance();

// Get parameters
$roomId = $_GET['room_id'] ?? null;
$limit = min((int)($_GET['limit'] ?? 50), 100);  // Max 100
$offset = (int)($_GET['offset'] ?? 0);

if (!$roomId) {
    Response::error('room_id is required', 400);
}

// Check if user is member of room
$isMember = $db->fetchOne(
    "SELECT id FROM room_members WHERE room_id = ? AND user_id = ?",
    [$roomId, $user['user_id']]
);

if (!$isMember) {
    Response::forbidden('You are not a member of this room');
}

// Get messages
$sql = "SELECT 
            m.id,
            m.room_id,
            m.user_id,
            m.content,
            m.message_type,
            m.file_url,
            m.file_name,
            m.reply_to_id,
            m.is_edited,
            m.is_deleted,
            m.created_at,
            u.username,
            u.display_name,
            u.avatar_url
        FROM messages m
        INNER JOIN users u ON m.user_id = u.id
        WHERE m.room_id = ? AND m.is_deleted = 0
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?";

$messages = $db->fetchAll($sql, [$roomId, $limit, $offset]);

// Reverse to show oldest first
$messages = array_reverse($messages);

Response::success($messages);
