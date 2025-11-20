-- Add current user to all public rooms
-- Run this in phpMyAdmin

-- Get all room IDs
SET @user_id = 5; -- Change this to your user_id

-- Add user to all public rooms
INSERT IGNORE INTO room_members (room_id, user_id, role)
SELECT room_id, @user_id, 'member'
FROM rooms
WHERE is_public = TRUE;

-- Verify memberships
SELECT 
    r.room_name,
    r.icon,
    rm.role,
    rm.joined_at
FROM room_members rm
JOIN rooms r ON rm.room_id = r.room_id
WHERE rm.user_id = @user_id;
