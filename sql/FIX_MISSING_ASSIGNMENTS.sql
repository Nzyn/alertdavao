-- Fix missing report assignments by barangay name
USE alertdavao;

-- First show what's missing
SELECT 'MISSING ASSIGNMENTS:' AS status;
SELECT r.report_id, l.barangay, l.latitude, l.longitude, r.assigned_station_id
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
WHERE r.assigned_station_id IS NULL
ORDER BY r.report_id;

-- Assign by barangay name matching
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
SET r.assigned_station_id = 3
WHERE LOWER(l.barangay) IN ('talomo', 'matina pangi', 'matina aplaya', 'matina crossing', 'baliok', 'maa', 'ecoland', 'eden', 'balengina')
  AND r.assigned_station_id IS NULL;

-- For coordinates that match Talomo area (around 7.084, 125.600)
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
SET r.assigned_station_id = 3
WHERE r.assigned_station_id IS NULL
  AND l.latitude BETWEEN 7.08 AND 7.09
  AND l.longitude BETWEEN 125.59 AND 125.61;

-- For Toril area
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
SET r.assigned_station_id = 8
WHERE LOWER(l.barangay) = 'toril' AND r.assigned_station_id IS NULL;

-- For Calinan area
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
SET r.assigned_station_id = 10
WHERE LOWER(l.barangay) LIKE '%calinan%' AND r.assigned_station_id IS NULL;

-- Show updated results
SELECT '' AS status;
SELECT 'AFTER FIX:' AS status;
SELECT 
  ps.station_name,
  COUNT(r.report_id) as report_count
FROM police_stations ps
LEFT JOIN reports r ON r.assigned_station_id = ps.station_id
WHERE r.assigned_station_id IS NOT NULL
GROUP BY ps.station_id, ps.station_name
ORDER BY report_count DESC;

-- Show still unassigned
SELECT '' AS status;
SELECT 'STILL UNASSIGNED:' AS status;
SELECT r.report_id, l.barangay, l.latitude, l.longitude
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
WHERE r.assigned_station_id IS NULL
ORDER BY r.report_id;
