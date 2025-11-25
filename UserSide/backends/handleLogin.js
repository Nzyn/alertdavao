// handleLogin.js
const bcrypt = require("bcryptjs");
const db = require("./db");
 
const handleLogin = async (req, res) => {
  const { email, password } = req.body;

  try {
    console.log("📩 Login attempt:", { email });

    // 1. Query user
    const [rows] = await db.query("SELECT * FROM users WHERE email = ?", [email]);
    console.log("📊 Query result:", rows);

    if (rows.length === 0) {
      return res.status(401).json({ message: "User not found" });
    }

    const user = rows[0];
    console.log("👤 Found user:", user);

    // 2. Compare password
    const passwordMatch = await bcrypt.compare(password, user.password);
    if (!passwordMatch) {
      console.log("❌ Invalid password for:", email);
      return res.status(401).json({ message: "Invalid credentials" });
    }

    // 3. Instead of immediately returning full success, require OTP verification.
    console.log("🔐 Password verified for:", user.email, "— requiring OTP to complete login");
    // Return minimal info so client can trigger OTP flow
    res.json({
      need_otp: true,
      message: 'OTP required to complete login',
      user: {
        id: user.id,
        contact: user.contact
      }
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
