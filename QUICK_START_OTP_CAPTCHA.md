# Quick Start Guide - OTP & Captcha Setup

## ✅ Configuration Complete!

Your Twilio and reCAPTCHA credentials have been configured.

### Twilio Configuration
- **Account SID**: ACc5612e755b74c6452e097c7696eb814e
- **Auth Token**: ✓ Configured
- **From Number**: +639054057984

### reCAPTCHA Configuration
- **Site Key**: 6Ld87BYsAAAAAK5Fu2BfDrKfWk0EoDt23yuyF2Zr
- **Base URL**: http://localhost

---

## 🚀 How to Test

### 1. Start the Backend Server
```bash
cd UserSide/backends
node server.js
```

### 2. Start the React Native App
```bash
cd UserSide
npm start
```

Press `a` for Android or `i` for iOS

### 3. Test Registration Flow

1. Open the app and navigate to **Register**
2. Fill in your details:
   - First Name
   - Last Name
   - Email
   - **Phone Number**: Enter your Philippine number (e.g., +639054057984)
   - Password
   - Confirm Password
3. Check the **Terms & Conditions** checkbox
4. Complete the **reCAPTCHA** (scroll down to see it)
5. Click **Register**
6. **OTP will be sent via SMS to your phone** (+639054057984)
7. Enter the 6-digit OTP code in the modal
8. Click **Verify & Register**

### 4. Test Login Flow

1. Enter your email and password
2. Click **Login**
3. **OTP will be sent via SMS to your registered phone**
4. Enter the 6-digit OTP code
5. Click **Verify OTP**
6. You'll be logged in!

---

## 📱 Important Notes

### Twilio Phone Number
The `TWILIO_FROM` number (+639054057984) should be:
- **Your Twilio phone number** (not your personal phone)
- Get it from: https://console.twilio.com/us1/develop/phone-numbers/manage/incoming

If you haven't purchased a Twilio number yet:
1. Go to https://console.twilio.com/
2. Navigate to **Phone Numbers** → **Buy a number**
3. Search for a Philippines number (+63)
4. Purchase it (trial accounts get some free credit)
5. Update `TWILIO_FROM` in `UserSide/backends/.env` with the purchased number

### Testing with Trial Account
If using a Twilio trial account:
- You can only send SMS to **verified phone numbers**
- Add your phone (+639054057984) as a verified number at:
  https://console.twilio.com/us1/develop/phone-numbers/manage/verified

---

## 🐛 Troubleshooting

### OTP Not Received?
1. Check server console for OTP (it will be logged if SMS fails)
2. Verify Twilio credentials are correct
3. Ensure your phone is verified in Twilio (for trial accounts)
4. Check Twilio logs: https://console.twilio.com/us1/monitor/logs/sms

### reCAPTCHA Not Loading?
1. Ensure you're connected to the internet
2. Check that site key is correct
3. Try restarting the app with cache clear: `npm start -- --reset-cache`

### Phone Input Not Working?
1. Restart Metro bundler
2. Clear cache: `npm start -- --reset-cache`
3. Reinstall dependencies: `npm install`

---

## 📝 Next Steps

1. **Test the complete flow** on your device
2. **Update Twilio number** with your purchased Twilio number (if different)
3. **Set up Gmail credentials** for email verification in `.env` (optional)
4. **Deploy to production** by setting `NODE_ENV=production` when ready

---

## 🔐 Security Reminders

- Never commit `.env` files to Git (they're in `.gitignore`)
- Keep your Twilio Auth Token secret
- Rotate credentials periodically
- Monitor Twilio usage to avoid unexpected charges

---

**Ready to test!** Start both backend and frontend servers and try registering a new account. 🎉
