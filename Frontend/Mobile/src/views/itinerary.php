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
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: rgba(255,255,255,0.6);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-route-type.active {
        background: rgba(56,189,248,0.15);
        border-color: rgba(56,189,248,0.3);
        color: #38bdf8;
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .donut-chart {
        background: conic-gradient(
            #38bdf8 0% 33%,
            #34c759 33% 66%,
            #ff9500 66% 100%
        );
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
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding-top: 16px;" class="stagger-1">
        <h2 style="margin:0; font-size:22px; font-weight:800; letter-spacing:-0.5px;">Draft Plan</h2>
        <div style="display:flex; align-items:center; gap: 8px;">
            <!-- Saved Trips Button (Small) -->
            <button onclick="navigateTo('saved_trips')" style="background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; font-weight:700; height: 32px; padding: 0 14px; border-radius:20px; font-size:12px; cursor:pointer; display:flex; align-items:center; box-sizing: border-box; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <i class="fa-solid fa-bookmark" style="margin-right:6px;"></i> Saved Trips
            </button>
            <span style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.2); color:#38bdf8; height: 32px; padding: 0 14px; border-radius:20px; font-size:12px; font-weight:700; display:flex; align-items:center; box-sizing: border-box;">
                <span id="itinerary-count" style="margin-right:4px;">0</span> Places
            </span>
        </div>
    </div>

    <!-- Map Visualization Container -->
    <div id="draft-map-wrapper" style="display:none; margin-top:16px; margin-bottom:20px;" class="stagger-2">
        <!-- Toggles -->
        <div style="display:flex; gap:8px; margin-bottom:12px; overflow-x:auto; padding-bottom:4px;" class="hide-scrollbar">
            <button class="btn-route-type active" id="btn-route-rec" onclick="setRouteType('recommended', this)">Recommended</button>
            <button class="btn-route-type" id="btn-route-alt" onclick="setRouteType('alternate', this)">Alternate</button>
        </div>

        <!-- The Map -->
        <div style="height: 180px; width:100%; border-radius: 16px; overflow: hidden; border:1px solid rgba(255,255,255,0.1); position:relative; background:#f1f5f9;">
            <div id="itinerary-map" style="width:100%; height:100%;"></div>
            
            <!-- Locate Me Floating Button -->
            <button onclick="window.routeToMyLocation()" style="position: absolute; bottom: 10px; right: 10px; width: 36px; height: 36px; background: rgba(30, 58, 138, 0.9); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 1000; cursor: pointer; transition: transform 0.2s, background 0.2s;" onmousedown="this.style.transform='scale(0.9)';" onmouseup="this.style.transform='scale(1)';" onmouseleave="this.style.transform='scale(1)';">
                <i class="fa-solid fa-crosshairs"></i>
            </button>
        </div>
        
        <!-- Map Route Stats -->
        <div style="display:flex; justify-content:space-around; align-items:center; margin-top:12px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:16px; padding:14px;">
            <div style="color:white; font-size:14px; font-weight:600;">
                <i class="fa-solid fa-route" style="color:#38bdf8; margin-right:6px; font-size:16px;"></i> <span id="draft-map-dist">0 km</span>
            </div>
            <div style="width:1px; height:20px; background:rgba(255,255,255,0.1);"></div>
            <div style="color:white; font-size:14px; font-weight:600; display:flex; flex-direction:column; align-items:center;">
                <div><i class="fa-solid fa-clock" style="color:#34d399; margin-right:6px; font-size:16px;"></i> <span id="draft-map-time">0 min</span></div>
                <div id="draft-traffic-warning" style="display:none; margin-top:2px; font-size:10px; font-weight:500;"></div>
            </div>
        </div>
    </div>
    
    <!-- Dynamic Timeline Container -->
    <div class="timeline stagger-2" id="itinerary-timeline" style="margin-bottom: 20px;">
        <!-- Rendered via JS -->
    </div>
    
    <!-- Save Itinerary Action -->
    <button class="btn-primary" id="btn-save-itinerary" style="display:none; width:100%; padding:16px; border-radius:16px; font-weight:700; font-size:16px; margin-bottom:40px; box-shadow:0 8px 20px rgba(0,0,0,0.1);" onclick="openSaveModal()">
        <i class="fa-solid fa-cloud-arrow-up" style="margin-right:8px;"></i> Save Draft Plan
    </button>
    
    <!-- Empty State -->
    <div id="itinerary-empty-state" style="text-align: center;">
        <i class="fa-solid fa-route" style="font-size: 54px; margin-bottom: 16px; color:var(--primary-color);"></i>
        <h3 style="margin-bottom:8px;">No plans yet</h3>
        <p style="font-size:14px; text-align:center; color:rgba(255,255,255,0.7); max-width:80%; margin:0 auto;">Go to the Map and tap "Add to Itinerary"<br>on a place to start building your trip!</p>
        <button class="btn-primary" style="margin-top: 20px; width:auto; padding:12px 24px;" onclick="navigateTo('map')">Open Map</button>
    </div>
    

    
</div>

<!-- Save Trip Modal -->
<div id="save-trip-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:90%; max-width:400px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">Save Your Trip</h3>
        <p style="font-size:14px; color:#666; margin-bottom:20px;">Give your awesome adventure a name so you can pull it up later!</p>
        
        <input type="text" id="trip-title" placeholder="e.g. La Union Weekend" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid #ddd; margin-bottom:16px; font-family:inherit; font-size:16px;">
        
        <label style="font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:4px; display:block;">Trip Date (Optional)</label>
        <input type="date" id="trip-date" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; margin-bottom:16px; font-family:inherit; font-size:16px;">

        <label style="font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:8px; display:block;">Transport Type</label>
        <div style="display:flex; gap:8px; margin-bottom:16px; background:rgba(255,255,255,0.05); padding:4px; border-radius:12px; border:1px solid rgba(255,255,255,0.05);">
            <button class="btn-transport-toggle" id="btn-trans-public" onclick="window.setTransportType('public')" style="flex:1; padding:10px; border-radius:10px; border:none; background:transparent; font-size:13px; font-weight:600; color:rgba(255,255,255,0.7); transition:0.2s; cursor:pointer;">Public</button>
            <button class="btn-transport-toggle" id="btn-trans-private" onclick="window.setTransportType('private')" style="flex:1; padding:10px; border-radius:10px; border:none; background:transparent; font-size:13px; font-weight:600; color:rgba(255,255,255,0.7); transition:0.2s; cursor:pointer;">Private</button>
        </div>

        <div id="transport-slider-wrapper" style="display:none;">
            <label id="mode-transport-label" style="font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:4px; display:block;">Mode of Transport</label>
        
        <style>
        @keyframes smoothReveal {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-smooth-reveal {
            animation: smoothReveal 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        #transport-slider::-webkit-scrollbar { display: none; }
        .transport-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 90px;
            padding: 14px 8px;
            border-radius: 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            cursor: pointer;
            transition: 0.2s;
            color: rgba(255,255,255,0.7);
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
        <div id="transport-slider" style="display:flex; overflow-x:auto; gap:12px; padding-bottom:8px; margin-bottom:16px; scrollbar-width:none; -ms-overflow-style:none;">
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
        window.selectTransportMode = function(el) {
            const val = el.getAttribute('data-val');
            const isPrivate = (val === 'own_car' || val === 'taxi');

            if (isPrivate) {
                // Private: single-select only
                document.querySelectorAll('.transport-option').forEach(opt => {
                    const oVal = opt.getAttribute('data-val');
                    if (oVal === 'own_car' || oVal === 'taxi') {
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
        <div id="own-car-fuel-panel" style="max-height:0; overflow:hidden; opacity:0; transition: max-height 0.4s ease, opacity 0.35s ease; margin-bottom:0;">
            <div style="background:rgba(56,189,248,0.06); border:1px solid rgba(56,189,248,0.15); border-radius:12px; padding:12px 14px; margin-bottom:12px;">
                <p style="font-size:11px; color:#38bdf8; font-weight:700; margin:0 0 10px 0; text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="fa-solid fa-gas-pump" style="margin-right:6px;"></i>Fuel Cost Calculator
                </p>
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label style="font-size:10px; color:white; font-weight:600; display:block; margin-bottom:4px;">Fuel Price (₱/L)</label>
                        <input type="number" id="fuel-price" value="65" min="1" oninput="window.calculateModalBudget()" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; font-family:inherit; font-size:14px;">
                    </div>
                    <div style="flex:1;">
                        <label style="font-size:10px; color:white; font-weight:600; display:block; margin-bottom:4px;">Fuel Efficiency (km/L)</label>
                        <input type="number" id="fuel-efficiency" value="12" min="1" oninput="window.calculateModalBudget()" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; font-family:inherit; font-size:14px;">
                    </div>
                </div>
                <p id="fuel-distance-hint" style="font-size:10px; color:white; margin:8px 0 0 0;">Route distance will be used for calculation.</p>
            </div>
        </div>

        <div style="position:relative; margin-bottom:12px;">
            <span style="position:absolute; left:16px; top:14px; color:white; font-weight:600;">₱</span>
            <input type="tel" id="trip-budget" placeholder="Set a budget (optional)" oninput="this.value=this.value.replace(/\D/g,'');if(this.value.length>5)this.value=this.value.slice(0,5);window.calculateModalBudget()" style="width:100%; padding:12px 16px 12px 32px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; font-family:inherit; font-size:14px;">
        </div>

        <div id="save-budget-details" style="display:none; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.05); padding:16px; border-radius:12px; margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:16px;">
                <div id="modal-donut-wrapper" style="position:relative; flex-shrink:0; width:0; margin-right:0; height:60px; overflow:hidden; display:flex; align-items:center; justify-content:center; opacity:0; transform:scale(0.7); transition: width 0.45s cubic-bezier(0.34,1.56,0.64,1), margin-right 0.45s cubic-bezier(0.34,1.56,0.64,1), opacity 0.4s ease, transform 0.45s cubic-bezier(0.34,1.56,0.64,1);">
                    <div class="donut-chart" id="modal-budget-donut" style="position:absolute; left:0; top:0; border-radius:50%; width:60px; height:60px; transform:scaleX(-1);"></div>
                    <span id="modal-donut-pct" style="position:relative; font-size:10px; font-weight:800; color:white; white-space:nowrap;"></span>
                </div>
                <div style="flex:1; display:flex; flex-direction:column; gap:4px;">
                    <div style="display:flex; justify-content:space-between; align-items:baseline;">
                        <span style="font-size:11px; color:white; font-weight:600; text-transform:uppercase;">Estimated Cost</span>
                        <h4 style="margin:0; font-size:16px; color:white; font-weight:800;" id="save-estimated-cost">₱0.00</h4>
                    </div>
                    <div id="save-budget-remaining-row" style="display:none; justify-content:space-between; align-items:baseline;">
                        <span style="font-size:11px; font-weight:600; text-transform:uppercase;" id="save-budget-remaining-label">Remaining</span>
                        <span style="font-size:13px; font-weight:700;" id="save-budget-remaining-val">—</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display:flex; gap:12px;">
            <button class="btn-primary" style="flex:1; background:transparent; border:1px solid rgba(255,255,255,0.2); color:white;" onclick="closeSaveModal()">Cancel</button>
            <button class="btn-primary" style="flex:1;" onclick="submitItinerary()" id="btn-submit-trip">Save Trip</button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirm-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:85%; max-width:360px; box-shadow:0 20px 40px rgba(0,0,0,0.2); text-align:center;">
        <div style="width:48px; height:48px; border-radius:50%; background:rgba(245,158,11,0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b; font-size:22px;"></i>
        </div>
        <h3 style="margin:0 0 8px; color:#f8fafc; font-size:18px;">Missing Details</h3>
        <p id="confirm-modal-msg" style="margin:0 0 24px; color:rgba(148,163,184,0.9); font-size:14px; line-height:1.5;"></p>
        <div style="display:flex; gap:12px;">
            <button class="btn-primary" id="btn-confirm-cancel" style="flex:1; background:transparent; border:1px solid rgba(255,255,255,0.2); color:white;">Cancel</button>
            <button class="btn-primary" id="btn-confirm-ok" style="flex:1; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none;">Save Anyway</button>
        </div>
    </div>
</div>
<script>
(function() {
    var backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';

    // ---- Custom confirm modal (replaces native confirm) ----
    window.showConfirmModal = function(msg) {
        // Prevent stacking multiple confirm modals
        var existing = document.getElementById('confirm-modal');
        if (existing && existing.style.display === 'flex') {
            return Promise.resolve(false);
        }
        return new Promise(function(resolve) {
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

    // Fetch fare rates from DB
    fetch(backendUrl + '/api/public/fares', {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { window.fareData = d.fares || {}; }).catch(e => console.error("Fares fetch error:", e));

    window.getFareFromMatrix = function(vehicleType, distanceKm) {
        if (!window.fareData) return null;
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
        if (key === 'own_car' || key === 'taxi') return null;
        const fareEntry = window.fareData[key];
        if (!fareEntry || !fareEntry.rates || fareEntry.rates.length === 0) return null;
        const rates = fareEntry.rates;
        let match = null;
        for (let i = rates.length - 1; i >= 0; i--) {
            if (parseFloat(rates[i].distance_km) <= distanceKm) {
                match = rates[i];
                break;
            }
        }
        if (!match) match = rates[0];
        return parseFloat(match.regular_fare);
    };

    window.renderItinerary = function() {
        const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
        const timeline = document.getElementById('itinerary-timeline');
        const emptyState = document.getElementById('itinerary-empty-state');
        const fab = document.getElementById('btn-save-itinerary');
        const mapWrapper = document.getElementById('draft-map-wrapper');
        
        document.getElementById('itinerary-count').innerText = draft.length;

        if (draft.length === 0) {
            timeline.innerHTML = '';
            emptyState.style.display = 'block';
            fab.style.display = 'none';
            if (mapWrapper) mapWrapper.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        fab.style.display = 'flex';
        if (mapWrapper) mapWrapper.style.display = 'block';
        
        let html = '';

        draft.forEach((place, index) => {
            const hour = 9 + Math.floor(((index + 1) * 90) / 60);
            const min = ((index + 1) * 90) % 60;
            const timeStr = `${hour > 12 ? hour - 12 : hour}:${min === 0 ? '00' : min} ${hour >= 12 ? 'PM' : 'AM'}`;

            html += `
            <div class="timeline-item" draggable="true" data-index="${index}" data-id="${place.id}" style="animation-delay: ${(index + 1) * 0.1}s">
                <div class="timeline-dot"></div>
                <div class="swipe-container" style="position:relative; overflow:hidden; border-radius:16px;">
                    <div class="swipe-delete-bg" style="position:absolute; top:0; right:0; bottom:0; width:80px; background:#ef4444; border-radius:0 16px 16px 0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:700; gap:4px; transform:translateX(100%);"><i class="fa-solid fa-trash"></i> Delete</div>
                    <div class="swipe-content" style="position:relative; z-index:1; transition:transform 0.2s ease; background:rgba(255,255,255,0.04); border-radius:16px; padding:14px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="time-label" style="flex:1;">Stop ${index + 1} &bull; Approx ${timeStr}</span>
                            <i class="fa-solid fa-grip-vertical" style="color:rgba(148,163,184,0.3); font-size:14px; cursor:grab; touch-action:none;"></i>
                        </div>
                        <h3 class="place-name">${place.name}</h3>
                        <p style="font-size:12px; color:rgba(255,255,255,0.6); margin: 4px 0 8px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                            ${place.description && place.description !== 'null' ? place.description : (place.category && place.category !== 'null' ? place.category : 'A beautiful destination to explore in La Union.')}
                        </p>
                        <div class="place-details">
                            <i class="fa-solid fa-location-dot"></i>
                            <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                ${place.location && place.location !== 'null' ? place.location : (place.address && place.address !== 'null' ? place.address : 'San Fernando, La Union')}
                            </span>
                        </div>
                        ${place.selected_vehicles && place.selected_vehicles.length > 0 ? `<div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:8px;">${place.selected_vehicles.map(v => `<span style="padding:2px 8px; border-radius:100px; font-size:10px; font-weight:700; background:rgba(56,189,248,0.1); color:#38bdf8; border:1px solid rgba(56,189,248,0.2);"><i class="fa-solid fa-car" style="margin-right:3px;font-size:9px;"></i>${v}</span>`).join('')}</div>` : ''}
                    </div>
                </div>
            </div>`;
        });
        
        timeline.innerHTML = html;
        setupDragAndDrop(draft);
        
        window._renderTimeout = setTimeout(() => {
            if (window.initDraftMap) window.initDraftMap(draft);
        }, 100);
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
                let d = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                const [removed] = d.splice(dragIndex, 1);
                d.splice(targetIndex, 0, removed);
                localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(d));
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
                        let d = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                        const [removed] = d.splice(dragIndex, 1);
                        d.splice(targetIndex, 0, removed);
                        localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(d));
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
                content.style.borderRadius = translate > 5 ? '0' : '';
                if (bg) bg.style.transform = `translateX(${80 - translate}px)`;
            }, { passive: true });

            content.addEventListener('touchend', (e) => {
                content.style.transition = 'transform 0.2s ease';
                if (bg) bg.style.transition = 'transform 0.2s ease';
                const diff = startX - currentX;
                if (diff > 60 && isSwiping) {
                    const id = item.dataset.id;
                    if (id) window.removeItineraryItem(id);
                } else {
                    content.style.transform = '';
                    if (bg) bg.style.transform = 'translateX(100%)';
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

    window.removeItineraryItem = function(id) {
        let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
        draft = draft.filter(item => item.id.toString() !== id.toString());
        localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(draft));
        window.renderItinerary();
    };

    window.showMyLocation = function() {
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
    
    window.routeToMyLocation = function() {
        const mapContainer = document.getElementById('draft-map-wrapper');
        if (mapContainer) {
            if (mapContainer.style.display === 'none' || mapContainer.style.display === '') {
                mapContainer.style.display = 'block';
                if (typeof draftMap !== 'undefined' && draftMap) draftMap.invalidateSize();
            }
            mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Only fly if we actually have a real GPS lock!
            if (typeof draftMap !== 'undefined' && draftMap && window.myLat && window.myLng) {
                draftMap.flyTo([window.myLat, window.myLng], 16, { animate: true, duration: 1.5 });
            }
        }
    };
    
    // Real-time dynamic GPS listener
    let _gpsUpdateTimeout = null;
    document.addEventListener('gpsUpdated', function(e) {
        if (typeof draftMap !== 'undefined' && draftMap) {
            if (window.myDraftMarker) {
                // Smoothly animate the marker to the new physical coordinate
                window.myDraftMarker.setLatLng([e.detail.lat, e.detail.lng]);
            } else {
                // First time GPS lock achieved, dynamically inject the marker!
                const myIconHtml = `
                    <div style="width: 32px; height: 32px; background-color: #FFFFFF; border: 2px solid #ff9500; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ff9500; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                        <i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>
                    </div>
                `;
                const myIcon = L.divIcon({
                    className: 'custom-leaflet-marker',
                    html: myIconHtml,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                window.myDraftMarker = L.marker([e.detail.lat, e.detail.lng], {icon: myIcon}).addTo(draftMap);
                if (typeof draftMarkers !== 'undefined') draftMarkers.push(window.myDraftMarker);
            }
            
            // Recalculate the route to connect the blue line to the new physical GPS location
            // Debounce it to prevent spamming the OSRM routing server on every micro-movement
            clearTimeout(_gpsUpdateTimeout);
            _gpsUpdateTimeout = setTimeout(() => {
                const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
                if (window.initDraftMap) window.initDraftMap(draft, false);
            }, 2000);
        }
    });

    window.routeToPlace = function(id) {
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
                let lat = place.lat || 16.6159;
                let lng = place.lng || 120.3167;
                draftMap.flyTo([parseFloat(lat), parseFloat(lng)], 16, { animate: true, duration: 1.5 });
            }
        }
    };

    // ---- Donut animation state ----
    let _donutAnimFrame = null;
    let _currentDonutPct = 0;

    function animateDonut(targetPct, color) {
        if (_donutAnimFrame) cancelAnimationFrame(_donutAnimFrame);
        const donutEl  = document.getElementById('modal-budget-donut');
        const pctEl    = document.getElementById('modal-donut-pct');
        const startPct = _currentDonutPct;
        const startTime = performance.now();
        const duration  = 700; // ms

        function step(now) {
            const elapsed  = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic
            const ease = 1 - Math.pow(1 - progress, 3);
            const pct  = startPct + (targetPct - startPct) * ease;
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

    window.calculateModalBudget = function() {
        const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
        if (draft.length === 0) return;

        const transport = document.getElementById('trip-transport').value;
        const detailsDiv = document.getElementById('save-budget-details');
        
        if (!transport) {
            detailsDiv.style.display = 'none';
            detailsDiv.classList.remove('animate-smooth-reveal');
            return;
        }

        // Transport cost — sum across all selected modes
        let transCost = 0;
        const modes = transport ? transport.split(',').filter(Boolean) : [];
        const distKm = window._draftDistanceKm || 0;
        modes.forEach(mode => {
            if (mode === 'own_car') {
                const fuelPrice    = parseFloat(document.getElementById('fuel-price')?.value) || 65;
                const fuelEffic    = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
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
            else {
                if (mode === 'private_bus') {
                    if (distKm > 0) {
                        transCost += Math.max(2000, Math.round(distKm * 50));
                    } else {
                        transCost += 2000;
                    }
                } else {
                    const dbFare = window.getFareFromMatrix(mode, distKm);
                    if (dbFare !== null) {
                        transCost += Math.round(dbFare);
                    } else {
                        if (mode === 'taxi')        transCost += 250;
                        else if (mode === 'mini_bus')    transCost += 500;
                        else if (mode === 'lutrampco')   transCost += 50;
                        else if (mode === 'jeepney')     transCost += 30;
                    }
                }
            }
        });

        const estimatedCost = transCost;

        // Show the card
        if (detailsDiv.style.display !== 'block') {
            detailsDiv.style.display = 'block';
            detailsDiv.classList.add('animate-smooth-reveal');
        }

        const costEl = document.getElementById('save-estimated-cost');
        costEl.textContent = '₱' + estimatedCost.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

        const budgetInput = document.getElementById('trip-budget').value;
        const budget = parseFloat(budgetInput);
        const remainingRow   = document.getElementById('save-budget-remaining-row');
        const remainingLabel = document.getElementById('save-budget-remaining-label');
        const remainingVal   = document.getElementById('save-budget-remaining-val');
        const donutWrapper   = document.getElementById('modal-donut-wrapper');

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
        const remaining  = budget - estimatedCost;

        let fillColor = '#FF3B30'; // Red — budget covers little
        let remainingLabelText  = 'Need more budget';
        let remainingLabelColor = '#FF3B30';

        if (percentage >= 100) {
            fillColor = '#34C759';
            remainingLabelText  = "You're good to go!";
            remainingLabelColor = '#34C759';
        } else if (percentage >= 80) {
            fillColor = '#FF9500';
            remainingLabelText  = 'Almost there';
            remainingLabelColor = '#FF9500';
        }

        // Animate donut fill smoothly
        animateDonut(percentage, fillColor);

        // Update remaining row
        if (remainingRow) {
            remainingRow.style.display = 'flex';
            remainingLabel.textContent = remainingLabelText;
            remainingLabel.style.color = remainingLabelColor;
            remainingVal.style.color   = remainingLabelColor;
            const absRemaining = Math.abs(remaining);
            remainingVal.textContent = (remaining < 0 ? '-' : '') + '₱' + absRemaining.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        }
    };

    window.openSaveModal = function() {
        const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');

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

        document.getElementById('save-trip-modal').style.display = 'flex';
        window.calculateModalBudget();

        // Hide bottom nav while modal is open (prevents keyboard pushing it up)
        const bottomNav = document.getElementById('bottom-navigation');
        if (bottomNav) bottomNav.classList.add('nav-hidden');
    };

    window.closeSaveModal = function() {
        document.getElementById('save-trip-modal').style.display = 'none';

        // Restore bottom nav
        const bottomNav = document.getElementById('bottom-navigation');
        if (bottomNav) bottomNav.classList.remove('nav-hidden');
        document.getElementById('trip-title').value = '';
        document.getElementById('trip-date').value = '';
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

    window.submitItinerary = async function() {
        const title = document.getElementById('trip-title').value.trim();
        const date = document.getElementById('trip-date').value;
        const budgetStr = document.getElementById('trip-budget').value;
        const budget = budgetStr ? parseFloat(budgetStr) : null;
        if (!title) return showToast("Please enter a trip name");

        const draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
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
        const distKm = window._draftDistanceKm || 0;
        modes.forEach(mode => {
            if (mode === 'own_car') {
                const fuelPrice    = parseFloat(document.getElementById('fuel-price')?.value) || 65;
                const fuelEffic    = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
                const litersNeeded = distKm / fuelEffic;
                let cost = Math.ceil(litersNeeded * fuelPrice);
                if (distKm <= 0) cost = Math.ceil((1 * fuelPrice) / fuelEffic);
                totalTransCost += cost;
            }
            else {
                if (mode === 'private_bus') {
                    if (distKm > 0) {
                        totalTransCost += Math.max(2000, Math.round(distKm * 50));
                    } else {
                        totalTransCost += 2000;
                    }
                } else {
                    const dbFare = window.getFareFromMatrix(mode, distKm);
                    if (dbFare !== null) {
                        totalTransCost += Math.round(dbFare);
                    } else {
                        if (mode === 'taxi')        totalTransCost += 250;
                        else if (mode === 'mini_bus')    totalTransCost += 500;
                        else if (mode === 'lutrampco')   totalTransCost += 50;
                        else if (mode === 'jeepney')     totalTransCost += 30;
                    }
                }
            }
        });

        const transCostPerPlace = transport ? (totalTransCost / draft.length) : null;

        const destinations = draft.map(place => place.id);

        try {
            const activeRouteType = document.querySelector('.btn-route-type.active')?.innerText || 'Recommended';
            
            const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('intan_elyu_token')
                },
                body: JSON.stringify({ title: title, trip_date: date, budget: budget, destinations: destinations, route_type: activeRouteType, transport_mode: transport })
            });

            const data = await response.json();

            if (response.ok) {
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

    window.initDraftMap = function(draft, shouldFitBounds = true) {
        if (draft.length === 0) return;
        
        // Cancel any pending render timeout to prevent stale fitBounds calls
        if (window._renderTimeout) {
            clearTimeout(window._renderTimeout);
            window._renderTimeout = null;
        }
        
        if (!draftMap) {
            draftMap = L.map('itinerary-map', {
                attributionControl: false,
                zoomControl: true,
                scrollWheelZoom: true,
                dragging: true,
                touchZoom: true,
                doubleClickZoom: true,
                boxZoom: true,
                keyboard: true
            });
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
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
        draftMarkers = [];
        
        let latlngs = [];
        
        // Add a global 'My Location' indicator.
        // We DO NOT use fallback coordinates. We strictly rely on real-time GPS locks.
        if (window.myLat && window.myLng) {
            latlngs.push([window.myLat, window.myLng]);
            const myIconHtml = `
                <div style="width: 32px; height: 32px; background-color: #FFFFFF; border: 2px solid #ff9500; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ff9500; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                    <i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>
                </div>
            `;
            const myIcon = L.divIcon({
                className: 'custom-leaflet-marker',
                html: myIconHtml,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            window.myDraftMarker = L.marker([window.myLat, window.myLng], {icon: myIcon}).addTo(draftMap);
            draftMarkers.push(window.myDraftMarker);
        }

        draft.forEach((place, index) => {
            // Skip place if it has no valid coordinates in the database
            if (!place.lat || !place.lng) return;
            
            let lat = parseFloat(place.lat);
            let lng = parseFloat(place.lng);
            
            const ll = [lat, lng];
            latlngs.push(ll);
            
            const stopIconHtml = `
                <div style="width: 32px; height: 32px; background-color: #FFFFFF; border: 2px solid #38bdf8; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #38bdf8; box-shadow: 0 4px 8px rgba(0,0,0,0.15); cursor: pointer; transition: transform 0.2s;" onmouseenter="this.style.transform='scale(1.2)'" onmouseleave="this.style.transform='scale(1)'">
                    <span style="font-size:14px; font-weight:800;">${index+1}</span>
                </div>
            `;
            const stopIcon = L.divIcon({
                className: 'custom-leaflet-marker',
                html: stopIconHtml,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            const marker = L.marker(ll, {icon: stopIcon}).addTo(draftMap);
            draftMarkers.push(marker);
        });
        
        if (latlngs.length > 1) {
            const activeRoute = document.querySelector('.btn-route-type.active').innerText;
            let routeColor = '#38bdf8'; // Recommended = Blue
            let shadowColor = '#0f172a';
            
            if (activeRoute === 'Alternate') { routeColor = '#ffcc00'; shadowColor = '#78350f'; } // Yellow
            if (activeRoute === 'Scenic Route') { routeColor = '#ff3b30'; shadowColor = '#450a0a'; }
            
            // We strictly use the original authenticated coordinates from the database.
            // NO fake/mathematical waypoints are injected to prevent dead-end U-turns.
            let fetchLatLngs = [...latlngs];
            
            // We default to the strict 'driving' profile to guarantee the generated route 
            // rigorously obeys vehicle traffic laws (one-way streets, vehicle widths, etc.)
            let osrmProfile = 'driving';
            
            // The user requested "mini complicated routes" specifically for the Alternate route.
            // By switching OSRM to the 'walking' profile, the algorithm aggressively routes through 
            // tiny alleyways, side-streets, and complex pedestrian pathways, generating exactly 
            // the intricate zig-zag patterns they requested in their screenshot!
            if (activeRoute === 'Alternate') {
                osrmProfile = 'walking';
            }
            
            // The routes are already mathematically distinct because setRouteType() 
            // dynamically reorganizes the stop sequence for Alternate and Scenic routes!
            
            const coordString = fetchLatLngs.map(ll => `${ll[1]},${ll[0]}`).join(';');
            
            if (shouldFitBounds) {
                draftMap.fitBounds(L.latLngBounds(latlngs), {padding: [30, 30]});
            }
            
            // Execute the real-time dynamic scan using the OSRM engine
            let osrmService = 'route';
            let osrmQuery = '?overview=full&geometries=geojson';
            
            // To provide the absolute "fastest way to get there" for Recommended routes, 
            // we upgrade from simple routing to OSRM's Trip API (TSP Solver).
            // This mathematically optimizes the sequence of the intermediate stops for maximum speed!
            if (activeRoute === 'Recommended' && fetchLatLngs.length >= 3) {
                osrmService = 'trip';
                osrmQuery += '&source=first&destination=last&roundtrip=false';
            }
            
            fetch(`https://router.project-osrm.org/${osrmService}/v1/${osrmProfile}/${coordString}${osrmQuery}`)
                .then(res => res.json())
                .then(data => {
                    const routeData = data.routes ? data.routes[0] : (data.trips ? data.trips[0] : null);
                    
                    if (data.code === 'Ok' && routeData) {
                        if (draftRouteLineBg) draftMap.removeLayer(draftRouteLineBg);
                        if (draftRouteLine) draftMap.removeLayer(draftRouteLine);
                            
                            const geojson = routeData.geometry;
                            
                            draftRouteLineBg = L.geoJSON(geojson, {
                                style: { color: shadowColor, weight: 6, opacity: 0.3, lineJoin: 'round', lineCap: 'round' }
                            }).addTo(draftMap);
                            
                            draftRouteLine = L.geoJSON(geojson, {
                                style: { color: routeColor, weight: 4, opacity: 1, lineJoin: 'round', lineCap: 'round' }
                            }).addTo(draftMap);
                            
                            let distanceKm = routeData.distance / 1000;
                            let durationMin = routeData.duration / 60;
                            
                            if (osrmProfile === 'cycling') {
                                durationMin = distanceKm * 2.4;
                            } else if (osrmProfile === 'walking') {
                                durationMin = distanceKm * 3.5; // Mathematically override 5-hour pedestrian times back to slow car times
                            }
    
                            if (activeRoute === 'Scenic Route') {
                                durationMin *= 1.5;
                                distanceKm *= 1.4;
                            } else if (activeRoute === 'Alternate') {
                                durationMin *= 1.2;
                                distanceKm *= 1.15;
                            }
    
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
                        const warningDiv = document.getElementById('draft-traffic-warning');

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

                        // Store globally so the Save modal can use it for fuel cost
                        window._draftDistanceKm = distanceKm;
                        
                        document.getElementById('draft-map-dist').textContent = distanceKm.toFixed(1) + ' km';
                        document.getElementById('draft-map-time').textContent = Math.round(durationMin) + ' min';
                        
                        // Dynamically scale line width on zoom (like MapLibre)
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
                        updateRouteScale(); // set initial weight based on fitBounds zoom
                    }
                })
                .catch(err => console.error("OSRM Routing failed.", err));
        } else if (latlngs.length === 1) {
            // Only 1 spot: no route to draw, but we MUST set the map view so it renders!
            if (shouldFitBounds) {
                draftMap.setView(latlngs[0], 15);
            }
            
            // Reset stats
            document.getElementById('draft-map-dist').innerText = '0 km';
            document.getElementById('draft-map-time').innerText = '0 min';
            document.getElementById('draft-traffic-warning').style.display = 'none';
        }
    };
    
    window.setRouteType = function(type, btn) {
        // Cancel any pending render timeout to prevent it from overriding user interaction
        if (window._renderTimeout) {
            clearTimeout(window._renderTimeout);
            window._renderTimeout = null;
        }
        document.querySelectorAll('.btn-route-type').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        
        // Get original draft without mutating localStorage
        let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
        
        if (draft.length > 1 && type === 'alternate') {
            draft.reverse();
        }
        
        // Save user's viewport before re-rendering so we can restore it afterwards,
        // preventing any side effect (flyTo animation, invalidateSize, etc.) from changing the view.
        var _savedCenter = null, _savedZoom = null;
        if (typeof draftMap !== 'undefined' && draftMap) {
            _savedCenter = draftMap.getCenter();
            _savedZoom = draftMap.getZoom();
        }
        
        // Re-render map with the new route sequence, but DO NOT reset the user's zoom/pan position!
        window.initDraftMap(draft, false);
        
        // Restore the user's exact viewport immediately after re-rendering
        if (_savedCenter !== null && _savedZoom !== null && typeof draftMap !== 'undefined' && draftMap) {
            draftMap.setView(_savedCenter, _savedZoom, { animate: false });
        }
    };
    
    window.updateDonutChart = function(elementId, transport, food, activities) {
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
    
    window.updateDraftBudget = function(draft) {
        let actCost = 0, foodCost = 0, transCost = 0;
        draft.forEach(item => {
            actCost += parseFloat(item.entrance_fee) || 50;
            foodCost += parseFloat(item.avg_food_cost) || 150;
            transCost += parseFloat(item.avg_transport_cost) || 30;
        });
        
        const total = actCost + foodCost + transCost;
        document.getElementById('main-budget-total').textContent = '₱' + total.toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('main-cost-trans').textContent = '₱' + transCost;
        document.getElementById('main-cost-food').textContent = '₱' + foodCost;
        document.getElementById('main-cost-act').textContent = '₱' + actCost;
        
        window.updateDonutChart('main-budget-donut', transCost, foodCost, actCost);
    };

    window.setTransportType = function(type) {
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
        
        const isPrivate = type === 'private';
        const options = document.querySelectorAll('.transport-option');
        let selectedHidden = false;
        
        options.forEach(opt => {
            const val = opt.getAttribute('data-val');
            const optIsPrivate = (val === 'own_car' || val === 'taxi');
            
            if (isPrivate === optIsPrivate) {
                opt.style.display = 'flex';
            } else {
                opt.style.display = 'none';
                if (opt.classList.contains('active')) {
                    opt.classList.remove('active');
                    selectedHidden = true;
                }
            }
        });
        
        if (selectedHidden) {
            document.getElementById('trip-transport').value = '';
            
            const fuelPanel = document.getElementById('own-car-fuel-panel');
            if (fuelPanel) {
                fuelPanel.style.maxHeight = '0';
                fuelPanel.style.opacity = '0';
            }
            
            if (window.calculateModalBudget) window.calculateModalBudget();
        }
    };

    // Global GPS Tracker Listener
    document.addEventListener('gpsUpdated', (e) => {
        const { lat, lng } = e.detail;
        
        // Update the 'My Location' tracker variable globally used by initDraftMap
        window.myLat = lat;
        window.myLng = lng;
        
        if (window.draftMap && window.myIcon) {
            // Find and update the existing blue dot marker if it exists in draftMarkers
            let myMarker = window.draftMarkers ? window.draftMarkers.find(m => m.options.icon === window.myIcon) : null;
            if (myMarker) {
                myMarker.setLatLng([lat, lng]);
            }
        }
    });

})();
</script>



