# AlertDavao 2.0 - Complete Setup Guide

## 📋 Table of Contents

1. [Backend Setup](#backend-setup)
2. [Frontend Setup (Expo Go)](#frontend-setup-expo-go)
3. [Quick Connection Test](#quick-connection-test)
4. [Troubleshooting](#troubleshooting)

---

## Backend Setup

### Prerequisites

- Node.js 14+ installed
- MariaDB server installed and running
- npm package manager

### 1. Install Backend Dependencies

```bash
cd alertdavao2.0
npm install
```

### 2. Configure Database

Create a `.env` file in the backend directory with:

```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=alertdavao2
DB_PORT=3306

NODE_ENV=development
PORT=3000
```

**Important:** Make sure MariaDB is running before starting backend.

### 3. Start Backend Server

```bash
npm start
```

**Expected output:**
```
Server listening on 0.0.0.0:3000
Database connected
```

**Keep this terminal open.** Backend must be running for the app to work.

---

## Frontend Setup (Expo Go)

### Prerequisites

- Expo CLI installed (`npm install -g expo-cli`)
- Expo Go app installed on your phone (iOS App Store or Google Play)
- Both phone and computer on same WiFi network

### 1. Install Frontend Dependencies

```bash
cd alertdavao2.0/UserSide
npm install
```

### 2. Find Your Computer's IP Address

**Windows - Command Prompt:**
```bash
ipconfig
```

Look for "IPv4 Address" like: `192.168.1.4`

**Mac/Linux - Terminal:**
```bash
ifconfig
```

Look for "inet" like: `192.168.1.4`

### 3. Configure Backend URL

In the `UserSide` directory, create `.env.local`:

```
EXPO_PUBLIC_BACKEND_URL=http://192.168.1.4:3000
```

**Replace `192.168.1.4` with YOUR actual IP from Step 2**

### 4. Start Expo Development Server

```bash
npx expo start
```

You should see:
```
Expo server is ready at http://...
Scan the QR code...
```

### 5. Connect with Expo Go

**On your phone:**
1. Open **Expo Go** app
2. Scan the **QR code** from terminal
3. Wait for app to load (first time takes ~30 seconds)
4. App should now be running

**Alternative (Web):**
```bash
# In terminal, press 'w' for web preview
```

---

## Quick Connection Test

### Test 1: Backend is accessible

**On your computer's browser:**
```
http://localhost:3000/api/barangays
```

Should see JSON data like:
```json
[{"location_id": 1, "barangay": "Barangay Name", ...}]
```

### Test 2: Backend is accessible from phone

**On your phone's browser (same WiFi):**
```
http://192.168.1.4:3000/api/barangays
```

(Replace 192.168.1.4 with your actual IP)

Should see same JSON data.

### Test 3: App can connect

Look at app console (shake phone → Show console, or press F12 on web):

Should see logs like:
```
📍 Backend URL: http://192.168.1.4:3000
✅ Barangays fetched successfully
```

---

## Environment Variables

### `.env.local` (UserSide)

```bash
# Set this to your computer's local network IP
EXPO_PUBLIC_BACKEND_URL=http://192.168.1.4:3000
```

### `.env` (Backend)

```bash
# Database
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=alertdavao2
DB_PORT=3306

# Server
NODE_ENV=development
PORT=3000
```

---

## Architecture

```
Your Computer (Development Machine)
├── Backend Server (Node.js + Express)
│   ├── Port 3000
│   ├── MariaDB Connection
│   └── Endpoints: /api/barangays, /login, /api/reports, etc.
│
└── Expo Development Server
    └── Serves UserSide app

Your Phone (Same WiFi)
├── Expo Go App
│   ├── Scans QR code from Expo server
│   ├── Downloads and runs app code
│   └── Connects to Backend Server on http://YOUR_IP:3000
│
└── WiFi Connection to Backend
```

---

## Troubleshooting

### "Network request failed" Error

This means app can't reach backend server.

**Quick fixes:**

1. ✅ Is backend running?
   ```bash
   # In backend terminal, should see "Server listening"
   ```

2. ✅ Is `.env.local` correct?
   ```bash
   cat UserSide/.env.local
   # Should show: EXPO_PUBLIC_BACKEND_URL=http://YOUR_IP:3000
   ```

3. ✅ Are both on same WiFi?
   - Computer and phone must be on same WiFi network
   - Check phone WiFi settings

4. ✅ Is firewall blocking port 3000?
   - Windows: Allow Node.js through firewall
   - Mac: System Preferences → Security & Privacy → Firewall

5. ✅ Did you restart Expo after changing `.env.local`?
   - Press Ctrl+C in Expo terminal
   - Run `npx expo start` again

### Barangay list not loading

**Causes:**
1. Backend not running
2. Database not connected
3. `/api/barangays` endpoint doesn't exist

**Check:**
```bash
# 1. Test endpoint in browser
http://localhost:3000/api/barangays

# 2. Check backend logs for errors
# Backend terminal should show request logs

# 3. Check MariaDB is running
# Check database credentials in backend .env
```

### Can't find .env.local after creating it

Make sure you're in the `UserSide` directory:
```bash
cd alertdavao2.0/UserSide
ls -la | grep .env    # Shows all .env* files
```

If file doesn't exist, create it:
```bash
echo "EXPO_PUBLIC_BACKEND_URL=http://192.168.1.4:3000" > .env.local
```

---

## Common Errors and Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| Network request failed | Backend unreachable | Check backend is running, correct IP in `.env.local` |
| Cannot GET /api/barangays | Wrong endpoint | Check backend has this route |
| ECONNREFUSED | Backend not running | Run `npm start` in backend |
| Timeout | Firewall blocking | Allow port 3000 through firewall |
| Wrong backend URL used | `.env.local` not updated or Expo not restarted | Update `.env.local` and restart Expo |
| CORS errors | Backend CORS not configured | Check backend has `cors()` middleware |

---

## Development Workflow

### Each time you develop:

**Terminal 1 - Backend:**
```bash
cd alertdavao2.0
npm start
```

**Terminal 2 - Expo:**
```bash
cd alertdavao2.0/UserSide
npx expo start
```

**On phone:**
1. Open Expo Go
2. Scan QR code
3. App loads

### Making changes:

- **Backend changes:** Restart backend (Ctrl+C, `npm start`)
- **Frontend changes:** Expo auto-reloads (see "Fast Refresh" in console)
- **`.env.local` changes:** Restart Expo (press 'r' in terminal)

---

## Next Steps

1. ✅ [Follow Backend Setup](#backend-setup)
2. ✅ [Follow Frontend Setup](#frontend-setup-expo-go)
3. ✅ [Run Quick Connection Test](#quick-connection-test)
4. ✅ Try logging in with test credentials
5. ✅ Check Troubleshooting if issues arise

See individual markdown files for detailed information:
- `UserSide/EXPO_GO_SETUP.md` - Detailed Expo Go setup
- `UserSide/BACKEND_CONNECTION_SETUP.md` - Backend connection details
- `UserSide/QUICK_FIX.md` - Quick troubleshooting steps
- `UserSide/TROUBLESHOOTING.md` - Comprehensive troubleshooting guide

---

## Support Files Created

- ✅ `.env.example` - Example configuration
- ✅ `.env.local` - Your local configuration
- ✅ `EXPO_GO_SETUP.md` - Expo Go setup guide
- ✅ `BACKEND_CONNECTION_SETUP.md` - Technical documentation
- ✅ `QUICK_FIX.md` - Quick troubleshooting
- ✅ `TROUBLESHOOTING.md` - Complete troubleshooting guide

All files are in the appropriate directories and ready to help you get set up.

---

## Questions?

Check these files in order:
1. `UserSide/QUICK_FIX.md` - For quick fixes
2. `UserSide/TROUBLESHOOTING.md` - For detailed troubleshooting
3. `UserSide/BACKEND_CONNECTION_SETUP.md` - For technical details

Good luck! 🚀
