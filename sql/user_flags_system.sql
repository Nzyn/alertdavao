-- =====================================================
-- AlertDavao User Flagging & Restriction System
-- =====================================================
-- This system allows admins to flag and restrict users
-- based on various violation types.
-- =====================================================

USE alertdavao;

-- =====================================================
-- 1. CREATE FLAG_TYPES TABLE
-- =====================================================
-- Stores the different types of violations/flags

CREATE TABLE IF NOT EXISTS flag_types (
    flag_type_id INT AUTO_INCREMENT PRIMARY KEY,
    flag_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique code for the flag type',
    flag_name VARCHAR(100) NOT NULL COMMENT 'Display name of the flag',
    description TEXT COMMENT 'Detailed description of the violation',
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium' COMMENT 'Severity level of the violation',
    default_action ENUM('warning', 'temporary_ban', 'permanent_ban', 'report_restriction', 'review') DEFAULT 'warning' COMMENT 'Default action for this flag type',
    default_duration_days INT DEFAULT NULL COMMENT 'Default restriction duration in days (NULL = permanent)',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether this flag type is currently active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default flag types
INSERT INTO flag_types (flag_code, flag_name, description, severity, default_action, default_duration_days) VALUES
('FALSE_REPORT', 'False or Misleading Reports', 'User has submitted reports that are intentionally false, fabricated, or misleading', 'high', 'temporary_ban', 30),
('SPAM', 'Prank or Spam Submissions', 'User has submitted prank reports, spam content, or repetitive/meaningless submissions', 'medium', 'temporary_ban', 14),
('ABUSIVE', 'Inappropriate or Abusive Behavior', 'User has engaged in inappropriate, vulgar, or abusive behavior through the platform', 'high', 'temporary_ban', 30),
('HARASSMENT', 'Harassment', 'User has harassed other users, police officers, or staff through messages or reports', 'critical', 'permanent_ban', NULL),
('IMPERSONATION', 'Impersonation', 'User has impersonated another person, officer, or official entity', 'critical', 'permanent_ban', NULL),
('INAPPROPRIATE_CONTENT', 'Uploading Inappropriate Content', 'User has uploaded inappropriate, offensive, or illegal images/media', 'high', 'temporary_ban', 60),
('SUSPICIOUS_ACTIVITY', 'Suspicious or Unusual Activity', 'User exhibits patterns of suspicious behavior that warrant monitoring', 'low', 'warning', NULL),
('PRIVACY_VIOLATION', 'Sharing Others\' Sensitive Info', 'User has shared personal information of others without consent', 'high', 'temporary_ban', 30),
('ANONYMOUS_MISUSE', 'Misusing Anonymous Reporting', 'User has misused the anonymous reporting feature for malicious purposes', 'medium', 'report_restriction', 14),
('SYSTEM_ABUSE', 'Attempted System Abuse or Unauthorized Access', 'User has attempted to abuse system features or gain unauthorized access', 'critical', 'permanent_ban', NULL)
ON DUPLICATE KEY UPDATE flag_name=VALUES(flag_name), description=VALUES(description);

-- =====================================================
-- 2. CREATE USER_FLAGS TABLE
-- =====================================================
-- Stores individual flag records for users

CREATE TABLE IF NOT EXISTS user_flags (
    flag_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'The flagged user',
    flag_type_id INT NOT NULL COMMENT 'Type of flag/violation',
    flagged_by INT NOT NULL COMMENT 'Admin/Police who flagged the user',
    reason TEXT NOT NULL COMMENT 'Specific reason for this flag',
    evidence TEXT COMMENT 'Links or references to evidence (report IDs, message IDs, etc.)',
    related_report_id INT DEFAULT NULL COMMENT 'Related report if applicable',
    status ENUM('active', 'reviewed', 'appealed', 'dismissed', 'expired') DEFAULT 'active' COMMENT 'Current status of the flag',
    notes TEXT COMMENT 'Additional admin notes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL COMMENT 'When the flag was reviewed',
    reviewed_by INT DEFAULT NULL COMMENT 'Admin who reviewed the flag',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (flag_type_id) REFERENCES flag_types(flag_type_id) ON DELETE RESTRICT,
    FOREIGN KEY (flagged_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (related_report_id) REFERENCES reports(report_id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_flags_user (user_id),
    INDEX idx_user_flags_status (status),
    INDEX idx_user_flags_type (flag_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. CREATE USER_RESTRICTIONS TABLE
-- =====================================================
-- Stores active restrictions on users

CREATE TABLE IF NOT EXISTS user_restrictions (
    restriction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'The restricted user',
    restriction_type ENUM('warning', 'temporary_ban', 'permanent_ban', 'report_restriction', 'chat_restriction', 'verification_required') NOT NULL COMMENT 'Type of restriction',
    reason TEXT NOT NULL COMMENT 'Reason for the restriction',
    related_flag_id INT DEFAULT NULL COMMENT 'Related flag record',
    applied_by INT NOT NULL COMMENT 'Admin who applied the restriction',
    starts_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the restriction starts',
    expires_at TIMESTAMP NULL COMMENT 'When the restriction expires (NULL = permanent)',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether restriction is currently active',
    lifted_at TIMESTAMP NULL COMMENT 'When the restriction was lifted (if early)',
    lifted_by INT DEFAULT NULL COMMENT 'Admin who lifted the restriction',
    lift_reason TEXT COMMENT 'Reason for lifting the restriction early',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_flag_id) REFERENCES user_flags(flag_id) ON DELETE SET NULL,
    FOREIGN KEY (applied_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (lifted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_restrictions_user (user_id),
    INDEX idx_restrictions_active (is_active),
    INDEX idx_restrictions_type (restriction_type),
    INDEX idx_restrictions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4. CREATE USER_FLAG_APPEALS TABLE
-- =====================================================
-- Stores user appeals against flags/restrictions

CREATE TABLE IF NOT EXISTS user_flag_appeals (
    appeal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'User making the appeal',
    flag_id INT DEFAULT NULL COMMENT 'Flag being appealed',
    restriction_id INT DEFAULT NULL COMMENT 'Restriction being appealed',
    appeal_reason TEXT NOT NULL COMMENT 'User\'s reason for appeal',
    supporting_evidence TEXT COMMENT 'Any evidence user provides',
    status ENUM('pending', 'under_review', 'approved', 'denied') DEFAULT 'pending' COMMENT 'Appeal status',
    admin_response TEXT COMMENT 'Admin\'s response to appeal',
    reviewed_by INT DEFAULT NULL COMMENT 'Admin who reviewed the appeal',
    reviewed_at TIMESTAMP NULL COMMENT 'When the appeal was reviewed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (flag_id) REFERENCES user_flags(flag_id) ON DELETE SET NULL,
    FOREIGN KEY (restriction_id) REFERENCES user_restrictions(restriction_id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_appeals_user (user_id),
    INDEX idx_appeals_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5. ADD COLUMNS TO USERS TABLE
-- =====================================================
-- Add flag-related columns to users table

-- Add is_flagged column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_flagged BOOLEAN DEFAULT FALSE COMMENT 'Whether user has active flags';

-- Add is_restricted column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_restricted BOOLEAN DEFAULT FALSE COMMENT 'Whether user has active restrictions';

-- Add restriction_type column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS current_restriction_type VARCHAR(50) DEFAULT NULL COMMENT 'Current active restriction type';

-- Add flag_count column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS flag_count INT DEFAULT 0 COMMENT 'Total number of flags received';

-- Add last_flagged_at column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS last_flagged_at TIMESTAMP NULL COMMENT 'When user was last flagged';

-- Add trust_score column (higher = more trusted)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS trust_score INT DEFAULT 100 COMMENT 'User trust score (0-100)';

-- =====================================================
-- 6. CREATE VIEW FOR ACTIVE RESTRICTIONS
-- =====================================================

CREATE OR REPLACE VIEW v_active_user_restrictions AS
SELECT 
    ur.restriction_id,
    ur.user_id,
    u.firstname,
    u.lastname,
    u.email,
    ur.restriction_type,
    ur.reason,
    ft.flag_name AS flag_type_name,
    ur.starts_at,
    ur.expires_at,
    CASE 
        WHEN ur.expires_at IS NULL THEN 'Permanent'
        ELSE CONCAT(DATEDIFF(ur.expires_at, NOW()), ' days remaining')
    END AS time_remaining,
    admin.firstname AS applied_by_firstname,
    admin.lastname AS applied_by_lastname
FROM user_restrictions ur
JOIN users u ON ur.user_id = u.id
LEFT JOIN user_flags uf ON ur.related_flag_id = uf.flag_id
LEFT JOIN flag_types ft ON uf.flag_type_id = ft.flag_type_id
LEFT JOIN users admin ON ur.applied_by = admin.id
WHERE ur.is_active = TRUE
  AND (ur.expires_at IS NULL OR ur.expires_at > NOW());

-- =====================================================
-- 7. CREATE VIEW FOR FLAGGED USERS SUMMARY
-- =====================================================

CREATE OR REPLACE VIEW v_flagged_users_summary AS
SELECT 
    u.id AS user_id,
    u.firstname,
    u.lastname,
    u.email,
    u.contact,
    u.is_flagged,
    u.is_restricted,
    u.flag_count,
    u.trust_score,
    u.last_flagged_at,
    COUNT(DISTINCT uf.flag_id) AS active_flags,
    COUNT(DISTINCT ur.restriction_id) AS active_restrictions,
    GROUP_CONCAT(DISTINCT ft.flag_name SEPARATOR ', ') AS flag_types
FROM users u
LEFT JOIN user_flags uf ON u.id = uf.user_id AND uf.status = 'active'
LEFT JOIN flag_types ft ON uf.flag_type_id = ft.flag_type_id
LEFT JOIN user_restrictions ur ON u.id = ur.user_id AND ur.is_active = TRUE
WHERE u.is_flagged = TRUE OR u.is_restricted = TRUE
GROUP BY u.id;

-- =====================================================
-- 8. CREATE STORED PROCEDURE TO FLAG USER
-- =====================================================

DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_flag_user(
    IN p_user_id INT,
    IN p_flag_type_code VARCHAR(50),
    IN p_flagged_by INT,
    IN p_reason TEXT,
    IN p_evidence TEXT,
    IN p_related_report_id INT,
    IN p_apply_default_action BOOLEAN
)
BEGIN
    DECLARE v_flag_type_id INT;
    DECLARE v_default_action VARCHAR(50);
    DECLARE v_default_duration INT;
    DECLARE v_new_flag_id INT;
    DECLARE v_current_trust_score INT;
    DECLARE v_severity VARCHAR(20);
    DECLARE v_trust_penalty INT;
    
    -- Get flag type details
    SELECT flag_type_id, default_action, default_duration_days, severity
    INTO v_flag_type_id, v_default_action, v_default_duration, v_severity
    FROM flag_types 
    WHERE flag_code = p_flag_type_code AND is_active = TRUE;
    
    IF v_flag_type_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid flag type code';
    END IF;
    
    -- Insert the flag
    INSERT INTO user_flags (user_id, flag_type_id, flagged_by, reason, evidence, related_report_id)
    VALUES (p_user_id, v_flag_type_id, p_flagged_by, p_reason, p_evidence, p_related_report_id);
    
    SET v_new_flag_id = LAST_INSERT_ID();
    
    -- Calculate trust penalty based on severity
    SET v_trust_penalty = CASE v_severity
        WHEN 'low' THEN 5
        WHEN 'medium' THEN 10
        WHEN 'high' THEN 20
        WHEN 'critical' THEN 50
        ELSE 10
    END;
    
    -- Update user's flag status and trust score
    UPDATE users 
    SET is_flagged = TRUE,
        flag_count = flag_count + 1,
        last_flagged_at = NOW(),
        trust_score = GREATEST(0, trust_score - v_trust_penalty)
    WHERE id = p_user_id;
    
    -- Apply default restriction if requested
    IF p_apply_default_action = TRUE THEN
        INSERT INTO user_restrictions (
            user_id, 
            restriction_type, 
            reason, 
            related_flag_id, 
            applied_by,
            expires_at
        ) VALUES (
            p_user_id,
            v_default_action,
            CONCAT('Auto-applied due to: ', p_reason),
            v_new_flag_id,
            p_flagged_by,
            CASE WHEN v_default_duration IS NOT NULL 
                 THEN DATE_ADD(NOW(), INTERVAL v_default_duration DAY)
                 ELSE NULL
            END
        );
        
        -- Update user restriction status
        UPDATE users 
        SET is_restricted = TRUE,
            current_restriction_type = v_default_action
        WHERE id = p_user_id;
    END IF;
    
    -- Return the new flag ID
    SELECT v_new_flag_id AS flag_id, 'User flagged successfully' AS message;
END$$

DELIMITER ;

-- =====================================================
-- 9. CREATE STORED PROCEDURE TO LIFT RESTRICTION
-- =====================================================

DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_lift_restriction(
    IN p_restriction_id INT,
    IN p_lifted_by INT,
    IN p_reason TEXT
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_remaining_restrictions INT;
    
    -- Get the user ID
    SELECT user_id INTO v_user_id FROM user_restrictions WHERE restriction_id = p_restriction_id;
    
    IF v_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Restriction not found';
    END IF;
    
    -- Lift the restriction
    UPDATE user_restrictions
    SET is_active = FALSE,
        lifted_at = NOW(),
        lifted_by = p_lifted_by,
        lift_reason = p_reason
    WHERE restriction_id = p_restriction_id;
    
    -- Check if user has any remaining active restrictions
    SELECT COUNT(*) INTO v_remaining_restrictions
    FROM user_restrictions
    WHERE user_id = v_user_id AND is_active = TRUE;
    
    -- Update user's restriction status
    IF v_remaining_restrictions = 0 THEN
        UPDATE users 
        SET is_restricted = FALSE,
            current_restriction_type = NULL
        WHERE id = v_user_id;
    END IF;
    
    SELECT 'Restriction lifted successfully' AS message;
END$$

DELIMITER ;

-- =====================================================
-- 10. CREATE EVENT TO AUTO-EXPIRE RESTRICTIONS
-- =====================================================

-- Enable event scheduler if not already enabled
SET GLOBAL event_scheduler = ON;

DELIMITER $$

CREATE EVENT IF NOT EXISTS evt_expire_restrictions
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    -- Find and expire restrictions that have passed their expiry date
    UPDATE user_restrictions
    SET is_active = FALSE
    WHERE is_active = TRUE
      AND expires_at IS NOT NULL
      AND expires_at <= NOW();
    
    -- Update user status for those with no more active restrictions
    UPDATE users u
    SET is_restricted = FALSE,
        current_restriction_type = NULL
    WHERE is_restricted = TRUE
      AND NOT EXISTS (
          SELECT 1 FROM user_restrictions ur 
          WHERE ur.user_id = u.id AND ur.is_active = TRUE
      );
END$$

DELIMITER ;

-- =====================================================
-- MIGRATION COMPLETE
-- =====================================================

SELECT 'User Flagging & Restriction System tables created successfully!' AS Status;
