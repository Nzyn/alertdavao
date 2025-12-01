# Report Assignment and Reassignment Implementation

**Date**: December 1, 2025  
**Feature**: Manual report assignment and reassignment request system

## Overview

This implementation adds the ability for admins to manually assign reports to police stations and for police officers to request reassignment of reports to different stations.

## Features Implemented

### 1. Admin Features
- **Assign Report to Station**: Admins can manually assign unassigned reports or reports that fell outside polygon coverage to any police station
- **Manage Reassignment Requests**: New "Requests" sub-menu under Reports where admins can view and approve/reject reassignment requests from police officers

### 2. Police Features
- **Request Reassignment**: Police officers can request to reassign a report to a different police station with an optional reason

## Files Created

### Database
1. **Migration**: `database/migrations/2025_12_01_000000_create_report_reassignment_requests_table.php`
   - Creates `report_reassignment_requests` table with fields:
     - request_id (primary key)
     - report_id (foreign key to reports)
     - requested_by_user_id (foreign key to users)
     - current_station_id (nullable, foreign key to police_stations)
     - requested_station_id (foreign key to police_stations)
     - reason (optional text, max 500 chars)
     - status (enum: pending, approved, rejected)
     - reviewed_by_user_id (nullable, foreign key to users)
     - reviewed_at (nullable timestamp)
     - timestamps

2. **Model**: `app/Models/ReportReassignmentRequest.php`
   - Eloquent model with relationships to Report, User, and PoliceStation

### Backend (Laravel)
3. **Controller Methods** (added to `app/Http/Controllers/ReportController.php`):
   - `assignToStation($id)` - Admin assigns report to a station
   - `requestReassignment($id)` - Police requests reassignment
   - `getReassignmentRequests()` - Admin fetches all requests
   - `reviewReassignmentRequest($requestId)` - Admin approves/rejects request

4. **Routes** (added to `routes/web.php`):
   - `POST /reports/{id}/assign-station` (admin only)
   - `POST /reports/{id}/request-reassignment` (police only)
   - `GET /api/reassignment-requests` (admin only)
   - `POST /reassignment-requests/{id}/review` (admin only)
   - `GET /reassignment-requests` - View page (admin only)

### Frontend (Blade Templates)
5. **Updated**: `resources/views/reports.blade.php`
   - Added CSS styles for action buttons and station select modals
   - Added "Assign to Station" modal for admins
   - Added "Request Reassignment" modal for police
   - Added JavaScript functions:
     - `getActionButtons(report)` - Dynamically shows appropriate button based on user role
     - `loadPoliceStations()` - Fetches available police stations
     - `openAssignStationModal()` / `closeAssignStationModal()`
     - `openReassignmentModal()` / `closeReassignmentModal()`
     - `submitAssignStation()` - Submits assignment
     - `submitReassignmentRequest()` - Submits reassignment request

6. **Created**: `resources/views/reassignment-requests.blade.php`
   - Complete page for admins to manage reassignment requests
   - Table showing all requests with:
     - Request ID
     - Report ID
     - Requested by (officer name)
     - Current station
     - Requested station
     - Date submitted
     - Status badge
   - Modal for viewing request details with approve/reject buttons
   - JavaScript for loading, displaying, and reviewing requests

7. **Updated**: `resources/views/layouts/app.blade.php`
   - Added nested "Requests" menu item under Reports (admin only)

## User Experience Flow

### Admin Workflow
1. Admin views report details and sees "Assign to Station" button for unassigned reports
2. Clicks button, modal opens with dropdown of all police stations
3. Selects station and clicks "Assign Station"
4. Report is immediately assigned to selected station
5. Admin can view reassignment requests via Reports > Requests menu
6. Can approve or reject requests, which updates the report's assigned station automatically

### Police Workflow
1. Police officer views report details
2. Sees "Request Reassignment" button
3. Clicks button, modal opens with:
   - Dropdown of all police stations
   - Optional reason text field
4. Submits request
5. Admin reviews and approves/rejects the request

## Button Visibility Logic
- **Admin users**: See "Assign to Station" button only for reports with `assigned_station_id = null`
- **Police users**: See "Request Reassignment" button for all reports
- Buttons follow the same styling as verification approval buttons for consistency

## API Endpoints

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| POST | `/reports/{id}/assign-station` | admin | Assign report to station |
| POST | `/reports/{id}/request-reassignment` | police | Request reassignment |
| GET | `/api/reassignment-requests` | admin | Get all requests |
| POST | `/reassignment-requests/{id}/review` | admin | Approve/reject request |

## Security
- All routes are protected by authentication middleware
- Role-specific routes use `role:admin` or `role:police` middleware
- CSRF tokens are included in all POST requests
- Foreign key constraints ensure data integrity

## Testing Checklist

### As Admin
- [ ] View report details and see "Assign to Station" button for unassigned reports
- [ ] Successfully assign a report to a station
- [ ] Verify assignment persists after page reload
- [ ] Access Reports > Requests page
- [ ] View reassignment request details
- [ ] Approve a reassignment request
- [ ] Verify report's station updates after approval
- [ ] Reject a reassignment request
- [ ] Verify rejected requests show correct status

### As Police Officer
- [ ] View report details and see "Request Reassignment" button
- [ ] Submit reassignment request with reason
- [ ] Submit reassignment request without reason
- [ ] Verify success message appears
- [ ] Verify request appears in admin's requests page

## Notes
- The system maintains a complete audit trail with timestamps and user IDs
- Requests can only be reviewed once (status changes from pending to approved/rejected)
- When a request is approved, the report's `assigned_station_id` is automatically updated
- The interface matches the existing verification system for consistency
- All police stations are loaded dynamically from the database

## Future Enhancements (Optional)
- Email notifications when requests are approved/rejected
- Dashboard widget showing pending reassignment count
- Bulk approve/reject functionality
- Search and filter on reassignment requests page
- Export reassignment request history
