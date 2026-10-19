<!-- Splash Screen View -->
<script>
    // Check if returning from Google OAuth or running in dev/app mode
    const isOAuthCallback = Boolean(
        window._isProcessingGoogleOAuth || 
        (window.location.hash && (window.location.hash.includes('access_token=') || window.location.hash.includes('id_token='))) ||
        (window.location.search && (window.location.search.includes('access_token=') || window.location.search.includes('id_token='))) ||
        (window.sessionStorage && sessionStorage.getItem('intan_elyu_google_oauth_pending') === 'true')
    );

    function isDevHost(hn) {
        if (!hn) return false;
        if (hn === 'localhost' || hn === '127.0.0.1' || hn === '::1') return true;
        return /^192\.168\.\d+\.\d+$/.test(hn) || /^10\.\d+\.\d+\.\d+$/.test(hn) || /^172\.(1[6-9]|2\d|3[01])\.\d+\.\d+$/.test(hn);
    }

    const hasAppCookie = Boolean(document.cookie && document.cookie.includes('is_intan_elyu_app=1'));

    // Browser Guard: Web browsers must not access the mobile splash screen. Redirect to the official download portal.
    if (!isOAuthCallback && !isDevHost(window.location.hostname) && !hasAppCookie && !window.Capacitor && !navigator.userAgent.includes('IntanElyuAPK') && !window.location.search.includes('app=')) {
        window.location.replace('index.php?view=download');
    }
</script>

<div class="splash-container animate-fade-in" id="splash-main">
    <div class="stagger-1" style="position:relative; z-index:100;">
        <div class="splash-logo">
            <div class="splash-logo-container"
                style="box-sizing: border-box; width: 140px; height: 140px; background: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; position: relative; z-index: 10; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4); padding: 5px;">
                <img id="splash-logo-img" src="assets/img/logo.png" alt="Intan Elyu Logo"
                    style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%; display: block;">
            </div>
            <span class="splash-title">Intan Elyu</span>
        </div>
    </div>

    <div class="splash-tagline stagger-2">Your Ultimate Travel Companion</div>

    <div class="loading-dots stagger-3">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>

    <!-- Offline Mode Indicator -->
    <div id="splash-offline-pill" class="splash-offline-pill" style="display: none;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b; font-size: 11px;"></i>
        <span>Offline Mode &bull; Loading saved guides</span>
    </div>

    <!-- Animated Seamless SVG Wave -->
    <div class="wave-wrapper" id="splash-wave-wrapper">
        <div class="wave-bottom">
            <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="splashWaveGrad1" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#00f2fe" stop-opacity="0.85" />
                        <stop offset="40%" stop-color="#06b6d4" stop-opacity="0.65" />
                        <stop offset="100%" stop-color="#0284c7" stop-opacity="0.25" />
                    </linearGradient>
                    <linearGradient id="splashWaveGrad2" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.9" />
                        <stop offset="50%" stop-color="#0284c7" stop-opacity="0.75" />
                        <stop offset="100%" stop-color="#1e3a8a" stop-opacity="0.45" />
                    </linearGradient>
                    <linearGradient id="splashWaveGrad3" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#74a3cf" stop-opacity="1" />
                        <stop offset="100%" stop-color="#74a3cf" stop-opacity="1" />
                    </linearGradient>
                </defs>
                <path class="wave-layer wave-1" fill="url(#splashWaveGrad1)"
                    d="M0,50 C150,100 350,0 500,50 C650,100 850,0 1000,50 C1150,100 1350,0 1500,50 C1650,100 1850,0 2000,50 L2000,160 L0,160 Z">
                </path>
                <path class="wave-layer wave-2" fill="url(#splashWaveGrad2)"
                    d="M0,60 C200,110 300,10 500,60 C700,110 800,10 1000,60 C1200,110 1300,10 1500,60 C1700,110 1800,10 2000,60 L2000,160 L0,160 Z">
                </path>
                <path class="wave-layer wave-3" fill="#74a3cf"
                    d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,160 L0,160 Z">
                </path>
            </svg>
        </div>
    </div>
</div>

<script>
    (function () {
        // Immediate check: If Google OAuth return is detected, hide splash and abort immediately!
        const isOAuth = Boolean(
            window._isProcessingGoogleOAuth || 
            (window.location.hash && (window.location.hash.includes('access_token=') || window.location.hash.includes('id_token='))) ||
            (window.location.search && (window.location.search.includes('access_token=') || window.location.search.includes('id_token='))) ||
            (window.sessionStorage && sessionStorage.getItem('intan_elyu_google_oauth_pending') === 'true')
        );

        if (isOAuth) {
            const sp = document.getElementById('splash-main');
            if (sp) {
                sp.style.display = 'none';
                try { sp.remove(); } catch(e) {}
            }
            return;
        }

        // Immediate Offline Status Check
        const isOffline = !navigator.onLine || window.isAppOnline === false;
        const pill = document.getElementById('splash-offline-pill');
        if (isOffline && pill) {
            pill.style.display = 'inline-flex';
        }
        window.addEventListener('offline', () => { if (pill) pill.style.display = 'inline-flex'; });
        window.addEventListener('online', () => { if (pill) pill.style.display = 'none'; });

        // Simulate loading and check authentication
        setTimeout(() => {
            const rawHash = window.location.hash || '';
            const rawSearch = window.location.search || '';
            if (window._isProcessingGoogleOAuth || rawHash.includes('access_token=') || rawHash.includes('id_token=') || rawSearch.includes('access_token=') || rawSearch.includes('id_token=')) {
                // OAuth redirect in progress, let main.js handle it
                return;
            }

            const splashMain = document.getElementById('splash-main');
            const token = localStorage.getItem('intan_elyu_token');
            const cachedUser = localStorage.getItem('auth_user');
            const isOffline = !navigator.onLine || window.isAppOnline === false;

            if (isOffline) {
                // Device has NO internet connection:
                localStorage.setItem('intan_elyu_offline_mode', 'true');
                if (!token) {
                    localStorage.setItem('intan_elyu_token', 'offline_guest_token');
                    localStorage.setItem('intan_elyu_offline_guest', 'true');
                    if (!cachedUser) {
                        const defaultGuest = {
                            id: 'offline_guest',
                            user_id: 'offline_guest',
                            name: 'Guest Explorer',
                            full_name: 'Guest Explorer',
                            role: 'tourist',
                            tourist_number: 'OFFLINE',
                            points: 0,
                            xp: 0,
                            completed_activities: 0,
                            home_location: 'La Union',
                            is_offline_guest: true
                        };
                        localStorage.setItem('auth_user', JSON.stringify(defaultGuest));
                    }
                }
                sessionStorage.setItem('elyu_show_offline_bypass_notice', 'true');
                if (splashMain) splashMain.classList.add('exit');
                setTimeout(() => {
                    navigateTo('dashboard');
                }, 450);
            } else {
                // Device is ONLINE (Wi-Fi or Mobile Data connected):
                // Clean up any stale offline flags
                localStorage.removeItem('intan_elyu_offline_mode');
                localStorage.removeItem('intan_elyu_offline_guest');
                sessionStorage.removeItem('elyu_show_offline_bypass_notice');

                const isRealTourist = Boolean(
                    token && 
                    token !== 'guest_token' && 
                    token !== 'offline_guest_token' && 
                    !token.startsWith('guest_') && 
                    !token.startsWith('offline_')
                );

                if (isRealTourist) {
                    // Valid real logged-in tourist account
                    if (splashMain) splashMain.classList.add('exit');
                    setTimeout(() => {
                        navigateTo('dashboard');
                    }, 450);
                } else {
                    // Auth page is always the first one to show when opening the app!
                    localStorage.removeItem('intan_elyu_token');
                    localStorage.removeItem('intan_elyu_guest');
                    localStorage.removeItem('intan_elyu_offline_guest');
                    try { sessionStorage.removeItem('intan_elyu_guest_session_active'); } catch(e) {}
                    if (splashMain) splashMain.classList.add('exit');
                    setTimeout(() => {
                        navigateTo('auth');
                    }, 450);
                }
            }
        }, 2200);
    })();
</script>