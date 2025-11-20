-- SnakkaZ Database Migration: Privacy Features
-- IMPORTANT: Run each statement ONE BY ONE in phpMyAdmin
-- Database: premium123_snakkaz

-- Step 1: Add privacy_level
ALTER TABLE rooms ADD COLUMN privacy_level ENUM('public', 'private', 'password') DEFAULT 'public';

-- Step 2: Add password_hash
ALTER TABLE rooms ADD COLUMN password_hash VARCHAR(255) NULL;

-- Step 3: Add is_encrypted
ALTER TABLE rooms ADD COLUMN is_encrypted TINYINT(1) DEFAULT 0;

-- Step 4: Add max_members
ALTER TABLE rooms ADD COLUMN max_members INT DEFAULT 100;

-- Step 5: Add invite_only
ALTER TABLE rooms ADD COLUMN invite_only TINYINT(1) DEFAULT 0;

-- Step 6: Add index
ALTER TABLE rooms ADD INDEX idx_privacy (privacy_level);

-- Step 7: Update existing rooms
UPDATE rooms SET privacy_level = 'public' WHERE privacy_level IS NULL;

-- Step 7: Create room_invites table
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

-- Step 8: Create room_join_requests table
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
