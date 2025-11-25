# 🔑 Get Your Google Client ID (Quick Guide)

## You're seeing "Missing required parameter: client_id" because:

The app.json still has the placeholder: `YOUR_WEB_CLIENT_ID_HERE.apps.googleusercontent.com`

You need to replace it with your **actual Google Web Client ID**.

---

## ⚡ Quick Steps (5 minutes):

### Step 1: Go to Google Cloud Console
Open: **https://console.cloud.google.com/**

### Step 2: Create/Select Project
- Click "Select a project" (top bar)
- Click "NEW PROJECT"
- Project name: **AlertDavao**
- Click "CREATE"
- Wait for project to be created (~10 seconds)

### Step 3: Enable Google Sign-In API
- In search bar, type: **"credentials"**
- Click **"Credentials"** (under APIs & Services)
- You may see "Configure Consent Screen" - click it:
  - User Type: **External** → CREATE
  - App name: **AlertDavao**
  - User support email: Your email
  - Developer contact: Your email
  - Click **SAVE AND CONTINUE**
  - Scopes: Click **SAVE AND CONTINUE** (skip this)
  - Test users: Click **SAVE AND CONTINUE** (skip this)
  - Click **BACK TO DASHBOARD**

### Step 4: Create OAuth Client ID
- Click **"Credentials"** tab (left sidebar)
- Click **"+ CREATE CREDENTIALS"** → **"OAuth client ID"**
- Application type: **Web application**
- Name: **AlertDavao Web Client**

### Step 5: Add Redirect URI
**You have 2 options:**

#### Option A: Without Expo Account (Easier for Development)
Use this redirect URI:
```
https://auth.expo.io/@anonymous/UserSide-[random]
```

Or even simpler, just use:
```
http://localhost:8081
https://localhost:8081
```

#### Option B: With Expo Account
If you want to use Expo's auth service, first login:
```bash
npx expo login
```

Then get your username:
```bash
npx expo whoami
```

And use this redirect URI:
```
https://auth.expo.io/@YOUR_USERNAME/UserSide
```

**For now, use Option A and add these redirect URIs:**
```
http://localhost:8081
https://auth.expo.io/@anonymous/UserSide
```

Paste both in **"Authorized redirect URIs"** field (one per line)

### Step 6: Create & Copy Client ID
- Click **CREATE**
- A popup appears with your credentials
- **Copy the Client ID** (looks like: `123456789-abc123xyz.apps.googleusercontent.com`)

### Step 7: Update app.json
1. Open: `UserSide/app.json`
2. Find this line:
   ```json
   "googleWebClientId": "YOUR_WEB_CLIENT_ID_HERE.apps.googleusercontent.com"
   ```
3. Replace with your actual Client ID:
   ```json
   "googleWebClientId": "123456789-abc123xyz.apps.googleusercontent.com"
   ```
4. **Save the file**

### Step 8: Restart the App
```bash
# Stop the app (Ctrl+C in terminal)
# Start again:
npm start
```

### Step 9: Test Google Sign-In
1. Open app in Expo Go
2. Go to Login screen
3. Click **"Continue with Google"**
4. Should now open Google Sign-In ✅

---

## ✅ What Each Error Means:

### "Missing required parameter: client_id"
→ The placeholder is still in app.json. Replace it with your actual Client ID.

### "Redirect URI mismatch"
→ Your redirect URI doesn't match. Must be: `https://auth.expo.io/@YOUR_USERNAME/UserSide`

### "Invalid Client ID"
→ Check you copied the correct Client ID from Google Console.

---

## 🔍 Verify It's Working:

When configured correctly:
- ✅ Google Sign-In browser window opens
- ✅ You can select your Google account
- ✅ App redirects back after sign-in
- ✅ You're logged into AlertDavao

---

## 📌 Quick Reference:

**Google Cloud Console:** https://console.cloud.google.com/  
**Check Expo username:** `npx expo whoami`  
**Redirect URI format:** `https://auth.expo.io/@username/UserSide`  
**File to edit:** `UserSide/app.json`  

---

**Need help?** See `GOOGLE_SIGNIN_5_STEPS.md` for more details.
