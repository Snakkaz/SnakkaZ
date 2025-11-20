<?php
/**
 * SnakkaZ Chat - Message Reactions
 * POST /api/chat/reactions.php - Toggle reaction
 * GET /api/chat/reactions.php?message_id=X - Get reactions
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

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// POST - Add or remove reaction
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['message_id']) || !isset($data['emoji'])) {
        Response::error('message_id and emoji required', 400);
    }
    
    $messageId = $data['message_id'];
    $emoji = $data['emoji'];
    
    try {
        // Check if reaction already exists
        $existing = $db->fetchOne(
            "SELECT reaction_id FROM message_reactions 
             WHERE message_id = ? AND user_id = ? AND emoji = ?",
            [$messageId, $user['user_id'], $emoji]
        );
        
        if ($existing) {
            // Remove reaction
            $db->execute(
                "DELETE FROM message_reactions WHERE reaction_id = ?",
                [$existing['reaction_id']]
            );
            
            Response::success([
                'action' => 'removed',
                'message_id' => $messageId,
                'emoji' => $emoji
            ]);
        } else {
            // Add reaction
            $reactionId = $db->insert(
                "INSERT INTO message_reactions (message_id, user_id, emoji, created_at) 
                 VALUES (?, ?, ?, NOW())",
                [$messageId, $user['user_id'], $emoji]
            );
            
            Response::success([
                'action' => 'added',
                'reaction_id' => $reactionId,
                'message_id' => $messageId,
                'emoji' => $emoji,
                'user_id' => $user['user_id']
            ]);
        }
        
    } catch (Exception $e) {
        error_log('Reaction error: ' . $e->getMessage());
        Response::serverError('Failed to process reaction');
    }
}

// GET - Get reactions for message
else if ($method === 'GET') {
    if (!isset($_GET['message_id'])) {
        Response::error('message_id required', 400);
    }
    
    $messageId = $_GET['message_id'];
    
    try {
        $reactions = $db->fetchAll(
            "SELECT 
                mr.reaction_id,
                mr.emoji,
                mr.user_id,
                u.username,
                u.display_name,
                mr.created_at
            FROM message_reactions mr
            JOIN users u ON u.user_id = mr.user_id
            WHERE mr.message_id = ?
            ORDER BY mr.created_at ASC",
            [$messageId]
        );
        
        // Group reactions by emoji
        $grouped = [];
        foreach ($reactions as $reaction) {
            $emoji = $reaction['emoji'];
            if (!isset($grouped[$emoji])) {
                $grouped[$emoji] = [
                    'emoji' => $emoji,
                    'count' => 0,
                    'users' => [],
                    'has_reacted' => false
                ];
            }
            $grouped[$emoji]['count']++;
            $grouped[$emoji]['users'][] = [
                'user_id' => $reaction['user_id'],
                'username' => $reaction['username'],
                'display_name' => $reaction['display_name']
            ];
            if ($reaction['user_id'] == $user['user_id']) {
                $grouped[$emoji]['has_reacted'] = true;
            }
        }
        
        Response::success([
            'message_id' => $messageId,
            'reactions' => array_values($grouped)
        ]);
        
    } catch (Exception $e) {
        error_log('Get reactions error: ' . $e->getMessage());
        Response::serverError('Failed to get reactions');
    }
}

else {
    Response::error('Method not allowed', 405);
}
