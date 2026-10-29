<?php
$pageTitle = "Trip Route";
$backRoute = "saved_trips";
$offlineMunicipalityGeoJson = json_decode((string) @file_get_contents(__DIR__ . '/../assets/la_union_municipalities.json'), true)
    ?: ['type' => 'FeatureCollection', 'features' => []];
require_once __DIR__ . '/../components/header.php';
include_once __DIR__ . '/../components/testimony_modal.php';
?>

<script>window.OFFLINE_MUNICIPALITIES_GEOJSON = <?= json_encode($offlineMunicipalityGeoJson) ?>;</script>

<div id="trip-map" style="width: 100%; height: 100vh; background: #0a0f1c;"></div>

<!-- Destination Conveyor Carousel Container -->
<div id="trip-conveyor-wrapper"
    style="position: absolute; bottom: 0; left: 0; right: 0; z-index: 1000; display: flex; flex-direction: column; align-items: stretch; pointer-events: none; width: 100%; box-sizing: border-box; overflow: hidden;">

    <!-- Offline Navigation Status Pill -->
    <div id="trip-offline-badge"
        style="display: none; align-self: center; background: #ffffff; border: 1.5px solid #fde68a; border-radius: 100px; padding: 5px 14px; color: #b45309; font-size: 11px; font-weight: 800; gap: 6px; align-items: center; pointer-events: auto; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.18); margin-bottom: 6px;">
        <i class="fa-solid fa-cloud-slash"></i> Offline Navigation &bull; Geodesic Mode
    </div>

    <!-- Online Mode: Unsynced Offline Data Available Banner with Sync Now Button -->
    <div id="trip-sync-banner"
        style="display: none; align-self: center; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #f59e0b; border-radius: 100px; padding: 4px 6px 4px 14px; color: #92400e; font-size: 11.5px; font-weight: 800; gap: 8px; align-items: center; pointer-events: auto; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25); max-width: calc(100vw - 32px); box-sizing: border-box; margin-bottom: 6px;">
        <div style="display:flex; align-items:center; gap:6px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
            <i class="fa-solid fa-cloud-arrow-up" style="color:#d97706; font-size:12px;"></i>
            <span id="trip-sync-banner-text">Offline check-in saved on device</span>
        </div>
        <button type="button" id="trip-sync-banner-btn" onclick="event.stopPropagation(); window.syncAllOfflineTripData(this);"
            style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; outline: none; color: #ffffff; padding: 5px 12px; border-radius: 100px; font-size: 11px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(217, 119, 6, 0.35); flex-shrink: 0;">
            <i class="fa-solid fa-rotate"></i> Sync Now
        </button>
    </div>

    <!-- White Background Card Container behind Tourist Card -->
    <div id="trip-card-container"
        style="background: #ffffff !important; border-top-left-radius: 24px; border-top-right-radius: 24px; box-shadow: 0 -8px 30px rgba(15, 23, 42, 0.16) !important; overflow: hidden; pointer-events: auto; width: 100%; box-sizing: border-box; display: flex; flex-direction: column;">

        <!-- Royal Blue Header above the Tourist Card -->
        <div id="trip-card-header"
            style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 11px 18px; display: flex; justify-content: space-between; align-items: center; border-top-left-radius: 24px; border-top-right-radius: 24px; flex-shrink: 0; box-sizing: border-box;">
            <div style="display:flex; align-items:center; gap:7px; color:#ffffff; font-weight:800; font-size:13px; min-width:0;">
                <i class="fa-solid fa-map-location-dot" style="color:#38bdf8; font-size:13px; flex-shrink:0;"></i>
                <span id="trip-info-name" style="color:#ffffff; font-weight:800; font-size:13px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">Trip Itinerary</span>
            </div>
            <div id="trip-info-pax-badge"
                style="display:flex; align-items:center; gap:6px; color:#ffffff; font-weight:700; font-size:11.5px; background:rgba(255,255,255,0.18); padding:4px 11px; border-radius:100px; flex-shrink:0;">
                <i id="trip-info-pax-icon" class="fa-solid fa-user" style="color:#ffffff; font-size:10.5px;"></i>
                <span id="trip-info-pax-text">Solo</span>
            </div>
            <!-- Vehicle and distance/time kept in DOM with display:none to guarantee JS compatibility -->
            <div id="trip-info-vehicle-badge" style="display:none !important;">
                <i id="trip-info-vehicle-icon" class="fa-solid fa-car"></i>
                <span id="trip-info-vehicle-name">Own Car</span>
            </div>
            <span id="trip-info-distance" style="display:none !important;">-- km</span>
            <span id="trip-info-time" style="display:none !important;">-- mins</span>
        </div>

        <!-- Conveyor Cards Carousel Scroll Container -->
        <div id="conveyor-cards-scroll"
            style="display: flex; justify-content: flex-start; align-items: stretch; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-padding: 0 16px; padding: 12px 16px max(env(safe-area-inset-bottom), 14px) 16px; pointer-events: auto; scroll-behavior: smooth; width: 100%; box-sizing: border-box; background: #ffffff !important;"
            class="hide-scrollbar">
            <!-- Injected via JS -->
        </div>
    </div>
</div>

<!-- Dedicated Multi-Photo Proof Upload Modal -->
<div id="proof-upload-modal" onclick="if(event.target===this) window.closeProofUploadModal();"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.72); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:99999; justify-content:center; align-items:center; padding:16px; box-sizing:border-box;">
    <div
        style="background:#ffffff; border:none; outline:none; border-radius:26px; width:100%; max-width:420px; box-shadow:0 20px 50px rgba(0,0,0,0.35); text-align:center; max-height:90vh; overflow:hidden; display:flex; flex-direction:column; box-sizing:border-box; animation:popInProofModal 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        
        <!-- Header Banner -->
        <div style="flex-shrink:0; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); padding:18px 20px; display:flex; justify-content:space-between; align-items:center; border-top-left-radius:26px; border-top-right-radius:26px; box-sizing:border-box;">
            <div style="text-align:left;">
                <h3 id="proof-spot-title" style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-camera" style="color:#38bdf8; font-size:17px;"></i> Upload Photo Proof
                </h3>
                <span id="proof-spot-subtitle" style="font-size:12px; color:rgba(255,255,255,0.85); font-weight:600; display:block; margin-top:3px;">Select up to 5 photos</span>
            </div>
            <button type="button" onclick="window.closeProofUploadModal()"
                style="background:rgba(255,255,255,0.18); border:none; outline:none; color:#ffffff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:all 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>

        <!-- Middle Scrollable Area (White) -->
        <div style="background:#ffffff !important; padding:20px; flex:1; overflow-y:auto; text-align:left; box-sizing:border-box;">
            <p style="font-size:13px; color:#475569; margin:0 0 16px 0; line-height:1.5; font-weight:500;">
                Take or select selfies and photos at this destination to verify your visit.
            </p>

            <input type="hidden" id="proof-upload-item-id">
            <input type="file" id="proof-upload-input" accept="image/*" multiple style="display:none;" onchange="window.handleProofPhotosSelected(this)">

            <!-- Add Photo Action Buttons -->
            <div style="display:flex; gap:10px; margin-bottom:16px;">
                <button type="button" onclick="window.addProofPhotoFromSource('camera')"
                    style="flex:1; padding:12px 10px; background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border:none; outline:none; border-radius:14px; color:#ffffff; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; box-shadow:0 3px 10px rgba(2, 132, 199, 0.28); transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-camera" style="font-size:15px;"></i> Take Photo
                </button>
                <button type="button" onclick="window.addProofPhotoFromSource('gallery')"
                    style="flex:1; padding:12px 10px; background:#f1f5f9; border:1.5px solid #e2e8f0; outline:none; border-radius:14px; color:#1e3a8a; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-images" style="color:#0284c7; font-size:15px;"></i> Add from Gallery
                </button>
            </div>

            <!-- Selected Photos Area -->
            <div style="margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <label style="font-size:11.5px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.5px;">
                        Selected Photos
                    </label>
                    <span id="proof-count-badge" style="font-size:11.5px; font-weight:800; color:#0284c7; background:#e0f2fe; padding:3px 10px; border-radius:100px;">0 / 5 photos</span>
                </div>

                <!-- Empty State Prompt -->
                <div id="proof-empty-prompt" onclick="window.addProofPhotoFromSource('gallery')"
                    style="border:2px dashed #94a3b8; border-radius:16px; padding:24px 16px; text-align:center; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px; color:#0284c7; margin-bottom:6px; display:block;"></i>
                    <p style="margin:0; font-size:13px; font-weight:800; color:#1e293b;">Tap to select photos</p>
                    <p style="margin:4px 0 0 0; font-size:11.5px; color:#64748b; font-weight:500;">You can choose multiple photos</p>
                </div>

                <!-- Thumbnails Grid -->
                <div id="proof-thumbnails-grid" style="display:none; grid-template-columns:repeat(3, 1fr); gap:10px;"></div>
            </div>
        </div>

        <!-- Footer Banner (Cancel on Left, Submit on Right) -->
        <div style="flex-shrink:0; background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 20px; border-bottom-left-radius:26px; border-bottom-right-radius:26px; box-sizing:border-box;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:center;">
                <button type="button"
                    style="width:100%; padding:13px 16px; border-radius:14px; border:1.5px solid #cbd5e1; outline:none; background:#ffffff; color:#475569; font-size:14px; font-weight:800; cursor:pointer; transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'"
                    onclick="window.closeProofUploadModal()">Cancel</button>
                <button type="button" id="btn-submit-proof-photos" class="btn-primary"
                    style="width:100%; padding:13px 16px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; cursor:pointer; box-shadow:0 4px 14px rgba(16, 185, 129, 0.35); transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'"
                    onclick="window.submitProofPhotos()">
                    <i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes popInProofModal {
    0% { opacity: 0; transform: scale(0.92); }
    100% { opacity: 1; transform: scale(1); }
}
</style>




<script>
    (function () {
        var backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
        var tripMap;

        // A cached SPA view can be mounted again while the previous map still has
        // asynchronous routing callbacks in flight. Dispose that instance first.
        if (window._tripMapInstance) {
            try { window._tripMapInstance.remove(); } catch (e) { }
            window._tripMapInstance = null;
        }

        function isTripMapActive(map) {
            try {
                return Boolean(
                    map &&
                    map === tripMap &&
                    map === window._tripMapInstance &&
                    document.getElementById('trip-map') &&
                    map.getContainer &&
                    map.getContainer().isConnected &&
                    map.getStyle &&
                    map.getStyle()
                );
            } catch (e) {
                return false;
            }
        }

        const rememberedGps = typeof window.getRememberedGpsLocation === 'function' ? window.getRememberedGpsLocation() : null;
        window.myLat = window.myLat || window.currentGPSLat || (rememberedGps && rememberedGps.lat) || null;
        window.myLng = window.myLng || window.currentGPSLng || (rememberedGps && rememberedGps.lng) || null;

        if (typeof window.requestPreciseLocation === 'function') {
            window.requestPreciseLocation(false).then(loc => {
                if (loc && loc.lat && loc.lng) {
                    window.myLat = loc.lat;
                    window.myLng = loc.lng;
                    document.dispatchEvent(new CustomEvent('gpsUpdated', {
                        detail: { lat: loc.lat, lng: loc.lng, accuracy: loc.accuracy, source: loc.source || 'gps' }
                    }));
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
            if (typeof maplibregl === 'undefined') {
                console.warn('MapLibre GL is not loaded yet. Retrying...');
                setTimeout(initTripMap, 200);
                return;
            }
            if (!document.getElementById('trip-map')) {
                return;
            }

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
                    },
                    "offline-municipalities": {
                        "type": "geojson",
                        "data": window.OFFLINE_MUNICIPALITIES_GEOJSON
                    }
                },
                "layers": [
                    {
                        "id": "background",
                        "type": "background",
                        "paint": { "background-color": "#eef2f6", "background-opacity": 0.78 }
                    },
                    {
                        "id": "base-map",
                        "type": "raster",
                        "source": "osm",
                        "layout": { "visibility": "visible" }
                    },
                    {
                        "id": "offline-municipality-fill",
                        "type": "fill",
                        "source": "offline-municipalities",
                        "paint": { "fill-color": "#b8dcec", "fill-opacity": 0.38 }
                    },
                    {
                        "id": "offline-municipality-border",
                        "type": "line",
                        "source": "offline-municipalities",
                        "paint": { "line-color": "#3978a8", "line-width": 0, "line-opacity": 0 }
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
            window._tripMapInstance = tripMap;

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

        // Immediate frame-0 vehicle and pax initialization so there is zero flicker
        (function initImmediateVehicleAndPax() {
            const urlParams = new URLSearchParams(window.location.search);
            const tripId = urlParams.get('trip_id') || sessionStorage.getItem('active_trip_id') || window.currentTripId;
            if (tripId) {
                const v = resolveTripVehicle(null, tripId);
                applyVehicleToUI(v);

                try {
                    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                    const tokenKey = token ? token.substring(0, 10) : 'default';
                    const raw = localStorage.getItem('saved_trips_' + tokenKey);
                    let cachedTrip = null;
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        const list = Array.isArray(parsed) ? parsed : (parsed.itineraries || parsed.data || []);
                        cachedTrip = list.find(t => t.id == tripId || t.local_id == tripId);
                    }
                    if (!cachedTrip && window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function') {
                        cachedTrip = window.OfflineTripManager.getQueuedTrips().find(t => t.id == tripId || t.local_id == tripId);
                    }
                    if (cachedTrip) {
                        const paxTextEl = document.getElementById('trip-info-pax-text');
                        const paxIconEl = document.getElementById('trip-info-pax-icon');
                        if (paxTextEl) {
                            const isGroup = cachedTrip.trip_type === 'group' || (cachedTrip.total_pax && cachedTrip.total_pax > 1);
                            const totalPax = parseInt(cachedTrip.total_pax || 1, 10);
                            const males = parseInt(cachedTrip.companion_males || 0, 10);
                            const females = parseInt(cachedTrip.companion_females || 0, 10);
                            if (isGroup) {
                                paxTextEl.textContent = `${totalPax} Pax${(males > 0 || females > 0) ? ` (${males}M, ${females}F)` : ''}`;
                                if (paxIconEl) paxIconEl.className = 'fa-solid fa-users';
                            } else {
                                paxTextEl.textContent = 'Solo';
                                if (paxIconEl) paxIconEl.className = 'fa-solid fa-user';
                            }
                        }
                    }
                } catch (e) { }
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

        const TRIP_MAP_CHECKIN_RADIUS_METERS = 70;
        const TRIP_MAP_MAX_GPS_ACCURACY_METERS = 200;

        window.updateTripMapCheckinAvailability = function (lat, lng, accuracy = null) {
            const userLat = Number(lat);
            const userLng = Number(lng);
            const gpsAccuracy = Number(accuracy);
            const hasReliableFix = Number.isFinite(userLat) && Number.isFinite(userLng) &&
                (!Number.isFinite(gpsAccuracy) || gpsAccuracy <= TRIP_MAP_MAX_GPS_ACCURACY_METERS);

            if (hasReliableFix) {
                window._tripLastGpsFix = { lat: userLat, lng: userLng, accuracy: gpsAccuracy };
            }

            document.querySelectorAll('.trip-map-checkin-btn').forEach(button => {
                const destinationLat = Number(button.dataset.destLat);
                const destinationLng = Number(button.dataset.destLng);
                const hasDestination = button.dataset.destLat !== '' && button.dataset.destLng !== '' &&
                    Number.isFinite(destinationLat) && Number.isFinite(destinationLng);
                const distance = hasReliableFix && hasDestination
                    ? calcCoordDistMeters(userLat, userLng, destinationLat, destinationLng)
                    : Infinity;
                const inRange = distance <= TRIP_MAP_CHECKIN_RADIUS_METERS;
                button.disabled = !inRange;
                button.classList.toggle('is-available', inRange);
                button.setAttribute('aria-disabled', String(!inRange));
                button.title = inRange
                    ? 'You are at this tourist site. Check in now.'
                    : 'Available when GPS detects you within 70 meters of this tourist site.';
            });
        };

        function hasSubmittedStopProof(item) {
            if (!item) return false;
            const hasCheckin = Boolean(item.is_location_verified || item.visited_at);
            const hasProof = Boolean((Array.isArray(item.proof_images) && item.proof_images.length > 0) || item.proof_image);
            return hasCheckin && hasProof && String(item.proof_status || 'pending').toLowerCase() !== 'rejected';
        }

        function renderConveyorCards(items, activeIndex) {
            const conveyorScroll = document.getElementById('conveyor-cards-scroll');
            if (!conveyorScroll) return;

            // If conveyor is already rendered for this trip and count matches, only update active status
            const existingCards = conveyorScroll.querySelectorAll('.conveyor-card');
            if (existingCards.length === items.length && window._conveyorRenderedTripId === window.currentTripId && window._allowConveyorFastRefresh === true) {
                items.forEach((item, idx) => {
                    const card = document.getElementById(`conveyor-card-${idx}`);
                    if (!card) return;
                    const isLocationVerified = Boolean(item.is_location_verified || item.visited_at);
                    const isPhotoApproved = (item.proof_status === 'approved');
                    const isVisited = Boolean(item.is_visited && isPhotoApproved && isLocationVerified);
                    const isPending = Boolean(item.proof_image && (item.proof_status === 'pending' || !item.proof_status));
                    const isRejected = (item.proof_status === 'rejected');
                    const spotIdVal = item.tourist_spot_id || item.destination_id || (item.destination ? item.destination.id : null);
                    const isQueued = Boolean((window.OfflineCheckinManager && window.OfflineCheckinManager.isItemQueued(item.id, spotIdVal, window.currentTripId)) || item.is_offline_queued);
                    const isActive = idx === activeIndex;
                    if (isActive) {
                        card.classList.add('active');
                        card.style.borderColor = '#0284c7';
                        card.style.boxShadow = '0 12px 32px rgba(2, 132, 199, 0.18), 0 4px 10px rgba(15, 23, 42, 0.06)';
                    } else {
                        card.classList.remove('active');
                        card.style.borderColor = '#e2e8f0';
                        card.style.boxShadow = '0 10px 28px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.04)';
                    }
                    const badgeEl = card.querySelector('.conveyor-status-badge');
                    if (badgeEl) {
                        if (isQueued) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Queued (Offline)';
                            badgeEl.style.background = '#fef3c7';
                            badgeEl.style.color = '#b45309';
                            badgeEl.style.borderColor = '#fde68a';
                        } else if (isVisited) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Visited';
                            badgeEl.style.background = '#dcfce7';
                            badgeEl.style.color = '#15803d';
                            badgeEl.style.borderColor = '#bbf7d0';
                        } else if (isPhotoApproved && !isLocationVerified) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-camera"></i> Photo Approved • GPS Req.';
                            badgeEl.style.background = '#e0f2fe';
                            badgeEl.style.color = '#0284c7';
                            badgeEl.style.borderColor = '#bae6fd';
                        } else if (isPending) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-clock"></i> Pending Review';
                            badgeEl.style.background = '#fef3c7';
                            badgeEl.style.color = '#b45309';
                            badgeEl.style.borderColor = '#fde68a';
                        } else if (isRejected) {
                            badgeEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Rejected';
                            badgeEl.style.background = '#fee2e2';
                            badgeEl.style.color = '#b91c1c';
                            badgeEl.style.borderColor = '#fecaca';
                        } else if (isActive) {
                            badgeEl.innerHTML = `Stop ${idx + 1} of ${items.length} • NEXT`;
                            badgeEl.style.background = '#eff6ff';
                            badgeEl.style.color = '#1d4ed8';
                            badgeEl.style.borderColor = '#bfdbfe';
                        } else {
                            badgeEl.innerHTML = `Stop ${idx + 1} of ${items.length}`;
                            badgeEl.style.background = '#f1f5f9';
                            badgeEl.style.color = '#475569';
                            badgeEl.style.borderColor = '#e2e8f0';
                        }
                    }
                });
                return;
            }

            window._conveyorRenderedTripId = window.currentTripId;
            const trip = window.currentTrip;
            const isGroupTrip = trip && (trip.trip_type === 'group' || (trip.total_pax && trip.total_pax > 1));
            const totalPaxCount = trip ? parseInt(trip.total_pax || 1, 10) : 1;
            const malesCount = trip ? parseInt(trip.companion_males || 0, 10) : 0;
            const femalesCount = trip ? parseInt(trip.companion_females || 0, 10) : 0;
            const paxLabel = isGroupTrip 
                ? `${totalPaxCount} Pax${(malesCount > 0 || femalesCount > 0) ? ` (${malesCount}M, ${femalesCount}F)` : ''}`
                : 'Solo';
            const paxIcon = isGroupTrip ? 'fa-users' : 'fa-user';

            let conveyorHtml = '';
            items.forEach((item, idx) => {
                const dest = item.destination;
                if (!dest) return;
                const lat = parseFloat(dest.lat || dest.latitude);
                const lng = parseFloat(dest.lng || dest.longitude);
                const isLocationVerified = Boolean(item.is_location_verified || item.visited_at);
                const proofStatus = item.proof_status || (item.is_visited ? 'approved' : null);
                const isPhotoApproved = (proofStatus === 'approved');
                const isVisited = Boolean(item.is_visited && isPhotoApproved && isLocationVerified);
                const isRejected = (item.proof_status === 'rejected');
                const spotIdVal = item.tourist_spot_id || item.destination_id || (item.destination ? item.destination.id : null);
                const queuedItem = window.OfflineCheckinManager ? window.OfflineCheckinManager.getQueuedItem(item.id, spotIdVal, window.currentTripId) : null;
                const isQueued = Boolean(queuedItem || item.is_offline_queued);
                const isActive = idx === activeIndex;
                const isStopUnlocked = isVisited || isQueued || hasSubmittedStopProof(item) || isActive;

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
                if (proofImages.length === 0 && isQueued && queuedItem && (queuedItem.previewUrl || queuedItem.image)) {
                    proofImages = [queuedItem.previewUrl || queuedItem.image];
                }
                const hasProof = Boolean(proofImages.length > 0);
                const isPendingApproval = Boolean(isLocationVerified && hasProof && (item.proof_status === 'pending' || !item.proof_status) && !isVisited);

                let badgeHtml = '';
                if (isQueued) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#fef3c7; border:1px solid #fde68a; color:#b45309; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-cloud-arrow-up"></i> Queued (Offline)</span>`;
                } else if (isVisited) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-circle-check"></i> Visited</span>`;
                } else if (isPhotoApproved && !isLocationVerified) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#e0f2fe; border:1px solid #bae6fd; color:#0284c7; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-camera"></i> Photo Approved • GPS Req.</span>`;
                } else if (isRejected) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>`;
                } else if (isPendingApproval) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#fef3c7; border:1px solid #fde68a; color:#b45309; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-clock"></i> Pending Review</span>`;
                } else if (isLocationVerified && !hasProof) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#e0f2fe; border:1px solid #bae6fd; color:#0284c7; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-location-dot"></i> Checked In</span>`;
                } else if (!isLocationVerified && hasProof) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#ede9fe; border:1px solid #ddd6fe; color:#6d28d9; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-camera"></i> Photo Ready</span>`;
                } else if (isActive) {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;">Stop ${idx + 1} of ${items.length} • NEXT</span>`;
                } else {
                    badgeHtml = `<span class="conveyor-status-badge" style="background:#f1f5f9; border:1px solid #e2e8f0; color:#475569; padding:4px 10px; border-radius:100px; font-size:10px; font-weight:700; flex-shrink:0;">Stop ${idx + 1} of ${items.length}</span>`;
                }

                const destEscaped = (dest && dest.name) ? dest.name.replace(/'/g, "\\'") : 'Destination';
                const resolvedAll = proofImages.map(u => (typeof window.resolveProofImageUrl === 'function' ? window.resolveProofImageUrl(u) : u));
                const jsonUrlsAttr = JSON.stringify(resolvedAll).replace(/"/g, '&quot;');
                const checkInButtonAttrs = `class="trip-map-checkin-btn" data-dest-lat="${Number.isFinite(lat) ? lat : ''}" data-dest-lng="${Number.isFinite(lng) ? lng : ''}" disabled aria-disabled="true"`;

                let proofThumbnailStrip = '';
                if (proofImages.length > 0) {
                    proofThumbnailStrip = `<div style="display:inline-flex; align-items:center; gap:4px; flex-shrink:0;">
                        ${proofImages.slice(0, 3).map((imgUrl, pIdx) => {
                            let pUrl = (typeof window.resolveProofImageUrl === 'function') ? window.resolveProofImageUrl(imgUrl) : imgUrl;
                            let fallbackUrl = (typeof window.getProofImageFallbackUrl === 'function') ? window.getProofImageFallbackUrl(imgUrl) : 'assets/img/no_image.svg';
                            return `<img src="${pUrl}" onerror="if(this.dataset.triedFallback!=='1'){this.dataset.triedFallback='1';this.src='${fallbackUrl}';}else{this.onerror=null;this.src='assets/img/no_image.svg';}" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal(${jsonUrlsAttr}, '${destEscaped}', '${isVisited ? 'Verified Visit Proof' : 'Uploaded Proof'}');" alt="Proof" title="Tap to view proof photo" style="width:30px; height:30px; border-radius:8px; object-fit:cover; border:1.5px solid #e2e8f0; flex-shrink:0; cursor:pointer;">`;
                        }).join('')}
                        ${proofImages.length > 3 ? `<span onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal(${jsonUrlsAttr}, '${destEscaped}', '${isVisited ? 'Verified Visit Proof' : 'Uploaded Proof'}');" style="background:#0f172a; color:#fff; font-size:8.5px; font-weight:800; padding:2px 5px; border-radius:6px; cursor:pointer;">+${proofImages.length - 3}</span>` : ''}
                    </div>`;
                }

                let queuedThumbnail = '';
                if (isQueued && queuedItem) {
                    const qSrc = queuedItem.previewUrl || queuedItem.image || '';
                    if (qSrc) {
                        queuedThumbnail = `<img src="${qSrc}" onerror="this.onerror=null; this.src='assets/img/no_image.svg';" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal('${qSrc}', '${destEscaped}', 'Offline Proof (Queued on Device)');" alt="Proof" title="Tap to view offline proof photo" style="width:30px; height:30px; border-radius:8px; object-fit:cover; border:1.5px solid #f59e0b !important; box-shadow:none !important; flex-shrink:0; cursor:pointer;">`;
                    } else {
                        queuedThumbnail = `<div style="width:30px; height:30px; border-radius:8px; background:rgba(245,158,11,0.12); border:1px dashed #f59e0b; display:flex; align-items:center; justify-content:center; color:#d97706; flex-shrink:0;"><i class="fa-solid fa-camera" style="font-size:11px;"></i></div>`;
                    }
                }

                let actionBtnHtml = '';
                if (isQueued) {
                    const isOnlineNow = navigator.onLine && window.isAppOnline !== false;
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${queuedThumbnail}
                        ${isOnlineNow && !(typeof window.isGuestUser === 'function' && window.isGuestUser()) ? `
                            <button type="button" onclick="event.stopPropagation(); window.syncAllOfflineTripData(this);" title="Tap to sync this check-in to cloud" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color:#fff; border:none; padding:4px 10px; border-radius:100px; font-size:10.5px; font-weight:800; display:inline-flex; align-items:center; gap:4px; cursor:pointer; box-shadow:0 2px 6px rgba(245,158,11,0.3); white-space:nowrap;">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Sync
                            </button>
                        ` : `
                            <span style="background:#fef3c7; border:1px solid #fde68a; color:#b45309; font-weight:800; font-size:10.5px; padding:3px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="fa-solid fa-cloud-arrow-up"></i> Queued</span>
                        `}
                    </div>`;
                } else if (isVisited) {
                    const sId = item.tourist_spot_id || (item.destination ? item.destination.id : '');
                    const isReviewed = window.userReviewedItemIds && window.userReviewedItemIds.has(Number(item.id));
                    const sClass = (dest && dest.classification_status) ? dest.classification_status : '';
                    const sMeta = (window.getRewardPointsForClassification) ? window.getRewardPointsForClassification(sClass) : { points: 25 };
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <span style="background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; font-weight:800; font-size:10px; padding:2px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Visited</span>
                            <button type="button" data-spot-id="${sId}" data-itinerary-item-id="${item.id}" data-spot-classification="${sClass}" onclick="event.stopPropagation(); window.openWriteTestimonyModal('${sId}', this, '${item.id}')" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; font-size:9.5px; font-weight:800; padding:2px 8px; border-radius:100px; cursor:pointer; width:fit-content;">
                                ${isReviewed ? '<i class="fa-solid fa-check"></i> Reviewed' : '<i class="fa-solid fa-pen"></i> Review'}
                            </button>
                        </div>
                    </div>`;
                } else if (isRejected) {
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            <span style="background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; font-weight:800; font-size:10px; padding:2px 6px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                            <button onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')" style="background:linear-gradient(135deg, #ef4444, #dc2626); color:#ffffff; border:none; padding:4px 8px; border-radius:100px; font-weight:800; font-size:9.5px; cursor:pointer; white-space:nowrap; box-shadow:0 2px 6px rgba(239,68,68,0.25);"><i class="fa-solid fa-camera" style="margin-right:2px;"></i> Re-upload Proof</button>
                        </div>
                    </div>`;
                } else if (isPendingApproval) {
                    actionBtnHtml = proofThumbnailStrip ? `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">${proofThumbnailStrip}</div>` : '';
                } else if (isLocationVerified && !hasProof) {
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        <span style="background:#e0f2fe; border:1px solid #bae6fd; color:#0284c7; font-weight:800; font-size:10px; padding:4px 8px; border-radius:100px; white-space:nowrap;"><i class="fa-solid fa-location-dot"></i> Checked In</span>
                        <button onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')" style="background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color:#ffffff; border:none; padding:6px 12px; border-radius:100px; font-weight:800; font-size:10.5px; box-shadow:0 2px 8px rgba(2,132,199,0.3); cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-camera" style="margin-right:3px;"></i> Upload Proof</button>
                    </div>`;
                } else if (isPhotoApproved && !isLocationVerified) {
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <button ${checkInButtonAttrs} onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${lat}, ${lng}, this)" style="background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color:#ffffff; border:none; padding:6px 12px; border-radius:100px; font-weight:800; font-size:10.5px; box-shadow:0 2px 8px rgba(2,132,199,0.3); cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-location-crosshairs" style="margin-right:3px;"></i> Check In</button>
                    </div>`;
                } else if (!isLocationVerified && hasProof) {
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        ${proofThumbnailStrip}
                        <button ${checkInButtonAttrs} onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${lat}, ${lng}, this)" style="background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color:#ffffff; border:none; padding:6px 12px; border-radius:100px; font-weight:800; font-size:10.5px; box-shadow:0 2px 8px rgba(2,132,199,0.3); cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-location-crosshairs" style="margin-right:3px;"></i> Check In</button>
                    </div>`;
                } else {
                    actionBtnHtml = `<div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        <button ${checkInButtonAttrs} onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${lat}, ${lng}, this)" style="background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color:#ffffff; border:none; padding:6px 11px; border-radius:100px; font-weight:800; font-size:10.5px; box-shadow:0 2px 8px rgba(2,132,199,0.3); cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-location-crosshairs" style="margin-right:3px;"></i> Check In</button>
                        <button onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')" style="background:#f1f5f9; color:#1e3a8a; border:1.5px solid #cbd5e1; padding:6px 11px; border-radius:100px; font-weight:800; font-size:10.5px; cursor:pointer; white-space:nowrap;"><i class="fa-solid fa-camera" style="margin-right:3px;"></i> Proof</button>
                    </div>`;
                }

                if (!isStopUnlocked) {
                    actionBtnHtml = `<span style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;font-weight:800;font-size:10px;padding:6px 10px;border-radius:100px;white-space:nowrap;"><i class="fa-solid fa-lock" style="margin-right:4px;"></i> Complete previous stop</span>`;
                }

                const classBadge = dest.classification_status ? `<span style="padding: 3px 8px; border-radius: 100px; font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#0284c7' : (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; border:none !important; outline:none !important; flex-shrink:0;">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : '';

                let legTransitHtml = '';
                if (item.transport_mode) {
                    const isSpotMaint = Boolean(
                        (dest && (dest.is_maintenance === 1 || dest.is_maintenance === true || dest.is_maintenance === '1' || (dest.status && String(dest.status).toLowerCase().includes('maint')))) ||
                        item.transport_mode === 'suspended'
                    );

                    if (isSpotMaint) {
                        legTransitHtml = `<div style="display:flex; align-items:center; gap:6px; margin-bottom:8px; flex-wrap:wrap;">
                        <span style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:3px 9px; border-radius:100px; font-size:10.5px; font-weight:800; display:inline-flex; align-items:center; gap:4px;">
                            <i class="fa-solid fa-ban" style="color:#ef4444; font-size:9.5px;"></i> Unavailable
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
                        const cStr = (cNum > 0) ? `&bull; ₱${cNum.toFixed(2)}` : '';

                        legTransitHtml = `<div style="display:flex; align-items:center; gap:6px; margin-bottom:8px; flex-wrap:wrap;">
                        <span style="background:#f8fafc; border:1px solid #e2e8f0; color:#334155; padding:3px 9px; border-radius:100px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                            <i class="fa-solid ${vIcon}" style="color:#0284c7; font-size:10px;"></i> ${vName} ${cStr}
                        </span>
                        ${(dest && (dest.accessible_by_private_vehicle === 0 || dest.accessible_by_private_vehicle === false || dest.accessible_by_private_vehicle === '0')) ? `<span style="background:#fef3c7; border:1px solid #fde68a; color:#92400e; padding:3px 8px; border-radius:100px; font-size:9.5px; font-weight:800; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i> Trailhead</span>` : ''}
                    </div>`;
                    }
                }

                conveyorHtml += `
            <div id="conveyor-card-${idx}" class="conveyor-card ${isActive ? 'active' : ''}" onclick="window.flyToConveyorSpot(${lng}, ${lat}, ${idx})" style="scroll-snap-align: center; flex: 0 0 calc(100vw - 48px); max-width: 330px; min-width: 260px; box-sizing: border-box; background: ${isActive ? '#f8fafc' : '#ffffff'} !important; border: 1.5px solid ${isActive ? '#0284c7' : '#e2e8f0'} !important; border-radius: 18px; padding: 14px 16px; box-shadow: none !important; overflow: hidden !important; cursor: pointer; transition: all 0.25s ease;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; gap:8px;">
                    ${badgeHtml}
                    ${classBadge}
                </div>
                <h4 style="margin:0 0 4px 0; font-size:16px; font-weight:800; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${dest.name}">${dest.name}</h4>
                <p style="margin:0 0 8px 0; font-size:12px; color:#64748b; font-weight:600; display:flex; align-items:center; gap:8px;">
                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fa-solid fa-location-dot" style="color:#0284c7; margin-right:4px;"></i>${dest.municipality || 'La Union'}</span>
                </p>
                ${legTransitHtml}
                <div style="display:flex; justify-content:space-between; align-items:center; gap:6px; min-width:0; padding-top:6px; border-top:1px solid #f1f5f9;">
                    <span style="font-size:11px; font-weight:700; color:#64748b; flex-shrink:0;"><i class="fa-solid fa-compass" style="color:#0284c7; margin-right:4px;"></i> Tap to view</span>
                    <div style="flex-shrink:0; max-width:68%;">${actionBtnHtml}</div>
                </div>
            </div>`;
            });

            conveyorScroll.innerHTML = conveyorHtml;
            const gpsFix = window._tripLastGpsFix || (window.deviceLastPos && window.deviceLastPos.coords ? {
                lat: window.deviceLastPos.coords.latitude,
                lng: window.deviceLastPos.coords.longitude,
                accuracy: window.deviceLastPos.coords.accuracy
            } : null);
            if (gpsFix) window.updateTripMapCheckinAvailability(gpsFix.lat, gpsFix.lng, gpsFix.accuracy);
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
            window.loadTripData = loadTripData;
            window._isStartingTrip = false;
            const urlParams = new URLSearchParams(window.location.search);
            const tripId = urlParams.get('trip_id') || sessionStorage.getItem('active_trip_id') || window.currentTripId;
            window.currentTripId = tripId;

            if (!tripId) {
                if (typeof showToast === 'function') showToast("No trip ID provided.");
                return;
            }

            const clearLoadingTripToast = () => {
                document.querySelectorAll('#toast-container .toast-card').forEach(toast => {
                    if ((toast.textContent || '').includes('Loading trip route')) toast.remove();
                });
                const toastContainer = document.getElementById('toast-container');
                if (toastContainer && toastContainer.children.length === 0) toastContainer.remove();
            };

            const applyTripToMap = (trip) => {
                clearLoadingTripToast();
                if (!document.getElementById('trip-map')) return; // Page was unmounted
                if (trip && (trip.status === 'completed' || (typeof window.isTripCompletedLocally === 'function' && window.isTripCompletedLocally(trip)))) {
                    window._savedTripsTab = 'history';
                    if (typeof window.navigateTo === 'function') window.navigateTo('saved_trips');
                    return;
                }

                // Deep-merge persistent verified item states AND offline queued checkins/photos into trip.items
                try {
                    const persistentStates = (typeof window.getPersistentItemStates === 'function') 
                        ? window.getPersistentItemStates() 
                        : JSON.parse(localStorage.getItem('intan_elyu_verified_item_states') || '{}');
                    const queuedCheckins = (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.getQueue === 'function')
                        ? window.OfflineCheckinManager.getQueue()
                        : JSON.parse(localStorage.getItem('intan_elyu_offline_checkins') || '[]');

                    if (trip && Array.isArray(trip.items)) {
                        trip.items = trip.items.map(it => {
                            if (!it) return it;
                            const itKey = String(it.id || '');
                            const sId = it.tourist_spot_id || it.destination_id || (it.destination ? it.destination.id : '');
                            const spotKey = sId ? String(sId) : '';

                            // Check offline queued check-in / photo
                            const queuedEntry = queuedCheckins.find(q => {
                                if (!q) return false;
                                if (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.matchesItem === 'function') {
                                    return window.OfflineCheckinManager.matchesItem(q, it.id, sId, trip.id || trip.local_id);
                                }
                                return Boolean(q.itemId && itKey && String(q.itemId) === itKey);
                            });

                            const override = persistentStates[itKey] || null;

                            const isLocVerified = Boolean(
                                it.is_location_verified || 
                                it.visited_at || 
                                (override && (override.is_location_verified || override.visited_at)) || 
                                queuedEntry
                            );

                            const visitedAt = it.visited_at || (override && override.visited_at) || (queuedEntry && queuedEntry.capturedAt) || null;

                            let proofImgs = [];
                            if (Array.isArray(it.proof_images) && it.proof_images.length > 0) {
                                proofImgs = it.proof_images;
                            } else if (it.proof_image) {
                                proofImgs = [it.proof_image];
                            }
                            if (proofImgs.length === 0 && override) {
                                if (Array.isArray(override.proof_images) && override.proof_images.length > 0) {
                                    proofImgs = override.proof_images;
                                } else if (override.proof_image) {
                                    proofImgs = [override.proof_image];
                                }
                            }
                            if (proofImgs.length === 0 && queuedEntry && (queuedEntry.previewUrl || queuedEntry.image)) {
                                proofImgs = [queuedEntry.previewUrl || queuedEntry.image];
                            }

                            const isServerApproved = Boolean(it.is_visited || it.proof_status === 'approved' || (override && (override.proof_status === 'approved' || override.is_visited)));
                            const isServerRejected = (it.proof_status === 'rejected' || (override && override.proof_status === 'rejected'));

                            let pStatus;
                            if (isServerApproved) {
                                pStatus = 'approved';
                            } else if (isServerRejected) {
                                pStatus = 'rejected';
                            } else if (it.proof_status) {
                                pStatus = it.proof_status;
                            } else if (override && override.proof_status) {
                                pStatus = override.proof_status;
                            } else if (queuedEntry) {
                                pStatus = 'pending';
                            } else {
                                pStatus = (proofImgs.length > 0 ? 'pending' : null);
                            }

                            return {
                                ...it,
                                is_visited: isServerApproved ? true : Boolean(it.is_visited),
                                is_location_verified: Boolean(isServerApproved || isLocVerified),
                                visited_at: visitedAt,
                                proof_image: proofImgs[0] || null,
                                proof_images: proofImgs,
                                proof_status: pStatus,
                                is_offline_queued: Boolean(queuedEntry)
                            };
                        });
                    }
                } catch (e) {
                    console.warn("Could not merge offline states in applyTripToMap:", e);
                }

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

                    // Update companion headcount badge in floating summary pill
                    const paxTextEl = document.getElementById('trip-info-pax-text');
                    const paxIconEl = document.getElementById('trip-info-pax-icon');
                    if (paxTextEl) {
                        const isGroup = trip.trip_type === 'group' || (trip.total_pax && trip.total_pax > 1);
                        const totalPax = parseInt(trip.total_pax || 1, 10);
                        const males = parseInt(trip.companion_males || 0, 10);
                        const females = parseInt(trip.companion_females || 0, 10);
                        if (isGroup) {
                            paxTextEl.textContent = `${totalPax} Pax${(males > 0 || females > 0) ? ` (${males}M, ${females}F)` : ''}`;
                            if (paxIconEl) paxIconEl.className = 'fa-solid fa-users';
                        } else {
                            paxTextEl.textContent = 'Solo';
                            if (paxIconEl) paxIconEl.className = 'fa-solid fa-user';
                        }
                    }

                    const conveyorWrapper = document.getElementById('trip-conveyor-wrapper');
                    if (conveyorWrapper) conveyorWrapper.style.display = 'flex';
                    window.currentTripItems = trip.items;
                    window.currentRouteType = trip.route_type || 'Recommended';
                    plotTrip(window.currentTripItems, window.currentRouteType);
                } else {
                    if (typeof showToast === 'function') showToast("This trip has no destinations yet.");
                }

                if (typeof checkAndManageTripMapApprovalPolling === 'function') {
                    checkAndManageTripMapApprovalPolling(trip);
                }
            };

            window._tripMapApprovalPollingInterval = null;
            function checkAndManageTripMapApprovalPolling(trip) {
                const curTrip = trip || window.currentTrip;
                const currentView = document.body.getAttribute('data-view');
                if (!curTrip || (currentView && currentView !== 'trip_map')) {
                    if (window._tripMapApprovalPollingInterval) {
                        clearInterval(window._tripMapApprovalPollingInterval);
                        window._tripMapApprovalPollingInterval = null;
                    }
                    return;
                }

                let hasPending = false;
                if (curTrip && Array.isArray(curTrip.items)) {
                    for (const it of curTrip.items) {
                        if (!it) continue;
                        const hasProof = (Array.isArray(it.proof_images) && it.proof_images.length > 0) || it.proof_image;
                        const isApproved = Boolean(it.is_visited || it.proof_status === 'approved');
                        const isRejected = (it.proof_status === 'rejected');
                        if (!isApproved && !isRejected && hasProof && (it.proof_status === 'pending' || !it.proof_status)) {
                            hasPending = true;
                            break;
                        }
                    }
                }

                if (hasPending) {
                    if (!window._tripMapApprovalPollingInterval) {
                        window._tripMapApprovalPollingInterval = setInterval(() => {
                            const curView = document.body.getAttribute('data-view');
                            if (curView && curView !== 'trip_map') {
                                clearInterval(window._tripMapApprovalPollingInterval);
                                window._tripMapApprovalPollingInterval = null;
                                return;
                            }
                            if (document.visibilityState === 'visible' && !window._isStartingTrip) {
                                const tok = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                                if (!tok) return;
                                const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                                fetch(bUrl + '/api/tourist/itineraries', {
                                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + tok, 'ngrok-skip-browser-warning': 'true' }
                                })
                                .then(r => r.json())
                                .then(d => {
                                    if (d && Array.isArray(d.itineraries)) {
                                        const freshTrip = d.itineraries.find(t => t.id == tripId || t.local_id == tripId);
                                        if (freshTrip) {
                                            applyTripToMap(freshTrip);
                                        }
                                    }
                                })
                                .catch(() => {});
                            }
                        }, 3500);
                    }
                } else {
                    if (window._tripMapApprovalPollingInterval) {
                        clearInterval(window._tripMapApprovalPollingInterval);
                        window._tripMapApprovalPollingInterval = null;
                    }
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

            if (typeof showToast === 'function') showToast("Loading trip route...", "info", 1400);

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
                        try {
                            const tokenKey = token ? token.substring(0, 10) : 'default';
                            let merged = Array.isArray(data.itineraries) ? [...data.itineraries] : [];
                            const serverIdSet = new Set(merged.map(m => String(m.id || m.local_id || '')));
                            if (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function') {
                                const queued = window.OfflineTripManager.getQueuedTrips();
                                if (Array.isArray(queued)) {
                                    queued.forEach(q => {
                                        const qId = String(q.id || q.local_id || '');
                                        if (qId && !serverIdSet.has(qId)) {
                                            merged.unshift(q);
                                            serverIdSet.add(qId);
                                        }
                                    });
                                }
                            }
                            localStorage.setItem('saved_trips_' + tokenKey, JSON.stringify({ data: merged, itineraries: merged, timestamp: Date.now() }));
                            localStorage.setItem('intan_elyu_saved_trips', JSON.stringify(merged));
                            localStorage.setItem('intan_elyu_offline_saved_trips_backup', JSON.stringify(merged));
                            if (Array.isArray(window._cachedSavedTrips)) {
                                window._cachedSavedTrips = merged;
                            }
                        } catch (e) {}

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
                })
                .finally(clearLoadingTripToast);
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
                const sId = i.tourist_spot_id || i.destination_id || (i.destination ? i.destination.id : null);
                const isQueued = Boolean((window.OfflineCheckinManager && window.OfflineCheckinManager.isItemQueued(i.id, sId, window.currentTripId)) || i.is_offline_queued);
                return !hasSubmittedStopProof(i) && !isQueued;
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
                        const isLocationVerified = Boolean(item.is_location_verified || item.visited_at);
                        const isPhotoApproved = (item.proof_status === 'approved');
                        const isVisited = Boolean(item.is_visited && isPhotoApproved && isLocationVerified);
                        const isRejected = (item.proof_status === 'rejected');
                        const hasProof = Boolean((Array.isArray(item.proof_images) && item.proof_images.length > 0) || item.proof_image);
                        const isPending = Boolean(isLocationVerified && hasProof && (item.proof_status === 'pending' || !item.proof_status) && !isVisited);
                        const isLocOnly = Boolean(isLocationVerified && !hasProof && !isVisited);
                        const isPhotoOnly = Boolean(!isLocationVerified && hasProof && !isVisited && !isPhotoApproved);
                        const sId = item.tourist_spot_id || item.destination_id || (item.destination ? item.destination.id : null);
                        const isQueued = Boolean((window.OfflineCheckinManager && window.OfflineCheckinManager.isItemQueued(item.id, sId, window.currentTripId)) || item.is_offline_queued);

                        if (isQueued) {
                            // QUEUED OFFLINE - Amber Cloud / Clock
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(245,158,11,0.4); z-index: 2;">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 14px rgba(245,158,11,0.18); z-index: 1;">
                                    <span style="color: #b45309; font-size: 10px; font-weight: 800;">Queued (Offline)</span>
                                </div>
                            </div>
                        `;
                        } else if (isVisited) {
                            // VISITED - Green Checkmark + Clean Tag
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(16,185,129,0.4); z-index: 2;">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div style="background: #ffffff; border: 1.5px solid #bbf7d0; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 14px rgba(16,185,129,0.18); z-index: 1;">
                                    <span style="color: #15803d; font-size: 11px; font-weight: 700; text-decoration: line-through;">${dest.name}</span>
                                </div>
                            </div>
                        `;
                        } else if (isPhotoApproved && !isLocationVerified) {
                            // PHOTO APPROVED, GPS PENDING - Sky Blue Camera + Check-in Required Tag
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(2,132,199,0.4); z-index: 2;">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                                <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 14px rgba(2,132,199,0.18); z-index: 1;">
                                    <span style="color: #0284c7; font-size: 10px; font-weight: 800;">Photo Approved (GPS Req.)</span>
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
                                <div style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 14px rgba(245,158,11,0.18); z-index: 1;">
                                    <span style="color: #b45309; font-size: 11px; font-weight: 700; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${dest.name}</span>
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
                                <div style="background: #ffffff; border: 1.5px solid #fecaca; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 14px rgba(239,68,68,0.18); z-index: 1;">
                                    <span style="color: #b91c1c; font-size: 10px; font-weight: 800;">Re-upload</span>
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
                                <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 14px rgba(2,132,199,0.18); z-index: 1;">
                                    <span style="color: #0284c7; font-size: 10px; font-weight: 800;">Checked In</span>
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
                                <div style="background: #ffffff; border: 1.5px solid #ddd6fe; border-radius: 10px; padding: 4px 8px; margin-top: 5px; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 14px rgba(109,40,217,0.18); z-index: 1;">
                                    <span style="color: #6d28d9; font-size: 10px; font-weight: 800;">Photo Ready</span>
                                </div>
                            </div>
                        `;
                        } else if (idx === activeIndex) {
                            // ACTIVE - Vibrant Cyan/Blue Pin + Clean Next Stop Tag
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                                <div style="background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color: #ffffff; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; border: 2.5px solid #ffffff; box-shadow: 0 4px 16px rgba(2, 132, 199, 0.45); z-index: 2;">
                                    ${idx + 1}
                                </div>
                                <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 12px; padding: 4px 9px; margin-top: 6px; display: flex; align-items: center; gap: 6px; box-shadow: 0 6px 18px rgba(2, 132, 199, 0.22); z-index: 1;">
                                    <span style="background: #e0f2fe; color: #0284c7; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.4px;">Next</span>
                                    <span style="color: #0f172a; font-size: 12px; font-weight: 800; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${dest.name}</span>
                                </div>
                            </div>
                        `;
                        } else {
                            // LOCKED - Grey Padlock + Stop Number
                            iconHtml = `
                            <div style="display: flex; flex-direction: column; align-items: center; opacity: 0.92; cursor: pointer;">
                                <div style="background: #475569; color: #ffffff; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px; border: 2px solid rgba(255,255,255,0.8); box-shadow: 0 2px 8px rgba(0,0,0,0.25); z-index: 2;">
                                    <i class="fa-solid fa-lock" style="font-size: 10px;"></i>
                                </div>
                                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 3px 8px; margin-top: 4px; box-shadow: 0 2px 8px rgba(15,23,42,0.1); z-index: 1;">
                                    <span style="color: #475569; font-size: 10px; font-weight: 700;">Stop ${idx + 1}</span>
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

                    const routeMap = tripMap;
                    const routeRequest = (!navigator.onLine || window.isAppOnline === false)
                        ? Promise.reject(new Error('Offline navigation uses the local geodesic route'))
                        : fetch(`https://router.project-osrm.org/route/v1/${osrmProfile}/${coords.join(';')}?overview=full&geometries=geojson`);
                    routeRequest
                        .then(r => r.json())
                        .then(routeData => {
                            if (!document.getElementById('trip-info-distance') || !isTripMapActive(routeMap)) return;

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

                                    let activeLeg = (activeIndex >= 0 && items[activeIndex]) ? items[activeIndex] : (items[0] || null);
                                    let activeLegCost = activeLeg ? parseFloat(activeLeg.leg_cost || 0) : 0;
                                    let legCostDisplay = activeLegCost > 0 ? `₱${activeLegCost.toFixed(2)}` : 'Free / Own';
                                    let activeLegVehicleName = activeVehicle.name || 'Vehicle';
                                    if (activeLeg && activeLeg.transport_mode) {
                                        const foundVeh = VEHICLE_CATALOG.find(v => v.key === activeLeg.transport_mode.toLowerCase());
                                        if (foundVeh) activeLegVehicleName = foundVeh.name;
                                    }

                                    const etaEl = document.createElement('div');
                                    etaEl.innerHTML = `
                                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; padding: 6px 12px; border-radius: 14px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.16); display: flex; align-items: center; gap: 8px; color: #0f172a; white-space: nowrap; pointer-events: none; transform: translateY(-4px);">
                                    <div style="width: 26px; height: 26px; border-radius: 8px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; font-size: 12px; flex-shrink: 0;">
                                        <i class="fa-solid ${activeVehicle.icon}"></i>
                                    </div>
                                    <span style="font-size: 12px; font-weight: 800; color: #0f172a;">${activeLegVehicleName}</span>
                                </div>
                            `;

                                    window.etaMarker = new maplibregl.Marker({ element: etaEl, anchor: 'center' })
                                        .setLngLat([midLng, midLat])
                                        .addTo(tripMap);
                                }

                                // Safely update or add MapLibre sources/layers
                                if (tripMap.getSource('route')) {
                                    tripMap.getSource('route').setData(geojson);
                                    const routeBackground = tripMap.getSource('route-bg');
                                    if (routeBackground) routeBackground.setData(geojson);
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
                            if (!isTripMapActive(routeMap)) return;
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
            if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
                window.promptGuestLogin('photo');
                return;
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

        if (typeof window.savePersistentItemState !== 'function') {
            window.savePersistentItemState = function (itemId, stateUpdates) {
                if (!itemId) return;
                try {
                    const states = JSON.parse(localStorage.getItem('intan_elyu_verified_item_states') || '{}');
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
                } catch (e) { }
            };
        }

        window.applyPhotoProofUpdateLocally = function (itemId, proofUrls, proofStatus = 'pending') {
            if (!itemId) return;
            const targetIdStr = String(itemId);
            const urls = Array.isArray(proofUrls) ? proofUrls : (proofUrls ? [proofUrls] : []);
            if (urls.length === 0) return;

            // Persist locally so status and uploaded photos survive page refresh
            window.savePersistentItemState(itemId, {
                proof_image: urls[0],
                proof_images: urls,
                proof_status: proofStatus
            });

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
                const activeIndex = window.currentTripItems.findIndex(i => !hasSubmittedStopProof(i));
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

            // Persist locally so status and uploaded photos survive page refresh
            window.savePersistentItemState(itemId, {
                is_location_verified: true,
                visited_at: nowIso
            });

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
                const activeIndex = window.currentTripItems.findIndex(i => !hasSubmittedStopProof(i));
                if (typeof renderConveyorCards === 'function') {
                    renderConveyorCards(window.currentTripItems, activeIndex >= 0 ? activeIndex : 0);
                }
            }
        };

        window.submitProofPhotos = async function () {
            if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
                window.closeProofUploadModal();
                window.promptGuestLogin('photo');
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

            // Optimistically update memory and DOM right now!
            window.applyPhotoProofUpdateLocally(itemId, previewUrls, 'pending');

            const matchingItem = (window.currentTripItems || []).find(it => String(it.id) === String(itemId));
            const spotId = matchingItem ? (matchingItem.tourist_spot_id || matchingItem.destination_id || (matchingItem.destination ? matchingItem.destination.id : null)) : null;
            const tripId = window.currentTripId || (window.currentTrip ? window.currentTrip.id : null);

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

        window.executeLocationCheckIn = async function (itemId, spotName = '', spotLat = null, spotLng = null, btnEl = null) {
            if (!itemId) return;

            const originalHtml = btnEl ? btnEl.innerHTML : '';
            const restoreCheckinButton = () => {
                if (!btnEl) return;
                btnEl.innerHTML = originalHtml;
                const fix = window._tripLastGpsFix;
                if (fix) {
                    window.updateTripMapCheckinAvailability(fix.lat, fix.lng, fix.accuracy);
                } else {
                    btnEl.disabled = true;
                    btnEl.setAttribute('aria-disabled', 'true');
                }
            };
            const lockCheckedInButton = () => {
                if (!btnEl) return;
                btnEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Checked In';
                btnEl.disabled = true;
                btnEl.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                btnEl.style.cursor = 'default';
                btnEl.removeAttribute('onclick');
            };

            if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
                window.promptGuestLogin('check-in');
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

            const getDistance = (lat1, lon1, lat2, lon2) => {
                const R = 6371e3;
                const φ1 = lat1 * Math.PI / 180;
                const φ2 = lat2 * Math.PI / 180;
                const Δφ = (lat2 - lat1) * Math.PI / 180;
                const Δλ = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            };

            const parentTrip = (window._cachedTrip || window._activeTrip || (window._cachedSavedTrips || []).find(t => (t.items || []).some(i => String(i.id) === String(itemId))));
            const targetItem = (window.currentTripItems || []).find(it => String(it.id) === String(itemId));
            if (targetItem && (targetItem.is_location_verified || targetItem.visited_at)) {
                lockCheckedInButton();
                return;
            }
            const spotId = targetItem ? (targetItem.tourist_spot_id || targetItem.destination_id || (targetItem.destination ? targetItem.destination.id : null)) : null;
            const tripId = window.currentTripId || (parentTrip ? parentTrip.id : null);
            const tPax = parentTrip ? (parentTrip.total_pax || 1) : 1;
            const cMales = parentTrip ? (parentTrip.companion_males || 0) : 0;
            const cFemales = parentTrip ? (parentTrip.companion_females || 0) : 0;

            let sLat = (spotLat != null && !isNaN(spotLat)) ? parseFloat(spotLat) : null;
            let sLng = (spotLng != null && !isNaN(spotLng)) ? parseFloat(spotLng) : null;
            if ((sLat === null || sLng === null) && targetItem && targetItem.destination) {
                if (targetItem.destination.latitude != null) sLat = parseFloat(targetItem.destination.latitude);
                if (targetItem.destination.longitude != null) sLng = parseFloat(targetItem.destination.longitude);
            }

            const doSubmitCheckin = async (lat, lng, accuracy = null) => {
                if (sLat !== null && sLng !== null && !isNaN(sLat) && !isNaN(sLng)) {
                    const dist = getDistance(lat, lng, sLat, sLng);
                    if (dist > 70) {
                        const distM = Math.round(dist);
                        const distKm = (dist / 1000).toFixed(1);
                        const displayDist = dist >= 1000 ? `${distKm} km` : `${distM} meters`;
                        if (typeof showToast === 'function') {
                            showToast(`⚠️ You are ${displayDist} away from ${spotName || 'the destination'}. You must be within 70m to check in! 📍`, 'warning', 4500);
                        }
                        restoreCheckinButton();
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
                            accuracy: accuracy,
                            destLat: sLat,
                            destLng: sLng,
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
                    lockCheckedInButton();
                    return;
                }

                const formData = new FormData();
                formData.append('lat', lat);
                formData.append('lng', lng);
                if (accuracy != null && Number.isFinite(Number(accuracy))) formData.append('accuracy', accuracy);
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
                        lockCheckedInButton();
                        if (typeof showToast === 'function') {
                            showToast(data.message || `📍 Location verified at ${spotName}! Photo proof is required to verify your visit.`, 'success');
                        }
                        if (window.confetti) {
                            try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Check-in failed. Please ensure you are within 70m of the destination.', 'error');
                        }
                    }
                    if (btnEl && !(response.ok && (data.success || data.is_location_verified))) restoreCheckinButton();
                } catch (err) {
                    clearTimeout(timeoutId);
                    console.warn('Location check-in error, queuing offline:', err);
                    if (window.OfflineCheckinManager) {
                        window.applyLocationCheckinLocally(itemId);
                        lockCheckedInButton();
                        await window.OfflineCheckinManager.queueOfflineCheckin({
                            type: 'itinerary',
                            itemId: itemId,
                            spotId: spotId,
                            itineraryId: tripId,
                            spotName: spotName || 'Destination',
                            lat: lat,
                            lng: lng,
                            accuracy: accuracy,
                            destLat: sLat,
                            destLng: sLng,
                            capturedAt: new Date().toISOString()
                        });
                        if (typeof showToast === 'function') {
                            showToast('📍 Saved Offline! Check-in queued on device. Auto-syncs when online.', 'info');
                        }
                    } else {
                        if (typeof showToast === 'function') showToast('Network error during check-in.');
                    }
                    if (btnEl && !window.OfflineCheckinManager) restoreCheckinButton();
                }
            };

            const checkAndProceed = (lat, lng, accuracy = null) => {
                // Strict 70m radius check (applies to both offline and online)
                if (sLat != null && sLng != null && !isNaN(sLat) && !isNaN(sLng)) {
                    const dist = getDistance(lat, lng, sLat, sLng);
                    if (dist > 70) {
                        const distM = Math.round(dist);
                        const distKm = (dist / 1000).toFixed(1);
                        const displayDist = dist >= 1000 ? `${distKm} km` : `${distM} meters`;
                        if (typeof showToast === 'function') {
                            showToast(`⚠️ You are ${displayDist} away from ${spotName || 'the tourist site'}. You must be within 70m of the tourist site to check in! 📍`, 'warning', 4500);
                        }
                        restoreCheckinButton();
                        return; // Neither offline nor online check-in is allowed outside 70m.
                    }
                }
                doSubmitCheckin(lat, lng, accuracy);
            };

            const handleGpsErr = (err) => {
                console.warn('Geolocation error during check-in:', err);
                if (typeof showToast === 'function') {
                    showToast('📍 Unable to acquire accurate GPS location. Please turn on Location/GPS and try again.', 'error');
                }
                restoreCheckinButton();
            };

            const cachedPosition = window.deviceLastPos;
            if (cachedPosition && cachedPosition.coords &&
                Date.now() - Number(cachedPosition.timestamp || 0) < 60000 &&
                Number(cachedPosition.coords.accuracy || 9999) <= 150) {
                checkAndProceed(cachedPosition.coords.latitude, cachedPosition.coords.longitude, cachedPosition.coords.accuracy);
                return;
            }

            // Capacitor's native GPS continues working without internet; the WebView
            // navigator API can report POSITION_UNAVAILABLE while fully offline.
            if (typeof window.requestPreciseLocation === 'function') {
                try {
                    const location = await window.requestPreciseLocation(false);
                    if (location && Number.isFinite(Number(location.lat)) && Number.isFinite(Number(location.lng))) {
                        window.updateTripMapCheckinAvailability(location.lat, location.lng, location.accuracy);
                        checkAndProceed(Number(location.lat), Number(location.lng), location.accuracy);
                        return;
                    }
                } catch (nativeGpsError) {
                    console.warn('Native offline GPS unavailable, trying browser geolocation:', nativeGpsError);
                }
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        window.myLat = pos.coords.latitude;
                        window.myLng = pos.coords.longitude;
                        window.updateTripMapCheckinAvailability(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                        checkAndProceed(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    },
                    (err) => {
                        if (window.myLat && window.myLng) {
                            const lastFix = window._tripLastGpsFix || {};
                            checkAndProceed(window.myLat, window.myLng, lastFix.accuracy);
                        } else {
                            handleGpsErr(err);
                        }
                    },
                    { enableHighAccuracy: true, timeout: 7000, maximumAge: 30000 }
                );
            } else if (window.myLat && window.myLng) {
                const lastFix = window._tripLastGpsFix || {};
                checkAndProceed(window.myLat, window.myLng, lastFix.accuracy);
            } else {
                handleGpsErr('No geolocation support');
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
            window.updateTripMapCheckinAvailability(e.detail.lat, e.detail.lng, e.detail.accuracy);

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
                    window.updateTripMapCheckinAvailability(loc.lat, loc.lng, loc.accuracy);

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
            var isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
            if (pill) {
                pill.style.setProperty('display', isOffline ? 'inline-flex' : 'none', 'important');
            }
            if (!isOffline) {
                try {
                    localStorage.removeItem('intan_elyu_offline_mode');
                    localStorage.removeItem('intan_elyu_offline_guest');
                } catch(e) {}
            }
            updateTripSyncBanner(isOffline);
        }

        function updateTripSyncBanner(isOffline) {
            var syncBanner = document.getElementById('trip-sync-banner');
            if (!syncBanner) return;
            if (isOffline) {
                syncBanner.style.setProperty('display', 'none', 'important');
                return;
            }
            var queue = (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.getQueue === 'function') 
                ? window.OfflineCheckinManager.getQueue() : [];
            var queuedTrips = (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function')
                ? window.OfflineTripManager.getQueuedTrips() : [];
            var count = queue.length + queuedTrips.length;
            if (count > 0 && !(typeof window.isGuestUser === 'function' && window.isGuestUser())) {
                var textEl = document.getElementById('trip-sync-banner-text');
                if (textEl) {
                    textEl.textContent = count === 1 ? '1 offline check-in ready to sync' : `${count} offline updates ready to sync`;
                }
                syncBanner.style.setProperty('display', 'inline-flex', 'important');
            } else {
                syncBanner.style.setProperty('display', 'none', 'important');
            }
        }

        window.syncAllOfflineTripData = async function (btnElement) {
            if (!navigator.onLine || window.isAppOnline === false) {
                if (btnElement && typeof showToast === 'function') {
                    showToast("Still offline. Please check your internet connection.", "warning");
                }
                return;
            }
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            if (!token || (typeof window.isGuestUser === 'function' && window.isGuestUser())) return;

            let origHtml = '';
            if (btnElement) {
                origHtml = btnElement.innerHTML;
                btnElement.disabled = true;
                btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Syncing...';
            }

            try {
                let syncedAny = false;
                // 1. Sync offline trips first so any new trips/items exist on backend
                if (window.OfflineTripManager && typeof window.OfflineTripManager.syncQueuedTrips === 'function') {
                    const tCount = await window.OfflineTripManager.syncQueuedTrips();
                    if (tCount > 0) syncedAny = true;
                }
                // 2. Sync queued check-ins and photo proofs
                if (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.processQueue === 'function') {
                    const cCount = await window.OfflineCheckinManager.processQueue();
                    if (cCount > 0) syncedAny = true;
                }
                // 3. Sync offline completed trips
                if (window.OfflineCompletedTripsManager && typeof window.OfflineCompletedTripsManager.processQueue === 'function') {
                    const compCount = await window.OfflineCompletedTripsManager.processQueue();
                    if (compCount > 0) syncedAny = true;
                }

                // Invalidate local trip caches
                const tokenKey = token.substring(0, 10);
                localStorage.removeItem('saved_trips_' + tokenKey);
                localStorage.removeItem('dashboard_trips_' + tokenKey);
                localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
                if (typeof window._cachedSavedTrips !== 'undefined') window._cachedSavedTrips = null;

                window.dispatchEvent(new CustomEvent('tripsSynced'));
                window.dispatchEvent(new CustomEvent('checkins-synced'));

                // Reload active trip on map if function available
                const urlParams = new URLSearchParams(window.location.search);
                const activeTripId = urlParams.get('id') || (window.currentTrip ? window.currentTrip.id : null);
                if (activeTripId && typeof window.loadTripDetails === 'function') {
                    await window.loadTripDetails(activeTripId);
                }

                updateTripOfflineStatus();
                if (btnElement && typeof showToast === 'function') {
                    showToast(syncedAny ? "Offline updates successfully synced to cloud!" : "All data is already up to date!", "success", 4000);
                }
            } catch (err) {
                console.error("Auto/Manual sync error on trip map:", err);
                if (btnElement && typeof showToast === 'function') {
                    showToast("Failed to sync some offline data. Will auto-retry.", "warning");
                }
            } finally {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = origHtml;
                }
                updateTripOfflineStatus();
            }
        };

        window.addEventListener('online', () => {
            updateTripOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
            if (typeof window.syncAllOfflineTripData === 'function') {
                window.syncAllOfflineTripData();
            }
        });
        window.addEventListener('offline', updateTripOfflineStatus);
        window.addEventListener('appOnline', () => {
            updateTripOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
            if (typeof window.syncAllOfflineTripData === 'function') {
                window.syncAllOfflineTripData();
            }
        });
        window.addEventListener('appOffline', updateTripOfflineStatus);
        window.addEventListener('checkins-synced', () => updateTripOfflineStatus());
        window.addEventListener('tripsSynced', () => updateTripOfflineStatus());
        window.addEventListener('offline-checkin-queued', () => updateTripOfflineStatus());
        setTimeout(updateTripOfflineStatus, 150);
        setTimeout(() => {
            updateTripOfflineStatus();
            if (navigator.onLine && window.isAppOnline !== false) {
                if (typeof window.syncAllOfflineTripData === 'function') {
                    window.syncAllOfflineTripData();
                }
            }
        }, 350);

        initTripMap();
    })();
</script>
