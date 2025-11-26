-- =====================================================
-- User Flagging System - Simple Migration
-- AlertDavao Database
-- Run this file directly in phpMyAdmin or MySQL CLI
-- =====================================================

-- 1. Create user_flags table
CREATE TABLE IF NOT EXISTS `user_flags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `violation_type` ENUM('false_report', 'prank_spam', 'inappropriate_content', 'harassment', 'impersonation', 'inappropriate_upload', 'suspicious_activity', 'sensitive_info_sharing', 'anonymous_misuse', 'system_abuse') NOT NULL,
  `severity` ENUM('minor', 'moderate', 'major', 'critical') DEFAULT 'minor',
  `description` TEXT,
  `evidence` JSON DEFAULT NULL,
  `reported_by` INT DEFAULT NULL,
  `related_report_id` INT DEFAULT NULL,
  `status` ENUM('pending', 'confirmed', 'dismissed', 'appealed') DEFAULT 'pending',
  `reviewed_by` INT DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_id (`user_id`),
  INDEX idx_violation_type (`violation_type`),
  INDEX idx_status (`status`),
  INDEX idx_created_at (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create user_restrictions table
CREATE TABLE IF NOT EXISTS `user_restrictions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `restriction_type` ENUM('warning', 'suspended', 'banned') NOT NULL,
  `reason` TEXT,
  `expires_at` DATETIME DEFAULT NULL,
  `can_report` BOOLEAN DEFAULT TRUE,
  `can_comment` BOOLEAN DEFAULT TRUE,
  `can_upload` BOOLEAN DEFAULT TRUE,
  `can_message` BOOLEAN DEFAULT TRUE,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_by` INT DEFAULT NULL,
  `lifted_by` INT DEFAULT NULL,
  `lifted_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_id (`user_id`),
  INDEX idx_is_active (`is_active`),
  INDEX idx_expires_at (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create flag_history table for appeals and actions
CREATE TABLE IF NOT EXISTS `flag_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `flag_id` INT NOT NULL,
  `action` ENUM('created', 'confirmed', 'dismissed', 'appealed', 'appeal_approved', 'appeal_denied') NOT NULL,
  `performed_by` INT DEFAULT NULL,
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_flag_id (`flag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Add flag tracking columns to users table (ignore errors if columns exist)
ALTER TABLE `users` ADD COLUMN `total_flags` INT DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `false_report_count` INT DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `spam_count` INT DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `harassment_count` INT DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `inappropriate_content_count` INT DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `last_flag_date` DATETIME DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `restriction_level` ENUM('none', 'warning', 'suspended', 'banned') DEFAULT 'none';
ALTER TABLE `users` ADD COLUMN `trust_score` INT DEFAULT 100;

-- Done!
SELECT 'Migration complete!' AS status;
