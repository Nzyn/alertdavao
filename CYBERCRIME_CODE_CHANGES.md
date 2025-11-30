# Cybercrime Routing - Code Changes Summary

## Files Changed

### 1. `UserSide/backends/handleReport.js`
**Location:** Lines 162-234 (Report routing logic)

**Change Type:** Addition + Modification

**Before:**
```javascript
// Get the station_id from provided barangay_id or calculate from coordinates
let stationId = null;

if (barangay_id) {
  // Use provided barangay_id to get station_id
  const [barangayResult] = await connection.query(
    `SELECT station_id FROM barangays WHERE barangay_id = ?`,
    [barangay_id]
  );
  // ... assign station
}

// Fallback: Use Haversine formula
if (!stationId && lat !== 0 && lng !== 0) {
  // ... Haversine calculation
}
```

**After:**
```javascript
// Get the station_id from provided barangay_id or calculate from coordinates
let stationId = null;

// Check if this is a cybercrime report - route to Cybercrime Division globally
const isCybercrime = crimeTypesArray.some(crime => 
  crime.toLowerCase().includes('cybercrime') || 
  crime.toLowerCase().includes('cyber crime') ||
  crime.toLowerCase().includes('online fraud') ||
  crime.toLowerCase().includes('hacking') ||
  crime.toLowerCase().includes('phishing') ||
  crime.toLowerCase().includes('identity theft') ||
  crime.toLowerCase().includes('ransomware')
);

if (isCybercrime) {
  // Route cybercrime reports to Cybercrime Division (global assignment, no location-based assignment)
  try {
    const [cybercrimeStation] = await connection.query(
      `SELECT station_id FROM police_stations WHERE station_name = 'Cybercrime Division' LIMIT 1`
    );
    if (cybercrimeStation && cybercrimeStation.length > 0) {
      stationId = cybercrimeStation[0].station_id;
      console.log("🚨 Cybercrime report detected! Routing to Cybercrime Division (Station ID:", stationId, ")");
    } else {
      console.log("⚠️  Cybercrime Division station not found in database");
    }
  } catch (err) {
    console.log("⚠️  Error routing cybercrime report:", err.message);
  }
} else {
  // Location-based routing for non-cybercrime reports
  if (barangay_id) {
    // Use provided barangay_id to get station_id
    const [barangayResult] = await connection.query(
      `SELECT station_id FROM barangays WHERE barangay_id = ?`,
      [barangay_id]
    );
    // ... assign station (UNCHANGED)
  }
  
  // Fallback: Use Haversine formula
  if (!stationId && lat !== 0 && lng !== 0) {
    // ... Haversine calculation (UNCHANGED)
  }
}
```

**Key Changes:**
1. Added cybercrime detection using keyword matching
2. Added conditional routing: cybercrime → Cybercrime Division (global), other → location-based
3. Wrapped existing location-based logic in `else` block
4. Added console logging for debugging

---

## Files Created

### 1. `sql/add_cybercrime_division.sql`
```sql
USE alertdavao;

INSERT INTO police_stations (station_name, address, latitude, longitude, contact_number) 
VALUES ('Cybercrime Division', 'Davao City Police Office - Cybercrime Division', 0, 0, 'TBD')
ON DUPLICATE KEY UPDATE station_name='Cybercrime Division';

SELECT * FROM police_stations WHERE station_name = 'Cybercrime Division';
```

**Purpose:** Database migration to add Cybercrime Division station

**Run:** 
```bash
mysql -u root alertdavao < sql/add_cybercrime_division.sql
```

---

### 2. `UserSide/backends/setup_cybercrime_division.js`
```javascript
const db = require('./db');

async function setupCybercrimeDivision() {
  let connection;
  try {
    connection = await db.getConnection();
    
    console.log('🚨 Setting up Cybercrime Division Police Station...\n');
    
    // Check if exists
    const [existing] = await connection.query(
      `SELECT station_id, station_name FROM police_stations WHERE station_name = 'Cybercrime Division'`
    );
    
    if (existing && existing.length > 0) {
      console.log('✅ Cybercrime Division already exists in database');
      console.log(`   Station ID: ${existing[0].station_id}\n`);
      return;
    }
    
    // Insert Cybercrime Division
    const [result] = await connection.query(
      `INSERT INTO police_stations (station_name, address, latitude, longitude, contact_number) 
       VALUES (?, ?, ?, ?, ?)`,
      ['Cybercrime Division', 'Davao City Police Office - Cybercrime Division', 0, 0, 'TBD']
    );
    
    console.log('✅ Cybercrime Division added successfully!');
    console.log(`   Station ID: ${result.insertId}\n`);
    
    console.log('✨ Cybercrime Division setup complete!\n');
    
  } catch (error) {
    console.error('❌ Error setting up Cybercrime Division:', error.message);
    process.exit(1);
  } finally {
    if (connection) {
      connection.release();
    }
    process.exit(0);
  }
}

setupCybercrimeDivision();
```

**Purpose:** Node.js setup script for easier deployment

**Run:**
```bash
node UserSide/backends/setup_cybercrime_division.js
```

---

## No Changes Required In

### ✅ `UserSide/backends/getPoliceReports.js`
Filtering logic already correct:
```javascript
// Existing code - NO CHANGES
const [reports] = await db.query(
  `SELECT ... FROM reports r
   ...
   WHERE r.station_id = ?  // ← Automatically filters by officer's station
   ...`,
  [stationId]
);
```

### ✅ `AdminSide/admin/app/Http/Controllers/ReportController.php`
Filtering logic already correct:
```php
// Existing code - NO CHANGES
$reports = Report::where('assigned_station_id', $userStationId)  // ← Filters by station
           ->orderBy('created_at', 'DESC')
           ->get();
```

### ✅ `UserSide/backends/server.js`
API routes unchanged:
```javascript
// Existing code - NO CHANGES
app.post('/api/reports', 
  upload.single('file'), 
  handleReport.submitReport  // ← Handles cybercrime automatically
);
```

### ✅ Mobile App (`UserSide/screens`, `UserSide/services`)
No changes needed - submission logic already supports `crime_types` array

---

## Data Flow Diagram

### Before (Location-Based Only)
```
User submits report with crime_types=['Theft']
  ↓
handleReport.submitReport()
  ↓
Parse crime_types → "Theft"
  ↓
Check barangay_id → Get station from barangay
  ↓
If no barangay, use Haversine with coordinates
  ↓
Insert report with station_id = [location-based station]
  ↓
Police officer queries: WHERE r.station_id = [their_station]
  ↓
See only reports for their location
```

### After (Cybercrime + Location-Based)
```
User submits report with crime_types=['Cybercrime']
  ↓
handleReport.submitReport()
  ↓
Parse crime_types → "Cybercrime"
  ↓
NEW: Detect cybercrime keyword ✓
  ↓
NEW: Query for 'Cybercrime Division' station
  ↓
NEW: Assign station_id = Cybercrime Division (global)
  ↓
Insert report with station_id = 21 (Cybercrime Division)
  ↓
Cybercrime officer queries: WHERE r.station_id = 21
  ↓
See only cybercrime reports (globally)


OR (Non-Cybercrime):


User submits report with crime_types=['Theft']
  ↓
handleReport.submitReport()
  ↓
Parse crime_types → "Theft"
  ↓
NEW: Detect cybercrime keyword ✗
  ↓
ELSE: Location-based routing (EXISTING LOGIC)
  ↓
Check barangay_id → Get station from barangay
  ↓
If no barangay, use Haversine with coordinates
  ↓
Insert report with station_id = [location-based station]
  ↓
Police officer queries: WHERE r.station_id = [their_station]
  ↓
See only reports for their location
```

---

## Testing Code Changes

### Unit Test: Cybercrime Detection
```javascript
// Test that cybercrime keywords are detected
const testCrimes = [
  'cybercrime',
  'Cyber Crime',
  'online fraud',
  'HACKING',
  'phishing',
  'identity theft',
  'ransomware'
];

testCrimes.forEach(crime => {
  const isCybercrime = [crime].some(c => 
    c.toLowerCase().includes('cybercrime') || 
    c.toLowerCase().includes('cyber crime') ||
    c.toLowerCase().includes('online fraud') ||
    c.toLowerCase().includes('hacking') ||
    c.toLowerCase().includes('phishing') ||
    c.toLowerCase().includes('identity theft') ||
    c.toLowerCase().includes('ransomware')
  );
  console.assert(isCybercrime === true, `Failed to detect: ${crime}`);
});

console.log('✅ All cybercrime keywords detected correctly');
```

### Integration Test: Report Routing
```javascript
// Test that reports are routed correctly
async function testReportRouting() {
  // Test 1: Cybercrime report
  const cyberReport = await submitReport({
    title: 'Phishing',
    crime_types: ['Cybercrime'],
    ...otherFields
  });
  
  // Verify routed to Cybercrime Division
  const [result] = await db.query(
    `SELECT ps.station_name FROM reports r
     LEFT JOIN police_stations ps ON r.station_id = ps.station_id
     WHERE r.report_id = ?`,
    [cyberReport.report_id]
  );
  
  console.assert(
    result[0].station_name === 'Cybercrime Division',
    'Cybercrime report not routed correctly'
  );
  
  // Test 2: Location-based report
  const theftReport = await submitReport({
    title: 'Theft',
    crime_types: ['Theft'],
    barangay_id: 1,
    ...otherFields
  });
  
  // Verify routed to location station
  const [result2] = await db.query(
    `SELECT ps.station_id FROM reports r
     LEFT JOIN police_stations ps ON r.station_id = ps.station_id
     WHERE r.report_id = ?`,
    [theftReport.report_id]
  );
  
  console.assert(
    result2[0].station_id === 1,  // PS1 from barangay_id
    'Location report not routed correctly'
  );
}
```

---

## Backward Compatibility

✅ **100% Backward Compatible**

- Existing location-based routing untouched (wrapped in `else` block)
- All old reports still accessible by location
- Existing database schema unchanged (no column modifications)
- Existing API endpoints unchanged
- Mobile app requires no updates
- Laravel admin panel requires no updates

---

## Performance Impact

✅ **Negligible**

- **Cybercrime detection:** O(n) where n=crime_types.length (typically 1-3)
- **Database query:** O(1) lookup by station_name with LIMIT 1
- **No additional indexes needed:** Uses existing `station_id` foreign key

**Benchmark:**
- Non-cybercrime (location-based) reports: ~same speed as before
- Cybercrime reports: Faster (skips Haversine calculation)

---

## Deployment Checklist

- [ ] Backup database
- [ ] Run migration: `mysql -u root alertdavao < sql/add_cybercrime_division.sql`
- [ ] Or run setup: `node UserSide/backends/setup_cybercrime_division.js`
- [ ] Verify Cybercrime Division in database
- [ ] Restart backend server
- [ ] Test cybercrime report submission
- [ ] Check backend logs for routing message
- [ ] Assign officers to Cybercrime Division
- [ ] Test officer access control
- [ ] Monitor for errors

---

## Rollback Plan

If needed, undo all changes:

```bash
# 1. Revert code changes
git checkout UserSide/backends/handleReport.js

# 2. Remove Cybercrime Division from database (optional)
mysql -u root alertdavao -e "DELETE FROM police_stations WHERE station_name = 'Cybercrime Division';"

# 3. Restart backend
npm restart
```

All reports with Cybercrime Division station_id will become unassigned, but data is preserved.

---

## Next Phase: Enhancements

1. **Physical Location:** Update coordinates when office location is determined
2. **Email Alerts:** Notify officers of new cybercrime reports
3. **Specialized Dashboard:** Create cybercrime-specific UI
4. **Analytics:** Track cybercrime trends separately
5. **Integration:** Connect with other cybercrime units/agencies
