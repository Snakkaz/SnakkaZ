#!/usr/bin/env php
<?php
/**
 * SnakkaZ WebSocket Server
 * Real-time messaging with PHP WebSockets
 */

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../config/database.php';

class ChatServer {
    private $clients = [];
    private $rooms = [];
    private $userSockets = [];
    private $db;
    private $auth;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
        echo "✅ ChatServer initialized\n";
    }

    public function handleConnection($socket) {
        $this->clients[(int)$socket] = [
            'socket' => $socket,
            'authenticated' => false,
            'user' => null,
            'rooms' => []
        ];
        
        echo "🔌 New connection: " . (int)$socket . "\n";
    }

    public function handleDisconnection($socket) {
        $socketId = (int)$socket;
        
        if (isset($this->clients[$socketId])) {
            $client = $this->clients[$socketId];
            
            // Leave all rooms
            foreach ($client['rooms'] as $roomId) {
                $this->leaveRoom($socketId, $roomId);
            }
            
            // Remove from user sockets
            if ($client['user']) {
                $userId = $client['user']['user_id'];
                unset($this->userSockets[$userId]);
                
                // Broadcast offline status
                $this->broadcastUserStatus($userId, 'offline');
            }
            
            unset($this->clients[$socketId]);
        }
        
        echo "❌ Disconnection: $socketId\n";
    }

    public function handleMessage($socket, $data) {
        $socketId = (int)$socket;
        
        try {
            $message = json_decode($data, true);
            
            if (!$message || !isset($message['type'])) {
                $this->sendError($socket, 'Invalid message format');
                return;
            }

            $type = $message['type'];

            // Handle authentication first
            if ($type === 'authenticate') {
                $this->handleAuth($socket, $message);
                return;
            }

            // Check if authenticated
            if (!$this->clients[$socketId]['authenticated']) {
                $this->sendError($socket, 'Not authenticated');
                return;
            }

            // Route message to handler
            switch ($type) {
                case 'join_room':
                    $this->handleJoinRoom($socket, $message);
                    break;
                case 'leave_room':
                    $this->handleLeaveRoom($socket, $message);
                    break;
                case 'message':
                    $this->handleChatMessage($socket, $message);
                    break;
                case 'typing':
                    $this->handleTyping($socket, $message);
                    break;
                case 'reaction':
                    $this->handleReaction($socket, $message);
                    break;
                case 'read_receipt':
                    $this->handleReadReceipt($socket, $message);
                    break;
                case 'ping':
                    $this->send($socket, ['type' => 'pong']);
                    break;
                default:
                    $this->sendError($socket, 'Unknown message type: ' . $type);
            }

        } catch (Exception $e) {
            echo "⚠️  Error handling message: " . $e->getMessage() . "\n";
            $this->sendError($socket, 'Server error');
        }
    }

    private function handleAuth($socket, $message) {
        $socketId = (int)$socket;
        
        if (!isset($message['token'])) {
            $this->sendError($socket, 'Token required');
            return;
        }

        try {
            $user = $this->auth->validateToken($message['token']);
            
            if (!$user) {
                $this->sendError($socket, 'Invalid token');
                return;
            }

            // Mark as authenticated
            $this->clients[$socketId]['authenticated'] = true;
            $this->clients[$socketId]['user'] = $user;
            $this->userSockets[$user['user_id']] = $socketId;

            // Update user status to online
            $this->db->query(
                "UPDATE users SET status = 'online', last_seen = NOW() WHERE user_id = ?",
                [$user['user_id']]
            );

            // Send authentication success
            $this->send($socket, [
                'type' => 'authenticated',
                'user' => [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'display_name' => $user['display_name'],
                    'avatar_url' => $user['avatar_url']
                ]
            ]);

            // Broadcast online status
            $this->broadcastUserStatus($user['user_id'], 'online');

            echo "✅ User authenticated: {$user['username']} (ID: {$user['user_id']})\n";

        } catch (Exception $e) {
            echo "⚠️  Auth error: " . $e->getMessage() . "\n";
            $this->sendError($socket, 'Authentication failed');
        }
    }

    private function handleJoinRoom($socket, $message) {
        $socketId = (int)$socket;
        $roomId = $message['roomId'] ?? null;

        if (!$roomId) {
            $this->sendError($socket, 'Room ID required');
            return;
        }

        $client = $this->clients[$socketId];
        $userId = $client['user']['user_id'];

        // Check if user is member of room
        $isMember = $this->db->fetchOne(
            "SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?",
            [$roomId, $userId]
        );

        if (!$isMember) {
            $this->sendError($socket, 'Not a member of this room');
            return;
        }

        // Add to room
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = [];
        }
        
        $this->rooms[$roomId][$socketId] = true;
        $this->clients[$socketId]['rooms'][] = $roomId;

        // Confirm room joined
        $this->send($socket, [
            'type' => 'room_joined',
            'roomId' => $roomId
        ]);

        // Notify other room members
        $this->broadcastToRoom($roomId, [
            'type' => 'user_joined',
            'roomId' => $roomId,
            'user' => [
                'user_id' => $userId,
                'username' => $client['user']['username'],
                'display_name' => $client['user']['display_name'],
                'avatar_url' => $client['user']['avatar_url']
            ]
        ], $socketId);

        echo "📥 User {$client['user']['username']} joined room $roomId\n";
    }

    private function handleLeaveRoom($socket, $message) {
        $socketId = (int)$socket;
        $roomId = $message['roomId'] ?? null;

        if (!$roomId) {
            $this->sendError($socket, 'Room ID required');
            return;
        }

        $this->leaveRoom($socketId, $roomId);
    }

    private function leaveRoom($socketId, $roomId) {
        if (isset($this->rooms[$roomId][$socketId])) {
            $client = $this->clients[$socketId];
            
            unset($this->rooms[$roomId][$socketId]);
            $this->clients[$socketId]['rooms'] = array_diff(
                $this->clients[$socketId]['rooms'],
                [$roomId]
            );

            // Notify room
            $this->broadcastToRoom($roomId, [
                'type' => 'user_left',
                'roomId' => $roomId,
                'user' => [
                    'user_id' => $client['user']['user_id'],
                    'username' => $client['user']['username']
                ]
            ]);

            echo "📤 User {$client['user']['username']} left room $roomId\n";
        }
    }

    private function handleChatMessage($socket, $message) {
        $socketId = (int)$socket;
        $roomId = $message['roomId'] ?? null;
        $content = $message['content'] ?? '';

        if (!$roomId || !$content) {
            $this->sendError($socket, 'Room ID and content required');
            return;
        }

        $client = $this->clients[$socketId];
        $userId = $client['user']['user_id'];

        // Save message to database
        try {
            $messageId = $this->db->insert(
                "INSERT INTO messages (room_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())",
                [$roomId, $userId, $content]
            );

            // Get full message data
            $messageData = $this->db->fetchOne(
                "SELECT m.*, u.username, u.display_name, u.avatar_url 
                 FROM messages m 
                 JOIN users u ON m.user_id = u.user_id 
                 WHERE m.message_id = ?",
                [$messageId]
            );

            // Broadcast to room
            $this->broadcastToRoom($roomId, [
                'type' => 'message',
                'message' => $messageData
            ]);

            echo "💬 Message from {$client['user']['username']} in room $roomId\n";

        } catch (Exception $e) {
            echo "⚠️  Error saving message: " . $e->getMessage() . "\n";
            $this->sendError($socket, 'Failed to save message');
        }
    }

    private function handleTyping($socket, $message) {
        $socketId = (int)$socket;
        $roomId = $message['roomId'] ?? null;
        $isTyping = $message['isTyping'] ?? false;

        if (!$roomId) {
            $this->sendError($socket, 'Room ID required');
            return;
        }

        $client = $this->clients[$socketId];

        // Broadcast typing status to room (except sender)
        $this->broadcastToRoom($roomId, [
            'type' => 'typing',
            'roomId' => $roomId,
            'user' => [
                'user_id' => $client['user']['user_id'],
                'username' => $client['user']['username']
            ],
            'isTyping' => $isTyping
        ], $socketId);
    }

    private function handleReaction($socket, $message) {
        $socketId = (int)$socket;
        $messageId = $message['messageId'] ?? null;
        $emoji = $message['emoji'] ?? null;

        if (!$messageId || !$emoji) {
            $this->sendError($socket, 'Message ID and emoji required');
            return;
        }

        $client = $this->clients[$socketId];
        $userId = $client['user']['user_id'];

        try {
            // Save reaction
            $this->db->query(
                "INSERT INTO message_reactions (message_id, user_id, emoji) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE created_at = NOW()",
                [$messageId, $userId, $emoji]
            );

            // Get room ID for this message
            $msg = $this->db->fetchOne(
                "SELECT room_id FROM messages WHERE message_id = ?",
                [$messageId]
            );

            if ($msg) {
                // Broadcast reaction to room
                $this->broadcastToRoom($msg['room_id'], [
                    'type' => 'reaction',
                    'messageId' => $messageId,
                    'userId' => $userId,
                    'emoji' => $emoji
                ]);
            }

        } catch (Exception $e) {
            echo "⚠️  Error saving reaction: " . $e->getMessage() . "\n";
            $this->sendError($socket, 'Failed to save reaction');
        }
    }

    private function handleReadReceipt($socket, $message) {
        $socketId = (int)$socket;
        $messageId = $message['messageId'] ?? null;

        if (!$messageId) {
            $this->sendError($socket, 'Message ID required');
            return;
        }

        $client = $this->clients[$socketId];
        $userId = $client['user']['user_id'];

        try {
            $this->db->query(
                "INSERT IGNORE INTO message_read_receipts (message_id, user_id) VALUES (?, ?)",
                [$messageId, $userId]
            );

        } catch (Exception $e) {
            echo "⚠️  Error saving read receipt: " . $e->getMessage() . "\n";
        }
    }

    private function broadcastToRoom($roomId, $data, $excludeSocketId = null) {
        if (!isset($this->rooms[$roomId])) {
            return;
        }

        foreach ($this->rooms[$roomId] as $socketId => $active) {
            if ($socketId !== $excludeSocketId && isset($this->clients[$socketId])) {
                $this->send($this->clients[$socketId]['socket'], $data);
            }
        }
    }

    private function broadcastUserStatus($userId, $status) {
        // Broadcast to all authenticated users
        foreach ($this->clients as $client) {
            if ($client['authenticated']) {
                $this->send($client['socket'], [
                    'type' => 'user_status',
                    'userId' => $userId,
                    'status' => $status
                ]);
            }
        }
    }

    private function send($socket, $data) {
        @socket_write($socket, json_encode($data) . "\n");
    }

    private function sendError($socket, $error) {
        $this->send($socket, [
            'type' => 'error',
            'error' => $error
        ]);
    }
}

// Create TCP socket
$address = '0.0.0.0';
$port = 8080;

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($server, $address, $port);
socket_listen($server);

$chatServer = new ChatServer();
$sockets = [$server];

echo "🚀 WebSocket Server started on $address:$port\n";
echo "⏳ Waiting for connections...\n";

while (true) {
    $read = $sockets;
    $write = null;
    $except = null;
    
    if (socket_select($read, $write, $except, 0, 200000) < 1) {
        continue;
    }

    // New connection
    if (in_array($server, $read)) {
        $client = socket_accept($server);
        $sockets[] = $client;
        $chatServer->handleConnection($client);
        
        $key = array_search($server, $read);
        unset($read[$key]);
    }

    // Read from clients
    foreach ($read as $socket) {
        $data = @socket_read($socket, 4096, PHP_NORMAL_READ);
        
        if ($data === false || $data === '') {
            // Connection closed
            $chatServer->handleDisconnection($socket);
            $key = array_search($socket, $sockets);
            unset($sockets[$key]);
            socket_close($socket);
        } else {
            $chatServer->handleMessage($socket, trim($data));
        }
    }
}

socket_close($server);
