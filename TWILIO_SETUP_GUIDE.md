# Twilio SMS Setup Guide for AlertDavao

## Quick Setup (5 minutes)

### Step 1: Get Twilio Credentials (FREE)

1. Go to https://www.twilio.com/try-twilio
2. Sign up for a **FREE trial account**
3. Verify your email and phone number
4. You'll get **$15 FREE credit** (~500 SMS messages)

### Step 2: Get Your Credentials

After signing in to Twilio Console (https://console.twilio.com/):

1. **Account SID**: Found on dashboard home page
2. **Auth Token**: Click "Show" next to Auth Token
3. **Phone Number**: 
   - Go to "Phone Numbers" → "Manage" → "Buy a number"
   - For trial, get a FREE number (Philippines +63 recommended)

### Step 3: Configure Backend

Add these to `UserSide/backends/.env`:

```env
TWILIO_SID=your_account_sid_here
TWILIO_TOKEN=your_auth_token_here
TWILIO_FROM=+1234567890
```

### Step 4: Verify Phone Numbers (Trial Only)

⚠️ **Important for Trial Accounts**: Twilio trial can only send SMS to **verified phone numbers**.

To verify a number:
1. Go to Twilio Console → Phone Numbers → Verified Caller IDs
2. Click "Add a new Caller ID"
3. Enter the phone number and verify via SMS

### Step 5: Test

Restart your backend server:
```bash
cd UserSide/backends
node server.js
```

## Alternative: Free SMS APIs

If you don't want to use Twilio, consider:

1. **Semaphore** (Philippines) - https://semaphore.co/
   - Good for Philippine numbers
   - Free trial available

2. **MSG91** - https://msg91.com/
   - Free tier: 100 SMS/day
   - Good international coverage

3. **TextLocal** - https://www.textlocal.com/
   - Free credits on signup

## Current Implementation

The backend (`handleOtp.js`) already supports:
- ✅ Twilio SMS sending
- ✅ Console logging for development (when no SMS provider)
- ✅ Debug OTP in API response (for testing)

## Production Notes

For production:
1. Upgrade Twilio account (no trial restrictions)
2. Set `NODE_ENV=production` in `.env`
3. Remove `debugOtp` from responses
4. Use environment variables, never hardcode credentials
