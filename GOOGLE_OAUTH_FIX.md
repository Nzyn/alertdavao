# Fix Google OAuth Error 400: invalid_request

## The Problem
Google is blocking sign-in because your OAuth app isn't properly configured in Google Cloud Console.

## Quick Fix (5 minutes)

### Step 1: Go to Google Cloud Console
1. Open https://console.cloud.google.com/
2. Select your project (or create one if needed)

### Step 2: Configure OAuth Consent Screen
1. Go to **APIs & Services** > **OAuth consent screen**
2. Select **External** user type (unless you have Google Workspace)
3. Click **Create**

### Step 3: Fill in App Information
**App Information:**
- **App name**: `AlertDavao` (or your app name)
- **User support email**: Your email
- **App logo**: (optional, can skip)

**App domain:**
- **Application home page**: `https://expo.dev` (or leave blank)
- **Privacy policy**: (can skip for testing)
- **Terms of service**: (can skip for testing)

**Developer contact:**
- **Email**: Your email

Click **Save and Continue**

### Step 4: Add Scopes
1. Click **Add or Remove Scopes**
2. Select these scopes:
   - `userinfo.email`
   - `userinfo.profile`
3. Click **Update**
4. Click **Save and Continue**

### Step 5: Add Test Users (IMPORTANT!)
1. Click **Add Users**
2. Add your Gmail address(es) that you'll use for testing
3. Click **Add**
4. Click **Save and Continue**

### Step 6: Review and Submit
1. Review the summary
2. Click **Back to Dashboard**

### Step 7: Verify OAuth Client
1. Go to **APIs & Services** > **Credentials**
2. Find your OAuth 2.0 Client ID
3. Make sure it has:
   - **Application type**: Web application
   - **Authorized redirect URIs**: Add `https://auth.expo.io/@your-username/UserSide`

## For Expo Go Specifically

Since you're using Expo Go, you need to add the Expo redirect URI:

1. In Google Cloud Console > Credentials > Your OAuth Client
2. Under **Authorized redirect URIs**, add:
   ```
   https://auth.expo.io/@YOUR_EXPO_USERNAME/UserSide
   ```
   Replace `YOUR_EXPO_USERNAME` with your actual Expo username

## Alternative: Use Development Mode

For testing, you can keep the app in **Testing** mode:
- This allows up to 100 test users
- No review needed
- Perfect for development

## After Setup

1. Wait 5-10 minutes for Google to propagate changes
2. Try signing in again with one of your test user emails
3. You should see the Google consent screen
4. Click "Allow" to grant permissions

## Common Issues

### "Access blocked: This app's request is invalid"
- Make sure you added your email as a test user
- Wait a few minutes after adding test users

### "Redirect URI mismatch"
- Check that you added the correct Expo redirect URI
- Format: `https://auth.expo.io/@username/projectslug`

### Still not working?
- Clear your browser/app cache
- Try a different Google account
- Check that OAuth consent screen is saved properly

---

**Quick Checklist:**
- ✅ OAuth consent screen configured
- ✅ Test users added (your email)
- ✅ Scopes added (email, profile)
- ✅ Redirect URI added for Expo
- ✅ Wait 5-10 minutes
- ✅ Try signing in again
