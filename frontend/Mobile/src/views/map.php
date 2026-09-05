<!-- Map View -->
<?php
$pageTitle = 'Explore Map';
$activeTab = 'map';

$municipalityImages = [];
$imgDir = __DIR__ . '/../assets/img/MUNICIPALITIES';
if (is_dir($imgDir)) {
    $munis = scandir($imgDir);
    foreach ($munis as $muni) {
        if ($muni === '.' || $muni === '..')
            continue;
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

    window.getFareFromMatrix = function (vehicleType, distanceKm, municipality = null) {
        if (!window.fareData) return null;
        
        const dKm = parseFloat(distanceKm) || 0;
        const normType = (vehicleType || '').toString().toLowerCase().trim();
        
        // For own_car and taxi — not in fare matrix table, use formula fallback
        if (normType === 'own_car' || normType === 'taxi' || normType === 'own car') return null;

        let fareEntry = null;

        if (normType === 'tricycle' || normType === 'trike') {
            const muniKey = municipality ? municipality.toLowerCase().trim().replace(/[^a-z0-9]/g, '_') : 'san_juan';
            const muniRaw = municipality ? municipality.toLowerCase().trim() : 'san juan';
            
            if (window.fareData.by_municipality) {
                fareEntry = window.fareData.by_municipality[muniKey] || window.fareData.by_municipality[muniRaw];
            }
            if (!fareEntry) {
                fareEntry = window.fareData['san_juan'] || window.fareData['san juan'] || window.fareData['tricycle'];
            }
        } else if (['jeepney', 'puj_ordinary', 'puj_aircon', 'lutrampco', 'mini_bus', 'van', 'uve'].includes(normType)) {
            fareEntry = window.fareData['jeepney'] || window.fareData['lutrampco'] || window.fareData['mini_bus'] || window.fareData['van'];
        } else if (['bus', 'private_bus', 'pub_aircon', 'pub_ordinary'].includes(normType)) {
            fareEntry = window.fareData['private_bus'] || window.fareData['bus'];
        } else {
            fareEntry = window.fareData[normType];
        }

        if (!fareEntry || !fareEntry.rates) return null;
        const rates = Array.isArray(fareEntry.rates) ? fareEntry.rates : Object.values(fareEntry.rates);
        if (!rates || rates.length === 0) return null;

        // Stage ceiling lookup: find first stage bracket where rate.distance_km >= dKm
        let match = null;
        for (let i = 0; i < rates.length; i++) {
            const r = rates[i];
            if (r && r.distance_km != null && parseFloat(r.distance_km) >= dKm) {
                match = r;
                break;
            }
        }

        // If distance is higher than the max stage, scale from the highest bracket
        if (!match) {
            const maxRate = rates[rates.length - 1];
            if (maxRate && maxRate.regular_fare != null) {
                const maxD = parseFloat(maxRate.distance_km || 0);
                const extra = Math.max(0, dKm - maxD);
                const perKm = (normType === 'tricycle' || normType === 'trike') ? 2.0 : 1.8;
                return Math.round(parseFloat(maxRate.regular_fare) + (extra * perKm));
            }
        }

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

    <!-- Classification Toggle Button & Popover (Vertical on Left Side Corner) -->
    <div class="btn-classification-wrapper" style="position: absolute; bottom: calc(115px + env(safe-area-inset-bottom)); left: 10px; z-index: 895;">
        <!-- Popover showing the 3 Types of Classification (Matched to blue gradient theme) -->
        <div id="classification-popover" style="display: none; position: absolute; bottom: 0; left: calc(100% + 10px); transform-origin: bottom left; transform: scale(0.95); opacity: 0; width: 250px; background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(45, 90, 155, 0.94) 50%, rgba(63, 125, 183, 0.94) 100%) !important; backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border-radius: 20px; padding: 12px 14px; box-shadow: 0 16px 36px rgba(10, 25, 60, 0.45); border: none !important; outline: none !important; transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1); pointer-events: none;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,0.18);">
                <span style="font-size: 11px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: 0.6px;">
                    <i class="fa-solid fa-tags" style="color: #00f2fe; margin-right: 5px;"></i> Classifications
                </span>
                <span onclick="window.toggleClassificationMenu(false)" style="cursor: pointer; color: #ffffff; opacity: 0.85; font-size: 12px; padding: 2px 4px;"><i class="fa-solid fa-xmark"></i></span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <!-- 1. Existing -->
                <div class="classification-item-chip" onclick="window.filterByClassification('EXIST')" style="cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 12px; background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; transition: background 0.15s ease;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #34c759; box-shadow: 0 0 8px #34c759; flex-shrink: 0;"></span>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; font-weight: 800; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
                            <span>Existing</span>
                            <span id="count-exist" style="font-size: 10px; font-weight: 800; color: #34c759; background: rgba(52,199,89,0.22); padding: 1px 6px; border-radius: 6px;">Site</span>
                        </div>
                        <div style="font-size: 10px; color: rgba(255,255,255,0.85); font-weight: 500;">Fully developed spots & facilities</div>
                    </div>
                </div>
                <!-- 2. Emerging -->
                <div class="classification-item-chip" onclick="window.filterByClassification('EMERGE')" style="cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 12px; background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; transition: background 0.15s ease;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 8px #38bdf8; flex-shrink: 0;"></span>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; font-weight: 800; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
                            <span>Emerging</span>
                            <span id="count-emerge" style="font-size: 10px; font-weight: 800; color: #38bdf8; background: rgba(56,189,248,0.22); padding: 1px 6px; border-radius: 6px;">Site</span>
                        </div>
                        <div style="font-size: 10px; color: rgba(255,255,255,0.85); font-weight: 500;">Rising attractions gaining visitors</div>
                    </div>
                </div>
                <!-- 3. Potential -->
                <div class="classification-item-chip" onclick="window.filterByClassification('POTENTIAL')" style="cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 12px; background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: none !important; outline: none !important; transition: background 0.15s ease;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 8px #f59e0b; flex-shrink: 0;"></span>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; font-weight: 800; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
                            <span>Potential</span>
                            <span id="count-potential" style="font-size: 10px; font-weight: 800; color: #f59e0b; background: rgba(245,158,11,0.22); padding: 1px 6px; border-radius: 6px;">Site</span>
                        </div>
                        <div style="font-size: 10px; color: rgba(255,255,255,0.85); font-weight: 500;">Unspoiled spots with high promise</div>
                    </div>
                </div>
            </div>
            <div onclick="window.filterByClassification('ALL')" style="cursor: pointer; margin-top: 8px; text-align: center; font-size: 11px; font-weight: 800; color: #ffffff; padding: 7px; border-radius: 10px; background: rgba(255,255,255,0.18); transition: background 0.15s ease;">
                Show All Classifications
            </div>
        </div>

        <!-- The Classification Button (Exact 44px x 44px matching other map buttons) -->
        <button type="button" id="btn-classification-toggle" onclick="window.toggleClassificationMenu()"
            style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(63, 125, 183, 0.88) 100%) !important; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 12px; border: none !important; outline: none !important; color: #ffffff; box-shadow: 0 8px 20px rgba(10, 25, 60, 0.3); cursor: pointer; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);"
            onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'"
            title="Classifications">
            <!-- 3 Vertical Classification Dots -->
            <span style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;">
                <span id="dot-exist" style="width: 6.5px; height: 6.5px; border-radius: 50%; background: #34c759; box-shadow: 0 0 5px rgba(52,199,89,0.9); transition: transform 0.2s ease, opacity 0.2s ease;"></span>
                <span id="dot-emerge" style="width: 6.5px; height: 6.5px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 5px rgba(56,189,248,0.9); transition: transform 0.2s ease, opacity 0.2s ease;"></span>
                <span id="dot-potential" style="width: 6.5px; height: 6.5px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 5px rgba(245,158,11,0.9); transition: transform 0.2s ease, opacity 0.2s ease;"></span>
            </span>
        </button>
    </div>

    <!-- Action Buttons Stack (Stacked on the Right Side) -->
    <!-- 1. Layer Toggle Button -->
    <div class="btn-layer-toggle animate-slide-up" id="btn-layer-toggle"
        style="position: absolute; bottom: calc(295px + env(safe-area-inset-bottom)); right: 10px !important; left: auto !important; width: 44px; height: 44px; background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(63, 125, 183, 0.88) 100%); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 8px 20px rgba(10, 25, 60, 0.3); z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-layer-group"></i>
    </div>

    <!-- 2. 3D Mode Button -->
    <div class="btn-3d-view animate-slide-up" id="btn-3d-view"
        style="position: absolute; bottom: calc(235px + env(safe-area-inset-bottom)); right: 10px !important; left: auto !important; width: 44px; height: 44px; background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(63, 125, 183, 0.88) 100%); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 8px 20px rgba(10, 25, 60, 0.3); z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-cube"></i>
    </div>

    <!-- 3. Nearby Tourist Sites Button (Aligned with other 3 buttons) -->
    <div class="btn-nearby-sites animate-slide-up" id="btn-nearby-sites" onclick="window.toggleNearbySitesSheet()"
        style="position: absolute; bottom: calc(175px + env(safe-area-inset-bottom)); right: 10px !important; left: auto !important; width: 44px; height: 44px; background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(63, 125, 183, 0.88) 100%); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 8px 20px rgba(10, 25, 60, 0.3); z-index: 900; cursor: pointer; transition: all 0.2s;"
        title="Tourist Sites List">
        <i class="fa-solid fa-compass"></i>
        <span id="nearby-sites-badge"
            style="display:none; position:absolute; top:-5px; right:-5px; min-width:18px; height:18px; padding:0 4px; border-radius:9px; background:#00f2fe; color:#0f172a; font-size:10px; font-weight:800; align-items:center; justify-content:center; box-shadow:none;">0</span>
    </div>

    <!-- 4. Locate Me Button -->
    <div class="btn-locate-me animate-slide-up" id="btn-locate-me"
        style="position: absolute; bottom: calc(115px + env(safe-area-inset-bottom)); right: 10px !important; left: auto !important; width: 44px; height: 44px; background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(63, 125, 183, 0.88) 100%); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: none !important; outline: none !important; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 18px; box-shadow: 0 8px 20px rgba(10, 25, 60, 0.3); z-index: 900; cursor: pointer; transition: all 0.2s;">
        <i class="fa-solid fa-crosshairs"></i>
    </div>

    <!-- Nearby Tourist Sites Sheet (Triggered by Left Button) -->
    <div class="bottom-sheet" id="nearby-sites-sheet" style="display:none;">
        <div class="sheet-drag-handle" id="nearby-drag-handle"><span class="sheet-drag-dot"></span></div>
        <div class="draggable-content" id="nearby-sites-scroll"
            style="max-height: calc(85vh - 80px); overflow-y: auto; padding: 0 6px calc(30px + env(safe-area-inset-bottom)) 6px;">

            <!-- Header -->
            <div
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding: 2px 8px;">
                <div>
                    <h3
                        style="margin:0; font-size:17px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-compass" style="color:#ffffff;"></i> Nearby Tourist Sites
                    </h3>
                    <p id="nearby-sites-subtext" style="margin:3px 0 0 0; font-size:12px; color:#ffffff; opacity:0.95;">
                        Discover attractions close to your current location
                    </p>
                </div>
                <button type="button" onclick="window.closeNearbySitesSheet()"
                    style="background:rgba(255,255,255,0.15); border:none !important; outline:none !important; color:#ffffff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; transition:background 0.2s;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Radius filter pills -->
            <div style="display:flex; gap:8px; overflow-x:auto; padding: 4px 8px 16px 8px; scrollbar-width:none;">
                <button type="button" class="nearby-radius-btn active" data-radius="2"
                    onclick="window.filterNearbyRadius(2, this)"
                    style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:800; border:none !important; outline:none !important; background:linear-gradient(135deg, #00f2fe, #0284c7); color:#ffffff; cursor:pointer; white-space:nowrap;">Within
                    2 km</button>
                <button type="button" class="nearby-radius-btn" data-radius="5"
                    onclick="window.filterNearbyRadius(5, this)"
                    style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:700; border:none !important; outline:none !important; background:rgba(30,58,138,0.78); color:#ffffff; cursor:pointer; white-space:nowrap;">Within
                    5 km</button>
                <button type="button" class="nearby-radius-btn" data-radius="15"
                    onclick="window.filterNearbyRadius(15, this)"
                    style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:700; border:none !important; outline:none !important; background:rgba(30,58,138,0.78); color:#ffffff; cursor:pointer; white-space:nowrap;">Within
                    15 km</button>
                <button type="button" class="nearby-radius-btn" data-radius="all"
                    onclick="window.filterNearbyRadius('all', this)"
                    style="padding:6px 14px; border-radius:100px; font-size:11.5px; font-weight:700; border:none !important; outline:none !important; background:rgba(30,58,138,0.78); color:#ffffff; cursor:pointer; white-space:nowrap;">All
                    Closest</button>
            </div>

            <!-- List container -->
            <div id="nearby-sites-list" style="display:flex; flex-direction:column; gap:12px; padding: 0 4px 16px 4px;">
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

            <!-- Quick Stats Grid (Distance, Hours, Visitors, Rating) -->
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
                <div class="quick-stat-card" id="sheet-visitors-stat-card">
                    <div class="quick-stat-icon purple">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="quick-stat-info">
                        <span class="quick-stat-label">Visitors</span>
                        <span class="quick-stat-value" id="sheet-visitors">Less than 100 this month</span>
                    </div>
                </div>
                <div class="quick-stat-card" id="sheet-rating-stat-card">
                    <div class="quick-stat-icon amber">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div class="quick-stat-info">
                        <span class="quick-stat-label">Rating</span>
                        <span class="quick-stat-value" id="sheet-rating">5.0 ★</span>
                    </div>
                </div>
            </div>

            <!-- Site Fee Summary Banner -->
            <div id="sheet-fees-card" class="dest-fees-card"
                style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.12); border:none !important; outline:none !important; border-radius:16px; padding:10px 14px; margin-bottom:10px; box-shadow:0 4px 16px rgba(10,25,60,0.15);">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="dest-fee-icon-box">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div style="display:flex; flex-direction:column;">
                        <span
                            style="font-size:10px; font-weight:700; color:#e2e8f0; text-transform:uppercase; letter-spacing:0.5px;">Site
                            Fees</span>
                        <span id="sheet-fee-main-text" style="font-size:13px; font-weight:800; color:#ffffff;">Free
                            Admission</span>
                    </div>
                </div>
                <div id="sheet-fee-breakdown-tags" style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                    <!-- Injected via JS: e.g. Entrance: ₱50 | Environmental: ₱20 -->
                </div>
            </div>

            <!-- About This Location & Travel Details -->
            <div id="sheet-desc-container" class="dest-info-card" style="display:none;">
                <div id="vehicle-accessibility-warning" class="dest-warning-card" style="display:none;">
                    <div class="dest-warning-icon-box" style="width:36px; height:36px; border-radius:10px; background:#FF3B30 !important; color:#ffffff !important; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.25);">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#ffffff !important;"></i>
                    </div>
                    <div>
                        <h6>Inaccessible by Private Car</h6>
                        <p>Prepare to hike or use specialized local transport to reach this destination.</p>
                    </div>
                </div>

                <div id="sheet-desc-animator">
                    <div class="dest-section-header">
                        <div class="dest-section-icon"><i class="fa-solid fa-compass"></i></div>
                        <h5 class="dest-section-title">About this destination</h5>
                    </div>

                    <p id="sheet-desc-short" class="dest-desc-text"></p>
                    <p id="sheet-desc-full" class="dest-desc-text" style="display:none;"></p>
                    <button id="btn-view-details" class="dest-toggle-btn" onclick="window.toggleFullDetails()" style="display:none; margin:8px 0 14px;">
                        <span id="details-btn-text">Read More</span>
                        <i class="fa-solid fa-chevron-down" id="details-chevron"
                            style="transition:transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);"></i>
                    </button>

                    <!-- Expanded Details (Directly Visible) -->
                    <div id="expanded-details" class="dest-expanded-wrapper" style="display:flex;">

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
                            <p id="sheet-tour-guide-text" class="dest-advisory-text">Some destinations may require a
                                tour guide for entry or navigation. The system only provides informational notices about
                                this requirement; it does not offer, book, or arrange tour guide services directly.</p>
                        </div>

                        <!-- Nearby Amenities Box with Individual Distances -->
                        <div id="sheet-amenities-notice" class="dest-amenities-box" style="display:none;">
                            <div class="dest-amenities-header">
                                <div style="display:flex; align-items:center; gap:9px;">
                                    <span class="dest-amenities-icon-circle">
                                        <i class="fa-solid fa-map-pin"></i>
                                    </span>
                                    <div>
                                        <div style="font-size:12px; font-weight:800; color:#ffffff; line-height:1.2;">Nearby Amenities</div>
                                        <div style="font-size:10px; color:rgba(255,255,255,0.75);">Distance from this tourist site</div>
                                    </div>
                                </div>
                                <span id="sheet-amenities-count-badge" class="dest-amenities-count-badge">--</span>
                            </div>

                            <!-- List showing each amenity and its distance -->
                            <div id="sheet-amenities-list" class="dest-amenities-list"></div>

                            <!-- Expand / Collapse Button when > 4 amenities -->
                            <button type="button" id="sheet-amenities-toggle-btn" class="dest-amenities-toggle-btn" style="display:none;" onclick="window.toggleSheetAmenities()">
                                <span id="sheet-amenities-toggle-text">Show All</span>
                                <i class="fa-solid fa-chevron-down" id="sheet-amenities-chevron" style="transition:transform 0.25s ease;"></i>
                            </button>
                        </div>

                        <!-- Service Center & Assistance -->
                        <div class="dest-support-box">
                            <div class="dest-support-header">
                                <span
                                    style="font-size:12.5px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-headset" style="color:#ffffff;"></i> Tourist Support & Service Centers
                                </span>
                                <span class="dest-support-badge" id="sheet-support-badge">LUPTO / MTO</span>
                            </div>

                            <!-- Dynamic Service Centers list -->
                            <div id="sheet-service-centers-container" style="display:none; margin-bottom:8px;"></div>

                            <div class="dest-contacts-list">
                                <div class="dest-contact-row" id="sheet-service-phone-row">
                                    <span class="dest-contact-label"><i class="fa-solid fa-phone"
                                            style="font-size:10px; color:#ffffff;"></i> Service Hotline:</span>
                                    <span class="dest-contact-val"><a id="sheet-service-phone"
                                            href="tel:+630728882454" style="color:#ffffff; text-decoration:none; font-weight:700;">+63 (072) 888-2454</a></span>
                                </div>
                                <div class="dest-contact-row">
                                    <span class="dest-contact-label"><i class="fa-solid fa-clock"
                                            style="font-size:10px; color:#ffffff;"></i> Service Hours:</span>
                                    <span class="dest-contact-val" id="sheet-service-hours" style="color:#ffffff; font-weight:700;">8:00 AM - 5:00 PM
                                        (Daily)</span>
                                </div>
                                <div class="dest-contact-row">
                                    <span class="dest-contact-label"><i class="fa-solid fa-kit-medical"
                                            style="font-size:10px; color:#34d399;"></i> Emergency / Medical:</span>
                                    <span class="dest-contact-val emergency"><a href="tel:911" style="color:#34d399; font-weight:800; text-decoration:none;">MDRRMO / Call
                                            911</a></span>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonies Section -->
                        <div id="sheet-testimonies-section"
                            style="display:none; margin-top:14px; padding-top:4px; border:none !important; outline:none !important;">
                            <div
                                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                                <h4
                                    style="margin:0; font-size:12.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#fff;">
                                    Tourist Testimonies</h4>
                            </div>
                            <div id="testimonies-summary-metrics" style="display:none;"></div>
                            <div id="testimonies-list-container" style="display:flex; flex-direction:column; gap:8px;">
                                <div
                                    style="font-size:12.5px; color:#ffffff; opacity:0.95; font-weight:500; text-align:center; padding:12px 0;">
                                    No testimonies yet. Be the first to share!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="sheet-btn-row" style="display:flex; gap:10px; align-items:center; margin-top:14px;">
                <button id="btn-add-itinerary" onclick="window.addToItinerary()" class="btn-add-itinerary-premium"
                    style="flex:1;">
                    <i class="fa-solid fa-calendar-plus"></i> Add to Trip
                </button>
                <button id="sheet-fav-btn" onclick="window.toggleMapFavorite(this)" class="btn-sheet-fav"
                    aria-label="Save to favorites" style="flex-shrink:0;">
                    <i class="fa-solid fa-heart"></i>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Itinerary Add Confirmation Dialog -->
<div id="itin-add-confirm" onclick="if(event.target===this)window.closeAddConfirm()"
    style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:99999; display:flex; align-items:center; justify-content:center; opacity:0; pointer-events:none; transition:opacity 0.3s ease; background:rgba(10,25,60,0.6); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);">
    <div
        style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:24px; padding:32px 28px 24px; margin:0 24px; width:100%; max-width:320px; text-align:center; box-shadow:0 20px 60px rgba(10,25,60,0.5); transform:scale(0.85); transition:transform 0.35s cubic-bezier(0.16,1,0.3,1);">
        <div id="itin-add-confirm-icon-wrap"
            style="width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg, #34d399 0%, #10b981 100%); border:3px solid rgba(255,255,255,0.4) !important; box-shadow:0 8px 24px rgba(16,185,129,0.5), 0 0 20px rgba(52,211,153,0.45); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <i class="fa-solid fa-check" style="font-size:32px; color:#ffffff;"></i>
        </div>
        <h3 style="margin:0 0 6px; font-size:20px; font-weight:800; color:#ffffff; letter-spacing:-0.3px;">Added to
            Itinerary!</h3>
        <p style="margin:0 0 24px; font-size:14px; color:rgba(255,255,255,0.88); line-height:1.5;"
            id="itin-add-confirm-name"></p>
        <button onclick="window.viewItinerary()"
            style="width:100%; padding:14px; border:none !important; outline:none !important; border-radius:14px; background:linear-gradient(135deg,#007AFF,#0055FF); color:#fff; font-size:15px; font-weight:800; cursor:pointer; margin-bottom:10px; box-shadow:0 4px 16px rgba(0,122,255,0.4);">
            <i class="fa-solid fa-list"></i> View Itinerary
        </button>
        <button onclick="window.closeAddConfirm()"
            style="width:100%; padding:12px; border:none !important; outline:none !important; border-radius:12px; background:rgba(255,255,255,0.16); color:#ffffff; font-size:14px; font-weight:700; cursor:pointer;">
            Continue Exploring
        </button>
    </div>
</div>

<!-- Write Testimony & Policy Recommendation Modal -->
<style>
    #testimony-modal.active {
        opacity: 1 !important;
    }

    #testimony-modal.active .testimony-card-anim {
        transform: scale(1) translateY(0) !important;
        opacity: 1 !important;
    }
</style>
<div id="testimony-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.65); z-index:99999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); opacity:0; transition:opacity 0.3s ease;">
    <div class="testimony-card-anim"
        style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:24px; padding:24px; width:100%; max-width:380px; max-height:85vh; overflow-y:auto; box-shadow:0 20px 40px rgba(10,25,60,0.45); text-align:left; box-sizing:border-box; transform:scale(0.88) translateY(16px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;">
        <h3 style="margin:0 0 4px; color:#fff; font-size:18px; font-weight:800;">Review Destination</h3>
        <p style="font-size:12px; color:rgba(255,255,255,0.6); margin-bottom:16px;">Help the tourism office and fellow
            travellers by sharing your site testimony and policy recommendations.</p>

        <form id="testimony-form" onsubmit="window.submitTestimony(event)">
            <input type="hidden" id="testimony-spot-id">

            <!-- Star Rating selection -->
            <div style="margin-bottom:14px;">
                <label
                    style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Your
                    Rating (1 to 5 Stars):</label>
                <div style="display:flex; gap:8px; font-size:24px; color:#f59e0b;">
                    <i class="fa-solid fa-star star-btn" data-star="1" style="cursor:pointer;"
                        onclick="window.setStarRating(1)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="2" style="cursor:pointer;"
                        onclick="window.setStarRating(2)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="3" style="cursor:pointer;"
                        onclick="window.setStarRating(3)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="4" style="cursor:pointer;"
                        onclick="window.setStarRating(4)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="5" style="cursor:pointer;"
                        onclick="window.setStarRating(5)"></i>
                </div>
                <input type="hidden" id="testimony-rating" value="5">
            </div>

            <!-- Cleanliness, Safety parameters -->
            <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">

                <!-- Cleanliness -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label
                            style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase;">Cleanliness:</label>
                        <span id="cleanliness-selected-label"
                            style="font-size:11px; font-weight:700; color:#10b981;">Clean</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill clean-pill active" data-val="clean"
                            onclick="window.selectCleanliness('clean')"
                            style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(16,185,129,0.22); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            ✨ Clean
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="moderate"
                            onclick="window.selectCleanliness('moderate')"
                            style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            🧹 Moderate
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="dirty"
                            onclick="window.selectCleanliness('dirty')"
                            style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            ⚠️ Dirty
                        </button>
                    </div>
                    <input type="hidden" id="testimony-cleanliness" value="clean">
                </div>

                <!-- Safety -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label
                            style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase;">Safety
                            Level:</label>
                        <span id="safety-selected-label"
                            style="font-size:11px; font-weight:700; color:#10b981;">Safe</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill safety-pill active" data-val="safe"
                            onclick="window.selectSafety('safe')"
                            style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(16,185,129,0.22); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            🛡️ Safe
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="moderate"
                            onclick="window.selectSafety('moderate')"
                            style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            ⚡ Moderate
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="unsafe"
                            onclick="window.selectSafety('unsafe')"
                            style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            🚨 Unsafe
                        </button>
                    </div>
                    <input type="hidden" id="testimony-safety" value="safe">
                </div>
            </div>

            <!-- Testimony description -->
            <div style="margin-bottom:14px;">
                <label
                    style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Your
                    Testimony:</label>
                <textarea id="testimony-comment" placeholder="Describe your experience during this site visit..."
                    style="width:100%; height:60px; background:rgba(255,255,255,0.05); border:none !important; outline:none !important; border-radius:12px; padding:10px; color:#fff; font-size:12px; font-family:inherit; resize:none; box-sizing:border-box;"
                    required></textarea>
            </div>

            <!-- Policy Recommendation -->
            <div style="margin-bottom:20px;">
                <label
                    style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Policy
                    Recommendations (Optional):</label>
                <textarea id="testimony-policy"
                    placeholder="Any suggestions or recommendations for safety, cleanliness, or crowd control policies?..."
                    style="width:100%; height:60px; background:rgba(255,255,255,0.05); border:none !important; outline:none !important; border-radius:12px; padding:10px; color:#fff; font-size:12px; font-family:inherit; resize:none; box-sizing:border-box;"></textarea>
            </div>

            <button type="submit" class="btn-primary"
                style="width:100%; padding:14px; font-size:14px; margin-bottom:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none !important; outline:none !important; color:#fff; border-radius:12px; font-weight:800; cursor:pointer;">
                Submit Feedback
            </button>
        </form>
        <button
            style="width:100%; padding:12px; border-radius:12px; border:none !important; outline:none !important; background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); font-size:13px; font-weight:600; cursor:pointer;"
            onclick="window.closeWriteTestimonyModal()">Cancel</button>
    </div>
</div>

<!-- Include Bottom Navigation Component -->




<script>
    (function () {
        // In an SPA context, this script is executed every time the view is injected.
        if (window.mapInstance) {
            try { window.mapInstance.remove(); } catch (e) { }
            window.mapInstance = null;
        }

        window.allMapLocations = window.allMapLocations || [];
        window.currentDestinationForRoute = null;
        window.userMarker = null;
        window.mapMarkers = [];

        window.initMap = async function () {
            const mapEl = document.getElementById('tourist-map');
            if (!mapEl) return;

            // Fetch data immediately (Cache + SWR in parallel with MapLibre initialization)
            const _backendBase = window.backendUrl || '';
            const mapCacheKey = 'public_map_data';
            let cachedMapData = null;
            try {
                const cachedRaw = localStorage.getItem(mapCacheKey);
                if (cachedRaw) {
                    const parsed = window.safeJsonParse(cachedRaw, null);
                    if (parsed && parsed.data && parsed.data.destinations && parsed.data.destinations.length > 0) {
                        cachedMapData = parsed.data;
                    }
                }
            } catch (e) {}

            const mapDataPromise = (async () => {
                if (cachedMapData && cachedMapData.destinations && cachedMapData.destinations.length > 0) {
                    setTimeout(() => {
                        fetch(_backendBase + '/api/public/map', { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(fresh => {
                                if (fresh && fresh.destinations) {
                                    try { localStorage.setItem(mapCacheKey, JSON.stringify({ data: fresh, timestamp: Date.now() })); } catch (e) {}
                                    const oldIds = (cachedMapData.destinations || []).map(d => d.id).sort().join(',');
                                    const freshIds = (fresh.destinations || []).map(d => d.id).sort().join(',');
                                    if (oldIds !== freshIds) {
                                        window.allMapLocations = fresh.destinations;
                                        if (typeof window.updateVisibleMarkers === 'function') window.updateVisibleMarkers();
                                    }
                                }
                            }).catch(() => {});
                    }, 800);
                    return cachedMapData;
                }
                const res = await fetch(_backendBase + '/api/public/map', { headers: { 'Accept': 'application/json' } });
                const fresh = await res.json();
                if (fresh && fresh.destinations) {
                    try { localStorage.setItem(mapCacheKey, JSON.stringify({ data: fresh, timestamp: Date.now() })); } catch (e) {}
                }
                return fresh;
            })();

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

            // Smoothly hide amenity markers when zoomed out away from tourist site (threshold 13.0)
            let isAmenityZoomedOut = false;
            let _amenityZoomRaf = null;
            const updateAmenityZoomState = () => {
                if (_amenityZoomRaf) return;
                _amenityZoomRaf = requestAnimationFrame(() => {
                    _amenityZoomRaf = null;
                    if (!window.mapInstance) return;
                    const mapEl = document.getElementById('tourist-map');
                    if (!mapEl) return;
                    const shouldHide = window.mapInstance.getZoom() < 13.0;
                    if (shouldHide !== isAmenityZoomedOut) {
                        isAmenityZoomedOut = shouldHide;
                        if (shouldHide) {
                            mapEl.classList.add('map-zoomed-out');
                        } else {
                            mapEl.classList.remove('map-zoomed-out');
                        }
                    }
                });
            };
            window.mapInstance.on('zoom', updateAmenityZoomState);

            // Collapse any expanded amenity markers when user clicks background map
            window.mapInstance.on('click', (e) => {
                if (e && e.originalEvent && e.originalEvent.target && e.originalEvent.target.closest('.elyu-amenity-marker')) {
                    return;
                }
                document.querySelectorAll('.elyu-amenity-marker.is-expanded').forEach(el => {
                    el.classList.remove('is-expanded');
                });
            });

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
                                const fText = filterCat.trim().toLowerCase();
                                pills.forEach(p => {
                                    const pText = p.textContent.trim().toLowerCase();
                                    if (pText === fText || pText.includes(fText) || fText.includes(pText)) {
                                        if (!matchedPill) matchedPill = p;
                                    }
                                });
                                const targetCategory = matchedPill ? matchedPill.textContent.trim() : filterCat;
                                window.filterCategory(targetCategory, matchedPill);
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

                        const worldBox = [[180, 90], [-180, 90], [-180, -90], [180, -90], [180, 90]];
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
                } catch (e) { console.error("Failed to slice region:", e); }

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
                            popupEl.style.cssText = 'background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border-radius:14px; padding:14px 16px; min-width:180px; border:none !important; outline:none !important; box-shadow:0 12px 30px rgba(10,25,60,0.5);';
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
                        if (window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
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
                        }

                        window.zonesLoaded = true;
                    }
                } catch (zoneErr) { console.error('Zone render error:', zoneErr); }
                // ── END TOURIST ZONES ────────────────────────────────────────────

            });

            setupEventListeners();
            window.mapInstance.on('moveend', () => {
                if (typeof window.updateVisibleMarkers === 'function') {
                    window.updateVisibleMarkers();
                }
            });
        };

        window.currentFilteredLocations = [];
        window.mapMarkers = [];
        let _viewportUpdateRaf = null;

        window.renderMarkers = function (locations) {
            window.currentFilteredLocations = locations || [];
            window.updateVisibleMarkers();
        };

        window.mountedMarkersMap = window.mountedMarkersMap || new Map();

        window.updateVisibleMarkers = function () {
            if (!window.mapInstance) return;
            if (_viewportUpdateRaf) cancelAnimationFrame(_viewportUpdateRaf);

            _viewportUpdateRaf = requestAnimationFrame(() => {
                const locations = window.currentFilteredLocations || [];
                if (!locations.length) {
                    if (window.mountedMarkersMap) {
                        window.mountedMarkersMap.forEach(m => m.remove());
                        window.mountedMarkersMap.clear();
                    }
                    window.mapMarkers = [];
                    return;
                }

                const bounds = window.mapInstance.getBounds();

                // 1. Viewport Culling with 25% spatial padding
                const lngBuffer = (bounds.getEast() - bounds.getWest()) * 0.25;
                const latBuffer = (bounds.getNorth() - bounds.getSouth()) * 0.25;
                const minLng = bounds.getWest() - lngBuffer;
                const maxLng = bounds.getEast() + lngBuffer;
                const minLat = bounds.getSouth() - latBuffer;
                const maxLat = bounds.getNorth() + latBuffer;

                const desiredKeys = new Set();

                // ── DIRECT INDIVIDUAL TOURIST SITES MARKERS (ALL ZOOM LEVELS) ──
                    for (let i = 0; i < locations.length; i++) {
                        const loc = locations[i];
                        const locLat = parseFloat(loc.lat || loc.latitude);
                        const locLng = parseFloat(loc.lng || loc.longitude);
                        if (isNaN(locLat) || isNaN(locLng)) continue;

                        if (locLng >= minLng && locLng <= maxLng && locLat >= minLat && locLat <= maxLat) {
                            const markerKey = 'spot_' + (loc.id || (locLat + '_' + locLng));
                            desiredKeys.add(markerKey);

                            if (!window.mountedMarkersMap.has(markerKey)) {
                                const cat = (loc.category || 'Other').toLowerCase();
                                let iconClass = 'fa-location-dot';

                                if (cat.includes('beach') || cat.includes('surf') || cat.includes('coastal') || cat.includes('island')) {
                                    iconClass = 'fa-umbrella-beach';
                                } else if (cat.includes('nature') || cat.includes('park') || cat.includes('agro-forestry') || cat.includes('tree') || cat.includes('mangrove') || cat.includes('lagoon')) {
                                    iconClass = 'fa-tree';
                                } else if (cat.includes('water') || cat.includes('fall') || cat.includes('river') || cat.includes('lake') || cat.includes('spring') || cat.includes('dam')) {
                                    iconClass = 'fa-water';
                                } else if (cat.includes('mountain') || cat.includes('hiking') || cat.includes('trail') || cat.includes('peak') || cat.includes('view')) {
                                    iconClass = 'fa-mountain';
                                } else if (cat.includes('cultural') || cat.includes('heritage') || cat.includes('historical') || cat.includes('museum')) {
                                    iconClass = 'fa-landmark';
                                } else if (cat.includes('monument')) {
                                    iconClass = 'fa-monument';
                                } else if (cat.includes('landmark')) {
                                    iconClass = 'fa-archway';
                                } else if (cat.includes('religio') || cat.includes('church') || cat.includes('shrine') || cat.includes('parish')) {
                                    iconClass = 'fa-place-of-worship';
                                } else if (cat.includes('food') || cat.includes('dining') || cat.includes('restaurant') || cat.includes('cafe')) {
                                    iconClass = 'fa-utensils';
                                } else if (cat.includes('art') || cat.includes('craft') || cat.includes('weaving') || cat.includes('pottery')) {
                                    iconClass = 'fa-palette';
                                } else if (cat.includes('farm') || cat.includes('agro') || cat.includes('plant')) {
                                    iconClass = 'fa-tractor';
                                } else if (cat.includes('cave')) {
                                    iconClass = 'fa-dungeon';
                                } else if (cat.includes('recreation') || cat.includes('resort')) {
                                    iconClass = 'fa-person-swimming';
                                }

                                const status = (loc.classification_status || 'EXIST').toUpperCase().trim();
                                let catColor = '#34c759';
                                let statusLabel = 'Existing';
                                if (status === 'EMERGE' || status === 'EMERGING') {
                                    catColor = '#38bdf8';
                                    statusLabel = 'Emerging';
                                } else if (status === 'POTENTIAL') {
                                    catColor = '#f59e0b';
                                    statusLabel = 'Potential';
                                }

                                const container = document.createElement('div');
                                container.className = 'elyu-custom-marker';
                                container.style.cssText = 'cursor:pointer; display:flex; flex-direction:column; align-items:center; user-select:none; will-change:transform; transform:translate3d(0,0,0); backface-visibility:hidden; z-index:10;';

                                const innerWrap = document.createElement('div');
                                innerWrap.className = 'spot-inner-wrapper';
                                const staggerDelay = Math.min((i % 15) * 0.025, 0.35);
                                innerWrap.style.animationDelay = `${staggerDelay}s`;

                                const pin = document.createElement('div');
                                pin.className = 'elyu-pin-bubble';
                                pin.style.cssText = `width:34px; height:34px; border-radius:50%; background:#ffffff; border:2.5px solid ${catColor}; display:flex; align-items:center; justify-content:center; color:${catColor}; box-shadow:0 4px 10px rgba(0,0,0,0.18), 0 1px 3px rgba(0,0,0,0.12);`;
                                pin.innerHTML = `<i class="fa-solid ${iconClass}" style="font-size:13.5px; color:${catColor};"></i>`;

                                innerWrap.appendChild(pin);
                                container.appendChild(innerWrap);

                                container.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    if (window.activePopup) window.activePopup.remove();
                                    window.openSheet(loc);
                                });

                                container.addEventListener('mouseenter', () => {
                                    pin.style.transform = 'scale(1.2)';
                                    container.style.zIndex = '100';
                                });
                                container.addEventListener('mouseleave', () => {
                                    pin.style.transform = 'scale(1)';
                                    container.style.zIndex = '10';
                                });

                                const marker = new maplibregl.Marker({ element: container, anchor: 'center' })
                                    .setLngLat([locLng, locLat])
                                    .addTo(window.mapInstance);

                                window.mountedMarkersMap.set(markerKey, marker);
                            }
                        }
                    }

                // ── RECONCILE: REMOVE MARKERS NO LONGER DESIRED ──
                for (const [key, marker] of window.mountedMarkersMap.entries()) {
                    if (!desiredKeys.has(key)) {
                        marker.remove();
                        window.mountedMarkersMap.delete(key);
                    }
                }
                window.mapMarkers = Array.from(window.mountedMarkersMap.values());
            });
        };

        // ── NEARBY AMENITIES LOGIC (ATMs, convenience stores, pharmacies, gas stations, etc.) ──
        // Per user requirements: Amenities are strictly non-clickable (pointer-events: none)
        window.activeAmenityMarkers = [];
        window.currentAmenitySpotId = null;
        window._sheetAmenitiesExpanded = false;

        window.formatAmenityDistance = function (meters) {
            if (meters == null || isNaN(meters)) return '--';
            const m = Math.round(Number(meters));
            if (m < 1000) {
                return `${m}m`;
            }
            return `${(m / 1000).toFixed(1)}km`;
        };

        window.toggleSheetAmenities = function () {
            const listEl = document.getElementById('sheet-amenities-list');
            const toggleText = document.getElementById('sheet-amenities-toggle-text');
            const toggleChevron = document.getElementById('sheet-amenities-chevron');
            if (!listEl) return;

            window._sheetAmenitiesExpanded = !window._sheetAmenitiesExpanded;
            const hiddenRows = listEl.querySelectorAll('[data-amenity-hidden="true"]');
            hiddenRows.forEach(r => {
                r.style.display = window._sheetAmenitiesExpanded ? 'flex' : 'none';
            });

            const total = listEl.children.length;
            if (window._sheetAmenitiesExpanded) {
                if (toggleText) toggleText.textContent = 'Show Less';
                if (toggleChevron) toggleChevron.style.transform = 'rotate(180deg)';
            } else {
                if (toggleText) toggleText.textContent = `Show All (${total} Nearby)`;
                if (toggleChevron) toggleChevron.style.transform = 'rotate(0deg)';
            }
        };

        window.clearAmenityMarkers = function () {
            if (window.activeAmenityMarkers && window.activeAmenityMarkers.length > 0) {
                window.activeAmenityMarkers.forEach(m => {
                    try { m.remove(); } catch (e) {}
                });
                window.activeAmenityMarkers = [];
            }
            window.currentAmenitySpotId = null;
            window._sheetAmenitiesExpanded = false;
            document.querySelectorAll('.elyu-amenity-marker.is-expanded').forEach(el => {
                el.classList.remove('is-expanded');
            });
            const noticeEl = document.getElementById('sheet-amenities-notice');
            if (noticeEl) noticeEl.style.display = 'none';
            const listEl = document.getElementById('sheet-amenities-list');
            if (listEl) listEl.innerHTML = '';
            const toggleBtn = document.getElementById('sheet-amenities-toggle-btn');
            if (toggleBtn) toggleBtn.style.display = 'none';
        };

        window.loadNearbyAmenities = async function (spot) {
            if (!spot || !window.mapInstance) return;

            const lat = parseFloat(spot.lat || spot.latitude);
            const lng = parseFloat(spot.lng || spot.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            const spotIdentifier = String(spot.id || `${lat}_${lng}`);
            if (window.currentAmenitySpotId === spotIdentifier && window.activeAmenityMarkers.length > 0) {
                return; // Already active for this spot
            }

            window.clearAmenityMarkers();
            window.currentAmenitySpotId = spotIdentifier;

            const _backendBase = window.backendUrl || '';

            try {
                // Proximity query: radius 800m & up to 35 closest amenities in this site
                const res = await fetch(`${_backendBase}/api/public/amenities?lat=${lat}&lng=${lng}&radius=800&limit=35`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                let rawAmenities = (data && data.amenities) ? data.amenities : [];

                // Filter for high accuracy & strict proximity:
                const genericNames = new Set([
                    'facility', 'atm', 'bank', 'convenience store', 'convenience', 'supermarket',
                    'supermarket / store', 'store', 'pharmacy', 'gas station', 'fuel',
                    'hospital', 'clinic', 'health clinic', 'police station', 'police',
                    'public toilet', 'toilets', 'parking', 'restaurant', 'cafe', 'fast food',
                    'hotel', 'motel', 'resort', 'church', 'chapel', 'park', 'vulcanizing', 'car repair'
                ]);
                const informalRegex = /(^app store$)/i;

                let amenities = rawAmenities.filter(am => {
                    const dist = Number(am.distance_meters);
                    const name = String(am.name || '').trim();
                    const lower = name.toLowerCase();
                    if (isNaN(dist) || dist > 800 || name.length < 3 || genericNames.has(lower) || lower.startsWith('unnamed')) {
                        return false;
                    }
                    if (informalRegex.test(name)) {
                        return false;
                    }
                    return true;
                }).slice(0, 35);

                // Sort by distance ascending (closest first)
                amenities.sort((a, b) => (Number(a.distance_meters) || 0) - (Number(b.distance_meters) || 0));

                // Check if user moved away or closed the sheet while fetching
                if (window.currentAmenitySpotId !== spotIdentifier) return;

                // If no amenities are close, keep all amenities hidden!
                if (amenities.length === 0) {
                    window.clearAmenityMarkers();
                    return;
                }

                // Map markers: Mount up to 15 closest amenities for ultra-smooth 60fps performance
                const mapAmenities = amenities.slice(0, 15);

                // Unified color for all amenities (ring border & icon)
                const AMENITY_COLOR = '#0284c7';

                mapAmenities.forEach((am, idx) => {
                    const amLat = parseFloat(am.lat);
                    const amLng = parseFloat(am.lng);
                    if (isNaN(amLat) || isNaN(amLng)) return;

                    const container = document.createElement('div');
                    container.className = 'elyu-amenity-marker';
                    container.style.cssText = `position:absolute !important; top:0 !important; left:0 !important; pointer-events:none; user-select:none; z-index:${20 + idx}; transition:none !important;`;
                    container.setAttribute('aria-hidden', 'true');

                    const inner = document.createElement('div');
                    inner.className = 'amenity-marker-inner';
                    inner.style.cssText = 'display:flex; flex-direction:column; align-items:center; pointer-events:none;';

                    const bubble = document.createElement('div');
                    bubble.className = 'amenity-marker-bubble';
                    const distStr = window.formatAmenityDistance(am.distance_meters);
                    bubble.title = `${am.name || am.label || 'Amenity'} (${distStr})`;

                    const iconCircle = document.createElement('div');
                    iconCircle.className = 'amenity-marker-icon-circle';
                    iconCircle.style.cssText = `background-color:#ffffff !important; border:1.5px solid ${AMENITY_COLOR} !important; color:${AMENITY_COLOR} !important;`;
                    iconCircle.innerHTML = `<i class="${am.icon || 'fa-solid fa-location-dot'}" style="color:${AMENITY_COLOR} !important;"></i>`;

                    const label = document.createElement('span');
                    label.className = 'amenity-marker-label';
                    label.textContent = `${am.name || am.label || 'Amenity'} • ${distStr}`;

                    bubble.appendChild(iconCircle);
                    bubble.appendChild(label);
                    inner.appendChild(bubble);

                    const tip = document.createElement('div');
                    tip.className = 'amenity-marker-tip';
                    inner.appendChild(tip);

                    container.appendChild(inner);

                    // Click / tap interaction: click expands to show name and distance, click again collapses
                    bubble.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const isCurrentlyExpanded = container.classList.contains('is-expanded');
                        document.querySelectorAll('.elyu-amenity-marker.is-expanded').forEach(el => {
                            if (el !== container) el.classList.remove('is-expanded');
                        });
                        if (isCurrentlyExpanded) {
                            container.classList.remove('is-expanded');
                        } else {
                            container.classList.add('is-expanded');
                        }
                    });

                    const marker = new maplibregl.Marker({
                        element: container,
                        anchor: 'bottom'
                    })
                        .setLngLat([amLng, amLat])
                        .addTo(window.mapInstance);

                    window.activeAmenityMarkers.push(marker);
                    am._markerContainer = container;
                });

                // Update tourist site details sheet with all amenities and individual distances
                const noticeEl = document.getElementById('sheet-amenities-notice');
                const countBadge = document.getElementById('sheet-amenities-count-badge');
                const listEl = document.getElementById('sheet-amenities-list');
                const toggleBtn = document.getElementById('sheet-amenities-toggle-btn');
                const toggleText = document.getElementById('sheet-amenities-toggle-text');
                const toggleChevron = document.getElementById('sheet-amenities-chevron');

                if (noticeEl && amenities.length > 0) {
                    noticeEl.style.display = 'flex';
                    if (countBadge) countBadge.textContent = `${amenities.length} Nearby`;

                    if (listEl) {
                        listEl.innerHTML = '';
                        const fragment = document.createDocumentFragment();

                        amenities.forEach((am, idx) => {
                            const row = document.createElement('div');
                            row.className = 'sheet-amenity-row';
                            if (idx >= 4) {
                                row.style.display = 'none';
                                row.setAttribute('data-amenity-hidden', 'true');
                            }

                            const distFormatted = window.formatAmenityDistance(am.distance_meters);
                            const safeName = (am.name || am.label || 'Amenity').replace(/"/g, '&quot;');
                            const safeLabel = (am.label || am.type || 'Amenity').replace(/"/g, '&quot;');
                            const safeIcon = am.icon || 'fa-solid fa-location-dot';

                            row.innerHTML = `
                                <div class="sheet-amenity-left">
                                    <div class="sheet-amenity-icon-circle" style="background-color:#ffffff !important; border:1.5px solid ${AMENITY_COLOR} !important; color:${AMENITY_COLOR} !important;">
                                        <i class="${safeIcon}" style="color:${AMENITY_COLOR} !important;"></i>
                                    </div>
                                    <div class="sheet-amenity-info">
                                        <span class="sheet-amenity-name" title="${safeName}">${safeName}</span>
                                        <span class="sheet-amenity-type">${safeLabel}</span>
                                    </div>
                                </div>
                                <div class="sheet-amenity-distance-badge">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>${distFormatted}</span>
                                </div>
                            `;

                            // Tapping an amenity in the sheet focuses and expands it on the map
                            row.addEventListener('click', () => {
                                const amLat = parseFloat(am.lat);
                                const amLng = parseFloat(am.lng);
                                if (window.mapInstance && !isNaN(amLat) && !isNaN(amLng)) {
                                    window.mapInstance.flyTo({
                                        center: [amLng, amLat],
                                        zoom: 16.5,
                                        duration: 700,
                                        essential: true
                                    });
                                }

                                // If marker is not yet mounted on map (beyond top 15), mount it on-demand!
                                if (!am._markerContainer && window.mapInstance && !isNaN(amLat) && !isNaN(amLng)) {
                                    const c = document.createElement('div');
                                    c.className = 'elyu-amenity-marker is-expanded';
                                    c.style.cssText = 'position:absolute !important; top:0 !important; left:0 !important; pointer-events:none; user-select:none; z-index:999; transition:none !important;';
                                    c.innerHTML = `
                                        <div class="amenity-marker-inner" style="display:flex; flex-direction:column; align-items:center; pointer-events:none;">
                                            <div class="amenity-marker-bubble" style="pointer-events:auto;">
                                                <div class="amenity-marker-icon-circle" style="background-color:#ffffff !important; border:1.5px solid ${AMENITY_COLOR} !important; color:${AMENITY_COLOR} !important;">
                                                    <i class="${safeIcon}" style="color:${AMENITY_COLOR} !important;"></i>
                                                </div>
                                                <span class="amenity-marker-label">${safeName} • ${distFormatted}</span>
                                            </div>
                                            <div class="amenity-marker-tip"></div>
                                        </div>
                                    `;
                                    const m = new maplibregl.Marker({ element: c, anchor: 'bottom' })
                                        .setLngLat([amLng, amLat])
                                        .addTo(window.mapInstance);
                                    window.activeAmenityMarkers.push(m);
                                    am._markerContainer = c;
                                }

                                document.querySelectorAll('.elyu-amenity-marker.is-expanded').forEach(el => el.classList.remove('is-expanded'));
                                if (am._markerContainer) {
                                    am._markerContainer.classList.add('is-expanded');
                                }
                            });

                            fragment.appendChild(row);
                        });

                        listEl.appendChild(fragment);

                        if (toggleBtn) {
                            if (amenities.length > 4) {
                                toggleBtn.style.display = 'flex';
                                if (toggleText) toggleText.textContent = `Show All (${amenities.length} Nearby)`;
                                if (toggleChevron) toggleChevron.style.transform = 'rotate(0deg)';
                                window._sheetAmenitiesExpanded = false;
                            } else {
                                toggleBtn.style.display = 'none';
                            }
                        }
                    }
                }
            } catch (err) {
                console.warn('Error loading nearby amenities:', err);
            }
        };

        function matchesCategoryFilter(loc, targetCat) {
            if (!targetCat || targetCat === 'All') return true;
            const t = targetCat.toLowerCase().trim();
            const c = (loc.category || '').toLowerCase().trim();
            const n = (loc.name || '').toLowerCase().trim();
            const d = (loc.description || '').toLowerCase().trim();
            const m = (loc.municipality || '').toLowerCase().trim();
            const combined = `${c} ${n} ${d} ${m}`;

            // 1. Direct match or inclusion
            if (c === t || c.includes(t) || t.includes(c)) return true;
            if (` ${c} `.includes(` ${t} `)) return true;

            // 2. Beach, Coastal & Surfing
            if (t.includes('beach') || t.includes('surf') || t.includes('coastal') || t.includes('island')) {
                return combined.includes('beach') || combined.includes('surf') || combined.includes('coastal') || 
                       combined.includes('island') || combined.includes('seascape') || combined.includes('water sports');
            }

            // 3. Nature, Eco-Parks & Town Plazas
            if (t.includes('nature') || t.includes('park')) {
                return c.includes('nature') || c.includes('park') || n.includes('park') || 
                       n.includes('plaza') || c.includes('agro-forestry') || c.includes('tree') ||
                       n.includes('mangrove') || n.includes('lagoon') || n.includes('baywalk');
            }

            // 4. Waterfalls, Rivers, Lakes & Springs
            if (t.includes('water') || t.includes('fall') || t.includes('lake') || t.includes('river')) {
                return c.includes('waterfall') || c.includes('river') || c.includes('lake') || 
                       n.includes('fall') || n.includes('river') || n.includes('lake') || n.includes('dam') || n.includes('spring');
            }

            // 5. Mountains, Hiking & View Decks
            if (t.includes('mountain') || t.includes('hiking') || t.includes('trail') || t.includes('view')) {
                return c.includes('mountain') || c.includes('hiking') || n.includes('trail') || 
                       n.includes('peak') || n.includes('view deck') || n.includes('viewdeck') || n.includes('terrace') || n.includes('mt.') || n.includes('mountain');
            }

            // 6. Cultural Heritage, Historical, Monuments & Museums
            if (t.includes('cultural') || t.includes('heritage') || t.includes('historical') || t.includes('monument') || t.includes('museum')) {
                return c.includes('cultural') || c.includes('heritage') || c.includes('historical') || 
                       c.includes('monument') || c.includes('museum') || n.includes('watchtower') || 
                       n.includes('tunnel') || n.includes('marker') || n.includes('station') || 
                       n.includes('memorial') || n.includes('ancestral') || n.includes('museum');
            }

            // 7. Churches & Religious
            if (t.includes('religious') || t.includes('church') || t.includes('shrine')) {
                return c.includes('religious') || n.includes('church') || n.includes('parish') || 
                       n.includes('basilica') || n.includes('shrine') || n.includes('grotto') || n.includes('chapel');
            }

            // 8. Landmarks
            if (t.includes('landmark')) {
                return c.includes('landmark') || n.includes('arc') || n.includes('center') || 
                       n.includes('bridge') || n.includes('tree house') || n.includes('port') || 
                       n.includes('srdi') || n.includes('building') || n.includes('institute');
            }

            // 9. Food & Dining
            if (t.includes('food') || t.includes('dining') || t.includes('restaurant')) {
                return c.includes('food') || combined.includes('restaurant') || combined.includes('seafood') || 
                       combined.includes('dining') || combined.includes('eatery') || combined.includes('cafe') || 
                       combined.includes('bistro') || combined.includes('grill');
            }

            // 10. Arts, Crafts & Weaving
            if (t.includes('art') || t.includes('craft') || t.includes('weaving')) {
                return c.includes('arts') || combined.includes('weaving') || combined.includes('pottery') || 
                       combined.includes('gallery') || combined.includes('craft') || combined.includes('paper');
            }

            // 11. Farms & Agriculture
            if (t.includes('farm') || t.includes('agro') || t.includes('plant')) {
                return c.includes('farm') || combined.includes('plantation') || combined.includes('grapes') || 
                       combined.includes('mushroom') || combined.includes('fishery') || combined.includes('agri');
            }

            // 12. Recreation & Resorts
            if (t.includes('recreation') || t.includes('resort')) {
                return c.includes('recreation') || combined.includes('resort') || combined.includes('eco-park');
            }

            // 13. Cave
            if (t.includes('cave')) {
                return c.includes('cave') || n.includes('cave');
            }

            return false;
        }

        function setupFilters() {
            const container = document.getElementById('map-categories-container');
            if (!container) return;

            const primaryOrder = [
                'All',
                'Beach',
                'Nature Park',
                'Park',
                'Waterfalls',
                'Mountain',
                'Landmark',
                'Monument',
                'Cultural Heritage',
                'Religious',
                'Food Destination',
                'Arts & craft',
                'Farm',
                'Hiking',
                'Lake',
                'River'
            ];

            const otherCats = [];
            (window.allMapLocations || []).forEach(loc => {
                if (!loc.category) return;
                const parts = String(loc.category).split(/[,/]/);
                parts.forEach(p => {
                    const trimmed = p.trim();
                    if (trimmed && !primaryOrder.includes(trimmed) && !otherCats.includes(trimmed)) {
                        otherCats.push(trimmed);
                    }
                });
            });
            otherCats.sort((a, b) => a.localeCompare(b));

            const finalCats = [...primaryOrder, ...otherCats];
            let html = '';
            finalCats.forEach((cat, idx) => {
                const safeCat = cat.replace(/'/g, "\\'");
                const activeClass = idx === 0 ? 'active' : '';
                html += `<div class="category-pill ${activeClass}" onclick="filterCategory('${safeCat}', this)">${cat}</div>`;
            });
            container.innerHTML = html;
        }

        window.activeClassificationFilter = null;

        window.toggleClassificationMenu = function (forceState) {
            const popover = document.getElementById('classification-popover');
            const chevron = document.getElementById('classification-chevron');
            if (!popover) return;

            const isCurrentlyOpen = popover.style.display === 'block';
            const nextState = typeof forceState === 'boolean' ? forceState : !isCurrentlyOpen;

            if (nextState) {
                // Update dynamic counts from currently loaded locations
                const allLocs = window.allMapLocations || [];
                let cExist = 0, cEmerge = 0, cPot = 0;
                allLocs.forEach(loc => {
                    const s = (loc.classification_status || 'EXIST').toUpperCase().trim();
                    if (s === 'EMERGE' || s === 'EMERGING') cEmerge++;
                    else if (s === 'POTENTIAL') cPot++;
                    else cExist++;
                });
                const countExist = document.getElementById('count-exist');
                const countEmerge = document.getElementById('count-emerge');
                const countPot = document.getElementById('count-potential');
                if (countExist) countExist.textContent = `${cExist} Sites`;
                if (countEmerge) countEmerge.textContent = `${cEmerge} Sites`;
                if (countPot) countPot.textContent = `${cPot} Sites`;

                popover.style.display = 'block';
                requestAnimationFrame(() => {
                    popover.style.opacity = '1';
                    popover.style.transform = 'scale(1)';
                    popover.style.pointerEvents = 'auto';
                });
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            } else {
                popover.style.opacity = '0';
                popover.style.transform = 'scale(0.95)';
                popover.style.pointerEvents = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
                setTimeout(() => {
                    if (popover.style.opacity === '0') popover.style.display = 'none';
                }, 200);
            }
        };

        window.filterByClassification = function (statusKey) {
            window.activeClassificationFilter = (statusKey === 'ALL') ? null : statusKey;

            const dotExist = document.getElementById('dot-exist');
            const dotEmerge = document.getElementById('dot-emerge');
            const dotPot = document.getElementById('dot-potential');

            if (dotExist && dotEmerge && dotPot) {
                dotExist.style.transform = 'scale(1)';
                dotExist.style.opacity = '1';
                dotEmerge.style.transform = 'scale(1)';
                dotEmerge.style.opacity = '1';
                dotPot.style.transform = 'scale(1)';
                dotPot.style.opacity = '1';

                if (statusKey === 'EXIST') {
                    dotExist.style.transform = 'scale(1.4)';
                    dotEmerge.style.opacity = '0.35';
                    dotPot.style.opacity = '0.35';
                } else if (statusKey === 'EMERGE') {
                    dotEmerge.style.transform = 'scale(1.4)';
                    dotExist.style.opacity = '0.35';
                    dotPot.style.opacity = '0.35';
                } else if (statusKey === 'POTENTIAL') {
                    dotPot.style.transform = 'scale(1.4)';
                    dotExist.style.opacity = '0.35';
                    dotEmerge.style.opacity = '0.35';
                }
            }

            window.toggleClassificationMenu(false);

            // Re-run filterCategory with the active category
            const activeCatEl = document.querySelector('.category-pill.active');
            window.filterCategory(window.currentActiveCategory || 'All', activeCatEl);
        };

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.btn-classification-wrapper')) {
                window.toggleClassificationMenu(false);
            }
        });

        window.filterCategory = function (category, element) {
            window.currentActiveCategory = category;
            document.querySelectorAll('.category-pill').forEach(pill => pill.classList.remove('active'));
            if (element) element.classList.add('active');

            const searchInput = document.getElementById('map-search-input');
            const searchText = searchInput ? searchInput.value.toLowerCase().trim() : '';

            const filtered = (window.allMapLocations || []).filter(loc => {
                const name = loc.name ? loc.name.toLowerCase() : '';
                const location = loc.location ? loc.location.toLowerCase() : '';
                const locCat = loc.category ? loc.category.toLowerCase() : '';
                const matchesSearch = !searchText || (name.includes(searchText) || location.includes(searchText) || locCat.includes(searchText));
                const matchesCat = matchesCategoryFilter(loc, category);

                let matchesStatus = true;
                if (window.activeClassificationFilter) {
                    const s = (loc.classification_status || 'EXIST').toUpperCase().trim();
                    if (window.activeClassificationFilter === 'EMERGE') {
                        matchesStatus = (s === 'EMERGE' || s === 'EMERGING');
                    } else {
                        matchesStatus = (s === window.activeClassificationFilter);
                    }
                }

                return matchesSearch && matchesCat && matchesStatus;
            });

            window.renderMarkers(filtered);

            // Update badge on tourist sites list button
            const nearbyBadge = document.getElementById('nearby-sites-badge');
            if (nearbyBadge) {
                if (filtered.length > 0 && category !== 'All') {
                    nearbyBadge.style.display = 'flex';
                    nearbyBadge.textContent = filtered.length;
                } else if (category === 'All') {
                    nearbyBadge.style.display = 'none';
                }
            }

            const validFiltered = filtered.filter(loc => loc.lat && loc.lng && !isNaN(parseFloat(loc.lat)) && !isNaN(parseFloat(loc.lng)));
            if (validFiltered.length > 0 && window.mapInstance) {
                if (validFiltered.length === 1) {
                    // Single destination: fly smoothly right to it with high zoom
                    window.mapInstance.flyTo({
                        center: [parseFloat(validFiltered[0].lng), parseFloat(validFiltered[0].lat)],
                        zoom: 14.5,
                        offset: [0, -30],
                        duration: 800
                    });
                } else {
                    const bounds = new maplibregl.LngLatBounds();
                    validFiltered.forEach(loc => bounds.extend([parseFloat(loc.lng), parseFloat(loc.lat)]));

                    // UI padding: top 180px, bottom 140px, left/right 45px
                    window.mapInstance.fitBounds(bounds, {
                        padding: { top: 180, bottom: 140, left: 45, right: 45 },
                        maxZoom: 14,
                        duration: 800
                    });
                }
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
                searchInput.blur(); // Dismiss mobile soft keyboard
                const activeCatEl = document.querySelector('.category-pill.active');
                window.filterCategory('All', document.querySelector('.category-pill'));
                const lat = parseFloat(loc.lat);
                const lng = parseFloat(loc.lng);
                if (!isNaN(lat) && !isNaN(lng) && window.mapInstance) {
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

                // Focus — show suggestions and auto-hide navigation bar
                searchInput.addEventListener('focus', () => {
                    document.body.classList.add('keyboard-open');
                    const bNav = document.getElementById('bottom-navigation');
                    const mNav = document.getElementById('magic-nav');
                    if (bNav) bNav.classList.add('keyboard-hidden');
                    if (mNav) mNav.classList.add('keyboard-hidden');
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

                // Blur — hide suggestions with smooth transition and restore nav bar
                searchInput.addEventListener('blur', () => {
                    setTimeout(() => {
                        if (suggestionsEl) suggestionsEl.classList.remove('open');
                        const active = document.activeElement;
                        if (!active || (active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA')) {
                            document.body.classList.remove('keyboard-open');
                            const bNav = document.getElementById('bottom-navigation');
                            const mNav = document.getElementById('magic-nav');
                            if (bNav) bNav.classList.remove('keyboard-hidden');
                            if (mNav) mNav.classList.remove('keyboard-hidden');
                        }
                    }, 180);
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
                                window.userMarker = new maplibregl.Marker({ element: el }).setLngLat([lng, lat]).addTo(window.mapInstance);
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
            document.addEventListener('gpsUpdated', function (e) {
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
                        window.userMarker = new maplibregl.Marker({ element: el }).setLngLat([lng, lat]).addTo(window.mapInstance);
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

            window.toggleNearbySitesSheet = function () {
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

            window.openNearbySitesSheet = async function () {
                if (window.closeSheet) window.closeSheet();
                const sheet = document.getElementById('nearby-sites-sheet');
                const btn = document.getElementById('btn-nearby-sites');
                if (!sheet) return;

                if (btn) btn.classList.add('active');
                document.body.classList.add('sheet-open');

                sheet.style.display = 'block';
                sheet.style.transition = 'transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease';
                sheet.style.transform = 'translateY(calc(100% + 120px))';
                sheet.classList.remove('active');

                void sheet.offsetHeight; // force reflow for smooth slide-up animation

                sheet.classList.add('active');
                sheet.style.transform = 'translateY(0)';

                await window.renderNearbyTouristSites();
            };

            window.closeNearbySitesSheet = function () {
                document.body.classList.remove('sheet-open');
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

            window.filterNearbyRadius = function (radius, btn) {
                currentNearbyRadius = radius;
                document.querySelectorAll('.nearby-radius-btn').forEach(b => {
                    b.classList.remove('active');
                });
                if (btn) {
                    btn.classList.add('active');
                }
                window.renderNearbyTouristSites();
            };

            window.renderNearbyTouristSites = async function () {
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
                    } catch (e) {
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
                    } catch (e) { }
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

                let activeCatSpots = calculatedSpots;
                if (window.currentActiveCategory && window.currentActiveCategory !== 'All') {
                    activeCatSpots = calculatedSpots.filter(s => matchesCategoryFilter(s, window.currentActiveCategory));
                }

                let filtered = [];
                if (currentNearbyRadius === 'all') {
                    filtered = activeCatSpots.filter(s => s.distanceKm < 999999).slice(0, 30);
                } else {
                    const radiusNum = parseFloat(currentNearbyRadius);
                    filtered = activeCatSpots.filter(s => s.distanceKm <= radiusNum);
                }

                if (subtext) {
                    const catLabel = (window.currentActiveCategory && window.currentActiveCategory !== 'All') ? ` in ${window.currentActiveCategory}` : '';
                    if (filtered.length > 0) {
                        subtext.textContent = `Found ${filtered.length} attraction${filtered.length > 1 ? 's' : ''}${catLabel} ${currentNearbyRadius === 'all' ? 'closest to you' : 'within ' + currentNearbyRadius + ' km'}`;
                    } else {
                        subtext.textContent = `No spots found${catLabel} within ${currentNearbyRadius} km`;
                    }
                }

                if (filtered.length === 0) {
                    const catTitle = (window.currentActiveCategory && window.currentActiveCategory !== 'All') ? `No ${window.currentActiveCategory} Spots Within ${currentNearbyRadius} km` : `No Spots Within ${currentNearbyRadius} km`;
                    container.innerHTML = `
                    <div style="text-align:center; padding:26px 16px 22px; margin-bottom:24px; background:rgba(255,255,255,0.15); border:none !important; outline:none !important; border-radius:18px; animation: nearbyCardSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                        <div style="width:48px; height:48px; margin:0 auto 12px; border-radius:50%; background:rgba(255,255,255,0.22); border:none !important; outline:none !important; display:flex; align-items:center; justify-content:center; color:#ffffff; font-size:20px;">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div style="font-size:15px; font-weight:800; color:#ffffff; margin-bottom:6px;">${catTitle}</div>
                        <div style="font-size:12.5px; color:#ffffff; opacity:0.95; margin-bottom:18px; line-height:1.45; font-weight:500;">
                            Try expanding your search radius to discover attractions across La Union.
                        </div>
                        <button type="button" onclick="window.filterNearbyRadius('all', document.querySelector('[data-radius=\\'all\\']'))" style="padding:10px 24px; border-radius:100px; background:linear-gradient(135deg, #1e3a8a, #3f7db7); border:none !important; outline:none !important; color:#ffffff; font-size:12.5px; font-weight:800; cursor:pointer; transition:transform 0.2s ease;">
                            Show All in La Union
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
                    <div class="nearby-site-card" style="animation: nearbyCardSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) ${delay}s forwards; opacity: 0; background:rgba(255,255,255,0.14); border:none !important; outline:none !important; border-radius:18px; padding:10px 12px; display:flex; align-items:center; gap:12px;" onclick="window.selectNearbySite('${safeSpotStr}')">
                        <img src="${img}" alt="${spot.name}" loading="lazy" decoding="async" style="width:64px; height:64px; border-radius:12px; object-fit:cover; flex-shrink:0; transition: transform 0.3s ease;" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=150';">
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; margin-bottom:3px;">
                                <h4 style="margin:0; font-size:14px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${spot.name}</h4>
                            </div>
                            <div style="font-size:11.5px; color:#ffffff; opacity:0.92; margin-bottom:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <i class="fa-solid fa-location-dot" style="color:#ffffff; margin-right:3px;"></i>${spot.municipality || spot.location || 'La Union'}
                            </div>
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span style="font-size:10px; font-weight:800; color:#ffffff; background:rgba(255,255,255,0.22); border:none !important; outline:none !important; padding:2px 7px; border-radius:100px; display:inline-flex; align-items:center; gap:3px;">
                                    <i class="fa-solid fa-location-arrow" style="font-size:9px; color:#ffffff;"></i> ${distBadge}
                                </span>
                                <span style="font-size:10.5px; font-weight:700; color:#fbbf24; display:inline-flex; align-items:center; gap:3px;">
                                    <i class="fa-solid fa-star" style="font-size:9.5px;"></i> ${rating}
                                </span>
                                ${spot.category ? `<span style="font-size:10px; color:#ffffff; background:rgba(255,255,255,0.18); border:none !important; outline:none !important; padding:2px 6px; border-radius:6px; font-weight:600;">${spot.category}</span>` : ''}
                            </div>
                        </div>
                        <button type="button" class="nearby-site-action-btn" title="View on Map" style="background:rgba(255,255,255,0.22); border:none !important; outline:none !important; color:#ffffff; width:38px; height:38px; border-radius:12px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                            <i class="fa-solid fa-chevron-right" style="font-size:13px; color:#ffffff;"></i>
                        </button>
                    </div>
                `;
                });

                container.innerHTML = html;
            };

            window.selectNearbySite = function (encodedSpot) {
                try {
                    const spot = JSON.parse(decodeURIComponent(encodedSpot));
                    window.closeNearbySitesSheet();
                    const sLat = parseFloat(spot.lat || spot.latitude);
                    const sLng = parseFloat(spot.lng || spot.longitude);
                    if (!isNaN(sLat) && !isNaN(sLng) && window.mapInstance) {
                        if (window.openSheet) window.openSheet(spot);
                    }
                } catch (e) {
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
                    window.requestPreciseLocation(false).catch(() => { });
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

            window.toggleWeatherTracker = function (expand) {
                const tracker = document.getElementById('weather-sunset-tracker');
                if (!tracker) return;

                if (expand) {
                    tracker.classList.remove('minimized');
                    const exp = document.getElementById('tracker-expanded');
                    if (exp) exp.style.animation = 'nearbyCardSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                } else {
                    tracker.classList.add('minimized');
                    const tab = document.getElementById('tracker-edge-tab');
                    if (tab) tab.style.animation = 'buttonTapPop 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                }
            };

            window.fetchLiveMarineTelemetry = async function (isManual) {
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
                } catch (e) {
                    console.warn("Marine telemetry live update:", e);
                } finally {
                    if (refreshIcon) setTimeout(() => refreshIcon.classList.remove('fa-spin'), 500);
                    window.updateWeatherSunsetTrackerUI();
                }
            };

            window.updateWeatherSunsetTrackerUI = function () {
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

            window.findSunsetSpots = function () {
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

            // Initialize real-time telemetry and schedule live ticks cleanly
            window.fetchLiveMarineTelemetry(false);
            if (window._mapWeatherTickInterval) clearInterval(window._mapWeatherTickInterval);
            if (window._mapMarineSyncInterval) clearInterval(window._mapMarineSyncInterval);
            window._mapWeatherTickInterval = setInterval(() => {
                if (document.visibilityState === 'visible' && document.body.getAttribute('data-view') === 'map') {
                    if (typeof window.updateWeatherSunsetTrackerUI === 'function') window.updateWeatherSunsetTrackerUI();
                }
            }, 30000); // 30s tick
            window._mapMarineSyncInterval = setInterval(() => {
                if (document.visibilityState === 'visible' && document.body.getAttribute('data-view') === 'map') {
                    if (typeof window.fetchLiveMarineTelemetry === 'function') window.fetchLiveMarineTelemetry(false);
                }
            }, 300000); // 5 min sync

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
                            try { window.mapInstance.setTerrain(null); } catch (e) { }
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
                document.body.classList.add('sheet-open');
                setTimeout(() => { if (!isDragging) sheet.style.transition = ''; }, 500);
            }

            function closeSheet() {
                isOpen = false;
                document.body.classList.remove('sheet-open');
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

        initDraggableSheet('place-details-sheet', 'place-drag-handle', function () {
            if (window.clearAmenityMarkers) {
                window.clearAmenityMarkers();
            }
        });
        initDraggableSheet('nearby-sites-sheet', 'nearby-drag-handle');


        window.isPlaceSaved = function (destId) {
            if (!destId) return false;
            try {
                const savedIds = JSON.parse(localStorage.getItem('intan_elyu_saved_place_ids') || '[]');
                return savedIds.some(id => id == destId);
            } catch (e) {
                return false;
            }
        };

        window.updateSheetFavButton = function (isSaved) {
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

        window.toggleMapFavorite = function (element) {
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
            } catch (e) { }

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

        window.openSheet = function (locationData) {
            if (!locationData) return;
            if (window.closeNearbySitesSheet) {
                window.closeNearbySitesSheet();
            }
            const targetSheet = document.getElementById('place-details-sheet');
            if (!targetSheet) return;
            const scrollArea = targetSheet.querySelector('.draggable-content');
            if (scrollArea) {
                scrollArea.scrollTop = 0;
                scrollArea.scrollLeft = 0;
            }
            window.currentDestinationForRoute = locationData;
            if (window.activePopup) {
                window.activePopup.remove();
            }

            // Display nearby non-clickable amenities on map for this destination
            if (window.loadNearbyAmenities) {
                window.loadNearbyAmenities(locationData);
            }

            // Smoothly zoom in to street level when viewing tourist site details (as requested)
            const destLat = parseFloat(locationData.lat || locationData.latitude);
            const destLng = parseFloat(locationData.lng || locationData.longitude);
            if (!isNaN(destLat) && !isNaN(destLng) && window.mapInstance) {
                window.mapInstance.flyTo({
                    center: [destLng, destLat],
                    zoom: 15.3,
                    offset: [0, -90],
                    duration: 850,
                    essential: true,
                    curve: 1.42
                });
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
                        statusBadge.innerHTML = '<i class="fa-solid fa-circle-check" style="font-size:9px; margin-right:4px; color:#ffffff;"></i>Existing';
                    } else if (locationData.classification_status === 'EMERGE') {
                        statusBadge.className = 'sheet-status-pill status-emerge';
                        statusBadge.innerHTML = '<i class="fa-solid fa-sparkles" style="font-size:9px; margin-right:4px; color:#ffffff;"></i>Emerging';
                    } else if (locationData.classification_status === 'POTENTIAL') {
                        statusBadge.className = 'sheet-status-pill status-potential';
                        statusBadge.innerHTML = '<i class="fa-solid fa-compass" style="font-size:9px; margin-right:4px; color:#ffffff;"></i>Potential';
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

            // Set Visitors & Rating stats in quick stats grid
            const visitorsEl = document.getElementById('sheet-visitors');
            if (visitorsEl) {
                const vCount = parseInt(locationData.visits) || 0;
                visitorsEl.textContent = window.formatVisitorCount ? window.formatVisitorCount(vCount) : (vCount < 100 ? 'Less than 100 this month' : `${vCount.toLocaleString()} visitors this month`);
            }

            const ratingEl = document.getElementById('sheet-rating');
            if (ratingEl) {
                const rVal = (locationData.rating && parseFloat(locationData.rating) > 0) ? parseFloat(locationData.rating).toFixed(1) : '5.0';
                ratingEl.textContent = `${rVal} ★`;
            }

            // Reset check-in button state
            const checkinBtn = document.getElementById('btn-checkin-spot');
            if (checkinBtn) {
                checkinBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Check In (+50 XP)';
                checkinBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                checkinBtn.disabled = false;
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
                    img.loading = 'lazy';
                    img.decoding = 'async';
                    img.className = 'sheet-img';
                    img.style.cssText = 'flex:0 0 100%; min-width:100%; width:100%; max-width:100%; height:100% !important; object-fit:cover !important; object-position:center !important; border-radius:20px !important; scroll-snap-align:start; scroll-snap-stop:always; display:block !important; margin:0 !important; box-sizing:border-box !important;';
                    img.onerror = function () {
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

                        track.onscroll = function () {
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
                    tagsHtml += `<span style="font-size:11px; font-weight:800; background:rgba(56,189,248,0.22); color:#7dd3fc; border:none !important; outline:none !important; padding:4px 9px; border-radius:8px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-ticket" style="font-size:10px;"></i> Entrance: ₱${entranceFee.toFixed(2)}</span>`;
                }
                if (hasEnvironmental && environmentalFee > 0) {
                    tagsHtml += `<span style="font-size:11px; font-weight:800; background:rgba(52,211,153,0.22); color:#6ee7b7; border:none !important; outline:none !important; padding:4px 9px; border-radius:8px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-leaf" style="font-size:10px;"></i> Envi: ₱${environmentalFee.toFixed(2)}</span>`;
                }

                if (tagsHtml !== '') {
                    const total = (hasEntrance ? entranceFee : 0) + (hasEnvironmental ? environmentalFee : 0);
                    feeMainText.textContent = total > 0 ? `₱${total.toFixed(2)} Total Fees` : 'Free Admission';
                    feeTags.innerHTML = tagsHtml;
                } else {
                    feeMainText.textContent = 'Free Admission';
                    feeTags.innerHTML = `<span style="font-size:11px; font-weight:800; background:rgba(16,185,129,0.22); color:#6ee7b7; border:none !important; outline:none !important; padding:4px 9px; border-radius:8px;">No Entrance Fee</span>`;
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
                    <div style="background:rgba(56,189,248,0.08); border:none !important; outline:none !important; border-radius:12px; padding:8px 10px;">
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
                const spotName = locationData.name || 'This destination';
                const muniName = locationData.municipality || 'La Union';
                const brgyName = locationData.barangay ? `in Barangay ${locationData.barangay}, ` : '';
                const catName = (locationData.category || 'tourist attraction').toLowerCase();

                let rawDesc = (locationData.description && locationData.description.trim()) ? locationData.description.trim() : '';

                // Provide a rich, informative fallback when database description is empty
                if (!rawDesc) {
                    if (catName.includes('heritage') || catName.includes('cultural') || catName.includes('watchtower') || catName.includes('monument') || catName.includes('landmark')) {
                        rawDesc = `A historic cultural landmark located ${brgyName}${muniName}, La Union. Built during the Spanish colonial era, it stands as an enduring heritage site historically utilized for coastal defense, beacon signaling, and maritime surveillance along the coast of La Union.`;
                    } else if (catName.includes('beach') || catName.includes('surf') || catName.includes('coastal') || catName.includes('island')) {
                        rawDesc = `A premier coastal destination located ${brgyName}${muniName}, La Union. Renowned for its scenic seaside shores, refreshing ocean breezes, and relaxing tropical ambiance.`;
                    } else if (catName.includes('water') || catName.includes('fall') || catName.includes('river') || catName.includes('lake')) {
                        rawDesc = `A refreshing eco-tourism attraction nestled ${brgyName}${muniName}, La Union, featuring pristine mountain waters, natural rock formations, and lush surrounding flora.`;
                    } else if (catName.includes('church') || catName.includes('relig') || catName.includes('shrine') || catName.includes('parish')) {
                        rawDesc = `A revered historical and spiritual sanctuary located ${brgyName}${muniName}, La Union, welcoming devotees and travelers with its sacred heritage and enduring architectural beauty.`;
                    } else if (catName.includes('nature') || catName.includes('park') || catName.includes('farm') || catName.includes('mountain')) {
                        rawDesc = `A peaceful nature destination situated ${brgyName}${muniName}, La Union, offering picturesque views, verdant outdoor scenery, and a relaxing retreat.`;
                    } else {
                        rawDesc = `A popular ${catName} situated ${brgyName}${muniName}, La Union, providing visitors with an authentic and memorable travel experience in the province of La Union.`;
                    }
                }

                const words = rawDesc.split(' ');
                if (words.length > 35) {
                    if (descShort) descShort.textContent = words.slice(0, 35).join(' ') + '...';
                    if (descFull) descFull.textContent = rawDesc;
                    if (btnViewDetails) {
                        btnViewDetails.style.display = 'inline-flex';
                        const btnText = document.getElementById('details-btn-text');
                        if (btnText) btnText.textContent = 'Read More';
                    }
                } else {
                    if (descShort) descShort.textContent = rawDesc;
                    if (descFull) descFull.textContent = '';
                    if (btnViewDetails) btnViewDetails.style.display = 'none';
                }
                if (descShort) descShort.style.display = 'block';
                if (descFull) descFull.style.display = 'none';
            }

            // Always display the travel details & support info directly
            const expDetails = document.getElementById('expanded-details');
            if (expDetails) expDetails.style.display = 'flex';

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

        window.closeSheet = function () {
            document.body.classList.remove('sheet-open');
            const bNav = document.getElementById('bottom-navigation');
            const mNav = document.getElementById('magic-nav');
            if (bNav) bNav.classList.remove('keyboard-hidden');
            if (mNav) mNav.classList.remove('keyboard-hidden');
            if (window.sheetSliderTimer) {
                clearInterval(window.sheetSliderTimer);
                window.sheetSliderTimer = null;
            }
            if (window.clearAmenityMarkers) {
                window.clearAmenityMarkers();
            }
            const placeSheet = document.getElementById('place-details-sheet');
            if (placeSheet.closeSheet) placeSheet.closeSheet();
            else placeSheet.classList.remove('active');
        };

        window.showAddConfirm = function (destName) {
            const overlay = document.getElementById('itin-add-confirm');
            const nameEl = document.getElementById('itin-add-confirm-name');
            const inner = overlay.querySelector('div > div');
            if (nameEl) nameEl.textContent = destName;
            overlay.style.pointerEvents = 'all';
            overlay.style.opacity = '1';
            inner.style.transform = 'scale(1)';
        };

        window.closeAddConfirm = function () {
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

        window.viewItinerary = function () {
            window.closeAddConfirm();
            window.location.hash = '#itinerary';
        };

        window.toggleFullDetails = function () {
            const shortDesc = document.getElementById('sheet-desc-short');
            const fullDesc = document.getElementById('sheet-desc-full');
            const btnText = document.getElementById('details-btn-text');
            const chevron = document.getElementById('details-chevron');

            if (!shortDesc || !fullDesc) return;

            if (fullDesc.style.display === 'none') {
                shortDesc.style.display = 'none';
                fullDesc.style.display = 'block';
                if (btnText) btnText.textContent = 'Read Less';
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            } else {
                shortDesc.style.display = 'block';
                fullDesc.style.display = 'none';
                if (btnText) btnText.textContent = 'Read More';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        };

        window.contactMTO = function () {
            showToast('Connecting you to Municipal Tourism Office...');
            // In a real app, this would open a phone dialer or chat:
            // window.location.href = 'tel:+639123456789';
            setTimeout(() => {
                alert("MTO Contact Info:\\nPhone: +63 912 345 6789\\nEmail: tourism@elyu.gov.ph\\n\\nThey can arrange a local guide or habal-habal for your trip!");
            }, 500);
        };

        window.selectRouteOption = function (index) {
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
            <div style="background: rgba(245, 158, 11, 0.15); border: none !important; outline: none !important; border-radius: 14px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #fbbf24; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                ☀️ <span>Peak Season Active (Oct-May): Fares include +20% surcharge</span>
            </div>`;
            }

            const createCard = (name, icon, color, desc, baseFare, schedule) => {
                const finalFare = Math.round(baseFare * peakSurcharge);
                const isPublic = (['Tricycle', 'Jeepney', 'Bus'].includes(name));
                return `
            <div onclick="toggleVehicle(this)"
                 data-vehicle='${JSON.stringify({ name, icon, color, desc, fare: finalFare })}'
                 data-type="${isPublic ? 'public' : 'private'}"
                 style="cursor:pointer; display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border:none !important; outline:none !important; border-radius:18px; background:rgba(255,255,255,0.06); margin-bottom:10px; transition:transform 0.15s, background 0.15s;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px; background:rgba(56,189,248,0.14); border:none !important; outline:none !important; color:${color}; flex-shrink:0;">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                    <div style="text-align: left;">
                        <h5 style="margin:0 0 3px; font-size:15px; font-weight:800; color:#f8fafc; letter-spacing:-0.2px;">${name}</h5>
                        <span style="font-size:12px; color:rgba(148,163,184,0.75); font-weight:500; display:block;">${desc}</span>
                        <span style="font-size:10px; color:rgba(148,163,184,0.5); font-weight:600; display:flex; align-items:center; gap:4px; margin-top:3px;"><i class="fa-regular fa-clock"></i> ${schedule}</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="background:rgba(56,189,248,0.16); border:none !important; outline:none !important; padding:6px 12px; border-radius:10px; font-weight:800; color:#38bdf8; font-size:15px; flex-shrink:0;">₱${finalFare}</div>
                    <div class="vehicle-check" style="width:22px;height:22px;border-radius:50%;border:none !important;outline:none !important;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;transition:all 0.15s;"><i class="fa-solid fa-check" style="opacity:0;transition:opacity 0.15s;"></i></div>
                </div>
            </div>`;
            };

            const targetMuni = destData.municipality || 'San Juan';
            const dbFare = (type) => window.getFareFromMatrix(type, distanceKm, targetMuni);

            const getDbVehicle = (searchName) => {
                if (window.vehicleData && Array.isArray(window.vehicleData)) {
                    return window.vehicleData.find(v => v.name.toLowerCase().includes(searchName.toLowerCase()) || searchName.toLowerCase().includes(v.name.toLowerCase()));
                }
                return null;
            };

            const trikeInfo = getDbVehicle('Tricycle');
            const jeepInfo = getDbVehicle('Jeepney');
            const busInfo = getDbVehicle('Bus');
            const taxiInfo = getDbVehicle('Taxi');
            const carInfo = getDbVehicle('Private Car') || getDbVehicle('Car');

            const trikeIcon = trikeInfo?.icon || 'fa-motorcycle';
            const jeepIcon = jeepInfo?.icon || 'fa-bus';
            const busIcon = busInfo?.icon || 'fa-bus-alt';
            const taxiIcon = taxiInfo?.icon || 'fa-taxi';
            const carIcon = carInfo?.icon || 'fa-car';

            const trikeDesc = trikeInfo?.description || 'Fits narrow roads, best for short trips';
            const jeepDesc = jeepInfo?.description || 'Main roads / highways only';
            const busDesc = busInfo?.description || 'Main roads / highways — best for long distance';
            const taxiDesc = taxiInfo?.description || 'Main roads / highways — metered fare';
            const carDesc = carInfo?.description || 'Cannot go on tight/narrow roads';

            const carKml = carInfo?.fuel_efficiency_kml ? parseFloat(carInfo.fuel_efficiency_kml) : 12.0;
            const currentFuelPrice = window.fuelPrice || 65.0;

            if (tightRoads) {
                const trikeFare = dbFare('Tricycle') ?? Math.round(16.32 + (Math.max(0, distanceKm - 1.7) * 2.0));
                faresHtml += createCard('Tricycle', trikeIcon, 'var(--secondary-color)', trikeInfo?.description || 'Only vehicle that fits narrow/tight roads', trikeFare, '24/7 (Night Rates 10PM+)');
            } else {
                if (distanceKm <= 10) {
                    const trikeFare = dbFare('Tricycle') ?? Math.round(16.32 + (Math.max(0, distanceKm - 1.7) * 2.0));
                    faresHtml += createCard('Tricycle', trikeIcon, 'var(--secondary-color)', trikeDesc, trikeFare, '24/7 (Night Rates 10PM+)');
                }
                if (distanceKm >= 2) {
                    const taxiFare = Math.round(40 + (distanceKm * 13));
                    faresHtml += createCard('Taxi', taxiIcon, '#f97316', taxiDesc, taxiFare, '24/7 Service');
                }
                if (distanceKm >= 3 && distanceKm <= 35) {
                    const jeepFare = dbFare('Jeepney') ?? Math.round(13 + (Math.max(0, distanceKm - 4) * 1.8));
                    faresHtml += createCard('Jeepney', jeepIcon, '#f59e0b', jeepDesc, jeepFare, '6:00 AM - 8:00 PM');
                }
                if (distanceKm > 10) {
                    const busFare = dbFare('Bus') ?? Math.round(15 + (Math.max(0, distanceKm - 5) * 2.2));
                    faresHtml += createCard('Bus', busIcon, '#ef4444', busDesc, busFare, '4:00 AM - 11:00 PM');
                }
                const ownCarFare = Math.max(10, Math.round((distanceKm / carKml) * currentFuelPrice));
                faresHtml += createCard('Own Car (Fuel Est.)', carIcon, '#34d399', carDesc, ownCarFare, 'Anytime');
            }
            document.getElementById('fare-list').innerHTML = faresHtml;
            setupVehicleSelection();
        };

        window.toggleVehicle = function (el) {
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

        window.addToItinerary = function () {
            if (!window.currentDestinationForRoute) return;
            const dest = window.currentDestinationForRoute;

            // Save to localStorage draft
            let draft = [];
            try {
                draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary')) || [];
            } catch (e) { }

            // Add if not already there
            if (!draft.find(item => String(item.id) === String(dest.id))) {
                draft.push(dest);
                localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
                // Show "Added to Itinerary!" modal
                const confirmName = document.getElementById('itin-add-confirm-name');
                if (confirmName) confirmName.textContent = dest.name;
                const titleEl = document.querySelector('#itin-add-confirm h3');
                const iconWrap = document.getElementById('itin-add-confirm-icon-wrap') || document.querySelector('#itin-add-confirm div > div');
                const btnView = document.querySelector('#itin-add-confirm button[onclick*="viewItinerary"]');
                if (titleEl) titleEl.textContent = 'Added to Itinerary!';
                if (iconWrap) {
                    iconWrap.innerHTML = '<i class="fa-solid fa-check" style="font-size:32px; color:#ffffff;"></i>';
                    iconWrap.style.cssText = 'width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg, #34d399 0%, #10b981 100%); border:3px solid rgba(255,255,255,0.4) !important; box-shadow:0 8px 24px rgba(16,185,129,0.5), 0 0 20px rgba(52,211,153,0.45); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;';
                }
                if (btnView) btnView.style.display = '';
            } else {
                // Show "Already in Itinerary" modal
                const confirmName = document.getElementById('itin-add-confirm-name');
                if (confirmName) confirmName.textContent = dest.name + ' is already added to itinerary.';
                const titleEl = document.querySelector('#itin-add-confirm h3');
                const iconWrap = document.getElementById('itin-add-confirm-icon-wrap') || document.querySelector('#itin-add-confirm div > div');
                const btnView = document.querySelector('#itin-add-confirm button[onclick*="viewItinerary"]');
                if (titleEl) titleEl.textContent = 'Already in Itinerary';
                if (iconWrap) {
                    iconWrap.innerHTML = '<i class="fa-solid fa-bookmark" style="font-size:30px; color:#ffffff;"></i>';
                    iconWrap.style.cssText = 'width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg, #fbbf24 0%, #d97706 100%); border:3px solid rgba(255,255,255,0.4) !important; box-shadow:0 8px 24px rgba(245,158,11,0.5), 0 0 20px rgba(251,191,36,0.45); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;';
                }
                if (btnView) btnView.style.display = 'none';
            }

            window.closeSheet();

            const confirmModal = document.getElementById('itin-add-confirm');
            if (confirmModal) {
                confirmModal.style.opacity = '1';
                confirmModal.style.pointerEvents = 'auto';
                const card = confirmModal.querySelector('div');
                if (card) {
                    card.style.transform = 'scale(1)';
                }
            }
        };

        window.closeAddConfirm = function () {
            const confirmModal = document.getElementById('itin-add-confirm');
            if (confirmModal) {
                confirmModal.style.opacity = '0';
                confirmModal.style.pointerEvents = 'none';
                const card = confirmModal.querySelector('div');
                if (card) {
                    card.style.transform = 'scale(0.85)';
                }
            }
        };

        window.viewItinerary = function () {
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
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px;">
                            <div style="background:rgba(255,255,255,0.06); border:none !important; outline:none !important; border-radius:12px; padding:8px 10px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:10px; color:rgba(226,232,240,0.7); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Cleanliness</span>
                                <span style="font-size:11px; font-weight:800; color:${cleanColor}; background:${cleanBg}; border:none !important; outline:none !important; padding:2px 8px; border-radius:6px;">${cleanVal}</span>
                            </div>
                            <div style="background:rgba(255,255,255,0.06); border:none !important; outline:none !important; border-radius:12px; padding:8px 10px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:10px; color:rgba(226,232,240,0.7); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Safety</span>
                                <span style="font-size:11px; font-weight:800; color:${safeColor}; background:${safeBg}; border:none !important; outline:none !important; padding:2px 8px; border-radius:6px;">${safeVal}</span>
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
                                part = part || '';
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
                            const date = fb.created_at ? new Date(fb.created_at).toLocaleDateString() : '';
                            const policyHtml = fb.policy_recommendation ? `
                            <div style="background:rgba(56,189,248,0.08); border:none !important; outline:none !important; padding:10px 12px; border-radius:12px; margin-top:10px;">
                                <div style="display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                                    <i class="fa-solid fa-lightbulb" style="color:#38bdf8; font-size:11px;"></i>
                                    <strong style="font-size:10px; color:#38bdf8; text-transform:uppercase; letter-spacing:0.5px; font-weight:800;">Policy Recommendation</strong>
                                </div>
                                <span style="color:rgba(226,232,240,0.9); font-size:12px; line-height:1.4; display:block;">${fb.policy_recommendation}</span>
                            </div>` : '';

                            return `
                        <div style="background:rgba(255,255,255,0.06); border:none !important; outline:none !important; padding:14px; border-radius:16px; font-size:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg, #38bdf8, #2563eb); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:11px;">
                                        ${initial}
                                    </div>
                                    <strong style="color:#ffffff; font-size:13px; font-weight:700;">${maskedName}</strong>
                                </div>
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
                        list.innerHTML = '<div style="font-size:12.5px; color:#ffffff; opacity:0.95; font-weight:500; text-align:center; padding:12px 10px;">No testimonies yet. Be the first to share!</div>';
                    }
                }
            } catch (e) {
                console.error("Testimonies load error:", e);
                list.innerHTML = '<div style="font-size:12.5px; color:#ffffff; opacity:0.95; font-weight:500; text-align:center; padding:12px 10px;">Failed to load reviews.</div>';
            }
        }

        window.toggleAllTestimonies = function () {
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

        window.openWriteTestimonyModal = function () {
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

        window.closeWriteTestimonyModal = function () {
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

        window.setStarRating = function (rating) {
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


        window.selectCleanliness = function (val) {
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

        window.selectSafety = function (val) {
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

        window.submitTestimony = async function (event) {
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
                    if (typeof showToast === 'function') showToast(data.message || "Thank you for your rating & feedback! 🗣️");
                    window.closeWriteTestimonyModal();
                    fetchTestimonies(spotId);

                    // Update rating in current sheet live
                    if (window.currentDestinationForRoute && window.currentDestinationForRoute.id == spotId) {
                        const newRating = data.spot_rating || rating;
                        window.currentDestinationForRoute.rating = newRating;
                        const ratingEl = document.getElementById('sheet-rating');
                        if (ratingEl) ratingEl.textContent = parseFloat(newRating).toFixed(1) + ' ★';
                    }
                } else {
                    if (typeof showToast === 'function') showToast(data.message || "Failed to submit review.");
                }
            } catch (error) {
                console.error("Testimony submission error:", error);
                if (typeof showToast === 'function') showToast("Network error.");
            }
        };

        window.checkInAtCurrentSpot = async function () {
            if (!window.currentDestinationForRoute || !window.currentDestinationForRoute.id) return;
            const dest = window.currentDestinationForRoute;
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');

            const btn = document.getElementById('btn-checkin-spot');
            const origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking in...';
                btn.disabled = true;
            }

            const _backendUrl = (window.backendUrl && window.backendUrl.trim() !== '')
                ? window.backendUrl
                : (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : 'https://api.intan-elyu.online');

            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };
            if (token) headers['Authorization'] = 'Bearer ' + token;

            try {
                const res = await fetch(_backendUrl + '/api/tourist/destinations/' + dest.id + '/check-in', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        lat: window.myLat || dest.lat || dest.latitude,
                        lng: window.myLng || dest.lng || dest.longitude
                    })
                });

                const data = await res.json();
                if (res.ok && (data.status === 'success' || data.success)) {
                    if (typeof showToast === 'function') showToast(data.message || `🎉 Check-in verified! Earned +50 XP & +50 Points at ${dest.name}!`);

                    // Update local spot visitors count
                    const newVisits = data.visits || ((parseInt(dest.visits) || 0) + 1);
                    dest.visits = newVisits;
                    const visitorsEl = document.getElementById('sheet-visitors');
                    if (visitorsEl) visitorsEl.textContent = window.formatVisitorCount ? window.formatVisitorCount(newVisits) : (newVisits < 100 ? 'Less than 100 this month' : `${newVisits.toLocaleString()} visitors this month`);

                    if (btn) {
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> Checked In!';
                        btn.style.background = 'linear-gradient(135deg, #059669, #047857)';
                    }
                } else {
                    if (typeof showToast === 'function') showToast(data.message || 'Check-in completed!');
                    if (btn) {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                    }
                }
            } catch (err) {
                console.error('Check-in error:', err);
                if (typeof showToast === 'function') showToast('Network error during check-in.');
                if (btn) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }
            }
        };

        setTimeout(window.initMap, 50);

        // Auto-refresh: poll for new spots cleanly (throttled & visibility-aware)
        if (window._mapSpotsCheckInterval) {
            clearInterval(window._mapSpotsCheckInterval);
            window._mapSpotsCheckInterval = null;
        }
        async function checkForNewSpots() {
            if (!window.mapInstance || document.visibilityState !== 'visible' || document.body.getAttribute('data-view') !== 'map') return;
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
        window._mapSpotsCheckInterval = setInterval(checkForNewSpots, 60000); // Poll every 60s instead of 10s to prevent mobile lag
    })();
</script>