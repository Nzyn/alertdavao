# Crime Hotspot Weather Forecast - Visual Style Guide

## Design Philosophy

The crime hotspot system uses a **weather forecast analogy** to help users quickly understand risk levels intuitively. Just as weather apps use color intensity to show danger levels (thunderstorms vs. light rain), our system uses visual intensity to show crime concentration.

## Color Palette

### Primary Risk Colors

#### 🔴 Red - Critical Risk
```
Primary:    #dc2626  (Red-600 - Dark red)
Light:      #fca5a5  (Red-300 - Light red)
Accent:     #fee2e2  (Red-100 - Very light red)
Text:       #991b1b  (Red-900 - Very dark red)
```

**Usage:**
- High crime rate barangays (> 8 per 1,000)
- Critical alerts
- Primary warnings
- Top hotspots

**Example Applications:**
```
Marker: Red gradient (DC2626 → FCA5A5)
Circle: Dark red outline, semi-transparent fill
Badge: Red text on light red background
Glow: Red shadow/glow around elements
```

#### 🟠 Orange - High Risk
```
Primary:    #f59e0b  (Amber-500 - Orange)
Light:      #fed7aa  (Amber-200 - Light orange)
Accent:     #fef3c7  (Amber-100 - Very light orange)
Text:       #92400e  (Amber-900 - Very dark orange)
```

**Usage:**
- Medium crime rate barangays (4-7 per 1,000)
- Caution areas
- Monitored zones
- Secondary hotspots

#### 🟢 Green - Low Risk
```
Primary:    #10b981  (Emerald-600 - Dark green)
Light:      #a7f3d0  (Emerald-300 - Light green)
Accent:     #dcfce7  (Emerald-100 - Very light green)
Text:       #166534  (Emerald-900 - Very dark green)
```

**Usage:**
- Low crime rate barangays (< 4 per 1,000)
- Safe areas
- Clear zones
- Well-maintained neighborhoods

### Supporting Colors

```
White:      #ffffff  (Borders, text on colored backgrounds)
Gray:       #6b7280  (Neutral text, secondary information)
Dark Gray:  #1f2937  (Primary text, headings)
Light Gray: #f3f4f6  (Backgrounds, dividers)
```

## Typography

### Font Stack
```css
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", sans-serif;
```

### Scale

| Usage | Size | Weight | Line Height |
|-------|------|--------|------------|
| Heading (Page Title) | 24px | 700 | 1.2 |
| Heading (Section) | 18px | 700 | 1.3 |
| Subheading | 16px | 600 | 1.4 |
| Body Text | 14px | 400 | 1.5 |
| Small Text | 12px | 400 | 1.4 |
| Tiny Text | 10px | 500 | 1.3 |
| Label | 11px | 600 | 1.2 |

## Spacing System

**Base Unit:** 8px (multiples of 8)

| Scale | Value | Usage |
|-------|-------|-------|
| xs | 4px | Small gaps |
| sm | 8px | Component padding |
| md | 16px | Section margins |
| lg | 24px | Card padding |
| xl | 32px | Major sections |
| 2xl | 48px | Page margins |

## Component Styles

### Marker (Leaflet Marker)

#### Dimensions
```
Size: 40x40 pixels
Border: 3px solid white
Icon Anchor: [20, 20] (centered)
Popup Anchor: [0, -20] (above marker)
```

#### Visual Effects

**Base Style:**
```css
border-radius: 50%;  /* Perfect circle */
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3),
            0 0 20px [COLOR]80;  /* Glow effect */
```

**Gradient (High Risk Example):**
```css
background: radial-gradient(
    circle at 30% 30%, 
    #fca5a5,  /* Light color (highlight) */
    #dc2626   /* Dark color (base) */
);
```

**Transition:**
```css
transition: all 0.3s ease;
```

#### State Changes
- **Default:** Gradient + glow
- **Hover:** Slight scale increase (105%)
- **Active (Clicked):** Slightly larger glow
- **Cluster:** Reduced size with count badge

### Pop-up (Information Card)

#### Layout
```
┌──────────────────────────────┐
│ Barangay Name    🔴 CRITICAL │  ← Header with emoji badge
├──────────────────────────────┤
│ 📊 Total Incidents:      145  │
│ 👥 Population:        17,234  │
│                              │
│ ┌────────────────────────┐  │
│ │     8.54              │  │  ← Crime rate highlight box
│ │  per 1,000 residents  │  │
│ └────────────────────────┘  │
└──────────────────────────────┘
```

#### Styling
```css
padding: 1rem;
background: white;
border-radius: 8px;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
max-width: 280px;
```

#### Badge Styling
```css
border-radius: 12px;
padding: 2px 8px;
font-size: 0.75rem;
font-weight: 700;
white-space: nowrap;
```

### Hotspot Circle (Overlay)

#### Properties
```css
weight: 3px;           /* Border thickness */
opacity: 0.9;         /* Border opacity */
fillOpacity: 0.4;     /* Fill opacity */
radius: 1500-4500m;   /* Dynamic based on crime rate */
className: hotspot-circle hotspot-circle-[LEVEL];
```

#### Dynamic Radius Calculation
```javascript
baseRadius = 1500m;  // 1.5km base
radiusScale = Math.min(crimeRate / 8, 3);
radius = baseRadius * (0.8 + radiusScale);

// Example:
// Crime rate 2/1K:   radius = 1.5 * 0.8  = 1200m
// Crime rate 8/1K:   radius = 1.5 * 1.8  = 2700m
// Crime rate 12/1K:  radius = 1.5 * 3.8  = 5700m (capped)
```

### Tooltip (Hover Text)

#### Style
```css
background: white;
border-radius: 4px;
padding: 0.5rem;
box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
font-size: 0.85rem;
font-weight: 500;
white-space: nowrap;
z-index: 1000;
```

#### Content Format
```
🔴
Barangay Name
8.54/1K

─── or ───

🟠
Talomo District
5.23/1K
```

### Statistics Card

#### Layout
```
┌─────────────────────────┐
│ CRITICAL BARANGAYS      │  ← Label (uppercase, gray)
│                         │
│        23               │  ← Value (large, bold)
│                         │
│ > 8 per 1,000 people   │  ← Description
└─────────────────────────┘
```

#### Styling
```css
background: white;
padding: 1.5rem;
border-radius: 10px;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
border-left: 4px solid [COLOR];  /* Left border indicates risk */
```

### Risk Badge

#### Types

**High Risk:**
```
Background: #fee2e2 (Red-100)
Text: #991b1b (Red-900)
Label: 🔴 CRITICAL
```

**Medium Risk:**
```
Background: #fef3c7 (Amber-100)
Text: #92400e (Amber-900)
Label: 🟠 HIGH
```

**Low Risk:**
```
Background: #dcfce7 (Emerald-100)
Text: #166534 (Emerald-900)
Label: 🟢 LOW
```

#### CSS
```css
padding: 0.5rem 1rem;
border-radius: 20px;
font-size: 0.875rem;
font-weight: 700;
display: inline-block;
text-transform: uppercase;
letter-spacing: 0.05em;
```

## Emoji Usage

### Risk Indicators
- **🔴** = Critical (High Crime)
- **🟠** = High Risk (Medium Crime)
- **🟢** = Low Risk (Safe)

### Information Icons
- **📊** = Statistics/Incidents
- **👥** = Population
- **🗺️** = Location/Map
- **⚠️** = Warning/Alert
- **✓** = Confirmed/OK

### Usage Rules
1. Always pair emoji with text label
2. Use for quick visual scanning
3. Don't rely on emoji alone (accessibility)
4. Consistent sizing and positioning
5. Size: usually 1.1-1.2em

## Interactive States

### Marker States

**Default**
```css
opacity: 1;
scale: 1;
box-shadow: 0 4px 12px rgba(0,0,0,0.3), 0 0 20px [COLOR]80;
```

**Hover**
```css
opacity: 1;
scale: 1.15;
box-shadow: 0 6px 16px rgba(0,0,0,0.4), 0 0 30px [COLOR];
cursor: pointer;
```

**Active (Popup Shown)**
```css
opacity: 1;
scale: 1.2;
box-shadow: 0 8px 20px rgba(0,0,0,0.5), 0 0 40px [COLOR];
```

### Button States

**Default**
```css
background: white;
border: 1px solid #d1d5db;
color: #374151;
cursor: pointer;
```

**Hover**
```css
background: #f3f4f6;
border-color: [PRIMARY-COLOR];
color: [PRIMARY-COLOR];
```

**Active**
```css
background: [PRIMARY-COLOR];
border-color: [PRIMARY-COLOR];
color: white;
```

## Animations & Transitions

### Fade In (Page Load)
```css
animation: fadeIn 0.3s ease-in-out;

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
```

### Scale On Hover (Markers)
```css
transition: transform 0.2s ease, box-shadow 0.2s ease;

&:hover {
    transform: scale(1.15);
}
```

### Slide Up (Pop-ups)
```css
animation: slideUp 0.2s ease-out;

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

### Duration Standards
- **Quick feedback:** 200ms
- **Smooth transition:** 300ms
- **Page transition:** 500ms

## Accessibility Considerations

### Color Contrast
- **Text on backgrounds:** Minimum WCAG AA (4.5:1)
- **UI components:** Minimum 3:1 for non-text
- **Red/Green only:** Avoid, always add text/pattern

### Size Minimums
- **Tap targets:** 44x44 pixels (mobile)
- **Text size:** Minimum 12px
- **Line height:** Minimum 1.4em

### Semantic HTML
```html
<button> for buttons (not <div>)
<a> for links
<label> for form labels
<h1-h6> for headings (proper hierarchy)
```

### ARIA Labels
```html
<div role="img" aria-label="Critical risk area">
  🔴
</div>
```

## Responsive Breakpoints

| Device | Width | Marker Size | Card Width |
|--------|-------|-----------|-----------|
| Mobile | 375px | 36px | 90% |
| Tablet | 768px | 38px | 85% |
| Desktop | 1024px+ | 40px | 280px |

## Print Styles

```css
@media print {
    /* Hide interactive elements */
    .control-btn { display: none; }
    
    /* Simplify colors */
    .hotspot-circle { opacity: 1; }
    
    /* Optimize for grayscale */
    filter: grayscale(0); /* Keep colors if color printer */
}
```

## Dark Mode Support (Future)

```css
@media (prefers-color-scheme: dark) {
    /* Adjust backgrounds */
    background: #1f2937;
    
    /* Invert text colors */
    color: #f3f4f6;
    
    /* Reduce opacity of glows */
    box-shadow: 0 0 15px [COLOR]40;
}
```

## Examples

### Weather Analogy

**Weather Forecast:**
```
🔴 SEVERE WEATHER ALERT
  Red circle with bold glow
  Heavy rain expected
```

**Crime Hotspot:**
```
🔴 CRITICAL AREA  
  Red marker with glow
  High crime expected
```

**Similar Visual Language:** Users instantly recognize the danger level

## Brand Consistency

- **Logo Position:** Top-left of map
- **Color Usage:** Consistent across all pages
- **Typography:** Helvetica/System fonts only
- **Spacing:** Always multiples of 8px
- **Border Radius:** 6px, 8px, or 12px (never odd numbers)

## Testing Visual Design

- [ ] Color contrast ratios verified
- [ ] Responsive layout tested
- [ ] Emoji render correctly on all OS
- [ ] Print preview looks good
- [ ] Dark mode preview works
- [ ] Animation performance smooth (60 FPS)
- [ ] Touch targets properly sized

---

**Design System Version:** 1.0
**Last Updated:** December 2024
**Status:** ✅ APPROVED
