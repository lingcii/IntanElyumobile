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

<!-- Floating Destination Conveyor Carousel -->
<div id="trip-conveyor-wrapper" style="position: absolute; bottom: max(env(safe-area-inset-bottom), 16px); left: 0; right: 0; z-index: 1000; display: flex; flex-direction: column; align-items: center; gap: 12px; pointer-events: none; width: 100%; box-sizing: border-box; overflow: hidden;">
    
    <!-- Trip Overview Summary Header Pill -->
    <div id="trip-summary-pill" style="align-self: center; background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(63, 125, 183, 0.94) 100%); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 100px; padding: 7px 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 10px 28px rgba(10, 25, 60, 0.35); pointer-events: auto;">
        <div style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800;">
            <i class="fa-solid fa-route" style="color:#67e8f9;"></i> <span id="trip-info-distance">-- km</span>
        </div>
        <div style="width:1px; height:12px; background:rgba(255,255,255,0.25);"></div>
        <div style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800;">
            <i class="fa-solid fa-stopwatch" style="color:#34d399;"></i> <span id="trip-info-time">-- mins</span>
        </div>
        <div style="width:1px; height:12px; background:rgba(255,255,255,0.25);"></div>
        <button id="trip-info-vehicle-btn" type="button" onclick="window.openVehicleSelectorModal()" style="display:flex; align-items:center; gap:6px; color:#ffffff; font-size:12px; font-weight:800; background:rgba(255,255,255,0.18); border:none !important; outline:none !important; border-radius:100px; padding:4px 10px; cursor:pointer; transition:transform 0.15s, background 0.15s;" title="Tap to switch vehicle">
            <i id="trip-info-vehicle-icon" class="fa-solid fa-car" style="color:#f59e0b;"></i>
            <span id="trip-info-vehicle-name">Own Car</span>
            <i class="fa-solid fa-chevron-down" style="font-size:9px; opacity:0.8; margin-left:2px;"></i>
        </button>
    </div>

    <!-- Conveyor Cards Carousel Scroll Container -->
    <div id="conveyor-cards-scroll" style="display: flex; justify-content: flex-start; align-items: stretch; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-padding: 0 16px; padding: 4px 16px 12px 16px; pointer-events: auto; scroll-behavior: smooth; width: 100%; box-sizing: border-box;" class="hide-scrollbar">
        <!-- Injected via JS -->
    </div>
</div>

<!-- Prompt Card when within 300m -->
<div id="checkin-prompt-card" style="position: absolute; top: calc(max(env(safe-area-inset-top), 40px) + 54px); left: 16px; right: 16px; z-index: 1001; background: linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 100%); border: none !important; outline: none !important; border-radius: 20px; padding: 14px 18px; box-shadow: 0 14px 36px rgba(10, 25, 60, 0.45); display: none; align-items: center; justify-content: space-between; backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="width:40px; height:40px; border-radius:12px; background:rgba(255,255,255,0.2); border:none !important; outline:none !important; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-solid fa-location-dot" style="color:#ffffff; font-size:18px;"></i>
        </div>
        <div style="text-align: left;">
            <h5 style="margin:0 0 2px; font-size:14px; font-weight:800; color:#ffffff; letter-spacing:-0.2px;">You've arrived!</h5>
            <p id="checkin-prompt-dest-name" style="margin:0; font-size:12px; color:rgba(255,255,255,0.85); font-weight:600;"></p>
        </div>
    </div>
    <button onclick="window.triggerMapCheckinModal()" style="background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; border:none !important; outline:none !important; padding:9px 18px; border-radius:12px; font-weight:800; font-size:12px; cursor:pointer; box-shadow:0 4px 14px rgba(2,132,199,0.4); display:flex; align-items:center; gap:6px; flex-shrink:0; transition:transform 0.15s ease;">
        <i class="fa-solid fa-camera" style="font-size:11px;"></i> Check In
    </button>
</div>

<!-- Check-in Verification Modal (GPS and Photo Proof) -->
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:99999; justify-content:center; align-items:center;">
    <div style="background:linear-gradient(145deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 100%); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:none !important; outline:none !important; border-radius:24px; padding:28px 24px; width:90%; max-width:380px; box-shadow:0 24px 60px rgba(10,25,60,0.55); text-align:center;">
        <div style="width:58px; height:58px; border-radius:50%; background:rgba(255,255,255,0.2); border:none !important; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
            <i class="fa-solid fa-camera" style="font-size:26px; color:#ffffff;"></i>
        </div>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Claim Your Reward</h3>
        <p style="font-size:13px; color:rgba(255, 255, 255, 0.85); margin-bottom:20px; line-height:1.5;">Take a selfie or capture a photo at this destination to verify your visit and earn <strong style="color:#67e8f9; font-weight:800;">+50 XP</strong> & <strong style="color:#67e8f9; font-weight:800;">+50 Points</strong>.</p>

        <input type="hidden" id="checkin-item-id">
        
        <!-- Step 1: Photo Proof -->
        <div style="margin-bottom: 16px; text-align: left;">
            <label style="font-size:11px; font-weight:800; color:#67e8f9; margin-bottom:6px; display:block; text-transform:uppercase; letter-spacing:0.5px;">Step 1: Photo Proof (Required)</label>
            <input type="file" id="checkin-proof-image" accept="image/*" style="display:none;" onchange="window.handlePhotoSelected(this)">
            <button type="button" onclick="window.openCheckinImagePickerModal()" id="btn-select-photo" style="width:100%; padding:14px; background:rgba(255,255,255,0.15); border:none !important; outline:none !important; border-radius:14px; color:#ffffff; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer; transition:all 0.2s ease;">
                <i class="fa-solid fa-camera" style="font-size:16px;"></i> <span id="photo-status-text">Take or Choose Photo</span>
            </button>

            <!-- Picture Preview Container (Displays actual picture preview instead of filename string) -->
            <div id="checkin-photo-preview-container" style="display:none; margin-top:12px; position:relative; border-radius:16px; overflow:hidden; border:none !important; outline:none !important; background:rgba(10,25,60,0.8); box-shadow:0 8px 24px rgba(0,0,0,0.3);">
                <img id="checkin-photo-preview-img" src="" alt="Proof Preview" style="width:100%; max-height:180px; object-fit:cover; display:block;">
                <div style="position:absolute; top:8px; right:8px; display:flex; gap:6px;">
                    <button type="button" onclick="window.openCheckinImagePickerModal()" title="Change Picture" style="background:rgba(10,25,60,0.85); color:#ffffff; border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; backdrop-filter:blur(6px);">
                        <i class="fa-solid fa-arrows-rotate" style="font-size:13px;"></i>
                    </button>
                    <button type="button" onclick="window.removeCheckinPhoto()" title="Remove Picture" style="background:rgba(239,68,68,0.9); color:#ffffff; border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; backdrop-filter:blur(6px);">
                        <i class="fa-solid fa-xmark" style="font-size:14px;"></i>
                    </button>
                </div>
                <div style="padding:6px 10px; background:rgba(10,25,60,0.9); font-size:10px; font-weight:700; color:#67e8f9; text-transform:uppercase; text-align:center; border:none !important;">
                    <i class="fa-solid fa-circle-check" style="margin-right:4px; color:#34c759;"></i> Picture Proof Attached
                </div>
            </div>
        </div>

        <!-- Step 2: Location Verification -->
        <div style="margin-bottom: 12px; text-align: left;">
            <label style="font-size:11px; font-weight:800; color:#67e8f9; margin-bottom:6px; display:block; text-transform:uppercase; letter-spacing:0.5px;">Step 2: Location Check-in</label>
            <button class="btn-primary" id="btn-verify-gps" style="width:100%; padding:14px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; color:#ffffff; border-radius:14px; box-shadow:0 4px 16px rgba(2,132,199,0.4); cursor:pointer;" onclick="verifyGpsCheckIn()">
                <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Submit
            </button>
        </div>

        <button style="width:100%; padding:12px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.12); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer;" onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<!-- Check-in Image Picker Choice Modal -->
<div id="checkin-image-picker-modal" onclick="if(event.target===this) window.closeCheckinImagePickerModal()" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(6,11,25,0.75); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:999999; align-items:flex-end; justify-content:center; padding:0; margin:0; box-sizing:border-box;">
    <div style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:28px 28px 0 0; width:100%; max-width:500px; padding:26px 22px; box-shadow:0 -10px 45px rgba(10,25,60,0.6); animation:slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); box-sizing:border-box;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:17px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-camera" style="color:#67e8f9; font-size:18px;"></i> Attach Proof Photo
            </h3>
            <button type="button" onclick="window.closeCheckinImagePickerModal()" style="background:rgba(255,255,255,0.15); border:none; color:#ffffff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <button type="button" onclick="window.selectCheckinImageSource('camera')" style="width:100%; padding:15px; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; border-radius:18px; color:#ffffff; font-size:14px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; box-shadow:0 4px 14px rgba(2,132,199,0.35); transition:transform 0.15s ease;">
                <i class="fa-solid fa-camera" style="font-size:17px;"></i> Take Photo with Camera
            </button>
            <button type="button" onclick="window.selectCheckinImageSource('gallery')" style="width:100%; padding:15px; background:rgba(255,255,255,0.15); border:none !important; outline:none !important; border-radius:18px; color:#ffffff; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, background 0.15s ease;">
                <i class="fa-solid fa-images" style="font-size:17px; color:#67e8f9;"></i> Choose from Photo Gallery
            </button>
            <button type="button" onclick="window.closeCheckinImagePickerModal()" style="width:100%; padding:12px; background:transparent; border:none; color:rgba(255,255,255,0.8); font-size:13px; font-weight:600; cursor:pointer; margin-top:4px;">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Vehicle Selector Bottom Sheet Modal -->
<div id="vehicle-selector-modal" onclick="if(event.target===this) window.closeVehicleSelectorModal()" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(6,11,25,0.75); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:999999; align-items:flex-end; justify-content:center; padding:0; margin:0; box-sizing:border-box;">
    <div style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:28px 28px 0 0; width:100%; max-width:500px; padding:24px 20px calc(24px + env(safe-area-inset-bottom)) 20px; box-shadow:0 -10px 45px rgba(10,25,60,0.6); animation:slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); box-sizing:border-box; max-height:85vh; overflow-y:auto;" class="hide-scrollbar">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-van-shuttle" style="color:#67e8f9; font-size:17px;"></i> Select Vehicle Mode
                </h3>
                <p style="margin:3px 0 0 0; font-size:12px; color:rgba(255,255,255,0.8); font-weight:600;">Choose how you are traveling on this route</p>
            </div>
            <button type="button" onclick="window.closeVehicleSelectorModal()" style="background:rgba(255,255,255,0.15); border:none; color:#ffffff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>
        <div id="vehicle-options-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:10px; margin-bottom:12px;">
            <!-- Populated via JS -->
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
                    "tiles": [
                        "https://a.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png",
                        "https://b.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png"
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
        { key: 'jeepney', name: 'Jeepney', icon: 'fa-van-shuttle', color: '#38bdf8', desc: 'Traditional jeepney', speedKmH: 24 },
        { key: 'tricycle', name: 'Tricycle', icon: 'fa-motorcycle', color: '#10b981', desc: 'Local tricycle', speedKmH: 20 },
        { key: 'bus', name: 'Bus', icon: 'fa-bus', color: '#a855f7', desc: 'Provincial commuter bus', speedKmH: 32 },
        { key: 'mini_bus', name: 'Mini Bus', icon: 'fa-bus-simple', color: '#06b6d4', desc: 'UV Express / Van', speedKmH: 30 },
        { key: 'lutrampco', name: 'LUTRAMPCO', icon: 'fa-van-shuttle', color: '#38bdf8', desc: 'Modernized Jeepney', speedKmH: 26 },
        { key: 'taxi', name: 'Taxi', icon: 'fa-taxi', color: '#eab308', desc: 'Metered or chartered taxi', speedKmH: 36 },
        { key: 'motorcycle', name: 'Motorcycle', icon: 'fa-motorcycle', color: '#f97316', desc: 'Motorbike / scooter', speedKmH: 36 },
        { key: 'walking', name: 'Walking', icon: 'fa-person-walking', color: '#22c55e', desc: 'On-foot navigation', speedKmH: 4.5 }
    ];

    function resolveTripVehicle(trip, tripId) {
        const sessionVal = sessionStorage.getItem('active_trip_transport_' + tripId);
        const localVal = localStorage.getItem('selected_trip_vehicle_' + tripId);
        let raw = localVal || sessionVal || (trip ? trip.transport_mode : null);

        if (!raw && trip && trip.items && trip.items.length > 0) {
            for (let item of trip.items) {
                if (item.transport_type) { raw = item.transport_type; break; }
                if (item.destination && item.destination.transport_type) { raw = item.destination.transport_type; break; }
            }
        }

        if (!raw) {
            raw = 'own_car';
        }

        const firstRawKey = raw.split(',')[0].trim().toLowerCase();
        let found = VEHICLE_CATALOG.find(v => v.key === firstRawKey || v.key === firstRawKey.replace(/[-_ ]/g, '_')) ||
                    VEHICLE_CATALOG.find(v => firstRawKey.includes(v.key)) ||
                    (firstRawKey.includes('car') ? VEHICLE_CATALOG[0] : null) ||
                    (firstRawKey.includes('jeep') ? VEHICLE_CATALOG[1] : null) ||
                    (firstRawKey.includes('tri') ? VEHICLE_CATALOG[2] : null) ||
                    (firstRawKey.includes('bus') ? VEHICLE_CATALOG[3] : null) ||
                    (firstRawKey.includes('motor') ? VEHICLE_CATALOG[7] : null) ||
                    VEHICLE_CATALOG[0];

        let displayName = found.name;
        if (raw.includes(',')) {
            const parts = raw.split(',').map(s => s.trim().toLowerCase());
            const mapped = parts.map(p => {
                const m = VEHICLE_CATALOG.find(v => v.key === p || p.includes(v.key));
                return m ? m.name : p;
            });
            displayName = mapped.join(' & ');
        }

        return {
            key: found.key,
            name: displayName,
            icon: found.icon,
            color: found.color,
            speedKmH: found.speedKmH
        };
    }

    function applyVehicleToUI(vehicle) {
        window.currentActiveVehicle = vehicle;
        const vehicleNameEl = document.getElementById('trip-info-vehicle-name');
        const vehicleIconEl = document.getElementById('trip-info-vehicle-icon');
        if (vehicleNameEl) vehicleNameEl.textContent = vehicle.name;
        if (vehicleIconEl) {
            vehicleIconEl.className = 'fa-solid ' + vehicle.icon;
            vehicleIconEl.style.color = vehicle.color || '#f59e0b';
        }
    }

    window.openVehicleSelectorModal = function() {
        const modal = document.getElementById('vehicle-selector-modal');
        const grid = document.getElementById('vehicle-options-grid');
        if (!modal || !grid) return;

        const currentKey = window.currentActiveVehicle?.key || 'own_car';
        grid.innerHTML = VEHICLE_CATALOG.map(v => {
            const isSel = v.key === currentKey;
            return `
                <div onclick="window.selectTripVehicle('${v.key}')" style="background:${isSel ? 'rgba(255,255,255,0.28)' : 'rgba(255,255,255,0.12)'}; border:none !important; outline:none !important; border-radius:16px; padding:12px 14px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:transform 0.15s, background 0.15s; ${isSel ? 'box-shadow:0 4px 14px rgba(0,0,0,0.25);' : ''}">
                    <div style="width:36px; height:36px; border-radius:10px; background:${isSel ? 'linear-gradient(135deg, #00f2fe 0%, #0284c7 100%)' : 'rgba(255,255,255,0.15)'}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid ${v.icon}" style="color:${isSel ? '#ffffff' : (v.color || '#ffffff')}; font-size:16px;"></i>
                    </div>
                    <div style="overflow:hidden; text-align:left;">
                        <div style="font-size:13px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v.name}</div>
                        <div style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.75); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v.desc}</div>
                    </div>
                    ${isSel ? '<i class="fa-solid fa-circle-check" style="margin-left:auto; color:#ffffff; font-size:14px;"></i>' : ''}
                </div>
            `;
        }).join('');

        modal.style.display = 'flex';
    };

    window.closeVehicleSelectorModal = function() {
        const modal = document.getElementById('vehicle-selector-modal');
        if (modal) modal.style.display = 'none';
    };

    window.selectTripVehicle = function(vehicleKey) {
        const v = VEHICLE_CATALOG.find(item => item.key === vehicleKey) || VEHICLE_CATALOG[0];
        applyVehicleToUI(v);
        window.closeVehicleSelectorModal();

        const tripId = window.currentTripId;
        if (tripId) {
            localStorage.setItem('selected_trip_vehicle_' + tripId, v.key);
            sessionStorage.setItem('active_trip_transport_' + tripId, v.key);

            // Background update backend itinerary
            const token = localStorage.getItem('intan_elyu_token');
            if (token) {
                fetch(backendUrl + '/api/tourist/itineraries/' + tripId, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ transport_mode: v.key })
                }).catch(e => console.warn("Async vehicle sync:", e));
            }
        }

        if (typeof showToast === 'function') {
            showToast(`Vehicle mode set to ${v.name}`);
        }

        // Force route recalculation with new vehicle
        window._lastRouteLat = null;
        window._lastRouteLng = null;
        if (window.currentTripItems) {
            plotTrip(window.currentTripItems, window.currentRouteType);
        }
    };

    function calcCoordDistMeters(lat1, lon1, lat2, lon2) {
        if (!lat1 || !lon1 || !lat2 || !lon2) return 999999;
        const p = 0.017453292519943295;
        const c = Math.cos;
        const a = 0.5 - c((lat2 - lat1) * p)/2 + c(lat1 * p) * c(lat2 * p) * (1 - c((lon2 - lon1) * p))/2;
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
                const isVisited = Boolean(item.is_visited || item.proof_status === 'approved' || item.proof_image);
                const isActive = idx === activeIndex;
                if (isActive) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
                const badgeEl = card.querySelector('.conveyor-status-badge');
                if (badgeEl) {
                    if (isVisited) {
                        badgeEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Visited';
                        badgeEl.style.background = 'rgba(52, 199, 89, 0.25)';
                    } else if (isActive) {
                        badgeEl.innerHTML = `Stop ${idx + 1} of ${items.length} • NEXT`;
                        badgeEl.style.background = 'rgba(255, 255, 255, 0.25)';
                    } else {
                        badgeEl.innerHTML = `Stop ${idx + 1} of ${items.length}`;
                        badgeEl.style.background = 'rgba(255, 255, 255, 0.15)';
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
            const isVisited = Boolean(item.is_visited || item.proof_status === 'approved' || item.proof_image);
            const isActive = idx === activeIndex;

            let badgeHtml = '';
            if (isVisited) {
                badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(52,199,89,0.25); border:none !important; outline:none !important; color:#ffffff; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;"><i class="fa-solid fa-circle-check"></i> Visited</span>`;
            } else if (isActive) {
                badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(255,255,255,0.25); border:none !important; outline:none !important; color:#ffffff; padding:4px 12px; border-radius:100px; font-size:10px; font-weight:800; flex-shrink:0;">Stop ${idx + 1} of ${items.length} • NEXT</span>`;
            } else {
                badgeHtml = `<span class="conveyor-status-badge" style="background:rgba(255,255,255,0.15); border:none !important; outline:none !important; color:rgba(255,255,255,0.85); padding:4px 12px; border-radius:100px; font-size:10px; font-weight:700; flex-shrink:0;">Stop ${idx + 1} of ${items.length}</span>`;
            }

            let proofThumbnail = '';
            if (item.proof_image) {
                let pUrl = item.proof_image;
                if (!pUrl.startsWith('http') && !pUrl.startsWith('data:') && !pUrl.startsWith('blob:')) {
                    let b = (window.backendUrl || '').replace(/\/+$/, '');
                    pUrl = b + '/' + pUrl.replace(/^\//, '');
                }
                let fallbackUrl = (window.backendUrl || '').replace(/\/+$/, '') + '/api/image/' + item.proof_image.replace(/^\//, '');
                proofThumbnail = `<img src="${pUrl}" onerror="if(this.src!=='${fallbackUrl}'){this.src='${fallbackUrl}';}" alt="Proof" style="width:40px; height:40px; border-radius:8px; object-fit:cover; border:none !important; box-shadow:0 2px 8px rgba(0,0,0,0.3); flex-shrink:0;">`;
            }

            let actionBtnHtml = '';
            if (isVisited || item.proof_status === 'approved') {
                actionBtnHtml = `<div style="display:flex; align-items:center; gap:10px;">
                    ${proofThumbnail}
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <span style="background:rgba(52,199,89,0.25); border:none !important; outline:none !important; color:#ffffff; font-weight:800; font-size:11px; padding:3px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Visited & Verified</span>
                        <button type="button" onclick="event.stopPropagation(); window.openWriteTestimonyModal('${item.tourist_spot_id || (item.destination ? item.destination.id : '')}')" style="background:rgba(255,255,255,0.18); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:100px; cursor:pointer; width:fit-content; margin-top:2px;">
                            <i class="fa-solid fa-pen" style="margin-right:4px;"></i> Review Site
                        </button>
                    </div>
                </div>`;
            } else if (item.proof_status === 'rejected') {
                actionBtnHtml = `<div style="display:flex; align-items:center; gap:8px;">
                    ${proofThumbnail}
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <span style="background:rgba(239,68,68,0.25); border:none !important; outline:none !important; color:#ffffff; font-weight:800; font-size:11px; padding:3px 8px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                        <button onclick="event.stopPropagation(); window.currentCheckinItemId='${item.id}'; window.triggerMapCheckinModal()" style="background:linear-gradient(135deg, #ef4444, #dc2626); color:#ffffff; border:none !important; outline:none !important; padding:6px 10px; border-radius:100px; font-weight:800; font-size:10px; cursor:pointer;"><i class="fa-solid fa-camera" style="margin-right:4px;"></i> Re-upload</button>
                    </div>
                </div>`;
            } else if (item.proof_image && (item.proof_status === 'pending' || !item.proof_status)) {
                actionBtnHtml = `<div style="display:flex; align-items:center; gap:8px;">
                    ${proofThumbnail}
                    <span style="background:rgba(255,149,0,0.25); border:none !important; outline:none !important; color:#ffffff; font-weight:800; font-size:11px; padding:4px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-clock"></i> Pending Validation</span>
                </div>`;
            } else if (isActive) {
                actionBtnHtml = `<button onclick="event.stopPropagation(); window.currentCheckinItemId='${item.id}'; window.triggerMapCheckinModal()" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; border:none !important; outline:none !important; padding:10px 16px; border-radius:100px; font-weight:800; font-size:12px; box-shadow:0 4px 14px rgba(2,132,199,0.4); cursor:pointer;"><i class="fa-solid fa-location-crosshairs" style="margin-right:4px;"></i> Check In (+50 XP)</button>`;
            } else {
                actionBtnHtml = `<span style="color:rgba(255,255,255,0.6); font-size:12px; font-weight:700;"><i class="fa-solid fa-lock"></i> Locked</span>`;
            }

            const classBadge = dest.classification_status ? `<span style="padding: 3px 8px; border-radius: 100px; font-size: 8px; font-weight: 800; text-transform: uppercase; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#10b981' : (dest.classification_status === 'EMERGE' ? '#0284c7' : '#f59e0b')}; border:none !important; outline:none !important; flex-shrink:0;">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : '';

            conveyorHtml += `
            <div id="conveyor-card-${idx}" class="conveyor-card ${isActive ? 'active' : ''}" onclick="window.flyToConveyorSpot(${lng}, ${lat}, ${idx})" style="scroll-snap-align: center; flex: 0 0 calc(100vw - 64px); max-width: 320px; min-width: 250px; box-sizing: border-box; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 24px; padding: 16px 18px; box-shadow: 0 12px 32px rgba(10, 25, 60, 0.35); cursor: pointer; transition: transform 0.25s ease;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; gap:8px;">
                    ${badgeHtml}
                    ${classBadge}
                </div>
                <h4 style="margin:0 0 4px 0; font-size:16px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${dest.name}">${dest.name}</h4>
                <p style="margin:0 0 12px 0; font-size:12px; color:rgba(255,255,255,0.85); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fa-solid fa-location-dot" style="color:#67e8f9; margin-right:5px;"></i>${dest.municipality || 'La Union'}</p>
                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                    <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.75); flex-shrink:0;"><i class="fa-solid fa-compass" style="color:#67e8f9;"></i> Tap to view</span>
                    <div style="flex-shrink:0;">${actionBtnHtml}</div>
                </div>
            </div>`;
        });

        conveyorScroll.innerHTML = conveyorHtml;
        setTimeout(() => {
            const targetCard = document.querySelector('.conveyor-card.active') || document.getElementById('conveyor-card-0');
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }, 120);
    }

    function loadTripData() {
        const urlParams = new URLSearchParams(window.location.search);
        const tripId = urlParams.get('trip_id');
        window.currentTripId = tripId;
        
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
                }
            }
        })
        .catch(e => console.error("Failed to load trip", e));
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

        const activeIndex = items.findIndex(i => !(i.is_visited || i.proof_status === 'approved' || i.proof_image));

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
                    const isVisited = Boolean(item.is_visited || item.proof_status === 'approved' || item.proof_image);

                    if (isVisited) {
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

        // Dynamically connect the physical GPS location as the starting point of the route
        if (window.myLat && window.myLng) {
            coords.unshift(`${window.myLng},${window.myLat}`);
            markerPoints.unshift([window.myLng, window.myLat]);
            bounds.extend([window.myLng, window.myLat]);
            
            const gpsEl = document.createElement('div');
            gpsEl.innerHTML = `
                <div style="width: 32px; height: 32px; background-color: #FFFFFF; border: 2.5px solid #0284c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0284c7; box-shadow: 0 4px 12px rgba(2,132,199,0.35);">
                    <i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>
                </div>
            `;
            window.tripGpsMarker = new maplibregl.Marker({ element: gpsEl, anchor: 'center' })
                .setLngLat([window.myLng, window.myLat])
                .addTo(tripMap);
        }

        if (markerPoints.length > 0) {
            if (coords.length > 1) {
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
            if (typeof showToast === 'function') showToast('Please select or capture a photo proof first.');
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
                closeCheckinModal();
                if (typeof showToast === 'function') showToast(result.message || 'Photo proof submitted! Pending verification before completion.');
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
    window._tripGpsTimeout = window._tripGpsTimeout || null;
    window._lastRouteLat = window._lastRouteLat || null;
    window._lastRouteLng = window._lastRouteLng || null;

    document.addEventListener('gpsUpdated', (e) => {
        window.myLat = e.detail.lat;
        window.myLng = e.detail.lng;
        
        if (window.tripGpsMarker) {
            window.tripGpsMarker.setLngLat([window.myLng, window.myLat]);
        }

        // VISITED CHECK-IN PROMPT LOGIC
        if (window.currentTripItems) {
            const activeItem = window.currentTripItems.find(i => !(i.is_visited || i.proof_status === 'approved' || i.proof_image));
            if (activeItem && activeItem.destination) {
                const destLat = parseFloat(activeItem.destination.lat || activeItem.destination.latitude);
                const destLng = parseFloat(activeItem.destination.lng || activeItem.destination.longitude);
                
                if (!isNaN(destLat) && !isNaN(destLng)) {
                    const distMeters = calcCoordDistMeters(destLat, destLng, window.myLat, window.myLng);
                    
                    // If within 300 meters
                    if (distMeters <= 300) {
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
