-- Create table to track IP addresses for report submissions
-- This table stores the IP address and timestamp for each report submission
-- for security, audit trail, and tracking purposes

USE alertdavao;

-- =====================================================
-- CREATE REPORT_IP_TRACKING TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS report_ip_tracking (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  ip_address VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 address of the submitter',
  user_agent TEXT COMMENT 'Browser/client user agent string',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the report was submitted',
  FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE,
  INDEX idx_report_id (report_id),
  INDEX idx_ip_address (ip_address),
  INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tracks IP addresses for report submissions';

-- Verify table creation
SELECT 'report_ip_tracking table created successfully!' AS Status;
DESCRIBE report_ip_tracking;
