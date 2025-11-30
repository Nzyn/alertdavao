# Report Validity System - Quick Start Guide

## What Changed?

Added a report validation system where police/admin can mark reports as:
- **VALID** ✅ → Included in SARIMA forecasting
- **INVALID** ❌ → Excluded from SARIMA forecasting  
- **CHECKING** ⏳ → Excluded from SARIMA (default when report submitted)

## For Developers

### Run Migration
```bash
cd AdminSide/admin
php artisan migrate
```

### Key Code Locations
- **Database Column**: `reports.is_valid` (VARCHAR, default: 'checking_for_report_validity')
- **Controller**: `ReportController::updateValidity()`
- **Route**: `PUT /reports/{id}/validity`
- **Model**: `Report::$fillable` includes 'is_valid'

### How SARIMA Filtering Works

**Before**: SARIMA used ALL reports
```php
$reports = DB::table('reports')
    ->where('created_at', '>=', '3 months ago')
    ->get();
```

**After**: SARIMA uses only VALID reports
```php
$reports = DB::table('reports')
    ->where('created_at', '>=', '3 months ago')
    ->where('is_valid', 'valid')  // ← NEW FILTER
    ->get();
```

## For Admin Users

### How to Mark a Report as Valid/Invalid

1. **Go to Reports Dashboard** (Admin Side)
2. **Find the Report** in the table
3. **Look for "Validity" Column** (right before Actions)
4. **Click the Dropdown** with "Checking..." selected
5. **Select**:
   - `Checking...` → Don't include in forecast yet
   - `Valid` ✅ → Include in SARIMA forecasting
   - `Invalid` ❌ → Don't include in SARIMA forecasting
6. **Confirm** → Success message appears

### Report Workflow

```
User submits report
        ↓
Report created with is_valid = "checking_for_report_validity"
        ↓
Admin/Police reviews report
        ↓
Admin sets validity status:
  - Click "Checking..." dropdown in Validity column
  - Select "Valid" or "Invalid"
  - Save
        ↓
Next SARIMA forecast run:
  - Only includes reports marked as "Valid"
  - Generates predictions based on verified data
```

## Database Schema

```sql
ALTER TABLE reports ADD COLUMN is_valid VARCHAR(50) DEFAULT 'checking_for_report_validity' AFTER status;
```

**Valid Values**:
- `checking_for_report_validity` (default, reports not yet reviewed)
- `valid` (verified and safe to use in forecasting)
- `invalid` (false report or unreliable data)

## Files Changed

| File | Change |
|------|--------|
| `2025_12_01_000000_add_is_valid_to_reports_table.php` | New migration |
| `ReportController.php` | Added `updateValidity()` method |
| `Report.php` | Added 'is_valid' to fillable |
| `web.php` | Added `PUT /reports/{id}/validity` route |
| `StatisticsController.php` | Added `->where('is_valid', 'valid')` filter |
| `reports.blade.php` | Added Validity column + dropdown |

## API Endpoint

**Update Report Validity**
```
PUT /reports/{id}/validity
Content-Type: application/json
X-CSRF-TOKEN: [token]

{
  "is_valid": "valid"
}
```

Valid values: `valid`, `invalid`, `checking_for_report_validity`

## Testing

1. Submit a test report → Check `is_valid = 'checking_for_report_validity'` in DB
2. Mark as Valid in admin dashboard → Run forecast → Verify report is counted
3. Mark as Invalid → Run forecast → Verify report is NOT counted
4. Export data as CSV → Verify only VALID reports included

## Troubleshooting

**Issue**: Validity dropdown not showing in admin dashboard
- **Solution**: Clear browser cache, run `php artisan view:clear`

**Issue**: SARIMA forecast still includes all reports
- **Solution**: Ensure migration is ran: `php artisan migrate`

**Issue**: Can't update validity status
- **Solution**: Check user role (must be police or admin), verify CSRF token

## Questions?

See full documentation: `REPORT_VALIDITY_IMPLEMENTATION.md`
