<div class="header-container" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 1000; background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%); padding: 16px; display: flex; align-items: center; justify-content: space-between;">
    <button class="btn-icon" onclick="navigateTo('saved_trips')" style="background: rgba(255,255,255,0.15); border: none; width: 40px; height: 40px; border-radius: 50%; color: white; backdrop-filter: blur(10px); cursor: pointer; display: flex; align-items: center; justify-content: center;">
        <i class="fa-solid fa-arrow-left"></i>
    </button>
    <h2 id="trip-map-title" style="margin: 0; color: white; font-size: 16px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">Trip Route</h2>
    <div style="width: 40px;"></div> <!-- Spacer -->
</div>

<div id="trip-map" style="width: 100%; height: 100vh; background: #F2F2F7;"></div>

<div id="trip-info-card" style="position: absolute; bottom: 80px; left: 16px; right: 16px; z-index: 1000; background: var(--glass-bg); backdrop-filter: blur(16px); border: 1px solid var(--glass-border); border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: none;">
    <h3 id="trip-info-name" style="margin: 0 0 4px; font-size: 18px; font-weight: 700; color: white;"></h3>
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
    </div>
</div>

<script>
(function() {
    var backendUrl = window.backendUrl || 'http://localhost:8000';
    var tripMap;

    function initTripMap() {
        const style = {
            "version": 8,
            "glyphs": "https://basemaps.cartocdn.com/gl/positron-gl-style/fonts/{fontstack}/{range}.pbf",
            "sources": {
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

            try {
                const res = await fetch('assets/geojson/la-union.geojson');
                if (res.ok) {
                    const geojson = await res.json();
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
                        tripMap.addSource('mask', {
                            'type': 'geojson',
                            'data': { "type": "Feature", "geometry": { "type": "Polygon", "coordinates": coordinates } }
                        });
                        tripMap.addLayer({
                            'id': 'mask-layer',
                            'type': 'fill',
                            'source': 'mask',
                            'paint': { 'fill-color': '#F2F2F7', 'fill-opacity': 1 }
                        });
                        
                        let luBounds = new maplibregl.LngLatBounds();
                        if (geojson.type === 'Polygon') {
                            geojson.coordinates[0].forEach(coord => luBounds.extend(coord));
                        } else if (geojson.type === 'MultiPolygon') {
                            geojson.coordinates.forEach(poly => poly[0].forEach(coord => luBounds.extend(coord)));
                        }
                        tripMap.setMaxBounds(luBounds);
                        tripMap.fitBounds(luBounds, { padding: 20 });
                    }
                }
            } catch (e) {
                console.error("Mask error", e);
            }

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
            if (data.itineraries) {
                const trip = data.itineraries.find(t => t.id == tripId);
                if (trip) {
                    document.getElementById('trip-map-title').textContent = trip.title;
                    document.getElementById('trip-info-name').textContent = trip.title;
                    
                    if (trip.items && trip.items.length > 0) {
                        document.getElementById('trip-info-desc').textContent = `Route preview for ${trip.items.length} destination(s).`;
                        document.getElementById('trip-info-card').style.display = 'block';
                        plotTrip(trip.items);
                    } else {
                        if (typeof showToast === 'function') showToast("This trip has no destinations yet.");
                    }
                }
            }
        })
        .catch(e => console.error("Failed to load trip", e));
    }

    function plotTrip(items) {
        const coords = [];
        const markerPoints = [];
        const bounds = new maplibregl.LngLatBounds();

        items.forEach((item, idx) => {
            const dest = item.destination;
            if (dest) {
                const lat = parseFloat(dest.lat || dest.latitude);
                const lng = parseFloat(dest.lng || dest.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    coords.push(`${lng},${lat}`);
                    markerPoints.push([lng, lat]);
                    bounds.extend([lng, lat]);

                    // Add Numbered Marker
                    const el = document.createElement('div');
                    el.innerHTML = `
                        <div style="background: #38bdf8; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; border: 3px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
                            ${idx + 1}
                        </div>
                        <div style="background: rgba(0,0,0,0.8); color: white; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; white-space: nowrap; margin-top: 4px; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 2px 5px rgba(0,0,0,0.5); text-align: center;">
                            ${dest.name}
                        </div>
                    `;
                    el.style.display = 'flex';
                    el.style.flexDirection = 'column';
                    el.style.alignItems = 'center';

                    new maplibregl.Marker({ element: el, anchor: 'top' })
                        .setLngLat([lng, lat])
                        .addTo(tripMap);
                }
            }
        });

        if (markerPoints.length > 0) {
            if (coords.length > 1) {
                fetch(`https://router.project-osrm.org/route/v1/driving/${coords.join(';')}?overview=full&geometries=geojson`)
                .then(r => r.json())
                .then(routeData => {
                    if (routeData.code === 'Ok' && routeData.routes.length > 0) {
                        const route = routeData.routes[0];
                        const geojson = route.geometry;
                        
                        document.getElementById('trip-info-distance').textContent = (route.distance / 1000).toFixed(1) + ' km';
                        document.getElementById('trip-info-time').textContent = Math.round(route.duration / 60) + ' mins';

                        tripMap.addSource('route', { 'type': 'geojson', 'data': geojson });
                        tripMap.addLayer({
                            'id': 'route-line',
                            'type': 'line',
                            'source': 'route',
                            'layout': { 'line-join': 'round', 'line-cap': 'round' },
                            'paint': { 'line-color': '#38bdf8', 'line-width': 5, 'line-opacity': 0.8, 'line-dasharray': [2, 2] }
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
                }).catch(e => console.error("Trip routing error", e));
            } else {
                document.getElementById('trip-info-distance').textContent = "N/A";
                document.getElementById('trip-info-time').textContent = "N/A";
            }
        }
    }

    initTripMap();
})();
</script>
