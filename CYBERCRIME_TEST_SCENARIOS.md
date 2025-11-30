# Cybercrime Report Routing - Test Scenarios

## Setup Before Testing

### 1. Run Database Migration
```bash
cd alertdavao/UserSide/backends
node setup_cybercrime_division.js
```

Output should show:
```
🚨 Setting up Cybercrime Division Police Station...

📝 Inserting Cybercrime Division station...
✅ Cybercrime Division added successfully!
   Station ID: [NUMBER]
   Station Name: Cybercrime Division
   ...
```

### 2. Verify in Database
```sql
SELECT * FROM police_stations WHERE station_name = 'Cybercrime Division';
```

Expected result:
```
station_id | station_name        | address
-----------+---------------------+-------------------------------------
21         | Cybercrime Division | Davao City Police Office - Cybercrime Division
```

### 3. Create Test User Accounts

#### Officer 1: Cybercrime Division Officer
- Email: cybercrime@davao.gov.ph
- Role: police
- Station: Cybercrime Division (station_id from above)
- Name: Officer Cyber

#### Officer 2: PS1 Station Officer (Location-Based)
- Email: ps1officer@davao.gov.ph
- Role: police
- Station: PS1 Sta. Ana (station_id: 1)
- Name: Officer PS1

#### Regular User: Report Submitter
- Email: user@test.com
- Role: user
- Name: Test User

---

## Test Scenario 1: Submit Cybercrime Report

### Steps:
1. Login as "Test User" in mobile app
2. Click "Submit Report"
3. Fill in details:
   - **Title:** "Phishing Attack - Bank Account Compromise"
   - **Crime Type:** Select "Cybercrime" (or similar)
   - **Description:** "Received phishing email attempting to steal bank credentials"
   - **Date:** Today
   - **Location:** Can be anywhere (ignored for cybercrime)
   - **Is Anonymous:** Yes
   - **Media:** Optional (upload screenshot of phishing email)

4. Submit report

### Expected Backend Behavior:
Backend logs should show:
```
📝 Submitting report: {
  title: "Phishing Attack - Bank Account Compromise",
  crime_types: ["Cybercrime"],
  ...
}

🚨 Cybercrime report detected! Routing to Cybercrime Division (Station ID: 21)

✅ Report created with ID: [NUMBER]
🎉 Report submitted successfully!
```

### Database Verification:
```sql
SELECT r.report_id, r.title, r.report_type, r.station_id, ps.station_name
FROM reports r
LEFT JOIN police_stations ps ON r.station_id = ps.station_id
WHERE r.title = 'Phishing Attack - Bank Account Compromise';
```

Expected result:
```
report_id | title                                      | report_type | station_id | station_name
----------+--------------------------------------------+------------------+-----------+---------------------
42        | Phishing Attack - Bank Account Compromise | Cybercrime  | 21         | Cybercrime Division
```

---

## Test Scenario 2: Officer Access Control - Cybercrime Officer

### Steps:
1. Login as "Officer Cyber" (Cybercrime Division)
2. View dashboard/reports

### Expected Result:
- **Should see:** The cybercrime report from Scenario 1
- **Should NOT see:** Any location-based reports (thefts, assaults, etc.)

### Database Query Officer Sees:
```sql
-- Backend filters by: WHERE r.station_id = 21 (Cybercrime Division)
SELECT r.report_id, r.title, r.report_type, ps.station_name
FROM reports r
LEFT JOIN police_stations ps ON r.station_id = ps.station_id
WHERE r.station_id = 21;  -- Officer Cyber's station
```

Result:
```
report_id | title                                      | report_type | station_name
----------+--------------------------------------------+------------------+---------------------
42        | Phishing Attack - Bank Account Compromise | Cybercrime  | Cybercrime Division
```

---

## Test Scenario 3: Officer Access Control - Location-Based Officer

### Steps:
1. Login as "Officer PS1" (PS1 Sta. Ana)
2. View dashboard/reports

### Expected Result:
- **Should see:** Only location-based reports assigned to PS1
- **Should NOT see:** Cybercrime reports (station_id 21)

### Database Query Officer Sees:
```sql
-- Backend filters by: WHERE r.station_id = 1 (PS1)
SELECT r.report_id, r.title, r.report_type, ps.station_name
FROM reports r
LEFT JOIN police_stations ps ON r.station_id = ps.station_id
WHERE r.station_id = 1;  -- Officer PS1's station
```

Result (only PS1 location-based reports):
```
report_id | title                | report_type | station_name
----------+----------------------+------------------+------------------
1         | Car Theft            | Theft       | PS1 Sta. Ana
5         | House Burglary       | Burglary    | PS1 Sta. Ana
...
```

The cybercrime report (report_id 42) is NOT in this list.

---

## Test Scenario 4: Submit Location-Based Crime Report

### Steps:
1. Login as "Test User" in mobile app
2. Click "Submit Report"
3. Fill in details:
   - **Title:** "Motorcycle Theft"
   - **Crime Type:** Select "Theft"
   - **Description:** "My motorcycle was stolen from the parking lot"
   - **Location:** Poblacion, Davao City
   - **Is Anonymous:** No
   - **Media:** Optional

4. Submit report

### Expected Backend Behavior:
Backend logs should show:
```
📝 Submitting report: {
  title: "Motorcycle Theft",
  crime_types: ["Theft"],
  latitude: 7.073926,
  longitude: 125.624608,
  barangay: "Poblacion",
  ...
}

✅ Station ID assigned from barangay_id: 1  (or via Haversine: PS1)

✅ Report created with ID: [NUMBER]
🎉 Report submitted successfully!
```

### Database Verification:
```sql
SELECT r.report_id, r.title, r.report_type, r.station_id, ps.station_name
FROM reports r
LEFT JOIN police_stations ps ON r.station_id = ps.station_id
WHERE r.title = 'Motorcycle Theft';
```

Expected result:
```
report_id | title             | report_type | station_id | station_name
----------+-------------------+------------------+-----------+------------------
43        | Motorcycle Theft  | Theft       | 1          | PS1 Sta. Ana
```

---

## Test Scenario 5: Verify Report Isolation

### Purpose:
Verify that location-based officers cannot see cybercrime reports and vice versa.

### Test 1: Officer Cyber Cannot See Location Reports
```sql
SELECT COUNT(*) as cybercrime_reports
FROM reports r
WHERE r.station_id = 21;  -- Cybercrime Division

-- Should return: 1 (only cybercrime reports)
-- Should NOT include report_id 43 (the motorcycle theft)
```

### Test 2: Officer PS1 Cannot See Cybercrime Reports
```sql
SELECT COUNT(*) as ps1_reports
FROM reports r
WHERE r.station_id = 1;  -- PS1

-- Should return: 2+ (only location-based reports for PS1)
-- Should NOT include report_id 42 (the cybercrime report)
```

### Test 3: Verify Station Assignments
```sql
SELECT ps.station_name, COUNT(r.report_id) as report_count
FROM police_stations ps
LEFT JOIN reports r ON ps.station_id = r.station_id
GROUP BY ps.station_id
ORDER BY report_count DESC;
```

Expected results:
```
station_name        | report_count
--------------------+-------------
PS1 Sta. Ana        | 2+ (location reports)
Cybercrime Division | 1  (cybercrime reports)
PS2 San Pedro       | 0 or more
PS3 Talomo          | 0 or more
...
```

---

## Test Scenario 6: Multiple Cybercrime Reports

### Steps:
1. Submit 3 different cybercrime reports with different keywords:
   - Report A: Crime Type = "Online Fraud" → Should route to Cybercrime Division
   - Report B: Crime Type = "Hacking" → Should route to Cybercrime Division
   - Report C: Crime Type = "Identity Theft" → Should route to Cybercrime Division

2. Verify all 3 appear in Cybercrime Officer's dashboard

### Database Verification:
```sql
SELECT r.report_id, r.title, r.report_type, ps.station_name
FROM reports r
LEFT JOIN police_stations ps ON r.station_id = ps.station_id
WHERE ps.station_name = 'Cybercrime Division'
ORDER BY r.created_at DESC;
```

Expected: All 3 reports listed with station_name = 'Cybercrime Division'

---

## Test Scenario 7: Edge Cases

### Edge Case 1: Mixed Crime Types
**Report submitted with crime types: ["Theft", "Cybercrime"]**

Expected: Should route to **Cybercrime Division** (because one type is cybercrime)

Backend code:
```javascript
const isCybercrime = crimeTypesArray.some(crime => 
  crime.toLowerCase().includes('cybercrime')  // ANY match triggers cybercrime routing
);
```

### Edge Case 2: Case Sensitivity
**Report with crime type: "CYBERCRIME" or "Cyber Crime" or "cybeRCrime"**

Expected: Should route to Cybercrime Division (case-insensitive detection)

```javascript
crime.toLowerCase().includes('cybercrime')  // toLowerCase handles case
```

### Edge Case 3: Missing Cybercrime Division
**If Cybercrime Division station was deleted from database**

Expected: Report creation fails gracefully with warning logged
```
⚠️  Cybercrime Division station not found in database
```

Solution: Run setup script again to recreate it

---

## Troubleshooting

### Problem: Cybercrime report not routing to division
**Solution:**
1. Check backend logs for "🚨 Cybercrime report detected" message
2. Verify Cybercrime Division exists: `SELECT * FROM police_stations WHERE station_name = 'Cybercrime Division'`
3. Check crime_types value matches one of the detection keywords
4. Add more keywords to detection logic if needed

### Problem: Officer can't see cybercrime reports
**Solution:**
1. Verify officer is assigned to Cybercrime Division: `SELECT * FROM users WHERE id = [officer_id]`
2. Check `users.station_id` = `police_stations.station_id` for Cybercrime Division
3. Backend query filters by `WHERE r.station_id = ?`

### Problem: Location officer seeing cybercrime reports
**Solution:**
1. Verify officer is NOT assigned to Cybercrime Division
2. Check report wasn't manually reassigned
3. Review backend filtering logic

---

## Test Checklist

- [ ] Cybercrime Division station added to database
- [ ] Can submit cybercrime report from mobile app
- [ ] Cybercrime report routes to Cybercrime Division (station_id = 21)
- [ ] Cybercrime officer sees cybercrime reports only
- [ ] Location officer (PS1) doesn't see cybercrime reports
- [ ] Location-based reports still route correctly
- [ ] Multiple cybercrime reports all route to same division
- [ ] Case-insensitive keyword detection works
- [ ] Mixed crime types route to Cybercrime Division
- [ ] Report isolation confirmed (no cross-station visibility)

---

## Performance Notes

- Cybercrime routing is O(1) - direct lookup by station name
- No Haversine formula needed = faster submission
- Location-based routing unchanged = no performance impact
- Filtering query uses indexed `station_id` = efficient

---

## Next Steps After Testing

1. **Assign real officers** to Cybercrime Division
2. **Notify officers** about new specialized reporting system
3. **Train officers** on cybercrime report handling
4. **Monitor** first week for any issues
5. **Update physical location** when Cybercrime Division office location is determined
6. **Add email notifications** to alert officers of new cybercrime reports
