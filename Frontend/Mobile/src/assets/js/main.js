/**
 * Intan Elyu - Mobile PHP Frontend Main Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // Global Auth Enforcement for Initial Direct Load
    const publicViews = ['splash', 'auth', 'reset-password'];
    if (!publicViews.includes(state.currentView) && !localStorage.getItem('intan_elyu_token')) {
        navigateTo('auth');
        return;
    }

    // Initialize history state for the initial load so the back button works correctly
    if (!window.history.state) {
        const url = new URL(window.location);
        url.searchParams.set('view', state.currentView);
        window.history.replaceState({ view: state.currentView }, '', url);
    }
    // Initialize dark theme if saved
    if (localStorage.getItem('intan_elyu_theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }

    // Check if we need to initialize any views on load
    initCurrentView();
});

// App State
const state = {
    currentView: new URLSearchParams(window.location.search).get('view') || 'splash',
    isNavigating: false
};

/**
 * Navigation Router Function (SPA feel)
 * @param {string} viewName - Name of the view to load
 * @param {boolean} addToHistory - Whether to push to browser history
 * @param {boolean} fade - Whether to apply the fade transition
 */
async function navigateTo(viewName, addToHistory = true, fade = true) {
    // Prevent overlapping navigations or navigating to the same view
    if (state.isNavigating) return;

    // Global Auth Enforcement: Ensure user is logged in
    const publicViews = ['splash', 'auth', 'about', 'terms', 'reset-password'];
    if (!publicViews.includes(viewName) && !localStorage.getItem('intan_elyu_token')) {
        viewName = 'auth';
    }

    // Redirect hidden pages (no longer redirecting merch view for finals)

    // If we're already on this view and it's not a back-button event, do nothing
    if (addToHistory && state.currentView === viewName) return;

    state.isNavigating = true;
    const mainContent = document.getElementById('main-content');

    // Emergency failsafe: auto-unlock after 3 seconds no matter what
    const failsafe = setTimeout(() => {
        if (state.isNavigating) {
            console.warn("Emergency unlock triggered!");
            state.isNavigating = false;
            if (mainContent) mainContent.classList.remove('view-transitioning');
        }
    }, 3000);

    if (!mainContent) {
        state.isNavigating = false;
        return;
    }

    // Animate out
    if (fade) {
        mainContent.classList.add('view-transitioning');
    }

    try {
        // Fetch new view via AJAX (with strict cache buster)
        const response = await fetch(`index.php?view=${viewName}&ajax=1&_t=${Date.now()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const html = await response.text();

        const updateContent = () => {
            try {
                mainContent.innerHTML = html;
                document.body.setAttribute('data-view', viewName);

                // Execute any scripts in the new view
                executeScripts(mainContent);

                // Toggle bottom nav visibility
                const bottomNav = document.getElementById('bottom-navigation');
                const noNavViews = ['splash', 'auth', 'about', 'terms', 'edit_profile', 'help', 'trip_map', 'saved_trips', 'trending', 'reset-password'];
                if (bottomNav) {
                    bottomNav.classList.toggle('nav-hidden', noNavViews.includes(viewName));
                }

                // Animate in
                if (fade) {
                    mainContent.classList.remove('view-transitioning');
                }

                // Update URL
                if (addToHistory) {
                    const url = new URL(window.location);
                    url.searchParams.set('view', viewName);
                    window.history.pushState({ view: viewName }, '', url);
                }

                state.currentView = viewName;
                initCurrentView();
            } catch (err) {
                console.error("Error during view initialization:", err);
            } finally {
                clearTimeout(failsafe);
                state.isNavigating = false;
            }
        };

        // If fading, wait for the fade out to finish (200ms). Otherwise update instantly.
        if (fade) {
            setTimeout(updateContent, 200);
        } else {
            updateContent();
        }

    } catch (error) {
        clearTimeout(failsafe);
        console.error('Navigation error:', error);
        showToast('Failed to load view');
        if (fade) mainContent.classList.remove('view-transitioning');
        state.isNavigating = false;
    }
}

// Handle Browser Back Button
window.addEventListener('popstate', (e) => {
    if (e.state && e.state.view) {
        navigateTo(e.state.view, false);
    } else {
        // Fallback if state is missing but URL has a view param
        const view = new URLSearchParams(window.location.search).get('view') || 'splash';
        navigateTo(view, false);
    }
});

/**
 * Toast Notification System
 */
function showToast(message, duration = 3000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toast-slide-down 0.3s reverse forwards';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Execute scripts injected via innerHTML
 */
function executeScripts(container) {
    const scripts = container.querySelectorAll('script');
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

/**
 * Initialize logic specific to the current view
 */
function initCurrentView() {
    // Update Magic Nav
    updateMagicNav(state.currentView);

    // Dispatch a custom event so individual views can listen for load
    document.dispatchEvent(new CustomEvent('viewLoaded', { detail: { view: state.currentView } }));
}

/**
 * Handle Magic Navigation Bar Indicator and Visibility
 */
function updateMagicNav(viewName) {
    const nav = document.getElementById('magic-nav');
    if (!nav) return;

    const items = nav.querySelectorAll('.magic-nav-item');
    const indicator = document.getElementById('magic-indicator');
    const indicatorCircle = document.querySelector('.magic-indicator-circle');

    const isMainNav = Array.from(items).some(item => item.dataset.view === viewName);
    if (!isMainNav) return;

    let activeColor = '#38bdf8';
    let activeIndex = 0;

    items.forEach(item => {
        if (item.dataset.view === viewName) {
            item.classList.add('active');
            activeColor = item.dataset.color || activeColor;
            activeIndex = parseInt(item.dataset.index);
        } else {
            item.classList.remove('active');
        }
    });

    // Update Magic Indicator Position
    // Center is at 10%, 30%, 50%, 70%, 90%
    const percent = 10 + (activeIndex * 20);
    if (indicator) {
        indicator.style.left = `${percent}%`;
        indicator.style.background = `radial-gradient(circle at 38% 32%, ${activeColor}ed, ${activeColor}ba)`;
        indicator.style.boxShadow = `0 4px 22px ${activeColor}90, inset 0 2px 4px rgba(255,255,255,0.3)`;
    }

    // Update SVG Path Notch
    const notchCX = (percent / 100) * 448; // ViewBox width is 448
    const { fullPath, topEdge } = buildNavPath(notchCX);

    const pathBase = document.getElementById('magic-nav-path-base');
    const pathTint = document.getElementById('magic-nav-path-tint');
    const pathEdge = document.getElementById('magic-nav-path-edge');

    if (pathBase) pathBase.setAttribute('d', fullPath);
    if (pathTint) {
        pathTint.setAttribute('d', fullPath);
        pathTint.setAttribute('fill', activeColor);
    }
    if (pathEdge) pathEdge.setAttribute('d', topEdge);
}

function buildNavPath(cx) {
    const W = 448;
    const H = 66;
    const w = 64;
    const d = 48;
    const c1x = 36;
    const c2x = 44;

    const topEdge = [
        `M 0 0`,
        `H ${cx - w}`,
        `C ${cx - c1x} 0, ${cx - c2x} ${d}, ${cx} ${d}`,
        `C ${cx + c2x} ${d}, ${cx + c1x} 0, ${cx + w} 0`,
        `H ${W}`,
    ].join(" ");

    const fullPath = [topEdge, `V ${H}`, `H 0`, `Z`].join(" ");

    return { fullPath, topEdge };
}



/**
 * Handle Logout
 */
async function handleLogout(e) {
    if (e) e.preventDefault();
    showToast('Logging out...', 1000);

    try {
        const token = localStorage.getItem('intan_elyu_token');
        if (token) {
            await fetch('/api/auth/logout', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',

                    'Authorization': 'Bearer ' + token
                }
            });
        }
    } catch (err) {
        console.warn('Backend logout failed', err);
    }

    localStorage.removeItem('intan_elyu_token');
    localStorage.removeItem('auth_user');

    setTimeout(() => {
        // Hard reset the URL to clear Capacitor saved state and show splash
        window.location.replace('index.php?view=splash');
    }, 1000);
}

/**
 * Pull to Refresh Logic
 */
let startY = 0;
let currentY = 0;
let isPulling = false;
let isRefreshing = false;


// Global Dark Mode Controller
window.toggleDarkMode = function (isDark) {
    if (isDark) {
        document.body.classList.add('dark-theme');
        localStorage.setItem('intan_elyu_theme', 'dark');
    } else {
        document.body.classList.remove('dark-theme');
        localStorage.setItem('intan_elyu_theme', 'light');
    }
};

// Initialize the dark mode toggle switch every time a view loads
document.addEventListener('viewLoaded', (e) => {
    // Sync CSS wave animations to global time so they don't jump horizontally on view transition
    const timePassed = performance.now() / 1000;
    const waves = document.querySelectorAll('.wave-layer');
    waves.forEach(wave => {
        if (!wave.dataset.synced) {
            wave.style.animationDelay = `-${timePassed}s`;
            wave.dataset.synced = 'true';
        }
    });

    if (e.detail.view === 'settings') {
        const toggle = document.getElementById('dark-mode-toggle');
        if (toggle) {
            // Set initial state
            toggle.checked = document.body.classList.contains('dark-theme');

            // Attach event listener natively to bypass inline execution issues on webviews
            toggle.addEventListener('change', function () {
                window.toggleDarkMode(this.checked);
            });
        }
    }
});

// --- Push Notifications & Location Services ---

window.intanElyuLocationWatchId = null;

// Initialize Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        // Use relative path for Service Worker to support both / and /mobile/ base URLs
        navigator.serviceWorker.register('sw.js').then(function (registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function (err) {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

// --- Custom In-App Notifications for WebViews ---

window.showInAppNotification = function (title, message, iconUrl = '') {
    let modal = document.getElementById('notif-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'notif-modal';
        modal.style.cssText = 'position:fixed; inset:0; z-index:999999; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); opacity:0; transition:opacity 0.3s;';
        modal.innerHTML = `
            <div style="background:rgba(28,28,30,0.95); backdrop-filter:blur(20px); border-radius:20px; padding:28px 24px 20px; width:300px; max-width:85vw; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.08); transform:scale(0.9); transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1);">
                <div id="notif-modal-icon" style="width:56px; height:56px; border-radius:16px; background:var(--primary-color); display:flex; align-items:center; justify-content:center; color:white; font-size:26px; margin:0 auto 14px;"><i class="fa-solid fa-bell"></i></div>
                <h3 id="notif-modal-title" style="margin:0 0 8px; color:white; font-size:17px; font-weight:700;">${title}</h3>
                <p id="notif-modal-msg" style="margin:0 0 20px; color:rgba(255,255,255,0.6); font-size:14px; line-height:1.4;">${message}</p>
                <button id="notif-modal-btn" style="background:var(--primary-color); color:white; border:none; padding:12px 24px; border-radius:100px; font-size:14px; font-weight:700; cursor:pointer; width:100%;">Got it</button>
            </div>
        `;
        document.body.appendChild(modal);

        const inner = modal.querySelector('div > div');
        modal.addEventListener('click', (e) => {
            if (e.target === modal) window.closeNotifModal();
        });
        modal.querySelector('#notif-modal-btn').addEventListener('click', window.closeNotifModal);
    }

    const iconDiv = modal.querySelector('#notif-modal-icon');
    if (iconUrl) {
        iconDiv.innerHTML = `<img src="${iconUrl}" style="width:56px; height:56px; border-radius:16px; object-fit:cover;">`;
    } else {
        iconDiv.innerHTML = '<i class="fa-solid fa-bell"></i>';
    }
    modal.querySelector('#notif-modal-title').textContent = title;
    modal.querySelector('#notif-modal-msg').textContent = message;

    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        modal.querySelector('div > div').style.transform = 'scale(1)';
    });

    try {
        const audio = new Audio('assets/audio/tuturu.mp3');
        audio.play().catch(e => { });
    } catch (e) { }
};

window.closeNotifModal = function () {
    const modal = document.getElementById('notif-modal');
    if (!modal) return;
    modal.style.opacity = '0';
    modal.querySelector('div > div').style.transform = 'scale(0.9)';
    setTimeout(() => { modal.style.display = 'none'; }, 300);
};

window.togglePushNotifications = async function (enabled) {
    localStorage.setItem('intan_elyu_push_enabled', enabled);
    if (enabled) {
        showToast("In-App Notifications enabled!");
        window.showInAppNotification("Intan Elyu", "Notifications are now active! You will be alerted when near a destination.");
    } else {
        showToast("In-App Notifications disabled");
    }
};

window.toggleLocationServices = function (enabled) {
    localStorage.setItem('intan_elyu_loc_enabled', enabled);
    if (enabled) {
        showToast("Location Services enabled!");
        window.startLocationWatch();
    } else {
        showToast("Location Services disabled");
        if (window.intanElyuLocationWatchId) {
            navigator.geolocation.clearWatch(window.intanElyuLocationWatchId);
            window.intanElyuLocationWatchId = null;
        }
    }
};

window.startLocationWatch = function () {
    // Force enable location tracking per user request (dynamic global tracking)
    localStorage.setItem('intan_elyu_loc_enabled', 'true');

    if (!navigator.geolocation) return;

    if (window.intanElyuLocationWatchId) {
        navigator.geolocation.clearWatch(window.intanElyuLocationWatchId);
    }

    let lastAlertedItems = JSON.parse(localStorage.getItem('intan_elyu_alerted_items') || '{}');
    let lastGpsProcessTime = 0;

    window.intanElyuLocationWatchId = navigator.geolocation.watchPosition(
        (position) => {
            // Throttle GPS processing to once every 3 seconds to prevent massive UI lagginess
            const now = Date.now();
            if (now - lastGpsProcessTime < 3000) return;
            lastGpsProcessTime = now;

            const currentLat = position.coords.latitude;
            const currentLng = position.coords.longitude;
            const accuracy = position.coords.accuracy;
            const altitude = position.coords.altitude;
            const speed = position.coords.speed;

            // Globally store for all maps (itinerary, trip map, etc.)
            window.currentGPSLat = currentLat;
            window.currentGPSLng = currentLng;
            window.currentGPSAccuracy = accuracy;
            window.currentGPSAltitude = altitude;
            window.currentGPSSpeed = speed;

            // Broadcast dynamic update inside requestAnimationFrame to prevent layout thrashing
            requestAnimationFrame(() => {
                document.dispatchEvent(new CustomEvent('gpsUpdated', { detail: { lat: currentLat, lng: currentLng, accuracy, altitude, speed } }));
            });

            // Check active itineraries
            const savedTrips = window.savedTripsData || [];

            savedTrips.forEach(trip => {
                // We only care about active/ongoing trips
                if (trip.status === 'active' && trip.items) {
                    trip.items.forEach(item => {
                        if (item.is_visited) return;

                        const dest = item.destination;
                        if (!dest || !dest.lat || !dest.lng) return;

                        // Calculate distance
                        const dist = calculateDistance(currentLat, currentLng, parseFloat(dest.lat), parseFloat(dest.lng));

                        // If within 500 meters and haven't alerted yet
                        if (dist <= 500 && !lastAlertedItems[item.id]) {
                            // Fire Notification
                            if (localStorage.getItem('intan_elyu_push_enabled') === 'true') {
                                window.showInAppNotification(
                                    "Destination Nearby!",
                                    `You are near ${dest.name}! Open the app to check in and earn XP.`
                                );
                            }

                            // Save state so we don't spam
                            lastAlertedItems[item.id] = true;
                            localStorage.setItem('intan_elyu_alerted_items', JSON.stringify(lastAlertedItems));
                        }
                    });
                }
            });
        },
        (error) => {
            // Suppress harmless timeout errors (code 3) from polluting the console, 
            // especially on desktop devices that take longer to get a location fix.
            if (error.code !== 3) {
                console.warn("Global Location watch error:", error);
            }
        },
        { enableHighAccuracy: false, maximumAge: 10000, timeout: 30000 }
    );
};

// Fast-track location: cached GPS -> IP geolocation -> GPS fallback
window.fastLocation = function () {
    if (window.currentGPSLat && window.currentGPSLng) {
        return Promise.resolve({ lat: window.currentGPSLat, lng: window.currentGPSLng });
    }
    return Promise.resolve(null);
};

// Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radius of the earth in m
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c; // Distance in m
}

// Start watching on load automatically
document.addEventListener('DOMContentLoaded', () => {
    window.startLocationWatch();
});

// View Itinerary from map's "Added to Itinerary!" confirmation modal
window.viewItinerary = function () {
    var modal = document.getElementById('itin-add-confirm');
    if (modal) {
        modal.style.opacity = '0';
        modal.style.pointerEvents = 'none';
    }
    navigateTo('itinerary');
};

/**
 * Global image error handler — replaces broken images with a placeholder.
 * Usage: onerror="return window.handleImageError.call(this, event)"
 */
window.handleImageError = function (e) {
    if (!e) e = window.event;
    var img = e ? e.target : this;
    if (!img) return true;
    img.onerror = null;
    var placeholder = window.placeholderImage || '';
    if (placeholder && img.src !== placeholder) {
        img.src = placeholder;
        img.style.objectFit = 'contain';
        img.style.background = '#1e293b';
    }
    return true;
};

// Delegated listener catches image errors across all dynamically loaded views
document.addEventListener('error', function (e) {
    var target = e.target;
    if (target && target.tagName === 'IMG' && target.src) {
        if (target.src.indexOf('placeholderImage') !== -1 || target.src.indexOf('data:image/svg') !== -1) return;
        target.onerror = null;
        var placeholder = window.placeholderImage || '';
        if (placeholder && target.src !== placeholder) {
            target.src = placeholder;
            target.style.objectFit = 'contain';
            target.style.background = '#1e293b';
        }
    }
}, true);

/**
 * Shared image resolution for all views.
 * @param {Object} dest - Destination object with name, municipality, image, photo_url
 * @param {number} [width=600] - Desired image width for placeholders
 * @returns {string} Resolved image URL
 */
window.getDestImage = function (dest, width) {
    if (!width) width = 600;
    var backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';

    // Phase 1: Try local filesystem images (AVAILABLE_MUNI_IMAGES)
    if (window.AVAILABLE_MUNI_IMAGES && dest && dest.name) {
        var munisToCheck = dest.municipality
            ? [dest.municipality.toUpperCase()]
            : Object.keys(window.AVAILABLE_MUNI_IMAGES);
        var dNorm = dest.name.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').trim();
        var dWords = dNorm.split(/\s+/).filter(function (w) { return w.length > 2; });
        var bestMatch = null, bestScore = 0, bestMuni = null;

        for (var mi = 0; mi < munisToCheck.length; mi++) {
            var muni = munisToCheck[mi];
            var images = window.AVAILABLE_MUNI_IMAGES[muni];
            if (!images || !images.length) continue;
            for (var ii = 0; ii < images.length; ii++) {
                var img = images[ii];
                var iNorm = img.replace(/\.(jpg|jpeg|png|webp|gif)$/i, '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ').trim();
                var dStr = dNorm.replace(/\s+/g, '');
                var iStr = iNorm.replace(/\s+/g, '').replace(/[0-9]+$/, '');
                if (dStr === iStr) {
                    return encodeURI('/api/image/municipalities/' + muni + '/' + img);
                }
                var score = 0;
                if (dStr.indexOf(iStr) !== -1 || iStr.indexOf(dStr) !== -1) score += 100;
                var iWords = iNorm.split(/\s+/).filter(function (w) { return w.length > 2; });
                var common = 0;
                for (var wi = 0; wi < dWords.length; wi++) {
                    var w = dWords[wi];
                    if (iWords.indexOf(w) !== -1) {
                        score += w === muni.toLowerCase() ? 1 : 10;
                        common++;
                    }
                }
                if (common > 0) score += (common / Math.max(dWords.length, iWords.length)) * 5;
                if (score > bestScore && score >= 10) {
                    bestScore = score;
                    bestMatch = img;
                    bestMuni = muni;
                } else if (score === bestScore && score >= 10) {
                    if (img.indexOf('1') !== -1 || img.toLowerCase().indexOf('one') !== -1) {
                        bestMatch = img;
                        bestMuni = muni;
                    }
                }
            }
        }
        if (bestMatch) {
            return encodeURI('/api/image/municipalities/' + bestMuni + '/' + bestMatch);
        }
    }

    // Phase 2: Use image from API response
    var url = dest ? (dest.image || dest.photo_url) : null;
    if (url) {
        if (url.indexOf('serve-image.php?file=') !== -1) {
            url = '/api/image/' + url.split('serve-image.php?file=')[1];
        } else if (url.indexOf('http') !== 0 && url.indexOf('/') !== 0) {
            url = '/api/image/' + url;
        }
        // Strip backend localhost hosts so the request goes through the frontend proxy
        if (url.indexOf('http') === 0) {
            var backendHosts = ['localhost:8000', '127.0.0.1:8000', 'localhost:3000'];
            try {
                var parsed = new URL(url);
                var isBackendHost = false;
                for (var hi = 0; hi < backendHosts.length; hi++) {
                    if (parsed.host === backendHosts[hi]) {
                        isBackendHost = true;
                        break;
                    }
                }
                if (isBackendHost) {
                    url = parsed.pathname + parsed.search;
                } else {
                    return url;
                }
            } catch (e) { return url; }
        }
        if (url.indexOf('/') === 0) return backendUrl + url;
        return backendUrl + '/' + url;
    }

    // Phase 3: Placeholder
    if (dest && dest.name) {
        return 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=' + width;
    }
    return window.placeholderImage || '';
};

/**
 * Stale-While-Revalidate Caching fetch helper
 * @param {string} cacheKey - The key to use in localStorage
 * @param {Function} fetchFn - Function returning a Promise that fetches the data
 * @param {Function} callback - Callback function(data, isCached) called with the data
 * @param {boolean} [forceRefresh=false] - If true, ignores cache age (but still does SWR if cache exists)
 * @param {number} [ttl=60000] - Time in ms before cache is considered stale (default 1 minute)
 */
window.useCache = async function (cacheKey, fetchFn, callback, forceRefresh = false, ttl = 60000) {
    const cached = localStorage.getItem(cacheKey);
    let cachedData = null;
    let isExpired = true;

    if (cached) {
        try {
            cachedData = JSON.parse(cached);
            if (cachedData && cachedData.hasOwnProperty('data')) {
                // Call callback with cached data immediately
                callback(cachedData.data, true);
                const age = Date.now() - (cachedData.timestamp || 0);
                isExpired = age > ttl;
            }
        } catch (e) {
            console.warn("Error parsing cache for " + cacheKey, e);
        }
    }

    // Fetch from network if expired, forceRefresh is true, or no cache exists
    if (!cachedData || isExpired || forceRefresh) {
        try {
            const data = await fetchFn();
            if (data !== undefined) {
                localStorage.setItem(cacheKey, JSON.stringify({
                    data: data,
                    timestamp: Date.now()
                }));
                callback(data, false);
            }
        } catch (err) {
            console.error("Fetch error for " + cacheKey, err);
            // If no cache exists, report the error via callback (passing null)
            if (!cachedData) {
                callback(null, false);
            }
        }
    }
};

// Pre-cache destination images in background
setTimeout(function () {
    fetch('/cache-all-images').catch(function () { });
}, 2000);
