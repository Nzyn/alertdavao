# Google Sign-In Setup Guide for AlertDavao (Expo)

## Prerequisites

### 1. Install Required Packages

```bash
cd UserSide
npx expo install expo-auth-session expo-crypto expo-web-browser
```

### 2. Get Google OAuth Credentials

#### Step 1: Go to Google Cloud Console
1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Name it "AlertDavao"

#### Step 2: Enable Google Sign-In API
1. Go to **APIs & Services** → **Library**
2. Search for "Google Sign-In API" or "Google Identity"
3. Click **Enable**

#### Step 3: Create OAuth 2.0 Credentials
1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **OAuth client ID**
3. Configure consent screen if prompted:
   - User Type: **External**
   - App name: **AlertDavao**
   - User support email: Your email
   - Developer contact: Your email
   - Save and continue

#### Step 4: Create OAuth Client IDs

You need **TWO** OAuth client IDs:

**A. Web Application Client ID** (for Expo)
- Application type: **Web application**
- Name: `AlertDavao Web Client`
- Authorized redirect URIs:
  ```
  https://auth.expo.io/@your-expo-username/userside
  ```
  To get your expo username: Run `npx expo whoami`

**B. Android Client ID** (for production app)
- Application type: **Android**
- Name: `AlertDavao Android`
- Package name: `com.yourcompany.alertdavao` (from app.json)
- SHA-1 certificate fingerprint: Get from your keystore

**Copy the Web Client ID** - you'll need this!

### 3. Configure app.json

Add to `UserSide/app.json`:

```json
{
  "expo": {
    "scheme": "alertdavao",
    "android": {
      "package": "com.yourcompany.alertdavao"
    },
    "extra": {
      "googleWebClientId": "YOUR_WEB_CLIENT_ID_HERE.apps.googleusercontent.com"
    }
  }
}
```

### 4. Create Environment Config

Create `UserSide/config/google.ts`:

```typescript
import Constants from 'expo-constants';

export const GOOGLE_WEB_CLIENT_ID = Constants.expoConfig?.extra?.googleWebClientId || '';

export const GOOGLE_CONFIG = {
  webClientId: GOOGLE_WEB_CLIENT_ID,
  scopes: ['profile', 'email'],
};
```

## Implementation

### 1. Update Login Screen

Replace the Google button `onPress` with actual Google Sign-In logic.

### 2. Handle Google Authentication

The system will:
1. Open Google Sign-In in browser
2. User selects Google account
3. User grants permissions
4. Get ID token and user info
5. Send to backend for verification
6. Create/login user in your database
7. Store session and redirect to app

### 3. Backend Verification

Backend must verify the Google ID token to prevent fake logins.

## Testing

### Development (Expo Go)
```bash
npx expo start
```
- Scan QR code with Expo Go app
- Click "Continue with Google"
- Sign in with Google account
- Should redirect back to app

### Production Build
```bash
npx eas build --platform android
```

## Security Notes

✅ **Always verify tokens on backend** - Don't trust client data  
✅ **Use HTTPS in production** - Required for OAuth  
✅ **Store client ID in app.json extra** - Not in code  
✅ **Validate redirect URIs** - Prevent phishing  

## Troubleshooting

### "Redirect URI mismatch"
- Check your redirect URI in Google Console matches Expo's
- Format: `https://auth.expo.io/@your-username/your-app-slug`

### "Invalid Client ID"
- Verify you're using the **Web Application** client ID
- Not the Android client ID

### "Sign in failed"
- Check internet connection
- Verify Google Sign-In API is enabled
- Check expo-auth-session is installed

## Next Steps

1. Run: `npx expo install expo-auth-session expo-crypto expo-web-browser`
2. Get Web Client ID from Google Console
3. Add to app.json
4. Update login.tsx with Google Sign-In code
5. Update backend to verify Google tokens
6. Test with Expo Go

Ready to implement? Let me know and I'll update the code files!
