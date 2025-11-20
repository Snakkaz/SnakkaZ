<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/Auth.php';
require_once __DIR__ . '/../../utils/Response.php';

$auth = new Auth();
$userId = $auth->getUserId();

if (!$userId) {
    Response::unauthorized('Authentication required');
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Get user profile
if ($method === 'GET') {
    $profileUserId = $_GET['user_id'] ?? $userId;
    
    try {
        $db = getDBConnection();
        
        // Get user info
        $stmt = $db->prepare("
            SELECT 
                user_id,
                username,
                email,
                display_name,
                avatar_url,
                status,
                last_seen,
                created_at
            FROM users
            WHERE user_id = ?
        ");
        $stmt->execute([$profileUserId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            Response::error('User not found', 404);
        }
        
        // Get user settings (only if viewing own profile)
        $settings = null;
        if ($profileUserId == $userId) {
            $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$userId]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Get shared rooms
        $stmt = $db->prepare("
            SELECT DISTINCT
                r.room_id,
                r.room_name,
                r.icon,
                r.room_type
            FROM room_members rm1
            JOIN room_members rm2 ON rm1.room_id = rm2.room_id
            JOIN rooms r ON r.room_id = rm1.room_id
            WHERE rm1.user_id = ? AND rm2.user_id = ?
            ORDER BY r.room_name
        ");
        $stmt->execute([$userId, $profileUserId]);
        $sharedRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        Response::success([
            'user' => $user,
            'settings' => $settings,
            'shared_rooms' => $sharedRooms
        ]);
        
    } catch (PDOException $e) {
        Response::error('Database error: ' . $e->getMessage(), 500);
    }
}

// PUT - Update user profile
else if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $db = getDBConnection();
        $updates = [];
        $params = [];
        
        // Allowed fields to update
        $allowedFields = ['display_name', 'avatar_url'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            Response::badRequest('No valid fields to update');
        }
        
        $params[] = $userId;
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // Get updated user
        $stmt = $db->prepare("
            SELECT user_id, username, email, display_name, avatar_url, status 
            FROM users WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Response::success($user);
        
    } catch (PDOException $e) {
        Response::error('Database error: ' . $e->getMessage(), 500);
    }
}

else {
    Response::error('Method not allowed', 405);
}
