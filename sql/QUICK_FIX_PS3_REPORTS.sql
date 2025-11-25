-- QUICK FIX: Assign ALL reports in Talomo/Matina area to PS3 Talomo (station_id = 3)
-- Run this if barangays table is already populated

USE alertdavao;

-- Show current state
SELECT '====== BEFORE FIX ======' AS status;
SELECT 
  COUNT(*) as total_ps3_reports
FROM reports r
WHERE r.assigned_station_id = 3;

-- UPDATE 1: Assign by location name matching
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
SET r.assigned_station_id = 3
WHERE (
  LOWER(l.barangay) LIKE '%talomo%'
  OR LOWER(l.barangay) LIKE '%matina%'
  OR LOWER(l.barangay) LIKE '%baliok%'
  OR LOWER(l.barangay) LIKE '%maa%'
  OR LOWER(l.barangay) LIKE '%ecoland%'
  OR LOWER(l.barangay) LIKE '%eden%'
)
AND l.latitude IS NOT NULL 
AND l.longitude IS NOT NULL;

-- UPDATE 2: Assign by geographic proximity (2km radius around PS3 Talomo)
-- PS3 Talomo: 7.055263, 125.546324
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
SET r.assigned_station_id = 3
WHERE r.assigned_station_id IS NULL
  AND l.latitude IS NOT NULL 
  AND l.longitude IS NOT NULL
  AND (
    6371 * ACOS(
      COS(RADIANS(90 - l.latitude)) * COS(RADIANS(90 - 7.055263)) +
      SIN(RADIANS(90 - l.latitude)) * SIN(RADIANS(90 - 7.055263)) * COS(RADIANS(l.longitude - 125.546324))
    ) < 2
  );

-- Show results
SELECT '' AS status;
SELECT '====== AFTER FIX ======' AS status;
SELECT 
  COUNT(*) as total_ps3_reports
FROM reports r
WHERE r.assigned_station_id = 3;

-- List all PS3 reports
SELECT '';
SELECT '====== PS3 TALOMO REPORTS ======' AS detail;
SELECT 
  r.report_id,
  r.title,
  r.status,
  l.barangay,
  ROUND(l.latitude, 4) as latitude,
  ROUND(l.longitude, 4) as longitude,
  r.assigned_station_id,
  r.created_at
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
WHERE r.assigned_station_id = 3
ORDER BY r.report_id DESC;

-- Show distribution by station
SELECT '';
SELECT '====== REPORTS BY STATION ======' AS summary;
SELECT 
  ps.station_id,
  ps.station_name,
  COUNT(r.report_id) as report_count
FROM police_stations ps
LEFT JOIN reports r ON r.assigned_station_id = ps.station_id
GROUP BY ps.station_id, ps.station_name
ORDER BY report_count DESC;
