<!-- Itinerary View -->
<?php
$pageTitle = 'My Itinerary';
$activeTab = 'itinerary';
?>



<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    body {
        background: var(--bg-primary) !important;
    }

    .btn-route-type {
        background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
        border: none !important;
        outline: none !important;
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        box-shadow: none !important;
    }

    .btn-route-type.active {
        background: linear-gradient(135deg, #00f2fe 0%, #0284c7 60%, #1e3a8a 100%) !important;
        border: none !important;
        outline: none !important;
        color: #ffffff !important;
        font-weight: 800;
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none;
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
        <h2 style="margin:0; font-size:22px; font-weight:800; letter-spacing:-0.5px;">Draft Plan</h2>
        <div style="display:flex; align-items:center; gap: 8px;">
            <!-- Saved Trips Button (Small) -->
            <button onclick="navigateTo('saved_trips')"
                style="background: rgba(30, 58, 138, 0.78); border: none !important; outline: none !important; color: #ffffff; font-weight:700; height: 32px; padding: 0 14px; border-radius:20px; font-size:12px; cursor:pointer; display:flex; align-items:center; box-sizing: border-box; box-shadow: none;">
                <i class="fa-solid fa-bookmark" style="margin-right:6px;"></i> Saved Trips
            </button>
            <span
                style="background:rgba(255, 255, 255, 0.16); border: none !important; outline: none !important; color:#ffffff; height: 32px; padding: 0 14px; border-radius:20px; font-size:12px; font-weight:800; display:flex; align-items:center; box-sizing: border-box;">
                <span id="itinerary-count" style="margin-right:4px;">0</span> Places
            </span>
        </div>
    </div>

    <!-- Map Visualization Container -->
    <div id="draft-map-wrapper" style="display:none; margin-top:16px; margin-bottom:20px;" class="stagger-2">
        <!-- Toggles -->
        <div style="display:flex; gap:8px; margin-bottom:12px; overflow-x:auto; padding-bottom:4px;"
            class="hide-scrollbar">
            <button class="btn-route-type active" id="btn-route-rec"
                onclick="setRouteType('recommended', this)">Recommended</button>
            <button class="btn-route-type" id="btn-route-alt"
                onclick="setRouteType('alternate', this)">Alternate</button>
        </div>

        <!-- The Map -->
        <div id="draft-map-container"
            style="height: 260px; width:100%; border-radius: 20px; overflow: hidden; border: none !important; outline: none !important; position:relative; background:#cadce4; box-shadow: none;">
            <div id="itinerary-map" style="width:100%; height:100%;"></div>
        </div>

        <!-- Map Route Stats -->
        <div
            style="display:flex; justify-content:space-around; align-items:center; margin-top:12px; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border: none !important; outline: none !important; border-radius:18px; padding:14px; box-shadow:0 8px 24px rgba(10, 25, 60, 0.25);">
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
    <div class="timeline stagger-2" id="itinerary-timeline" style="margin-bottom: 20px;">
        <!-- Rendered via JS -->
    </div>

    <!-- Save Itinerary Action -->
    <button class="btn-primary" id="btn-save-itinerary"
        style="display:none; width:100%; padding:16px; border-radius:20px; font-weight:900; font-size:16px; margin-bottom:40px; border: none !important; outline: none !important; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; box-shadow:none;"
        onclick="openSaveModal()">
        <i class="fa-solid fa-cloud-arrow-up" style="margin-right:8px;"></i> Save Draft Plan
    </button>

    <!-- Empty State Card -->
    <div id="itinerary-empty-state" class="empty-state-card is-hidden" style="display:none;">
        <div class="empty-state-icon">
            <i class="fa-solid fa-route"></i>
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
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }
        #save-trip-modal input[type="text"]:focus,
        #save-trip-modal input[type="tel"]:focus,
        #save-trip-modal input[type="number"]:focus {
            outline: none !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }
    </style>

    <div
        style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.99) 100%); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:1px solid rgba(56, 189, 248, 0.3); border-radius:24px; padding:22px; width:100%; max-width:400px; max-height:90vh; overflow-y:auto; box-shadow:0 24px 60px rgba(0,0,0,0.7), 0 0 30px rgba(56,189,248,0.15);" class="hide-scrollbar">
        <h3 style="margin-top:0; color:#ffffff; font-size:20px; font-weight:800; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-cloud-arrow-up" style="color:#38bdf8; font-size:18px;"></i> Save Your Trip
        </h3>
        <p style="font-size:13px; color:rgba(226, 232, 240, 0.85); margin-bottom:18px; line-height:1.4;">Give your awesome adventure a name so you can pull it up later!</p>

        <label style="font-size:12px; color:rgba(226, 232, 240, 0.85); margin-bottom:6px; display:block; font-weight:600;">Trip Name</label>
        <input type="text" id="trip-title" placeholder="e.g. La Union Weekend"
            style="width:100%; padding:12px 16px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.07); color:#ffffff; margin-bottom:16px; font-family:inherit; font-size:14px; box-sizing:border-box;">

        <!-- Custom Designed Calendar Date Picker -->
        <label style="font-size:12px; color:rgba(226, 232, 240, 0.85); margin-bottom:6px; display:flex; align-items:center; justify-content:space-between; font-weight:600;">
            <span><i class="fa-regular fa-calendar-days" style="color:#38bdf8; margin-right:5px;"></i> Trip Date (Optional)</span>
            <span id="calendar-clear-link" onclick="window.customClearDate(event)" style="display:none; font-size:11px; color:#ef4444; cursor:pointer; font-weight:700;">Clear</span>
        </label>
        
        <div id="custom-date-trigger" onclick="window.toggleCustomCalendar(event)" style="position:relative; width:100%; padding:11px 16px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.07); color:white; margin-bottom:16px; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:all 0.2s ease; user-select:none;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-calendar-day" style="color:#38bdf8; font-size:14px;"></i>
                <span id="custom-date-display" style="color:rgba(255,255,255,0.5);">Select trip date</span>
            </div>
            <i class="fa-solid fa-chevron-down" id="custom-date-arrow" style="font-size:11px; color:rgba(255,255,255,0.4); transition:transform 0.25s ease;"></i>
        </div>
        <input type="hidden" id="trip-date" value="">

        <!-- Floating Sleek Custom Calendar Card -->
        <div id="custom-calendar-dropdown" style="display:none; margin-top:-8px; margin-bottom:16px; background:linear-gradient(145deg, rgba(15,23,42,0.98), rgba(30,41,59,0.98)); border:1px solid rgba(56,189,248,0.35); border-radius:20px; padding:16px; box-shadow:0 15px 35px rgba(0,0,0,0.6); animation:smoothReveal 0.25s ease;">
            <!-- Month & Year Navigation -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding:0 4px;">
                <button type="button" onclick="window.changeCalendarMonth(-1)" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:white; width:30px; height:30px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s;">
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                </button>
                <div id="calendar-month-year" style="font-size:14px; font-weight:800; color:#ffffff; letter-spacing:0.3px;"></div>
                <button type="button" onclick="window.changeCalendarMonth(1)" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:white; width:30px; height:30px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s;">
                    <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                </button>
            </div>

            <!-- Day of Week Headers -->
            <div style="display:grid; grid-template-columns:repeat(7, 1fr); text-align:center; margin-bottom:6px;">
                <span style="font-size:11px; font-weight:700; color:#ef4444;">Su</span>
                <span style="font-size:11px; font-weight:700; color:rgba(56,189,248,0.8);">Mo</span>
                <span style="font-size:11px; font-weight:700; color:rgba(56,189,248,0.8);">Tu</span>
                <span style="font-size:11px; font-weight:700; color:rgba(56,189,248,0.8);">We</span>
                <span style="font-size:11px; font-weight:700; color:rgba(56,189,248,0.8);">Th</span>
                <span style="font-size:11px; font-weight:700; color:rgba(56,189,248,0.8);">Fr</span>
                <span style="font-size:11px; font-weight:700; color:#38bdf8;">Sa</span>
            </div>

            <!-- Days Grid -->
            <div id="calendar-days-grid" style="display:grid; grid-template-columns:repeat(7, 1fr); gap:3px; text-align:center;">
                <!-- Generated dynamically via JS -->
            </div>

            <!-- Footer Quick Actions -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.08);">
                <button type="button" onclick="window.selectTodayDate()" style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; font-size:11px; font-weight:700; padding:5px 12px; border-radius:100px; cursor:pointer;">
                    Today
                </button>
                <button type="button" onclick="window.toggleCustomCalendar(null, false)" style="background:rgba(255,255,255,0.08); border:none; color:white; font-size:11px; font-weight:700; padding:5px 12px; border-radius:100px; cursor:pointer;">
                    Done
                </button>
            </div>
        </div>

        <label style="font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:8px; display:block;">Transport Type</label>
        <div
            style="display:flex; gap:8px; margin-bottom:16px; background:rgba(255,255,255,0.05); padding:4px; border-radius:12px; border:1px solid rgba(255,255,255,0.05);">
            <button class="btn-transport-toggle" id="btn-trans-public" onclick="window.setTransportType('public')"
                style="flex:1; padding:10px; border-radius:10px; border:none; background:transparent; font-size:13px; font-weight:600; color:rgba(255,255,255,0.7); transition:0.2s; cursor:pointer;">Public</button>
            <button class="btn-transport-toggle" id="btn-trans-private" onclick="window.setTransportType('private')"
                style="flex:1; padding:10px; border-radius:10px; border:none; background:transparent; font-size:13px; font-weight:600; color:rgba(255,255,255,0.7); transition:0.2s; cursor:pointer;">Private</button>
        </div>

        <div id="transport-slider-wrapper" style="display:none;">
            <label id="mode-transport-label"
                style="font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:4px; display:block;">Mode of
                Transport</label>

            <style>
                @keyframes smoothReveal {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-smooth-reveal {
                    animation: smoothReveal 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                }

                #transport-slider::-webkit-scrollbar {
                    display: none;
                }

                .transport-option {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    min-width: 90px;
                    padding: 14px 8px;
                    border-radius: 16px;
                    background: rgba(255, 255, 255, 0.03);
                    border: 1px solid rgba(255, 255, 255, 0.05);
                    cursor: pointer;
                    transition: 0.2s;
                    color: rgba(255, 255, 255, 0.7);
                    flex-shrink: 0;
                }

                .transport-option i {
                    font-size: 24px;
                    margin-bottom: 8px;
                }

                .transport-option span {
                    font-size: 11px;
                    font-weight: 600;
                    text-align: center;
                }

                .transport-option.active {
                    background: rgba(56, 189, 248, 0.1);
                    border-color: #38bdf8;
                    color: #38bdf8;
                }
            </style>

            <input type="hidden" id="trip-transport" value="">
            <div id="transport-slider"
                style="display:flex; overflow-x:auto; gap:12px; padding-bottom:8px; margin-bottom:16px; scrollbar-width:none; -ms-overflow-style:none;">
                <div class="transport-option" data-val="own_car" onclick="window.selectTransportMode(this)">
                    <i class="fa-solid fa-car"></i>
                    <span>Own Car</span>
                </div>
                <div class="transport-option" data-val="taxi" onclick="window.selectTransportMode(this)">
                    <i class="fa-solid fa-taxi"></i>
                    <span>Taxi</span>
                </div>
                <div class="transport-option" data-val="private_bus" onclick="window.selectTransportMode(this)">
                    <i class="fa-solid fa-bus"></i>
                    <span>Private Bus</span>
                </div>
                <div class="transport-option" data-val="mini_bus" onclick="window.selectTransportMode(this)">
                    <i class="fa-solid fa-van-shuttle"></i>
                    <span>Mini Bus</span>
                </div>
                <div class="transport-option" data-val="lutrampco" onclick="window.selectTransportMode(this)">
                    <i class="fa-solid fa-bus-simple"></i>
                    <span>LUTRAMPCO</span>
                </div>
                <div class="transport-option" data-val="jeepney" onclick="window.selectTransportMode(this)">
                    <i class="fa-solid fa-truck-pickup"></i>
                    <span>Jeepney</span>
                </div>
            </div>
        </div>

        <script>
            window.selectTransportMode = function (el) {
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
                } else {
                    // Public: multi-select allowed
                    el.classList.toggle('active');
                }

                const selected = [...document.querySelectorAll('.transport-option.active')].map(o => o.getAttribute('data-val'));
                document.getElementById('trip-transport').value = selected.join(',');

                const fuelPanel = document.getElementById('own-car-fuel-panel');
                if (fuelPanel) {
                    if (selected.includes('own_car')) {
                        fuelPanel.style.maxHeight = '200px';
                        fuelPanel.style.opacity = '1';
                    } else {
                        fuelPanel.style.maxHeight = '0';
                        fuelPanel.style.opacity = '0';
                    }
                }

                if (window.calculateModalBudget) window.calculateModalBudget();
            };
        </script>

        <!-- Own Car: Fuel Inputs (hidden until own_car selected) -->
        <div id="own-car-fuel-panel"
            style="max-height:0; overflow:hidden; opacity:0; transition: max-height 0.4s ease, opacity 0.35s ease; margin-bottom:0;">
            <div
                style="background:rgba(56,189,248,0.06); border:1px solid rgba(56,189,248,0.15); border-radius:12px; padding:12px 14px; margin-bottom:12px;">
                <p
                    style="font-size:11px; color:#38bdf8; font-weight:700; margin:0 0 10px 0; text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="fa-solid fa-gas-pump" style="margin-right:6px;"></i>Fuel Cost Calculator
                </p>
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
            <span style="position:absolute; left:16px; top:14px; color:white; font-weight:600;">₱</span>
            <input type="tel" id="trip-budget" placeholder="Set a budget (optional)"
                oninput="this.value=this.value.replace(/\D/g,'');if(this.value.length>5)this.value=this.value.slice(0,5);window.calculateModalBudget()"
                style="width:100%; padding:12px 16px 12px 32px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; font-family:inherit; font-size:14px;">
        </div>

        <div id="save-budget-details"
            style="display:none; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.05); padding:16px; border-radius:12px; margin-bottom:24px;">
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
                style="flex:1; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#e2e8f0; padding:12px; border-radius:14px; font-weight:700; font-size:14px; cursor:pointer;"
                onclick="closeSaveModal()">Cancel</button>
            <button class="btn-primary"
                style="flex:1; background:linear-gradient(135deg, #38bdf8 0%, #2563eb 100%); border:1px solid rgba(255,255,255,0.2); color:#ffffff; padding:12px; border-radius:14px; font-weight:800; font-size:14px; box-shadow:0 4px 16px rgba(56,189,248,0.4); cursor:pointer;"
                onclick="submitItinerary()" id="btn-submit-trip">Save Trip</button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirm-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div
        style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:85%; max-width:360px; box-shadow:0 20px 40px rgba(0,0,0,0.2); text-align:center;">
        <div
            style="width:48px; height:48px; border-radius:50%; background:rgba(245,158,11,0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b; font-size:22px;"></i>
        </div>
        <h3 style="margin:0 0 8px; color:#f8fafc; font-size:18px;">Missing Details</h3>
        <p id="confirm-modal-msg"
            style="margin:0 0 24px; color:rgba(148,163,184,0.9); font-size:14px; line-height:1.5;"></p>
        <div style="display:flex; gap:12px;">
            <button class="btn-primary" id="btn-confirm-cancel"
                style="flex:1; background:transparent; border:1px solid rgba(255,255,255,0.2); color:white;">Cancel</button>
            <button class="btn-primary" id="btn-confirm-ok"
                style="flex:1; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none;">Save Anyway</button>
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
                }
                function onOk() { cleanup(); resolve(true); }
                function onCancel() { cleanup(); resolve(false); }
                btnOk.addEventListener('click', onOk);
                btnCancel.addEventListener('click', onCancel);
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

        window.getFareFromMatrix = function (vehicleType, distanceKm) {
            if (!window.fareData) return null;
            const keyMap = {
                'Tricycle': 'tricycle', 'tricycle': 'tricycle',
                'Jeepney': 'jeepney', 'jeepney': 'jeepney', 'PUJ_Ordinary': 'jeepney',
                'Bus': 'private_bus', 'bus': 'private_bus', 'private_bus': 'private_bus', 'public_bus': 'private_bus', 'PUB_Aircon': 'private_bus', 'PUB_Ordinary': 'private_bus',
                'LUTRAMPCO': 'lutrampco', 'lutrampco': 'lutrampco',
                'Mini Bus': 'mini_bus', 'mini_bus': 'mini_bus', 'van': 'mini_bus', 'Van': 'mini_bus', 'uve': 'mini_bus', 'PUJ_Aircon': 'mini_bus',
                'Taxi': 'taxi', 'taxi': 'taxi',
                'Own Car': 'own_car', 'own_car': 'own_car'
            };
            const key = keyMap[vehicleType] || (vehicleType ? vehicleType.toLowerCase().replace(/\s+/g, '_') : '');
            if (!key || key === 'own_car') return null;

            // Search in fareData with fallback keys
            let fareEntry = window.fareData[key] || window.fareData[vehicleType];
            if (!fareEntry && (key === 'private_bus' || key === 'bus' || key === 'public_bus')) {
                fareEntry = window.fareData['private_bus'] || window.fareData['bus'] || window.fareData['PUB_Aircon'] || window.fareData['PUB_Ordinary'];
            }
            if (!fareEntry && (key === 'mini_bus' || key === 'van' || key === 'uve')) {
                fareEntry = window.fareData['mini_bus'] || window.fareData['van'] || window.fareData['PUJ_Aircon'];
            }
            if (!fareEntry && key === 'lutrampco') {
                fareEntry = window.fareData['lutrampco'] || window.fareData['PUB_Ordinary'] || window.fareData['jeepney'];
            }

            if (!fareEntry || !fareEntry.rates) return null;
            const rates = Array.isArray(fareEntry.rates) ? fareEntry.rates : Object.values(fareEntry.rates);
            if (!rates || rates.length === 0) return null;

            const dKm = parseFloat(distanceKm) || 0;
            let match = null;
            for (let i = rates.length - 1; i >= 0; i--) {
                const r = rates[i];
                if (r && r.distance_km != null && parseFloat(r.distance_km) <= dKm) {
                    match = r;
                    break;
                }
            }
            if (!match) match = rates.find(r => r && r.regular_fare != null) || rates[0];
            if (!match || match.regular_fare == null) return null;

            return parseFloat(match.regular_fare);
        };

        window.currentRouteType = window.currentRouteType || 'recommended';

        window.getEffectiveDraft = function () {
            let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            if (draft.length > 1 && window.currentRouteType === 'alternate') {
                return [...draft].reverse();
            }
            return draft;
        };

        window.renderItinerary = function (skipMap = false) {
            const draft = window.getEffectiveDraft();
            const rawDraft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
            const timeline = document.getElementById('itinerary-timeline');
            const emptyState = document.getElementById('itinerary-empty-state');
            const fab = document.getElementById('btn-save-itinerary');
            const mapWrapper = document.getElementById('draft-map-wrapper');

            document.getElementById('itinerary-count').innerText = rawDraft.length;

            if (rawDraft.length === 0) {
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
            fab.style.setProperty('display', 'flex', 'important');
            if (mapWrapper) mapWrapper.style.display = 'block';

            // Sync active class on route toggle buttons
            const recBtn = document.getElementById('btn-route-rec');
            const altBtn = document.getElementById('btn-route-alt');
            if (recBtn && altBtn) {
                if (window.currentRouteType === 'alternate') {
                    recBtn.classList.remove('active');
                    altBtn.classList.add('active');
                } else {
                    altBtn.classList.remove('active');
                    recBtn.classList.add('active');
                }
            }

            let html = '';

            // Render Starting Point (Your Location) Card at the top of the timeline
            const hasGPS = (window.myLat && window.myLng);
            const startingStatus = hasGPS
                ? `<i class="fa-solid fa-circle-dot" style="color:#10b981; font-size:9px;"></i> Real-time GPS Locked`
                : `<i class="fa-solid fa-spinner fa-spin" style="color:#f59e0b; font-size:9px;"></i> Acquiring GPS position...`;

            html += `
        <div class="starting-point-item stagger-1" onclick="window.routeToMyLocation()">
            <div class="starting-point-card">
                <div class="starting-point-icon-box">
                    <i class="fa-solid fa-location-crosshairs"></i>
                    <div class="starting-point-pulse"></div>
                </div>
                <div class="starting-point-info">
                    <div class="starting-point-label"><i class="fa-solid fa-play" style="font-size:8px;"></i> Starting Point</div>
                    <h3 class="starting-point-title">Your Current Location</h3>
                    <div class="starting-point-status" id="itinerary-starting-status">${startingStatus}</div>
                </div>
                <button type="button" onclick="event.stopPropagation(); window.routeToMyLocation();" style="background:rgba(255,255,255,0.18); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:800; padding:6px 14px; border-radius:100px; cursor:pointer; display:flex; align-items:center; gap:4px; flex-shrink:0;">
                    <i class="fa-solid fa-crosshairs"></i> Locate
                </button>
            </div>
            <div class="timeline-route-connector"></div>
        </div>`;

            draft.forEach((place, index) => {
                const hour = 9 + Math.floor(((index + 1) * 90) / 60);
                const min = ((index + 1) * 90) % 60;
                const timeStr = `${hour > 12 ? hour - 12 : hour}:${min === 0 ? '00' : min} ${hour >= 12 ? 'PM' : 'AM'}`;

                const isNextStop = (index === 0);
                let nextStopBadge = '';
                let nextStopEtaHtml = '';

                if (isNextStop) {
                    nextStopBadge = `<span class="badge-next-stop"><i class="fa-solid fa-location-dot"></i> NEXT STOP</span>`;
                    const lat = place.lat || place.latitude;
                    const lng = place.lng || place.longitude;
                    const eta = window.getDistanceAndETA(lat, lng);
                    if (eta) {
                        nextStopEtaHtml = `
                    <div class="next-stop-distance-chip" id="itinerary-next-eta">
                        <i class="fa-solid fa-route" style="color:#38bdf8;"></i> 
                        <span>${eta.distanceText} away &bull; ~${eta.durationText} drive from your location</span>
                    </div>`;
                    } else {
                        nextStopEtaHtml = `
                    <div class="next-stop-distance-chip" id="itinerary-next-eta">
                        <i class="fa-solid fa-location-arrow" style="color:#38bdf8;"></i> 
                        <span>First destination on your itinerary route</span>
                    </div>`;
                    }
                }

                html += `
            <div class="timeline-item ${isNextStop ? 'is-next-stop' : ''}" draggable="true" data-index="${index}" data-id="${place.id}" style="animation-delay: ${(index + 1) * 0.08}s">
                <div class="timeline-dot"></div>
                <div class="swipe-container" style="position:relative; overflow:hidden; border-radius:20px; -webkit-mask-image:-webkit-radial-gradient(white, black); mask-image:radial-gradient(white, black); isolation:isolate; contain:paint;">
                    <div class="swipe-delete-bg" style="position:absolute; top:0; right:0; bottom:0; width:80px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius:0 20px 20px 0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:800; gap:4px; transform:translateX(100%); z-index:1; opacity:0; pointer-events:none; transition:transform 0.2s ease, opacity 0.2s ease;"><i class="fa-solid fa-trash-can"></i> Delete</div>
                    <div class="swipe-content" style="position:relative; z-index:2; transition:transform 0.2s ease, border-radius 0.2s ease; border-radius:20px; padding:18px 20px; border:none !important; outline:none !important; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; box-shadow:0 8px 24px rgba(10, 25, 60, 0.25) !important;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:4px;">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span class="time-label">Stop ${index + 1} &bull; Approx ${timeStr}</span>
                                ${nextStopBadge}
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <button type="button" onclick="event.stopPropagation(); window.removeItineraryItem('${place.id}');" title="Remove stop" style="background:rgba(239,68,68,0.15); border:none !important; outline:none !important; color:#ef4444; border-radius:8px; cursor:pointer; font-size:11px; padding:4px 8px; display:inline-flex; align-items:center; gap:3px;">
                                    <i class="fa-solid fa-trash-can" style="font-size:10px;"></i>
                                </button>
                                <i class="fa-solid fa-grip-vertical" style="color:rgba(148,163,184,0.4); font-size:14px; cursor:grab; touch-action:none;"></i>
                            </div>
                        </div>
                        <h3 class="place-name">${place.name}</h3>
                        <p style="font-size:12.5px; color:#ffffff; opacity:0.95; font-weight:500; margin: 4px 0 8px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; line-height:1.4;">
                            ${place.description && place.description !== 'null' ? place.description : (place.category && place.category !== 'null' ? place.category : 'A beautiful destination to explore in La Union.')}
                        </p>
                        <div class="place-details">
                            <i class="fa-solid fa-location-dot"></i>
                            <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                ${place.location && place.location !== 'null' ? place.location : (place.address && place.address !== 'null' ? place.address : (place.municipality ? place.municipality + ', La Union' : 'San Fernando, La Union'))}
                            </span>
                        </div>
                        ${nextStopEtaHtml}
                        ${place.selected_vehicles && place.selected_vehicles.length > 0 ? `<div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:8px;">${place.selected_vehicles.map(v => `<span style="padding:2px 8px; border-radius:100px; font-size:10px; font-weight:700; background:rgba(56,189,248,0.15); color:#38bdf8; border:none !important; outline:none !important;"><i class="fa-solid fa-car" style="margin-right:3px;font-size:9px;"></i>${v}</span>`).join('')}</div>` : ''}
                    </div>
                </div>
            </div>`;
            });

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
                    let d = [...window.getEffectiveDraft()];
                    const [removed] = d.splice(dragIndex, 1);
                    d.splice(targetIndex, 0, removed);
                    localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(d));
                    window.currentRouteType = 'recommended';
                    window.renderItinerary();
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
                            let d = [...window.getEffectiveDraft()];
                            const [removed] = d.splice(dragIndex, 1);
                            d.splice(targetIndex, 0, removed);
                            localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(d));
                            window.currentRouteType = 'recommended';
                            window.renderItinerary();
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
                    content.style.borderRadius = translate > 5 ? '20px 0 0 20px' : '20px';
                    content.style.borderRightColor = translate > 5 ? 'transparent' : '';
                    if (bg) {
                        bg.style.opacity = '1';
                        bg.style.pointerEvents = 'auto';
                        bg.style.transform = `translateX(${80 - translate}px)`;
                    }
                }, { passive: true });

                content.addEventListener('touchend', (e) => {
                    content.style.transition = 'transform 0.2s ease, border-radius 0.2s ease';
                    if (bg) bg.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
                    const diff = startX - currentX;
                    if (diff > 60 && isSwiping) {
                        const id = item.dataset.id;
                        if (id) window.removeItineraryItem(id);
                    } else {
                        content.style.transform = '';
                        content.style.borderRadius = '20px';
                        content.style.borderRightColor = '';
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

        // Real-time dynamic GPS listener
        let _gpsUpdateTimeout = null;
        document.addEventListener('gpsUpdated', function (e) {
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
                    // Dynamically create and inject the glowing GPS user marker
                    const myIconHtml = `
                    <div class="gps-user-marker-icon">
                        <div class="gps-user-marker-wave"></div>
                        <div class="gps-user-marker-inner">
                            <i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>
                        </div>
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
                    if (typeof draftMarkers !== 'undefined') draftMarkers.push(window.myDraftMarker);
                }

                // Recalculate the route to connect the path to the new physical GPS location
                clearTimeout(_gpsUpdateTimeout);
                _gpsUpdateTimeout = setTimeout(() => {
                    const currentDraft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                    if (window.initDraftMap) window.initDraftMap(currentDraft, false);
                }, 2000);
            }
        });

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
        }

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

            // Transport cost — sum across all selected modes with realistic commuter rates
            let transCost = 0;
            const modes = transport ? transport.split(',').filter(Boolean) : [];
            let distKm = window._draftDistanceKm || 0;
            if (distKm <= 0 && draft.length > 0) {
                let totalD = 0;
                const pts = [];
                if (window.myLat && window.myLng) pts.push([parseFloat(window.myLat), parseFloat(window.myLng)]);
                draft.forEach(p => {
                    const lat = parseFloat(p.lat || p.latitude);
                    const lng = parseFloat(p.lng || p.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) pts.push([lat, lng]);
                });
                for (let i = 0; i < pts.length - 1; i++) {
                    const lat1 = pts[i][0], lon1 = pts[i][1];
                    const lat2 = pts[i + 1][0], lon2 = pts[i + 1][1];
                    const p = 0.017453292519943295;
                    const a = 0.5 - Math.cos((lat2 - lat1) * p) / 2 + Math.cos(lat1 * p) * Math.cos(lat2 * p) * (1 - Math.cos((lon2 - lon1) * p)) / 2;
                    totalD += (12742 * Math.asin(Math.sqrt(a))) * 1.25;
                }
                distKm = Math.max(1, totalD);
            }
            modes.forEach(mode => {
                if (mode === 'own_car') {
                    const fuelPrice = parseFloat(document.getElementById('fuel-price')?.value) || 65;
                    const fuelEffic = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
                    const litersNeeded = distKm / fuelEffic;
                    const cost = Math.ceil(litersNeeded * fuelPrice);
                    transCost += cost;

                    const hint = document.getElementById('fuel-distance-hint');
                    if (hint && distKm > 0) {
                        hint.textContent = `Route: ${distKm.toFixed(1)} km • ~${litersNeeded.toFixed(2)} L needed`;
                        hint.style.color = 'rgba(255,255,255,0.5)';
                    } else if (hint) {
                        hint.textContent = 'Open the Map first to get an accurate route distance.';
                        hint.style.color = '#FF9500';
                        transCost += Math.ceil((1 * fuelPrice) / fuelEffic);
                    }
                }
                else if (mode === 'private_bus' || mode === 'bus') {
                    const dbFare = window.getFareFromMatrix('bus', distKm) || window.getFareFromMatrix('private_bus', distKm);
                    if (dbFare !== null) {
                        transCost += Math.round(dbFare);
                    } else {
                        // Standard Provincial Commuter Bus fare: ₱15 base (first 5 km) + ₱2.20/km
                        transCost += Math.max(15, Math.round(15 + (Math.max(0, distKm - 5) * 2.2)));
                    }
                }
                else if (mode === 'mini_bus' || mode === 'van' || mode === 'uve') {
                    const dbFare = window.getFareFromMatrix('mini_bus', distKm) || window.getFareFromMatrix('van', distKm);
                    if (dbFare !== null) {
                        transCost += Math.round(dbFare);
                    } else {
                        // Standard UV Express / Mini Bus fare: ₱25 base (first 4 km) + ₱2.50/km
                        transCost += Math.max(25, Math.round(25 + (Math.max(0, distKm - 4) * 2.5)));
                    }
                }
                else if (mode === 'lutrampco') {
                    const dbFare = window.getFareFromMatrix('lutrampco', distKm);
                    if (dbFare !== null) {
                        transCost += Math.round(dbFare);
                    } else {
                        // Standard LUTRAMPCO Modernized Jeepney fare: ₱14 base (first 4 km) + ₱2.20/km
                        transCost += Math.max(14, Math.round(14 + (Math.max(0, distKm - 4) * 2.2)));
                    }
                }
                else if (mode === 'jeepney') {
                    const dbFare = window.getFareFromMatrix('jeepney', distKm);
                    if (dbFare !== null) {
                        transCost += Math.round(dbFare);
                    } else {
                        // Traditional Jeepney fare: ₱13 base (first 4 km) + ₱1.80/km
                        transCost += Math.max(13, Math.round(13 + (Math.max(0, distKm - 4) * 1.8)));
                    }
                }
                else if (mode === 'tricycle') {
                    const dbFare = window.getFareFromMatrix('tricycle', distKm);
                    if (dbFare !== null) {
                        transCost += Math.round(dbFare);
                    } else {
                        transCost += Math.max(15, Math.round(15 + (Math.max(0, distKm - 2) * 5)));
                    }
                }
                else if (mode === 'taxi') {
                    transCost += Math.max(50, Math.round(45 + (distKm * 15)));
                }
                else {
                    const dbFare = window.getFareFromMatrix(mode, distKm);
                    if (dbFare !== null) transCost += Math.round(dbFare);
                    else transCost += 30;
                }
            });

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
                    bg = 'linear-gradient(135deg, #38bdf8, #2563eb)';
                    color = '#ffffff';
                    weight = '800';
                    shadow = '0 4px 12px rgba(56,189,248,0.45)';
                } else if (isToday) {
                    bg = 'rgba(56,189,248,0.15)';
                    border = '1px solid rgba(56,189,248,0.5)';
                    color = '#38bdf8';
                    weight = '700';
                }

                html += `
                    <div onclick="window.selectCalendarDate('${dayStr}')" style="padding:7px 0; font-size:13px; font-weight:${weight}; cursor:pointer; border-radius:11px; background:${bg}; color:${color}; border:${border}; box-shadow:${shadow}; transition:all 0.15s ease; position:relative; display:flex; align-items:center; justify-content:center;">
                        ${day}
                        ${isToday && !isSelected ? '<span style="position:absolute; bottom:2px; width:4px; height:4px; border-radius:50%; background:#38bdf8;"></span>' : ''}
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
            }, 180);
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
                displayEl.style.color = 'rgba(255,255,255,0.5)';
                displayEl.style.fontWeight = 'normal';
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

            const isOpen = forceState !== undefined ? !forceState : (dropdown.style.display === 'block');
            if (isOpen) {
                dropdown.style.display = 'none';
                if (arrow) arrow.style.transform = 'rotate(0deg)';
                if (trigger) trigger.style.borderColor = 'rgba(255,255,255,0.15)';
            } else {
                dropdown.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(180deg)';
                if (trigger) trigger.style.borderColor = 'rgba(56,189,248,0.5)';
                window.renderCalendarGrid();
            }
        };

        window.openSaveModal = function () {
            const draft = window.getEffectiveDraft();

            // Populate dynamic fuel price from Railway DB
            const fuelInput = document.getElementById('fuel-price');
            if (fuelInput && window.fuelPrice) {
                fuelInput.value = window.fuelPrice;
            }

            // Auto-detect transport type from selected vehicles
            const veh = draft.find(p => p.transport_type);
            if (veh) {
                window.setTransportType(veh.transport_type);
                const vehicles = [...new Set(draft.flatMap(p => p.selected_vehicles || []).filter(Boolean))];
                document.querySelectorAll('.transport-option').forEach(opt => {
                    if (vehicles.includes(opt.dataset.val)) {
                        opt.classList.add('active');
                    }
                });
            }

            // Initialize calendar to current month
            const today = new Date();
            window.calendarState.currentYear = today.getFullYear();
            window.calendarState.currentMonth = today.getMonth();
            window.renderCalendarGrid();

            document.getElementById('save-trip-modal').style.display = 'flex';
            window.calculateModalBudget();

            // Hide bottom nav while modal is open (prevents keyboard pushing it up)
            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.add('nav-hidden');
        };

        window.closeSaveModal = function () {
            document.getElementById('save-trip-modal').style.display = 'none';

            // Restore bottom nav
            const bottomNav = document.getElementById('bottom-navigation');
            if (bottomNav) bottomNav.classList.remove('nav-hidden');
            document.getElementById('trip-title').value = '';
            window.customClearDate();
            window.toggleCustomCalendar(null, false);
            document.getElementById('trip-transport').value = '';
            document.getElementById('trip-budget').value = '';
            document.getElementById('save-budget-details').style.display = 'none';
            const pctEl = document.getElementById('modal-donut-pct');
            const remainingRow = document.getElementById('save-budget-remaining-row');
            const donutWrapper = document.getElementById('modal-donut-wrapper');
            if (pctEl) pctEl.textContent = '';
            if (remainingRow) remainingRow.style.display = 'none';
            if (donutWrapper) { donutWrapper.style.opacity = '0'; donutWrapper.style.transform = 'scale(0.7)'; donutWrapper.style.width = '0'; donutWrapper.style.marginRight = '0'; }
            if (_donutAnimFrame) { cancelAnimationFrame(_donutAnimFrame); _donutAnimFrame = null; }
            _currentDonutPct = 0;
            // Reset transport mode UI
            document.querySelectorAll('.transport-option').forEach(opt => opt.classList.remove('active'));
            const wrapper = document.getElementById('transport-slider-wrapper');
            if (wrapper) wrapper.style.display = 'none';
            const btnPublic = document.getElementById('btn-trans-public');
            const btnPrivate = document.getElementById('btn-trans-private');
            if (btnPublic) { btnPublic.style.background = 'transparent'; btnPublic.style.color = 'rgba(255,255,255,0.7)'; btnPublic.style.boxShadow = 'none'; }
            if (btnPrivate) { btnPrivate.style.background = 'transparent'; btnPrivate.style.color = 'rgba(255,255,255,0.7)'; btnPrivate.style.boxShadow = 'none'; }
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
                if (!transport) missing.push('a transport type');
                if (!budgetStr) missing.push('a budget');
                msg += missing.join(' or ');
                if (!(await window.showConfirmModal(msg))) return;
            }

            const btn = document.getElementById('btn-submit-trip');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            let totalTransCost = 0;
            const modes = transport ? transport.split(',').filter(Boolean) : [];
            let distKm = window._draftDistanceKm || 0;
            if (distKm <= 0 && draft.length > 0) {
                let totalD = 0;
                const pts = [];
                if (window.myLat && window.myLng) pts.push([parseFloat(window.myLat), parseFloat(window.myLng)]);
                draft.forEach(p => {
                    const lat = parseFloat(p.lat || p.latitude);
                    const lng = parseFloat(p.lng || p.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) pts.push([lat, lng]);
                });
                for (let i = 0; i < pts.length - 1; i++) {
                    const lat1 = pts[i][0], lon1 = pts[i][1];
                    const lat2 = pts[i + 1][0], lon2 = pts[i + 1][1];
                    const p = 0.017453292519943295;
                    const a = 0.5 - Math.cos((lat2 - lat1) * p) / 2 + Math.cos(lat1 * p) * Math.cos(lat2 * p) * (1 - Math.cos((lon2 - lon1) * p)) / 2;
                    totalD += (12742 * Math.asin(Math.sqrt(a))) * 1.25;
                }
                distKm = Math.max(1, totalD);
            }
            modes.forEach(mode => {
                if (mode === 'own_car') {
                    const fuelPrice = parseFloat(document.getElementById('fuel-price')?.value) || 65;
                    const fuelEffic = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
                    const litersNeeded = distKm / fuelEffic;
                    let cost = Math.ceil(litersNeeded * fuelPrice);
                    if (distKm <= 0) cost = Math.ceil((1 * fuelPrice) / fuelEffic);
                    totalTransCost += cost;
                }
                else if (mode === 'private_bus' || mode === 'bus') {
                    const dbFare = window.getFareFromMatrix('bus', distKm) || window.getFareFromMatrix('private_bus', distKm);
                    if (dbFare !== null) {
                        totalTransCost += Math.round(dbFare);
                    } else {
                        // Standard Provincial Commuter Bus fare: ₱15 base (first 5 km) + ₱2.20/km
                        totalTransCost += Math.max(15, Math.round(15 + (Math.max(0, distKm - 5) * 2.2)));
                    }
                }
                else if (mode === 'mini_bus' || mode === 'van' || mode === 'uve') {
                    const dbFare = window.getFareFromMatrix('mini_bus', distKm) || window.getFareFromMatrix('van', distKm);
                    if (dbFare !== null) {
                        totalTransCost += Math.round(dbFare);
                    } else {
                        // Standard UV Express / Mini Bus fare: ₱25 base (first 4 km) + ₱2.50/km
                        totalTransCost += Math.max(25, Math.round(25 + (Math.max(0, distKm - 4) * 2.5)));
                    }
                }
                else if (mode === 'lutrampco') {
                    const dbFare = window.getFareFromMatrix('lutrampco', distKm);
                    if (dbFare !== null) {
                        totalTransCost += Math.round(dbFare);
                    } else {
                        // Standard LUTRAMPCO Modernized Jeepney fare: ₱14 base (first 4 km) + ₱2.20/km
                        totalTransCost += Math.max(14, Math.round(14 + (Math.max(0, distKm - 4) * 2.2)));
                    }
                }
                else if (mode === 'jeepney') {
                    const dbFare = window.getFareFromMatrix('jeepney', distKm);
                    if (dbFare !== null) {
                        totalTransCost += Math.round(dbFare);
                    } else {
                        // Traditional Jeepney fare: ₱13 base (first 4 km) + ₱1.80/km
                        totalTransCost += Math.max(13, Math.round(13 + (Math.max(0, distKm - 4) * 1.8)));
                    }
                }
                else if (mode === 'tricycle') {
                    const dbFare = window.getFareFromMatrix('tricycle', distKm);
                    if (dbFare !== null) {
                        totalTransCost += Math.round(dbFare);
                    } else {
                        totalTransCost += Math.max(15, Math.round(15 + (Math.max(0, distKm - 2) * 5)));
                    }
                }
                else if (mode === 'taxi') {
                    totalTransCost += Math.max(50, Math.round(45 + (distKm * 15)));
                }
                else {
                    const dbFare = window.getFareFromMatrix(mode, distKm);
                    if (dbFare !== null) totalTransCost += Math.round(dbFare);
                    else totalTransCost += 30;
                }
            });

            const transCostPerPlace = transport ? (totalTransCost / draft.length) : null;

            const destinations = draft.map(place => place.id);

            try {
                const activeRouteType = document.querySelector('.btn-route-type.active')?.innerText || (window.currentRouteType === 'alternate' ? 'Alternate' : 'Recommended');
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                if (!token) {
                    btn.innerHTML = 'Save Trip';
                    btn.disabled = false;
                    showToast("Session expired. Please log in to save your trip.");
                    navigateTo('auth');
                    return;
                }

                const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ title: title, trip_date: date, budget: budget, destinations: destinations, route_type: activeRouteType, transport_mode: transport })
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
                    // Optimistically inject the new trip into the saved trips cache so it appears with 0ms latency
                    const cacheKey = 'saved_trips_' + token.substring(0, 10);
                    const dashCacheKey = 'dashboard_trips_' + token.substring(0, 10);
                    localStorage.removeItem(dashCacheKey);

                    if (data.itinerary) {
                        try {
                            const rawCached = localStorage.getItem(cacheKey);
                            let existingTrips = [];
                            if (rawCached) {
                                const parsed = window.safeJsonParse(rawCached, null);
                                if (parsed && Array.isArray(parsed.data)) {
                                    existingTrips = parsed.data;
                                }
                            }
                            const updatedTrips = [data.itinerary, ...existingTrips.filter(t => t.id !== data.itinerary.id)];
                            localStorage.setItem(cacheKey, JSON.stringify({
                                data: updatedTrips,
                                timestamp: Date.now()
                            }));
                        } catch (e) {
                            localStorage.removeItem(cacheKey);
                        }
                    } else {
                        localStorage.removeItem(cacheKey);
                    }

                    if (data.itinerary_id || (data.itinerary && data.itinerary.id)) {
                        sessionStorage.setItem('just_saved_trip_id', String(data.itinerary_id || data.itinerary.id));
                    }

                    showToast("Trip saved successfully!");
                    localStorage.removeItem('intan_elyu_draft_itinerary');
                    closeSaveModal();
                    window.renderItinerary();
                    navigateTo('saved_trips');
                } else {
                    throw new Error(data.message || "Failed to save trip");
                }
            } catch (error) {
                console.error("Save Error:", error);
                showToast(error.message || "Failed to save. Check connection.");
            } finally {
                btn.innerHTML = 'Save Trip';
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
                L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    subdomains: ['a', 'b'],
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

            // clear old markers and routes
            draftMarkers.forEach(m => draftMap.removeLayer(m));
            if (draftRouteLineBg) draftMap.removeLayer(draftRouteLineBg);
            if (draftRouteLine) draftMap.removeLayer(draftRouteLine);
            if (window.myDraftMarker && draftMap) draftMap.removeLayer(window.myDraftMarker);
            window.myDraftMarker = null;
            draftMarkers = [];

            let latlngs = [];

            // Add a global 'My Location' indicator.
            // We strictly rely on real-time GPS locks.
            if (window.myLat && window.myLng) {
                latlngs.push([window.myLat, window.myLng]);
                const myIconHtml = `
                <div class="gps-user-marker-icon">
                    <div class="gps-user-marker-wave"></div>
                    <div class="gps-user-marker-inner">
                        <i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>
                    </div>
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
                const activeRoute = (activeRouteEl ? activeRouteEl.innerText.trim() : (window.currentRouteType === 'alternate' ? 'Alternate' : 'Recommended'));
                let routeColor = '#38bdf8'; // Recommended = Blue
                let shadowColor = '#0f172a';

                if (activeRoute === 'Alternate') { routeColor = '#ffb703'; shadowColor = '#78350f'; } // Vibrant Gold/Yellow
                if (activeRoute === 'Scenic Route') { routeColor = '#ff3b30'; shadowColor = '#450a0a'; }

                // We strictly use the original authenticated coordinates from the database.
                let fetchLatLngs = [...latlngs];

                const coordString = fetchLatLngs.map(ll => `${ll[1]},${ll[0]}`).join(';');

                if (shouldFitBounds) {
                    draftMap.fitBounds(L.latLngBounds(latlngs), { padding: [30, 30] });
                }

                // Execute the real-time dynamic scan using the OSRM engine
                let osrmService = 'route';
                let osrmQuery = '?overview=full&geometries=geojson&alternatives=true';

                // To provide the absolute fastest way for Recommended routes with 3+ waypoints, use TSP solver
                if (activeRoute === 'Recommended' && fetchLatLngs.length >= 3) {
                    osrmService = 'trip';
                    osrmQuery = '?overview=full&geometries=geojson&source=first&destination=last&roundtrip=false';
                }

                const osrmUrl = `https://router.project-osrm.org/${osrmService}/v1/driving/${coordString}${osrmQuery}`;

                fetch(osrmUrl)
                    .then(res => res.json())
                    .then(async data => {
                        let routeData = null;

                        if (data.code === 'Ok') {
                            if (activeRoute === 'Alternate') {
                                // If OSRM returned multiple driving routes, select the distinct alternate corridor!
                                if (data.routes && data.routes.length > 1) {
                                    routeData = data.routes[1];
                                } else if (data.routes && data.routes.length > 0) {
                                    // If single highway, query secondary road network (bike/secondary profile) for distinct route
                                    try {
                                        const altRes = await fetch(`https://router.project-osrm.org/route/v1/bike/${coordString}?overview=full&geometries=geojson`);
                                        const altData = await altRes.json();
                                        if (altData.code === 'Ok' && altData.routes && altData.routes[0]) {
                                            routeData = altData.routes[0];
                                        } else {
                                            routeData = data.routes[0];
                                        }
                                    } catch (e) {
                                        routeData = data.routes[0];
                                    }
                                }
                            } else {
                                routeData = data.routes ? data.routes[0] : (data.trips ? data.trips[0] : null);
                            }
                        }

                        if (routeData) {
                            if (draftRouteLineBg) draftMap.removeLayer(draftRouteLineBg);
                            if (draftRouteLine) draftMap.removeLayer(draftRouteLine);

                            const geojson = routeData.geometry;

                            draftRouteLineBg = L.geoJSON(geojson, {
                                style: { color: shadowColor, weight: 6, opacity: 0.35, lineJoin: 'round', lineCap: 'round' }
                            }).addTo(draftMap);

                            draftRouteLine = L.geoJSON(geojson, {
                                style: {
                                    color: routeColor,
                                    weight: 4,
                                    opacity: 1,
                                    lineJoin: 'round',
                                    lineCap: 'round',
                                    dashArray: activeRoute === 'Alternate' ? '7, 5' : null
                                }
                            }).addTo(draftMap);

                            let distanceKm = routeData.distance / 1000;
                            let durationMin = routeData.duration / 60;

                            if (activeRoute === 'Scenic Route') {
                                durationMin *= 1.5;
                                distanceKm *= 1.4;
                            } else if (activeRoute === 'Alternate') {
                                durationMin *= 1.25;
                                distanceKm *= 1.15;
                            }

                            // OSRM assumes perfect driving at the speed limit.
                            // Apply a dynamic realism multiplier:
                            let baseMultiplier = 1.6;
                            if (distanceKm <= 3) baseMultiplier = 2.5;
                            else if (distanceKm <= 7) baseMultiplier = 2.0;

                            durationMin *= baseMultiplier;

                            // Traffic Buffer Logic
                            const currentHour = new Date().getHours();
                            const isRushHour = (currentHour >= 7 && currentHour <= 9) || (currentHour >= 16 && currentHour <= 19);
                            const warningDiv = document.getElementById('draft-traffic-warning');

                            if (isRushHour) {
                                durationMin *= 1.4;
                                if (warningDiv) {
                                    warningDiv.style.display = 'block';
                                    warningDiv.style.color = '#FF9500';
                                    warningDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Heavy traffic expected at this hour';
                                }
                            } else {
                                if (warningDiv) {
                                    warningDiv.style.display = 'block';
                                    warningDiv.style.color = 'rgba(255,255,255,0.4)';
                                    warningDiv.innerHTML = activeRoute === 'Alternate' ? '<i class="fa-solid fa-route" style="color:#ffb703; margin-right:4px;"></i> Alternate Road Corridor' : 'Typical traffic conditions';
                                }
                            }

                            // Store globally so the Save modal can use it for fuel cost
                            window._draftDistanceKm = distanceKm;

                            window.setTxt('draft-map-dist', distanceKm.toFixed(1) + ' km');
                            window.setTxt('draft-map-time', Math.round(durationMin) + ' min');

                            // Dynamically scale line width on zoom
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
                    })
                    .catch(err => console.error("OSRM Routing failed.", err));
            } else if (latlngs.length === 1) {
                // Only 1 spot: no route to draw, but we MUST set the map view so it renders!
                if (shouldFitBounds) {
                    draftMap.setView(latlngs[0], 15);
                }

                // Reset stats
                window.setTxt('draft-map-dist', '0 km');
                window.setTxt('draft-map-time', '0 min');
                const warnEl = document.getElementById('draft-traffic-warning');
                if (warnEl) warnEl.style.display = 'none';
            }
        };

        window.setRouteType = function (type, btn) {
            // Cancel any pending render timeout to prevent it from overriding user interaction
            if (window._renderTimeout) {
                clearTimeout(window._renderTimeout);
                window._renderTimeout = null;
            }
            window.currentRouteType = type;
            document.querySelectorAll('.btn-route-type').forEach(el => el.classList.remove('active'));
            if (btn) btn.classList.add('active');
            else {
                const targetBtn = document.getElementById(type === 'alternate' ? 'btn-route-alt' : 'btn-route-rec');
                if (targetBtn) targetBtn.classList.add('active');
            }

            const draft = window.getEffectiveDraft();

            // Save user's viewport before re-rendering so we can restore it afterwards
            var _savedCenter = null, _savedZoom = null;
            if (typeof draftMap !== 'undefined' && draftMap) {
                _savedCenter = draftMap.getCenter();
                _savedZoom = draftMap.getZoom();
            }

            // Re-render map with the new route sequence
            window.initDraftMap(draft, false);

            // Restore the user's exact viewport immediately after re-rendering
            if (_savedCenter !== null && _savedZoom !== null && typeof draftMap !== 'undefined' && draftMap) {
                draftMap.setView(_savedCenter, _savedZoom, { animate: false });
            }

            // Re-render the timeline stops to reflect the new stop sequence!
            window.renderItinerary(true);

            if (typeof showToast === 'function') {
                showToast(type === 'alternate' ? "Switched to Alternate Route — Stop sequence reversed" : "Switched to Recommended Route — Optimal stop sequence");
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

            if (window.vehicleTypes && Array.isArray(window.vehicleTypes) && window.vehicleTypes.length > 0) {
                const isPrivate = type === 'private';
                const privateNames = ['Car', 'Motorcycle', 'Van', 'TAXI'];
                const matchedTypes = window.vehicleTypes.filter(vt => {
                    const isPriv = privateNames.includes(vt.name);
                    return isPrivate ? isPriv : !isPriv;
                });

                const iconMap = {
                    'TAXI': 'fa-taxi',
                    'UVE': 'fa-van-shuttle',
                    'PUB_Regular': 'fa-bus',
                    'PUB_Aircon': 'fa-bus-simple',
                    'MPUJ': 'fa-bus',
                    'TPUJ': 'fa-truck-pickup',
                    'Tricycle': 'fa-motorcycle',
                    'Car': 'fa-car',
                    'Motorcycle': 'fa-motorcycle',
                    'Van': 'fa-shuttle-van'
                };

                const keyMap = {
                    'TAXI': 'taxi',
                    'UVE': 'mini_bus',
                    'PUB_Regular': 'private_bus',
                    'PUB_Aircon': 'private_bus',
                    'MPUJ': 'jeepney',
                    'TPUJ': 'jeepney',
                    'Tricycle': 'tricycle',
                    'Car': 'own_car',
                    'Motorcycle': 'motorcycle',
                    'Van': 'mini_bus'
                };

                const labelMap = {
                    'TAXI': 'Taxi',
                    'UVE': 'UV Express',
                    'PUB_Regular': 'Regular Bus',
                    'PUB_Aircon': 'Aircon Bus',
                    'MPUJ': 'Modern Jeepney',
                    'TPUJ': 'Traditional Jeepney',
                    'Tricycle': 'Tricycle',
                    'Car': 'Own Car',
                    'Motorcycle': 'Motorcycle',
                    'Van': 'Van'
                };

                optionsList = matchedTypes.map(vt => ({
                    val: keyMap[vt.name] || vt.name.toLowerCase(),
                    name: labelMap[vt.name] || vt.name,
                    icon: iconMap[vt.name] || 'fa-car'
                }));
            } else if (window.vehicleData && Array.isArray(window.vehicleData) && window.vehicleData.length > 0) {
                const isPrivate = type === 'private';
                const privateNames = ['Private Car', 'Taxi', 'Motorcycle', 'Van'];
                optionsList = window.vehicleData.filter(v => {
                    const isPriv = privateNames.includes(v.name);
                    return isPrivate ? isPriv : !isPriv;
                }).map(v => {
                    const keyMap = {
                        'Tricycle': 'tricycle', 'Jeepney': 'jeepney', 'Bus': 'private_bus',
                        'Van': 'mini_bus', 'Taxi': 'taxi', 'Motorcycle': 'motorcycle', 'Private Car': 'own_car'
                    };
                    return {
                        val: keyMap[v.name] || v.name.toLowerCase().replace(/\s+/g, '_'),
                        name: v.name,
                        icon: v.icon || 'fa-car'
                    };
                });
            }

            if (optionsList.length === 0) {
                if (type === 'private') {
                    optionsList = [
                        { val: 'own_car', name: 'Own Car', icon: 'fa-car' },
                        { val: 'taxi', name: 'Taxi', icon: 'fa-taxi' },
                        { val: 'van', name: 'Van', icon: 'fa-shuttle-van' },
                        { val: 'motorcycle', name: 'Motorcycle', icon: 'fa-motorcycle' }
                    ];
                } else {
                    optionsList = [
                        { val: 'private_bus', name: 'Bus', icon: 'fa-bus' },
                        { val: 'mini_bus', name: 'Mini Bus / UVE', icon: 'fa-van-shuttle' },
                        { val: 'lutrampco', name: 'LUTRAMPCO', icon: 'fa-bus-simple' },
                        { val: 'jeepney', name: 'Jeepney', icon: 'fa-truck-pickup' },
                        { val: 'tricycle', name: 'Tricycle', icon: 'fa-motorcycle' }
                    ];
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

            const currentSelected = (document.getElementById('trip-transport').value || '').split(',').filter(Boolean);

            let html = '';
            unique.forEach(opt => {
                const isActive = currentSelected.includes(opt.val) ? 'active' : '';
                html += `
            <div class="transport-option ${isActive}" data-val="${opt.val}" onclick="window.selectTransportMode(this)">
                <i class="fa-solid ${opt.icon}"></i>
                <span>${opt.name}</span>
            </div>`;
            });

            slider.innerHTML = html;
        };

        window.setTransportType = function (type) {
            const btnPublic = document.getElementById('btn-trans-public');
            const btnPrivate = document.getElementById('btn-trans-private');

            btnPublic.classList.remove('active');
            btnPrivate.classList.remove('active');

            btnPublic.style.background = 'transparent';
            btnPublic.style.color = 'rgba(255,255,255,0.7)';
            btnPublic.style.boxShadow = 'none';
            btnPrivate.style.background = 'transparent';
            btnPrivate.style.color = 'rgba(255,255,255,0.7)';
            btnPrivate.style.boxShadow = 'none';

            const activeBtn = document.getElementById('btn-trans-' + type);
            if (activeBtn) {
                activeBtn.classList.add('active');
                activeBtn.style.background = 'white';
                activeBtn.style.color = '#333';
                activeBtn.style.boxShadow = '0 2px 8px rgba(0,0,0,0.05)';
            }

            const wrapper = document.getElementById('transport-slider-wrapper');
            if (wrapper.style.display === 'none') {
                wrapper.style.display = 'block';
                wrapper.style.animation = 'smoothReveal 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            }

            // Dynamically render vehicle options from Railway DB!
            window.renderRailwayVehicleOptions(type);

            const fuelPanel = document.getElementById('own-car-fuel-panel');
            const isCarSelected = (document.getElementById('trip-transport').value || '').includes('own_car');
            if (fuelPanel) {
                if (isCarSelected) {
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