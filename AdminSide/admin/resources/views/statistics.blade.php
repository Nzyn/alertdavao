@extends('layouts.app')

@section('title', 'Statistics & Crime Forecast')

@section('styles')
<style>
    .statistics-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .stats-header {
        margin-bottom: 2rem;
    }

    .stats-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1D3557;
        margin-bottom: 0.5rem;
    }

    .stats-subtitle {
        color: #6b7280;
        font-size: 1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-icon.blue { background: #dbeafe; color: #1e40af; }
    .stat-icon.green { background: #d1fae5; color: #065f46; }
    .stat-icon.orange { background: #fed7aa; color: #c2410c; }
    .stat-icon.purple { background: #e9d5ff; color: #6b21a8; }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .stat-change {
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-change.positive { color: #059669; }
    .stat-change.negative { color: #dc2626; }

    .chart-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
    }

    .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .chart-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .chart-controls {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .chart-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chart-btn:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .chart-btn.active {
        background: #1D3557;
        color: white;
        border-color: #1D3557;
    }

    .chart-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .forecast-select {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .chart-canvas {
        width: 100%;
        height: 400px;
        position: relative;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        z-index: 10;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e5e7eb;
        border-top-color: #1D3557;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .error-message {
        background: #fef2f2;
        color: #991b1b;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #fecaca;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-message {
        background: #eff6ff;
        color: #1e40af;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #bfdbfe;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .forecast-legend {
        display: flex;
        gap: 1.5rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 3px;
    }

    .export-section {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .statistics-container {
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endsection

@section('content')
<div class="statistics-container">
    <div class="stats-header">
        <h1 class="stats-title">Crime Statistics & Forecast</h1>
        <p class="stats-subtitle">Advanced analytics and predictive insights using SARIMA modeling</p>
    </div>

    <!-- SARIMA API Status (removed) -->

    <!-- Overview Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Total Reports</span>
                <div class="stat-icon blue">📊</div>
            </div>
            <div class="stat-value" id="totalReports">-</div>
            <div class="stat-change">All time</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">This Month</span>
                <div class="stat-icon green">📈</div>
            </div>
            <div class="stat-value" id="thisMonthReports">-</div>
            <div class="stat-change" id="monthChange">-</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Last Month</span>
                <div class="stat-icon orange">📉</div>
            </div>
            <div class="stat-value" id="lastMonthReports">-</div>
            <div class="stat-change">Previous period</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-label">Forecast Status</span>
                <div class="stat-icon purple">🔮</div>
            </div>
            <div class="stat-value" style="font-size: 1.25rem;" id="forecastStatus">Loading...</div>
            <div class="stat-change">SARIMA Model</div>
        </div>
    </div>

    <!-- Crime Trend Chart -->
    <div class="chart-section">
        <div class="chart-header">
            <h2 class="chart-title">Crime Trends & Forecast</h2>
            <div class="chart-controls">
                <select class="forecast-select" id="forecastHorizon">
                    <option value="6">6 Months Forecast</option>
                    <option value="12" selected>12 Months Forecast</option>
                    <option value="18">18 Months Forecast</option>
                    <option value="24">24 Months Forecast</option>
                </select>
                <button class="chart-btn" id="refreshForecast">🔄 Refresh</button>
            </div>
        </div>
        <div class="chart-canvas" id="trendChartContainer">
            <canvas id="trendChart"></canvas>
        </div>
        <div class="forecast-legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #1D3557;"></div>
                <span>Historical Data</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #e63946;"></div>
                <span>Forecast</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: rgba(230, 57, 70, 0.2);"></div>
                <span>Confidence Interval (95%)</span>
            </div>
        </div>
    </div>

    <!-- Crime by Type & Location -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        <div class="chart-section">
            <div class="chart-header">
                <h2 class="chart-title">Crime by Type</h2>
            </div>
            <div class="chart-canvas" style="height: 400px;">
                <canvas id="typeChart"></canvas>
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-header">
                <h2 class="chart-title">Top Locations</h2>
            </div>
            <div class="chart-canvas" style="height: 400px;">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Export Section -->
    <div class="chart-section">
        <div class="chart-header">
            <h2 class="chart-title">Data Export</h2>
        </div>
        <p style="color: #6b7280; margin-bottom: 1rem;">Export crime data for external analysis or model training</p>
        <div class="export-section">
            <button class="chart-btn" onclick="exportCrimeData()">📥 Export Crime Data (CSV)</button>
            <button class="chart-btn" onclick="exportForecastData()">📥 Download Forecast Data (JSON)</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let trendChart, typeChart, locationChart;
let crimeStats = null;
let forecastData = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCrimeStats();
    loadForecast();
    
    document.getElementById('forecastHorizon').addEventListener('change', loadForecast);
    document.getElementById('refreshForecast').addEventListener('click', loadForecast);
});

// Load crime statistics
async function loadCrimeStats() {
    console.log('Loading crime statistics...');
    try {
        const response = await fetch('/api/statistics/crime-stats');
        console.log('Crime stats response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Crime stats data:', data);
        
        if (data.status === 'success') {
            crimeStats = data.data;
            updateOverviewCards(data.data.overview);
            renderTypeChart(data.data.byType);
            renderLocationChart(data.data.byLocation);
            console.log('Crime statistics loaded successfully');
        } else {
            console.error('Crime stats API returned error:', data.message);
        }
    } catch (error) {
        console.error('Error loading crime stats:', error);
        alert('Failed to load crime statistics. Please ensure you are logged in and try again.');
    }
}

// Load forecast data
async function loadForecast() {
    const horizon = document.getElementById('forecastHorizon').value;
    const forecastStatusEl = document.getElementById('forecastStatus');
    
    console.log(`Loading forecast with horizon: ${horizon} months...`);
    
    // Show loading
    if (trendChart) {
        trendChart.destroy();
        trendChart = null;
    }
    
    const container = document.getElementById('trendChartContainer');
    container.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div><canvas id="trendChart"></canvas>';
    
    try {
        const response = await fetch(`/api/statistics/forecast?horizon=${horizon}`);
        console.log('Forecast response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Forecast data:', data);
        
        if (data.status === 'success' && data.data && Array.isArray(data.data)) {
            forecastData = data.data;
            console.log(`Rendering chart with ${crimeStats?.monthly?.length || 0} historical points and ${forecastData.length} forecast points`);
            
            // Remove loading overlay before rendering chart
            container.innerHTML = '<canvas id="trendChart"></canvas>';
            
            // Wait for DOM to update
            await new Promise(resolve => setTimeout(resolve, 10));
            
            renderTrendChart(crimeStats?.monthly || [], data.data);
            
            forecastStatusEl.textContent = 'Active';
            forecastStatusEl.style.color = '#059669';
        } else {
            throw new Error(data.message || 'Invalid forecast data');
        }
    } catch (error) {
        console.error('Error loading forecast:', error);
        forecastStatusEl.textContent = 'Offline';
        forecastStatusEl.style.color = '#dc2626';
        
        // Remove loading overlay
        container.innerHTML = '<canvas id="trendChart"></canvas>';
        
        // Wait for DOM to update
        await new Promise(resolve => setTimeout(resolve, 10));
        
        // Render historical data only
        if (crimeStats && crimeStats.monthly && crimeStats.monthly.length > 0) {
            console.log('Rendering historical data only (forecast failed)');
            renderTrendChart(crimeStats.monthly, []);
        } else {
            // Show error message in chart area
            console.warn('No historical data available to display');
            container.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 400px; color: #9ca3af;"><p>No data available to display. Please ensure reports exist in the database.</p></div>';
        }
    }
}

// Update overview cards
function updateOverviewCards(overview) {
    document.getElementById('totalReports').textContent = overview.total.toLocaleString();
    document.getElementById('thisMonthReports').textContent = overview.thisMonth.toLocaleString();
    document.getElementById('lastMonthReports').textContent = overview.lastMonth.toLocaleString();
    
    const changeEl = document.getElementById('monthChange');
    const change = overview.percentChange;
    const arrow = change >= 0 ? '↑' : '↓';
    changeEl.textContent = `${arrow} ${Math.abs(change)}% from last month`;
    changeEl.className = `stat-change ${change >= 0 ? 'positive' : 'negative'}`;
}

// Render trend chart with forecast
// Render trend chart with forecast
function renderTrendChart(historical, forecast) {
    console.log(`renderTrendChart called with ${historical.length} historical points and ${forecast.length} forecast points`);
    
    const ctx = document.getElementById('trendChart');
    
    if (!ctx) {
        console.error('Chart canvas not found');
        return;
    }
    
    // Prepare historical data
    const historicalLabels = historical.map(d => `${d.year}-${String(d.month).padStart(2, '0')}`);
    const historicalData = historical.map(d => parseInt(d.count) || 0);
    
    // Prepare forecast data
    const forecastLabels = forecast.map(d => d.date.substring(0, 7));
    const forecastValues = forecast.map(d => parseFloat(d.forecast) || 0);
    const lowerCI = forecast.map(d => parseFloat(d.lower_ci) || 0);
    const upperCI = forecast.map(d => parseFloat(d.upper_ci) || 0);
    
    console.log('Historical labels:', historicalLabels);
    console.log('Historical data:', historicalData);
    console.log('Forecast labels:', forecastLabels);
    console.log('Forecast values:', forecastValues);
    
    // Combine labels
    const allLabels = [...historicalLabels, ...forecastLabels];
    
    // Historical dataset (ends where forecast begins)
    const historicalDataset = [...historicalData, ...Array(forecastLabels.length).fill(null)];
    
    // Forecast dataset (starts where historical ends)
    const forecastDataset = [...Array(historicalLabels.length).fill(null), ...forecastValues];
    
    // Connect the gap
    if (historicalData.length > 0 && forecastValues.length > 0) {
        forecastDataset[historicalLabels.length] = historicalData[historicalData.length - 1];
    }
    
    // Destroy existing chart
    if (trendChart) {
        trendChart.destroy();
    }
    
    console.log('Creating Chart.js instance...');
    
    trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Historical',
                    data: historicalDataset,
                    borderColor: '#1D3557',
                    backgroundColor: 'rgba(29, 53, 87, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y'
                },
                {
                    label: 'Forecast',
                    data: forecastDataset,
                    borderColor: '#e63946',
                    backgroundColor: 'rgba(230, 57, 70, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
                },
                {
                    label: 'Upper CI',
                    data: [...Array(historicalLabels.length).fill(null), ...upperCI],
                    borderColor: 'rgba(230, 57, 70, 0.3)',
                    backgroundColor: 'rgba(230, 57, 70, 0.1)',
                    borderWidth: 1,
                    fill: '+1',
                    pointRadius: 0,
                    yAxisID: 'y1'
                },
                {
                    label: 'Lower CI',
                    data: [...Array(historicalLabels.length).fill(null), ...lowerCI],
                    borderColor: 'rgba(230, 57, 70, 0.3)',
                    backgroundColor: 'rgba(230, 57, 70, 0.1)',
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                // Primary axis for historical data
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Reports (Historical)'
                    }
                },
                // Secondary axis for forecast (separate scale so large forecasts don't squash history)
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Forecast (Model)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
    
    console.log('Trend chart created successfully!');
}

// Render crime by type chart
function renderTypeChart(data) {
    const ctx = document.getElementById('typeChart');
    
    if (!ctx) {
        console.error('Type chart canvas not found');
        return;
    }
    
    if (!data || !Array.isArray(data) || data.length === 0) {
        console.warn('No crime type data to display');
        return;
    }
    
    if (typeChart) typeChart.destroy();
    
    typeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.type || 'Unknown'),
            datasets: [{
                data: data.map(d => parseInt(d.count) || 0),
                backgroundColor: [
                    '#1D3557',
                    '#457B9D',
                    '#A8DADC',
                    '#F77F00',
                    '#E63946',
                    '#06D6A0',
                    '#8338EC'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Render top locations chart
function renderLocationChart(data) {
    const ctx = document.getElementById('locationChart');
    
    if (!ctx) {
        console.error('Location chart canvas not found');
        return;
    }
    
    if (!data || !Array.isArray(data) || data.length === 0) {
        console.warn('No location data to display');
        return;
    }
    
    if (locationChart) locationChart.destroy();
    
    locationChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.location || 'Unknown'),
            datasets: [{
                label: 'Reports',
                data: data.map(d => parseInt(d.count) || 0),
                backgroundColor: '#1D3557'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Export crime data
function exportCrimeData() {
    window.location.href = '/api/statistics/export';
}

// Export forecast data
function exportForecastData() {
    const horizon = document.getElementById('forecastHorizon').value;
    window.location.href = `/api/statistics/forecast?horizon=${horizon}`;
}
</script>
@endsection
