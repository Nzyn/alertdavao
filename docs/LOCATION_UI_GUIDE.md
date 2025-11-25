# Location Selection UI Guide

## User Interface Walkthrough

### Screen 1: Report Form - Location Section

```
┌─────────────────────────────────┐
│  Alert       Report Crime    ✓  │
├─────────────────────────────────┤
│                                 │
│  Location *                     │
│  ┌─────────────────────────────┐│
│  │ Mindanao, Davao Del Sur,    ││
│  │ Davao City, Barangay 10-A   ││
│  │                             ││
│  │ Silver Right Street Marfori,││
│  │ San Rafael Village          ││
│  └─────────────────────────────┘│
│                                 │
│  ✓ Coordinates saved:           │
│    7.1907, 125.4553             │
│                                 │
└─────────────────────────────────┘

[When empty - displays: "Tap to select location..."]
```

### Screen 2: Location Selector Modal - Opened

```
┌──────────────────────────────────┐
│  Select Location              ✕  │
├──────────────────────────────────┤
│                                  │
│  Location Details                │
│                                  │
│  [Region]      [City]            │
│  ┌────────────┐  ┌────────────┐ │
│  │ Mindanao   │  │ Davao City │ │
│  └────────────┘  └────────────┘ │
│                                  │
│  [Province]    [Barangay]        │
│  ┌────────────┐  ┌────────────┐ │
│  │Davao Del Su│  │Poblacion ▼ │ │
│  └────────────┘  └────────────┘ │
│                                  │
│  Street Address *                │
│  Hint: "Silver Right Street      │
│        Marfori, San Rafael..."   │
│                                  │
│  ┌─────────────────────────────┐ │
│  │ Type street address...      │ │
│  │                             │ │
│  │ 🔄 (loading spinner if      │ │
│  │     searching)              │ │
│  └─────────────────────────────┘ │
│                                  │
│  [Address Suggestions - if any]  │
│  ┌─────────────────────────────┐ │
│  │ 📍 Roxas Avenue Davao...    │ │
│  │ 📍 Marfori Extension Davao..│ │
│  │ 📍 Silver Right St Marfori..│ │
│  └─────────────────────────────┘ │
│                                  │
│  ┌────────────────────────────┐  │
│  │  📍 Use My Location  (GPS) │  │
│  └────────────────────────────┘  │
│                                  │
│  📍 Davao City, Barangay 10-A    │
│  Silver Right Street Marfori...  │
│                                  │
│  ┌────────────────────────────┐  │
│  │ ✓ Confirm Location         │  │
│  └────────────────────────────┘  │
│                                  │
└──────────────────────────────────┘
```

### Screen 3: Barangay Dropdown - Opened

```
┌──────────────────────────────────┐
│  Select Location              ✕  │
├──────────────────────────────────┤
│                                  │
│  Barangay *                      │
│  ┌────────────────────────────┐  │
│  │ Poblacion District       ▲ │  │
│  └────────────────────────────┘  │
│  ┌────────────────────────────┐  │
│  │ □ Poblacion District       │  │
│  │ ☑ Buhangin                 │  │
│  │ □ Matina                   │  │
│  │ □ Agdao                    │  │
│  │ □ Lanang                   │  │
│  │ □ Talomo                   │  │
│  │ □ Toril                    │  │
│  │ □ Paquibato                │  │
│  │ □ Tugbok                   │  │
│  │ □ Baguio                   │  │
│  └────────────────────────────┘  │
│     (scroll for more)            │
│                                  │
│  Street Address *                │
│  [text input...]                 │
│                                  │
└──────────────────────────────────┘
```

### Screen 4: Address Suggestions - Showing

```
┌──────────────────────────────────┐
│  Select Location              ✕  │
├──────────────────────────────────┤
│                                  │
│  Street Address *                │
│  ┌──────────────────────────────┐│
│  │ Type silver right street  🔄 ││
│  └──────────────────────────────┘│
│                                  │
│  Search Results                  │
│  ┌──────────────────────────────┐│
│  │ 📍 Silver Right St Marfori   ││
│  │    Davao City, Davao Del Sur ││
│  │ 📍 Silver Right Ext Marfori  ││
│  │    Davao City, Davao Del Sur ││
│  │ 📍 Marfori Extension St      ││
│  │    Davao City, Davao Del Sur ││
│  │ 📍 San Rafael Village Road   ││
│  │    Davao City, Davao Del Sur ││
│  │ 📍 Roxas Avenue Marfori      ││
│  │    Davao City, Davao Del Sur ││
│  └──────────────────────────────┘│
│                                  │
│  [Rest of form below...]         │
│                                  │
└──────────────────────────────────┘
```

### Screen 5: GPS Location Detected

```
┌──────────────────────────────────┐
│  Select Location              ✕  │
├──────────────────────────────────┤
│                                  │
│  [All fields auto-filled]        │
│                                  │
│  Region: Mindanao                │
│  Province: Davao Del Sur         │
│  City: Davao City                │
│  Barangay: Buhangin ✓            │
│                                  │
│  Street Address:                 │
│  198 J.P. Laurel Avenue, Davao   │
│  City, Davao Del Sur             │
│                                  │
│  ✓ Location Detected             │
│  🎯 Buhangin (from GPS)          │
│     J.P. Laurel Avenue, Davao... │
│                                  │
│  ┌────────────────────────────┐  │
│  │ ✓ Confirm Location         │  │
│  └────────────────────────────┘  │
│                                  │
└──────────────────────────────────┘
```

### Screen 6: Summary Card

```
┌──────────────────────────────────┐
│                                  │
│  ✓ Mindanao, Davao Del Sur,      │
│    Davao City, Buhangin          │
│    198 J.P. Laurel Avenue,       │
│    Davao City                    │
│                                  │
│  [Green background, left border] │
│                                  │
└──────────────────────────────────┘
```

---

## Interactive Flow

### Path 1: Manual Barangay Selection
```
1. Tap Location Selector
   ↓
2. Tap Barangay dropdown
   ↓
3. Select "Buhangin"
   ↓
4. See dropdown close, barangay updates
   ↓
5. Type street address
   ↓
6. Tap Confirm Location
```

### Path 2: Address Search with Auto-Detect
```
1. Tap Location Selector
   ↓
2. Start typing street address
   ↓
3. See suggestions appear (after 3 chars)
   ↓
4. Tap a suggestion
   ↓
5. System auto-detects barangay
   ↓
6. If in service area: Barangay updates
   ↓
7. If outside area: Show warning, keep selection
   ↓
8. See summary, Tap Confirm Location
```

### Path 3: GPS Detection
```
1. Tap Location Selector
   ↓
2. Tap "Use My Location" button
   ↓
3. System requests permission
   ↓
4. User allows permission
   ↓
5. System gets GPS coordinates
   ↓
6. Reverse geocode to get address
   ↓
7. Auto-detect barangay from coordinates
   ↓
8. If in service area:
   - All fields auto-fill
   - Show success message
   ↓
   If outside service area:
   - Show error message
   - Ask for manual selection
   ↓
9. See summary, Tap Confirm Location
```

---

## Element Descriptions

### Location Selector Fields

| Field | Type | Behavior | Example |
|-------|------|----------|---------|
| Region | Text (Read-only) | Always "Mindanao" | Mindanao |
| Province | Text (Read-only) | Always "Davao Del Sur" | Davao Del Sur |
| City | Text (Read-only) | Always "Davao City" | Davao City |
| Barangay | Dropdown | Clickable, selectable | Poblacion District |
| Street Address | Text Input | Editable, shows suggestions | Silver Right St Marfori |

### Buttons & Controls

| Control | Function | Icon | Color |
|---------|----------|------|-------|
| Use My Location | GPS detection | 📍 | Dark Blue (#1D3557) |
| Confirm Location | Submit selection | ✓ | Green (#27AE60) |
| Close (X) | Cancel selector | ✕ | Gray |
| Dropdown Arrow | Toggle barangay list | ▼/▲ | Gray |

### Status Indicators

| Indicator | Meaning | Color |
|-----------|---------|-------|
| ✓ Green checkmark | Location set | Green (#27AE60) |
| 🔄 Spinner | Loading suggestions | Blue (#1D3557) |
| 📍 Location icon | Address item | Blue (#1D3557) |
| ✓ In dropdown | Selected barangay | Green (#27AE60) |

---

## Responsive Design Notes

- **Width**: Full screen minus padding (16px each side)
- **Fields**: Stack vertically on mobile
- **Dropdown**: Max height 200px with scroll
- **Suggestions**: Max height 180px with scroll
- **Buttons**: Full width, 12px padding vertical
- **Text**: Scales with device font size
- **Touch targets**: Minimum 44px height (accessibility)

---

## Color Scheme

| Element | Color | Hex |
|---------|-------|-----|
| Primary Text | Dark Gray | #333 |
| Secondary Text | Medium Gray | #666 |
| Placeholder | Light Gray | #999 |
| Borders | Border Gray | #ddd |
| Background | Off White | #f5f5f5 |
| Primary Button | Dark Blue | #1D3557 |
| Success Button | Green | #27AE60 |
| Success Text | Green | #27AE60 |
| Icons | Dark Blue | #1D3557 |
| Summary Background | Light Blue | #f0f8ff |

---

## Accessibility Features

- ✓ Large touch targets (44px minimum)
- ✓ Clear color contrast ratios
- ✓ Icons paired with text labels
- ✓ Helpful placeholder text
- ✓ Error messages are clear
- ✓ Loading states are visible
- ✓ All buttons are clearly labeled
- ✓ Keyboard navigation supported

---

## Example Data Flow

**User selects:**
- Region: Mindanao
- Province: Davao Del Sur
- City: Davao City
- Barangay: Buhangin
- Street Address: 198 J.P. Laurel Avenue

**System displays:**
```
Location: Mindanao, Davao Del Sur, Davao City, Buhangin
Street Address: 198 J.P. Laurel Avenue, Davao City
Coordinates: 7.2010, 125.4450
```

**Report form shows:**
```
Location: Mindanao, Davao Del Sur, Davao City, Buhangin
         198 J.P. Laurel Avenue, Davao City

✓ Coordinates saved: 7.2010, 125.4450
```

**Database stores:**
```sql
INSERT INTO locations (barangay, reporters_address, latitude, longitude)
VALUES ('Buhangin', '198 J.P. Laurel Avenue, Davao City', 7.2010, 125.4450);
```
