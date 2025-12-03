<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
// use App\Http\Controllers\BarangayController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HotspotDataController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Email Verification Routes
Route::get('/email/verify/{token}', [AuthController::class, 'verifyEmail'])->name('email.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerification'])->name('email.resend');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// OTP Routes
Route::post('/api/otp/send', [OtpController::class, 'sendOtp'])->name('otp.send');
Route::post('/api/otp/verify', [OtpController::class, 'verifyOtp'])->name('otp.verify');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/crime-data', [DashboardController::class, 'getCrimeData'])->name('crime.data');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::put('/reports/{id}/status', [ReportController::class, 'updateStatus'])->name('reports.updateStatus');
    Route::put('/reports/{id}/validity', [ReportController::class, 'updateValidity'])->name('reports.updateValidity');
    Route::get('/reports/{id}/details', [ReportController::class, 'getDetails'])->name('reports.details');
    
    // Report reassignment routes
    Route::post('/reports/{id}/assign-station', [ReportController::class, 'assignToStation'])->name('reports.assignStation')->middleware('role:admin');
    Route::post('/reports/{id}/request-reassignment', [ReportController::class, 'requestReassignment'])->name('reports.requestReassignment')->middleware('role:police');
    Route::get('/api/reassignment-requests', [ReportController::class, 'getReassignmentRequests'])->name('api.reassignmentRequests')->middleware('role:admin');
    Route::post('/reassignment-requests/{id}/review', [ReportController::class, 'reviewReassignmentRequest'])->name('reports.reviewReassignment')->middleware('role:admin');

    // Barangay management routes (commented out - controller missing)
    // Route::get('/barangays', [BarangayController::class, 'index'])->name('barangays.index');
    // Route::get('/barangays/{barangayId}', [BarangayController::class, 'show'])->name('barangays.show');

    Route::get('/users', function () {
        $users = \App\Models\User::with('roles')->orderBy('created_at', 'desc')->get();
        return view('users', compact('users'));
    })->name('users');
    
    Route::get('/flagged-users', function () {
        $users = \App\Models\User::with('roles')
            ->where(function($query) {
                $query->where('total_flags', '>', 0)
                      ->orWhere('restriction_level', '!=', 'none');
            })
            ->orderBy('total_flags', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('flagged-users', compact('users'));
    })->name('flagged-users');
    
    // Personnel management routes
    Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel');
    
    // User management routes (flag/unflag requires admin or police role)
    Route::middleware('role:admin,police')->group(function () {
        Route::post('/users/{id}/flag', [UserController::class, 'flagUser'])->name('users.flag');
        Route::post('/users/{id}/unflag', [UserController::class, 'unflagUser'])->name('users.unflag');
    });
    Route::post('/users/{id}/promote', [UserController::class, 'promoteToOfficer'])->name('users.promote');
    Route::post('/users/{id}/change-role', [UserController::class, 'changeRole'])->name('users.changeRole');
    Route::post('/users/{id}/assign-station', [UserController::class, 'assignStation'])->name('users.assignStation');
    Route::get('/api/users/{id}/flags', [UserController::class, 'getFlagHistory'])->name('users.flags');
    Route::get('/api/users/{id}/flag-status', [UserController::class, 'getFlagStatus'])->name('users.flagStatus');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages');
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'getConversation'])->name('messages.conversation');
    Route::get('/messages/list', [MessageController::class, 'getConversationsList'])->name('messages.list');
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');
    Route::get('/messages/unread-count', [MessageController::class, 'getUnreadCount'])->name('messages.unread');
    Route::post('/messages/typing', [MessageController::class, 'updateTypingStatus'])->name('messages.typing');
    Route::get('/messages/typing-status/{userId}', [MessageController::class, 'checkTypingStatus'])->name('messages.typingStatus');

    Route::get('/verification', function () {
        return view('verification');
    })->name('verification');

    // Reassignment requests page (admin only)
    Route::get('/reassignment-requests', function () {
        return view('reassignment-requests');
    })->name('reassignment-requests')->middleware('role:admin');

    // API routes for verification management (now properly protected by auth middleware)
    Route::get('/api/verifications/all', [VerificationController::class, 'getAllVerifications']);
    Route::get('/api/verifications/pending', [VerificationController::class, 'getPendingVerifications']);
    Route::post('/api/verification/approve', [VerificationController::class, 'approveVerification']);
    Route::post('/api/verification/reject', [VerificationController::class, 'rejectVerification']);
    
    // Police station routes
    Route::get('/api/police-stations', [PersonnelController::class, 'getPoliceStations']);

    // Statistics routes
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
    Route::get('/api/statistics/forecast', [StatisticsController::class, 'getForecast'])->name('statistics.forecast');
    Route::get('/api/statistics/crime-stats', [StatisticsController::class, 'getCrimeStats'])->name('statistics.crime');
    Route::get('/api/statistics/export', [StatisticsController::class, 'exportCrimeData'])->name('statistics.export');
    Route::get('/api/statistics/barangay-stats', [StatisticsController::class, 'getBarangayCrimeStats'])->name('statistics.barangay');

    Route::get('/view-map', [MapController::class, 'index'])->name('view-map');
    Route::get('/api/reports', [MapController::class, 'getReports'])->name('api.reports');
    Route::get('/api/csv-crime-data', [MapController::class, 'getCsvCrimeData'])->name('api.csv-crimes');
    
    // Crime hotspot mapping route
    Route::get('/hotspot-map', [MapController::class, 'hotspotIndex'])->name('hotspot-map');
    Route::get('/api/hotspot-data', [HotspotDataController::class, 'getHotspotData'])->name('api.hotspot-data');

    // Notification routes
    Route::get('/api/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::get('/api/notifications', [NotificationController::class, 'getAll'])->name('notifications.all');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
    Route::post('/api/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::delete('/api/notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
});