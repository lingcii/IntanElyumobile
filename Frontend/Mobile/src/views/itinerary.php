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
            <button class="btn-route-type" id="btn-route-sce" onclick="setRouteType('scenic', this)">Scenic Route</button>
        </div>

        <!-- The Map -->
        <div style="height: 180px; width:100%; border-radius: 16px; overflow: hidden; border:1px solid rgba(255,255,255,0.1); position:relative; background:#f1f5f9;">
            <div id="itinerary-map" style="width:100%; height:100%;"></div>
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
            document.querySelectorAll('.transport-option').forEach(opt => opt.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('trip-transport').value = el.getAttribute('data-val');

            // Show/hide fuel inputs panel
            const fuelPanel = document.getElementById('own-car-fuel-panel');
            if (fuelPanel) {
                if (el.getAttribute('data-val') === 'own_car') {
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
            <input type="number" id="trip-budget" placeholder="Set a budget (optional)" oninput="window.calculateModalBudget()" style="width:100%; padding:12px 16px 12px 32px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:white; font-family:inherit; font-size:14px;">
        </div>

        <div id="save-budget-details" style="display:none; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.05); padding:16px; border-radius:12px; margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:16px;">
                <div id="modal-donut-wrapper" style="position:relative; flex-shrink:0; width:0; margin-right:0; height:60px; overflow:hidden; display:flex; align-items:center; justify-content:center; opacity:0; transform:scale(0.7); transition: width 0.45s cubic-bezier(0.34,1.56,0.64,1), margin-right 0.45s cubic-bezier(0.34,1.56,0.64,1), opacity 0.4s ease, transform 0.45s cubic-bezier(0.34,1.56,0.64,1);">
                    <div class="donut-chart" id="modal-budget-donut" style="position:absolute; left:0; top:0; border-radius:50%; width:60px; height:60px;"></div>
                    <span id="modal-donut-pct" style="position:relative; font-size:10px; font-weight:800; color:white; white-space:nowrap;"></span>
                </div>
                <div style="flex:1; display:flex; flex-direction:column; gap:4px;">
                    <div style="display:flex; justify-content:space-between; align-items:baseline;">
                        <span style="font-size:11px; color:white; font-weight:600; text-transform:uppercase;">Est. Cost</span>
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



<script>
(function() {
    const backendUrl = "http://localhost:8000";

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
        
        // Add 'My Location' as the starting point toggle
        html += `
        <div id="add-my-loc-wrapper" style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="window.showMyLocation()" style="width:100%; padding: 12px; background: rgba(255,149,0,0.1); color: #ff9500; border: 1px dashed rgba(255,149,0,0.5); border-radius: 12px; font-weight: 700; font-size: 14px;">
                <i class="fa-solid fa-location-crosshairs" style="margin-right: 6px;"></i> Set My Starting Location
            </button>
        </div>
        
        <div id="my-location-container" style="display: none; opacity: 0; transform: translateY(-20px); transition: opacity 0.4s ease, transform 0.4s ease;">
            <div class="timeline-item">
                <div class="timeline-dot" style="background:#ff9500; border-color:#ff9500; box-shadow:0 0 0 4px rgba(255,149,0,0.2);"></div>
                <div class="timeline-content" onclick="const r = this.querySelector('.card-action-row'); r.style.display = (r.style.display === 'none' ? 'flex' : 'none');" style="cursor:pointer;">
                <span class="time-label" style="color:#ff9500;">Starting Point &bull; Right Now</span>
                <h3 class="place-name">My Location</h3>
                <div class="place-details">
                    <i class="fa-solid fa-location-crosshairs" style="color:#ff9500;"></i>
                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Current GPS Location</span>
                </div>
                <div class="card-action-row" style="margin-top: 10px; display: none; gap: 8px;">
                    <button class="btn-card-directions" onclick="event.stopPropagation(); window.routeToMyLocation()" style="width: 100%; border: 1px solid rgba(255,149,0,0.3); color: #ff9500; background: rgba(255,149,0,0.1);">
                        <i class="fa-solid fa-crosshairs" style="margin-right:4px;"></i>Locate Me
                    </button>
                </div>
            </div>
        </div>
        </div>`;

        draft.forEach((place, index) => {
            // Calculate a mock time just for visuals (starting at 9 AM, 1.5 hours per stop)
            const hour = 9 + Math.floor(((index + 1) * 90) / 60); // Offset by 1 for My Location
            const min = ((index + 1) * 90) % 60;
            const timeStr = `${hour > 12 ? hour - 12 : hour}:${min === 0 ? '00' : min} ${hour >= 12 ? 'PM' : 'AM'}`;

            html += `
            <div class="timeline-item" style="animation-delay: ${(index + 1) * 0.1}s">
                <div class="timeline-dot"></div>
                <div class="timeline-content" onclick="const r = this.querySelector('.card-action-row'); r.style.display = (r.style.display === 'none' ? 'flex' : 'none');" style="cursor:pointer;">
                    <span class="time-label">Stop ${index + 1} &bull; Approx ${timeStr}</span>
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
                    <div class="card-action-row" style="display: none; margin-top: 10px; gap: 8px;">
                        <button class="btn-card-remove" onclick="event.stopPropagation(); window.removeItineraryItem('${place.id}')">
                            <i class="fa-solid fa-trash" style="margin-right:4px;"></i>Remove
                        </button>
                        <button class="btn-card-directions" onclick="event.stopPropagation(); window.routeToPlace('${place.id}')">
                            <i class="fa-solid fa-diamond-turn-right" style="margin-right:4px;"></i>Directions
                        </button>
                    </div>
                </div>
            </div>`;
        });
        
        timeline.innerHTML = html;
        
        // Initialize/Update map
        setTimeout(() => {
            if (window.initDraftMap) window.initDraftMap(draft);
        }, 100);
    };

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
            
            if (typeof draftMap !== 'undefined' && draftMap) {
                draftMap.flyTo([16.6120, 120.3150], 16, { animate: true, duration: 1.5 });
            }
        }
    };

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

        // Transport cost — computed per mode
        let transCost = 0;
        if (transport === 'own_car') {
            // Fuel-based calculation: (distance / km_per_liter) * price_per_liter
            const distKm       = window._draftDistanceKm || 0;
            const fuelPrice    = parseFloat(document.getElementById('fuel-price')?.value) || 65;
            const fuelEffic    = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
            const litersNeeded = distKm / fuelEffic;
            transCost = Math.ceil(litersNeeded * fuelPrice);

            // Update the hint with actual distance used
            const hint = document.getElementById('fuel-distance-hint');
            if (hint) {
                if (distKm > 0) {
                    hint.textContent = `Route: ${distKm.toFixed(1)} km • ~${litersNeeded.toFixed(2)} L needed`;
                    hint.style.color = 'rgba(255,255,255,0.5)';
                } else {
                    hint.textContent = 'Open the Map first to get an accurate route distance.';
                    hint.style.color = '#FF9500';
                    transCost = Math.ceil((1 * fuelPrice) / fuelEffic); // fallback 1L estimate
                }
            }
        }
        else if (transport === 'taxi')        transCost = 250;
        else if (transport === 'private_bus') transCost = 800;
        else if (transport === 'mini_bus')    transCost = 500;
        else if (transport === 'lutrampco')   transCost = 50;
        else if (transport === 'jeepney')     transCost = 30;

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

        const percentage = Math.min((estimatedCost / budget) * 100, 100);
        const remaining  = budget - estimatedCost;

        let fillColor = '#34C759'; // Green — good
        let remainingLabelText  = "You're good to go!";
        let remainingLabelColor = '#34C759';

        if (percentage >= 100) {
            fillColor = '#FF3B30';
            remainingLabelText  = 'Need more budget';
            remainingLabelColor = '#FF3B30';
        } else if (percentage >= 80) {
            fillColor = '#FF9500';
            remainingLabelText  = 'Running low';
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
        document.getElementById('save-trip-modal').style.display = 'flex';
        window.calculateModalBudget();
    };

    window.closeSaveModal = function() {
        document.getElementById('save-trip-modal').style.display = 'none';
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

        const btn = document.getElementById('btn-submit-trip');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const transport = document.getElementById('trip-transport').value;
        let totalTransCost = 0;
        if (transport === 'own_car') {
            const distKm       = window._draftDistanceKm || 0;
            const fuelPrice    = parseFloat(document.getElementById('fuel-price')?.value) || 65;
            const fuelEffic    = parseFloat(document.getElementById('fuel-efficiency')?.value) || 12;
            const litersNeeded = distKm / fuelEffic;
            totalTransCost = Math.ceil(litersNeeded * fuelPrice);
            if (distKm <= 0) totalTransCost = Math.ceil((1 * fuelPrice) / fuelEffic);
        }
        else if (transport === 'taxi')        totalTransCost = 250;
        else if (transport === 'private_bus') totalTransCost = 800;
        else if (transport === 'mini_bus')    totalTransCost = 500;
        else if (transport === 'lutrampco')   totalTransCost = 50;
        else if (transport === 'jeepney')     totalTransCost = 30;

        const transCostPerPlace = transport ? (totalTransCost / draft.length) : null;

        const destinations = draft.map(place => place.id);

        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('intan_elyu_token')
                },
                body: JSON.stringify({ title: title, trip_date: date, budget: budget, destinations: destinations })
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

    window.initDraftMap = function(draft) {
        if (draft.length === 0) return;
        
        if (!draftMap) {
            draftMap = L.map('itinerary-map', {zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false, touchZoom: false, attributionControl: false});
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
            }).addTo(draftMap);
        }
        
        // Force Leaflet to recalculate size since container was display:none
        setTimeout(() => draftMap.invalidateSize(), 50);
        
        // clear old markers and routes
        draftMarkers.forEach(m => draftMap.removeLayer(m));
        if (draftRouteLineBg) draftMap.removeLayer(draftRouteLineBg);
        if (draftRouteLine) draftMap.removeLayer(draftRouteLine);
        draftMarkers = [];
        
        let latlngs = [];
        
        // Add "My Location" mock starting point
        const myLat = 16.6120;
        const myLng = 120.3150;
        latlngs.push([myLat, myLng]);
        
        const myIconHtml = `
            <div style="width: 32px; height: 32px; background-color: #FFFFFF; border: 2px solid #ff9500; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ff9500; box-shadow: 0 4px 8px rgba(0,0,0,0.15); cursor: pointer; transition: transform 0.2s;" onmouseenter="this.style.transform='scale(1.2)'" onmouseleave="this.style.transform='scale(1)'">
                <i class="fa-solid fa-location-crosshairs" style="font-size:14px;"></i>
            </div>
        `;
        const myIcon = L.divIcon({
            className: 'custom-leaflet-marker',
            html: myIconHtml,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        const myMarker = L.marker([myLat, myLng], {icon: myIcon}).addTo(draftMap);
        draftMarkers.push(myMarker);

        draft.forEach((place, index) => {
            // Check if place has lat/lng, otherwise mock for demo
            let lat = place.lat || (16.6159 + (Math.random()*0.1 - 0.05));
            let lng = place.lng || (120.3167 + (Math.random()*0.1 - 0.05));
            
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
            if (activeRoute === 'Scenic Route') { routeColor = '#ff3b30'; shadowColor = '#450a0a'; } // Red
            
            // Build coordinate string for OSRM: lng,lat;lng,lat
            const coordString = latlngs.map(ll => `${ll[1]},${ll[0]}`).join(';');
            
            draftMap.fitBounds(L.latLngBounds(latlngs), {padding: [30, 30]});
            
            fetch(`https://router.project-osrm.org/route/v1/driving/${coordString}?overview=full&geometries=geojson`)
                .then(res => res.json())
                .then(data => {
                    if (data.code === 'Ok' && data.routes.length > 0) {
                        // Remove fallback lines
                        if (draftRouteLineBg) draftMap.removeLayer(draftRouteLineBg);
                        if (draftRouteLine) draftMap.removeLayer(draftRouteLine);
                        
                        const geojson = data.routes[0].geometry;
                        
                        draftRouteLineBg = L.geoJSON(geojson, {
                            style: { color: shadowColor, weight: 6, opacity: 0.3, lineJoin: 'round', lineCap: 'round' }
                        }).addTo(draftMap);
                        
                        draftRouteLine = L.geoJSON(geojson, {
                            style: { color: routeColor, weight: 4, opacity: 1, lineJoin: 'round', lineCap: 'round' }
                        }).addTo(draftMap);
                        
                        // Update realistic distance and time
                        const distanceKm = data.routes[0].distance / 1000;
                        let durationMin = data.routes[0].duration / 60;

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
                .catch(err => console.error("OSRM Routing failed, keeping straight lines.", err));
        }
    };
    
    window.setRouteType = function(type, btn) {
        document.querySelectorAll('.btn-route-type').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        
        // Get original draft without mutating localStorage
        let draft = JSON.parse(localStorage.getItem('intan_elyu_draft_itinerary') || '[]');
        
        if (draft.length > 1) {
            if (type === 'alternate') {
                const first = draft.shift();
                draft.push(first);
            } else if (type === 'scenic') {
                draft.reverse();
            }
        }
        
        // Re-render map with the new route sequence
        window.initDraftMap(draft);
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
        
        const options = document.querySelectorAll('.transport-option');
        let selectedHidden = false;
        
        options.forEach(opt => {
            const val = opt.getAttribute('data-val');
            const isPrivate = (val === 'own_car' || val === 'taxi');
            
            if ((type === 'private' && isPrivate) || (type === 'public' && !isPrivate)) {
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

})();
</script>


