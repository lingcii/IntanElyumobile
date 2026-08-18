/**
 * Intan Elyu - Mobile PHP Frontend Main Logic
 */

window.safeJsonParse = function (str, fallback = {}) {
    if (!str || str === 'undefined' || str === 'null' || str === 'NaN') return fallback;
    try {
        return typeof str === 'object' ? str : JSON.parse(str);
    } catch (e) {
        return fallback;
    }
};

/**
 * Asynchronous IndexedDB + localStorage Hybrid Storage Engine
 */
window.AppStorage = {
    _dbPromise: null,

    _getDB: function () {
        if (!this._dbPromise) {
            this._dbPromise = new Promise((resolve) => {
                if (!window.indexedDB) {
                    resolve(null);
                    return;
                }
                const request = window.indexedDB.open('intan_elyu_app_storage', 1);
                request.onupgradeneeded = function (e) {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains('store')) {
                        db.createObjectStore('store');
                    }
                };
                request.onsuccess = function (e) { resolve(e.target.result); };
                request.onerror = function () { resolve(null); };
            });
        }
        return this._dbPromise;
    },

    getItem: async function (key, fallback = null) {
        try {
            const db = await this._getDB();
            if (db) {
                const val = await new Promise((resolve) => {
                    const tx = db.transaction('store', 'readonly');
                    const req = tx.objectStore('store').get(key);
                    req.onsuccess = () => resolve(req.result);
                    req.onerror = () => resolve(undefined);
                });
                if (val !== undefined && val !== null) return val;
            }
        } catch (e) { }

        const raw = localStorage.getItem(key);
        return raw !== null ? raw : fallback;
    },

    setItem: async function (key, val) {
        try {
            const strVal = typeof val === 'string' ? val : JSON.stringify(val);
            localStorage.setItem(key, strVal);
        } catch (e) { }

        try {
            const db = await this._getDB();
            if (db) {
                const tx = db.transaction('store', 'readwrite');
                tx.objectStore('store').put(val, key);
            }
        } catch (e) { }
    },

    removeItem: async function (key) {
        try { localStorage.removeItem(key); } catch (e) { }
        try {
            const db = await this._getDB();
            if (db) {
                const tx = db.transaction('store', 'readwrite');
                tx.objectStore('store').delete(key);
            }
        } catch (e) { }
    }
};

window.setTxt = function (id, val) {
    const el = (typeof id === 'string') ? document.getElementById(id) : id;
    if (el) el.textContent = (val !== undefined && val !== null) ? val : '';
};

window.setHtml = function (id, html) {
    const el = (typeof id === 'string') ? document.getElementById(id) : id;
    if (el) el.innerHTML = (html !== undefined && html !== null) ? html : '';
};

window.getBackendUrl = function () {
    var url = window.backendUrl || window.BACKEND_URL;
    if (url) {
        return url.replace(/\/+$/, '');
    }
    if (typeof window !== 'undefined' && window.location) {
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            if (window.location.port === '3000') return 'http://localhost:8000';
            if (window.location.pathname.includes('/Intan-Elyu-Tourism-Management-System/')) {
                return window.location.protocol + '//' + window.location.host + '/Intan-Elyu-Tourism-Management-System/backend/public';
            }
        }
        return window.location.origin.replace(/\/+$/, '');
    }
    return 'https://app.intan-elyu.online';
};

window.getFullImageUrl = function (url) {
    if (!url) return window.placeholderImage || '';
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('data:')) {
        return url;
    }
    const base = window.getBackendUrl();
    const clean = url.replace(/^\/+/, '');
    if (clean.startsWith('api/image/') || clean.startsWith('api/serve')) return base + '/' + clean;
    return base + '/api/image/' + clean;
};

document.addEventListener('DOMContentLoaded', () => {
    // Auto-invalidate stale caches from previous builds
    const CACHE_VER = 'v1.0.5_r2_regex';
    if (localStorage.getItem('intan_elyu_cache_ver') !== CACHE_VER) {
        Object.keys(localStorage).forEach(k => {
            if (k.startsWith('dashboard_') || k.startsWith('trending_') || k.startsWith('map_') || k.startsWith('spots_') || k.startsWith('destinations_') || k.includes('cache')) {
                localStorage.removeItem(k);
            }
        });
        localStorage.setItem('intan_elyu_cache_ver', CACHE_VER);
    }

    // Global Auth Enforcement for Initial Direct Load
    const publicViews = ['splash', 'auth', 'download', 'reset-password'];
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

// Extract initial view name from query param (?view=...) or URL path (/download)
function getInitialViewName() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('view')) return params.get('view');
    const pathSegs = window.location.pathname.split('/').filter(Boolean);
    const lastSeg = pathSegs.length > 0 ? pathSegs[pathSegs.length - 1] : '';
    if (lastSeg && lastSeg !== 'index.php' && !lastSeg.includes('.')) {
        return lastSeg;
    }
    return 'splash';
}

// App State
const state = {
    currentView: getInitialViewName(),
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
    const publicViews = ['splash', 'auth', 'download', 'about', 'terms', 'reset-password', 'user_manual'];
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
                const noNavViews = ['splash', 'auth', 'about', 'terms', 'edit_profile', 'help', 'trip_map', 'saved_trips', 'saved_places', 'trending', 'reset-password', 'puzzles', 'discount', 'settings', 'user_manual'];
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
window.showToast = function showToast(message, type = 'info', duration = 3200) {
    if (typeof type === 'number') {
        duration = type;
        type = 'info';
    }

    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    // Determine icon and color variant
    let iconHTML = '<i class="fa-solid fa-circle-info" style="color:#38bdf8; font-size:16px;"></i>';
    let borderColor = 'rgba(56, 189, 248, 0.35)';

    const lowerMsg = String(message).toLowerCase();
    if (type === 'success' || lowerMsg.includes('success') || lowerMsg.includes('deleted') || lowerMsg.includes('checked in') || lowerMsg.includes('completed') || lowerMsg.includes('added') || lowerMsg.includes('saved')) {
        iconHTML = '<i class="fa-solid fa-circle-check" style="color:#34c759; font-size:16px;"></i>';
        borderColor = 'rgba(52, 199, 89, 0.4)';
    } else if (type === 'error' || lowerMsg.includes('error') || lowerMsg.includes('failed') || lowerMsg.includes('invalid') || lowerMsg.includes('inaccessible') || lowerMsg.includes('timeout')) {
        iconHTML = '<i class="fa-solid fa-circle-exclamation" style="color:#ef4444; font-size:16px;"></i>';
        borderColor = 'rgba(239, 68, 68, 0.4)';
    } else if (type === 'warning' || lowerMsg.includes('warning') || lowerMsg.includes('select') || lowerMsg.includes('capture')) {
        iconHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b; font-size:16px;"></i>';
        borderColor = 'rgba(245, 158, 11, 0.4)';
    }

    const toast = document.createElement('div');
    toast.className = 'toast-card';
    toast.style.borderColor = borderColor;
    toast.innerHTML = `<div style="display:flex; align-items:center; gap:10px;">${iconHTML}<span style="font-size:13px; font-weight:700; color:#ffffff; line-height:1.3;">${message}</span></div>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toastOut 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards';
        setTimeout(() => {
            toast.remove();
            if (container && container.children.length === 0) {
                container.remove();
            }
        }, 280);
    }, duration);
};
var showToast = window.showToast;

/**
 * Execute scripts injected via innerHTML
 */
function executeScripts(container) {
    const scripts = container.querySelectorAll('script');
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
        if (oldScript.parentNode) {
            oldScript.parentNode.replaceChild(newScript, oldScript);
        }
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
            await fetch(window.backendUrl + '/api/auth/logout', {
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
    if (!navigator.geolocation) return;
    if (localStorage.getItem('intan_elyu_loc_enabled') === 'false') {
        if (window.intanElyuLocationWatchId) {
            navigator.geolocation.clearWatch(window.intanElyuLocationWatchId);
            window.intanElyuLocationWatchId = null;
        }
        return;
    }

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
            window.myLat = currentLat;
            window.myLng = currentLng;
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
                            if (localStorage.getItem('intan_elyu_push_enabled') !== 'false') {
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
            // Suppress harmless timeout errors (code 3) and permission denied errors (code 1)
            // from polluting the console when location access is denied or delayed.
            if (error.code !== 3 && error.code !== 1) {
                console.warn("Global Location watch error:", error);
            }
        },
        { enableHighAccuracy: false, maximumAge: 10000, timeout: 30000 }
    );
};

// Fast-track location: cached GPS -> IP geolocation -> GPS fallback
window.fastLocation = function () {
    const lat = window.currentGPSLat || window.myLat;
    const lng = window.currentGPSLng || window.myLng;
    if (lat && lng) {
        return Promise.resolve({ lat, lng });
    }
    return window.resolveUserLocation();
};

// Multi-tier user location resolver (GPS -> IP Geolocation -> La Union Fallback)
window.resolveUserLocation = async function (forceIP = false) {
    const cachedLat = window.currentGPSLat || window.myLat;
    const cachedLng = window.currentGPSLng || window.myLng;
    if (!forceIP && cachedLat && cachedLng) {
        return { lat: cachedLat, lng: cachedLng, source: 'cached' };
    }

    // Tier 1: Try HTML5 Geolocation
    if (!forceIP && navigator.geolocation && localStorage.getItem('intan_elyu_loc_enabled') !== 'false') {
        try {
            const pos = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 8000,
                    maximumAge: 30000
                });
            });
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            window.currentGPSLat = lat;
            window.currentGPSLng = lng;
            window.myLat = lat;
            window.myLng = lng;
            document.dispatchEvent(new CustomEvent('gpsUpdated', {
                detail: { lat, lng, accuracy: pos.coords.accuracy, source: 'gps' }
            }));
            return { lat, lng, source: 'gps' };
        } catch (e) {
            console.warn("HTML5 Geolocation denied/unavailable. Falling back to IP Geolocation:", e && e.message);
        }
    }

    // Tier 2: IP-based Geolocation via ipwho.is
    try {
        const ipRes = await fetch('https://ipwho.is/?fields=latitude,longitude,city,region,success', { cache: 'no-store' });
        if (ipRes.ok) {
            const data = await ipRes.json();
            if (data && data.success && data.latitude && data.longitude) {
                const lat = data.latitude;
                const lng = data.longitude;
                window.currentGPSLat = lat;
                window.currentGPSLng = lng;
                window.myLat = lat;
                window.myLng = lng;
                document.dispatchEvent(new CustomEvent('gpsUpdated', {
                    detail: { lat, lng, accuracy: 5000, source: 'ip', city: data.city, region: data.region }
                }));
                return { lat, lng, source: 'ip', city: data.city, region: data.region };
            }
        }
    } catch (err) {
        console.warn("Primary IP Geolocation failed:", err && err.message);
    }

    // Tier 3: Secondary IP Geolocation via ipapi.co
    try {
        const ipRes2 = await fetch('https://ipapi.co/json/', { cache: 'no-store' });
        if (ipRes2.ok) {
            const data = await ipRes2.json();
            if (data && data.latitude && data.longitude) {
                const lat = data.latitude;
                const lng = data.longitude;
                window.currentGPSLat = lat;
                window.currentGPSLng = lng;
                window.myLat = lat;
                window.myLng = lng;
                document.dispatchEvent(new CustomEvent('gpsUpdated', {
                    detail: { lat, lng, accuracy: 5000, source: 'ip', city: data.city, region: data.region }
                }));
                return { lat, lng, source: 'ip', city: data.city, region: data.region };
            }
        }
    } catch (err) {
        console.warn("Secondary IP Geolocation failed:", err && err.message);
    }

    // Tier 4: San Fernando, La Union Heart Fallback
    const fallbackLat = 16.6159;
    const fallbackLng = 120.3167;
    window.currentGPSLat = fallbackLat;
    window.currentGPSLng = fallbackLng;
    window.myLat = fallbackLat;
    window.myLng = fallbackLng;
    document.dispatchEvent(new CustomEvent('gpsUpdated', {
        detail: { lat: fallbackLat, lng: fallbackLng, accuracy: 10000, source: 'fallback', city: 'San Fernando', region: 'La Union' }
    }));
    return { lat: fallbackLat, lng: fallbackLng, source: 'fallback', city: 'San Fernando', region: 'La Union' };
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
window.calculateDistance = calculateDistance;

// Start watching on load automatically and resolve initial location
document.addEventListener('DOMContentLoaded', () => {
    window.startLocationWatch();
    if (!window.currentGPSLat || !window.currentGPSLng) {
        window.resolveUserLocation();
    }
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
        if (target.src.indexOf('placeholderImage') !== -1 || target.src.indexOf('data:image/svg') !== -1 || target.src.indexOf('ui-avatars.com') !== -1) return;
        target.onerror = null;

        // Special handling for user profile avatars
        var isAvatar = target.classList.contains('profile-avatar') ||
            target.classList.contains('podium-avatar') ||
            target.classList.contains('rank-item-avatar') ||
            target.id === 'profile-img' ||
            target.id === 'dash-avatar' ||
            target.id === 'avatar-img' ||
            (target.closest && (target.closest('.profile-avatar-container') || target.closest('.avatar-preview')));

        if (isAvatar) {
            var userName = 'Tourist';
            try {
                var authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                if (authUser.name) userName = authUser.name;
            } catch (err) { }
            target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userName) + '&background=007AFF&color=fff&rounded=true&bold=true&size=128';
            target.style.objectFit = 'cover';
            return;
        }

        var placeholder = window.placeholderImage || '';
        if (placeholder && target.src !== placeholder) {
            target.src = placeholder;
            target.style.objectFit = 'cover';
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
    var backendUrl = window.getBackendUrl ? window.getBackendUrl() : (window.backendUrl || '').replace(/\/+$/, '');
    var r2PublicBase = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev';

    // Phase 1: Extract URL string from dest (photo_url, image, avatar, profile_picture)
    var rawUrl = null;
    if (typeof dest === 'string') {
        rawUrl = dest;
    } else if (dest && typeof dest === 'object') {
        rawUrl = dest.photo_url || dest.image || dest.avatar || dest.profile_picture || null;
    }

    if (rawUrl && typeof rawUrl === 'string' && rawUrl.trim() !== '') {
        var url = rawUrl.trim();

        // 1. Data or Blob URIs
        if (url.indexOf('data:') === 0 || url.indexOf('blob:') === 0) return url;

        // 2. Full HTTP / HTTPS URLs — preserve intact if already an API / serve link
        if (url.indexOf('http://') === 0 || url.indexOf('https://') === 0) {
            if (url.includes('localhost') || url.includes('127.0.0.1')) {
                var localMatch = url.match(/(spot_|avatar_|proof_)[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif)/i);
                if (localMatch && localMatch[0]) {
                    url = localMatch[0];
                } else if (backendUrl) {
                    url = url.replace(/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i, backendUrl);
                }
            } else if (url.includes('/api/serve') || url.includes('/api/image/')) {
                // If it contains a spot/avatar/proof filename, map directly to Cloudflare R2 for fast delivery
                var directMatch = url.match(/(spot_|avatar_|proof_)[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif)/i);
                if (directMatch && directMatch[0]) {
                    url = directMatch[0];
                } else {
                    return url;
                }
            } else {
                try {
                    var parsed = new URL(url);
                    if (parsed.host.includes('r2.dev') || parsed.host.includes('r2.cloudflarestorage.com') || parsed.host.includes('cloudinary.com') || parsed.host.includes('unsplash.com') || parsed.host.includes('googleapis.com') || parsed.host.includes('ui-avatars.com')) {
                        return url;
                    }
                } catch (e) { }
                return url;
            }
        }

        // 3. Extract spot_xxx.jpg / png / webp filename if present -> fetch directly from Cloudflare R2 Bucket
        var spotMatch = url.match(/(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
        if (spotMatch && spotMatch[1]) {
            return r2PublicBase + '/tourist_spots/' + spotMatch[1];
        }

        var avatarMatch = url.match(/(avatar_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
        if (avatarMatch && avatarMatch[1]) {
            return r2PublicBase + '/avatars/' + avatarMatch[1];
        }

        var proofMatch = url.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
        if (proofMatch && proofMatch[1]) {
            return r2PublicBase + '/proof_images/' + proofMatch[1];
        }

        // 4. Relative API endpoints (e.g. /api/serve-image.php?file=..., /api/image/..., /api/serve...)
        if (url.indexOf('/api/') === 0 || url.indexOf('api/') === 0) {
            var cleanApi = url.indexOf('/') === 0 ? url : '/' + url;
            return backendUrl + cleanApi;
        }

        // 4. Local asset paths
        if (url.indexOf('assets/') === 0 || url.indexOf('/assets/') === 0) {
            return (url.indexOf('/') === 0 ? '' : '/') + url;
        }

        // 5. Relative storage/upload paths
        var cleanPath = url.replace(/^\/+/, '').replace(/^storage\//i, '');
        return backendUrl + '/api/image/' + cleanPath;
    }

    // Phase 2: Fallback to local filesystem images (AVAILABLE_MUNI_IMAGES) if photo_url is missing
    if (window.AVAILABLE_MUNI_IMAGES && dest && dest.name) {
        var munisToCheck = [];
        if (dest.municipality) {
            var mClean = dest.municipality.toUpperCase().replace(/\s*TEST$/i, '').trim();
            munisToCheck.push(mClean);
            munisToCheck.push(dest.municipality.toUpperCase());
        }
        var allKeys = Object.keys(window.AVAILABLE_MUNI_IMAGES);
        for (var k = 0; k < allKeys.length; k++) {
            if (munisToCheck.indexOf(allKeys[k]) === -1) {
                munisToCheck.push(allKeys[k]);
            }
        }

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
                    return encodeURI('assets/img/MUNICIPALITIES/' + muni + '/' + img);
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
                }
            }
            if (bestMatch && bestScore >= 100) {
                return encodeURI('assets/img/MUNICIPALITIES/' + bestMuni + '/' + bestMatch);
            }
        }
        if (bestMatch) {
            return encodeURI('assets/img/MUNICIPALITIES/' + bestMuni + '/' + bestMatch);
        }
    }

    // Phase 3: Final fallback SVG image
    return window.noImageFallback;
};

window.handleImgError = function (imgEl, spotName, muniName) {
    if (!imgEl) return;
    imgEl.onerror = null;
    if (window.getDestImage && (spotName || muniName)) {
        var fallback = window.getDestImage({ name: spotName || '', municipality: muniName || '', photo_url: null }, 600);
        if (fallback && fallback !== imgEl.src && !fallback.includes('unsplash.com') && !fallback.startsWith('data:image/svg')) {
            imgEl.src = fallback;
            return;
        }
    }
    imgEl.src = window.noImageFallback;
};

/**
 * Resolves an array of all images for a destination.
 * If dest.images is provided, resolves each image; otherwise falls back to single image.
 * @param {Object} dest
 * @param {number} [width=600]
 * @returns {Array<string>} Array of image URLs
 */
window.getDestImages = function (dest, width) {
    if (!width) width = 600;
    var list = [];

    if (dest && typeof dest === 'object') {
        if (Array.isArray(dest.images) && dest.images.length > 0) {
            dest.images.forEach(function (imgItem) {
                var resolved = window.getDestImage(imgItem, width);
                if (resolved && !list.includes(resolved) && resolved !== window.noImageFallback) {
                    list.push(resolved);
                }
            });
        }
    }

    if (list.length === 0) {
        var single = window.getDestImage(dest, width);
        if (single) list.push(single);
    }

    return list;
};

var rawFallbackSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400" fill="none"><rect width="600" height="400" fill="#0F172A"/><rect x="2" y="2" width="596" height="396" rx="16" fill="url(#bg_grad)" stroke="rgba(255,255,255,0.08)" stroke-width="2"/><defs><linearGradient id="bg_grad" x1="0" y1="0" x2="600" y2="400" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#0F172A"/><stop offset="100%" stop-color="#1E293B"/></linearGradient></defs><circle cx="300" cy="165" r="44" fill="rgba(56,189,248,0.1)" stroke="#38BDF8" stroke-width="2" stroke-dasharray="4 4"/><path d="M284 153H288L290.5 149H309.5L312 153H316C320.418 153 324 156.582 324 161V177C324 181.418 320.418 185 316 185H284C279.582 185 276 181.418 276 177V161C276 156.582 279.582 153 284 153Z" stroke="#38BDF8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><circle cx="300" cy="169" r="7" stroke="#38BDF8" stroke-width="3"/><text x="300" y="240" text-anchor="middle" fill="#F8FAFC" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="20" font-weight="800" letter-spacing="2">NO IMAGE ADDED</text><text x="300" y="268" text-anchor="middle" fill="#94A3B8" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="13" font-weight="500" letter-spacing="0.5">Destination photo coming soon</text></svg>';
window.noImageFallback = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(rawFallbackSvg);

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
            cachedData = window.safeJsonParse(cached, null);
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

// ── Global Badge Detail Modal Handler ─────────────────────────────────────────
window.openBadgeModal = function (name, description, isUnlocked, category, icon) {
    const existing = document.getElementById('badge-details-modal');
    if (existing) existing.remove();

    const isQuest = category === 'Quest';
    const borderGlow = isUnlocked
        ? 'border: 1.5px solid rgba(251, 191, 36, 0.5); box-shadow: 0 0 35px rgba(251, 191, 36, 0.25);'
        : 'border: 1.5px solid rgba(255, 255, 255, 0.15); box-shadow: 0 0 35px rgba(0, 0, 0, 0.5);';

    const statusBadge = isUnlocked
        ? `<span style="background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.3); color:#34d399; font-size:11px; font-weight:800; padding:4px 12px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-check-circle"></i> UNLOCKED BADGE</span>`
        : `<span style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; font-size:11px; font-weight:800; padding:4px 12px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-lock"></i> LOCKED BADGE</span>`;

    const iconStyle = isUnlocked
        ? 'background:rgba(251,191,36,0.15); color:#fbbf24; border:1px solid rgba(251,191,36,0.4);'
        : 'background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.3); border:1px dashed rgba(255,255,255,0.15); filter:grayscale(1);';

    const actionButton = !isUnlocked
        ? `<button onclick="window.closeBadgeModal(); if(typeof navigateTo === 'function') navigateTo('map');" style="width:100%; margin-top:16px; padding:12px; border-radius:100px; border:none; background:linear-gradient(135deg,#10b981,#059669); color:#fff; font-weight:800; font-size:13px; cursor:pointer;"><i class="fa-solid fa-map-location-dot" style="margin-right:6px;"></i>Explore Destinations</button>`
        : `<button onclick="window.closeBadgeModal()" style="width:100%; margin-top:16px; padding:12px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#e2e8f0; font-weight:700; font-size:13px; cursor:pointer;">Close</button>`;

    const safeDesc = (description || 'Complete activities in La Union to unlock this badge.').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

    const modalHtml = `
    <div id="badge-details-modal" onclick="if(event.target === this) window.closeBadgeModal();" style="position:fixed; inset:0; z-index:99999; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); display:flex; align-items:center; justify-content:center; padding:20px; opacity:0; transition:opacity 0.3s ease;">
        <div style="position:relative; background:linear-gradient(145deg, rgba(30, 41, 59, 0.96) 0%, rgba(15, 23, 42, 0.99) 100%); ${borderGlow} border-radius:24px; padding:26px 22px; width:100%; max-width:360px; text-align:center; transform:scale(0.92) translateY(12px); transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
            <button onclick="window.closeBadgeModal()" style="position:absolute; top:14px; right:14px; background:rgba(255,255,255,0.08); border:none; color:rgba(255,255,255,0.7); width:30px; height:30px; border-radius:50%; font-size:14px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s ease;" title="Close">✕</button>
            <div style="width:68px; height:68px; border-radius:50%; ${iconStyle} display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto; font-size:32px;">
                ${isUnlocked ? '<i class="fa-solid fa-award"></i>' : '<i class="fa-solid fa-lock"></i>'}
            </div>
            <div style="margin-bottom:8px;">${statusBadge}</div>
            <h3 style="margin:10px 0 4px; color:#fff; font-size:19px; font-weight:900;">${name}</h3>
            <div style="font-size:11px; color:rgba(148,163,184,0.7); text-transform:uppercase; font-weight:800; letter-spacing:0.5px; margin-bottom:12px;">${category || 'Badge'}</div>
            <p style="font-size:12.5px; color:rgba(255,255,255,0.8); margin:0 0 4px; line-height:1.5; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:12px; border-radius:14px;">
                ${safeDesc}
            </p>
            ${actionButton}
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    requestAnimationFrame(() => {
        const modal = document.getElementById('badge-details-modal');
        if (modal) {
            modal.style.opacity = '1';
            const card = modal.querySelector('div > div');
            if (card) card.style.transform = 'scale(1) translateY(0)';
        }
    });
};

window.closeBadgeModal = function () {
    const modal = document.getElementById('badge-details-modal');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 300);
    }
};

// ── View All Badges Modal Sheet ───────────────────────────────────────────────
window.openAllBadgesModal = function (badgesData) {
    const existing = document.getElementById('all-badges-modal');
    if (existing) existing.remove();

    const badges = badgesData || window._cachedMasterBadges || [];

    const unlocked = badges.filter(b => b.is_unlocked);
    const locked = badges.filter(b => !b.is_unlocked);

    const renderBadgeItem = (b) => {
        const safeName = (b.name || '').replace(/'/g, "\\'");
        const safeDesc = (b.description || '').replace(/'/g, "\\'");
        const clickFn = `onclick="window.openBadgeModal('${safeName}', '${safeDesc}', ${b.is_unlocked ? 'true' : 'false'}, '${b.category || 'Badge'}', '${b.icon || '🏅'}')"`;

        const badgeIcons = {
            'Beach Chiller': '<i class="fa-solid fa-umbrella-beach"></i>',
            'City Express': '<i class="fa-solid fa-city"></i>',
            'Sunset Chaser': '<i class="fa-solid fa-sun"></i>',
            'Foodie Explorer': '<i class="fa-solid fa-utensils"></i>',
            'Adrenaline Chaser': '<i class="fa-solid fa-person-hiking"></i>',
            'Heritage Guardian': '<i class="fa-solid fa-landmark"></i>',
            'Nature Seeker': '<i class="fa-solid fa-water"></i>',
            'Wave Rider': '<i class="fa-solid fa-water-ladder"></i>',
            'First Step': '<i class="fa-solid fa-star"></i>',
            'Globe Trotter': '<i class="fa-solid fa-globe"></i>',
            'Master Voyager': '<i class="fa-solid fa-crown"></i>',
            'Pioneer Explorer': '<i class="fa-solid fa-flag"></i>',
            'Local Voice': '<i class="fa-solid fa-comments"></i>',
        };
        const displayIcon = badgeIcons[b.name] || (b.is_unlocked ? '<i class="fa-solid fa-award"></i>' : '<i class="fa-solid fa-lock"></i>');

        if (b.is_unlocked) {
            return `
            <div ${clickFn} style="background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.3); border-radius: 18px; padding: 14px 10px; text-align: center; cursor: pointer; transition: transform 0.2s;">
                <div style="font-size: 24px; margin-bottom: 6px; color: #fbbf24;">${displayIcon}</div>
                <div style="font-size: 11.5px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${b.name}</div>
                <div style="font-size: 9px; color: #fbbf24; margin-top: 2px; font-weight: 700;">✓ Unlocked</div>
            </div>`;
        } else {
            return `
            <div ${clickFn} style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.12); border-radius: 18px; padding: 14px 10px; text-align: center; opacity: 0.55; filter: grayscale(1); cursor: pointer; transition: transform 0.2s;">
                <div style="font-size: 22px; margin-bottom: 6px; color: rgba(255,255,255,0.4);"><i class="fa-solid fa-lock"></i></div>
                <div style="font-size: 11.5px; font-weight: 700; color: rgba(255,255,255,0.6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${b.name}</div>
                <div style="font-size: 9px; color: rgba(255,255,255,0.4); margin-top: 2px;">Locked</div>
            </div>`;
        }
    };

    const modalHtml = `
    <div id="all-badges-modal" style="position:fixed; inset:0; z-index:99998; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); display:flex; align-items:flex-end; justify-content:center; opacity:0; transition:opacity 0.3s ease;">
        <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.99) 100%); border-top:1.5px solid rgba(251,191,36,0.4); border-radius:28px 28px 0 0; padding:24px 20px 36px; width:100%; max-width:480px; max-height:85vh; overflow-y:auto; transform:translateY(100%); transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
            <div style="width:40px; height:4px; background:rgba(255,255,255,0.2); border-radius:2px; margin:0 auto 16px auto;"></div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                <div>
                    <h3 style="margin:0; font-size:18px; font-weight:900; color:#fff; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-award" style="color:#fbbf24;"></i> All Explorer Badges</h3>
                    <p style="margin:2px 0 0; font-size:11px; color:rgba(255,255,255,0.5);">${unlocked.length} / ${badges.length} Unlocked Badges</p>
                </div>
                <button onclick="window.closeAllBadgesModal()" style="background:rgba(255,255,255,0.08); border:none; color:rgba(255,255,255,0.7); width:32px; height:32px; border-radius:50%; font-size:14px; cursor:pointer;">✕</button>
            </div>

            ${unlocked.length > 0 ? `
                <div style="margin-bottom:20px;">
                    <div style="font-size:11px; font-weight:800; color:#fbbf24; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;"><i class="fa-solid fa-trophy" style="margin-right:6px;"></i> Unlocked Badges (${unlocked.length})</div>
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px;">
                        ${unlocked.map(renderBadgeItem).join('')}
                    </div>
                </div>
            ` : ''}

            <div>
                <div style="font-size:11px; font-weight:800; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;"><i class="fa-solid fa-lock" style="margin-right:6px;"></i> Locked Badges (${locked.length})</div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px;">
                    ${locked.map(renderBadgeItem).join('')}
                </div>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    requestAnimationFrame(() => {
        const modal = document.getElementById('all-badges-modal');
        if (modal) {
            modal.style.opacity = '1';
            const sheet = modal.querySelector('div > div');
            if (sheet) sheet.style.transform = 'translateY(0)';
        }
    });
};

window.closeAllBadgesModal = function () {
    const modal = document.getElementById('all-badges-modal');
    if (modal) {
        modal.style.opacity = '0';
        const sheet = modal.querySelector('div > div');
        if (sheet) sheet.style.transform = 'translateY(100%)';
        setTimeout(() => modal.remove(), 350);
    }
};

// ── Offline Low-Signal Check-in Auto-Sync Queue Handler ─────────────────────
window.processOfflineCheckinQueue = async function () {
    const queueRaw = localStorage.getItem('offline_checkin_queue');
    if (!queueRaw) return;
    try {
        const queue = JSON.parse(queueRaw);
        if (!Array.isArray(queue) || queue.length === 0) return;

        const token = localStorage.getItem('tourist_token');
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        if (token) headers['Authorization'] = `Bearer ${token}`;

        const remaining = [];
        let syncedCount = 0;

        for (const item of queue) {
            try {
                const res = await fetch(`${backendUrl}/api/tourist/points/ar-checkin`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify(item)
                });
                if (res.ok) {
                    syncedCount++;
                } else {
                    remaining.push(item);
                }
            } catch (err) {
                remaining.push(item);
            }
        }

        if (remaining.length > 0) {
            localStorage.setItem('offline_checkin_queue', JSON.stringify(remaining));
        } else {
            localStorage.removeItem('offline_checkin_queue');
        }

        if (syncedCount > 0 && typeof showToast === 'function') {
            showToast(`⚡ ${syncedCount} Offline Check-in(s) synced! XP awarded!`);
        }
    } catch (e) {
        console.warn('Failed processing offline checkin queue', e);
    }
};

window.addEventListener('online', window.processOfflineCheckinQueue);
document.addEventListener('DOMContentLoaded', () => {
    if (navigator.onLine) {
        window.processOfflineCheckinQueue();
    }
});
