const CACHE_NAME = 'Intan_Elyu_cache-v6';
const ASSETS = [
    './',
    './assets/css/style.css',
    './assets/js/main.js',
    './assets/img/logo.png',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS).catch(err => console.warn('Pre-cache warning:', err));
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

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    
    // Ignore non-GET requests or browser extension requests
    if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // Network-first strategy for dynamic resources (API, PHP pages, index query params)
    const isDynamic = url.pathname.includes('/api/') || 
                      url.pathname.endsWith('.php') || 
                      url.pathname === '/' || 
                      url.pathname === '/index.php' ||
                      url.search.includes('view=');

    if (isDynamic) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Cache-first strategy for static assets (images, css, js, fonts)
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) return cachedResponse;
            return fetch(event.request).then((response) => {
                if (response.status === 200) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                }
                return response;
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
