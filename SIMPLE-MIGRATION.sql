-- SnakkaZ Simple Migration - Without Foreign Keys
-- Run in phpMyAdmin SQL tab

-- Create room_invites table (no foreign keys)
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
  INDEX idx_room (room_id),
  INDEX idx_code (invite_code),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create room_join_requests table (no foreign keys)
CREATE TABLE IF NOT EXISTS room_join_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_request (room_id, user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
