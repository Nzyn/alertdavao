# Cybercrime Report Routing Implementation Guide

## Overview
This document explains the cybercrime report routing system that routes all cybercrime-type reports to a centralized Cybercrime Division police station, with visibility restricted to officers assigned to that division.

## Changes Made

### 1. Database Migration: Add Cybercrime Division Station
**File:** `sql/add_cybercrime_division.sql`

- Adds a new police station record named "Cybercrime Division"
- Station location coordinates set to (0, 0) as it's a virtual/global station (no physical location yet)
- Contact number set to "TBD" (to be determined)

**SQL Command:**
```sql
INSERT INTO police_stations (station_name, address, latitude, longitude, contact_number) 
VALUES ('Cybercrime Division', 'Davao City Police Office - Cybercrime Division', 0, 0, 'TBD');
```

### 2. Report Routing Logic: Cybercrime Detection & Routing
**File:** `UserSide/backends/handleReport.js` (lines 162-234)

#### How It Works:

**Step 1: Crime Type Detection**
When a user submits a report, the system checks if the `crime_types` includes any cybercrime-related keywords:
- "cybercrime"
- "cyber crime"
- "online fraud"
- "hacking"
- "phishing"
- "identity theft"
- "ransomware"

**Step 2: Conditional Routing**

**If Cybercrime Report:**
- Query database for "Cybercrime Division" station
- Assign report directly to Cybercrime Division's `station_id`
- **Ignores location-based assignment** (no geolocation processing needed)
- All cybercrime reports route to the same station globally

**If Non-Cybercrime Report:**
- Use existing location-based routing
  1. Check if `barangay_id` is provided → get station from barangay
  2. If no station assigned, use Haversine formula with coordinates to find nearest barangay/station
  3. Assign report to that station

#### Code Logic:
```javascript
// Check if cybercrime report
const isCybercrime = crimeTypesArray.some(crime => 
  crime.toLowerCase().includes('cybercrime') || 
  crime.toLowerCase().includes('cyber crime') ||
  // ... other cybercrime keywords
);

if (isCybercrime) {
  // Route to Cybercrime Division (global, no location)
  stationId = cybercrimeStationId;
} else {
  // Route based on location (existing logic)
  stationId = getStationByLocation(...);
}
```

### 3. Police Officer Report Filtering
**Existing Files (No Changes Required):**
- `UserSide/backends/getPoliceReports.js`
- `AdminSide/admin/app/Http/Controllers/ReportController.php`

The existing station-based filtering automatically restricts report visibility:
```sql
WHERE r.station_id = ?  -- Police officer can only see reports for their assigned station
```

**How It Works:**
- Police officer assigned to Cybercrime Division can only see cybercrime reports
- Police officer assigned to PS1, PS2, etc. can only see location-based reports for their station
- Cybercrime reports remain invisible to location-based station officers (and vice versa)

## Report Flow Diagram

### Location-Based Crime (Existing)
```
User submits report (e.g., "Theft in Poblacion")
    ↓
Check barangay → Get station_id from barangay → Assign to PS1 (Sta. Ana)
    ↓
PS1 officers see this report (filtered by station_id)
Other stations don't see it
```

### Cybercrime (New)
```
User submits report (e.g., "Online Fraud - Phishing")
    ↓
Detect "cybercrime" keyword in crime_types
    ↓
Route to Cybercrime Division station (globally, no location needed)
    ↓
Cybercrime Division officers see this report
Location-based station officers don't see it
```

## Database Schema

### police_stations Table
```
station_id | station_name        | address                              | latitude | longitude | contact_number
-----------+---------------------+--------------------------------------+----------+-----------+----------------
1          | PS1 Sta. Ana        | M L Quezon Blvd, Poblacion...       | 7.073926 | 125.624608| 233-4884
2          | PS2 San Pedro       | San Pedro St, Poblacion...          | 7.063635 | 125.609838| 226-4835
...
21         | Cybercrime Division | Davao City Police Office - Cybercrime Division | 0 | 0 | TBD
```

### reports Table
```
report_id | user_id | station_id | report_type | title | ...
----------+---------+------------+------------------+-------+-----
1         | 5       | 1          | Theft            | Car Theft | ...
2         | 6       | 21         | Cybercrime       | Online Fraud | ...
```

## Implementation Checklist

- [x] Create database migration SQL file
- [x] Modify handleReport.js to detect cybercrime reports
- [x] Add cybercrime routing logic
- [ ] Run migration: `mysql -u root alertdavao < sql/add_cybercrime_division.sql`
- [ ] Verify Cybercrime Division station is in database
- [ ] Create test cybercrime report in mobile app
- [ ] Verify report is assigned to Cybercrime Division
- [ ] Verify Cybercrime Division officer can see the report
- [ ] Verify location-based officers cannot see cybercrime reports

## Testing Steps

### 1. Add Cybercrime Division to Database
```bash
cd alertdavao/UserSide/backends
mysql -u root alertdavao < ../../sql/add_cybercrime_division.sql
```

### 2. Verify in Database
```sql
SELECT * FROM police_stations WHERE station_name = 'Cybercrime Division';
```

### 3. Test Report Submission (Frontend)
- Open AlertDavao mobile app
- Create new report
- Select crime type: "Cybercrime" or similar
- Submit report
- Check backend logs for: "🚨 Cybercrime report detected! Routing to Cybercrime Division"

### 4. Verify Report Assignment
```sql
SELECT r.report_id, r.title, r.report_type, r.station_id, ps.station_name
FROM reports r
LEFT JOIN police_stations ps ON r.station_id = ps.station_id
WHERE ps.station_name = 'Cybercrime Division'
ORDER BY r.created_at DESC;
```

### 5. Test Police Officer Access
- Login as Cybercrime Division officer
- Should see cybercrime reports only
- Should NOT see location-based reports

- Login as PS1 officer
- Should see PS1 location-based reports only
- Should NOT see cybercrime reports

## Future Enhancements

### Add Real Physical Location for Cybercrime Division
When the physical location is determined, update the coordinates:
```sql
UPDATE police_stations 
SET latitude = [actual_latitude], 
    longitude = [actual_longitude],
    contact_number = '[actual_contact]'
WHERE station_name = 'Cybercrime Division';
```

### Add More Cybercrime Keywords
Edit the detection logic in `handleReport.js` to include additional keywords:
```javascript
const isCybercrime = crimeTypesArray.some(crime => 
  crime.toLowerCase().includes('cybercrime') || 
  crime.toLowerCase().includes('cyber crime') ||
  // Add more keywords here
);
```

### Create Cybercrime Dashboard
- Create specialized dashboard for Cybercrime Division
- Show statistics specific to cybercrime cases
- Track cyber crime trends

### Email Notifications
Send immediate email to Cybercrime Division when new cybercrime report arrives:
```javascript
if (isCybercrime) {
  // Send email notification to cybercrime division officers
  notifyOfficers('cybercrime_new_report', {reportId, title});
}
```

## Key Points

1. **Global Routing:** Cybercrime reports ignore location coordinates and route globally
2. **No Location Processing:** Cybercrime reports don't need barangay assignment
3. **Automatic Filtering:** Existing database queries automatically filter by station_id
4. **Backward Compatible:** Location-based routing unchanged for non-cybercrime reports
5. **Scalable:** Easy to add more specialized units (e.g., "Drug Enforcement Division")

## Related Files

- `UserSide/backends/handleReport.js` - Report submission & routing
- `UserSide/backends/getPoliceReports.js` - Police officer report filtering
- `sql/add_cybercrime_division.sql` - Database migration
- `AdminSide/admin/app/Http/Controllers/ReportController.php` - Laravel backend filtering

## Questions?

For implementation details or troubleshooting:
1. Check backend logs in `handleReport.js` for routing messages
2. Verify database query results with MySQL
3. Test with sample cybercrime report submission
