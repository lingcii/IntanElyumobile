const CACHE_NAME = 'Intan_Elyu_cache-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
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
        icon: '/mobile/assets/images/logo.png',
        badge: '/mobile/assets/images/logo.png',
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
