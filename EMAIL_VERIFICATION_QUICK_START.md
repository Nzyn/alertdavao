# Email Verification - Quick Start Guide

## ✅ What's Been Implemented

The system now requires email verification before users can login. When users register, they receive:

**Pop-up Message:**
> **Verification Email Sent**
> 
> If the email you provided exists, a verification link has been sent to your inbox. Please check your email and click the confirmation link to activate your account before logging in.
> 
> The verification link will expire in 24 hours.

## 🚀 Setup Instructions (5 Minutes)

### Step 1: Install Dependencies

```bash
cd UserSide/backends
npm install nodemailer
```

### Step 2: Create Database Table

**Option A - Using Batch File (Easiest):**
```bash
# From project root
SETUP_EMAIL_VERIFICATION.bat
```

**Option B - Manual SQL:**
```bash
mysql -u root alertdavao < UserSide/backends/migrations/create_pending_users_table.sql
```

### Step 3: Configure Email

Create `.env` file in `UserSide/backends/.env`:

```env
EMAIL_USER=your-email@gmail.com
EMAIL_PASSWORD=your-app-password
```

**For Gmail:**
1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable 2-Factor Authentication
3. Generate App Password: Security → App Passwords → Mail
4. Use that password in `.env`

### Step 4: Restart Backend

```bash
cd UserSide/backends
npm start
```

## 🧪 Testing

### 1. Register New User

- Open UserSide app
- Go to Register
- Fill in details with **real email**
- Click Register

### 2. Check Message

You should see:
```
Verification Email Sent

If the email you provided exists, a verification link has been sent to your inbox...
```

### 3. Check Your Email

Look for email: **"AlertDavao - Verify Your Email Address"**

Click the **"Verify Email Address"** button

### 4. See Success Page

Browser opens showing:
```
✓ Email Verified Successfully!

Your AlertDavao account has been activated.
You can now log in to the AlertDavao app.
```

### 5. Try Login

- Go back to app
- Login with your credentials
- Should work! ✅

## 📧 Email Template

Users receive this professional email:

```
Subject: AlertDavao - Verify Your Email Address

Hello [Firstname],

Thank you for registering with AlertDavao. Please verify your email 
address to complete your registration.

[Verify Email Address Button]

If the button doesn't work, copy and paste this link:
http://localhost:3000/api/verify-email?token=abc123...

This link will expire in 24 hours.

If you didn't create an account with AlertDavao, you can safely ignore 
this email.
```

## 🔧 Verification Flow

```
User Registers
    ↓
Data Saved to pending_users Table
    ↓
Verification Email Sent
    ↓
User Sees: "If the email exists, check your inbox..."
    ↓
User Clicks Email Link
    ↓
Token Verified
    ↓
User Moved to users Table
    ↓
Success Page Shown
    ↓
User Can Now Login ✅
```

## ❓ Troubleshooting

### Email Not Sending?

**Check:**
1. `.env` file exists in `UserSide/backends/`
2. Email credentials are correct
3. Gmail App Password (not regular password)
4. 2FA is enabled on Gmail

**Solution:**
```bash
# Check .env file
cat UserSide/backends/.env

# Should show:
EMAIL_USER=your-email@gmail.com
EMAIL_PASSWORD=16-character-app-password
```

### Token Doesn't Work?

**Check database:**
```sql
-- Should be in pending_users
SELECT * FROM pending_users WHERE email = 'test@example.com';

-- Check expiration
SELECT email, expires_at, NOW() FROM pending_users;
```

**Token expires after 24 hours!**

### User Still Can't Login?

**Check if verified:**
```sql
-- Should be in users table after verification
SELECT id, email, created_at FROM users WHERE email = 'test@example.com';

-- Should NOT be in pending_users anymore
SELECT * FROM pending_users WHERE email = 'test@example.com';
```

## 🎯 What Happens Without Verification?

- User registers → Gets success message
- User tries to login → **FAILS** ❌
- Email still in `pending_users` table (not `users`)
- Must click verification link first

## 🔒 Security Features

✅ **Prevents fake accounts** - Must verify real email
✅ **24-hour expiration** - Links don't work forever  
✅ **One-time use** - Can't reuse verification link
✅ **Secure tokens** - Cryptographically random
✅ **Email privacy** - Doesn't reveal if email exists

## 📝 Files Modified

### New Files Created:
- ✅ `UserSide/backends/handleEmailVerification.js` - Verification logic
- ✅ `UserSide/backends/migrations/create_pending_users_table.sql` - Database
- ✅ `EMAIL_VERIFICATION_SETUP_GUIDE.md` - Full documentation
- ✅ `SETUP_EMAIL_VERIFICATION.bat` - Quick setup script

### Modified Files:
- ✅ `UserSide/backends/server.js` - Added routes
- ✅ `UserSide/app/register.tsx` - Updated message
- ✅ `UserSide/backends/package.json` - Added nodemailer

### New Routes:
- ✅ `POST /register` - With email verification
- ✅ `GET /api/verify-email` - Verify token
- ✅ `POST /register-direct` - Legacy (no verification)

## 🎉 You're Done!

Users must now verify their email before they can login to AlertDavao!

For detailed information, see `EMAIL_VERIFICATION_SETUP_GUIDE.md`
