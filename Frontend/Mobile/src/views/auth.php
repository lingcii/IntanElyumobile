<!-- Auth View (Login, Register & Forgot Password) -->

<div class="auth-container">
    <!-- Top Blue Section -->
    <div class="auth-top">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Intan Elyu Logo">
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
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin()" style="width:100%; padding:14px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
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
                    <div class="input-group">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="reg-name" class="auth-input" placeholder="Full Name" required>
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
                    <button type="button" class="btn-google" onclick="window.triggerGoogleLogin()" style="width:100%; padding:14px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.04); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); color:white; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:background 0.2s, transform 0.1s;">
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
                        <p>Enter your email to receive a reset link.</p>
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

                <div id="fp-success-state" style="display: none; text-align: center; padding: 20px 0;">
                    <i class="fa-solid fa-check-circle" style="font-size: 40px; color: #38bdf8; margin-bottom: 16px;"></i>
                    <h3 style="margin: 0 0 10px 0; color: white;">Email Sent!</h3>
                    <p style="color: rgba(255,255,255,0.6); font-size: 14px; margin-bottom: 30px;">Check your inbox for the reset link.</p>
                    <button class="btn-circle-submit" type="button" onclick="hideForgotPassword(event)">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Login Success Modal -->
<div id="login-success-modal" class="login-success-overlay">
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

<style>
.login-success-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: rgba(15, 23, 42, 0.82);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}
.login-success-overlay.active {
    opacity: 1;
    visibility: visible;
}
.login-success-card {
    background: rgba(30, 41, 59, 0.96);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 35px rgba(56, 189, 248, 0.25);
    border-radius: 28px;
    padding: 36px 30px;
    width: 100%;
    max-width: 330px;
    text-align: center;
    transform: scale(0.85) translateY(20px);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.login-success-overlay.active .login-success-card {
    transform: scale(1) translateY(0);
}
.success-icon-badge {
    width: 76px;
    height: 76px;
    margin: 0 auto 20px auto;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
    transition: background 0.5s ease, box-shadow 0.5s ease, transform 0.4s ease;
}
.success-icon-badge.is-done {
    background: linear-gradient(135deg, #38bdf8 0%, #10b981 100%);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.45);
    transform: scale(1.08);
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
    // Auto-cleanup legacy/mock client IDs from local storage
    (function() {
        const storedId = localStorage.getItem('intan_elyu_google_client_id');
        if (storedId && (storedId.includes('xxx') || storedId.includes('102834710293'))) {
            localStorage.removeItem('intan_elyu_google_client_id');
        }
    })();

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
        wrapper.classList.remove('show-forgot');
        
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

    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

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

        // Transition Stage: Spinner turns to Green Checkmark after 5 seconds
        setTimeout(() => {
            if (spinner) spinner.style.display = 'none';
            if (checkmark) checkmark.style.display = 'block';
            if (badge) badge.classList.add('is-done');
            if (titleEl) titleEl.textContent = 'Successfully Logged In!';
            if (nameEl) {
                nameEl.textContent = (user && user.name) ? 'Welcome back, ' + user.name + '!' : 'Welcome back to Intan Elyu!';
            }
        }, 5000);

        // Final Stage: Navigate to Dashboard after 6.5 seconds total
        setTimeout(() => {
            if (typeof navigateTo === 'function') navigateTo('dashboard');
        }, 6500);
    };

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
                throw new Error(data.message || data.error || 'Login failed');
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

    window.handleRegister = async function(e) {
        e.preventDefault();
        const pwd = document.getElementById('reg-password').value;
        const name = document.getElementById('reg-name').value;
        const email = document.getElementById('reg-email').value;

        const btn = document.getElementById('btn-register');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;

        try {
            const response = await fetch(backendUrl + '/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ name: name, email: email, password: pwd, password_confirmation: pwd })
            });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Registration failed');
            }

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            
            if (typeof showToast === 'function') showToast('Account created successfully!');
            if (typeof navigateTo === 'function') navigateTo('dashboard');
        } catch (error) {
            console.error('Register Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            btn.disabled = false;
        }
    };

    window.handleForgotPassword = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('fp-btn');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;
        
        const email = document.getElementById('fp-email').value;

        try {
            const response = await fetch(backendUrl + '/api/auth/forgot-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email })
            });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || data.error || 'Failed to send reset link.');
            }

            document.getElementById('fp-form-state').style.display = 'none';
            document.getElementById('fp-success-state').style.display = 'block';
        } catch (error) {
            console.error('Forgot Password Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    };

    window.triggerGoogleLogin = function(event) {
        const googleBtn = event ? event.currentTarget : document.querySelector('.btn-google');
        const oldHtml = googleBtn ? googleBtn.innerHTML : '';
        if (googleBtn) {
            googleBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Connecting to Google...';
            googleBtn.disabled = true;
        }

        const clientId = window.GOOGLE_CLIENT_ID || localStorage.getItem('intan_elyu_google_client_id') || '874613490302-qno8lkqoujur0db888hg72hogjv6cp5v.apps.googleusercontent.com';

        if (!clientId) {
            if (typeof showToast === 'function') showToast('Google Client ID is required to connect to Google Cloud.');
            if (googleBtn) {
                googleBtn.innerHTML = oldHtml;
                googleBtn.disabled = false;
            }
            return;
        }

        function promptGoogle() {
            if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
                try {
                    google.accounts.id.initialize({
                        client_id: clientId,
                        callback: window.handleCredentialResponse,
                        auto_select: false,
                        use_fedcm_for_prompt: false
                    });
                    google.accounts.id.prompt((notification) => {
                        if ((notification.isNotDisplayed && notification.isNotDisplayed()) || (notification.isSkippedMoment && notification.isSkippedMoment())) {
                            if (google.accounts.oauth2) {
                                const tokenClient = google.accounts.oauth2.initTokenClient({
                                    client_id: clientId,
                                    scope: 'email profile openid',
                                    callback: async (tokenResponse) => {
                                        if (tokenResponse && tokenResponse.access_token) {
                                            try {
                                                const userInfoRes = await fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
                                                    headers: { Authorization: `Bearer ${tokenResponse.access_token}` }
                                                });
                                                const profile = await userInfoRes.json();
                                                window.handleCredentialResponse({ profile: profile });
                                            } catch (e) {
                                                console.error('Fetch Google profile error:', e);
                                            }
                                        }
                                        if (googleBtn) {
                                            googleBtn.innerHTML = oldHtml;
                                            googleBtn.disabled = false;
                                        }
                                    }
                                });
                                tokenClient.requestAccessToken();
                                return;
                            }
                        }
                        if (googleBtn) {
                            googleBtn.innerHTML = oldHtml;
                            googleBtn.disabled = false;
                        }
                    });
                } catch (err) {
                    console.error('Google GIS error:', err);
                    if (googleBtn) {
                        googleBtn.innerHTML = oldHtml;
                        googleBtn.disabled = false;
                    }
                }
            } else {
                if (googleBtn) {
                    googleBtn.innerHTML = oldHtml;
                    googleBtn.disabled = false;
                }
            }
        }

        if (typeof google === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://accounts.google.com/gsi/client?hl=en';
            script.async = true;
            script.defer = true;
            script.onload = promptGoogle;
            script.onerror = () => {
                if (typeof showToast === 'function') showToast('Could not load Google Sign-In SDK.');
                if (googleBtn) {
                    googleBtn.innerHTML = oldHtml;
                    googleBtn.disabled = false;
                }
            };
            document.head.appendChild(script);
        } else {
            promptGoogle();
        }
    };

    window.handleCredentialResponse = async function(response) {
        const googleBtn = document.querySelector('.btn-google');
        const oldHtml = googleBtn ? googleBtn.innerHTML : '';
        if (googleBtn) {
            googleBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Authenticating...';
            googleBtn.disabled = true;
        }
        
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
            if (typeof showToast === 'function') showToast(error.message);
            if (googleBtn) {
                googleBtn.innerHTML = oldHtml;
                googleBtn.disabled = false;
            }
        }
    };
