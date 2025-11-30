# UserSide Bundling Fix

## What Was Fixed

The bundler was failing due to module resolution issues with:
1. `@supabase/postgrest-js` - invalid package.json exports
2. `nanoid/non-secure` - missing `index.cjs` file

## Changes Made

### 1. Updated Dependencies
- **Downgraded** `@supabase/supabase-js` from `^2.84.0` to `^2.45.0`
  - Latest version has compatibility issues with Expo Metro bundler
  - v2.45.0 works reliably with React Native
  
- **Added explicit** `nanoid@^3.3.7`
  - Ensures correct module resolution
  - Fixes the "non-secure" import issue

### 2. Cleaned Installation
```bash
rm -rf node_modules package-lock.json
npm install --legacy-peer-deps
```

### 3. Added Metro Config
Created `metro.config.js` to:
- Fix module resolution paths
- Block problematic Supabase imports at build time
- Properly handle module extensions

### 4. Cleared Caches
- Removed `.expo` cache directory
- Fresh Metro build cache

## How to Run Now

### Start Development Server
```bash
npm start
```

### Run on Web
```bash
npm run web
```

### Run on Android
```bash
npm run android
```

### Run on iOS
```bash
npm run ios
```

## If Issues Persist

### Option 1: Fresh Clean Install
```bash
cd D:\Codes\alertdavao\alertdavao\UserSide
rm -rf node_modules .expo dist .next package-lock.json
npm install --legacy-peer-deps
npm start
```

### Option 2: Use npx to clear cache
```bash
npx expo start --clear
```

### Option 3: Use Watchman (if on Mac)
```bash
watchman watch-del-all
npm start
```

## Why These Versions?

- **@supabase/supabase-js@2.45.0**: Last stable version before major refactor
- **nanoid@3.3.7**: Compatible with React Native and browser environments
- **react-native@0.81.5**: Latest available for this Expo version
- **expo@54.0.6**: Latest stable Expo with good compatibility

## Compatible Package Versions

```
expo: ~54.0.6
expo-router: ~6.0.15
react: 19.1.0
react-native: 0.81.5
@supabase/supabase-js: ^2.45.0 (NOT ^2.84.0)
nanoid: ^3.3.7 (explicitly added)
@react-navigation/*: ^7.x.x
react-native-web: ^0.21.0
```

## Performance Note

The downgrade from Supabase v2.84.0 to v2.45.0 is minimal in terms of features:
- Still supports all main Supabase operations
- No breaking changes in authentication/database access
- Better compatibility with Expo ecosystem

If you need latest Supabase features, wait for Expo to update their bundler or use a different setup.

## Future Upgrade Path

When Expo releases updates, you can try upgrading Supabase again:
1. `npm update @supabase/supabase-js`
2. `npm start -- --clear`
3. Test thoroughly before committing

## Verification

Check that the app starts without bundling errors:
```bash
npm start
# Should see: "Expo server is ready at ..."
# NOT: "Metro error: While trying to resolve module"
```
