# Cybercrime Routing - Quick Start Guide

## What Changed?
- ✅ Added "Cybercrime Division" police station
- ✅ Cybercrime reports automatically route to Cybercrime Division (globally, no location needed)
- ✅ Only Cybercrime Division officers can see cybercrime reports
- ✅ Location-based reports unaffected

## 30-Second Setup

```bash
cd alertdavao/UserSide/backends
node setup_cybercrime_division.js
```

Done! Database is ready.

## How It Works

### User Submits Cybercrime Report
```
User selects crime type: "Cybercrime" / "Online Fraud" / "Hacking" / etc.
    ↓
Backend detects cybercrime keyword
    ↓
Routes to Cybercrime Division (globally, ignores location)
```

### Police Officer Access
```
Cybercrime Officer (assigned to Cybercrime Division)
    → Sees ALL cybercrime reports
    → Cannot see location-based reports

PS1 Officer (assigned to PS1 Sta. Ana)
    → Sees PS1 location-based reports only
    → Cannot see cybercrime reports
```

## Detected Cybercrime Keywords

Any report with these keywords routes to Cybercrime Division:
- `cybercrime`
- `cyber crime`
- `online fraud`
- `hacking`
- `phishing`
- `identity theft`
- `ransomware`

Add more keywords in: `UserSide/backends/handleReport.js` (line 165)

## Files Modified/Created

### New Files:
- `sql/add_cybercrime_division.sql` - Database migration
- `UserSide/backends/setup_cybercrime_division.js` - Setup script
- `CYBERCRIME_ROUTING_IMPLEMENTATION.md` - Full documentation
- `CYBERCRIME_TEST_SCENARIOS.md` - Test cases

### Modified Files:
- `UserSide/backends/handleReport.js` - Added cybercrime detection & routing logic

## Testing in 5 Steps

1. **Run setup script**
   ```bash
   node setup_cybercrime_division.js
   ```

2. **Verify database**
   ```sql
   SELECT * FROM police_stations WHERE station_name = 'Cybercrime Division';
   ```

3. **Submit cybercrime report from mobile app**
   - Crime Type: "Cybercrime" or similar
   - Other fields: anything

4. **Check backend logs**
   - Look for: `🚨 Cybercrime report detected! Routing to Cybercrime Division`

5. **Verify database**
   ```sql
   SELECT r.report_id, r.title, r.station_id, ps.station_name
   FROM reports r
   LEFT JOIN police_stations ps ON r.station_id = ps.station_id
   WHERE ps.station_name = 'Cybercrime Division';
   ```
   Should show your report.

## Admin Setup

To test full flow, create these accounts:

### 1. Cybercrime Officer
- **Email:** cybercrime@davao.gov.ph
- **Role:** police
- **Station:** Cybercrime Division
- **Can see:** All cybercrime reports

### 2. Location Officer (e.g., PS1)
- **Email:** ps1@davao.gov.ph
- **Role:** police
- **Station:** PS1 Sta. Ana
- **Can see:** PS1 location-based reports only

### 3. Test User
- **Email:** user@test.com
- **Role:** user
- **Submits:** Reports

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Setup script fails | Check database connection in `.env` |
| Cybercrime report not routing | Verify crime type keyword matches (case-insensitive) |
| Officer can't see reports | Check `users.station_id` matches Cybercrime Division station_id |
| Location reports affected | Should not be - old logic unchanged |

## Backend Code Overview

### Detection Logic
```javascript
// handleReport.js, line 165
const isCybercrime = crimeTypesArray.some(crime => 
  crime.toLowerCase().includes('cybercrime') || 
  crime.toLowerCase().includes('cyber crime') ||
  crime.toLowerCase().includes('online fraud') ||
  // ... more keywords
);
```

### Routing Logic
```javascript
if (isCybercrime) {
  // Route to Cybercrime Division (global)
  stationId = cybercrimeStationId;
} else {
  // Route by location (existing logic)
  stationId = getStationByBarangay(barangay_id) || getStationByCoordinates(lat, lng);
}
```

### Filtering Logic
```javascript
// Existing queries automatically filter by station_id
WHERE r.station_id = ?  // Officer only sees their station's reports
```

## API Endpoint (No Changes)

Report submission endpoint unchanged:
```
POST /api/reports
```

Submit with any `crime_type` containing cybercrime keywords → automatic routing.

## Database Changes

### New Station
```sql
INSERT INTO police_stations (station_name, address, latitude, longitude, contact_number) 
VALUES ('Cybercrime Division', 'Davao City Police Office - Cybercrime Division', 0, 0, 'TBD');
```

### Example Report Flow
```
User submits: crime_type = "Phishing", lat = 7.123, lng = 125.456
↓
Backend detects: isCybercrime = true
↓
Query: SELECT station_id FROM police_stations WHERE station_name = 'Cybercrime Division'
↓
Result: station_id = 21
↓
Create report: INSERT INTO reports ... station_id = 21
↓
Officer sees via: WHERE r.station_id = 21
```

## No Breaking Changes

- ✅ Existing location-based routing works as before
- ✅ Existing police officer filtering unchanged
- ✅ User report submission API unchanged
- ✅ Backward compatible with old reports

## Future Enhancements

- Add Cybercrime Division physical location coordinates when available
- Create dedicated cybercrime dashboard
- Add email alerts for new cybercrime reports
- Add specialized cybercrime investigation tools

## Questions?

See full documentation:
- `CYBERCRIME_ROUTING_IMPLEMENTATION.md` - Complete details
- `CYBERCRIME_TEST_SCENARIOS.md` - Test cases & verification

---

**Status:** ✅ Implementation Complete | Ready for Testing
