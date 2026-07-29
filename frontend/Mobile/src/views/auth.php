<!-- Auth View (Login, Register & Forgot Password) -->

<div class="auth-container">
    <!-- Top Blue Section -->
    <div class="auth-top">
        <div class="logo-container">
            <img id="auth-logo-img" src="assets/img/logo.png" alt="Intan Elyu Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%; display: block;">
            <script>
                (function(){
                    const img = document.getElementById('auth-logo-img');
                    if (img) {
                        const bUrl = window.backendUrl || 'https://api.intan-elyu.online';
                        img.src = bUrl + '/api/image/storage/Logo/intan-elyu-logo.png';
                        img.onerror = function() { this.onerror = null; this.src = 'assets/img/logo.png'; };
                    }
                })();
            </script>
        </div>
        <h1 id="auth-title" style="color: #ffffff; font-weight: 800;">Welcome to Elyu</h1>
        
        <!-- Animated Seamless SVG Wave -->
        <div class="wave-bottom">
            <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
                <path class="wave-layer wave-1" fill="rgba(30,41,59,0.3)" d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,100 L0,100 Z"></path>
                <path class="wave-layer wave-2" fill="rgba(30,41,59,0.5)" d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,100 L0,100 Z"></path>
                <path class="wave-layer wave-3" fill="#1e293b" d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,100 L0,100 Z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Bottom White Section -->
    <div class="auth-bottom">
        <div class="auth-tabs" id="auth-tabs">
            <div class="auth-tab active" id="tab-login" onclick="toggleAuthMode(false)">Login</div>
            <div class="auth-tab" id="tab-register" onclick="toggleAuthMode(true)">Register</div>
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
                    <a href="#" class="forgot-pwd" onclick="showForgotPassword(event)">Forgot Password?</a>
                    
                    <button type="submit" id="btn-login" class="btn-circle-submit">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div style="display:flex; flex-direction:column; align-items:center; margin-top:20px; width:100%;">
                    <div style="width:100%; display:flex; align-items:center; gap:8px; margin-bottom:16px;">
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                        <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px;">Or Connect With</span>
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                    </div>
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin(event)" style="width:100%; padding:14px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
                        <svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.53-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-8.7c0-.18-.01-.35-.05-.47z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.11 0-5.74-2.11-6.68-4.96H1.21v3.15C3.18 21.88 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.32 14.24A7.16 7.16 0 0 1 5 12c0-.79.13-1.57.32-2.31V6.54H1.21A11.96 11.96 0 0 0 0 12c0 1.92.45 3.74 1.21 5.38l4.11-3.14z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.18 2.12 1.21 5.46l4.11 3.22c.94-2.85 3.57-4.93 6.68-4.93z"/>
                        </svg>
                        <span>Sign in with Google</span>
                    </button>
                </div>
            </div>
            
            <!-- Panel 2: Register -->
            <div class="form-panel register-form">
                <form id="form-register" onsubmit="handleRegister(event)">
                    <div style="display: flex; gap: 10px;">
                        <div class="input-group" style="flex: 1;">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="reg-first-name" class="auth-input" placeholder="First Name" required>
                        </div>
                        <div class="input-group" style="flex: 1;">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="reg-last-name" class="auth-input" placeholder="Last Name" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-mobile-screen"></i>
                        <input type="email" id="reg-email" class="auth-input" placeholder="Email Address" required>
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="reg-password" class="auth-input" placeholder="Create Password" required>
                        <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('reg-password', this)"></i>
                    </div>
                    
                    <div style="font-size: 11px; color: rgba(255,255,255,0.85); margin: 10px 0 16px 4px; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="reg-privacy-checkbox" class="circular-checkbox" required>
                        <label for="reg-privacy-checkbox" style="cursor: pointer; margin: 0; line-height: 1.35;">
                            I agree to the <a href="#" onclick="openPrivacyPolicyModal(event)" style="color: #38bdf8; font-weight: 700; text-decoration: underline;">Privacy Policy & 2FA Terms</a>.
                        </label>
                    </div>
                    
                    <button type="submit" id="btn-register" class="btn-circle-submit">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div style="display:flex; flex-direction:column; align-items:center; margin-top:20px; width:100%;">
                    <div style="width:100%; display:flex; align-items:center; gap:8px; margin-bottom:16px;">
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                        <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px;">Or Connect With</span>
                        <hr style="flex:1; border:none; border-top:1.5px dashed rgba(255,255,255,0.15);">
                    </div>
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin(event)" style="width:100%; padding:14px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
                        <svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.53-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-8.7c0-.18-.01-.35-.05-.47z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.11 0-5.74-2.11-6.68-4.96H1.21v3.15C3.18 21.88 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.32 14.24A7.16 7.16 0 0 1 5 12c0-.79.13-1.57.32-2.31V6.54H1.21A11.96 11.96 0 0 0 0 12c0 1.92.45 3.74 1.21 5.38l4.11-3.14z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.18 2.12 1.21 5.46l4.11 3.22c.94-2.85 3.57-4.93 6.68-4.93z"/>
                        </svg>
                        <span>Sign up with Google</span>
                    </button>
                </div>
            </div>

            <!-- Panel 3: Forgot Password -->
            <div class="form-panel forgot-form">
                <a href="#" class="back-link" onclick="hideForgotPassword(event)">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>

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

                <div id="fp-success-state" style="display: none; text-align: center; padding: 10px 0;">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.3); display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 20px; margin: 0 auto 10px auto;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <h3 style="margin: 0 0 4px 0; color: white; font-size: 18px; font-weight: 800;">Reset Code Sent</h3>
                    <p style="color: rgba(255,255,255,0.7); font-size: 12px; margin: 0 0 16px 0;">We sent a 6-digit reset code to <br><strong id="fp-target-email" style="color: #38bdf8; font-family: monospace;"></strong></p>

                    <form id="form-fp-reset" onsubmit="handleResetPasswordOtp(event)">
                        <!-- 6 Individual Digit Input Boxes -->
                        <div class="otp-boxes-container" style="margin: 14px 0 16px 0;">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" oninput="handleFpOtpBoxInput(this, 0)" onkeydown="handleFpOtpBoxKeydown(this, event, 0)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 1)" onkeydown="handleFpOtpBoxKeydown(this, event, 1)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 2)" onkeydown="handleFpOtpBoxKeydown(this, event, 2)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 3)" onkeydown="handleFpOtpBoxKeydown(this, event, 3)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 4)" onkeydown="handleFpOtpBoxKeydown(this, event, 4)" onpaste="handleFpOtpPaste(event)">
                            <input type="text" class="otp-box fp-otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" oninput="handleFpOtpBoxInput(this, 5)" onkeydown="handleFpOtpBoxKeydown(this, event, 5)" onpaste="handleFpOtpPaste(event)">
                        </div>
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

                        <button type="submit" id="fp-reset-btn" class="btn-circle-submit" style="margin-bottom: 14px;">
                            <i class="fa-solid fa-check"></i>
                        </button>

                        <div style="font-size: 12px; color: rgba(255,255,255,0.6); margin-top: 10px;">
                            Didn't receive email? 
                            <button type="button" id="fp-resend-btn" onclick="handleResendFpEmail(event)" style="background: none; border: none; color: #38bdf8; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">
                                Resend Code
                            </button>
                            <span id="fp-countdown-text" style="display: none; color: #f59e0b; font-weight: 700;">(Resend in <span id="fp-countdown-sec">45</span>s)</span>
                        </div>
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

<!-- Privacy Policy Agreement Modal (Required Before Account Registration) -->
<div id="privacy-policy-modal" class="auth-2fa-overlay" style="display: none;">
    <div class="auth-2fa-card" style="max-width: 440px; text-align: left; padding: 24px 20px; border-radius: 28px; max-height: 85vh; display: flex; flex-direction: column;">
        <button type="button" class="auth-2fa-close" onclick="closePrivacyPolicyModal()"><i class="fa-solid fa-xmark"></i></button>
        
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(56, 189, 248, 0.15); border: 1.5px solid #38bdf8; display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 20px; flex-shrink: 0;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff;">Privacy Policy & Terms</h3>
                <span style="font-size: 11px; color: #38bdf8; font-weight: 700;">Step 1 of 2 · Data Protection Terms</span>
            </div>
        </div>

        <div style="font-size: 12px; color: rgba(226, 232, 240, 0.9); line-height: 1.6; overflow-y: auto; padding-right: 6px; margin-bottom: 16px; flex: 1; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 14px; background: rgba(15, 23, 42, 0.5);">
            <p style="margin-top: 0;">Welcome to <strong>Intan Elyu Tourism Management System</strong>. Before your account registration is created, please review and accept our privacy policy and security terms:</p>
            
            <h4 style="color: #38bdf8; margin: 12px 0 4px 0; font-size: 13px;"><i class="fa-solid fa-database" style="margin-right: 6px;"></i> 1. Information We Collect</h4>
            <p style="margin: 0 0 10px 0;">We store your full name, email address, password hashes, and optional profile preferences to deliver personalized itinerary recommendations.</p>
            
            <h4 style="color: #38bdf8; margin: 12px 0 4px 0; font-size: 13px;"><i class="fa-solid fa-location-dot" style="margin-right: 6px;"></i> 2. Location & Check-In Data</h4>
            <p style="margin: 0 0 10px 0;">Location coordinates are accessed strictly during active check-in tasks to verify XP rewards. We do not track location in the background or sell location data.</p>
            
            <h4 style="color: #38bdf8; margin: 12px 0 4px 0; font-size: 13px;"><i class="fa-solid fa-lock" style="margin-right: 6px;"></i> 3. 2FA Security & Verification</h4>
            <p style="margin: 0 0 10px 0;">After accepting this policy, a mandatory 2-Factor Authentication (2FA) email code will be issued to your email address to confirm identity before account activation.</p>

            <h4 style="color: #38bdf8; margin: 12px 0 4px 0; font-size: 13px;"><i class="fa-solid fa-user-shield" style="margin-right: 6px;"></i> 4. Privacy & Leaderboards</h4>
            <p style="margin: 0 0 4px 0;">Your email remains confidential. You can set your profile to private at any time in App Settings to hide rankings on public leaderboards.</p>
        </div>

        <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 14px; padding: 10px 12px; margin-bottom: 16px; font-size: 11px; color: #38bdf8; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="chk-accept-privacy" class="circular-checkbox">
            <label for="chk-accept-privacy" style="cursor: pointer; margin: 0; line-height: 1.3;">I have read, understood, and accept the Privacy Policy & Terms of Service.</label>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="closePrivacyPolicyModal()" class="auth-2fa-btn-primary" style="flex: 1; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #94a3b8; font-size: 13px;">
                Decline
            </button>
            <button type="button" id="btn-accept-policy-proceed" onclick="acceptPolicyAndProceed()" class="auth-2fa-btn-primary" style="flex: 1.8; background: linear-gradient(135deg, #38bdf8, #2563eb); font-size: 13px;">
                <i class="fa-solid fa-check" style="margin-right: 6px;"></i>Accept & 2FA Setup →
            </button>
        </div>
    </div>
</div>

<style>
.login-success-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 999999;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
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
    background: rgba(15, 23, 42, 0.96);
    border: 1.5px solid rgba(56, 189, 248, 0.4);
    border-radius: 24px;
    padding: 28px 24px;
    max-width: 340px;
    width: 100%;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 35px rgba(56, 189, 248, 0.15);
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
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(56, 189, 248, 0.15);
    border: 2px solid #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px auto;
    box-shadow: 0 0 22px rgba(56, 189, 248, 0.35);
    transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
}
.success-icon-badge.is-done {
    background: rgba(52, 199, 89, 0.15);
    border-color: #34c759;
    box-shadow: 0 0 22px rgba(52, 199, 89, 0.35);
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

    function toggleAuthMode(isRegister) {
        tabsContainer.style.display = 'flex';
        wrapper.classList.remove('show-forgot', 'show-otp');
        
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

    function showForgotPassword(e) {
        if(e) e.preventDefault();
        
        // Hide tabs
        tabsContainer.style.display = 'none';
        
        // Reset forgot form
        document.getElementById('fp-form-state').style.display = 'block';
        document.getElementById('fp-success-state').style.display = 'none';
        document.getElementById('fp-email').value = '';
        
        const btn = document.getElementById('fp-btn');
        btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
        btn.disabled = false;

        wrapper.classList.remove('show-register');
        wrapper.classList.add('show-forgot');
        updateTitleWithTransition('Account Recovery');
    }

    function hideForgotPassword(e) {
        if(e) e.preventDefault();
        // Restore tabs
        tabsContainer.style.display = 'flex';
        wrapper.classList.remove('show-forgot', 'show-register');
        tabRegister.classList.remove('active');
        tabLogin.classList.add('active');
        updateTitleWithTransition('Welcome to Elyu');
    }

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

        try {
            const response = await fetch(backendUrl + '/api/auth/login', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Invalid login credentials.');
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

    window.openPrivacyPolicyModal = function(e) {
        if (e) e.preventDefault();
        const modal = document.getElementById('privacy-policy-modal');
        if (!modal) return;
        const chk = document.getElementById('chk-accept-privacy');
        if (chk) chk.checked = false;
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    };

    window.closePrivacyPolicyModal = function() {
        const modal = document.getElementById('privacy-policy-modal');
        if (modal) modal.classList.remove('active');
        setTimeout(() => {
            if (modal) modal.style.display = 'none';
        }, 300);
    };

    window.handleRegister = async function(e) {
        if (e) e.preventDefault();
        const pwd = document.getElementById('reg-password')?.value || '';
        const firstName = (document.getElementById('reg-first-name')?.value || '').trim();
        const lastName = (document.getElementById('reg-last-name')?.value || '').trim();
        const name = `${firstName} ${lastName}`.trim();
        const email = (document.getElementById('reg-email')?.value || '').trim();

        if (!firstName || !lastName || !email || !pwd) {
            if (typeof showToast === 'function') showToast('Please fill in all registration fields.');
            return;
        }

        if (pwd.length < 8) {
            if (typeof showToast === 'function') showToast('Password must be at least 8 characters long.');
            return;
        }

        const btn = document.getElementById('btn-register');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';
            btn.disabled = true;
        }

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
                let errMsg = data.message || 'Registration failed';
                if (data.errors) {
                    const details = Object.values(data.errors).flat().join(' ');
                    if (details) errMsg += ': ' + details;
                }
                throw new Error(errMsg);
            }

            // Set session cache for sequential dashboard onboarding modals:
            // Step 1: Privacy Terms Modal -> Step 2: Complete Profile Modal -> Step 3: 2FA OTP Modal
            sessionStorage.setItem('onboarding_active', '1');
            sessionStorage.setItem('onboarding_step', '1');
            sessionStorage.setItem('pending_reg_email', email);
            sessionStorage.setItem('pending_reg_name', name);

            if (typeof showToast === 'function') showToast('Account created! Welcome to Intan Elyu!');
            
            // Redirect to dashboard to start onboarding sequence
            window.location.href = '?view=dashboard';
        } catch (error) {
            console.error('Register Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Register';
                btn.disabled = false;
            }
        }
    };

    window.acceptPolicyAndProceed = async function() {
        const chk = document.getElementById('chk-accept-privacy');
        if (chk && !chk.checked) {
            if (typeof showToast === 'function') showToast('Please check the box to accept the Privacy Policy & Security Terms.');
            return;
        }

        const regChk = document.getElementById('reg-privacy-checkbox');
        if (regChk) regChk.checked = true;

        closePrivacyPolicyModal();
        await submitRegistrationAndTrigger2FA();
    };

    window.submitRegistrationAndTrigger2FA = async function() {
        const pwd = document.getElementById('reg-password')?.value || '';
        const firstName = (document.getElementById('reg-first-name')?.value || '').trim();
        const lastName = (document.getElementById('reg-last-name')?.value || '').trim();
        const name = `${firstName} ${lastName}`.trim();
        const email = document.getElementById('reg-email')?.value || '';

        const btn = document.getElementById('btn-register');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
        }

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
                let errMsg = data.message || 'Registration failed';
                if (data.errors) {
                    const details = Object.values(data.errors).flat().join(' ');
                    if (details) errMsg += ': ' + details;
                }
                throw new Error(errMsg);
            }

            // STEP 2: Transition into 2FA Email OTP Verification after policy agreement & account creation
            if (typeof showToast === 'function') showToast(data.message || 'Privacy policy accepted! 2FA verification code sent to your email.');
            window.setTxt('otp-target-email', data.email || email);
            tabsContainer.style.display = 'none';
            wrapper.classList.remove('show-register', 'show-forgot');
            wrapper.classList.add('show-otp');
            updateTitleWithTransition('Verify 2FA Email Code');
            
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
                btn.disabled = false;
            }
            setTimeout(() => {
                const boxes = document.querySelectorAll('.otp-box');
                boxes.forEach(b => b.value = '');
                if (boxes[0]) boxes[0].focus();
            }, 300);
        } catch (error) {
            console.error('Register Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
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
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
        }
        
        const email = document.getElementById('fp-email').value;

        try {
            const response = await fetch(backendUrl + '/api/auth/forgot-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email })
            });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || data.error || 'Failed to send reset code.');
            }

            const targetEmailEl = document.getElementById('fp-target-email');
            if (targetEmailEl) targetEmailEl.textContent = data.email || email;

            document.getElementById('fp-form-state').style.display = 'none';
            document.getElementById('fp-success-state').style.display = 'block';
            if (typeof showToast === 'function') showToast('Security reset code sent to ' + (data.email || email));
            startFpResendTimer();
            setTimeout(() => {
                const fpBoxes = document.querySelectorAll('.fp-otp-box');
                fpBoxes.forEach(b => b.value = '');
                if (fpBoxes[0]) fpBoxes[0].focus();
            }, 300);
        } catch (error) {
            console.error('Forgot Password Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            if (btn) {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
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

    window.handleResetPasswordOtp = async function(e) {
        e.preventDefault();
        const email = document.getElementById('fp-target-email').textContent || document.getElementById('fp-email').value;
        const boxes = document.querySelectorAll('.fp-otp-box');
        const otp = Array.from(boxes).map(b => b.value).join('');
        const newPassword = document.getElementById('fp-new-password').value;
        const confirmPassword = document.getElementById('fp-confirm-password').value;

        if (otp.length < 6) {
            if (typeof showToast === 'function') showToast('Please enter all 6 digits of your security code.');
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

        const btn = document.getElementById('fp-reset-btn');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;

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
                throw new Error(data.message || data.error || 'Failed to reset password.');
            }

            if (typeof showToast === 'function') showToast('🎉 Password reset successfully! Please sign in.');
            
            // Auto fill email into login form
            const loginEmailInput = document.getElementById('login-email');
            if (loginEmailInput) loginEmailInput.value = email;
            
            // Clear inputs and return back to Login tab
            boxes.forEach(b => b.value = '');
            document.getElementById('fp-new-password').value = '';
            document.getElementById('fp-confirm-password').value = '';
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            
            hideForgotPassword();
        } catch (error) {
            console.error('Reset Password OTP Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    };

    window.triggerGoogleLogin = function(event) {
        const googleBtns = document.querySelectorAll('.btn-google');
        googleBtns.forEach((btn) => {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Connecting to Google...';
            btn.disabled = true;
        });

        const clientId = window.GOOGLE_CLIENT_ID || localStorage.getItem('intan_elyu_google_client_id') || '620598190857-37a0ucobfd4b3rct7ofti8rtvl3qt884.apps.googleusercontent.com';

        function resetBtns() {
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

        const isNativeMobile = !!(window.Capacitor || (window.Capacitor && window.Capacitor.isNativePlatform()) || navigator.userAgent.includes('wv') || navigator.userAgent.includes('Android'));

        // On mobile APK / WebView, use clean OAuth Redirect Flow to avoid gsi/transform postMessage WebView hangs
        if (isNativeMobile) {
            performFallbackRedirect();
            return;
        }

        // 1. Try Google OAuth2 Token Client (Desktop / standard web)
        if (window.google && window.google.accounts && window.google.accounts.oauth2) {
            try {
                const tokenClient = window.google.accounts.oauth2.initTokenClient({
                    client_id: clientId,
                    scope: 'email profile openid',
                    callback: (tokenResponse) => {
                        if (tokenResponse && tokenResponse.access_token) {
                            fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
                                headers: { Authorization: `Bearer ${tokenResponse.access_token}` }
                            })
                            .then(res => res.json())
                            .then(profile => {
                                if (profile && profile.email) {
                                    window.handleCredentialResponse({ profile: profile }, resetBtns);
                                } else {
                                    throw new Error('Unable to fetch profile from Google.');
                                }
                            })
                            .catch(err => {
                                console.error('Google Userinfo Error:', err);
                                resetBtns();
                                if (typeof showToast === 'function') showToast('Google login failed.');
                            });
                        } else {
                            resetBtns();
                        }
                    },
                    error_callback: (err) => {
                        console.warn("Google OAuth popup error, falling back:", err);
                        performFallbackRedirect();
                    }
                });
                tokenClient.requestAccessToken({ prompt: 'select_account' });
                return;
            } catch (e) {
                console.warn("OAuth2 initTokenClient exception:", e);
            }
        }

        // 2. Try Google One Tap prompt
        if (window.google && window.google.accounts && window.google.accounts.id) {
            try {
                window.google.accounts.id.initialize({
                    client_id: clientId,
                    callback: function(response) {
                        window.handleCredentialResponse(response, resetBtns);
                    }
                });
                window.google.accounts.id.prompt((notification) => {
                    if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
                        performFallbackRedirect();
                    }
                });
                return;
            } catch (e) {
                console.warn("GSI prompt exception:", e);
            }
        }

        performFallbackRedirect();

        function performFallbackRedirect() {
            let redirectUri = window.location.origin + '/index.php';
            if (window.location.pathname && window.location.pathname !== '/' && window.location.pathname.endsWith('.php')) {
                redirectUri = window.location.origin + window.location.pathname;
            }
            const googleAuthUrl = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${encodeURIComponent(clientId)}&redirect_uri=${encodeURIComponent(redirectUri)}&response_type=token&scope=email%20profile%20openid&prompt=select_account`;
            window.location.href = googleAuthUrl;
        }
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

            const fetchRes = await fetch(backendUrl + '/api/auth/google', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payloadData)
            });
            const data = await fetchRes.json();
            
            if (!fetchRes.ok) {
                throw new Error(data.message || data.error || 'Google login failed');
            }
            
            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            
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
            window.openAuthCancelModal(error.message || 'Google sign-in could not be completed.');
        }
    };

    (function checkGoogleOAuthRedirect() {
        const hash = window.location.hash || window.location.search;
        if (hash && (hash.includes('access_token=') || hash.includes('id_token='))) {
            const rawParams = hash.startsWith('#') ? hash.substring(1) : (hash.startsWith('?') ? hash.substring(1) : hash);
            const params = new URLSearchParams(rawParams);
            const accessToken = params.get('access_token');
            if (accessToken) {
                // Clean URL hash so token is not left in history
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname + '?view=auth');
                }
                
                const googleBtns = document.querySelectorAll('.btn-google');
                googleBtns.forEach(btn => {
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Logging in with Google...';
                    btn.disabled = true;
                });

                fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
                    headers: { Authorization: `Bearer ${accessToken}` }
                })
                .then(res => res.json())
                .then(profile => {
                    if (profile && profile.email) {
                        window.handleCredentialResponse({ profile: profile });
                    } else {
                        throw new Error('Unable to retrieve profile from Google.');
                    }
                })
                .catch(err => {
                    console.error('Google OAuth redirect error:', err);
                    googleBtns.forEach(btn => {
                        btn.innerHTML = '<span>Sign in with Google</span>';
                        btn.disabled = false;
                    });
                    if (typeof showToast === 'function') showToast('Google login failed. Please try again.');
                });
            }
        }
    })();
</script>
