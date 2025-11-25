# Notification Read/Unread Status System

## Overview
This system tracks which notifications each user has read, ensuring that notification counts only show **new/unread** notifications and persist across login sessions.

## Problem Solved
Previously, all notifications appeared as "new" every time a user logged in because there was no persistent read status tracking. Now notifications maintain their read/unread state permanently.

## Database Schema

### notification_reads Table
```sql
CREATE TABLE notification_reads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  notification_id VARCHAR(50) NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_notification (user_id, notification_id)
);
```

**Columns:**
- `id`: Primary key
- `user_id`: The user who read the notification
- `notification_id`: String identifier for the notification (e.g., "msg_123", "report_456")
- `read_at`: When the notification was marked as read
- **Unique constraint**: Prevents duplicate read records for the same user/notification

## Notification ID Format

Notifications are identified by a **string prefix + ID** to avoid conflicts:

| Type | Prefix | Example | Description |
|------|--------|---------|-------------|
| Messages | `msg_` | `msg_123` | New chat message received |
| Reports | `report_` | `report_456` | Report submission confirmation |
| Report Updates | `update_` | `update_789` | Report status changed |
| Verifications | `verify_` | `verify_101` | Account verification status |

## How It Works

### 1. Getting Notifications (getUserNotifications)
When fetching notifications, the backend:
1. Queries messages, reports, report updates, and verifications
2. **LEFT JOINs** with `notification_reads` table
3. Sets `read: true` if a record exists in `notification_reads`
4. Sets `read: false` if no record exists
5. Returns all notifications with their read status

**Example Query:**
```sql
SELECT 
  m.message_id,
  m.message,
  m.created_at,
  CASE WHEN nr.read_at IS NOT NULL THEN TRUE ELSE FALSE END as is_read
FROM messages m
LEFT JOIN notification_reads nr 
  ON nr.user_id = ? 
  AND nr.notification_id = CONCAT('msg_', m.message_id)
WHERE m.receiver_id = ?
ORDER BY m.created_at DESC
LIMIT 10
```

### 2. Marking as Read (markNotificationAsRead)
When a user views/clicks a notification:
1. Frontend calls `markAsRead(notificationId, userId)`
2. Backend inserts a record into `notification_reads`
3. Uses `INSERT IGNORE` to prevent duplicate errors
4. Notification now appears as read on next fetch

**Example:**
```javascript
// User clicks notification with id "report_123"
await markAsRead('report_123', userId);

// Backend executes:
INSERT IGNORE INTO notification_reads (user_id, notification_id, read_at) 
VALUES (1, 'report_123', NOW())
```

## Frontend Integration

### NotificationPopup Component
Already implemented - gray text styling for read notifications:
```tsx
<Text style={notification.read ? styles.readText : styles.unreadText}>
  {notification.message}
</Text>
```

### Marking Notifications as Read
Call `markAsRead()` when user:
- Clicks/taps a notification
- Views notification details
- Opens notification popup (mark all visible as read)

**Example:**
```typescript
import { markAsRead } from '@/services/notificationService';

const handleNotificationPress = async (notification: Notification) => {
  if (!notification.read) {
    await markAsRead(notification.id, userId);
    // Reload notifications to update UI
    await loadNotifications();
  }
  // Navigate to related content
};
```

### Unread Count Badge
Calculate unread count from notifications:
```typescript
const unreadCount = notifications.filter(n => !n.read).length;

// Display on bell icon
<Badge count={unreadCount} />
```

## Installation Steps

### 1. Run Database Migration
```bash
cd UserSide/backends
run_migration.bat
```

Or manually:
```bash
mysql -u root -p alertdavao < migrations/create_notification_reads_table.sql
```

### 2. Restart Backend Server
```bash
cd UserSide/backends
node server.js
```

### 3. Clear App Cache (Optional)
If using Expo:
```bash
npx expo start --clear
```

## Testing Checklist

- [ ] Create test notifications (send message, create report)
- [ ] Verify all show as unread initially (count shows on bell icon)
- [ ] Click a notification
- [ ] Verify it's marked as read (grayed out text)
- [ ] Logout and login again
- [ ] Verify read status persists (notification still grayed out)
- [ ] Check unread count decreased by 1
- [ ] Create new notification
- [ ] Verify only new notification is highlighted
- [ ] Check bell badge shows correct unread count

## API Endpoints

### GET /notifications/:userId
Fetches all notifications with read status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "msg_123",
      "title": "You have a new Message",
      "message": "Hello there!",
      "timestamp": "2024-01-15T10:30:00Z",
      "read": false,
      "type": "message"
    },
    {
      "id": "report_456",
      "title": "You have successfully submitted a new report",
      "message": "Submitted on 1/15/2024 at 10:25 AM",
      "timestamp": "2024-01-15T10:25:00Z",
      "read": true,
      "type": "report"
    }
  ]
}
```

### PATCH /notifications/:notificationId/read
Marks a notification as read

**Request Body:**
```json
{
  "userId": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

## Files Modified

1. **UserSide/backends/handleNotifications.js**
   - Updated `getUserNotifications()` to LEFT JOIN with `notification_reads`
   - Updated `markNotificationAsRead()` to INSERT into `notification_reads`
   - Changed notification IDs from numeric offsets to string prefixes

2. **UserSide/backends/migrations/create_notification_reads_table.sql** (NEW)
   - SQL migration to create the `notification_reads` table

3. **UserSide/backends/run_migration.bat** (NEW)
   - Helper script to run the migration easily

## Benefits

✅ **Persistent Read Status** - Notifications stay marked as read across sessions  
✅ **Accurate Unread Count** - Bell badge only shows truly unread notifications  
✅ **Better UX** - Users aren't overwhelmed by old notifications  
✅ **Scalable** - Uses efficient database joins instead of storing flags  
✅ **Clean Architecture** - Single source of truth for read status  

## Troubleshooting

### Notifications still showing as unread after clicking
- Check browser console for API errors
- Verify `markAsRead()` is being called with correct notification ID
- Check database for inserted records: `SELECT * FROM notification_reads WHERE user_id = 1`

### Migration fails
- Verify MySQL is running
- Check database credentials in `run_migration.bat`
- Ensure database name is correct (`alertdavao`)
- Try running SQL manually in phpMyAdmin or MySQL Workbench

### Backend errors after update
- Restart Node.js server
- Check for syntax errors in `handleNotifications.js`
- Verify `notification_reads` table exists: `SHOW TABLES LIKE 'notification_reads'`

## Future Enhancements

- [ ] Add "Mark all as read" button
- [ ] Add notification preferences (mute certain types)
- [ ] Add push notifications for mobile
- [ ] Add email notifications
- [ ] Add notification history page
- [ ] Add notification grouping (e.g., "3 new messages")

---

**Last Updated:** January 2024  
**Version:** 1.0.0
