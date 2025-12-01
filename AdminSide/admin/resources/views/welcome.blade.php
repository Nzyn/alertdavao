@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8fafc;
                color: #1f2937;
                line-height: 1.6;
            }
            
            .dashboard {
                display: flex;
                min-height: 100vh;
            }
            
            /* Sidebar Styles */
            .sidebar {
                width: 250px;
                background: white;
                padding: 2rem 0;
                position: fixed;
                height: 100vh;
                left: 0;
                top: 0;
                z-index: 1000;
                border-right: 1px solid #e5e7eb;
                box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar-header {
                padding: 0 1.5rem;
                margin-bottom: 2rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .sidebar-title {
                color: #1D3557;
                font-size: 1.25rem;
                font-weight: 700;
                margin: 0;
            }
            
            .sidebar-close {
                background: none;
                border: none;
                color: #6b7280;
                font-size: 1.25rem;
                margin-left: auto;
                cursor: pointer;
            }
            
            .nav-menu {
                list-style: none;
                padding: 0;
            }
            
            .nav-item {
                margin: 0.25rem 0;
            }
            
            .nav-link {
                display: flex;
                align-items: center;
                padding: 0.875rem 1.5rem;
                color: #6b7280;
                text-decoration: none;
                transition: all 0.3s ease;
                gap: 0.75rem;
                border-radius: 0.375rem;
                margin: 0.125rem 0.75rem;
            }
            
            .nav-link:hover,
            .nav-link.active {
                background: #f3f4f6;
                color: #1D3557;
                border-left: 3px solid #3b82f6;
                font-weight: 500;
            }
            
            .nav-icon {
                width: 20px;
                height: 20px;
                fill: currentColor;
            }
            
            /* Main Content */
            .main-content {
                margin-left: 250px;
                padding: 2rem;
                width: calc(100% - 250px);
            }
            
            .content-header {
                margin-bottom: 2rem;
            }
            
            .content-title {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }
            
            /* Stats Cards */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            
            .stat-card {
                background: white;
                padding: 1.5rem;
                border-radius: 12px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                border-left: 4px solid #e5e7eb;
                transition: all 0.3s ease;
                text-decoration: none;
                display: block;
                color: inherit;
                cursor: pointer;
            }
            
            .stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            }
            
            .stat-card.total {
                border-left-color: #3b82f6;
            }
            
            .stat-card.verified {
                border-left-color: #10b981;
            }
            
            .stat-card.pending {
                border-left-color: #f59e0b;
            }
            
            .stat-title {
                font-size: 0.875rem;
                color: #6b7280;
                margin-bottom: 0.5rem;
                text-transform: uppercase;
                font-weight: 500;
            }
            
            .stat-value {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 0.25rem;
            }
            
            /* Dashboard Grid */
            .dashboard-grid {
                display: block;
                margin-bottom: 2rem;
            }
            
            .priority-section {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                border: 3px solid #3b82f6;
                width: 100%;
            }
            
            .section-title {
                font-size: 1.125rem;
                font-weight: 600;
                margin-bottom: 1rem;
                color: #1f2937;
            }
            
            .priority-content {
                display: grid;
                grid-template-columns: 350px 1fr;
                gap: 2rem;
                align-items: start;
            }
            
            .priority-cases {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .priority-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
            }
            
            .priority-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                flex-shrink: 0;
            }
            
            .priority-dot.high {
                background-color: #dc2626;
            }
            
            .priority-dot.medium {
                background-color: #f59e0b;
            }
            
            .priority-dot.low {
                background-color: #10b981;
            }
            
            .priority-text {
                font-weight: 500;
            }
            
            .priority-count {
                font-weight: 700;
                margin-left: auto;
            }
            
            .map-placeholder {
                background: #f3f4f6;
                border-radius: 8px;
                height: 500px;
                min-height: 500px;
                width: 100%;
                position: relative;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            #dashboard-mini-map {
                height: 100%;
                width: 100%;
                border-radius: 8px;
                z-index: 1;
            }
            
            .crime-icon-with-count {
                position: relative;
            }
            
            .crime-icon-with-count::after {
                content: attr(data-count);
                position: absolute;
                top: -8px;
                right: -8px;
                background: #ef4444;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 11px;
                font-weight: bold;
                border: 2px solid white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            
            .mini-map-controls {
                position: absolute;
                top: 10px;
                right: 10px;
                z-index: 1000;
                display: flex;
                gap: 5px;
            }
            
            .mini-map-btn {
                background: white;
                border: none;
                padding: 6px 10px;
                border-radius: 4px;
                font-size: 0.75rem;
                cursor: pointer;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                font-weight: 500;
                color: #374151;
                transition: all 0.2s;
            }
            
            .mini-map-btn:hover {
                background: #f3f4f6;
                color: #3b82f6;
            }
            
            .mini-map-btn.active {
                background: #3b82f6;
                color: white;
            }
            
            .gender-chart {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .pie-chart {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                margin: 1rem auto;
                background: conic-gradient(
                    #3b82f6 0deg 180deg,
                    #10b981 180deg 300deg,
                    #6b7280 300deg 360deg
                );
                position: relative;
            }
            
            .pie-chart::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 60px;
                height: 60px;
                background: white;
                border-radius: 50%;
            }
            
            .chart-legend {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }
            
            .legend-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
            }
            
            .legend-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
            }
            
            .legend-dot.male {
                background-color: #3b82f6;
            }
            
            .legend-dot.female {
                background-color: #10b981;
            }
            
            .legend-dot.others {
                background-color: #6b7280;
            }
            
            /* Bottom Chart */
            .bottom-chart {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .line-chart {
                height: 200px;
                background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200" fill="none"><rect width="400" height="200" fill="white"/><polyline points="50,150 100,130 150,120 200,110 250,100 350,80" stroke="%233b82f6" stroke-width="3" fill="none"/><circle cx="50" cy="150" r="4" fill="%233b82f6"/><circle cx="100" cy="130" r="4" fill="%233b82f6"/><circle cx="150" cy="120" r="4" fill="%233b82f6"/><circle cx="200" cy="110" r="4" fill="%233b82f6"/><circle cx="250" cy="100" r="4" fill="%233b82f6"/><circle cx="350" cy="80" r="4" fill="%233b82f6"/></svg>');
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #6b7280;
            }
            
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                    transition: transform 0.3s ease;
                }
                
                .main-content {
                    margin-left: 0;
                    width: 100%;
                }
                
                .dashboard-grid {
                    grid-template-columns: 1fr;
                }
                
                .priority-content {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }
            }
        </style>
@endsection

@section('content')
    <div class="content-header">
        @if($userRole === 'police')
            <h1 class="content-title">Police Station Dashboard</h1>
            <p class="content-subtitle">Manage reports for your assigned station</p>
        @else
            <h1 class="content-title">Dashboard Overview</h1>
            <p class="content-subtitle">System-wide statistics and overview</p>
        @endif
    </div>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    @if($userRole === 'police')
                        <!-- Police Officer Stats -->
                        <a href="{{ route('reports') }}" class="stat-card total">
                            <div class="stat-title">Total Reports</div>
                            <div class="stat-value">{{ $totalReports }}</div>
                        </a>
                        <a href="{{ route('reports', ['status' => 'investigating']) }}" class="stat-card verified">
                            <div class="stat-title">Investigating</div>
                            <div class="stat-value">{{ $investigatingReports }}</div>
                        </a>
                        <a href="{{ route('reports', ['status' => 'pending']) }}" class="stat-card pending">
                            <div class="stat-title">Pending</div>
                            <div class="stat-value">{{ $pendingReports }}</div>
                        </a>
                        <a href="{{ route('reports', ['status' => 'resolved']) }}" class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <div class="stat-title">Resolved</div>
                            <div class="stat-value">{{ $resolvedReports }}</div>
                        </a>
                    @else
                        <!-- Admin Stats -->
                        <a href="{{ route('reports') }}" class="stat-card total">
                            <div class="stat-title">Total Reports</div>
                            <div class="stat-value">{{ $totalReports }}</div>
                        </a>
                        <a href="{{ route('users') }}" class="stat-card verified">
                            <div class="stat-title">Total Users</div>
                            <div class="stat-value">{{ $totalUsers }}</div>
                        </a>
                        <a href="{{ route('users') }}" class="stat-card pending">
                            <div class="stat-title">Police Officers</div>
                            <div class="stat-value">{{ $totalPoliceOfficers }}</div>
                        </a>
                    @endif
                </div>
                
                <!-- Priority Cases and Map -->
                <div class="dashboard-grid">
                    <div class="priority-section">
                        <h2 class="section-title">Crime Map by Barangay</h2>
                        <div class="priority-content">
                            <div class="priority-cases">
                                <div style="margin-bottom: 1rem;">
                                    <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 600; margin-bottom: 0.5rem;">FILTER BY BARANGAY</label>
                                    <select id="barangay-filter" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;">
                                        <option value="">All Barangays</option>
                                    </select>
                                </div>
                                <div style="margin-bottom: 1rem;">
                                    <label style="display: block; font-size: 0.75rem; color: #64748b; font-weight: 600; margin-bottom: 0.5rem;">FILTER BY CRIME TYPE</label>
                                    <select id="crime-type-filter" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;">
                                        <option value="">All Crime Types</option>
                                    </select>
                                </div>
                                <button onclick="applyDashboardFilters()" style="width: 100%; padding: 0.5rem; background: #3b82f6; color: white; border: none; border-radius: 6px; font-size: 0.875rem; cursor: pointer; margin-bottom: 0.5rem;">Apply Filters</button>
                                <button onclick="resetDashboardFilters()" style="width: 100%; padding: 0.5rem; background: #6b7280; color: white; border: none; border-radius: 6px; font-size: 0.875rem; cursor: pointer;">Reset</button>
                            </div>
                            
                            <div class="map-placeholder">
                                <div id="dashboard-mini-map"></div>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@section('scripts')
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    // Global variables for mini map
    let miniMap;
    let miniMarkers = [];
    let miniReports = [];
    let miniBarangays = [];
    let miniCrimeTypes = [];
    
    // Crime type to icon mapping
    const crimeIcons = {
        'carnapping': '/legends/001-close.png',
        'physical injury': '/legends/001-pointed-star.png',
        'assault': '/legends/001-pointed-star.png', // Using physical injury icon
        'motornapping': '/legends/002-plus.png',
        'robbery': '/legends/002-rectangle.png',
        'burglary': '/legends/002-rectangle.png', // Using robbery icon
        'theft': '/legends/003-ellipse.png',
        'homicide': '/legends/diamondHOMICIDE.png',
        'rape': '/legends/moonRAPE.png',
        'murder': '/legends/squareMURDER.png'
    };
    
    // Davao City bounds (approximate)
    const davaoCityBounds = [
        [6.9, 125.2],  // Southwest corner
        [7.5, 125.7]   // Northeast corner
    ];
    
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            // Initialize the mini map centered on Davao City with bounds restriction
            miniMap = L.map('dashboard-mini-map', {
                zoomControl: false,
                attributionControl: false,
                maxBounds: davaoCityBounds,
                maxBoundsViscosity: 1.0,
                minZoom: 11,
                maxZoom: 18
            }).setView([7.1907, 125.4553], 12);
    
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '',
                maxZoom: 18,
            }).addTo(miniMap);
            
            // Load initial data
            loadMiniMapReports();
        }, 100);
    });
    
    // Function to load reports from API
    function loadMiniMapReports(filters = {}) {
        const params = new URLSearchParams(filters).toString();
        const url = '{{ route("api.reports") }}' + (params ? '?' + params : '');
        
        console.log('Loading mini map reports from:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                console.log('Reports count:', data.reports ? data.reports.length : 0);
                console.log('Barangays:', data.barangays);
                console.log('Crime types:', data.crime_types);
                
                miniReports = data.reports;
                miniBarangays = data.barangays || [];
                miniCrimeTypes = data.crime_types || [];
                
                // Populate filter dropdowns
                populateBarangayFilter();
                populateCrimeTypeFilter();
                
                updateMiniMapMarkers(data.reports);
            })
            .catch(error => {
                console.error('Error loading mini map reports:', error);
                alert('Error loading map data. Check console for details.');
            });
    }
    
    function cleanText(str) {
        if (!str) return '';
        
        // Decode HTML entities
        const textarea = document.createElement('textarea');
        textarea.innerHTML = str;
        let cleaned = textarea.value;
        
        // Remove brackets and quotes that might be stored as-is
        cleaned = cleaned.replace(/[\[\]"']/g, '');
        
        // Remove extra whitespace
        cleaned = cleaned.trim().replace(/\s+/g, ' ');
        
        return cleaned;
    }
    
    function populateBarangayFilter() {
         const select = document.getElementById('barangay-filter');
         const currentValue = select.value;
         
         // Keep "All Barangays" option
         select.innerHTML = '<option value="">All Barangays</option>';
         
         miniBarangays.forEach(barangay => {
             const option = document.createElement('option');
             // Clean barangay name to remove HTML entities, brackets, quotes
             const cleanedBarangay = cleanText(barangay);
             option.value = cleanedBarangay;
             option.textContent = cleanedBarangay;
             select.appendChild(option);
         });
         
         select.value = currentValue;
     }
     
     function populateCrimeTypeFilter() {
         const select = document.getElementById('crime-type-filter');
         const currentValue = select.value;
         
         // Keep "All Crime Types" option
         select.innerHTML = '<option value="">All Crime Types</option>';
         
         miniCrimeTypes.forEach(crimeType => {
             const option = document.createElement('option');
             // Clean crime type name to remove HTML entities, brackets, quotes
             const cleanedCrimeType = cleanText(crimeType);
             option.value = cleanedCrimeType;
             option.textContent = cleanedCrimeType.charAt(0).toUpperCase() + cleanedCrimeType.slice(1);
             select.appendChild(option);
         });
         
         select.value = currentValue;
     }
    
    // Function to get icon for crime type
    function getCrimeIcon(crimeType) {
        if (!crimeType) return null;
        
        // Clean the crime type first
        const cleanedType = cleanText(crimeType);
        const normalizedType = cleanedType.toLowerCase().trim();
        return crimeIcons[normalizedType] || null;
    }
    
    // Function to create cluster icon with legend icons
    function createMiniClusterIcon(uniqueCrimeTypes) {
        // Get up to 3 crime type icons to display
        const iconUrls = uniqueCrimeTypes.slice(0, 3).map(crimeType => getCrimeIcon(crimeType)).filter(url => url);
        
        if (iconUrls.length === 0) {
            // No icons found, show question mark
            return L.divIcon({
                className: 'custom-cluster-marker',
                html: `<div style="background-color: #3b82f6; color: white; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">?</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -16]
            });
        } else if (iconUrls.length === 1) {
            // Single crime type, show its icon
            return L.icon({
                iconUrl: iconUrls[0],
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -14]
            });
        } else {
            // Multiple crime types, show icons in a grid
            const iconHtmlItems = iconUrls.map(url => 
                `<img src="${url}" style="width: 12px; height: 12px; object-fit: contain;">`
            ).join('');
            
            return L.divIcon({
                className: 'custom-cluster-marker',
                html: `<div style="background-color: white; width: 34px; height: 34px; border-radius: 4px; border: 2px solid #3b82f6; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 1px; padding: 2px;">${iconHtmlItems}</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -17]
            });
        }
    }
    
    // Function to create custom marker icon
    function createCrimeMarker(crimeType, count = 1) {
        const iconUrl = getCrimeIcon(crimeType);
        
        if (iconUrl && count === 1) {
            // Single crime with custom icon
            return L.icon({
                iconUrl: iconUrl,
                iconSize: [24, 24],
                iconAnchor: [12, 12],
                popupAnchor: [0, -12]
            });
        } else if (count > 1) {
            // Multiple crimes - show count badge
            return L.divIcon({
                className: 'custom-cluster-marker',
                html: `<div style="background-color: #3b82f6; color: white; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">${count}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -16]
            });
        } else {
            // Default marker for unknown crime type
            return L.divIcon({
                className: 'custom-marker',
                html: '<div style="background-color: #6b7280; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
        }
    }
    
    // Function to update map markers
    function updateMiniMapMarkers(reports) {
        console.log('Updating mini map markers with', reports.length, 'reports');
        
        // Clear existing markers
        miniMarkers.forEach(marker => {
            miniMap.removeLayer(marker);
        });
        miniMarkers = [];
        
        // Add new markers
        reports.forEach((report, index) => {
            console.log('Processing report', index, ':', report);
            
            if (report.is_cluster) {
                // Check if all crimes in cluster are the same type
                const crimeTypes = report.crimes.map(c => c.crime_type);
                const uniqueTypes = [...new Set(crimeTypes)];
                
                // Create cluster icon showing legend icons instead of count
                const icon = createMiniClusterIcon(uniqueTypes);
                
                // Build popup content showing all crimes
                let popupContent = `<div style="max-height: 200px; overflow-y: auto;">
                    <strong style="font-size: 0.875rem;">${report.count} Crimes at this location</strong><br>
                    <strong>Location:</strong> ${report.location_name}<br><hr style="margin: 0.5rem 0;">`;
                
                report.crimes.forEach((crime, index) => {
                    const cleanedCrimeType = cleanText(crime.crime_type || crime.title);
                    popupContent += `
                        <div style="margin-bottom: 0.5rem; padding-bottom: 0.5rem; ${index < report.crimes.length - 1 ? 'border-bottom: 1px solid #e5e7eb;' : ''}">
                            <strong>${cleanedCrimeType}</strong><br>
                            <span style="font-size: 0.75rem; color: #6b7280;">Status: ${crime.status}</span>
                        </div>`;
                });
                
                popupContent += '</div>';
                
                const marker = L.marker([report.latitude, report.longitude], { icon: icon })
                    .addTo(miniMap)
                    .bindPopup(popupContent);
                
                // Add tooltip on hover
                if (uniqueTypes.length === 1) {
                    const cleanedType = cleanText(uniqueTypes[0]);
                    marker.bindTooltip(`${report.count}x ${cleanedType}`, {
                        permanent: false,
                        direction: 'top',
                        offset: [0, -14]
                    });
                } else {
                    marker.bindTooltip(`${report.count} different crimes here`, {
                        permanent: false,
                        direction: 'top',
                        offset: [0, -16]
                    });
                }
                
                miniMarkers.push(marker);
            } else {
                // Single crime
                const cleanedCrimeType = cleanText(report.crime_type || report.title);
                const icon = createCrimeMarker(report.crime_type, 1);
                
                const marker = L.marker([report.latitude, report.longitude], { icon: icon })
                    .addTo(miniMap)
                    .bindPopup(`
                        <div style="font-size: 0.75rem;">
                            <strong style="font-size: 0.875rem;">${cleanedCrimeType}</strong><br>
                            <strong>Location:</strong> ${report.location_name}<br>
                            <strong>Status:</strong> ${report.status}<br>
                            <strong>Date:</strong> ${new Date(report.date_reported).toLocaleDateString()}
                        </div>
                    `);
                
                miniMarkers.push(marker);
            }
        });
        
        // Fit bounds if there are markers
        if (miniMarkers.length > 0) {
            const group = L.featureGroup(miniMarkers);
            miniMap.fitBounds(group.getBounds().pad(0.1), {
                maxZoom: 14
            });
        }
    }
    
    function applyDashboardFilters() {
        const filters = {
            barangay: document.getElementById('barangay-filter').value,
            crime_type: document.getElementById('crime-type-filter').value
        };
        
        // Remove empty filters
        Object.keys(filters).forEach(key => {
            if (filters[key] === '') {
                delete filters[key];
            }
        });
        
        loadMiniMapReports(filters);
    }
    
    function resetDashboardFilters() {
        document.getElementById('barangay-filter').value = '';
        document.getElementById('crime-type-filter').value = '';
        loadMiniMapReports();
    }

    // Auto-refresh dashboard stats every 3 seconds
    function checkForNewStats() {
        fetch('{{ route("dashboard") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update stat values
            const statCards = document.querySelectorAll('.stat-value');
            const newStatCards = doc.querySelectorAll('.stat-value');
            let hasUpdates = false;
            
            statCards.forEach((card, index) => {
                if (newStatCards[index] && card.textContent !== newStatCards[index].textContent) {
                    console.log('📊 Dashboard stat updated:', card.parentElement.querySelector('.stat-title').textContent, card.textContent, '→', newStatCards[index].textContent);
                    card.textContent = newStatCards[index].textContent;
                    hasUpdates = true;
                    // Add flash animation
                    card.style.animation = 'flash 0.5s';
                    setTimeout(() => {
                        card.style.animation = '';
                    }, 500);
                }
            });
            
            if (hasUpdates) {
                console.log('✅ Dashboard statistics updated successfully');
            }
        })
        .catch(error => {
            console.error('❌ Error checking for new stats:', error);
        });
    }
    
    // Start auto-refresh when page loads
    console.log('🔄 Dashboard auto-refresh enabled - Checking every 3 seconds for all users');
    setInterval(checkForNewStats, 3000);
</script>

<style>
@keyframes flash {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; background-color: #fef3c7; }
}
</style>
@endsection
