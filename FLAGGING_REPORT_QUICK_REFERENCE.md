# Flag Restriction - Quick Reference

## What Was Added

Real-time notification and report submission restriction when a user account is flagged by an admin.

## File Changed

- `UserSide/app/(tabs)/report.tsx` - ONLY FILE MODIFIED

## Key Code Sections

### 1. Flag Status Check (On Page Focus)

```typescript
useFocusEffect(
    React.useCallback(() => {
        const loadUserAndCheckFlags = async () => {
            // Load user ID from storage
            // Fetch notifications from backend
            // Find if type === 'user_flagged'
            // Update isFlagged state
        };
        loadUserAndCheckFlags();
    }, [])
);
```

### 2. Block Submission

```typescript
const handleSubmit = async () => {
    if (isFlagged) {
        Alert.alert('Account Flagged', 'You cannot submit reports...');
        return;
    }
    // ... rest of validation
};
```

### 3. UI Changes

**Toast Notification:**
```typescript
<FlagNotificationToast
    notification={flagNotification}
    onDismiss={() => setFlagNotification(null)}
/>
```

**Submit Button:**
```typescript
<Button
    title={isFlagged ? "Account Flagged - Cannot Submit" : "Submit Report"}
    disabled={isSubmitting || isFlagged}
/>
```

**Warning Box:**
```typescript
{isFlagged && (
    <View style={{ backgroundColor: '#fee2e2', borderLeftColor: '#dc2626' }}>
        {/* Red warning box with icon and message */}
    </View>
)}
```

## User Flow

1. User navigates to Report page
2. System checks: Is user flagged?
   - YES → Toast appears, button disabled, warning shown
   - NO → Page loads normally, button enabled
3. User tries to submit
   - If flagged → Blocked with Alert
   - If not flagged → Proceeds normally

## States

### State 1: Normal User (Not Flagged)
- `isFlagged = false`
- `flagNotification = null`
- Toast: Hidden
- Button: Enabled (blue, "Submit Report")
- Warning: Hidden

### State 2: Flagged User
- `isFlagged = true`
- `flagNotification = {notification object}`
- Toast: Visible (red, auto-dismiss in 8s)
- Button: Disabled (gray, "Account Flagged - Cannot Submit")
- Warning: Visible (red box with explanation)

## Key Variables

| Variable | Type | Purpose |
|----------|------|---------|
| `isFlagged` | boolean | User is flagged? |
| `flagNotification` | Notification \| null | Flag notification details |
| `userId` | string | Current user ID |

## Colors Used

| Usage | Color | Hex |
|-------|-------|-----|
| Toast background | Red | #dc2626 |
| Toast text | White | #fff |
| Warning box bg | Light Red | #fee2e2 |
| Warning box border | Red | #dc2626 |
| Warning icon | Red | #dc2626 |
| Disabled button | Gray | #999 |

## Event Flow

```
User navigates to Report page
    ↓
useFocusEffect triggers
    ↓
Load user ID from AsyncStorage
    ↓
Fetch notifications via API
    ↓
Find type === 'user_flagged' notification
    ↓
IF found:
    setIsFlagged(true)
    setFlagNotification(data)
    Toast component renders
    Button disabled attribute set
    Warning box appears
ELSE:
    setIsFlagged(false)
    setFlagNotification(null)
    Toast hidden
    Button enabled
```

## Validation Layers

1. **UI Layer**: Button disabled (CSS/attribute)
2. **Logic Layer**: handleSubmit() checks isFlagged
3. **Dialog Layer**: Alert shown if somehow bypassed
4. **Backend Layer**: Backend should also validate (ultimate safety)

## Testing Checklist

- [ ] Unflagged user sees no flag UI
- [ ] Flagged user sees toast notification
- [ ] Flagged user sees disabled button
- [ ] Flagged user sees warning box
- [ ] Toast auto-closes after 8 seconds
- [ ] Manual close button works
- [ ] Submit button is actually disabled
- [ ] If flag removed, UI updates on page re-visit
- [ ] Alert shown if user somehow clicks submit while flagged

## API Requirements

Backend must provide:

```
GET /notifications/{userId}
{
  "success": true,
  "data": [
    {
      "type": "user_flagged",
      "data": {
        "violation_type": "...",
        "restriction_applied": "...",
        "total_flags": 1
      }
    }
  ]
}
```

## Imports Added

```typescript
import { router, useFocusEffect } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import FlagNotificationToast from '../../components/FlagNotificationToast';
import { notificationService, type Notification } from '../../services/notificationService';
```

## Dependencies

- **New**: None
- **Used Existing**: 
  - FlagNotificationToast component
  - notificationService
  - AsyncStorage
  - useFocusEffect hook

## Performance

- Page load: +1 API call (notification fetch) - same as existing system
- Animation: Smooth (uses Native Driver)
- Memory: No leaks (useEffect cleanup handled)
- Re-renders: Only when state changes

## Troubleshooting

| Problem | Cause | Solution |
|---------|-------|----------|
| Toast not showing | `isFlagged` still false | Check notification API response |
| Button still enabled | `isFlagged` not set | Refresh page, check console |
| Warning box missing | Conditional render failed | Check `isFlagged` state |
| API call fails | Network error | App gracefully continues (fails open) |

## Fallback Behavior

If notification system fails:
- ✅ User can still access report page
- ✅ Form is still usable
- ❌ Flag restriction is lost (but backend should catch this)

To improve: Add backend validation as final safety net.

## Real-Time Updates

**Not real-time (polling-based):**
- When admin flags → User sees it next time they navigate to report page
- Default check interval: Every 5-30 seconds (if on home page)
- Report page only checks on page focus

**For true real-time:**
- Upgrade to WebSocket notifications
- Or increase polling frequency

## Code Size

- **Lines added**: ~50 lines (state + hook + UI)
- **Files modified**: 1 file
- **Breaking changes**: 0
- **New dependencies**: 0

---

**Quick Links:**
- Full docs: `FLAGGING_REPORT_RESTRICTION_IMPLEMENTATION.md`
- Testing: `FLAGGING_REPORT_TESTING.md`
- High-level: `FLAGGING_REPORT_SUMMARY.md`
