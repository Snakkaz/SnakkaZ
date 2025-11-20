<?php
/**
 * SnakkaZ Real-time Polling Endpoint
 * Long-polling for real-time updates without WebSocket
 * 
 * GET /api/realtime/poll.php?room_id=1&last_message_id=0
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/Response.php';
require_once __DIR__ . '/../../utils/Auth.php';

// Authenticate user
$auth = new Auth();
$user = $auth->authenticateRequest();

if (!$user) {
    Response::unauthorized();
}

// Get parameters
$roomId = $_GET['room_id'] ?? null;
$lastMessageId = isset($_GET['last_message_id']) ? (int)$_GET['last_message_id'] : 0;
$timeout = isset($_GET['timeout']) ? min((int)$_GET['timeout'], 30) : 25; // Max 30 seconds

if (!$roomId) {
    Response::error('Room ID required', 400);
}

$db = Database::getInstance();

// Check if user is member of room
$isMember = $db->fetchOne(
    "SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?",
    [$roomId, $user['user_id']]
);

if (!$isMember) {
    Response::error('Not a member of this room', 403);
}

// Long polling loop
$start = time();
$polled = false;

while ((time() - $start) < $timeout && !$polled) {
    // Check for new messages
    $newMessages = $db->fetchAll(
        "SELECT m.*, u.username, u.display_name, u.avatar_url,
                (SELECT JSON_ARRAYAGG(
                    JSON_OBJECT('emoji', emoji, 'user_id', user_id, 'username', 
                        (SELECT username FROM users WHERE user_id = mr.user_id))
                ) FROM message_reactions mr WHERE mr.message_id = m.message_id) as reactions
         FROM messages m
         JOIN users u ON m.user_id = u.user_id
         WHERE m.room_id = ? AND m.message_id > ?
         ORDER BY m.created_at ASC
         LIMIT 50",
        [$roomId, $lastMessageId]
    );

    // Check for typing indicators
    $typingUsers = $db->fetchAll(
        "SELECT ti.user_id, u.username, u.display_name
         FROM typing_indicators ti
         JOIN users u ON ti.user_id = u.user_id
         WHERE ti.room_id = ? 
           AND ti.user_id != ?
           AND ti.started_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)",
        [$roomId, $user['user_id']]
    );

    // Check for user status changes (online/offline)
    $onlineUsers = $db->fetchAll(
        "SELECT DISTINCT u.user_id, u.username, u.status
         FROM room_members rm
         JOIN users u ON rm.user_id = u.user_id
         WHERE rm.room_id = ?
           AND u.last_seen > DATE_SUB(NOW(), INTERVAL 2 MINUTE)",
        [$roomId]
    );

    // If we have updates, return immediately
    if (!empty($newMessages) || !empty($typingUsers)) {
        Response::success([
            'messages' => $newMessages,
            'typing' => $typingUsers,
            'online_users' => $onlineUsers,
            'timestamp' => time()
        ]);
        $polled = true;
    }

    // Sleep for a short interval before checking again
    if (!$polled) {
        usleep(500000); // 0.5 seconds
    }
}

// No updates within timeout
Response::success([
    'messages' => [],
    'typing' => [],
    'online_users' => $db->fetchAll(
        "SELECT DISTINCT u.user_id, u.username, u.status
         FROM room_members rm
         JOIN users u ON rm.user_id = u.user_id
         WHERE rm.room_id = ?
           AND u.last_seen > DATE_SUB(NOW(), INTERVAL 2 MINUTE)",
        [$roomId]
    ),
    'timestamp' => time()
]);
