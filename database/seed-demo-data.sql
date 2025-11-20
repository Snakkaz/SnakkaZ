-- SnakkaZ Demo Data
-- Lager default rooms og test messages
-- Kjør dette etter schema.sql

-- Create demo rooms
INSERT INTO rooms (room_name, room_type, description, created_by) VALUES
('General', 'group', 'Welcome to SnakkaZ! Discuss anything here. 👋', 1),
('Random', 'group', 'Random thoughts, memes, and off-topic fun 🎲', 1),
('Tech Talk', 'group', 'Discuss technology, coding, and development 💻', 1),
('Gaming', 'group', 'Video games, esports, and gaming culture 🎮', 1),
('Music', 'group', 'Share your favorite music and discover new tracks 🎵', 1);

-- Create welcome messages (assuming user ID 1 exists)
-- If not, these will be created when first user registers

INSERT INTO messages (room_id, user_id, content, created_at) VALUES
(1, 1, 'Welcome to SnakkaZ! 👋', NOW() - INTERVAL 2 DAY),
(1, 1, 'This is a modern real-time chat platform built with React and PHP.', NOW() - INTERVAL 2 DAY),
(1, 1, 'Feel free to explore the different rooms and start chatting!', NOW() - INTERVAL 2 DAY),
(2, 1, 'This is the random room - anything goes here! 🎉', NOW() - INTERVAL 1 DAY),
(3, 1, 'Tech enthusiasts welcome! Share your latest projects here. 💡', NOW() - INTERVAL 1 DAY),
(4, 1, 'What games are you playing right now? 🎮', NOW() - INTERVAL 1 DAY),
(5, 1, 'Drop your Spotify playlists here! 🎧', NOW() - INTERVAL 1 DAY);

-- Auto-add users to General room when they register
-- This will be handled by the registration endpoint

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_messages_room_created ON messages(room_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_messages_user ON messages(user_id);
CREATE INDEX IF NOT EXISTS idx_room_members_user ON room_members(user_id);
CREATE INDEX IF NOT EXISTS idx_room_members_room ON room_members(room_id);
CREATE INDEX IF NOT EXISTS idx_sessions_token ON sessions(token);
CREATE INDEX IF NOT EXISTS idx_sessions_user ON sessions(user_id);

-- Add online status column to users if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('online', 'away', 'offline') DEFAULT 'offline';
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL DEFAULT NULL;

-- Create message_reactions table
CREATE TABLE IF NOT EXISTS message_reactions (
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

-- Create typing_indicators table (for real-time typing)
CREATE TABLE IF NOT EXISTS typing_indicators (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_room_user (room_id, user_id),
  INDEX idx_room_typing (room_id, started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create uploads table
CREATE TABLE IF NOT EXISTS uploads (
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

-- Add attachment support to messages
ALTER TABLE messages ADD COLUMN IF NOT EXISTS attachment_id INT NULL;
ALTER TABLE messages ADD CONSTRAINT fk_message_attachment 
  FOREIGN KEY (attachment_id) REFERENCES uploads(upload_id) ON DELETE SET NULL;

-- User settings table
CREATE TABLE IF NOT EXISTS user_settings (
  user_id INT PRIMARY KEY,
  theme ENUM('light', 'dark', 'auto') DEFAULT 'auto',
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

-- Room settings
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS icon VARCHAR(50) DEFAULT '💬';
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS is_public BOOLEAN DEFAULT TRUE;
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS max_members INT DEFAULT 1000;

-- Update existing rooms with icons
UPDATE rooms SET icon = '👋' WHERE room_name = 'General';
UPDATE rooms SET icon = '🎲' WHERE room_name = 'Random';
UPDATE rooms SET icon = '💻' WHERE room_name = 'Tech Talk';
UPDATE rooms SET icon = '🎮' WHERE room_name = 'Gaming';
UPDATE rooms SET icon = '🎵' WHERE room_name = 'Music';

-- Add read receipts
CREATE TABLE IF NOT EXISTS message_read_receipts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (message_id) REFERENCES messages(message_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY unique_message_user (message_id, user_id),
  INDEX idx_message_read (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create view for unread message counts
CREATE OR REPLACE VIEW unread_message_counts AS
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

-- Success message
SELECT 'Demo data seeded successfully! 🎉' as status;
