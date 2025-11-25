# Storage Setup Instructions for Report Images

## Quick Setup (Windows)

### Step 1: Navigate to Admin Directory
```bash
cd d:\Codes\Laravel.ReactNative\AlertDavao\alertdavao\AdminSide\admin
```

### Step 2: Create Storage Symlink

**Option A: Using PowerShell (Admin)**
```powershell
New-Item -ItemType SymbolicLink -Path "public\storage" -Target "..\storage\app\public" -Force
```

**Option B: Using Command Prompt (Admin)**
```cmd
mklink /D public\storage ..\storage\app\public
```

**Option C: Using Laravel Artisan**
```bash
php artisan storage:link
```

### Step 3: Verify Setup
Check that the symlink was created:
```bash
dir public | findstr storage
```

You should see something like:
```
11/26/2025  12:00 AM    <SYMLINKD>     storage -> ..\storage\app\public
```

### Step 4: Verify Directories Exist
Ensure the storage directory structure exists:
```bash
dir storage\app\public\reports
```

If the directory doesn't exist, create it:
```bash
mkdir storage\app\public\reports
```

## Troubleshooting

### Symlink Won't Create
**Error:** "Cannot create a file when that file already exists"
```bash
# Remove old symlink first
rmdir public\storage
# Or on some systems
del public\storage
# Then create new one
New-Item -ItemType SymbolicLink -Path "public\storage" -Target "..\storage\app\public"
```

### Permission Denied
**Run PowerShell or Command Prompt as Administrator**

### Symlink Not Working
**Check if your Windows version supports symlinks:**
- Windows 10 Pro/Enterprise: Supported
- Windows 10 Home: Limited support (requires developer mode)
- Windows 11: Fully supported

**Enable Developer Mode (Windows 10 Home):**
1. Settings → Update & Security → For developers
2. Enable Developer Mode
3. Try creating symlink again

## Verify Images Display

### 1. Check via Laravel
```bash
php artisan tinker
```

Then in Tinker:
```php
$report = App\Models\Report::with('media')->first();
$report->media;
```

You should see report_media records with paths like:
```
"media_url" => "reports/filename.jpg"
```

### 2. Check via Browser
1. Navigate to: `http://your-admin-url/storage/`
2. You should see a file browser
3. If you see reports folder, the symlink is working

### 3. Test Image Loading
1. Go to Reports page
2. View report details
3. Check browser console (F12)
4. Should see: `Image loaded successfully: reports/...`

## File Paths Reference

### Storage Location
```
storage/app/public/reports/
├── [filename1].jpg
├── [filename2].png
└── ...
```

### Web Accessible URL
```
public/storage/reports/
├── [filename1].jpg
├── [filename2].png
└── ...
```

### Database Entry
```
media_url: "reports/[filename].jpg"
```

### Generated URL (in backend)
```
/storage/reports/[filename].jpg
```

### Full URL (in browser)
```
https://your-domain.com/storage/reports/[filename].jpg
```

## Next Steps

After setting up storage:

1. **Run diagnostic check:**
   ```bash
   php artisan check:report-media
   ```

2. **Clear caches:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Test image display:**
   - Go to Reports page
   - Open report with images
   - Images should display in modal

## Still Having Issues?

1. Check the detailed guide: `REPORT_IMAGES_FIX_GUIDE.md`
2. Run: `php artisan check:report-media --fix`
3. Review Laravel logs: `storage/logs/laravel.log`
