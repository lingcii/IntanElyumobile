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

    var backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';

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
            
            if (typeof showToast === 'function') showToast('Logged in successfully!');
            if (typeof navigateTo === 'function') navigateTo('dashboard');
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

    window.triggerGoogleLogin = function() {
        let overlay = document.getElementById('google-sim-modal');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'google-sim-modal';
            overlay.style.cssText = 'position:fixed; inset:0; z-index:999999; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.65); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); opacity:0; transition:opacity 0.3s;';
            overlay.innerHTML = `
                <div style="background:rgba(15,23,42,0.98); border:1px solid rgba(255,255,255,0.1); border-radius:28px; padding:28px; width:360px; max-width:92vw; text-align:center; box-shadow:0 24px 60px rgba(0,0,0,0.6); transform:scale(0.9); transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1); overflow-y:auto; max-height:90vh;">
                    <div style="width:52px; height:52px; border-radius:50%; background:white; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                        <svg viewBox="0 0 24 24" width="28" height="28">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.53-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-8.7c0-.18-.01-.35-.05-.47z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.11 0-5.74-2.11-6.68-4.96H1.21v3.15C3.18 21.88 7.31 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.32 14.24A7.16 7.16 0 0 1 5 12c0-.79.13-1.57.32-2.31V6.54H1.21A11.96 11.96 0 0 0 0 12c0 1.92.45 3.74 1.21 5.38l4.11-3.14z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.18 2.12 1.21 5.46l4.11 3.22c.94-2.85 3.57-4.93 6.68-4.93z"/>
                        </svg>
                    </div>
                    <h3 style="margin:0 0 4px; color:white; font-size:18px; font-weight:800;">Google Account</h3>
                    <p style="margin:0 0 16px; color:rgba(255,255,255,0.4); font-size:11px;">Select connection mode for presentation/testing</p>
                    
                    <!-- Real Mode Setup -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:18px; padding:16px; margin-bottom:14px; text-align:left;">
                        <span style="font-size:11px; font-weight:700; color:#38bdf8; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:8px;">Real Google Sign-In</span>
                        <div id="google-real-btn-container" style="display:flex; justify-content:center; min-height:40px;"></div>
                        
                        <div id="google-id-setup" style="display:none; flex-direction:column; gap:8px; margin-top:8px;">
                            <input type="text" id="g-client-id-input" placeholder="Google Client ID" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:white; font-size:11px;">
                            <button onclick="window.saveGoogleClientId()" style="width:100%; padding:10px; border-radius:8px; border:none; background:#38bdf8; color:black; font-size:11px; font-weight:700; cursor:pointer;">Save & Initialize</button>
                        </div>
                    </div>
                    
                    <!-- Demo Mode Setup -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:18px; padding:16px; margin-bottom:16px; text-align:left;">
                        <span style="font-size:11px; font-weight:700; color:#a78bfa; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:8px;">One-Click Demo Accounts</span>
                        
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <div onclick="window.submitGoogleSim('James Vergel', 'james.vergel@gmail.com')" style="display:flex; align-items:center; gap:10px; padding:8px 12px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:12px; cursor:pointer; transition:background 0.2s;">
                                <img src="https://ui-avatars.com/api/?name=James+Vergel&background=007AFF&color=fff&rounded=true" style="width:30px; height:30px; border-radius:50%;">
                                <div>
                                    <div style="color:white; font-size:12px; font-weight:700; line-height:1.2;">James Vergel</div>
                                    <div style="color:rgba(255,255,255,0.4); font-size:10px;">james.vergel@gmail.com</div>
                                </div>
                            </div>
                            <div onclick="window.submitGoogleSim('Elyu Explorer', 'elyu.explorer@gmail.com')" style="display:flex; align-items:center; gap:10px; padding:8px 12px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:12px; cursor:pointer; transition:background 0.2s;">
                                <img src="https://ui-avatars.com/api/?name=Elyu+Explorer&background=34c759&color=fff&rounded=true" style="width:30px; height:30px; border-radius:50%;">
                                <div>
                                    <div style="color:white; font-size:12px; font-weight:700; line-height:1.2;">Elyu Explorer</div>
                                    <div style="color:rgba(255,255,255,0.4); font-size:10px;">elyu.explorer@gmail.com</div>
                                </div>
                            </div>
                            <div onclick="window.showGoogleCustomInput()" id="btn-custom-toggle" style="display:flex; align-items:center; gap:10px; padding:8px 12px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:12px; cursor:pointer; transition:background 0.2s;">
                                <div style="width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:center; color:white; font-size:11px;"><i class="fa-solid fa-user-plus"></i></div>
                                <div>
                                    <div style="color:white; font-size:12px; font-weight:700; line-height:1.2;">Use Another Email</div>
                                    <div style="color:rgba(255,255,255,0.4); font-size:10px;">Enter custom credentials</div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="google-custom-inputs" style="display:none; flex-direction:column; gap:8px; margin-top:8px;">
                            <input type="text" id="g-custom-name" placeholder="Full Name" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:white; font-size:11px;">
                            <input type="email" id="g-custom-email" placeholder="Email Address" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:white; font-size:11px;">
                            <button onclick="window.submitGoogleCustom()" style="width:100%; padding:10px; border-radius:8px; border:none; background:#a78bfa; color:black; font-size:11px; font-weight:700; cursor:pointer;">Demo Sign-In</button>
                        </div>
                    </div>
                    
                    <button style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.15); background:transparent; color:rgba(255,255,255,0.6); font-size:12px; font-weight:600; cursor:pointer;" onclick="window.closeGoogleSim()">Close</button>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        
        setTimeout(() => {
            overlay.style.opacity = '1';
            overlay.querySelector('div > div').style.transform = 'scale(1)';
        }, 50);

        let clientId = localStorage.getItem('intan_elyu_google_client_id');
        if (clientId && !clientId.includes('xxx')) {
            loadGisAndInit(clientId);
        } else {
            showIdSetupForm();
        }
    };

    function showIdSetupForm() {
        document.getElementById('google-real-btn-container').style.display = 'none';
        document.getElementById('google-id-setup').style.display = 'flex';
    }

    window.saveGoogleClientId = function() {
        const id = document.getElementById('g-client-id-input').value.trim();
        if (!id || id.includes('xxx') || id.includes('102834710293') || id.includes('apps.googleusercontent.com') === false) {
            if (typeof showToast === 'function') showToast('Please enter a valid Google Client ID from Google Cloud Console.');
            return;
        }
        localStorage.setItem('intan_elyu_google_client_id', id);
        document.getElementById('google-id-setup').style.display = 'none';
        document.getElementById('google-real-btn-container').style.display = 'flex';
        loadGisAndInit(id);
    };

    window.resetGoogleClientId = function(e) {
        if (e) e.preventDefault();
        localStorage.removeItem('intan_elyu_google_client_id');
        showIdSetupForm();
    };

    function loadGisAndInit(clientId) {
        if (typeof google === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://accounts.google.com/gsi/client?hl=en';
            script.async = true;
            script.onload = () => initGoogleGis(clientId);
            document.head.appendChild(script);
        } else {
            initGoogleGis(clientId);
        }
    }

    function initGoogleGis(clientId) {
        try {
            google.accounts.id.initialize({
                client_id: clientId,
                callback: window.handleCredentialResponse
            });
            
            const container = document.getElementById('google-real-btn-container');
            if (container) {
                container.innerHTML = '';
                google.accounts.id.renderButton(container, {
                    theme: 'filled_blue',
                    size: 'large',
                    shape: 'pill',
                    width: 240
                });
            }
            google.accounts.id.prompt();
        } catch (e) {
            console.error('Google initialization error:', e);
            showIdSetupForm();
        }
    }

    window.closeGoogleSim = function() {
        const overlay = document.getElementById('google-sim-modal');
        if (overlay) {
            overlay.style.opacity = '0';
            overlay.querySelector('div > div').style.transform = 'scale(0.9)';
            setTimeout(() => { overlay.remove(); }, 300);
        }
    };

    window.showGoogleCustomInput = function() {
        document.getElementById('google-custom-inputs').style.display = 'flex';
        document.getElementById('btn-custom-toggle').style.display = 'none';
    };

    window.submitGoogleCustom = function() {
        const name = document.getElementById('g-custom-name').value;
        const email = document.getElementById('g-custom-email').value;
        if (!name || !email) {
            if (typeof showToast === 'function') showToast('Please fill in all fields.');
            return;
        }
        window.submitGoogleSim(name, email);
    };

    window.submitGoogleSim = async function(name, email) {
        window.closeGoogleSim();
        
        const googleBtn = document.querySelector('.btn-google');
        const oldHtml = googleBtn.innerHTML;
        googleBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Connecting...';
        googleBtn.disabled = true;
        
        const googleId = 'g_' + email.replace(/[^a-zA-Z0-9]/g, '');
        const avatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        
        try {
            const response = await fetch(backendUrl + '/api/auth/google', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    google_id: googleId,
                    avatar: avatar
                })
            });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || data.error || 'Google login failed');
            }
            
            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            
            if (typeof showToast === 'function') showToast(`Welcome, ${name}! 👋`);
            if (typeof navigateTo === 'function') navigateTo('dashboard');
        } catch (error) {
            console.error('Google Sign-in Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            googleBtn.innerHTML = oldHtml;
            googleBtn.disabled = false;
        }
    };

    window.handleCredentialResponse = async function(response) {
        window.closeGoogleSim();
        
        const googleBtn = document.querySelector('.btn-google');
        const oldHtml = googleBtn.innerHTML;
        googleBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Authenticating...';
        googleBtn.disabled = true;
        
        try {
            const fetchRes = await fetch(backendUrl + '/api/auth/google', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ credential: response.credential })
            });
            const data = await fetchRes.json();
            
            if (!fetchRes.ok) {
                throw new Error(data.message || data.error || 'Google login failed');
            }
            
            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            
            if (typeof showToast === 'function') showToast(`Welcome back, ${data.user.name}! 👋`);
            if (typeof navigateTo === 'function') navigateTo('dashboard');
        } catch (error) {
            console.error('Google Auth Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            googleBtn.innerHTML = oldHtml;
            googleBtn.disabled = false;
        }
    };
</script>

