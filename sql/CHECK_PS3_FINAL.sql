-- Final verification of PS3 Talomo reports
USE alertdavao;

SELECT '✅ PS3 TALOMO FINAL REPORT COUNT:' AS status;
SELECT COUNT(*) as ps3_reports FROM reports WHERE assigned_station_id = 3;

SELECT '';
SELECT '=== PS3 TALOMO REPORTS ===' AS detail;
SELECT 
  r.report_id,
  r.title,
  r.status,
  l.barangay,
  ROUND(l.latitude, 4) as latitude,
  ROUND(l.longitude, 4) as longitude,
  r.assigned_station_id
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
WHERE r.assigned_station_id = 3
ORDER BY r.report_id;

SELECT '';
SELECT '=== DISTRIBUTION ACROSS ALL STATIONS ===' AS summary;
SELECT 
  ps.station_id,
  ps.station_name,
  COUNT(r.report_id) as report_count
FROM police_stations ps
LEFT JOIN reports r ON r.assigned_station_id = ps.station_id
GROUP BY ps.station_id, ps.station_name
ORDER BY report_count DESC;

SELECT '';
SELECT 'TOTAL ASSIGNED REPORTS:' AS final_check;
SELECT COUNT(*) as assigned FROM reports WHERE assigned_station_id IS NOT NULL;
SELECT COUNT(*) as total FROM reports;
