<style>
    .maplibregl-ctrl-bottom-right {
        bottom: 230px !important;
        right: 16px !important;
        z-index: 999;
    }
</style>
<?php
$pageTitle = "Trip Route";
$backRoute = "saved_trips";
require_once __DIR__ . '/../components/header.php';
?>

<div id="trip-map" style="width: 100%; height: 100vh; background: #F2F2F7;"></div>

<div id="trip-info-card" style="position: absolute; bottom: 16px; left: 16px; right: 16px; z-index: 1000; background: radial-gradient(circle at 50% 30%, rgba(45, 100, 170, 0.95) 0%, rgba(20, 40, 90, 0.96) 50%, rgba(15, 23, 42, 0.98) 100%); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 28px; padding: 20px; box-shadow: 0 -4px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(56, 189, 248, 0.06), inset 0 1px 0 rgba(255,255,255,0.06); display: none;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
        <h3 id="trip-info-name" style="margin: 0; font-size: 18px; font-weight: 700; color: white;"></h3>
        <span id="trip-info-route-type" style="background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 8px; text-transform: uppercase;"></span>
    </div>
    <p id="trip-info-desc" style="margin: 0 0 16px; font-size: 13px; color: rgba(148,163,184,0.9); line-height: 1.4;"></p>
    <div style="display: flex; gap: 10px;">
        <div style="flex: 1; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px; text-align: center;">
            <i class="fa-solid fa-route" style="color: #38bdf8; font-size: 16px; margin-bottom: 4px;"></i>
            <div id="trip-info-distance" style="font-size: 14px; font-weight: 700; color: white;">-- km</div>
        </div>
        <div style="flex: 1; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px; text-align: center;">
            <i class="fa-solid fa-stopwatch" style="color: #34d399; font-size: 16px; margin-bottom: 4px;"></i>
            <div id="trip-info-time" style="font-size: 14px; font-weight: 700; color: white;">-- mins</div>
        </div>
        <div style="flex: 1; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px; text-align: center;">
            <i id="trip-info-vehicle-icon" class="fa-solid fa-car" style="color: #f59e0b; font-size: 16px; margin-bottom: 4px;"></i>
            <div id="trip-info-vehicle-name" style="font-size: 14px; font-weight: 700; color: white;">N/A</div>
        </div>
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
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.65); z-index:1002; justify-content:center; align-items:center;">
    <div style="background:rgba(15,23,42,0.95); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,0.1); border-radius:24px; padding:28px 24px; width:90%; max-width:380px; box-shadow:0 20px 40px rgba(0,0,0,0.3); text-align:center;">
        <div style="font-size:48px; margin-bottom:12px;">📸</div>
        <h3 style="margin:0 0 8px; color:#fff;">Verify Visit</h3>
        <p style="font-size:13px; color:rgba(255,255,255,0.6); margin-bottom:20px; line-height:1.5;">Take a selfie or capture a photo at this destination to verify your visit and earn <strong>+50 XP</strong> & <strong>+50 Points</strong>.</p>

        <input type="hidden" id="checkin-item-id">
        
        <!-- File Input for Image Proof -->
        <div style="margin-bottom: 20px; text-align: left;">
            <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.75); margin-bottom:6px; display:block; text-transform:uppercase;">Photo Proof (Required):</label>
            <input type="file" id="checkin-proof-image" accept="image/*" capture="environment" style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:10px; color:#fff; font-size:12px;">
        </div>

        <button class="btn-primary" id="btn-verify-gps" style="width:100%; padding:16px; margin-bottom:12px; font-size:15px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; border-radius:14px; font-weight:800; cursor:pointer;" onclick="verifyGpsCheckIn()">
            <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo
        </button>

        <button style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:transparent; color:rgba(255,255,255,0.6); font-size:13px; font-weight:600; cursor:pointer;" onclick="closeCheckinModal()">Cancel</button>
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
    var backendUrl = window.backendUrl || 'https://intanelyu-production.up.railway.app';
    var tripMap;

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
            attributionControl: false
        });

        tripMap.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'bottom-right');

        tripMap.on('load', async () => {
            tripMap.setTerrain({ "source": "terrain", "exaggeration": 1.5 });

            tripMap.setTerrain({ "source": "terrain", "exaggeration": 1.5 });
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
                    document.getElementById('trip-info-name').textContent = trip.title;
                    
                    if (trip.items && trip.items.length > 0) {
                        document.getElementById('trip-info-desc').textContent = `Route preview for ${trip.items.length} destination(s).`;
                        document.getElementById('trip-info-route-type').textContent = trip.route_type || 'Recommended';
                        
                        const tMap = {
                            'own_car': { name: 'Own Car', icon: 'fa-car' },
                            'taxi': { name: 'Taxi', icon: 'fa-taxi' },
                            'jeepney': { name: 'Jeepney', icon: 'fa-van-shuttle' },
                            'private_bus': { name: 'Private Bus', icon: 'fa-bus' },
                            'mini_bus': { name: 'Mini Bus', icon: 'fa-bus-simple' },
                            'lutrampco': { name: 'LUTRAMPCO', icon: 'fa-van-shuttle' }
                        };
                        const trans = tMap[trip.transport_mode];
                        if (trans) {
                            document.getElementById('trip-info-vehicle-name').textContent = trans.name;
                            document.getElementById('trip-info-vehicle-icon').className = 'fa-solid ' + trans.icon;
                        } else {
                            // Fallback for legacy trips saved before the transport_mode database column was added
                            document.getElementById('trip-info-vehicle-name').textContent = 'Own Car';
                            document.getElementById('trip-info-vehicle-icon').className = 'fa-solid fa-car';
                        }
                        
                        document.getElementById('trip-info-card').style.display = 'block';
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
                            <div style="background: #10b981; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; border: 3px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        `;
                        labelHtml = `
                            <div style="background: rgba(16,185,129,0.8); color: white; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; white-space: nowrap; margin-top: 4px; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 2px 5px rgba(0,0,0,0.5); text-align: center; text-decoration: line-through;">
                                ${dest.name}
                            </div>
                        `;
                    } else if (idx === activeIndex) {
                        // ACTIVE - Glowing Blue Number
                        iconHtml = `
                            <div style="background: #38bdf8; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; border: 3px solid #ffffff; box-shadow: 0 0 15px rgba(56,189,248,0.8), 0 4px 10px rgba(0,0,0,0.5); animation: pulse 2s infinite;">
                                ${idx + 1}
                            </div>
                        `;
                        labelHtml = `
                            <div style="background: #0f172a; color: #38bdf8; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; white-space: nowrap; margin-top: 4px; border: 1px solid #38bdf8; box-shadow: 0 2px 5px rgba(0,0,0,0.5); text-align: center;">
                                Next: ${dest.name}
                            </div>
                        `;
                    } else {
                        // LOCKED - Grey Padlock
                        iconHtml = `
                            <div style="background: #94a3b8; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; border: 3px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.3); opacity: 0.8;">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                        `;
                        labelHtml = `
                            <div style="background: rgba(148,163,184,0.8); color: white; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; white-space: nowrap; margin-top: 4px; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 2px 5px rgba(0,0,0,0.3); text-align: center; opacity: 0.8;">
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
                        
                        document.getElementById('trip-info-distance').textContent = distanceKm.toFixed(1) + ' km';
                        document.getElementById('trip-info-time').textContent = Math.round(durationMin) + ' mins';

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
                    if (distEl) {
                        distEl.textContent = "N/A";
                        document.getElementById('trip-info-time').textContent = "N/A";
                    }
                });
            } else {
                const distEl = document.getElementById('trip-info-distance');
                if (distEl) {
                    distEl.textContent = "N/A";
                    document.getElementById('trip-info-time').textContent = "N/A";
                }
            }
        }
    }

    // Map Checkin Modal functions
    window.triggerMapCheckinModal = function() {
        if (!window.currentCheckinItemId) return;
        document.getElementById('checkin-item-id').value = window.currentCheckinItemId;
        document.getElementById('checkin-modal').style.display = 'flex';
    };

    window.closeCheckinModal = function() {
        document.getElementById('checkin-modal').style.display = 'none';
        document.getElementById('checkin-item-id').value = '';
        const imgInput = document.getElementById('checkin-proof-image');
        if (imgInput) imgInput.value = '';
        const btn = document.getElementById('btn-verify-gps');
        if (btn) { btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo'; btn.disabled = false; }
    };

    window.verifyGpsCheckIn = async function() {
        const imageFile = document.getElementById('checkin-proof-image').files[0];
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
                if (typeof showToast === 'function') showToast(result.message || 'Checked in! 🌟');
                closeCheckinModal();
                document.getElementById('checkin-prompt-card').style.display = 'none';
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

    initTripMap();
})();
</script>
