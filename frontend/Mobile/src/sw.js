const CACHE_NAME = 'Intan_Elyu_cache-v14';
const ASSETS = [
    './',
    './index.php',
    './index.php?app=true',
    './?app=true',
    './index.php?view=splash',
    './index.php?view=auth',
    './index.php?view=dashboard',
    './index.php?view=splash&ajax=1',
    './index.php?view=auth&ajax=1',
    './index.php?view=dashboard&ajax=1',
    './index.php?view=map&ajax=1',
    './index.php?view=itinerary&ajax=1',
    './index.php?view=saved_trips&ajax=1',
    './index.php?view=saved_places&ajax=1',
    './index.php?view=leaderboard&ajax=1',
    './index.php?view=profile&ajax=1',
    './index.php?view=discount&ajax=1',
    './index.php?view=trending&ajax=1',
    './index.php?view=puzzles&ajax=1',
    './index.php?view=settings&ajax=1',
    './index.php?view=help&ajax=1',
    './index.php?view=about&ajax=1',
    './index.php?view=terms&ajax=1',
    './index.php?view=user_manual&ajax=1',
    './index.php?view=edit_profile&ajax=1',
    './assets/css/style.css',
    './assets/js/main.js',
    './assets/img/logo.png',
    './assets/img/no_image.svg',
    './assets/la_union_municipalities.json',
    // Component CSS
    './assets/css/components/header.css',
    './assets/css/components/bottom_nav.css',
    './assets/css/components/modals.css',
    // View CSS
    './assets/css/views/dashboard.css',
    './assets/css/views/map.css',
    './assets/css/views/itinerary.css',
    './assets/css/views/trip_map.css',
    './assets/css/views/saved_trips.css',
    './assets/css/views/saved_places.css',
    './assets/css/views/leaderboard.css',
    './assets/css/views/profile.css',
    './assets/css/views/discount.css',
    './assets/css/views/trending.css',
    './assets/css/views/puzzles.css',
    './assets/css/views/settings.css',
    './assets/css/views/help.css',
    './assets/css/views/about.css',
    './assets/css/views/terms.css',
    './assets/css/views/auth.css',
    './assets/css/views/splash.css',
    './assets/css/views/user_manual.css',
    './assets/css/views/edit_profile.css',
    // External CDN Libraries
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css',
    'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css',
    'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js',
    'https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css',
    'https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                ASSETS.map(url => cache.add(url).catch(err => console.warn('Pre-cache item failed:', url, err)))
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => clients.claim())
    );
});

// Normalizes request URL by removing volatile timestamp parameters (_t)
function normalizeUrl(urlStr) {
    try {
        const u = new URL(urlStr);
        if (u.searchParams.has('_t')) {
            u.searchParams.delete('_t');
            return u.toString();
        }
    } catch (e) {}
    return urlStr;
}

self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    const isPublicApi = url.pathname.includes('/api/public/');
    const isViewRequest = url.searchParams.has('view');
    const isDynamic = url.pathname.includes('/api/') || 
                      url.pathname.endsWith('.php') || 
                      url.pathname === '/' || 
                      url.pathname === '/index.php' ||
                      isViewRequest;

    // SWR / Network-First with Normalized Cache for Views, App Shell and APIs
    if (isDynamic) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            // Cache both exact request and normalized URL (without _t)
                            cache.put(event.request, clone);
                            const normUrl = normalizeUrl(event.request.url);
                            if (normUrl !== event.request.url) {
                                cache.put(normUrl, response.clone());
                            }
                        });
                    }
                    return response;
                })
                .catch(async () => {
                    const cache = await caches.open(CACHE_NAME);
                    // 1. Try exact match first
                    let cached = await cache.match(event.request);
                    if (cached) return cached;

                    // 2. Try normalized match (without volatile query parameters)
                    const normUrl = normalizeUrl(event.request.url);
                    cached = await cache.match(normUrl);
                    if (cached) return cached;

                    // 3. If requesting an AJAX view or sub-view
                    if (isViewRequest) {
                        const viewName = url.searchParams.get('view');
                        const isAjax = url.searchParams.get('ajax') === '1';
                        if (isAjax) {
                            cached = await cache.match(`index.php?view=${viewName}&ajax=1`) ||
                                     await cache.match(`./index.php?view=${viewName}&ajax=1`);
                            if (cached) return cached;
                        }
                        cached = await cache.match(`index.php?view=${viewName}`) ||
                                 await cache.match(`./index.php?view=${viewName}`) ||
                                 await cache.match(`./?view=${viewName}`);
                        if (cached) return cached;

                        // Fallback for primary views if not found under exact key
                        if (viewName === 'splash') {
                            cached = await cache.match('./index.php?view=splash&ajax=1') ||
                                     await cache.match('./index.php?view=splash');
                            if (cached) return cached;
                        } else if (viewName === 'auth') {
                            cached = await cache.match('./index.php?view=auth&ajax=1') ||
                                     await cache.match('./index.php?view=auth');
                            if (cached) return cached;
                        } else if (viewName === 'dashboard') {
                            cached = await cache.match('./index.php?view=dashboard&ajax=1') ||
                                     await cache.match('./index.php?view=dashboard');
                            if (cached) return cached;
                        }
                    }

                    // 4. If this is a page navigation / full app launch:
                    // Return the cached application shell so the app boots seamlessly offline!
                    const isNav = event.request.mode === 'navigate' ||
                                  (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html') && !url.searchParams.has('ajax'));
                    if (isNav) {
                        cached = await cache.match('./index.php?app=true') ||
                                 await cache.match('./index.php') ||
                                 await cache.match('./?app=true') ||
                                 await cache.match('./') ||
                                 await cache.match('index.php');
                        if (cached) return cached;
                    }

                    // 5. If this is an AJAX view request that truly hasn't been cached:
                    if (isViewRequest) {
                        return new Response(
                            `<div class="offline-view-fallback" style="padding: 48px 24px; text-align: center; color: #1e3a8a; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                                <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                                    <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
                                </div>
                                <h3 style="margin: 0 0 8px; font-weight: 800; font-size: 18px; color: #0f172a;">Offline Mode</h3>
                                <p style="margin: 0 0 20px; font-size: 13px; color: #64748b; line-height: 1.5;">This view was not pre-cached on this device. You can explore saved attractions, itineraries, and maps in Offline Mode.</p>
                                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                                    <button onclick="if(typeof navigateTo==='function') navigateTo('dashboard');" style="padding: 10px 20px; border-radius: 100px; border: none; background: #1e3a8a; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;">Explore Dashboard</button>
                                    <button onclick="if(typeof navigateTo==='function') navigateTo('auth');" style="padding: 10px 20px; border-radius: 100px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; font-weight: 700; font-size: 13px; cursor: pointer;">Switch Account</button>
                                </div>
                            </div>`,
                            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                        );
                    }

                    // 6. JSON fallback for offline API calls
                    return new Response(JSON.stringify({ status: 'offline', message: 'You are currently offline.' }), {
                        status: 503,
                        headers: { 'Content-Type': 'application/json' }
                    });
                })
        );
        return;
    }

    // Cache-first strategy for static assets (images, css, js, fonts, cdn)
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) return cachedResponse;
            return fetch(event.request).then((response) => {
                if (response && response.status === 200) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                }
                return response;
            }).catch(async () => {
                const cached = await caches.match(event.request);
                return cached || new Response('', { status: 404, statusText: 'Not Found' });
            });
        })
    );
});

self.addEventListener('push', function (event) {
    let data = { title: 'Intan Elyu', body: 'New update from Intan Elyu!' };

    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: 'assets/img/logo.png',
        badge: 'assets/img/logo.png',
        vibrate: [200, 100, 200],
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            if (windowClients.length > 0) {
                windowClients[0].focus();
                if (event.notification.data.url) {
                    windowClients[0].navigate(event.notification.data.url);
                }
            } else {
                clients.openWindow(event.notification.data.url || '/');
            }
        })
    );
});
