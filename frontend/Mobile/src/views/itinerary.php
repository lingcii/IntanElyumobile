<?php
$pageTitle = 'My Itinerary';
$activeTab = 'itinerary';
$offlineMunicipalityGeoJson = json_decode((string) @file_get_contents(__DIR__ . '/../assets/la_union_municipalities.json'), true)
    ?: ['type' => 'FeatureCollection', 'features' => []];

// Load spots map with R2 photo_urls and full active spots list for instant Step 2 Slider
$spotsPhotoMap = [];
$allSpotsList = [];
try {
    if (class_exists('App\Models\TouristSpot')) {
        $spots = App\Models\TouristSpot::with('municipality:id,name')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->get(['id', 'name', 'municipality_id', 'barangay', 'category', 'entrance_fee', 'environmental_fee', 'photo_url', 'latitude', 'longitude', 'rating', 'description', 'opening_time', 'closing_time', 'is_open_24_hours']);
        foreach ($spots as $sp) {
            $muniName = ($sp->municipality && $sp->municipality->name) ? $sp->municipality->name : 'La Union';
            $spotData = [
                'id' => $sp->id,
                'name' => $sp->name,
                'category' => $sp->category ?? 'Attractions',
                'municipality' => $muniName,
                'location' => $sp->barangay ? $sp->barangay . ', ' . $muniName : $muniName,
                'photo_url' => $sp->photo_url,
                'latitude' => (float) $sp->latitude,
                'longitude' => (float) $sp->longitude,
                'price' => (float) ($sp->entrance_fee ?? 0),
                'environmental_fee' => (float) ($sp->environmental_fee ?? 0),
                'rating' => (float) ($sp->rating ?? ($sp->reviews_avg_rating ?? 0)),
                'description' => $sp->description ?? '',
                'opening_time' => $sp->opening_time ?? '',
                'closing_time' => $sp->closing_time ?? '',
                'is_open_24_hours' => (bool) $sp->is_open_24_hours,
            ];
            $allSpotsList[] = $spotData;
            if ($sp->photo_url) {
                $spotsPhotoMap[$sp->id] = [
                    'photo_url' => $sp->photo_url,
                    'name' => $sp->name
                ];
            }
        }
    }
} catch (\Throwable $e) {
}
?>
<script>
    window.SPOTS_R2_MAP = <?= json_encode($spotsPhotoMap) ?>;
    window.ALL_TOURIST_SPOTS = <?= json_encode($allSpotsList) ?>;
    window.OFFLINE_MUNICIPALITIES_GEOJSON = <?= json_encode($offlineMunicipalityGeoJson) ?>;
</script>



<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/trip_guide_modal.php'; ?>



<div class="itinerary-container has-bottom-nav" style="padding-top: calc(max(env(safe-area-inset-top, 0px), 36px) + 116px) !important; padding-bottom: calc(max(env(safe-area-inset-bottom, 0px), 18px) + 210px) !important; --page-bottom-padding: calc(max(env(safe-area-inset-bottom, 0px), 18px) + 210px) !important; transform: none !important; animation: none !important;">

    <style>
        /* 3-Step Page-Level Itinerary Wizard Styles */
        .itinerary-step-panel {
            display: none;
            animation: itinFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .itinerary-step-panel.active {
            display: block !important;
        }
        @keyframes itinFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .itin-stepper-track {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
            min-width: 0;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 3px;
            box-sizing: border-box;
            gap: 2px;
        }
        .itin-page-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            user-select: none;
            padding: 5px 8px;
            border-radius: 9px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap !important;
            flex: 1;
            min-width: 0;
            -webkit-tap-highlight-color: transparent;
        }
        .itin-page-tab .itin-step-circle {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .itin-page-tab .itin-step-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #64748b;
            transition: all 0.2s ease;
            white-space: nowrap !important;
            letter-spacing: -0.2px;
        }
        .itin-page-tab.active {
            background: #ffffff !important;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08) !important;
        }
        .itin-page-tab.active .itin-step-circle {
            background: #1e3a8a !important;
            color: #ffffff !important;
        }
        .itin-page-tab.active .itin-step-title {
            color: #1e3a8a !important;
            font-weight: 800 !important;
        }
        .itin-page-tab.completed .itin-step-circle {
            background: #10b981 !important;
            color: #ffffff !important;
        }
        .itin-page-tab.completed .itin-step-title {
            color: #059669 !important;
            font-weight: 700 !important;
        }

        .wizard-trans-card {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 14px;
            padding: 7px 12px;
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }
        .wizard-trans-card:hover {
            border-color: #0284c7;
            background: #f0f9ff;
        }
        .wizard-trans-card.active {
            background: #f0f9ff !important;
            border-color: #0284c7 !important;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.16);
        }
        .wizard-trans-card.active .wizard-check-radio {
            border-color: #0284c7;
            background: #0284c7;
        }
        .wizard-trans-card.active .wizard-check-radio i {
            display: block !important;
        }

        #custom-calendar-dropdown {
            background: #ffffff !important;
            border: 1.5px solid #0284c7 !important;
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.12) !important;
            border-radius: 18px !important;
            overflow: visible !important;
            max-height: none !important;
        }
        .calendar-day-cell {
            color: #0f172a !important;
            font-weight: 700 !important;
        }

        /* Suggested Stops Slider & Category Pills */
        .itin-cat-pill {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 5px 12px;
            border-radius: 100px;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .itin-cat-pill:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .itin-cat-pill.active {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.25);
        }
        .itin-spot-slider-card {
            flex: 0 0 148px;
            width: 148px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            scroll-snap-align: start;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
            position: relative;
        }
        .itin-spot-slider-card:active {
            transform: scale(0.98);
        }
        .btn-slider-add-spot {
            width: 100%;
            margin-top: 6px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            padding: 6px 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all 0.2s ease;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Prevent floating ? button from overlapping bottom CTA buttons */
        #btn-floating-trip-guide {
            display: none !important;
        }

        /* Blur bottom navigation bar when spot details modal is active */
        body.spot-detail-modal-open #bottom-navigation,
        html.spot-detail-modal-open #bottom-navigation,
        body.spot-detail-modal-open #magic-nav,
        html.spot-detail-modal-open #magic-nav,
        body.spot-detail-modal-open .magic-nav,
        html.spot-detail-modal-open .magic-nav {
            pointer-events: none !important;
            filter: blur(14px) brightness(0.65) !important;
            -webkit-filter: blur(14px) brightness(0.65) !important;
            opacity: 0.28 !important;
            z-index: 100 !important;
            transition: filter 0.25s ease, opacity 0.25s ease !important;
        }

        /* Fixed Bottom Action Bar: Intact directly above bottom navigation bar */
        #itin-fixed-bottom-bar {
            position: fixed !important;
            bottom: calc(max(18px, env(safe-area-inset-bottom, 18px)) + 66px + 8px) !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: calc(100% - clamp(16px, 4vw, 32px)) !important;
            max-width: 448px !important;
            z-index: 9400 !important;
            pointer-events: auto !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease !important;
        }

        body.keyboard-open #itin-fixed-bottom-bar,
        html.keyboard-open #itin-fixed-bottom-bar,
        body.modal-blurred #itin-fixed-bottom-bar,
        html.modal-blurred #itin-fixed-bottom-bar,
        body.spot-detail-modal-open #itin-fixed-bottom-bar,
        html.spot-detail-modal-open #itin-fixed-bottom-bar,
        body.trip-guide-open #itin-fixed-bottom-bar,
        html.trip-guide-open #itin-fixed-bottom-bar {
            opacity: 0 !important;
            pointer-events: none !important;
            visibility: hidden !important;
            transform: translate(-50%, 40px) !important;
        }

        /* Prevent fixed wizard header overlap when virtual keyboard opens or scrolling */
        html, body, .itinerary-container {
            scroll-padding-top: calc(max(env(safe-area-inset-top, 0px), 36px) + 124px) !important;
            scroll-padding-bottom: 90px !important;
        }

        #companion-stepper-box,
        #companion-ages-section,
        .companion-age-row,
        .companion-age-input {
            scroll-margin-top: calc(max(env(safe-area-inset-top, 0px), 36px) + 128px) !important;
            scroll-margin-bottom: 90px !important;
        }

    </style>

    <!-- 3-Step Wizard Progress Stepper Header Bar (Fixed) -->
    <div class="itinerary-header-bar" style="position:fixed; top:calc(max(env(safe-area-inset-top, 0px), 36px) + 48px); left:50%; transform:translateX(-50%); width:100%; max-width:480px; z-index:9990; background:#ffffff; padding:8px 12px; box-sizing:border-box; display:flex; justify-content:space-between; align-items:center; gap:8px; border-bottom:1px solid #e2e8f0; border-radius:0 0 16px 16px; box-shadow:0 3px 12px rgba(15,23,42,0.05);">
        
        <!-- Squeezed 3-Step Track -->
        <div class="itin-stepper-track">
            <!-- Step 1 Tab -->
            <div class="itin-page-tab active" id="itin-tab-1" onclick="window.switchItineraryStep(1)">
                <span class="itin-step-circle" id="itin-circle-1">1</span>
                <span class="itin-step-title" id="itin-label-1">Plan</span>
            </div>
            
            <!-- Step 2 Tab -->
            <div class="itin-page-tab" id="itin-tab-2" onclick="window.proceedToStep(2)">
                <span class="itin-step-circle" id="itin-circle-2">2</span>
                <span class="itin-step-title" id="itin-label-2">Stops (<span id="itinerary-count">0</span>)</span>
            </div>
            
            <!-- Step 3 Tab -->
            <div class="itin-page-tab" id="itin-tab-3" onclick="window.proceedToStep(3)">
                <span class="itin-step-circle" id="itin-circle-3">3</span>
                <span class="itin-step-title" id="itin-label-3">Ride</span>
            </div>
        </div>

        <!-- Header Actions: Saved Trips & Guide (?) -->
        <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
            <button type="button" onclick="navigateTo('saved_trips')" title="Saved Trips" aria-label="Saved Trips"
                style="background:#f1f5f9; border:none; outline:none; color:#1e3a8a; width:30px; height:30px; border-radius:50%; font-size:12px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s ease;">
                <i class="fa-solid fa-bookmark"></i>
            </button>
            <button type="button" onclick="window.openTripGuideModal()" title="Trip Guide: How to create and complete a trip" aria-label="Trip Guide"
                style="background:#f1f5f9; border:none; outline:none; color:#1e3a8a; width:30px; height:30px; border-radius:50%; font-size:12px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s ease;">
                <i class="fa-solid fa-question"></i>
            </button>
        </div>
    </div>

    <!-- Floating '?' Guide Button -->
    <button type="button" id="btn-floating-trip-guide" class="floating-trip-guide-btn"
        onclick="window.openTripGuideModal()" title="Trip Guide: How to add and complete a trip"
        aria-label="Trip Guide">
        <i class="fa-solid fa-question"></i>
    </button>

    <!-- Active Editing Trip Banner (Shown in all steps when editing an existing trip) -->
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

    <!-- ============================================================ -->
    <!-- STEP 1: TRIP SCOPE & PARAMETERS                              -->
    <!-- ============================================================ -->
    <div id="itin-step-1" class="itinerary-step-panel active">
        <div class="scope-card" style="background:#ffffff; border-radius:20px; padding:18px; border:1px solid #e2e8f0; box-shadow:0 4px 16px rgba(15,23,42,0.04); display:flex; flex-direction:column;">
            
            <div style="margin-bottom:16px;">
                <h3 style="margin:0; font-size:15.5px; font-weight:800; color:#0f172a;">Trip Plan</h3>
                <p style="margin:1px 0 0; font-size:11px; color:#64748b;">Set your adventure name, dates, base hub, and party.</p>
            </div>

            <!-- Trip Name Input -->
            <label style="font-size:12.5px; color:#1e293b !important; margin-bottom:6px; display:block; font-weight:700;">
                Trip Name <span style="color:#ef4444;">*</span>
            </label>
            <input type="text" id="trip-title" placeholder="e.g. Elyu Weekend Getaway"
                style="width:100%; padding:12px 14px; border-radius:14px; border:1.5px solid #cbd5e1 !important; outline:none !important; background:#ffffff !important; color:#0f172a !important; -webkit-text-fill-color:#0f172a !important; margin-bottom:14px; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box; transition:border-color 0.2s;"
                onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#cbd5e1'">

            <!-- Trip Date Picker -->
            <label style="font-size:12.5px; color:#1e293b !important; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between; font-weight:700;">
                <span>Trip Date (Optional)</span>
                <span id="calendar-clear-link" onclick="window.customClearDate(event)"
                    style="display:none; font-size:11px; color:#ef4444 !important; cursor:pointer; font-weight:700;">Clear</span>
            </label>

            <div id="custom-date-trigger" onclick="window.toggleCustomCalendar(event)"
                style="position:relative; width:100%; padding:12px 14px; border-radius:14px; border:1.5px solid #cbd5e1 !important; outline:none !important; background:#ffffff; color:#0f172a; margin-bottom:14px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:all 0.2s ease; user-select:none; box-sizing:border-box;">
                <div>
                    <span id="custom-date-display" style="color:#64748b; font-weight:600;">Select trip date</span>
                </div>
                <i class="fa-solid fa-chevron-down" id="custom-date-arrow"
                    style="font-size:11px; color:#64748b; transition:transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);"></i>
            </div>
            <input type="hidden" id="trip-date" value="">

            <!-- Floating Calendar Card -->
            <div id="custom-calendar-dropdown"
                style="display:none; background:#ffffff !important; border:1.5px solid #0284c7 !important; border-radius:18px !important; padding:14px !important; margin-top:-6px !important; margin-bottom:14px !important; box-shadow:0 10px 25px rgba(2, 132, 199, 0.12) !important; box-sizing:border-box;"
                onclick="event.stopPropagation()">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding:0 4px;">
                    <button type="button" onclick="window.changeCalendarMonth(-1, event)"
                        style="background:#f1f5f9; border:1px solid #cbd5e1 !important; color:#0f172a; width:34px; height:34px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                    </button>
                    <div id="calendar-month-year" style="font-size:15px; font-weight:800; color:#0f172a !important;"></div>
                    <button type="button" onclick="window.changeCalendarMonth(1, event)"
                        style="background:#f1f5f9; border:1px solid #cbd5e1 !important; color:#0f172a; width:34px; height:34px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                    </button>
                </div>
                <div style="display:grid; grid-template-columns:repeat(7, 1fr); text-align:center; margin-bottom:8px;">
                    <span style="font-size:11.5px; font-weight:800; color:#ef4444 !important;">Su</span>
                    <span style="font-size:11.5px; font-weight:700; color:#475569 !important;">Mo</span>
                    <span style="font-size:11.5px; font-weight:700; color:#475569 !important;">Tu</span>
                    <span style="font-size:11.5px; font-weight:700; color:#475569 !important;">We</span>
                    <span style="font-size:11.5px; font-weight:700; color:#475569 !important;">Th</span>
                    <span style="font-size:11.5px; font-weight:700; color:#475569 !important;">Fr</span>
                    <span style="font-size:11.5px; font-weight:800; color:#0284c7 !important;">Sa</span>
                </div>
                <div id="calendar-days-grid" style="display:grid; grid-template-columns:repeat(7, 1fr); gap:4px; text-align:center;"></div>
                <div style="display:flex; justify-content:flex-start; align-items:center; margin-top:12px; padding-top:10px; border-top:1px solid #e2e8f0;">
                    <button type="button" onclick="window.selectTodayDate(event)"
                        style="background:#f1f5f9; border:1px solid #cbd5e1 !important; color:#0f172a !important; font-size:11.5px; font-weight:700; padding:6px 14px; border-radius:100px; cursor:pointer;">
                        <i class="fa-solid fa-calendar-day" style="margin-right:4px; color:#0284c7;"></i> Today
                    </button>
                </div>
            </div>

            <!-- Focus Base Municipality Picker -->
            <label style="font-size:12.5px; color:#1e293b !important; margin-bottom:6px; display:block; font-weight:700;">
                Focus Base Municipality / Hub
            </label>
            <div style="position:relative; margin-bottom:14px;">
                <select id="trip-municipality"
                    style="width:100%; padding:12px 14px; border-radius:14px; border:1.5px solid #cbd5e1 !important; outline:none !important; background:#ffffff; color:#0f172a; font-family:inherit; font-size:13.5px; font-weight:600; box-sizing:border-box; appearance:none; -webkit-appearance:none; cursor:pointer;">
                    <option value="All La Union">All La Union (Provincial-wide)</option>
                    <option value="San Juan" selected>San Juan (Surfing Capital & Coastal)</option>
                    <option value="San Fernando City">San Fernando City (Capital & Commercial Hub)</option>
                    <option value="Luna">Luna (Pebble Beach & Heritage)</option>
                    <option value="Bauang">Bauang (Grape Farms & Calm Beaches)</option>
                    <option value="Agoo">Agoo (Basilica & Eco-Fun)</option>
                    <option value="San Gabriel">San Gabriel (Tangadan Falls)</option>
                    <option value="Rosario">Rosario (Gateway to La Union)</option>
                </select>
                <i class="fa-solid fa-chevron-down" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:11px; color:#64748b; pointer-events:none;"></i>
            </div>

            <!-- Travel Group / Companions Section -->
            <label style="font-size:12.5px; color:#1e293b !important; margin-bottom:8px; display:block; font-weight:700;">
                Travel Group Type
            </label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                <label id="opt-trip-solo" onclick="window.setTripType('solo')"
                    style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:14px; background:#f0f9ff; border:1.5px solid #0284c7; cursor:pointer; transition:all 0.2s ease;">
                    <input type="radio" name="trip_companion_type" id="radio-trip-solo" value="solo" checked
                        style="accent-color:#0284c7; cursor:pointer; width:16px; height:16px;">
                    <div>
                        <div style="color:#0f172a; font-size:13px; font-weight:800;">Solo Trip</div>
                        <div style="color:#64748b; font-size:10px;">Just myself</div>
                    </div>
                </label>
                <label id="opt-trip-group" onclick="window.setTripType('group')"
                    style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:14px; background:#f8fafc; border:1.5px solid #e2e8f0; cursor:pointer; transition:all 0.2s ease;">
                    <input type="radio" name="trip_companion_type" id="radio-trip-group" value="group"
                        style="accent-color:#0284c7; cursor:pointer; width:16px; height:16px;">
                    <div>
                        <div style="color:#0f172a; font-size:13px; font-weight:800;">With Companions</div>
                        <div style="color:#64748b; font-size:10px;">Friends / Family</div>
                    </div>
                </label>
            </div>

            <!-- Collapsible Companion Stepper Box -->
            <div id="companion-stepper-box"
                style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:14px 16px; margin-bottom:16px;">
                <div style="font-size:12px; color:#475569; font-weight:700; margin-bottom:12px;">
                    Additional Travelers:
                </div>
                <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:18px;">
                    <!-- Male -->
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:30px; height:30px; border-radius:8px; background:#2563eb; display:flex; align-items:center; justify-content:center; color:#ffffff; box-shadow:0 2px 6px rgba(37,99,235,0.35);">
                                <i class="fa-solid fa-mars" style="font-size:14px;"></i>
                            </div>
                            <span style="font-size:13px; font-weight:700; color:#1e293b;">Male</span>
                        </div>
                        <div style="display:flex; align-items:center; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:10px; padding:3px 4px; gap:4px;">
                            <button type="button" id="btn-dec-males" onclick="window.adjustCompanionCount('males', -1)"
                                style="width:30px; height:28px; border-radius:7px; background:#2563eb; border:none; color:#ffffff; font-size:14px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(37,99,235,0.25); transition:all 0.15s ease;">−</button>
                            <span id="companion-males-count" style="font-size:14px; font-weight:800; color:#0f172a; min-width:24px; text-align:center;">0</span>
                            <button type="button" id="btn-inc-males" onclick="window.adjustCompanionCount('males', 1)"
                                style="width:30px; height:28px; border-radius:7px; background:#2563eb; border:none; color:#ffffff; font-size:14px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(37,99,235,0.25); transition:all 0.15s ease;">+</button>
                        </div>
                    </div>
                    <!-- Female -->
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:30px; height:30px; border-radius:8px; background:#db2777; display:flex; align-items:center; justify-content:center; color:#ffffff; box-shadow:0 2px 6px rgba(219,39,119,0.35);">
                                <i class="fa-solid fa-venus" style="font-size:14px;"></i>
                            </div>
                            <span style="font-size:13px; font-weight:700; color:#1e293b;">Female</span>
                        </div>
                        <div style="display:flex; align-items:center; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:10px; padding:3px 4px; gap:4px;">
                            <button type="button" id="btn-dec-females" onclick="window.adjustCompanionCount('females', -1)"
                                style="width:30px; height:28px; border-radius:7px; background:#2563eb; border:none; color:#ffffff; font-size:14px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(37,99,235,0.25); transition:all 0.15s ease;">−</button>
                            <span id="companion-females-count" style="font-size:14px; font-weight:800; color:#0f172a; min-width:24px; text-align:center;">0</span>
                            <button type="button" id="btn-inc-females" onclick="window.adjustCompanionCount('females', 1)"
                                style="width:30px; height:28px; border-radius:7px; background:#2563eb; border:none; color:#ffffff; font-size:14px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(37,99,235,0.25); transition:all 0.15s ease;">+</button>
                        </div>
                    </div>

                    <!-- Companion Ages Section -->
                    <div id="companion-ages-section" style="display:none; margin-top:8px; border-top:1px solid #e2e8f0; padding-top:10px;">
                        <div style="font-size:12px; color:#334155; font-weight:800; margin-bottom:8px; display:flex; align-items:center; justify-content:space-between;">
                            <span>Companion Ages</span>
                            <span id="companion-detected-summary" style="display:none !important; font-size:10.5px; font-weight:800; color:#0284c7; background:#e0f2fe; padding:2px 8px; border-radius:10px;">0 travelers</span>
                        </div>
                        <div id="companion-age-list" style="display:flex; flex-direction:column; gap:8px;">
                            <!-- Dynamically generated rows per companion -->
                        </div>
                    </div>

                    <!-- Hidden Concession Counters (keeps fee calculation formulas 100% synchronized) -->
                    <div style="display:none !important;" aria-hidden="true">
                        <span id="companion-adults-count">0</span>
                        <span id="companion-kids-count">0</span>
                        <span id="companion-seniors-count">0</span>
                        <span id="companion-pwds-count">0</span>
                        <span id="companion-fee-limit-info">(0 of 0)</span>
                    </div>
                </div>

                <div id="companion-summary-note"
                    style="margin-top:12px; padding-top:10px; border-top:1px solid #e2e8f0; font-size:11px; color:#64748b; display:flex; align-items:center; justify-content:space-between;">
                    <span>Total Party:</span>
                    <span id="companion-summary-text" style="color:#0284c7; font-weight:800;">1 Traveler (You)</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- STEP 2: ACTIVITY CURATION & ROUTE ALLOCATION                 -->
    <!-- ============================================================ -->
    <div id="itin-step-2" class="itinerary-step-panel">

        <!-- Suggested Spots Quick-Add Slider Section -->
        <div id="itin-suggested-slider-section" class="stagger-1" style="background:#ffffff; border-radius:18px; padding:14px; border:1px solid #e2e8f0; box-shadow:0 4px 16px rgba(15,23,42,0.04); margin-bottom:14px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div>
                    <h4 style="margin:0; font-size:13.5px; font-weight:800; color:#0f172a;">Suggested Stops to Add</h4>
                    <p style="margin:1px 0 0; font-size:11px; color:#64748b;">Tap + Add to insert spots into your stops</p>
                </div>
                <button type="button" onclick="navigateTo('map')" style="background:#f1f5f9; border:none; outline:none; color:#0284c7; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:100px; cursor:pointer; display:flex; align-items:center; gap:4px; flex-shrink:0;">
                    <i class="fa-solid fa-map"></i> Map
                </button>
            </div>

            <!-- Category Filter Pills -->
            <div id="itin-slider-categories" class="hide-scrollbar" style="display:flex; gap:6px; overflow-x:auto; padding-bottom:6px; margin-bottom:8px; scrollbar-width:none; -webkit-overflow-scrolling:touch;">
                <button type="button" class="itin-cat-pill active" onclick="window.filterStep2Slider('all', this)">All</button>
                <button type="button" class="itin-cat-pill" onclick="window.filterStep2Slider('beach', this)">Beaches</button>
                <button type="button" class="itin-cat-pill" onclick="window.filterStep2Slider('nature', this)">Nature</button>
                <button type="button" class="itin-cat-pill" onclick="window.filterStep2Slider('culture', this)">Culture</button>
                <button type="button" class="itin-cat-pill" onclick="window.filterStep2Slider('food', this)">Food</button>
            </div>

            <!-- Horizontal Scroll Slider Track -->
            <div id="itin-spots-slider-track" class="hide-scrollbar" style="display:flex; gap:10px; overflow-x:auto; scroll-snap-type:x mandatory; padding:2px 2px 6px 2px; scrollbar-width:none; -webkit-overflow-scrolling:touch;">
                <!-- Populated dynamically via JS -->
            </div>
        </div>

        <div id="draft-plan-card-wrapper" class="draft-plan-card-wrapper stagger-2" style="margin-top:0;">
            <!-- Visualization Container -->
            <div id="draft-map-wrapper" style="display:none; margin-top:0; margin-bottom:14px;">
                <!-- Route Type Container with Smooth Sliding Pill -->
                <div class="route-toggle-container" id="route-toggle-container">
                    <div class="route-toggle-slider" id="route-toggle-slider"></div>
                    <button class="btn-route-type active" id="btn-route-rec"
                        onclick="setRouteType('recommended', this)">Recommended</button>
                    <button class="btn-route-type" id="btn-route-alt"
                        onclick="setRouteType('alternative', this)">Alternative</button>
                </div>

                <div id="draft-map-container"
                    style="height: 140px; width:100%; border-radius: 18px; overflow: hidden; border: none !important; outline: none !important; position:relative; background:#cadce4; box-shadow: none;">
                    <div id="itinerary-map" style="width:100%; height:100%;"></div>
                    <button type="button" id="btn-itinerary-full-preview" onclick="window.openItineraryFullMapPreview()"
                        style="position:absolute; top:8px; right:8px; z-index:1000; width:34px; height:34px; background:linear-gradient(135deg, rgba(30, 58, 138, 0.92) 0%, rgba(63, 125, 183, 0.9) 100%); backdrop-filter:blur(10px); border:none; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#ffffff; font-size:14px; box-shadow:0 4px 12px rgba(10,25,60,0.28); cursor:pointer;"
                        title="Full Preview in Map" aria-label="Full Preview in Map">
                        <i class="fa-solid fa-expand"></i>
                    </button>
                </div>

                <div
                    style="display:flex; justify-content:space-around; align-items:center; margin-top:12px; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border:none; border-radius:18px; padding:14px;">
                    <div style="color:white; font-size:14px; font-weight:700;">
                        <i class="fa-solid fa-route" style="color:#00f2fe; margin-right:6px; font-size:16px;"></i> <span
                            id="draft-map-dist">0 km</span>
                    </div>
                    <div style="width:1px; height:20px; background:rgba(255,255,255,0.2);"></div>
                    <div
                        style="color:white; font-size:14px; font-weight:700; display:flex; flex-direction:column; align-items:center;">
                        <div><i class="fa-solid fa-clock" style="color:#00f2fe; margin-right:6px; font-size:16px;"></i>
                            <span id="draft-map-time">0 min</span>
                        </div>
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
        </div>

        <!-- Empty State Card (When no spots in draft yet) -->
        <div id="itinerary-empty-state" class="empty-state-card is-hidden" style="display:none; padding:22px 18px; margin-top:4px;">
            <div class="empty-state-icon"
                style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;">
                <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
            </div>
            <h3 style="margin-top:10px; font-size:15px; font-weight:800; color:#0f172a;">Choose Your First Stop</h3>
            <p style="margin-top:4px; max-width:280px; font-size:12px; color:#64748b; line-height:1.4;">
                Tap <strong>+ Add</strong> on any suggested spot above to start your route timeline.
            </p>
            <div style="display:flex; flex-direction:column; gap:10px; width:100%; max-width:280px; margin:14px auto 0;">
                <button class="btn-open-map" onclick="navigateTo('map')"
                    style="width:100%; margin:0; justify-content:center; background:#f0f9ff; border:1px solid #bae6fd; color:#0284c7; box-shadow:none;">
                    <i class="fa-solid fa-location-dot"></i> Or Browse Map
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- STEP 3: TIMETABLE REVIEW & COST FORECAST                     -->
    <!-- ============================================================ -->
    <div id="itin-step-3" class="itinerary-step-panel">
        <div class="transpo-card" style="background:#ffffff; border-radius:20px; padding:18px; border:1px solid #e2e8f0; box-shadow:0 4px 16px rgba(15,23,42,0.04); display:flex; flex-direction:column;">
            
            <div style="margin-bottom:12px;">
                <h3 style="margin:0; font-size:15.5px; font-weight:800; color:#0f172a;">Transportation</h3>
            </div>

            <!-- Trip Overview Pill (Hidden) -->
            <div style="display:none !important;" aria-hidden="true">
                <span id="page-review-name">My La Union Trip</span>
                <span id="page-review-date">Flexible / Anytime</span>
                <span id="page-review-party">1 Traveler (Solo)</span>
                <span id="page-review-muni">San Juan</span>
            </div>

            <!-- Vehicle Selection Cards (Multi-Select) -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                <label style="font-size:12.5px; color:#1e293b !important; margin:0; display:block; font-weight:700;">
                    Select Vehicles
                </label>
                <div style="display:flex; align-items:center; gap:6px;">
                    <button type="button" id="wizard-unselect-all-btn" onclick="window.unselectAllVehicles()" 
                        style="background:#f1f5f9; border:none; outline:none; color:#ef4444; font-size:11px; font-weight:700; padding:3px 9px; border-radius:100px; cursor:pointer; display:none; align-items:center; gap:4px; transition:background 0.2s;"
                        onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#f1f5f9'"
                        title="Unselect all vehicles">
                        <i class="fa-solid fa-xmark" style="font-size:10px;"></i> Unselect All
                    </button>
                    <span style="font-size:11px; font-weight:700; color:#0284c7; background:#e0f2fe; padding:3px 9px; border-radius:100px;">
                        Multi-Select
                    </span>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:18px;">
                <!-- Option 1: Tricycle -->
                <div class="wizard-trans-card active" data-mode="tricycle" onclick="window.selectWizardVehicle('tricycle')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Tricycle</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>

                <!-- Option 2: Traditional Jeepney -->
                <div class="wizard-trans-card" data-mode="tpuj" onclick="window.selectWizardVehicle('tpuj')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Traditional Jeepney</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>

                <!-- Option 3: Modern Jeepney -->
                <div class="wizard-trans-card" data-mode="mpuj" onclick="window.selectWizardVehicle('mpuj')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-bus-simple"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Modern Jeepney</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>

                                <!-- Option 4: Public Bus -->
                <div class="wizard-trans-card" data-mode="public_bus" onclick="window.selectWizardVehicle('public_bus')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Public Bus</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>

                <!-- Option 5: Private Bus -->
                <div class="wizard-trans-card" data-mode="private_bus" onclick="window.selectWizardVehicle('private_bus')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-bus-simple"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Private Bus</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>
<!-- Option 6: Van -->
                <div class="wizard-trans-card" data-mode="van" onclick="window.selectWizardVehicle('van')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Van</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>

                <!-- Option 7: Own Vehicle -->
                <div class="wizard-trans-card" data-mode="own_car" onclick="window.selectWizardVehicle('own_car')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Own Vehicle</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>

                <!-- Option 8: Motorcycle -->
                <div class="wizard-trans-card" data-mode="motorcycle" onclick="window.selectWizardVehicle('motorcycle')">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:#0284c7; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">Motorcycle</div>
                    </div>
                    <div class="wizard-check-radio" style="width:20px; height:20px; border-radius:6px; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <i class="fa-solid fa-check" style="font-size:10px; color:#ffffff; display:none;"></i>
                    </div>
                </div>
            </div>



            <!-- Total Summary of the Trip (Cost Breakdown Card) -->
            <div id="save-trip-total-summary-card" style="margin-top:10px; margin-bottom:10px; display:flex; flex-direction:column; gap:8px;">
                <div style="font-size:12.5px; font-weight:700; color:#1e293b;">
                    Total Summary of the Trip
                </div>
                <!-- Dynamically populated summary items -->
                <div id="save-modal-summary-items" style="display:flex; flex-direction:column; gap:8px;"></div>
            </div>

            <!-- Hidden Inputs & Legacy Stubs -->
            <input type="hidden" id="trip-transport" value="no_vehicle">
            <input type="hidden" id="trip-budget" value="">
            <span id="save-estimated-cost" style="display:none !important;">₱0.00</span>

        </div>
    </div>

<!-- Fixed Bottom Action Bar: Intact directly above bottom navigation bar -->
<div id="itin-fixed-bottom-bar">
    <!-- Step 1 Fixed Action Bar -->
    <div id="itin-bar-step-1" style="display:block;">
        <div style="background:#ffffff; border-radius:20px; padding:12px 14px; border:1px solid #e2e8f0; box-shadow:0 6px 20px rgba(15,23,42,0.12);">
            <button type="button" class="btn-primary" onclick="window.proceedToStep(2)"
                style="width:100%; padding:13px 18px; border-radius:14px; font-weight:800; font-size:14px; margin:0; border:none; background:linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%); color:#ffffff; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 14px rgba(2,132,199,0.25);">
                Proceed to Stops <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- Step 2 Fixed Action Bar -->
    <div id="itin-bar-step-2" style="display:none;">
        <div style="background:#ffffff; border-radius:20px; padding:10px 14px 12px; border:1px solid #e2e8f0; box-shadow:0 6px 20px rgba(15,23,42,0.12); display:flex; flex-direction:column; gap:8px;">
            <button type="button" onclick="navigateTo('map')"
                style="width:100%; background:#f0f9ff; border:1.5px dashed #0284c7; color:#0284c7; padding:9px 14px; border-radius:12px; font-size:12.5px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                <i class="fa-solid fa-plus"></i> Add More Spots from Map
            </button>
            <div style="display:flex; gap:10px; align-items:center;">
                <button type="button" onclick="window.proceedToStep(1)"
                    style="flex:1; background:#f1f5f9; border:none; outline:none; color:#1e3a8a; padding:12px 14px; border-radius:12px; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; display:flex; align-items:center; justify-content:center; gap:6px;">
                    <i class="fa-solid fa-arrow-left" style="font-size:12px;"></i> Plan
                </button>
                <button type="button" class="btn-primary" onclick="window.proceedToStep(3)"
                    style="flex:1.8; background:linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%); color:#ffffff; padding:12px 16px; border-radius:12px; font-size:13.5px; font-weight:800; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 4px 14px rgba(2,132,199,0.25); white-space:nowrap;">
                    Proceed to Ride <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 3 Fixed Action Bar -->
    <div id="itin-bar-step-3" style="display:none;">
        <div style="background:#ffffff; border-radius:20px; padding:12px 14px; border:1px solid #e2e8f0; box-shadow:0 6px 20px rgba(15,23,42,0.12); display:flex; gap:10px; align-items:center;">
            <button type="button" onclick="window.proceedToStep(2)"
                style="flex:1; background:#f1f5f9; border:none; outline:none; color:#1e3a8a; padding:12px 14px; border-radius:12px; font-size:13.5px; font-weight:700; cursor:pointer; white-space:nowrap; display:flex; align-items:center; justify-content:center; gap:6px;">
                <i class="fa-solid fa-arrow-left" style="font-size:12px;"></i> Stops
            </button>
            <button type="button" class="btn-primary" id="btn-submit-trip" onclick="submitItinerary()"
                style="flex:1.8; background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:#ffffff; padding:12px 18px; border-radius:12px; font-size:14px; font-weight:800; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 4px 12px rgba(16,185,129,0.3); white-space:nowrap;">
                <i class="fa-solid fa-check"></i> Save Trip
            </button>
        </div>
    </div>
</div>
</div>

<!-- Spot Quick Details Modal -->
<div id="itin-spot-details-modal" onclick="if(event.target===this) window.closeSpotDetailsModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10, 25, 60, 0.65); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:100010 !important; justify-content:center; align-items:center; padding:16px; box-sizing:border-box;">
    <div style="background:#ffffff; border-radius:24px; width:100%; max-width:400px; max-height:88vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 60px rgba(10, 25, 60, 0.35); animation:popInTripGuide 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        
        <!-- Header Image & Overlay Badges -->
        <div style="position:relative; width:100%; height:190px; background:#0f172a; flex-shrink:0;">
            <img id="spot-detail-img" src="" alt="Attraction"
                style="width:100%; height:100%; object-fit:cover; display:block;"
                onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/spot_6a686f4d0f48b.jpg';">
            
            <!-- Close Button -->
            <button type="button" onclick="window.closeSpotDetailsModal()"
                style="position:absolute; top:12px; right:12px; width:34px; height:34px; border-radius:50%; background:rgba(15,23,42,0.65); backdrop-filter:blur(8px); border:none; outline:none; color:#ffffff; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.3); z-index:2;">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <!-- Star Rating Badge -->
            <div id="spot-detail-rating-badge" style="display:none; position:absolute; top:12px; left:12px; background:rgba(15,23,42,0.75); backdrop-filter:blur(8px); border-radius:100px; padding:4px 10px; color:#facc15; font-size:11.5px; font-weight:800; align-items:center; gap:4px;">
                <i class="fa-solid fa-star" style="font-size:10px;"></i>
                <span id="spot-detail-rating" style="color:#ffffff;"></span>
            </div>

            <!-- Category Badge -->
            <div style="position:absolute; bottom:12px; left:12px; background:#0284c7; color:#ffffff; font-size:10.5px; font-weight:800; padding:4px 10px; border-radius:100px; box-shadow:0 2px 8px rgba(0,0,0,0.25);">
                <span id="spot-detail-category">Attractions</span>
            </div>
        </div>

        <!-- Scrollable Details Content -->
        <div class="hide-scrollbar" style="padding:18px 20px 14px; overflow-y:auto; flex:1;">
            <h3 id="spot-detail-title" style="margin:0 0 4px; font-size:17.5px; font-weight:800; color:#0f172a; line-height:1.25;">
                Attraction Name
            </h3>
            
            <div style="display:flex; align-items:center; gap:5px; font-size:12px; color:#64748b; margin-bottom:14px; font-weight:600;">
                <i class="fa-solid fa-location-dot" style="color:#0284c7; font-size:12px;"></i>
                <span id="spot-detail-location">Location, La Union</span>
            </div>

            <!-- Description -->
            <div style="margin-bottom:6px;">
                <h5 style="margin:0 0 6px; font-size:12.5px; font-weight:800; color:#1e293b; text-transform:uppercase; letter-spacing:0.4px;">
                    About Attraction
                </h5>
                <p id="spot-detail-desc" style="margin:0; font-size:13px; line-height:1.55; color:#475569;">
                    Explore this premier tourist attraction in the heart of La Union.
                </p>
            </div>
        </div>

        <!-- Sticky Bottom CTA -->
        <div style="padding:12px 18px 16px; background:#ffffff; border-top:1px solid #f1f5f9; flex-shrink:0;">
            <button type="button" id="spot-detail-cta-btn" onclick="window.toggleSpotFromDetailModal()"
                style="width:100%; padding:13px 16px; border-radius:14px; font-size:13.5px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all 0.2s ease; box-shadow:0 4px 12px rgba(2,132,199,0.25);">
                <i class="fa-solid fa-plus"></i> Add to Itinerary Stops
            </button>
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
            <i id="confirm-modal-icon" class="fa-solid fa-triangle-exclamation" style="color:#1e3a8a !important; font-size:26px;"></i>
        </div>
        <h3 id="confirm-modal-title" style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800; letter-spacing:-0.3px;">Missing
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

<!-- Transit Summary Modal (Opensheet from Save Trip Modal) -->
<div id="transit-summary-opensheet" onclick="if(event.target===this) window.closeTransitSummaryOpensheet()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6, 11, 25, 0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:100005 !important; justify-content:center; align-items:flex-end; padding:0;">
    <div
        style="background:#ffffff !important; border-radius:28px 28px 0 0; width:100%; max-width:480px; box-shadow:0 -10px 40px rgba(10, 25, 60, 0.5) !important; max-height:86vh; display:flex; flex-direction:column; box-sizing:border-box; overflow:hidden; animation: slideUpSheet 0.28s cubic-bezier(0.16, 1, 0.3, 1);">

        <!-- Header (Royal Blue Banner) -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 18px; color:#ffffff; flex-shrink:0; border:none !important; outline:none !important;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <h3 style="margin:0; font-size:16.5px; font-weight:800; color:#ffffff; letter-spacing:-0.2px;">
                    Total Summary of the Trip</h3>
                <button type="button" onclick="window.closeTransitSummaryOpensheet()"
                    style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink:0; transition:transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
                </button>
            </div>
        </div>

        <!-- Body: Destinations Summary List -->
        <div
            style="background:#f8fafc; color:#1e293b; padding:16px 18px 12px 18px; flex:1; overflow-y:auto; display:flex; flex-direction:column; box-sizing:border-box; -webkit-overflow-scrolling:touch;">
            <div id="transit-summary-items-list" style="display:flex; flex-direction:column; gap:10px;">
                <!-- Populated dynamically via JS -->
            </div>
        </div>

        <!-- Footer: Total Summary Banner -->
        <div
            style="flex-shrink:0; padding:14px 18px calc(14px + env(safe-area-inset-bottom, 0px)) 18px; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); display:flex; align-items:center; justify-content:space-between; gap:12px; border:none !important; outline:none !important;">
            <div>
                <div id="transit-summary-footer-label"
                    style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.75); text-transform:uppercase; letter-spacing:0.5px;">
                    Estimated Cost</div>
                <div id="transit-summary-total-fare"
                    style="font-size:18px; font-weight:900; color:#ffffff; letter-spacing:-0.2px;">₱0.00</div>
                <div id="trip-summary-total-cost-sub"
                    style="font-size:10.5px; color:rgba(255,255,255,0.85); font-weight:600; margin-top:2px;">
                    Transit: ₱0.00
                </div>
            </div>
            <button type="button" onclick="window.closeTransitSummaryOpensheet()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; font-size:12.5px; font-weight:800; cursor:pointer; padding:9px 20px; border-radius:100px; box-shadow:0 2px 8px rgba(0, 0, 0, 0.15) !important; display:inline-flex; align-items:center; gap:7px; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.95)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-check" style="color:#1e3a8a !important; font-size:12px;"></i> Done
            </button>
        </div>

    </div>
</div>

<!-- Point-to-Point Leg Transport Selection Modal (Bottom Sheet) -->
<div id="leg-transport-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6, 11, 25, 0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:100003 !important; justify-content:center; align-items:flex-end; padding:0;">
    <div
        style="background:#ffffff !important; border-radius:28px 28px 0 0; width:100%; max-width:480px; box-shadow:0 -10px 40px rgba(10, 25, 60, 0.5) !important; max-height:88vh; display:flex; flex-direction:column; box-sizing:border-box; overflow:hidden; animation: slideUpSheet 0.28s cubic-bezier(0.16, 1, 0.3, 1);">

        <!-- Header (Royal Blue Banner) -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 18px 14px 18px; color:#ffffff; flex-shrink:0; border:none !important; outline:none !important;">
            <div
                style="width:36px; height:4px; background:rgba(255,255,255,0.35); border-radius:99px; margin:0 auto 12px auto;">
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="margin:0; font-size:16.5px; font-weight:800; color:#ffffff; letter-spacing:-0.2px;">
                        Choose Leg Transports</h3>
                    <div id="leg-modal-subtitle"
                        style="font-size:11.5px; color:rgba(255,255,255,0.85); font-weight:600; margin-top:3px;">
                        Leg Route Details
                    </div>
                </div>
                <button type="button" onclick="window.closeLegTransportModal()"
                    style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink:0; transition:transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
                </button>
            </div>
        </div>

        <!-- Body Below Header (Pure White Background) -->
        <div
            style="background:#ffffff !important; color:#1e293b; padding:16px 18px 12px 18px; flex:1; overflow-y:auto; display:flex; flex-direction:column; box-sizing:border-box;">

            <!-- Multi-Select Selection Info Bar -->
            <div id="leg-modal-selection-bar"
                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; background:#f8fafc; padding:8px 12px; border-radius:12px; border:1px solid #e2e8f0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:12px; font-weight:700; color:#1e3a8a; display:flex; align-items:center;">
                        Select vehicles:
                    </span>
                    <button type="button" id="leg-modal-unselect-btn" onclick="window.unselectAllLegCandidates()"
                        style="display:none; background:rgba(239, 68, 68, 0.1); border:none !important; outline:none !important; color:#dc2626 !important; font-size:11px; font-weight:800; cursor:pointer; padding:3px 9px; border-radius:100px; transition:all 0.15s ease;">
                        Unselect All
                    </button>
                </div>
                <span id="leg-modal-selection-count"
                    style="font-size:10.5px; font-weight:800; background:rgba(30, 58, 138, 0.08); color:#1e3a8a; padding:3px 10px; border-radius:100px;">
                    0 Selected
                </span>
            </div>

            <!-- Warning Notice if destination is inaccessible by private car -->
            <div id="leg-modal-warning"
                style="display:none; background:#fffbeb; border:1px solid #fde68a; border-radius:14px; padding:10px 12px; margin-bottom:12px; display:flex; gap:10px; align-items:flex-start;">
                <i class="fa-solid fa-triangle-exclamation"
                    style="color:#d97706; font-size:14px; margin-top:2px; flex-shrink:0;"></i>
                <div style="font-size:11.5px; color:#92400e; line-height:1.4;">
                    <strong>Restricted Access:</strong> <span id="leg-modal-warning-text">This spot is not accessible by
                        private car. Park at Trailhead and hike or take a local tricycle.</span>
                </div>
            </div>

            <!-- Transport Options List -->
            <div id="leg-modal-options-list"
                style="overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:9px; padding-right:2px; -webkit-overflow-scrolling:touch; max-height:48vh; margin-bottom:4px;">
                <!-- Rendered dynamically -->
            </div>

        </div>

        <!-- Locked Bottom Footer Banner -->
        <div
            style="flex-shrink:0; padding:12px 18px calc(14px + env(safe-area-inset-bottom, 0px)) 18px; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); display:flex; align-items:center; justify-content:space-between; gap:12px; border:none !important; outline:none !important;">
            <div id="leg-modal-fare-container">
                <div
                    style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.75); text-transform:uppercase; letter-spacing:0.5px;">
                    Estimated Leg Fare</div>
                <div id="leg-modal-total-fare"
                    style="font-size:18px; font-weight:900; color:#ffffff; letter-spacing:-0.2px;">₱0.00</div>
            </div>
            <button type="button" id="btn-apply-leg-transport" onclick="window.applyLegVehicleSelection()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; font-size:12.5px; font-weight:800; cursor:pointer; padding:9px 18px; border-radius:100px; box-shadow:0 2px 8px rgba(0, 0, 0, 0.15) !important; display:inline-flex; align-items:center; gap:7px; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.95)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-check" style="color:#1e3a8a !important; font-size:12px;"></i> Done
            </button>
        </div>

    </div>
</div>

<!-- Vehicle Selection Modal (Bottom Sheet - Matched to Leg Transport Modal) -->
<div id="travel-mode-starter-modal" onclick="if(event.target===this) window.closeTravelModeStarterModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6, 11, 25, 0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:100003 !important; justify-content:center; align-items:flex-end; padding:0;">
    <div
        style="background:#ffffff !important; border-radius:28px 28px 0 0; width:100%; max-width:480px; box-shadow:0 -10px 40px rgba(10, 25, 60, 0.5) !important; max-height:88vh; display:flex; flex-direction:column; box-sizing:border-box; overflow:hidden; animation: slideUpSheet 0.28s cubic-bezier(0.16, 1, 0.3, 1);">

        <!-- Header (Royal Blue Banner) -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 18px 14px 18px; color:#ffffff; flex-shrink:0; border:none !important; outline:none !important;">
            <div
                style="width:36px; height:4px; background:rgba(255,255,255,0.35); border-radius:99px; margin:0 auto 12px auto;">
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="margin:0; font-size:16.5px; font-weight:800; color:#ffffff; letter-spacing:-0.2px;">
                        Choose Your Vehicle</h3>
                    <div style="font-size:11.5px; color:rgba(255,255,255,0.85); font-weight:600; margin-top:3px;">
                        Select one or more vehicles for your trip
                    </div>
                </div>
                <button type="button" onclick="window.closeTravelModeStarterModal()"
                    style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink:0; transition:transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
                </button>
            </div>
        </div>

        <!-- Body Below Header (Pure White Background) -->
        <div
            style="background:#ffffff !important; color:#1e293b; padding:16px 18px 12px 18px; flex:1; overflow-y:auto; display:flex; flex-direction:column; box-sizing:border-box;">

            <!-- Multi-Select Selection Info Bar -->
            <div id="starter-modal-selection-bar"
                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; background:#f8fafc; padding:8px 12px; border-radius:12px; border:1px solid #e2e8f0;">
                <span style="font-size:12px; font-weight:700; color:#1e3a8a; display:flex; align-items:center;">
                    Select multiple vehicles if needed:
                </span>
                <span id="starter-modal-selection-count"
                    style="font-size:10.5px; font-weight:800; background:rgba(30, 58, 138, 0.08); color:#1e3a8a; padding:3px 10px; border-radius:100px;">
                    1 Selected
                </span>
            </div>

            <!-- Information Bar -->
            <div
                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; background:#f8fafc; padding:10px 12px; border-radius:12px; border:1px solid #e2e8f0; font-size:11.5px; color:#475569; line-height:1.4;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-info" style="color:#0284c7; font-size:13px; flex-shrink:0;"></i>
                    <span>Select multiple vehicles for connecting routes (e.g. Car + Tricycle for trailhead access or
                        Bus + Jeepney).</span>
                </span>
            </div>

            <!-- Transport Options List -->
            <div
                style="overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:9px; padding-right:2px; -webkit-overflow-scrolling:touch; max-height:48vh; margin-bottom:4px;">

                <!-- Category 1: Private Vehicles -->
                <div
                    style="font-size:11px; font-weight:800; text-transform:uppercase; color:#1e3a8a; letter-spacing:0.6px; margin:4px 0 2px 2px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-car" style="font-size:11px; color:#1e3a8a;"></i> Private Vehicles
                </div>

                <!-- Option 1: Own Car -->
                <div class="travel-starter-card" data-mode="own_car"
                    onclick="window.toggleStarterVehicleMode('own_car')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Own Car</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 2: Motorcycle -->
                <div class="travel-starter-card" data-mode="motorcycle"
                    onclick="window.toggleStarterVehicleMode('motorcycle')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Motorcycle</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 3: Van -->
                <div class="travel-starter-card" data-mode="van" onclick="window.toggleStarterVehicleMode('van')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Van</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Category 2: Public Vehicles -->
                <div
                    style="font-size:11px; font-weight:800; text-transform:uppercase; color:#1e3a8a; letter-spacing:0.6px; margin:12px 0 2px 2px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-bus" style="font-size:11px; color:#1e3a8a;"></i> Public Vehicles
                </div>

                <!-- Option 4: Modern Jeepney (MPUJ) -->
                <div class="travel-starter-card" data-mode="mpuj" onclick="window.toggleStarterVehicleMode('mpuj')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-bus-simple"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Modern Jeepney</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 5: Traditional Jeepney -->
                <div class="travel-starter-card" data-mode="tpuj" onclick="window.toggleStarterVehicleMode('tpuj')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Traditional Jeepney
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 6: Tricycle -->
                <div class="travel-starter-card" data-mode="tricycle"
                    onclick="window.toggleStarterVehicleMode('tricycle')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Tricycle</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 7: Taxi -->
                <div class="travel-starter-card" data-mode="taxi" onclick="window.toggleStarterVehicleMode('taxi')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-taxi"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Taxi</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 8: Regular Bus -->
                <div class="travel-starter-card" data-mode="pub_regular"
                    onclick="window.toggleStarterVehicleMode('pub_regular')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Regular Bus
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

                <!-- Option 10: Aircon Bus -->
                <div class="travel-starter-card" data-mode="pub_aircon"
                    onclick="window.toggleStarterVehicleMode('pub_aircon')">
                    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                        <div class="starter-check-box"
                            style="width:22px; height:22px; border-radius:6px; border:2px solid rgba(255,255,255,0.65); background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#1e3a8a; font-size:12px; font-weight:900; flex-shrink:0; transition:all 0.18s ease;">
                            <i class="fa-solid fa-check starter-check-icon"
                                style="color:#1e3a8a !important; display:none;"></i>
                        </div>
                        <div class="travel-starter-icon">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:13.5px; color:#ffffff;">Aircon Bus</div>
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <span class="starter-active-badge"
                            style="display:none; font-size:10px; font-weight:800; background:rgba(255, 255, 255, 0.22); color:#ffffff; padding:3px 10px; border-radius:100px; text-transform:uppercase; letter-spacing:0.3px;">
                            <i class="fa-solid fa-check" style="margin-right:3px;"></i> Selected
                        </span>
                    </div>
                </div>

            </div>

        </div>

        <!-- Locked Bottom Footer Banner -->
        <div
            style="flex-shrink:0; padding:12px 18px calc(14px + env(safe-area-inset-bottom, 0px)) 18px; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); display:flex; align-items:center; justify-content:space-between; gap:12px; border:none !important; outline:none !important;">
            <div style="min-width:0; flex:1;">
                <div
                    style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.75); text-transform:uppercase; letter-spacing:0.5px;">
                    Trip Travel Mode</div>
                <div id="starter-modal-current-mode-label"
                    style="font-size:15px; font-weight:900; color:#ffffff; letter-spacing:-0.2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    Own Car</div>
            </div>
            <button type="button" id="btn-apply-starter-transport" onclick="window.applyStarterVehicleSelection()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; font-size:12.5px; font-weight:800; cursor:pointer; padding:9px 18px; border-radius:100px; box-shadow:0 2px 8px rgba(0, 0, 0, 0.15) !important; display:inline-flex; align-items:center; gap:7px; transition:transform 0.15s ease; flex-shrink:0;"
                onpointerdown="this.style.transform='scale(0.95)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-check" style="color:#1e3a8a !important; font-size:12px;"></i> Done
            </button>
        </div>

    </div>
</div>

<!-- Fullscreen Itinerary Route Preview Modal -->
<div id="itinerary-fullscreen-map-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; z-index:999980; background:#0a0f1c; flex-direction:column;">
    
    <!-- Header Bar matching the App Mobile Header Style -->
    <div class="mobile-header"
        style="position:absolute !important; top:0 !important; left:0 !important; transform:none !important; width:100% !important; max-width:100% !important; background:#1e3a8a !important; padding:10px clamp(16px, 4.5vw, 22px) !important; padding-top:max(env(safe-area-inset-top, 0px), 36px) !important; display:flex !important; justify-content:space-between !important; align-items:center !important; z-index:10000 !important; box-sizing:border-box !important; border:none !important; outline:none !important; box-shadow:none !important;">
        <button type="button" class="header-icon" onclick="window.closeItineraryFullscreenMap()" title="Back" aria-label="Back"
            style="width:38px; height:38px; border-radius:12px; background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.15) !important;"
            onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-arrow-left" style="color:#1e3a8a !important;"></i>
        </button>
        <h1 class="header-title"
            style="font-size:17px; font-weight:800; color:#ffffff !important; margin:0; letter-spacing:-0.3px; text-align:center; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding:0 8px;">
            Route Preview
        </h1>
        <button type="button" class="header-icon" onclick="typeof toggleNotifications === 'function' ? toggleNotifications() : null" title="Notifications" aria-label="Notifications"
            style="width:38px; height:38px; border-radius:12px; background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.15) !important; position:relative;"
            onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-bell" style="color:#1e3a8a !important;"></i>
            <div class="bell-dot" id="fs-bell-dot" style="position:absolute; top:8px; right:8px; width:7px; height:7px; background:#ef4444; border-radius:50%; display:none;"></div>
        </button>
    </div>

    <!-- Map Legend on Bottom Left (Above Cards Dock) - Minimizable -->
    <div id="itinerary-fs-legend"
        style="position:absolute; bottom:calc(max(env(safe-area-inset-bottom, 0px), 12px) + 94px); left:12px; z-index:9999; background:rgba(255, 255, 255, 0.94); backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); border-radius:14px; box-shadow:0 4px 14px rgba(10, 25, 60, 0.12); border:1px solid rgba(226, 232, 240, 0.85); display:flex; flex-direction:column; pointer-events:auto; min-width:115px; overflow:hidden; transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
        <div onclick="window.toggleItineraryFsLegend()"
            style="display:flex; align-items:center; justify-content:space-between; padding:7px 10px; cursor:pointer; user-select:none; gap:8px;">
            <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Legend</span>
            <i id="itinerary-fs-legend-chevron" class="fa-solid fa-chevron-down" style="font-size:9px; color:#64748b; transition:transform 0.25s ease;"></i>
        </div>
        <div id="itinerary-fs-legend-body"
            style="display:flex; flex-direction:column; gap:6px; padding:0 10px 8px 10px; max-height:120px; opacity:1; overflow:hidden; transition:max-height 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease, padding 0.25s ease;">
            <div style="display:flex; align-items:center; gap:8px; font-size:10.5px; font-weight:700; color:#1e3a8a; min-height:18px;">
                <span style="width:18px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#0284c7; border:2px solid #ffffff; box-shadow:0 0 0 1.5px #0284c7; flex-shrink:0;"></span>
                </span>
                <span>You</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; font-size:10.5px; font-weight:700; color:#1e3a8a; min-height:18px;">
                <span style="width:18px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span style="width:14px; height:14px; border-radius:50%; background:linear-gradient(135deg, #00f2fe, #0284c7); color:#fff; font-size:8px; font-weight:900; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">#</span>
                </span>
                <span>Trip Stops</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; font-size:10.5px; font-weight:700; color:#1e3a8a; min-height:18px;">
                <span style="width:18px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span style="width:16px; height:3px; border-radius:2px; background:#00f2fe; flex-shrink:0;"></span>
                </span>
                <span>Route</span>
            </div>
        </div>
    </div>

    <!-- Floating Action Controls on Bottom Right -->
    <div style="position:absolute; right:12px; bottom:calc(max(env(safe-area-inset-bottom, 0px), 12px) + 94px); z-index:10000; display:flex; flex-direction:column; gap:6px; align-items:center;">
        <button type="button" id="btn-fs-recenter" onclick="window.fitItineraryFullscreenMapBounds()" title="Recenter Route" aria-label="Recenter Route"
            style="width:42px; height:42px; border-radius:12px; background:#0284c7 !important; border:none !important; outline:none !important; color:#ffffff !important; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:none !important; transition:transform 0.15s ease;"
            onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-route" style="color:#ffffff !important;"></i>
        </button>
        <button type="button" id="btn-fs-locate-me" onclick="window.locateItineraryFullscreenMap()" title="My Location" aria-label="Locate Me"
            style="width:42px; height:42px; border-radius:12px; background:#0284c7 !important; border:none !important; outline:none !important; color:#ffffff !important; font-size:17px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:none !important; transition:transform 0.15s ease;"
            onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-crosshairs" style="color:#ffffff !important;"></i>
        </button>
    </div>

    <!-- Bottom Container: Trip Stop Cards Background Dock -->
    <div id="itinerary-fs-bottom-panel"
        style="position:absolute; bottom:calc(max(env(safe-area-inset-bottom, 0px), 12px) + 10px); left:12px; right:12px; z-index:10000; pointer-events:none; box-sizing:border-box;">
        
        <!-- Frosted Background Container Dock for Cards -->
        <div id="itinerary-fs-cards-container"
            style="background:rgba(255, 255, 255, 0.82) !important; backdrop-filter:blur(20px) !important; -webkit-backdrop-filter:blur(20px) !important; border-radius:18px !important; padding:7px 8px !important; border:1px solid rgba(255, 255, 255, 0.95) !important; box-shadow:0 10px 30px rgba(10, 25, 60, 0.16) !important; pointer-events:auto !important; width:100% !important; box-sizing:border-box !important;">
            
            <!-- Trip Stop Cards Carousel -->
            <div id="itinerary-fs-cards-carousel" class="hide-scrollbar"
                style="display:flex; gap:9px; overflow-x:auto; padding:2px; scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch; scrollbar-width:none;">
                <!-- Populated via renderItineraryFsCards() -->
            </div>
        </div>
    </div>

    <!-- The Fullscreen Leaflet Map Canvas -->
    <div id="itinerary-fullscreen-map" style="width:100%; height:100%; background:#cadce4;"></div>
</div>

<script>
    (function () {
        if (document.body) document.body.setAttribute('data-view', 'itinerary');
        if (typeof window.setTxt !== 'function') {
            window.setTxt = function (id, val) {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };
        }
        var backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');

        // Synchronize GPS state immediately or resolve via high-accuracy hardware GPS
        const rememberedGps = typeof window.getRememberedGpsLocation === 'function' ? window.getRememberedGpsLocation() : null;
        window.myLat = window.myLat || window.currentGPSLat || (rememberedGps && rememberedGps.lat) || null;
        window.myLng = window.myLng || window.currentGPSLng || (rememberedGps && rememberedGps.lng) || null;

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
            const straightKm = 12742 * Math.asin(Math.sqrt(a));
            const distKm = straightKm * 1.215; // Realistic La Union road winding factor (~1.215x straight line)

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

        // Clean up any lingering modals previously ported to document.body from past view loads
        ['save-trip-modal', 'confirm-modal', 'leg-transport-modal', 'transit-summary-opensheet', 'travel-mode-starter-modal'].forEach(id => {
            const bodyModals = document.body.querySelectorAll(':scope > #' + id);
            bodyModals.forEach(m => {
                try { m.remove(); } catch (e) { }
            });
        });

        function portModalToBody(modalId) {
            const inAppContent = document.querySelector('#app-content #' + modalId);
            const existingInBody = Array.from(document.body.children).filter(el => el.id === modalId);
            if (inAppContent) {
                existingInBody.forEach(el => {
                    if (el !== inAppContent) {
                        try { el.remove(); } catch (e) { }
                    }
                });
                document.body.appendChild(inAppContent);
                return inAppContent;
            }
            const target = document.getElementById(modalId);
            if (!target) return null;
            existingInBody.forEach(el => {
                if (el !== target) {
                    try { el.remove(); } catch (e) { }
                }
            });
            if (target.parentElement !== document.body) {
                document.body.appendChild(target);
            }
            return target;
        }
        window.portModalToBody = portModalToBody;



        // ---- Custom confirm modal (replaces native confirm) ----
        window.showConfirmModal = function (msg, options) {
            options = options || {};
            var modal = portModalToBody('confirm-modal');
            var msgEl = document.getElementById('confirm-modal-msg');
            var titleEl = document.getElementById('confirm-modal-title');
            var btnOk = document.getElementById('btn-confirm-ok');
            var btnCancel = document.getElementById('btn-confirm-cancel');
            if (!modal || !msgEl || !btnOk || !btnCancel) { return Promise.resolve(true); }

            return new Promise(function (resolve) {
                // Hide / put bottom navigation bar behind modal
                var bottomNav = document.getElementById('bottom-navigation');
                if (bottomNav) {
                    bottomNav.classList.add('nav-hidden');
                }
                document.body.classList.add('itin-confirm-open');

                msgEl.textContent = msg;
                if (titleEl) titleEl.textContent = options.title || 'Missing Details';
                var iconEl = document.getElementById('confirm-modal-icon') || (modal ? modal.querySelector('i') : null);
                if (iconEl) iconEl.className = 'fa-solid ' + (options.icon || 'fa-triangle-exclamation');
                btnOk.textContent = options.confirmText || 'Save Anyway';
                btnCancel.textContent = options.cancelText || 'Cancel';
                btnCancel.style.display = options.hideCancel ? 'none' : '';
                modal.style.display = 'flex';

                function cleanup() {
                    document.querySelectorAll('#confirm-modal').forEach(m => {
                        m.style.display = 'none';
                    });
                    document.body.classList.remove('itin-confirm-open');
                    if (bottomNav) {
                        var saveModal = document.getElementById('save-trip-modal');
                        var legModal = document.getElementById('leg-transport-modal');
                        var isOtherModalOpen = (saveModal && saveModal.style.display === 'flex') || (legModal && legModal.style.display === 'flex');
                        if (!isOtherModalOpen) {
                            bottomNav.classList.remove('nav-hidden');
                        }
                    }
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
        const FARES_CACHE_KEY = 'public_fare_data_v2';
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



        window.recalculateCosts = function () {
            if (typeof window.renderItinerary === 'function') {
                window.renderItinerary(true);
            }
            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.syncStep2SliderButtons === 'function') {
                window.syncStep2SliderButtons();
            }
        };

        window.getFareFromMatrix = function (vehicleType, distanceKm, municipality = null) {
            if (!window.fareData) return null;

            const dKm = parseFloat(distanceKm) || 0;
            const rawType = (vehicleType || '').toString().toLowerCase().trim();
            const normType = rawType.replace(/[- ]/g, '_');

            if (['own_car', 'taxi', 'motorcycle', 'car'].includes(normType)) return null;
            if (['van', 'uve', 'mini_bus', 'private_bus'].includes(normType)) return 0;

            let fareEntry = null;

            // 1. Direct match in top-level window.fareData
            if (window.fareData[normType]) {
                fareEntry = window.fareData[normType];
            }

            // 2. Municipality lookup for local / inter-municipal guides
            const cleanMuni = municipality ? municipality.toString().trim().replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').replace(/\s+city$/i, '').trim() : '';
            const muniKey = cleanMuni.toLowerCase().replace(/[^a-z0-9]/g, '_');
            const muniRaw = cleanMuni.toLowerCase();

            if (window.fareData.by_municipality) {
                const byMuni = window.fareData.by_municipality;
                let muniObj = (cleanMuni && (byMuni[muniKey] || byMuni[muniRaw] || byMuni[muniKey + '_city'] || byMuni[muniRaw + ' city'])) || null;
                if (!muniObj && cleanMuni) {
                    const matchedKey = Object.keys(byMuni).find(k => k.includes(muniKey) || muniKey.includes(k));
                    if (matchedKey) muniObj = byMuni[matchedKey];
                }
                if (muniObj) {
                    if (muniObj[normType]) {
                        fareEntry = muniObj[normType];
                    } else if ((normType === 'tricycle' || normType === 'trike') && muniObj.tricycle) {
                        fareEntry = muniObj.tricycle;
                    } else if ((normType === 'tricycle' || normType === 'trike') && muniObj.default && String(muniObj.default.vehicle_type || '').toLowerCase().includes('tri')) {
                        fareEntry = muniObj.default;
                    }
                }
                if ((normType === 'tricycle' || normType === 'trike') && (!fareEntry || !fareEntry.rates || fareEntry.rates.length === 0)) {
                    // Fallback to top-level window.fareData['tricycle'] or any active tricycle tariff guide in by_municipality (e.g. LUPTO North/South/East Routes)
                    if (window.fareData['tricycle'] && Array.isArray(window.fareData['tricycle'].rates) && window.fareData['tricycle'].rates.length > 0) {
                        fareEntry = window.fareData['tricycle'];
                    } else {
                        for (const k of Object.keys(byMuni)) {
                            const candidate = byMuni[k]?.tricycle || (String(byMuni[k]?.default?.vehicle_type || '').toLowerCase().includes('tri') ? byMuni[k].default : null);
                            if (candidate && Array.isArray(candidate.rates) && candidate.rates.length > (!fareEntry?.rates ? 0 : fareEntry.rates.length)) {
                                fareEntry = candidate;
                            }
                        }
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
                        || window.fareData['tricycle']
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
            const rawRates = Array.isArray(fareEntry.rates) ? fareEntry.rates : Object.values(fareEntry.rates);
            if (!rawRates || rawRates.length === 0) return null;
            const rates = [...rawRates].sort((a, b) => (parseFloat(a?.distance_km) || 0) - (parseFloat(b?.distance_km) || 0));

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
                    const isTrike = (normType === 'tricycle' || normType === 'trike');
                    const perKm = isTrike ? 3.0 : 1.8;
                    const extraUnits = isTrike ? Math.ceil(extra) : extra;
                    return Math.round(parseFloat(maxRate.regular_fare) + (extraUnits * perKm));
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
            if (s === 'public_bus') return 'public_bus';
            if (s === 'private_bus') return 'private_bus';
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
            const validModes = ['own_car', 'motorcycle', 'van', 'mpuj', 'tpuj', 'tricycle', 'public_bus', 'private_bus', 'pub_regular', 'pub_aircon', 'uve', 'taxi'];
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
            if (subModes.length > 1) {
                return `${subModes.length} Vehicles`;
            }

            const norm = (subModes.length === 1) ? subModes[0] : window.normalizeVehicleKey(modeKey);
            const map = {
                'own_car': 'Own Car',
                'motorcycle': 'Motorcycle',
                'van': 'Van',
                'mpuj': 'Modern Jeepney',
                'tpuj': 'Traditional Jeepney',
                'jeepney': 'Traditional Jeepney',
                'tricycle': 'Tricycle',
                'public_bus': 'Public Bus',
                'private_bus': 'Private Bus',
                'public_bus': 'Public Bus',
                'private_bus': 'Private Bus',
                'pub_regular': 'Regular Bus',
                'pub_aircon': 'Aircon Bus',
                'uve': 'UV Express',
                'taxi': 'Taxi'
            };
            return map[norm] || norm.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        };

        window.getVehicleShortName = function (modeKey) {
            if (!modeKey) return 'Own Car';
            const subModes = window.parseCompositeTransportModes(modeKey);
            if (subModes.length > 1) {
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
            if (norm === 'public_bus') return '<i class="fa-solid fa-bus" style="color:#0284c7;"></i>';
            if (norm === 'private_bus') return '<i class="fa-solid fa-bus-simple" style="color:#0284c7;"></i>';
            if (norm === 'pub_regular') return '<i class="fa-solid fa-bus" style="color:#fb923c;"></i>';
            if (norm === 'pub_aircon') return '<i class="fa-solid fa-bus" style="color:#f87171;"></i>';
            if (norm === 'uve') return '<i class="fa-solid fa-van-shuttle" style="color:#a855f7;"></i>';
            if (norm === 'taxi') return '<i class="fa-solid fa-taxi" style="color:#facc15;"></i>';
            return '<i class="fa-solid fa-car" style="color:#00f2fe;"></i>';
        };

        // ---- Point-to-Point (P2P) Fare & Party Headcount Engine ----
        window.getPartyHeadcount = function () {
            const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            if (!isGroup) {
                const savedType = sessionStorage.getItem('editing_trip_type');
                if (savedType === 'group') {
                    const numA = 1 + Math.max(0, parseInt(sessionStorage.getItem('editing_companion_adults') || '0', 10));
                    const numK = Math.max(0, parseInt(sessionStorage.getItem('editing_companion_kids') || '0', 10));
                    const numS = Math.max(0, parseInt(sessionStorage.getItem('editing_companion_seniors') || '0', 10));
                    const numP = Math.max(0, parseInt(sessionStorage.getItem('editing_companion_pwds') || '0', 10));
                    return { isGroup: true, numAdults: numA, numKids: numK, numSeniors: numS, numPwds: numP, totalPax: (numA + numK + numS + numP) };
                }
                return { isGroup: false, numAdults: 1, numKids: 0, numSeniors: 0, numPwds: 0, totalPax: 1 };
            }
            const compMales = Math.max(0, parseInt(document.getElementById('companion-males-count')?.textContent || '0', 10));
            const compFemales = Math.max(0, parseInt(document.getElementById('companion-females-count')?.textContent || '0', 10));
            const compAdults = Math.max(0, parseInt(document.getElementById('companion-adults-count')?.textContent || '0', 10));
            const compKids = Math.max(0, parseInt(document.getElementById('companion-kids-count')?.textContent || '0', 10));
            const compSeniors = Math.max(0, parseInt(document.getElementById('companion-seniors-count')?.textContent || '0', 10));
            const compPwds = Math.max(0, parseInt(document.getElementById('companion-pwds-count')?.textContent || '0', 10));

            const totalGenderCompanions = compMales + compFemales;
            let numAdults = 1 + compAdults;
            let numKids = compKids;
            let numSeniors = compSeniors;
            let numPwds = compPwds;
            const categorized = compAdults + compKids + compSeniors + compPwds;
            const totalPax = 1 + categorized;
            return { isGroup: true, numAdults, numKids, numSeniors, numPwds, totalPax };
        };

        window.calculatePartyTransitFare = function (unitCost, mode) {
            const cost = parseFloat(unitCost) || 0;
            if (cost <= 0) return 0;
            if (!mode) return cost;

            const norm = (typeof window.normalizeVehicleKey === 'function')
                ? window.normalizeVehicleKey(mode)
                : String(mode).toLowerCase().trim().replace(/[- ]+/g, '_');

            if (['own_car', 'car', 'motorcycle', 'taxi', 'walking', 'walk', 'no_vehicle'].includes(norm)) {
                return cost;
            }

            const party = (typeof window.getPartyHeadcount === 'function')
                ? window.getPartyHeadcount()
                : { isGroup: false, numAdults: 1, numKids: 0, numSeniors: 0, numPwds: 0 };

            if (!party.isGroup) {
                return cost;
            }

            const discountedUnit = Math.round(cost * 0.8 * 100) / 100;
            const totalPartyCost = (party.numAdults * cost) + ((party.numKids + party.numSeniors + party.numPwds) * discountedUnit);
            return Math.round(totalPartyCost * 100) / 100;
        };

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
            if (['own_car', 'motorcycle', 'walking', 'walk', 'no_vehicle', 'van', 'uve', 'mini_bus'].includes(norm)) {
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
                    if (pA === null || pA === undefined || pA === 0) pA = Math.max(15, 15 + Math.max(0, Math.ceil(dA) - 1) * 3);
                    let pB = window.getFareFromMatrix('tricycle', dB, cleanB);
                    if (pB === null || pB === undefined || pB === 0) pB = Math.max(15, 15 + Math.max(0, Math.ceil(dB) - 1) * 3);
                    const trikeFare = Math.max(25, (pA + pB) * 2); // 2x multiplier for direct tricycle drop-off, minimum ₱25
                    return Math.round(trikeFare * 100) / 100;
                } else {
                    let p = window.getFareFromMatrix('tricycle', d, cleanA || cleanB);
                    if (p === null || p === undefined || p === 0) p = Math.max(15, 15 + Math.max(0, Math.ceil(d) - 1) * 3);
                    const trikeFare = Math.max(25, p * 2); // 2x multiplier for direct tricycle drop-off, minimum ₱25
                    return Math.round(trikeFare * 100) / 100;
                }
            }

            // Public transit: Jeepney (MPUJ/TPUJ), PUB Aircon, PUB Ordinary, Van
            const targetVeh = (norm === 'private_bus') ? 'pub_aircon' : ((norm === 'public_bus') ? 'pub_regular' : ((norm === 'jeepney') ? 'mpuj' : norm));
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
                let p = window.getFareFromMatrix(targetVeh, d, cleanA || cleanB);
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
            const muniB = toSpot ? window.getSpotMuniName(toSpot) : '';
            const muniA = fromSpot ? window.getSpotMuniName(fromSpot) : muniB;
            const cleanA = muniA.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim().toLowerCase();
            const cleanB = muniB.replace(/^(municipality of|city of)\s+/i, '').replace(/,\s*la\s*union$/i, '').trim().toLowerCase();
            const isCross = Boolean(cleanA && cleanB && cleanA !== cleanB);

            const isNonDrivable = Boolean(toSpot && (toSpot.accessible_by_private_vehicle === 0 || toSpot.accessible_by_private_vehicle === false || toSpot.accessible_by_private_vehicle === '0'));

            // Multi-modal global transport support (e.g. "own_car + tricycle", "own_car + motorcycle")
            const allSubModes = window.parseCompositeTransportModes(globalMode);
            // Filter global vehicles to only those accessible for this specific leg destination
            const availSubModes = allSubModes.filter(m => {
                const chk = window.isVehicleAllowedForSpot(m, toSpot);
                return chk && chk.allowed;
            });
            const subModes = (availSubModes.length > 0) ? availSubModes : allSubModes;

            if (subModes.length > 1) {
                const cost = window.calculateSingleLegCost(subModes.join(' + '), d, muniA, muniB);
                const dispName = `${subModes.length} Vehicles`;

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

            const normGlobal = (subModes.length === 1) ? subModes[0] : (globalMode ? window.normalizeVehicleKey(globalMode) : 'no_vehicle');

            if (!globalMode || normGlobal === 'no_vehicle' || String(globalMode).toLowerCase().includes('no vehicle')) {
                return {
                    mode: 'no_vehicle',
                    transport_mode: 'no_vehicle',
                    transport_modes: [],
                    name: 'No Vehicle Selected',
                    full_names: 'No Vehicle Selected',
                    icon: '',
                    cost: 0,
                    leg_cost: 0,
                    warning: null,
                    is_non_drivable: isNonDrivable
                };
            }

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

                        // 3.5. If global is Public Bus
            if (normGlobal === 'public_bus') {
                const cost = window.calculateSingleLegCost('public_bus', d, muniA, muniB);
                return {
                    mode: 'public_bus',
                    transport_mode: 'public_bus',
                    transport_modes: ['public_bus'],
                    name: 'Public Bus',
                    icon: 'fa-bus',
                    cost: cost,
                    warning: isNonDrivable ? 'Drop off along highway; transfer or hike to site.' : null,
                    is_non_drivable: isNonDrivable
                };
            }

            // 3.6. If global is Private Bus
            if (normGlobal === 'private_bus') {
                return {
                    mode: 'private_bus',
                    transport_mode: 'private_bus',
                    transport_modes: ['private_bus'],
                    name: 'Private Bus',
                    icon: 'fa-bus-simple',
                    cost: 0,
                    warning: isNonDrivable ? 'Large vehicle; park at designated bus terminal or highway drop-off.' : null,
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

            // Default fallback: No vehicle selected
            return {
                mode: 'no_vehicle',
                transport_mode: 'no_vehicle',
                transport_modes: [],
                name: 'No Vehicle Selected',
                icon: '',
                cost: 0,
                leg_cost: 0,
                warning: null,
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
            } catch (e) { }

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
                return { allowed: false, reason: 'Not accessible for this destination' };
            }
            return { allowed: true, reason: '' };
        };

        window.isSpotUnderMaintenance = function (spot) {
            if (!spot) return false;
            const target = spot.destination || spot;
            try {
                const spotId = String(target.id || target.tourist_spot_id || spot.id || spot.tourist_spot_id || '');
                if (spotId && target.is_maintenance === undefined && spot.is_maintenance === undefined) {
                    if (window._cachedMapSpots && window._cachedMapSpots[spotId]) {
                        target.is_maintenance = window._cachedMapSpots[spotId].is_maintenance;
                    } else {
                        const rawMap = localStorage.getItem('public_map_data');
                        if (rawMap) {
                            const parsed = JSON.parse(rawMap);
                            const list = parsed?.destinations || parsed?.data?.destinations || [];
                            const mapSpot = list.find(s => String(s?.id) === spotId);
                            if (mapSpot && mapSpot.is_maintenance !== undefined) {
                                target.is_maintenance = mapSpot.is_maintenance;
                            }
                        }
                    }
                }
            } catch (e) { }
            return Boolean(
                target.is_maintenance === 1 ||
                target.is_maintenance === true ||
                target.is_maintenance === '1' ||
                spot.is_maintenance === 1 ||
                spot.is_maintenance === true ||
                spot.is_maintenance === '1' ||
                (target.status && String(target.status).toLowerCase().includes('maint')) ||
                (target.operating_status && String(target.operating_status).toLowerCase().includes('maint')) ||
                (spot.status && String(spot.status).toLowerCase().includes('maint')) ||
                (spot.operating_status && String(spot.operating_status).toLowerCase().includes('maint'))
            );
        };

        window.getLegTransportInfo = function (legIdx) {
            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

            if (!draft || draft.length === 0 || legIdx >= draft.length) {
                return { mode: 'no_vehicle', transport_mode: 'no_vehicle', name: 'No Vehicle Selected', icon: '', cost: 0, leg_cost: 0, distance_km: 0 };
            }

            const curGlobalTransport = localStorage.getItem('intan_elyu_draft_trip_transport') || document.getElementById('trip-transport')?.value || '';

            let fromSpot = null;
            let toSpot = draft[legIdx];
            let distKm = 2.0;
            let muniA = '';
            let muniB = window.getSpotMuniName(toSpot);

            if (legIdx === 0) {
                const toLat = toSpot.lat || toSpot.latitude;
                const toLng = toSpot.lng || toSpot.longitude;
                const placeKey = String(toSpot.id || toSpot.name);
                if (window._cachedNextStopOSRM && window._cachedNextStopOSRM.spotKey === placeKey && window._cachedNextStopOSRM.distanceKm) {
                    distKm = window._cachedNextStopOSRM.distanceKm;
                } else if (typeof window.getDistanceAndETA === 'function' && toLat && toLng) {
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
                const legKey = `${fromSpot.id || fromSpot.name}_${toSpot.id || toSpot.name}`;
                if (window._cachedLegOSRM && window._cachedLegOSRM[legKey] && window._cachedLegOSRM[legKey].distanceKm) {
                    distKm = window._cachedLegOSRM[legKey].distanceKm;
                } else if (typeof window.calculateLegETA === 'function' && fromLat && fromLng && toLat && toLng) {
                    const eta = window.calculateLegETA(fromLat, fromLng, toLat, toLng);
                    if (eta && eta.distanceKm) distKm = eta.distanceKm;
                }
            }

            const isSiteUnderMaintenance = Boolean(window.isSpotUnderMaintenance(toSpot) || (fromSpot && window.isSpotUnderMaintenance(fromSpot)));
            if (isSiteUnderMaintenance) {
                return {
                    mode: 'suspended',
                    transport_mode: 'suspended',
                    transport_modes: [],
                    name: 'Unavailable',
                    icon: 'fa-ban',
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
                if (override.transport_mode === 'no_vehicle' || (override.is_custom && Array.isArray(override.transport_modes) && override.transport_modes.length === 0)) {
                    return {
                        mode: 'no_vehicle',
                        transport_mode: 'no_vehicle',
                        transport_modes: [],
                        name: 'No Vehicle Selected',
                        full_names: 'No Vehicle Selected',
                        icon: '',
                        cost: 0,
                        leg_cost: 0,
                        distance_km: distKm,
                        warning: null,
                        is_inaccessible: false,
                        is_non_drivable: false,
                        is_custom: true,
                        has_selection: false
                    };
                }

                let modes = window.parseCompositeTransportModes(override.transport_modes || override.transport_mode);
                if (modes.length === 0) {
                    return {
                        mode: 'no_vehicle',
                        transport_mode: 'no_vehicle',
                        transport_modes: [],
                        name: 'No Vehicle Selected',
                        full_names: 'No Vehicle Selected',
                        icon: '',
                        cost: 0,
                        leg_cost: 0,
                        distance_km: distKm,
                        warning: null,
                        is_inaccessible: false,
                        is_non_drivable: false,
                        is_custom: true,
                        has_selection: false
                    };
                }

                // Filter out any vehicles not accessible for this spot only if not an explicit custom leg selection
                if (!override.is_custom) {
                    const availModes = modes.filter(m => window.isVehicleAllowedForSpot(m, toSpot).allowed);
                    if (availModes.length > 0 && availModes.length < modes.length) {
                        modes = availModes;
                    }
                }

                const unitCost = window.calculateSingleLegCost(modes.join(' + '), distKm, muniA, muniB);
                const cost = window.calculatePartyTransitFare(unitCost, modes.join(' + '));

                let name = '';
                let icon = 'fa-car';
                if (modes.length === 1) {
                    name = window.getVehicleDisplayName(modes[0]);
                    icon = (modes[0] === 'motorcycle' || modes[0] === 'tricycle') ? 'fa-motorcycle' : (modes[0].includes('bus') ? 'fa-bus' : 'fa-car');
                } else {
                    name = `${modes.length} Vehicles`;
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
                    unit_cost: unitCost,
                    distance_km: distKm,
                    warning: !hasInaccessible ? (isNonDrivable && modes.includes('own_car') ? 'Trailhead drop-off only' : null) : warningReason,
                    is_inaccessible: hasInaccessible,
                    is_non_drivable: isNonDrivable,
                    is_custom: true,
                    has_selection: true
                };
            }

            const isNonDrivable = Boolean(toSpot && (toSpot.accessible_by_private_vehicle === 0 || toSpot.accessible_by_private_vehicle === false || toSpot.accessible_by_private_vehicle === '0'));

            const tripModes = (typeof window.getNormalizedTripModes === 'function')
                ? window.getNormalizedTripModes()
                : (curGlobalTransport ? window.parseCompositeTransportModes(curGlobalTransport) : []);

            const isGlobalNoVeh = (tripModes.length === 0 || tripModes.includes('no_vehicle') || tripModes.every(m => !m || m === 'no_vehicle' || String(m).toLowerCase().includes('no vehicle')));

            if (!isGlobalNoVeh && typeof window.resolveLegOptimalTransport === 'function') {
                const resolved = window.resolveLegOptimalTransport(fromSpot, toSpot, distKm, curGlobalTransport || tripModes.join(' + '));
                const check = window.isVehicleAllowedForSpot(resolved.mode, toSpot);
                const subModes = (Array.isArray(resolved.transport_modes) && resolved.transport_modes.length > 0)
                    ? resolved.transport_modes
                    : window.parseCompositeTransportModes(resolved.mode);

                let name = resolved.name;
                if (subModes.length > 1) {
                    name = `${subModes.length} Vehicles`;
                }

                const unitCost = resolved.cost;
                const cost = window.calculatePartyTransitFare(unitCost, resolved.mode);

                return {
                    ...resolved,
                    transport_mode: resolved.mode,
                    transport_modes: subModes,
                    name: name,
                    full_names: subModes.map(m => window.getVehicleDisplayName(m)).join(', '),
                    cost: cost,
                    leg_cost: cost,
                    unit_cost: unitCost,
                    distance_km: distKm,
                    warning: check.allowed ? resolved.warning : check.reason,
                    is_inaccessible: !check.allowed,
                    is_non_drivable: isNonDrivable,
                    is_custom: false,
                    has_selection: true
                };
            }

            return {
                mode: 'no_vehicle',
                transport_mode: 'no_vehicle',
                transport_modes: [],
                name: 'No Vehicle Selected',
                full_names: 'No Vehicle Selected',
                icon: '',
                cost: 0,
                leg_cost: 0,
                distance_km: distKm,
                warning: null,
                is_inaccessible: false,
                is_non_drivable: isNonDrivable,
                is_custom: false,
                has_selection: false
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
            const muniB = window.getSpotMuniName(toSpot);
            const muniA = fromSpot ? window.getSpotMuniName(fromSpot) : muniB;

            let distKm = 2.0;
            if (legIdx === 0) {
                const toLat = toSpot.lat || toSpot.latitude;
                const toLng = toSpot.lng || toSpot.longitude;
                const placeKey = String(toSpot.id || toSpot.name);
                if (window._cachedNextStopOSRM && window._cachedNextStopOSRM.spotKey === placeKey && window._cachedNextStopOSRM.distanceKm) {
                    distKm = window._cachedNextStopOSRM.distanceKm;
                } else if (typeof window.getDistanceAndETA === 'function' && toLat && toLng) {
                    const eta = window.getDistanceAndETA(toLat, toLng);
                    if (eta && eta.distanceKm) distKm = eta.distanceKm;
                }
            } else {
                const fromLat = fromSpot.lat || fromSpot.latitude;
                const fromLng = fromSpot.lng || fromSpot.longitude;
                const toLat = toSpot.lat || toSpot.latitude;
                const toLng = toSpot.lng || toSpot.longitude;
                const legKey = `${fromSpot.id || fromSpot.name}_${toSpot.id || toSpot.name}`;
                if (window._cachedLegOSRM && window._cachedLegOSRM[legKey] && window._cachedLegOSRM[legKey].distanceKm) {
                    distKm = window._cachedLegOSRM[legKey].distanceKm;
                } else if (typeof window.calculateLegETA === 'function' && fromLat && fromLng && toLat && toLng) {
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
            } catch (e) { }

            const isNonDrivable = Boolean(toSpot && (toSpot.accessible_by_private_vehicle === 0 || toSpot.accessible_by_private_vehicle === false || toSpot.accessible_by_private_vehicle === '0'));
            const isSiteUnderMaintenance = Boolean(
                window.isSpotUnderMaintenance(toSpot) ||
                (fromSpot && window.isSpotUnderMaintenance(fromSpot)) ||
                (typeof window.getLegTransportInfo === 'function' && window.getLegTransportInfo(legIdx).is_maintenance)
            );
            window.currentLegIsSiteUnderMaintenance = isSiteUnderMaintenance;

            // If any destination on this leg is under maintenance, DO NOT show the Choose Leg Transport opensheet modal!
            if (isSiteUnderMaintenance) {
                const modal = document.getElementById('leg-transport-modal');
                if (modal) modal.style.display = 'none';
                if (typeof showToast === 'function') {
                    const maintName = window.isSpotUnderMaintenance(toSpot) ? toName : (fromSpot ? fromName : 'This destination');
                    showToast(`${maintName || 'This site'} is currently under maintenance or temporarily closed. Transport options are disabled.`);
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
                    cost: window.calculatePartyTransitFare(window.calculateSingleLegCost('mpuj', distKm, muniA, muniB), 'mpuj'),
                    isRecommended: mpujOptimal,
                    category: 'Public'
                },
                {
                    mode: 'tpuj',
                    name: 'TPUJ (Traditional Jeepney)',
                    desc: 'Classic open-air commuter jeepney',
                    icon: 'fa-van-shuttle',
                    cost: window.calculatePartyTransitFare(window.calculateSingleLegCost('tpuj', distKm, muniA, muniB), 'tpuj'),
                    isRecommended: false,
                    category: 'Public'
                },
                {
                    mode: 'tricycle',
                    name: 'Tricycle',
                    desc: (!isCross ? 'Direct point-to-point drop-off' : 'Local town & beach transit'),
                    icon: 'fa-motorcycle',
                    cost: window.calculatePartyTransitFare(window.calculateSingleLegCost('tricycle', distKm, muniA, muniB), 'tricycle'),
                    isRecommended: trikeOptimal,
                    category: 'Public'
                },
                {
                    mode: 'pub_regular',
                    name: 'PUB Regular (Ordinary Bus)',
                    desc: 'Provincial highway regular passenger bus',
                    icon: 'fa-bus',
                    cost: window.calculatePartyTransitFare(window.calculateSingleLegCost('pub_regular', distKm, muniA, muniB), 'pub_regular'),
                    isRecommended: false,
                    category: 'Public'
                },
                {
                    mode: 'pub_aircon',
                    name: 'PUB Aircon (Aircon Bus)',
                    desc: 'Provincial air-conditioned coach bus',
                    icon: 'fa-bus',
                    cost: window.calculatePartyTransitFare(window.calculateSingleLegCost('pub_aircon', distKm, muniA, muniB), 'pub_aircon'),
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
            } else if (override && override.is_custom && Array.isArray(override.transport_modes) && override.transport_modes.length > 0) {
                initialModes = window.parseCompositeTransportModes(override.transport_modes);
            } else if (override && override.is_custom && override.transport_mode && override.transport_mode !== 'no_vehicle') {
                initialModes = window.parseCompositeTransportModes(override.transport_mode);
            } else {
                // Do NOT add a default - make all vehicles unselected by default
                initialModes = [];
            }

            window.currentLegModalSelectedModes = initialModes;

            window.renderLegModalList(availableCount);

            const modal = portModalToBody('leg-transport-modal');
            if (modal) {
                modal.style.display = 'flex';
            }

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

                const isPrivate = (opt.category === 'Private') || ['own_car', 'motorcycle', 'private_van', 'van', 'private_car', 'car', 'walking'].includes(optNorm);
                const costStr = (opt.cost > 0)
                    ? `₱${opt.cost.toFixed(2)}`
                    : (isPrivate ? '' : '₱0');
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
                        ${(opt.isAvail && costStr) ? `<div style="font-size:14.5px; font-weight:900; color:${costColor}; letter-spacing:-0.2px;">${costStr}</div>` : (!opt.isAvail ? `<div style="font-size:14.5px; font-weight:900; color:#94a3b8; letter-spacing:-0.2px;">—</div>` : '')}
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

            // Toggle Unselect All button: only show if there is at least one selected vehicle
            const unselectBtn = document.getElementById('leg-modal-unselect-btn');
            if (unselectBtn) {
                unselectBtn.style.display = (selectedModes.length > 0 && !isSiteMaint) ? 'inline-block' : 'none';
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
                    const u = window.calculateSingleLegCost(m, window.currentLegModalDistKm, window.currentLegModalMuniA, window.currentLegModalMuniB);
                    totalLegFare += window.calculatePartyTransitFare(u, m);
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
                    btnApply.innerHTML = `Unavailable`;
                } else {
                    btnApply.disabled = false;
                    btnApply.style.opacity = '1';
                    btnApply.style.cursor = 'pointer';
                    btnApply.style.width = 'auto';
                    btnApply.style.justifyContent = 'flex-start';
                    btnApply.innerHTML = selectedModes.length > 0
                        ? `<i class="fa-solid fa-check"></i> Done (${selectedModes.length})`
                        : `<i class="fa-solid fa-check"></i> Done`;
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
                list.splice(idx, 1);
            } else {
                list.push(norm);
            }

            window.currentLegModalSelectedModes = list;
            window.renderLegModalList();
        };

        window.unselectAllLegCandidates = function () {
            window.currentLegModalSelectedModes = [];
            window.renderLegModalList();
        };

        window.applyLegVehicleSelection = function () {
            const legIdx = window.currentLegModalIdx;
            if (legIdx === null || legIdx === undefined) return;

            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            const toSpot = draft[legIdx];
            const fromSpot = (legIdx === 0) ? null : draft[legIdx - 1];
            const isSiteUnderMaintenance = Boolean(
                window.isSpotUnderMaintenance(toSpot) ||
                (fromSpot && window.isSpotUnderMaintenance(fromSpot)) ||
                (typeof window.getLegTransportInfo === 'function' && window.getLegTransportInfo(legIdx).is_maintenance)
            );
            if (isSiteUnderMaintenance) {
                if (typeof showToast === 'function') {
                    showToast('Transit cannot be configured while this destination is under maintenance.');
                }
                const modal = document.getElementById('leg-transport-modal');
                if (modal) modal.style.display = 'none';
                return;
            }

            const selectedModes = window.parseCompositeTransportModes(window.currentLegModalSelectedModes || []);
            let overrides = [];
            try {
                overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
            } catch (e) { overrides = []; }

            if (selectedModes.length === 0) {
                overrides[legIdx] = {
                    transport_mode: 'no_vehicle',
                    transport_modes: [],
                    leg_cost: 0,
                    leg_distance_km: parseFloat(window.currentLegModalDistKm) || 0,
                    display_name: 'Select your vehicle',
                    full_names: 'Select your vehicle',
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
                    showToast(`Stop ${legIdx + 1} transit set to Select your vehicle`);
                }
                return;
            }

            let totalCost = 0;
            selectedModes.forEach(m => {
                const u = window.calculateSingleLegCost(m, window.currentLegModalDistKm, window.currentLegModalMuniA, window.currentLegModalMuniB);
                totalCost += window.calculatePartyTransitFare(u, m);
            });

            const displayNames = selectedModes.map(m => window.getVehicleDisplayName(m));

            let displayName = '';
            if (selectedModes.length === 1) {
                displayName = displayNames[0];
            } else {
                displayName = `${selectedModes.length} Vehicles`;
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

            // Leg vehicle configuration is strictly isolated to this leg.
            // It does NOT overwrite or accumulate into the overall Trip Transportation.
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
                showToast(`Stop ${legIdx + 1} transit set to ${displayName}`);
            }
        };

        window.closeLegTransportModal = function () {
            document.querySelectorAll('#leg-transport-modal').forEach(m => {
                m.style.display = 'none';
            });

            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.remove('nav-hidden');
            document.body.classList.remove('itin-confirm-open');
        };

        window.generateTripSummaryHtmlPayload = function () {
            const draft = (typeof window.getEffectiveDraft === 'function')
                ? window.getEffectiveDraft()
                : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

            // Companion & traveler data
            const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            const compAdults = isGroup ? Math.max(0, parseInt(document.getElementById('companion-adults-count')?.textContent || '0', 10)) : 0;
            const compKids = isGroup ? Math.max(0, parseInt(document.getElementById('companion-kids-count')?.textContent || '0', 10)) : 0;
            const compSeniors = isGroup ? Math.max(0, parseInt(document.getElementById('companion-seniors-count')?.textContent || '0', 10)) : 0;
            const compPwds = isGroup ? Math.max(0, parseInt(document.getElementById('companion-pwds-count')?.textContent || '0', 10)) : 0;
            const compMales = isGroup ? Math.max(0, parseInt(document.getElementById('companion-males-count')?.textContent || '0', 10)) : 0;
            const compFemales = isGroup ? Math.max(0, parseInt(document.getElementById('companion-females-count')?.textContent || '0', 10)) : 0;
            const totalGenderCompanions = compMales + compFemales;
            const totalTravelers = 1 + (isGroup ? totalGenderCompanions : 0);
            const numAdults = 1 + compAdults;
            const numKids = compKids;
            const numSeniors = compSeniors;
            const numPwds = compPwds;
            const totalPayingTravelers = numAdults + numKids + numSeniors + numPwds;

            if (!draft || draft.length === 0) {
                return {
                    summaryHtml: `
                        <div style="text-align:center; padding:24px 16px; color:#64748b; background:#ffffff; border-radius:16px; border:1.5px solid #e2e8f0;">
                            <i class="fa-regular fa-compass" style="font-size:28px; color:#94a3b8; margin-bottom:8px;"></i>
                            <div style="font-size:13.5px; font-weight:700; color:#334155;">No Destinations Added</div>
                            <div style="font-size:11.5px; color:#94a3b8; margin-top:2px;">Add spots to your itinerary to view your complete trip summary.</div>
                        </div>
                    `,
                    grandTotal: 0,
                    totalSiteFees: 0,
                    totalTripEstimatedCost: 0
                };
            }

            // 1. Build Travelers & Companions Summary Card (matching Image 2)
            let travelersCardHtml = '';
            if (!isGroup) {
                travelersCardHtml = `
                    <div style="background:#ffffff !important; border-radius:16px; padding:13px 15px; border:1.5px solid #e2e8f0 !important; box-shadow:0 3px 10px rgba(0, 0, 0, 0.04) !important; display:flex; flex-direction:column; gap:6px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="font-size:11px; font-weight:800; color:#0284c7; text-transform:uppercase; letter-spacing:0.5px;">
                                Travelers & Companions
                            </div>
                            <span style="font-size:10.5px; font-weight:800; color:#0369a1; background:#e0f2fe; padding:2px 8px; border-radius:100px;">
                                Solo Trip
                            </span>
                        </div>
                        <div style="font-size:13.5px; font-weight:800; color:#0f172a;">
                            1 Traveler <span style="font-size:11.5px; font-weight:600; color:#64748b;">(You)</span>
                        </div>
                    </div>
                `;
            } else {
                const feePills = [];
                if (compAdults > 0) feePills.push(`<span style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-user" style="color:#2563eb; font-size:10px;"></i> ${compAdults} Adult${compAdults > 1 ? 's' : ''}</span>`);
                if (compKids > 0) feePills.push(`<span style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-child" style="color:#16a34a; font-size:10px;"></i> ${compKids} Kid${compKids > 1 ? 's' : ''}</span>`);
                if (compSeniors > 0) feePills.push(`<span style="background:#fffbeb; color:#92400e; border:1px solid #fde68a; font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-person-cane" style="color:#d97706; font-size:10px;"></i> ${compSeniors} Senior${compSeniors > 1 ? 's' : ''}</span>`);
                if (compPwds > 0) feePills.push(`<span style="background:#faf5ff; color:#6b21a8; border:1px solid #e9d5ff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-wheelchair" style="color:#9333ea; font-size:10px;"></i> ${compPwds} PWD</span>`);

                travelersCardHtml = `
                    <div style="background:#ffffff !important; border-radius:16px; padding:13px 15px; border:1.5px solid #e2e8f0 !important; box-shadow:0 3px 10px rgba(0, 0, 0, 0.04) !important; display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="font-size:11px; font-weight:800; color:#0284c7; text-transform:uppercase; letter-spacing:0.5px;">
                                Travelers & Companions
                            </div>
                            <span style="font-size:10.5px; font-weight:800; color:#0369a1; background:#e0f2fe; padding:2px 8px; border-radius:100px;">
                                With Companions
                            </span>
                        </div>
                        <div>
                            <div style="font-size:13.5px; font-weight:800; color:#0f172a;">
                                ${totalTravelers} Total Travelers <span style="font-size:11.5px; font-weight:600; color:#64748b;">(You + ${totalGenderCompanions} Companions)</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px; margin-top:6px;">
                                <span style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:11px; font-weight:700; padding:3px 9px; border-radius:8px; display:inline-flex; align-items:center; gap:5px;">
                                    <i class="fa-solid fa-mars" style="color:#2563eb; font-size:11px;"></i> ${compMales} Male
                                </span>
                                <span style="background:#fdf2f8; color:#be185d; border:1px solid #fbcfe8; font-size:11px; font-weight:700; padding:3px 9px; border-radius:8px; display:inline-flex; align-items:center; gap:5px;">
                                    <i class="fa-solid fa-venus" style="color:#db2777; font-size:11px;"></i> ${compFemales} Female
                                </span>
                            </div>
                        </div>
                        ${feePills.length > 0 ? `
                            <div style="padding-top:7px; border-top:1px dashed #e2e8f0; margin-top:1px;">
                                <div style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:5px;">
                                    Traveler Types (${compAdults + compKids + compSeniors + compPwds} of ${totalGenderCompanions} Categorized)
                                </div>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    ${feePills.join('')}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
            }

            const routeHeaderHtml = `
                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:4px; padding:0 2px;">
                    <span style="font-size:11px; font-weight:800; color:#0284c7; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:5px;">
                        <i class="fa-solid fa-route" style="font-size:11px;"></i> Route & Destinations (${draft.length} Stop${draft.length > 1 ? 's' : ''})
                    </span>
                </div>
            `;

            // 2. Build Destination Cards and Aggregate Fees Summary for Trip Cost Breakdown
            let grandTotal = 0;
            let totalSiteFeesAllSpots = 0;
            let cardsHtml = '';

            let totAdultEntrance = 0;
            let totChildEntrance = 0;
            let totPwdEntrance = 0;
            let totSeniorEntrance = 0;

            let totAdultEnvi = 0;
            let totChildEnvi = 0;
            let totPwdEnvi = 0;
            let totSeniorEnvi = 0;

            try {
                draft.forEach((dest, idx) => {
                    const info = (typeof window.getLegTransportInfo === 'function')
                        ? window.getLegTransportInfo(idx)
                        : { mode: 'own_car', name: 'Own Car', cost: 0, distance_km: 2.0 };

                    const isMaintenance = Boolean(info && info.is_maintenance);
                    const cost = (info && !isMaintenance) ? parseFloat(info.cost || info.leg_cost || 0) : 0;
                    grandTotal += cost;

                    const fromName = (idx === 0) ? 'Your Starting Location' : (draft[idx - 1].name || `Stop ${idx}`);
                    const toName = dest.name || `Destination ${idx + 1}`;
                    const muni = (typeof window.getSpotMuniName === 'function') ? window.getSpotMuniName(dest) : '';
                    const distKm = (info && info.distance_km != null) ? parseFloat(info.distance_km).toFixed(1) : '—';
                    const isSelected = Boolean(info && info.has_selection && !String(info.name || '').toLowerCase().includes('select your vehicle') && !String(info.name || '').toLowerCase().includes('no vehicle'));

                    // Site fees & rates based directly on Image 2
                    const adultFee = parseFloat(dest.adult_fee || dest.adultFee || 0);
                    const entranceFee = parseFloat(dest.entrance_fee || dest.entranceFee || dest.fee || 0);
                    const baseAdult = adultFee > 0 ? adultFee : (entranceFee > 0 ? entranceFee : 0);
                    const kidsFee = (dest.kids_fee !== undefined && dest.kids_fee !== null && dest.kids_fee !== '')
                        ? (parseFloat(dest.kids_fee) || 0)
                        : baseAdult;
                    const seniorFee = (dest.senior_citizen_fee !== undefined && dest.senior_citizen_fee !== null && dest.senior_citizen_fee !== '' && parseFloat(dest.senior_citizen_fee) > 0)
                        ? parseFloat(dest.senior_citizen_fee)
                        : (baseAdult > 0 ? baseAdult * 0.8 : 0);
                    const pwdFee = (dest.pwd_fee !== undefined && dest.pwd_fee !== null && dest.pwd_fee !== '' && parseFloat(dest.pwd_fee) > 0)
                        ? parseFloat(dest.pwd_fee)
                        : (baseAdult > 0 ? baseAdult * 0.8 : 0);
                    const envFee = parseFloat(dest.environmental_fee || dest.environmentalFee || dest.envFee || 0) || 0;

                    const displayAdult = adultFee > 0 ? adultFee : 0;
                    const displayChild = kidsFee > 0 ? kidsFee : 0;
                    const displayPwd = pwdFee > 0 ? pwdFee : 0;
                    const displaySenior = seniorFee > 0 ? seniorFee : 0;
                    const displayEntrance = entranceFee > 0 ? entranceFee : 0;
                    const displayEnvi = envFee > 0 ? envFee : 0;
                    const childEnvFee = kidsFee > 0 ? displayEnvi : 0;

                    const spotEnvSenior = (seniorFee > 0 && baseAdult > 0) ? Math.round(displayEnvi * (seniorFee / baseAdult)) : (displayEnvi > 0 ? Math.round(displayEnvi * 0.8) : 0);
                    const spotEnvPwd = (pwdFee > 0 && baseAdult > 0) ? Math.round(displayEnvi * (pwdFee / baseAdult)) : (displayEnvi > 0 ? Math.round(displayEnvi * 0.8) : 0);
                    const spotEntranceCatTotal = (numAdults * baseAdult) + (numKids * kidsFee) + (numSeniors * seniorFee) + (numPwds * pwdFee);
                    const spotEnvTotal = (numAdults * displayEnvi) + (numKids * childEnvFee) + (numSeniors * spotEnvSenior) + (numPwds * spotEnvPwd);
                    const spotPartyCost = isMaintenance ? 0 : (spotEntranceCatTotal + spotEnvTotal);
                    totalSiteFeesAllSpots += spotPartyCost;

                    if (!isMaintenance) {
                        totAdultEntrance += (numAdults * baseAdult);
                        totChildEntrance += (numKids * kidsFee);
                        totPwdEntrance += (numPwds * pwdFee);
                        totSeniorEntrance += (numSeniors * seniorFee);

                        totAdultEnvi += (numAdults * displayEnvi);
                        totChildEnvi += (numKids * childEnvFee);
                        totPwdEnvi += (numPwds * spotEnvPwd);
                        totSeniorEnvi += (numSeniors * spotEnvSenior);
                    }

                    // Vehicle badge / icon
                    let transportMarkup = '';
                    if (isMaintenance) {
                        transportMarkup = `
                            <span style="display:inline-flex; align-items:center; gap:5px; background:#fef2f2; color:#ef4444; border:1px solid #fecaca; font-size:11px; font-weight:800; padding:4px 10px; border-radius:100px;">
                                <i class="fa-solid fa-ban" style="font-size:10px;"></i> Unavailable
                            </span>
                        `;
                    } else {
                        let vehicleLabel = 'No Vehicle Selected';
                        let iconMarkup = '<i class="fa-solid fa-ban" style="color:#ef4444; font-size:11px;"></i>';

                        if (isSelected) {
                            const mCount = (info.transport_modes && Array.isArray(info.transport_modes)) ? info.transport_modes.length : 0;
                            if (mCount > 1) {
                                vehicleLabel = `${mCount} Vehicles`;
                                iconMarkup = '<i class="fa-solid fa-shuffle" style="color:#0284c7; font-size:12px;"></i>';
                            } else if (info.name && info.name.includes('Vehicles')) {
                                vehicleLabel = info.name;
                                iconMarkup = '<i class="fa-solid fa-shuffle" style="color:#0284c7; font-size:12px;"></i>';
                            } else {
                                vehicleLabel = info.full_names || info.name || 'Vehicle Selected';
                                iconMarkup = info.icon ? `<i class="fa-solid ${info.icon}" style="color:#0284c7; font-size:12px;"></i>` : '<i class="fa-solid fa-car" style="color:#0284c7; font-size:12px;"></i>';
                            }
                        }

                        transportMarkup = `
                            <div style="display:inline-flex; align-items:center; gap:6px; background:#f8fafc; color:#1e3a8a; font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:100px; border:1.5px solid #e2e8f0;">
                                ${iconMarkup}
                                <span style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${vehicleLabel}</span>
                            </div>
                        `;
                    }

                    // Fare display
                    let fareMarkup = '';
                    if (isMaintenance || !isSelected) {
                        fareMarkup = `<span style="font-size:13px; font-weight:700; color:#94a3b8;">—</span>`;
                    } else if (cost === 0) {
                        fareMarkup = `<span style="font-size:13.5px; font-weight:800; color:#059669;">₱0.00</span> <span style="font-size:10px; font-weight:700; color:#64748b;">(Free)</span>`;
                    } else {
                        fareMarkup = `<span style="font-size:15px; font-weight:900; color:#0284c7;">₱${cost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`;
                    }

                    // Warning note
                    let noteMarkup = '';
                    if (isMaintenance) {
                        noteMarkup = `
                            <div style="margin-top:6px; font-size:11px; font-weight:600; color:#b91c1c; background:#fef2f2; padding:6px 10px; border-radius:8px; border:1px solid #fecaca; display:flex; align-items:center; gap:6px;">
                                <i class="fa-solid fa-triangle-exclamation" style="font-size:11px; color:#ef4444;"></i>
                                <span>Destination temporarily closed / under maintenance</span>
                            </div>
                        `;
                    } else if (info && info.warning) {
                        noteMarkup = `
                            <div style="margin-top:6px; font-size:11px; font-weight:600; color:#b45309; background:#fffbeb; padding:6px 10px; border-radius:8px; border:1px solid #fde68a; display:flex; align-items:center; gap:6px;">
                                <i class="fa-solid fa-circle-info" style="font-size:11px; color:#d97706;"></i>
                                <span>${info.warning}</span>
                            </div>
                        `;
                    }

                    cardsHtml += `
                        <div style="background:#ffffff !important; border-radius:16px; padding:13px 15px; border:1.5px solid #e2e8f0 !important; box-shadow:0 3px 10px rgba(0, 0, 0, 0.04) !important; display:flex; flex-direction:column; gap:8px; color:#0f172a;">
                            <!-- Header: Stop Badge + Distance -->
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size:11px; font-weight:800; color:#1e3a8a; background:#e0edff; padding:3px 10px; border-radius:100px; border:none !important; outline:none !important;">
                                    Stop ${idx + 1}
                                </span>
                                <span style="font-size:11.5px; font-weight:700; color:#64748b; display:inline-flex; align-items:center; gap:4px;">
                                    <i class="fa-solid fa-route" style="font-size:10.5px; color:#0284c7;"></i> ${distKm} km
                                </span>
                            </div>

                            <!-- Route Segment: From -> To -->
                            <div style="display:flex; flex-direction:column; gap:4px; padding:2px 0;">
                                <div style="display:flex; align-items:center; gap:7px; font-size:11.5px; color:#64748b; font-weight:600;">
                                    <i class="fa-regular fa-circle-dot" style="font-size:9.5px; color:#0284c7; flex-shrink:0;"></i>
                                    <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${fromName}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:7px; font-size:13.5px; color:#0f172a; font-weight:800;">
                                    <i class="fa-solid fa-location-dot" style="font-size:12px; color:#0284c7; flex-shrink:0;"></i>
                                    <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${toName}</span>
                                    ${muni ? `<span style="font-size:10px; font-weight:700; color:#0284c7; background:#e0f2fe; padding:2px 7px; border-radius:6px; flex-shrink:0;">${muni}</span>` : ''}
                                </div>
                            </div>

                            <!-- Footer: Transport Mode & Price -->
                            <div style="display:flex; align-items:center; justify-content:space-between; padding-top:8px; border-top:1px dashed #e2e8f0; margin-top:2px;">
                                <div>${transportMarkup}</div>
                                <div style="text-align:right;">
                                    <div style="font-size:9.5px; font-weight:700; color:#64748b; text-transform:uppercase;">Estimated Fare</div>
                                    <div>${fareMarkup}</div>
                                </div>
                            </div>

                            ${noteMarkup}
                        </div>
                    `;
                });
            } catch (err) {
                console.error("Error generating transit summary items:", err);
            }

            const totEntranceAll = totAdultEntrance + totChildEntrance + totPwdEntrance + totSeniorEntrance;
            const totEnviAll = totAdultEnvi + totChildEnvi + totPwdEnvi + totSeniorEnvi;
            const grandTotalCost = grandTotal + totalSiteFeesAllSpots;

            const overallCostCardHtml = `
                <div style="background:#ffffff !important; border-radius:16px; padding:12px 14px; border:1.5px solid #e2e8f0 !important; box-shadow:0 3px 10px rgba(0, 0, 0, 0.04) !important; display:flex; flex-direction:column; gap:8px;">
                    <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:8px;">
                        <!-- Transit Total Row -->
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                            <span style="color:#475569; font-weight:600; display:inline-flex; align-items:center; gap:7px;">
                                <i class="fa-solid fa-van-shuttle" style="color:#64748b; font-size:12px; width:14px; text-align:center;"></i> Transit Total
                            </span>
                            <span style="font-weight:800; color:#0f172a;">
                                ₱${grandTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                        </div>

                        <!-- Entrance Fees Row -->
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                            <span style="color:#475569; font-weight:600; display:inline-flex; align-items:center; gap:7px;">
                                <i class="fa-solid fa-ticket" style="color:#64748b; font-size:12px; width:14px; text-align:center;"></i> Entrance Fees
                            </span>
                            <span style="font-weight:800; color:#0f172a;">
                                ${totEntranceAll > 0 ? '₱' + totEntranceAll.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'Free'}
                            </span>
                        </div>

                        <!-- Environmental Fees Row -->
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                            <span style="color:#475569; font-weight:600; display:inline-flex; align-items:center; gap:7px;">
                                <i class="fa-solid fa-leaf" style="color:#64748b; font-size:12px; width:14px; text-align:center;"></i> Environmental Fees
                            </span>
                            <span style="font-weight:800; color:#0f172a;">
                                ${totEnviAll > 0 ? '₱' + totEnviAll.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'Free'}
                            </span>
                        </div>
                    </div>

                    <!-- Estimated Total Cost Row (Positioned below Environmental Fees) -->
                    <div style="display:flex; align-items:center; justify-content:space-between; padding-top:8px; border-top:1.5px solid #f1f5f9; margin-top:0;">
                        <div>
                            <span style="font-size:12.5px; font-weight:800; color:#0f172a; letter-spacing:-0.1px; display:block;">
                                Estimated Total Cost
                            </span>
                        </div>
                        <span style="font-size:16px; font-weight:900; color:#0f172a; letter-spacing:-0.2px;">
                            ₱${grandTotalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </span>
                    </div>
                </div>
            `;

            const totalTripEstimatedCost = grandTotal + totalSiteFeesAllSpots;
            const fullContentHtml = overallCostCardHtml;

            return {
                summaryHtml: fullContentHtml,
                travelersCardHtml: travelersCardHtml,
                grandTotal: grandTotal,
                totalSiteFees: totalSiteFeesAllSpots,
                totalTripEstimatedCost: totalTripEstimatedCost
            };
        };

        window.renderSaveModalSummaryContent = function () {
            const container = document.getElementById('save-modal-summary-items');
            if (!container) return;
            const payload = window.generateTripSummaryHtmlPayload();
            container.innerHTML = payload.summaryHtml;
            const costEl = document.getElementById('save-estimated-cost');
            if (costEl) {
                costEl.textContent = '₱' + payload.totalTripEstimatedCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        };

        window.openTransitSummaryOpensheet = function () {
            const modal = portModalToBody('transit-summary-opensheet');
            if (!modal) return;

            const listEl = document.getElementById('transit-summary-items-list');
            const totalFareEl = document.getElementById('transit-summary-total-fare');
            const totalCostSubEl = document.getElementById('trip-summary-total-cost-sub');

            const payload = window.generateTripSummaryHtmlPayload();
            if (listEl) {
                listEl.innerHTML = (payload.travelersCardHtml || '') + payload.summaryHtml;
            }
            if (totalFareEl) {
                totalFareEl.textContent = '₱' + payload.totalTripEstimatedCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (totalCostSubEl) {
                totalCostSubEl.textContent = `Transit: ₱${payload.grandTotal.toFixed(2)}`;
                totalCostSubEl.style.display = 'block';
            }

            modal.style.display = 'flex';
            modal.style.pointerEvents = 'auto';
        };

        window.closeTransitSummaryOpensheet = function () {
            document.querySelectorAll('#transit-summary-opensheet').forEach(m => {
                m.style.display = 'none';
            });
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
            } catch (e) { }

            if (modes.length === 0) {
                const raw = localStorage.getItem('intan_elyu_draft_trip_transport');
                if (raw && raw !== 'no_vehicle' && !raw.toLowerCase().includes('no vehicle')) {
                    modes = window.parseCompositeTransportModes(raw);
                }
            }

            // Also check per-leg vehicle selections if modes is still empty
            if (modes.length === 0) {
                try {
                    const rawLegs = localStorage.getItem('intan_elyu_draft_leg_vehicles');
                    if (rawLegs) {
                        const parsedLegs = JSON.parse(rawLegs);
                        if (parsedLegs && typeof parsedLegs === 'object') {
                            const legModes = [];
                            Object.values(parsedLegs).forEach(leg => {
                                if (leg) {
                                    const legArr = leg.transport_modes || (leg.transport_mode ? [leg.transport_mode] : []);
                                    if (Array.isArray(legArr)) {
                                        legArr.forEach(m => {
                                            if (m && m !== 'no_vehicle' && !String(m).toLowerCase().includes('no vehicle') && !legModes.includes(m)) {
                                                legModes.push(m);
                                            }
                                        });
                                    }
                                }
                            });
                            if (legModes.length > 0) {
                                modes = legModes;
                            }
                        }
                    }
                } catch (e) { }
            }

            // Auto-heal legacy or corrupted localStorage entries and sync trip-transport input
            try {
                if (modes.length > 0) {
                    const cleanComposite = (modes.length === 1) ? modes[0] : modes.join(' + ');
                    const storedRaw = localStorage.getItem('intan_elyu_draft_trip_transport');
                    if (storedRaw !== cleanComposite) {
                        localStorage.setItem('intan_elyu_draft_trip_transport', cleanComposite);
                        localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify(modes));
                    }
                    const transInput = document.getElementById('trip-transport');
                    if (transInput && (!transInput.value || /no[_ ]vehicle/i.test(transInput.value))) {
                        transInput.value = cleanComposite;
                    }
                }
            } catch (e) { }

            return modes;
        };

        window.updateDraftTravelModeBar = function () {
            const bar = document.getElementById('draft-travel-mode-bar');
            if (!bar) return;
            const iconEl = document.getElementById('draft-travel-mode-icon');
            const labelEl = document.getElementById('draft-travel-mode-label');

            const tripModes = window.getNormalizedTripModes();

            if (tripModes.length === 0) {
                if (iconEl) iconEl.innerHTML = '<i class="fa-solid fa-route" style="color:#1e3a8a !important;"></i>';
                if (labelEl) labelEl.textContent = 'Select your vehicle';
            } else if (tripModes.length === 1) {
                const mode = tripModes[0];
                if (mode === 'no_vehicle') {
                    if (iconEl) iconEl.innerHTML = '<i class="fa-solid fa-ban" style="color:#ef4444 !important;"></i>';
                    if (labelEl) labelEl.textContent = 'No Vehicle Selected';
                } else {
                    if (iconEl) {
                        let html = window.getVehicleIconHtml(mode);
                        iconEl.innerHTML = html.replace(/color:[^;"]+/g, 'color:#1e3a8a !important');
                    }
                    if (labelEl) labelEl.textContent = window.getVehicleDisplayName(mode);
                }
            } else {
                if (iconEl) iconEl.innerHTML = '<i class="fa-solid fa-route" style="color:#1e3a8a !important;"></i>';
                if (labelEl) labelEl.textContent = `${tripModes.length} Vehicles`;
            }
        };

        window.starterModalSelectedModes = [];

        window.openTravelModeStarterModal = function () {
            const modal = portModalToBody('travel-mode-starter-modal');
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

            const selected = (window.starterModalSelectedModes || []).map(m => window.normalizeVehicleKey(m));

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
                } else {
                    labelEl.textContent = `${selected.length} Vehicles`;
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
                : `${selected.length} Vehicles`;

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
            document.querySelectorAll('#travel-mode-starter-modal').forEach(m => {
                m.style.display = 'none';
            });
            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.remove('nav-hidden');
            document.body.classList.remove('itin-confirm-open');
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

                try {
                    let overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
                    if (Array.isArray(overrides) && overrides.length > Math.max(i, j)) {
                        const tempLeg = overrides[i];
                        overrides[i] = overrides[j];
                        overrides[j] = tempLeg;
                        localStorage.setItem('intan_elyu_draft_leg_vehicles', JSON.stringify(overrides));
                    }
                } catch (e) { }

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
            const timeline = document.getElementById('itinerary-timeline');
            if (!timeline) return;

            const draft = window.getEffectiveDraft();
            const rawDraft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            const emptyState = document.getElementById('itinerary-empty-state');
            const fab = document.getElementById('btn-save-itinerary');
            const mapWrapper = document.getElementById('draft-map-wrapper');
            const draftPlanCard = document.getElementById('draft-plan-card-wrapper');
            const countEl = document.getElementById('itinerary-count');

            if (countEl) countEl.innerText = rawDraft.length;

            if (rawDraft.length === 0) {
                localStorage.removeItem('intan_elyu_draft_trip_transport');
                localStorage.removeItem('intan_elyu_draft_trip_transports');
                localStorage.removeItem('intan_elyu_draft_leg_vehicles');
                const transportInput = document.getElementById('trip-transport');
                if (transportInput) transportInput.value = 'no_vehicle';
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
                if (emptyState) {
                    emptyState.style.setProperty('display', 'flex', 'important');
                    emptyState.classList.remove('is-hidden');
                    emptyState.style.animation = 'none';
                    void emptyState.offsetHeight; // Trigger reflow for smooth re-animation
                    emptyState.style.animation = 'cardFadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                }
                if (fab) fab.style.setProperty('display', 'none', 'important');
                if (mapWrapper) mapWrapper.style.display = 'none';
                if (typeof window.syncStep2SliderButtons === 'function') {
                    window.syncStep2SliderButtons();
                }
                return;
            }

            if (emptyState) {
                emptyState.style.setProperty('display', 'none', 'important');
                emptyState.classList.add('is-hidden');
            }
            if (draftPlanCard) {
                draftPlanCard.classList.remove('is-hidden');
                draftPlanCard.style.setProperty('display', 'flex', 'important');
            }
            if (fab) fab.style.setProperty('display', 'flex', 'important');
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
                if (fab) fab.innerHTML = '<i class="fa-solid fa-pen-to-square" style="margin-right:8px;"></i> Update Saved Trip';

                const editingTransport = sessionStorage.getItem('editing_trip_transport');
                if (editingTransport && !sessionStorage.getItem('editing_transport_initialized')) {
                    sessionStorage.setItem('editing_transport_initialized', 'true');
                    localStorage.setItem('intan_elyu_draft_trip_transport', editingTransport);
                    const modes = (typeof window.parseCompositeTransportModes === 'function')
                        ? window.parseCompositeTransportModes(editingTransport)
                        : [editingTransport];
                    localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify(modes.length > 0 ? modes : [editingTransport]));
                    const transInput = document.getElementById('trip-transport');
                    if (transInput) transInput.value = editingTransport;
                }
            } else {
                sessionStorage.removeItem('editing_transport_initialized');
                if (pageTitleEl) pageTitleEl.textContent = 'Draft Plan';
                if (editBannerEl) editBannerEl.style.display = 'none';
                if (fab) fab.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:8px;"></i> Save Draft Plan';
            }
            if (mapWrapper) mapWrapper.style.display = 'block';

            if (typeof window.updateDraftTravelModeBar === 'function') {
                window.updateDraftTravelModeBar();
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
                startingLegHtml = `
            <div class="stops-swap-divider starting-leg-divider" style="height:4px !important; margin:0 !important;">
                <div class="stops-swap-line"></div>
            </div>`;
            }

            html += `<div class="itinerary-stops-container" id="itinerary-stops-container">`;

            html += `
        <div class="starting-point-item" onclick="window.routeToMyLocation()">
            <div class="starting-point-card" style="background:#ffffff !important; border:1.5px solid #e2e8f0 !important; outline:none !important; border-radius:20px !important; padding:14px 16px !important; box-shadow:0 4px 14px rgba(15,23,42,0.05) !important; display:flex; align-items:center; justify-content:space-between; gap:14px; cursor:pointer;">
                <div class="starting-point-info" style="flex:1; min-width:0;">
                    <div class="starting-point-label" style="font-size:10.5px; font-weight:800; color:#0284c7 !important; text-transform:uppercase; letter-spacing:0.7px; display:inline-flex; align-items:center; gap:5px; margin-bottom:3px;">
                        <i class="fa-solid fa-satellite-dish" style="font-size:9px; color:#0284c7;"></i> Starting Point
                    </div>
                    <h3 class="starting-point-title" style="margin:0; font-size:15.5px; font-weight:800; color:#0f172a !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.2px;">Your Current Location</h3>
                </div>
                <button type="button" onclick="event.stopPropagation(); window.routeToMyLocation();" class="starting-point-locate-btn" style="position:relative !important; width:auto !important; height:auto !important; min-width:auto !important; max-width:none !important; background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; border:none !important; outline:none !important; color:#ffffff !important; font-size:12px; font-weight:800; padding:8px 16px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; flex-shrink:0; box-shadow:0 2px 8px rgba(2,132,199,0.25) !important; transition:transform 0.15s ease;">
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

                    const isMaint = (typeof window.isSpotUnderMaintenance === 'function') ? window.isSpotUnderMaintenance(place) : Boolean(place.is_maintenance === 1 || place.is_maintenance === true || place.is_maintenance === '1');

                    if (isNextStop) {
                        nextStopBadge = `<span class="badge-next-stop" style="border:none !important; outline:none !important; box-shadow:none !important;"><i class="fa-solid fa-location-dot"></i> NEXT STOP</span>`;
                        if (!isMaint) {
                            const lat = place.lat || place.latitude;
                            const lng = place.lng || place.longitude;
                            const placeKey = String(place.id || place.name);
                            const hasCachedOSRM = window._cachedNextStopOSRM && window._cachedNextStopOSRM.spotKey === placeKey;
                            const eta = hasCachedOSRM ? window._cachedNextStopOSRM : window.getDistanceAndETA(lat, lng);
                            if (eta) {
                                nextStopEtaHtml = `
                            <div class="next-stop-distance-chip" id="itinerary-next-eta" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; border-radius:100px !important; padding:4px 11px !important; font-size:10px !important; font-weight:700 !important; display:inline-flex !important; align-items:center !important; margin-top:8px !important; box-shadow:0 2px 6px rgba(0,0,0,0.12) !important; white-space:nowrap !important; max-width:100% !important;">
                                <span style="color:#1e3a8a !important; font-weight:700; font-size:10px; letter-spacing:-0.1px;">${eta.distanceText} away &bull; ~${eta.durationText} drive from your location</span>
                            </div>`;
                            } else {
                                nextStopEtaHtml = `
                            <div class="next-stop-distance-chip" id="itinerary-next-eta" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; border-radius:100px !important; padding:4px 11px !important; font-size:10px !important; font-weight:700 !important; display:inline-flex !important; align-items:center !important; margin-top:8px !important; box-shadow:0 2px 6px rgba(0,0,0,0.12) !important; white-space:nowrap !important; max-width:100% !important;">
                                <span style="color:#1e3a8a !important; font-weight:700; font-size:10px; letter-spacing:-0.1px;">First destination on your itinerary route</span>
                            </div>`;
                            }
                        }
                    }

                    if (index > 0) {
                        html += `
                        <div class="stops-swap-divider">
                            <div class="stops-swap-line"></div>
                            <button type="button" class="btn-swap-pill" onclick="event.stopPropagation(); window.swapDraftStops(${index - 1}, ${index});" title="Swap Stop ${index} and Stop ${index + 1}" aria-label="Swap order">
                                <i class="fa-solid fa-arrows-up-down"></i>
                            </button>
                        </div>`;
                    }

                    const placeKey = String(place.id || ('place_' + (place.name || index)));
                    const r2ThumbUrl = window.getSpotR2Thumbnail(place);

                    html += `
                <div class="timeline-item ${isNextStop ? 'is-next-stop' : ''}" draggable="true" data-index="${index}" data-id="${place.id}" data-key="${placeKey}" style="animation-delay: ${(index + 1) * 0.08}s">
                    <div class="timeline-dot"></div>
                    <div class="swipe-container" style="position:relative; overflow:hidden; border-radius:16px !important; transform:translate3d(0,0,0); -webkit-transform:translate3d(0,0,0); -webkit-backface-visibility:hidden; backface-visibility:hidden; -webkit-mask-image:-webkit-radial-gradient(white, black); mask-image:radial-gradient(white, black);">
                        <div class="swipe-delete-bg" style="position:absolute; top:0; right:0; bottom:0; width:80px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius:0 16px 16px 0 !important; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:800; gap:4px; transform:translateX(100%); z-index:1; opacity:0; pointer-events:none; transition:transform 0.2s ease, opacity 0.2s ease;"><i class="fa-solid fa-trash-can"></i> Delete</div>
                        <div class="swipe-content" style="position:relative; z-index:2; transition:transform 0.2s ease; border-radius:16px !important; padding:10px 14px; border:none !important; outline:none !important; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; box-shadow:none !important;">
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
                                    <img src="${r2ThumbUrl}" alt="${place.name}" data-spot-id="${place.id || ''}" loading="lazy" onerror="if(window.handleImgError){const p=(window.itineraryDraft||[]).find(x=>String(x.id)===String(this.dataset.spotId));window.handleImgError(this,p?.name||'',p?.municipality||'');}else{this.onerror=null;this.src='assets/img/no_image.svg';}">
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <h3 class="place-name" style="margin:0 0 2px 0 !important; font-size:14px !important; font-weight:800; color:#ffffff; letter-spacing:-0.2px; line-height:1.2; white-space:normal !important; overflow:visible !important; text-overflow:clip !important; word-break:break-word;">${place.name}</h3>
                                    ${place.classification_status ? `<div style="margin-bottom:4px;"><span style="padding: 2px 7px; border-radius: 100px; font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${place.classification_status === 'EXIST' ? '#0284c7' : (place.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; border: none !important; outline: none !important;">${place.classification_status === 'EXIST' ? 'EXISTING' : (place.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span></div>` : ''}
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
                            const isMaint = (typeof window.isSpotUnderMaintenance === 'function') ? window.isSpotUnderMaintenance(place) : Boolean(place.is_maintenance === 1 || place.is_maintenance === true || place.is_maintenance === '1');
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
                const resetSwipe = () => {
                    content.style.transform = '';
                    content.style.borderRadius = '20px';
                    if (bg) {
                        bg.style.opacity = '0';
                        bg.style.pointerEvents = 'none';
                        bg.style.transform = 'translateX(100%)';
                    }
                };

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
                        resetSwipe();
                        if (id) window.confirmRemoveItineraryItem(id);
                    } else {
                        resetSwipe();
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

                if (bg) {
                    bg.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const id = item.dataset.id;
                        resetSwipe();
                        if (id) window.confirmRemoveItineraryItem(id);
                    });
                }
            });

            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.syncStep2SliderButtons === 'function') {
                window.syncStep2SliderButtons();
            }
        }

        window.confirmRemoveItineraryItem = async function (id) {
            const confirmed = await window.showConfirmModal('Are you sure you want to delete this?', {
                title: 'Delete Destination?',
                confirmText: 'Yes',
                cancelText: 'No'
            });
            if (confirmed) window.removeItineraryItem(id);
            return confirmed;
        };

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
            } catch (e) { }

            window.renderItinerary();
            if (typeof showToast === 'function') showToast("Destination removed from itinerary");
        };

        window.clearAllItinerary = function () {
            localStorage.removeItem('intan_elyu_draft_itinerary');
            localStorage.removeItem('intan_elyu_draft_leg_vehicles');
            localStorage.removeItem('intan_elyu_draft_trip_transport');
            localStorage.removeItem('intan_elyu_draft_trip_transports');
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
            if (window.currentItinStep !== 2) {
                window.switchItineraryStep(2);
            }
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
                    try {
                        draftMap.flyTo([loc.lat, loc.lng], 16, { animate: true, duration: 1.5 });
                    } catch (mapFlyErr) {
                        draftMap.setView([loc.lat, loc.lng], 16);
                    }
                    if (!window.myDraftMarker) {
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
                        window.myDraftMarker = L.marker([loc.lat, loc.lng], { icon: myIcon, zIndexOffset: 1000 })
                            .addTo(draftMap)
                            .bindPopup('<div style="font-size:9.5px; font-weight:800; color:#1e3a8a; line-height:1.15;">📍 Your Location</div><div style="font-size:8px; color:#64748b; margin-top:1px;">Starting Point</div>', { closeButton: false, autoPanPadding: [10, 10] });
                        window.myDraftMarker._isUserGps = true;
                        if (typeof draftMarkers !== 'undefined') draftMarkers.push(window.myDraftMarker);
                    } else {
                        window.myDraftMarker.setLatLng([loc.lat, loc.lng]);
                    }
                    try { window.myDraftMarker.openPopup(); } catch (e) { }
                }
                if (typeof showToast === 'function') {
                    showToast(loc.source === 'gps' || window.currentGPSSource === 'gps' ? "Centered on your precise GPS location 📍" : "Centered on your estimated location");
                }
            }
        };

        var itineraryFsMap = null;
        var itineraryFsLayers = [];
        window.myFsDraftMarker = null;

        function renderItineraryFsCards() {
            const container = document.getElementById('itinerary-fs-cards-carousel');
            const panel = document.getElementById('itinerary-fs-bottom-panel');
            if (!container) return;
            const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            if (!draft || draft.length === 0) {
                container.innerHTML = '';
                if (panel) panel.style.display = 'none';
                return;
            }
            if (panel) panel.style.display = 'block';
            let html = '';
            draft.forEach((place, index) => {
                const rawName = String(place.name || place.title || `Stop ${index + 1}`).trim();
                const cleanName = rawName.replace(/\\'/g, "'").replace(/\\"/g, '"').replace(/\\\\/g, '\\');
                const safeName = cleanName.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                const pLat = parseFloat(place.lat || place.latitude || 0);
                const pLng = parseFloat(place.lng || place.longitude || 0);
                html += `
                <div class="itinerary-fs-card" id="fs-card-${index}" onclick="window.zoomToItineraryFsStop(${index}, ${pLat}, ${pLng})"
                    style="flex:0 0 auto; scroll-snap-align:center; background:#ffffff !important; border:1.5px solid #e2e8f0 !important; border-radius:14px; padding:7px 13px; display:flex; align-items:center; gap:8px; box-shadow:0 2px 8px rgba(10, 25, 60, 0.08) !important; cursor:pointer; transition:all 0.2s ease; max-width:220px; min-width:110px;">
                    <div style="width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg, #00f2fe, #0284c7); color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:10.5px; font-weight:900; flex-shrink:0;">
                        ${index + 1}
                    </div>
                    <span style="font-size:12px; font-weight:800; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        ${safeName}
                    </span>
                </div>`;
            });
            container.innerHTML = html;
        }

        window.toggleItineraryFsLegend = function () {
            const body = document.getElementById('itinerary-fs-legend-body');
            const chevron = document.getElementById('itinerary-fs-legend-chevron');
            if (!body || !chevron) return;
            const isCollapsed = body.style.maxHeight === '0px' || body.getAttribute('data-collapsed') === 'true';
            if (isCollapsed) {
                body.style.maxHeight = '120px';
                body.style.opacity = '1';
                body.style.padding = '0 10px 8px 10px';
                body.setAttribute('data-collapsed', 'false');
                chevron.style.transform = 'rotate(0deg)';
            } else {
                body.style.maxHeight = '0px';
                body.style.opacity = '0';
                body.style.padding = '0 10px 0 10px';
                body.setAttribute('data-collapsed', 'true');
                chevron.style.transform = 'rotate(180deg)';
            }
        };

        window.zoomToItineraryFsStop = function (index, lat, lng) {
            if (!itineraryFsMap) return;
            const pLat = parseFloat(lat);
            const pLng = parseFloat(lng);
            if (!isNaN(pLat) && !isNaN(pLng) && pLat !== 0 && pLng !== 0) {
                itineraryFsMap.flyTo([pLat, pLng], 16, { animate: true, duration: 1.2 });
            }

            // Highlight the clicked card
            document.querySelectorAll('.itinerary-fs-card').forEach(c => {
                c.style.borderColor = '#e2e8f0';
                c.style.background = '#ffffff';
                c.style.transform = 'scale(1)';
                c.style.boxShadow = '0 4px 14px rgba(10, 25, 60, 0.14)';
            });
            const activeCard = document.getElementById(`fs-card-${index}`);
            if (activeCard) {
                activeCard.style.borderColor = '#0284c7';
                activeCard.style.background = '#f0f9ff';
                activeCard.style.transform = 'scale(1.04)';
                activeCard.style.boxShadow = '0 6px 18px rgba(2, 132, 199, 0.25)';
                activeCard.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }

            // Open marker popup on fullscreen map
            const targetMarker = itineraryFsLayers.find(l => l instanceof L.Marker && l._stopIndex === index);
            if (targetMarker && targetMarker.openPopup) {
                targetMarker.openPopup();
            }
        };

        window.locateItineraryFullscreenMap = async function () {
            if (!itineraryFsMap) return;
            const btn = document.getElementById('btn-fs-locate-me');
            if (btn) {
                btn.style.transform = 'scale(0.9)';
                setTimeout(() => { if (btn) btn.style.transform = 'scale(1)'; }, 150);
            }

            let lat = window.myLat || window.currentGPSLat;
            let lng = window.myLng || window.currentGPSLng;

            const applyZoom = (uLat, uLng) => {
                window.myLat = uLat;
                window.myLng = uLng;
                window.currentGPSLat = uLat;
                window.currentGPSLng = uLng;

                if (!window.myFsDraftMarker) {
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
                    window.myFsDraftMarker = L.marker([uLat, uLng], { icon: myIcon, zIndexOffset: 2000 })
                        .addTo(itineraryFsMap)
                        .bindPopup('<div style="font-size:10px; font-weight:800; color:#1e3a8a;">📍 Your Location</div><div style="font-size:8.5px; color:#64748b;">Starting Point</div>', { closeButton: false });
                } else {
                    window.myFsDraftMarker.setLatLng([uLat, uLng]);
                    if (!itineraryFsMap.hasLayer(window.myFsDraftMarker)) {
                        window.myFsDraftMarker.addTo(itineraryFsMap);
                    }
                }
                itineraryFsMap.flyTo([uLat, uLng], 16, { animate: true, duration: 1.2 });
                try { window.myFsDraftMarker.openPopup(); } catch (e) { }
                if (typeof showToast === 'function') showToast("Zoomed to your location 📍");
            };

            if (lat && lng) {
                applyZoom(lat, lng);
            } else if (typeof showToast === 'function') {
                showToast("Acquiring GPS location...");
            }

            if (typeof window.requestPreciseLocation === 'function') {
                try {
                    const loc = await window.requestPreciseLocation(true);
                    if (loc && loc.lat && loc.lng) {
                        applyZoom(loc.lat, loc.lng);
                        return;
                    }
                } catch (e) { }
            }
            if (typeof window.resolveUserLocation === 'function') {
                try {
                    const loc = await window.resolveUserLocation(true);
                    if (loc && loc.lat && loc.lng) {
                        applyZoom(loc.lat, loc.lng);
                        return;
                    }
                } catch (e) { }
            }
            if (!lat || !lng) {
                if (typeof showToast === 'function') showToast("Unable to detect GPS position", "warning");
            }
        };

        window.openItineraryFullMapPreview = function () {
            const modal = document.getElementById('itinerary-fullscreen-map-modal');
            if (!modal) return;
            modal.style.display = 'flex';

            // Hide bottom navigation
            const bNav = document.getElementById('bottom-navigation');
            if (bNav) {
                bNav.classList.add('nav-hidden');
                bNav.style.setProperty('display', 'none', 'important');
            }

            renderItineraryFsCards();

            const mapDiv = document.getElementById('itinerary-fullscreen-map');
            if (!itineraryFsMap && typeof L !== 'undefined' && mapDiv) {
                itineraryFsMap = L.map(mapDiv, {
                    attributionControl: false,
                    zoomControl: false,
                    scrollWheelZoom: true,
                    dragging: true,
                    touchZoom: true,
                    doubleClickZoom: true,
                    minZoom: 11,
                    maxZoom: 19
                }).setView([16.6159, 120.3210], 13);
                window.itineraryFsMap = itineraryFsMap;

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    subdomains: ['a', 'b', 'c'],
                    detectRetina: true
                }).addTo(itineraryFsMap);
            }

            syncFullscreenMapLayers();

            setTimeout(() => {
                if (itineraryFsMap) {
                    itineraryFsMap.invalidateSize();
                    window.fitItineraryFullscreenMapBounds();
                }
            }, 180);
        };

        window.closeItineraryFullscreenMap = function () {
            const modal = document.getElementById('itinerary-fullscreen-map-modal');
            if (modal) modal.style.display = 'none';

            // Restore bottom navigation
            const bNav = document.getElementById('bottom-navigation');
            if (bNav) {
                bNav.classList.remove('nav-hidden');
                bNav.style.removeProperty('display');
            }
        };

        window.fitItineraryFullscreenMapBounds = function () {
            if (!itineraryFsMap) return;

            const routeBounds = L.latLngBounds([]);
            let hasPoints = false;

            // 1. Include all stop locations from draft itinerary
            try {
                const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                draft.forEach(p => {
                    const lat = parseFloat(p.lat || p.latitude);
                    const lng = parseFloat(p.lng || p.longitude);
                    if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                        routeBounds.extend([lat, lng]);
                        hasPoints = true;
                    }
                });
            } catch (e) { }

            // 2. Include all polyline points and markers in fullscreen layers
            if (Array.isArray(itineraryFsLayers)) {
                itineraryFsLayers.forEach(l => {
                    if (l instanceof L.Polyline && !(l instanceof L.Polygon)) {
                        if (typeof l.getBounds === 'function' && l.getBounds().isValid && l.getBounds().isValid()) {
                            routeBounds.extend(l.getBounds());
                            hasPoints = true;
                        }
                    } else if (l instanceof L.Marker && l.getLatLng) {
                        const ll = l.getLatLng();
                        if (ll && !isNaN(ll.lat) && !isNaN(ll.lng) && ll.lat !== 0) {
                            routeBounds.extend(ll);
                            hasPoints = true;
                        }
                    }
                });
            }

            // 3. Include user GPS if present
            if (window.myFsDraftMarker && window.myFsDraftMarker.getLatLng) {
                const uPos = window.myFsDraftMarker.getLatLng();
                if (uPos && !isNaN(uPos.lat) && !isNaN(uPos.lng) && uPos.lat !== 0) {
                    routeBounds.extend(uPos);
                    hasPoints = true;
                }
            }

            if (hasPoints && routeBounds.isValid()) {
                // Fit nicely with vertical padding for header (top) and card dock (bottom)
                itineraryFsMap.fitBounds(routeBounds, {
                    paddingTopLeft: [24, 65],
                    paddingBottomRight: [24, 120],
                    maxZoom: 15,
                    animate: true,
                    duration: 0.8
                });
            } else if (draftMap && draftMap.getCenter) {
                itineraryFsMap.setView(draftMap.getCenter(), 13, { animate: true });
            } else {
                itineraryFsMap.setView([16.6159, 120.3210], 13, { animate: true });
            }
        };

        function syncFullscreenMapLayers() {
            if (!itineraryFsMap) return;
            itineraryFsLayers.forEach(l => {
                try { itineraryFsMap.removeLayer(l); } catch (e) { }
            });
            itineraryFsLayers = [];

            if (draftMap) {
                draftMap.eachLayer(layer => {
                    if (layer instanceof L.TileLayer) return;
                    if (layer._isOfflineBaseLayer) return;
                    if (layer instanceof L.Polygon) return;
                    if (layer instanceof L.GeoJSON) return;

                    if (layer instanceof L.Polyline && !(layer instanceof L.Polygon)) {
                        const latlngs = layer.getLatLngs();
                        const opts = layer.options || {};
                        const poly = L.polyline(latlngs, {
                            color: opts.color || '#00f2fe',
                            weight: (opts.weight || 4.5) + 1,
                            opacity: opts.opacity || 1,
                            dashArray: opts.dashArray || null,
                            lineJoin: 'round',
                            lineCap: 'round'
                        }).addTo(itineraryFsMap);
                        itineraryFsLayers.push(poly);
                    } else if (layer instanceof L.Marker) {
                        const mOpts = layer.options || {};
                        const marker = L.marker(layer.getLatLng(), {
                            icon: mOpts.icon || new L.Icon.Default(),
                            zIndexOffset: mOpts.zIndexOffset || 500
                        }).addTo(itineraryFsMap);
                        if (layer.getPopup && layer.getPopup()) {
                            marker.bindPopup(layer.getPopup().getContent(), { autoPanPadding: [20, 20] });
                        }
                        marker._stopIndex = layer._stopIndex;
                        marker._isUserGps = layer._isUserGps;
                        itineraryFsLayers.push(marker);
                    }
                });
            }
        }

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
                const isMaint = (typeof window.isSpotUnderMaintenance === 'function') ? window.isSpotUnderMaintenance(nextPlace) : Boolean(nextPlace.is_maintenance === 1 || nextPlace.is_maintenance === true || nextPlace.is_maintenance === '1');
                const nextEtaEl = document.getElementById('itinerary-next-eta');
                if (nextEtaEl) {
                    if (isMaint) {
                        nextEtaEl.remove();
                    } else {
                        const pLat = nextPlace.lat || nextPlace.latitude;
                        const pLng = nextPlace.lng || nextPlace.longitude;
                        const placeKey = String(nextPlace.id || nextPlace.name);
                        const hasCachedOSRM = window._cachedNextStopOSRM && window._cachedNextStopOSRM.spotKey === placeKey;
                        const eta = hasCachedOSRM ? window._cachedNextStopOSRM : window.getDistanceAndETA(pLat, pLng);
                        if (eta) {
                            nextEtaEl.innerHTML = `<span style="color:#1e3a8a !important; font-weight:700; font-size:10px; letter-spacing:-0.1px;">${eta.distanceText} away &bull; ~${eta.durationText} drive from your location</span>`;
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
                        .bindPopup('<div style="font-size:9.5px; font-weight:800; color:#1e3a8a; line-height:1.15;">📍 Your Location</div><div style="font-size:8px; color:#64748b; margin-top:1px;">Starting Point</div>', { closeButton: false, autoPanPadding: [10, 10] });
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
            if (window.currentItinStep !== 2) {
                window.switchItineraryStep(2);
            }
            const mapContainer = document.getElementById('draft-map-wrapper');
            if (mapContainer) {
                if (mapContainer.style.display === 'none' || mapContainer.style.display === '') {
                    mapContainer.style.display = 'block';
                    if (typeof draftMap !== 'undefined' && draftMap) draftMap.invalidateSize();
                }
                mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

                const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                const place = draft.find(item => item.id.toString() === id.toString());
                const placeIdx = draft.findIndex(item => item.id.toString() === id.toString());

                if (place && typeof draftMap !== 'undefined' && draftMap) {
                    let lat = place.lat || place.latitude || 16.6159;
                    let lng = place.lng || place.longitude || 120.3167;
                    try {
                        draftMap.flyTo([parseFloat(lat), parseFloat(lng)], 16, { animate: true, duration: 1.5 });
                    } catch (mapFlyErr) {
                        draftMap.setView([parseFloat(lat), parseFloat(lng)], 16);
                    }
                    if (placeIdx !== -1 && window.draftPlaceMarkers && window.draftPlaceMarkers[placeIdx]) {
                        try { window.draftPlaceMarkers[placeIdx].openPopup(); } catch (e) { }
                    }
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
            const startTime = (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now();
            const duration = 650; // ms

            function step(now) {
                const currentTime = now || ((typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now());
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic
                const ease = 1 - Math.pow(1 - progress, 3);
                const pct = startPct + (targetPct - startPct) * ease;
                _currentDonutPct = pct;

                if (donutEl) {
                    const fillPct = Math.min(Math.max(pct, 0), 100);
                    donutEl.style.background = `conic-gradient(
                    ${color} 0% ${fillPct}%,
                    rgba(15, 23, 42, 0.08) ${fillPct}% 100%
                )`;
                    donutEl.style.mask = 'radial-gradient(transparent 58%, black 59%)';
                    donutEl.style.webkitMask = 'radial-gradient(transparent 58%, black 59%)';
                }
                if (pctEl) {
                    if (customLabel !== undefined && customLabel !== null) {
                        pctEl.textContent = customLabel;
                    } else if (Math.round(pct) === 100 || targetPct === 100) {
                        pctEl.textContent = 'MET!';
                    } else if (pct > 999) {
                        pctEl.textContent = '>999%';
                    } else if (targetPct > 0 && targetPct < 1 && pct < 1) {
                        pctEl.textContent = '<1%';
                    } else {
                        pctEl.textContent = Math.round(pct) + '%';
                    }
                    pctEl.style.color = '#0f172a';
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

            const currentGlobalTransport = localStorage.getItem('intan_elyu_draft_trip_transport') || '';

            // Calculate Point-to-Point transit from draft legs using active transport mode
            const transCost = window.computeItineraryTransCost(draft, currentGlobalTransport);

            // Update Transport Mode summary card in the Save modal
            const p2pLabelEl = document.getElementById('save-p2p-transit-label');
            const p2pLegsEl = document.getElementById('save-p2p-legs-count');
            const p2pCostEl = document.getElementById('save-p2p-transit-cost');
            const p2pIconEl = document.getElementById('save-p2p-transit-icon');
            const transInput = document.getElementById('trip-transport');

            const legInfos = draft.map((_, i) => (typeof window.getLegTransportInfo === 'function') ? window.getLegTransportInfo(i) : null).filter(Boolean);

            let tripModes = (typeof window.getNormalizedTripModes === 'function')
                ? window.getNormalizedTripModes()
                : (currentGlobalTransport ? [currentGlobalTransport] : []);

            // Also check if any leg has custom selection if global tripModes is empty
            if (tripModes.length === 0 || tripModes.includes('no_vehicle') || tripModes.every(m => !m || m === 'no_vehicle' || String(m).toLowerCase().includes('no vehicle'))) {
                const legModes = [];
                legInfos.forEach(info => {
                    if (info && info.has_selection && info.transport_modes && Array.isArray(info.transport_modes)) {
                        info.transport_modes.forEach(m => {
                            if (m && m !== 'no_vehicle' && !m.toLowerCase().includes('no vehicle') && !legModes.includes(m)) {
                                legModes.push(m);
                            }
                        });
                    }
                });
                if (legModes.length > 0) {
                    tripModes = legModes;
                }
            }

            const isNoVehTrip = (tripModes.length === 0 || tripModes.includes('no_vehicle') || tripModes.every(m => !m || m === 'no_vehicle' || String(m).toLowerCase().includes('no vehicle')));
            const compositeTripTransport = isNoVehTrip ? 'No Vehicle Selected' : ((tripModes.length === 1) ? tripModes[0] : tripModes.join(' + '));
            if (transInput) transInput.value = compositeTripTransport;

            if (p2pCostEl) {
                p2pCostEl.textContent = '₱' + transCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const isGroupMode = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            const curMales = isGroupMode ? Math.max(0, parseInt(document.getElementById('companion-males-count')?.textContent || '0', 10)) : 0;
            const curFemales = isGroupMode ? Math.max(0, parseInt(document.getElementById('companion-females-count')?.textContent || '0', 10)) : 0;
            const curTotalTravelers = 1 + (isGroupMode ? (curMales + curFemales) : 0);

            if (p2pLabelEl) {
                p2pLabelEl.textContent = 'View Summary';
            }
            if (p2pLegsEl) {
                p2pLegsEl.textContent = '';
                p2pLegsEl.style.display = 'none';
            }

            // Sync icon in Save modal
            if (p2pIconEl) {
                p2pIconEl.style.background = '#ffffff';
                if (isNoVehTrip) {
                    p2pIconEl.innerHTML = '<i class="fa-solid fa-route" style="color:#1e3a8a !important; font-size:16px;"></i>';
                } else if (tripModes.length === 1 && typeof window.getVehicleIconHtml === 'function') {
                    let iconHtml = window.getVehicleIconHtml(tripModes[0]);
                    p2pIconEl.innerHTML = iconHtml.replace(/color:[^;"]+/g, 'color:#1e3a8a !important');
                } else if (tripModes.length > 1) {
                    p2pIconEl.innerHTML = '<i class="fa-solid fa-route" style="color:#1e3a8a !important; font-size:16px;"></i>';
                } else {
                    p2pIconEl.innerHTML = '<i class="fa-solid fa-route" style="color:#1e3a8a !important; font-size:16px;"></i>';
                }
            }

            // Sum Entrance Fees & Environmental Fees across all destinations in draft for all travelers (Companions calculation)
            let feesTotal = 0;
            const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            const compAdults = isGroup ? Math.max(0, parseInt(document.getElementById('companion-adults-count')?.textContent || '0', 10)) : 0;
            const compKids = isGroup ? Math.max(0, parseInt(document.getElementById('companion-kids-count')?.textContent || '0', 10)) : 0;
            const compSeniors = isGroup ? Math.max(0, parseInt(document.getElementById('companion-seniors-count')?.textContent || '0', 10)) : 0;
            const compPwds = isGroup ? Math.max(0, parseInt(document.getElementById('companion-pwds-count')?.textContent || '0', 10)) : 0;
            const compMales = isGroup ? Math.max(0, parseInt(document.getElementById('companion-males-count')?.textContent || '0', 10)) : 0;
            const compFemales = isGroup ? Math.max(0, parseInt(document.getElementById('companion-females-count')?.textContent || '0', 10)) : 0;

            const totalGenderCompanions = compMales + compFemales;
            const totalCategorizedCompanions = compAdults + compKids + compSeniors + compPwds;
            let numAdults = 1 + compAdults;
            let numKids = compKids;
            let numSeniors = compSeniors;
            let numPwds = compPwds;

            // Companions calculation: Fees calculate strictly from categorized companions (Adult, Kids, Senior, PWD) + primary traveler (You).
            // Male and Female counts are demographic counters and do NOT add fee multipliers.
            const totalTravelersForFees = numAdults + numKids + numSeniors + numPwds;

            draft.forEach(p => {
                if (typeof window.isSpotUnderMaintenance === 'function' && window.isSpotUnderMaintenance(p)) {
                    return; // Exclude Under Maintenance
                }
                const adultFee = parseFloat(p.adult_fee || p.adultFee || 0);
                const generalEntrance = parseFloat(p.entrance_fee || p.entranceFee || p.fee || 0);
                const baseAdult = adultFee > 0 ? adultFee : (generalEntrance > 0 ? generalEntrance : 0);

                let kidsFee = 0;
                if (p.kids_fee !== undefined && p.kids_fee !== null && p.kids_fee !== '') {
                    kidsFee = parseFloat(p.kids_fee) || 0;
                } else {
                    kidsFee = baseAdult;
                }

                let seniorFee = 0;
                if (p.senior_citizen_fee !== undefined && p.senior_citizen_fee !== null && p.senior_citizen_fee !== '' && parseFloat(p.senior_citizen_fee) > 0) {
                    seniorFee = parseFloat(p.senior_citizen_fee);
                } else if (baseAdult > 0) {
                    seniorFee = baseAdult * 0.8;
                }

                let pwdFee = 0;
                if (p.pwd_fee !== undefined && p.pwd_fee !== null && p.pwd_fee !== '' && parseFloat(p.pwd_fee) > 0) {
                    pwdFee = parseFloat(p.pwd_fee);
                } else if (baseAdult > 0) {
                    pwdFee = baseAdult * 0.8;
                }

                const env = parseFloat(p.environmental_fee || p.environmentalFee || p.envFee || 0) || 0;
                const envSenior = (seniorFee > 0 && baseAdult > 0) ? Math.round(env * (seniorFee / baseAdult)) : (env > 0 ? Math.round(env * 0.8) : 0);
                const envPwd = (pwdFee > 0 && baseAdult > 0) ? Math.round(env * (pwdFee / baseAdult)) : (env > 0 ? Math.round(env * 0.8) : 0);

                const spotEntranceFees = (numAdults * baseAdult) + (numKids * kidsFee) + (numSeniors * seniorFee) + (numPwds * pwdFee);
                const childEnv = kidsFee > 0 ? env : 0;
                const spotEnvFees = (numAdults * env) + (numKids * childEnv) + (numSeniors * envSenior) + (numPwds * envPwd);

                feesTotal += spotEntranceFees + spotEnvFees;
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

            // Immediately update and render embedded total summary inside Save Trip modal
            if (typeof window.renderSaveModalSummaryContent === 'function') {
                window.renderSaveModalSummaryContent();
            }
            return;

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
                donutWrapper.style.width = '58px';
                donutWrapper.style.marginRight = '0';
                donutWrapper.style.opacity = '1';
                donutWrapper.style.transform = 'scale(1)';
            }

            if (!budgetInput || isNaN(budget) || budget <= 0) {
                // No budget entered yet — show cyan ring outline with dash
                animateDonut(100, '#00f2fe', '—');
                if (remainingRow) remainingRow.style.display = 'none';
                if (pillGreen) { pillGreen.style.opacity = '0.6'; pillGreen.style.transform = 'scale(1)'; pillGreen.style.fontWeight = '700'; }
                if (pillOrange) { pillOrange.style.opacity = '0.6'; pillOrange.style.transform = 'scale(1)'; pillOrange.style.fontWeight = '700'; }
                if (pillRed) { pillRed.style.opacity = '0.6'; pillRed.style.transform = 'scale(1)'; pillRed.style.fontWeight = '700'; }
                return;
            }

            const remaining = budget - estimatedCost;
            const absRemaining = Math.abs(remaining);
            const formattedRemaining = '₱' + absRemaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let fillColor = '#10b981';
            let remainingColor = '#10b981';
            let remainingLabelHtml = '<i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Remaining';
            let remainingValueText = (remaining === 0) ? '₱0.00' : '+' + formattedRemaining;
            let activeTier = 'green';
            let donutPct = 100;

            if (estimatedCost > budget) {
                // Shortfall: Cost exceeds budget
                const shortfall = estimatedCost - budget;
                const formattedShortfall = '₱' + shortfall.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const coveragePct = (estimatedCost > 0) ? Math.max(0, Math.min(99, Math.round((budget / estimatedCost) * 100))) : 0;
                donutPct = coveragePct;

                if (coveragePct >= 80) {
                    // YELLOW: 80% - 99% covered (Nearing estimated cost, close to target!)
                    fillColor = '#f59e0b';
                    remainingColor = '#f59e0b';
                    remainingLabelHtml = '<i class="fa-solid fa-triangle-exclamation" style="margin-right:4px;"></i> Nearing';
                    remainingValueText = '-' + formattedShortfall;
                    activeTier = 'orange';
                } else {
                    // RED: Under 80% covered (Cost exceeds budget significantly!)
                    fillColor = '#ef4444';
                    remainingColor = '#ef4444';
                    remainingLabelHtml = '<i class="fa-solid fa-circle-xmark" style="margin-right:4px;"></i> Exceeded';
                    remainingValueText = '-' + formattedShortfall;
                    activeTier = 'red';
                }
            } else {
                // Budget meets or exceeds estimated cost (100% MET!)
                donutPct = 100;
                fillColor = '#10b981';
                remainingColor = '#10b981';
                remainingLabelHtml = (remaining === 0)
                    ? '<i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> MET!'
                    : '<i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Remaining';
                remainingValueText = (remaining === 0) ? '₱0.00' : '+' + formattedRemaining;
                activeTier = 'green';
            }

            setLegendStatus(activeTier);

            // Animate donut fill smoothly with color-coded status (if 100% make it MET!)
            animateDonut(donutPct, fillColor, (donutPct === 100 ? 'MET!' : undefined));

            // Update embedded summary inside Save Trip modal
            if (typeof window.renderSaveModalSummaryContent === 'function') {
                window.renderSaveModalSummaryContent();
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
                html += `<div style="padding:8px 0; font-size:12px; color:#cbd5e1 !important; pointer-events:none; border-radius:10px;">${prevDay}</div>`;
            }

            // Days of current month
            for (let day = 1; day <= totalDays; day++) {
                const dayStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isSelected = (dayStr === selectedDateStr);
                const isToday = (dayStr === todayStr);
                const isPast = (dayStr < todayStr);

                let bg = 'transparent';
                let color = '#0f172a';
                let border = '1.5px solid transparent';
                let weight = '600';
                let shadow = 'none';

                if (isPast) {
                    html += `
                        <div class="calendar-day-cell is-past" style="padding:8px 0; font-size:13px; font-weight:500; cursor:not-allowed; border-radius:11px; background:transparent; color:#cbd5e1 !important; border:1.5px solid transparent !important; outline:none !important; opacity:0.45; user-select:none; display:flex; align-items:center; justify-content:center;">
                            ${day}
                        </div>
                    `;
                    continue;
                }

                if (isSelected) {
                    bg = 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)';
                    color = '#ffffff';
                    weight = '800';
                    border = '1.5px solid transparent';
                    shadow = '0 2px 6px rgba(2, 132, 199, 0.3)';
                } else if (isToday) {
                    bg = '#e0f2fe';
                    border = '1.5px solid #7dd3fc';
                    color = '#0284c7';
                    weight = '800';
                    shadow = 'none';
                }

                html += `
                    <div class="calendar-day-cell" onclick="window.selectCalendarDate('${dayStr}')" style="padding:8px 0; font-size:13px; font-weight:${weight}; cursor:pointer; border-radius:11px; background:${bg}; color:${color} !important; border:${border} !important; outline:none !important; box-shadow:${shadow} !important; transition:all 0.15s ease; position:relative; display:flex; align-items:center; justify-content:center;">
                        ${day}
                        ${isToday && !isSelected ? '<span style="position:absolute; bottom:2px; width:4px; height:4px; border-radius:50%; background:#0284c7;"></span>' : ''}
                    </div>
                `;
            }

            // Trailing days to fill standard calendar rows
            const totalRendered = firstDayIndex + totalDays;
            const remainingCells = (totalRendered <= 35 ? 35 : 42) - totalRendered;
            for (let j = 1; j <= remainingCells; j++) {
                html += `<div style="padding:8px 0; font-size:12px; color:#cbd5e1 !important; pointer-events:none; border-radius:10px;">${j}</div>`;
            }

            gridEl.innerHTML = html;
        };

        window.changeCalendarMonth = function (delta, e) {
            if (e) e.stopPropagation();
            const today = new Date();
            const minYear = today.getFullYear();
            const minMonth = today.getMonth();

            const nextMonth = window.calendarState.currentMonth + delta;
            let targetYear = window.calendarState.currentYear;
            let targetMonth = nextMonth;
            if (nextMonth > 11) {
                targetMonth = 0;
                targetYear += 1;
            } else if (nextMonth < 0) {
                targetMonth = 11;
                targetYear -= 1;
            }

            // Reject navigating to months prior to current month
            if (targetYear < minYear || (targetYear === minYear && targetMonth < minMonth)) {
                return;
            }

            window.calendarState.currentMonth = targetMonth;
            window.calendarState.currentYear = targetYear;
            window.renderCalendarGrid();
        };

        window.selectCalendarDate = function (dateStr) {
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
            if (dateStr && dateStr < todayStr) {
                if (typeof window.showToast === 'function') {
                    window.showToast("Cannot select past dates for a trip plan", "warning");
                }
                return;
            }

            window.calendarState.selectedDateStr = dateStr;
            const hiddenInput = document.getElementById('trip-date');
            const displayEl = document.getElementById('custom-date-display');
            const clearLink = document.getElementById('calendar-clear-link');

            if (hiddenInput) hiddenInput.value = dateStr;

            if (displayEl && dateStr) {
                const parts = dateStr.split('-');
                const y = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const d = parseInt(parts[2], 10);
                const dateObj = new Date(y, m - 1, d);
                const formatted = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                displayEl.textContent = formatted;
                displayEl.style.color = '#0f172a';
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
                displayEl.style.color = '#64748b';
                displayEl.style.fontWeight = '600';
            }
            if (clearLink) clearLink.style.display = 'none';
            window.renderCalendarGrid();
        };

        window.selectTodayDate = function (e) {
            if (e) e.stopPropagation();
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
                dropdown.classList.add('calendar-open');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
                if (trigger) {
                    trigger.style.borderColor = '#0284c7';
                    trigger.style.boxShadow = '0 0 0 3px rgba(2, 132, 199, 0.15)';
                }
                setTimeout(() => {
                    dropdown.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 50);
            } else {
                dropdown.classList.remove('calendar-open');
                dropdown.style.display = 'none';
                if (arrow) arrow.style.transform = 'rotate(0deg)';
                if (trigger) {
                    trigger.style.borderColor = '#cbd5e1';
                    trigger.style.boxShadow = 'none';
                }
            }
        };

        // Close calendar dropdown when tapping outside
        document.addEventListener('click', function (e) {
            const dropdown = document.getElementById('custom-calendar-dropdown');
            const trigger = document.getElementById('custom-date-trigger');
            if (!dropdown || !dropdown.classList.contains('calendar-open')) return;
            if (trigger && (trigger === e.target || trigger.contains(e.target))) return;
            if (dropdown.contains(e.target)) return;
            window.toggleCustomCalendar(null, false);
        });

        window.setTripType = function (type) {
            const isGroup = type === 'group';
            const radioSolo = document.getElementById('radio-trip-solo');
            const radioGroup = document.getElementById('radio-trip-group');
            const optSolo = document.getElementById('opt-trip-solo');
            const optGroup = document.getElementById('opt-trip-group');
            const box = document.getElementById('companion-stepper-box');

            if (radioSolo) radioSolo.checked = !isGroup;
            if (radioGroup) radioGroup.checked = isGroup;

            if (optSolo) {
                optSolo.style.background = isGroup ? '#f8fafc' : 'rgba(2, 132, 199, 0.08)';
                optSolo.style.border = isGroup ? '1.5px solid #cbd5e1' : '1.5px solid #0284c7';
                optSolo.style.color = isGroup ? '#475569' : '#0284c7';
            }
            if (optGroup) {
                optGroup.style.background = isGroup ? 'rgba(2, 132, 199, 0.08)' : '#f8fafc';
                optGroup.style.border = isGroup ? '1.5px solid #0284c7' : '1.5px solid #cbd5e1';
                optGroup.style.color = isGroup ? '#0284c7' : '#475569';
            }
            if (box) {
                box.style.display = isGroup ? 'block' : 'none';
            }
            if (isGroup && typeof window.syncCompanionListFromCounters === 'function') {
                window.syncCompanionListFromCounters();
            }
            window.updateCompanionHeadcountSummary();
            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.updateItineraryMainBudgetDisplay === 'function') {
                window.updateItineraryMainBudgetDisplay();
            }
            if (typeof window.renderItinerary === 'function') {
                window.renderItinerary(true);
            }
        };

        window.companionList = [];

        window.syncCompanionListFromCounters = function () {
            const males = Math.max(0, parseInt(document.getElementById('companion-males-count')?.textContent || '0', 10));
            const females = Math.max(0, parseInt(document.getElementById('companion-females-count')?.textContent || '0', 10));

            const currentMales = (window.companionList || []).filter(c => c.gender === 'male');
            const currentFemales = (window.companionList || []).filter(c => c.gender === 'female');

            while (currentMales.length < males) {
                currentMales.push({ id: 'm_' + Date.now() + '_' + Math.random(), gender: 'male', age: 25, isPwd: false });
            }
            while (currentMales.length > males) {
                currentMales.pop();
            }

            while (currentFemales.length < females) {
                currentFemales.push({ id: 'f_' + Date.now() + '_' + Math.random(), gender: 'female', age: 25, isPwd: false });
            }
            while (currentFemales.length > females) {
                currentFemales.pop();
            }

            window.companionList = [...currentMales, ...currentFemales];
            window.renderCompanionAgeRows();
        };

        window.renderCompanionAgeRows = function () {
            const wrapper = document.getElementById('companion-ages-section');
            const listEl = document.getElementById('companion-age-list');
            if (!wrapper || !listEl) return;

            if (!window.companionList || window.companionList.length === 0) {
                wrapper.style.display = 'none';
                listEl.innerHTML = '';
                window.recalcConcessionsFromAges();
                return;
            }

            wrapper.style.display = 'block';

            let html = '';
            window.companionList.forEach((comp, idx) => {
                const isMale = comp.gender === 'male';
                const genderLabel = isMale ? 'Male' : 'Female';
                const genderColor = isMale ? '#2563eb' : '#db2777';
                const genderIcon = isMale ? 'fa-mars' : 'fa-venus';
                const ageVal = (comp.age !== undefined && comp.age !== null) ? comp.age : '';

                let badgeText = 'Adult';
                let badgeBg = '#f1f5f9';
                let badgeColor = '#334155';

                if (ageVal !== '' && Number(ageVal) < 3) {
                    badgeText = '3+ only';
                    badgeBg = '#fee2e2';
                    badgeColor = '#dc2626';
                } else if (ageVal !== '' && Number(ageVal) < 18) {
                    badgeText = 'Kid';
                    badgeBg = '#e0f2fe';
                    badgeColor = '#0284c7';
                } else if (ageVal !== '' && Number(ageVal) >= 60) {
                    badgeText = 'Senior';
                    badgeBg = '#fef3c7';
                    badgeColor = '#b45309';
                }

                html += `
                    <div class="companion-age-row" style="display:flex; align-items:center; justify-content:space-between; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:8px 10px; gap:6px; box-sizing:border-box;">
                        <div style="display:flex; align-items:center; gap:5px; flex-shrink:0;">
                            <span style="font-size:11.5px; color:#334155; font-weight:700;">${genderLabel}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                            <div style="display:flex; align-items:center; gap:3px;">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" autocomplete="off"
                                    value="${ageVal}" placeholder="Age" min="3" max="99"
                                    class="companion-age-input"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length > 2) this.value = this.value.slice(0, 2); window.updateCompanionAge(${idx}, this.value)"
                                    onblur="window.validateCompanionAgeBlur(${idx}, this)"
                                    onfocus="this.select(); setTimeout(() => { try { this.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch(e){} }, 180);"
                                    style="width:48px; height:32px; border:1.5px solid #cbd5e1; border-radius:8px; text-align:center; font-size:13px; font-weight:800; color:#0f172a; padding:0 3px; outline:none; background:#ffffff; box-sizing:border-box; transition:border-color 0.2s;" />
                                <span style="font-size:10px; color:#64748b; font-weight:700;">y.o.</span>
                            </div>
                            <span id="comp-badge-${idx}" style="font-size:10.5px; font-weight:800; padding:3px 7px; border-radius:7px; background:${badgeBg}; color:${badgeColor}; min-width:38px; text-align:center;">
                                ${badgeText}
                            </span>
                            <label style="display:flex; align-items:center; gap:3px; font-size:11px; font-weight:700; color:#475569; cursor:pointer; user-select:none; margin:0;">
                                <input type="checkbox" ${comp.isPwd ? 'checked' : ''} onchange="window.toggleCompanionPwd(${idx}, this.checked)" style="accent-color:#7c3aed; width:15px; height:15px; cursor:pointer;" /> PWD
                            </label>
                        </div>
                    </div>
                `;
            });

            listEl.innerHTML = html;
            window.recalcConcessionsFromAges();
        };

        window.validateCompanionAgeBlur = function (idx, inputEl) {
            if (!window.companionList || !window.companionList[idx]) return;
            const val = inputEl.value.trim().replace(/[^0-9]/g, '');
            if (val !== '') {
                const num = parseInt(val, 10);
                if (num < 3) {
                    inputEl.value = '3';
                    window.updateCompanionAge(idx, '3');
                    if (typeof showToast === 'function') {
                        showToast('Ages 0 to 2 are not included. Minimum companion age is 3.');
                    }
                } else if (num > 99) {
                    inputEl.value = '99';
                    window.updateCompanionAge(idx, '99');
                    if (typeof showToast === 'function') {
                        showToast('Maximum companion age is 99.');
                    }
                }
            }
        };

        window.updateCompanionAge = function (idx, val) {
            if (!window.companionList || !window.companionList[idx]) return;
            const cleanDigits = String(val || '').replace(/[^0-9]/g, '').slice(0, 2);
            const cleanVal = cleanDigits === '' ? '' : Math.min(99, parseInt(cleanDigits, 10) || 0);
            window.companionList[idx].age = cleanVal;

            const comp = window.companionList[idx];
            const badgeEl = document.getElementById(`comp-badge-${idx}`);
            if (badgeEl) {
                if (cleanVal !== '' && cleanVal < 3) {
                    badgeEl.textContent = '3+ only';
                    badgeEl.style.background = '#fee2e2';
                    badgeEl.style.color = '#dc2626';
                } else if (cleanVal !== '' && cleanVal < 18) {
                    badgeEl.textContent = 'Kid';
                    badgeEl.style.background = '#e0f2fe';
                    badgeEl.style.color = '#0284c7';
                } else if (cleanVal !== '' && cleanVal >= 60) {
                    badgeEl.textContent = 'Senior';
                    badgeEl.style.background = '#fef3c7';
                    badgeEl.style.color = '#b45309';
                } else {
                    badgeEl.textContent = 'Adult';
                    badgeEl.style.background = '#f1f5f9';
                    badgeEl.style.color = '#334155';
                }
            }

            window.recalcConcessionsFromAges();
        };

        window.toggleCompanionPwd = function (idx, isChecked) {
            if (!window.companionList || !window.companionList[idx]) return;
            window.companionList[idx].isPwd = Boolean(isChecked);
            window.recalcConcessionsFromAges();
        };

        window.recalcConcessionsFromAges = function () {
            let adults = 0;
            let kids = 0;
            let seniors = 0;
            let pwds = 0;

            (window.companionList || []).forEach(comp => {
                if (comp.isPwd) {
                    pwds++;
                } else if (comp.age !== '' && comp.age !== null && Number(comp.age) >= 3 && Number(comp.age) < 18) {
                    kids++;
                } else if (comp.age !== '' && comp.age !== null && Number(comp.age) >= 60) {
                    seniors++;
                } else {
                    adults++;
                }
            });

            const aEl = document.getElementById('companion-adults-count');
            const kEl = document.getElementById('companion-kids-count');
            const sEl = document.getElementById('companion-seniors-count');
            const pEl = document.getElementById('companion-pwds-count');
            if (aEl) aEl.textContent = adults;
            if (kEl) kEl.textContent = kids;
            if (sEl) sEl.textContent = seniors;
            if (pEl) pEl.textContent = pwds;

            const summaryEl = document.getElementById('companion-detected-summary');
            if (summaryEl) {
                const parts = [];
                if (adults > 0) parts.push(`${adults} Adult${adults > 1 ? 's' : ''}`);
                if (kids > 0) parts.push(`${kids} Kid${kids > 1 ? 's' : ''}`);
                if (seniors > 0) parts.push(`${seniors} Senior${seniors > 1 ? 's' : ''}`);
                if (pwds > 0) parts.push(`${pwds} PWD${pwds > 1 ? 's' : ''}`);
                summaryEl.textContent = parts.length > 0 ? parts.join(', ') : '0 travelers';
            }

            window.updateCompanionHeadcountSummary();
            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.updateItineraryMainBudgetDisplay === 'function') {
                window.updateItineraryMainBudgetDisplay();
            }
            if (typeof window.renderItinerary === 'function') {
                window.renderItinerary(true);
            }
        };

        window.adjustCompanionCount = function (type, delta) {
            const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            if (!isGroup) return;

            const countEl = document.getElementById(`companion-${type}-count`);
            if (!countEl) return;
            let current = parseInt(countEl.textContent || '0', 10);
            if (isNaN(current)) current = 0;
            current = Math.max(0, current + delta);
            countEl.textContent = current;

            // Guardrail check: if (currentFeeCompanions >= totalCompanionsLimit) { showToast('Limit reached: Maximum of companions allocated'); }
            window.syncCompanionListFromCounters();
        };

        window.updateCompanionHeadcountSummary = function () {
            const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            const males = isGroup ? Math.max(0, parseInt(document.getElementById('companion-males-count')?.textContent || '0', 10)) : 0;
            const females = isGroup ? Math.max(0, parseInt(document.getElementById('companion-females-count')?.textContent || '0', 10)) : 0;
            const adults = isGroup ? Math.max(0, parseInt(document.getElementById('companion-adults-count')?.textContent || '0', 10)) : 0;
            const kids = isGroup ? Math.max(0, parseInt(document.getElementById('companion-kids-count')?.textContent || '0', 10)) : 0;
            const seniors = isGroup ? Math.max(0, parseInt(document.getElementById('companion-seniors-count')?.textContent || '0', 10)) : 0;
            const pwds = isGroup ? Math.max(0, parseInt(document.getElementById('companion-pwds-count')?.textContent || '0', 10)) : 0;

            const totalGenderCompanions = males + females;
            const totalTypeCompanions = adults + kids + seniors + pwds;
            const totalPax = isGroup ? (1 + totalGenderCompanions) : 1;

            const badge = document.getElementById('trip-pax-count-badge');
            if (badge) {
                badge.textContent = `${totalPax} Traveler${totalPax > 1 ? 's' : ''}${isGroup ? '' : ' (Solo)'}`;
            }
            const summaryText = document.getElementById('companion-summary-text');
            if (summaryText) {
                if (!isGroup || totalGenderCompanions === 0) {
                    summaryText.textContent = isGroup ? '1 Traveler (Group)' : '1 Traveler (Solo)';
                } else {
                    let details = ['You'];
                    if (males > 0) details.push(`${males}M`);
                    if (females > 0) details.push(`${females}F`);
                    summaryText.textContent = `${totalPax} Travelers (${details.join(' + ')})`;
                }
            }

            const pageReviewParty = document.getElementById('page-review-party');
            if (pageReviewParty && summaryText) {
                pageReviewParty.textContent = summaryText.textContent;
            }

            // Update limit pill / indicator
            const limitInfoEl = document.getElementById('companion-fee-limit-info');
            if (limitInfoEl) {
                if (!isGroup || totalGenderCompanions === 0) {
                    limitInfoEl.textContent = '(0 of 0)';
                    limitInfoEl.style.color = '#94a3b8';
                    limitInfoEl.style.background = '#f1f5f9';
                } else if (totalTypeCompanions >= totalGenderCompanions) {
                    limitInfoEl.textContent = `(${totalTypeCompanions} of ${totalGenderCompanions} full)`;
                    limitInfoEl.style.color = '#166534';
                    limitInfoEl.style.background = '#dcfce7';
                } else {
                    limitInfoEl.textContent = `(${totalTypeCompanions} of ${totalGenderCompanions})`;
                    limitInfoEl.style.color = '#0284c7';
                    limitInfoEl.style.background = '#e0f2fe';
                }
            }

            // Sync visual button states
            const allTypes = [
                { type: 'males', count: males },
                { type: 'females', count: females },
                { type: 'adults', count: adults },
                { type: 'kids', count: kids },
                { type: 'seniors', count: seniors },
                { type: 'pwds', count: pwds }
            ];

            const feeLimitReached = (totalGenderCompanions === 0) || (totalTypeCompanions >= totalGenderCompanions);

            allTypes.forEach(item => {
                const decBtn = document.getElementById(`btn-dec-${item.type}`);
                const incBtn = document.getElementById(`btn-inc-${item.type}`);
                if (decBtn) {
                    decBtn.style.opacity = (item.count <= 0) ? '0.35' : '1';
                    decBtn.style.cursor = (item.count <= 0) ? 'not-allowed' : 'pointer';
                }
                if (incBtn) {
                    if (['adults', 'kids', 'seniors', 'pwds'].includes(item.type)) {
                        incBtn.style.opacity = feeLimitReached ? '0.35' : '1';
                        incBtn.style.cursor = feeLimitReached ? 'not-allowed' : 'pointer';
                    }
                }
            });
        };

        window.resetSaveModalInputs = function () {
            const titleInput = document.getElementById('trip-title');
            if (titleInput) titleInput.value = '';
            window.customClearDate();
            window.toggleCustomCalendar(null, false);
            const transInput = document.getElementById('trip-transport');
            if (transInput) transInput.value = 'no_vehicle';
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

            if (typeof window.setTripType === 'function') window.setTripType('solo');
            const compM = document.getElementById('companion-males-count');
            const compF = document.getElementById('companion-females-count');
            const compA = document.getElementById('companion-adults-count');
            const compK = document.getElementById('companion-kids-count');
            const compS = document.getElementById('companion-seniors-count');
            const compP = document.getElementById('companion-pwds-count');
            if (compM) compM.textContent = '0';
            if (compF) compF.textContent = '0';
            if (compA) compA.textContent = '0';
            if (compK) compK.textContent = '0';
            if (compS) compS.textContent = '0';
            if (compP) compP.textContent = '0';
            if (typeof window.updateCompanionHeadcountSummary === 'function') window.updateCompanionHeadcountSummary();

            sessionStorage.removeItem('editing_itinerary_id');
            sessionStorage.removeItem('editing_trip_title');
            sessionStorage.removeItem('editing_trip_date');
            sessionStorage.removeItem('editing_trip_budget');
            sessionStorage.removeItem('editing_trip_transport');
            sessionStorage.removeItem('editing_trip_type');
            sessionStorage.removeItem('editing_companion_males');
            sessionStorage.removeItem('editing_companion_females');
            sessionStorage.removeItem('editing_companion_adults');
            sessionStorage.removeItem('editing_companion_kids');
            sessionStorage.removeItem('editing_companion_seniors');
            sessionStorage.removeItem('editing_companion_pwds');
            sessionStorage.removeItem('editing_total_pax');
            sessionStorage.removeItem('editing_transport_initialized');
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
            sessionStorage.removeItem('editing_transport_initialized');

            if (typeof showToast === 'function') {
                showToast(restored ? "Restored your draft itinerary." : "Exited trip edit mode.");
            }

            window.renderItinerary();
        };

        
        // ----------------------------------------------------
        // STEP 2: SUGGESTED SPOTS SLIDER & QUICK-ADD
        // ----------------------------------------------------
        window.step2SliderActiveCat = 'all';

        window.findSpotById = function (spotId) {
            if (!spotId) return null;
            const strId = String(spotId);
            if (Array.isArray(window.ALL_TOURIST_SPOTS)) {
                const found = window.ALL_TOURIST_SPOTS.find(s => String(s.id) === strId);
                if (found) return found;
            }
            try {
                const rawMap = localStorage.getItem('public_map_data');
                if (rawMap) {
                    const parsed = JSON.parse(rawMap);
                    const spots = parsed?.data?.destinations || parsed?.destinations || (Array.isArray(parsed) ? parsed : []);
                    const found = spots.find(s => String(s.id) === strId);
                    if (found) {
                        return {
                            id: found.id,
                            name: found.name,
                            category: found.category || 'Attractions',
                            municipality: found.municipality || 'La Union',
                            location: found.location || found.address || (found.municipality ? found.municipality + ', La Union' : 'La Union'),
                            photo_url: found.photo_url || found.image || '',
                            latitude: parseFloat(found.latitude || found.lat || 0),
                            longitude: parseFloat(found.longitude || found.lng || 0),
                            price: parseFloat(found.entrance_fee || found.price || 0),
                            environmental_fee: parseFloat(found.environmental_fee || 0),
                            rating: (found.rating && parseFloat(found.rating) > 0) ? parseFloat(found.rating) : (found.reviews_avg_rating ? parseFloat(found.reviews_avg_rating) : 0),
                            description: found.description || '',
                            opening_time: found.opening_time || '',
                            closing_time: found.closing_time || '',
                            is_open_24_hours: Boolean(found.is_open_24_hours)
                        };
                    }
                }
            } catch (e) { }
            return null;
        };

        window.filterStep2Slider = function (category, btnEl) {
            window.step2SliderActiveCat = category || 'all';
            document.querySelectorAll('#itin-slider-categories .itin-cat-pill').forEach(pill => {
                pill.classList.remove('active');
            });
            if (btnEl) btnEl.classList.add('active');
            window.renderStep2SpotsSlider(category);
        };

        window.syncStep2SliderButtons = function () {
            let draft = [];
            try {
                draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary')) || [];
            } catch (e) { }
            const draftIds = new Set(draft.map(s => String(s.id)));

            document.querySelectorAll('.btn-slider-add-spot').forEach(btn => {
                const spotId = btn.getAttribute('data-spot-id');
                const isAdded = draftIds.has(String(spotId));
                if (isAdded) {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
                    btn.style.background = '#ecfdf5';
                    btn.style.color = '#059669';
                    btn.style.borderColor = '#a7f3d0';
                    btn.setAttribute('aria-pressed', 'true');
                } else {
                    btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add';
                    btn.style.background = '#e0f2fe';
                    btn.style.color = '#0284c7';
                    btn.style.borderColor = '#bae6fd';
                    btn.setAttribute('aria-pressed', 'false');
                }
            });

            const modalBtn = document.getElementById('spot-detail-cta-btn');
            if (modalBtn) {
                const modalSpotId = modalBtn.getAttribute('data-spot-id');
                if (modalSpotId && typeof window.syncSpotDetailModalBtn === 'function') {
                    window.syncSpotDetailModalBtn(draftIds.has(String(modalSpotId)));
                }
            }
        };

        window.toggleStep2SliderSpot = function (spotId, btnEl) {
            let draft = [];
            try {
                draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary')) || [];
            } catch (e) { }

            const strId = String(spotId);
            const existingIdx = draft.findIndex(item => String(item.id) === strId);

            if (existingIdx >= 0) {
                // Remove from draft
                draft.splice(existingIdx, 1);
                localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
                try {
                    let overrides = JSON.parse(localStorage.getItem('intan_elyu_draft_leg_vehicles') || '[]');
                    if (Array.isArray(overrides) && overrides.length > existingIdx) {
                        overrides.splice(existingIdx, 1);
                        localStorage.setItem('intan_elyu_draft_leg_vehicles', JSON.stringify(overrides));
                    }
                } catch (e) { }

                if (typeof showToast === 'function') {
                    showToast("Removed from stops");
                }
            } else {
                // Add to draft
                const spot = window.findSpotById(spotId);
                if (!spot) {
                    if (typeof showToast === 'function') showToast("Spot information not found");
                    return;
                }
                const newPlace = {
                    id: spot.id,
                    name: spot.name,
                    category: spot.category || 'Attractions',
                    municipality: spot.municipality || 'La Union',
                    location: spot.location || (spot.municipality ? spot.municipality + ', La Union' : 'La Union'),
                    photo_url: spot.photo_url || '',
                    latitude: spot.latitude,
                    longitude: spot.longitude,
                    price: spot.price || 0,
                    avg_transport_cost: 0,
                    avg_duration_minutes: 60
                };
                draft.push(newPlace);
                localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));

                if (typeof showToast === 'function') {
                    showToast(`Added ${spot.name} to stops!`);
                }
            }

            if (typeof window.renderItinerary === 'function') {
                window.renderItinerary();
            }
            window.syncStep2SliderButtons();
        };

        window.renderStep2SpotsSlider = function (category) {
            const track = document.getElementById('itin-spots-slider-track');
            if (!track) return;

            const activeCat = category || window.step2SliderActiveCat || 'all';

            // Gather candidate spots pool
            let spotsPool = [];
            if (Array.isArray(window.ALL_TOURIST_SPOTS) && window.ALL_TOURIST_SPOTS.length > 0) {
                spotsPool = [...window.ALL_TOURIST_SPOTS];
            } else {
                try {
                    const rawMap = localStorage.getItem('public_map_data');
                    if (rawMap) {
                        const parsed = JSON.parse(rawMap);
                        const list = parsed?.data?.destinations || parsed?.destinations || (Array.isArray(parsed) ? parsed : []);
                        spotsPool = list.map(s => ({
                            id: s.id,
                            name: s.name,
                            category: s.category || 'Attractions',
                            municipality: s.municipality || 'La Union',
                            location: s.location || s.address || (s.municipality ? s.municipality + ', La Union' : 'La Union'),
                            photo_url: s.photo_url || s.image || '',
                            latitude: parseFloat(s.latitude || s.lat || 0),
                            longitude: parseFloat(s.longitude || s.lng || 0),
                            price: parseFloat(s.entrance_fee || s.price || 0),
                            rating: (s.rating && parseFloat(s.rating) > 0) ? parseFloat(s.rating) : (s.reviews_avg_rating ? parseFloat(s.reviews_avg_rating) : 0)
                        }));
                    }
                } catch (e) { }
            }

            if (!spotsPool || spotsPool.length === 0) {
                track.innerHTML = '<div style="font-size:11.5px; color:#64748b; padding:12px;">Loading suggested spots...</div>';
                // Asynchronously fetch public map if cache wasn't ready
                if (typeof getAppBackendUrl === 'function') {
                    fetch(`${getAppBackendUrl()}/api/public/map?_t=${Date.now()}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && Array.isArray(data.destinations)) {
                                window.ALL_TOURIST_SPOTS = data.destinations;
                                window.renderStep2SpotsSlider(activeCat);
                            }
                        })
                        .catch(() => {});
                }
                return;
            }

            // Get base municipality selected in Step 1
            const baseMuniSelect = document.getElementById('trip-municipality');
            const baseMuniRaw = (baseMuniSelect ? baseMuniSelect.value : '').toLowerCase().trim();
            const hasBaseMuni = baseMuniRaw && !baseMuniRaw.includes('all');

            // Category filter check
            const catMatches = (spot, cat) => {
                if (cat === 'all') return true;
                const spotCat = String(spot.category || '').toLowerCase();
                const name = String(spot.name || '').toLowerCase();
                if (cat === 'beach') return /beach|surf|coast|sea|island/.test(spotCat) || /beach|surf|point/.test(name);
                if (cat === 'nature') return /nature|waterfall|fall|mountain|peak|river|forest|cave|eco|park/.test(spotCat) || /falls|tangadan|lake/.test(name);
                if (cat === 'culture') return /culture|heritage|church|historical|shrine|museum|monument|religious/.test(spotCat) || /church|basilica|shrine|machu|watchtower/.test(name);
                if (cat === 'food') return /food|dining|restaurant|cafe|bistro|bar|coffee/.test(spotCat) || /cafe|dining|restaurant|grill|bistro/.test(name);
                return true;
            };

            let filtered = spotsPool.filter(s => catMatches(s, activeCat));

            // Sort: Prioritize matching base municipality hub
            filtered.sort((a, b) => {
                const aMuni = String(a.municipality || '').toLowerCase();
                const bMuni = String(b.municipality || '').toLowerCase();
                const aIsBase = hasBaseMuni && aMuni.includes(baseMuniRaw);
                const bIsBase = hasBaseMuni && bMuni.includes(baseMuniRaw);

                if (aIsBase && !bIsBase) return -1;
                if (!aIsBase && bIsBase) return 1;

                // Secondary sort: rating desc
                return (b.rating || 0) - (a.rating || 0);
            });

            // Limit to top 24 curated recommendations for swift mobile performance
            filtered = filtered.slice(0, 24);

            let draft = [];
            try {
                draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary')) || [];
            } catch (e) { }
            const draftIds = new Set(draft.map(s => String(s.id)));

            let html = '';
            filtered.forEach(spot => {
                const isAdded = draftIds.has(String(spot.id));
                const thumbUrl = (typeof window.getSpotR2Thumbnail === 'function')
                    ? window.getSpotR2Thumbnail(spot)
                    : (spot.photo_url || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/spot_6a686f4d0f48b.jpg');
                const ratingVal = (spot.rating && spot.rating > 0) ? Number(spot.rating).toFixed(1) : ((spot.reviews_avg_rating && spot.reviews_avg_rating > 0) ? Number(spot.reviews_avg_rating).toFixed(1) : null);
                const muniName = spot.municipality || 'La Union';
                const isBaseMatch = hasBaseMuni && String(muniName).toLowerCase().includes(baseMuniRaw);

                const btnText = isAdded ? '<i class="fa-solid fa-check"></i> Added' : '<i class="fa-solid fa-plus"></i> Add';
                const btnBg = isAdded ? '#ecfdf5' : '#e0f2fe';
                const btnColor = isAdded ? '#059669' : '#0284c7';
                const btnBorder = isAdded ? '#a7f3d0' : '#bae6fd';

                html += `
                    <div class="itin-spot-slider-card" onclick="window.openSpotDetailsModal('${spot.id}')" style="cursor:pointer;" title="Tap to view attraction details">
                        <div style="position:relative; width:100%; height:88px; background:#e2e8f0; overflow:hidden;">
                            <img src="${thumbUrl}" alt="${spot.name}" loading="lazy"
                                onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/spot_6a686f4d0f48b.jpg';"
                                style="width:100%; height:100%; object-fit:cover; display:block;">
                            ${ratingVal ? `
                            <div style="position:absolute; top:5px; right:5px; background:rgba(15,23,42,0.78); backdrop-filter:blur(4px); border-radius:6px; padding:2px 5px; color:#facc15; font-size:10px; font-weight:800; display:flex; align-items:center; gap:2px;">
                                <i class="fa-solid fa-star" style="font-size:9px;"></i> ${ratingVal}
                            </div>
                            ` : ''}
                            ${isBaseMatch ? `
                                <div style="position:absolute; bottom:5px; left:5px; background:#0284c7; color:#ffffff; font-size:9px; font-weight:800; padding:2px 6px; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,0.2);">
                                    Base Hub
                                </div>
                            ` : ''}
                        </div>
                        <div style="padding:8px 9px 9px 9px; display:flex; flex-direction:column; flex:1; justify-content:space-between;">
                            <div>
                                <div style="font-size:12px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${spot.name}">
                                    ${spot.name}
                                </div>
                                <div style="font-size:10.5px; color:#64748b; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:flex; align-items:center; gap:3px;">
                                    <i class="fa-solid fa-location-dot" style="font-size:9px; color:#0284c7;"></i>
                                    <span>${muniName}</span>
                                </div>
                            </div>
                            <button type="button" class="btn-slider-add-spot" data-spot-id="${spot.id}"
                                onclick="event.stopPropagation(); window.toggleStep2SliderSpot('${spot.id}', this)"
                                style="background:${btnBg}; color:${btnColor}; border-color:${btnBorder};">
                                ${btnText}
                            </button>
                        </div>
                    </div>
                `;
            });

            track.innerHTML = html;
        };

        // ----------------------------------------------------
        // SPOT QUICK DETAILS MODAL CONTROLLER
        // ----------------------------------------------------
        window.openSpotDetailsModal = function (spotId) {
            const spot = window.findSpotById(spotId);
            if (!spot) return;

            const modal = document.getElementById('itin-spot-details-modal');
            if (!modal) return;

            let draft = [];
            try {
                draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary')) || [];
            } catch (e) { }
            const isAdded = draft.some(s => String(s.id) === String(spot.id));

            const imgEl = document.getElementById('spot-detail-img');
            const titleEl = document.getElementById('spot-detail-title');
            const locEl = document.getElementById('spot-detail-location');
            const catEl = document.getElementById('spot-detail-category');
            const ratingEl = document.getElementById('spot-detail-rating');
            const descEl = document.getElementById('spot-detail-desc');
            const ctaBtn = document.getElementById('spot-detail-cta-btn');

            const thumbUrl = (typeof window.getSpotR2Thumbnail === 'function')
                ? window.getSpotR2Thumbnail(spot)
                : (spot.photo_url || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/spot_6a686f4d0f48b.jpg');

            if (imgEl) imgEl.src = thumbUrl;
            if (titleEl) titleEl.textContent = spot.name || 'Attraction';
            if (locEl) locEl.textContent = spot.location || (spot.municipality ? spot.municipality + ', La Union' : 'La Union');
            if (catEl) catEl.textContent = spot.category || 'Attractions';
            const ratingBadgeEl = document.getElementById('spot-detail-rating-badge');
            const rawRating = (spot.rating && Number(spot.rating) > 0) ? Number(spot.rating) : ((spot.reviews_avg_rating && Number(spot.reviews_avg_rating) > 0) ? Number(spot.reviews_avg_rating) : 0);
            if (rawRating > 0) {
                if (ratingEl) ratingEl.textContent = rawRating.toFixed(1);
                if (ratingBadgeEl) ratingBadgeEl.style.display = 'inline-flex';
            } else {
                if (ratingBadgeEl) ratingBadgeEl.style.display = 'none';
            }
            if (descEl) {
                descEl.textContent = spot.description && spot.description.trim().length > 0
                    ? spot.description
                    : 'Explore this premier tourist attraction in the heart of La Union. Add it to your itinerary stops to enjoy route-optimized transit directions and cost estimates.';
            }

            if (ctaBtn) {
                ctaBtn.setAttribute('data-spot-id', spot.id);
                window.syncSpotDetailModalBtn(isAdded);
            }

            // Move modal to body so backdrop and blur cover entire viewport including bottom nav
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.body.classList.add('spot-detail-modal-open', 'modal-blurred');
            document.documentElement.classList.add('spot-detail-modal-open', 'modal-blurred');
            const bNav = document.getElementById('magic-nav') || document.getElementById('bottom-navigation') || document.querySelector('.magic-nav');
            if (bNav) {
                bNav.style.filter = 'blur(14px) brightness(0.65)';
                bNav.style.webkitFilter = 'blur(14px) brightness(0.65)';
                bNav.style.opacity = '0.28';
                bNav.style.pointerEvents = 'none';
            }
        };

        window.syncSpotDetailModalBtn = function (isAdded) {
            const ctaBtn = document.getElementById('spot-detail-cta-btn');
            if (!ctaBtn) return;
            if (isAdded) {
                ctaBtn.innerHTML = '<i class="fa-solid fa-check"></i> Added to Itinerary';
                ctaBtn.style.background = '#ecfdf5';
                ctaBtn.style.color = '#059669';
                ctaBtn.style.border = '1.5px solid #a7f3d0';
                ctaBtn.style.boxShadow = 'none';
            } else {
                ctaBtn.innerHTML = '<i class="fa-solid fa-plus"></i> Add to Itinerary Stops';
                ctaBtn.style.background = 'linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%)';
                ctaBtn.style.color = '#ffffff';
                ctaBtn.style.border = 'none';
                ctaBtn.style.boxShadow = '0 4px 12px rgba(2,132,199,0.25)';
            }
        };

        window.toggleSpotFromDetailModal = function () {
            const ctaBtn = document.getElementById('spot-detail-cta-btn');
            if (!ctaBtn) return;
            const spotId = ctaBtn.getAttribute('data-spot-id');
            if (!spotId) return;

            window.toggleStep2SliderSpot(spotId);

            let draft = [];
            try {
                draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary')) || [];
            } catch (e) { }
            const isAdded = draft.some(s => String(s.id) === String(spotId));
            window.syncSpotDetailModalBtn(isAdded);
        };

        window.closeSpotDetailsModal = function () {
            const modal = document.getElementById('itin-spot-details-modal');
            if (modal) modal.style.display = 'none';
            document.body.style.overflow = '';
            document.body.classList.remove('spot-detail-modal-open', 'modal-blurred');
            document.documentElement.classList.remove('spot-detail-modal-open', 'modal-blurred');
            const bNav = document.getElementById('magic-nav') || document.getElementById('bottom-navigation') || document.querySelector('.magic-nav');
            if (bNav) {
                bNav.style.filter = '';
                bNav.style.webkitFilter = '';
                bNav.style.opacity = '';
                bNav.style.pointerEvents = '';
            }
        };

        // ----------------------------------------------------
        // PLAN ITINERARY 3-STEP PAGE-LEVEL WIZARD CONTROLLER
        // ----------------------------------------------------
        window.currentItinStep = 1;

        window.switchItineraryStep = function (step) {
            step = parseInt(step, 10) || 1;
            if (step < 1) step = 1;
            if (step > 3) step = 3;
            window.currentItinStep = step;

            // Toggle step containers
            const s1 = document.getElementById('itin-step-1');
            const s2 = document.getElementById('itin-step-2');
            const s3 = document.getElementById('itin-step-3');
            if (s1) s1.classList.toggle('active', step === 1);
            if (s2) s2.classList.toggle('active', step === 2);
            if (s3) s3.classList.toggle('active', step === 3);

            // Update fixed bottom action bar steps (docked intact above bottom navigation)
            document.querySelectorAll('#itin-bar-step-1').forEach(b => b.style.setProperty('display', (step === 1) ? 'block' : 'none', 'important'));
            document.querySelectorAll('#itin-bar-step-2').forEach(b => b.style.setProperty('display', (step === 2) ? 'block' : 'none', 'important'));
            document.querySelectorAll('#itin-bar-step-3').forEach(b => b.style.setProperty('display', (step === 3) ? 'block' : 'none', 'important'));

            // Ensure container padding accommodates the active fixed bar height so content is never covered
            const itinContainer = document.querySelector('.itinerary-container');
            if (itinContainer) {
                itinContainer.style.paddingBottom = (step === 2)
                    ? 'calc(max(env(safe-area-inset-bottom, 0px), 18px) + 235px)'
                    : 'calc(max(env(safe-area-inset-bottom, 0px), 18px) + 180px)';
            }

            // Update top progress tabs
            for (let i = 1; i <= 3; i++) {
                const tab = document.getElementById('itin-tab-' + i);
                const circle = document.getElementById('itin-circle-' + i);
                if (!tab) continue;

                tab.classList.remove('active', 'completed');
                if (i === step) {
                    tab.classList.add('active');
                    if (circle) circle.innerHTML = String(i);
                } else if (i < step) {
                    tab.classList.add('completed');
                    if (circle) circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:9px;"></i>';
                } else {
                    if (circle) circle.innerHTML = String(i);
                }
            }

            // Scroll smoothly to top of wizard
            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (step === 2) {
                if (typeof window.renderStep2SpotsSlider === 'function') {
                    window.renderStep2SpotsSlider();
                }
                if (typeof window.renderItinerary === 'function') {
                    window.renderItinerary();
                }
                setTimeout(() => {
                    try {
                        if (typeof draftMap !== 'undefined' && draftMap) {
                            draftMap.invalidateSize();
                            if (typeof window.fitDraftBounds === 'function') {
                                window.fitDraftBounds();
                            }
                        }
                    } catch (e) {
                        console.warn('Map resize error in Step 2:', e);
                    }
                }, 200);
            } else if (step === 3) {
                // Ensure default vehicle exists
                const curTrans = document.getElementById('trip-transport')?.value || '';
                if (!curTrans || curTrans === 'no_vehicle' || /no[_ ]vehicle/i.test(curTrans)) {
                    window.selectWizardVehicle('tricycle');
                }
                window.syncWizardStep2UI();
                window.syncWizardStep3UI();
            }
        };

        window.proceedToStep = function (targetStep) {
            targetStep = parseInt(targetStep, 10) || 1;

            // Step 1 Validation: Trip name & Companion ages are required
            if (targetStep >= 2) {
                const titleInput = document.getElementById('trip-title');
                const titleVal = titleInput ? titleInput.value.trim() : '';
                if (!titleVal) {
                    if (typeof showToast === 'function') showToast('Please enter a trip name before proceeding');
                    if (titleInput) titleInput.focus();
                    return;
                }

                const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
                if (isGroup && Array.isArray(window.companionList) && window.companionList.length > 0) {
                    const emptyIdx = window.companionList.findIndex(c => c.age === '' || c.age === null || c.age === undefined || isNaN(Number(c.age)) || Number(c.age) < 3 || Number(c.age) > 99);
                    if (emptyIdx !== -1) {
                        const comp = window.companionList[emptyIdx];
                        const genderLabel = comp.gender === 'female' ? 'Female' : 'Male';
                        const isUnder3 = comp.age !== '' && comp.age !== null && Number(comp.age) < 3;
                        const isOver99 = comp.age !== '' && comp.age !== null && Number(comp.age) > 99;
                        let msg = `Please enter an age for companion #${emptyIdx + 1} (${genderLabel})`;
                        if (isUnder3) msg = `Companion #${emptyIdx + 1} (${genderLabel}) age must be 3 or older (0–2 not included)`;
                        if (isOver99) msg = `Companion #${emptyIdx + 1} (${genderLabel}) age cannot exceed 99`;
                        if (typeof showToast === 'function') {
                            showToast(msg);
                        }
                        const ageInputs = document.querySelectorAll('.companion-age-input');
                        if (ageInputs && ageInputs[emptyIdx]) {
                            ageInputs[emptyIdx].focus();
                            ageInputs[emptyIdx].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return;
                    }
                }
            }

            // Step 2 Validation: Must have at least 1 spot
            if (targetStep === 3) {
                const draft = window.getEffectiveDraft ? window.getEffectiveDraft() : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                if (!Array.isArray(draft) || draft.length === 0) {
                    if (typeof showToast === 'function') showToast('Your itinerary has no spots yet! Add destinations in Step 2.');
                    window.switchItineraryStep(2);
                    return;
                }
            }

            window.switchItineraryStep(targetStep);
        };

        // Backward compatibility mappings
        window.goToWizardStep = function (step) {
            window.proceedToStep(step);
        };

        window.openSaveModal = function (e) {
            if (e && typeof e.preventDefault === 'function') e.preventDefault();
            window.proceedToStep(3);
        };

        window.closeSaveModal = function (btn) {
            document.querySelectorAll('#save-trip-modal').forEach(m => m.style.display = 'none');
            document.body.classList.remove('modal-blurred', 'itin-confirm-open');
            window.switchItineraryStep(2);
        };

        window.confirmCancelSaveModal = function () {
            window.switchItineraryStep(2);
        };

        // Unselect all vehicles in Step 3
        window.unselectAllVehicles = function () {
            const transInput = document.getElementById('trip-transport');
            if (transInput) transInput.value = 'no_vehicle';

            localStorage.setItem('intan_elyu_draft_trip_transport', 'no_vehicle');
            localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify([]));
            localStorage.removeItem('intan_elyu_draft_leg_vehicles');

            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.updateDraftTravelModeBar === 'function') {
                window.updateDraftTravelModeBar();
            }

            window.syncWizardStep2UI();
            window.syncWizardStep3UI();
        };

        // Vehicle selection in Step 3 (Multi-Select)
        window.selectWizardVehicle = function (mode) {
            const validModes = ['tricycle', 'tpuj', 'mpuj', 'public_bus', 'private_bus', 'van', 'own_car', 'car', 'motorcycle'];
            if (mode === 'car') mode = 'own_car';
            if (!validModes.includes(mode)) mode = 'tricycle';

            let currentModes = [];
            const rawStored = localStorage.getItem('intan_elyu_draft_trip_transport') || '';
            const isExplicitlyNoVehicle = /no[_ ]vehicle/i.test(rawStored);

            if (!isExplicitlyNoVehicle) {
                try {
                    const rawArr = localStorage.getItem('intan_elyu_draft_trip_transports');
                    if (rawArr) currentModes = JSON.parse(rawArr);
                } catch (e) { }

                if (!Array.isArray(currentModes) || currentModes.length === 0) {
                    const single = document.getElementById('trip-transport')?.value || rawStored;
                    if (single && !/no[_ ]vehicle/i.test(single)) {
                        currentModes = (typeof window.parseCompositeTransportModes === 'function')
                            ? window.parseCompositeTransportModes(single)
                            : [single];
                    }
                }
            }

            currentModes = (Array.isArray(currentModes) ? currentModes : [])
                .map(m => (m === 'car' ? 'own_car' : m))
                .filter(m => validModes.includes(m));

            // Toggle mode
            const targetMode = (mode === 'car') ? 'own_car' : mode;
            const hasMode = currentModes.some(m => m === targetMode || (targetMode === 'own_car' && m === 'car'));
            if (hasMode) {
                currentModes = currentModes.filter(m => m !== targetMode && m !== 'car' && m !== 'own_car');
            } else {
                currentModes.push(targetMode);
            }

            const compositeMode = currentModes.length > 0 ? currentModes.join(' + ') : 'no_vehicle';
            const transInput = document.getElementById('trip-transport');
            if (transInput) transInput.value = compositeMode;

            localStorage.setItem('intan_elyu_draft_trip_transport', compositeMode);
            localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify(currentModes));
            localStorage.removeItem('intan_elyu_draft_leg_vehicles');

            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.updateDraftTravelModeBar === 'function') {
                window.updateDraftTravelModeBar();
            }

            window.syncWizardStep2UI();
            window.syncWizardStep3UI();
        };

        window.syncWizardStep2UI = function () {
            const validModes = ['tricycle', 'tpuj', 'mpuj', 'public_bus', 'private_bus', 'van', 'own_car', 'car', 'motorcycle'];
            let activeModes = [];
            const rawStored = localStorage.getItem('intan_elyu_draft_trip_transport') || '';
            const isExplicitlyNoVehicle = !rawStored || /no[_ ]vehicle/i.test(rawStored);

            if (!isExplicitlyNoVehicle) {
                try {
                    const raw = localStorage.getItem('intan_elyu_draft_trip_transports');
                    if (raw) activeModes = JSON.parse(raw);
                } catch (e) { }

                if (!Array.isArray(activeModes) || activeModes.length === 0) {
                    const cur = document.getElementById('trip-transport')?.value || rawStored;
                    if (cur && !/no[_ ]vehicle/i.test(cur)) {
                        activeModes = (typeof window.parseCompositeTransportModes === 'function')
                            ? window.parseCompositeTransportModes(cur)
                            : [cur];
                    }
                }
            }

            activeModes = (Array.isArray(activeModes) ? activeModes : [])
                .map(m => (m === 'car' ? 'own_car' : m))
                .filter(m => validModes.includes(m));

            const unselectBtn = document.getElementById('wizard-unselect-all-btn');
            if (unselectBtn) {
                unselectBtn.style.display = (activeModes.length > 0) ? 'inline-flex' : 'none';
            }

            const cards = document.querySelectorAll('.wizard-trans-card');
            cards.forEach(card => {
                const rawM = card.getAttribute('data-mode');
                const m = (rawM === 'car') ? 'own_car' : rawM;
                const radio = card.querySelector('.wizard-check-radio');
                const checkIcon = radio ? radio.querySelector('i') : null;
                const isSelected = activeModes.includes(m) || (m === 'own_car' && activeModes.includes('car')) || (m === 'car' && activeModes.includes('own_car'));
                if (isSelected) {
                    card.classList.add('active');
                    if (radio) {
                        radio.style.borderColor = '#0284c7';
                        radio.style.background = '#0284c7';
                    }
                    if (checkIcon) checkIcon.style.display = 'block';
                } else {
                    card.classList.remove('active');
                    if (radio) {
                        radio.style.borderColor = '#cbd5e1';
                        radio.style.background = 'transparent';
                    }
                    if (checkIcon) checkIcon.style.display = 'none';
                }
            });

            // Update live fare pill
            const fareEl = document.getElementById('wizard-transpo-fare-text');
            const legsBadge = document.getElementById('wizard-transpo-legs-badge');
            const draft = window.getEffectiveDraft ? window.getEffectiveDraft() : JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

            if (legsBadge) {
                legsBadge.textContent = draft.length > 0 ? (draft.length + ' Leg' + (draft.length > 1 ? 's' : '')) : '0 Legs';
            }

            let totalTransitFare = 0;
            if (Array.isArray(draft) && draft.length > 0 && typeof window.getLegTransportInfo === 'function') {
                for (let i = 0; i < draft.length; i++) {
                    const legInfo = window.getLegTransportInfo(i);
                    if (legInfo && legInfo.cost && !legInfo.is_maintenance) {
                        totalTransitFare += Number(legInfo.cost) || 0;
                    }
                }
            }
            if (fareEl) {
                fareEl.textContent = '₱' + totalTransitFare.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        };

        window.syncWizardStep3UI = function () {
            const nameEl = document.getElementById('wizard-review-name') || document.getElementById('page-review-name');
            const dateEl = document.getElementById('wizard-review-date') || document.getElementById('page-review-date');
            const partyEl = document.getElementById('wizard-review-party') || document.getElementById('page-review-party');
            const muniEl = document.getElementById('page-review-muni');
            const transEl = document.getElementById('wizard-review-transport');

            const titleVal = document.getElementById('trip-title')?.value || 'My La Union Trip';
            const dateDisplay = document.getElementById('custom-date-display')?.textContent || 'Flexible / Anytime';
            const partySummary = document.getElementById('companion-summary-text')?.textContent || '1 Traveler (Solo)';
            const muniSelect = document.getElementById('trip-municipality');
            const muniVal = (muniSelect && muniSelect.options[muniSelect.selectedIndex])
                ? (muniSelect.options[muniSelect.selectedIndex].text.split('(')[0].trim() || muniSelect.value)
                : (muniSelect?.value || 'San Juan');
            const transMode = document.getElementById('trip-transport')?.value || 'tricycle';

            const transLabels = {
                'tricycle': 'Tricycle',
                'tpuj': 'Traditional Jeepney',
                'mpuj': 'Modern Jeepney',
                'van': 'Van',
                'car': 'Own Vehicle',
                'motorcycle': 'Motorcycle'
            };
            let prettyTrans = 'None (Own arrangement)';
            if (transMode && !/no[_ ]vehicle/i.test(transMode)) {
                const rawModes = (typeof window.parseCompositeTransportModes === 'function')
                    ? window.parseCompositeTransportModes(transMode)
                    : String(transMode).split('+').map(s => s.trim());
                prettyTrans = rawModes
                    .map(m => transLabels[window.normalizeVehicleKey ? window.normalizeVehicleKey(m) : m] || m)
                    .join(' + ') || 'None';
            }

            if (nameEl) nameEl.textContent = titleVal;
            if (dateEl) dateEl.textContent = (dateDisplay === 'Select trip date') ? 'Flexible / Anytime' : dateDisplay;
            if (partyEl) partyEl.textContent = partySummary;
            if (muniEl) muniEl.textContent = muniVal;
            if (transEl) transEl.textContent = prettyTrans;

            if (typeof window.calculateModalBudget === 'function') {
                window.calculateModalBudget();
            }
            if (typeof window.renderSaveModalSummaryContent === 'function') {
                window.renderSaveModalSummaryContent();
            }
        };

        // Initialize state for fresh or edit trip
        window.initTripWizardState = function () {
            // Clean up any stray bar previously ported to document.body
            document.querySelectorAll('body > #itin-fixed-bottom-bar').forEach(el => {
                try { el.remove(); } catch (e) { }
            });

            const editingId = sessionStorage.getItem('editing_itinerary_id');
            const submitBtn = document.getElementById('btn-submit-trip');

            if (editingId) {
                const savedTitle = sessionStorage.getItem('editing_trip_title') || '';
                const savedDate = sessionStorage.getItem('editing_trip_date') || '';
                const savedTransport = sessionStorage.getItem('editing_trip_transport') || 'tricycle';
                const savedTripType = sessionStorage.getItem('editing_trip_type') || 'solo';

                const titleInput = document.getElementById('trip-title');
                if (titleInput && savedTitle) titleInput.value = savedTitle;

                if (savedDate) {
                    const targetDateStr = savedDate.split(/[T ]/)[0];
                    const dateInput = document.getElementById('trip-date');
                    if (dateInput) dateInput.value = targetDateStr;

                    const display = document.getElementById('custom-date-display') || document.getElementById('trip-date-display');
                    if (display) {
                        const parts = targetDateStr.split('-');
                        if (parts.length === 3) {
                            const dObj = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                            if (!isNaN(dObj.getTime())) {
                                const formatted = dObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                                display.textContent = formatted;
                                display.style.color = '#0f172a';
                                display.style.fontWeight = '700';
                            }
                        }
                    }
                    const clearLink = document.getElementById('calendar-clear-link');
                    if (clearLink) clearLink.style.display = 'inline';
                }

                if (typeof window.setTripType === 'function') {
                    window.setTripType(savedTripType);
                }

                const savedMales = parseInt(sessionStorage.getItem('editing_companion_males') || '0', 10);
                const savedFemales = parseInt(sessionStorage.getItem('editing_companion_females') || '0', 10);
                const savedAdults = parseInt(sessionStorage.getItem('editing_companion_adults') || '0', 10);
                const savedKids = parseInt(sessionStorage.getItem('editing_companion_kids') || '0', 10);
                const savedSeniors = parseInt(sessionStorage.getItem('editing_companion_seniors') || '0', 10);
                const savedPwds = parseInt(sessionStorage.getItem('editing_companion_pwds') || '0', 10);

                const mEl = document.getElementById('companion-males-count');
                const fEl = document.getElementById('companion-females-count');
                const aEl = document.getElementById('companion-adults-count');
                const kEl = document.getElementById('companion-kids-count');
                const sEl = document.getElementById('companion-seniors-count');
                const pEl = document.getElementById('companion-pwds-count');
                if (mEl) mEl.textContent = isNaN(savedMales) ? '0' : savedMales;
                if (fEl) fEl.textContent = isNaN(savedFemales) ? '0' : savedFemales;
                if (aEl) aEl.textContent = isNaN(savedAdults) ? '0' : savedAdults;
                if (kEl) kEl.textContent = isNaN(savedKids) ? '0' : savedKids;
                if (sEl) sEl.textContent = isNaN(savedSeniors) ? '0' : savedSeniors;
                if (pEl) pEl.textContent = isNaN(savedPwds) ? '0' : savedPwds;

                // Restore individual companion age inputs
                const savedAgesJson = sessionStorage.getItem('editing_companion_ages');
                if (savedAgesJson) {
                    try {
                        window.companionList = JSON.parse(savedAgesJson);
                    } catch (e) {
                        window.companionList = [];
                    }
                } else if ((isNaN(savedMales) ? 0 : savedMales) > 0 || (isNaN(savedFemales) ? 0 : savedFemales) > 0) {
                    window.companionList = [];
                    const totM = isNaN(savedMales) ? 0 : savedMales;
                    const totF = isNaN(savedFemales) ? 0 : savedFemales;
                    let kidLeft = isNaN(savedKids) ? 0 : savedKids;
                    let seniorLeft = isNaN(savedSeniors) ? 0 : savedSeniors;
                    let pwdLeft = isNaN(savedPwds) ? 0 : savedPwds;
                    for (let i = 0; i < totM; i++) {
                        let age = 25, isPwd = false;
                        if (pwdLeft > 0) { isPwd = true; pwdLeft--; }
                        else if (kidLeft > 0) { age = 10; kidLeft--; }
                        else if (seniorLeft > 0) { age = 65; seniorLeft--; }
                        window.companionList.push({ id: 'm_' + i, gender: 'male', age, isPwd });
                    }
                    for (let i = 0; i < totF; i++) {
                        let age = 25, isPwd = false;
                        if (pwdLeft > 0) { isPwd = true; pwdLeft--; }
                        else if (kidLeft > 0) { age = 10; kidLeft--; }
                        else if (seniorLeft > 0) { age = 65; seniorLeft--; }
                        window.companionList.push({ id: 'f_' + i, gender: 'female', age, isPwd });
                    }
                } else {
                    window.companionList = [];
                }
                if (typeof window.renderCompanionAgeRows === 'function') {
                    window.renderCompanionAgeRows();
                }

                if (typeof window.updateCompanionHeadcountSummary === 'function') {
                    window.updateCompanionHeadcountSummary();
                }

                if (savedTransport) {
                    window.selectWizardVehicle(savedTransport);
                }

                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Update Trip';
                }
            } else {
                const rawDraft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                if (rawDraft.length > 0) {
                    const titleInput = document.getElementById('trip-title');
                    if (titleInput && !titleInput.value) {
                        titleInput.value = 'My La Union Trip';
                    }
                }
            }

            // Always start at Step 1 (Trip Scope & Parameters) on refresh or navigation
            window.switchItineraryStep(1);
        };


        window.submitItinerary = async function () {
            if (window._isSubmittingItinerary) return;

            const title = document.getElementById('trip-title').value.trim();
            const date = document.getElementById('trip-date').value;
            const todayIso = new Date().toISOString().split('T')[0];
            if (date && date < todayIso) {
                return showToast("Please select a current or future trip date");
            }
            const budgetStr = document.getElementById('trip-budget')?.value || '';
            const budget = budgetStr ? parseFloat(budgetStr) : null;
            if (!title) return showToast("Please enter a trip name");

            const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            if (isGroup && Array.isArray(window.companionList) && window.companionList.length > 0) {
                const emptyIdx = window.companionList.findIndex(c => c.age === '' || c.age === null || c.age === undefined || isNaN(Number(c.age)) || Number(c.age) < 3 || Number(c.age) > 99);
                if (emptyIdx !== -1) {
                    const comp = window.companionList[emptyIdx];
                    const genderLabel = comp.gender === 'female' ? 'Female' : 'Male';
                    const isUnder3 = comp.age !== '' && comp.age !== null && Number(comp.age) < 3;
                    const isOver99 = comp.age !== '' && comp.age !== null && Number(comp.age) > 99;
                    let msg = `Please enter an age for companion #${emptyIdx + 1} (${genderLabel})`;
                    if (isUnder3) msg = `Companion #${emptyIdx + 1} (${genderLabel}) age must be 3 or older (0–2 not included)`;
                    if (isOver99) msg = `Companion #${emptyIdx + 1} (${genderLabel}) age cannot exceed 99`;
                    showToast(msg);
                    window.switchItineraryStep(1);
                    const ageInputs = document.querySelectorAll('.companion-age-input');
                    if (ageInputs && ageInputs[emptyIdx]) {
                        ageInputs[emptyIdx].focus();
                        ageInputs[emptyIdx].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }
            }

            const draft = window.getEffectiveDraft();
            if (draft.length === 0) return showToast("Your itinerary is empty!");

            const transport = document.getElementById('trip-transport').value;
            const isNoVeh = !transport || !transport.trim() || transport.toLowerCase().includes('no_vehicle') || transport.toLowerCase().includes('no vehicle');

            let missingLegVehicle = false;
            if (Array.isArray(draft) && draft.length > 0) {
                for (let i = 0; i < draft.length; i++) {
                    const leg = (typeof window.getLegTransportInfo === 'function') ? window.getLegTransportInfo(i) : null;
                    if (leg && !leg.is_maintenance) {
                        const legNoVeh = !leg.has_selection || leg.mode === 'no_vehicle' || !leg.mode || String(leg.name).toLowerCase().includes('no vehicle') || (Array.isArray(leg.transport_modes) && leg.transport_modes.length === 0);
                        if (legNoVeh) {
                            missingLegVehicle = true;
                            break;
                        }
                    }
                }
            }

            if (isNoVeh) {
                window.showConfirmModal('Please choose a vehicle before saving your draft plan.', {
                    title: 'Vehicle Required',
                    confirmText: 'OK',
                    hideCancel: true
                });
                return;
            }
            if (missingLegVehicle) {
                window.showConfirmModal('Vehicle is required in all legs. Please choose a vehicle for each leg before saving your draft plan.', {
                    title: 'Vehicle Required',
                    confirmText: 'OK',
                    hideCancel: true
                });
                return;
            }

            const editingId = sessionStorage.getItem('editing_itinerary_id');
            const confirmMsg = editingId
                ? 'Ready to update this trip? You can still make changes anytime later.'
                : 'Ready to save this trip? You can still edit your plan anytime later.';

            const confirmed = await window.showConfirmModal(confirmMsg, {
                title: editingId ? 'Update Trip?' : 'Save Trip?',
                confirmText: editingId ? 'Update Trip' : 'Save Trip',
                cancelText: 'Cancel',
                icon: 'fa-bookmark'
            });
            if (!confirmed) return;

            const effectiveTransport = transport.trim();

            window._isSubmittingItinerary = true;

            const btn = document.getElementById('btn-submit-trip');
            const clientRequestId = editingId ? null : ('trip_' + Date.now() + '_' + Math.random().toString(36).slice(2, 12));
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${editingId ? 'Updating...' : 'Saving...'}`;
            btn.disabled = true;

            const extractTrueSpotId = function (place) {
                if (!place) return 0;
                // Prefer explicit tourist spot foreign keys first
                if (place.tourist_spot_id && parseInt(place.tourist_spot_id, 10) > 0) {
                    return parseInt(place.tourist_spot_id, 10);
                }
                if (place.destination_id && parseInt(place.destination_id, 10) > 0) {
                    return parseInt(place.destination_id, 10);
                }
                if (place.destination && (place.destination.tourist_spot_id || place.destination.id)) {
                    const dId = parseInt(place.destination.tourist_spot_id || place.destination.id, 10);
                    if (dId > 0) return dId;
                }
                if (place.id) {
                    const parsed = parseInt(place.id, 10);
                    if (!isNaN(parsed) && parsed > 0) return parsed;
                }
                return 0;
            };

            const destinations = draft.map(place => extractTrueSpotId(place)).filter(id => id > 0);

            // Universal local storage updater so saved_trips ALWAYS displays the new trip instantly (0ms)
            const persistTripLocally = function (tripObj) {
                if (!tripObj) return;
                try {
                    const tripId = String(tripObj.id || tripObj.local_id || '');
                    const nowIso = new Date().toISOString();
                    const isQueued = Boolean(tripObj.is_offline_queued);
                    const enrichedTrip = {
                        ...tripObj,
                        id: tripId,
                        updated_at: tripObj.updated_at || nowIso,
                        is_offline_queued: isQueued
                    };

                    if (typeof window.syncTripToAllStorage === 'function') {
                        window.syncTripToAllStorage(enrichedTrip, { clearQueuedFlag: !isQueued, matchTitle: true });
                    } else {
                        // Direct fallback if main.js helper is not loaded
                        ['intan_elyu_saved_trips', 'intan_elyu_offline_saved_trips_backup'].forEach(k => {
                            let list = JSON.parse(localStorage.getItem(k) || '[]');
                            if (!Array.isArray(list)) list = [];
                            const idx = list.findIndex(t => t && (String(t.id) === tripId || String(t.local_id) === tripId || (editingId && String(t.id) === String(editingId))));
                            if (idx >= 0) list[idx] = enrichedTrip;
                            else list.unshift(enrichedTrip);
                            localStorage.setItem(k, JSON.stringify(list));
                        });
                    }

                    // Clean up any queued offline update in OfflineTripManager if saving online
                    if (!isQueued && window.OfflineTripManager && typeof window.OfflineTripManager.removeQueuedTrip === 'function') {
                        if (editingId) {
                            window.OfflineTripManager.removeQueuedTrip('offline_edit_' + editingId);
                            window.OfflineTripManager.removeQueuedTrip(editingId);
                        }
                        if (tripId) {
                            window.OfflineTripManager.removeQueuedTrip(tripId);
                        }
                    }
                } catch (err) {
                    console.warn("persistTripLocally error:", err);
                }
            };

            const cleanupItineraryDraft = function (finalTripId) {
                sessionStorage.setItem('just_saved_trip_id', String(finalTripId));
                sessionStorage.setItem('active_trip_transport_' + finalTripId, effectiveTransport);
                localStorage.setItem('selected_trip_vehicle_' + finalTripId, effectiveTransport);
                window._lastRenderedTripsSig = null;
                window._isStartingTrip = false;

                // Clear stale trip and dashboard caches so saved_trips renders fresh data without flashing old data
                const curToken = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                if (curToken) {
                    const tKey = curToken.substring(0, 10);
                    localStorage.removeItem('saved_trips_' + tKey);
                    localStorage.removeItem('dashboard_saved_trips_' + tKey);
                    localStorage.removeItem('dashboard_trips_' + tKey);
                }
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && (key.startsWith('saved_trips_') || key.startsWith('dashboard_saved_trips_') || key.startsWith('dashboard_trips_'))) {
                        localStorage.removeItem(key);
                        i--;
                    }
                }
                window._cachedSavedTrips = null;

                // Always clean up draft itinerary and pre-edit backup so old data never restores over new input!
                localStorage.removeItem('intan_elyu_draft_itinerary');
                localStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                sessionStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                sessionStorage.removeItem('editing_itinerary_id');
                sessionStorage.removeItem('editing_trip_title');
                sessionStorage.removeItem('editing_trip_date');
                sessionStorage.removeItem('editing_trip_budget');
                sessionStorage.removeItem('editing_trip_transport');
                sessionStorage.removeItem('editing_trip_type');
                sessionStorage.removeItem('editing_companion_males');
                sessionStorage.removeItem('editing_companion_females');
                sessionStorage.removeItem('editing_total_pax');
                sessionStorage.removeItem('editing_transport_initialized');
                localStorage.removeItem('intan_elyu_draft_leg_vehicles');
                localStorage.removeItem('intan_elyu_draft_trip_transport');
                localStorage.removeItem('intan_elyu_draft_trip_transports');
                window.resetSaveModalInputs();
                document.querySelectorAll('#save-trip-modal').forEach(m => { m.style.display = 'none'; });
                document.body.classList.remove('itin-confirm-open');
                const bottomNav = document.getElementById('bottom-navigation');
                if (bottomNav) bottomNav.classList.remove('nav-hidden');
                window.renderItinerary();
            };

            let tripPayload = null;
            try {
                const activeRouteType = document.querySelector('.btn-route-type.active')?.innerText || ((window.currentRouteType === 'alternative' || window.currentRouteType === 'alternate') ? 'Alternative' : 'Recommended');
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                const isGuest = (typeof window.isGuestUser === 'function')
                    ? window.isGuestUser()
                    : (!token ||
                        token === 'guest_token' ||
                        token === 'offline_guest_token' ||
                        token.startsWith('guest_') ||
                        token.startsWith('offline_') ||
                        localStorage.getItem('intan_elyu_guest') === 'true' ||
                        localStorage.getItem('intan_elyu_offline_guest') === 'true');

                const isOffline = (typeof navigator.onLine === 'boolean' && !navigator.onLine)
                    || window.isNetworkOffline === true
                    || (window.isAppOnline === false)
                    || localStorage.getItem('intan_elyu_offline_mode') === 'true'
                    || localStorage.getItem('intan_elyu_offline_guest') === 'true';

                const isOfflineTripId = Boolean(editingId && String(editingId).startsWith('offline_'));

                if (!token && !isGuest && navigator.onLine && !isOffline) {
                    btn.innerHTML = editingId ? 'Update Trip' : 'Save Trip';
                    btn.disabled = false;
                    showToast("Session expired. Please log in to save your trip.");
                    navigateTo('auth');
                    return;
                }

                const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                const url = editingId ? `${bUrl}/api/tourist/itineraries/${editingId}` : `${bUrl}/api/tourist/itineraries`;
                const method = editingId ? 'PUT' : 'POST';

                const legTransports = draft.map((place, idx) => {
                    const legInfo = (typeof window.getLegTransportInfo === 'function')
                        ? window.getLegTransportInfo(idx)
                        : { mode: effectiveTransport, cost: 0, distance_km: 0 };
                    return {
                        leg_index: idx + 1,
                        to_spot_id: extractTrueSpotId(place),
                        transport_mode: legInfo.mode || legInfo.transport_mode || effectiveTransport,
                        leg_cost: parseFloat(legInfo.cost || legInfo.leg_cost || 0),
                        leg_distance_km: parseFloat(legInfo.distance_km || 0)
                    };
                });

                // Derive overall transport from legs if user picked leg vehicles
                const distinctLegModes = Array.from(new Set(
                    legTransports.map(l => l.transport_mode).filter(m => m && m !== 'no_vehicle' && !m.toLowerCase().includes('no vehicle'))
                ));

                let finalTransport = effectiveTransport;
                if (distinctLegModes.length > 0) {
                    finalTransport = distinctLegModes.join(' + ');
                } else if (isNoVeh || finalTransport === 'own_car') {
                    finalTransport = 'No Vehicle Selected';
                }

                const tripType = document.querySelector('input[name="trip_companion_type"]:checked')?.value || 'solo';
                const compMales = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('companion-males-count')?.textContent || '0', 10)) : 0;
                const compFemales = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('companion-females-count')?.textContent || '0', 10)) : 0;
                const compAdults = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('companion-adults-count')?.textContent || '0', 10)) : 0;
                const compKids = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('companion-kids-count')?.textContent || '0', 10)) : 0;
                const compSeniors = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('companion-seniors-count')?.textContent || '0', 10)) : 0;
                const compPwds = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('companion-pwds-count')?.textContent || '0', 10)) : 0;
                const addCompanions = Math.max(compMales + compFemales, compAdults + compKids + compSeniors + compPwds);
                const totalPax = tripType === 'group' ? (1 + addCompanions) : 1;

                tripPayload = {
                    title: title,
                    trip_date: date || null,
                    budget: budget,
                    destinations: destinations,
                    route_type: activeRouteType,
                    transport_mode: finalTransport,
                    trip_type: tripType,
                    companion_males: compMales,
                    companion_females: compFemales,
                    companion_adults: compAdults,
                    companion_kids: compKids,
                    companion_seniors: compSeniors,
                    companion_pwds: compPwds,
                    companion_ages: (window.companionList || []).map(c => Number(c.age) || 25),
                    total_pax: totalPax,
                    client_request_id: clientRequestId,
                    leg_transports: legTransports,
                    update_id: editingId || null,
                    items: draft.map((place, idx) => ({
                        id: 'offline_item_' + Date.now() + '_' + (idx + 1),
                        tourist_spot_id: extractTrueSpotId(place),
                        destination: place,
                        transport_type: finalTransport,
                        transport_mode: (legTransports && legTransports[idx] && legTransports[idx].transport_mode) ? legTransports[idx].transport_mode : finalTransport,
                        companion_males: compMales,
                        companion_females: compFemales,
                        companion_adults: compAdults,
                        companion_kids: compKids,
                        companion_seniors: compSeniors,
                        companion_pwds: compPwds,
                        companion_ages: (window.companionList || []).map(c => Number(c.age) || 25),
                        total_pax: totalPax,
                        is_visited: false,
                        proof_image: null,
                        proof_status: null
                    }))
                };

                // Fast offline trip queueing or Guest mode local device save
                if ((isGuest || isOffline || isOfflineTripId) && tripPayload) {
                    let queued = null;
                    if (window.OfflineTripManager && typeof window.OfflineTripManager.queueOfflineTrip === 'function') {
                        queued = window.OfflineTripManager.queueOfflineTrip(tripPayload);
                    }
                    const queuedId = queued ? (queued.local_id || queued.id) : ('offline_' + Date.now());
                    const localTripObj = {
                        ...tripPayload,
                        id: queuedId,
                        local_id: queuedId,
                        is_offline_queued: true,
                        created_at: new Date().toISOString()
                    };
                    persistTripLocally(localTripObj);

                    if (isGuest) {
                        showToast(editingId ? "Trip updated on device! Create an account anytime to sync to cloud." : "Trip saved on device! Log in or register anytime to sync.", "success", 4000);
                    } else {
                        showToast(editingId ? "📍 Offline Mode: Trip changes saved on device for auto-sync!" : "📍 Offline Mode: Trip saved on device for auto-sync!", "info", 5000);
                    }

                    cleanupItineraryDraft(queuedId);
                    navigateTo('saved_trips');
                    return;
                }

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
                        trip_type: tripType,
                        companion_males: compMales,
                        companion_females: compFemales,
                        companion_adults: compAdults,
                        companion_kids: compKids,
                        companion_seniors: compSeniors,
                        companion_pwds: compPwds,
                        companion_ages: (window.companionList || []).map(c => Number(c.age) || 25),
                        total_pax: totalPax,
                        client_request_id: clientRequestId,
                        leg_transports: legTransports
                    })
                });

                if (response.status === 401) {
                    if (!isGuest) {
                        if (window.shouldPreserveAuthSession && window.shouldPreserveAuthSession()) {
                            throw new TypeError('Network disconnected while saving itinerary');
                        }
                        localStorage.removeItem('intan_elyu_token');
                        localStorage.removeItem('Intan_Elyu_Token');
                        showToast("Session expired. Please log in again.");
                        navigateTo('auth');
                        return;
                    }
                }

                const data = await response.json();

                if (response.ok) {
                    const savedId = String(editingId || data.itinerary_id || (data.itinerary && data.itinerary.id) || ('trip_' + Date.now()));
                    const finalTripObj = (data.itinerary && typeof data.itinerary === 'object')
                        ? data.itinerary
                        : {
                            ...tripPayload,
                            id: savedId,
                            created_at: new Date().toISOString()
                        };
                    persistTripLocally(finalTripObj);

                    showToast(editingId ? "Trip updated successfully!" : "Trip saved successfully!");
                    cleanupItineraryDraft(savedId);
                    navigateTo('saved_trips');
                } else {
                    throw new Error(data.message || (editingId ? "Failed to update trip" : "Failed to save trip"));
                }
            } catch (error) {
                console.error("Save Error:", error);
                // Fallback: If network failed or offline or error occurred, ALWAYS queue on device so trip is NEVER lost!
                if (tripPayload) {
                    let queued = null;
                    if (window.OfflineTripManager && typeof window.OfflineTripManager.queueOfflineTrip === 'function') {
                        queued = window.OfflineTripManager.queueOfflineTrip(tripPayload);
                    }
                    const queuedId = queued ? (queued.local_id || queued.id) : ('offline_' + Date.now());
                    const offlineTripObj = {
                        ...tripPayload,
                        id: queuedId,
                        local_id: queuedId,
                        is_offline_queued: true,
                        created_at: new Date().toISOString()
                    };
                    persistTripLocally(offlineTripObj);
                    showToast(editingId ? "📍 Connection issue: Trip changes saved on device for auto-sync!" : "📍 Connection issue: Saved on device for auto-sync!", "info", 5000);
                    cleanupItineraryDraft(queuedId);
                    navigateTo('saved_trips');
                    return;
                }
                showToast(error.message || "Failed to save. Check connection.");
            } finally {
                btn.innerHTML = editingId ? 'Update Trip' : 'Save Trip';
                btn.disabled = false;
                window._isSubmittingItinerary = false;
            }
        };

        // Render immediately on view load
        window.renderItinerary();

        // ==========================================
        // MAP & DONUT CHART LOGIC
        // ==========================================

        var draftMap = window.draftMap || null;
        var draftRouteLineBg = null;
        var draftRouteLine = null;
        var draftMarkers = [];

        window.initDraftMap = function (draft, shouldFitBounds = true) {
            if (!draft || draft.length === 0) return;

            const mapContainer = document.getElementById('itinerary-map');
            if (!mapContainer) return;

            // Cancel any pending render timeout to prevent stale fitBounds calls
            if (window._renderTimeout) {
                clearTimeout(window._renderTimeout);
                window._renderTimeout = null;
            }

            // Reset if draftMap was bound to an older DOM node that was swapped during SPA navigation
            if (draftMap && draftMap.getContainer && draftMap.getContainer() !== mapContainer) {
                try { draftMap.remove(); } catch (e) { }
                draftMap = null;
            }

            if (!draftMap) {
                if (typeof L === 'undefined') return;
                try {
                    draftMap = L.map(mapContainer, {
                        attributionControl: false,
                        zoomControl: false,
                        scrollWheelZoom: false,
                        dragging: false,
                        touchZoom: false,
                        doubleClickZoom: false,
                        boxZoom: false,
                        keyboard: false
                    }).setView([16.6159, 120.3210], 13);
                    window.draftMap = draftMap;
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        subdomains: ['a', 'b', 'c'],
                        detectRetina: true,
                        keepBuffer: 4,
                        updateWhenZooming: true,
                        updateWhenIdle: false
                    }).addTo(draftMap);

                    // Bundled municipal geometry gives every offline map real geographic context.
                    draftMap.createPane('offlineBasePane');
                    draftMap.getPane('offlineBasePane').style.zIndex = 250;
                    const isOfflineDraftMap = !navigator.onLine || window.isAppOnline === false;
                    Promise.resolve(window.OFFLINE_MUNICIPALITIES_GEOJSON)
                        .then(geojson => {
                            if (!draftMap || !geojson) return;
                            const offlineBase = L.geoJSON(geojson, {
                                pane: 'offlineBasePane',
                                interactive: false,
                                style: {
                                    stroke: false,
                                    weight: 0,
                                    opacity: 0,
                                    fillColor: '#b8dcec',
                                    fillOpacity: isOfflineDraftMap ? 0.18 : 0
                                }
                            }).addTo(draftMap);
                            offlineBase._isOfflineBaseLayer = true;
                        })
                        .catch(error => console.warn('Offline municipality map unavailable:', error));
                } catch (e) {
                    console.warn('initDraftMap init error:', e);
                    draftMap = null;
                    return;
                }
            }

            if (!draftMap) return;

            // Force Leaflet to recalculate size since container was display:none.
            // Use double rAF so browser has fully laid out the container before we measure it.
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    if (draftMap && typeof draftMap.invalidateSize === 'function') {
                        draftMap.invalidateSize();
                    }
                });
            });

            // Clear ALL previous markers, overlays, and routes from draftMap (leaving only base tiles)
            if (draftMap) {
                draftMap.eachLayer(layer => {
                    if (!(layer instanceof L.TileLayer) && !layer._isOfflineBaseLayer) {
                        try { draftMap.removeLayer(layer); } catch (e) { }
                    }
                });
            }
            draftRouteLineBg = null;
            draftRouteLine = null;
            window.myDraftMarker = null;
            draftMarkers = [];
            window.itineraryDraft = draft;
            window.draftPlaceMarkers = [];

            var latlngs = [];
            window._draftLatLngs = latlngs;

            // Add single GPS Location indicator
            var rememberedGps = (typeof window.getRememberedGpsLocation === 'function') ? window.getRememberedGpsLocation() : null;
            var userGpsLat = window.myLat || window.currentGPSLat || (rememberedGps && rememberedGps.lat) || null;
            var userGpsLng = window.myLng || window.currentGPSLng || (rememberedGps && rememberedGps.lng) || null;
            if (userGpsLat && userGpsLng) {
                window.myLat = userGpsLat;
                window.myLng = userGpsLng;
                latlngs.push([userGpsLat, userGpsLng]);
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
                window.myDraftMarker = L.marker([userGpsLat, userGpsLng], { icon: myIcon, zIndexOffset: 1000 })
                    .addTo(draftMap)
                    .bindPopup('<div style="font-size:9.5px; font-weight:800; color:#1e3a8a; line-height:1.15;">📍 Your Location</div><div style="font-size:8px; color:#64748b; margin-top:1px;">Starting Point</div>', { closeButton: false, autoPanPadding: [10, 10] });
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
                    .bindPopup(`<div style="font-size:9.5px; font-weight:800; color:#1e3a8a; line-height:1.15;">Stop ${index + 1}: ${place.name}</div><div style="font-size:8px; color:#64748b; margin-top:1px;">${place.location || place.address || (place.municipality ? place.municipality + ', La Union' : 'La Union')}</div>`, { closeButton: false, autoPanPadding: [10, 10] });
                marker._stopIndex = index;
                draftMarkers.push(marker);
                window.draftPlaceMarkers.push(marker);
            });

            if (latlngs.length > 1) {
                const activeRouteEl = document.querySelector('.btn-route-type.active');
                const isAlt = (window.currentRouteType === 'alternative' || window.currentRouteType === 'alternate' || activeRouteEl?.innerText.trim() === 'Alternative' || activeRouteEl?.innerText.trim() === 'Alternate');
                const activeRoute = isAlt ? 'Alternative' : 'Recommended';

                let routeColor = isAlt ? '#f59e0b' : '#38bdf8'; // Alternative = Vibrant Amber/Gold, Recommended = Cyan/Blue
                let shadowColor = isAlt ? '#78350f' : '#0f172a';

                // Draw immediately so offline mode never waits for OSRM to fail.
                draftRouteLineBg = L.polyline(latlngs, {
                    color: shadowColor, weight: 7, opacity: 0.4, lineJoin: 'round', lineCap: 'round'
                }).addTo(draftMap);
                draftRouteLine = L.polyline(latlngs, {
                    color: routeColor, weight: 4.5, opacity: 1, lineJoin: 'round', lineCap: 'round', dashArray: '8, 6'
                }).addTo(draftMap);
                let offlineDistanceKm = 0;
                for (let routeIndex = 0; routeIndex < latlngs.length - 1; routeIndex++) {
                    offlineDistanceKm += draftMap.distance(latlngs[routeIndex], latlngs[routeIndex + 1]) / 1000;
                }
                const offlineDurationMin = Math.max(1, Math.round((offlineDistanceKm / 30) * 60));
                window.setTxt('draft-map-dist', offlineDistanceKm.toFixed(1) + ' km');
                window.setTxt('draft-map-time', offlineDurationMin + ' min');

                let fetchLatLngs = [...latlngs];
                const coordString = fetchLatLngs.map(ll => `${ll[1]},${ll[0]}`).join(';');

                if (shouldFitBounds) {
                    draftMap.fitBounds(L.latLngBounds(latlngs), { padding: [30, 30] });
                }

                // If Alternative and multiple stops: query leg-by-leg alternative corridors or multi-point route
                const fetchRoutePromise = (!navigator.onLine || window.isAppOnline === false) ? Promise.resolve(null) : (async () => {
                    if (isAlt && fetchLatLngs.length >= 2) {
                        // Fetch leg by leg with alternative option for each leg
                        try {
                            const legGeometries = [];
                            const legList = [];
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
                                    legList.push({ distance: chosen.distance, duration: chosen.duration });
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
                                    },
                                    legs: legList
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
                                geometry: chosenRoute.geometry,
                                legs: chosenRoute.legs || []
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

                        // Synchronize Next Stop (Stop 1) distance and drive duration chip with real OSRM road calculation
                        let leg0DistKm = distanceKm;
                        let leg0DurMin = durationMin;
                        if (routeData.legs && routeData.legs.length > 0) {
                            let lDist = (routeData.legs[0].distance || 0) / 1000;
                            let lDur = (routeData.legs[0].duration || 0) / 60;
                            if (isAlt) {
                                lDist *= 1.12;
                                lDur *= 1.25;
                            }
                            let lMult = 1.6;
                            if (lDist <= 3) lMult = 2.5;
                            else if (lDist <= 7) lMult = 2.0;
                            lDur *= lMult;
                            if (isRushHour) lDur *= 1.35;
                            leg0DistKm = lDist;
                            leg0DurMin = lDur;
                        }

                        const leg0DistText = leg0DistKm < 1 ? Math.round(leg0DistKm * 1000) + ' m' : leg0DistKm.toFixed(1) + ' km';
                        const leg0DurMinRound = Math.max(1, Math.round(leg0DurMin));
                        const leg0DurText = leg0DurMinRound >= 60
                            ? `${Math.floor(leg0DurMinRound / 60)}h ${leg0DurMinRound % 60}m`
                            : `${leg0DurMinRound} mins`;

                        const firstPlace = (typeof draft !== 'undefined' && Array.isArray(draft) && draft[0]) ? draft[0] : null;
                        const firstKey = firstPlace ? String(firstPlace.id || firstPlace.name) : null;

                        window._cachedNextStopOSRM = {
                            distanceKm: leg0DistKm,
                            distanceText: leg0DistText,
                            durationMin: leg0DurMinRound,
                            durationText: leg0DurText,
                            spotKey: firstKey
                        };

                        window._cachedLegOSRM = window._cachedLegOSRM || {};
                        if (routeData.legs && Array.isArray(routeData.legs)) {
                            const hasGpsStart = Boolean(userGpsLat && userGpsLng);
                            routeData.legs.forEach((legObj, rIdx) => {
                                let lDist = (legObj.distance || 0) / 1000;
                                if (isAlt) lDist *= 1.12;
                                const draftIdx = hasGpsStart ? rIdx : rIdx + 1;
                                if (draftIdx > 0 && draft[draftIdx - 1] && draft[draftIdx]) {
                                    const fSpot = draft[draftIdx - 1];
                                    const tSpot = draft[draftIdx];
                                    const lKey = `${fSpot.id || fSpot.name}_${tSpot.id || tSpot.name}`;
                                    window._cachedLegOSRM[lKey] = { distanceKm: lDist };
                                }
                            });
                        }

                        const nextEtaEl = document.getElementById('itinerary-next-eta');
                        if (nextEtaEl) {
                            nextEtaEl.innerHTML = `
                                <span style="color:#1e3a8a !important; font-weight:700; font-size:10px; letter-spacing:-0.1px;">${leg0DistText} away &bull; ~${leg0DurText} drive from your location</span>
                            `;
                        }

                        if (typeof window.renderItinerary === 'function') {
                            window.renderItinerary(true);
                        }

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
            const isGroup = document.querySelector('input[name="trip_companion_type"]:checked')?.value === 'group';
            const compAdults = isGroup ? Math.max(0, parseInt(document.getElementById('companion-adults-count')?.textContent || '0', 10)) : 0;
            const compKids = isGroup ? Math.max(0, parseInt(document.getElementById('companion-kids-count')?.textContent || '0', 10)) : 0;
            const compSeniors = isGroup ? Math.max(0, parseInt(document.getElementById('companion-seniors-count')?.textContent || '0', 10)) : 0;
            const compPwds = isGroup ? Math.max(0, parseInt(document.getElementById('companion-pwds-count')?.textContent || '0', 10)) : 0;
            const numAdults = 1 + compAdults;
            const numKids = compKids;
            const numSeniors = compSeniors;
            const numPwds = compPwds;
            const totalTravelers = numAdults + numKids + numSeniors + numPwds;

            // NOTE: Sites under maintenance are closed/restricted and do NOT charge entrance, activity or food fees (₱0.00).
            // Sites that are simply Closed by operating hours (e.g. 6am - 5pm outside operating hours) STILL get their site fees, option fees, and category.
            draft.forEach(item => {
                if (typeof window.isSpotUnderMaintenance === 'function' && window.isSpotUnderMaintenance(item)) {
                    return; // Exclude Under Maintenance
                }
                const adultFee = parseFloat(item.adult_fee || item.adultFee || 0);
                const entranceFee = parseFloat(item.entrance_fee || item.entranceFee || item.fee || 0);
                const baseAdult = adultFee > 0 ? adultFee : (entranceFee > 0 ? entranceFee : 0);
                const kidsFee = (item.kids_fee !== undefined && item.kids_fee !== null && item.kids_fee !== '')
                    ? (parseFloat(item.kids_fee) || 0)
                    : baseAdult;
                const seniorFee = (item.senior_citizen_fee !== undefined && item.senior_citizen_fee !== null && item.senior_citizen_fee !== '' && parseFloat(item.senior_citizen_fee) > 0)
                    ? parseFloat(item.senior_citizen_fee)
                    : (baseAdult > 0 ? baseAdult * 0.8 : 0);
                const pwdFee = (item.pwd_fee !== undefined && item.pwd_fee !== null && item.pwd_fee !== '' && parseFloat(item.pwd_fee) > 0)
                    ? parseFloat(item.pwd_fee)
                    : (baseAdult > 0 ? baseAdult * 0.8 : 0);
                const envFee = parseFloat(item.environmental_fee || item.environmentalFee || item.envFee || 0) || 0;

                const spotEnvSenior = (seniorFee > 0 && baseAdult > 0) ? Math.round(envFee * (seniorFee / baseAdult)) : (envFee > 0 ? Math.round(envFee * 0.8) : 0);
                const spotEnvPwd = (pwdFee > 0 && baseAdult > 0) ? Math.round(envFee * (pwdFee / baseAdult)) : (envFee > 0 ? Math.round(envFee * 0.8) : 0);

                const spotEntrance = (numAdults * baseAdult) + (numKids * kidsFee) + (numSeniors * seniorFee) + (numPwds * pwdFee);
                const childEnvFee = kidsFee > 0 ? envFee : 0;
                const spotEnvi = (numAdults * envFee) + (numKids * childEnvFee) + (numSeniors * spotEnvSenior) + (numPwds * spotEnvPwd);

                actCost += (spotEntrance + spotEnvi);
                foodCost += (parseFloat(item.avg_food_cost) || 150) * totalTravelers;
            });

            // Calculate dynamic transit cost based on current draft sequence and selected transport
            const curTransport = localStorage.getItem('intan_elyu_draft_trip_transport') || document.getElementById('trip-transport')?.value || '';

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
                    { val: 'mpuj', name: 'Modern Jeepney', icon: 'fa-van-shuttle', available: true, key: 'mpuj' },
                    { val: 'tpuj', name: 'Traditional Jeepney', icon: 'fa-van-shuttle', available: true, key: 'tpuj' },
                    { val: 'pub_aircon', name: 'Aircon Bus', icon: 'fa-bus', available: true, key: 'pub_aircon' },
                    { val: 'pub_ordinary', name: 'Regular Bus', icon: 'fa-bus-simple', available: true, key: 'pub_regular' },
                    {
                        val: 'tricycle',
                        name: 'Tricycle',
                        icon: 'fa-motorcycle',
                        available: true,
                        key: 'tricycle',
                        badgeHtml: isInterMunicipal ? '<span style="padding:1px 6px; border-radius:100px; font-size:8px; font-weight:800; background:rgba(56,189,248,0.25); color:#7dd3fc;">Local Legs</span>' : ''
                    },
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

        // Initial render on load
        try {
            if (window.renderItinerary) window.renderItinerary();
        } catch (e) {
            console.warn('renderItinerary on load error:', e);
        }
        try {
            if (window.initTripWizardState) window.initTripWizardState();
        } catch (e) {
            console.warn('initTripWizardState on load error:', e);
        }
    })();
</script>
