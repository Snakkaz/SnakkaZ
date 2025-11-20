<?php
/**
 * SnakkaZ Typing Indicator Endpoint
 * POST /api/realtime/typing.php
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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

// Get input
$input = json_decode(file_get_contents('php://input'), true);

$roomId = $input['room_id'] ?? null;
$isTyping = $input['is_typing'] ?? false;

if (!$roomId) {
    Response::error('Room ID required', 400);
}

$db = Database::getInstance();

try {
    if ($isTyping) {
        // Insert or update typing indicator
        $db->execute(
            "INSERT INTO typing_indicators (room_id, user_id, started_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE started_at = NOW()",
            [$roomId, $user['user_id']]
        );
    } else {
        // Remove typing indicator
        $db->execute(
            "DELETE FROM typing_indicators WHERE room_id = ? AND user_id = ?",
            [$roomId, $user['user_id']]
        );
    }

    Response::success(['is_typing' => $isTyping]);

} catch (Exception $e) {
    error_log('Typing indicator error: ' . $e->getMessage());
    Response::serverError('Failed to update typing status');
}
