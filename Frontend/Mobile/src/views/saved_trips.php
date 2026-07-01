<?php
$pageTitle = 'About Intan Elyu';
?>

<?php include __DIR__ . '/../components/header.php'; ?>



<div class="about-container has-header has-bottom-nav animate-slide-up">
    
    <div class="about-logo stagger-1">
        <img src="assets/img/logo.png" alt="Intan Elyu Logo" onerror="this.src=''">
    </div>
    
    <h1 class="app-name stagger-2">Intan Elyu</h1>
    <p class="app-version stagger-2">Version 1.0.0 (Beta)</p>
    
    <div class="about-description stagger-3">
        Intan Elyu is an innovative tourist application designed exclusively for navigating and discovering the beautiful province of La Union. Our goal is to make your travel experience seamless, enjoyable, and memorable.
    </div>
    
    <div class="dev-team stagger-4">
        <h4>Developed By</h4>
        <div class="team-member">
            <div class="member-avatar">A</div>
            <div class="member-name">Acekillersmile2131</div>
        </div>
        <div class="team-member">
            <div class="member-avatar">S</div>
            <div class="member-name">System Team</div>
        </div>
    </div>
    
</div>
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
                    <form id="form-login" onsubmit="handleLogin(event)">
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
                    <form id="form-register" onsubmit="handleRegister(event)">
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

    async function handleLogin(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-login');
        btn.textContent = 'Authenticating...';
        btn.disabled = true;

        try {
            const response = await fetch(backendUrl + '/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true'
                },
                body: JSON.stringify({
                    email: document.getElementById('login-email').value,
                    password: document.getElementById('login-password').value
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Login failed');
            }

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            if (data.token) localStorage.setItem('Intan_Elyu_Token', data.token);
            
            showToast('Welcome back, ' + data.user.name + '!');
            setTimeout(() => { navigateTo('dashboard'); }, 800);
        } catch (error) {
            showToast(error.message, 4000);
            btn.textContent = 'Sign In';
            btn.disabled = false;
        }
    }

    async function handleRegister(e) {
        e.preventDefault();
        const pwd = document.getElementById('reg-password').value;
        const confirmPwd = document.getElementById('reg-password-confirm').value;

        if (pwd !== confirmPwd) {
            showToast('Passwords do not match');
            return;
        }

        const btn = document.getElementById('btn-register');
        btn.textContent = 'Creating...';
        btn.disabled = true;

        try {
            const response = await fetch(backendUrl + '/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true'
                },
                body: JSON.stringify({
                    name: document.getElementById('reg-name').value,
                    email: document.getElementById('reg-email').value,
                    password: pwd,
                    password_confirmation: confirmPwd
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Registration failed');
            }

            localStorage.setItem('auth_user', JSON.stringify(data.user));
            if (data.token) localStorage.setItem('Intan_Elyu_Token', data.token);
            
            showToast('Account created successfully!');
            setTimeout(() => { navigateTo('setup_profile'); }, 800);
        } catch (error) {
            showToast(error.message, 4000);
            btn.textContent = 'Create Account';
            btn.disabled = false;
        }
    }
</script>
<!-- Dashboard View -->
<?php
$pageTitle = 'Discover La Union';
$activeTab = 'dashboard';
?>


<?php include __DIR__ . '/../components/header.php'; ?>

<div class="dashboard-container has-header has-bottom-nav animate-slide-up">

    <!-- Profile + EXP -->
    <div class="profile-header stagger-1" onclick="showToast('View profile')">
        <div class="profile-info-row">
            <div class="profile-avatar">
                <img src="https://ui-avatars.com/api/?name=Tourist&amp;background=007AFF&amp;color=fff&amp;rounded=true&amp;bold=true&amp;size=128" alt="Avatar">
            </div>
            <div class="profile-text">
                <h2 class="profile-name">Hi, Tourist! 👋</h2>
                <p class="profile-title">Explorer of Elyu</p>
            </div>
        </div>
        <div class="exp-container">
            <div class="exp-header">
                <span class="exp-label">Level Progress</span>
                <span class="exp-value">450 / 1000 XP</span>
            </div>
            <div class="exp-bar-bg"><div class="exp-bar-fill" style="width:45%;"></div></div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row stagger-1">
        <div class="stat-card" onclick="showToast('Places visited')">
            <div class="stat-icon"><i class="fa-solid fa-map-location-dot" style="color:#007AFF;"></i></div>
            <div class="stat-value">12</div>
            <div class="stat-label">Places</div>
        </div>
        <div class="stat-card" onclick="showToast('Points earned')">
            <div class="stat-icon"><i class="fa-solid fa-star" style="color:#FFD700;"></i></div>
            <div class="stat-value">450</div>
            <div class="stat-label">Points</div>
        </div>
        <div class="stat-card" onclick="showToast('Your rank')">
            <div class="stat-icon"><i class="fa-solid fa-trophy" style="color:#FF9500;"></i></div>
            <div class="stat-value">#8</div>
            <div class="stat-label">Rank</div>
        </div>
    </div>

    <!-- Weather Widget -->
    <div class="weather-card stagger-2">
        <div class="weather-left">
            <div class="weather-temp">29°C</div>
            <div class="weather-desc">Partly Cloudy</div>
            <div class="weather-loc">📍 San Fernando, La Union</div>
            <div class="weather-details">
                <span class="weather-detail"><i class="fa-solid fa-droplet"></i> 72%</span>
                <span class="weather-detail"><i class="fa-solid fa-wind"></i> 14 km/h</span>
                <span class="weather-detail"><i class="fa-solid fa-sun"></i> UV 6</span>
            </div>
        </div>
        <div class="weather-icon">⛅</div>
    </div>

    <!-- Search -->
    <div class="search-wrapper stagger-2">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" class="search-input" placeholder="Search beaches, cafes, falls...">
    </div>

    <!-- Trending Spots -->
    <div class="dash-section stagger-2">
        <div class="section-title">
            <h3>Trending Spots</h3>
            <a href="#" onclick="showToast('Loading all places...')">See All</a>
        </div>
        <div class="carousel-container">
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="San Juan" class="card-img">
                <div class="card-content">
                    <h4 class="card-title">San Juan Surf Resort</h4>
                    <span class="card-location"><i class="fa-solid fa-location-dot"></i> San Juan, La Union</span>
                </div>
            </div>
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Watchtower" class="card-img">
                <div class="card-content">
                    <h4 class="card-title">Baluarte Watchtower</h4>
                    <span class="card-location"><i class="fa-solid fa-location-dot"></i> Luna, La Union</span>
                </div>
            </div>
            <div class="destination-card">
                <img src="https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Falls" class="card-img">
                <div class="card-content">
                    <h4 class="card-title">Tangadan Falls</h4>
                    <span class="card-location"><i class="fa-solid fa-location-dot"></i> San Gabriel, La Union</span>
                </div>
            </div>
        </div>
    </div>



    <!-- My Itinerary Preview -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>My Itinerary</h3>
            <a href="#" onclick="navigateTo('itinerary')">Open</a>
        </div>
        <div class="itinerary-preview">
            <div class="itinerary-header">
                <span style="font-size:14px; font-weight:700; color:var(--text-dark);">Today's Plan</span>
                <span class="itinerary-badge">3 Stops</span>
            </div>
            <div class="itinerary-stop">
                <div class="stop-dot"><i class="fa-solid fa-check"></i></div>
                <div class="stop-info">
                    <p class="stop-name">Ma-Cho Temple</p>
                    <p class="stop-time">9:00 AM · San Fernando</p>
                </div>
                <span class="stop-status done">Done</span>
            </div>
            <div class="itinerary-stop">
                <div class="stop-dot active"><i class="fa-solid fa-location-dot"></i></div>
                <div class="stop-info">
                    <p class="stop-name">San Juan Surf Resort</p>
                    <p class="stop-time">12:00 PM · San Juan</p>
                </div>
                <span class="stop-status next">Next</span>
            </div>
            <div class="itinerary-stop">
                <div class="stop-dot"><i class="fa-solid fa-flag"></i></div>
                <div class="stop-info">
                    <p class="stop-name">Tangadan Falls</p>
                    <p class="stop-time">3:00 PM · San Gabriel</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorites / Saved Places -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>Saved Places</h3>
            <a href="#" onclick="showToast('View all saved')">See All</a>
        </div>
        <div class="favorites-row">
            <div class="fav-card" onclick="showToast('Ma-Cho Temple')">
                <img src="https://images.unsplash.com/photo-1622313628787-8e6f1f4ab986?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Ma-Cho">
                <div class="fav-card-overlay"><span class="fav-card-name">Ma-Cho Temple</span></div>
                <i class="fa-solid fa-heart fav-heart"></i>
            </div>
            <div class="fav-card" onclick="showToast('Grape Farms')">
                <img src="https://images.unsplash.com/photo-1542640244-7e672d6cb466?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Grape">
                <div class="fav-card-overlay"><span class="fav-card-name">Bauang Grape Farms</span></div>
                <i class="fa-solid fa-heart fav-heart"></i>
            </div>
            <div class="fav-card" onclick="showToast('Tangadan Falls')">
                <img src="https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Falls">
                <div class="fav-card-overlay"><span class="fav-card-name">Tangadan Falls</span></div>
                <i class="fa-solid fa-heart fav-heart"></i>
            </div>
            <div class="fav-card" onclick="showToast('Surf Resort')">
                <img src="https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Surf">
                <div class="fav-card-overlay"><span class="fav-card-name">San Juan Surf</span></div>
                <i class="fa-solid fa-heart fav-heart"></i>
            </div>
        </div>
    </div>

    <!-- Recommended For You -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>Recommended For You</h3>
        </div>
        <div class="recommended-item" onclick="showToast('Loading Ma-Cho Temple...')">
            <img src="https://images.unsplash.com/photo-1622313628787-8e6f1f4ab986?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" alt="Macho Temple" class="recommended-img">
            <div style="flex:1;">
                <h4 style="margin:0 0 5px; font-size:16px; font-weight:800; letter-spacing:-0.3px;">Ma-Cho Temple</h4>
                <p style="margin:0 0 7px; font-size:13px; color:#8E8E93;"><i class="fa-solid fa-location-dot" style="margin-right:4px; color:var(--accent-color);"></i>San Fernando City</p>
                <div style="display:flex; align-items:center; gap:5px;">
                    <i class="fa-solid fa-star" style="color:#FFD700; font-size:12px;"></i>
                    <span style="font-size:13px; font-weight:700;">4.8</span>
                    <span style="font-size:12px; color:#8E8E93;">(2k+ reviews)</span>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color:#C7C7CC;"></i>
        </div>
        <div class="recommended-item" onclick="showToast('Loading Bauang Grape Farms...')">
            <img src="https://images.unsplash.com/photo-1542640244-7e672d6cb466?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" alt="Grape Farms" class="recommended-img">
            <div style="flex:1;">
                <h4 style="margin:0 0 5px; font-size:16px; font-weight:800; letter-spacing:-0.3px;">Bauang Grape Farms</h4>
                <p style="margin:0 0 7px; font-size:13px; color:#8E8E93;"><i class="fa-solid fa-location-dot" style="margin-right:4px; color:var(--accent-color);"></i>Bauang, La Union</p>
                <div style="display:flex; align-items:center; gap:5px;">
                    <i class="fa-solid fa-star" style="color:#FFD700; font-size:12px;"></i>
                    <span style="font-size:13px; font-weight:700;">4.5</span>
                    <span style="font-size:12px; color:#8E8E93;">(850+ reviews)</span>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color:#C7C7CC;"></i>
        </div>
    </div>

</div>
<?php
$pageTitle = 'Help Center';
?>

<?php include __DIR__ . '/../components/header.php'; ?>



<div class="help-container has-header has-bottom-nav animate-slide-up">
    
    <div class="faq-item stagger-1">
        <div class="faq-question"><i class="fa-solid fa-circle-question"></i> How do I generate an itinerary?</div>
        <div class="faq-answer">Simply go to the Map tab, select the tourist spots you want to visit, and click "Generate Route". The app will automatically calculate the best path.</div>
    </div>
    
    <div class="faq-item stagger-2">
        <div class="faq-question"><i class="fa-solid fa-location-dot"></i> Why is my location inaccurate?</div>
        <div class="faq-answer">Ensure that you have granted Intan Elyu GPS permissions in your phone's settings and that you have a clear view of the sky.</div>
    </div>
    
    <div class="faq-item stagger-3">
        <div class="faq-question"><i class="fa-solid fa-trophy"></i> How do I rank up on the Leaderboard?</div>
        <div class="faq-answer">Visit more verified tourist spots in La Union! Each spot you check into awards you points that increase your rank.</div>
    </div>
    
    <div class="contact-support stagger-4" onclick="showToast('Opening Support Email...')">
        <i class="fa-solid fa-envelope" style="font-size: 24px; margin-bottom: 10px;"></i>
        <h3 style="margin: 0 0 5px 0;">Need more help?</h3>
        <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">Contact our Support Team</p>
    </div>
</div>
<!-- Itinerary View -->
<?php
$pageTitle = 'My Itinerary';
$activeTab = 'itinerary';
?>



<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="itinerary-container has-header has-bottom-nav animate-slide-up">
    
    <div style="display: flex; justify-content: space-between; align-items: center;" class="stagger-1">
        <h2 style="margin:0;">Draft Plan</h2>
        <span style="background: #E5E5EA; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight:600;"><span id="itinerary-count">0</span> Places</span>
    </div>
    
    <!-- Dynamic Timeline Container -->
    <div class="timeline stagger-2" id="itinerary-timeline" style="margin-bottom: 20px;">
        <!-- Rendered via JS -->
    </div>
    
    <!-- Save Itinerary Action -->
    <button class="btn-primary" id="btn-save-itinerary" style="display:none; width:100%; padding:16px; border-radius:16px; font-weight:700; font-size:16px; margin-bottom:40px; box-shadow:0 8px 20px rgba(0,0,0,0.1);" onclick="openSaveModal()">
        <i class="fa-solid fa-cloud-arrow-up" style="margin-right:8px;"></i> Save Draft Plan
    </button>
    
    <!-- Empty State -->
    <div id="itinerary-empty-state">
        <i class="fa-solid fa-route" style="font-size: 54px; margin-bottom: 16px; color:var(--primary-color);"></i>
        <h3 style="margin-bottom:8px;">No plans yet</h3>
        <p style="font-size:14px;">Go to the Map and tap "Add to Itinerary" on a place to start building your trip!</p>
        <button class="btn-primary" style="margin-top: 20px; width:auto; padding:12px 24px;" onclick="navigateTo('map')">Open Map</button>
    </div>
    
    <!-- Saved Trips Container -->
    <div id="saved-trips-container" style="margin-top: 40px; display: none;" class="stagger-3">
        <h2 style="margin:0 0 16px 0;">My Saved Trips</h2>
        <div id="saved-trips-list"></div>
    </div>
    
</div>

<!-- Save Trip Modal -->
<div id="save-trip-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:90%; max-width:400px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">Save Your Trip</h3>
        <p style="font-size:14px; color:#666; margin-bottom:20px;">Give your awesome adventure a name so you can pull it up later!</p>
        
        <input type="text" id="trip-title" placeholder="e.g. La Union Weekend" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid #ddd; margin-bottom:16px; font-family:inherit; font-size:16px;">
        
        <label style="font-size:12px; color:#666; margin-bottom:4px; display:block;">Trip Date (Optional)</label>
        <input type="date" id="trip-date" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid #ddd; margin-bottom:16px; font-family:inherit; font-size:16px;">

        <label style="font-size:12px; color:#666; margin-bottom:4px; display:block;">Mode of Transport</label>
        <select id="trip-transport" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid #ddd; margin-bottom:16px; font-family:inherit; font-size:16px; background:white; appearance:none;" onchange="window.calculateModalBudget()">
            <option value="">Select Primary Transport</option>
            <option value="own_car">Own Car</option>
            <option value="taxi">Taxi / Ride-hailing</option>
            <option value="private_bus">Private Bus</option>
            <option value="mini_bus">Mini Bus</option>
            <option value="lutrampco">LUTRAMPCO</option>
            <option value="jeepney">Jeepney</option>
        </select>

        <label style="font-size:12px; color:#666; margin-bottom:4px; display:flex; justify-content:space-between;">
            <span>Overall Budget (Optional)</span>
            <span id="save-budget-indicator"></span>
        </label>
        <div style="position:relative; margin-bottom:12px;">
            <span style="position:absolute; left:16px; top:14px; color:#666; font-weight:600;">₱</span>
            <input type="number" id="trip-budget" placeholder="0.00" oninput="window.calculateModalBudget()" style="width:100%; padding:12px 16px 12px 32px; border-radius:12px; border:1px solid #ddd; font-family:inherit; font-size:16px;">
        </div>
        
        <div id="save-budget-details" style="display:none; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); padding:12px; border-radius:12px; margin-bottom:24px; font-size:13px; color:#666; text-align:left;">
            Estimated Trip Cost: <strong id="save-estimated-cost" style="color:var(--text-dark); font-size:15px; float:right;">₱0.00</strong>
        </div>
        
        <div style="display:flex; gap:12px;">
            <button class="btn-primary" style="flex:1; background:transparent; border:1px solid #E5E5EA; color:#333;" onclick="closeSaveModal()">Cancel</button>
            <button class="btn-primary" style="flex:1;" onclick="submitItinerary()" id="btn-submit-trip">Save Trip</button>
        </div>
    </div>
</div>

<!-- Check-in Verification Modal -->
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:90%; max-width:400px; box-shadow:0 20px 40px rgba(0,0,0,0.2); text-align:center;">
        <h3 style="margin-top:0;">Verify Check-In</h3>
        <p style="font-size:14px; color:#666; margin-bottom:20px;">Prove you are here to earn your XP! Select a verification method.</p>
        
        <input type="hidden" id="checkin-item-id">

        <button class="btn-primary" style="width:100%; margin-bottom:12px; padding:16px;" onclick="verifyGpsCheckIn()" id="btn-verify-gps">
            <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify via GPS
        </button>
        
        <div style="position:relative;">
            <button class="btn-primary" style="width:100%; background:transparent; border:2px dashed var(--primary-color); color:var(--primary-color); padding:16px; margin-bottom:12px;" onclick="document.getElementById('proof-photo').click()" id="btn-verify-photo">
                <i class="fa-solid fa-camera" style="margin-right:8px;"></i> Upload Photo Proof
            </button>
            <input type="file" id="proof-photo" accept="image/*" style="display:none;" onchange="verifyPhotoCheckIn(this)">
        </div>
        
        <button class="btn-primary" style="width:100%; background:#FF9500; border:none; padding:16px; margin-bottom:24px;" onclick="verifyTestCheckIn()" id="btn-verify-test">
            <i class="fa-solid fa-check-double" style="margin-right:8px;"></i> Mark as Completed (Bypass)
        </button>

        <button class="btn-primary" style="width:100%; background:transparent; border:1px solid #E5E5EA; color:#333;" onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<script>
(function() {
    let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';

    window.renderItinerary = function() {
        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        const timeline = document.getElementById('itinerary-timeline');
        const emptyState = document.getElementById('itinerary-empty-state');
        const fab = document.getElementById('btn-save-itinerary');
        
        document.getElementById('itinerary-count').innerText = draft.length;

        if (draft.length === 0) {
            timeline.innerHTML = '';
            emptyState.style.display = 'block';
            fab.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        fab.style.display = 'flex';
        
        let html = '';
        draft.forEach((place, index) => {
            // Calculate a mock time just for visuals (starting at 9 AM, 1.5 hours per stop)
            const hour = 9 + Math.floor((index * 90) / 60);
            const min = (index * 90) % 60;
            const timeStr = `${hour > 12 ? hour - 12 : hour}:${min === 0 ? '00' : min} ${hour >= 12 ? 'PM' : 'AM'}`;

            html += `
            <div class="timeline-item" style="animation-delay: ${index * 0.1}s">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="time-label">Stop ${index + 1} &bull; Approx ${timeStr}</span>
                    <h3 class="place-name">${place.name}</h3>
                    <div class="place-details">
                        <i class="fa-solid fa-location-dot"></i>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${place.location}</span>
                    </div>
                    <div style="margin-top:16px; display:flex; gap:8px;">
                        <button style="padding: 8px 16px; font-size:12px; width:auto; border-radius: 100px; border:1px solid #E5E5EA; background:transparent; font-weight:600;" onclick="window.removeItineraryItem('${place.id}')">
                            <i class="fa-solid fa-trash" style="margin-right:4px;"></i> Remove
                        </button>
                        <button class="btn-primary" style="padding: 8px 16px; font-size:12px; width:auto; flex:1;" onclick="window.routeToPlace('${place.id}')">
                            <i class="fa-solid fa-diamond-turn-right" style="margin-right:4px;"></i> Directions
                        </button>
                    </div>
                </div>
            </div>`;
        });
        
        timeline.innerHTML = html;
    };

    window.removeItineraryItem = function(id) {
        let draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        draft = draft.filter(item => item.id.toString() !== id.toString());
        localStorage.setItem('Intan_Elyu_draft_itinerary', JSON.stringify(draft));
        window.renderItinerary();
    };

    window.routeToPlace = function(id) {
        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        const place = draft.find(item => item.id.toString() === id.toString());
        if (place) {
            // Save the routing target so map.php knows what to do
            localStorage.setItem('Intan_Elyu_pending_route', JSON.stringify(place));
            navigateTo('map');
        }
    };

    window.calculateModalBudget = function() {
        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        if (draft.length === 0) return;

        let estimatedCost = 0;
        draft.forEach(item => {
            estimatedCost += parseFloat(item.entrance_fee) || 50;
            estimatedCost += parseFloat(item.avg_food_cost) || 150;
            estimatedCost += parseFloat(item.avg_transport_cost) || 30;
        });

        const transport = document.getElementById('trip-transport').value;
        if (transport === 'own_car') estimatedCost += 300;
        else if (transport === 'taxi') estimatedCost += 250;
        else if (transport === 'private_bus') estimatedCost += 800;
        else if (transport === 'mini_bus') estimatedCost += 500;
        else if (transport === 'lutrampco') estimatedCost += 50;
        else if (transport === 'jeepney') estimatedCost += 30;

        const budgetInput = document.getElementById('trip-budget').value;
        const budget = parseFloat(budgetInput);

        const detailsDiv = document.getElementById('save-budget-details');
        const costEl = document.getElementById('save-estimated-cost');
        const indicatorEl = document.getElementById('save-budget-indicator');

        detailsDiv.style.display = 'block';
        costEl.textContent = '₱' + estimatedCost.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

        if (!budgetInput || isNaN(budget) || budget <= 0) {
            indicatorEl.innerHTML = '';
            return;
        }

        const percentage = (estimatedCost / budget) * 100;
        let color = '#34C759'; // Green
        let statusText = 'Safe';
        
        if (percentage >= 100) {
            color = '#FF3B30'; // Red
            statusText = 'Over Budget!';
        } else if (percentage >= 80) {
            color = '#FF9500'; // Orange
            statusText = 'Near Limit';
        }
        
        let displayPct = Math.round(percentage);
        if (displayPct > 100) displayPct = 100;
        
        indicatorEl.innerHTML = `<span style="color:${color}; font-weight:700;"><i class="fa-solid fa-circle" style="font-size:8px; vertical-align:middle;"></i> ${displayPct}% (${statusText})</span>`;
    };

    window.openSaveModal = function() {
        document.getElementById('save-trip-modal').style.display = 'flex';
        window.calculateModalBudget();
    };

    window.closeSaveModal = function() {
        document.getElementById('save-trip-modal').style.display = 'none';
        document.getElementById('trip-title').value = '';
        document.getElementById('trip-date').value = '';
        document.getElementById('trip-transport').value = '';
        document.getElementById('trip-budget').value = '';
        document.getElementById('save-budget-indicator').innerHTML = '';
        document.getElementById('save-budget-details').style.display = 'none';
    };

    window.submitItinerary = async function() {
        const title = document.getElementById('trip-title').value.trim();
        const date = document.getElementById('trip-date').value;
        const budgetStr = document.getElementById('trip-budget').value;
        const budget = budgetStr ? parseFloat(budgetStr) : null;
        if (!title) return showToast("Please enter a trip name");

        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        if (draft.length === 0) return showToast("Your itinerary is empty!");

        const btn = document.getElementById('btn-submit-trip');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const items = draft.map(place => ({
            destination_id: place.id,
            transport_cost: parseFloat(place.avg_transport_cost || 0),
            activity_cost: parseFloat(place.entrance_fee || 0),
            food_cost: parseFloat(place.avg_food_cost || 0),
            accommodation_cost: 0
        }));

        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                },
                body: JSON.stringify({ title: title, trip_date: date, budget: budget, items: items })
            });

            const data = await response.json();

            if (response.ok) {
                showToast("Trip saved successfully!");
                localStorage.removeItem('Intan_Elyu_draft_itinerary');
                closeSaveModal();
                window.renderItinerary();
                window.fetchSavedTrips();
            } else {
                throw new Error(data.message || "Failed to save trip");
            }
        } catch (error) {
            console.error("Save Error:", error);
            showToast(error.message || "Failed to save. Check connection.");
        } finally {
            btn.innerHTML = 'Save Trip';
            btn.disabled = false;
        }
    };

    window.fetchSavedTrips = async function() {
        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                renderSavedTrips(data.itineraries || []);
            }
        } catch (error) {
            console.error("Error fetching saved trips:", error);
        }
    };

    function renderSavedTrips(itineraries) {
        const container = document.getElementById('saved-trips-container');
        const list = document.getElementById('saved-trips-list');
        
        if (!itineraries || itineraries.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        let html = '';

        itineraries.forEach(trip => {
            let budgetIndicator = '';
            if (trip.budget && trip.budget > 0) {
                const cost = parseFloat(trip.total_cost || 0);
                const budget = parseFloat(trip.budget);
                const pct = cost / budget;
                
                let color = '#34C759'; // Green (Safe)
                if (pct >= 1.0) color = '#FF3B30'; // Red (Over/Warning)
                else if (pct >= 0.8) color = '#FF9500'; // Orange (Near)
                
                budgetIndicator = `<span style="display:inline-block; width:10px; height:10px; border-radius:50%; background-color:${color}; margin-left:6px; box-shadow:0 0 4px ${color}80;" title="Estimated Cost: ₱${cost.toFixed(2)}"></span>`;
            }

            html += `
            <div style="background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:16px; padding:20px; margin-bottom:20px;">
                <h3 style="margin:0 0 4px 0;">${trip.title}</h3>
                <p style="font-size:12px; color:#666; margin:0 0 16px 0;">
                    ${trip.trip_date ? 'Date: ' + new Date(trip.trip_date).toLocaleDateString() : 'No date set'} 
                    ${trip.budget ? '&nbsp;&bull;&nbsp; <span style="color:var(--primary-color); font-weight:700;">Budget: ₱' + parseFloat(trip.budget).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</span>' + budgetIndicator : ''}
                </p>
                <div class="timeline" style="margin-top:0;">`;
                
            let unvisitedCount = 0;
            if (trip.items && trip.items.length) {
                trip.items.forEach((item, index) => {
                    const dest = item.destination;
                    const isVisited = item.is_visited;
                    if (!isVisited) unvisitedCount++;
                    
                    html += `
                    <div class="timeline-item ${isVisited ? 'completed' : ''}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content" style="padding:12px;">
                            <h4 style="margin:0 0 4px 0; font-size:15px;">${dest ? dest.name : 'Unknown Destination'}</h4>
                            ${isVisited ? 
                                '<span style="color:var(--primary-color); font-size:12px; font-weight:600;"><i class="fa-solid fa-check-circle"></i> Visited</span>' : 
                                item.proof_image ? 
                                '<span style="color:#FF9500; font-size:12px; font-weight:600;"><i class="fa-solid fa-clock"></i> Pending Approval</span>' :
                                `<button class="btn-primary" style="padding: 6px 12px; font-size:11px; width:auto; border-radius:100px; margin-top:8px;" onclick="window.openCheckinModal('${item.id}')">
                                    <i class="fa-solid fa-location-arrow"></i> Check In (Earn XP)
                                 </button>`
                            }
                        </div>
                    </div>`;
                });
            } else {
                html += `<p style="font-size:13px; color:#999;">No destinations in this trip.</p>`;
            }
                
            html += `</div>`; // Close timeline

            // Action buttons
            html += `<div style="display:flex; gap:8px; margin-top:16px;">`;
            
            if (unvisitedCount === 0 && trip.items && trip.items.length > 0) {
                html += `
                <button class="btn-primary" style="flex:1; background:#34C759; border:none; padding:12px;" onclick="window.markTripCompleted('${trip.id}')">
                    <i class="fa-solid fa-flag-checkered" style="margin-right:8px;"></i> Mark Trip as Completed
                </button>`;
            } else {
                html += `
                <button class="btn-primary" style="flex:1; background:transparent; border:1px solid var(--primary-color); color:var(--primary-color); padding:12px;" onclick="navigateTo('map')">
                    <i class="fa-solid fa-plus" style="margin-right:8px;"></i> Add more destinations
                </button>`;
            }
            
            html += `</div></div>`; // Close card
        });

        list.innerHTML = html;
    }

    window.openCheckinModal = function(itemId) {
        document.getElementById('checkin-item-id').value = itemId;
        document.getElementById('checkin-modal').style.display = 'flex';
    };

    window.closeCheckinModal = function() {
        document.getElementById('checkin-modal').style.display = 'none';
        document.getElementById('checkin-item-id').value = '';
        document.getElementById('proof-photo').value = '';
    };

    window.verifyGpsCheckIn = function() {
        if (!navigator.geolocation) {
            showToast("Geolocation is not supported by your browser.");
            return;
        }

        const btn = document.getElementById('btn-verify-gps');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(async (position) => {
            await submitVerification({
                verification_method: 'gps',
                lat: position.coords.latitude,
                lng: position.coords.longitude
            });
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, (error) => {
            console.error(error);
            showToast("Failed to get location. Make sure GPS is enabled.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, { enableHighAccuracy: true });
    };

    window.verifyPhotoCheckIn = async function(input) {
        if (!input.files || input.files.length === 0) return;
        
        const btn = document.getElementById('btn-verify-photo');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('verification_method', 'photo');
        formData.append('proof_photo', input.files[0]);

        await submitVerification(formData, true);

        btn.innerHTML = originalText;
        btn.disabled = false;
        input.value = ''; // Reset
    };

    window.verifyTestCheckIn = async function() {
        if (!confirm('Are you sure you want to bypass GPS check-in? This is for testing only.')) return;

        const btn = document.getElementById('btn-verify-test');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Bypassing...';
        btn.disabled = true;

        await submitVerification({
            verification_method: 'test'
        });

        btn.innerHTML = originalText;
        btn.disabled = false;
    };

    async function submitVerification(data, isFormData = false) {
        const itemId = document.getElementById('checkin-item-id').value;
        if (!itemId) return;

        try {
            const options = {
                method: 'POST', // Use POST with _method=PATCH for multipart/form-data support in Laravel
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                }
            };

            if (isFormData) {
                data.append('_method', 'PATCH');
                options.body = data;
            } else {
                data._method = 'PATCH';
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }

            const response = await fetch(backendUrl + '/api/tourist/itineraries/items/' + itemId + '/visit', options);
            const result = await response.json();

            if (response.ok) {
                showToast(result.message || "Checked In! 🌟");
                closeCheckinModal();
                window.fetchSavedTrips(); // Refresh the list
            } else {
                showToast(result.message || "Verification failed.");
            }
        } catch (error) {
            console.error("Verification Error:", error);
            showToast("Failed to check in. Check connection.");
        }
    }

    // Render immediately on view load
    window.renderItinerary();
    window.fetchSavedTrips();
    window.markTripCompleted = async function(id) {
        if (!confirm("Are you sure you want to mark this trip as completed? It will be moved to your History.")) return;
        
        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries/' + id + '/complete', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                }
            });
            
            const data = await response.json();
            if (response.ok) {
                showToast(data.message || "Trip completed!");
                window.fetchSavedTrips(); // Refresh the list
            } else {
                showToast(data.message || "Failed to complete trip.");
            }
        } catch (error) {
            console.error("Error completing trip:", error);
            showToast("Network error.");
        }
    };

})();
</script>

<!-- Leaderboard View -->
<?php
$pageTitle = 'Top Explorers';
$activeTab = 'leaderboard';
?>



<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="leaderboard-container has-header has-bottom-nav animate-fade-in">
    
    <!-- Title -->
    <div class="leaderboard-title stagger-0">
        <h1>Top Explorers</h1>
        <p>Climb the ranks by exploring places!</p>
    </div>
    
    <!-- Podium -->
    <div class="podium-container stagger-1" id="podium-container">
        <!-- Injected via JS -->
    </div>
    
    <!-- Rank List -->
    <div class="rank-list stagger-2" id="rank-list-container">
        <!-- Injected via JS -->
    </div>
    
</div>

<!-- Include Bottom Navigation Component -->


<script>
(async function() {
    const podiumContainer = document.getElementById('podium-container');
    const rankListContainer = document.getElementById('rank-list-container');
    
    try {
        const token = localStorage.getItem('Intan_Elyu_Token');
        const headers = { 'Accept': 'application/json' };
        
        let url = '/api/public/leaderboard';
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
            url = '/api/tourist/leaderboard'; // Will be prefixed by proxy or relative if on same domain
        }

        // Fix absolute URL for mobile webview
        if (window.location.protocol === 'file:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            url = '../api' + url.replace('/api', '');
        } else {
            url = '../api' + url.replace('/api', '');
        }

        const res = await fetch(url, { headers });
        if (!res.ok) throw new Error("Failed to fetch leaderboard");
        const data = await res.json();
        
        const leaders = data.leaders || [];
        const myRank = data.myRank || 999;
        const me = data.me || null;

        // Render Podium
        let podiumHTML = '';
        if (leaders[1]) podiumHTML += generatePodiumPlace(leaders[1], 2);
        if (leaders[0]) podiumHTML += generatePodiumPlace(leaders[0], 1);
        if (leaders[2]) podiumHTML += generatePodiumPlace(leaders[2], 3);
        podiumContainer.innerHTML = podiumHTML;

        // Render Rank List (Ranks 4-20)
        let rankListHTML = '';
        for (let i = 3; i < leaders.length; i++) {
            const user = leaders[i];
            const isMe = me && user.id === me.id;
            rankListHTML += generateRankItem(user, i + 1, isMe);
        }

        if (me && myRank > 20) {
            rankListHTML += generateRankItem(me, myRank, true);
        }

        rankListContainer.innerHTML = rankListHTML;

    } catch(e) {
        console.error("Leaderboard error:", e);
        podiumContainer.innerHTML = "<div style='color:var(--text-dark); text-align:center; width:100%; margin-bottom:20px;'>Failed to load leaderboard.</div>";
    }

    function generatePodiumPlace(user, rank) {
        const displayName = `Explorer #${user.id}`;
        const avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=random&color=fff`;
        const crown = rank === 1 ? `<div style="position:absolute; top:-35px; left:50%; width:30px; height:30px; margin-left:-15px; text-align:center; animation: pulseCrown 2s infinite; z-index:10;"><i class="fa-solid fa-crown" style="color:#FFD700; font-size:24px;"></i></div>` : '';
        return `
        <div class="podium-place place-${rank}">
            <div style="position:relative;">
                ${crown}
                <img src="${avatarUrl}" alt="${displayName}" class="podium-avatar">
                <div class="rank-badge">${rank}</div>
            </div>
            <div class="podium-name">${displayName}</div>
            <div class="podium-xp">${parseInt(user.total_xp).toLocaleString()} XP</div>
            <div class="podium-block"></div>
        </div>`;
    }

    function generateRankItem(user, rank, isMe) {
        const displayName = `Explorer #${user.id}`;
        const avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=random&color=fff`;
        const activeStyle = isMe ? 'border-color: var(--primary-color); background: rgba(0, 122, 255, 0.05); box-shadow: 0 4px 15px rgba(0, 122, 255, 0.15);' : '';
        const numBadgeClass = rank <= 10 ? 'rank-badge-top10' : 'rank-badge-normal';
        const numBadgeStyle = isMe ? 'background: var(--primary-color); color: white; border-color: var(--primary-color);' : '';
        const imgStyle = isMe ? 'border: 2px solid var(--primary-color); padding: 2px;' : '';
        const youTag = isMe ? `<span style="font-size:10px; background:var(--primary-color); color:white; padding:2px 6px; border-radius:10px; margin-left:6px; vertical-align:middle;">You</span>` : '';
        
        return `
        <div class="rank-item" style="${activeStyle}">
            <div class="rank-number-container">
                <div class="rank-number-badge ${numBadgeClass}" style="${numBadgeStyle}">${rank}</div>
            </div>
            <img src="${avatarUrl}" alt="${displayName}" class="rank-item-avatar" style="${imgStyle}">
            <div class="rank-item-info">
                <h4 class="rank-item-name">${displayName} ${youTag}</h4>
                <p class="rank-item-level"><i class="fa-solid fa-medal" style="color:#CD7F32; margin-right:4px;"></i> Lvl ${user.level} Explorer</p>
            </div>
            <div class="rank-item-xp"><i class="fa-solid fa-bolt" style="color:#FFD700; margin-right:4px;"></i> ${parseInt(user.total_xp).toLocaleString()}</div>
        </div>`;
    }
})();
</script>

<!-- Include Bottom Navigation Component -->

<!-- Map View -->
<?php
$pageTitle = 'Explore Map';
$activeTab = 'map';
?>





<div class="map-container animate-fade-in">
    <!-- Map Container -->
    <div id="tourist-map"></div>

    <!-- Floating Search & Filters -->
    <div class="map-floating-header stagger-1">
        <div class="map-search">
            <i class="fa-solid fa-location-arrow"></i>
            <input type="text" id="map-search-input" placeholder="Search places on map...">
        </div>
        <!-- Categories Container -->
        <div class="map-categories" id="map-categories-container">
            <!-- Dynamically populated -->
        </div>
    </div>

    <!-- Locate Me Button -->
    <div class="btn-locate-me animate-slide-up" id="btn-locate-me">
        <i class="fa-solid fa-crosshairs"></i>
    </div>



        <!-- Layer Toggle Button -->
    <div class="btn-layer-toggle animate-slide-up" id="btn-layer-toggle" style="position: absolute; bottom: calc(340px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(0,0,0,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1000; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-layer-group"></i>
    </div>

    <!-- 3D Mode Button -->
    <div class="btn-3d-view animate-slide-up" id="btn-3d-view" style="position: absolute; bottom: calc(280px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(0,0,0,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1000; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-cube"></i>
    </div>

    <!-- Bottom Sheet (hidden by default) -->
    <div class="bottom-sheet" id="place-details-sheet">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="flex:1;">
                <h3 class="sheet-title" id="sheet-title">Destination Name</h3>
                <p class="sheet-location"><i class="fa-solid fa-location-dot"></i> <span id="sheet-location">Location details</span></p>
            </div>
            <button class="btn-close" onclick="window.closeSheet()" style="background:rgba(0,0,0,0.05); border:none; width:30px; height:30px; border-radius:15px; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <img src="" alt="Place Image" class="sheet-img" id="sheet-img">
        
        <!-- Tourist Guide Details -->
        <div id="tourist-guide-details" style="display:none; flex-direction:column; gap:8px; margin-bottom:16px; font-size:13px;">
            <h4 style="margin:4px 0; font-size:14px; color:#8E8E93; text-transform:uppercase;">Tourist Guide Details</h4>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                <span><i class="fa-solid fa-ticket" style="color:var(--primary-color); width:20px;"></i> Entrance Fee</span>
                <strong id="sheet-fee">₱0</strong>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                <span><i class="fa-solid fa-utensils" style="color:var(--primary-color); width:20px;"></i> Avg. Food</span>
                <strong id="sheet-food">₱0</strong>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                <span><i class="fa-solid fa-van-shuttle" style="color:var(--primary-color); width:20px;"></i> Avg. Transport</span>
                <strong id="sheet-transport">₱0</strong>
            </div>
            <div id="sheet-desc-container" style="margin-top:8px;">
                <h5 style="margin:0 0 4px 0; font-size:13px; color:var(--text-dark);">About this location</h5>
                <p id="sheet-desc" style="color:#666; line-height:1.4; margin:0;"></p>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top: 10px;">
            <button class="btn-primary" style="flex:1; padding: 12px; font-size:14px;" onclick="window.addToItinerary()">Add to Itinerary</button>
            <button id="btn-show-route" style="flex:1; padding: 12px; font-size:14px; background:white; color:var(--primary-color); border:2px solid var(--primary-color); border-radius:12px; font-weight:700;">Show Route</button>
        </div>
    </div>

    <!-- Route Details & Fares Sheet -->
    <div class="bottom-sheet" id="route-details-sheet" style="padding-bottom: 30px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="flex:1;">
                <h3 class="sheet-title">Route & Fares</h3>
                <p class="sheet-location" style="margin-top:4px;"><i class="fa-solid fa-route" style="color:var(--primary-color);"></i> <span id="route-distance" style="font-weight:700;">0 km</span> &nbsp;|&nbsp; <i class="fa-regular fa-clock" style="color:var(--secondary-color);"></i> <span id="route-time" style="font-weight:700;">0 min</span></p>
            </div>
            <button class="btn-close" onclick="document.getElementById('route-details-sheet').classList.remove('active')" style="background:rgba(0,0,0,0.05); border:none; width:30px; height:30px; border-radius:15px; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <h4 style="margin: 16px 0 10px 0; font-size: 14px; text-transform:uppercase; color:#8E8E93; letter-spacing:0.5px;">Estimated Transport Options</h4>
        <div style="display:flex; flex-direction:column; gap:10px; max-height:45vh; overflow-y:auto; padding-bottom:20px; -webkit-overflow-scrolling: touch;" id="fare-list">
            <!-- Dynamic fares injected here -->
        </div>
    </div>
</div>

<!-- Include Bottom Navigation Component -->




<script>
(function() {
    // In an SPA context, this script is executed every time the view is injected.
    if (window.mapInstance) {
        try { window.mapInstance.remove(); } catch(e) {}
        window.mapInstance = null;
    }

    window.allMapLocations = window.allMapLocations || [];
    window.currentDestinationForRoute = null;
    window.userMarker = null;
    window.mapMarkers = [];

    window.initMap = async function() {
        const mapEl = document.getElementById('tourist-map');
        if (!mapEl) return;

        const style = {
            "version": 8,
            "glyphs": "https://basemaps.cartocdn.com/gl/positron-gl-style/fonts/{fontstack}/{range}.pbf",
            "sources": {
                "satellite": {
                    "type": "raster",
                    "tiles": ["https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"],
                    "tileSize": 256
                },
                "osm": {
                    "type": "raster",
                    "tiles": ["https://a.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png"],
                    "tileSize": 256
                },
                "terrain": {
                    "type": "raster-dem",
                    "tiles": ["https://s3.amazonaws.com/elevation-tiles-prod/terrarium/{z}/{x}/{y}.png"],
                    "encoding": "terrarium",
                    "tileSize": 256
                }
            },
            "layers": [
                {
                    "id": "satellite",
                    "type": "raster",
                    "source": "satellite",
                    "layout": { "visibility": "none" }
                },
                {
                    "id": "base-map",
                    "type": "raster",
                    "source": "osm",
                    "layout": { "visibility": "visible" }
                }
            ]
        };

        window.mapInstance = new maplibregl.Map({
            container: 'tourist-map',
            style: style,
            center: [120.3167, 16.6159],
            zoom: 11,
            pitch: 0,
            attributionControl: false
        });

        // Add Zoom Controls (+ and -)
        window.mapInstance.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'bottom-right');

        // Add 3D Terrain and Region Mask
        window.mapInstance.on('load', async () => {
            window.mapInstance.setTerrain({ "source": "terrain", "exaggeration": 1.5 });

            try {
                // Use local cached JSON to eliminate the 3-second Nominatim network lag
                const regionRes = await fetch('assets/la_union.json');
                const regionData = await regionRes.json();
                if (regionData && regionData[0] && regionData[0].geojson) {
                    const geojson = regionData[0].geojson;
                    
                    const worldBox = [ [180, 90], [-180, 90], [-180, -90], [180, -90], [180, 90] ];
                    let coordinates = [];
                    if (geojson.type === 'Polygon') {
                        coordinates = [worldBox, ...geojson.coordinates];
                    } else if (geojson.type === 'MultiPolygon') {
                        let holes = [];
                        geojson.coordinates.forEach(polygon => { holes.push(polygon[0]); });
                        coordinates = [worldBox, ...holes];
                    }

                    if (coordinates.length > 0) {
                        window.mapInstance.addSource('mask', {
                            'type': 'geojson',
                            'data': { "type": "Feature", "geometry": { "type": "Polygon", "coordinates": coordinates } }
                        });
                        window.mapInstance.addLayer({
                            'id': 'mask-layer',
                            'type': 'fill',
                            'source': 'mask',
                            'paint': { 'fill-color': '#F2F2F7', 'fill-opacity': 1 }
                        });
                    }
                    
                    let bounds = new maplibregl.LngLatBounds();
                    if (geojson.type === 'Polygon') {
                        geojson.coordinates[0].forEach(coord => bounds.extend(coord));
                    } else if (geojson.type === 'MultiPolygon') {
                        geojson.coordinates.forEach(poly => poly[0].forEach(coord => bounds.extend(coord)));
                    }
                    window.mapInstance.setMaxBounds(bounds);
                }
            } catch(e) { console.error("Failed to slice region:", e); }

            // Fetch and render markers
            try {
                let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';
                const response = await fetch(backendUrl + '/api/public/map', {
                    headers: { 'ngrok-skip-browser-warning': 'true', 'Accept': 'application/json' }
                });
                const data = await response.json();
                window.allMapLocations = data.destinations || [];

                setupFilters();
                renderMarkers(window.allMapLocations);
                
                setTimeout(() => {
                    const pendingStr = localStorage.getItem('Intan_Elyu_pending_route');
                    if (pendingStr) {
                        localStorage.removeItem('Intan_Elyu_pending_route');
                        const place = JSON.parse(pendingStr);
                        window.mapInstance.flyTo({ center: [parseFloat(place.lng), parseFloat(place.lat) - 0.02], zoom: 14 });
                        window.openSheet(place);
                        setTimeout(() => {
                            const routeBtn = document.getElementById('btn-show-route');
                            if (routeBtn) routeBtn.click();
                        }, 800);
                    }
                }, 600);
            } catch (error) {
                console.error("Map fetch error:", error);
            }
        });

        setupEventListeners();
    };

    window.renderMarkers = function(locations) {
        // Remove old DOM markers (used for text labels)
        if (window.mapMarkers) {
            window.mapMarkers.forEach(m => m.remove());
        }
        window.mapMarkers = [];

        const features = locations.filter(loc => loc.lat && loc.lng).map(loc => ({
            type: 'Feature',
            geometry: {
                type: 'Point',
                coordinates: [parseFloat(loc.lng), parseFloat(loc.lat)]
            },
            properties: {
                id: loc.id,
                name: loc.name,
                category: loc.category,
                lat: loc.lat,
                lng: loc.lng,
                raw_data: JSON.stringify(loc)
            }
        }));

        const geojsonData = {
            type: 'FeatureCollection',
            features: features
        };

        if (window.mapInstance.getSource('tourist-spots')) {
            window.mapInstance.getSource('tourist-spots').setData(geojsonData);
        } else {
            window.mapInstance.addSource('tourist-spots', {
                type: 'geojson',
                data: geojsonData
            });

            // Native WebGL Circles (Zero Lag)
            window.mapInstance.addLayer({
                id: 'tourist-spots-circles',
                type: 'circle',
                source: 'tourist-spots',
                paint: {
                    'circle-color': '#007AFF', // var(--primary-color)
                    'circle-radius': 12,
                    'circle-stroke-width': 3,
                    'circle-stroke-color': '#FFFFFF'
                }
            });

            window.mapInstance.on('click', 'tourist-spots-circles', (e) => {
                const props = e.features[0].properties;
                const loc = JSON.parse(props.raw_data);
                
                window.openSheet(loc);
                window.mapInstance.flyTo({
                    center: [parseFloat(loc.lng), parseFloat(loc.lat) - 0.02],
                    zoom: 14,
                    duration: 1000
                });
            });
        }

        // Lightweight DOM Elements JUST for the names (Bypasses font server CORS)
        locations.forEach(loc => {
            if(!loc.lat || !loc.lng || !loc.name) return;

            const el = document.createElement('div');
            el.style.cssText = 'font-size:11px; font-weight:900; color:#1C1C1E; text-shadow: -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff; white-space:nowrap; pointer-events:none; transform: translate(0, -20px);';
            el.textContent = loc.name;
            
            const marker = new maplibregl.Marker({ element: el })
                .setLngLat([parseFloat(loc.lng), parseFloat(loc.lat)])
                .addTo(window.mapInstance);

            window.mapMarkers.push(marker);
        });
    }

    function setupFilters() {
        const container = document.getElementById('map-categories-container');
        if (!container) return;

        const categories = [...new Set(window.allMapLocations.map(loc => loc.category).filter(Boolean))];
        let html = `<div class="category-pill active" onclick="filterCategory('All', this)">All</div>`;
        categories.forEach(cat => {
            html += `<div class="category-pill" onclick="filterCategory('${cat}', this)">${cat}</div>`;
        });
        container.innerHTML = html;
    }

    window.filterCategory = function(category, element) {
        document.querySelectorAll('.category-pill').forEach(pill => pill.classList.remove('active'));
        if(element) element.classList.add('active');

        const searchInput = document.getElementById('map-search-input');
        const searchText = searchInput ? searchInput.value.toLowerCase() : '';
        
        const filtered = window.allMapLocations.filter(loc => {
            const name = loc.name ? loc.name.toLowerCase() : '';
            const location = loc.location ? loc.location.toLowerCase() : '';
            return (name.includes(searchText) || location.includes(searchText)) && (category === 'All' || loc.category === category);
        });
        
        window.renderMarkers(filtered);

        if (filtered.length > 0 && window.mapInstance) {
            const bounds = new maplibregl.LngLatBounds();
            filtered.forEach(loc => bounds.extend([parseFloat(loc.lng), parseFloat(loc.lat)]));
            window.mapInstance.fitBounds(bounds, { padding: 50, duration: 1000, maxZoom: 15 });
        }
    };

    function setupEventListeners() {
        const searchInput = document.getElementById('map-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const activeCatEl = document.querySelector('.category-pill.active');
                const activeCat = activeCatEl ? activeCatEl.innerText : 'All';
                window.filterCategory(activeCat, activeCatEl || document.querySelector('.category-pill'));
            });
        }

        const locateBtn = document.getElementById('btn-locate-me');
        if (locateBtn) {
            locateBtn.addEventListener('click', () => {
                showToast("Locating...");
                const handleLocation = (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    window.mapInstance.flyTo({ center: [lng, lat], zoom: 15 });
                    
                    if (window.userMarker) window.userMarker.remove();
                    const el = document.createElement('div');
                    el.innerHTML = `<div style="background:#007AFF; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow:0 0 0 5px rgba(0,122,255,0.3);"></div>`;
                    window.userMarker = new maplibregl.Marker({element: el}).setLngLat([lng, lat]).addTo(window.mapInstance);
                };
                if ("geolocation" in navigator) navigator.geolocation.getCurrentPosition(handleLocation, () => showToast("Location denied"), { enableHighAccuracy: false, timeout: 30000 });
            });
        }
        let isSatellite = false;
        const btn3d = document.getElementById('btn-3d-view');
        const btnLayer = document.getElementById('btn-layer-toggle');

        if (btnLayer) {
            btnLayer.addEventListener('click', () => {
                isSatellite = !isSatellite;
                
                if (isSatellite) {
                    btnLayer.style.background = 'var(--primary-color)';
                    btnLayer.style.color = 'white';
                    window.mapInstance.setLayoutProperty('base-map', 'visibility', 'none');
                    window.mapInstance.setLayoutProperty('satellite', 'visibility', 'visible');
                    showToast("Satellite Layer Enabled");
                } else {
                    btnLayer.style.background = 'rgba(255, 255, 255, 0.95)';
                    btnLayer.style.color = 'var(--primary-color)';
                    window.mapInstance.setLayoutProperty('satellite', 'visibility', 'none');
                    window.mapInstance.setLayoutProperty('base-map', 'visibility', 'visible');
                    showToast("Street Layer Enabled");
                }
            });
        }

        if (btn3d) {
            btn3d.addEventListener('click', () => {
                const is3D = btn3d.classList.toggle('active');
                if (is3D) {
                    btn3d.style.background = 'var(--primary-color)';
                    btn3d.style.color = 'white';
                    window.mapInstance.easeTo({ pitch: 65, bearing: -20, duration: 1000 });
                    showToast("3D Terrain View Enabled");
                } else {
                    btn3d.style.background = 'rgba(255, 255, 255, 0.95)';
                    btn3d.style.color = 'var(--primary-color)';
                    window.mapInstance.easeTo({ pitch: 0, bearing: 0, duration: 1000 });
                    showToast("2D View Restored");
                }
            });
        }

        const showRouteBtn = document.getElementById('btn-show-route');
        if(showRouteBtn) {
            showRouteBtn.addEventListener('click', async () => {
                if (!window.currentDestinationForRoute) return;
                const dest = window.currentDestinationForRoute;
                showToast("Calculating route...");
                window.closeSheet();

                const handleRouting = async (position) => {
                    const startLat = position.coords.latitude;
                    const startLng = position.coords.longitude;
                    
                    try {
                        const res = await fetch(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${dest.lng},${dest.lat}?overview=full&geometries=geojson`);
                        const routeData = await res.json();
                        if (routeData.code !== 'Ok' || !routeData.routes.length) throw new Error("No route");

                        const route = routeData.routes[0];
                        const geojson = route.geometry;

                        if (window.mapInstance.getSource('route')) {
                            window.mapInstance.removeLayer('route-line');
                            window.mapInstance.removeSource('route');
                        }
                        
                        window.mapInstance.addSource('route', { 'type': 'geojson', 'data': geojson });
                        window.mapInstance.addLayer({
                            'id': 'route-line',
                            'type': 'line',
                            'source': 'route',
                            'layout': { 'line-join': 'round', 'line-cap': 'round' },
                            'paint': { 'line-color': '#007AFF', 'line-width': 5 }
                        });

                        const bounds = geojson.coordinates.reduce((b, coord) => b.extend(coord), new maplibregl.LngLatBounds(geojson.coordinates[0], geojson.coordinates[0]));
                        window.mapInstance.fitBounds(bounds, { padding: 50 });

                        if (window.userMarker) window.userMarker.remove();
                        const el = document.createElement('div');
                        el.innerHTML = `<div style="background:#007AFF; width:20px; height:20px; border-radius:50%; border:3px solid white;"></div>`;
                        window.userMarker = new maplibregl.Marker({element: el}).setLngLat([startLng, startLat]).addTo(window.mapInstance);

                        // Fare calculation
                        const distanceKm = route.distance / 1000;
                        const durationMin = route.duration / 60;
                        document.getElementById('route-distance').textContent = distanceKm.toFixed(1) + ' km';
                        document.getElementById('route-time').textContent = Math.round(durationMin) + ' mins';
                        
                        const destName = dest.name.toLowerCase();
                        const isHighTerrain = destName.includes('falls') || destName.includes('peak') || destName.includes('mountain') || destName.includes('ridge');
                        
                        const createCard = (name, icon, color, desc, fare) => `
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid #eee; border-radius:16px; background:white;">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="background:rgba(0,0,0,0.03); width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:${color}; font-size:18px;"><i class="fa-solid ${icon}"></i></div>
                                <div><h5 style="margin:0 0 2px 0; font-size:15px; font-weight:800; color:var(--text-dark);">${name}</h5><span style="font-size:12px; color:#8E8E93;">${desc}</span></div>
                            </div>
                            <div style="font-weight:800; color:var(--text-dark); font-size:18px;">₱${fare}</div>
                        </div>`;

                        let faresHtml = '';
                        if (isHighTerrain) {
                            faresHtml += createCard('Habal-Habal', 'fa-motorcycle', '#FF3B30', 'Primary: Best for steep mountain climbs', Math.round(50 + (distanceKm * 15)));
                        } else if (distanceKm <= 5) {
                            faresHtml += createCard('Tricycle', 'fa-motorcycle', 'var(--secondary-color)', 'Primary: Private & direct', Math.round(20 + (Math.max(0, distanceKm - 1) * 10)));
                        } else {
                            faresHtml += createCard('Jeepney', 'fa-van-shuttle', 'var(--primary-color)', 'Primary: Designated terminal route', Math.round(13 + (Math.max(0, distanceKm - 4) * 1.80)));
                        }
                        
                        document.getElementById('fare-list').innerHTML = faresHtml;
                        document.getElementById('route-details-sheet').classList.add('active');
                    } catch (err) { showToast("Failed to calculate route."); }
                };
                if ("geolocation" in navigator) navigator.geolocation.getCurrentPosition(handleRouting, () => showToast("Location denied"), { enableHighAccuracy: false, timeout: 30000 });
            });
        }
    }

    window.openSheet = function(locationData) {
        document.getElementById('route-details-sheet').classList.remove('active');
        window.currentDestinationForRoute = locationData;
        document.getElementById('sheet-title').textContent = locationData.name;
        document.getElementById('sheet-location').textContent = locationData.location;
        document.getElementById('sheet-img').src = locationData.image || 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
        
        document.getElementById('tourist-guide-details').style.display = 'flex';
        document.getElementById('sheet-fee').textContent = '₱' + (locationData.entrance_fee || 0);
        document.getElementById('sheet-food').textContent = '₱' + (locationData.avg_food_cost || 0);
        document.getElementById('sheet-transport').textContent = '₱' + (locationData.avg_transport_cost || 0);
        if (locationData.description) {
            document.getElementById('sheet-desc-container').style.display = 'block';
            document.getElementById('sheet-desc').textContent = locationData.description;
        } else {
            document.getElementById('sheet-desc-container').style.display = 'none';
        }

        document.getElementById('place-details-sheet').classList.add('active');
    };

    window.closeSheet = function() {
        document.getElementById('place-details-sheet').classList.remove('active');
    };

    window.addToItinerary = function() {
        if (!window.currentDestinationForRoute) return;
        const dest = window.currentDestinationForRoute;
        let draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        if (draft.some(item => item.id === dest.id)) return showToast("Already in your itinerary!");
        draft.push({ ...dest, entrance_fee: dest.entrance_fee || 0, avg_food_cost: dest.avg_food_cost || 0, avg_transport_cost: dest.avg_transport_cost || 0 });
        localStorage.setItem('Intan_Elyu_draft_itinerary', JSON.stringify(draft));
        showToast("Added to Itinerary!");
        window.closeSheet();
    };

    setTimeout(window.initMap, 50);
})();
</script>








<!-- Profile View -->
<?php
$pageTitle = 'My Profile';
$activeTab = 'profile';
?>



<div class="profile-container has-bottom-nav animate-slide-up" style="padding-top: env(safe-area-inset-top, 40px);">
    
    <div class="profile-header stagger-1">
        <div class="profile-avatar-container">
            <img src="https://i.pravatar.cc/150?img=11" alt="Profile" class="profile-avatar">
            <div class="profile-level-badge" id="profile-lvl">Lvl 1</div>
        </div>
        <h2 class="profile-name" id="profile-name">Loading...</h2>
        <p class="profile-email" id="profile-email">loading@example.com</p>
    </div>
    
    <div class="stats-container stagger-2">
        <div class="stat-card">
            <div class="stat-value" id="stat-xp">0</div>
            <div class="stat-label">Total XP</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-places">0</div>
            <div class="stat-label">Places Visited</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-badges">0</div>
            <div class="stat-label">Badges</div>
        </div>
    </div>
    
    <h3 class="stagger-3" style="font-size: 18px; margin-bottom: 12px; margin-left: 8px;">Trip History</h3>
    <div id="trip-history-list" class="stagger-3" style="margin-bottom: 24px;">
        <div style="text-align:center; padding:20px; color:#8E8E93; font-size:14px; background:var(--glass-bg); border-radius:16px;">Loading history...</div>
    </div>
    

    
    <h3 class="stagger-3" style="font-size: 18px; margin-bottom: 12px; margin-left: 8px;">Account</h3>
    
    <div class="settings-group stagger-3">
        <a href="#" class="settings-item" onclick="navigateTo('settings'); return false;">
            <div class="settings-icon" style="background: #8E8E93;"><i class="fa-solid fa-gear"></i></div>
            <div class="settings-text">Settings</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="showToast('Edit Profile coming soon!')">
            <div class="settings-icon" style="background: #007AFF;"><i class="fa-solid fa-user-pen"></i></div>
            <div class="settings-text">Edit Profile</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="showToast('Help center coming soon!')">
            <div class="settings-icon" style="background: #34C759;"><i class="fa-solid fa-circle-question"></i></div>
            <div class="settings-text">Help & Support</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="handleLogout(event)">
            <div class="settings-icon" style="background: #FF3B30;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="settings-text" style="color: #FF3B30;">Log Out</div>
        </a>
    </div>
    
</div>

<!-- Include Bottom Navigation Component -->


<script>
    let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';

    async function fetchProfileData() {
        try {
            const response = await fetch(backendUrl + '/api/tourist/profile', {
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                }
            });
            const data = await response.json();
            
            if (response.ok) {
                document.getElementById('stat-xp').textContent = data.user.xp || 0;
                document.getElementById('stat-places').textContent = data.places_visited || 0;
                document.getElementById('stat-badges').textContent = data.user.badges ? data.user.badges.length : 0;
                
                document.getElementById('profile-name').textContent = data.user.name;
                document.getElementById('profile-email').textContent = data.user.email;
                document.getElementById('profile-lvl').textContent = "Lvl " + (data.user.level || 1);

                const historyList = document.getElementById('trip-history-list');
                if (!data.completed_trips || data.completed_trips.length === 0) {
                    historyList.innerHTML = '<div style="text-align:center; padding:20px; color:#8E8E93; font-size:14px; background:var(--glass-bg); border-radius:16px;">No completed trips yet. Start exploring!</div>';
                    return;
                }

                let html = '';
                data.completed_trips.forEach(trip => {
                    const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date';
                    html += `
                    <div style="background:var(--glass-bg); backdrop-filter:blur(16px); border:1px solid var(--glass-border); border-radius:16px; padding:16px; margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <strong style="color:var(--text-dark); font-size:16px;">${trip.title}</strong>
                            <span style="color:#34C759; font-weight:700; font-size:14px;">Completed</span>
                        </div>
                        <div style="font-size:13px; color:#8E8E93; margin-bottom:12px;">
                            <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> ${date}
                            <span style="margin:0 8px;">&bull;</span>
                            <i class="fa-solid fa-coins" style="margin-right:4px;"></i> ₱${trip.total_cost || 0}
                        </div>
                        <div style="font-size:12px; color:#666;">
                            ${trip.items.length} Destinations Visited
                        </div>
                    </div>`;
                });
                historyList.innerHTML = html;
            }
        } catch (e) {
            console.error(e);
        }
    }

    // handleLogout is now globally available in main.js

    fetchProfileData();
</script>
<!-- Saved Trips View -->
<?php
$pageTitle = 'Saved Trips';
$activeTab = 'itinerary'; // Keep it active for the Trips tab
?>



<!-- Premium Hero Header -->
<div
    style="background: linear-gradient(135deg, var(--primary-color) 0%, #005ce6 100%); color: white; padding: 40px 20px 30px 20px; border-radius: 0 0 32px 32px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,122,255,0.3); position: relative; overflow: hidden; margin-top: -10px;">
    <!-- Decorative elements -->
    <div
        style="position: absolute; top: -30px; right: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;">
    </div>
    <div
        style="position: absolute; bottom: -20px; left: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%;">
    </div>

    <div style="display: flex; align-items: center; margin-bottom: 12px; position: relative; z-index: 1;">
        <button onclick="navigateTo('itinerary')"
            style="background: rgba(255,255,255,0.2); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 16px; backdrop-filter: blur(4px);">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <h2 style="margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 0.5px;">Saved Trips</h2>
    </div>
    <p style="margin: 0; font-size: 15px; opacity: 0.9; position: relative; z-index: 1; padding-left: 56px;">Your
        planned itineraries and past adventures.</p>
</div>

<div class="saved-trips-container animate-slide-up" style="padding-top: 10px;">
    <!-- Empty State -->
    <div id="saved-empty-state" style="display:none; text-align:center; margin-top:80px; opacity:0.6;">
        <i class="fa-solid fa-folder-open"
            style="font-size: 54px; margin-bottom: 16px; color:var(--primary-color);"></i>
        <h3 style="margin-bottom:8px;">No saved trips yet</h3>
        <p style="font-size:14px;">Create a draft plan and save it to see it here!</p>
        <button class="btn-primary" style="margin-top: 20px; width:auto; padding:12px 24px;"
            onclick="navigateTo('itinerary')">Back to Draft Plan</button>
    </div>

    <!-- Saved Trips List -->
    <div id="saved-trips-list" style="margin-top: 20px;"></div>
</div>

<!-- Map Preview Modal -->
<div id="map-preview-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:var(--bg-color); z-index:1000; flex-direction:column; overscroll-behavior: none;">
    <div style="position:relative; flex:1; width:100%;">
        <!-- Map sits underneath everything -->
        <div id="preview-map" style="position:absolute; top:0; left:0; right:0; bottom:0;"></div>
        
        <!-- Solid Black Header -->
        <div style="position:absolute; top:0; left:0; right:0; padding:40px 20px 20px 20px; background:#1C1C1E; display:flex; justify-content:space-between; align-items:center; z-index:10; touch-action: none;">
            <h3 style="margin:0; color:white;">Your Journey</h3>
            <button onclick="closePreviewRoute()" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:none; width:36px; height:36px; border-radius:18px; color:white; pointer-events:auto; box-shadow:0 2px 8px rgba(0,0,0,0.3);"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Legend -->
        <div style="position: absolute; bottom: 24px; left: 16px; background: rgba(255,255,255,0.15); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); padding: 12px 16px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2); z-index: 20; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <div style="width: 14px; height: 14px; border-radius: 50%; background: #34C759; border: 2px solid white;"></div>
                <span style="font-size: 13px; color: white; font-weight: 600;">Visited</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 14px; height: 14px; border-radius: 50%; background: #007AFF; border: 2px solid white;"></div>
                <span style="font-size: 13px; color: white; font-weight: 600;">Pending</span>
            </div>
        </div>

        <!-- Start Navigation Button -->
        <button id="start-nav-btn" class="btn-primary" style="position: absolute; bottom: 24px; right: 16px; z-index: 20; width: auto; padding: 14px 24px; border-radius: 100px; font-weight: bold; box-shadow: 0 8px 32px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 8px;" onclick="window.startInAppNavigation()">
            <i class="fa-solid fa-location-arrow"></i> Start
        </button>
    </div>
</div>

<!-- Add New Trip FAB -->
<button onclick="localStorage.removeItem('Intan_Elyu_active_trip_id'); navigateTo('itinerary');" style="position:fixed; bottom:100px; right:20px; width:56px; height:56px; border-radius:28px; background:var(--primary-color); color:white; border:none; box-shadow:0 8px 24px rgba(0,122,255,0.4); display:flex; justify-content:center; align-items:center; font-size:24px; z-index:90; cursor:pointer; transition:transform 0.2s;">
    <i class="fa-solid fa-plus"></i>
</button>

<!-- Check-in Verification Modal -->
<div id="checkin-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div
        style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:90%; max-width:400px; box-shadow:0 20px 40px rgba(0,0,0,0.2); text-align:center;">
        <h3 style="margin-top:0;">Verify Check-In</h3>
        <p style="font-size:14px; color:#EBEBF5; margin-bottom:20px;">Prove you are here to earn your XP! Select a
            verification method.</p>

        <input type="hidden" id="checkin-item-id">

        <button class="btn-primary" style="width:100%; margin-bottom:12px; padding:16px;" onclick="verifyGpsCheckIn()"
            id="btn-verify-gps">
            <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify via GPS
        </button>

        <div style="position:relative;">
            <button class="btn-primary"
                style="width:100%; background:transparent; border:2px dashed var(--primary-color); color:var(--primary-color); padding:16px; margin-bottom:12px;"
                onclick="document.getElementById('proof-photo').click()" id="btn-verify-photo">
                <i class="fa-solid fa-camera" style="margin-right:8px;"></i> Upload Photo Proof
            </button>
            <input type="file" id="proof-photo" accept="image/*" style="display:none;"
                onchange="verifyPhotoCheckIn(this)">
        </div>

        <button class="btn-primary"
            style="width:100%; background:#FF9500; border:none; padding:16px; margin-bottom:24px;"
            onclick="verifyTestCheckIn()" id="btn-verify-test">
            <i class="fa-solid fa-check-double" style="margin-right:8px;"></i> Mark as Visited
        </button>

        <button class="btn-primary" style="width:100%; background:#1C1C1E; border:1px solid #333333; color:white;"
            onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<!-- Edit Budget Modal -->
<div id="edit-budget-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:90%; max-width:340px; box-shadow:0 24px 48px rgba(0,0,0,0.4); text-align:center; animation:popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
        <h3 style="margin-top:0; font-size:20px; font-weight:800;">Set Trip Budget</h3>
        <p style="font-size:14px; color:#EBEBF5; margin-bottom:24px;">Enter your estimated budget for this trip. We'll help you track your costs!</p>

        <input type="hidden" id="edit-budget-trip-id">
        
        <div style="position:relative; margin-bottom:24px;">
            <span style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--primary-color); font-size:20px; font-weight:800;">₱</span>
            <input type="number" id="edit-budget-input" placeholder="0.00" style="width:100%; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:16px 16px 16px 44px; color:white; font-size:28px; font-weight:800; outline:none; text-align:center; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>

        <div style="display:flex; gap:12px;">
            <button class="btn-primary" style="flex:1; background:rgba(255,255,255,0.1); border:none; color:white; padding:16px; font-weight:700;" onclick="window.closeEditBudgetModal()">Cancel</button>
            <button class="btn-primary" style="flex:1; padding:16px; background:var(--primary-color); font-weight:700;" onclick="window.saveTripBudget()">Save</button>
        </div>
    </div>
</div>

<script>
    (function () {
        let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';

        window.fetchSavedTrips = async function () {
            try {
                const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                    headers: {
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    renderSavedTrips(data.itineraries || []);
                }
            } catch (error) {
                console.error("Error fetching saved trips:", error);
            }
        };

        function renderSavedTrips(itineraries) {
            window.savedTripsData = itineraries; // Save globally for resume functionality
            const list = document.getElementById('saved-trips-list');
            const empty = document.getElementById('saved-empty-state');

            // Capture currently open accordions
            const openAccordions = new Set();
            document.querySelectorAll('[id^="accordion-"]').forEach(el => {
                if (el.style.maxHeight && el.style.maxHeight !== '0px') {
                    openAccordions.add(el.id.replace('accordion-', ''));
                }
            });

            if (!itineraries || itineraries.length === 0) {
                list.innerHTML = '';
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';
            let html = '';

            itineraries.forEach(trip => {
                let budgetIndicator = '';
                if (trip.budget && trip.budget > 0) {
                    const cost = parseFloat(trip.total_cost || 0);
                    const budget = parseFloat(trip.budget);
                    const pct = cost / budget;

                    let color = '#34C759'; // Green (Safe)
                    if (pct >= 1.0) color = '#FF3B30'; // Red (Over/Warning)
                    else if (pct >= 0.8) color = '#FF9500'; // Orange (Near)

                    budgetIndicator = `&nbsp;&bull;&nbsp; <span style="font-weight:700; color:${color};">Est: ₱${cost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span> <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background-color:${color}; margin-left:2px; box-shadow:0 0 4px ${color}80;"></span>`;
                } else if (trip.total_cost && trip.total_cost > 0) {
                    const cost = parseFloat(trip.total_cost);
                    budgetIndicator = `&nbsp;&bull;&nbsp; <span style="font-weight:700; color:var(--text-muted);">Est: ₱${cost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`;
                }

                let destinationCount = trip.items ? trip.items.length : 0;
                
                html += `
            <div style="background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:16px; padding:20px; margin-bottom:20px; box-shadow:var(--glass-shadow); transition: all 0.3s ease;">
                <div onclick="window.toggleTripAccordion('${trip.id}')" style="cursor:pointer; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="margin:0 0 4px 0; color:var(--text-dark);">${trip.title}</h3>
                        <p style="font-size:12px; color:var(--text-muted); margin:0; display:flex; align-items:center; flex-wrap:wrap; gap:4px;">
                            ${trip.trip_date ? 'Date: ' + new Date(trip.trip_date).toLocaleDateString() : 'No date set'} 
                            ${trip.budget ? '&nbsp;&bull;&nbsp; <span style="color:var(--primary-color); font-weight:700;">Budget: ₱' + parseFloat(trip.budget).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>' + budgetIndicator + ' <i class="fa-solid fa-pen-to-square" style="margin-left:4px; color:#A1A1A6; cursor:pointer; padding:4px;" onclick="event.stopPropagation(); window.editTripBudget(\'' + trip.id + '\', ' + trip.budget + ')"></i>' : '&nbsp;&bull;&nbsp; <span style="color:#A1A1A6; font-size:11px; cursor:pointer; padding:4px; background:rgba(255,255,255,0.05); border-radius:4px;" onclick="event.stopPropagation(); window.editTripBudget(\'' + trip.id + '\', \'\')"><i class="fa-solid fa-plus"></i> Add Budget</span>'}
                        </p>
                        <p style="font-size:12px; color:var(--primary-color); margin:4px 0 0 0; font-weight:600;"><i class="fa-solid fa-map-pin"></i> ${destinationCount} Locations</p>
                    </div>
                    <div style="padding:4px;">
                        <i id="chevron-${trip.id}" class="fa-solid fa-chevron-down" style="color:var(--text-muted); transition: transform 0.3s ease;"></i>
                    </div>
                </div>
                
                <div id="accordion-${trip.id}" style="max-height:0; opacity:0; overflow:hidden; transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease-in-out;">
                    <div style="padding-top: 20px;">
                        <div class="timeline" style="margin-top:0;">`;

                let unvisitedCount = 0;
                if (trip.items && trip.items.length) {
                    trip.items.forEach((item, index) => {
                        const dest = item.destination;
                        const isVisited = item.is_visited;
                        if (!isVisited) unvisitedCount++;

                        html += `
                    <div class="timeline-item ${isVisited ? 'completed' : ''}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content" style="padding:12px;">
                            <h4 style="margin:0 0 4px 0; font-size:15px;">${dest ? dest.name : 'Unknown Destination'}</h4>
                            ${isVisited ?
                                '<span style="color:var(--primary-color); font-size:12px; font-weight:600;"><i class="fa-solid fa-check-circle"></i> Visited</span>' :
                                `<button class="btn-primary" style="padding: 6px 12px; font-size:11px; width:auto; border-radius:100px; margin-top:8px;" onclick="window.openCheckinModal('${item.id}')">
                                    <i class="fa-solid fa-location-arrow"></i> Check In (Earn XP)
                                 </button>`
                            }
                        </div>
                    </div>`;
                    });
                } else {
                    html += `<p style="font-size:13px; color:#999;">No destinations in this trip.</p>`;
                }

                html += `</div></div></div>`; // Close timeline, padding div, accordion div

                // Action buttons (outside accordion)
                html += `<div style="display:flex; gap:8px; margin-top:16px;">`;

                if (unvisitedCount === 0 && trip.items && trip.items.length > 0) {
                    html += `
                <button class="btn-primary" style="flex:1; background:#34C759; border:none; padding:12px;" onclick="window.markTripCompleted('${trip.id}')">
                    <i class="fa-solid fa-flag-checkered" style="margin-right:8px;"></i> Complete Trip
                </button>`;
                } else {
                    html += `
                <button class="btn-primary" style="flex:1; background:var(--primary-color); border:none; padding:12px;" onclick="window.previewRoute('${trip.id}')">
                    <i class="fa-solid fa-map-location-dot" style="margin-right:8px;"></i> Open Map
                </button>
                <button class="btn-primary" style="flex:1; background:transparent; border:1px solid var(--primary-color); color:var(--primary-color); padding:12px;" onclick="window.addPlacesToTrip('${trip.id}')">
                    <i class="fa-solid fa-plus" style="margin-right:8px;"></i> Places
                </button>`;
                }

                html += `</div></div>`; // Close buttons div and card div
            });

            list.innerHTML = html;

            // Restore previously open accordions
            openAccordions.forEach(id => {
                const content = document.getElementById('accordion-' + id);
                const chevron = document.getElementById('chevron-' + id);
                if (content && chevron) {
                    content.style.maxHeight = (content.scrollHeight + 50) + 'px';
                    content.style.opacity = '1';
                    chevron.style.transform = 'rotate(180deg)';
                }
            });
        }

        window.toggleTripAccordion = function(id) {
            const content = document.getElementById('accordion-' + id);
            const chevron = document.getElementById('chevron-' + id);
            if (content.style.maxHeight && content.style.maxHeight !== '0px') {
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                chevron.style.transform = 'rotate(0deg)';
            } else {
                content.style.maxHeight = (content.scrollHeight + 50) + 'px';
                content.style.opacity = '1';
                chevron.style.transform = 'rotate(180deg)';
            }
        };

        window.editTripBudget = function(id, currentBudget) {
            document.getElementById('edit-budget-trip-id').value = id;
            document.getElementById('edit-budget-input').value = currentBudget || '';
            
            const modal = document.getElementById('edit-budget-modal');
            modal.style.display = 'flex';
            
            // Auto focus input after modal animation
            setTimeout(() => {
                document.getElementById('edit-budget-input').focus();
            }, 300);
        };

        window.closeEditBudgetModal = function() {
            document.getElementById('edit-budget-modal').style.display = 'none';
        };

        window.saveTripBudget = async function() {
            const id = document.getElementById('edit-budget-trip-id').value;
            const newBudgetStr = document.getElementById('edit-budget-input').value;
            
            const newBudget = parseFloat(newBudgetStr);
            if (isNaN(newBudget) || newBudget < 0) {
                return showToast("Please enter a valid positive number.");
            }

            try {
                const response = await fetch(backendUrl + '/api/tourist/itineraries/' + id + '/budget', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                    },
                    body: JSON.stringify({ budget: newBudget })
                });

                if (response.ok) {
                    showToast("Budget saved! 💸");
                    window.closeEditBudgetModal();
                    window.fetchSavedTrips(); // Refresh to show new budget
                } else {
                    showToast("Failed to update budget.");
                }
            } catch (error) {
                console.error("Budget Update Error:", error);
                showToast("Network error.");
            }
        };

        window.addPlacesToTrip = function(tripId) {
            localStorage.setItem('Intan_Elyu_active_trip_id', tripId);
            navigateTo('map');
        };

        let previewMapInstance = null;

        window.previewRoute = async function (tripId) {
            const trip = (window.savedTripsData || []).find(t => t.id.toString() === tripId.toString());
            if (!trip || !trip.items || trip.items.length === 0) {
                showToast("No locations in this trip to navigate.");
                return;
            }

            window.currentPreviewTripId = tripId;
            document.getElementById('map-preview-modal').style.display = 'flex';
            
            const magicNav = document.getElementById('magic-nav');
            if (magicNav) magicNav.classList.add('hidden');

            // Initialize map
            previewMapInstance = new maplibregl.Map({
                container: 'preview-map',
                style: document.body.classList.contains('dark-theme') 
                    ? 'https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json'
                    : 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                center: [120.3209, 16.6159],
                zoom: 10,
                attributionControl: false
            });

            previewMapInstance.on('load', async () => {
                const coordinates = [];
                const bounds = new maplibregl.LngLatBounds();

                trip.items.forEach((item, index) => {
                    if (item.destination && item.destination.lat && item.destination.lng) {
                        const lat = parseFloat(item.destination.lat);
                        const lng = parseFloat(item.destination.lng);
                        coordinates.push([lng, lat]);
                        bounds.extend([lng, lat]);

                        // Add markers
                        const el = document.createElement('div');
                        el.className = 'custom-marker';
                        el.style.width = '32px';
                        el.style.height = '42px';
                        el.style.display = 'flex';
                        el.style.alignItems = 'flex-start';
                        el.style.justifyContent = 'center';
                        el.style.cursor = 'pointer';
                        el.style.filter = 'drop-shadow(0px 4px 6px rgba(0,0,0,0.3))';
                        el.style.paddingTop = '6px';
                        el.style.fontWeight = '900';
                        el.style.fontSize = '12px';
                        
                        const bgColor = item.is_visited ? '#34C759' : '#007AFF'; // Green for visited, Blue for unvisited
                        el.style.color = bgColor;
                        
                        // Use background image for the pin so the text sits naturally inside the div
                        const svgString = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="100%" height="100%"><path fill="${bgColor}" d="M172.3 501.7C27 291 0 269.4 0 192 0 86 86 0 192 0s192 86 192 192c0 77.4-27 99-172.3 309.7-9.5 13.8-29.9 13.8-39.5 0z"/><circle cx="192" cy="192" r="90" fill="white"/></svg>`;
                        el.style.backgroundImage = 'url("data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgString) + '")';
                        el.style.backgroundSize = 'contain';
                        el.style.backgroundRepeat = 'no-repeat';
                        el.style.backgroundPosition = 'center';
                        
                        el.innerText = (index + 1);

                        const statusHtml = item.is_visited 
                            ? `<span style="color:#34C759; font-weight:bold; font-size:12px;"><i class="fa-solid fa-circle-check"></i> Visited</span>` 
                            : `<span style="color:#007AFF; font-weight:bold; font-size:12px;"><i class="fa-solid fa-clock"></i> Pending</span>`;

                        const popup = new maplibregl.Popup({ offset: [0, -35], closeButton: false, className: 'smooth-map-popup' })
                            .setHTML(`
                                <div style="font-weight:700; font-size:14px; color:white; margin-bottom:4px;">${item.destination.name}</div>
                                ${statusHtml}
                            `);

                        new maplibregl.Marker({ element: el, anchor: 'bottom' })
                            .setLngLat([lng, lat])
                            .setPopup(popup)
                            .addTo(previewMapInstance);
                    }
                });

                if (coordinates.length > 0) {
                    previewMapInstance.fitBounds(bounds, { padding: 50 });
                }


            });
        };

        window.closePreviewRoute = function () {
            if (previewMapInstance) {
                previewMapInstance.remove(); // Safely destroy WebGL context
                previewMapInstance = null;
            }
            if (window.userLocationMarker) {
                window.userLocationMarker.remove();
                window.userLocationMarker = null;
            }
            document.getElementById('map-preview-modal').style.display = 'none';
            const magicNav = document.getElementById('magic-nav');
            if (magicNav) magicNav.classList.remove('hidden');
            
            const btn = document.getElementById('start-nav-btn');
            if (btn) btn.innerHTML = '<i class="fa-solid fa-location-arrow"></i> Start';
        };

        window.startInAppNavigation = function () {
            if (!navigator.geolocation) {
                showToast("Geolocation is not supported by your browser.");
                return;
            }

            const tripId = window.currentPreviewTripId;
            const trip = (window.savedTripsData || []).find(t => t.id.toString() === tripId.toString());
            if (!trip || !trip.items || trip.items.length === 0) return;

            const btn = document.getElementById('start-nav-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Locating...';
            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                if (previewMapInstance.getSource('route')) {
                    previewMapInstance.removeLayer('route');
                    previewMapInstance.removeSource('route');
                }
                
                if (window.userLocationMarker) {
                    window.userLocationMarker.remove();
                }
                
                const el = document.createElement('div');
                el.style.width = '24px';
                el.style.height = '24px';
                el.style.backgroundColor = '#007AFF';
                el.style.border = '4px solid white';
                el.style.borderRadius = '50%';
                el.style.boxShadow = '0 0 12px rgba(0, 122, 255, 0.6)';
                
                window.userLocationMarker = new maplibregl.Marker({ element: el })
                    .setLngLat([lng, lat])
                    .addTo(previewMapInstance);

                const coordinates = [[lng, lat]];
                const bounds = new maplibregl.LngLatBounds();
                bounds.extend([lng, lat]);

                trip.items.forEach(item => {
                    if (item.destination && item.destination.lat && item.destination.lng) {
                        const destLat = parseFloat(item.destination.lat);
                        const destLng = parseFloat(item.destination.lng);
                        coordinates.push([destLng, destLat]);
                        bounds.extend([destLng, destLat]);
                    }
                });

                previewMapInstance.fitBounds(bounds, { padding: 40 });

                if (coordinates.length > 1) {
                    const coordsString = coordinates.map(c => c.join(',')).join(';');
                    try {
                        const pref = trip.route_preference || 'recommended';
                        let apiUrl = `https://router.project-osrm.org/route/v1/driving/${coordsString}?overview=full&geometries=geojson`;
                        let lineColor = '#007AFF';
                        
                        if (pref === 'scenic') {
                            apiUrl = `https://router.project-osrm.org/route/v1/bike/${coordsString}?overview=full&geometries=geojson`;
                            lineColor = '#34C759'; // Green
                        } else if (pref === 'alternative') {
                            lineColor = '#FF9500'; // Orange
                        }

                        const res = await fetch(apiUrl);
                        const routeData = await res.json();

                        if (routeData.code === 'Ok') {
                            previewMapInstance.addSource('route', {
                                type: 'geojson',
                                data: {
                                    type: 'Feature',
                                    properties: {},
                                    geometry: routeData.routes[0].geometry
                                }
                            });

                            previewMapInstance.addLayer({
                                id: 'route',
                                type: 'line',
                                source: 'route',
                                layout: {
                                    'line-join': 'round',
                                    'line-cap': 'round'
                                },
                                paint: {
                                    'line-color': lineColor,
                                    'line-width': 6,
                                    'line-opacity': 0.9
                                }
                            });
                        }
                    } catch (e) {
                        console.error("OSRM Routing Error:", e);
                        showToast("Failed to calculate route.");
                    }
                }
                
                btn.innerHTML = '<i class="fa-solid fa-location-arrow"></i> Navigating...';
                btn.disabled = false;
                
            }, (error) => {
                console.error(error);
                showToast("Failed to get location. Please enable GPS.");
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, { enableHighAccuracy: true });
        };

        window.openCheckinModal = function (itemId) {
            document.getElementById('checkin-item-id').value = itemId;
            document.getElementById('checkin-modal').style.display = 'flex';
        };

        window.closeCheckinModal = function () {
            document.getElementById('checkin-modal').style.display = 'none';
            document.getElementById('checkin-item-id').value = '';
            document.getElementById('proof-photo').value = '';
        };

        window.verifyGpsCheckIn = function () {
            if (!navigator.geolocation) {
                showToast("Geolocation is not supported by your browser.");
                return;
            }

            const btn = document.getElementById('btn-verify-gps');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';
            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(async (position) => {
                await submitVerification({
                    verification_method: 'gps',
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, (error) => {
                console.error(error);
                showToast("Failed to get location. Make sure GPS is enabled.");
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, { enableHighAccuracy: true });
        };

        window.verifyPhotoCheckIn = async function (input) {
            if (!input.files || input.files.length === 0) return;

            const btn = document.getElementById('btn-verify-photo');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('verification_method', 'photo');
            formData.append('proof_photo', input.files[0]);

            await submitVerification(formData, true);

            btn.innerHTML = originalText;
            btn.disabled = false;
            input.value = ''; // Reset
        };

        window.verifyTestCheckIn = async function () {
            const btn = document.getElementById('btn-verify-test');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking In...';
            btn.disabled = true;

            await submitVerification({
                verification_method: 'test'
            });

            btn.innerHTML = originalText;
            btn.disabled = false;
        };

        async function submitVerification(data, isFormData = false) {
            const itemId = document.getElementById('checkin-item-id').value;
            if (!itemId) return;

            try {
                const options = {
                    method: 'POST', // Use POST with _method=PATCH for multipart/form-data support in Laravel
                    headers: {
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                    }
                };

                if (isFormData) {
                    data.append('_method', 'PATCH');
                    options.body = data;
                } else {
                    data._method = 'PATCH';
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(backendUrl + '/api/tourist/itineraries/items/' + itemId + '/visit', options);
                const result = await response.json();

                if (response.ok) {
                    showToast(result.message || "Checked In! 🌟");
                    closeCheckinModal();
                    window.fetchSavedTrips(); // Refresh the list
                } else {
                    showToast(result.message || "Verification failed.");
                }
            } catch (error) {
                console.error("Verification Error:", error);
                showToast("Failed to check in. Check connection.");
            }
        }

        window.markTripCompleted = async function (id) {
            if (!confirm("Are you sure you want to mark this trip as completed? It will be moved to your History.")) return;

            try {
                const response = await fetch(backendUrl + '/api/tourist/itineraries/' + id + '/complete', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                    }
                });

                const data = await response.json();
                if (response.ok) {
                    showToast(data.message || "Trip completed!");
                    window.fetchSavedTrips(); // Refresh the list
                } else {
                    showToast(data.message || "Failed to complete trip.");
                }
            } catch (error) {
                console.error("Error completing trip:", error);
                showToast("Network error.");
            }
        };

        // Render immediately on view load
        window.fetchSavedTrips();

    })();
</script>
<?php
$pageTitle = 'Settings';
?>

<?php include __DIR__ . '/../components/header.php'; ?>



<div class="settings-container has-header has-bottom-nav animate-slide-up">
    
    <h3 class="settings-header stagger-1">Preferences</h3>
    <div class="settings-group stagger-1">
        <div class="settings-item">
            <div class="settings-label">
                <div class="icon-box red"><i class="fa-solid fa-bell"></i></div> 
                Push Notifications
            </div>
            <label class="switch">
                <input type="checkbox" id="push-notif-toggle" onchange="window.togglePushNotifications(this.checked)">
                <span class="slider"></span>
            </label>
        </div>
        <div class="settings-item">
            <div class="settings-label">
                <div class="icon-box green"><i class="fa-solid fa-location-dot"></i></div> 
                Location Services
            </div>
            <label class="switch">
                <input type="checkbox" id="location-service-toggle" onchange="window.toggleLocationServices(this.checked)">
                <span class="slider"></span>
            </label>
        </div>
        <div class="settings-item">
            <div class="settings-label">
                <div class="icon-box purple"><i class="fa-solid fa-moon"></i></div> 
                Dark Mode
            </div>
            <label class="switch">
                <input type="checkbox" id="dark-mode-toggle" onclick="document.body.classList.toggle('dark-theme', this.checked); localStorage.setItem('Intan_Elyu_Theme', this.checked ? 'dark' : 'light');">
                <span class="slider"></span>
            </label>
            <!-- Global Failsafe CSS Injector for SPA Navigation -->
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="
                if(!document.getElementById('dark-theme-styles')) {
                    const s = document.createElement('style');
                    s.id = 'dark-theme-styles';
                    s.innerHTML = `body.dark-theme { --background-light: #000000; --text-dark: #FFFFFF; --glass-bg: rgba(28, 28, 30, 0.85); --glass-border: rgba(255, 255, 255, 0.1); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5); } body.dark-theme .form-control, body.dark-theme .search-input { background: #1C1C1E; color: #FFFFFF; border-color: #2C2C2E; } body.dark-theme .bottom-sheet, body.dark-theme .map-categories .category-pill, body.dark-theme .btn-locate-me, body.dark-theme .btn-3d-view { background: rgba(28, 28, 30, 0.95); color: #FFFFFF; border-color: #2C2C2E; } body.dark-theme .btn-3d-view.active { background: var(--primary-color); border-color: var(--primary-color); } body.dark-theme .magic-nav { background: rgba(28, 28, 30, 0.85); border-color: rgba(255, 255, 255, 0.1); } body.dark-theme .magic-nav-item { color: #8E8E93; } body.dark-theme .magic-nav-item.active { color: var(--primary-light); } body.dark-theme .magic-indicator-circle { background: var(--primary-light); } body.dark-theme .settings-item { border-bottom-color: rgba(255, 255, 255, 0.05); } body.dark-theme .stat-card { background: rgba(28, 28, 30, 0.85); border-color: rgba(255, 255, 255, 0.1); } body.dark-theme .slider { background-color: rgba(255,255,255,0.2); } body.dark-theme .leaflet-popup-content-wrapper, body.dark-theme .leaflet-popup-tip { background: #1C1C1E; color: #FFFFFF; } body.dark-theme .profile-name, body.dark-theme .stat-value, body.dark-theme .settings-header, body.dark-theme .settings-label, body.dark-theme .stop-name, body.dark-theme h1, body.dark-theme h2, body.dark-theme h3, body.dark-theme h4, body.dark-theme h5, body.dark-theme strong { color: #FFFFFF !important; }`;
                    document.head.appendChild(s);
                }
                document.getElementById('dark-mode-toggle').checked = document.body.classList.contains('dark-theme');
                this.remove();
            " style="display:none;">
        </div>
    </div>
    
    <h3 class="settings-header stagger-2">Account Security</h3>
    <div class="settings-group stagger-2">
        <div class="settings-item clickable" onclick="showToast('Change Password')">
            <div class="settings-label">
                <div class="icon-box"><i class="fa-solid fa-lock"></i></div> 
                Change Password
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(0,0,0,0.2);"></i>
        </div>
        <div class="settings-item clickable" onclick="showToast('Two-Factor Auth')">
            <div class="settings-label">
                <div class="icon-box"><i class="fa-solid fa-shield-check"></i></div> 
                Two-Factor Auth
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(0,0,0,0.2);"></i>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const pushEnabled = localStorage.getItem('Intan_Elyu_push_enabled') === 'true';
        const locEnabled = localStorage.getItem('Intan_Elyu_loc_enabled') === 'true';
        document.getElementById('push-notif-toggle').checked = pushEnabled;
        document.getElementById('location-service-toggle').checked = locEnabled;
    });
</script>
<!-- Setup Profile View -->
<?php
$pageTitle = 'Setup Profile';
?>



<div class="setup-container animate-slide-up" style="padding-top: env(safe-area-inset-top, 40px);">
    
    <div class="setup-header stagger-1">
        <h2>Complete Your Profile</h2>
        <p>Let's personalize your Intan Elyu experience.</p>
    </div>
    
    <div class="avatar-upload stagger-2">
        <div class="avatar-preview" id="avatarPreview">
            <i class="fa-solid fa-user" id="avatarIcon"></i>
            <img id="avatarImage" src="" alt="Avatar">
        </div>
        <div class="avatar-btn" onclick="document.getElementById('avatarInput').click()">
            <i class="fa-solid fa-camera"></i>
        </div>
        <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
    </div>
    
    <form class="setup-form stagger-3" onsubmit="saveProfile(event)">
        <div class="form-group">
            <label class="form-label">Phone Number (Optional)</label>
            <input type="tel" class="form-control" placeholder="+63 9XX XXX XXXX">
        </div>
        
        <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea class="form-control" rows="3" placeholder="I love traveling to mountains..."></textarea>
        </div>
        
        <button type="submit" class="btn-primary" style="padding:16px; margin-top:20px; width:100%;">Save Profile</button>
        <button type="button" class="btn-skip" onclick="skipProfile()">Skip for now</button>
    </form>
    
</div>

<script>
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarIcon').style.display = 'none';
                const img = document.getElementById('avatarImage');
                img.src = e.target.result;
                img.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }
    
    function saveProfile(e) {
        e.preventDefault();
        showToast('Profile saved!', 1500);
        setTimeout(() => {
            navigateTo('dashboard');
        }, 1000);
    }
    
    function skipProfile() {
        showToast('You can update this later.', 1500);
        setTimeout(() => {
            navigateTo('dashboard');
        }, 1000);
    }
</script>
<!-- Splash Screen View -->


<div class="splash-container animate-fade-in">
    <div class="splash-logo stagger-1" style="display:flex; flex-direction:column; align-items:center;">
        <img src="assets/img/logo.png" alt="Intan Elyu Logo" style="width: 120px; height: auto; margin-bottom: 16px;">
        Intan Elyu
    </div>
    <div class="splash-tagline stagger-2">Your Ultimate Travel Companion</div>
    
    <div class="loading-dots stagger-3">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
</div>

<script>
    // Simulate loading and redirect to auth
    setTimeout(() => {
        navigateTo('auth');
    }, 2500);
</script>
<?php
$pageTitle = 'Terms & Privacy';
?>

<?php include __DIR__ . '/../components/header.php'; ?>