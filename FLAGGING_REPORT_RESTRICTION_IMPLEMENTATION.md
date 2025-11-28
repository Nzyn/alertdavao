# Flag Restriction on Report Submission - Implementation Guide

## Overview

This document explains the implementation of real-time flag notification on the Report page and the restriction that prevents flagged users from submitting reports.

## What Was Implemented

### 1. Flag Notification Toast on Report Page
- **Location**: Report page top section
- **Trigger**: When user navigates to the report page and has an active flag
- **Appearance**: Red warning toast with flag icon, message, and restriction details
- **Duration**: Auto-dismisses after 8 seconds or can be manually closed
- **Component**: `FlagNotificationToast.tsx`

### 2. Report Submission Restriction
- **Check Point**: `handleSubmit()` function
- **Condition**: If user has `isFlagged = true`, shows alert and blocks submission
- **Button State**: 
  - Text changes to "Account Flagged - Cannot Submit"
  - Button becomes disabled (grayed out)
  - Disabled state: `disabled={isSubmitting || isFlagged}`
- **User Feedback**: 
  - Visual warning box below the button
  - Clear explanation of why they can't submit
  - Red color scheme matching flag notification

### 3. Real-Time Flag Status Monitoring
- **Hook**: `useFocusEffect` from `expo-router`
- **Timing**: Runs when user navigates TO the report page
- **Action**:
  1. Loads user ID from AsyncStorage
  2. Fetches latest notifications from backend
  3. Finds `user_flagged` type notification
  4. Updates `isFlagged` and `flagNotification` state
  5. Shows toast if flag exists

## File Changes

### Modified: `UserSide/app/(tabs)/report.tsx`

#### Imports Added
```typescript
import { router, useFocusEffect } from 'expo-router';  // Added useFocusEffect
import AsyncStorage from '@react-native-async-storage/async-storage';
import FlagNotificationToast from '../../components/FlagNotificationToast';
import { notificationService, type Notification } from '../../services/notificationService';
import React, { useState, useEffect, useCallback } from 'react';  // Added useCallback
```

#### State Variables Added
```typescript
const [flagNotification, setFlagNotification] = useState<Notification | null>(null);
const [isFlagged, setIsFlagged] = useState(false);
const [userId, setUserId] = useState<string>('');
```

#### New Effect Hook
```typescript
useFocusEffect(
    React.useCallback(() => {
        const loadUserAndCheckFlags = async () => {
            try {
                const userData = await AsyncStorage.getItem('userData');
                if (userData) {
                    const parsedUser = JSON.parse(userData);
                    const currentUserId = parsedUser.id || parsedUser.user_id || '';
                    setUserId(currentUserId);
                    
                    // Load notifications to check if user is flagged
                    const notifications = await notificationService.getUserNotifications(currentUserId);
                    
                    // Find the latest user_flagged notification
                    const flaggedNotification = notifications.find(n => n.type === 'user_flagged');
                    
                    if (flaggedNotification) {
                        setFlagNotification(flaggedNotification);
                        setIsFlagged(true);
                        console.log('User is flagged:', flaggedNotification);
                    } else {
                        setIsFlagged(false);
                        setFlagNotification(null);
                    }
                }
            } catch (error) {
                console.error('Error loading user flag status:', error);
            }
        };
        
        loadUserAndCheckFlags();
    }, [])
);
```

#### Updated `handleSubmit()` Method
```typescript
const handleSubmit = async () => {
    console.log('🔍 Validating report submission...');

    // Check if user is flagged
    if (isFlagged) {
        Alert.alert(
            'Account Flagged',
            'Your account has been flagged. You are unable to submit new reports until the flag is lifted by an administrator.',
            [{ text: 'OK' }]
        );
        return;
    }
    
    // ... rest of validation logic
};
```

#### Updated Submit Button
```typescript
<View style={styles.submitButton}>
    <Button
        title={isFlagged ? "Account Flagged - Cannot Submit" : (isSubmitting ? "Submitting..." : "Submit Report")}
        onPress={handleSubmit}
        color={isFlagged ? "#999" : "#1D3557"}
        disabled={isSubmitting || isFlagged}
    />
</View>

{isFlagged && (
    <View style={{
        backgroundColor: '#fee2e2',
        borderLeftWidth: 4,
        borderLeftColor: '#dc2626',
        padding: 12,
        marginTop: 12,
        marginHorizontal: 12,
        borderRadius: 6,
    }}>
        <View style={{ flexDirection: 'row', alignItems: 'flex-start', gap: 8 }}>
            <Ionicons name="warning" size={18} color="#dc2626" style={{ marginTop: 2 }} />
            <View style={{ flex: 1 }}>
                <Text style={{ fontSize: 14, fontWeight: '600', color: '#991b1b', marginBottom: 4 }}>
                    Account Flagged
                </Text>
                <Text style={{ fontSize: 13, color: '#7f1d1d', lineHeight: 18 }}>
                    Your account has been flagged. You are unable to submit new reports until the flag is lifted by an administrator.
                </Text>
            </View>
        </View>
    </View>
)}
```

#### Added Toast Notification Rendering
```typescript
return (
    <ScrollView style={styles.screen} contentContainerStyle={{ paddingBottom: 48 }}>
        {/* Flag Notification Toast */}
        <FlagNotificationToast
            notification={flagNotification}
            onDismiss={() => setFlagNotification(null)}
        />
        
        {/* Rest of the form... */}
    </ScrollView>
);
```

## How It Works

### User Journey

1. **User navigates to Report page**
   - `useFocusEffect` hook triggers
   - User ID loaded from AsyncStorage
   - Backend queried for current notifications

2. **If user is flagged**
   - `user_flagged` notification found
   - `isFlagged` state set to true
   - `flagNotification` state populated
   - Toast animates in at top of page

3. **User sees flag indicators**
   - Toast notification at top with warning
   - Submit button shows "Account Flagged - Cannot Submit"
   - Submit button is disabled (grayed out)
   - Red warning box explains the situation

4. **User tries to click Submit (if somehow enabled)**
   - Alert dialog shows with explanation
   - Submission is blocked early

### Real-Time Updates

The notification is checked each time the user navigates to the report page. If the admin unflag the user during their session, the next time they navigate away and back to the report page, the flag will be cleared.

For immediate updates during their session on the report page:
- Toast auto-dismisses after 8 seconds
- User can manually close it with the X button
- If user navigates away and back, fresh flag status is loaded

## Integration with Existing Systems

### Notification Service
Uses existing `notificationService.getUserNotifications()` which:
- Fetches from backend API
- Returns array of Notification objects
- Already handles `user_flagged` type

### Flag Notification Component
Uses existing `FlagNotificationToast.tsx` which:
- Already styled and animated
- Auto-dismisses or can be manually closed
- Displays restriction details from notification data

### Admin Side
When admin flags a user:
1. Flag created in database
2. Notification generated with type `user_flagged`
3. Backend broadcasts or stores notification
4. User-side polling detects new notification (within 5-30 seconds)
5. Toast appears automatically when user is on report page

## Backend Requirements

The backend must provide:

1. **Flag notification data structure**:
```json
{
  "id": 123,
  "type": "user_flagged",
  "title": "Account Flagged",
  "message": "Your account has been flagged for: [violation]",
  "timestamp": "2025-11-28T10:30:00Z",
  "read": false,
  "data": {
    "flag_id": 456,
    "violation_type": "Multiple violations",
    "reason": "Reason for flag",
    "total_flags": 1,
    "restriction_applied": "warning"
  }
}
```

2. **Endpoints**:
   - `GET /notifications/{userId}` - Returns all notifications including flags
   - Already exists and working

## Testing Checklist

- [ ] Navigate to Report page as normal (unflagged) user
  - Toast should not appear
  - Submit button should be enabled
  - No warning box visible

- [ ] Admin flags a user account
- [ ] User navigates to Report page
  - Toast appears with red background and warning icon
  - Message shows violation type
  - Submit button shows "Account Flagged - Cannot Submit"
  - Submit button is disabled (grayed out)
  - Warning box visible below button

- [ ] User closes toast manually
  - Toast slides out
  - Message disappears but button remains disabled

- [ ] User navigates away and back to Report page
  - Fresh notification check performed
  - Toast appears again if flag still active

- [ ] Admin unflag the user
- [ ] User navigates away and back to Report page
  - No `user_flagged` notification found
  - `isFlagged` set to false
  - Submit button re-enables
  - Toast does not appear
  - Warning box hidden

- [ ] User navigates to other pages
  - Flag status still visible on dashboard (FlagStatusBadge)
  - Flag status still visible on profile (FlagStatusBadge)

- [ ] Alert dialog appears correctly
  - If user somehow clicks submit while flagged
  - Shows "Account Flagged" title
  - Clear explanation of restriction

## Error Handling

- **No user found**: Logged to console, no flag status loaded
- **Network error**: Toast won't appear, submit button remains enabled
  - User can still submit if backend is down
  - Security fails open (allows submission)
  
For production, consider:
- Retry logic for failed notification fetches
- Offline flag status cache
- More aggressive error handling

## Performance Considerations

- Flag check runs only on page focus (efficient)
- Single API call to fetch all notifications
- Filter performed client-side (fast)
- Toast animation uses Native Driver (smooth)
- No additional polling beyond existing notification system

## Color Scheme

| Element | Color | Hex | Purpose |
|---------|-------|-----|---------|
| Toast background | Red | #dc2626 | Urgent warning |
| Toast text | White | #fff | High contrast |
| Warning box background | Light red | #fee2e2 | Soft warning |
| Warning box border | Red | #dc2626 | Emphasis |
| Warning icon | Red | #dc2626 | Consistency |
| Warning text | Dark red | #991b1b | Readable |
| Disabled button | Gray | #999 | Disabled state |

## Future Enhancements

- [ ] Real-time WebSocket for immediate flag updates
- [ ] Flag appeal mechanism (user submits appeal)
- [ ] Countdown timer for temporary flags
- [ ] Flag reason details expandable view
- [ ] Email notification when flagged
- [ ] Notification of flag lift

## Files Modified

- `d:/Codes/alertdavao/alertdavao/UserSide/app/(tabs)/report.tsx` - Main implementation

## Files Used (Existing)

- `UserSide/components/FlagNotificationToast.tsx` - Toast component (no changes)
- `UserSide/services/notificationService.ts` - Notification fetching (no changes)
- `UserSide/contexts/UserContext.tsx` - User data (no changes)

---

**Version**: 1.0.0
**Status**: Complete
**Date**: 2025-11-28
**Type**: Feature - Report Restriction on Flagged Accounts
