const bcrypt = require("bcryptjs");
const crypto = require("crypto");
const db = require("./db");
const nodemailer = require("nodemailer");

// Configure email transporter
// TODO: Replace with your actual email credentials
const transporter = nodemailer.createTransport({
  service: 'gmail', // or your email service
  auth: {
    user: process.env.EMAIL_USER || 'your-email@gmail.com',
    pass: process.env.EMAIL_PASSWORD || 'your-app-password'
  }
});

// Generate verification token
const generateVerificationToken = () => {
  return crypto.randomBytes(32).toString('hex');
};

// Send verification email
const sendVerificationEmail = async (email, token, firstname) => {
  const verificationUrl = `http://localhost:3000/api/verify-email?token=${token}`;
  
  const mailOptions = {
    from: process.env.EMAIL_USER || 'your-email@gmail.com',
    to: email,
    subject: 'AlertDavao - Verify Your Email Address',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #dc2626;">AlertDavao Email Verification</h2>
        <p>Hello ${firstname},</p>
        <p>Thank you for registering with AlertDavao. Please verify your email address to complete your registration.</p>
        <p style="margin: 30px 0;">
          <a href="${verificationUrl}" 
             style="background-color: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Verify Email Address
          </a>
        </p>
        <p style="color: #666; font-size: 14px;">
          If the button doesn't work, copy and paste this link into your browser:<br>
          <a href="${verificationUrl}">${verificationUrl}</a>
        </p>
        <p style="color: #666; font-size: 14px;">
          This link will expire in 24 hours.
        </p>
        <p style="color: #666; font-size: 14px;">
          If you didn't create an account with AlertDavao, you can safely ignore this email.
        </p>
      </div>
    `
  };

  try {
    await transporter.sendMail(mailOptions);
    return true;
  } catch (error) {
    console.error('Error sending verification email:', error);
    return false;
  }
};

// Handle registration with email verification
const handleRegisterWithVerification = async (req, res) => {
  const { firstname, lastname, email, contact, password } = req.body;

  if (!firstname || !lastname || !email || !contact || !password) {
    return res.status(400).json({ message: "All fields are required" });
  }

  // Validate email format
  const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailRegex.test(email)) {
    return res.status(400).json({ 
      message: "Invalid email format. Please enter a valid email address with @ and domain (e.g., user@gmail.com, admin@yahoo.com)" 
    });
  }

  if (!email.includes('@')) {
    return res.status(400).json({ 
      message: "Email must contain @ symbol. For example: nicolequim@gmail.com" 
    });
  }

  // Validate password requirements
  const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  if (!passwordRegex.test(password)) {
    return res.status(400).json({ 
      message: "Password must contain minimum 8 characters with at least one letter, one number, and one symbol (@$!%*?&)" 
    });
  }

  try {
    // Ensure phone has been OTP-verified
    const [verifiedRows] = await db.query("SELECT * FROM verified_phones WHERE phone = ? AND verified = 1", [contact]);
    if (verifiedRows.length === 0) {
      return res.status(400).json({ message: 'Phone number not verified. Please complete OTP verification.' });
    }

    // Check if email already exists in users table
    const [existingUsers] = await db.query("SELECT id FROM users WHERE email = ?", [email]);
    if (existingUsers.length > 0) {
      return res.status(409).json({ message: "Email already registered" });
    }

    // Check if email already has a pending verification
    const [pendingUsers] = await db.query("SELECT id FROM pending_users WHERE email = ?", [email]);
    if (pendingUsers.length > 0) {
      // Delete old pending verification
      await db.query("DELETE FROM pending_users WHERE email = ?", [email]);
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    const verificationToken = generateVerificationToken();
    const expiresAt = new Date(Date.now() + 24 * 60 * 60 * 1000); // 24 hours

    // Store in pending_users table
    const sql = `
      INSERT INTO pending_users (first_name, last_name, email, password, phone_number, verification_token, expires_at) 
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `;
    await db.query(sql, [firstname, lastname, email, hashedPassword, contact, verificationToken, expiresAt]);

    // Send verification email
    const emailSent = await sendVerificationEmail(email, verificationToken, firstname);

    if (!emailSent) {
      // Email sending failed, but we still saved the pending user
      console.warn('Verification email failed to send');
    }

    res.status(200).json({ 
      message: "If the email you provided exists, a verification link has been sent. Please check your inbox and click the link to complete your registration. The link will expire in 24 hours.",
      emailSent: emailSent
    });

  } catch (err) {
    console.error("❌ Registration error:", err);
    
    if (err.code === 'ECONNREFUSED') {
      return res.status(500).json({ message: "Database connection failed. Please check if MySQL is running." });
    }
    if (err.code === 'ER_ACCESS_DENIED_ERROR') {
      return res.status(500).json({ message: "Database access denied. Please check credentials." });
    }
    
    res.status(500).json({ 
      message: "Error processing registration",
      error: err.message || err.sqlMessage || "Unknown error"
    });
  }
};

// Verify email token and activate account
const verifyEmail = async (req, res) => {
  const { token } = req.query;

  if (!token) {
    return res.status(400).send(`
      <html>
        <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
          <h2 style="color: #dc2626;">Invalid Verification Link</h2>
          <p>The verification link is invalid or missing.</p>
        </body>
      </html>
    `);
  }

  try {
    // Find pending user with this token
    const [pendingUsers] = await db.query(
      "SELECT * FROM pending_users WHERE verification_token = ? AND expires_at > NOW()",
      [token]
    );

    if (pendingUsers.length === 0) {
      return res.status(400).send(`
        <html>
          <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
            <h2 style="color: #dc2626;">Verification Link Expired</h2>
            <p>This verification link has expired or is invalid. Please register again.</p>
          </body>
        </html>
      `);
    }

    const pendingUser = pendingUsers[0];

    // Move user to users table
    const insertSql = `
      INSERT INTO users (firstname, lastname, email, contact, password, latitude, longitude, created_at) 
      VALUES (?, ?, ?, ?, ?, NULL, NULL, NOW())
    `;
    await db.query(insertSql, [
      pendingUser.first_name,
      pendingUser.last_name,
      pendingUser.email,
      pendingUser.phone_number,
      pendingUser.password
    ]);

    // Delete from pending_users
    await db.query("DELETE FROM pending_users WHERE id = ?", [pendingUser.id]);

    res.status(200).send(`
      <html>
        <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
          <div style="max-width: 500px; margin: 0 auto;">
            <h2 style="color: #16a34a;">✓ Email Verified Successfully!</h2>
            <p>Your AlertDavao account has been activated.</p>
            <p>You can now log in to the AlertDavao app with your credentials.</p>
            <p style="margin-top: 30px; color: #666;">You can close this window now.</p>
          </div>
        </body>
      </html>
    `);

  } catch (err) {
    console.error("❌ Email verification error:", err);
    res.status(500).send(`
      <html>
        <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
          <h2 style="color: #dc2626;">Verification Failed</h2>
          <p>An error occurred while verifying your email. Please try again later.</p>
        </body>
      </html>
    `);
  }
};

module.exports = {
  handleRegisterWithVerification,
  verifyEmail
};
