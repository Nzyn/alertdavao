# Email Verification Setup Guide

## Overview
The email verification system requires users to verify their email address before they can login. This adds an extra layer of security to prevent fake accounts.

## Database Setup

### 1. Create `pending_users` Table

Run this SQL in your MySQL database:

```sql
-- UserSide/backends/migrations/create_pending_users_table.sql
CREATE TABLE IF NOT EXISTS pending_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    verification_token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_verification_token (verification_token),
    INDEX idx_email (email),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Install nodemailer Package

```bash
cd UserSide/backends
npm install nodemailer
```

## Email Configuration

### For UserSide (Node.js Backend)

1. **Create `.env` file** in `UserSide/backends/` directory:

```env
EMAIL_USER=your-email@gmail.com
EMAIL_PASSWORD=your-app-specific-password
```

2. **Generate Gmail App Password** (if using Gmail):
   - Go to Google Account Settings → Security
   - Enable 2-Factor Authentication
   - Generate an "App Password" for "Mail"
   - Use this password in `.env` file

3. **Update `handleEmailVerification.js`** with your email service details (already created)

### For AdminSide (Laravel)

1. **Update `.env` file** in `AdminSide/admin/`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@alertdavao.com"
MAIL_FROM_NAME="AlertDavao"
```

2. For Gmail, use the same App Password as above.

## How It Works

### Registration Flow

1. **User Submits Registration**
   - UserSide: `POST /register`
   - AdminSide: `POST /register`

2. **System Validates Data**
   - Email format (must have @ and domain)
   - Password strength (min 8 chars, letter, number, symbol)

3. **System Creates Pending User**
   - Data saved to `pending_users` table
   - Generates unique verification token
   - Token expires in 24 hours

4. **System Sends Verification Email**
   - Email contains clickable link: `http://localhost:3000/api/verify-email?token=...`
   - Beautiful HTML template with instructions

5. **User Receives Email**
   - Shows message: "If the email you provided exists, a verification link has been sent..."
   - This prevents email enumeration attacks

6. **User Clicks Verification Link**
   - System verifies token
   - Moves user from `pending_users` to `users` table
   - Shows success page
   - User can now login

### Verification Endpoint

**URL**: `GET /api/verify-email?token={token}`

**Success**: Shows HTML page with "Email Verified Successfully!"

**Failure**: Shows "Link Expired" or "Invalid Link" message

## User Messages

### After Registration Submit
```
Title: "Verification Email Sent"

Message: "If the email you provided exists, a verification link has been sent to your inbox. 
Please check your email and click the confirmation link to activate your account before logging in.

The verification link will expire in 24 hours."
```

### In Verification Email
```
Subject: AlertDavao - Verify Your Email Address

Hello {firstname},

Thank you for registering with AlertDavao. Please verify your email address to complete your registration.

[Verify Email Address Button]

If the button doesn't work, copy and paste this link:
http://localhost:3000/api/verify-email?token={token}

This link will expire in 24 hours.

If you didn't create an account with AlertDavao, you can safely ignore this email.
```

### After Clicking Verification Link
```
✓ Email Verified Successfully!

Your AlertDavao account has been activated.
You can now log in to the AlertDavao app with your credentials.

You can close this window now.
```

## Testing

### 1. Test Registration

```bash
# UserSide
curl -X POST http://localhost:3000/register \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Test",
    "lastname": "User",
    "email": "test@example.com",
    "contact": "09123456789",
    "password": "Test123!"
  }'
```

### 2. Check Database

```sql
-- Should see pending user
SELECT * FROM pending_users WHERE email = 'test@example.com';

-- Should NOT see in users table yet
SELECT * FROM users WHERE email = 'test@example.com';
```

### 3. Check Email

- Open your email inbox
- Look for "AlertDavao - Verify Your Email Address"
- Click the verification button

### 4. Verify Token Worked

```sql
-- Should now be in users table
SELECT * FROM users WHERE email = 'test@example.com';

-- Should be removed from pending_users
SELECT * FROM pending_users WHERE email = 'test@example.com';
```

## Maintenance

### Clean Up Expired Tokens

Run this periodically (e.g., daily cron job):

```sql
DELETE FROM pending_users WHERE expires_at < NOW();
```

### Check Pending Verifications

```sql
SELECT 
    email, 
    TIMESTAMPDIFF(HOUR, created_at, NOW()) as hours_waiting,
    expires_at
FROM pending_users 
ORDER BY created_at DESC;
```

## Troubleshooting

### Email Not Sending

**Check Logs**:
```bash
# In UserSide/backends terminal
# Look for "Error sending verification email"
```

**Common Issues**:
- Wrong Gmail App Password
- 2FA not enabled on Gmail
- Firewall blocking SMTP port
- Email service limits reached

**Solution**:
1. Verify `.env` credentials
2. Test with Mailtrap or MailHog for development
3. Check Gmail "Less secure apps" setting

### Token Not Working

**Check Database**:
```sql
SELECT * FROM pending_users WHERE verification_token = 'your-token-here';
```

**Common Issues**:
- Token expired (> 24 hours)
- User already verified
- Token not found in database

### User Can't Login After Verification

**Check users table**:
```sql
SELECT id, email, created_at FROM users WHERE email = 'user@example.com';
```

**If missing**: Verification didn't complete properly. Check error logs.

## Security Features

✅ **Email Enumeration Protection**: Always shows same message whether email exists or not

✅ **Token Expiration**: Links expire after 24 hours

✅ **Unique Tokens**: Cryptographically secure random tokens

✅ **One-Time Use**: Token deleted after successful verification

✅ **Password Hashing**: Passwords hashed before storing in pending_users

✅ **HTTPS Ready**: Works with SSL/TLS encryption

## Production Deployment

Before going live:

1. **Change Email Service**: Use professional service (SendGrid, AWS SES, Mailgun)
2. **Update URLs**: Change `http://localhost:3000` to your production domain
3. **Enable HTTPS**: Use SSL certificates
4. **Set Up Cron**: Auto-delete expired pending users
5. **Monitor Emails**: Track delivery rates and bounces
6. **Update Messages**: Customize email templates with your branding

## Files Created/Modified

### New Files
- `UserSide/backends/handleEmailVerification.js` - Email verification logic
- `UserSide/backends/migrations/create_pending_users_table.sql` - Database schema

### Modified Files
- `UserSide/backends/server.js` - Added verification routes
- `UserSide/app/register.tsx` - Updated success message
- `AdminSide/admin/app/Http/Controllers/AuthController.php` - Added email validation

### Routes Added
- `POST /register` - Now uses `handleRegisterWithVerification`
- `GET /api/verify-email` - New verification endpoint
- `POST /register-direct` - Legacy direct registration (backup)

