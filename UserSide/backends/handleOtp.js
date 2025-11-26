const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const db = require('./db');
const fetch = require('node-fetch');
const { createClient } = require('@supabase/supabase-js');

// Load environment variables
require('dotenv').config();

// Supabase SMS OTP Configuration - use SUPABASE_URL (not EXPO_PUBLIC_ prefix for backend)
const SUPABASE_URL = process.env.SUPABASE_URL || process.env.EXPO_PUBLIC_SUPABASE_URL;
const SUPABASE_ANON_KEY = process.env.SUPABASE_ANON_KEY || process.env.EXPO_PUBLIC_SUPABASE_ANON_KEY;

console.log('🔧 Supabase Config Check:');
console.log('   SUPABASE_URL:', SUPABASE_URL ? '✅ Set' : '❌ Not set');
console.log('   SUPABASE_ANON_KEY:', SUPABASE_ANON_KEY ? '✅ Set' : '❌ Not set');

// Initialize Supabase client for server-side operations
let supabase = null;
if (SUPABASE_URL && SUPABASE_ANON_KEY) {
  supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
    auth: {
      autoRefreshToken: false,
      persistSession: false,
      detectSessionInUrl: false,
    },
  });
  console.log('✅ Supabase client initialized for backend OTP');
} else {
  console.log('⚠️ Supabase client NOT initialized - missing credentials');
}

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

/**
 * Send SMS via Supabase signInWithOtp or fallback methods
 * SMS Format: "Your verification code is {{OTP}}. It is valid for 5 minutes. Do not share this code with anyone for your security."
 * Sender: AlertDavao (configured in Supabase Dashboard)
 * 
 * Priority:
 * 1. Supabase SMS OTP (native signInWithOtp - sends SMS directly to phone)
 * 2. Twilio (if configured)
 * 3. Console log (development mode)
 */
const sendSms = async (phone, otp) => {
  const message = `Your verification code is ${otp}. It is valid for 5 minutes. Do not share this code with anyone for your security.`;
  
  // Try Supabase SMS OTP first using signInWithOtp
  // This sends SMS directly to the user's phone via Supabase's configured SMS provider
  if (supabase) {
    try {
      console.log('📨 Attempting to send SMS via Supabase signInWithOtp to:', phone);
      
      // Use Supabase's native signInWithOtp which handles SMS sending
      // The SMS template should be configured in Supabase Dashboard with:
      // Sender: AlertDavao
      // Message: "Your verification code is {{.Token}}. It is valid for 5 minutes. Do not share this code with anyone for your security."
      const { data, error } = await supabase.auth.signInWithOtp({
        phone: phone,
        options: {
          channel: 'sms',
        },
      });

      if (error) {
        console.error('⚠️ Supabase SMS error:', error.message);
        // Fall through to next method
      } else {
        console.log('✅ SMS sent successfully via Supabase');
        return true;
      }
    } catch (err) {
      console.error('⚠️ Supabase SMS exception:', err.message);
      // Fall through to next method
    }
  }

  // Fallback to Twilio if configured
  const TWILIO_SID = process.env.TWILIO_SID;
  const TWILIO_TOKEN = process.env.TWILIO_TOKEN;
  const TWILIO_FROM = process.env.TWILIO_FROM;

  if (TWILIO_SID && TWILIO_TOKEN && TWILIO_FROM) {
    try {
      console.log('📲 Sending SMS via Twilio to:', phone);
      const url = `https://api.twilio.com/2010-04-01/Accounts/${TWILIO_SID}/Messages.json`;
      const body = new URLSearchParams({ 
        To: phone, 
        From: TWILIO_FROM, 
        Body: message 
      });
      const resp = await fetch(url, {
        method: 'POST',
        headers: {
          Authorization: 'Basic ' + Buffer.from(`${TWILIO_SID}:${TWILIO_TOKEN}`).toString('base64'),
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: body.toString()
      });
      return resp.ok;
    } catch (err) {
      console.error('❌ Twilio SMS error:', err.message);
      // Fall through to development mode
    }
  }

  // Development mode: log OTP to console instead of sending SMS
  console.warn('⚠️ No SMS provider configured (Supabase or Twilio).');
  console.warn('🔐 OTP for', phone, 'is logged to server console for testing.');
  return true;
};

/**
 * Send OTP for registration (signup only)
 * OTP is NOT sent for login/sign-in
 */
const sendOtp = async (req, res) => {
  try {
    let { phone, purpose } = req.body;
    
    // OTP is only for registration
    if (purpose !== 'register') {
      return res.status(400).json({ 
        success: false,
        message: 'OTP is only available during registration' 
      });
    }

    if (!phone) {
      return res.status(400).json({ message: 'phone number required' });
    }

    // Normalize phone number to international format
    phone = phone.trim().replace(/\s+/g, '');
    if (phone.startsWith('0')) {
      phone = '+63' + phone.slice(1);
    }

    console.log('📱 Sending OTP for registration to:', phone);
    await ensureTables();

    // Generate 6-digit OTP
    const otp = generateOtp();
    const otpHash = await bcrypt.hash(otp, 10);
    const expiresAt = new Date(Date.now() + 5 * 60 * 1000); // 5 minutes expiration

    // Store OTP in database
    await db.query(
      'INSERT INTO otp_codes (phone, otp_hash, purpose, expires_at) VALUES (?, ?, ?, ?)',
      [phone, otpHash, 'register', expiresAt]
    );

    // Send SMS via Supabase or fallback
    const sent = await sendSms(phone, otp);

    // Log for development
    console.log('\n' + '═'.repeat(60));
    console.log('📨 OTP SENT FOR REGISTRATION');
    console.log('═'.repeat(60));
    console.log('Phone:', phone);
    console.log('OTP Code:', otp);
    console.log('Expires:', expiresAt.toLocaleString());
    console.log('Message: "Your verification code is ' + otp + '. It is valid for 5 minutes. Do not share this code with anyone for your security."');
    console.log('═'.repeat(60) + '\n');

    // Include OTP in response for development (remove in production)
    const debug = (process.env.NODE_ENV !== 'production' || !SUPABASE_URL) ? otp : undefined;

    res.json({ 
      success: true, 
      message: 'OTP sent successfully to your phone',
      sent, 
      debugOtp: debug 
    });
  } catch (err) {
    console.error('❌ sendOtp error:', err);
    res.status(500).json({ 
      success: false,
      message: 'Failed to send OTP', 
      error: err.message 
    });
  }
};

/**
 * Verify OTP code during registration (signup only)
 * @param {string} phone - Phone number in international format
 * @param {string} code - 6-digit OTP code
 * @param {string} purpose - Should be 'register' only
 */
const verifyOtp = async (req, res) => {
  try {
    let { phone, code, purpose } = req.body;
    console.log('📥 Verify OTP request for:', phone);
    
    // OTP verification is only for registration
    if (purpose !== 'register') {
      return res.status(400).json({ 
        success: false,
        message: 'OTP verification is only available during registration' 
      });
    }

    if (!phone || !code || !purpose) {
      return res.status(400).json({ 
        success: false,
        message: 'phone, code, and purpose required' 
      });
    }

    // Validate OTP is 6 digits
    if (!/^\d{6}$/.test(code)) {
      return res.status(400).json({ 
        success: false,
        message: 'OTP must be 6 digits' 
      });
    }

    // Normalize phone: international format
    phone = phone.trim().replace(/\s+/g, '');
    if (phone.startsWith('0')) {
      phone = '+63' + phone.slice(1);
    }
    
    console.log('📱 Normalized phone:', phone);
    await ensureTables();

    // Find the most recent OTP for this phone
    const [rows] = await db.query(
      'SELECT * FROM otp_codes WHERE phone = ? AND purpose = ? ORDER BY created_at DESC LIMIT 1',
      [phone, 'register']
    );

    if (rows.length === 0) {
      console.log('❌ No OTP found for phone:', phone);
      return res.status(400).json({ 
        success: false,
        message: 'No OTP found. Please request a new OTP.' 
      });
    }

    const otpRow = rows[0];
    console.log('🔍 OTP record found, ID:', otpRow.id);

    // Check if OTP has expired (5 minutes)
    if (new Date(otpRow.expires_at) < new Date()) {
      console.log('❌ OTP expired at:', otpRow.expires_at);
      // Delete expired OTP
      await db.query('DELETE FROM otp_codes WHERE id = ?', [otpRow.id]);
      return res.status(400).json({ 
        success: false,
        message: 'OTP expired. Please request a new OTP.' 
      });
    }

    // Compare OTP code with hash
    const match = await bcrypt.compare(code, otpRow.otp_hash);
    
    if (!match) {
      console.log('❌ Invalid OTP code provided');
      return res.status(400).json({ 
        success: false,
        message: 'Invalid OTP code. Please try again.' 
      });
    }

    console.log('✅ OTP verified successfully!');

    // Mark phone as verified for future use
    await db.query(
      'INSERT INTO verified_phones (phone, verified) VALUES (?, 1) ON DUPLICATE KEY UPDATE verified = 1, verified_at = NOW()',
      [phone]
    );

    // Delete the used OTP
    await db.query('DELETE FROM otp_codes WHERE id = ?', [otpRow.id]);

    res.json({ 
      success: true, 
      message: 'Phone number verified successfully. You can now complete your registration.' 
    });
  } catch (err) {
    console.error('❌ verifyOtp error:', err);
    res.status(500).json({ 
      success: false,
      message: 'Failed to verify OTP', 
      error: err.message 
    });
  }
};

module.exports = { sendOtp, verifyOtp };
