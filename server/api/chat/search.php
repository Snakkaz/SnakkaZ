<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database.php now has inline credentials
require_once __DIR__ . '/../../utils/Auth.php';
require_once __DIR__ . '/../../utils/Response.php';

$auth = new Auth();
$user = $auth->authenticateRequest();

if (!$user) {
    Response::unauthorized('Authentication required');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed', 405);
}

$query = $_GET['q'] ?? '';
$type = $_GET['type'] ?? 'all'; // all, messages, users, rooms
$roomId = $_GET['room_id'] ?? null;
$limit = min((int)($_GET['limit'] ?? 50), 100);

if (empty($query)) {
    Response::error('Search query required', 400);
}

try {
    $db = Database::getInstance();
    $results = [];
    
    // Search messages
    if ($type === 'all' || $type === 'messages') {
        $sql = "
            SELECT 
                m.message_id,
                m.content,
                m.created_at,
                m.room_id,
                r.room_name,
                u.user_id,
                u.username,
                u.display_name,
                u.avatar_url
            FROM messages m
            JOIN rooms r ON r.room_id = m.room_id
            JOIN users u ON u.user_id = m.user_id
            JOIN room_members rm ON rm.room_id = r.room_id AND rm.user_id = ?
            WHERE m.content LIKE ?
        ";
        
        $params = [$user['user_id'], '%' . $query . '%'];
        
        if ($roomId) {
            $sql .= " AND m.room_id = ?";
            $params[] = $roomId;
        }
        
        $sql .= " ORDER BY m.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $results['messages'] = $db->query($sql, $params);
    }
    
    // Search users
    if ($type === 'all' || $type === 'users') {
        $sql = "
            SELECT 
                user_id,
                username,
                display_name,
                avatar_url,
                status,
                last_seen
            FROM users
            WHERE (username LIKE ? OR display_name LIKE ?)
              AND user_id != ?
            ORDER BY 
                CASE 
                    WHEN username LIKE ? THEN 1
                    WHEN display_name LIKE ? THEN 2
                    ELSE 3
                END,
                username ASC
            LIMIT ?
        ";
        
        $searchPattern = '%' . $query . '%';
        $exactPattern = $query . '%';
        
        $results['users'] = $db->query($sql, [
            $searchPattern,
            $searchPattern,
            $user['user_id'],
            $exactPattern,
            $exactPattern,
            $limit
        ]);
    }
    
    // Search rooms
    if ($type === 'all' || $type === 'rooms') {
        $sql = "
            SELECT 
                r.room_id,
                r.room_name,
                r.room_type,
                r.description,
                r.avatar_url,
                COUNT(DISTINCT rm.user_id) as member_count,
                CASE 
                    WHEN rm2.user_id IS NOT NULL THEN 1 
                    ELSE 0 
                END as is_member
            FROM rooms r
            LEFT JOIN room_members rm ON rm.room_id = r.room_id
            LEFT JOIN room_members rm2 ON rm2.room_id = r.room_id AND rm2.user_id = ?
            WHERE (r.room_name LIKE ? OR r.description LIKE ?)
            GROUP BY r.room_id
            ORDER BY 
                is_member DESC,
                member_count DESC,
                r.room_name ASC
            LIMIT ?
        ";
        
        $results['rooms'] = $db->query($sql, [
            $user['user_id'],
            '%' . $query . '%',
            '%' . $query . '%',
            $limit
        ]);
    }
    
    Response::success([
        'query' => $query,
        'type' => $type,
        'results' => $results
    ]);
    
} catch (PDOException $e) {
    Response::error('Database error: ' . $e->getMessage(), 500);
}
