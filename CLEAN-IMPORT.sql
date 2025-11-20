-- =====================================================
-- SnakkaZ CLEAN Database Setup
-- Drops all existing tables first, then recreates
-- For MariaDB 11.4.8
-- =====================================================

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop all existing tables
DROP TABLE IF EXISTS message_read_receipts;
DROP TABLE IF EXISTS message_reactions;
DROP TABLE IF EXISTS typing_indicators;
DROP TABLE IF EXISTS user_settings;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS uploads;
DROP TABLE IF EXISTS room_members;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

-- Drop views if they exist
DROP VIEW IF EXISTS unread_message_counts;
DROP VIEW IF EXISTS room_messages_view;
DROP VIEW IF EXISTS user_rooms_view;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CORE TABLES
-- =====================================================

-- Users table
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100),
  avatar_url VARCHAR(512),
  bio TEXT,
  status ENUM('online', 'away', 'offline') DEFAULT 'offline',
  last_seen TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE sessions (
  session_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) UNIQUE NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_user_id (user_id),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rooms table
CREATE TABLE rooms (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  room_name VARCHAR(100) NOT NULL,
  room_type ENUM('private', 'group') DEFAULT 'group',
  description TEXT,
  icon VARCHAR(50) DEFAULT '💬',
  is_public BOOLEAN DEFAULT TRUE,
  max_members INT DEFAULT 1000,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
  INDEX idx_room_type (room_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Room members
CREATE TABLE room_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  role ENUM('admin', 'moderator', 'member') DEFAULT 'member',
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_room_user (room_id, user_id),
  INDEX idx_user_rooms (user_id),
  INDEX idx_room_users (room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Uploads table
CREATE TABLE uploads (
  upload_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  original_filename VARCHAR(255) NOT NULL,
  file_type VARCHAR(50) NOT NULL,
  file_size INT NOT NULL,
  file_path VARCHAR(512) NOT NULL,
  thumbnail_path VARCHAR(512) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX idx_user_uploads (user_id, created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table
CREATE TABLE messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  message_type ENUM('text', 'image', 'file', 'system') DEFAULT 'text',
  attachment_id INT NULL,
  is_edited BOOLEAN DEFAULT FALSE,
  is_deleted BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (attachment_id) REFERENCES uploads(upload_id) ON DELETE SET NULL,
  INDEX idx_room_messages (room_id, created_at DESC),
  INDEX idx_user_messages (user_id),
  FULLTEXT INDEX idx_message_search (content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Message reactions
CREATE TABLE message_reactions (
  reaction_id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  emoji VARCHAR(10) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (message_id) REFERENCES messages(message_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_message_emoji (message_id, user_id, emoji),
  INDEX idx_message_reactions (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Typing indicators
CREATE TABLE typing_indicators (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_room_user (room_id, user_id),
  INDEX idx_room_typing (room_id, started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User settings
CREATE TABLE user_settings (
  user_id INT PRIMARY KEY,
  theme ENUM('light', 'dark', 'auto') DEFAULT 'dark',
  notifications_enabled BOOLEAN DEFAULT TRUE,
  sound_enabled BOOLEAN DEFAULT TRUE,
  push_notifications BOOLEAN DEFAULT FALSE,
  email_notifications BOOLEAN DEFAULT TRUE,
  language VARCHAR(10) DEFAULT 'en',
  timezone VARCHAR(50) DEFAULT 'UTC',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Message read receipts
CREATE TABLE message_read_receipts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (message_id) REFERENCES messages(message_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_message_user (message_id, user_id),
  INDEX idx_message_read (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DEMO DATA
-- =====================================================

-- System user
INSERT INTO users (user_id, username, email, password_hash, display_name, status)
VALUES (1, 'system', 'system@snakkaz.com', '$2y$10$dummyhashforsystemuser1234567890', 'SnakkaZ Bot', 'online');

-- Demo rooms
INSERT INTO rooms (room_name, room_type, description, icon, created_by) VALUES
('General', 'group', 'Welcome to SnakkaZ! Discuss anything here. 👋', '👋', 1),
('Random', 'group', 'Random thoughts, memes, and off-topic fun 🎲', '🎲', 1),
('Tech Talk', 'group', 'Discuss technology, coding, and development 💻', '💻', 1),
('Gaming', 'group', 'Video games, esports, and gaming culture 🎮', '🎮', 1),
('Music', 'group', 'Share your favorite music and discover new tracks 🎵', '🎵', 1);

-- Welcome messages
INSERT INTO messages (room_id, user_id, content, message_type, created_at) VALUES
(1, 1, 'Welcome to SnakkaZ! 👋', 'system', NOW() - INTERVAL 2 DAY),
(1, 1, 'This is a modern real-time chat platform built with React and PHP.', 'text', NOW() - INTERVAL 2 DAY),
(1, 1, 'Feel free to explore the different rooms and start chatting!', 'text', NOW() - INTERVAL 2 DAY),
(2, 1, 'This is the random room - anything goes here! 🎉', 'system', NOW() - INTERVAL 1 DAY),
(3, 1, 'Tech enthusiasts welcome! Share your latest projects here. 💡', 'system', NOW() - INTERVAL 1 DAY),
(4, 1, 'What games are you playing right now? 🎮', 'text', NOW() - INTERVAL 1 DAY),
(5, 1, 'Drop your Spotify playlists here! 🎧', 'text', NOW() - INTERVAL 1 DAY);

-- =====================================================
-- VIEWS
-- =====================================================

CREATE VIEW unread_message_counts AS
SELECT 
  rm.user_id,
  rm.room_id,
  COUNT(m.message_id) as unread_count
FROM room_members rm
JOIN messages m ON m.room_id = rm.room_id
LEFT JOIN message_read_receipts mrr ON mrr.message_id = m.message_id AND mrr.user_id = rm.user_id
WHERE mrr.id IS NULL
  AND m.user_id != rm.user_id
  AND m.created_at > rm.joined_at
GROUP BY rm.user_id, rm.room_id;

CREATE VIEW room_messages_view AS
SELECT 
  m.message_id,
  m.room_id,
  m.user_id,
  m.content,
  m.message_type,
  m.attachment_id,
  m.is_edited,
  m.is_deleted,
  m.created_at,
  m.updated_at,
  u.username,
  u.display_name,
  u.avatar_url,
  r.room_name,
  r.room_type,
  up.file_path as attachment_url,
  up.thumbnail_path as attachment_thumbnail
FROM messages m
JOIN users u ON m.user_id = u.user_id
JOIN rooms r ON m.room_id = r.room_id
LEFT JOIN uploads up ON m.attachment_id = up.upload_id
WHERE m.is_deleted = FALSE;

CREATE VIEW user_rooms_view AS
SELECT 
  rm.user_id,
  r.room_id,
  r.room_name,
  r.room_type,
  r.description,
  r.icon,
  r.created_at as room_created_at,
  rm.role as user_role,
  rm.joined_at,
  (SELECT COUNT(*) FROM room_members WHERE room_id = r.room_id) as member_count,
  (SELECT content FROM messages WHERE room_id = r.room_id ORDER BY created_at DESC LIMIT 1) as last_message,
  (SELECT created_at FROM messages WHERE room_id = r.room_id ORDER BY created_at DESC LIMIT 1) as last_message_at
FROM room_members rm
JOIN rooms r ON rm.room_id = r.room_id;

-- =====================================================
-- SUCCESS
-- =====================================================

SELECT 
  '✅ Database setup complete!' as status,
  COUNT(*) as total_rooms,
  (SELECT COUNT(*) FROM messages) as total_messages,
  '🎉 SnakkaZ is ready to use!' as message
FROM rooms;
