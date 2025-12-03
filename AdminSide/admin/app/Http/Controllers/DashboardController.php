<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the authenticated user's role
        $userRole = auth()->check() ? auth()->user()->role : 'guest';
        
        // Check if user is super admin (alertdavao.ph)
        $isSuperAdmin = auth()->check() && auth()->user()->email === 'alertdavao.ph';
        
        // Load crime data from CSV and create individual reports
        $csvPath = base_path('../../AdminSide/sarima_api/data/CrimeDAta.csv');
        $csvReports = [];
        $totalCrimesFromCSV = 0;
        
        if (file_exists($csvPath)) {
            $file = fopen($csvPath, 'r');
            $headers = fgetcsv($file); // Skip header row
            
            $reportId = 1;
            while (($row = fgetcsv($file)) !== false) {
                $count = (int)$row[2];
                $date = $row[3];
                $year = $row[0];
                $month = $row[1];
                
                // Create individual report entries for each crime in the count
                for ($i = 0; $i < $count; $i++) {
                    $csvReports[] = [
                        'id' => $reportId++,
                        'year' => $year,
                        'month' => $month,
                        'date' => $date,
                        'type' => 'Crime Incident', // Generic type from CSV
                        'status' => 'recorded',
                        'title' => "Crime Report #{$reportId} - " . date('F Y', strtotime($date))
                    ];
                }
                
                $totalCrimesFromCSV += $count;
            }
            fclose($file);
        }
        
        // Fetch dashboard statistics
        if ($isSuperAdmin) {
            // Super admin sees ALL reports including unassigned (no CSV inflation)
            $totalReports = DB::table('reports')->count();
            $pendingReports = DB::table('reports')->where('status', 'pending')->count();
            $investigatingReports = DB::table('reports')->where('status', 'investigating')->count();
            $resolvedReports = DB::table('reports')->where('status', 'resolved')->count();
            
            \Log::info('Dashboard stats for super admin', [
                'user_id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'total' => $totalReports,
                'pending' => $pendingReports,
                'investigating' => $investigatingReports,
                'resolved' => $resolvedReports
            ]);
        } elseif ($userRole === 'police' && auth()->check()) {
            // Police users only see reports from their assigned station
            $stationId = auth()->user()->station_id;
            
            if ($stationId) {
                $totalReports = DB::table('reports')->where('assigned_station_id', $stationId)->count();
                $pendingReports = DB::table('reports')
                    ->where('assigned_station_id', $stationId)
                    ->where('status', 'pending')
                    ->count();
                $investigatingReports = DB::table('reports')
                    ->where('assigned_station_id', $stationId)
                    ->where('status', 'investigating')
                    ->count();
                $resolvedReports = DB::table('reports')
                    ->where('assigned_station_id', $stationId)
                    ->where('status', 'resolved')
                    ->count();
                    
                \Log::info('Dashboard stats for police station', [
                    'user_id' => auth()->user()->id,
                    'station_id' => $stationId,
                    'total' => $totalReports,
                    'pending' => $pendingReports,
                    'investigating' => $investigatingReports,
                    'resolved' => $resolvedReports
                ]);
            } else {
                // Police user without station - show zero stats
                $totalReports = 0;
                $pendingReports = 0;
                $investigatingReports = 0;
                $resolvedReports = 0;
                
                \Log::warning('Police user has no station assignment', [
                    'user_id' => auth()->user()->id
                ]);
            }
        } else {
            // Admin and barangay users see all reports (no CSV inflation)
            $totalReports = DB::table('reports')->count();
            $pendingReports = DB::table('reports')
                ->where('status', 'pending')
                ->count();
            $investigatingReports = DB::table('reports')
                ->where('status', 'investigating')
                ->count();
            $resolvedReports = DB::table('reports')
                ->where('status', 'resolved')
                ->count();
        }
        
        $totalUsers = DB::table('users')->where('role', 'user')->count();
        $totalPoliceOfficers = DB::table('users')->where('role', 'police')->count();
        
        return view('welcome', compact(
            'userRole',
            'totalReports',
            'pendingReports',
            'investigatingReports',
            'resolvedReports',
            'totalUsers',
            'totalPoliceOfficers',
            'csvReports'
        ));
    }
}
