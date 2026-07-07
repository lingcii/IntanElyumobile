/**
 * Intan Elyu - Mobile PHP Frontend Main Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // Global Auth Enforcement for Initial Direct Load
    const publicViews = ['splash', 'auth', 'setup_profile'];
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
    const publicViews = ['splash', 'auth', 'setup_profile'];
    if (!publicViews.includes(viewName) && !localStorage.getItem('intan_elyu_token')) {
        viewName = 'auth'; // Force redirect to login if session is empty
    }

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
                
                // Execute any scripts in the new view
                executeScripts(mainContent);
                
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
    
    // Hide nav on splash, auth, and setup profile screens
    const hiddenViews = ['splash', 'auth', 'setup_profile', 'saved_trips'];
    if (hiddenViews.includes(viewName)) {
        nav.classList.add('hidden');
        return;
    }
    
    nav.classList.remove('hidden');
    
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
        const backendUrl = 'http://localhost:8000';
        if (token) {
            await fetch(backendUrl + '/api/auth/logout', {
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
window.toggleDarkMode = function(isDark) {
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
            toggle.addEventListener('change', function() {
                window.toggleDarkMode(this.checked);
            });
        }
    }
});

// --- Push Notifications & Location Services ---

window.intanElyuLocationWatchId = null;

// Initialize Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        // Use relative path for Service Worker to support both / and /mobile/ base URLs
        navigator.serviceWorker.register('sw.js').then(function(registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function(err) {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

// --- Custom In-App Notifications for WebViews ---

window.showInAppNotification = function(title, message, iconUrl = '') {
    // Create the container if it doesn't exist
    let container = document.getElementById('in-app-notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'in-app-notification-container';
        container.style.position = 'fixed';
        container.style.top = '0';
        container.style.left = '0';
        container.style.right = '0';
        container.style.padding = '10px';
        container.style.zIndex = '999999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '8px';
        container.style.pointerEvents = 'none'; // Let clicks pass through empty space
        document.body.appendChild(container);
    }

    // Create the notification card
    const notif = document.createElement('div');
    notif.style.background = 'rgba(28, 28, 30, 0.9)';
    notif.style.backdropFilter = 'blur(20px)';
    notif.style.webkitBackdropFilter = 'blur(20px)';
    notif.style.borderRadius = '16px';
    notif.style.padding = '12px 16px';
    notif.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
    notif.style.border = '1px solid rgba(255,255,255,0.1)';
    notif.style.display = 'flex';
    notif.style.alignItems = 'center';
    notif.style.gap = '12px';
    notif.style.transform = 'translateY(-150%)';
    notif.style.opacity = '0';
    notif.style.transition = 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
    notif.style.pointerEvents = 'auto'; // Make the card itself clickable

    // Determine Icon
    let iconHtml = '';
    if (iconUrl) {
        iconHtml = `<img src="${iconUrl}" style="width:40px; height:40px; border-radius:10px; object-fit:cover;">`;
    } else {
        iconHtml = `<div style="width:40px; height:40px; border-radius:10px; background:var(--primary-color); display:flex; align-items:center; justify-content:center; color:white; font-size:20px;"><i class="fa-solid fa-bell"></i></div>`;
    }

    notif.innerHTML = `
        ${iconHtml}
        <div style="flex:1;">
            <h4 style="margin:0 0 4px 0; color:white; font-size:15px; font-weight:600;">${title}</h4>
            <p style="margin:0; color:#EBEBF5; font-size:13px; line-height:1.3;">${message}</p>
        </div>
    `;

    container.appendChild(notif);

    // Play a subtle native-like sound if possible
    try {
        const audio = new Audio('assets/audio/tuturu.mp3'); // Fallback if missing
        audio.play().catch(e => {}); 
    } catch(e) {}

    // Animate in
    requestAnimationFrame(() => {
        notif.style.transform = 'translateY(env(safe-area-inset-top, 10px))';
        notif.style.opacity = '1';
    });

    // Dismiss logic
    const dismiss = () => {
        notif.style.transform = 'translateY(-150%)';
        notif.style.opacity = '0';
        setTimeout(() => notif.remove(), 400);
    };

    notif.addEventListener('click', dismiss);

    // Auto dismiss after 5 seconds
    setTimeout(dismiss, 5000);
};

window.togglePushNotifications = async function(enabled) {
    localStorage.setItem('intan_elyu_push_enabled', enabled);
    if (enabled) {
        showToast("In-App Notifications enabled!");
        window.showInAppNotification("Intan Elyu", "Notifications are now active! You will be alerted when near a destination.");
    } else {
        showToast("In-App Notifications disabled");
    }
};

window.toggleLocationServices = function(enabled) {
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

window.startLocationWatch = function() {
    if (localStorage.getItem('intan_elyu_loc_enabled') !== 'true') return;
    
    if (!navigator.geolocation) return;

    if (window.intanElyuLocationWatchId) {
        navigator.geolocation.clearWatch(window.intanElyuLocationWatchId);
    }

    let lastAlertedItems = JSON.parse(localStorage.getItem('intan_elyu_alerted_items') || '{}');

    window.intanElyuLocationWatchId = navigator.geolocation.watchPosition(
        (position) => {
            const currentLat = position.coords.latitude;
            const currentLng = position.coords.longitude;
            
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
        (error) => { console.error("Location watch error:", error); },
        { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
    );
};

// Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radius of the earth in m
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2); 
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
    return R * c; // Distance in m
}

// Start watching on load if enabled
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('intan_elyu_loc_enabled') === 'true') {
        window.startLocationWatch();
    }
});
