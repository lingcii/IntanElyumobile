<!-- Auth View (Login, Register & Forgot Password) -->

<div class="auth-container">
    <!-- Top Blue Section -->
    <div class="auth-top">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="Intan Elyu Logo">
        </div>
        <h1 id="auth-title">Welcome to Elyu</h1>
        
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
                
                <div class="auth-links">
                    <div class="auth-divider"><span>OR</span></div>
                    <button type="button" class="btn-google" onclick="signInWithGoogle()">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google">
                        Sign in with Google
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

                <div class="auth-links">
                    <div class="auth-divider"><span>OR</span></div>
                    <button type="button" class="btn-google" onclick="signInWithGoogle()">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google">
                        Sign up with Google
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
                    <i class="fa-solid fa-check-circle" style="font-size: 40px; color: #1e3a8a; margin-bottom: 16px;"></i>
                    <h3 style="margin: 0 0 10px 0; color: #333;">Email Sent!</h3>
                    <p style="color: #888; font-size: 14px; margin-bottom: 30px;">Check your inbox for the reset link.</p>
                    <button class="btn-circle-submit" type="button" onclick="hideForgotPassword(event)">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
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

    let backendUrl = 'http://localhost:8000';

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
            if (typeof navigateTo === 'function') navigateTo('setup_profile');
        } catch (error) {
            console.error('Register Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            btn.disabled = false;
        }
    };

    window.handleForgotPassword = function(e) {
        e.preventDefault();
        const btn = document.getElementById('fp-btn');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        btn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            document.getElementById('fp-form-state').style.display = 'none';
            document.getElementById('fp-success-state').style.display = 'block';
        }, 1500);
    };

    function signInWithGoogle() {
        showToast('Opening Google Sign-In...');
        setTimeout(() => {
            window.location.href = backendUrl + '/api/auth/google/redirect';
        }, 400);
    }
</script>
