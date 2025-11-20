<?php
/**
 * SnakkaZ Chat - Get Chat Rooms
 * GET /api/chat/rooms.php
 */

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

$auth = new Auth();
$user = $auth->authenticateRequest();

if (!$user) {
    Response::unauthorized();
}

// Ensure user ID exists
if (!isset($user['id'])) {
    error_log('User ID not found in auth response: ' . json_encode($user));
    Response::error('Invalid user data', 500);
}

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Get all rooms user is member of
        $sql = "SELECT 
                    r.id as room_id,
                    r.name as room_name,
                    r.type as room_type,
                    r.creator_id as created_by,
                    r.avatar_url,
                    r.description,
                    r.privacy_level,
                    r.created_at,
                    r.updated_at,
                    (SELECT content FROM messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                    (SELECT COUNT(*) FROM messages WHERE room_id = r.id) as message_count,
                    (SELECT COUNT(*) FROM room_members WHERE room_id = r.id) as member_count
                FROM rooms r
                INNER JOIN room_members rm ON r.id = rm.room_id
                WHERE rm.user_id = ?
                ORDER BY last_message_time DESC";
        
        $rooms = $db->fetchAll($sql, [$user['id']]);
        
        Response::success($rooms);
    } catch (Exception $e) {
        error_log('Get rooms error: ' . $e->getMessage());
        Response::error('Failed to fetch rooms: ' . $e->getMessage(), 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new room
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate
    if (empty($input['name'])) {
        Response::error('Room name is required', 400);
    }
    
    $type = $input['type'] ?? 'group';
    
    if (!in_array($type, ['private', 'group'])) {
        Response::error('Invalid room type', 400);
    }
    
    try {
        $db->beginTransaction();
        
        // Create room
        $db->execute(
            "INSERT INTO rooms (name, type, creator_id, description, created_at) 
             VALUES (?, ?, ?, ?, NOW())",
            [
                $input['name'],
                $type,
                $user['id'],
                $input['description'] ?? null
            ]
        );
        
        $roomId = $db->getConnection()->lastInsertId();
        
        // Add creator as admin
        $db->execute(
            "INSERT INTO room_members (room_id, user_id, role) VALUES (?, ?, 'admin')",
            [$roomId, $user['id']]
        );
        
        // Add other members if provided
        if (!empty($input['members']) && is_array($input['members'])) {
            foreach ($input['members'] as $memberId) {
                $db->execute(
                    "INSERT INTO room_members (room_id, user_id) VALUES (?, ?)",
                    [$roomId, $memberId]
                );
            }
        }
        
        $db->commit();
        
        // Get created room
        $room = $db->fetchOne(
            "SELECT id as room_id, name as room_name, type as room_type, 
                    creator_id as created_by, avatar_url, description, 
                    privacy_level, created_at, updated_at 
             FROM rooms WHERE id = ?",
            [$roomId]
        );
        
        Response::success($room, 'Room created successfully');
        
    } catch (Exception $e) {
        $db->rollback();
        error_log('Create room error: ' . $e->getMessage());
        Response::serverError('Failed to create room');
    }
    
} else {
    Response::error('Method not allowed', 405);
}
