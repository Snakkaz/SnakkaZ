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

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all rooms user is member of
    $sql = "SELECT 
                r.id,
                r.name,
                r.type,
                r.avatar_url,
                r.description,
                r.created_at,
                r.updated_at,
                (SELECT content FROM messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                (SELECT COUNT(*) FROM messages WHERE room_id = r.id) as message_count,
                (SELECT COUNT(*) FROM room_members WHERE room_id = r.id) as member_count
            FROM rooms r
            INNER JOIN room_members rm ON r.id = rm.room_id
            WHERE rm.user_id = ? AND r.is_active = 1
            ORDER BY last_message_time DESC";
    
    $rooms = $db->fetchAll($sql, [$user['user_id']]);
    
    Response::success($rooms);
    
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
        $roomId = $db->insert(
            "INSERT INTO rooms (name, type, creator_id, description, created_at) 
             VALUES (?, ?, ?, ?, NOW())",
            [
                $input['name'],
                $type,
                $user['user_id'],
                $input['description'] ?? null
            ]
        );
        
        // Add creator as admin
        $db->insert(
            "INSERT INTO room_members (room_id, user_id, role) VALUES (?, ?, 'admin')",
            [$roomId, $user['user_id']]
        );
        
        // Add other members if provided
        if (!empty($input['members']) && is_array($input['members'])) {
            foreach ($input['members'] as $memberId) {
                $db->insert(
                    "INSERT INTO room_members (room_id, user_id) VALUES (?, ?)",
                    [$roomId, $memberId]
                );
            }
        }
        
        $db->commit();
        
        // Get created room
        $room = $db->fetchOne(
            "SELECT * FROM rooms WHERE id = ?",
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
