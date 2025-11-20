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
        "SELECT id as room_id, name as room_name, privacy_level, password_hash, invite_only, 
                max_members, is_active
         FROM rooms 
         WHERE id = ?",
        [$roomId]
    );

    if (!$room) {
        Response::error('Room not found', 404);
    }

    if (!$room['is_active']) {
        Response::error('Room is not active', 403);
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

    // Handle different privacy levels
    if ($room['privacy_level'] === 'password') {
        // Verify password
        if (!$password) {
            Response::error('Password is required', 401);
        }

        if (!password_verify($password, $room['password_hash'])) {
            Response::error('Incorrect password', 401);
        }
    } 
    elseif ($room['privacy_level'] === 'private' || $room['invite_only']) {
        // Verify invite code
        if (!$inviteCode) {
            Response::error('Invite code is required', 401);
        }

        $invite = $db->fetchOne(
            "SELECT * FROM room_invites 
             WHERE room_id = ? AND invite_code = ? 
             AND (expires_at IS NULL OR expires_at > NOW())
             AND (max_uses = 0 OR current_uses < max_uses)",
            [$roomId, $inviteCode]
        );

        if (!$invite) {
            Response::error('Invalid or expired invite code', 401);
        }

        // Update invite usage
        $db->execute(
            "UPDATE room_invites 
             SET current_uses = current_uses + 1 
             WHERE invite_id = ?",
            [$invite['invite_id']]
        );
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
