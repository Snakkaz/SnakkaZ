-- ================================================
-- SNAKKAZ CHAT - MySQL Database Schema
-- Optimized for Namecheap Shared Hosting
-- ================================================

-- Drop tables if they exist (for clean install)
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS room_members;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

-- ================================================
-- Users Table
-- ================================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100),
  avatar_url VARCHAR(255),
  status ENUM('online', 'offline', 'away') DEFAULT 'offline',
  last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Rooms Table (Chat Rooms/Conversations)
-- ================================================
CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type ENUM('private', 'group') DEFAULT 'private',
  creator_id INT NOT NULL,
  avatar_url VARCHAR(255),
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_type (type),
  INDEX idx_creator (creator_id),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Room Members Table (Many-to-Many: Users <-> Rooms)
-- ================================================
CREATE TABLE room_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  role ENUM('admin', 'member') DEFAULT 'member',
  muted BOOLEAN DEFAULT FALSE,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_member (room_id, user_id),
  INDEX idx_room (room_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Messages Table
-- ================================================
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  message_type ENUM('text', 'image', 'file', 'audio', 'video') DEFAULT 'text',
  file_url VARCHAR(255),
  file_name VARCHAR(255),
  file_size INT,
  reply_to_id INT NULL,
  is_edited BOOLEAN DEFAULT FALSE,
  is_deleted BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reply_to_id) REFERENCES messages(id) ON DELETE SET NULL,
  INDEX idx_room (room_id),
  INDEX idx_user (user_id),
  INDEX idx_created (created_at),
  INDEX idx_type (message_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Sessions Table (Authentication)
-- ================================================
CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) UNIQUE NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_user (user_id),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Insert Demo Data (Optional - for testing)
-- ================================================

-- Demo User 1
INSERT INTO users (username, email, password_hash, display_name, status) VALUES
('admin', 'admin@snakkaz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'online');

-- Demo User 2
INSERT INTO users (username, email, password_hash, display_name, status) VALUES
('testuser', 'test@snakkaz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', 'offline');

-- Demo Room (Group Chat)
INSERT INTO rooms (name, type, creator_id, description) VALUES
('General Chat', 'group', 1, 'Welcome to SnakkaZ General Chat!');

-- Add members to room
INSERT INTO room_members (room_id, user_id, role) VALUES
(1, 1, 'admin'),
(1, 2, 'member');

-- Demo Messages
INSERT INTO messages (room_id, user_id, content, message_type) VALUES
(1, 1, 'Welcome to SnakkaZ Chat! 🚀', 'text'),
(1, 2, 'Thanks! This is awesome!', 'text');

-- ================================================
-- Views (Optional - for easier queries)
-- ================================================

-- View: User's Rooms with latest message
CREATE VIEW user_rooms_view AS
SELECT 
  r.id AS room_id,
  r.name AS room_name,
  r.type AS room_type,
  r.avatar_url,
  rm.user_id,
  (SELECT content FROM messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) AS last_message,
  (SELECT created_at FROM messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) AS last_message_time,
  (SELECT COUNT(*) FROM messages WHERE room_id = r.id) AS message_count,
  r.updated_at
FROM rooms r
INNER JOIN room_members rm ON r.id = rm.room_id
WHERE r.is_active = TRUE
ORDER BY last_message_time DESC;

-- ================================================
-- Stored Procedures (Optional - for complex operations)
-- ================================================

DELIMITER //

-- Create Private Room between two users
CREATE PROCEDURE create_private_room(
  IN p_user1_id INT,
  IN p_user2_id INT,
  OUT p_room_id INT
)
BEGIN
  -- Check if room already exists
  SELECT r.id INTO p_room_id
  FROM rooms r
  INNER JOIN room_members rm1 ON r.id = rm1.room_id AND rm1.user_id = p_user1_id
  INNER JOIN room_members rm2 ON r.id = rm2.room_id AND rm2.user_id = p_user2_id
  WHERE r.type = 'private'
  LIMIT 1;
  
  -- If not exists, create new room
  IF p_room_id IS NULL THEN
    INSERT INTO rooms (name, type, creator_id) VALUES
    (CONCAT('Private Chat'), 'private', p_user1_id);
    
    SET p_room_id = LAST_INSERT_ID();
    
    -- Add both users as members
    INSERT INTO room_members (room_id, user_id) VALUES
    (p_room_id, p_user1_id),
    (p_room_id, p_user2_id);
  END IF;
END//

DELIMITER ;

-- ================================================
-- Cleanup old sessions (run periodically via cron)
-- ================================================
-- DELETE FROM sessions WHERE expires_at < NOW();
