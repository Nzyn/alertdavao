<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Show registration form
    public function showRegister()
    {
        return view('auth.register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'max:100',
                'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'contact' => 'required|string|max:15',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'password_confirmation' => 'required|same:password',
            'captcha_input' => 'required|string|size:6',
            'captcha_word' => 'required|string|size:6'
        ], [
            'email.regex' => 'Please enter a valid email address with @ and domain (e.g., user@gmail.com, admin@yahoo.com)',
            'email.email' => 'Email must be a valid email format',
            'password.regex' => 'Password must contain at least one letter, one number, and one symbol (@$!%*?&)',
            'password.min' => 'Password must be at least 8 characters long',
            'captcha_input.required' => 'Please enter the security code',
            'captcha_input.size' => 'Security code must be 6 characters'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify captcha
        if (strtoupper($request->captcha_input) !== strtoupper($request->captcha_word)) {
            return back()->withErrors(['captcha_input' => 'Invalid security code. Please try again.'])->withInput();
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'contact' => $request->contact,
            'password' => Hash::make($request->password)
        ]);

        // Redirect to login page instead of auto-login for extra security
        return redirect()->route('login')->with('success', 'Registration successful! Please login with your credentials.');
    }

    // Show login form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        // Check if this is an AJAX request for OTP initiation
        if ($request->isJson() && $request->input('send_otp')) {
            return $this->initiateOtpLogin($request);
        }

        // Regular form submission for final login after OTP verification
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Only use email and password for authentication
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Get the authenticated user
            $user = Auth::user();
            
            // Check if user has appropriate role for AdminSide access
            if ($user->role === 'user') {
                // Regular users cannot access AdminSide
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'role' => 'Your account is still on Level: USER. Please contact admin support to change your level into Police or Admin to access this panel.',
                ])->withInput($request->except('password'));
            } else if (in_array($user->role, ['police', 'admin', 'barangay'])) {
                // Police, Admin, and Barangay users can access AdminSide dashboard
                \Log::info('User logged in to AdminSide', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email
                ]);
                return redirect()->intended(route('dashboard'))->with('success', 'Login successful!');
            }
            
            // Unknown role - deny access
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'role' => 'Your account role is not authorized to access this system. Please contact admin support.',
            ])->withInput($request->except('password'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }

    // Initiate OTP login flow
    private function initiateOtpLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        \Log::info("🔐 ADMINSIDE LOGIN ATTEMPT");
        \Log::info("📧 Email: {$email}");

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            \Log::warning("❌ Invalid credentials for: {$email}");
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        \Log::info("✅ Credentials valid for user ID: {$user->id}");

        // Check if user has appropriate role for AdminSide access
        if (!in_array($user->role, ['police', 'admin', 'barangay'])) {
            \Log::warning("❌ Unauthorized role: {$user->role} for user: {$email}");
            return response()->json([
                'success' => false,
                'message' => 'Your account is not authorized for AdminSide access.'
            ], 403);
        }

        \Log::info("✅ User has authorized role: {$user->role}");

        // Get user's phone number
        $phone = $user->contact ?? $user->phone ?? $user->mobile;

        if (!$phone) {
            \Log::error("❌ No phone number for user: {$email}");
            return response()->json([
                'success' => false,
                'message' => 'No phone number found for your account. Please contact admin support.'
            ], 400);
        }

        \Log::info("📱 Phone number found: {$phone}");

        // Normalize phone number to +63 format
        $normalizedPhone = trim($phone);
        $normalizedPhone = preg_replace('/\s+/', '', $normalizedPhone);
        if (substr($normalizedPhone, 0, 1) === '0') {
            $normalizedPhone = '+63' . substr($normalizedPhone, 1);
        }

        \Log::info("📱 Normalized phone: {$normalizedPhone}");

        // Generate and send OTP
        try {
            // Generate OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpHash = Hash::make($otp);
            $expiresAt = \Carbon\Carbon::now()->addMinutes(10);

            \Log::info("🔐 Generated OTP: {$otp}");
            \Log::info("⏱️  Expires at: " . $expiresAt->toDateTimeString());

            // Store OTP
            \DB::table('otp_codes')->insert([
                'phone' => $normalizedPhone,
                'otp_hash' => $otpHash,
                'purpose' => 'login',
                'user_id' => $user->id,
                'expires_at' => $expiresAt,
                'created_at' => \Carbon\Carbon::now()
            ]);

            \Log::info("✅ OTP stored in database for phone: {$normalizedPhone}");

            // Send SMS via Twilio or log for development
            $sent = $this->sendSms($normalizedPhone, "Your AlertDavao AdminSide verification code is: $otp");

            // Log OTP for development - ALWAYS log regardless of Twilio config
            \Log::info("🔐🔐🔐 ADMINSIDE LOGIN OTP for {$normalizedPhone}: {$otp}");
            \Log::info("📱 Purpose: login");
            \Log::info("👤 User: {$user->email} (ID: {$user->id}, Role: {$user->role})");
            \Log::info("⏱️  Expires: " . $expiresAt->toDateTimeString());

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'phone' => $normalizedPhone,
                'debugOtp' => (env('APP_ENV') !== 'production' || !env('TWILIO_SID')) ? $otp : null
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ OTP generation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
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
        \Log::warning("📲 No SMS provider configured. OTP for $phone logged to Laravel log.");
        return true;
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out successfully!');
    }
}
