<?php
/**
 * SnakkaZ Chat - Get Messages
 * GET /api/chat/messages.php?room_id=1&limit=50&offset=0
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// Allow GET and POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    Response::error('Method not allowed', 405);
}

$auth = new Auth();
$user = $auth->authenticateRequest();

if (!$user) {
    Response::unauthorized();
}

$db = Database::getInstance();

// Handle POST request (mark as read)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;
    $roomId = $input['room_id'] ?? null;
    
    if ($action === 'mark_read' && $roomId) {
        // Mark all messages in room as read for this user
        $db->execute(
            "INSERT INTO message_read_receipts (message_id, user_id, read_at)
             SELECT m.message_id, ?, NOW()
             FROM messages m
             WHERE m.room_id = ? AND m.user_id != ?
             ON DUPLICATE KEY UPDATE read_at = NOW()",
            [$user['user_id'], $roomId, $user['user_id']]
        );
        Response::success(['marked' => true], 'Messages marked as read');
    } else {
        Response::error('Invalid action', 400);
    }
}

// Handle GET request (fetch messages)
$roomId = $_GET['room_id'] ?? null;
$limit = min((int)($_GET['limit'] ?? 50), 100);  // Max 100
$offset = (int)($_GET['offset'] ?? 0);

if (!$roomId) {
    Response::error('room_id is required', 400);
}

// Check if user is member of room
$isMember = $db->fetchOne(
    "SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?",
    [$roomId, $user['user_id']]
);

if (!$isMember) {
    Response::forbidden('You are not a member of this room');
}

// Get messages
$sql = "SELECT 
            m.message_id,
            m.room_id,
            m.user_id,
            m.content,
            m.message_type,
            m.attachment_id,
            m.is_edited,
            m.is_deleted,
            m.created_at,
            u.username,
            u.display_name,
            u.avatar_url
        FROM messages m
        INNER JOIN users u ON m.user_id = u.user_id
        WHERE m.room_id = ? AND m.is_deleted = 0
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?";

$messages = $db->fetchAll($sql, [$roomId, $limit, $offset]);

// Get reactions for all messages
$messageIds = array_column($messages, 'message_id');
$reactions = [];

if (!empty($messageIds)) {
    $placeholders = str_repeat('?,', count($messageIds) - 1) . '?';
    $reactionsData = $db->fetchAll(
        "SELECT 
            mr.message_id,
            mr.emoji,
            mr.user_id,
            u.username,
            u.display_name
        FROM message_reactions mr
        JOIN users u ON u.user_id = mr.user_id
        WHERE mr.message_id IN ($placeholders)
        ORDER BY mr.created_at ASC",
        $messageIds
    );
    
    // Group reactions by message_id and emoji
    foreach ($reactionsData as $reaction) {
        $msgId = $reaction['message_id'];
        $emoji = $reaction['emoji'];
        
        if (!isset($reactions[$msgId])) {
            $reactions[$msgId] = [];
        }
        
        if (!isset($reactions[$msgId][$emoji])) {
            $reactions[$msgId][$emoji] = [
                'emoji' => $emoji,
                'count' => 0,
                'users' => [],
                'has_reacted' => false
            ];
        }
        
        $reactions[$msgId][$emoji]['count']++;
        $reactions[$msgId][$emoji]['users'][] = [
            'user_id' => $reaction['user_id'],
            'username' => $reaction['username'],
            'display_name' => $reaction['display_name']
        ];
        
        if ($reaction['user_id'] == $user['user_id']) {
            $reactions[$msgId][$emoji]['has_reacted'] = true;
        }
    }
}

// Add reactions to messages
foreach ($messages as &$message) {
    $msgId = $message['message_id'];
    $message['reactions'] = isset($reactions[$msgId]) 
        ? array_values($reactions[$msgId]) 
        : [];
}
unset($message); // Break reference

// Reverse to show oldest first
$messages = array_reverse($messages);

Response::success($messages);
