# Global Loading System Implementation Guide

## Overview
A comprehensive loading indicator system has been implemented across both **UserSide** (React Native) and **AdminSide** (Laravel Blade) to provide visual feedback during all system interactions, improving user experience and preventing confusion during async operations.

---

## 🎯 UserSide (React Native/Expo)

### Architecture

#### 1. **LoadingContext** (`UserSide/contexts/LoadingContext.tsx`)
Global state management for loading indicators.

```typescript
import { useLoading } from '../contexts/LoadingContext';

const { showLoading, hideLoading, isLoading, loadingMessage } = useLoading();

// Show loading with custom message
showLoading('Logging in...');

// Hide loading
hideLoading();
```

**Features:**
- Centralized loading state
- Custom loading messages
- Context-based (accessible from any component)

#### 2. **LoadingOverlay Component** (`UserSide/components/LoadingOverlay.tsx`)
Reusable modal overlay with spinner and message.

**Visual Features:**
- Semi-transparent backdrop (rgba(0, 0, 0, 0.5))
- White card with shadow
- Activity indicator (native spinner)
- Custom message display
- Smooth fade animation

**Props:**
```typescript
interface LoadingOverlayProps {
  visible: boolean;
  message?: string; // Default: "Loading..."
}
```

### Integration in App Layout

**File:** `UserSide/app/_layout.tsx`

```typescript
import { LoadingProvider } from '../contexts/LoadingContext';
import LoadingOverlay from '../components/LoadingOverlay';

// Wrap app with LoadingProvider
<LoadingProvider>
  <UserProvider>
    <AppContent />
  </UserProvider>
</LoadingProvider>

// Add LoadingOverlay in AppContent
<LoadingOverlay visible={isLoading} message={loadingMessage} />
```

### Usage Examples

#### Login Page (`app/login.tsx`)
```typescript
import { useLoading } from '../contexts/LoadingContext';

const { showLoading, hideLoading } = useLoading();

const handlePress = async () => {
  showLoading('Logging in...');
  try {
    const response = await fetch(...);
    // Handle response
  } catch (err) {
    alert('Error');
  } finally {
    hideLoading();
  }
};
```

#### Register Page (`app/register.tsx`)
```typescript
showLoading('Creating your account...');
try {
  const response = await fetch(`${backendUrl}/register`, {...});
  // Handle registration
} finally {
  hideLoading();
}
```

#### Edit Profile (`app/edit-profile.tsx`)
```typescript
showLoading('Updating profile...');
try {
  await directDbService.updateProfile(...);
  // Show success
} finally {
  hideLoading();
}
```

### Best Practices (UserSide)

✅ **DO:**
- Always call `hideLoading()` in `finally` block
- Use descriptive messages ("Logging in...", "Saving changes...")
- Show loading for any network request
- Show loading for navigation that takes time

❌ **DON'T:**
- Forget to hide loading (causes stuck state)
- Use generic "Loading..." for everything
- Show loading for instant operations (<100ms)
- Nest multiple loading states

---

## 🎯 AdminSide (Laravel Blade)

### Architecture

#### Global Loading Functions
**File:** `AdminSide/admin/resources/views/layouts/app.blade.php`

```javascript
// Show loading overlay
window.showLoading(message = 'Loading...')

// Hide loading overlay
window.hideLoading()
```

**Features:**
- Globally accessible functions
- Custom loading messages
- Automatic backdrop and spinner
- CSS animations

### HTML Structure

```html
<div id="globalLoading">
    <div class="loading-container">
        <div class="loading-spinner"></div>
        <div class="loading-message" id="loadingMessage">Loading...</div>
    </div>
</div>
```

### CSS Styling

```css
#globalLoading {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
}

.loading-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid #e5e7eb;
    border-top-color: #1D3557;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
```

### Usage Examples

#### Personnel Page - Load Stations
```javascript
async function loadPoliceStations() {
    showLoading('Loading police stations...');
    try {
        const response = await fetch('/api/police-stations');
        const data = await response.json();
        // Process data
    } catch (error) {
        console.error('Error:', error);
    } finally {
        hideLoading();
    }
}
```

#### Personnel Page - Assign Station
```javascript
function assignStationToOfficer() {
    showLoading('Assigning officer to station...');
    
    fetch('/users/' + userId + '/assign-station', {
        method: 'POST',
        // ... headers and body
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Success!');
        }
    })
    .finally(() => {
        hideLoading();
    });
}
```

#### Users Page - Change Role
```javascript
const btn = document.getElementById('roleChangeBtn');
btn.disabled = true;
btn.textContent = 'Changing...';

showLoading('Updating user role...');

fetch('/users/' + userId + '/change-role', {
    method: 'POST',
    // ... request details
})
.then(response => response.json())
.then(data => {
    customAlert('Role changed successfully', 'success');
})
.finally(() => {
    hideLoading();
    btn.disabled = false;
    btn.textContent = 'Change Role';
});
```

#### Users Page - Flag User
```javascript
customConfirm('Are you sure you want to flag this user?', 'Flag User')
    .then(confirmed => {
        if (!confirmed) return;
        
        showLoading('Flagging user...');
        
        fetch('/users/' + userId + '/flag', {
            method: 'POST',
            // ... request
        })
        .then(response => response.json())
        .then(data => {
            customAlert('User flagged successfully', 'success');
        })
        .finally(() => {
            hideLoading();
        });
    });
```

### Best Practices (AdminSide)

✅ **DO:**
- Call `showLoading()` before fetch/AJAX
- Call `hideLoading()` in `.finally()` block
- Use descriptive messages
- Combine with button disabled states
- Use with custom modals (customAlert/customConfirm)

❌ **DON'T:**
- Forget `hideLoading()` (causes stuck overlay)
- Show loading for synchronous operations
- Use native `alert()` with loading (use customAlert instead)
- Show multiple loading overlays

---

## 📋 Implementation Checklist

### UserSide ✅
- [x] Created `LoadingContext.tsx`
- [x] Created `LoadingOverlay.tsx` component
- [x] Integrated in `_layout.tsx`
- [x] Updated `login.tsx`
- [x] Updated `register.tsx`
- [x] Updated `edit-profile.tsx`

### AdminSide ✅
- [x] Added global `showLoading()` and `hideLoading()` functions
- [x] Added loading overlay HTML in `app.blade.php`
- [x] Added CSS animations
- [x] Updated `personnel.blade.php` (loadPoliceStations, assignStation)
- [x] Updated `users.blade.php` (changeRole, flagUser)

---

## 🎨 Visual Design

### UserSide
- **Backdrop:** Semi-transparent black (50% opacity)
- **Container:** White card with rounded corners (12px)
- **Spinner:** Large blue ActivityIndicator (#1D3557)
- **Message:** Gray text (14px, weight 500)
- **Animation:** Fade in/out

### AdminSide
- **Backdrop:** Semi-transparent black (50% opacity)
- **Container:** White card with shadow
- **Spinner:** 48px circular spinner with blue top (#1D3557)
- **Message:** Dark gray text (14px, weight 500)
- **Animation:** Fade in (0.2s), rotate spinner (1s infinite)

---

## 🔧 Troubleshooting

### Issue: Loading stays visible after operation
**Solution:** Ensure `hideLoading()` is in `finally` block, not just `catch`

### Issue: Loading not showing
**UserSide:** Check LoadingProvider wraps entire app
**AdminSide:** Verify `showLoading()` is called before fetch

### Issue: Multiple loading overlays
**Solution:** Use single global loading state, don't nest calls

### Issue: Loading shows for instant operations
**Solution:** Only show for network requests or operations >100ms

---

## 📊 Coverage Summary

### UserSide Operations with Loading:
- ✅ Login
- ✅ Register
- ✅ Edit Profile
- ✅ Profile Picture Upload (existing)
- ✅ Report Submission (existing)
- ✅ Location Search (existing)

### AdminSide Operations with Loading:
- ✅ Load Police Stations
- ✅ Assign Officer to Station
- ✅ Change User Role
- ✅ Flag User
- ✅ Load Verifications (existing)
- ✅ Approve/Reject Verification (existing)

---

## 🚀 Future Enhancements

- [ ] Add progress bars for file uploads
- [ ] Add timeout warnings (>10s operations)
- [ ] Add retry buttons on errors
- [ ] Add loading skeletons for list views
- [ ] Add success animations on completion
- [ ] Add loading states for tab switches
- [ ] Add loading for data export operations

---

**Last Updated:** November 23, 2025  
**Version:** 1.0.0
