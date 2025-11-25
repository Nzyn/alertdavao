# Complete OTP Implementation Guide

## ✅ What Has Been Implemented

### UserSide (React Native)
1. **OTP Modal Fix**: 
   - Fixed TextInput not being editable
   - Added `autoFocus={true}` for better UX
   - Added `Pressable` wrapper to prevent modal dismiss on text input

2. **Existing OTP Flow**:
   - ✅ Registration with OTP
   - ✅ Login with OTP
   - ✅ Backend handles OTP generation and verification
   - ✅ Console logging for development (when Twilio not configured)

### AdminSide (Laravel/PHP)
1. **New OTP System**:
   - ✅ `OtpController.php` - Handles send and verify OTP
   - ✅ Database tables (`otp_codes`, `verified_phones`)
   - ✅ API routes (`/api/otp/send`, `/api/otp/verify`)
   - ✅ Twilio SMS integration (same as UserSide)

## 🔧 Setup Instructions

### Step 1: Database Setup

Run this SQL in your `alertdavao` database:

```sql
-- For UserSide (if not already exists)
CREATE TABLE IF NOT EXISTS otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(64) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    purpose VARCHAR(64) NOT NULL,
    user_id INT DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone_purpose (phone, purpose)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS verified_phones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(64) NOT NULL UNIQUE,
    verified TINYINT(1) DEFAULT 1,
    verified_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 2: Configure Twilio (Optional but Recommended)

#### Get FREE Twilio Account:
1. Sign up at https://www.twilio.com/try-twilio
2. Get $15 FREE credit (~500 SMS)
3. Note your credentials from console

#### Add to UserSide `.env`:
```env
# UserSide/backends/.env
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+1234567890
NODE_ENV=development
```

#### Add to AdminSide `.env`:
```env
# AdminSide/admin/.env
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+1234567890
APP_ENV=local
```

### Step 3: Start Servers

#### UserSide Backend:
```bash
cd UserSide/backends
node server.js
```

#### AdminSide:
```bash
cd AdminSide/admin
php artisan serve
```

## 📱 How It Works

### UserSide Login Flow (Already Implemented):
1. User enters email + password
2. Backend validates credentials
3. If valid, returns `need_otp: true` with user's phone
4. Frontend sends OTP to phone via `/api/otp/send`
5. User enters 6-digit OTP
6. Frontend verifies via `/api/otp/verify`
7. On success, user is logged in

### UserSide Register Flow (Already Implemented):
1. User fills registration form
2. Frontend sends OTP to phone
3. User enters OTP in modal
4. Frontend verifies OTP
5. On success, account is created

### AdminSide Login/Register (NEW - Needs Frontend Update):
- Backend is ready with OTP API endpoints
- Frontend (Blade templates) need JavaScript to call OTP APIs
- Same flow as UserSide

## 🧪 Testing Without Twilio

If you haven't set up Twilio:
- OTP codes will be logged to the console
- Check terminal output for `🔐 OTP CODE for +639...`
- The OTP is also included in the API response as `debugOtp`

## 🔒 Security Notes

1. **Never use `react-native-mobile-sms`** for OTP - it's insecure and costs users money
2. **Always use server-side OTP** generation
3. **OTP expires in 10 minutes**
4. **OTP is hashed** before storing in database
5. **Twilio credentials** must be in `.env`, never hardcode

## 📋 Next Steps

To complete the implementation:

### For AdminSide Login/Register:
1. Update `login.blade.php` to add OTP modal
2. Add JavaScript to call `/api/otp/send` and `/api/otp/verify`
3. Update `AuthController` to require OTP verification

### Files to Update:
- `AdminSide/admin/resources/views/auth/login.blade.php`
- `AdminSide/admin/resources/views/auth/register.blade.php`
- `AdminSide/admin/app/Http/Controllers/AuthController.php`

## 🆘 Troubleshooting

### OTP TextInput not working:
- ✅ Fixed by adding `autoFocus={true}` and `Pressable` wrapper

### OTP not received:
- Check if Twilio credentials are set
- Check terminal console for logged OTP
- Verify phone number format (+63XXXXXXXXXX)

### Database errors:
- Run the SQL migration scripts
- Check MySQL connection in `.env`

## 🎯 Summary

**UserSide**: ✅ Fully functional with OTP for login & register
**AdminSide**: ✅ Backend ready, needs frontend integration

The OTP system is secure, reliable, and uses industry-standard Twilio for SMS delivery.
