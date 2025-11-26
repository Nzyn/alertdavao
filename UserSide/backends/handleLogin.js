// handleLogin.js
const bcrypt = require("bcryptjs");
const db = require("./db");
const { checkUserRestrictions } = require("./handleUserRestrictions");

// Sanitize email input
const sanitizeEmail = (email) => {
  if (!email) return '';
  return email.toString().trim().toLowerCase().replace(/[<>'"]/g, '');
};

const handleLogin = async (req, res) => {
  const { email, password } = req.body;
  
  // Sanitize inputs
  const sanitizedEmail = sanitizeEmail(email);

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(sanitizedEmail)) {
    return res.status(400).json({ message: "Invalid email format" });
  }

  // Validate password presence
  if (!password || password.length < 6) {
    return res.status(400).json({ message: "Password must be at least 6 characters" });
  }

  try {
    console.log("📩 Login attempt:", { email: sanitizedEmail });

    // 1. Query user
    const [rows] = await db.query("SELECT * FROM users WHERE email = ?", [sanitizedEmail]);
    console.log("📊 Query result:", rows.length > 0 ? "User found" : "User not found");

    if (rows.length === 0) {
      return res.status(401).json({ message: "User not found" });
    }

    const user = rows[0];
    console.log("👤 Found user:", user.email);

    // 2. Check for user restrictions BEFORE password verification
    try {
      const restrictions = await checkUserRestrictions(user.id);
      
      if (restrictions.isRestricted) {
        console.log("🚫 User is restricted:", restrictions.restrictionType);
        
        // If user is banned, deny login completely
        if (restrictions.restrictionType === 'banned') {
          return res.status(403).json({
            message: "Your account has been suspended",
            restricted: true,
            restrictionType: restrictions.restrictionType,
            reason: restrictions.reason,
            expiresAt: restrictions.expiresAt
          });
        }
        
        // For other restrictions, allow login but include restriction info
        console.log("⚠️ User has active restrictions but can still login");
      }
    } catch (restrictionError) {
      // If restriction check fails (e.g., tables don't exist yet), continue with login
      console.log("⚠️ Could not check restrictions (tables may not exist):", restrictionError.message);
    }

    // 3. Compare password
    const passwordMatch = await bcrypt.compare(password, user.password);
    if (!passwordMatch) {
      console.log("❌ Invalid password for:", sanitizedEmail);
      return res.status(401).json({ message: "Invalid credentials" });
    }

    // 4. Get any active restrictions to include in response
    let userRestrictions = null;
    try {
      userRestrictions = await checkUserRestrictions(user.id);
    } catch (err) {
      // Ignore if tables don't exist
    }

    // 5. Instead of immediately returning full success, require OTP verification.
    console.log("🔐 Password verified for:", user.email, "— requiring OTP to complete login");
    
    // Return minimal info so client can trigger OTP flow
    res.json({
      need_otp: true,
      message: 'OTP required to complete login',
      user: {
        id: user.id,
        contact: user.contact
      },
      restrictions: userRestrictions
    });
  } catch (error) {
    // ✅ Print full error details in terminal
    console.error("❌ Login error occurred:", error);
    res.status(500).json({
      message: "Server error",
      error: error.message || error.sqlMessage || "Unknown error",
    });
  }
};

module.exports = handleLogin;
