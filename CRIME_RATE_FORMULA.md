# Crime Rate Formula Documentation

## Standard Worldwide Formula

The crime hotspot mapping uses the **standard international crime rate formula**:

```
Crime Rate per 1,000 = (Incidents in a Year / Population in that Year) × 1000
```

This is the globally accepted method used by:
- FBI (United States)
- Interpol
- UN Office on Drugs and Crime
- National police departments worldwide

## Why This Formula?

**Normalization:** Raw incident counts are misleading because larger populations naturally have more crimes.

**Example Problem:**
- City A: 100 crimes, 100,000 people = ???
- City B: 100 crimes, 50,000 people = ???

Without normalization, both look equally bad. But City B is actually twice as dangerous per capita.

**Solution:** Crime Rate normalizes by population, making fair comparison possible.

## Calculation Steps

### Step 1: Get the Data
From CSV file, extract for each barangay:
- Name
- Total Incidents (Total Crimes)
- Population

### Step 2: Divide
```
Incidents ÷ Population
```

### Step 3: Multiply by 1,000
```
(Result) × 1000
```

### Step 4: Round to 2 Decimals
```
Math.round(result × 100) / 100
```

## Real Examples from Davao CSV

### Example 1: Low Crime Area
**Barangay:** BUNAWAN (POB.)
```
Incidents: 29
Population: 23,111

Crime Rate = (29 / 23,111) × 1000
           = 0.001256 × 1000
           = 1.26 crimes per 1,000 people
```
**Interpretation:** In every 1,000 residents of Bunawan, 1.26 experience crime per year.
**Intensity:** LOW (Green)

---

### Example 2: Medium Crime Area
**Barangay:** BARANGAY 37-D
```
Incidents: 32
Population: 5,726

Crime Rate = (32 / 5,726) × 1000
           = 0.005586 × 1000
           = 5.59 crimes per 1,000 people
```
**Interpretation:** In every 1,000 residents of Barangay 37-D, 5.59 experience crime per year.
**Intensity:** MEDIUM (Amber)

---

### Example 3: High Crime Area
**Barangay:** 40-D BOLTON ISLA
```
Incidents: 28
Population: 2,190

Crime Rate = (28 / 2,190) × 1000
           = 0.012785 × 1000
           = 12.79 crimes per 1,000 people
```
**Interpretation:** In every 1,000 residents of 40-D Bolton Isla, 12.79 experience crime per year.
**Intensity:** CRITICAL (Dark Red)

---

### Example 4: Very Low Crime Area
**Barangay:** BARANGAY 3-A (POB.)
```
Incidents: 23
Population: 531

Crime Rate = (23 / 531) × 1000
           = 0.043313 × 1000
           = 43.31 crimes per 1,000 people
```
**Interpretation:** In every 1,000 residents of Barangay 3-A, 43.31 experience crime per year.
**Intensity:** CRITICAL (Dark Red)

Note: Despite smaller population, very high crime rate indicates serious problem.

---

## Intensity Level Classification

Based on crimes per 1,000 people:

| Level | Range | Color | Description | Priority |
|-------|-------|-------|-------------|----------|
| CRITICAL | >8.0 | #7f1d1d | Emergency situation | 🔴 Immediate Action |
| HIGH | 6.0-8.0 | #dc2626 | Serious concern | 🟠 Urgent Action |
| MEDIUM | 4.0-6.0 | #f59e0b | Significant issue | 🟡 Action Needed |
| LOW | <4.0 | #10b981 | Acceptable level | 🟢 Monitor |

## Data Calculation in Code

### PHP Implementation (MapController.php)

```php
// Load CSV data
$barangayData = $this->loadBarangayDataFromCsv();

// Process each barangay
foreach ($barangayData as $barangay) {
    $incidents = $barangay['total_crimes'] ?? 0;
    $population = $barangay['population'] ?? 1;
    
    // Apply formula
    $crimeRate = ($incidents / $population) * 1000;
    
    // Round to 2 decimal places
    $crimeRate = round($crimeRate, 2);
    
    // Store result
    $hotspotData[] = [
        'name' => $barangay['barangay'],
        'incidents' => $incidents,
        'population' => $population,
        'crime_rate' => $crimeRate
    ];
}
```

### JavaScript Display (hotspot-map.blade.php)

```javascript
// Load data from API
fetch('/api/hotspot-data')
    .then(response => response.json())
    .then(data => {
        // Get color based on crime rate
        const { color, intensity } = getColorIntensity(crimeRate);
        
        // Display on map
        L.circleMarker(latlng, {
            radius: Math.max(10, Math.min(30, crimeRate * 3)),
            fillColor: color,
            fillOpacity: 0.75
        }).bindPopup(`Crime Rate: ${crimeRate.toFixed(2)}`);
    });
```

## Important Considerations

### 1. Division by Zero Prevention
If population is 0, default to 1 to prevent errors:
```php
$population = max($population, 1);
```

### 2. Rounding
Always round to 2 decimal places for readability:
```php
$crimeRate = round($crimeRate, 2);
```

### 3. Data Accuracy
Crime rates are only as accurate as source data:
- Incident counts from DCPO records
- Population data from census
- Updates are periodic (not real-time)

### 4. Interpretation Tips
- **Higher rate ≠ Worse police:** Could indicate:
  - More accurate crime reporting
  - Better-policed areas (crimes detected)
  - Actual higher crime concentration
  
- **Lower rate = More safe:** Generally indicates:
  - Fewer crimes occurring
  - Safer neighborhoods
  - Could also indicate under-reporting

## Comparison with Other Methods

### Alternative: Per 10,000
Some countries use per 10,000 instead of 1,000:
```
Crime Rate = (Incidents / Population) × 10,000
```
Less common, results in larger numbers.

### Alternative: Per 100,000
Some use per 100,000:
```
Crime Rate = (Incidents / Population) × 100,000
```
Used by FBI for US national statistics.

### Why We Use Per 1,000
- Smaller numbers easier to understand (1.26 vs 126)
- Better for visualizing relative risk
- Clearer communication to public
- Standard in many countries

## Verification Examples

### Checking Calculation

**Data from CSV:**
- BAGO APLAYA: 33 incidents, 18,515 population

**Manual Calculation:**
1. 33 ÷ 18,515 = 0.001781
2. 0.001781 × 1000 = 1.781
3. Round to 2 decimals = 1.78

**Intensity Assignment:**
- 1.78 is < 4.0
- Therefore: LOW intensity (Green)

### Top 10 Highest Crime Rate Barangays

Calculated from CSV totals data:

1. BARANGAY 3-A (POB.) - 43.31 (Critical)
2. SIRIB - 15.94 (Critical)
3. BARANGAY 26-C - 7.28 (High)
4. BARANGAY 32-D - 9.19 (Critical)
5. BARANGAY 27-C - 5.22 (Medium)
6. 40-D BOLTON ISLA - 12.79 (Critical)
7. TIBUNGCO - 0.20 (Low)
8. BARANGAY 13-B - 51.91 (Critical)
9. PAQUIBATO (POB.) - 4.19 (Medium)
10. BARANGAY 38-D (OB.) - 12.64 (Critical)

## What This Means for Police Operations

### Critical Areas (>8 per 1,000)
- Require immediate investigation
- Need increased patrols
- Priority for crime prevention
- Consider community programs

### High Areas (6-8 per 1,000)
- Regular monitoring needed
- Standard patrols
- Prevention initiatives
- Data collection ongoing

### Medium Areas (4-6 per 1,000)
- Routine monitoring
- Standard responses
- Community engagement
- Trend tracking

### Low Areas (<4 per 1,000)
- Minimal incidents
- Community policing focus
- Quality of life issues
- Prevention focus

## Data Reliability

### Factors Affecting Accuracy

**Positive:**
- Based on verified incident reports
- Standardized calculation method
- Multiple barangays for comparison
- Long-term data (multiple years)

**Limitations:**
- Population data may be outdated
- Not real-time (historical snapshot)
- Only counts reported crimes
- May not reflect unreported incidents

### Best Practice
- Use as trends, not absolutes
- Combine with other data sources
- Consider context (population changes)
- Review annually for changes

## Questions & Answers

**Q: Does high crime rate mean high crime volume?**
A: Not necessarily. It's normalized by population. A small area might have high rate but low volume.

**Q: Can crime rate go above 1,000?**
A: Theoretically no. Would mean every person experienced crime, impossible.

**Q: Should all barangays have same target rate?**
A: No. Target varies by development level, population density, and city standards.

**Q: How often should this be updated?**
A: Ideally annually with new crime and census data.

**Q: Can crime rates go to zero?**
A: Theoretically yes, practically unlikely unless area is unpopulated.

## References

- **FBI Crime Statistics:** Uses per 100,000 formula
- **Interpol Standards:** Recommends normalization by population
- **UN-UNODC:** Uses per 100,000 globally
- **Philippine Statistics Authority:** Standard calculation method

## Conclusion

The crime rate formula normalizes crime data by population, allowing fair comparison between areas of different sizes. This implementation uses the standard worldwide formula, ensuring data is internationally comparable and professionally calculated.

The intensity levels (critical, high, medium, low) provide immediate visual communication of risk levels, enabling quick decision-making by police and administrators.
