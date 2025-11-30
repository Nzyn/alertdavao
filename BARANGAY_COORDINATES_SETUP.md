# Davao City Barangay Coordinates - Accurate Setup Guide

## Overview

This guide explains how to update barangay coordinates in AlertDavao using Google Maps Geocoding API for accurate location data.

## Problem

The current hardcoded coordinates in `MapController.php` are estimates and contain duplicates. Multiple barangays share identical coordinates, which makes hotspot visualization inaccurate.

## Solution

Use **Google Maps Geocoding API** to fetch accurate coordinates for all 182 Davao City barangays.

---

## Step 1: Get Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable "Geocoding API"
4. Create API key (Credentials → API Key)
5. Copy your API key

---

## Step 2: Run the Coordinate Fetcher Script

```bash
cd AdminSide/admin
php ../../scripts/fetch_barangay_coordinates.php YOUR_GOOGLE_API_KEY
```

**Replace `YOUR_GOOGLE_API_KEY`** with your actual API key.

### What the script does:

- Geocodes each barangay name using Google Maps API
- Generates a JSON file with accurate coordinates
- Creates a PHP array output (for reference)
- Saves results to `storage/app/barangay_coordinates.json`

### Example output:

```
Fetching coordinates for 182 barangays...
============================================================
[1/182] BAGO APLAYA... ✓ (7.0512, 125.5689)
[2/182] PAMPANGA... ✓ (7.0687, 125.5502)
[3/182] BARANGAY 37-D... ✓ (7.0845, 125.6178)
...
============================================================

✓ Successfully fetched: 182 barangays
✗ Failed: 0 barangays

✓ PHP array saved to: AdminSide/admin/app/Data/davao_barangay_coordinates.php
✓ JSON file saved to: davao_barangay_coordinates_accurate.json
```

---

## Step 3: Move JSON File to Cache Directory

```bash
# Copy the generated JSON to the application storage
cp davao_barangay_coordinates_accurate.json AdminSide/admin/storage/app/barangay_coordinates.json
```

The MapController will automatically use this file when available.

---

## Step 4: Verify Updated Coordinates

1. Navigate to admin dashboard
2. Go to **Hotspot Map** page
3. Verify barangay markers are now in correct locations
4. Check that there are no duplicate coordinates for different barangays

---

## Troubleshooting

### Issue: "API key not valid" error

**Solution:** Ensure:
- Geocoding API is enabled in Google Cloud Console
- API key has no IP restrictions (or whitelist your server IP)
- Rate limits not exceeded (Google allows ~10 requests/second)

### Issue: Some barangays not found

**Possible causes:**
- Misspelled barangay names
- Very small barangays not in Google's database
- Network timeout

**Solution:** 
- Add manual coordinates for missing barangays
- Use local data source (Davao City GIS office)

### Issue: Script taking too long

**Solution:** 
- Script includes 150ms delay between requests to avoid rate limiting
- Total time: ~27 seconds for 182 barangays
- This is normal

---

## Alternative: Use Offline Data

If you don't have a Google API key, request official data from:

**Davao City Planning & Development Office**
- Phone: Contact via Davao City Government website
- Request: Barangay centroid coordinates (GIS shapefile or CSV)

**Philippine Statistics Authority (PSA)**
- Website: https://psa.gov.ph/
- Request: Official barangay geographic codes with coordinates

---

## File Structure

After running the script:

```
alertdavao/
├── scripts/
│   └── fetch_barangay_coordinates.php    ← Run this
├── AdminSide/admin/
│   ├── storage/app/
│   │   └── barangay_coordinates.json     ← Generated (auto-loaded)
│   └── app/Http/Controllers/
│       └── MapController.php             ← Updated with cache check
└── davao_barangay_coordinates_accurate.json  ← Backup copy
```

---

## Maintenance

### To refresh coordinates (e.g., yearly):

```bash
# Re-run the script with API key
php scripts/fetch_barangay_coordinates.php YOUR_GOOGLE_API_KEY

# Move to storage
cp davao_barangay_coordinates_accurate.json AdminSide/admin/storage/app/barangay_coordinates.json

# Clear application cache (if caching enabled)
php AdminSide/admin/artisan cache:clear
```

---

## API Costs

**Google Maps Geocoding API Pricing:**
- Free tier: 25,000 requests/month
- Cost: $0.005 per request after free tier
- 182 barangays = 1 request = negligible cost
- Ideal for one-time or annual updates

---

## Questions?

For implementation support or to discuss alternative data sources, contact your development team.
