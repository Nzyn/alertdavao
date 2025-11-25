-- Migration: Create notification_reads table
-- Purpose: Track which notifications each user has read
-- This allows notifications to persist read/unread status across sessions

CREATE TABLE IF NOT EXISTS notification_reads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  notification_id VARCHAR(50) NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  -- Ensure each user can only mark a notification as read once
  UNIQUE KEY unique_user_notification (user_id, notification_id),
  
  -- Foreign key to users table (if you have it)
  -- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  
  -- Index for faster lookups
  INDEX idx_user_id (user_id),
  INDEX idx_notification_id (notification_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE notification_reads COMMENT = 'Tracks read status of user notifications';
