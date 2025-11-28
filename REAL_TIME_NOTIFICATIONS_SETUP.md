# Real-Time Notifications for User Flagging System

## Overview

The flagging system now sends **real-time notifications** when a user is flagged. Notifications are:
1. **Stored in database** (persistent)
2. **Broadcast in real-time** via Laravel Broadcasting
3. **Queryable via API** for frontend consumption

## Files Created/Modified

### New Files Created
1. `app/Events/UserFlagged.php` - Broadcast event triggered when user is flagged
2. `app/Models/Notification.php` - Notification model for database operations
3. `app/Http/Controllers/NotificationController.php` - API endpoints for notifications
4. `database/migrations/2025_11_28_000000_create_user_flags_table.php`
5. `database/migrations/2025_11_28_000001_create_user_restrictions_table.php`
6. `database/migrations/2025_11_28_000002_add_flag_columns_to_users_table.php`

### Modified Files
1. `app/Http/Controllers/UserController.php` - Updated `flagUser()` to create notifications and dispatch events
2. `routes/web.php` - Added notification API routes
3. `routes/channels.php` - Added private channel for user notifications
4. `config/broadcasting.php` - Set default broadcast driver to 'log'
5. `database/migrations/2025_11_16_000003_create_notifications_table.php` - Enhanced with JSON data and indexes
6. `app/Http/Kernel.php` - Registered role middleware (from previous fix)

## How It Works

### Flow Diagram
```
Admin/Police flags user
        ↓
flagUser() controller executed
        ↓
1. Create flag record in user_flags table
2. Update user's total_flags count
3. Auto-apply restriction if needed
4. CREATE Notification record
5. DISPATCH UserFlagged broadcast event
        ↓
Notification saved to DB
Broadcast sent to user's private channel
        ↓
Frontend listens on private channel
        ↓
Real-time notification received
```

## Database Schema

### notifications table
```sql
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT FOREIGN KEY,
    type VARCHAR(255),              -- e.g., 'user_flagged'
    message TEXT,                   -- Human-readable message
    data JSON,                       -- Additional data (flag_id, violation_type, etc.)
    read BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX user_id,
    INDEX read,
    INDEX created_at
);
```

### Example notification data
```json
{
    "flag_id": 123,
    "violation_type": "false_report",
    "reason": "Submitted false crime report",
    "total_flags": 1,
    "restriction_applied": "warning"
}
```

## API Endpoints

### Get Unread Notifications
```
GET /api/notifications/unread
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "type": "user_flagged",
            "message": "Your account has been flagged for: False Report. A warning restriction has been applied to your account.",
            "data": {
                "flag_id": 123,
                "violation_type": "false_report",
                "reason": "Submitted false report",
                "total_flags": 1,
                "restriction_applied": "warning"
            },
            "read": false,
            "created_at": "2025-11-28T10:30:00Z",
            "updated_at": "2025-11-28T10:30:00Z"
        }
    ],
    "count": 1
}
```

### Get All Notifications
```
GET /api/notifications?limit=20&offset=0
```

### Get Unread Count
```
GET /api/notifications/unread-count
```

**Response:**
```json
{
    "success": true,
    "unread_count": 3
}
```

### Mark as Read
```
POST /api/notifications/{id}/read
```

### Mark All as Read
```
POST /api/notifications/read-all
```

### Delete Notification
```
DELETE /api/notifications/{id}
```

## Real-Time Broadcasting

### Event Details
The `UserFlagged` event broadcasts on a private channel:

**Channel:** `user.{userId}`
**Event Name:** `user.flagged`

**Broadcast Data:**
```json
{
    "flag_id": 123,
    "violation_type": "false_report",
    "reason": "Submitted false report",
    "total_flags": 1,
    "restriction_applied": "warning",
    "flagged_at": "2025-11-28T10:30:00Z",
    "message": "Your account has been flagged for: False Report. A warning restriction has been applied to your account."
}
```

## Frontend Integration (UserSide)

### Setup with Laravel Echo (Recommended)

**Installation:**
```bash
npm install laravel-echo pusher-js
```

**Configuration (resources/js/bootstrap.js):**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
});
```

**Listen to User Flagged Event:**
```javascript
// In your React/Vue component
useEffect(() => {
    window.Echo.private(`user.${userId}`)
        .listen('UserFlagged', (data) => {
            console.log('User flagged notification:', data);
            
            // Show notification to user
            showNotificationPopup({
                type: 'warning',
                title: 'Account Flagged',
                message: data.message,
                data: data
            });
            
            // Update UI with new restriction info
            if (data.restriction_applied) {
                updateUserRestrictions(data.restriction_applied);
            }
        });
    
    return () => {
        window.Echo.leaveChannel(`user.${userId}`);
    };
}, [userId]);
```

### Alternative: Polling (Without Real-Time)

If you prefer polling instead of real-time broadcasting:

```javascript
// Check for new notifications every 30 seconds
useEffect(() => {
    const interval = setInterval(async () => {
        const response = await fetch('/api/notifications/unread');
        const { data } = await response.json();
        
        if (data.length > 0) {
            data.forEach(notification => {
                if (notification.type === 'user_flagged') {
                    showNotificationPopup({
                        type: 'warning',
                        title: 'Account Flagged',
                        message: notification.message
                    });
                }
            });
        }
    }, 30000);
    
    return () => clearInterval(interval);
}, []);
```

## Development Setup

### Using Log Driver (Development)
By default, the system uses the `log` driver for broadcasting. This logs all events to `storage/logs/laravel.log`:

```bash
# Monitor log file for broadcast events
tail -f storage/logs/laravel.log
```

Example log output:
```
[2025-11-28 10:30:00] local.DEBUG: Broadcast: user.flagged {"flag_id":123,"violation_type":"false_report",...}
```

### Testing Real-Time Notifications

**Using Postman/Curl:**
```bash
# Flag a user
curl -X POST http://localhost:8000/users/5/flag \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN" \
  -d '{
    "violation_type": "false_report",
    "reason": "Submitted false report"
  }'
```

**Check database:**
```sql
SELECT * FROM notifications WHERE user_id = 5 ORDER BY created_at DESC;
```

**Check broadcast logs:**
```bash
tail storage/logs/laravel.log | grep "user.flagged"
```

## Production Setup

### Option 1: Pusher (Recommended)

1. **Sign up at:** https://pusher.com
2. **Add to .env:**
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

3. **Restart application**

### Option 2: Redis

1. **Install Redis** (if not already installed)
2. **Add to .env:**
```
BROADCAST_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

3. **Run Laravel WebSocket server:**
```bash
php artisan websockets:serve
```

### Option 3: Ably

1. **Sign up at:** https://www.ably.io
2. **Add to .env:**
```
BROADCAST_DRIVER=ably
ABLY_KEY=your_ably_key
```

## Setup Checklist

- [ ] Run migrations:
  ```bash
  php artisan migrate
  ```

- [ ] Clear event cache:
  ```bash
  php artisan event:clear
  ```

- [ ] Test flag user endpoint from AdminSide
- [ ] Verify notification created in database
- [ ] Check broadcast logged (in storage/logs/laravel.log)
- [ ] Test API endpoints:
  ```bash
  curl http://localhost:8000/api/notifications/unread
  ```

## Troubleshooting

### No notifications in database
- Check if migrations ran: `php artisan migrate:status`
- Verify user_id exists in users table
- Check controller logs for errors

### Broadcasts not being logged
- Verify `BROADCAST_DRIVER=log` in .env
- Check `storage/logs/laravel.log` exists and is writable
- Restart Laravel application

### Private channel authorization fails
- Ensure user is authenticated
- Check `routes/channels.php` has user channel defined
- Verify user ID matches in frontend and backend

## Architecture Notes

1. **Notifications are persistent** - Stored in database for history
2. **Broadcasting is optional** - Works without real-time if needed
3. **Role-based access** - Only admin/police can flag users
4. **Scalable** - Works with Pusher, Redis, or Ably for production
5. **Backward compatible** - Existing APIs still work
