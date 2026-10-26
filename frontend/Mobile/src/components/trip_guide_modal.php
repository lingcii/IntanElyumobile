<?php
/**
 * Trip Guide Modal Component
 * Explains how to add (plan/save) and complete (navigate/check-in/rewards) trips in Intan Elyu.
 */
?>
<div id="trip-guide-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10, 25, 60, 0.45); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:999999; justify-content:center; align-items:center; padding:16px; box-sizing:border-box;"
    onclick="if(event.target===this) window.closeTripGuideModal();">
    
    <div style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:420px; max-height:88dvh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(10, 25, 60, 0.45) !important; overflow:hidden; animation:popInTripGuide 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        
        <!-- Royal Blue Header Banner -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 18px; color:#ffffff; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; border:none !important; outline:none !important;">
            <div style="min-width:0; flex:1;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:28px; height:28px; border-radius:8px; background:rgba(255,255,255,0.18); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid fa-map-location-dot" style="color:#38bdf8; font-size:14px;"></i>
                    </div>
                    <h3 style="margin:0; color:#ffffff; font-size:16px; font-weight:800; letter-spacing:-0.3px;">
                        Trip Guide
                    </h3>
                </div>
                <p style="margin:4px 0 0; font-size:11.5px; color:rgba(255,255,255,0.85); line-height:1.35; font-weight:500;">
                    How to plan, customize, and complete trips in La Union
                </p>
            </div>
            <button type="button" onclick="window.closeTripGuideModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; flex-shrink:0; margin-left:12px; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
            </button>
        </div>

        <!-- White Scrollable Body -->
        <div class="hide-scrollbar" style="background:#ffffff !important; color:#0f172a; padding:16px 18px 20px 18px; flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:16px; box-sizing:border-box; scrollbar-width:none; -ms-overflow-style:none;">
            
            <!-- SECTION 1: Adding & Planning a Trip -->
            <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-size:11.5px; font-weight:800; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.6px; display:inline-flex; align-items:center; gap:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#e0f2fe; color:#0284c7; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:900;">A</span>
                        Part 1: How to Add & Plan a Trip
                    </span>
                    <span style="font-size:11px; font-weight:700; color:#64748b; background:#f1f5f9; padding:2px 8px; border-radius:100px;">3 steps</span>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px;">
                    <!-- Step 1 -->
                    <div style="display:flex; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; box-shadow:0 2px 6px rgba(2,132,199,0.25);">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h4 style="margin:0 0 3px 0; font-size:13px; font-weight:800; color:#0f172a;">1. Discover Spots on Map</h4>
                            <p style="margin:0; font-size:12px; color:#475569; line-height:1.45;">
                                Open the <strong>Map</strong> or <strong>Explore Spots</strong>. Tap any tourist destination and click <strong style="color:#0284c7;">+ Add to Trip</strong>.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div style="display:flex; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; box-shadow:0 2px 6px rgba(37,99,235,0.25);">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h4 style="margin:0 0 3px 0; font-size:13px; font-weight:800; color:#0f172a;">2. Review & Optimize Itinerary</h4>
                            <p style="margin:0; font-size:12px; color:#475569; line-height:1.45;">
                                Go to <strong>Itinerary</strong> (Draft Plan). Reorder stops, select your vehicle (car, van, motorcycle), and view calculated travel time and distance.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div style="display:flex; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; box-shadow:0 2px 6px rgba(16,185,129,0.25);">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h4 style="margin:0 0 3px 0; font-size:13px; font-weight:800; color:#0f172a;">3. Save Draft Plan</h4>
                            <p style="margin:0; font-size:12px; color:#475569; line-height:1.45;">
                                Tap <strong style="color:#059669;">Save Draft Plan</strong>, enter a trip title, optional date, and target budget. Your plan is saved in <strong>Saved Trips</strong>!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Completing a Trip -->
            <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-size:11.5px; font-weight:800; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.6px; display:inline-flex; align-items:center; gap:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#dcfce7; color:#16a34a; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:900;">B</span>
                        Part 2: How to Navigate & Complete a Trip
                    </span>
                    <span style="font-size:11px; font-weight:700; color:#64748b; background:#f1f5f9; padding:2px 8px; border-radius:100px;">3 steps</span>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px;">
                    <!-- Step 4 -->
                    <div style="display:flex; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; box-shadow:0 2px 6px rgba(99,102,241,0.25);">
                            <i class="fa-solid fa-diamond-turn-right"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h4 style="margin:0 0 3px 0; font-size:13px; font-weight:800; color:#0f172a;">4. Start Trip Navigation</h4>
                            <p style="margin:0; font-size:12px; color:#475569; line-height:1.45;">
                                Open <strong>Saved Trips</strong> and tap <strong style="color:#4f46e5;">Start Trip</strong> to open interactive turn-by-turn routing with real-time GPS tracking.
                            </p>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div style="display:flex; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h4 style="margin:0 0 3px 0; font-size:13px; font-weight:800; color:#0f172a;">5. Check In & Upload Proof</h4>
                            <p style="margin:0; font-size:12px; color:#475569; line-height:1.45;">
                                When you reach each destination, tap <strong>Check In</strong>. You can verify via <strong>GPS location</strong> or snap <strong>photos/selfies</strong> at the spot.
                            </p>
                        </div>
                    </div>

                    <!-- Step 6 -->
                    <div style="display:flex; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, #10b981 0%, #047857 100%); color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:14px; box-shadow:0 2px 6px rgba(16,185,129,0.25);">
                            <i class="fa-solid fa-flag-checkered"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h4 style="margin:0 0 3px 0; font-size:13px; font-weight:800; color:#0f172a;">6. Complete & Earn Points</h4>
                            <p style="margin:0; font-size:12px; color:#475569; line-height:1.45;">
                                Once all stops are visited, tap <strong style="color:#059669;">Complete Trip</strong>. Rate and leave reviews to earn <strong>Explorer Points & Badges</strong>!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pro Tip Note -->
            <div style="display:flex; align-items:flex-start; gap:10px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:14px; padding:11px 13px;">
                <i class="fa-solid fa-circle-info" style="color:#0284c7; font-size:14px; margin-top:2px;"></i>
                <div style="font-size:11.5px; color:#0369a1; line-height:1.45; font-weight:600;">
                    <strong>Offline Friendly:</strong> No signal? You can still view cached trips, take verification photos, and sync them automatically once reconnected.
                </div>
            </div>

            <!-- Close Action Button -->
            <button type="button" onclick="window.closeTripGuideModal()"
                style="width:100%; padding:13px; border-radius:16px; border:none !important; outline:none !important; background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color:#ffffff !important; font-weight:800; font-size:13.5px; cursor:pointer; box-shadow:0 4px 14px rgba(30,58,138,0.25) !important; transition:all 0.15s ease; margin-top:4px;"
                onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                Got It, Let's Explore!
            </button>
        </div>
    </div>
</div>

<style>
@keyframes popInTripGuide {
    0% {
        opacity: 0;
        transform: scale(0.92) translateY(12px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>

<script>
window.openTripGuideModal = function () {
    const modal = document.getElementById('trip-guide-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.closeTripGuideModal = function () {
    const modal = document.getElementById('trip-guide-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};
</script>
