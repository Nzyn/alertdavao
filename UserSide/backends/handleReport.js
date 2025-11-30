const db = require("./db");
const multer = require("multer");
const path = require("path");
const fs = require("fs");
const { encrypt, decrypt, canDecrypt, encryptFields, decryptFields } = require("./encryptionService");

// Configure multer for file uploads
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    const uploadDir = path.join(__dirname, "../evidence");
    // Create directory if it doesn't exist
    if (!fs.existsSync(uploadDir)) {
      fs.mkdirSync(uploadDir, { recursive: true });
    }
    cb(null, uploadDir);
  },
  filename: function (req, file, cb) {
    const uniqueSuffix = Date.now() + "-" + Math.round(Math.random() * 1e9);
    const ext = path.extname(file.originalname);
    cb(null, "evidence-" + uniqueSuffix + ext);
  },
});

const upload = multer({
  storage: storage,
  limits: { fileSize: 25 * 1024 * 1024 }, // 25MB limit
  fileFilter: function (req, file, cb) {
    const allowedTypes = /jpeg|jpg|png|gif|mp4|mov|avi/;
    const extname = allowedTypes.test(
      path.extname(file.originalname).toLowerCase()
    );
    const mimetype = allowedTypes.test(file.mimetype);

    if (mimetype && extname) {
      return cb(null, true);
    } else {
      cb(new Error("Only images and videos are allowed!"));
    }
  },
});

// Submit a new report
async function submitReport(req, res) {
  const connection = await db.getConnection();
  
  try {
    await connection.beginTransaction();

    const {
      title,
      crime_types,
      description,
      incident_date,
      is_anonymous,
      user_id,
      latitude,
      longitude,
      reporters_address,
      barangay,
      barangay_id,
    } = req.body;

    console.log("📝 Submitting report:", {
      title,
      crime_types,
      description,
      incident_date,
      is_anonymous,
      user_id,
      latitude,
      longitude,
      reporters_address,
      barangay,
      barangay_id,
      hasFile: !!req.file,
      fileDetails: req.file ? {
        fieldname: req.file.fieldname,
        originalname: req.file.originalname,
        encoding: req.file.encoding,
        mimetype: req.file.mimetype,
        size: req.file.size,
        destination: req.file.destination,
        filename: req.file.filename,
        path: req.file.path
      } : null
    });

    // Validation
    if (!title || !crime_types || !description || !incident_date || !user_id) {
      await connection.rollback();
      return res.status(422).json({
        success: false,
        message: "Missing required fields",
        errors: {
          title: !title ? ["Title is required"] : [],
          crime_types: !crime_types ? ["Crime type is required"] : [],
          description: !description ? ["Description is required"] : [],
          incident_date: !incident_date ? ["Incident date is required"] : [],
          user_id: !user_id ? ["User ID is required"] : [],
        },
      });
    }

    // Check if user is restricted
    const [userCheck] = await connection.query(
      'SELECT restriction_level, total_flags FROM users WHERE id = ?',
      [user_id]
    );
    
    if (userCheck.length > 0) {
      const { restriction_level, total_flags } = userCheck[0];
      if (restriction_level && restriction_level !== 'none') {
        await connection.rollback();
        return res.status(403).json({
          success: false,
          message: `Your account is ${restriction_level}. You cannot submit reports until the restriction is lifted by an administrator.`,
          restriction: {
            level: restriction_level,
            flags: total_flags
          }
        });
      }
    }

    // Parse crime types if it's a JSON string
    let crimeTypesArray;
    try {
      crimeTypesArray =
        typeof crime_types === "string"
          ? JSON.parse(crime_types)
          : crime_types;
    } catch (e) {
      crimeTypesArray = [crime_types];
    }
    const reportType = JSON.stringify(crimeTypesArray);

    // Create location record
    const lat = latitude ? parseFloat(latitude) : 0;
    const lng = longitude ? parseFloat(longitude) : 0;
    
    // Use provided barangay name or fallback to coordinates
    const barangayName = barangay || (lat !== 0 && lng !== 0 ? `Lat: ${lat}, Lng: ${lng}` : "Unknown");
    const address = reporters_address || null;

    // 🔐 ENCRYPT location data before storing
    const encryptedBarangayName = encrypt(barangayName);
    const encryptedAddress = address ? encrypt(address) : null;

    const [locationResult] = await connection.query(
      "INSERT INTO locations (barangay, reporters_address, latitude, longitude, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())",
      [encryptedBarangayName, encryptedAddress, lat, lng]
    );

    const locationId = locationResult.insertId;
    console.log("✅ Location created with ID:", locationId);
    console.log("   Barangay:", barangayName);
    console.log("   Address:", address);
    console.log("   Coordinates:", lat, lng);

    // Get the station_id from provided barangay_id or calculate from coordinates
    let stationId = null;
    
    // Check if this is a cybercrime report - route to Cybercrime Division globally
    const isCybercrime = crimeTypesArray.some(crime => 
      crime.toLowerCase().includes('cybercrime') || 
      crime.toLowerCase().includes('cyber crime') ||
      crime.toLowerCase().includes('online fraud') ||
      crime.toLowerCase().includes('hacking') ||
      crime.toLowerCase().includes('phishing') ||
      crime.toLowerCase().includes('identity theft') ||
      crime.toLowerCase().includes('ransomware')
    );
    
    if (isCybercrime) {
      // Route cybercrime reports to Cybercrime Division (global assignment, no location-based assignment)
      try {
        const [cybercrimeStation] = await connection.query(
          `SELECT station_id FROM police_stations WHERE station_name = 'Cybercrime Division' LIMIT 1`
        );
        if (cybercrimeStation && cybercrimeStation.length > 0) {
          stationId = cybercrimeStation[0].station_id;
          console.log("🚨 Cybercrime report detected! Routing to Cybercrime Division (Station ID:", stationId, ")");
        } else {
          console.log("⚠️  Cybercrime Division station not found in database");
        }
      } catch (err) {
        console.log("⚠️  Error routing cybercrime report:", err.message);
      }
    } else {
      // Location-based routing for non-cybercrime reports
      if (barangay_id) {
        // Use provided barangay_id to get station_id
        try {
          const [barangayResult] = await connection.query(
            `SELECT station_id FROM barangays WHERE barangay_id = ?`,
            [barangay_id]
          );
          if (barangayResult && barangayResult.length > 0) {
            stationId = barangayResult[0].station_id;
            console.log("✅ Station ID assigned from barangay_id:", stationId);
          }
        } catch (err) {
          console.log("⚠️  Could not get station from barangay_id:", err.message);
        }
      }
      
      // Fallback: Use Haversine formula to find nearest barangay
      if (!stationId && lat !== 0 && lng !== 0) {
        try {
          const [stationResult] = await connection.query(
            `SELECT b.station_id FROM barangays b 
             WHERE b.latitude IS NOT NULL AND b.longitude IS NOT NULL
             ORDER BY (
               6371 * acos(
                 cos(radians(90 - b.latitude)) * cos(radians(90 - ?)) +
                 sin(radians(90 - b.latitude)) * sin(radians(90 - ?)) * cos(radians(b.longitude - ?))
               )
             ) ASC
             LIMIT 1`,
            [lat, lat, lng]
          );
          if (stationResult && stationResult.length > 0) {
            stationId = stationResult[0].station_id;
            console.log("✅ Station ID assigned via nearest barangay (Haversine):", stationId);
          } else {
            console.log("⚠️  No nearby barangay found");
          }
        } catch (err) {
          console.log("⚠️  Could not determine station from location:", err.message);
        }
      }
    }

    // Create report record
    const isAnon = is_anonymous === "1" || is_anonymous === true || is_anonymous === "true";
    
    // 🔐 ENCRYPT SENSITIVE DATA (AES-256-CBC)
    // As per capstone requirement: encrypt incident reports for confidentiality
    const encryptedDescription = encrypt(description);
    
    console.log("🔐 Encrypting sensitive report data using AES-256-CBC...");
    
    const [reportResult] = await connection.query(
      `INSERT INTO reports 
       (user_id, location_id, title, report_type, description, date_reported, status, is_anonymous, station_id, created_at, updated_at) 
       VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW(), NOW())`,
      [user_id, locationId, title, reportType, encryptedDescription, incident_date, isAnon, stationId]
    );

    const reportId = reportResult.insertId;
    console.log("✅ Report created with ID:", reportId);

    // Handle media upload if file exists
    let mediaData = null;
    if (req.file) {
      console.log("📸 Processing file upload...");
      console.log("   Original name:", req.file.originalname);
      console.log("   Saved as:", req.file.filename);
      console.log("   Size:", req.file.size, "bytes");
      console.log("   MIME type:", req.file.mimetype);
      console.log("   Saved to:", req.file.path);
      
      const mediaUrl = `/evidence/${req.file.filename}`;
      const mediaType = path.extname(req.file.originalname).substring(1).toLowerCase();

      console.log("   Media URL:", mediaUrl);
      console.log("   Media Type:", mediaType);

      try {
        const [mediaResult] = await connection.query(
          "INSERT INTO report_media (report_id, media_url, media_type, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
          [reportId, mediaUrl, mediaType]
        );

        mediaData = {
          media_id: mediaResult.insertId,
          media_url: mediaUrl,
          media_type: mediaType,
          file_size: req.file.size,
          original_name: req.file.originalname,
        };

        console.log("✅ Media uploaded successfully!");
        console.log("   Media ID:", mediaResult.insertId);
        console.log("   Stored in database: report_media table");
        console.log("   File location: evidence/", req.file.filename);
      } catch (dbError) {
        console.error("❌ Database insertion failed:", dbError);
        throw new Error("Failed to save media to database: " + dbError.message);
      }
    } else {
      console.log("⚠️  No file uploaded with this report");
    }

    // Commit transaction
    await connection.commit();

    // Return success response
    res.status(201).json({
      success: true,
      message: "Report submitted successfully",
      data: {
        report_id: reportId,
        title: title,
        report_type: reportType,
        status: "pending",
        is_anonymous: isAnon,
        date_reported: incident_date,
        station_id: stationId,
        location: {
          location_id: locationId,
          latitude: lat,
          longitude: lng,
          barangay: barangay,
          reporters_address: address,
        },
        media: mediaData,
      },
    });

    console.log("🎉 Report submitted successfully!");
  } catch (error) {
    await connection.rollback();
    console.error("❌ Error submitting report:", error);
    res.status(500).json({
      success: false,
      message: "Failed to submit report: " + error.message,
    });
  } finally {
    connection.release();
  }
}

// Get reports for a specific user
async function getUserReports(req, res) {
  try {
    const { userId } = req.params;
    const userRole = req.query.role || req.body.role || 'user'; // Get user role from request

    const [reports] = await db.query(
      `SELECT 
        r.report_id,
        r.title,
        r.report_type,
        r.description,
        r.status,
        r.is_anonymous,
        r.date_reported,
        r.created_at,
        r.station_id,
        l.latitude,
        l.longitude,
        l.barangay,
        l.reporters_address,
        ps.station_name,
        ps.address as station_address,
        ps.contact_number,
        GROUP_CONCAT(CONCAT(rm.media_id, ':', rm.media_url, ':', rm.media_type) SEPARATOR '|') as media
      FROM reports r
      LEFT JOIN locations l ON r.location_id = l.location_id
      LEFT JOIN police_stations ps ON r.station_id = ps.station_id
      LEFT JOIN report_media rm ON r.report_id = rm.report_id
      WHERE r.user_id = ? 
        AND r.location_id IS NOT NULL 
        AND r.location_id != 0
        AND l.latitude IS NOT NULL 
        AND l.longitude IS NOT NULL
        AND l.latitude != 0
        AND l.longitude != 0
      GROUP BY r.report_id
      ORDER BY r.created_at DESC`,
      [userId]
    );

    // Parse media data and decrypt if authorized
    const formattedReports = reports.map((report) => {
      let mediaArray = [];
      if (report.media) {
        mediaArray = report.media.split("|").map((m) => {
          const [media_id, media_url, media_type] = m.split(":");
          return { media_id: parseInt(media_id), media_url, media_type };
        });
      }

      // 🔓 DECRYPT sensitive data for authorized roles (police/admin)
      // Regular users can only see their own encrypted data decrypted
      const decryptedDescription = decrypt(report.description);
      const decryptedBarangay = report.barangay ? decrypt(report.barangay) : null;
      const decryptedAddress = report.reporters_address ? decrypt(report.reporters_address) : null;

      return {
        report_id: report.report_id,
        title: report.title,
        report_type: report.report_type,
        description: decryptedDescription,
        status: report.status,
        is_anonymous: Boolean(report.is_anonymous),
        date_reported: report.date_reported,
        created_at: report.created_at,
        station_id: report.station_id,
        location: {
          latitude: report.latitude,
          longitude: report.longitude,
          barangay: decryptedBarangay,
          reporters_address: decryptedAddress,
        },
        station: report.station_id ? {
          station_id: report.station_id,
          station_name: report.station_name,
          address: report.station_address,
          contact_number: report.contact_number,
        } : null,
        media: mediaArray,
      };
    });

    res.json({
      success: true,
      data: formattedReports,
    });
  } catch (error) {
    console.error("❌ Error fetching user reports:", error);
    res.status(500).json({
      success: false,
      message: "Failed to fetch reports",
    });
  }
}

// Get all reports
async function getAllReports(req, res) {
  try {
    const userRole = req.query.role || req.body.role || 'user'; // Get user role from request
    
    const [reports] = await db.query(
      `SELECT 
        r.report_id,
        r.title,
        r.report_type,
        r.description,
        r.status,
        r.is_anonymous,
        r.date_reported,
        r.created_at,
        r.user_id,
        r.station_id,
        l.latitude,
        l.longitude,
        l.barangay,
        l.reporters_address,
        u.firstname,
        u.lastname,
        u.email,
        u.role as user_role,
        ps.station_name,
        ps.address as station_address,
        ps.contact_number,
        GROUP_CONCAT(CONCAT(rm.media_id, ':', rm.media_url, ':', rm.media_type) SEPARATOR '|') as media
      FROM reports r
      LEFT JOIN locations l ON r.location_id = l.location_id
      LEFT JOIN users u ON r.user_id = u.id
      LEFT JOIN police_stations ps ON r.station_id = ps.station_id
      LEFT JOIN report_media rm ON r.report_id = rm.report_id
      WHERE r.location_id IS NOT NULL 
        AND r.location_id != 0
        AND l.latitude IS NOT NULL 
        AND l.longitude IS NOT NULL
        AND l.latitude != 0
        AND l.longitude != 0
      GROUP BY r.report_id
      ORDER BY r.created_at DESC`
    );

    // Parse media data and decrypt for authorized roles
    const formattedReports = reports.map((report) => {
      let mediaArray = [];
      if (report.media) {
        mediaArray = report.media.split("|").map((m) => {
          const [media_id, media_url, media_type] = m.split(":");
          return { media_id: parseInt(media_id), media_url, media_type };
        });
      }

      // 🔓 DECRYPT sensitive data ONLY for police/admin roles
      // As per capstone requirement: only authorized personnel can view decrypted incident reports
      let decryptedDescription = report.description;
      let decryptedBarangay = report.barangay;
      let decryptedAddress = report.reporters_address;
      
      if (canDecrypt(userRole)) {
        console.log(`🔓 Decrypting report ${report.report_id} for authorized role: ${userRole}`);
        decryptedDescription = decrypt(report.description);
        decryptedBarangay = report.barangay ? decrypt(report.barangay) : null;
        decryptedAddress = report.reporters_address ? decrypt(report.reporters_address) : null;
      } else {
        console.log(`🔒 Keeping report ${report.report_id} encrypted for role: ${userRole}`);
      }

      return {
        report_id: report.report_id,
        title: report.title,
        report_type: report.report_type,
        description: decryptedDescription,
        status: report.status,
        is_anonymous: Boolean(report.is_anonymous),
        date_reported: report.date_reported,
        created_at: report.created_at,
        station_id: report.station_id,
        user: {
          user_id: report.user_id,
          firstname: report.firstname,
          lastname: report.lastname,
          email: report.email,
        },
        location: {
          latitude: report.latitude,
          longitude: report.longitude,
          barangay: decryptedBarangay,
          reporters_address: decryptedAddress,
        },
        station: report.station_id ? {
          station_id: report.station_id,
          station_name: report.station_name,
          address: report.station_address,
          contact_number: report.contact_number,
        } : null,
        media: mediaArray,
      };
    });

    res.json({
      success: true,
      data: formattedReports,
    });
  } catch (error) {
    console.error("❌ Error fetching all reports:", error);
    res.status(500).json({
      success: false,
      message: "Failed to fetch reports",
    });
  }
}

module.exports = {
  upload,
  submitReport,
  getUserReports,
  getAllReports,
};
