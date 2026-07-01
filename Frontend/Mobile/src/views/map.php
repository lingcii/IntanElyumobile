<!-- Map View -->
<?php
$pageTitle = 'Explore Map';
$activeTab = 'map';
?>





<div class="map-container animate-fade-in">
    <!-- Map Container -->
    <div id="tourist-map"></div>

    <!-- Floating Search & Filters -->
    <div class="map-floating-header stagger-1">
        <div class="map-search">
            <i class="fa-solid fa-location-arrow"></i>
            <input type="text" id="map-search-input" placeholder="Search places on map...">
        </div>
        <!-- Categories Container -->
        <div class="map-categories" id="map-categories-container">
            <!-- Dynamically populated -->
        </div>
    </div>

    <!-- Locate Me Button -->
    <div class="btn-locate-me animate-slide-up" id="btn-locate-me">
        <i class="fa-solid fa-crosshairs"></i>
    </div>

        <!-- Layer Toggle Button -->
    <div class="btn-layer-toggle animate-slide-up" id="btn-layer-toggle" style="position: absolute; bottom: calc(340px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: var(--bg-secondary); border: 1px solid var(--glass-border); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1000; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-layer-group"></i>
    </div>

    <!-- 3D Mode Button -->
    <div class="btn-3d-view animate-slide-up" id="btn-3d-view" style="position: absolute; bottom: calc(280px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: var(--bg-secondary); border: 1px solid var(--glass-border); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1000; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-cube"></i>
    </div>

    <!-- Bottom Sheet (hidden by default) -->
    <div class="bottom-sheet" id="place-details-sheet">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="flex:1;">
                <h3 class="sheet-title" id="sheet-title">Destination Name</h3>
                <p class="sheet-location"><i class="fa-solid fa-location-dot"></i> <span id="sheet-location">Location details</span></p>
            </div>
            <button class="btn-close" onclick="window.closeSheet()" style="background:rgba(0,0,0,0.05); border:none; width:30px; height:30px; border-radius:15px; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <img src="" alt="Place Image" class="sheet-img" id="sheet-img">
        
        <!-- Tourist Guide Details -->
        <div id="tourist-guide-details" style="display:none; flex-direction:column; gap:8px; margin-bottom:16px; font-size:13px;">
            <h4 style="margin:4px 0; font-size:14px; color:#8E8E93; text-transform:uppercase;">Tourist Guide Details</h4>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--glass-border);">
                <span><i class="fa-solid fa-car" style="color:var(--primary-color); width:20px;"></i> How to go there</span>
                <strong id="sheet-how-to-go">...</strong>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--glass-border);">
                <span><i class="fa-solid fa-map-location-dot" style="color:var(--primary-color); width:20px;"></i> Distance</span>
                <strong id="sheet-distance">Calculating...</strong>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--glass-border);">
                <span><i class="fa-solid fa-van-shuttle" style="color:var(--primary-color); width:20px;"></i> Avg. Transport</span>
                <strong id="sheet-transport">₱0</strong>
            </div>
            <div id="sheet-desc-container" style="margin-top:8px;">
                <h5 style="margin:0 0 4px 0; font-size:13px; color:var(--text-dark);">About this location</h5>
                <p id="sheet-desc" style="color:var(--text-muted); line-height:1.4; margin:0;"></p>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top: 10px;">
            <button class="btn-primary" style="flex:1; padding: 12px; font-size:14px;" onclick="window.addToItinerary()">Add to Itinerary</button>
            <button id="btn-show-route" style="flex:1; padding: 12px; font-size:14px; background:transparent; color:var(--primary-color); border:2px solid var(--primary-color); border-radius:12px; font-weight:700;">Show Route</button>
        </div>
    </div>

    <!-- Route Details & Fares Sheet -->
    <div class="bottom-sheet" id="route-details-sheet" style="padding-bottom: 30px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="flex:1;">
                <h3 class="sheet-title">Route & Fares</h3>
                <p class="sheet-location" style="margin-top:4px;"><i class="fa-solid fa-route" style="color:var(--primary-color);"></i> <span id="route-distance" style="font-weight:700;">0 km</span> &nbsp;|&nbsp; <i class="fa-regular fa-clock" style="color:var(--secondary-color);"></i> <span id="route-time" style="font-weight:700;">0 min</span></p>
            </div>
            <button class="btn-close" onclick="document.getElementById('route-details-sheet').classList.remove('active')" style="background:var(--glass-bg); border:none; width:30px; height:30px; border-radius:15px; display:flex; align-items:center; justify-content:center; color:var(--text-primary);"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <h4 style="margin: 16px 0 10px 0; font-size: 14px; text-transform:uppercase; color:#8E8E93; letter-spacing:0.5px;">Estimated Transport Options</h4>
        <div style="display:flex; flex-direction:column; gap:10px; max-height:45vh; overflow-y:auto; padding-bottom:20px; -webkit-overflow-scrolling: touch;" id="fare-list">
            <!-- Dynamic fares injected here -->
        </div>
    </div>

    <!-- Selected Vehicle Sheet -->
    <div class="bottom-sheet" id="selected-vehicle-sheet" style="padding-bottom: 30px; z-index: 1005;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="flex:1;">
                <h3 class="sheet-title">Selected Transport</h3>
            </div>
            <button class="btn-close" onclick="document.getElementById('selected-vehicle-sheet').classList.remove('active')" style="background:var(--glass-bg); border:none; width:30px; height:30px; border-radius:15px; display:flex; align-items:center; justify-content:center; color:var(--text-primary);"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div style="display:flex; flex-direction:column; align-items:center; margin-top:20px; padding-bottom: 10px;">
            <div style="width:80px; height:80px; border-radius:24px; display:flex; align-items:center; justify-content:center; font-size:40px; margin-bottom:16px; background:var(--glass-bg);">
                <i id="vehicle-icon" class="fa-solid fa-car"></i>
            </div>
            <h2 id="vehicle-name" style="margin:0 0 8px 0; font-size:24px; font-weight:800; color:var(--text-dark);">Vehicle</h2>
            <p id="vehicle-desc" style="color:var(--text-muted); font-size:14px; text-align:center; margin:0 0 16px 0;">Description</p>
            <div style="background:var(--glass-bg); padding:16px; border-radius:16px; width:100%; border:1px solid var(--glass-border); text-align:center;">
                <span style="font-size:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Estimated Fare</span>
                <div id="vehicle-fare" style="font-size:32px; font-weight:800; color:var(--text-dark); margin-top:4px;">₱0</div>
            </div>
        </div>
        <button class="btn-primary" style="width:100%; padding: 16px; font-size:16px; border-radius:16px; margin-top:10px;" onclick="window.confirmVehicleBooking()">Confirm Booking & Add to Itinerary</button>
    </div>
</div>

<!-- Include Bottom Navigation Component -->




<script>
(function() {
    // In an SPA context, this script is executed every time the view is injected.
    if (window.mapInstance) {
        try { window.mapInstance.remove(); } catch(e) {}
        window.mapInstance = null;
    }

    window.allMapLocations = window.allMapLocations || [];
    window.currentDestinationForRoute = null;
    window.userMarker = null;
    window.mapMarkers = [];

    window.initMap = async function() {
        const mapEl = document.getElementById('tourist-map');
        if (!mapEl) return;

        const style = {
            "version": 8,
            "glyphs": "https://basemaps.cartocdn.com/gl/positron-gl-style/fonts/{fontstack}/{range}.pbf",
            "sources": {
                "satellite": {
                    "type": "raster",
                    "tiles": ["https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"],
                    "tileSize": 256
                },
                "osm": {
                    "type": "raster",
                    "tiles": ["https://a.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png"],
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
                    "id": "satellite",
                    "type": "raster",
                    "source": "satellite",
                    "layout": { "visibility": "none" }
                },
                {
                    "id": "base-map",
                    "type": "raster",
                    "source": "osm",
                    "layout": { "visibility": "visible" }
                }
            ]
        };

        window.mapInstance = new maplibregl.Map({
            container: 'tourist-map',
            style: style,
            center: [120.3167, 16.6159],
            zoom: 11,
            pitch: 0,
            attributionControl: false
        });

        // Add Zoom Controls (+ and -)
        window.mapInstance.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'bottom-right');

        // Add 3D Terrain and Region Mask
        window.mapInstance.on('load', async () => {
            window.mapInstance.setTerrain({ "source": "terrain", "exaggeration": 1.5 });

            try {
                // Use local cached JSON to eliminate the 3-second Nominatim network lag
                const regionRes = await fetch('assets/la_union.json');
                const regionData = await regionRes.json();
                if (regionData && regionData[0] && regionData[0].geojson) {
                    const geojson = regionData[0].geojson;
                    
                    const worldBox = [ [180, 90], [-180, 90], [-180, -90], [180, -90], [180, 90] ];
                    let coordinates = [];
                    if (geojson.type === 'Polygon') {
                        coordinates = [worldBox, ...geojson.coordinates];
                    } else if (geojson.type === 'MultiPolygon') {
                        let holes = [];
                        geojson.coordinates.forEach(polygon => { holes.push(polygon[0]); });
                        coordinates = [worldBox, ...holes];
                    }

                    if (coordinates.length > 0) {
                        window.mapInstance.addSource('mask', {
                            'type': 'geojson',
                            'data': { "type": "Feature", "geometry": { "type": "Polygon", "coordinates": coordinates } }
                        });
                        window.mapInstance.addLayer({
                            'id': 'mask-layer',
                            'type': 'fill',
                            'source': 'mask',
                            'paint': { 'fill-color': '#F2F2F7', 'fill-opacity': 1 }
                        });
                    }
                    
                    let bounds = new maplibregl.LngLatBounds();
                    if (geojson.type === 'Polygon') {
                        geojson.coordinates[0].forEach(coord => bounds.extend(coord));
                    } else if (geojson.type === 'MultiPolygon') {
                        geojson.coordinates.forEach(poly => poly[0].forEach(coord => bounds.extend(coord)));
                    }
                    window.mapInstance.setMaxBounds(bounds);
                }
            } catch(e) { console.error("Failed to slice region:", e); }

            // Fetch and render markers
            try {
                let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';
                const response = await fetch(backendUrl + '/api/public/map', {
                    headers: { 'ngrok-skip-browser-warning': 'true', 'Accept': 'application/json' }
                });
                const data = await response.json();
                window.allMapLocations = data.destinations || [];

                setupFilters();
                renderMarkers(window.allMapLocations);
                
                setTimeout(() => {
                    const pendingStr = localStorage.getItem('intan_elyu_pending_route');
                    if (pendingStr) {
                        localStorage.removeItem('intan_elyu_pending_route');
                        const place = JSON.parse(pendingStr);
                        window.mapInstance.flyTo({ center: [parseFloat(place.lng), parseFloat(place.lat) - 0.02], zoom: 14 });
                        window.openSheet(place);
                        setTimeout(() => {
                            const routeBtn = document.getElementById('btn-show-route');
                            if (routeBtn) routeBtn.click();
                        }, 800);
                    }

                    const viewStr = localStorage.getItem('intan_elyu_view_destination');
                    if (viewStr) {
                        localStorage.removeItem('intan_elyu_view_destination');
                        const place = JSON.parse(viewStr);
                        window.mapInstance.flyTo({ center: [parseFloat(place.lng), parseFloat(place.lat) - 0.02], zoom: 14 });
                        window.openSheet(place);
                    }
                }, 600);
            } catch (error) {
                console.error("Map fetch error:", error);
            }
        });

        setupEventListeners();
    };

    window.renderMarkers = function(locations) {
        if (window.mapMarkers) {
            window.mapMarkers.forEach(m => m.remove());
        }
        window.mapMarkers = [];

        const features = locations.filter(loc => loc.lat && loc.lng).map(loc => ({
            type: 'Feature',
            geometry: {
                type: 'Point',
                coordinates: [parseFloat(loc.lng), parseFloat(loc.lat)]
            },
            properties: {
                id: loc.id,
                name: loc.name,
                category: loc.category,
                lat: loc.lat,
                lng: loc.lng,
                raw_data: JSON.stringify(loc)
            }
        }));

        const geojsonData = {
            type: 'FeatureCollection',
            features: features
        };

        if (window.mapInstance.getSource('tourist-spots')) {
            window.mapInstance.getSource('tourist-spots').setData(geojsonData);
        } else {
            window.mapInstance.addSource('tourist-spots', {
                type: 'geojson',
                data: geojsonData
            });

            // Native WebGL Circles (Zero Lag)
            window.mapInstance.addLayer({
                id: 'tourist-spots-circles',
                type: 'circle',
                source: 'tourist-spots',
                paint: {
                    'circle-color': '#007AFF', // var(--primary-color)
                    'circle-radius': 10,
                    'circle-stroke-width': 3,
                    'circle-stroke-color': '#FFFFFF'
                }
            });

            window.mapInstance.on('click', 'tourist-spots-circles', (e) => {
                const props = e.features[0].properties;
                const loc = JSON.parse(props.raw_data);
                
                // Clear any existing active popup
                if (window.activePopup) {
                    window.activePopup.remove();
                }

                const popupContent = document.createElement('div');
                popupContent.style.cssText = "font-weight:700; font-size:14px; color:var(--text-dark); padding: 4px 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;";
                popupContent.innerHTML = `${loc.name} <i class="fa-solid fa-chevron-right" style="font-size:12px; color:var(--primary-color);"></i>`;
                
                popupContent.addEventListener('click', () => {
                    window.openSheet(loc);
                });

                // Show a smooth popup right on the marker
                window.activePopup = new maplibregl.Popup({
                    closeButton: false,
                    closeOnClick: false,
                    offset: 15,
                    className: 'smooth-map-popup'
                })
                .setLngLat([parseFloat(loc.lng), parseFloat(loc.lat)])
                .setDOMContent(popupContent)
                .addTo(window.mapInstance);

                // Ensure it stays on top
                const popupEl = window.activePopup.getElement();
                if(popupEl) popupEl.style.zIndex = 9999;

                window.mapInstance.flyTo({
                    center: [parseFloat(loc.lng), parseFloat(loc.lat)],
                    zoom: 14,
                    duration: 1000
                });
            });

            // Change cursor on hover
            window.mapInstance.on('mouseenter', 'tourist-spots-circles', () => {
                window.mapInstance.getCanvas().style.cursor = 'pointer';
            });
            window.mapInstance.on('mouseleave', 'tourist-spots-circles', () => {
                window.mapInstance.getCanvas().style.cursor = '';
            });
        }
    }

    function setupFilters() {
        const container = document.getElementById('map-categories-container');
        if (!container) return;

        const categories = [...new Set(window.allMapLocations.map(loc => loc.category).filter(Boolean))];
        let html = `<div class="category-pill active" onclick="filterCategory('All', this)">All</div>`;
        categories.forEach(cat => {
            html += `<div class="category-pill" onclick="filterCategory('${cat}', this)">${cat}</div>`;
        });
        container.innerHTML = html;
    }

    window.filterCategory = function(category, element) {
        document.querySelectorAll('.category-pill').forEach(pill => pill.classList.remove('active'));
        if(element) element.classList.add('active');

        const searchInput = document.getElementById('map-search-input');
        const searchText = searchInput ? searchInput.value.toLowerCase() : '';
        
        const filtered = window.allMapLocations.filter(loc => {
            const name = loc.name ? loc.name.toLowerCase() : '';
            const location = loc.location ? loc.location.toLowerCase() : '';
            return (name.includes(searchText) || location.includes(searchText)) && (category === 'All' || loc.category === category);
        });
        
        window.renderMarkers(filtered);

        if (filtered.length > 0 && window.mapInstance) {
            const bounds = new maplibregl.LngLatBounds();
            filtered.forEach(loc => bounds.extend([parseFloat(loc.lng), parseFloat(loc.lat)]));
            window.mapInstance.fitBounds(bounds, { padding: 50, duration: 1000, maxZoom: 15 });
        }
    };

    function setupEventListeners() {
        window.getDeviceLocation = async () => {
            if (window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform()) {
                try {
                    // Dynamically acquire the Geolocation plugin proxy
                    const Geolocation = (window.Capacitor.Plugins && window.Capacitor.Plugins.Geolocation) || 
                                      (window.Capacitor.registerPlugin ? window.Capacitor.registerPlugin('Geolocation') : null);
                    
                    if (!Geolocation) throw new Error("Geolocation plugin not loaded in Capacitor");

                    const perm = await Geolocation.checkPermissions();
                    if (perm.location !== 'granted') {
                        const req = await Geolocation.requestPermissions();
                        if (req.location !== 'granted') throw new Error('Permission denied by user');
                    }
                    const pos = await Geolocation.getCurrentPosition({ enableHighAccuracy: true });
                    return pos;
                } catch (e) {
                    throw new Error("Native location error: " + e.message);
                }
            } else {
                return new Promise((resolve, reject) => {
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(resolve, () => reject(new Error("Location denied by browser")), { enableHighAccuracy: false, timeout: 30000 });
                    } else {
                        reject(new Error("Geolocation not supported"));
                    }
                });
            }
        };

        const searchInput = document.getElementById('map-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const activeCatEl = document.querySelector('.category-pill.active');
                const activeCat = activeCatEl ? activeCatEl.innerText : 'All';
                window.filterCategory(activeCat, activeCatEl || document.querySelector('.category-pill'));
            });
        }

        const locateBtn = document.getElementById('btn-locate-me');
        if (locateBtn) {
            locateBtn.addEventListener('click', () => {
                showToast("Locating...");
                const handleLocation = (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    window.mapInstance.flyTo({ center: [lng, lat], zoom: 15 });
                    
                    if (window.userMarker) window.userMarker.remove();
                    const el = document.createElement('div');
                    el.innerHTML = `<div style="background:#007AFF; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow:0 0 0 5px rgba(0,122,255,0.3);"></div>`;
                    window.userMarker = new maplibregl.Marker({element: el}).setLngLat([lng, lat]).addTo(window.mapInstance);
                };
                window.getDeviceLocation().then(handleLocation).catch(e => showToast(e.message));
            });
        }
        let isSatellite = false;
        const btn3d = document.getElementById('btn-3d-view');
        const btnLayer = document.getElementById('btn-layer-toggle');

        if (btnLayer) {
            btnLayer.addEventListener('click', () => {
                isSatellite = !isSatellite;
                
                if (isSatellite) {
                    btnLayer.style.background = 'var(--primary-color)';
                    btnLayer.style.color = 'white';
                    window.mapInstance.setLayoutProperty('base-map', 'visibility', 'none');
                    window.mapInstance.setLayoutProperty('satellite', 'visibility', 'visible');
                    showToast("Satellite Layer Enabled");
                } else {
                    btnLayer.style.background = 'var(--bg-secondary)';
                    btnLayer.style.color = 'var(--primary-color)';
                    window.mapInstance.setLayoutProperty('satellite', 'visibility', 'none');
                    window.mapInstance.setLayoutProperty('base-map', 'visibility', 'visible');
                    showToast("Street Layer Enabled");
                }
            });
        }

        if (btn3d) {
            btn3d.addEventListener('click', () => {
                const is3D = btn3d.classList.toggle('active');
                if (is3D) {
                    btn3d.style.background = 'var(--primary-color)';
                    btn3d.style.color = 'white';
                    window.mapInstance.easeTo({ pitch: 65, bearing: -20, duration: 1000 });
                    showToast("3D Terrain View Enabled");
                } else {
                    btn3d.style.background = 'var(--bg-secondary)';
                    btn3d.style.color = 'var(--primary-color)';
                    window.mapInstance.easeTo({ pitch: 0, bearing: 0, duration: 1000 });
                    showToast("2D View Restored");
                }
            });
        }

        const showRouteBtn = document.getElementById('btn-show-route');
        if(showRouteBtn) {
            showRouteBtn.addEventListener('click', async () => {
                if (!window.currentDestinationForRoute) return;
                const dest = window.currentDestinationForRoute;
                showToast("Calculating route...");
                window.closeSheet();

                const handleRouting = async (position) => {
                    const startLat = position.coords.latitude;
                    const startLng = position.coords.longitude;
                    
                    try {
                        const res = await fetch(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${dest.lng},${dest.lat}?overview=full&geometries=geojson`);
                        const routeData = await res.json();
                        if (routeData.code !== 'Ok' || !routeData.routes.length) throw new Error("No route");

                        const route = routeData.routes[0];
                        const geojson = route.geometry;

                        if (window.mapInstance.getSource('route')) {
                            window.mapInstance.removeLayer('route-line');
                            window.mapInstance.removeSource('route');
                        }
                        
                        window.mapInstance.addSource('route', { 'type': 'geojson', 'data': geojson });
                        window.mapInstance.addLayer({
                            'id': 'route-line',
                            'type': 'line',
                            'source': 'route',
                            'layout': { 'line-join': 'round', 'line-cap': 'round' },
                            'paint': { 'line-color': '#007AFF', 'line-width': 5 }
                        });

                        const bounds = geojson.coordinates.reduce((b, coord) => b.extend(coord), new maplibregl.LngLatBounds(geojson.coordinates[0], geojson.coordinates[0]));
                        window.mapInstance.fitBounds(bounds, { padding: 50 });

                        if (window.userMarker) window.userMarker.remove();
                        const el = document.createElement('div');
                        el.innerHTML = `<div style="background:#007AFF; width:20px; height:20px; border-radius:50%; border:3px solid white;"></div>`;
                        window.userMarker = new maplibregl.Marker({element: el}).setLngLat([startLng, startLat]).addTo(window.mapInstance);

                        // Fare calculation
                        const distanceKm = route.distance / 1000;
                        const durationMin = route.duration / 60;
                        document.getElementById('route-distance').textContent = distanceKm.toFixed(1) + ' km';
                        document.getElementById('route-time').textContent = Math.round(durationMin) + ' mins';
                        
                        const destName = dest.name.toLowerCase();
                        const isHighTerrain = destName.includes('falls') || destName.includes('peak') || destName.includes('mountain') || destName.includes('ridge');
                        
                        const createCard = (name, icon, color, desc, fare) => `
                        <div onclick="window.openVehicleCard('${name}', '${icon}', '${color}', '${desc}', ${fare})" style="cursor:pointer; display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid var(--glass-border); border-radius:16px; background:var(--bg-primary); transition: transform 0.1s;">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="background:var(--glass-bg); width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:${color}; font-size:18px;"><i class="fa-solid ${icon}"></i></div>
                                <div><h5 style="margin:0 0 2px 0; font-size:15px; font-weight:800; color:var(--text-dark);">${name}</h5><span style="font-size:12px; color:var(--text-muted);">${desc}</span></div>
                            </div>
                            <div style="font-weight:800; color:var(--text-dark); font-size:18px;">₱${fare}</div>
                        </div>`;

                        let faresHtml = '';
                        if (isHighTerrain) {
                            faresHtml += createCard('Habal-Habal', 'fa-motorcycle', '#FF3B30', 'Primary: Best for steep mountain climbs', Math.round(50 + (distanceKm * 15)));
                        } else if (distanceKm <= 5) {
                            faresHtml += createCard('Tricycle', 'fa-motorcycle', 'var(--secondary-color)', 'Primary: Private & direct', Math.round(20 + (Math.max(0, distanceKm - 1) * 10)));
                        } else {
                            faresHtml += createCard('Jeepney', 'fa-van-shuttle', 'var(--primary-color)', 'Primary: Designated terminal route', Math.round(13 + (Math.max(0, distanceKm - 4) * 1.80)));
                        }
                        
                        document.getElementById('fare-list').innerHTML = faresHtml;
                        document.getElementById('route-details-sheet').classList.add('active');
                    } catch (err) { showToast("Failed to calculate route."); }
                };
                window.getDeviceLocation().then(handleRouting).catch(e => showToast(e.message));
            });
        }
    }

    window.openSheet = function(locationData) {
        if (window.activePopup) {
            window.activePopup.remove();
        }
        document.getElementById('route-details-sheet').classList.remove('active');
        window.currentDestinationForRoute = locationData;
        document.getElementById('sheet-title').textContent = locationData.name;
        document.getElementById('sheet-location').textContent = locationData.location;
        
        let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';
        const imgPath = locationData.image ? (locationData.image.startsWith('http') ? locationData.image : (locationData.image.startsWith('uploads/') ? backendUrl + '/' + locationData.image : backendUrl + '/storage/' + locationData.image)) : 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
        
        const imgEl = document.getElementById('sheet-img');
        if (imgEl) {
            imgEl.src = imgPath;
            imgEl.onerror = function() { this.src = 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; };
        }
        
        document.getElementById('tourist-guide-details').style.display = 'flex';
        
        const destName = locationData.name.toLowerCase();
        const isHighTerrain = destName.includes('falls') || destName.includes('peak') || destName.includes('mountain') || destName.includes('ridge');
        let howToGo = isHighTerrain ? 'Habal-Habal' : 'Tricycle / Jeepney';
        if (!isHighTerrain && locationData.accessible_by_car) howToGo = 'Car / Tricycle / Jeepney';
        document.getElementById('sheet-how-to-go').textContent = howToGo;
        
        document.getElementById('sheet-distance').textContent = 'Calculating...';
        document.getElementById('sheet-transport').textContent = '₱' + (locationData.avg_transport_cost || 0);

        if (window.getDeviceLocation) {
            window.getDeviceLocation().then(async (pos) => {
                const startLat = pos.coords.latitude;
                const startLng = pos.coords.longitude;
                const destLat = parseFloat(locationData.lat);
                const destLng = parseFloat(locationData.lng);
                try {
                    const res = await fetch(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${destLng},${destLat}?overview=false`);
                    const routeData = await res.json();
                    if (routeData.code === 'Ok' && routeData.routes.length > 0) {
                        const distanceKm = routeData.routes[0].distance / 1000;
                        document.getElementById('sheet-distance').textContent = distanceKm.toFixed(1) + ' km';
                    } else {
                        document.getElementById('sheet-distance').textContent = 'Unknown';
                    }
                } catch (e) {
                    document.getElementById('sheet-distance').textContent = 'Unknown';
                }
            }).catch(() => {
                document.getElementById('sheet-distance').textContent = 'Location needed';
            });
        }
        if (locationData.description) {
            document.getElementById('sheet-desc-container').style.display = 'block';
            document.getElementById('sheet-desc').textContent = locationData.description;
        } else {
            document.getElementById('sheet-desc-container').style.display = 'none';
        }

        document.getElementById('place-details-sheet').classList.add('active');
    };

    window.closeSheet = function() {
        document.getElementById('place-details-sheet').classList.remove('active');
    };

    window.openVehicleCard = function(name, icon, color, desc, fare) {
        document.getElementById('vehicle-icon').className = 'fa-solid ' + icon;
        document.getElementById('vehicle-icon').style.color = color;
        document.getElementById('vehicle-name').textContent = name;
        document.getElementById('vehicle-desc').textContent = desc;
        document.getElementById('vehicle-fare').textContent = '₱' + fare;
        window.currentSelectedVehicleFare = fare;
        document.getElementById('selected-vehicle-sheet').classList.add('active');
    };

    window.confirmVehicleBooking = function() {
        if (!window.currentDestinationForRoute) return;
        const dest = window.currentDestinationForRoute;
        let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
        
        // Remove if already exists so we can update it with the new transport fare
        draft = draft.filter(item => item.id !== dest.id);
        
        const fare = window.currentSelectedVehicleFare || dest.avg_transport_cost || 0;
        
        draft.push({ ...dest, entrance_fee: dest.entrance_fee || 0, avg_food_cost: dest.avg_food_cost || 0, avg_transport_cost: fare });
        localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
        
        showToast("Transport Booked & Added to Itinerary!");
        document.getElementById('selected-vehicle-sheet').classList.remove('active');
        document.getElementById('route-details-sheet').classList.remove('active');
        window.closeSheet();
    };

    window.addToItinerary = function() {
        if (!window.currentDestinationForRoute) return;
        const dest = window.currentDestinationForRoute;
        let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
        if (draft.some(item => item.id === dest.id)) return showToast("Already in your itinerary!");
        draft.push({ ...dest, entrance_fee: dest.entrance_fee || 0, avg_food_cost: dest.avg_food_cost || 0, avg_transport_cost: dest.avg_transport_cost || 0 });
        localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
        showToast("Added to Itinerary!");
        window.closeSheet();
    };

    setTimeout(window.initMap, 50);
})();
</script>








