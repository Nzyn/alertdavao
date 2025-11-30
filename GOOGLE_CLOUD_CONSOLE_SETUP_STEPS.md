# Google Cloud Console Setup - Step by Step

## Goal
Create a **NEW** OAuth 2.0 Web Application credential for Expo with the correct redirect URI.

---

## Step 1: Open Google Cloud Console

**URL:** https://console.cloud.google.com/

You should see a dashboard. If not logged in, log in with the account where you created the project.

---

## Step 2: Make Sure You're in the Right Project

Look at the **top of the page** - there's a project dropdown.

**You need to be in:** `alertdavao` (or whatever your Google Cloud project is named)

If not, click the dropdown and select the correct project.

---

## Step 3: Navigate to Credentials

In the **left sidebar**, find and click:
```
APIs & Services
  ↓
Credentials
```

Or use the search bar at the top and search for "Credentials"

---

## Step 4: Create New OAuth Client ID

Click the blue button: **"+ CREATE CREDENTIALS"**

Then select: **"OAuth client ID"**

---

## Step 5: Configure the New Credential

A form will appear. Fill it like this:

### Application Type
```
⚪ Desktop application
⚪ Mobile application  
⚪ Chrome application
⚪ Chrome extension
⚪ Web application  ← SELECT THIS
⚫ Installed application
```

### Name
```
Text field: AlertDavao Expo
```

(Just give it a clear name so you can identify it later)

### Authorized JavaScript Origins
**Leave this BLANK** - don't fill it

### Authorized Redirect URIs
**This is the important part!** Click "ADD URI" and add **EXACTLY these two lines:**

```
https://auth.expo.io/@imiyataox/userside
https://auth.expo.io/@imiyataox/UserSide
```

**Important notes:**
- Copy EXACTLY as shown (no typos!)
- **https** (not http)
- Both lines have different capitalization - add both
- Separate them by clicking "ADD URI" between them

Your screen should look like:
```
┌─────────────────────────────────────────────┐
│ Authorized redirect URIs                    │
│ ┌─────────────────────────────────────────┐ │
│ │ https://auth.expo.io/@imiyataox/userside│ │
│ └─────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────┐ │
│ │ https://auth.expo.io/@imiyataox/UserSide│ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

---

## Step 6: Create the Credential

Click the blue **"CREATE"** button at the bottom.

---

## Step 7: Copy Your New Client ID

A **popup or modal** will appear with your credentials:

```
┌──────────────────────────────────────────┐
│ OAuth client created                     │
│                                          │
│ Client ID:                               │
│ 123456789-abc123xyz.apps.googleusercontent.com │
│                                          │
│ Client Secret:                           │
│ your_secret_key_here                     │
│                                          │
│ [DOWNLOAD JSON] [CLOSE]                  │
└──────────────────────────────────────────┘
```

**Copy the Client ID** - you'll need it for app.json

The Client ID will look like: `XXXXXXXXX-XXXXXXXXXXX.apps.googleusercontent.com`

---

## Step 8: Verify in Credentials List

Close the popup and scroll down in the Credentials page.

You should now see **TWO** OAuth 2.0 Client IDs:

1. **Old one:** `662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com`
   - Authorized redirect URIs: `http://localhost:8081` (or similar)

2. **New one:** `[YOUR_NEW_ONE_HERE]` (the one you just created)
   - Authorized redirect URIs:
     - `https://auth.expo.io/@imiyataox/userside`
     - `https://auth.expo.io/@imiyataox/UserSide`

You'll use the **NEW one** in your app.

---

## Done with Google Cloud Console! ✅

Next steps in your code:

1. Copy the new Client ID
2. Update `UserSide/app.json` with it
3. Restart the app with `npm start -- --clear`
4. Test Google Sign-In

---

## Quick Reference: What You Just Created

| Setting | Value |
|---------|-------|
| **Name** | AlertDavao Expo |
| **Type** | Web application |
| **Redirect URIs** | `https://auth.expo.io/@imiyataox/userside` + `https://auth.expo.io/@imiyataox/UserSide` |
| **Client ID** | `[YOUR_NEW_CLIENT_ID]` |

---

## Troubleshooting

### Can't find "Create Credentials"?
- Make sure you're in the right project (top of page)
- Make sure you're in "Credentials" page (left sidebar)
- Refresh the page if needed

### "Consent screen" popup appears?
- If you see a form asking about app name, user support email, etc:
  - Fill in the required fields
  - Click "SAVE AND CONTINUE"
  - Click "SAVE AND CONTINUE" again
  - Then you can create the OAuth credential

### The popup closed without showing Client ID?
- Go back to Credentials
- Find your new credential in the list
- Click on it to see the Client ID

---

## Next: Update Your Code

Once you have the new Client ID, follow these steps:

**In `UserSide/app.json` (around line 55):**
```json
"googleWebClientId": "[PASTE_YOUR_NEW_CLIENT_ID_HERE]",
```

**Then:**
```bash
npm start -- --clear
```

That's it! Google Sign-In should now work.
