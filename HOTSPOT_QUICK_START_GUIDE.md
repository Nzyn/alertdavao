# Crime Hotspot Mapping - Quick Start Guide

## What's New?

The crime hotspot mapping system now displays accurate barangay locations with a **weather-forecast-style** visualization. Think of it like a weather radar showing risk intensity instead of precipitation.

## Color Coding (Like Weather Forecasts)

### 🔴 Red - CRITICAL (High Risk)
- Crime rate: **> 8 per 1,000 residents**
- Equivalent to: Severe Weather Alert
- Action: Enhanced patrol, heightened awareness

### 🟠 Orange - HIGH RISK (Medium)
- Crime rate: **4-7 per 1,000 residents**
- Equivalent to: Storm Warning
- Action: Regular monitoring, community alerts

### 🟢 Green - LOW RISK
- Crime rate: **< 4 per 1,000 residents**
- Equivalent to: Clear Weather
- Action: Routine patrols

## How to Access

### Admin Dashboard
1. Go to **Crime Hotspot Map** (`/hotspot-map`)
2. See interactive map with all barangays marked
3. View ranking of top hotspots below the map

### Crime Incident Map
1. Go to **View Map** (`/map`)
2. Click "Crime Hotspot Overlay" checkbox to enable
3. See weather-forecast circles overlay over individual crimes

## Visual Indicators

### On the Map

#### Marker Design
```
   ┌─────────┐
   │    8.5  │     ← Gradient circle showing crime rate
   │   ●●●   │     ← Glow effect for emphasis
   └─────────┘
```

- **Large numbers** = High crime rate
- **Red glow** = Critical area
- **White border** = Clear visibility

#### Interactive Features
- **Hover:** Shows barangay name + crime rate
- **Click:** Displays detailed statistics
  - Number of incidents
  - Population
  - Exact crime rate
  - Risk classification

### Circle Overlay (Hotspot Map)
```
  ╔═══════════════╗
  ║ HIGH RISK    ║
  ║   (8.54/1K)  ║   ← Larger, more intense circles
  ║    AREA      ║      show higher crime rates
  ║   Red Glow   ║
  ╚═══════════════╝
```

## Understanding the Stats

### Crime Rate Formula
```
Crime Rate = (Total Incidents ÷ Population) × 1,000
```

**Example:**
- Barangay with 100 incidents
- Population of 12,000 residents
- Crime Rate = (100 ÷ 12,000) × 1,000 = **8.33 per 1,000**

### Statistics Cards
At the top of the Hotspot Map page:

```
┌─────────────────────┬────────────────────┬──────────────────┬────────────────────┐
│ CRITICAL BARANGAYS  │ HIGH RISK AREAS    │ LOW RISK AREAS   │ AVERAGE CRIME RATE │
│                     │                    │                  │                    │
│        15           │        28          │        70        │       5.34          │
│                     │                    │                  │ per 1,000 residents│
│ > 8 per 1,000      │ 4-7 per 1,000      │ < 4 per 1,000    │                    │
└─────────────────────┴────────────────────┴──────────────────┴────────────────────┘
```

## Top Hotspots Ranking

Shows the 10-20 most dangerous barangays in order:

```
#1  🔴 BUNAWAN (POB.)
    Incidents: 145    Population: 17K    Rate: 8.54/1K    [CRITICAL]

#2  🔴 INDANGAN
    Incidents: 132    Population: 14K    Rate: 9.43/1K    [CRITICAL]

#3  🟠 BARANGAY 37-D
    Incidents: 89     Population: 19K    Rate: 4.68/1K    [HIGH]
    
... and so on
```

**Sort:** By crime rate (highest first)

## Using Filters

### On the Map (Crime Incident Map)

**Available Filters:**
- Year (2023, 2024, 2025)
- Month (Jan-Dec)
- Date Range (From/To)
- Status (Pending, Investigating, Resolved)

**Example:**
To see hotspots only from July 2024:
1. Set Year = 2024
2. Set Month = July
3. Click "Apply Filters"

The map updates to show only relevant crimes, and the hotspot overlay adjusts accordingly.

## Map Controls

### Layer Toggle (Top Right)

**Map View** - Standard street map (recommended for details)
**Satellite View** - Aerial imagery (good for geography context)

Click either button to switch views instantly.

### Hotspot Overlay Toggle

**Checkbox:** "Crime Hotspot Overlay"

- **Checked:** Shows barangay-level risk circles
- **Unchecked:** Shows only individual crime incidents

### Legend

Always visible at the bottom showing color meanings:
- 🔴 Red = High risk
- 🟠 Orange = Medium risk
- 🟢 Green = Low risk

## Common Tasks

### Find the Most Dangerous Area
1. Go to Hotspot Map
2. Look at top of page - "Statistics Cards"
3. Number showing critical barangays
4. Scroll down to "Crime Hotspots Ranking"
5. #1 is the most dangerous

### Check a Specific Barangay
1. Click on any red/orange/green marker on the map
2. Pop-up appears with details:
   - Incidents count
   - Population
   - Exact crime rate
   - Risk level

### Compare Hotspots
1. Open Crime Hotspots Ranking section
2. Hover over rows to see highlighted details
3. Compare incident counts and rates
4. Click on a marker to see its location on map

### Monitor Changes Over Time
1. Use date filters on Crime Incident Map
2. Switch dates to see hotspot changes
3. Toggle overlay on/off to compare layers

## Dashboard Insights

### What High Numbers Mean
- **High crime rate in low population area:** More concentrated danger
- **Low crime rate in high population area:** Better public safety
- **Growing hotspots:** Areas with increasing incidents

### What to Watch
- 🔴 Critical barangays (red) → Priority patrol areas
- 🟠 High-risk barangays (orange) → Monitor closely
- 🟢 Green areas → Maintain current security

## Mobile Viewing

- Map is **responsive** - works on phones/tablets
- **Pinch to zoom** on touch devices
- **Swipe to pan** around the map
- **Tap markers** for information (instead of click)
- **Rotate device** for better visibility

## Troubleshooting

### Map Loads Slowly
- Disable hotspot overlay if not needed
- Try satellite view (sometimes faster)
- Clear browser cache and reload

### Markers Not Showing
- Ensure Leaflet/OpenStreetMap load (check console)
- Try resetting map bounds (zoom out then in)
- Verify barangay coordinates in database

### Hotspot Circles Too Large/Small
- Circles scale with crime rate
- Very high rates (>10/1K) = very large circles
- This is intentional for visual emphasis

### Pop-up Information Missing
- Check that API endpoint `/api/hotspot-data` returns data
- Verify population data in CSV is current
- Ensure crime rate calculation in controller is correct

## Advanced Features

### Popup Emoji Guide
- 🔴 = Critical (>8/1K)
- 🟠 = High (4-7/1K)
- 🟢 = Low (<4/1K)
- 📊 = Statistics icon
- 👥 = Population icon

### CSS Classes (For Customization)
- `.hotspot-marker-high` - High risk markers
- `.hotspot-circle-high` - High risk circles
- `.leaflet-tooltip-high` - High risk tooltips
- Similar for `medium` and `low`

### Data Download
- Hotspot data available at `/api/hotspot-data` (JSON)
- All reports available at `/api/reports` (JSON)
- Use these for custom analysis/reports

## Performance Tips

- Map works best with < 500 visible crime markers
- Hotspot overlay adds ~50-100ms load time
- Satellite view slower than street map
- Filter by date to improve performance

## Best Practices

1. **Use Hotspot Map** for overview/analysis
2. **Use Crime Incident Map** for specific incident details
3. **Check rankings** to identify priority areas
4. **Review trends** by applying date filters
5. **Share findings** using screenshot or data export

## Contact & Support

For issues with:
- **Map display:** Check browser console (F12)
- **Data accuracy:** Verify CSV/database sources
- **Performance:** Contact system admin
- **Feature requests:** Document specific use case

---

**Updated:** December 2024
**Version:** 2.0 (Weather Forecast Style)
