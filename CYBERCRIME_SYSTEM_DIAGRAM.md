# Cybercrime Routing System - Visual Diagrams

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                    ALERTDAVAO REPORT SYSTEM v2.0                    │
│              (with Cybercrime Division Routing)                      │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────┐
│                         MOBILE APP (User)                            │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ Submit Report                                               │   │
│  │ ├─ Title                                                    │   │
│  │ ├─ Crime Type(s) ← KEY: "cybercrime" or location-based     │   │
│  │ ├─ Description                                              │   │
│  │ ├─ Location (latitude, longitude)                          │   │
│  │ └─ Media (optional)                                         │   │
│  └──────────────────┬──────────────────────────────────────────┘   │
└─────────────────────┼──────────────────────────────────────────────┘
                      │
                      │ POST /api/reports
                      ↓
┌──────────────────────────────────────────────────────────────────────┐
│                    BACKEND SERVER (Node.js)                          │
│                  (handleReport.js - NEW LOGIC)                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Receive Report                                                 │ │
│  └────────┬─────────────────────────────────────────────────────┘ │
│           ↓                                                         │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Validate & Encrypt                                             │ │
│  │ ├─ Check user restrictions                                     │ │
│  │ ├─ Encrypt description, barangay, address                      │ │
│  │ └─ Parse crime_types array                                     │ │
│  └────────┬─────────────────────────────────────────────────────┘ │
│           ↓                                                         │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ ★ NEW: DETECT CRIME TYPE ★                                    │ │
│  │                                                                 │ │
│  │  const isCybercrime = crimeTypesArray.some(crime =>            │ │
│  │    crime.toLowerCase().includes('cybercrime') ||               │ │
│  │    crime.toLowerCase().includes('cyber crime') ||              │ │
│  │    crime.toLowerCase().includes('online fraud') ||             │ │
│  │    crime.toLowerCase().includes('hacking') ||                  │ │
│  │    crime.toLowerCase().includes('phishing') ||                 │ │
│  │    crime.toLowerCase().includes('identity theft') ||           │ │
│  │    crime.toLowerCase().includes('ransomware')                  │ │
│  │  )                                                              │ │
│  └────────┬─────────────────────────────────────────────────────┘ │
│           ↓                                                         │
│      ┌────┴─────────────────────────────────────┐                 │
│      │                                           │                 │
│    YES (Cybercrime)                          NO (Location-Based)   │
│      │                                           │                 │
│      ↓                                           ↓                 │
│  ┌────────────────────────┐        ┌────────────────────────────┐ │
│  │ CYBERCRIME ROUTING     │        │ LOCATION-BASED ROUTING     │ │
│  │ (NEW)                  │        │ (EXISTING - UNCHANGED)     │ │
│  │                        │        │                            │ │
│  │ Query:                 │        │ 1. Get barangay_id         │ │
│  │ SELECT station_id      │        │ 2. Query barangays table   │ │
│  │ FROM police_stations   │        │    for station_id          │ │
│  │ WHERE station_name =   │        │ 3. If no barangay_id,      │ │
│  │ 'Cybercrime Division'  │        │    use Haversine formula   │ │
│  │                        │        │    with coordinates        │ │
│  │ Result:                │        │                            │ │
│  │ stationId = 21         │        │ Result:                    │ │
│  │ (GLOBAL - No location) │        │ stationId = 1-20+          │ │
│  │                        │        │ (Location-based)           │ │
│  └────────┬───────────────┘        └────────┬───────────────────┘ │
│           │                                  │                     │
│           └──────────────────┬───────────────┘                     │
│                              ↓                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Insert Report into Database                                    │ │
│  │ ├─ report_id (auto-increment)                                  │ │
│  │ ├─ station_id = 21 (Cybercrime) OR 1-20+ (Location)           │ │
│  │ ├─ encrypted description, barangay, address                    │ │
│  │ ├─ status = 'pending'                                          │ │
│  │ ├─ created_at = NOW()                                          │ │
│  │ └─ handle media upload (if any)                                │ │
│  └────────┬─────────────────────────────────────────────────────┘ │
│           ↓                                                         │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Return Success Response                                        │ │
│  │ {                                                              │ │
│  │   success: true,                                               │ │
│  │   data: {                                                      │ │
│  │     report_id: [NUMBER],                                       │ │
│  │     title: "[TITLE]",                                          │ │
│  │     station_id: 21 or 1-20+                                    │ │
│  │   }                                                            │ │
│  │ }                                                              │ │
│  └─────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────┘
                      ↓
┌──────────────────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL)                                  │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ reports table                                                  │ │
│  │  report_id | station_id | report_type | description | ...     │ │
│  │  42        | 21         | Cybercrime  | [encrypted] | ...     │ │
│  │  43        | 1          | Theft       | [encrypted] | ...     │ │
│  │  44        | 21         | Phishing    | [encrypted] | ...     │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ police_stations table (NEW ENTRY)                              │ │
│  │  station_id | station_name        | address     | lat | lng   │ │
│  │  1          | PS1 Sta. Ana        | ...         | ... | ...   │ │
│  │  21         | Cybercrime Division | [Address]   | 0   | 0     │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ users table                                                    │ │
│  │  id | station_id | role   | ...                                │ │
│  │  1  | 21         | police | ... (Cybercrime Officer)          │ │
│  │  2  | 1          | police | ... (PS1 Officer)                 │ │
│  │  3  | NULL       | user   | ... (Regular User)                │ │
│  └────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────┘
                      ↓
┌──────────────────────────────────────────────────────────────────────┐
│                   POLICE DASHBOARD (Web/Mobile)                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Officer 1 (Cybercrime Division - station_id=21)               │ │
│  │                                                                │ │
│  │ Dashboard Query:                                              │ │
│  │ SELECT * FROM reports WHERE station_id = 21                  │ │
│  │                                                                │ │
│  │ Visible Reports:                                              │ │
│  │ ├─ Report #42: Phishing Attack                                │ │
│  │ ├─ Report #44: Identity Theft                                 │ │
│  │ └─ (More cybercrime reports...)                               │ │
│  │                                                                │ │
│  │ NOT Visible:                                                  │ │
│  │ ├─ Report #43: Motorcycle Theft (station_id=1)               │ │
│  │ └─ (All location-based reports)                               │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Officer 2 (PS1 Sta. Ana - station_id=1)                       │ │
│  │                                                                │ │
│  │ Dashboard Query:                                              │ │
│  │ SELECT * FROM reports WHERE station_id = 1                   │ │
│  │                                                                │ │
│  │ Visible Reports:                                              │ │
│  │ ├─ Report #43: Motorcycle Theft                               │ │
│  │ └─ (More PS1 location-based reports...)                       │ │
│  │                                                                │ │
│  │ NOT Visible:                                                  │ │
│  │ ├─ Report #42: Phishing (station_id=21)                       │ │
│  │ ├─ Report #44: Identity Theft (station_id=21)                 │ │
│  │ └─ (All cybercrime reports)                                   │ │
│  └────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Report Routing Decision Tree

```
                         ┌─────────────────┐
                         │  Report Submitted│
                         └────────┬─────────┘
                                  │
                    ┌─────────────┴──────────────┐
                    │                            │
              Parse crime_types array
              (e.g., ["Cybercrime"] or ["Theft"])
                    │
                    ↓
         ┌──────────────────────────┐
         │  Check for Cybercrime    │
         │  Keywords:               │
         │  - cybercrime            │
         │  - cyber crime           │
         │  - online fraud          │
         │  - hacking               │
         │  - phishing              │
         │  - identity theft        │
         │  - ransomware            │
         └──────────────────────────┘
                    │
        ┌───────────┴────────────┐
        │                        │
       YES                      NO
        │                        │
        ↓                        ↓
    ┌─────────────┐         ┌──────────────────┐
    │  CYBERCRIME │         │  LOCATION-BASED  │
    │  DIVISION   │         │  ROUTING         │
    │  ROUTING    │         │  (Existing)      │
    └─────┬───────┘         └────────┬─────────┘
          │                          │
          ↓                          ↓
   Query for             ┌───────────────────────────────┐
   'Cybercrime Division' │ Do we have barangay_id?       │
   station_id            └─────┬───────────────┬─────────┘
          │                   YES             NO
          │                    │                │
          ↓                    ↓                ↓
    station_id = 21   Query barangays   Use Haversine
    (GLOBAL)          table for         formula with
                      station_id        lat/lng to find
                                        nearest barangay
          │                    │                │
          │                    ↓                │
          │            Get station_id      Get station_id
          │            from barangay        from nearest
          │                    │            barangay
          │                    │                │
          └────────┬───────────┴────────────────┘
                   │
                   ↓
            ┌─────────────────────┐
            │  Insert Report      │
            │  station_id = [X]   │
            │  status = 'pending' │
            │  created_at = NOW() │
            └────────┬────────────┘
                     │
                     ↓
          ┌────────────────────────┐
          │ Return Success Response │
          │ (report_id, station_id)│
          └────────────────────────┘
```

---

## Data Model Diagram

```
┌──────────────────────────────────────────────┐
│           POLICE STATIONS                    │
├──────────────────────────────────────────────┤
│ station_id (PK)                              │
│ station_name                                 │
│ address                                      │
│ latitude                                     │
│ longitude                                    │
│ contact_number                               │
└──────┬───────────────────────────────────────┘
       │
       │ 1:N
       │ (assigned_to)
       │
       └─────────────────────────────┐
                                     │
       ┌────────────────────────────────────────────────────┐
       │              USERS                                 │
       ├────────────────────────────────────────────────────┤
       │ id (PK)                                            │
       │ firstname                                          │
       │ lastname                                           │
       │ email                                              │
       │ role ('user', 'police', 'admin')                   │
       │ station_id (FK) → police_stations                  │
       │   ├─ police users: assigned to station (1-21)     │
       │   └─ regular users: NULL                          │
       ├────────────────────────────────────────────────────┤
       │ Examples:                                          │
       │ id=1, station_id=21 (Cybercrime Officer)          │
       │ id=2, station_id=1  (PS1 Officer)                 │
       │ id=3, station_id=NULL (Regular User)              │
       └────────────────────────────────────────────────────┘
       │
       │ 1:N
       │ (submits)
       │
       └─────────────────────────────┐
                                     │
       ┌────────────────────────────────────────────────────┐
       │              REPORTS                               │
       ├────────────────────────────────────────────────────┤
       │ report_id (PK)                                     │
       │ user_id (FK) → users                               │
       │ location_id (FK) → locations                       │
       │ station_id (FK) → police_stations                  │
       │   ├─ Cybercrime: station_id = 21                  │
       │   └─ Location-based: station_id = 1-20            │
       │ title                                              │
       │ report_type                                        │
       │ description (encrypted)                            │
       │ status ('pending', 'investigating', 'resolved')    │
       │ is_anonymous                                       │
       │ date_reported                                      │
       │ created_at                                         │
       │ updated_at                                         │
       ├────────────────────────────────────────────────────┤
       │ Examples:                                          │
       │ report_id=42, station_id=21, report_type='Phishing'     │
       │ report_id=43, station_id=1,  report_type='Theft'       │
       │ report_id=44, station_id=21, report_type='Identity...' │
       └────────────────────────────────────────────────────┘
       │
       │ 1:N
       │ (located_at)
       │
       └─────────────────────────────┐
                                     │
       ┌────────────────────────────────────────────────────┐
       │              LOCATIONS                             │
       ├────────────────────────────────────────────────────┤
       │ location_id (PK)                                   │
       │ barangay (encrypted)                               │
       │ reporters_address (encrypted)                      │
       │ latitude                                           │
       │ longitude                                          │
       │ created_at                                         │
       │ updated_at                                         │
       ├────────────────────────────────────────────────────┤
       │ Note: For cybercrime reports, lat/lng unused       │
       └────────────────────────────────────────────────────┘
```

---

## Query Flow for Officer Access

### Cybercrime Officer Query
```sql
-- Officer_ID = 1, assigned to Cybercrime Division (station_id = 21)
SELECT r.report_id, r.title, r.report_type, r.status, l.latitude, l.longitude
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
WHERE r.station_id = 21  ← Automatically filters to Cybercrime Division
ORDER BY r.created_at DESC;

-- Results:
-- report_id=42, title='Phishing Attack', report_type='Cybercrime'
-- report_id=44, title='Online Fraud', report_type='Online Fraud'
-- (All cybercrime reports only)
```

### Location Officer Query
```sql
-- Officer_ID = 2, assigned to PS1 Sta. Ana (station_id = 1)
SELECT r.report_id, r.title, r.report_type, r.status, l.latitude, l.longitude
FROM reports r
LEFT JOIN locations l ON r.location_id = l.location_id
WHERE r.station_id = 1  ← Automatically filters to PS1
ORDER BY r.created_at DESC;

-- Results:
-- report_id=43, title='Motorcycle Theft', report_type='Theft'
-- report_id=45, title='House Burglary', report_type='Burglary'
-- (All PS1 location-based reports only)
```

---

## Station Assignment Reference

### Complete Station List After Implementation

```
LOCATION-BASED STATIONS (Existing):
┌─────────────┬──────────────────────────────────────────┐
│ station_id  │ station_name                             │
├─────────────┼──────────────────────────────────────────┤
│ 1           │ PS1 Sta. Ana                             │
│ 2           │ PS2 San Pedro                            │
│ 3           │ PS3 Talomo                               │
│ 4           │ PS4 Sasa                                 │
│ 5           │ PS5 Buhangin                             │
│ 6           │ PS6 Bunawan                              │
│ 7           │ PS7 Paquibato                            │
│ 8           │ PS8 Toril                                │
│ 9           │ PS9 Tugbok                               │
│ 10          │ PS10 Calinan                             │
│ 11          │ PS11 Baguio                              │
│ 12          │ PS12 Marilog                             │
│ 13          │ PS13 Mandug                              │
│ 15          │ PS15 Ecoland                             │
│ 16          │ PS16 Maa                                 │
│ 17          │ PS17 Baliok                              │
│ 18          │ PS18 Bajada                              │
│ 20          │ PS20 Los Amigos                          │
└─────────────┴──────────────────────────────────────────┘

SPECIALIZED DIVISION (NEW):
┌─────────────┬──────────────────────────────────────────┐
│ station_id  │ station_name                             │
├─────────────┼──────────────────────────────────────────┤
│ 21          │ Cybercrime Division                      │
│             │ (Global - latitude=0, longitude=0)       │
└─────────────┴──────────────────────────────────────────┘
```

---

## Report Type Distribution Example

### After 100 Reports Submitted

```
Distribution by Report Type:
┌────────────────────┬─────────┬────────────────────────┐
│ Report Type        │ Count   │ Station                │
├────────────────────┼─────────┼────────────────────────┤
│ Theft              │ 25      │ PS1-PS20 (location)    │
│ Burglary           │ 15      │ PS1-PS20 (location)    │
│ Assault            │ 20      │ PS1-PS20 (location)    │
│ Drug Possession    │ 10      │ PS1-PS20 (location)    │
│ Cybercrime         │ 15      │ Cybercrime Division 21 │
│ Online Fraud       │ 10      │ Cybercrime Division 21 │
│ Phishing           │ 5       │ Cybercrime Division 21 │
└────────────────────┴─────────┴────────────────────────┘

Visualization:
Location-Based (70):  [████████████████████████████████████]
Cybercrime (30):      [███████████████]

By Station:
PS1:          [▓▓▓▓▓▓] 12
PS2:          [▓▓▓▓] 8
PS3:          [▓▓▓] 5
...
Cybercrime:   [███████████████] 30  ← All cybercrime reports
```

---

## Deployment Timeline

```
Week 1: Setup & Preparation
├─ Monday: Review documentation (2 hours)
├─ Tuesday: Setup test database (1 hour)
├─ Wednesday: Test report submission (2 hours)
├─ Thursday: Test officer access control (2 hours)
└─ Friday: Stress testing & verification (3 hours)

Week 2: Production Deployment
├─ Monday: Backup production database (1 hour)
├─ Tuesday: Run migration script (30 min)
├─ Wednesday: Deploy code changes (30 min)
├─ Thursday: Verify & monitor logs (2 hours)
└─ Friday: Create test accounts, user training (3 hours)

Week 3: Monitoring & Support
├─ Daily: Monitor logs, check for errors
├─ Track cybercrime report submissions
├─ Verify officer access control
└─ Gather feedback for enhancements

Total Implementation Time: ~20-25 hours
```

---

## Success Metrics

After deployment, verify:

```
✅ Implementation Metrics:
├─ Cybercrime Division station created: 1 station
├─ Database migration successful: 0 errors
├─ Code changes deployed: handleReport.js updated
└─ System online: Backend restarted

✅ Functional Metrics:
├─ Cybercrime reports routed correctly: 100%
├─ Location-based reports unaffected: 100%
├─ Officer access control working: 100%
├─ Database queries performant: <100ms

✅ User Metrics:
├─ Officers assigned to Cybercrime Division: N
├─ First cybercrime reports submitted: Y
├─ Reports visible to assigned officers: 100%
├─ False positives/negatives: 0
└─ User feedback positive: Yes

✅ System Metrics:
├─ Uptime: >99.9%
├─ Average report submission: <200ms
├─ Database query time: <100ms
├─ Memory usage: Normal
└─ Error logs: None related to routing
```

---

This completes the cybercrime routing system implementation.
All documentation, code, and deployment materials are ready.
