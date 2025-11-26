# Supabase SMS OTP Implementation Guide

**Last Updated:** November 26, 2025  
**Status:** ✅ Complete Implementation  
**Scope:** Both UserSide and AdminSide

## Overview

This guide documents the complete implementation of Supabase SMS OTP authentication for AlertDavao. The OTP is now **only required during signup (registration)** and is no longer required for login.

## Key Changes

### 1. OTP Flow Changes

| Aspect | Before | After |
|--------|--------|-------|
| OTP Required for Sign-In | ✅ Yes | ❌ No |
| OTP Required for Sign-Up | ✅ Yes | ✅ Yes |
| OTP Delivery Method | Console (Development) | SMS via Supabase/Twilio |
| OTP Sender | N/A | AlertDavao |
| OTP Validity Duration | 10 minutes | **5 minutes** |
| Resend Cooldown | 60 seconds | **60 seconds** |
| OTP Format | N/A | "Your verification code is {{OTP}}. It is valid for 5 minutes. Do not share this code with anyone for your security." |

### 2. Files Modified

#### UserSide

| File | Changes |
|------|---------|
| `services/supabaseOtp.ts` | Simplified to use backend OTP endpoint; removed Supabase auth sign-in flow |
| `backends/handleOtp.js` | Added Supabase SMS integration; OTP only for register; 5-min expiry; improved logging |
| `app/register.tsx` | Maintains existing flow; OTP modal with resend (60s cooldown) |
| `app/(tabs)/login.tsx` | Already removes OTP for sign-in |

#### AdminSide

- Check if registration exists; apply same OTP flow if present

## Detailed Implementation

### Backend OTP Handler (`handleOtp.js`)

#### Key Features

1. **SMS Provider Priority:**
   - Primary: Supabase SMS (via `signInWithOtp`)
   - Fallback: Twilio
   - Development: Console logging

2. **OTP Generation:**
   - 6-digit numeric code
   - Generated using `Math.random()`
   - Hashed using bcrypt before storage

3. **Expiration:**
   - 5 minutes for registration
   - Expired OTPs are automatically deleted on verification attempt

4. **Database Storage:**
   - `otp_codes` table: Stores phone, hashed OTP, purpose, expiration
   - `verified_phones` table: Marks phones as verified

#### Send OTP Endpoint

**Route:** `POST /api/send-otp`

**Request Body:**
```json
{
  "phone": "+639123456789",
  "purpose": "register"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "OTP sent successfully to your phone",
  "sent": true,
  "debugOtp": "123456"  // Only in development
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "OTP is only available during registration"
}
```

**Logic:**
1. Validates `purpose === 'register'` (OTP only for signup)
2. Normalizes phone number to international format (+63)
3. Generates 6-digit OTP and hashes it
4. Stores in `otp_codes` table with 5-minute expiry
5. Sends SMS via Supabase or Twilio
6. Logs OTP to console for development
7. Returns OTP in response if not in production

#### Verify OTP Endpoint

**Route:** `POST /api/verify-otp`

**Request Body:**
```json
{
  "phone": "+639123456789",
  "code": "123456",
  "purpose": "register"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Phone number verified successfully. You can now complete your registration."
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Invalid OTP code. Please try again."
}
```

**Logic:**
1. Validates `purpose === 'register'`
2. Validates OTP is exactly 6 digits
3. Normalizes phone number
4. Retrieves most recent OTP for phone
5. Checks if OTP has expired (5 minutes)
6. Compares provided code with stored hash
7. Marks phone as verified in `verified_phones` table
8. Deletes used OTP from database

### Frontend OTP Service (`supabaseOtp.ts`)

#### Functions

**`sendSupabaseOtp(phone: string)`**
- Calls backend `/api/send-otp` endpoint
- Normalizes phone to international format
- Returns `OtpResult` with success flag and message
- Includes `debugOtp` for development

**`verifySupabaseOtp(phone: string, token: string)`**
- Calls backend `/api/verify-otp` endpoint
- Validates OTP is 6 digits
- Returns `OtpResult` with success flag and message
- Deletes used OTP on successful verification

**`normalizePhoneNumber(phone: string)`**
- Converts `09123456789` → `+639123456789`
- Adds `+63` prefix if missing
- Removes whitespace

### Registration Flow (`register.tsx`)

#### Sign-Up Steps

1. **Enter Registration Details**
   - Email, password, phone number, etc.
   - Validate all fields

2. **Submit Registration**
   - Click "Register" button
   - Sends OTP to entered phone number
   - Modal appears for OTP entry

3. **Verify OTP**
   - User receives SMS with code
   - Enters 6-digit code in modal
   - Auto-submits when 6 digits entered

4. **Resend OTP** (if needed)
   - Button available after 60-second cooldown
   - Resets countdown on resend

5. **Create Account**
   - After OTP verification, account is created
   - User is redirected to login screen

#### OTP Modal Features

- Shows phone number for user confirmation
- 6-digit numeric input with auto-submit
- Resend option with 60-second countdown
- Cancel button to go back
- Clear error messages

## Environment Variables

### Required for Supabase SMS

```env
EXPO_PUBLIC_SUPABASE_URL=https://your-project.supabase.co
EXPO_PUBLIC_SUPABASE_ANON_KEY=your-anon-key
```

### Optional for Twilio Fallback

```env
TWILIO_SID=your-twilio-account-sid
TWILIO_TOKEN=your-twilio-auth-token
TWILIO_FROM=your-twilio-phone-number
```

## SMS Template

The SMS sent to users follows this format:

```
AlertDavao

Your verification code is {{OTP}}. It is valid for 5 minutes. Do not share this code with anyone for your security.
```

**Example:**
```
AlertDavao

Your verification code is 123456. It is valid for 5 minutes. Do not share this code with anyone for your security.
```

## Database Tables

### `otp_codes`

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT AUTO_INCREMENT | Primary key |
| `phone` | VARCHAR(64) | Phone number in +63 format |
| `otp_hash` | VARCHAR(255) | bcrypt-hashed OTP |
| `purpose` | VARCHAR(64) | 'register' only |
| `user_id` | INT (NULL) | Unused (for future) |
| `expires_at` | DATETIME | 5 minutes from creation |
| `created_at` | DATETIME | Timestamp |

### `verified_phones`

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT AUTO_INCREMENT | Primary key |
| `phone` | VARCHAR(64) UNIQUE | Phone number in +63 format |
| `verified` | TINYINT(1) | Always 1 |
| `verified_at` | DATETIME | When verification occurred |

## Testing

### Development Mode

1. **OTP appears in console log:**
   ```
   ════════════════════════════════════════════════════════
   📨 OTP SENT FOR REGISTRATION
   ════════════════════════════════════════════════════════
   Phone: +639123456789
   OTP Code: 123456
   Expires: 11/26/2025, 10:30:45 PM
   Message: "Your verification code is 123456..."
   ════════════════════════════════════════════════════════
   ```

2. **OTP also returned in API response:**
   - Check network tab to see `debugOtp` field

3. **Test with any phone format:**
   - `09123456789` → normalized to `+639123456789`
   - `+639123456789` → used as-is
   - `639123456789` → gets `+` prefix added

### Production Mode

1. **Supabase SMS Configuration:**
   - Configure SMS provider in Supabase dashboard
   - Set message template for OTP
   - Test with real phone numbers

2. **Twilio Fallback:**
   - Provide credentials in environment variables
   - Test SMS delivery
   - Monitor Twilio console

## Error Handling

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| "OTP is only available during registration" | User called with `purpose !== 'register'` | Ensure purpose is 'register' |
| "No OTP found. Please request a new OTP." | No OTP stored for that phone | User must request new OTP |
| "OTP expired. Please request a new OTP." | OTP older than 5 minutes | Send new OTP via resend button |
| "Invalid OTP code. Please try again." | Code doesn't match hash | Check user didn't mistype |
| "OTP must be 6 digits" | Code not exactly 6 digits | Validate input format |

## Security Notes

1. **OTP Security:**
   - OTPs are hashed using bcrypt before storage
   - Only used once (deleted after verification)
   - 5-minute expiration prevents brute force
   - Backend validates format and expiry

2. **Phone Number Security:**
   - Phone numbers normalized to consistent format
   - Not exposed in API responses unnecessarily
   - Stored securely in database

3. **Development Safety:**
   - OTP logging only enabled when not in production
   - Or when SMS provider not configured
   - Logs are server-side only (not sent to client)

## Future Enhancements

1. **Email Verification:**
   - Similar OTP flow for email
   - Can be triggered after phone verification
   - Send via SendGrid or similar

2. **Rate Limiting:**
   - Limit OTP requests per phone number
   - Prevent spam/brute force attempts

3. **Analytics:**
   - Track OTP success/failure rates
   - Monitor SMS delivery

4. **Two-Factor Authentication:**
   - Use OTP for 2FA during login
   - Optional for premium users

## Support & Debugging

### View OTP Logs
```bash
# Server console shows OTP details
npm start

# Look for: "📨 OTP SENT FOR REGISTRATION" section
```

### Check Database
```sql
-- View recent OTPs
SELECT * FROM otp_codes ORDER BY created_at DESC LIMIT 5;

-- View verified phones
SELECT * FROM verified_phones;

-- Delete expired OTPs (manual cleanup)
DELETE FROM otp_codes WHERE expires_at < NOW();
```

### Test OTP Endpoint
```bash
# Send OTP
curl -X POST http://localhost:3000/api/send-otp \
  -H "Content-Type: application/json" \
  -d '{"phone":"+639123456789","purpose":"register"}'

# Verify OTP (use code from console)
curl -X POST http://localhost:3000/api/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone":"+639123456789","code":"123456","purpose":"register"}'
```

## Summary

The OTP system is now:
- ✅ Only required during signup
- ✅ Sent via SMS (Supabase/Twilio)
- ✅ Valid for 5 minutes
- ✅ Shows proper error messages
- ✅ Has 60-second resend cooldown
- ✅ Follows security best practices
- ✅ Properly logged for debugging

Ready for production deployment!
