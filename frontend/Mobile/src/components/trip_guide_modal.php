<?php
/**
 * Trip Guide Walkthrough Modal Component
 * Interactive step-by-step tour matching the requested UI design:
 * - Real phone screenshots for all 6 screens
 * - Numbered hotspot pins on screen preview
 * - Selected item explanation card
 * - Interactive "ALL ITEMS ON THIS SCREEN" list
 * - Previous / Next navigation with pagination dots
 * - Question mark removed from modal header
 * - Bottom navigation bar blurred behind modal
 */
?>
<div id="trip-guide-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10, 25, 60, 0.65); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:1000050 !important; justify-content:center; align-items:center; padding:12px; box-sizing:border-box;"
    onclick="if(event.target===this) window.closeTripGuideModal();">
    
    <div class="trip-guide-card-container"
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:820px; max-height:92dvh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(10, 25, 60, 0.45) !important; overflow:hidden; animation:popInTripGuide 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards; font-family:-apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', Roboto, sans-serif;">
        
        <!-- Header Banner (Clean White, question mark icon removed) -->
        <div style="background:#ffffff; padding:16px 20px; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f1f5f9;">
            <div style="min-width:0;">
                <h3 id="tg-header-title" style="margin:0; color:#0f172a; font-size:17px; font-weight:800; letter-spacing:-0.3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    How to Make an Itinerary
                </h3>
                <p id="tg-header-subtitle" style="margin:2px 0 0; font-size:12px; color:#64748b; font-weight:500;">
                    Step-by-step INTAN ELYU interactive guide
                </p>
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
            <div class="trip-guide-preview-col" style="flex:1; min-width:240px; display:flex; flex-direction:column; align-items:center;">
                <div style="width:100%; display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span id="tg-screen-badge" style="font-size:11px; font-weight:800; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.6px; background:#e0f2fe; padding:3px 10px; border-radius:100px;">
                        Screen 1 of 7
                    </span>
                    <span style="font-size:11px; color:#94a3b8; font-weight:600;">
                        <i class="fa-solid fa-hand-pointer" style="margin-right:4px;"></i> Tap pins to inspect
                    </span>
                </div>

                <div id="tg-preview-frame" onclick="window.togglePreviewZoom()" title="Tap to toggle zoom" style="position:relative; width:100%; max-width:220px; aspect-ratio:9/19; max-height:420px; border-radius:18px; border:2px solid #e2e8f0; background:#0f172a; overflow:hidden; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 24px rgba(10,25,60,0.12); cursor:zoom-out;">
                    <div id="tg-zoom-stage" style="width:100%; height:100%; position:relative; transition:transform 0.38s cubic-bezier(0.16, 1, 0.3, 1), transform-origin 0.38s cubic-bezier(0.16, 1, 0.3, 1); transform-origin:center center; will-change:transform;">
                        <!-- Dynamic Preview Image / Graphic -->
                        <img id="tg-preview-img" src="" alt="Screen Preview" style="width:100%; height:100%; object-fit:contain; background:#0f172a; display:block;">
                        
                        <!-- Illustrated Fallback / Dynamic SVG Mockup when screenshot not loaded -->
                        <div id="tg-preview-svg-mockup" style="width:100%; height:100%; display:none; flex-direction:column; padding:16px; box-sizing:border-box; background:linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);">
                            <!-- Rendered via JS -->
                        </div>

                        <!-- Overlay Pin Hotspots Layer -->
                        <div id="tg-pins-layer" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;">
                            <!-- Injected via JS -->
                        </div>
                    </div>
                </div>

                <!-- Zoom button placed OUTSIDE the image -->
                <div style="margin-top:8px; display:flex; justify-content:center; width:100%;">
                    <button type="button" id="tg-zoom-indicator" onclick="window.togglePreviewZoom()" style="background:#f8fafc; border:1px solid #cbd5e1; color:#1e3a8a; font-size:11px; font-weight:800; padding:4px 12px; border-radius:100px; display:inline-flex; align-items:center; gap:5px; cursor:pointer; outline:none; box-shadow:0 1px 3px rgba(15,23,42,0.06); transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.95)'" onpointerup="this.style.transform='scale(1)'">
                        <i id="tg-zoom-icon" class="fa-solid fa-magnifying-glass-minus" style="font-size:10.5px; color:#0284c7;"></i> <span id="tg-zoom-label">1.7× Focused</span>
                    </button>
                </div>
            </div>

            <!-- Right Side: Selected Item Card & All Items On This Screen -->
            <div class="trip-guide-info-col" style="flex:1.2; min-width:260px; display:flex; flex-direction:column; gap:14px;">
                
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
        <div style="background:#ffffff; padding:10px 18px; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; border-top:1px solid #f1f5f9; gap:10px;">
            <button type="button" id="tg-btn-prev" onclick="window.navigateTripGuide(-1)"
                style="background:#f8fafc; border:1px solid #cbd5e1; outline:none; color:#475569; font-weight:800; font-size:11.5px; height:32px; min-height:32px; padding:0 14px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.15s ease;">
                <i class="fa-solid fa-arrow-left" style="font-size:10.5px;"></i> Previous
            </button>

            <!-- Center Pagination Dots -->
            <div id="tg-dots-container" style="display:flex; align-items:center; gap:5px;">
                <!-- Injected via JS -->
            </div>

            <button type="button" id="tg-btn-next" onclick="window.navigateTripGuide(1)"
                style="background:#1e3a8a; border:none; outline:none; color:#ffffff; font-weight:800; font-size:11.5px; height:32px; min-height:32px; padding:0 16px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:5px; box-shadow:0 2px 8px rgba(30,58,138,0.22); transition:all 0.15s ease;">
                <span>Next</span> <i class="fa-solid fa-arrow-right" style="font-size:10.5px;"></i>
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
        padding: 12px 14px !important;
    }
    .trip-guide-preview-col {
        min-width: 100% !important;
    }
    #tg-preview-frame {
        max-width: 175px !important;
        max-height: 270px !important;
        aspect-ratio: 9/19 !important;
        margin: 0 auto !important;
    }
    .trip-guide-info-col {
        min-width: 100% !important;
    }
    #tg-items-list {
        max-height: 160px !important;
    }
}

/* Floating Trip Guide Button */
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
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, opacity 0.2s ease, visibility 0.2s ease !important;
    -webkit-tap-highlight-color: transparent !important;
}

.floating-trip-guide-btn:active {
    transform: scale(0.92) !important;
}

/* When guide modal is active, blur bottom nav and hide floating btn */
body.trip-guide-open #bottom-navigation,
html.trip-guide-open #bottom-navigation {
    pointer-events: none !important;
    filter: blur(10px) !important;
    opacity: 0.25 !important;
}

body.trip-guide-open .floating-trip-guide-btn {
    pointer-events: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
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
    box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.35), 0 4px 12px rgba(0,0,0,0.4);
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
    // 6-Screen Complete Walkthrough Guide Data
    window.TRIP_GUIDE_SCREENS = [
        {
            title: "Step 1: Explore & Add Spots",
            subtitle: "Find attractions on the map and add them to your trip",
            screenshot: "assets/img/guide/step1_spot_sheet.jpg",
            items: [
                {
                    number: 1,
                    title: "Attraction Overview & Rating",
                    desc: "Inspect destination photos, category tags, distance, community ratings, and explorer point rewards.",
                    pinX: "50%",
                    pinY: "28%"
                },
                {
                    number: 2,
                    title: "Site & Environmental Fees",
                    desc: "Review base entrance fees and categorized rates for Adults, Children, PWDs, and Senior Citizens.",
                    pinX: "50%",
                    pinY: "72%"
                },
                {
                    number: 3,
                    title: "+ Add to Trip Button",
                    desc: "Tap '+ Add to Trip' to instantly queue the attraction into your personalized active draft itinerary.",
                    pinX: "44%",
                    pinY: "94%"
                }
            ]
        },
        {
            title: "Step 2: Review Itinerary Stops",
            subtitle: "Organize route stops, distances, and starting point",
            screenshot: "assets/img/guide/step2_itinerary.jpg",
            items: [
                {
                    number: 1,
                    title: "Route Distance & Travel Time",
                    desc: "Real-time summary of computed travel distance in kilometers and estimated driving duration.",
                    pinX: "50%",
                    pinY: "26%"
                },
                {
                    number: 2,
                    title: "Starting Point (GPS)",
                    desc: "Automatically detects your live GPS coordinates or lets you select any custom starting location.",
                    pinX: "50%",
                    pinY: "38%"
                },
                {
                    number: 3,
                    title: "Destination Stops Timeline",
                    desc: "Review your sequenced stops with arrival estimates, spot distance from departure, and municipal tags.",
                    pinX: "50%",
                    pinY: "62%"
                },
                {
                    number: 4,
                    title: "Save Draft Plan Button",
                    desc: "Tap 'Save Draft Plan' to proceed to companion selection, vehicle assignment, and budget configuration.",
                    pinX: "50%",
                    pinY: "83%"
                }
            ]
        },
        {
            title: "Step 3: Choose Leg Transports",
            subtitle: "Select vehicles and calculate official LTFRB fares",
            screenshot: "assets/img/guide/step3_transport.jpg",
            items: [
                {
                    number: 1,
                    title: "Private & Rental Transport",
                    desc: "Select private vehicles like Motorcycle or Van for personal driving and charter party groups.",
                    pinX: "50%",
                    pinY: "42%"
                },
                {
                    number: 2,
                    title: "Public Transit & Modern Jeepneys",
                    desc: "Choose MPUJ, Traditional TPUJ, or Tricycle with official LTFRB distance-calibrated fares.",
                    pinX: "50%",
                    pinY: "72%"
                },
                {
                    number: 3,
                    title: "Done / Confirm Fares",
                    desc: "Tap 'Done' to lock in your transport selections and calculate your estimated transit expenses.",
                    pinX: "78%",
                    pinY: "95%"
                }
            ]
        },
        {
            title: "Step 4: Set Companions & Budget",
            subtitle: "Specify travelers for automatic discounts and budgeting",
            screenshot: "assets/img/guide/step4_companions_filled.jpg",
            items: [
                {
                    number: 1,
                    title: "Gender Headcount",
                    desc: "Set male and female companion counts for accurate party tracking and safety records.",
                    pinX: "50%",
                    pinY: "25%"
                },
                {
                    number: 2,
                    title: "Traveler Categories",
                    desc: "Classify companions as Adult, Kids, Senior, or PWD to apply legal statutory discounts automatically.",
                    pinX: "50%",
                    pinY: "46%"
                },
                {
                    number: 3,
                    title: "Personal Trip Budget",
                    desc: "Set an optional budget target to receive real-time budget health alerts as you travel.",
                    pinX: "50%",
                    pinY: "76%"
                },
                {
                    number: 4,
                    title: "Save Trip Button",
                    desc: "Tap 'Save Trip' to store your configured itinerary into your account and local device storage.",
                    pinX: "68%",
                    pinY: "92%"
                }
            ]
        },
        {
            title: "Step 5: Inspect Trip Summary",
            subtitle: "Review traveler party, transit costs, and spot fees",
            screenshot: "assets/img/guide/step5_summary.jpg",
            items: [
                {
                    number: 1,
                    title: "Traveler Party Verification",
                    desc: "Complete summary of all travelers with applied senior citizen, child, and PWD privileges.",
                    pinX: "50%",
                    pinY: "30%"
                },
                {
                    number: 2,
                    title: "Cost Breakdown Matrix",
                    desc: "Transparent division of Transit Total alongside combined Attraction Entrance & Environmental Fees.",
                    pinX: "50%",
                    pinY: "48%"
                },
                {
                    number: 3,
                    title: "Destination Route Plan",
                    desc: "Final overview of your destinations sequence, starting point, and computed leg distances.",
                    pinX: "50%",
                    pinY: "82%"
                },
                {
                    number: 4,
                    title: "Done / Finalize Itinerary",
                    desc: "Tap 'Done' to finalize your trip and immediately access it under My Saved Trips.",
                    pinX: "80%",
                    pinY: "94%"
                }
            ]
        },
        {
            title: "Step 6: Saved Trips & Start Trip",
            subtitle: "Access saved itineraries, check budget, and launch navigation",
            screenshot: "assets/img/guide/step6_saved_trips.jpg",
            items: [
                {
                    number: 1,
                    title: "My Saved Trips List",
                    desc: "Access all your planned adventures anytime from the Saved Trips tab in the bottom navigation bar.",
                    pinX: "50%",
                    pinY: "18%"
                },
                {
                    number: 2,
                    title: "Trip Budget & Vehicle Status",
                    desc: "Check your budget health badge, assigned vehicle mode, and travel party headcount at a glance.",
                    pinX: "50%",
                    pinY: "34%"
                },
                {
                    number: 3,
                    title: "Start Trip Button",
                    desc: "Tap 'Start Trip' to launch the interactive live turn-by-turn GPS navigation map and begin your adventure!",
                    pinX: "50%",
                    pinY: "62%"
                }
            ]
        },
        {
            title: "Step 7: Live GPS Trip Map",
            subtitle: "Follow turn-by-turn route, check in, and upload proof",
            screenshot: "assets/img/guide/step7_trip_map.jpg",
            items: [
                {
                    number: 1,
                    title: "Live Route & Transit Indicator",
                    desc: "Follow the animated route path with live vehicle mode indicators and next attraction previews.",
                    pinX: "50%",
                    pinY: "48%"
                },
                {
                    number: 2,
                    title: "Destination Arrival Card",
                    desc: "View current destination info, municipal location, and assigned transit fare for this leg.",
                    pinX: "50%",
                    pinY: "83%"
                },
                {
                    number: 3,
                    title: "GPS Check-In Button",
                    desc: "When inside the destination radius, tap 'Check In' to instantly verify your visit on the blockchain/system.",
                    pinX: "53%",
                    pinY: "91%"
                },
                {
                    number: 4,
                    title: "Photo Proof Upload",
                    desc: "No GPS signal? Tap 'Proof' to snap a photo at the spot. It securely queues and syncs once you are back online!",
                    pinX: "75%",
                    pinY: "91%"
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
                dot.style.width = '18px';
                dot.style.height = '7px';
                dot.style.borderRadius = '100px';
                dot.style.background = '#1e3a8a';
            } else {
                dot.style.width = '7px';
                dot.style.height = '7px';
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
                nextBtn.innerHTML = `<span>Finish</span> <i class="fa-solid fa-check" style="font-size:10.5px;"></i>`;
                nextBtn.style.background = '#10b981';
                nextBtn.style.boxShadow = '0 2px 8px rgba(16, 185, 129, 0.25)';
            } else {
                nextBtn.innerHTML = `<span>Next</span> <i class="fa-solid fa-arrow-right" style="font-size:10.5px;"></i>`;
                nextBtn.style.background = '#1e3a8a';
                nextBtn.style.boxShadow = '0 2px 8px rgba(30, 58, 138, 0.22)';
            }
        }

        renderScreenMockup(screen);
        renderPins(screen.items || []);
        renderItemsList(screen.items || []);
        if (screen.items && screen.items[selectedItemIdx]) {
            updateSelectedCard(screen.items[selectedItemIdx]);
            applyFocalZoom(screen.items[selectedItemIdx]);
        }
        renderDots();
    };

    function applyFocalZoom(item) {
        const stage = document.getElementById('tg-zoom-stage');
        const frame = document.getElementById('tg-preview-frame');
        const label = document.getElementById('tg-zoom-label');
        const icon = document.getElementById('tg-zoom-icon');
        if (!stage) return;
        if (item && item.pinX && item.pinY) {
            stage.style.transformOrigin = `${item.pinX} ${item.pinY}`;
            stage.style.transform = 'scale(1.68)';
            if (frame) frame.style.cursor = 'zoom-out';
            if (label) label.textContent = '1.7× Focused';
            if (icon) icon.className = 'fa-solid fa-magnifying-glass-minus';
        } else {
            stage.style.transformOrigin = 'center center';
            stage.style.transform = 'scale(1)';
            if (frame) frame.style.cursor = 'zoom-in';
            if (label) label.textContent = '1.0× Full';
            if (icon) icon.className = 'fa-solid fa-magnifying-glass-plus';
        }
    }

    window.togglePreviewZoom = function() {
        const stage = document.getElementById('tg-zoom-stage');
        const frame = document.getElementById('tg-preview-frame');
        const label = document.getElementById('tg-zoom-label');
        const icon = document.getElementById('tg-zoom-icon');
        if (!stage) return;
        const screen = window.TRIP_GUIDE_SCREENS[currentScreenIdx];
        const item = (screen && screen.items) ? screen.items[selectedItemIdx] : null;

        if (stage.style.transform === 'scale(1)') {
            applyFocalZoom(item);
        } else {
            stage.style.transformOrigin = 'center center';
            stage.style.transform = 'scale(1)';
            if (frame) frame.style.cursor = 'zoom-in';
            if (label) label.textContent = '1.0× Full';
            if (icon) icon.className = 'fa-solid fa-magnifying-glass-plus';
        }
    };

    window.selectTripGuideItem = function(idx) {
        selectedItemIdx = idx;
        const screen = window.TRIP_GUIDE_SCREENS[currentScreenIdx];
        if (screen && screen.items && screen.items[idx]) {
            updateSelectedCard(screen.items[idx]);
            renderPins(screen.items);
            renderItemsList(screen.items);
            applyFocalZoom(screen.items[idx]);
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
            // Finished! Reset back to Step 1 and close modal
            currentScreenIdx = 0;
            selectedItemIdx = 0;
            window.closeTripGuideModal();
        }
    };

    window.openTripGuideModal = function() {
        const modal = document.getElementById('trip-guide-modal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
            document.body.classList.add('trip-guide-open');
            document.documentElement.classList.add('trip-guide-open');
            document.body.style.overflow = 'hidden';
            window.renderTripGuide();
        }
    };

    window.closeTripGuideModal = function() {
        const modal = document.getElementById('trip-guide-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('trip-guide-open');
            document.documentElement.classList.remove('trip-guide-open');
            document.body.style.overflow = '';
            // Reset to Step 1 for next open
            currentScreenIdx = 0;
            selectedItemIdx = 0;
        }
    };
})();
</script>
