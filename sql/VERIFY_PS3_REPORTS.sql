-- Verify reports assigned to PS3 Talomo
USE alertdavao;

-- Check PS3 Talomo station ID
SELECT @ps3_id := station_id FROM police_stations WHERE station_name = 'PS3 Talomo';

-- Show all reports and their current assignments
SELECT '=== ALL REPORTS ===' AS info;
SELECT 
  r.report_id,
  r.title,
  r.station_id,
  r.assigned_station_id,
  l.barangay,
  l.latitude,
  l.longitude,
  ps.station_name
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
LEFT JOIN police_stations ps ON r.assigned_station_id = ps.station_id
WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL
ORDER BY r.report_id;

-- Show which reports should be assigned to PS3 based on barangay
SELECT '=== REPORTS IN PS3 JURISDICTION (By Barangay) ===' AS info;
SELECT 
  r.report_id,
  r.title,
  l.barangay,
  b.barangay_name,
  b.station_id,
  ps_correct.station_name as correct_station
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
LEFT JOIN barangays b ON LOWER(l.barangay) LIKE CONCAT('%', LOWER(b.barangay_name), '%') 
  OR (b.latitude IS NOT NULL AND b.longitude IS NOT NULL AND
    6371 * ACOS(
      COS(RADIANS(90 - b.latitude)) * COS(RADIANS(90 - l.latitude)) +
      SIN(RADIANS(90 - b.latitude)) * SIN(RADIANS(90 - l.latitude)) * COS(RADIANS(b.longitude - l.longitude))
    ) < 2)
LEFT JOIN police_stations ps_correct ON b.station_id = ps_correct.station_id
WHERE b.station_id = 3 AND l.latitude IS NOT NULL AND l.longitude IS NOT NULL;

-- Show summary
SELECT '=== SUMMARY ===' AS info;
SELECT ps.station_name, COUNT(r.report_id) as report_count
FROM police_stations ps
LEFT JOIN reports r ON r.assigned_station_id = ps.station_id
GROUP BY ps.station_id, ps.station_name
ORDER BY report_count DESC;

-- Specific check for reports 27 & 28
SELECT '=== REPORTS 27 & 28 ===' AS info;
SELECT 
  r.report_id,
  r.title,
  r.station_id,
  r.assigned_station_id,
  l.barangay,
  l.latitude,
  l.longitude,
  ps.station_name
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
LEFT JOIN police_stations ps ON r.assigned_station_id = ps.station_id
WHERE r.report_id IN (27, 28);
