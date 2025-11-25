# 🚀 Quick Start - SARIMA Crime Forecasting

## What Was Integrated?

Your AdminSide Statistics page now has:
- **Crime Trend Forecasting** using SARIMA machine learning model
- **Interactive Charts** showing historical data and future predictions
- **Crime Analysis** by type and location
- **Data Export** capabilities
- **Auto-Start** - API starts automatically in development!

## How to Use

### Development Mode (Easiest - Auto-Start!)

**Just visit the Statistics page - that's it!**

1. Make sure your AdminSide Laravel server is running
2. Login to AdminSide
3. Click **Statistics** in the sidebar
4. The SARIMA API will **automatically start** in the background
5. You should see:
   - ✅ Green message: "SARIMA API is running and providing forecasts"
   - Crime trend chart with forecast (red dashed line)
   - Crime by Type (doughnut chart)
   - Top Locations (bar chart)

**No manual setup needed in development!**

### Manual Start (Optional)

If you prefer to start manually or auto-start doesn't work:

**Option A - Python script:**
```bash
python start_sarima.py
```

**Option B - Batch file (Windows):**
```bash
START_SARIMA_API.bat
```

**Option C - Direct command:**
```bash
cd AdminSide\sarima_api
pip install -r requirements.txt
python -m uvicorn main:app --reload --host 0.0.0.0 --port 8001
```

## What You Can Do

### View Forecasts
- Select forecast horizon: 6, 12, 18, or 24 months
- See confidence intervals (shaded area)
- Click refresh to reload data

### Analyze Crime Patterns
- **Overview Cards** - Total reports, this month, last month, trends
- **Crime by Type** - Distribution of different crime types
- **Top Locations** - Which barangays have most reports

### Export Data
- **Export Crime Data (CSV)** - Download all crime data for analysis
- **Download Forecast (JSON)** - Get raw forecast data from API

## File Structure

```
📁 AdminSide/
  📁 sarima_api/                    ← Python API
    📄 main.py                      ← SARIMA model & API endpoints
    📄 requirements.txt             ← Python dependencies
    📄 start.bat                    ← API startup script
    📁 data/
      📄 CrimeDAta.csv             ← Historical crime data
  
  📁 admin/
    📁 app/Http/Controllers/
      📄 StatisticsController.php  ← Laravel controller
    📁 resources/views/
      📄 statistics.blade.php      ← UI page
    📁 routes/
      📄 web.php                   ← Routes (updated)

📄 START_SARIMA_API.bat            ← Quick start script (root)
📄 SARIMA_INTEGRATION_GUIDE.md     ← Full documentation
```

## Troubleshooting

### Auto-start not working
**Check:**
1. Python installed? Run: `python --version`
2. `.env` has `APP_ENV=local`?
3. Check Laravel logs: `AdminSide/admin/storage/logs/laravel.log`

**Workaround:** Start manually with `python start_sarima.py`

### "SARIMA API is not available" error
**Fix:** 
1. Check if Python is installed
2. Try manual start: `python start_sarima.py`
3. Check if port 8001 is free

### Python not found
**Fix:** Install Python from https://www.python.org/downloads/
Make sure to check "Add Python to PATH" during installation

### No forecast line appearing
**Fix:** 
1. Check if API is running: Open http://localhost:8001 in browser
2. Refresh the statistics page
3. Check browser console (F12) for errors

### Charts not loading
**Fix:**
1. Make sure AdminSide database connection is working
2. Verify you have reports in the database
3. Check Laravel logs: `AdminSide/admin/storage/logs/laravel.log`

## API Endpoints

### SARIMA API (Python - Port 8001)
- `GET /` - Health check
- `GET /forecast?horizon=12` - Get forecast

### Laravel API
- `GET /api/statistics/forecast?horizon=12` - Get forecast (proxied)
- `GET /api/statistics/crime-stats` - Database statistics
- `GET /api/statistics/export` - Export CSV

## Next Steps

1. ✅ **Just visit the Statistics page** - API will auto-start!
2. ✅ Explore the forecasts and charts
3. ✅ Try different forecast horizons (6, 12, 18, 24 months)
4. Update the model with fresh data (export → replace CSV → restart API)

## For Production Deployment

See: `SARIMA_DEPLOYMENT.md` for systemd/supervisor setup

## Need Help?

- **Quick Start:** This file
- **Deployment:** `SARIMA_DEPLOYMENT.md`
- **Full Documentation:** `SARIMA_INTEGRATION_GUIDE.md`
- **Summary:** `SARIMA_INTEGRATION_SUMMARY.md`
