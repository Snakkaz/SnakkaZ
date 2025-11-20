<?php
namespace SnakkaZ\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $rooms;
    protected $userConnections;
    protected $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
        $this->userConnections = [];
        
        // Database connection
        require_once __DIR__ . '/../config/database.php';
        $this->db = getDBConnection();
        
        echo "WebSocket server initialized\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'connection',
            'status' => 'connected',
            'connectionId' => $conn->resourceId,
            'timestamp' => time()
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['type'])) {
            return;
        }

        echo "Message received: {$data['type']} from {$from->resourceId}\n";

        switch ($data['type']) {
            case 'authenticate':
                $this->handleAuthentication($from, $data);
                break;
                
            case 'join_room':
                $this->handleJoinRoom($from, $data);
                break;
                
            case 'leave_room':
                $this->handleLeaveRoom($from, $data);
                break;
                
            case 'message':
                $this->handleMessage($from, $data);
                break;
                
            case 'typing':
                $this->handleTyping($from, $data);
                break;
                
            case 'reaction':
                $this->handleReaction($from, $data);
                break;
                
            case 'read_receipt':
                $this->handleReadReceipt($from, $data);
                break;
                
            case 'ping':
                $this->handlePing($from, $data);
                break;
                
            default:
                echo "Unknown message type: {$data['type']}\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // User disconnected - update status
        if (isset($conn->userId)) {
            $this->updateUserStatus($conn->userId, 'offline');
            $this->broadcastUserStatus($conn->userId, 'offline');
            
            // Remove from user connections
            unset($this->userConnections[$conn->userId]);
        }
        
        // Remove from rooms
        foreach ($this->rooms as $roomId => &$room) {
            if (isset($room['connections'][$conn->resourceId])) {
                unset($room['connections'][$conn->resourceId]);
            }
        }
        
        $this->clients->detach($conn);
        echo "Connection closed: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    // Authentication
    private function handleAuthentication(ConnectionInterface $conn, $data) {
        if (!isset($data['token'])) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Token required']));
            return;
        }

        // Verify token in database
        $stmt = $this->db->prepare("
            SELECT s.user_id, u.username, u.display_name, u.avatar_url 
            FROM sessions s 
            JOIN users u ON u.user_id = s.user_id 
            WHERE s.token = ? AND s.expires_at > NOW()
        ");
        $stmt->execute([$data['token']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Invalid token']));
            return;
        }

        // Store user info on connection
        $conn->userId = $user['user_id'];
        $conn->username = $user['username'];
        $conn->displayName = $user['display_name'];
        
        // Store connection for this user
        $this->userConnections[$user['user_id']] = $conn;

        // Update user status to online
        $this->updateUserStatus($user['user_id'], 'online');

        // Send success
        $conn->send(json_encode([
            'type' => 'authenticated',
            'user' => [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'displayName' => $user['display_name'],
                'avatar' => $user['avatar_url']
            ]
        ]));

        // Broadcast user online status
        $this->broadcastUserStatus($user['user_id'], 'online');

        echo "User authenticated: {$user['username']} (ID: {$user['user_id']})\n";
    }

    // Join room
    private function handleJoinRoom(ConnectionInterface $conn, $data) {
        if (!isset($conn->userId) || !isset($data['roomId'])) {
            return;
        }

        $roomId = $data['roomId'];

        // Verify user is member of room
        $stmt = $this->db->prepare("SELECT 1 FROM room_members WHERE room_id = ? AND user_id = ?");
        $stmt->execute([$roomId, $conn->userId]);
        
        if (!$stmt->fetch()) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Not a member of this room']));
            return;
        }

        // Add to room
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = ['connections' => []];
        }
        
        $this->rooms[$roomId]['connections'][$conn->resourceId] = $conn;

        // Send confirmation
        $conn->send(json_encode([
            'type' => 'room_joined',
            'roomId' => $roomId
        ]));

        // Notify others in room
        $this->broadcastToRoom($roomId, [
            'type' => 'user_joined',
            'roomId' => $roomId,
            'user' => [
                'id' => $conn->userId,
                'username' => $conn->username,
                'displayName' => $conn->displayName
            ]
        ], $conn->resourceId);

        echo "User {$conn->username} joined room {$roomId}\n";
    }

    // Leave room
    private function handleLeaveRoom(ConnectionInterface $conn, $data) {
        if (!isset($data['roomId'])) {
            return;
        }

        $roomId = $data['roomId'];

        if (isset($this->rooms[$roomId]['connections'][$conn->resourceId])) {
            unset($this->rooms[$roomId]['connections'][$conn->resourceId]);

            // Notify others
            $this->broadcastToRoom($roomId, [
                'type' => 'user_left',
                'roomId' => $roomId,
                'userId' => $conn->userId
            ]);

            echo "User {$conn->username} left room {$roomId}\n";
        }
    }

    // Message
    private function handleMessage(ConnectionInterface $conn, $data) {
        if (!isset($conn->userId) || !isset($data['roomId']) || !isset($data['content'])) {
            return;
        }

        // Save to database
        $stmt = $this->db->prepare("
            INSERT INTO messages (room_id, user_id, content, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$data['roomId'], $conn->userId, $data['content']]);
        $messageId = $this->db->lastInsertId();

        // Prepare message object
        $message = [
            'type' => 'message',
            'messageId' => $messageId,
            'roomId' => $data['roomId'],
            'content' => $data['content'],
            'senderId' => $conn->userId,
            'senderName' => $conn->displayName ?? $conn->username,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Broadcast to room
        $this->broadcastToRoom($data['roomId'], $message);

        echo "Message sent in room {$data['roomId']} by {$conn->username}\n";
    }

    // Typing indicator
    private function handleTyping(ConnectionInterface $conn, $data) {
        if (!isset($conn->userId) || !isset($data['roomId'])) {
            return;
        }

        $isTyping = $data['isTyping'] ?? true;

        if ($isTyping) {
            // Update typing indicator in DB
            $stmt = $this->db->prepare("
                INSERT INTO typing_indicators (room_id, user_id, started_at) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE started_at = NOW()
            ");
            $stmt->execute([$data['roomId'], $conn->userId]);
        } else {
            // Remove typing indicator
            $stmt = $this->db->prepare("DELETE FROM typing_indicators WHERE room_id = ? AND user_id = ?");
            $stmt->execute([$data['roomId'], $conn->userId]);
        }

        // Broadcast to room
        $this->broadcastToRoom($data['roomId'], [
            'type' => 'typing',
            'roomId' => $data['roomId'],
            'userId' => $conn->userId,
            'username' => $conn->displayName ?? $conn->username,
            'isTyping' => $isTyping
        ], $conn->resourceId);
    }

    // Reaction
    private function handleReaction(ConnectionInterface $conn, $data) {
        if (!isset($conn->userId) || !isset($data['messageId']) || !isset($data['emoji'])) {
            return;
        }

        // Toggle reaction in database
        $stmt = $this->db->prepare("
            INSERT INTO message_reactions (message_id, user_id, emoji) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE created_at = NOW()
        ");
        $stmt->execute([$data['messageId'], $conn->userId, $data['emoji']]);

        // Get room ID for this message
        $stmt = $this->db->prepare("SELECT room_id FROM messages WHERE message_id = ?");
        $stmt->execute([$data['messageId']]);
        $roomId = $stmt->fetchColumn();

        if ($roomId) {
            // Broadcast to room
            $this->broadcastToRoom($roomId, [
                'type' => 'reaction',
                'messageId' => $data['messageId'],
                'userId' => $conn->userId,
                'emoji' => $data['emoji']
            ]);
        }
    }

    // Read receipt
    private function handleReadReceipt(ConnectionInterface $conn, $data) {
        if (!isset($conn->userId) || !isset($data['messageId'])) {
            return;
        }

        // Mark as read
        $stmt = $this->db->prepare("
            INSERT INTO message_read_receipts (message_id, user_id, read_at) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE read_at = NOW()
        ");
        $stmt->execute([$data['messageId'], $conn->userId]);
    }

    // Ping (heartbeat)
    private function handlePing(ConnectionInterface $conn, $data) {
        if (isset($conn->userId)) {
            $this->updateUserStatus($conn->userId, 'online');
        }
        
        $conn->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
    }

    // Helper: Broadcast to room
    private function broadcastToRoom($roomId, $message, $excludeConnectionId = null) {
        if (!isset($this->rooms[$roomId])) {
            return;
        }

        $messageJson = json_encode($message);
        
        foreach ($this->rooms[$roomId]['connections'] as $connId => $conn) {
            if ($excludeConnectionId && $connId == $excludeConnectionId) {
                continue;
            }
            $conn->send($messageJson);
        }
    }

    // Helper: Update user status
    private function updateUserStatus($userId, $status) {
        $stmt = $this->db->prepare("UPDATE users SET status = ?, last_seen = NOW() WHERE user_id = ?");
        $stmt->execute([$status, $userId]);
    }

    // Helper: Broadcast user status
    private function broadcastUserStatus($userId, $status) {
        $message = json_encode([
            'type' => 'user_status',
            'userId' => $userId,
            'status' => $status,
            'timestamp' => time()
        ]);

        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }
}
