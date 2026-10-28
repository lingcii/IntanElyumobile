<!-- Four-scene mobile onboarding shown before Login. -->
<div class="splash-container elyu-onboarding" id="splash-main" aria-label="Welcome to Elyu">
    <section class="onboarding-slide onboarding-slide--brand is-active" data-onboarding-slide="0">
        <div class="brand-sky" aria-hidden="true"></div>
        <div class="onboarding-brand-lockup">
            <img src="assets/img/logo.png" alt="Elyu">
            <strong>Elyu</strong>
            <span>DISCOVER LA UNION</span>
        </div>
        <div class="brand-waves" aria-hidden="true"><span></span><span></span><span></span></div>
    </section>

    <section class="onboarding-slide onboarding-slide--photo" data-onboarding-slide="1">
        <img class="onboarding-backdrop" src="assets/img/onboarding/elyu-coast-morning.jpg" alt="La Union mountain coastline">
        <div class="onboarding-photo-shade"></div>
        <div class="onboarding-mini-brand">
            <img src="assets/img/logo.png" alt=""><strong>Elyu</strong><span>DISCOVER LA UNION</span>
        </div>
        <p class="onboarding-explore">Explore<br><strong>Beautiful Places</strong></p>
    </section>

    <section class="onboarding-slide onboarding-slide--photo" data-onboarding-slide="2">
        <img class="onboarding-backdrop" src="assets/img/onboarding/elyu-coast-island.jpg" alt="Rock island on the La Union coast">
        <div class="onboarding-photo-shade onboarding-photo-shade--soft"></div>
        <div class="onboarding-copy onboarding-copy--center">
            <h1>Create<br><strong>Memorable Trips</strong></h1>
            <p>Save your favorite places<br>and plan your journey.</p>
        </div>
    </section>

    <section class="onboarding-slide onboarding-slide--photo onboarding-slide--discover" data-onboarding-slide="3">
        <img class="onboarding-backdrop" src="assets/img/onboarding/elyu-coast-blue-hour.jpg" alt="La Union coastline at blue hour">
        <div class="onboarding-photo-shade onboarding-photo-shade--deep"></div>
        <div class="onboarding-copy onboarding-copy--discover">
            <h1>Discover<br><strong>La Union</strong></h1>
            <p>Beaches <b>•</b> Nature <b>•</b> Culture<br>and more</p>
        </div>
        <svg class="onboarding-route" viewBox="0 0 260 360" role="img" aria-label="Animated destination route">
            <path d="M206 34 C154 78 184 122 104 152 S83 231 42 277 S91 322 150 337" />
            <g class="route-pin route-pin--1"><circle cx="206" cy="34" r="8"/><circle cx="206" cy="34" r="3"/></g>
            <g class="route-pin route-pin--2"><circle cx="104" cy="152" r="8"/><circle cx="104" cy="152" r="3"/></g>
            <g class="route-pin route-pin--3"><circle cx="42" cy="277" r="8"/><circle cx="42" cy="277" r="3"/></g>
            <g class="route-pin route-pin--4"><circle cx="150" cy="337" r="8"/><circle cx="150" cy="337" r="3"/></g>
        </svg>
    </section>

    <div class="onboarding-loader" id="onboarding-loader" role="progressbar" aria-label="Loading Elyu" aria-valuemin="1" aria-valuemax="4" aria-valuenow="1">
        <span class="is-loading"></span><span></span><span></span><span></span>
    </div>

    <div id="splash-offline-pill" class="splash-offline-pill" hidden>
        <i class="fa-solid fa-cloud-slash" aria-hidden="true"></i>
        <span>Offline Mode &bull; Loading saved guides</span>
    </div>
</div>

<script>
    (function initElyuOnboarding() {
        if (typeof window._elyuSplashCleanup === 'function') window._elyuSplashCleanup();
        const splash = document.getElementById('splash-main');
        if (!splash) return;

        const hash = window.location.hash || '';
        const search = window.location.search || '';
        const oauthPending = Boolean(
            window._isProcessingGoogleOAuth || window._googleOAuthActiveProcessing || window._handledDirectToken ||
            /(?:access_token|id_token|token)=/.test(hash + search) ||
            (window.sessionStorage && sessionStorage.getItem('intan_elyu_google_oauth_pending') === 'true')
        );
        if (oauthPending) { splash.remove(); return; }

        const isDevHost = (hostname) => hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1' ||
            /^192\.168\.\d+\.\d+$/.test(hostname) || /^10\.\d+\.\d+\.\d+$/.test(hostname) ||
            /^172\.(1[6-9]|2\d|3[01])\.\d+\.\d+$/.test(hostname);
        if (!isDevHost(window.location.hostname) && !window.Capacitor && !navigator.userAgent.includes('IntanElyuAPK') && !search.includes('app=')) {
            window.location.replace('index.php?view=download');
            return;
        }

        const timers = [];
        let navigated = false;
        const slides = Array.from(splash.querySelectorAll('[data-onboarding-slide]'));
        const loader = document.getElementById('onboarding-loader');
        const loaderParts = loader ? Array.from(loader.children) : [];
        const offlinePill = document.getElementById('splash-offline-pill');
        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const slideDuration = reducedMotion ? 650 : 1650;

        function setOfflineStatus() {
            const offline = typeof navigator.onLine === 'boolean' ? !navigator.onLine : window.isAppOnline === false;
            if (offlinePill) offlinePill.hidden = !offline;
        }
        window.addEventListener('online', setOfflineStatus);
        window.addEventListener('offline', setOfflineStatus);
        setOfflineStatus();

        function cleanup() {
            timers.forEach(clearTimeout);
            window.removeEventListener('online', setOfflineStatus);
            window.removeEventListener('offline', setOfflineStatus);
            if (window._elyuSplashCleanup === cleanup) window._elyuSplashCleanup = null;
        }
        window._elyuSplashCleanup = cleanup;

        function showSlide(index) {
            slides.forEach((slide, position) => {
                slide.classList.toggle('is-active', position === index);
                slide.classList.toggle('is-before', position < index);
            });
            loaderParts.forEach((part, position) => {
                part.classList.toggle('is-complete', position < index);
                part.classList.toggle('is-loading', position === index);
            });
            if (loader) loader.setAttribute('aria-valuenow', String(index + 1));
        }

        function navigateAfterSplash(destination) {
            if (navigated) return;
            navigated = true;
            splash.classList.add('exit');
            timers.push(setTimeout(() => { cleanup(); navigateTo(destination); }, reducedMotion ? 100 : 420));
        }

        const rawToken = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
        const rawUser = localStorage.getItem('auth_user') || localStorage.getItem('intan_elyu_user');
        let user = null;
        try { user = rawUser ? JSON.parse(rawUser) : null; } catch (error) { user = null; }
        const realTourist = Boolean(
            (rawToken && rawToken !== 'guest_token' && rawToken !== 'offline_guest_token' && !rawToken.startsWith('guest_') && !rawToken.startsWith('offline_')) ||
            (user && user.id && user.id !== 'offline_guest' && !user.is_offline_guest && user.role !== 'guest')
        );

        if (realTourist) {
            timers.push(setTimeout(() => navigateAfterSplash('dashboard'), 900));
            return;
        }

        ['intan_elyu_token', 'Intan_Elyu_Token', 'api_token', 'auth_user', 'auth_user_backup', 'intan_elyu_user', 'intan_elyu_guest', 'intan_elyu_offline_guest']
            .forEach((key) => localStorage.removeItem(key));
        try { sessionStorage.removeItem('intan_elyu_guest_session_active'); } catch (error) {}

        slides.forEach((slide, index) => {
            if (index > 0) timers.push(setTimeout(() => showSlide(index), slideDuration * index));
        });
        timers.push(setTimeout(() => navigateAfterSplash('auth'), slideDuration * slides.length));
    })();
</script>
