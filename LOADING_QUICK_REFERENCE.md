# Loading System - Quick Reference

## 🎯 UserSide (React Native)

### Import
```typescript
import { useLoading } from '../contexts/LoadingContext';
```

### Usage
```typescript
const { showLoading, hideLoading } = useLoading();

const handleAction = async () => {
  showLoading('Custom message...'); // Show with message
  try {
    await someAsyncOperation();
  } finally {
    hideLoading(); // Always hide in finally
  }
};
```

### Common Messages
- `'Logging in...'`
- `'Creating your account...'`
- `'Updating profile...'`
- `'Submitting report...'`
- `'Loading data...'`
- `'Saving changes...'`

---

## 🎯 AdminSide (Laravel Blade)

### Usage
```javascript
// Show loading
showLoading('Custom message...');

// Hide loading
hideLoading();
```

### Example with Fetch
```javascript
showLoading('Loading data...');

fetch('/api/endpoint', {
    method: 'POST',
    headers: { /* ... */ },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => {
    // Handle success
})
.catch(error => {
    // Handle error
})
.finally(() => {
    hideLoading(); // Always in finally
});
```

### Example with Async/Await
```javascript
async function loadData() {
    showLoading('Loading...');
    try {
        const response = await fetch('/api/data');
        const data = await response.json();
        // Process data
    } catch (error) {
        console.error(error);
    } finally {
        hideLoading();
    }
}
```

---

## ⚡ Best Practices

### ✅ DO
- Always use `try-catch-finally`
- Put `hideLoading()` in `finally` block
- Use descriptive messages
- Show loading for network requests
- Combine with button disabled states

### ❌ DON'T
- Forget to call `hideLoading()`
- Use generic "Loading..." everywhere
- Show loading for instant operations
- Nest multiple loading states
- Put `hideLoading()` only in `catch`

---

## 🐛 Common Mistakes

### ❌ Wrong
```typescript
showLoading('Saving...');
try {
  await save();
  hideLoading(); // Missing in catch!
} catch (error) {
  console.error(error);
}
```

### ✅ Correct
```typescript
showLoading('Saving...');
try {
  await save();
} catch (error) {
  console.error(error);
} finally {
  hideLoading(); // Always here!
}
```

---

## 📱 Component Props

### UserSide - LoadingOverlay
```typescript
<LoadingOverlay 
  visible={isLoading}      // boolean
  message="Loading..."     // string (optional)
/>
```

### UserSide - useLoading Hook
```typescript
const {
  isLoading,      // boolean - current loading state
  loadingMessage, // string - current message
  showLoading,    // (message?: string) => void
  hideLoading     // () => void
} = useLoading();
```

---

## 🎨 Styling

### UserSide Colors
- Backdrop: `rgba(0, 0, 0, 0.5)`
- Container: `white`
- Spinner: `#1D3557` (AlertDavao blue)
- Text: `#1f2937`

### AdminSide Colors
- Backdrop: `rgba(0, 0, 0, 0.5)`
- Spinner border: `#e5e7eb`
- Spinner top: `#1D3557`
- Text: `#1f2937`

---

## 📋 Quick Checklist

Before commit:
- [ ] Added `showLoading()` before async operation
- [ ] Added `hideLoading()` in `finally` block
- [ ] Used descriptive loading message
- [ ] Tested loading shows and hides correctly
- [ ] No console errors
- [ ] Loading doesn't stick on error

---

**Need help?** See `LOADING_SYSTEM_GUIDE.md` for full documentation
