# 🚀 SARIMA API - Auto-Start Explained

## TL;DR

**In development:** The SARIMA API automatically starts when you visit the Statistics page. No manual action needed!

**In production:** You set it up once as a system service, and it runs automatically forever.

## How Auto-Start Works

### Development Mode (Local)

When you visit `/statistics` page:

```
1. User visits /statistics
        ↓
2. StatisticsController checks: Is SARIMA API running?
        ↓
3. If NO → Automatically starts SARIMA API in background
        ↓
4. If YES → Page loads normally
        ↓
5. Statistics page displays with forecasts
```

**Requirements:**
- `APP_ENV=local` in `.env`
- Python installed
- `start_sarima.py` in project root

**How to check if it worked:**
- Green message: ✅ "SARIMA API is running and providing forecasts"
- Red message: ⚠️ "SARIMA API is not available" (auto-start failed)

### Production Mode (Deployment)

In production (`APP_ENV=production`), auto-start is **disabled** for security and stability.

Instead, you configure it as a **system service** that:
- ✅ Starts automatically on server boot
- ✅ Restarts automatically if it crashes
- ✅ Runs in background 24/7
- ✅ Has proper logging

## Why Different for Development vs Production?

| Aspect | Development (Auto-Start) | Production (Service) |
|--------|-------------------------|----------------------|
| **Setup** | Automatic, no config needed | One-time manual setup |
| **Reliability** | Starts on-demand | Always running |
| **Performance** | May delay first page load | No delays |
| **Security** | Local only | Proper user permissions |
| **Logging** | Console output | System logs |
| **Restart** | On-demand | Automatic on crash |

## Auto-Start Code

The auto-start logic is in `StatisticsController.php`:

```php
public function index()
{
    // Try to auto-start SARIMA API if in development
    $this->autoStartSarimaApi();
    
    return view('statistics');
}

private function autoStartSarimaApi()
{
    // Only in local environment
    if (!app()->environment('local')) {
        return false;
    }
    
    // Check if already running
    if ($this->isSarimaApiRunning()) {
        return true;
    }
    
    // Start in background using start_sarima.py
    // ...
}
```

## Deployment Options

### Option 1: Systemd (Linux - Recommended)

**Setup once:**
```bash
sudo cp AdminSide/sarima_api/sarima-api.service /etc/systemd/system/
sudo systemctl enable sarima-api
sudo systemctl start sarima-api
```

**Benefits:**
- Starts on boot
- Auto-restarts on crash
- System-level logging
- Standard Linux service

### Option 2: Supervisor (Linux)

**Setup once:**
```bash
sudo apt install supervisor
sudo cp AdminSide/sarima_api/supervisor.conf /etc/supervisor/conf.d/sarima-api.conf
sudo supervisorctl reread
sudo supervisorctl update
```

**Benefits:**
- Easy process management
- Web dashboard available
- Multiple process support
- Good for shared hosting

### Option 3: Docker

**Setup once:**
```bash
docker-compose up -d sarima-api
```

**Benefits:**
- Isolated environment
- Easy scaling
- Portable deployment
- Version control

### Option 4: Windows Service (Production Windows)

Use NSSM (Non-Sucking Service Manager):
```bash
nssm install SarimaAPI "C:\Python\python.exe" "-m uvicorn main:app --host 0.0.0.0 --port 8001"
nssm set SarimaAPI AppDirectory "C:\path\to\AdminSide\sarima_api"
nssm start SarimaAPI
```

## Manual Control (All Environments)

### Check if Running
```bash
# Browser
http://localhost:8001

# Command line
curl http://localhost:8001
```

### Start Manually
```bash
# From project root
python start_sarima.py

# Or from sarima_api directory
python -m uvicorn main:app --host 0.0.0.0 --port 8001
```

### Stop
```bash
# Find process
# Windows
netstat -ano | findstr :8001

# Linux
sudo lsof -i :8001

# Kill process
# Windows
taskkill /PID <PID> /F

# Linux
sudo kill <PID>
```

## FAQ

**Q: Do I need to start it manually in development?**
A: No! Just visit the Statistics page and it starts automatically.

**Q: What about in production?**
A: Set it up once as a system service (systemd/supervisor). Then it runs automatically forever.

**Q: Can I disable auto-start in development?**
A: Yes, change `APP_ENV` to anything other than `local` in `.env`.

**Q: What if auto-start fails?**
A: The page will show an error. Start manually with `python start_sarima.py`.

**Q: Does the .bat file still work?**
A: Yes! You can still use `START_SARIMA_API.bat` for manual start if you prefer.

**Q: How do I know if it's running?**
A: Visit http://localhost:8001 - you should see: `{"status":"ok","message":"SARIMA API is running."}`

**Q: Can I run multiple instances?**
A: No, port 8001 can only have one instance. For multiple instances, use different ports.

## Summary

✅ **Development:** Auto-starts automatically - just visit the page!
✅ **Production:** Set up once as a service - runs automatically forever
✅ **Manual:** Still available if you prefer control
✅ **No .bat file needed:** But it's still there if you want it

See detailed deployment instructions: `SARIMA_DEPLOYMENT.md`
