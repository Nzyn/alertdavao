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

    // Handle login (Direct login - no OTP required)
    // OTP is only required during signup
    public function login(Request $request)
    {
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

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
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
