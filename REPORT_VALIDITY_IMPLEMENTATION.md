# Report Validity Implementation

## Overview

This implementation adds a report validation system where police/admin users can mark reports as VALID or INVALID. Only VALID reports are fed into the SARIMA forecasting API, ensuring that forecast predictions are based on verified crime data.

## Changes Made

### 1. Database Schema Updates

#### New Migration File
- **File**: `AdminSide/admin/database/migrations/2025_12_01_000000_add_is_valid_to_reports_table.php`
- **Change**: Adds `is_valid` column to reports table with default value `'checking_for_report_validity'`
- **Values**: 
  - `checking_for_report_validity` (default status when report is submitted)
  - `valid` (police/admin marks report as valid)
  - `invalid` (police/admin marks report as invalid)

#### Updated SQL Files
- `sql/database_migration_fixed.sql`: Updated reports table definition
- `UserSide/backends/database_migration.sql`: Added conditional column addition for `is_valid`

### 2. Backend Changes

#### Report Model
- **File**: `AdminSide/admin/app/Models/Report.php`
- **Change**: Added `is_valid` to the `$fillable` array

#### ReportController
- **File**: `AdminSide/admin/app/Http/Controllers/ReportController.php`
- **New Method**: `updateValidity($request, $id)`
  - Validates the is_valid value (must be one of: 'valid', 'invalid', 'checking_for_report_validity')
  - Updates the report's validity status
  - Returns JSON response with success/error message

#### Routes
- **File**: `AdminSide/admin/routes/web.php`
- **New Route**: `PUT /reports/{id}/validity` → `ReportController@updateValidity`

#### StatisticsController
- **File**: `AdminSide/admin/app/Http/Controllers/StatisticsController.php`
- **getForecast() Method**:
  - Added filter: `->where('is_valid', 'valid')`
  - Only queries reports marked as VALID for SARIMA training and forecasting
  - Updated comments to reflect this change
- **getCrimeStats() Method**:
  - Updated comment to clarify statistics show all reports
  - Forecasting uses only VALID reports
- **exportCrimeData() Method**:
  - Added filter: `->where('is_valid', 'valid')`
  - CSV export only includes VALID reports

### 3. Frontend Changes

#### Reports View
- **File**: `AdminSide/admin/resources/views/reports.blade.php`

##### Styling
- Added `.validity-badge` class for displaying validity status badges
  - `.validity-badge.valid` - green badge
  - `.validity-badge.invalid` - red badge
  - `.validity-badge.checking_for_report_validity` - yellow badge
- Added `.validity-select` class for dropdown styling

##### Table Headers and Rows
- Added new table header: "Validity" (width: 140px)
- Added new table column with validity status dropdown
- Dropdown options: "Checking...", "Valid", "Invalid"
- Uses `updateValidity()` JavaScript function on change

##### JavaScript Functions
- **New Function**: `updateValidity(reportId, isValid)`
  - Similar to `updateStatus()` function
  - Makes PUT request to `/reports/{id}/validity`
  - Sends selected validity status
  - Updates UI on success or reverts on error
  - Shows confirmation alert to user

## Workflow

### User Report Submission
1. User submits a new report via mobile app
2. Report is created in database with `is_valid = 'checking_for_report_validity'`
3. Report appears in admin dashboard

### Police/Admin Review (Admin Side)
1. Admin/Police officer opens Reports page
2. Sees report with Validity dropdown showing "Checking..."
3. Reviews report details
4. Changes Validity status to:
   - **Valid** → Report will be included in SARIMA forecasting
   - **Invalid** → Report will NOT be included in SARIMA forecasting
   - **Checking...** → Report will NOT be included (default state)

### SARIMA Forecasting Integration
1. When `StatisticsController::getForecast()` is called:
   - Queries database for reports where `is_valid = 'valid'` only
   - Trains SARIMA model with validated crime data
   - Generates forecasts based only on verified reports
   - Returns predictions to frontend

## Data Flow Diagram

```
User Submits Report (Mobile/Web)
    ↓
Report Created with is_valid = 'checking_for_report_validity'
    ↓
Admin Reviews Report (Admin Dashboard)
    ↓
Admin Sets Validity Status:
    ├── Valid → Report included in SARIMA
    ├── Invalid → Report excluded from SARIMA
    └── Checking → Report excluded from SARIMA (default)
    ↓
SARIMA Forecasting Query:
    ├── WHERE is_valid = 'valid'
    ├── Gets monthly counts of VALID reports
    ├── Trains SARIMA model
    └── Generates predictions
```

## Key Features

1. **Default Validity Status**: All new reports start with `is_valid = 'checking_for_report_validity'`
2. **Admin Control**: Only police and admin users can change validity status
3. **Dropdown Interface**: Easy-to-use dropdown in reports table for quick status changes
4. **SARIMA Integration**: Automatically filters reports before sending to forecasting API
5. **Validation**: Server-side validation ensures only valid enum values are saved
6. **User Feedback**: Toast/alert messages confirm status changes

## Testing Steps

1. **Create a Test Report**: Submit a new report from user side
   - Verify `is_valid` field is set to `'checking_for_report_validity'`

2. **Change Validity Status**: In admin dashboard
   - Click validity dropdown
   - Select "Valid"
   - Verify success message
   - Verify dropdown shows "Valid" after save

3. **Test SARIMA Filtering**: Check forecast generation
   - Create 5 test reports with mixed validity statuses
   - Set 3 as "Valid" and 2 as "Invalid"
   - Generate forecast via `/api/statistics/forecast`
   - Verify forecast uses only the 3 valid reports

4. **Test Export**: Check CSV export
   - Export crime data as CSV
   - Verify only VALID reports are included

5. **Police User Filtering**: 
   - Login as police officer
   - See only reports assigned to their station
   - Can mark reports as valid/invalid

## Database Migration

To apply changes to existing database:

### Option 1: Run Laravel Migration
```bash
cd AdminSide/admin
php artisan migrate
```

### Option 2: Run SQL Scripts
```sql
-- For new installations or manual setup
-- Execute: sql/database_migration_fixed.sql
-- For existing databases:
-- Execute: UserSide/backends/database_migration.sql
```

### Option 3: Manual SQL
```sql
ALTER TABLE reports ADD COLUMN is_valid VARCHAR(50) DEFAULT 'checking_for_report_validity' AFTER status;
```

## Backward Compatibility

- Existing reports without `is_valid` value will default to `'checking_for_report_validity'`
- Old SARIMA forecasting queries will need to be updated to filter by validity
- No impact on existing report submission or retrieval workflows

## Future Enhancements

1. **Audit Trail**: Log who changed validity status and when
2. **Bulk Actions**: Allow marking multiple reports as valid/invalid at once
3. **Auto-Validation**: Implement ML model to auto-suggest validity status
4. **Webhook Notifications**: Notify users when their report validity changes
5. **Analytics**: Dashboard showing percentage of valid vs invalid reports
6. **Role-Based Permissions**: Restrict who can mark reports as valid (e.g., only senior officers)

## Files Modified

1. ✅ `AdminSide/admin/database/migrations/2025_12_01_000000_add_is_valid_to_reports_table.php` (created)
2. ✅ `AdminSide/admin/app/Models/Report.php`
3. ✅ `AdminSide/admin/app/Http/Controllers/ReportController.php`
4. ✅ `AdminSide/admin/routes/web.php`
5. ✅ `AdminSide/admin/app/Http/Controllers/StatisticsController.php`
6. ✅ `AdminSide/admin/resources/views/reports.blade.php`
7. ✅ `sql/database_migration_fixed.sql`
8. ✅ `UserSide/backends/database_migration.sql`
