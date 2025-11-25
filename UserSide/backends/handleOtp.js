const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const db = require('./db');
const fetch = require('node-fetch');

// Utility: ensure tables exist
const ensureTables = async () => {
  // otp_codes table
  await db.query(`
    CREATE TABLE IF NOT EXISTS otp_codes (
      id INT AUTO_INCREMENT PRIMARY KEY,
      phone VARCHAR(64) NOT NULL,
      otp_hash VARCHAR(255) NOT NULL,
      purpose VARCHAR(64) NOT NULL,
      user_id INT DEFAULT NULL,
      expires_at DATETIME NOT NULL,
      created_at DATETIME DEFAULT NOW()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  `);

  // verified_phones table
  await db.query(`
    CREATE TABLE IF NOT EXISTS verified_phones (
      id INT AUTO_INCREMENT PRIMARY KEY,
      phone VARCHAR(64) NOT NULL UNIQUE,
      verified TINYINT(1) DEFAULT 1,
      verified_at DATETIME DEFAULT NOW()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  `);
};

const generateOtp = () => {
  return Math.floor(100000 + Math.random() * 900000).toString();
};

const sendSms = async (phone, message) => {
  // If Twilio env vars are set, try to send via Twilio
  const TWILIO_SID = process.env.TWILIO_SID;
  const TWILIO_TOKEN = process.env.TWILIO_TOKEN;
  const TWILIO_FROM = process.env.TWILIO_FROM;

  if (TWILIO_SID && TWILIO_TOKEN && TWILIO_FROM) {
    const url = `https://api.twilio.com/2010-04-01/Accounts/${TWILIO_SID}/Messages.json`;
    const body = new URLSearchParams({ To: phone, From: TWILIO_FROM, Body: message });
    const resp = await fetch(url, {
      method: 'POST',
      headers: {
        Authorization: 'Basic ' + Buffer.from(`${TWILIO_SID}:${TWILIO_TOKEN}`).toString('base64'),
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body.toString()
    });
    return resp.ok;
  }

  // No SMS provider configured: log OTP for development
  console.warn('No SMS provider configured. OTP for', phone, 'is logged to server console.');
  return true;
};

const sendOtp = async (req, res) => {
  try {
    let { phone, purpose, userId } = req.body;
    if (!phone || !purpose) return res.status(400).json({ message: 'phone and purpose required' });

    // Normalize phone number
    phone = phone.trim().replace(/\s+/g, '');
    if (phone.startsWith('0')) {
      phone = '+63' + phone.slice(1);
    }

    await ensureTables();

    const otp = generateOtp();
    const otpHash = await bcrypt.hash(otp, 10);
    const expiresAt = new Date(Date.now() + 10 * 60 * 1000); // 10 minutes

    await db.query(
      'INSERT INTO otp_codes (phone, otp_hash, purpose, user_id, expires_at) VALUES (?, ?, ?, ?, ?)',
      [phone, otpHash, purpose, userId || null, expiresAt]
    );

    const message = `Your AlertDavao verification code is: ${otp}`;
    const sent = await sendSms(phone, message);

    // For dev: always log OTP to console and include in response when SMS provider not configured
    if (!process.env.TWILIO_SID) {
      console.log('\n🔐 OTP CODE for', phone, ':', otp);
      console.log('📱 Purpose:', purpose);
      console.log('⏱️  Expires:', expiresAt.toLocaleString());
      console.log('─'.repeat(50) + '\n');
    }

    // Always include OTP in response when not in production or when SMS provider not configured
    const debug = (process.env.NODE_ENV !== 'production' || !process.env.TWILIO_SID) ? otp : undefined;

    res.json({ success: true, sent, debugOtp: debug });
  } catch (err) {
    console.error('❌ sendOtp error:', err);
    res.status(500).json({ message: 'Failed to send OTP', error: err.message });
  }
};

const verifyOtp = async (req, res) => {
  try {
    let { phone, purpose, code } = req.body;
    console.log('📥 Verify OTP request:', { phone, purpose, code });
    
    if (!phone || !purpose || !code) {
      console.log('❌ Missing required fields');
      return res.status(400).json({ message: 'phone, purpose and code required' });
    }

    // Normalize phone: trim, remove spaces, ensure starts with country code if needed
    phone = phone.trim().replace(/\s+/g, '');
    if (phone.startsWith('0')) {
      // Example: convert '09123456789' to '+639123456789'
      phone = '+63' + phone.slice(1);
    }
    console.log('📱 Normalized phone:', phone);

    await ensureTables();

    const [rows] = await db.query(
      'SELECT * FROM otp_codes WHERE phone = ? AND purpose = ? ORDER BY created_at DESC LIMIT 1',
      [phone, purpose]
    );
    console.log('🔍 Found OTP records:', rows.length);

    if (rows.length === 0) {
      console.log('❌ No OTP found for phone:', phone, 'purpose:', purpose);
      return res.status(400).json({ message: 'No OTP found for this phone' });
    }
    const otpRow = rows[0];
    console.log('📋 OTP row:', { id: otpRow.id, phone: otpRow.phone, purpose: otpRow.purpose, expires_at: otpRow.expires_at });

    if (new Date(otpRow.expires_at) < new Date()) {
      console.log('❌ OTP expired');
      return res.status(400).json({ message: 'OTP expired' });
    }

    const match = await bcrypt.compare(code, otpRow.otp_hash);
    console.log('🔐 OTP match:', match);
    if (!match) {
      console.log('❌ Invalid OTP code');
      return res.status(400).json({ message: 'Invalid OTP' });
    }

    console.log('✅ OTP verified successfully!');

    // Mark phone as verified
    await db.query('INSERT INTO verified_phones (phone, verified) VALUES (?, 1) ON DUPLICATE KEY UPDATE verified = 1, verified_at = NOW()', [phone]);

    // Remove used OTPs for this phone/purpose
    await db.query('DELETE FROM otp_codes WHERE id = ?', [otpRow.id]);

    // If this OTP was for login, return the user object to allow completing login
    if (purpose === 'login') {
      console.log('🔍 Looking for user with phone:', phone);
      // Try to find user by normalized phone OR original phone format
      const [users] = await db.query(
        'SELECT * FROM users WHERE contact = ? OR contact = ?', 
        [phone, phone.replace('+63', '0')]
      );
      console.log('👥 Found users:', users.length);
      if (users.length > 0) {
        const user = users[0];
        console.log('✅ Returning user:', user.email);
        return res.json({ success: true, message: 'OTP verified', user: {
          id: user.id,
          email: user.email,
          firstname: user.firstname,
          lastname: user.lastname,
          role: user.role || 'user',
          contact: user.contact,
          address: user.address,
          is_verified: user.is_verified,
          profile_image: user.profile_image
        }});
      } else {
        console.log('❌ No user found with phone:', phone);
      }
    }

    res.json({ success: true, message: 'OTP verified' });
  } catch (err) {
    console.error('❌ verifyOtp error:', err);
    res.status(500).json({ message: 'Failed to verify OTP', error: err.message });
  }
};

module.exports = { sendOtp, verifyOtp };
