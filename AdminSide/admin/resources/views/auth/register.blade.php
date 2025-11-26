<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlertDavao - Register</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            max-width: 1000px;
            width: 100%;
            min-height: 600px;
            border: 3px solid #3b82f6;
        }

        .auth-image {
            flex: 1;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400"><defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:%234f46e5;stop-opacity:0.8" /><stop offset="100%" style="stop-color:%236366f1;stop-opacity:0.9" /></linearGradient></defs><rect width="600" height="400" fill="url(%23bg)"/><rect x="50" y="50" width="500" height="150" fill="white" opacity="0.1" rx="8"/><rect x="70" y="220" width="460" height="130" fill="white" opacity="0.1" rx="8"/><circle cx="150" cy="120" r="15" fill="white" opacity="0.3"/><circle cx="200" cy="140" r="12" fill="white" opacity="0.3"/><circle cx="250" cy="110" r="18" fill="white" opacity="0.3"/><circle cx="300" cy="135" r="14" fill="white" opacity="0.3"/><circle cx="350" cy="115" r="16" fill="white" opacity="0.3"/><circle cx="400" cy="125" r="13" fill="white" opacity="0.3"/><circle cx="450" cy="105" r="15" fill="white" opacity="0.3"/></svg>');
            background-size: cover;
            background-position: center;
            position: relative;
            display: none;
        }

        .auth-form {
            flex: 1;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            flex: 1;
        }

        .form-group.full-width {
            width: 100%;
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background-color: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        }

        .password-row {
            margin-bottom: 0;
        }

        .confirm-password {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 0.5rem;
            accent-color: #3b82f6;
        }

        .checkbox-container a {
            color: #3b82f6;
            text-decoration: none;
        }

        .checkbox-container a:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            background: #1D3557;
            color: white;
            border: none;
            padding: 0.875rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-bottom: 1rem;
        }

        .submit-btn:hover {
            background: #0f172a;
        }

        .submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .login-link {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        @media (min-width: 768px) {
            .auth-image {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .auth-container {
                margin: 1rem;
                border-radius: 12px;
            }

            .auth-form {
                padding: 2rem 1.5rem;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-group {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <img src="{{ asset('dcpo.jpg') }}" alt="DCPO" style="width:100%;height:100%;object-fit:cover;display:block;" />
        </div>
        <div class="auth-form">
            <h1 class="auth-title">Welcome Back AlertDavao!</h1>
            <p class="auth-subtitle">Sign into your account</p>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="registerForm" action="{{ route('register') }}" method="POST">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname" class="form-label">First Name</label>
                        <input 
                            type="text" 
                            id="firstname" 
                            name="firstname" 
                            class="form-input @error('firstname') error @enderror" 
                            value="{{ old('firstname') }}"
                            required
                        >
                        @error('firstname')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name</label>
                        <input 
                            type="text" 
                            id="lastname" 
                            name="lastname" 
                            class="form-input @error('lastname') error @enderror" 
                            value="{{ old('lastname') }}"
                            required
                        >
                        @error('lastname')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input @error('email') error @enderror" 
                            value="{{ old('email') }}"
                            pattern="[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                            title="Please enter a valid email address (e.g., example@gmail.com)"
                            required
                        >
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">
                            Must include @ and domain (e.g., user@gmail.com, admin@yahoo.com)
                        </small>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input 
                            type="text" 
                            id="contact" 
                            name="contact" 
                            class="form-input @error('contact') error @enderror" 
                            value="{{ old('contact') }}"
                            required
                        >
                        @error('contact')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group full-width password-row">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input @error('password') error @enderror" 
                        pattern="^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                        title="Min 8 characters with letter, number & symbol"
                        required
                    >
                    <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">
                        Min. 8 characters with at least one letter, number & symbol (@$!%*?&)
                    </small>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width confirm-password">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-input @error('password_confirmation') error @enderror" 
                        required
                    >
                    @error('password_confirmation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                @include('components.captcha')

                <div class="checkbox-container">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        By clicking this you agree to accept our <a href="#" target="_blank">Terms & Conditions</a>, that you are not from a 
                        <br>government investigating position
                    </label>
                </div>

                <button type="submit" class="submit-btn" id="registerBtn" disabled>Send OTP</button>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Login</a>
                </div>
            </form>

            <!-- OTP Modal -->
            <div id="otpModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
                <div style="background: white; padding: 2rem; border-radius: 12px; width: 340px; max-width: 100%;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600; text-align: center;">Verify Your Phone Number</h3>
                    <p style="margin: 0 0 0.25rem 0; font-size: 0.75rem; color: #666; text-align: center;">Enter the 6-digit code sent to</p>
                    <p id="otpPhoneDisplay" style="margin: 0 0 1rem 0; font-size: 0.875rem; color: #1D3557; font-weight: 600; text-align: center;"></p>
                    <input 
                        type="text" 
                        id="otpInput" 
                        placeholder="000000"
                        maxlength="6"
                        inputmode="numeric"
                        style="width: 100%; padding: 1rem; border: 1.5px solid #d1d5db; border-radius: 6px; text-align: center; font-size: 24px; letter-spacing: 8px; font-weight: 600; margin-bottom: 1rem; background-color: white; box-sizing: border-box;"
                    >
                    <p id="otpVerifyingText" style="text-align: center; color: #1D3557; font-weight: 600; display: none; margin: 1rem 0;">Verifying...</p>
                    <span class="error-message" id="otpErrorMsg" style="display:none; margin-bottom: 1rem;"></span>
                    
                    <!-- Resend OTP Section -->
                    <div id="resendSection" style="text-align: center; margin-top: 0.5rem;">
                        <span id="resendCountdown" style="color: #666; font-size: 0.75rem;"></span>
                        <button type="button" id="resendBtn" style="display: none; background: none; border: none; color: #1D3557; font-weight: 600; cursor: pointer; font-size: 0.875rem;">Resend OTP</button>
                    </div>
                    
                    <!-- Cancel Button -->
                    <button type="button" id="cancelOtpBtn" style="width: 100%; margin-top: 1rem; padding: 0.75rem; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; cursor: pointer; font-size: 0.875rem;">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Supabase SDK -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        // Initialize Supabase client - these values should be set in your environment config
        const SUPABASE_URL = '{{ env("SUPABASE_URL", "") }}';
        const SUPABASE_ANON_KEY = '{{ env("SUPABASE_ANON_KEY", "") }}';
        
        let supabaseClient = null;
        if (SUPABASE_URL && SUPABASE_ANON_KEY) {
            supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
                auth: {
                    autoRefreshToken: true,
                    persistSession: false,
                    detectSessionInUrl: false,
                }
            });
            console.log('✅ Supabase client initialized for OTP');
        } else {
            console.warn('⚠️ Supabase credentials not configured. OTP will use fallback method.');
        }

        const registerBtn = document.getElementById('registerBtn');
        const registerForm = document.getElementById('registerForm');
        const termsCheckbox = document.getElementById('terms');
        const captchaInput = document.getElementById('captchaInput');
        const otpModal = document.getElementById('otpModal');
        const otpInput = document.getElementById('otpInput');
        const otpVerifyingText = document.getElementById('otpVerifyingText');
        const otpErrorMsg = document.getElementById('otpErrorMsg');
        const otpPhoneDisplay = document.getElementById('otpPhoneDisplay');
        const resendCountdown = document.getElementById('resendCountdown');
        const resendBtn = document.getElementById('resendBtn');
        const cancelOtpBtn = document.getElementById('cancelOtpBtn');
        
        let captchaValid = false;
        let pendingRegistrationData = null;
        let userPhone = '';
        let normalizedPhone = '';
        let countdownTimer = null;
        let canResend = false;

        console.log('🔐 Register page loaded');

        // Normalize phone number to international format
        function normalizePhoneNumber(phone) {
            let normalized = phone.trim().replace(/\s+/g, '');
            if (normalized.startsWith('0')) {
                normalized = '+63' + normalized.slice(1);
            }
            if (!normalized.startsWith('+')) {
                normalized = '+63' + normalized;
            }
            return normalized;
        }

        // Start resend countdown timer
        function startResendCountdown() {
            canResend = false;
            let seconds = 60;
            resendBtn.style.display = 'none';
            resendCountdown.style.display = 'inline';
            resendCountdown.textContent = `Resend OTP in ${seconds}s`;
            
            if (countdownTimer) clearInterval(countdownTimer);
            
            countdownTimer = setInterval(() => {
                seconds--;
                if (seconds <= 0) {
                    clearInterval(countdownTimer);
                    canResend = true;
                    resendCountdown.style.display = 'none';
                    resendBtn.style.display = 'inline';
                } else {
                    resendCountdown.textContent = `Resend OTP in ${seconds}s`;
                }
            }, 1000);
        }

        // Send OTP via Supabase
        async function sendOtpToPhone(phone) {
            normalizedPhone = normalizePhoneNumber(phone);
            console.log('📱 Sending OTP to:', normalizedPhone);
            
            if (supabaseClient) {
                // Use Supabase OTP
                try {
                    const { data, error } = await supabaseClient.auth.signInWithOtp({
                        phone: normalizedPhone,
                        options: {
                            channel: 'sms',
                        },
                    });

                    if (error) {
                        console.error('❌ Supabase OTP send error:', error);
                        return { success: false, message: error.message || 'Failed to send OTP' };
                    }

                    console.log('✅ Supabase OTP sent successfully');
                    return { success: true, message: 'OTP sent successfully' };
                } catch (err) {
                    console.error('❌ Exception sending Supabase OTP:', err);
                    return { success: false, message: err.message || 'Failed to send OTP' };
                }
            } else {
                // Fallback to backend OTP
                try {
                    const response = await fetch('/api/otp/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            phone: normalizedPhone,
                            purpose: 'register'
                        })
                    });

                    const data = await response.json();
                    
                    if (data.debugOtp) {
                        console.log('🔐🔐🔐 ADMINSIDE REGISTER OTP CODE:', data.debugOtp);
                    }
                    
                    return data;
                } catch (err) {
                    console.error('❌ Error sending fallback OTP:', err);
                    return { success: false, message: 'Failed to send OTP' };
                }
            }
        }

        // Verify OTP via Supabase
        async function verifyOtpCode(phone, code) {
            const normalPhone = normalizePhoneNumber(phone);
            console.log('🔐 Verifying OTP for:', normalPhone, 'code:', code);
            
            if (supabaseClient) {
                // Use Supabase verification
                try {
                    const { data, error } = await supabaseClient.auth.verifyOtp({
                        phone: normalPhone,
                        token: code,
                        type: 'sms',
                    });

                    if (error) {
                        console.error('❌ Supabase OTP verify error:', error);
                        return { success: false, message: error.message || 'Invalid OTP code' };
                    }

                    console.log('✅ Supabase OTP verified successfully');
                    
                    // Sign out immediately since we're only using this for verification
                    await supabaseClient.auth.signOut();
                    
                    return { success: true, message: 'Phone number verified successfully' };
                } catch (err) {
                    console.error('❌ Exception verifying Supabase OTP:', err);
                    return { success: false, message: err.message || 'Failed to verify OTP' };
                }
            } else {
                // Fallback to backend verification
                try {
                    const response = await fetch('/api/otp/verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            phone: normalPhone,
                            code: code,
                            purpose: 'register'
                        })
                    });

                    return await response.json();
                } catch (err) {
                    console.error('❌ Error verifying fallback OTP:', err);
                    return { success: false, message: 'Failed to verify OTP' };
                }
            }
        }

        // Update button state based on terms and captcha
        function updateRegisterButton() {
            const termsChecked = termsCheckbox.checked;
            console.log('🔘 Update button - Terms:', termsChecked, 'Captcha:', captchaValid);
            registerBtn.disabled = !(termsChecked && captchaValid);
        }

        // Override the global validateCaptcha function for register page
        const originalValidateCaptcha = window.validateCaptcha;
        window.validateCaptcha = function() {
            const result = originalValidateCaptcha();
            captchaValid = result;
            console.log('🔐 Register captcha validation:', captchaValid);
            updateRegisterButton();
            return result;
        };

        // Listen to terms checkbox changes
        termsCheckbox.addEventListener('change', function() {
            console.log('🔘 Terms checkbox changed:', this.checked);
            updateRegisterButton();
        });

        // Handle resend OTP button
        resendBtn.addEventListener('click', async function() {
            if (!canResend) return;
            
            resendBtn.disabled = true;
            resendBtn.textContent = 'Sending...';
            
            const result = await sendOtpToPhone(userPhone);
            
            if (result.success) {
                alert('A new verification code has been sent to your phone.');
                startResendCountdown();
            } else {
                alert(result.message || 'Failed to resend OTP');
            }
            
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend OTP';
        });

        // Handle cancel OTP button
        cancelOtpBtn.addEventListener('click', function() {
            otpModal.style.display = 'none';
            otpInput.value = '';
            otpErrorMsg.style.display = 'none';
            otpVerifyingText.style.display = 'none';
            registerBtn.disabled = false;
            registerBtn.innerHTML = 'Send OTP';
            if (countdownTimer) clearInterval(countdownTimer);
        });

        // Handle register form submission - send OTP first
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const captchaInputValue = captchaInput.value.toUpperCase();
            const captchaWord = document.getElementById('captchaWord').value;
            
            // Validate captcha
            if (captchaInputValue !== captchaWord) {
                document.getElementById('captchaError').style.display = 'block';
                captchaInput.focus();
                alert('Invalid Security Code\n\nPlease enter the correct code shown in the image.');
                return false;
            }

            const emailInput = document.getElementById('email');
            const emailValue = emailInput.value.trim();
            const contactInput = document.getElementById('contact');
            const contactValue = contactInput.value.trim();

            // Validate email
            if (!emailValue.includes('@')) {
                alert('Invalid Email Format\n\nEmail must contain @ symbol.\nFor example: user@gmail.com');
                emailInput.focus();
                return false;
            }

            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(emailValue)) {
                alert('Invalid Email Address\n\nPlease enter a valid email address.\nExamples:\n• user@gmail.com\n• admin@yahoo.com\n• police@outlook.com');
                emailInput.focus();
                return false;
            }

            // Validate password
            const passwordInput = document.getElementById('password');
            const passwordValue = passwordInput.value;
            const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!passwordRegex.test(passwordValue)) {
                alert('Weak Password\n\nPassword must contain:\n• Minimum 8 characters\n• At least one letter\n• At least one number\n• At least one symbol (@$!%*?&)');
                passwordInput.focus();
                return false;
            }

            // Store registration data for after OTP verification
            pendingRegistrationData = {
                firstname: document.getElementById('firstname').value,
                lastname: document.getElementById('lastname').value,
                email: emailValue,
                contact: contactValue,
                password: passwordValue,
                password_confirmation: document.getElementById('password_confirmation').value,
                terms: termsCheckbox.checked ? 'on' : '',
                captcha_input: captchaInputValue,
                captcha_word: captchaWord
            };

            userPhone = contactValue;

            // Send OTP
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<span class="spinner" style="margin-right:8px;width:18px;height:18px;display:inline-block;border:2px solid #fff;border-top:2px solid #1D3557;border-radius:50%;animation:spin 1s linear infinite;"></span>Sending OTP...';

            const result = await sendOtpToPhone(userPhone);

            if (!result.success) {
                alert(result.message || 'Failed to send OTP');
                registerBtn.disabled = false;
                registerBtn.innerHTML = 'Send OTP';
                return;
            }

            // Show OTP modal
            otpPhoneDisplay.textContent = normalizedPhone;
            otpModal.style.display = 'flex';
            otpInput.focus();
            startResendCountdown();
            
            alert('Your verification code has been sent to ' + normalizedPhone + '. It is valid for 5 minutes.');
        });

        // Auto-submit OTP when 6 digits are entered
        otpInput.addEventListener('input', async function() {
            const code = this.value.trim();
            if (code.length === 6 && /^\d{6}$/.test(code)) {
                otpVerifyingText.style.display = 'block';
                otpErrorMsg.style.display = 'none';

                const result = await verifyOtpCode(userPhone, code);

                if (!result.success) {
                    otpErrorMsg.style.display = 'block';
                    otpErrorMsg.textContent = result.message || 'Invalid OTP';
                    otpInput.value = '';
                    otpVerifyingText.style.display = 'none';
                    return;
                }

                // OTP verified, submit registration
                await submitRegistration();
            }
        });

        // Submit registration after OTP verification
        async function submitRegistration() {
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                formData.append('firstname', pendingRegistrationData.firstname);
                formData.append('lastname', pendingRegistrationData.lastname);
                formData.append('email', pendingRegistrationData.email);
                formData.append('contact', pendingRegistrationData.contact);
                formData.append('password', pendingRegistrationData.password);
                formData.append('password_confirmation', pendingRegistrationData.password_confirmation);
                formData.append('terms', pendingRegistrationData.terms);
                formData.append('captcha_input', pendingRegistrationData.captcha_input);
                formData.append('captcha_word', pendingRegistrationData.captcha_word);

                const response = await fetch('{{ route("register") }}', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    const text = await response.text();
                    if (text.includes('success') || response.status === 302 || response.status === 200) {
                        alert('Registration Successful!\n\nYour account has been created.\n\nPlease login with your credentials.');
                        window.location.href = '{{ route("login") }}';
                    } else {
                        otpErrorMsg.style.display = 'block';
                        otpErrorMsg.textContent = 'Registration failed. Please try again.';
                        otpInput.value = '';
                        otpVerifyingText.style.display = 'none';
                    }
                } else {
                    otpErrorMsg.style.display = 'block';
                    otpErrorMsg.textContent = 'Registration failed. Please try again.';
                    otpInput.value = '';
                    otpVerifyingText.style.display = 'none';
                }
            } catch (err) {
                console.error('Error submitting registration:', err);
                otpErrorMsg.style.display = 'block';
                otpErrorMsg.textContent = 'An error occurred. Please try again.';
                otpInput.value = '';
                otpVerifyingText.style.display = 'none';
            }
        }

        // Spinner animation
        const style = document.createElement('style');
        style.innerHTML = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    </script>
</body>
</html>