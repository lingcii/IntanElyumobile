<?php
/**
 * Trip Guide Walkthrough Modal Component
 * Interactive step-by-step tour matching the requested UI design:
 * - Numbered hotspot pins on screen preview
 * - Selected item explanation card
 * - Interactive "ALL ITEMS ON THIS SCREEN" list
 * - Previous / Next navigation with pagination dots
 */
?>
<div id="trip-guide-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10, 25, 60, 0.6); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:999999; justify-content:center; align-items:center; padding:12px; box-sizing:border-box;"
    onclick="if(event.target===this) window.closeTripGuideModal();">
    
    <div class="trip-guide-card-container"
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:820px; max-height:92dvh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(10, 25, 60, 0.45) !important; overflow:hidden; animation:popInTripGuide 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards; font-family:-apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', Roboto, sans-serif;">
        
        <!-- Header Banner (Clean White with Dark Blue Badge) -->
        <div style="background:#ffffff; padding:16px 20px; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f1f5f9;">
            <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                <div style="width:38px; height:38px; border-radius:12px; background:#1e3a8a; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 12px rgba(30,58,138,0.28);">
                    <i class="fa-solid fa-question" style="color:#ffffff; font-size:16px; font-weight:900;"></i>
                </div>
                <div style="min-width:0;">
                    <h3 id="tg-header-title" style="margin:0; color:#0f172a; font-size:17px; font-weight:800; letter-spacing:-0.3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        How to Add & Complete a Trip
                    </h3>
                    <p id="tg-header-subtitle" style="margin:2px 0 0; font-size:12px; color:#64748b; font-weight:500;">
                        Step-by-step INTAN ELYU interactive guide
                    </p>
                </div>
            </div>
            <button type="button" onclick="window.closeTripGuideModal()"
                style="background:#f1f5f9 !important; border:none !important; outline:none !important; color:#64748b !important; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; margin-left:12px; transition:all 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="font-size:15px; color:#64748b !important;"></i>
            </button>
        </div>

        <!-- Walkthrough Content Area (Split: Screen Preview on Left, Item Details & List on Right) -->
        <div class="trip-guide-body hide-scrollbar" style="background:#ffffff; padding:18px 20px; flex:1; overflow-y:auto; display:flex; gap:18px; box-sizing:border-box;">
            
            <!-- Left Side: Interactive Screen Mockup / Screenshot Preview with Pins -->
            <div class="trip-guide-preview-col" style="flex:1; min-width:280px; display:flex; flex-direction:column;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span id="tg-screen-badge" style="font-size:11px; font-weight:800; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.6px; background:#e0f2fe; padding:3px 10px; border-radius:100px;">
                        Screen 1 of 4
                    </span>
                    <span style="font-size:11px; color:#94a3b8; font-weight:600;">
                        <i class="fa-solid fa-hand-pointer" style="margin-right:4px;"></i> Tap pins to inspect
                    </span>
                </div>

                <div id="tg-preview-frame" style="position:relative; width:100%; min-height:260px; max-height:420px; height:100%; border-radius:18px; border:1.5px solid #e2e8f0; background:#f8fafc; overflow:hidden; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 18px rgba(0,0,0,0.04);">
                    <!-- Dynamic Preview Image / Graphic -->
                    <img id="tg-preview-img" src="" alt="Screen Preview" style="width:100%; height:100%; object-fit:cover; display:none;">
                    
                    <!-- Illustrated Fallback / Dynamic SVG Mockup when screenshot not loaded -->
                    <div id="tg-preview-svg-mockup" style="width:100%; height:100%; display:flex; flex-direction:column; padding:16px; box-sizing:border-box; background:linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);">
                        <!-- Rendered via JS -->
                    </div>

                    <!-- Overlay Pin Hotspots Layer -->
                    <div id="tg-pins-layer" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;">
                        <!-- Injected via JS -->
                    </div>
                </div>
            </div>

            <!-- Right Side: Selected Item Card & All Items On This Screen -->
            <div class="trip-guide-info-col" style="flex:1.15; min-width:280px; display:flex; flex-direction:column; gap:14px;">
                
                <!-- Top: Selected Item Box -->
                <div id="tg-selected-card" style="background:#ffffff; border:1.5px solid #0284c7; border-radius:16px; padding:14px 16px; box-shadow:0 4px 16px rgba(2, 132, 199, 0.08); transition:all 0.2s ease;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                        <span id="tg-selected-number" style="width:24px; height:24px; border-radius:50%; background:#1e3a8a; color:#ffffff; font-size:12px; font-weight:900; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                            1
                        </span>
                        <div>
                            <span style="font-size:10px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.7px; display:block;">
                                SELECTED ITEM
                            </span>
                            <h4 id="tg-selected-title" style="margin:0; font-size:15px; font-weight:800; color:#0f172a; line-height:1.25;">
                                Item Title
                            </h4>
                        </div>
                    </div>
                    <p id="tg-selected-desc" style="margin:0; font-size:12.5px; color:#475569; line-height:1.5; font-weight:500;">
                        Description text explaining this item on the screen.
                    </p>
                </div>

                <!-- Bottom: All Items On This Screen -->
                <div style="display:flex; flex-direction:column; flex:1;">
                    <span style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.7px; margin-bottom:8px; display:block;">
                        ALL ITEMS ON THIS SCREEN
                    </span>
                    
                    <div id="tg-items-list" style="display:flex; flex-direction:column; gap:8px; overflow-y:auto; max-height:220px; padding-right:2px;" class="hide-scrollbar">
                        <!-- Injected via JS -->
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Footer Navigation (Previous, Pagination Dots, Next) -->
        <div style="background:#ffffff; padding:12px 20px; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; border-top:1px solid #f1f5f9; gap:12px;">
            <button type="button" id="tg-btn-prev" onclick="window.navigateTripGuide(-1)"
                style="background:#f8fafc; border:1px solid #e2e8f0; outline:none; color:#475569; font-weight:700; font-size:12.5px; padding:9px 18px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.15s ease;">
                <i class="fa-solid fa-arrow-left" style="font-size:11px;"></i> Previous
            </button>

            <!-- Center Pagination Dots -->
            <div id="tg-dots-container" style="display:flex; align-items:center; gap:6px;">
                <!-- Injected via JS -->
            </div>

            <button type="button" id="tg-btn-next" onclick="window.navigateTripGuide(1)"
                style="background:#1e3a8a; border:none; outline:none; color:#ffffff; font-weight:800; font-size:12.5px; padding:9px 22px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 3px 10px rgba(30,58,138,0.28); transition:all 0.15s ease;">
                <span>Next</span> <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
            </button>
        </div>

    </div>
</div>

<style>
@keyframes popInTripGuide {
    0% {
        opacity: 0;
        transform: scale(0.94) translateY(16px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Responsive adjustment for portrait mobile devices */
@media (max-width: 679px) {
    .trip-guide-body {
        flex-direction: column !important;
        gap: 14px !important;
        padding: 14px 16px !important;
    }
    .trip-guide-preview-col {
        min-width: 100% !important;
    }
    #tg-preview-frame {
        height: 180px !important;
        min-height: 180px !important;
        max-height: 180px !important;
    }
    .trip-guide-info-col {
        min-width: 100% !important;
    }
    #tg-items-list {
        max-height: 180px !important;
    }
}

/* Floating Trip Guide Button (Above profile navigation button) */
.floating-trip-guide-btn {
    position: fixed !important;
    right: clamp(18px, calc((100vw - 448px) / 2 + 18px), calc(50vw - 224px + 18px)) !important;
    bottom: calc(max(18px, env(safe-area-inset-bottom, 18px)) + 74px) !important;
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important;
    color: #ffffff !important;
    border: 2px solid #ffffff !important;
    box-shadow: 0 4px 16px rgba(30, 58, 138, 0.42), 0 2px 6px rgba(0,0,0,0.12) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 17px !important;
    font-weight: 900 !important;
    cursor: pointer !important;
    z-index: 9400 !important;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease !important;
    -webkit-tap-highlight-color: transparent !important;
}

.floating-trip-guide-btn:active {
    transform: scale(0.92) !important;
}

/* Numbered Hotspot Pin */
.tg-pin {
    position: absolute;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #1e3a8a;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.32);
    border: 2px solid #ffffff;
    cursor: pointer;
    pointer-events: auto;
    transform: translate(-50%, -50%);
    transition: all 0.2s ease;
    user-select: none;
}

.tg-pin.active {
    background: #0284c7;
    transform: translate(-50%, -50%) scale(1.22);
    box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.35), 0 4px 12px rgba(0,0,0,0.35);
    z-index: 10;
}

/* List Item Card */
.tg-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.18s ease;
    user-select: none;
}

.tg-list-item:hover,
.tg-list-item:active {
    background: #f1f5f9;
}

.tg-list-item.active {
    background: #f0f9ff !important;
    border-color: #0284c7 !important;
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.12);
}
</style>

<script>
(function() {
    // Walkthrough Tour Screens Data
    // User can customize screenshot URLs, items, and pin coordinates (x%, y%)
    window.TRIP_GUIDE_SCREENS = [
        {
            title: "How to Add & Plan a Trip",
            subtitle: "Explore La Union spots and add them to your draft",
            screenshot: "", // Screenshot image path will be set once provided
            mockupType: "map",
            items: [
                {
                    number: 1,
                    title: "Tourist Spot Pins",
                    desc: "Browse the interactive Map of La Union. Tap on any spot marker or attraction card to view full details, photos, and amenities.",
                    pinX: "32%",
                    pinY: "38%"
                },
                {
                    number: 2,
                    title: "Spot Bottom Sheet",
                    desc: "When a spot is selected, its preview sheet slides up from the bottom displaying verified ratings, operating hours, and location.",
                    pinX: "50%",
                    pinY: "68%"
                },
                {
                    number: 3,
                    title: "+ Add to Trip Button",
                    desc: "Tap '+ Add to Trip' to immediately queue this attraction into your personalized Draft Plan. You can add as many stops as you like!",
                    pinX: "80%",
                    pinY: "72%"
                }
            ]
        },
        {
            title: "Customize Your Itinerary",
            subtitle: "Optimize route, select vehicles, and set budget",
            screenshot: "",
            mockupType: "itinerary",
            items: [
                {
                    number: 1,
                    title: "Timeline & Reordering",
                    desc: "Review your selected destinations. Drag or use quick controls to reorder stops for the most efficient travel sequence.",
                    pinX: "48%",
                    pinY: "35%"
                },
                {
                    number: 2,
                    title: "Route Type (Recommended / Alt)",
                    desc: "Switch between Recommended and Alternative routes to view distance in kilometers, estimated travel time, and traffic updates.",
                    pinX: "50%",
                    pinY: "20%"
                },
                {
                    number: 3,
                    title: "Transport & Vehicle Mode",
                    desc: "Select your ride (Car, Motorcycle, Van, Tricycle). The app calculates estimated fuel costs and fare breakdowns automatically.",
                    pinX: "50%",
                    pinY: "62%"
                },
                {
                    number: 4,
                    title: "Save Draft Plan Button",
                    desc: "Tap 'Save Draft Plan' to give your trip a custom name, date, and budget. Once saved, your trip is safely stored in Saved Trips!",
                    pinX: "50%",
                    pinY: "86%"
                }
            ]
        },
        {
            title: "Saved Trips & Starting",
            subtitle: "Access saved plans and launch live navigation",
            screenshot: "",
            mockupType: "saved_trips",
            items: [
                {
                    number: 1,
                    title: "Saved Trip Cards",
                    desc: "All your created itineraries are listed here. View your scheduled trip date, estimated budget, and companion headcount.",
                    pinX: "50%",
                    pinY: "34%"
                },
                {
                    number: 2,
                    title: "Start Trip Button",
                    desc: "Ready to explore? Tap 'Start Trip' to launch the interactive turn-by-turn navigation map with real-time GPS tracking.",
                    pinX: "32%",
                    pinY: "72%"
                },
                {
                    number: 3,
                    title: "Edit & Share Controls",
                    desc: "Easily update trip details, add more stops, or download an offline copy of your itinerary anytime before or during your journey.",
                    pinX: "80%",
                    pinY: "72%"
                }
            ]
        },
        {
            title: "Check-in & Complete Trip",
            subtitle: "Verify visits, upload photos, and earn points",
            screenshot: "",
            mockupType: "trip_map",
            items: [
                {
                    number: 1,
                    title: "Live GPS Route Map",
                    desc: "Follow the live navigation route with arrival radius detection as you travel across La Union attractions.",
                    pinX: "50%",
                    pinY: "32%"
                },
                {
                    number: 2,
                    title: "GPS Check-In Button",
                    desc: "Upon arriving at each spot, tap 'Check In'. If within range, your visit is automatically verified on your itinerary.",
                    pinX: "36%",
                    pinY: "68%"
                },
                {
                    number: 3,
                    title: "Photo Proof Upload",
                    desc: "No GPS signal? Snap a selfie or upload photos at the site. Queued photos sync automatically once you're back online.",
                    pinX: "68%",
                    pinY: "68%"
                },
                {
                    number: 4,
                    title: "Complete Trip & Earn Rewards",
                    desc: "After visiting your stops, tap 'Complete Trip' (checkered flag). Rate your destinations to earn Explorer Points, Badges, and Vouchers!",
                    pinX: "50%",
                    pinY: "88%"
                }
            ]
        }
    ];

    let currentScreenIdx = 0;
    let selectedItemIdx = 0;

    function renderScreenMockup(screen) {
        const mockupEl = document.getElementById('tg-preview-svg-mockup');
        const imgEl = document.getElementById('tg-preview-img');
        if (!mockupEl || !imgEl) return;

        if (screen.screenshot && screen.screenshot.trim() !== '') {
            imgEl.src = screen.screenshot;
            imgEl.style.display = 'block';
            mockupEl.style.display = 'none';
        } else {
            imgEl.style.display = 'none';
            mockupEl.style.display = 'flex';
            
            // Render styled vector mockups matching each section
            if (screen.mockupType === 'map') {
                mockupEl.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span style="font-size:11px; font-weight:800; color:#1e3a8a;">INTAN ELYU MAP</span>
                        <span style="font-size:9.5px; background:#ffffff; padding:2px 8px; border-radius:10px; font-weight:700; color:#0284c7;">GPS ACTIVE</span>
                    </div>
                    <div style="flex:1; border-radius:12px; background:#cadce4; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-map" style="font-size:42px; color:rgba(30,58,138,0.2);"></i>
                        <div style="position:absolute; bottom:8px; left:8px; right:8px; background:#ffffff; border-radius:10px; padding:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-size:11px; font-weight:800; color:#0f172a;">San Juan Beach</div>
                                <div style="font-size:9px; color:#64748b;">Surfing Capital of the North</div>
                            </div>
                            <span style="font-size:10px; background:#1e3a8a; color:#fff; padding:4px 8px; border-radius:6px; font-weight:800;">+ Add</span>
                        </div>
                    </div>
                `;
            } else if (screen.mockupType === 'itinerary') {
                mockupEl.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span style="font-size:11px; font-weight:800; color:#1e3a8a;">DRAFT PLAN</span>
                        <span style="font-size:9.5px; background:#dcfce7; padding:2px 8px; border-radius:10px; font-weight:700; color:#16a34a;">3 PLACES</span>
                    </div>
                    <div style="flex:1; border-radius:12px; background:#ffffff; padding:10px; display:flex; flex-direction:column; gap:6px; justify-content:space-between;">
                        <div style="display:flex; gap:6px; align-items:center; background:#f1f5f9; padding:6px 8px; border-radius:8px;">
                            <span style="width:16px; height:16px; border-radius:50%; background:#1e3a8a; color:#fff; font-size:9px; display:flex; align-items:center; justify-content:center; font-weight:800;">1</span>
                            <span style="font-size:10.5px; font-weight:700; color:#0f172a;">Tangadan Falls</span>
                        </div>
                        <div style="display:flex; gap:6px; align-items:center; background:#f1f5f9; padding:6px 8px; border-radius:8px;">
                            <span style="width:16px; height:16px; border-radius:50%; background:#1e3a8a; color:#fff; font-size:9px; display:flex; align-items:center; justify-content:center; font-weight:800;">2</span>
                            <span style="font-size:10.5px; font-weight:700; color:#0f172a;">Urbiztondo Beach</span>
                        </div>
                        <div style="background:#1e3a8a; color:#fff; text-align:center; padding:7px; border-radius:8px; font-size:10.5px; font-weight:800;">
                            Save Draft Plan
                        </div>
                    </div>
                `;
            } else if (screen.mockupType === 'saved_trips') {
                mockupEl.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span style="font-size:11px; font-weight:800; color:#1e3a8a;">MY SAVED TRIPS</span>
                        <span style="font-size:9.5px; background:#e0f2fe; padding:2px 8px; border-radius:10px; font-weight:700; color:#0284c7;">READY</span>
                    </div>
                    <div style="flex:1; border-radius:12px; background:#ffffff; padding:10px; display:flex; flex-direction:column; justify-content:space-between; border:1px solid #e2e8f0;">
                        <div>
                            <div style="font-size:12px; font-weight:800; color:#0f172a;">Weekend Elyu Gateway</div>
                            <div style="font-size:9.5px; color:#64748b; margin-top:2px;">3 Spots &bull; Car Travel &bull; ₱2,500 Budget</div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <div style="flex:1; background:#10b981; color:#fff; text-align:center; padding:6px; border-radius:8px; font-size:10px; font-weight:800;">Start Trip</div>
                            <div style="flex:1; background:#f1f5f9; color:#1e3a8a; text-align:center; padding:6px; border-radius:8px; font-size:10px; font-weight:700;">Edit</div>
                        </div>
                    </div>
                `;
            } else {
                mockupEl.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span style="font-size:11px; font-weight:800; color:#1e3a8a;">TRIP MAP NAVIGATION</span>
                        <span style="font-size:9.5px; background:#fef3c7; padding:2px 8px; border-radius:10px; font-weight:700; color:#d97706;">NAVIGATING</span>
                    </div>
                    <div style="flex:1; border-radius:12px; background:#cadce4; display:flex; flex-direction:column; justify-content:space-between; padding:10px; position:relative;">
                        <i class="fa-solid fa-location-arrow" style="font-size:24px; color:#0284c7; align-self:center; margin-top:20px;"></i>
                        <div style="background:#ffffff; border-radius:10px; padding:8px; display:flex; gap:6px;">
                            <div style="flex:1; background:#0284c7; color:#fff; text-align:center; padding:5px; border-radius:6px; font-size:9.5px; font-weight:800;">Check In</div>
                            <div style="flex:1; background:#10b981; color:#fff; text-align:center; padding:5px; border-radius:6px; font-size:9.5px; font-weight:800;">Complete</div>
                        </div>
                    </div>
                `;
            }
        }
    }

    function renderPins(items) {
        const layer = document.getElementById('tg-pins-layer');
        if (!layer) return;
        layer.innerHTML = '';

        items.forEach((item, idx) => {
            const pin = document.createElement('div');
            pin.className = 'tg-pin' + (idx === selectedItemIdx ? ' active' : '');
            pin.style.left = item.pinX || '50%';
            pin.style.top = item.pinY || '50%';
            pin.innerText = item.number || (idx + 1);
            pin.title = item.title;
            pin.onclick = (e) => {
                e.stopPropagation();
                window.selectTripGuideItem(idx);
            };
            layer.appendChild(pin);
        });
    }

    function renderItemsList(items) {
        const listEl = document.getElementById('tg-items-list');
        if (!listEl) return;
        listEl.innerHTML = '';

        items.forEach((item, idx) => {
            const card = document.createElement('div');
            card.className = 'tg-list-item' + (idx === selectedItemIdx ? ' active' : '');
            card.innerHTML = `
                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                    <span style="width:22px; height:22px; border-radius:50%; background:${idx === selectedItemIdx ? '#0284c7' : '#1e3a8a'}; color:#ffffff; font-size:11px; font-weight:800; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                        ${item.number || (idx + 1)}
                    </span>
                    <span style="font-size:13px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        ${item.title}
                    </span>
                </div>
                <i class="fa-solid fa-chevron-right" style="font-size:11px; color:${idx === selectedItemIdx ? '#0284c7' : '#94a3b8'};"></i>
            `;
            card.onclick = () => window.selectTripGuideItem(idx);
            listEl.appendChild(card);
        });
    }

    function updateSelectedCard(item) {
        const numEl = document.getElementById('tg-selected-number');
        const titleEl = document.getElementById('tg-selected-title');
        const descEl = document.getElementById('tg-selected-desc');
        if (numEl) numEl.innerText = item.number || (selectedItemIdx + 1);
        if (titleEl) titleEl.innerText = item.title;
        if (descEl) descEl.innerText = item.desc;
    }

    function renderDots() {
        const container = document.getElementById('tg-dots-container');
        if (!container) return;
        container.innerHTML = '';

        window.TRIP_GUIDE_SCREENS.forEach((_, idx) => {
            const dot = document.createElement('div');
            dot.style.cursor = 'pointer';
            dot.style.transition = 'all 0.2s ease';
            if (idx === currentScreenIdx) {
                dot.style.width = '20px';
                dot.style.height = '6px';
                dot.style.borderRadius = '100px';
                dot.style.background = '#1e3a8a';
            } else {
                dot.style.width = '6px';
                dot.style.height = '6px';
                dot.style.borderRadius = '50%';
                dot.style.background = '#cbd5e1';
            }
            dot.onclick = () => {
                currentScreenIdx = idx;
                selectedItemIdx = 0;
                window.renderTripGuide();
            };
            container.appendChild(dot);
        });
    }

    window.renderTripGuide = function() {
        const screens = window.TRIP_GUIDE_SCREENS;
        if (!screens || screens.length === 0) return;
        const screen = screens[currentScreenIdx];

        const headerTitle = document.getElementById('tg-header-title');
        const headerSub = document.getElementById('tg-header-subtitle');
        const badge = document.getElementById('tg-screen-badge');
        const prevBtn = document.getElementById('tg-btn-prev');
        const nextBtn = document.getElementById('tg-btn-next');

        if (headerTitle) headerTitle.innerText = screen.title;
        if (headerSub) headerSub.innerText = screen.subtitle;
        if (badge) badge.innerText = `Screen ${currentScreenIdx + 1} of ${screens.length}`;

        if (prevBtn) {
            prevBtn.disabled = (currentScreenIdx === 0);
            prevBtn.style.opacity = (currentScreenIdx === 0) ? '0.45' : '1';
            prevBtn.style.cursor = (currentScreenIdx === 0) ? 'default' : 'pointer';
        }
        if (nextBtn) {
            if (currentScreenIdx === screens.length - 1) {
                nextBtn.innerHTML = `<span>Finish</span> <i class="fa-solid fa-check" style="font-size:11px;"></i>`;
                nextBtn.style.background = '#10b981';
            } else {
                nextBtn.innerHTML = `<span>Next</span> <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>`;
                nextBtn.style.background = '#1e3a8a';
            }
        }

        renderScreenMockup(screen);
        renderPins(screen.items || []);
        renderItemsList(screen.items || []);
        if (screen.items && screen.items[selectedItemIdx]) {
            updateSelectedCard(screen.items[selectedItemIdx]);
        }
        renderDots();
    };

    window.selectTripGuideItem = function(idx) {
        selectedItemIdx = idx;
        const screen = window.TRIP_GUIDE_SCREENS[currentScreenIdx];
        if (screen && screen.items && screen.items[idx]) {
            updateSelectedCard(screen.items[idx]);
            renderPins(screen.items);
            renderItemsList(screen.items);
        }
    };

    window.navigateTripGuide = function(direction) {
        const screens = window.TRIP_GUIDE_SCREENS;
        const newIdx = currentScreenIdx + direction;
        if (newIdx >= 0 && newIdx < screens.length) {
            currentScreenIdx = newIdx;
            selectedItemIdx = 0;
            window.renderTripGuide();
        } else if (newIdx >= screens.length) {
            window.closeTripGuideModal();
        }
    };

    window.openTripGuideModal = function() {
        const modal = document.getElementById('trip-guide-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            window.renderTripGuide();
        }
    };

    window.closeTripGuideModal = function() {
        const modal = document.getElementById('trip-guide-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };
})();
</script>
