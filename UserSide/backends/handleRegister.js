const bcrypt = require("bcryptjs");
const db = require("./db");

const handleRegister = async (req, res) => {
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

  // Additional check for @ symbol
  if (!email.includes('@')) {
    return res.status(400).json({ 
      message: "Email must contain @ symbol. For example: nicolequim@gmail.com" 
    });
  }

  // Validate password requirements: min 8 chars, letter, number, symbol
  const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  if (!passwordRegex.test(password)) {
    return res.status(400).json({ 
      message: "Password must contain minimum 8 characters with at least one letter, one number, and one symbol (@$!%*?&)" 
    });
  }

  try {
    const hashedPassword = await bcrypt.hash(password, 10); // ✅ hash password

    // Include NULL values for latitude and longitude
    const sql =
      "INSERT INTO users (firstname, lastname, email, contact, password, latitude, longitude) VALUES (?, ?, ?, ?, ?, NULL, NULL)";
    await db.query(sql, [firstname, lastname, email, contact, hashedPassword]);

    res.status(201).json({ message: "✅ User registered successfully!" });
  } catch (err) {
    console.error("❌ Registration error:", err);
    
    // Provide more specific error messages
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(409).json({ message: "Email already registered" });
    }
    if (err.code === 'ECONNREFUSED') {
      return res.status(500).json({ message: "Database connection failed. Please check if MySQL is running." });
    }
    if (err.code === 'ER_ACCESS_DENIED_ERROR') {
      return res.status(500).json({ message: "Database access denied. Please check credentials." });
    }
    
    res.status(500).json({ 
      message: "Error saving user",
      error: err.message || err.sqlMessage || "Unknown error"
    });
  }
};

module.exports = handleRegister;