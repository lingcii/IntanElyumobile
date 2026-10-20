<?php
$pageTitle = "Trip Route";
$backRoute = "saved_trips";
require_once __DIR__ . '/../components/header.php';
include_once __DIR__ . '/../components/testimony_modal.php';
?>

<div id="trip-map" style="width: 100%; height: 100vh; background: #0a0f1c;"></div>

<!-- Floating Destination Conveyor Carousel -->
<div id="trip-conveyor-wrapper"
    style="position: absolute; bottom: max(env(safe-area-inset-bottom), 16px); left: 0; right: 0; z-index: 1000; display: flex; flex-direction: column; align-items: center; gap: 12px; pointer-events: none; width: 100%; box-sizing: border-box; overflow: hidden;">

    <!-- Offline Navigation Status Pill -->
    <div id="trip-offline-badge"
        style="display: none; align-self: center; background: rgba(245, 158, 11, 0.22); border: 1px solid rgba(245, 158, 11, 0.45); border-radius: 100px; padding: 5px 14px; color: #fbbf24; font-size: 11px; font-weight: 800; gap: 6px; align-items: center; pointer-events: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
        <i class="fa-solid fa-cloud-slash"></i> Offline Navigation &bull; Geodesic Mode
    </div>

    <!-- Trip Overview Summary Header Pill -->
    <div id="trip-summary-pill"
        style="align-self: center; background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 100px; padding: 7px 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 10px 28px rgba(10, 25, 60, 0.35); pointer-events: auto;">
        <div style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800;">
            <i class="fa-solid fa-route" style="color:#67e8f9;"></i> <span id="trip-info-distance">-- km</span>
        </div>
        <div style="width:1px; height:12px; background:rgba(255,255,255,0.25);"></div>
        <div style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800;">
            <i class="fa-solid fa-stopwatch" style="color:#34d399;"></i> <span id="trip-info-time">-- mins</span>
        </div>
        <div style="width:1px; height:12px; background:rgba(255,255,255,0.25);"></div>
        <div id="trip-info-vehicle-badge"
            style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800; background:rgba(255,255,255,0.18); border:none !important; outline:none !important; border-radius:100px; padding:4px 10px;">
            <i id="trip-info-vehicle-icon" class="fa-solid fa-car" style="color:#f59e0b;"></i>
            <span id="trip-info-vehicle-name">Own Car</span>
        </div>
    </div>

    <!-- Conveyor Cards Carousel Scroll Container -->
    <div id="conveyor-cards-scroll"
        style="display: flex; justify-content: flex-start; align-items: stretch; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-padding: 0 16px; padding: 4px 16px 12px 16px; pointer-events: auto; scroll-behavior: smooth; width: 100%; box-sizing: border-box;"
        class="hide-scrollbar">
        <!-- Injected via JS -->
    </div>
</div>

<!-- Multi-Photo Proof Upload Modal (Independent Process) -->
<div id="proof-upload-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.78); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); z-index:999999; justify-content:center; align-items:center; padding:16px;">
    <div
        style="background:linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%); border:none; outline:none; border-radius:24px; padding:24px 20px; width:100%; max-width:380px; box-shadow:0 20px 50px rgba(10,25,60,0.55); text-align:center; max-height:90vh; overflow-y:auto;">
        
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="text-align:left;">
                <h3 id="proof-spot-title" style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-camera" style="color:#00f2fe; font-size:17px;"></i> Upload Photo Proof
                </h3>
                <span style="font-size:11px; color:#ffffff; opacity:0.9; font-weight:600;">Multiple photos allowed (up to 5)</span>
            </div>
            <button type="button" onclick="window.closeProofUploadModal()"
                style="background:rgba(255,255,255,0.18); border:none; outline:none; color:#ffffff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="font-size:14px;"></i>
            </button>
        </div>

        <p style="font-size:12px; color:#ffffff; opacity:0.95; line-height:1.45; text-align:left; margin-bottom:14px;">
            Capture selfies or photos at the destination. Staff will verify your submission to complete your visit and award <strong style="color:#fde047;">+50 Points</strong>.
        </p>

        <input type="file" id="proof-upload-input" accept="image/*" multiple style="display:none;" onchange="window.handleProofPhotosSelected(this)">

        <!-- Add Photo Action Buttons -->
        <div style="display:flex; gap:10px; margin-bottom:14px;">
            <button type="button" onclick="window.addProofPhotoFromSource('camera')"
                style="flex:1; padding:12px 10px; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none; outline:none; border-radius:14px; color:#ffffff; font-weight:800; font-size:12.5px; display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer; box-shadow:none;">
                <i class="fa-solid fa-camera" style="font-size:15px;"></i> Take Photo
            </button>
            <button type="button" onclick="window.addProofPhotoFromSource('gallery')"
                style="flex:1; padding:12px 10px; background:rgba(255,255,255,0.18); border:none; outline:none; border-radius:14px; color:#ffffff; font-weight:800; font-size:12.5px; display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer;">
                <i class="fa-solid fa-images" style="color:#00f2fe; font-size:15px;"></i> Add from Gallery
            </button>
        </div>

        <!-- Selected Photos Area -->
        <div style="margin-bottom:16px; text-align:left;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <label style="font-size:11px; font-weight:800; color:#ffffff; text-transform:uppercase; letter-spacing:0.5px;">
                    Selected Photos
                </label>
                <span id="proof-count-badge" style="font-size:11px; font-weight:700; color:#00f2fe;">0 / 5 photos</span>
            </div>

            <!-- Empty State Prompt -->
            <div id="proof-empty-prompt" onclick="window.addProofPhotoFromSource('gallery')"
                style="border:2px dashed rgba(255,255,255,0.35); border-radius:16px; padding:24px 16px; text-align:center; cursor:pointer; background:rgba(15,23,42,0.35); transition:all 0.2s ease;">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size:26px; color:#00f2fe; margin-bottom:6px; display:block;"></i>
                <p style="margin:0; font-size:12.5px; font-weight:800; color:#ffffff;">Tap to select photos</p>
                <p style="margin:4px 0 0 0; font-size:11px; color:rgba(255,255,255,0.75);">You can choose multiple photos</p>
            </div>

            <!-- Thumbnails Grid -->
            <div id="proof-thumbnails-grid" style="display:none; grid-template-columns:repeat(3, 1fr); gap:10px;"></div>
        </div>

        <!-- Submit & Cancel Buttons -->
        <div style="display:flex; flex-direction:column; gap:8px;">
            <button type="button" id="btn-submit-proof-photos" class="btn-primary"
                style="width:100%; padding:14px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; cursor:pointer;"
                onclick="window.submitProofPhotos()">
                <i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit Photo Proof
            </button>
            <button type="button"
                style="width:100%; padding:11px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.15); color:#ffffff; font-size:12.5px; font-weight:700; cursor:pointer;"
                onclick="window.closeProofUploadModal()">Cancel</button>
        </div>
    </div>
</div>




<script>
    (function () {
        var backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
        var tripMap;

        window.myLat = window.myLat || window.currentGPSLat || null;
        window.myLng = window.myLng || window.currentGPSLng || null;

        if (typeof window.requestPreciseLocation === 'function') {
            window.requestPreciseLocation(false).then(loc => {
                if (loc && loc.lat && loc.lng) {
                    window.myLat = loc.lat;
                    window.myLng = loc.lng;
                }
            }).catch(err => {
                console.warn("Direct GPS attempt in trip_map:", err && err.message);
            });
        } else if ((!window.myLat || !window.myLng) && navigator.geolocation && localStorage.getItem('intan_elyu_loc_enabled') !== 'false') {
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    window.myLat = pos.coords.latitude;
                    window.myLng = pos.coords.longitude;
                    window.currentGPSLat = pos.coords.latitude;
                    window.currentGPSLng = pos.coords.longitude;
                    window.currentGPSSource = 'gps';
                    document.dispatchEvent(new CustomEvent('gpsUpdated', {
                        detail: { lat: pos.coords.latitude, lng: pos.coords.longitude, accuracy: pos.coords.accuracy, source: 'gps' }
                    }));
                },
                function (err) {
                    console.warn("Direct GPS attempt in trip_map:", err && err.message);
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
            );
        }

        function initTripMap() {
            const style = {
                "version": 8,
                "glyphs": "https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",
                "sources": {
                    "osm": {
                        "type": "raster",
                        "tiles": [
                            "https://a.tile.openstreetmap.org/{z}/{x}/{y}.png",
                            "https://b.tile.openstreetmap.org/{z}/{x}/{y}.png",
                            "https://c.tile.openstreetmap.org/{z}/{x}/{y}.png"
                        ],
                        "tileSize": 256
                    },
                    "terrain": {
                        "type": "raster-dem",
                        "tiles": ["https://s3.amazonaws.com/elevation-tiles-prod/terrarium/{z}/{x}/{y}.png"],
                        "encoding": "terrarium",
                        "tileSize": 256
                    }
                },
                "layers": [
                    {
                        "id": "background",
                        "type": "background",
                        "paint": { "background-color": "#eef2f6" }
                    },
                    {
                        "id": "base-map",
                        "type": "raster",
                        "source": "osm",
                        "layout": { "visibility": "visible" }
                    }
                ]
            };

            tripMap = new maplibregl.Map({
                container: 'trip-map',
                style: style,
                center: [120.3186, 16.6159],
                zoom: 10,
                pitch: 0,
                bearing: 0,
                fadeDuration: 0,
                attributionControl: false
            });

            // tripMap.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'bottom-right');

            tripMap.on('load', async () => {
                loadTripData();
            });
        }

        const VEHICLE_CATALOG = [
            { key: 'own_car', name: 'Own Car', icon: 'fa-car', color: '#f59e0b', desc: 'Private automobile', speedKmH: 38 },
            { key: 'mpuj', name: 'Modern Jeepney (MPUJ)', icon: 'fa-van-shuttle', color: '#10b981', desc: 'Aircon modern utility jeepney', speedKmH: 26 },
            { key: 'tpuj', name: 'Traditional Jeepney (TPUJ)', icon: 'fa-van-shuttle', color: '#f59e0b', desc: 'Traditional open-air jeepney', speedKmH: 24 },
            { key: 'pub_aircon', name: 'PUB Aircon Bus', icon: 'fa-bus', color: '#38bdf8', desc: 'Aircon provincial commuter bus', speedKmH: 34 },
            { key: 'pub_ordinary', name: 'PUB Ordinary Bus', icon: 'fa-bus-simple', color: '#06b6d4', desc: 'Regular provincial commuter bus', speedKmH: 30 },
            { key: 'tricycle', name: 'Tricycle', icon: 'fa-motorcycle', color: '#10b981', desc: 'Local tricycle', speedKmH: 20 },
            { key: 'taxi', name: 'Taxi', icon: 'fa-taxi', color: '#eab308', desc: 'Metered or chartered taxi', speedKmH: 36 },
            { key: 'uve', name: 'UV Express / Van', icon: 'fa-van-shuttle', color: '#a855f7', desc: 'UV Express shuttle van', speedKmH: 32 },
            { key: 'motorcycle', name: 'Motorcycle', icon: 'fa-motorcycle', color: '#f97316', desc: 'Motorcycle', speedKmH: 36 },
            { key: 'jeepney', name: 'Jeepney', icon: 'fa-van-shuttle', color: '#10b981', desc: 'Public Utility Jeepney', speedKmH: 24, hiddenFromModal: true },
            { key: 'bus', name: 'Bus', icon: 'fa-bus', color: '#38bdf8', desc: 'Provincial bus', speedKmH: 32, hiddenFromModal: true },
            { key: 'private_bus', name: 'Aircon Bus', icon: 'fa-bus', color: '#38bdf8', desc: 'Aircon bus', speedKmH: 34, hiddenFromModal: true },
            { key: 'walking', name: 'Walking', icon: 'fa-person-walking', color: '#22c55e', desc: 'On-foot navigation', speedKmH: 4.5 }
        ];

        function resolveTripVehicle(trip, tripId) {
            // Strictly use the vehicle selected when saving the trip
            let raw = (trip ? trip.transport_mode : null);

            if (!raw && tripId) {
                try {
                    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                    if (token) {
                        const cacheKey = 'saved_trips_' + token.substring(0, 10);
                        const rawCached = localStorage.getItem(cacheKey);
                        if (rawCached) {
                            const parsed = typeof window.safeJsonParse === 'function' ? window.safeJsonParse(rawCached, null) : JSON.parse(rawCached);
                            if (parsed && Array.isArray(parsed.data)) {
                                const cachedTrip = parsed.data.find(t => t.id == tripId);
                                if (cachedTrip && cachedTrip.transport_mode) {
                                    raw = cachedTrip.transport_mode;
                                }
                            }
                        }
                    }
                } catch (e) { }
            }

            if (!raw) {
                const urlParams = new URLSearchParams(window.location.search);
                raw = urlParams.get('transport');
            }

            if (!raw && trip && trip.items && trip.items.length > 0) {
                for (let item of trip.items) {
                    if (item.transport_type) { raw = item.transport_type; break; }
                    if (item.destination && item.destination.transport_type) { raw = item.destination.transport_type; break; }
                }
            }

            if (!raw || raw.toLowerCase().includes('no_vehicle') || raw.toLowerCase().includes('no vehicle')) {
                return {
                    key: 'no_vehicle',
                    name: 'No Vehicle Selected',
                    icon: 'fa-ban',
                    color: '#f87171',
                    desc: 'No vehicle selected for this trip',
                    speedKmH: 25
                };
            }

            const parts = raw.split(/[,+]|\band\b/i).map(s => s.trim().toLowerCase().replace(/[- ]/g, '_')).filter(Boolean);
            if (parts.length > 1) {
                return {
                    key: 'mixed',
                    name: `${parts.length} Vehicles`,
                    icon: 'fa-route',
                    color: '#38bdf8',
                    desc: `${parts.length} vehicles selected`,
                    speedKmH: 30
                };
            }
            const firstRawKey = parts[0] || 'own_car';
            let found = VEHICLE_CATALOG.find(v => v.key === firstRawKey) ||
                VEHICLE_CATALOG.find(v => firstRawKey.includes(v.key)) ||
                (firstRawKey.includes('aircon') ? VEHICLE_CATALOG.find(v => v.key === 'pub_aircon') : null) ||
                (firstRawKey.includes('ordinary') || firstRawKey.includes('regular') ? VEHICLE_CATALOG.find(v => v.key === 'pub_ordinary') : null) ||
                (firstRawKey.includes('mpuj') ? VEHICLE_CATALOG.find(v => v.key === 'mpuj') : null) ||
                (firstRawKey.includes('tpuj') ? VEHICLE_CATALOG.find(v => v.key === 'tpuj') : null) ||
                (firstRawKey.includes('car') ? VEHICLE_CATALOG[0] : null) ||
                (firstRawKey.includes('jeep') ? VEHICLE_CATALOG.find(v => v.key === 'mpuj') : null) ||
                (firstRawKey.includes('tri') ? VEHICLE_CATALOG.find(v => v.key === 'tricycle') : null) ||
                (firstRawKey.includes('bus') ? VEHICLE_CATALOG.find(v => v.key === 'pub_aircon') : null) ||
                (firstRawKey.includes('motor') ? VEHICLE_CATALOG.find(v => v.key === 'motorcycle') : null) ||
                VEHICLE_CATALOG[0];

            let displayName = found.name;

            return {
                key: found.key,
                name: displayName,
                icon: found.icon,
                color: found.color,
                speedKmH: found.speedKmH
            };
        }

        function applyVehicleToUI(vehicle) {
            if (!vehicle) return;
            window.currentActiveVehicle = vehicle;
            const vehicleNameEl = document.getElementById('trip-info-vehicle-name');
            const vehicleIconEl = document.getElementById('trip-info-vehicle-icon');
            if (vehicleNameEl) vehicleNameEl.textContent = vehicle.name;
            if (vehicleIconEl) {
                vehicleIconEl.className = 'fa-solid ' + vehicle.icon;
                vehicleIconEl.style.color = vehicle.color || '#f59e0b';
            }
        }

        // Immediate frame-0 vehicle initialization so there is zero flicker of "Own Car"
        (function initImmediateVehicle() {
            const urlParams = new URLSearchParams(window.location.search);
            const tripId = urlParams.get('trip_id') || sessionStorage.getItem('active_trip_id') || window.currentTripId;
            if (tripId) {
                const v = resolveTripVehicle(null, tripId);
                applyVehicleToUI(v);
            }
        })();

        // Stubs for modal removal to avoid errors if triggered anywhere
        window.openVehicleSelectorModal = function () { };
        window.closeVehicleSelectorModal = function () { };
        window.selectTripVehicle = function () { };

        function calcCoordDistMeters(lat1, lon1, lat2, lon2) {
            if (!lat1 || !lon1 || !lat2 || !lon2) return 999999;
            const p = 0.017453292519943295;
            const c = Math.cos;
            const a = 0.5 - c((lat2 - lat1) * p) / 2 + c(lat1 * p) * c(lat2 * p) * (1 - c((lon2 - lon1) * p)) / 2;
            return 12742000 * Math.asin(Math.sqrt(a));
        }

        function renderConveyorCards(items, activeIndex) {
            const conveyorScroll = document.getElementById('conveyor-cards-scroll');
            if (!conveyorScroll) return;

            // If conveyor is already rendered for this trip and count matches, only update active status
            const existingCards = conveyorScroll.querySelectorAll('.conveyor-card');
            if (existingCards.length === items.length && window._conveyorRenderedTripId === window.currentTripId) {
                items.forEach((item, idx) => {
                    const card = document.getElementById(`conveyor-card-${idx}`);
                    if (!card) return;
                    const isVisited = Boolean(item.is_visited || item.proof_status === 'approved');
                    const isPending = Boolean(item.proof_image && (item.proof_status === 'pending' || !item.proof_status));
                    const isRejected = (item.proof_status === 'rejected');
                    const isQueued = Boolean(window.OfflineCheckinManager && window.OfflineCheckinManager.isItemQueued(item.id));
                    const isActive = idx === activeIndex;
                    if (isActive) {
                        card.classList.add('active');
                    } else {
                        card.classList.remove('active');
                    }
                    const badgeEl = card.querySelector('.conveyor-status-badge');
                    if (badgeEl) {
                        if (isQueued) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Queued (Offline)';
                            badgeEl.style.background = 'rgba(245, 158, 11, 0.25)';
                            badgeEl.style.color = '#fde68a';
                        } else if (isVisited) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Visited';
                            badgeEl.style.background = 'rgba(52, 199, 89, 0.25)';
                            badgeEl.style.color = '#ffffff';
                        } else if (isPending) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-clock"></i> Pending Review';
                            badgeEl.style.background = 'rgba(245, 158, 11, 0.25)';
                            badgeEl.style.color = '#ffffff';
                        } else if (isRejected) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Rejected';
                            badgeEl.style.background = 'rgba(239, 68, 68, 0.25)';
                            badgeEl.style.color = '#ffffff';
                        } else if (isActive) {
                            badgeEl.innerHTML = `Stop ${idx + 1} of ${items.length} • NEXT`;
                            badgeEl.style.background = 'rgba(255, 255, 255, 0.25)';
                            badgeEl.style.color = '#ffffff';
                        } else {
                            badgeEl.innerHTML = `Stop ${idx + 1} of ${items.length}`;
                            badgeEl.style.background = 'rgba(255, 255, 255, 0.15)';
                            badgeEl.style.color = 'rgba(255, 255, 255, 0.85)';
                        }
                    }
                });
                return;
            }

            window._conveyorRenderedTripId = window.currentTripId;
            let conveyorHtml = '';
            items.forEach((item, idx) => {
                const dest = item.destination;
                if (!dest) return;
                const lat = parseFloat(dest.lat || dest.latitude);
                const lng = parseFloat(dest.lng || dest.longitude);
                const isVisited = Boolean(item.is_visited || item.proof_status === 'approved');
                const isRejected = (item.proof_status === 'rejected');
                const isLocationVerified = Boolean(item.is_location_verified || item.visited_at);
                const queuedItem = window.OfflineCheckinManager ? window.OfflineCheckinManager.getQueuedItem(item.id) : null;
                const isQueued = Boolean(queuedItem);
                const isActive = idx === activeIndex;

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
                const hasProof = Boolean(proofImages.length > 0);
                const isPendingApproval = Boolean(isLocationVerified && hasProof && (item.proof_status === 'pending' || !item.proof_status) && !isVisited);

                let badgeHtml = '';
                if (isQueued) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(245,158,11,0.25); border:none !important; outline:none !important; color:#fde68a; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-cloud-arrow-up"></i> Queued (Offline)</span>`;
                } else if (isVisited) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(52,199,89,0.25); border:none !important; outline:none !important; color:#ffffff; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-circle-check"></i> Visited</span>`;
                } else if (isRejected) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(239,68,68,0.25); border:none !important; outline:none !important; color:#ffffff; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>`;
                } else if (isPendingApproval) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(245,158,11,0.25); border:none !important; outline:none !important; color:#ffffff; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-clock"></i> Pending Review</span>`;
                } else if (isLocationVerified && !hasProof) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(56,189,248,0.25); border:none !important; outline:none !important; color:#38bdf8; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-location-dot"></i> Checked In</span>`;
                } else if (!isLocationVerified && hasProof) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(167,139,250,0.25); border:none !important; outline:none !important; color:#c4b5fd; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-camera"></i> Photo Ready</span>`;
                } else if (isActive) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(255,255,255,0.25); border:none !important; outline:none !important; color:#ffffff; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;">Stop ${idx + 1} of ${items.length} • NEXT</span>`;
                } else {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(255,255,255,0.15); border:none !important; outline:none !important; color:rgba(255,255,255,0.85); padding:4px 12px; border-radius:100px; font-size:10px; font-weight:700; flex-shrink:0;">Stop ${idx + 1} of ${items.length}</span>`;
                }

                const destEscaped = (dest && dest.name) ? dest.name.replace(/'/g, "\\'") : 'Destination';
                const jsonUrlsAttr = JSON.stringify(proofImages).replace(/"/g, '&quot;');

                let proofThumbnailStrip = '';
                if (proofImages.length > 0) {
                    const activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                    proofThumbnailStrip = `<div style="display:inline-flex; align-items:center; gap:4px; flex-shrink:0;">
                        ${proofImages.slice(0, 3).map((imgUrl, pIdx) => {
                            let pUrl = imgUrl;
                            if (!pUrl.startsWith('http') && !pUrl.startsWith('data:') && !pUrl.startsWith('blob:')) {
                                pUrl = activeBackend + '/' + pUrl.replace(/^\//, '');
                            }
                            const cleanImgName = pUrl.split('/').pop().replace(/[?#].*$/, '');
                            let fallbackUrl = activeBackend + '/api/image/proof_images/' + cleanImgName;
                            return `<img src="${pUrl}" onerror="if(this.dataset.triedFallback!=='1'){this.dataset.triedFallback='1';this.src='${fallbackUrl}';}" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal(${jsonUrlsAttr}, '${destEscaped}', '${isVisited ? 'Verified Visit Proof' : 'Uploaded Proof'}');" alt="Proof" title="Tap to view proof photo" style="width:30px; height:30px; border-radius:7px; object-fit:cover; border:1px solid rgba(255,255,255,0.3); flex-shrink:0; cursor:pointer;">`;
                        }).join('')}
                        ${proofImages.length > 3 ? `<span onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal(${jsonUrlsAttr}, '${destEscaped}', '${isVisited ? 'Verified Visit Proof' : 'Uploaded Proof'}');" style="background:rgba(0,0,0,0.7); color:#fff; font-size:8px; font-weight:800; padding:2px 4px; border-radius:4px; cursor:pointer;">+${proofImages.length - 3}</span>` : ''}
                    </div>`;
                }

                let queuedThumbnail = '';
                if (isQueued && queuedItem) {
                    const qSrc = queuedItem.previewUrl || '';
                    if (qSrc) {
                        queuedThumbnail = `<img src="${qSrc}" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal('${qSrc}', '${destEscaped}', 'Offline Proof (Queued on Device)');" alt="Proof" title="Tap to view offline proof photo" style="width:30px; height:30px; border-radius:7px; object-fit:cover; border:1.5px solid #f59e0b !important; box-shadow:none !important; flex-shrink:0; cursor:pointer;">`;
                    } else {
                        queuedThumbnail = `<div style="width:30px; height:30px; border-radius:7px; background:rgba(245,158,11,0.2); border:1px dashed #f59e0b; display:flex; align-items:center; justify-content:center; color:#fbbf24; flex-shrink:0;"><i class="fa-solid fa-camera" style="font-size:11px;"></i></div>`;
                    }
                }

                let actionBtnHtml = '';
                if (isQueued) {
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${queuedThumbnail}
                        <span style="background:rgba(245,158,11,0.25); border:none !important; outline:none !important; color:#fde68a; font-weight:800; font-size:10.5px; padding:3px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="fa-solid fa-cloud-arrow-up"></i> Queued</span>
                    </div>`;
                } else if (isVisited) {
                    const sId = item.tourist_spot_id || (item.destination ? item.destination.id : '');
                    const isReviewed = sId && window.userReviewedSpotIds && window.userReviewedSpotIds.has(Number(sId));
                    const sClass = (dest && dest.classification_status) ? dest.classification_status : '';
                    const sMeta = (window.getRewardPointsForClassification) ? window.getRewardPointsForClassification(sClass) : { points: 50 };
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <span style="background:rgba(52,199,89,0.25); border:none !important; outline:none !important; color:#ffffff; font-weight:800; font-size:10px; padding:3px 7px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Visited</span>
                            <button type="button" data-spot-id="${sId}" data-spot-classification="${sClass}" onclick="event.stopPropagation(); window.openWriteTestimonyModal('${sId}', this)" style="background:rgba(255,255,255,0.18); border:none !important; outline:none !important; color:#ffffff; font-size:9.5px; font-weight:700; padding:2px 7px; border-radius:100px; cursor:pointer; width:fit-content;">
                                ${isReviewed ? '<i class="fa-solid fa-check"></i> Reviewed' : `<i class="fa-solid fa-pen"></i> Review (+${sMeta.points} PTS)`}
                            </button>
                        </div>
                    </div>`;
                } else if (isRejected) {
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <span style="background:rgba(239,68,68,0.25); border:none !important; outline:none !important; color:#ffffff; font-weight:800; font-size:10px; padding:2px 6px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                            <button onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')" style="background:linear-gradient(135deg, #ef4444, #dc2626); color:#ffffff; border:none !important; outline:none !important; padding:4px 8px; border-radius:100px; font-weight:800; font-size:9.5px; cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-camera" style="margin-right:2px;"></i> Re-upload Proof</button>
                        </div>
                    </div>`;
                } else if (isPendingApproval) {
                    // State 4: Both Submitted
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <span style="background:rgba(245,158,11,0.25); border:none !important; outline:none !important; color:#ffffff; font-weight:800; font-size:10px; padding:4px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="fa-solid fa-clock"></i> Awaiting Approval</span>
                    </div>`;
                } else if (isLocationVerified && !hasProof) {
                    // State 2: Location Only
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        <span style="background:rgba(56,189,248,0.2); color:#38bdf8; font-weight:800; font-size:10px; padding:4px 8px; border-radius:100px; white-space:nowrap;"><i class="fa-solid fa-location-dot"></i> Checked In</span>
                        <button onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; border:none !important; outline:none !important; padding:6px 11px; border-radius:100px; font-weight:800; font-size:10.5px; box-shadow:none !important; cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-camera" style="margin-right:3px;"></i> Upload Proof</button>
                    </div>`;
                } else if (!isLocationVerified && hasProof) {
                    // State 3: Photo Only
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <button onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${lat}, ${lng}, this)" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; border:none !important; outline:none !important; padding:6px 11px; border-radius:100px; font-weight:800; font-size:10.5px; box-shadow:none !important; cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-location-crosshairs" style="margin-right:3px;"></i> Check In</button>
                    </div>`;
                } else {
                    // State 1: Not Started
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:5px; flex-shrink:0;">
                        <button onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${lat}, ${lng}, this)" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; border:none !important; outline:none !important; padding:6px 10px; border-radius:100px; font-weight:800; font-size:10.5px; box-shadow:none !important; cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-location-crosshairs" style="margin-right:3px;"></i> Check In</button>
                        <button onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')" style="background:rgba(255,255,255,0.18); color:#ffffff; border:none !important; outline:none !important; padding:6px 10px; border-radius:100px; font-weight:800; font-size:10.5px; cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-camera" style="margin-right:3px;"></i> Proof</button>
                    </div>`;
                }

                const classBadge = dest.classification_status ? `<span style="padding: 3px 8px; border-radius: 100px; font-size: 8px; font-weight: 800; text-transform: uppercase; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#0284c7' : (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; border:none !important; outline:none !important; flex-shrink:0;">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : '';

                let legTransitHtml = '';
                if (item.transport_mode) {
                    const isSpotMaint = Boolean(
                        (dest && (dest.is_maintenance === 1 || dest.is_maintenance === true || dest.is_maintenance === '1' || (dest.status && String(dest.status).toLowerCase().includes('maint')))) ||
                        item.transport_mode === 'suspended'
                    );

                    if (isSpotMaint) {
                        legTransitHtml = `<div style="display:flex; align-items:center; gap:6px; margin-bottom:8px; flex-wrap:wrap;">
                        <span style="background:#ef4444; color:#ffffff; padding:2px 8px; border-radius:100px; font-size:10px; font-weight:800; display:inline-flex; align-items:center; gap:4px; box-shadow:0 2px 6px rgba(239,68,68,0.3);">
                            <i class="fa-solid fa-ban" style="color:#ffffff; font-size:9px;"></i> Unavailable (Maintenance) &bull; ₱0.00
                        </span>
                    </div>`;
                    } else {
                        const legParts = String(item.transport_mode).split(/[,+]|\band\b/i).map(s => s.trim().toLowerCase().replace(/[- ]/g, '_')).filter(Boolean);
                        let vIcon = 'fa-van-shuttle';
                        let vName = item.transport_mode;

                        if (legParts.length > 1) {
                            vIcon = 'fa-route';
                            vName = `${legParts.length} Vehicles`;
                        } else if (legParts.length === 1) {
                            const single = legParts[0];
                            if (single === 'own_car' || single === 'car') { vIcon = 'fa-car'; vName = 'Own Car'; }
                            else if (single === 'motorcycle') { vIcon = 'fa-motorcycle'; vName = 'Motorcycle'; }
                            else if (single === 'tricycle' || single === 'trike') { vIcon = 'fa-motorcycle'; vName = 'Tricycle'; }
                            else if (single === 'mpuj') { vIcon = 'fa-bus-simple'; vName = 'Modern Jeepney'; }
                            else if (single === 'tpuj') { vIcon = 'fa-van-shuttle'; vName = 'Traditional Jeepney'; }
                            else if (single === 'pub_aircon' || single.includes('aircon')) { vIcon = 'fa-bus'; vName = 'Aircon Bus'; }
                            else if (single === 'pub_regular' || single === 'pub_ordinary' || single.includes('ordinary') || single.includes('regular')) { vIcon = 'fa-bus-simple'; vName = 'Ordinary Bus'; }
                            else if (single === 'uve') { vIcon = 'fa-van-shuttle'; vName = 'UV Express'; }
                            else if (single === 'van') { vIcon = 'fa-van-shuttle'; vName = 'Van'; }
                            else if (single === 'taxi') { vIcon = 'fa-taxi'; vName = 'Taxi'; }
                            else if (single === 'bus' || single === 'private_bus') { vIcon = 'fa-bus'; vName = 'Bus'; }
                            else if (single === 'walking' || single === 'walk') { vIcon = 'fa-person-walking'; vName = 'Walk / Hike'; }
                            else {
                                vName = single.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            }
                        }

                        const cNum = parseFloat(item.leg_cost || 0);
                        const cStr = (cNum > 0) ? `&bull; ₱${cNum.toFixed(2)}` : (legParts.length === 1 && ['own_car', 'motorcycle', 'walking'].includes(legParts[0]) ? '&bull; ₱0' : '');

                        legTransitHtml = `<div style="display:flex; align-items:center; gap:6px; margin-bottom:8px; flex-wrap:wrap;">
                        <span style="background:rgba(255,255,255,0.18); color:#e0f2fe; padding:2px 8px; border-radius:100px; font-size:10px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                            <i class="fa-solid ${vIcon}" style="color:#00f2fe; font-size:9px;"></i> ${vName} ${cStr}
                        </span>
                        ${(dest && (dest.accessible_by_private_vehicle === 0 || dest.accessible_by_private_vehicle === false || dest.accessible_by_private_vehicle === '0')) ? `<span style="background:rgba(245,158,11,0.22); color:#fef3c7; padding:2px 7px; border-radius:100px; font-size:9px; font-weight:800; display:inline-flex; align-items:center; gap:3px;"><i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;"></i> Trailhead</span>` : ''}
                    </div>`;
                    }
                }

                conveyorHtml += `
            <div id="conveyor-card-${idx}" class="conveyor-card ${isActive ? 'active' : ''}" onclick="window.flyToConveyorSpot(${lng}, ${lat}, ${idx})" style="scroll-snap-align: center; flex: 0 0 calc(100vw - 64px); max-width: 320px; min-width: 250px; box-sizing: border-box; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 24px; padding: 16px 18px; box-shadow: none !important; overflow: hidden !important; cursor: pointer; transition: transform 0.25s ease;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; gap:8px;">
                    ${badgeHtml}
                    ${classBadge}
                </div>
                <h4 style="margin:0 0 4px 0; font-size:16px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${dest.name}">${dest.name}</h4>
                <p style="margin:0 0 8px 0; font-size:12px; color:rgba(255,255,255,0.85); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fa-solid fa-location-dot" style="color:#67e8f9; margin-right:5px;"></i>${dest.municipality || 'La Union'}</p>
                ${legTransitHtml}
                <div style="display:flex; justify-content:space-between; align-items:center; gap:6px; min-width:0;">
                    <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.75); flex-shrink:0;"><i class="fa-solid fa-compass" style="color:#67e8f9;"></i> Tap to view</span>
                    <div style="flex-shrink:0; max-width:68%;">${actionBtnHtml}</div>
                </div>
            </div>`;
            });

            conveyorScroll.innerHTML = conveyorHtml;
            if (typeof window.syncReviewedButtons === 'function') {
                window.syncReviewedButtons();
            }
            setTimeout(() => {
                const targetCard = document.querySelector('.conveyor-card.active') || document.getElementById('conveyor-card-0');
                if (targetCard) {
                    targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            }, 120);
        }

        function loadTripData() {
            const urlParams = new URLSearchParams(window.location.search);
            const tripId = urlParams.get('trip_id') || sessionStorage.getItem('active_trip_id') || window.currentTripId;
            window.currentTripId = tripId;

            if (!tripId) {
                if (typeof showToast === 'function') showToast("No trip ID provided.");
                return;
            }

            const applyTripToMap = (trip) => {
                if (!document.getElementById('trip-map')) return; // Page was unmounted
                window.currentTrip = trip;
                const headerTitleEl = document.querySelector('.header-title');
                if (headerTitleEl) headerTitleEl.textContent = trip.title;
                const nameEl = document.getElementById('trip-info-name');
                if (nameEl) nameEl.textContent = trip.title;

                if (trip.items && trip.items.length > 0) {
                    const descEl = document.getElementById('trip-info-desc');
                    if (descEl) descEl.textContent = `Route preview for ${trip.items.length} destination(s).`;
                    const routeTypeEl = document.getElementById('trip-info-route-type');
                    if (routeTypeEl) routeTypeEl.textContent = trip.route_type || 'Recommended';

                    // Resolve selected vehicle dynamically from trip / session / storage
                    const vehicleInfo = resolveTripVehicle(trip, tripId);
                    applyVehicleToUI(vehicleInfo);

                    const conveyorWrapper = document.getElementById('trip-conveyor-wrapper');
                    if (conveyorWrapper) conveyorWrapper.style.display = 'flex';
                    window.currentTripItems = trip.items;
                    window.currentRouteType = trip.route_type || 'Recommended';
                    plotTrip(window.currentTripItems, window.currentRouteType);
                } else {
                    if (typeof showToast === 'function') showToast("This trip has no destinations yet.");
                }
            };

            const resolveTripOffline = (targetId) => {
                if (!targetId) return null;
                if (window.currentTrip && (window.currentTrip.id == targetId || window.currentTrip.local_id == targetId)) {
                    return window.currentTrip;
                }
                // Check localStorage cached saved_trips
                try {
                    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                    const tokenKey = token ? token.substring(0, 10) : 'default';
                    const raw = localStorage.getItem('saved_trips_' + tokenKey);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        const list = Array.isArray(parsed) ? parsed : (parsed.itineraries || parsed.data || []);
                        const found = list.find(t => t.id == targetId || t.local_id == targetId);
                        if (found) return found;
                    }
                } catch (e) {}

                // Check OfflineTripManager queued trips
                if (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function') {
                    const queued = window.OfflineTripManager.getQueuedTrips();
                    const found = queued.find(t => t.id == targetId || t.local_id == targetId);
                    if (found) return found;
                }

                return null;
            };

            // If it is a locally stored offline trip ID, resolve offline immediately
            if (String(tripId).startsWith('offline_')) {
                const offlineTrip = resolveTripOffline(tripId);
                if (offlineTrip) {
                    applyTripToMap(offlineTrip);
                    return;
                }
            }

            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            if (!token) {
                const offlineTrip = resolveTripOffline(tripId);
                if (offlineTrip) {
                    applyTripToMap(offlineTrip);
                }
                return;
            }

            if (typeof showToast === 'function') showToast("Loading trip route...");

            const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 8000) : null;

            fetch(backendUrl + '/api/tourist/itineraries', {
                signal: controller ? controller.signal : undefined,
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (timeoutId) clearTimeout(timeoutId);
                    if (data && data.itineraries) {
                        const trip = data.itineraries.find(t => t.id == tripId || t.local_id == tripId);
                        if (trip) {
                            applyTripToMap(trip);
                            return;
                        }
                    }
                    // Fall back to offline cache if not found in payload
                    const offlineTrip = resolveTripOffline(tripId);
                    if (offlineTrip) applyTripToMap(offlineTrip);
                })
                .catch(e => {
                    if (timeoutId) clearTimeout(timeoutId);
                    console.warn("Network load failed, falling back to offline cache:", e);
                    const offlineTrip = resolveTripOffline(tripId);
                    if (offlineTrip) {
                        applyTripToMap(offlineTrip);
                    } else {
                        console.error("Failed to load trip", e);
                    }
                });
        }

        function plotTrip(items, routeType = 'Recommended') {
            if (!items || items.length === 0) return;

            // NOTE: We preserve the saved sequence of items without dynamic distance re-sorting
            // to prevent erratic stop order flipping ("fluctuating codes") as the user moves!
            const coords = [];
            const markerPoints = [];
            const bounds = new maplibregl.LngLatBounds();

            // Clear old destination markers before re-drawing
            if (window.tripMarkers) window.tripMarkers.forEach(m => m.remove());
            window.tripMarkers = [];

            const activeIndex = items.findIndex(i => {
                const isQueued = window.OfflineCheckinManager && window.OfflineCheckinManager.isItemQueued(i.id);
                return !(i.is_visited || i.proof_status === 'approved' || isQueued);
            });

            items.forEach((item, idx) => {
                const dest = item.destination;
                if (dest) {
                    const lat = parseFloat(dest.lat || dest.latitude);
                    const lng = parseFloat(dest.lng || dest.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        // Only draw routing segment to the next active destination
                        if (idx === activeIndex || (!window.myLat && !window.myLng)) {
                            coords.push(`${lng},${lat}`);
                        }
                        markerPoints.push([lng, lat]);
                        bounds.extend([lng, lat]);

                        let iconHtml = '';
                        const isVisited = Boolean(item.is_visited || item.proof_status === 'approved');
                        const isRejected = (item.proof_status === 'rejected');
                        const isLocationVerified = Boolean(item.is_location_verified || item.visited_at);
                        const hasProof = Boolean((Array.isArray(item.proof_images) && item.proof_images.length > 0) || item.proof_image);
                        const isPending = Boolean(isLocationVerified && hasProof && (item.proof_status === 'pending' || !item.proof_status) && !isVisited);
                        const isLocOnly = Boolean(isLocationVerified && !hasProof && !isVisited);
                        const isPhotoOnly = Boolean(!isLocationVerified && hasProof && !isVisited);
                        const isQueued = Boolean(window.OfflineCheckinManager && window.OfflineCheckinManager.isItemQueued(item.id));

                        if (isQueued) {
                            // QUEUED OFFLINE - Amber Cloud / Clock
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(245,158,11,0.4); z-index: 2;">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 12px rgba(10,25,60,0.3); z-index: 1;">
                                    <span style="color: #fde68a; font-size: 10px; font-weight: 700;">Queued (Offline)</span>
                                </div>
                            </div>
                        `;
                        } else if (isVisited) {
                            // VISITED - Green Checkmark + Royal Blue Tag without outline
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(16,185,129,0.4); z-index: 2;">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 12px rgba(10,25,60,0.3); z-index: 1;">
                                    <span style="color: rgba(255, 255, 255, 0.75); font-size: 11px; font-weight: 600; text-decoration: line-through;">${dest.name}</span>
                                </div>
                            </div>
                        `;
                        } else if (isPending) {
                            // PENDING REVIEW - Orange Clock
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(245,158,11,0.4); z-index: 2;">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 12px rgba(10,25,60,0.3); z-index: 1;">
                                    <span style="color: #fde68a; font-size: 10px; font-weight: 700;">Pending Review</span>
                                </div>
                            </div>
                        `;
                        } else if (isRejected) {
                            // REJECTED - Red X
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(239,68,68,0.4); z-index: 2;">
                                    <i class="fa-solid fa-xmark"></i>
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 12px rgba(10,25,60,0.3); z-index: 1;">
                                    <span style="color: #fca5a5; font-size: 10px; font-weight: 700;">Re-upload</span>
                                </div>
                            </div>
                        `;
                        } else if (isLocOnly) {
                            // LOCATION ONLY - Cyan GPS Check
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(2,132,199,0.4); z-index: 2;">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 12px rgba(10,25,60,0.3); z-index: 1;">
                                    <span style="color: #38bdf8; font-size: 10px; font-weight: 700;">Checked In</span>
                                </div>
                            </div>
                        `;
                        } else if (isPhotoOnly) {
                            // PHOTO ONLY - Purple Camera
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(109,40,217,0.4); z-index: 2;">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 12px rgba(10,25,60,0.3); z-index: 1;">
                                    <span style="color: #c4b5fd; font-size: 10px; font-weight: 700;">Photo Ready</span>
                                </div>
                            </div>
                        `;
                        } else if (idx === activeIndex) {
                            // ACTIVE - Vibrant Cyan/Blue Pin + Royal Blue Next Stop Tag without outline
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color: #ffffff; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; border: 2.5px solid #ffffff; box-shadow: 0 4px 16px rgba(2, 132, 199, 0.5); z-index: 2;">
                                    ${idx + 1}
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: none !important; outline: none !important; border-radius: 12px; padding: 5px 10px; margin-top: 6px; display: flex; align-items: center; gap: 6px; box-shadow: 0 8px 20px rgba(10,25,60,0.4); z-index: 1;">
                                    <span style="background: rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.4px;">Next</span>
                                    <span style="color: #ffffff; font-size: 12px; font-weight: 700; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${dest.name}</span>
                                </div>
                            </div>
                        `;
                        } else {
                            // LOCKED - Grey Padlock + Stop Number
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; opacity: 0.88; cursor: pointer;">
                                <div style="background: #475569; color: #ffffff; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px; border: 2px solid rgba(255,255,255,0.6); box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 2;">
                                    <i class="fa-solid fa-lock" style="font-size: 10px;"></i>
                                </div>
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.92) 0%, rgba(63, 125, 183, 0.90) 100%); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: none !important; outline: none !important; border-radius: 8px; padding: 3px 8px; margin-top: 4px; z-index: 1;">
                                    <span style="color: rgba(255, 255, 255, 0.85); font-size: 10px; font-weight: 600;">Stop ${idx + 1}</span>
                                </div>
                            </div>
                        `;
                        }

                        const el = document.createElement('div');
                        el.innerHTML = iconHtml;
                        el.style.display = 'flex';
                        el.style.flexDirection = 'column';
                        el.style.alignItems = 'center';

                        const m = new maplibregl.Marker({ element: el, anchor: 'center' })
                            .setLngLat([lng, lat])
                            .addTo(tripMap);

                        window.tripMarkers.push(m);
                    }
                }
            });

            // Stable conveyor card rendering without jumpy DOM destruction
            renderConveyorCards(items, activeIndex);

            window.flyToConveyorSpot = function (lng, lat, idx) {
                if (tripMap && !isNaN(lng) && !isNaN(lat)) {
                    tripMap.flyTo({
                        center: [lng, lat],
                        zoom: 15,
                        pitch: 30,
                        duration: 1200
                    });
                }
                const card = document.getElementById(`conveyor-card-${idx}`);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            };

            // Clear existing GPS marker if any
            if (window.tripGpsMarker) {
                window.tripGpsMarker.remove();
                window.tripGpsMarker = null;
            }

            // Dynamically connect the physical GPS location as the starting point of the route
            if (window.myLat && window.myLng) {
                coords.unshift(`${window.myLng},${window.myLat}`);
                markerPoints.unshift([window.myLng, window.myLat]);
                bounds.extend([window.myLng, window.myLat]);

                const gpsEl = document.createElement('div');
                gpsEl.className = 'user-gps-tracking-marker';
                gpsEl.innerHTML = `
                <div style="position:relative; width:24px; height:24px; display:flex; align-items:center; justify-content:center;">
                    <div style="position:absolute; width:40px; height:40px; border-radius:50%; background:rgba(56,189,248,0.38); animation:markerPulse 2s infinite ease-out; pointer-events:none;"></div>
                    <div style="position:relative; background:#0284c7; width:20px; height:20px; border-radius:50%; border:2.5px solid #ffffff; box-shadow:0 2px 8px rgba(2,132,199,0.6); z-index:2;"></div>
                </div>
            `;
                window.tripGpsMarker = new maplibregl.Marker({ element: gpsEl, anchor: 'center' })
                    .setLngLat([window.myLng, window.myLat])
                    .addTo(tripMap);
            }

            if (markerPoints.length > 0) {
                if (coords.length > 1) {
                    if (activeIndex >= 0 && items[activeIndex] && items[activeIndex].transport_mode) {
                        const legModeKey = items[activeIndex].transport_mode.toLowerCase().replace(/[- ]/g, '_');
                        const foundVeh = VEHICLE_CATALOG.find(v => v.key === legModeKey || legModeKey.includes(v.key));
                        if (foundVeh) {
                            window.currentActiveVehicle = foundVeh;
                        }
                    }
                    const activeVehicle = window.currentActiveVehicle || VEHICLE_CATALOG[0];
                    let osrmProfile = activeVehicle.key === 'walking' ? 'walking' : 'driving';
                    let routeColor = '#00f2fe';
                    const isAlternative = (routeType === 'Alternative' || routeType === 'Alternate');

                    if (isAlternative) {
                        routeColor = '#f59e0b';
                    } else if (routeType === 'Scenic Route') {
                        routeColor = '#38bdf8';
                    }

                    fetch(`https://router.project-osrm.org/route/v1/${osrmProfile}/${coords.join(';')}?overview=full&geometries=geojson`)
                        .then(r => r.json())
                        .then(routeData => {
                            if (!document.getElementById('trip-info-distance')) return; // Page was unmounted

                            if (routeData.code === 'Ok' && routeData.routes.length > 0) {
                                const route = routeData.routes[0];
                                const geojson = route.geometry;

                                let distanceKm = route.distance / 1000;
                                let baseDurationMin = route.duration / 60;

                                // Realistic, stable speed calculation based on selected vehicle
                                const speed = activeVehicle.speedKmH || 35;
                                let durationMin = Math.max(baseDurationMin * 1.25, (distanceKm / speed) * 60);

                                if (routeType === 'Scenic Route') {
                                    durationMin *= 1.3; distanceKm *= 1.25;
                                } else if (isAlternative) {
                                    durationMin *= 1.15; distanceKm *= 1.1;
                                }

                                window.setTxt('trip-info-distance', distanceKm.toFixed(1) + ' km');
                                window.setTxt('trip-info-time', Math.round(durationMin) + ' mins');

                                // Floating ETA Box on map with royal blue gradient and dynamic vehicle icon
                                if (window.etaMarker) window.etaMarker.remove();
                                if (coords.length > 1) {
                                    const startLngLat = coords[0].split(',').map(Number);
                                    const nextLngLat = coords[1].split(',').map(Number);
                                    const midLng = (startLngLat[0] + nextLngLat[0]) / 2;
                                    const midLat = (startLngLat[1] + nextLngLat[1]) / 2;

                                    let leg = route.legs ? route.legs[0] : null;
                                    let legDistKm = leg ? (leg.distance / 1000) : distanceKm;
                                    let legBaseDurMin = leg ? (leg.duration / 60) : baseDurationMin;
                                    let legDurMin = Math.max(legBaseDurMin * 1.25, (legDistKm / speed) * 60);

                                    if (routeType === 'Scenic Route') { legDurMin *= 1.3; legDistKm *= 1.25; }
                                    else if (isAlternative) { legDurMin *= 1.15; legDistKm *= 1.1; }

                                    const etaEl = document.createElement('div');
                                    etaEl.innerHTML = `
                                <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: none !important; outline: none !important; padding: 7px 13px; border-radius: 16px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.45); display: flex; align-items: center; gap: 8px; color: #ffffff; white-space: nowrap; pointer-events: none; transform: translateY(-4px);">
                                    <div style="width: 28px; height: 28px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 13px; flex-shrink: 0;">
                                        <i class="fa-solid ${activeVehicle.icon}"></i>
                                    </div>
                                    <div style="display: flex; flex-direction: column; line-height: 1.15;">
                                        <div style="display: flex; align-items: baseline; gap: 3px;">
                                            <span style="font-size: 14px; font-weight: 800; color: #ffffff;">${Math.round(legDurMin)}</span>
                                            <span style="font-size: 10px; font-weight: 700; color: #67e8f9;">min</span>
                                        </div>
                                        <span style="font-size: 10px; font-weight: 600; color: rgba(255, 255, 255, 0.85);">${legDistKm < 1 ? Math.round(legDistKm * 1000) + ' m' : legDistKm.toFixed(1) + ' km'}</span>
                                    </div>
                                </div>
                            `;

                                    window.etaMarker = new maplibregl.Marker({ element: etaEl, anchor: 'center' })
                                        .setLngLat([midLng, midLat])
                                        .addTo(tripMap);
                                }

                                // Safely update or add MapLibre sources/layers
                                if (tripMap.getSource('route')) {
                                    tripMap.getSource('route').setData(geojson);
                                    tripMap.getSource('route-bg').setData(geojson);
                                    tripMap.setPaintProperty('route-line', 'line-color', routeColor);
                                } else {
                                    tripMap.addSource('route', { 'type': 'geojson', 'data': geojson });
                                    tripMap.addLayer({
                                        'id': 'route-line',
                                        'type': 'line',
                                        'source': 'route',
                                        'layout': { 'line-join': 'round', 'line-cap': 'round' },
                                        'paint': { 'line-color': routeColor, 'line-width': 5, 'line-opacity': 0.85 }
                                    });

                                    tripMap.addSource('route-bg', { 'type': 'geojson', 'data': geojson });
                                    tripMap.addLayer({
                                        'id': 'route-line-bg',
                                        'type': 'line',
                                        'source': 'route-bg',
                                        'layout': { 'line-join': 'round', 'line-cap': 'round' },
                                        'paint': { 'line-color': '#1e3a8a', 'line-width': 8, 'line-opacity': 0.6 }
                                    }, 'route-line');
                                }
                            }
                        }).catch(e => {
                            console.warn("Trip online routing failed, using geodesic route fallback:", e);
                            try {
                                const directCoords = coords.map(c => c.split(',').map(Number));
                                if (directCoords.length >= 2) {
                                    const geojson = {
                                        'type': 'Feature',
                                        'geometry': {
                                            'type': 'LineString',
                                            'coordinates': directCoords
                                        }
                                    };

                                    let totalDistKm = 0;
                                    function getLocalHaversineKm(lat1, lon1, lat2, lon2) {
                                        const R = 6371;
                                        const dLat = (lat2 - lat1) * Math.PI / 180;
                                        const dLon = (lon2 - lon1) * Math.PI / 180;
                                        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                                            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                                            Math.sin(dLon / 2) * Math.sin(dLon / 2);
                                        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                                    }

                                    for (let i = 0; i < directCoords.length - 1; i++) {
                                        totalDistKm += getLocalHaversineKm(directCoords[i][1], directCoords[i][0], directCoords[i + 1][1], directCoords[i + 1][0]);
                                    }

                                    const speed = activeVehicle.speedKmH || 35;
                                    const durationMin = Math.max(5, Math.round((totalDistKm / speed) * 60));

                                    window.setTxt('trip-info-distance', totalDistKm.toFixed(1) + ' km (direct)');
                                    window.setTxt('trip-info-time', durationMin + ' mins');

                                    if (tripMap.getSource('route')) {
                                        tripMap.getSource('route').setData(geojson);
                                        tripMap.setPaintProperty('route-line', 'line-color', '#f59e0b');
                                        tripMap.setPaintProperty('route-line', 'line-dasharray', [2, 2]);
                                    } else {
                                        tripMap.addSource('route', { 'type': 'geojson', 'data': geojson });
                                        tripMap.addLayer({
                                            'id': 'route-line',
                                            'type': 'line',
                                            'source': 'route',
                                            'layout': { 'line-join': 'round', 'line-cap': 'round' },
                                            'paint': { 'line-color': '#f59e0b', 'line-width': 4, 'line-dasharray': [2, 2], 'line-opacity': 0.85 }
                                        });
                                    }
                                    return;
                                }
                            } catch (fallbackErr) {
                                console.error('Geodesic route fallback failed:', fallbackErr);
                            }
                            const distEl = document.getElementById('trip-info-distance');
                            const timeEl = document.getElementById('trip-info-time');
                            if (distEl) distEl.textContent = "N/A";
                            if (timeEl) timeEl.textContent = "N/A";
                        });
                } else {
                    const distEl = document.getElementById('trip-info-distance');
                    const timeEl = document.getElementById('trip-info-time');
                    if (distEl) distEl.textContent = "N/A";
                    if (timeEl) timeEl.textContent = "N/A";
                }
            }
        }

        // --- MULTI-PHOTO PROOF & INDEPENDENT LOCATION CHECK-IN HANDLERS ---
        window.selectedProofPhotos = [];
        window._currentProofItemId = null;
        window._currentProofSpotName = null;

        window.openProofUploadModal = function (itemId, spotName = '') {
            window._currentProofItemId = itemId;
            window._currentProofSpotName = spotName;
            window.selectedProofPhotos = [];

            const titleEl = document.getElementById('proof-spot-title');
            if (titleEl) {
                titleEl.innerHTML = `<i class="fa-solid fa-camera" style="color:#00f2fe; font-size:17px;"></i> ${spotName || 'Upload Photo Proof'}`;
            }

            const input = document.getElementById('proof-upload-input');
            if (input) input.value = '';

            window.renderProofThumbnailsGrid();

            const modal = document.getElementById('proof-upload-modal');
            if (modal) modal.style.display = 'flex';
        };

        window.closeProofUploadModal = function () {
            const modal = document.getElementById('proof-upload-modal');
            if (modal) modal.style.display = 'none';
            window.selectedProofPhotos = [];
            window._currentProofItemId = null;
            window._currentProofSpotName = null;

            const btn = document.getElementById('btn-submit-proof-photos');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit Photo Proof';
                btn.disabled = false;
            }
        };

        // Aliases for backwards compatibility
        window.triggerMapCheckinModal = function (id) {
            window.openProofUploadModal(id || window.currentCheckinItemId);
        };
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
                submitBtn.innerHTML = `<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit Photo Proof ${total > 0 ? `(${total})` : ''}`;
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
                        <div style="position:relative; aspect-ratio:1/1; border-radius:14px; overflow:hidden; background:#000; box-shadow:0 4px 12px rgba(0,0,0,0.3); border:1.5px solid rgba(255,255,255,0.2);">
                            <img src="${item.dataUrl}" alt="Photo ${index + 1}" style="width:100%; height:100%; object-fit:cover; display:block;">
                            <span style="position:absolute; bottom:4px; left:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:9px; font-weight:800; padding:2px 6px; border-radius:6px;">
                                ${index + 1}
                            </span>
                            <button type="button" onclick="event.stopPropagation(); window.removeProofPhotoAtIndex(${index})" title="Remove Photo"
                                style="position:absolute; top:4px; right:4px; width:26px; height:26px; border-radius:50%; background:rgba(239,68,68,0.9); color:#fff; border:none; outline:none; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.4);">
                                <i class="fa-solid fa-xmark" style="font-size:12px;"></i>
                            </button>
                        </div>
                    `;
                });

                if (total < 5) {
                    html += `
                        <div onclick="window.addProofPhotoFromSource('gallery')"
                            style="aspect-ratio:1/1; border:2px dashed rgba(255,255,255,0.4); border-radius:14px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; background:rgba(255,255,255,0.08); transition:background 0.2s ease;">
                            <i class="fa-solid fa-plus" style="font-size:20px; color:#ffffff; opacity:0.8; margin-bottom:4px;"></i>
                            <span style="font-size:10px; font-weight:800; color:#ffffff; opacity:0.8;">Add More</span>
                        </div>
                    `;
                }

                grid.innerHTML = html;
            }
        };

        window.removeProofPhotoAtIndex = function (index) {
            window.selectedProofPhotos.splice(index, 1);
            window.renderProofThumbnailsGrid();
        };

        window.addProofPhotoFromSource = async function (mode) {
            const remainingSlots = 5 - window.selectedProofPhotos.length;
            if (remainingSlots <= 0) {
                if (typeof showToast === 'function') showToast('Maximum 5 photos reached.');
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
                        source: mode === 'camera' ? 'CAMERA' : 'PHOTOS'
                    });

                    if (image && image.dataUrl) {
                        const res = await fetch(image.dataUrl);
                        const blob = await res.blob();
                        const rawFile = new File([blob], `proof_${Date.now()}.jpg`, { type: blob.type || 'image/jpeg' });
                        const compressed = await window.compressImageFile(rawFile, 1280, 0.8);

                        window.selectedProofPhotos.push({
                            file: compressed,
                            dataUrl: image.dataUrl
                        });
                        window.renderProofThumbnailsGrid();
                    }
                } catch (err) {
                    console.warn('Native camera cancelled or failed:', err);
                }
            } else {
                const input = document.getElementById('proof-upload-input');
                if (!input) return;
                if (mode === 'camera') {
                    input.setAttribute('capture', 'environment');
                } else {
                    input.removeAttribute('capture');
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

            if (window.currentTrip && Array.isArray(window.currentTrip.items)) {
                window.currentTrip.items = window.currentTrip.items.map(it => {
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
                window.currentTripItems = window.currentTrip.items;
                const activeIndex = window.currentTripItems.findIndex(i => !i.is_visited && i.proof_status !== 'approved');
                if (typeof renderConveyorCards === 'function') {
                    renderConveyorCards(window.currentTripItems, activeIndex >= 0 ? activeIndex : 0);
                }
            }

            // Sync with local storage
            ['intan_elyu_saved_trips', 'intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips'].forEach(k => {
                try {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        const updateList = (list) => {
                            if (!Array.isArray(list)) return list;
                            return list.map(trip => {
                                if (!trip || !Array.isArray(trip.items)) return trip;
                                return {
                                    ...trip,
                                    items: trip.items.map(it => {
                                        if (String(it.id) === targetIdStr) {
                                            return { ...it, proof_image: urls[0], proof_images: urls, proof_status: proofStatus, rejection_reason: null };
                                        }
                                        return it;
                                    })
                                };
                            });
                        };
                        if (Array.isArray(parsed)) {
                            localStorage.setItem(k, JSON.stringify(updateList(parsed)));
                        } else if (parsed && Array.isArray(parsed.itineraries)) {
                            parsed.itineraries = updateList(parsed.itineraries);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        }
                    }
                } catch (e) { }
            });
        };

        window.applyLocationCheckinLocally = function (itemId) {
            if (!itemId) return;
            const targetIdStr = String(itemId);
            const nowIso = new Date().toISOString();

            if (window.currentTrip && Array.isArray(window.currentTrip.items)) {
                window.currentTrip.items = window.currentTrip.items.map(it => {
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
                window.currentTripItems = window.currentTrip.items;
                const activeIndex = window.currentTripItems.findIndex(i => !i.is_visited && i.proof_status !== 'approved');
                if (typeof renderConveyorCards === 'function') {
                    renderConveyorCards(window.currentTripItems, activeIndex >= 0 ? activeIndex : 0);
                }
            }
        };

        window.submitProofPhotos = async function () {
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

            // Optimistically update memory and DOM right now!
            window.applyPhotoProofUpdateLocally(itemId, previewUrls, 'pending');

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
                        spotName: spotName,
                        lat: window.myLat || 16.6159,
                        lng: window.myLng || 120.3209,
                        image: firstFile,
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
                        spotName: spotName,
                        lat: window.myLat || 16.6159,
                        lng: window.myLng || 120.3209,
                        image: firstFile,
                        capturedAt: new Date().toISOString()
                    });
                    window.closeProofUploadModal();
                    if (typeof showToast === 'function') {
                        showToast('📍 Saved Offline! Photo proof saved on device. Auto-syncs when online.', 'success');
                    }
                } else {
                    if (typeof showToast === 'function') showToast(reason || 'Upload failed. Please try again.');
                    if (btn) {
                        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit Photo Proof';
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
                        showToast(data.message || 'Photo proof submitted! Pending review before completion.', 'success');
                    }
                    if (window.confetti) {
                        try { window.confetti({ particleCount: 80, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                    }
                } else {
                    console.warn('Server proof upload error:', data);
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

            const doSubmitCheckin = async (lat, lng) => {
                window.applyLocationCheckinLocally(itemId);

                if (isOfflineItem) {
                    if (window.OfflineCheckinManager) {
                        await window.OfflineCheckinManager.queueOfflineCheckin({
                            type: 'itinerary',
                            itemId: itemId,
                            spotName: spotName || 'Destination',
                            lat: lat,
                            lng: lng,
                            capturedAt: new Date().toISOString()
                        });
                    }
                    if (typeof showToast === 'function') {
                        showToast(`📍 Checked in offline at ${spotName}! Auto-syncs when online.`, 'success');
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
                            showToast(data.message || `📍 Location verified at ${spotName}! Photo proof is required to earn +50 Points.`, 'success');
                        }
                        if (window.confetti) {
                            try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Check-in failed. Please ensure you are at the destination.', 'error');
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
                        await window.OfflineCheckinManager.queueOfflineCheckin({
                            type: 'itinerary',
                            itemId: itemId,
                            spotName: spotName || 'Destination',
                            lat: lat,
                            lng: lng,
                            capturedAt: new Date().toISOString()
                        });
                        if (typeof showToast === 'function') {
                            showToast('📍 Saved Offline! Check-in queued on device. Auto-syncs when online.', 'info');
                        }
                    } else {
                        if (typeof showToast === 'function') showToast('Network error during check-in.');
                    }
                    if (btnEl) {
                        btnEl.innerHTML = originalHtml;
                        btnEl.disabled = false;
                    }
                }
            };

            const getDistance = (lat1, lon1, lat2, lon2) => {
                const R = 6371e3;
                const φ1 = lat1 * Math.PI / 180;
                const φ2 = lat2 * Math.PI / 180;
                const Δφ = (lat2 - lat1) * Math.PI / 180;
                const Δλ = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            };

            const checkAndProceed = (lat, lng) => {
                if (spotLat != null && spotLng != null && !isNaN(spotLat) && !isNaN(spotLng)) {
                    const dist = getDistance(lat, lng, parseFloat(spotLat), parseFloat(spotLng));
                    if (dist > 300) {
                        const distM = Math.round(dist);
                        const distKm = (dist / 1000).toFixed(1);
                        const displayDist = dist >= 1000 ? `${distKm} km` : `${distM} meters`;
                        if (typeof showToast === 'function') {
                            showToast(`⚠️ You are ${displayDist} away from ${spotName}. Must be within 300m to check in.`, 'warning');
                        }
                        if (btnEl) {
                            btnEl.innerHTML = originalHtml;
                            btnEl.disabled = false;
                        }
                        return;
                    }
                }
                doSubmitCheckin(lat, lng);
            };

            if (window.myLat && window.myLng) {
                checkAndProceed(window.myLat, window.myLng);
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        window.myLat = pos.coords.latitude;
                        window.myLng = pos.coords.longitude;
                        checkAndProceed(pos.coords.latitude, pos.coords.longitude);
                    },
                    (err) => {
                        console.warn('Geolocation error:', err);
                        const fallbackLat = 16.6159;
                        const fallbackLng = 120.3209;
                        checkAndProceed(fallbackLat, fallbackLng);
                    },
                    { enableHighAccuracy: true, timeout: 6000 }
                );
            } else {
                doSubmitCheckin(16.6159, 120.3209);
            }
        };

        // Automatically refresh route and pins when offline check-ins or trips finish syncing
        window.addEventListener('checkins-synced', () => {
            if (typeof loadTripData === 'function') {
                loadTripData();
            }
        });
        window.addEventListener('tripsSynced', () => {
            if (typeof loadTripData === 'function') {
                loadTripData();
            }
        });

        // Real-time GPS Listener for live navigation mode on Saved Trips
        window._tripGpsTimeout = window._tripGpsTimeout || null;
        window._lastRouteLat = window._lastRouteLat || null;
        window._lastRouteLng = window._lastRouteLng || null;

        document.addEventListener('gpsUpdated', (e) => {
            window.myLat = e.detail.lat;
            window.myLng = e.detail.lng;

            if (window.tripGpsMarker) {
                window.tripGpsMarker.setLngLat([window.myLng, window.myLat]);
            }

            // Stabilize: Only recalculate route if user moved at least 30 meters
            const distMoved = calcCoordDistMeters(window._lastRouteLat, window._lastRouteLng, window.myLat, window.myLng);
            if (distMoved >= 30) {
                clearTimeout(_tripGpsTimeout);
                _tripGpsTimeout = setTimeout(() => {
                    window._lastRouteLat = window.myLat;
                    window._lastRouteLng = window.myLng;
                    if (window.currentTripItems) plotTrip(window.currentTripItems, window.currentRouteType);
                }, 1500);
            }
        });

        window.locateTripUser = async function () {
            const btn = document.getElementById('btn-trip-locate-me');
            const icon = btn ? btn.querySelector('i') || btn : null;
            const origClass = icon ? icon.className : '';
            if (icon) icon.className = 'fa-solid fa-spinner fa-spin';
            if (typeof showToast === 'function') showToast("Acquiring precise GPS location...");

            try {
                let loc = null;
                if (typeof window.requestPreciseLocation === 'function') {
                    loc = await window.requestPreciseLocation(true);
                } else if (typeof window.resolveUserLocation === 'function') {
                    loc = await window.resolveUserLocation(true);
                }

                if (loc && loc.lat && loc.lng && !isNaN(loc.lat) && !isNaN(loc.lng)) {
                    window.myLat = loc.lat;
                    window.myLng = loc.lng;
                    window.currentGPSLat = loc.lat;
                    window.currentGPSLng = loc.lng;

                    if (tripMap) {
                        tripMap.flyTo({ center: [parseFloat(loc.lng), parseFloat(loc.lat)], zoom: 15, duration: 1200 });
                    }
                    if (typeof showToast === 'function') {
                        showToast(loc.source === 'gps' || window.currentGPSSource === 'gps' ? "Centered on your precise GPS location 📍" : "Centered on your estimated location");
                    }
                } else {
                    throw new Error("Could not acquire coordinates");
                }
            } catch (e) {
                console.warn("Trip locate error:", e);
                if (typeof showToast === 'function') {
                    showToast("GPS blocked or unavailable. Select your town below.");
                }
                if (typeof window.openLocationPickerModal === 'function') {
                    window.openLocationPickerModal();
                }
            } finally {
                if (icon) icon.className = origClass || 'fa-solid fa-location-crosshairs';
            }
        };

        function updateTripOfflineStatus() {
            var pill = document.getElementById('trip-offline-badge');
            var isOffline = window.isAppOnline === false || (typeof navigator.onLine === 'boolean' ? !navigator.onLine : false);
            if (pill) {
                pill.style.setProperty('display', isOffline ? 'inline-flex' : 'none', 'important');
            }
        }
        window.addEventListener('online', updateTripOfflineStatus);
        window.addEventListener('offline', updateTripOfflineStatus);
        window.addEventListener('appOnline', updateTripOfflineStatus);
        window.addEventListener('appOffline', updateTripOfflineStatus);
        setTimeout(updateTripOfflineStatus, 100);

        initTripMap();
    })();
</script>