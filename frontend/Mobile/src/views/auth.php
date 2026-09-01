<!-- Auth View (Login, Register & Forgot Password) -->
<script>
    (function () {
        var winH = window.innerHeight || 0;
        var scrH = (window.screen && window.screen.height) ? window.screen.height : 0;
        var h = Math.max(winH, scrH > 300 ? scrH : winH);
        if (h > 0) {
            var topH = Math.min(330, Math.max(250, Math.round(h * 0.38)));
            document.documentElement.style.setProperty('--auth-screen-h', h + 'px');
            document.documentElement.style.setProperty('--auth-top-h', topH + 'px');
        }
    })();
</script>

<div class="auth-container">
    <!-- Top Blue Section -->
    <div class="auth-top">
        <div class="logo-container">
            <img id="auth-logo-img" src="assets/img/logo.png" alt="Intan Elyu Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%; display: block;">
        </div>
        <h1 id="auth-title" style="color: #ffffff; font-weight: 800;">Welcome to Elyu</h1>
        
        <!-- Animated Seamless SVG Wave -->
        <div class="wave-bottom">
            <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="authWaveGrad1" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#00f2fe" stop-opacity="0.85" />
                        <stop offset="40%" stop-color="#06b6d4" stop-opacity="0.65" />
                        <stop offset="100%" stop-color="#0284c7" stop-opacity="0.25" />
                    </linearGradient>
                    <linearGradient id="authWaveGrad2" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.9" />
                        <stop offset="50%" stop-color="#0284c7" stop-opacity="0.75" />
                        <stop offset="100%" stop-color="#1e3a8a" stop-opacity="0.45" />
                    </linearGradient>
                    <linearGradient id="authWaveGrad3" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#74a3cf" stop-opacity="1" />
                        <stop offset="100%" stop-color="#74a3cf" stop-opacity="1" />
                    </linearGradient>
                </defs>
                <path class="wave-layer wave-1" fill="url(#authWaveGrad1)" d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,160 L0,160 Z"></path>
                <path class="wave-layer wave-2" fill="url(#authWaveGrad2)" d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,160 L0,160 Z"></path>
                <path class="wave-layer wave-3" fill="#74a3cf" d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,160 L0,160 Z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Bottom White Section -->
    <div class="auth-bottom">
        <div class="auth-tabs" id="auth-tabs">
            <div class="auth-tab active" id="tab-login" onclick="toggleAuthMode(false)">Login</div>
            <div class="auth-tab" id="tab-register" onclick="toggleAuthMode(true)">Register</div>
            <div class="tab-gooey-glider" id="tab-gooey-glider"></div>
        </div>
        
        <div class="forms-wrapper" id="forms-wrapper">
            
            <!-- Panel 1: Login -->
            <div class="form-panel login-form">
                <form id="form-login" onsubmit="handleLogin(event)">
                    <div class="input-group">
                        <i class="fa-solid fa-mobile-screen"></i>
                        <input type="email" id="login-email" class="auth-input" placeholder="Email Address" required>
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="login-password" class="auth-input" placeholder="Password" required>
                        <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('login-password', this)"></i>
                    </div>
                    <div class="auth-options-row">
                        <label class="remember-me-label" for="login-remember">
                            <input type="checkbox" id="login-remember" class="custom-terms-checkbox">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-pwd" onclick="showForgotPassword(event)">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" id="btn-login" class="btn-circle-submit">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-social-section">
                    <div style="width:100%; display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.25);">
                        <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:1px;">Or Connect With</span>
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.25);">
                    </div>
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin(event)" style="width:100%; padding:10.5px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:13.5px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
                        <svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.53-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-8.7c0-.18-.01-.35-.05-.47z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.11 0-5.74-2.11-6.68-4.96H1.21v3.15C3.18 21.88 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.32 14.24A7.16 7.16 0 0 1 5 12c0-.79.13-1.57.32-2.31V6.54H1.21A11.96 11.96 0 0 0 0 12c0 1.92.45 3.74 1.21 5.38l4.11-3.14z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.18 2.12 1.21 5.46l4.11 3.22c.94-2.85 3.57-4.93 6.68-4.93z"/>
                        </svg>
                        <span>Sign in with Google</span>
                    </button>
                    <p class="auth-switch-prompt">
                        Don't have an account? <a href="#" onclick="toggleAuthMode(true); return false;" class="auth-switch-link">Register now</a>
                    </p>
                </div>
            </div>
            
            <!-- Panel 2: Register -->
            <div class="form-panel register-form">
                <form id="form-register" onsubmit="handleRegisterSubmit(event)">
                    <div class="input-group">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="reg-first-name" class="auth-input" placeholder="First Name" required oninput="validateRegisterFormInline()">
                    </div>
                    <div class="input-group">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="reg-last-name" class="auth-input" placeholder="Last Name" required oninput="validateRegisterFormInline()">
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-mobile-screen"></i>
                        <input type="email" id="reg-email" class="auth-input" placeholder="Email Address" required oninput="validateRegisterFormInline()">
                        <i id="reg-email-status-icon" class="fa-solid field-status-icon"></i>
                    </div>
                    <div id="reg-email-hint" class="input-field-hint" style="display: none;"></div>

                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="reg-password" class="auth-input" placeholder="Create Password" required oninput="validateRegisterFormInline()">
                        <i id="reg-password-status-icon" class="fa-solid field-status-icon password-offset"></i>
                        <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('reg-password', this)"></i>
                    </div>
                    <div id="pwd-strength-container" class="pwd-strength-wrapper" style="display: none; margin-top: 6px; margin-bottom: 12px;" data-score="0">
                        <div class="pwd-strength-segments">
                            <div class="pwd-segment seg-1"></div>
                            <div class="pwd-segment seg-2"></div>
                            <div class="pwd-segment seg-3"></div>
                            <div class="pwd-segment seg-4"></div>
                        </div>
                        <div style="display: flex; align-items: center; justify-space-between; margin-top: 6px; font-size: 11px; font-weight: 700;">
                            <span id="pwd-strength-label" style="color: #94a3b8; transition: color 0.2s ease;">Password Strength</span>
                            <span id="pwd-strength-score" style="color: rgba(148, 163, 184, 0.7); font-size: 10px;">0/4</span>
                        </div>
                        <div class="pwd-checklist" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; font-size: 10.5px; font-weight: 600;">
                            <span id="chk-len8" class="pwd-chk-item"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle;"></i> 8+ chars</span>
                            <span id="chk-num" class="pwd-chk-item"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle;"></i> a number</span>
                            <span id="chk-cap" class="pwd-chk-item"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle;"></i> a capital</span>
                            <span id="chk-sym" class="pwd-chk-item"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle;"></i> a symbol</span>
                        </div>
                    </div>
                    
                    <div class="terms-agreement-row">
                        <input type="checkbox" id="reg-privacy-checkbox" class="custom-terms-checkbox">
                        <label for="reg-privacy-checkbox" id="reg-privacy-label" class="terms-agreement-label">
                            I agree to the <a href="#" id="link-terms-privacy" onclick="openPrivacyPolicyModal(event)" class="terms-policy-highlight">Terms &amp; Privacy Policy</a>.
                        </label>
                    </div>
                    
                    <button type="submit" id="btn-register" class="btn-circle-submit">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-social-section">
                    <div style="width:100%; display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                        <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px;">Or Connect With</span>
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                    </div>
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin(event)" style="width:100%; padding:10.5px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:13.5px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
                        <svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.53-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-8.7c0-.18-.01-.35-.05-.47z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.11 0-5.74-2.11-6.68-4.96H1.21v3.15C3.18 21.88 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.32 14.24A7.16 7.16 0 0 1 5 12c0-.79.13-1.57.32-2.31V6.54H1.21A11.96 11.96 0 0 0 0 12c0 1.92.45 3.74 1.21 5.38l4.11-3.14z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.18 2.12 1.21 5.46l4.11 3.22c.94-2.85 3.57-4.93 6.68-4.93z"/>
                        </svg>
                        <span>Sign up with Google</span>
                    </button>
                    <p class="auth-switch-prompt">
                        Already have an account? <a href="#" onclick="toggleAuthMode(false); return false;" class="auth-switch-link">Log in</a>
                    </p>
                </div>
            </div>

            <!-- Panel 3: Forgot Password -->
            <div class="form-panel forgot-form">
                <a href="#" class="back-link" id="fp-back-link" onclick="handleFpBack(event)">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>

                <!-- Step 1: Enter Email -->
                <div id="fp-form-state">
                    <div class="fp-header">
                        <h3>Reset Password</h3>
                        <p>Enter your email to receive a 6-digit reset code.</p>
                    </div>

                    <form id="form-forgot" onsubmit="handleForgotPassword(event)">
                        <div class="input-group">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <input type="email" id="fp-email" class="auth-input" placeholder="Email Address" required>
                        </div>
                        
                        <button type="submit" id="fp-btn" class="btn-circle-submit">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <!-- Step 2: Enter 6-Digit Code -->
                <div id="fp-code-state" style="display: none; text-align: center; padding: 10px 0;">
                    <h3 style="margin: 0 0 8px 0; color: #ffffff; font-size: 20px; font-weight: 800;">Reset Code Sent</h3>
                    <p style="color: rgba(255, 255, 255, 0.95); font-size: 13.5px; margin: 0 0 20px 0; font-weight: 500; line-height: 1.5;">We sent a 6-digit reset code to <br><strong id="fp-target-email" style="color: #ffffff; font-weight: 800; font-size: 14px; background: rgba(0, 0, 0, 0.18); padding: 3px 10px; border-radius: 6px; display: inline-block; margin-top: 5px;"></strong></p>

                    <form id="form-fp-verify-code" onsubmit="handleVerifyFpCode(event)">
                        <!-- 6 Individual Digit Input Boxes -->
                        <div class="otp-boxes-container" style="margin: 18px 0 20px 0;">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" oninput="handleFpOtpBoxInput(this, 0)" onkeydown="handleFpOtpBoxKeydown(this, event, 0)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 1)" onkeydown="handleFpOtpBoxKeydown(this, event, 1)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 2)" onkeydown="handleFpOtpBoxKeydown(this, event, 2)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 3)" onkeydown="handleFpOtpBoxKeydown(this, event, 3)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 4)" onkeydown="handleFpOtpBoxKeydown(this, event, 4)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 5)" onkeydown="handleFpOtpBoxKeydown(this, event, 5)" onpaste="handleFpOtpPaste(event)">
                        </div>

                        <button type="submit" id="fp-verify-btn" class="btn-circle-submit" style="margin-bottom: 14px;">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                        <div style="font-size: 13px; color: rgba(255, 255, 255, 0.95); margin-top: 14px; font-weight: 500;">
                            Didn't receive email? 
                            <button type="button" id="fp-resend-btn" onclick="handleResendFpEmail(event)" style="background: none; border: none; color: #ffffff; font-weight: 800; cursor: pointer; text-decoration: underline; padding: 0;">
                                Resend Code
                            </button>
                            <span id="fp-countdown-text" style="display: none; color: #fef08a; font-weight: 700;"> (Resend in <span id="fp-countdown-sec">45</span>s)</span>
                        </div>
                    </form>
                </div>

                <!-- Step 3: Enter New Password -->
                <div id="fp-password-state" style="display: none; text-align: center; padding: 10px 0;">
                    <h3 style="margin: 0 0 6px 0; color: white; font-size: 19px; font-weight: 800;">Create New Password</h3>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 13px; margin: 0 0 20px 0; font-weight: 500;">Enter your new password below.</p>

                    <form id="form-fp-password" onsubmit="handleResetPasswordSubmit(event)">
                        <div class="input-group" style="margin-bottom: 16px;">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="fp-new-password" class="auth-input" placeholder="New Password (min 8 chars)" minlength="8" required>
                            <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('fp-new-password', this)"></i>
                        </div>
                        <div class="input-group" style="margin-bottom: 20px;">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="fp-confirm-password" class="auth-input" placeholder="Confirm New Password" minlength="8" required>
                            <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('fp-confirm-password', this)"></i>
                        </div>

                        <button type="submit" id="fp-password-btn" class="btn-circle-submit" style="margin-bottom: 10px;">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel 4: Email OTP Verification -->
            <div class="form-panel otp-form" id="panel-otp" style="padding: 0 10px;">
                <a href="#" class="back-link" onclick="hideOtpPanel(event)" style="display: inline-flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.7); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 16px; transition: color 0.2s;">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>

                <div style="text-align: center; margin-bottom: 16px;">
                    <div style="width: 54px; height: 54px; border-radius: 16px; background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.3); display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 22px; margin: 0 auto 10px auto;">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                    </div>
                    <h3 style="margin: 0 0 4px 0; color: white; font-size: 20px; font-weight: 800;">Verify Your Email</h3>
                    <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 0;">We sent a 6-digit code to <br><strong id="otp-target-email" style="color: #38bdf8; font-family: monospace;"></strong></p>
                </div>

                <form id="form-otp" onsubmit="handleVerifyOtp(event)">
                    <!-- 6 Individual Digit Input Boxes -->
                    <div class="otp-boxes-container">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" oninput="handleOtpBoxInput(this, 0)" onkeydown="handleOtpBoxKeydown(this, event, 0)" onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleOtpBoxInput(this, 1)" onkeydown="handleOtpBoxKeydown(this, event, 1)" onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleOtpBoxInput(this, 2)" onkeydown="handleOtpBoxKeydown(this, event, 2)" onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleOtpBoxInput(this, 3)" onkeydown="handleOtpBoxKeydown(this, event, 3)" onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleOtpBoxInput(this, 4)" onkeydown="handleOtpBoxKeydown(this, event, 4)" onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleOtpBoxInput(this, 5)" onkeydown="handleOtpBoxKeydown(this, event, 5)" onpaste="handleOtpPaste(event)">
                    </div>
                    
                    <button type="submit" id="btn-otp" class="btn-circle-submit" style="margin-top: 10px;">
                        <i class="fa-solid fa-check"></i>
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>

<!-- Login Success Modal -->
<div id="login-success-modal" class="login-success-overlay" style="display: none;">
    <div class="login-success-card">
        <div class="success-icon-badge" id="modal-badge">
            <!-- Spinner tail circle -->
            <svg class="circular-spinner" id="modal-spinner-svg" viewBox="0 0 50 50">
                <circle class="spinner-track" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
                <circle class="spinner-head" cx="25" cy="25" r="20" fill="none" stroke-width="4"></circle>
            </svg>
            <!-- Checkmark icon -->
            <i class="fa-solid fa-check modal-check-mark" id="modal-checkmark-icon" style="display:none;"></i>
        </div>
        <h2 class="success-modal-title" id="login-modal-title">Logging in...</h2>
        <p class="success-modal-sub" id="login-success-user-name">Authenticating your account</p>
    </div>
</div>



<!-- Google Auth Cancelled / Exit Modal -->
<div id="auth-cancel-modal" class="auth-2fa-overlay" style="display: none;">
    <div class="auth-2fa-card">
        <button type="button" class="auth-2fa-close" onclick="closeAuthCancelModal()"><i class="fa-solid fa-xmark"></i></button>
        
        <div class="auth-2fa-icon-ring" style="border-color: #f59e0b; color: #f59e0b; background: rgba(245, 158, 11, 0.15); box-shadow: 0 0 22px rgba(245, 158, 11, 0.35);">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        
        <h3 class="auth-2fa-title">Sign-In Cancelled</h3>
        
        <p class="auth-2fa-desc" id="auth-cancel-modal-msg">
            You exited the Google account chooser without logging in or signing up. Please choose an account to continue or sign in with your email.
        </p>
        
        <div class="auth-2fa-alert-box" style="margin-bottom: 20px;">
            <i class="fa-solid fa-shield-halved" style="color: #f59e0b; font-size: 14px; margin-right: 8px; flex-shrink: 0;"></i>
            <span>No changes were made to your account.</span>
        </div>

        <button type="button" onclick="closeAuthCancelModal()" class="auth-2fa-btn-primary" style="background: linear-gradient(135deg, #38bdf8, #2563eb);">
            <i class="fa-solid fa-check" style="margin-right: 8px;"></i>Got it
        </button>
    </div>
</div>

<div id="privacy-policy-modal" class="auth-2fa-overlay" style="display: none;">
    <div class="privacy-modal-card">
        <button type="button" class="privacy-modal-close" onclick="closePrivacyPolicyModal()" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>
        
        <div class="privacy-modal-header">
            <div class="privacy-modal-icon-ring">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h3 class="privacy-modal-title">Terms &amp; Privacy Policy</h3>
                <span class="privacy-modal-badge">Data Protection · Step 1 of 2</span>
            </div>
        </div>

        <div id="privacy-modal-scroll-body" class="privacy-modal-scroll-body">
            <div class="policy-welcome-banner">
                <i class="fa-solid fa-circle-info policy-welcome-icon"></i>
                <p style="margin: 0; font-size: 12px; color: #334155; line-height: 1.55;">
                    Welcome to <strong>Intan Elyu Tourism Management System</strong>. Please read through our terms of service and privacy practices before activating your account:
                </p>
            </div>
            
            <div class="policy-item-card">
                <div class="policy-item-header">
                    <div class="policy-icon-badge"><i class="fa-solid fa-user-check"></i></div>
                    <span class="policy-item-title">1. Account &amp; Registration</span>
                </div>
                <p class="policy-item-desc">By registering, you confirm that personal details provided (Full Name, Email) are accurate and belong to you. You are responsible for safeguarding your credentials.</p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <div class="policy-icon-badge"><i class="fa-solid fa-lock"></i></div>
                    <span class="policy-item-title">2. Information &amp; Encryption</span>
                </div>
                <p class="policy-item-desc">We store your name, email, and Bcrypt-encrypted password hashes to personalize your Elyu itinerary. We never sell or share your data with unauthorized third parties.</p>
            </div>
            
            <div class="policy-item-card">
                <div class="policy-item-header">
                    <div class="policy-icon-badge"><i class="fa-solid fa-location-dot"></i></div>
                    <span class="policy-item-title">3. Location &amp; Fair Play XP</span>
                </div>
                <p class="policy-item-desc">Device location is accessed strictly during active tourist spot check-ins to verify XP rewards and badge unlocks. We do not track your location in the background.</p>
            </div>
            
            <div class="policy-item-card">
                <div class="policy-item-header">
                    <div class="policy-icon-badge"><i class="fa-solid fa-shield-halved"></i></div>
                    <span class="policy-item-title">4. 2-Factor Email Security (2FA)</span>
                </div>
                <p class="policy-item-desc">After accepting these terms, a 6-digit verification code will be dispatched to your email address to confirm identity before account activation.</p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <div class="policy-icon-badge"><i class="fa-solid fa-leaf"></i></div>
                    <span class="policy-item-title">5. Responsible Tourism</span>
                </div>
                <p class="policy-item-desc">As a registered tourist on Intan Elyu, you agree to respect local La Union heritage, avoid littering, preserve coastal beaches, and follow local municipal guidelines.</p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <div class="policy-icon-badge"><i class="fa-solid fa-eye-slash"></i></div>
                    <span class="policy-item-title">6. Privacy Rights &amp; Profile</span>
                </div>
                <p class="policy-item-desc">Your email remains private. You can toggle your profile to Private mode anytime in App Settings to hide your rank on public leaderboards or request account erasure.</p>
            </div>
        </div>

        <div class="privacy-acceptance-box">
            <input type="checkbox" id="chk-accept-privacy" class="custom-terms-checkbox" style="cursor: pointer;">
            <label for="chk-accept-privacy" id="lbl-chk-accept-privacy" style="cursor: pointer; margin: 0; line-height: 1.35; font-size: 11.5px; font-weight: 600; color: #1e293b;">
                I have read, understood, and accept the Terms &amp; Privacy Policy.
            </label>
        </div>

        <div class="privacy-modal-actions">
            <button type="button" onclick="closePrivacyPolicyModal()" class="btn-privacy-decline">
                Decline
            </button>
            <button type="button" id="btn-accept-policy-proceed" onclick="acceptPolicyAndProceed()" class="btn-privacy-accept">
                Accept
            </button>
        </div>
    </div>
</div>

<style>
.login-success-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 999999;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.login-success-overlay.active {
    opacity: 1;
    pointer-events: auto;
}
.login-success-card {
    background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
    border: none !important;
    outline: none !important;
    border-radius: 26px;
    padding: 34px 24px 28px 24px;
    max-width: 310px;
    width: 86%;
    text-align: center;
    box-shadow: 0 15px 35px rgba(10, 25, 60, 0.4), 0 0 25px rgba(63, 125, 183, 0.25);
    position: relative;
    transform: scale(0.88) translateY(16px);
    transition: transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
}
.login-success-overlay.active .login-success-card {
    transform: scale(1) translateY(0);
}
.success-icon-badge {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.22);
    border: 2px solid rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px auto;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
}
.success-icon-badge.is-done {
    background: #ffffff;
    border-color: #ffffff;
    box-shadow: 0 8px 25px rgba(15, 23, 42, 0.2);
}
.success-modal-title {
    color: #ffffff !important;
    font-size: 19px !important;
    font-weight: 800 !important;
    margin: 0 0 6px 0 !important;
    letter-spacing: -0.2px;
    text-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
}
.success-modal-sub {
    color: rgba(255, 255, 255, 0.95) !important;
    font-size: 13.5px !important;
    margin: 0 !important;
    font-weight: 500 !important;
    text-shadow: 0 1px 3px rgba(15, 23, 42, 0.15);
}
.circular-spinner .spinner-track {
    stroke: rgba(255, 255, 255, 0.35) !important;
}
.circular-spinner .spinner-head {
    stroke: #ffffff !important;
}
.modal-check-mark {
    color: #0284c7 !important;
    font-size: 24px !important;
}
.auth-2fa-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 999999;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.auth-2fa-overlay.active {
    opacity: 1;
    pointer-events: auto;
}
.auth-2fa-card {
    background: rgba(15, 23, 42, 0.96);
    border: 1.5px solid rgba(56, 189, 248, 0.4);
    border-radius: 24px;
    padding: 28px 24px;
    max-width: 380px;
    width: 100%;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 35px rgba(56, 189, 248, 0.15);
    position: relative;
    transform: scale(0.88) translateY(16px);
    transition: transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.auth-2fa-overlay.active .auth-2fa-card {
    transform: scale(1) translateY(0);
}
.auth-2fa-close {
    position: absolute;
    top: 14px;
    right: 14px;
    background: rgba(255, 255, 255, 0.08);
    border: none;
    color: rgba(248, 250, 252, 0.7);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.2s;
}
.auth-2fa-close:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
}
.auth-2fa-icon-ring {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(56, 189, 248, 0.15);
    border: 2px solid #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px auto;
    box-shadow: 0 0 22px rgba(56, 189, 248, 0.35);
    font-size: 28px;
    color: #38bdf8;
}
.auth-2fa-title {
    font-size: 20px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 8px 0;
    letter-spacing: -0.3px;
}
.auth-2fa-desc {
    font-size: 12px;
    color: rgba(148, 163, 184, 0.9);
    line-height: 1.55;
    margin: 0 0 16px 0;
}
.auth-2fa-alert-box {
    background: rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(245, 158, 11, 0.35);
    border-radius: 14px;
    padding: 12px 14px;
    margin-bottom: 20px;
    text-align: left;
    font-size: 12px;
    color: #f59e0b;
    font-weight: 700;
    display: flex;
    align-items: center;
}
.auth-2fa-info-box {
    background: rgba(56, 189, 248, 0.08);
    border: 1px solid rgba(56, 189, 248, 0.3);
    border-radius: 14px;
    padding: 12px 14px;
    margin-bottom: 18px;
    text-align: left;
    font-size: 12px;
    color: #38bdf8;
    font-weight: 700;
    display: flex;
    align-items: center;
}
.auth-2fa-btn-primary {
    width: 100%;
    padding: 14px;
    border-radius: 100px;
    background: linear-gradient(135deg, #38bdf8, #2563eb);
    border: none;
    color: #ffffff;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(56, 189, 248, 0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.auth-2fa-btn-primary:active {
    transform: scale(0.97);
}
.auth-2fa-btn-success {
    width: 100%;
    padding: 14px;
    border-radius: 100px;
    background: linear-gradient(135deg, #34c759, #10b981);
    border: none;
    color: #ffffff;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(52, 199, 89, 0.35);
    transition: transform 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.auth-2fa-otp-input {
    width: 100%;
    text-align: center;
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 8px;
    padding: 12px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.06);
    border: 1.5px solid #38bdf8;
    color: #ffffff;
    margin-bottom: 16px;
    outline: none;
}
.circular-spinner {
    width: 38px;
    height: 38px;
    animation: spinnerRotate 1.2s linear infinite;
}
.spinner-track {
    stroke: rgba(255, 255, 255, 0.2);
}
.spinner-head {
    stroke: #ffffff;
    stroke-linecap: round;
    stroke-dasharray: 80, 200;
    stroke-dashoffset: 0;
    animation: spinnerDash 1.4s ease-in-out infinite;
}
@keyframes spinnerRotate {
    100% { transform: rotate(360deg); }
}
@keyframes spinnerDash {
    0% { stroke-dasharray: 1, 200; stroke-dashoffset: 0; }
    50% { stroke-dasharray: 89, 200; stroke-dashoffset: -35px; }
    100% { stroke-dasharray: 89, 200; stroke-dashoffset: -124px; }
}
.modal-check-mark {
    font-size: 34px;
    color: #ffffff;
    animation: checkPop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes checkPop {
    0% { transform: scale(0.2); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.success-modal-title {
    color: #ffffff;
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 6px 0;
    letter-spacing: -0.3px;
}
.success-modal-sub {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    font-weight: 500;
    margin: 0;
    line-height: 1.4;
}
</style>

<script>
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

    window.openAuthCancelModal = function(msg) {
        const modal = document.getElementById('auth-cancel-modal');
        if (!modal) return;
        if (msg) {
            const msgEl = document.getElementById('auth-cancel-modal-msg');
            if (msgEl) msgEl.textContent = msg;
        }
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    };

    window.closeAuthCancelModal = function() {
        const modal = document.getElementById('auth-cancel-modal');
        if (modal) modal.classList.remove('active');
        setTimeout(() => {
            if (modal) modal.style.display = 'none';
        }, 300);
    };

    window.openAuth2FAModal = function(user) {
        const modal = document.getElementById('auth-2fa-modal');
        if (!modal) {
            if (typeof navigateTo === 'function') navigateTo('dashboard');
            return;
        }
        
        document.getElementById('auth-2fa-step-disabled').style.display = 'block';
        document.getElementById('auth-2fa-step-verify').style.display = 'none';

        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    };

    window.closeAuth2FAModal = function() {
        const modal = document.getElementById('auth-2fa-modal');
        if (modal) modal.classList.remove('active');
        setTimeout(() => {
            if (modal) modal.style.display = 'none';
            if (typeof navigateTo === 'function') navigateTo('dashboard');
        }, 300);
    };

    window.handleAuthInitiate2FA = async function() {
        const btn = document.getElementById('btn-auth-enable-2fa');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" style="margin-right: 6px;"></i> Sending Code...';
            btn.disabled = true;
        }

        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const res = await fetch(backendUrl + '/api/tourist/2fa/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ enable: true })
            });
            const data = await res.json();
            if (data.awaiting_verification || data.success) {
                if (typeof showToast === 'function') showToast(data.message || 'Security verification code sent to your email!');
                document.getElementById('auth-2fa-step-disabled').style.display = 'none';
                document.getElementById('auth-2fa-step-verify').style.display = 'block';
                const boxes = document.querySelectorAll('.auth-2fa-otp-box');
                boxes.forEach(b => b.value = '');
                if (boxes[0]) boxes[0].focus();
            } else {
                throw new Error(data.message || 'Failed to initiate 2FA setup.');
            }
        } catch (e) {
            console.error('2FA Initiate Error:', e);
            if (typeof showToast === 'function') showToast(e.message || 'Error sending 2FA code.');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-lock" style="margin-right: 8px;"></i>Enable 2FA Security';
                btn.disabled = false;
            }
        }
    };

    window.handleAuth2FAOtpBoxInput = function(el, index) {
        el.value = el.value.replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.auth-2fa-otp-box');
        if (el.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }
    };

    window.handleAuth2FAOtpBoxKeydown = function(el, e, index) {
        const boxes = document.querySelectorAll('.auth-2fa-otp-box');
        if (e.key === 'Backspace' && !el.value && index > 0) {
            boxes[index - 1].focus();
        }
    };

    window.handleAuth2FAOtpPaste = function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.auth-2fa-otp-box');
        for (let i = 0; i < boxes.length && i < pasted.length; i++) {
            boxes[i].value = pasted[i];
        }
        if (pasted.length >= 6) {
            boxes[5].focus();
        }
    };

    window.handleAuthVerify2FA = async function(e) {
        e.preventDefault();
        const boxes = document.querySelectorAll('.auth-2fa-otp-box');
        const code = Array.from(boxes).map(b => b.value).join('');
        if (code.length < 6) {
            if (typeof showToast === 'function') showToast('Please enter all 6 digits of your verification code.');
            return;
        }

        const btn = document.getElementById('btn-auth-verify-2fa');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" style="margin-right: 6px;"></i> Verifying...';
            btn.disabled = true;
        }

        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const res = await fetch(backendUrl + '/api/tourist/2fa/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ code: code })
            });
            const data = await res.json();
            if (res.ok && data.enabled) {
                localStorage.setItem('intan_elyu_2fa_active', 'true');
                if (typeof showToast === 'function') showToast('🔒 Two-Factor Authentication Activated!');
                closeAuth2FAModal();
            } else {
                throw new Error(data.message || 'Invalid 6-digit verification code.');
            }
        } catch (e) {
            console.error('2FA Verify Error:', e);
            if (typeof showToast === 'function') showToast(e.message || 'Verification failed.');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i>Verify & Activate 2FA';
                btn.disabled = false;
            }
        }
    };

    window.showLoginSuccessModal = function(user) {
        const modal = document.getElementById('login-success-modal');
        const badge = document.getElementById('modal-badge');
        const spinner = document.getElementById('modal-spinner-svg');
        const checkmark = document.getElementById('modal-checkmark-icon');
        const titleEl = document.getElementById('login-modal-title');
        const nameEl = document.getElementById('login-success-user-name');

        // Initial State: Spinning Tail Circle
        if (badge) badge.classList.remove('is-done');
        if (spinner) spinner.style.display = 'block';
        if (checkmark) checkmark.style.display = 'none';
        if (titleEl) titleEl.textContent = 'Logging in...';
        if (nameEl) nameEl.textContent = 'Authenticating your account';

        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('active'); }, 20);
        }

        // Transition Stage: Spinner turns to Green Checkmark after 2.2 seconds
        setTimeout(() => {
            if (spinner) spinner.style.display = 'none';
            if (checkmark) checkmark.style.display = 'block';
            if (badge) badge.classList.add('is-done');
            if (titleEl) titleEl.textContent = 'Successfully Logged In!';
            if (nameEl) {
                nameEl.textContent = (user && user.name) ? 'Welcome back, ' + user.name + '!' : 'Welcome back to Intan Elyu!';
            }
        }, 2200);

        // Final Stage: Direct transition to dashboard
        setTimeout(() => {
            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => { modal.style.display = 'none'; }, 250);
            }
            if (typeof navigateTo === 'function') {
                navigateTo('dashboard');
            } else {
                window.location.href = '?view=dashboard';
            }
        }, 2800);
    };

    const wrapper = document.getElementById('forms-wrapper');
    const titleEl = document.getElementById('auth-title');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    const tabsContainer = document.getElementById('auth-tabs');

    function updateTitleWithTransition(newText) {
        if (titleEl.textContent === newText) return;
        titleEl.classList.add('text-fade-out');
        setTimeout(() => {
            titleEl.textContent = newText;
            titleEl.classList.remove('text-fade-out');
        }, 200); // Wait for the 0.2s fade out transition
    }

    function positionTabGlider(isRegister, animate = true) {
        const glider = document.getElementById('tab-gooey-glider');
        const targetTab = isRegister ? tabRegister : tabLogin;
        if (!glider || !targetTab || !tabsContainer) return;

        const targetRect = targetTab.getBoundingClientRect();
        const containerRect = tabsContainer.getBoundingClientRect();
        const targetLeft = targetRect.left - containerRect.left + (targetRect.width - 32) / 2;

        if (!animate) {
            glider.style.transition = 'none';
            glider.style.left = `${targetLeft}px`;
            return;
        }

        glider.style.transition = '';
        glider.classList.remove('stretching-right', 'stretching-left');
        void glider.offsetWidth; // trigger reflow

        if (isRegister) {
            glider.classList.add('stretching-right');
        } else {
            glider.classList.add('stretching-left');
        }
        glider.style.left = `${targetLeft}px`;
    }

    // Initialize glider position on load & handle resize
    setTimeout(() => { positionTabGlider(false, false); }, 80);
    window.addEventListener('resize', () => {
        const isReg = wrapper && wrapper.classList.contains('show-register');
        positionTabGlider(isReg, false);
    });

    function toggleAuthMode(isRegister) {
        tabsContainer.style.display = 'flex';
        wrapper.classList.remove('show-forgot', 'show-otp');
        positionTabGlider(isRegister, true);
        
        if (isRegister) {
            wrapper.classList.add('show-register');
            tabLogin.classList.remove('active');
            tabRegister.classList.add('active');
            updateTitleWithTransition('Start your Journey');
        } else {
            wrapper.classList.remove('show-register');
            tabRegister.classList.remove('active');
            tabLogin.classList.add('active');
            updateTitleWithTransition('Welcome to Elyu');
        }
    }

    window.showForgotPassword = function(e) {
        if (e) e.preventDefault();
        
        // Hide tabs
        if (tabsContainer) tabsContainer.style.display = 'none';
        
        // Reset forgot form steps
        const formState = document.getElementById('fp-form-state');
        const codeState = document.getElementById('fp-code-state');
        const pwdState = document.getElementById('fp-password-state');
        const emailInput = document.getElementById('fp-email');

        if (formState) formState.style.display = 'block';
        if (codeState) codeState.style.display = 'none';
        if (pwdState) pwdState.style.display = 'none';
        if (emailInput) emailInput.value = '';
        
        const btn = document.getElementById('fp-btn');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            btn.disabled = false;
        }

        if (wrapper) {
            wrapper.classList.remove('show-register');
            wrapper.classList.add('show-forgot');
        }
        if (typeof updateTitleWithTransition === 'function') {
            updateTitleWithTransition('Account Recovery');
        }
    };

    window.hideForgotPassword = function(e) {
        if (e) e.preventDefault();
        // Restore tabs
        if (tabsContainer) tabsContainer.style.display = 'flex';
        if (wrapper) wrapper.classList.remove('show-forgot', 'show-register');
        if (tabRegister) tabRegister.classList.remove('active');
        if (tabLogin) tabLogin.classList.add('active');
        if (typeof updateTitleWithTransition === 'function') {
            updateTitleWithTransition('Welcome to Elyu');
        }
    };

    function togglePasswordVisibility(inputId, iconElement) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            iconElement.classList.remove('fa-eye');
            iconElement.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            iconElement.classList.remove('fa-eye-slash');
            iconElement.classList.add('fa-eye');
        }
    }

    window.handleLogin = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-login');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;

        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const remember = document.getElementById('login-remember')?.checked || false;

        try {
            const response = await fetch(backendUrl + '/api/auth/login', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, password: password, remember: remember })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Invalid login credentials.');
            }

            if (remember) {
                localStorage.setItem('intan_elyu_remembered_email', email);
                localStorage.setItem('intan_elyu_remember_me', '1');
            } else {
                localStorage.removeItem('intan_elyu_remembered_email');
                localStorage.removeItem('intan_elyu_remember_me');
            }

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            
            showLoginSuccessModal(data.user);
        } catch (error) {
            console.error('Login Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            btn.disabled = false;
        }
    };

    window.validateRegisterFormInline = function() {
        const emailEl = document.getElementById('reg-email');
        const emailIcon = document.getElementById('reg-email-status-icon');
        const emailHint = document.getElementById('reg-email-hint');

        const pwdEl = document.getElementById('reg-password');
        const pwdIcon = document.getElementById('reg-password-status-icon');
        const pwdHint = document.getElementById('reg-password-hint');

        if (emailEl) {
            const val = emailEl.value.trim();
            if (val.length === 0) {
                if (emailIcon) emailIcon.className = 'fa-solid field-status-icon';
                if (emailHint) { emailHint.innerHTML = ''; emailHint.style.display = 'none'; }
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(val)) {
                    if (emailIcon) emailIcon.className = 'fa-solid fa-circle-check field-status-icon valid';
                    if (emailHint) { emailHint.innerHTML = ''; emailHint.style.display = 'none'; }
                } else {
                    if (emailIcon) emailIcon.className = 'fa-solid fa-circle-xmark field-status-icon invalid';
                    if (emailHint) {
                        emailHint.style.display = 'block';
                        emailHint.innerHTML = '<span style="color:#f87171;"><i class="fa-solid fa-circle-exclamation" style="margin-right:3px;"></i> Enter a valid email address</span>';
                    }
                }
            }
        }

        if (pwdEl) {
            const pwd = pwdEl.value;
            const container = document.getElementById('pwd-strength-container');
            const label = document.getElementById('pwd-strength-label');
            const scoreEl = document.getElementById('pwd-strength-score');

            const chkLen8 = document.getElementById('chk-len8');
            const chkNum = document.getElementById('chk-num');
            const chkCap = document.getElementById('chk-cap');
            const chkSym = document.getElementById('chk-sym');

            if (pwd.length === 0) {
                if (pwdIcon) pwdIcon.className = 'fa-solid field-status-icon password-offset';
                if (container) container.style.display = 'none';
            } else {
                if (container) container.style.display = 'block';

                const len8 = pwd.length >= 8;
                const hasNum = /\d/.test(pwd);
                const hasCap = /[A-Z]/.test(pwd);
                const hasSym = /[^A-Za-z0-9]/.test(pwd);

                const score = [len8, hasNum, hasCap, hasSym].filter(Boolean).length;
                if (container) container.dataset.score = score;
                if (scoreEl) scoreEl.textContent = score + '/4';

                // Update checklist item styles
                if (chkLen8) chkLen8.className = 'pwd-chk-item' + (len8 ? ' passed' : '');
                if (chkNum) chkNum.className = 'pwd-chk-item' + (hasNum ? ' passed' : '');
                if (chkCap) chkCap.className = 'pwd-chk-item' + (hasCap ? ' passed' : '');
                if (chkSym) chkSym.className = 'pwd-chk-item' + (hasSym ? ' passed' : '');

                // Label and status icon update
                const labels = ['Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const colors = ['#f87171', '#f87171', '#fb923c', '#facc15', '#34d399'];
                
                if (label) {
                    label.textContent = labels[score] || 'Password Strength';
                    label.style.color = colors[score] || '#94a3b8';
                }

                if (score >= 3 && len8) {
                    if (pwdIcon) pwdIcon.className = 'fa-solid fa-circle-check field-status-icon password-offset valid';
                } else {
                    if (pwdIcon) pwdIcon.className = 'fa-solid fa-circle-xmark field-status-icon password-offset invalid';
                }
            }
        }
    };

    window.handleRegisterSubmit = async function(e) {
        if (e) e.preventDefault();
        window.validateRegisterFormInline();
        const pwd = document.getElementById('reg-password')?.value || '';
        const firstName = (document.getElementById('reg-first-name')?.value || '').trim();
        const lastName = (document.getElementById('reg-last-name')?.value || '').trim();
        const email = (document.getElementById('reg-email')?.value || '').trim();
        const chk = document.getElementById('reg-privacy-checkbox');

        if (!firstName || !lastName || !email || !pwd) {
            if (typeof showToast === 'function') showToast('Please fill in all registration fields.');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            const emailHint = document.getElementById('reg-email-hint');
            if (emailHint) {
                emailHint.style.display = 'block';
                emailHint.innerHTML = '<span style="color:#f87171; font-size:11px; font-weight:600; display:flex; align-items:center; gap:4px; margin-top:3px;"><i class="fa-solid fa-circle-xmark"></i> Enter a valid email (e.g. name@domain.com)</span>';
            }
            document.getElementById('reg-email')?.focus();
            return;
        }

        if (pwd.length < 8) {
            const pwdHint = document.getElementById('reg-password-hint');
            if (pwdHint) {
                pwdHint.style.display = 'block';
                pwdHint.innerHTML = '<span style="color:#f87171; font-size:11px; font-weight:600; display:flex; align-items:center; gap:4px; margin-top:3px;"><i class="fa-solid fa-circle-xmark"></i> Password needs at least 8 characters</span>';
            }
            document.getElementById('reg-password')?.focus();
            return;
        }

        if (chk && !chk.checked) {
            if (typeof showToast === 'function') showToast('Please accept the Terms & Privacy Policy first.');
            return;
        }

        await window.submitRegistrationAndTrigger2FA();
    };

    window.openPrivacyPolicyModal = function(e) {
        if (e) e.preventDefault();

        const modal = document.getElementById('privacy-policy-modal');
        if (!modal) return;

        const chk = document.getElementById('chk-accept-privacy');
        const lblChk = document.getElementById('lbl-chk-accept-privacy');
        const acceptBtn = document.getElementById('btn-accept-policy-proceed');
        const scrollBody = document.getElementById('privacy-modal-scroll-body');

        if (chk) { 
            chk.checked = false; 
            chk.disabled = false; 
            chk.style.opacity = '1'; 
            chk.style.cursor = 'pointer'; 
        }
        if (lblChk) { 
            lblChk.style.cursor = 'pointer'; 
            lblChk.style.opacity = '1'; 
        }
        if (acceptBtn) {
            acceptBtn.disabled = false;
            acceptBtn.style.opacity = '1';
            acceptBtn.style.cursor = 'pointer';
            acceptBtn.innerHTML = 'Accept';
        }

        if (scrollBody) {
            scrollBody.scrollTop = 0;
        }

        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
            if (scrollBody) {
                scrollBody.scrollTop = 0;
                scrollBody.scrollTo(0, 0);
            }
        });
    };

    window.closePrivacyPolicyModal = function() {
        const modal = document.getElementById('privacy-policy-modal');
        const scrollBody = document.getElementById('privacy-modal-scroll-body');
        if (modal) modal.classList.remove('active');
        if (scrollBody) {
            scrollBody.scrollTop = 0;
            scrollBody.scrollTo(0, 0);
        }
        setTimeout(() => {
            if (modal) modal.style.display = 'none';
        }, 300);
    };

    window.acceptPolicyAndProceed = async function() {
        const chk = document.getElementById('chk-accept-privacy');
        if (chk) chk.checked = true;

        const regChk = document.getElementById('reg-privacy-checkbox');
        if (regChk) regChk.checked = true;

        closePrivacyPolicyModal();
        await window.submitRegistrationAndTrigger2FA();
    };

    window.submitRegistrationAndTrigger2FA = async function() {
        const pwd = document.getElementById('reg-password')?.value || '';
        const firstName = (document.getElementById('reg-first-name')?.value || '').trim();
        const lastName = (document.getElementById('reg-last-name')?.value || '').trim();
        const name = `${firstName} ${lastName}`.trim();
        const email = (document.getElementById('reg-email')?.value || '').trim();

        const modal = document.getElementById('login-success-modal');
        const titleEl = document.getElementById('login-modal-title');
        const subEl = document.getElementById('login-success-user-name');
        const spinnerSvg = document.getElementById('modal-spinner-svg');
        const checkmarkIcon = document.getElementById('modal-checkmark-icon');

        const btn = document.getElementById('btn-register');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
        }

        // Show full-screen loading modal with active CSS transition
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('active'); }, 10);
        }
        if (titleEl) titleEl.textContent = 'Registering your account...';
        if (subEl) subEl.textContent = 'Please wait while we create your tourist profile';
        if (spinnerSvg) spinnerSvg.style.display = 'block';
        if (checkmarkIcon) checkmarkIcon.style.display = 'none';

        const startTime = Date.now();

        try {
            const response = await fetch(backendUrl + '/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ 
                    first_name: firstName,
                    last_name: lastName,
                    name: name, 
                    email: email, 
                    password: pwd, 
                    password_confirmation: pwd 
                })
            });
            const data = await response.json();
            
            if (!response.ok) {
                let errMsg = 'Registration failed';
                if (data.errors && data.errors.email && data.errors.email[0]) {
                    errMsg = data.errors.email[0];
                } else if (data.message) {
                    errMsg = data.message;
                } else if (data.errors) {
                    const details = Object.values(data.errors).flat().join(' ');
                    if (details) errMsg = details;
                }
                throw new Error(errMsg);
            }

            // Guarantee spinner stays visible for at least 5 seconds
            const elapsedTime = Date.now() - startTime;
            const minSpinnerTime = 5000;
            if (elapsedTime < minSpinnerTime) {
                await new Promise(r => setTimeout(r, minSpinnerTime - elapsedTime));
            }

            // Animate checkmark success in modal
            if (titleEl) titleEl.textContent = 'Account Registered Successfully!';
            if (subEl) subEl.textContent = 'Redirecting to your dashboard...';
            if (spinnerSvg) spinnerSvg.style.display = 'none';
            if (checkmarkIcon) checkmarkIcon.style.display = 'block';

            setTimeout(() => {
                if (modal) {
                    modal.classList.remove('active');
                    setTimeout(() => { modal.style.display = 'none'; }, 300);
                }

                if (data.user) localStorage.setItem('auth_user', JSON.stringify(data.user));
                if (data.token) localStorage.setItem('intan_elyu_token', data.token);

                sessionStorage.setItem('show_onboarding', '1');
                sessionStorage.setItem('pending_reg_email', data.email || email);

                if (typeof showToast === 'function') showToast('Account created successfully! Welcome to Intan Elyu!');
                window.location.href = '?view=dashboard';
            }, 1800);

        } catch (error) {
            console.error('Register Error:', error);
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
            if (typeof showToast === 'function') {
                showToast(error.message, 'error', 4500);
            }
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
                btn.disabled = false;
            }
        }
    };

    window.hideOtpPanel = function(e) {
        if (e) e.preventDefault();
        tabsContainer.style.display = 'flex';
        wrapper.classList.remove('show-forgot', 'show-otp');
        wrapper.classList.add('show-register');
        tabLogin.classList.remove('active');
        tabRegister.classList.add('active');
        updateTitleWithTransition('Start your Journey');
    };

    window.handleOtpBoxInput = function(el, index) {
        el.value = el.value.replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.otp-box');
        if (el.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }
        checkAutoSubmitOtp();
    };

    window.handleOtpBoxKeydown = function(el, e, index) {
        const boxes = document.querySelectorAll('.otp-box');
        if (e.key === 'Backspace' && !el.value && index > 0) {
            boxes[index - 1].focus();
        }
    };

    window.handleOtpPaste = function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.otp-box');
        for (let i = 0; i < boxes.length && i < pasted.length; i++) {
            boxes[i].value = pasted[i];
        }
        if (pasted.length >= 6) {
            boxes[5].focus();
            checkAutoSubmitOtp();
        }
    };

    function checkAutoSubmitOtp() {
        const boxes = document.querySelectorAll('.otp-box');
        let code = '';
        boxes.forEach(b => code += b.value);
        if (code.length === 6) {
            const btn = document.getElementById('btn-otp');
            if (btn && !btn.disabled) {
                document.getElementById('form-otp').requestSubmit();
            }
        }
    }

    window.handleVerifyOtp = async function(e) {
        e.preventDefault();
        const email = document.getElementById('otp-target-email').textContent;
        const boxes = document.querySelectorAll('.otp-box');
        const otp = Array.from(boxes).map(b => b.value).join('');

        if (otp.length < 6) {
            if (typeof showToast === 'function') showToast('Please enter all 6 digits of your verification code.');
            return;
        }

        const btn = document.getElementById('btn-otp');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;

        if (window._pending2FALogin) {
            try {
                const response = await fetch(backendUrl + '/api/auth/login', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ 
                        email: window._pending2FALogin.email, 
                        password: window._pending2FALogin.password,
                        two_factor_code: otp
                    })
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || data.error || 'Invalid 2FA security code');
                }

                window._pending2FALogin = null;
                localStorage.setItem('auth_user', JSON.stringify(data.user));
                localStorage.setItem('intan_elyu_token', data.token);

                showLoginSuccessModal(data.user);
            } catch (error) {
                console.error('2FA Login Verification Error:', error);
                if (typeof showToast === 'function') showToast(error.message);
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
            return;
        }

        try {
            const response = await fetch(backendUrl + '/api/auth/verify-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, otp: otp })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Invalid verification code');
            }

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);

            showLoginSuccessModal(data.user);
        } catch (error) {
            console.error('OTP Verification Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    };

    let fpTimerInterval = null;

    function startFpResendTimer() {
        const resendBtn = document.getElementById('fp-resend-btn');
        const countdownText = document.getElementById('fp-countdown-text');
        const countdownSec = document.getElementById('fp-countdown-sec');
        if (!resendBtn || !countdownText || !countdownSec) return;

        let secondsLeft = 45;
        resendBtn.style.display = 'none';
        countdownText.style.display = 'inline';
        countdownSec.textContent = secondsLeft;

        if (fpTimerInterval) clearInterval(fpTimerInterval);
        fpTimerInterval = setInterval(() => {
            secondsLeft--;
            countdownSec.textContent = secondsLeft;
            if (secondsLeft <= 0) {
                clearInterval(fpTimerInterval);
                countdownText.style.display = 'none';
                resendBtn.style.display = 'inline';
            }
        }, 1000);
    }

    window.handleForgotPassword = async function(e) {
        if (e) e.preventDefault();
        const btn = document.getElementById('fp-btn');
        const oldHtml = btn ? btn.innerHTML : '';
        const email = (document.getElementById('fp-email')?.value || '').trim();

        if (!email) {
            if (typeof showToast === 'function') showToast('Please enter your email address.');
            return;
        }

        const modal = document.getElementById('login-success-modal');
        const titleEl = document.getElementById('login-modal-title');
        const subEl = document.getElementById('login-success-user-name');
        const spinnerSvg = document.getElementById('modal-spinner-svg');
        const checkmarkIcon = document.getElementById('modal-checkmark-icon');

        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
        }

        // Display loading modal with active CSS transition
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('active'); }, 10);
        }
        if (titleEl) titleEl.textContent = 'Sending Reset Code...';
        if (subEl) subEl.textContent = 'Please wait while we send the code to your email';
        if (spinnerSvg) spinnerSvg.style.display = 'block';
        if (checkmarkIcon) checkmarkIcon.style.display = 'none';

        const startTime = Date.now();

        try {
            const response = await fetch(backendUrl + '/api/auth/forgot-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email })
            });
            const data = await response.json();
            
            if (!response.ok) {
                if (data.is_google_user) {
                    if (modal) {
                        modal.classList.remove('active');
                        setTimeout(() => { modal.style.display = 'none'; }, 300);
                    }
                    if (typeof showToast === 'function') showToast(data.error || 'Please sign in with Google.', 'info');
                    setTimeout(() => {
                        window.hideForgotPassword();
                    }, 2000);
                    return;
                }
                throw new Error(data.error || data.message || 'Failed to send reset code.');
            }

            // Guarantee spinner stays visible for at least 5 seconds
            const elapsedTime = Date.now() - startTime;
            const minSpinnerTime = 5000;
            if (elapsedTime < minSpinnerTime) {
                await new Promise(r => setTimeout(r, minSpinnerTime - elapsedTime));
            }

            // Animate checkmark success in modal
            if (titleEl) titleEl.textContent = 'Reset Code Sent!';
            if (subEl) subEl.textContent = 'Check your email inbox or spam folder';
            if (spinnerSvg) spinnerSvg.style.display = 'none';
            if (checkmarkIcon) checkmarkIcon.style.display = 'block';

            setTimeout(() => {
                if (modal) {
                    modal.classList.remove('active');
                    setTimeout(() => { modal.style.display = 'none'; }, 300);
                }
                
                const targetEmailEl = document.getElementById('fp-target-email');
                if (targetEmailEl) targetEmailEl.textContent = data.email || email;

                document.getElementById('fp-form-state').style.display = 'none';
                document.getElementById('fp-password-state').style.display = 'none';
                document.getElementById('fp-code-state').style.display = 'block';
                if (typeof showToast === 'function') showToast('Security reset code sent to ' + (data.email || email));
                startFpResendTimer();
                
                const fpBoxes = document.querySelectorAll('.fp-otp-box');
                fpBoxes.forEach(b => b.value = '');
                if (fpBoxes[0]) fpBoxes[0].focus();
            }, 1800);

        } catch (error) {
            console.error('Forgot Password Error:', error);
            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => { modal.style.display = 'none'; }, 300);
            }
            if (typeof showToast === 'function') showToast(error.message);
        } finally {
            if (btn) {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }
    };

    window._verifiedFpOtp = '';

    window.handleFpBack = function(e) {
        if (e) e.preventDefault();
        const pwdState = document.getElementById('fp-password-state');
        const codeState = document.getElementById('fp-code-state');
        const formState = document.getElementById('fp-form-state');

        if (pwdState && pwdState.style.display === 'block') {
            pwdState.style.display = 'none';
            if (codeState) codeState.style.display = 'block';
        } else if (codeState && codeState.style.display === 'block') {
            codeState.style.display = 'none';
            if (formState) formState.style.display = 'block';
        } else {
            window.hideForgotPassword(e);
        }
    };

    window.handleFpOtpBoxInput = function(el, index) {
        el.value = el.value.replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.fp-otp-box');
        if (el.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }
    };

    window.handleFpOtpBoxKeydown = function(el, e, index) {
        const boxes = document.querySelectorAll('.fp-otp-box');
        if (e.key === 'Backspace' && !el.value && index > 0) {
            boxes[index - 1].focus();
        }
    };

    window.handleFpOtpPaste = function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.fp-otp-box');
        for (let i = 0; i < boxes.length && i < pasted.length; i++) {
            boxes[i].value = pasted[i];
        }
        if (pasted.length >= 6) {
            boxes[5].focus();
        }
    };

    window.handleResendFpEmail = function(e) {
        if (e) e.preventDefault();
        window.handleForgotPassword(null);
    };

    window.handleVerifyFpCode = async function(e) {
        if (e) e.preventDefault();
        const email = document.getElementById('fp-target-email').textContent || document.getElementById('fp-email').value;
        const boxes = document.querySelectorAll('.fp-otp-box');
        const otp = Array.from(boxes).map(b => b.value).join('');

        if (otp.length < 6) {
            if (typeof showToast === 'function') showToast('Please enter all 6 digits of your reset code.');
            return;
        }

        const btn = document.getElementById('fp-verify-btn');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
        }

        try {
            const response = await fetch(backendUrl + '/api/auth/validate-reset-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, otp: otp })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Invalid or expired code.');
            }

            window._verifiedFpOtp = otp;
            if (typeof showToast === 'function') showToast('Code verified! Please create your new password.', 'success');

            document.getElementById('fp-code-state').style.display = 'none';
            document.getElementById('fp-password-state').style.display = 'block';
            
            const newPwdInput = document.getElementById('fp-new-password');
            if (newPwdInput) {
                newPwdInput.value = '';
                newPwdInput.focus();
            }
            const confirmPwdInput = document.getElementById('fp-confirm-password');
            if (confirmPwdInput) confirmPwdInput.value = '';

        } catch (error) {
            console.error('Code validation error:', error);
            if (typeof showToast === 'function') showToast(error.message);
        } finally {
            if (btn) {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }
    };

    window.handleResetPasswordSubmit = async function(e) {
        if (e) e.preventDefault();
        const email = document.getElementById('fp-target-email').textContent || document.getElementById('fp-email').value;
        const otp = window._verifiedFpOtp || Array.from(document.querySelectorAll('.fp-otp-box')).map(b => b.value).join('');
        const newPassword = document.getElementById('fp-new-password').value;
        const confirmPassword = document.getElementById('fp-confirm-password').value;

        if (!otp || otp.length < 6) {
            if (typeof showToast === 'function') showToast('Security code missing. Please verify code again.');
            document.getElementById('fp-password-state').style.display = 'none';
            document.getElementById('fp-code-state').style.display = 'block';
            return;
        }

        if (newPassword !== confirmPassword) {
            if (typeof showToast === 'function') showToast('Passwords do not match. Please verify.');
            return;
        }

        if (newPassword.length < 8) {
            if (typeof showToast === 'function') showToast('Password must be at least 8 characters long.');
            return;
        }

        const modal = document.getElementById('login-success-modal');
        const titleEl = document.getElementById('login-modal-title');
        const subEl = document.getElementById('login-success-user-name');
        const spinnerSvg = document.getElementById('modal-spinner-svg');
        const checkmarkIcon = document.getElementById('modal-checkmark-icon');

        const btn = document.getElementById('fp-password-btn');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
        }

        // Show full-screen loading modal
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('active'); }, 10);
        }
        if (titleEl) titleEl.textContent = 'Resetting Password...';
        if (subEl) subEl.textContent = 'Updating your account credentials';
        if (spinnerSvg) spinnerSvg.style.display = 'block';
        if (checkmarkIcon) checkmarkIcon.style.display = 'none';

        const startTime = Date.now();

        try {
            const response = await fetch(backendUrl + '/api/auth/reset-password-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    email: email,
                    otp: otp,
                    password: newPassword,
                    password_confirmation: confirmPassword
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Failed to reset password.');
            }

            const elapsedTime = Date.now() - startTime;
            const minSpinnerTime = 3000;
            if (elapsedTime < minSpinnerTime) {
                await new Promise(r => setTimeout(r, minSpinnerTime - elapsedTime));
            }

            // Animate checkmark success in modal
            if (titleEl) titleEl.textContent = 'Password Changed Successfully!';
            if (subEl) subEl.textContent = 'Please log in with your new credentials';
            if (spinnerSvg) spinnerSvg.style.display = 'none';
            if (checkmarkIcon) checkmarkIcon.style.display = 'block';

            setTimeout(() => {
                if (modal) {
                    modal.classList.remove('active');
                    setTimeout(() => { modal.style.display = 'none'; }, 300);
                }

                // Clear and reset forgot password form fields and state
                document.getElementById('fp-new-password').value = '';
                document.getElementById('fp-confirm-password').value = '';
                const fpEmailEl = document.getElementById('fp-email');
                if (fpEmailEl) fpEmailEl.value = '';
                window._verifiedFpOtp = '';
                const fpBoxes = document.querySelectorAll('.fp-otp-box');
                fpBoxes.forEach(b => b.value = '');

                const formState = document.getElementById('fp-form-state');
                const codeState = document.getElementById('fp-code-state');
                const pwdState = document.getElementById('fp-password-state');
                if (formState) formState.style.display = 'block';
                if (codeState) codeState.style.display = 'none';
                if (pwdState) pwdState.style.display = 'none';

                if (btn) {
                    btn.innerHTML = oldHtml;
                    btn.disabled = false;
                }

                // Switch back to Login Credentials tab / panel
                window.hideForgotPassword();

                // Pre-fill email in Login Credentials
                const loginEmailEl = document.getElementById('login-email');
                if (loginEmailEl && email) {
                    loginEmailEl.value = email;
                }
                const loginPwdEl = document.getElementById('login-password');
                if (loginPwdEl) {
                    loginPwdEl.value = '';
                    loginPwdEl.focus();
                }

                if (typeof showToast === 'function') {
                    showToast('Password changed successfully! Please log in with your new credentials.', 'success');
                }
            }, 1500);

        } catch (error) {
            console.error('Reset Password OTP Error:', error);
            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => { modal.style.display = 'none'; }, 300);
            }
            if (typeof showToast === 'function') showToast(error.message);
            if (btn) {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }
    };

    window.triggerGoogleLogin = function(event) {
        if (event && event.preventDefault) event.preventDefault();

        if (typeof window.showGoogleOAuthModal === 'function') {
            window.showGoogleOAuthModal('Connecting to Google...', 'Redirecting to Google account selection...');
        }

        const googleBtns = document.querySelectorAll('.btn-google');
        googleBtns.forEach((btn) => {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Connecting to Google...';
            btn.disabled = true;
        });

        const clientId = window.GOOGLE_CLIENT_ID || localStorage.getItem('intan_elyu_google_client_id') || '620598190857-37a0ucobfd4b3rct7ofti8rtvl3qt884.apps.googleusercontent.com';

        function resetBtns() {
            if (typeof window.hideGoogleOAuthModal === 'function') {
                window.hideGoogleOAuthModal();
            }
            googleBtns.forEach(btn => {
                btn.innerHTML = '<span>Sign in with Google</span>';
                btn.disabled = false;
            });
        }

        if (!clientId) {
            if (typeof showToast === 'function') showToast('Google Client ID is required to connect to Google Cloud.');
            resetBtns();
            return;
        }

        // Direct Google OAuth 2.0 Authorization Redirection (Universal for Android APK, WebViews & Web)
        let redirectUri = window.location.origin + window.location.pathname;
        if (!redirectUri.endsWith('.php') && !redirectUri.endsWith('/')) {
            redirectUri += '/index.php';
        } else if (redirectUri.endsWith('/')) {
            redirectUri += 'index.php';
        }

        const stateObj = { timestamp: Date.now(), returnView: 'auth' };
        const stateStr = encodeURIComponent(JSON.stringify(stateObj));
        const googleAuthUrl = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${encodeURIComponent(clientId)}&redirect_uri=${encodeURIComponent(redirectUri)}&response_type=token&scope=email%20profile%20openid&prompt=select_account&state=${stateStr}`;
        
        window.location.href = googleAuthUrl;
    };

    window.handleCredentialResponse = async function(response, onDone) {
        const googleBtns = document.querySelectorAll('.btn-google');
        try {
            let payloadData = {};
            if (response.credential) {
                payloadData = { credential: response.credential };
            } else if (response.profile) {
                payloadData = {
                    email: response.profile.email,
                    name: response.profile.name,
                    google_id: 'g_' + response.profile.sub,
                    avatar: response.profile.picture
                };
            }

            const backend = (typeof window.getBackendUrl === 'function') ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online');
            const fetchRes = await fetch(backend + '/api/auth/google', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payloadData)
            });
            const text = await fetchRes.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Non-JSON response from Google auth endpoint:', text.substring(0, 200));
                throw new Error('Invalid response from server. Please try again.');
            }
            
            if (!fetchRes.ok) {
                throw new Error(data.message || data.error || 'Google login failed');
            }
            
            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            if (window.AppStorage) {
                window.AppStorage.setItem('auth_user', data.user);
                window.AppStorage.setItem('intan_elyu_token', data.token);
            }
            
            showLoginSuccessModal(data.user);
        } catch (error) {
            console.error('Google Auth Error:', error);
            if (typeof onDone === 'function') onDone();
            else {
                googleBtns.forEach(btn => {
                    btn.innerHTML = '<span>Sign in with Google</span>';
                    btn.disabled = false;
                });
            }
            if (typeof showToast === 'function') showToast(error.message);
            if (typeof window.openAuthCancelModal === 'function') {
                window.openAuthCancelModal(error.message || 'Google sign-in could not be completed.');
            }
        }
    };

    (function checkGoogleOAuthRedirect() {
        if (typeof window.initGoogleOAuthHandler === 'function') {
            window.initGoogleOAuthHandler();
        }
    })();

    (function checkPrefillEmail() {
        const params = new URLSearchParams(window.location.search);
        const prefillEmail = sessionStorage.getItem('login_prefill_email') || params.get('email') || localStorage.getItem('intan_elyu_remembered_email');
        if (prefillEmail) {
            const loginEmailEl = document.getElementById('login-email');
            if (loginEmailEl) {
                loginEmailEl.value = prefillEmail;
                const loginPwdEl = document.getElementById('login-password');
                if (loginPwdEl && !loginPwdEl.value) loginPwdEl.focus();
            }
            if (sessionStorage.getItem('login_prefill_email')) {
                sessionStorage.removeItem('login_prefill_email');
            }
        }
        const rememberMeSaved = localStorage.getItem('intan_elyu_remember_me');
        const rememberCheckEl = document.getElementById('login-remember');
        if (rememberCheckEl && rememberMeSaved === '1') {
            rememberCheckEl.checked = true;
        }
    })();

    // Lock scroll on login page to ensure completely fixed layout
    (function preventLoginScroll() {
        document.addEventListener('touchmove', function(e) {
            const wrapper = document.getElementById('forms-wrapper');
            if (wrapper && !wrapper.classList.contains('show-register') && !wrapper.classList.contains('show-forgot') && !wrapper.classList.contains('show-otp')) {
                // On login page - prevent bounce/scrolling
                if (!e.target.closest('.privacy-modal-card')) {
                    e.preventDefault();
                }
            }
        }, { passive: false });
    })();

    // Freeze and lock Auth viewport height to prevent keyboard squeezing
    (function freezeAuthLayout() {
        function applyLockedDimensions() {
            var winH = window.innerHeight || 0;
            var scrH = (window.screen && window.screen.height) ? window.screen.height : 0;
            var storedH = parseInt(sessionStorage.getItem('auth_locked_screen_h') || '0', 10);
            if (!storedH || winH > storedH) {
                storedH = Math.max(winH, scrH > 300 ? scrH : winH);
                try { sessionStorage.setItem('auth_locked_screen_h', storedH); } catch(e) {}
            }
            if (storedH > 0) {
                var topH = Math.min(330, Math.max(250, Math.round(storedH * 0.38)));
                document.documentElement.style.setProperty('--auth-screen-h', storedH + 'px');
                document.documentElement.style.setProperty('--auth-top-h', topH + 'px');
                var container = document.querySelector('.auth-container');
                if (container) {
                    container.style.height = storedH + 'px';
                    container.style.minHeight = storedH + 'px';
                    container.style.maxHeight = storedH + 'px';
                }
                var topEl = document.querySelector('.auth-top');
                if (topEl) {
                    topEl.style.height = topH + 'px';
                    topEl.style.minHeight = topH + 'px';
                    topEl.style.maxHeight = topH + 'px';
                }
            }
        }

        applyLockedDimensions();
        window.addEventListener('resize', function() {
            var currentH = window.innerHeight || 0;
            var storedH = parseInt(sessionStorage.getItem('auth_locked_screen_h') || '0', 10);
            if (currentH > storedH) {
                applyLockedDimensions();
            }
        });
        window.addEventListener('orientationchange', function() {
            try { sessionStorage.removeItem('auth_locked_screen_h'); } catch(e) {}
            setTimeout(applyLockedDimensions, 250);
        });
    })();
</script>
