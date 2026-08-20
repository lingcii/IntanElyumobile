<!-- Map View -->
<?php
$pageTitle = 'Explore Map';
$activeTab = 'map';

$municipalityImages = [];
$imgDir = __DIR__ . '/../assets/img/MUNICIPALITIES';
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

    <!-- Live Weather, Wave & Sunset Tracker Widget -->
    <div id="weather-sunset-tracker" class="weather-sunset-tracker animate-slide-down">
        <!-- Minimized Left Edge Tab (Only > is showing on the left) -->
        <button type="button" id="tracker-edge-tab" class="tracker-edge-tab" onclick="window.toggleWeatherTracker(true)" title="Open Live Weather & Sunset Tracker" style="display:none;">
            <span class="pulse-dot dot-green" style="width:5px; height:5px; margin:0;" title="Live"></span>
            <i class="fa-solid fa-water tracker-edge-icon"></i>
            <i class="fa-solid fa-chevron-right tracker-edge-arrow"></i>
        </button>

        <!-- Expanded Full Card -->
        <div id="tracker-expanded" class="tracker-expanded-card">
            <div class="tracker-header">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span class="pulse-dot dot-green" style="width:6px; height:6px; margin:0; display:inline-block;" title="Live Telemetry"></span>
                    <span class="tracker-title">Live Weather, Wave & Sunset</span>
                </div>
                <div style="display:flex; align-items:center; gap:3px;">
                    <button type="button" id="tracker-btn-refresh" class="tracker-btn-more" onclick="window.fetchLiveMarineTelemetry(true)" title="Refresh Live Data">
                        <i class="fa-solid fa-arrows-rotate" id="tracker-refresh-icon" style="font-size:11px;"></i>
                    </button>
                    <button type="button" class="tracker-btn-more" onclick="window.toggleWeatherTracker(false)" title="Hide to Left Side">
                        <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                    </button>
                </div>
            </div>
            
            <div class="tracker-body">
                <!-- Row 1: Swell -->
                <div class="tracker-row">
                    <div class="tracker-icon-box wave-icon">
                        <i class="fa-solid fa-water"></i>
                    </div>
                    <span class="tracker-text" id="tracker-swell-text">1.2m - Moderate Swell</span>
                </div>

                <!-- Row 2: Tide -->
                <div class="tracker-row">
                    <div class="tracker-icon-box tide-icon">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                    <span class="tracker-text" id="tracker-tide-text">RISING TIDE (High in 3h 15m)</span>
                </div>

                <!-- Row 3: Sunset -->
                <div class="tracker-row sunset-row">
                    <div class="tracker-icon-box sunset-icon">
                        <i class="fa-solid fa-sun"></i>
                    </div>
                    <span class="tracker-text sunset-highlight" id="tracker-sunset-text">
                        Sunset in <span id="tracker-sunset-countdown">2h 15m</span> at San Juan Beach 🌅
                    </span>
                </div>

                <!-- Action Button -->
                <button type="button" class="tracker-btn-action" onclick="window.findSunsetSpots()">
                    Find Sunset Spots
                </button>
            </div>
        </div>
    </div>

    <!-- Locate Me Button -->
    <div class="btn-locate-me animate-slide-up" id="btn-locate-me" style="position: absolute; bottom: calc(115px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 18px; box-shadow: none; z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-crosshairs"></i>
    </div>

    <!-- Nearby Tourist Sites Button (Left Side) -->
    <div class="btn-nearby-sites animate-slide-up" id="btn-nearby-sites" onclick="window.toggleNearbySitesSheet()" style="position: absolute; bottom: calc(115px + env(safe-area-inset-bottom)); left: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 18px; box-shadow: none; z-index: 900; cursor: pointer; transition: all 0.2s;" title="Nearby Tourist Sites">
        <i class="fa-solid fa-compass"></i>
        <span id="nearby-sites-badge" style="display:none; position:absolute; top:-5px; right:-5px; min-width:18px; height:18px; padding:0 4px; border-radius:9px; background:#38bdf8; color:#0f172a; font-size:10px; font-weight:800; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.5);">0</span>
    </div>

    <!-- Layer Toggle Button -->
    <div class="btn-layer-toggle animate-slide-up" id="btn-layer-toggle" style="position: absolute; bottom: calc(235px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 18px; box-shadow: none; z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-layer-group"></i>
    </div>

    <!-- 3D Mode Button -->
    <div class="btn-3d-view animate-slide-up" id="btn-3d-view" style="position: absolute; bottom: calc(175px + env(safe-area-inset-bottom)); right: 10px; width: 44px; height: 44px; background: #1E3A8A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 18px; box-shadow: none; z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-cube"></i>
    </div>

    <!-- Nearby Tourist Sites Sheet (Triggered by Left Button) -->
    <div class="bottom-sheet" id="nearby-sites-sheet" style="display:none;">
        <div class="sheet-drag-handle" id="nearby-drag-handle"><span class="sheet-drag-dot"></span></div>
        <div class="draggable-content" id="nearby-sites-scroll" style="max-height: calc(75vh - 70px); overflow-y: auto; padding: 0 4px 16px 4px;">
            
            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding: 0 8px;">
                <div>
                    <h3 style="margin:0; font-size:17px; font-weight:800; color:#f8fafc; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-compass" style="color:#38bdf8;"></i> Nearby Tourist Sites
                    </h3>
                    <p id="nearby-sites-subtext" style="margin:3px 0 0 0; font-size:12px; color:rgba(148,163,184,0.8);">
                        Discover attractions close to your current location
                    </p>
                </div>
                <button type="button" onclick="window.closeNearbySitesSheet()" style="background:rgba(255,255,255,0.08); border:none; color:rgba(255,255,255,0.7); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; transition:background 0.2s;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Radius filter pills -->
            <div style="display:flex; gap:6px; overflow-x:auto; padding: 4px 8px 12px 8px; scrollbar-width:none;">
                <button type="button" class="nearby-radius-btn active" data-radius="2" onclick="window.filterNearbyRadius(2, this)" style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:700; border:1px solid #38bdf8; background:linear-gradient(135deg, #38bdf8, #2563eb); color:#fff; cursor:pointer; white-space:nowrap;">Within 2 km</button>
                <button type="button" class="nearby-radius-btn" data-radius="5" onclick="window.filterNearbyRadius(5, this)" style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:700; border:1px solid rgba(56,189,248,0.25); background:rgba(30,58,138,0.6); color:rgba(248,250,252,0.9); cursor:pointer; white-space:nowrap;">Within 5 km</button>
                <button type="button" class="nearby-radius-btn" data-radius="15" onclick="window.filterNearbyRadius(15, this)" style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:700; border:1px solid rgba(56,189,248,0.25); background:rgba(30,58,138,0.6); color:rgba(248,250,252,0.9); cursor:pointer; white-space:nowrap;">Within 15 km</button>
                <button type="button" class="nearby-radius-btn" data-radius="all" onclick="window.filterNearbyRadius('all', this)" style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:700; border:1px solid rgba(56,189,248,0.25); background:rgba(30,58,138,0.6); color:rgba(248,250,252,0.9); cursor:pointer; white-space:nowrap;">All Closest</button>
            </div>

            <!-- List container -->
            <div id="nearby-sites-list" style="display:flex; flex-direction:column; gap:10px; padding: 0 4px;">
                <!-- Rendered dynamically -->
            </div>
        </div>
    </div>

    <!-- Bottom Sheet (hidden by default) -->
    <div class="bottom-sheet" id="place-details-sheet">
        <div class="sheet-drag-handle" id="place-drag-handle"><span class="sheet-drag-dot"></span></div>
        <div class="draggable-content" id="place-details-scroll">
            
            <!-- Destination Header -->
            <div class="dest-sheet-header" id="sheet-title-frame">
                <div class="dest-sheet-header-main">
                    <h3 class="sheet-title" id="sheet-title">Destination Name</h3>
                    <p class="sheet-location" id="sheet-location-container">
                        <i class="fa-solid fa-location-dot"></i>
                        <span id="sheet-location">Location details</span>
                    </p>
                </div>
                <button type="button" class="dest-sheet-close-btn" onclick="window.closeSheet()" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Slidable Image Banner Carousel -->
            <div id="sheet-slider-container" class="dest-slider-container">
                <!-- Floating Overlay Badges -->
                <div id="sheet-badges-overlay" class="dest-badges-overlay">
                    <div id="sheet-category-badge" class="dest-cat-badge-wrap" style="display:none;"></div>
                    <div id="sheet-status-badge" class="sheet-status-pill" style="display:none;"></div>
                    <div id="sheet-open-badge" class="sheet-open-pill" style="display:none;"></div>
                </div>
                <div id="sheet-slider-track" class="dest-slider-track">
                    <img src="" alt="Place Image" class="sheet-img" id="sheet-img">
                </div>
                <div id="sheet-slider-dots" class="dest-slider-dots" style="display:none;"></div>
            </div>

            <!-- Quick Stats Grid (Distance & Visiting Hours) -->
            <div class="dest-quick-stats-grid">
                <div class="quick-stat-card">
                    <div class="quick-stat-icon blue">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="quick-stat-info">
                        <span class="quick-stat-label">Distance</span>
                        <span class="quick-stat-value" id="sheet-distance">Calculating...</span>
                    </div>
                </div>
                <div class="quick-stat-card" id="sheet-hours-stat-card">
                    <div class="quick-stat-icon emerald">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div class="quick-stat-info">
                        <span class="quick-stat-label">Hours</span>
                        <span class="quick-stat-value" id="sheet-hours">--</span>
                    </div>
                </div>
            </div>

            <!-- Site Fee Summary Banner -->
            <div id="sheet-fees-card" class="dest-fees-card" style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:10px 14px; margin-bottom:10px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; border-radius:10px; background:rgba(16,185,129,0.12); color:#34d399; border:1px solid rgba(52,211,153,0.25); display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0;">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Site Fees</span>
                        <span id="sheet-fee-main-text" style="font-size:13px; font-weight:800; color:#f8fafc;">Free Admission</span>
                    </div>
                </div>
                <div id="sheet-fee-breakdown-tags" style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                    <!-- Injected via JS: e.g. Entrance: ₱50 | Environmental: ₱20 -->
                </div>
            </div>

            <!-- About This Location & Travel Details -->
            <div id="sheet-desc-container" class="dest-info-card" style="display:none;">
                <div id="vehicle-accessibility-warning" class="dest-warning-card" style="display:none;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <h6>Inaccessible by Private Car</h6>
                        <p>Prepare to hike or use specialized local transport to reach this destination.</p>
                    </div>
                </div>

                <div id="sheet-desc-animator" style="overflow:hidden;">
                    <div class="dest-section-header">
                        <div class="dest-section-icon"><i class="fa-solid fa-compass"></i></div>
                        <h5 class="dest-section-title">About this destination</h5>
                    </div>
                
                    <p id="sheet-desc-short" class="dest-desc-text"></p>
                    <p id="sheet-desc-full" class="dest-desc-text" style="display:none;"></p>

                    <!-- Expanded Details -->
                    <div id="expanded-details" class="dest-expanded-wrapper" style="display:none;">
                        
                        <!-- Route Guide -->
                        <div class="dest-guide-box" id="sheet-route-guide-box">
                            <div class="dest-guide-title">
                                <i class="fa-solid fa-signs-post"></i> Route Guide
                            </div>
                            <p id="sheet-manual-guide" class="dest-guide-text"></p>
                        </div>

                        <!-- Tour Guide Notice -->
                        <div class="dest-advisory-box" id="sheet-tour-guide-box">
                            <div class="dest-advisory-title">
                                <i class="fa-solid fa-circle-info"></i> Tour Guide Notice
                            </div>
                            <p id="sheet-tour-guide-text" class="dest-advisory-text">Some destinations may require a tour guide for entry or navigation. The system only provides informational notices about this requirement; it does not offer, book, or arrange tour guide services directly.</p>
                        </div>

                        <!-- Service Center & Assistance -->
                        <div class="dest-support-box">
                            <div class="dest-support-header">
                                <span style="font-size:12px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-headset"></i> Tourist Support & Service Centers
                                </span>
                                <span class="dest-support-badge" id="sheet-support-badge">LUPTO / MTO</span>
                            </div>
                            
                            <!-- Dynamic Service Centers list -->
                            <div id="sheet-service-centers-container" style="display:none; margin-bottom:8px;"></div>

                            <div class="dest-contacts-list">
                                <div class="dest-contact-row" id="sheet-service-phone-row">
                                    <span class="dest-contact-label"><i class="fa-solid fa-phone" style="font-size:10px;"></i> Service Hotline:</span>
                                    <span class="dest-contact-val"><a id="sheet-service-phone" href="tel:+630728882454">+63 (072) 888-2454</a></span>
                                </div>
                                <div class="dest-contact-row">
                                    <span class="dest-contact-label"><i class="fa-solid fa-clock" style="font-size:10px;"></i> Service Hours:</span>
                                    <span class="dest-contact-val" id="sheet-service-hours">8:00 AM - 5:00 PM (Daily)</span>
                                </div>
                                <div class="dest-contact-row">
                                    <span class="dest-contact-label"><i class="fa-solid fa-kit-medical" style="font-size:10px; color:#34d399;"></i> Emergency / Medical:</span>
                                    <span class="dest-contact-val emergency"><a href="tel:911">MDRRMO / Call 911</a></span>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonies Section -->
                        <div id="sheet-testimonies-section" style="display:none; margin-top:14px; padding-top:14px; border-top:1px dashed rgba(255,255,255,0.08);">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                <h4 style="margin:0; font-size:12.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#fff;">Tourist Testimonies</h4>
                                <span style="font-size:10px; font-weight:700; color:#38bdf8; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); padding:3px 8px; border-radius:6px;">
                                    <i class="fa-solid fa-shield-halved" style="margin-right:3px;"></i> Verified Reviews
                                </span>
                            </div>
                            <div id="testimonies-summary-metrics" style="display:none;"></div>
                            <div id="testimonies-list-container" style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:12px; color:rgba(255,255,255,0.45); text-align:center; padding:12px 0;">No testimonies yet. Be the first to share!</div>
                            </div>
                        </div>
                    </div>
                </div>

                <button id="btn-view-details" class="dest-toggle-btn" onclick="window.toggleFullDetails()">
                    <span id="details-btn-text">View Full Details</span>
                    <i class="fa-solid fa-chevron-down" id="details-chevron" style="transition:transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);"></i>
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="sheet-btn-row">
                <button id="btn-add-itinerary" onclick="window.addToItinerary()" class="btn-add-itinerary-premium">
                    <i class="fa-solid fa-calendar-plus"></i> Add to Itinerary
                </button>
                <button id="sheet-fav-btn" onclick="window.toggleMapFavorite(this)" class="btn-sheet-fav" aria-label="Save to favorites">
                    <i class="fa-solid fa-heart"></i>
                </button>
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
<style>
#testimony-modal.active { opacity: 1 !important; }
#testimony-modal.active .testimony-card-anim { transform: scale(1) translateY(0) !important; opacity: 1 !important; }
</style>
<div id="testimony-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:99999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); opacity:0; transition:opacity 0.3s ease;">
    <div class="testimony-card-anim" style="background:linear-gradient(135deg, #1e293b, #0f172a); border:1px solid rgba(255,255,255,0.1); border-radius:24px; padding:24px; width:100%; max-width:380px; max-height:85vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,0.3); text-align:left; box-sizing:border-box; transform:scale(0.88) translateY(16px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;">
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

            <!-- Cleanliness, Safety parameters -->
            <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">

                <!-- Cleanliness -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase;">Cleanliness:</label>
                        <span id="cleanliness-selected-label" style="font-size:11px; font-weight:700; color:#10b981;">Clean</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill clean-pill active" data-val="clean" onclick="window.selectCleanliness('clean')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid #10b981; background:rgba(16,185,129,0.18); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            ✨ Clean
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="moderate" onclick="window.selectCleanliness('moderate')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            🧹 Moderate
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="dirty" onclick="window.selectCleanliness('dirty')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            ⚠️ Dirty
                        </button>
                    </div>
                    <input type="hidden" id="testimony-cleanliness" value="clean">
                </div>

                <!-- Safety -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase;">Safety Level:</label>
                        <span id="safety-selected-label" style="font-size:11px; font-weight:700; color:#10b981;">Safe</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill safety-pill active" data-val="safe" onclick="window.selectSafety('safe')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid #10b981; background:rgba(16,185,129,0.18); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            🛡️ Safe
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="moderate" onclick="window.selectSafety('moderate')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            ⚡ Moderate
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="unsafe" onclick="window.selectSafety('unsafe')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            🚨 Unsafe
                        </button>
                    </div>
                    <input type="hidden" id="testimony-safety" value="safe">
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

        const regionDataPromise = fetch('assets/la_union_municipalities.json').then(r => r.json()).catch(e => console.error("Region fetch error:", e));

        // Fetch fare rates and vehicle data from Railway DB
        const faresPromise = fetch(_backendBase + '/api/public/fares', {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => { 
            window.fareData = d.fares || {}; 
            window.vehicleData = d.vehicles || []; 
            window.fuelPrice = d.fuel_price || 65.0;
        }).catch(e => console.error("Fares fetch error:", e));

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
                        "background-color": "#eef2f6"
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
            fadeDuration: 0,
            attributionControl: false
        });

        // Suppress tile load errors (harmless — tiles fall back gracefully)
        window.mapInstance.on('error', (e) => {
            if (e && e.error && e.error.status === 404) return;
            if (e && e.source && e.source.type === 'raster') return;
        });

        // window.mapInstance.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'bottom-right');

        // Map Load Initialization
        window.mapInstance.on('load', async () => {
            // Render markers immediately
            try {
                const data = await mapDataPromise;
                if (data && data.destinations) {
                    window.allMapLocations = data.destinations || [];
                    setupFilters();
                    renderMarkers(window.allMapLocations);

                    setTimeout(() => {
                        const filterCat = localStorage.getItem('intan_elyu_filter_category');
                        if (filterCat) {
                            localStorage.removeItem('intan_elyu_filter_category');
                            const pills = document.querySelectorAll('.category-pill');
                            let matchedPill = null;
                            pills.forEach(p => {
                                if (p.textContent.trim().toLowerCase() === filterCat.trim().toLowerCase()) {
                                    matchedPill = p;
                                }
                            });
                            window.filterCategory(filterCat, matchedPill);
                        }
                    }, 100);

                    setTimeout(() => {
                        const pendingStr = localStorage.getItem('intan_elyu_pending_route');
                        if (pendingStr) {
                            localStorage.removeItem('intan_elyu_pending_route');
                            try {
                                const place = (window.safeJsonParse ? window.safeJsonParse(pendingStr, null) : JSON.parse(pendingStr));
                                if (place) {
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
                            } catch (e) { console.error('Error parsing pending route:', e); }
                        }

                        const viewStr = localStorage.getItem('intan_elyu_view_destination');
                        if (viewStr) {
                            localStorage.removeItem('intan_elyu_view_destination');
                            try {
                                const place = (window.safeJsonParse ? window.safeJsonParse(viewStr, null) : JSON.parse(viewStr));
                                if (place) {
                                    const pLat = place.lat || place.latitude;
                                    const pLng = place.lng || place.longitude;
                                    if (pLat && pLng && !isNaN(parseFloat(pLat)) && !isNaN(parseFloat(pLng))) {
                                        window.mapInstance.flyTo({ center: [parseFloat(pLng), parseFloat(pLat)], zoom: 14, offset: [0, -160] });
                                        window.openSheet(place);
                                    }
                                }
                            } catch (e) { console.error('Error parsing view destination:', e); }
                        }
                    }, 300);
                }
            } catch (error) {
                console.error("Map data processing error:", error);
            }

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
                            const nextId = e.features[0].id;
                            if (hoveredId !== nextId) {
                                if (hoveredId !== null) {
                                    window.mapInstance.setFeatureState({ source: 'municipality-zones', id: hoveredId }, { hover: false });
                                }
                                hoveredId = nextId;
                                window.mapInstance.setFeatureState({ source: 'municipality-zones', id: hoveredId }, { hover: true });
                            }
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

        });

        setupEventListeners();
    };

    window.renderMarkers = function(locations) {
        if (window.mapMarkers) {
            window.mapMarkers.forEach(m => m.remove());
        }
        window.mapMarkers = [];

        locations.forEach(loc => {
            const locLat = parseFloat(loc.lat || loc.latitude);
            const locLng = parseFloat(loc.lng || loc.longitude);
            if (isNaN(locLat) || isNaN(locLng)) return;
            
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
            container.style.cssText = 'will-change:transform; transform:translate3d(0,0,0); backface-visibility:hidden;';
            
            const el = document.createElement('div');
            el.className = 'custom-map-marker';
            el.style.cssText = `width:32px; height:32px; background-color:#FFFFFF; border:2px solid ${catColor}; border-radius:50%; display:flex; align-items:center; justify-content:center; color:${catColor}; box-shadow:0 4px 8px rgba(0,0,0,0.15); cursor:pointer; will-change:transform; transform:translate3d(0,0,0); backface-visibility:hidden; contain:layout style;`;
            
            el.innerHTML = `<i class="fa-solid ${iconClass}" style="font-size:14px;"></i>`;
            
            container.appendChild(el);
            
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
                    window.mapInstance.flyTo({ center: [locLng, locLat], zoom: Math.max(cz, 14), offset: [0, -180], duration: 400 });
                    window.openSheet(loc);
                });

                window.activePopup = new maplibregl.Popup({
                    closeButton: false, closeOnClick: false, offset: 15, className: 'smooth-map-popup'
                })
                .setLngLat([locLng, locLat])
                .setDOMContent(popupContent)
                .addTo(window.mapInstance);

                const popupEl = window.activePopup.getElement();
                if(popupEl) popupEl.style.zIndex = 9999;
                
                const cp = window.mapInstance.getCenter();
                const cz = window.mapInstance.getZoom();
                window.mapInstance.flyTo({ center: [locLng, locLat], zoom: Math.max(cz, 14), offset: [0, -180], duration: 1000 });
            });
            
            const marker = new maplibregl.Marker({ element: container })
                .setLngLat([locLng, locLat])
                .addTo(window.mapInstance);
                
            window.mapMarkers.push(marker);
        });
    }

    function matchesCategoryFilter(loc, targetCat) {
        if (!targetCat || targetCat === 'All') return true;
        const t = targetCat.toLowerCase().trim();
        const c = (loc.category || '').toLowerCase();
        const n = (loc.name || '').toLowerCase();
        const d = (loc.description || '').toLowerCase();
        const combined = `${c} ${n} ${d}`;

        if (combined.includes(t)) return true;

        if (t.includes('cultural') || t.includes('heritage')) {
            return combined.includes('culture') || combined.includes('cultural') || combined.includes('heritage') || combined.includes('historic') || combined.includes('museum') || combined.includes('church') || combined.includes('shrine') || combined.includes('landmark') || combined.includes('monument');
        }
        if (t.includes('resort') || t.includes('stay') || t.includes('hotel')) {
            return combined.includes('resort') || combined.includes('hotel') || combined.includes('stay') || combined.includes('inn') || combined.includes('lodge') || combined.includes('accommodation');
        }
        if (t.includes('shopping') || t.includes('market')) {
            return combined.includes('shopping') || combined.includes('market') || combined.includes('mall') || combined.includes('store') || combined.includes('pasalubong');
        }
        if (t.includes('festival') || t.includes('event')) {
            return combined.includes('festival') || combined.includes('event') || combined.includes('plaza') || combined.includes('venue');
        }
        if (t.includes('beach') || t.includes('surf')) {
            return combined.includes('beach') || combined.includes('surf') || combined.includes('coastal') || combined.includes('island') || combined.includes('cove');
        }
        if (t.includes('mountain') || t.includes('nature') || t.includes('park')) {
            return combined.includes('mountain') || combined.includes('hiking') || combined.includes('nature') || combined.includes('park') || combined.includes('hill') || combined.includes('trail') || combined.includes('peak') || combined.includes('viewpoint');
        }
        if (t.includes('water') || t.includes('fall') || t.includes('river') || t.includes('lake')) {
            return combined.includes('waterfall') || combined.includes('fall') || combined.includes('river') || combined.includes('lake') || combined.includes('spring');
        }
        if (t.includes('food') || t.includes('dining') || t.includes('restaurant')) {
            return combined.includes('food') || combined.includes('dining') || combined.includes('restaurant') || combined.includes('cafe') || combined.includes('bistro') || combined.includes('eatery');
        }

        return false;
    }

    function setupFilters() {
        const container = document.getElementById('map-categories-container');
        if (!container) return;

        const rawCats = [];
        (window.allMapLocations || []).forEach(loc => {
            if (!loc.category) return;
            const parts = String(loc.category).split(/[,/]/);
            parts.forEach(p => {
                const trimmed = p.trim();
                if (trimmed) rawCats.push(trimmed);
            });
        });

        const uniqueCats = [...new Set(rawCats)];
        let html = `<div class="category-pill active" onclick="filterCategory('All', this)">All</div>`;
        uniqueCats.forEach(cat => {
            const safeCat = cat.replace(/'/g, "\\'");
            html += `<div class="category-pill" onclick="filterCategory('${safeCat}', this)">${cat}</div>`;
        });
        container.innerHTML = html;
    }

    window.filterCategory = function(category, element) {
        document.querySelectorAll('.category-pill').forEach(pill => pill.classList.remove('active'));
        if (element) element.classList.add('active');

        const searchInput = document.getElementById('map-search-input');
        const searchText = searchInput ? searchInput.value.toLowerCase() : '';
        
        const filtered = (window.allMapLocations || []).filter(loc => {
            const name = loc.name ? loc.name.toLowerCase() : '';
            const location = loc.location ? loc.location.toLowerCase() : '';
            const locCat = loc.category ? loc.category.toLowerCase() : '';
            const matchesSearch = (name.includes(searchText) || location.includes(searchText) || locCat.includes(searchText));
            const matchesCat = matchesCategoryFilter(loc, category);
            return matchesSearch && matchesCat;
        });
        
        window.renderMarkers(filtered);

        const validFiltered = filtered.filter(loc => loc.lat && loc.lng && !isNaN(parseFloat(loc.lat)) && !isNaN(parseFloat(loc.lng)));
        if (validFiltered.length > 0 && window.mapInstance) {
            const bounds = new maplibregl.LngLatBounds();
            validFiltered.forEach(loc => bounds.extend([parseFloat(loc.lng), parseFloat(loc.lat)]));
            
            // UI padding: top 150px (search header + categories), bottom 120px (bottom nav bar), left/right 40px
            window.mapInstance.fitBounds(bounds, {
                padding: { top: 150, bottom: 120, left: 40, right: 40 },
                maxZoom: 14,
                duration: 800
            });
            if (typeof showToast === 'function' && category !== 'All') {
                showToast(`Showing ${validFiltered.length} spot${validFiltered.length !== 1 ? 's' : ''} for ${category}`);
            }
        } else if (category !== 'All' && typeof showToast === 'function') {
            showToast(`No spots found for ${category}`);
        }
    };

    function setupEventListeners() {
        window.getDeviceLocation = async (forceFresh = true) => {
            if (typeof window.requestPreciseLocation === 'function') {
                const loc = await window.requestPreciseLocation(forceFresh);
                if (loc && loc.lat && loc.lng) {
                    return { coords: { latitude: loc.lat, longitude: loc.lng, accuracy: loc.accuracy || 10, source: 'gps' } };
                }
            }
            if (typeof window.resolveUserLocation === 'function') {
                const loc = await window.resolveUserLocation(forceFresh);
                if (loc && loc.lat && loc.lng) {
                    return { coords: { latitude: loc.lat, longitude: loc.lng, accuracy: loc.source === 'gps' ? 10 : 5000, source: loc.source } };
                }
            }
            throw new Error("Device GPS location unavailable. Please grant location permissions in browser.");
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

        function highlightMatch(text, query) {
            if (!text) return '';
            if (!query) return text;
            const idx = text.toLowerCase().indexOf(query.toLowerCase());
            if (idx === -1) return text;
            const before = text.slice(0, idx);
            const matched = text.slice(idx, idx + query.length);
            const after = text.slice(idx + query.length);
            return `${before}<mark class="search-highlight" style="background:rgba(56,189,248,0.22); color:#38bdf8; font-weight:700; padding:0 3px; border-radius:4px;">${matched}</mark>${after}`;
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
                const highlightedName = highlightMatch(loc.name, q);
                const subtitle = loc.municipality ? (loc.municipality + (loc.category ? ' • ' + loc.category : '')) : (loc.category || '');
                const highlightedSubtitle = highlightMatch(subtitle, q);

                return `
                    <div class="map-search-suggestion-item" data-id="${loc.id}" data-lat="${loc.lat}" data-lng="${loc.lng}">
                        <div class="suggestion-icon" style="background:${color}22; color:${color}; border:1px solid ${color}44;">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <div class="suggestion-info" style="flex:1; min-width:0; overflow:hidden;">
                            <div class="suggestion-name" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${highlightedName}</div>
                            <div class="suggestion-sub" style="font-size:11px; opacity:0.75; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${highlightedSubtitle}</div>
                        </div>
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
            locateBtn.addEventListener('click', async () => {
                locateBtn.classList.add('btn-tap-pop');
                setTimeout(() => locateBtn.classList.remove('btn-tap-pop'), 400);
                const icon = locateBtn.querySelector('i') || locateBtn;
                const origIconClass = icon.className;
                icon.className = 'fa-solid fa-spinner fa-spin';
                if (typeof showToast === 'function') showToast("Acquiring precise GPS location...");

                try {
                    const position = await window.getDeviceLocation(true);
                    const lat = position && position.coords ? position.coords.latitude : null;
                    const lng = position && position.coords ? position.coords.longitude : null;
                    const isGps = position && position.coords && (position.coords.source === 'gps' || window.currentGPSSource === 'gps');

                    if (window.mapInstance && lat && lng && !isNaN(lat) && !isNaN(lng)) {
                        window.mapInstance.flyTo({ center: [parseFloat(lng), parseFloat(lat)], zoom: 15, duration: 1200 });

                        // Ensure user marker is updated
                        if (window.userMarker) {
                            window.userMarker.setLngLat([lng, lat]);
                        } else {
                            const el = document.createElement('div');
                            el.innerHTML = `<div style="background:#007AFF; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow:0 0 0 5px rgba(0,122,255,0.3);"></div>`;
                            window.userMarker = new maplibregl.Marker({element: el}).setLngLat([lng, lat]).addTo(window.mapInstance);
                        }

                        if (typeof showToast === 'function') {
                            showToast(isGps ? "Centered on your precise GPS location 📍" : "Centered on your estimated location");
                        }
                    } else {
                        throw new Error("Unable to determine coordinates");
                    }
                } catch (e) {
                    console.warn("Location error:", e);
                    if (typeof showToast === 'function') {
                        showToast("GPS access denied. Select your town below or allow location permission.");
                    }
                    if (typeof window.openLocationPickerModal === 'function') {
                        window.openLocationPickerModal();
                    }
                } finally {
                    icon.className = origIconClass || 'fa-solid fa-crosshairs';
                }
            });
        }

        // Real-time GPS Tracker Hook with Proximity Auto-Pop Trigger
        let _hasAutoCenteredGPS = false;
        document.addEventListener('gpsUpdated', function(e) {
            const lat = e.detail.lat;
            const lng = e.detail.lng;
            const isRealGps = (e.detail.source === 'gps' || window.currentGPSSource === 'gps');
            if (window.mapInstance && lat && lng) {
                if (window.userMarker) {
                    window.userMarker.setLngLat([lng, lat]);
                } else {
                    const el = document.createElement('div');
                    el.className = 'user-gps-tracking-marker';
                    el.innerHTML = `
                        <div style="position:relative; width:22px; height:22px; display:flex; align-items:center; justify-content:center;">
                            <div style="position:absolute; width:36px; height:36px; border-radius:50%; background:rgba(56,189,248,0.35); animation:pulse 2s infinite;"></div>
                            <div style="position:relative; background:#0284c7; width:20px; height:20px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 0 12px rgba(2,132,199,0.8); z-index:2;"></div>
                        </div>
                    `;
                    window.userMarker = new maplibregl.Marker({element: el}).setLngLat([lng, lat]).addTo(window.mapInstance);
                }

                // If this is the first live GPS signal, smoothly center the map on the user's real position
                if (isRealGps && !_hasAutoCenteredGPS) {
                    _hasAutoCenteredGPS = true;
                    window.mapInstance.flyTo({ center: [parseFloat(lng), parseFloat(lat)], zoom: 15, duration: 1200 });
                    if (typeof showToast === 'function') {
                        showToast("📍 Live GPS Tracking Active");
                    }
                }

                updateNearbyBadge(lat, lng);
            }
        });

        // ── Nearby Tourist Sites Logic ──
        function getDistanceKm(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth radius in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function updateNearbyBadge(lat, lng) {
            if (!window.allMapLocations || window.allMapLocations.length === 0) return;
            const nearbySpots = window.allMapLocations.filter(loc => {
                const locLat = parseFloat(loc.lat || loc.latitude);
                const locLng = parseFloat(loc.lng || loc.longitude);
                if (isNaN(locLat) || isNaN(locLng)) return false;
                return getDistanceKm(lat, lng, locLat, locLng) <= 2.0;
            });
            const badge = document.getElementById('nearby-sites-badge');
            if (badge) {
                if (nearbySpots.length > 0) {
                    badge.textContent = nearbySpots.length;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        let currentNearbyRadius = 2; // default 2km

        window.toggleNearbySitesSheet = function() {
            const btn = document.getElementById('btn-nearby-sites');
            if (btn) {
                btn.classList.add('btn-tap-pop');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.remove('icon-spin-bounce');
                    void icon.offsetWidth;
                    icon.classList.add('icon-spin-bounce');
                }
                setTimeout(() => btn.classList.remove('btn-tap-pop'), 400);
            }

            const sheet = document.getElementById('nearby-sites-sheet');
            if (!sheet) return;
            if (sheet.classList.contains('active')) {
                window.closeNearbySitesSheet();
            } else {
                window.openNearbySitesSheet();
            }
        };

        window.openNearbySitesSheet = async function() {
            if (window.closeSheet) window.closeSheet();
            const sheet = document.getElementById('nearby-sites-sheet');
            const btn = document.getElementById('btn-nearby-sites');
            if (!sheet) return;

            if (btn) btn.classList.add('active');

            sheet.style.display = 'block';
            sheet.style.transition = 'transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease';
            sheet.style.transform = 'translateY(calc(100% + 120px))';
            sheet.classList.remove('active');

            void sheet.offsetHeight; // force reflow for smooth slide-up animation

            sheet.classList.add('active');
            sheet.style.transform = 'translateY(0)';

            await window.renderNearbyTouristSites();
        };

        window.closeNearbySitesSheet = function() {
            const sheet = document.getElementById('nearby-sites-sheet');
            const btn = document.getElementById('btn-nearby-sites');
            if (btn) btn.classList.remove('active');
            if (!sheet) return;
            sheet.style.transition = 'transform 0.38s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease';
            sheet.style.transform = 'translateY(calc(100% + 120px))';
            sheet.classList.remove('active');
            setTimeout(() => {
                if (!sheet.classList.contains('active')) {
                    sheet.style.display = 'none';
                }
            }, 380);
        };

        window.filterNearbyRadius = function(radius, btn) {
            currentNearbyRadius = radius;
            document.querySelectorAll('.nearby-radius-btn').forEach(b => {
                b.classList.remove('active');
            });
            if (btn) {
                btn.classList.add('active');
            }
            window.renderNearbyTouristSites();
        };

        window.renderNearbyTouristSites = async function() {
            const container = document.getElementById('nearby-sites-list');
            const subtext = document.getElementById('nearby-sites-subtext');
            if (!container) return;

            container.innerHTML = `
                <div style="text-align:center; padding:30px 10px; color:rgba(148,163,184,0.8); font-size:13px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size:22px; color:#38bdf8; margin-bottom:10px; display:block;"></i>
                    Locating nearby tourist attractions...
                </div>
            `;

            let lat = window.currentGPSLat || (window.userCurrentCoords ? window.userCurrentCoords.lat : null);
            let lng = window.currentGPSLng || (window.userCurrentCoords ? window.userCurrentCoords.lng : null);

            if (!lat || !lng) {
                try {
                    if (typeof window.requestPreciseLocation === 'function') {
                        const loc = await window.requestPreciseLocation(false);
                        if (loc && loc.lat && loc.lng) {
                            lat = loc.lat;
                            lng = loc.lng;
                        }
                    } else if (navigator.geolocation) {
                        const pos = await new Promise((res, rej) => navigator.geolocation.getCurrentPosition(res, rej, { timeout: 6000, enableHighAccuracy: true }));
                        if (pos && pos.coords) {
                            lat = pos.coords.latitude;
                            lng = pos.coords.longitude;
                        }
                    }
                } catch(e) {
                    console.log("Nearby sites location fallback:", e.message);
                }
            }

            if (!lat || !lng) {
                if (window.mapInstance) {
                    const center = window.mapInstance.getCenter();
                    lat = center.lat;
                    lng = center.lng;
                } else {
                    lat = 16.6159;
                    lng = 120.3209;
                }
            }

            let spots = window.allMapLocations || [];
            if (spots.length === 0) {
                try {
                    const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
                    const res = await fetch(backendUrl + '/api/public/map');
                    if (res.ok) {
                        const d = await res.json();
                        spots = d.destinations || [];
                        window.allMapLocations = spots;
                    }
                } catch(e) {}
            }

            if (spots.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:30px 10px; color:rgba(148,163,184,0.8); font-size:13px;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size:24px; color:#f59e0b; margin-bottom:10px; display:block;"></i>
                        No tourist sites found. Please check your connection.
                    </div>
                `;
                return;
            }

            const calculatedSpots = spots.map(s => {
                const copy = { ...s };
                const sLat = parseFloat(copy.lat || copy.latitude);
                const sLng = parseFloat(copy.lng || copy.longitude);
                if (!isNaN(sLat) && !isNaN(sLng)) {
                    copy.distanceKm = getDistanceKm(lat, lng, sLat, sLng);
                    copy.distanceMeters = Math.round(copy.distanceKm * 1000);
                } else {
                    copy.distanceKm = 999999;
                    copy.distanceMeters = 999999999;
                }
                return copy;
            });

            calculatedSpots.sort((a, b) => a.distanceKm - b.distanceKm);

            let filtered = [];
            if (currentNearbyRadius === 'all') {
                filtered = calculatedSpots.filter(s => s.distanceKm < 999999).slice(0, 15);
            } else {
                const radiusNum = parseFloat(currentNearbyRadius);
                filtered = calculatedSpots.filter(s => s.distanceKm <= radiusNum);
            }

            if (subtext) {
                if (filtered.length > 0) {
                    subtext.textContent = `Found ${filtered.length} attraction${filtered.length > 1 ? 's' : ''} ${currentNearbyRadius === 'all' ? 'closest to you' : 'within ' + currentNearbyRadius + ' km'}`;
                } else {
                    subtext.textContent = `No spots found within ${currentNearbyRadius} km`;
                }
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:28px 14px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:18px; animation: nearbyCardSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                        <div style="width:48px; height:48px; margin:0 auto 12px; border-radius:50%; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); display:flex; align-items:center; justify-content:center; color:#38bdf8; font-size:20px;">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div style="font-size:14px; font-weight:800; color:#f8fafc; margin-bottom:4px;">No Spots Within ${currentNearbyRadius} km</div>
                        <div style="font-size:12px; color:rgba(148,163,184,0.8); margin-bottom:14px; line-height:1.4;">
                            Try expanding your search radius to discover attractions across La Union.
                        </div>
                        <button type="button" onclick="window.filterNearbyRadius(15, document.querySelector('[data-radius=\\'15\\']'))" style="padding:8px 18px; border-radius:100px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; font-size:12px; font-weight:800; cursor:pointer; transition:transform 0.2s ease;">
                            Show Within 15 km
                        </button>
                    </div>
                `;
                return;
            }

            let html = '';
            filtered.forEach((spot, idx) => {
                const img = window.getDestImage ? window.getDestImage(spot, 300) : (spot.image || 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300');
                const rating = spot.rating ? parseFloat(spot.rating).toFixed(1) : (spot.reviews_avg_rating ? parseFloat(spot.reviews_avg_rating).toFixed(1) : 'New');
                
                let distBadge = '';
                if (spot.distanceKm < 0.05) {
                    distBadge = `${Math.max(5, spot.distanceMeters)}m away`;
                } else if (spot.distanceKm < 1.0) {
                    distBadge = `${Math.round(spot.distanceMeters / 10) * 10}m away`;
                } else {
                    distBadge = `${spot.distanceKm.toFixed(1)} km away`;
                }

                const safeSpotStr = encodeURIComponent(JSON.stringify(spot));
                const delay = (idx * 0.04).toFixed(2);

                html += `
                    <div class="nearby-site-card" style="animation: nearbyCardSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) ${delay}s forwards; opacity: 0;" onclick="window.selectNearbySite('${safeSpotStr}')">
                        <img src="${img}" alt="${spot.name}" style="width:64px; height:64px; border-radius:12px; object-fit:cover; flex-shrink:0; transition: transform 0.3s ease;" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=150';">
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; margin-bottom:3px;">
                                <h4 style="margin:0; font-size:14px; font-weight:800; color:#f8fafc; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${spot.name}</h4>
                            </div>
                            <div style="font-size:11.5px; color:rgba(148,163,184,0.85); margin-bottom:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <i class="fa-solid fa-location-dot" style="color:#38bdf8; margin-right:3px;"></i>${spot.municipality || spot.location || 'La Union'}
                            </div>
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span style="font-size:10px; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); padding:2px 7px; border-radius:100px; display:inline-flex; align-items:center; gap:3px;">
                                    <i class="fa-solid fa-location-arrow" style="font-size:9px;"></i> ${distBadge}
                                </span>
                                <span style="font-size:10.5px; font-weight:700; color:#fbbf24; display:inline-flex; align-items:center; gap:3px;">
                                    <i class="fa-solid fa-star" style="font-size:9.5px;"></i> ${rating}
                                </span>
                                ${spot.category ? `<span style="font-size:10px; color:rgba(255,255,255,0.7); background:rgba(255,255,255,0.06); padding:2px 6px; border-radius:6px;">${spot.category}</span>` : ''}
                            </div>
                        </div>
                        <button type="button" class="nearby-site-action-btn" title="View on Map">
                            <i class="fa-solid fa-chevron-right" style="font-size:13px;"></i>
                        </button>
                    </div>
                `;
            });

            container.innerHTML = html;
        };

        window.selectNearbySite = function(encodedSpot) {
            try {
                const spot = JSON.parse(decodeURIComponent(encodedSpot));
                window.closeNearbySitesSheet();
                const sLat = parseFloat(spot.lat || spot.latitude);
                const sLng = parseFloat(spot.lng || spot.longitude);
                if (!isNaN(sLat) && !isNaN(sLng) && window.mapInstance) {
                    window.mapInstance.flyTo({ center: [sLng, sLat], zoom: 15, offset: [0, -180], duration: 900 });
                    setTimeout(() => {
                        if (window.openSheet) window.openSheet(spot);
                    }, 500);
                }
            } catch(e) {
                console.error("Error selecting nearby site:", e);
            }
        };

        // Auto-check on load in case GPS already acquired globally or request precise fix
        setTimeout(() => {
            if (window.currentGPSLat && window.currentGPSLng && window.mapInstance) {
                document.dispatchEvent(new CustomEvent('gpsUpdated', { 
                    detail: { 
                        lat: window.currentGPSLat, 
                        lng: window.currentGPSLng,
                        accuracy: window.currentGPSAccuracy || null,
                        altitude: window.currentGPSAltitude || null,
                        speed: window.currentGPSSpeed || null,
                        source: window.currentGPSSource || 'gps'
                    } 
                }));
            } else if (typeof window.requestPreciseLocation === 'function') {
                window.requestPreciseLocation(false).catch(() => {});
            }
        }, 500);

        // ── Real-Time Live Weather, Marine Swell & Sunset Telemetry ──
        window._liveMarineTelemetry = window._liveMarineTelemetry || {
            waveHeight: 1.2,
            waveLabel: '1.2m - Moderate Swell',
            sunsetTime: null,
            temperature: null,
            weatherCode: null,
            lastFetched: 0
        };

        window.toggleWeatherTracker = function(expand) {
            const tracker = document.getElementById('weather-sunset-tracker');
            const exp = document.getElementById('tracker-expanded');
            const tab = document.getElementById('tracker-edge-tab');
            if (!tracker || !exp || !tab) return;

            if (expand) {
                tracker.classList.remove('minimized');
                tab.style.display = 'none';
                exp.style.display = 'block';
                exp.style.animation = 'nearbyCardSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            } else {
                exp.style.display = 'none';
                tracker.classList.add('minimized');
                tab.style.display = 'inline-flex';
                tab.style.animation = 'buttonTapPop 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            }
        };

        window.fetchLiveMarineTelemetry = async function(isManual) {
            const refreshIcon = document.getElementById('tracker-refresh-icon');
            if (refreshIcon) refreshIcon.classList.add('fa-spin');

            const now = Date.now();
            // Cache for 5 minutes unless manually requested
            if (!isManual && (now - window._liveMarineTelemetry.lastFetched) < 300000 && window._liveMarineTelemetry.lastFetched > 0) {
                if (refreshIcon) setTimeout(() => refreshIcon.classList.remove('fa-spin'), 400);
                window.updateWeatherSunsetTrackerUI();
                return;
            }

            try {
                // Real-time Marine wave & swell API for San Juan / La Union coast (16.6667, 120.3333)
                const marinePromise = fetch('https://marine-api.open-meteo.com/v1/marine?latitude=16.6667&longitude=120.3333&current=wave_height,wave_period,swell_wave_height&timezone=Asia%2FManila')
                    .then(r => r.ok ? r.json() : null)
                    .catch(() => null);

                // Real-time Weather & exact Sunset/Sunrise for San Juan / La Union
                const weatherPromise = fetch('https://api.open-meteo.com/v1/forecast?latitude=16.6667&longitude=120.3333&current=temperature_2m,weather_code,wind_speed_10m&daily=sunset,sunrise&timezone=Asia%2FManila')
                    .then(r => r.ok ? r.json() : null)
                    .catch(() => null);

                const [marineData, weatherData] = await Promise.all([marinePromise, weatherPromise]);

                if (marineData && marineData.current) {
                    const waveH = parseFloat(marineData.current.wave_height || marineData.current.swell_wave_height || 1.2);
                    window._liveMarineTelemetry.waveHeight = waveH;
                    if (waveH < 0.6) {
                        window._liveMarineTelemetry.waveLabel = `${waveH.toFixed(1)}m - Calm Beach Waters`;
                    } else if (waveH < 1.1) {
                        window._liveMarineTelemetry.waveLabel = `${waveH.toFixed(1)}m - Gentle Beach Waves`;
                    } else if (waveH < 1.6) {
                        window._liveMarineTelemetry.waveLabel = `${waveH.toFixed(1)}m - Moderate Swell 🏄`;
                    } else if (waveH < 2.3) {
                        window._liveMarineTelemetry.waveLabel = `${waveH.toFixed(1)}m - Peak Surf Swell 🏄‍♂️`;
                    } else {
                        window._liveMarineTelemetry.waveLabel = `${waveH.toFixed(1)}m - Heavy Swell Caution ⚠️`;
                    }
                }

                if (weatherData) {
                    if (weatherData.current) {
                        window._liveMarineTelemetry.temperature = Math.round(weatherData.current.temperature_2m);
                        window._liveMarineTelemetry.weatherCode = weatherData.current.weather_code;
                    }
                    if (weatherData.daily && weatherData.daily.sunset && weatherData.daily.sunset.length > 0) {
                        window._liveMarineTelemetry.sunsetTime = new Date(weatherData.daily.sunset[0]);
                    }
                }

                window._liveMarineTelemetry.lastFetched = now;
                if (isManual && typeof showToast === 'function') {
                    showToast("🌊 Live Elyu Marine & Sunset Data Synced");
                }
            } catch(e) {
                console.warn("Marine telemetry live update:", e);
            } finally {
                if (refreshIcon) setTimeout(() => refreshIcon.classList.remove('fa-spin'), 500);
                window.updateWeatherSunsetTrackerUI();
            }
        };

        window.updateWeatherSunsetTrackerUI = function() {
            const now = new Date();
            
            // Sunset calculation from live API or realistic astronomical fallback
            let todaySunset = window._liveMarineTelemetry.sunsetTime;
            if (!todaySunset || isNaN(todaySunset.getTime())) {
                todaySunset = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 18, 15, 0);
            }
            
            let diffMs = todaySunset - now;
            let sunsetText = '';
            let pillSunsetText = '';

            if (diffMs > 0) {
                const totalMins = Math.floor(diffMs / 60000);
                const hours = Math.floor(totalMins / 60);
                const mins = totalMins % 60;
                const countdownStr = hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
                
                sunsetText = `Sunset in <span id="tracker-sunset-countdown">${countdownStr}</span> at San Juan Beach 🌅`;
                pillSunsetText = `🌅 Sunset in ${countdownStr}`;
            } else if (diffMs > -3600000) {
                sunsetText = `Sunset is happening now at San Juan Beach! 🌅`;
                pillSunsetText = `🌅 Sunset Active`;
            } else {
                const fmtSunset = todaySunset.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                sunsetText = `Sunset was at ${fmtSunset} • Golden Hour ended 🌙`;
                pillSunsetText = `🌙 Evening Beach Vibe`;
            }

            // Real-time Swell from live telemetry
            const waveLabel = window._liveMarineTelemetry.waveLabel || '1.2m - Moderate Swell';
            const wavePill = `${(window._liveMarineTelemetry.waveHeight || 1.2).toFixed(1)}m Swell`;

            // Astronomical Tide Cycle (12h 25m semi-diurnal period for Lingayen Gulf / San Juan Coast)
            const tideCycleMs = 12.42 * 3600 * 1000;
            const refTideEpoch = new Date('2026-01-01T06:00:00+08:00').getTime();
            const phaseMs = (now.getTime() - refTideEpoch) % tideCycleMs;
            const halfCycle = tideCycleMs / 2;
            
            let tideText = '';
            if (phaseMs < halfCycle) {
                const remMs = halfCycle - phaseMs;
                const remHours = Math.floor(remMs / 3600000);
                const remMins = Math.floor((remMs % 3600000) / 60000);
                tideText = `RISING TIDE (High in ${remHours > 0 ? remHours + 'h ' : ''}${remMins}m)`;
            } else {
                const remMs = tideCycleMs - phaseMs;
                const remHours = Math.floor(remMs / 3600000);
                const remMins = Math.floor((remMs % 3600000) / 60000);
                tideText = `FALLING TIDE (Low in ${remHours > 0 ? remHours + 'h ' : ''}${remMins}m)`;
            }

            // Temperature info if available
            const tempStr = window._liveMarineTelemetry.temperature ? ` • ${window._liveMarineTelemetry.temperature}°C` : '';

            const sunsetEl = document.getElementById('tracker-sunset-text');
            const swellEl = document.getElementById('tracker-swell-text');
            const tideEl = document.getElementById('tracker-tide-text');
            const pillEl = document.getElementById('tracker-pill-summary');

            if (sunsetEl) sunsetEl.innerHTML = sunsetText;
            if (swellEl) swellEl.textContent = waveLabel;
            if (tideEl) tideEl.textContent = tideText;
            if (pillEl) pillEl.textContent = `${wavePill}${tempStr} • ${pillSunsetText}`;
        };

        window.findSunsetSpots = function() {
            if (typeof showToast === 'function') {
                showToast("🌅 Top Sunset Viewing Spots in La Union");
            }

            // Find beach and sunset spots
            const spots = window.allMapLocations || [];
            const sunsetSpots = spots.filter(s => {
                const name = (s.name || '').toLowerCase();
                const cat = (s.category || '').toLowerCase();
                const loc = (s.municipality || s.location || '').toLowerCase();
                return cat.includes('beach') || name.includes('beach') || name.includes('surf') || name.includes('sunset') || name.includes('point') || name.includes('island') || loc.includes('san juan') || loc.includes('bauang');
            });

            // If San Juan beach exists, fly to it
            const sanJuan = sunsetSpots.find(s => (s.name || '').toLowerCase().includes('san juan') || (s.name || '').toLowerCase().includes('urbiztondo')) || sunsetSpots[0];
            if (sanJuan && window.mapInstance) {
                const sLat = parseFloat(sanJuan.lat || sanJuan.latitude);
                const sLng = parseFloat(sanJuan.lng || sanJuan.longitude);
                if (!isNaN(sLat) && !isNaN(sLng)) {
                    window.mapInstance.flyTo({ center: [sLng, sLat], zoom: 15, offset: [0, -150], duration: 1100 });
                    setTimeout(() => {
                        if (window.openSheet) window.openSheet(sanJuan);
                    }, 600);
                }
            } else if (window.openNearbySitesSheet) {
                window.openNearbySitesSheet();
            }
        };

        // Initialize real-time telemetry and schedule live ticks
        window.fetchLiveMarineTelemetry(false);
        setInterval(window.updateWeatherSunsetTrackerUI, 15000); // Live countdown tick every 15s
        setInterval(() => window.fetchLiveMarineTelemetry(false), 300000); // Live API sync every 5 minutes

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
                btnLayer.classList.add('btn-tap-pop');
                setTimeout(() => btnLayer.classList.remove('btn-tap-pop'), 400);
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
                btn3d.classList.add('btn-tap-pop');
                setTimeout(() => btn3d.classList.remove('btn-tap-pop'), 400);
                const is3D = btn3d.classList.toggle('active');
                if (is3D) {
                    btn3d.style.background = 'var(--primary-color)';
                    btn3d.style.color = 'white';
                    window.mapInstance.setTerrain({ "source": "terrain", "exaggeration": 1.2 });
                    window.mapInstance.easeTo({ pitch: 60, bearing: -20, duration: 800 });
                    showToast("3D Terrain View Enabled");
                } else {
                    btn3d.style.background = '#1E3A8A';
                    btn3d.style.color = '#ffffff';
                    window.mapInstance.easeTo({ pitch: 0, bearing: 0, duration: 800 });
                    setTimeout(() => {
                        try { window.mapInstance.setTerrain(null); } catch(e) {}
                    }, 800);
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
            sheet.style.display = 'block';
            if (animate) {
                sheet.style.transition = 'transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease';
                sheet.style.transform = 'translateY(calc(100% + 120px))';
                sheet.classList.remove('active');
                
                void sheet.offsetHeight; // force reflow for smooth slide-up animation
                
                sheet.classList.add('active');
                sheet.style.transform = 'translateY(0)';
                currentY = 0;
            } else {
                sheet.classList.add('active');
                applyY(0);
            }
            setTimeout(() => { if (!isDragging) sheet.style.transition = ''; }, 500);
        }

        function closeSheet() {
            isOpen = false;
            sheet.style.transition = 'transform 0.38s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease';
            sheet.style.transform = 'translateY(calc(100% + 120px))';
            sheet.classList.remove('active');
            setTimeout(() => {
                if (!sheet.classList.contains('active')) {
                    sheet.style.display = 'none';
                }
            }, 380);
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

            sheet.style.transition = 'transform 0.45s cubic-bezier(0.16, 1, 0.3, 1)';

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
    initDraggableSheet('nearby-sites-sheet', 'nearby-drag-handle');


    window.isPlaceSaved = function(destId) {
        if (!destId) return false;
        try {
            const savedIds = JSON.parse(localStorage.getItem('intan_elyu_saved_place_ids') || '[]');
            return savedIds.some(id => id == destId);
        } catch(e) {
            return false;
        }
    };

    window.updateSheetFavButton = function(isSaved) {
        const favBtn = document.getElementById('sheet-fav-btn');
        if (!favBtn) return;
        if (isSaved) {
            favBtn.style.color = '#ff3b30';
            favBtn.style.background = 'rgba(255, 59, 48, 0.15)';
            favBtn.style.borderColor = 'rgba(255, 59, 48, 0.35)';
            favBtn.innerHTML = '<i class="fa-solid fa-heart" style="color:#ff3b30;"></i>';
        } else {
            favBtn.style.color = 'rgba(255, 255, 255, 0.4)';
            favBtn.style.background = 'rgba(255, 255, 255, 0.07)';
            favBtn.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            favBtn.innerHTML = '<i class="fa-solid fa-heart" style="color:rgba(255,255,255,0.4);"></i>';
        }
    };

    window.toggleMapFavorite = function(element) {
        if (!window.currentDestinationForRoute) return;
        const destId = window.currentDestinationForRoute.id;
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) {
            if (typeof showToast === 'function') showToast('Please login to save places');
            return;
        }
        
        let savedIds = [];
        try {
            savedIds = JSON.parse(localStorage.getItem('intan_elyu_saved_place_ids') || '[]');
        } catch(e) {}
        
        const wasSaved = savedIds.some(id => id == destId);
        const willBeSaved = !wasSaved;

        // 1. Update localStorage instantly
        if (willBeSaved) {
            if (!savedIds.some(id => id == destId)) savedIds.push(destId);
        } else {
            savedIds = savedIds.filter(id => id != destId);
        }
        localStorage.setItem('intan_elyu_saved_place_ids', JSON.stringify(savedIds));

        // 2. Instant UI update with pop animation & red fading/filling
        element.classList.remove('heart-pop-anim');
        void element.offsetWidth;
        element.classList.add('heart-pop-anim');
        window.updateSheetFavButton(willBeSaved);

        if (typeof showToast === 'function') {
            showToast(willBeSaved ? 'Added to Saved Places' : 'Removed from Saved Places');
        }

        // 3. Clear cached dashboard data so other views reflect the change immediately
        const tokenPrefix = token ? token.substring(0, 10) : '';
        localStorage.removeItem('saved_places_' + tokenPrefix);
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('dashboard_data_')) {
                const cached = JSON.parse(localStorage.getItem(key) || '{}');
                if (cached.savedPlaces && Array.isArray(cached.savedPlaces)) {
                    if (!willBeSaved) {
                        cached.savedPlaces = cached.savedPlaces.filter(p => p.id != destId);
                    }
                    localStorage.setItem(key, JSON.stringify(cached));
                }
            }
        }

        // 4. Background network request
        const _backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
        fetch(_backendUrl + '/api/tourist/destinations/' + destId + '/favorite', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token,
            }
        }).catch(e => {
            console.warn('Background favorite sync error:', e);
        });
    };

    window.openSheet = function(locationData) {
        if (!locationData) return;
        if (window.closeNearbySitesSheet) {
            window.closeNearbySitesSheet();
        }
        const targetSheet = document.getElementById('place-details-sheet');
        if (!targetSheet) return;
        const scrollArea = targetSheet.querySelector('.draggable-content');
        if (scrollArea) {
            scrollArea.scrollTop = 0;
        }
        window.currentDestinationForRoute = locationData;
        if (window.activePopup) {
            window.activePopup.remove();
        }
        const titleEl = document.getElementById('sheet-title');
        if (titleEl) titleEl.textContent = locationData.name || '';
        
        const locElement = document.getElementById('sheet-location');
        const locContainer = document.getElementById('sheet-location-container');
        const displayLoc = (locationData.location && locationData.location.trim() !== '') 
            ? locationData.location 
            : (locationData.municipality ? (locationData.municipality + ', La Union') : 'La Union, Philippines');

        if (locElement && locContainer) {
            locElement.textContent = displayLoc;
            locContainer.style.display = 'flex';
        }

        const catBadge = document.getElementById('sheet-category-badge');
        if (catBadge) {
            catBadge.innerHTML = '';
            if (locationData.category && locationData.category.trim() !== '') {
                const cats = locationData.category.split(',').map(c => c.trim()).filter(Boolean);
                cats.slice(0, 3).forEach(cat => {
                    const pill = document.createElement('span');
                    pill.className = 'sheet-tag-pill';
                    const formatted = cat.charAt(0).toUpperCase() + cat.slice(1).toLowerCase();
                    pill.innerHTML = `<i class="fa-solid fa-tag" style="font-size:8px; opacity:0.8; margin-right:3px;"></i>${formatted}`;
                    catBadge.appendChild(pill);
                });
                catBadge.style.display = 'flex';
            } else {
                catBadge.style.display = 'none';
            }
        }

        const statusBadge = document.getElementById('sheet-status-badge');
        if (statusBadge) {
            if (locationData.classification_status) {
                statusBadge.style.display = 'inline-flex';
                if (locationData.classification_status === 'EXIST') {
                    statusBadge.className = 'sheet-status-pill status-exist';
                    statusBadge.innerHTML = '<i class="fa-solid fa-circle-check" style="font-size:8px; margin-right:3px;"></i>Existing';
                } else if (locationData.classification_status === 'EMERGE') {
                    statusBadge.className = 'sheet-status-pill status-emerge';
                    statusBadge.innerHTML = '<i class="fa-solid fa-sparkles" style="font-size:8px; margin-right:3px;"></i>Emerging';
                } else if (locationData.classification_status === 'POTENTIAL') {
                    statusBadge.className = 'sheet-status-pill status-potential';
                    statusBadge.innerHTML = '<i class="fa-solid fa-compass" style="font-size:8px; margin-right:3px;"></i>Potential';
                } else {
                    statusBadge.style.display = 'none';
                }
            } else {
                statusBadge.style.display = 'none';
            }
        }

        // Open/Closed badge with pulse indicator
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
                    openBadge.style.display = 'inline-flex';
                    openBadge.className = 'sheet-open-pill status-maint';
                    openBadge.innerHTML = '<span class="pulse-dot dot-amber"></span>Maintenance';
                } else if (currentMinutes >= openMinutes && currentMinutes < closeMinutes) {
                    openBadge.style.display = 'inline-flex';
                    openBadge.className = 'sheet-open-pill status-open';
                    openBadge.innerHTML = '<span class="pulse-dot dot-green"></span>Open Now';
                } else {
                    openBadge.style.display = 'inline-flex';
                    openBadge.className = 'sheet-open-pill status-closed';
                    openBadge.innerHTML = '<span class="pulse-dot dot-red"></span>Closed';
                }
            } else {
                openBadge.style.display = 'none';
            }
        }

        // Sync heart button state (red if saved, faded if unsaved)
        window.updateSheetFavButton(window.isPlaceSaved(locationData.id));
        
        const token = localStorage.getItem('intan_elyu_token');
        if (token && !window.savedPlaceIdsFetched) {
            fetch((window.backendUrl || '') + '/api/tourist/dashboard', {
                headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
            }).then(r => r.ok ? r.text() : null).then(txt => {
                const d = txt ? window.safeJsonParse(txt, null) : null;
                if (d && d.savedPlaces) {
                    const ids = d.savedPlaces.map(p => p.id);
                    localStorage.setItem('intan_elyu_saved_place_ids', JSON.stringify(ids));
                    window.savedPlaceIdsFetched = true;
                    if (window.currentDestinationForRoute) {
                        window.updateSheetFavButton(window.isPlaceSaved(window.currentDestinationForRoute.id));
                    }
                }
            }).catch(e => console.error(e));
        }
        
        const fallbackBanner = window.noImageFallback || 'assets/img/no_image.svg';
        const imagesList = (window.getDestImages ? window.getDestImages(locationData, 600) : [window.getDestImage(locationData, 600)]);
        const track = document.getElementById('sheet-slider-track');
        const dotsContainer = document.getElementById('sheet-slider-dots');

        if (track) {
            track.innerHTML = '';
            track.scrollLeft = 0;

            const finalImages = (imagesList && imagesList.length > 0) ? imagesList : [fallbackBanner];

            finalImages.forEach((imgUrl, idx) => {
                const img = document.createElement('img');
                img.src = (imgUrl && imgUrl !== window.noImageFallback) ? imgUrl : fallbackBanner;
                img.alt = locationData.name || 'Place Image';
                img.className = 'sheet-img';
                img.style.cssText = 'flex:0 0 100%; min-width:100%; width:100%; max-width:100%; height:100% !important; object-fit:cover !important; object-position:center !important; border-radius:20px !important; scroll-snap-align:start; scroll-snap-stop:always; display:block !important; margin:0 !important; box-sizing:border-box !important;';
                img.onerror = function() {
                    this.onerror = null;
                    if (window.handleImgError) {
                        window.handleImgError(this, locationData.name, locationData.municipality);
                    } else {
                        this.src = fallbackBanner;
                    }
                };
                track.appendChild(img);
            });

            if (window.sheetSliderTimer) {
                clearInterval(window.sheetSliderTimer);
                window.sheetSliderTimer = null;
            }

            if (dotsContainer) {
                if (finalImages.length > 1) {
                    dotsContainer.style.display = 'flex';
                    dotsContainer.innerHTML = finalImages.map((_, i) => 
                        `<span class="slider-dot" data-index="${i}" style="width:${i === 0 ? '16px' : '6px'}; height:6px; border-radius:3px; background:${i === 0 ? '#38bdf8' : 'rgba(255,255,255,0.4)'}; transition:all 0.3s ease;"></span>`
                    ).join('');

                    track.onscroll = function() {
                        const scrollPos = track.scrollLeft;
                        const width = track.offsetWidth || 1;
                        const activeIndex = Math.round(scrollPos / width);
                        const dots = dotsContainer.querySelectorAll('.slider-dot');
                        dots.forEach((dot, i) => {
                            if (i === activeIndex) {
                                dot.style.width = '16px';
                                dot.style.background = '#38bdf8';
                            } else {
                                dot.style.width = '6px';
                                dot.style.background = 'rgba(255,255,255,0.4)';
                            }
                        });
                    };

                    // Auto-slide interval timer (every 3.5s)
                    window.sheetSliderTimer = setInterval(() => {
                        if (!track) return;
                        const width = track.offsetWidth || 1;
                        const currentIdx = Math.round(track.scrollLeft / width);
                        const nextIdx = (currentIdx + 1) % finalImages.length;
                        track.scrollTo({
                            left: nextIdx * width,
                            behavior: 'smooth'
                        });
                    }, 3500);

                    // Pause auto-slide on user touch
                    const pauseAutoSlide = () => {
                        if (window.sheetSliderTimer) {
                            clearInterval(window.sheetSliderTimer);
                            window.sheetSliderTimer = null;
                        }
                    };
                    track.addEventListener('touchstart', pauseAutoSlide, { passive: true, once: true });
                } else {
                    dotsContainer.style.display = 'none';
                    track.onscroll = null;
                }
            }
        }
        

        // 1. Accessibility Warning
        const warningEl = document.getElementById('vehicle-accessibility-warning');
        if (warningEl) {
            if (locationData.accessible_by_private_vehicle === false || locationData.accessible_by_private_vehicle === 0) {
                warningEl.style.display = 'flex';
            } else {
                warningEl.style.display = 'none';
            }
        }

        // 2. Fees & Pricing Breakdown
        const feeMainText = document.getElementById('sheet-fee-main-text');
        const feeTags = document.getElementById('sheet-fee-breakdown-tags');
        if (feeMainText && feeTags) {
            const entranceFee = parseFloat(locationData.entrance_fee || 0);
            const environmentalFee = parseFloat(locationData.environmental_fee || 0);
            const feeTypes = Array.isArray(locationData.fee_types) ? locationData.fee_types : [];
            const hasEntrance = feeTypes.includes('entrance') || feeTypes.includes('Entrance Fee') || entranceFee > 0;
            const hasEnvironmental = feeTypes.includes('environmental') || feeTypes.includes('Environmental Fee') || environmentalFee > 0;

            let tagsHtml = '';
            if (hasEntrance && entranceFee > 0) {
                tagsHtml += `<span style="font-size:11px; font-weight:800; background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); padding:3px 8px; border-radius:8px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-ticket" style="font-size:10px;"></i> Entrance: ₱${entranceFee.toFixed(2)}</span>`;
            }
            if (hasEnvironmental && environmentalFee > 0) {
                tagsHtml += `<span style="font-size:11px; font-weight:800; background:rgba(52,211,153,0.12); color:#34d399; border:1px solid rgba(52,211,153,0.3); padding:3px 8px; border-radius:8px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-leaf" style="font-size:10px;"></i> Envi: ₱${environmentalFee.toFixed(2)}</span>`;
            }

            if (tagsHtml !== '') {
                const total = (hasEntrance ? entranceFee : 0) + (hasEnvironmental ? environmentalFee : 0);
                feeMainText.textContent = total > 0 ? `₱${total.toFixed(2)} Total Fees` : 'Free Admission';
                feeTags.innerHTML = tagsHtml;
            } else {
                feeMainText.textContent = 'Free Admission';
                feeTags.innerHTML = `<span style="font-size:10.5px; font-weight:800; background:rgba(52,211,153,0.15); color:#34d399; border:1px solid rgba(52,211,153,0.3); padding:3px 8px; border-radius:8px;">No Entrance Fee</span>`;
            }
        }

        // 3. Route Guide
        const manualGuideEl = document.getElementById('sheet-manual-guide');
        if (manualGuideEl) {
            let manualGuide = (locationData.route_guide && locationData.route_guide.trim())
                ? locationData.route_guide.trim()
                : ("From the town proper of " + (locationData.municipality || "La Union") + ", take a local tricycle heading to " + (locationData.barangay || locationData.location || "the barangay") + ". Ask the driver to drop you off at " + (locationData.name || "this location") + ".");
            manualGuideEl.textContent = manualGuide;
        }

        // 4. Tour Guide Notice
        const tourGuideTextEl = document.getElementById('sheet-tour-guide-text');
        if (tourGuideTextEl) {
            let tourGuideNotice = (locationData.tour_guide_notice && locationData.tour_guide_notice.trim())
                ? locationData.tour_guide_notice.trim()
                : "Some destinations may require a tour guide for entry or navigation. The system only provides informational notices about this requirement; it does not offer, book, or arrange tour guide services directly.";
            tourGuideTextEl.textContent = tourGuideNotice;
        }

        // 5. Service Centers & Support
        const scContainer = document.getElementById('sheet-service-centers-container');
        const supportBadgeEl = document.getElementById('sheet-support-badge');
        if (scContainer) {
            if (locationData.service_centers && locationData.service_centers.length > 0) {
                scContainer.style.display = 'flex';
                scContainer.style.flexDirection = 'column';
                scContainer.style.gap = '6px';
                if (supportBadgeEl) supportBadgeEl.textContent = `${locationData.service_centers.length} Service Center${locationData.service_centers.length > 1 ? 's' : ''}`;
                scContainer.innerHTML = locationData.service_centers.map(sc => `
                    <div style="background:rgba(56,189,248,0.06); border:1px solid rgba(56,189,248,0.2); border-radius:12px; padding:8px 10px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                            <span style="font-size:12px; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:5px;">
                                <i class="fa-solid fa-building-flag" style="font-size:11px;"></i> ${sc.name}
                            </span>
                            <span style="font-size:9.5px; font-weight:700; color:#94a3b8; background:rgba(255,255,255,0.08); padding:1px 6px; border-radius:4px;">${sc.type || 'Terminal'}</span>
                        </div>
                        ${sc.address ? `<div style="font-size:11px; color:rgba(226,232,240,0.85); margin-bottom:2px;"><i class="fa-solid fa-location-dot" style="font-size:10px; color:#38bdf8; margin-right:4px;"></i>${sc.address}</div>` : ''}
                        ${sc.contact_number ? `<div style="font-size:11px; color:#34d399; font-weight:700;"><i class="fa-solid fa-phone" style="font-size:10px; margin-right:4px;"></i><a href="tel:${sc.contact_number}" style="color:#34d399; text-decoration:none;">${sc.contact_number}</a></div>` : ''}
                    </div>
                `).join('');
            } else {
                scContainer.style.display = 'none';
                scContainer.innerHTML = '';
                if (supportBadgeEl) supportBadgeEl.textContent = 'LUPTO / MTO';
            }
        }
        
        const distanceEl = document.getElementById('sheet-distance');
        if (distanceEl) distanceEl.textContent = 'Calculating...';

        const hoursCard = document.getElementById('sheet-hours-stat-card');
        const hoursEl = document.getElementById('sheet-hours');
        if (hoursEl) {
            if (locationData.opening_time && locationData.closing_time) {
                if (hoursCard) hoursCard.style.display = 'flex';
                const fmt = (t) => { const p = t.split(':'); const h = parseInt(p[0]), m = p[1]; return (h % 12 || 12) + ':' + m + (h < 12 ? ' AM' : ' PM'); };
                hoursEl.textContent = fmt(locationData.opening_time) + ' — ' + fmt(locationData.closing_time);
            } else {
                if (hoursCard) hoursCard.style.display = 'none';
            }
        }

        const servicePhoneEl = document.getElementById('sheet-service-phone');
        if (servicePhoneEl && locationData.service_phone) {
            servicePhoneEl.textContent = locationData.service_phone;
            servicePhoneEl.href = 'tel:' + locationData.service_phone.replace(/[^0-9+]/g, '');
        }

        if (window.getDeviceLocation) {
            window.getDeviceLocation().then(async (pos) => {
                const startLat = pos.coords.latitude;
                const startLng = pos.coords.longitude;
                const destLat = parseFloat(locationData.lat || locationData.latitude);
                const destLng = parseFloat(locationData.lng || locationData.longitude);
                try {
                    const res = await fetch(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${destLng},${destLat}?overview=false`);
                    const text = await res.text();
                    const routeData = window.safeJsonParse(text, null);
                    if (routeData && routeData.code === 'Ok' && routeData.routes && routeData.routes.length > 0) {
                        const distanceKm = routeData.routes[0].distance / 1000;
                        if (distanceEl) distanceEl.textContent = distanceKm.toFixed(1) + ' km';
                    } else {
                        if (distanceEl) distanceEl.textContent = 'Unknown';
                    }
                } catch (e) {
                    if (distanceEl) distanceEl.textContent = 'Unknown';
                }
            }).catch(() => {
                if (distanceEl) distanceEl.textContent = 'Location needed';
            });
        }

        const descContainer = document.getElementById('sheet-desc-container');
        const descShort = document.getElementById('sheet-desc-short');
        const descFull = document.getElementById('sheet-desc-full');
        const btnViewDetails = document.getElementById('btn-view-details');

        if (descContainer) {
            descContainer.style.display = 'block';
            if (locationData.description) {
                const words = locationData.description.split(' ');
                if (words.length > 40) {
                    if (descShort) descShort.textContent = words.slice(0, 40).join(' ') + '...';
                    if (descFull) descFull.textContent = locationData.description;
                } else {
                    if (descShort) descShort.textContent = locationData.description;
                    if (descFull) descFull.textContent = '';
                }
            } else {
                if (descShort) descShort.textContent = 'No description available.';
                if (descFull) descFull.textContent = '';
            }
            if (btnViewDetails) btnViewDetails.style.display = 'flex';
            if (descShort) descShort.style.display = 'block';
            if (descFull) descFull.style.display = 'none';
        }
        
        // Reset toggle state every time we open a sheet
        document.getElementById('expanded-details').style.display = 'none';
        document.getElementById('details-chevron').style.transform = 'rotate(0deg)';
        const btnText = document.getElementById('details-btn-text');
        if (btnText) btnText.textContent = 'View Full Details';

        if (locationData && locationData.id) {
            window.currentSelectedSpotId = locationData.id;
            const testimoniesSec = document.getElementById('sheet-testimonies-section');
            if (testimoniesSec) {
                testimoniesSec.style.display = 'block';
                fetchTestimonies(locationData.id);
            }
        }

        const placeSheet = document.getElementById('place-details-sheet');
        if (placeSheet.openSheet) placeSheet.openSheet(true);
        else placeSheet.classList.add('active');
    };

    window.closeSheet = function() {
        if (window.sheetSliderTimer) {
            clearInterval(window.sheetSliderTimer);
            window.sheetSliderTimer = null;
        }
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

        const getDbVehicle = (searchName) => {
            if (window.vehicleData && Array.isArray(window.vehicleData)) {
                return window.vehicleData.find(v => v.name.toLowerCase().includes(searchName.toLowerCase()) || searchName.toLowerCase().includes(v.name.toLowerCase()));
            }
            return null;
        };

        const trikeInfo = getDbVehicle('Tricycle');
        const jeepInfo  = getDbVehicle('Jeepney');
        const busInfo   = getDbVehicle('Bus');
        const taxiInfo  = getDbVehicle('Taxi');
        const carInfo   = getDbVehicle('Private Car') || getDbVehicle('Car');

        const trikeIcon = trikeInfo?.icon || 'fa-motorcycle';
        const jeepIcon  = jeepInfo?.icon  || 'fa-bus';
        const busIcon   = busInfo?.icon   || 'fa-bus-alt';
        const taxiIcon  = taxiInfo?.icon  || 'fa-taxi';
        const carIcon   = carInfo?.icon   || 'fa-car';

        const trikeDesc = trikeInfo?.description || 'Fits narrow roads, best for short trips';
        const jeepDesc  = jeepInfo?.description  || 'Main roads / highways only';
        const busDesc   = busInfo?.description   || 'Main roads / highways — best for long distance';
        const taxiDesc  = taxiInfo?.description  || 'Main roads / highways — metered fare';
        const carDesc   = carInfo?.description   || 'Cannot go on tight/narrow roads';

        const carKml = carInfo?.fuel_efficiency_kml ? parseFloat(carInfo.fuel_efficiency_kml) : 12.0;
        const currentFuelPrice = window.fuelPrice || 65.0;

        if (tightRoads) {
            const trikeFare = dbFare('Tricycle') ?? Math.round(20 + (Math.max(0, distanceKm - 1) * 10));
            faresHtml += createCard('Tricycle', trikeIcon, 'var(--secondary-color)', trikeInfo?.description || 'Only vehicle that fits narrow/tight roads', trikeFare, '24/7 (Night Rates 10PM+)');
        } else {
            if (distanceKm <= 5) {
                const trikeFare = dbFare('Tricycle') ?? Math.round(20 + (Math.max(0, distanceKm - 1) * 10));
                faresHtml += createCard('Tricycle', trikeIcon, 'var(--secondary-color)', trikeDesc, trikeFare, '24/7 (Night Rates 10PM+)');
            }
            if (distanceKm >= 2) {
                const taxiFare = Math.round(40 + (distanceKm * 13));
                faresHtml += createCard('Taxi', taxiIcon, '#f97316', taxiDesc, taxiFare, '24/7 Service');
            }
            if (distanceKm >= 3 && distanceKm <= 20) {
                const jeepFare = dbFare('Jeepney') ?? Math.round(15 + (distanceKm * 2.5));
                faresHtml += createCard('Jeepney', jeepIcon, '#f59e0b', jeepDesc, jeepFare, '6:00 AM - 8:00 PM');
            }
            if (distanceKm > 15) {
                const busFare = dbFare('Bus') ?? Math.round(20 + (distanceKm * 1.8));
                faresHtml += createCard('Bus', busIcon, '#ef4444', busDesc, busFare, '4:00 AM - 11:00 PM');
            }
            const ownCarFare = Math.max(10, Math.round((distanceKm / carKml) * currentFuelPrice));
            faresHtml += createCard('Own Car (Fuel Est.)', carIcon, '#34d399', carDesc, ownCarFare, 'Anytime');
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
        const _backendBase = window.backendUrl || '';

        try {
            const headers = { 'Accept': 'application/json' };
            if (token) headers['Authorization'] = 'Bearer ' + token;

            let res = await fetch(_backendBase + '/api/public/feedback?tourist_spot_id=' + spotId, { headers });
            if (!res.ok && token) {
                res = await fetch(_backendBase + '/api/tourist/feedback?tourist_spot_id=' + spotId, { headers });
            }
            const text = await res.text();
            const d = window.safeJsonParse(text, null);
            if (d && d.status === 'success') {
                // Render summary metrics
                if (d.summary && d.summary.total_reviews > 0) {
                    const sm = d.summary;
                    const reviewCount = parseInt(sm.total_reviews) || 0;
                    const reviewText = reviewCount === 1 ? '1 Review' : `${reviewCount} Reviews`;
                    const avgRating = parseFloat(sm.average_rating || 5).toFixed(1);

                    const cleanVal = sm.cleanliness.clean >= sm.cleanliness.moderate && sm.cleanliness.clean >= sm.cleanliness.dirty ? 'Clean' : (sm.cleanliness.moderate >= sm.cleanliness.dirty ? 'Moderate' : 'Dirty');
                    const cleanColor = cleanVal === 'Clean' ? '#34c759' : (cleanVal === 'Moderate' ? '#f59e0b' : '#ef4444');
                    const cleanBg = cleanVal === 'Clean' ? 'rgba(52,199,89,0.12)' : (cleanVal === 'Moderate' ? 'rgba(245,158,11,0.12)' : 'rgba(239,68,68,0.12)');

                    const safeVal = sm.safety.safe >= sm.safety.moderate && sm.safety.safe >= sm.safety.unsafe ? 'Safe' : (sm.safety.moderate >= sm.safety.unsafe ? 'Moderate' : 'Unsafe');
                    const safeColor = safeVal === 'Safe' ? '#34c759' : (safeVal === 'Moderate' ? '#f59e0b' : '#ef4444');
                    const safeBg = safeVal === 'Safe' ? 'rgba(52,199,89,0.12)' : (safeVal === 'Moderate' ? 'rgba(245,158,11,0.12)' : 'rgba(239,68,68,0.12)');

                    summary.style.display = 'block';
                    summary.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
                            <strong style="color:#ffffff; font-size:13px; font-weight:800; display:flex; align-items:center; gap:6px;">
                                <i class="fa-solid fa-chart-simple" style="color:#38bdf8; font-size:11px;"></i> Visitor Insights (${reviewText})
                            </strong>
                            <div style="display:flex; align-items:center; gap:4px; background:rgba(251,191,36,0.12); border:1px solid rgba(251,191,36,0.3); padding:2px 8px; border-radius:100px;">
                                <i class="fa-solid fa-star" style="color:#fbbf24; font-size:11px;"></i>
                                <span style="color:#fbbf24; font-size:12px; font-weight:800;">${avgRating}</span>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px;">
                            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:12px; padding:8px 10px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:10px; color:rgba(226,232,240,0.6); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Cleanliness</span>
                                <span style="font-size:11px; font-weight:800; color:${cleanColor}; background:${cleanBg}; border:1px solid ${cleanColor}40; padding:2px 8px; border-radius:6px;">${cleanVal}</span>
                            </div>
                            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:12px; padding:8px 10px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:10px; color:rgba(226,232,240,0.6); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Safety</span>
                                <span style="font-size:11px; font-weight:800; color:${safeColor}; background:${safeBg}; border:1px solid ${safeColor}40; padding:2px 8px; border-radius:6px;">${safeVal}</span>
                            </div>
                        </div>`;
                } else {
                    summary.style.display = 'none';
                }

                // Render testimonies list
                if (d.data && d.data.length > 0) {
                    const maskUserName = (name) => {
                        if (!name || typeof name !== 'string') return 'Explorer';
                        const trimmed = name.trim();
                        if (!trimmed) return 'Explorer';
                        
                        const parts = trimmed.split(/\s+/);
                        if (parts.length === 1) {
                            const w = parts[0];
                            if (w.length <= 2) return w.charAt(0) + '*';
                            if (w.length <= 4) return w.slice(0, 2) + '*'.repeat(w.length - 2);
                            return w.slice(0, 2) + '*'.repeat(w.length - 4) + w.slice(-2);
                        }
                        
                        return parts.map((part, index) => {
                            const len = part.length;
                            if (len <= 2) return part.charAt(0) + '*';
                            
                            if (index === 0) {
                                // First name (e.g. "temi" -> "te**")
                                const visible = Math.min(2, Math.max(1, len - 2));
                                return part.slice(0, visible) + '*'.repeat(len - visible);
                            } else if (index === parts.length - 1) {
                                // Last name (e.g. "simer" -> "***er")
                                const visible = Math.min(2, Math.max(1, len - 2));
                                return '*'.repeat(len - visible) + part.slice(-visible);
                            } else {
                                // Middle names
                                return '*'.repeat(len);
                            }
                        }).join(' ');
                    };

                    const renderCard = (fb) => {
                        const user = fb.user || { name: 'Explorer' };
                        const rawName = user.name || user.full_name || 'Explorer';
                        const maskedName = maskUserName(rawName);
                        const initial = (rawName || 'E').charAt(0).toUpperCase();
                        const rating = parseInt(fb.rating) || 5;
                        let starsHtml = '';
                        for (let s = 1; s <= 5; s++) {
                            starsHtml += s <= rating 
                                ? '<i class="fa-solid fa-star" style="color:#fbbf24; font-size:11px;"></i>' 
                                : '<i class="fa-regular fa-star" style="color:rgba(255,255,255,0.2); font-size:11px;"></i>';
                        }
                        const date = fb.created_at ? new Date(fb.created_at).toLocaleDateString() : '';
                        const policyHtml = fb.policy_recommendation ? `
                            <div style="background:rgba(56,189,248,0.06); border:1px solid rgba(56,189,248,0.2); padding:10px 12px; border-radius:12px; margin-top:10px;">
                                <div style="display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                                    <i class="fa-solid fa-lightbulb" style="color:#38bdf8; font-size:11px;"></i>
                                    <strong style="font-size:10px; color:#38bdf8; text-transform:uppercase; letter-spacing:0.5px; font-weight:800;">Policy Recommendation</strong>
                                </div>
                                <span style="color:rgba(226,232,240,0.9); font-size:12px; line-height:1.4; display:block;">${fb.policy_recommendation}</span>
                            </div>` : '';

                        return `
                        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:14px; border-radius:16px; font-size:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg, #38bdf8, #2563eb); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:11px;">
                                        ${initial}
                                    </div>
                                    <strong style="color:#ffffff; font-size:13px; font-weight:700;">${maskedName}</strong>
                                </div>
                                <div style="display:flex; gap:2px; align-items:center;">${starsHtml}</div>
                            </div>
                            <p style="margin:0; color:rgba(226,232,240,0.88); font-size:12.5px; line-height:1.5;">${fb.testimony || 'Visited and checked in.'}</p>
                            ${policyHtml}
                            <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                                <span style="font-size:10px; color:rgba(148,163,184,0.6); font-weight:600;"><i class="fa-regular fa-clock" style="margin-right:3px; font-size:9px;"></i>${date}</span>
                            </div>
                        </div>`;
                    };

                    let html = renderCard(d.data[0]);

                    if (d.data.length > 1) {
                        let extraCardsHtml = '';
                        for (let i = 1; i < d.data.length; i++) {
                            extraCardsHtml += renderCard(d.data[i]);
                        }
                        html += `
                        <div id="extra-testimonies-container" data-total-count="${d.data.length}" style="display:none; flex-direction:column; gap:10px; margin-top:10px; max-height:0; opacity:0; transform:translateY(-8px); overflow:hidden; transition:max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease, transform 0.35s ease;">
                            ${extraCardsHtml}
                        </div>
                        <div style="display:flex; justify-content:center; margin-top:6px;">
                            <button id="btn-toggle-testimonies" onclick="window.toggleAllTestimonies()" style="background:rgba(56,189,248,0.06); border:none; color:#38bdf8; font-size:11px; font-weight:700; padding:6px 14px; border-radius:20px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
                                <span id="toggle-testimonies-text">View All Testimonies (${d.data.length})</span>
                                <i class="fa-solid fa-chevron-down" id="toggle-testimonies-chevron" style="transition:transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);"></i>
                            </button>
                        </div>`;
                    }

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

    window.toggleAllTestimonies = function() {
        const extraContainer = document.getElementById('extra-testimonies-container');
        const btnText = document.getElementById('toggle-testimonies-text');
        const chevron = document.getElementById('toggle-testimonies-chevron');
        if (!extraContainer) return;

        const isExpanded = extraContainer.classList.contains('expanded');

        if (!isExpanded) {
            extraContainer.style.display = 'flex';
            void extraContainer.offsetHeight; // force reflow
            extraContainer.style.maxHeight = '2500px';
            extraContainer.style.opacity = '1';
            extraContainer.style.transform = 'translateY(0)';
            extraContainer.classList.add('expanded');

            if (btnText) btnText.textContent = 'Show Less';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            extraContainer.style.maxHeight = '0';
            extraContainer.style.opacity = '0';
            extraContainer.style.transform = 'translateY(-8px)';
            extraContainer.classList.remove('expanded');

            const totalCount = extraContainer.dataset.totalCount || '';
            if (btnText) btnText.textContent = 'View All Testimonies' + (totalCount ? ` (${totalCount})` : '');
            if (chevron) chevron.style.transform = 'rotate(0deg)';

            setTimeout(() => {
                if (!extraContainer.classList.contains('expanded')) {
                    extraContainer.style.display = 'none';
                }
            }, 380);
        }
    };

    window.openWriteTestimonyModal = function() {
        if (!window.currentSelectedSpotId) return;
        document.getElementById('testimony-spot-id').value = window.currentSelectedSpotId;
        window.setStarRating(5);
        if (typeof window.selectCleanliness === 'function') window.selectCleanliness('clean');
        if (typeof window.selectSafety === 'function') window.selectSafety('safe');
        document.getElementById('testimony-comment').value = '';
        document.getElementById('testimony-policy').value = '';

        const modal = document.getElementById('testimony-modal');
        if (modal) {
            modal.style.display = 'flex';
            void modal.offsetHeight; // force reflow
            modal.classList.add('active');
        }
    };

    window.closeWriteTestimonyModal = function() {
        const modal = document.getElementById('testimony-modal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                if (!modal.classList.contains('active')) {
                    modal.style.display = 'none';
                }
            }, 320);
        }
    };

    window.setStarRating = function(rating) {
        document.getElementById('testimony-rating').value = rating;
        document.querySelectorAll('.star-btn').forEach((btn, index) => {
            const starNum = parseInt(btn.dataset.star);
            btn.classList.remove('pop-anim');
            void btn.offsetWidth; // force reflow for animation reset

            if (starNum <= rating) {
                btn.style.opacity = '1';
                btn.className = 'fa-solid fa-star star-btn';
                setTimeout(() => {
                    btn.classList.add('pop-anim');
                }, index * 40);
            } else {
                btn.style.opacity = '0.35';
                btn.className = 'fa-regular fa-star star-btn';
                btn.style.filter = 'none';
            }
        });
    };


    window.selectCleanliness = function(val) {
        const input = document.getElementById('testimony-cleanliness');
        if (input) input.value = val;

        const labelMap = { clean: 'Clean', moderate: 'Moderate', dirty: 'Dirty' };
        const colorMap = { clean: '#10b981', moderate: '#f59e0b', dirty: '#f43f5e' };
        const bgMap = { clean: 'rgba(16,185,129,0.18)', moderate: 'rgba(245,158,11,0.18)', dirty: 'rgba(244,63,94,0.18)' };

        const labelEl = document.getElementById('cleanliness-selected-label');
        if (labelEl) {
            labelEl.textContent = labelMap[val] || val;
            labelEl.style.color = colorMap[val] || '#38bdf8';
        }

        document.querySelectorAll('.clean-pill').forEach(btn => {
            if (btn.dataset.val === val) {
                btn.classList.add('active');
                btn.style.borderColor = colorMap[val];
                btn.style.background = bgMap[val];
                btn.style.color = colorMap[val];
                btn.style.boxShadow = `0 0 12px ${colorMap[val]}33`;
            } else {
                btn.classList.remove('active');
                btn.style.borderColor = 'rgba(255,255,255,0.1)';
                btn.style.background = 'rgba(255,255,255,0.04)';
                btn.style.color = 'rgba(255,255,255,0.7)';
                btn.style.boxShadow = 'none';
            }
        });
    };

    window.selectSafety = function(val) {
        const input = document.getElementById('testimony-safety');
        if (input) input.value = val;

        const labelMap = { safe: 'Safe', moderate: 'Moderate', unsafe: 'Unsafe' };
        const colorMap = { safe: '#10b981', moderate: '#f59e0b', unsafe: '#f43f5e' };
        const bgMap = { safe: 'rgba(16,185,129,0.18)', moderate: 'rgba(245,158,11,0.18)', unsafe: 'rgba(244,63,94,0.18)' };

        const labelEl = document.getElementById('safety-selected-label');
        if (labelEl) {
            labelEl.textContent = labelMap[val] || val;
            labelEl.style.color = colorMap[val] || '#38bdf8';
        }

        document.querySelectorAll('.safety-pill').forEach(btn => {
            if (btn.dataset.val === val) {
                btn.classList.add('active');
                btn.style.borderColor = colorMap[val];
                btn.style.background = bgMap[val];
                btn.style.color = colorMap[val];
                btn.style.boxShadow = `0 0 12px ${colorMap[val]}33`;
            } else {
                btn.classList.remove('active');
                btn.style.borderColor = 'rgba(255,255,255,0.1)';
                btn.style.background = 'rgba(255,255,255,0.04)';
                btn.style.color = 'rgba(255,255,255,0.7)';
                btn.style.boxShadow = 'none';
            }
        });
    };

    window.submitTestimony = async function(event) {
        event.preventDefault();
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) {
            if (typeof showToast === 'function') showToast("Please log in to submit a review.");
            return;
        }
        const _backendBase = window.backendUrl || '';

        const spotId = document.getElementById('testimony-spot-id').value;
        const rating = document.getElementById('testimony-rating').value;
        const testimony = document.getElementById('testimony-comment').value;
        const policy = document.getElementById('testimony-policy').value;
        const cleanliness = document.getElementById('testimony-cleanliness').value;
        const safety = document.getElementById('testimony-safety').value;

        try {
            const response = await fetch(_backendBase + '/api/tourist/feedback', {
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
                    cleanliness_level: cleanliness,
                    safety_level: safety
                })
            });

            const text = await response.text();
            const data = window.safeJsonParse(text, {});
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

    setTimeout(window.initMap, 50);

    // Auto-refresh: poll for new spots every 10s
    async function checkForNewSpots() {
        if (!window.mapInstance) return;
        try {
            const res = await fetch((window.backendUrl || '') + '/api/public/map', {
                headers: { 'Accept': 'application/json' }
            });
            const text = await res.text();
            const data = window.safeJsonParse(text, null);
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

