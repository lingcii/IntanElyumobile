<style>
    .maplibregl-ctrl-bottom-right {
        bottom: 190px !important;
        right: 16px !important;
        z-index: 999;
    }
    .maplibregl-ctrl-group {
        box-shadow: none !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
<?php
$pageTitle = "Trip Route";
$backRoute = "saved_trips";
require_once __DIR__ . '/../components/header.php';
?>

<div id="trip-map" style="width: 100%; height: 100vh; background: #0a0f1c;"></div>

<!-- Floating Locate Me Button -->
<div class="btn-locate-me animate-slide-up" id="btn-trip-locate-me" style="position: absolute; bottom: 180px; right: 16px; width: 44px; height: 44px; border-radius: 14px; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(56, 189, 248, 0.35); display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 18px; z-index: 1000; cursor: pointer; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);" onclick="window.locateTripUser()">
    <i class="fa-solid fa-crosshairs"></i>
</div>

<!-- Floating Destination Conveyor Carousel -->
<div id="trip-conveyor-wrapper" style="position: absolute; bottom: max(env(safe-area-inset-bottom), 16px); left: 0; right: 0; z-index: 1000; display: flex; flex-direction: column; align-items: center; gap: 12px; pointer-events: none; width: 100%; box-sizing: border-box; overflow: hidden;">
    
    <!-- Trip Overview Summary Header Pill -->
    <div id="trip-summary-pill" style="align-self: center; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 100px; padding: 8px 18px; display: flex; align-items: center; gap: 14px; box-shadow: none; pointer-events: auto;">
        <div style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800;">
            <i class="fa-solid fa-route" style="color:#38bdf8;"></i> <span id="trip-info-distance">-- km</span>
        </div>
        <div style="width:1px; height:12px; background:rgba(255,255,255,0.2);"></div>
        <div style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800;">
            <i class="fa-solid fa-stopwatch" style="color:#34d399;"></i> <span id="trip-info-time">-- mins</span>
        </div>
        <div style="width:1px; height:12px; background:rgba(255,255,255,0.2);"></div>
        <div style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800;">
            <i id="trip-info-vehicle-icon" class="fa-solid fa-car" style="color:#f59e0b;"></i> <span id="trip-info-vehicle-name">Own Car</span>
        </div>
    </div>

    <!-- Conveyor Cards Carousel Scroll Container -->
    <div id="conveyor-cards-scroll" style="display: flex; justify-content: flex-start; align-items: stretch; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-padding: 0 16px; padding: 4px 16px 12px 16px; pointer-events: auto; scroll-behavior: smooth; width: 100%; box-sizing: border-box;" class="hide-scrollbar">
        <!-- Injected via JS -->
    </div>
</div>

<!-- Prompt Card when within 300m -->
<div id="checkin-prompt-card" style="position: absolute; top: 80px; left: 16px; right: 16px; z-index: 1001; background: rgba(15,23,42,0.9); border: 1.5px solid rgba(56,189,248,0.4); border-radius: 18px; padding: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: none; align-items: center; justify-content: space-between; backdrop-filter: blur(10px); animation: slideDown 0.3s ease-out;">
    <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:24px;">📍</span>
        <div style="text-align: left;">
            <h5 style="margin:0; font-size:13px; font-weight:700; color:#fff;">You've arrived!</h5>
            <p id="checkin-prompt-dest-name" style="margin:2px 0 0; font-size:11px; color:rgba(255,255,255,0.7); font-weight:500;"></p>
        </div>
    </div>
    <button onclick="window.triggerMapCheckinModal()" style="background:#38bdf8; color:#000; border:none; padding:8px 14px; border-radius:10px; font-weight:800; font-size:11px; cursor:pointer; box-shadow: 0 4px 10px rgba(56,189,248,0.25);">
        Check In
    </button>
</div>

<!-- Check-in Verification Modal (GPS and Photo Proof) -->
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.78); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:99999; justify-content:center; align-items:center;">
    <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:1px solid rgba(56, 189, 248, 0.3); border-radius:24px; padding:28px 24px; width:90%; max-width:380px; box-shadow:0 24px 60px rgba(0,0,0,0.6), 0 0 30px rgba(56,189,248,0.15); text-align:center;">
        <div style="font-size:48px; margin-bottom:12px;">📸</div>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Claim Your Reward</h3>
        <p style="font-size:13px; color:rgba(226, 232, 240, 0.85); margin-bottom:20px; line-height:1.5;">Take a selfie or capture a photo at this destination to verify your visit and earn <strong style="color:#38bdf8; font-weight:800;">+50 XP</strong> & <strong style="color:#38bdf8; font-weight:800;">+50 Points</strong>.</p>

        <input type="hidden" id="checkin-item-id">
        
        <!-- Step 1: Photo Proof -->
        <div style="margin-bottom: 16px; text-align: left;">
            <label style="font-size:11px; font-weight:800; color:#38bdf8; margin-bottom:6px; display:block; text-transform:uppercase; letter-spacing:0.5px;">Step 1: Photo Proof (Required)</label>
            <input type="file" id="checkin-proof-image" accept="image/*" style="display:none;" onchange="window.handlePhotoSelected(this)">
            <button type="button" onclick="window.openCheckinImagePickerModal()" id="btn-select-photo" style="width:100%; padding:14px; background:rgba(56,189,248,0.1); border:1.5px dashed rgba(56,189,248,0.4); border-radius:14px; color:#38bdf8; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer; transition:all 0.2s ease;">
                <i class="fa-solid fa-camera" style="font-size:16px;"></i> <span id="photo-status-text">Take or Choose Photo</span>
            </button>

            <!-- Picture Preview Container (Displays actual picture preview instead of filename string) -->
            <div id="checkin-photo-preview-container" style="display:none; margin-top:12px; position:relative; border-radius:16px; overflow:hidden; border:1.5px solid rgba(56,189,248,0.4); background:rgba(15,23,42,0.8); box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                <img id="checkin-photo-preview-img" src="" alt="Proof Preview" style="width:100%; max-height:180px; object-fit:cover; display:block;">
                <div style="position:absolute; top:8px; right:8px; display:flex; gap:6px;">
                    <button type="button" onclick="window.openCheckinImagePickerModal()" title="Change Picture" style="background:rgba(15,23,42,0.85); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; backdrop-filter:blur(6px);">
                        <i class="fa-solid fa-arrows-rotate" style="font-size:13px;"></i>
                    </button>
                    <button type="button" onclick="window.removeCheckinPhoto()" title="Remove Picture" style="background:rgba(239,68,68,0.85); color:#ffffff; border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; backdrop-filter:blur(6px);">
                        <i class="fa-solid fa-xmark" style="font-size:14px;"></i>
                    </button>
                </div>
                <div style="padding:6px 10px; background:rgba(15,23,42,0.9); font-size:10px; font-weight:700; color:#38bdf8; text-transform:uppercase; text-align:center; border-top:1px solid rgba(255,255,255,0.08);">
                    <i class="fa-solid fa-circle-check" style="margin-right:4px; color:#34c759;"></i> Picture Proof Attached
                </div>
            </div>
        </div>

        <!-- Step 2: Location Verification -->
        <div style="margin-bottom: 12px; text-align: left;">
            <label style="font-size:11px; font-weight:800; color:#38bdf8; margin-bottom:6px; display:block; text-transform:uppercase; letter-spacing:0.5px;">Step 2: Location Check-in</label>
            <button class="btn-primary" id="btn-verify-gps" style="width:100%; padding:14px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #38bdf8 0%, #2563eb 100%); border:1px solid rgba(255,255,255,0.25); color:#ffffff; border-radius:14px; box-shadow:0 4px 16px rgba(56,189,248,0.4); cursor:pointer;" onclick="verifyGpsCheckIn()">
                <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Submit
            </button>
        </div>

        <button style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#e2e8f0; font-size:13px; font-weight:700; cursor:pointer;" onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<!-- Check-in Image Picker Choice Modal -->
<div id="checkin-image-picker-modal" onclick="if(event.target===this) window.closeCheckinImagePickerModal()" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:999999; align-items:flex-end; justify-content:center; padding:0; margin:0; box-sizing:border-box;">
    <div style="background:linear-gradient(135deg, rgba(30,41,59,0.98) 0%, rgba(15,23,42,1) 100%); border-top:1px solid rgba(56,189,248,0.3); border-radius:28px 28px 0 0; width:100%; max-width:500px; padding:26px 22px; box-shadow:0 -10px 45px rgba(0,0,0,0.8); animation:slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); box-sizing:border-box;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:17px; font-weight:800; color:#f8fafc; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-camera" style="color:#38bdf8; font-size:18px;"></i> Attach Proof Photo
            </h3>
            <button type="button" onclick="window.closeCheckinImagePickerModal()" style="background:rgba(255,255,255,0.08); border:none; color:#94a3b8; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <button type="button" onclick="window.selectCheckinImageSource('camera')" style="width:100%; padding:15px; background:linear-gradient(135deg, rgba(56,189,248,0.18) 0%, rgba(37,99,235,0.22) 100%); border:1px solid rgba(56,189,248,0.35); border-radius:18px; color:#38bdf8; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                <i class="fa-solid fa-camera" style="font-size:17px;"></i> Take Photo with Camera
            </button>
            <button type="button" onclick="window.selectCheckinImageSource('gallery')" style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:18px; color:#f8fafc; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, background 0.15s ease;">
                <i class="fa-solid fa-images" style="font-size:17px; color:#38bdf8;"></i> Choose from Photo Gallery
            </button>
            <button type="button" onclick="window.closeCheckinImagePickerModal()" style="width:100%; padding:12px; background:transparent; border:none; color:#94a3b8; font-size:13px; font-weight:600; cursor:pointer; margin-top:4px;">
                Cancel
            </button>
        </div>
    </div>
</div>

<style>
@keyframes slideDown {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
(function() {
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
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
            function(pos) {
                window.myLat = pos.coords.latitude;
                window.myLng = pos.coords.longitude;
                window.currentGPSLat = pos.coords.latitude;
                window.currentGPSLng = pos.coords.longitude;
                window.currentGPSSource = 'gps';
                document.dispatchEvent(new CustomEvent('gpsUpdated', {
                    detail: { lat: pos.coords.latitude, lng: pos.coords.longitude, accuracy: pos.coords.accuracy, source: 'gps' }
                }));
            },
            function(err) {
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
                    "tiles": ["https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png"],
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

    function loadTripData() {
        const urlParams = new URLSearchParams(window.location.search);
        const tripId = urlParams.get('trip_id');
        
        if (!tripId) {
            if (typeof showToast === 'function') showToast("No trip ID provided.");
            return;
        }

        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        if (typeof showToast === 'function') showToast("Loading trip route...");

        fetch(backendUrl + '/api/tourist/itineraries', {
            headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        })
        .then(r => r.json())
        .then(data => {
            if (!document.getElementById('trip-map')) return; // Page was unmounted

            if (data.itineraries) {
                const trip = data.itineraries.find(t => t.id == tripId);
                if (trip) {
                    const headerTitleEl = document.querySelector('.header-title');
                    if (headerTitleEl) headerTitleEl.textContent = trip.title;
                    const nameEl = document.getElementById('trip-info-name');
                    if (nameEl) nameEl.textContent = trip.title;
                    
                    if (trip.items && trip.items.length > 0) {
                        const descEl = document.getElementById('trip-info-desc');
                        if (descEl) descEl.textContent = `Route preview for ${trip.items.length} destination(s).`;
                        const routeTypeEl = document.getElementById('trip-info-route-type');
                        if (routeTypeEl) routeTypeEl.textContent = trip.route_type || 'Recommended';
                        
                        const tMap = {
                            'own_car': { name: 'Own Car', icon: 'fa-car' },
                            'taxi': { name: 'Taxi', icon: 'fa-taxi' },
                            'jeepney': { name: 'Jeepney', icon: 'fa-van-shuttle' },
                            'private_bus': { name: 'Private Bus', icon: 'fa-bus' },
                            'mini_bus': { name: 'Mini Bus', icon: 'fa-bus-simple' },
                            'lutrampco': { name: 'LUTRAMPCO', icon: 'fa-van-shuttle' }
                        };
                        const trans = tMap[trip.transport_mode];
                        const vehicleNameEl = document.getElementById('trip-info-vehicle-name');
                        const vehicleIconEl = document.getElementById('trip-info-vehicle-icon');
                        if (trans) {
                            if (vehicleNameEl) vehicleNameEl.textContent = trans.name;
                            if (vehicleIconEl) vehicleIconEl.className = 'fa-solid ' + trans.icon;
                        } else {
                            if (vehicleNameEl) vehicleNameEl.textContent = 'Own Car';
                            if (vehicleIconEl) vehicleIconEl.className = 'fa-solid fa-car';
                        }
                        
                        const conveyorWrapper = document.getElementById('trip-conveyor-wrapper');
                        if (conveyorWrapper) conveyorWrapper.style.display = 'flex';
                        window.currentTripItems = trip.items;
                        window.currentRouteType = trip.route_type || 'Recommended';
                        plotTrip(window.currentTripItems, window.currentRouteType);
                    } else {
                        if (typeof showToast === 'function') showToast("This trip has no destinations yet.");
                    }
                }
            }
        })
        .catch(e => console.error("Failed to load trip", e));
    }

    function plotTrip(items, routeType = 'Recommended') {
        // Real-Time Live Navigation Optimization Algorithm
        if (window.myLat && window.myLng && items.length > 1) {
            let sorted = [];
            let currentLat = window.myLat;
            let currentLng = window.myLng;
            let remaining = [...items];
            
            const calcDist = (lat1, lon1, lat2, lon2) => {
                const p = 0.017453292519943295;
                const c = Math.cos;
                const a = 0.5 - c((lat2 - lat1) * p)/2 + c(lat1 * p) * c(lat2 * p) * (1 - c((lon2 - lon1) * p))/2;
                return 12742 * Math.asin(Math.sqrt(a));
            };

            if (routeType === 'Recommended' || routeType === 'Alternate') {
                while(remaining.length > 0) {
                    let targetIdx = 0;
                    let targetDist = routeType === 'Recommended' ? Infinity : -1;
                    
                    for (let i = 0; i < remaining.length; i++) {
                        let dest = remaining[i].destination;
                        if (!dest) continue;
                        let lat = parseFloat(dest.lat || dest.latitude);
                        let lng = parseFloat(dest.lng || dest.longitude);
                        let d = calcDist(currentLat, currentLng, lat, lng);
                        if ((routeType === 'Recommended' && d < targetDist) || (routeType === 'Alternate' && d > targetDist)) {
                            targetDist = d;
                            targetIdx = i;
                        }
                    }
                    let nextSpot = remaining.splice(targetIdx, 1)[0];
                    sorted.push(nextSpot);
                    currentLat = parseFloat(nextSpot.destination.lat || nextSpot.destination.latitude);
                    currentLng = parseFloat(nextSpot.destination.lng || nextSpot.destination.longitude);
                }
            } else if (routeType === 'Scenic Route') {
                let avgLat = remaining.reduce((sum, spot) => sum + parseFloat(spot.destination.lat || spot.destination.latitude), 0) / remaining.length;
                if (window.myLat > avgLat) {
                    sorted = remaining.sort((a, b) => parseFloat(b.destination.lat || b.destination.latitude) - parseFloat(a.destination.lat || a.destination.latitude));
                } else {
                    sorted = remaining.sort((a, b) => parseFloat(a.destination.lat || a.destination.latitude) - parseFloat(b.destination.lat || b.destination.latitude));
                }
            }
            items = sorted;
        }

        const coords = [];
        const markerPoints = [];
        const bounds = new maplibregl.LngLatBounds();
        
        // Clear old markers before re-drawing real-time sequences
        if (window.tripMarkers) window.tripMarkers.forEach(m => m.remove());
        window.tripMarkers = [];

        const activeIndex = items.findIndex(i => !i.is_visited);

        items.forEach((item, idx) => {
            const dest = item.destination;
            if (dest) {
                const lat = parseFloat(dest.lat || dest.latitude);
                const lng = parseFloat(dest.lng || dest.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    // Route Rendering Optimization: Only draw the route to the active destination!
                    if (idx === activeIndex || (!window.myLat && !window.myLng)) {
                        coords.push(`${lng},${lat}`);
                    }
                    markerPoints.push([lng, lat]);
                    bounds.extend([lng, lat]);

                    let iconHtml = '';
                    let labelHtml = '';

                    if (item.is_visited) {
                        // VISITED - Green Checkmark
                        iconHtml = `
                            <div style="background: #10b981; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; border: 3px solid #ffffff; box-shadow: none;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        `;
                        labelHtml = `
                            <div style="background: rgba(16,185,129,0.8); color: white; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; white-space: nowrap; margin-top: 4px; border: 1px solid rgba(255,255,255,0.2); box-shadow: none; text-align: center; text-decoration: line-through;">
                                ${dest.name}
                            </div>
                        `;
                    } else if (idx === activeIndex) {
                        // ACTIVE - Glowing Blue Number
                        iconHtml = `
                            <div style="background: #38bdf8; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; border: 3px solid #ffffff; box-shadow: none; animation: pulse 2s infinite;">
                                ${idx + 1}
                            </div>
                        `;
                        labelHtml = `
                            <div style="background: #0f172a; color: #38bdf8; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; white-space: nowrap; margin-top: 4px; border: 1px solid #38bdf8; box-shadow: none; text-align: center;">
                                Next: ${dest.name}
                            </div>
                        `;
                    } else {
                        // LOCKED - Grey Padlock
                        iconHtml = `
                            <div style="background: #94a3b8; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; border: 3px solid #ffffff; box-shadow: none; opacity: 0.8;">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                        `;
                        labelHtml = `
                            <div style="background: rgba(148,163,184,0.8); color: white; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; white-space: nowrap; margin-top: 4px; border: 1px solid rgba(255,255,255,0.2); box-shadow: none; text-align: center; opacity: 0.8;">
                                Locked
                            </div>
                        `;
                    }

                    const el = document.createElement('div');
                    el.innerHTML = iconHtml + labelHtml;
                    el.style.display = 'flex';
                    el.style.flexDirection = 'column';
                    el.style.alignItems = 'center';

                    const m = new maplibregl.Marker({ element: el, anchor: 'top' })
                        .setLngLat([lng, lat])
                        .addTo(tripMap);
                    
                    window.tripMarkers.push(m);
                }
            }
        });

        // Build Conveyor Cards HTML
        let conveyorHtml = '';
        items.forEach((item, idx) => {
            const dest = item.destination;
            if (!dest) return;
            const lat = parseFloat(dest.lat || dest.latitude);
            const lng = parseFloat(dest.lng || dest.longitude);
            const isVisited = item.is_visited;
            const isActive = idx === activeIndex;

            let badgeHtml = '';
            if (isVisited) {
                badgeHtml = `<span style="background:rgba(52,199,89,0.15); border:1px solid rgba(52,199,89,0.4); color:#34c759; padding:3px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-circle-check"></i> Visited</span>`;
            } else if (isActive) {
                badgeHtml = `<span style="background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; padding:3px 10px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;">Stop ${idx + 1} of ${items.length} • NEXT</span>`;
            } else {
                badgeHtml = `<span style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:rgba(226,232,240,0.7); padding:3px 10px; border-radius:100px; font-size:10px; font-weight:700; flex-shrink:0;">Stop ${idx + 1} of ${items.length}</span>`;
            }

            let proofThumbnail = '';
            if (item.proof_image) {
                let pUrl = item.proof_image;
                if (!pUrl.startsWith('http') && !pUrl.startsWith('data:') && !pUrl.startsWith('blob:')) {
                    let b = (window.backendUrl || '').replace(/\/+$/, '');
                    pUrl = b + '/' + pUrl.replace(/^\//, '');
                }
                let fallbackUrl = (window.backendUrl || '').replace(/\/+$/, '') + '/api/image/' + item.proof_image.replace(/^\//, '');
                proofThumbnail = `<img src="${pUrl}" onerror="if(this.src!=='${fallbackUrl}'){this.src='${fallbackUrl}';}" alt="Proof" style="width:40px; height:40px; border-radius:8px; object-fit:cover; border:1px solid rgba(52,199,89,0.5); box-shadow:0 2px 8px rgba(0,0,0,0.3); flex-shrink:0;">`;
            }

            let actionBtnHtml = '';
            if (isVisited || item.proof_status === 'approved') {
                actionBtnHtml = `<div style="display:flex; align-items:center; gap:10px;">
                    ${proofThumbnail}
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <span style="background:rgba(52,199,89,0.15); border:1px solid rgba(52,199,89,0.35); color:#34c759; font-weight:800; font-size:11px; padding:3px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Visited & Verified</span>
                        <button type="button" onclick="event.stopPropagation(); window.openWriteTestimonyModal('${item.tourist_spot_id || (item.destination ? item.destination.id : '')}')" style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; font-size:11px; font-weight:700; padding:4px 10px; border-radius:100px; cursor:pointer; width:fit-content; margin-top:2px;">
                            <i class="fa-solid fa-pen" style="margin-right:4px;"></i> Review Site
                        </button>
                    </div>
                </div>`;
            } else if (item.proof_status === 'rejected') {
                actionBtnHtml = `<div style="display:flex; align-items:center; gap:8px;">
                    ${proofThumbnail}
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <span style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.35); color:#ef4444; font-weight:800; font-size:11px; padding:3px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                        <button onclick="event.stopPropagation(); window.currentCheckinItemId='${item.id}'; window.triggerMapCheckinModal()" style="background:linear-gradient(135deg, #ef4444, #dc2626); color:#ffffff; border:none; padding:6px 10px; border-radius:100px; font-weight:800; font-size:10px; cursor:pointer;"><i class="fa-solid fa-camera" style="margin-right:4px;"></i> Re-upload Proof</button>
                    </div>
                </div>`;
            } else if (item.proof_image && (item.proof_status === 'pending' || !item.proof_status)) {
                actionBtnHtml = `<div style="display:flex; align-items:center; gap:8px;">
                    ${proofThumbnail}
                    <span style="background:rgba(255,149,0,0.15); border:1px solid rgba(255,149,0,0.35); color:#FF9500; font-weight:800; font-size:11px; padding:4px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-clock"></i> Pending Validation</span>
                </div>`;
            } else if (isActive) {
                actionBtnHtml = `<button onclick="event.stopPropagation(); window.currentCheckinItemId='${item.id}'; window.triggerMapCheckinModal()" style="background:linear-gradient(135deg, #38bdf8, #2563eb); color:#ffffff; border:none; padding:10px 16px; border-radius:100px; font-weight:800; font-size:12px; box-shadow:none; cursor:pointer;"><i class="fa-solid fa-location-crosshairs" style="margin-right:4px;"></i> Check In (+50 XP)</button>`;
            } else {
                actionBtnHtml = `<span style="color:rgba(226,232,240,0.5); font-size:12px; font-weight:700;"><i class="fa-solid fa-lock"></i> Locked</span>`;
            }

            const classBadge = dest.classification_status ? `<span style="padding: 2px 7px; border-radius: 100px; font-size: 8px; font-weight: 800; text-transform: uppercase; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')}; flex-shrink:0;">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : '';

            conveyorHtml += `
            <div id="conveyor-card-${idx}" class="conveyor-card ${isActive ? 'active' : ''}" onclick="window.flyToConveyorSpot(${lng}, ${lat}, ${idx})" style="scroll-snap-align: center; flex: 0 0 calc(100vw - 64px); max-width: 320px; min-width: 250px; box-sizing: border-box; background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1.5px solid ${isActive ? 'rgba(56, 189, 248, 0.6)' : 'rgba(255, 255, 255, 0.15)'}; border-radius: 24px; padding: 16px 18px; box-shadow: none; cursor: pointer; transition: transform 0.25s ease, border-color 0.25s ease;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; gap:8px;">
                    ${badgeHtml}
                    ${classBadge}
                </div>
                <h4 style="margin:0 0 4px 0; font-size:15px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${dest.name}">${dest.name}</h4>
                <p style="margin:0 0 12px 0; font-size:12px; color:rgba(226,232,240,0.8); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fa-solid fa-location-dot" style="color:#38bdf8; margin-right:4px;"></i>${dest.municipality || 'La Union'}</p>
                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                    <span style="font-size:11px; font-weight:700; color:rgba(226,232,240,0.6); flex-shrink:0;"><i class="fa-solid fa-compass" style="color:#38bdf8;"></i> Tap to view</span>
                    <div style="flex-shrink:0;">${actionBtnHtml}</div>
                </div>
            </div>`;
        });

        const conveyorScroll = document.getElementById('conveyor-cards-scroll');
        if (conveyorScroll) {
            conveyorScroll.innerHTML = conveyorHtml;
            setTimeout(() => {
                const targetCard = document.querySelector('.conveyor-card.active') || document.getElementById('conveyor-card-0');
                if (targetCard) {
                    targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            }, 100);
        }

        window.flyToConveyorSpot = function(lng, lat, idx) {
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

        // Dynamically connect the physical GPS location as the starting point of the route!
        if (window.myLat && window.myLng) {
            coords.unshift(`${window.myLng},${window.myLat}`);
            markerPoints.unshift([window.myLng, window.myLat]);
            bounds.extend([window.myLng, window.myLat]);
            
            const gpsEl = document.createElement('div');
            gpsEl.innerHTML = `
                <div style="width: 32px; height: 32px; background-color: #FFFFFF; border: 2px solid #ff9500; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ff9500; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                    <i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>
                </div>
            `;
            window.tripGpsMarker = new maplibregl.Marker({ element: gpsEl, anchor: 'center' })
                .setLngLat([window.myLng, window.myLat])
                .addTo(tripMap);
        }

        if (markerPoints.length > 0) {
            if (coords.length > 1) {
                let osrmProfile = 'driving';
                let routeColor = '#38bdf8';
                
                if (routeType === 'Alternate') {
                    osrmProfile = 'walking';
                    routeColor = '#ffcc00';
                } else if (routeType === 'Scenic Route') {
                    routeColor = '#ff3b30';
                }

                fetch(`https://router.project-osrm.org/route/v1/${osrmProfile}/${coords.join(';')}?overview=full&geometries=geojson`)
                .then(r => r.json())
                .then(routeData => {
                    if (!document.getElementById('trip-info-distance')) return; // Page was unmounted
                    
                    if (routeData.code === 'Ok' && routeData.routes.length > 0) {
                        const route = routeData.routes[0];
                        const geojson = route.geometry;
                        
                        let distanceKm = route.distance / 1000;
                        let durationMin = route.duration / 60;
                        
                        // Simulate heavy traffic load in the province
                        durationMin *= 5.0;
                        
                        // Mathematically adjust walking back to slow car times
                        if (osrmProfile === 'walking') {
                            durationMin = distanceKm * 3.5;
                        }
                        
                        if (routeType === 'Scenic Route') {
                            durationMin *= 1.5; distanceKm *= 1.4;
                        } else if (routeType === 'Alternate') {
                            durationMin *= 1.2; distanceKm *= 1.15;
                        }
                        
                        window.setTxt('trip-info-distance', distanceKm.toFixed(1) + ' km');
                        window.setTxt('trip-info-time', Math.round(durationMin) + ' mins');

                        // NEW MECHANIC: Real-time Dynamic Google-Maps-Style Floating ETA Box!
                        if (window.etaMarker) window.etaMarker.remove();
                        if (coords.length > 1) {
                            // Find the midpoint between the GPS (or start point) and the very first destination
                            const startLngLat = coords[0].split(',').map(Number);
                            const nextLngLat = coords[1].split(',').map(Number);
                            const midLng = (startLngLat[0] + nextLngLat[0]) / 2;
                            const midLat = (startLngLat[1] + nextLngLat[1]) / 2;
                            
                            // Grab the OSRM duration of the FIRST leg (the drive to the next stop)
                            let leg = route.legs ? route.legs[0] : null;
                            let legDistKm = leg ? (leg.distance / 1000) : distanceKm;
                            let legDurMin = leg ? (leg.duration / 60) : (durationMin / 5.0); // raw OSRM minutes
                            
                            // Apply the same heavy traffic load multiplier to the current leg
                            legDurMin *= 5.0;
                            
                            if (osrmProfile === 'walking') legDurMin = legDistKm * 3.5;
                            if (routeType === 'Scenic Route') { legDurMin *= 1.5; legDistKm *= 1.4; }
                            else if (routeType === 'Alternate') { legDurMin *= 1.2; legDistKm *= 1.15; }
                            
                            let iconHtml = '<i class="fa-solid fa-car"></i>';
                            if (osrmProfile === 'walking') iconHtml = '<i class="fa-solid fa-person-walking"></i>';

                            const etaEl = document.createElement('div');
                            etaEl.innerHTML = `
                                <div style="background: white; border: 1px solid #e2e8f0; padding: 6px 10px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 13px; font-weight: 800; color: #0f172a; display: flex; flex-direction: column; align-items: center; white-space: nowrap;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        ${iconHtml} <span style="font-size: 15px;">${Math.round(legDurMin)} min</span>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px; font-weight: 600;">
                                        ${legDistKm < 1 ? Math.round(legDistKm * 1000) + ' m' : legDistKm.toFixed(1) + ' km'}
                                    </div>
                                </div>
                            `;
                            
                            // Place the floating ETA box directly on the map route
                            window.etaMarker = new maplibregl.Marker({ element: etaEl, anchor: 'center' })
                                .setLngLat([midLng, midLat])
                                .addTo(tripMap);
                        }

                        // Safely update or add MapLibre sources/layers to prevent "already exists" errors during live navigation
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
                                'paint': { 'line-color': routeColor, 'line-width': 5, 'line-opacity': 0.8 }
                            });
                            
                            tripMap.addSource('route-bg', { 'type': 'geojson', 'data': geojson });
                            tripMap.addLayer({
                                'id': 'route-line-bg',
                                'type': 'line',
                                'source': 'route-bg',
                                'layout': { 'line-join': 'round', 'line-cap': 'round' },
                                'paint': { 'line-color': '#1E3A8A', 'line-width': 8, 'line-opacity': 0.5 }
                            }, 'route-line');
                        }
                        
                    }
                }).catch(e => {
                    console.error("Trip routing error", e);
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

    // Map Checkin Modal functions
    window.selectedCheckinImageFile = null;

    window.triggerMapCheckinModal = function() {
        if (!window.currentCheckinItemId) return;
        document.getElementById('checkin-item-id').value = window.currentCheckinItemId;
        document.getElementById('checkin-modal').style.display = 'flex';
    };

    window.openCheckinImagePickerModal = function() {
        const modal = document.getElementById('checkin-image-picker-modal');
        if (modal) modal.style.display = 'flex';
    };

    window.closeCheckinImagePickerModal = function() {
        const modal = document.getElementById('checkin-image-picker-modal');
        if (modal) modal.style.display = 'none';
    };

    window.selectCheckinImageSource = async function(mode) {
        window.closeCheckinImagePickerModal();
        const input = document.getElementById('checkin-proof-image');

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
                    quality: 85,
                    allowEditing: false,
                    resultType: 'dataUrl',
                    source: mode === 'camera' ? 'CAMERA' : 'PHOTOS'
                });

                if (image && image.dataUrl) {
                    const res = await fetch(image.dataUrl);
                    const blob = await res.blob();
                    const file = new File([blob], 'proof_' + Date.now() + '.jpg', { type: blob.type || 'image/jpeg' });
                    window.selectedCheckinImageFile = file;
                    window.updateCheckinPhotoPreview(image.dataUrl);
                }
            } catch (err) {
                console.warn('Capacitor Camera cancel or error:', err);
            }
        } else {
            if (!input) return;
            if (mode === 'camera') {
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }
            input.click();
        }
    };

    window.handlePhotoSelected = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            window.selectedCheckinImageFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                window.updateCheckinPhotoPreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    };

    window.updateCheckinPhotoPreview = function(dataUrl) {
        const previewContainer = document.getElementById('checkin-photo-preview-container');
        const previewImg = document.getElementById('checkin-photo-preview-img');
        const btnText = document.getElementById('photo-status-text');
        const btn = document.getElementById('btn-select-photo');

        if (previewContainer && previewImg) {
            previewImg.src = dataUrl;
            previewContainer.style.display = 'block';
        }

        if (btnText) btnText.textContent = 'Change Photo 📸';
        if (btn) {
            btn.style.background = 'rgba(52, 199, 89, 0.15)';
            btn.style.borderColor = 'rgba(52, 199, 89, 0.5)';
            btn.style.color = '#34c759';
        }
    };

    window.removeCheckinPhoto = function() {
        window.selectedCheckinImageFile = null;
        const imgInput = document.getElementById('checkin-proof-image');
        if (imgInput) imgInput.value = '';

        const previewContainer = document.getElementById('checkin-photo-preview-container');
        const previewImg = document.getElementById('checkin-photo-preview-img');
        if (previewContainer) previewContainer.style.display = 'none';
        if (previewImg) previewImg.src = '';

        const photoBtn = document.getElementById('btn-select-photo');
        const photoText = document.getElementById('photo-status-text');
        if (photoText) photoText.textContent = 'Take or Choose Photo';
        if (photoBtn) {
            photoBtn.style.background = 'rgba(56,189,248,0.1)';
            photoBtn.style.borderColor = 'rgba(56,189,248,0.4)';
            photoBtn.style.color = '#38bdf8';
        }
    };

    window.closeCheckinModal = function() {
        document.getElementById('checkin-modal').style.display = 'none';
        document.getElementById('checkin-item-id').value = '';
        window.removeCheckinPhoto();

        const btn = document.getElementById('btn-verify-gps');
        if (btn) { btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Submit'; btn.disabled = false; }
    };

    window.verifyGpsCheckIn = async function() {
        const imageFile = window.selectedCheckinImageFile || (document.getElementById('checkin-proof-image') ? document.getElementById('checkin-proof-image').files[0] : null);
        if (!imageFile) {
            if (typeof showToast === 'function') showToast('Please select or capture a photo proof first! 📸');
            return;
        }

        const itemId = document.getElementById('checkin-item-id').value;
        if (!itemId) return;

        const btn = document.getElementById('btn-verify-gps');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Verifying...';
        btn.disabled = true;

        const token = localStorage.getItem('intan_elyu_token');
        const formData = new FormData();
        formData.append('lat', window.myLat || 16.6159);
        formData.append('lng', window.myLng || 120.3186);
        formData.append('image', imageFile);

        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries/items/' + itemId + '/visit', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + token
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                if (typeof showToast === 'function') showToast(result.message || 'Checked in! Earned +50 XP & Points');
                closeCheckinModal();
                document.getElementById('checkin-prompt-card').style.display = 'none';
                
                const item = window.currentTripItems?.find(i => i.id == itemId);
                const visitedSpotId = result.item?.tourist_spot_id || (item ? item.tourist_spot_id : null);

                loadTripData();
            } else {
                if (typeof showToast === 'function') showToast(result.message || 'Check-in failed.');
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo';
                btn.disabled = false;
            }
        } catch (error) {
            console.error('Check-in error:', error);
            if (typeof showToast === 'function') showToast('Network error. Please try again.');
            btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo';
            btn.disabled = false;
        }
    };

    // Real-time GPS Listener for live navigation mode on Saved Trips
    let _tripGpsTimeout = null;
    document.addEventListener('gpsUpdated', (e) => {
        window.myLat = e.detail.lat;
        window.myLng = e.detail.lng;
        
        if (window.tripGpsMarker) {
            window.tripGpsMarker.setLngLat([window.myLng, window.myLat]);
        }

        // VISITED CHECK-IN PROMPT LOGIC
        if (window.currentTripItems) {
            const activeItem = window.currentTripItems.find(i => !i.is_visited);
            if (activeItem && activeItem.destination) {
                const destLat = parseFloat(activeItem.destination.lat || activeItem.destination.latitude);
                const destLng = parseFloat(activeItem.destination.lng || activeItem.destination.longitude);
                
                if (!isNaN(destLat) && !isNaN(destLng)) {
                    const p = 0.017453292519943295;
                    const c = Math.cos;
                    const a = 0.5 - c((destLat - window.myLat) * p)/2 + c(window.myLat * p) * c(destLat * p) * (1 - c((destLng - window.myLng) * p))/2;
                    const distKm = 12742 * Math.asin(Math.sqrt(a));
                    
                    // If within 300 meters (0.3 km)
                    if (distKm <= 0.3) {
                        window.currentCheckinItemId = activeItem.id;
                        const promptCard = document.getElementById('checkin-prompt-card');
                        const destNameEl = document.getElementById('checkin-prompt-dest-name');
                        if (promptCard && destNameEl) {
                            destNameEl.textContent = activeItem.destination.name;
                            promptCard.style.display = 'flex';
                        }
                    } else {
                        const promptCard = document.getElementById('checkin-prompt-card');
                        if (promptCard) promptCard.style.display = 'none';
                    }
                }
            } else {
                const promptCard = document.getElementById('checkin-prompt-card');
                if (promptCard) promptCard.style.display = 'none';
            }
        }
        
        // Debounce route recalculation to prevent OSRM spam while moving
        clearTimeout(_tripGpsTimeout);
        _tripGpsTimeout = setTimeout(() => {
            if (window.currentTripItems) plotTrip(window.currentTripItems, window.currentRouteType);
        }, 2000);
    });

    window.locateTripUser = async function() {
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

    initTripMap();
})();
</script>
