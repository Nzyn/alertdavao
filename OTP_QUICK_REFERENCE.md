# OTP Implementation - Quick Reference

## What Changed

| Aspect | Before | Now |
|--------|--------|-----|
| OTP in Login | ✅ Yes | ❌ No |
| OTP in Signup | ✅ Yes | ✅ Yes |
| Expiry | 10 min | **5 min** |
| SMS Sender | N/A | AlertDavao |
| Delivery | Console | SMS/Supabase/Twilio |

## Flow Diagrams

### User Registration Flow
```
User Registration
    ↓
Submit form (email, phone, password, captcha)
    ↓
Send OTP to phone → SMS sent via Supabase/Twilio
    ↓
User receives SMS: "Your verification code is 123456..."
    ↓
User enters OTP in modal
    ↓
OTP verified → Account created
    ↓
Redirected to login
```

### User Login Flow
```
User Login
    ↓
Enter email & password
    ↓
Authenticate directly
    ↓
✅ Logged in (NO OTP)
```

## SMS Message

```
Your verification code is {{OTP}}. It is valid for 5 minutes.
Do not share this code with anyone for your security.
```

**Sender:** AlertDavao

## Key Files

### UserSide
- `services/supabaseOtp.ts` - OTP service functions
- `backends/handleOtp.js` - OTP backend logic
- `app/register.tsx` - Registration with OTP modal

### AdminSide
- `app/Http/Controllers/OtpController.php` - OTP logic
- `app/Http/Controllers/AuthController.php` - Authentication (no OTP for login)

## API Reference

### Send OTP
```bash
POST /api/send-otp
{
  "phone": "+639123456789",
  "purpose": "register"
}
```

### Verify OTP
```bash
POST /api/verify-otp
{
  "phone": "+639123456789",
  "code": "123456",
  "purpose": "register"
}
```

## Configuration

### Supabase (Primary SMS)
```env
EXPO_PUBLIC_SUPABASE_URL=https://your-project.supabase.co
EXPO_PUBLIC_SUPABASE_ANON_KEY=your-key
```

### Twilio (Fallback)
```env
TWILIO_SID=sid
TWILIO_TOKEN=token
TWILIO_FROM=phone-number
```

## Database

### Tables
- `otp_codes` - Stores hashed OTPs with 5-min expiry
- `verified_phones` - Tracks verified phone numbers

### Cleanup
```sql
DELETE FROM otp_codes WHERE expires_at < NOW();
```

## Debugging

### Development Mode
- OTP printed to console/logs
- `debugOtp` in API response

### Logging
```
UserSide: Server console output
AdminSide: Laravel logs (storage/logs/)
```

## Status

✅ **Ready for Production**

- Both UserSide and AdminSide updated
- SMS providers configured
- Error handling complete
- Logging in place
- Documentation complete
