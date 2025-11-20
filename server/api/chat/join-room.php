<?php
/**
 * Join Room Endpoint
 * Handles joining public, password-protected, and private rooms
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

$auth = new Auth();
$user = $auth->authenticateRequest();

if (!$user) {
    Response::unauthorized();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$roomId = $data['room_id'] ?? null;
$password = $data['password'] ?? null;
$inviteCode = $data['invite_code'] ?? null;

if (!$roomId) {
    Response::error('Room ID is required', 400);
}

$db = Database::getInstance();

try {
    // Get room details
    $room = $db->fetchOne(
        "SELECT room_id, room_name, description, icon, is_public,
                max_members, created_by, created_at
         FROM rooms
         WHERE room_id = ?",
        [$roomId]
    );

    if (!$room) {
        Response::error('Room not found', 404);
    }

    // Check if already a member
    $existingMember = $db->fetchOne(
        "SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?",
        [$roomId, $user['id']]
    );

    if ($existingMember) {
        Response::error('Already a member of this room', 400);
    }

    // Check member limit
    $memberCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM room_members WHERE room_id = ?",
        [$roomId]
    )['count'];

    if ($memberCount >= $room['max_members']) {
        Response::error('Room is full', 403);
    }

    // For now, allow joining if room is public
    if (!$room['is_public']) {
        // TODO: Add invite code verification when room_invites features are implemented
        Response::error('This room is private', 403);
    }

    // Add user to room
    $db->execute(
        "INSERT INTO room_members (room_id, user_id, role) VALUES (?, ?, 'member')",
        [$roomId, $user['id']]
    );

    // Send system message
    $db->execute(
        "INSERT INTO messages (room_id, user_id, content, message_type)
         VALUES (?, ?, ?, 'text')",
        [$roomId, $user['id'], $user['username'] . ' joined the room']
    );

    Response::success([
        'message' => 'Successfully joined room',
        'room' => $room
    ]);

} catch (PDOException $e) {
    error_log('Join room error: ' . $e->getMessage());
    Response::error('Failed to join room', 500);
}
