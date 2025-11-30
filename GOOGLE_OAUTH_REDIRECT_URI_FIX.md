# Fix: OAuth 2.0 Redirect URI Mismatch Error

## Error You're Seeing:
```
Error 400: redirect_uri_mismatch
Request details: redirect_uri=http://localhost:8081 flowName=GeneralOAuthFlow
```

## Root Cause:
Your Google OAuth credentials (`662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com`) were configured with:
- ❌ **Configured URI:** `http://localhost:8081` (for local development)
- ✅ **Expected URI:** `https://auth.expo.io/@imiyataox/userside` (for Expo)

These don't match, so Google rejects the request.

---

## Solution: Update Google Cloud Console

### Step 1: Open Google Cloud Console
Go to: **https://console.cloud.google.com/**

### Step 2: Find Your OAuth Credentials
1. Click on **APIs & Services** (left sidebar)
2. Click on **Credentials**
3. Under "OAuth 2.0 Client IDs", find your credential:
   - **Name:** Look for the one with ID `662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com`
   - **Type:** Should be "Web application"
4. Click on it to edit

### Step 3: Update Authorized Redirect URIs
1. Find the **"Authorized redirect URIs"** section
2. **REMOVE** any of these:
   - ❌ `http://localhost:8081`
   - ❌ `https://localhost:8081`
   - ❌ Any other localhost URIs

3. **ADD** only this:
   ```
   https://auth.expo.io/@imiyataox/userside
   ```

   ⚠️ **IMPORTANT:** 
   - Must be **https** (not http)
   - Must be lowercase: `@imiyataox/userside` (not @imiyataox/UserSide)
   - No trailing slash

4. Click **"SAVE"**

### Step 4: Verify Your Credentials
After saving, verify:
- Web Client ID: `662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com`
- Authorized redirect URI: `https://auth.expo.io/@imiyataox/userside`

---

## Step-by-Step Screenshots (What You Should See)

### In Google Cloud Console:
```
APIs & Services
  ├─ Credentials
  │   └─ OAuth 2.0 Client IDs
  │       └─ [Your App Name]
  │           ├─ Application type: Web application
  │           ├─ Client ID: 662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com
  │           └─ Authorized redirect URIs:
  │               └─ https://auth.expo.io/@imiyataox/userside  ✅ ONLY THIS
```

---

## Step 5: Restart Your App
```bash
cd UserSide
npm start -- --clear
```

Then test Google Sign-In again.

---

## If Still Not Working: Delete & Recreate

If the above doesn't work, create **new** OAuth credentials:

### Option A: Keep Current (If Email/Password Works)
If email/password login is working, you can add a **different** OAuth credential just for Expo:

1. Go to Google Cloud Console → Credentials
2. Click **"+ CREATE CREDENTIALS"** → **"OAuth client ID"**
3. Application type: **Web application**
4. Name: `AlertDavao Expo`
5. Authorized redirect URIs: `https://auth.expo.io/@imiyataox/userside`
6. Click **CREATE**
7. **Copy the new Client ID**
8. Update `app.json`:
   ```json
   "googleWebClientId": "[NEW_CLIENT_ID]"
   ```

### Option B: Delete & Start Fresh
1. Delete the old credential (662961186057...)
2. Create a new one with correct settings
3. Update app.json with the new Client ID
4. Restart the app

---

## Checklist Before Testing

- [ ] Logged into Google Cloud Console
- [ ] Found OAuth 2.0 Client ID: `662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com`
- [ ] Removed localhost URIs
- [ ] Added `https://auth.expo.io/@imiyataox/userside`
- [ ] Clicked **SAVE**
- [ ] Waited 30 seconds for changes to propagate
- [ ] Restarted app with `npm start -- --clear`
- [ ] Tested Google Sign-In button

---

## Reference: Your Current Setup

| Setting | Value |
|---------|-------|
| Expo Owner | `imiyataox` |
| App Slug | `userside` |
| Client ID | `662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com` |
| **Correct Redirect URI** | `https://auth.expo.io/@imiyataox/userside` |

---

## Why This Error Happens

Google OAuth works like this:
1. Your app opens Google Sign-In
2. App sends: `client_id=XXX&redirect_uri=https://auth.expo.io/@imiyataox/userside`
3. Google checks: "Is this redirect_uri registered for this client_id?"
4. If **NOT found** in registered URIs → Error 400: redirect_uri_mismatch
5. If **found** → Proceeds with sign-in

Your client_id has `http://localhost:8081` registered, but Expo sends `https://auth.expo.io/@imiyataox/userside` → Mismatch!

---

## Quick Test After Fixing

1. Open app: `npm start -- --clear`
2. Go to Login screen
3. Click **"Continue with Google"**
4. Browser should open Google Sign-In screen
5. Select your account
6. Should redirect back to app and log you in

If you see the same error, it means Google Console changes haven't taken effect. Wait 1-2 minutes and try again.

---

**Status:** Follow the 5 steps above and it should work!
