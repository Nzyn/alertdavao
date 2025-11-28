# Real-Time Notifications Implementation Summary

## What Was Added

When a user is flagged in AdminSide, the system now:

1. **Creates a persistent notification** in the database
2. **Broadcasts a real-time event** to the flagged user via Laravel Broadcasting
3. **Provides API endpoints** to query and manage notifications

## Quick Setup (5 minutes)

```bash
# 1. Run migrations
cd AdminSide/admin
php artisan migrate

# 2. Clear event cache
php artisan event:clear

# 3. Done!
```

## Files Summary

| File | Purpose |
|------|---------|
| `app/Events/UserFlagged.php` | Defines the broadcast event |
| `app/Models/Notification.php` | Notification database model |
| `app/Http/Controllers/NotificationController.php` | 6 notification endpoints |
| `app/Http/Controllers/UserController.php` (modified) | Triggers notification on flag |
| `routes/web.php` (modified) | Notification API routes |
| `routes/channels.php` (modified) | Private broadcast channel |
| `config/broadcasting.php` (modified) | Set to 'log' driver by default |

## API Endpoints

```
GET  /api/notifications/unread           # Get unread notifications
GET  /api/notifications                  # Get all notifications (with pagination)
GET  /api/notifications/unread-count     # Get unread count
POST /api/notifications/{id}/read        # Mark as read
POST /api/notifications/read-all         # Mark all as read
DELETE /api/notifications/{id}           # Delete notification
```

## What Happens When User is Flagged

```
Admin/Police flags user
        ↓
Notification record created in DB
        ↓
UserFlagged event dispatched
        ↓
Event broadcast to user's private channel (user.{userId})
        ↓
Frontend receives real-time notification
```

## Notification Structure

```json
{
    "id": 1,
    "user_id": 5,
    "type": "user_flagged",
    "message": "Your account has been flagged for: False Report. A warning restriction has been applied.",
    "data": {
        "flag_id": 123,
        "violation_type": "false_report",
        "reason": "User submitted false crime report",
        "total_flags": 1,
        "restriction_applied": "warning"
    },
    "read": false,
    "created_at": "2025-11-28T10:30:00Z"
}
```

## Broadcasting Drivers

- **Development (Default)**: `log` - Logs events to storage/logs/laravel.log
- **Production Options**:
  - Pusher (recommended)
  - Redis
  - Ably

See `REAL_TIME_NOTIFICATIONS_SETUP.md` for production setup.

## Frontend Usage Example

### Using Laravel Echo (Real-time)
```javascript
window.Echo.private(`user.${userId}`)
    .listen('UserFlagged', (data) => {
        console.log('You have been flagged!', data);
        // Show notification popup
    });
```

### Using API Polling
```javascript
setInterval(async () => {
    const res = await fetch('/api/notifications/unread');
    const { data } = await res.json();
    // Show notifications
}, 30000);
```

## Testing

**Test flagging with curl:**
```bash
curl -X POST http://localhost:8000/users/5/flag \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_token" \
  -d '{"violation_type": "false_report", "reason": "Test"}'
```

**Check database:**
```sql
SELECT * FROM notifications WHERE user_id = 5;
```

**Check broadcast logs:**
```bash
tail storage/logs/laravel.log | grep "user.flagged"
```

## Key Features

✅ Persistent notifications (stored in DB)
✅ Real-time broadcasts (WebSocket-ready)
✅ Private channels (only recipient gets notification)
✅ Scalable drivers (Pusher, Redis, Ably)
✅ RESTful API for notification management
✅ Unread count tracking
✅ Mark as read functionality
✅ Notification history
