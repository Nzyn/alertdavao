# System Startup Test Results

## Quick Test - Run `php artisan serve`

This document shows what happens when you run `php artisan serve`.

### Expected Behavior:

1. **Laravel starts** on `http://localhost:8000`
2. **SARIMA API auto-starts** on `http://localhost:8001` (via AppServiceProvider)
3. Both services run simultaneously

### Test Commands:

```powershell
# Terminal 1: Start Laravel (auto-starts SARIMA)
cd AdminSide/admin
php artisan serve

# Terminal 2: Verify both are running
curl http://localhost:8000          # Should return Laravel welcome page
curl http://localhost:8001/health   # Should return SARIMA API health status
```

### Auto-Start Configuration:

✅ **AppServiceProvider.php** - Configured to auto-start SARIMA API
- Location: `AdminSide/admin/app/Providers/AppServiceProvider.php`
- Trigger: When Laravel boots in 'local' environment
- Method: Uses PowerShell script on Windows

✅ **Environment** - Set to 'local'
- File: `AdminSide/admin/.env`
- Value: `APP_ENV=local`

✅ **PowerShell Script** - Background startup script
- Location: `AdminSide/sarima_api/start_background.ps1`
- Command: Starts Python uvicorn server in hidden window

### System Health Check Results:

Run: `php test_full_system.php`

```
✓ PHP Version: 8.3.26
✓ Laravel bootstrapped successfully
✓ Database connected: alertdavao
✓ All critical tables exist
✓ Reports Data:
  - Total: 960 reports
  - PS2 Assigned: 90 reports
  - PS3 Assigned: 237 reports
  - Unassigned: 611 reports
✓ Police Users:
  - Total: 4 users
  - PS2: 2 users
  - PS3: 1 user
✓ Routes: reports, statistics, view-map registered
```

### Components Status:

| Component | Status | Port | Notes |
|-----------|--------|------|-------|
| Laravel Server | ✅ Ready | 8000 | Start with `php artisan serve` |
| SARIMA API | ✅ Auto-starts | 8001 | Launches when Laravel boots |
| Database | ✅ Connected | 3306 | MySQL - alertdavao |
| Reports System | ✅ Working | - | 960 total reports |
| Police Assignment | ✅ Working | - | PS2: 90, PS3: 237 |
| Statistics & Forecasts | ✅ Working | - | Live training enabled |

### Manual Start (If Auto-Start Fails):

If SARIMA API doesn't auto-start, run manually:

```powershell
# Terminal 1: SARIMA API
cd AdminSide/sarima_api
python -m uvicorn main:app --host 0.0.0.0 --port 8001

# Terminal 2: Laravel
cd AdminSide/admin
php artisan serve
```

### Verification URLs:

After starting, test these URLs in browser:

- **Laravel Dashboard**: http://localhost:8000
- **Reports Page**: http://localhost:8000/reports
- **Statistics**: http://localhost:8000/statistics
- **Map View**: http://localhost:8000/view-map
- **SARIMA Health**: http://localhost:8001/health
- **SARIMA Forecast**: http://localhost:8001/forecast

### Recent Updates:

1. ✅ SARIMA API updated to v2.0.0 with live training
2. ✅ Reports table uses `assigned_station_id` for police filtering
3. ✅ All controllers updated: DashboardController, MapController, ReportController
4. ✅ 922 sample reports geocoded and assigned to PS2/PS3
5. ✅ Auto-assignment works for new reports via `assignReportToStation()`

### Key Features Working:

- **Live SARIMA Training**: API trains on database data, not static CSV
- **Police Station Filtering**: Police users see only their assigned reports
- **Automatic Assignment**: New reports auto-assign based on barangay location
- **Statistics Dashboard**: Crime trends & forecasts with Chart.js
- **Real-time Map**: Filtered by police station for police role users

### Troubleshooting:

**If SARIMA doesn't auto-start:**
1. Check AppServiceProvider.php is loading
2. Verify APP_ENV=local in .env
3. Ensure start_background.ps1 exists
4. Check Python is in system PATH
5. Manually start SARIMA API as shown above

**If reports not showing for police:**
1. Verify user has `station_id` set (2 or 3)
2. Check reports have `assigned_station_id` matching
3. Run: `php verify_police_access.php` to check assignments

**If statistics not loading:**
1. Check SARIMA API is running on port 8001
2. Verify database has reports data
3. Check browser console for errors
4. Test: `curl http://localhost:8001/health`
