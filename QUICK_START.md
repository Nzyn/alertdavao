# 🚀 QUICK START GUIDE

## Start the Server in 3 Seconds

```bash
cd AdminSide/admin
php artisan serve
```

**Done!** Both Laravel and SARIMA API will start automatically.

---

## What Just Happened?

When you run `php artisan serve`:

1. ✅ Laravel starts on http://localhost:8000
2. ✅ AppServiceProvider detects 'local' environment
3. ✅ Checks if SARIMA API is already running
4. ✅ If not, launches it via PowerShell in hidden window
5. ✅ SARIMA API starts on http://localhost:8001
6. ✅ Both services ready!

---

## Verify It's Working

Open browser and test:

```
http://localhost:8000          → Laravel dashboard
http://localhost:8000/reports  → Reports page
http://localhost:8000/statistics → Statistics with live forecasts
http://localhost:8001/         → SARIMA API health check
```

Or use PowerShell:

```powershell
# Test Laravel
curl http://localhost:8000

# Test SARIMA API
curl http://localhost:8001/ | ConvertFrom-Json
```

---

## What's Included?

### Data:
- 960 total reports
- 90 assigned to PS2 (San Pedro)
- 237 assigned to PS3 (Talomo)
- 4 police users configured

### Features:
- Live SARIMA crime forecasting
- Police station-based filtering
- Automatic report assignment
- Interactive crime map
- Statistics dashboard with charts

---

## Troubleshooting

### SARIMA API didn't auto-start?

Start it manually in a separate terminal:

```bash
cd AdminSide/sarima_api
python -m uvicorn main:app --host 0.0.0.0 --port 8001
```

### Want to verify everything?

```bash
cd AdminSide/admin
php test_full_system.php
```

---

## Login Credentials

### Police Users (Example):
- Email: joshua@gmail.com (PS2)
- Email: PCOLDanserdan@gmail.com (PS3)

Check database for passwords or create new users via admin panel.

---

## Stop the Server

Press `Ctrl+C` in the terminal running `php artisan serve`

To ensure all processes stopped:

```powershell
Get-Process php,python -ErrorAction SilentlyContinue | Stop-Process -Force
```

---

**That's it! Your system is ready to use.** ✅
