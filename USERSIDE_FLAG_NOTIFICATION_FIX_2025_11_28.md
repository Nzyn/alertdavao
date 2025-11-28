# User-Side Flag Notification System - Fix Implementation (November 28, 2025)

## Problem Identified

The UserSide app was not displaying flag notifications when users were flagged by admins, and users could still press "Report Now" even when flagged. This was due to two critical issues:

### Issue 1: Missing Database Query for Flag Notifications
The Node.js backend's `handleNotifications.js` was not querying the `user_flags` table. It only checked for:
- Unread messages
- New reports  
- Report status updates
- Verification status updates

**Missing**: User flag notifications

### Issue 2: "Report Now" Button Not Disabled on Home Screen
The home screen dashboard allowed users to click "Report Now" even when flagged. While the report page had guards, the home screen button should be disabled immediately upon detection of a flag.

## Solutions Applied

### 1. Added User Flag Query to handleNotifications.js
**File**: `UserSide/backends/handleNotifications.js`

Added a new section (lines 159-204) that:
- Queries the `user_flags` table for active flags
- Joins with `flag_types` to get the violation type name
- Joins with `users` to get the flag count and restriction type
- Returns notifications in the correct format with `type: 'user_flagged'`
- Includes flag data (flag_id, violation_type, reason, total_flags, restriction_applied)

**Query Details**:
```sql
SELECT 
  uf.flag_id,
  uf.user_id,
  COALESCE(ft.flag_name, 'Unknown Violation') as violation_type,
  uf.reason,
  uf.created_at as flagged_at,
  uf.status,
  COALESCE(u.flag_count, 1) as total_flags,
  COALESCE(u.current_restriction_type, 'warning') as restriction_applied
FROM user_flags uf
LEFT JOIN flag_types ft ON ft.flag_type_id = uf.flag_type_id
LEFT JOIN users u ON u.id = uf.user_id
WHERE uf.user_id = ? 
AND uf.status = 'active'
ORDER BY uf.created_at DESC 
LIMIT 10
```

### 2. Updated Home Screen to Disable "Report Now" When Flagged
**File**: `UserSide/app/(tabs)/index.tsx` (lines 321-343)

Changed from:
```typescript
// Always clickable
<Link href="/report" asChild>
  <Pressable>Report Now</Pressable>
</Link>
```

To:
```typescript
// Disabled when flagged
{flagStatus ? (
  <Pressable style={[styles.reportButton, { backgroundColor: '#ccc', opacity: 0.6 }]} disabled={true}>
    <Text style={styles.reportButtonText}>Account Flagged - Cannot Report</Text>
  </Pressable>
) : (
  <Link href="/report" asChild>
    <Pressable>Report Now</Pressable>
  </Link>
)}
```

## How It Works Now

### Flow 1: User Flagged While App is Open
1. Admin flags user in AdminSide
2. Flag is saved to `user_flags` table with `status = 'active'`
3. Notification polling (every 5 seconds) detects new flag
4. Backend returns flag notification with `type: 'user_flagged'`
5. Frontend displays:
   - **Toast notification** at top with warning icon
   - **Flag status badge** on dashboard
   - **Disabled "Report Now" button** (disabled state)
   - **Warning box** below button explaining the flag
6. User receives real-time visual feedback

### Flow 2: User Navigates to Report Page While Flagged
1. User taps "Report Now" (or navigates directly)
2. Report page loads and calls `useFocusEffect`
3. Loads flag notifications via `notificationService.getUserNotifications()`
4. Detects `user_flagged` notification
5. Sets `isFlagged = true`
6. Button is disabled with gray color
7. Warning box appears below button
8. If user somehow bypasses and submits, `handleSubmit()` checks flag status and shows alert

### Flow 3: User is Already Flagged When App Opens
1. App loads home screen
2. `useFocusEffect` loads notifications
3. Finds flag notification in the list
4. Sets `flagStatus` and displays badge + disabled button
5. User cannot report

## Testing Checklist

- [ ] Flag a user in AdminSide
- [ ] Check that UserSide app detects notification within 5 seconds
- [ ] Verify toast notification appears at top with flag details
- [ ] Check that "Report Now" button is disabled on home screen
- [ ] Verify button shows "Account Flagged - Cannot Report" text
- [ ] Verify button has gray appearance (opacity 0.6)
- [ ] Check that flag status badge appears on dashboard
- [ ] Try clicking disabled "Report Now" button (should not navigate)
- [ ] Navigate to report page directly (should show disabled state)
- [ ] Try form submission when flagged (should show alert)
- [ ] Unflag user in AdminSide
- [ ] Verify button becomes enabled again
- [ ] Verify badge disappears
- [ ] Test with multiple flags (should show count)

## Technical Details

### Notification Polling
- **Interval**: 5 seconds (configurable in `index.tsx` line 116)
- **Trigger**: Every time notification polling callback receives a `user_flagged` type
- **Auto-display**: Toast and notification popup shown automatically
- **Persistence**: Flag badge persists until flag is removed from database

### Database Status Check
- **Query Status**: Only returns flags with `status = 'active'`
- **Field Names**: Uses correct column names from schema:
  - `flag_count` (not `total_flags`)
  - `current_restriction_type` (not `restriction_level`)
  - `flag_type_id` (joins with `flag_types` for name)

### Frontend States
- **Home Screen**: `flagStatus` state controls button display
- **Report Page**: `isFlagged` state controls button and warning display
- **Toast**: Displays and auto-dismisses after 8 seconds
- **Badge**: Shows total flag count with color coding

## Files Modified

1. ✅ `UserSide/backends/handleNotifications.js` - Added user flag query
2. ✅ `UserSide/app/(tabs)/index.tsx` - Disabled report button when flagged

## Performance Impact

- **Query Cost**: Minimal (single user lookup with indexes)
- **Polling**: No additional load (same interval as before)
- **Frontend**: No additional rendering overhead
- **Backward Compatible**: Doesn't affect other notification types

## Future Improvements

- [ ] Add sound/haptic feedback when flagged
- [ ] Add flag appeal system through app
- [ ] Show countdown timer for temporary restrictions
- [ ] Add flag history timeline
- [ ] Support WebSocket for instant updates (instead of polling)
- [ ] Add notification settings to control flag alert preferences

## Known Limitations

- Toast notifications are session-only (don't persist after app close)
- Polling interval is fixed at 5 seconds (could be optimized)
- No offline queue for missed flags during offline periods

## Rollback Instructions

If needed to revert these changes:

1. Remove user flag query from `handleNotifications.js` (lines 159-204)
2. Revert "Report Now" button to always-enabled in `index.tsx`
3. Restart both UserSide backend and frontend

## Verification Commands

### Check if tables exist:
```sql
SELECT * FROM user_flags LIMIT 1;
SELECT * FROM flag_types LIMIT 1;
```

### Test notification endpoint:
```bash
curl http://localhost:3000/api/notifications/USER_ID
```

### Expected response:
```json
{
  "success": true,
  "data": [
    {
      "id": "flag_123",
      "title": "Account Flagged",
      "message": "Your account has been flagged for: False or Misleading Reports - Multiple false reports submitted",
      "timestamp": "2025-11-28T10:30:00Z",
      "read": false,
      "type": "user_flagged",
      "data": {
        "flag_id": 123,
        "violation_type": "False or Misleading Reports",
        "reason": "Multiple false reports submitted",
        "total_flags": 1,
        "restriction_applied": "temporary_ban"
      }
    }
  ]
}
```

---

**Version**: 1.0.0  
**Status**: Ready for Testing  
**Created**: 2025-11-28  
**Modified**: 2025-11-28
