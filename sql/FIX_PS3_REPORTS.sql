-- Fix Reports Assignment to PS3 Talomo
USE alertdavao;

-- First, check PS3 station_id
SET @ps3_station_id = 3;

-- Method 1: Assign by barangay name (for reports with clear barangay names)
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
JOIN barangays b ON (
  LOWER(l.barangay) LIKE CONCAT('%', LOWER(b.barangay_name), '%')
  OR (LOWER(b.barangay_name) IN ('Talomo', 'Matina Pangi', 'Matina Aplaya', 'Matina Crossing', 'Baliok', 'Maa', 'Ecoland', 'Eden', 'Balengina'))
    AND b.station_id = @ps3_station_id
)
SET r.assigned_station_id = @ps3_station_id
WHERE b.station_id = @ps3_station_id
  AND l.latitude IS NOT NULL 
  AND l.longitude IS NOT NULL;

-- Method 2: Assign by geographic proximity (for reports with coordinates)
UPDATE reports r
JOIN locations l ON r.location_id = l.location_id
SET r.assigned_station_id = @ps3_station_id
WHERE l.latitude IS NOT NULL 
  AND l.longitude IS NOT NULL
  AND r.assigned_station_id IS NULL
  AND (
    6371 * ACOS(
      COS(RADIANS(90 - l.latitude)) * COS(RADIANS(90 - 7.055263)) +  -- PS3 Talomo center
      SIN(RADIANS(90 - l.latitude)) * SIN(RADIANS(90 - 7.055263)) * COS(RADIANS(l.longitude - 125.546324))
    ) < 2  -- within 2km of PS3 Talomo
  );

-- Verify PS3 Talomo assignments
SELECT '✅ Reports now assigned to PS3 Talomo:' AS status;
SELECT 
  r.report_id,
  r.title,
  l.barangay,
  l.latitude,
  l.longitude,
  ps.station_name,
  r.status
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
LEFT JOIN police_stations ps ON r.assigned_station_id = ps.station_id
WHERE r.assigned_station_id = @ps3_station_id
ORDER BY r.report_id;

SELECT '';
SELECT 'Total PS3 reports: ' || COUNT(*) FROM reports WHERE assigned_station_id = @ps3_station_id;
