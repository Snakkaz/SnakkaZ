<?php
/**
 * SnakkaZ - Join Public Rooms
 * Utility endpoint to join all public rooms
 * GET /api/user/join-public-rooms.php
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

$db = Database::getInstance();

try {
    // Join all public rooms
    $db->query(
        "INSERT IGNORE INTO room_members (room_id, user_id, role)
         SELECT room_id, ?, 'member' FROM rooms WHERE is_public = TRUE",
        [$user['user_id']]
    );

    // Get joined rooms
    $rooms = $db->fetchAll(
        "SELECT r.*, rm.role, rm.joined_at
         FROM room_members rm
         JOIN rooms r ON rm.room_id = r.room_id
         WHERE rm.user_id = ?
         ORDER BY r.created_at",
        [$user['user_id']]
    );

    Response::success([
        'joined_count' => count($rooms),
        'rooms' => $rooms
    ], 'Joined all public rooms');

} catch (Exception $e) {
    error_log('Join rooms error: ' . $e->getMessage());
    Response::serverError('Failed to join rooms');
}
