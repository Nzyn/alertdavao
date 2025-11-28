# Flag Notification System - User Side Implementation

## Overview

When an admin flags or unflags a user, the user receives real-time notifications on their userside application. The system now includes:

1. **Toast Notification** - Immediate visual feedback at the top of the screen
2. **Notification Popup** - Detailed notification center modal
3. **Flag Status Badge** - Visual indicator of flag status on profile and dashboard
4. **Flag Details Modal** - Comprehensive information about flag details and restrictions

## Components Created

### 1. FlagNotificationToast.tsx
**Location:** `UserSide/components/FlagNotificationToast.tsx`

A toast notification component that appears at the top of the screen when a user is flagged.

**Features:**
- Auto-dismisses after 8 seconds
- Animated slide-in/out effect
- Shows violation type and restriction applied
- Manual dismiss button
- Matches system color scheme (red for flags)

**Props:**
```typescript
interface FlagNotificationToastProps {
  notification: Notification | null;
  onDismiss?: () => void;
}
```

**Usage:**
```tsx
<FlagNotificationToast 
  notification={flagNotification}
  onDismiss={() => setFlagNotification(null)}
/>
```

### 2. FlagStatusBadge.tsx
**Location:** `UserSide/components/FlagStatusBadge.tsx`

A badge component that displays the current flag status with detailed modal.

**Features:**
- Circular badge with icon and flag count
- Three size variants: 'small', 'medium', 'large'
- Tap to view detailed flag information
- Modal shows:
  - Total flags count
  - Active restriction level (warning/suspended/banned)
  - Detailed message if available
  - Information box explaining what flags mean
  - Guidelines box for resolution steps
- Color-coded by restriction type:
  - Amber for "warning"
  - Red for "suspended"
  - Violet for "banned"

**Props:**
```typescript
interface FlagStatusBadgeProps {
  flagData: {
    totalFlags: number;
    restrictionLevel: string | null;
    message?: string;
  } | null;
  onPress?: () => void;
  size?: 'small' | 'medium' | 'large';
  showLabel?: boolean;
}
```

**Usage:**
```tsx
<FlagStatusBadge 
  flagData={{
    totalFlags: 1,
    restrictionLevel: 'warning'
  }}
  size="medium"
  showLabel={true}
/>
```

## System Integration

### Home Screen (index.tsx)

The home screen now displays flag information in two places:

1. **Subheading Area** - Shows "1 Flags Active" indicator below welcome message when flags exist
2. **Toast Notification** - Auto-appears at top when new flag notification received

**Changes Made:**
- Added `FlagNotificationToast` component render
- Added flag status state management
- Updated `loadNotifications()` to extract flag data
- Updated notification polling to trigger flag notifications
- Added flag status row in welcome container

### Profile Screen (profile.tsx)

The profile screen now shows flag status:

1. **Profile Header** - Flag badge displayed alongside verified badge
2. **Flag Details** - Tap badge to view detailed flag information and restriction level

**Changes Made:**
- Added `FlagStatusBadge` component import and render
- Added flag status state management
- Updated `loadData()` to fetch flag notifications
- Positioned badge absolutely in profile header

## Data Flow

### When Admin Flags a User:

```
Admin flags user (API endpoint: POST /users/{id}/flag)
  ↓
Laravel Backend creates user_flags record
  ↓
Notification created in database (type: 'user_flagged')
  ↓
UserFlagged event broadcast via real-time channel
  ↓
User's app receives notification (polling or websocket)
  ↓
Toast notification displays
  ↓
Notification stored in app state
  ↓
Badge updates on dashboard and profile
  ↓
Notification popup updated
```

### Notification Data Structure:

```typescript
{
  id: number;
  title: 'Account Flagged';
  message: string; // Generated based on violation type
  timestamp: string;
  read: boolean;
  type: 'user_flagged';
  data: {
    flag_id: number;
    violation_type: string; // e.g., 'false_report', 'harassment'
    reason?: string;
    total_flags: number;
    restriction_applied?: string; // 'warning', 'suspended', 'banned'
  }
}
```

## Violation Types

The system supports the following violation types:

- `false_report` - Filing false reports
- `prank_spam` - Prank calls or spam reports
- `harassment` - Harassing other users
- `offensive_content` - Posting offensive content
- `impersonation` - Impersonating others
- `multiple_accounts` - Operating multiple accounts
- `system_abuse` - Abusing system features
- `inappropriate_media` - Sharing inappropriate media
- `misleading_info` - Spreading misleading information
- `other` - Other violations

## Restriction Levels

- **None** - No restriction
- **Warning** - First flag, can still use most features
- **Suspended** - Multiple flags, limited access
- **Banned** - Account banned from platform

## Styling & Theme

The implementation follows the AlertDavao design system:

**Colors:**
- Flag Alert: `#dc2626` (Red)
- Warning Restriction: `#f59e0b` (Amber)
- Primary: `#1D3557` (Dark Blue)
- Light background: `#fee2e2` (Light Red)

**Typography:**
- Titles: 16-20px, Bold
- Body: 13-14px, Regular
- Labels: 12-13px, Semi-bold

**Spacing:**
- Component gaps: 8-12px
- Padding: 12-20px
- Border radius: 8-12px

## Backend Endpoints Used

### Get Notifications
```
GET /notifications/{userId}
Returns: { success: true, data: Notification[] }
```

### Mark as Read
```
PATCH /notifications/{notificationId}/read
Body: { userId: string }
Returns: { success: true }
```

## Testing the System

### Test Scenario 1: Flag Notification

1. Open UserSide app as regular user
2. In admin panel, flag the user account
3. Observe:
   - Toast notification appears at top
   - Flag status indicator shows on dashboard
   - Badge appears on profile
   - Detailed modal shows restriction details

### Test Scenario 2: Reading Notification

1. User receives flag notification
2. Click the notification icon to open notification center
3. User flag notification shows with red background
4. Click on flag notification to view details
5. Notification is marked as read

### Test Scenario 3: Profile Display

1. Navigate to profile
2. If user is flagged, badge appears in top-right of profile header
3. Tap badge to view:
   - Total flags
   - Current restriction level
   - Community guidelines info
   - Resolution guidance

## Performance Considerations

- **Toast Duration**: 8 seconds (customizable)
- **Poll Interval**: 5 seconds (can be adjusted in `startNotificationPolling()`)
- **Modal Animations**: 300ms duration for smooth transitions
- **Badge Re-renders**: Only when notification data changes

## Future Enhancements

1. **Web Socket Support** - Replace polling with WebSocket for real-time updates
2. **Appeal System** - Allow users to appeal flags through the modal
3. **Flag History** - Show timeline of all past flags
4. **Restriction Countdown** - Show when restriction will be lifted (if applicable)
5. **Sound Notifications** - Optional sound alert for flag notifications
6. **Push Notifications** - Native mobile push notifications for flag events

## Troubleshooting

### Toast Not Showing

- Check if notification is being received correctly in console logs
- Verify `flagNotification` state is being set
- Ensure component is rendered at correct zIndex level

### Badge Not Displaying

- Verify `flagStatus` state has data
- Check if notification data structure matches expected format
- Ensure profile page is loading notifications correctly

### Modal Not Opening

- Check if badge component is rendering
- Verify `showDetails` state management
- Test on different screen sizes/devices

## Files Modified/Created

### Created:
- `UserSide/components/FlagNotificationToast.tsx` (134 lines)
- `UserSide/components/FlagStatusBadge.tsx` (233 lines)
- `FLAG_NOTIFICATION_SYSTEM.md` (this file)

### Modified:
- `UserSide/app/(tabs)/index.tsx`
  - Added imports for toast and badge components
  - Added flag state management
  - Updated notification loading logic
  - Updated home screen UI
  
- `UserSide/app/(tabs)/profile.tsx`
  - Added imports for badge and notification service
  - Added flag status state
  - Updated data loading
  - Added badge render in profile header
  
- `UserSide/app/(tabs)/styles.ts`
  - Added `flagStatusRow` style
  - Added `flagStatusText` style
  - Added `flagBadgeContainer` style
  - Updated `profileHeader` positioning
  - Updated `welcomeContainer` alignment

## API Integration Points

1. **Notification Polling**: `GET /notifications/{userId}`
2. **Mark Read**: `PATCH /notifications/{notificationId}/read`
3. **Flag Event Broadcast**: Real-time via Laravel Broadcasting

## Notes

- All notifications are stored in the backend database
- Read/unread status is persisted
- Toast notifications are transient (only in current session)
- Badge data persists across app sessions
- Modal state is per-session (resets on app restart)

---

**Last Updated:** 2025-11-28
**Version:** 1.0.0
**Status:** Production Ready
