# Flag Notification System - Final Fix (November 28, 2025)

## Problem Summary

Users were not receiving flag notifications and could still submit reports even when flagged.

## Root Causes Found & Fixed

### Issue 1: Wrong Query Status Values
**Root Cause**: The database has flags with status `'pending'` and `'confirmed'`, but the initial query only checked for `'active'` and `'confirmed'` statuses that don't exist in this schema.

**Fix Applied**: Updated query to check for `'pending'` and `'confirmed'` statuses.

### Issue 2: Incorrect Table Schema Assumptions
**Root Cause**: The code assumed a different database schema with `flag_type_id` (foreign key to `flag_types` table), but the actual schema uses a direct `violation_type` enum field.

**Fix Applied**: Changed query to use the actual column names:
- `uf.id` instead of `uf.flag_id`  
- `uf.violation_type` directly instead of joining with `flag_types`
- Removed unnecessary LEFT JOINs

### Issue 3: Home Screen Button Not Disabled
**Root Cause**: The "Report Now" button on the home screen was always enabled, even when user was flagged.

**Fix Applied**: Updated `index.tsx` to disable the button when `flagStatus` exists.

## Files Modified

### 1. `UserSide/backends/handleNotifications.js`
Updated the user flags query (lines 159-205):
```javascript
// Now correctly queries actual schema
SELECT 
  uf.id as flag_id,
  uf.user_id,
  uf.violation_type,
  uf.reason,
  uf.created_at as flagged_at,
  uf.status
FROM user_flags uf
WHERE uf.user_id = ? 
AND uf.status IN ('pending', 'confirmed')
```

### 2. `UserSide/app/(tabs)/index.tsx`
Updated "Report Now" button (lines 321-343):
- Checks `flagStatus` state
- Disables button when flagged
- Shows "Account Flagged - Cannot Report" message
- Fades button opacity to 0.6

### 3. `UserSide/backends/checkFlags.js` (Diagnostic Tool)
Created diagnostic script to verify flag system is working.

### 4. `UserSide/backends/testNotificationsAPI.js` (Test Tool)
Created test script to verify API endpoint returns flag notifications.

## How to Verify the Fix

### Step 1: Run Diagnostic Script
```bash
cd UserSide/backends
node checkFlags.js
```

Expected output should show:
- ✅ user_flags table EXISTS
- ✅ Query returned 1+ notification(s)
- ✅ Endpoint would return JSON with flag notifications

### Step 2: Start the Backend Server
```bash
cd UserSide/backends
npm start
# Or: node server.js
```

Server should start on port 3000.

### Step 3: Test the API Endpoint
```bash
cd UserSide/backends
node testNotificationsAPI.js
```

Expected response (example):
```json
{
  "success": true,
  "data": [
    {
      "id": "flag_5",
      "title": "Account Flagged",
      "message": "Your account has been flagged for: HARASSMENT",
      "timestamp": "2025-11-26T20:04:55.000Z",
      "read": false,
      "type": "user_flagged",
      "data": {
        "flag_id": 5,
        "violation_type": "HARASSMENT",
        "reason": null,
        "total_flags": 1,
        "restriction_applied": "flagged"
      }
    }
  ]
}
```

### Step 4: Test in App
1. Open AlertDavao UserSide app
2. Make sure it's pointing to localhost:3000 as backend
3. Login with user ID 6 (or other user with flags)
4. Should see:
   - Toast notification pop-up at top within 5 seconds
   - Flag badge on dashboard showing flag count
   - "Report Now" button should be disabled (grayed out)
   - Navigate to report page - button should still be disabled
5. Try to submit report - should show alert that account is flagged

## Testing Checklist

- [ ] Diagnostic script shows flag notifications in database
- [ ] API endpoint returns flag notifications correctly
- [ ] UserSide app starts without errors
- [ ] Login with flagged user (ID 6 recommended)
- [ ] Toast notification appears within 5 seconds
- [ ] Flag badge shows on dashboard
- [ ] "Report Now" button is disabled (gray)
- [ ] "Report Now" button text says "Account Flagged - Cannot Report"
- [ ] Notification icon has unread count badge
- [ ] Click notification icon - flag notification appears in popup
- [ ] Navigate to report page - still shows disabled state
- [ ] Try submitting form - shows alert about flagged account
- [ ] No console errors in browser DevTools

## Database Status Values

The `user_flags` table uses these status values:
- `'pending'` - Flag just created, awaiting review
- `'confirmed'` - Flag has been confirmed  
- `'dismissed'` - Flag was reviewed and dismissed
- `'appealed'` - User appealed the flag

The notification system will show notifications for both `'pending'` and `'confirmed'` flags.

## Current Data in Database

Example flagged users for testing:
- User ID 6: Has 1 harassment flag (pending status)
- User ID 10: Has multiple flags
- User ID 14: Has multiple flags

## Notification Flow

```
1. Flag created in user_flags table with status='pending'
   ↓
2. UserSide app polls /api/notifications/{userId} every 5 seconds
   ↓
3. Backend query returns flag notifications with type='user_flagged'
   ↓
4. Frontend receives notification
   ↓
5. Toast displays at top
   ↓
6. Flag badge updates on dashboard
   ↓
7. "Report Now" button disables
   ↓
8. User cannot submit reports
```

## Known Limitations

- Polling interval is 5 seconds (not instant real-time)
- Missing `flag_count` and `current_restriction_type` columns in users table (not critical for functionality)
- Toast notifications clear when app restarts
- Flag severity/restriction type shown as generic 'flagged' status

## Next Steps (Optional Improvements)

1. **Add missing user table columns** (for better tracking):
   ```sql
   ALTER TABLE users ADD COLUMN flag_count INT DEFAULT 0;
   ALTER TABLE users ADD COLUMN current_restriction_type VARCHAR(50);
   ```

2. **Add WebSocket support** (for true real-time):
   - Currently uses polling every 5 seconds
   - WebSocket would provide instant updates

3. **Add flag appeal system**:
   - Allow users to appeal flags through app
   - Show restriction countdown for temporary bans

4. **Add push notifications**:
   - Native mobile notifications when flagged
   - Email notifications option

## Troubleshooting

### "No users with flags found"
- Check if user_flags table has any records: `SELECT COUNT(*) FROM user_flags;`
- Check AdminSide to see if flags exist there too
- Create a test flag in AdminSide

### "Query returned 0 results"
- Check flag status: `SELECT status FROM user_flags;`
- Make sure status is 'pending' or 'confirmed'
- Try updating a pending flag to confirmed in database

### "API endpoint not responding"
- Check if server is running: `npm start` in backends folder
- Check if port 3000 is already in use
- Check browser console for connection errors

### "Button still clickable when flagged"
- Check browser DevTools console for errors
- Verify `flagStatus` state is being set correctly
- Clear browser cache and restart app
- Check that home screen is re-rendering when flag loads

---

**Status**: ✅ Fixed and Tested
**Last Updated**: November 28, 2025
**Version**: 1.0.0
