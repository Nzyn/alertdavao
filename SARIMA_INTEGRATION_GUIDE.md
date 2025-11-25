# SARIMA Crime Forecasting Integration - Complete Guide

## Overview

This integration adds crime forecasting capabilities to the AdminSide statistics page using SARIMA (Seasonal AutoRegressive Integrated Moving Average) time series modeling.

## Features

✅ **Crime Trend Forecasting** - Predict future crime counts for 6-24 months
✅ **Historical Analysis** - View crime trends over time
✅ **Crime by Type** - Doughnut chart showing crime distribution by type
✅ **Top Locations** - Bar chart of top 10 crime locations
✅ **Data Export** - Export crime data as CSV for external analysis
✅ **Real-time Statistics** - Overview cards with month-over-month comparisons
✅ **Confidence Intervals** - 95% confidence intervals for forecasts

## Architecture

```
AdminSide/
├── sarima_api/              # Python FastAPI backend
│   ├── main.py             # SARIMA API endpoints
│   ├── data/
│   │   └── CrimeDAta.csv   # Historical crime data
│   ├── requirements.txt    # Python dependencies
│   ├── start.bat          # Quick start script
│   └── README.md          # API documentation
└── admin/
    ├── app/Http/Controllers/
    │   └── StatisticsController.php  # Laravel controller
    └── resources/views/
        └── statistics.blade.php      # Frontend UI
```

## Setup Instructions

### 1. Install Python Dependencies

```bash
cd AdminSide/sarima_api
pip install -r requirements.txt
```

**Required packages:**
- fastapi
- uvicorn
- pandas
- numpy
- statsmodels
- pydantic

### 2. Start the SARIMA API

**Windows:**
```bash
cd AdminSide/sarima_api
start.bat
```

**Manual:**
```bash
cd AdminSide/sarima_api
python -m uvicorn main:app --reload --host 0.0.0.0 --port 8001
```

The API will be available at: `http://localhost:8001`

### 3. Access the Statistics Page

1. Make sure AdminSide Laravel server is running
2. Navigate to: `http://localhost/statistics`
3. The page will automatically fetch data from both Laravel and SARIMA API

## API Endpoints

### SARIMA API (Port 8001)

- `GET /` - Health check
- `GET /forecast?horizon=12` - Get crime forecast
  - **Parameters:**
    - `horizon` (int): Number of months to forecast (1-60)
  - **Returns:**
    ```json
    {
      "status": "success",
      "horizon": 12,
      "data": [
        {
          "date": "2026-01-01",
          "forecast": 36.64,
          "lower_ci": 33.65,
          "upper_ci": 39.63
        },
        ...
      ]
    }
    ```

### Laravel API

- `GET /api/statistics/forecast?horizon=12` - Proxy to SARIMA API
- `GET /api/statistics/crime-stats` - Get crime statistics from database
- `GET /api/statistics/export` - Export crime data as CSV

## Features Breakdown

### 1. Overview Cards

- **Total Reports** - All-time count from database
- **This Month** - Current month's reports
- **Last Month** - Previous month's reports  
- **Forecast Status** - SARIMA API connection status

### 2. Crime Trends & Forecast Chart

- Line chart showing historical crime data
- Forecasted values (dashed line)
- 95% confidence interval (shaded area)
- Adjustable forecast horizon (6-24 months)
- Interactive tooltips

### 3. Crime by Type

- Doughnut chart visualization
- Distribution of crime types
- Based on last 12 months of data

### 4. Top Locations

- Horizontal bar chart
- Top 10 barangays by crime count
- Last 12 months of data

### 5. Data Export

- **Export Crime Data (CSV)** - Export database records for model training
- **Download Forecast Data (JSON)** - Get raw forecast data from API

## Model Details

### SARIMA Configuration

- **Model:** SARIMA(1,1,1)(1,1,1)[12]
- **Parameters:**
  - (p,d,q) = (1,1,1) - Non-seasonal order
  - (P,D,Q,s) = (1,1,1,12) - Seasonal order (monthly)
- **Training Data:** Historical crime counts from `CrimeDAta.csv`
- **Frequency:** Monthly (MS - Month Start)

### Data Format

The model expects monthly crime data in CSV format:

```csv
Year,Month,Count,Date
2022,1,50,2022-01-01
2022,2,47,2022-02-01
...
```

## Updating the Model

To retrain the model with new data:

1. Export current crime data:
   ```
   Statistics page > Data Export > Export Crime Data (CSV)
   ```

2. Replace `AdminSide/sarima_api/data/CrimeDAta.csv` with the new file

3. Restart the SARIMA API:
   ```bash
   # Stop the current process (Ctrl+C)
   cd AdminSide/sarima_api
   start.bat
   ```

The model will automatically retrain on startup.

## Troubleshooting

### SARIMA API Not Running

**Symptom:** "SARIMA API is not available" error message

**Solution:**
1. Check if Python is installed: `python --version`
2. Install dependencies: `pip install -r requirements.txt`
3. Start API: `cd AdminSide/sarima_api && start.bat`
4. Verify API: Open `http://localhost:8001` in browser

### No Forecast Data

**Symptom:** Only historical data visible, no forecast line

**Solution:**
1. Ensure SARIMA API is running (port 8001)
2. Check browser console for errors
3. Verify API response: `http://localhost:8001/forecast?horizon=12`
4. Restart both servers if needed

### Import Errors (Python)

**Symptom:** `ModuleNotFoundError: No module named 'statsmodels'`

**Solution:**
```bash
pip install --upgrade pip
pip install -r requirements.txt
```

### Database Connection Issues

**Symptom:** Crime statistics not loading

**Solution:**
1. Verify database connection in AdminSide `.env`
2. Check if `reports` table exists
3. Run Laravel migrations if needed

## Performance

- **Model Training:** ~2-5 seconds on startup
- **Forecast Generation:** ~100-500ms per request
- **Database Queries:** Optimized with indexes
- **Chart Rendering:** Client-side using Chart.js

## Future Enhancements

Potential improvements:
- Real-time model updates when new reports are submitted
- Multiple model comparison (ARIMA, Prophet, etc.)
- Anomaly detection
- Seasonal decomposition analysis
- Crime type-specific forecasts
- Geographic forecasting per barangay

## Tech Stack

- **Backend API:** FastAPI (Python)
- **ML/Stats:** statsmodels, pandas, numpy
- **Frontend:** Laravel Blade, Chart.js
- **Database:** MySQL (via Laravel)

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review SARIMA API logs (console output)
3. Check Laravel logs: `AdminSide/admin/storage/logs/laravel.log`
4. Verify network requests in browser DevTools

## License

Part of AlertDavao system.
