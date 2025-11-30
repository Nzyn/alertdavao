# Crime Hotspot Map - Visual Guide

## Color Legend

### Intensity Levels

```
┌─────────────────────────────────────────────────────────┐
│                 CRIME INTENSITY LEVELS                   │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  🔴 CRITICAL  ████████████████████████████  >8 per 1,000 │
│     Dark Red  - Immediate Action Required                │
│               - High crime concentration                 │
│               - Emergency interventions                  │
│                                                           │
│  🔴 HIGH      ████████████████  6-8 per 1,000           │
│     Red       - Urgent attention needed                  │
│               - Increased patrols                        │
│               - Community programs                       │
│                                                           │
│  🟠 MEDIUM    ████████  4-6 per 1,000                   │
│     Amber     - Monitor and respond                      │
│               - Standard policing                        │
│               - Crime prevention                         │
│                                                           │
│  🟢 LOW       ██  <4 per 1,000                           │
│     Green     - Safe neighborhoods                       │
│               - Routine patrols                          │
│               - Quality of life focus                    │
│                                                           │
└─────────────────────────────────────────────────────────┘
```

## Map Layout

```
┌──────────────────────────────────────────────────────────────────┐
│                    HOTSPOT MAP INTERFACE                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                    │
│  Crime Hotspot Analysis Map                                       │
│  Satellite view showing crime density and rates by barangay       │
│                                                                    │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Year: [Select ▼]  Intensity: [Select ▼]  [Apply] [Reset]  │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                    │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                                                              │  │
│  │              SATELLITE MAP (700px height)                   │  │
│  │                                                              │  │
│  │        🔴 Marker circles sized by crime rate               │  │
│  │        🔴 Larger circles = Higher crime rates              │  │
│  │        🔴 Click for details, hover for quick view          │  │
│  │                                                              │  │
│  │              [Zoom Controls]        [Satellite View]        │  │
│  │                                                              │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                    │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐            │
│  │ CRITICAL │ │   HIGH   │ │ MEDIUM   │ │   LOW    │            │
│  │    X     │ │    Y     │ │    Z     │ │    W     │            │
│  │ Avg: 9.5 │ │ Avg: 7.2 │ │ Avg: 5.1 │ │ Avg: 2.3 │            │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘            │
│                                                                    │
│  ═══════════════════════════════════════════════════════════════ │
│  Crime Rate Intensity Legend:                                    │
│  ■ Critical (>8)  ■ High (6-8)  ■ Medium (4-6)  ■ Low (<4)      │
│  ═══════════════════════════════════════════════════════════════ │
│                                                                    │
└──────────────────────────────────────────────────────────────────┘
```

## Marker Visualization

### Small Marker (Low Crime)
```
      ╭─╮
     (   )  <- Barangay with <4 crimes per 1,000
      ╰─╯   <- Small circle = Low crime rate
      🟢    <- Green color
```

### Medium Marker (Medium Crime)
```
      ╭───╮
     (     )  <- Barangay with 4-6 crimes per 1,000
      ╰───╯  <- Medium circle = Medium crime rate
      🟠     <- Amber color
```

### Large Marker (High Crime)
```
      ╭─────╮
     (       )  <- Barangay with 6-8 crimes per 1,000
      ╰─────╯  <- Large circle = High crime rate
      🔴      <- Red color
```

### Largest Marker (Critical Crime)
```
      ╭───────╮
     (         )  <- Barangay with >8 crimes per 1,000
      ╰───────╯  <- Largest circle = Critical crime rate
      🔴        <- Dark red color
```

## Interactive Popups

### Click on a Marker → Detailed Popup

```
┌─────────────────────────────────────────────┐
│                                              │
│  BUNAWAN (POB.)                             │
│  ┌────────────────────────────────────────┐ │
│  │ Crime Rate:     1.26 per 1,000         │ │
│  │ Total Incidents: 29                    │ │
│  │ Population:     23,111                 │ │
│  │                                         │ │
│  │ LOW intensity                           │ │
│  └────────────────────────────────────────┘ │
│                                              │
└─────────────────────────────────────────────┘
```

## Hover Tooltips

### Hover on Marker → Quick Info

```
┌───────────────────────────────┐
│ BARANGAY 37-D                 │
│ Rate: 5.59                    │
└───────────────────────────────┘
```

## Statistics Cards

### Four Summary Cards at Bottom

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│  CRITICAL       │  │  HIGH RISK      │  │  MEDIUM RISK    │  │  LOW RISK       │
│  ╔═══╗          │  │  ╔════╗         │  │  ╔═══════╗      │  │  ╔═════════════╗ │
│  ║ 12│ Areas    │  │  ║ 18 ║ Areas  │  │  ║  45  ║ Areas │  │  ║   65      ║ Areas │
│  ╚═══╝          │  │  ╚════╝         │  │  ╚═══════╝      │  │  ╚═════════════╝ │
│  Avg: 9.8/1,000 │  │  Avg: 7.1/1,000 │  │  Avg: 5.2/1,000 │  │  Avg: 2.4/1,000 │
│                 │  │                 │  │                 │  │                 │
│  🔴 Dark Red    │  │  🔴 Red         │  │  🟠 Amber       │  │  🟢 Green       │
└─────────────────┘  └─────────────────┘  └─────────────────┘  └─────────────────┘
```

## Crime Rate Examples

### Low Crime Area
```
BARANGAY: Marilog
├─ Incidents: 17
├─ Population: 20,852
├─ Calculation: (17 ÷ 20,852) × 1000 = 0.81
├─ Crime Rate: 0.81 per 1,000
├─ Intensity: LOW 🟢
└─ Safety Status: Safe neighborhood
```

### Medium Crime Area
```
BARANGAY: Barangay 37-D
├─ Incidents: 32
├─ Population: 5,726
├─ Calculation: (32 ÷ 5,726) × 1000 = 5.59
├─ Crime Rate: 5.59 per 1,000
├─ Intensity: MEDIUM 🟠
└─ Safety Status: Moderate crime level
```

### High Crime Area
```
BARANGAY: Barangay 13-B
├─ Incidents: 23
├─ Population: 443
├─ Calculation: (23 ÷ 443) × 1000 = 51.91
├─ Crime Rate: 51.91 per 1,000
├─ Intensity: CRITICAL 🔴
└─ Safety Status: Emergency intervention needed
```

## Controls Guide

### Filter Section
```
┌────────────────────────────────────────────────┐
│  Year Selection          Intensity Selection    │
│  ┌──────────────┐       ┌──────────────────┐   │
│  │ All Years ▼  │       │ All Intensities▼ │   │
│  ├──────────────┤       ├──────────────────┤   │
│  │ 2024         │       │ Critical (>8)    │   │
│  │ 2023         │       │ High (6-8)       │   │
│  │ 2022         │       │ Medium (4-6)     │   │
│  │ 2021         │       │ Low (<4)         │   │
│  │ 2020         │       └──────────────────┘   │
│  └──────────────┘                              │
│                                                 │
│  [Apply Filters]         [Reset]               │
└────────────────────────────────────────────────┘
```

## Mobile View

### How It Appears on Mobile

```
╔════════════════════════════╗
║   HOTSPOT MAP              ║
║ Satellite view Davao City  ║
╠════════════════════════════╣
║ Year: [▼]                  ║
║ Intensity: [▼]             ║
║ [Apply] [Reset]            ║
╠════════════════════════════╣
║                            ║
║     SATELLITE MAP          ║
║     (500px height)         ║
║                            ║
║  🟢 🟠 🔴 🔴 Markers       ║
║                            ║
╠════════════════════════════╣
║ CRITICAL │HIGH│MEDIUM│LOW  ║
║    X     │ Y  │  Z   │ W   ║
╠════════════════════════════╣
║ Legend: 🔴🟠🟢             ║
╚════════════════════════════╝
```

## User Journey

### Step 1: Open Hotspot Map
```
Dashboard → Admin Sidebar → Click "Hotspot Map"
```

### Step 2: View Map
```
Satellite map loads with 130+ barangay markers
Each marker shows crime rate through:
- Color (intensity level)
- Size (larger = higher crime)
```

### Step 3: Filter Data (Optional)
```
Select Year → Select Intensity → Click Apply
OR
Just hover/click to see details
```

### Step 4: Analyze Results
```
Click marker → See detailed popup
Hover marker → See quick tooltip
Check cards → See summary statistics
```

### Step 5: Make Decisions
```
Use heat visualization to:
- Identify hotspot areas
- Allocate police resources
- Plan prevention programs
- Monitor trends
```

## Color Accessibility

### Distinguishable by Color-Blind Users

```
NORMAL VISION:
🔴 Dark Red    🔴 Red    🟠 Amber    🟢 Green
Critical      High      Medium      Low

DEUTERANOPIA (Green-Red Colorblind):
- Still distinguishable by saturation/brightness
- Legend provides text labels
- Intensity text labels in popups
```

## Print-Friendly View

### How Map Looks When Printed

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DAVAO CITY CRIME HOTSPOT ANALYSIS MAP
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[SATELLITE MAP IMAGE]

Crime Rate Intensity Legend:
■ Critical (>8 per 1,000)    ■ High (6-8 per 1,000)
■ Medium (4-6 per 1,000)     ■ Low (<4 per 1,000)

STATISTICS SUMMARY:
Critical Areas: 12 (Average: 9.8)
High-Risk Areas: 18 (Average: 7.1)
Medium-Risk Areas: 45 (Average: 5.2)
Low-Risk Areas: 55 (Average: 2.4)

Date Generated: December 1, 2025
Data Source: DCPO Crime Records
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## Real-World Usage Scenarios

### Scenario 1: Resource Allocation Meeting
```
Police Chief views hotspot map
↓
Identifies 12 critical areas (dark red)
↓
Allocates 40% of patrol resources to critical areas
↓
30% to high-risk (red) areas
↓
20% to medium-risk (amber) areas
↓
10% to low-risk (green) areas
```

### Scenario 2: Community Safety Planning
```
Mayor sees hotspot map
↓
Identifies medium-risk areas (amber) as intervention opportunity
↓
Plans community engagement programs
↓
Coordinates with barangay officials
↓
Implements crime prevention initiatives
```

### Scenario 3: Performance Monitoring
```
Police Superintendent checks map
↓
Compares current year to previous year
↓
Identifies improving areas (green) - celebrates progress
↓
Focuses on declining areas (becoming red) - investigates cause
↓
Adjusts strategies based on trends
```

## Keyboard Shortcuts (Future Enhancement)

```
Function              Shortcut    Effect
─────────────────────────────────────────
Zoom In              +           Zoom to next level
Zoom Out             -           Zoom to previous level
Reset View           Home        Center map on Davao
Help                 ?           Show help overlay
Export               Ctrl+S      Save as image
```

## Data Interpretation Tips

### ✅ DO
- Use as trends, not absolutes
- Compare areas relative to each other
- Consider population density context
- Combine with other data sources
- Review regularly (annual updates)

### ❌ DON'T
- Assume color directly reflects danger level
- Ignore barangay characteristics
- Make policy decisions from single data point
- Compare with other cities without adjusting formula
- Trust outdated population data

## Quick Reference Card

```
╔═════════════════════════════════════════╗
║  HOTSPOT MAP QUICK REFERENCE            ║
╠═════════════════════════════════════════╣
║                                         ║
║  Formula: (Incidents / Population)×1000║
║                                         ║
║  Colors:  🔴Dark  🔴 Red  🟠Amber 🟢Green
║  Rates:   >8     6-8    4-6      <4
║                                         ║
║  Marker Size = Crime Rate              ║
║  Click = Details                       ║
║  Hover = Quick Info                    ║
║  Filter = Focus Analysis               ║
║  Cards = Summary Stats                 ║
║                                         ║
║  Access: Admin Dashboard → Hotspot Map ║
║                                         ║
╚═════════════════════════════════════════╝
```

---

This visual guide provides quick reference for understanding and using the crime hotspot mapping system. Refer to documentation files for detailed technical information.
