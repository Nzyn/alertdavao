# Working Solution: Google OAuth Redirect URI Issue

## The Real Problem

You're getting `redirect_uri=http://localhost:8081` because:
- **npm start** runs a web development server on localhost:8081
- Expo's auth-session library defaults to localhost in development mode
- This conflicts with the Expo redirect URI registered in Google Cloud

This is a **known limitation** of developing Expo apps locally with Google OAuth.

---

## Working Solutions

### Option 1: Use Expo Go App (RECOMMENDED) ✅

Instead of `npm start`, use the actual **Expo Go** mobile app:

```bash
cd UserSide
npx expo start
```

Then:
1. **Android:** Scan QR code with Expo Go app
2. **iOS:** Scan QR code with iPhone camera, open in Expo Go

This will:
- ✅ Use HTTPS Expo redirect URI (not localhost)
- ✅ Work with your registered OAuth credentials
- ✅ Google Sign-In will work properly

**Verify your redirect URIs in Google Cloud Console are set to:**
- `https://auth.expo.io/@imiyataox/userside`
- `https://auth.expo.io/@imiyataox/UserSide`

---

### Option 2: Register localhost:8081 in Google Cloud ✅

If you want to keep using `npm start` (web preview):

1. Go to Google Cloud Console
2. Find your credential (`662961186057-603clci7p37le9pg1et7it4b9e8md6ig`)
3. Add this redirect URI:
   ```
   http://localhost:8081
   ```
4. Save
5. Restart: `npm start -- --clear`

**Note:** This only works for local development. Won't work in production.

---

### Option 3: Disable Google Sign-In for Development (TEMPORARY)

If you need to test other features and Google Sign-In isn't critical right now:

Update `UserSide/app/(tabs)/login.tsx` to disable the button:

```tsx
<Pressable
  onPress={() => promptAsync()}
  disabled={!request || isLoading || true}  // <- Add || true to disable
  style={[localStyles.googleButton, true && { opacity: 0.5 }]}  // <- Add opacity
>
  <Text style={localStyles.googleButtonText}>
    🔐 Sign in with Google (Coming Soon)
  </Text>
</Pressable>
```

Then use **email/password login** for testing.

---

## Recommended: Use Expo Go App

This is the simplest and most reliable way to test Google Sign-In during development:

### Step 1: Install Expo Go
- **Android:** Download from Google Play Store
- **iOS:** Download from App Store

### Step 2: Start the Dev Server
```bash
cd UserSide
npx expo start
```

### Step 3: Connect
1. Android: Scan QR code in Expo Go
2. iOS: Scan QR code with iPhone camera → "Open in Expo Go"

### Step 4: Test Google Sign-In
1. Go to Login screen
2. Click "Continue with Google"
3. Should work! ✅

---

## What's Already Fixed

✅ **googleAuth.ts** - Updated to use proper Expo auth flow  
✅ **login.tsx** - Has correct payload fields (googleId, profilePicture)  
✅ **app.json** - Has new Client ID  
✅ **AdminSide** - Google button disabled with message  

---

## Verify Your Setup

To confirm everything is ready:

### 1. Check app.json
```json
"googleWebClientId": "662961186057-603clci7p37le9pg1et7it4b9e8md6ig"
```

### 2. Check Google Cloud Console
Credential: `662961186057-603clci7p37le9pg1et7it4b9e8md6ig`

Redirect URIs should include:
- ✅ `https://auth.expo.io/@imiyataox/userside`
- ✅ `https://auth.expo.io/@imiyataox/UserSide`
- ✅ `http://localhost:8081` (optional, for web development)

### 3. Check Database
Run:
```sql
SHOW COLUMNS FROM users;
```

Should have:
- ✅ `google_id` column
- ✅ `profile_picture` column

### 4. Check Backend Routes
File: `UserSide/backends/server.js`

Should have:
```js
app.post("/google-login", handleGoogleLogin);
app.post("/google-login-token", handleGoogleLoginWithToken);
```

---

## Testing Checklist

### For Expo Go App:
- [ ] Installed Expo Go on phone/emulator
- [ ] Run `npx expo start`
- [ ] Scanned QR code
- [ ] App opened in Expo Go
- [ ] Went to Login screen
- [ ] Clicked "Continue with Google"
- [ ] Google Sign-In opened ✅
- [ ] Signed in with Google account
- [ ] Redirected back to app and logged in ✅

### For npm start (Web):
- [ ] Registered `http://localhost:8081` in Google Cloud
- [ ] Run `npm start`
- [ ] Opened app in browser (usually port 8081)
- [ ] Clicked "Continue with Google"
- [ ] Google Sign-In opened ✅
- [ ] Signed in and logged in ✅

---

## Why This Issue Happens

```
npm start (development server on localhost:8081)
  ↓
App requests OAuth
  ↓
expo-auth-session automatically uses: http://localhost:8081
  ↓
Google checks: Is http://localhost:8081 registered for this credential?
  ↓
NOT FOUND (only https://auth.expo.io/@imiyataox/userside is registered)
  ↓
❌ Error: redirect_uri_mismatch
```

**Solution:** Use Expo Go app instead of npm start
```
Expo Go app (HTTPS)
  ↓
App requests OAuth
  ↓
expo-auth-session uses: https://auth.expo.io/@imiyataox/userside
  ↓
Google checks: Is this registered for this credential?
  ↓
FOUND! ✅
  ↓
✅ Google Sign-In works!
```

---

## Production Deployment

When you build for production:

```bash
eas build --platform android  # or ios
```

Google Sign-In will work with the Expo redirect URI automatically, as long as you have it registered in Google Cloud Console.

---

## Summary

**Current Status:** Everything is configured correctly ✅

**To make Google Sign-In work:**
- **Option A (Recommended):** Use Expo Go app instead of `npm start`
- **Option B:** Register `http://localhost:8081` in Google Cloud Console
- **Option C:** Disable Google Sign-In temporarily and use email/password

**Next Step:** Try Option A - it's the simplest and most reliable!

---

**Last Updated:** 2025-01-10  
**Status:** All code fixes applied, ready to test
