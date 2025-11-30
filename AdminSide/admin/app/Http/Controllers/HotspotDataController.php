<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotspotDataController extends Controller
{
    /**
     * Get comprehensive hotspot data with yearly crime rates
     * GET /api/hotspot-data
     * 
     * Returns all barangays with:
     * - Yearly crime statistics (2020-2024)
     * - Population data
     * - Crime rates per 1,000 people
     * - Coordinates for mapping
     * - Risk levels (High > 8, Medium 4-7, Low < 4)
     */
    public function getHotspotData(Request $request)
    {
        try {
            \Log::info('getHotspotData called');
            
            // Get current year or use latest available
            $currentYear = intval($request->input('year', date('Y')));
            
            // Load the hardcoded barangay data from CSV
            $barangayData = $this->loadBarangayDataFromCSV();
            
            \Log::info('Loaded ' . count($barangayData) . ' barangays from CSV');
            
            if (empty($barangayData)) {
                \Log::warning('No barangay data loaded from CSV');
                return response()->json([
                    'barangays' => [],
                    'total_barangays' => 0,
                    'highest_crime_rate' => 0,
                    'message' => 'No barangay data available'
                ]);
            }
            
            // Get all barangay locations with coordinates
            $barangayCoordinates = $this->getBarangayCoordinates();
            
            // Process each barangay
             $hotspotData = [];
             foreach ($barangayData as $barangay) {
                 $name = trim($barangay['name'] ?? 'Unknown');
                 $totalCrimes = intval($barangay['total_crimes'] ?? 0);
                 $population = intval($barangay['population'] ?? 50000);
                 
                 // Prevent division by zero
                 if ($population <= 0) {
                     $population = 50000;
                 }
                 
                 // Crime Rate Formula: (Total Incidents / Population) × 1000
                 $crimeRate = ($totalCrimes / $population) * 1000;
                
                // Get coordinates
                $lat = 7.1907;
                $lng = 125.4553;
                
                if (isset($barangayCoordinates[$name])) {
                    $lat = $barangayCoordinates[$name][0];
                    $lng = $barangayCoordinates[$name][1];
                } elseif (isset($barangayCoordinates[strtoupper($name)])) {
                    $lat = $barangayCoordinates[strtoupper($name)][0];
                    $lng = $barangayCoordinates[strtoupper($name)][1];
                }
                
                // Determine risk level
                $riskLevel = 'low';
                if ($crimeRate > 8) {
                    $riskLevel = 'high';
                } elseif ($crimeRate >= 4) {
                    $riskLevel = 'medium';
                }
                
                $hotspotData[] = [
                    'name' => $name,
                    'incidents' => $totalCrimes,
                    'population' => $population,
                    'crime_rate' => round($crimeRate, 2),
                    'risk_level' => $riskLevel,
                    'latitude' => $lat,
                    'longitude' => $lng
                ];
            }
            
            // Sort by crime rate descending (highest risk first)
            usort($hotspotData, function($a, $b) {
                return $b['crime_rate'] <=> $a['crime_rate'];
            });
            
            return response()->json([
                'barangays' => $hotspotData,
                'total_barangays' => count($hotspotData),
                'highest_crime_rate' => !empty($hotspotData) ? $hotspotData[0]['crime_rate'] : 0,
                'risk_thresholds' => [
                    'high' => 'Greater than 8 per 1,000',
                    'medium' => '4 to 7 per 1,000',
                    'low' => 'Less than 4 per 1,000'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getHotspotData: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to load hotspot data',
                'message' => $e->getMessage(),
                'barangays' => []
            ], 500);
        }
    }
    
    /**
     * Load barangay data from hardcoded CSV
     * Contains barangay names, total crimes, and population
     */
    private function loadBarangayDataFromCSV()
    {
        // The admin app is at: D:\Codes\alertdavao\alertdavao\AdminSide\admin
        // We need to go to: D:\Codes\alertdavao\alertdavao\for hotspot
        // That's up 3 directories from admin: admin -> AdminSide -> alertdavao -> up
        $possiblePaths = [
            base_path('../../../for hotspot/DCPO_Data_barangay_totals (1).csv'),
            'D:/Codes/alertdavao/alertdavao/for hotspot/DCPO_Data_barangay_totals (1).csv',
            'D:\\Codes\\alertdavao\\alertdavao\\for hotspot\\DCPO_Data_barangay_totals (1).csv',
        ];
        
        $csvPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $csvPath = $path;
                break;
            }
        }
        
        if (!$csvPath) {
            \Log::warning('Barangay CSV file not found. Tried paths: ' . json_encode($possiblePaths));
            return [];
        }
        
        \Log::info('Loading barangay data from: ' . $csvPath);
        
        $barangays = [];
        $handle = fopen($csvPath, 'r');
        
        if ($handle === false) {
            \Log::error('Cannot open barangay CSV file: ' . $csvPath);
            return [];
        }
        
        // Skip header
        fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                // Remove quotes and parse population (remove commas)
                $name = trim(str_replace('"', '', $row[0]));
                $totalCrimes = intval(str_replace('"', '', $row[1]));
                $population = intval(str_replace(['"', ','], '', $row[2]));
                
                $barangays[] = [
                    'name' => $name,
                    'total_crimes' => $totalCrimes,
                    'population' => $population
                ];
            }
        }
        
        fclose($handle);
        
        \Log::info('Loaded ' . count($barangays) . ' barangays from CSV');
        
        return $barangays;
    }
    
    /**
     * Get barangay coordinates from cached JSON file
     */
    private function getBarangayCoordinates()
    {
        $cacheFile = storage_path('app/barangay_coordinates.json');
        
        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (!empty($cached['barangays'])) {
                $coords = [];
                
                foreach ($cached['barangays'] as $brgy) {
                    $simpleName = $brgy['name'];
                    $lat = $brgy['latitude'];
                    $lng = $brgy['longitude'];
                    
                    // Add entry for multiple name variations
                    $coords[$simpleName] = [$lat, $lng];
                    $coords[strtoupper($simpleName)] = [$lat, $lng];
                    $coords[$simpleName . ' (POB.)'] = [$lat, $lng];
                    
                    // Handle special cases
                    if ($simpleName === 'BARANGAY 19-B') {
                        $coords['BARANGAY 19-B (POB.) (BRGY UNDER ps 14, DCPO)'] = [$lat, $lng];
                    }
                    if ($simpleName === 'BUNAWAN') {
                        $coords['BUNAWAN (POB.)'] = [$lat, $lng];
                    }
                    if ($simpleName === '76-A BUCANA') {
                        $coords['76-A (BUCANA)'] = [$lat, $lng];
                    }
                    if ($simpleName === '74-A MATINA CROSSING') {
                        $coords['74-A (MATINA CROSSING)'] = [$lat, $lng];
                    }
                }
                
                return $coords;
            }
        }
        
        // Fallback coordinates
        return [
            'BAGO APLAYA (BRGY IS NOW UNDER PS 17, DCPO)' => [7.0456, 125.5789],
            'PAMPANGA' => [7.0623, 125.5534],
            'BARANGAY 37-D' => [7.0812, 125.6234],
            'BUNAWAN (POB.)' => [7.2353, 125.6428],
            '40-D BOLTON ISLA' => [7.0389, 125.6634],
        ];
    }
}
