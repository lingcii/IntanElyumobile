<!-- Auth View (Login, Register & Forgot Password) -->
<script>
    (function () {
        var winH = window.innerHeight || 0;
        var viewportH = window.visualViewport ? window.visualViewport.height : winH;
        var h = Math.max(winH, viewportH || 0);
        if (h > 0) {
            document.documentElement.style.setProperty('--auth-screen-h', h + 'px');
        }
    })();
</script>

<div class="auth-container">
    <!-- Top Blue Section -->
    <div class="auth-top">
        <div class="logo-container">
            <img id="auth-logo-img" src="assets/img/logo.png" alt="Intan Elyu Logo"
                style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%; display: block;">
        </div>
        <h1 id="auth-title" style="color: #ffffff; font-weight: 800;">Welcome to <span>Elyu</span></h1>
        <p id="auth-subtitle" class="auth-subtitle">Explore places. Create memories.<br>Discover La Union.</p>

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
                <path class="wave-layer wave-1" fill="url(#authWaveGrad1)"
                    d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,160 L0,160 Z">
                </path>
                <path class="wave-layer wave-2" fill="url(#authWaveGrad2)"
                    d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,160 L0,160 Z">
                </path>
                <path class="wave-layer wave-3" fill="#74a3cf"
                    d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,160 L0,160 Z">
                </path>
            </svg>
        </div>
    </div>

    <!-- Bottom White Section -->
    <div class="auth-bottom">

        <!-- Offline Mode Status Indicator -->
        <div id="auth-offline-badge" class="auth-offline-badge" style="display: none;">
            <i class="fa-solid fa-cloud-slash" style="color: #f59e0b; font-size: 11px;"></i>
            <span>Offline Mode Active &bull; Saved Guides Available</span>
        </div>

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
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="login-email" class="auth-input" placeholder="Email Address" required
                            autocomplete="email" inputmode="email" enterkeyhint="next" data-auth-focus-order="1">
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="login-password" class="auth-input" placeholder="Password" required
                            autocomplete="current-password" enterkeyhint="done" data-auth-focus-order="2">
                        <i class="fa-regular fa-eye password-toggle"
                            onclick="togglePasswordVisibility('login-password', this)"></i>
                    </div>
                    <div class="auth-options-row">
                        <label class="remember-me-label" for="login-remember">
                            <input type="checkbox" id="login-remember" class="custom-terms-checkbox">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-pwd" onclick="showForgotPassword(event)">Forgot Password?</a>
                    </div>

                    <button type="submit" id="btn-login" class="btn-circle-submit" data-label="Log In">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-social-section">
                    <div style="width:100%; display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.25);">
                        <span
                            style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:1px;">Or
                            Continue With</span>
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.25);">
                    </div>
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin(event)"
                        style="width:100%; padding:10.5px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:13.5px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
                        <svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;">
                            <path fill="#4285F4"
                                d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.53-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-8.7c0-.18-.01-.35-.05-.47z" />
                            <path fill="#34A853"
                                d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.11 0-5.74-2.11-6.68-4.96H1.21v3.15C3.18 21.88 7.31 24 12 24z" />
                            <path fill="#FBBC05"
                                d="M5.32 14.24A7.16 7.16 0 0 1 5 12c0-.79.13-1.57.32-2.31V6.54H1.21A11.96 11.96 0 0 0 0 12c0 1.92.45 3.74 1.21 5.38l4.11-3.14z" />
                            <path fill="#EA4335"
                                d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.18 2.12 1.21 5.46l4.11 3.22c.94-2.85 3.57-4.93 6.68-4.93z" />
                        </svg>
                        <span>Sign in with Google</span>
                    </button>
                    <button type="button" id="btn-offline-mode" class="btn-offline-guest" onclick="window.enterOfflineMode(event)"
                        style="background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1 !important; box-shadow: 0 4px 14px rgba(0,0,0,0.08) !important;">
                        <i class="fa-regular fa-user" style="color: #1452d6; font-size: 16px;"></i>
                        <span style="color: #0f172a; font-weight: 700;">Continue as Guest</span>
                    </button>
                    <p class="auth-switch-prompt">
                        Don't have an account? <a href="#" onclick="toggleAuthMode(true); return false;"
                            class="auth-switch-link">Register now</a>
                    </p>
                </div>
            </div>

            <!-- Panel 2: Register -->
            <div class="form-panel register-form">
                <form id="form-register" onsubmit="handleRegisterSubmit(event)">
                    <div class="input-group">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="reg-first-name" class="auth-input" placeholder="First Name" required
                            autocomplete="given-name" enterkeyhint="next" data-auth-focus-order="1"
                            oninput="validateRegisterFormInline()">
                    </div>
                    <div class="input-group">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="reg-last-name" class="auth-input" placeholder="Last Name" required
                            autocomplete="family-name" enterkeyhint="next" data-auth-focus-order="2"
                            oninput="validateRegisterFormInline()">
                    </div>
                    <div class="input-group" id="reg-gender-trigger" onclick="openGenderModal(event)" style="cursor: pointer;">
                        <i class="fa-solid fa-venus-mars"></i>
                        <input type="text" id="reg-gender-display" class="auth-input" placeholder="Select Sex / Gender" readonly style="cursor: pointer; background: transparent; caret-color: transparent; -webkit-user-select: none; user-select: none;" onclick="openGenderModal(event)">
                        <input type="hidden" id="reg-gender" value="">
                        <i class="fa-solid fa-chevron-down select-arrow" id="reg-gender-arrow"></i>
                    </div>
                    <div class="input-group">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="reg-email" class="auth-input" placeholder="Email Address" required
                            autocomplete="email" inputmode="email" enterkeyhint="next" data-auth-focus-order="3"
                            oninput="validateRegisterFormInline()">
                        <i id="reg-email-status-icon" class="fa-solid field-status-icon"></i>
                    </div>
                    <div id="reg-email-hint" class="input-field-hint" style="display: none;"></div>

                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="reg-password" class="auth-input" placeholder="Create Password"
                            autocomplete="new-password" enterkeyhint="done" data-auth-focus-order="4" required oninput="validateRegisterFormInline()"
                            onfocus="onPasswordFocus()" onblur="onPasswordBlur()">
                        <i id="reg-password-status-icon" class="fa-solid field-status-icon password-offset"></i>
                        <i class="fa-regular fa-eye password-toggle"
                            onclick="togglePasswordVisibility('reg-password', this)"></i>
                    </div>
                    <div id="pwd-strength-container" class="pwd-strength-wrapper"
                        style="display: none;" data-score="0">
                        <div class="pwd-strength-card">
                            <div class="pwd-strength-header">
                                <div class="pwd-strength-badge status-weak" id="pwd-strength-badge">
                                    <i class="fa-solid fa-shield-halved" id="pwd-strength-icon"></i>
                                    <span id="pwd-strength-label">Password Strength: Weak</span>
                                </div>
                                <span id="pwd-strength-score" class="pwd-score-pill">0/4</span>
                            </div>
                            <div class="pwd-strength-segments">
                                <div class="pwd-segment seg-1"></div>
                                <div class="pwd-segment seg-2"></div>
                                <div class="pwd-segment seg-3"></div>
                                <div class="pwd-segment seg-4"></div>
                            </div>
                            <div class="pwd-checklist">
                                <span id="chk-len8" class="pwd-chk-item"><i class="fa-solid fa-circle-dot"></i> 8+ characters</span>
                                <span id="chk-num" class="pwd-chk-item"><i class="fa-solid fa-circle-dot"></i> A number</span>
                                <span id="chk-cap" class="pwd-chk-item"><i class="fa-solid fa-circle-dot"></i> A capital letter</span>
                                <span id="chk-sym" class="pwd-chk-item"><i class="fa-solid fa-circle-dot"></i> A symbol</span>
                            </div>
                        </div>
                    </div>

                    <div class="terms-agreement-row" style="position: relative; z-index: 10; pointer-events: auto !important;">
                        <input type="checkbox" id="reg-privacy-checkbox" class="custom-terms-checkbox" style="pointer-events: auto !important; cursor: pointer;">
                        <div class="terms-agreement-text" style="pointer-events: auto !important;">
                            <label for="reg-privacy-checkbox" id="reg-privacy-label" class="terms-agreement-label" style="pointer-events: auto !important; cursor: pointer;">I agree to the</label>
                            <button type="button" id="link-terms-privacy" onclick="openPrivacyPolicyModal(event)"
                                class="terms-policy-btn" style="position: relative; z-index: 12; pointer-events: auto !important; cursor: pointer; touch-action: manipulation;">Terms &amp; Privacy Policy</button><span
                                class="terms-period">.</span>
                        </div>
                    </div>

                    <button type="submit" id="btn-register" class="btn-circle-submit" data-label="Create Account">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-social-section">
                    <div style="width:100%; display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                        <span
                            style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px;">Or
                            Continue With</span>
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                    </div>
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin(event)"
                        style="width:100%; padding:10.5px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:13.5px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
                        <svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;">
                            <path fill="#4285F4"
                                d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.53-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-8.7c0-.18-.01-.35-.05-.47z" />
                            <path fill="#34A853"
                                d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.11 0-5.74-2.11-6.68-4.96H1.21v3.15C3.18 21.88 7.31 24 12 24z" />
                            <path fill="#FBBC05"
                                d="M5.32 14.24A7.16 7.16 0 0 1 5 12c0-.79.13-1.57.32-2.31V6.54H1.21A11.96 11.96 0 0 0 0 12c0 1.92.45 3.74 1.21 5.38l4.11-3.14z" />
                            <path fill="#EA4335"
                                d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.18 2.12 1.21 5.46l4.11 3.22c.94-2.85 3.57-4.93 6.68-4.93z" />
                        </svg>
                        <span>Sign up with Google</span>
                    </button>
                    <button type="button" class="btn-offline-guest" onclick="window.enterOfflineMode(event)"
                        style="background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1 !important; box-shadow: 0 4px 14px rgba(0,0,0,0.08) !important;">
                        <i class="fa-regular fa-user" style="color: #1452d6; font-size: 16px;"></i>
                        <span style="color: #0f172a; font-weight: 700;">Continue as Guest</span>
                    </button>
                    <p class="auth-switch-prompt">
                        Already have an account? <a href="#" onclick="toggleAuthMode(false); return false;"
                            class="auth-switch-link">Log in now</a>
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
                    <h3 style="margin: 0 0 8px 0; color: #ffffff; font-size: 20px; font-weight: 800;">Reset Code Sent
                    </h3>
                    <p
                        style="color: rgba(255, 255, 255, 0.95); font-size: 13.5px; margin: 0 0 20px 0; font-weight: 500; line-height: 1.5;">
                        We sent a 6-digit reset code to <br><strong id="fp-target-email"
                            style="color: #ffffff; font-weight: 800; font-size: 14px; background: rgba(0, 0, 0, 0.18); padding: 3px 10px; border-radius: 6px; display: inline-block; margin-top: 5px;"></strong>
                    </p>

                    <form id="form-fp-verify-code" onsubmit="handleVerifyFpCode(event)">
                        <!-- 6 Individual Digit Input Boxes -->
                        <div class="otp-boxes-container" style="margin: 18px 0 20px 0;">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric"
                                pattern="[0-9]*" autocomplete="one-time-code" oninput="handleFpOtpBoxInput(this, 0)"
                                onkeydown="handleFpOtpBoxKeydown(this, event, 0)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric"
                                pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 1)"
                                onkeydown="handleFpOtpBoxKeydown(this, event, 1)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric"
                                pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 2)"
                                onkeydown="handleFpOtpBoxKeydown(this, event, 2)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric"
                                pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 3)"
                                onkeydown="handleFpOtpBoxKeydown(this, event, 3)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric"
                                pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 4)"
                                onkeydown="handleFpOtpBoxKeydown(this, event, 4)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric"
                                pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 5)"
                                onkeydown="handleFpOtpBoxKeydown(this, event, 5)" onpaste="handleFpOtpPaste(event)">
                        </div>

                        <button type="submit" id="fp-verify-btn" class="btn-circle-submit" style="margin-bottom: 14px;">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                        <div
                            style="font-size: 13px; color: rgba(255, 255, 255, 0.95); margin-top: 14px; font-weight: 500;">
                            Didn't receive email?
                            <button type="button" id="fp-resend-btn" onclick="handleResendFpEmail(event)"
                                style="background: none; border: none; color: #ffffff; font-weight: 800; cursor: pointer; text-decoration: underline; padding: 0;">
                                Resend Code
                            </button>
                            <span id="fp-countdown-text" style="display: none; color: #fef08a; font-weight: 700;">
                                (Resend in <span id="fp-countdown-sec">45</span>s)</span>
                        </div>
                    </form>
                </div>

                <!-- Step 3: Enter New Password -->
                <div id="fp-password-state" style="display: none; text-align: center; padding: 10px 0;">
                    <h3 style="margin: 0 0 6px 0; color: white; font-size: 19px; font-weight: 800;">Create New Password
                    </h3>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 13px; margin: 0 0 20px 0; font-weight: 500;">
                        Enter your new password below.</p>

                    <form id="form-fp-password" onsubmit="handleResetPasswordSubmit(event)">
                        <div class="input-group" style="margin-bottom: 16px;">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="fp-new-password" class="auth-input"
                                placeholder="New Password (min 8 chars)" minlength="8" required>
                            <i class="fa-regular fa-eye password-toggle"
                                onclick="togglePasswordVisibility('fp-new-password', this)"></i>
                        </div>
                        <div class="input-group" style="margin-bottom: 20px;">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="fp-confirm-password" class="auth-input"
                                placeholder="Confirm New Password" minlength="8" required>
                            <i class="fa-regular fa-eye password-toggle"
                                onclick="togglePasswordVisibility('fp-confirm-password', this)"></i>
                        </div>

                        <button type="submit" id="fp-password-btn" class="btn-circle-submit"
                            style="margin-bottom: 10px;">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel 4: Email OTP Verification -->
            <div class="form-panel otp-form" id="panel-otp">
                <a href="#" class="back-link otp-back-link" onclick="hideOtpPanel(event)">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>

                <div class="otp-panel-header">
                    <div class="otp-panel-icon">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                    </div>
                    <h3>Enter Verification Code</h3>
                    <p>We sent a secure 6-digit code to<br><strong id="otp-target-email"></strong></p>
                </div>

                <form id="form-otp" onsubmit="handleVerifyOtp(event)">
                    <!-- 6 Individual Digit Input Boxes -->
                    <div class="otp-boxes-container">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            autocomplete="one-time-code" oninput="handleOtpBoxInput(this, 0)"
                            onkeydown="handleOtpBoxKeydown(this, event, 0)" onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            oninput="handleOtpBoxInput(this, 1)" onkeydown="handleOtpBoxKeydown(this, event, 1)"
                            onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            oninput="handleOtpBoxInput(this, 2)" onkeydown="handleOtpBoxKeydown(this, event, 2)"
                            onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            oninput="handleOtpBoxInput(this, 3)" onkeydown="handleOtpBoxKeydown(this, event, 3)"
                            onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            oninput="handleOtpBoxInput(this, 4)" onkeydown="handleOtpBoxKeydown(this, event, 4)"
                            onpaste="handleOtpPaste(event)">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            oninput="handleOtpBoxInput(this, 5)" onkeydown="handleOtpBoxKeydown(this, event, 5)"
                            onpaste="handleOtpPaste(event)">
                    </div>

                    <button type="submit" id="btn-otp" class="btn-circle-submit otp-submit-btn" data-label="Verify Code">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<button type="button" id="auth-keyboard-dismiss" class="auth-keyboard-dismiss"
    aria-label="Dismiss keyboard" hidden>
    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
</button>

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

<!-- Registration email verification result modals -->
<div id="otp-success-modal" class="auth-result-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="otp-success-title">
    <div class="auth-result-card auth-result-card--success">
        <div class="auth-result-icon"><i class="fa-solid fa-check"></i></div>
        <h2 id="otp-success-title">Email Verified!</h2>
        <p>Your account was created successfully. Taking you to your dashboard...</p>
        <div class="auth-result-progress"><span></span></div>
    </div>
</div>

<div id="otp-invalid-modal" class="auth-result-overlay" style="display:none;" role="alertdialog" aria-modal="true" aria-labelledby="otp-invalid-title">
    <div class="auth-result-card auth-result-card--invalid">
        <button type="button" class="auth-result-close" onclick="closeOtpInvalidModal()" aria-label="Close invalid code message"><i class="fa-solid fa-xmark"></i></button>
        <div class="auth-result-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h2 id="otp-invalid-title">Invalid Code</h2>
        <p id="otp-invalid-message">The verification code is incorrect or expired. Please check your email and try again.</p>
        <button type="button" class="auth-result-action" onclick="closeOtpInvalidModal()">Try Again</button>
    </div>
</div>



<!-- Google Auth Cancelled / Exit Modal -->
<div id="auth-cancel-modal" class="auth-2fa-overlay" style="display: none;">
    <div class="auth-2fa-card">
        <button type="button" class="auth-2fa-close" onclick="closeAuthCancelModal()"><i
                class="fa-solid fa-xmark"></i></button>

        <div class="auth-2fa-icon-ring"
            style="border-color: #f59e0b; color: #f59e0b; background: rgba(245, 158, 11, 0.15); box-shadow: 0 0 22px rgba(245, 158, 11, 0.35);">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h3 class="auth-2fa-title">Sign-In Cancelled</h3>

        <p class="auth-2fa-desc" id="auth-cancel-modal-msg">
            You exited the Google account chooser without logging in or signing up. Please choose an account to continue
            or sign in with your email.
        </p>

        <div class="auth-2fa-alert-box" style="margin-bottom: 20px;">
            <i class="fa-solid fa-shield-halved"
                style="color: #f59e0b; font-size: 14px; margin-right: 8px; flex-shrink: 0;"></i>
            <span>No changes were made to your account.</span>
        </div>

        <button type="button" onclick="closeAuthCancelModal()" class="auth-2fa-btn-primary"
            style="background: linear-gradient(135deg, #38bdf8, #2563eb);">
            <i class="fa-solid fa-check" style="margin-right: 8px;"></i>Got it
        </button>
    </div>
</div>

<!-- Two-Factor Authentication Login Verification Modal -->
<div id="auth-login-2fa-modal" class="auth-2fa-overlay" style="display: none;" onclick="if(event.target===this) closeAuthLogin2FAModal();">
    <div class="auth-2fa-card"
        style="background: #ffffff !important; border: none !important; outline: none !important; color: #0f172a !important; border-radius: 24px; padding: 0 !important; max-width: 385px; width: 92%; max-height: 88vh; display: flex; flex-direction: column; overflow: hidden; text-align: center; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.45); box-sizing: border-box; position: relative;">
        
        <!-- Modal Top Header Banner (Royal Blue) with Exit Button on Right -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 14px 18px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
            <div style="text-align: left;">
                <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-shield-halved" style="color: #00f2fe; font-size: 15px;"></i> Two-Factor Verification
                </h4>
                <p style="margin: 2px 0 0 0; font-size: 11px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">
                    Account Security Check
                </p>
            </div>
            <button type="button" onclick="closeAuthLogin2FAModal()" aria-label="Close"
                style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: 12px; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 14px;"></i>
            </button>
        </div>

        <!-- Middle Body Area (Pure White Background) -->
        <div class="hide-scrollbar" style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 22px 20px 18px 20px; text-align: center; color: #0f172a !important; box-sizing: border-box;">
            <div class="auth-2fa-icon-ring"
                style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff !important; color: #1e3a8a !important; border: 1.5px solid #dbeafe !important; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.12) !important;">
                <i class="fa-solid fa-shield-halved" style="color: #1e3a8a !important; font-size: 28px;"></i>
            </div>

            <h3 class="auth-2fa-title" style="color: #0f172a !important; text-shadow: none !important; font-size: 18px; font-weight: 800; margin: 0 0 6px 0; letter-spacing: -0.3px;">Security Code Required</h3>

            <p class="auth-2fa-desc" style="color: #64748b !important; font-size: 13px; margin-bottom: 16px; line-height: 1.5;">
                Enter the 6-digit security code sent to:<br>
                <strong id="auth-2fa-target-email" style="color: #0284c7; font-size: 13.5px; font-weight: 800;">your email</strong>
            </p>

            <!-- 6 Segmented Digit Input Boxes -->
            <div id="auth-login-2fa-boxes" style="display: flex; justify-content: center; gap: 8px; margin-bottom: 16px;">
                <input type="text" class="auth-login-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" oninput="handleLogin2FAOtpInput(this, 0)" onkeydown="handleLogin2FAOtpKeydown(this, event, 0)" onpaste="handleLogin2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                <input type="text" class="auth-login-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleLogin2FAOtpInput(this, 1)" onkeydown="handleLogin2FAOtpKeydown(this, event, 1)" onpaste="handleLogin2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                <input type="text" class="auth-login-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleLogin2FAOtpInput(this, 2)" onkeydown="handleLogin2FAOtpKeydown(this, event, 2)" onpaste="handleLogin2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                <input type="text" class="auth-login-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleLogin2FAOtpInput(this, 3)" onkeydown="handleLogin2FAOtpKeydown(this, event, 3)" onpaste="handleLogin2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                <input type="text" class="auth-login-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleLogin2FAOtpInput(this, 4)" onkeydown="handleLogin2FAOtpKeydown(this, event, 4)" onpaste="handleLogin2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
                <input type="text" class="auth-login-2fa-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleLogin2FAOtpInput(this, 5)" onkeydown="handleLogin2FAOtpKeydown(this, event, 5)" onpaste="handleLogin2FAOtpPaste(event)" onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'" style="width: 44px; height: 50px; text-align: center; font-size: 22px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc; color: #0f172a; outline: none; transition: all 0.2s;">
            </div>

            <div style="margin-bottom: 6px; font-size: 12px; color: #64748b; font-weight: 600;">
                Didn't receive the code? 
                <span id="auth-2fa-resend-btn" onclick="resendLogin2FACode()" style="color: #0284c7; font-weight: 800; cursor: pointer; text-decoration: underline;">Resend Code</span>
                <span id="auth-2fa-timer" style="display: none; color: #94a3b8; font-weight: 700;"></span>
            </div>
        </div>

        <!-- Locked Bottom Footer Banner (Royal Blue) for Verify Action -->
        <div id="auth-login-2fa-footer"
            style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 14px 18px; border: none !important; outline: none !important; display: flex; flex-direction: column; gap: 8px; box-sizing: border-box;">
            <button type="button" id="btn-submit-login-2fa" onclick="submitLogin2FA()"
                style="width: 100%; padding: 13px 20px; border: none !important; outline: none !important; border-radius: 100px; background: #ffffff !important; color: #1e3a8a !important; font-size: 14px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-lock-open" style="color: #1e3a8a !important;"></i> Verify &amp; Log In
            </button>
        </div>
    </div>
</div>

<div id="privacy-policy-modal" class="auth-2fa-overlay" style="display: none;"
    onclick="if(event.target===this) closePrivacyPolicyModal(event);">
    <div class="privacy-modal-card">
        <div class="privacy-modal-header">
            <div class="privacy-modal-header-content">
                <h3 class="privacy-modal-title">Terms &amp; Privacy Policy</h3>
                <span class="privacy-modal-badge">RA 10173 Compliant</span>
            </div>
            <button type="button" class="privacy-modal-close" onclick="closePrivacyPolicyModal(event)" aria-label="Close">
                &times;
            </button>
        </div>

        <div id="privacy-modal-scroll-body" class="privacy-modal-scroll-body">
            <div class="policy-welcome-banner">
                <p>
                    Welcome to <strong>Intan Elyu Tourism Management System</strong>. In strict
                    compliance with the <strong>Data Privacy Act of 2012 (Republic Act No.
                        10173)</strong>, please review our terms of service and personal data protection practices
                    before activating your account:
                </p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <span class="policy-item-title">1. Account &amp; Registration</span>
                </div>
                <p class="policy-item-desc">By registering, you confirm that personal details provided (Full Name,
                    Email) are accurate and belong to you. You are responsible for safeguarding your credentials.</p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <span class="policy-item-title">2. Information &amp; Encryption</span>
                </div>
                <p class="policy-item-desc">We store your name, email, and Bcrypt-encrypted password hashes to
                    personalize your Elyu itinerary. We never sell or share your data with unauthorized third parties.
                </p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <span class="policy-item-title">3. Location &amp; Fair Play Points</span>
                </div>
                <p class="policy-item-desc">Device location is accessed strictly during active tourist spot check-ins to
                    verify Points rewards and badge unlocks. We do not track your location in the background.</p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <span class="policy-item-title">4. 2-Factor Email Security (2FA)</span>
                </div>
                <p class="policy-item-desc">After accepting these terms, a 6-digit verification code will be dispatched
                    to your email address to confirm identity before account activation.</p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <span class="policy-item-title">5. Responsible Tourism</span>
                </div>
                <p class="policy-item-desc">As a registered tourist on Intan Elyu, you agree to respect local La Union
                    heritage, avoid littering, preserve coastal beaches, and follow local municipal guidelines.</p>
            </div>

            <div class="policy-item-card">
                <div class="policy-item-header">
                    <span class="policy-item-title">6. Data Subject Rights (RA 10173)</span>
                </div>
                <p class="policy-item-desc">Under Republic Act No. 10173, you retain the Right to be Informed, Right to
                    Access, Right to Object, and Right to Erasure. Your email and account credentials remain strictly
                    confidential, and you may request account erasure at any time.</p>
            </div>
        </div>

        <div class="privacy-modal-footer" style="pointer-events: auto !important;">
            <div class="privacy-acceptance-box" id="box-chk-accept-privacy" onclick="window.togglePrivacyCheckboxDirect(event)" style="cursor: pointer; pointer-events: auto !important; touch-action: manipulation;">
                <input type="checkbox" id="chk-accept-privacy" class="custom-terms-checkbox" onchange="window.syncPrivacyCheckbox(this.checked)" style="pointer-events: auto !important; cursor: pointer;">
                <label for="chk-accept-privacy" id="lbl-chk-accept-privacy" style="cursor: pointer; pointer-events: auto !important;">
                    I have read, understood, and accept the Terms &amp; Privacy Policy.
                </label>
            </div>

            <div class="privacy-modal-actions" style="pointer-events: auto !important;">
                <button type="button" onclick="window.declinePrivacyPolicy(event)" class="btn-privacy-decline" style="pointer-events: auto !important; cursor: pointer; touch-action: manipulation;">
                    Decline
                </button>
                <button type="button" id="btn-accept-policy-proceed" onclick="window.acceptPolicyAndProceed(event)"
                    class="btn-privacy-accept is-disabled" style="pointer-events: auto !important; cursor: pointer; touch-action: manipulation;">
                    Accept
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sex / Gender Selection Droplist Modal -->
<div id="gender-select-modal" class="auth-2fa-overlay" style="display: none; background: rgba(15, 23, 42, 0.96) !important;"
    onclick="if(event.target===this) window.closeGenderModal(event);">
    <div class="gender-modal-card" style="background: #ffffff !important;">
        <div class="gender-modal-header" style="background: #1e3a8a !important;">
            <div class="gender-modal-header-content">
                <span class="gender-modal-badge"><i class="fa-solid fa-venus-mars"></i> Personalization</span>
                <h3 class="gender-modal-title">Select Sex / Gender</h3>
            </div>
            <button type="button" class="gender-modal-close" onclick="window.closeGenderModal(event)" aria-label="Close">
                &times;
            </button>
        </div>
        <div class="gender-modal-body" style="background: #ffffff !important;">
            <div class="gender-option-item" data-value="Male" onclick="window.selectGenderOption('Male')" style="background: #ffffff !important;">
                <div class="gender-option-icon icon-male" style="background: #dbeafe !important; color: #1d4ed8 !important;"><i class="fa-solid fa-mars"></i></div>
                <div class="gender-option-info">
                    <span class="gender-option-name" style="color: #0f172a !important;">Male</span>
                </div>
                <i class="fa-solid fa-circle-check gender-check-icon"></i>
            </div>
            <div class="gender-option-item" data-value="Female" onclick="window.selectGenderOption('Female')" style="background: #ffffff !important;">
                <div class="gender-option-icon icon-female" style="background: #fce7f3 !important; color: #db2777 !important;"><i class="fa-solid fa-venus"></i></div>
                <div class="gender-option-info">
                    <span class="gender-option-name" style="color: #0f172a !important;">Female</span>
                </div>
                <i class="fa-solid fa-circle-check gender-check-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Offline Mode Dialog Modal -->
<div id="offline-auth-modal" class="auth-offline-modal" style="display: none;">
    <div class="auth-offline-backdrop" onclick="window.closeOfflineAuthModal()"></div>
    <div class="auth-offline-card">
        <div class="auth-offline-icon-circle">
            <i class="fa-solid fa-cloud-slash"></i>
        </div>
        <h3 class="auth-offline-title">Internet Required to Log In</h3>
        <p class="auth-offline-desc" id="offline-auth-desc">
            An active internet connection is required to log in and verify your cloud account. You can enter Offline Mode to explore all saved attractions, itineraries, and maps right now.
        </p>
        <div class="auth-offline-actions">
            <button type="button" class="btn-offline-confirm" onclick="window.enterOfflineMode()">
                <i class="fa-solid fa-compass"></i> Explore in Offline Mode
            </button>
            <button type="button" class="btn-offline-dismiss" onclick="window.closeOfflineAuthModal()">
                Dismiss
            </button>
        </div>
    </div>
</div>

<script>
(function initAuthScope() {
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

    // =========================================================================
    // Universal Offline Mode Engine for Auth View
    // =========================================================================
        function updateOfflineStatus() {
            var isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
            if (isOffline) {
                document.body.classList.add('is-offline');
                document.body.setAttribute('data-offline', 'true');
            } else {
                document.body.classList.remove('is-offline');
                document.body.removeAttribute('data-offline');
                try {
                    localStorage.removeItem('intan_elyu_offline_mode');
                    localStorage.removeItem('intan_elyu_offline_guest');
                    sessionStorage.removeItem('elyu_show_offline_bypass_notice');
                } catch(e) {}
            }
            var badge = document.getElementById('auth-offline-badge');
            if (badge) {
                badge.style.setProperty('display', isOffline ? 'inline-flex' : 'none', 'important');
            }
            var offlineBtns = document.querySelectorAll('.btn-offline-guest');
            offlineBtns.forEach(function (btn) {
                btn.style.setProperty('display', 'flex', 'important');
                var span = btn.querySelector('span');
                var icon = btn.querySelector('i');
                if (isOffline) {
                    if (span) span.textContent = 'Explore in Offline Mode';
                    if (icon) icon.className = 'fa-solid fa-cloud-slash';
                } else {
                    if (span) span.textContent = 'Continue as Guest';
                    if (icon) icon.className = 'fa-solid fa-compass';
                }
            });
        }
        updateOfflineStatus();
        window.addEventListener('online', () => {
            updateOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('offline', updateOfflineStatus);
        window.addEventListener('appOnline', () => {
            updateOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('appOffline', updateOfflineStatus);

    window.showOfflineAuthModal = function (email) {
        const modal = document.getElementById('offline-auth-modal');
        if (!modal) return;
        const desc = document.getElementById('offline-auth-desc');
        if (desc && email) {
            desc.innerHTML = `An active internet connection is required to log in with <strong>${email}</strong>. Please connect to Wi-Fi or mobile data, or explore saved guides in Offline Mode.`;
        }
        modal.style.display = 'flex';
        modal.classList.add('active');
        modal.style.pointerEvents = 'auto';
    };

    window.closeOfflineAuthModal = function () {
        const modal = document.getElementById('offline-auth-modal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            modal.style.pointerEvents = 'none';
        }
    };

    window.enterOfflineMode = function (e) {
        if (e && e.preventDefault) e.preventDefault();
        window.closeOfflineAuthModal();

        var isOffline = !navigator.onLine || window.isAppOnline === false;
        if (isOffline) {
            // Truly Offline: Activate Offline Mode
            let cached = null;
            try {
                cached = JSON.parse(localStorage.getItem('auth_user') || 'null');
            } catch (err) {}

            if (!cached || !cached.name) {
                cached = {
                    id: 'offline_guest',
                    user_id: 'offline_guest',
                    name: 'Offline Explorer',
                    full_name: 'Offline Explorer',
                    role: 'tourist',
                    points: 0,
                    xp: 0,
                    completed_activities: 0,
                    home_location: 'La Union',
                    is_offline_guest: true
                };
                localStorage.setItem('auth_user', JSON.stringify(cached));
            }

            const prevTok = localStorage.getItem('intan_elyu_token');
            if (prevTok && !prevTok.startsWith('offline_') && !prevTok.startsWith('guest_')) {
                localStorage.setItem('intan_elyu_real_token_backup', prevTok);
            }
            localStorage.setItem('intan_elyu_token', 'offline_guest_token');
            localStorage.setItem('intan_elyu_offline_mode', 'true');
            localStorage.setItem('intan_elyu_offline_guest', 'true');
            sessionStorage.setItem('elyu_show_offline_bypass_notice', 'true');

            if (typeof window.notifyAppOffline === 'function') {
                window.notifyAppOffline();
            }
            if (typeof showToast === 'function') {
                showToast('Entered Offline Mode! Showing saved guides & maps.');
            }
        } else {
            // Truly Online: Continue as Guest (Do NOT force offline mode!)
            localStorage.setItem('intan_elyu_token', 'guest_token');
            localStorage.setItem('intan_elyu_guest', 'true');
            localStorage.removeItem('intan_elyu_offline_mode');
            localStorage.removeItem('intan_elyu_offline_guest');
            sessionStorage.removeItem('elyu_show_offline_bypass_notice');

            // Always start Guest Mode with a clean profile so the previously logged-in
            // account's name, avatar, points and vouchers never leak into the guest session.
            const guestProfile = {
                id: 'guest',
                user_id: 'guest',
                name: 'Guest Explorer',
                full_name: 'Guest Explorer',
                role: 'tourist',
                points: 0,
                xp: 0,
                completed_activities: 0,
                home_location: 'La Union',
                is_guest: true
            };
            localStorage.setItem('auth_user', JSON.stringify(guestProfile));
            // Explicit guest choice: drop any backed-up account session so it can't be auto-restored later
            localStorage.removeItem('intan_elyu_real_token_backup');
            localStorage.removeItem('auth_user_backup');

            if (typeof showToast === 'function') {
                showToast('Continuing as Guest Explorer. Welcome!', 'info', 3000);
            }
        }

        if (typeof navigateTo === 'function') {
            navigateTo('dashboard');
        } else {
            window.location.href = 'index.php?view=dashboard';
        }
    };
    window.handleGuestOrOfflineClick = window.enterOfflineMode;

    window.openAuthCancelModal = function (msg) {
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

    window.closeAuthCancelModal = function () {
        const modal = document.getElementById('auth-cancel-modal');
        if (modal) modal.classList.remove('active');
        setTimeout(() => {
            if (modal) modal.style.display = 'none';
        }, 300);
    };

    window.openAuthLogin2FAModal = function (email) {
        const modal = document.getElementById('auth-login-2fa-modal');
        const emailEl = document.getElementById('auth-2fa-target-email');
        if (emailEl && email) {
            emailEl.textContent = email;
        }
        if (!modal) return;

        // Reset boxes
        const boxes = document.querySelectorAll('.auth-login-2fa-otp-box');
        boxes.forEach(b => b.value = '');

        modal.style.display = 'flex';
        modal.style.pointerEvents = 'auto';
        requestAnimationFrame(() => {
            modal.classList.add('active');
            if (boxes[0]) setTimeout(() => boxes[0].focus(), 150);
        });
    };

    window.closeAuthLogin2FAModal = function () {
        const modal = document.getElementById('auth-login-2fa-modal');
        if (modal) modal.classList.remove('active');
        setTimeout(() => {
            if (modal) {
                modal.style.display = 'none';
                modal.style.pointerEvents = 'none';
            }
        }, 250);
    };

    window.handleLogin2FAOtpInput = function (el, index) {
        el.value = el.value.replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.auth-login-2fa-otp-box');
        if (el.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }
        // If all 6 digits filled, auto-submit
        const fullCode = Array.from(boxes).map(b => b.value).join('');
        if (fullCode.length === 6) {
            window.submitLogin2FA();
        }
    };

    window.handleLogin2FAOtpKeydown = function (el, e, index) {
        const boxes = document.querySelectorAll('.auth-login-2fa-otp-box');
        if (e.key === 'Backspace' && !el.value && index > 0) {
            boxes[index - 1].focus();
        }
    };

    window.handleLogin2FAOtpPaste = function (e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.auth-login-2fa-otp-box');
        for (let i = 0; i < boxes.length && i < pasted.length; i++) {
            boxes[i].value = pasted[i];
        }
        if (pasted.length >= 6) {
            if (boxes[5]) boxes[5].focus();
            window.submitLogin2FA();
        }
    };

    window.submitLogin2FA = async function () {
        if (!window._pending2FALogin) {
            window.closeAuthLogin2FAModal();
            return;
        }

        const boxes = document.querySelectorAll('.auth-login-2fa-otp-box');
        const code = Array.from(boxes).map(b => b.value.trim()).join('');
        if (code.length < 6) {
            if (typeof showToast === 'function') {
                showToast('Please enter all 6 digits of your 2FA verification code.', 'warning');
            }
            return;
        }

        const btn = document.getElementById('btn-submit-login-2fa');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';
            btn.disabled = true;
        }

        try {
            const response = await fetch(backendUrl + '/api/auth/login', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    email: window._pending2FALogin.email,
                    password: window._pending2FALogin.password,
                    two_factor_code: code,
                    remember: window._pending2FALogin.remember
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Invalid or expired 2FA code.');
            }

            // 2FA login verified!
            window.closeAuthLogin2FAModal();
            const remember = window._pending2FALogin.remember;
            const email = window._pending2FALogin.email;
            window._pending2FALogin = null;

            if (remember) {
                localStorage.setItem('intan_elyu_remembered_email', email);
                localStorage.setItem('intan_elyu_remember_me', '1');
            } else {
                localStorage.removeItem('intan_elyu_remembered_email');
                localStorage.removeItem('intan_elyu_remember_me');
            }

            window.purgeAllUserSessionCaches();

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('auth_user_backup', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            localStorage.setItem('Intan_Elyu_Token', data.token);
            localStorage.setItem('api_token', data.token);
            localStorage.setItem('intan_elyu_real_token_backup', data.token);
            localStorage.setItem('intan_elyu_2fa_active', 'true');
            if (window.AppStorage) {
                try {
                    window.AppStorage.setItem('auth_user', data.user);
                    window.AppStorage.setItem('intan_elyu_token', data.token);
                } catch (e) { }
            }

            if (typeof window.seedUserVouchersAndPoints === 'function') {
                window.seedUserVouchersAndPoints(data.token, data.user, data.vouchers);
            }

            window.showLoginSuccessModal(data.user);
        } catch (error) {
            console.error('2FA Verification Error:', error);
            if (typeof showToast === 'function') {
                showToast(error.message || 'Invalid 2FA code.', 'error', 4000);
            }
            const boxContainer = document.getElementById('auth-login-2fa-boxes');
            if (boxContainer) {
                boxContainer.classList.remove('shake-attention');
                void boxContainer.offsetWidth;
                boxContainer.classList.add('shake-attention');
            }
            boxes.forEach(b => b.value = '');
            if (boxes[0]) boxes[0].focus();
        } finally {
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-lock-open" style="color: #1e3a8a !important;"></i> Verify &amp; Log In';
                btn.disabled = false;
            }
        }
    };

    window.resendLogin2FACode = async function () {
        if (!window._pending2FALogin) return;
        const resendBtn = document.getElementById('auth-2fa-resend-btn');
        const timerEl = document.getElementById('auth-2fa-timer');
        if (resendBtn) resendBtn.style.pointerEvents = 'none';

        try {
            const response = await fetch(backendUrl + '/api/auth/2fa/resend', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    email: window._pending2FALogin.email,
                    password: window._pending2FALogin.password
                })
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Failed to resend 2FA code.');
            }
            if (typeof showToast === 'function') {
                showToast('A new 2FA code has been sent to your email!', 'success');
            }

            let timeLeft = 60;
            if (resendBtn) resendBtn.style.display = 'none';
            if (timerEl) {
                timerEl.style.display = 'inline';
                timerEl.textContent = `(Resend in ${timeLeft}s)`;
            }
            const timer = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    if (timerEl) timerEl.style.display = 'none';
                    if (resendBtn) {
                        resendBtn.style.display = 'inline';
                        resendBtn.style.pointerEvents = 'auto';
                    }
                } else if (timerEl) {
                    timerEl.textContent = `(Resend in ${timeLeft}s)`;
                }
            }, 1000);
        } catch (err) {
            if (typeof showToast === 'function') showToast(err.message || 'Error resending code.', 'warning');
            if (resendBtn) resendBtn.style.pointerEvents = 'auto';
        }
    };

    window.showLoginSuccessModal = function (user) {
        if (window._loginModalActive) return;
        window._loginModalActive = true;

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

    function updateTitleWithTransition(newText) {
        const titleEl = document.getElementById('auth-title');
        const subtitleEl = document.getElementById('auth-subtitle');
        if (!titleEl || titleEl.textContent === newText) return;
        titleEl.classList.add('text-fade-out');
        if (subtitleEl) subtitleEl.classList.add('text-fade-out');
        setTimeout(() => {
            const highlightedTitles = {
                'Welcome to Elyu': 'Welcome to <span>Elyu</span>',
                'Start your Journey': 'Start your <span>Journey</span>'
            };
            const subtitles = {
                'Welcome to Elyu': 'Explore places. Create memories.<br>Discover La Union.',
                'Start your Journey': 'Create your account and explore<br>the beauty of La Union.',
                'Account Recovery': 'Recover your account securely.',
                'Verify Your Email': 'Check your inbox and enter the 6-digit code below.'
            };
            titleEl.innerHTML = highlightedTitles[newText] || newText;
            if (subtitleEl) subtitleEl.innerHTML = subtitles[newText] || '';
            titleEl.classList.remove('text-fade-out');
            if (subtitleEl) subtitleEl.classList.remove('text-fade-out');
        }, 200);
    }

    function positionTabGlider(isRegister, animate = true) {
        const glider = document.getElementById('tab-gooey-glider');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');
        const tabsContainer = document.getElementById('auth-tabs');
        const targetTab = isRegister ? tabRegister : tabLogin;
        if (!glider || !targetTab || !tabsContainer) return;

        const targetRect = targetTab.getBoundingClientRect();
        const containerRect = tabsContainer.getBoundingClientRect();

        if (targetRect.width === 0 || containerRect.width === 0) {
            requestAnimationFrame(() => positionTabGlider(isRegister, false));
            return;
        }

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
    window.positionTabGlider = positionTabGlider;

    function toggleAuthMode(isRegister) {
        const wrapper = document.getElementById('forms-wrapper');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');
        const tabsContainer = document.getElementById('auth-tabs');
        const authContainer = document.querySelector('.auth-container');

        if (tabsContainer) tabsContainer.style.display = 'flex';
        if (wrapper) wrapper.classList.remove('show-forgot', 'show-otp');

        if (authContainer) {
            if (isRegister) {
                authContainer.classList.add('mode-register');
            } else {
                authContainer.classList.remove('mode-register');
            }
        }

        if (isRegister) {
            if (wrapper) wrapper.classList.add('show-register');
            if (tabLogin) tabLogin.classList.remove('active');
            if (tabRegister) tabRegister.classList.add('active');
            updateTitleWithTransition('Start your Journey');
        } else {
            if (wrapper) wrapper.classList.remove('show-register');
            if (tabRegister) tabRegister.classList.remove('active');
            if (tabLogin) tabLogin.classList.add('active');
            updateTitleWithTransition('Welcome to Elyu');
        }

        positionTabGlider(isRegister, true);
    }
    window.toggleAuthMode = toggleAuthMode;

    window.initAuthView = function () {
        const searchParams = new URLSearchParams(window.location.search);
        const isRegisterMode = searchParams.get('mode') === 'register' || 
                               searchParams.get('tab') === 'register' || 
                               window._authStartMode === 'register';
        const isFromSidebar = searchParams.get('from_sidebar') === '1' || 
                              window._authFromSidebar === true;

        // Dismiss all lingering overlays and backdrops so they can NEVER block touch events
        ['sidebar-overlay', 'global-oauth-overlay', 'offline-auth-modal', 'login-success-modal', 'auth-cancel-modal', 'privacy-policy-modal', 'gender-select-modal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (id === 'sidebar-overlay') {
                    el.style.display = 'none';
                    el.style.opacity = '0';
                    el.style.pointerEvents = 'none';
                    try { el.remove(); } catch (e) { }
                } else if (id === 'global-oauth-overlay') {
                    el.style.display = 'none';
                    try { el.remove(); } catch (e) { }
                } else {
                    el.classList.remove('active');
                    el.style.display = 'none';
                    el.style.pointerEvents = 'none';
                }
            }
        });

        toggleAuthMode(Boolean(isRegisterMode));

        // Ensure "Continue as Guest" / "Explore in Offline Mode" button is always visible and clickable
        document.querySelectorAll('.btn-offline-guest').forEach(btn => {
            btn.style.setProperty('display', 'flex', 'important');
            btn.style.setProperty('pointer-events', 'auto', 'important');
        });

        setTimeout(() => { positionTabGlider(Boolean(isRegisterMode), false); }, 50);
        setTimeout(() => { positionTabGlider(Boolean(isRegisterMode), false); }, 180);
    };

    window.addEventListener('resize', () => {
        const wrapper = document.getElementById('forms-wrapper');
        const isReg = wrapper && wrapper.classList.contains('show-register');
        positionTabGlider(Boolean(isReg), false);
    });

    window.showForgotPassword = function (e) {
        if (e) e.preventDefault();

        const tabsContainer = document.getElementById('auth-tabs');
        const wrapper = document.getElementById('forms-wrapper');
        const authContainer = document.querySelector('.auth-container');

        if (tabsContainer) tabsContainer.style.display = 'none';
        if (authContainer) authContainer.classList.remove('mode-register');

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
        updateTitleWithTransition('Account Recovery');
    };

    window.hideForgotPassword = function (e) {
        if (e) e.preventDefault();
        const tabsContainer = document.getElementById('auth-tabs');
        const wrapper = document.getElementById('forms-wrapper');
        const authContainer = document.querySelector('.auth-container');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');

        if (tabsContainer) tabsContainer.style.display = 'flex';
        if (authContainer) authContainer.classList.remove('mode-register');
        if (wrapper) wrapper.classList.remove('show-forgot', 'show-register');
        if (tabRegister) tabRegister.classList.remove('active');
        if (tabLogin) tabLogin.classList.add('active');
        updateTitleWithTransition('Welcome to Elyu');
        positionTabGlider(false, false);
    };

    window.togglePasswordVisibility = function (inputId, iconElement) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (iconElement) {
                iconElement.classList.remove('fa-eye');
                iconElement.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (iconElement) {
                iconElement.classList.remove('fa-eye-slash');
                iconElement.classList.add('fa-eye');
            }
        }
    };
    function togglePasswordVisibility(inputId, iconElement) {
        window.togglePasswordVisibility(inputId, iconElement);
    }

    window.purgeAllUserSessionCaches = function () {
        try {
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (!k) continue;
                if (
                    k.startsWith('profile_data_') ||
                    k.startsWith('leaderboard_data_') ||
                    k.startsWith('points_balance_') ||
                    k.startsWith('dashboard_data_') ||
                    k.startsWith('dashboard_rank_') ||
                    k.startsWith('dashboard_trips_') ||
                    k.startsWith('saved_trips_') ||
                    k.startsWith('saved_places_') ||
                    k.startsWith('intan_cache_') ||
                    k.startsWith('intan_elyu_claimed_vouchers') ||
                    k.startsWith('intan_elyu_cached_vouchers') ||
                    k.startsWith('game_done_') ||
                    k.startsWith('intan_elyu_offline_game_claims') ||
                    k.startsWith('intan_elyu_offline_checkins') ||
                    k.startsWith('intan_elyu_offline_visited_spots') ||
                    k === 'api_token' ||
                    k === 'intan_elyu_token' ||
                    k === 'Intan_Elyu_Token' ||
                    k === 'intan_elyu_real_token_backup' ||
                    k === 'auth_user' ||
                    k === 'auth_user_backup' ||
                    k === 'intan_elyu_user' ||
                    k === 'my_rank' ||
                    k === 'intan_elyu_guest' ||
                    k === 'intan_elyu_offline_guest' ||
                    k === 'intan_elyu_offline_mode'
                ) {
                    keysToRemove.push(k);
                }
            }
            keysToRemove.forEach(k => localStorage.removeItem(k));
            sessionStorage.removeItem('elyu_show_offline_bypass_notice');
        } catch (e) {
            try {
                localStorage.removeItem('api_token');
                localStorage.removeItem('intan_elyu_token');
                localStorage.removeItem('Intan_Elyu_Token');
                localStorage.removeItem('auth_user');
                localStorage.removeItem('auth_user_backup');
                localStorage.removeItem('intan_elyu_user');
            } catch (err) { }
        }

        window._cachedMeData = null;
        window._cachedMyRank = null;
        window._cachedRawLeadersList = [];
        window.myUserData = null;
        window._userPointsBalance = null;
        window._touristRedemptions = null;
        window._cachedActiveVouchers = null;
        window._cachedHistoryVouchers = null;
        window._practiceModes = {};
        window._cachedCompletedTrips = [];
        window.profileNeedsRefresh = true;
        window.leaderboardNeedsRefresh = true;
        window.dashboardNeedsRefresh = true;
    };

    window.seedUserVouchersAndPoints = function (token, user, vouchers) {
        if (!token) return;
        try {
            const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
            const userVouchers = Array.isArray(vouchers) ? vouchers : [];
            const userPoints = (user && (user.points !== undefined ? user.points : user.xp)) || 0;
            const voucherPayload = {
                status: 'success',
                points: userPoints,
                xp: (user && user.xp) || 0,
                level: (user && user.level) || 1,
                user_id: user ? (user.id || user.user_id) : null,
                vouchers: userVouchers
            };
            localStorage.setItem(ptsCacheKey, JSON.stringify(voucherPayload));

            // Populate in-memory globals
            window._userPointsBalance = userPoints;
            window._touristRedemptions = userVouchers;

            const activeVouchers = [];
            const historyVouchers = [];
            userVouchers.forEach(v => {
                const st = (v.status || '').toLowerCase().trim();
                const isRedeemed = ['redeemed', 'used', 'completed'].includes(st);
                let isExpired = st === 'expired';
                if (!isExpired && v.expires_at && (v.expiration_type === 'date' || v.expiration_type === 'fixed_date')) {
                    const expDate = new Date(String(v.expires_at).replace(' ', 'T'));
                    if (!isNaN(expDate.getTime()) && expDate < new Date()) {
                        isExpired = true;
                    }
                }
                if (isRedeemed || isExpired) {
                    historyVouchers.push(v);
                } else {
                    activeVouchers.push(v);
                }
            });
            window._cachedActiveVouchers = activeVouchers;
            window._cachedHistoryVouchers = historyVouchers;

            // Seed scoped claimed vouchers for discount & profile matching
            const uid = String(user ? (user.id || user.user_id) : 'guest');
            const claimedSet = new Set();
            userVouchers.forEach(v => {
                if (v.voucher_id) {
                    claimedSet.add(String(v.voucher_id));
                    claimedSet.add('db_' + v.voucher_id);
                }
                if (v.id) claimedSet.add(String(v.id));
                const c = v.voucher_code || v.code || v.redemption_code;
                if (c) claimedSet.add(String(c).toUpperCase().trim());
            });
            localStorage.setItem('intan_elyu_claimed_vouchers_' + uid, JSON.stringify(Array.from(claimedSet)));
        } catch (e) {
            console.warn('seedUserVouchersAndPoints error:', e);
        }
    };

    window.handleLogin = async function (e) {
        e.preventDefault();
        if (window._isLoggingIn) return;
        window._isLoggingIn = true;

        const btn = document.getElementById('btn-login');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;

        const email = (document.getElementById('login-email')?.value || '').trim();
        const password = document.getElementById('login-password')?.value || '';
        const remember = document.getElementById('login-remember')?.checked || false;

        // Offline mode interceptor
        if (!navigator.onLine || window.isAppOnline === false) {
            btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            btn.disabled = false;

            let cachedUser = null;
            try { cachedUser = JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch (err) {}
            const rememberedEmail = localStorage.getItem('intan_elyu_remembered_email');

            // If the user previously logged into this account on this device
            if (cachedUser && (
                (cachedUser.email && cachedUser.email.toLowerCase() === email.toLowerCase()) ||
                (rememberedEmail && rememberedEmail.toLowerCase() === email.toLowerCase())
            )) {
                const prevTok = localStorage.getItem('intan_elyu_token');
                if (prevTok && !prevTok.startsWith('offline_') && !prevTok.startsWith('guest_')) {
                    localStorage.setItem('intan_elyu_real_token_backup', prevTok);
                }
                localStorage.setItem('intan_elyu_token', 'offline_session_token');
                localStorage.setItem('intan_elyu_offline_mode', 'true');
                if (remember) {
                    localStorage.setItem('intan_elyu_remembered_email', email);
                    localStorage.setItem('intan_elyu_remember_me', '1');
                }
                if (typeof showToast === 'function') {
                    showToast('Logged in offline with saved profile! Cloud sync will resume when online.', 'success');
                }
                showLoginSuccessModal(cachedUser);
                return;
            }

            // Not matched or new account while offline: show modal with Explore in Offline Mode option
            window._isLoggingIn = false;
            if (typeof window.showOfflineAuthModal === 'function') {
                window.showOfflineAuthModal(email);
            } else if (typeof showToast === 'function') {
                showToast('Internet connection is required to log in. Please connect to the internet or enter Offline Mode.', 'warning', 5000);
            }
            return;
        }

        try {
            const response = await fetch(backendUrl + '/api/auth/login', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, password: password, remember: remember })
            });

            let data;
            try {
                data = await response.json();
            } catch (jsonErr) {
                if (!response.ok) {
                    throw new Error('Internet connection is required to log in. Please connect to the internet.');
                }
                throw jsonErr;
            }

            if (!response.ok) {
                throw new Error(data.error || data.message || 'Invalid login credentials.');
            }

            // Check if Two-Factor Authentication (2FA) is required
            if (data.requires_2fa) {
                window._pending2FALogin = { email: email, password: password, remember: remember };
                window.openAuthLogin2FAModal(data.email || email);
                return;
            }

            if (remember) {
                localStorage.setItem('intan_elyu_remembered_email', email);
                localStorage.setItem('intan_elyu_remember_me', '1');
            } else {
                localStorage.removeItem('intan_elyu_remembered_email');
                localStorage.removeItem('intan_elyu_remember_me');
            }

            // Purge previous user session caches and tokens before storing new authenticated session
            window.purgeAllUserSessionCaches();

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('auth_user_backup', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            localStorage.setItem('Intan_Elyu_Token', data.token);
            localStorage.setItem('api_token', data.token);
            localStorage.setItem('intan_elyu_real_token_backup', data.token);
            if (window.AppStorage) {
                try {
                    window.AppStorage.setItem('auth_user', data.user);
                    window.AppStorage.setItem('intan_elyu_token', data.token);
                } catch (e) { }
            }

            if (typeof window.seedUserVouchersAndPoints === 'function') {
                window.seedUserVouchersAndPoints(data.token, data.user, data.vouchers);
            }

            showLoginSuccessModal(data.user);
        } catch (error) {
            console.error('Login Error:', error);
            const errStr = (error && (error.message || String(error))) || '';
            const isNetworkIssue = !navigator.onLine || window.isAppOnline === false ||
                errStr.toLowerCase().includes('failed to fetch') ||
                errStr.toLowerCase().includes('networkerror') ||
                errStr.toLowerCase().includes('network') ||
                errStr.toLowerCase().includes('load failed') ||
                errStr.toLowerCase().includes('typeerror');

            if (isNetworkIssue) {
                let cachedUser = null;
                try { cachedUser = JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch (err) {}
                if (cachedUser && cachedUser.email && cachedUser.email.toLowerCase() === email.toLowerCase()) {
                    localStorage.setItem('intan_elyu_token', 'offline_session_token');
                    if (typeof showToast === 'function') {
                        showToast('Offline Mode: Logged in using saved profile.', 'success');
                    }
                    showLoginSuccessModal(cachedUser);
                    return;
                }
                if (typeof showToast === 'function') {
                    showToast('Internet connection is required to log in. Please connect to the internet.', 'warning', 5000);
                }
            } else {
                let cleanMsg = error.message || 'Invalid login credentials.';
                if (cleanMsg.toLowerCase().includes('failed to fetch') || cleanMsg.toLowerCase().includes('networkerror') || cleanMsg.toLowerCase().includes('load failed') || cleanMsg.toLowerCase().includes('network') || cleanMsg.toLowerCase().includes('typeerror')) {
                    cleanMsg = 'Internet connection is required to log in. Please connect to the internet.';
                }
                if (typeof showToast === 'function') showToast(cleanMsg, 'error', 4500);
            }
            window._isLoggingIn = false;
            btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            btn.disabled = false;
        }
    };

    window.isCompleteRegistrationEmail = function (email) {
        const completeEmailPattern = /^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)*\.[A-Za-z]{2,63}$/;
        const normalizedEmail = String(email || '').trim().toLowerCase();
        if (!completeEmailPattern.test(normalizedEmail)) return false;
        const domain = normalizedEmail.split('@')[1];
        return !domain.startsWith('gmail.') || domain === 'gmail.com';
    };

    window.validateRegisterFormInline = function () {
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
                if (window.isCompleteRegistrationEmail(val)) {
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

            var isFocused = (document.activeElement === pwdEl);
            if (pwd.length === 0 && !isFocused) {
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

                // Update checklist item styles with high-contrast icons
                const updateChk = (el, passed, text) => {
                    if (!el) return;
                    el.className = 'pwd-chk-item' + (passed ? ' passed' : '');
                    el.innerHTML = passed
                        ? '<i class="fa-solid fa-circle-check"></i> ' + text
                        : '<i class="fa-solid fa-circle-dot"></i> ' + text;
                };

                updateChk(chkLen8, len8, '8+ characters');
                updateChk(chkNum, hasNum, 'A number');
                updateChk(chkCap, hasCap, 'A capital letter');
                updateChk(chkSym, hasSym, 'A symbol');

                // High-contrast badge and score update
                const labels = ['Too Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const badgeClasses = ['status-weak', 'status-weak', 'status-fair', 'status-good', 'status-strong'];
                const badgeEl = document.getElementById('pwd-strength-badge');

                if (label) {
                    label.textContent = 'Strength: ' + (labels[score] || 'Weak');
                }
                if (badgeEl) {
                    badgeEl.className = 'pwd-strength-badge ' + (badgeClasses[score] || 'status-weak');
                }

                if (score >= 3 && len8) {
                    if (pwdIcon) pwdIcon.className = 'fa-solid fa-circle-check field-status-icon password-offset valid';
                } else if (pwd.length > 0) {
                    if (pwdIcon) pwdIcon.className = 'fa-solid fa-circle-xmark field-status-icon password-offset invalid';
                } else {
                    if (pwdIcon) pwdIcon.className = 'fa-solid field-status-icon password-offset';
                }
            }
        }
    };

    window.handleRegisterSubmit = async function (e) {
        if (e) e.preventDefault();
        window.validateRegisterFormInline();
        const pwd = document.getElementById('reg-password')?.value || '';
        const firstName = (document.getElementById('reg-first-name')?.value || '').trim();
        const lastName = (document.getElementById('reg-last-name')?.value || '').trim();
        const gender = (document.getElementById('reg-gender')?.value || '').trim();
        const email = (document.getElementById('reg-email')?.value || '').trim();
        const chk = document.getElementById('reg-privacy-checkbox');

        if (!firstName || !lastName || !gender || !email || !pwd) {
            if (!gender) {
                if (typeof showToast === 'function') showToast('Please select your sex or gender.');
                document.getElementById('reg-gender')?.focus();
            } else {
                if (typeof showToast === 'function') showToast('Please fill in all registration fields.');
            }
            return;
        }

        if (!window.isCompleteRegistrationEmail(email)) {
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

    window.syncPrivacyCheckbox = function (isChecked) {
        const box = document.getElementById('box-chk-accept-privacy');
        const chk = document.getElementById('chk-accept-privacy');
        const acceptBtn = document.getElementById('btn-accept-policy-proceed');

        if (chk) chk.checked = !!isChecked;

        if (box) {
            box.classList.remove('shake-attention');
            if (isChecked) {
                box.classList.add('is-checked');
            } else {
                box.classList.remove('is-checked');
            }
        }

        if (acceptBtn) {
            if (isChecked) {
                acceptBtn.classList.remove('is-disabled');
                acceptBtn.removeAttribute('disabled');
            } else {
                acceptBtn.classList.add('is-disabled');
            }
        }
    };

    window.togglePrivacyCheckboxDirect = function (e) {
        const chk = document.getElementById('chk-accept-privacy');
        if (!chk) return;
        if (e && e.target === chk) {
            // Already toggled natively
            window.syncPrivacyCheckbox(chk.checked);
            return;
        }
        chk.checked = !chk.checked;
        window.syncPrivacyCheckbox(chk.checked);
    };

    window.openPrivacyPolicyModal = function (e) {
        if (e) {
            try { e.preventDefault(); } catch (err) { }
            try { e.stopPropagation(); } catch (err) { }
        }

        const modal = document.getElementById('privacy-policy-modal');
        if (!modal) return;

        const scrollBody = document.getElementById('privacy-modal-scroll-body');

        // Check if registration checkbox is already checked
        const regChk = document.getElementById('reg-privacy-checkbox');
        const isAccepted = !!(regChk && regChk.checked);

        window.syncPrivacyCheckbox(isAccepted);

        if (scrollBody) {
            scrollBody.scrollTop = 0;
        }

        modal.style.display = 'flex';
        modal.style.pointerEvents = 'auto';
        requestAnimationFrame(() => {
            modal.classList.add('active');
            modal.style.pointerEvents = 'auto';
        });
    };

    window.closePrivacyPolicyModal = function (e) {
        if (e) {
            try { e.preventDefault(); } catch (err) { }
            try { e.stopPropagation(); } catch (err) { }
        }
        const modal = document.getElementById('privacy-policy-modal');
        const scrollBody = document.getElementById('privacy-modal-scroll-body');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.style.pointerEvents = 'none';
            }, 250);
        }
        if (scrollBody) {
            scrollBody.scrollTop = 0;
        }
    };

    window.declinePrivacyPolicy = function (e) {
        if (e) {
            try { e.preventDefault(); } catch (err) { }
            try { e.stopPropagation(); } catch (err) { }
        }
        // Explicitly keep register terms unchecked if declined
        const regChk = document.getElementById('reg-privacy-checkbox');
        if (regChk) {
            regChk.checked = false;
            regChk.dispatchEvent(new Event('change'));
        }
        window.closePrivacyPolicyModal();
    };

    window.acceptPolicyAndProceed = async function (e) {
        if (e) {
            try { e.preventDefault(); } catch (err) { }
            try { e.stopPropagation(); } catch (err) { }
        }

        const chk = document.getElementById('chk-accept-privacy');
        if (!chk || !chk.checked) {
            const box = document.getElementById('box-chk-accept-privacy');
            if (box) {
                box.classList.remove('shake-attention');
                void box.offsetWidth; // Force CSS reflow
                box.classList.add('shake-attention');
            }
            if (typeof showToast === 'function') {
                showToast('Please check the box to accept the Terms & Privacy Policy first.', 'warning', 2500);
            }
            return;
        }

        // Check the register terms checkbox on the auth form
        const regChk = document.getElementById('reg-privacy-checkbox');
        if (regChk) {
            regChk.checked = true;
            regChk.dispatchEvent(new Event('change'));
        }

        if (typeof showToast === 'function') {
            showToast('Terms & Privacy Policy accepted.', 'success', 2000);
        }

        window.closePrivacyPolicyModal();
    };

    // Sex / Gender Custom Droplist Modal Functions
    window.openGenderModal = function (e) {
        if (e) {
            try { e.preventDefault(); } catch (err) { }
            try { e.stopPropagation(); } catch (err) { }
        }
        const modal = document.getElementById('gender-select-modal');
        const arrow = document.getElementById('reg-gender-arrow');
        if (!modal) return;

        const currentVal = (document.getElementById('reg-gender')?.value || '').trim();
        document.querySelectorAll('#gender-select-modal .gender-option-item').forEach(item => {
            if (item.getAttribute('data-value') === currentVal) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });

        if (arrow) arrow.style.transform = 'translateY(-50%) rotate(180deg)';

        modal.style.display = 'flex';
        modal.style.pointerEvents = 'auto';
        requestAnimationFrame(() => {
            modal.classList.add('active');
            modal.style.pointerEvents = 'auto';
        });
    };

    window.closeGenderModal = function (e) {
        if (e) {
            try { e.preventDefault(); } catch (err) { }
            try { e.stopPropagation(); } catch (err) { }
        }
        const modal = document.getElementById('gender-select-modal');
        const arrow = document.getElementById('reg-gender-arrow');
        if (arrow) arrow.style.transform = 'translateY(-50%) rotate(0deg)';
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.style.pointerEvents = 'none';
            }, 250);
        }
    };

    window.selectGenderOption = function (val) {
        const hidden = document.getElementById('reg-gender');
        const display = document.getElementById('reg-gender-display');
        if (hidden) hidden.value = val;
        if (display) {
            display.value = val;
            display.style.color = '#0f172a';
            display.style.fontWeight = '600';
        }
        document.querySelectorAll('#gender-select-modal .gender-option-item').forEach(item => {
            if (item.getAttribute('data-value') === val) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
        if (typeof validateRegisterFormInline === 'function') {
            validateRegisterFormInline();
        }
        window.closeGenderModal();
    };

    // Ensure direct click listener is bound as well as inline onclick
    document.addEventListener('DOMContentLoaded', () => {
        const linkTerms = document.getElementById('link-terms-privacy');
        if (linkTerms) {
            linkTerms.addEventListener('click', (ev) => {
                window.openPrivacyPolicyModal(ev);
            });
        }
        const genderTrigger = document.getElementById('reg-gender-trigger');
        if (genderTrigger) {
            genderTrigger.addEventListener('click', (ev) => {
                window.openGenderModal(ev);
            });
        }
    });

    window.submitRegistrationAndTrigger2FA = async function () {
        if (window._isRegistering) return;
        window._isRegistering = true;
        const pwd = document.getElementById('reg-password')?.value || '';
        const firstName = (document.getElementById('reg-first-name')?.value || '').trim();
        const lastName = (document.getElementById('reg-last-name')?.value || '').trim();
        const gender = (document.getElementById('reg-gender')?.value || '').trim();
        const name = `${firstName} ${lastName}`.trim();
        const email = (document.getElementById('reg-email')?.value || '').trim();

        if (!navigator.onLine || window.isAppOnline === false) {
            window._isRegistering = false;
            if (typeof showToast === 'function') {
                showToast('Internet connection is required to register. Please connect to the internet and try again.', 'warning', 5000);
            }
            return;
        }

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
        if (titleEl) titleEl.textContent = 'Sending your code...';
        if (subEl) subEl.textContent = "We're emailing your secure 6-digit verification code.";
        if (spinnerSvg) spinnerSvg.style.display = 'block';
        if (checkmarkIcon) checkmarkIcon.style.display = 'none';

        try {
            const response = await fetch(backendUrl + '/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    name: name,
                    gender: gender,
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

            if (!data.requires_otp) throw new Error('The server did not start email verification. Please try again.');

            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => { modal.style.display = 'none'; }, 250);
            }
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
                btn.disabled = false;
            }
            window._isRegistering = false;
            window.showRegistrationOtpPanel(data.email || email);

        } catch (error) {
            console.error('Register Error:', error);
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
            let errText = error.message || 'Registration failed';
            if (!navigator.onLine || window.isAppOnline === false ||
                errText.toLowerCase().includes('failed to fetch') ||
                errText.toLowerCase().includes('networkerror') ||
                errText.toLowerCase().includes('load failed') ||
                errText.toLowerCase().includes('network') ||
                errText.toLowerCase().includes('typeerror')) {
                errText = 'Internet connection is required to register. Please connect to the internet and try again.';
            }
            if (typeof showToast === 'function') {
                showToast(errText, 'error', 4500);
            }
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
                btn.disabled = false;
            }
            window._isRegistering = false;
        }
    };

    window.showRegistrationOtpPanel = function (email) {
        const wrapper = document.getElementById('forms-wrapper');
        const tabs = document.getElementById('auth-tabs');
        const target = document.getElementById('otp-target-email');
        const authContainer = document.querySelector('.auth-container');
        const keyboardDismiss = document.getElementById('auth-keyboard-dismiss');
        if (!wrapper || !target) return;

        const resetOtpViewport = function () {
            document.body.classList.remove('keyboard-open', 'auth-keyboard-visible');
            document.documentElement.classList.remove('keyboard-open');
            document.documentElement.style.setProperty('--auth-keyboard-height', '0px');
            if (keyboardDismiss) keyboardDismiss.hidden = true;
            if (authContainer) authContainer.scrollTop = 0;
            window.scrollTo(0, 0);
        };

        target.textContent = email;
        sessionStorage.setItem('pending_reg_email', email);
        wrapper.classList.remove('show-register', 'show-forgot');
        wrapper.classList.add('show-otp');
        if (tabs) tabs.style.display = 'none';
        document.activeElement?.blur();
        if (authContainer) {
            authContainer.classList.add('mode-otp');
        }
        resetOtpViewport();
        window.requestAnimationFrame(resetOtpViewport);
        window.setTimeout(resetOtpViewport, 320);
        document.querySelectorAll('.otp-box').forEach((box) => { box.value = ''; });
        updateTitleWithTransition('Verify Your Email');
    };

    window.hideOtpPanel = function (e) {
        if (e) e.preventDefault();
        const tabsContainer = document.getElementById('auth-tabs');
        const wrapper = document.getElementById('forms-wrapper');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');
        const authContainer = document.querySelector('.auth-container');
        if (tabsContainer) tabsContainer.style.display = 'flex';
        if (!wrapper) return;
        wrapper.classList.remove('show-forgot', 'show-otp');
        wrapper.classList.add('show-register');
        if (tabLogin) tabLogin.classList.remove('active');
        if (tabRegister) tabRegister.classList.add('active');
        if (authContainer) authContainer.classList.remove('mode-otp');
        updateTitleWithTransition('Start your Journey');
    };

    window.handleOtpBoxInput = function (el, index) {
        el.value = el.value.replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.otp-box');
        if (el.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }
        checkAutoSubmitOtp();
    };

    window.handleOtpBoxKeydown = function (el, e, index) {
        const boxes = document.querySelectorAll('.otp-box');
        if (e.key === 'Backspace' && !el.value && index > 0) {
            boxes[index - 1].focus();
        }
    };

    window.handleOtpPaste = function (e) {
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

    window.handleVerifyOtp = async function (e) {
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
                window.purgeAllUserSessionCaches();
                localStorage.setItem('auth_user', JSON.stringify(data.user));
                localStorage.setItem('auth_user_backup', JSON.stringify(data.user));
                localStorage.setItem('intan_elyu_user', JSON.stringify(data.user));
                localStorage.setItem('intan_elyu_token', data.token);
                localStorage.setItem('Intan_Elyu_Token', data.token);
                localStorage.setItem('api_token', data.token);
                localStorage.setItem('intan_elyu_real_token_backup', data.token);
                if (window.AppStorage) {
                    try {
                        window.AppStorage.setItem('auth_user', data.user);
                        window.AppStorage.setItem('intan_elyu_token', data.token);
                    } catch (e) { }
                }

                if (typeof window.seedUserVouchersAndPoints === 'function') {
                    window.seedUserVouchersAndPoints(data.token, data.user, data.vouchers);
                }

                showLoginSuccessModal(data.user);
            } catch (error) {
                console.error('2FA Login Verification Error:', error);
                let errText = error.message || 'Invalid 2FA security code';
                if (!navigator.onLine || window.isAppOnline === false ||
                    errText.toLowerCase().includes('failed to fetch') ||
                    errText.toLowerCase().includes('networkerror') ||
                    errText.toLowerCase().includes('load failed') ||
                    errText.toLowerCase().includes('typeerror')) {
                    errText = 'Internet connection is required to complete login. Please connect to the internet and try again.';
                }
                if (typeof showToast === 'function') showToast(errText, 'warning', 5000);
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

            window.purgeAllUserSessionCaches();
            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('auth_user_backup', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            localStorage.setItem('Intan_Elyu_Token', data.token);
            localStorage.setItem('api_token', data.token);
            localStorage.setItem('intan_elyu_real_token_backup', data.token);
            if (window.AppStorage) {
                try {
                    window.AppStorage.setItem('auth_user', data.user);
                    window.AppStorage.setItem('intan_elyu_token', data.token);
                } catch (e) { }
            }

            if (typeof window.seedUserVouchersAndPoints === 'function') {
                window.seedUserVouchersAndPoints(data.token, data.user, data.vouchers);
            }

            window.showOtpSuccessModal(data.user);
        } catch (error) {
            console.error('OTP Verification Error:', error);
            let errText = error.message || 'Invalid verification code';
            const networkError = !navigator.onLine || window.isAppOnline === false ||
                errText.toLowerCase().includes('failed to fetch') ||
                errText.toLowerCase().includes('networkerror') ||
                errText.toLowerCase().includes('load failed') ||
                errText.toLowerCase().includes('typeerror');
            if (networkError) {
                errText = 'Internet connection is required to verify your account. Please connect to the internet and try again.';
                if (typeof showToast === 'function') showToast(errText, 'warning', 5000);
            } else {
                window.showOtpInvalidModal(errText);
            }
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    };

    window.showOtpInvalidModal = function (message) {
        const modal = document.getElementById('otp-invalid-modal');
        const messageEl = document.getElementById('otp-invalid-message');
        if (messageEl) messageEl.textContent = message || 'The verification code is incorrect or expired. Please try again.';
        if (!modal) return;
        modal.style.display = 'flex';
        requestAnimationFrame(() => modal.classList.add('active'));
    };

    window.closeOtpInvalidModal = function () {
        const modal = document.getElementById('otp-invalid-modal');
        if (!modal) return;
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            const boxes = document.querySelectorAll('.otp-box');
            boxes.forEach((box) => { box.value = ''; });
            boxes[0]?.focus();
        }, 220);
    };

    window.showOtpSuccessModal = function (user) {
        const modal = document.getElementById('otp-success-modal');
        if (modal) {
            modal.style.display = 'flex';
            requestAnimationFrame(() => modal.classList.add('active'));
        }
        const regForm = document.getElementById('form-register');
        if (regForm) regForm.reset();
        sessionStorage.removeItem('pending_reg_email');
        setTimeout(() => {
            if (typeof navigateTo === 'function') navigateTo('dashboard');
            else window.location.href = '?view=dashboard';
        }, 1800);
    };

    var fpTimerInterval = null;

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

    window.handleForgotPassword = async function (e) {
        if (e) e.preventDefault();
        const btn = document.getElementById('fp-btn');
        const oldHtml = btn ? btn.innerHTML : '';
        const email = (document.getElementById('fp-email')?.value || '').trim();

        if (!email) {
            if (typeof showToast === 'function') showToast('Please enter your email address.');
            return;
        }

        if (!navigator.onLine || window.isAppOnline === false) {
            if (typeof showToast === 'function') {
                showToast('Internet connection is required to reset your password. Please connect to the internet and try again.', 'warning', 5000);
            }
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
            let errText = error.message || 'Failed to send reset code.';
            if (!navigator.onLine || window.isAppOnline === false ||
                errText.toLowerCase().includes('failed to fetch') ||
                errText.toLowerCase().includes('networkerror') ||
                errText.toLowerCase().includes('load failed') ||
                errText.toLowerCase().includes('typeerror')) {
                errText = 'Internet connection is required to reset your password. Please connect to the internet and try again.';
            }
            if (typeof showToast === 'function') showToast(errText, 'warning', 5000);
        } finally {
            if (btn) {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }
    };

    window._verifiedFpOtp = '';

    window.handleFpBack = function (e) {
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

    window.handleFpOtpBoxInput = function (el, index) {
        el.value = el.value.replace(/[^0-9]/g, '');
        const boxes = document.querySelectorAll('.fp-otp-box');
        if (el.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }
    };

    window.handleFpOtpBoxKeydown = function (el, e, index) {
        const boxes = document.querySelectorAll('.fp-otp-box');
        if (e.key === 'Backspace' && !el.value && index > 0) {
            boxes[index - 1].focus();
        }
    };

    window.handleFpOtpPaste = function (e) {
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

    window.handleResendFpEmail = function (e) {
        if (e) e.preventDefault();
        window.handleForgotPassword(null);
    };

    window.handleVerifyFpCode = async function (e) {
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
            let errText = error.message || 'Invalid or expired code.';
            if (!navigator.onLine || window.isAppOnline === false ||
                errText.toLowerCase().includes('failed to fetch') ||
                errText.toLowerCase().includes('networkerror') ||
                errText.toLowerCase().includes('load failed') ||
                errText.toLowerCase().includes('typeerror')) {
                errText = 'Internet connection is required to verify code. Please connect to the internet and try again.';
            }
            if (typeof showToast === 'function') showToast(errText, 'warning', 5000);
        } finally {
            if (btn) {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }
    };

    window.handleResetPasswordSubmit = async function (e) {
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
            let errText = error.message || 'Failed to reset password.';
            if (!navigator.onLine || window.isAppOnline === false ||
                errText.toLowerCase().includes('failed to fetch') ||
                errText.toLowerCase().includes('networkerror') ||
                errText.toLowerCase().includes('load failed') ||
                errText.toLowerCase().includes('typeerror')) {
                errText = 'Internet connection is required to reset your password. Please connect to the internet and try again.';
            }
            if (typeof showToast === 'function') showToast(errText, 'warning', 5000);
            if (btn) {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }
    };

    window.triggerGoogleLogin = function (event) {
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

        // Check if accessed from a private IP or unsupported host for Google OAuth
        const hn = window.location.hostname;
        const isPrivateIp = /^(\d{1,3}\.){3}\d{1,3}$/.test(hn) ||
                            hn.startsWith('192.168.') ||
                            hn.startsWith('10.') ||
                            hn.startsWith('172.') ||
                            window.location.protocol === 'file:' ||
                            window.location.protocol === 'capacitor:';

        // Google OAuth strictly forbids private IP addresses in redirect_uri.
        // When accessed via USB tethering/local IP, warn the user clearly instead of letting Google show the 400 malformed error.
        if (isPrivateIp && hn !== 'localhost' && hn !== '127.0.0.1') {
            resetBtns();
            const msg = 'Google Sign-In requires the official domain (https://app.intan-elyu.online). Google strictly forbids local IP addresses (192.168.x.x). Please log in with your Email & Password or use Offline Mode.';
            if (typeof showToast === 'function') {
                showToast(msg, 'warning', 6000);
            }
            if (typeof window.openAuthCancelModal === 'function') {
                window.openAuthCancelModal(msg);
            } else {
                alert(msg);
            }
            return;
        }

        // Direct Google OAuth 2.0 Authorization Redirection (Universal for Android APK, WebViews & Web)
        // Must match registered authorized redirect URI in Google Cloud Console
        const redirectUri = 'https://app.intan-elyu.online/index.php';

        const isNative = Boolean(
            (window.Capacitor && typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform()) ||
            (navigator.userAgent && navigator.userAgent.includes('IntanElyuAPK'))
        );

        try {
            sessionStorage.setItem('intan_elyu_google_oauth_pending', 'true');
        } catch (e) { }

        const stateObj = { timestamp: Date.now(), returnView: 'auth', fromApp: isNative };
        const stateStr = encodeURIComponent(JSON.stringify(stateObj));
        const googleAuthUrl = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${encodeURIComponent(clientId)}&redirect_uri=${encodeURIComponent(redirectUri)}&response_type=token&scope=email%20profile%20openid&prompt=select_account&state=${stateStr}`;

        // In Capacitor native mobile platform, open in system browser to prevent Google WebView blocking (Error 403: disallowed_useragent)
        if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Browser) {
            window.Capacitor.Plugins.Browser.open({ url: googleAuthUrl }).catch(function () {
                window.location.href = googleAuthUrl;
            });
            resetBtns();
        } else {
            window.location.href = googleAuthUrl;
        }
    };

    window.handleCredentialResponse = async function (response, onDone) {
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

            window.purgeAllUserSessionCaches();
            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('auth_user_backup', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            localStorage.setItem('Intan_Elyu_Token', data.token);
            localStorage.setItem('api_token', data.token);
            localStorage.setItem('intan_elyu_real_token_backup', data.token);
            if (window.AppStorage) {
                try {
                    window.AppStorage.setItem('auth_user', data.user);
                    window.AppStorage.setItem('intan_elyu_token', data.token);
                } catch (e) { }
            }

            try {
                sessionStorage.removeItem('intan_elyu_google_oauth_pending');
                sessionStorage.removeItem('elyu_show_offline_bypass_notice');
            } catch (e) { }

            if (typeof window.seedUserVouchersAndPoints === 'function') {
                window.seedUserVouchersAndPoints(data.token, data.user, data.vouchers);
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
            let errText = error.message || 'Google sign-in could not be completed.';
            if (!navigator.onLine || window.isAppOnline === false ||
                errText.toLowerCase().includes('failed to fetch') ||
                errText.toLowerCase().includes('networkerror') ||
                errText.toLowerCase().includes('load failed') ||
                errText.toLowerCase().includes('typeerror')) {
                errText = 'Internet connection is required to log in with Google. Please connect to the internet and try again.';
            }
            if (typeof showToast === 'function') showToast(errText, 'warning', 5000);
            if (typeof window.openAuthCancelModal === 'function') {
                window.openAuthCancelModal(errText);
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



    // Password Strength Focus Helpers
    window.onPasswordFocus = function () {
        var container = document.getElementById('pwd-strength-container');
        if (container) {
            container.style.display = 'block';
            if (typeof window.validateRegisterFormInline === 'function') {
                window.validateRegisterFormInline();
            }
            setTimeout(function () {
                try {
                    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (e) { }
            }, 180);
        }
    };

    window.onPasswordBlur = function () {
        var pwdEl = document.getElementById('reg-password');
        var container = document.getElementById('pwd-strength-container');
        if (pwdEl && pwdEl.value.length === 0 && container) {
            container.style.display = 'none';
        }
    };

    // Keyboard-aware scrolling, sequential focus, and an explicit keyboard dismiss control.
    (function setupKeyboardAdaptiveAuth() {
        var dismissButton = document.getElementById('auth-keyboard-dismiss');
        var authContainer = document.querySelector('.auth-container');
        var viewport = window.visualViewport;
        var largestViewportHeight = viewport ? viewport.height : window.innerHeight;

        function isEditable(element) {
            if (!element) return false;
            return element.matches('input:not([type="hidden"]):not([type="checkbox"]):not([readonly]), textarea');
        }

        function keepFocusedFieldVisible(element) {
            if (!isEditable(element)) return;
            var visibleTop = viewport ? viewport.offsetTop : 0;
            var visibleBottom = visibleTop + (viewport ? viewport.height : window.innerHeight);
            var controlSpace = 72;
            var rect = element.closest('.input-group')?.getBoundingClientRect() || element.getBoundingClientRect();
            var delta = 0;

            if (rect.bottom > visibleBottom - controlSpace) {
                delta = rect.bottom - (visibleBottom - controlSpace);
            } else if (rect.top < visibleTop + 16) {
                delta = rect.top - (visibleTop + 16);
            }

            if (Math.abs(delta) > 1) {
                var scroller = authContainer && authContainer.scrollHeight > authContainer.clientHeight
                    ? authContainer
                    : document.scrollingElement;
                if (scroller && typeof scroller.scrollBy === 'function') {
                    scroller.scrollBy({ top: delta, behavior: 'smooth' });
                }
            }
        }

        function setKeyboardState(visible, keyboardHeight) {
            document.body.classList.toggle('auth-keyboard-visible', visible);
            if (!dismissButton) return;

            dismissButton.hidden = !visible;
            if (!visible) {
                dismissButton.style.removeProperty('top');
                document.documentElement.style.setProperty('--auth-keyboard-height', '0px');
                return;
            }

            var height = Math.max(0, keyboardHeight || 0);
            document.documentElement.style.setProperty('--auth-keyboard-height', height + 'px');
            var buttonTop = (viewport ? viewport.offsetTop + viewport.height : window.innerHeight) - 58;
            dismissButton.style.top = Math.max(12, buttonTop) + 'px';
            window.requestAnimationFrame(function () {
                keepFocusedFieldVisible(document.activeElement);
            });
        }

        function updateFromVisualViewport() {
            if (!viewport) return;
            if (!isEditable(document.activeElement)) {
                largestViewportHeight = Math.max(largestViewportHeight, viewport.height);
                setKeyboardState(false, 0);
                return;
            }
            var keyboardHeight = Math.max(0, largestViewportHeight - viewport.height - viewport.offsetTop);
            setKeyboardState(keyboardHeight > 100, keyboardHeight);
        }

        function dismissKeyboard() {
            var activeElement = document.activeElement;
            if (isEditable(activeElement)) activeElement.blur();
            try {
                var keyboard = window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Keyboard;
                if (keyboard && typeof keyboard.hide === 'function') keyboard.hide();
            } catch (err) { }
            setKeyboardState(false, 0);
        }

        if (dismissButton) dismissButton.addEventListener('click', dismissKeyboard);

        document.addEventListener('focusin', function (event) {
            if (!isEditable(event.target)) return;
            window.setTimeout(function () {
                updateFromVisualViewport();
                keepFocusedFieldVisible(event.target);
            }, 180);
        }, true);

        document.addEventListener('focusout', function () {
            window.setTimeout(function () {
                if (!isEditable(document.activeElement)) setKeyboardState(false, 0);
            }, 120);
        }, true);

        document.querySelectorAll('#form-login, #form-register').forEach(function (form) {
            form.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' || !isEditable(event.target)) return;
                var fields = Array.from(form.querySelectorAll('[data-auth-focus-order]'))
                    .filter(function (field) { return !field.disabled && field.offsetParent !== null; });
                var index = fields.indexOf(event.target);
                if (index < 0) return;
                event.preventDefault();
                if (index < fields.length - 1) {
                    fields[index + 1].focus();
                } else {
                    dismissKeyboard();
                }
            });
        });

        if (viewport) {
            viewport.addEventListener('resize', updateFromVisualViewport);
            viewport.addEventListener('scroll', updateFromVisualViewport);
        }

        try {
            var nativeKeyboard = window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Keyboard;
            if (nativeKeyboard) {
                nativeKeyboard.addListener('keyboardWillShow', function (event) {
                    setKeyboardState(true, event && event.keyboardHeight);
                });
                nativeKeyboard.addListener('keyboardDidShow', function (event) {
                    setKeyboardState(true, event && event.keyboardHeight);
                });
                nativeKeyboard.addListener('keyboardWillHide', function () { setKeyboardState(false, 0); });
                nativeKeyboard.addListener('keyboardDidHide', function () { setKeyboardState(false, 0); });
            }
        } catch (err) { }
    })();

    if (typeof window.initAuthView === 'function') {
        window.initAuthView();
    }
})();
</script>
