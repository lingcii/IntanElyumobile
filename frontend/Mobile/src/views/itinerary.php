<?php
$pageTitle = 'My Itinerary';
$activeTab = 'itinerary';

// Load spots map with R2 photo_urls for instant client-side thumbnail lookup
$spotsPhotoMap = [];
try {
    if (class_exists('App\Models\TouristSpot')) {
        $spotsWithPhotos = App\Models\TouristSpot::whereNotNull('photo_url')->where('photo_url', '!=', '')->get(['id', 'name', 'photo_url']);
        foreach ($spotsWithPhotos as $sp) {
            $spotsPhotoMap[$sp->id] = [
                'photo_url' => $sp->photo_url,
                'name' => $sp->name
            ];
        }
    }
} catch (\Throwable $e) {}
?>
<script>
window.SPOTS_R2_MAP = <?= json_encode($spotsPhotoMap) ?>;
</script>



<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    body,
    body[data-view="itinerary"],
    .itinerary-container {
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    .route-toggle-container {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        position: relative;
        background: #f1f5f9 !important;
        border: none !important;
        outline: none !important;
        border-radius: 9999px;
        padding: 3px;
        margin-bottom: 14px;
        width: 240px;
        max-width: 100%;
        box-sizing: border-box;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
        overflow: hidden;
    }

    .route-toggle-slider {
        position: absolute;
        top: 3px;
        bottom: 3px;
        left: 3px;
        width: calc(50% - 3px);
        border-radius: 9999px;
        background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
        box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;
        transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        z-index: 1;
        pointer-events: none;
        transform: translateX(0);
    }

    .route-toggle-container.alt-active .route-toggle-slider {
        transform: translateX(100%) !important;
    }

    .btn-route-type {
        position: relative;
        z-index: 2;
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #64748b !important;
        padding: 8px 0 !important;
        border-radius: 9999px !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        transition: color 0.25s ease, transform 0.15s ease !important;
        white-space: nowrap !important;
        text-align: center !important;
        box-shadow: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        margin: 0 !important;
    }

    .btn-route-type:active {
        transform: scale(0.96);
    }

    .btn-route-type.active {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        text-shadow: none !important;
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    /* Big Container from Recommended to Save Draft Plan */
    .draft-plan-card-wrapper {
        background: #f8fafc !important;
        border: 1.5px solid #e2e8f0 !important;
        outline: none !important;
        border-radius: 26px !important;
        padding: 16px !important;
        margin-top: 14px !important;
        margin-bottom: 36px !important;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 8px -1px rgba(15, 23, 42, 0.03) !important;
        display: flex !important;
        flex-direction: column !important;
        box-sizing: border-box !important;
        position: relative !important;
        width: 100% !important;
    }

    .draft-plan-card-wrapper .itinerary-stops-container {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        border-radius: 0 !important;
    }

    /* Itinerary Stops Container & Swap Controls */
    .itinerary-stops-container {
        background: #f8fafc !important;
        border: none !important;
        outline: none !important;
        border-radius: 26px !important;
        padding: 14px !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03) !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        margin-top: 6px !important;
        margin-bottom: 20px !important;
        position: relative !important;
        box-sizing: border-box !important;
    }

    .stops-swap-divider {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative !important;
        margin: 2px 0 !important;
        z-index: 6 !important;
        height: 32px !important;
    }

    .stops-swap-line {
        position: absolute !important;
        left: 20px !important;
        right: 20px !important;
        height: 1px !important;
        background: #e2e8f0 !important;
        pointer-events: none !important;
    }

    .btn-swap-pill {
        position: relative !important;
        background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
        color: #ffffff !important;
        border: none !important;
        outline: none !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        box-shadow: 0 2px 8px rgba(32, 63, 141, 0.28) !important;
        font-size: 12px !important;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        z-index: 2 !important;
    }

    .btn-swap-pill:hover {
        transform: scale(1.12) !important;
        box-shadow: 0 4px 12px rgba(32, 63, 141, 0.35) !important;
    }

    .btn-swap-pill:active {
        transform: scale(0.9) rotate(180deg) !important;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .itinerary-stops-container .timeline-item {
        margin-bottom: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
        will-change: transform;
        animation: none !important;
    }

    .itinerary-stops-container .starting-point-item {
        margin-bottom: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
        animation: none !important;
    }

    .stop-thumbnail-wrapper {
        width: 72px;
        height: 72px;
        border-radius: 16px !important;
        overflow: hidden !important;
        flex-shrink: 0 !important;
        position: relative !important;
        box-shadow: none !important;
        background: rgba(15, 23, 42, 0.45) !important;
        border: none !important;
        outline: none !important;
    }

    .stop-thumbnail-wrapper img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        transition: transform 0.3s ease !important;
    }

    .stops-leg-chip {
        background: #ffffff !important;
        border-radius: 100px !important;
        padding: 5px 13px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #1e3a8a !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
        border: none !important;
        outline: none !important;
        user-select: none;
    }

    .starting-leg-divider {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 12px 0 10px !important;
        height: 36px !important;
    }

    .starting-leg-icon-pill {
        position: relative !important;
        background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
        color: #ffffff !important;
        border: none !important;
        outline: none !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 2px 8px rgba(32, 63, 141, 0.28) !important;
        font-size: 11px !important;
        z-index: 2 !important;
        flex-shrink: 0 !important;
        transition: transform 0.2s ease !important;
    }

    /* Redesigned Route Connector */
    .timeline-route-connector {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 6px 0 !important;
        margin: 0 auto !important;
        position: relative !important;
        z-index: 1 !important;
        background: none !important;
        width: 100% !important;
        height: auto !important;
        opacity: 1 !important;
    }

    .route-connector-track {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative !important;
        height: 32px !important;
        width: 24px !important;
    }

    .route-connector-line {
        width: 2.5px !important;
        flex: 1 !important;
        background: linear-gradient(to bottom, rgba(0, 242, 254, 0.8), rgba(56, 189, 248, 0.4)) !important;
        border-radius: 999px !important;
        box-shadow: none !important;
    }

    .route-connector-node {
        width: 20px !important;
        height: 20px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #ffffff !important;
        font-size: 9px !important;
        box-shadow: none !important;
        margin: 2px 0 !important;
        border: none !important;
        outline: none !important;
    }

    .starting-point-pulse {
        display: none !important;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .donut-chart {
        background: conic-gradient(#38bdf8 0% 33%,
                #34c759 33% 66%,
                #ff9500 66% 100%);
        mask: radial-gradient(transparent 50%, black 51%);
        -webkit-mask: radial-gradient(transparent 50%, black 51%);
        transition: background 0.4s ease;
    }

    /* Hide number input spinners */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<div class="itinerary-container has-header has-bottom-nav animate-slide-up">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding-top: 16px;"
        class="stagger-1">
        <h2 id="itinerary-page-title" style="margin:0; font-size:22px; font-weight:800; letter-spacing:-0.5px; color:#0f172a !important;">Draft Plan</h2>
        <div style="display:flex; align-items:center; gap: 8px;">
            <!-- Saved Trips Button (Small) -->
            <button onclick="navigateTo('saved_trips')"
                style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; color: #ffffff !important; font-weight:700; height: 32px; padding: 0 14px; border-radius:20px; font-size:12px; cursor:pointer; display:flex; align-items:center; box-sizing: border-box; box-shadow: none !important;">
                <i class="fa-solid fa-bookmark" style="margin-right:6px;"></i> Saved Trips
            </button>
            <span
                style="background:#f1f5f9 !important; border: none !important; outline: none !important; color:#1e3a8a !important; height: 32px; padding: 0 14px; border-radius:20px; font-size:12px; font-weight:800; display:flex; align-items:center; box-sizing: border-box; box-shadow: none !important;">
                <span id="itinerary-count" style="margin-right:4px;">0</span> Places
            </span>
        </div>
    </div>

    <!-- Big Container from Recommended to Save Draft Plan -->
    <div id="draft-plan-card-wrapper" class="draft-plan-card-wrapper stagger-2" style="display:none;">
        <!-- Active Editing Trip Banner -->
        <div id="editing-plan-banner" style="display:none; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color:#ffffff; padding:10px 16px; border-radius:14px; margin-bottom:14px; font-size:12.5px; font-weight:700; border:none !important; outline:none !important; box-shadow:none !important;">
            <div style="display:flex; align-items:center; gap:8px; min-width:0; flex:1;">
                <i class="fa-solid fa-pen-to-square" style="color:#38bdf8; font-size:14px;"></i>
                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Editing: <strong id="editing-banner-title">Saved Trip</strong></span>
            </div>
            <button type="button" onclick="window.cancelEditingSavedTrip()" style="background:rgba(255,255,255,0.22); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:800; padding:5px 12px; border-radius:100px; cursor:pointer; margin-left:10px; flex-shrink:0; box-shadow:none !important; transition:background 0.2s ease;">
                Cancel Edit
            </button>
        </div>
        <!-- Map Visualization Container -->
        <div id="draft-map-wrapper" style="display:none; margin-top:0; margin-bottom:14px;">
            <!-- Route Type Container with Smooth Sliding Pill -->
            <div class="route-toggle-container" id="route-toggle-container">
                <div class="route-toggle-slider" id="route-toggle-slider"></div>
                <button class="btn-route-type active" id="btn-route-rec"
                    onclick="setRouteType('recommended', this)">Recommended</button>
                <button class="btn-route-type" id="btn-route-alt"
                    onclick="setRouteType('alternative', this)">Alternative</button>
            </div>

            <!-- The Map -->
            <div id="draft-map-container"
                style="height: 260px; width:100%; border-radius: 20px; overflow: hidden; border: none !important; outline: none !important; position:relative; background:#cadce4; box-shadow: none;">
                <div id="itinerary-map" style="width:100%; height:100%;"></div>
            </div>

            <!-- Map Route Stats -->
            <div
                style="display:flex; justify-content:space-around; align-items:center; margin-top:12px; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border: none !important; outline: none !important; border-radius:18px; padding:14px; box-shadow:none !important;">
                <div style="color:white; font-size:14px; font-weight:700;">
                    <i class="fa-solid fa-route" style="color:#00f2fe; margin-right:6px; font-size:16px;"></i> <span
                        id="draft-map-dist">0 km</span>
                </div>
                <div style="width:1px; height:20px; background:rgba(255,255,255,0.2);"></div>
                <div
                    style="color:white; font-size:14px; font-weight:700; display:flex; flex-direction:column; align-items:center;">
                    <div><i class="fa-solid fa-clock" style="color:#00f2fe; margin-right:6px; font-size:16px;"></i> <span
                            id="draft-map-time">0 min</span></div>
                    <div id="draft-traffic-warning" style="display:none; margin-top:2px; font-size:10px; font-weight:500;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Timeline Container -->
        <div class="timeline" id="itinerary-timeline" style="margin-bottom: 14px;">
            <!-- Rendered via JS -->
        </div>

        <!-- Save Itinerary Action -->
        <button class="btn-primary" id="btn-save-itinerary"
            style="display:none; width:100%; padding:16px; border-radius:20px; font-weight:900; font-size:16px; margin-top:4px; margin-bottom:0; border: none !important; outline: none !important; background:linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; color:#ffffff !important; box-shadow: none !important;"
            onclick="openSaveModal()">
            <i class="fa-solid fa-cloud-arrow-up" style="margin-right:8px;"></i> Save Draft Plan
        </button>
    </div>

    <!-- Empty State Card -->
    <div id="itinerary-empty-state" class="empty-state-card is-hidden" style="display:none;">
        <div class="empty-state-icon" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;">
            <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
        </div>
        <h3>No plans yet</h3>
        <p>Go to the Map and tap <strong>"Add to Itinerary"</strong> on a place to start building your trip!</p>
        <button class="btn-open-map" onclick="navigateTo('map')">
            <i class="fa-solid fa-location-dot"></i> Open Map
        </button>
    </div>



</div>

<!-- Save Trip Modal -->
<div id="save-trip-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:99999; justify-content:center; align-items:center; padding:16px;">
    
    <style>
        #save-trip-modal input,
        #save-trip-modal button,
        #save-trip-modal select,
        #save-trip-modal div {
            outline: none !important;
            -webkit-tap-highlight-color: transparent !important;
            box-sizing: border-box;
        }
        #save-trip-modal input[type="text"],
        #save-trip-modal input[type="tel"],
        #save-trip-modal input[type="number"] {
            background: rgba(255, 255, 255, 0.12) !important;
            border: none !important;
            outline: none !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }
        #save-trip-modal input[type="text"]:focus,
        #save-trip-modal input[type="tel"]:focus,
        #save-trip-modal input[type="number"]:focus {
            outline: none !important;
            border: none !important;
            box-shadow: none !important;
            background: rgba(255, 255, 255, 0.18) !important;
        }
        #save-trip-modal input::placeholder {
            color: rgba(255, 255, 255, 0.8) !important;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.8) !important;
            opacity: 1 !important;
            font-weight: 500 !important;
        }
        #save-trip-modal input::-webkit-input-placeholder {
            color: rgba(255, 255, 255, 0.8) !important;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.8) !important;
            opacity: 1 !important;
            font-weight: 500 !important;
        }
        #save-trip-modal input::-moz-placeholder {
            color: rgba(255, 255, 255, 0.8) !important;
            opacity: 1 !important;
            font-weight: 500 !important;
        }
        #save-trip-modal input:-ms-input-placeholder {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500 !important;
        }
        #save-trip-modal label {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 13px !important;
        }
        #save-trip-modal p {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        #save-trip-modal * {
            outline: none !important;
        }
        #custom-calendar-dropdown {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transform: translateY(-10px) scale(0.98);
            transition: max-height 0.38s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s ease, transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), margin 0.3s ease, padding 0.3s ease !important;
            background: linear-gradient(135deg, rgba(16, 38, 86, 0.96) 0%, rgba(25, 55, 115, 0.96) 100%) !important;
            backdrop-filter: blur(24px) !important;
            -webkit-backdrop-filter: blur(24px) !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            border-radius: 20px !important;
            padding: 0 16px !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            pointer-events: none;
        }
        #custom-calendar-dropdown.calendar-open {
            max-height: 480px !important;
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
            padding: 16px !important;
            margin-top: -8px !important;
            margin-bottom: 16px !important;
            pointer-events: auto !important;
        }
    </style>

    <div
        style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.99) 100%); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:none !important; outline:none !important; border-radius:24px; padding:22px; width:100%; max-width:400px; max-height:90vh; overflow-y:auto; box-shadow:none !important;" class="hide-scrollbar">
        <h3 id="save-trip-modal-title" style="margin-top:0; color:#ffffff; font-size:20px; font-weight:800; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-cloud-arrow-up" style="color:#38bdf8; font-size:18px;"></i> Save Your Trip
        </h3>
        <p style="font-size:13px; color:rgba(255, 255, 255, 0.9); margin-bottom:18px; line-height:1.4;">Give your awesome adventure a name so you can pull it up later!</p>

        <label style="font-size:13px; color:#ffffff; margin-bottom:6px; display:block; font-weight:700;">Trip Name</label>
        <input type="text" id="trip-title" placeholder="e.g. La Union Weekend"
            style="width:100%; padding:12px 16px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.12); color:#ffffff; -webkit-text-fill-color:#ffffff; margin-bottom:16px; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box; box-shadow:none !important;">

        <!-- Custom Designed Calendar Date Picker -->
        <label style="font-size:13px; color:#ffffff; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between; font-weight:700;">
            <span><i class="fa-regular fa-calendar-days" style="color:#38bdf8; margin-right:5px;"></i> Trip Date (Optional)</span>
            <span id="calendar-clear-link" onclick="window.customClearDate(event)" style="display:none; font-size:11px; color:#ef4444; cursor:pointer; font-weight:700;">Clear</span>
        </label>
        
        <div id="custom-date-trigger" onclick="window.toggleCustomCalendar(event)" style="position:relative; width:100%; padding:11px 16px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.12); color:white; margin-bottom:16px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:all 0.25s ease; user-select:none; box-shadow:none !important;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-calendar-day" style="color:#38bdf8; font-size:14px;"></i>
                <span id="custom-date-display" style="color:rgba(255,255,255,0.85); font-weight:600;">Select trip date</span>
            </div>
            <i class="fa-solid fa-chevron-down" id="custom-date-arrow" style="font-size:11px; color:rgba(255,255,255,0.8); transition:transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);"></i>
        </div>
        <input type="hidden" id="trip-date" value="">

        <!-- Floating Sleek Custom Calendar Card with Smooth Slide Animation -->
        <div id="custom-calendar-dropdown">
            <!-- Month & Year Navigation -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding:0 4px;">
                <button type="button" onclick="window.changeCalendarMonth(-1)" style="background:rgba(255,255,255,0.12); border:none !important; outline:none !important; color:white; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; box-shadow:none !important;">
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                </button>
                <div id="calendar-month-year" style="font-size:14.5px; font-weight:800; color:#ffffff; letter-spacing:0.3px;"></div>
                <button type="button" onclick="window.changeCalendarMonth(1)" style="background:rgba(255,255,255,0.12); border:none !important; outline:none !important; color:white; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; box-shadow:none !important;">
                    <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                </button>
            </div>

            <!-- Day of Week Headers -->
            <div style="display:grid; grid-template-columns:repeat(7, 1fr); text-align:center; margin-bottom:6px;">
                <span style="font-size:11px; font-weight:700; color:#f87171;">Su</span>
                <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.85);">Mo</span>
                <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.85);">Tu</span>
                <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.85);">We</span>
                <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.85);">Th</span>
                <span style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.85);">Fr</span>
                <span style="font-size:11px; font-weight:700; color:#38bdf8;">Sa</span>
            </div>

            <!-- Days Grid -->
            <div id="calendar-days-grid" style="display:grid; grid-template-columns:repeat(7, 1fr); gap:3px; text-align:center;">
                <!-- Generated dynamically via JS -->
            </div>

            <!-- Footer Quick Actions -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.12);">
                <button type="button" onclick="window.selectTodayDate()" style="background:rgba(255,255,255,0.15); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:700; padding:6px 14px; border-radius:100px; cursor:pointer; box-shadow:none !important;">
                    Today
                </button>
                <button type="button" onclick="window.toggleCustomCalendar(null, false)" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:800; padding:6px 16px; border-radius:100px; cursor:pointer; box-shadow:none !important;">
                    Done
                </button>
            </div>
        </div>

        <label style="font-size:13px; color:#ffffff; margin-bottom:8px; display:block; font-weight:700;">Transport Type</label>
        <div id="transport-toggle-track"
            style="position:relative; display:grid; grid-template-columns:1fr 1fr; margin-bottom:16px; background:rgba(0,0,0,0.25); padding:4px; border-radius:14px; border:none !important; outline:none !important; box-shadow:none !important; user-select:none; height:44px; box-sizing:border-box; contain:layout style paint; -webkit-tap-highlight-color:transparent;">
            <!-- Smooth Sliding Pill Indicator with zero-twitch 3D transform -->
            <div id="transport-toggle-pill"
                style="position:absolute; top:4px; bottom:4px; left:4px; width:calc(50% - 4px); background:#ffffff; border-radius:10px; will-change:transform; transform:translate3d(0,0,0); -webkit-transform:translate3d(0,0,0); transition:transform 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important; pointer-events:none; z-index:1; border:none !important; outline:none !important; box-shadow:none !important; -webkit-backface-visibility:hidden; backface-visibility:hidden;"></div>
            <button type="button" class="btn-transport-toggle active" id="btn-trans-public" onclick="window.setTransportType('public', true)"
                style="position:relative; z-index:2; height:36px; line-height:36px; padding:0; border-radius:10px; border:none !important; outline:none !important; background:transparent !important; font-size:13px; font-weight:800 !important; color:#1e3a8a; transition:color 0.2s ease; cursor:pointer; box-shadow:none !important; text-align:center; display:flex; align-items:center; justify-content:center; -webkit-tap-highlight-color:transparent; touch-action:manipulation; user-select:none;">Public</button>
            <button type="button" class="btn-transport-toggle" id="btn-trans-private" onclick="window.setTransportType('private', true)"
                style="position:relative; z-index:2; height:36px; line-height:36px; padding:0; border-radius:10px; border:none !important; outline:none !important; background:transparent !important; font-size:13px; font-weight:800 !important; color:rgba(255,255,255,0.85); transition:color 0.2s ease; cursor:pointer; box-shadow:none !important; text-align:center; display:flex; align-items:center; justify-content:center; -webkit-tap-highlight-color:transparent; touch-action:manipulation; user-select:none;">Private</button>
        </div>

        <div id="transport-slider-wrapper" style="display:block; margin-bottom:16px;">
            <label id="mode-transport-label"
                style="font-size:13px; font-weight:700; color:#ffffff; margin-bottom:8px; display:block;">Mode of
                Transport</label>

            <style>
                #transport-slider::-webkit-scrollbar {
                    display: none;
                }

                .transport-option {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    min-width: 90px;
                    padding: 14px 10px;
                    border-radius: 16px;
                    background: #ffffff !important;
                    border: none !important;
                    outline: none !important;
                    box-shadow: none !important;
                    cursor: pointer;
                    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
                    color: #1e293b !important;
                    flex-shrink: 0;
                    box-sizing: border-box;
                    user-select: none;
                    -webkit-tap-highlight-color: transparent;
                }

                .transport-option i {
                    font-size: 22px;
                    margin-bottom: 8px;
                    color: #1e3a8a !important;
                    transition: color 0.25s ease;
                }

                .transport-option span {
                    font-size: 11px;
                    font-weight: 800;
                    text-align: center;
                    color: #1e293b !important;
                    white-space: nowrap;
                    transition: color 0.25s ease;
                }

                .transport-option.active {
                    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important;
                    border: none !important;
                    outline: none !important;
                    box-shadow: none !important;
                }

                .transport-option.active i {
                    color: #ffffff !important;
                }

                .transport-option.active span {
                    color: #ffffff !important;
                    font-weight: 800 !important;
                }

                .transport-option.disabled-transport {
                    opacity: 0.45 !important;
                    background: rgba(255, 255, 255, 0.65) !important;
                    border: none !important;
                    outline: none !important;
                    box-shadow: none !important;
                    cursor: not-allowed !important;
                }

                .transport-option.disabled-transport i {
                    color: #94a3b8 !important;
                }

                .transport-option.disabled-transport span {
                    color: #94a3b8 !important;
                }

                .trans-badge-unavailable {
                    font-size: 8.5px;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 0.4px;
                    padding: 2px 6px;
                    border-radius: 6px;
                    margin-top: 5px;
                    background: rgba(239, 68, 68, 0.14);
                    color: #ef4444;
                    border: none !important;
                    outline: none !important;
                    display: inline-block;
                }
            </style>

            <input type="hidden" id="trip-transport" value="">
            <div id="transport-slider"
                style="display:flex; overflow-x:auto; gap:12px; padding-bottom:8px; margin-bottom:16px; scrollbar-width:none; -ms-overflow-style:none;">
                <!-- Populated dynamically based on draft destination municipality and active fare matrices -->
            </div>
        </div>

        <script>
            window.selectTransportMode = function (el) {
                if (el.getAttribute('data-available') === '0' || el.classList.contains('disabled-transport')) {
                    const vehName = el.querySelector('span')?.textContent;
                    const msg = vehName ? `${vehName} is not available.` : 'This vehicle is not available.';
                    if (typeof showToast === 'function') {
                        showToast(msg);
                    }
                    return;
                }
                const val = el.getAttribute('data-val');
                const privateKeys = ['own_car', 'taxi', 'motorcycle', 'van'];
                const isPrivate = privateKeys.includes(val);

                if (isPrivate) {
                    // Private: single-select only
                    document.querySelectorAll('.transport-option').forEach(opt => {
                        const oVal = opt.getAttribute('data-val');
                        if (privateKeys.includes(oVal)) {
                            opt.classList.remove('active');
                        }
                    });
                    el.classList.add('active');
                    document.getElementById('trip-transport').value = val;
                } else {
                    // Public: multi-select allowed
                    el.classList.toggle('active');
                    const activePublic = [];
                    document.querySelectorAll('.transport-option.active').forEach(opt => {
                        const oVal = opt.getAttribute('data-val');
                        if (!privateKeys.includes(oVal) && opt.getAttribute('data-available') !== '0') {
                            activePublic.push(oVal);
                        }
                    });
                    document.getElementById('trip-transport').value = activePublic.join(',');
                }

                // Show/Hide Fuel details for Own Car
                const fuelPanel = document.getElementById('own-car-fuel-panel');
                if (fuelPanel) {
                    if (val === 'own_car' && el.classList.contains('active')) {
                        fuelPanel.style.maxHeight = '200px';
                        fuelPanel.style.opacity = '1';
                    } else if (isPrivate) {
                        fuelPanel.style.maxHeight = '0';
                        fuelPanel.style.opacity = '0';
                    }
                }

                if (window.calculateModalBudget) window.calculateModalBudget();
            };
        </script>

        <!-- Dynamic Fuel Details Panel for Own Car -->
        <div id="own-car-fuel-panel"
            style="max-height:0; opacity:0; overflow:hidden; transition:all 0.3s cubic-bezier(0.16, 1, 0.3, 1); margin-bottom:12px;">
            <div
                style="background:rgba(56, 189, 248, 0.05); border:1px solid rgba(56, 189, 248, 0.2); border-radius:12px; padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11px; font-weight:700; color:#38bdf8; text-transform:uppercase;"><i
                            class="fa-solid fa-gas-pump" style="margin-right:4px;"></i> Estimated Fuel Cost</span>
                    <span id="fuel-cost-calc" style="font-size:12px; font-weight:700; color:white;">₱0.00</span>
                </div>
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label
                            style="font-size:10px; color:white; font-weight:600; display:block; margin-bottom:4px;">Fuel
                            Price (₱/L)</label>
                        <input type="number" id="fuel-price" value="65" min="1" oninput="window.calculateModalBudget()"
                            style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; font-family:inherit; font-size:14px;">
                    </div>
                    <div style="flex:1;">
                        <label
                            style="font-size:10px; color:white; font-weight:600; display:block; margin-bottom:4px;">Fuel
                            Efficiency (km/L)</label>
                        <input type="number" id="fuel-efficiency" value="12" min="1"
                            oninput="window.calculateModalBudget()"
                            style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; font-family:inherit; font-size:14px;">
                    </div>
                </div>
                <p id="fuel-distance-hint" style="font-size:10px; color:white; margin:8px 0 0 0;">Route distance will be
                    used for calculation.</p>
            </div>
        </div>

        <div style="position:relative; margin-bottom:12px;">
            <span style="position:absolute; left:16px; top:14px; color:#38bdf8; font-weight:800; font-size:15px;">₱</span>
            <input type="tel" id="trip-budget" placeholder="Set a budget (optional)"
                oninput="this.value=this.value.replace(/\D/g,'');if(this.value.length>5)this.value=this.value.slice(0,5);window.calculateModalBudget()"
                style="width:100%; padding:12px 16px 12px 34px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.12); color:#ffffff; -webkit-text-fill-color:#ffffff; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box; box-shadow:none !important;">
        </div>

        <div id="save-budget-details"
            style="display:none; background:rgba(255,255,255,0.05); border:none !important; outline:none !important; padding:16px; border-radius:12px; margin-bottom:24px; box-shadow:none !important;">
            <div style="display:flex; align-items:center; gap:16px;">
                <div id="modal-donut-wrapper"
                    style="position:relative; flex-shrink:0; width:0; margin-right:0; height:60px; overflow:hidden; display:flex; align-items:center; justify-content:center; opacity:0; transform:scale(0.7); transition: width 0.45s cubic-bezier(0.34,1.56,0.64,1), margin-right 0.45s cubic-bezier(0.34,1.56,0.64,1), opacity 0.4s ease, transform 0.45s cubic-bezier(0.34,1.56,0.64,1);">
                    <div class="donut-chart" id="modal-budget-donut"
                        style="position:absolute; left:0; top:0; border-radius:50%; width:60px; height:60px; transform:scaleX(-1);">
                    </div>
                    <span id="modal-donut-pct"
                        style="position:relative; font-size:10px; font-weight:800; color:white; white-space:nowrap;"></span>
                </div>
                <div style="flex:1; display:flex; flex-direction:column; gap:4px;">
                    <div style="display:flex; justify-content:space-between; align-items:baseline;">
                        <span style="font-size:11px; color:white; font-weight:600; text-transform:uppercase;">Estimated
                            Cost</span>
                        <h4 style="margin:0; font-size:16px; color:white; font-weight:800;" id="save-estimated-cost">
                            ₱0.00</h4>
                    </div>
                    <div id="save-budget-remaining-row"
                        style="display:none; justify-content:space-between; align-items:baseline;">
                        <span style="font-size:11px; font-weight:600; text-transform:uppercase;"
                            id="save-budget-remaining-label">Remaining</span>
                        <span style="font-size:13px; font-weight:700;" id="save-budget-remaining-val">—</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:20px;">
            <button class="btn-primary"
                style="flex:1; background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; padding:12px; border-radius:14px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.15) !important; transition:transform 0.15s ease;"
                onclick="closeSaveModal()">Cancel</button>
            <button class="btn-primary"
                style="flex:1; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; color:#ffffff; padding:12px; border-radius:14px; font-weight:800; font-size:14px; box-shadow:0 2px 10px rgba(2, 132, 199, 0.35) !important; cursor:pointer; transition:transform 0.15s ease;"
                onclick="submitItinerary()" id="btn-submit-trip">Save Trip</button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirm-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10, 25, 60, 0.75); backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); z-index:100002 !important; justify-content:center; align-items:center; padding:16px;">
    <div
        style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.99) 100%) !important; backdrop-filter:blur(24px) !important; -webkit-backdrop-filter:blur(24px) !important; border:none !important; outline:none !important; border-radius:24px; padding:26px 22px; width:90%; max-width:360px; box-shadow:none !important; text-align:center; color:#ffffff;">
        <div
            style="width:52px; height:52px; border-radius:50%; background:rgba(245, 158, 11, 0.18); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; border:none !important; outline:none !important; box-shadow:none !important;">
            <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24; font-size:24px;"></i>
        </div>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:19px; font-weight:800; letter-spacing:-0.3px;">Missing Details</h3>
        <p id="confirm-modal-msg"
            style="margin:0 0 24px; color:rgba(255,255,255,0.85); font-size:13.5px; line-height:1.55; font-weight:500;"></p>
        <div style="display:flex; gap:12px;">
            <button type="button" class="btn-primary" id="btn-confirm-cancel"
                style="flex:1; background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; padding:12px; border-radius:14px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:none !important; transition:transform 0.15s ease;">Cancel</button>
            <button type="button" class="btn-primary" id="btn-confirm-ok"
                style="flex:1; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important; border:none !important; outline:none !important; color:#ffffff !important; padding:12px; border-radius:14px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:none !important; transition:transform 0.15s ease;">Save Anyway</button>
        </div>
    </div>
</div>
<script>
    (function () {
        var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

        // Synchronize GPS state immediately or resolve via high-accuracy hardware GPS
        window.myLat = window.myLat || window.currentGPSLat || null;
        window.myLng = window.myLng || window.currentGPSLng || null;

        if (!window.myLat || !window.myLng || window.currentGPSSource !== 'gps') {
            if (typeof window.requestPreciseLocation === 'function') {
                window.requestPreciseLocation(false).then(loc => {
                    if (loc && loc.lat && loc.lng) {
                        window.myLat = loc.lat;
                        window.myLng = loc.lng;
                        window.currentGPSLat = loc.lat;
                        window.currentGPSLng = loc.lng;
                        if (window.renderItinerary) window.renderItinerary();
                    }
                }).catch(() => {
                    if (typeof window.resolveUserLocation === 'function') {
                        window.resolveUserLocation().then(loc => {
                            if (loc) {
                                window.myLat = loc.lat;
                                window.myLng = loc.lng;
                                window.currentGPSLat = loc.lat;
                                window.currentGPSLng = loc.lng;
                                if (window.renderItinerary) window.renderItinerary();
                            }
                        });
                    }
                });
            } else if (typeof window.resolveUserLocation === 'function') {
                window.resolveUserLocation().then(loc => {
                    if (loc) {
                        window.myLat = loc.lat;
                        window.myLng = loc.lng;
                        window.currentGPSLat = loc.lat;
                        window.currentGPSLng = loc.lng;
                        if (window.renderItinerary) window.renderItinerary();
                    }
                });
            }
        }

        // Helper to calculate distance and estimated driving travel time
        window.getDistanceAndETA = function (destLat, destLng) {
            const curLat = window.myLat || window.currentGPSLat;
            const curLng = window.myLng || window.currentGPSLng;
            if (!curLat || !curLng || !destLat || !destLng) return null;

            const lat1 = parseFloat(curLat);
            const lon1 = parseFloat(curLng);
            const lat2 = parseFloat(destLat);
            const lon2 = parseFloat(destLng);
            if (isNaN(lat1) || isNaN(lon1) || isNaN(lat2) || isNaN(lon2)) return null;

            const p = 0.017453292519943295;
            const c = Math.cos;
            const a = 0.5 - c((lat2 - lat1) * p) / 2 + c(lat1 * p) * c(lat2 * p) * (1 - c((lon2 - lon1) * p)) / 2;
            const distKm = 12742 * Math.asin(Math.sqrt(a));

            // Average realistic province speed ~30 km/h with traffic
            let durationMin = Math.round((distKm / 30) * 60);
            if (durationMin < 1) durationMin = 1;

            return {
                distanceKm: distKm,
                distanceText: distKm < 1 ? Math.round(distKm * 1000) + ' m' : distKm.toFixed(1) + ' km',
                durationMin: durationMin,
                durationText: durationMin >= 60 ? `${Math.floor(durationMin / 60)}h ${durationMin % 60}m` : `${durationMin} mins`
            };
        };

        // Helper to calculate travel distance and time between two consecutive itinerary stops
        window.calculateLegETA = function (lat1, lon1, lat2, lon2) {
            if (!lat1 || !lon1 || !lat2 || !lon2) return null;
            const l1 = parseFloat(lat1), o1 = parseFloat(lon1);
            const l2 = parseFloat(lat2), o2 = parseFloat(lon2);
            if (isNaN(l1) || isNaN(o1) || isNaN(l2) || isNaN(o2)) return null;

            const p = 0.017453292519943295;
            const c = Math.cos;
            const a = 0.5 - c((l2 - l1) * p) / 2 + c(l1 * p) * c(l2 * p) * (1 - c((o2 - o1) * p)) / 2;
            const distKm = 12742 * Math.asin(Math.sqrt(a));

            let durationMin = Math.round((distKm / 30) * 60);
            if (durationMin < 1) durationMin = 1;

            return {
                distanceKm: distKm,
                distanceText: distKm < 1 ? Math.round(distKm * 1000) + ' m' : distKm.toFixed(1) + ' km',
                durationMin: durationMin,
                durationText: durationMin >= 60 ? `${Math.floor(durationMin / 60)}h ${durationMin % 60}m` : `${durationMin} mins`
            };
        };

        // Helper to resolve destination thumbnail URL on Cloudflare R2
        window.getSpotR2Thumbnail = function (place) {
            const r2Base = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev';
            const defaultR2 = `${r2Base}/tourist_spots/spot_6a686f4d0f48b.jpg`;

            if (!place) return defaultR2;

            // 1. Direct R2 URL
            const url = place.photo_url || place.image || place.avatar || '';
            if (typeof url === 'string' && url.includes('r2.dev')) {
                return url;
            }

            // 2. Extract spot_xxx from serve-image or storage path
            const spotMatch = String(url).match(/(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
            if (spotMatch && spotMatch[1]) {
                return `${r2Base}/tourist_spots/${spotMatch[1]}`;
            }

            // 3. Normalized spot name match for prominent destinations on Cloudflare R2
            const nameNorm = (place.name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            if (nameNorm.includes('bacnotanwatchtower')) {
                return `${r2Base}/tourist_spots/spot_bacnotan_watchtower.jpg`;
            }
            if (nameNorm.includes('barorobattlemarker')) {
                return `${r2Base}/tourist_spots/spot_baroro_battle_marker.jpg`;
            }

            // 4. If window.SPOTS_R2_MAP is available
            if (window.SPOTS_R2_MAP && place.id && window.SPOTS_R2_MAP[place.id]) {
                const mapSpot = window.SPOTS_R2_MAP[place.id];
                if (mapSpot.photo_url && mapSpot.photo_url.includes('r2.dev')) {
                    return mapSpot.photo_url;
                }
            }

            // 5. Fallback to main.js getDestImage if it resolves to R2
            if (typeof window.getDestImage === 'function') {
                const resolved = window.getDestImage(place, 300);
                if (resolved && resolved.includes('r2.dev')) {
                    return resolved;
                }
                const resolvedMatch = String(resolved).match(/(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                if (resolvedMatch && resolvedMatch[1]) {
                    return `${r2Base}/tourist_spots/${resolvedMatch[1]}`;
                }
            }

            return defaultR2;
        };

        // ---- Custom confirm modal (replaces native confirm) ----
        window.showConfirmModal = function (msg) {
            // Prevent stacking multiple confirm modals
            var existing = document.getElementById('confirm-modal');
            if (existing && existing.style.display === 'flex') {
                return Promise.resolve(false);
            }
            return new Promise(function (resolve) {
                var modal = document.getElementById('confirm-modal');
                var msgEl = document.getElementById('confirm-modal-msg');
                var btnOk = document.getElementById('btn-confirm-ok');
                var btnCancel = document.getElementById('btn-confirm-cancel');
                if (!modal || !msgEl || !btnOk || !btnCancel) { resolve(true); return; }
                msgEl.textContent = msg;
                modal.style.display = 'flex';
                function cleanup() {
                    modal.style.display = 'none';
                    btnOk.removeEventListener('click', onOk);
                    btnCancel.removeEventListener('click', onCancel);
                    modal.removeEventListener('click', onBackdrop);
                }
                function onOk() { cleanup(); resolve(true); }
                function onCancel() { cleanup(); resolve(false); }
                function onBackdrop(e) { if (e.target === modal) { cleanup(); resolve(false); } }
                btnOk.addEventListener('click', onOk);
                btnCancel.addEventListener('click', onCancel);
                modal.addEventListener('click', onBackdrop);
            });
        };

        // Fetch fare rates and vehicle data from Railway DB
        fetch(backendUrl + '/api/public/fares', {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            window.fareData = d.fares || {};
            window.vehicleData = d.vehicles || [];
            window.vehicleTypes = d.vehicle_types || [];
            window.fuelPrice = d.fuel_price || 65.0;
        }).catch(e => console.error("Fares fetch error:", e));

        window.getFareFromMatrix = function (vehicleType, distanceKm, municipality = null) {
            if (!window.fareData) return null;
            
            const dKm = parseFloat(distanceKm) || 0;
            const normType = (vehicleType || '').toString().toLowerCase().trim();
            
            if (normType === 'own_car' || normType === 'taxi' || normType === 'own car') return null;

            let fareEntry = null;

            if (normType === 'tricycle' || normType === 'trike') {
                if (municipality && window.fareData.by_municipality) {
                    const cleanMuni = municipality.toString().trim().replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '');
                    const muniKey = cleanMuni.toLowerCase().replace(/[^a-z0-9]/g, '_');
                    const muniRaw = cleanMuni.toLowerCase();
                    const muniObj = window.fareData.by_municipality[muniKey] || window.fareData.by_municipality[muniRaw];
                    if (muniObj) {
                        fareEntry = muniObj.tricycle || muniObj.default || muniObj;
                    }
                }
                if (!fareEntry && window.fareData.by_municipality) {
                    const firstMKey = Object.keys(window.fareData.by_municipality)[0];
                    if (firstMKey) {
                        const mObj = window.fareData.by_municipality[firstMKey];
                        fareEntry = mObj ? (mObj.tricycle || mObj.default || mObj) : null;
                    }
                }
                if (!fareEntry) {
                    fareEntry = window.fareData['tricycle'] || null;
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

            // If distance exceeds all matrix steps, calculate from highest bracket
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

        window.currentRouteType = window.currentRouteType || 'recommended';

        window.animateTimelineSwap = function (renderFn) {
            const existingCards = document.querySelectorAll('.timeline-item[data-key]');
            if (existingCards.length < 2) {
                if (typeof renderFn === 'function') renderFn();
                return;
            }

            // 1. FIRST: Measure current screen positions of all existing cards
            const firstRects = new Map();
            existingCards.forEach(card => {
                const key = card.dataset.key;
                firstRects.set(key, card.getBoundingClientRect());
            });

            // 2. Perform DOM re-render
            if (typeof renderFn === 'function') renderFn();

            // 3. INVERT: Must be SYNCHRONOUS in the same event-loop turn so the browser
            // NEVER paints the swapped DOM at translateY(0) before inverting (eliminating twitch/flicker)
            const newCards = document.querySelectorAll('.timeline-item[data-key]');
            const toAnimate = [];

            newCards.forEach(card => {
                // Disable CSS keyframe animations to prevent transform/opacity conflict
                card.style.animation = 'none';
                card.style.transition = 'none';

                const key = card.dataset.key;
                const firstRect = firstRects.get(key);
                if (firstRect) {
                    const lastRect = card.getBoundingClientRect();
                    const deltaY = firstRect.top - lastRect.top;
                    if (Math.abs(deltaY) > 1) {
                        card.style.transform = `translate3d(0, ${deltaY}px, 0)`;
                        card.style.zIndex = '10';
                        toAnimate.push(card);
                    }
                }
            });

            if (toAnimate.length === 0) return;

            // Force synchronous layout reflow so the browser registers the inverted positions before the next paint
            void document.body.offsetHeight;

            // 4. PLAY: Animate smoothly to natural position (translateY 0) on the very next frame
            requestAnimationFrame(() => {
                toAnimate.forEach(card => {
                    card.style.transition = 'transform 0.42s cubic-bezier(0.2, 0.85, 0.25, 1)';
                    card.style.transform = 'translate3d(0, 0, 0)';
                });

                setTimeout(() => {
                    toAnimate.forEach(card => {
                        card.style.transition = '';
                        card.style.transform = '';
                        card.style.zIndex = '';
                        card.style.animation = '';
                    });
                }, 450);
            });
        };

        window.swapDraftStops = function (i, j) {
            let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            if (i < 0 || j < 0 || i >= draft.length || j >= draft.length || i === j) return;

            window.animateTimelineSwap(() => {
                const temp = draft[i];
                draft[i] = draft[j];
                draft[j] = temp;
                localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
                window.currentRouteType = 'recommended';

                const container = document.getElementById('route-toggle-container');
                if (container) container.classList.remove('alt-active');
                const recBtn = document.getElementById('btn-route-rec');
                const altBtn = document.getElementById('btn-route-alt');
                if (recBtn) recBtn.classList.add('active');
                if (altBtn) altBtn.classList.remove('active');

                // Render stops with skipMap = true so map doesn't re-init mid-animation
                window.renderItinerary(true);

                // Seamlessly update map polyline without jarring viewport resets
                if (typeof draftMap !== 'undefined' && draftMap && window.initDraftMap) {
                    var _savedCenter = draftMap.getCenter();
                    var _savedZoom = draftMap.getZoom();
                    window.initDraftMap(draft, false);
                    if (_savedCenter && _savedZoom) {
                        draftMap.setView(_savedCenter, _savedZoom, { animate: false });
                    }
                }
            });

            if (typeof showToast === 'function') {
                showToast("Stops swapped! Route updated.");
            }
        };

        window.getEffectiveDraft = function () {
            let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            if (!draft || draft.length <= 1) return draft || [];

            const isAlt = (window.currentRouteType === 'alternative' || window.currentRouteType === 'alternate');
            if (!isAlt) return draft;

            if (draft.length === 2) {
                return [...draft].reverse();
            }

            // For 3+ spots: Compute distinct Alternative Exploration Sequence
            // (Furthest-Anchor First: start with the farthest destination anchor, then explore intermediate spots on return)
            const startLat = window.myLat || parseFloat(draft[0].lat || draft[0].latitude || 0);
            const startLng = window.myLng || parseFloat(draft[0].lng || draft[0].longitude || 0);

            const calcDist = (lat1, lon1, lat2, lon2) => {
                const p = 0.017453292519943295;
                const c = Math.cos;
                const a = 0.5 - c((lat2 - lat1) * p)/2 + c(lat1 * p) * c(lat2 * p) * (1 - c((lon2 - lon1) * p))/2;
                return 12742 * Math.asin(Math.sqrt(a));
            };

            const scoredDraft = draft.map((item, idx) => {
                const lat = parseFloat(item.lat || item.latitude || 0);
                const lng = parseFloat(item.lng || item.longitude || 0);
                const d = (startLat && startLng) ? calcDist(startLat, startLng, lat, lng) : idx;
                return { item, d, originalIdx: idx };
            });

            scoredDraft.sort((a, b) => b.d - a.d);
            const anchor = scoredDraft[0];
            const remaining = scoredDraft.slice(1);

            const altSequence = [anchor.item];
            let currLat = parseFloat(anchor.item.lat || anchor.item.latitude || 0);
            let currLng = parseFloat(anchor.item.lng || anchor.item.longitude || 0);

            while (remaining.length > 0) {
                let nearestIdx = 0;
                let nearestDist = Infinity;
                for (let i = 0; i < remaining.length; i++) {
                    const rLat = parseFloat(remaining[i].item.lat || remaining[i].item.latitude || 0);
                    const rLng = parseFloat(remaining[i].item.lng || remaining[i].item.longitude || 0);
                    const dist = calcDist(currLat, currLng, rLat, rLng);
                    if (dist < nearestDist) {
                        nearestDist = dist;
                        nearestIdx = i;
                    }
                }
                const nextItem = remaining.splice(nearestIdx, 1)[0];
                altSequence.push(nextItem.item);
                currLat = parseFloat(nextItem.item.lat || nextItem.item.latitude || 0);
                currLng = parseFloat(nextItem.item.lng || nextItem.item.longitude || 0);
            }

            return altSequence;
        };

        window.renderItinerary = function (skipMap = false) {
            const draft = window.getEffectiveDraft();
            const rawDraft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            const timeline = document.getElementById('itinerary-timeline');
            const emptyState = document.getElementById('itinerary-empty-state');
            const fab = document.getElementById('btn-save-itinerary');
            const mapWrapper = document.getElementById('draft-map-wrapper');
            const draftPlanCard = document.getElementById('draft-plan-card-wrapper');

            document.getElementById('itinerary-count').innerText = rawDraft.length;

            if (rawDraft.length === 0) {
                if (draftPlanCard) draftPlanCard.style.setProperty('display', 'none', 'important');
                const pageTitleEl = document.getElementById('itinerary-page-title');
                const editBannerEl = document.getElementById('editing-plan-banner');
                if (editBannerEl) editBannerEl.style.setProperty('display', 'none', 'important');
                if (pageTitleEl) pageTitleEl.textContent = 'Draft Plan';
                sessionStorage.removeItem('editing_itinerary_id');
                sessionStorage.removeItem('editing_trip_title');
                sessionStorage.removeItem('editing_trip_date');
                sessionStorage.removeItem('editing_trip_budget');
                sessionStorage.removeItem('editing_trip_transport');
                timeline.innerHTML = '';
                emptyState.style.setProperty('display', 'flex', 'important');
                emptyState.classList.remove('is-hidden');
                emptyState.style.animation = 'none';
                void emptyState.offsetHeight; // Trigger reflow for smooth re-animation
                emptyState.style.animation = 'cardFadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                fab.style.setProperty('display', 'none', 'important');
                if (mapWrapper) mapWrapper.style.display = 'none';
                return;
            }

            emptyState.style.setProperty('display', 'none', 'important');
            emptyState.classList.add('is-hidden');
            if (draftPlanCard) draftPlanCard.style.setProperty('display', 'flex', 'important');
            fab.style.setProperty('display', 'flex', 'important');
            const isEditingPlan = Boolean(sessionStorage.getItem('editing_itinerary_id'));
            const pageTitleEl = document.getElementById('itinerary-page-title');
            const editBannerEl = document.getElementById('editing-plan-banner');
            const editBannerTitleEl = document.getElementById('editing-banner-title');
            const savedTitle = sessionStorage.getItem('editing_trip_title') || '';

            if (isEditingPlan) {
                if (pageTitleEl) pageTitleEl.textContent = 'Edit Saved Trip';
                if (editBannerEl) {
                    editBannerEl.style.display = 'flex';
                    if (editBannerTitleEl) editBannerTitleEl.textContent = savedTitle || 'Saved Trip';
                }
                fab.innerHTML = '<i class="fa-solid fa-pen-to-square" style="margin-right:8px;"></i> Update Saved Trip';
            } else {
                if (pageTitleEl) pageTitleEl.textContent = 'Draft Plan';
                if (editBannerEl) editBannerEl.style.display = 'none';
                fab.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:8px;"></i> Save Draft Plan';
            }
            if (mapWrapper) mapWrapper.style.display = 'block';

            // Sync active class on route toggle buttons
            const recBtn = document.getElementById('btn-route-rec');
            const altBtn = document.getElementById('btn-route-alt');
            const toggleContainer = document.getElementById('route-toggle-container');
            const toggleSlider = document.getElementById('route-toggle-slider');
            if (recBtn && altBtn) {
                const isAlt = (window.currentRouteType === 'alternative' || window.currentRouteType === 'alternate');
                if (isAlt) {
                    recBtn.classList.remove('active');
                    altBtn.classList.add('active');
                } else {
                    altBtn.classList.remove('active');
                    recBtn.classList.add('active');
                }
                if (toggleContainer) {
                    if (isAlt) toggleContainer.classList.add('alt-active');
                    else toggleContainer.classList.remove('alt-active');
                }
            }

            let html = '';

            // Render Starting Point (Your Location) Card at the top of the timeline
            const hasGPS = (window.myLat && window.myLng);
            const startingStatus = hasGPS
                ? `<i class="fa-solid fa-circle-dot" style="color:#10b981; font-size:9px;"></i> Real-time GPS Locked`
                : `<i class="fa-solid fa-spinner fa-spin" style="color:#f59e0b; font-size:9px;"></i> Acquiring GPS position...`;

            let startingLegHtml = '';
            if (draft.length > 0) {
                const firstStop = draft[0];
                const firstLat = firstStop.lat || firstStop.latitude;
                const firstLng = firstStop.lng || firstStop.longitude;
                const startEta = window.getDistanceAndETA(firstLat, firstLng);

                const startLegText = startEta
                    ? `${startEta.distanceText} to Stop 1 &bull; ~${startEta.durationText} drive`
                    : `Route to Stop 1 &bull; ${firstStop.name}`;

                startingLegHtml = `
            <div class="stops-swap-divider starting-leg-divider">
                <div class="stops-swap-line"></div>
                <div class="stops-leg-wrapper" style="display:flex; align-items:center; gap:8px; z-index:3;">
                    <div class="stops-leg-chip">
                        <i class="fa-solid fa-car-side" style="color:#00f2fe; font-size:10px;"></i>
                        <span>${startLegText}</span>
                    </div>
                    <div class="starting-leg-icon-pill" title="Start of Itinerary Route">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                </div>
            </div>`;
            }

            html += `<div class="itinerary-stops-container" id="itinerary-stops-container">`;

            html += `
        <div class="starting-point-item" onclick="window.routeToMyLocation()">
            <div class="starting-point-card" style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border:none !important; outline:none !important; border-radius:20px !important; padding:16px 18px !important; box-shadow:none !important; display:flex; align-items:center; justify-content:space-between; gap:14px; cursor:pointer;">
                <div class="starting-point-icon-box" style="width:46px; height:46px; border-radius:50%; background:linear-gradient(135deg, rgba(56,189,248,0.25) 0%, rgba(37,99,235,0.35) 100%) !important; border:none !important; outline:none !important; display:flex; align-items:center; justify-content:center; position:relative; flex-shrink:0; box-shadow:none !important;">
                    <i class="fa-solid fa-location-crosshairs" style="color:#00f2fe; font-size:20px;"></i>
                </div>
                <div class="starting-point-info" style="flex:1; min-width:0;">
                    <div class="starting-point-label" style="font-size:10.5px; font-weight:800; color:#00f2fe; text-transform:uppercase; letter-spacing:0.7px; display:inline-flex; align-items:center; gap:5px; margin-bottom:3px;">
                        <i class="fa-solid fa-satellite-dish" style="font-size:9px;"></i> Starting Point
                    </div>
                    <h3 class="starting-point-title" style="margin:0 0 3px 0; font-size:15.5px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.2px;">Your Current Location</h3>
                    <div class="starting-point-status" id="itinerary-starting-status" style="font-size:11.5px; color:rgba(226,232,240,0.9); display:flex; align-items:center; gap:5px; font-weight:600;">${startingStatus}</div>
                </div>
                <button type="button" onclick="event.stopPropagation(); window.routeToMyLocation();" class="starting-point-locate-btn" style="position:relative !important; width:auto !important; height:auto !important; min-width:auto !important; max-width:none !important; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important; border:none !important; outline:none !important; color:#ffffff !important; font-size:12px; font-weight:800; padding:8px 16px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; flex-shrink:0; box-shadow:none !important; transition:transform 0.15s ease;">
                    <i class="fa-solid fa-location-arrow" style="font-size:11px;"></i> Locate
                </button>
            </div>
        </div>
        ${startingLegHtml}`;

            if (draft.length > 0) {
                draft.forEach((place, index) => {
                    const hour = 9 + Math.floor(((index + 1) * 90) / 60);
                    const min = ((index + 1) * 90) % 60;
                    const timeStr = `${hour > 12 ? hour - 12 : hour}:${min === 0 ? '00' : min} ${hour >= 12 ? 'PM' : 'AM'}`;

                    const isNextStop = (index === 0);
                    let nextStopBadge = '';
                    let nextStopEtaHtml = '';

                    if (isNextStop) {
                        nextStopBadge = `<span class="badge-next-stop" style="border:none !important; outline:none !important; box-shadow:none !important;"><i class="fa-solid fa-location-dot"></i> NEXT STOP</span>`;
                        const lat = place.lat || place.latitude;
                        const lng = place.lng || place.longitude;
                        const eta = window.getDistanceAndETA(lat, lng);
                        if (eta) {
                            nextStopEtaHtml = `
                        <div class="next-stop-distance-chip" id="itinerary-next-eta" style="border:none !important; outline:none !important; box-shadow:none !important;">
                            <i class="fa-solid fa-route" style="color:#00f2fe;"></i> 
                            <span>${eta.distanceText} away &bull; ~${eta.durationText} drive from your location</span>
                        </div>`;
                        } else {
                            nextStopEtaHtml = `
                        <div class="next-stop-distance-chip" id="itinerary-next-eta" style="border:none !important; outline:none !important; box-shadow:none !important;">
                            <i class="fa-solid fa-location-arrow" style="color:#00f2fe;"></i> 
                            <span>First destination on your itinerary route</span>
                        </div>`;
                        }
                    }

                    if (index > 0) {
                        const prevPlace = draft[index - 1];
                        const prevLat = prevPlace.lat || prevPlace.latitude;
                        const prevLng = prevPlace.lng || prevPlace.longitude;
                        const curLat = place.lat || place.latitude;
                        const curLng = place.lng || place.longitude;
                        const legEta = window.calculateLegETA(prevLat, prevLng, curLat, curLng);

                        html += `
                        <div class="stops-swap-divider">
                            <div class="stops-swap-line"></div>
                            <div class="stops-leg-wrapper" style="display:flex; align-items:center; gap:8px; z-index:3;">
                                ${legEta ? `
                                <div class="stops-leg-chip">
                                    <i class="fa-solid fa-car-side" style="color:#00f2fe; font-size:10px;"></i>
                                    <span>${legEta.distanceText} &bull; ~${legEta.durationText} drive</span>
                                </div>` : ''}
                                <button type="button" class="btn-swap-pill" onclick="event.stopPropagation(); window.swapDraftStops(${index - 1}, ${index});" title="Swap Stop ${index} and Stop ${index + 1}" aria-label="Swap order">
                                    <i class="fa-solid fa-arrows-up-down"></i>
                                </button>
                            </div>
                        </div>`;
                    }

                    const placeKey = String(place.id || ('place_' + (place.name || index)));
                    const r2ThumbUrl = window.getSpotR2Thumbnail(place);

                    html += `
                <div class="timeline-item ${isNextStop ? 'is-next-stop' : ''}" draggable="true" data-index="${index}" data-id="${place.id}" data-key="${placeKey}" style="animation-delay: ${(index + 1) * 0.08}s">
                    <div class="timeline-dot"></div>
                    <div class="swipe-container" style="position:relative; overflow:hidden; border-radius:20px !important; transform:translate3d(0,0,0); -webkit-transform:translate3d(0,0,0); -webkit-backface-visibility:hidden; backface-visibility:hidden; -webkit-mask-image:-webkit-radial-gradient(white, black); mask-image:radial-gradient(white, black);">
                        <div class="swipe-delete-bg" style="position:absolute; top:0; right:0; bottom:0; width:80px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius:0 20px 20px 0 !important; display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:800; gap:4px; transform:translateX(100%); z-index:1; opacity:0; pointer-events:none; transition:transform 0.2s ease, opacity 0.2s ease;"><i class="fa-solid fa-trash-can"></i> Delete</div>
                        <div class="swipe-content" style="position:relative; z-index:2; transition:transform 0.2s ease; border-radius:20px !important; padding:16px 18px; border:none !important; outline:none !important; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; box-shadow:none !important;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px;">
                                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                    <span class="time-label" style="border:none !important; outline:none !important; box-shadow:none !important;">Stop ${index + 1} &bull; Approx ${timeStr}</span>
                                    ${nextStopBadge}
                                </div>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-grip-vertical" style="color:rgba(255,255,255,0.45); font-size:16px; cursor:grab; touch-action:none; padding:4px;" title="Drag to reorder"></i>
                                </div>
                            </div>
                            <div style="display:flex; gap:14px; align-items:flex-start;">
                                <div class="stop-thumbnail-wrapper">
                                    <img src="${r2ThumbUrl}" alt="${place.name}" loading="lazy" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/spot_6a686f4d0f48b.jpg';">
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <h3 class="place-name" style="margin:0 0 3px 0; font-size:16px; font-weight:800; color:#ffffff; letter-spacing:-0.2px; line-height:1.25; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${place.name}</h3>
                                    <div style="font-size:12px; color:rgba(255,255,255,0.85); font-weight:600; margin-bottom:4px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                        <div style="display:flex; align-items:center; gap:5px;">
                                            <i class="fa-solid fa-tag" style="color:#38bdf8; font-size:10px;"></i>
                                            <span>${place.category && place.category !== 'null' ? place.category : 'Tourist Destination'}</span>
                                        </div>
                                        ${place.classification_status ? `<span style="padding: 2px 7px; border-radius: 100px; font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${place.classification_status === 'EXIST' ? '#0284c7' : (place.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; border: none !important; outline: none !important;">${place.classification_status === 'EXIST' ? 'EXISTING' : (place.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : ''}
                                    </div>
                                    <div class="place-details" style="font-size:12px; color:rgba(255,255,255,0.75); display:flex; align-items:center; gap:5px;">
                                        <i class="fa-solid fa-location-dot" style="color:#00f2fe; font-size:11px; flex-shrink:0;"></i>
                                        <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            ${place.location && place.location !== 'null' ? place.location : (place.address && place.address !== 'null' ? place.address : (place.municipality ? place.municipality + ', La Union' : 'San Fernando, La Union'))}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            ${nextStopEtaHtml}
                            ${place.selected_vehicles && place.selected_vehicles.length > 0 ? `<div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:8px;">${place.selected_vehicles.map(v => `<span style="padding:2px 8px; border-radius:100px; font-size:10px; font-weight:700; background:rgba(56,189,248,0.15); color:#38bdf8; border:none !important; outline:none !important;"><i class="fa-solid fa-car" style="margin-right:3px;font-size:9px;"></i>${v}</span>`).join('')}</div>` : ''}
                        </div>
                    </div>
                </div>`;
                });
                html += `</div>`; // Close itinerary-stops-container
            }

            timeline.innerHTML = html;
            setupDragAndDrop(draft);

            if (!skipMap) {
                window._renderTimeout = setTimeout(() => {
                    if (window.initDraftMap) window.initDraftMap(draft);
                }, 100);
            }
        };

        function setupDragAndDrop(draft) {
            const items = document.querySelectorAll('.timeline-item[draggable]');
            let dragIndex = null;

            items.forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    dragIndex = parseInt(item.dataset.index);
                    e.dataTransfer.effectAllowed = 'move';
                });
                item.addEventListener('dragend', () => {
                    document.querySelectorAll('.timeline-item').forEach(el => el.style.borderLeft = '');
                });
                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                });
                item.addEventListener('dragenter', (e) => {
                    e.preventDefault();
                    item.style.borderLeft = '3px solid #38bdf8';
                });
                item.addEventListener('dragleave', () => {
                    item.style.borderLeft = '';
                });
                item.addEventListener('drop', (e) => {
                    e.preventDefault();
                    item.style.borderLeft = '';
                    if (dragIndex === null) return;
                    const targetIndex = parseInt(item.dataset.index);
                    if (dragIndex === targetIndex) return;
                    window.swapDraftStops(dragIndex, targetIndex);
                });

                // Touch support
                item.addEventListener('touchstart', (e) => {
                    const grip = e.target.closest('.fa-grip-vertical');
                    if (!grip) return;
                    dragIndex = parseInt(item.dataset.index);
                    const touch = e.touches[0];
                    item._touchStartY = touch.clientY;
                    item._touchMoved = false;
                }, { passive: true });

                item.addEventListener('touchmove', (e) => {
                    const grip = e.target.closest('.fa-grip-vertical');
                    if (!grip) return;
                    e.preventDefault();
                    item._touchMoved = true;
                    const touch = e.touches[0];
                    const siblings = [...document.querySelectorAll('.timeline-item[draggable]')];
                    const target = siblings.find(s => {
                        if (s === item) return false;
                        const rect = s.getBoundingClientRect();
                        return touch.clientY >= rect.top && touch.clientY <= rect.bottom;
                    });
                    siblings.forEach(s => s.style.borderLeft = '');
                    if (target) target.style.borderLeft = '3px solid #38bdf8';
                }, { passive: false });

                item.addEventListener('touchend', (e) => {
                    if (!item._touchMoved || dragIndex === null) return;
                    const touch = e.changedTouches[0];
                    const siblings = [...document.querySelectorAll('.timeline-item[draggable]')];
                    const target = siblings.find(s => {
                        if (s === item) return false;
                        const rect = s.getBoundingClientRect();
                        return touch.clientY >= rect.top && touch.clientY <= rect.bottom;
                    });
                    siblings.forEach(s => s.style.borderLeft = '');
                    if (target) {
                        const targetIndex = parseInt(target.dataset.index);
                        if (dragIndex !== targetIndex) {
                            window.swapDraftStops(dragIndex, targetIndex);
                        }
                    }
                    item._touchMoved = false;
                }, { passive: true });
            });

            setupSwipeToDelete();
        }

        function setupSwipeToDelete() {
            document.querySelectorAll('.swipe-container').forEach(container => {
                const content = container.querySelector('.swipe-content');
                const bg = container.querySelector('.swipe-delete-bg');
                const item = container.closest('.timeline-item');
                if (!content || !item) return;
                let startX = 0, currentX = 0, isSwiping = false;

                content.addEventListener('touchstart', (e) => {
                    if (e.target.closest('.fa-grip-vertical')) return;
                    startX = e.touches[0].clientX;
                    isSwiping = false;
                    content.style.transition = 'none';
                }, { passive: true });

                content.addEventListener('touchmove', (e) => {
                    if (startX === 0) return;
                    currentX = e.touches[0].clientX;
                    let diff = startX - currentX;
                    if (Math.abs(diff) > 5) isSwiping = true;
                    if (diff < 0) diff = 0;
                    const translate = Math.min(diff, 80);
                    content.style.transform = `translateX(-${translate}px)`;
                    content.style.borderRadius = '20px';
                    if (bg) {
                        bg.style.opacity = '1';
                        bg.style.pointerEvents = 'auto';
                        bg.style.transform = `translateX(${80 - translate}px)`;
                    }
                }, { passive: true });

                content.addEventListener('touchend', (e) => {
                    content.style.transition = 'transform 0.2s ease';
                    if (bg) bg.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
                    const diff = startX - currentX;
                    if (diff > 60 && isSwiping) {
                        const id = item.dataset.id;
                        if (id) window.removeItineraryItem(id);
                    } else {
                        content.style.transform = '';
                        content.style.borderRadius = '20px';
                        if (bg) {
                            bg.style.opacity = '0';
                            bg.style.pointerEvents = 'none';
                            bg.style.transform = 'translateX(100%)';
                        }
                    }
                    startX = 0;
                    currentX = 0;
                    isSwiping = false;
                }, { passive: true });

                content.addEventListener('click', (e) => {
                    if (e.target.closest('.fa-grip-vertical')) return;
                    const id = item.dataset.id;
                    if (id) window.routeToPlace(id);
                });
            });
        }

        window.removeItineraryItem = function (id) {
            let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            draft = draft.filter(item => item.id.toString() !== id.toString());
            localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
            window.renderItinerary();
            if (typeof showToast === 'function') showToast("Destination removed from itinerary");
        };

        window.clearAllItinerary = function () {
            localStorage.removeItem('intan_elyu_draft_itinerary');
            window.renderItinerary();
            if (typeof showToast === 'function') showToast("Itinerary cleared");
        };

        window.showMyLocation = function () {
            const wrapper = document.getElementById('add-my-loc-wrapper');
            const container = document.getElementById('my-location-container');
            if (wrapper && container) {
                wrapper.style.display = 'none';
                container.style.display = 'flex';
                setTimeout(() => {
                    container.style.opacity = '1';
                    container.style.transform = 'translateY(0)';
                }, 10);
            }
        };

        window.routeToMyLocation = async function () {
            const mapContainer = document.getElementById('draft-map-wrapper');
            if (mapContainer) {
                if (mapContainer.style.display === 'none' || mapContainer.style.display === '') {
                    mapContainer.style.display = 'block';
                    if (typeof draftMap !== 'undefined' && draftMap) draftMap.invalidateSize();
                }
                mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            if (typeof showToast === 'function') showToast("Acquiring precise GPS location...");
            let loc = null;
            if (typeof window.requestPreciseLocation === 'function') {
                try {
                    loc = await window.requestPreciseLocation(true);
                } catch (e) {
                    console.warn("Precise GPS in itinerary locate:", e);
                }
            }
            if (!loc && typeof window.resolveUserLocation === 'function') {
                loc = await window.resolveUserLocation(true);
            }

            if (loc && loc.lat && loc.lng) {
                window.myLat = loc.lat;
                window.myLng = loc.lng;
                window.currentGPSLat = loc.lat;
                window.currentGPSLng = loc.lng;

                if (typeof draftMap !== 'undefined' && draftMap) {
                    draftMap.flyTo([loc.lat, loc.lng], 16, { animate: true, duration: 1.5 });
                    if (window.myDraftMarker) {
                        window.myDraftMarker.setLatLng([loc.lat, loc.lng]);
                        window.myDraftMarker.openPopup();
                    }
                }
                if (typeof showToast === 'function') {
                    showToast(loc.source === 'gps' || window.currentGPSSource === 'gps' ? "Centered on your precise GPS location 📍" : "Centered on your estimated location");
                }
            }
        };

        // Real-time dynamic GPS listener (Singleton listener to prevent duplicates)
        let _gpsUpdateTimeout = null;
        if (window._itineraryGpsHandler) {
            document.removeEventListener('gpsUpdated', window._itineraryGpsHandler);
        }
        window._itineraryGpsHandler = function (e) {
            const lat = e.detail.lat;
            const lng = e.detail.lng;
            window.myLat = lat;
            window.myLng = lng;
            window.currentGPSLat = lat;
            window.currentGPSLng = lng;

            // Update Starting Point status text live
            const startStatusEl = document.getElementById('itinerary-starting-status');
            if (startStatusEl) {
                startStatusEl.innerHTML = `<i class="fa-solid fa-circle-dot" style="color:#10b981; font-size:9px;"></i> Real-time GPS Locked`;
            }

            // Update Next Stop distance & ETA live in timeline
            const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            if (draft.length > 0) {
                const nextPlace = draft[0];
                const pLat = nextPlace.lat || nextPlace.latitude;
                const pLng = nextPlace.lng || nextPlace.longitude;
                const eta = window.getDistanceAndETA(pLat, pLng);
                const nextEtaEl = document.getElementById('itinerary-next-eta');
                if (nextEtaEl && eta) {
                    nextEtaEl.innerHTML = `<i class="fa-solid fa-route" style="color:#38bdf8;"></i> <span>${eta.distanceText} away &bull; ~${eta.durationText} drive from your location</span>`;
                }
            }

            if (typeof draftMap !== 'undefined' && draftMap) {
                if (window.myDraftMarker && draftMap.hasLayer(window.myDraftMarker)) {
                    // Smoothly animate the marker to the new physical coordinate
                    window.myDraftMarker.setLatLng([lat, lng]);
                } else {
                    // Ensure any old GPS markers are cleanly removed
                    draftMap.eachLayer(layer => {
                        if (layer._isUserGps) {
                            try { draftMap.removeLayer(layer); } catch (err) {}
                        }
                    });

                    // Dynamically create and inject the clean blue GPS location dot
                    const myIconHtml = `
                    <div class="gps-user-marker-icon">
                        <div class="gps-user-marker-wave"></div>
                        <div class="gps-user-marker-inner"></div>
                    </div>
                `;
                    const myIcon = L.divIcon({
                        className: 'custom-leaflet-marker',
                        html: myIconHtml,
                        iconSize: [36, 36],
                        iconAnchor: [18, 18]
                    });
                    window.myDraftMarker = L.marker([lat, lng], { icon: myIcon, zIndexOffset: 1000 })
                        .addTo(draftMap)
                        .bindPopup('<b>📍 Your Current Location</b><br><span style="font-size:11px;color:#64748b;">Starting Point of Itinerary</span>');
                    window.myDraftMarker._isUserGps = true;
                    if (typeof draftMarkers !== 'undefined') draftMarkers.push(window.myDraftMarker);
                }

                // Recalculate the route to connect the path to the new physical GPS location
                clearTimeout(_gpsUpdateTimeout);
                _gpsUpdateTimeout = setTimeout(() => {
                    const currentDraft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                    if (window.initDraftMap) window.initDraftMap(currentDraft, false);
                }, 2000);
            }
        };
        document.addEventListener('gpsUpdated', window._itineraryGpsHandler);

        window.routeToPlace = function (id) {
            const mapContainer = document.getElementById('draft-map-wrapper');
            if (mapContainer) {
                if (mapContainer.style.display === 'none' || mapContainer.style.display === '') {
                    mapContainer.style.display = 'block';
                    if (typeof draftMap !== 'undefined' && draftMap) draftMap.invalidateSize();
                }
                mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

                const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                const place = draft.find(item => item.id.toString() === id.toString());

                if (place && typeof draftMap !== 'undefined' && draftMap) {
                    let lat = place.lat || place.latitude || 16.6159;
                    let lng = place.lng || place.longitude || 120.3167;
                    draftMap.flyTo([parseFloat(lat), parseFloat(lng)], 16, { animate: true, duration: 1.5 });
                }
            }
        };

        // ---- Donut animation state ----
        let _donutAnimFrame = null;
        let _currentDonutPct = 0;

        function animateDonut(targetPct, color) {
            if (_donutAnimFrame) cancelAnimationFrame(_donutAnimFrame);
            const donutEl = document.getElementById('modal-budget-donut');
            const pctEl = document.getElementById('modal-donut-pct');
            const startPct = _currentDonutPct;
            const startTime = performance.now();
            const duration = 700; // ms

            function step(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic
                const ease = 1 - Math.pow(1 - progress, 3);
                const pct = startPct + (targetPct - startPct) * ease;
                _currentDonutPct = pct;

                if (donutEl) {
                    donutEl.style.background = `conic-gradient(
                    ${color} 0% ${pct}%,
                    rgba(255,255,255,0.08) ${pct}% 100%
                )`;
                    donutEl.style.mask = 'radial-gradient(transparent 50%, black 51%)';
                    donutEl.style.webkitMask = 'radial-gradient(transparent 50%, black 51%)';
                }
                if (pctEl) {
                    pctEl.textContent = Math.round(pct) + '%';
                    pctEl.style.color = color;
                }

                if (progress < 1) {
                    _donutAnimFrame = requestAnimationFrame(step);
                }
            }
            _donutAnimFrame = requestAnimationFrame(step);
        };

        window.computeItineraryTransCost = function (draft, transport) {
            if (!draft || draft.length === 0 || !transport) return 0;
            const modes = transport.split(',').map(m => m.trim()).filter(Boolean);
            if (modes.length === 0) return 0;

            const pts = [];
            if (window.myLat && window.myLng) {
                pts.push({ lat: parseFloat(window.myLat), lng: parseFloat(window.myLng), muni: (draft[0]?.municipality || draft[0]?.city || '').trim() });
            }
            draft.forEach(p => {
                const lat = parseFloat(p.lat || p.latitude);
                const lng = parseFloat(p.lng || p.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    pts.push({ lat, lng, muni: (p.municipality || p.city || '').trim() });
                }
            });

            const calcHaversine = (lat1, lon1, lat2, lon2) => {
                const p = 0.017453292519943295;
                const a = 0.5 - Math.cos((lat2 - lat1) * p) / 2 + Math.cos(lat1 * p) * Math.cos(lat2 * p) * (1 - Math.cos((lon2 - lon1) * p)) / 2;
                return (12742 * Math.asin(Math.sqrt(a))) * 1.25;
            };

            const legs = [];
            let totalDist = 0;
            for (let i = 0; i < pts.length - 1; i++) {
                const pA = pts[i];
                const pB = pts[i + 1];
                const d = Math.max(0.5, calcHaversine(pA.lat, pA.lng, pB.lat, pB.lng));
                totalDist += d;
                legs.push({ distKm: d, muniA: pA.muni || pB.muni || '', muniB: pB.muni || pA.muni || '' });
            }

            const distKm = Math.max(window._draftDistanceKm || 0, totalDist, 1);
            if (legs.length === 0) {
                const defMuni = (draft[0]?.municipality || draft[0]?.city || '').trim();
                legs.push({ distKm, muniA: defMuni, muniB: defMuni });
            }

            let totalCost = 0;
            modes.forEach(mode => {
                if (mode === 'own_car') {
                    const fuelPrice = parseFloat(document.getElementById('fuel-price')?.value) || window.fuelPrice || 65;
                    const fuelEffic = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
                    const liters = distKm / fuelEffic;
                    totalCost += Math.ceil(liters * fuelPrice);
                } else if (mode === 'taxi') {
                    totalCost += Math.max(50, Math.round(40 + (distKm * 13)));
                } else if (mode === 'tricycle') {
                    let trikeTotal = 0;
                    legs.forEach(leg => {
                        const mA = leg.muniA.toLowerCase();
                        const mB = leg.muniB.toLowerCase();
                        if (mA && mB && mA !== mB) {
                            // Boundary-crossing leg: cut distance into two municipal segments
                            const dA = leg.distKm / 2;
                            const dB = leg.distKm / 2;
                            const fareA = window.getFareFromMatrix('tricycle', dA, leg.muniA) ?? Math.max(16, Math.round(16.32 + (Math.max(0, dA - 1.7) * 2.0)));
                            const fareB = window.getFareFromMatrix('tricycle', dB, leg.muniB) ?? Math.max(16, Math.round(16.32 + (Math.max(0, dB - 1.7) * 2.0)));
                            trikeTotal += (fareA + fareB);
                        } else {
                            const fare = window.getFareFromMatrix('tricycle', leg.distKm, leg.muniA || leg.muniB) ?? Math.max(16, Math.round(16.32 + (Math.max(0, leg.distKm - 1.7) * 2.0)));
                            trikeTotal += fare;
                        }
                    });
                    totalCost += Math.round(trikeTotal);
                } else if (mode === 'private_bus' || mode === 'bus') {
                    const dbFare = window.getFareFromMatrix('bus', distKm);
                    if (dbFare !== null) totalCost += Math.round(dbFare);
                    else totalCost += Math.max(15, Math.round(15 + (Math.max(0, distKm - 5) * 2.2)));
                } else if (mode === 'jeepney') {
                    const dbFare = window.getFareFromMatrix('jeepney', distKm);
                    if (dbFare !== null) totalCost += Math.round(dbFare);
                    else totalCost += Math.max(13, Math.round(13 + (Math.max(0, distKm - 4) * 1.8)));
                } else if (mode === 'lutrampco') {
                    const dbFare = window.getFareFromMatrix('lutrampco', distKm);
                    if (dbFare !== null) totalCost += Math.round(dbFare);
                    else totalCost += Math.max(14, Math.round(14 + (Math.max(0, distKm - 4) * 2.2)));
                } else if (mode === 'mini_bus' || mode === 'van' || mode === 'uve') {
                    const dbFare = window.getFareFromMatrix('mini_bus', distKm) || window.getFareFromMatrix('van', distKm);
                    if (dbFare !== null) totalCost += Math.round(dbFare);
                    else totalCost += Math.max(25, Math.round(25 + (Math.max(0, distKm - 4) * 2.5)));
                } else {
                    const dbFare = window.getFareFromMatrix(mode, distKm);
                    if (dbFare !== null) totalCost += Math.round(dbFare);
                    else totalCost += 30;
                }
            });

            return totalCost;
        };

        window.calculateModalBudget = function () {
            const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            if (draft.length === 0) return;

            const transport = document.getElementById('trip-transport').value;
            const detailsDiv = document.getElementById('save-budget-details');

            if (!transport) {
                detailsDiv.style.display = 'none';
                detailsDiv.classList.remove('animate-smooth-reveal');
                return;
            }

            const transCost = window.computeItineraryTransCost(draft, transport);

            const distKm = window._draftDistanceKm || 0;
            const hint = document.getElementById('fuel-distance-hint');
            if (hint && distKm > 0) {
                const fuelEffic = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
                hint.textContent = `Route: ${distKm.toFixed(1)} km • ~${(distKm / fuelEffic).toFixed(2)} L needed`;
                hint.style.color = 'rgba(255,255,255,0.5)';
            } else if (hint) {
                hint.textContent = 'Open the Map first to get an accurate route distance.';
                hint.style.color = '#FF9500';
            }

            // Sum Entrance Fees & Environmental Fees across all destinations in draft
            let feesTotal = 0;
            draft.forEach(p => {
                const entrance = parseFloat(p.entrance_fee || p.entranceFee || p.fee || 0);
                const env = parseFloat(p.environmental_fee || p.environmentalFee || p.envFee || 0);
                feesTotal += (isNaN(entrance) ? 0 : entrance) + (isNaN(env) ? 0 : env);
            });

            const estimatedCost = transCost + feesTotal;

            // Show the card
            if (detailsDiv.style.display !== 'block') {
                detailsDiv.style.display = 'block';
                detailsDiv.classList.add('animate-smooth-reveal');
            }

            const costEl = document.getElementById('save-estimated-cost');
            costEl.textContent = '₱' + estimatedCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const budgetInput = document.getElementById('trip-budget').value;
            const budget = parseFloat(budgetInput);
            const remainingRow = document.getElementById('save-budget-remaining-row');
            const remainingLabel = document.getElementById('save-budget-remaining-label');
            const remainingVal = document.getElementById('save-budget-remaining-val');
            const donutWrapper = document.getElementById('modal-donut-wrapper');

            if (!budgetInput || isNaN(budget) || budget <= 0) {
                // No budget — collapse donut out (slides left)
                if (donutWrapper) {
                    donutWrapper.style.opacity = '0';
                    donutWrapper.style.transform = 'scale(0.7)';
                    donutWrapper.style.width = '0';
                    donutWrapper.style.marginRight = '0';
                }
                if (_donutAnimFrame) { cancelAnimationFrame(_donutAnimFrame); _donutAnimFrame = null; }
                _currentDonutPct = 0;
                if (remainingRow) remainingRow.style.display = 'none';
                return;
            }

            // Budget is set — expand donut in (slides right)
            if (donutWrapper) {
                donutWrapper.style.width = '60px'; // 60px donut
                donutWrapper.style.marginRight = '16px'; // 16px gap
                donutWrapper.style.opacity = '1';
                donutWrapper.style.transform = 'scale(1)';
            }

            const percentage = Math.min((budget / estimatedCost) * 100, 100);
            const remaining = budget - estimatedCost;

            let fillColor = '#FF3B30'; // Red — budget covers little
            let remainingLabelText = 'Need more budget';
            let remainingLabelColor = '#FF3B30';

            if (percentage >= 100) {
                fillColor = '#34C759';
                remainingLabelText = "You're good to go!";
                remainingLabelColor = '#34C759';
            } else if (percentage >= 80) {
                fillColor = '#FF9500';
                remainingLabelText = 'Almost there';
                remainingLabelColor = '#FF9500';
            }

            // Animate donut fill smoothly
            animateDonut(percentage, fillColor);

            // Update remaining row
            if (remainingRow) {
                remainingRow.style.display = 'flex';
                remainingLabel.textContent = remainingLabelText;
                remainingLabel.style.color = remainingLabelColor;
                remainingVal.style.color = remainingLabelColor;
                const absRemaining = Math.abs(remaining);
                remainingVal.textContent = (remaining < 0 ? '-' : '') + '₱' + absRemaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        };

        // ---- Custom Dark-Glass Calendar Functions ----
        window.calendarState = {
            currentYear: new Date().getFullYear(),
            currentMonth: new Date().getMonth(),
            selectedDateStr: ''
        };

        window.MONTH_NAMES = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        window.renderCalendarGrid = function () {
            const { currentYear, currentMonth, selectedDateStr } = window.calendarState;
            const titleEl = document.getElementById('calendar-month-year');
            const gridEl = document.getElementById('calendar-days-grid');
            if (!titleEl || !gridEl) return;

            titleEl.textContent = `${window.MONTH_NAMES[currentMonth]} ${currentYear}`;

            const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay();
            const totalDays = new Date(currentYear, currentMonth + 1, 0).getDate();
            const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            let html = '';

            // Leading days from previous month
            for (let i = firstDayIndex - 1; i >= 0; i--) {
                const prevDay = prevMonthDays - i;
                html += `<div style="padding:7px 0; font-size:12px; color:rgba(255,255,255,0.2); pointer-events:none; border-radius:10px;">${prevDay}</div>`;
            }

            // Days of current month
            for (let day = 1; day <= totalDays; day++) {
                const dayStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isSelected = (dayStr === selectedDateStr);
                const isToday = (dayStr === todayStr);

                let bg = 'transparent';
                let color = 'rgba(255,255,255,0.9)';
                let border = 'none';
                let shadow = 'none';
                let weight = '600';

                if (isSelected) {
                    bg = 'linear-gradient(135deg, #00f2fe 0%, #0284c7 100%)';
                    color = '#ffffff';
                    weight = '800';
                    border = 'none';
                    shadow = 'none';
                } else if (isToday) {
                    bg = 'rgba(255, 255, 255, 0.18)';
                    border = 'none';
                    color = '#00f2fe';
                    weight = '700';
                    shadow = 'none';
                }

                html += `
                    <div onclick="window.selectCalendarDate('${dayStr}')" style="padding:7px 0; font-size:13px; font-weight:${weight}; cursor:pointer; border-radius:11px; background:${bg}; color:${color}; border:none !important; outline:none !important; box-shadow:none !important; transition:all 0.15s ease; position:relative; display:flex; align-items:center; justify-content:center;">
                        ${day}
                        ${isToday && !isSelected ? '<span style="position:absolute; bottom:2px; width:4px; height:4px; border-radius:50%; background:#00f2fe;"></span>' : ''}
                    </div>
                `;
            }

            // Trailing days to fill standard calendar rows
            const totalRendered = firstDayIndex + totalDays;
            const remainingCells = (totalRendered <= 35 ? 35 : 42) - totalRendered;
            for (let j = 1; j <= remainingCells; j++) {
                html += `<div style="padding:7px 0; font-size:12px; color:rgba(255,255,255,0.2); pointer-events:none; border-radius:10px;">${j}</div>`;
            }

            gridEl.innerHTML = html;
        };

        window.changeCalendarMonth = function (delta) {
            window.calendarState.currentMonth += delta;
            if (window.calendarState.currentMonth > 11) {
                window.calendarState.currentMonth = 0;
                window.calendarState.currentYear += 1;
            } else if (window.calendarState.currentMonth < 0) {
                window.calendarState.currentMonth = 11;
                window.calendarState.currentYear -= 1;
            }
            window.renderCalendarGrid();
        };

        window.selectCalendarDate = function (dateStr) {
            window.calendarState.selectedDateStr = dateStr;
            const hiddenInput = document.getElementById('trip-date');
            const displayEl = document.getElementById('custom-date-display');
            const clearLink = document.getElementById('calendar-clear-link');

            if (hiddenInput) hiddenInput.value = dateStr;

            if (displayEl && dateStr) {
                const parts = dateStr.split('-');
                const y = parseInt(parts[0]);
                const m = parseInt(parts[1]);
                const d = parseInt(parts[2]);
                const dateObj = new Date(y, m - 1, d);
                const formatted = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                displayEl.textContent = formatted;
                displayEl.style.color = '#ffffff';
                displayEl.style.fontWeight = '700';
            }
            if (clearLink) clearLink.style.display = dateStr ? 'inline' : 'none';

            window.renderCalendarGrid();
            setTimeout(() => {
                window.toggleCustomCalendar(null, false);
            }, 200);
        };

        window.customClearDate = function (e) {
            if (e) e.stopPropagation();
            window.calendarState.selectedDateStr = '';
            const hiddenInput = document.getElementById('trip-date');
            const displayEl = document.getElementById('custom-date-display');
            const clearLink = document.getElementById('calendar-clear-link');
            if (hiddenInput) hiddenInput.value = '';
            if (displayEl) {
                displayEl.textContent = 'Select trip date';
                displayEl.style.color = 'rgba(255,255,255,0.85)';
                displayEl.style.fontWeight = '600';
            }
            if (clearLink) clearLink.style.display = 'none';
            window.renderCalendarGrid();
        };

        window.selectTodayDate = function () {
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
            window.calendarState.currentYear = today.getFullYear();
            window.calendarState.currentMonth = today.getMonth();
            window.selectCalendarDate(todayStr);
        };

        window.toggleCustomCalendar = function (e, forceState) {
            if (e) e.stopPropagation();
            const dropdown = document.getElementById('custom-calendar-dropdown');
            const arrow = document.getElementById('custom-date-arrow');
            const trigger = document.getElementById('custom-date-trigger');
            if (!dropdown) return;

            const isOpen = dropdown.classList.contains('calendar-open');
            const shouldOpen = forceState !== undefined ? forceState : !isOpen;

            if (shouldOpen) {
                window.renderCalendarGrid();
                dropdown.style.display = 'block';
                // Trigger reflow for smooth height slide transition
                dropdown.offsetHeight;
                dropdown.classList.add('calendar-open');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
                if (trigger) trigger.style.background = 'rgba(255, 255, 255, 0.18)';
            } else {
                dropdown.classList.remove('calendar-open');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
                if (trigger) trigger.style.background = 'rgba(255, 255, 255, 0.12)';
                setTimeout(() => {
                    if (!dropdown.classList.contains('calendar-open')) {
                        dropdown.style.display = 'none';
                    }
                }, 380);
            }
        };

        window.resetSaveModalInputs = function () {
            const titleInput = document.getElementById('trip-title');
            if (titleInput) titleInput.value = '';
            window.customClearDate();
            window.toggleCustomCalendar(null, false);
            const transInput = document.getElementById('trip-transport');
            if (transInput) transInput.value = '';
            const budgetInput = document.getElementById('trip-budget');
            if (budgetInput) budgetInput.value = '';
            const details = document.getElementById('save-budget-details');
            if (details) details.style.display = 'none';
            const pctEl = document.getElementById('modal-donut-pct');
            const remainingRow = document.getElementById('save-budget-remaining-row');
            const donutWrapper = document.getElementById('modal-donut-wrapper');
            if (pctEl) pctEl.textContent = '';
            if (remainingRow) remainingRow.style.display = 'none';
            if (donutWrapper) { donutWrapper.style.opacity = '0'; donutWrapper.style.transform = 'scale(0.7)'; donutWrapper.style.width = '0'; donutWrapper.style.marginRight = '0'; }
            if (_donutAnimFrame) { cancelAnimationFrame(_donutAnimFrame); _donutAnimFrame = null; }
            _currentDonutPct = 0;
            document.querySelectorAll('.transport-option').forEach(opt => opt.classList.remove('active'));
            const wrapper = document.getElementById('transport-slider-wrapper');
            if (wrapper) wrapper.style.display = 'none';

            sessionStorage.removeItem('editing_itinerary_id');
            sessionStorage.removeItem('editing_trip_title');
            sessionStorage.removeItem('editing_trip_date');
            sessionStorage.removeItem('editing_trip_budget');
            sessionStorage.removeItem('editing_trip_transport');
        };

        window.cancelEditingSavedTrip = function () {
            sessionStorage.removeItem('editing_itinerary_id');
            sessionStorage.removeItem('editing_trip_title');
            sessionStorage.removeItem('editing_trip_date');
            sessionStorage.removeItem('editing_trip_budget');
            sessionStorage.removeItem('editing_trip_transport');
            if (typeof showToast === 'function') {
                showToast("Exited trip edit mode.");
            }
            window.renderItinerary();
        };

        window.openSaveModal = function () {
            const draft = window.getEffectiveDraft();

            // Check if in edit mode
            const editingId = sessionStorage.getItem('editing_itinerary_id');
            const modalTitleEl = document.getElementById('save-trip-modal-title');
            const submitBtn = document.getElementById('btn-submit-trip');

            if (editingId) {
                if (modalTitleEl) {
                    modalTitleEl.innerHTML = '<i class="fa-solid fa-pen-to-square" style="color:#38bdf8; font-size:18px;"></i> Edit Your Trip';
                }
                if (submitBtn) {
                    submitBtn.textContent = 'Update Trip';
                }
                const savedTitle = sessionStorage.getItem('editing_trip_title');
                const savedDate = sessionStorage.getItem('editing_trip_date');
                const savedBudget = sessionStorage.getItem('editing_trip_budget');
                const savedTransport = sessionStorage.getItem('editing_trip_transport');

                const titleInput = document.getElementById('trip-title');
                if (titleInput && savedTitle !== null && savedTitle !== undefined) {
                    titleInput.value = savedTitle;
                }
                const dateInput = document.getElementById('trip-date');
                if (dateInput) {
                    dateInput.value = savedDate || '';
                    const display = document.getElementById('trip-date-display');
                    if (display) {
                        display.value = savedDate ? new Date(savedDate + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
                    }
                    const clearLink = document.getElementById('calendar-clear-link');
                    if (clearLink) clearLink.style.display = savedDate ? 'block' : 'none';
                }
                const budgetInput = document.getElementById('trip-budget');
                if (budgetInput) {
                    budgetInput.value = (savedBudget !== null && savedBudget !== undefined && savedBudget !== '') ? savedBudget : '';
                }
                if (savedTransport) {
                    const transEl = document.getElementById('trip-transport');
                    if (transEl) transEl.value = savedTransport;
                }
            } else {
                if (modalTitleEl) {
                    modalTitleEl.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="color:#38bdf8; font-size:18px;"></i> Save Your Trip';
                }
                if (submitBtn) {
                    submitBtn.textContent = 'Save Trip';
                }
            }

            // Populate dynamic fuel price from Railway DB
            const fuelInput = document.getElementById('fuel-price');
            if (fuelInput && window.fuelPrice) {
                fuelInput.value = window.fuelPrice;
            }

            // Auto-detect transport type from existing trip-transport or draft
            let existingTransport = document.getElementById('trip-transport').value;
            let currentTransType = 'public';
            if (existingTransport) {
                const parts = existingTransport.split(',').filter(Boolean);
                const privateSet = ['own_car', 'taxi', 'van', 'motorcycle'];
                if (parts.some(p => privateSet.includes(p))) {
                    currentTransType = 'private';
                }
            } else {
                const veh = draft.find(p => p.transport_type);
                currentTransType = veh ? veh.transport_type : 'public';
            }
            window.setTransportType(currentTransType, false);

            // Manage active vehicle cards in the slider (only select available options)
            const activeVehicles = existingTransport ? existingTransport.split(',').filter(Boolean) : [...new Set(draft.flatMap(p => p.selected_vehicles || []).filter(Boolean))];
            if (activeVehicles.length > 0) {
                document.querySelectorAll('.transport-option').forEach(opt => {
                    if (activeVehicles.includes(opt.dataset.val) && !opt.classList.contains('disabled-transport')) {
                        opt.classList.add('active');
                    } else {
                        opt.classList.remove('active');
                    }
                });
                const validActive = [];
                document.querySelectorAll('.transport-option.active').forEach(opt => {
                    if (opt.getAttribute('data-available') !== '0') {
                        validActive.push(opt.dataset.val);
                    }
                });
                document.getElementById('trip-transport').value = validActive.join(',');
            } else {
                const defaultOpt = document.querySelector('.transport-option:not(.disabled-transport)') || document.querySelector('.transport-option');
                if (defaultOpt && defaultOpt.getAttribute('data-available') !== '0') {
                    defaultOpt.classList.add('active');
                    document.getElementById('trip-transport').value = defaultOpt.dataset.val;
                }
            }

            // Initialize calendar to current month
            const today = new Date();
            window.calendarState.currentYear = today.getFullYear();
            window.calendarState.currentMonth = today.getMonth();
            window.renderCalendarGrid();

            document.getElementById('save-trip-modal').style.display = 'flex';
            window.calculateModalBudget();

            // Hide bottom nav while modal is open
            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.add('nav-hidden');
        };

        window.closeSaveModal = function () {
            document.getElementById('save-trip-modal').style.display = 'none';

            // Restore bottom nav
            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.remove('nav-hidden');

            // Close calendar dropdown without clearing date or input values
            window.toggleCustomCalendar(null, false);
            // All draft inputs (#trip-title, #trip-date, #trip-budget, #trip-transport) remain intact on cancel!
        };

        window.submitItinerary = async function () {
            const title = document.getElementById('trip-title').value.trim();
            const date = document.getElementById('trip-date').value;
            const budgetStr = document.getElementById('trip-budget').value;
            const budget = budgetStr ? parseFloat(budgetStr) : null;
            if (!title) return showToast("Please enter a trip name");

            const draft = window.getEffectiveDraft();
            if (draft.length === 0) return showToast("Your itinerary is empty!");

            const transport = document.getElementById('trip-transport').value;

            // Warn if no transport or budget is set — confirm before saving
            if (!transport || !budgetStr) {
                let msg = "You haven't set ";
                const missing = [];
                if (!transport) missing.push('a transport mode');
                if (!budgetStr) missing.push('a budget');
                msg += missing.join(' or ');
                msg += '. Do you want to save anyway?';
                if (!(await window.showConfirmModal(msg))) return;
            }

            const btn = document.getElementById('btn-submit-trip');
            const editingId = sessionStorage.getItem('editing_itinerary_id');
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${editingId ? 'Updating...' : 'Saving...'}`;
            btn.disabled = true;

            const destinations = draft.map(place => parseInt(place.id || place.tourist_spot_id || 0)).filter(id => id > 0);

            try {
                const activeRouteType = document.querySelector('.btn-route-type.active')?.innerText || ((window.currentRouteType === 'alternative' || window.currentRouteType === 'alternate') ? 'Alternative' : 'Recommended');
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                if (!token) {
                    btn.innerHTML = editingId ? 'Update Trip' : 'Save Trip';
                    btn.disabled = false;
                    showToast("Session expired. Please log in to save your trip.");
                    navigateTo('auth');
                    return;
                }

                const url = editingId ? `${backendUrl}/api/tourist/itineraries/${editingId}` : `${backendUrl}/api/tourist/itineraries`;
                const method = editingId ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        title: title,
                        trip_date: date || null,
                        budget: budget,
                        destinations: destinations,
                        route_type: activeRouteType,
                        transport_mode: transport
                    })
                });

                if (response.status === 401) {
                    localStorage.removeItem('intan_elyu_token');
                    localStorage.removeItem('Intan_Elyu_Token');
                    showToast("Session expired. Please log in again.");
                    navigateTo('auth');
                    return;
                }

                const data = await response.json();

                if (response.ok) {
                    // Invalidate caches
                    const cacheKey = 'saved_trips_' + token.substring(0, 10);
                    const dashCacheKey = 'dashboard_trips_' + token.substring(0, 10);
                    localStorage.removeItem(dashCacheKey);
                    localStorage.removeItem(cacheKey);

                    const savedId = String(editingId || data.itinerary_id || (data.itinerary && data.itinerary.id) || '');
                    if (savedId) {
                        sessionStorage.setItem('just_saved_trip_id', savedId);
                        if (transport) {
                            sessionStorage.setItem('active_trip_transport_' + savedId, transport);
                            localStorage.setItem('selected_trip_vehicle_' + savedId, transport);
                        }
                    }

                    showToast(editingId ? "Trip updated successfully!" : "Trip saved successfully!");
                    localStorage.removeItem('intan_elyu_draft_itinerary');
                    window.resetSaveModalInputs();
                    document.getElementById('save-trip-modal').style.display = 'none';
                    const bottomNav = document.getElementById('bottom-navigation');
                    if (bottomNav) bottomNav.classList.remove('nav-hidden');
                    window.renderItinerary();
                    navigateTo('saved_trips');
                } else {
                    throw new Error(data.message || (editingId ? "Failed to update trip" : "Failed to save trip"));
                }
            } catch (error) {
                console.error("Save Error:", error);
                showToast(error.message || "Failed to save. Check connection.");
            } finally {
                btn.innerHTML = editingId ? 'Update Trip' : 'Save Trip';
                btn.disabled = false;
            }
        };

        // Render immediately on view load
        window.renderItinerary();

        // ==========================================
        // MAP & DONUT CHART LOGIC
        // ==========================================

        let draftMap = null;
        let draftRouteLineBg = null;
        let draftRouteLine = null;
        let draftMarkers = [];

        window.initDraftMap = function (draft, shouldFitBounds = true) {
            if (draft.length === 0) return;

            // Cancel any pending render timeout to prevent stale fitBounds calls
            if (window._renderTimeout) {
                clearTimeout(window._renderTimeout);
                window._renderTimeout = null;
            }

            if (!draftMap) {
                draftMap = L.map('itinerary-map', {
                    attributionControl: false,
                    zoomControl: false,
                    scrollWheelZoom: true,
                    dragging: true,
                    touchZoom: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true
                });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    subdomains: ['a', 'b', 'c'],
                    detectRetina: true,
                    keepBuffer: 4,
                    updateWhenZooming: true,
                    updateWhenIdle: false
                }).addTo(draftMap);
            }

            // Force Leaflet to recalculate size since container was display:none.
            // Use double rAF so browser has fully laid out the container before we measure it.
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    draftMap.invalidateSize();
                });
            });

            // Clear ALL previous markers, overlays, and routes from draftMap (leaving only base tiles)
            if (draftMap) {
                draftMap.eachLayer(layer => {
                    if (!(layer instanceof L.TileLayer)) {
                        try { draftMap.removeLayer(layer); } catch (e) {}
                    }
                });
            }
            draftRouteLineBg = null;
            draftRouteLine = null;
            window.myDraftMarker = null;
            draftMarkers = [];

            let latlngs = [];

            // Add single GPS Location indicator
            if (window.myLat && window.myLng) {
                latlngs.push([window.myLat, window.myLng]);
                const myIconHtml = `
                <div class="gps-user-marker-icon">
                    <div class="gps-user-marker-wave"></div>
                    <div class="gps-user-marker-inner"></div>
                </div>
            `;
                const myIcon = L.divIcon({
                    className: 'custom-leaflet-marker',
                    html: myIconHtml,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });
                window.myDraftMarker = L.marker([window.myLat, window.myLng], { icon: myIcon, zIndexOffset: 1000 })
                    .addTo(draftMap)
                    .bindPopup('<b>📍 Your Current Location</b><br><span style="font-size:11px;color:#64748b;">Starting Point of Itinerary</span>');
                window.myDraftMarker._isUserGps = true;
                draftMarkers.push(window.myDraftMarker);
            }

            draft.forEach((place, index) => {
                // Skip place if it has no valid coordinates in the database
                const pLat = place.lat || place.latitude;
                const pLng = place.lng || place.longitude;
                if (!pLat || !pLng) return;

                let lat = parseFloat(pLat);
                let lng = parseFloat(pLng);

                const ll = [lat, lng];
                latlngs.push(ll);

                let stopIconHtml = '';
                if (index === 0) {
                    // Next Stop prominent visual pin
                    stopIconHtml = `
                    <div class="next-stop-marker-inner" style="cursor: pointer;" onmouseenter="this.style.transform='scale(1.2)'" onmouseleave="this.style.transform='scale(1)'" title="Next Stop: ${place.name}">
                        <i class="fa-solid fa-flag" style="font-size:13px;"></i>
                    </div>
                `;
                } else {
                    stopIconHtml = `
                    <div style="width: 32px; height: 32px; background-color: #FFFFFF; border: 2px solid #38bdf8; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #38bdf8; box-shadow: 0 4px 8px rgba(0,0,0,0.15); cursor: pointer; transition: transform 0.2s;" onmouseenter="this.style.transform='scale(1.2)'" onmouseleave="this.style.transform='scale(1)'">
                        <span style="font-size:14px; font-weight:800;">${index + 1}</span>
                    </div>
                `;
                }

                const stopIcon = L.divIcon({
                    className: 'custom-leaflet-marker',
                    html: stopIconHtml,
                    iconSize: index === 0 ? [36, 36] : [32, 32],
                    iconAnchor: index === 0 ? [18, 18] : [16, 16]
                });
                const marker = L.marker(ll, { icon: stopIcon })
                    .addTo(draftMap)
                    .bindPopup(`<b>${index === 0 ? '🚀 NEXT STOP: ' : 'Stop ' + (index + 1) + ': '}${place.name}</b><br><span style="font-size:11px; color:#64748b;">${place.location || place.address || (place.municipality ? place.municipality + ', La Union' : 'La Union')}</span>`);
                draftMarkers.push(marker);
            });

            if (latlngs.length > 1) {
                const activeRouteEl = document.querySelector('.btn-route-type.active');
                const isAlt = (window.currentRouteType === 'alternative' || window.currentRouteType === 'alternate' || activeRouteEl?.innerText.trim() === 'Alternative' || activeRouteEl?.innerText.trim() === 'Alternate');
                const activeRoute = isAlt ? 'Alternative' : 'Recommended';
                
                let routeColor = isAlt ? '#f59e0b' : '#38bdf8'; // Alternative = Vibrant Amber/Gold, Recommended = Cyan/Blue
                let shadowColor = isAlt ? '#78350f' : '#0f172a';

                let fetchLatLngs = [...latlngs];
                const coordString = fetchLatLngs.map(ll => `${ll[1]},${ll[0]}`).join(';');

                if (shouldFitBounds) {
                    draftMap.fitBounds(L.latLngBounds(latlngs), { padding: [30, 30] });
                }

                // If Alternative and multiple stops: query leg-by-leg alternative corridors or multi-point route
                const fetchRoutePromise = (async () => {
                    if (isAlt && fetchLatLngs.length >= 2) {
                        // Fetch leg by leg with alternative option for each leg
                        try {
                            const legGeometries = [];
                            let totalDist = 0;
                            let totalDur = 0;

                            for (let k = 0; k < fetchLatLngs.length - 1; k++) {
                                const p1 = `${fetchLatLngs[k][1]},${fetchLatLngs[k][0]}`;
                                const p2 = `${fetchLatLngs[k+1][1]},${fetchLatLngs[k+1][0]}`;
                                const legUrl = `https://router.project-osrm.org/route/v1/driving/${p1};${p2}?overview=full&geometries=geojson&alternatives=3&continue_straight=true`;
                                const legRes = await fetch(legUrl);
                                const legData = await legRes.json();

                                if (legData.code === 'Ok' && legData.routes && legData.routes.length > 0) {
                                    // Choose genuine secondary route if returned by OSRM road network, otherwise follow direct road
                                    const chosen = (legData.routes.length > 1) ? legData.routes[1] : legData.routes[0];
                                    legGeometries.push(chosen.geometry);
                                    totalDist += chosen.distance;
                                    totalDur += chosen.duration;
                                }
                            }

                            if (legGeometries.length > 0) {
                                return {
                                    code: 'Ok',
                                    distance: totalDist,
                                    duration: totalDur,
                                    geometry: {
                                        type: 'FeatureCollection',
                                        features: legGeometries.map(g => ({
                                            type: 'Feature',
                                            geometry: g
                                        }))
                                    }
                                };
                            }
                        } catch (e) {
                            console.warn("Alternative leg query fallback:", e);
                        }
                    }

                    // Standard OSRM query
                    let osrmService = 'route';
                    let osrmQuery = '?overview=full&geometries=geojson&alternatives=true';
                    if (!isAlt && fetchLatLngs.length >= 3) {
                        osrmService = 'trip';
                        osrmQuery = '?overview=full&geometries=geojson&source=first&destination=last&roundtrip=false';
                    }

                    const osrmUrl = `https://router.project-osrm.org/${osrmService}/v1/driving/${coordString}${osrmQuery}`;
                    const res = await fetch(osrmUrl);
                    const data = await res.json();
                    
                    if (data.code === 'Ok') {
                        let chosenRoute = null;
                        if (isAlt && data.routes && data.routes.length > 1) {
                            chosenRoute = data.routes[1];
                        } else {
                            chosenRoute = data.routes ? data.routes[0] : (data.trips ? data.trips[0] : null);
                        }
                        if (chosenRoute) {
                            return {
                                code: 'Ok',
                                distance: chosenRoute.distance,
                                duration: chosenRoute.duration,
                                geometry: chosenRoute.geometry
                            };
                        }
                    }
                    return null;
                })();

                fetchRoutePromise.then(routeData => {
                    if (routeData && routeData.code === 'Ok') {
                        if (draftRouteLineBg) draftMap.removeLayer(draftRouteLineBg);
                        if (draftRouteLine) draftMap.removeLayer(draftRouteLine);

                        const geojson = routeData.geometry;

                        draftRouteLineBg = L.geoJSON(geojson, {
                            style: { color: shadowColor, weight: 7, opacity: 0.4, lineJoin: 'round', lineCap: 'round' }
                        }).addTo(draftMap);

                        draftRouteLine = L.geoJSON(geojson, {
                            style: {
                                color: routeColor,
                                weight: 4.5,
                                opacity: 1,
                                lineJoin: 'round',
                                lineCap: 'round',
                                dashArray: isAlt ? '8, 6' : null
                            }
                        }).addTo(draftMap);

                        let distanceKm = routeData.distance / 1000;
                        let durationMin = routeData.duration / 60;

                        if (isAlt) {
                            durationMin *= 1.25;
                            distanceKm *= 1.12;
                        }

                        let baseMultiplier = 1.6;
                        if (distanceKm <= 3) baseMultiplier = 2.5;
                        else if (distanceKm <= 7) baseMultiplier = 2.0;
                        durationMin *= baseMultiplier;

                        // Traffic Buffer Logic
                        const currentHour = new Date().getHours();
                        const isRushHour = (currentHour >= 7 && currentHour <= 9) || (currentHour >= 16 && currentHour <= 19);
                        const warningDiv = document.getElementById('draft-traffic-warning');

                        if (isRushHour) {
                            durationMin *= 1.35;
                            if (warningDiv) {
                                warningDiv.style.display = 'block';
                                warningDiv.style.color = '#FF9500';
                                warningDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Heavy traffic expected at this hour';
                            }
                        } else {
                            if (warningDiv) {
                                warningDiv.style.display = 'block';
                                warningDiv.style.color = 'rgba(255,255,255,0.7)';
                                warningDiv.innerHTML = isAlt ? '<i class="fa-solid fa-route" style="color:#f59e0b; margin-right:4px;"></i> Alternative Road Corridor' : 'Typical traffic conditions';
                            }
                        }

                        window._draftDistanceKm = distanceKm;
                        window.setTxt('draft-map-dist', distanceKm.toFixed(1) + ' km');
                        window.setTxt('draft-map-time', Math.round(durationMin) + ' min');

                        const updateRouteScale = () => {
                            if (!draftMap) return;
                            const z = draftMap.getZoom();
                            const w = z >= 17 ? 12 : (z >= 15 ? 8 : (z >= 13 ? 5 : 3));
                            const bgw = w + 4;
                            if (draftRouteLine) draftRouteLine.setStyle({ weight: w });
                            if (draftRouteLineBg) draftRouteLineBg.setStyle({ weight: bgw });
                        };
                        draftMap.off('zoom', updateRouteScale);
                        draftMap.on('zoom', updateRouteScale);
                        updateRouteScale();
                    }
                }).catch(err => console.error("OSRM Routing failed.", err));
            } else if (latlngs.length === 1) {
                if (shouldFitBounds) {
                    draftMap.setView(latlngs[0], 15);
                }
                window.setTxt('draft-map-dist', '0 km');
                window.setTxt('draft-map-time', '0 min');
                const warnEl = document.getElementById('draft-traffic-warning');
                if (warnEl) warnEl.style.display = 'none';
            }
        };

        window.setRouteType = function (type, btn) {
            if (window._renderTimeout) {
                clearTimeout(window._renderTimeout);
                window._renderTimeout = null;
            }
            window.currentRouteType = (type === 'alternate' || type === 'alternative') ? 'alternative' : 'recommended';
            document.querySelectorAll('.btn-route-type').forEach(el => el.classList.remove('active'));
            let activeBtn = btn;
            if (btn) {
                btn.classList.add('active');
            } else {
                activeBtn = document.getElementById(window.currentRouteType === 'alternative' ? 'btn-route-alt' : 'btn-route-rec');
                if (activeBtn) activeBtn.classList.add('active');
            }

            const container = document.getElementById('route-toggle-container');
            if (container) {
                if (window.currentRouteType === 'alternative') {
                    container.classList.add('alt-active');
                } else {
                    container.classList.remove('alt-active');
                }
            }

            window.animateTimelineSwap(() => {
                const draft = window.getEffectiveDraft();
                window.renderItinerary(true);

                var _savedCenter = null, _savedZoom = null;
                if (typeof draftMap !== 'undefined' && draftMap) {
                    _savedCenter = draftMap.getCenter();
                    _savedZoom = draftMap.getZoom();
                }

                window.initDraftMap(draft, false);

                if (_savedCenter !== null && _savedZoom !== null && typeof draftMap !== 'undefined' && draftMap) {
                    draftMap.setView(_savedCenter, _savedZoom, { animate: false });
                }
            });

            if (typeof showToast === 'function') {
                showToast(window.currentRouteType === 'alternative' ? "Switched to Alternative Route — Farthest-anchor exploration sequence" : "Switched to Recommended Route — Optimal stop sequence");
            }
        };

        window.updateDonutChart = function (elementId, transport, food, activities) {
            const total = transport + food + activities;
            const el = document.getElementById(elementId);
            if (!el) return;

            if (total === 0) {
                el.style.background = 'rgba(255,255,255,0.1)';
                return;
            }

            const tPct = (transport / total) * 100;
            const fPct = (food / total) * 100;

            const tEnd = tPct;
            const fEnd = tPct + fPct;

            el.style.background = `conic-gradient(
            #38bdf8 0% ${tEnd}%,
            #34c759 ${tEnd}% ${fEnd}%,
            #ff9500 ${fEnd}% 100%
        )`;
        };

        window.updateDraftBudget = function (draft) {
            let actCost = 0, foodCost = 0, transCost = 0;
            draft.forEach(item => {
                actCost += parseFloat(item.entrance_fee) || 50;
                foodCost += parseFloat(item.avg_food_cost) || 150;
                transCost += parseFloat(item.avg_transport_cost) || 30;
            });

            const total = actCost + foodCost + transCost;
            window.setTxt('main-budget-total', '₱' + total.toLocaleString(undefined, { minimumFractionDigits: 2 }));
            window.setTxt('main-cost-trans', '₱' + transCost);
            window.setTxt('main-cost-food', '₱' + foodCost);
            window.setTxt('main-cost-act', '₱' + actCost);

            window.updateDonutChart('main-budget-donut', transCost, foodCost, actCost);
        };

        window.renderRailwayVehicleOptions = function (type) {
            const slider = document.getElementById('transport-slider');
            if (!slider) return;

            let optionsList = [];

            if (type === 'private') {
                optionsList = [
                    { val: 'own_car', name: 'Own Car', icon: 'fa-car', available: true },
                    { val: 'taxi', name: 'Taxi', icon: 'fa-taxi', available: true },
                    { val: 'van', name: 'Van', icon: 'fa-shuttle-van', available: true },
                    { val: 'motorcycle', name: 'Motorcycle', icon: 'fa-motorcycle', available: true }
                ];
            } else {
                // Public vehicles: show all standard public vehicles; mark as unavailable if no imported fare guide
                const draft = window.getEffectiveDraft ? window.getEffectiveDraft() : [];
                const rawMunis = draft.map(p => (p.municipality || '').trim()).filter(Boolean);
                const uniqueMunis = [...new Set(rawMunis)];

                // Inter-municipal trip condition: draft crosses more than 1 municipality
                const isInterMunicipal = uniqueMunis.length > 1;

                if (isInterMunicipal) {
                    // Inter-municipal trips: highway vehicles (Jeepney, Bus) have provincial LTFRB guides; Tricycle handles boundary routes
                    optionsList = [
                        { val: 'jeepney', name: 'Modern Jeepney', icon: 'fa-bus', available: true },
                        { val: 'private_bus', name: 'Aircon Bus', icon: 'fa-bus-simple', available: true },
                        { val: 'tricycle', name: 'Tricycle', icon: 'fa-motorcycle', available: true }
                    ];
                } else {
                    // Single municipality trip: check imported fare guides for this municipality
                    const destMuni = uniqueMunis.length === 1 ? uniqueMunis[0] : '';
                    const cleanMuni = destMuni.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim();
                    const muniKey = cleanMuni.toLowerCase().replace(/[^a-z0-9]/g, '_');
                    const muniRaw = cleanMuni.toLowerCase();

                    let activeTypes = [];
                    if (muniKey && window.fareData?.active_vehicles_by_municipality?.[muniKey]) {
                        activeTypes = window.fareData.active_vehicles_by_municipality[muniKey];
                    } else if (muniRaw && window.fareData?.active_vehicles_by_municipality?.[muniRaw]) {
                        activeTypes = window.fareData.active_vehicles_by_municipality[muniRaw];
                    } else if (muniKey && window.fareData?.by_municipality?.[muniKey]) {
                        activeTypes = Object.keys(window.fareData.by_municipality[muniKey]);
                    } else if (muniRaw && window.fareData?.by_municipality?.[muniRaw]) {
                        activeTypes = Object.keys(window.fareData.by_municipality[muniRaw]);
                    }

                    const activeLower = activeTypes.map(t => String(t).toLowerCase());

                    // If no municipality selected yet (e.g. empty draft), default all to available
                    const noDraft = uniqueMunis.length === 0;

                    const hasTrike = noDraft || activeLower.some(t => t.includes('tri') || t.includes('pedicab')) || !!window.fareData?.by_municipality?.[muniKey]?.tricycle;
                    const hasJeep = noDraft || activeLower.some(t => t.includes('jeep') || t.includes('mpuj') || t.includes('puj')) || !!window.fareData?.by_municipality?.[muniKey]?.jeepney;
                    const hasBus = noDraft || activeLower.some(t => t.includes('bus') || t.includes('pub')) || !!window.fareData?.by_municipality?.[muniKey]?.bus;

                    optionsList = [
                        { val: 'jeepney', name: 'Modern Jeepney', icon: 'fa-bus', available: hasJeep },
                        { val: 'private_bus', name: 'Aircon Bus', icon: 'fa-bus-simple', available: hasBus },
                        { val: 'tricycle', name: 'Tricycle', icon: 'fa-motorcycle', available: hasTrike }
                    ];

                    // Also include any other imported public vehicle types (e.g. UV Express / Mini Bus)
                    activeLower.forEach(vType => {
                        if ((vType.includes('van') || vType.includes('uve')) && !optionsList.some(o => o.val === 'van')) {
                            optionsList.push({ val: 'van', name: 'UV Express / Van', icon: 'fa-shuttle-van', available: true });
                        } else if (vType.includes('mini') && !optionsList.some(o => o.val === 'mini_bus')) {
                            optionsList.push({ val: 'mini_bus', name: 'Mini Bus', icon: 'fa-bus-simple', available: true });
                        }
                    });
                }
            }

            const unique = [];
            const seen = new Set();
            optionsList.forEach(opt => {
                if (!seen.has(opt.val)) {
                    seen.add(opt.val);
                    unique.push(opt);
                }
            });

            // Sort: Available first, Unavailable second
            unique.sort((a, b) => {
                const aAvail = a.available !== false ? 1 : 0;
                const bAvail = b.available !== false ? 1 : 0;
                return bAvail - aAvail;
            });

            const currentSelected = (document.getElementById('trip-transport').value || '').split(',').filter(Boolean);

            // Clean trip-transport to drop any vehicle that is now unavailable
            const availKeys = unique.filter(o => o.available !== false).map(o => o.val);
            const validSelected = currentSelected.filter(v => availKeys.includes(v));
            if (validSelected.length !== currentSelected.length) {
                document.getElementById('trip-transport').value = validSelected.join(',');
            }

            let html = '';
            unique.forEach(opt => {
                const isAvail = opt.available !== false;
                const isActive = (isAvail && validSelected.includes(opt.val)) ? 'active' : '';
                const disabledClass = !isAvail ? 'disabled-transport' : '';
                const badgeHtml = !isAvail ? '<span class="trans-badge-unavailable">Unavailable</span>' : '';

                html += `
            <div class="transport-option ${isActive} ${disabledClass}" 
                 data-val="${opt.val}" 
                 data-available="${isAvail ? '1' : '0'}"
                 onclick="window.selectTransportMode(this)">
                <i class="fa-solid ${opt.icon}"></i>
                <span>${opt.name}</span>
                ${badgeHtml}
            </div>`;
            });

            slider.innerHTML = html;
        };

        window.setTransportType = function (type, shouldReset = false) {
            const btnPublic = document.getElementById('btn-trans-public');
            const btnPrivate = document.getElementById('btn-trans-private');
            const pill = document.getElementById('transport-toggle-pill');

            if (type === 'private') {
                if (pill) pill.style.transform = 'translate3d(100%, 0, 0)';
                if (btnPrivate) {
                    btnPrivate.classList.add('active');
                    btnPrivate.style.color = '#1e3a8a';
                }
                if (btnPublic) {
                    btnPublic.classList.remove('active');
                    btnPublic.style.color = 'rgba(255,255,255,0.85)';
                }
            } else {
                if (pill) pill.style.transform = 'translate3d(0, 0, 0)';
                if (btnPublic) {
                    btnPublic.classList.add('active');
                    btnPublic.style.color = '#1e3a8a';
                }
                if (btnPrivate) {
                    btnPrivate.classList.remove('active');
                    btnPrivate.style.color = 'rgba(255,255,255,0.85)';
                }
            }

            const wrapper = document.getElementById('transport-slider-wrapper');
            if (wrapper) {
                wrapper.style.display = 'block';
            }

            // Reset transport selection if button clicked
            if (shouldReset) {
                const transInput = document.getElementById('trip-transport');
                if (transInput) transInput.value = '';
                document.querySelectorAll('.transport-option').forEach(opt => opt.classList.remove('active'));

                const fuelCalc = document.getElementById('fuel-cost-calc');
                if (fuelCalc) fuelCalc.textContent = '₱0.00';
            }

            // Dynamically render vehicle options from Railway DB!
            window.renderRailwayVehicleOptions(type);

            const fuelPanel = document.getElementById('own-car-fuel-panel');
            const isCarSelected = (document.getElementById('trip-transport').value || '').includes('own_car');
            if (fuelPanel) {
                if (isCarSelected && !shouldReset) {
                    fuelPanel.style.maxHeight = '200px';
                    fuelPanel.style.opacity = '1';
                } else {
                    fuelPanel.style.maxHeight = '0';
                    fuelPanel.style.opacity = '0';
                }
            }

            if (window.calculateModalBudget) window.calculateModalBudget();
        };

        // Initial render on load
        if (window.renderItinerary) {
            window.renderItinerary();
        }
    })();
</script>