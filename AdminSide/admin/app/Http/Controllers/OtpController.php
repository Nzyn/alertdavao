<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OtpController extends Controller
{
    /**
     * Generate and send OTP to phone number
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'purpose' => 'required|string|in:login,register,verify',
            'user_id' => 'nullable|integer'
        ]);

        $phone = $request->phone;
        $purpose = $request->purpose;
        $userId = $request->user_id;

        \Log::info("📲 ADMINSIDE SEND OTP REQUEST");
        \Log::info("📱 Phone received: {$phone}");
        \Log::info("📋 Purpose: {$purpose}");
        \Log::info("👤 User ID: " . ($userId ?? 'null'));

        // Normalize phone number
        $phone = trim($phone);
        $phone = preg_replace('/\s+/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '+63' . substr($phone, 1);
        }

        \Log::info("📱 Normalized phone: {$phone}");

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = Hash::make($otp);
        $expiresAt = Carbon::now()->addMinutes(10);

        \Log::info("🔐 Generated OTP: {$otp}");
        \Log::info("⏱️  Expires at: " . $expiresAt->toDateTimeString());

        // Store OTP in database
        DB::table('otp_codes')->insert([
            'phone' => $phone,
            'otp_hash' => $otpHash,
            'purpose' => $purpose,
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'created_at' => Carbon::now()
        ]);

        \Log::info("✅ OTP stored in database");

        // Send SMS
        $sent = $this->sendSms($phone, "Your AlertDavao verification code is: $otp");

        // For development: always log OTP
        \Log::info("🔐🔐🔐 ADMINSIDE OTP CODE for $phone: $otp");
        \Log::info("📱 Purpose: $purpose");
        \Log::info("⏱️  Expires: " . $expiresAt->toDateTimeString());

        return response()->json([
            'success' => true,
            'sent' => $sent,
            'message' => 'OTP sent successfully',
            // Include OTP in response for development/testing
            'debugOtp' => (env('APP_ENV') !== 'production' || !env('TWILIO_SID')) ? $otp : null
        ]);
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
            'purpose' => 'required|string'
        ]);

        $phone = $request->phone;
        $code = $request->code;
        $purpose = $request->purpose;

        \Log::info("🔍 ADMINSIDE OTP VERIFICATION STARTED");
        \Log::info("📱 Phone received: {$phone}");
        \Log::info("🔐 Code received: {$code}");
        \Log::info("📋 Purpose: {$purpose}");

        // Normalize phone number
        $phone = trim($phone);
        $phone = preg_replace('/\s+/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '+63' . substr($phone, 1);
        }

        \Log::info("📱 Normalized phone: {$phone}");

        // Get latest OTP for this phone and purpose
        $otpRecord = DB::table('otp_codes')
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->orderBy('created_at', 'desc')
            ->first();

        \Log::info("🔎 OTP record search result: " . ($otpRecord ? "FOUND (ID: {$otpRecord->id})" : "NOT FOUND"));

        if (!$otpRecord) {
            \Log::warning("❌ No OTP found for phone: {$phone}, purpose: {$purpose}");
            return response()->json([
                'success' => false,
                'message' => 'No OTP found for this phone number'
            ], 400);
        }

        \Log::info("⏱️  OTP expires at: {$otpRecord->expires_at}");
        \Log::info("🕐 Current time: " . \Carbon\Carbon::now());

        // Check if expired
        if (Carbon::parse($otpRecord->expires_at)->isPast()) {
            \Log::warning("❌ OTP expired for phone: {$phone}");
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        \Log::info("🔐 Verifying OTP hash...");
        // Verify OTP
        if (!Hash::check($code, $otpRecord->otp_hash)) {
            \Log::warning("❌ OTP verification failed - incorrect code");
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code'
            ], 400);
        }

        \Log::info("✅ OTP hash verified successfully!");

        // Mark phone as verified
        DB::table('verified_phones')->updateOrInsert(
            ['phone' => $phone],
            [
                'verified' => 1,
                'verified_at' => Carbon::now()
            ]
        );

        \Log::info("✅ Phone marked as verified: {$phone}");

        // Delete used OTP
        DB::table('otp_codes')->where('id', $otpRecord->id)->delete();
        \Log::info("🗑️  OTP deleted (ID: {$otpRecord->id})");

        // If this is for login, return user data
        if ($purpose === 'login') {
            \Log::info("🔍 Looking up user for login...");
            
            // Normalize phone number for lookup
            $phoneVariant1 = $phone; // +63 format
            $phoneVariant2 = '0' . substr($phone, 3); // 0 format
            
            \Log::info("📱 Searching with phone variants: {$phoneVariant1} OR {$phoneVariant2}");
            
            $user = DB::table('users')
                ->where(function($query) use ($phoneVariant1, $phoneVariant2) {
                    $query->where('contact', $phoneVariant1)
                          ->orWhere('contact', $phoneVariant2);
                })
                ->first();

            \Log::info("👤 User lookup result: " . ($user ? "FOUND (ID: {$user->id}, Email: {$user->email})" : "NOT FOUND"));

            if ($user) {
                \Log::info("✅ ADMINSIDE OTP VERIFICATION COMPLETE - Returning user data");
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully',
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'firstname' => $user->firstname ?? $user->name,
                        'lastname' => $user->lastname ?? '',
                        'role' => $user->role ?? 'user',
                        'contact' => $user->contact
                    ]
                ]);
            }
            
            \Log::warning("⚠️  OTP verified but user not found for phone: {$phone}");
        }

        \Log::info("✅ ADMINSIDE OTP VERIFICATION COMPLETE");
        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    /**
     * Send SMS via Twilio or log for development
     */
    private function sendSms($phone, $message)
    {
        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_TOKEN');
        $twilioFrom = env('TWILIO_FROM');

        if ($twilioSid && $twilioToken && $twilioFrom) {
            try {
                $url = "https://api.twilio.com/2010-04-01/Accounts/$twilioSid/Messages.json";
                
                $data = [
                    'To' => $phone,
                    'From' => $twilioFrom,
                    'Body' => $message
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "$twilioSid:$twilioToken");

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                return $httpCode >= 200 && $httpCode < 300;
            } catch (\Exception $e) {
                \Log::error('Twilio SMS error: ' . $e->getMessage());
                return false;
            }
        }

        // No SMS provider configured
        \Log::warning("No SMS provider configured. OTP for $phone logged to console.");
        return true;
    }
}
