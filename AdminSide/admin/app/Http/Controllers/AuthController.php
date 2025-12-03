<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Notifications\EmailVerification;
use App\Notifications\PasswordResetNotification;

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

        // Generate verification token
        $verificationToken = Str::random(64);
        $tokenExpiresAt = Carbon::now()->addHours(24);

        // Create user with verification token (email not verified yet)
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'contact' => $request->contact,
            'password' => Hash::make($request->password),
            'verification_token' => $verificationToken,
            'token_expires_at' => $tokenExpiresAt,
            'email_verified_at' => null, // Not verified yet
        ]);

        // Generate verification URL
        $verificationUrl = route('email.verify', ['token' => $verificationToken]);

        // Send verification email
        try {
            $user->notify(new EmailVerification($verificationUrl, $user->firstname));
            
            return redirect()->route('login')->with('success', 
                'Registration successful! Please check your email (' . $user->email . ') for a verification link to activate your account. The link will expire in 24 hours.');
        } catch (\Exception $e) {
            // Delete user if email fails to send
            $user->delete();
            \Log::error('Email verification failed: ' . $e->getMessage());
            
            return back()->withErrors(['email' => 'Failed to send verification email. Please check your email address and try again.'])->withInput();
        }
    }

    // Show login form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle login (Direct login - no OTP required)
    // OTP is only required during signup
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->except('password'));
        }

        // Check if account is locked
        if ($user->lockout_until && Carbon::now()->lessThan($user->lockout_until)) {
            $remainingMinutes = Carbon::now()->diffInMinutes($user->lockout_until) + 1;
            return back()->withErrors([
                'email' => "Account temporarily locked due to too many failed login attempts. Please try again in {$remainingMinutes} minute(s).",
            ])->withInput($request->except('password'));
        }

        // Reset lockout if expired
        if ($user->lockout_until && Carbon::now()->greaterThanOrEqualTo($user->lockout_until)) {
            $user->failed_login_attempts = 0;
            $user->lockout_until = null;
            $user->save();
        }

        // Check if email is verified
        if (is_null($user->email_verified_at)) {
            return back()->withErrors([
                'email' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
            ])->withInput($request->except('password'));
        }

        // Only use email and password for authentication
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Reset failed login attempts on successful login
            $user->failed_login_attempts = 0;
            $user->lockout_until = null;
            $user->last_failed_login = null;
            $user->save();
            
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
            } else if (in_array($user->role, ['police', 'admin'])) {
                // Police and Admin users can access AdminSide dashboard
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

        // Failed login - increment attempt counter
        $user->failed_login_attempts += 1;
        $user->last_failed_login = Carbon::now();

        // Apply lockout based on attempt count
        if ($user->failed_login_attempts >= 15) {
            // 15+ attempts: 15 minute lockout + email alert
            $user->lockout_until = Carbon::now()->addMinutes(15);
            $user->save();
            
            // Send email notification
            try {
                $user->notify(new \App\Notifications\AccountLockoutNotification($user->failed_login_attempts));
            } catch (\Exception $e) {
                \Log::error('Failed to send lockout email: ' . $e->getMessage());
            }
            
            return back()->withErrors([
                'email' => 'Account locked for 15 minutes due to 15 failed login attempts. A security alert has been sent to your email.',
            ])->withInput($request->except('password'));
        } else if ($user->failed_login_attempts >= 10) {
            // 10-14 attempts: 10 minute lockout
            $user->lockout_until = Carbon::now()->addMinutes(10);
            $user->save();
            
            return back()->withErrors([
                'email' => 'Account locked for 10 minutes due to multiple failed login attempts.',
            ])->withInput($request->except('password'));
        } else if ($user->failed_login_attempts >= 5) {
            // 5-9 attempts: 5 minute lockout
            $user->lockout_until = Carbon::now()->addMinutes(5);
            $user->save();
            
            return back()->withErrors([
                'email' => 'Account locked for 5 minutes due to multiple failed login attempts.',
            ])->withInput($request->except('password'));
        } else {
            // Less than 5 attempts: just save the counter
            $user->save();
            
            $remainingAttempts = 5 - $user->failed_login_attempts;
            return back()->withErrors([
                'email' => "The provided credentials do not match our records. {$remainingAttempts} attempt(s) remaining before account lockout.",
            ])->withInput($request->except('password'));
        }
    }



    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out successfully!');
    }

    // Show forgot password form
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Handle forgot password request
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'We could not find a user with that email address.'
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if email is verified
        if (is_null($user->email_verified_at)) {
            return back()->withErrors([
                'email' => 'Your email address is not verified. Please verify your email before resetting your password.',
            ])->withInput();
        }

        // Generate reset token
        $resetToken = Str::random(64);
        $tokenExpiresAt = Carbon::now()->addHour();

        // Update user with reset token
        $user->update([
            'reset_token' => $resetToken,
            'reset_token_expires_at' => $tokenExpiresAt,
        ]);

        // Generate reset URL
        $resetUrl = route('password.reset.form', ['token' => $resetToken]);

        // Send reset email
        try {
            $user->notify(new PasswordResetNotification($resetUrl, $user->firstname));
            
            return back()->with('success', 
                'Password reset link has been sent to your email address. Please check your inbox.');
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'email' => 'Failed to send password reset email. Please try again later.'
            ])->withInput();
        }
    }

    // Show reset password form
    public function showResetPassword($token)
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expires_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'token' => 'This password reset link is invalid or has expired.'
            ]);
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $user->email]);
    }

    // Handle password reset
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'password_confirmation' => 'required|same:password',
        ], [
            'password.regex' => 'Password must contain at least one letter, one number, and one symbol (@$!%*?&)',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('reset_token', $request->token)
            ->where('email', $request->email)
            ->where('reset_token_expires_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return back()->withErrors([
                'token' => 'This password reset link is invalid or has expired.'
            ])->withInput();
        }

        // Update password and clear reset token
        $user->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return redirect()->route('login')->with('success', 
            'Your password has been reset successfully! Please login with your new password.');
    }

    // Verify email
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)
            ->where('token_expires_at', '>', Carbon::now())
            ->whereNull('email_verified_at')
            ->first();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'token' => 'This verification link is invalid or has expired.'
            ]);
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => Carbon::now(),
            'verification_token' => null,
            'token_expires_at' => null,
        ]);

        return redirect()->route('login')->with('success', 
            'Email verified successfully! You can now login to your account.');
    }

    // Resend verification email
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return back()->withErrors([
                'email' => 'This email address is already verified.'
            ])->withInput();
        }

        // Generate new verification token
        $verificationToken = Str::random(64);
        $tokenExpiresAt = Carbon::now()->addHours(24);

        $user->update([
            'verification_token' => $verificationToken,
            'token_expires_at' => $tokenExpiresAt,
        ]);

        // Generate verification URL
        $verificationUrl = route('email.verify', ['token' => $verificationToken]);

        // Send verification email
        try {
            $user->notify(new EmailVerification($verificationUrl, $user->firstname));
            
            return back()->with('success', 
                'Verification email has been sent! Please check your inbox.');
        } catch (\Exception $e) {
            \Log::error('Email verification resend failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'email' => 'Failed to send verification email. Please try again later.'
            ])->withInput();
        }
    }
}
