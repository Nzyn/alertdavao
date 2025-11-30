# Crime Hotspot Weather Forecast - Testing Checklist

## Pre-Testing Setup

- [ ] Clear browser cache (Ctrl+Shift+Delete / Cmd+Shift+Delete)
- [ ] Disable browser cache in DevTools while testing
- [ ] Open console (F12) to check for errors
- [ ] Test on at least 2 browsers (Chrome, Firefox)
- [ ] Test on mobile device or responsive mode
- [ ] Ensure backend API is running
- [ ] Verify database has hotspot data (CSV loaded)

## Visual Tests

### Marker Appearance

- [ ] Red markers show for critical barangays (rate > 8)
- [ ] Orange markers show for medium barangays (rate 4-7)
- [ ] Green markers show for low barangays (rate < 4)
- [ ] Markers have gradient background (light to dark)
- [ ] Markers have white border (3px)
- [ ] Markers have glow/shadow effect
- [ ] Crime rate number visible inside marker
- [ ] Markers are 40x40 pixels
- [ ] Markers don't overlap excessively

### Marker Positioning

- [ ] All 113 barangays display on map (check zoom out)
- [ ] Markers positioned correctly on Davao City
- [ ] Markers distributed across city (not clustered)
- [ ] No markers appear outside Davao City bounds
- [ ] Marker positions consistent between page reloads

### Hotspot Circles (Overlay)

- [ ] Circles render when "Crime Hotspot Overlay" checked
- [ ] Circles disappear when unchecked
- [ ] Circle color matches barangay risk level
- [ ] Circle radius scales with crime rate
  - [ ] Higher rates = larger circles
  - [ ] Lower rates = smaller circles
- [ ] Circles are semi-transparent (can see map below)
- [ ] Circles have colored outline (3px weight)
- [ ] Circle glow visible on high-risk areas

### Pop-ups

#### Marker Pop-ups (Click Marker)

- [ ] Pop-up appears near marker
- [ ] Pop-up contains barangay name
- [ ] Pop-up shows emoji badge (🔴/🟠/🟢)
- [ ] Pop-up displays incidents count
- [ ] Pop-up displays population
- [ ] Pop-up shows crime rate (e.g., "8.54")
- [ ] Pop-up shows rate unit ("per 1,000 residents")
- [ ] Pop-up background color matches risk level
- [ ] Pop-up closes when clicking X or outside

#### Circle Pop-ups (Click Hotspot Circle)

- [ ] Pop-up appears with barangay details
- [ ] Shows emoji risk indicator
- [ ] Displays all statistics
- [ ] Formatted nicely (not cramped)
- [ ] Shows risk level badge with color

### Tooltips

#### Marker Hover
- [ ] Tooltip appears on marker hover
- [ ] Shows barangay name
- [ ] Shows emoji indicator
- [ ] Shows crime rate value
- [ ] Centered above marker
- [ ] Appears immediately (no delay)
- [ ] Disappears on mouse out

#### Circle Hover
- [ ] Tooltip appears on circle hover
- [ ] Shows barangay information
- [ ] Positioned correctly
- [ ] Not blocking map interaction

## Functional Tests

### Map Controls

- [ ] Map/Satellite toggle buttons visible
- [ ] "Map" button starts active
- [ ] Click "Satellite" switches to satellite view
- [ ] Click "Map" switches back to street view
- [ ] View toggle buttons change active state visually
- [ ] Map tiles load correctly in both views

### Hotspot Overlay Toggle

- [ ] Checkbox visible (labeled "Crime Hotspot Overlay")
- [ ] Checkbox starts unchecked
- [ ] Checking enables hotspot circles
- [ ] Unchecking disables hotspot circles
- [ ] Toggle works smoothly without lag
- [ ] Legend shows when overlay enabled
- [ ] Legend hides when overlay disabled

### Map Zoom & Pan

- [ ] Can zoom in/out with mouse wheel
- [ ] Can zoom with +/- buttons
- [ ] Can pan by dragging
- [ ] Zoom range: 11-18 (as configured)
- [ ] Can't zoom beyond limits
- [ ] Can't pan outside Davao City bounds
- [ ] Fit bounds works (shows all markers)

### Statistics Cards

- [ ] 4 cards visible at top of hotspot map
- [ ] "Critical Risk Barangays" card shows count
- [ ] "High Risk Barangays" card shows count
- [ ] "Low Risk Barangays" card shows count
- [ ] "Average Crime Rate" card shows value
- [ ] Cards have correct color indicators
- [ ] Numbers add up correctly (critical + high + low = total)
- [ ] Average calculation is accurate

### Hotspots Ranking Table

- [ ] Table shows at bottom of page
- [ ] Table sorted by crime rate (highest first)
- [ ] Shows rank number (#1, #2, etc.)
- [ ] Shows emoji indicator for risk level
- [ ] Shows barangay name
- [ ] Shows incident count
- [ ] Shows population
- [ ] Shows crime rate
- [ ] Shows risk level badge
- [ ] Can scroll through all entries
- [ ] Clicking row highlights barangay on map

### Data Accuracy

#### Crime Rate Calculation
- [ ] Formula: (Incidents ÷ Population) × 1,000
- [ ] Test example:
  - [ ] 100 incidents ÷ 12,000 pop = 8.33 per 1K ✓
  - [ ] 50 incidents ÷ 15,000 pop = 3.33 per 1K ✓
  - [ ] 200 incidents ÷ 20,000 pop = 10.0 per 1K ✓

#### Risk Level Classification
- [ ] Values > 8 show as RED/CRITICAL ✓
- [ ] Values 4-7 show as ORANGE/HIGH ✓
- [ ] Values < 4 show as GREEN/LOW ✓
- [ ] Boundary values correct (4.0, 8.0) ✓

#### Barangay Coordinates
- [ ] Check 5 random barangays:
  1. [ ] BUNAWAN (POB.) → [7.2353, 125.6428]
  2. [ ] INDANGAN → [7.2389, 125.3734]
  3. [ ] CALINAN (POB.) → [7.2312, 125.2634]
  4. [ ] TALOMO (POB.) → [7.2034, 125.6145]
  5. [ ] BARANGAY 37-D → [7.0812, 125.6234]

## Performance Tests

### Load Time
- [ ] Page loads within 3 seconds
- [ ] Map initializes smoothly
- [ ] No freezing during zoom/pan
- [ ] Hotspot overlay loads in < 2 seconds
- [ ] Pop-ups appear instantly

### Network Activity
- [ ] `GET /api/hotspot-data` returns < 100KB
- [ ] API response time < 1 second
- [ ] Hotspot data cached (no redundant requests)
- [ ] Map tiles load progressively

### Browser DevTools Checks
- [ ] No console errors (check F12 Console)
- [ ] No JavaScript warnings (except external libs)
- [ ] No CSS errors
- [ ] No resource loading failures (404, 500)
- [ ] Network waterfall shows reasonable timing

## Responsive Design Tests

### Desktop (1920x1080)
- [ ] All elements visible
- [ ] Stats cards in single row
- [ ] Hotspots table fully readable
- [ ] Map takes up most of screen space
- [ ] Controls accessible and properly spaced

### Tablet (768x1024)
- [ ] Map scales properly
- [ ] Stats cards stack appropriately
- [ ] Text remains readable
- [ ] Buttons remain clickable
- [ ] No horizontal scrolling needed

### Mobile (375x667)
- [ ] Map visible and interactive
- [ ] Markers tap-able (not too small)
- [ ] Pop-ups readable without zooming
- [ ] Controls accessible
- [ ] Toggle switches work
- [ ] Rankings table scrollable

## Browser Compatibility Tests

### Chrome/Chromium
- [ ] Map loads
- [ ] Markers display correctly
- [ ] Overlays work smoothly
- [ ] DevTools shows no errors

### Firefox
- [ ] Map loads
- [ ] All features functional
- [ ] Performance acceptable
- [ ] No console errors

### Safari
- [ ] Map loads
- [ ] Touch gestures work (pinch zoom, swipe)
- [ ] Animations smooth
- [ ] No styling issues

### Edge
- [ ] All functionality works
- [ ] Performance comparable to Chrome
- [ ] No rendering issues

## Filter Tests (Crime Incident Map)

### Date Filters
- [ ] Year filter works (2023, 2024, 2025)
- [ ] Month filter works (Jan-Dec)
- [ ] Date range (from/to) works
- [ ] Hotspot overlay updates with filter changes
- [ ] "Apply Filters" button functions

### Status Filters
- [ ] Pending filter shows only pending incidents
- [ ] Investigating filter shows only investigating
- [ ] Resolved filter shows only resolved
- [ ] All Status shows everything

### Crime Type Filters
- [ ] Filters show available crime types
- [ ] Selecting type shows only that crime type
- [ ] Multiple selections work (if supported)

### Filter Reset
- [ ] "Reset Filters" clears all selections
- [ ] Map returns to showing all data
- [ ] Statistics recalculate correctly

## API Endpoint Tests

### GET /api/hotspot-data

```bash
curl "http://localhost:8000/api/hotspot-data"
```

Response should contain:
- [ ] barangays array (113 items)
- [ ] Each item has: name, incidents, population, crime_rate, latitude, longitude
- [ ] Crime rates are numerical (0.0-12.0+)
- [ ] Coordinates are valid (lat: 6.9-7.5, lon: 125.2-125.7)
- [ ] Population values are > 1000
- [ ] Incident counts are >= 0
- [ ] Response time < 1 second

### GET /api/reports (with filters)

```bash
# Test with hotspot overlay active
curl "http://localhost:8000/api/reports?year=2024&month=7"
```

Response should contain:
- [ ] reports array with filtered data
- [ ] Each report has: location, date, type, status
- [ ] Hotspot circles update based on filtered data

## Edge Cases & Error Handling

### Data Edge Cases
- [ ] Barangay with 0 incidents (rate = 0.0)
- [ ] Barangay with very high rate (> 10.0/1K)
- [ ] Barangay with very low rate (< 0.5/1K)
- [ ] Barangay name with special characters
- [ ] Very long barangay names truncated properly

### Map Edge Cases
- [ ] Zoom to see all barangays at once
- [ ] Zoom to see individual barangay details
- [ ] Pan to extreme edges of Davao City
- [ ] Click multiple markers in succession
- [ ] Rapid toggle overlay on/off
- [ ] Resize browser window while map open

### Error Scenarios
- [ ] API offline → Show error message
- [ ] Empty barangay data → Show "No data available"
- [ ] Invalid coordinates → Skip or use fallback
- [ ] Missing population → Show warning
- [ ] Network timeout → Retry or show offline message

## Accessibility Tests

### Keyboard Navigation
- [ ] Tab through all interactive elements
- [ ] Space/Enter to click buttons
- [ ] Escape to close pop-ups
- [ ] Arrow keys to pan map

### Screen Reader Compatibility
- [ ] Alt text for all images
- [ ] Labels for form inputs
- [ ] Semantic HTML structure
- [ ] ARIA labels for icons

### Color Accessibility
- [ ] Color scheme works for colorblind users
- [ ] Not relying on color alone to convey meaning
- [ ] Text contrast sufficient (WCAG AA)
- [ ] Emoji used as supplements, not primary info

## Security Tests

- [ ] No XSS vulnerabilities (test with `<script>` in data)
- [ ] No SQL injection (check API parameters)
- [ ] CSRF token present (if applicable)
- [ ] API requires authentication (if sensitive)
- [ ] No sensitive data in client-side code
- [ ] No API keys exposed in JavaScript

## Documentation Tests

- [ ] README updated with new features
- [ ] API documentation current
- [ ] Code comments explain complex logic
- [ ] Troubleshooting guide covers common issues
- [ ] Examples show how to use filters

## Final Integration Tests

### Full User Workflow - Admin
1. [ ] Navigate to `/hotspot-map`
2. [ ] See interactive map with markers
3. [ ] Click a critical barangay (red)
4. [ ] Review detailed information
5. [ ] Check stats cards at top
6. [ ] Scroll to see rankings table
7. [ ] Switch to satellite view
8. [ ] Switch back to map view
9. [ ] Compare with previous data

### Full User Workflow - Police Officer
1. [ ] Navigate to `/map`
2. [ ] See crime incidents on map
3. [ ] Enable hotspot overlay
4. [ ] Check which barangays have highest concentration
5. [ ] Apply date filter for last 30 days
6. [ ] See hotspot circles update
7. [ ] Disable overlay to focus on individual crimes
8. [ ] Use for patrol planning

## Sign-Off Checklist

- [ ] All visual tests passed
- [ ] All functional tests passed
- [ ] Performance acceptable (< 3 sec load time)
- [ ] Mobile responsive
- [ ] Cross-browser compatible
- [ ] No critical bugs
- [ ] Data accuracy verified
- [ ] Documentation complete
- [ ] Ready for production deployment

## Known Limitations (Document)

- [ ] List any issues found that are acceptable
- [ ] Note any TODOs for future improvement
- [ ] Document any browser-specific quirks
- [ ] Note performance thresholds

## Test Results Summary

**Date Tested:** _______________
**Tester Name:** _______________
**Browser:** _______________
**OS:** _______________

**Overall Result:** ☐ PASSED ☐ FAILED

**Critical Issues:** 
- [ ] None
- [ ] (List any)

**Minor Issues:**
- [ ] None
- [ ] (List any)

**Notes:**
_________________________________________________________________
_________________________________________________________________

---

**Ready for Production:** ☐ YES ☐ NO

**Approved By:** _________________  **Date:** _______________
