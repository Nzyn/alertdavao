<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\CrimeForecast;
use App\Models\CrimeAnalytics;

class StatisticsController extends Controller
{
    private $sarimaApiUrl = 'http://localhost:8001';

    /**
     * Check if SARIMA API is running
     */
    private function isSarimaApiRunning()
    {
        try {
            $response = Http::timeout(2)->get("{$this->sarimaApiUrl}/");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Auto-start SARIMA API if not running (development only)
     */
    private function autoStartSarimaApi()
    {
        if ($this->isSarimaApiRunning()) {
            return true;
        }

        // Only auto-start in local development
        if (!app()->environment('local')) {
            return false;
        }

        try {
            $projectRoot = base_path('..');
            $pythonScript = $projectRoot . DIRECTORY_SEPARATOR . 'start_sarima.py';
            
            if (file_exists($pythonScript)) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Windows
                    pclose(popen("start /B python \"$pythonScript\"", "r"));
                } else {
                    // Linux/Mac
                    exec("python3 \"$pythonScript\" > /dev/null 2>&1 &");
                }
                
                // Wait a moment for the API to start
                sleep(3);
                return $this->isSarimaApiRunning();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to auto-start SARIMA API: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Display the statistics page
     */
    public function index()
    {
        // Try to auto-start SARIMA API if in development
        $this->autoStartSarimaApi();
        
        return view('statistics');
    }

    /**
     * Get crime forecast from pre-generated SARIMA CSV file
     * Uses sarima_forecast.csv (12 months forecast from CrimeDAta.csv)
     */
    public function getForecast(Request $request)
    {
        $horizon = $request->input('horizon', 12);
        
        try {
            // Load sarima_forecast.csv
            $forecastPath = storage_path('app/sarima_forecast.csv');
            
            if (!file_exists($forecastPath)) {
                throw new \Exception('SARIMA forecast file not found');
            }
            
            $forecastData = [];
            $file = fopen($forecastPath, 'r');
            fgetcsv($file); // Skip header: Date,Forecast_Crimes,Lower_CI,Upper_CI
            
            $count = 0;
            while (($row = fgetcsv($file)) !== false && $count < $horizon) {
                if (count($row) >= 4) {
                    $forecastData[] = [
                        'date' => $row[0],
                        'forecast' => round(floatval($row[1]), 2),
                        'lower_ci' => round(floatval($row[2]), 2),
                        'upper_ci' => round(floatval($row[3]), 2)
                    ];
                    $count++;
                }
            }
            fclose($file);
            
            return response()->json([
                'status' => 'success',
                'data' => $forecastData,
                'horizon' => $horizon,
                'model' => 'SARIMA(1,1,1)(1,1,1)[12]',
                'source' => 'Pre-generated from CrimeDAta.csv'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load SARIMA forecast data',
                'details' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Get crime statistics from CSV files
     * Uses CrimeDAta.csv and DCPO_5years_monthly.csv
     */
    public function getCrimeStats()
    {
        try {
            // Load CrimeDAta.csv for monthly trends (Year, Month, Count, Date)
            $monthlyCsvPath = storage_path('app/CrimeDAta.csv');
            $monthlyStats = [];
            
            if (file_exists($monthlyCsvPath)) {
                $file = fopen($monthlyCsvPath, 'r');
                fgetcsv($file); // Skip header
                
                while (($row = fgetcsv($file)) !== false) {
                    if (count($row) >= 4) {
                        $monthlyStats[] = (object)[
                            'year' => intval($row[0]),
                            'month' => intval($row[1]),
                            'count' => intval($row[2])
                        ];
                    }
                }
                fclose($file);
            }

            // Load DCPO_5years_monthly.csv for detailed crime breakdown
            $dcpoPath = storage_path('app/DCPO_5years_monthly.csv');
            $crimesByType = [];
            $crimesByLocation = [];
            
            if (file_exists($dcpoPath)) {
                $file = fopen($dcpoPath, 'r');
                fgetcsv($file); // Skip header: gu,Date,offense,Count
                
                while (($row = fgetcsv($file)) !== false) {
                    if (count($row) >= 4) {
                        $barangay = trim($row[0]);
                        $offense = trim($row[2]);
                        $count = intval($row[3]);
                        
                        // Aggregate by crime type
                        if (!isset($crimesByType[$offense])) {
                            $crimesByType[$offense] = 0;
                        }
                        $crimesByType[$offense] += $count;
                        
                        // Aggregate by location
                        if (!isset($crimesByLocation[$barangay])) {
                            $crimesByLocation[$barangay] = 0;
                        }
                        $crimesByLocation[$barangay] += $count;
                    }
                }
                fclose($file);
            }
            
            // Transform to expected format
            $crimeByType = collect($crimesByType)
                ->map(function($count, $type) {
                    return (object)['type' => $type, 'count' => $count];
                })
                ->sortByDesc('count')
                ->values()
                ->take(15);
            
            $crimeByLocation = collect($crimesByLocation)
                ->map(function($count, $location) {
                    return (object)['location' => $location, 'count' => $count];
                })
                ->sortByDesc('count')
                ->values()
                ->take(10);

            // Calculate overview stats
            $totalCrimes = array_sum($crimesByType);
            $latestMonthData = collect($monthlyStats)->last();
            $secondLatestMonthData = collect($monthlyStats)->slice(-2, 1)->first();
            
            $totalThisMonth = $latestMonthData ? $latestMonthData->count : 0;
            $totalLastMonth = $secondLatestMonthData ? $secondLatestMonthData->count : 0;
            
            $percentChange = $totalLastMonth > 0 
                ? round((($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100, 2)
                : 0;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'monthly' => $monthlyStats,
                    'byType' => $crimeByType,
                    'byStatus' => [], // Not available in CSV
                    'byLocation' => $crimeByLocation,
                    'overview' => [
                        'total' => $totalCrimes,
                        'thisMonth' => $totalThisMonth,
                        'lastMonth' => $totalLastMonth,
                        'percentChange' => $percentChange
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch crime statistics',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export crime data as CSV for SARIMA model
     * Only exports VALID reports
     */
    public function exportCrimeData()
    {
        try {
            $data = DB::table('reports')
                ->where('is_valid', 'valid')
                ->select(
                    DB::raw('YEAR(created_at) as Year'),
                    DB::raw('MONTH(created_at) as Month'),
                    DB::raw('COUNT(*) as Count'),
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m-01") as Date')
                )
                ->groupBy('Year', 'Month', 'Date')
                ->orderBy('Year', 'asc')
                ->orderBy('Month', 'asc')
                ->get();

            $csv = "Year,Month,Count,Date\n";
            foreach ($data as $row) {
                $csv .= "{$row->Year},{$row->Month},{$row->Count},{$row->Date}\n";
            }

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="CrimeData_' . date('Y-m-d') . '.csv"');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export crime data',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get barangay-level crime statistics from CrimeReports.csv
     */
    public function getBarangayCrimeStats()
    {
        try {
            $csvPath = storage_path('app/CrimeReports.csv');
            
            if (!file_exists($csvPath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'CrimeReports.csv file not found'
                ], 404);
            }

            // Read and parse CSV
            $file = fopen($csvPath, 'r');
            $header = fgetcsv($file); // Skip header: gu,typeOfPlace,dateCommitted,timeCommitted,offense,lat,lng
            
            $barangayData = [];
            
            while (($row = fgetcsv($file)) !== false) {
                $barangay = trim($row[0]); // 'gu' column
                
                // Aggregate by barangay
                if (!isset($barangayData[$barangay])) {
                    $barangayData[$barangay] = 0;
                }
                $barangayData[$barangay]++;
            }
            
            fclose($file);
            
            // Convert to array format for frontend
            $result = [];
            foreach ($barangayData as $barangay => $totalCrimes) {
                $result[] = [
                    'barangay' => $barangay,
                    'total_crimes' => $totalCrimes
                ];
            }
            
            // Sort by total crimes (descending)
            usort($result, function($a, $b) {
                return $b['total_crimes'] - $a['total_crimes'];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $result,
                'total_barangays' => count($result),
                'total_crimes' => array_sum($barangayData)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load barangay crime statistics',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

