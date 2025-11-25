<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlertDavao - Login</title>
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
            max-width: 900px;
            width: 100%;
            min-height: 500px;
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
            max-width: 400px;
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

        .form-group {
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
            padding: 0.875rem 1rem;
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

        .password-group {
            margin-bottom: 0;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 0.5rem;
            accent-color: #3b82f6;
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
            margin-bottom: 1.5rem;
        }

        .submit-btn:hover {
            background: #0f172a;
        }

        .submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .google-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
            padding: 0.875rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }

        .google-btn:hover {
            background: #f9fafb;
            border-color: #bbb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .register-link {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .register-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
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
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <img src="<?php echo e(asset('dcpo.jpg')); ?>" alt="DCPO" style="width:100%;height:100%;object-fit:cover;display:block;" />
        </div>
        <div class="auth-form">
            <h1 class="auth-title">Welcome Back to AlertDavao!</h1>
            <p class="auth-subtitle">Sign into your account</p>

            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-error">
                    <?php if($errors->has('role')): ?>
                        <strong style="display: block; margin-bottom: 0.5rem;">⚠️ Access Denied</strong>
                        <p style="margin: 0;"><?php echo e($errors->first('role')); ?></p>
                    <?php else: ?>
                        <ul style="margin: 0; padding-left: 1rem;">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form id="loginForm">
               <?php echo csrf_field(); ?>
               
               <div id="emailPasswordForm">
                   <div class="form-group">
                       <label for="email" class="form-label">Your Email</label>
                       <input 
                           type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           value="<?php echo e(old('email')); ?>"
                           required
                           placeholder="Enter your email address"
                       >
                       <span class="error-message" id="emailError" style="display:none;"></span>
                   </div>

                   <div class="form-group password-group">
                       <label for="password" class="form-label">Password</label>
                       <input 
                           type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           required
                           placeholder="Enter your password"
                       >
                       <span class="error-message" id="passwordError" style="display:none;"></span>
                   </div>

                   <div class="checkbox-container">
                       <input type="checkbox" id="remember" name="remember">
                       <label for="remember">Remember Me</label>
                   </div>

                   <?php echo $__env->make('components.captcha', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                   <button type="button" class="submit-btn" id="proceedBtn">Login</button>
               </div>

               <div id="otpForm" style="display:none;">
                   <div class="form-group">
                       <label class="form-label">Enter OTP sent to your phone</label>
                       <p style="font-size: 0.75rem; color: #666; margin: 0.5rem 0 1rem 0;">Auto-proceeds when complete</p>
                       <input 
                           type="text" 
                           id="otpCode" 
                           name="otp_code" 
                           class="form-input" 
                           placeholder="000000"
                           maxlength="6"
                           inputmode="numeric"
                           style="text-align: center; font-size: 24px; letter-spacing: 8px; font-weight: 600; tracking-code"
                       >
                       <span class="error-message" id="otpError" style="display:none;"></span>
                   </div>
                   <p id="verifyingText" style="text-align: center; color: #1D3557; font-weight: 600; display: none; margin-top: 1rem;">Verifying...</p>
                   <button type="button" class="submit-btn" id="backBtn" style="background: #6c757d;">Back</button>
               </div>

                <div style="display: flex; align-items: center; margin: 20px 0;">
                    <div style="flex: 1; height: 1px; background: #ddd;"></div>
                    <span style="padding: 0 15px; color: #666; font-size: 14px;">OR</span>
                    <div style="flex: 1; height: 1px; background: #ddd;"></div>
                </div>

                <button type="button" class="google-btn" onclick="alert('Google Sign-In for AdminSide is currently available only for UserSide app. Admin users should use email/password login.')">
                    <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Continue with Google
                </button>

                <div class="register-link">
                    Don't have an account? <a href="<?php echo e(route('register')); ?>">Register</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const proceedBtn = document.getElementById('proceedBtn');
        const backBtn = document.getElementById('backBtn');
        const loginForm = document.getElementById('loginForm');
        const emailPasswordForm = document.getElementById('emailPasswordForm');
        const otpForm = document.getElementById('otpForm');
        const otpCode = document.getElementById('otpCode');
        const verifyingText = document.getElementById('verifyingText');
        let currentUserEmail = '';
        let currentUserPhone = '';

        // Handle Proceed to OTP button
        proceedBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const captchaInput = document.getElementById('captchaInput').value.toUpperCase();
            const captchaWord = document.getElementById('captchaWord').value;

            // Validate captcha
            if (captchaInput !== captchaWord) {
                document.getElementById('captchaError').style.display = 'block';
                document.getElementById('captchaInput').focus();
                alert('Invalid Security Code\n\nPlease enter the correct code shown in the image.');
                return false;
            }

            // Validate email and password
            if (!email || !password) {
                alert('Please enter email and password');
                return false;
            }

            proceedBtn.disabled = true;
            proceedBtn.innerHTML = '<span class="spinner" style="margin-right:8px;width:18px;height:18px;display:inline-block;border:2px solid #fff;border-top:2px solid #1D3557;border-radius:50%;animation:spin 1s linear infinite;"></span>Verifying...';

            try {
                // Call backend to verify email/password and trigger OTP send
                const response = await fetch('<?php echo e(route("login")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        send_otp: true
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Invalid credentials. Please try again.');
                    proceedBtn.disabled = false;
                    proceedBtn.innerHTML = 'Proceed to OTP';
                    return;
                }

                // Store email and phone for OTP verification
                currentUserEmail = email;
                currentUserPhone = data.phone;

                // Show OTP form
                emailPasswordForm.style.display = 'none';
                otpForm.style.display = 'block';
                otpCode.focus();

                // Log OTP to console instead of alert popup
                if (data.debugOtp) {
                    console.log('🔐🔐🔐 ADMINSIDE OTP CODE:', data.debugOtp);
                    console.log('📱 Check the backend terminal/logs for the OTP code');
                }
            } catch (err) {
                console.error('Error:', err);
                alert('An error occurred. Please try again.');
            } finally {
                proceedBtn.disabled = false;
                proceedBtn.innerHTML = 'Proceed to OTP';
            }
        });

        // Handle Back button
        backBtn.addEventListener('click', function(e) {
            e.preventDefault();
            emailPasswordForm.style.display = 'block';
            otpForm.style.display = 'none';
            otpCode.value = '';
            document.getElementById('otpError').style.display = 'none';
            verifyingText.style.display = 'none';
        });

        // Auto-submit OTP when 6 digits are entered
        otpCode.addEventListener('input', async function() {
            const code = this.value.trim();
            if (code.length === 6 && /^\d{6}$/.test(code)) {
                verifyingText.style.display = 'block';
                try {
                    const response = await fetch('/api/otp/verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            phone: currentUserPhone,
                            code: code,
                            purpose: 'login'
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        document.getElementById('otpError').style.display = 'block';
                        document.getElementById('otpError').textContent = data.message || 'Invalid OTP';
                        otpCode.value = '';
                        verifyingText.style.display = 'none';
                        return;
                    }

                    // OTP verified, now complete login with email/password
                    loginForm.method = 'POST';
                    loginForm.action = '<?php echo e(route("login")); ?>';
                    const emailInput = document.createElement('input');
                    emailInput.type = 'hidden';
                    emailInput.name = 'email';
                    emailInput.value = currentUserEmail;
                    loginForm.appendChild(emailInput);

                    const passwordInput = document.querySelector('input[name="password"]');
                    const tokenInput = document.querySelector('input[name="_token"]');
                    const rememberInput = document.querySelector('input[name="remember"]');

                    // Create new form for final submission
                    const finalForm = document.createElement('form');
                    finalForm.method = 'POST';
                    finalForm.action = '<?php echo e(route("login")); ?>';

                    finalForm.appendChild(emailInput.cloneNode(true));
                    const pwdInput = document.createElement('input');
                    pwdInput.type = 'hidden';
                    pwdInput.name = 'password';
                    pwdInput.value = passwordInput.value;
                    finalForm.appendChild(pwdInput);

                    const tokenInput2 = document.createElement('input');
                    tokenInput2.type = 'hidden';
                    tokenInput2.name = '_token';
                    tokenInput2.value = tokenInput.value;
                    finalForm.appendChild(tokenInput2);

                    if (rememberInput.checked) {
                        const rememberInput2 = document.createElement('input');
                        rememberInput2.type = 'hidden';
                        rememberInput2.name = 'remember';
                        rememberInput2.value = 'on';
                        finalForm.appendChild(rememberInput2);
                    }

                    document.body.appendChild(finalForm);
                    finalForm.submit();
                } catch (err) {
                    console.error('Error verifying OTP:', err);
                    document.getElementById('otpError').style.display = 'block';
                    document.getElementById('otpError').textContent = 'Failed to verify OTP. Please try again.';
                    verifyingText.style.display = 'none';
                }
            }
        });

        // Spinner animation
        const style = document.createElement('style');
        style.innerHTML = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    </script>
</body>
</html><?php /**PATH D:\Codes\Laravel.ReactNative\AlertDavao\alertdavao\AdminSide\admin\resources\views/auth/login.blade.php ENDPATH**/ ?>