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

// GET - Get user settings
if ($method === 'GET') {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create default settings if not exists
        if (!$settings) {
            $stmt = $db->prepare("
                INSERT INTO user_settings (user_id) VALUES (?)
            ");
            $stmt->execute([$userId]);
            
            $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$userId]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        Response::success($settings);
        
    } catch (PDOException $e) {
        Response::error('Database error: ' . $e->getMessage(), 500);
    }
}

// PUT - Update user settings
else if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $db = getDBConnection();
        $updates = [];
        $params = [];
        
        // Allowed settings
        $allowedSettings = [
            'theme',
            'notifications_enabled',
            'sound_enabled',
            'push_notifications',
            'email_notifications',
            'language',
            'timezone'
        ];
        
        foreach ($allowedSettings as $setting) {
            if (isset($data[$setting])) {
                $updates[] = "$setting = ?";
                $params[] = $data[$setting];
            }
        }
        
        if (empty($updates)) {
            Response::badRequest('No valid settings to update');
        }
        
        $params[] = $userId;
        
        // Try to update
        $sql = "UPDATE user_settings SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // If no rows affected, insert
        if ($stmt->rowCount() === 0) {
            $fields = array_merge(['user_id'], array_keys(array_filter($data, function($key) use ($allowedSettings) {
                return in_array($key, $allowedSettings);
            }, ARRAY_FILTER_USE_KEY)));
            
            $values = array_merge([$userId], array_values(array_filter($data, function($key) use ($allowedSettings) {
                return in_array($key, $allowedSettings);
            }, ARRAY_FILTER_USE_KEY)));
            
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            $sql = "INSERT INTO user_settings (" . implode(',', $fields) . ") VALUES ($placeholders)";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
        }
        
        // Get updated settings
        $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        Response::success($settings);
        
    } catch (PDOException $e) {
        Response::error('Database error: ' . $e->getMessage(), 500);
    }
}

else {
    Response::error('Method not allowed', 405);
}
