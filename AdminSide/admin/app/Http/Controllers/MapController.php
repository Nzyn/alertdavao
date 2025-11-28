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
}
