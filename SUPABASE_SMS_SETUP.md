# Supabase SMS Configuration Guide

## Overview
Configure Supabase Phone Authentication to send SMS OTP codes using Twilio.

## Prerequisites
- Supabase project: https://dveegaqomrpocyywllwu.supabase.co
- Twilio account with verified phone number
- Credentials from user screenshot

## Configuration Steps

### 1. Access Supabase Dashboard
1. Go to: https://supabase.com/dashboard/project/dveegaqomrpocyywllwu
2. Navigate to **Authentication** → **Providers**
3. Click on **Phone** provider

### 2. Enable Phone Authentication
- ✅ Enable Phone provider should be **ON**

### 3. Configure Twilio SMS Provider

Enter the following credentials (from user's screenshot):

```
SMS Provider: Twilio

Twilio Account SID: AC5612e755b74c6452e097c7696eb814e

Twilio Auth Token: [Enter your Twilio Auth Token from dashboard]
(You can find this at: https://console.twilio.com/)

Twilio Message Service SID: MGfc44cfbee3b5239a4c3ed427f3a39151

Twilio Phone Number: +19294608714
```

### 4. OTP Settings

Configure these settings as shown in screenshot:

```
SMS OTP Expiry: 640 seconds (10 minutes 40 seconds)
SMS OTP Length: 6 digits
```

### 5. SMS Message Template

Use this template for consistency:

```
Your verification code is {{ .Code }}. It is valid for 5 minutes. Do not share this code with anyone for your security.
```

### 6. Save Configuration

Click **Save** at the bottom of the Phone provider settings.

## Testing SMS OTP

### 1. Test from UserSide App

1. Open the AlertDavao app
2. Go to Sign Up screen
3. Enter phone number (e.g., +639123456789)
4. Click "Send OTP"
5. Check your phone for SMS

### 2. Check Supabase Logs

1. Go to **Authentication** → **Users**
2. Look for recent phone authentication attempts
3. Check for any error messages

### 3. Check Twilio Logs

1. Go to: https://console.twilio.com/us1/monitor/logs/sms
2. Verify SMS was sent successfully
3. Check delivery status

## Troubleshooting

### SMS Not Received

1. **Check Twilio Balance**: Ensure you have credits in Twilio account
2. **Verify Phone Number**: Must be in international format (+639...)
3. **Check Twilio Logs**: Look for failed deliveries
4. **Verify Credentials**: Ensure all Twilio credentials are correct
5. **Check Supabase Logs**: Look for authentication errors

### Common Issues

**Error: "Invalid credentials"**
- Verify Twilio Account SID and Auth Token are correct
- Check if Auth Token has expired (regenerate if needed)

**Error: "Message Service not found"**
- Verify Message Service SID (MGfc44cfbee3b5239a4c3ed427f3a39151)
- Ensure Message Service is active in Twilio

**Error: "Phone number not verified"**
- For Twilio trial accounts, you can only send to verified numbers
- Upgrade to paid account or verify recipient phone in Twilio console

## Current Configuration Status

✅ **Frontend (.env)**
- Supabase URL: Configured
- Supabase Anon Key: Configured

⏳ **Supabase Dashboard** (Needs Update)
- Enable Phone provider: ✅ Already enabled (from screenshot)
- Configure Twilio: ⚠️ **Needs Auth Token entry**
- Save settings: ⏳ Pending

## Integration Flow

1. User enters phone number in app
2. App calls `sendSupabaseOtp(phone)` from `supabaseOtp.ts`
3. Supabase receives request
4. Supabase sends SMS via Twilio Message Service
5. User receives 6-digit OTP code
6. User enters code in app
7. App calls `verifySupabaseOtp(phone, code)`
8. Supabase verifies the code
9. Phone number marked as verified

## Files Modified

- `UserSide/.env` - Already has Supabase credentials
- `UserSide/config/supabase.ts` - Supabase client configuration
- `UserSide/services/supabaseOtp.ts` - OTP send/verify functions

## Next Steps

1. **Enter Twilio Auth Token** in Supabase Dashboard
   - The user has it (shown as •••• in screenshot)
   - Get it from: https://console.twilio.com/
   
2. **Save Phone Provider Settings** in Supabase

3. **Test SMS Delivery**
   - Register new account with real phone number
   - Verify OTP is received via SMS

4. **Monitor First Week**
   - Check Twilio usage and costs
   - Monitor delivery rates
   - Review user feedback

## Support Resources

- Supabase Phone Auth Docs: https://supabase.com/docs/guides/auth/phone-login
- Twilio SMS Docs: https://www.twilio.com/docs/sms
- Supabase Project: https://supabase.com/dashboard/project/dveegaqomrpocyywllwu
- Twilio Console: https://console.twilio.com/
