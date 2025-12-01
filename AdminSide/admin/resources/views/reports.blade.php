@extends('layouts.app')

@section('title', 'Reports')

@section('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        .reports-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .reports-title-section h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .reports-title-section p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            fill: #9ca3af;
        }

        .reports-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reports-table thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        .reports-table th {
            padding: 0.75rem 0.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        .reports-table th:hover {
            background: #f3f4f6;
        }

        .reports-table th.sortable::after {
            content: '\2195';
            margin-left: 0.5rem;
            opacity: 0.3;
        }

        .reports-table th.sorted-asc::after {
            content: '\2191';
            margin-left: 0.5rem;
            opacity: 1;
        }

        .reports-table th.sorted-desc::after {
            content: '\2193';
            margin-left: 0.5rem;
            opacity: 1;
        }

        .reports-table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .reports-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .reports-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .reports-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Column widths */
        .reports-table th:nth-child(1),
        .reports-table td:nth-child(1) {
            width: 80px;
        }

        /* Report ID */

        .reports-table th:nth-child(2),
        .reports-table td:nth-child(2) {
            width: 120px;
        }

        /* User */

        .reports-table th:nth-child(3),
        .reports-table td:nth-child(3) {
            width: 100px;
        }

        /* Type */

        .reports-table th:nth-child(4),
        .reports-table td:nth-child(4) {
            width: 150px;
        }

        /* Title */

        .reports-table th:nth-child(5),
        .reports-table td:nth-child(5) {
            width: 90px;
        }

        /* Verified */

        .reports-table th:nth-child(6),
        .reports-table td:nth-child(6) {
            width: 120px;
        }

        /* Date Reported */

        .reports-table th:nth-child(7),
        .reports-table td:nth-child(7) {
            width: 120px;
        }

        /* Updated At */

        .reports-table th:nth-child(8),
        .reports-table td:nth-child(8) {
            width: 130px;
        }

        /* Status */

        .reports-table th:nth-child(9),
        .reports-table td:nth-child(9) {
            width: 120px;
        }

        /* Action */

        .verified-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .verified-badge.verified {
            background-color: #d1fae5;
            color: #065f46;
        }

        .verified-badge.unverified {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .verified-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-badge.investigating {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-badge.resolved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .validity-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .validity-badge.valid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .validity-badge.invalid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .validity-badge.checking_for_report_validity {
            background-color: #fef3c7;
            color: #92400e;
        }

        .validity-select {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            font-size: 0.813rem;
            font-weight: 500;
            background-color: white;
            cursor: pointer;
            width: 100%;
            max-width: 150px;
            transition: all 0.2s ease;
            color: #374151;
        }

        .validity-select:hover {
            border-color: #3b82f6;
            background-color: #f9fafb;
        }

        .validity-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .status-select {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            font-size: 0.813rem;
            font-weight: 500;
            background-color: white;
            cursor: pointer;
            width: 100%;
            max-width: 140px;
            transition: all 0.2s ease;
            color: #374151;
        }

        .status-select:hover {
            border-color: #10b981;
            background-color: #f9fafb;
        }

        .status-select:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #6b7280;
            margin-right: 0.25rem;
            width: 36px;
            height: 36px;
        }

        .action-btn:hover {
            background: #f9fafb;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .report-id {
            font-family: 'Courier New', monospace;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .no-results {
            text-align: center;
            padding: 3rem 1rem;
            color: #9ca3af;
        }

        .pagination {
            display: flex;
            justify-content: center;
            padding: 1.5rem;
            gap: 0.5rem;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .pagination a {
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }

        .pagination a:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .pagination .active {
            background: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
        }

        .pagination .disabled {
            color: #d1d5db;
            cursor: not-allowed;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            width: 800px;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #9ca3af;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .report-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            margin-bottom: 1rem;
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 0.875rem;
            color: #1f2937;
        }

        .media-container {
            margin-top: 1.5rem;
        }

        .media-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .media-item {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
            cursor: pointer;
        }

        .media-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            transition: transform 0.2s ease;
        }

        .media-item:hover img {
            transform: scale(1.05);
        }

        .enlarge-icon {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .media-item:hover .enlarge-icon {
            opacity: 1;
        }

        .media-item.video {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            height: 150px;
        }

        .media-placeholder {
            width: 100%;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Image Lightbox */
        .lightbox-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
        }

        .lightbox-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 90vh;
        }

        .lightbox-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.6);
            border: 2px solid rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10002;
            padding: 0;
            line-height: 1;
        }

        .lightbox-close:hover {
            background: rgba(0, 0, 0, 0.8);
            border-color: white;
            transform: scale(1.1);
        }

        /* Map Container Styles */
        .report-map-container {
            margin-top: 1.5rem;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .report-map-header {
            background: #f9fafb;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .report-map-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        #reportDetailMap {
            width: 100%;
            height: 300px;
            background: #f3f4f6;
        }

        /* Custom Leaflet popup styles */
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .leaflet-popup-content {
            margin: 12px 16px;
            line-height: 1.5;
        }

        /* Action Buttons Section */
        .actions-section {
            display: flex;
            gap: 0.75rem;
            padding: 1.5rem;
            padding-left: 0;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: #2563eb;
        }

        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }

        .btn-warning:hover:not(:disabled) {
            background-color: #d97706;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background-color: #059669;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            background-color: #dc2626;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: #4b5563;
        }

        /* Station Assignment Modal */
        .station-select-container {
            margin: 1rem 0;
        }

        .station-select-container label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }

        .station-select-container select,
        .station-select-container textarea {
            width: 100%;
            padding: 0.625rem;
            border: 1.5px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .station-select-container select:focus,
        .station-select-container textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        @media (max-width: 768px) {
            .reports-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-box {
                width: 100%;
            }

            .reports-table-container {
                overflow-x: auto;
            }

            .reports-table {
                min-width: 850px;
            }

            .report-details-grid {
                grid-template-columns: 1fr;
            }
        }

        /* PDF Styles */
        .pdf-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .alertWelcome {
            color: "#1D3557";
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .davao {
            color: black;
            margin-left: 5px;
            font-size: 30px;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="reports-header">
        <div class="reports-title-section">
            <h1>Reports</h1>
            <p>Manage and view all incident reports</p>
        </div>

        <div class="search-box">
            <svg class="search-icon" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input type="text" class="search-input" placeholder="Search reports..." id="searchInput"
                onkeyup="searchReports()">
        </div>
    </div>

    <div class="reports-table-container">
        <table class="reports-table" id="reportsTable">
            <thead>
                <tr>
                    <th class="sortable" data-column="0" style="width: 80px;" onclick="sortTable(0)">Report ID</th>
                    <th class="sortable" data-column="1" style="width: 120px;" onclick="sortTable(1)">User</th>
                    <th class="sortable" data-column="2" style="width: 100px;" onclick="sortTable(2)">Type</th>
                    <th class="sortable" data-column="3" style="width: 150px;" onclick="sortTable(3)">Title</th>
                    <th class="sortable" data-column="4" style="width: 90px;" onclick="sortTable(4)">User Status</th>
                     <th class="sortable" data-column="5" style="width: 120px;" onclick="sortTable(5)">Date Reported</th>
                     <th class="sortable" data-column="6" style="width: 120px;" onclick="sortTable(6)">Updated At</th>
                     <th class="sortable" data-column="7" style="width: 130px;" onclick="sortTable(7)">Report Status</th>
                     <th style="width: 140px;">Validity</th>
                     <th style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                            <tr data-report-id="{{ $report->report_id }}">
                                <td class="report-id">{{ str_pad($report->report_id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    @if($report->user)
                                        {{ substr($report->user->firstname, 0, 1) }}. {{ substr($report->user->lastname, 0, 1) }}.
                                    @else
                                        Unknown
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $reportType = $report->report_type ?? 'N/A';
                                        if (is_string($reportType)) {
                                            $decoded = json_decode($reportType, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $reportType = implode(', ', $decoded);
                                            }
                                        } elseif (is_array($reportType)) {
                                            $reportType = implode(', ', $reportType);
                                        }
                                    @endphp
                                    {{ \Illuminate\Support\Str::limit($reportType, 20) }}
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($report->title, 30) }}</td>
                                <td>
                                    <?php 
                                        $user = $report->user;
                    $verification = $user ? $user->verification : null;
                    $verifiedStatus = $verification ? $verification->is_verified : null;
                    $verificationStatus = 'unverified';
                    if ($verifiedStatus === 1) {
                        $verificationStatus = 'verified';
                    } elseif ($verifiedStatus === 0) {
                        $verificationStatus = 'pending';
                    }
                                    ?>
                                    <span class="verified-badge {{ $verificationStatus }}">
                                        @if($verificationStatus === 'verified')
                                            Verified
                                        @elseif($verificationStatus === 'pending')
                                            Pending
                                        @else
                                            Unverified
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $report->created_at->timezone('Asia/Manila')->format('m/d/Y H:i') }}</td>
                                <td>{{ $report->updated_at->timezone('Asia/Manila')->format('m/d/Y H:i') }}</td>
                                <td>
                                    <?php    $reportId = $report->report_id;
                                $status = $report->status; ?>
                                    <select class="status-select" onchange="updateStatus(<?php    echo $reportId; ?>, this.value)"
                                        data-original-status="<?php    echo $status; ?>">
                                        <option value="pending" <?php    echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="investigating" <?php    echo $status === 'investigating' ? 'selected' : ''; ?>>
                                            Investigating</option>
                                        <option value="resolved" <?php    echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                </td>
                                <td>
                                    <?php    $reportId = $report->report_id;
                                $isValid = $report->is_valid ?? 'checking_for_report_validity'; ?>
                                    <select class="validity-select" onchange="updateValidity(<?php    echo $reportId; ?>, this.value)"
                                        data-original-validity="<?php    echo $isValid; ?>">
                                        <option value="checking_for_report_validity" <?php    echo $isValid === 'checking_for_report_validity' ? 'selected' : ''; ?>>Checking...</option>
                                        <option value="valid" <?php    echo $isValid === 'valid' ? 'selected' : ''; ?>>Valid</option>
                                        <option value="invalid" <?php    echo $isValid === 'invalid' ? 'selected' : ''; ?>>Invalid</option>
                                    </select>
                                </td>
                                <td>
                                    <?php    $reportId = $report->report_id; ?>
                                    <button class="action-btn" title="View Details"
                                        onclick="showReportDetails(<?php    echo $reportId; ?>)">
                                        <svg class="action-icon" viewBox="0 0 24 24" width="18" height="18">
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download Report"
                                        onclick="downloadReport(<?php    echo $reportId; ?>)">
                                        <svg class="action-icon" viewBox="0 0 24 24" width="18" height="18">
                                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                @empty
                    <tr>
                        <td colspan="9" class="no-results">
                            <svg style="width: 48px; height: 48px; margin: 0 auto 1rem; opacity: 0.3;" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                            </svg>
                            <p>No reports found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Report Details Modal -->
    <div class="modal-overlay" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Report Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Image Lightbox -->
    <div class="lightbox-overlay" id="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <div class="lightbox-content">
            <img id="lightboxImage" src="" alt="Enlarged view">
        </div>
    </div>

    <!-- Assign Station Modal (Admin) -->
    <div class="modal-overlay" id="assignStationModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 class="modal-title">Assign Report to Station</h2>
                <button class="modal-close" onclick="closeAssignStationModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="station-select-container">
                    <label for="assignStationSelect">Select Police Station</label>
                    <select id="assignStationSelect" class="station-select">
                        <option value="">-- Select a station --</option>
                    </select>
                </div>
                <div class="actions-section" style="margin-top: 0; border-top: none; background: white;">
                    <button class="btn btn-primary" onclick="submitAssignStation()">
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        Assign Station
                    </button>
                    <button class="btn btn-secondary" onclick="closeAssignStationModal()">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Reassignment Modal (Police) -->
    <div class="modal-overlay" id="requestReassignmentModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 class="modal-title">Request Report Reassignment</h2>
                <button class="modal-close" onclick="closeReassignmentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="station-select-container">
                    <label for="reassignStationSelect">Reassign to Police Station</label>
                    <select id="reassignStationSelect" class="station-select">
                        <option value="">-- Select a station --</option>
                    </select>
                </div>
                <div class="station-select-container">
                    <label for="reassignReason">Reason for Reassignment</label>
                    <textarea id="reassignReason" rows="3" placeholder="Optional: Provide a reason for this reassignment request..."></textarea>
                </div>
                <div class="actions-section" style="margin-top: 0; border-top: none; background: white;">
                    <button class="btn btn-warning" onclick="submitReassignmentRequest()">
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2z"/>
                        </svg>
                        Submit Request
                    </button>
                    <button class="btn btn-secondary" onclick="closeReassignmentModal()">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($reports->hasPages())
<div class="pagination">
    {{-- Previous Page Link --}}
    @if ($reports->onFirstPage())
        <span class="disabled">&laquo;</span>
    @else
        <a href="{{ $reports->previousPageUrl() }}">&laquo;</a>
    @endif

    {{-- Pagination Elements --}}
    @php
        $currentPage = $reports->currentPage();
        $lastPage = $reports->lastPage();
        $maxPagesToShow = 10;
        $halfMax = floor($maxPagesToShow / 2);
        
        // Calculate start and end pages
        if ($lastPage <= $maxPagesToShow) {
            $startPage = 1;
            $endPage = $lastPage;
        } else {
            if ($currentPage <= $halfMax) {
                $startPage = 1;
                $endPage = $maxPagesToShow;
            } elseif ($currentPage >= $lastPage - $halfMax) {
                $startPage = $lastPage - $maxPagesToShow + 1;
                $endPage = $lastPage;
            } else {
                $startPage = $currentPage - $halfMax;
                $endPage = $currentPage + $halfMax;
            }
        }
    @endphp

    {{-- First Page --}}
    @if ($startPage > 1)
        <a href="{{ $reports->url(1) }}">1</a>
        @if ($startPage > 2)
            <span class="disabled">...</span>
        @endif
    @endif

    {{-- Page Numbers --}}
    @for ($i = $startPage; $i <= $endPage; $i++)
        @if ($i == $currentPage)
            <span class="active">{{ $i }}</span>
        @else
            <a href="{{ $reports->url($i) }}">{{ $i }}</a>
        @endif
    @endfor

    {{-- Last Page --}}
    @if ($endPage < $lastPage)
        @if ($endPage < $lastPage - 1)
            <span class="disabled">...</span>
        @endif
        <a href="{{ $reports->url($lastPage) }}">{{ $lastPage }}</a>
    @endif

    {{-- Next Page Link --}}
    @if ($reports->hasMorePages())
        <a href="{{ $reports->nextPageUrl() }}">&raquo;</a>
    @else
        <span class="disabled">&raquo;</span>
    @endif
</div>
@endif

@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        // Global variables for lightbox
        let currentImages = [];
        let currentImageIndex = 0;
        let reportDetailMap = null; // Global variable for the report detail map

        // Initialize jsPDF
        const { jsPDF } = window.jspdf;

        // Auto-refresh reports every 10 seconds for live updates
        let autoRefreshInterval = null;
        let lastReportCount = {{ $reports->total() ?? 0 }};

        function checkForNewReports() {
            // Get current URL with all query parameters
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            
            // Add a timestamp to prevent caching
            url.searchParams.set('check_new', Date.now());
            
            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.text())
            .then(html => {
                // Parse the HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('#reportsTable tbody');
                const currentTableBody = document.querySelector('#reportsTable tbody');
                
                if (newTableBody && currentTableBody) {
                    const newRowCount = newTableBody.querySelectorAll('tr').length;
                    const currentRowCount = currentTableBody.querySelectorAll('tr').length;
                    
                    // Check if there are new reports
                    if (newRowCount !== currentRowCount) {
                        console.log('New reports detected! Updating table...');
                        
                        // Show notification
                        showNewReportNotification(newRowCount - currentRowCount);
                        
                        // Update the table
                        currentTableBody.innerHTML = newTableBody.innerHTML;
                        
                        // Update pagination if exists
                        const newPagination = doc.querySelector('.pagination');
                        const currentPagination = document.querySelector('.pagination');
                        if (newPagination && currentPagination) {
                            currentPagination.innerHTML = newPagination.innerHTML;
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error checking for new reports:', error);
            });
        }

        function showNewReportNotification(count) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #10b981;
                color: white;
                padding: 16px 24px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                font-weight: 500;
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = count === 1 
                ? '🚨 New report received!' 
                : `🚨 ${count} new reports received!`;
            
            document.body.appendChild(notification);
            
            // Remove notification after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
            
            // Play notification sound (optional)
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIGWi77eafTRALT6fk77RgGwU7k9bxy3ktBSJ1xe/glEILElyx6OyrVhYMQp3e8bhlHQYogczx2Ik2CBlouezmn00QC06m5O+0YRsGOJHX8ct5LQUidMXu4ZVDBBFYrOfqrFgWDT+a3vK6aB4HI33H8NqJNwgZaLvt559NEAtOpuTvtGEbBjiR1/HLeS0FInXF7+KWRAUSVqnm6axaGQ0+m97yuWgeBx9+yPDaiTYHGGi77+SfTBEMTKbk7bNhHAQ4kdXzyn0tBSJ1xe/jl0QGEVan5eitWhsMPpne87ppHwcdfMbv2ok3CBdpvO7kn0wRDU2m4+60YRsGOZPY88p9LQQhdsfv45dFBhFWp+XprVsbDD6Y3/K6ah8HHn7J79qKNggXabzv5J9MEAJQ');
                audio.play();
            } catch (e) {
                // Ignore audio errors
            }
        }

        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Check for new reports every 3 seconds for real-time updates
            autoRefreshInterval = setInterval(checkForNewReports, 3000);
            console.log('Auto-refresh enabled: Checking for new reports every 3 seconds');
        });

        // Stop auto-refresh when page is hidden/user switches tabs
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    console.log('Auto-refresh paused (page hidden)');
                }
            } else {
                if (!autoRefreshInterval) {
                    autoRefreshInterval = setInterval(checkForNewReports, 3000);
                    console.log('Auto-refresh resumed (page visible)');
                    // Check immediately when page becomes visible
                    checkForNewReports();
                }
            }
        });

        // Add CSS animation for notification
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        function searchReports() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('reportsTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let txtValue = tr[i].textContent || tr[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }

        let sortDirections = {};

        function sortTable(columnIndex) {
            const table = document.getElementById('reportsTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            const headers = table.getElementsByTagName('th');

            // Toggle sort direction
            if (!sortDirections[columnIndex]) {
                sortDirections[columnIndex] = 'asc';
            } else if (sortDirections[columnIndex] === 'asc') {
                sortDirections[columnIndex] = 'desc';
            } else {
                sortDirections[columnIndex] = 'asc';
            }

            const direction = sortDirections[columnIndex];

            // Remove sort classes from all headers
            for (let i = 0; i < headers.length; i++) {
                headers[i].classList.remove('sorted-asc', 'sorted-desc');
            }

            // Add sort class to current header
            headers[columnIndex].classList.add(direction === 'asc' ? 'sorted-asc' : 'sorted-desc');

            // Sort rows
            rows.sort((a, b) => {
                let aValue = a.getElementsByTagName('td')[columnIndex]?.textContent.trim() || '';
                let bValue = b.getElementsByTagName('td')[columnIndex]?.textContent.trim() || '';

                // Handle numeric values (Report ID)
                if (columnIndex === 0) {
                    aValue = parseInt(aValue) || 0;
                    bValue = parseInt(bValue) || 0;
                }
                // Handle dates (columns 4 and 5)
                else if (columnIndex === 4 || columnIndex === 5) {
                    aValue = new Date(aValue).getTime() || 0;
                    bValue = new Date(bValue).getTime() || 0;
                }

                if (aValue < bValue) return direction === 'asc' ? -1 : 1;
                if (aValue > bValue) return direction === 'asc' ? 1 : -1;
                return 0;
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        }

        function updateStatus(reportId, status) {
             // Store reference to the select element before the fetch call
             const selectElement = event.target;

             fetch(`/reports/${reportId}/status`, {
                 method: 'PUT',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                 },
                 body: JSON.stringify({
                     status: status
                 })
             })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         // Update successful - no need to update UI since we're using a select dropdown
                         // The select already shows the current status
                         // Update the original status attribute
                         selectElement.setAttribute('data-original-status', status);

                         alert('Status updated successfully');
                     } else {
                         alert('Failed to update status: ' + (data.message || 'Unknown error'));
                         // Revert to original status
                         selectElement.value = selectElement.getAttribute('data-original-status');
                     }
                 })
                 .catch(error => {
                     console.error('Error:', error);
                     alert('An error occurred while updating status: ' + (error.message || 'Unknown error'));
                     // Revert to original status
                     selectElement.value = selectElement.getAttribute('data-original-status');
                 });
         }

         function updateValidity(reportId, isValid) {
             // Store reference to the select element before the fetch call
             const selectElement = event.target;

             fetch(`/reports/${reportId}/validity`, {
                 method: 'PUT',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                 },
                 body: JSON.stringify({
                     is_valid: isValid
                 })
             })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         // Update successful - no need to update UI since we're using a select dropdown
                         // The select already shows the current validity status
                         // Update the original validity attribute
                         selectElement.setAttribute('data-original-validity', isValid);

                         alert('Report validity status updated successfully');
                     } else {
                         alert('Failed to update validity status: ' + (data.message || 'Unknown error'));
                         // Revert to original validity status
                         selectElement.value = selectElement.getAttribute('data-original-validity');
                     }
                 })
                 .catch(error => {
                     console.error('Error:', error);
                     alert('An error occurred while updating validity status: ' + (error.message || 'Unknown error'));
                     // Revert to original validity status
                     selectElement.value = selectElement.getAttribute('data-original-validity');
                 });
         }

        function showReportDetails(reportId) {
            fetch(`/reports/${reportId}/details`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const report = data.data;
                        const modalBody = document.getElementById('modalBody');

                        // Format the content for the modal
                        let mediaContent = '';
                        if (report.media && report.media.length > 0) {
                            mediaContent = `
                         <div class="media-container">
                             <h3 class="media-title">Media Files</h3>
                             <div class="media-grid">
                     `;

                            // Use the properly formatted display_url from the backend
                             const getMediaUrl = (media) => {
                                  if (!media) return null;
                                  // Prefer display_url which is generated by the backend
                                  if (media.display_url) {
                                      return media.display_url;
                                  }
                                  // Fallback to media_url if display_url not available
                                  if (!media.media_url) return null;
                                  // If already a full URL (starts with http), use as is
                                  if (media.media_url.startsWith('http')) {
                                      return media.media_url;
                                  }
                                  // If relative path, construct the full storage URL
                                  const url = media.media_url.startsWith('/storage/') 
                                      ? media.media_url 
                                      : `/storage/${media.media_url}`;
                                  return url;
                              };

                            // Filter only images for the lightbox
                            const imageUrls = report.media
                                .filter(media => {
                                    const ext = (media.media_type || '').toLowerCase();
                                    return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
                                })
                                .map(media => getMediaUrl(media))
                                .filter(url => url !== null);

                            report.media.forEach((media, index) => {
                                const mediaUrl = getMediaUrl(media);
                                const mediaType = (media.media_type || '').toLowerCase();

                                // Determine file type
                                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(mediaType);
                                const isVideo = ['mp4', 'mov', 'avi', 'webm'].includes(mediaType);

                                if (isImage) {
                                    // Find the index of this image in the imageUrls array
                                    const imageIndex = imageUrls.indexOf(mediaUrl);
                                    // Escape quotes in the JSON string for the onclick handler
                                    const imageUrlsJson = JSON.stringify(imageUrls).replace(/"/g, '&quot;');
                                    mediaContent += `
                                 <div class="media-item" onclick="openLightbox(${imageIndex}, '${imageUrlsJson}')">
                                     <img src="${mediaUrl}" alt="Report media" onerror="this.src='https://placehold.co/200x150?text=Image+Not+Found'" loading="lazy">
                                     <div class="enlarge-icon">
                                         <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                             <path d="M15 3l2.3 2.3-2.89 2.87 1.42 1.42L18.7 6.7 21 9V3zM3 9l2.3-2.3 2.87 2.89 1.42-1.42L6.7 5.3 9 3H3zm6 12l-2.3-2.3 2.89-2.87-1.42-1.42L5.3 17.3 3 15v6zm12-6l-2.3 2.3-2.87-2.89-1.42 1.42 2.89 2.87L15 21h6z"/>
                                         </svg>
                                     </div>
                                 </div>
                             `;
                                } else if (isVideo) {
                                    mediaContent += `
                                 <div class="media-item video">
                                     <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor">
                                         <path d="M8 5v14l11-7z"/>
                                     </svg>
                                     <span>Video File</span>
                                 </div>
                             `;
                                } else {
                                    mediaContent += `
                                 <div class="media-item">
                                     <div class="media-placeholder">
                                         <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                                             <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                         </svg>
                                     </div>
                                 </div>
                             `;
                                }
                            });

                            mediaContent += `
                             </div>
                         </div>
                     `;
                        } else {
                            mediaContent = '<p>No media files available for this report.</p>';
                        }

                        // Create map container HTML
                        const mapContainer = `
                    <div class="report-map-container">
                        <div class="report-map-header">
                            <h3 class="report-map-title">Report Location</h3>
                        </div>
                        <div id="reportDetailMap"></div>
                    </div>
                `;

                        modalBody.innerHTML = `
                    <div class="report-details-grid">
                        <div>
                            <div class="detail-item">
                                <div class="detail-label">Title</div>
                                <div class="detail-value">${report.title || 'No title provided'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value">${getLocationDisplay(report)}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Description</div>
                                <div class="detail-value">${report.description || 'No description provided'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Report ID</div>
                                <div class="detail-value">${report.report_id.toString().padStart(5, '0')}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Report Type</div>
                                <div class="detail-value">${Array.isArray(report.report_type) ? report.report_type.join(', ') : (report.report_type || 'N/A')}</div>
                            </div>
                        </div>
                        <div>
                            <div class="detail-item">
                                <div class="detail-label">Date Reported</div>
                                <div class="detail-value">${new Date(report.created_at).toLocaleString('en-US', { timeZone: 'Asia/Manila' })}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value">${new Date(report.updated_at).toLocaleString('en-US', { timeZone: 'Asia/Manila' })}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">User</div>
                                <div class="detail-value">
                                    ${report.is_anonymous ? 'Anonymous' : (report.user ? report.user.firstname + ' ' + report.user.lastname : 'Unknown User')}
                                    ${getVerificationBadge(report)}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span class="status-badge ${report.status}">${report.status}</span>
                                </div>
                            </div>
                            ${getActionButtons(report)}
                        </div>
                    </div>
                </div>
                ${mapContainer}
                ${mediaContent}
            `;
            
            // Store current report ID globally for modal actions
            window.currentReportId = reportId;
            
            // Show the modal
            document.getElementById('reportModal').classList.add('active');
            
            // Initialize map after modal is shown and DOM is updated
            setTimeout(() => {
                initializeReportMap(report);
            }, 100);
        } else {
            alert('Failed to load report details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading report details: ' + error.message);
    });
}

function getLocationDisplay(report) {
     // Check if location object exists with barangay name
     if (report.location && report.location.barangay) {
         const barangay = report.location.barangay;
         // Only show barangay if it's not just coordinates
         if (barangay && barangay !== 'Unknown' && !barangay.startsWith('Lat:') && !barangay.includes(',')) {
             return barangay;
         }
     }
     
     // No valid location name found
     return 'Location not specified';
 }

function getVerificationBadge(report) {
     // Return empty string if user is anonymous or doesn't exist
     if (report.is_anonymous || !report.user) {
         return '';
     }
     
     // Check if user is verified (email_verified_at is not null)
     if (report.user.email_verified_at) {
         return '<span style="display: inline-block; margin-left: 6px; background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">✓ Verified</span>';
     }
     
     return '';
 }

 function getActionButtons(report) {
     const userRole = '{{ auth()->user()->role ?? "" }}';
     const isUnassigned = !report.assigned_station_id;
     
     let buttons = '';
     
     // For admin users - always show assign/reassign button
     if (userRole === 'admin') {
         const buttonText = isUnassigned ? 'Assign to Station' : 'Reassign Station';
         buttons = `
             <div class="actions-section">
                 <button class="btn btn-primary" onclick="openAssignStationModal(${report.report_id})">
                     <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                         <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                     </svg>
                     ${buttonText}
                 </button>
             </div>
         `;
     }
     // For police users
     else if (userRole === 'police') {
         buttons = `
             <div class="actions-section">
                 <button class="btn btn-warning" onclick="openReassignmentModal(${report.report_id})">
                     <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                         <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                     </svg>
                     Request Reassignment
                 </button>
             </div>
         `;
     }
     
     return buttons;
 }

 // Station assignment/reassignment functions
 let policeStations = [];

 function loadPoliceStations() {
     if (policeStations.length > 0) return Promise.resolve();
     
     return fetch('/api/police-stations')
         .then(response => response.json())
         .then(data => {
             policeStations = data;
             console.log('Loaded police stations:', policeStations);
         })
         .catch(error => {
             console.error('Error loading police stations:', error);
             alert('Failed to load police stations');
         });
 }

 function populateStationSelect(selectId) {
     const select = document.getElementById(selectId);
     select.innerHTML = '<option value="">-- Select a station --</option>';
     
     policeStations.forEach(station => {
         const option = document.createElement('option');
         option.value = station.station_id;
         option.textContent = station.station_name;
         select.appendChild(option);
     });
 }

 function openAssignStationModal(reportId) {
     window.currentReportId = reportId;
     loadPoliceStations().then(() => {
         populateStationSelect('assignStationSelect');
         document.getElementById('assignStationModal').classList.add('active');
     });
 }

 function closeAssignStationModal() {
     document.getElementById('assignStationModal').classList.remove('active');
     document.getElementById('assignStationSelect').value = '';
 }

 function openReassignmentModal(reportId) {
     window.currentReportId = reportId;
     loadPoliceStations().then(() => {
         populateStationSelect('reassignStationSelect');
         document.getElementById('requestReassignmentModal').classList.add('active');
     });
 }

 function closeReassignmentModal() {
     document.getElementById('requestReassignmentModal').classList.remove('active');
     document.getElementById('reassignStationSelect').value = '';
     document.getElementById('reassignReason').value = '';
 }

 function submitAssignStation() {
     const stationId = document.getElementById('assignStationSelect').value;
     
     if (!stationId) {
         alert('Please select a police station');
         return;
     }
     
     fetch(`/reports/${window.currentReportId}/assign-station`, {
         method: 'POST',
         headers: {
             'Content-Type': 'application/json',
             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
         },
         body: JSON.stringify({ station_id: stationId })
     })
     .then(response => response.json())
     .then(data => {
         if (data.success) {
             alert('Report successfully assigned to station');
             closeAssignStationModal();
             closeModal();
             location.reload(); // Refresh to show updated data
         } else {
             alert('Failed to assign report: ' + (data.message || 'Unknown error'));
         }
     })
     .catch(error => {
         console.error('Error:', error);
         alert('An error occurred while assigning the report');
     });
 }

 function submitReassignmentRequest() {
     const stationId = document.getElementById('reassignStationSelect').value;
     const reason = document.getElementById('reassignReason').value;
     
     if (!stationId) {
         alert('Please select a police station');
         return;
     }
     
     fetch(`/reports/${window.currentReportId}/request-reassignment`, {
         method: 'POST',
         headers: {
             'Content-Type': 'application/json',
             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
         },
         body: JSON.stringify({ 
             station_id: stationId,
             reason: reason
         })
     })
     .then(response => response.json())
     .then(data => {
         if (data.success) {
             alert('Reassignment request submitted successfully');
             closeReassignmentModal();
             closeModal();
         } else {
             alert('Failed to submit request: ' + (data.message || 'Unknown error'));
         }
     })
     .catch(error => {
         console.error('Error:', error);
         alert('An error occurred while submitting the request');
     });
 }
 
 function initializeReportMap(report) {
     // Remove existing map if it exists
     if (reportDetailMap) {
         reportDetailMap.remove();
         reportDetailMap = null;
     }
     
     // Get latitude and longitude from the report's location object
     const lat = report.location ? (report.location.latitude || report.location.lat) : (report.latitude || report.lat);
     const lng = report.location ? (report.location.longitude || report.location.lng || report.location.long) : (report.longitude || report.lng || report.long);
     
     // Default to Davao City center if no coordinates
     const latitude = lat ? parseFloat(lat) : 7.1907;
     const longitude = lng ? parseFloat(lng) : 125.4553;
     const hasValidCoordinates = lat && lng;
     
     console.log('Report coordinates:', { lat, lng, hasValidCoordinates });
     
     // Davao City bounds for geofencing
     const davaoCityBounds = [
         [6.9, 125.2],  // Southwest corner
         [7.5, 125.7]   // Northeast corner
     ];
     
     // Initialize the map with geofencing
     reportDetailMap = L.map('reportDetailMap', {
         maxBounds: davaoCityBounds,
         maxBoundsViscosity: 1.0,
         minZoom: 11,
         maxZoom: 18
     }).setView([latitude, longitude], hasValidCoordinates ? 15 : 13);
     
     // Add OpenStreetMap tile layer
     L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
         attribution: '© OpenStreetMap contributors',
         maxZoom: 18,
     }).addTo(reportDetailMap);
     
     // Add a RED marker for the crime location if coordinates are valid
     if (hasValidCoordinates) {
         // Create custom red person icon for crime location
         const redIcon = L.divIcon({
             className: 'custom-marker-icon',
             html: `<div style="position: relative; width: 40px; height: 40px;">
                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                     <circle cx="12" cy="8" r="5" fill="#EF4444" stroke="white" stroke-width="1.5"/>
                     <path d="M12 14c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6z" fill="#EF4444" stroke="white" stroke-width="1.5"/>
                 </svg>
             </div>`,
             iconSize: [40, 40],
             iconAnchor: [20, 40],
             popupAnchor: [0, -40]
         });
         
         const crimeMarker = L.marker([latitude, longitude], { icon: redIcon }).addTo(reportDetailMap);
         
         // Add popup with location info
         const locationName = getLocationDisplay(report);
         const popupContent = `
             <div style="text-align: center; min-width: 150px;">
                 <strong style="color: #EF4444; font-size: 14px;">📍 Crime Location</strong><br>
                 <strong style="font-size: 13px; margin-top: 8px; display: block;">${report.title || 'Incident Report'}</strong><br>
                 <span style="font-size: 12px; color: #666;">${locationName}</span><br>
                 <span style="font-size: 11px; color: #999; margin-top: 4px; display: block;">
                     ${latitude.toFixed(6)}, ${longitude.toFixed(6)}
                 </span>
             </div>
         `;
         crimeMarker.bindPopup(popupContent).openPopup();
     }
     
     // Fetch and add police station markers (BLUE pins)
     fetch(`/reports/${report.report_id}/details`)
         .then(response => response.json())
         .then(data => {
             if (data.success && data.policeStations) {
                 console.log('Police stations loaded:', data.policeStations.length);
                 
                 // Create custom blue shield/officer icon for police stations
                 const blueIcon = L.divIcon({
                     className: 'custom-marker-icon',
                     html: `<div style="position: relative; width: 40px; height: 40px;">
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                             <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" fill="#3B82F6" stroke="white" stroke-width="1"/>
                             <circle cx="12" cy="10" r="2.5" fill="white"/>
                             <path d="M12 13c-2 0-3.5 1-3.5 2v1.5h7V15c0-1-1.5-2-3.5-2z" fill="white"/>
                         </svg>
                     </div>`,
                     iconSize: [40, 40],
                     iconAnchor: [20, 40],
                     popupAnchor: [0, -40]
                 });
                 
                 let stationsAdded = 0;
                 data.policeStations.forEach(station => {
                     // Only add stations with valid, non-zero coordinates
                     if (station.latitude && station.longitude && 
                         parseFloat(station.latitude) !== 0 && 
                         parseFloat(station.longitude) !== 0) {
                         
                         const stationMarker = L.marker(
                             [parseFloat(station.latitude), parseFloat(station.longitude)], 
                             { icon: blueIcon }
                         ).addTo(reportDetailMap);
                         
                         const stationPopup = `
                             <div style="text-align: center; min-width: 150px;">
                                 <strong style="color: #3B82F6; font-size: 14px;">🚔 Police Station</strong><br>
                                 <strong style="font-size: 13px; margin-top: 8px; display: block;">${station.station_name}</strong><br>
                                 <span style="font-size: 11px; color: #666; margin-top: 4px; display: block;">${station.address || 'N/A'}</span>
                             </div>
                         `;
                         stationMarker.bindPopup(stationPopup);
                         stationsAdded++;
                     }
                 });
                 console.log('Police station markers added:', stationsAdded);
             }
         })
         .catch(error => {
             console.error('Error loading police stations:', error);
         });
     
     // Invalidate size to ensure proper rendering
     setTimeout(() => {
         if (reportDetailMap) {
             reportDetailMap.invalidateSize();
         }
     }, 200);
 }
 
 function closeModal() {
    // Remove the map when closing modal
    if (reportDetailMap) {
        reportDetailMap.remove();
        reportDetailMap = null;
    }
    document.getElementById('reportModal').classList.remove('active');
}

function downloadReport(reportId) {
    fetch(`/reports/${reportId}/details`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const report = data.data;
            generatePDF(report);
        } else {
            alert('Failed to load report details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading report details: ' + error.message);
    });
}

function generatePDF(report) {
    // Create a temporary HTML element for rendering
    const tempContainer = document.createElement('div');
    tempContainer.style.position = 'absolute';
    tempContainer.style.left = '-9999px';
    tempContainer.style.width = '800px';
    tempContainer.style.padding = '20px';
    tempContainer.style.fontFamily = 'Arial, sans-serif';
    tempContainer.style.backgroundColor = 'white';
    
    // Get location display
    const locationDisplay = getLocationDisplay(report);
    
    // Get user display (anonymous or actual name)
    const userDisplay = report.is_anonymous ? 'Anonymous' : (report.user ? report.user.firstname + ' ' + report.user.lastname : 'Unknown User');
    
    // Determine verification status
    let verificationStatus = 'Unverified';
    if (!report.is_anonymous && report.user) {
        if (report.user.email_verified_at) {
            verificationStatus = 'Verified';
        } else if (report.user.verification_status === 'pending') {
            verificationStatus = 'Pending';
        }
    }
    
    // Create HTML content for the PDF
    tempContainer.innerHTML = `
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1D3557; padding-bottom: 20px;">
            <div class="alertWelcome">Alert</div>
            <div class="davao">Davao</div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Title</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">${report.title || 'No title provided'}</div>
                
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Location</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">${locationDisplay}</div>
                
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Description</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">${report.description || 'No description provided'}</div>
                
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Report ID</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">${report.report_id.toString().padStart(5, '0')}</div>
                
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Report Type</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">${Array.isArray(report.report_type) ? report.report_type.join(', ') : (report.report_type || 'N/A')}</div>
            </div>
            <div>
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Date Reported</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">${new Date(report.created_at).toLocaleString('en-US', { timeZone: 'Asia/Manila' })}</div>
                
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Last Updated</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">${new Date(report.updated_at).toLocaleString('en-US', { timeZone: 'Asia/Manila' })}</div>
                
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">User</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">
                    ${userDisplay} 
                    <span style="display: inline-block; margin-left: 8px; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; 
                        ${verificationStatus === 'Verified' ? 'background-color: #d1fae5; color: #065f46;' : verificationStatus === 'Pending' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #991b1b;'}">
                        ${verificationStatus}
                    </span>
                </div>
                
                <div style="font-weight: bold; margin-bottom: 5px; color: #1D3557;">Status</div>
                <div style="margin-bottom: 15px; padding-left: 10px;">
                    <span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: capitalize;
                        ${report.status === 'pending' ? 'background-color: #fef3c7; color: #92400e;' : 
                          report.status === 'investigating' ? 'background-color: #dbeafe; color: #1e40af;' : 
                          report.status === 'resolved' ? 'background-color: #d1fae5; color: #065f46;' : 
                          'background-color: #fee2e2; color: #991b1b;'}">
                        ${report.status}
                    </span>
                </div>
            </div>
        </div>

        <div id="images-container" style="margin-top: 20px;"></div>
    `;

            // Add images container
            const imagesContainer = tempContainer.querySelector('#images-container');
            if (report.media && report.media.length > 0) {
                const imagesTitle = document.createElement('div');
                imagesTitle.style.fontWeight = 'bold';
                imagesTitle.style.marginBottom = '15px';
                imagesTitle.style.color = '#1D3557';
                imagesTitle.textContent = 'Attached Images';
                imagesContainer.appendChild(imagesTitle);

                // Add placeholder for images
                const imagesPlaceholder = document.createElement('div');
                imagesPlaceholder.id = 'pdf-images';
                imagesPlaceholder.style.display = 'flex';
                imagesPlaceholder.style.flexWrap = 'wrap';
                imagesPlaceholder.style.gap = '10px';
                imagesContainer.appendChild(imagesPlaceholder);
            }

            document.body.appendChild(tempContainer);

            // Load and insert images before capturing
            const imagePromises = [];
            if (report.media && report.media.length > 0) {
                const imagesPlaceholder = tempContainer.querySelector('#pdf-images');

                report.media.forEach((mediaItem) => {
                    // Only process image files
                    const mediaType = (mediaItem.media_type || '').toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(mediaType);

                    if (!isImage) {
                        return; // Skip non-image files
                    }

                    const promise = new Promise((resolve, reject) => {
                         const img = new Image();
                         img.crossOrigin = 'anonymous';
                         img.onload = () => {
                             const container = document.createElement('div');
                             container.style.marginBottom = '8px';
                             container.style.width = '100%';

                             const imgElement = document.createElement('img');
                             imgElement.src = img.src;
                             imgElement.style.maxWidth = '40%';
                             imgElement.style.height = 'auto';
                             imgElement.style.border = '1px solid #ccc';
                             imgElement.style.borderRadius = '4px';

                             container.appendChild(imgElement);
                             imagesPlaceholder.appendChild(container);
                             console.log('Image loaded successfully:', mediaItem.media_url);
                             resolve();
                         };
                         img.onerror = () => {
                             console.error('Failed to load image:', mediaItem.media_url);
                             // Still create a placeholder to show there was media
                             const container = document.createElement('div');
                             container.style.marginBottom = '8px';
                             container.style.width = '100%';
                             container.style.padding = '10px';
                             container.style.backgroundColor = '#f3f4f6';
                             container.style.borderRadius = '4px';
                             container.textContent = 'Image could not be loaded';
                             imagesPlaceholder.appendChild(container);
                             resolve(); // Resolve instead of reject to continue with other images
                         };

                         // Use display_url if available (generated by backend), otherwise construct manually
                         let imageUrl = mediaItem.display_url || mediaItem.media_url;
                         if (!imageUrl.startsWith('http')) {
                             imageUrl = `/storage/${mediaItem.media_url}`;
                         }

                         console.log('Loading image from:', imageUrl);
                         img.src = imageUrl;
                     });

                    imagePromises.push(promise);
                });
            }

            // Wait for all images to load before generating PDF
            Promise.allSettled(imagePromises).then(() => {
                // Use html2canvas to capture the content
                html2canvas(tempContainer, {
                    scale: 2, // Higher quality
                    useCORS: true,
                    logging: false,
                    allowTaint: true
                }).then(canvas => {
                    // Create PDF
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    const imgWidth = 210; // A4 width in mm
                    const pageHeight = 297; // A4 height in mm
                    const imgHeight = canvas.height * imgWidth / canvas.width;
                    let heightLeft = imgHeight;
                    let position = 0;

                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;

                    // Add new pages if content is too long
                    while (heightLeft >= 0) {
                        position = heightLeft - imgHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }

                    // Save the PDF
                    const fileName = `report_${report.report_id.toString().padStart(5, '0')}.pdf`;
                    pdf.save(fileName);

                    // Clean up
                    document.body.removeChild(tempContainer);
                }).catch(error => {
                    console.error('Error generating PDF:', error);
                    alert('Error generating PDF: ' + error.message);
                    document.body.removeChild(tempContainer);
                });
            });
        }

        // Lightbox functions
        function openLightbox(index, imagesJson) {
            // Parse the JSON string back to an array
            currentImages = JSON.parse(imagesJson.replace(/&quot;/g, '"'));
            currentImageIndex = index;
            document.getElementById('lightboxImage').src = currentImages[currentImageIndex];
            document.getElementById('lightbox').classList.add('active');

            // Prevent background scrolling
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lightboxElement = document.getElementById('lightbox');
            lightboxElement.classList.remove('active');

            // Re-enable background scrolling
            document.body.style.overflow = '';

            // Clear the image
            document.getElementById('lightboxImage').src = '';
        }

        function changeImage(direction) {
            currentImageIndex += direction;

            // Handle boundaries
            if (currentImageIndex < 0) {
                currentImageIndex = currentImages.length - 1;
            } else if (currentImageIndex >= currentImages.length) {
                currentImageIndex = 0;
            }

            document.getElementById('lightboxImage').src = currentImages[currentImageIndex];
        }

        // Close modals when clicking outside
        document.getElementById('reportModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeModal();
            }
        });

        document.getElementById('lightbox').addEventListener('click', function (event) {
            if (event.target === this) {
                closeLightbox();
            }
        });

        // Keyboard navigation for lightbox
        document.addEventListener('keydown', function (event) {
            // Only handle keyboard events when lightbox is open
            if (document.getElementById('lightbox').classList.contains('active')) {
                switch (event.key) {
                    case 'Escape':
                        closeLightbox();
                        break;
                    case 'ArrowLeft':
                        changeImage(-1);
                        break;
                    case 'ArrowRight':
                        changeImage(1);
                        break;
                }
            }
        });
    </script>
@endsection