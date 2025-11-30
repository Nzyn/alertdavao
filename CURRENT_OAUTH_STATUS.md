# Current OAuth Status Check

## Your Current Setup (in app.json)

**Line 55:** `"googleWebClientId": "662961186057-603clci7p37le9pg1et7it4b9e8md6ig.apps.googleusercontent.com"`

This is a **DIFFERENT Client ID** than the original one!

**Original:** `662961186057-3ski6ooemfi1j19k6b5nh6o648kk6pdp.apps.googleusercontent.com`  
**Current:** `662961186057-603clci7p37le9pg1et7it4b9e8md6ig.apps.googleusercontent.com`

This means you already created a new credential! ✅

---

## What You Need to Verify

In **Google Cloud Console**, for the new credential (`662961186057-603clci7p37le9pg1et7it4b9e8md6ig`), verify:

1. **Go to:** https://console.cloud.google.com/
2. **APIs & Services** → **Credentials**
3. **Find:** OAuth 2.0 Client ID ending with `...603clci7p37le9pg1et7it4b9e8md6ig`
4. **Check "Authorized redirect URIs"** - should include:
   - ✅ `https://auth.expo.io/@imiyataox/userside`
   - ✅ `https://auth.expo.io/@imiyataox/UserSide`
   - ✅ `http://localhost:8081` (optional, but helpful for development)

---

## The Issue You're Seeing

Error: `redirect_uri=http://localhost:8081`

This means the app is still trying to use localhost. This happens because:
1. You're running `npm start` (development server)
2. Development mode defaults to `http://localhost:8081`
3. But that URI isn't registered with your new credential

---

## Fix: Make Sure Redirect URIs Are Registered

Go to your new credential in Google Cloud Console and **verify/add these redirect URIs:**

```
https://auth.expo.io/@imiyataox/userside
https://auth.expo.io/@imiyataox/UserSide
http://localhost:8081
```

All three should be there!

---

## Then:

1. **Save in Google Cloud Console**
2. **Wait 30-60 seconds** for changes to propagate
3. **Clear browser cache** (Ctrl+Shift+Delete)
4. **Restart Expo:**
   ```bash
   npm start -- --clear
   ```
5. **Test Google Sign-In**

---

## Code Changes Already Done

✅ `googleAuth.ts` - Updated to use explicit redirect URI: `https://auth.expo.io/@imiyataox/userside`  
✅ `app.json` - Has new Client ID: `662961186057-603clci7p37le9pg1et7it4b9e8md6ig`

**Now you just need to confirm the redirect URIs are registered in Google Cloud!**

---

## Quick Checklist

- [ ] Logged into Google Cloud Console
- [ ] Found the new credential (`...603clci7p37le9pg1et7it4b9e8md6ig`)
- [ ] Verified "Authorized redirect URIs" includes all three URIs
- [ ] Clicked SAVE
- [ ] Waited 30-60 seconds
- [ ] Cleared browser cache
- [ ] Ran `npm start -- --clear`
- [ ] Tested Google Sign-In button
- [ ] Works! ✅

---

## If You Don't See Your New Credential

It's possible the new credential wasn't created yet. 

In that case, **create it now:**
1. APIs & Services → Credentials
2. **+ CREATE CREDENTIALS** → **OAuth client ID**
3. **Application type:** Web application
4. **Name:** AlertDavao Expo
5. **Authorized redirect URIs:**
   ```
   https://auth.expo.io/@imiyataox/userside
   https://auth.expo.io/@imiyataox/UserSide
   http://localhost:8081
   ```
6. Click CREATE
7. Copy the new Client ID
8. **Update app.json line 55** with this new Client ID

Then follow the "Then:" steps above.

---

**Current Status:** You're 90% there! Just need to verify/add the redirect URIs in Google Cloud Console.
