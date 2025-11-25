<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoliceOfficer;
use App\Models\PoliceStation;

class PersonnelController extends Controller
{
    public function index()
    {
        $officers = PoliceOfficer::with(['user', 'policeStation'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('personnel', compact('officers'));
    }
    
    /**
     * Get all police stations for assignment
     */
    public function getPoliceStations()
    {
        try {
            $stations = PoliceStation::select('station_id', 'station_name', 'address', 'latitude', 'longitude', 'contact_number')
                ->orderBy('station_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $stations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching police stations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
