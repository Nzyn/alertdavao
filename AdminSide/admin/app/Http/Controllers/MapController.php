<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Location;
use Carbon\Carbon;

class MapController extends Controller
{
    public function index()
    {
        return view('view-map');
    }
    
    public function hotspotIndex()
    {
        return view('hotspot-map');
    }
    
    public function getReports(Request $request)
    {
        $query = Report::with(['location', 'user', 'policeStation']);
        
        // Filter by station if user is a police officer
        if (auth()->user() && auth()->user()->role === 'police') {
            $userStationId = auth()->user()->station_id;
            if ($userStationId) {
                $query->where('reports.assigned_station_id', $userStationId);
                \Log::info('Map filtering reports for police station', [
                    'user_id' => auth()->user()->id,
                    'station_id' => $userStationId
                ]);
            } else {
                // Police user without station - show no reports
                \Log::warning('Police user has no station assignment on map', [
                    'user_id' => auth()->user()->id
                ]);
                $query->whereRaw('1 = 0'); // Return empty result
            }
        }
        
        // Apply date filters if provided
        if ($request->has('year') && $request->year != '') {
            $query->whereYear('date_reported', $request->year);
        }
        
        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('date_reported', $request->month);
        }
        
        if ($request->has('day') && $request->day != '') {
            $query->whereDay('date_reported', $request->day);
        }
        
        // Apply date range filter if provided
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('date_reported', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('date_reported', '<=', $request->date_to);
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Apply report type filter
        if ($request->has('report_type') && $request->report_type != '') {
            $query->where('report_type', $request->report_type);
        }
        
        // Apply crime type filter
        if ($request->has('crime_type') && $request->crime_type != '') {
            $query->where('report_type', $request->crime_type);
        }
        
        // Apply barangay filter
        if ($request->has('barangay') && $request->barangay != '') {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('barangay', $request->barangay);
            });
        }
        
        $reports = $query->orderBy('date_reported', 'desc')->get();
        
        // Transform data for map display
        $mapData = $reports->map(function ($report) {
            return [
                'id' => $report->report_id,
                'title' => $report->report_type,
                'description' => $report->description,
                'crime_type' => $report->report_type, // Using report_type as crime_type
                'latitude' => $report->location->latitude,
                'longitude' => $report->location->longitude,
                'location_name' => $report->location->barangay,
                'status' => $report->status,
                'date_reported' => $report->date_reported->timezone('Asia/Manila')->format('Y-m-d H:i:s'),
                'reporter' => $report->user->firstname . ' ' . $report->user->lastname,
                'station_id' => $report->assigned_station_id,
                'station_name' => $report->policeStation ? $report->policeStation->station_name : 'Unassigned',
                'risk_level' => $this->determineRiskLevel($report)
            ];
        });
        
        // Group by location to find overlapping crimes
        $groupedData = $this->groupOverlappingCrimes($mapData);
        
        return response()->json([
            'reports' => $groupedData,
            'total_count' => $reports->count(),
            'stats' => $this->getReportStats($reports),
            'barangays' => $this->getBarangays(),
            'crime_types' => $this->getCrimeTypes()
        ]);
    }
    
    private function groupOverlappingCrimes($reports)
    {
        $grouped = [];
        $processed = [];
        
        foreach ($reports as $report) {
            if (in_array($report['id'], $processed)) {
                continue;
            }
            
            // Find all reports at the same location (within 0.0001 degrees - approximately 11 meters)
            $sameLocation = collect($reports)->filter(function($r) use ($report, $processed) {
                return !in_array($r['id'], $processed) &&
                       abs($r['latitude'] - $report['latitude']) < 0.0001 &&
                       abs($r['longitude'] - $report['longitude']) < 0.0001;
            })->values()->toArray();
            
            if (count($sameLocation) > 1) {
                // Multiple crimes at same location
                $grouped[] = [
                    'id' => 'cluster_' . $report['id'],
                    'latitude' => $report['latitude'],
                    'longitude' => $report['longitude'],
                    'location_name' => $report['location_name'],
                    'is_cluster' => true,
                    'count' => count($sameLocation),
                    'crimes' => $sameLocation
                ];
                
                foreach ($sameLocation as $r) {
                    $processed[] = $r['id'];
                }
            } else {
                // Single crime at this location
                $report['is_cluster'] = false;
                $report['count'] = 1;
                $grouped[] = $report;
                $processed[] = $report['id'];
            }
        }
        
        return $grouped;
    }
    
    private function getBarangays()
    {
        $barangays = Location::select('barangay')
            ->distinct()
            ->whereNotNull('barangay')
            ->where('barangay', '!=', '')
            ->orderBy('barangay')
            ->pluck('barangay')
            ->filter(function($barangay) {
                // Filter out coordinates and encrypted data
                return !preg_match('/^Lat:|^Lng:|^[a-zA-Z0-9+\/]{20,}/', $barangay);
            })
            ->values()
            ->toArray();
            
        return $barangays;
    }
    
    private function getCrimeTypes()
    {
        return Report::select('report_type')
            ->distinct()
            ->whereNotNull('report_type')
            ->where('report_type', '!=', '')
            ->orderBy('report_type')
            ->pluck('report_type')
            ->toArray();
    }
    
    private function determineRiskLevel($report)
    {
        // Define risk levels based on report type or other criteria
        $highRiskTypes = ['emergency', 'violence', 'accident', 'fire'];
        $mediumRiskTypes = ['theft', 'vandalism', 'suspicious'];
        
        if (in_array(strtolower($report->report_type), $highRiskTypes)) {
            return 'high';
        } elseif (in_array(strtolower($report->report_type), $mediumRiskTypes)) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    private function getReportStats($reports)
    {
        $stats = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        foreach ($reports as $report) {
            $riskLevel = $this->determineRiskLevel($report);
            $stats[$riskLevel]++;
        }
        
        return $stats;
    }
    
    /**
     * Get hotspot data for crime rate visualization
     * Aggregates crime data from reports by barangay
     */
    public function getHotspotData(Request $request)
    {
        try {
            // Get all unique barangays from locations table
            $allBarangays = \DB::table('locations')
                ->select('barangay')
                ->distinct()
                ->whereNotNull('barangay')
                ->pluck('barangay')
                ->toArray();
            
            // Count incidents per barangay
            $barangayIncidents = \DB::table('reports')
                ->join('locations', 'reports.location_id', '=', 'locations.location_id')
                ->select('locations.barangay', \DB::raw('COUNT(reports.report_id) as incident_count'))
                ->groupBy('locations.barangay')
                ->pluck('incident_count', 'barangay')
                ->toArray();
            
            $barangayCoordinates = $this->getBarangayCoordinates();
            
            // Build hotspot data for all barangays
            $hotspotData = [];
            foreach ($allBarangays as $barangay) {
                $barangay = trim($barangay ?? 'Unknown');
                $incidents = $barangayIncidents[$barangay] ?? 0;
                $population = 50000; // Default population per barangay
                
                // Get coordinates with fallback
                $lat = 7.1907;
                $lng = 125.4553;
                
                if (isset($barangayCoordinates[$barangay])) {
                    $lat = $barangayCoordinates[$barangay][0];
                    $lng = $barangayCoordinates[$barangay][1];
                } elseif (isset($barangayCoordinates[strtoupper($barangay)])) {
                    $lat = $barangayCoordinates[strtoupper($barangay)][0];
                    $lng = $barangayCoordinates[strtoupper($barangay)][1];
                }
                
                // Crime Rate = (Incidents / Population) × 1000
                $crimeRate = ($incidents / $population) * 1000;
                
                $hotspotData[] = [
                    'name' => $barangay,
                    'incidents' => intval($incidents),
                    'population' => $population,
                    'crime_rate' => round($crimeRate, 2),
                    'latitude' => $lat,
                    'longitude' => $lng
                ];
            }
            
            // Sort by crime rate descending
            usort($hotspotData, function($a, $b) {
                return $b['crime_rate'] <=> $a['crime_rate'];
            });
            
            return response()->json([
                'barangays' => $hotspotData,
                'total_barangays' => count($hotspotData),
                'highest_crime_rate' => !empty($hotspotData) ? $hotspotData[0]['crime_rate'] : 0
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
     * Load barangay crime data from CSV files
     */
    private function loadBarangayDataFromCsv()
    {
        // Try multiple possible paths
        $possiblePaths = [
            base_path('../../davao_barangays.csv'),
            base_path('../../for hotspot/DCPO_Data_barangay_totals (1).csv'),
            storage_path('app/davao_barangays.csv')
        ];
        
        $csvPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $csvPath = $path;
                break;
            }
        }
        
        if (!$csvPath) {
            \Log::warning('Hotspot CSV file not found. Tried paths:', $possiblePaths);
            return [];
        }
        
        $data = [];
        $barangayCoordinates = $this->getBarangayCoordinates();
        
        if (($file = fopen($csvPath, 'r')) !== false) {
            // Skip header
            fgetcsv($file);
            
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) >= 3) {
                    $barangay = trim($row[0]);
                    $crimes = (int) preg_replace('/[^0-9]/', '', $row[1] ?? 0);
                    $population = (int) preg_replace('/[^0-9]/', '', $row[2] ?? 1);
                    
                    $coords = $barangayCoordinates[$barangay] ?? [7.1907, 125.4553];
                    
                    $data[] = [
                        'barangay' => $barangay,
                        'total_crimes' => $crimes,
                        'population' => max($population, 1), // Prevent division by zero
                        'latitude' => $coords[0],
                        'longitude' => $coords[1]
                    ];
                }
            }
            fclose($file);
        }
        
        return $data;
    }
    
    /**
     * Get accurate coordinates for Davao City barangays
     * Loaded from storage/app/barangay_coordinates.json (cached from OSM/Google Maps)
     * 
     * Updated: ' . date('Y-m-d H:i:s') . '
     * Source: OpenStreetMap Nominatim
     */
    private function getBarangayCoordinates()
    {
        // Load cached coordinates from OSM/Google Maps
        $cacheFile = storage_path('app/barangay_coordinates.json');
        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (!empty($cached['barangays'])) {
                $coords = [];
                
                // Map both simplified and full barangay names to coordinates
                foreach ($cached['barangays'] as $brgy) {
                    $simpleName = $brgy['name'];
                    $lat = $brgy['latitude'];
                    $lng = $brgy['longitude'];
                    
                    // Add entry for simplified name
                    $coords[$simpleName] = [$lat, $lng];
                    
                    // Also add common variations to handle both CSV and old data
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

        // Fallback to estimated coordinates
        return [
            'BAGO APLAYA (BRGY IS NOW UNDER PS 17, DCPO)' => [7.0456, 125.5789],
            'PAMPANGA' => [7.0623, 125.5534],
            'BARANGAY 37-D' => [7.0812, 125.6234],
            'BUNAWAN (POB.)' => [7.2353, 125.6428],
            '40-D BOLTON ISLA' => [7.0389, 125.6634],
            'BARANGAY 19-B (POB.) (BRGY UNDER ps 14, DCPO)' => [7.1234, 125.4745],
            'BARANGAY 14-B' => [7.1234, 125.4745],
            'TOMAS MONTEVERDE, SR. (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0645, 125.5401],
            'AGDAO PROPER (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0645, 125.5401],
            'BARANGAY 18-B' => [7.0823, 125.5089],
            'SALAPAWAN' => [7.2156, 125.4923],
            'BARANGAY 28-C' => [7.1867, 125.4612],
            'WANGAN' => [7.1312, 125.3756],
            'INDANGAN (BRGY UNDER PS 13, DCPO)' => [7.2389, 125.3734],
            'BALIOK (BRGY IS NOW UNDER PS 17, DCPO)' => [7.0467, 125.5867],
            'TUNGKALAN' => [7.1934, 125.4278],
            'BUDA' => [7.1867, 125.4867],
            'SUBASTA' => [7.1634, 125.5234],
            'MAGTUOD (BRGY IS NOW UNDER ps 16, DCPO)' => [7.1645, 125.3356],
            '76-A (BUCANA)' => [7.0834, 125.6912],
            '74-A (MATINA CROSSING)' => [7.1712, 125.6167],
            'LACSON' => [7.3234, 125.4612],
            'TIBULOY (BRGY IS NOW UNDER PS 19, DCPO)' => [7.2734, 125.4212],
            'TACUNAN' => [7.1512, 125.5123],
            'SALOY' => [7.2478, 125.5012],
            'BARANGAY 2-A (POB.)' => [7.1034, 125.4834],
            'BINUGAO' => [7.1712, 125.5723],
            'DUMOY (BRGY IS NOW UNDER PS 17, DCPO)' => [7.0356, 125.6389],
            'PACIANO BANGOY (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0867, 125.5212],
            'BARANGAY 5-A (POB.)' => [7.1145, 125.5034],
            'BARANGAY 3-A (POB.)' => [7.1089, 125.4723],
            'BARANGAY 10-A (BRGY UNDER ps 14, DCPO)' => [7.1034, 125.5089],
            'BARANGAY 13-B' => [7.1323, 125.4345],
            'LAPU-LAPU(BRGY IS NOW UNDER PS 18, DCPO)' => [7.0612, 125.5456],
            'SAN ISIDRO' => [7.1123, 125.5678],
            'MAHAYAG' => [7.2134, 125.5712],
            'BARANGAY 11-A' => [7.1156, 125.4312],
            'BARANGAY 39-D (POB.)' => [7.1056, 125.6534],
            'CARMEN' => [7.2312, 125.6089],
            'DALIAO' => [7.1478, 125.4534],
            'BARANGAY 8-A (POB.) (BRGY UNDER ps 14, DCPO)' => [7.1034, 125.5089],
            'BAO JOAQUIN' => [7.1634, 125.5312],
            'BAGANIHAN' => [7.1834, 125.5367],
            'BARANGAY 20-B (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0634, 125.5534],
            'TAMUGAN' => [7.1067, 125.5645],
            'SAN ANTONIO (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0612, 125.5312],
            'MALAGOS' => [7.2089, 125.6412],
            'RIVERSIDE' => [7.1456, 125.6378],
            'TAMAYONG' => [7.1867, 125.6078],
            'MALABOG' => [7.2145, 125.5867],
            'TALOMO RIVER' => [7.1867, 125.5712],
            'WINES' => [7.2345, 125.5134],
            'GUMALANG' => [7.2134, 125.4645],
            'ALAMBRE' => [7.2478, 125.5389],
            'BAYABAS' => [7.2312, 125.6834],
            'ULAS' => [7.2312, 125.5867],
            'MATINA PANGI' => [7.1712, 125.6367],
            'CAWAYAN' => [7.2156, 125.6145],
            'BANGKAS HEIGHTS' => [7.1712, 125.6712],
            'BARANGAY 9-A (POB.) (BRGY UNDER PS 14, DCPO)' => [7.1034, 125.5089],
            'EDEN' => [7.1867, 125.4334],
            'MULIG' => [7.2156, 125.4156],
            'SALAYSAY' => [7.1156, 125.6156],
            'BARANGAY 17-B' => [7.1312, 125.4678],
            'MAGSAYSAY' => [7.1667, 125.5712],
            'LOS AMIGOS (BRGY IS NOW UNDER PS 20, DCPO)' => [7.3145, 125.4867],
            'TAGURANO (BRGY IS NOW UNDER PS 19, DCPO)' => [7.2845, 125.3845],
            'MATINA APLAYA (BRGY UNDER PS 15, DCPO)' => [7.1867, 125.6145],
            'MAA (BRGY IS NOW UNDER ps 16, DCPO)' => [7.2145, 125.3667],
            'TUGBOK (POB.)' => [7.2156, 125.3145],
            'TALOMO (POB.)' => [7.2034, 125.6145],
            'MAPI-ILA' => [7.1634, 125.5867],
            'CATALUNAN GRANDE' => [7.2712, 125.2834],
            'CATALUNAN PEQUENO (BRGY IS NOW UNDER ps 17, DCPO)' => [7.2534, 125.3156],
            'GUMITAN' => [7.2678, 125.4678],
            'BALENGAENG (BRGY IS NOW UNDER PS 20, DCPO)' => [7.3312, 125.5134],
            'TAMBOBONG' => [7.1478, 125.4978],
            'SALUMAY' => [7.1312, 125.3312],
            'MARILOG' => [7.2478, 125.2534],
            'VICENTE HIZON SR.' => [7.1989, 125.5023],
            'MARAPANGI' => [7.1156, 125.3845],
            'PANALUM' => [7.2312, 125.4867],
            'LASANG' => [7.2712, 125.2645],
            'BARANGAY 38-D (OB.)' => [7.0867, 125.6712],
            'BARANGAY 22-C' => [7.1312, 125.4134],
            'CENTRO (SAN JUAN) (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0645, 125.5401],
            'CALINAN (POB.)' => [7.2312, 125.2634],
            'BARANGAY 23-C' => [7.1156, 125.4145],
            'BANTOL' => [7.1667, 125.3867],
            'CABANTIAN' => [7.2156, 125.5867],
            'TAPAK' => [7.1867, 125.3845],
            'AQUINO (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0623, 125.5034],
            'ALFONSO ANGLIONGTO SR' => [7.1867, 125.5867],
            'BARANGAY 32-D' => [7.0623, 125.6312],
            'CATIGAN (BRGY IS NOW UNDER ps 19, DCPO)' => [7.2867, 125.4156],
            'DUTERTE (BRGY IS NOW UNDER PS 18, DCPO)' => [7.0634, 125.5645],
            'SASA' => [7.1156, 125.6867],
            'LUBOGAN' => [7.2312, 125.2967],
            'BAO ESCUELA (BRGY IS NOW UNDER PS 20, DCPO)' => [7.3156, 125.5823],
            'ACACIA (BRGY UNDER PS 13, DCPO)' => [7.2145, 125.3634],
            'MANDUG (BRGY UNDER PS 13, DCPO)' => [7.2312, 125.3312],
            'NEW VALENCIA' => [7.1967, 125.5645],
            'LIZADA' => [7.1312, 125.5134],
            'TORIL (POB.)' => [7.2712, 125.3312],
            'LAMANAN' => [7.1156, 125.3634],
            'FATIMA (BENOWANG)' => [7.1478, 125.3312],
            'SUAWAN (TULI)' => [7.1312, 125.3134],
            'WAAN (BRGY UNDER ps 13, DCPO)' => [7.2145, 125.3867],
            'TALANDANG (BRGY IS NOW UNDER PS 20, DCPO)' => [7.3156, 125.4812],
            'ANGALAN (BRGY IS NOW UNDER PS 20, DCPO)' => [7.3156, 125.5312],
            'BUHANGIN (POB.)' => [7.2712, 125.4134],
            'COMMUNAL' => [7.1667, 125.4145],
            'TIGATTO' => [7.1034, 125.4312],
            'SIRIB' => [7.1645, 125.3312],
            'PAQUIBATO (POB.)' => [7.2712, 125.3845],
            'PANACAN' => [7.1034, 125.6156],
            'MINTAL' => [7.2312, 125.5645],
            'BAGO GALLERA (BRGY IS NOW UNDER PS 17, DCPO)' => [7.0612, 125.6145],
            'BAGO OSHIRO' => [7.0623, 125.6312],
            'MANUEL GUIANGA (BRGY IS NOW UNDER PS 20, DCPO)' => [7.3312, 125.4645],
            'SANTO NINO' => [7.1867, 125.5645],
            'MABUHAY' => [7.2156, 125.5156],
            'BARANGAY 26-C' => [7.1667, 125.4312],
            'BARANGAY 27-C' => [7.1478, 125.4145],
            'ILANG' => [7.2156, 125.5312],
            'BAGUIO PROPER' => [7.2478, 125.2312],
            'TIBUNGCO' => [7.1478, 125.2967],
            'MATINA CROSSING (BRGY IS NOW UNDER PS 15, DCPO)' => [7.1712, 125.6145]
        ];
    }
}
