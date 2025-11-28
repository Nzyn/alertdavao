# User Flagging System - Error Fix (November 28, 2025)

## Problem
When admin/police users tried to flag a user in the AdminSide, they received an error. The root cause was missing database tables required by the flagging system.

## Root Causes Identified

1. **Missing Database Tables**: The `flagUser()` controller method checks for the existence of `user_flags` and `user_restrictions` tables (line 169 in UserController.php), but these tables were never created via migrations.

2. **Missing User Columns**: The users table was missing `total_flags` and `restriction_level` columns needed to track flag status.

3. **No Role-Based Authorization**: While the system required `admin` and `police` roles, there was no explicit middleware enforcing this at the route level.

## Solutions Applied

### 1. Created Migration for `user_flags` Table
**File**: `database/migrations/2025_11_28_000000_create_user_flags_table.php`

Creates the `user_flags` table with:
- User ID (foreign key)
- Reported by (which admin/police officer flagged)
- Violation type (false_report, prank_spam, harassment, offensive_content, impersonation, multiple_accounts, system_abuse, inappropriate_media, misleading_info, other)
- Description of the violation
- Status (confirmed, appealed, dismissed)
- Severity level
- Reviewed by and timestamp
- Proper indexes for performance

### 2. Created Migration for `user_restrictions` Table
**File**: `database/migrations/2025_11_28_000001_create_user_restrictions_table.php`

Creates the `user_restrictions` table with:
- User ID (foreign key)
- Restriction type (warning, suspended, banned)
- Reason for restriction
- Admin who applied restriction
- Expiration timestamp
- Permission flags (can_report, can_message)
- Is active flag
- Admin who lifted restriction and timestamp
- Proper indexes

### 3. Created Migration for User Table Columns
**File**: `database/migrations/2025_11_28_000002_add_flag_columns_to_users_table.php`

Adds missing columns to users table:
- `total_flags` (integer, default 0) - tracks number of flags against a user
- `restriction_level` (enum: none, warning, suspended, banned) - current restriction status

### 4. Created Role-Based Authorization Middleware
**File**: `app/Http/Middleware/CheckRole.php`

New middleware that:
- Validates user authentication
- Checks if user has required role(s)
- Returns 403 Forbidden if user lacks permissions
- Returns 401 Unauthorized if not authenticated

### 5. Updated Routes Configuration
**File**: `routes/web.php`

- Wrapped flag/unflag routes with `middleware('role:admin,police')`
- Ensures only admin and police users can flag/unflag users

### 6. Registered Middleware in Kernel
**File**: `app/Http/Kernel.php`

Added role middleware alias to `$middlewareAliases` for route usage.

## How to Apply These Fixes

1. Run the migrations:
```bash
php artisan migrate
```

2. Test the flagging functionality:
- Log in as admin or police user
- Navigate to Users management page
- Try to flag a user
- System should now work without errors

## Files Modified
1. ✅ `routes/web.php` - Added role middleware to flag routes
2. ✅ `app/Http/Kernel.php` - Registered role middleware
3. ✅ `app/Http/Middleware/CheckRole.php` - New middleware (created)
4. ✅ `database/migrations/2025_11_28_000000_create_user_flags_table.php` - New migration (created)
5. ✅ `database/migrations/2025_11_28_000001_create_user_restrictions_table.php` - New migration (created)
6. ✅ `database/migrations/2025_11_28_000002_add_flag_columns_to_users_table.php` - New migration (created)

## Testing Checklist

- [ ] Run migrations successfully
- [ ] Log in as admin user and attempt to flag a user
- [ ] Verify flag is recorded with violation type and reason
- [ ] Log in as police user and attempt to flag a user
- [ ] Verify user restriction level is updated
- [ ] Try accessing flag endpoint as regular user (should get 403 Forbidden)
- [ ] Verify unflag functionality works and removes restrictions
- [ ] Check that flagged users appear in /flagged-users page
