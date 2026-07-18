<!-- Map View -->
<?php
$pageTitle = 'Explore Map';
$activeTab = 'map';

$municipalityImages = [];
$imgDir = __DIR__ . '/../../../../backend/storage/app/public/municipalities';
if (is_dir($imgDir)) {
    $munis = scandir($imgDir);
    foreach ($munis as $muni) {
        if ($muni === '.' || $muni === '..') continue;
        if (is_dir("$imgDir/$muni")) {
            $files = scandir("$imgDir/$muni");
            foreach ($files as $f) {
                $fLower = strtolower($f);
                if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/', $fLower)) {
                    $municipalityImages[strtoupper($muni)][] = $f;
                }
            }
        }
    }
}
?>

<script>
window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;

window.getFareFromMatrix = function(vehicleType, distanceKm) {
    if (!window.fareData) return null;
    // Map frontend vehicle names to fare data keys
    const keyMap = {
        'Tricycle': 'tricycle', 'Jeepney': 'jeepney', 'Bus': 'private_bus',
        'Taxi': 'taxi', 'Own Car': 'own_car',
        'bus': 'private_bus', 'jeepney': 'jeepney', 'tricycle': 'tricycle',
        'taxi': 'taxi', 'own_car': 'own_car',
        'lutrampco': 'lutrampco', 'private_bus': 'private_bus',
        'mini_bus': 'mini_bus', 'van': 'van',
    };
    const key = keyMap[vehicleType];
    if (!key) return null;
    // For own_car and taxi — not in DB, return null to use formula fallback
    if (key === 'own_car' || key === 'taxi') return null;
    const fareEntry = window.fareData[key];
    const rates = Array.isArray(fareEntry.rates) ? fareEntry.rates : Object.values(fareEntry.rates);
    if (!rates || rates.length === 0) return null;
    
    let match = null;
    for (let i = rates.length - 1; i >= 0; i--) {
        const rate = rates[i];
        if (rate && rate.distance_km != null && parseFloat(rate.distance_km) <= distanceKm) {
            match = rate;
            break;
        }
    }
    if (!match) match = rates.find(r => r && r.regular_fare != null);
    if (!match || match.regular_fare == null) return null;
    
    return parseFloat(match.regular_fare);
};
</script>

<div class="map-container animate-fade-in">
    <!-- Map Container -->
    <div id="tourist-map"></div>

    <!-- Floating Search & Filters -->
    <div class="map-floating-header stagger-1">
        <div style="position:relative;">
            <div class="map-search" style="position:relative;">
                <i class="fa-solid fa-location-arrow"></i>
                <input type="text" id="map-search-input" placeholder="Search places on map..." autocomplete="off">
            </div>
            <div id="map-search-suggestions" class="map-search-suggestions"></div>
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
    <div class="btn-layer-toggle animate-slide-up" id="btn-layer-toggle" style="position: absolute; bottom: calc(340px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-layer-group"></i>
    </div>

    <!-- 3D Mode Button -->
    <div class="btn-3d-view animate-slide-up" id="btn-3d-view" style="position: absolute; bottom: calc(280px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-cube"></i>
    </div>


    <!-- Bottom Sheet (hidden by default) -->
    <div class="bottom-sheet" id="place-details-sheet">
        <div class="sheet-drag-handle" id="place-drag-handle"><span class="sheet-drag-dot"></span></div>
        <div class="draggable-content" id="place-details-scroll">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <div style="flex:1; min-width:0;">
                <h3 class="sheet-title" id="sheet-title">Destination Name</h3>
                <p class="sheet-location" style="margin-bottom: 8px;"><i class="fa-solid fa-location-dot"></i><span id="sheet-location">Location details</span></p>
                <div id="sheet-status-badge" style="display:none; margin-top: 4px; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #fff; background: #38bdf8; width: max-content;"></div>
                <div id="sheet-open-badge" style="display:none; margin-top: 4px; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; width: max-content;"></div>
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
            <div id="vehicle-accessibility-warning" style="display:none; background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); border-radius:12px; padding:12px; margin-bottom:12px;">
                <div style="display:flex; align-items:flex-start; gap:8px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444; margin-top:2px;"></i>
                    <div>
                        <h5 style="margin:0 0 4px 0; font-size:12px; font-weight:700; color:#ef4444; text-transform:uppercase; letter-spacing:0.5px;">Inaccessible by Private Car</h5>
                        <p style="margin:0; font-size:11px; color:rgba(248,250,252,0.8); line-height:1.4;">Prepare to hike or use specialized local transport to reach this destination.</p>
                    </div>
                </div>
            </div>
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
                .btn-details-active:active { transform: scale(0.97); }
                .selected-vehicle { border-color: #38bdf8 !important; background: rgba(56,189,248,0.08) !important; }
                .selected-vehicle .vehicle-check { border-color: #38bdf8 !important; background: #38bdf8; }
                .selected-vehicle .vehicle-check i { opacity: 1 !important; }
                .disabled-vehicle { opacity: 0.35; pointer-events: none; }
            </style>

            <!-- Expanded Info (hidden initially) -->
            <div id="expanded-details" style="display:none; flex-direction:column; margin-top:16px; padding-top:16px; border-top:1px dashed rgba(255,255,255,0.1);">
                <h4 style="margin:0 0 10px; font-size:11px; font-weight:800; letter-spacing:1px; color:rgba(148,163,184,0.7); text-transform:uppercase;">Location Info</h4>

                <div class="map-info-row">
                    <span class="map-info-label">
                        <i class="fa-solid fa-location-arrow"></i>
                        Distance
                    </span>
                    <span class="map-info-value" id="sheet-distance">Calculating...</span>
                </div>

                <div class="map-info-row" id="sheet-hours-row" style="display:none;">
                    <span class="map-info-label">
                        <i class="fa-regular fa-clock"></i>
                        Hours
                    </span>
                    <span class="map-info-value" id="sheet-hours">--</span>
                </div>

                <div style="margin-top: 10px; background: rgba(255,255,255,0.03); padding: 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <h5 style="margin: 0 0 6px; font-size: 10px; color: rgba(148,163,184,0.7); text-transform: uppercase; letter-spacing: 0.5px;"><i class="fa-solid fa-map" style="margin-right: 4px;"></i> Route Guide</h5>
                    <p id="sheet-manual-guide" style="margin: 0; font-size: 13px; color: #e2e8f0; line-height: 1.5;"></p>
                </div>
                <div style="margin-top: 10px; background: rgba(255,255,255,0.03); padding: 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <h5 style="margin: 0 0 6px; font-size: 10px; color: rgba(148,163,184,0.7); text-transform: uppercase; letter-spacing: 0.5px;"><i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Tour Guide Notice</h5>
                    <p style="margin: 0; font-size: 12px; color: #f59e0b; line-height: 1.5;">Some destinations may require a tour guide for entry or navigation. The system only provides informational notices about this requirement; it does not offer, book, or arrange tour guide services directly.</p>
                </div>
            </div> <!-- End sheet-desc-animator -->

            <button id="btn-view-details" class="btn-details-active" onclick="window.toggleFullDetails()" style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.15); border-radius:12px; width:100%; color:#38bdf8; font-size:13px; font-weight:700; padding:12px 0; margin-top:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:background 0.2s, transform 0.1s;">
                <span id="details-btn-text">View Full Details</span>
                <i class="fa-solid fa-chevron-down" id="details-chevron" style="transition:transform 0.3s ease;"></i>
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="sheet-btn-row" style="display: flex; gap: 8px;">
            <button id="btn-add-itinerary" onclick="window.addToItinerary()" style="flex: 1; padding: 0 10px; font-size: 13px; height: 46px; background: linear-gradient(135deg, #34c759, #00a844); border: none; border-radius: 14px; color: #fff; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: opacity 0.2s, transform 0.1s;">
                <i class="fa-solid fa-calendar-plus"></i> Add to Itinerary
            </button>
            <button id="sheet-fav-btn" onclick="window.toggleMapFavorite(this)" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.1); width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.4); font-size:16px; cursor:pointer; flex-shrink:0; transition:color 0.2s, background 0.2s;">
                <i class="fa-solid fa-heart"></i>
            </button>
        </div>

        <!-- Testimonies & Policy Recommendations Section -->
        <div id="sheet-testimonies-section" style="display:none; margin-top:24px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.08); text-align: left;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <h4 style="margin:0; font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#fff;">🗣️ Tourist Testimonies</h4>
                <button onclick="window.openWriteTestimonyModal()" style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.25); color:#38bdf8; font-size:11px; font-weight:700; padding:6px 12px; border-radius:8px; cursor:pointer;">
                    Write Review
                </button>
            </div>

            <!-- Aggregated Ratings / Crowd / Cleanliness / Safety metrics display -->
            <div id="testimonies-summary-metrics" style="display:none; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:14px; padding:12px; margin-bottom:16px; font-size:11px; color:rgba(255,255,255,0.7);">
                <!-- Populated dynamically via JS -->
            </div>

            <!-- List of testimonies -->
            <div id="testimonies-list-container" style="display:flex; flex-direction:column; gap:10px;">
                <div style="font-size:12px; color:rgba(255,255,255,0.4); text-align:center; padding:10px;">No testimonies yet. Be the first to share!</div>
            </div>
        </div>

    </div>
</div>




</div>

<!-- Itinerary Add Confirmation Dialog -->
<div id="itin-add-confirm" onclick="if(event.target===this)window.closeAddConfirm()" style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:99999; display:flex; align-items:center; justify-content:center; opacity:0; pointer-events:none; transition:opacity 0.3s ease; background:rgba(0,0,0,0.55); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px);">
    <div style="background:linear-gradient(135deg, #1a2a4a 0%, #0f172a 100%); border:1px solid rgba(255,255,255,0.08); border-radius:24px; padding:32px 28px 24px; margin:0 24px; width:100%; max-width:320px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.6); transform:scale(0.85); transition:transform 0.35s cubic-bezier(0.16,1,0.3,1);">
        <div style="width:68px; height:68px; border-radius:50%; background:rgba(52,199,89,0.12); border:2px solid rgba(52,199,89,0.25); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <i class="fa-solid fa-check" style="font-size:30px; color:#34c759;"></i>
        </div>
        <h3 style="margin:0 0 6px; font-size:20px; font-weight:800; color:#f8fafc; letter-spacing:-0.3px;">Added to Itinerary!</h3>
        <p style="margin:0 0 24px; font-size:14px; color:rgba(148,163,184,0.8); line-height:1.5;" id="itin-add-confirm-name"></p>
        <button onclick="window.viewItinerary()" style="width:100%; padding:14px; border:none; border-radius:14px; background:linear-gradient(135deg,#007AFF,#0055FF); color:#fff; font-size:15px; font-weight:800; cursor:pointer; margin-bottom:10px; box-shadow:0 4px 16px rgba(0,122,255,0.3);">
            <i class="fa-solid fa-list"></i> View Itinerary
        </button>
        <button onclick="window.closeAddConfirm()" style="width:100%; padding:12px; border:1px solid rgba(255,255,255,0.08); border-radius:12px; background:rgba(255,255,255,0.04); color:rgba(148,163,184,0.9); font-size:14px; font-weight:700; cursor:pointer;">
            Continue Exploring
        </button>
    </div>
</div>

<!-- Write Testimony & Policy Recommendation Modal -->
<div id="testimony-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:99999; display:flex; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px);">
    <div style="background:linear-gradient(135deg, #1e293b, #0f172a); border:1px solid rgba(255,255,255,0.1); border-radius:24px; padding:24px; width:100%; max-width:380px; max-height:85vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,0.3); text-align:left; box-sizing:border-box;">
        <h3 style="margin:0 0 4px; color:#fff; font-size:18px; font-weight:800;">Review Destination</h3>
        <p style="font-size:12px; color:rgba(255,255,255,0.6); margin-bottom:16px;">Help the tourism office and fellow travellers by sharing your site testimony and policy recommendations.</p>

        <form id="testimony-form" onsubmit="window.submitTestimony(event)">
            <input type="hidden" id="testimony-spot-id">

            <!-- Star Rating selection -->
            <div style="margin-bottom:14px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Your Rating (1 to 5 Stars):</label>
                <div style="display:flex; gap:8px; font-size:24px; color:#f59e0b;">
                    <i class="fa-solid fa-star star-btn" data-star="1" style="cursor:pointer;" onclick="window.setStarRating(1)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="2" style="cursor:pointer;" onclick="window.setStarRating(2)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="3" style="cursor:pointer;" onclick="window.setStarRating(3)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="4" style="cursor:pointer;" onclick="window.setStarRating(4)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="5" style="cursor:pointer;" onclick="window.setStarRating(5)"></i>
                </div>
                <input type="hidden" id="testimony-rating" value="5">
            </div>

            <!-- Crowd, Cleanliness, Safety parameters -->
            <div style="display:flex; gap:8px; margin-bottom:14px;">
                <!-- Crowd Level -->
                <div style="flex:1;">
                    <label style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:4px;">Crowd:</label>
                    <select id="testimony-crowd" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:6px; color:#fff; font-size:11px; font-weight:600; box-sizing:border-box;">
                        <option value="low" style="background:#1e293b;">Low</option>
                        <option value="medium" style="background:#1e293b;" selected>Medium</option>
                        <option value="high" style="background:#1e293b;">High</option>
                    </select>
                </div>
                <!-- Cleanliness -->
                <div style="flex:1;">
                    <label style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:4px;">Clean:</label>
                    <select id="testimony-cleanliness" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:6px; color:#fff; font-size:11px; font-weight:600; box-sizing:border-box;">
                        <option value="clean" style="background:#1e293b;" selected>Clean</option>
                        <option value="moderate" style="background:#1e293b;">Moderate</option>
                        <option value="dirty" style="background:#1e293b;">Dirty</option>
                    </select>
                </div>
                <!-- Safety -->
                <div style="flex:1;">
                    <label style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:4px;">Safety:</label>
                    <select id="testimony-safety" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:6px; color:#fff; font-size:11px; font-weight:600; box-sizing:border-box;">
                        <option value="safe" style="background:#1e293b;" selected>Safe</option>
                        <option value="moderate" style="background:#1e293b;">Moderate</option>
                        <option value="unsafe" style="background:#1e293b;">Unsafe</option>
                    </select>
                </div>
            </div>

            <!-- Testimony description -->
            <div style="margin-bottom:14px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Your Testimony:</label>
                <textarea id="testimony-comment" placeholder="Describe your experience during this site visit..." style="width:100%; height:60px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:10px; color:#fff; font-size:12px; font-family:inherit; resize:none; box-sizing:border-box;" required></textarea>
            </div>

            <!-- Policy Recommendation -->
            <div style="margin-bottom:20px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Policy Recommendations (Optional):</label>
                <textarea id="testimony-policy" placeholder="Any suggestions or recommendations for safety, cleanliness, or crowd control policies?..." style="width:100%; height:60px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:10px; color:#fff; font-size:12px; font-family:inherit; resize:none; box-sizing:border-box;"></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; padding:14px; font-size:14px; margin-bottom:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; border-radius:12px; font-weight:800; cursor:pointer;">
                Submit Feedback
            </button>
        </form>
        <button style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:transparent; color:rgba(255,255,255,0.5); font-size:13px; font-weight:600; cursor:pointer;" onclick="window.closeWriteTestimonyModal()">Cancel</button>
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
        const _backendBase = window.backendUrl || '';
        const mapDataPromise = fetch(_backendBase + '/api/public/map', {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.json()).catch(e => console.error("Map fetch error:", e));

        const regionDataPromise = fetch('assets/la_union.json').then(r => r.json()).catch(e => console.error("Region fetch error:", e));

        // Fetch fare rates from DB
        const faresPromise = fetch(_backendBase + '/api/public/fares', {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => { window.fareData = d.fares || {}; }).catch(e => console.error("Fares fetch error:", e));

        const style = {
            "version": 8,
            "glyphs": "https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",
            "sources": {
                "satellite": {
                    "type": "raster",
                    "tiles": ["https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"],
                    "tileSize": 256
                },
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
                    "paint": {
                        "background-color": "#0a0f1c"
                    }
                },
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

        // Suppress tile load errors (harmless — tiles fall back gracefully)
        window.mapInstance.on('error', (e) => {
            if (e && e.error && e.error.status === 404) return;
            if (e && e.source && e.source.type === 'raster') return;
        });

        // Add Zoom Controls (+ and -)
        window.mapInstance.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'bottom-right');

        // Add 3D Terrain and Region Mask
        window.mapInstance.on('load', async () => {
            window.mapInstance.setTerrain({ "source": "terrain", "exaggeration": 1.5 });
            try {
                window.mapInstance.setSky({
                    'sky-color': '#0a0f1c',
                    'horizon-color': '#0a0f1c',
                    'sky-horizon-blend': 0,
                    'horizon-fog-blend': 0,
                    'fog-color': '#0a0f1c',
                    'atmosphere-blend': 0
                });
            } catch (skyErr) { console.error("setSky error:", skyErr); }

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
                        /* 
                        window.mapInstance.addLayer({
                            'id': 'mask-layer',
                            'type': 'fill',
                            'source': 'mask',
                            'paint': { 'fill-color': '#F2F2F7', 'fill-opacity': 1 }
                        });
                        */
                    }
                    
                    let bounds = new maplibregl.LngLatBounds();
                    if (geojson.type === 'Polygon') {
                        geojson.coordinates[0].forEach(coord => bounds.extend(coord));
                    } else if (geojson.type === 'MultiPolygon') {
                        geojson.coordinates.forEach(poly => poly[0].forEach(coord => bounds.extend(coord)));
                    }
                    // window.mapInstance.setMaxBounds(bounds);
                }
            } catch(e) { console.error("Failed to slice region:", e); }

            // ── TOURIST ZONES ────────────────────────────────────────────────
            try {
                const muniGeoJsonPromise = fetch('assets/la_union_municipalities.json').then(r => r.json()).catch(() => null);
                const muniApiPromise = fetch((window.backendUrl || '') + '/api/public/municipalities', {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json()).catch(() => null);

                const [muniGeoJson, muniApi] = await Promise.all([muniGeoJsonPromise, muniApiPromise]);

                if (muniGeoJson && muniGeoJson.features) {
                    // Merge live spot counts from API
                    if (muniApi && muniApi.municipalities) {
                        const spotMap = {};
                        muniApi.municipalities.forEach(m => {
                            spotMap[m.name.toLowerCase()] = m.spot_count || 0;
                        });
                        muniGeoJson.features.forEach(f => {
                            const key = f.properties.name.toLowerCase();
                            if (spotMap[key] !== undefined) {
                                f.properties.spot_count = spotMap[key];
                            }
                        });
                    }

                    window.muniGeoJson = muniGeoJson;

                    // Add zone fill layer (below markers, above base tiles)
                    window.mapInstance.addSource('municipality-zones', {
                        type: 'geojson',
                        data: muniGeoJson,
                        generateId: true  // needed for feature-state hover
                    });
                    window.mapInstance.addLayer({
                        id: 'municipality-fill',
                        type: 'fill',
                        source: 'municipality-zones',
                        layout: { visibility: 'visible' },
                        paint: {
                            'fill-color': ['get', 'color'],
                            'fill-opacity': 0.10
                        }
                    });

                    // Add zone border layer
                    window.mapInstance.addLayer({
                        id: 'municipality-borders',
                        type: 'line',
                        source: 'municipality-zones',
                        layout: { visibility: 'visible' },
                        paint: {
                            'line-color': ['get', 'color'],
                            'line-opacity': 0.5,
                            'line-width': 1.5,
                            'line-dasharray': [3, 2]
                        }
                    });

                    // Add municipality name labels
                    window.mapInstance.addLayer({
                        id: 'municipality-labels',
                        type: 'symbol',
                        source: 'municipality-zones',
                        layout: {
                            visibility: 'visible',
                            'text-field': ['get', 'name'],
                            'text-font': ['Open Sans Bold', 'Arial Unicode MS Bold'],
                            'text-size': 11,
                            'text-anchor': 'center',
                            'text-allow-overlap': false,
                            'text-ignore-placement': false,
                            'symbol-placement': 'point'
                        },
                        paint: {
                            'text-color': ['get', 'color'],
                            'text-halo-color': 'rgba(0,0,0,0.8)',
                            'text-halo-width': 1.5
                        }
                    });

                    // Zone click → show popup
                    window.mapInstance.on('click', 'municipality-fill', (e) => {
                        const props = e.features[0].properties;
                        const spotCount = props.spot_count || 0;
                        const zoneColors = { 'North La Union': '#7c3aed', 'Central La Union': '#0ea5e9', 'South La Union': '#10b981' };
                        const color = props.color || '#0ea5e9';

                        if (window.activePopup) window.activePopup.remove();

                        const popupEl = document.createElement('div');
                        popupEl.style.cssText = 'background:rgba(15,23,42,0.95); border-radius:14px; padding:14px 16px; min-width:180px; border:1px solid rgba(255,255,255,0.1);';
                        popupEl.innerHTML = `
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                <div style="width:10px; height:10px; border-radius:50%; background:${color}; flex-shrink:0;"></div>
                                <span style="font-size:11px; font-weight:700; color:${color}; text-transform:uppercase; letter-spacing:0.8px;">${props.zone}</span>
                            </div>
                            <div style="font-size:16px; font-weight:800; color:#f8fafc; margin-bottom:4px;">${props.name}</div>
                            <div style="font-size:12px; color:rgba(148,163,184,0.8); margin-bottom:12px;">
                                <i class="fa-solid fa-location-dot" style="color:${color}; margin-right:4px;"></i>
                                ${spotCount} tourist spot${spotCount !== 1 ? 's' : ''}
                            </div>
                            <button id="zone-filter-btn" style="width:100%; padding:8px; border:none; border-radius:10px; background:${color}22; color:${color}; font-size:12px; font-weight:700; cursor:pointer; border:1px solid ${color}44;">
                                <i class="fa-solid fa-filter" style="margin-right:4px;"></i>View spots here
                            </button>
                        `;

                        const coords = e.lngLat;
                        window.activePopup = new maplibregl.Popup({
                            closeButton: false, closeOnClick: true, offset: 0, className: 'smooth-map-popup zone-popup'
                        })
                        .setLngLat(coords)
                        .setDOMContent(popupEl)
                        .addTo(window.mapInstance);

                        // Filter by municipality name
                        popupEl.querySelector('#zone-filter-btn').addEventListener('click', () => {
                            window.activePopup.remove();
                            const muniName = props.name;
                            const filtered = (window.allMapLocations || []).filter(loc =>
                                loc.municipality && loc.municipality.toLowerCase().includes(muniName.toLowerCase())
                            );
                            window.renderMarkers(filtered.length ? filtered : window.allMapLocations);
                            if (filtered.length > 0) {
                                const bounds = new maplibregl.LngLatBounds();
                                filtered.forEach(loc => {
                                    if (loc.lat && loc.lng) bounds.extend([parseFloat(loc.lng), parseFloat(loc.lat)]);
                                });
                                window.mapInstance.fitBounds(bounds, { padding: 60, duration: 800, maxZoom: 14 });
                                showToast(`Showing ${filtered.length} spot${filtered.length !== 1 ? 's' : ''} in ${muniName}`);
                            } else {
                                showToast(`No spots found in ${muniName}`);
                            }
                        });
                    });

                    // Hover cursor
                    window.mapInstance.on('mouseenter', 'municipality-fill', () => {
                        window.mapInstance.getCanvas().style.cursor = 'pointer';
                    });
                    window.mapInstance.on('mouseleave', 'municipality-fill', () => {
                        window.mapInstance.getCanvas().style.cursor = '';
                    });

                    // Hover highlight
                    window.mapInstance.addLayer({
                        id: 'municipality-hover',
                        type: 'fill',
                        source: 'municipality-zones',
                        layout: { visibility: 'visible' },
                        paint: {
                            'fill-color': ['get', 'color'],
                            'fill-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 0.22, 0]
                        }
                    });

                    let hoveredId = null;
                    window.mapInstance.on('mousemove', 'municipality-fill', (e) => {
                        if (e.features.length > 0) {
                            if (hoveredId !== null) {
                                window.mapInstance.setFeatureState({ source: 'municipality-zones', id: hoveredId }, { hover: false });
                            }
                            hoveredId = e.features[0].id;
                            window.mapInstance.setFeatureState({ source: 'municipality-zones', id: hoveredId }, { hover: true });
                        }
                    });
                    window.mapInstance.on('mouseleave', 'municipality-fill', () => {
                        if (hoveredId !== null) {
                            window.mapInstance.setFeatureState({ source: 'municipality-zones', id: hoveredId }, { hover: false });
                        }
                        hoveredId = null;
                    });

                    window.zonesLoaded = true;
                }
            } catch (zoneErr) { console.error('Zone render error:', zoneErr); }
            // ── END TOURIST ZONES ────────────────────────────────────────────

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
                            const pLat = place.lat || place.latitude;
                            const pLng = place.lng || place.longitude;
                            if (pLat && pLng && !isNaN(parseFloat(pLat)) && !isNaN(parseFloat(pLng))) {
                                window.mapInstance.flyTo({ center: [parseFloat(pLng), parseFloat(pLat)], zoom: 14, offset: [0, -160] });
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
                            const pLat = place.lat || place.latitude;
                            const pLng = place.lng || place.longitude;
                            if (pLat && pLng && !isNaN(parseFloat(pLat)) && !isNaN(parseFloat(pLng))) {
                                window.mapInstance.flyTo({ center: [parseFloat(pLng), parseFloat(pLat)], zoom: 14, offset: [0, -160] });
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
            
            const cat = loc.category || 'Other';
            let iconClass = 'fa-location-dot';
            const catLower = cat.toLowerCase();
            
            if (catLower.includes('beach') || catLower.includes('surf')) {
                iconClass = 'fa-umbrella-beach';
            } else if (catLower.includes('mountain') || catLower.includes('nature') || catLower.includes('park')) {
                iconClass = 'fa-mountain';
            } else if (catLower.includes('historic') || catLower.includes('culture') || catLower.includes('museum')) {
                iconClass = 'fa-landmark';
            } else if (catLower.includes('water') || catLower.includes('fall') || catLower.includes('river')) {
                iconClass = 'fa-water';
            } else if (catLower.includes('adventure')) {
                iconClass = 'fa-person-hiking';
            } else if (catLower.includes('farm')) {
                iconClass = 'fa-tractor';
            } else if (catLower.includes('religio') || catLower.includes('church')) {
                iconClass = 'fa-place-of-worship';
            } else if (catLower.includes('hotel') || catLower.includes('resort') || catLower.includes('stay')) {
                iconClass = 'fa-bed';
            } else if (catLower.includes('food') || catLower.includes('restaurant') || catLower.includes('cafe')) {
                iconClass = 'fa-utensils';
            }

            let catColor = '#888';
            if (loc.classification_status === 'EXIST') {
                catColor = '#34c759';
            } else if (loc.classification_status === 'EMERGE') {
                catColor = '#38bdf8';
            } else if (loc.classification_status === 'POTENTIAL') {
                catColor = '#f59e0b';
            }

            const container = document.createElement('div');
            
            const el = document.createElement('div');
            el.className = 'custom-map-marker';
            el.style.width = '32px';
            el.style.height = '32px';
            el.style.backgroundColor = '#FFFFFF';
            el.style.border = `2px solid ${catColor}`;
            el.style.borderRadius = '50%';
            el.style.display = 'flex';
            el.style.alignItems = 'center';
            el.style.justifyContent = 'center';
            el.style.color = catColor;
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
                
                popupContent.addEventListener('click', () => {
                    const cp = window.mapInstance.getCenter();
                    const cz = window.mapInstance.getZoom();
                    window.mapInstance.flyTo({ center: [parseFloat(loc.lng), parseFloat(loc.lat)], zoom: Math.max(cz, 14), offset: [0, -180], duration: 400 });
                    window.openSheet(loc);
                });

                window.activePopup = new maplibregl.Popup({
                    closeButton: false, closeOnClick: false, offset: 15, className: 'smooth-map-popup'
                })
                .setLngLat([parseFloat(loc.lng), parseFloat(loc.lat)])
                .setDOMContent(popupContent)
                .addTo(window.mapInstance);

                const popupEl = window.activePopup.getElement();
                if(popupEl) popupEl.style.zIndex = 9999;
                
                const cp = window.mapInstance.getCenter();
                const cz = window.mapInstance.getZoom();
                window.mapInstance.flyTo({ center: [parseFloat(loc.lng), parseFloat(loc.lat)], zoom: Math.max(cz, 14), offset: [0, -180], duration: 1000 });
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
            if (typeof window.fastLocation === 'function') {
                const fast = await window.fastLocation();
                if (fast) return { coords: { latitude: fast.lat, longitude: fast.lng, accuracy: 5000, source: fast.source } };
            }
            if (window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform()) {
                try {
                    const Geolocation = (window.Capacitor.Plugins && window.Capacitor.Plugins.Geolocation) || 
                                      (window.Capacitor.registerPlugin ? window.Capacitor.registerPlugin('Geolocation') : null);
                    
                    if (!Geolocation) throw new Error("Geolocation plugin not loaded in Capacitor");

                    const perm = await Geolocation.checkPermissions();
                    if (perm.location !== 'granted') {
                        const req = await Geolocation.requestPermissions();
                        if (req.location !== 'granted') throw new Error('Permission denied by user');
                    }
                    const pos = await Geolocation.getCurrentPosition({ enableHighAccuracy: false, maximumAge: 60000, timeout: 50000 });
                    return pos;
                } catch (e) {
                    throw new Error("Native location error: " + e.message);
                }
            } else {
                return new Promise((resolve, reject) => {
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => resolve(position), 
                            () => reject(new Error("Location denied by browser")), 
                            { enableHighAccuracy: false, timeout: 50000, maximumAge: 60000 }
                        );
                    } else {
                        reject(new Error("Geolocation not supported"));
                    }
                });
            }
        };

        const searchInput = document.getElementById('map-search-input');
        const suggestionsEl = document.getElementById('map-search-suggestions');

        function getCatColor(cat) {
            const c = (cat || '').toLowerCase();
            if (c.includes('beach') || c.includes('surf')) return '#0ea5e9';
            if (c.includes('mountain') || c.includes('nature') || c.includes('park')) return '#10b981';
            if (c.includes('historic') || c.includes('culture') || c.includes('museum')) return '#d97706';
            if (c.includes('water') || c.includes('fall') || c.includes('river')) return '#3b82f6';
            if (c.includes('adventure')) return '#ef4444';
            if (c.includes('farm')) return '#84cc16';
            if (c.includes('religio') || c.includes('church')) return '#8b5cf6';
            if (c.includes('hotel') || c.includes('resort') || c.includes('stay')) return '#f43f5e';
            if (c.includes('food') || c.includes('restaurant') || c.includes('cafe')) return '#f97316';
            return '#007AFF';
        }

        function getCatIcon(cat) {
            const c = (cat || '').toLowerCase();
            if (c.includes('beach') || c.includes('surf')) return 'fa-umbrella-beach';
            if (c.includes('mountain') || c.includes('nature') || c.includes('park')) return 'fa-mountain';
            if (c.includes('historic') || c.includes('culture') || c.includes('museum')) return 'fa-landmark';
            if (c.includes('water') || c.includes('fall') || c.includes('river')) return 'fa-water';
            if (c.includes('adventure')) return 'fa-person-hiking';
            if (c.includes('farm')) return 'fa-tractor';
            if (c.includes('religio') || c.includes('church')) return 'fa-place-of-worship';
            if (c.includes('hotel') || c.includes('resort') || c.includes('stay')) return 'fa-bed';
            if (c.includes('food') || c.includes('restaurant') || c.includes('cafe')) return 'fa-utensils';
            return 'fa-location-dot';
        }

        function renderSuggestions(query) {
            if (!suggestionsEl) return;
            const q = (query || '').toLowerCase().trim();
            const locations = window.allMapLocations || [];
            let matches = [];
            if (q.length === 0) {
                // Show some popular/recent spots when search is empty
                matches = locations.slice(0, 5);
            } else {
                matches = locations.filter(loc => {
                    const name = (loc.name || '').toLowerCase();
                    const muni = (loc.municipality || '').toLowerCase();
                    const cat = (loc.category || '').toLowerCase();
                    return name.includes(q) || muni.includes(q) || cat.includes(q);
                }).slice(0, 8);
            }
            if (matches.length === 0 || !searchInput || searchInput !== document.activeElement) {
                suggestionsEl.classList.remove('open');
                return;
            }
            suggestionsEl.innerHTML = matches.map(loc => {
                const color = getCatColor(loc.category);
                const icon = getCatIcon(loc.category);
                const muni = loc.municipality ? `<span class="suggestion-municipality">${loc.municipality}</span>` : '';
                const detail = loc.category || '';
                return `
                    <div class="map-search-suggestion-item" data-id="${loc.id}" data-lat="${loc.lat}" data-lng="${loc.lng}">
                        <div class="suggestion-icon" style="background:${color}22; color:${color}; border:1px solid ${color}44;">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <div class="suggestion-info">
                            <div class="suggestion-name">${loc.name} <span class="suggestion-category" style="color:${color};">${detail}</span></div>
                        </div>
                        ${muni}
                    </div>
                `;
            }).join('');
            suggestionsEl.classList.add('open');
        }

        function selectSuggestion(loc) {
            if (!loc) return;
            suggestionsEl.classList.remove('open');
            searchInput.value = loc.name;
            const activeCatEl = document.querySelector('.category-pill.active');
            window.filterCategory('All', document.querySelector('.category-pill'));
            const lat = parseFloat(loc.lat);
            const lng = parseFloat(loc.lng);
            if (!isNaN(lat) && !isNaN(lng) && window.mapInstance) {
                window.mapInstance.flyTo({ center: [lng, lat], zoom: 14, offset: [0, -180], duration: 800 });
                window.openSheet(loc);
            }
        }

        if (searchInput) {
            // Input event — filter map markers AND show suggestions
            searchInput.addEventListener('input', () => {
                const activeCatEl = document.querySelector('.category-pill.active');
                const activeCat = activeCatEl ? activeCatEl.innerText : 'All';
                window.filterCategory(activeCat, activeCatEl || document.querySelector('.category-pill'));
                renderSuggestions(searchInput.value);
            });

            // Focus — show suggestions
            searchInput.addEventListener('focus', () => {
                renderSuggestions(searchInput.value);
            });

            // Click on suggestions via delegation
            if (suggestionsEl) {
                suggestionsEl.addEventListener('click', (e) => {
                    const item = e.target.closest('.map-search-suggestion-item');
                    if (!item) return;
                    const id = item.dataset.id;
                    const loc = (window.allMapLocations || []).find(l => String(l.id) === id);
                    if (loc) selectSuggestion(loc);
                });
            }

            // Keyboard navigation
            searchInput.addEventListener('keydown', (e) => {
                if (!suggestionsEl || !suggestionsEl.classList.contains('open')) return;
                const items = suggestionsEl.querySelectorAll('.map-search-suggestion-item');
                if (items.length === 0) return;
                let activeIdx = Array.from(items).findIndex(el => el.classList.contains('active'));
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = Math.min(activeIdx + 1, items.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = Math.max(activeIdx - 1, 0);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIdx >= 0) {
                        const active = items[activeIdx];
                        const id = active.dataset.id;
                        const loc = (window.allMapLocations || []).find(l => String(l.id) === id);
                        if (loc) selectSuggestion(loc);
                    }
                    return;
                } else {
                    return;
                }
                items.forEach(el => el.classList.remove('active'));
                if (activeIdx >= 0) items[activeIdx].classList.add('active');
            });

            // Blur — hide suggestions with smooth transition
            searchInput.addEventListener('blur', () => {
                setTimeout(() => {
                    if (suggestionsEl) suggestionsEl.classList.remove('open');
                }, 200);
            });
        }

        const locateBtn = document.getElementById('btn-locate-me');
        if (locateBtn) {
            locateBtn.addEventListener('click', () => {
                showToast("Locating...");
                if (window.currentGPSLat && window.currentGPSLng && window.mapInstance) {
                    window.mapInstance.flyTo({ center: [window.currentGPSLng, window.currentGPSLat], zoom: 15 });
                } else {
                    const handleLocation = (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        window.mapInstance.flyTo({ center: [lng, lat], zoom: 15 });
                    };
                    window.getDeviceLocation().then(handleLocation).catch(e => showToast(e.message));
                }
            });
        }

        // Real-time GPS Tracker Hook
        document.addEventListener('gpsUpdated', function(e) {
            const lat = e.detail.lat;
            const lng = e.detail.lng;
            if (window.mapInstance) {
                if (window.userMarker) {
                    window.userMarker.setLngLat([lng, lat]);
                } else {
                    const el = document.createElement('div');
                    el.innerHTML = `<div style="background:#007AFF; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow:0 0 0 5px rgba(0,122,255,0.3);"></div>`;
                    window.userMarker = new maplibregl.Marker({element: el}).setLngLat([lng, lat]).addTo(window.mapInstance);
                }
            }
        });

        // Auto-check on load in case GPS already acquired globally
        setTimeout(() => {
            if (window.currentGPSLat && window.currentGPSLng && window.mapInstance) {
                document.dispatchEvent(new CustomEvent('gpsUpdated', { 
                    detail: { 
                        lat: window.currentGPSLat, 
                        lng: window.currentGPSLng,
                        accuracy: window.currentGPSAccuracy || null,
                        altitude: window.currentGPSAltitude || null,
                        speed: window.currentGPSSpeed || null
                    } 
                }));
            }
        }, 500);

        // Zone Toggle Button
        let zonesVisible = true;
        const btnZone = document.getElementById('btn-zone-toggle');
        const zoneLegend = document.getElementById('zone-legend');
        if (btnZone) {
            btnZone.addEventListener('click', () => {
                zonesVisible = !zonesVisible;
                const vis = zonesVisible ? 'visible' : 'none';
                if (window.mapInstance && window.zonesLoaded) {
                    ['municipality-fill', 'municipality-borders', 'municipality-labels', 'municipality-hover'].forEach(id => {
                        if (window.mapInstance.getLayer(id)) {
                            window.mapInstance.setLayoutProperty(id, 'visibility', vis);
                        }
                    });
                }
                if (zonesVisible) {
                    btnZone.style.background = 'var(--primary-color)';
                    btnZone.style.color = 'white';
                    if (zoneLegend) zoneLegend.style.display = 'block';
                    showToast('Tourist Zones Enabled');
                } else {
                    btnZone.style.background = '#1E3A8A';
                    btnZone.style.color = '#ffffff';
                    if (zoneLegend) zoneLegend.style.display = 'none';
                    showToast('Tourist Zones Hidden');
                }
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
                const sheet = document.getElementById('place-details-sheet');
                if (sheet && sheet.classList.contains('active') && window.currentDestinationForRoute) {
                    const d = window.currentDestinationForRoute;
                    const dLat = d.lat || d.latitude;
                    const dLng = d.lng || d.longitude;
                    if (dLat && dLng) {
                        setTimeout(() => {
                            window.mapInstance.flyTo({ center: [parseFloat(dLng), parseFloat(dLat)], zoom: 14, offset: [0, -180], duration: 600 });
                        }, 1100);
                    }
                }
            });
        }
    }

    // ── Draggable Sheet ──
    const NAV_BAR_HEIGHT = 84;
    const SHEET_REST_Y = 0; // Floating card is already positioned above navbar via CSS
    const SHEET_CLOSE_THRESHOLD = 25; // Distance dragged down before closing

    function initDraggableSheet(sheetId, handleId, onClose) {
        const sheet = document.getElementById(sheetId);
        const handle = document.getElementById(handleId);
        if (!sheet || !handle) return;

        let startY = 0, currentY = 0, initialY = 0, isDragging = false;
        let animFrame = null;
        let isOpen = false;

        function getPeekY() {
            const h = sheet.offsetHeight;
            const peekHeight = 160; // Show about 160px (handle + title + route buttons)
            return Math.max(0, h - peekHeight);
        }

        function applyY(y) {
            let finalY = y;
            if (y > 0) {
                // Allow dragging down normally
            } else if (y < 0) {
                // Premium rubber-banding effect when dragging UP into the sky
                finalY = -Math.sqrt(-y) * 2;
            }
            sheet.style.transform = 'translateY(' + finalY + 'px)';
            currentY = finalY;
        }

        function openSheet(animate) {
            isOpen = true;
            sheet.classList.add('active');
            if (animate) {
                sheet.style.transition = 'transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
            } else {
                sheet.classList.add('sheet-dragging');
            }
            applyY(0);
            if (!animate) sheet.classList.remove('sheet-dragging');
            setTimeout(() => { if (!isDragging) sheet.style.transition = ''; }, 500);
        }

        function closeSheet() {
            isOpen = false;
            sheet.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            sheet.style.transform = 'translateY(calc(100% + 120px))';
            sheet.classList.remove('active');
            if (onClose) onClose();
        }

        function onPointerDown(e) {
            if (!isOpen) return;
            isDragging = true;
            startY = e.clientY || e.touches[0].clientY;
            initialY = currentY;
            sheet.classList.add('sheet-dragging');
            sheet.style.transition = 'none';
        }

        function onPointerMove(e) {
            if (!isDragging) return;
            const clientY = e.clientY || e.touches[0].clientY;
            const delta = clientY - startY;
            if (animFrame) cancelAnimationFrame(animFrame);
            animFrame = requestAnimationFrame(() => applyY(initialY + delta));
        }

        function onPointerUp() {
            if (!isDragging) return;
            isDragging = false;
            sheet.classList.remove('sheet-dragging');
            if (animFrame) cancelAnimationFrame(animFrame);

            sheet.style.transition = 'transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';

            const peekY = getPeekY();
            const delta = currentY - initialY;

            if (delta > 30) {
                // User dragged DOWN
                if (initialY < 50 && peekY > 20) {
                    // Start from OPEN -> snap to PEEK
                    applyY(peekY);
                } else {
                    // Already at PEEK or dragging down aggressively -> CLOSE
                    closeSheet();
                }
            } else if (delta < -30) {
                // User dragged UP
                applyY(0); // Snap to OPEN
            } else {
                // Drag distance too short, snap back to nearest state
                if (peekY > 0 && Math.abs(currentY - peekY) < Math.abs(currentY - 0)) {
                    applyY(peekY);
                } else {
                    applyY(0);
                }
            }
            
            setTimeout(() => { if (!isDragging) sheet.style.transition = ''; }, 500);
        }

        handle.addEventListener('mousedown', onPointerDown);
        document.addEventListener('mousemove', onPointerMove);
        document.addEventListener('mouseup', onPointerUp);
        handle.addEventListener('touchstart', onPointerDown, { passive: true });
        document.addEventListener('touchmove', onPointerMove, { passive: true });
        document.addEventListener('touchend', onPointerUp);

        sheet.addEventListener('mousedown', (e) => {
            // Prevent dragging from bubbling up if not on handle
            if (!e.target.closest('#' + handleId) && sheet.classList.contains('active')) {
               isOpen = true; // ensure it's marked open if someone adds active class manually
            }
        });

        sheet.openSheet = openSheet;
        sheet.closeSheet = closeSheet;
    }

    initDraggableSheet('place-details-sheet', 'place-drag-handle');


    window.toggleMapFavorite = function(element) {
        if (!window.currentDestinationForRoute) return;
        const destId = window.currentDestinationForRoute.id;
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) {
            if (typeof showToast === 'function') showToast('Please login to save places');
            return;
        }
        
        // Save original state for reverting
        const originalColor = element.style.color;
        const wasRed = originalColor === 'rgb(255, 59, 48)' || originalColor === '#ff3b30';
        
        // 1. INSTANT OPTIMISTIC UPDATE (Zero Delay)
        // Trigger pop animation
        element.classList.remove('heart-pop-anim');
        void element.offsetWidth; // trigger reflow
        element.classList.add('heart-pop-anim');

        if (wasRed) {
            element.style.color = 'rgba(255,255,255,0.4)';
            if (typeof showToast === 'function') showToast('Removed from Saved Places');
            if (window.savedPlaceIds) {
                window.savedPlaceIds = window.savedPlaceIds.filter(id => id !== destId);
            }
        } else {
            element.style.color = '#ff3b30';
            if (typeof showToast === 'function') showToast('Added to Saved Places');
            if (!window.savedPlaceIds) window.savedPlaceIds = [];
            if (!window.savedPlaceIds.includes(destId)) window.savedPlaceIds.push(destId);
        }

        // 2. BACKGROUND NETWORK REQUEST
                fetch('/api/tourist/destinations/' + destId + '/favorite', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token,
            }
        }).catch(e => {
            // Revert on error
            if (typeof showToast === 'function') showToast('Error updating favorite');
            element.style.color = originalColor;
            
            if (wasRed) {
                if (!window.savedPlaceIds) window.savedPlaceIds = [];
                if (!window.savedPlaceIds.includes(destId)) window.savedPlaceIds.push(destId);
            } else {
                if (window.savedPlaceIds) {
                    window.savedPlaceIds = window.savedPlaceIds.filter(id => id !== destId);
                }
            }
        });
    };

    window.openSheet = function(locationData) {
        if (window.activePopup) {
            window.activePopup.remove();
        }
        window.currentDestinationForRoute = locationData;
        document.getElementById('sheet-title').textContent = locationData.name;
        
        const locElement = document.getElementById('sheet-location');
        if (locationData.location && locationData.location.trim() !== '') {
            locElement.textContent = locationData.location;
            locElement.parentElement.style.display = 'block';
        } else {
            locElement.parentElement.style.display = 'none';
        }

        const statusBadge = document.getElementById('sheet-status-badge');
        if (statusBadge) {
            if (locationData.classification_status) {
                statusBadge.style.display = 'inline-block';
                if (locationData.classification_status === 'EXIST') {
                    statusBadge.style.background = '#34c759';
                    statusBadge.textContent = 'EXISTING';
                } else if (locationData.classification_status === 'EMERGE') {
                    statusBadge.style.background = '#38bdf8';
                    statusBadge.textContent = 'EMERGING';
                } else if (locationData.classification_status === 'POTENTIAL') {
                    statusBadge.style.background = '#f59e0b';
                    statusBadge.textContent = 'POTENTIAL';
                } else {
                    statusBadge.style.display = 'none';
                }
            } else {
                statusBadge.style.display = 'none';
            }
        }

        // Open/Closed badge
        const openBadge = document.getElementById('sheet-open-badge');
        if (openBadge) {
            if (locationData.opening_time && locationData.closing_time) {
                const now = new Date();
                const currentMinutes = now.getHours() * 60 + now.getMinutes();
                const openParts = locationData.opening_time.split(':');
                const closeParts = locationData.closing_time.split(':');
                const openMinutes = parseInt(openParts[0]) * 60 + parseInt(openParts[1]);
                const closeMinutes = parseInt(closeParts[0]) * 60 + parseInt(closeParts[1]);

                if (locationData.is_maintenance) {
                    openBadge.style.display = 'inline-block';
                    openBadge.style.background = '#ef4444';
                    openBadge.textContent = 'Under Maintenance';
                } else if (currentMinutes >= openMinutes && currentMinutes < closeMinutes) {
                    openBadge.style.display = 'inline-block';
                    openBadge.style.background = '#34c759';
                    openBadge.textContent = 'Open Now';
                } else {
                    openBadge.style.display = 'inline-block';
                    openBadge.style.background = '#ef4444';
                    openBadge.textContent = 'Closed';
                }
            } else {
                openBadge.style.display = 'none';
            }
        }

        const favBtn = document.getElementById('sheet-fav-btn');
        if (favBtn) {
            if (window.savedPlaceIds && window.savedPlaceIds.includes(locationData.id)) {
                favBtn.style.color = '#ff3b30';
            } else {
                favBtn.style.color = 'rgba(255,255,255,0.4)';
            }
            
            const token = localStorage.getItem('intan_elyu_token');
            if (token && !window.savedPlaceIdsFetched) {
                fetch('/api/tourist/dashboard', {
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
        
        const imgPath = window.getDestImage(locationData, 600);
        
        const imgEl = document.getElementById('sheet-img');
        if (imgEl) {
            imgEl.src = imgPath;
            imgEl.onerror = function() { this.onerror = null; this.src = ''; this.style.display = 'none'; };
        }
        

        const destName = locationData.name.toLowerCase();

        const warningEl = document.getElementById('vehicle-accessibility-warning');
        if (warningEl) {
            if (locationData.accessible_by_private_vehicle === false || locationData.accessible_by_private_vehicle === 0) {
                warningEl.style.display = 'block';
            } else {
                warningEl.style.display = 'none';
            }
        }

        let manualGuide = "From the town proper of " + (locationData.municipality || "La Union") + ", take a local tricycle heading to " + (locationData.location || "the barangay") + ". Ask the driver to drop you off at " + locationData.name + ".";
        
        document.getElementById('sheet-manual-guide').textContent = manualGuide;
        
        document.getElementById('sheet-distance').textContent = 'Calculating...';

        const hoursRow = document.getElementById('sheet-hours-row');
        const hoursEl = document.getElementById('sheet-hours');
        if (locationData.opening_time && locationData.closing_time) {
            hoursRow.style.display = 'flex';
            const fmt = (t) => { const p = t.split(':'); const h = parseInt(p[0]), m = p[1]; return (h % 12 || 12) + ':' + m + (h < 12 ? ' AM' : ' PM'); };
            hoursEl.textContent = fmt(locationData.opening_time) + ' — ' + fmt(locationData.closing_time);
        } else {
            hoursRow.style.display = 'none';
        }

        if (window.getDeviceLocation) {
            window.getDeviceLocation().then(async (pos) => {
                const startLat = pos.coords.latitude;
                const startLng = pos.coords.longitude;
                const destLat = parseFloat(locationData.lat || locationData.latitude);
                const destLng = parseFloat(locationData.lng || locationData.longitude);
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

        const placeSheet = document.getElementById('place-details-sheet');
        if (placeSheet.openSheet) placeSheet.openSheet(true);
        else placeSheet.classList.add('active');
    };

    window.closeSheet = function() {
        const placeSheet = document.getElementById('place-details-sheet');
        if (placeSheet.closeSheet) placeSheet.closeSheet();
        else placeSheet.classList.remove('active');
        if (window.mapInstance) {
            window.mapInstance.flyTo({ center: [120.3167, 16.6159], zoom: 11, duration: 800 });
        }
    };

    window.showAddConfirm = function(destName) {
        const overlay = document.getElementById('itin-add-confirm');
        const nameEl = document.getElementById('itin-add-confirm-name');
        const inner = overlay.querySelector('div > div');
        if (nameEl) nameEl.textContent = destName;
        overlay.style.pointerEvents = 'all';
        overlay.style.opacity = '1';
        inner.style.transform = 'scale(1)';
    };

    window.closeAddConfirm = function() {
        const overlay = document.getElementById('itin-add-confirm');
        const inner = overlay.querySelector('div > div');
        inner.style.transform = 'scale(0.85)';
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';
        // Reset modal to default "Added to Itinerary!" state
        const titleEl = overlay.querySelector('h3');
        const iconWrap = overlay.querySelector('div > div');
        const btnView = overlay.querySelector('button[onclick*="viewItinerary"]');
        if (titleEl) titleEl.textContent = 'Added to Itinerary!';
        if (iconWrap) {
            iconWrap.innerHTML = '<i class="fa-solid fa-check" style="font-size:30px; color:#34c759;"></i>';
            iconWrap.style.borderColor = 'rgba(52,199,89,0.25)';
            iconWrap.style.background = 'rgba(52,199,89,0.12)';
        }
        if (btnView) btnView.style.display = '';
    };

    window.viewItinerary = function() {
        window.closeAddConfirm();
        window.location.hash = '#itinerary';
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

    window.selectRouteOption = function(index) {
        const routes = window._routeAlternatives;
        if (!routes || !routes[index]) return;
        const route = routes[index];

        // Update pill highlights
        const colors = ['#007AFF', '#34d399', '#f59e0b'];
        for (let i = 0; i < 3; i++) {
            const el = document.getElementById('route-opt-' + i);
            if (!el) continue;
            if (i === index) {
                el.style.borderColor = colors[i];
                el.style.background = i === 0 ? 'rgba(0,122,255,0.1)' : i === 1 ? 'rgba(52,211,153,0.1)' : 'rgba(245,158,11,0.1)';
            } else {
                el.style.borderColor = 'rgba(255,255,255,0.1)';
                el.style.background = 'rgba(255,255,255,0.03)';
            }
        }

        // Update route layer opacities
        for (let i = 0; i < 3; i++) {
            const lid = 'route-line-' + i;
            if (window.mapInstance.getLayer(lid)) {
                window.mapInstance.setPaintProperty(lid, 'line-opacity', i === index ? 1 : 0.2);
                if (i === index) {
                    window.mapInstance.setPaintProperty(lid, 'line-width', ['interpolate', ['linear'], ['zoom'], 10, 4, 14, 7, 18, 13, 22, 22]);
                } else {
                    window.mapInstance.setPaintProperty(lid, 'line-width', 3);
                }
            }
        }

        // Recalculate fares with selected route
        const distanceKm = route.distance / 1000;
        let durationMin = route.duration / 60;
        let baseMultiplier = 1.6;
        if (distanceKm <= 3) baseMultiplier = 2.5;
        else if (distanceKm <= 7) baseMultiplier = 2.0;
        durationMin *= baseMultiplier;

        const currentHour = new Date().getHours();
        const isRushHour = (currentHour >= 7 && currentHour <= 9) || (currentHour >= 16 && currentHour <= 19);
        const warningDiv = document.getElementById('route-traffic-warning');
        if (isRushHour) {
            durationMin *= 1.4;
            if (warningDiv) { warningDiv.style.display = 'block'; warningDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Heavy traffic expected at this hour'; }
        } else if (warningDiv) { warningDiv.style.display = 'none'; }

        document.getElementById('route-distance').textContent = distanceKm.toFixed(1) + ' km';
        document.getElementById('route-time').textContent = Math.round(durationMin) + ' mins';

        // Rebuild transport options
        const destData = window.currentDestinationForRoute || {};
        const tightRoads = destData.accessible_by_private_vehicle === false || destData.accessible_by_private_vehicle === 0;
        
        // Peak season check (October to May)
        const currentMonth = new Date().getMonth(); // 0-indexed: 9=Oct, 4=May
        const isPeak = (currentMonth >= 9 || currentMonth <= 4);
        const peakSurcharge = isPeak ? 1.2 : 1.0;

        let faresHtml = '';
        
        if (isPeak) {
            faresHtml += `
            <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 14px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #f59e0b; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                ☀️ <span>Peak Season Active (Oct-May): Fares include +20% surcharge</span>
            </div>`;
        }

        const createCard = (name, icon, color, desc, baseFare, schedule) => {
            const finalFare = Math.round(baseFare * peakSurcharge);
            const isPublic = (['Tricycle','Jeepney','Bus'].includes(name));
            return `
            <div onclick="toggleVehicle(this)"
                 data-vehicle='${JSON.stringify({name, icon, color, desc, fare: finalFare})}'
                 data-type="${isPublic ? 'public' : 'private'}"
                 style="cursor:pointer; display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border:1px solid rgba(255,255,255,0.07); border-radius:18px; background:rgba(255,255,255,0.04); margin-bottom:10px; transition:transform 0.15s, background 0.15s, border-color 0.15s;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.15); color:${color}; flex-shrink:0;">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                    <div style="text-align: left;">
                        <h5 style="margin:0 0 3px; font-size:15px; font-weight:800; color:#f8fafc; letter-spacing:-0.2px;">${name}</h5>
                        <span style="font-size:12px; color:rgba(148,163,184,0.75); font-weight:500; display:block;">${desc}</span>
                        <span style="font-size:10px; color:rgba(148,163,184,0.5); font-weight:600; display:flex; align-items:center; gap:4px; margin-top:3px;"><i class="fa-regular fa-clock"></i> ${schedule}</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.2); padding:6px 12px; border-radius:10px; font-weight:800; color:#38bdf8; font-size:15px; flex-shrink:0;">₱${finalFare}</div>
                    <div class="vehicle-check" style="width:22px;height:22px;border-radius:50%;border:2px solid rgba(148,163,184,0.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;transition:all 0.15s;"><i class="fa-solid fa-check" style="opacity:0;transition:opacity 0.15s;"></i></div>
                </div>
            </div>`;
        };
 
        const dbFare = (type) => window.getFareFromMatrix(type, distanceKm);
        if (tightRoads) {
            const trikeFare = dbFare('Tricycle') ?? Math.round(20 + (Math.max(0, distanceKm - 1) * 10));
            faresHtml += createCard('Tricycle', 'fa-motorcycle', 'var(--secondary-color)', 'Only vehicle that fits narrow/tight roads', trikeFare, '24/7 (Night Rates 10PM+)');
        } else {
            if (distanceKm <= 5) {
                const trikeFare = dbFare('Tricycle') ?? Math.round(20 + (Math.max(0, distanceKm - 1) * 10));
                faresHtml += createCard('Tricycle', 'fa-motorcycle', 'var(--secondary-color)', 'Fits narrow roads, best for short trips', trikeFare, '24/7 (Night Rates 10PM+)');
            }
            if (distanceKm >= 2) {
                const taxiFare = Math.round(40 + (distanceKm * 13));
                faresHtml += createCard('Taxi', 'fa-taxi', '#f97316', 'Main roads / highways — metered fare', taxiFare, '24/7 Service');
            }
            if (distanceKm >= 3 && distanceKm <= 20) {
                const jeepFare = dbFare('Jeepney') ?? Math.round(15 + (distanceKm * 2.5));
                faresHtml += createCard('Jeepney', 'fa-bus', '#f59e0b', 'Main roads / highways only', jeepFare, '6:00 AM - 8:00 PM');
            }
            if (distanceKm > 15) {
                const busFare = dbFare('Bus') ?? Math.round(20 + (distanceKm * 1.8));
                faresHtml += createCard('Bus', 'fa-bus', '#ef4444', 'Main roads / highways — best for long distance', busFare, '4:00 AM - 11:00 PM');
            }
            const ownCarFare = Math.max(10, Math.round((distanceKm / 12) * 65));
            faresHtml += createCard('Own Car (Fuel Est.)', 'fa-car', '#34d399', 'Cannot go on tight/narrow roads', ownCarFare, 'Anytime');
        }
        document.getElementById('fare-list').innerHTML = faresHtml;
        setupVehicleSelection();
    };

    window.toggleVehicle = function(el) {
        const type = el.dataset.type;
        const list = document.getElementById('fare-list');
        if (!list) return;
        const isSelected = el.classList.contains('selected-vehicle');
        if (isSelected) {
            el.classList.remove('selected-vehicle');
        } else {
            el.classList.add('selected-vehicle');
            list.querySelectorAll('[data-type="' + (type === 'public' ? 'private' : 'public') + '"]').forEach(c => c.classList.add('disabled-vehicle'));
            // Private mode — single selection only
            if (type === 'private') {
                list.querySelectorAll('.selected-vehicle[data-type="private"]').forEach(c => {
                    if (c !== el) c.classList.remove('selected-vehicle');
                });
            }
        }
        const selected = list.querySelectorAll('.selected-vehicle');
        const sameType = list.querySelectorAll('.selected-vehicle[data-type="' + type + '"]');
        if (sameType.length === 0) {
            const opposingType = type === 'public' ? 'private' : 'public';
            list.querySelectorAll('.disabled-vehicle[data-type="' + opposingType + '"]').forEach(c => c.classList.remove('disabled-vehicle'));
        }
        if (selected.length === 0) {
            list.querySelectorAll('.disabled-vehicle').forEach(c => c.classList.remove('disabled-vehicle'));
        }
    };

    window.addToItinerary = function() {
        if (!window.currentDestinationForRoute) return;
        const dest = window.currentDestinationForRoute;
        
        // Save to localStorage draft
        let draft = [];
        try {
            draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary')) || [];
        } catch(e) {}
        
        // Add if not already there
        if (!draft.find(item => String(item.id) === String(dest.id))) {
            draft.push(dest);
            localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
            // Show "Added to Itinerary!" modal
            const confirmName = document.getElementById('itin-add-confirm-name');
            if (confirmName) confirmName.textContent = dest.name;
            const titleEl = document.querySelector('#itin-add-confirm h3');
            const iconWrap = document.querySelector('#itin-add-confirm div > div');
            const btnView = document.querySelector('#itin-add-confirm button[onclick*="viewItinerary"]');
            if (titleEl) titleEl.textContent = 'Added to Itinerary!';
            if (iconWrap) {
                iconWrap.innerHTML = '<i class="fa-solid fa-check" style="font-size:30px; color:#34c759;"></i>';
                iconWrap.style.borderColor = 'rgba(52,199,89,0.25)';
                iconWrap.style.background = 'rgba(52,199,89,0.12)';
            }
            if (btnView) btnView.style.display = '';
        } else {
            // Show "Already in Itinerary" modal
            const confirmName = document.getElementById('itin-add-confirm-name');
            if (confirmName) confirmName.textContent = dest.name + ' is already added to itinerary.';
            const titleEl = document.querySelector('#itin-add-confirm h3');
            const iconWrap = document.querySelector('#itin-add-confirm div > div');
            const btnView = document.querySelector('#itin-add-confirm button[onclick*="viewItinerary"]');
            if (titleEl) titleEl.textContent = 'Already in Itinerary';
            if (iconWrap) {
                iconWrap.innerHTML = '<i class="fa-solid fa-bookmark" style="font-size:30px; color:#f59e0b;"></i>';
                iconWrap.style.borderColor = 'rgba(245,158,11,0.25)';
                iconWrap.style.background = 'rgba(245,158,11,0.12)';
            }
            if (btnView) btnView.style.display = 'none';
        }
        
        window.closeSheet();
        
        const confirmModal = document.getElementById('itin-add-confirm');
        if (confirmModal) {
            confirmModal.style.opacity = '1';
            confirmModal.style.pointerEvents = 'auto';
            const card = confirmModal.querySelector('div');
            if(card) {
                card.style.transform = 'scale(1)';
            }
        }
    };

    window.closeAddConfirm = function() {
        const confirmModal = document.getElementById('itin-add-confirm');
        if (confirmModal) {
            confirmModal.style.opacity = '0';
            confirmModal.style.pointerEvents = 'none';
            const card = confirmModal.querySelector('div');
            if(card) {
                card.style.transform = 'scale(0.85)';
            }
        }
    };

    window.viewItinerary = function() {
        window.closeAddConfirm();
        if (typeof navigateTo === 'function') {
            navigateTo('itinerary');
        }
    };

    function setupVehicleSelection() {
        // Reset any selected/disabled states when route recalculates
        const list = document.getElementById('fare-list');
        if (list) {
            list.querySelectorAll('.selected-vehicle').forEach(c => c.classList.remove('selected-vehicle'));
            list.querySelectorAll('.disabled-vehicle').forEach(c => c.classList.remove('disabled-vehicle'));
        }
    }



    // --- Site Testimonies & Policy Recommendations ---
    async function fetchTestimonies(spotId) {
        const list = document.getElementById('testimonies-list-container');
        const summary = document.getElementById('testimonies-summary-metrics');
        if (!list) return;

        list.innerHTML = '<div style="font-size:12px; color:rgba(255,255,255,0.4); text-align:center; padding:10px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading reviews...</div>';

        const token = localStorage.getItem('intan_elyu_token');

        try {
            const res = await fetch(backendUrl + '/api/tourist/feedback?tourist_spot_id=' + spotId, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const d = await res.json();
            if (d.status === 'success') {
                // Render summary metrics
                if (d.summary && d.summary.total_reviews > 0) {
                    const sm = d.summary;
                    summary.style.display = 'block';
                    summary.innerHTML = `
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:6px;">
                            <strong style="color:#fff;">📊 Visitor Insights (${sm.total_reviews} reviews)</strong>
                            <strong style="color:#38bdf8;">★ ${sm.average_rating}</strong>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
                            <div>
                                <span style="display:block; font-size:9px; color:rgba(255,255,255,0.45); text-transform:uppercase;">Crowd Level</span>
                                <span style="font-weight:700; color:#fff;">
                                    ${sm.crowd.high >= sm.crowd.medium && sm.crowd.high >= sm.crowd.low ? 'High' : (sm.crowd.medium >= sm.crowd.low ? 'Medium' : 'Low')}
                                </span>
                            </div>
                            <div>
                                <span style="display:block; font-size:9px; color:rgba(255,255,255,0.45); text-transform:uppercase;">Cleanliness</span>
                                <span style="font-weight:700; color:#fff;">
                                    ${sm.cleanliness.clean >= sm.cleanliness.moderate && sm.cleanliness.clean >= sm.cleanliness.dirty ? 'Clean' : (sm.cleanliness.moderate >= sm.cleanliness.dirty ? 'Moderate' : 'Dirty')}
                                </span>
                            </div>
                            <div>
                                <span style="display:block; font-size:9px; color:rgba(255,255,255,0.45); text-transform:uppercase;">Safety</span>
                                <span style="font-weight:700; color:#fff;">
                                    ${sm.safety.safe >= sm.safety.moderate && sm.safety.safe >= sm.safety.unsafe ? 'Safe' : (sm.safety.moderate >= sm.safety.unsafe ? 'Moderate' : 'Unsafe')}
                                </span>
                            </div>
                        </div>`;
                } else {
                    summary.style.display = 'none';
                }

                // Render testimonies list
                if (d.data && d.data.length > 0) {
                    let html = '';
                    d.data.forEach(fb => {
                        const user = fb.user || { name: 'Explorer' };
                        const stars = '★'.repeat(fb.rating || 0) + '☆'.repeat(5 - (fb.rating || 0));
                        const date = new Date(fb.created_at).toLocaleDateString();
                        const policyHtml = fb.policy_recommendation ? `
                            <div style="background:rgba(56,189,248,0.06); border-left:2px solid #38bdf8; padding:8px 10px; border-radius:4px; margin-top:8px; font-size:11px;">
                                <strong style="display:block; font-size:10px; color:#38bdf8; text-transform:uppercase; margin-bottom:2px;">Policy Recommendation:</strong>
                                <span style="color:rgba(255,255,255,0.85);">${fb.policy_recommendation}</span>
                            </div>` : '';

                        html += `
                        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); padding:12px; border-radius:14px; font-size:12px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                <strong style="color:#fff;">${user.name}</strong>
                                <span style="color:#f59e0b; font-weight:700;">${stars}</span>
                            </div>
                            <p style="margin:0; color:rgba(255,255,255,0.85); line-height:1.4;">${fb.testimony || 'Visited and checked in.'}</p>
                            ${policyHtml}
                            <span style="display:block; font-size:9px; color:rgba(255,255,255,0.35); text-align:right; margin-top:6px;">${date}</span>
                        </div>`;
                    });
                    list.innerHTML = html;
                } else {
                    list.innerHTML = '<div style="font-size:12px; color:rgba(255,255,255,0.4); text-align:center; padding:10px;">No testimonies yet. Be the first to share!</div>';
                }
            }
        } catch (e) {
            console.error("Testimonies load error:", e);
            list.innerHTML = '<div style="font-size:12px; color:rgba(255,255,255,0.4); text-align:center; padding:10px;">Failed to load reviews.</div>';
        }
    }

    window.openWriteTestimonyModal = function() {
        if (!window.currentSelectedSpotId) return;
        document.getElementById('testimony-spot-id').value = window.currentSelectedSpotId;
        window.setStarRating(5);
        document.getElementById('testimony-comment').value = '';
        document.getElementById('testimony-policy').value = '';
        document.getElementById('testimony-modal').style.display = 'flex';
    };

    window.closeWriteTestimonyModal = function() {
        document.getElementById('testimony-modal').style.display = 'none';
    };

    window.setStarRating = function(rating) {
        document.getElementById('testimony-rating').value = rating;
        document.querySelectorAll('.star-btn').forEach(btn => {
            const starNum = parseInt(btn.dataset.star);
            if (starNum <= rating) {
                btn.style.opacity = '1';
                btn.className = 'fa-solid fa-star star-btn';
            } else {
                btn.style.opacity = '0.35';
                btn.className = 'fa-regular fa-star star-btn';
            }
        });
    };

    window.submitTestimony = async function(event) {
        event.preventDefault();
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const spotId = document.getElementById('testimony-spot-id').value;
        const rating = document.getElementById('testimony-rating').value;
        const testimony = document.getElementById('testimony-comment').value;
        const policy = document.getElementById('testimony-policy').value;
        const crowd = document.getElementById('testimony-crowd').value;
        const cleanliness = document.getElementById('testimony-cleanliness').value;
        const safety = document.getElementById('testimony-safety').value;

        try {
            const response = await fetch(backendUrl + '/api/tourist/feedback', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({
                    tourist_spot_id: spotId,
                    rating: rating,
                    testimony: testimony,
                    policy_recommendation: policy,
                    crowd_level: crowd,
                    cleanliness_level: cleanliness,
                    safety_level: safety
                })
            });

            const data = await response.json();
            if (response.ok) {
                if (typeof showToast === 'function') showToast("Thank you for your feedback! 🗣️");
                window.closeWriteTestimonyModal();
                fetchTestimonies(spotId);
            } else {
                if (typeof showToast === 'function') showToast(data.message || "Failed to submit review.");
            }
        } catch (error) {
            console.error("Testimony submission error:", error);
            if (typeof showToast === 'function') showToast("Network error.");
        }
    };

    // Hook into openSheet to fetch reviews
    const originalOpenSheet = window.openSheet;
    window.openSheet = function(locationData) {
        originalOpenSheet(locationData);
        window.currentSelectedSpotId = locationData.id;
        document.getElementById('sheet-testimonies-section').style.display = 'block';
        fetchTestimonies(locationData.id);
    };

    setTimeout(window.initMap, 50);

    // Auto-refresh: poll for new spots every 10s
    async function checkForNewSpots() {
        if (!window.mapInstance) return;
        try {
            const res = await fetch((window.backendUrl || '') + '/api/public/map', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!data || !data.destinations) return;
            const newIds = data.destinations.map(d => String(d.id)).sort().join(',');
            const oldIds = (window.allMapLocations || []).map(d => String(d.id)).sort().join(',');
            if (newIds !== oldIds) {
                const prevCatEl = document.querySelector('.category-pill.active');
                const prevCat = prevCatEl ? prevCatEl.innerText : 'All';
                window.allMapLocations = data.destinations;
                setupFilters();
                const newCatEl = Array.from(document.querySelectorAll('.category-pill')).find(el => el.innerText === prevCat);
                window.filterCategory(prevCat, newCatEl || document.querySelector('.category-pill'));
            }
        } catch (e) { console.error('Auto-refresh error:', e); }
    }
    setInterval(checkForNewSpots, 10000);
})();
</script>

