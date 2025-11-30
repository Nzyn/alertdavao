# Crime Hotspot Overlay - Visual Guide

## Map Layout with Hotspot Overlay

```
┌─────────────────────────────────────────────────────────────────┐
│  View Map - All Crimes                                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  📍 Map View | 🛰️ Satellite  ┌────────────────────────┐        │
│                             │ ☑️ Crime Hotspot     │        │
│  ┌──────────────────────────┤    Overlay         │        │
│  │                          └────────────────────────┘        │
│  │         [Satellite View]                                   │
│  │                                                             │
│  │      🟢 Low Risk        🔴 High Risk                       │
│  │      (green circles)    (red circles)                      │
│  │                                                             │
│  │    🟢 ○    ○    🟠 ○                                      │
│  │           🟠    ○                                         │
│  │      ○    ○    🔴 ○    ○                                 │
│  │          🟠                                               │
│  │                                                             │
│  │    🔴 Large circle = High crime rate                      │
│  │    🟠 Medium circle = Medium crime rate                   │
│  │    🟢 Small circle = Low crime rate                       │
│  │                                                             │
│  │  [Semi-transparent circles overlay on satellite]           │
│  │                                                             │
│  └──────────────────────────────────────────────────────────┘
│                                ┌─────────────────────────┐    │
│                                │ Crime Intensity         │    │
│                                ├─────────────────────────┤    │
│                                │ 🟢 Low (< 4 per 1K)   │    │
│                                │ 🟠 Medium (4-7/1K)    │    │
│                                │ 🔴 High (> 8 per 1K)  │    │
│                                └─────────────────────────┘    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Circle Appearance (Weather Forecast Style)

### Individual Circle

```
         High Crime Area
         Crime Rate: 12.5/1K
              │
       ┌──────▼──────┐
       │   🔴 RED    │
       │  CIRCLE     │ ← Red border (2px)
       │   HOTSPOT   │ ← Semi-transparent red fill
       └─────────────┘
              │
         Radius: ~5km
         (scaled by crime rate)
```

### Circle Colors & Meanings

```
🟢 GREEN (Low Crime)           🟠 ORANGE (Medium Crime)       🔴 RED (High Crime)
┌──────────────┐               ┌──────────────┐               ┌──────────────┐
│ #10b981      │               │ #f59e0b      │               │ #dc2626      │
│ Opacity: 0.6 │               │ Opacity: 0.7 │               │ Opacity: 0.8 │
│              │               │              │               │              │
│  <4 per 1K   │               │  4-7 per 1K  │               │  >8 per 1K   │
│              │               │              │               │              │
│ Small Circle │               │ Med Circle   │               │ Large Circle │
│ Low Risk     │               │ Medium Risk  │               │ High Risk    │
└──────────────┘               └──────────────┘               └──────────────┘

Safe Areas                     Moderate Caution              High Alert
Recommend                      Increased Police             Deploy Resources
```

---

## Interaction Examples

### 1. Hovering Over Circle

```
           Before Hover
         ┌─────────────┐
         │             │
         │   🟠 Circle │
         │  (no label) │
         │             │
         └─────────────┘

           After Hover
         ┌──────────────────┐
         │  BUNAWAN (POB.)  │
         │  Risk: LOW       │ ← Tooltip appears
         │  (1.26/1K)       │
         └──────────────────┘
              ▲
              │
         (follows mouse)
```

### 2. Clicking on Circle

```
         Map View
    ┌─────────────────┐
    │   🟠 Circle     │
    │    (clicked)    │ ← Click here
    └─────────────────┘
             │
             ▼
         ┌──────────────────────────┐
         │ BUNAWAN (POB.)           │
         │                          │
         │ Crime Rate: 1.26 per K   │
         │ Incidents: 29            │
         │ Population: 23,111       │
         │                          │
         │        [Close]           │
         └──────────────────────────┘
         
         (Popup with full details)
```

---

## Toggle Control Location & Appearance

### Top-Right Corner of Map

```
     ┌──────────────────────────────────────┐
     │                                      │
     │        [Map View] [Satellite] ┌──┐  │
     │                              │☑︎│  │
     │                              │CH│  │ ← Toggle Box
     │                              └──┘  │
     │  Crime Hotspot Overlay             │
     │                                      │
     │ [Satellite View with Hotspots]      │
     │                                      │
     │ 🟢 circles show low crime            │
     │ 🟠 circles show medium crime         │
     │ 🔴 circles show high crime           │
     │                                      │
     │                     ┌──────────────┐ │
     │                     │ Crime Intensity│
     │                     ├──────────────┤ │ ← Legend Box
     │                     │ 🟢 Low  Rates│ │
     │                     │ 🟠 Med  Rates│ │
     │                     │ 🔴 High Rates│ │
     │                     └──────────────┘ │
     │                                      │
     └──────────────────────────────────────┘
```

---

## Size Scaling Visualization

### Crime Rate vs Circle Size

```
Crime Rate: 2.0          Crime Rate: 4.0          Crime Rate: 8.0
(Low Risk)               (Medium Risk)            (High Risk)

  🟢 ○                  🟠  ○                   🔴   ○
  Small                Medium                  Large
  ~2.5km               ~3.5km                  ~4.5km

Radius = 2km × (0.5 + crime_rate/8)

Formula Examples:
2.0 → 2km × (0.5 + 0.25) = 1.5km   ✓ Small
4.0 → 2km × (0.5 + 0.50) = 2.0km   ✓ Medium  
8.0 → 2km × (0.5 + 1.00) = 3.0km   ✓ Large
12.0 → 2km × (0.5 + 1.50) = 4.0km  ✓ Very Large (capped at 3x)
```

---

## Color Gradient Visual

### Crime Intensity Scale (Like Weather Radar)

```
        Crime Rate Scale

0      2      4      6      8      10     12+
├──────┼──────┼──────┼──────┼──────┼──────┤
🟢              🟠              🔴

GREEN          ORANGE         RED
Low Risk       Medium Risk    High Risk
Safe           Caution        Alert

Weather Analogy:
🟢 Clear (Light rain)
🟠 Cloudy (Moderate rain)
🔴 Stormy (Heavy rain)
```

---

## Full Map View Example

### Before Overlay Enabled

```
         /View Map\

     [Map] [Satellite]     ← View toggle

  Filters: Year, Month...  ← Crime filters

  ┌────────────────────────────────────┐
  │                                    │
  │   [Satellite/Street View]          │
  │   🚩 Individual Crime Markers      │
  │   (colored by crime type)          │
  │                                    │
  │   No overlay circles yet           │
  │                                    │
  └────────────────────────────────────┘

  Crime Type Legends: 8 types shown
```

### After Overlay Enabled

```
         /View Map\

     [Map] [Satellite]  ☑ Crime Hotspot Overlay

  Filters: Year, Month...

  ┌────────────────────────────────────┐
  │         ☑ Crime Hotspot Overlay    │
  │                                    │
  │   [Satellite/Street View]          │
  │   🚩 Crime Markers                 │
  │   + 🟢 Hotspot Circles (Overlay)   │
  │   + 🟠 Semi-Transparent            │
  │   + 🔴 Weather-Forecast Style      │
  │                                    │
  │                                    │
  │            Legend                  │
  │      🟢 Low (< 4/1K)              │
  │      🟠 Med (4-7/1K)              │
  │      🔴 High (> 8/1K)             │
  └────────────────────────────────────┘
```

---

## Popup Detail View

### Circle Click → Popup

```
           MAP VIEW
         ┌─────────────┐
         │  🔴 Circle  │
         │  (clicked)  │
         └─────────────┘
              │
              ▼ Click event
              
    ┌────────────────────────────┐
    │ BUNAWAN (POB.)            │
    ├────────────────────────────┤
    │                            │
    │ Crime Rate: 1.26 per 1,000 │ ← Key stat
    │ Incidents: 29              │ ← Count
    │ Population: 23,111         │ ← Denominator
    │                            │
    └────────────────────────────┘
    
    (White popup with shadow)
    (Shows on click, closable)
```

---

## Legend Position & Content

### Bottom-Right Corner (When Overlay Enabled)

```
                          ┌─────────────────────┐
                          │  Crime Intensity    │
                          ├─────────────────────┤
                          │                     │
                          │  🟢 Low (< 4/1K)  │
                          │  🟠 Medium (4-7)  │
                          │  🔴 High (> 8/1K) │
                          │                     │
                          └─────────────────────┘

Legend Properties:
- White background
- Shadow effect
- 220px min-width
- 1.25rem padding
- 8px border-radius
- Appears when toggle on
- Disappears when toggle off
```

---

## Transparency Example

### Semi-Transparent Overlay Effect

```
         SATELLITE VIEW
        (Actual Street View)

    ┌────────────────────────┐
    │  🗺️ City Streets       │
    │     🏢 Buildings       │
    │     🌳 Parks           │
    │                        │
    └────────────────────────┘
              ▲ Base map
              │
              │ (Overlay added)
              │
         ┌────────────────────────┐
         │  🔴 Hotspot Circles   │
         │     (50% opacity)     │ ← Can see through
         │  🟠 Still see map     │ ← Map visible underneath
         │  🟢 below circles     │ ← Geographic context
         └────────────────────────┘

Result: Professional weather-forecast appearance
```

---

## Animation Sequence

### Toggle Sequence

```
1. Initial State
   ☐ Crime Hotspot Overlay (unchecked)
   No circles visible

2. User Clicks Checkbox
   ↓ onClick event triggered
   
3. System Loads Data
   "Loading hotspot data..." (50ms)
   
4. Circles Appear
   ☑ Crime Hotspot Overlay (checked)
   Circles fade in on map
   
5. Legend Appears
   Bottom-right box slides in
   Shows color meanings
   
6. Ready for Interaction
   User can hover/click circles
   Get details and statistics

7. User Unchecks
   ☐ Crime Hotspot Overlay
   Circles fade out
   Legend disappears
   Back to normal map view
```

---

## Real-World Example

### Bunawan Area

```
DATA:
Barangay: BUNAWAN (POB.)
Incidents: 29
Population: 23,111
Crime Rate: (29/23,111) × 1000 = 1.26 per 1,000

VISUALIZATION:
🟢 Small green circle
   Latitude: 7.1333
   Longitude: 125.5833
   Radius: ~1.5km
   
APPEARANCE:
- Green circle (low risk)
- Small size (low rate)
- Semi-transparent fill
- Can see map beneath
- On satellite imagery

INTERACTION:
Click → Shows popup with statistics
Hover → Shows "BUNAWAN (POB.) - Risk: LOW (1.26/1K)"
```

### High-Risk Area

```
DATA:
Barangay: 40-D BOLTON ISLA
Incidents: 28
Population: 2,190
Crime Rate: (28/2,190) × 1000 = 12.79 per 1,000

VISUALIZATION:
🔴 Large red circle
   Latitude: 7.0417
   Longitude: 125.6583
   Radius: ~4.5km
   
APPEARANCE:
- Red circle (high risk)
- Large size (high rate)
- Semi-transparent red fill
- Can see map beneath
- Prominent on satellite
- Draws attention

INTERACTION:
Click → Shows popup with high crime rate
Hover → Shows "40-D BOLTON ISLA - Risk: HIGH (12.79/1K)"
```

---

## Mobile View

### Responsive Design on Mobile

```
Portrait Mode (Mobile)
┌─────────────────┐
│ View Map        │
├─────────────────┤
│  ☑ Hotspot      │ ← Toggle moved to top
│                 │
│ [Map View]      │
│ 🔴 🟠 🟢 ○     │
│ Circles adapted │
│ for mobile      │
│                 │
│ Legend          │
│ (vertical)      │
│ - Low           │
│ - Medium        │
│ - High          │
├─────────────────┤
│ Crime Legends:  │
│ (8 crime types) │
└─────────────────┘
```

---

## Summary of Visual Elements

| Element | Location | Style | Purpose |
|---------|----------|-------|---------|
| **Toggle** | Top-right | Checkbox + label | Enable/disable overlay |
| **Circles** | Over map | Color-coded, semi-transparent | Show crime hotspots |
| **Legend** | Bottom-right | White box, shows 3 colors | Explain color meaning |
| **Popups** | Center (on click) | White box with shadow | Show detailed stats |
| **Tooltips** | Near cursor (on hover) | Small floating box | Show quick info |

---

## Color Psychology

| Color | Real-World Meaning | Our Use | Psychology |
|-------|-------------------|---------|-------------|
| 🟢 Green | Safety, Go | Low Crime | Safe, calm |
| 🟠 Orange | Caution, Slow | Med Crime | Alert, moderate |
| 🔴 Red | Danger, Stop | High Crime | Urgent, danger |

Similar to:
- Traffic lights (green→orange→red)
- Weather warnings (clear→moderate→severe)
- Heat maps (cool→warm→hot)

---

This is what users will see when they enable the Crime Hotspot Overlay on the satellite view of the map!
