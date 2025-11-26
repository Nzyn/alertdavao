# Supabase OTP Implementation Guide

## Overview

This document describes the OTP (One-Time Password) implementation for AlertDavao using Supabase as the SMS provider. The OTP is now **only used during sign-up** to verify phone numbers before account creation.

## Changes Made

### 1. OTP Flow Changes

**Before:**
- OTP was required for both sign-in and sign-up
- OTP appeared in console (development mode)

**After:**
- OTP is **only required during sign-up** (registration)
- OTP is sent via Supabase SMS to the user's actual phone number
- Sign-in no longer requires OTP verification

### 2. Files Modified

#### UserSide (React Native)

| File | Changes |
|------|---------|
| `app/(tabs)/login.tsx` | Removed OTP modal and flow - direct login after password verification |
| `app/register.tsx` | Uses Supabase OTP with resend functionality (60-second cooldown) |
| `services/supabaseOtp.ts` | Supabase OTP send/verify service |
| `config/supabase.ts` | Supabase client configuration |

#### AdminSide (Laravel)

| File | Changes |
|------|---------|
| `resources/views/auth/login.blade.php` | Removed OTP flow - direct login after password verification |
| `resources/views/auth/register.blade.php` | Uses Supabase OTP with resend functionality (60-second cooldown) |

## Supabase Configuration

### 1. Environment Variables

Add these to your `.env` file:

**UserSide (.env):**
```
EXPO_PUBLIC_SUPABASE_URL=https://your-project.supabase.co
EXPO_PUBLIC_SUPABASE_ANON_KEY=your-anon-key
```

**AdminSide (.env):**
```
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
```

### 2. Supabase Dashboard Configuration

1. Go to your Supabase Dashboard: https://supabase.com/dashboard
2. Navigate to **Authentication** > **Providers** > **Phone**
3. Enable Phone Auth
4. Configure SMS Provider (Twilio, MessageBird, Vonage, etc.)

### 3. SMS Template Configuration

Configure the SMS message template in Supabase:

**Sender ID:** `AlertDavao`

**SMS Template:**
```
Your verification code is {{.Token}}. It is valid for 5 minutes. Do not share this code with anyone for your security.
```

## OTP Flow

### Sign-Up Flow

```
1. User fills registration form
2. User clicks "Register"
3. App validates form fields
4. App sends OTP via Supabase to user's phone
5. OTP modal appears with:
   - Phone number display
   - 6-digit code input (auto-submits on complete)
   - Resend button (60-second cooldown)
   - Cancel button
6. User enters OTP code
7. App verifies OTP with Supabase
8. If valid, account is created
9. User redirected to login
```

### Sign-In Flow

```
1. User enters email/password
2. User solves captcha
3. User clicks "Login"
4. App verifies credentials with backend
5. If valid, user is logged in directly
6. No OTP required!
```

## OTP Resend Feature

- Users can request a new OTP after 60 seconds
- Countdown timer shows remaining time
- "Resend OTP" button appears when cooldown expires
- Each resend resets the 60-second countdown

## Fallback Behavior

If Supabase is not configured:
- UserSide: Will display error message
- AdminSide: Falls back to backend OTP (console logging in development)

## Testing

### Development Testing

1. Configure Supabase with a test phone number provider
2. Or use Supabase's built-in test OTP feature
3. Check Supabase Dashboard > Authentication > Users to see sent OTPs

### Production Checklist

- [ ] Supabase project created
- [ ] Phone auth enabled
- [ ] SMS provider configured (Twilio, etc.)
- [ ] SMS template customized
- [ ] Environment variables set
- [ ] Test with real phone number
- [ ] Verify OTP expires after 5 minutes

## Code Reference

### UserSide - sendSupabaseOtp()

```typescript
const { data, error } = await supabase.auth.signInWithOtp({
  phone: phone,  // Format: +639XXXXXXXXX
  options: {
    channel: 'sms',
  },
});
```

### UserSide - verifySupabaseOtp()

```typescript
const { data, error } = await supabase.auth.verifyOtp({
  phone: phone,
  token: code,
  type: 'sms',
});
```

### AdminSide - Supabase Client (Browser)

```javascript
const supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
  auth: {
    autoRefreshToken: true,
    persistSession: false,
    detectSessionInUrl: false,
  }
});
```

## Security Notes

1. OTP is only used for phone verification during registration
2. OTP codes expire after 5 minutes
3. Sign-in uses password authentication only (more secure for frequent logins)
4. Supabase session is immediately cleared after OTP verification (we only use it for phone verification, not authentication)

## Troubleshooting

### OTP Not Sending

1. Check Supabase Dashboard > Authentication > Logs
2. Verify SMS provider configuration
3. Check phone number format (+639XXXXXXXXX)
4. Check SMS provider balance/quota

### OTP Verification Failing

1. Ensure correct phone number format
2. Check OTP hasn't expired (5 minutes)
3. Check for typos in the 6-digit code
4. Try requesting a new OTP

### Fallback Not Working (AdminSide)

1. Check if `SUPABASE_URL` and `SUPABASE_ANON_KEY` are set
2. If not set, backend fallback will be used
3. Check Laravel logs for OTP codes in development
