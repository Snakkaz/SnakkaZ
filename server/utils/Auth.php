<?php
/**
 * SnakkaZ Chat - Authentication Helper
 * JWT-based authentication (simplified for shared hosting)
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Response.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate JWT token (simplified version)
     */
    public function generateToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + JWT_EXPIRY);
        
        // Store token in sessions table
        $sql = "INSERT INTO sessions (user_id, token, expires_at) 
                VALUES (?, ?, ?)";
        
        $params = [
            $userId,
            $token,
            $expiresAt
        ];
        
        $this->db->query($sql, $params);
        
        return $token;
    }
    
    /**
     * Validate token and return user
     */
    public function validateToken($token) {
        if (empty($token)) {
            return null;
        }
        
        $sql = "SELECT s.*, u.user_id, u.username, u.email, u.display_name, u.avatar_url, u.status
                FROM sessions s
                INNER JOIN users u ON s.user_id = u.user_id
                WHERE s.token = ? AND s.expires_at > NOW()
                LIMIT 1";
        
        $session = $this->db->fetchOne($sql, [$token]);
        
        if (!$session) {
            return null;
        }
        
        // Add id alias for backend compatibility (some code uses $user['id'])
        $session['id'] = $session['user_id'];
        
        // Update user status to online
        $this->updateUserStatus($session['user_id'], 'online');
        
        return $session;
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser() {
        $token = $this->getBearerToken();
        
        if (!$token) {
            Response::unauthorized('No token provided');
        }
        
        $user = $this->validateToken($token);
        
        if (!$user) {
            Response::unauthorized('Invalid or expired token');
        }
        
        return $user;
    }
    
    /**
     * Authenticate request and return user or null
     * Does not terminate on failure
     */
    public function authenticateRequest() {
        $token = $this->getBearerToken();
        
        if (!$token) {
            return null;
        }
        
        return $this->validateToken($token);
    }
    
    /**
     * Logout (delete session)
     */
    public function logout($token) {
        $sql = "DELETE FROM sessions WHERE token = ?";
        return $this->db->execute($sql, [$token]);
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Update user online status
     */
    private function updateUserStatus($userId, $status) {
        $sql = "UPDATE users SET status = ?, last_seen = NOW() WHERE user_id = ?";
        $this->db->execute($sql, [$status, $userId]);
    }
    
    /**
     * Get Bearer token from Authorization header
     */
    private function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Get Authorization header
     */
    private function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)), 
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
    
    /**
     * Clean expired sessions (run periodically)
     */
    public function cleanExpiredSessions() {
        $sql = "DELETE FROM sessions WHERE expires_at < NOW()";
        return $this->db->execute($sql);
    }
}
