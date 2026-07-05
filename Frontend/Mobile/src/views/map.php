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
    <div class="btn-layer-toggle animate-slide-up" id="btn-layer-toggle" style="position: absolute; bottom: calc(340px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1000; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-layer-group"></i>
    </div>

    <!-- 3D Mode Button -->
    <div class="btn-3d-view animate-slide-up" id="btn-3d-view" style="position: absolute; bottom: calc(280px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1000; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-cube"></i>
    </div>

    <!-- Bottom Sheet (hidden by default) -->
    <div class="bottom-sheet" id="place-details-sheet">
        <!-- Header row -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2px;">
            <div style="flex:1; min-width:0;">
                <h3 class="sheet-title" id="sheet-title">Destination Name</h3>
                <p class="sheet-location"><i class="fa-solid fa-location-dot"></i><span id="sheet-location">Location details</span></p>
            </div>
            <div style="display:flex; gap: 8px;">
                <button onclick="window.closeSheet()" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.1); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:rgba(148,163,184,0.9); font-size:14px; cursor:pointer; flex-shrink:0; transition:background 0.2s;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <img src="" alt="Place Image" class="sheet-img" id="sheet-img">

        <!-- About This Location & Tourist Guide Details -->
        <div id="sheet-desc-container" style="margin-top:16px; margin-bottom:16px; display:none; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:18px; padding:16px;">
            <div id="sheet-desc-animator" style="overflow:hidden;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                    <div style="width:30px; height:30px; border-radius:10px; background:rgba(56,189,248,0.15); display:flex; align-items:center; justify-content:center; border:1px solid rgba(56,189,248,0.2);">
                        <i class="fa-solid fa-book-open" style="color:#38bdf8; font-size:13px;"></i>
                    </div>
                    <h5 style="margin:0; font-size:12px; font-weight:800; letter-spacing:1px; color:#f8fafc; text-transform:uppercase;">About this location</h5>
                </div>
            
            <p id="sheet-desc-short" style="font-size:13px; color:rgba(248,250,252,0.8); line-height:1.6; margin:0; font-weight:400; letter-spacing:0.3px;"></p>
            <p id="sheet-desc-full" style="font-size:13px; color:rgba(248,250,252,0.8); line-height:1.6; margin:0; display:none; font-weight:400; letter-spacing:0.3px;"></p>
            
            
            <style>
                .btn-details-active:active {
                    transform: scale(0.97);
                }
            </style>

            <!-- Expanded Info (hidden initially) -->
            <div id="expanded-details" style="display:none; flex-direction:column; margin-top:16px; padding-top:16px; border-top:1px dashed rgba(255,255,255,0.1);">
                <h4 style="margin:0 0 10px; font-size:11px; font-weight:800; letter-spacing:1px; color:rgba(148,163,184,0.7); text-transform:uppercase;">Tourist Guide</h4>

                <div class="map-info-row">
                    <span class="map-info-label">
                        <i class="fa-solid fa-car"></i>
                        How to get there
                    </span>
                    <span class="map-info-value" id="sheet-how-to-go">...</span>
                </div>

                <div class="map-info-row">
                    <span class="map-info-label">
                        <i class="fa-solid fa-location-arrow"></i>
                        Distance
                    </span>
                    <span class="map-info-value" id="sheet-distance">Calculating...</span>
                </div>

                <div class="map-info-row">
                    <span class="map-info-label">
                        <i class="fa-solid fa-van-shuttle"></i>
                        Avg. Transport
                    </span>
                    <span class="map-info-value" id="sheet-transport">₱0</span>
                </div>

                <div style="margin-top: 10px; background: rgba(255,255,255,0.03); padding: 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <h5 style="margin: 0 0 6px; font-size: 10px; color: rgba(148,163,184,0.7); text-transform: uppercase; letter-spacing: 0.5px;"><i class="fa-solid fa-map" style="margin-right: 4px;"></i> Route Guide</h5>
                    <p id="sheet-manual-guide" style="margin: 0; font-size: 13px; color: #e2e8f0; line-height: 1.5;"></p>
                </div>

                <button onclick="window.contactMTO()" style="margin-top:12px; width:100%; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.2); color:#38bdf8; padding:12px; border-radius:14px; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="fa-solid fa-user-tie"></i> Contact MTO / Guide
                </button>
            </div>
            </div> <!-- End sheet-desc-animator -->

            <button id="btn-view-details" class="btn-details-active" onclick="window.toggleFullDetails()" style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.15); border-radius:12px; width:100%; color:#38bdf8; font-size:13px; font-weight:700; padding:12px 0; margin-top:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:background 0.2s, transform 0.1s;">
                <span id="details-btn-text">View Full Details</span>
                <i class="fa-solid fa-chevron-down" id="details-chevron" style="transition:transform 0.3s ease;"></i>
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="sheet-btn-row" style="display: flex; gap: 8px;">
            <button class="btn-sheet-primary" onclick="window.addToItinerary()" style="flex: 1; padding: 0 10px; font-size: 13px; height: 46px;">
                <i class="fa-solid fa-plus"></i> Add
            </button>
            <button id="btn-show-route" class="btn-sheet-secondary" style="flex: 1; padding: 0 10px; font-size: 13px; height: 46px;">
                <i class="fa-solid fa-route"></i> Route
            </button>
            <button id="sheet-fav-btn" onclick="window.toggleMapFavorite(this)" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.1); width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.4); font-size:16px; cursor:pointer; flex-shrink:0; transition:color 0.2s, background 0.2s;">
                <i class="fa-solid fa-heart"></i>
            </button>
        </div>
    </div>

    <!-- Route Details & Fares Sheet -->
    <div class="bottom-sheet" id="route-details-sheet" style="padding-bottom:30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div style="flex:1;">
                <h3 class="sheet-title">Route &amp; Fares</h3>
                <p class="sheet-location" style="margin:4px 0 0;">
                    <i class="fa-solid fa-route"></i>
                    <span id="route-distance" style="font-weight:700;">0 km</span>
                    <span style="color:rgba(255,255,255,0.2); margin:0 6px;">|</span>
                    <i class="fa-regular fa-clock" style="color:#34d399;"></i>
                    <span id="route-time" style="font-weight:700; color:#34d399;">0 min</span>
                </p>
                <div id="route-traffic-warning" style="display:none; font-size:10px; font-weight:500; margin-top:4px;"></div>
            </div>
            <button onclick="document.getElementById('route-details-sheet').classList.remove('active')" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.1); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:rgba(148,163,184,0.9); font-size:14px; cursor:pointer; flex-shrink:0; margin-left:10px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <h4>Estimated Transport Options</h4>
        <div style="display:flex; flex-direction:column; max-height:45vh; overflow-y:auto; padding-bottom:20px; -webkit-overflow-scrolling:touch;" id="fare-list">
            <!-- Dynamic fares injected here -->
        </div>
    </div>

    <!-- Selected Vehicle Sheet -->
    <div class="bottom-sheet" id="selected-vehicle-sheet" style="padding-bottom:30px; z-index:1005;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <h3 class="sheet-title">Selected Transport</h3>
            <button onclick="document.getElementById('selected-vehicle-sheet').classList.remove('active')" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.1); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:rgba(148,163,184,0.9); font-size:14px; cursor:pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div style="display:flex; flex-direction:column; align-items:center; margin-top:16px; padding-bottom:10px;">
            <div style="width:76px; height:76px; border-radius:22px; display:flex; align-items:center; justify-content:center; font-size:34px; margin-bottom:14px; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.2);">
                <i id="vehicle-icon" class="fa-solid fa-car" style="color:#38bdf8;"></i>
            </div>
            <h2 id="vehicle-name" style="margin:0 0 6px; font-size:22px; font-weight:800; color:#f8fafc;">Vehicle</h2>
            <p id="vehicle-desc" style="color:rgba(148,163,184,0.8); font-size:13px; text-align:center; margin:0 0 18px;">Description</p>
            <div style="background:rgba(255,255,255,0.05); padding:18px 16px; border-radius:18px; width:100%; border:1px solid rgba(255,255,255,0.08); text-align:center;">
                <span style="font-size:11px; font-weight:700; letter-spacing:1.2px; color:rgba(148,163,184,0.7); text-transform:uppercase;">Estimated Fare</span>
                <div id="vehicle-fare" style="font-size:36px; font-weight:800; color:#38bdf8; margin-top:6px; letter-spacing:-1px;">₱0</div>
            </div>
        </div>
        <button class="btn-sheet-primary" style="width:100%; margin-top:14px; padding:15px;" onclick="window.confirmVehicleBooking()">
            <i class="fa-solid fa-check-circle" style="margin-right:8px;"></i>Confirm Booking &amp; Add to Itinerary
        </button>
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

        // Fetch data immediately to run in parallel with MapLibre initialization
        let backendUrl = 'http://localhost:8000';
        const mapDataPromise = fetch(backendUrl + '/api/public/map', {
            headers: { 'ngrok-skip-browser-warning': 'true', 'Accept': 'application/json' }
        }).then(r => r.json()).catch(e => console.error("Map fetch error:", e));

        const regionDataPromise = fetch('assets/la_union.json').then(r => r.json()).catch(e => console.error("Region fetch error:", e));

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
                // Wait for the parallel region data fetch
                const regionData = await regionDataPromise;
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
                const data = await mapDataPromise;
                if (data && data.destinations) {
                    window.allMapLocations = data.destinations || [];

                    setupFilters();
                    renderMarkers(window.allMapLocations);
                    
                    setTimeout(() => {
                        const pendingStr = localStorage.getItem('intan_elyu_pending_route');
                        if (pendingStr) {
                            localStorage.removeItem('intan_elyu_pending_route');
                            const place = JSON.parse(pendingStr);
                            if (place.lat && place.lng && !isNaN(parseFloat(place.lat)) && !isNaN(parseFloat(place.lng))) {
                                window.mapInstance.flyTo({ center: [parseFloat(place.lng), parseFloat(place.lat) - 0.02], zoom: 14 });
                                window.openSheet(place);
                                setTimeout(() => {
                                    const routeBtn = document.getElementById('btn-show-route');
                                    if (routeBtn) routeBtn.click();
                                }, 800);
                            }
                        }

                        const viewStr = localStorage.getItem('intan_elyu_view_destination');
                        if (viewStr) {
                            localStorage.removeItem('intan_elyu_view_destination');
                            const place = JSON.parse(viewStr);
                            if (place.lat && place.lng && !isNaN(parseFloat(place.lat)) && !isNaN(parseFloat(place.lng))) {
                                window.mapInstance.flyTo({ center: [parseFloat(place.lng), parseFloat(place.lat) - 0.02], zoom: 14 });
                                window.openSheet(place);
                            }
                        }
                    }, 300); // Reduced delay since data is ready faster
                }
            } catch (error) {
                console.error("Map data processing error:", error);
            }
        });

        setupEventListeners();
    };

    window.renderMarkers = function(locations) {
        if (window.mapMarkers) {
            window.mapMarkers.forEach(m => m.remove());
        }
        window.mapMarkers = [];

        locations.forEach(loc => {
            if (!loc.lat || !loc.lng || isNaN(parseFloat(loc.lat)) || isNaN(parseFloat(loc.lng))) return;
            
            const cat = loc.category || 'Default';
            let iconClass = 'fa-location-dot';
            const catLower = cat.toLowerCase();
            
            if (catLower.includes('beach') || catLower.includes('surf')) iconClass = 'fa-umbrella-beach';
            else if (catLower.includes('hotel') || catLower.includes('resort') || catLower.includes('stay')) iconClass = 'fa-bed';
            else if (catLower.includes('food') || catLower.includes('restaurant') || catLower.includes('cafe') || catLower.includes('eat') || catLower.includes('dining')) iconClass = 'fa-utensils';
            else if (catLower.includes('nature') || catLower.includes('park') || catLower.includes('mountain') || catLower.includes('trail')) iconClass = 'fa-leaf';
            else if (catLower.includes('history') || catLower.includes('culture') || catLower.includes('church') || catLower.includes('museum')) iconClass = 'fa-monument';
            else if (catLower.includes('water') || catLower.includes('fall') || catLower.includes('river')) iconClass = 'fa-water';

            const container = document.createElement('div');
            
            const el = document.createElement('div');
            el.className = 'custom-map-marker';
            el.style.width = '32px';
            el.style.height = '32px';
            el.style.backgroundColor = '#FFFFFF';
            el.style.border = '2px solid #007AFF'; // var(--primary-color)
            el.style.borderRadius = '50%';
            el.style.display = 'flex';
            el.style.alignItems = 'center';
            el.style.justifyContent = 'center';
            el.style.color = '#007AFF'; // var(--primary-color)
            el.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
            el.style.cursor = 'pointer';
            el.style.transition = 'transform 0.2s';
            
            el.innerHTML = `<i class="fa-solid ${iconClass}" style="font-size:14px;"></i>`;
            
            container.appendChild(el);
            
            container.addEventListener('mouseenter', () => el.style.transform = 'scale(1.2)');
            container.addEventListener('mouseleave', () => el.style.transform = 'scale(1)');
            
            container.addEventListener('click', (e) => {
                e.stopPropagation();
                // Clear any existing active popup
                if (window.activePopup) window.activePopup.remove();

                const popupContent = document.createElement('div');
                popupContent.style.cssText = "font-weight:700; font-size:14px; color:var(--text-dark); padding: 4px 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;";
                popupContent.innerHTML = `${loc.name} <i class="fa-solid fa-chevron-right" style="font-size:12px; color:var(--primary-color);"></i>`;
                
                popupContent.addEventListener('click', () => window.openSheet(loc));

                window.activePopup = new maplibregl.Popup({
                    closeButton: false, closeOnClick: false, offset: 15, className: 'smooth-map-popup'
                })
                .setLngLat([parseFloat(loc.lng), parseFloat(loc.lat)])
                .setDOMContent(popupContent)
                .addTo(window.mapInstance);

                const popupEl = window.activePopup.getElement();
                if(popupEl) popupEl.style.zIndex = 9999;
                
                window.mapInstance.flyTo({ center: [parseFloat(loc.lng), parseFloat(loc.lat)], zoom: 14, duration: 1000 });
            });
            
            const marker = new maplibregl.Marker({ element: container })
                .setLngLat([parseFloat(loc.lng), parseFloat(loc.lat)])
                .addTo(window.mapInstance);
                
            window.mapMarkers.push(marker);
        });
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

        const validFiltered = filtered.filter(loc => loc.lat && loc.lng && !isNaN(parseFloat(loc.lat)) && !isNaN(parseFloat(loc.lng)));
        if (validFiltered.length > 0 && window.mapInstance) {
            const bounds = new maplibregl.LngLatBounds();
            validFiltered.forEach(loc => bounds.extend([parseFloat(loc.lng), parseFloat(loc.lat)]));
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
                    btnLayer.style.background = '#1E3A8A';
                    btnLayer.style.color = '#ffffff';
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
                    btn3d.style.background = '#1E3A8A';
                    btn3d.style.color = '#ffffff';
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
                            'paint': { 
                                'line-color': '#007AFF', 
                                'line-width': [
                                    'interpolate', ['linear'], ['zoom'],
                                    10, 3,
                                    14, 6,
                                    18, 12,
                                    22, 20
                                ]
                            }
                        });

                        const bounds = geojson.coordinates.reduce((b, coord) => b.extend(coord), new maplibregl.LngLatBounds(geojson.coordinates[0], geojson.coordinates[0]));
                        window.mapInstance.fitBounds(bounds, { padding: 50 });

                        if (window.userMarker) window.userMarker.remove();
                        const el = document.createElement('div');
                        el.innerHTML = `<div style="background:#007AFF; width:20px; height:20px; border-radius:50%; border:3px solid white;"></div>`;
                        window.userMarker = new maplibregl.Marker({element: el}).setLngLat([startLng, startLat]).addTo(window.mapInstance);

                        // Fare calculation
                        const distanceKm = route.distance / 1000;
                        let durationMin = route.duration / 60;

                        // OSRM assumes perfect driving at the speed limit.
                        // Apply a dynamic realism multiplier:
                        // Public transport involves waiting, passenger drop-offs, and general traffic.
                        // We use higher multipliers to account for these inherent delays.
                        let baseMultiplier = 1.6; // Long highway trips
                        if (distanceKm <= 3) baseMultiplier = 2.5; // Short local trips have heavy overhead
                        else if (distanceKm <= 7) baseMultiplier = 2.0; // Medium trips
                        
                        durationMin *= baseMultiplier;

                        // Traffic Buffer Logic
                        const currentHour = new Date().getHours();
                        const isRushHour = (currentHour >= 7 && currentHour <= 9) || (currentHour >= 16 && currentHour <= 19);
                        const warningDiv = document.getElementById('route-traffic-warning');

                        if (isRushHour) {
                            durationMin *= 1.4; // Additional penalty for rush hour (total ~2.5x)
                            if (warningDiv) {
                                warningDiv.style.display = 'block';
                                warningDiv.style.color = '#FF9500';
                                warningDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Heavy traffic expected at this hour';
                            }
                        } else {
                            if (warningDiv) {
                                warningDiv.style.display = 'block';
                                warningDiv.style.color = 'rgba(255,255,255,0.4)';
                                warningDiv.innerHTML = 'Typical traffic conditions';
                            }
                        }

                        document.getElementById('route-distance').textContent = distanceKm.toFixed(1) + ' km';
                        document.getElementById('route-time').textContent = Math.round(durationMin) + ' mins';
                        
                        const createCard = (name, icon, color, desc, fare) => `
                        <div onclick="window.openVehicleCard('${name}', '${icon}', '${color}', '${desc}', ${fare})"
                             style="cursor:pointer; display:flex; align-items:center; justify-content:space-between;
                                    padding:14px 16px; border:1px solid rgba(255,255,255,0.07); border-radius:18px;
                                    background:rgba(255,255,255,0.04); margin-bottom:10px;
                                    transition:transform 0.15s, background 0.15s;"
                             onpointerdown="this.style.transform='scale(0.97)'; this.style.background='rgba(56,189,248,0.08)'"
                             onpointerup="this.style.transform=''; this.style.background='rgba(255,255,255,0.04)'"
                             onpointercancel="this.style.transform=''; this.style.background='rgba(255,255,255,0.04)'">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:46px; height:46px; border-radius:14px; display:flex; align-items:center;
                                            justify-content:center; font-size:20px;
                                            background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.15);
                                            color:${color}; flex-shrink:0;">
                                    <i class="fa-solid ${icon}"></i>
                                </div>
                                <div>
                                    <h5 style="margin:0 0 3px; font-size:15px; font-weight:800; color:#f8fafc; letter-spacing:-0.2px;">${name}</h5>
                                    <span style="font-size:12px; color:rgba(148,163,184,0.75); font-weight:500;">${desc}</span>
                                </div>
                            </div>
                            <div style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.2);
                                        padding:6px 12px; border-radius:10px; font-weight:800;
                                        color:#38bdf8; font-size:15px; flex-shrink:0;">
                                ₱${fare}
                            </div>
                        </div>`;

                        let faresHtml = '';
                        
                        const ownCarFare = Math.max(10, Math.round((distanceKm / 12) * 65)); // Est. 65 per liter, 12km/L
                        const trikeFare = Math.round(20 + (Math.max(0, distanceKm - 1) * 10));

                        faresHtml += createCard('Tricycle', 'fa-motorcycle', 'var(--secondary-color)', 'Primary: Private & direct drop-off', trikeFare);
                        faresHtml += createCard('Own Car (Fuel Est.)', 'fa-car', '#34d399', 'Alternative: Personal vehicle fuel cost', ownCarFare);
                        
                        document.getElementById('fare-list').innerHTML = faresHtml;
                        document.getElementById('route-details-sheet').classList.add('active');
                    } catch (err) { showToast("Failed to calculate route."); }
                };
                window.getDeviceLocation().then(handleRouting).catch(e => showToast(e.message));
            });
        }
    }

    window.toggleMapFavorite = async function(element) {
        if (!window.currentDestinationForRoute) return;
        const destId = window.currentDestinationForRoute.id;
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) {
            showToast('Please login to save places');
            return;
        }
        let backendUrl = 'http://localhost:8000';
        try {
            const res = await fetch(backendUrl + '/api/tourist/destinations/' + destId + '/favorite', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const data = await res.json();
            if (data.status === 'added') {
                element.style.color = '#ff3b30';
                showToast('Added to Saved Places');
                if (!window.savedPlaceIds) window.savedPlaceIds = [];
                if (!window.savedPlaceIds.includes(destId)) window.savedPlaceIds.push(destId);
            } else {
                element.style.color = 'rgba(255,255,255,0.4)';
                showToast('Removed from Saved Places');
                if (window.savedPlaceIds) {
                    window.savedPlaceIds = window.savedPlaceIds.filter(id => id !== destId);
                }
            }
        } catch(e) {
            showToast('Error updating favorite');
        }
    };

    window.openSheet = function(locationData) {
        if (window.activePopup) {
            window.activePopup.remove();
        }
        document.getElementById('route-details-sheet').classList.remove('active');
        window.currentDestinationForRoute = locationData;
        document.getElementById('sheet-title').textContent = locationData.name;
        document.getElementById('sheet-location').textContent = locationData.location;

        const favBtn = document.getElementById('sheet-fav-btn');
        if (favBtn) {
            if (window.savedPlaceIds && window.savedPlaceIds.includes(locationData.id)) {
                favBtn.style.color = '#ff3b30';
            } else {
                favBtn.style.color = 'rgba(255,255,255,0.4)';
            }
            
            const token = localStorage.getItem('intan_elyu_token');
            if (token && !window.savedPlaceIdsFetched) {
                fetch('http://localhost:8000/api/tourist/dashboard', {
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
                }).then(r => r.json()).then(d => {
                    if (d.savedPlaces) {
                        window.savedPlaceIds = d.savedPlaces.map(p => p.id);
                        window.savedPlaceIdsFetched = true;
                        if (window.savedPlaceIds.includes(window.currentDestinationForRoute.id)) {
                            favBtn.style.color = '#ff3b30';
                        } else {
                            favBtn.style.color = 'rgba(255,255,255,0.4)';
                        }
                    }
                }).catch(e => console.error(e));
            }
        }
        
        let backendUrl = 'http://localhost:8000';
        const imgPath = locationData.image ? (locationData.image.startsWith('http') ? locationData.image : (locationData.image.startsWith('uploads/') ? backendUrl + '/' + locationData.image : backendUrl + '/storage/' + locationData.image)) : 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
        
        const imgEl = document.getElementById('sheet-img');
        if (imgEl) {
            imgEl.src = imgPath;
            imgEl.onerror = function() { this.onerror = null; this.src = 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; };
        }
        

        
        const destName = locationData.name.toLowerCase();
        
        let howToGo = locationData.accessible_by_car ? 'Car / Tricycle' : 'Tricycle';
        document.getElementById('sheet-how-to-go').textContent = howToGo;
        
        let manualGuide = "From the town proper of " + (locationData.municipality || "La Union") + ", take a local tricycle heading to " + (locationData.location || "the barangay") + ". Ask the driver to drop you off at " + locationData.name + ".";
        
        document.getElementById('sheet-manual-guide').textContent = manualGuide;
        
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
            
            const words = locationData.description.split(' ');
            if (words.length > 40) {
                document.getElementById('sheet-desc-short').textContent = words.slice(0, 40).join(' ') + '...';
                document.getElementById('sheet-desc-full').textContent = locationData.description;
                document.getElementById('btn-view-details').style.display = 'flex';
                document.getElementById('sheet-desc-short').style.display = 'block';
                document.getElementById('sheet-desc-full').style.display = 'none';
            } else {
                document.getElementById('sheet-desc-short').textContent = locationData.description;
                document.getElementById('sheet-desc-full').textContent = '';
                // Since it's short, maybe still allow expanding to see the Tourist Guide info
                document.getElementById('btn-view-details').style.display = 'flex';
                document.getElementById('sheet-desc-short').style.display = 'block';
                document.getElementById('sheet-desc-full').style.display = 'none';
            }
        } else {
            document.getElementById('sheet-desc-short').textContent = 'No description available.';
            document.getElementById('sheet-desc-full').textContent = '';
            document.getElementById('btn-view-details').style.display = 'flex';
            document.getElementById('sheet-desc-short').style.display = 'block';
            document.getElementById('sheet-desc-full').style.display = 'none';
            document.getElementById('sheet-desc-container').style.display = 'block';
        }
        
        // Reset toggle state every time we open a sheet
        document.getElementById('expanded-details').style.display = 'none';
        document.getElementById('details-chevron').style.transform = 'rotate(0deg)';
        const btnText = document.getElementById('details-btn-text');
        if (btnText) btnText.textContent = 'View Full Details';

        document.getElementById('place-details-sheet').classList.add('active');
    };

    window.closeSheet = function() {
        document.getElementById('place-details-sheet').classList.remove('active');
    };

    window.toggleFullDetails = function() {
        const animator = document.getElementById('sheet-desc-animator');
        const expanded = document.getElementById('expanded-details');
        const shortDesc = document.getElementById('sheet-desc-short');
        const fullDesc = document.getElementById('sheet-desc-full');
        const btnText = document.getElementById('details-btn-text');
        const chevron = document.getElementById('details-chevron');
        
        if (!animator || !expanded) return;

        const startHeight = animator.offsetHeight;
        animator.style.height = startHeight + 'px';
        animator.style.transition = 'none';
        void animator.offsetHeight;

        if (expanded.style.display === 'none') {
            // -- EXPANDING --
            expanded.style.display = 'flex';
            if (fullDesc && shortDesc && fullDesc.textContent.trim() !== '') {
                shortDesc.style.display = 'none';
                fullDesc.style.display = 'block';
                fullDesc.style.opacity = '0';
            }
            if (btnText) btnText.textContent = 'Show Less';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
            
            animator.style.transition = 'height 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            animator.style.height = animator.scrollHeight + 'px';
            
            expanded.style.opacity = '0';
            setTimeout(() => {
                expanded.style.transition = 'opacity 0.3s ease';
                expanded.style.opacity = '1';
                if (fullDesc && fullDesc.style.display !== 'none') {
                    fullDesc.style.transition = 'opacity 0.3s ease';
                    fullDesc.style.opacity = '1';
                }
            }, 10);
            
            setTimeout(() => {
                animator.style.height = 'auto';
            }, 400);

        } else {
            // -- COLLAPSING --
            expanded.style.display = 'none';
            if (fullDesc && shortDesc && fullDesc.textContent.trim() !== '') {
                shortDesc.style.display = 'block';
                fullDesc.style.display = 'none';
            }
            
            animator.style.height = 'auto';
            const targetHeight = animator.scrollHeight;
            
            animator.style.height = startHeight + 'px';
            expanded.style.display = 'flex';
            
            // Swap immediately to avoid the 'cut frame' void
            if (fullDesc && shortDesc && fullDesc.textContent.trim() !== '') {
                shortDesc.style.display = 'block';
                fullDesc.style.display = 'none';
            }
            
            void animator.offsetHeight;
            
            animator.style.transition = 'height 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            animator.style.height = targetHeight + 'px';
            
            expanded.style.transition = 'opacity 0.2s ease';
            expanded.style.opacity = '0';
            
            if (btnText) btnText.textContent = 'View Full Details';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            
            setTimeout(() => {
                expanded.style.display = 'none';
                animator.style.transition = 'none';
                animator.style.height = 'auto';
            }, 320);
        }
    };

    window.contactMTO = function() {
        showToast('Connecting you to Municipal Tourism Office...');
        // In a real app, this would open a phone dialer or chat:
        // window.location.href = 'tel:+639123456789';
        setTimeout(() => {
            alert("MTO Contact Info:\\nPhone: +63 912 345 6789\\nEmail: tourism@elyu.gov.ph\\n\\nThey can arrange a local guide or habal-habal for your trip!");
        }, 500);
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