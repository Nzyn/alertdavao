# Complete Fix: Google OAuth Redirect URI Errors

## Problem Summary
You're getting **two different redirect URIs** being sent:
- `http://localhost:8081` (development/web)
- `https://auth.expo.io/@imiyataox/userside` (Expo Go)

Google OAuth credentials are strict - they only accept **exact matches**.

---

## Solution: Create Separate OAuth Credentials

You need **TWO** separate OAuth credentials in Google Cloud Console:

1. **One for local development** (`http://localhost:8081`)
2. **One for Expo Go** (`https://auth.expo.io/@imiyataox/userside`)

Then use the **Expo one** in your app.json.

---

## Step-by-Step Fix

### Step 1: Create New OAuth Credential for Expo

1. Go to: **https://console.cloud.google.com/**
2. **APIs & Services** → **Credentials**
3. Click **"+ CREATE CREDENTIALS"** → **"OAuth client ID"**
4. **Application type:** `Web application`
5. **Name:** `AlertDavao Expo`
6. **Authorized redirect URIs:** Add these **two lines**:
   ```
   https://auth.expo.io/@imiyataox/userside
   https://auth.expo.io/@imiyataox/UserSide
   ```
   (Add both to handle case variations)

7. Click **CREATE**
8. **Copy the new Client ID** - it will look like: `XXXXXX-XXXXXXX.apps.googleusercontent.com`

### Step 2: Update app.json

Open `UserSide/app.json` and replace:

```json
"extra": {
  "googleWebClientId": "662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com",
```

With:

```json
"extra": {
  "googleWebClientId": "[PASTE_YOUR_NEW_CLIENT_ID_HERE]",
```

**Replace `[PASTE_YOUR_NEW_CLIENT_ID_HERE]` with the Client ID you just copied!**

### Step 3: Update googleAuth.ts

Update `UserSide/config/googleAuth.ts`:

```typescript
import * as Google from 'expo-auth-session/providers/google';
import * as WebBrowser from 'expo-web-browser';
import { makeRedirectUri } from 'expo-auth-session';
import Constants from 'expo-constants';

WebBrowser.maybeCompleteAuthSession();

// Get Google Client ID from app.json configuration
export const GOOGLE_WEB_CLIENT_ID = Constants.expoConfig?.extra?.googleWebClientId || '';

export const useGoogleAuth = () => {
  const redirectUrl = makeRedirectUri({
    scheme: 'userside',
  });

  console.log('🔐 Google Auth Config:', {
    clientId: GOOGLE_WEB_CLIENT_ID,
    redirectUrl: redirectUrl,
  });

  const [request, response, promptAsync] = Google.useAuthRequest({
    clientId: GOOGLE_WEB_CLIENT_ID,
    scopes: ['profile', 'email'],
    redirectUrl: redirectUrl,
  });

  return {
    request,
    response,
    promptAsync,
  };
};

export const getGoogleUserInfo = async (accessToken: string) => {
  try {
    const response = await fetch(
      'https://www.googleapis.com/userinfo/v2/me',
      {
        headers: { Authorization: `Bearer ${accessToken}` },
      }
    );
    return await response.json();
  } catch (error) {
    console.error('Error getting Google user info:', error);
    return null;
  }
};
```

### Step 4: Verify app.json Settings

Make sure your app.json has:

```json
{
  "expo": {
    "name": "UserSide",
    "slug": "UserSide",
    "scheme": "userside",
    "owner": "imiyataox",
    "extra": {
      "googleWebClientId": "[YOUR_NEW_EXPO_CLIENT_ID]",
      "googleAndroidClientId": "[KEEP_YOUR_EXISTING_ONE]",
      "eas": {
        "projectId": "c8a44159-9039-49f2-abec-c046849b3883"
      }
    }
  }
}
```

### Step 5: Clear Cache & Restart

```bash
cd UserSide
npm start -- --clear
```

### Step 6: Test

1. Open app in Expo Go
2. Go to Login screen
3. Click **"Continue with Google"**
4. Check console logs - should see:
   ```
   🔐 Google Auth Config: {
     clientId: "[YOUR_NEW_CLIENT_ID]",
     redirectUrl: "https://auth.expo.io/@imiyataox/userside"
   }
   ```
5. Browser should open Google Sign-In
6. Select account → should redirect back to app

---

## Checklist

- [ ] Logged into Google Cloud Console
- [ ] Created **new** OAuth credential for Expo
- [ ] **Copied** the new Client ID
- [ ] Updated `app.json` with new Client ID
- [ ] Updated `googleAuth.ts` with new code (added redirectUrl)
- [ ] Ran `npm start -- --clear`
- [ ] Checked console logs for correct redirect URL
- [ ] Tested Google Sign-In button
- [ ] Successfully signed in ✅

---

## What Each Part Does

| Component | Purpose |
|-----------|---------|
| **Google OAuth Credential** | Authenticates your app with Google |
| **Client ID in app.json** | Tells Expo which credential to use |
| **Redirect URI** | Where Google sends user after sign-in |
| **makeRedirectUri()** | Generates correct redirect URI for Expo |
| **WebBrowser.maybeCompleteAuthSession()** | Handles OAuth completion in browser |

---

## Troubleshooting

### Still getting `redirect_uri_mismatch`?
- Check that you **copied the NEW Client ID** (not the old one)
- Verify the **new** credential has correct redirect URIs
- Wait 30-60 seconds for Google to update
- Clear app cache: `npm start -- --clear`

### Console shows `clientId: ""`?
- app.json doesn't have `googleWebClientId` set
- Check you're reading from the right property in app.json
- Verify `Constants.expoConfig` is working

### Browser doesn't open?
- Check that `expo-web-browser` is installed
- Check that `WebBrowser.maybeCompleteAuthSession()` is called
- Look for errors in console

---

## Reference: OAuth Flow

```
User clicks "Continue with Google"
  ↓
App calls: promptAsync()
  ↓
makeRedirectUri() generates: https://auth.expo.io/@imiyataox/userside
  ↓
App sends to Google:
  - client_id: [YOUR_NEW_CLIENT_ID]
  - redirect_uri: https://auth.expo.io/@imiyataox/userside
  ↓
Google checks: "Is this redirect_uri registered for this client_id?"
  ↓
Google finds it in credentials ✅
  ↓
Google opens Sign-In browser window
  ↓
User signs in, Google redirects to: https://auth.expo.io/@imiyataox/userside
  ↓
Expo receives token, redirects back to app
  ↓
App has accessToken → fetches user info → sends to backend
  ↓
User logged in ✅
```

---

## Keep Your Old Credential

Don't delete your old credential `662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com`. It might be used elsewhere.

Just create a **new one specifically for Expo** and use that in app.json.

---

**Status:** Follow all 6 steps and Google Sign-In should work!

If you've done everything and still have issues, reply with:
1. The new Client ID you created
2. Console output when clicking Google Sign-In button
3. The exact error message
