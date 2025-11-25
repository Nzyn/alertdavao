# AlertDavao 2.0

Emergency reporting system for Davao City with real-time chat, geolocation, and police station integration.

---

## 🚀 Quick Start

### Automatic Restart (Recommended)
```bash
scripts\restart-all.bat          # Restart all services
scripts\restart-backend.bat      # Restart backend only
scripts\restart-userside.bat     # Restart mobile app only
scripts\restart-admin.bat        # Restart admin panel only
```

### Manual Start
```bash
# 1. Start Backend Server
cd alertdavao2.0/UserSide/backends
npm start

# 2. Start UserSide App (in new terminal)
cd alertdavao2.0/UserSide
npm start

# 3. Start Admin Panel (in new terminal)
cd alertdavao2.0/adminSide/admin
php -S localhost:8000
```

---

## 📱 Access Points

- **Backend API**: `http://192.168.1.11:3000`
- **UserSide App**: Scan QR code in Expo terminal or access `http://localhost:8082`
- **Admin Panel**: `http://localhost:8000`

---

## 📚 Documentation

**All project documentation is in ONE place:**

**→ [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)** ← Single source of truth (625 KB, 18,211 lines, 92 docs)

This comprehensive file includes:
- ✅ Complete chronological history of all 92 documentation files
- ✅ All fixes applied with timestamps
- ✅ Complete API endpoints reference
- ✅ Database schema information
- ✅ Troubleshooting commands
- ✅ Configuration details
- ✅ Development notes

**⚠️ Important**: Do NOT create new .md files. Add all documentation to PROJECT_SUMMARY.md

**Need help?** See [HOW_TO_ADD_DOCS.md](HOW_TO_ADD_DOCS.md) for instructions

---

## 🔧 Current Configuration

- **Machine IP**: `192.168.1.11`
- **Backend Port**: `3000`
- **Backend URL**: `http://192.168.1.11:3000/api`

---

## ⚠️ Important Notes

1. **After network/IP changes**: Update `.env.local` and run `scripts\restart-all.bat`
2. **All documentation in ONE file**: Check `PROJECT_SUMMARY.md` (18,216 lines, 92 docs compiled)
3. **DO NOT create new .md files**: Add content to `PROJECT_SUMMARY.md` instead
4. **Restart scripts**: All scripts in `scripts/` folder
5. **To recompile docs**: Run `scripts\compile-docs.ps1` (if needed)
6. **SQL files**: All database scripts in `sql/` folder

---

## 🐛 Troubleshooting

### Backend not accessible?
```bash
# Check if backend is running
netstat -ano | findstr :3000

# Check your IP address
ipconfig | findstr "IPv4"

# Test connection
curl http://192.168.1.11:3000/api/test-connection
```

### UserSide connection issues?
1. Verify `.env.local` has correct IP: `192.168.1.11`
2. Restart UserSide: `scripts\restart-userside.bat`
3. Check backend is running: `netstat -ano | findstr :3000`

---

## 📦 Project Structure

```
alertdavao2.0.new/
├── README.md                    ← Quick start guide
├── PROJECT_SUMMARY.md           ← ALL documentation (632 KB)
│
├── scripts/                     ← All automation scripts
│   ├── restart-all.bat         ← Restart all services
│   ├── restart-backend.bat     ← Restart backend
│   ├── restart-userside.bat    ← Restart mobile app
│   ├── compile-docs.ps1        ← Compile documentation
│   └── ... (19 scripts total)
│
├── sql/                         ← Database scripts
│   ├── seed_police_stations.sql
│   ├── FIX_PS3_REPORTS.sql
│   └── ... (7 SQL files)
│
├── docs/                        ← Documentation guides
│   ├── HOW_TO_ADD_DOCS.md
│   └── DOCUMENTATION_POLICY.md
│
└── alertdavao2.0/
    ├── UserSide/               ← Mobile app (React Native/Expo)
    │   ├── backends/           ← Node.js backend server
    │   ├── .env.local          ← Environment config
    │   └── ...
    │
    └── adminSide/              ← PHP admin panel
        └── admin/
```

---

**For complete documentation, see [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)**
