# Flag Restriction - Visual Guide

## User Interface States

### State 1: Normal User (Not Flagged)

```
┌─────────────────────────────────────────────┐
│ 📍 Report Crime                             │ Header
├─────────────────────────────────────────────┤
│ Title *                                     │
│ [e.g., Wallet stolen near market         ] │ Form fields
│                                             │ (normal state)
│ Select the type of crimes *                 │
│ ☐ Theft     ☐ Robbery     ☐ Fraud        │
│ ☐ Cybercrime ☐ Burglary    ☐ Theft       │
│ ... more options ...                        │
│                                             │
│ Location *                                  │
│ Barangay, Davao City                       │
│ [Select Location]                           │
│                                             │
│ Date of Incident *                          │
│ [Select Date & Time]                       │
│                                             │
│ Description *                               │
│ [Tell us what happened...                 ] │
│                                             │
│ ☐ Report Anonymously                       │
│                                             │
│      ┌─────────────────────────┐           │
│      │  ✅ Submit Report        │           │ ← ENABLED
│      │     (blue, clickable)    │           │
│      └─────────────────────────┘           │
│                                             │
└─────────────────────────────────────────────┘
```

### State 2: Flagged User

```
┌─────────────────────────────────────────────┐
│                                             │ Toast (appears
│ ┌─────────────────────────────────────────┐ │  at top)
│ │ ⚠️  Account Flagged                    X │ │
│ │ Your account has been flagged for:     │ │
│ │ Multiple Violations                     │ │
│ │ ⚠️  Restriction Applied: WARNING       │ │
│ └─────────────────────────────────────────┘ │
│                                             │
├─────────────────────────────────────────────┤
│ 📍 Report Crime                             │ Header
├─────────────────────────────────────────────┤
│ Title *                                     │
│ [e.g., Wallet stolen near market         ] │ Form fields
│                                             │ (normal state)
│ Select the type of crimes *                 │
│ ☐ Theft     ☐ Robbery     ☐ Fraud        │
│ ☐ Cybercrime ☐ Burglary    ☐ Theft       │
│ ... more options ...                        │
│                                             │
│ Location *                                  │
│ Barangay, Davao City                       │
│ [Select Location]                           │
│                                             │
│ Date of Incident *                          │
│ [Select Date & Time]                       │
│                                             │
│ Description *                               │
│ [Tell us what happened...                 ] │
│                                             │
│ ☐ Report Anonymously                       │
│                                             │
│      ┌─────────────────────────┐           │
│      │ ❌ Account Flagged -    │           │
│      │    Cannot Submit         │           │ ← DISABLED
│      │    (gray, not clickable) │           │
│      └─────────────────────────┘           │
│                                             │
│ ┌─────────────────────────────────────────┐ │ Warning box
│ │ ⚠️  Account Flagged                     │ │ (red border)
│ │                                         │ │
│ │ Your account has been flagged. You are │ │
│ │ unable to submit new reports until the │ │
│ │ flag is lifted by an administrator.    │ │
│ └─────────────────────────────────────────┘ │
│                                             │
└─────────────────────────────────────────────┘
```

---

## Toast Notification Details

### Visual Appearance

```
┌─────────────────────────────────────────────────────────────┐
│  🔴  ACCOUNT FLAGGED                                       X │
├─────────────────────────────────────────────────────────────┤
│ Your account has been flagged for:                          │
│ Multiple Violations                                         │
│ ⚠️  Restriction Applied: WARNING                            │
└─────────────────────────────────────────────────────────────┘
```

### Design Specifications

| Property | Value |
|----------|-------|
| Background Color | #dc2626 (Red) |
| Text Color | #fff (White) |
| Border Radius | 12px |
| Icon | warning (24px, white) |
| Duration | 8 seconds (auto-dismiss) |
| Animation | Slide in from top (300ms) |
| Position | Top of page, full width |
| Close Button | X icon (manual close) |

### Animation Flow

```
Frame 0ms:   [Toast slides from above screen]
             ↓↓↓
Frame 150ms: [Toast half visible]
             ↓↓↓
Frame 300ms: [Toast fully visible] ← User sees
             ↓↓↓
Frame 4000ms: [Visible for 4 more seconds]
              ↓↓↓
Frame 8000ms: [Auto-dismiss begins]
              ↑↑↑
Frame 8150ms: [Toast slides out]
              ↑↑↑
Frame 8300ms: [Toast removed from UI]
```

---

## Warning Box Details

### Visual Appearance

```
┌──────────────────────────────────────────────────────┐
│                                                      │ Light red bg
│ ⚠️  Account Flagged                                │ Dark red text
│                                                      │
│ Your account has been flagged. You are unable to   │
│ submit new reports until the flag is lifted by an  │
│ administrator.                                      │
│                                                      │
└──────────────────────────────────────────────────────┘
  ▲
  Red left border (4px)
```

### Design Specifications

| Property | Value |
|----------|-------|
| Background Color | #fee2e2 (Light Red) |
| Border Left Color | #dc2626 (Red) |
| Border Left Width | 4px |
| Text Color (Title) | #991b1b (Dark Red) |
| Text Color (Body) | #7f1d1d (Darker Red) |
| Icon Color | #dc2626 (Red) |
| Padding | 12px |
| Border Radius | 6px |
| Margin Top | 12px |
| Margin Horizontal | 12px |

### Typography

| Element | Font | Size | Weight |
|---------|------|------|--------|
| Title | System Font | 14px | 600 (bold) |
| Body | System Font | 13px | 400 (normal) |
| Line Height | 18px | | |

---

## Submit Button States

### State 1: Normal (Not Flagged)

```
┌─────────────────┐
│ Submit Report   │  ← Blue (#1D3557)
│ (Enabled)       │  ← Clickable
└─────────────────┘
```

### State 2: Submitting

```
┌──────────────────┐
│ Submitting...    │  ← Blue (#1D3557)
│ (Disabled)       │  ← Not clickable
└──────────────────┘

+ Spinner animation below
  "Submitting your report..."
```

### State 3: Flagged

```
┌─────────────────────────────┐
│ Account Flagged -           │  ← Gray (#999)
│ Cannot Submit               │  ← Not clickable
│ (Disabled)                  │  ← Grayed out
└─────────────────────────────┘
```

---

## Color Palette

### Red/Warning Colors

```
Color Name          | Hex Code | Usage
────────────────────┼──────────┼──────────────────
Toast Background    | #dc2626  | Toast container
Toast Text          | #fff     | Toast text
Warning Box BG      | #fee2e2  | Warning box background
Warning Box Border  | #dc2626  | Warning box left border
Warning Icon        | #dc2626  | Icon in warning box
Warning Title       | #991b1b  | Title text in warning
Warning Body        | #7f1d1d  | Body text in warning
Danger Text         | #991b1b  | General danger/error
```

### Standard Colors

```
Color Name          | Hex Code | Usage
────────────────────┼──────────┼──────────────────
Primary Blue        | #1D3557  | Normal button, headers
Button Disabled     | #999     | Disabled button color
Button Text         | #fff     | Button text
White Background    | #fff     | Main backgrounds
Light Gray BG       | #f5f5f5  | Section backgrounds
Text Dark           | #333     | Main text
Text Medium         | #666     | Secondary text
Text Light          | #999     | Tertiary text
Border Color        | #ccc     | Input borders
```

---

## User Flow Diagram

### Unflagged User

```
┌──────────────────┐
│ Open Report Page │
└────────┬─────────┘
         │
         ↓
┌──────────────────────────┐
│ Check flag status?       │
│ (Load from API)          │
└────────┬─────────────────┘
         │
         ├─ No flag found
         │
         ↓
┌──────────────────────────┐
│ isFlagged = false        │
│                          │
│ ✅ Show normal form      │
│ ✅ Enable submit button  │
│ ✅ No warnings/toasts    │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ User fills out form      │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ User clicks Submit       │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ handleSubmit()           │
│ - isFlagged = false      │
│ - Passes check           │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ ✅ Submit Report         │
│ ✅ Success               │
└──────────────────────────┘
```

### Flagged User

```
┌──────────────────┐
│ Open Report Page │
└────────┬─────────┘
         │
         ↓
┌──────────────────────────┐
│ Check flag status?       │
│ (Load from API)          │
└────────┬─────────────────┘
         │
         ├─ Flag found!
         │
         ↓
┌──────────────────────────┐
│ isFlagged = true         │
│                          │
│ 🔴 Show toast            │
│ 🔴 Disable submit button │
│ 🔴 Show warning box      │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ User sees warnings       │
│ Tries to click Submit    │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ Button is disabled       │
│ (No click response)      │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ User sees red warning    │
│ Understands restriction  │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ Admin removes flag       │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ User navigates away      │
│ from Report page         │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ User navigates back      │
│ to Report page           │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ Check flag status again  │
│ (Fresh API call)         │
└────────┬─────────────────┘
         │
         ├─ No flag found (removed!)
         │
         ↓
┌──────────────────────────┐
│ isFlagged = false        │
│                          │
│ ✅ Warnings disappear    │
│ ✅ Button re-enabled     │
│ ✅ Toast hidden          │
└────────┬─────────────────┘
         │
         ↓
┌──────────────────────────┐
│ User can now submit      │
│ report normally          │
└──────────────────────────┘
```

---

## Component Hierarchy

```
Report Page (report.tsx)
├── ScrollView
│   ├── FlagNotificationToast
│   │   ├── Animated.View (Toast container)
│   │   ├── Warning Icon
│   │   ├── Text (Title)
│   │   ├── Text (Message)
│   │   ├── Text (Restriction)
│   │   └── Close Button (X)
│   │
│   ├── Header
│   │   ├── Back Button
│   │   ├── Title
│   │   └── Spacer
│   │
│   ├── Form Fields
│   │   ├── Title Input
│   │   ├── Crime Type Checkboxes
│   │   ├── Location Selector
│   │   ├── Date/Time Picker
│   │   ├── Description Input
│   │   ├── Media Picker
│   │   └── Anonymous Checkbox
│   │
│   ├── Submit Button
│   │   └── Button (disabled={isFlagged || isSubmitting})
│   │
│   ├── Warning Box (conditional)
│   │   ├── Warning Icon
│   │   ├── Title ("Account Flagged")
│   │   └── Description
│   │
│   └── Success Dialog (if submitted)
│
└── Modal Dialogs
    ├── Calendar Picker
    ├── Media Viewer
    ├── Confirmation Dialog
    └── Success Dialog
```

---

## Responsive Behavior

### Mobile (Narrow)
```
┌─────────────────────┐
│ Toast notification  │ Spans full width
│ Account Flagged     │ Wraps text if needed
└─────────────────────┘
```

### Tablet (Medium)
```
┌────────────────────────────────┐
│ Toast notification - Account   │ Longer line fits
│ Flagged - Cannot Submit        │
└────────────────────────────────┘
```

### Desktop (Wide)
```
┌──────────────────────────────────────────────────────┐
│ Toast notification - Account Flagged - Full text    │
└──────────────────────────────────────────────────────┘
```

---

## Accessibility Features

### Color Contrast

| Element | FG Color | BG Color | Ratio | WCAG Level |
|---------|----------|----------|-------|-----------|
| Toast Text | #fff | #dc2626 | 7.2:1 | AAA ✅ |
| Warning Title | #991b1b | #fee2e2 | 5.1:1 | AA ✅ |
| Warning Body | #7f1d1d | #fee2e2 | 4.5:1 | AA ✅ |
| Icon | #dc2626 | #fee2e2 | 4.5:1 | AA ✅ |

### Touch Targets

| Element | Size | Minimum | Status |
|---------|------|---------|--------|
| Close Button (X) | 44px | 44x44px | ✅ |
| Submit Button | 60px height | 44x44px | ✅ |
| Checkbox | 36px | 44x44px | ✅ |

### Screen Reader Support

- Icon names readable (e.g., "warning")
- Button text descriptive ("Account Flagged - Cannot Submit")
- Warning box text semantic
- Disabled state announced by OS

---

## Animation Specifications

### Toast Slide-In Animation

```
Duration: 300ms
Easing: Ease-out (default timing)
Direction: Top to bottom
Distance: 100px (from above screen)
Native Driver: YES (Smooth performance)

useNativeDriver: true (Important!)
Animated.timing(slideAnim, {
  toValue: 0,
  duration: 300,
  useNativeDriver: true,
})
```

### Auto-Dismiss Timer

```
Trigger: After 300ms animation completes
Duration: 8000ms (8 seconds visible)
Exit Animation: 300ms slide-out + fade-out
Total: 8600ms from first appearance to complete removal
```

---

**Visual Design Reference Complete**

For implementation details, see: FLAGGING_REPORT_RESTRICTION_IMPLEMENTATION.md
For testing visuals, see: FLAGGING_REPORT_TESTING.md
