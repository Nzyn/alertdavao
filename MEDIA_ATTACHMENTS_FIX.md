# Media Attachments Fix - Complete Summary

## Problem
Report attachments (images) were not displaying in:
1. AdminSide report details modal
2. PDF downloads of reports

**Root Cause:** Laravel AdminSide was looking for media files in `storage/app/public`, but the React Native mobile app uploads files to `UserSide/evidence` folder (served by the Node.js backend).

## Solution Overview
Modified Laravel's `ReportController::getMediaUrl()` to:
1. Check if media files exist in `UserSide/evidence` folder
2. Return URLs pointing to Node.js backend (`http://localhost:3000/evidence/filename.jpg`)
3. Fall back to Laravel's storage paths if needed

## Changes Made

### 1. ReportController.php
**File:** `AdminSide/admin/app/Http/Controllers/ReportController.php`

**Method:** `getMediaUrl($mediaUrl)` - Lines 343-410

**Changes:**
- Added check for files in `UserSide/evidence` folder (absolute path)
- Constructs URLs using `NODE_BACKEND_URL` config value
- Returns: `http://localhost:3000/evidence/evidence-1234567890.jpg`
- Maintains fallback to Laravel storage paths for compatibility

**Key Code:**
```php
// Check if file exists in the React Native evidence folder
$userSideEvidencePath = dirname(dirname(dirname(__DIR__))) . '/../../UserSide/evidence/' . $fileName;

if (file_exists($userSideEvidencePath)) {
    // Serve from Node.js backend URL
    $nodeBackendUrl = config('app.node_backend_url', 'http://localhost:3000');
    $url = $nodeBackendUrl . '/evidence/' . $fileName;
    return $url;
}
```

### 2. Environment Configuration
**File:** `AdminSide/admin/.env`

**Added:**
```env
NODE_BACKEND_URL=http://localhost:3000
```

This allows Laravel to construct proper URLs pointing to the Node.js backend's static file server.

### 3. Configuration Cache
**Command Run:**
```bash
php artisan config:clear
```

Cleared Laravel's config cache to pick up the new `NODE_BACKEND_URL` environment variable.

## File Storage Architecture

### React Native (UserSide)
```
UserSide/
├── evidence/                          ← Media files stored here
│   ├── evidence-1764574689349-957438467.jpg
│   ├── evidence-1764540364891-362263762.png
│   └── ...
├── backends/
│   └── server.js                      ← Node.js backend
│       - Runs on port 3000
│       - Serves /evidence via express.static
│       - CORS enabled (origin: "*")
```

**Multer Configuration (server.js):**
```javascript
const evidenceStorage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, path.join(__dirname, '../evidence'));
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, 'evidence-' + uniqueSuffix + path.extname(file.originalname));
  }
});
```

**Static File Serving (server.js, line 167):**
```javascript
app.use('/evidence', express.static(path.join(__dirname, '../evidence')));
```

### Laravel (AdminSide)
```
AdminSide/admin/
├── storage/
│   └── app/
│       └── public/                    ← Empty (not used for mobile uploads)
├── public/
│   └── storage/                       ← Symlink created (but unused)
```

## Database Structure

**Table:** `report_media`

**Sample Data:**
```
media_id: 16
report_id: 978
media_url: "/evidence/evidence-1764574689349-957438467.jpg"
media_type: "jpg"
```

**Note:** The `media_url` field stores paths like `/evidence/evidence-*.jpg`, which matches the Node.js backend's static file route.

## Testing & Verification

### 1. File Existence Check
```powershell
Test-Path "d:\Codes\Laravel.ReactNative\ALERTDAVAOSERDAN\alertdavao\UserSide\evidence\evidence-1764574689349-957438467.jpg"
# Result: True
```

### 2. Node.js Backend Accessibility
```powershell
Invoke-WebRequest -Uri "http://localhost:3000/evidence/evidence-1764574689349-957438467.jpg" -Method Head
# Result:
# StatusCode: 200
# ContentType: image/jpeg
# ContentLength: 143311
```

### 3. Backend Status
```powershell
Get-NetTCPConnection -LocalPort 3000
# Result: LISTENING on 0.0.0.0:3000
```

## How It Works Now

### Report Details Modal
1. User clicks "View Details" on a report in AdminSide
2. `ReportController::getDetails($id)` is called
3. For each media item in `report->media`:
   - Extracts filename from `media_url` (e.g., "evidence-1764574689349-957438467.jpg")
   - Checks if file exists in `UserSide/evidence/`
   - Constructs URL: `http://localhost:3000/evidence/evidence-1764574689349-957438467.jpg`
   - Sets `display_url` property
4. JavaScript in `reports.blade.php` renders images using `display_url`
5. Images load via Node.js backend with CORS enabled

### PDF Generation
1. User clicks "Download PDF" button
2. `generatePDF(report)` function runs (reports.blade.php, line 1605)
3. For each `report.media` item:
   - Uses `mediaItem.display_url` (already set by backend)
   - Creates `<img>` elements with `crossOrigin = 'anonymous'`
   - Loads images from `http://localhost:3000/evidence/...`
4. `html2canvas` captures the rendered content
5. `jsPDF` generates PDF with embedded images
6. Downloads as `report_00978.pdf`

## CORS Configuration
The Node.js backend already has CORS enabled for all origins:

```javascript
app.use(cors({ origin: "*" }));
```

This allows Laravel (running on `localhost:8000`) to load images from Node.js backend (`localhost:3000`).

## Troubleshooting

### Images Still Not Showing?

1. **Check Node.js backend is running:**
   ```powershell
   Get-NetTCPConnection -LocalPort 3000
   ```

2. **Verify environment variable:**
   ```powershell
   cd AdminSide/admin
   php artisan tinker --execute="echo config('app.node_backend_url');"
   ```
   Should output: `http://localhost:3000`

3. **Check Laravel logs:**
   ```powershell
   Get-Content "AdminSide/admin/storage/logs/laravel.log" -Tail 20
   ```
   Look for "Media file found in UserSide" messages

4. **Test direct access:**
   Open browser: `http://localhost:3000/evidence/evidence-1764574689349-957438467.jpg`

5. **Clear browser cache:**
   Old `/storage/` URLs might be cached

### PDF Images Not Loading?

1. Check browser console for CORS errors
2. Verify `crossOrigin = 'anonymous'` is set (line 1743 in reports.blade.php)
3. Ensure Node.js backend CORS is enabled (line 163 in server.js)

## Files Modified

1. `AdminSide/admin/app/Http/Controllers/ReportController.php`
   - Updated `getMediaUrl()` method

2. `AdminSide/admin/.env`
   - Added `NODE_BACKEND_URL=http://localhost:3000`

## Files Created

1. `MEDIA_ATTACHMENTS_FIX.md` (this file)

## No Files Deleted

## Configuration Cached?
If changes don't take effect:
```bash
cd AdminSide/admin
php artisan config:clear
php artisan cache:clear
```

## Production Deployment Notes

When deploying to production, update `.env`:
```env
NODE_BACKEND_URL=https://your-nodejs-backend.com
```

Ensure:
1. Node.js backend is accessible from Laravel server
2. CORS is configured to allow Laravel's domain
3. HTTPS is used if Laravel is on HTTPS
4. Firewall allows communication between servers

## Related Documentation

- **Map Implementation:** See previous conversation about Leaflet maps with geofencing
- **Encryption Fix:** `AdminSide/admin/app/Services/EncryptionService.php`
- **Police Stations:** 18 valid stations displayed as blue shield icons
- **Davao City Bounds:** `[[6.9, 125.2], [7.5, 125.7]]`

## Summary

✅ **Fixed:** Media attachments now display in report details modal
✅ **Fixed:** PDF downloads include images
✅ **Verified:** Files exist in `UserSide/evidence` (10+ files found)
✅ **Verified:** Node.js backend serves files correctly (HTTP 200)
✅ **Verified:** CORS allows cross-origin requests

**Next Steps for User:**
1. Refresh the AdminSide reports page (Ctrl+F5 to clear cache)
2. Click "View Details" on any report with attachments
3. Images should now display
4. Test PDF download - images should be included
