# User Flagging & Restriction System

## Overview
This system allows administrators to flag users for violations and automatically applies restrictions based on violation count thresholds.

## Violation Types (10 Types)

| Type | Description | Severity |
|------|-------------|----------|
| `false_report` | False or misleading reports | Minor to Major |
| `prank_spam` | Prank or spam submissions | Minor |
| `inappropriate_content` | Inappropriate or abusive behavior | Minor to Major |
| `harassment` | Harassment of other users | Major |
| `impersonation` | Impersonating other users/officials | Major |
| `inappropriate_upload` | Uploading inappropriate content | Minor to Major |
| `suspicious_activity` | Suspicious or unusual activity | Minor |
| `sensitive_info_sharing` | Sharing others' sensitive information | Major |
| `anonymous_misuse` | Misusing anonymous reporting feature | Minor |
| `system_abuse` | Attempted system abuse or unauthorized access | Critical |

## Restriction Levels

### 1. Warning (3 flags)
- **Duration:** 24 hours
- **Restrictions:**
  - ✅ Can report
  - ✅ Can comment
  - ❌ Cannot upload media
  - ✅ Can message

### 2. Suspended (7 flags)
- **Duration:** 7 days
- **Restrictions:**
  - ❌ Cannot report
  - ❌ Cannot comment
  - ❌ Cannot upload media
  - ✅ Can message

### 3. Banned (15 flags)
- **Duration:** Permanent
- **Restrictions:**
  - ❌ Cannot report
  - ❌ Cannot comment
  - ❌ Cannot upload media
  - ❌ Cannot message
  - ❌ Cannot login

## API Endpoints

### Check User Restrictions
```
GET /api/users/:userId/restrictions
```

**Response:**
```json
{
  "isRestricted": true,
  "restrictionType": "warning",
  "reason": "Auto-restriction: 3 violations accumulated",
  "expiresAt": "2024-01-15T12:00:00Z",
  "canReport": true,
  "canComment": true,
  "canUpload": false,
  "canMessage": true
}
```

### Flag a User
```
POST /api/users/flag
```

**Request Body:**
```json
{
  "userId": 123,
  "violationType": "false_report",
  "severity": "minor",
  "description": "User submitted a false crime report",
  "reportedBy": 456,
  "relatedReportId": 789,
  "evidence": { "screenshots": ["url1", "url2"] }
}
```

**Response:**
```json
{
  "success": true,
  "flagId": 1,
  "message": "Flag created successfully"
}
```

### Get User Flag History
```
GET /api/users/:userId/flags
```

**Response:**
```json
{
  "totalFlags": 5,
  "restrictionLevel": "warning",
  "recentFlags": [
    {
      "violation_type": "false_report",
      "severity": "minor",
      "description": "...",
      "created_at": "2024-01-10T10:00:00Z",
      "status": "confirmed"
    }
  ],
  "flagBreakdown": {
    "falseReport": 2,
    "spam": 1,
    "harassment": 0,
    "inappropriateContent": 2
  }
}
```

## Database Setup

Run the SQL script to create necessary tables:

```bash
# Using MySQL command line
mysql -u root -p alertdavao < sql/user_flags_system.sql
```

Or import via phpMyAdmin:
1. Open phpMyAdmin
2. Select `alertdavao` database
3. Go to "Import" tab
4. Select `sql/user_flags_system.sql`
5. Click "Go"

## Implementation Files

### Backend Files
- `UserSide/backends/handleUserRestrictions.js` - Main handler for restrictions
- `UserSide/backends/handleLogin.js` - Updated to check restrictions on login
- `UserSide/backends/server.js` - Added restriction routes

### SQL Files
- `sql/user_flags_system.sql` - Database schema for flagging system

## Frontend Integration

### Check Restrictions on Login (UserSide)
```javascript
// After successful login, check restrictions
const response = await fetch(`${API_URL}/api/users/${userId}/restrictions`);
const restrictions = await response.json();

if (restrictions.isRestricted) {
  if (restrictions.restrictionType === 'banned') {
    Alert.alert('Account Suspended', 'Your account has been permanently suspended.');
    return;
  }
  
  // Show warning for other restrictions
  Alert.alert(
    'Account Restriction',
    `Your account has restrictions until ${new Date(restrictions.expiresAt).toLocaleDateString()}.
    
Reason: ${restrictions.reason}`,
    [{ text: 'OK' }]
  );
}
```

### Check Before Submitting Reports
```javascript
const checkCanReport = async (userId) => {
  const response = await fetch(`${API_URL}/api/users/${userId}/restrictions`);
  const restrictions = await response.json();
  
  if (restrictions.isRestricted && !restrictions.canReport) {
    Alert.alert(
      'Cannot Submit Report',
      'Your account is currently restricted from submitting reports.'
    );
    return false;
  }
  return true;
};
```

## Admin Dashboard Integration (Laravel)

### Flag User from Admin Panel
```php
// In Laravel controller
public function flagUser(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|integer',
        'violation_type' => 'required|string',
        'severity' => 'required|in:minor,moderate,major,critical',
        'description' => 'required|string'
    ]);
    
    // Insert flag
    DB::table('user_flags')->insert([
        'user_id' => $validated['user_id'],
        'violation_type' => $validated['violation_type'],
        'severity' => $validated['severity'],
        'description' => $validated['description'],
        'created_by' => auth()->id(),
        'created_at' => now()
    ]);
    
    // Update user flag count
    DB::table('users')->where('id', $validated['user_id'])
        ->increment('total_flags');
    
    return response()->json(['success' => true]);
}
```

## Notes

1. **Auto-restriction** triggers automatically when flag thresholds are reached
2. **Manual restrictions** can still be applied by admins regardless of flag count
3. **Appeals** can be handled through the `flag_history` table
4. **Expired restrictions** are automatically honored (checked against `expires_at`)
5. **Banned users** cannot login at all - they receive an error message immediately
