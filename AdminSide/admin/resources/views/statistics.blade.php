@extends('layouts.app')

@section('title', 'Statistics & Crime Forecast')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        <h1 class="stats-title">📊 Crime Statistics & SARIMA Forecast</h1>
        <p class="stats-subtitle">Advanced predictive analytics using Seasonal AutoRegressive Integrated Moving Average modeling</p>
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
                <span class="stat-label">SARIMA API Status</span>
                <div class="stat-icon purple">🔮</div>
            </div>
            <div class="stat-value" style="font-size: 1.25rem;" id="forecastStatus">Checking...</div>
            <div class="stat-change">Port 8001</div>
        </div>
    </div>

    <!-- Crime Trend Chart -->
    <div class="chart-section">
        <div class="chart-header">
            <h2 class="chart-title">📊 Crime Trends & Forecast</h2>
            <div class="chart-controls">
                <select class="forecast-select" id="forecastHorizon">
                    <option value="6">6 Months Forecast</option>
                    <option value="12" selected>12 Months Forecast</option>
                    <option value="18">18 Months Forecast</option>
                    <option value="24">24 Months Forecast</option>
                </select>
                <button class="chart-btn" id="refreshForecast">🔄 Refresh Forecast</button>
            </div>
        </div>
        <div id="forecastInfoBox" style="display: none; margin-bottom: 1rem; padding: 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; color: #0c4a6e;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <span style="font-size: 1.25rem;">ℹ️</span>
                <strong>Forecast Information</strong>
            </div>
            <div id="forecastInfoText" style="font-size: 0.875rem; line-height: 1.5;"></div>
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
                <span>SARIMA Forecast</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: rgba(230, 57, 70, 0.2);"></div>
                <span>95% Confidence Interval</span>
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

    <!-- Top Barangays by Crime Count -->
    <div class="chart-section" style="margin-top: 2rem;">
        <div class="chart-header">
            <h2 class="chart-title">Top Barangays by Crime Count</h2>
            <p style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">
                Historical crime data from CrimeReports.csv
            </p>
        </div>
        <div id="topBarangays" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-top: 1rem;"></div>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let trendChart, typeChart, locationChart;
let crimeStats = null;
let forecastData = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCrimeStats();
    loadForecast();
    loadBarangayStats();
    
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
    const forecastInfoBox = document.getElementById('forecastInfoBox');
    const forecastInfoText = document.getElementById('forecastInfoText');
    
    console.log(`🔮 Loading SARIMA forecast with horizon: ${horizon} months...`);
    
    // Show loading
    if (trendChart) {
        trendChart.destroy();
        trendChart = null;
    }
    
    const container = document.getElementById('trendChartContainer');
    container.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div><canvas id="trendChart"></canvas>';
    
    try {
        console.log('📡 Fetching from SARIMA API endpoint...');
        const response = await fetch(`/api/statistics/forecast?horizon=${horizon}`);
        console.log('✅ Forecast response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📊 Forecast data received:', data);
        
        if (data.status === 'success' && data.data && Array.isArray(data.data)) {
            forecastData = data.data;
            console.log(`✅ Successfully loaded ${forecastData.length} forecast points`);
            console.log(`📈 Rendering chart with ${crimeStats?.monthly?.length || 0} historical points and ${forecastData.length} forecast points`);
            
            // Remove loading overlay before rendering chart
            container.innerHTML = '<canvas id="trendChart"></canvas>';
            
            // Wait for DOM to update
            await new Promise(resolve => setTimeout(resolve, 10));
            
            renderTrendChart(crimeStats?.monthly || [], data.data);
            
            // Update forecast status
            forecastStatusEl.textContent = '✓ Active';
            forecastStatusEl.style.color = '#059669';
            
            // Show forecast information
            forecastInfoBox.style.display = 'block';
            const firstDate = data.data[0]?.date?.substring(0, 7) || 'N/A';
            const lastDate = data.data[data.data.length - 1]?.date?.substring(0, 7) || 'N/A';
            const avgForecast = (data.data.reduce((sum, d) => sum + parseFloat(d.forecast || 0), 0) / data.data.length).toFixed(1);
            
            forecastInfoText.innerHTML = `
                <strong>Forecast Period:</strong> ${firstDate} to ${lastDate} (${horizon} months)<br>
                <strong>Average Predicted Crimes:</strong> ${avgForecast} per month<br>
                <strong>Model:</strong> SARIMA(1,1,1)(1,1,1)[12] with 95% confidence intervals<br>
                <strong>Last Updated:</strong> ${new Date().toLocaleString()}
            `;
            
            console.log('✅ SARIMA forecast loaded and displayed successfully');
        } else {
            throw new Error(data.message || 'Invalid forecast data structure');
        }
    } catch (error) {
        console.error('❌ Error loading SARIMA forecast:', error);
        forecastStatusEl.textContent = '✗ Offline';
        forecastStatusEl.style.color = '#dc2626';
        
        // Hide forecast info box
        forecastInfoBox.style.display = 'none';
        
        // Remove loading overlay
        container.innerHTML = '<canvas id="trendChart"></canvas>';
        
        // Wait for DOM to update
        await new Promise(resolve => setTimeout(resolve, 10));
        
        // Render historical data only
        if (crimeStats && crimeStats.monthly && crimeStats.monthly.length > 0) {
            console.log('⚠️ Rendering historical data only (SARIMA API unavailable)');
            renderTrendChart(crimeStats.monthly, []);
        } else {
            // Show error message in chart area
            console.warn('❌ No historical data available to display');
            container.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; color: #9ca3af; text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                    <p style="font-size: 1.125rem; font-weight: 600; color: #4b5563; margin-bottom: 0.5rem;">No Data Available</p>
                    <p style="font-size: 0.875rem;">Please ensure crime reports exist in the database and the SARIMA API is running.</p>
                    <p style="font-size: 0.75rem; margin-top: 1rem; color: #6b7280;">SARIMA API should be running on <code style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px;">localhost:8001</code></p>
                </div>
            `;
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
function renderTrendChart(historical, forecast) {
    console.log(`📊 renderTrendChart: ${historical.length} historical + ${forecast.length} forecast points`);
    
    const ctx = document.getElementById('trendChart');
    
    if (!ctx) {
        console.error('❌ Chart canvas not found');
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
    
    console.log('📅 Historical period:', historicalLabels.length > 0 ? `${historicalLabels[0]} to ${historicalLabels[historicalLabels.length - 1]}` : 'None');
    console.log('🔮 Forecast period:', forecastLabels.length > 0 ? `${forecastLabels[0]} to ${forecastLabels[forecastLabels.length - 1]}` : 'None');
    
    // Combine labels
    const allLabels = [...historicalLabels, ...forecastLabels];
    
    // Historical dataset (ends where forecast begins)
    const historicalDataset = [...historicalData, ...Array(forecastLabels.length).fill(null)];
    
    // Forecast dataset (starts where historical ends)
    const forecastDataset = [...Array(historicalLabels.length).fill(null), ...forecastValues];
    
    // Connect the gap between historical and forecast
    if (historicalData.length > 0 && forecastValues.length > 0) {
        forecastDataset[historicalLabels.length] = historicalData[historicalData.length - 1];
    }
    
    // Destroy existing chart
    if (trendChart) {
        trendChart.destroy();
    }
    
    console.log('🎨 Creating SARIMA forecast chart...');
    
    trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Historical Crime Data',
                    data: historicalDataset,
                    borderColor: '#1D3557',
                    backgroundColor: 'rgba(29, 53, 87, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y',
                    pointRadius: 3,
                    pointHoverRadius: 6
                },
                {
                    label: 'SARIMA Forecast',
                    data: forecastDataset,
                    borderColor: '#e63946',
                    backgroundColor: 'rgba(230, 57, 70, 0.1)',
                    borderWidth: 3,
                    borderDash: [8, 4],
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1',
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointStyle: 'circle'
                },
                {
                    label: '95% Upper Confidence',
                    data: [...Array(historicalLabels.length).fill(null), ...upperCI],
                    borderColor: 'rgba(230, 57, 70, 0.3)',
                    backgroundColor: 'rgba(230, 57, 70, 0.15)',
                    borderWidth: 1,
                    fill: '+1',
                    pointRadius: 0,
                    yAxisID: 'y1',
                    tension: 0.4
                },
                {
                    label: '95% Lower Confidence',
                    data: [...Array(historicalLabels.length).fill(null), ...lowerCI],
                    borderColor: 'rgba(230, 57, 70, 0.3)',
                    backgroundColor: 'rgba(230, 57, 70, 0.15)',
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0,
                    yAxisID: 'y1',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        filter: function(item) {
                            // Hide confidence interval labels from legend
                            return !item.text.includes('Confidence');
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        title: function(context) {
                            return 'Period: ' + context[0].label;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += Math.round(context.parsed.y) + ' crimes';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                // Primary axis for historical data
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '📊 Historical Crime Reports',
                        font: {
                            size: 13,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        precision: 0
                    }
                },
                // Secondary axis for forecast
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: '🔮 SARIMA Forecast',
                        font: {
                            size: 13,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: '📅 Time Period (Year-Month)',
                        font: {
                            size: 13,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
    
    console.log('✅ SARIMA forecast chart rendered successfully!');
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

// Load barangay crime statistics
async function loadBarangayStats() {
    console.log('Loading barangay statistics...');
    try {
        const response = await fetch('/api/statistics/barangay-stats');
        console.log('Barangay stats response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Barangay stats data:', result);
        
        if (result.status === 'success') {
            displayTopBarangays(result.data.slice(0, 15));
            console.log('Barangay statistics loaded successfully');
        } else {
            console.error('Barangay stats API returned error:', result.message);
        }
    } catch (error) {
        console.error('Error loading barangay statistics:', error);
    }
}

// Display top barangays in a clean, concise format
function displayTopBarangays(topBarangays) {
    const container = document.getElementById('topBarangays');
    container.innerHTML = '';
    
    topBarangays.forEach((item, index) => {
        const div = document.createElement('div');
        div.style.cssText = 'padding: 1rem; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-radius: 8px; border-left: 4px solid ' + getRankColor(index) + '; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: transform 0.2s;';
        div.onmouseenter = function() { this.style.transform = 'translateY(-2px)'; this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)'; };
        div.onmouseleave = function() { this.style.transform = 'translateY(0)'; this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)'; };
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem;">#${index + 1}</div>
                    <div style="font-weight: 600; font-size: 0.875rem; color: #1f2937; line-height: 1.2;">${item.barangay}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: ${getRankColor(index)};">${item.total_crimes}</div>
                    <div style="font-size: 0.65rem; color: #6b7280; text-transform: uppercase;">crimes</div>
                </div>
            </div>
        `;
        container.appendChild(div);
    });
}

// Get color based on rank
function getRankColor(index) {
    if (index === 0) return '#dc2626'; // Red for #1
    if (index === 1) return '#ea580c'; // Orange-red for #2
    if (index === 2) return '#f59e0b'; // Orange for #3
    if (index < 5) return '#3b82f6'; // Blue for top 5
    return '#6b7280'; // Gray for others
}

</script>
@endsection
