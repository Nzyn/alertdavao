/**
 * Get Reports for Police Officer's Station
 * Police officers see only reports assigned to their station
 */

const db = require("./db");

/**
 * Get reports for a specific police station
 * Used by police dashboard to show station-specific reports
 */
async function getReportsByStation(req, res) {
  try {
    const { stationId } = req.params;

    if (!stationId) {
      return res.status(400).json({
        success: false,
        message: "Station ID is required"
      });
    }

    console.log(`📍 Fetching reports for station ${stationId}...`);

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
        r.user_id,
        l.latitude,
        l.longitude,
        l.barangay,
        l.reporters_address,
        u.firstname,
        u.lastname,
        u.email,
        ps.station_name,
        ps.address as station_address,
        ps.contact_number,
        GROUP_CONCAT(CONCAT(rm.media_id, ':', rm.media_url, ':', rm.media_type) SEPARATOR '|') as media
      FROM reports r
      LEFT JOIN locations l ON r.location_id = l.location_id
      LEFT JOIN users u ON r.user_id = u.id
      LEFT JOIN police_stations ps ON r.station_id = ps.station_id
      LEFT JOIN report_media rm ON r.report_id = rm.report_id
      WHERE r.station_id = ?
        AND r.location_id IS NOT NULL 
        AND r.location_id != 0
        AND l.latitude IS NOT NULL 
        AND l.longitude IS NOT NULL
        AND l.latitude != 0
        AND l.longitude != 0
      GROUP BY r.report_id
      ORDER BY r.created_at DESC`,
      [stationId]
    );

    // Parse media data
    const formattedReports = reports.map((report) => {
      let mediaArray = [];
      if (report.media) {
        mediaArray = report.media.split("|").map((m) => {
          const [media_id, media_url, media_type] = m.split(":");
          return { media_id: parseInt(media_id), media_url, media_type };
        });
      }

      return {
        report_id: report.report_id,
        title: report.title,
        report_type: report.report_type,
        description: report.description,
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
          barangay: report.barangay,
          reporters_address: report.reporters_address,
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

    console.log(`✅ Found ${formattedReports.length} reports for station ${stationId}`);

    res.json({
      success: true,
      station_id: stationId,
      count: formattedReports.length,
      data: formattedReports,
    });
  } catch (error) {
    console.error("❌ Error fetching station reports:", error);
    res.status(500).json({
      success: false,
      message: "Failed to fetch station reports",
      error: error.message,
    });
  }
}

/**
 * Get reports by status for a police station
 * Used to filter reports by pending, in_progress, resolved, etc.
 */
async function getReportsByStationAndStatus(req, res) {
  try {
    const { stationId, status } = req.params;

    if (!stationId) {
      return res.status(400).json({
        success: false,
        message: "Station ID is required"
      });
    }

    if (!status) {
      return res.status(400).json({
        success: false,
        message: "Status is required"
      });
    }

    console.log(`📍 Fetching ${status} reports for station ${stationId}...`);

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
        r.user_id,
        l.latitude,
        l.longitude,
        l.barangay,
        l.reporters_address,
        u.firstname,
        u.lastname,
        u.email,
        ps.station_name,
        ps.address as station_address,
        ps.contact_number,
        GROUP_CONCAT(CONCAT(rm.media_id, ':', rm.media_url, ':', rm.media_type) SEPARATOR '|') as media
      FROM reports r
      LEFT JOIN locations l ON r.location_id = l.location_id
      LEFT JOIN users u ON r.user_id = u.id
      LEFT JOIN police_stations ps ON r.station_id = ps.station_id
      LEFT JOIN report_media rm ON r.report_id = rm.report_id
      WHERE r.station_id = ? AND r.status = ?
        AND r.location_id IS NOT NULL 
        AND r.location_id != 0
        AND l.latitude IS NOT NULL 
        AND l.longitude IS NOT NULL
        AND l.latitude != 0
        AND l.longitude != 0
      GROUP BY r.report_id
      ORDER BY r.created_at DESC`,
      [stationId, status]
    );

    // Parse media data
    const formattedReports = reports.map((report) => {
      let mediaArray = [];
      if (report.media) {
        mediaArray = report.media.split("|").map((m) => {
          const [media_id, media_url, media_type] = m.split(":");
          return { media_id: parseInt(media_id), media_url, media_type };
        });
      }

      return {
        report_id: report.report_id,
        title: report.title,
        report_type: report.report_type,
        description: report.description,
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
          barangay: report.barangay,
          reporters_address: report.reporters_address,
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

    console.log(`✅ Found ${formattedReports.length} ${status} reports for station ${stationId}`);

    res.json({
      success: true,
      station_id: stationId,
      status: status,
      count: formattedReports.length,
      data: formattedReports,
    });
  } catch (error) {
    console.error("❌ Error fetching station reports by status:", error);
    res.status(500).json({
      success: false,
      message: "Failed to fetch station reports",
      error: error.message,
    });
  }
}

/**
 * Get dashboard stats for a police station
 */
async function getStationDashboardStats(req, res) {
  try {
    const { stationId } = req.params;

    if (!stationId) {
      return res.status(400).json({
        success: false,
        message: "Station ID is required"
      });
    }

    console.log(`📊 Fetching dashboard stats for station ${stationId}...`);

    // Get summary stats
    const [stats] = await db.query(
      `SELECT 
        COUNT(*) as total_reports,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
      FROM reports
      WHERE station_id = ?`,
      [stationId]
    );

    // Get top crime types
    const [topCrimes] = await db.query(
      `SELECT 
        report_type,
        COUNT(*) as count
      FROM reports
      WHERE station_id = ?
      GROUP BY report_type
      ORDER BY count DESC
      LIMIT 5`,
      [stationId]
    );

    // Get recent reports
    const [recentReports] = await db.query(
      `SELECT 
        r.report_id,
        r.title,
        r.report_type,
        r.status,
        r.date_reported,
        r.created_at,
        l.barangay,
        u.firstname,
        u.lastname
      FROM reports r
      LEFT JOIN locations l ON r.location_id = l.location_id
      LEFT JOIN users u ON r.user_id = u.id
      WHERE r.station_id = ?
      ORDER BY r.created_at DESC
      LIMIT 10`,
      [stationId]
    );

    console.log(`✅ Got dashboard stats for station ${stationId}`);

    res.json({
      success: true,
      station_id: stationId,
      stats: stats[0],
      top_crime_types: topCrimes,
      recent_reports: recentReports,
    });
  } catch (error) {
    console.error("❌ Error fetching dashboard stats:", error);
    res.status(500).json({
      success: false,
      message: "Failed to fetch dashboard stats",
      error: error.message,
    });
  }
}

module.exports = {
  getReportsByStation,
  getReportsByStationAndStatus,
  getStationDashboardStats,
};
