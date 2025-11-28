# Testing the Flag Restriction on Report Submission

## Quick Test Steps

### Test 1: Normal User (No Flag)

1. Start the UserSide app with an unflagged user account
2. Navigate to Report page
3. **Verify:**
   - ✅ No red toast notification appears at top
   - ✅ Submit button is enabled (blue/normal color)
   - ✅ Submit button text says "Submit Report"
   - ✅ No red warning box below the button
   - ✅ User can fill out form and submit normally

### Test 2: Admin Flags User

1. Open AdminSide app
2. Navigate to Users section
3. Find a user and click "Flag User"
4. Fill in violation type and reason
5. Submit the flag
6. **Wait 5-30 seconds** for the notification to propagate

### Test 3: Flagged User Receives Notification

1. On the UserSide app (same user that was flagged)
2. If on home page: 
   - Look for notification toast at top (may auto-dismiss)
   - Flag badge appears near welcome message
3. Navigate to Report page
4. **Verify:**
   - ✅ Red toast notification appears at top
   - ✅ Toast shows "Account Flagged" title
   - ✅ Toast shows violation type in message
   - ✅ Toast shows restriction applied (e.g., "WARNING")
   - ✅ Toast auto-dismisses after 8 seconds OR can be manually closed with X
   - ✅ Submit button is disabled (grayed out, #999 color)
   - ✅ Submit button text says "Account Flagged - Cannot Submit"
   - ✅ Red warning box visible below button explaining the situation

### Test 4: Attempt to Submit While Flagged

1. While flag is active and user is on Report page
2. Try to click the Submit button
3. **Verify:**
   - ✅ Button is disabled and doesn't respond
   - ✅ OR if somehow enabled, Alert dialog appears saying "Account Flagged"
   - ✅ Alert shows explanation: "Your account has been flagged. You are unable to submit new reports until the flag is lifted by an administrator."

### Test 5: Close Toast Notification

1. On Report page with active flag
2. Toast is visible
3. Click the X button on the toast
4. **Verify:**
   - ✅ Toast slides out smoothly
   - ✅ Toast is removed from screen
   - ✅ Submit button REMAINS disabled (flag is still active)
   - ✅ Warning box still visible below button

### Test 6: Navigate Away and Back

1. On Report page with active flag
2. Close toast notification
3. Navigate to home page (back button)
4. Navigate back to Report page
5. **Verify:**
   - ✅ Fresh flag status is loaded
   - ✅ Toast appears again (or warning box is visible)
   - ✅ Submit button is disabled again

### Test 7: Admin Unflag User

1. Open AdminSide app
2. Find the flagged user
3. Click on their flag record
4. Click "Remove Flag" or similar action
5. **Wait 5-30 seconds** for the notification to be updated

### Test 8: Verify Flag is Removed

1. On the UserSide app (same user)
2. Close any open toast notifications
3. Navigate away from Report page
4. Navigate back to Report page
5. **Verify:**
   - ✅ No red toast notification appears
   - ✅ Submit button is enabled (blue/normal color)
   - ✅ Submit button text says "Submit Report"
   - ✅ No red warning box visible
   - ✅ Flag badge removed from home page and profile

---

## Expected Behavior by State

### State 1: User Not Flagged

```
Report Page
├─ Toast: [Hidden]
├─ Form: [All fields enabled]
├─ Submit Button: [Enabled - Blue - "Submit Report"]
└─ Warning Box: [Hidden]
```

### State 2: User Flagged

```
Report Page
├─ Toast: [Visible - Red - Auto-dismiss in 8s]
│  ├─ Icon: Warning ⚠️
│  ├─ Title: Account Flagged
│  ├─ Message: [Violation type]
│  ├─ Restriction: [e.g., WARNING/SUSPENDED]
│  └─ Close button: X
├─ Form: [All fields visible but submit disabled]
├─ Submit Button: [Disabled - Gray - "Account Flagged - Cannot Submit"]
└─ Warning Box: [Visible - Red border, explains situation]
   ├─ Icon: Warning ⚠️
   ├─ Title: Account Flagged
   └─ Message: [Clear explanation about the flag]
```

---

## Debug Tips

### If toast doesn't appear:

1. Check console logs:
   ```
   ✓ "User is flagged:" log should appear
   ✓ No error logs in red
   ```

2. Verify flag notification exists:
   - Go to home page → Notifications
   - Look for "Account Flagged" notification
   - Verify it's type "user_flagged"

3. Check the API:
   - In browser console: 
     ```javascript
     fetch('/notifications/{userId}').then(r => r.json())
     ```
   - Look for `type: 'user_flagged'` in response

### If button is still enabled when flagged:

1. Refresh the page with F5
2. Navigate away and back to Report page
3. Check if `isFlagged` state updated (should be true)
4. Verify notification service returned the flag

### If warning box doesn't show:

1. Check that `isFlagged` state is true
2. Verify React re-rendered (should be automatic)
3. Check no CSS is hiding the component

---

## Integration with Backend

### Expected API Response for Flagged User

```json
GET /notifications/{userId}
Status: 200 OK

{
  "success": true,
  "data": [
    {
      "id": 123,
      "type": "user_flagged",
      "title": "Account Flagged",
      "message": "Your account has been flagged for: Repeated Violations",
      "timestamp": "2025-11-28T10:30:00Z",
      "read": false,
      "data": {
        "flag_id": 456,
        "violation_type": "Repeated Violations",
        "reason": "User has violated community guidelines",
        "total_flags": 1,
        "restriction_applied": "warning"
      }
    },
    ... other notifications ...
  ]
}
```

### Expected API Response for Normal User

```json
GET /notifications/{userId}
Status: 200 OK

{
  "success": true,
  "data": [
    {
      "id": 789,
      "type": "report",
      "title": "Report Status",
      "message": "Your report has been reviewed",
      "timestamp": "2025-11-28T09:00:00Z",
      "read": false
    },
    ... no user_flagged type ...
  ]
}
```

---

## Test Report Template

Use this to document your testing:

```
Test Date: ___________
Tester: ___________
UserID Tested: ___________

Test 1 - Normal User: [ ] PASS [ ] FAIL
Test 2 - Admin Flags User: [ ] PASS [ ] FAIL
Test 3 - Toast Appears: [ ] PASS [ ] FAIL
Test 4 - Button Disabled: [ ] PASS [ ] FAIL
Test 5 - Close Toast: [ ] PASS [ ] FAIL
Test 6 - Navigate Away/Back: [ ] PASS [ ] FAIL
Test 7 - Admin Unflag: [ ] PASS [ ] FAIL
Test 8 - Flag Removed: [ ] PASS [ ] FAIL

Issues Found:
- Issue 1: ___________
- Issue 2: ___________

Additional Notes:
___________
```

---

## Common Test Scenarios

### Scenario A: User Logs In After Being Flagged
1. Flag user via AdminSide
2. User logs out
3. User logs back in
4. Navigate to Report page
5. Expected: Toast appears, button disabled

### Scenario B: Multiple Flags
1. Flag same user again (different violation)
2. Check notification (should show total_flags: 2)
3. Navigate to Report page
4. Expected: Toast appears with updated count

### Scenario C: Concurrent Admin & User
1. Admin unflag while user is on Report page
2. User navigates away
3. User navigates back to Report page
4. Expected: Flag is removed, button re-enabled

### Scenario D: Network Delay
1. Start app with slow network
2. Navigate to Report page
3. Wait for notifications to load
4. Expected: Flag status eventually updates correctly

---

## Performance Checklist

- [ ] Toast animation is smooth (300ms)
- [ ] Toast auto-dismiss works (8 seconds)
- [ ] Manual close button responds immediately
- [ ] Page loads without lag even with flag
- [ ] No excessive console logging
- [ ] No memory leaks when toggling flag

---

**Version**: 1.0.0
**Created**: 2025-11-28
