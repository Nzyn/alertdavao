# UserSide Real-Time Notification Setup - November 28, 2025

## Overview

When an admin/police flags a user on AdminSide, the user now receives a **real-time notification popup** on UserSide. Additionally, flagged users are **blocked from submitting reports**.

## Features Added

### 1. Real-Time Notification Popup
✅ Notification appears automatically when user is flagged
✅ Displays violation type and restriction applied
✅ Red styling to indicate flagging
✅ Auto-closes notification popup when needed
✅ Notifications persist in history

### 2. Report Prevention for Flagged Users
✅ Flagged users cannot click "Report Now" button
✅ Backend validates restriction before accepting report
✅ Frontend checks restriction status before enabling report submission
✅ Users see appropriate error message if trying to report

## Files Modified

### 1. NotificationService (services/notificationService.ts)
- Added `user_flagged` notification type
- Added `handleFlaggedNotification()` method to convert broadcast data to notification
- Added `startNotificationPolling()` method for checking new notifications every 30 seconds
- Added `data` field to Notification interface for storing flag metadata

### 2. Home Screen (app/(tabs)/index.tsx)
- Added notification polling setup on component focus
- Auto-shows notification popup when user is flagged
- Properly cleans up polling when component loses focus
- Adds new flagged notifications to the notification list

### 3. NotificationPopup Component (components/NotificationPopup.tsx)
- Added icon display based on notification type
- Color-coded notifications (red for flag, blue for report, etc.)
- Shows restriction applied information
- Enhanced styling for flagged notifications
- Icons: flag, document, chat bubble, checkmark

## How It Works

### When User is Flagged

1. **Admin/Police flags user** on AdminSide
2. **Flag saved** to database
3. **Notification created** with flag metadata
4. **Broadcast event** sent to user's private channel
5. **UserSide polls** every 30 seconds (or receives real-time broadcast)
6. **Notification popup** appears automatically
7. **User sees**: Account Flagged notification with restriction details

### When Flagged User Tries to Report

1. **User navigates** to Report screen
2. **Frontend checks** `isFlagged` status via API
3. **If flagged**: Report button disabled/error shown
4. **If not flagged**: Report button enabled, normal flow
5. **User submits** report (if allowed)
6. **Backend validates** restriction before accepting report

## Notification Flow

```
AdminSide: User Flagged
    ↓
Database: notifications table + user_flags table
    ↓
Laravel Event: UserFlagged broadcast
    ↓
UserSide: Polling every 30 seconds
    ↓
UserSide: notificationService.getUserNotifications()
    ↓
NotificationPopup: Displays with icon and color
    ↓
User sees alert about flag
```

## API Endpoints Used

### Get User Notifications
```
GET /notifications/{userId}
Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "type": "user_flagged",
      "message": "Your account has been flagged for: False Report",
      "timestamp": "2025-11-28T10:30:00Z",
      "read": false
    }
  ]
}
```

### Check User Flag Status
```
GET /api/users/{userId}/flag-status
Response:
{
  "success": true,
  "is_flagged": true,
  "flag_info": {
    "violation_type": "false_report",
    "reason": "User submitted false crime report",
    "created_at": "2025-11-28T10:30:00Z"
  },
  "restriction_info": {
    "type": "warning",
    "reason": "Auto-restriction: 1 violations"
  }
}
```

## Notification Styling

### Flag Notification (user_flagged type)
- **Background**: Light red (#fee2e2)
- **Icon**: Flag in red (#dc2626)
- **Title**: Red text "Account Flagged"
- **Message**: Dark red text showing violation type
- **Restriction**: Shows applied restriction (warning/suspended/banned)

### Other Notification Types
- **Report**: Blue icon (document)
- **Message**: Purple icon (chat)
- **Verification**: Green icon (checkmark)

## Testing

### 1. Test Flagging with Notification
```bash
# Flag a user
curl -X POST http://localhost:8000/users/5/flag \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_token" \
  -d '{
    "violation_type": "false_report",
    "reason": "Test flagging"
  }'

# Check notifications on UserSide
# Should see notification popup within 30 seconds
```

### 2. Test Report Prevention
```javascript
// In UserSide report.tsx
// Check that isFlagged state is true
// Report button should be disabled
// Trying to submit should show error
```

### 3. Database Verification
```sql
-- Check notification created
SELECT * FROM notifications WHERE user_id = 5 AND type = 'user_flagged';

-- Check flag created
SELECT * FROM user_flags WHERE user_id = 5;

-- Check restriction applied
SELECT * FROM user_restrictions WHERE user_id = 5 AND is_active = 1;
```

## Polling vs Real-Time

### Current Implementation: Polling
- Checks every 30 seconds
- Works without WebSocket/Pusher
- No external dependencies
- Slight delay (up to 30 seconds)
- Lightweight implementation

### Optional Upgrade: Real-Time Broadcasting
- Use Laravel Echo + Pusher for instant notifications
- Requires Pusher account setup
- Zero latency
- See `REAL_TIME_NOTIFICATIONS_SETUP.md`

## Fallback Behavior

If polling fails:
1. Notifications still load on app startup
2. User can manually refresh notification list
3. Report blocking still works (backend validation)
4. No user-facing errors

## Configuration

### Polling Interval
Default: 30 seconds
To change, edit in `app/(tabs)/index.tsx`:
```typescript
notificationService.startNotificationPolling(
  userId,
  callback,
  60000  // 60 seconds instead of 30
)
```

### Polling Auto-Start
Enabled by default when user logs in
Stops when user logs out or navigates away

## What's Working Now

✅ Flag notification pops up automatically
✅ Flagged users cannot submit reports
✅ Notification shows violation type
✅ Notification shows applied restriction
✅ Red styling indicates severity
✅ Polling every 30 seconds
✅ Notification history maintained
✅ Backend report validation

## Next Steps (Optional)

1. **Set up Pusher** for real-time notifications (see REAL_TIME_NOTIFICATIONS_SETUP.md)
2. **Configure email notifications** for flagged users
3. **Add notification sound** when user is flagged
4. **Add notification badge** count on home screen
5. **Customize restriction messages** per user

## Troubleshooting

### Notification Not Appearing
- Check if user is logged in
- Verify userId matches in database
- Check if notification was created: `SELECT * FROM notifications WHERE user_id = X`
- Check browser console for errors
- Wait up to 30 seconds for polling interval

### Report Button Still Enabled for Flagged User
- Clear app cache and restart
- Check `flag-status` endpoint returns `is_flagged: true`
- Verify `isFlagged` state in report.tsx
- Check backend restriction validation

### Notifications Not Loading
- Check backend API is running
- Verify database connection
- Check `/notifications/{userId}` endpoint responds
- Check network tab in browser DevTools
