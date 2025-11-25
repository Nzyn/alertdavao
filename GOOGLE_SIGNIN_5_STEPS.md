# 🚀 Google Sign-In Quick Start (5 Steps)

## ✅ Step 1: Install Packages (2 minutes)

```bash
# Option A: Run automated script
SETUP_GOOGLE_SIGNIN.bat

# Option B: Manual installation
cd UserSide
npx expo install expo-auth-session expo-crypto expo-web-browser

cd backends
npm install google-auth-library
```

## ✅ Step 2: Update Database (1 minute)

```bash
mysql -u root alertdavao < UserSide/backends/migrations/add_google_signin_to_users.sql
```

## ✅ Step 3: Get Google OAuth Client ID (3 minutes)

1. Go to https://console.cloud.google.com/
2. **Create Project** → Name: "AlertDavao" → Create
3. **APIs & Services** → **Credentials** → **Create Credentials** → **OAuth client ID**
4. **Application type:** Web application
5. **Name:** AlertDavao Web Client
6. **Authorized redirect URIs:** 
   ```
   https://auth.expo.io/@YOUR_EXPO_USERNAME/UserSide
   ```
   Get your username: `npx expo whoami`
   
7. Click **Create**
8. **Copy the Client ID** (123456789-abc.apps.googleusercontent.com)

## ✅ Step 4: Configure App (1 minute)

### A. Update `UserSide/app.json`:
Find the `extra` section and replace the placeholder:

```json
{
  "expo": {
    "extra": {
      "googleWebClientId": "123456789-abc.apps.googleusercontent.com"
    }
  }
}
```

### B. Create `UserSide/backends/.env`:
```env
GOOGLE_WEB_CLIENT_ID=123456789-abc.apps.googleusercontent.com
```

## ✅ Step 5: Test (2 minutes)

```bash
# Terminal 1 - Start backend
cd UserSide/backends
npm start

# Terminal 2 - Start app
cd UserSide
npm start
```

1. Open app in Expo Go
2. Go to Login screen
3. Click **"Continue with Google"**
4. Sign in with your Google account
5. Should redirect back and login ✅

---

## 🎯 That's It!

**Total time:** ~10 minutes

**What works:**
- ✅ Google Sign-In button functional
- ✅ Auto-registration for new users
- ✅ Auto-login for existing users
- ✅ Profile picture from Google
- ✅ No password needed

**Common Issues:**

❌ **"Redirect URI mismatch"**
→ Check your redirect URI matches: `https://auth.expo.io/@username/UserSide`

❌ **"Invalid Client ID"**
→ Make sure you updated `app.json` with your actual client ID

❌ **Button disabled**
→ Verify `googleWebClientId` is set in `app.json`

---

For detailed documentation, see: `GOOGLE_SIGNIN_COMPLETE.md`
