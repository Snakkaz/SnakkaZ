<?php
// Migration Runner - Run via browser: https://snakkaz.com/migrate.php
require_once 'server/config/database.php';

$sql = <<<'SQL'
-- Migration: Add privacy features to rooms
-- Date: 2025-11-19
-- Description: Adds password protection and privacy levels

ALTER TABLE rooms
ADD COLUMN privacy_level ENUM('public', 'private', 'password') DEFAULT 'public' AFTER type,
ADD COLUMN password_hash VARCHAR(255) NULL AFTER privacy_level,
ADD COLUMN is_encrypted BOOLEAN DEFAULT FALSE AFTER password_hash,
ADD COLUMN max_members INT DEFAULT 100 AFTER is_encrypted,
ADD INDEX idx_privacy (privacy_level);

-- Update existing rooms to be public by default
UPDATE rooms SET privacy_level = 'public' WHERE privacy_level IS NULL;

-- Add invite_only field for private rooms
ALTER TABLE rooms
ADD COLUMN invite_only BOOLEAN DEFAULT FALSE AFTER max_members;

-- Create room_invites table for private room invitations
CREATE TABLE IF NOT EXISTS room_invites (
  invite_id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  invited_by INT NOT NULL,
  invited_user INT NULL,
  invite_code VARCHAR(32) UNIQUE NOT NULL,
  expires_at TIMESTAMP NULL,
  max_uses INT DEFAULT 1,
  current_uses INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (invited_user) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_room (room_id),
  INDEX idx_code (invite_code),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add room_join_requests for approval-based joining
CREATE TABLE IF NOT EXISTS room_join_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_request (room_id, user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SQL;

$db = Database::getInstance();
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "<h2>Running Migration: 001_add_room_privacy.sql</h2>";
echo "<pre>";

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        $db->execute($statement);
        echo "✅ " . substr($statement, 0, 100) . "...\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "   SQL: " . substr($statement, 0, 200) . "...\n";
    }
}

echo "</pre>";
echo "<h3>Migration Complete!</h3>";
?>
