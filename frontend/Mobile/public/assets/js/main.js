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

    // Synchronize user auth tokens and purge stale cross-user caches
    const mainTok = localStorage.getItem('intan_elyu_token');
    const authUser = (function () {
        try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch(e) { return null; }
    })();
    const curId = authUser ? Number(authUser.id || authUser.user_id || 0) : 0;

    if (mainTok && mainTok !== 'guest_token' && mainTok !== 'offline_guest_token' && !mainTok.startsWith('guest_') && !mainTok.startsWith('offline_')) {
        localStorage.setItem('Intan_Elyu_Token', mainTok);
        localStorage.setItem('api_token', mainTok);

        // Reconcile intan_elyu_user if it had a mismatched user
        try {
            const altUser = JSON.parse(localStorage.getItem('intan_elyu_user') || 'null');
            if (altUser && curId > 0 && Number(altUser.id || altUser.user_id || 0) !== curId) {
                localStorage.setItem('intan_elyu_user', JSON.stringify(authUser));
            }
        } catch(e) {}

        // Discard any profile_data_ caches belonging to another user
        for (let i = localStorage.length - 1; i >= 0; i--) {
            const k = localStorage.key(i);
            if (!k) continue;
            if (k.startsWith('profile_data_')) {
                try {
                    const parsed = JSON.parse(localStorage.getItem(k));
                    const data = parsed && (parsed.data || parsed);
                    const uid = Number(data && data.user && (data.user.id || data.user.user_id) || 0);
                    if (curId > 0 && uid > 0 && uid !== curId) {
                        localStorage.removeItem(k);
                    }
                } catch(e) {}
            }
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

    isItemQueued: function (itemId, spotId = null) {
        if (!itemId && !spotId) return false;
        const q = this.getQueue();
        const target = itemId ? String(itemId) : '';
        const targetSpot = spotId ? String(spotId) : '';
        return q.some(entry => {
            if (!entry) return false;
            const eItem = String(entry.itemId || '');
            const eSpot = String(entry.spotId || '');
            if (target && (eItem === target || eSpot === target)) return true;
            if (targetSpot && (eSpot === targetSpot || eItem === targetSpot)) return true;
            return false;
        });
    },

    getQueuedItem: function (itemId, spotId = null) {
        if (!itemId && !spotId) return null;
        const q = this.getQueue();
        const target = itemId ? String(itemId) : '';
        const targetSpot = spotId ? String(spotId) : '';
        return q.find(entry => {
            if (!entry) return false;
            const eItem = String(entry.itemId || '');
            const eSpot = String(entry.spotId || '');
            if (target && (eItem === target || eSpot === target)) return true;
            if (targetSpot && (eSpot === targetSpot || eItem === targetSpot)) return true;
            return false;
        }) || null;
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
        // Enforce 200m radius if destination coordinates are provided and this is a location checkin
        const destLat = data.destLat ?? data.dest_lat ?? null;
        const destLng = data.destLng ?? data.dest_lng ?? null;
        if (destLat != null && destLng != null && data.lat != null && data.lng != null && !data.isPhotoUploadOnly) {
            const dLat = (parseFloat(destLat) - parseFloat(data.lat)) * Math.PI / 180;
            const dLng = (parseFloat(destLng) - parseFloat(data.lng)) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(parseFloat(data.lat) * Math.PI / 180) * Math.cos(parseFloat(destLat) * Math.PI / 180) *
                      Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const dist = 6371000 * c;
            if (dist > 200) {
                console.warn(`[OfflineCheckinManager] Check-in blocked: tourist is ${Math.round(dist)}m away (> 200m radius)`);
                throw new Error(`You are ${Math.round(dist)}m away from ${data.spotName || 'the destination'}. You must be within 200m to check in! 📍`);
            }
        }

        const id = 'chk_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
        const capturedAt = data.capturedAt || new Date().toISOString();

        let hasPhoto = false;
        let previewUrl = data.previewUrl || null;
        const photoData = data.image || data.imageFile || data.photo || null;

        if (!previewUrl && photoData) {
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
        const existingIdx = queue.findIndex(q => {
            if (!q) return false;
            if (entry.itemId && q.itemId && String(q.itemId) === String(entry.itemId)) return true;
            if (entry.spotId && q.spotId && String(q.spotId) === String(entry.spotId)) return true;
            return false;
        });

        if (existingIdx !== -1) {
            queue[existingIdx] = {
                ...queue[existingIdx],
                ...entry,
                hasPhoto: entry.hasPhoto || queue[existingIdx].hasPhoto,
                previewUrl: entry.previewUrl || queue[existingIdx].previewUrl,
                lat: entry.lat || queue[existingIdx].lat,
                lng: entry.lng || queue[existingIdx].lng
            };
        } else {
            queue.push(entry);
        }
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
        if (!token || (typeof window.isGuestUser === 'function' && window.isGuestUser())) return;

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

                let resolvedItemId = item.itemId;

                // If itemId starts with offline_, attempt to resolve real numeric item ID from cached trips using spotId
                if ((!resolvedItemId || String(resolvedItemId).startsWith('offline_')) && item.spotId) {
                    try {
                        const tokenKey = (token || '').substring(0, 10);
                        ['saved_trips_' + tokenKey, 'dashboard_saved_trips_' + tokenKey, 'dashboard_trips_' + tokenKey].forEach(k => {
                            const raw = localStorage.getItem(k);
                            if (!raw) return;
                            const parsed = JSON.parse(raw);
                            const list = Array.isArray(parsed) ? parsed : (parsed.itineraries || parsed.data || []);
                            if (Array.isArray(list)) {
                                list.forEach(t => {
                                    if (t.items && Array.isArray(t.items)) {
                                        const match = t.items.find(i => {
                                            const sId = String(i.tourist_spot_id || i.destination_id || (i.destination && i.destination.id) || '');
                                            return sId === String(item.spotId) && i.id && !String(i.id).startsWith('offline_');
                                        });
                                        if (match) {
                                            resolvedItemId = match.id;
                                            item.itemId = match.id;
                                        }
                                    }
                                });
                            }
                        });
                    } catch (e) { }
                }

                // If item is an offline itinerary item that hasn't received a server ID yet, defer until next sync cycle
                if (item.type === 'itinerary' && String(resolvedItemId || '').startsWith('offline_')) {
                    continue;
                }

                let targetUrl = '';
                if (item.type === 'itinerary' && resolvedItemId && !String(resolvedItemId).startsWith('offline_')) {
                    targetUrl = (item.isPhotoUploadOnly && photoBlob)
                        ? `${baseUrl}/api/tourist/itineraries/items/${resolvedItemId}/proof`
                        : `${baseUrl}/api/tourist/itineraries/items/${resolvedItemId}/visit`;
                } else if (item.type === 'destination' && item.spotId) {
                    targetUrl = `${baseUrl}/api/tourist/destinations/${item.spotId}/check-in`;
                } else if (item.type === 'ar' && item.spotId) {
                    targetUrl = `${baseUrl}/api/tourist/points/ar-checkin`;
                    formData.append('spot_id', String(item.spotId));
                    formData.append('destination_id', String(item.spotId));
                } else if (resolvedItemId && !String(resolvedItemId).startsWith('offline_')) {
                    targetUrl = (item.isPhotoUploadOnly && photoBlob)
                        ? `${baseUrl}/api/tourist/itineraries/items/${resolvedItemId}/proof`
                        : `${baseUrl}/api/tourist/itineraries/items/${resolvedItemId}/visit`;
                }

                if (!targetUrl) {
                    if (!item.hasPhoto) {
                        await this.removeQueuedCheckin(item.id);
                    }
                    continue;
                }

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 20000);

                let response = null;
                try {
                    response = await fetch(targetUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token,
                            'ngrok-skip-browser-warning': 'true'
                        },
                        body: formData,
                        signal: controller.signal
                    });
                } catch (netErr) {
                    clearTimeout(timeoutId);
                    throw netErr;
                }
                clearTimeout(timeoutId);

                let data = await response.json().catch(() => ({}));
                let isResolved = response.ok || response.status === 409 || data.already_recorded || (data.status && ['pending', 'approved', 'success'].includes(data.status)) || Boolean(data.proof_status);

                // If /visit was rejected due to distance (403) or 422, but item has a photo proof, upload directly to /proof!
                if (!isResolved && photoBlob && resolvedItemId && !String(resolvedItemId).startsWith('offline_')) {
                    try {
                        const proofUrl = `${baseUrl}/api/tourist/itineraries/items/${resolvedItemId}/proof`;
                        const resProof = await fetch(proofUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': 'Bearer ' + token,
                                'ngrok-skip-browser-warning': 'true'
                            },
                            body: formData
                        });
                        const proofData = await resProof.json().catch(() => ({}));
                        if (resProof.ok || proofData.proof_status || proofData.success) {
                            isResolved = true;
                            data = proofData;
                        }
                    } catch (fbProofErr) { }
                }

                // If itinerary item check-in 404s (e.g. temporary offline item ID) and spotId is known, fallback to destination check-in
                if (!isResolved && response && response.status === 404 && item.spotId) {
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
                    } catch (fbErr) { }
                }

                // Treat 200, 201, 409 (already recorded), or explicit already_recorded flag as successfully resolved
                if (isResolved) {
                    syncedCount++;

                    // Optimistically update cached itinerary trips so user immediately sees their photo & pending review status
                    const returnedPhoto = data.proof_image || item.previewUrl || null;
                    const finalStatus = data.proof_status || data.status || 'pending';
                    const isApproved = finalStatus === 'approved' || finalStatus === 'success';

                    if (item.itemId || item.spotId) {
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
                                            const match = trip.items.find(i => {
                                                if (!i) return false;
                                                const itId = String(i.id || '');
                                                const itSpot = String(i.tourist_spot_id || i.destination_id || (i.destination ? i.destination.id : ''));
                                                if (item.itemId && (itId === String(item.itemId) || itSpot === String(item.itemId))) return true;
                                                if (item.spotId && (itSpot === String(item.spotId) || itId === String(item.spotId))) return true;
                                                return false;
                                            });
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
                            if (typeof window.savePersistentItemState === 'function') {
                                window.savePersistentItemState(item.itemId || item.spotId, {
                                    proof_image: returnedPhoto,
                                    proof_images: returnedPhoto ? [returnedPhoto] : [],
                                    proof_status: finalStatus,
                                    is_location_verified: true,
                                    is_visited: isApproved,
                                    spot_id: item.spotId
                                });
                            }
                        } catch (cacheErr) {
                            console.warn('Could not update local cached trip after sync:', cacheErr);
                        }
                    }

                    await this.removeQueuedCheckin(item.id);
                    window.dispatchEvent(new CustomEvent('offline-checkin-synced', { detail: { id: item.id, spotId: item.spotId, itemId: item.itemId, data } }));
                } else if (response && response.status >= 400 && response.status < 500 && response.status !== 408) {
                    item.attempts = (item.attempts || 0) + 1;
                    // Do NOT prune checkins with photos until at least 10 attempts
                    if (item.attempts >= 10 && !item.hasPhoto) {
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

// ══════════════════════════════════════════════════════════════════════════
// PERSISTENT DELETED TRIPS BLACKLIST & PENDING SERVER DELETIONS
// ══════════════════════════════════════════════════════════════════════════
window.getDeletedTripIds = function () {
    try {
        return (JSON.parse(localStorage.getItem('intan_elyu_deleted_trip_ids') || '[]') || []).map(String);
    } catch (e) {
        return [];
    }
};

window.markTripAsDeleted = function (tripId) {
    if (!tripId) return;
    try {
        const deleted = window.getDeletedTripIds();
        const sId = String(tripId).trim();
        if (!deleted.includes(sId)) {
            deleted.push(sId);
            if (deleted.length > 250) deleted.shift();
            localStorage.setItem('intan_elyu_deleted_trip_ids', JSON.stringify(deleted));
        }
    } catch (e) { }
};

window.isTripDeleted = function (trip) {
    if (!trip) return true;
    const deleted = window.getDeletedTripIds();
    if (!deleted || deleted.length === 0) return false;
    const ids = [
        String(trip.id || ''),
        String(trip.trip_id || ''),
        String(trip.local_id || ''),
        String(trip.update_id || '')
    ];
    for (const id of ids) {
        if (id && deleted.includes(id)) return true;
        if (id && id.startsWith('offline_edit_') && deleted.includes(id.replace('offline_edit_', ''))) return true;
    }
    return false;
};

window.queuePendingServerDelete = function (tripId) {
    if (!tripId || String(tripId).startsWith('offline_')) return;
    try {
        const sId = String(tripId).trim();
        let pending = JSON.parse(localStorage.getItem('intan_elyu_pending_delete_trips') || '[]');
        if (!Array.isArray(pending)) pending = [];
        if (!pending.map(String).includes(sId)) {
            pending.push(sId);
            localStorage.setItem('intan_elyu_pending_delete_trips', JSON.stringify(pending));
        }
    } catch (e) { }
};

// ══════════════════════════════════════════════════════════════════════════
// PERSISTENT COMPLETED TRIPS TRACKER & BLACKLIST
// ══════════════════════════════════════════════════════════════════════════
window.getCompletedTripIds = function () {
    try {
        const direct = JSON.parse(localStorage.getItem('intan_elyu_completed_trip_ids') || '[]');
        const queued = (window.OfflineCompletedTripsManager && typeof window.OfflineCompletedTripsManager.getQueue === 'function')
            ? window.OfflineCompletedTripsManager.getQueue().map(q => q.tripId)
            : [];
        return Array.from(new Set([...(Array.isArray(direct) ? direct : []), ...queued])).map(String);
    } catch (e) {
        return [];
    }
};

window.markTripAsCompletedLocally = function (tripId, earnedPoints = 0, newTotalPoints = null) {
    if (!tripId) return;
    try {
        const completed = window.getCompletedTripIds();
        const sId = String(tripId).trim();
        if (!completed.includes(sId)) {
            completed.push(sId);
            if (completed.length > 250) completed.shift();
            localStorage.setItem('intan_elyu_completed_trip_ids', JSON.stringify(completed));
        }

        // 1. Update auth_user points and completed_activities in localStorage
        try {
            const uStr = localStorage.getItem('auth_user');
            if (uStr) {
                const uObj = JSON.parse(uStr);
                const ptsNum = parseInt(earnedPoints, 10) || 0;
                if (newTotalPoints !== null && newTotalPoints !== undefined) {
                    uObj.points = parseInt(newTotalPoints, 10);
                    uObj.xp = parseInt(newTotalPoints, 10);
                } else if (ptsNum > 0) {
                    uObj.points = (parseInt(uObj.points, 10) || 0) + ptsNum;
                    uObj.xp = (parseInt(uObj.xp, 10) || 0) + ptsNum;
                }
                uObj.completed_activities = (parseInt(uObj.completed_activities, 10) || 0) + 1;
                localStorage.setItem('auth_user', JSON.stringify(uObj));
                localStorage.setItem('auth_user_backup', JSON.stringify(uObj));
            }
        } catch (e) { }

        // 2. Mark completed in intan_elyu_saved_trips
        try {
            let localSaved = JSON.parse(localStorage.getItem('intan_elyu_saved_trips') || '[]');
            if (Array.isArray(localSaved)) {
                localSaved = localSaved.map(t => {
                    if (t && (String(t.id) === sId || String(t.local_id) === sId)) {
                        return { ...t, status: 'completed' };
                    }
                    return t;
                });
                localStorage.setItem('intan_elyu_saved_trips', JSON.stringify(localSaved));
            }
        } catch (e) { }

        // 3. Mark completed in intan_elyu_offline_saved_trips_backup
        try {
            let backup = JSON.parse(localStorage.getItem('intan_elyu_offline_saved_trips_backup') || '[]');
            if (Array.isArray(backup)) {
                backup = backup.map(t => {
                    if (t && (String(t.id) === sId || String(t.local_id) === sId)) {
                        return { ...t, status: 'completed' };
                    }
                    return t;
                });
                localStorage.setItem('intan_elyu_offline_saved_trips_backup', JSON.stringify(backup));
            }
        } catch (e) { }

        // 4. Update in-memory window._cachedSavedTrips
        if (Array.isArray(window._cachedSavedTrips)) {
            window._cachedSavedTrips = window._cachedSavedTrips.map(t => {
                if (t && (String(t.id) === sId || String(t.local_id) === sId)) {
                    return { ...t, status: 'completed' };
                }
                return t;
            });
        }

        // 5. Remove from OfflineTripManager queue if queued
        if (window.OfflineTripManager && typeof window.OfflineTripManager.removeQueuedTrip === 'function') {
            try { window.OfflineTripManager.removeQueuedTrip(sId); } catch(e) {}
        }

        // 6. Purge token caches
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (token) {
            const tokenKey = token.substring(0, 10);
            localStorage.removeItem('saved_trips_' + tokenKey);
            localStorage.removeItem('dashboard_trips_' + tokenKey);
        }

        // 7. Update UI points ticker if present
        try {
            const uStr = localStorage.getItem('auth_user');
            if (uStr) {
                const uObj = JSON.parse(uStr);
                const ptTickers = document.querySelectorAll('#user-points-display, .header-points-badge, .user-points-badge, .points-count, [data-user-points]');
                ptTickers.forEach(el => {
                    if (el && uObj.points !== undefined) {
                        el.textContent = Number(uObj.points).toLocaleString();
                    }
                });
            }
        } catch(e) {}

        window.dispatchEvent(new CustomEvent('tripCompleted', { detail: { tripId: sId, earnedPoints: earnedPoints } }));
    } catch (e) { }
};

window.isTripCompletedLocally = function (trip) {
    if (!trip) return false;
    if (trip.status === 'completed') return true;
    const completed = window.getCompletedTripIds();
    if (!completed || completed.length === 0) return false;
    const ids = [
        String(trip.id || ''),
        String(trip.trip_id || ''),
        String(trip.local_id || ''),
        String(trip.update_id || '')
    ];
    for (const id of ids) {
        if (id && completed.includes(id)) return true;
        if (id && id.startsWith('offline_edit_') && completed.includes(id.replace('offline_edit_', ''))) return true;
    }
    return false;
};

// ══════════════════════════════════════════════════════════════════════════
// GUEST USER STATE & AUTH PROMPT UTILITIES
// ══════════════════════════════════════════════════════════════════════════
window.isGuestUser = function () {
    try {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        return !token ||
            token === 'guest_token' ||
            token === 'offline_guest_token' ||
            token.startsWith('guest_') ||
            token.startsWith('offline_') ||
            localStorage.getItem('intan_elyu_guest') === 'true' ||
            localStorage.getItem('intan_elyu_offline_guest') === 'true';
    } catch (e) {
        return false;
    }
};

window.promptGuestLogin = function (action = 'check-in') {
    let modal = document.getElementById('guest-auth-prompt-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'guest-auth-prompt-modal';
        modal.style.cssText = 'display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10, 25, 60, 0.55); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:9999999; justify-content:center; align-items:center; padding:20px; box-sizing:border-box;';
        modal.innerHTML = `
            <style>
                @keyframes popInGuestCard {
                    0% { opacity: 0; transform: scale(0.92) translateY(12px); }
                    100% { opacity: 1; transform: scale(1) translateY(0); }
                }
                #guest-auth-prompt-modal .guest-card {
                    background: #ffffff !important;
                    border-radius: 26px;
                    width: 100%;
                    max-width: 380px;
                    overflow: hidden;
                    box-shadow: 0 20px 48px rgba(10, 25, 60, 0.35);
                    border: 1px solid rgba(226, 232, 240, 0.9);
                    animation: popInGuestCard 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                    display: flex;
                    flex-direction: column;
                }
            </style>
            <div class="guest-card" onclick="event.stopPropagation()">
                <!-- Header Banner -->
                <div style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 18px 20px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.16); display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 16px;">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">Account Required</h3>
                            <span style="font-size: 11px; color: #bfdbfe; font-weight: 600;">Sign in to unlock all features</span>
                        </div>
                    </div>
                    <button type="button" onclick="window.closeGuestAuthModal()" style="background: rgba(255,255,255,0.18); border: none; color: #ffffff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body Content -->
                <div style="padding: 22px 20px; text-align: center;">
                    <div style="width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1.5px solid #bfdbfe; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; color: #0284c7; font-size: 28px; box-shadow: 0 8px 20px rgba(2, 132, 199, 0.15);">
                        <i id="guest-auth-icon" class="fa-solid fa-location-crosshairs"></i>
                    </div>

                    <h4 id="guest-auth-title" style="margin: 0 0 8px 0; font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px;">Check-in & Photo Upload</h4>
                    <p id="guest-auth-desc" style="margin: 0 0 18px 0; font-size: 13px; color: #475569; line-height: 1.5; font-weight: 500;">
                        You are currently in <strong>Guest Mode</strong>. While you can plan and customize trips freely, checking in and uploading photo proof requires a registered account.
                    </p>

                    <!-- Feature Perks Card -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 12px 14px; text-align: left; margin-bottom: 20px; display: flex; flex-direction: column; gap: 9px;">
                        <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: #334155; font-weight: 700;">
                            <i class="fa-solid fa-award" style="color: #f59e0b; width: 16px; text-align: center;"></i>
                            <span>Earn travel XP, points & leaderboard rank</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: #334155; font-weight: 700;">
                            <i class="fa-solid fa-camera-retro" style="color: #0284c7; width: 16px; text-align: center;"></i>
                            <span>Verify your visits with photo stamps</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: #334155; font-weight: 700;">
                            <i class="fa-solid fa-cloud-arrow-up" style="color: #10b981; width: 16px; text-align: center;"></i>
                            <span>All your planned trips sync automatically</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; flex-direction: column; gap: 9px;">
                        <button type="button" onclick="window.proceedToAuthFromGuest()" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border: none; outline: none; border-radius: 14px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> Log In or Register
                        </button>
                        <button type="button" onclick="window.closeGuestAuthModal()" style="width: 100%; padding: 12px; background: #ffffff; color: #64748b; border: 1.5px solid #cbd5e1; outline: none; border-radius: 14px; font-size: 13px; font-weight: 700; cursor: pointer; transition: background 0.15s ease;">
                            Continue as Guest
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const isPhoto = action === 'photo' || action === 'upload' || action === 'proof';
    const iconEl = document.getElementById('guest-auth-icon');
    const titleEl = document.getElementById('guest-auth-title');
    const descEl = document.getElementById('guest-auth-desc');

    if (iconEl) {
        iconEl.className = isPhoto ? 'fa-solid fa-camera' : 'fa-solid fa-location-crosshairs';
    }
    if (titleEl) {
        titleEl.textContent = isPhoto ? 'Photo Upload Requires Login' : 'Check-in Requires Login';
    }
    if (descEl) {
        descEl.innerHTML = isPhoto
            ? 'Guests can plan and navigate trips freely! To <strong>upload photo proof</strong> and verify your visits, please log in or create an account.'
            : 'Guests can plan and navigate trips freely! To <strong>check in at destinations</strong> and earn travel XP, please log in or create an account.';
    }

    modal.style.display = 'flex';
};

window.closeGuestAuthModal = function () {
    const modal = document.getElementById('guest-auth-prompt-modal');
    if (modal) modal.style.display = 'none';
};

window.proceedToAuthFromGuest = function () {
    window.closeGuestAuthModal();
    if (typeof navigateTo === 'function') {
        navigateTo('auth');
    }
};

window.syncAllOfflineTripData = async function (btnEl = null) {
    if (!navigator.onLine) {
        if (typeof showToast === 'function') {
            showToast("You are currently offline. Connect to internet to sync.", "warning", 3500);
        }
        return false;
    }

    const origHtml = btnEl ? btnEl.innerHTML : '';
    if (btnEl) {
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:4px;"></i> Syncing...';
    }

    try {
        let totalCount = 0;
        // 1. Sync queued offline trips / trip updates
        if (window.OfflineTripManager && typeof window.OfflineTripManager.syncQueuedTrips === 'function') {
            const tCount = await window.OfflineTripManager.syncQueuedTrips();
            totalCount += (tCount || 0);
        }
        // 2. Sync queued offline check-ins & photo proofs
        if (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.processQueue === 'function') {
            const cCount = await window.OfflineCheckinManager.processQueue();
            totalCount += (cCount || 0);
        }
        // 3. Sync offline completed trips
        if (window.OfflineCompletedTripsManager && typeof window.OfflineCompletedTripsManager.processQueue === 'function') {
            const compCount = await window.OfflineCompletedTripsManager.processQueue();
            totalCount += (compCount || 0);
        }

        if (typeof showToast === 'function') {
            showToast(totalCount > 0 ? `Successfully synced ${totalCount} offline items to cloud!` : "Offline data synced with cloud!", "success", 4000);
        }

        window.dispatchEvent(new CustomEvent('tripsSynced'));
        window.dispatchEvent(new CustomEvent('checkins-synced'));

        if (typeof window.fetchSavedTrips === 'function') {
            window.fetchSavedTrips(true);
        }
        if (typeof window.loadTripData === 'function') {
            window.loadTripData();
        }

        return true;
    } catch (err) {
        console.error("Full offline sync failed:", err);
        if (typeof showToast === 'function') {
            showToast("Sync failed. Check connection and try again.", "error", 4000);
        }
        return false;
    } finally {
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.innerHTML = origHtml;
        }
    }
};

// ══════════════════════════════════════════════════════════════════════════
// OFFLINE PLANNING STRATEGY: OFFLINE TRIP & ITINERARY MANAGER
// ══════════════════════════════════════════════════════════════════════════
window.OfflineTripManager = {
    STORAGE_KEY: 'intan_elyu_offline_saved_trips',
    _isSyncing: false,

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

            // Prevent duplicate queued trips with identical title and destinations
            const destStr = JSON.stringify(tripData.destinations || []);
            const existingDuplicate = trips.findIndex(t => t && t.title === tripData.title && JSON.stringify(t.destinations || []) === destStr);
            if (existingDuplicate !== -1 && !updateId) {
                trips[existingDuplicate] = {
                    ...trips[existingDuplicate],
                    ...tripData,
                    updated_at: new Date().toISOString()
                };
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(trips));
                return trips[existingDuplicate];
            }

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
        if (!token || (typeof window.isGuestUser === 'function' && window.isGuestUser())) return false;

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

        const cleanDests = (trip.destinations || []).map(id => {
            if (typeof id === 'object' && id) return parseInt(id.tourist_spot_id || id.destination_id || id.id || 0, 10);
            const p = parseInt(id, 10);
            return isNaN(p) ? 0 : p;
        }).filter(id => id > 0);

        const payload = {
            title: trip.title,
            trip_date: trip.trip_date || null,
            budget: trip.budget || null,
            destinations: cleanDests,
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
        if (this._isSyncing) return 0;
        this._isSyncing = true;

        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            if (!token || (typeof window.isGuestUser === 'function' && window.isGuestUser())) return 0;

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

            // Process pending server deletions first
            try {
                let pendingDeletes = JSON.parse(localStorage.getItem('intan_elyu_pending_delete_trips') || '[]');
                if (Array.isArray(pendingDeletes) && pendingDeletes.length > 0) {
                    const remainingDeletes = [];
                    for (const delId of pendingDeletes) {
                        let delDone = false;
                        for (const bUrl of baseCandidates) {
                            try {
                                const dRes = await fetch(`${bUrl}/api/tourist/itineraries/${delId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Authorization': 'Bearer ' + token,
                                        'Accept': 'application/json',
                                        'ngrok-skip-browser-warning': 'true'
                                    }
                                });
                                if (dRes.ok || dRes.status === 404) {
                                    delDone = true;
                                    break;
                                }
                            } catch(e) {}
                        }
                        if (!delDone) remainingDeletes.push(delId);
                    }
                    localStorage.setItem('intan_elyu_pending_delete_trips', JSON.stringify(remainingDeletes));
                }
            } catch(e) {}

            const trips = this.getQueuedTrips();
            if (!trips || trips.length === 0) return 0;

            const deletedIds = (typeof window.getDeletedTripIds === 'function') ? window.getDeletedTripIds() : [];
            let syncedCount = 0;

            for (const trip of trips) {
                // If trip was deleted while offline or queued, do NOT sync! Purge it from queue immediately.
                if (!trip || (deletedIds.length > 0 && (
                    deletedIds.includes(String(trip.id || '')) ||
                    deletedIds.includes(String(trip.local_id || '')) ||
                    deletedIds.includes(String(trip.update_id || ''))
                ))) {
                    this.removeQueuedTrip(trip ? trip.id : null);
                    continue;
                }

                try {
                    const cleanDests = (trip.destinations || []).map(id => {
                        if (typeof id === 'object' && id) return parseInt(id.tourist_spot_id || id.destination_id || id.id || 0, 10);
                        const p = parseInt(id, 10);
                        return isNaN(p) ? 0 : p;
                    }).filter(id => id > 0);

                    const payload = {
                        title: trip.title,
                        trip_date: trip.trip_date || null,
                        budget: trip.budget || null,
                        destinations: cleanDests,
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
        } finally {
            this._isSyncing = false;
        }
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
                    try {
                        const data = await res.json();
                        if (typeof window.markTripAsCompletedLocally === 'function') {
                            window.markTripAsCompletedLocally(item.tripId, data?.points_awarded || 0, data?.total_points);
                        }
                    } catch(e) {
                        if (typeof window.markTripAsCompletedLocally === 'function') {
                            window.markTripAsCompletedLocally(item.tripId, 0, null);
                        }
                    }
                } else if (res.status >= 400 && res.status < 500 && res.status !== 408) {
                    // permanent client error (e.g. 404 deleted)
                    if (typeof window.markTripAsCompletedLocally === 'function') {
                        window.markTripAsCompletedLocally(item.tripId, 0, null);
                    }
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

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
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
                    // No real account to restore: ensure the guest doesn't inherit a previous user's cached profile
                    try {
                        const cachedUser = JSON.parse(localStorage.getItem('auth_user') || 'null');
                        if (!cachedUser || !cachedUser.is_guest || cachedUser.is_offline_guest) {
                            localStorage.setItem('auth_user', JSON.stringify({
                                id: 'guest',
                                user_id: 'guest',
                                name: 'Guest Explorer',
                                full_name: 'Guest Explorer',
                                role: 'tourist',
                                points: 0,
                                xp: 0,
                                completed_activities: 0,
                                home_location: 'La Union',
                                is_guest: true
                            }));
                        }
                    } catch (e) { }
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

// ══════════════════════════════════════════════════════════════════════════
// GLOBAL SAVED TRIPS RUNTIME & PERSISTENCE (WORKS IN NATIVE APK & WEB)
// ══════════════════════════════════════════════════════════════════════════
const SAVED_TRIPS_VEHICLE_CATALOG = {
    'own_car': { name: 'Own Car', icon: 'fa-car', color: '#f59e0b' },
    'motorcycle': { name: 'Motorcycle', icon: 'fa-motorcycle', color: '#f97316' },
    'van': { name: 'Van', icon: 'fa-van-shuttle', color: '#a855f7' },
    'mpuj': { name: 'Modern Jeepney', icon: 'fa-bus-simple', color: '#10b981' },
    'tpuj': { name: 'Traditional Jeepney', icon: 'fa-van-shuttle', color: '#f59e0b' },
    'jeepney': { name: 'Jeepney', icon: 'fa-van-shuttle', color: '#38bdf8' },
    'tricycle': { name: 'Tricycle', icon: 'fa-motorcycle', color: '#10b981' },
    'pub_regular': { name: 'Ordinary Bus', icon: 'fa-bus-simple', color: '#06b6d4' },
    'pub_ordinary': { name: 'Ordinary Bus', icon: 'fa-bus-simple', color: '#06b6d4' },
    'pub_aircon': { name: 'Aircon Bus', icon: 'fa-bus', color: '#38bdf8' },
    'bus': { name: 'Bus', icon: 'fa-bus', color: '#38bdf8' },
    'private_bus': { name: 'Aircon Bus', icon: 'fa-bus', color: '#38bdf8' },
    'mini_bus': { name: 'Mini Bus', icon: 'fa-bus-simple', color: '#06b6d4' },
    'lutrampco': { name: 'Modern Jeepney', icon: 'fa-bus-simple', color: '#38bdf8' },
    'uve': { name: 'UV Express', icon: 'fa-van-shuttle', color: '#a855f7' },
    'taxi': { name: 'Taxi', icon: 'fa-taxi', color: '#eab308' },
    'walking': { name: 'Walk / Hike', icon: 'fa-person-walking', color: '#22c55e' }
};

function parseVehicleModesGlobal(str) {
    if (!str) return [];
    if (Array.isArray(str)) return str.map(s => String(s).trim().toLowerCase().replace(/[- ]/g, '_')).filter(Boolean);
    return String(str)
        .split(/[,+]|\band\b/i)
        .map(s => s.trim().toLowerCase().replace(/^[-_\s]+|[-_\s]+$/g, '').replace(/[- ]+/g, '_').replace(/_+/g, '_'))
        .filter(Boolean);
}

function getVehicleInfoGlobal(key) {
    const norm = String(key || '').toLowerCase().trim().replace(/[- ]/g, '_');
    if (norm === 'no_vehicle' || norm === 'none' || norm === 'no_vehicle_selected' || norm === '') {
        return {
            name: 'No Vehicle Selected',
            icon: 'fa-ban',
            color: '#f87171'
        };
    }
    if (SAVED_TRIPS_VEHICLE_CATALOG[norm]) return SAVED_TRIPS_VEHICLE_CATALOG[norm];
    if (norm === 'car' || norm === 'own_car' || norm === 'private_car') return SAVED_TRIPS_VEHICLE_CATALOG['own_car'];
    if (norm === 'trike') return SAVED_TRIPS_VEHICLE_CATALOG['tricycle'];
    if (norm.includes('aircon') || norm.includes('pub_aircon')) return SAVED_TRIPS_VEHICLE_CATALOG['pub_aircon'];
    if (norm.includes('ordinary') || norm.includes('regular')) return SAVED_TRIPS_VEHICLE_CATALOG['pub_regular'];
    if (norm.includes('bus')) return SAVED_TRIPS_VEHICLE_CATALOG['pub_aircon'];
    if (norm.includes('mpuj') || norm.includes('modern')) return SAVED_TRIPS_VEHICLE_CATALOG['mpuj'];
    if (norm.includes('tpuj') || norm.includes('traditional')) return SAVED_TRIPS_VEHICLE_CATALOG['tpuj'];
    if (norm.includes('motor')) return SAVED_TRIPS_VEHICLE_CATALOG['motorcycle'];
    return {
        name: norm.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
        icon: 'fa-route',
        color: '#38bdf8'
    };
}

function getSavedTripTransportBadgeGlobal(rawMode, trip = null) {
    let mode = rawMode;
    if (trip && Array.isArray(trip.items) && trip.items.length > 0) {
        const itemModes = trip.items
            .map(it => (it.transport_mode || it.transport_type || '').trim())
            .filter(m => m && m !== 'suspended');
        const validNonEmptyModes = itemModes.filter(m => m !== 'no_vehicle' && !m.toLowerCase().includes('no vehicle'));
        if (validNonEmptyModes.length > 0) {
            const uniqueModes = Array.from(new Set(validNonEmptyModes));
            if (mode === 'own_car' || !mode || mode === 'No Vehicle Selected' || mode === 'no_vehicle') {
                mode = uniqueModes.join(' + ');
            }
        } else if (itemModes.length > 0 && itemModes.every(m => m === 'no_vehicle' || m.toLowerCase().includes('no vehicle'))) {
            mode = 'No Vehicle Selected';
        }
    }

    if (!mode || String(mode).toLowerCase().includes('no_vehicle') || String(mode).toLowerCase().includes('no vehicle') || String(mode).toLowerCase() === 'none') {
        return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid fa-ban" style="font-size:11px; color:#f87171;"></i>No Vehicle Selected</span>`;
    }

    const parts = parseVehicleModesGlobal(mode).filter(p => p !== 'no_vehicle' && !p.includes('no_vehicle'));
    if (parts.length === 0) {
        return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid fa-ban" style="font-size:11px; color:#f87171;"></i>No Vehicle Selected</span>`;
    }

    if (parts.length > 1) {
        return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid fa-route" style="font-size:11px; color:#38bdf8;"></i>${parts.length} Vehicles</span>`;
    }

    const info = getVehicleInfoGlobal(parts[0]);
    return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid ${info.icon}" style="font-size:11px; color:${info.color};"></i>${info.name}</span>`;
}

if (typeof window.getTouristSpotById !== 'function') {
    window.getTouristSpotById = function (id) {
        if (!id) return null;
        const sId = String(id);
        if (window._cachedSpotsMap && window._cachedSpotsMap[sId]) {
            return window._cachedSpotsMap[sId];
        }
        const sources = [window.destinations, window.allDestinations, window.spotsList];
        for (const arr of sources) {
            if (Array.isArray(arr)) {
                const found = arr.find(s => s && (String(s.id) === sId || String(s.tourist_spot_id) === sId));
                if (found) return found;
            }
        }
        try {
            const rawMap = localStorage.getItem('public_map_data');
            if (rawMap) {
                const parsedMap = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(rawMap, null) : JSON.parse(rawMap);
                const spotsArr = (parsedMap && parsedMap.data && parsedMap.data.destinations) ? parsedMap.data.destinations : (parsedMap && parsedMap.destinations ? parsedMap.destinations : []);
                if (Array.isArray(spotsArr)) {
                    const found = spotsArr.find(s => s && (String(s.id) === sId || String(s.tourist_spot_id) === sId));
                    if (found) return found;
                }
            }
            const rawSpots = localStorage.getItem('intan_elyu_spots') || localStorage.getItem('all_destinations');
            if (rawSpots) {
                const parsed = JSON.parse(rawSpots);
                const list = Array.isArray(parsed) ? parsed : (parsed.data || []);
                if (Array.isArray(list)) {
                    const found = list.find(s => s && (String(s.id) === sId || String(s.tourist_spot_id) === sId));
                    if (found) return found;
                }
            }
        } catch (e) { }
        return null;
    };
}

if (typeof window.getPersistentItemStates !== 'function') {
    window.getPersistentItemStates = function () {
        try {
            return JSON.parse(localStorage.getItem('intan_elyu_verified_item_states') || '{}');
        } catch (e) {
            return {};
        }
    };
}

if (typeof window.savePersistentItemState !== 'function') {
    window.savePersistentItemState = function (itemId, stateUpdates) {
        if (!itemId) return;
        try {
            const states = window.getPersistentItemStates();
            const key = String(itemId);
            const existing = states[key] || {};
            states[key] = {
                ...existing,
                ...stateUpdates,
                updated_at: Date.now()
            };
            if (stateUpdates && stateUpdates.spot_id) {
                states['spot_' + stateUpdates.spot_id] = states[key];
            }
            localStorage.setItem('intan_elyu_verified_item_states', JSON.stringify(states));
        } catch (e) {
            console.warn('savePersistentItemState error:', e);
        }
    };
}

if (typeof window.resolveProofImageUrl !== 'function') {
    window.resolveProofImageUrl = function (rawUrl) {
        if (!rawUrl || typeof rawUrl !== 'string') return '';
        var url = rawUrl.trim();
        if (!url) return '';
        if (url.startsWith('data:') || url.startsWith('blob:')) return url;
        var activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
        var proofMatch = url.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
        if (proofMatch && proofMatch[1]) return activeBackend + '/api/image/proof_images/' + proofMatch[1];
        if (url.startsWith('http://') || url.startsWith('https://')) {
            if (url.includes('r2.dev') || url.includes('localhost') || url.includes('127.0.0.1')) {
                var clean = url.split('/').pop().replace(/[?#].*$/, '');
                var localMatch = clean.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                if (localMatch && localMatch[1]) return activeBackend + '/api/image/proof_images/' + localMatch[1];
                return activeBackend + '/api/image/proof_images/' + clean;
            }
            return url;
        }
        var clean = url.replace(/^\/+/, '');
        if (clean.startsWith('storage/proof_images/')) return activeBackend + '/api/image/proof_images/' + clean.replace('storage/proof_images/', '');
        if (clean.startsWith('proof_images/')) return activeBackend + '/api/image/proof_images/' + clean.replace('proof_images/', '');
        if (clean.startsWith('api/')) return activeBackend + '/' + clean;
        return activeBackend + '/api/image/proof_images/' + clean.split('/').pop().replace(/[?#].*$/, '');
    };
}

if (typeof window.getProofImageFallbackUrl !== 'function') {
    window.getProofImageFallbackUrl = function (rawUrl) {
        if (!rawUrl || typeof rawUrl !== 'string') return 'assets/img/no_image.svg';
        var url = rawUrl.trim();
        if (url.startsWith('data:') || url.startsWith('blob:')) return url;
        var activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
        var proofMatch = url.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
        if (proofMatch && proofMatch[1]) return activeBackend + '/api/image/proof_images/' + proofMatch[1];
        var clean = url.replace(/^\/+/, '').split('/').pop().replace(/[?#].*$/, '');
        if (clean) return activeBackend + '/api/image/proof_images/' + clean;
        return 'assets/img/no_image.svg';
    };
}

window.fetchSavedTrips = async function (forceRefresh = false) {
    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    const isGuest = !token || token === 'guest_token' || token.startsWith('guest_') || token.startsWith('offline_') || localStorage.getItem('intan_elyu_guest') === 'true';
    const isOfflineMode = localStorage.getItem('intan_elyu_offline_mode') === 'true' || localStorage.getItem('intan_elyu_offline_guest') === 'true';
    const isNetworkDown = (typeof navigator.onLine === 'boolean' && !navigator.onLine)
        || window.isNetworkOffline === true
        || (window.isAppOnline === false);
    const isOffline = isNetworkDown || isOfflineMode || isGuest;

    function getLocalTripsCombined() {
        const combined = [];
        const seenIds = new Set();
        const addTrip = (t) => {
            if (!t) return;
            const id = String(t.id || t.local_id || t.trip_id || '');
            if (id) {
                if (seenIds.has(id)) return;
                seenIds.add(id);
                if (id.startsWith('offline_edit_')) seenIds.add(id.replace('offline_edit_', ''));
            }
            combined.push(t);
        };

        // A. Offline queued trips
        if (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function') {
            try {
                const queued = window.OfflineTripManager.getQueuedTrips();
                if (Array.isArray(queued)) queued.forEach(addTrip);
            } catch(e) {}
        }

        // B. Token-specific cache
        if (token) {
            try {
                const tokenKey = 'saved_trips_' + token.substring(0, 10);
                const raw = localStorage.getItem(tokenKey);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    const list = Array.isArray(parsed) ? parsed : (parsed.data || parsed.itineraries || []);
                    if (Array.isArray(list)) list.forEach(addTrip);
                }
            } catch(e) {}
        }

        // C. Local storage backups
        ['intan_elyu_saved_trips', 'intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips'].forEach(k => {
            try {
                const raw = localStorage.getItem(k);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    const list = Array.isArray(parsed) ? parsed : (parsed.data || parsed.itineraries || []);
                    if (Array.isArray(list)) list.forEach(addTrip);
                }
            } catch(e) {}
        });

        return combined.filter(t => {
            const isDeleted = (typeof window.isTripDeleted === 'function') ? window.isTripDeleted(t) : false;
            const isCompleted = (typeof window.isTripCompletedLocally === 'function') ? window.isTripCompletedLocally(t) : (t && t.status === 'completed');
            return !isDeleted && !isCompleted;
        });
    }

    // 1. Instant pre-hydrate and render immediately with 0ms delay!
    const localTrips = getLocalTripsCombined();
    if (typeof window.renderSavedTrips === 'function') {
        window.renderSavedTrips(localTrips);
    }

    // 2. If offline, ensure offline badge displays and do NOT attempt blocking network fetch
    if (isOffline) {
        const badge = document.getElementById('saved-trips-offline-badge');
        if (badge) {
            badge.style.setProperty('display', 'inline-flex', 'important');
        }
        return;
    }

    if (!token || token.startsWith('guest_') || token.startsWith('offline_')) {
        return;
    }

    // 3. Online mode: fetch fresh trips from API and update local caches
    const cacheKey = 'saved_trips_' + token.substring(0, 10);
    const fetchCache = (typeof window.useCache === 'function') ? window.useCache : (async (key, fetcher, renderer, forceRefresh) => {
        try {
            const d = await fetcher();
            if (typeof renderer === 'function') renderer(d);
            return d;
        } catch (e) {
            if (typeof renderer === 'function') renderer(null);
        }
    });

    try {
        await fetchCache(
            cacheKey,
            async () => {
                const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                const timeoutId = controller ? setTimeout(() => controller.abort(), 8000) : null;
                try {
                    const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                    const response = await fetch(bUrl + '/api/tourist/itineraries', {
                        signal: controller ? controller.signal : undefined,
                        headers: {
                            'Accept': 'application/json',
                            'ngrok-skip-browser-warning': 'true',
                            'Authorization': 'Bearer ' + token
                        }
                    });
                    if (response.status === 401) {
                        localStorage.removeItem('intan_elyu_token');
                        localStorage.removeItem('Intan_Elyu_Token');
                        if (typeof showToast === 'function') showToast("Session expired. Please log in again.");
                        if (typeof window.navigateTo === 'function') window.navigateTo('auth');
                        return [];
                    }
                    if (!response.ok) throw new Error("Failed to fetch saved trips: " + response.status);
                    const data = await response.json();
                    const itins = data.itineraries || [];
                    const persistentStates = (typeof window.getPersistentItemStates === 'function') ? window.getPersistentItemStates() : {};
                    const queuedCheckins = (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.getQueue === 'function')
                        ? window.OfflineCheckinManager.getQueue()
                        : [];

                    const mergedItins = itins.map(trip => {
                        if (!trip || !Array.isArray(trip.items)) return trip;
                        return {
                            ...trip,
                            items: trip.items.map(it => {
                                if (!it) return it;
                                const itKey = String(it.id || '');
                                const spotIdKey = String(it.tourist_spot_id || it.destination_id || (it.destination ? it.destination.id : ''));
                                const spotKey = 'spot_' + spotIdKey;
                                const override = persistentStates[itKey] || (spotIdKey ? persistentStates[spotKey] : null);

                                const queued = queuedCheckins.find(q => {
                                    if (!q) return false;
                                    if (q.itemId && String(q.itemId) === itKey) return true;
                                    if (q.spotId && spotIdKey && String(q.spotId) === spotIdKey) return true;
                                    return false;
                                });

                                let inMemoryItem = null;
                                if (Array.isArray(window._cachedSavedTrips)) {
                                    for (const t of window._cachedSavedTrips) {
                                        if (t && Array.isArray(t.items)) {
                                            const match = t.items.find(m => m && (String(m.id) === itKey || (spotIdKey && String(m.tourist_spot_id || m.destination_id || (m.destination ? m.destination.id : '')) === spotIdKey)));
                                            if (match && (match.proof_image || (Array.isArray(match.proof_images) && match.proof_images.length > 0))) {
                                                inMemoryItem = match;
                                                break;
                                            }
                                        }
                                    }
                                }

                                const serverProofs = (Array.isArray(it.proof_images) && it.proof_images.length > 0)
                                    ? it.proof_images
                                    : (it.proof_image ? [it.proof_image] : []);

                                const localProofs = (override && Array.isArray(override.proof_images) && override.proof_images.length > 0)
                                    ? override.proof_images
                                    : ((override && override.proof_image) ? [override.proof_image] : []);

                                const queuedProofs = (queued && (queued.previewUrl || queued.image))
                                    ? [queued.previewUrl || queued.image]
                                    : [];

                                const inMemProofs = (inMemoryItem && Array.isArray(inMemoryItem.proof_images) && inMemoryItem.proof_images.length > 0)
                                    ? inMemoryItem.proof_images
                                    : ((inMemoryItem && inMemoryItem.proof_image) ? [inMemoryItem.proof_image] : []);

                                const activeProofs = serverProofs.length > 0
                                    ? serverProofs
                                    : (queuedProofs.length > 0 ? queuedProofs : (localProofs.length > 0 ? localProofs : inMemProofs));

                                const activeStatus = (serverProofs.length > 0 && it.proof_status)
                                    ? it.proof_status
                                    : (it.proof_status || (override ? override.proof_status : null) || (queued ? 'pending' : null) || (inMemoryItem ? inMemoryItem.proof_status : null) || (activeProofs.length > 0 ? 'pending' : null));

                                return {
                                    ...it,
                                    is_location_verified: Boolean(it.is_location_verified || it.visited_at || (override && (override.is_location_verified || override.visited_at)) || (queued && queued.status) || (inMemoryItem && inMemoryItem.is_location_verified)),
                                    visited_at: it.visited_at || (override ? override.visited_at : null) || (queued ? queued.capturedAt : null) || (inMemoryItem ? inMemoryItem.visited_at : null),
                                    proof_image: activeProofs.length > 0 ? activeProofs[0] : null,
                                    proof_images: activeProofs,
                                    proof_status: activeStatus,
                                    is_offline_queued: Boolean(queued || (override && override.is_queued) || (inMemoryItem && inMemoryItem.is_offline_queued))
                                };
                            })
                        };
                    });
                    try {
                        localStorage.setItem('intan_elyu_offline_saved_trips_backup', JSON.stringify(mergedItins));
                    } catch (e) { }
                    return mergedItins;
                } finally {
                    if (timeoutId) clearTimeout(timeoutId);
                }
            },
            (itineraries) => {
                if (window._isStartingTrip) return;
                if (itineraries && Array.isArray(itineraries)) {
                    window.renderSavedTrips(itineraries);
                } else {
                    window.renderSavedTrips(getLocalTripsCombined());
                }
            },
            forceRefresh,
            60000
        );
    } catch (fetchErr) {
        console.warn("fetchSavedTrips handled offline fallback:", fetchErr);
        if (typeof window.renderSavedTrips === 'function') {
            window.renderSavedTrips(getLocalTripsCombined());
        }
    }
};
window.loadSavedTrips = window.fetchSavedTrips;

window.updateSavedTripsScrollview = function () {
    const container = document.getElementById('saved-trips-container');
    const list = document.getElementById('saved-trips-list');
    if (!container || !list) return;

    const cards = list.querySelectorAll('.trip-swipe-container');
    const hasEmptyState = list.querySelector('.empty-state-card');

    if (hasEmptyState || cards.length === 0) {
        container.classList.remove('is-scrollable');
        container.scrollTop = 0;
        return;
    }

    requestAnimationFrame(() => {
        const isOverflowing = cards.length > 1 || list.scrollHeight > (container.clientHeight - 30);
        if (isOverflowing) {
            container.classList.add('is-scrollable');
        } else {
            container.classList.remove('is-scrollable');
            container.scrollTop = 0;
        }
    });
};
window.addEventListener('resize', window.updateSavedTripsScrollview);

window.renderSavedTrips = function (itineraries) {
    try {
        const queuedTrips = (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function')
            ? window.OfflineTripManager.getQueuedTrips()
            : [];

        const queuedNewTrips = queuedTrips.filter(t => !t.is_offline_update);
        const queuedUpdates = queuedTrips.filter(t => t.is_offline_update && t.update_id);
        const updatesMap = {};
        queuedUpdates.forEach(u => { updatesMap[String(u.update_id)] = u; });

        const localItemProofs = {};
        const harvestProofsFromTrips = (trips) => {
            if (!Array.isArray(trips)) return;
            trips.forEach(t => {
                if (!t || !Array.isArray(t.items)) return;
                t.items.forEach(it => {
                    if (!it || !it.id) return;
                    const itId = String(it.id);
                    const proofs = (Array.isArray(it.proof_images) && it.proof_images.length)
                        ? it.proof_images
                        : (it.proof_image ? [it.proof_image] : []);
                    const hasProofs = (proofs.length > 0);
                    const hasCheckin = Boolean(it.is_location_verified || it.visited_at);
                    if (hasProofs || hasCheckin) {
                        localItemProofs[itId] = {
                            proof_image: it.proof_image || proofs[0] || null,
                            proof_images: proofs,
                            proof_status: it.proof_status || (hasProofs ? 'pending' : null),
                            is_location_verified: hasCheckin,
                            visited_at: it.visited_at || null
                        };
                    }
                });
            });
        };

        try {
            const persistentStates = (typeof window.getPersistentItemStates === 'function') ? window.getPersistentItemStates() : {};
            Object.keys(persistentStates).forEach(k => {
                const st = persistentStates[k];
                if (!st) return;
                if (!localItemProofs[k]) {
                    localItemProofs[k] = {
                        proof_image: st.proof_image || null,
                        proof_images: st.proof_images || (st.proof_image ? [st.proof_image] : []),
                        proof_status: st.proof_status || 'pending',
                        is_location_verified: Boolean(st.is_location_verified || st.visited_at),
                        visited_at: st.visited_at || null
                    };
                } else {
                    localItemProofs[k].is_location_verified = Boolean(localItemProofs[k].is_location_verified || st.is_location_verified || st.visited_at);
                    if (!localItemProofs[k].visited_at && st.visited_at) localItemProofs[k].visited_at = st.visited_at;
                    if ((!localItemProofs[k].proof_images || localItemProofs[k].proof_images.length === 0) && st.proof_images && st.proof_images.length > 0) {
                        localItemProofs[k].proof_images = st.proof_images;
                        localItemProofs[k].proof_image = st.proof_image || st.proof_images[0];
                        localItemProofs[k].proof_status = st.proof_status || 'pending';
                    }
                }
            });
        } catch (e) { }

        harvestProofsFromTrips(window._cachedSavedTrips);

        let serverTrips = (Array.isArray(itineraries) ? itineraries : []).map(t => {
            let tripObj = t;
            if (updatesMap[String(t.id)]) {
                tripObj = {
                    ...t,
                    ...updatesMap[String(t.id)],
                    id: t.id,
                    is_offline_queued: true,
                    is_offline_update: true
                };
            }
            if (tripObj && Array.isArray(tripObj.items)) {
                tripObj.items = tripObj.items.map(it => {
                    if (!it) return it;
                    const itId = String(it.id);
                    const spotIdKey = String(it.tourist_spot_id || it.destination_id || (it.destination ? it.destination.id : ''));
                    const saved = localItemProofs[itId] || (spotIdKey ? localItemProofs[spotIdKey] : null);
                    if (saved) {
                        const currentProofs = (Array.isArray(it.proof_images) && it.proof_images.length)
                            ? it.proof_images
                            : (it.proof_image ? [it.proof_image] : []);
                        const mergedProofs = currentProofs.length > 0 ? currentProofs : saved.proof_images;
                        return {
                            ...it,
                            proof_image: (mergedProofs && mergedProofs.length > 0) ? mergedProofs[0] : (saved.proof_image || it.proof_image),
                            proof_images: mergedProofs || [],
                            proof_status: (it.proof_status && it.proof_status !== 'pending' && it.proof_status !== null) ? it.proof_status : (saved.proof_status || 'pending'),
                            is_location_verified: Boolean(it.is_location_verified || saved.is_location_verified || it.visited_at || saved.visited_at),
                            visited_at: it.visited_at || saved.visited_at,
                            is_offline_queued: Boolean(saved.is_queued)
                        };
                    }
                    return it;
                });
            }
            return tripObj;
        });

        const combinedTrips = [...queuedNewTrips, ...serverTrips].filter(t => !window.isTripDeleted(t));
        const seenTripIds = new Set();
        const dedupedTrips = combinedTrips.filter(t => {
            if (!t) return false;
            const tId = String(t.id || t.local_id || t.trip_id || '');
            if (tId) {
                if (seenTripIds.has(tId)) return false;
                seenTripIds.add(tId);
                if (tId.startsWith('offline_edit_')) seenTripIds.add(tId.replace('offline_edit_', ''));
            }
            return true;
        });
        window._cachedSavedTrips = dedupedTrips;
        const list = document.getElementById('saved-trips-list');
        if (!list) return;

        const emptyStateHtml = `
            <div class="empty-state-card" style="margin-top: 30px; margin-bottom: 30px; opacity: 1 !important; visibility: visible !important;">
                <div class="empty-state-icon" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                    <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                </div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff; text-wrap: balance;">No Saved Trips Yet</h3>
                <p style="margin: 0; font-size: 13.5px; color: rgba(255, 255, 255, 0.88); line-height: 1.5; max-width: 260px; text-wrap: pretty;">
                    Your adventure starts here! Explore La Union tourist spots and plan your personalized itinerary.
                </p>
                <button type="button" class="btn-open-map" onclick="if(typeof window.navigateTo==='function') window.navigateTo('itinerary');">
                    <i class="fa-solid fa-plus"></i> Create New Trip
                </button>
            </div>
        `;

        if (!dedupedTrips || dedupedTrips.length === 0) {
            list.innerHTML = emptyStateHtml;
            if (typeof window.updateSavedTripsScrollview === 'function') window.updateSavedTripsScrollview();
            return;
        }

        let activeItineraries = dedupedTrips.filter(trip => trip && trip.status !== 'completed' && !(typeof window.isTripCompletedLocally === 'function' && window.isTripCompletedLocally(trip)));

        if (activeItineraries.length === 0) {
            list.innerHTML = emptyStateHtml;
            if (typeof window.updateSavedTripsScrollview === 'function') window.updateSavedTripsScrollview();
            return;
        }

        const justSavedId = sessionStorage.getItem('just_saved_trip_id');
        if (justSavedId) {
            const matchIdx = activeItineraries.findIndex(t => t && (String(t.id) === String(justSavedId) || String(t.local_id) === String(justSavedId)));
            if (matchIdx > 0) {
                const savedItem = activeItineraries.splice(matchIdx, 1)[0];
                activeItineraries.unshift(savedItem);
            }
        }

        const currentRenderSig = JSON.stringify(activeItineraries.map(t => [
            t.id, t.title, t.trip_date, t.companion_males, t.companion_females, t.total_pax,
            (t.items || []).map(i => [i.id, i.proof_status, i.is_visited, i.is_location_verified, i.proof_image, (i.proof_images || []).length])
        ]));

        const hasExistingTripCards = list.querySelector('.trip-swipe-container') !== null;
        if (!justSavedId && window._lastRenderedTripsSig === currentRenderSig && hasExistingTripCards) {
            return;
        }
        window._lastRenderedTripsSig = currentRenderSig;

        let html = '';
        activeItineraries.forEach(trip => {
            if (!trip) return;
            try {
                let budgetIndicator = '';
                if (trip.budget && trip.budget > 0) {
                    const cost = parseFloat(trip.total_cost || 0);
                    const budget = parseFloat(trip.budget);
                    let color = '#10b981';
                    let statusTitle = `Within Budget: ₱${cost.toFixed(2)} / ₱${budget.toFixed(2)}`;
                    if (cost > budget) {
                        const coverage = (cost > 0) ? (budget / cost) : 0;
                        if (coverage >= 0.8) {
                            color = '#f59e0b';
                            statusTitle = `Nearing Budget: ₱${budget.toFixed(2)} / ₱${cost.toFixed(2)}`;
                        } else {
                            color = '#ef4444';
                            statusTitle = `Over Budget by ₱${(cost - budget).toFixed(2)}`;
                        }
                    }
                    budgetIndicator = `<span style="display:inline-block; width:10px; height:10px; border-radius:50%; background-color:${color}; margin-left:6px; border:none; outline:none; box-shadow:0 0 6px ${color}80;" title="${statusTitle}"></span>`;
                }

                const transportBadge = getSavedTripTransportBadgeGlobal(trip.transport_mode, trip);
                const safeTitle = trip.title ? trip.title.replace(/"/g, '&quot;').replace(/'/g, "\\'") : 'Saved Trip';
                const tripType = trip.trip_type || (trip.total_pax > 1 ? 'group' : 'solo');
                const totalPax = parseInt(trip.total_pax || 1, 10);
                const males = parseInt(trip.companion_males || 0, 10);
                const females = parseInt(trip.companion_females || 0, 10);
                let paxBadge = '';
                if (tripType === 'group' || totalPax > 1) {
                    paxBadge = `<span style="background:rgba(56,189,248,0.22); border:1px solid rgba(56,189,248,0.38); color:#ffffff; padding:3px 10px; border-radius:100px; font-weight:800; font-size:11.5px; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-users" style="color:#38bdf8;"></i> ${totalPax} Pax ${males > 0 || females > 0 ? `(${males}M, ${females}F)` : ''}</span>`;
                } else {
                    paxBadge = `<span style="background:rgba(255,255,255,0.18); color:#ffffff; padding:3px 9px; border-radius:100px; font-weight:700; font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-user" style="font-size:10px; color:#38bdf8;"></i> Solo</span>`;
                }

                html += `
                <div class="trip-swipe-container" data-trip-id="${trip.id}" data-trip-title="${safeTitle}" style="position:relative; overflow:hidden; border-radius:24px; -webkit-mask-image:-webkit-radial-gradient(white, black); mask-image:radial-gradient(white, black); isolation:isolate; contain:paint; margin-bottom:20px; touch-action:pan-y;">
                    <div class="trip-swipe-bg" onclick="event.stopPropagation(); window.confirmDeleteSavedTrip('${trip.id}', this.closest('.trip-swipe-container'), '${safeTitle}');" style="position:absolute; top:0; right:0; bottom:0; width:95px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius:0 24px 24px 0; display:flex; align-items:center; justify-content:center; color:#ffffff; font-size:13px; font-weight:800; gap:6px; z-index:1; cursor:pointer; opacity:0; pointer-events:none; transform:translateX(95px); transition:transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease; touch-action:manipulation;">
                        <i class="fa-solid fa-trash-can"></i> Delete
                    </div>
                    <div class="trip-swipe-content" style="position:relative; z-index:2; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none !important; outline: none !important; border-radius: 24px; padding: 22px; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-radius 0.25s ease; box-shadow: 0 10px 28px rgba(10, 25, 60, 0.28);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                            <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; flex: 1; min-width: 0; word-break: break-word;">${trip.title}</h3>
                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                <button type="button" class="btn-edit-saved-trip" onclick="event.stopPropagation(); window.editSavedTrip('${trip.id}')" style="background: rgba(255,255,255,0.18); border: none !important; outline: none !important; border-radius: 12px; padding: 6px 14px; color: #ffffff; font-size: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; box-shadow: none !important;">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                            </div>
                        </div>
                        <div style="font-size: 12.5px; color: #ffffff; opacity: 0.95; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span style="display:inline-flex; align-items:center; gap:5px; font-weight:700;"><i class="fa-regular fa-calendar" style="color: #ffffff;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set'}</span> 
                            ${transportBadge}
                            ${paxBadge}
                            ${trip.is_offline_queued ? `<span style="background: rgba(245, 158, 11, 0.28); border: 1px solid rgba(245, 158, 11, 0.5); color: #fef08a; padding: 3px 10px; border-radius: 100px; font-weight: 800; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-cloud-arrow-up"></i> Queued (Offline)</span>` : ''}
                            ${trip.budget ? `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-coins" style="font-size:10px; color:#fbbf24;"></i>Budget: ₱${parseFloat(trip.budget).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${budgetIndicator}</span>` : ''}
                        </div>
                        <div class="timeline-collapsible" id="timeline-${trip.id}">
                            <div class="timeline-inner">
                                <div class="timeline">`;
                
                let unvisitedCount = 0;
                let hasQueuedCheckins = false;
                const isTripCompleted = (trip.status === 'completed');
                if (trip.items && trip.items.length) {
                    const sortedTripItems = [...trip.items].sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
                    const firstUnvisitedIdx = sortedTripItems.findIndex(i => !(i.is_visited || i.proof_status === 'approved'));
                    sortedTripItems.forEach((item, index) => {
                        if (!item) return;
                        const dest = item.destination || (typeof window.getTouristSpotById === 'function' ? window.getTouristSpotById(item.tourist_spot_id) : null);
                        const destName = (dest && (dest.name || dest.title)) ? String(dest.name || dest.title) : 'Destination';
                        const destEscaped = destName.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        const spotImgUrl = (typeof window.getDestImage === 'function')
                            ? window.getDestImage(dest, 300)
                            : ((dest && (dest.image || dest.photo_url)) || 'assets/img/no_image.svg');
                        
                        let proofImages = [];
                        if (Array.isArray(item.proof_images) && item.proof_images.length > 0) {
                            proofImages = item.proof_images;
                        } else if (item.proof_image) {
                            try {
                                const parsed = JSON.parse(item.proof_image);
                                if (Array.isArray(parsed)) proofImages = parsed;
                                else proofImages = [item.proof_image];
                            } catch (e) {
                                proofImages = [item.proof_image];
                            }
                        }

                        const spotIdVal = item.tourist_spot_id || item.destination_id || (item.destination ? item.destination.id : null);
                        const queuedItem = window.OfflineCheckinManager ? window.OfflineCheckinManager.getQueuedItem(item.id, spotIdVal) : null;
                        const isQueued = Boolean(queuedItem || item.is_offline_queued);
                        if (isQueued) hasQueuedCheckins = true;
                        if (proofImages.length === 0 && isQueued && queuedItem && (queuedItem.previewUrl || queuedItem.image)) {
                            proofImages = [queuedItem.previewUrl || queuedItem.image];
                        }

                        const hasProof = (proofImages.length > 0);
                        const isLocVerified = Boolean(item.visited_at || item.is_location_verified);
                        const proofStatus = item.proof_status || (hasProof ? (item.is_visited ? 'approved' : 'pending') : null);
                        const isPhotoApproved = (proofStatus === 'approved');
                        const isApproved = Boolean(item.is_visited || isPhotoApproved || item.proof_status === 'approved' || (item.is_visited && isLocVerified));
                        const isRejected = (proofStatus === 'rejected');
                        const isPendingProof = !isApproved && !isRejected && !isQueued && hasProof && (proofStatus === 'pending');
                        const isNextStop = (!isApproved && !isPendingProof && !isQueued && index === firstUnvisitedIdx);
                        if (!isApproved && !isQueued) unvisitedCount++;

                        let photosStripHtml = '';
                        if (proofImages.length > 0) {
                            const resolvedUrls = proofImages.map(u => (typeof window.resolveProofImageUrl === 'function' ? window.resolveProofImageUrl(u) : u));
                            const jsonUrlsAttr = JSON.stringify(resolvedUrls).replace(/"/g, '&quot;');
                            const thumbs = proofImages.map((pUrl, pIdx) => {
                                const displayUrl = (typeof window.resolveProofImageUrl === 'function') ? window.resolveProofImageUrl(pUrl) : pUrl;
                                const fallbackUrl = (typeof window.getProofImageFallbackUrl === 'function') ? window.getProofImageFallbackUrl(pUrl) : 'assets/img/no_image.svg';
                                return `
                                    <div style="position:relative; flex-shrink:0;">
                                        <img src="${displayUrl}" 
                                            onerror="if(this.dataset.triedFallback!=='1'){this.dataset.triedFallback='1';this.src='${fallbackUrl}';}else{this.onerror=null;this.src='assets/img/no_image.svg';}" 
                                            onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal(${jsonUrlsAttr}, '${destEscaped}', 'Photo Proof (${pIdx + 1} of ${proofImages.length})', ${pIdx});" 
                                            alt="Proof Photo ${pIdx + 1}" 
                                            title="Tap to view proof photo" 
                                            loading="lazy"
                                            style="width:52px; height:52px; border-radius:12px; object-fit:cover; border:1.5px solid rgba(255,255,255,0.25); box-shadow:0 4px 12px rgba(0,0,0,0.3); cursor:pointer; display:block;">
                                        ${proofImages.length > 1 ? `<span style="position:absolute; bottom:2px; right:2px; background:rgba(0,0,0,0.7); color:#fff; font-size:8px; font-weight:800; padding:1px 4px; border-radius:4px;">${pIdx + 1}</span>` : ''}
                                    </div>
                                `;
                            }).join('');

                            photosStripHtml = `
                                <div style="display:flex; align-items:center; gap:8px; overflow-x:auto; padding:4px 0; max-width:100%; -webkit-overflow-scrolling:touch;">
                                    ${thumbs}
                                    ${proofImages.length > 1 ? `<span style="font-size:10px; color:#00f2fe; font-weight:700; white-space:nowrap; margin-left:2px;">${proofImages.length} photos</span>` : ''}
                                </div>
                            `;
                        }

                        let queuedThumbnailHtml = '';
                        if (isQueued && queuedItem) {
                            const qSrc = queuedItem.previewUrl || queuedItem.image || '';
                            if (qSrc) {
                                queuedThumbnailHtml = `<img src="${qSrc}" onerror="this.onerror=null; this.src='assets/img/no_image.svg';" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal('${qSrc}', '${destEscaped}', 'Offline Proof (Queued on Device)');" alt="Proof" title="Tap to view offline proof photo" style="width:52px; height:52px; border-radius:12px; object-fit:cover; border:2px solid #f59e0b !important; outline:none !important; box-shadow:0 4px 12px rgba(0,0,0,0.3); flex-shrink:0; cursor:pointer;">`;
                            } else {
                                queuedThumbnailHtml = `<div style="width:52px; height:52px; border-radius:12px; background:rgba(245,158,11,0.2); border:1.5px dashed #f59e0b; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fbbf24; flex-shrink:0;"><i class="fa-solid fa-camera" style="font-size:16px;"></i><span style="font-size:8px; font-weight:800; margin-top:2px;">OFFLINE</span></div>`;
                            }
                        }

                        const sClass = (dest && dest.classification_status) ? dest.classification_status : '';
                        const sMeta = (typeof window.getRewardPointsForClassification === 'function')
                            ? window.getRewardPointsForClassification(sClass)
                            : { points: (sClass === 'POTENTIAL' ? 75 : (sClass === 'EMERGE' ? 50 : 25)) };

                        const itemMode = item.transport_mode || item.transport_type || '';
                        if (itemMode) {
                            const isNoVehLeg = !itemMode || itemMode === 'no_vehicle' || itemMode.toLowerCase().includes('no vehicle');
                            if (isNoVehLeg) {
                                html += `
                                <div style="display:flex; align-items:center; gap:8px; margin:${index === 0 ? '0 0 10px 18px' : '4px 0 10px 18px'};">
                                    <span style="padding:3px 10px; border-radius:100px; font-size:10.5px; font-weight:700; background:rgba(255,255,255,0.16); color:#fca5a5; display:inline-flex; align-items:center; gap:5px; border:none !important; outline:none !important;">
                                        <i class="fa-solid fa-ban" style="color:#f87171; font-size:10px;"></i>
                                        <span>No Vehicle Selected</span>
                                    </span>
                                </div>`;
                            } else {
                                const legModes = parseVehicleModesGlobal(itemMode).filter(m => m !== 'no_vehicle' && !m.includes('no_vehicle'));
                                let vIcon = 'fa-route';
                                let vName = itemMode;
                                if (legModes.length > 1) {
                                    vIcon = 'fa-route';
                                    vName = `${legModes.length} Vehicles`;
                                } else if (legModes.length === 1) {
                                    const info = getVehicleInfoGlobal(legModes[0]);
                                    vIcon = info.icon;
                                    vName = info.name;
                                }
                                const costNum = parseFloat(item.leg_cost || 0);
                                const costStr = (costNum > 0) ? `&bull; ₱${costNum.toFixed(2)}` : '';

                                html += `
                                <div style="display:flex; align-items:center; gap:8px; margin:${index === 0 ? '0 0 10px 18px' : '4px 0 10px 18px'};">
                                    <span style="padding:3px 10px; border-radius:100px; font-size:10.5px; font-weight:700; background:rgba(255,255,255,0.16); color:#e0f2fe; display:inline-flex; align-items:center; gap:5px; border:none !important; outline:none !important;">
                                        <i class="fa-solid ${vIcon}" style="color:#00f2fe; font-size:10px;"></i>
                                        <span>${vName}</span>
                                        ${costStr ? `<span style="color:#38bdf8; font-weight:800;">${costStr}</span>` : ''}
                                    </span>
                                </div>`;
                            }
                        }

                        html += `
                        <div class="timeline-item ${isApproved ? 'completed' : (isPendingProof ? 'pending' : (isNextStop ? 'is-next-stop' : ''))}" style="margin-bottom: 12px;">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content" style="padding:14px; background: rgba(255,255,255,0.12); border: none !important; outline: none !important; border-radius: 16px; display:flex; flex-direction:column; gap:10px;">
                                <div style="display:flex; gap:12px; align-items:flex-start;">
                                    <div class="saved-trip-spot-thumb" style="width:56px; height:56px; border-radius:14px; overflow:hidden; flex-shrink:0; background:rgba(0,0,0,0.25); position:relative; box-shadow:0 3px 10px rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.18);">
                                        <img src="${spotImgUrl}" alt="${destEscaped}" loading="lazy" style="width:100%; height:100%; object-fit:cover; display:block;" onerror="this.onerror=null; this.src='assets/img/no_image.svg';">
                                    </div>
                                    <div style="flex:1; min-width:0;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <h4 style="margin:0; font-size:15px; font-weight:800; color:#ffffff; line-height:1.25;">${destName}</h4>
                                            ${isNextStop ? `<span style="padding: 2px 8px; border-radius: 100px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: linear-gradient(135deg, #00f2fe, #0284c7); border: none !important; outline: none !important;"><i class="fa-solid fa-location-dot" style="font-size:8px;"></i> Next Stop</span>` : ''}
                                        </div>
                                        ${dest && (dest.municipality || dest.location) ? `
                                            <div style="font-size:11.5px; color:rgba(255,255,255,0.8); display:flex; align-items:center; gap:4px; margin-top:3px;">
                                                <i class="fa-solid fa-location-dot" style="color:#00f2fe; font-size:10px; flex-shrink:0;"></i>
                                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${dest.municipality ? dest.municipality + ', La Union' : dest.location}</span>
                                            </div>` : ''}
                                    </div>
                                </div>
                                ${isApproved ?
                                    `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                        <span style="color:#34c759; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:5px; background:rgba(52,199,89,0.18); padding:4px 10px; border-radius:100px;">
                                            <i class="fa-solid fa-circle-check"></i> Visit Verified
                                        </span>
                                        ${photosStripHtml}
                                    </div>` :
                                    (isRejected ?
                                        `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                                <div>
                                                    <span style="color:#ef4444; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:5px;">
                                                        <i class="fa-solid fa-circle-xmark"></i> Photo Proof Rejected
                                                    </span>
                                                    <span style="font-size:10px; color:#fca5a5; display:block; margin-top:2px;">${item.rejection_reason || 'Please upload a clearer photo taken at this destination.'}</span>
                                                </div>
                                                <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #ef4444, #dc2626); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')">
                                                    <i class="fa-solid fa-camera" style="margin-right:4px;"></i> Re-upload Proof
                                                </button>
                                            </div>
                                            ${photosStripHtml}
                                        </div>` :
                                        (isQueued ?
                                            `<div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                                                ${queuedThumbnailHtml}
                                                <div>
                                                    <span style="background:rgba(245,158,11,0.25); border:none !important; outline:none !important; color:#fbbf24; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;">
                                                        <i class="fa-solid fa-clock-rotate-left"></i> Queued for Sync (Offline)
                                                    </span>
                                                    <span style="font-size:10px; color:#ffffff; opacity:0.85; display:block; margin-top:4px;"><i class="fa-solid fa-camera" style="color:#fbbf24; margin-right:4px;"></i>Saved on device &bull; Auto-syncs when online</span>
                                                </div>
                                            </div>` :
                                        (isPhotoApproved && !isLocVerified ?
                                            `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                                    <div>
                                                        <span style="background:rgba(52,199,89,0.18); border:none !important; outline:none !important; color:#34c759; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                            <i class="fa-solid fa-circle-check"></i> Photo Approved &bull; ⚠️ Check-in Required
                                                        </span>
                                                        <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">Photo proof verified! Please check in to complete this stop.</span>
                                                    </div>
                                                    <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${dest?.latitude || 'null'}, ${dest?.longitude || 'null'}, this)">
                                                        <i class="fa-solid fa-location-crosshairs" style="margin-right:4px;"></i> Check In
                                                    </button>
                                                </div>
                                                ${photosStripHtml}
                                            </div>` :
                                        (isLocVerified && isPendingProof ?
                                            `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                                <div>
                                                    <span style="background:rgba(255,149,0,0.22); border:none !important; outline:none !important; color:#ff9f0a; font-size:11.5px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                        <i class="fa-solid fa-hourglass-half"></i> Pending Approval
                                                    </span>
                                                    <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">
                                                        <i class="fa-solid fa-circle-check" style="color:#34c759; margin-right:3px;"></i> Location Verified &bull; Photos submitted
                                                    </span>
                                                </div>
                                                ${photosStripHtml}
                                            </div>` :
                                        (!isLocVerified && (isPendingProof || hasProof) ?
                                            `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                                    <div>
                                                        <span style="background:rgba(245,158,11,0.22); border:none !important; outline:none !important; color:#fbbf24; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                            <i class="fa-solid fa-camera"></i> Photo Ready &bull; ⚠️ GPS Check-in Required
                                                        </span>
                                                        <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">Please check in on-site to verify location</span>
                                                    </div>
                                                    <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${dest?.latitude || 'null'}, ${dest?.longitude || 'null'}, this)">
                                                        <i class="fa-solid fa-location-crosshairs" style="margin-right:4px;"></i> Check In
                                                    </button>
                                                </div>
                                                ${photosStripHtml}
                                            </div>` :
                                        (isLocVerified && !hasProof ?
                                            `<div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:4px; flex-wrap:wrap;">
                                                <div>
                                                    <span style="background:rgba(52,199,89,0.18); border:none !important; outline:none !important; color:#34c759; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                        <i class="fa-solid fa-circle-check"></i> Checked In &bull; ⚠️ Photo Proof Required
                                                    </span>
                                                    <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">Take or upload photos to complete check-in</span>
                                                </div>
                                                <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')">
                                                    <i class="fa-solid fa-camera" style="margin-right:4px;"></i> Upload Proof
                                                </button>
                                            </div>` :
                                            `<div style="display:flex; align-items:center; gap:8px; margin-top:6px; flex-wrap:wrap;">
                                                <button type="button" class="btn-primary" style="padding:8px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${dest?.latitude || 'null'}, ${dest?.longitude || 'null'}, this)">
                                                    <i class="fa-solid fa-location-crosshairs"></i> Check In
                                                </button>
                                                <button type="button" class="btn-primary" style="padding:8px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:rgba(255,255,255,0.18); border:none !important; outline:none !important; color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')">
                                                    <i class="fa-solid fa-camera"></i> Upload Proof
                                                </button>
                                            </div>`))))))
                                }
                            </div>
                        </div>`;
                    });
                } else {
                    html += `<p style="font-size:13px; color:#ffffff; opacity:0.85; margin:10px 0;">No destinations in this trip.</p>`;
                }

                html += `</div></div></div>`; // Close timeline

                // Action buttons
                html += `<div style="display:flex; gap:8px; margin-top:16px; flex-wrap:wrap; align-items:center;">`;
                html += `
                <button class="btn-primary" style="flex:1; min-width:105px; background: rgba(255, 255, 255, 0.22); border: none !important; outline: none !important; color: #ffffff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; transition: all 0.25s ease;" onclick="window.toggleTripDetails('${trip.id}')">
                    <i class="fa-solid fa-chevron-down" id="chevron-${trip.id}" style="margin-right:6px; transition:transform 0.3s ease;"></i> View Details
                </button>`;

                if (trip.is_offline_queued || hasQueuedCheckins) {
                    html += `
                    <button type="button" class="btn-primary" id="btn-sync-${trip.id}" onclick="event.stopPropagation(); window.syncSingleOfflineTrip('${trip.id}', this)" style="flex:1; min-width:115px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none !important; outline: none !important; color: #ffffff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Sync Now
                    </button>`;
                }

                html += `<div class="start-collapsible" id="start-wrapper-${trip.id}" style="flex:1.2; min-width:120px;">`;
                if (isTripCompleted) {
                    html += `
                    <button class="btn-primary" style="width:100%; white-space:nowrap; background: rgba(16,185,129,0.25); border: none !important; outline: none !important; color: #ffffff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: default;" disabled>
                        <i class="fa-solid fa-circle-check" style="margin-right:6px; color:#34c759;"></i> Completed
                    </button>`;
                } else if (unvisitedCount === 0 && trip.items && trip.items.length > 0) {
                    html += `
                    <button class="btn-primary" id="btn-complete-trip-${trip.id}" style="width:100%; white-space:nowrap; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none !important; outline: none !important; color: #fff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; box-shadow: none; cursor: pointer;" onclick="window.markTripCompleted('${trip.id}')">
                        <i class="fa-solid fa-flag-checkered" style="margin-right:6px;"></i> Complete
                    </button>`;
                } else {
                    html += `
                    <button type="button" class="btn-primary" style="width:100%; white-space:nowrap; background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border: none !important; outline: none !important; color: #fff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; box-shadow: none; cursor: pointer;" onclick="event.stopPropagation(); window.startTrip('${trip.id}', this)">
                        <i class="fa-solid fa-play" style="margin-right:6px;"></i> Start Trip
                    </button>`;
                }
                html += `</div>`; // Close start-collapsible
                html += `</div>`; // Close action buttons flex
                html += `</div></div>`; // Close swipe
            } catch (tripErr) {
                console.error("Error rendering saved trip item:", tripErr, trip);
            }
        });

        list.innerHTML = html || emptyStateHtml;
        if (typeof window.updateSavedTripsScrollview === 'function') {
            window.updateSavedTripsScrollview();
        }
        if (typeof window.initSavedTripsSwipe === 'function') {
            window.initSavedTripsSwipe();
        }

        if (justSavedId) {
            sessionStorage.removeItem('just_saved_trip_id');
            setTimeout(() => {
                const savedContainer = document.querySelector(`.trip-swipe-container[data-trip-id="${justSavedId}"]`);
                if (savedContainer) {
                    if (typeof window.toggleTripDetails === 'function') window.toggleTripDetails(justSavedId);
                    savedContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 80);
        }
    } catch (fatalErr) {
        console.error("Fatal error rendering saved trips:", fatalErr);
    }
};

window.toggleTripDetails = function (tripId) {
    const timeline = document.getElementById('timeline-' + tripId);
    const chevron = document.getElementById('chevron-' + tripId);
    const startWrapper = document.getElementById('start-wrapper-' + tripId);

    if (timeline && chevron) {
        if (!timeline.classList.contains('expanded')) {
            timeline.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
            if (startWrapper) startWrapper.classList.add('expanded');
        } else {
            timeline.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        }
        if (typeof window.updateSavedTripsScrollview === 'function') {
            requestAnimationFrame(window.updateSavedTripsScrollview);
            setTimeout(window.updateSavedTripsScrollview, 350);
        }
    }
};

window.startTrip = function (tripId, btnElement) {
    if (window._isStartingTrip) return;
    window._isStartingTrip = true;

    const btn = btnElement || (typeof event !== 'undefined' && event && event.target ? event.target.closest('button') : null);
    let prevHtml = '';
    if (btn) {
        prevHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Starting...';
    }

    // Automatic failsafe: Re-enable button & reset starting flag after 2.5s
    setTimeout(() => {
        window._isStartingTrip = false;
        if (btn) {
            btn.disabled = false;
            if (prevHtml) btn.innerHTML = prevHtml;
        }
    }, 2500);

    let transportParam = '';
    if (window._cachedSavedTrips) {
        const found = window._cachedSavedTrips.find(t => t.id == tripId || t.local_id == tripId);
        if (found) {
            window.currentTrip = found;
            if (found.transport_mode) {
                sessionStorage.setItem('active_trip_transport_' + tripId, found.transport_mode);
                localStorage.setItem('selected_trip_vehicle_' + tripId, found.transport_mode);
                transportParam = '&transport=' + encodeURIComponent(found.transport_mode);
            }
        }
    }

    sessionStorage.setItem('active_trip_id', String(tripId));
    window.currentTripId = String(tripId);

    const curParams = new URLSearchParams(window.location.search);
    const appParam = curParams.get('app') ? '&app=' + encodeURIComponent(curParams.get('app')) : '';

    if (typeof showToast === 'function') showToast("Starting trip navigation...");

    setTimeout(() => {
        const target = 'trip_map&trip_id=' + encodeURIComponent(tripId) + transportParam + appParam;
        if (typeof window.navigateTo === 'function') {
            window.navigateTo(target);
        } else {
            window.location.href = '?view=trip_map&trip_id=' + encodeURIComponent(tripId) + transportParam + appParam;
        }
    }, 180);
};

// --- MULTI-PHOTO PROOF, INDEPENDENT LOCATION CHECK-IN & TRIP COMPLETION HANDLERS ---
window.selectedProofPhotos = [];
window._currentProofItemId = null;
window._currentProofSpotName = null;

window.ensureProofUploadModal = function () {
    let modal = document.getElementById('proof-upload-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'proof-upload-modal';
        modal.setAttribute('onclick', 'if(event.target===this) window.closeProofUploadModal();');
        modal.style.cssText = 'display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.72); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:99999; justify-content:center; align-items:center; padding:16px; box-sizing:border-box;';
        modal.innerHTML = `
            <div style="background:#ffffff; border:none; outline:none; border-radius:26px; width:100%; max-width:420px; box-shadow:0 20px 50px rgba(0,0,0,0.35); text-align:center; max-height:90vh; overflow:hidden; display:flex; flex-direction:column; box-sizing:border-box;">
                <div style="flex-shrink:0; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); padding:18px 20px; display:flex; justify-content:space-between; align-items:center; border-top-left-radius:26px; border-top-right-radius:26px; box-sizing:border-box;">
                    <div style="text-align:left;">
                        <h3 id="proof-spot-title" style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-camera" style="color:#38bdf8; font-size:17px;"></i> Upload Photo Proof
                        </h3>
                        <span id="proof-spot-subtitle" style="font-size:12px; color:rgba(255,255,255,0.85); font-weight:600; display:block; margin-top:3px;">Select up to 5 photos</span>
                    </div>
                    <button type="button" onclick="window.closeProofUploadModal()"
                        style="background:rgba(255,255,255,0.18); border:none; outline:none; color:#ffffff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:all 0.15s ease;">
                        <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
                    </button>
                </div>
                <div style="background:#ffffff !important; padding:20px; flex:1; overflow-y:auto; text-align:left; box-sizing:border-box;">
                    <p style="font-size:13px; color:#475569; margin:0 0 16px 0; line-height:1.5; font-weight:500;">
                        Take or select selfies and photos at this destination to verify your visit.
                    </p>
                    <input type="hidden" id="proof-upload-item-id">
                    <input type="file" id="proof-upload-input" accept="image/*" multiple style="display:none;" onchange="window.handleProofPhotosSelected(this)">
                    <div style="display:flex; gap:10px; margin-bottom:16px;">
                        <button type="button" onclick="window.addProofPhotoFromSource('camera')"
                            style="flex:1; padding:12px 10px; background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border:none; outline:none; border-radius:14px; color:#ffffff; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; box-shadow:0 3px 10px rgba(2, 132, 199, 0.28);">
                            <i class="fa-solid fa-camera" style="font-size:15px;"></i> Take Photo
                        </button>
                        <button type="button" onclick="window.addProofPhotoFromSource('gallery')"
                            style="flex:1; padding:12px 10px; background:#f1f5f9; border:1.5px solid #e2e8f0; outline:none; border-radius:14px; color:#1e3a8a; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer;">
                            <i class="fa-solid fa-images" style="color:#0284c7; font-size:15px;"></i> Add from Gallery
                        </button>
                    </div>
                    <div style="margin-bottom:8px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <label style="font-size:11.5px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.5px;">Selected Photos</label>
                            <span id="proof-count-badge" style="font-size:11.5px; font-weight:800; color:#0284c7; background:#e0f2fe; padding:3px 10px; border-radius:100px;">0 / 5 photos</span>
                        </div>
                        <div id="proof-empty-prompt" onclick="window.addProofPhotoFromSource('gallery')"
                            style="border:2px dashed #94a3b8; border-radius:16px; padding:24px 16px; text-align:center; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px; color:#0284c7; margin-bottom:6px; display:block;"></i>
                            <p style="margin:0; font-size:13px; font-weight:800; color:#1e293b;">Tap to select photos</p>
                            <p style="margin:4px 0 0 0; font-size:11.5px; color:#64748b; font-weight:500;">You can choose multiple photos</p>
                        </div>
                        <div id="proof-thumbnails-grid" style="display:none; grid-template-columns:repeat(3, 1fr); gap:10px;"></div>
                    </div>
                </div>
                <div style="flex-shrink:0; background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 20px; border-bottom-left-radius:26px; border-bottom-right-radius:26px; box-sizing:border-box;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:center;">
                        <button type="button" style="width:100%; padding:13px 16px; border-radius:14px; border:1.5px solid #cbd5e1; outline:none; background:#ffffff; color:#475569; font-size:14px; font-weight:800; cursor:pointer;" onclick="window.closeProofUploadModal()">Cancel</button>
                        <button type="button" id="btn-submit-proof-photos" class="btn-primary" style="width:100%; padding:13px 16px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; cursor:pointer; box-shadow:0 4px 14px rgba(16, 185, 129, 0.35);" onclick="window.submitProofPhotos()">
                            <i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    return modal;
};

window.ensureCompleteTripModal = function () {
    let modal = document.getElementById('complete-trip-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'complete-trip-modal';
        modal.style.cssText = 'display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); z-index:999999; justify-content:center; align-items:center;';
        modal.innerHTML = `
            <div style="background:linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%); border:none; outline:none; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 16px 40px rgba(10, 25, 60, 0.45); text-align:center;">
                <i class="fa-solid fa-flag-checkered" style="font-size:32px; color:#34c759; margin-bottom:10px; display:block;"></i>
                <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Complete Trip?</h3>
                <p style="font-size:13px; color:#ffffff; opacity:0.95; margin-bottom:22px; line-height:1.5;">Are you sure you want to mark this trip as completed? It will be moved to your History.</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" style="flex:1; padding:13px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.12); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer;" onclick="window.closeCompleteTripModal()">Cancel</button>
                    <button type="button" id="btn-confirm-complete-trip" style="flex:1; padding:13px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; box-shadow:none; cursor:pointer;" onclick="window.executeConfirmCompleteTrip()">Complete</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    return modal;
};

window.openProofUploadModal = function (itemId, spotName = '') {
    if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
        if (typeof window.promptGuestLogin === 'function') {
            window.promptGuestLogin('photo');
        } else if (typeof showToast === 'function') {
            showToast('Please sign in to upload photo proofs.');
        }
        return;
    }

    if (typeof window.ensureProofUploadModal === 'function') {
        window.ensureProofUploadModal();
    }
    document.body.classList.add('modal-blurred');
    window._currentProofItemId = itemId;
    window._currentProofSpotName = spotName;
    window.selectedProofPhotos = [];

    const titleEl = document.getElementById('proof-spot-title');
    if (titleEl) {
        titleEl.innerHTML = `<i class="fa-solid fa-camera" style="color:#38bdf8; font-size:17px;"></i> ${spotName || 'Upload Photo Proof'}`;
    }

    const input = document.getElementById('proof-upload-input');
    if (input) input.value = '';

    window.renderProofThumbnailsGrid();

    const modal = document.getElementById('proof-upload-modal');
    if (modal) modal.style.display = 'flex';
};

window.closeProofUploadModal = function () {
    document.body.classList.remove('modal-blurred');
    const modal = document.getElementById('proof-upload-modal');
    if (modal) modal.style.display = 'none';
    window.selectedProofPhotos = [];
    window._currentProofItemId = null;
    window._currentProofSpotName = null;

    const btn = document.getElementById('btn-submit-proof-photos');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit';
        btn.disabled = false;
    }
};

// Backwards compatibility aliases
window.openCheckinModal = window.openProofUploadModal;
window.closeCheckinModal = window.closeProofUploadModal;

window.compressImageFile = async function (fileOrBlob, maxDimension = 1280, quality = 0.8) {
    return new Promise((resolve) => {
        if (!fileOrBlob || !fileOrBlob.type || !fileOrBlob.type.startsWith('image/')) {
            return resolve(fileOrBlob);
        }
        const img = new Image();
        const reader = new FileReader();
        reader.onload = (e) => {
            img.onload = () => {
                let width = img.width;
                let height = img.height;

                if (width > maxDimension || height > maxDimension) {
                    if (width > height) {
                        height = Math.round((height * maxDimension) / width);
                        width = maxDimension;
                    } else {
                        width = Math.round((width * maxDimension) / height);
                        height = maxDimension;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (!blob) {
                        resolve(fileOrBlob);
                        return;
                    }
                    const name = (fileOrBlob.name || 'proof_' + Date.now() + '.jpg').replace(/\.[^/.]+$/, "") + ".jpg";
                    const compressedFile = new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
                    resolve(compressedFile);
                }, 'image/jpeg', quality);
            };
            img.onerror = () => resolve(fileOrBlob);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(fileOrBlob);
        reader.readAsDataURL(fileOrBlob);
    });
};

window.renderProofThumbnailsGrid = function () {
    const emptyPrompt = document.getElementById('proof-empty-prompt');
    const grid = document.getElementById('proof-thumbnails-grid');
    const countBadge = document.getElementById('proof-count-badge');
    const submitBtn = document.getElementById('btn-submit-proof-photos');

    const total = window.selectedProofPhotos.length;
    if (countBadge) {
        countBadge.textContent = `${total} / 5 photos`;
    }

    if (submitBtn) {
        submitBtn.innerHTML = `<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit ${total > 0 ? `(${total})` : ''}`;
    }

    if (total === 0) {
        if (emptyPrompt) emptyPrompt.style.display = 'block';
        if (grid) {
            grid.style.display = 'none';
            grid.innerHTML = '';
        }
        return;
    }

    if (emptyPrompt) emptyPrompt.style.display = 'none';
    if (grid) {
        grid.style.display = 'grid';
        let html = '';

        window.selectedProofPhotos.forEach((item, index) => {
            html += `
                <div style="position:relative; aspect-ratio:1/1; border-radius:14px; overflow:hidden; background:#0f172a; box-shadow:0 3px 8px rgba(0,0,0,0.15); border:1.5px solid #e2e8f0;">
                    <img src="${item.dataUrl}" alt="Photo ${index + 1}" style="width:100%; height:100%; object-fit:cover; display:block;">
                    <span style="position:absolute; bottom:4px; left:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:9px; font-weight:800; padding:2px 6px; border-radius:6px;">
                        ${index + 1}
                    </span>
                    <button type="button" onclick="event.stopPropagation(); window.removeProofPhotoAtIndex(${index})" title="Remove Photo"
                        style="position:absolute; top:4px; right:4px; width:26px; height:26px; border-radius:50%; background:rgba(239,68,68,0.95); color:#fff; border:none; outline:none; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.25);">
                        <i class="fa-solid fa-xmark" style="font-size:12px;"></i>
                    </button>
                </div>
            `;
        });

        if (total < 5) {
            html += `
                <div onclick="window.addProofPhotoFromSource('gallery')"
                    style="aspect-ratio:1/1; border:2px dashed #94a3b8; border-radius:14px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                    <i class="fa-solid fa-plus" style="font-size:20px; color:#0284c7; margin-bottom:4px;"></i>
                    <span style="font-size:11px; font-weight:700; color:#475569;">Add Photo</span>
                </div>
            `;
        }

        grid.innerHTML = html;
    }
};

window.removeProofPhotoAtIndex = function (index) {
    if (index >= 0 && index < window.selectedProofPhotos.length) {
        window.selectedProofPhotos.splice(index, 1);
        window.renderProofThumbnailsGrid();
    }
};

window.addProofPhotoFromSource = async function (source) {
    if (window.selectedProofPhotos.length >= 5) {
        if (typeof showToast === 'function') showToast('Maximum 5 photos allowed.');
        return;
    }

    const isCapacitorNative = Boolean(
        window.Capacitor &&
        typeof window.Capacitor.isNativePlatform === 'function' &&
        window.Capacitor.isNativePlatform() &&
        window.Capacitor.Plugins &&
        window.Capacitor.Plugins.Camera
    );

    if (isCapacitorNative) {
        try {
            const cameraPlugin = window.Capacitor.Plugins.Camera;
            const image = await cameraPlugin.getPhoto({
                quality: 80,
                width: 1280,
                height: 1280,
                allowEditing: false,
                resultType: 'dataUrl',
                source: source === 'camera' ? 'CAMERA' : 'PHOTOS'
            });

            if (image && image.dataUrl) {
                const res = await fetch(image.dataUrl);
                const blob = await res.blob();
                const rawFile = new File([blob], 'proof_' + Date.now() + '.jpg', { type: blob.type || 'image/jpeg' });
                const compressed = await window.compressImageFile(rawFile, 1280, 0.8);
                window.selectedProofPhotos.push({
                    file: compressed,
                    dataUrl: image.dataUrl
                });
                window.renderProofThumbnailsGrid();
            }
        } catch (err) {
            console.warn('Capacitor Camera cancel or error:', err);
        }
    } else {
        const input = document.getElementById('proof-upload-input');
        if (!input) return;

        if (source === 'camera') {
            input.setAttribute('capture', 'environment');
            input.removeAttribute('multiple');
        } else {
            input.removeAttribute('capture');
            input.setAttribute('multiple', 'multiple');
        }
        input.click();
    }
};

window.handleProofPhotosSelected = async function (input) {
    if (!input.files || input.files.length === 0) return;

    const remainingSlots = 5 - window.selectedProofPhotos.length;
    if (remainingSlots <= 0) {
        if (typeof showToast === 'function') showToast('Maximum 5 photos allowed.');
        return;
    }

    const filesToProcess = Array.from(input.files).slice(0, remainingSlots);

    for (const file of filesToProcess) {
        try {
            const compressed = await window.compressImageFile(file, 1280, 0.8);
            const dataUrl = await new Promise((res) => {
                const r = new FileReader();
                r.onload = (e) => res(e.target.result);
                r.readAsDataURL(compressed);
            });
            window.selectedProofPhotos.push({
                file: compressed,
                dataUrl: dataUrl
            });
        } catch (e) {
            console.warn('Failed to process file:', e);
        }
    }

    input.value = '';
    window.renderProofThumbnailsGrid();
};

window.applyPhotoProofUpdateLocally = function (itemId, proofUrls, proofStatus = 'pending') {
    if (!itemId) return;
    const targetIdStr = String(itemId);
    const urls = Array.isArray(proofUrls) ? proofUrls : (proofUrls ? [proofUrls] : []);
    if (urls.length === 0) return;

    const parentTrip = (window._cachedSavedTrips || []).find(t => (t.items || []).some(i => String(i.id) === targetIdStr));
    const targetItem = parentTrip ? (parentTrip.items || []).find(i => String(i.id) === targetIdStr) : null;
    const spotId = targetItem ? (targetItem.tourist_spot_id || targetItem.destination_id || (targetItem.destination ? targetItem.destination.id : null)) : null;

    if (typeof window.savePersistentItemState === 'function') {
        window.savePersistentItemState(itemId, {
            proof_image: urls[0],
            proof_images: urls,
            proof_status: proofStatus,
            spot_id: spotId
        });
    }

    const updateItemInTrips = (tripsList) => {
        if (!Array.isArray(tripsList)) return tripsList;
        return tripsList.map(trip => {
            if (!trip || !Array.isArray(trip.items)) return trip;
            const updatedItems = trip.items.map(it => {
                if (!it) return it;
                const itIdStr = String(it.id || '');
                if (itIdStr === targetIdStr || (targetIdStr && itIdStr && targetIdStr.includes(itIdStr))) {
                    return {
                        ...it,
                        proof_image: urls[0],
                        proof_images: urls,
                        proof_status: proofStatus,
                        rejection_reason: null
                    };
                }
                return it;
            });
            return {
                ...trip,
                items: updatedItems
            };
        });
    };

    if (Array.isArray(window._cachedSavedTrips)) {
        window._cachedSavedTrips = updateItemInTrips(window._cachedSavedTrips);
        if (typeof window.renderSavedTrips === 'function') {
            window.renderSavedTrips(window._cachedSavedTrips);
        }
    }

    ['intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips', 'intan_elyu_saved_trips'].forEach(k => {
        try {
            const raw = localStorage.getItem(k);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    localStorage.setItem(k, JSON.stringify(updateItemInTrips(parsed)));
                } else if (parsed && Array.isArray(parsed.itineraries)) {
                    parsed.itineraries = updateItemInTrips(parsed.itineraries);
                    localStorage.setItem(k, JSON.stringify(parsed));
                } else if (parsed && Array.isArray(parsed.data)) {
                    parsed.data = updateItemInTrips(parsed.data);
                    localStorage.setItem(k, JSON.stringify(parsed));
                }
            }
        } catch (e) { }
    });

    try {
        for (let i = 0; i < localStorage.length; i++) {
            const k = localStorage.key(i);
            if (k && (k.startsWith('saved_trips_') || k.startsWith('dashboard_trips_') || k.startsWith('dashboard_saved_trips_'))) {
                try {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (Array.isArray(parsed)) {
                            localStorage.setItem(k, JSON.stringify(updateItemInTrips(parsed)));
                        } else if (parsed && Array.isArray(parsed.itineraries)) {
                            parsed.itineraries = updateItemInTrips(parsed.itineraries);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        } else if (parsed && Array.isArray(parsed.data)) {
                            parsed.data = updateItemInTrips(parsed.data);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        }
                    }
                } catch (e) { }
            }
        }
    } catch (e) { }
};

window.applyLocationCheckinLocally = function (itemId) {
    if (!itemId) return;
    const targetIdStr = String(itemId);
    const nowIso = new Date().toISOString();

    if (typeof window.savePersistentItemState === 'function') {
        window.savePersistentItemState(itemId, {
            is_location_verified: true,
            visited_at: nowIso
        });
    }

    const updateItemInTrips = (tripsList) => {
        if (!Array.isArray(tripsList)) return tripsList;
        return tripsList.map(trip => {
            if (!trip || !Array.isArray(trip.items)) return trip;
            const updatedItems = trip.items.map(it => {
                if (!it) return it;
                const itIdStr = String(it.id || '');
                if (itIdStr === targetIdStr || (targetIdStr && itIdStr && targetIdStr.includes(itIdStr))) {
                    return {
                        ...it,
                        is_location_verified: true,
                        visited_at: it.visited_at || nowIso
                    };
                }
                return it;
            });
            return {
                ...trip,
                items: updatedItems
            };
        });
    };

    if (Array.isArray(window._cachedSavedTrips)) {
        window._cachedSavedTrips = updateItemInTrips(window._cachedSavedTrips);
        if (typeof window.renderSavedTrips === 'function') {
            window.renderSavedTrips(window._cachedSavedTrips);
        }
    }

    ['intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips', 'intan_elyu_saved_trips'].forEach(k => {
        try {
            const raw = localStorage.getItem(k);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    localStorage.setItem(k, JSON.stringify(updateItemInTrips(parsed)));
                } else if (parsed && Array.isArray(parsed.itineraries)) {
                    parsed.itineraries = updateItemInTrips(parsed.itineraries);
                    localStorage.setItem(k, JSON.stringify(parsed));
                } else if (parsed && Array.isArray(parsed.data)) {
                    parsed.data = updateItemInTrips(parsed.data);
                    localStorage.setItem(k, JSON.stringify(parsed));
                }
            }
        } catch (e) { }
    });
};

window.submitProofPhotos = async function () {
    if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
        window.closeProofUploadModal();
        if (typeof window.promptGuestLogin === 'function') window.promptGuestLogin('photo');
        return;
    }

    const itemId = window._currentProofItemId;
    if (!itemId) return;

    if (window.selectedProofPhotos.length === 0) {
        if (typeof showToast === 'function') showToast('Please select or capture at least one photo proof.');
        return;
    }

    const btn = document.getElementById('btn-submit-proof-photos');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Submitting...';
        btn.disabled = true;
    }

    const previewUrls = window.selectedProofPhotos.map(p => p.dataUrl).filter(Boolean);
    const firstFile = window.selectedProofPhotos[0].file;
    const spotName = window._currentProofSpotName || 'Destination';

    window.applyPhotoProofUpdateLocally(itemId, previewUrls, 'pending');

    const parentTrip = (window._cachedSavedTrips || []).find(t => (t.items || []).some(i => String(i.id) === String(itemId)));
    const targetItem = parentTrip ? (parentTrip.items || []).find(i => String(i.id) === String(itemId)) : null;
    const spotId = targetItem ? (targetItem.tourist_spot_id || targetItem.destination_id || (targetItem.destination ? targetItem.destination.id : null)) : null;
    const tripId = parentTrip ? parentTrip.id : null;

    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    const isOfflineItem = !itemId || 
        String(itemId).startsWith('offline_') || 
        !navigator.onLine || 
        window.isAppOnline === false ||
        !token ||
        token === 'guest_token' ||
        token.startsWith('guest_') ||
        token.startsWith('offline_');

    if (isOfflineItem) {
        if (window.OfflineCheckinManager) {
            await window.OfflineCheckinManager.queueOfflineCheckin({
                type: 'itinerary',
                itemId: itemId,
                spotId: spotId,
                itineraryId: tripId,
                spotName: spotName,
                lat: window.myLat || 16.6159,
                lng: window.myLng || 120.3209,
                image: firstFile,
                previewUrl: previewUrls[0] || null,
                capturedAt: new Date().toISOString()
            });
        }
        window.closeProofUploadModal();
        if (typeof showToast === 'function') {
            showToast('📍 Photo proof saved on device! Auto-syncs when online.', 'success');
        }
        if (window.confetti) {
            try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
        }
        if (Array.isArray(window._cachedSavedTrips) && typeof window.renderSavedTrips === 'function') {
            window.renderSavedTrips(window._cachedSavedTrips);
        }
        return;
    }

    const formData = new FormData();
    window.selectedProofPhotos.forEach((item, idx) => {
        formData.append('images[]', item.file, item.file.name || `proof_${Date.now()}_${idx}.jpg`);
    });
    formData.append('image', firstFile);

    const fallbackOffline = async (reason) => {
        if (window.OfflineCheckinManager) {
            await window.OfflineCheckinManager.queueOfflineCheckin({
                type: 'itinerary',
                itemId: itemId,
                spotId: spotId,
                itineraryId: tripId,
                spotName: spotName,
                lat: window.myLat || 16.6159,
                lng: window.myLng || 120.3209,
                image: firstFile,
                previewUrl: previewUrls[0] || null,
                capturedAt: new Date().toISOString()
            });
            window.closeProofUploadModal();
            if (typeof showToast === 'function') {
                showToast('📍 Saved Offline! Photo proof saved on device. Auto-syncs when online.', 'success');
            }
            if (Array.isArray(window._cachedSavedTrips) && typeof window.renderSavedTrips === 'function') {
                window.renderSavedTrips(window._cachedSavedTrips);
            }
        } else {
            if (typeof showToast === 'function') showToast(reason || 'Upload failed. Please try again.');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit';
                btn.disabled = false;
            }
        }
    };

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 20000);

    try {
        const activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
        const response = await fetch(activeBackend + '/api/tourist/itineraries/items/' + itemId + '/proof', {
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
        const data = await response.json();

        if (response.ok && data.success) {
            window.closeProofUploadModal();
            const permanentUrls = data.proof_images || (data.proof_image ? [data.proof_image] : previewUrls);
            window.applyPhotoProofUpdateLocally(itemId, permanentUrls, data.proof_status || 'pending');

            if (typeof showToast === 'function') {
                showToast(data.message || 'Photo proof submitted! Awaiting for approval.', 'success');
            }
            if (window.confetti) {
                try { window.confetti({ particleCount: 80, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
            }
        } else {
            console.warn('Server proof upload failed:', data);
            await fallbackOffline(data.message || 'Server upload failed; saved offline.');
        }
    } catch (err) {
        clearTimeout(timeoutId);
        console.warn('Network timeout or error during proof upload, falling back offline:', err);
        await fallbackOffline('Network timeout during upload.');
    }
};

window.executeLocationCheckIn = function (itemId, spotName = '', spotLat = null, spotLng = null, btnEl = null) {
    if (!itemId) return;

    const originalHtml = btnEl ? btnEl.innerHTML : '';

    if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
        if (typeof window.promptGuestLogin === 'function') window.promptGuestLogin('check-in');
        return;
    }

    if (btnEl) {
        btnEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking in...';
        btnEl.disabled = true;
    }

    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    const isOfflineItem = !itemId || 
        String(itemId).startsWith('offline_') || 
        !navigator.onLine || 
        window.isAppOnline === false ||
        !token ||
        token === 'guest_token' ||
        token.startsWith('guest_') ||
        token.startsWith('offline_');

    const getHaversineDist = (lat1, lon1, lat2, lon2) => {
        const R = 6371e3;
        const phi1 = lat1 * Math.PI / 180;
        const phi2 = lat2 * Math.PI / 180;
        const dPhi = (lat2 - lat1) * Math.PI / 180;
        const dLam = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dPhi / 2) * Math.sin(dPhi / 2) + Math.cos(phi1) * Math.cos(phi2) * Math.sin(dLam / 2) * Math.sin(dLam / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    };

    const parentTrip = (window._cachedSavedTrips || []).find(t => (t.items || []).some(i => String(i.id) === String(itemId)));
    const targetItem = parentTrip ? (parentTrip.items || []).find(i => String(i.id) === String(itemId)) : null;
    const spotId = targetItem ? (targetItem.tourist_spot_id || targetItem.destination_id || (targetItem.destination ? targetItem.destination.id : null)) : null;
    const tripId = parentTrip ? parentTrip.id : null;
    const tPax = parentTrip ? (parentTrip.total_pax || 1) : 1;
    const cMales = parentTrip ? (parentTrip.companion_males || 0) : 0;
    const cFemales = parentTrip ? (parentTrip.companion_females || 0) : 0;

    let destLat = (spotLat !== null && !isNaN(spotLat)) ? parseFloat(spotLat) : null;
    let destLng = (spotLng !== null && !isNaN(spotLng)) ? parseFloat(spotLng) : null;
    if ((destLat === null || destLng === null) && targetItem && targetItem.destination) {
        if (targetItem.destination.latitude != null) destLat = parseFloat(targetItem.destination.latitude);
        if (targetItem.destination.longitude != null) destLng = parseFloat(targetItem.destination.longitude);
    }
    if ((destLat === null || destLng === null) && spotId && typeof window.getTouristSpotById === 'function') {
        const sp = window.getTouristSpotById(spotId);
        if (sp) {
            if (sp.latitude != null) destLat = parseFloat(sp.latitude);
            if (sp.longitude != null) destLng = parseFloat(sp.longitude);
        }
    }

    const doSubmitCheckin = async (lat, lng) => {
        if (destLat !== null && destLng !== null && !isNaN(destLat) && !isNaN(destLng)) {
            const dist = getHaversineDist(lat, lng, destLat, destLng);
            if (dist > 200) {
                const distM = Math.round(dist);
                const distKm = (dist / 1000).toFixed(1);
                const displayDist = dist >= 1000 ? `${distKm} km` : `${distM} meters`;
                if (typeof showToast === 'function') {
                    showToast(`⚠️ You are ${displayDist} away from ${spotName || 'the tourist site'}. You must be within 200m of the tourist site to check in! 📍`, 'warning', 4500);
                }
                if (btnEl) {
                    btnEl.innerHTML = originalHtml;
                    btnEl.disabled = false;
                }
                return;
            }
        }

        if (isOfflineItem) {
            window.applyLocationCheckinLocally(itemId);

            if (window.OfflineCheckinManager) {
                await window.OfflineCheckinManager.queueOfflineCheckin({
                    type: 'itinerary',
                    itemId: itemId,
                    spotId: spotId,
                    itineraryId: tripId,
                    spotName: spotName || 'Destination',
                    lat: lat,
                    lng: lng,
                    total_pax: tPax,
                    companion_males: cMales,
                    companion_females: cFemales,
                    capturedAt: new Date().toISOString()
                });
            }
            if (typeof showToast === 'function') {
                showToast(`📍 Checked in party of ${tPax} offline at ${spotName}! Auto-syncs when online.`, 'success');
            }
            if (window.confetti) {
                try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
            }
            if (btnEl) {
                btnEl.innerHTML = originalHtml;
                btnEl.disabled = false;
            }
            return;
        }

        const formData = new FormData();
        formData.append('lat', lat);
        formData.append('lng', lng);
        formData.append('total_pax', tPax);
        formData.append('companion_males', cMales);
        formData.append('companion_females', cFemales);
        formData.append('captured_at', new Date().toISOString());

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 12000);

        try {
            const activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
            const response = await fetch(activeBackend + '/api/tourist/itineraries/items/' + itemId + '/checkin', {
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
            const data = await response.json();

            if (response.ok && (data.success || data.is_location_verified)) {
                window.applyLocationCheckinLocally(itemId);
                if (typeof showToast === 'function') {
                    showToast(data.message || `📍 Location verified at ${spotName}! Photo proof is required to earn points.`, 'success');
                }
                if (window.confetti) {
                    try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Check-in failed. Please ensure you are within 200m of the destination.', 'error');
                }
            }
            if (btnEl) {
                btnEl.innerHTML = originalHtml;
                btnEl.disabled = false;
            }
        } catch (err) {
            clearTimeout(timeoutId);
            console.warn('Location check-in error, queuing offline:', err);
            if (window.OfflineCheckinManager) {
                window.applyLocationCheckinLocally(itemId);
                await window.OfflineCheckinManager.queueOfflineCheckin({
                    type: 'itinerary',
                    itemId: itemId,
                    spotId: spotId,
                    itineraryId: tripId,
                    spotName: spotName || 'Destination',
                    lat: lat,
                    lng: lng,
                    capturedAt: new Date().toISOString()
                });
                if (typeof showToast === 'function') {
                    showToast(`📍 Checked in offline at ${spotName}! Auto-syncs when online.`, 'success');
                }
            } else {
                if (typeof showToast === 'function') showToast('Network error during check-in. Please try again.');
            }
            if (btnEl) {
                btnEl.innerHTML = originalHtml;
                btnEl.disabled = false;
            }
        }
    };

    const handleGpsFailure = (err) => {
        console.warn('GPS location acquisition error:', err);
        if (typeof showToast === 'function') {
            showToast('📍 Unable to acquire accurate GPS location. Please turn on Location/GPS and try again.', 'error');
        }
        if (btnEl) {
            btnEl.innerHTML = originalHtml;
            btnEl.disabled = false;
        }
    };

    const options = { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 };

    if (typeof window.requestPreciseLocation === 'function') {
        window.requestPreciseLocation(true).then(loc => {
            if (loc && loc.lat && loc.lng) doSubmitCheckin(loc.lat, loc.lng);
            else handleGpsFailure("No coords");
        }).catch(() => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => doSubmitCheckin(pos.coords.latitude, pos.coords.longitude),
                    handleGpsFailure,
                    options
                );
            } else {
                handleGpsFailure("Geolocation unsupported");
            }
        });
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => doSubmitCheckin(pos.coords.latitude, pos.coords.longitude),
            handleGpsFailure,
            options
        );
    } else {
        handleGpsFailure("Geolocation unsupported");
    }
};

window.markTripCompleted = function (id) {
    window._pendingCompleteTripId = id;
    if (typeof window.ensureCompleteTripModal === 'function') {
        window.ensureCompleteTripModal();
    }
    const modal = document.getElementById('complete-trip-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
};

window.closeCompleteTripModal = function () {
    const modal = document.getElementById('complete-trip-modal');
    if (modal) {
        modal.style.display = 'none';
    }
};

window.executeConfirmCompleteTrip = async function () {
    const id = window._pendingCompleteTripId;
    if (!id) return;

    window.closeCompleteTripModal();
    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');

    if (typeof window.markTripAsCompletedLocally === 'function') {
        window.markTripAsCompletedLocally(id, 0, null);
    }

    const tripCard = document.querySelector(`.trip-swipe-container[data-trip-id="${id}"]`);
    if (tripCard) {
        tripCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease, max-height 0.4s ease, margin 0.4s ease';
        tripCard.style.opacity = '0';
        tripCard.style.transform = 'translateY(-10px) scale(0.95)';
        setTimeout(() => {
            tripCard.remove();
            const list = document.getElementById('saved-trips-list');
            if (list && list.children.length === 0) {
                list.innerHTML = '<p style="text-align:center; color:#ffffff; margin-top: 20px;">No active trips found.</p>';
            }
        }, 300);
    }

    let localVisitedItems = [];
    try {
        if (Array.isArray(window._cachedSavedTrips)) {
            const found = window._cachedSavedTrips.find(t => t && (String(t.id) === String(id) || String(t.local_id) === String(id)));
            if (found && Array.isArray(found.items)) {
                localVisitedItems = found.items;
            }
        }
        if (localVisitedItems.length === 0) {
            const backupRaw = localStorage.getItem('intan_elyu_offline_saved_trips_backup') || localStorage.getItem('intan_elyu_saved_trips');
            if (backupRaw) {
                const parsed = JSON.parse(backupRaw);
                const itins = Array.isArray(parsed) ? parsed : (parsed.itineraries || parsed.data || []);
                const found = itins.find(t => t && (String(t.id) === String(id) || String(t.local_id) === String(id)));
                if (found && Array.isArray(found.items)) {
                    localVisitedItems = found.items;
                }
            }
        }
    } catch (e) {
        console.warn('Could not extract local trip items:', e);
    }

    if (!navigator.onLine || window.isAppOnline === false) {
        if (window.OfflineCompletedTripsManager) {
            window.OfflineCompletedTripsManager.queueCompletion(id);
        }
        if (typeof showToast === 'function') {
            showToast("🎉 Trip completed! Please review your destinations to earn points.", "success", 5000);
        }
        if (window.confetti) {
            window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
        }
        if (token) {
            const tokenKey = token.substring(0, 10);
            localStorage.removeItem('saved_trips_' + tokenKey);
            localStorage.removeItem('dashboard_trips_' + tokenKey);
            localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
        }
        if (typeof window.fetchSavedTrips === 'function') window.fetchSavedTrips(true);
        if (typeof window.showTripCompletionReviewModal === 'function') {
            window.showTripCompletionReviewModal(localVisitedItems);
        }
        return;
    }

    try {
        const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
        const response = await fetch(bUrl + '/api/tourist/itineraries/' + id + '/complete', {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'ngrok-skip-browser-warning': 'true',
                'Authorization': 'Bearer ' + token
            }
        });

        const data = await response.json();
        if (response.ok) {
            if (typeof showToast === 'function') showToast(data.message || "Trip completed! Please review your destinations to earn points.");
            if (window.confetti) {
                window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
            }

            if (token) {
                const tokenKey = token.substring(0, 10);
                localStorage.removeItem('saved_trips_' + tokenKey);
                localStorage.removeItem('dashboard_trips_' + tokenKey);
                localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
            }
            if (typeof window.fetchSavedTrips === 'function') window.fetchSavedTrips(true);

            let visitedItems = data.visited_items || data.visited_spots || [];
            if (!visitedItems || visitedItems.length === 0) {
                visitedItems = data.itinerary?.items || data.items || localVisitedItems;
            }
            if (typeof window.showTripCompletionReviewModal === 'function') {
                window.showTripCompletionReviewModal(visitedItems);
            }
        } else {
            if (typeof showToast === 'function') showToast(data.message || "Failed to complete trip.");
        }
    } catch (error) {
        console.error("Error completing trip, queuing offline:", error);
        if (window.OfflineCompletedTripsManager) {
            window.OfflineCompletedTripsManager.queueCompletion(id);
            if (typeof showToast === 'function') {
                showToast("🎉 Trip completed! Please review your destinations to earn points.", "success", 5000);
            }
            if (window.confetti) {
                window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
            }
            if (token) {
                const tokenKey = token.substring(0, 10);
                localStorage.removeItem('saved_trips_' + tokenKey);
                localStorage.removeItem('dashboard_trips_' + tokenKey);
                localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
            }
            if (typeof window.fetchSavedTrips === 'function') window.fetchSavedTrips(true);
            if (typeof window.showTripCompletionReviewModal === 'function') {
                window.showTripCompletionReviewModal(localVisitedItems);
            }
        } else {
            if (typeof showToast === 'function') showToast("Could not complete trip. Please check your connection.");
        }
    }
};

window.showTripCompletionReviewModal = function (visitedItems) {
    const existing = document.getElementById('trip-completion-review-modal');
    if (existing) existing.remove();

    if (!Array.isArray(visitedItems) || visitedItems.length === 0) {
        return;
    }

    window.userReviewedSpotIds = window.userReviewedSpotIds || new Set();

    let destListHtml = '';
    visitedItems.forEach((item, idx) => {
        if (!item) return;
        const spotId = item.tourist_spot_id || item.destination_id || (item.destination ? item.destination.id : item.id);
        if (!spotId) return;

        const destName = item.destination_name || (item.destination ? item.destination.name : (item.name || item.spot_name || 'Tourist Destination'));
        const sClass = item.classification_status || (item.destination ? item.destination.classification_status : (item.classification || 'EXIST'));
        const isReviewed = window.userReviewedSpotIds && window.userReviewedSpotIds.has(Number(spotId));

        destListHtml += `
        <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:8px;">
            <div style="width:30px; height:30px; border-radius:8px; background:linear-gradient(135deg, #1e3a8a, #2563eb); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:12px; color:#fff;">${idx + 1}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:13.5px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${destName}</div>
                <div style="font-size:11px; color:#64748b; font-weight:600; margin-top:1px;">
                    ${isReviewed 
                        ? '<span style="color:#10b981;"><i class="fa-solid fa-check" style="font-size:10px; margin-right:3px;"></i> Reviewed</span>' 
                        : '<span style="color:#64748b;">Leave a review for this stop</span>'}
                </div>
            </div>
            <button type="button" 
                    data-spot-id="${spotId}" 
                    data-spot-classification="${sClass}"
                    onclick="window.startReviewFromCompletion('${spotId}', this)" 
                    style="padding:6px 14px; border-radius:100px; background:${isReviewed ? '#e2e8f0' : 'linear-gradient(135deg, #1e3a8a, #2563eb)'}; border:none; outline:none; color:${isReviewed ? '#64748b' : '#ffffff'}; font-size:11.5px; font-weight:800; cursor:pointer; white-space:nowrap; box-shadow:none; display:inline-flex; align-items:center; gap:4px; flex-shrink:0;">
                ${isReviewed ? '<i class="fa-solid fa-check" style="font-size:10px; margin-right:4px;"></i> Reviewed' : '<i class="fa-solid fa-pen" style="font-size:10px;"></i> Review'}
            </button>
        </div>`;
    });

    const modalHtml = `
    <div id="trip-completion-review-modal" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:99998; display:flex; align-items:center; justify-content:center; padding:16px; opacity:0; transition:opacity 0.3s ease;">
        <div style="background:#ffffff; border:none; outline:none; border-radius:24px; width:100%; max-width:390px; box-shadow:0 25px 50px rgba(10, 25, 60, 0.45); overflow:hidden; display:flex; flex-direction:column; transform:scale(0.92) translateY(12px); transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
            <!-- Header (Royal Blue) -->
            <div style="background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; padding:18px 20px; display:flex; justify-content:space-between; align-items:center; color:#ffffff; flex-shrink:0;">
                <div>
                    <h4 style="margin:0; font-size:16px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-flag-checkered" style="color:#60a5fa;"></i> Trip Completed!
                    </h4>
                    <p style="margin:2px 0 0 0; font-size:11.5px; color:rgba(255,255,255,0.9);">
                        Review destinations you visited
                    </p>
                </div>
                <button type="button" onclick="window.closeTripCompletionReviewModal()" style="background:rgba(255,255,255,0.2); border:none !important; outline:none !important; color:#ffffff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px; transition:background 0.2s;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Middle (White) -->
            <div style="background:#ffffff !important; padding:18px 20px 14px 20px; overflow-y:auto; max-height:calc(75vh - 120px); scrollbar-width:none; -ms-overflow-style:none; flex:1; text-align:center;">
                <div style="width:52px; height:52px; border-radius:50%; background:#eff6ff; display:flex; align-items:center; justify-content:center; margin:0 auto 10px auto; border:none; outline:none;">
                    <i class="fa-solid fa-award" style="font-size:24px; color:#2563eb;"></i>
                </div>
                <h3 style="margin:0 0 4px; color:#0f172a; font-size:18px; font-weight:900;">Congratulations!</h3>
                <p style="font-size:12px; color:#64748b; margin:0 0 16px 0; line-height:1.45;">
                    Share your experience! Review each destination you visited to help fellow travelers.
                </p>
                <div style="text-align:left; max-height:220px; overflow-y:auto; margin-bottom:0; padding-right:4px; scrollbar-width:none; -ms-overflow-style:none;">
                    ${destListHtml}
                </div>
            </div>

            <!-- Footer (Royal Blue) -->
            <div style="background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; padding:14px 20px; flex-shrink:0;">
                <button type="button" id="btn-trip-completion-close" onclick="window.closeTripCompletionReviewModal()" style="width:100%; padding:12px; border-radius:100px; border:none; outline:none; background:rgba(255,255,255,0.2); color:#ffffff; font-size:13px; font-weight:800; cursor:pointer; transition:all 0.2s ease;">
                    Done
                </button>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    requestAnimationFrame(() => {
        const modal = document.getElementById('trip-completion-review-modal');
        if (modal) {
            modal.style.opacity = '1';
            const card = modal.querySelector('div > div');
            if (card) {
                card.style.transform = 'scale(1) translateY(0)';
            }
        }
    });
};

window.closeTripCompletionReviewModal = function () {
    const modal = document.getElementById('trip-completion-review-modal');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 320);
    }
};

window.startReviewFromCompletion = function (spotId, btnEl) {
    if (btnEl) {
        btnEl.dataset.reviewing = "true";
        window._lastReviewedBtn = btnEl;
    }
    const compModal = document.getElementById('trip-completion-review-modal');
    if (compModal) {
        compModal.style.zIndex = '99990';
    }
    if (typeof window.openWriteTestimonyModal === 'function') {
        window.openWriteTestimonyModal(spotId, btnEl);
    }
};

window.initSavedTripsSwipe = function () {
    const containers = document.querySelectorAll('.trip-swipe-container');
    containers.forEach(container => {
        if (container.dataset.swipeInitialized === 'true') return;
        container.dataset.swipeInitialized = 'true';

        const content = container.querySelector('.trip-swipe-content');
        const bg = container.querySelector('.trip-swipe-bg');
        if (!content) return;

        let startX = 0, startY = 0, currentX = 0, currentY = 0;
        let isSwiping = false, isHorizontalSwipe = false, isGestureDetermined = false;
        let isOpen = false;
        const tripId = container.dataset.tripId;
        const tripTitle = container.dataset.tripTitle;

        const openSwipe = () => {
            isOpen = true;
            content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
            content.style.transform = 'translateX(-95px)';
            if (bg) {
                bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
                bg.style.width = '95px';
                bg.style.opacity = '1';
                bg.style.pointerEvents = 'auto';
                bg.style.transform = 'translateX(0px)';
                bg.style.zIndex = '10';
            }
        };

        const closeSwipe = () => {
            isOpen = false;
            content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
            content.style.transform = 'translateX(0px)';
            if (bg) {
                bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
                bg.style.width = '95px';
                bg.style.opacity = '0';
                bg.style.pointerEvents = 'none';
                bg.style.transform = 'translateX(95px)';
                bg.style.zIndex = '1';
            }
        };

        const handleStart = (clientX, clientY, target) => {
            if (target && target.closest('button, .btn-edit-saved-trip, a, input, select, textarea, .start-collapsible, .timeline-collapsible button')) {
                return;
            }
            if (target && target.closest('.trip-swipe-bg')) {
                return;
            }

            startX = clientX;
            startY = clientY;
            currentX = startX;
            currentY = startY;
            isSwiping = true;
            isHorizontalSwipe = false;
            isGestureDetermined = false;
            content.style.transition = 'none';
            if (bg) bg.style.transition = 'none';
        };

        const handleMove = (clientX, clientY) => {
            if (!isSwiping) return;
            currentX = clientX;
            currentY = clientY;

            const diffX = startX - currentX;
            const diffY = startY - currentY;

            if (!isGestureDetermined) {
                if (Math.abs(diffX) > 7 || Math.abs(diffY) > 7) {
                    isGestureDetermined = true;
                    if (Math.abs(diffX) > Math.abs(diffY)) {
                        isHorizontalSwipe = true;
                    } else {
                        isHorizontalSwipe = false;
                        isSwiping = false;
                        return;
                    }
                } else {
                    return;
                }
            }

            if (!isHorizontalSwipe) return;

            const baseOffset = isOpen ? 95 : 0;
            const effectiveDiff = diffX + baseOffset;

            if (effectiveDiff > 0) {
                const cardWidth = container.offsetWidth || 340;
                const dragAmount = Math.min(effectiveDiff, cardWidth * 0.85);
                content.style.transform = `translateX(-${dragAmount}px)`;
                if (bg) {
                    bg.style.opacity = '1';
                    bg.style.pointerEvents = 'auto';
                    bg.style.zIndex = '10';
                    if (dragAmount > 95) {
                        bg.style.width = `${dragAmount}px`;
                        bg.style.transform = 'translateX(0px)';
                    } else {
                        bg.style.width = '95px';
                        bg.style.transform = `translateX(${95 - dragAmount}px)`;
                    }
                }
            } else {
                content.style.transform = 'translateX(0px)';
                if (bg) {
                    bg.style.width = '95px';
                    bg.style.opacity = '0';
                    bg.style.pointerEvents = 'none';
                    bg.style.transform = 'translateX(95px)';
                    bg.style.zIndex = '1';
                }
            }
        };

        const handleEnd = () => {
            if (!isSwiping) return;
            isSwiping = false;

            const diffX = startX - currentX;
            const baseOffset = isOpen ? 95 : 0;
            const effectiveDiff = diffX + baseOffset;
            const cardWidth = container.offsetWidth || 340;

            if (effectiveDiff > 130 || effectiveDiff > cardWidth * 0.38) {
                content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                content.style.transform = 'translateX(-100px)';
                if (bg) {
                    bg.style.transition = 'transform 0.25s ease, opacity 0.25s ease';
                    bg.style.width = '100px';
                    bg.style.transform = 'translateX(0px)';
                    bg.style.opacity = '1';
                    bg.style.pointerEvents = 'auto';
                    bg.style.zIndex = '10';
                }
                isOpen = true;
                if (typeof window.confirmDeleteSavedTrip === 'function') {
                    window.confirmDeleteSavedTrip(tripId, container, tripTitle);
                }
            } else if (effectiveDiff > 35) {
                openSwipe();
            } else {
                closeSwipe();
            }

            startX = 0; startY = 0; currentX = 0; currentY = 0;
            isHorizontalSwipe = false; isGestureDetermined = false;
        };

        const handleCancel = () => {
            if (!isSwiping) return;
            isSwiping = false;
            if (isOpen) openSwipe(); else closeSwipe();
        };

        content.addEventListener('touchstart', (e) => {
            if (e.touches && e.touches.length === 1) {
                handleStart(e.touches[0].clientX, e.touches[0].clientY, e.target);
            }
        }, { passive: true });

        content.addEventListener('touchmove', (e) => {
            if (e.touches && e.touches.length === 1) {
                handleMove(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });

        content.addEventListener('touchend', handleEnd, { passive: true });
        content.addEventListener('touchcancel', handleCancel, { passive: true });

        content.addEventListener('mousedown', (e) => handleStart(e.clientX, e.clientY, e.target));
        window.addEventListener('mousemove', (e) => { if (isSwiping) handleMove(e.clientX, e.clientY); });
        window.addEventListener('mouseup', () => { if (isSwiping) handleEnd(); });
    });
};

window.confirmDeleteSavedTrip = function (id, container, title) {
    window._pendingDeleteTripId = id;
    window._pendingDeleteTripContainer = container || (id ? document.querySelector(`.trip-swipe-container[data-trip-id="${id}"]`) : null);
    window._pendingDeleteTripTitle = title || container?.dataset?.tripTitle || '';

    let modal = document.getElementById('delete-trip-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'delete-trip-modal';
        modal.style.cssText = 'display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); z-index:999999; justify-content:center; align-items:center;';
        modal.innerHTML = `
            <div style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none; outline:none; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 16px 40px rgba(10, 25, 60, 0.45); text-align:center;">
                <i class="fa-solid fa-trash-can" style="font-size:32px; color:#ffffff; margin-bottom:10px; display:block;"></i>
                <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Delete Saved Trip?</h3>
                <p id="delete-trip-title-text" style="font-size:13px; color:rgba(255,255,255,0.95); margin-bottom:22px; line-height:1.5;">Are you sure you want to delete this trip?</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" style="flex:1; padding:13px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.22); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer;" onclick="window.closeDeleteTripModal()">Cancel</button>
                    <button type="button" id="btn-confirm-delete-trip" style="flex:1; padding:13px; font-size:14px; font-weight:800; background:#ef4444; border:none; outline:none; color:#ffffff; border-radius:14px; cursor:pointer;" onclick="window.executeConfirmDeleteTrip()">Delete</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    const textEl = document.getElementById('delete-trip-title-text');
    if (textEl) {
        const displayTitle = window._pendingDeleteTripTitle || 'this trip';
        textEl.textContent = `Are you sure you want to delete "${displayTitle}"? All saved itinerary items will be removed.`;
    }
    modal.style.display = 'flex';
};

window.closeDeleteTripModal = function () {
    const modal = document.getElementById('delete-trip-modal');
    if (modal) modal.style.display = 'none';

    const container = window._pendingDeleteTripContainer || (window._pendingDeleteTripId ? document.querySelector(`.trip-swipe-container[data-trip-id="${window._pendingDeleteTripId}"]`) : null);
    if (container) {
        const content = container.querySelector('.trip-swipe-content');
        const bg = container.querySelector('.trip-swipe-bg');
        if (content) {
            content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
            content.style.transform = 'translateX(0px)';
        }
        if (bg) {
            bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
            bg.style.width = '95px';
            bg.style.opacity = '0';
            bg.style.pointerEvents = 'none';
            bg.style.transform = 'translateX(95px)';
            bg.style.zIndex = '1';
        }
    }

    window._pendingDeleteTripId = null;
    window._pendingDeleteTripContainer = null;
    window._pendingDeleteTripTitle = null;
};

window.executeConfirmDeleteTrip = async function () {
    const id = window._pendingDeleteTripId;
    let element = window._pendingDeleteTripContainer || (id ? document.querySelector(`.trip-swipe-container[data-trip-id="${id}"]`) : null);
    const deletedTitle = window._pendingDeleteTripTitle || '';
    window.closeDeleteTripModal();

    if (!id) return;

    if (typeof window.markTripAsDeleted === 'function') {
        window.markTripAsDeleted(id);
    }

    const pruneTripFromLocalCaches = (tripId) => {
        if (!tripId) return;
        const targetStr = String(tripId).trim();
        const filterOut = (list) => {
            if (!Array.isArray(list)) return [];
            return list.filter(t => {
                if (!t) return false;
                if (typeof window.isTripDeleted === 'function' && window.isTripDeleted(t)) return false;
                const idStr = String(t.id || '');
                const tripIdStr = String(t.trip_id || '');
                const localIdStr = String(t.local_id || '');
                const updateIdStr = String(t.update_id || '');
                if (idStr === targetStr || tripIdStr === targetStr || localIdStr === targetStr || updateIdStr === targetStr) return false;
                return true;
            });
        };

        ['intan_elyu_offline_saved_trips', 'intan_elyu_offline_saved_trips_backup', 'intan_elyu_saved_trips'].forEach(k => {
            try {
                const raw = localStorage.getItem(k);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) {
                        localStorage.setItem(k, JSON.stringify(filterOut(parsed)));
                    } else if (parsed && Array.isArray(parsed.itineraries)) {
                        parsed.itineraries = filterOut(parsed.itineraries);
                        localStorage.setItem(k, JSON.stringify(parsed));
                    }
                }
            } catch (e) { }
        });

        if (window.OfflineTripManager && typeof window.OfflineTripManager.removeQueuedTrip === 'function') {
            try { window.OfflineTripManager.removeQueuedTrip(tripId); } catch(e) {}
        }

        try {
            for (let i = localStorage.length - 1; i >= 0; i--) {
                const k = localStorage.key(i);
                if (k && (k.startsWith('saved_trips_') || k.startsWith('dashboard_trips_') || k.startsWith('dashboard_saved_trips_'))) {
                    localStorage.removeItem(k);
                }
            }
        } catch(e) {}

        if (Array.isArray(window._cachedSavedTrips)) {
            window._cachedSavedTrips = filterOut(window._cachedSavedTrips);
        }
    };

    pruneTripFromLocalCaches(id);

    if (element) {
        element.style.transition = 'opacity 0.25s ease, transform 0.25s ease, max-height 0.35s ease, margin 0.35s ease';
        element.style.opacity = '0';
        element.style.transform = 'translateX(-100%) scale(0.9)';
        setTimeout(() => {
            element.remove();
            const list = document.getElementById('saved-trips-list');
            if (list && list.querySelectorAll('.trip-swipe-container').length === 0) {
                if (typeof window.renderSavedTrips === 'function') window.renderSavedTrips([]);
            }
        }, 300);
    }

    if (typeof showToast === 'function') showToast("Trip deleted.");

    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    if (navigator.onLine && token && !String(id).startsWith('offline_')) {
        try {
            const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
            fetch(`${bUrl}/api/tourist/itineraries/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true'
                }
            }).catch(() => {});
        } catch(e) {}
    }
};

window.editSavedTrip = function (tripId) {
    let trip = (window._cachedSavedTrips || []).find(t => String(t.id) === String(tripId));
    if (!trip) {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (token) {
            try {
                const rawCached = localStorage.getItem('saved_trips_' + token.substring(0, 10));
                if (rawCached) {
                    const parsed = JSON.parse(rawCached);
                    const list = parsed && parsed.data ? parsed.data : parsed;
                    if (Array.isArray(list)) trip = list.find(t => String(t.id) === String(tripId));
                }
            } catch (e) { }
        }
    }
    if (!trip) {
        if (typeof showToast === 'function') showToast("Could not load trip details for editing.");
        return;
    }

    sessionStorage.setItem('editing_itinerary_id', trip.id);
    sessionStorage.setItem('editing_trip_title', trip.title || '');
    sessionStorage.setItem('editing_trip_date', trip.trip_date || '');
    sessionStorage.setItem('editing_trip_budget', (trip.budget !== null && trip.budget !== undefined) ? trip.budget : '');
    sessionStorage.setItem('editing_trip_transport', trip.transport_mode || '');
    sessionStorage.setItem('editing_trip_type', trip.trip_type || 'solo');
    sessionStorage.setItem('editing_companion_males', String(trip.companion_males || 0));
    sessionStorage.setItem('editing_companion_females', String(trip.companion_females || 0));
    sessionStorage.setItem('editing_total_pax', String(trip.total_pax || 1));
    if (trip.transport_mode) {
        localStorage.setItem('intan_elyu_draft_trip_transport', trip.transport_mode);
        const rawModes = trip.transport_mode.split(/[\+,]/).map(s => s.trim().toLowerCase()).filter(Boolean);
        localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify(rawModes.length > 0 ? rawModes : [trip.transport_mode]));
    }

    const sortedTripItems = [...(trip.items || [])].sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
    const spots = sortedTripItems.map(i => {
        let dest = i.destination || (typeof window.getTouristSpotById === 'function' ? window.getTouristSpotById(i.tourist_spot_id || i.destination_id) : null);
        if (!dest && (i.name || i.title)) dest = i;
        if (dest) {
            const spotId = i.tourist_spot_id || i.destination_id || (dest ? (dest.tourist_spot_id || dest.id) : null);
            return {
                ...dest,
                id: spotId,
                tourist_spot_id: spotId,
                itinerary_item_id: i.id
            };
        }
        return null;
    }).filter(Boolean);

    localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(spots));

    if (typeof window.navigateTo === 'function') {
        window.navigateTo('itinerary');
    } else {
        window.location.href = '?view=itinerary';
    }
};

window.syncSingleOfflineTrip = async function (tripId, btnElement) {
    if (!navigator.onLine || window.isAppOnline === false) {
        if (typeof showToast === 'function') {
            showToast("You are currently offline. Connect to the internet to sync to cloud.", "warning", 3500);
        }
        return;
    }

    let origHtml = '';
    if (btnElement) {
        origHtml = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Syncing...';
    }

    try {
        let syncedAny = false;
        if (window.OfflineTripManager && typeof window.OfflineTripManager.syncSingleTrip === 'function') {
            const ok = await window.OfflineTripManager.syncSingleTrip(tripId);
            if (ok) syncedAny = true;
        }
        if (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.processQueue === 'function') {
            const chkCount = await window.OfflineCheckinManager.processQueue();
            if (chkCount && chkCount > 0) syncedAny = true;
        }
        if (typeof showToast === 'function') {
            showToast(syncedAny ? "Trip data successfully synced to cloud!" : "Trip is up to date with cloud!", "success", 4000);
        }
        window.dispatchEvent(new CustomEvent('tripsSynced'));
        if (typeof window.fetchSavedTrips === 'function') {
            await window.fetchSavedTrips(true);
        }
    } catch (err) {
        console.error("Manual trip sync failed:", err);
        if (typeof showToast === 'function') {
            showToast("Could not sync trip. Please check your internet connection.", "error", 4000);
        }
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = origHtml || '<i class="fa-solid fa-cloud-arrow-up"></i> Sync Now';
        }
    }
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
    if (window._initialOfflineView) return window._initialOfflineView;
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
        try {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            if (oldScript.parentNode) {
                oldScript.parentNode.replaceChild(newScript, oldScript);
            }
        } catch (scriptErr) {
            console.error('Error executing injected script:', scriptErr);
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

    // GameZone is browsable in Guest Mode; puzzles.php itself shows a login-required
    // modal whenever a guest tries to play any mode (no redirect to auth here).

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
        function isValidViewPartial(str) {
            if (!str || typeof str !== 'string' || str.length < 30) return false;
            // Reject full HTML outer document shells (like index.html containing app-container or DOCTYPE)
            if (str.includes('<!DOCTYPE') || str.includes('<html') || str.includes('id="app-container"') || str.includes('id="magic-nav"')) {
                return false;
            }
            return true;
        }

        // In-memory and persistent disk view template cache for instantaneous and offline navigation
        window._viewHtmlCache = window._viewHtmlCache || {};
        let html = window._viewHtmlCache[targetView] || null;
        if (!isValidViewPartial(html)) {
            html = null;
        }

        if (!html) {
            try {
                const cached = localStorage.getItem('elyu_view_cache_v3_' + targetView) || localStorage.getItem('elyu_view_cache_v2_' + targetView);
                if (isValidViewPartial(cached)) {
                    html = cached;
                    window._viewHtmlCache[targetView] = html;
                } else if (cached) {
                    localStorage.removeItem('elyu_view_cache_v3_' + targetView);
                    localStorage.removeItem('elyu_view_cache_v2_' + targetView);
                }
            } catch (e) {}
        }

        // Only attempt network fetch if not explicitly offline, or if view is not yet available in cache
        if (!isNetworkOffline || !html) {
            try {
                let fetchUrl = `index.php?view=${encodeURIComponent(targetView)}&ajax=1&_t=${Date.now()}`;
                extraParams.forEach((val, key) => {
                    fetchUrl += `&${encodeURIComponent(key)}=${encodeURIComponent(val)}`;
                });

                const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                const timeoutId = controller ? setTimeout(() => controller.abort(), isNetworkOffline ? 1500 : 7000) : null;

                const response = await fetch(fetchUrl, {
                    signal: controller ? controller.signal : undefined,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (timeoutId) clearTimeout(timeoutId);

                if (response.ok) {
                    const freshHtml = await response.text();
                    if (isValidViewPartial(freshHtml)) {
                        html = freshHtml;
                        window._viewHtmlCache[targetView] = html;
                        try { localStorage.setItem('elyu_view_cache_v3_' + targetView, html); } catch(e) {}
                    }
                }
            } catch (fetchErr) {
                console.warn(`View fetch for ${targetView} failed (using cache if available):`, fetchErr);
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
        } else if (!html && targetView === 'dashboard') {
            html = `<div class="mobile-header">
                <div class="header-icon" onclick="typeof toggleSidebar==='function'&&toggleSidebar()" title="Menu">
                    <i class="fa-solid fa-bars"></i>
                </div>
                <h1 class="header-title">Discover La Union</h1>
                <div class="header-icon" onclick="typeof toggleNotifications==='function'&&toggleNotifications()" title="Notifications">
                    <i class="fa-solid fa-bell" id="bell-icon"></i>
                    <div class="bell-dot" id="bell-dot"></div>
                </div>
            </div>
            <div class="dashboard-container has-header has-bottom-nav animate-slide-up">
                <div id="dash-offline-pill" style="display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0 0 14px 0; padding: 8px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 12px; font-weight: 800; text-align: center;">
                    <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
                    <span>Offline Mode &bull; Showing Saved Guides & Local GPS</span>
                </div>
                <div class="profile-header stagger-1" onclick="navigateTo('profile')"
                    style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
                        <div class="profile-info-row" style="margin-bottom:0; flex:1;">
                            <div class="profile-avatar">
                                <img id="dash-avatar" src="https://ui-avatars.com/api/?name=Explorer&background=007AFF&color=fff&rounded=true&bold=true&size=128" alt="Avatar">
                            </div>
                            <div class="profile-text">
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <h2 class="profile-name" id="dash-name" style="margin:0;">Hi, Explorer! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i></h2>
                                </div>
                                <div id="dash-title-row" style="display:flex; align-items:center; gap:8px; margin-top:6px; flex-wrap:wrap;">
                                    <span class="profile-title" id="dash-title"><i class="fa-solid fa-compass" style="color:#ffffff; font-size:11px;"></i> Explorer of Elyu</span>
                                    <span id="dash-status-badge"><i class="fa-solid fa-circle" style="font-size:6px; margin-right:4px; color:#ffffff;"></i> Active Tourist</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="stats-row" id="dash-stats-row" style="margin-bottom:0; display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
                        <div class="stat-card" onclick="event.stopPropagation(); navigateTo('itinerary');"
                            style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; cursor: pointer; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                            <div class="stat-icon" style="font-size:18px; margin-bottom:3px;"><i class="fa-solid fa-map-location-dot" style="color:#38bdf8;"></i></div>
                            <div class="stat-value" id="dash-stat-places" style="font-size:17px; font-weight:900; color:#ffffff; margin-bottom:1px; letter-spacing:-0.4px;">—</div>
                            <div class="stat-label" style="font-size:10px; font-weight:800; color:#ffffff !important; text-transform:uppercase; letter-spacing:0.5px; opacity:0.95;">Places</div>
                        </div>
                        <div class="stat-card" onclick="event.stopPropagation(); navigateTo('leaderboard');"
                            style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; cursor: pointer; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                            <div class="stat-icon" style="font-size:18px; margin-bottom:3px;"><i class="fa-solid fa-coins" style="color:#fbbf24;"></i></div>
                            <div class="stat-value" id="dash-stat-points" style="font-size:17px; font-weight:900; color:#ffffff; margin-bottom:1px; letter-spacing:-0.4px;">—</div>
                            <div class="stat-label" style="font-size:10px; font-weight:800; color:#ffffff !important; text-transform:uppercase; letter-spacing:0.5px; opacity:0.95;">Points</div>
                        </div>
                        <div class="stat-card" onclick="event.stopPropagation(); navigateTo('leaderboard');"
                            style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; cursor: pointer; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                            <div class="stat-icon" style="font-size:18px; margin-bottom:3px;"><i class="fa-solid fa-trophy" style="color:#f59e0b;"></i></div>
                            <div class="stat-value" id="dash-stat-rank" style="font-size:17px; font-weight:900; color:#ffffff; margin-bottom:1px; letter-spacing:-0.4px;">—</div>
                            <div class="stat-label" style="font-size:10px; font-weight:800; color:#ffffff !important; text-transform:uppercase; letter-spacing:0.5px; opacity:0.95;">Rank</div>
                        </div>
                    </div>
                </div>
                <div class="weather-card stagger-2" style="position: relative;">
                    <div class="weather-left">
                        <div class="weather-temp" id="weather-temp">29°C</div>
                        <div class="weather-desc" id="weather-desc">Sunny &bull; Good for Travel</div>
                        <div class="weather-loc" id="weather-loc"><i class="fa-solid fa-location-dot" style="color:#38bdf8; margin-right:4px;"></i> San Fernando, La Union</div>
                    </div>
                    <div style="text-align: right;">
                        <div class="weather-icon" id="weather-icon">⛅</div>
                    </div>
                </div>
                <div class="dash-quick-actions stagger-2" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:8px; margin: 16px 0;">
                    <div onclick="navigateTo('map')" style="background:#ffffff; border-radius:16px; padding:12px 6px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06); cursor:pointer;">
                        <i class="fa-solid fa-map-location-dot" style="color:#2563eb; font-size:20px; margin-bottom:6px; display:block;"></i>
                        <span style="font-size:11px; font-weight:700; color:#1e293b; display:block;">Map</span>
                    </div>
                    <div onclick="navigateTo('itinerary')" style="background:#ffffff; border-radius:16px; padding:12px 6px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06); cursor:pointer;">
                        <i class="fa-solid fa-route" style="color:#059669; font-size:20px; margin-bottom:6px; display:block;"></i>
                        <span style="font-size:11px; font-weight:700; color:#1e293b; display:block;">Itinerary</span>
                    </div>
                    <div onclick="navigateTo('discount')" style="background:#ffffff; border-radius:16px; padding:12px 6px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06); cursor:pointer;">
                        <i class="fa-solid fa-tags" style="color:#d97706; font-size:20px; margin-bottom:6px; display:block;"></i>
                        <span style="font-size:11px; font-weight:700; color:#1e293b; display:block;">Discounts</span>
                    </div>
                    <div onclick="navigateTo('saved_places')" style="background:#ffffff; border-radius:16px; padding:12px 6px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06); cursor:pointer;">
                        <i class="fa-solid fa-heart" style="color:#dc2626; font-size:20px; margin-bottom:6px; display:block;"></i>
                        <span style="font-size:11px; font-weight:700; color:#1e293b; display:block;">Saved</span>
                    </div>
                </div>
                <div class="dash-section stagger-3">
                    <div class="section-title">
                        <h3>Explore Municipalities</h3>
                        <span style="font-size:11px; font-weight:700; color:#64748b;">20 Municipalities</span>
                    </div>
                    <div id="dash-municipalities-row" class="municipalities-row" style="display:flex; gap:12px; overflow-x:auto; padding-bottom:8px; scrollbar-width:none;">
                    </div>
                </div>
            </div>
            <script>
                (function() {
                    try {
                        var rawUser = localStorage.getItem('auth_user') || localStorage.getItem('intan_elyu_user');
                        if (rawUser) {
                            var u = typeof rawUser === 'string' ? JSON.parse(rawUser) : rawUser;
                            if (u) {
                                var nameEl = document.getElementById('dash-name');
                                if (nameEl && (u.name || u.full_name)) {
                                    var fName = (u.name || u.full_name).split(' ')[0];
                                    nameEl.innerHTML = 'Hi, ' + fName + '! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i>';
                                }
                                var avEl = document.getElementById('dash-avatar');
                                if (avEl && (u.avatar || u.profile_picture || u.avatar_url)) {
                                    avEl.src = u.avatar || u.profile_picture || u.avatar_url;
                                }
                                if (u.points !== undefined) {
                                    var ptEl = document.getElementById('dash-stat-points');
                                    if (ptEl) ptEl.textContent = Number(u.points).toLocaleString();
                                }
                            }
                        }
                    } catch(e) {}
                    if (typeof window.populateMunicipalitiesRow === 'function') {
                        window.populateMunicipalitiesRow();
                    }
                })();
            </script>`;
        } else if (!html && targetView === 'saved_trips') {
            html = `<div class="mobile-header">
                <div class="header-icon" onclick="typeof window.goBack==='function'?window.goBack():(typeof navigateTo==='function'?navigateTo('dashboard'):window.history.back())" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </div>
                <h1 class="header-title">My Saved Trips</h1>
                <div class="header-icon" onclick="typeof toggleNotifications==='function'&&toggleNotifications()" title="Notifications">
                    <i class="fa-solid fa-bell" id="bell-icon"></i>
                    <div class="bell-dot" id="bell-dot"></div>
                </div>
            </div>
            <div id="saved-trips-container" class="saved-trips-page-container has-header animate-slide-up">
                <div id="saved-trips-offline-badge" style="display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 14px auto; padding: 7px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 11.5px; font-weight: 800; text-align: center; width: fit-content;">
                    <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
                    <span>Offline Mode &bull; Cached Trips & Queued Check-ins</span>
                </div>
                <div id="saved-trips-list">
                    <div class="empty-state-card" style="margin-top: 30px; margin-bottom: 30px; opacity: 1 !important; visibility: visible !important;">
                        <div class="empty-state-icon" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                            <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                        </div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff; text-wrap: balance;">No Saved Trips Yet</h3>
                        <p style="margin: 0; font-size: 13.5px; color: rgba(255, 255, 255, 0.88); line-height: 1.5; max-width: 260px; text-wrap: pretty;">
                            Your adventure starts here! Explore La Union tourist spots and plan your personalized itinerary.
                        </p>
                        <button type="button" class="btn-open-map" onclick="if(typeof window.navigateTo==='function') window.navigateTo('itinerary');">
                            <i class="fa-solid fa-plus"></i> Create New Trip
                        </button>
                    </div>
                </div>
            </div>
            <!-- Dedicated Multi-Photo Proof Upload Modal -->
            <div id="proof-upload-modal" onclick="if(event.target===this) window.closeProofUploadModal();"
                style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.72); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:99999; justify-content:center; align-items:center; padding:16px; box-sizing:border-box;">
                <div style="background:#ffffff; border:none; outline:none; border-radius:26px; width:100%; max-width:420px; box-shadow:0 20px 50px rgba(0,0,0,0.35); text-align:center; max-height:90vh; overflow:hidden; display:flex; flex-direction:column; box-sizing:border-box;">
                    <div style="flex-shrink:0; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); padding:18px 20px; display:flex; justify-content:space-between; align-items:center; border-top-left-radius:26px; border-top-right-radius:26px; box-sizing:border-box;">
                        <div style="text-align:left;">
                            <h3 id="proof-spot-title" style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-camera" style="color:#38bdf8; font-size:17px;"></i> Upload Photo Proof
                            </h3>
                            <span id="proof-spot-subtitle" style="font-size:12px; color:rgba(255,255,255,0.85); font-weight:600; display:block; margin-top:3px;">Select up to 5 photos</span>
                        </div>
                        <button type="button" onclick="window.closeProofUploadModal()"
                            style="background:rgba(255,255,255,0.18); border:none; outline:none; color:#ffffff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:all 0.15s ease;">
                            <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
                        </button>
                    </div>
                    <div style="background:#ffffff !important; padding:20px; flex:1; overflow-y:auto; text-align:left; box-sizing:border-box;">
                        <p style="font-size:13px; color:#475569; margin:0 0 16px 0; line-height:1.5; font-weight:500;">
                            Take or select selfies and photos at this destination to verify your visit.
                        </p>
                        <input type="hidden" id="proof-upload-item-id">
                        <input type="file" id="proof-upload-input" accept="image/*" multiple style="display:none;" onchange="window.handleProofPhotosSelected(this)">
                        <div style="display:flex; gap:10px; margin-bottom:16px;">
                            <button type="button" onclick="window.addProofPhotoFromSource('camera')"
                                style="flex:1; padding:12px 10px; background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border:none; outline:none; border-radius:14px; color:#ffffff; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; box-shadow:0 3px 10px rgba(2, 132, 199, 0.28);">
                                <i class="fa-solid fa-camera" style="font-size:15px;"></i> Take Photo
                            </button>
                            <button type="button" onclick="window.addProofPhotoFromSource('gallery')"
                                style="flex:1; padding:12px 10px; background:#f1f5f9; border:1.5px solid #e2e8f0; outline:none; border-radius:14px; color:#1e3a8a; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer;">
                                <i class="fa-solid fa-images" style="color:#0284c7; font-size:15px;"></i> Add from Gallery
                            </button>
                        </div>
                        <div style="margin-bottom:8px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <label style="font-size:11.5px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.5px;">Selected Photos</label>
                                <span id="proof-count-badge" style="font-size:11.5px; font-weight:800; color:#0284c7; background:#e0f2fe; padding:3px 10px; border-radius:100px;">0 / 5 photos</span>
                            </div>
                            <div id="proof-empty-prompt" onclick="window.addProofPhotoFromSource('gallery')"
                                style="border:2px dashed #94a3b8; border-radius:16px; padding:24px 16px; text-align:center; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                                <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px; color:#0284c7; margin-bottom:6px; display:block;"></i>
                                <p style="margin:0; font-size:13px; font-weight:800; color:#1e293b;">Tap to select photos</p>
                                <p style="margin:4px 0 0 0; font-size:11.5px; color:#64748b; font-weight:500;">You can choose multiple photos</p>
                            </div>
                            <div id="proof-thumbnails-grid" style="display:none; grid-template-columns:repeat(3, 1fr); gap:10px;"></div>
                        </div>
                    </div>
                    <div style="flex-shrink:0; background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 20px; border-bottom-left-radius:26px; border-bottom-right-radius:26px; box-sizing:border-box;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:center;">
                            <button type="button" style="width:100%; padding:13px 16px; border-radius:14px; border:1.5px solid #cbd5e1; outline:none; background:#ffffff; color:#475569; font-size:14px; font-weight:800; cursor:pointer;" onclick="window.closeProofUploadModal()">Cancel</button>
                            <button type="button" id="btn-submit-proof-photos" class="btn-primary" style="width:100%; padding:13px 16px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; cursor:pointer; box-shadow:0 4px 14px rgba(16, 185, 129, 0.35);" onclick="window.submitProofPhotos()">
                                <i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Complete Trip Confirmation Modal -->
            <div id="complete-trip-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); z-index:999999; justify-content:center; align-items:center;">
                <div style="background:linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%); border:none; outline:none; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 16px 40px rgba(10, 25, 60, 0.45); text-align:center;">
                    <i class="fa-solid fa-flag-checkered" style="font-size:32px; color:#34c759; margin-bottom:10px; display:block;"></i>
                    <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Complete Trip?</h3>
                    <p style="font-size:13px; color:#ffffff; opacity:0.95; margin-bottom:22px; line-height:1.5;">Are you sure you want to mark this trip as completed? It will be moved to your History.</p>
                    <div style="display:flex; gap:10px;">
                        <button type="button" style="flex:1; padding:13px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.12); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer;" onclick="window.closeCompleteTripModal()">Cancel</button>
                        <button type="button" id="btn-confirm-complete-trip" style="flex:1; padding:13px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; box-shadow:none; cursor:pointer;" onclick="window.executeConfirmCompleteTrip()">Complete</button>
                    </div>
                </div>
            </div>
            <!-- Delete Trip Confirmation Modal -->
            <div id="delete-trip-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); z-index:999999; justify-content:center; align-items:center;">
                <div style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none; outline:none; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 16px 40px rgba(10, 25, 60, 0.45); text-align:center;">
                    <i class="fa-solid fa-trash-can" style="font-size:32px; color:#ffffff; margin-bottom:10px; display:block;"></i>
                    <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Delete Saved Trip?</h3>
                    <p id="delete-trip-title-text" style="font-size:13px; color:rgba(255,255,255,0.95); margin-bottom:22px; line-height:1.5;">Are you sure you want to delete this trip? All saved itinerary items will be removed.</p>
                    <div style="display:flex; gap:10px;">
                        <button type="button" style="flex:1; padding:13px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.22); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer; touch-action:manipulation; pointer-events:auto !important;" onclick="window.closeDeleteTripModal()">Cancel</button>
                        <button type="button" id="btn-confirm-delete-trip" style="flex:1; padding:13px; font-size:14px; font-weight:800; background:#ef4444; border:none; outline:none; color:#ffffff; border-radius:14px; box-shadow:none; cursor:pointer; touch-action:manipulation; pointer-events:auto !important;" onclick="window.executeConfirmDeleteTrip()">Delete</button>
                    </div>
                </div>
            </div>
            <!-- Edit Trip Modal -->
            <div id="edit-trip-details-modal" onclick="if(event.target === this) window.closeEditTripDetailsModal();" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.55); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:9999999; justify-content:center; align-items:center; padding:16px; box-sizing:border-box; pointer-events:auto !important; touch-action:manipulation;">
                <div onclick="event.stopPropagation();" style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:22px; width:100%; max-width:390px; max-height:86dvh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 16px 40px rgba(10,25,60,0.45) !important; pointer-events:auto !important; touch-action:manipulation;" class="hide-scrollbar">
                    <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:14px 16px; color:#ffffff; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; border:none !important;">
                        <h3 style="margin:0; color:#ffffff; font-size:16px; font-weight:800; letter-spacing:-0.3px;">Edit Trip</h3>
                        <button type="button" onclick="window.closeEditTripDetailsModal()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; flex-shrink:0; transition:transform 0.15s ease; touch-action:manipulation;">
                            <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
                        </button>
                    </div>
                    <input type="hidden" id="quick-edit-trip-id" value="">
                    <div style="background:#ffffff !important; color:#0f172a; padding:16px; flex:1; min-height:0; overflow-y:auto; display:flex; flex-direction:column; box-sizing:border-box; pointer-events:auto !important;" class="hide-scrollbar">
                        <label style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:6px; display:block;">Trip Name</label>
                        <input type="text" id="quick-edit-trip-title" placeholder="Trip Title" style="width:100%; padding:12px 14px; border-radius:14px; border:1.5px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#0f172a; margin-bottom:14px; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box;">
                        <label style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:6px; display:block;">Trip Date</label>
                        <input type="date" id="quick-edit-trip-date" style="width:100%; padding:12px 14px; border-radius:14px; border:1.5px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#0f172a; margin-bottom:14px; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box;">
                        <label style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:8px; display:block;">Travel Companions</label>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                            <label id="quick-edit-opt-solo" onclick="window.setQuickEditTripType('solo')" style="display:flex; align-items:center; gap:8px; padding:11px 14px; border-radius:14px; background:#eff6ff; border:1.5px solid #2563eb; cursor:pointer;">
                                <input type="radio" name="quick_edit_companion_type" id="quick-edit-radio-solo" value="solo" checked style="accent-color:#2563eb; cursor:pointer; width:17px; height:17px;">
                                <span style="color:#1e3a8a; font-size:13px; font-weight:800;">Solo Trip</span>
                            </label>
                            <label id="quick-edit-opt-group" onclick="window.setQuickEditTripType('group')" style="display:flex; align-items:center; gap:8px; padding:11px 14px; border-radius:14px; background:#f8fafc; border:1.5px solid #e2e8f0; cursor:pointer;">
                                <input type="radio" name="quick_edit_companion_type" id="quick-edit-radio-group" value="group" style="accent-color:#2563eb; cursor:pointer; width:17px; height:17px;">
                                <span style="color:#64748b; font-size:13px; font-weight:800;">With Companions</span>
                            </label>
                        </div>
                        <input type="hidden" id="quick-edit-trip-type" value="solo">
                        <div id="quick-edit-companion-box" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:14px; margin-bottom:14px;">
                            <div style="font-size:12px; color:#475569; font-weight:700; margin-bottom:12px;">Additional Travelers:</div>
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                <span style="font-size:13px; font-weight:700; color:#1e293b;">Male</span>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <button type="button" onclick="window.adjustQuickEditCompanions('males', -1)" style="width:32px; height:32px; border-radius:50%; background:#334155; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center;">−</button>
                                    <span id="quick-edit-males-count" style="font-size:15px; font-weight:800; color:#0f172a; min-width:24px; text-align:center;">0</span>
                                    <button type="button" onclick="window.adjustQuickEditCompanions('males', 1)" style="width:32px; height:32px; border-radius:50%; background:#0284c7; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center;">+</button>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                <span style="font-size:13px; font-weight:700; color:#1e293b;">Female</span>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <button type="button" onclick="window.adjustQuickEditCompanions('females', -1)" style="width:32px; height:32px; border-radius:50%; background:#334155; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center;">−</button>
                                    <span id="quick-edit-females-count" style="font-size:15px; font-weight:800; color:#0f172a; min-width:24px; text-align:center;">0</span>
                                    <button type="button" onclick="window.adjustQuickEditCompanions('females', 1)" style="width:32px; height:32px; border-radius:50%; background:#db2777; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center;">+</button>
                                </div>
                            </div>
                            <div style="padding-top:10px; border-top:1px solid #e2e8f0; font-size:11.5px; color:#64748b; display:flex; align-items:center; justify-content:space-between;">
                                <span>Total Party Size:</span>
                                <span id="quick-edit-party-summary" style="color:#0284c7; font-weight:800;">1 Pax (Solo)</span>
                            </div>
                        </div>
                    </div>
                    <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:12px 16px max(12px, env(safe-area-inset-bottom)) 16px; flex-shrink:0; display:flex; flex-direction:row; gap:10px; align-items:center; justify-content:space-between;">
                        <button type="button" onclick="window.openPlannerFromQuickEdit()" style="flex:1; height:42px; border-radius:100px; border:1px solid rgba(255,255,255,0.28) !important; outline:none !important; background:rgba(255,255,255,0.14) !important; color:#ffffff !important; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center;">Edit Stops</button>
                        <button type="button" id="btn-save-quick-edit" onclick="window.submitQuickEditTripDetails()" style="flex:1; height:42px; border-radius:100px; border:none !important; outline:none !important; background:#ffffff !important; color:#1e3a8a !important; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important;">Save Changes</button>
                    </div>
                </div>
            </div>
            <script>
                (function() {
                    if (typeof window.fetchSavedTrips === 'function') {
                        window.fetchSavedTrips(false);
                    }
                })();
            </script>`;
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
                if (targetView === 'saved_trips' && typeof window.fetchSavedTrips === 'function') {
                    window.fetchSavedTrips(false);
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
            { id: 'weather-modal', fn: window.closeWeatherModal },
            { id: 'logout-confirm-modal', fn: window.closeLogoutModal },
            { id: 'push-notification-modal', fn: window.closePushNotificationModal },
            { id: 'game-confirm-modal', fn: window.closeGameConfirm },
            { id: 'game-success-modal', fn: window.closeGameSuccess },
            { id: 'game-alert-modal', fn: window.closeGameAlert }
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
            const isNetOffline = (typeof navigator.onLine === 'boolean' && !navigator.onLine) || 
                                 window.isAppOnline === false || 
                                 localStorage.getItem('intan_elyu_offline_mode') === 'true';
            const publicViews = ['splash', 'auth', 'download', 'reset-password'];
            const activeToken = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const cachedUser = localStorage.getItem('auth_user');

            if (!publicViews.includes(state.currentView)) {
                if (!activeToken && !cachedUser && !isNetOffline) {
                    navigateTo('auth');
                    return;
                }
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

        if (state.currentView === 'dashboard') {
            try {
                var rawUser = localStorage.getItem('auth_user') || localStorage.getItem('intan_elyu_user');
                if (rawUser) {
                    var u = typeof rawUser === 'string' ? JSON.parse(rawUser) : rawUser;
                    if (u) {
                        var nameEl = document.getElementById('dash-name');
                        if (nameEl && (u.name || u.full_name)) {
                            var fName = (u.name || u.full_name).split(' ')[0];
                            nameEl.innerHTML = 'Hi, ' + fName + '! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i>';
                        }
                        var avEl = document.getElementById('dash-avatar');
                        if (avEl) {
                            if (u.avatar || u.profile_picture || u.avatar_url) {
                                avEl.src = u.avatar || u.profile_picture || u.avatar_url;
                            } else if (u.name || u.full_name) {
                                avEl.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name || u.full_name) + '&background=007AFF&color=fff&rounded=true&bold=true&size=128';
                            }
                        }
                        var ptEl = document.getElementById('dash-stat-points');
                        if (ptEl && u.points !== undefined) {
                            ptEl.textContent = Number(u.points).toLocaleString();
                        }
                        var rkEl = document.getElementById('dash-stat-rank');
                        if (rkEl && (u.rank || u.position || u.ranking)) {
                            rkEl.textContent = '#' + (u.rank || u.position || u.ranking);
                        }
                        var plEl = document.getElementById('dash-stat-places');
                        if (plEl) {
                            var p = (u.places_visited !== undefined) ? u.places_visited : (u.completed_activities || 0);
                            plEl.textContent = p;
                        }
                    }
                }
            } catch(e) {}
        }

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
        document.body.classList.remove('modal-blurred', 'logout-open');
        document.documentElement.classList.remove('modal-blurred', 'logout-open');
        const bottomNav = document.getElementById('bottom-navigation');
        const currentView = document.body.getAttribute('data-view') || '';
        const noNavViews = ['splash', 'auth', 'about', 'terms', 'edit_profile', 'help', 'trip_map', 'saved_trips', 'saved_places', 'trending', 'reset-password', 'puzzles', 'discount', 'settings', 'user_manual', 'download'];
        if (bottomNav && !noNavViews.includes(currentView) && !document.body.classList.contains('sheet-open')) {
            bottomNav.classList.remove('nav-hidden');
        }
    };

    window.showLogoutConfirmationModal = function () {
        const modal = document.getElementById('logout-confirm-modal');
        if (modal) {
            // Port to document.body so it is never trapped by any transformed/animated container stacking context
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            // Apply modal-blurred and logout-open to blur and disable navigation bar
            document.body.classList.add('modal-blurred', 'logout-open');
            document.documentElement.classList.add('modal-blurred', 'logout-open');

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
                    k.startsWith('points_balance_') ||
                    k.startsWith('dashboard_data_') ||
                    k.startsWith('dashboard_rank_') ||
                    k.startsWith('dashboard_trips_') ||
                    k.startsWith('saved_trips_') ||
                    k.startsWith('saved_places_') ||
                    k.startsWith('leaderboard_data_') ||
                    k.startsWith('intan_cache_') ||
                    k.startsWith('game_done_') ||
                    k.startsWith('intan_elyu_offline_game_claims') ||
                    k.startsWith('intan_elyu_offline_checkins') ||
                    k.startsWith('intan_elyu_offline_visited_spots') ||
                    k === 'api_token' ||
                    k === 'intan_elyu_token' ||
                    k === 'Intan_Elyu_Token' ||
                    k === 'intan_elyu_real_token_backup' ||
                    k === 'auth_user' ||
                    k === 'auth_user_backup' ||
                    k === 'intan_elyu_user' ||
                    k === 'my_rank' ||
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
            localStorage.removeItem('api_token');
            localStorage.removeItem('intan_elyu_token');
            localStorage.removeItem('Intan_Elyu_Token');
            localStorage.removeItem('auth_user');
            localStorage.removeItem('auth_user_backup');
            localStorage.removeItem('intan_elyu_user');
            localStorage.removeItem('intan_elyu_claimed_vouchers');
            localStorage.removeItem('intan_elyu_cached_vouchers');
        }

        window._cachedMeData = null;
        window._cachedMyRank = null;
        window._cachedRawLeadersList = [];
        window.myUserData = null;
        window._userPointsBalance = null;
        window._cachedCompletedTrips = [];
        window.profileNeedsRefresh = true;
        window.leaderboardNeedsRefresh = true;
        window.dashboardNeedsRefresh = true;

        window._touristRedemptions = null;
        window._cachedActiveVouchers = null;
        window._cachedHistoryVouchers = null;
        window._practiceModes = {};

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
     * Resolves a proof image path or filename to a direct Cloudflare R2 bucket or API URL.
     * Prevents broken local relative links and ensures fast CDN delivery.
     * @param {string} rawUrl - Image URL or filename
     * @returns {string} Fully qualified image URL
     */
    window.resolveProofImageUrl = function (rawUrl) {
        if (!rawUrl || typeof rawUrl !== 'string') return '';
        var url = rawUrl.trim();
        if (!url) return '';
        if (url.startsWith('data:') || url.startsWith('blob:')) return url;

        var activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');

        // 1. Check for proof_xxx filename pattern -> route to reliable backend streaming endpoint
        var proofMatch = url.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
        if (proofMatch && proofMatch[1]) {
            return activeBackend + '/api/image/proof_images/' + proofMatch[1];
        }

        // 2. Full HTTP URLs
        if (url.startsWith('http://') || url.startsWith('https://')) {
            if (url.includes('r2.dev') || url.includes('localhost') || url.includes('127.0.0.1') || /192\.168\.\d+\.\d+/.test(url) || /10\.\d+\.\d+\.\d+/.test(url) || /172\.(1[6-9]|2\d|3[01])\.\d+\.\d+/.test(url)) {
                var clean = url.split('/').pop().replace(/[?#].*$/, '');
                var localProofMatch = clean.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                if (localProofMatch && localProofMatch[1]) {
                    return activeBackend + '/api/image/proof_images/' + localProofMatch[1];
                }
                return activeBackend + '/api/image/proof_images/' + clean;
            }
            return url;
        }

        // 3. Relative storage / API paths
        var cleanPath = url.replace(/^\/+/, '');
        if (cleanPath.startsWith('storage/proof_images/')) {
            var sFname = cleanPath.replace('storage/proof_images/', '');
            return activeBackend + '/api/image/proof_images/' + sFname;
        }
        if (cleanPath.startsWith('proof_images/')) {
            var pFname = cleanPath.replace('proof_images/', '');
            return activeBackend + '/api/image/proof_images/' + pFname;
        }
        if (cleanPath.startsWith('api/')) {
            return activeBackend + '/' + cleanPath;
        }

        var bareName = cleanPath.split('/').pop().replace(/[?#].*$/, '');
        return activeBackend + '/api/image/proof_images/' + bareName;
    };

    /**
     * Fallback URL for proof images when primary Cloudflare R2 fails.
     * @param {string} rawUrl
     * @returns {string}
     */
    window.getProofImageFallbackUrl = function (rawUrl) {
        if (!rawUrl || typeof rawUrl !== 'string') return 'assets/img/no_image.svg';
        var url = rawUrl.trim();
        if (url.startsWith('data:') || url.startsWith('blob:')) return url;

        var activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
        var proofMatch = url.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
        if (proofMatch && proofMatch[1]) {
            return activeBackend + '/api/image/proof_images/' + proofMatch[1];
        }
        var clean = url.replace(/^\/+/, '').split('/').pop().replace(/[?#].*$/, '');
        if (clean) {
            return activeBackend + '/api/image/proof_images/' + clean;
        }
        return 'assets/img/no_image.svg';
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

        // Resolve all URLs using resolveProofImageUrl so they load reliably
        urls = urls.map(function (u) {
            return (typeof window.resolveProofImageUrl === 'function') ? window.resolveProofImageUrl(u) : u;
        });

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
                    <button type="button" class="proof-nav-btn prev" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:38px; height:38px; border-radius:50%; background:rgba(15,23,42,0.8); color:#fff; border:1px solid rgba(255,255,255,0.25); display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:10; backdrop-filter:blur(8px);">
                        <i class="fa-solid fa-chevron-left" style="font-size:14px;"></i>
                    </button>
                    <button type="button" class="proof-nav-btn next" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:38px; height:38px; border-radius:50%; background:rgba(15,23,42,0.8); color:#fff; border:1px solid rgba(255,255,255,0.25); display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:10; backdrop-filter:blur(8px);">
                        <i class="fa-solid fa-chevron-right" style="font-size:14px;"></i>
                    </button>
                `;
            }

            var thumbStrip = '';
            if (total > 1) {
                var thumbsHtml = urls.map(function (u, idx) {
                    var isSelected = (idx === currentIndex);
                    var fallback = (typeof window.getProofImageFallbackUrl === 'function') ? window.getProofImageFallbackUrl(u) : 'assets/img/no_image.svg';
                    return `<img src="${u}" data-idx="${idx}" onerror="if(this.dataset.triedFallback!=='1'){this.dataset.triedFallback='1';this.src='${fallback}';}else{this.onerror=null;this.src='assets/img/no_image.svg';}" class="proof-thumb-item" style="width:44px; height:44px; border-radius:10px; object-fit:cover; border:${isSelected ? '2px solid #00f2fe' : '1px solid rgba(255,255,255,0.2)'}; opacity:${isSelected ? '1' : '0.6'}; cursor:pointer; flex-shrink:0; transition:all 0.2s ease;">`;
                }).join('');
                thumbStrip = `<div style="display:flex; gap:8px; overflow-x:auto; padding:6px 0; justify-content:center; max-width:100%; -webkit-overflow-scrolling:touch;">${thumbsHtml}</div>`;
            }

            var currentFallback = (typeof window.getProofImageFallbackUrl === 'function') ? window.getProofImageFallbackUrl(currentUrl) : 'assets/img/no_image.svg';

            modal.innerHTML = `
                <div style="position:relative; width:100%; max-width:440px; background:linear-gradient(135deg, rgba(30, 58, 138, 0.98), rgba(15, 23, 42, 0.99)); border-radius:24px; padding:20px; box-shadow:0 20px 50px rgba(0,0,0,0.6); display:flex; flex-direction:column; gap:14px; border:1px solid rgba(255,255,255,0.15);" onclick="event.stopPropagation();">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h3 style="margin:0; font-size:16px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-camera" style="color:#00f2fe; font-size:15px;"></i> ${title} ${counterText}
                            </h3>
                            ${subtitle ? `<span style="font-size:11px; color:#cbd5e1; font-weight:600; display:block; margin-top:2px;">${subtitle}</span>` : ''}
                        </div>
                        <button type="button" class="close-viewer-btn" onclick="document.getElementById('elyu-proof-viewer-modal')?.remove();" style="width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.18); border:none; outline:none; color:#ffffff; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div style="position:relative; width:100%; max-height:60vh; min-height:220px; border-radius:16px; overflow:hidden; background:#000; display:flex; align-items:center; justify-content:center;">
                        <img src="${currentUrl}" onerror="if(this.dataset.triedFallback!=='1'){this.dataset.triedFallback='1';this.src='${currentFallback}';}else{this.onerror=null;this.src='assets/img/no_image.svg';}" alt="Visit Proof" style="width:100%; height:auto; max-height:60vh; object-fit:contain; display:block;">
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
