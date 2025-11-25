# OTP, Phone Number Validation & Captcha Implementation Guide

## Overview

This guide documents the complete implementation of **OTP verification**, **phone number validation**, and **reCAPTCHA** security for the AlertDavao 2.0 UserSide application.

## Features Implemented

### 1. Phone Number Validation
- **Package**: `react-native-phone-number-input`
- **Purpose**: Ensures users enter valid mobile numbers with proper international formatting
- **Location**: Registration screen
- **Validation**: Real-time phone number format checking with country code support (defaults to Philippines)

### 2. OTP (One-Time Password) Verification
- **Package**: `react-native-otp-entry` (installed, but used simple TextInput for flexibility)
- **Purpose**: Verify phone number ownership and add login security
- **Implementation**:
  - **Registration Flow**: Users must verify their phone number via OTP before account creation
  - **Login Flow**: After password verification, users must enter OTP sent to their phone

### 3. reCAPTCHA Security
- **Package**: `react-native-recaptcha-that-works` + `react-native-webview`
- **Purpose**: Prevent automated bot registrations
- **Location**: Registration screen (below Terms & Conditions)

---

## Backend Implementation

### Database Tables

Two new tables are automatically created:

#### `otp_codes`
```sql
CREATE TABLE IF NOT EXISTS otp_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phone VARCHAR(64) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  purpose VARCHAR(64) NOT NULL,
  user_id INT DEFAULT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT NOW()
);
```

#### `verified_phones`
```sql
CREATE TABLE IF NOT EXISTS verified_phones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phone VARCHAR(64) NOT NULL UNIQUE,
  verified TINYINT(1) DEFAULT 1,
  verified_at DATETIME DEFAULT NOW()
);
```

### API Endpoints

#### POST `/api/send-otp`
**Request Body**:
```json
{
  "phone": "+639123456789",
  "purpose": "register",  // or "login"
  "userId": 123           // optional, for login
}
```

**Response**:
```json
{
  "success": true,
  "sent": true,
  "debugOtp": "123456"    // only in development (NODE_ENV !== 'production')
}
```

#### POST `/api/verify-otp`
**Request Body**:
```json
{
  "phone": "+639123456789",
  "purpose": "register",  // or "login"
  "code": "123456"
}
```

**Response (register)**:
```json
{
  "success": true,
  "message": "OTP verified"
}
```

**Response (login)**:
```json
{
  "success": true,
  "message": "OTP verified",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "firstname": "John",
    "lastname": "Doe",
    "role": "user",
    "contact": "+639123456789"
  }
}
```

### SMS Provider Configuration

The backend supports **Twilio** for SMS delivery. Configure these environment variables:

```bash
TWILIO_SID=your_twilio_account_sid
TWILIO_TOKEN=your_twilio_auth_token
TWILIO_FROM=+1234567890  # Your Twilio phone number
```

**Development Mode**: If Twilio credentials are not configured, OTPs are logged to the server console for testing.

---

## Frontend Implementation

### Registration Flow

1. User fills in registration form with:
   - First Name
   - Last Name
   - Email
   - **Phone Number** (validated using PhoneInput component)
   - Password
   - Confirm Password

2. User checks **Terms & Conditions** checkbox

3. User completes **reCAPTCHA** (required)

4. User clicks **Register** button

5. System validates phone number format

6. System sends OTP to phone number

7. **OTP Modal appears** with 6-digit input field

8. User enters OTP code

9. System verifies OTP

10. If OTP is correct:
    - Phone is marked as verified
    - Registration proceeds
    - Verification email is sent
    - User is redirected to login

### Login Flow

1. User enters email and password

2. User clicks **Login** button

3. System verifies password

4. If password is correct, system sends OTP to user's phone

5. **OTP Modal appears**

6. User enters OTP code

7. System verifies OTP

8. If OTP is correct:
    - User is logged in
    - Session is created
    - User is redirected to main app

### Error Handling

#### Invalid Phone Number
- **Alert**: "Please enter a valid mobile number"
- **Action**: User must correct phone number

#### Missing reCAPTCHA
- **Alert**: "Please complete the captcha below"
- **Action**: User must complete reCAPTCHA

#### OTP Send Failure
- **Alert**: "Failed to send OTP"
- **Action**: User can retry registration/login

#### Invalid OTP
- **Alert**: "Incorrect OTP"
- **Action**: User must enter correct code (OTP remains valid for 10 minutes)

#### Expired OTP
- **Alert**: "OTP expired"
- **Action**: User must restart registration/login to get new OTP

#### Phone Not Verified (Registration)
- **Alert**: "Phone number not verified. Please complete OTP verification."
- **Action**: User must complete OTP flow before account creation

---

## Environment Variables

### Backend (UserSide/backends)

Create a `.env` file:

```bash
# Twilio SMS Configuration (Optional - for production)
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token_here
TWILIO_FROM=+1234567890

# Environment
NODE_ENV=development  # or 'production'
```

### Frontend (UserSide)

Add to your app config or `.env`:

```bash
# reCAPTCHA Configuration
RECAPTCHA_SITE_KEY=your_recaptcha_site_key_here
RECAPTCHA_BASE_URL=http://localhost  # or your production URL
```

---

## Files Modified

### Backend Files
- **`UserSide/backends/handleOtp.js`** (NEW) - OTP generation, sending, and verification
- **`UserSide/backends/server.js`** - Added OTP endpoints
- **`UserSide/backends/handleEmailVerification.js`** - Added phone verification check
- **`UserSide/backends/handleLogin.js`** - Modified to require OTP after password verification

### Frontend Files
- **`UserSide/app/register.tsx`** - Added PhoneInput, reCAPTCHA, and OTP modal
- **`UserSide/app/(tabs)/register.tsx`** - Added PhoneInput, reCAPTCHA, and OTP modal
- **`UserSide/app/(tabs)/login.tsx`** - Added OTP modal for login verification

---

## Testing

### Development Testing (without Twilio)

1. Start backend server:
   ```bash
   cd UserSide/backends
   node server.js
   ```

2. Start React Native app:
   ```bash
   cd UserSide
   npm start
   ```

3. When OTP is sent, check server console for debug OTP:
   ```
   No SMS provider configured. OTP for +639123456789 is logged to server console.
   Your AlertDavao verification code is: 123456
   ```

4. Enter the OTP shown in console into the app

### Production Testing (with Twilio)

1. Set up Twilio account at https://www.twilio.com

2. Get your credentials:
   - Account SID
   - Auth Token
   - Twilio phone number

3. Configure `.env` file with Twilio credentials

4. Set `NODE_ENV=production`

5. Test registration and login - real SMS will be sent

---

## Security Considerations

### OTP Security
- OTPs are **hashed** using bcrypt before storage
- OTPs expire after **10 minutes**
- Used OTPs are **deleted** after verification
- Rate limiting should be added to prevent OTP spam (TODO)

### Phone Verification
- Phone numbers must be verified before account creation
- Verified phones are stored in `verified_phones` table
- Prevents fake accounts with invalid phone numbers

### reCAPTCHA
- Prevents automated bot registrations
- Must be completed before OTP is sent
- Token is verified server-side (TODO: add server verification)

---

## Troubleshooting

### Phone Input Not Showing
- Ensure `react-native-phone-number-input` is installed
- Restart Metro bundler: `npm start -- --reset-cache`

### OTP Modal Not Appearing
- Check browser/app console for errors
- Ensure `Modal` is imported from `react-native`

### OTP Not Sending
- **Development**: Check server console for logged OTP
- **Production**: Verify Twilio credentials and phone number format

### reCAPTCHA Not Loading
- Check `RECAPTCHA_SITE_KEY` and `RECAPTCHA_BASE_URL` are set
- Ensure WebView is supported on your platform
- Get reCAPTCHA keys from https://www.google.com/recaptcha/admin

### Database Errors
- Tables are auto-created on first OTP request
- Ensure MySQL is running and credentials in `db.js` are correct

---

## Future Improvements

1. **Rate Limiting**: Prevent OTP spam (max 3 OTPs per phone per hour)
2. **Server-side reCAPTCHA Verification**: Verify reCAPTCHA token on backend
3. **OTP Retry Logic**: Allow users to request new OTP if not received
4. **SMS Template Customization**: Customize OTP message text
5. **Multi-Factor Authentication (MFA)**: Optional MFA for enhanced security
6. **Phone Number Change Flow**: Allow users to update verified phone number

---

## Support

For issues or questions:
1. Check server console for error messages
2. Verify all environment variables are set
3. Ensure all npm packages are installed
4. Check database connection and table creation

---

**Last Updated**: November 24, 2025  
**Version**: 1.0  
**Author**: GitHub Copilot
