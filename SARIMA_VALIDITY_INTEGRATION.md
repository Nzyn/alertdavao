# SARIMA Forecasting - Validity Filter Integration

## Overview

The SARIMA forecasting system has been updated to only include reports marked as **VALID** by police/admin users. This ensures that crime predictions are based on verified, reliable data.

## How It Works

### 1. Report Submission (User Side)

When a user submits a report:
- Report is created in database
- `is_valid` field is automatically set to `'checking_for_report_validity'`
- Report is NOT included in SARIMA forecasting yet

```php
// From ReportController::store()
$reportData = [
    'user_id' => $request->user_id,
    'title' => $request->title,
    'description' => $request->description,
    // ... other fields
    // is_valid is NOT explicitly set, so it defaults to 'checking_for_report_validity'
];
$report = Report::create($reportData);
```

### 2. Admin Review (Admin Side)

Police/Admin users review the report in the admin dashboard:
- New "Validity" column shows dropdown with three options
- Admin can select:
  - **Checking...** (default) - report awaiting validation
  - **Valid** ✅ - report is legitimate and reliable
  - **Invalid** ❌ - report is false, unreliable, or incomplete

```javascript
// From reports.blade.php
function updateValidity(reportId, isValid) {
    fetch(`/reports/${reportId}/validity`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', ... },
        body: JSON.stringify({ is_valid: isValid })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report validity status updated successfully');
            // UI already shows the selected status
        }
    });
}
```

### 3. SARIMA Forecast Generation

When SARIMA API generates forecasts, it queries ONLY **VALID** reports:

```php
// From StatisticsController::getForecast()
$liveData = DB::table('reports')
    ->select(
        DB::raw('YEAR(created_at) as year'),
        DB::raw('MONTH(created_at) as month'),
        DB::raw('COUNT(*) as count')
    )
    ->where('created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 36 MONTH)'))
    ->where('is_valid', 'valid')  // ← KEY FILTER
    ->groupBy('year', 'month')
    ->orderBy('year', 'asc')
    ->orderBy('month', 'asc')
    ->get();

// Send to SARIMA for training
$trainResponse = Http::post("{$this->sarimaApiUrl}/train", [
    'data' => $trainingData  // Only VALID reports
]);
```

## Data States and Transitions

```
Report Created
    ↓
is_valid = 'checking_for_report_validity'
    ↓ [Admin reviews and sets status]
    ├─→ is_valid = 'valid' ✅ [Included in SARIMA]
    └─→ is_valid = 'invalid' ❌ [Excluded from SARIMA]
         ↓ [Can change mind later]
         ├─→ is_valid = 'valid' ✅ [Now included in SARIMA]
         └─→ is_valid = 'checking_for_report_validity' ⏳ [Excluded until reviewed]
```

## Forecast Impact Examples

### Scenario 1: All Reports Valid
- **Month 1**: 100 reports submitted, all marked as VALID
- **SARIMA sees**: 100 reports for Month 1
- **Forecast includes**: Crime count of 100

### Scenario 2: Mixed Validity
- **Month 1**: 100 reports submitted
  - 80 marked as VALID
  - 15 marked as INVALID
  - 5 still CHECKING
- **SARIMA sees**: 80 reports for Month 1 (only the VALID ones)
- **Forecast includes**: Crime count of 80

### Scenario 3: No Valid Reports
- **Month 1**: 50 reports submitted, all marked as INVALID
- **SARIMA sees**: 0 reports for Month 1
- **Forecast includes**: Crime count of 0

## Database Filtering

### Original Query (Before)
```sql
SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count
FROM reports
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 36 MONTH)
GROUP BY year, month;
```

### Updated Query (After)
```sql
SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count
FROM reports
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 36 MONTH)
AND is_valid = 'valid'  ← NEW CONDITION
GROUP BY year, month;
```

## API Integration Points

### 1. StatisticsController::getForecast()
- **Called**: When admin requests crime forecast
- **Action**: Queries VALID reports, trains SARIMA model
- **Returns**: Forecast data

### 2. StatisticsController::getCrimeStats()
- **Called**: For dashboard statistics
- **Action**: Shows all reports (not filtered by validity)
- **Purpose**: Admin wants to see total reports vs. valid ones

### 3. StatisticsController::exportCrimeData()
- **Called**: When exporting crime data as CSV
- **Action**: Exports only VALID reports
- **Use Case**: Data analysis, reporting

## Backwards Compatibility

### For Existing Reports (Before Migration)

When migration is applied, existing reports will:
1. Get `is_valid = 'checking_for_report_validity'` as default
2. NOT be included in SARIMA forecasting until explicitly marked as VALID
3. Still be visible in admin dashboard and reports list

**Migration Query**:
```sql
ALTER TABLE reports 
ADD COLUMN is_valid VARCHAR(50) DEFAULT 'checking_for_report_validity' AFTER status;
```

### For Old SARIMA Models

Old forecast data (before this update) will show higher crime counts because:
- Old data included ALL reports (valid + invalid + checking)
- New data includes only VALID reports
- This is intentional - we want more accurate predictions

## Monitoring and Validation Metrics

### Key Metrics to Track

1. **Validity Rate** = (Valid Reports / Total Reports) × 100
2. **Reports Pending Review** = COUNT(is_valid = 'checking_for_report_validity')
3. **Invalid Report Rate** = (Invalid Reports / Total Reports) × 100
4. **Forecast Accuracy** = Compare SARIMA predictions vs. actual VALID reports

### Example Dashboard Query

```php
$stats = [
    'total_reports' => DB::table('reports')->count(),
    'valid_reports' => DB::table('reports')->where('is_valid', 'valid')->count(),
    'invalid_reports' => DB::table('reports')->where('is_valid', 'invalid')->count(),
    'pending_reports' => DB::table('reports')
        ->where('is_valid', 'checking_for_report_validity')->count(),
    'validity_rate' => (valid / total) * 100,
];
```

## Admin Permissions

### Who Can Mark Reports as Valid/Invalid?

- **Admin** ✅ (role = 'admin')
- **Police Officer** ✅ (role = 'police')
- **Regular User** ❌ (role = 'user')

### Recommended Workflow

1. **Police Officer** marks reports as valid/invalid based on initial review
2. **Admin** can override/review police decisions
3. **System Admin** monitors validity metrics

## Testing Scenarios

### Test 1: New Report Default State
```
1. Submit report via mobile app
2. Check database: SELECT is_valid FROM reports WHERE report_id = X
3. Expected: is_valid = 'checking_for_report_validity'
4. Verify SARIMA doesn't include it in forecast
```

### Test 2: Valid Report Included
```
1. Create test report
2. Mark as VALID in admin dashboard
3. Run forecast: /api/statistics/forecast
4. Verify forecast includes this report in count
5. Compare with historical data
```

### Test 3: Invalid Report Excluded
```
1. Create test report
2. Mark as INVALID in admin dashboard
3. Run forecast: /api/statistics/forecast
4. Verify forecast excludes this report
5. Compare count with total reports submitted
```

### Test 4: Bulk Status Changes
```
1. Create 5 test reports
2. Mark 3 as VALID, 2 as INVALID
3. Generate forecast
4. Verify forecast uses only the 3 valid reports
5. Delete one VALID report
6. Generate forecast again
7. Verify forecast now uses 2 reports
```

## Troubleshooting

### Issue: SARIMA Forecast Still Includes Invalid Reports

**Solution**: Check that migration was run successfully
```bash
php artisan migrate --check
```

### Issue: Validity Dropdown Not Appearing

**Solution**: Clear view cache
```bash
php artisan view:clear
php artisan config:clear
```

### Issue: Reports Disappearing from Forecast

**Explanation**: This is expected! When you change a report from VALID to INVALID, it will:
- Still appear in reports list
- Be excluded from next SARIMA forecast
- Not counted in monthly crime statistics for forecasting

## Performance Considerations

### Query Optimization

The new filter `->where('is_valid', 'valid')` is:
- Indexed on `is_valid` column for fast lookups
- Combined with date range filter for efficiency
- Reduces dataset size before aggregation

### Recommended Index

```sql
-- If not exists, add index for faster queries
CREATE INDEX idx_is_valid ON reports(is_valid);
CREATE INDEX idx_created_valid ON reports(created_at, is_valid);
```

## Future Enhancements

1. **Automatic Validity Detection**
   - Use ML model to suggest validity status
   - Admin approves/rejects suggestion

2. **Validity History**
   - Track when validity status changed
   - Store who changed it and why
   - Audit trail for compliance

3. **Batch Operations**
   - Mark multiple reports as valid/invalid at once
   - Filter by location, type, date range

4. **Notifications**
   - Notify users when report validity changes
   - Alert admins of reports pending review

5. **Analytics**
   - Dashboard showing validity metrics
   - Comparison of valid vs. invalid report types
   - Geographic validity patterns
