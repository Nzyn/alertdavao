-- Verify IP tracking functionality
-- This script checks the report_ip_tracking table and shows recent IP tracking records

USE alertdavao;

SELECT '\n========================================' AS '';
SELECT 'IP Tracking Verification Report' AS '';
SELECT '========================================\n' AS '';

-- Check if table exists
SELECT 'Checking if table exists...' AS '';
SELECT COUNT(*) AS 'table_exists' FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'alertdavao' AND TABLE_NAME = 'report_ip_tracking';

-- Show table structure
SELECT '\nTable Structure:' AS '';
DESCRIBE report_ip_tracking;

-- Count total IP tracking records
SELECT '\nTotal IP Tracking Records:' AS '';
SELECT COUNT(*) AS total_records FROM report_ip_tracking;

-- Show recent IP tracking records with report details
SELECT '\nRecent IP Tracking Records (Last 10):' AS '';
SELECT 
    ipt.id,
    ipt.report_id,
    r.title AS report_title,
    r.report_type,
    u.email AS submitted_by,
    ipt.ip_address,
    LEFT(ipt.user_agent, 50) AS user_agent_preview,
    ipt.submitted_at
FROM report_ip_tracking ipt
LEFT JOIN reports r ON ipt.report_id = r.report_id
LEFT JOIN users u ON r.user_id = u.id
ORDER BY ipt.submitted_at DESC
LIMIT 10;

-- Count reports by IP address
SELECT '\nReports per IP Address:' AS '';
SELECT 
    ip_address,
    COUNT(*) AS report_count,
    MIN(submitted_at) AS first_submission,
    MAX(submitted_at) AS last_submission
FROM report_ip_tracking
GROUP BY ip_address
ORDER BY report_count DESC
LIMIT 10;

-- Show reports without IP tracking (if any)
SELECT '\nReports without IP tracking:' AS '';
SELECT 
    r.report_id,
    r.title,
    r.created_at,
    'Missing IP tracking' AS status
FROM reports r
LEFT JOIN report_ip_tracking ipt ON r.report_id = ipt.report_id
WHERE ipt.id IS NULL
ORDER BY r.created_at DESC
LIMIT 5;

SELECT '\n========================================' AS '';
SELECT 'Verification Complete' AS '';
SELECT '========================================' AS '';
