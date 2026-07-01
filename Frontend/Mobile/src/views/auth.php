<!-- Auth View (Login, Register & Forgot Password) -->


<div class="auth-container animate-fade-in">
    <div class="auth-overlay"></div>
    
    <div class="auth-content">
        <div class="auth-header stagger-1">
            <div class="logo-container">
                <img src="assets/img/logo.png" alt="Intan Elyu Logo" style="width: 50px; height: auto;">
            </div>
            <h1 id="auth-title">Welcome.</h1>
            <p id="auth-subtitle">Start your adventure with Intan Elyu.</p>
        </div>
        
        <div class="glass-panel auth-card stagger-2">
            <div class="forms-wrapper" id="forms-wrapper">
                
                <!-- Panel 1: Login -->
                <div class="form-panel login-form">
                    <form id="form-login" onsubmit="event.preventDefault(); handleLogin(event);">
                        <div class="input-group">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="login-email" class="form-control auth-input" placeholder="Email Address" required>
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="login-password" class="form-control auth-input" placeholder="Password" required>
                        </div>
                        <a href="#" class="forgot-pwd" onclick="showForgotPassword(event)">Forgot Password?</a>
                        <button type="submit" id="btn-login" class="btn-primary" style="padding:16px;">Sign In</button>
                    </form>
                    
                    <div class="divider">or</div>
                    
                    <button class="btn-social" onclick="signInWithGoogle()">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" style="width:20px;">
                        Continue with Google
                    </button>
                    
                    <div class="auth-links">
                        <span style="color:#8E8E93;">New to Intan Elyu?</span> 
                        <a href="#" onclick="toggleAuthMode(event)">Sign Up</a>
                    </div>
                </div>
                
                <!-- Panel 2: Register -->
                <div class="form-panel register-form">
                    <div style="margin-bottom:16px;">
                        <a href="#" class="back-link" onclick="toggleAuthMode(event)">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <form id="form-register" onsubmit="event.preventDefault(); handleRegister(event);">
                        <div class="input-group">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="reg-name" class="form-control auth-input" placeholder="Full Name" required>
                        </div>
                        <div class="input-group">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="reg-email" class="form-control auth-input" placeholder="Email Address" required>
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg-password" class="form-control auth-input" placeholder="Create Password" required>
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg-password-confirm" class="form-control auth-input" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" id="btn-register" class="btn-primary" style="padding:16px; margin-top:10px;">Create Account</button>
                    </form>
                    
                    <div class="divider">or</div>
                    
                    <button class="btn-social" onclick="signInWithGoogle()">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" style="width:20px;">
                        Sign up with Google
                    </button>
                    
                    <div class="auth-links">
                        <span style="color:#8E8E93;">Already a member?</span> 
                        <a href="#" onclick="toggleAuthMode(event)">Sign In</a>
                    </div>
                </div>

                <!-- Panel 3: Forgot Password -->
                <div class="form-panel forgot-form">
                    <a href="#" class="back-link" onclick="hideForgotPassword(event)">
                        <i class="fa-solid fa-arrow-left"></i> Back to Login
                    </a>

                    <!-- Form state -->
                    <div id="fp-form-state">
                        <h3 style="font-size:22px; font-weight:800; color:var(--text-dark); margin:0 0 8px;">Reset Password</h3>
                        <p class="fp-hint">Enter your email and we'll send you a link to reset your password.</p>

                        <form id="form-forgot" onsubmit="handleForgotPassword(event)">
                            <div class="input-group">
                                <i class="fa-regular fa-envelope"></i>
                                <input type="email" id="fp-email" class="form-control auth-input" placeholder="Email Address" required>
                            </div>
                            <button type="submit" id="fp-btn" class="btn-primary" style="padding:16px;">
                                Send Reset Link
                            </button>
                        </form>
                    </div>

                    <!-- Success state -->
                    <div class="fp-success" id="fp-success-state">
                        <div class="fp-success-icon">
                            <i class="fa-solid fa-paper-plane"></i>
                        </div>
                        <h3>Check your email!</h3>
                        <p>We've sent a password reset link to <strong id="fp-sent-email"></strong>. Check your inbox.</p>
                        <button class="btn-primary" style="padding:14px; width:100%;" onclick="hideForgotPassword(event)">
                            Back to Login
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    const wrapper = document.getElementById('forms-wrapper');
    const titleEl = document.getElementById('auth-title');
    const subtitleEl = document.getElementById('auth-subtitle');

    function setHeader(title, subtitle) {
        titleEl.style.opacity = 0;
        subtitleEl.style.opacity = 0;
        setTimeout(() => {
            titleEl.textContent = title;
            subtitleEl.textContent = subtitle;
            titleEl.style.opacity = 1;
            subtitleEl.style.opacity = 1;
        }, 300);
    }

    function toggleAuthMode(e) {
        e.preventDefault();
        const isRegister = wrapper.classList.contains('show-register');
        wrapper.classList.remove('show-register', 'show-forgot');
        if (!isRegister) {
            wrapper.classList.add('show-register');
            setHeader('Join Us.', 'Create an account to explore.');
        } else {
            setHeader('Welcome.', 'Start your adventure with Intan Elyu.');
        }
    }

    function showForgotPassword(e) {
        e.preventDefault();
        // Reset forgot form to default state
        document.getElementById('fp-form-state').style.display = 'block';
        document.getElementById('fp-success-state').style.display = 'none';
        document.getElementById('fp-email').value = '';
        document.getElementById('fp-btn').textContent = 'Send Reset Link';
        document.getElementById('fp-btn').disabled = false;

        wrapper.classList.remove('show-register');
        wrapper.classList.add('show-forgot');
        setHeader('Forgot Password?', 'No worries, we got you.');
    }

    function hideForgotPassword(e) {
        e.preventDefault();
        wrapper.classList.remove('show-forgot', 'show-register');
        setHeader('Welcome.', 'Start your adventure with Intan Elyu.');
    }

    function handleForgotPassword(e) {
        e.preventDefault();
        const email = document.getElementById('fp-email').value;
        const btn = document.getElementById('fp-btn');
        
        // Loading state
        btn.textContent = 'Sending...';
        btn.disabled = true;

        // Simulate API call
        setTimeout(() => {
            document.getElementById('fp-sent-email').textContent = email;
            document.getElementById('fp-form-state').style.display = 'none';
            document.getElementById('fp-success-state').style.display = 'flex';
            setHeader('Email Sent! ✓', 'Check your inbox.');
        }, 1500);
    }

    let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';

    function signInWithGoogle() {
        showToast('Opening Google Sign-In...');
        setTimeout(() => {
            window.location.href = backendUrl + '/api/auth/google/redirect';
        }, 400);
    }

    window.handleLogin = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-login');
        btn.textContent = 'Authenticating...';
        btn.disabled = true;

        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;

        try {
            const response = await fetch(backendUrl + '/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Login failed');
            }

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('intan_elyu_token', data.token);
            
            if (typeof showToast === 'function') showToast('Logged in successfully!');
            if (typeof navigateTo === 'function') navigateTo('dashboard');
        } catch (error) {
            console.error('Login Error:', error);
            if (typeof showToast === 'function') showToast(error.message);
            btn.textContent = 'Sign In';
            btn.disabled = false;
        }
    };

    window.handleRegister = async function(e) {
        e.preventDefault();
        const pwd = document.getElementById('reg-password').value;
        const confirmPwd = document.getElementById('reg-password-confirm').value;
        const name = document.getElementById('reg-name').value;
        const email = document.getElementById('reg-email').value;

        if (pwd !== confirmPwd) {
            if (typeof showToast === 'function') showToast('Passwords do not match');
            return;
        }

        const btn = document.getElementById('btn-register');
        btn.textContent = 'Creating...';
        btn.disabled = true;

        try {
            const response = await fetch(backendUrl + '/api/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ name: name, email: email, password: pwd, password_confirmation: confirmPwd })
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
            btn.textContent = 'Create Account';
            btn.disabled = false;
        }
    }
</script>
