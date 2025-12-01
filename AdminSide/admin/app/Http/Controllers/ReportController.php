<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportMedia;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Assign a report to the correct police station based on its location
     */
    private function assignReportToStation(Report $report)
    {
        try {
            // Robustly handle report_type as array, JSON string, or comma-separated string
            $types = [];
            $rawType = $report->report_type;
            if (is_array($rawType)) {
                $types = $rawType;
            } elseif (is_string($rawType)) {
                $decoded = json_decode($rawType, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $types = $decoded;
                } else if (!empty($rawType)) {
                    // Fallback: treat as comma-separated string
                    $types = array_map('trim', explode(',', $rawType));
                }
            }

            if (empty($types)) {
                \Log::warning('Report assignment failed: report_type is empty or invalid', [
                    'report_id' => $report->report_id,
                    'rawType' => $rawType
                ]);
            }

            // Normalize to lowercase for comparison
            $types = array_map('strtolower', $types);
            $types = array_map('trim', $types);
            
            // Check if 'cybercrime' or 'cyber crime' is explicitly selected
            $isCybercrime = in_array('cybercrime', $types) || in_array('cyber crime', $types);
            
            // Also check for variations like "cybercrime - fraud", "cyber crime - hacking"
            foreach ($types as $type) {
                if (str_starts_with($type, 'cybercrime -') || str_starts_with($type, 'cyber crime -')) {
                    $isCybercrime = true;
                    break;
                }
            }
            
            if ($isCybercrime) {
                $cybercrimeStation = \App\Models\PoliceStation::where('station_name', 'Cybercrime Division')->first();
                if ($cybercrimeStation) {
                    $report->assigned_station_id = $cybercrimeStation->station_id;
                    $report->save();
                    \Log::info('Report assigned to Cybercrime Division', [
                        'report_id' => $report->report_id,
                        'station_id' => $cybercrimeStation->station_id,
                        'types' => $types,
                        'rawType' => $rawType
                    ]);
                    // Do NOT overwrite cybercrime assignment with barangay assignment
                    return;
                } else {
                    \Log::error('Cybercrime Division station not found for assignment', [
                        'report_id' => $report->report_id
                    ]);
                }
            } else {
                if (!$report->location_id || !$report->location) {
                    \Log::warning('Cannot assign report: no location', ['report_id' => $report->report_id]);
                    return;
                }

                $location = $report->location;
                // Validate location has coordinates
                if (!$location->latitude || !$location->longitude) {
                    \Log::warning('Cannot assign report: no coordinates', ['location_id' => $location->location_id]);
                    return;
                }

                // Find barangay by coordinates (using proximity search)
                $barangay = Barangay::whereBetween('latitude', [$location->latitude - 0.01, $location->latitude + 0.01])
                    ->whereBetween('longitude', [$location->longitude - 0.01, $location->longitude + 0.01])
                    ->first();

                if ($barangay && $barangay->station_id) {
                    $report->assigned_station_id = $barangay->station_id;
                    $report->save();
                    \Log::info('Report assigned to station', [
                        'report_id' => $report->report_id,
                        'station_id' => $barangay->station_id,
                        'barangay' => $barangay->name ?? 'Unknown'
                    ]);
                } else {
                    \Log::warning('No barangay found for coordinates', [
                        'report_id' => $report->report_id,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error assigning report to station: ' . $e->getMessage(), [
                'report_id' => $report->report_id
            ]);
        }
    }

    /**
     * Display a listing of the reports
     */
    public function index(Request $request)
    {
        $query = Report::with(['user.verification', 'location', 'media', 'policeStation'])
            ->join('locations', 'reports.location_id', '=', 'locations.location_id');
        
        // Exclude reports without a location - must have valid coordinates
        $query->where('locations.latitude', '!=', 0)
              ->where('locations.longitude', '!=', 0)
              ->where('locations.latitude', '!=', null)
              ->where('locations.longitude', '!=', null);
        
        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'investigating', 'resolved'])) {
            $query->where('reports.status', $request->status);
        }

        // Filter by station if user is a police officer
        if (auth()->user() && auth()->user()->role === 'police') {
            $userStationId = auth()->user()->station_id;
            if ($userStationId) {
                // For police officers: show ONLY reports assigned to their station
                $query->where('reports.assigned_station_id', $userStationId);
                \Log::info('Police user filtering reports', [
                    'user_id' => auth()->user()->id,
                    'station_id' => $userStationId
                ]);
            } else {
                // Police user without station assignment - show no reports
                \Log::warning('Police user has no station assignment', [
                    'user_id' => auth()->user()->id
                ]);
                $query->whereRaw('1 = 0'); // Return empty result
            }
        }
        // For admin users, show ALL reports (excluding those without valid location)
        
        $reports = $query->select('reports.*')
                         ->orderBy('reports.created_at', 'desc')
                         ->paginate(10);
        return view('reports', compact('reports'));
    }

    /**
     * Update the status of a report
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,investigating,resolved'
            ]);

            $report = Report::findOrFail($id);
            $report->status = $request->status;
            $report->save();

            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the validity status of a report (VALID, INVALID, or CHECKING)
     */
    public function updateValidity(Request $request, $id)
    {
        try {
            $request->validate([
                'is_valid' => 'required|in:valid,invalid,checking_for_report_validity'
            ]);

            $report = Report::findOrFail($id);
            $report->is_valid = $request->is_valid;
            $report->save();

            return response()->json(['success' => true, 'message' => 'Report validity status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update validity status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a new report with automatic station assignment
     */
    public function store(Request $request)
    {
        try {
            // Handle both crime_types (JSON array from frontend) and location creation if needed
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string',
                'description' => 'required|string',
                'crime_types' => 'required|json',  // Accept JSON array of crime types
                'incident_date' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'reporters_address' => 'nullable|string',
                'barangay' => 'nullable|string',
                'barangay_id' => 'nullable|integer',
                'is_anonymous' => 'boolean',
                'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:25600', // 25MB
            ]);

            // Create or get the location record
            $location = \App\Models\Location::create([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->reporters_address ?? '',
                'barangay_id' => $request->barangay_id,
            ]);

            // Encrypt sensitive fields before saving
            $reportData = [
                'user_id' => $request->user_id,
                'title' => \App\Services\EncryptionService::encrypt($request->title),
                'description' => \App\Services\EncryptionService::encrypt($request->description),
                'report_type' => $request->crime_types,  // Store the JSON array directly
                'location_id' => $location->location_id,
                'is_anonymous' => $request->boolean('is_anonymous', false),
                'date_reported' => $request->incident_date,
            ];

            $report = Report::create($reportData);

            // Handle media upload if present (supports multiple files)
            if ($request->hasFile('media')) {
                $files = $request->file('media');
                // Handle both single and multiple files
                if (!is_array($files)) {
                    $files = [$files];
                }
                
                foreach ($files as $file) {
                    $path = $file->store('reports', 'public');
                    
                    // Determine media type extension from file
                    $extension = strtolower($file->getClientOriginalExtension());
                    
                    ReportMedia::create([
                        'report_id' => $report->report_id,
                        'media_url' => $path,
                        'media_type' => $extension,
                    ]);
                }
            }

            // Automatically assign to the correct police station based on location
            $this->assignReportToStation($report);

            // Aggressive failsafe: Always force assignment for cybercrime reports
            $types = [];
            $rawType = $report->report_type;
            \Log::info('Aggressive assignment check', ['report_id' => $report->report_id, 'rawType' => $rawType]);
            if (is_array($rawType)) {
                $types = $rawType;
            } elseif (is_string($rawType)) {
                $decoded = json_decode($rawType, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $types = $decoded;
                } else if (!empty($rawType)) {
                    $types = array_map('trim', explode(',', $rawType));
                }
            }
            $types = array_map('strtolower', $types);
            \Log::info('Aggressive assignment types', ['report_id' => $report->report_id, 'types' => $types]);
            if (in_array('cybercrime', $types)) {
                $cybercrimeStation = \App\Models\PoliceStation::where('station_name', 'Cybercrime Division')->first();
                if ($cybercrimeStation) {
                    $report->assigned_station_id = $cybercrimeStation->station_id;
                    $report->save();
                    \Log::info('Aggressive: Forced assignment to Cybercrime Division', [
                        'report_id' => $report->report_id,
                        'station_id' => $cybercrimeStation->station_id,
                        'types' => $types,
                        'rawType' => $rawType
                    ]);
                } else {
                    \Log::error('Aggressive: Cybercrime Division station not found', ['report_id' => $report->report_id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Report created and assigned to the appropriate police station',
                'data' => $report->load(['user', 'location']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating report: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the proper URL for a media file
     * Handles both storage disk paths and ensures proper accessibility
     */
    private function getMediaUrl($mediaUrl)
    {
        if (!$mediaUrl) {
            return null;
        }

        // If already a full URL (starts with http), use as is
        if (strpos($mediaUrl, 'http') === 0) {
            return $mediaUrl;
        }

        // Check if file exists in public storage
        if (Storage::disk('public')->exists($mediaUrl)) {
            // Use the proper public URL for storage disk
            return Storage::disk('public')->url($mediaUrl);
        }

        // Fallback: construct the URL manually if storage symlink is set up
        // This handles paths like "reports/filename.jpg"
        $url = url('/storage/' . ltrim($mediaUrl, '/'));
        return $url;
    }

    /**
     * Get report details for modal display
     * Now with properly formatted media URLs
     */
    public function getDetails($id)
    {
        try {
            $report = Report::with(['user.verification', 'location', 'media', 'policeStation'])->findOrFail($id);

            // Only police and admin can decrypt sensitive fields
            $userRole = auth()->check() ? auth()->user()->role : null;
            if (\App\Services\EncryptionService::canDecrypt($userRole)) {
                // Decrypt sensitive fields
                $fieldsToDecrypt = ['title', 'description'];
                $report = \App\Services\EncryptionService::decryptModelFields($report, $fieldsToDecrypt);
            }

            // Transform media URLs to ensure they're properly accessible
            if ($report->media && count($report->media) > 0) {
                foreach ($report->media as $media) {
                    $media->display_url = $this->getMediaUrl($media->media_url);
                    // Log media information for debugging
                    \Log::debug('Media file info', [
                        'media_id' => $media->media_id,
                        'original_url' => $media->media_url,
                        'display_url' => $media->display_url,
                        'exists' => Storage::disk('public')->exists($media->media_url)
                    ]);
                }
            }

            return response()->json(['success' => true, 'data' => $report]);
        } catch (\Exception $e) {
            \Log::error('Error loading report details', [
                'report_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to load report details: ' . $e->getMessage()], 500);
        }
    }
}
