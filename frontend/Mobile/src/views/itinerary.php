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
} catch (\Throwable $e) {
}
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
        display: flex;
        flex-direction: column !important;
        box-sizing: border-box !important;
        position: relative !important;
        width: 100% !important;
    }

    .draft-plan-card-wrapper.is-hidden {
        display: none !important;
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

    .swipe-content .time-label {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: #1e3a8a !important;
        background: #ffffff !important;
        border: none !important;
        outline: none !important;
        padding: 4px 11px !important;
        border-radius: 100px !important;
        letter-spacing: 0.4px !important;
        text-transform: uppercase !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12) !important;
        display: inline-flex !important;
        align-items: center !important;
    }

    .next-stop-distance-chip {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        background: #ffffff !important;
        border: none !important;
        outline: none !important;
        border-radius: 100px !important;
        padding: 5px 13px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #1e3a8a !important;
        margin-top: 8px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12) !important;
    }

    .next-stop-distance-chip i {
        color: #0284c7 !important;
        font-size: 11px !important;
    }

    .next-stop-distance-chip span {
        color: #1e3a8a !important;
        font-weight: 700 !important;
    }

    .stops-leg-chip,
    .p2p-leg-chip {
        background: #ffffff !important;
        border-radius: 100px !important;
        padding: 6px 14px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #1e3a8a !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        border: none !important;
        outline: none !important;
        user-select: none;
        cursor: pointer;
        white-space: nowrap !important;
        max-width: calc(100vw - 84px) !important;
        box-sizing: border-box !important;
        transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease !important;
    }

    .p2p-leg-chip:hover {
        transform: scale(1.03) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.13) !important;
    }

    .p2p-leg-chip:active {
        transform: scale(0.96) !important;
    }

    .p2p-leg-chip .leg-chip-name {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 180px !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }

    .p2p-leg-chip .leg-fare-tag {
        background: rgba(30, 58, 138, 0.08);
        color: #1e3a8a;
        font-weight: 800;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 99px;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    .p2p-leg-chip .leg-dist-text {
        color: #64748b;
        font-size: 10.5px;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    .p2p-leg-chip .leg-action-edit {
        color: #0284c7;
        font-size: 10px;
        margin-left: 2px;
    }

    .p2p-leg-warning-tag {
        display: none !important;
    }

    .p2p-leg-chip.leg-maintenance {
        background: #ef4444 !important;
        border: none !important;
        outline: none !important;
        box-shadow: 0 3px 10px rgba(239, 68, 68, 0.3) !important;
        color: #ffffff !important;
        cursor: not-allowed !important;
    }

    .p2p-leg-chip.leg-maintenance:hover {
        background: #dc2626 !important;
        transform: none !important;
        box-shadow: 0 3px 10px rgba(239, 68, 68, 0.3) !important;
    }

    .p2p-leg-chip.leg-maintenance:active {
        transform: none !important;
    }

    .leg-option-card {
        background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; /* Notification Card Gradient */
        border-radius: 14px !important;
        padding: 12px 14px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        cursor: pointer !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        border: 2px solid transparent !important;
        box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28) !important;
        color: #ffffff !important;
        position: relative !important;
        user-select: none !important;
    }

    .leg-option-card:hover {
        background: linear-gradient(135deg, #254b9f 0%, #315ea9 50%, #3c75be 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 16px rgba(32, 63, 141, 0.38) !important;
    }

    .leg-option-card.active {
        background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
        border: 2px solid #ffffff !important;
        box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28) !important;
    }

    .leg-option-card.disabled-leg-option {
        opacity: 0.45 !important;
        cursor: not-allowed !important;
        background: #334155 !important;
        border: 1.5px dashed #64748b !important;
        box-shadow: none !important;
    }

    .leg-option-card.disabled-leg-option:hover {
        background: #334155 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .leg-option-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        background: #ffffff !important;
        color: #1e3a8a !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important;
    }

    .travel-starter-card {
        background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; /* Notification Card Gradient */
        border-radius: 14px !important;
        padding: 12px 14px !important;
        cursor: pointer !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        user-select: none !important;
        -webkit-tap-highlight-color: transparent !important;
        border: 2px solid transparent !important;
        box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28) !important;
        color: #ffffff !important;
        position: relative !important;
    }

    .travel-starter-card:hover {
        background: linear-gradient(135deg, #254b9f 0%, #315ea9 50%, #3c75be 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 16px rgba(32, 63, 141, 0.38) !important;
    }

    .travel-starter-card.active {
        background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
        border: 2px solid #ffffff !important;
        box-shadow: 0 4px 14px rgba(32, 63, 141, 0.42) !important;
    }

    .travel-starter-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        background: #ffffff !important;
        color: #1e3a8a !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important;
    }

    .travel-starter-icon i {
        color: #1e3a8a !important;
        font-size: 14px;
    }

    @keyframes slideUpSheet {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
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
        <h2 id="itinerary-page-title"
            style="margin:0; font-size:22px; font-weight:800; letter-spacing:-0.5px; color:#0f172a !important;">Draft
            Plan</h2>
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
    <div id="draft-plan-card-wrapper" class="draft-plan-card-wrapper stagger-2 is-hidden" style="display:none;">
        <!-- Active Editing Trip Banner -->
        <div id="editing-plan-banner"
            style="display:none; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color:#ffffff; padding:10px 16px; border-radius:14px; margin-bottom:14px; font-size:12.5px; font-weight:700; border:none !important; outline:none !important; box-shadow:none !important; cursor:pointer;"
            onclick="window.cancelEditingSavedTrip()">
            <div style="display:flex; align-items:center; gap:8px; min-width:0; flex:1;">
                <i class="fa-solid fa-pen-to-square" style="color:#38bdf8; font-size:14px;"></i>
                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Editing: <strong
                        id="editing-banner-title">Saved Trip</strong></span>
            </div>
            <button type="button" onclick="event.stopPropagation(); window.cancelEditingSavedTrip();"
                style="background:rgba(255,255,255,0.22); border:none !important; outline:none !important; color:#ffffff; font-size:11.5px; font-weight:800; padding:6px 14px; border-radius:100px; cursor:pointer; margin-left:10px; flex-shrink:0; box-shadow:none !important; transition:all 0.2s ease; user-select:none; -webkit-tap-highlight-color:transparent;">
                <i class="fa-solid fa-xmark" style="margin-right:4px;"></i> Cancel Edit
            </button>
        </div>

        <!-- Step 1 Primary Travel Mode Card -->
        <div id="draft-travel-mode-bar" onclick="window.openTravelModeStarterModal()"
            style="display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; color:#ffffff; padding:11px 16px; border-radius:16px; margin-bottom:14px; cursor:pointer; border:none !important; outline:none !important; box-shadow:none !important; transition:all 0.2s ease;">
            <div style="display:flex; align-items:center; gap:10px; min-width:0; flex:1;">
                <div id="draft-travel-mode-icon" style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.18); display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0;">
                    <i class="fa-solid fa-car" style="color:#00f2fe;"></i>
                </div>
                <div style="min-width:0; flex:1;">
                    <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:rgba(255,255,255,0.8);">Trip Transportation</div>
                    <div id="draft-travel-mode-label" style="font-size:13.5px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Own Car</div>
                </div>
            </div>
            <span style="font-size:11px; font-weight:800; background:rgba(255,255,255,0.22); color:#ffffff; padding:4px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:4px; flex-shrink:0;">
                <i class="fa-solid fa-sliders" style="font-size:10px;"></i> Change
            </span>
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
                    <div><i class="fa-solid fa-clock" style="color:#00f2fe; margin-right:6px; font-size:16px;"></i>
                        <span id="draft-map-time">0 min</span></div>
                    <div id="draft-traffic-warning"
                        style="display:none; margin-top:2px; font-size:10px; font-weight:500;">
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
            style="display:none; width:100%; padding:16px; border-radius:20px; font-weight:900; font-size:16px; margin-top:4px; margin-bottom:0; border: none !important; outline: none !important; background:linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; color:#ffffff !important; box-shadow: none !important; cursor:pointer;"
            onclick="window.openSaveModal(event)">
            <i class="fa-solid fa-cloud-arrow-up" style="margin-right:8px;"></i> Save Draft Plan
        </button>
    </div>

    <!-- Empty State Card -->
    <div id="itinerary-empty-state" class="empty-state-card is-hidden" style="display:none;">
        <div class="empty-state-icon"
            style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;">
            <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
        </div>
        <h3>No plans yet</h3>
        <p>Start your itinerary by selecting which vehicle you will use for transportation, then add your stops!</p>
        <div style="display:flex; flex-direction:column; gap:10px; width:100%; max-width:280px; margin:14px auto 0;">
            <button class="btn-primary" onclick="window.openTravelModeStarterModal()"
                style="width:100%; padding:14px 18px; border-radius:18px; font-weight:800; font-size:14px; border:none !important; outline:none !important; background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color:#ffffff !important; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fa-solid fa-car-side" style="color:#00f2fe;"></i> Select Vehicle
            </button>
            <button class="btn-open-map" onclick="navigateTo('map')" style="width:100%; margin:0;">
                <i class="fa-solid fa-location-dot"></i> Browse Destinations Map
            </button>
        </div>
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

    <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.99) 100%); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:none !important; outline:none !important; border-radius:24px; padding:22px; width:100%; max-width:400px; max-height:90vh; overflow-y:auto; box-shadow:none !important;"
        class="hide-scrollbar">
        <h3 id="save-trip-modal-title"
            style="margin-top:0; color:#ffffff; font-size:20px; font-weight:800; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-cloud-arrow-up" style="color:#38bdf8; font-size:18px;"></i> Save Your Trip
        </h3>
        <p style="font-size:13px; color:rgba(255, 255, 255, 0.9); margin-bottom:18px; line-height:1.4;">Give your
            awesome adventure a name so you can pull it up later!</p>

        <label style="font-size:13px; color:#ffffff; margin-bottom:6px; display:block; font-weight:700;">Trip
            Name</label>
        <input type="text" id="trip-title" placeholder="e.g. La Union Weekend"
            style="width:100%; padding:12px 16px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.12); color:#ffffff; -webkit-text-fill-color:#ffffff; margin-bottom:16px; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box; box-shadow:none !important;">

        <!-- Custom Designed Calendar Date Picker -->
        <label
            style="font-size:13px; color:#ffffff; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between; font-weight:700;">
            <span><i class="fa-regular fa-calendar-days" style="color:#38bdf8; margin-right:5px;"></i> Trip Date
                (Optional)</span>
            <span id="calendar-clear-link" onclick="window.customClearDate(event)"
                style="display:none; font-size:11px; color:#ef4444; cursor:pointer; font-weight:700;">Clear</span>
        </label>

        <div id="custom-date-trigger" onclick="window.toggleCustomCalendar(event)"
            style="position:relative; width:100%; padding:11px 16px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.12); color:white; margin-bottom:16px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:all 0.25s ease; user-select:none; box-shadow:none !important;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-calendar-day" style="color:#38bdf8; font-size:14px;"></i>
                <span id="custom-date-display" style="color:rgba(255,255,255,0.85); font-weight:600;">Select trip
                    date</span>
            </div>
            <i class="fa-solid fa-chevron-down" id="custom-date-arrow"
                style="font-size:11px; color:rgba(255,255,255,0.8); transition:transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);"></i>
        </div>
        <input type="hidden" id="trip-date" value="">

        <!-- Floating Sleek Custom Calendar Card with Smooth Slide Animation -->
        <div id="custom-calendar-dropdown">
            <!-- Month & Year Navigation -->
            <div
                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding:0 4px;">
                <button type="button" onclick="window.changeCalendarMonth(-1)"
                    style="background:rgba(255,255,255,0.12); border:none !important; outline:none !important; color:white; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; box-shadow:none !important;">
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                </button>
                <div id="calendar-month-year"
                    style="font-size:14.5px; font-weight:800; color:#ffffff; letter-spacing:0.3px;"></div>
                <button type="button" onclick="window.changeCalendarMonth(1)"
                    style="background:rgba(255,255,255,0.12); border:none !important; outline:none !important; color:white; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; box-shadow:none !important;">
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
            <div id="calendar-days-grid"
                style="display:grid; grid-template-columns:repeat(7, 1fr); gap:3px; text-align:center;">
                <!-- Generated dynamically via JS -->
            </div>

            <!-- Footer Quick Actions -->
            <div
                style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.12);">
                <button type="button" onclick="window.selectTodayDate()"
                    style="background:rgba(255,255,255,0.15); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:700; padding:6px 14px; border-radius:100px; cursor:pointer; box-shadow:none !important;">
                    Today
                </button>
                <button type="button" onclick="window.toggleCustomCalendar(null, false)"
                    style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:800; padding:6px 16px; border-radius:100px; cursor:pointer; box-shadow:none !important;">
                    Done
                </button>
            </div>
        </div>

        <!-- Point-to-Point Route & Transit Summary Card -->
        <input type="hidden" id="trip-transport" value="">
        <div id="save-p2p-transit-summary"
            style="background:rgba(255,255,255,0.08); border-radius:16px; padding:14px 16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:12px; border:1px solid rgba(255,255,255,0.08);">
            <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                <div id="save-p2p-transit-icon" style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fa-solid fa-car" style="color:#ffffff; font-size:16px;"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; letter-spacing:0.6px; margin-bottom:2px;">Transport Mode</div>
                    <div id="save-p2p-transit-label" style="font-size:13px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Own Car</div>
                    <div id="save-p2p-legs-count" style="font-size:11px; color:rgba(255,255,255,0.75); margin-top:1px;">Configured from draft timeline</div>
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:10px; color:rgba(255,255,255,0.65); font-weight:700; text-transform:uppercase;">Transit Total</div>
                <div id="save-p2p-transit-cost" style="font-size:16px; font-weight:800; color:#00f2fe; margin-top:2px;">₱0.00</div>
            </div>
        </div>

        <script>
            // Backward compatibility stubs
            window.selectNoVehicleMode = function () {
                document.getElementById('trip-transport').value = 'no_vehicle';
                if (window.calculateModalBudget) window.calculateModalBudget();
            };
            window.selectTransportMode = function () {
                if (window.calculateModalBudget) window.calculateModalBudget();
            };
        </script>

        <div style="position:relative; margin-bottom:12px;">
            <span
                style="position:absolute; left:16px; top:14px; color:#38bdf8; font-weight:800; font-size:15px;">₱</span>
            <input type="tel" id="trip-budget" placeholder="Set a budget (optional)"
                oninput="this.value=this.value.replace(/\D/g,'');if(this.value.length>5)this.value=this.value.slice(0,5);window.calculateModalBudget()"
                style="width:100%; padding:12px 16px 12px 34px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.12); color:#ffffff; -webkit-text-fill-color:#ffffff; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box; box-shadow:none !important;">
        </div>

        <div id="save-budget-details"
            style="display:block; background:rgba(255,255,255,0.05); border:none !important; outline:none !important; padding:16px; border-radius:12px; margin-bottom:24px; box-shadow:none !important;">
            <div style="display:flex; align-items:center; gap:16px;">
                <div id="modal-donut-wrapper"
                    style="position:relative; flex-shrink:0; width:60px; margin-right:16px; height:60px; overflow:hidden; display:flex; align-items:center; justify-content:center; opacity:1; transform:scale(1); transition: width 0.45s cubic-bezier(0.34,1.56,0.64,1), margin-right 0.45s cubic-bezier(0.34,1.56,0.64,1), opacity 0.4s ease, transform 0.45s cubic-bezier(0.34,1.56,0.64,1);">
                    <div class="donut-chart" id="modal-budget-donut"
                        style="position:absolute; left:0; top:0; border-radius:50%; width:60px; height:60px; transform:scaleX(-1);">
                    </div>
                    <span id="modal-donut-pct"
                        style="position:relative; font-size:10px; font-weight:800; color:white; white-space:nowrap; text-shadow:0 1px 4px rgba(0,0,0,0.5);">100%</span>
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

            <!-- Color-Coded Budget Readability Legend -->
            <div id="save-budget-legend"
                style="display:flex; align-items:center; justify-content:space-between; gap:6px; margin-top:12px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.08); font-size:10px; font-weight:700;">
                <div id="legend-pill-green" style="display:flex; align-items:center; gap:4px; color:#10b981; transition:all 0.25s ease; opacity:0.6;">
                    <span style="width:6px; height:6px; border-radius:50%; background:#10b981; display:inline-block; box-shadow:0 0 6px rgba(16,185,129,0.6);"></span>
                    <span>Within (&lt;80%)</span>
                </div>
                <div id="legend-pill-orange" style="display:flex; align-items:center; gap:4px; color:#f59e0b; transition:all 0.25s ease; opacity:0.6;">
                    <span style="width:6px; height:6px; border-radius:50%; background:#f59e0b; display:inline-block; box-shadow:0 0 6px rgba(245,158,11,0.6);"></span>
                    <span>Nearing (80-100%)</span>
                </div>
                <div id="legend-pill-red" style="display:flex; align-items:center; gap:4px; color:#ef4444; transition:all 0.25s ease; opacity:0.6;">
                    <span style="width:6px; height:6px; border-radius:50%; background:#ef4444; display:inline-block; box-shadow:0 0 6px rgba(239,68,68,0.6);"></span>
                    <span>Over (&gt;100%)</span>
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
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6, 11, 25, 0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:100002 !important; justify-content:center; align-items:center; padding:16px;">
    <div
        style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; backdrop-filter:blur(24px) !important; -webkit-backdrop-filter:blur(24px) !important; border:none !important; outline:none !important; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 16px 40px rgba(10, 25, 60, 0.45) !important; text-align:center; color:#ffffff;">
        <div
            style="width:56px; height:56px; border-radius:50%; background:#ffffff !important; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; border:none !important; outline:none !important; box-shadow:none !important;">
            <i class="fa-solid fa-triangle-exclamation" style="color:#1e3a8a !important; font-size:26px;"></i>
        </div>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800; letter-spacing:-0.3px;">Missing
            Details</h3>
        <p id="confirm-modal-msg"
            style="margin:0 0 24px; color:rgba(255,255,255,0.95); font-size:13.5px; line-height:1.55; font-weight:500;">
        </p>
        <div style="display:flex; gap:12px;">
            <button type="button" class="btn-primary" id="btn-confirm-cancel"
                style="flex:1; background:rgba(255,255,255,0.22) !important; border:none !important; outline:none !important; color:#ffffff !important; padding:13px; border-radius:14px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:none !important; transition:transform 0.15s ease;">Cancel</button>
            <button type="button" class="btn-primary" id="btn-confirm-ok"
                style="flex:1; background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; padding:13px; border-radius:14px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:none !important; transition:transform 0.15s ease;">Save
                Anyway</button>
        </div>
    </div>
</div>

<!-- Point-to-Point Leg Transport Selection Modal (Bottom Sheet) -->
<div id="leg-transport-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6, 11, 25, 0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:100003 !important; justify-content:center; align-items:flex-end; padding:0;">
    <div style="background:#ffffff !important; border-radius:28px 28px 0 0; width:100%; max-width:480px; box-shadow:0 -10px 40px rgba(10, 25, 60, 0.5) !important; max-height:88vh; display:flex; flex-direction:column; box-sizing:border-box; overflow:hidden; animation: slideUpSheet 0.28s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <!-- Header (Matched to Notifications Header Banner) -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 18px 14px 18px; color:#ffffff; flex-shrink:0; border:none !important; outline:none !important;">
            <div style="width:36px; height:4px; background:rgba(255,255,255,0.35); border-radius:99px; margin:0 auto 12px auto;"></div>
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:#ffffff !important; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.18);">
                        <i class="fa-solid fa-route" style="color:#1e3a8a !important; font-size:15px;"></i>
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <h3 style="margin:0; font-size:16px; font-weight:800; color:#ffffff; letter-spacing:-0.2px;">Choose Leg Transports</h3>
                            <span style="font-size:9.5px; font-weight:800; background:rgba(255, 255, 255, 0.2); color:#ffffff; padding:2px 8px; border-radius:100px; text-transform:uppercase; letter-spacing:0.4px;">Multi-Select</span>
                        </div>
                        <div id="leg-modal-subtitle" style="font-size:11.5px; color:rgba(255,255,255,0.85); font-weight:600; margin-top:2px;">
                            Leg Route Details
                        </div>
                    </div>
                </div>
                <button type="button" onclick="window.closeLegTransportModal()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink:0; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
                </button>
            </div>
        </div>

        <!-- Body Below Header (Pure White Background) -->
        <div style="background:#ffffff !important; color:#1e293b; padding:16px 18px 12px 18px; flex:1; overflow-y:auto; display:flex; flex-direction:column; box-sizing:border-box;">
            
            <!-- Multi-Select Selection Info Bar -->
            <div id="leg-modal-selection-bar" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; background:#f8fafc; padding:8px 12px; border-radius:12px; border:1px solid #e2e8f0;">
                <span style="font-size:12px; font-weight:700; color:#1e3a8a; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-check-double" style="color:#1e3a8a; font-size:12px;"></i> Select multiple vehicles if needed:
                </span>
                <span id="leg-modal-selection-count" style="font-size:10.5px; font-weight:800; background:rgba(30, 58, 138, 0.08); color:#1e3a8a; padding:3px 10px; border-radius:100px;">
                    1 Selected
                </span>
            </div>

            <!-- Warning Notice if destination is inaccessible by private car -->
            <div id="leg-modal-warning" style="display:none; background:#fffbeb; border:1px solid #fde68a; border-radius:14px; padding:10px 12px; margin-bottom:12px; display:flex; gap:10px; align-items:flex-start;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#d97706; font-size:14px; margin-top:2px; flex-shrink:0;"></i>
                <div style="font-size:11.5px; color:#92400e; line-height:1.4;">
                    <strong>Restricted Access:</strong> <span id="leg-modal-warning-text">This spot is not accessible by private car. Park at Trailhead and hike or take a local tricycle.</span>
                </div>
            </div>

            <!-- Transport Options List (Royal Blue Notification Cards) -->
            <div id="leg-modal-options-list" style="overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:9px; padding-right:2px; -webkit-overflow-scrolling:touch; max-height:48vh; margin-bottom:4px;">
                <!-- Rendered dynamically -->
            </div>

        </div>

        <!-- Locked Bottom Footer Banner (Matched to Notifications Footer Banner) -->
        <div style="flex-shrink:0; padding:12px 18px calc(14px + env(safe-area-inset-bottom, 0px)) 18px; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); display:flex; align-items:center; justify-content:space-between; gap:12px; border:none !important; outline:none !important;">
            <div id="leg-modal-fare-container">
                <div style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.75); text-transform:uppercase; letter-spacing:0.5px;">Estimated Leg Fare</div>
                <div id="leg-modal-total-fare" style="font-size:18px; font-weight:900; color:#ffffff; letter-spacing:-0.2px;">₱0.00</div>
            </div>
            <button type="button" id="btn-apply-leg-transport" onclick="window.applyLegVehicleSelection()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; font-size:12.5px; font-weight:800; cursor:pointer; padding:9px 18px; border-radius:100px; box-shadow:0 2px 8px rgba(0, 0, 0, 0.15) !important; display:inline-flex; align-items:center; gap:7px; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.95)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-check" style="color:#1e3a8a !important; font-size:12px;"></i> Done
            </button>
        </div>

    </div>
</div>

<!-- Vehicle Selection Modal (Bottom Sheet - Matched to Leg Transport Modal) -->
<div id="travel-mode-starter-modal" onclick="if(event.target===this) window.closeTravelModeStarterModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6, 11, 25, 0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:100003 !important; justify-content:center; align-items:flex-end; padding:0;">
    <div style="background:#ffffff !important; border-radius:28px 28px 0 0; width:100%; max-width:480px; box-shadow:0 -10px 40px rgba(10, 25, 60, 0.5) !important; max-height:88vh; display:flex; flex-direction:column; box-sizing:border-box; overflow:hidden; animation: slideUpSheet 0.28s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <!-- Header (Matched to Notifications / Leg Transport Header Banner) -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 18px 14px 18px; color:#ffffff; flex-shrink:0; border:none !important; outline:none !important;">
            <div style="width:36px; height:4px; background:rgba(255,255,255,0.35); border-radius:99px; margin:0 auto 12px auto;"></div>
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:#ffffff !important; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.18);">
                        <i class="fa-solid fa-car-side" style="color:#1e3a8a !important; font-size:15px;"></i>
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <h3 style="margin:0; font-size:16px; font-weight:800; color:#ffffff; letter-spacing:-0.2px;">Choose Your Vehicle</h3>
                            <span style="font-size:9.5px; font-weight:800; background:rgba(255, 255, 255, 0.2); color:#ffffff; padding:2px 8px; border-radius:100px; text-transform:uppercase; letter-spacing:0.4px;">Multi-Select</span>
                        </div>
                        <div style="font-size:11.5px; color:rgba(255,255,255,0.85); font-weight:600; margin-top:2px;">
                            Select one or more vehicles for your trip
                        </div>
                    </div>
                </div>
                <button type="button" onclick="window.closeTravelModeStarterModal()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink:0; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
                </button>
            </div>
        </div>

        <!-- Body Below Header (Pure White Background) -->
        <div style="background:#ffffff !important; color:#1e293b; padding:16px 18px 12px 18px; flex:1; overflow-y:auto; display:flex; flex-direction:column; box-sizing:border-box;">
            
            <!-- Multi-Select Selection Info Bar -->
            <div id="starter-modal-selection-bar" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; background:#f8fafc; padding:8px 12px; border-radius:12px; border:1px solid #e2e8f0;">
                <span style="font-size:12px; font-weight:700; color:#1e3a8a; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-check-double" style="color:#1e3a8a; font-size:12px;"></i> Select multiple vehicles if needed:
                </span>
                <span id="starter-modal-selection-count" style="font-size:10.5px; font-weight:800; background:rgba(30, 58, 138, 0.08); color:#1e3a8a; padding:3px 10px; border-radius:100px;">
                    1 Selected
                </span>
            </div>

            <!-- Information Bar -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; background:#f8fafc; padding:10px 12px; border-radius:12px; border:1px solid #e2e8f0; font-size:11.5px; color:#475569; line-height:1.4;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-info" style="color:#0284c7; font-size:13px; flex-shrink:0;"></i>
                    <span>Select multiple vehicles for connecting routes (e.g. Car + Tricycle for trailhead access or Bus + Jeepney).</span>
                </span>
            </div>

            <!-- Transport Options List (Royal Blue Notification Cards) -->
            <div style="overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:9px; padding-right:2px; -webkit-overflow-scrolling:touch; max-height:48vh; margin-bottom:4px;">
                
                <!-- Category 1: Private Vehicles -->
                <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#1e3a8a; letter-spacing:0.6px; margin:4px 0 2px 2px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-car" style="font-size:11px; color:#1e3a8a;"></i> Private Vehicles
                </div>

                <!-- Option 1: Own Car -->
                <div class="travel-starter-card" data-mode="own_car" onclick="window.toggleStarterVehicleMode('own_car')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Own Car</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 2: Motorcycle -->
                <div class="travel-starter-card" data-mode="motorcycle" onclick="window.toggleStarterVehicleMode('motorcycle')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Motorcycle</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 3: Van -->
                <div class="travel-starter-card" data-mode="van" onclick="window.toggleStarterVehicleMode('van')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Van</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Category 2: Public Transit & Hired -->
                <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#1e3a8a; letter-spacing:0.6px; margin:12px 0 2px 2px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-bus" style="font-size:11px; color:#1e3a8a;"></i> Public Transit & Hired
                </div>

                <!-- Option 4: Modern Jeepney (MPUJ) -->
                <div class="travel-starter-card" data-mode="mpuj" onclick="window.toggleStarterVehicleMode('mpuj')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-bus-simple"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Modern Jeepney (MPUJ)</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 5: Traditional Jeepney (TPUJ) -->
                <div class="travel-starter-card" data-mode="tpuj" onclick="window.toggleStarterVehicleMode('tpuj')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Traditional Jeepney (TPUJ)</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 6: Tricycle -->
                <div class="travel-starter-card" data-mode="tricycle" onclick="window.toggleStarterVehicleMode('tricycle')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Tricycle</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 7: Taxi -->
                <div class="travel-starter-card" data-mode="taxi" onclick="window.toggleStarterVehicleMode('taxi')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-taxi"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Taxi</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 8: UV Express (UVE) -->
                <div class="travel-starter-card" data-mode="uve" onclick="window.toggleStarterVehicleMode('uve')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">UV Express (UVE)</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 9: PUB Regular Bus -->
                <div class="travel-starter-card" data-mode="pub_regular" onclick="window.toggleStarterVehicleMode('pub_regular')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">PUB Regular (Ordinary Bus)</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 10: PUB Aircon Bus -->
                <div class="travel-starter-card" data-mode="pub_aircon" onclick="window.toggleStarterVehicleMode('pub_aircon')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box" style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon" style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">PUB Aircon (Aircon Bus)</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge" style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

            </div>

        </div>

        <!-- Locked Bottom Footer Banner (Matched to Notifications / Leg Transport Footer Banner) -->
        <div style="flex-shrink:0; padding:12px 18px calc(14px + env(safe-area-inset-bottom, 0px)) 18px; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); display:flex; align-items:center; justify-content:space-between; gap:12px; border:none !important; outline:none !important;">
            <div style="min-width:0; flex:1;">
                <div style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.75); text-transform:uppercase; letter-spacing:0.5px;">Trip Travel Mode</div>
                <div id="starter-modal-current-mode-label" style="font-size:15px; font-weight:900; color:#ffffff; letter-spacing:-0.2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Own Car</div>
            </div>
            <button type="button" id="btn-apply-starter-transport" onclick="window.applyStarterVehicleSelection()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; font-size:12.5px; font-weight:800; cursor:pointer; padding:9px 18px; border-radius:100px; box-shadow:0 2px 8px rgba(0, 0, 0, 0.15) !important; display:inline-flex; align-items:center; gap:7px; transition:transform 0.15s ease; flex-shrink:0;" onpointerdown="this.style.transform='scale(0.95)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-check" style="color:#1e3a8a !important; font-size:12px;"></i> Done
            </button>
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

        // Helper to calculate distance and estimated driving travel time from user GPS to destination
        window.getDistanceAndETA = function (destLat, destLng) {
            const curLat = window.myLat || window.currentGPSLat;
            const curLng = window.myLng || window.currentGPSLng;
            return window.calculateLegETA(curLat, curLng, destLat, destLng);
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

        // SWR Caching for Fare Rates & Vehicle Types (Eliminates redundant Railway hits)
        const FARES_CACHE_KEY = 'public_fare_data';
        const FARES_CACHE_TTL = 3600000; // 1 hour TTL
        let cachedFarePayload = null;
        try {
            const rawFares = localStorage.getItem(FARES_CACHE_KEY);
            if (rawFares) {
                const parsed = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(rawFares, null) : JSON.parse(rawFares);
                if (parsed && parsed.data) {
                    cachedFarePayload = parsed;
                    window.fareData = parsed.data.fares || {};
                    window.vehicleData = parsed.data.vehicles || [];
                    window.vehicleTypes = parsed.data.vehicle_types || [];
                }
            }
        } catch (e) { }

        const now = Date.now();
        const shouldFetchFreshFares = !cachedFarePayload || !cachedFarePayload.timestamp || (now - cachedFarePayload.timestamp > FARES_CACHE_TTL);

        if (shouldFetchFreshFares) {
            fetch(backendUrl + '/api/public/fares', {
                headers: { 'Accept': 'application/json' }
            }).then(r => r.json()).then(d => {
                if (d && d.fares) {
                    window.fareData = d.fares || {};
                    window.vehicleData = d.vehicles || [];
                    window.vehicleTypes = d.vehicle_types || [];
                    try {
                        localStorage.setItem(FARES_CACHE_KEY, JSON.stringify({ data: d, timestamp: Date.now() }));
                    } catch (e) { }
                    if (typeof window.recalculateCosts === 'function') {
                        window.recalculateCosts();
                    }
                }
            }).catch(e => console.error("Fares fetch error:", e));
        }



        window.getFareFromMatrix = function (vehicleType, distanceKm, municipality = null) {
            if (!window.fareData) return null;

            const dKm = parseFloat(distanceKm) || 0;
            const rawType = (vehicleType || '').toString().toLowerCase().trim();
            const normType = rawType.replace(/[- ]/g, '_');

            if (['own_car', 'taxi', 'motorcycle', 'car'].includes(normType)) return null;

            let fareEntry = null;

            // 1. Direct match in top-level window.fareData
            if (window.fareData[normType]) {
                fareEntry = window.fareData[normType];
            }

            // 2. Municipality lookup for local / inter-municipal guides
            const cleanMuni = municipality ? municipality.toString().trim().replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '') : '';
            const muniKey = cleanMuni.toLowerCase().replace(/[^a-z0-9]/g, '_');
            const muniRaw = cleanMuni.toLowerCase();

            if (!fareEntry && window.fareData.by_municipality) {
                const muniObj = window.fareData.by_municipality[muniKey] || window.fareData.by_municipality[muniRaw];
                if (muniObj) {
                    if (muniObj[normType]) {
                        fareEntry = muniObj[normType];
                    } else if ((normType === 'tricycle' || normType === 'trike') && muniObj.tricycle) {
                        fareEntry = muniObj.tricycle;
                    } else if ((normType === 'tricycle' || normType === 'trike') && muniObj.default) {
                        fareEntry = muniObj.default;
                    }
                }
            }

            // 3. Fallbacks and alias matching
            if (!fareEntry) {
                if (normType === 'pub_aircon' || normType.includes('aircon')) {
                    fareEntry = window.fareData['pub_aircon'] || window.fareData['bus'] || window.fareData['private_bus'];
                } else if (normType === 'pub_ordinary' || normType === 'pub_regular' || normType.includes('ordinary') || normType.includes('regular')) {
                    fareEntry = window.fareData['pub_ordinary'] || window.fareData['pub_regular'] || window.fareData['bus'];
                } else if (normType === 'mpuj' || normType.includes('mpuj') || normType.includes('modern')) {
                    fareEntry = window.fareData['mpuj'] || window.fareData['jeepney'] || window.fareData['lutrampco'];
                } else if (normType === 'tricycle' || normType === 'trike') {
                    fareEntry = (cleanMuni && window.fareData?.by_municipality?.[muniKey]?.tricycle)
                        || (cleanMuni && window.fareData?.by_municipality?.[muniRaw]?.tricycle)
                        || null;
                } else if (normType === 'bus' || normType === 'private_bus') {
                    fareEntry = window.fareData['pub_aircon'] || window.fareData['pub_ordinary'] || window.fareData['bus'];
                } else if (normType === 'jeepney' || normType === 'lutrampco') {
                    fareEntry = window.fareData['mpuj'] || window.fareData['tpuj'] || window.fareData['jeepney'];
                } else if (normType === 'uve' || normType === 'van' || normType === 'mini_bus') {
                    fareEntry = window.fareData['uve'] || window.fareData['van'] || window.fareData['mini_bus'];
                }
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

        // ---- Unified Vehicle Normalization & Display Name Engine ----
        window.normalizeVehicleKey = function (m) {
            if (!m) return '';
            let s = String(m).trim().toLowerCase();
            // Strip leading and trailing underscores, hyphens, and spaces
            s = s.replace(/^[-_\s]+|[-_\s]+$/g, '');
            // Collapse internal spaces, hyphens, and multiple underscores into a single underscore
            s = s.replace(/[- ]+/g, '_').replace(/_+/g, '_');

            if (s === 'car') return 'own_car';
            if (s === 'motor') return 'motorcycle';
            if (s === 'pub_ordinary') return 'pub_regular';
            if (s === 'bus') return 'pub_aircon';
            if (s === 'trike') return 'tricycle';
            if (s.startsWith('pub_aircon')) return 'pub_aircon';
            if (s.startsWith('pub_reg')) return 'pub_regular';
            if (s.startsWith('own_car')) return 'own_car';
            return s;
        };

        window.parseCompositeTransportModes = function (str) {
            if (!str) return [];
            if (Array.isArray(str)) {
                return str.map(s => window.normalizeVehicleKey(s)).filter(Boolean);
            }
            // CRITICAL: Split by '+' or ',' FIRST, so whitespace around '+' is NOT converted into underscores!
            const parts = String(str).split(/[\+,]/);
            const validModes = ['own_car', 'motorcycle', 'van', 'mpuj', 'tpuj', 'tricycle', 'pub_regular', 'pub_aircon', 'uve', 'taxi'];
            const res = [];
            parts.forEach(p => {
                const norm = window.normalizeVehicleKey(p);
                if (norm && validModes.includes(norm) && !res.includes(norm)) {
                    res.push(norm);
                }
            });
            return res;
        };

        window.getVehicleDisplayName = function (modeKey) {
            if (!modeKey) return 'Own Car';
            const subModes = window.parseCompositeTransportModes(modeKey);
            if (subModes.length === 2) {
                return `${window.getVehicleShortName(subModes[0])} + ${window.getVehicleShortName(subModes[1])}`;
            } else if (subModes.length > 2) {
                return `${subModes.length} Vehicles (${subModes.map(m => window.getVehicleShortName(m)).join(', ')})`;
            }

            const norm = (subModes.length === 1) ? subModes[0] : window.normalizeVehicleKey(modeKey);
            const map = {
                'own_car': 'Own Car',
                'motorcycle': 'Motorcycle',
                'van': 'Van',
                'mpuj': 'Modern Jeepney (MPUJ)',
                'tpuj': 'Traditional Jeepney (TPUJ)',
                'jeepney': 'Traditional Jeepney (TPUJ)',
                'tricycle': 'Tricycle',
                'pub_regular': 'PUB Regular (Ordinary Bus)',
                'pub_aircon': 'PUB Aircon (Aircon Bus)',
                'uve': 'UV Express (UVE)',
                'taxi': 'Taxi'
            };
            return map[norm] || norm.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        };

        window.getVehicleShortName = function (modeKey) {
            if (!modeKey) return 'Own Car';
            const subModes = window.parseCompositeTransportModes(modeKey);
            if (subModes.length === 2) {
                return `${window.getVehicleShortName(subModes[0])} + ${window.getVehicleShortName(subModes[1])}`;
            } else if (subModes.length > 2) {
                return `${subModes.length} Vehicles`;
            }

            const norm = (subModes.length === 1) ? subModes[0] : window.normalizeVehicleKey(modeKey);
            const map = {
                'own_car': 'Own Car',
                'motorcycle': 'Motorcycle',
                'van': 'Van',
                'mpuj': 'Modern Jeepney',
                'tpuj': 'Traditional Jeepney',
                'jeepney': 'Jeepney',
                'tricycle': 'Tricycle',
                'pub_regular': 'Regular Bus',
                'pub_aircon': 'Aircon Bus',
                'uve': 'UV Express',
                'taxi': 'Taxi'
            };
            return map[norm] || norm.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        };

        window.getVehicleIconHtml = function (modeKey) {
            const subModes = window.parseCompositeTransportModes(modeKey);
            if (subModes.length > 1) {
                return '<i class="fa-solid fa-shuffle" style="color:#00f2fe;"></i>';
            }
            const norm = (subModes.length === 1) ? subModes[0] : window.normalizeVehicleKey(modeKey || 'own_car');
            if (norm === 'motorcycle') return '<i class="fa-solid fa-motorcycle" style="color:#fbbf24;"></i>';
            if (norm === 'van') return '<i class="fa-solid fa-van-shuttle" style="color:#c084fc;"></i>';
            if (norm === 'mpuj') return '<i class="fa-solid fa-bus-simple" style="color:#34d399;"></i>';
            if (norm === 'tpuj' || norm === 'jeepney') return '<i class="fa-solid fa-van-shuttle" style="color:#10b981;"></i>';
            if (norm === 'tricycle') return '<i class="fa-solid fa-motorcycle" style="color:#22d3ee;"></i>';
            if (norm === 'pub_regular') return '<i class="fa-solid fa-bus" style="color:#fb923c;"></i>';
            if (norm === 'pub_aircon') return '<i class="fa-solid fa-bus" style="color:#f87171;"></i>';
            if (norm === 'uve') return '<i class="fa-solid fa-van-shuttle" style="color:#a855f7;"></i>';
            if (norm === 'taxi') return '<i class="fa-solid fa-taxi" style="color:#facc15;"></i>';
            return '<i class="fa-solid fa-car" style="color:#00f2fe;"></i>';
        };

        // ---- Point-to-Point (P2P) Fare Calculator ----
        window.calculateSingleLegCost = function (mode, distKm, muniA, muniB) {
            if (!mode) return 0;
            const subParts = window.parseCompositeTransportModes(mode);
            if (subParts.length > 1) {
                let subSum = 0;
                subParts.forEach(p => {
                    subSum += window.calculateSingleLegCost(p, distKm, muniA, muniB);
                });
                return Math.round(subSum * 100) / 100;
            }
            const norm = (subParts.length === 1) ? subParts[0] : window.normalizeVehicleKey(mode);
            if (['own_car', 'motorcycle', 'walking', 'walk', 'no_vehicle'].includes(norm)) {
                return 0;
            }
            const cleanA = (muniA || '').replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim();
            const cleanB = (muniB || '').replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim();
            const isCross = Boolean(cleanA && cleanB && cleanA.toLowerCase() !== cleanB.toLowerCase());
            const d = parseFloat(distKm) || 1.0;

            if (norm === 'taxi') {
                return Math.round(40 + (d * 13));
            }

            if (norm === 'tricycle' || norm === 'trike') {
                if (isCross) {
                    const dA = Math.round((d / 2.0) * 100) / 100;
                    const dB = Math.max(0.1, Math.round((d - dA) * 100) / 100);
                    let pA = window.getFareFromMatrix('tricycle', dA, cleanA);
                    if (pA === null || pA === undefined || pA === 0) pA = Math.max(15, Math.round(15 + Math.max(0, dA - 1.5) * 2.0));
                    let pB = window.getFareFromMatrix('tricycle', dB, cleanB);
                    if (pB === null || pB === undefined || pB === 0) pB = Math.max(15, Math.round(15 + Math.max(0, dB - 1.5) * 2.0));
                    return Math.round((pA + pB) * 100) / 100;
                } else {
                    let p = window.getFareFromMatrix('tricycle', d, cleanA);
                    if (p === null || p === undefined || p === 0) p = Math.max(15, Math.round(15 + Math.max(0, d - 1.5) * 2.0));
                    return Math.round(p * 100) / 100;
                }
            }

            // Public transit: Jeepney (MPUJ/TPUJ), PUB Aircon, PUB Ordinary, Van
            const targetVeh = (norm === 'private_bus') ? 'pub_aircon' : ((norm === 'jeepney') ? 'mpuj' : norm);
            if (isCross) {
                const dA = Math.round((d / 2.0) * 100) / 100;
                let pA = window.getFareFromMatrix(targetVeh, dA, cleanA);
                let full = window.getFareFromMatrix(targetVeh, d, cleanA);
                if (pA === null) {
                    if (norm === 'mpuj' || norm === 'jeepney') pA = Math.max(15, Math.round(15 + Math.max(0, dA - 4) * 2.2));
                    else if (norm === 'tpuj') pA = Math.max(13, Math.round(13 + Math.max(0, dA - 4) * 1.8));
                    else if (norm.includes('aircon') || norm.includes('bus')) pA = Math.max(11, Math.round(10.50 + Math.max(0, dA - 5) * 2.2));
                    else if (norm.includes('ordinary') || norm.includes('regular')) pA = Math.max(11, Math.round(11 + Math.max(0, dA - 5) * 2.0));
                    else if (norm === 'uve' || norm === 'van' || norm === 'mini_bus') pA = Math.max(25, Math.round(25 + Math.max(0, dA - 4) * 2.5));
                    else pA = Math.max(15, Math.round(15 + Math.max(0, dA - 4) * 2.0));
                }
                if (full === null) {
                    if (norm === 'mpuj' || norm === 'jeepney') full = Math.max(15, Math.round(15 + Math.max(0, d - 4) * 2.2));
                    else if (norm === 'tpuj') full = Math.max(13, Math.round(13 + Math.max(0, d - 4) * 1.8));
                    else if (norm.includes('aircon') || norm.includes('bus')) full = Math.max(11, Math.round(10.50 + Math.max(0, d - 5) * 2.2));
                    else if (norm.includes('ordinary') || norm.includes('regular')) full = Math.max(11, Math.round(11 + Math.max(0, d - 5) * 2.0));
                    else if (norm === 'uve' || norm === 'van' || norm === 'mini_bus') full = Math.max(25, Math.round(25 + Math.max(0, d - 4) * 2.5));
                    else full = Math.max(15, Math.round(15 + Math.max(0, d - 4) * 2.0));
                }
                const estB = Math.max(0, full - pA);
                return Math.round((pA + estB) * 100) / 100;
            } else {
                let p = window.getFareFromMatrix(targetVeh, d, cleanA);
                if (p === null) {
                    if (norm === 'mpuj' || norm === 'jeepney') p = Math.max(15, Math.round(15 + Math.max(0, d - 4) * 2.2));
                    else if (norm === 'tpuj') p = Math.max(13, Math.round(13 + Math.max(0, d - 4) * 1.8));
                    else if (norm.includes('aircon') || norm.includes('bus')) p = Math.max(11, Math.round(10.50 + Math.max(0, d - 5) * 2.2));
                    else if (norm.includes('ordinary') || norm.includes('regular')) p = Math.max(11, Math.round(11 + Math.max(0, d - 5) * 2.0));
                    else if (norm === 'uve' || norm === 'van' || norm === 'mini_bus') p = Math.max(25, Math.round(25 + Math.max(0, d - 4) * 2.5));
                    else p = Math.max(15, Math.round(15 + Math.max(0, d - 4) * 2.0));
                }
                return Math.round(p * 100) / 100;
            }
        };

        window.resolveLegOptimalTransport = function (fromSpot, toSpot, distKm, globalMode) {
            const d = parseFloat(distKm) || 1.5;
            const muniA = fromSpot ? window.getSpotMuniName(fromSpot) : '';
            const muniB = toSpot ? window.getSpotMuniName(toSpot) : '';
            const cleanA = muniA.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim().toLowerCase();
            const cleanB = muniB.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim().toLowerCase();
            const isCross = Boolean(cleanA && cleanB && cleanA !== cleanB);

            const isNonDrivable = Boolean(toSpot && (toSpot.accessible_by_private_vehicle === 0 || toSpot.accessible_by_private_vehicle === false || toSpot.accessible_by_private_vehicle === '0'));

            // Multi-modal global transport support (e.g. "own_car + tricycle", "own_car + motorcycle")
            const subModes = window.parseCompositeTransportModes(globalMode);
            if (subModes.length > 1) {
                const cost = window.calculateSingleLegCost(subModes.join(' + '), d, muniA, muniB);
                const dispName = (subModes.length === 2)
                    ? `${window.getVehicleShortName(subModes[0])} + ${window.getVehicleShortName(subModes[1])}`
                    : `${subModes.length} Vehicles Selected`;

                let warningNotice = null;
                if (isNonDrivable) {
                    const canTricycleOrMotor = subModes.some(m => ['tricycle', 'motorcycle'].includes(m));
                    if (canTricycleOrMotor && subModes.some(m => ['own_car', 'van'].includes(m))) {
                        warningNotice = 'Park car at trailhead; proceed via local tricycle/motorcycle.';
                    } else if (!canTricycleOrMotor) {
                        warningNotice = 'Not accessible by selected vehicles (trailhead drop-off only).';
                    }
                }

                return {
                    mode: subModes.join(' + '),
                    transport_mode: subModes.join(' + '),
                    transport_modes: subModes,
                    name: dispName,
                    full_names: subModes.map(m => window.getVehicleDisplayName(m)).join(', '),
                    icon: 'fa-shuffle',
                    cost: cost,
                    leg_cost: cost,
                    warning: warningNotice,
                    is_non_drivable: isNonDrivable
                };
            }

            const normGlobal = (subModes.length === 1) ? subModes[0] : window.normalizeVehicleKey(globalMode || 'own_car');

            // 1. If global is Own Car
            if (normGlobal === 'own_car') {
                return {
                    mode: 'own_car',
                    transport_mode: 'own_car',
                    transport_modes: ['own_car'],
                    name: 'Own Car',
                    icon: 'fa-car',
                    cost: 0,
                    warning: isNonDrivable ? 'Not accessible by private car. Park at Trailhead & hike or ride local trike.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 2. If global is Motorcycle
            if (normGlobal === 'motorcycle') {
                return {
                    mode: 'motorcycle',
                    transport_mode: 'motorcycle',
                    transport_modes: ['motorcycle'],
                    name: 'Motorcycle',
                    icon: 'fa-motorcycle',
                    cost: 0,
                    warning: isNonDrivable ? 'Trailhead parking recommended for non-drivable spots.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 3. If global is Van
            if (normGlobal === 'van') {
                return {
                    mode: 'van',
                    transport_mode: 'van',
                    transport_modes: ['van'],
                    name: 'Van',
                    icon: 'fa-van-shuttle',
                    cost: 0,
                    warning: isNonDrivable ? 'Not accessible by private van. Park at Trailhead & hike or ride local trike.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 4. If global is Modern Jeepney (MPUJ)
            if (normGlobal === 'mpuj') {
                const cost = window.calculateSingleLegCost('mpuj', d, muniA, muniB);
                return {
                    mode: 'mpuj',
                    transport_mode: 'mpuj',
                    transport_modes: ['mpuj'],
                    name: 'Modern Jeepney (MPUJ)',
                    icon: 'fa-bus-simple',
                    cost: cost,
                    warning: isNonDrivable ? 'Requires hike/local ride from highway drop-off.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 5. If global is Traditional Jeepney (TPUJ)
            if (normGlobal === 'tpuj' || normGlobal === 'jeepney') {
                const cost = window.calculateSingleLegCost('tpuj', d, muniA, muniB);
                return {
                    mode: 'tpuj',
                    transport_mode: 'tpuj',
                    transport_modes: ['tpuj'],
                    name: 'Traditional Jeepney (TPUJ)',
                    icon: 'fa-van-shuttle',
                    cost: cost,
                    warning: isNonDrivable ? 'Requires hike/local ride from highway drop-off.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 6. If global is Tricycle
            if (normGlobal === 'tricycle') {
                const cost = window.calculateSingleLegCost('tricycle', d, muniA, muniB);
                return {
                    mode: 'tricycle',
                    transport_mode: 'tricycle',
                    transport_modes: ['tricycle'],
                    name: 'Tricycle',
                    icon: 'fa-motorcycle',
                    cost: cost,
                    warning: isNonDrivable ? 'Drop off at trailhead / jump-off point.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 7. If global is PUB Regular (Ordinary Bus)
            if (normGlobal === 'pub_regular') {
                const cost = window.calculateSingleLegCost('pub_regular', d, muniA, muniB);
                return {
                    mode: 'pub_regular',
                    transport_mode: 'pub_regular',
                    transport_modes: ['pub_regular'],
                    name: 'PUB Regular (Ordinary Bus)',
                    icon: 'fa-bus',
                    cost: cost,
                    warning: isNonDrivable ? 'Drop off along highway; transfer or hike to site.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 8. If global is PUB Aircon (Aircon Bus)
            if (normGlobal === 'pub_aircon') {
                const cost = window.calculateSingleLegCost('pub_aircon', d, muniA, muniB);
                return {
                    mode: 'pub_aircon',
                    transport_mode: 'pub_aircon',
                    transport_modes: ['pub_aircon'],
                    name: 'PUB Aircon (Aircon Bus)',
                    icon: 'fa-bus',
                    cost: cost,
                    warning: isNonDrivable ? 'Drop off along highway; transfer or hike to site.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 9. If global is UV Express (UVE)
            if (normGlobal === 'uve') {
                const cost = window.calculateSingleLegCost('uve', d, muniA, muniB);
                return {
                    mode: 'uve',
                    transport_mode: 'uve',
                    transport_modes: ['uve'],
                    name: 'UV Express (UVE)',
                    icon: 'fa-van-shuttle',
                    cost: cost,
                    warning: isNonDrivable ? 'Terminal drop-off; local transfer required.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 10. If global is Taxi
            if (normGlobal === 'taxi') {
                const cost = window.calculateSingleLegCost('taxi', d, muniA, muniB);
                return {
                    mode: 'taxi',
                    transport_mode: 'taxi',
                    transport_modes: ['taxi'],
                    name: 'Taxi',
                    icon: 'fa-taxi',
                    cost: cost,
                    warning: isNonDrivable ? 'Trailhead drop-off point only.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // Default fallback: Own Car
            return {
                mode: 'own_car',
                transport_mode: 'own_car',
                transport_modes: ['own_car'],
                name: 'Own Car',
                icon: 'fa-car',
                cost: 0,
                warning: isNonDrivable ? 'Not accessible by private car. Park at Trailhead & hike or ride local trike.' : null,
                is_non_drivable: isNonDrivable
            };
        };

        window.resolveSpotAccessibleVehicles = function (spot) {
            if (!spot) return [];
            if (Array.isArray(spot.accessible_vehicles) && spot.accessible_vehicles.length > 0) {
                return spot.accessible_vehicles;
            }
            const spotId = String(spot.id || spot.tourist_spot_id || '');
            if (window._cachedMapSpots && window._cachedMapSpots[spotId] && Array.isArray(window._cachedMapSpots[spotId].accessible_vehicles) && window._cachedMapSpots[spotId].accessible_vehicles.length > 0) {
                return window._cachedMapSpots[spotId].accessible_vehicles;
            }
            try {
                const rawMap = localStorage.getItem('public_map_data');
                if (rawMap) {
                    const parsed = JSON.parse(rawMap);
                    const list = parsed?.destinations || parsed?.data?.destinations || [];
                    const found = list.find(s => String(s?.id) === spotId);
                    if (found && Array.isArray(found.accessible_vehicles) && found.accessible_vehicles.length > 0) {
                        return found.accessible_vehicles;
                    }
                }
            } catch (e) {}

            // Standard fallback for spots without explicit custom database assignments
            const isDrivable = !(spot.accessible_by_private_vehicle === 0 || spot.accessible_by_private_vehicle === false || spot.accessible_by_private_vehicle === '0');
            if (isDrivable) {
                return ['Car', 'Motorcycle', 'Van', 'MPUJ', 'TPUJ', 'Tricycle', 'PUB_Regular', 'PUB_Aircon', 'UVE', 'TAXI'];
            } else {
                return ['Tricycle', 'Motorcycle'];
            }
        };

        window.isVehicleAllowedForSpot = function (mode, spot) {
            if (!spot) return { allowed: true, reason: '' };
            const subModes = window.parseCompositeTransportModes(mode);
            if (subModes.length > 1) {
                const checks = subModes.map(m => window.isVehicleAllowedForSpot(m, spot));
                const anyAllowed = checks.some(c => c.allowed);
                if (anyAllowed) {
                    return { allowed: true, reason: '' };
                } else {
                    return checks[0] || { allowed: false, reason: 'Destination inaccessible by selected vehicles' };
                }
            }
            
            const norm = (subModes.length === 1) ? subModes[0] : window.normalizeVehicleKey(mode);
            const isNonDrivable = Boolean(spot.accessible_by_private_vehicle === 0 || spot.accessible_by_private_vehicle === false || spot.accessible_by_private_vehicle === '0');

            if (norm === 'own_car' && isNonDrivable) {
                return { allowed: false, reason: 'Destination inaccessible by car (trailhead drop-off only)' };
            }
            if (norm === 'van' && isNonDrivable) {
                return { allowed: false, reason: 'Destination inaccessible by van (trailhead drop-off only)' };
            }

            const rawList = window.resolveSpotAccessibleVehicles(spot);
            if (!Array.isArray(rawList) || rawList.length === 0) {
                return { allowed: true, reason: '' };
            }

            const accList = rawList.map(v => String(v).toLowerCase().trim());
            let matched = false;
            if (norm === 'own_car') {
                matched = accList.some(v => v.includes('car'));
            } else if (norm === 'motorcycle') {
                matched = accList.some(v => v.includes('motorcycle') || v.includes('motor'));
            } else if (norm === 'van') {
                matched = accList.some(v => v.includes('van'));
            } else if (norm === 'mpuj') {
                matched = accList.some(v => v.includes('mpuj') || v.includes('modern') || v.includes('jeep'));
            } else if (norm === 'tpuj' || norm === 'jeepney') {
                matched = accList.some(v => v.includes('tpuj') || v.includes('traditional') || v.includes('jeep'));
            } else if (norm === 'tricycle') {
                matched = accList.some(v => v.includes('tricycle') || v.includes('trike'));
            } else if (norm === 'pub_regular') {
                matched = accList.some(v => v.includes('pub_regular') || v.includes('regular') || v.includes('ordinary') || (v.includes('pub') && !v.includes('aircon')) || v.includes('bus'));
            } else if (norm === 'pub_aircon') {
                matched = accList.some(v => v.includes('pub_aircon') || v.includes('aircon') || v.includes('bus'));
            } else if (norm === 'uve') {
                matched = accList.some(v => v.includes('uve') || v.includes('uv') || v.includes('van'));
            } else if (norm === 'taxi') {
                matched = accList.some(v => v.includes('taxi'));
            } else {
                matched = accList.some(v => v.includes(norm));
            }

            if (!matched) {
                return { allowed: false, reason: 'Not in this site\'s accessible vehicles (DB restricted)' };
            }
            return { allowed: true, reason: '' };
        };

        window.isSpotUnderMaintenance = function (spot) {
            if (!spot) return false;
            try {
                const spotId = String(spot.id || spot.tourist_spot_id || '');
                if (spotId && spot.is_maintenance === undefined) {
                    if (window._cachedMapSpots && window._cachedMapSpots[spotId]) {
                        spot.is_maintenance = window._cachedMapSpots[spotId].is_maintenance;
                    } else {
                        const rawMap = localStorage.getItem('public_map_data');
                        if (rawMap) {
                            const parsed = JSON.parse(rawMap);
                            const list = parsed?.destinations || parsed?.data?.destinations || [];
                            const mapSpot = list.find(s => String(s?.id) === spotId);
                            if (mapSpot && mapSpot.is_maintenance !== undefined) {
                                spot.is_maintenance = mapSpot.is_maintenance;
                            }
                        }
                    }
                }
            } catch (e) {}
            return Boolean(
                spot.is_maintenance === 1 || 
                spot.is_maintenance === true || 
                spot.is_maintenance === '1' ||
                (spot.status && String(spot.status).toLowerCase().includes('maint'))
            );
        };

        window.getLegTransportInfo = function (legIdx) {
            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

            if (!draft || draft.length === 0 || legIdx >= draft.length) {
                return { mode: 'own_car', transport_mode: 'own_car', name: 'Own Car', icon: 'fa-car', cost: 0, leg_cost: 0, distance_km: 0 };
            }

            const curGlobalTransport = localStorage.getItem('intan_elyu_draft_trip_transport') || document.getElementById('trip-transport')?.value || 'own_car';

            let fromSpot = null;
            let toSpot = draft[legIdx];
            let distKm = 2.0;
            let muniA = '';
            let muniB = window.getSpotMuniName(toSpot);

            if (legIdx === 0) {
                const toLat = toSpot.lat || toSpot.latitude;
                const toLng = toSpot.lng || toSpot.longitude;
                if (typeof window.getDistanceAndETA === 'function' && toLat && toLng) {
                    const eta = window.getDistanceAndETA(toLat, toLng);
                    if (eta && eta.distanceKm) distKm = eta.distanceKm;
                }
                muniA = muniB;
            } else {
                fromSpot = draft[legIdx - 1];
                muniA = window.getSpotMuniName(fromSpot);
                const fromLat = fromSpot.lat || fromSpot.latitude;
                const fromLng = fromSpot.lng || fromSpot.longitude;
                const toLat = toSpot.lat || toSpot.latitude;
                const toLng = toSpot.lng || toSpot.longitude;
                if (typeof window.calculateLegETA === 'function' && fromLat && fromLng && toLat && toLng) {
                    const eta = window.calculateLegETA(fromLat, fromLng, toLat, toLng);
                    if (eta && eta.distanceKm) distKm = eta.distanceKm;
                }
            }

            const isSiteUnderMaintenance = window.isSpotUnderMaintenance(toSpot);
            if (isSiteUnderMaintenance) {
                return {
                    mode: 'suspended',
                    transport_mode: 'suspended',
                    transport_modes: [],
                    name: 'Site Under Maintenance',
                    icon: 'fa-triangle-exclamation',
                    cost: 0,
                    leg_cost: 0,
                    distance_km: distKm,
                    warning: 'Destination temporarily closed / under maintenance',
                    is_inaccessible: true,
                    is_non_drivable: true,
                    is_maintenance: true,
                    is_custom: false
                };
            }

            let overrides = [];
            try {
                overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
            } catch (e) { overrides = []; }

            const override = Array.isArray(overrides) ? overrides[legIdx] : null;
            if (override && (override.transport_mode || override.transport_modes)) {
                let modes = window.parseCompositeTransportModes(override.transport_modes || override.transport_mode);
                if (modes.length === 0) modes = ['own_car'];

                const cost = (override.leg_cost !== null && override.leg_cost !== undefined)
                    ? parseFloat(override.leg_cost)
                    : window.calculateSingleLegCost(modes.join(' + '), distKm, muniA, muniB);

                let name = '';
                let icon = 'fa-car';
                if (modes.length === 1) {
                    name = window.getVehicleDisplayName(modes[0]);
                    icon = (modes[0] === 'motorcycle' || modes[0] === 'tricycle') ? 'fa-motorcycle' : (modes[0].includes('bus') ? 'fa-bus' : 'fa-car');
                } else if (modes.length === 2) {
                    name = `${window.getVehicleShortName(modes[0])} + ${window.getVehicleShortName(modes[1])}`;
                    icon = 'fa-shuffle';
                } else {
                    name = `${modes.length} Vehicles Selected`;
                    icon = 'fa-shuffle';
                }

                const isNonDrivable = Boolean(toSpot && (toSpot.accessible_by_private_vehicle === 0 || toSpot.accessible_by_private_vehicle === false || toSpot.accessible_by_private_vehicle === '0'));
                let hasInaccessible = false;
                let warningReason = null;
                modes.forEach(m => {
                    const check = window.isVehicleAllowedForSpot(m, toSpot);
                    if (!check.allowed) {
                        hasInaccessible = true;
                        warningReason = check.reason;
                    }
                });

                return {
                    mode: modes.join(' + '),
                    transport_mode: modes.join(' + '),
                    transport_modes: modes,
                    name: name,
                    full_names: modes.map(m => window.getVehicleDisplayName(m)).join(', '),
                    icon: icon,
                    cost: cost,
                    leg_cost: cost,
                    distance_km: distKm,
                    warning: !hasInaccessible ? (isNonDrivable && modes.includes('own_car') ? 'Trailhead drop-off only' : null) : warningReason,
                    is_inaccessible: hasInaccessible,
                    is_non_drivable: isNonDrivable,
                    is_custom: true
                };
            }

            const resolved = window.resolveLegOptimalTransport(fromSpot, toSpot, distKm, curGlobalTransport);
            const isNonDrivable = Boolean(toSpot && (toSpot.accessible_by_private_vehicle === 0 || toSpot.accessible_by_private_vehicle === false || toSpot.accessible_by_private_vehicle === '0'));
            const check = window.isVehicleAllowedForSpot(resolved.mode, toSpot);

            return {
                ...resolved,
                transport_mode: resolved.mode,
                transport_modes: (Array.isArray(resolved.transport_modes) && resolved.transport_modes.length > 0)
                    ? resolved.transport_modes
                    : window.parseCompositeTransportModes(resolved.mode),
                leg_cost: resolved.cost,
                distance_km: distKm,
                warning: check.allowed ? resolved.warning : check.reason,
                is_inaccessible: !check.allowed,
                is_non_drivable: isNonDrivable,
                is_custom: false
            };
        };

        window.currentLegModalIdx = null;
        window.currentLegModalDistKm = 2.0;
        window.currentLegModalMuniA = '';
        window.currentLegModalMuniB = '';
        window.currentLegModalSelectedModes = [];
        window.currentLegEvaluatedCandidates = [];

        window.openLegTransportModal = function (legIdx) {
            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

            if (!draft || draft.length === 0 || legIdx >= draft.length) return;

            const toSpot = draft[legIdx];
            const fromSpot = (legIdx === 0) ? null : draft[legIdx - 1];
            const muniA = fromSpot ? window.getSpotMuniName(fromSpot) : '';
            const muniB = window.getSpotMuniName(toSpot);

            let distKm = 2.0;
            if (legIdx === 0) {
                const toLat = toSpot.lat || toSpot.latitude;
                const toLng = toSpot.lng || toSpot.longitude;
                if (typeof window.getDistanceAndETA === 'function' && toLat && toLng) {
                    const eta = window.getDistanceAndETA(toLat, toLng);
                    if (eta && eta.distanceKm) distKm = eta.distanceKm;
                }
            } else {
                const fromLat = fromSpot.lat || fromSpot.latitude;
                const fromLng = fromSpot.lng || fromSpot.longitude;
                const toLat = toSpot.lat || toSpot.latitude;
                const toLng = toSpot.lng || toSpot.longitude;
                if (typeof window.calculateLegETA === 'function' && fromLat && fromLng && toLat && toLng) {
                    const eta = window.calculateLegETA(fromLat, fromLng, toLat, toLng);
                    if (eta && eta.distanceKm) distKm = eta.distanceKm;
                }
            }

            window.currentLegModalIdx = legIdx;
            window.currentLegModalDistKm = distKm;
            window.currentLegModalMuniA = muniA;
            window.currentLegModalMuniB = muniB;

            const fromName = (legIdx === 0) ? 'Your Location' : fromSpot.name;
            const toName = toSpot.name;

            // Enrich destination spot metadata from public_map_data if cached in localStorage
            try {
                const rawMap = localStorage.getItem('public_map_data');
                if (rawMap) {
                    const parsedMap = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(rawMap, null) : JSON.parse(rawMap);
                    const spotsArr = (parsedMap && parsedMap.data && parsedMap.data.destinations) ? parsedMap.data.destinations : (parsedMap && parsedMap.destinations ? parsedMap.destinations : []);
                    if (Array.isArray(spotsArr)) {
                        const spotId = String(toSpot.id || toSpot.tourist_spot_id || '');
                        const mapSpot = spotsArr.find(s => s && String(s.id) === spotId);
                        if (mapSpot) {
                            if (toSpot.accessible_by_private_vehicle === undefined && mapSpot.accessible_by_private_vehicle !== undefined) {
                                toSpot.accessible_by_private_vehicle = mapSpot.accessible_by_private_vehicle;
                            }
                            if ((!toSpot.accessible_vehicles || toSpot.accessible_vehicles.length === 0) && Array.isArray(mapSpot.accessible_vehicles) && mapSpot.accessible_vehicles.length > 0) {
                                toSpot.accessible_vehicles = mapSpot.accessible_vehicles;
                            }
                            if (mapSpot.is_maintenance !== undefined) {
                                toSpot.is_maintenance = mapSpot.is_maintenance;
                            }
                        }
                    }
                }
            } catch (e) {}

            const isNonDrivable = Boolean(toSpot && (toSpot.accessible_by_private_vehicle === 0 || toSpot.accessible_by_private_vehicle === false || toSpot.accessible_by_private_vehicle === '0'));
            const isSiteUnderMaintenance = Boolean(
                (toSpot && (toSpot.is_maintenance === 1 || toSpot.is_maintenance === true || toSpot.is_maintenance === '1')) ||
                (typeof window.getLegTransportInfo === 'function' && window.getLegTransportInfo(legIdx).is_maintenance)
            );
            window.currentLegIsSiteUnderMaintenance = isSiteUnderMaintenance;

            // If the destination site is under maintenance, DO NOT show the Choose Leg Transport opensheet modal!
            if (isSiteUnderMaintenance) {
                const modal = document.getElementById('leg-transport-modal');
                if (modal) modal.style.display = 'none';
                if (typeof showToast === 'function') {
                    showToast(`${toName || 'This site'} is currently under maintenance or temporarily closed. Vehicle transit is restricted.`);
                }
                return;
            }

            const subEl = document.getElementById('leg-modal-subtitle');
            if (subEl) {
                subEl.innerHTML = isSiteUnderMaintenance
                    ? `<strong>Leg ${legIdx + 1}:</strong> ${fromName} &rarr; ${toName}`
                    : `<strong>Leg ${legIdx + 1}:</strong> ${fromName} &rarr; ${toName} &bull; ${distKm.toFixed(1)} km`;
            }
            const warnEl = document.getElementById('leg-modal-warning');
            const warnTxtEl = document.getElementById('leg-modal-warning-text');
            if (warnEl && warnTxtEl) {
                if (isSiteUnderMaintenance) {
                    warnEl.style.display = 'flex';
                    warnTxtEl.textContent = `${toName} is currently under maintenance or temporarily closed. Vehicle transit is restricted.`;
                } else if (isNonDrivable) {
                    warnEl.style.display = 'flex';
                    warnTxtEl.textContent = `${toName} is inaccessible by private vehicles. If choosing Own Car, plan to park at the trailhead and hike or ride a local tricycle.`;
                } else {
                    warnEl.style.display = 'none';
                }
            }

            const cleanA = muniA.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim();
            const cleanB = muniB.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim();
            const isCross = Boolean(cleanA && cleanB && cleanA.toLowerCase() !== cleanB.toLowerCase());

            const trikeOptimal = (!isCross && distKm <= 3.5);
            const mpujOptimal = (isCross || distKm > 3.5);

            const candidates = [
                // Private Vehicles
                {
                    mode: 'own_car',
                    name: 'Own Car',
                    desc: isNonDrivable ? 'Trailhead drop-off point only' : 'Direct personal car driving',
                    icon: 'fa-car',
                    cost: 0,
                    isRecommended: false,
                    category: 'Private'
                },
                {
                    mode: 'motorcycle',
                    name: 'Motorcycle',
                    desc: 'Fast two-wheeler personal ride',
                    icon: 'fa-motorcycle',
                    cost: 0,
                    isRecommended: false,
                    category: 'Private'
                },
                {
                    mode: 'van',
                    name: 'Van',
                    desc: isNonDrivable ? 'Trailhead drop-off point only' : 'Family & group private vehicle',
                    icon: 'fa-van-shuttle',
                    cost: 0,
                    isRecommended: false,
                    category: 'Private'
                },
                // Public Vehicles
                {
                    mode: 'mpuj',
                    name: 'MPUJ (Modern Jeepney)',
                    desc: 'Air-conditioned modern commuter jeepney',
                    icon: 'fa-bus-simple',
                    cost: window.calculateSingleLegCost('mpuj', distKm, muniA, muniB),
                    isRecommended: mpujOptimal,
                    category: 'Public'
                },
                {
                    mode: 'tpuj',
                    name: 'TPUJ (Traditional Jeepney)',
                    desc: 'Classic open-air commuter jeepney',
                    icon: 'fa-van-shuttle',
                    cost: window.calculateSingleLegCost('tpuj', distKm, muniA, muniB),
                    isRecommended: false,
                    category: 'Public'
                },
                {
                    mode: 'tricycle',
                    name: 'Tricycle',
                    desc: (!isCross ? 'Direct point-to-point drop-off' : 'Local town & beach transit'),
                    icon: 'fa-motorcycle',
                    cost: window.calculateSingleLegCost('tricycle', distKm, muniA, muniB),
                    isRecommended: trikeOptimal,
                    category: 'Public'
                },
                {
                    mode: 'pub_regular',
                    name: 'PUB Regular (Ordinary Bus)',
                    desc: 'Provincial highway regular passenger bus',
                    icon: 'fa-bus',
                    cost: window.calculateSingleLegCost('pub_regular', distKm, muniA, muniB),
                    isRecommended: false,
                    category: 'Public'
                },
                {
                    mode: 'pub_aircon',
                    name: 'PUB Aircon (Aircon Bus)',
                    desc: 'Provincial air-conditioned coach bus',
                    icon: 'fa-bus',
                    cost: window.calculateSingleLegCost('pub_aircon', distKm, muniA, muniB),
                    isRecommended: false,
                    category: 'Public'
                },
                {
                    mode: 'uve',
                    name: 'UV Express (UVE)',
                    desc: 'Express inter-town shuttle service',
                    icon: 'fa-van-shuttle',
                    cost: window.calculateSingleLegCost('uve', distKm, muniA, muniB),
                    isRecommended: false,
                    category: 'Public'
                },
                {
                    mode: 'taxi',
                    name: 'Taxi',
                    desc: 'Direct metered air-conditioned taxi',
                    icon: 'fa-taxi',
                    cost: window.calculateSingleLegCost('taxi', distKm, muniA, muniB),
                    isRecommended: false,
                    category: 'Public'
                }
            ];

            let availableCount = 0;
            window.currentLegEvaluatedCandidates = candidates.map(opt => {
                let isAvail = true;
                let unavailReason = '';

                // 1. Site maintenance check
                if (isSiteUnderMaintenance) {
                    isAvail = false;
                    unavailReason = 'Destination currently under maintenance / closed';
                }

                // 2. Road & Database Accessible Vehicles check
                if (isAvail) {
                    const check = window.isVehicleAllowedForSpot(opt.mode, toSpot);
                    if (!check.allowed) {
                        isAvail = false;
                        unavailReason = check.reason;
                    }
                }

                // 3. Operational range constraints
                if (isAvail && opt.mode === 'tricycle' && distKm > 15) {
                    isAvail = false;
                    unavailReason = 'Exceeds tricycle service range (> 15 km)';
                }

                if (isAvail) availableCount++;
                return { ...opt, isAvail, unavailReason };
            });

            // Initialize multi-selection from existing saved overrides or leg info
            let overrides = [];
            try {
                overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
            } catch (e) { overrides = []; }
            const override = Array.isArray(overrides) ? overrides[legIdx] : null;

            let initialModes = [];
            if (isSiteUnderMaintenance) {
                initialModes = [];
            } else if (override && Array.isArray(override.transport_modes) && override.transport_modes.length > 0) {
                initialModes = window.parseCompositeTransportModes(override.transport_modes);
            } else if (override && override.transport_mode) {
                initialModes = window.parseCompositeTransportModes(override.transport_mode);
            } else {
                const curLegInfo = window.getLegTransportInfo(legIdx);
                if (curLegInfo && Array.isArray(curLegInfo.transport_modes) && curLegInfo.transport_modes.length > 0) {
                    initialModes = window.parseCompositeTransportModes(curLegInfo.transport_modes);
                } else if (curLegInfo && curLegInfo.transport_mode && curLegInfo.transport_mode !== 'suspended') {
                    initialModes = window.parseCompositeTransportModes(curLegInfo.transport_mode);
                } else {
                    initialModes = window.getNormalizedTripModes();
                }
            }

            if (!isSiteUnderMaintenance && initialModes.length === 0) {
                initialModes = window.getNormalizedTripModes();
            }
            window.currentLegModalSelectedModes = initialModes;

            window.renderLegModalList(availableCount);

            const modal = document.getElementById('leg-transport-modal');
            if (modal) modal.style.display = 'flex';

            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.add('nav-hidden');
        };

        window.renderLegModalList = function (availableCount) {
            const listEl = document.getElementById('leg-modal-options-list');
            if (!listEl) return;

            let listHtml = '';
            const availCount = (availableCount !== undefined) 
                ? availableCount 
                : (window.currentLegEvaluatedCandidates || []).filter(c => c.isAvail).length;

            const selectedModes = window.currentLegModalSelectedModes || [];

            let prevCat = '';
            (window.currentLegEvaluatedCandidates || []).forEach(opt => {
                if (opt.category && opt.category !== prevCat) {
                    prevCat = opt.category;
                    const catIcon = prevCat === 'Private' ? 'fa-car' : 'fa-bus';
                    listHtml += `
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#1e3a8a; letter-spacing:0.6px; margin:8px 0 3px 2px; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid ${catIcon}" style="font-size:11px; color:#1e3a8a;"></i> ${prevCat} Transport
                    </div>`;
                }

                const optNorm = window.normalizeVehicleKey(opt.mode);
                const isSelected = selectedModes.some(m => {
                    return window.normalizeVehicleKey(m) === optNorm;
                }) && opt.isAvail;

                const costStr = (opt.cost > 0) ? `₱${opt.cost.toFixed(2)}` : '₱0';
                const costColor = '#ffffff';
                const disabledClass = !opt.isAvail ? 'disabled-leg-option' : '';
                const clickHandler = opt.isAvail
                    ? `onclick="window.toggleLegCandidateMode('${opt.mode}')"`
                    : `onclick="if(typeof showToast==='function') showToast('Unavailable: ${opt.unavailReason.replace(/'/g, "\\'")}');"`;

                listHtml += `
                <div class="leg-option-card ${isSelected ? 'active' : ''} ${disabledClass}" ${clickHandler} style="${!opt.isAvail ? 'opacity:0.45; cursor:not-allowed;' : ''}">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <!-- Multi-select checkbox -->
                        <div style="width:22px; height:22px; border-radius:6px; border:2px solid ${isSelected ? '#ffffff' : 'rgba(255,255,255,0.65)'}; background:${isSelected ? '#ffffff' : 'rgba(255,255,255,0.1)'}; display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            ${isSelected ? '<i class="fa-solid fa-check" style="color:#1e3a8a !important;"></i>' : ''}
                        </div>

                        <div class="leg-option-icon">
                            <i class="fa-solid ${opt.icon}"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span>${opt.name}</span>
                                ${opt.isRecommended && opt.isAvail ? '<span style="font-size:9px; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:1px 7px; border-radius:100px; font-weight:800; letter-spacing:0.3px;">OPTIMAL</span>' : ''}
                                ${!opt.isAvail ? '<span style="font-size:8.5px; background:rgba(239,68,68,0.25); color:#fca5a5; padding:1px 6px; border-radius:100px; font-weight:800; letter-spacing:0.3px;"><i class="fa-solid fa-ban" style="font-size:8px;"></i> UNAVAILABLE</span>' : ''}
                            </div>
                            ${!opt.isAvail ? `<div style="font-size:10.5px; color:#fca5a5; font-weight:700; margin-top:3px;"><i class="fa-solid fa-circle-exclamation" style="font-size:9px;"></i> ${opt.unavailReason}</div>` : ''}
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <div style="font-size:14.5px; font-weight:900; color:${opt.isAvail ? costColor : '#94a3b8'}; letter-spacing:-0.2px;">
                            ${opt.isAvail ? costStr : '—'}
                        </div>
                        ${isSelected ? '<span style="font-size:9.5px; color:#ffffff; font-weight:800;"><i class="fa-solid fa-check"></i> Selected</span>' : ''}
                    </div>
                </div>`;
            });

            listEl.innerHTML = listHtml;

            const isSiteMaint = Boolean(window.currentLegIsSiteUnderMaintenance || availCount === 0);

            // Toggle selection info bar: remove "select multiple vehicles if needed: Site Under Maintenance"
            const selBar = document.getElementById('leg-modal-selection-bar');
            if (selBar) {
                selBar.style.display = isSiteMaint ? 'none' : 'flex';
            }

            // Update Selection Count Pill
            const countEl = document.getElementById('leg-modal-selection-count');
            if (countEl) {
                const count = selectedModes.length;
                countEl.textContent = `${count} Selected`;
                countEl.style.background = 'rgba(30, 58, 138, 0.08)';
                countEl.style.color = '#1e3a8a';
                countEl.style.borderColor = 'transparent';
            }

            // Toggle estimated leg fare container: remove the estimated leg fare if Site Under Maintenance
            const fareContainer = document.getElementById('leg-modal-fare-container');
            if (fareContainer) {
                fareContainer.style.display = isSiteMaint ? 'none' : 'block';
            }

            // Calculate & Update Total Fare for the Leg
            let totalLegFare = 0;
            if (!isSiteMaint) {
                selectedModes.forEach(m => {
                    totalLegFare += window.calculateSingleLegCost(m, window.currentLegModalDistKm, window.currentLegModalMuniA, window.currentLegModalMuniB);
                });
            }
            const fareEl = document.getElementById('leg-modal-total-fare');
            if (fareEl) {
                fareEl.textContent = (totalLegFare > 0) ? `₱${totalLegFare.toFixed(2)}` : '₱0.00';
            }

            const btnApply = document.getElementById('btn-apply-leg-transport');
            if (btnApply) {
                if (isSiteMaint) {
                    btnApply.disabled = true;
                    btnApply.style.opacity = '0.7';
                    btnApply.style.cursor = 'not-allowed';
                    btnApply.style.width = '100%';
                    btnApply.style.justifyContent = 'center';
                    btnApply.innerHTML = `<i class="fa-solid fa-ban"></i> Site Under Maintenance`;
                } else {
                    btnApply.disabled = false;
                    btnApply.style.opacity = '1';
                    btnApply.style.cursor = 'pointer';
                    btnApply.style.width = 'auto';
                    btnApply.style.justifyContent = 'flex-start';
                    btnApply.innerHTML = `<i class="fa-solid fa-check"></i> Done (${selectedModes.length})`;
                }
            }
        };

        window.toggleLegCandidateMode = function (mode) {
            const opt = (window.currentLegEvaluatedCandidates || []).find(c => c.mode === mode);
            if (opt && !opt.isAvail) {
                if (typeof showToast === 'function') {
                    showToast('Unavailable: ' + (opt.unavailReason || 'Not permitted for this destination'));
                }
                return;
            }

            const norm = window.normalizeVehicleKey(mode);
            let list = (window.currentLegModalSelectedModes || []).map(m => window.normalizeVehicleKey(m)).filter(Boolean);

            const idx = list.indexOf(norm);
            if (idx > -1) {
                if (list.length === 1) {
                    if (typeof showToast === 'function') {
                        showToast('Please keep at least one transport mode selected.');
                    }
                    return;
                }
                list.splice(idx, 1);
            } else {
                list.push(norm);
            }

            window.currentLegModalSelectedModes = list;
            window.renderLegModalList();
        };

        window.applyLegVehicleSelection = function () {
            const legIdx = window.currentLegModalIdx;
            if (legIdx === null || legIdx === undefined) return;

            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            const toSpot = draft[legIdx];
            const isSiteUnderMaintenance = Boolean(toSpot && (toSpot.is_maintenance === 1 || toSpot.is_maintenance === true || toSpot.is_maintenance === '1'));
            if (isSiteUnderMaintenance) {
                if (typeof showToast === 'function') {
                    showToast('Transit cannot be configured while the destination is under maintenance.');
                }
                return;
            }

            const selectedModes = window.parseCompositeTransportModes(window.currentLegModalSelectedModes || []);
            if (selectedModes.length === 0) {
                if (typeof showToast === 'function') showToast('Please select at least one transport mode.');
                return;
            }

            let overrides = [];
            try {
                overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
            } catch (e) { overrides = []; }

            let totalCost = 0;
            selectedModes.forEach(m => {
                totalCost += window.calculateSingleLegCost(m, window.currentLegModalDistKm, window.currentLegModalMuniA, window.currentLegModalMuniB);
            });

            const displayNames = selectedModes.map(m => window.getVehicleDisplayName(m));

            let displayName = '';
            if (selectedModes.length === 1) {
                displayName = displayNames[0];
            } else if (selectedModes.length === 2) {
                const s1 = window.getVehicleShortName(selectedModes[0]);
                const s2 = window.getVehicleShortName(selectedModes[1]);
                const combo = `${s1} + ${s2}`;
                displayName = (combo.length <= 26) ? combo : '2 Vehicles Selected';
            } else {
                displayName = `${selectedModes.length} Vehicles Selected`;
            }

            overrides[legIdx] = {
                transport_mode: selectedModes.join(' + '),
                transport_modes: selectedModes,
                leg_cost: parseFloat(totalCost) || 0,
                leg_distance_km: parseFloat(window.currentLegModalDistKm) || 0,
                display_name: displayName,
                full_names: displayNames.join(', '),
                is_custom: true
            };

            localStorage.setItem('intan_elyu_draft_leg_vehicles', JSON.stringify(overrides));
            window.closeLegTransportModal();

            if (typeof window.renderItinerary === 'function') {
                window.renderItinerary(true);
            }
            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.updateDraftBudget === 'function') {
                window.updateDraftBudget(draft);
            }
            if (typeof window.updateDraftTravelModeBar === 'function') {
                window.updateDraftTravelModeBar();
            }
            if (typeof showToast === 'function') {
                const toastLabel = (selectedModes.length > 2) ? `${selectedModes.length} vehicles` : displayName;
                showToast(`Leg ${legIdx + 1} transit set to ${toastLabel}`);
            }
        };

        window.closeLegTransportModal = function () {
            const modal = document.getElementById('leg-transport-modal');
            if (modal) modal.style.display = 'none';

            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.remove('nav-hidden');
        };

        window.selectLegVehicle = function (legIdx, mode, cost, distKm) {
            window.currentLegModalIdx = legIdx;
            window.currentLegModalDistKm = distKm;
            window.currentLegModalSelectedModes = window.parseCompositeTransportModes(mode);
            window.applyLegVehicleSelection();
        };

        window.getNormalizedTripModes = function () {
            let modes = [];
            try {
                const rawArr = localStorage.getItem('intan_elyu_draft_trip_transports');
                if (rawArr) {
                    const parsed = JSON.parse(rawArr);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        modes = window.parseCompositeTransportModes(parsed);
                    }
                }
            } catch (e) {}

            if (modes.length === 0) {
                const raw = localStorage.getItem('intan_elyu_draft_trip_transport') || 'own_car';
                modes = window.parseCompositeTransportModes(raw);
            }

            if (modes.length === 0) {
                modes = ['own_car'];
            }

            // Auto-heal legacy or corrupted localStorage entries (e.g. "own_car_ + _motorcycle" or missing array)
            try {
                const cleanComposite = (modes.length === 1) ? modes[0] : modes.join(' + ');
                const storedRaw = localStorage.getItem('intan_elyu_draft_trip_transport');
                if (storedRaw !== cleanComposite) {
                    localStorage.setItem('intan_elyu_draft_trip_transport', cleanComposite);
                    localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify(modes));
                }
            } catch (e) {}

            return modes;
        };

        window.updateDraftTravelModeBar = function () {
            const bar = document.getElementById('draft-travel-mode-bar');
            if (!bar) return;
            const iconEl = document.getElementById('draft-travel-mode-icon');
            const labelEl = document.getElementById('draft-travel-mode-label');

            const tripModes = window.getNormalizedTripModes();

            if (tripModes.length === 1) {
                const mode = tripModes[0];
                if (iconEl) iconEl.innerHTML = window.getVehicleIconHtml(mode);
                if (labelEl) labelEl.textContent = window.getVehicleDisplayName(mode);
            } else if (tripModes.length === 2) {
                if (iconEl) iconEl.innerHTML = '<i class="fa-solid fa-shuffle" style="color:#00f2fe;"></i>';
                const s1 = window.getVehicleShortName(tripModes[0]);
                const s2 = window.getVehicleShortName(tripModes[1]);
                if (labelEl) labelEl.textContent = `${s1} + ${s2}`;
            } else {
                if (iconEl) iconEl.innerHTML = '<i class="fa-solid fa-route" style="color:#00f2fe;"></i>';
                const names = tripModes.map(m => window.getVehicleShortName(m)).join(', ');
                if (labelEl) labelEl.textContent = `${tripModes.length} Vehicles (${names})`;
            }
        };

        window.starterModalSelectedModes = [];

        window.openTravelModeStarterModal = function () {
            const modal = document.getElementById('travel-mode-starter-modal');
            if (!modal) return;

            const initialModes = window.getNormalizedTripModes();
            window.starterModalSelectedModes = [...initialModes];
            window.renderStarterModalCards();

            modal.style.display = 'flex';
            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.add('nav-hidden');

            setTimeout(() => {
                const firstSelected = modal.querySelector('.travel-starter-card.active');
                if (firstSelected) {
                    firstSelected.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 60);
        };

        window.renderStarterModalCards = function () {
            const modal = document.getElementById('travel-mode-starter-modal');
            if (!modal) return;

            const selected = (window.starterModalSelectedModes || ['own_car']).map(m => window.normalizeVehicleKey(m));

            const cards = modal.querySelectorAll('.travel-starter-card');
            cards.forEach(card => {
                const mode = window.normalizeVehicleKey(card.getAttribute('data-mode') || '');
                const badge = card.querySelector('.starter-active-badge');
                const checkIcon = card.querySelector('.starter-check-icon');
                const checkBox = card.querySelector('.starter-check-box');
                const isSelected = selected.includes(mode);

                if (isSelected) {
                    card.classList.add('active');
                    if (badge) badge.style.display = 'inline-block';
                    if (checkIcon) {
                        checkIcon.style.display = 'inline-block';
                        checkIcon.style.color = '#1e3a8a';
                    }
                    if (checkBox) {
                        checkBox.style.background = '#ffffff';
                        checkBox.style.borderColor = '#ffffff';
                    }
                } else {
                    card.classList.remove('active');
                    if (badge) badge.style.display = 'none';
                    if (checkIcon) checkIcon.style.display = 'none';
                    if (checkBox) {
                        checkBox.style.background = 'rgba(255,255,255,0.1)';
                        checkBox.style.borderColor = 'rgba(255,255,255,0.65)';
                    }
                }
            });

            // Update selection count badge
            const countEl = document.getElementById('starter-modal-selection-count');
            if (countEl) {
                countEl.textContent = `${selected.length} Selected`;
            }

            // Update footer label with proper display names
            const labelEl = document.getElementById('starter-modal-current-mode-label');
            if (labelEl) {
                if (selected.length === 1) {
                    labelEl.textContent = window.getVehicleDisplayName(selected[0]);
                } else if (selected.length === 2) {
                    const s1 = window.getVehicleShortName(selected[0]);
                    const s2 = window.getVehicleShortName(selected[1]);
                    labelEl.textContent = `${s1} + ${s2}`;
                } else {
                    const names = selected.map(m => window.getVehicleShortName(m)).join(', ');
                    labelEl.textContent = `${selected.length} Vehicles (${names})`;
                }
            }

            // Update Done button with prominent checkmark icon
            const btn = document.getElementById('btn-apply-starter-transport');
            if (btn) {
                btn.innerHTML = `<i class="fa-solid fa-check" style="color:#1e3a8a !important; font-size:12px;"></i> Done (${selected.length})`;
            }
        };

        window.toggleStarterVehicleMode = function (mode) {
            const norm = window.normalizeVehicleKey(mode);
            let list = (window.starterModalSelectedModes || []).map(m => window.normalizeVehicleKey(m)).filter(Boolean);

            const idx = list.indexOf(norm);
            if (idx > -1) {
                if (list.length === 1) {
                    if (typeof showToast === 'function') {
                        showToast('Please keep at least one vehicle selected.');
                    }
                    return;
                }
                list.splice(idx, 1);
            } else {
                list.push(norm);
            }

            window.starterModalSelectedModes = list;
            window.renderStarterModalCards();
        };

        window.applyStarterVehicleSelection = function () {
            const selected = window.parseCompositeTransportModes(window.starterModalSelectedModes || []);
            if (selected.length === 0) {
                if (typeof showToast === 'function') showToast('Please select at least one vehicle.');
                return;
            }

            const compositeMode = (selected.length === 1) ? selected[0] : selected.join(' + ');

            localStorage.setItem('intan_elyu_draft_trip_transport', compositeMode);
            localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify(selected));

            // Reset per-leg overrides so the whole itinerary cleanly inherits the newly chosen trip vehicle(s)
            localStorage.removeItem('intan_elyu_draft_leg_vehicles');

            const transInput = document.getElementById('trip-transport');
            if (transInput) transInput.value = compositeMode;

            window.updateDraftTravelModeBar();
            window.closeTravelModeStarterModal();

            if (typeof window.renderItinerary === 'function') {
                window.renderItinerary(true);
            }
            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }

            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            if (typeof window.updateDraftBudget === 'function') {
                window.updateDraftBudget(draft);
            }

            const chosenName = (selected.length === 1)
                ? window.getVehicleDisplayName(selected[0])
                : (selected.length === 2)
                    ? `${window.getVehicleShortName(selected[0])} + ${window.getVehicleShortName(selected[1])}`
                    : `${selected.length} Vehicles (${selected.map(m => window.getVehicleShortName(m)).join(', ')})`;

            if (draft.length === 0) {
                if (typeof showToast === 'function') {
                    showToast(`Vehicles set: ${chosenName}! Now add spots from the Map.`);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(`Trip transportation set to ${chosenName}`);
                }
            }
        };

        window.closeTravelModeStarterModal = function () {
            const modal = document.getElementById('travel-mode-starter-modal');
            if (!modal) return;
            modal.style.display = 'none';
            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.remove('nav-hidden');
        };

        window.selectTripTravelMode = function (mode) {
            window.starterModalSelectedModes = [mode];
            window.applyStarterVehicleSelection();
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
                const a = 0.5 - c((lat2 - lat1) * p) / 2 + c(lat1 * p) * c(lat2 * p) * (1 - c((lon2 - lon1) * p)) / 2;
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
                if (draftPlanCard) {
                    draftPlanCard.classList.add('is-hidden');
                    draftPlanCard.style.setProperty('display', 'none', 'important');
                }
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
            if (draftPlanCard) {
                draftPlanCard.classList.remove('is-hidden');
                draftPlanCard.style.setProperty('display', 'flex', 'important');
            }
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

            if (typeof window.updateDraftTravelModeBar === 'function') {
                window.updateDraftTravelModeBar();
            }

            // Step 1: If tourist has items in draft but hasn't picked a vehicle yet, prompt once
            if (rawDraft.length > 0 && !localStorage.getItem('intan_elyu_draft_trip_transport') && !sessionStorage.getItem('editing_itinerary_id') && !sessionStorage.getItem('intan_elyu_starter_prompted')) {
                sessionStorage.setItem('intan_elyu_starter_prompted', 'true');
                setTimeout(() => {
                    if (typeof window.openTravelModeStarterModal === 'function') {
                        window.openTravelModeStarterModal();
                    }
                }, 400);
            }

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

                const leg0Info = (typeof window.getLegTransportInfo === 'function')
                    ? window.getLegTransportInfo(0)
                    : { mode: 'own_car', name: 'Own Car', icon: 'fa-car', cost: 0 };

                const startDistText = startEta ? `${startEta.distanceText} &bull; ~${startEta.durationText}` : 'Start Leg';
                const fareBadge = leg0Info.is_maintenance
                    ? ''
                    : ((leg0Info.cost > 0)
                        ? `<span class="leg-fare-tag">₱${leg0Info.cost.toFixed(2)}</span>`
                        : `<span class="leg-fare-tag" style="color:#059669; background:rgba(16,185,129,0.1);">₱0</span>`);

                const leg0ChipClass = leg0Info.is_maintenance ? 'p2p-leg-chip leg-maintenance' : 'p2p-leg-chip';
                const leg0IconColor = leg0Info.is_maintenance ? '#ffffff' : '#0284c7';
                const leg0NameStyle = leg0Info.is_maintenance ? 'color:#ffffff; font-weight:800;' : '';
                const leg0DestName = (draft[0] && draft[0].name) ? draft[0].name.replace(/'/g, "\\'") : 'Destination';
                const leg0ClickAction = leg0Info.is_maintenance
                    ? `if(typeof showToast==='function') showToast('${leg0DestName} is currently under maintenance or temporarily closed. Vehicle transit is restricted.');`
                    : `window.openLegTransportModal(0)`;
                const leg0TrailingIcon = leg0Info.is_maintenance
                    ? `<i class="fa-solid fa-ban" style="color:#ffffff; font-size:10px; margin-left:2px; flex-shrink:0;"></i>`
                    : `<i class="fa-solid fa-chevron-right leg-action-edit" flex-shrink:0;"></i>`;

                startingLegHtml = `
            <div class="stops-swap-divider starting-leg-divider">
                <div class="stops-swap-line"></div>
                <div class="stops-leg-wrapper" style="display:flex; align-items:center; gap:8px; z-index:3;">
                    <div class="${leg0ChipClass}" onclick="${leg0ClickAction}" title="${leg0Info.is_maintenance ? 'Destination is under maintenance. Transport options are disabled.' : 'Selected: ' + (leg0Info.full_names || leg0Info.name)}">
                        <i class="fa-solid ${leg0Info.icon}" style="color:${leg0IconColor}; font-size:11px; flex-shrink:0;"></i>
                        <span class="leg-chip-name" style="${leg0NameStyle}">${leg0Info.name}</span>
                        ${fareBadge}
                        ${leg0Info.is_maintenance ? '' : `<span class="leg-dist-text">&bull; ${startDistText}</span>`}
                        ${leg0TrailingIcon}
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

                    const isMaint = Boolean(place.is_maintenance === 1 || place.is_maintenance === true || place.is_maintenance === '1');

                    if (isNextStop) {
                        nextStopBadge = `<span class="badge-next-stop" style="border:none !important; outline:none !important; box-shadow:none !important;"><i class="fa-solid fa-location-dot"></i> NEXT STOP</span>`;
                        if (!isMaint) {
                            const lat = place.lat || place.latitude;
                            const lng = place.lng || place.longitude;
                            const eta = window.getDistanceAndETA(lat, lng);
                            if (eta) {
                                nextStopEtaHtml = `
                            <div class="next-stop-distance-chip" id="itinerary-next-eta" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; border-radius:100px !important; padding:5px 13px !important; font-size:11px !important; font-weight:700 !important; display:inline-flex !important; align-items:center !important; gap:7px !important; margin-top:8px !important; box-shadow:0 2px 6px rgba(0,0,0,0.12) !important;">
                                <i class="fa-solid fa-route" style="color:#0284c7 !important; font-size:11px;"></i> 
                                <span style="color:#1e3a8a !important; font-weight:700; font-size:11px;">${eta.distanceText} away &bull; ~${eta.durationText} drive from your location</span>
                            </div>`;
                            } else {
                                nextStopEtaHtml = `
                            <div class="next-stop-distance-chip" id="itinerary-next-eta" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; border-radius:100px !important; padding:5px 13px !important; font-size:11px !important; font-weight:700 !important; display:inline-flex !important; align-items:center !important; gap:7px !important; margin-top:8px !important; box-shadow:0 2px 6px rgba(0,0,0,0.12) !important;">
                                <i class="fa-solid fa-location-arrow" style="color:#0284c7 !important; font-size:11px;"></i> 
                                <span style="color:#1e3a8a !important; font-weight:700; font-size:11px;">First destination on your itinerary route</span>
                            </div>`;
                            }
                        }
                    }

                    if (index > 0) {
                        const prevPlace = draft[index - 1];
                        const prevLat = prevPlace.lat || prevPlace.latitude;
                        const prevLng = prevPlace.lng || prevPlace.longitude;
                        const curLat = place.lat || place.latitude;
                        const curLng = place.lng || place.longitude;
                        const legEta = window.calculateLegETA(prevLat, prevLng, curLat, curLng);

                        const legInfo = (typeof window.getLegTransportInfo === 'function')
                            ? window.getLegTransportInfo(index)
                            : { mode: 'mpuj', name: 'Modern Jeepney', icon: 'fa-van-shuttle', cost: 0 };

                        const legDistText = legEta ? `${legEta.distanceText} &bull; ~${legEta.durationText}` : 'Leg Route';
                        const fareBadge = legInfo.is_maintenance
                            ? ''
                            : ((legInfo.cost > 0)
                                ? `<span class="leg-fare-tag">₱${legInfo.cost.toFixed(2)}</span>`
                                : `<span class="leg-fare-tag" style="color:#059669; background:rgba(16,185,129,0.1);">₱0</span>`);

                        const legChipClass = legInfo.is_maintenance ? 'p2p-leg-chip leg-maintenance' : 'p2p-leg-chip';
                        const legIconColor = legInfo.is_maintenance ? '#ffffff' : '#0284c7';
                        const legNameStyle = legInfo.is_maintenance ? 'color:#ffffff; font-weight:800;' : '';
                        const legDestName = (place && place.name) ? place.name.replace(/'/g, "\\'") : 'Destination';
                        const legClickAction = legInfo.is_maintenance
                            ? `if(typeof showToast==='function') showToast('${legDestName} is currently under maintenance or temporarily closed. Vehicle transit is restricted.');`
                            : `window.openLegTransportModal(${index})`;
                        const legTrailingIcon = legInfo.is_maintenance
                            ? `<i class="fa-solid fa-ban" style="color:#ffffff; font-size:10px; margin-left:2px; flex-shrink:0;"></i>`
                            : `<i class="fa-solid fa-chevron-right leg-action-edit" flex-shrink:0;"></i>`;

                        html += `
                        <div class="stops-swap-divider">
                            <div class="stops-swap-line"></div>
                            <div class="stops-leg-wrapper" style="display:flex; align-items:center; gap:8px; z-index:3;">
                                <div class="${legChipClass}" onclick="${legClickAction}" title="${legInfo.is_maintenance ? 'Destination is under maintenance. Transport options are disabled.' : 'Selected: ' + (legInfo.full_names || legInfo.name)}">
                                    <i class="fa-solid ${legInfo.icon}" style="color:${legIconColor}; font-size:11px; flex-shrink:0;"></i>
                                    <span class="leg-chip-name" style="${legNameStyle}">${legInfo.name}</span>
                                    ${fareBadge}
                                    ${legInfo.is_maintenance ? '' : `<span class="leg-dist-text">&bull; ${legDistText}</span>`}
                                    ${legTrailingIcon}
                                </div>
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
                                    <span class="time-label" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; padding:4px 11px !important; border-radius:100px !important; font-size:11px !important; font-weight:800 !important; letter-spacing:0.4px !important; text-transform:uppercase !important; box-shadow:0 2px 6px rgba(0,0,0,0.12) !important; display:inline-flex !important; align-items:center !important;">Stop ${index + 1} &bull; Approx ${timeStr}</span>
                                    ${nextStopBadge}
                                </div>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-grip-vertical" style="color:rgba(255,255,255,0.45); font-size:16px; cursor:grab; touch-action:none; padding:4px;" title="Drag to reorder"></i>
                                </div>
                            </div>
                            <div style="display:flex; gap:14px; align-items:flex-start;">
                                <div class="stop-thumbnail-wrapper">
                                    <img src="${r2ThumbUrl}" alt="${place.name}" loading="lazy" onerror="this.onerror=null; this.src='assets/img/no_image.svg';">
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
                            ${(() => {
                            const isNonDrivable = (place.accessible_by_private_vehicle === 0 || place.accessible_by_private_vehicle === false || place.accessible_by_private_vehicle === '0');
                            const isMaint = Boolean(place.is_maintenance === 1 || place.is_maintenance === true || place.is_maintenance === '1');
                            const acc = Array.isArray(place.accessible_vehicles) ? place.accessible_vehicles : [];
                            if (isMaint) {
                                return `<div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:8px;">
                                        <span style="padding:3px 9px; border-radius:100px; font-size:10px; font-weight:800; background:#ef4444 !important; color:#ffffff !important; border:none !important; outline:none !important; display:inline-flex; align-items:center; gap:5px; box-shadow:0 2px 6px rgba(239, 68, 68, 0.3);">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size:9.5px; color:#ffffff;"></i> Site Under Maintenance
                                        </span>
                                    </div>`;
                            }
                            if (isNonDrivable) {
                                return `<div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:8px;">
                                        <span style="padding:2px 8px; border-radius:100px; font-size:10px; font-weight:700; background:rgba(245,158,11,0.2); color:#fbbf24; border:none !important; outline:none !important; display:inline-flex; align-items:center; gap:4px;">
                                            <i class="fa-solid fa-triangle-exclamation" style="font-size:9px; color:#fbbf24;"></i> Trailhead Only (Non-Drivable)
                                        </span>
                                    </div>`;
                            }
                            if (acc.length > 0) {
                                return `<div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:8px;">
                                        <span style="padding:2px 8px; border-radius:100px; font-size:10px; font-weight:700; background:rgba(56,189,248,0.18); color:#7dd3fc; border:none !important; outline:none !important; display:inline-flex; align-items:center; gap:4px;">
                                            <i class="fa-solid fa-van-shuttle" style="font-size:9px; color:#38bdf8;"></i> ${acc.length} Available Vehicle${acc.length > 1 ? 's' : ''}
                                        </span>
                                    </div>`;
                            }
                            return '';
                        })()}
                        </div>
                    </div>
                </div>`;
                });
                html += `</div>`; // Close itinerary-stops-container
            }

            timeline.innerHTML = html;
            setupDragAndDrop(draft);

            if (typeof window.updateDraftBudget === 'function') {
                window.updateDraftBudget(draft);
            }

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
            const oldIdx = draft.findIndex(item => item.id.toString() === id.toString());
            draft = draft.filter(item => item.id.toString() !== id.toString());
            localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));

            try {
                let overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
                if (Array.isArray(overrides) && oldIdx >= 0) {
                    overrides.splice(oldIdx, 1);
                    localStorage.setItem('intan_elyu_draft_leg_vehicles', JSON.stringify(overrides));
                }
            } catch (e) {}

            window.renderItinerary();
            if (typeof showToast === 'function') showToast("Destination removed from itinerary");
        };

        window.clearAllItinerary = function () {
            localStorage.removeItem('intan_elyu_draft_itinerary');
            localStorage.removeItem('intan_elyu_draft_leg_vehicles');
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
                const isMaint = Boolean(nextPlace.is_maintenance === 1 || nextPlace.is_maintenance === true || nextPlace.is_maintenance === '1');
                const nextEtaEl = document.getElementById('itinerary-next-eta');
                if (nextEtaEl) {
                    if (isMaint) {
                        nextEtaEl.remove();
                    } else {
                        const pLat = nextPlace.lat || nextPlace.latitude;
                        const pLng = nextPlace.lng || nextPlace.longitude;
                        const eta = window.getDistanceAndETA(pLat, pLng);
                        if (eta) {
                            nextEtaEl.innerHTML = `<i class="fa-solid fa-route" style="color:#0284c7 !important; font-size:11px;"></i> <span style="color:#1e3a8a !important; font-weight:700; font-size:11px;">${eta.distanceText} away &bull; ~${eta.durationText} drive from your location</span>`;
                        }
                    }
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
                            try { draftMap.removeLayer(layer); } catch (err) { }
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

        function animateDonut(targetPct, color, customLabel) {
            if (_donutAnimFrame) cancelAnimationFrame(_donutAnimFrame);
            const donutEl = document.getElementById('modal-budget-donut');
            const pctEl = document.getElementById('modal-donut-pct');
            const startPct = _currentDonutPct;
            const startTime = performance.now();
            const duration = 650; // ms

            function step(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic
                const ease = 1 - Math.pow(1 - progress, 3);
                const pct = startPct + (targetPct - startPct) * ease;
                _currentDonutPct = pct;

                if (donutEl) {
                    const fillPct = Math.min(Math.max(pct, 0), 100);
                    donutEl.style.background = `conic-gradient(
                    ${color} 0% ${fillPct}%,
                    rgba(255,255,255,0.08) ${fillPct}% 100%
                )`;
                    donutEl.style.mask = 'radial-gradient(transparent 50%, black 51%)';
                    donutEl.style.webkitMask = 'radial-gradient(transparent 50%, black 51%)';
                }
                if (pctEl) {
                    if (customLabel !== undefined && customLabel !== null) {
                        pctEl.textContent = customLabel;
                    } else if (pct > 999) {
                        pctEl.textContent = '>999%';
                    } else if (targetPct > 0 && targetPct < 1 && pct < 1) {
                        pctEl.textContent = '<1%';
                    } else {
                        pctEl.textContent = Math.round(pct) + '%';
                    }
                    pctEl.style.color = color;
                }

                if (progress < 1) {
                    _donutAnimFrame = requestAnimationFrame(step);
                }
            }
            _donutAnimFrame = requestAnimationFrame(step);
        };

        window.getSpotMuniName = function (p) {
            if (!p) return '';
            if (typeof p.municipality === 'string') return p.municipality.trim();
            if (p.municipality && typeof p.municipality.name === 'string') return p.municipality.name.trim();
            if (typeof p.city === 'string') return p.city.trim();
            if (p.city && typeof p.city.name === 'string') return p.city.name.trim();
            return '';
        };

        window.computeItineraryTransCost = function (draft, transport) {
            if (!draft || draft.length === 0) return 0;

            let overrides = [];
            try {
                overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
            } catch (e) { overrides = []; }
            const hasOverrides = Array.isArray(overrides) && overrides.some(o => o && (o.transport_mode || o.transport_modes));

            const isNoVeh = !hasOverrides && (!transport || transport.toLowerCase().includes('no_vehicle') || transport.toLowerCase().includes('no vehicle'));
            if (isNoVeh) {
                window._draftBoundaryBreakdown = [];
                return 0;
            }

            window._draftBoundaryBreakdown = [];
            let totalTransit = 0;

            draft.forEach((place, idx) => {
                const legInfo = (typeof window.getLegTransportInfo === 'function')
                    ? window.getLegTransportInfo(idx)
                    : { mode: 'own_car', cost: 0, distance_km: 2.0 };

                const legCost = (legInfo && !legInfo.is_maintenance)
                    ? parseFloat(legInfo.cost || legInfo.leg_cost || 0)
                    : 0;
                totalTransit += legCost;

                const fromName = (idx === 0) ? 'Your Location' : draft[idx - 1].name;
                const toName = place.name;
                const distKm = parseFloat(legInfo.distance_km || 2.0);

                window._draftBoundaryBreakdown.push({
                    legIndex: idx + 1,
                    from: fromName,
                    to: toName,
                    distKm: distKm,
                    legTotal: legCost,
                    mode: legInfo.mode || legInfo.transport_mode || 'mpuj',
                    warning: legInfo.warning || null
                });
            });

            return Math.round(totalTransit * 100) / 100;
        };

        window.calculateModalBudget = function () {
            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

            const currentGlobalTransport = localStorage.getItem('intan_elyu_draft_trip_transport') || 'own_car';

            // Calculate Point-to-Point transit from draft legs using active transport mode
            const transCost = window.computeItineraryTransCost(draft, currentGlobalTransport);

            // Update Transport Mode summary card in the Save modal
            const p2pLabelEl = document.getElementById('save-p2p-transit-label');
            const p2pLegsEl = document.getElementById('save-p2p-legs-count');
            const p2pCostEl = document.getElementById('save-p2p-transit-cost');
            const p2pIconEl = document.getElementById('save-p2p-transit-icon');
            const transInput = document.getElementById('trip-transport');

            const legInfos = draft.map((_, i) => (typeof window.getLegTransportInfo === 'function') ? window.getLegTransportInfo(i) : null).filter(Boolean);

            // Extract the actual individual transport modes used across all valid legs
            const activeModes = [];
            legInfos.forEach(l => {
                if (!l || l.is_maintenance) return;
                const modes = window.parseCompositeTransportModes(l.transport_modes || l.transport_mode || l.mode);
                modes.forEach(m => {
                    if (m && !activeModes.includes(m)) {
                        activeModes.push(m);
                    }
                });
            });

            // Preserve the user-selected trip transportation in transInput (#trip-transport).
            // Do NOT let per-leg selections overwrite or add up into the trip transportation!
            const tripModes = (typeof window.getNormalizedTripModes === 'function')
                ? window.getNormalizedTripModes()
                : [currentGlobalTransport || 'own_car'];

            const compositeTripTransport = (tripModes.length === 1) ? tripModes[0] : tripModes.join(' + ');
            if (transInput) transInput.value = compositeTripTransport;

            if (p2pCostEl) {
                p2pCostEl.textContent = '₱' + transCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (p2pLegsEl) {
                p2pLegsEl.textContent = `${draft.length} Destination${draft.length > 1 ? 's' : ''} • ${draft.length} Leg${draft.length > 1 ? 's' : ''}`;
            }

            if (p2pLabelEl) {
                if (tripModes.length === 1) {
                    p2pLabelEl.textContent = (typeof window.getVehicleDisplayName === 'function')
                        ? window.getVehicleDisplayName(tripModes[0])
                        : tripModes[0].replace(/_/g, ' ').toUpperCase();
                } else if (tripModes.length === 2) {
                    const n1 = (typeof window.getVehicleShortName === 'function') ? window.getVehicleShortName(tripModes[0]) : tripModes[0];
                    const n2 = (typeof window.getVehicleShortName === 'function') ? window.getVehicleShortName(tripModes[1]) : tripModes[1];
                    p2pLabelEl.textContent = `${n1} + ${n2}`;
                } else {
                    const names = tripModes.map(m => (typeof window.getVehicleShortName === 'function') ? window.getVehicleShortName(m) : m).join(', ');
                    p2pLabelEl.textContent = `${tripModes.length} Vehicles (${names})`;
                }
            }

            // Sync icon in Save modal
            if (p2pIconEl) {
                if (tripModes.length === 1 && typeof window.getVehicleIconHtml === 'function') {
                    p2pIconEl.innerHTML = window.getVehicleIconHtml(tripModes[0]);
                } else if (tripModes.length > 1) {
                    p2pIconEl.innerHTML = '<i class="fa-solid fa-route" style="color:#ffffff; font-size:16px;"></i>';
                } else {
                    p2pIconEl.innerHTML = '<i class="fa-solid fa-car" style="color:#ffffff; font-size:16px;"></i>';
                }
            }

            // Sum Entrance Fees & Environmental Fees across all destinations in draft
            // NOTE: Sites under maintenance are closed/restricted and do NOT charge entrance or environmental fees (₱0.00).
            // Sites that are simply Closed by operating hours (e.g. 6am - 5pm outside operating hours) STILL get their site fees, option fees, and category.
            let feesTotal = 0;
            draft.forEach(p => {
                if (typeof window.isSpotUnderMaintenance === 'function' && window.isSpotUnderMaintenance(p)) {
                    return; // Exclude Under Maintenance
                }
                const adultFee = parseFloat(p.adult_fee || p.adultFee || 0);
                const generalEntrance = parseFloat(p.entrance_fee || p.entranceFee || p.fee || 0);
                const entrance = adultFee > 0 ? adultFee : generalEntrance;
                const env = parseFloat(p.environmental_fee || p.environmentalFee || p.envFee || 0);
                feesTotal += (isNaN(entrance) ? 0 : entrance) + (isNaN(env) ? 0 : env);
            });

            const estimatedCost = transCost + feesTotal;

            // Ensure save-budget-details is immediately shown when modal opens
            const detailsDiv = document.getElementById('save-budget-details');
            if (detailsDiv) {
                detailsDiv.style.display = 'block';
            }

            const costEl = document.getElementById('save-estimated-cost');
            if (costEl) {
                costEl.textContent = '₱' + estimatedCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const budgetInput = document.getElementById('trip-budget') ? document.getElementById('trip-budget').value : '';
            const budget = parseFloat(budgetInput);
            const remainingRow = document.getElementById('save-budget-remaining-row');
            const remainingLabel = document.getElementById('save-budget-remaining-label');
            const remainingVal = document.getElementById('save-budget-remaining-val');
            const donutWrapper = document.getElementById('modal-donut-wrapper');
            const pillGreen = document.getElementById('legend-pill-green');
            const pillOrange = document.getElementById('legend-pill-orange');
            const pillRed = document.getElementById('legend-pill-red');

            const setLegendStatus = (activeTier) => {
                if (!pillGreen || !pillOrange || !pillRed) return;
                pillGreen.style.opacity = (activeTier === 'green') ? '1' : '0.45';
                pillGreen.style.transform = (activeTier === 'green') ? 'scale(1.05)' : 'scale(1)';
                pillGreen.style.fontWeight = (activeTier === 'green') ? '800' : '600';

                pillOrange.style.opacity = (activeTier === 'orange') ? '1' : '0.45';
                pillOrange.style.transform = (activeTier === 'orange') ? 'scale(1.05)' : 'scale(1)';
                pillOrange.style.fontWeight = (activeTier === 'orange') ? '800' : '600';

                pillRed.style.opacity = (activeTier === 'red') ? '1' : '0.45';
                pillRed.style.transform = (activeTier === 'red') ? 'scale(1.05)' : 'scale(1)';
                pillRed.style.fontWeight = (activeTier === 'red') ? '800' : '600';
            };

            // Donut automatically shows when the modal is open
            if (donutWrapper) {
                donutWrapper.style.width = '60px';
                donutWrapper.style.marginRight = '16px';
                donutWrapper.style.opacity = '1';
                donutWrapper.style.transform = 'scale(1)';
            }

            if (!budgetInput || isNaN(budget) || budget <= 0) {
                // No budget entered yet — show 100% cyan ring outline and reset remaining row
                animateDonut(100, '#00f2fe', '100%');
                if (remainingRow) remainingRow.style.display = 'none';
                if (pillGreen) { pillGreen.style.opacity = '0.6'; pillGreen.style.transform = 'scale(1)'; pillGreen.style.fontWeight = '700'; }
                if (pillOrange) { pillOrange.style.opacity = '0.6'; pillOrange.style.transform = 'scale(1)'; pillOrange.style.fontWeight = '700'; }
                if (pillRed) { pillRed.style.opacity = '0.6'; pillRed.style.transform = 'scale(1)'; pillRed.style.fontWeight = '700'; }
                return;
            }

            // Standard Budget Consumption Percentage: (Total Estimated Cost / User Budget) * 100
            const spentPct = (budget > 0) ? (estimatedCost / budget) * 100 : 0;
            const remaining = budget - estimatedCost;
            const absRemaining = Math.abs(remaining);
            const formattedRemaining = '₱' + absRemaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let fillColor = '#10b981'; // Green: Within Budget (<80%)
            let remainingLabelHtml = '<i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Within Budget';
            let remainingValueText = '+' + formattedRemaining + ' left';
            let remainingColor = '#10b981';
            let activeTier = 'green';

            if (estimatedCost > budget) {
                // RED: Over budget (> 100%)
                fillColor = '#ef4444';
                remainingLabelHtml = '<i class="fa-solid fa-circle-xmark" style="margin-right:4px;"></i> Over Budget';
                remainingValueText = '-' + formattedRemaining;
                remainingColor = '#ef4444';
                activeTier = 'red';
            } else if (spentPct >= 80) {
                // ORANGE: Nearing the limit (80% - 100%)
                fillColor = '#f59e0b';
                remainingLabelHtml = '<i class="fa-solid fa-triangle-exclamation" style="margin-right:4px;"></i> Nearing Limit';
                remainingValueText = (remaining === 0 ? '₱0.00 left' : '+' + formattedRemaining + ' left');
                remainingColor = '#f59e0b';
                activeTier = 'orange';
            } else {
                // GREEN: Within budget (< 80%)
                fillColor = '#10b981';
                remainingLabelHtml = '<i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Within Budget';
                remainingValueText = '+' + formattedRemaining + ' left';
                remainingColor = '#10b981';
                activeTier = 'green';
            }

            setLegendStatus(activeTier);

            // Animate donut fill smoothly with color-coded status
            animateDonut(spentPct, fillColor);

            // Update remaining row
            if (remainingRow) {
                remainingRow.style.display = 'flex';
                remainingLabel.innerHTML = remainingLabelHtml;
                remainingLabel.style.color = remainingColor;
                remainingVal.style.color = remainingColor;
                remainingVal.textContent = remainingValueText;
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
            const remainingRow = document.getElementById('save-budget-remaining-row');
            if (remainingRow) remainingRow.style.display = 'none';
            const pillGreen = document.getElementById('legend-pill-green');
            const pillOrange = document.getElementById('legend-pill-orange');
            const pillRed = document.getElementById('legend-pill-red');
            if (pillGreen) { pillGreen.style.opacity = '0.6'; pillGreen.style.transform = 'scale(1)'; pillGreen.style.fontWeight = '700'; }
            if (pillOrange) { pillOrange.style.opacity = '0.6'; pillOrange.style.transform = 'scale(1)'; pillOrange.style.fontWeight = '700'; }
            if (pillRed) { pillRed.style.opacity = '0.6'; pillRed.style.transform = 'scale(1)'; pillRed.style.fontWeight = '700'; }
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
            // Check if there was an existing draft with added spots before entering edit mode
            const backupDraftRaw = localStorage.getItem('intan_elyu_pre_edit_backup_draft') || sessionStorage.getItem('intan_elyu_pre_edit_backup_draft');
            let restored = false;

            if (backupDraftRaw) {
                try {
                    const parsed = JSON.parse(backupDraftRaw);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        localStorage.setItem('intan_elyu_draft_itinerary', backupDraftRaw);
                        restored = true;
                    } else {
                        localStorage.removeItem('intan_elyu_draft_itinerary');
                    }
                } catch (e) {
                    localStorage.removeItem('intan_elyu_draft_itinerary');
                }
            } else {
                // If there were no prior added spots in itinerary, go back to empty state!
                localStorage.removeItem('intan_elyu_draft_itinerary');
            }

            // Clean up pre-edit backup
            localStorage.removeItem('intan_elyu_pre_edit_backup_draft');
            sessionStorage.removeItem('intan_elyu_pre_edit_backup_draft');

            // Clear editing trip session keys
            sessionStorage.removeItem('editing_itinerary_id');
            sessionStorage.removeItem('editing_trip_title');
            sessionStorage.removeItem('editing_trip_date');
            sessionStorage.removeItem('editing_trip_budget');
            sessionStorage.removeItem('editing_trip_transport');

            if (typeof showToast === 'function') {
                showToast(restored ? "Restored your draft itinerary." : "Exited trip edit mode.");
            }

            window.renderItinerary();
        };

        window.openSaveModal = function (e) {
            if (e && typeof e.preventDefault === 'function') e.preventDefault();
            try {
                const draft = window.getEffectiveDraft ? window.getEffectiveDraft() : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

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
                        const display = document.getElementById('custom-date-display') || document.getElementById('trip-date-display');
                        if (display) {
                            const formatted = savedDate ? new Date(savedDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : 'Select trip date';
                            if ('value' in display) display.value = formatted;
                            display.textContent = formatted;
                        }
                        const clearLink = document.getElementById('calendar-clear-link');
                        if (clearLink) clearLink.style.display = savedDate ? 'inline' : 'none';
                    }
                    const budgetInput = document.getElementById('trip-budget');
                    if (budgetInput) {
                        budgetInput.value = (savedBudget !== null && savedBudget !== undefined && savedBudget !== '') ? savedBudget : '';
                    }
                    if (savedTransport) {
                        const transEl = document.getElementById('trip-transport');
                        const isNoVeh = savedTransport.toLowerCase().includes('no_vehicle') || savedTransport.toLowerCase().includes('no vehicle');
                        if (transEl) transEl.value = isNoVeh ? '' : savedTransport;
                    }
                } else {
                    if (modalTitleEl) {
                        modalTitleEl.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="color:#38bdf8; font-size:18px;"></i> Save Your Trip';
                    }
                    if (submitBtn) {
                        submitBtn.textContent = 'Save Trip';
                    }
                }


                // Calculate Point-to-Point transit budget and populate summary card
                if (typeof window.calculateModalBudget === 'function') {
                    window.calculateModalBudget();
                }

                // Initialize calendar to current month
                const today = new Date();
                if (window.calendarState) {
                    window.calendarState.currentYear = today.getFullYear();
                    window.calendarState.currentMonth = today.getMonth();
                }
                if (typeof window.renderCalendarGrid === 'function') {
                    window.renderCalendarGrid();
                }

                const modal = document.getElementById('save-trip-modal');
                if (modal) modal.style.display = 'flex';

                if (typeof window.calculateModalBudget === 'function') {
                    window.calculateModalBudget();
                }

                // Hide bottom nav while modal is open
                const bottomNav = document.getElementById('bottom-navigation');
                if (bottomNav) bottomNav.classList.add('nav-hidden');
            } catch (err) {
                console.error("Error in openSaveModal:", err);
                const modal = document.getElementById('save-trip-modal');
                if (modal) modal.style.display = 'flex';
            }
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
            const isNoVeh = !transport || !transport.trim() || transport.toLowerCase().includes('no_vehicle') || transport.toLowerCase().includes('no vehicle');
            const effectiveTransport = isNoVeh ? 'No Vehicle Selected' : transport.trim();

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

                const legTransports = draft.map((place, idx) => {
                    const legInfo = (typeof window.getLegTransportInfo === 'function')
                        ? window.getLegTransportInfo(idx)
                        : { mode: effectiveTransport, cost: 0, distance_km: 0 };
                    return {
                        leg_index: idx + 1,
                        to_spot_id: parseInt(place.id || place.tourist_spot_id || 0),
                        transport_mode: legInfo.mode || legInfo.transport_mode || effectiveTransport,
                        leg_cost: parseFloat(legInfo.cost || legInfo.leg_cost || 0),
                        leg_distance_km: parseFloat(legInfo.distance_km || 0)
                    };
                });

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
                        transport_mode: effectiveTransport,
                        leg_transports: legTransports
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
                        sessionStorage.setItem('active_trip_transport_' + savedId, effectiveTransport);
                        localStorage.setItem('selected_trip_vehicle_' + savedId, effectiveTransport);
                    }

                    showToast(editingId ? "Trip updated successfully!" : "Trip saved successfully!");

                    // If the user updated an edited trip, restore their original pre-edit draft (if any was saved)
                    const backupDraftRaw = localStorage.getItem('intan_elyu_pre_edit_backup_draft') || sessionStorage.getItem('intan_elyu_pre_edit_backup_draft');
                    if (editingId && backupDraftRaw) {
                        try {
                            const parsed = JSON.parse(backupDraftRaw);
                            if (Array.isArray(parsed) && parsed.length > 0) {
                                localStorage.setItem('intan_elyu_draft_itinerary', backupDraftRaw);
                            } else {
                                localStorage.removeItem('intan_elyu_draft_itinerary');
                            }
                        } catch (e) {
                            localStorage.removeItem('intan_elyu_draft_itinerary');
                        }
                    } else {
                        localStorage.removeItem('intan_elyu_draft_itinerary');
                    }
                    localStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                    sessionStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                    localStorage.removeItem('intan_elyu_draft_leg_vehicles');

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
                        try { draftMap.removeLayer(layer); } catch (e) { }
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
                                const p2 = `${fetchLatLngs[k + 1][1]},${fetchLatLngs[k + 1][0]}`;
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
            if (!draft) draft = (typeof window.getEffectiveDraft === 'function') ? window.getEffectiveDraft() : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            let actCost = 0, foodCost = 0, transCost = 0;
            // NOTE: Sites under maintenance are closed/restricted and do NOT charge entrance, activity or food fees (₱0.00).
            // Sites that are simply Closed by operating hours (e.g. 6am - 5pm outside operating hours) STILL get their site fees, option fees, and category.
            draft.forEach(item => {
                if (typeof window.isSpotUnderMaintenance === 'function' && window.isSpotUnderMaintenance(item)) {
                    return; // Exclude Under Maintenance
                }
                const adult = parseFloat(item.adult_fee || item.adultFee || 0);
                const gen = parseFloat(item.entrance_fee || item.fee || 0);
                actCost += (adult > 0 ? adult : gen);
                foodCost += parseFloat(item.avg_food_cost) || 150;
            });

            // Calculate dynamic transit cost based on current draft sequence and selected transport
            const curTransport = localStorage.getItem('intan_elyu_draft_trip_transport') || document.getElementById('trip-transport')?.value || 'own_car';

            if (typeof window.computeItineraryTransCost === 'function') {
                transCost = window.computeItineraryTransCost(draft, curTransport);
            } else {
                draft.forEach(item => {
                    transCost += parseFloat(item.avg_transport_cost) || 0;
                });
            }

            const total = actCost + foodCost + transCost;
            window.setTxt('main-budget-total', '₱' + total.toLocaleString(undefined, { minimumFractionDigits: 2 }));
            window.setTxt('main-cost-trans', '₱' + transCost.toFixed(2));
            window.setTxt('main-cost-food', '₱' + foodCost.toFixed(2));
            window.setTxt('main-cost-act', '₱' + actCost.toFixed(2));

            window.updateDonutChart('main-budget-donut', transCost, foodCost, actCost);
        };

        window.renderRailwayVehicleOptions = function (type) {
            const slider = document.getElementById('transport-slider');
            if (!slider) return;

            let optionsList = [];
            const draft = window.getEffectiveDraft ? window.getEffectiveDraft() : [];

            // Helper to determine if a vehicle type is allowed for a spot's accessible vehicles
            // If no vehicles are added to a site, motorized vehicles are restricted/unavailable
            const isVehAllowed = (vehKey, vehVal, accVehicles) => {
                if (!Array.isArray(accVehicles) || accVehicles.length === 0) return false;
                const list = accVehicles.map(v => String(v).toLowerCase().trim());
                if (vehVal === 'own_car' || vehKey === 'car') return list.some(v => v.includes('car'));
                if (vehVal === 'taxi' || vehKey === 'taxi') return list.some(v => v.includes('taxi'));
                if (vehVal === 'van' || vehKey === 'van') return list.some(v => v.includes('van'));
                if (vehVal === 'motorcycle' || vehKey === 'motorcycle') return list.some(v => v.includes('motorcycle') || v.includes('motor'));
                if (vehVal === 'mpuj' || vehKey === 'mpuj') return list.some(v => v.includes('mpuj') || (v.includes('modern') && v.includes('jeep')));
                if (vehVal === 'tpuj' || vehKey === 'tpuj') return list.some(v => v.includes('tpuj') || v.includes('traditional') || (v.includes('jeep') && !v.includes('modern')));
                if (vehVal === 'pub_aircon' || vehKey === 'pub_aircon') return list.some(v => v.includes('pub_aircon') || v.includes('aircon') || v.includes('bus'));
                if (vehVal === 'pub_ordinary' || vehKey === 'pub_regular') return list.some(v => v.includes('pub_regular') || v.includes('ordinary') || v.includes('regular') || v.includes('bus'));
                if (vehVal === 'tricycle' || vehKey === 'tricycle') return list.some(v => v.includes('tricycle') || v.includes('trike'));
                if (vehVal === 'uve' || vehKey === 'uve') return list.some(v => v.includes('uve') || v.includes('van'));
                return list.some(v => v.includes(vehKey) || v.includes(vehVal));
            };

            const resolveSpotVehicleInfo = (p) => {
                const spotId = String(p.id || p.tourist_spot_id || '');
                const cachedSpot = (window._cachedMapSpots && window._cachedMapSpots[spotId]) ? window._cachedMapSpots[spotId] : null;

                let accVeh = (Array.isArray(p.accessible_vehicles) && p.accessible_vehicles.length > 0)
                    ? p.accessible_vehicles
                    : (cachedSpot && Array.isArray(cachedSpot.accessible_vehicles) ? cachedSpot.accessible_vehicles : (p.accessible_vehicles || []));

                let hasVeh = (p.has_available_vehicles !== undefined)
                    ? p.has_available_vehicles
                    : (cachedSpot && cachedSpot.has_available_vehicles !== undefined ? cachedSpot.has_available_vehicles : (Array.isArray(accVeh) && accVeh.length > 0));

                return {
                    accessible_vehicles: accVeh,
                    has_available_vehicles: Boolean(hasVeh) && Array.isArray(accVeh) && accVeh.length > 0
                };
            };

            const hasNonDrivableSpot = draft.some(p => p && (p.accessible_by_private_vehicle === 0 || p.accessible_by_private_vehicle === false || p.accessible_by_private_vehicle === '0'));

            if (type === 'private') {
                optionsList = [
                    { 
                        val: 'own_car', 
                        name: 'Own Car', 
                        icon: 'fa-car', 
                        key: 'car', 
                        available: true,
                        badgeHtml: hasNonDrivableSpot ? '<span style="padding:1px 6px; border-radius:100px; font-size:8px; font-weight:800; background:rgba(245,158,11,0.25); color:#fbbf24;">Trailhead</span>' : ''
                    },
                    { val: 'motorcycle', name: 'Motorcycle', icon: 'fa-motorcycle', key: 'motorcycle', available: true },
                    { val: 'van', name: 'Van', icon: 'fa-shuttle-van', key: 'van', available: true },
                    { val: 'tricycle', name: 'Tricycle', icon: 'fa-motorcycle', key: 'tricycle', available: true }
                ];
            } else {
                const rawMunis = draft.map(p => (typeof window.getSpotMuniName === 'function' ? window.getSpotMuniName(p) : ((typeof p.municipality === 'string' ? p.municipality : p.municipality?.name) || ''))).filter(Boolean);
                const uniqueMunis = [...new Set(rawMunis.map(m => m.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim().toLowerCase()))];
                const isInterMunicipal = uniqueMunis.length > 1;

                optionsList = [
                    { val: 'mpuj', name: 'Modern Jeepney (MPUJ)', icon: 'fa-van-shuttle', available: true, key: 'mpuj' },
                    { val: 'tpuj', name: 'Traditional Jeepney (TPUJ)', icon: 'fa-van-shuttle', available: true, key: 'tpuj' },
                    { val: 'pub_aircon', name: 'Aircon Bus (PUB)', icon: 'fa-bus', available: true, key: 'pub_aircon' },
                    { val: 'pub_ordinary', name: 'Ordinary Bus (PUB)', icon: 'fa-bus-simple', available: true, key: 'pub_regular' },
                    { 
                        val: 'tricycle', 
                        name: 'Tricycle', 
                        icon: 'fa-motorcycle', 
                        available: true, 
                        key: 'tricycle',
                        badgeHtml: isInterMunicipal ? '<span style="padding:1px 6px; border-radius:100px; font-size:8px; font-weight:800; background:rgba(56,189,248,0.25); color:#7dd3fc;">Local Legs</span>' : ''
                    },
                    { val: 'uve', name: 'UV Express / Van', icon: 'fa-shuttle-van', available: true, key: 'uve' },
                    { val: 'taxi', name: 'Taxi', icon: 'fa-taxi', available: true, key: 'taxi' }
                ];
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

            // Add No Vehicle as the first, explicit choice
            unique.unshift({
                val: 'no_vehicle',
                name: 'No Vehicle',
                icon: 'fa-ban',
                available: true
            });

            let currentSelected = (document.getElementById('trip-transport').value || '').split(',').filter(Boolean);
            currentSelected = currentSelected.map(v => {
                if (v === 'jeepney') return 'mpuj';
                if (v === 'private_bus' || v === 'bus') return 'pub_aircon';
                return v;
            });

            const isNoVehCurrent = currentSelected.length === 0 || currentSelected.some(v => v.includes('no_vehicle') || v.includes('no vehicle'));

            // Clean trip-transport to drop any vehicle that is now unavailable
            const availKeys = unique.filter(o => o.available !== false).map(o => o.val);
            const validSelected = isNoVehCurrent
                ? ['no_vehicle']
                : currentSelected.filter(v => availKeys.includes(v) && v !== 'no_vehicle');

            if (isNoVehCurrent) {
                document.getElementById('trip-transport').value = '';
            } else if (validSelected.length !== currentSelected.length) {
                document.getElementById('trip-transport').value = validSelected.join(',');
            }

            let html = '';
            unique.forEach(opt => {
                const isAvail = opt.available !== false;
                const isActive = (isAvail && validSelected.includes(opt.val)) ? 'active' : '';
                const disabledClass = !isAvail ? 'disabled-transport' : '';
                const badgeHtml = !isAvail ? '<span class="trans-badge-unavailable">Unavailable</span>' : (opt.badgeHtml || '');

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

        // Attach direct click listener to Save Draft Plan button
        const fabBtn = document.getElementById('btn-save-itinerary');
        if (fabBtn) {
            fabBtn.addEventListener('click', function (e) {
                if (e) e.preventDefault();
                window.openSaveModal(e);
            });
        }

        // Initial render on load
        if (window.renderItinerary) {
            window.renderItinerary();
        }
    })();
</script>