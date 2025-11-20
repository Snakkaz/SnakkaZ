<?php
/**
 * Create Room Endpoint
 * Supports: public, private, and password-protected rooms
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

// Validate required fields
$roomName = trim($data['name'] ?? '');
$roomType = $data['type'] ?? 'group'; // direct, group, channel
$privacyLevel = $data['privacy_level'] ?? 'public'; // public, private, password
$password = $data['password'] ?? null;
$description = trim($data['description'] ?? '');
$inviteOnly = isset($data['invite_only']) ? (bool)$data['invite_only'] : false;
$isEncrypted = isset($data['is_encrypted']) ? (bool)$data['is_encrypted'] : false;
$maxMembers = isset($data['max_members']) ? (int)$data['max_members'] : 100;

if (empty($roomName)) {
    Response::error('Room name is required', 400);
}

if (!in_array($privacyLevel, ['public', 'private', 'password'])) {
    Response::error('Invalid privacy level', 400);
}

if ($privacyLevel === 'password' && empty($password)) {
    Response::error('Password is required for password-protected rooms', 400);
}

$db = Database::getInstance();

try {
    // Hash password if provided
    $passwordHash = null;
    if ($privacyLevel === 'password' && $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }

    // Create room
    $db->execute(
        "INSERT INTO rooms (name, type, privacy_level, password_hash, description, 
                          invite_only, is_encrypted, max_members, creator_id, is_active, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())",
        [
            $roomName,
            $roomType,
            $privacyLevel,
            $passwordHash,
            $description,
            $inviteOnly ? 1 : 0,
            $isEncrypted ? 1 : 0,
            $maxMembers,
            $user['id']
        ]
    );
    
    $roomId = $db->getConnection()->lastInsertId();

    // Add creator as admin member
    $db->execute(
        "INSERT INTO room_members (room_id, user_id, role) VALUES (?, ?, 'admin')",
        [$roomId, $user['id']]
    );

    // Generate invite code if private or invite-only
    $inviteCode = null;
    if ($privacyLevel === 'private' || $inviteOnly) {
        $inviteCode = bin2hex(random_bytes(16));
        $db->execute(
            "INSERT INTO room_invites (room_id, invited_by, invite_code, max_uses, current_uses) 
             VALUES (?, ?, ?, 0, 0)",
            [$roomId, $user['id'], $inviteCode]
        );
    }

    // Fetch created room
    $room = $db->fetchOne(
        "SELECT id as room_id, name as room_name, type as room_type, privacy_level, description, 
                invite_only, is_encrypted, max_members, creator_id as created_by, created_at
         FROM rooms 
         WHERE id = ?",
        [$roomId]
    );

    Response::success([
        'room' => $room,
        'invite_code' => $inviteCode,
        'message' => 'Room created successfully'
    ]);

} catch (PDOException $e) {
    error_log('Create room error: ' . $e->getMessage());
    Response::error('Failed to create room', 500);
}
