/**
 * Intan Elyu - Mobile PHP Frontend Main Logic
 */

// Suppress benign browser-extension / VM performance profiler errors (e.g. Web Vitals / reportAllChanges / startTime)
(function () {
    function shouldSuppress(err) {
        if (!err) return false;
        const str = typeof err === 'string' ? err : (err.message || err.stack || String(err));
        return str.includes('startTime') || str.includes('reportAllChanges');
    }

    window.addEventListener('error', function (e) {
        if (shouldSuppress(e.message) || shouldSuppress(e.error)) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return true;
        }
    }, true);

    window.addEventListener('unhandledrejection', function (e) {
        if (shouldSuppress(e.reason)) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return true;
        }
    }, true);

    const prevOnError = window.onerror;
    window.onerror = function (msg, url, lineNo, columnNo, error) {
        if (shouldSuppress(msg) || shouldSuppress(error)) {
            return true;
        }
        if (typeof prevOnError === 'function') {
            return prevOnError.apply(this, arguments);
        }
        return false;
    };

    const origConsoleError = console.error;
    console.error = function () {
        const args = Array.prototype.slice.call(arguments);
        const fullStr = args.map(function (a) {
            return typeof a === 'string' ? a : (a && (a.message || a.stack)) ? (a.message + ' ' + a.stack) : String(a);
        }).join(' ');
        if (shouldSuppress(fullStr)) return;
        return origConsoleError.apply(console, arguments);
    };
})();

// Purge outdated view template and leaderboard caches to force fresh HTML and live ranking data
try {
    for (let i = localStorage.length - 1; i >= 0; i--) {
        const k = localStorage.key(i);
        if (k && (
            k.startsWith('elyu_view_cache_v1_') || 
            k.startsWith('elyu_view_cache_v2_') || 
            k.startsWith('leaderboard_data_v15_') || 
            k.startsWith('leaderboard_data_v16_') || 
            k.startsWith('leaderboard_data_v17_')
        )) {
            localStorage.removeItem(k);
        }
    }
} catch (e) {}

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
                const request = window.indexedDB.open('intan_elyu_app_storage', 3);
                request.onupgradeneeded = function (e) {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains('store')) {
                        db.createObjectStore('store');
                    }
                    if (!db.objectStoreNames.contains('checkin_photos')) {
                        db.createObjectStore('checkin_photos');
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
    },

    setPhoto: async function (key, data) {
        if (!key || !data) return false;
        try {
            const db = await this._getDB();
            if (db) {
                let payload = data;
                if (data instanceof Blob) {
                    payload = data;
                } else if (typeof data === 'string' && data.startsWith('data:')) {
                    const res = await fetch(data);
                    payload = await res.blob();
                }
                return new Promise((resolve) => {
                    const tx = db.transaction('checkin_photos', 'readwrite');
                    tx.objectStore('checkin_photos').put(payload, key);
                    tx.oncomplete = () => resolve(true);
                    tx.onerror = (err) => {
                        console.warn('AppStorage.setPhoto tx error:', err);
                        resolve(false);
                    };
                });
            }
        } catch (e) {
            console.warn('AppStorage.setPhoto exception:', e);
        }
        return false;
    },

    getPhoto: async function (key) {
        if (!key) return null;
        try {
            const db = await this._getDB();
            if (db) {
                return new Promise((resolve) => {
                    const tx = db.transaction('checkin_photos', 'readonly');
                    const req = tx.objectStore('checkin_photos').get(key);
                    req.onsuccess = () => resolve(req.result);
                    req.onerror = () => resolve(null);
                });
            }
        } catch (e) {
            console.warn('AppStorage.getPhoto exception:', e);
        }
        return null;
    },

    removePhoto: async function (key) {
        try {
            const db = await this._getDB();
            if (db) {
                return new Promise((resolve) => {
                    const tx = db.transaction('checkin_photos', 'readwrite');
                    tx.objectStore('checkin_photos').delete(key);
                    tx.oncomplete = () => resolve(true);
                    tx.onerror = () => resolve(false);
                });
            }
        } catch (e) { }
        return false;
    }
};

/**
 * Robust Centralized Offline Check-in Manager & Auto-Synchronizer
 * Stores GPS proof and photo blobs locally when in signal dead zones (e.g. Tangadan Falls, Tapuakan River)
 * and automatically dispatches to backend upon regaining internet.
 */
window.OfflineCheckinManager = {
    QUEUE_KEY: 'intan_elyu_offline_checkins',
    isSyncing: false,

    getQueue: function () {
        try {
            return JSON.parse(localStorage.getItem(this.QUEUE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    },

    saveQueue: function (queue) {
        try {
            localStorage.setItem(this.QUEUE_KEY, JSON.stringify(queue));
        } catch (e) { }
    },

    isItemQueued: function (itemId) {
        if (!itemId) return false;
        const q = this.getQueue();
        const target = String(itemId);
        return q.some(entry => String(entry.itemId) === target || String(entry.spotId) === target);
    },

    getQueuedItem: function (itemId) {
        if (!itemId) return null;
        const q = this.getQueue();
        const target = String(itemId);
        return q.find(entry => String(entry.itemId) === target || String(entry.spotId) === target) || null;
    },

    createThumbnail: function (blobOrFile, maxDim = 120, quality = 0.6) {
        return new Promise((resolve) => {
            try {
                if (!blobOrFile) return resolve(null);
                if (typeof blobOrFile === 'string') {
                    if (blobOrFile.startsWith('data:image')) {
                        if (blobOrFile.length < 60000) return resolve(blobOrFile);
                    } else if (!blobOrFile.startsWith('http') && !blobOrFile.startsWith('blob:')) {
                        return resolve(null);
                    }
                }
                const img = new Image();
                let objectUrl = null;
                if (blobOrFile instanceof Blob) {
                    objectUrl = URL.createObjectURL(blobOrFile);
                    img.src = objectUrl;
                } else if (typeof blobOrFile === 'string') {
                    img.src = blobOrFile;
                } else {
                    return resolve(null);
                }

                img.onload = () => {
                    try {
                        if (objectUrl) URL.revokeObjectURL(objectUrl);
                        let w = img.width || 120;
                        let h = img.height || 120;
                        if (w > maxDim || h > maxDim) {
                            if (w > h) { h = Math.round((h * maxDim) / w); w = maxDim; }
                            else { w = Math.round((w * maxDim) / h); h = maxDim; }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.max(w, 1);
                        canvas.height = Math.max(h, 1);
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        resolve(canvas.toDataURL('image/jpeg', quality));
                    } catch (err) {
                        resolve(null);
                    }
                };
                img.onerror = () => {
                    if (objectUrl) URL.revokeObjectURL(objectUrl);
                    resolve(null);
                };
            } catch (e) {
                resolve(null);
            }
        });
    },

    queueOfflineCheckin: async function (data) {
        const id = 'chk_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
        const capturedAt = data.capturedAt || new Date().toISOString();

        let hasPhoto = false;
        let previewUrl = null;
        const photoData = data.image || data.imageFile || data.photo || null;

        if (photoData) {
            try {
                previewUrl = await this.createThumbnail(photoData, 120, 0.65);
            } catch (thumbErr) {
                console.warn('Could not generate offline photo preview:', thumbErr);
            }
        }

        if (photoData && window.AppStorage && window.AppStorage.setPhoto) {
            try {
                const saved = await window.AppStorage.setPhoto(id, photoData);
                if (saved) {
                    hasPhoto = true;
                }
            } catch (e) {
                console.warn('Could not cache check-in photo in IndexedDB:', e);
            }
        }

        const entry = {
            id: id,
            type: data.type || 'itinerary',
            itemId: data.itemId || null,
            spotId: data.spotId || null,
            spotName: data.spotName || 'Tourist Destination',
            itineraryId: data.itineraryId || null,
            lat: data.lat,
            lng: data.lng,
            accuracy: data.accuracy || null,
            altitude: data.altitude || null,
            speed: data.speed || null,
            capturedAt: capturedAt,
            hasPhoto: hasPhoto || Boolean(previewUrl),
            previewUrl: previewUrl,
            status: 'queued',
            attempts: 0
        };

        const queue = this.getQueue();
        queue.push(entry);
        this.saveQueue(queue);

        // Optimistically record locally in visited spots cache
        try {
            let offlineSpots = JSON.parse(localStorage.getItem('intan_elyu_offline_visited_spots') || '[]');
            const spotKey = String(entry.itemId || entry.spotId);
            if (!offlineSpots.includes(spotKey)) {
                offlineSpots.push(spotKey);
                localStorage.setItem('intan_elyu_offline_visited_spots', JSON.stringify(offlineSpots));
            }
        } catch (e) { }

        window.dispatchEvent(new CustomEvent('offline-checkin-queued', { detail: entry }));
        return entry;
    },

    removeQueuedCheckin: async function (id) {
        let queue = this.getQueue();
        queue = queue.filter(item => item.id !== id);
        this.saveQueue(queue);
        if (window.AppStorage && window.AppStorage.removePhoto) {
            await window.AppStorage.removePhoto(id);
        }
    },

    processQueue: async function () {
        if (this.isSyncing) return;
        if (!navigator.onLine) return;

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return;

        const queue = this.getQueue();
        if (queue.length === 0) return;

        this.isSyncing = true;
        const baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        let syncedCount = 0;

        for (const item of [...queue]) {
            try {
                let photoBlob = null;
                if (item.hasPhoto && window.AppStorage && window.AppStorage.getPhoto) {
                    photoBlob = await window.AppStorage.getPhoto(item.id);
                }

                const formData = new FormData();
                formData.append('lat', String(item.lat));
                formData.append('lng', String(item.lng));
                if (item.accuracy) formData.append('accuracy', String(item.accuracy));
                if (item.altitude) formData.append('altitude', String(item.altitude));
                if (item.speed) formData.append('speed', String(item.speed));
                if (item.capturedAt) formData.append('captured_at', item.capturedAt);

                if (photoBlob) {
                    if (photoBlob instanceof Blob) {
                        formData.append('image', photoBlob, `offline_proof_${item.id}.jpg`);
                    } else if (typeof photoBlob === 'string' && photoBlob.startsWith('data:')) {
                        const res = await fetch(photoBlob);
                        const b = await res.blob();
                        formData.append('image', b, `offline_proof_${item.id}.jpg`);
                    } else if (photoBlob instanceof ArrayBuffer) {
                        const b = new Blob([photoBlob], { type: 'image/jpeg' });
                        formData.append('image', b, `offline_proof_${item.id}.jpg`);
                    }
                }

                let targetUrl = '';
                if (item.type === 'itinerary' && item.itemId) {
                    targetUrl = `${baseUrl}/api/tourist/itineraries/items/${item.itemId}/visit`;
                } else if (item.type === 'destination' && item.spotId) {
                    targetUrl = `${baseUrl}/api/tourist/destinations/${item.spotId}/check-in`;
                } else if (item.type === 'ar' && item.spotId) {
                    targetUrl = `${baseUrl}/api/tourist/points/ar-checkin`;
                    formData.append('spot_id', String(item.spotId));
                    formData.append('destination_id', String(item.spotId));
                } else if (item.itemId) {
                    targetUrl = `${baseUrl}/api/tourist/itineraries/items/${item.itemId}/visit`;
                }

                if (!targetUrl) {
                    await this.removeQueuedCheckin(item.id);
                    continue;
                }

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 20000);

                const response = await fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                        'ngrok-skip-browser-warning': 'true'
                    },
                    body: formData,
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                let data = await response.json().catch(() => ({}));
                let isResolved = response.ok || response.status === 409 || data.already_recorded || (data.status && ['pending', 'approved', 'success'].includes(data.status));

                // If itinerary item check-in 404s (e.g. temporary offline item ID) and spotId is known, fallback to destination check-in
                if (!isResolved && response.status === 404 && item.spotId) {
                    try {
                        const fallbackUrl = `${baseUrl}/api/tourist/destinations/${item.spotId}/check-in`;
                        const res2 = await fetch(fallbackUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': 'Bearer ' + token,
                                'ngrok-skip-browser-warning': 'true'
                            },
                            body: formData
                        });
                        const data2 = await res2.json().catch(() => ({}));
                        if (res2.ok || res2.status === 409 || data2.already_recorded || (data2.status && ['pending', 'approved', 'success'].includes(data2.status))) {
                            isResolved = true;
                            data = data2;
                        }
                    } catch (fbErr) {}
                }

                // Treat 200, 201, 409 (already recorded), or explicit already_recorded flag as successfully resolved
                if (isResolved) {
                    syncedCount++;

                    // Optimistically update cached itinerary trips so user immediately sees their photo & pending review status
                    const returnedPhoto = data.proof_image || item.previewUrl || null;
                    const finalStatus = data.status || 'pending';
                    const isApproved = finalStatus === 'approved' || finalStatus === 'success';

                    if (item.itemId) {
                        try {
                            const tokenKey = token.substring(0, 10);
                            ['saved_trips_' + tokenKey, 'dashboard_saved_trips_' + tokenKey, 'dashboard_trips_' + tokenKey].forEach(cacheKey => {
                                const raw = localStorage.getItem(cacheKey);
                                if (!raw) return;
                                const parsed = JSON.parse(raw);
                                const list = Array.isArray(parsed) ? parsed : (parsed.itineraries || parsed.data || null);
                                if (Array.isArray(list)) {
                                    list.forEach(trip => {
                                        if (trip.items && Array.isArray(trip.items)) {
                                            const match = trip.items.find(i => String(i.id) === String(item.itemId));
                                            if (match) {
                                                match.proof_status = finalStatus;
                                                if (returnedPhoto) match.proof_image = returnedPhoto;
                                                if (isApproved) match.is_visited = true;
                                            }
                                        }
                                    });
                                    if (Array.isArray(parsed)) {
                                        localStorage.setItem(cacheKey, JSON.stringify(list));
                                    } else {
                                        parsed[parsed.itineraries ? 'itineraries' : 'data'] = list;
                                        localStorage.setItem(cacheKey, JSON.stringify(parsed));
                                    }
                                }
                            });
                        } catch (cacheErr) {
                            console.warn('Could not update local cached trip after sync:', cacheErr);
                        }
                    }

                    await this.removeQueuedCheckin(item.id);
                } else if (response.status >= 400 && response.status < 500 && response.status !== 408) {
                    // Client permanent errors (e.g. 404 item deleted, 422 permanent invalid)
                    item.attempts = (item.attempts || 0) + 1;
                    if (item.attempts >= 3) {
                        await this.removeQueuedCheckin(item.id);
                    } else {
                        const curQ = this.getQueue();
                        const idx = curQ.findIndex(q => q.id === item.id);
                        if (idx !== -1) curQ[idx].attempts = item.attempts;
                        this.saveQueue(curQ);
                    }
                }
            } catch (err) {
                console.warn('Offline check-in sync paused for item:', item.id, err);
                // Connection failed during upload; keep remaining items in queue and retry next cycle
                break;
            }
        }

        this.isSyncing = false;

        if (syncedCount > 0) {
            // Force cache expiration so next useCache fetch calls the network
            try {
                const tokenKey = token.substring(0, 10);
                ['saved_trips_' + tokenKey, 'dashboard_saved_trips_' + tokenKey, 'dashboard_trips_' + tokenKey].forEach(k => {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        try {
                            const parsed = JSON.parse(raw);
                            if (parsed && typeof parsed === 'object') {
                                parsed.timestamp = 0;
                                localStorage.setItem(k, JSON.stringify(parsed));
                            }
                        } catch (e) { }
                    }
                });
            } catch (e) { }

            if (typeof showToast === 'function') {
                showToast(`⚡ ${syncedCount} Offline Check-in(s) synced! Proof submitted.`);
            }
            window.dispatchEvent(new CustomEvent('checkins-synced', { detail: { count: syncedCount } }));
        }
        return syncedCount;
    },

    syncOfflineQueue: function () {
        return this.processQueue();
    },

    init: function () {
        window.addEventListener('online', () => {
            setTimeout(() => this.processQueue(), 1200);
        });

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                this.processQueue();
            }
        });

        // Periodic background retry every 45 seconds if queue has items
        setInterval(() => {
            if (navigator.onLine && this.getQueue().length > 0) {
                this.processQueue();
            }
        }, 45000);

        // Initial check upon app boot
        if (navigator.onLine) {
            setTimeout(() => this.processQueue(), 3000);
        }
    }
};

window.OfflineCheckinManager.init();

// ══════════════════════════════════════════════════════════════════════════
// OFFLINE PLANNING STRATEGY: OFFLINE TRIP & ITINERARY MANAGER
// ══════════════════════════════════════════════════════════════════════════
window.OfflineTripManager = {
    STORAGE_KEY: 'intan_elyu_offline_saved_trips',

    getQueuedTrips: function () {
        try {
            return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    },

    queueOfflineTrip: function (tripData) {
        try {
            const trips = this.getQueuedTrips();
            const updateId = tripData.update_id ? String(tripData.update_id) : null;

            // 1. If updating an already-queued offline trip (e.g. offline_1729...)
            if (updateId && updateId.startsWith('offline_')) {
                const existingIdx = trips.findIndex(t => t.id === updateId || t.local_id === updateId);
                if (existingIdx !== -1) {
                    trips[existingIdx] = {
                        ...trips[existingIdx],
                        ...tripData,
                        id: updateId,
                        local_id: updateId,
                        is_offline_queued: true,
                        updated_at: new Date().toISOString()
                    };
                    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(trips));
                    window.dispatchEvent(new CustomEvent('offlineTripQueued', { detail: trips[existingIdx] }));
                    return trips[existingIdx];
                }
            }

            // 2. If editing an existing server-synced trip while offline
            if (updateId && !updateId.startsWith('offline_')) {
                const editTempId = 'offline_edit_' + updateId;
                const existingEditIdx = trips.findIndex(t => t.id === editTempId || String(t.update_id) === updateId);
                const updatedTrip = {
                    ...tripData,
                    id: editTempId,
                    local_id: editTempId,
                    update_id: updateId,
                    is_offline_queued: true,
                    is_offline_update: true,
                    updated_at: new Date().toISOString()
                };

                if (existingEditIdx !== -1) {
                    trips[existingEditIdx] = updatedTrip;
                } else {
                    trips.unshift(updatedTrip);
                }
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(trips));
                window.dispatchEvent(new CustomEvent('offlineTripQueued', { detail: updatedTrip }));
                return updatedTrip;
            }

            // 3. Brand new offline trip
            const tempId = 'offline_' + Date.now();
            const newTrip = {
                ...tripData,
                id: tempId,
                local_id: tempId,
                is_offline_queued: true,
                created_at: new Date().toISOString()
            };
            trips.unshift(newTrip);
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(trips));
            window.dispatchEvent(new CustomEvent('offlineTripQueued', { detail: newTrip }));
            return newTrip;
        } catch (e) {
            console.error('Failed to queue offline trip:', e);
            return null;
        }
    },

    removeQueuedTrip: function (tempId) {
        try {
            if (!tempId) return;
            const targetStr = String(tempId).trim();
            const trips = this.getQueuedTrips().filter(t => {
                if (!t) return false;
                const idStr = String(t.id || '');
                const localIdStr = String(t.local_id || '');
                const updateIdStr = String(t.update_id || '');
                const tripIdStr = String(t.trip_id || '');

                if (idStr === targetStr || localIdStr === targetStr || updateIdStr === targetStr || tripIdStr === targetStr) {
                    return false;
                }
                if (idStr === 'offline_edit_' + targetStr || localIdStr === 'offline_edit_' + targetStr) {
                    return false;
                }
                if (targetStr.startsWith('offline_edit_')) {
                    const rawId = targetStr.replace('offline_edit_', '');
                    if (idStr === rawId || updateIdStr === rawId || localIdStr === rawId) {
                        return false;
                    }
                }
                return true;
            });
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(trips));
        } catch (e) {}
    },

    syncSingleTrip: async function (tripId) {
        if (!navigator.onLine) return false;
        const trips = this.getQueuedTrips();
        const trip = trips.find(t => t.id === tripId || t.local_id === tripId);
        if (!trip) return false;

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return false;

        const primaryUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || window.BACKEND_URL || '')).replace(/\/+$/, '');
        const baseCandidates = [];
        if (primaryUrl && !primaryUrl.includes('localhost') && !primaryUrl.includes('127.0.0.1')) {
            baseCandidates.push(primaryUrl);
        }
        baseCandidates.push('https://api.intan-elyu.online');
        if (primaryUrl && !baseCandidates.includes(primaryUrl)) {
            baseCandidates.push(primaryUrl);
        }
        baseCandidates.push('');

        const payload = {
            title: trip.title,
            trip_date: trip.trip_date || null,
            budget: trip.budget || null,
            destinations: trip.destinations || [],
            route_type: trip.route_type || 'Recommended',
            transport_mode: trip.transport_mode || 'No Vehicle Selected',
            leg_transports: trip.leg_transports || []
        };

        let synced = false;
        for (const bUrl of baseCandidates) {
            try {
                const endpoint = (trip.is_offline_update && trip.update_id)
                    ? `${bUrl}/api/tourist/itineraries/${trip.update_id}`
                    : `${bUrl}/api/tourist/itineraries`;
                const method = (trip.is_offline_update && trip.update_id) ? 'PUT' : 'POST';
                const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                const timeoutId = controller ? setTimeout(() => controller.abort(), 10000) : null;
                const res = await fetch(endpoint, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(payload),
                    signal: controller ? controller.signal : undefined
                });
                if (timeoutId) clearTimeout(timeoutId);
                if (res.ok) {
                    this.removeQueuedTrip(trip.id);
                    synced = true;
                    break;
                }
            } catch (e) {
                // Try next candidate
            }
        }

        if (synced) {
            const tokenKey = token.substring(0, 10);
            localStorage.removeItem('saved_trips_' + tokenKey);
            localStorage.removeItem('dashboard_trips_' + tokenKey);
            localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
            if (typeof window._cachedSavedTrips !== 'undefined') {
                window._cachedSavedTrips = null;
            }
            window.dispatchEvent(new CustomEvent('tripsSynced'));
            if (typeof window.fetchSavedTrips === 'function') {
                window.fetchSavedTrips(true);
            }
            if (typeof window.loadSavedTrips === 'function') {
                window.loadSavedTrips(true);
            }
        }
        return synced;
    },

    syncQueuedTrips: async function () {
        if (!navigator.onLine) return 0;
        const trips = this.getQueuedTrips();
        if (!trips || trips.length === 0) return 0;

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return 0;

        const primaryUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || window.BACKEND_URL || '')).replace(/\/+$/, '');
        const baseCandidates = [];
        if (primaryUrl && !primaryUrl.includes('localhost') && !primaryUrl.includes('127.0.0.1')) {
            baseCandidates.push(primaryUrl);
        }
        baseCandidates.push('https://api.intan-elyu.online');
        if (primaryUrl && !baseCandidates.includes(primaryUrl)) {
            baseCandidates.push(primaryUrl);
        }
        baseCandidates.push('');

        let syncedCount = 0;

        for (const trip of trips) {
            try {
                const payload = {
                    title: trip.title,
                    trip_date: trip.trip_date || null,
                    budget: trip.budget || null,
                    destinations: trip.destinations || [],
                    route_type: trip.route_type || 'Recommended',
                    transport_mode: trip.transport_mode || 'No Vehicle Selected',
                    leg_transports: trip.leg_transports || []
                };

                let tripSuccess = false;
                for (const bUrl of baseCandidates) {
                    try {
                        const endpoint = (trip.is_offline_update && trip.update_id)
                            ? `${bUrl}/api/tourist/itineraries/${trip.update_id}`
                            : `${bUrl}/api/tourist/itineraries`;
                        const method = (trip.is_offline_update && trip.update_id) ? 'PUT' : 'POST';
                        const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                        const timeoutId = controller ? setTimeout(() => controller.abort(), 10000) : null;
                        const res = await fetch(endpoint, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': 'Bearer ' + token
                            },
                            body: JSON.stringify(payload),
                            signal: controller ? controller.signal : undefined
                        });
                        if (timeoutId) clearTimeout(timeoutId);
                        if (res.ok) {
                            this.removeQueuedTrip(trip.id);
                            syncedCount++;
                            tripSuccess = true;
                            break;
                        }
                    } catch (candErr) {
                        // continue to next base URL candidate
                    }
                }
            } catch (err) {
                console.warn('Could not sync trip:', trip.title, err);
            }
        }

        if (syncedCount > 0) {
            const tokenKey = token.substring(0, 10);
            localStorage.removeItem('saved_trips_' + tokenKey);
            localStorage.removeItem('dashboard_trips_' + tokenKey);
            localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
            if (typeof window._cachedSavedTrips !== 'undefined') {
                window._cachedSavedTrips = null;
            }
            window.dispatchEvent(new CustomEvent('tripsSynced'));
            if (typeof window.fetchSavedTrips === 'function') {
                window.fetchSavedTrips(true);
            }
            if (typeof window.loadSavedTrips === 'function') {
                window.loadSavedTrips(true);
            }
        }
        return syncedCount;
    }
};

// ══════════════════════════════════════════════════════════════════════════
// OFFLINE QUEUE: COMPLETED TRIPS MANAGER
// ══════════════════════════════════════════════════════════════════════════
window.OfflineCompletedTripsManager = {
    STORAGE_KEY: 'intan_elyu_offline_completed_trips',

    getQueue: function () {
        try {
            return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    },

    saveQueue: function (queue) {
        try {
            if (!queue || queue.length === 0) {
                localStorage.removeItem(this.STORAGE_KEY);
            } else {
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(queue));
            }
        } catch (e) {}
    },

    queueCompletion: function (tripId) {
        const queue = this.getQueue();
        if (!queue.some(item => String(item.tripId) === String(tripId))) {
            queue.push({ tripId: String(tripId), timestamp: Date.now() });
            this.saveQueue(queue);
        }
    },

    processQueue: async function () {
        if (!navigator.onLine) return 0;
        const queue = this.getQueue();
        if (!queue || queue.length === 0) return 0;

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return 0;

        const bUrl = (window.backendUrl || window.BACKEND_URL || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        let synced = 0;
        const remaining = [];

        for (const item of queue) {
            try {
                const res = await fetch(`${bUrl}/api/tourist/itineraries/${item.tripId}/complete`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (res.ok || res.status === 409) {
                    synced++;
                } else if (res.status >= 400 && res.status < 500 && res.status !== 408) {
                    // permanent client error (e.g. 404 deleted)
                } else {
                    remaining.push(item);
                }
            } catch (err) {
                remaining.push(item);
                break;
            }
        }

        this.saveQueue(remaining);
        if (synced > 0) {
            const tokenKey = token.substring(0, 10);
            localStorage.removeItem('saved_trips_' + tokenKey);
            localStorage.removeItem('dashboard_trips_' + tokenKey);
            window.dispatchEvent(new CustomEvent('tripsSynced'));
        }
        return synced;
    }
};

// ══════════════════════════════════════════════════════════════════════════
// OFFLINE QUEUE: FAVORITE / BOOKMARK TOGGLES MANAGER
// ══════════════════════════════════════════════════════════════════════════
window.OfflineFavoriteManager = {
    STORAGE_KEY: 'intan_elyu_offline_favorite_toggles',

    getQueue: function () {
        try {
            return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    },

    saveQueue: function (queue) {
        try {
            if (!queue || queue.length === 0) {
                localStorage.removeItem(this.STORAGE_KEY);
            } else {
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(queue));
            }
        } catch (e) {}
    },

    queueToggle: function (spotId) {
        const queue = this.getQueue();
        queue.push({ spotId: String(spotId), timestamp: Date.now() });
        this.saveQueue(queue);
    },

    processQueue: async function () {
        if (!navigator.onLine) return 0;
        const queue = this.getQueue();
        if (!queue || queue.length === 0) return 0;

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return 0;

        const bUrl = (window.backendUrl || window.BACKEND_URL || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        let synced = 0;
        const remaining = [];

        for (const item of queue) {
            try {
                const res = await fetch(`${bUrl}/api/tourist/destinations/${item.spotId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (res.ok || res.status === 409) {
                    synced++;
                } else if (res.status >= 400 && res.status < 500 && res.status !== 408) {
                    // permanent client error
                } else {
                    remaining.push(item);
                }
            } catch (err) {
                remaining.push(item);
                break;
            }
        }

        this.saveQueue(remaining);
        if (synced > 0) {
            const tokenKey = token.substring(0, 10);
            localStorage.removeItem('saved_places_' + tokenKey);
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && k.startsWith('dashboard_data_')) localStorage.removeItem(k);
            }
            window.dispatchEvent(new CustomEvent('favoritesSynced'));
        }
        return synced;
    }
};

// ══════════════════════════════════════════════════════════════════════════
// OFFLINE QUEUE: GAMEZONE CLAIMS MANAGER
// ══════════════════════════════════════════════════════════════════════════
window.OfflineGameClaimsManager = {
    STORAGE_KEY: 'intan_elyu_offline_game_claims',

    getQueue: function () {
        try {
            return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    },

    saveQueue: function (queue) {
        try {
            if (!queue || queue.length === 0) {
                localStorage.removeItem(this.STORAGE_KEY);
            } else {
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(queue));
            }
        } catch (e) {}
    },

    queueClaim: function (gameType) {
        const queue = this.getQueue();
        if (!queue.some(item => item.game_type === gameType)) {
            queue.push({ game_type: gameType, timestamp: Date.now() });
            this.saveQueue(queue);
        }
    },

    processQueue: async function () {
        if (!navigator.onLine) return 0;
        const queue = this.getQueue();
        if (!queue || queue.length === 0) return 0;

        const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return 0;

        const bUrl = (window.backendUrl || window.BACKEND_URL || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        let synced = 0;
        const remaining = [];

        for (const item of queue) {
            try {
                let endpoint = '/api/tourist/points/minigame';
                let bodyObj = { game_type: item.game_type };
                if (item.game_type === 'puzzle') {
                    endpoint = '/api/tourist/points/puzzle';
                    bodyObj = {};
                } else if (item.game_type === 'trivia') {
                    endpoint = '/api/tourist/points/trivia';
                    bodyObj = {};
                }

                const res = await fetch(`${bUrl}${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(bodyObj)
                });
                if (res.ok || res.status === 409 || res.status === 429) {
                    synced++;
                } else if (res.status >= 400 && res.status < 500 && res.status !== 408) {
                    // permanent client error
                } else {
                    remaining.push(item);
                }
            } catch (err) {
                remaining.push(item);
                break;
            }
        }

        this.saveQueue(remaining);
        if (synced > 0) {
            window.leaderboardNeedsRefresh = true;
            for (let i = localStorage.length - 1; i >= 0; i--) {
                const k = localStorage.key(i);
                if (k && (k.startsWith('leaderboard_data_') || k.startsWith('points_balance_'))) {
                    localStorage.removeItem(k);
                }
            }
            window.dispatchEvent(new CustomEvent('gamePointsSynced'));
        }
        return synced;
    }
};

// ══════════════════════════════════════════════════════════════════════════
// OFFLINE QUEUE: PENDING PROFILE UPDATES MANAGER
// ══════════════════════════════════════════════════════════════════════════
window.OfflineProfileManager = {
    STORAGE_KEY: 'intan_elyu_pending_profile_update',

    getPending: function () {
        try {
            return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || 'null');
        } catch (e) {
            return null;
        }
    },

    savePending: function (data) {
        try {
            if (!data) localStorage.removeItem(this.STORAGE_KEY);
            else localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
        } catch (e) {}
    },

    processQueue: async function () {
        if (!navigator.onLine) return 0;
        const pending = this.getPending();
        if (!pending) return 0;

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return 0;

        const bUrl = (window.backendUrl || window.BACKEND_URL || 'https://api.intan-elyu.online').replace(/\/+$/, '');

        try {
            const formData = new FormData();
            if (pending.name) formData.append('name', pending.name);
            if (pending.email) formData.append('email', pending.email);
            if (pending.phone) formData.append('phone', pending.phone);
            if (pending.home_location) formData.append('home_location', pending.home_location);
            if (pending.age) formData.append('age', pending.age);
            if (pending.gender) formData.append('gender', pending.gender);
            if (pending.bio) formData.append('bio', pending.bio);
            if (pending.travel_preferences) formData.append('travel_preferences', pending.travel_preferences);

            if (pending.avatarBase64 && pending.avatarBase64.startsWith('data:')) {
                const bRes = await fetch(pending.avatarBase64);
                const blob = await bRes.blob();
                formData.append('avatar', blob, 'offline_avatar.jpg');
            }

            const res = await fetch(`${bUrl}/api/tourist/profile`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: formData
            });

            if (res.ok) {
                this.savePending(null);
                const data = await res.json().catch(() => ({}));
                if (data.user) {
                    const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    Object.assign(stored, data.user);
                    localStorage.setItem('auth_user', JSON.stringify(stored));
                }
                window.dispatchEvent(new CustomEvent('userProfileUpdated'));
                return 1;
            }
        } catch (err) {
            console.warn('Offline profile sync error:', err);
        }
        return 0;
    }
};

// ══════════════════════════════════════════════════════════════════════════
// AMBIENT CONNECTIVITY MANAGER (CALM, EVENT-DRIVEN, NO-FLAP)
// ══════════════════════════════════════════════════════════════════════════
(function initAmbientConnectivity() {
    let wasOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : false;
    let hidePillTimeout = null;
    let offlineDebounceTimeout = null;
    let lastOnlineSyncTime = 0;
    window.isAppOnline = (typeof navigator.onLine === 'boolean') ? navigator.onLine : true;
    window.isAppOffline = function () {
        if (typeof navigator !== 'undefined' && typeof navigator.onLine === 'boolean') {
            return !navigator.onLine;
        }
        return window.isAppOnline === false;
    };
    window.isOffline = window.isAppOffline;
    if (window.isAppOnline) {
        try {
            localStorage.removeItem('intan_elyu_offline_mode');
            sessionStorage.removeItem('elyu_show_offline_bypass_notice');
        } catch(e) {}
    }

    function showOfflinePill() {
        const pill = document.getElementById('elyu-offline-pill');
        const icon = document.getElementById('elyu-offline-pill-icon');
        const text = document.getElementById('elyu-offline-pill-text');
        if (!pill) return;
        if (hidePillTimeout) { clearTimeout(hidePillTimeout); hidePillTimeout = null; }
        pill.classList.remove('hidden', 'online-syncing');
        pill.style.display = 'inline-flex';
        if (icon) {
            icon.className = 'fa-solid fa-cloud-slash';
            icon.style.color = '#f59e0b';
        }
        if (text) text.innerHTML = 'Offline Mode &bull; Showing saved data';

        // Auto-dismiss after 3.2s so it never stays stuck or blocks the UI
        hidePillTimeout = setTimeout(() => {
            pill.classList.add('hidden');
            setTimeout(() => {
                if (pill.classList.contains('hidden')) pill.style.display = 'none';
            }, 400);
        }, 3200);
    }

    function showOnlineSyncPill() {
        const pill = document.getElementById('elyu-offline-pill');
        const icon = document.getElementById('elyu-offline-pill-icon');
        const text = document.getElementById('elyu-offline-pill-text');
        if (!pill) return;
        if (hidePillTimeout) { clearTimeout(hidePillTimeout); hidePillTimeout = null; }
        pill.classList.remove('hidden');
        pill.classList.add('online-syncing');
        pill.style.display = 'inline-flex';
        if (icon) {
            icon.className = 'fa-solid fa-cloud-arrow-up';
            icon.style.color = '#34d399';
        }
        if (text) text.innerHTML = 'Back online &bull; Syncing data...';

        hidePillTimeout = setTimeout(() => {
            pill.classList.add('hidden');
            setTimeout(() => {
                if (pill.classList.contains('hidden')) pill.style.display = 'none';
            }, 400);
        }, 2500);
    }

    // Unified background sync orchestrator when connection returns
    window.syncAllDataWhenOnline = async function () {
        if (window._isSyncingOnlineData) return;
        if (!navigator.onLine) return;
        window._isSyncingOnlineData = true;

        try {
            let totalSynced = 0;

            // 1. Sync queued offline itineraries
            if (window.OfflineTripManager && typeof window.OfflineTripManager.syncQueuedTrips === 'function') {
                const count = await window.OfflineTripManager.syncQueuedTrips();
                totalSynced += (count || 0);
            }

            // 2. Sync queued offline check-ins (with photo blobs)
            if (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.processQueue === 'function') {
                const count = await window.OfflineCheckinManager.processQueue();
                totalSynced += (count || 0);
            }

            // 3. Sync queued offline trip completions
            if (window.OfflineCompletedTripsManager && typeof window.OfflineCompletedTripsManager.processQueue === 'function') {
                const count = await window.OfflineCompletedTripsManager.processQueue();
                totalSynced += (count || 0);
            }

            // 4. Sync queued offline favorite bookmarks
            if (window.OfflineFavoriteManager && typeof window.OfflineFavoriteManager.processQueue === 'function') {
                const count = await window.OfflineFavoriteManager.processQueue();
                totalSynced += (count || 0);
            }

            // 5. Sync queued offline GameZone claims
            if (window.OfflineGameClaimsManager && typeof window.OfflineGameClaimsManager.processQueue === 'function') {
                const count = await window.OfflineGameClaimsManager.processQueue();
                totalSynced += (count || 0);
            }

            // 6. Sync queued offline profile updates
            if (window.OfflineProfileManager && typeof window.OfflineProfileManager.processQueue === 'function') {
                const count = await window.OfflineProfileManager.processQueue();
                totalSynced += (count || 0);
            }

            // Consolidated status notification
            if (totalSynced > 0 && typeof showToast === 'function') {
                showToast(`⚡ All offline data synced (${totalSynced} items updated)!`);
            }

            // Mark views as needing refresh so they fetch cleanly when user navigates
            window.leaderboardNeedsRefresh = true;
            window.dashboardNeedsRefresh = true;
            window.profileNeedsRefresh = true;
            window.discountNeedsRefresh = true;
            window.trendingNeedsRefresh = true;

            // Refresh ONLY the currently active view
            const activeView = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
            if (activeView === 'leaderboard' && typeof window.initLeaderboardView === 'function') {
                window.initLeaderboardView(true);
            } else if (activeView === 'dashboard' && typeof window.dashboardInit === 'function') {
                window.dashboardInit(true);
            } else if (activeView === 'profile' && typeof window.fetchProfileData === 'function') {
                window.fetchProfileData(true);
                if (typeof window.fetchPointsAndVouchers === 'function') window.fetchPointsAndVouchers(true);
            } else if (activeView === 'discount' && typeof window.fetchLiveDatabaseVouchers === 'function') {
                window.fetchLiveDatabaseVouchers();
                if (typeof window.fetchUserPointsAndRedemptions === 'function') window.fetchUserPointsAndRedemptions();
            } else if (activeView === 'trending' && typeof window.fetchAllData === 'function') {
                window.fetchAllData(true);
            } else if (activeView === 'saved_places' && typeof window.fetchSavedPlaces === 'function') {
                window.fetchSavedPlaces(true);
            } else if (activeView === 'saved_trips' && typeof window.fetchSavedTrips === 'function') {
                window.fetchSavedTrips(true);
            }

            prewarmOfflineData();

        } catch (e) {
            console.warn('Ambient reconnect sync error:', e);
        } finally {
            setTimeout(() => { window._isSyncingOnlineData = false; }, 2000);
        }
    };

    function enforceAllOfflineBadgesHidden() {
        if (typeof navigator.onLine === 'boolean' && !navigator.onLine) return;
        window.isAppOnline = true;
        if (document.body) {
            document.body.classList.remove('is-offline');
            document.body.removeAttribute('data-offline');
        }
        try {
            localStorage.removeItem('intan_elyu_offline_mode');
            localStorage.removeItem('intan_elyu_offline_guest');
            sessionStorage.removeItem('elyu_show_offline_bypass_notice');

            const curToken = localStorage.getItem('intan_elyu_token');
            const realBackup = localStorage.getItem('intan_elyu_real_token_backup');
            if (curToken === 'offline_guest_token' || (curToken && curToken.startsWith('offline_'))) {
                if (realBackup && realBackup !== 'guest_token' && !realBackup.startsWith('offline_') && !realBackup.startsWith('guest_')) {
                    localStorage.setItem('intan_elyu_token', realBackup);
                    const realUserBackup = localStorage.getItem('auth_user_backup');
                    if (realUserBackup) {
                        try { localStorage.setItem('auth_user', realUserBackup); } catch (e) { }
                    }
                } else if (curToken === 'offline_guest_token') {
                    localStorage.setItem('intan_elyu_token', 'guest_token');
                    localStorage.setItem('intan_elyu_guest', 'true');
                }
            }
        } catch(e) {}

        const pillsToHide = [
            'elyu-offline-pill', 'dash-offline-pill', 'trending-offline-pill',
            'trip-offline-badge', 'saved-trips-offline-badge', 'saved-places-offline-badge',
            'puzzles-offline-badge', 'auth-offline-badge', 'profile-offline-badge',
            'discount-offline-badge', 'edit-profile-offline-badge', 'leaderboard-offline-badge',
            'reset-offline-badge', 'splash-offline-pill'
        ];
        pillsToHide.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.setProperty('display', 'none', 'important');
                el.classList.add('hidden');
            }
        });

        document.querySelectorAll('[id*="offline-badge"], [id*="offline-pill"], .auth-offline-badge, .offline-badge, .offline-banner, .splash-offline-pill').forEach(el => {
            el.style.setProperty('display', 'none', 'important');
            el.classList.add('hidden');
        });

        document.querySelectorAll('.btn-offline-guest').forEach(btn => {
            const span = btn.querySelector('span');
            const icon = btn.querySelector('i');
            if (span && span.textContent && span.textContent.includes('Offline')) {
                span.textContent = 'Continue as Guest';
            }
            if (icon && icon.classList.contains('fa-cloud-slash')) {
                icon.className = 'fa-solid fa-compass';
            }
        });
    }
    window.enforceAllOfflineBadgesHidden = enforceAllOfflineBadgesHidden;

    window.notifyAppOnline = function () {
        if (offlineDebounceTimeout) {
            clearTimeout(offlineDebounceTimeout);
            offlineDebounceTimeout = null;
        }

        window.isAppOnline = true;
        enforceAllOfflineBadgesHidden();
        [50, 150, 300, 600, 1200, 2000].forEach(d => setTimeout(enforceAllOfflineBadgesHidden, d));

        const now = Date.now();
        if (wasOffline) {
            wasOffline = false;
            showOnlineSyncPill();
        } else {
            const pill = document.getElementById('elyu-offline-pill');
            if (pill) {
                pill.classList.add('hidden');
                pill.style.setProperty('display', 'none', 'important');
            }
        }

        try {
            window.dispatchEvent(new CustomEvent('appOnline', { detail: { timestamp: now } }));
        } catch(e) {}

        if (now - lastOnlineSyncTime >= 2000) {
            lastOnlineSyncTime = now;
            window.syncAllDataWhenOnline();
        }
    };

    window.notifyAppOffline = function () {
        // Debounce offline notification to avoid false alarms from mobile network handoffs
        if (offlineDebounceTimeout) clearTimeout(offlineDebounceTimeout);
        offlineDebounceTimeout = setTimeout(() => {
            offlineDebounceTimeout = null;
            if (typeof navigator.onLine === 'boolean' && navigator.onLine) {
                // False alarm: network is already back online
                window.isAppOnline = true;
                enforceAllOfflineBadgesHidden();
                return;
            }
            window.isAppOnline = false;
            wasOffline = true;
            showOfflinePill();

            try {
                window.dispatchEvent(new CustomEvent('appOffline', { detail: { timestamp: Date.now() } }));
            } catch(e) {}
        }, 2200);
    };

    let isProbing = false;
    let lastProbeTime = 0;
    async function probeBackendReachability(force = false) {
        const now = Date.now();
        if (isProbing || (!force && now - lastProbeTime < 2000)) return;
        isProbing = true;
        lastProbeTime = now;

        try {
            const bUrl = (window.backendUrl || window.BACKEND_URL || 'https://api.intan-elyu.online').replace(/\/+$/, '');
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 2600);

            let ok = false;
            try {
                const res = await fetch(`${bUrl}/api/public/map?probe=${now}`, {
                    method: 'HEAD',
                    cache: 'no-store',
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal
                });
                if (res && (res.ok || (res.status >= 200 && res.status < 500))) {
                    ok = true;
                }
            } catch (eMap) {
                try {
                    const res2 = await fetch(`${bUrl}/favicon.ico?probe=${now}`, {
                        method: 'HEAD',
                        cache: 'no-store',
                        signal: controller.signal
                    });
                    if (res2 && (res2.ok || (res2.status >= 200 && res2.status < 500))) {
                        ok = true;
                    }
                } catch (eFav) { }
            }
            clearTimeout(timer);

            if (ok) {
                window.isAppOnline = true;
                enforceAllOfflineBadgesHidden();
            }
        } catch (e) {
        } finally {
            isProbing = false;
        }
    }
    window.probeBackendReachability = probeBackendReachability;

    // Periodic watchdog to guarantee no offline text stays stuck when Wi-Fi is on
    setInterval(() => {
        if (typeof navigator.onLine === 'boolean' && navigator.onLine) {
            window.isAppOnline = true;
            enforceAllOfflineBadgesHidden();
        }
    }, 1000);

    // 1. Standard browser network events (fired by OS / WebView)
    window.addEventListener('online', () => {
        window.isAppOnline = true;
        enforceAllOfflineBadgesHidden();
        window.notifyAppOnline();
    });
    window.addEventListener('offline', () => {
        window.notifyAppOffline();
    });
    window.addEventListener('focus', () => {
        if (typeof navigator.onLine === 'boolean' && navigator.onLine) {
            window.isAppOnline = true;
            enforceAllOfflineBadgesHidden();
        }
    });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && typeof navigator.onLine === 'boolean' && navigator.onLine) {
            window.isAppOnline = true;
            enforceAllOfflineBadgesHidden();
        }
    });

    // 2. Capacitor Native Plugins integration (Network & App)
    if (window.Capacitor && window.Capacitor.Plugins) {
        if (window.Capacitor.Plugins.Network && typeof window.Capacitor.Plugins.Network.addListener === 'function') {
            try {
                window.Capacitor.Plugins.Network.addListener('networkStatusChange', (status) => {
                    if (status && status.connected) {
                        window.isAppOnline = true;
                        enforceAllOfflineBadgesHidden();
                        window.notifyAppOnline();
                    } else if (status && status.connected === false) {
                        window.notifyAppOffline();
                    }
                });
            } catch(e) {}
        }
        if (window.Capacitor.Plugins.App && typeof window.Capacitor.Plugins.App.addListener === 'function') {
            try {
                window.Capacitor.Plugins.App.addListener('appStateChange', (state) => {
                    if (state && state.isActive) {
                        if (typeof navigator.onLine === 'boolean' && navigator.onLine) {
                            window.isAppOnline = true;
                            enforceAllOfflineBadgesHidden();
                            window.notifyAppOnline();
                        }
                    }
                });
            } catch(e) {}
        }
    }

    // Pre-warm offline data when online
    function prewarmOfflineData() {
        if (!navigator.onLine) return;
        const bUrl = (window.backendUrl || window.BACKEND_URL || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        
        fetch(`${bUrl}/api/public/map`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(d => {
                if (d && (d.spots || d.data || d.destinations)) {
                    try { localStorage.setItem('intan_elyu_cached_spots_map', JSON.stringify({ data: d, time: Date.now() })); } catch(e) {}
                }
            }).catch(() => {});
    }
})();

window.setTxt = function (id, val) {
    const el = (typeof id === 'string') ? document.getElementById(id) : id;
    if (el) el.textContent = (val !== undefined && val !== null) ? val : '';
};

window.setHtml = function (id, html) {
    const el = (typeof id === 'string') ? document.getElementById(id) : id;
    if (el) el.innerHTML = (html !== undefined && html !== null) ? html : '';
};

/**
 * Format destination visitor counts consistently across the application.
 * If visits < 100, display 'Less than 100 this month'.
 * If visits >= 100, display '{count} visitors this month' or compact format.
 */
window.formatVisitorCount = function (visits, options = {}) {
    const count = parseInt(visits) || 0;
    if (count < 100) {
        return options.compact ? '< 100 this mo.' : 'Less than 100 this month';
    }
    if (options.compact) {
        return `${count.toLocaleString()} visits`;
    }
    return `${count.toLocaleString()} visitors this month`;
};

window.getBackendUrl = function () {
    const isNative = Boolean(
        (typeof window !== 'undefined' && window.Capacitor && typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform()) ||
        (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.includes('IntanElyuAPK')) ||
        (typeof window !== 'undefined' && window.location && (window.location.protocol === 'capacitor:' || window.location.protocol === 'file:'))
    );

    var url = window.backendUrl || window.BACKEND_URL;
    if (url) {
        if (isNative && (url.includes('localhost') || url.includes('127.0.0.1'))) {
            return 'https://api.intan-elyu.online';
        }
        return url.replace(/\/+$/, '');
    }
    if (typeof window !== 'undefined' && window.location) {
        if (isNative) {
            return 'https://api.intan-elyu.online';
        }
        const hn = window.location.hostname;
        const isLocalHost = (hn === 'localhost' || hn === '127.0.0.1' || hn === '::1' ||
            /^192\.168\.\d+\.\d+$/.test(hn) ||
            /^10\.\d+\.\d+\.\d+$/.test(hn) ||
            /^172\.(1[6-9]|2\d|3[01])\.\d+\.\d+$/.test(hn));

        if (isLocalHost) {
            if (window.location.port === '3000') {
                return window.location.protocol + '//' + hn + ':8000';
            }
            if (window.location.pathname.includes('/Intan-Elyu-Tourism-Management-System/')) {
                return window.location.protocol + '//' + window.location.host + '/Intan-Elyu-Tourism-Management-System/backend/public';
            }
            return 'https://api.intan-elyu.online';
        }
        if (hn === 'app.intan-elyu.online') {
            return 'https://api.intan-elyu.online';
        }
        return window.location.origin.replace('app.', 'api.').replace(/\/+$/, '');
    }
    return 'https://api.intan-elyu.online';
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
window.state = state;

/**
 * Execute scripts injected via innerHTML
 */
function executeScripts(container) {
    if (!container) return;
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
window.executeScripts = executeScripts;

/**
 * Navigation Router Function (SPA feel)
 */

// Ensure browser does not try to restore previous scroll position on view transitions
if ('scrollRestoration' in history) {
    try {
        history.scrollRestoration = 'manual';
    } catch (e) { }
}

function resetAppScrollTop() {
    try {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    } catch (e) {
        window.scrollTo(0, 0);
    }
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
    const mc = document.getElementById('main-content');
    if (mc) mc.scrollTop = 0;
    const ac = document.getElementById('app-container');
    if (ac) ac.scrollTop = 0;
    const epc = document.querySelector('.edit-profile-container');
    if (epc) epc.scrollTop = 0;
}
window.resetAppScrollTop = resetAppScrollTop;

/**
 * Enhanced Navigation function that loads views via AJAX
 * @param {string} viewName - Name of the view to load
 * @param {boolean} addToHistory - Whether to push to browser history
 * @param {boolean} fade - Whether to apply the fade transition
 */
async function navigateTo(viewName, addToHistory = true, fade = true) {
    // Prevent overlapping navigations
    if (state.isNavigating) return;

    // Parse pure view name and any query parameters passed in viewName (e.g. 'trip_map&trip_id=123' or 'trip_map?trip_id=123')
    let targetView = viewName;
    const extraParams = new URLSearchParams();

    if (typeof viewName === 'string') {
        const sepIndex = viewName.search(/[?&]/);
        if (sepIndex !== -1) {
            targetView = viewName.substring(0, sepIndex);
            const queryStr = viewName.substring(sepIndex + 1);
            const parsed = new URLSearchParams(queryStr);
            parsed.forEach((val, key) => extraParams.set(key, val));
        }
    }

    // Parse targetView params for Auth mode & source
    if (targetView === 'auth') {
        if (extraParams.get('mode') === 'register' || extraParams.get('tab') === 'register') {
            window._authStartMode = 'register';
        } else if (extraParams.has('mode') || extraParams.has('tab')) {
            window._authStartMode = extraParams.get('mode') || extraParams.get('tab');
        }
        if (extraParams.get('from_sidebar') === '1') {
            window._authFromSidebar = true;
        }
    }

    // Mobile Guest Session Initialization:
    let authToken = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    const isNetworkOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);

    if (!authToken && targetView !== 'auth' && targetView !== 'download') {
        if (isNetworkOffline) {
            authToken = 'offline_guest_token';
            localStorage.setItem('intan_elyu_token', authToken);
            localStorage.setItem('intan_elyu_offline_mode', 'true');
            localStorage.setItem('intan_elyu_offline_guest', 'true');
        } else if (localStorage.getItem('intan_elyu_guest') === 'true') {
            // User explicitly tapped "Continue as Guest" on Auth screen
            authToken = 'guest_token';
            localStorage.setItem('intan_elyu_token', authToken);
        } else {
            // Online and unauthenticated: do NOT auto-create a guest session!
            // Direct to Auth screen where the user can sign in or tap "Continue as Guest"
            targetView = 'auth';
            if (addToHistory) {
                try {
                    window.history.replaceState({ view: 'auth' }, '', 'index.php?view=auth');
                } catch (e) { }
            }
        }
        if (!localStorage.getItem('auth_user') && (isNetworkOffline || localStorage.getItem('intan_elyu_guest') === 'true')) {
            localStorage.setItem('auth_user', JSON.stringify({
                id: isNetworkOffline ? 'offline_guest' : 'guest',
                user_id: isNetworkOffline ? 'offline_guest' : 'guest',
                name: 'Guest',
                full_name: 'Guest Explorer',
                role: 'tourist',
                tourist_number: '',
                points: 0,
                xp: 0,
                completed_activities: 0,
                home_location: 'La Union',
                is_guest: true,
                is_offline_guest: isNetworkOffline
            }));
        }
    }

    // GUEST / OFFLINE PERMISSION RULES:
    // Guests can view Dashboard, Map, Itinerary, Leaderboard, and Profile barrier (to sign in).
    // Direct edit views like edit_profile require a cloud account.
    const isRealAuthToken = Boolean(
        authToken && 
        authToken !== 'guest_token' && 
        authToken !== 'offline_guest_token' && 
        !authToken.startsWith('guest_') && 
        !authToken.startsWith('offline_')
    );
    if (isRealAuthToken) {
        try {
            localStorage.removeItem('intan_elyu_guest');
            localStorage.removeItem('intan_elyu_offline_guest');
        } catch(e) {}
    }
    const isGuestUser = !isRealAuthToken;

    if (isGuestUser && targetView === 'edit_profile') {
        if (typeof showToast === 'function') {
            showToast('Please sign in to customize and edit your profile.', 'info', 4500);
        }
        navigateTo('auth');
        return;
    }

    // If we're already on this view with no extra params and it's not a back-button event, do nothing
    if (addToHistory && state.currentView === targetView && extraParams.toString() === '') return;

    // Immediately reset scroll on navigation start
    resetAppScrollTop();

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
        // In-memory and persistent disk view template cache for instantaneous and offline navigation
        window._viewHtmlCache = window._viewHtmlCache || {};
        let html = (!extraParams.size && window._viewHtmlCache[targetView]) ? window._viewHtmlCache[targetView] : null;

        if (!html && !extraParams.size) {
            try {
                html = localStorage.getItem('elyu_view_cache_v3_' + targetView);
                if (html) {
                    window._viewHtmlCache[targetView] = html;
                }
            } catch (e) {}
        }

        // Always attempt network fetch for fresh view HTML, with resilient timeout
        try {
            let fetchUrl = `index.php?view=${encodeURIComponent(targetView)}&ajax=1&_t=${Date.now()}`;
            extraParams.forEach((val, key) => {
                fetchUrl += `&${encodeURIComponent(key)}=${encodeURIComponent(val)}`;
            });

            const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 7000) : null;

            const response = await fetch(fetchUrl, {
                signal: controller ? controller.signal : undefined,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (timeoutId) clearTimeout(timeoutId);

            if (response.ok) {
                const freshHtml = await response.text();
                if (freshHtml && freshHtml.length > 50) {
                    html = freshHtml;
                    if (!extraParams.size) {
                        window._viewHtmlCache[targetView] = html;
                        try { localStorage.setItem('elyu_view_cache_v3_' + targetView, html); } catch(e) {}
                    }
                }
            }
        } catch (fetchErr) {
            console.warn(`View fetch for ${targetView} failed (using cache if available):`, fetchErr);
            // Fall back to memory or disk cache if network fetch failed
            if (!html) {
                try {
                    html = window._viewHtmlCache[targetView] || localStorage.getItem('elyu_view_cache_v3_' + targetView) || localStorage.getItem('elyu_view_cache_v2_' + targetView);
                } catch(e) {}
            }
        }

        if (!html && targetView === 'auth') {
            html = `<div class="auth-container">
                <div class="auth-top">
                    <div class="logo-container">
                        <img id="auth-logo-img" src="assets/img/logo.png" alt="Intan Elyu Logo" style="width:100%; height:100%; object-fit:contain; border-radius:50%; display:block;">
                    </div>
                    <h1 id="auth-title" style="color:#ffffff; font-weight:800;">Welcome to Elyu</h1>
                    <div class="wave-bottom">
                        <svg viewBox="0 0 2000 100" preserveAspectRatio="none">
                            <path class="wave-layer wave-3" fill="#74a3cf" d="M0,70 C250,120 250,20 500,70 C750,120 750,20 1000,70 C1250,120 1250,20 1500,70 C1750,120 1750,20 2000,70 L2000,160 L0,160 Z"></path>
                        </svg>
                    </div>
                </div>
                <div class="auth-bottom">
                    <div id="auth-offline-badge" class="auth-offline-badge" style="display:none;">
                        <i class="fa-solid fa-cloud-slash" style="color:#f59e0b; font-size:11px;"></i>
                        <span>Offline Mode Active &bull; Saved Guides Available</span>
                    </div>
                    <div class="auth-tabs" id="auth-tabs">
                        <div class="auth-tab active" id="tab-login" onclick="toggleAuthMode(false)">Login</div>
                        <div class="auth-tab" id="tab-register" onclick="toggleAuthMode(true)">Register</div>
                        <div class="tab-gooey-glider" id="tab-gooey-glider"></div>
                    </div>
                    <div class="forms-wrapper" id="forms-wrapper">
                        <div class="form-panel login-form">
                            <form id="form-login" onsubmit="handleLogin(event)">
                                <div class="input-group">
                                    <i class="fa-solid fa-mobile-screen"></i>
                                    <input type="email" id="login-email" class="auth-input" placeholder="Email Address" required>
                                </div>
                                <div class="input-group">
                                    <i class="fa-solid fa-lock"></i>
                                    <input type="password" id="login-password" class="auth-input" placeholder="Password" required>
                                    <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('login-password', this)"></i>
                                </div>
                                <div class="auth-options-row">
                                    <label class="remember-me-label" for="login-remember">
                                        <input type="checkbox" id="login-remember" class="custom-terms-checkbox">
                                        <span>Remember me</span>
                                    </label>
                                    <a href="#" class="forgot-pwd" onclick="showForgotPassword(event)">Forgot Password?</a>
                                </div>
                                <button type="submit" id="btn-login" class="btn-circle-submit">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </form>
                            <div class="auth-social-section" style="margin-top:16px;">
                                <p class="auth-switch-prompt" style="margin-top:10px;">
                                    Don't have an account? <a href="#" onclick="toggleAuthMode(true); return false;" class="auth-switch-link">Register now</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        } else if (!html && targetView === 'splash') {
            html = `<div class="splash-container" id="splash-main">
                <div class="splash-logo-container stagger-1">
                    <div class="splash-logo">
                        <img src="assets/img/logo.png" alt="Intan Elyu Logo">
                    </div>
                </div>
                <div class="splash-title-wrapper stagger-2">
                    <h1 class="splash-title">Intan Elyu</h1>
                    <p class="splash-subtitle">Explore the Province of La Union</p>
                    <div class="splash-offline-pill">
                        <i class="fa-solid fa-cloud-slash" style="color:#f59e0b; font-size:11px;"></i>
                        <span>Offline Mode &bull; Loading saved guides</span>
                    </div>
                </div>
            </div>`;
        }

        if (!html) {
            html = `<div style="padding: 48px 24px; text-align: center; color: #1e3a8a; font-family: -apple-system, sans-serif;">
                <div style="width:68px; height:68px; margin:0 auto 16px; border-radius:50%; background:#e0f2fe; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-cloud-slash" style="font-size:30px; color:#f59e0b;"></i>
                </div>
                <h3 style="margin:0 0 8px; font-weight:800; font-size:18px; color:#0f172a;">Offline Mode Active</h3>
                <p style="margin:0 0 20px; font-size:13px; color:#64748b; line-height:1.5;">This section is being synchronized for offline access. You can explore all saved attractions, itineraries, and maps in Offline Mode.</p>
                <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                    <button onclick="if(typeof navigateTo==='function') navigateTo('dashboard'); else window.history.back();" style="padding:10px 22px; border-radius:100px; border:none; background:#1e3a8a; color:#fff; font-weight:700; font-size:13px; cursor:pointer;">Explore Dashboard</button>
                    <button onclick="if(typeof navigateTo==='function') navigateTo('auth');" style="padding:10px 22px; border-radius:100px; border:1.5px solid #cbd5e1; background:#fff; color:#334155; font-weight:700; font-size:13px; cursor:pointer;">Switch Account</button>
                </div>
            </div>`;
        }

        const updateContent = () => {
            try {
                // Immediately dismiss and remove any lingering sidebar menu or overlay
                const lingeringOverlay = document.getElementById('sidebar-overlay');
                if (lingeringOverlay) {
                    lingeringOverlay.style.display = 'none';
                    lingeringOverlay.style.opacity = '0';
                    lingeringOverlay.style.pointerEvents = 'none';
                    try { lingeringOverlay.remove(); } catch (e) { }
                }
                const lingeringSidebar = document.getElementById('sidebar-menu');
                if (lingeringSidebar) {
                    lingeringSidebar.style.left = '-280px';
                    try { lingeringSidebar.remove(); } catch (e) { }
                }

                // ── Teardown previous view resources to prevent mobile lag & leaks ──
                ['full-history-modal', 'full-vouchers-modal', 'full-deals-modal', 'active-voucher-qr-modal', 'trip-details-modal', 'reward-details-modal', 'delete-trip-modal', 'elyu-proof-viewer-modal', 'global-oauth-overlay', 'offline-auth-modal', 'login-success-modal', 'auth-cancel-modal', 'privacy-policy-modal'].forEach(id => {
                    document.querySelectorAll('#' + id).forEach(m => {
                        if (m.parentElement === document.body) {
                            try { m.remove(); } catch (e) { }
                        } else {
                            m.style.display = 'none';
                            m.style.pointerEvents = 'none';
                            m.classList.remove('active');
                        }
                    });
                });
                if (window._savedTripsInterval) {
                    clearInterval(window._savedTripsInterval);
                    window._savedTripsInterval = null;
                }
                if (window._mapSpotsCheckInterval) {
                    clearInterval(window._mapSpotsCheckInterval);
                    window._mapSpotsCheckInterval = null;
                }
                if (window._mapWeatherTickInterval) {
                    clearInterval(window._mapWeatherTickInterval);
                    window._mapWeatherTickInterval = null;
                }
                if (window._mapMarineSyncInterval) {
                    clearInterval(window._mapMarineSyncInterval);
                    window._mapMarineSyncInterval = null;
                }
                if (window._gpsDebounceTimer) {
                    clearTimeout(window._gpsDebounceTimer);
                    window._gpsDebounceTimer = null;
                }
                if (targetView !== 'map' && window.mapInstance) {
                    try {
                        if (window.mountedMarkersMap) {
                            window.mountedMarkersMap.forEach(m => {
                                try { m.remove(); } catch (e) { }
                            });
                            window.mountedMarkersMap.clear();
                        }
                        if (window.userMarker) {
                            try { window.userMarker.remove(); } catch (e) { }
                            window.userMarker = null;
                        }
                        if (window._mapGpsHandler) {
                            document.removeEventListener('gpsUpdated', window._mapGpsHandler);
                            window._mapGpsHandler = null;
                        }
                        window.mapInstance.remove();
                    } catch (e) { }
                    window.mapInstance = null;
                }

                resetAppScrollTop();
                mainContent.innerHTML = html;
                document.body.setAttribute('data-view', targetView);
                resetAppScrollTop();

                // Execute any scripts in the new view
                executeScripts(mainContent);

                // Toggle bottom nav visibility
                const bottomNav = document.getElementById('bottom-navigation');
                const noNavViews = ['splash', 'auth', 'about', 'terms', 'edit_profile', 'help', 'trip_map', 'saved_trips', 'saved_places', 'trending', 'reset-password', 'puzzles', 'discount', 'settings', 'user_manual'];
                if (bottomNav) {
                    bottomNav.classList.toggle('nav-hidden', noNavViews.includes(targetView));
                }

                // Animate in
                if (fade) {
                    mainContent.classList.remove('view-transitioning');
                }

                // Update URL & Track View Navigation History
                if (addToHistory) {
                    if (state.currentView && state.currentView !== targetView && state.currentView !== 'splash') {
                        window._navHistory = window._navHistory || [];
                        if (window._navHistory[window._navHistory.length - 1] !== state.currentView) {
                            window._navHistory.push(state.currentView);
                            if (window._navHistory.length > 30) {
                                window._navHistory.shift();
                            }
                        }
                    }
                    const url = new URL(window.location);
                    url.searchParams.set('view', targetView);
                    extraParams.forEach((val, key) => {
                        url.searchParams.set(key, val);
                    });
                    window.history.pushState({ view: targetView }, '', url);
                }

                state.currentView = targetView;
                if (typeof initCurrentView === 'function') initCurrentView();

                // Dispatch viewLoaded event for view auto-refresh and lifecycle handlers
                window.dispatchEvent(new CustomEvent('viewLoaded', { detail: { view: targetView } }));

                if (targetView === 'profile' && typeof window.updateProfileAccessBarrier === 'function') {
                    window.updateProfileAccessBarrier();
                }
                if (targetView === 'auth' && typeof window.initAuthView === 'function') {
                    window.initAuthView();
                }
                if (targetView === 'dashboard' && typeof window.updateDashboardProfileHeader === 'function') {
                    const _curTok = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                    const _isG = !_curTok || _curTok === 'guest_token' || _curTok === 'offline_guest_token' || _curTok.indexOf('guest_') === 0 || _curTok.indexOf('offline_') === 0 || localStorage.getItem('intan_elyu_guest') === 'true' || localStorage.getItem('intan_elyu_offline_guest') === 'true';
                    const _curU = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('auth_user'), {}) : {};
                    window.updateDashboardProfileHeader(_isG, _curU);
                }

                // Post-render scroll resets on next animation frames and timeouts to guarantee top placement
                resetAppScrollTop();
                requestAnimationFrame(resetAppScrollTop);
                setTimeout(resetAppScrollTop, 50);
                setTimeout(resetAppScrollTop, 150);
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
        if (typeof showToast === 'function') showToast('Failed to load view');
        if (fade && mainContent) mainContent.classList.remove('view-transitioning');
        state.isNavigating = false;
    }
    window.navigateTo = navigateTo;

    /**
     * Universal App Exit Confirmation Modal
     * Displays a clean, premium dialog: "Are you sure you want to exit?" with Yes and No buttons.
     */
    window.ensureAppExitModal = function () {
        let modal = document.getElementById('app-exit-confirm-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'app-exit-confirm-modal';
            modal.style.cssText = 'display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(10,25,60,0.72); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:9999999; align-items:center; justify-content:center; padding:20px; box-sizing:border-box;';
            modal.onclick = function (e) {
                if (e.target === modal) window.closeAppExitModal();
            };
            modal.innerHTML = `
                <div style="background:#ffffff; border-radius:24px; width:100%; max-width:330px; padding:26px 22px 22px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.3); position:relative; animation:popInAppModal 0.22s cubic-bezier(0.16,1,0.3,1) forwards;">
                    <div style="width:58px; height:58px; border-radius:50%; background:#fee2e2; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px; box-shadow:0 4px 14px rgba(239,68,68,0.2);">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </div>
                    <h3 style="margin:0 0 8px 0; font-size:18px; font-weight:800; color:#0f172a; line-height:1.3; letter-spacing:-0.2px;">
                        Are you sure you want to exit?
                    </h3>
                    <p style="margin:0 0 22px 0; font-size:13px; color:#64748b; line-height:1.45;">
                        You will exit the Intan Elyu application.
                    </p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <button type="button" id="app-exit-btn-no" onclick="window.closeAppExitModal()"
                            style="padding:13px 16px; border-radius:14px; background:#f1f5f9; border:1.5px solid #e2e8f0; color:#475569; font-size:14px; font-weight:800; cursor:pointer; transition:transform 0.15s ease;"
                            onpointerdown="this.style.transform='scale(0.96)'" onpointerup="this.style.transform='scale(1)'">
                            No
                        </button>
                        <button type="button" id="app-exit-btn-yes" onclick="window.confirmExecuteAppExit()"
                            style="padding:13px 16px; border-radius:14px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border:none; color:#ffffff; font-size:14px; font-weight:800; cursor:pointer; box-shadow:0 4px 14px rgba(220,38,38,0.35); transition:transform 0.15s ease;"
                            onpointerdown="this.style.transform='scale(0.96)'" onpointerup="this.style.transform='scale(1)'">
                            Yes
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            if (!document.getElementById('app-exit-modal-styles')) {
                const style = document.createElement('style');
                style.id = 'app-exit-modal-styles';
                style.textContent = `
                    @keyframes popInAppModal {
                        0% { opacity: 0; transform: scale(0.92); }
                        100% { opacity: 1; transform: scale(1); }
                    }
                `;
                document.head.appendChild(style);
            }
        }
        return modal;
    };

    window.showAppExitModal = function () {
        const modal = window.ensureAppExitModal();
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeAppExitModal = function () {
        const modal = document.getElementById('app-exit-confirm-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.confirmExecuteAppExit = function () {
        window.closeAppExitModal();
        try {
            if (window.IntanElyuNative && typeof window.IntanElyuNative.exitApp === 'function') {
                window.IntanElyuNative.exitApp();
                return;
            }
        } catch (e) {}

        try {
            if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.App && typeof window.Capacitor.Plugins.App.exitApp === 'function') {
                window.Capacitor.Plugins.App.exitApp();
                return;
            }
        } catch (e) {}

        try {
            if (navigator && navigator.app && typeof navigator.app.exitApp === 'function') {
                navigator.app.exitApp();
                return;
            }
        } catch (e) {}

        try {
            window.close();
        } catch (e) {}
    };

    /**
     * Universal Mobile Back Button & Navigation Handler
     * Handles hardware back button, Android gesture swipe, header back button, and popstate.
     * Closes open modals/drawers first; if none, navigates to previous view or natural parent;
     * if on root dashboard, prompts the user with an exit confirmation modal (Yes/No).
     * @returns {boolean} True if back action was handled, false if app should exit.
     */
    window.handleAppBack = function (explicitFallback) {
        // 0. If App Exit Modal is currently open, dismiss it
        const exitModal = document.getElementById('app-exit-confirm-modal');
        if (exitModal && exitModal.style.display !== 'none' && window.getComputedStyle(exitModal).display !== 'none') {
            window.closeAppExitModal();
            return true;
        }

        // 1. Check Sidebar & Notifications in header
        const sidebar = document.getElementById('sidebar-menu');
        if (sidebar && (sidebar.style.left === '0px' || window.getComputedStyle(sidebar).left === '0px')) {
            if (typeof toggleSidebar === 'function') toggleSidebar();
            return true;
        }

        const notifDropdown = document.getElementById('notifications-dropdown');
        if (notifDropdown && (notifDropdown.style.opacity === '1' || window.getComputedStyle(notifDropdown).opacity === '1')) {
            if (typeof toggleNotifications === 'function') toggleNotifications();
            return true;
        }

        // 2. Check Map Sheets & Overlays
        const destSheet = document.getElementById('destination-sheet');
        if (destSheet && destSheet.classList.contains('open')) {
            if (typeof window.closeSheet === 'function') window.closeSheet();
            else destSheet.classList.remove('open');
            return true;
        }

        const nearbySheet = document.getElementById('nearby-sites-sheet');
        if (nearbySheet && nearbySheet.classList.contains('open')) {
            if (typeof window.closeNearbySitesSheet === 'function') window.closeNearbySitesSheet();
            else nearbySheet.classList.remove('open');
            return true;
        }

        // 3. Check Known Modals by ID
        const knownModalClosers = [
            { id: 'spot-vehicles-modal', fn: window.closeSpotVehiclesModal },
            { id: 'itin-add-confirm', fn: window.closeAddConfirm },
            { id: 'leg-transport-modal', fn: window.closeLegTransportModal },
            { id: 'transit-summary-opensheet', fn: window.closeTransitSummaryOpensheet },
            { id: 'travel-mode-starter-modal', fn: window.closeTravelModeStarterModal },
            { id: 'save-trip-modal', fn: window.closeSaveModal },
            { id: 'checkin-modal', fn: window.closeCheckinModal },
            { id: 'checkin-image-picker-modal', fn: window.closeCheckinImagePickerModal },
            { id: 'complete-trip-modal', fn: window.closeCompleteTripModal },
            { id: 'delete-trip-modal', fn: window.closeDeleteTripModal },
            { id: 'trip-completion-review-modal', fn: window.closeTripCompletionReviewModal },
            { id: 'user-profile-modal', fn: window.closeUserProfile },
            { id: 'full-deals-modal', fn: window.closeFullDealsModal },
            { id: 'full-history-modal', fn: window.closeFullHistoryModal },
            { id: 'full-vouchers-modal', fn: window.closeFullVouchersModal },
            { id: 'reward-details-modal', fn: window.closeRewardDetailsModal },
            { id: 'active-voucher-qr-modal', fn: window.closeActiveVoucherQrModal },
            { id: 'trip-details-modal', fn: window.closeTripDetailsModal },
            { id: 'voucher-modal', fn: window.closeVoucherModal },
            { id: 'logout-confirm-modal', fn: window.closeLogoutModal },
            { id: 'push-notification-modal', fn: window.closePushNotificationModal }
        ];

        for (const item of knownModalClosers) {
            const el = document.getElementById(item.id);
            if (el) {
                const style = window.getComputedStyle(el);
                if (style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0') {
                    if (typeof item.fn === 'function') {
                        item.fn();
                    } else {
                        el.style.display = 'none';
                        el.classList.remove('active', 'open', 'show');
                    }
                    return true;
                }
            }
        }

        // 4. Check Generic Open Modals & Dialogs (ignoring #app-exit-confirm-modal)
        const openModals = Array.from(document.querySelectorAll(
            '.modal, .profile-modal-overlay, .modal-backdrop, .dialog-overlay, [role="dialog"], #change-password-modal, #two-factor-modal, #game-confirm-modal, #game-success-modal, #game-alert-modal'
        )).filter(el => {
            if (el.id === 'app-exit-confirm-modal') return false;
            const style = window.getComputedStyle(el);
            return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
        });

        if (openModals.length > 0) {
            const topModal = openModals[openModals.length - 1];
            topModal.style.display = 'none';
            topModal.classList.remove('active', 'open', 'show');
            return true;
        }

        // 5. Navigate back to previous view or explicit fallback
        const currentView = (state && state.currentView) || new URLSearchParams(window.location.search).get('view') || 'dashboard';

        // Explicit fallback provided by header back button (e.g. $backRoute)
        if (explicitFallback && explicitFallback !== currentView) {
            navigateTo(explicitFallback, false);
            return true;
        }

        // Navigation history stack check
        window._navHistory = window._navHistory || [];
        while (window._navHistory.length > 0) {
            const prevView = window._navHistory.pop();
            if (prevView && prevView !== currentView && prevView !== 'splash') {
                navigateTo(prevView, false);
                return true;
            }
        }

        // View hierarchy parent fallback
        const parentMap = {
            'trip_map': 'saved_trips',
            'saved_trips': 'itinerary',
            'saved_places': 'dashboard',
            'trending': 'dashboard',
            'settings': 'dashboard',
            'user_manual': 'settings',
            'help': 'dashboard',
            'about': 'dashboard',
            'terms': 'dashboard',
            'edit_profile': 'profile',
            'discount': 'profile',
            'puzzles': 'dashboard',
            'explore': 'dashboard',
            'map': 'dashboard',
            'itinerary': 'dashboard',
            'leaderboard': 'dashboard',
            'profile': 'dashboard',
            'reset-password': 'auth'
        };

        if (parentMap[currentView] && parentMap[currentView] !== currentView) {
            navigateTo(parentMap[currentView], false);
            return true;
        }

        if (currentView !== 'dashboard' && currentView !== 'auth' && currentView !== 'splash') {
            navigateTo('dashboard', false);
            return true;
        }

        // 6. User is on Root Screen ('dashboard' or 'auth') -> Prompt with Exit Confirmation Modal
        window.showAppExitModal();
        return true;
    };

    window.navigateBack = function (fallbackRoute = 'dashboard') {
        return window.handleAppBack(fallbackRoute);
    };

    /**
     * Global Google OAuth 2.0 Direct Handler
     * Handles access_token / id_token returned via Google OAuth redirect across all views & platforms (APK / Web)
     */
    window._isProcessingGoogleOAuth = false;

    window.initGoogleOAuthHandler = function (customUrl) {
        let rawHash = window._googleOAuthHash || window.location.hash || '';
        let rawSearch = window._googleOAuthSearch || window.location.search || '';

        if (customUrl) {
            try {
                const parsed = new URL(customUrl);
                rawHash = parsed.hash || rawHash;
                rawSearch = parsed.search || rawSearch;
            } catch (e) {
                if (customUrl.includes('#')) rawHash = '#' + customUrl.split('#')[1];
                if (customUrl.includes('?')) rawSearch = '?' + customUrl.split('?')[1];
            }
        }

        // 1. Check for direct token handoff (?token=...&user=...) from mobile browser to APK
        const searchParams = new URLSearchParams(rawSearch.startsWith('?') ? rawSearch.substring(1) : rawSearch);
        const directToken = searchParams.get('token');
        const directUser = searchParams.get('user');

        if (directToken) {
            // Prevent duplicate parallel or repeated executions with the same token
            if (window._handledDirectToken === directToken || window._googleOAuthActiveProcessing) {
                return;
            }
            window._handledDirectToken = directToken;
            window._googleOAuthActiveProcessing = true;
            window._isProcessingGoogleOAuth = true;

            // Auto-close Capacitor Browser if returning from OAuth
            if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Browser && typeof window.Capacitor.Plugins.Browser.close === 'function') {
                window.Capacitor.Plugins.Browser.close().catch(function () {});
            }

            // Immediately hide and suppress splash screen elements if still in DOM
            const splashMain = document.getElementById('splash-main');
            if (splashMain) {
                splashMain.style.display = 'none';
                try { splashMain.remove(); } catch (e) { }
            }

            localStorage.setItem('intan_elyu_token', directToken);
            if (directUser) {
                try {
                    let userStr = directUser;
                    if (typeof userStr === 'string' && (userStr.startsWith('%') || userStr.includes('%22') || userStr.includes('%7B'))) {
                        try { userStr = decodeURIComponent(userStr); } catch (eDec) {}
                    }
                    const parsedUser = typeof userStr === 'object' ? userStr : JSON.parse(userStr);
                    localStorage.setItem('auth_user', JSON.stringify(parsedUser));
                    if (window.AppStorage) window.AppStorage.setItem('auth_user', parsedUser);
                } catch (e) { }
            }
            if (window.AppStorage) window.AppStorage.setItem('intan_elyu_token', directToken);

            // Clear offline flags on real authenticated login
            localStorage.removeItem('intan_elyu_offline_mode');
            localStorage.removeItem('intan_elyu_offline_guest');
            try {
                sessionStorage.removeItem('intan_elyu_google_oauth_pending');
                sessionStorage.removeItem('elyu_show_offline_bypass_notice');
            } catch (e) { }

            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname + '?view=dashboard');
            }
            if (typeof showToast === 'function') showToast('Logged in successfully!', 'success');
            navigateTo('dashboard', true, true);
            return;
        }

        const hasAccessToken = rawHash.includes('access_token=') || rawSearch.includes('access_token=');
        const hasIdToken = rawHash.includes('id_token=') || rawSearch.includes('id_token=');
        const hasError = rawHash.includes('error=') || rawSearch.includes('error=');

        if (!hasAccessToken && !hasIdToken && !hasError) {
            return;
        }

        if (window._googleOAuthActiveProcessing && !customUrl) return;
        window._googleOAuthActiveProcessing = true;
        window._isProcessingGoogleOAuth = true;

        // Auto-close Capacitor Browser if returning from OAuth
        if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Browser && typeof window.Capacitor.Plugins.Browser.close === 'function') {
            window.Capacitor.Plugins.Browser.close().catch(function () {});
        }

        // Immediately hide and suppress splash screen elements if still in DOM
        const splashMain = document.getElementById('splash-main');
        if (splashMain) {
            splashMain.style.display = 'none';
            try { splashMain.remove(); } catch (e) { }
        }

        const rawParams = rawHash.startsWith('#') ? rawHash.substring(1) : (rawSearch.startsWith('?') ? rawSearch.substring(1) : (rawHash || rawSearch));
        const params = new URLSearchParams(rawParams);

        // Clean hash from URL so the raw access token is not retained in browser history
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname + '?view=auth');
        }

        if (hasError) {
            window._isProcessingGoogleOAuth = false;
            try { sessionStorage.removeItem('intan_elyu_google_oauth_pending'); } catch (e) { }
            const errorDesc = params.get('error_description') || params.get('error') || 'Google sign-in was cancelled.';
            console.warn('Google OAuth returned error:', errorDesc);
            if (typeof showToast === 'function') showToast(errorDesc, 'error');
            navigateTo('auth', true, false);
            return;
        }

        const accessToken = params.get('access_token');
        const idToken = params.get('id_token');

        if (!accessToken && !idToken) {
            window._isProcessingGoogleOAuth = false;
            try { sessionStorage.removeItem('intan_elyu_google_oauth_pending'); } catch (e) { }
            return;
        }

        // Display global full-screen OAuth loader styled with the 3 colors (#00f2fe, #38bdf8, #74a3cf)
        const showOverlay = (title = 'Logging in with Google...', subtitle = 'Authenticating your account, please wait') => {
            let overlay = document.getElementById('global-oauth-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'global-oauth-overlay';
                if (document.body) {
                    document.body.appendChild(overlay);
                } else {
                    document.addEventListener('DOMContentLoaded', () => {
                        if (!document.getElementById('global-oauth-overlay') && document.body) {
                            document.body.appendChild(overlay);
                        }
                    });
                }
            }
            overlay.innerHTML = `
            <div class="oauth-modal-content" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; box-shadow: 0 15px 35px rgba(10, 25, 60, 0.4), 0 0 25px rgba(63, 125, 183, 0.25) !important;">
                <div class="oauth-spinner-circle">
                    <div class="oauth-spinner-ring"></div>
                </div>
                <h3 class="oauth-modal-title">${title}</h3>
                <p class="oauth-modal-subtitle">${subtitle}</p>
            </div>
        `;
            return overlay;
        };

        window.showGoogleOAuthModal = showOverlay;
        window.hideGoogleOAuthModal = () => {
            const ov = document.getElementById('global-oauth-overlay');
            if (ov) {
                ov.style.transition = 'opacity 0.3s ease';
                ov.style.opacity = '0';
                setTimeout(() => ov.remove(), 300);
            }
        };

        const overlay = showOverlay();

        // Retrieve profile info using OAuth access token
        const fetchProfile = accessToken
            ? fetch('https://www.googleapis.com/oauth2/v3/userinfo', { headers: { Authorization: `Bearer ${accessToken}` } }).then(r => r.json())
            : Promise.resolve(null);

        fetchProfile
            .then(profile => {
                let payload = {};
                if (profile && profile.email) {
                    payload = {
                        email: profile.email,
                        name: profile.name || (profile.given_name + ' ' + (profile.family_name || '')).trim(),
                        google_id: 'g_' + profile.sub,
                        avatar: profile.picture
                    };
                } else if (idToken) {
                    payload = { credential: idToken };
                } else {
                    throw new Error('Unable to retrieve profile from Google.');
                }

                const backend = (typeof window.getBackendUrl === 'function') ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online');
                return fetch(backend + '/api/auth/google', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
            })
            .then(async res => {
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Non-JSON response from Google auth endpoint:', text.substring(0, 200));
                    throw new Error('Invalid response from server. Please try again.');
                }
                if (!res.ok) throw new Error(data.error || data.message || 'Google authentication failed.');

                localStorage.setItem('auth_user', JSON.stringify(data.user));
                localStorage.setItem('intan_elyu_token', data.token);
                if (window.AppStorage) {
                    window.AppStorage.setItem('auth_user', data.user);
                    window.AppStorage.setItem('intan_elyu_token', data.token);
                }

                // Clear offline flags on real authenticated login
                localStorage.removeItem('intan_elyu_offline_mode');
                localStorage.removeItem('intan_elyu_offline_guest');
                try {
                    sessionStorage.removeItem('intan_elyu_google_oauth_pending');
                    sessionStorage.removeItem('elyu_show_offline_bypass_notice');
                } catch (e) { }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname + '?view=dashboard');
                }

                const currentOverlay = document.getElementById('global-oauth-overlay');
                if (currentOverlay) {
                    currentOverlay.innerHTML = `
                    <div class="oauth-modal-content" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; box-shadow: 0 15px 35px rgba(10, 25, 60, 0.4), 0 0 25px rgba(63, 125, 183, 0.25) !important;">
                        <div class="oauth-success-circle">
                            <i class="fa-solid fa-check" style="font-size:28px; color:#1e3a8a;"></i>
                        </div>
                        <h3 class="oauth-modal-title">Welcome${data.user?.name ? ', ' + data.user.name : ''}!</h3>
                        <p class="oauth-modal-subtitle">Redirecting to dashboard...</p>
                    </div>
                `;
                }

                const stateRaw = params.get('state');
                let stateObj = null;
                try {
                    stateObj = JSON.parse(decodeURIComponent(stateRaw));
                } catch (e) {
                    try { stateObj = JSON.parse(stateRaw); } catch (e2) {}
                }

                const isNativePlatform = Boolean(
                    (window.Capacitor && typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform()) ||
                    (navigator.userAgent && navigator.userAgent.includes('IntanElyuAPK'))
                );

                const shouldHandoffToApp = !isNativePlatform && (
                    Boolean(stateObj && stateObj.fromApp) ||
                    /Android/i.test(navigator.userAgent)
                );

                if (shouldHandoffToApp) {
                    const deepLink = `intanelyu://auth?app=true&token=${encodeURIComponent(data.token)}&user=${encodeURIComponent(JSON.stringify(data.user))}`;
                    
                    const currentOverlay = document.getElementById('global-oauth-overlay');
                    if (currentOverlay) {
                        currentOverlay.innerHTML = `
                        <div class="oauth-modal-content" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; box-shadow: 0 15px 35px rgba(10, 25, 60, 0.4), 0 0 25px rgba(63, 125, 183, 0.25) !important;">
                            <div class="oauth-success-circle">
                                <i class="fa-solid fa-check" style="font-size:28px; color:#1e3a8a;"></i>
                            </div>
                            <h3 class="oauth-modal-title">Welcome, ${data.user?.name || 'Explorer'}!</h3>
                            <p class="oauth-modal-subtitle">Returning to the Intan Elyu app...</p>
                            <a href="${deepLink}" class="dash-empty-btn" style="margin-top: 14px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #ffffff !important; color: #1e3a8a !important; font-weight: 800; border-radius: 100px; padding: 12px 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.2);">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open App
                            </a>
                        </div>
                        `;
                    }
                    if (!window._deepLinkDispatched) {
                        window._deepLinkDispatched = true;
                        window.location.href = deepLink;
                    }
                    return;
                }

                setTimeout(() => {
                    const ov = document.getElementById('global-oauth-overlay');
                    if (ov) {
                        ov.style.transition = 'opacity 0.3s ease';
                        ov.style.opacity = '0';
                        setTimeout(() => ov.remove(), 300);
                    }
                    window._isProcessingGoogleOAuth = false;
                    if (typeof showToast === 'function') {
                        showToast(`Welcome back, ${data.user?.name || 'Explorer'}!`, 'success');
                    }
                    navigateTo('dashboard', true, true);
                }, 1000);
            })
            .catch(err => {
                console.error('Google OAuth Handshake Error:', err);
                window._isProcessingGoogleOAuth = false;
                try { sessionStorage.removeItem('intan_elyu_google_oauth_pending'); } catch (e) { }
                const ov = document.getElementById('global-oauth-overlay');
                if (ov) ov.remove();
                if (typeof showToast === 'function') showToast(err.message || 'Google sign-in failed.', 'error');
                navigateTo('auth', true, false);
            });
    };

    // Check for Google OAuth token immediately on script execution
    window.initGoogleOAuthHandler();

    // Listen to hash changes in case WebView or browser updates hash in place
    window.addEventListener('hashchange', function () {
        if (window.location.hash && (window.location.hash.includes('access_token=') || window.location.hash.includes('id_token='))) {
            window.initGoogleOAuthHandler();
        }
    });

    // Listen to Capacitor App url open events
    if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.App) {
        window.Capacitor.Plugins.App.addListener('appUrlOpen', function (data) {
            if (data && data.url) {
                if (window.Capacitor.Plugins.Browser && typeof window.Capacitor.Plugins.Browser.close === 'function') {
                    window.Capacitor.Plugins.Browser.close().catch(function () {});
                }
                if (typeof window.initGoogleOAuthHandler === 'function') {
                    window.initGoogleOAuthHandler(data.url);
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Check again once DOM is ready if not already handled
        window.initGoogleOAuthHandler();

        // Auto-invalidate stale caches from previous builds
        const CACHE_VER = 'v1.0.6_state_order';
        if (localStorage.getItem('intan_elyu_cache_ver') !== CACHE_VER) {
            Object.keys(localStorage).forEach(k => {
                if (k.startsWith('dashboard_') || k.startsWith('trending_') || k.startsWith('map_') || k.startsWith('spots_') || k.startsWith('destinations_') || k.includes('cache')) {
                    localStorage.removeItem(k);
                }
            });
            localStorage.setItem('intan_elyu_cache_ver', CACHE_VER);
        }

        // Global Auth Enforcement for Initial Direct Load (skip if OAuth handshake in progress)
        if (!window._isProcessingGoogleOAuth) {
            const publicViews = ['splash', 'auth', 'download', 'reset-password'];
            if (!publicViews.includes(state.currentView) && !localStorage.getItem('intan_elyu_token')) {
                navigateTo('auth');
                return;
            }
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
        if (typeof initCurrentView === 'function') initCurrentView();
    });

    // Handle Browser Back Button & Android System Back via Universal Handler
    window.addEventListener('popstate', (e) => {
        if (typeof window.handleAppBack === 'function') {
            window.handleAppBack();
        }
    });

    // Hook into Capacitor Android hardware back button
    if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.App && typeof window.Capacitor.Plugins.App.addListener === 'function') {
        try {
            window.Capacitor.Plugins.App.addListener('backButton', () => {
                if (typeof window.handleAppBack === 'function') {
                    window.handleAppBack();
                } else if (typeof window.navigateBack === 'function') {
                    window.navigateBack('dashboard');
                }
            });
        } catch(e) {}
    }

    /**
     * Toast Notification System
     */
    window.showToast = function showToast(message, type = 'info', duration = 3200) {
        if (typeof type === 'number') {
            duration = type;
            type = 'info';
        }

        let msgStr = String(message || '').trim();
        if (!msgStr) return;

        // Universal Network / Fetch Interceptor: Replace raw browser errors with user-friendly notices
        const lowerMsg = msgStr.toLowerCase();
        if (lowerMsg.includes('failed to fetch') || lowerMsg.includes('networkerror') || lowerMsg.includes('load failed') || lowerMsg.includes('network request failed')) {
            const isAuth = !!document.getElementById('login-form') || !!document.getElementById('btn-login') || (typeof window !== 'undefined' && window.location && window.location.pathname.includes('auth'));
            if (isAuth) {
                msgStr = 'Internet connection is required to log in. Please connect to the internet.';
            } else {
                msgStr = 'Internet connection is required. Please check your network connection and try again.';
            }
            type = 'warning';
            duration = Math.max(duration, 4500);
        }

        // Deduplication & Debounce: Prevent duplicate identical toasts within 2.5 seconds
        const now = Date.now();
        if (window._lastToastMsg === msgStr && (now - (window._lastToastTime || 0)) < 2500) {
            return;
        }
        window._lastToastMsg = msgStr;
        window._lastToastTime = now;

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        } else {
            // Cap visible toasts to max 2 to prevent screen cluttering
            while (container.children.length >= 2) {
                container.firstElementChild.remove();
            }
        }

        // Determine icon and color variant
        let iconHTML = '<i class="fa-solid fa-circle-info" style="color:#38bdf8; font-size:16px;"></i>';
        let borderColor = 'rgba(56, 189, 248, 0.35)';

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
        toast.innerHTML = `<div style="display:flex; align-items:center; gap:10px;">${iconHTML}<span style="font-size:13px; font-weight:700; color:#ffffff; line-height:1.3;">${msgStr}</span></div>`;

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
     * Universal Cross-Browser / Mobile Clipboard Copy Helper
     * Handles HTTPS, HTTP (LAN/XAMPP IP), WebViews, iOS/Android, and Desktop
     */
    window.copyToClipboard = function (text, onSuccess, onError) {
        if (!text) {
            if (typeof onError === 'function') onError(new Error('No text provided to copy'));
            return;
        }

        const trimmed = String(text).trim();

        // 1. Try modern Async Clipboard API if supported and in secure context
        if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(trimmed).then(() => {
                if (typeof onSuccess === 'function') onSuccess(trimmed);
            }).catch((err) => {
                console.warn('Clipboard writeText failed, trying execCommand fallback:', err);
                fallbackExecCopy(trimmed, onSuccess, onError);
            });
            return;
        }

        // 2. Fallback using document.execCommand('copy')
        fallbackExecCopy(trimmed, onSuccess, onError);
    };

    function fallbackExecCopy(text, onSuccess, onError) {
        let ta = null;
        try {
            ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.top = '0';
            ta.style.left = '-9999px';
            ta.style.width = '2em';
            ta.style.height = '2em';
            ta.style.padding = '0';
            ta.style.border = 'none';
            ta.style.outline = 'none';
            ta.style.boxShadow = 'none';
            ta.style.background = 'transparent';
            ta.style.opacity = '0.01';
            ta.style.zIndex = '-9999';
            ta.style.pointerEvents = 'none';

            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            ta.setSelectionRange(0, ta.value.length);

            const successful = document.execCommand('copy');
            document.body.removeChild(ta);
            ta = null;

            if (successful) {
                if (typeof onSuccess === 'function') onSuccess(text);
            } else {
                throw new Error('execCommand returned false');
            }
        } catch (e) {
            if (ta && ta.parentNode) {
                ta.parentNode.removeChild(ta);
            }
            console.warn('Fallback execCommand copy error:', e);
            if (typeof onError === 'function') {
                onError(e);
            } else if (typeof showToast === 'function') {
                showToast('Unable to copy automatically. Code: ' + text);
            }
        }
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
     * Universal Logout Confirmation Modal Handlers
     */
    window.closeLogoutModal = function () {
        const modal = document.getElementById('logout-confirm-modal');
        if (modal) modal.style.display = 'none';
    };

    window.showLogoutConfirmationModal = function () {
        const modal = document.getElementById('logout-confirm-modal');
        if (modal) {
            const hasPending = (function () {
                try {
                    const c1 = JSON.parse(localStorage.getItem('intan_elyu_offline_checkins_queue') || '[]').length;
                    const c2 = JSON.parse(localStorage.getItem('intan_elyu_offline_trips_queue') || '[]').length;
                    const c3 = JSON.parse(localStorage.getItem('intan_elyu_offline_completed_trips_queue') || '[]').length;
                    const c4 = JSON.parse(localStorage.getItem('intan_elyu_offline_favorites_queue') || '[]').length;
                    const c5 = JSON.parse(localStorage.getItem('intan_elyu_offline_game_claims_queue') || '[]').length;
                    const c6 = JSON.parse(localStorage.getItem('intan_elyu_offline_profile_queue') || '[]').length;
                    return (c1 + c2 + c3 + c4 + c5 + c6);
                } catch (e) { return 0; }
            })();

            const subtext = document.getElementById('logout-modal-subtext');
            if (subtext) {
                if (hasPending > 0) {
                    subtext.innerHTML = `<span style="color: #b45309; font-weight: 700;">⚠️ You have ${hasPending} unsynced offline item(s).</span><br>Logging out will clear this session and unsynced items.`;
                } else {
                    subtext.textContent = 'You will be signed out of your tourist explorer session.';
                }
            }

            modal.style.display = 'flex';
        } else {
            if (confirm("Are you sure you want to log out?")) {
                window.confirmExecuteLogout();
            }
        }
    };

    window.confirmExecuteLogout = async function () {
        window.closeLogoutModal();
        if (typeof showToast === 'function') showToast('Logging out...', 1000);

        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            if (token) {
                const backend = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
                await fetch(backend + '/api/auth/logout', {
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

        try {
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (!k) continue;
                if (
                    k.startsWith('intan_elyu_claimed_vouchers') ||
                    k.startsWith('intan_elyu_cached_vouchers') ||
                    k.startsWith('profile_data_') ||
                    k.startsWith('dashboard_data_') ||
                    k.startsWith('dashboard_trips_') ||
                    k.startsWith('saved_trips_') ||
                    k.startsWith('saved_places_') ||
                    k.startsWith('leaderboard_data_') ||
                    k.startsWith('intan_cache_') ||
                    k === 'intan_elyu_token' ||
                    k === 'Intan_Elyu_Token' ||
                    k === 'auth_user' ||
                    k === 'intan_elyu_guest' ||
                    k === 'intan_elyu_offline_guest' ||
                    k === 'intan_elyu_offline_mode'
                ) {
                    keysToRemove.push(k);
                }
            }
            keysToRemove.forEach(k => localStorage.removeItem(k));
            sessionStorage.removeItem('elyu_show_offline_bypass_notice');
        } catch (e) {
            localStorage.removeItem('intan_elyu_token');
            localStorage.removeItem('Intan_Elyu_Token');
            localStorage.removeItem('auth_user');
            localStorage.removeItem('intan_elyu_claimed_vouchers');
            localStorage.removeItem('intan_elyu_cached_vouchers');
        }

        if (window._touristRedemptions) window._touristRedemptions = [];
        if (window._cachedActiveVouchers) window._cachedActiveVouchers = [];
        if (window._cachedHistoryVouchers) window._cachedHistoryVouchers = [];

        setTimeout(() => {
            // Direct cleanly to Auth (Sign In / Register) screen
            if (typeof window.navigateTo === 'function') {
                window.navigateTo('auth', true, false);
            } else {
                window.location.replace('index.php?view=auth');
            }
        }, 300);
    };

    /**
     * Handle Logout - Shows confirmation notification modal
     */
    async function handleLogout(e) {
        if (e) {
            if (typeof e.preventDefault === 'function') e.preventDefault();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
        }
        window.showLogoutConfirmationModal();
    }
    window.handleLogout = handleLogout;

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
    if ('serviceWorker' in navigator && (window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
        window.addEventListener('load', function () {
            try {
                // Resolve sw.js relative to document.baseURI to support clean routes and sub-directories without 404
                const swUrl = document.baseURI ? new URL('sw.js', document.baseURI).href : 'sw.js';
                navigator.serviceWorker.register(swUrl).then(function (registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    try { registration.update(); } catch(e) {}
                }).catch(function (err) {
                    console.warn('ServiceWorker registration note: ', err);
                });
            } catch (e) {
                console.warn('ServiceWorker init skipped: ', e);
            }
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

        if (window.intanElyuLocationWatchId) {
            navigator.geolocation.clearWatch(window.intanElyuLocationWatchId);
        }

        let lastAlertedItems = JSON.parse(localStorage.getItem('intan_elyu_alerted_items') || '{}');
        let lastGpsProcessTime = 0;

        const onPos = (position) => {
            const currentLat = position.coords.latitude;
            const currentLng = position.coords.longitude;
            const accuracy = position.coords.accuracy;
            const altitude = position.coords.altitude;
            const speed = position.coords.speed;

            // Mark as real verified GPS
            window.currentGPSSource = 'gps';
            window.currentGPSLat = currentLat;
            window.currentGPSLng = currentLng;
            window.myLat = currentLat;
            window.myLng = currentLng;
            window.currentGPSAccuracy = accuracy;
            window.currentGPSAltitude = altitude;
            window.currentGPSSpeed = speed;

            // Broadcast dynamic update for real-time map tracking
            requestAnimationFrame(() => {
                document.dispatchEvent(new CustomEvent('gpsUpdated', { detail: { lat: currentLat, lng: currentLng, accuracy, altitude, speed, source: 'gps' } }));
            });

            // Proximity auto check-in is intentionally disabled as check-in requires explicit photo proof submission and pending review.
        };

        const onErr = (error) => {
            if (error.code === 2 || error.code === 3) {
                if (window.intanElyuLocationWatchId) {
                    navigator.geolocation.clearWatch(window.intanElyuLocationWatchId);
                    window.intanElyuLocationWatchId = navigator.geolocation.watchPosition(
                        onPos,
                        (e2) => { if (e2.code !== 3 && e2.code !== 1) console.warn("Network location watch error:", e2); },
                        { enableHighAccuracy: false, maximumAge: 10000, timeout: 20000 }
                    );
                }
            } else if (error.code === 1) {
                console.warn("Location permission denied by browser. Please allow location in browser site settings.");
                localStorage.setItem('intan_elyu_loc_enabled', 'false');
                localStorage.setItem('Intan_Elyu_loc_enabled', 'false');
                const locToggle = document.getElementById('location-service-toggle');
                if (locToggle) locToggle.checked = false;
                document.dispatchEvent(new CustomEvent('locationStatusChanged', { detail: { enabled: false, error } }));
            }
        };

        window.intanElyuLocationWatchId = navigator.geolocation.watchPosition(
            onPos,
            onErr,
            { enableHighAccuracy: true, maximumAge: 3000, timeout: 15000 }
        );
    };

    window.stopLocationWatch = function () {
        if (window.intanElyuLocationWatchId && navigator.geolocation) {
            navigator.geolocation.clearWatch(window.intanElyuLocationWatchId);
            window.intanElyuLocationWatchId = null;
        }
        window.currentGPSSource = null;
        document.dispatchEvent(new CustomEvent('locationStatusChanged', { detail: { enabled: false } }));
    };

    // Request high-accuracy hardware GPS location from the device with Progressive Fallback
    window.requestPreciseLocation = async function (forceFresh = false) {
        // 0. If we already have a real verified GPS fix and not forcing cold fresh, return it immediately
        if (!forceFresh && window.currentGPSSource === 'gps' && window.currentGPSLat && window.currentGPSLng) {
            return { lat: window.currentGPSLat, lng: window.currentGPSLng, source: 'gps', accuracy: window.currentGPSAccuracy || 10 };
        }

        // 1. If running under Capacitor native runtime, use Capacitor Geolocation plugin
        if (window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform()) {
            try {
                const Geolocation = (window.Capacitor.Plugins && window.Capacitor.Plugins.Geolocation) ||
                    (window.Capacitor.registerPlugin ? window.Capacitor.registerPlugin('Geolocation') : null);
                if (Geolocation) {
                    const perm = await Geolocation.checkPermissions();
                    if (perm.location !== 'granted') {
                        const req = await Geolocation.requestPermissions();
                        if (req.location !== 'granted') throw new Error('Permission denied');
                    }
                    const pos = await Geolocation.getCurrentPosition({
                        enableHighAccuracy: true,
                        timeout: 20000,
                        maximumAge: 10000
                    });
                    if (pos && pos.coords) {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        const accuracy = pos.coords.accuracy || 10;
                        window.currentGPSLat = lat;
                        window.currentGPSLng = lng;
                        window.myLat = lat;
                        window.myLng = lng;
                        window.currentGPSSource = 'gps';
                        window.currentGPSAccuracy = accuracy;
                        document.dispatchEvent(new CustomEvent('gpsUpdated', {
                            detail: { lat, lng, accuracy, source: 'gps', altitude: pos.coords.altitude, speed: pos.coords.speed }
                        }));
                        return { lat, lng, source: 'gps', accuracy };
                    }
                }
            } catch (e) {
                console.warn("Capacitor precise geolocation error:", e);
            }
        }

        // 2. Standard HTML5 Geolocation API with Progressive Fallback (Warm Cache -> High Accuracy -> Network)
        if (navigator.geolocation) {
            const getPositionPromise = (options) => new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, options);
            });

            let pos = null;

            // Pass 1: Try quick warm cache (instant if device recently had a fix)
            try {
                pos = await getPositionPromise({
                    enableHighAccuracy: true,
                    timeout: 2500,
                    maximumAge: 60000
                });
            } catch (e) {
                // Warm cache not available, proceed to live request
            }

            // Pass 2: Request high-accuracy GPS with generous 20-second timeout
            if (!pos) {
                try {
                    pos = await getPositionPromise({
                        enableHighAccuracy: true,
                        timeout: 20000,
                        maximumAge: 10000
                    });
                } catch (err2) {
                    // Pass 3: If high accuracy timed out or is weak indoors, fallback to network/cell/Wi-Fi positioning
                    if (err2.code === 2 || err2.code === 3) {
                        console.log("High accuracy GPS timed out, trying network/Wi-Fi positioning...");
                        try {
                            pos = await getPositionPromise({
                                enableHighAccuracy: false,
                                timeout: 20000,
                                maximumAge: 30000
                            });
                        } catch (err3) {
                            // If all timed out but we have a previous GPS in memory, use it
                            if (window.currentGPSLat && window.currentGPSLng) {
                                return { lat: window.currentGPSLat, lng: window.currentGPSLng, source: window.currentGPSSource || 'gps', accuracy: window.currentGPSAccuracy || 20 };
                            }
                            throw err3;
                        }
                    } else {
                        throw err2;
                    }
                }
            }

            if (pos && pos.coords) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const accuracy = pos.coords.accuracy || 15;
                window.currentGPSLat = lat;
                window.currentGPSLng = lng;
                window.myLat = lat;
                window.myLng = lng;
                window.currentGPSSource = 'gps';
                window.currentGPSAccuracy = accuracy;
                document.dispatchEvent(new CustomEvent('gpsUpdated', {
                    detail: { lat, lng, accuracy, source: 'gps', altitude: pos.coords.altitude, speed: pos.coords.speed }
                }));
                return { lat, lng, source: 'gps', accuracy };
            }
        }

        throw new Error('Geolocation not supported by device');
    };

    // Fast-track location: returns real GPS if available, else requests it
    window.fastLocation = function () {
        if (window.currentGPSSource === 'gps' && window.currentGPSLat && window.currentGPSLng) {
            return Promise.resolve({ lat: window.currentGPSLat, lng: window.currentGPSLng, source: 'gps' });
        }
        return window.resolveUserLocation(false);
    };

    // Pure real location resolver (No fake mock locations)
    window.resolveUserLocation = async function (forceFresh = false) {
        // If we already have a real verified GPS fix and not forcing fresh, return it
        if (!forceFresh && window.currentGPSSource === 'gps' && window.currentGPSLat && window.currentGPSLng) {
            return { lat: window.currentGPSLat, lng: window.currentGPSLng, source: 'gps' };
        }

        // Try real hardware GPS
        try {
            const precise = await window.requestPreciseLocation(forceFresh);
            if (precise && precise.lat && precise.lng) {
                return precise;
            }
        } catch (e) {
            console.warn("Live GPS acquisition error:", e && e.message);
        }

        // Check if user manually saved a location
        try {
            const manualLocStr = localStorage.getItem('intan_elyu_manual_loc');
            if (manualLocStr) {
                const manual = JSON.parse(manualLocStr);
                if (manual && manual.lat && manual.lng) {
                    window.currentGPSLat = manual.lat;
                    window.currentGPSLng = manual.lng;
                    window.myLat = manual.lat;
                    window.myLng = manual.lng;
                    window.currentGPSSource = 'manual';
                    document.dispatchEvent(new CustomEvent('gpsUpdated', {
                        detail: { lat: manual.lat, lng: manual.lng, accuracy: 10, source: 'manual', name: manual.name }
                    }));
                    return { lat: manual.lat, lng: manual.lng, source: 'manual', name: manual.name };
                }
            }
        } catch (e) { }

        return null;
    };

    // La Union towns catalog for instant manual location picking (All 20 Municipalities & City)
    window.LA_UNION_TOWNS = [
        { name: 'San Juan (Surfing Capital)', lat: 16.671123, lng: 120.338487, icon: 'fa-person-surfing', desc: 'Urbiztondo Beach, Surf Breaks & Cafes' },
        { name: 'San Fernando City (Capitol)', lat: 16.6159, lng: 120.3167, icon: 'fa-landmark-dome', desc: 'City Center, Capitol, Poro Point & Malls' },
        { name: 'Bauang', lat: 16.5319, lng: 120.3298, icon: 'fa-wine-bottle', desc: 'Grape Farms, Beaches & Resorts' },
        { name: 'Bacnotan', lat: 16.7202, lng: 120.3353, icon: 'fa-cubes-stacked', desc: 'Apiary, Surfing & Coastal Views' },
        { name: 'Balaoan', lat: 16.8228, lng: 120.4005, icon: 'fa-gem', desc: 'Immuki Island & Coral Lagoons' },
        { name: 'Luna', lat: 16.8554, lng: 120.3758, icon: 'fa-chess-rook', desc: 'Pebble Beach, Baluarte & Ruins' },
        { name: 'Bangar', lat: 16.8942, lng: 120.4245, icon: 'fa-shirt', desc: 'Abel Loom Weaving & Cultural Heritage' },
        { name: 'Sudipen', lat: 16.9031, lng: 120.4700, icon: 'fa-bridge-water', desc: 'Amburayan River & Northern Gateway' },
        { name: 'Santol', lat: 16.7686, lng: 120.4578, icon: 'fa-mountain', desc: 'Highland Waterfalls & Mountain Vistas' },
        { name: 'San Gabriel', lat: 16.6711, lng: 120.4050, icon: 'fa-water-ladder', desc: 'Tangadan Falls Jump-off & Eco Nature' },
        { name: 'Bagulin', lat: 16.6072, lng: 120.4422, icon: 'fa-campground', desc: 'Loslosi Hills, Bamboo Craft & Nature' },
        { name: 'Burgos', lat: 16.5183, lng: 120.4578, icon: 'fa-mountain-sun', desc: 'Highland Ridge Trails & Basi Legacy' },
        { name: 'Naguilian', lat: 16.5366, lng: 120.3926, icon: 'fa-bottle-droplet', desc: 'Basi Wine, Woodcraft & Foothills' },
        { name: 'Aringay', lat: 16.3958, lng: 120.3325, icon: 'fa-train-subway', desc: 'Centennial Tunnel & Eco Tourism' },
        { name: 'Caba', lat: 16.4292, lng: 120.3344, icon: 'fa-basket-shopping', desc: 'Bamboo Crafts & Agri-Tourism' },
        { name: 'Agoo', lat: 16.3217, lng: 120.3667, icon: 'fa-church', desc: 'Basilica Minore & Eco-Fun Park' },
        { name: 'Tubao', lat: 16.3470, lng: 120.4126, icon: 'fa-place-of-worship', desc: 'Mount Franciscan & Heritage Grotto' },
        { name: 'Pugo', lat: 16.3167, lng: 120.4667, icon: 'fa-person-hiking', desc: 'Pugad Adventure & Tapuakan River' },
        { name: 'Santo Tomas', lat: 16.2842, lng: 120.3861, icon: 'fa-fish', desc: 'Daing Capital & Marine Sanctuaries' },
        { name: 'Rosario', lat: 16.2286, lng: 120.4850, icon: 'fa-archway', desc: 'Gateway to Ilocandia & Tree Canopy' }
    ];

    window.setManualLocation = function (lat, lng, name) {
        const pLat = parseFloat(lat);
        const pLng = parseFloat(lng);
        if (isNaN(pLat) || isNaN(pLng)) return;

        window.currentGPSLat = pLat;
        window.currentGPSLng = pLng;
        window.myLat = pLat;
        window.myLng = pLng;
        window.currentGPSSource = 'manual';

        localStorage.setItem('intan_elyu_manual_loc', JSON.stringify({ lat: pLat, lng: pLng, name: name || 'Selected Location' }));

        document.dispatchEvent(new CustomEvent('gpsUpdated', {
            detail: { lat: pLat, lng: pLng, accuracy: 10, source: 'manual', name: name || 'Selected Location' }
        }));

        if (typeof showToast === 'function') {
            showToast(`📍 Location set to ${name || 'Selected Spot'}`);
        }

        if (window.mapInstance) {
            window.mapInstance.flyTo({ center: [pLng, pLat], zoom: 14, duration: 1000 });
            if (window.userMarker) {
                window.userMarker.setLngLat([pLng, pLat]);
            }
        }
        if (typeof draftMap !== 'undefined' && draftMap) {
            draftMap.flyTo([pLat, pLng], 15);
            if (window.myDraftMarker) {
                window.myDraftMarker.setLatLng([pLat, pLng]);
            }
        }
        if (typeof tripMap !== 'undefined' && tripMap) {
            tripMap.flyTo({ center: [pLng, pLat], zoom: 15 });
            if (window.tripGpsMarker) {
                window.tripGpsMarker.setLngLat([pLng, pLat]);
            }
        }
        window.closeLocationPickerModal();
    };

    window.openLocationPickerModal = function () {
        let modal = document.getElementById('location-picker-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'location-picker-modal';
            modal.style.cssText = "position:fixed; top:0; left:0; right:0; bottom:0; z-index:11000; background:rgba(0,0,0,0.65); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; padding:16px; opacity:0; transition:opacity 0.25s ease;";

            const content = document.createElement('div');
            content.style.cssText = "background:linear-gradient(135deg, rgba(15,23,42,0.98), rgba(30,41,59,0.98)); border:1px solid rgba(56,189,248,0.3); border-radius:24px; width:100%; max-width:420px; max-height:85vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.7);";

            content.innerHTML = `
            <div style="padding:18px 20px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div>
                    <h3 style="margin:0; font-size:16px; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-location-crosshairs" style="color:#38bdf8;"></i> Select Your Location
                    </h3>
                    <span style="font-size:11px; color:rgba(148,163,184,0.9);">Choose your town or acquire device GPS</span>
                </div>
                <button onclick="window.closeLocationPickerModal()" style="background:rgba(255,255,255,0.08); border:none; color:white; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div style="padding:14px 20px; background:rgba(56,189,248,0.06); border-bottom:1px solid rgba(56,189,248,0.15); flex-shrink:0;">
                <button id="btn-modal-gps-acquire" onclick="window.acquireGpsFromModal()" style="width:100%; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:white; padding:11px 16px; border-radius:14px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 14px rgba(56,189,248,0.35);">
                    <i class="fa-solid fa-location-arrow"></i> Use Live Device GPS
                </button>
                <div style="font-size:10px; color:rgba(255,255,255,0.6); text-align:center; margin-top:6px;">
                    Ensure device GPS is turned on and location permission is granted for Intan Elyu.
                </div>
            </div>
            <div style="padding:14px 20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:8px;">
                <div style="font-size:11px; font-weight:700; color:#38bdf8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                    Or Choose La Union Municipality:
                </div>
                ${window.LA_UNION_TOWNS.map(t => `
                    <div onclick="window.setManualLocation(${t.lat}, ${t.lng}, '${t.name.replace(/'/g, "\\'")}')" style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:14px; cursor:pointer; transition:all 0.15s ease;" onmouseover="this.style.background='rgba(56,189,248,0.15)'; this.style.borderColor='rgba(56,189,248,0.4)';" onmouseout="this.style.background='rgba(255,255,255,0.04)'; this.style.borderColor='rgba(255,255,255,0.08)';">
                        <span style="font-size:20px; width:28px; text-align:center;">${t.icon}</span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; font-weight:700; color:#fff;">${t.name}</div>
                            <div style="font-size:11px; color:rgba(148,163,184,0.8);">${t.desc}</div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:rgba(255,255,255,0.3); font-size:11px;"></i>
                    </div>
                `).join('')}
            </div>
        `;
            modal.appendChild(content);
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
        requestAnimationFrame(() => { modal.style.opacity = '1'; });
    };

    window.closeLocationPickerModal = function () {
        const modal = document.getElementById('location-picker-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => { modal.style.display = 'none'; }, 250);
        }
    };

    window.acquireGpsFromModal = async function () {
        const btn = document.getElementById('btn-modal-gps-acquire');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Getting your location...';
        try {
            const loc = await window.requestPreciseLocation(false);
            if (loc && loc.lat && loc.lng) {
                localStorage.removeItem('intan_elyu_manual_loc');
                if (typeof showToast === 'function') showToast("Live GPS Locked 📍");
                window.closeLocationPickerModal();
            }
        } catch (err) {
            console.warn("Modal GPS request failed:", err);
            if (typeof showToast === 'function') {
                if (err && err.code === 3) {
                    showToast("GPS signal timed out. Please ensure Location/GPS is ON on your device or pick a town below.");
                } else if (err && err.code === 1) {
                    showToast("Location Permission Denied. Please enable Location in app settings or choose a town below.");
                } else {
                    showToast("Could not acquire GPS. Please choose a town below.");
                }
            }
        } finally {
            if (btn) btn.innerHTML = origHtml;
        }
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
                if (url.includes('localhost') || url.includes('127.0.0.1') || /192\.168\.\d+\.\d+/.test(url) || /10\.\d+\.\d+\.\d+/.test(url) || /172\.(1[6-9]|2\d|3[01])\.\d+\.\d+/.test(url)) {
                    var localMatch = url.match(/(spot_|avatar_|proof_)[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif)/i);
                    if (localMatch && localMatch[0]) {
                        url = localMatch[0];
                    } else if (backendUrl) {
                        url = url.replace(/^https?:\/\/(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+|172\.(?:1[6-9]|2\d|3[01])\.\d+\.\d+)(:\d+)?/i, backendUrl);
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

        // Phase 3: Final fallback to no_image.svg if no image was uploaded or inputted
        return 'assets/img/no_image.svg';
    };

    window.handleImgError = function (imgEl, spotName, muniName) {
        if (!imgEl) return;
        imgEl.onerror = null;
        var defaultNoImage = 'assets/img/no_image.svg';
        if (window.getDestImage && (spotName || muniName)) {
            var fallback = window.getDestImage({ name: spotName || '', municipality: muniName || '', photo_url: null }, 600);
            if (fallback && fallback !== imgEl.src && !fallback.includes('unsplash.com') && !fallback.endsWith('no_image.svg')) {
                imgEl.src = fallback;
                return;
            }
        }
        imgEl.src = defaultNoImage;
    };

    window.checkForAppUpdate = function () {
        const apkUrl = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/apks/intan-elyu.apk';
        if (typeof showToast === 'function') {
            showToast('Downloading latest Intan Elyu update (v0.0.0 dev)...');
        }
        setTimeout(() => {
            window.open(apkUrl, '_system');
        }, 350);
    };

    /**
     * Global Lightbox Proof Photo Viewer Modal (Supports single photo or multiple photos gallery)
     */
    window.viewProofPhotoModal = function (urlOrUrls, title = 'Visit Proof', subtitle = '', initialIndex = 0) {
        if (!urlOrUrls) return;
        var urls = [];
        if (Array.isArray(urlOrUrls)) {
            urls = urlOrUrls.filter(Boolean);
        } else if (typeof urlOrUrls === 'string') {
            try {
                var parsed = JSON.parse(urlOrUrls);
                if (Array.isArray(parsed)) urls = parsed.filter(Boolean);
                else urls = [urlOrUrls];
            } catch (e) {
                urls = [urlOrUrls];
            }
        }
        if (!urls.length) return;

        var currentIndex = Math.max(0, Math.min(initialIndex || 0, urls.length - 1));
        var existing = document.getElementById('elyu-proof-viewer-modal');
        if (existing) existing.remove();

        var modal = document.createElement('div');
        modal.id = 'elyu-proof-viewer-modal';
        modal.style.cssText = 'display:flex; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.88); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; flex-direction:column; align-items:center; justify-content:center; padding:16px; box-sizing:border-box;';
        modal.onclick = function (e) {
            if (e.target === modal || e.target.closest('.close-viewer-btn')) {
                modal.remove();
            }
        };

        function renderContent() {
            var total = urls.length;
            var currentUrl = urls[currentIndex];
            var counterText = total > 1 ? `<span style="font-size:11px; font-weight:800; background:rgba(0,242,254,0.2); color:#00f2fe; padding:2px 8px; border-radius:100px;">${currentIndex + 1} / ${total}</span>` : '';

            var navControls = '';
            if (total > 1) {
                navControls = `
                    <button type="button" class="proof-nav-btn prev" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:36px; height:36px; border-radius:50%; background:rgba(15,23,42,0.75); color:#fff; border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:10; backdrop-filter:blur(8px);">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="proof-nav-btn next" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:36px; height:36px; border-radius:50%; background:rgba(15,23,42,0.75); color:#fff; border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:10; backdrop-filter:blur(8px);">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                `;
            }

            var thumbStrip = '';
            if (total > 1) {
                var thumbsHtml = urls.map(function (u, idx) {
                    var isSelected = (idx === currentIndex);
                    return `<img src="${u}" data-idx="${idx}" class="proof-thumb-item" style="width:40px; height:40px; border-radius:8px; object-fit:cover; border:${isSelected ? '2px solid #00f2fe' : '1px solid rgba(255,255,255,0.2)'}; opacity:${isSelected ? '1' : '0.6'}; cursor:pointer; flex-shrink:0;">`;
                }).join('');
                thumbStrip = `<div style="display:flex; gap:8px; overflow-x:auto; padding:6px 0; justify-content:center; max-width:100%;">${thumbsHtml}</div>`;
            }

            modal.innerHTML = `
                <div style="position:relative; width:100%; max-width:440px; background:linear-gradient(135deg, rgba(30, 58, 138, 0.96), rgba(15, 23, 42, 0.98)); border-radius:24px; padding:20px; box-shadow:0 20px 50px rgba(0,0,0,0.6); display:flex; flex-direction:column; gap:14px; border:1px solid rgba(255,255,255,0.12);" onclick="event.stopPropagation();">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h3 style="margin:0; font-size:16px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-camera" style="color:#00f2fe; font-size:15px;"></i> ${title} ${counterText}
                            </h3>
                            ${subtitle ? `<span style="font-size:11px; color:#cbd5e1; font-weight:600; display:block; margin-top:2px;">${subtitle}</span>` : ''}
                        </div>
                        <button type="button" class="close-viewer-btn" onclick="document.getElementById('elyu-proof-viewer-modal')?.remove();" style="width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.15); border:none; outline:none; color:#ffffff; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div style="position:relative; width:100%; max-height:60vh; min-height:220px; border-radius:16px; overflow:hidden; background:#000; display:flex; align-items:center; justify-content:center;">
                        <img src="${currentUrl}" alt="Visit Proof" style="width:100%; height:auto; max-height:60vh; object-fit:contain; display:block;">
                        ${navControls}
                    </div>
                    ${thumbStrip}
                </div>
            `;

            var prevBtn = modal.querySelector('.proof-nav-btn.prev');
            var nextBtn = modal.querySelector('.proof-nav-btn.next');
            if (prevBtn) {
                prevBtn.onclick = function (ev) {
                    ev.stopPropagation();
                    currentIndex = (currentIndex - 1 + total) % total;
                    renderContent();
                };
            }
            if (nextBtn) {
                nextBtn.onclick = function (ev) {
                    ev.stopPropagation();
                    currentIndex = (currentIndex + 1) % total;
                    renderContent();
                };
            }

            modal.querySelectorAll('.proof-thumb-item').forEach(function (thumb) {
                thumb.onclick = function (ev) {
                    ev.stopPropagation();
                    var targetIdx = parseInt(thumb.getAttribute('data-idx'), 10);
                    if (!isNaN(targetIdx) && targetIdx !== currentIndex) {
                        currentIndex = targetIdx;
                        renderContent();
                    }
                };
            });
        }

        renderContent();
        document.body.appendChild(modal);
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
                    if (resolved && !list.includes(resolved) && resolved !== 'assets/img/no_image.svg' && resolved !== window.noImageFallback) {
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

    window.noImageFallback = 'assets/img/no_image.svg';

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
                    if (window.isAppOnline === false && typeof window.notifyAppOnline === 'function') {
                        window.notifyAppOnline();
                    }
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

    // ── Badges Removed: Safe No-Op Handlers & Residual DOM Purge ────────────────
    window.openBadgeModal = function () {};
    window.closeBadgeModal = function () {
        const m1 = document.getElementById('badge-details-modal');
        if (m1) m1.remove();
        const m2 = document.getElementById('all-badges-modal');
        if (m2) m2.remove();
    };
    window.openAllBadgesModal = function () {};
    window.closeAllBadgesModal = function () {
        const m2 = document.getElementById('all-badges-modal');
        if (m2) m2.remove();
    };

    // Emergency purge for stale cached DOM elements from ServiceWorker or web cache
    window.purgeResidualBadges = function () {
        try {
            const el1 = document.getElementById('profile-badges-container');
            if (el1) el1.remove();
            const el2 = document.getElementById('profile-badges-grid');
            if (el2) el2.remove();
            const el3 = document.getElementById('profile-badge-count-label');
            if (el3) el3.remove();
            const m1 = document.getElementById('badge-details-modal');
            if (m1) m1.remove();
            const m2 = document.getElementById('all-badges-modal');
            if (m2) m2.remove();
            document.querySelectorAll('h3').forEach(h3 => {
                if (h3 && h3.textContent && h3.textContent.includes('Badges & Achievements')) {
                    const row = h3.closest('.stagger-3') || h3.parentElement;
                    if (row) row.remove();
                }
            });
        } catch (e) {}
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.purgeResidualBadges);
    } else {
        window.purgeResidualBadges();
    }
    [100, 300, 600, 1000, 2000].forEach(delay => setTimeout(window.purgeResidualBadges, delay));

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
                showToast(`⚡ ${syncedCount} Offline Check-in(s) synced! Points awarded!`);
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

    // =========================================================================
    // Mobile Virtual Keyboard Detection & Auto-Hide Navigation Bar
    // =========================================================================
    (function initVirtualKeyboardDetector() {
        function isEditableTarget(el) {
            if (!el) return false;
            const tag = (el.tagName || '').toUpperCase();
            if (tag === 'TEXTAREA' || el.isContentEditable) return true;
            if (tag === 'INPUT') {
                const type = (el.type || 'text').toLowerCase();
                return !['submit', 'button', 'checkbox', 'radio', 'file', 'image', 'reset', 'range', 'color'].includes(type);
            }
            return false;
        }

        function hideNavForKeyboard() {
            document.body.classList.add('keyboard-open');
            document.documentElement.classList.add('keyboard-open');
            const bNav = document.getElementById('bottom-navigation');
            const mNav = document.getElementById('magic-nav');
            if (bNav) bNav.classList.add('keyboard-hidden');
            if (mNav) mNav.classList.add('keyboard-hidden');
        }

        function showNavAfterKeyboard() {
            document.body.classList.remove('keyboard-open');
            document.documentElement.classList.remove('keyboard-open');
            const bNav = document.getElementById('bottom-navigation');
            const mNav = document.getElementById('magic-nav');
            if (bNav) bNav.classList.remove('keyboard-hidden');
            if (mNav) mNav.classList.remove('keyboard-hidden');
        }

        // 1. Focusin / Focusout listeners (instant reaction when tapping any input)
        document.addEventListener('focusin', (e) => {
            if (isEditableTarget(e.target)) {
                hideNavForKeyboard();
            }
        }, true);

        document.addEventListener('focusout', (e) => {
            if (isEditableTarget(e.target)) {
                setTimeout(() => {
                    if (!isEditableTarget(document.activeElement)) {
                        showNavAfterKeyboard();
                    }
                }, 120);
            }
        }, true);

        // 2. VisualViewport API (detects virtual keyboard slide-up on Android & iOS)
        if (window.visualViewport) {
            const onViewportChange = () => {
                const currentHeight = window.visualViewport.height;
                const screenHeight = window.screen.height || window.innerHeight;
                const heightDiff = window.innerHeight - currentHeight;

                if (heightDiff > 120 || (isEditableTarget(document.activeElement) && currentHeight < screenHeight * 0.8)) {
                    hideNavForKeyboard();
                } else if (!isEditableTarget(document.activeElement)) {
                    showNavAfterKeyboard();
                }
            };

            window.visualViewport.addEventListener('resize', onViewportChange);
            window.visualViewport.addEventListener('scroll', onViewportChange);
        }

        // 3. Capacitor Keyboard plugin events (for native Android / iOS wrapper)
        if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Keyboard) {
            try {
                const { Keyboard } = window.Capacitor.Plugins;
                Keyboard.addListener('keyboardWillShow', hideNavForKeyboard);
                Keyboard.addListener('keyboardDidShow', hideNavForKeyboard);
                Keyboard.addListener('keyboardWillHide', showNavAfterKeyboard);
                Keyboard.addListener('keyboardDidHide', showNavAfterKeyboard);
            } catch (e) { }
        }
    })();

    // =========================================================================
    // Global Dimension Lock for Auth & Reset Password Views
    // =========================================================================
    window.freezeAuthLayout = function () {
        const view = document.body ? document.body.getAttribute('data-view') : '';
        if (view !== 'auth' && view !== 'reset-password') return;

        const winH = window.innerHeight || 0;
        const scrH = (window.screen && window.screen.height) ? window.screen.height : 0;
        let storedH = parseInt(sessionStorage.getItem('auth_locked_screen_h') || '0', 10);
        if (!storedH || winH > storedH) {
            storedH = Math.max(winH, scrH > 300 ? scrH : winH);
            try { sessionStorage.setItem('auth_locked_screen_h', storedH); } catch (e) { }
        }
        if (storedH > 0) {
            document.documentElement.style.setProperty('--auth-screen-h', storedH + 'px');
        }
    };

    document.addEventListener('DOMContentLoaded', window.freezeAuthLayout);
    window.addEventListener('load', window.freezeAuthLayout);
    window.addEventListener('orientationchange', function () {
        try { sessionStorage.removeItem('auth_locked_screen_h'); } catch (e) { }
        setTimeout(window.freezeAuthLayout, 250);
    });

    // =========================================================================
    // Universal Ambient View Pre-Warming Engine (Zero-Latency Offline Support)
    // =========================================================================
    window.prewarmAllViews = async function () {
        if (!navigator.onLine) return;
        const viewsToPrewarm = [
            'splash', 'auth', 'dashboard', 'itinerary', 'saved_trips', 'trip_map',
            'leaderboard', 'profile', 'map', 'trending', 'discount',
            'puzzles', 'saved_places', 'settings', 'about', 'help',
            'terms', 'user_manual', 'edit_profile', 'ar_checkin'
        ];
        window._viewHtmlCache = window._viewHtmlCache || {};

        for (const v of viewsToPrewarm) {
            try {
                if (!window._viewHtmlCache[v] && !localStorage.getItem('elyu_view_cache_v3_' + v)) {
                    const res = await fetch(`index.php?view=${encodeURIComponent(v)}&ajax=1`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (res.ok) {
                        const text = await res.text();
                        if (text && text.length > 50) {
                            window._viewHtmlCache[v] = text;
                            try { localStorage.setItem('elyu_view_cache_v3_' + v, text); } catch (e) { }
                        }
                    }
                }
            } catch (e) { }
        }
    };

    if (document.readyState === 'complete') {
        setTimeout(window.prewarmAllViews, 1000);
    } else {
        window.addEventListener('load', () => setTimeout(window.prewarmAllViews, 1000));
    }
}
