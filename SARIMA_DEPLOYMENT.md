# SARIMA API Deployment Guide

## Auto-Start Options

### Option 1: Local Development (Automatic)

The SARIMA API will **auto-start automatically** when you visit the Statistics page in local development.

**How it works:**
1. Visit `/statistics` page
2. Laravel checks if SARIMA API is running
3. If not running, automatically starts it in background
4. No manual intervention needed!

**Requirements:**
- Python installed on your machine
- `start_sarima.py` in project root
- Laravel environment set to `local`

### Option 2: Production Deployment (Systemd Service)

For production Linux servers, use systemd to run SARIMA API as a system service.

**Setup:**

1. **Install Python dependencies:**
```bash
cd /var/www/alertdavao/AdminSide/sarima_api
pip3 install -r requirements.txt
```

2. **Copy service file:**
```bash
sudo cp sarima-api.service /etc/systemd/system/
```

3. **Edit the service file (adjust paths):**
```bash
sudo nano /etc/systemd/system/sarima-api.service
# Update WorkingDirectory to your actual path
# Update User to your web server user
```

4. **Enable and start service:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable sarima-api
sudo systemctl start sarima-api
```

5. **Check status:**
```bash
sudo systemctl status sarima-api
```

**Service will:**
- ✅ Start automatically on server boot
- ✅ Restart automatically if it crashes
- ✅ Run in background
- ✅ Log to system journal

### Option 3: Production Deployment (Supervisor)

Alternative to systemd, use Supervisor for process management.

**Setup:**

1. **Install Supervisor:**
```bash
sudo apt-get install supervisor
```

2. **Copy config:**
```bash
sudo cp AdminSide/sarima_api/supervisor.conf /etc/supervisor/conf.d/sarima-api.conf
```

3. **Edit config (adjust paths):**
```bash
sudo nano /etc/supervisor/conf.d/sarima-api.conf
```

4. **Reload and start:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sarima-api
```

5. **Check status:**
```bash
sudo supervisorctl status sarima-api
```

### Option 4: Docker Deployment

Create `AdminSide/sarima_api/Dockerfile`:

```dockerfile
FROM python:3.9-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

EXPOSE 8001

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001"]
```

Then in `docker-compose.yml`:

```yaml
services:
  sarima-api:
    build: ./AdminSide/sarima_api
    ports:
      - "8001:8001"
    volumes:
      - ./AdminSide/sarima_api:/app
    restart: unless-stopped
```

Start with:
```bash
docker-compose up -d sarima-api
```

## Environment-Specific Behavior

### Local Development (APP_ENV=local)
- **Auto-starts** when you visit Statistics page
- No manual startup needed
- Runs in background

### Production (APP_ENV=production)
- Requires **manual setup** (systemd/supervisor/docker)
- Runs as system service
- Auto-starts on server boot
- Production-ready with logging and auto-restart

## Quick Reference

### Development (Windows)
```bash
# Option 1: Auto-start (just visit /statistics)
# Option 2: Manual start
python start_sarima.py
```

### Development (Linux/Mac)
```bash
# Option 1: Auto-start (just visit /statistics)
# Option 2: Manual start
python3 start_sarima.py
```

### Production (Linux - Systemd)
```bash
# Start
sudo systemctl start sarima-api

# Stop
sudo systemctl stop sarima-api

# Restart
sudo systemctl restart sarima-api

# Status
sudo systemctl status sarima-api

# Logs
sudo journalctl -u sarima-api -f
```

### Production (Supervisor)
```bash
# Start
sudo supervisorctl start sarima-api

# Stop
sudo supervisorctl stop sarima-api

# Restart
sudo supervisorctl restart sarima-api

# Status
sudo supervisorctl status sarima-api
```

## Checking if API is Running

**Browser:**
- Visit: http://localhost:8001
- Should show: `{"status":"ok","message":"SARIMA API is running."}`

**Command line:**
```bash
curl http://localhost:8001
```

**From Statistics page:**
- Green message: ✅ API is running
- Red message: ⚠️ API is not running

## Troubleshooting

### API not auto-starting in development

**Check:**
1. Is `start_sarima.py` in project root?
2. Is Python installed? Run: `python --version`
3. Is Laravel env set to `local`? Check `.env`: `APP_ENV=local`
4. Check Laravel logs: `storage/logs/laravel.log`

### API not running in production

**Check:**
1. Service status: `sudo systemctl status sarima-api`
2. Service logs: `sudo journalctl -u sarima-api -f`
3. Python dependencies installed?
4. Firewall allows port 8001?

### Port 8001 already in use

**Find process:**
```bash
# Windows
netstat -ano | findstr :8001

# Linux
sudo lsof -i :8001
```

**Kill process:**
```bash
# Windows (use PID from netstat)
taskkill /PID <PID> /F

# Linux
sudo kill <PID>
```

## Summary

| Environment | Method | Auto-Start? | Setup Required |
|------------|--------|-------------|----------------|
| **Local Dev** | Auto-start | ✅ Yes | None - just visit page |
| **Local Manual** | Python script | ❌ No | Run `start_sarima.py` |
| **Production** | Systemd | ✅ On boot | One-time setup |
| **Production** | Supervisor | ✅ On boot | One-time setup |
| **Production** | Docker | ✅ On boot | Docker setup |

**Recommended:**
- **Development:** Use auto-start (no action needed)
- **Production:** Use systemd or supervisor
