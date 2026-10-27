<?php
$pageTitle = 'My Saved Trips';
$backRoute = 'itinerary';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/testimony_modal.php'; ?>
<?php include __DIR__ . '/../components/trip_guide_modal.php'; ?>


<style>
    /* Clean white theme with dynamic scrollview and completely hidden scrollbars */
    html:has(body[data-view="saved_trips"]),
    body[data-view="saved_trips"] {
        background: #ffffff !important;
        background-color: #ffffff !important;
        height: 100vh !important;
        height: 100dvh !important;
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    body[data-view="saved_trips"] #app-container,
    body[data-view="saved_trips"] #main-content {
        background: #ffffff !important;
        background-color: #ffffff !important;
        height: 100vh !important;
        height: 100dvh !important;
        overflow: hidden !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    html, body, .saved-trips-page-container, #saved-trips-list, #main-content, #app-container {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    html:has(body[data-view="saved_trips"]) *,
    body[data-view="saved_trips"] *,
    .saved-trips-page-container * {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    html::-webkit-scrollbar,
    body::-webkit-scrollbar,
    html:has(body[data-view="saved_trips"]) *::-webkit-scrollbar,
    body[data-view="saved_trips"] *::-webkit-scrollbar,
    .saved-trips-page-container::-webkit-scrollbar,
    .saved-trips-page-container.is-scrollable::-webkit-scrollbar,
    .saved-trips-page-container.scrollview::-webkit-scrollbar,
    #saved-trips-list::-webkit-scrollbar,
    #main-content::-webkit-scrollbar,
    #app-container::-webkit-scrollbar,
    .timeline-collapsible::-webkit-scrollbar,
    .timeline-inner::-webkit-scrollbar,
    .timeline::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        background: transparent !important;
    }
    .saved-trips-page-container {
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
        height: 100vh;
        height: 100dvh;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        padding-top: var(--page-top-padding, calc(max(env(safe-area-inset-top, 0px), 36px) + 52px)) !important;
        padding-bottom: var(--page-bottom-padding, calc(max(18px, env(safe-area-inset-bottom, 18px)) + 82px)) !important;
        padding-left: var(--page-inline-padding, clamp(12px, 3.8vw, 18px)) !important;
        padding-right: var(--page-inline-padding, clamp(12px, 3.8vw, 18px)) !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        overflow-x: hidden !important;
        overflow-y: hidden; /* Default: NO scrollview when empty */
        touch-action: pan-y !important;
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Roboto, sans-serif;
    }
    .saved-trips-page-container.is-scrollable,
    .saved-trips-page-container.scrollview {
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-y !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    #saved-trips-list {
        touch-action: pan-y !important;
    }
    #edit-trip-details-modal {
        -webkit-overflow-scrolling: touch;
        touch-action: manipulation;
    }
    #edit-trip-details-modal input,
    #edit-trip-details-modal button,
    #edit-trip-details-modal label {
        touch-action: manipulation !important;
        pointer-events: auto !important;
        -webkit-user-select: auto !important;
        user-select: auto !important;
    }
</style>

<!-- Saved Trips Container -->
<div id="saved-trips-container" class="saved-trips-page-container has-header animate-slide-up">
    <!-- Offline Status Badge -->
    <div id="saved-trips-offline-badge" style="display: none; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 14px auto; padding: 7px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 11.5px; font-weight: 800; text-align: center; width: fit-content;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
        <span>Offline Mode &bull; Cached Trips & Queued Check-ins</span>
    </div>
    <div id="saved-trips-list">
        <div class="empty-state-card" style="margin-top: 30px; margin-bottom: 30px; opacity: 1 !important; visibility: visible !important;">
            <div class="empty-state-icon" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
            </div>
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff; text-wrap: balance;">No Saved Trips Yet</h3>
            <p style="margin: 0; font-size: 13.5px; color: rgba(255, 255, 255, 0.88); line-height: 1.5; max-width: 260px; text-wrap: pretty;">
                Your adventure starts here! Explore La Union tourist spots and plan your personalized itinerary.
            </p>
            <div style="display:flex; flex-direction:column; gap:10px; width:100%; max-width:280px; margin:14px auto 0;">
                <button type="button" class="btn-open-map" onclick="if(typeof window.navigateTo==='function') window.navigateTo('itinerary');" style="width:100%; margin:0; justify-content:center;">
                    <i class="fa-solid fa-plus"></i> Create New Trip
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dedicated Multi-Photo Proof Upload Modal -->
<div id="proof-upload-modal" onclick="if(event.target===this) window.closeProofUploadModal();"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.72); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:99999; justify-content:center; align-items:center; padding:16px; box-sizing:border-box;">
    <div
        style="background:#ffffff; border:none; outline:none; border-radius:26px; width:100%; max-width:420px; box-shadow:0 20px 50px rgba(0,0,0,0.35); text-align:center; max-height:90vh; overflow:hidden; display:flex; flex-direction:column; box-sizing:border-box; animation:popInProofModal 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        
        <!-- Header Banner -->
        <div style="flex-shrink:0; background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%); padding:18px 20px; display:flex; justify-content:space-between; align-items:center; border-top-left-radius:26px; border-top-right-radius:26px; box-sizing:border-box;">
            <div style="text-align:left;">
                <h3 id="proof-spot-title" style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-camera" style="color:#38bdf8; font-size:17px;"></i> Upload Photo Proof
                </h3>
                <span id="proof-spot-subtitle" style="font-size:12px; color:rgba(255,255,255,0.85); font-weight:600; display:block; margin-top:3px;">Select up to 5 photos</span>
            </div>
            <button type="button" onclick="window.closeProofUploadModal()"
                style="background:rgba(255,255,255,0.18); border:none; outline:none; color:#ffffff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:all 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>

        <!-- Middle Scrollable Area (White) -->
        <div style="background:#ffffff !important; padding:20px; flex:1; overflow-y:auto; text-align:left; box-sizing:border-box;">
            <p style="font-size:13px; color:#475569; margin:0 0 16px 0; line-height:1.5; font-weight:500;">
                Take or select selfies and photos at this destination to verify your visit.
            </p>

            <input type="hidden" id="proof-upload-item-id">
            <input type="file" id="proof-upload-input" accept="image/*" multiple style="display:none;" onchange="window.handleProofPhotosSelected(this)">

            <!-- Add Photo Action Buttons -->
            <div style="display:flex; gap:10px; margin-bottom:16px;">
                <button type="button" onclick="window.addProofPhotoFromSource('camera')"
                    style="flex:1; padding:12px 10px; background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border:none; outline:none; border-radius:14px; color:#ffffff; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; box-shadow:0 3px 10px rgba(2, 132, 199, 0.28); transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-camera" style="font-size:15px;"></i> Take Photo
                </button>
                <button type="button" onclick="window.addProofPhotoFromSource('gallery')"
                    style="flex:1; padding:12px 10px; background:#f1f5f9; border:1.5px solid #e2e8f0; outline:none; border-radius:14px; color:#1e3a8a; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-images" style="color:#0284c7; font-size:15px;"></i> Add from Gallery
                </button>
            </div>

            <!-- Selected Photos Area -->
            <div style="margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <label style="font-size:11.5px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.5px;">
                        Selected Photos
                    </label>
                    <span id="proof-count-badge" style="font-size:11.5px; font-weight:800; color:#0284c7; background:#e0f2fe; padding:3px 10px; border-radius:100px;">0 / 5 photos</span>
                </div>

                <!-- Empty State Prompt -->
                <div id="proof-empty-prompt" onclick="window.addProofPhotoFromSource('gallery')"
                    style="border:2px dashed #94a3b8; border-radius:16px; padding:24px 16px; text-align:center; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px; color:#0284c7; margin-bottom:6px; display:block;"></i>
                    <p style="margin:0; font-size:13px; font-weight:800; color:#1e293b;">Tap to select photos</p>
                    <p style="margin:4px 0 0 0; font-size:11.5px; color:#64748b; font-weight:500;">You can choose multiple photos</p>
                </div>

                <!-- Thumbnails Grid -->
                <div id="proof-thumbnails-grid" style="display:none; grid-template-columns:repeat(3, 1fr); gap:10px;"></div>
            </div>
        </div>

        <!-- Footer Banner (Cancel on Left, Submit on Right) -->
        <div style="flex-shrink:0; background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 20px; border-bottom-left-radius:26px; border-bottom-right-radius:26px; box-sizing:border-box;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:center;">
                <button type="button"
                    style="width:100%; padding:13px 16px; border-radius:14px; border:1.5px solid #cbd5e1; outline:none; background:#ffffff; color:#475569; font-size:14px; font-weight:800; cursor:pointer; transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'"
                    onclick="window.closeProofUploadModal()">Cancel</button>
                <button type="button" id="btn-submit-proof-photos" class="btn-primary"
                    style="width:100%; padding:13px 16px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; cursor:pointer; box-shadow:0 4px 14px rgba(16, 185, 129, 0.35); transition:all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'"
                    onclick="window.submitProofPhotos()">
                    <i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes popInProofModal {
    0% { opacity: 0; transform: scale(0.92); }
    100% { opacity: 1; transform: scale(1); }
}
</style>

<!-- Complete Trip Confirmation Modal -->
<div id="complete-trip-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); z-index:999999; justify-content:center; align-items:center;">
    <div
        style="background:linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%); border:none; outline:none; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 16px 40px rgba(10, 25, 60, 0.45); text-align:center;">
        <i class="fa-solid fa-flag-checkered"
            style="font-size:32px; color:#34c759; margin-bottom:10px; display:block;"></i>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Complete Trip?</h3>
        <p style="font-size:13px; color:#ffffff; opacity:0.95; margin-bottom:22px; line-height:1.5;">Are you sure you
            want to mark this trip as completed? It will be moved to your History.</p>

        <div style="display:flex; gap:10px;">
            <button type="button"
                style="flex:1; padding:13px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.12); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer;"
                onclick="window.closeCompleteTripModal()">
                Cancel
            </button>
            <button type="button" id="btn-confirm-complete-trip"
                style="flex:1; padding:13px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; outline:none; color:#ffffff; border-radius:14px; box-shadow:none; cursor:pointer;"
                onclick="window.executeConfirmCompleteTrip()">
                Complete
            </button>
        </div>
    </div>
</div>

<!-- Delete Trip Confirmation Modal -->
<div id="delete-trip-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); z-index:999999; justify-content:center; align-items:center;">
    <div
        style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none; outline:none; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 16px 40px rgba(10, 25, 60, 0.45); text-align:center;">
        <i class="fa-solid fa-trash-can" style="font-size:32px; color:#ffffff; margin-bottom:10px; display:block;"></i>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Delete Saved Trip?</h3>
        <p id="delete-trip-title-text"
            style="font-size:13px; color:rgba(255,255,255,0.95); margin-bottom:22px; line-height:1.5;">Are you sure you
            want to delete this trip? All saved itinerary items will be removed.</p>

        <div style="display:flex; gap:10px;">
            <button type="button"
                style="flex:1; padding:13px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.22); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer; touch-action:manipulation; pointer-events:auto !important;"
                onclick="window.closeDeleteTripModal()">
                Cancel
            </button>
            <button type="button" id="btn-confirm-delete-trip"
                style="flex:1; padding:13px; font-size:14px; font-weight:800; background:#ef4444; border:none; outline:none; color:#ffffff; border-radius:14px; box-shadow:none; cursor:pointer; touch-action:manipulation; pointer-events:auto !important;"
                onclick="window.executeConfirmDeleteTrip()">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Edit Trip Modal (Direct In-Place Editing, Notifications style) -->
<div id="edit-trip-details-modal"
    onclick="if(event.target === this) window.closeEditTripDetailsModal();"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.55); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:9999999; justify-content:center; align-items:center; padding:16px; box-sizing:border-box; pointer-events:auto !important; touch-action:manipulation;">
    <div onclick="event.stopPropagation();" style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:22px; width:100%; max-width:390px; max-height:86dvh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 16px 40px rgba(10,25,60,0.45) !important; pointer-events:auto !important; touch-action:manipulation;" class="hide-scrollbar">
        
        <!-- Header Banner (Locked top) -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:14px 16px; color:#ffffff; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; border:none !important;">
            <h3 style="margin:0; color:#ffffff; font-size:16px; font-weight:800; letter-spacing:-0.3px;">
                Edit Trip
            </h3>
            <button type="button" onclick="window.closeEditTripDetailsModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; flex-shrink:0; transition:transform 0.15s ease; touch-action:manipulation;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important; font-size:14px;"></i>
            </button>
        </div>

        <input type="hidden" id="quick-edit-trip-id" value="">

        <!-- Scrollable Middle Body (White Background) -->
        <div style="background:#ffffff !important; color:#0f172a; padding:16px; flex:1; min-height:0; overflow-y:auto; display:flex; flex-direction:column; box-sizing:border-box; pointer-events:auto !important;" class="hide-scrollbar">

            <!-- Trip Name -->
            <label style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:6px; display:block;">Trip Name</label>
            <input type="text" id="quick-edit-trip-title" placeholder="Trip Title"
                style="width:100%; padding:12px 14px; border-radius:14px; border:1.5px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#0f172a; -webkit-text-fill-color:#0f172a; margin-bottom:14px; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box; pointer-events:auto !important; touch-action:manipulation;"
                onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#e2e8f0'">

            <!-- Trip Date -->
            <label style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:6px; display:block;">Trip Date</label>
            <input type="date" id="quick-edit-trip-date"
                style="width:100%; padding:12px 14px; border-radius:14px; border:1.5px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#0f172a; -webkit-text-fill-color:#0f172a; margin-bottom:14px; font-family:inherit; font-size:14px; font-weight:600; box-sizing:border-box; pointer-events:auto !important; touch-action:manipulation;"
                onfocus="this.style.borderColor='#0284c7'" onblur="this.style.borderColor='#e2e8f0'">

            <!-- Travel Companions Section with Radio Buttons (No icons) -->
            <label style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:8px; display:block;">Travel Companions</label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                <label id="quick-edit-opt-solo" onclick="window.setQuickEditTripType('solo')"
                    style="display:flex; align-items:center; gap:8px; padding:11px 14px; border-radius:14px; background:#eff6ff; border:1.5px solid #2563eb; cursor:pointer; transition:all 0.2s ease; touch-action:manipulation; pointer-events:auto !important;">
                    <input type="radio" name="quick_edit_companion_type" id="quick-edit-radio-solo" value="solo" checked style="accent-color:#2563eb; cursor:pointer; width:17px; height:17px; pointer-events:auto !important;">
                    <span style="color:#1e3a8a; font-size:13px; font-weight:800;">Solo Trip</span>
                </label>
                <label id="quick-edit-opt-group" onclick="window.setQuickEditTripType('group')"
                    style="display:flex; align-items:center; gap:8px; padding:11px 14px; border-radius:14px; background:#f8fafc; border:1.5px solid #e2e8f0; cursor:pointer; transition:all 0.2s ease; touch-action:manipulation; pointer-events:auto !important;">
                    <input type="radio" name="quick_edit_companion_type" id="quick-edit-radio-group" value="group" style="accent-color:#2563eb; cursor:pointer; width:17px; height:17px; pointer-events:auto !important;">
                    <span style="color:#64748b; font-size:13px; font-weight:800;">With Companions</span>
                </label>
            </div>
            <input type="hidden" id="quick-edit-trip-type" value="solo">

            <!-- Stepper Box for Companions (No icons) -->
            <div id="quick-edit-companion-box" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:14px; margin-bottom:14px; pointer-events:auto !important;">
                <div style="font-size:12px; color:#475569; font-weight:700; margin-bottom:12px;">
                    Additional Travelers:
                </div>
                <!-- Male -->
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-size:13px; font-weight:700; color:#1e293b;">Male</span>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" onclick="window.adjustQuickEditCompanions('males', -1)" style="width:32px; height:32px; border-radius:50%; background:#334155; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(0,0,0,0.2); touch-action:manipulation; pointer-events:auto !important;">−</button>
                        <span id="quick-edit-males-count" style="font-size:15px; font-weight:800; color:#0f172a; min-width:24px; text-align:center;">0</span>
                        <button type="button" onclick="window.adjustQuickEditCompanions('males', 1)" style="width:32px; height:32px; border-radius:50%; background:#0284c7; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(2,132,199,0.35); touch-action:manipulation; pointer-events:auto !important;">+</button>
                    </div>
                </div>
                <!-- Female -->
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <span style="font-size:13px; font-weight:700; color:#1e293b;">Female</span>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" onclick="window.adjustQuickEditCompanions('females', -1)" style="width:32px; height:32px; border-radius:50%; background:#334155; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(0,0,0,0.2); touch-action:manipulation; pointer-events:auto !important;">−</button>
                        <span id="quick-edit-females-count" style="font-size:15px; font-weight:800; color:#0f172a; min-width:24px; text-align:center;">0</span>
                        <button type="button" onclick="window.adjustQuickEditCompanions('females', 1)" style="width:32px; height:32px; border-radius:50%; background:#db2777; border:none; outline:none; color:#ffffff; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(219,39,119,0.35); touch-action:manipulation; pointer-events:auto !important;">+</button>
                    </div>
                </div>
                <!-- Live Party Size Summary -->
                <div style="padding-top:10px; border-top:1px solid #e2e8f0; font-size:11.5px; color:#64748b; display:flex; align-items:center; justify-content:space-between;">
                    <span>Total Party Size:</span>
                    <span id="quick-edit-party-summary" style="color:#0284c7; font-weight:800;">1 Pax (Solo)</span>
                </div>
            </div>
        </div>

        <!-- Locked Bottom Footer Banner: Edit Stops on Left, Save Changes on Right -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:12px 16px max(12px, env(safe-area-inset-bottom)) 16px; flex-shrink:0; display:flex; flex-direction:row; gap:10px; align-items:center; justify-content:space-between; border:none !important; pointer-events:auto !important;">
            <button type="button" onclick="window.openPlannerFromQuickEdit()"
                style="flex:1; height:42px; border-radius:100px; border:1px solid rgba(255,255,255,0.28) !important; outline:none !important; background:rgba(255,255,255,0.14) !important; color:#ffffff !important; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:transform 0.15s ease; touch-action:manipulation; pointer-events:auto !important;"
                onpointerdown="this.style.transform='scale(0.96)'" onpointerup="this.style.transform='scale(1)'">
                Edit Stops
            </button>
            <button type="button" id="btn-save-quick-edit" onclick="window.submitQuickEditTripDetails()"
                style="flex:1; height:42px; border-radius:100px; border:none !important; outline:none !important; background:#ffffff !important; color:#1e3a8a !important; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; transition:transform 0.15s ease; touch-action:manipulation; pointer-events:auto !important;"
                onpointerdown="this.style.transform='scale(0.96)'" onpointerup="this.style.transform='scale(1)'">
                Save Changes
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        var backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');

        // Ensure proof image resolution and fallback helpers exist
        if (typeof window.resolveProofImageUrl !== 'function') {
            window.resolveProofImageUrl = function (rawUrl) {
                if (!rawUrl || typeof rawUrl !== 'string') return '';
                var url = rawUrl.trim();
                if (!url) return '';
                if (url.startsWith('data:') || url.startsWith('blob:')) return url;
                var activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                var proofMatch = url.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                if (proofMatch && proofMatch[1]) return activeBackend + '/api/image/proof_images/' + proofMatch[1];
                if (url.startsWith('http://') || url.startsWith('https://')) {
                    if (url.includes('r2.dev') || url.includes('localhost') || url.includes('127.0.0.1')) {
                        var clean = url.split('/').pop().replace(/[?#].*$/, '');
                        var localMatch = clean.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                        if (localMatch && localMatch[1]) return activeBackend + '/api/image/proof_images/' + localMatch[1];
                        return activeBackend + '/api/image/proof_images/' + clean;
                    }
                    return url;
                }
                var clean = url.replace(/^\/+/, '');
                if (clean.startsWith('storage/proof_images/')) return activeBackend + '/api/image/proof_images/' + clean.replace('storage/proof_images/', '');
                if (clean.startsWith('proof_images/')) return activeBackend + '/api/image/proof_images/' + clean.replace('proof_images/', '');
                if (clean.startsWith('api/')) return activeBackend + '/' + clean;
                return activeBackend + '/api/image/proof_images/' + clean.split('/').pop().replace(/[?#].*$/, '');
            };
        }

        if (typeof window.getProofImageFallbackUrl !== 'function') {
            window.getProofImageFallbackUrl = function (rawUrl) {
                if (!rawUrl || typeof rawUrl !== 'string') return 'assets/img/no_image.svg';
                var url = rawUrl.trim();
                if (url.startsWith('data:') || url.startsWith('blob:')) return url;
                var activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                var proofMatch = url.match(/(proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                if (proofMatch && proofMatch[1]) return activeBackend + '/api/image/proof_images/' + proofMatch[1];
                var clean = url.replace(/^\/+/, '').split('/').pop().replace(/[?#].*$/, '');
                if (clean) return activeBackend + '/api/image/proof_images/' + clean;
                return 'assets/img/no_image.svg';
            };
        }

        window.getPersistentItemStates = function () {
            try {
                const raw = JSON.parse(localStorage.getItem('intan_elyu_verified_item_states') || '{}');
                let changed = false;
                Object.keys(raw).forEach(k => {
                    if (k.startsWith('spot_')) {
                        delete raw[k];
                        changed = true;
                    }
                });
                if (changed) {
                    try { localStorage.setItem('intan_elyu_verified_item_states', JSON.stringify(raw)); } catch (e) {}
                }
                return raw;
            } catch (e) {
                return {};
            }
        };

        window.savePersistentItemState = function (itemId, stateUpdates) {
            if (!itemId) return;
            try {
                const states = window.getPersistentItemStates();
                const key = String(itemId);
                const existing = states[key] || {};
                states[key] = {
                    ...existing,
                    ...stateUpdates,
                    updated_at: Date.now()
                };
                localStorage.setItem('intan_elyu_verified_item_states', JSON.stringify(states));
            } catch (e) {
                console.warn('savePersistentItemState error:', e);
            }
        };

        window.getTouristSpotById = function (id) {
            if (!id) return null;
            const sId = String(id);
            if (window._cachedSpotsMap && window._cachedSpotsMap[sId]) {
                return window._cachedSpotsMap[sId];
            }
            const sources = [window.destinations, window.allDestinations, window.spotsList];
            for (const arr of sources) {
                if (Array.isArray(arr)) {
                    const found = arr.find(s => s && (String(s.id) === sId || String(s.tourist_spot_id) === sId));
                    if (found) return found;
                }
            }
            try {
                const rawMap = localStorage.getItem('public_map_data');
                if (rawMap) {
                    const parsedMap = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(rawMap, null) : JSON.parse(rawMap);
                    const spotsArr = (parsedMap && parsedMap.data && parsedMap.data.destinations) ? parsedMap.data.destinations : (parsedMap && parsedMap.destinations ? parsedMap.destinations : []);
                    if (Array.isArray(spotsArr)) {
                        const found = spotsArr.find(s => s && (String(s.id) === sId || String(s.tourist_spot_id) === sId));
                        if (found) return found;
                    }
                }
                const rawSpots = localStorage.getItem('intan_elyu_spots') || localStorage.getItem('all_destinations');
                if (rawSpots) {
                    const parsed = JSON.parse(rawSpots);
                    const list = Array.isArray(parsed) ? parsed : (parsed.data || []);
                    if (Array.isArray(list)) {
                        const found = list.find(s => s && (String(s.id) === sId || String(s.tourist_spot_id) === sId));
                        if (found) return found;
                    }
                }
            } catch (e) { }
            return null;
        };

        function updateScrollviewState() {
            const container = document.getElementById('saved-trips-container');
            const list = document.getElementById('saved-trips-list');
            if (!container || !list) return;

            const cards = list.querySelectorAll('.trip-swipe-container');
            const hasEmptyState = list.querySelector('.empty-state-card');

            // If empty state or 0 cards, remove scrollview completely
            if (hasEmptyState || cards.length === 0) {
                container.classList.remove('is-scrollable');
                container.scrollTop = 0;
                return;
            }

            // Enable scrollview if multiple cards exist or content overflows
            requestAnimationFrame(() => {
                const isOverflowing = cards.length > 1 || list.scrollHeight > (container.clientHeight - 30);
                if (isOverflowing) {
                    container.classList.add('is-scrollable');
                } else {
                    container.classList.remove('is-scrollable');
                    container.scrollTop = 0;
                }
            });
        }
        window.updateSavedTripsScrollview = updateScrollviewState;
        window.addEventListener('resize', updateScrollviewState);

        window.fetchSavedTrips = async function (forceRefresh = false) {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const isGuest = !token || token === 'guest_token' || token.startsWith('guest_') || token.startsWith('offline_') || localStorage.getItem('intan_elyu_guest') === 'true';
            const isOfflineMode = localStorage.getItem('intan_elyu_offline_mode') === 'true' || localStorage.getItem('intan_elyu_offline_guest') === 'true';
            const isNetworkDown = (typeof navigator.onLine === 'boolean' && !navigator.onLine)
                || window.isNetworkOffline === true
                || (window.isAppOnline === false);
            const isOffline = isNetworkDown || isOfflineMode || isGuest;

            // Helper to harvest all locally available trips across all storage keys
            function getLocalTripsCombined() {
                const combined = [];
                const seenIds = new Set();
                const addTrip = (t) => {
                    if (!t) return;
                    const id = String(t.id || t.local_id || t.trip_id || '');
                    const updateId = t.update_id ? String(t.update_id) : '';
                    if (id) {
                        const existingIdx = combined.findIndex(ex => {
                            if (!ex) return false;
                            const exId = String(ex.id || ex.local_id || ex.trip_id || '');
                            const exUpdateId = ex.update_id ? String(ex.update_id) : '';
                            if (exId === id) return true;
                            if (updateId && (exId === updateId || exUpdateId === updateId)) return true;
                            if (exUpdateId && (exUpdateId === id)) return true;
                            if (exId === ('offline_edit_' + id) || id === ('offline_edit_' + exId)) return true;
                            return false;
                        });

                        if (existingIdx !== -1) {
                            const exTime = combined[existingIdx].updated_at ? new Date(combined[existingIdx].updated_at).getTime() : 0;
                            const inTime = t.updated_at ? new Date(t.updated_at).getTime() : 0;
                            if (inTime > exTime) {
                                combined[existingIdx] = { ...combined[existingIdx], ...t };
                            }
                            return;
                        }
                        seenIds.add(id);
                        if (updateId) seenIds.add(updateId);
                        if (id.startsWith('offline_edit_')) seenIds.add(id.replace('offline_edit_', ''));
                    }
                    combined.push(t);
                };

                // A. Offline queued trips (highest priority for offline edits)
                if (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function') {
                    try {
                        const queued = window.OfflineTripManager.getQueuedTrips();
                        if (Array.isArray(queued)) queued.forEach(addTrip);
                    } catch(e) {}
                }

                // B. Token-specific cache
                if (token) {
                    try {
                        const tokenKey = 'saved_trips_' + token.substring(0, 10);
                        const raw = localStorage.getItem(tokenKey);
                        if (raw) {
                            const parsed = JSON.parse(raw);
                            const list = Array.isArray(parsed) ? parsed : (parsed.data || parsed.itineraries || []);
                            if (Array.isArray(list)) list.forEach(addTrip);
                        }
                    } catch(e) {}
                }

                // C. Local storage backups
                ['intan_elyu_saved_trips', 'intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips'].forEach(k => {
                    try {
                        const raw = localStorage.getItem(k);
                        if (raw) {
                            const parsed = JSON.parse(raw);
                            const list = Array.isArray(parsed) ? parsed : (parsed.data || parsed.itineraries || []);
                            if (Array.isArray(list)) list.forEach(addTrip);
                        }
                    } catch(e) {}
                });

                return combined.filter(t => {
                    const isDeleted = (typeof window.isTripDeleted === 'function') ? window.isTripDeleted(t) : false;
                    const isCompleted = (typeof window.isTripCompletedLocally === 'function') ? window.isTripCompletedLocally(t) : (t && t.status === 'completed');
                    return !isDeleted && !isCompleted;
                });
            }

            // Helper to merge fresh server trips with genuine pending offline-queued trips
            function mergeServerWithQueuedTrips(serverList) {
                if (!Array.isArray(serverList)) return getLocalTripsCombined();
                const list = [...serverList];
                const serverIds = new Set();
                list.forEach(t => {
                    if (!t) return;
                    if (t.id != null) serverIds.add(String(t.id));
                    if (t.trip_id != null) serverIds.add(String(t.trip_id));
                    if (t.local_id != null) serverIds.add(String(t.local_id));
                });

                const queuedTrips = (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function')
                    ? window.OfflineTripManager.getQueuedTrips()
                    : [];

                const localTrips = getLocalTripsCombined();
                localTrips.forEach(lTrip => {
                    if (!lTrip) return;
                    const lId = String(lTrip.id || lTrip.local_id || '');
                    const uId = String(lTrip.update_id || '');

                    // Check if this trip is genuinely waiting in the OfflineTripManager queue
                    const isTrulyQueued = queuedTrips.some(q => {
                        if (!q) return false;
                        const qId = String(q.id || q.local_id || '');
                        const qUpdateId = String(q.update_id || '');
                        return (qId === lId || qUpdateId === lId || (uId && (qId === uId || qUpdateId === uId)) || qId === ('offline_edit_' + lId));
                    });

                    // If this was an offline edit of an existing server trip and is genuinely queued
                    if (uId && serverIds.has(uId) && isTrulyQueued) {
                        const sIdx = list.findIndex(st => String(st.id) === uId || String(st.trip_id) === uId);
                        if (sIdx !== -1) {
                            list[sIdx] = { ...list[sIdx], ...lTrip, id: uId, is_offline_queued: true };
                        }
                        return;
                    }

                    // If local trip has the same ID as a server trip:
                    if (lId && serverIds.has(lId)) {
                        const sIdx = list.findIndex(st => String(st.id) === lId || String(st.trip_id) === lId);
                        if (sIdx !== -1) {
                            const serverTrip = list[sIdx];
                            const localUpdatedAt = lTrip.updated_at ? new Date(lTrip.updated_at).getTime() : 0;
                            const serverUpdatedAt = serverTrip.updated_at ? new Date(serverTrip.updated_at).getTime() : 0;
                            // Only override server trip if local trip has genuine pending offline edits
                            if (localUpdatedAt > serverUpdatedAt && isTrulyQueued) {
                                list[sIdx] = {
                                    ...serverTrip,
                                    ...lTrip,
                                    id: serverTrip.id || lTrip.id
                                };
                            }
                        }
                        return;
                    }

                    // If it's a new offline-created trip that is genuinely in the offline queue, prepend to keep it visible!
                    if (lId && !serverIds.has(lId) && isTrulyQueued) {
                        serverIds.add(lId);
                        list.unshift(lTrip);
                    }
                });

                return list;
            }

            // 1. Instant pre-hydrate and render immediately with 0ms delay!
            const localTrips = getLocalTripsCombined();
            renderSavedTrips(localTrips);

            // 2. If offline, ensure offline badge displays and do NOT attempt blocking network fetch
            if (isOffline) {
                const badge = document.getElementById('saved-trips-offline-badge');
                if (badge) {
                    badge.style.setProperty('display', 'inline-flex', 'important');
                }
                return;
            }

            if (!token || token.startsWith('guest_') || token.startsWith('offline_')) {
                return;
            }

            // 3. Online mode: fetch fresh trips from API and update local caches
            const cacheKey = 'saved_trips_' + token.substring(0, 10);
            const fetchCache = (typeof window.useCache === 'function') ? window.useCache : (async (key, fetcher, renderer, forceRefresh) => {
                try {
                    const d = await fetcher();
                    if (typeof renderer === 'function') renderer(d);
                    return d;
                } catch (e) {
                    if (typeof renderer === 'function') renderer(null);
                }
            });

            try {
                await fetchCache(
                    cacheKey,
                    async () => {
                        const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                        const timeoutId = controller ? setTimeout(() => controller.abort(), 8000) : null;
                        try {
                            const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                            const response = await fetch(bUrl + '/api/tourist/itineraries', {
                                signal: controller ? controller.signal : undefined,
                                headers: {
                                    'Accept': 'application/json',
                                    'ngrok-skip-browser-warning': 'true',
                                    'Authorization': 'Bearer ' + token
                                }
                            });
                            if (response.status === 401) {
                                localStorage.removeItem('intan_elyu_token');
                                localStorage.removeItem('Intan_Elyu_Token');
                                if (typeof showToast === 'function') showToast("Session expired. Please log in again.");
                                if (typeof window.navigateTo === 'function') window.navigateTo('auth');
                                return [];
                            }
                            if (!response.ok) throw new Error("Failed to fetch saved trips: " + response.status);
                            const data = await response.json();
                            const itins = data.itineraries || [];
                            const persistentStates = (typeof window.getPersistentItemStates === 'function') ? window.getPersistentItemStates() : {};
                            let persistentStatesChanged = false;
                            const newlyApprovedItems = [];
                            const queuedCheckins = (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.getQueue === 'function')
                                ? window.OfflineCheckinManager.getQueue()
                                : [];

                            const mergedItins = itins.map(trip => {
                                if (!trip || !Array.isArray(trip.items)) return trip;
                                return {
                                    ...trip,
                                    items: trip.items.map(it => {
                                        if (!it) return it;
                                        const itKey = String(it.id || '');
                                        const spotIdKey = String(it.tourist_spot_id || it.destination_id || (it.destination ? it.destination.id : ''));
                                        const override = persistentStates[itKey] || null;

                                        // Server status takes absolute precedence if server says approved or rejected
                                        const isServerApproved = Boolean(it.is_visited || it.proof_status === 'approved');
                                        const isServerRejected = (it.proof_status === 'rejected');

                                        // Detect newly approved proof to celebrate to the user
                                        if (isServerApproved) {
                                            if (override && (override.proof_status === 'pending' || !override.proof_status) && override.proof_status !== 'approved') {
                                                newlyApprovedItems.push(it);
                                            }
                                            if (persistentStates[itKey] && persistentStates[itKey].proof_status !== 'approved') {
                                                persistentStates[itKey].proof_status = 'approved';
                                                persistentStates[itKey].is_visited = true;
                                                persistentStates[itKey].is_location_verified = true;
                                                persistentStatesChanged = true;
                                            }
                                        } else if (isServerRejected) {
                                            if (persistentStates[itKey] && persistentStates[itKey].proof_status !== 'rejected') {
                                                persistentStates[itKey].proof_status = 'rejected';
                                                persistentStates[itKey].rejection_reason = it.rejection_reason;
                                                persistentStatesChanged = true;
                                            }
                                        }

                                        // Check queued check-in / photo upload in OfflineCheckinManager for this specific item
                                        const queued = queuedCheckins.find(q => q && q.itemId && String(q.itemId) === itKey);

                                        // Check in-memory cached trip item strictly within the same trip ID
                                        let inMemoryItem = null;
                                        if (Array.isArray(window._cachedSavedTrips)) {
                                            const tripIdStr = String(trip.id || trip.local_id || '');
                                            const sameTrip = window._cachedSavedTrips.find(t => t && String(t.id || t.local_id || '') === tripIdStr);
                                            if (sameTrip && Array.isArray(sameTrip.items)) {
                                                inMemoryItem = sameTrip.items.find(m => m && String(m.id) === itKey);
                                            }
                                        }

                                        const serverProofs = (Array.isArray(it.proof_images) && it.proof_images.length > 0)
                                            ? it.proof_images
                                            : (it.proof_image ? [it.proof_image] : []);

                                        const localProofs = (override && Array.isArray(override.proof_images) && override.proof_images.length > 0)
                                            ? override.proof_images
                                            : ((override && override.proof_image) ? [override.proof_image] : []);

                                        const queuedProofs = (queued && (queued.previewUrl || queued.image))
                                            ? [queued.previewUrl || queued.image]
                                            : [];

                                        const inMemProofs = (inMemoryItem && Array.isArray(inMemoryItem.proof_images) && inMemoryItem.proof_images.length > 0)
                                            ? inMemoryItem.proof_images
                                            : ((inMemoryItem && inMemoryItem.proof_image) ? [inMemoryItem.proof_image] : []);

                                        // Server proofs take precedence if present; otherwise keep queued/local/in-memory proof so turning online never loses photos!
                                        const activeProofs = serverProofs.length > 0
                                            ? serverProofs
                                            : (queuedProofs.length > 0 ? queuedProofs : (localProofs.length > 0 ? localProofs : inMemProofs));

                                        let activeStatus;
                                        if (isServerApproved) {
                                            activeStatus = 'approved';
                                        } else if (isServerRejected) {
                                            activeStatus = 'rejected';
                                        } else if (it.proof_status) {
                                            activeStatus = it.proof_status;
                                        } else if (override && override.proof_status) {
                                            activeStatus = override.proof_status;
                                        } else if (queued) {
                                            activeStatus = 'pending';
                                        } else if (inMemoryItem && inMemoryItem.proof_status) {
                                            activeStatus = inMemoryItem.proof_status;
                                        } else {
                                            activeStatus = (activeProofs.length > 0 ? 'pending' : null);
                                        }

                                        return {
                                            ...it,
                                            is_visited: isServerApproved ? true : Boolean(it.is_visited || (override && override.is_visited)),
                                            is_location_verified: Boolean(isServerApproved || it.is_location_verified || it.visited_at || (override && (override.is_location_verified || override.visited_at)) || (queued && queued.status) || (inMemoryItem && inMemoryItem.is_location_verified)),
                                            visited_at: it.visited_at || (override ? override.visited_at : null) || (queued ? queued.capturedAt : null) || (inMemoryItem ? inMemoryItem.visited_at : null),
                                            proof_image: activeProofs.length > 0 ? activeProofs[0] : null,
                                            proof_images: activeProofs,
                                            proof_status: activeStatus,
                                            is_offline_queued: Boolean(queued || (override && override.is_queued) || (inMemoryItem && inMemoryItem.is_offline_queued))
                                        };
                                    })
                                };
                            });

                            if (persistentStatesChanged) {
                                try {
                                    localStorage.setItem('intan_elyu_verified_item_states', JSON.stringify(persistentStates));
                                } catch (e) { }
                            }

                            if (newlyApprovedItems.length > 0) {
                                newlyApprovedItems.forEach(itemApproved => {
                                    const destName = (itemApproved.destination && itemApproved.destination.name) ? itemApproved.destination.name : 'Your destination';
                                    if (typeof showToast === 'function') {
                                        showToast(`🎉 Verified! Photo proof for ${destName} was approved by Admin!`, 'success', 5000);
                                    }
                                });
                                if (typeof window.fetchUserProfile === 'function') {
                                    window.fetchUserProfile();
                                }
                            }

                            const fullMergedItins = mergeServerWithQueuedTrips(mergedItins);
                            try {
                                localStorage.setItem('intan_elyu_offline_saved_trips_backup', JSON.stringify(fullMergedItins));
                                localStorage.setItem('intan_elyu_saved_trips', JSON.stringify(fullMergedItins));
                            } catch (e) { }
                            return fullMergedItins;
                        } finally {
                            if (timeoutId) clearTimeout(timeoutId);
                        }
                    },
                    (itineraries) => {
                        const mergedFinal = mergeServerWithQueuedTrips(itineraries);
                        if (mergedFinal && Array.isArray(mergedFinal)) {
                            window._cachedSavedTrips = mergedFinal;
                            try {
                                localStorage.setItem('intan_elyu_offline_saved_trips_backup', JSON.stringify(mergedFinal));
                                localStorage.setItem('intan_elyu_saved_trips', JSON.stringify(mergedFinal));
                            } catch (e) { }
                            if (!window._isStartingTrip) {
                                renderSavedTrips(mergedFinal);
                            }
                        } else {
                            if (!window._isStartingTrip) {
                                renderSavedTrips(getLocalTripsCombined());
                            }
                        }
                    },
                    (forceRefresh || Boolean(window._hasPendingApprovalItems)),
                    (window._hasPendingApprovalItems ? 3000 : 30000)
                );
            } catch (fetchErr) {
                console.warn("fetchSavedTrips handled offline fallback:", fetchErr);
                renderSavedTrips(getLocalTripsCombined());
            }
        };

        window.loadSavedTrips = window.fetchSavedTrips;

        const VEHICLE_CATALOG = {
            'own_car': { name: 'Own Car', icon: 'fa-car', color: '#f59e0b' },
            'motorcycle': { name: 'Motorcycle', icon: 'fa-motorcycle', color: '#f97316' },
            'van': { name: 'Van', icon: 'fa-van-shuttle', color: '#a855f7' },
            'mpuj': { name: 'Modern Jeepney', icon: 'fa-bus-simple', color: '#10b981' },
            'tpuj': { name: 'Traditional Jeepney', icon: 'fa-van-shuttle', color: '#f59e0b' },
            'jeepney': { name: 'Jeepney', icon: 'fa-van-shuttle', color: '#38bdf8' },
            'tricycle': { name: 'Tricycle', icon: 'fa-motorcycle', color: '#10b981' },
            'pub_regular': { name: 'Ordinary Bus', icon: 'fa-bus-simple', color: '#06b6d4' },
            'pub_ordinary': { name: 'Ordinary Bus', icon: 'fa-bus-simple', color: '#06b6d4' },
            'pub_aircon': { name: 'Aircon Bus', icon: 'fa-bus', color: '#38bdf8' },
            'bus': { name: 'Bus', icon: 'fa-bus', color: '#38bdf8' },
            'private_bus': { name: 'Aircon Bus', icon: 'fa-bus', color: '#38bdf8' },
            'mini_bus': { name: 'Mini Bus', icon: 'fa-bus-simple', color: '#06b6d4' },
            'lutrampco': { name: 'Modern Jeepney', icon: 'fa-bus-simple', color: '#38bdf8' },
            'uve': { name: 'UV Express', icon: 'fa-van-shuttle', color: '#a855f7' },
            'taxi': { name: 'Taxi', icon: 'fa-taxi', color: '#eab308' },
            'walking': { name: 'Walk / Hike', icon: 'fa-person-walking', color: '#22c55e' }
        };

        function parseVehicleModes(str) {
            if (!str) return [];
            if (Array.isArray(str)) return str.map(s => String(s).trim().toLowerCase().replace(/[- ]/g, '_')).filter(Boolean);
            return String(str)
                .split(/[,+]|\band\b/i)
                .map(s => s.trim().toLowerCase().replace(/^[-_\s]+|[-_\s]+$/g, '').replace(/[- ]+/g, '_').replace(/_+/g, '_'))
                .filter(Boolean);
        }

        function getVehicleInfo(key) {
            const norm = String(key || '').toLowerCase().trim().replace(/[- ]/g, '_');
            if (norm === 'no_vehicle' || norm === 'none' || norm === 'no_vehicle_selected' || norm === '') {
                return {
                    name: 'No Vehicle Selected',
                    icon: 'fa-ban',
                    color: '#f87171'
                };
            }
            if (VEHICLE_CATALOG[norm]) return VEHICLE_CATALOG[norm];
            if (norm === 'car' || norm === 'own_car' || norm === 'private_car') return VEHICLE_CATALOG['own_car'];
            if (norm === 'trike') return VEHICLE_CATALOG['tricycle'];
            if (norm.includes('aircon') || norm.includes('pub_aircon')) return VEHICLE_CATALOG['pub_aircon'];
            if (norm.includes('ordinary') || norm.includes('regular')) return VEHICLE_CATALOG['pub_regular'];
            if (norm.includes('bus')) return VEHICLE_CATALOG['pub_aircon'];
            if (norm.includes('mpuj') || norm.includes('modern')) return VEHICLE_CATALOG['mpuj'];
            if (norm.includes('tpuj') || norm.includes('traditional')) return VEHICLE_CATALOG['tpuj'];
            if (norm.includes('motor')) return VEHICLE_CATALOG['motorcycle'];
            return {
                name: norm.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                icon: 'fa-route',
                color: '#38bdf8'
            };
        }

        function getSavedTripTransportBadge(rawMode, trip = null) {
            let mode = rawMode;
            if (trip && Array.isArray(trip.items) && trip.items.length > 0) {
                const itemModes = trip.items
                    .map(it => (it.transport_mode || it.transport_type || '').trim())
                    .filter(m => m && m !== 'suspended');
                const validNonEmptyModes = itemModes.filter(m => m !== 'no_vehicle' && !m.toLowerCase().includes('no vehicle'));
                if (validNonEmptyModes.length > 0) {
                    const uniqueModes = Array.from(new Set(validNonEmptyModes));
                    if (mode === 'own_car' || !mode || mode === 'No Vehicle Selected' || mode === 'no_vehicle') {
                        mode = uniqueModes.join(' + ');
                    }
                } else if (itemModes.length > 0 && itemModes.every(m => m === 'no_vehicle' || m.toLowerCase().includes('no vehicle'))) {
                    mode = 'No Vehicle Selected';
                }
            }

            if (!mode || String(mode).toLowerCase().includes('no_vehicle') || String(mode).toLowerCase().includes('no vehicle') || String(mode).toLowerCase() === 'none') {
                return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid fa-ban" style="font-size:11px; color:#f87171;"></i>No Vehicle Selected</span>`;
            }

            const parts = parseVehicleModes(mode).filter(p => p !== 'no_vehicle' && !p.includes('no_vehicle'));
            if (parts.length === 0) {
                return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid fa-ban" style="font-size:11px; color:#f87171;"></i>No Vehicle Selected</span>`;
            }

            if (parts.length > 1) {
                return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid fa-route" style="font-size:11px; color:#38bdf8;"></i>${parts.length} Vehicles</span>`;
            }

            const info = getVehicleInfo(parts[0]);
            return `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid ${info.icon}" style="font-size:11px; color:${info.color};"></i>${info.name}</span>`;
        }

        function renderSavedTrips(itineraries) {
            window._isStartingTrip = false;
            try {
                // Prepend queued offline trips and merge offline updates
                const queuedTrips = (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function')
                    ? window.OfflineTripManager.getQueuedTrips()
                    : [];

                const queuedNewTrips = queuedTrips.filter(t => !t.is_offline_update);
                const queuedUpdates = queuedTrips.filter(t => t.is_offline_update && t.update_id);
                const updatesMap = {};
                queuedUpdates.forEach(u => { updatesMap[String(u.update_id)] = u; });

                // Harvest known local item proof photos & states so server re-fetches NEVER wipe them out
                const localItemProofs = {};
                const harvestProofsFromTrips = (trips) => {
                    if (!Array.isArray(trips)) return;
                    trips.forEach(t => {
                        if (!t || !Array.isArray(t.items)) return;
                        t.items.forEach(it => {
                            if (!it || !it.id) return;
                            const itId = String(it.id);
                            const proofs = (Array.isArray(it.proof_images) && it.proof_images.length)
                                ? it.proof_images
                                : (it.proof_image ? [it.proof_image] : []);
                            const hasProofs = (proofs.length > 0);
                            const hasCheckin = Boolean(it.is_location_verified || it.visited_at);
                            if (hasProofs || hasCheckin) {
                                localItemProofs[itId] = {
                                    proof_image: it.proof_image || proofs[0] || null,
                                    proof_images: proofs,
                                    proof_status: it.proof_status || (hasProofs ? 'pending' : null),
                                    is_location_verified: hasCheckin,
                                    visited_at: it.visited_at || null
                                };
                            }
                        });
                    });
                };

                // Check persistent verified states from localStorage
                try {
                    const persistentStates = (typeof window.getPersistentItemStates === 'function') ? window.getPersistentItemStates() : {};
                    Object.keys(persistentStates).forEach(k => {
                        const st = persistentStates[k];
                        if (!st) return;
                        if (!localItemProofs[k]) {
                            localItemProofs[k] = {
                                proof_image: st.proof_image || null,
                                proof_images: st.proof_images || (st.proof_image ? [st.proof_image] : []),
                                proof_status: st.proof_status || 'pending',
                                is_location_verified: Boolean(st.is_location_verified || st.visited_at),
                                visited_at: st.visited_at || null
                            };
                        } else {
                            localItemProofs[k].is_location_verified = Boolean(localItemProofs[k].is_location_verified || st.is_location_verified || st.visited_at);
                            if (!localItemProofs[k].visited_at && st.visited_at) localItemProofs[k].visited_at = st.visited_at;
                            if ((!localItemProofs[k].proof_images || localItemProofs[k].proof_images.length === 0) && st.proof_images && st.proof_images.length > 0) {
                                localItemProofs[k].proof_images = st.proof_images;
                                localItemProofs[k].proof_image = st.proof_image || st.proof_images[0];
                                localItemProofs[k].proof_status = st.proof_status || 'pending';
                            }
                        }
                    });
                } catch (e) { }

                // Check in-memory cached trips
                harvestProofsFromTrips(window._cachedSavedTrips);

                // Check offline checkin queue
                try {
                    const queuedCheckins = (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.getQueue === 'function')
                        ? window.OfflineCheckinManager.getQueue()
                        : ((window.OfflineCheckinManager && typeof window.OfflineCheckinManager.getQueuedCheckins === 'function')
                            ? window.OfflineCheckinManager.getQueuedCheckins()
                            : JSON.parse(localStorage.getItem('intan_elyu_offline_checkins') || '[]'));

                    if (Array.isArray(queuedCheckins)) {
                        queuedCheckins.forEach(q => {
                            if (!q) return;
                            const qImg = q.previewUrl || q.image;
                            const keys = [q.itemId, q.spotId].filter(Boolean).map(String);
                            keys.forEach(targetKey => {
                                const existing = (localItemProofs[targetKey] && localItemProofs[targetKey].proof_images) ? localItemProofs[targetKey].proof_images : [];
                                const merged = qImg ? ((existing.length > 0) ? existing : [qImg]) : existing;
                                localItemProofs[targetKey] = {
                                    proof_image: merged[0] || null,
                                    proof_images: merged,
                                    proof_status: 'pending',
                                    is_location_verified: true,
                                    visited_at: q.capturedAt || new Date().toISOString(),
                                    is_queued: true
                                };
                            });
                        });
                    }
                } catch (e) { }

                // Check localStorage persistent backups
                ['intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips', 'intan_elyu_saved_trips'].forEach(k => {
                    try {
                        const raw = localStorage.getItem(k);
                        if (raw) {
                            const parsed = JSON.parse(raw);
                            const arr = Array.isArray(parsed) ? parsed : (parsed.itineraries || parsed.data || []);
                            harvestProofsFromTrips(arr);
                        }
                    } catch (e) { }
                });

                let serverTrips = (Array.isArray(itineraries) ? itineraries : []).map(t => {
                    let tripObj = t;
                    if (updatesMap[String(t.id)]) {
                        tripObj = {
                            ...t,
                            ...updatesMap[String(t.id)],
                            id: t.id,
                            is_offline_queued: true,
                            is_offline_update: true
                        };
                    }
                    // Deep-merge local proofs into tripObj.items if server item has no proofs yet
                    if (tripObj && Array.isArray(tripObj.items)) {
                        tripObj.items = tripObj.items.map(it => {
                            if (!it) return it;
                            const itId = String(it.id);
                            const spotIdKey = String(it.tourist_spot_id || it.destination_id || (it.destination ? it.destination.id : ''));
                            const saved = localItemProofs[itId] || null;
                            if (saved) {
                                const currentProofs = (Array.isArray(it.proof_images) && it.proof_images.length)
                                    ? it.proof_images
                                    : (it.proof_image ? [it.proof_image] : []);
                                const mergedProofs = currentProofs.length > 0 ? currentProofs : (saved.proof_images || []);
                                const isServerApproved = Boolean(it.is_visited || it.proof_status === 'approved' || (saved && (saved.proof_status === 'approved' || saved.is_visited)));
                                const isServerRejected = (it.proof_status === 'rejected' || (saved && saved.proof_status === 'rejected'));

                                let resolvedStatus;
                                if (isServerApproved) {
                                    resolvedStatus = 'approved';
                                } else if (isServerRejected) {
                                    resolvedStatus = 'rejected';
                                } else if (it.proof_status) {
                                    resolvedStatus = it.proof_status;
                                } else if (saved && saved.proof_status) {
                                    resolvedStatus = saved.proof_status;
                                } else {
                                    resolvedStatus = (mergedProofs.length > 0 ? 'pending' : null);
                                }

                                return {
                                    ...it,
                                    is_visited: isServerApproved ? true : Boolean(it.is_visited || (saved && saved.is_visited)),
                                    proof_image: (mergedProofs && mergedProofs.length > 0) ? mergedProofs[0] : (saved.proof_image || it.proof_image),
                                    proof_images: mergedProofs || [],
                                    proof_status: resolvedStatus,
                                    is_location_verified: Boolean(isServerApproved || it.is_location_verified || (saved && saved.is_location_verified) || it.visited_at || (saved && saved.visited_at)),
                                    visited_at: it.visited_at || (saved ? saved.visited_at : null),
                                    is_offline_queued: Boolean(saved.is_queued)
                                };
                            }
                            return it;
                        });
                    }
                    return tripObj;
                });

                const combinedTrips = [...queuedNewTrips, ...serverTrips].filter(t => !window.isTripDeleted(t));
                // Deduplicate by unique trip id so duplicate store entries never double-render
                const seenTripIds = new Set();
                const dedupedTrips = combinedTrips.filter(t => {
                    if (!t) return false;
                    const tId = String(t.id || t.local_id || t.trip_id || '');
                    if (tId) {
                        if (seenTripIds.has(tId)) return false;
                        seenTripIds.add(tId);
                        if (tId.startsWith('offline_edit_')) seenTripIds.add(tId.replace('offline_edit_', ''));
                    }
                    return true;
                });
                window._cachedSavedTrips = dedupedTrips;
                const list = document.getElementById('saved-trips-list');

                if (!list) return;

                const emptyStateHtml = `
                <div class="empty-state-card" style="margin-top: 30px; margin-bottom: 30px; opacity: 1 !important; visibility: visible !important;">
                    <div class="empty-state-icon" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                        <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                    </div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff; text-wrap: balance;">No Saved Trips Yet</h3>
                    <p style="margin: 0; font-size: 13.5px; color: rgba(255, 255, 255, 0.88); line-height: 1.5; max-width: 260px; text-wrap: pretty;">
                        Your adventure starts here! Explore La Union tourist spots and plan your personalized itinerary.
                    </p>
                    <div style="display:flex; flex-direction:column; gap:10px; width:100%; max-width:280px; margin:14px auto 0;">
                        <button type="button" class="btn-open-map" onclick="if(typeof window.navigateTo==='function') window.navigateTo('itinerary');" style="width:100%; margin:0; justify-content:center;">
                            <i class="fa-solid fa-plus"></i> Create New Trip
                        </button>
                    </div>
                </div>
            `;

                if (!dedupedTrips || dedupedTrips.length === 0) {
                    list.innerHTML = emptyStateHtml;
                    updateScrollviewState();
                    return;
                }

                let activeItineraries = dedupedTrips.filter(trip => trip && trip.status !== 'completed' && !(typeof window.isTripCompletedLocally === 'function' && window.isTripCompletedLocally(trip)));

                if (activeItineraries.length === 0) {
                    list.innerHTML = emptyStateHtml;
                    updateScrollviewState();
                    return;
                }

                // If a trip was just saved, prioritize it at index 0 so user sees it right away
                const justSavedId = sessionStorage.getItem('just_saved_trip_id');
                if (justSavedId) {
                    const matchIdx = activeItineraries.findIndex(t => t && (String(t.id) === String(justSavedId) || String(t.local_id) === String(justSavedId)));
                    if (matchIdx > 0) {
                        const savedItem = activeItineraries.splice(matchIdx, 1)[0];
                        activeItineraries.unshift(savedItem);
                    }
                }

                // Silent signature check: prevent DOM rebuild and flicker if data has not changed
                const currentRenderSig = JSON.stringify(activeItineraries.map(t => [
                    t.id, t.title, t.trip_date, t.companion_males, t.companion_females, t.total_pax,
                    (t.items || []).map(i => [i.id, i.proof_status, i.is_visited, i.is_location_verified, i.proof_image, (i.proof_images || []).length])
                ]));

                const hasExistingTripCards = list.querySelector('.trip-swipe-container') !== null;
                if (!justSavedId && window._lastRenderedTripsSig === currentRenderSig && hasExistingTripCards) {
                    return; // Zero DOM teardown, completely silent refresh
                }
                window._lastRenderedTripsSig = currentRenderSig;

                let html = '';

                activeItineraries.forEach(trip => {
                    if (!trip) return;
                    try {
                        let budgetIndicator = '';
                        if (trip.budget && trip.budget > 0) {
                            const cost = parseFloat(trip.total_cost || 0);
                            const budget = parseFloat(trip.budget);

                            let color = '#10b981'; // Green
                            let statusTitle = `Within Budget: ₱${cost.toFixed(2)} / ₱${budget.toFixed(2)}`;

                            if (cost > budget) {
                                const coverage = (cost > 0) ? (budget / cost) : 0;
                                if (coverage >= 0.8) {
                                    color = '#f59e0b'; // Yellow (Nearing target: 80% to 99% covered)
                                    statusTitle = `Nearing Budget: ₱${budget.toFixed(2)} / ₱${cost.toFixed(2)}`;
                                } else {
                                    color = '#ef4444'; // Red (Over budget > 100%)
                                    statusTitle = `Over Budget by ₱${(cost - budget).toFixed(2)}`;
                                }
                            } else {
                                // Budget meets or exceeds cost: GREEN!
                                color = '#10b981';
                                statusTitle = `Within Budget: ₱${cost.toFixed(2)} / ₱${budget.toFixed(2)}`;
                            }

                            budgetIndicator = `<span style="display:inline-block; width:10px; height:10px; border-radius:50%; background-color:${color}; margin-left:6px; border:none; outline:none; box-shadow:0 0 6px ${color}80;" title="${statusTitle}"></span>`;
                        }

                        const transportBadge = getSavedTripTransportBadge(trip.transport_mode, trip);
                        const safeTitle = trip.title ? trip.title.replace(/"/g, '&quot;').replace(/'/g, "\\'") : 'Saved Trip';

                        const tripType = trip.trip_type || (trip.total_pax > 1 ? 'group' : 'solo');
                        const totalPax = parseInt(trip.total_pax || 1, 10);
                        const males = parseInt(trip.companion_males || 0, 10);
                        const females = parseInt(trip.companion_females || 0, 10);
                        let paxBadge = '';
                        if (tripType === 'group' || totalPax > 1) {
                            paxBadge = `<span style="background:rgba(56,189,248,0.22); border:1px solid rgba(56,189,248,0.38); color:#ffffff; padding:3px 10px; border-radius:100px; font-weight:800; font-size:11.5px; display:inline-flex; align-items:center; gap:5px;" title="Party of ${totalPax} (${males} Males, ${females} Females)"><i class="fa-solid fa-users" style="color:#38bdf8;"></i> ${totalPax} Pax ${males > 0 || females > 0 ? `(${males}M, ${females}F)` : ''}</span>`;
                        } else {
                            paxBadge = `<span style="background:rgba(255,255,255,0.18); color:#ffffff; padding:3px 9px; border-radius:100px; font-weight:700; font-size:11.5px; display:inline-flex; align-items:center; gap:4px;" title="Solo Traveler"><i class="fa-solid fa-user" style="font-size:10px; color:#38bdf8;"></i> Solo</span>`;
                        }

                        html += `
                    <div class="trip-swipe-container" data-trip-id="${trip.id}" data-trip-title="${safeTitle}" style="position:relative; overflow:hidden; border-radius:24px; -webkit-mask-image:-webkit-radial-gradient(white, black); mask-image:radial-gradient(white, black); isolation:isolate; contain:paint; margin-bottom:20px; touch-action:pan-y;">
                        <!-- Red Delete Action Button (Slides smoothly in tandem from right wall) -->
                        <div class="trip-swipe-bg" onclick="event.stopPropagation(); window.confirmDeleteSavedTrip('${trip.id}', this.closest('.trip-swipe-container'), '${safeTitle}');" style="position:absolute; top:0; right:0; bottom:0; width:95px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius:0 24px 24px 0; display:flex; align-items:center; justify-content:center; color:#ffffff; font-size:13px; font-weight:800; gap:6px; z-index:1; cursor:pointer; opacity:0; pointer-events:none; transform:translateX(95px); transition:transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease; touch-action:manipulation;">
                            <i class="fa-solid fa-trash-can"></i> Delete
                        </div>
                        
                        <!-- Rich Glassmorphic Front Card Content (Overlays z-index 2) -->
                        <div class="trip-swipe-content" style="position:relative; z-index:2; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none !important; outline: none !important; border-radius: 24px; padding: 22px; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-radius 0.25s ease; box-shadow: 0 10px 28px rgba(10, 25, 60, 0.28);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                                <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; flex: 1; min-width: 0; word-break: break-word;">${trip.title}</h3>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <button type="button" class="btn-edit-saved-trip" onclick="event.stopPropagation(); window.editSavedTrip('${trip.id}')" ontouchstart="event.stopPropagation();" onpointerdown="event.stopPropagation();" onmousedown="event.stopPropagation();" onmouseover="this.style.background='rgba(255,255,255,0.28)'" onmouseout="this.style.background='rgba(255,255,255,0.18)'" style="background: rgba(255,255,255,0.18); border: none !important; outline: none !important; border-radius: 12px; padding: 6px 14px; color: #ffffff; font-size: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; box-shadow: none !important; position: relative; z-index: 10; pointer-events: auto; touch-action: manipulation;">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                </div>
                            </div>
                            <div style="font-size: 12.5px; color: #ffffff; opacity: 0.95; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="display:inline-flex; align-items:center; gap:5px; font-weight:700;"><i class="fa-regular fa-calendar" style="color: #ffffff;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set'}</span> 
                                ${paxBadge}
                                ${trip.is_offline_queued ? `<span style="background: rgba(245, 158, 11, 0.28); border: 1px solid rgba(245, 158, 11, 0.5); color: #fef08a; padding: 3px 10px; border-radius: 100px; font-weight: 800; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-cloud-arrow-up"></i> Queued (Offline)</span>` : ''}
                                ${trip.budget ? `<span style="background: rgba(255,255,255,0.22); border: none !important; outline: none !important; color: #ffffff; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-coins" style="font-size:10px; color:#fbbf24;"></i>Budget: ₱${parseFloat(trip.budget).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${budgetIndicator}</span>` : ''}
                            </div>
                            <div class="timeline-collapsible" id="timeline-${trip.id}">
                                <div class="timeline-inner">
                                    <div class="timeline">`;
                        let unvisitedCount = 0;
                        let hasQueuedCheckins = false;
                        const isTripCompleted = (trip.status === 'completed');
                        if (trip.items && trip.items.length) {
                            const sortedTripItems = [...trip.items].sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
                            const firstUnvisitedIdx = sortedTripItems.findIndex(i => !(i.is_visited || i.proof_status === 'approved'));
                            sortedTripItems.forEach((item, index) => {
                                if (!item) return;
                                const dest = item.destination || (typeof window.getTouristSpotById === 'function' ? window.getTouristSpotById(item.tourist_spot_id) : null);
                                const destName = (dest && (dest.name || dest.title)) ? String(dest.name || dest.title) : 'Destination';
                                const destEscaped = destName.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                                const spotImgUrl = (typeof window.getDestImage === 'function')
                                    ? window.getDestImage(dest, 300)
                                    : ((dest && (dest.image || dest.photo_url)) || 'assets/img/no_image.svg');
                                
                                // Resolve all proof images into an array
                                let proofImages = [];
                                if (Array.isArray(item.proof_images) && item.proof_images.length > 0) {
                                    proofImages = item.proof_images;
                                } else if (item.proof_image) {
                                    try {
                                        const parsed = JSON.parse(item.proof_image);
                                        if (Array.isArray(parsed)) proofImages = parsed;
                                        else proofImages = [item.proof_image];
                                    } catch (e) {
                                        proofImages = [item.proof_image];
                                    }
                                }

                                const spotIdVal = item.tourist_spot_id || item.destination_id || (item.destination ? item.destination.id : null);
                                const queuedItem = window.OfflineCheckinManager ? window.OfflineCheckinManager.getQueuedItem(item.id, spotIdVal) : null;
                                const isCheckinQueued = Boolean(queuedItem);
                                if (isCheckinQueued) {
                                    hasQueuedCheckins = true;
                                }

                                // If proofImages is empty but queuedItem has an image, include it so the proof is visible
                                if (proofImages.length === 0 && isCheckinQueued && queuedItem && (queuedItem.previewUrl || queuedItem.image)) {
                                    proofImages = [queuedItem.previewUrl || queuedItem.image];
                                }

                                const hasProof = (proofImages.length > 0);
                                const isLocVerified = Boolean(item.visited_at || item.is_location_verified);
                                const proofStatus = item.proof_status || (hasProof ? (item.is_visited ? 'approved' : 'pending') : null);
                                const isPhotoApproved = (proofStatus === 'approved');
                                const isApproved = Boolean(item.is_visited || isPhotoApproved || item.proof_status === 'approved' || (item.is_visited && isLocVerified));
                                const isRejected = (proofStatus === 'rejected');
                                const isPendingProof = !isApproved && !isRejected && !isCheckinQueued && hasProof && (proofStatus === 'pending');
                                const isNextStop = (!isApproved && !isPendingProof && !isCheckinQueued && index === firstUnvisitedIdx);
                                if (!isApproved && !isCheckinQueued) unvisitedCount++;

                                // Build multi-photo gallery strip
                                let photosStripHtml = '';
                                if (proofImages.length > 0) {
                                    const resolvedUrls = proofImages.map(u => (typeof window.resolveProofImageUrl === 'function' ? window.resolveProofImageUrl(u) : u));
                                    const jsonUrlsAttr = JSON.stringify(resolvedUrls).replace(/"/g, '&quot;');
                                    const thumbs = proofImages.map((pUrl, pIdx) => {
                                        const displayUrl = (typeof window.resolveProofImageUrl === 'function') ? window.resolveProofImageUrl(pUrl) : pUrl;
                                        const fallbackUrl = (typeof window.getProofImageFallbackUrl === 'function') ? window.getProofImageFallbackUrl(pUrl) : 'assets/img/no_image.svg';
                                        return `
                                            <div style="position:relative; flex-shrink:0;">
                                                <img src="${displayUrl}" 
                                                    onerror="if(this.dataset.triedFallback!=='1'){this.dataset.triedFallback='1';this.src='${fallbackUrl}';}else{this.onerror=null;this.src='assets/img/no_image.svg';}" 
                                                    onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal(${jsonUrlsAttr}, '${destEscaped}', 'Photo Proof (${pIdx + 1} of ${proofImages.length})', ${pIdx});" 
                                                    alt="Proof Photo ${pIdx + 1}" 
                                                    title="Tap to view proof photo" 
                                                    loading="lazy"
                                                    style="width:52px; height:52px; border-radius:12px; object-fit:cover; border:1.5px solid rgba(255,255,255,0.25); box-shadow:0 4px 12px rgba(0,0,0,0.3); cursor:pointer; display:block;">
                                                ${proofImages.length > 1 ? `<span style="position:absolute; bottom:2px; right:2px; background:rgba(0,0,0,0.7); color:#fff; font-size:8px; font-weight:800; padding:1px 4px; border-radius:4px;">${pIdx + 1}</span>` : ''}
                                            </div>
                                        `;
                                    }).join('');

                                    photosStripHtml = `
                                        <div style="display:flex; align-items:center; gap:8px; overflow-x:auto; padding:4px 0; max-width:100%; -webkit-overflow-scrolling:touch;">
                                            ${thumbs}
                                            ${proofImages.length > 1 ? `<span style="font-size:10px; color:#00f2fe; font-weight:700; white-space:nowrap; margin-left:2px;">${proofImages.length} photos</span>` : ''}
                                        </div>
                                    `;
                                }

                                let queuedThumbnailHtml = '';
                                if (isQueued && queuedItem) {
                                    const qSrc = queuedItem.previewUrl || queuedItem.image || '';
                                    if (qSrc) {
                                        queuedThumbnailHtml = `<img src="${qSrc}" onerror="this.onerror=null; this.src='assets/img/no_image.svg';" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal('${qSrc}', '${destEscaped}', 'Offline Proof (Queued on Device)');" alt="Proof" title="Tap to view offline proof photo" style="width:52px; height:52px; border-radius:12px; object-fit:cover; border:2px solid #f59e0b !important; outline:none !important; box-shadow:0 4px 12px rgba(0,0,0,0.3); flex-shrink:0; cursor:pointer;">`;
                                    } else {
                                        queuedThumbnailHtml = `<div style="width:52px; height:52px; border-radius:12px; background:rgba(245,158,11,0.2); border:1.5px dashed #f59e0b; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fbbf24; flex-shrink:0;"><i class="fa-solid fa-camera" style="font-size:16px;"></i><span style="font-size:8px; font-weight:800; margin-top:2px;">OFFLINE</span></div>`;
                                    }
                                }

                                const sClass = (dest && dest.classification_status) ? dest.classification_status : '';
                                const sMeta = (typeof window.getRewardPointsForClassification === 'function')
                                    ? window.getRewardPointsForClassification(sClass)
                                    : { points: ((sClass === 'EMERGE' || sClass === 'EMERGING') ? 45 : (sClass === 'POTENTIAL' ? 35 : 25)) };

                                const itemMode = item.transport_mode || item.transport_type || '';
                                if (itemMode) {
                                    const isDestMaint = Boolean(
                                        (dest && (dest.is_maintenance === 1 || dest.is_maintenance === true || dest.is_maintenance === '1' || (dest.status && String(dest.status).toLowerCase().includes('maint')))) ||
                                        itemMode === 'suspended'
                                    );

                                    if (isDestMaint) {
                                        html += `
                                        <div style="display:flex; align-items:center; gap:8px; margin:${index === 0 ? '0 0 10px 18px' : '4px 0 10px 18px'};">
                                            <span style="padding:3px 10px; border-radius:100px; font-size:10.5px; font-weight:700; background:#ef4444; color:#ffffff; display:inline-flex; align-items:center; gap:5px; border:none !important; outline:none !important; box-shadow:0 2px 6px rgba(239,68,68,0.3);">
                                                <i class="fa-solid fa-ban" style="color:#ffffff; font-size:10px;"></i>
                                                <span>Unavailable</span>
                                            </span>
                                        </div>`;
                                    } else {
                                        const isNoVehLeg = !itemMode || itemMode === 'no_vehicle' || itemMode.toLowerCase().includes('no vehicle');
                                        if (isNoVehLeg) {
                                            html += `
                                            <div style="display:flex; align-items:center; gap:8px; margin:${index === 0 ? '0 0 10px 18px' : '4px 0 10px 18px'};">
                                                <span style="padding:3px 10px; border-radius:100px; font-size:10.5px; font-weight:700; background:rgba(255,255,255,0.16); color:#fca5a5; display:inline-flex; align-items:center; gap:5px; border:none !important; outline:none !important;">
                                                    <i class="fa-solid fa-ban" style="color:#f87171; font-size:10px;"></i>
                                                    <span>No Vehicle Selected</span>
                                                </span>
                                            </div>`;
                                        } else {
                                            const legModes = parseVehicleModes(itemMode).filter(m => m !== 'no_vehicle' && !m.includes('no_vehicle'));
                                            let vIcon = 'fa-route';
                                            let vName = itemMode;

                                            if (legModes.length > 1) {
                                                vIcon = 'fa-route';
                                                vName = `${legModes.length} Vehicles`;
                                            } else if (legModes.length === 1) {
                                                const info = getVehicleInfo(legModes[0]);
                                                vIcon = info.icon;
                                                vName = info.name;
                                            }

                                            const costNum = parseFloat(item.leg_cost || 0);
                                            const costStr = (costNum > 0) ? `&bull; ₱${costNum.toFixed(2)}` : '';

                                            html += `
                                            <div style="display:flex; align-items:center; gap:8px; margin:${index === 0 ? '0 0 10px 18px' : '4px 0 10px 18px'};">
                                                <span style="padding:3px 10px; border-radius:100px; font-size:10.5px; font-weight:700; background:rgba(255,255,255,0.16); color:#e0f2fe; display:inline-flex; align-items:center; gap:5px; border:none !important; outline:none !important;">
                                                    <i class="fa-solid ${vIcon}" style="color:#00f2fe; font-size:10px;"></i>
                                                    <span>${vName}</span>
                                                    ${costStr ? `<span style="color:#38bdf8; font-weight:800;">${costStr}</span>` : ''}
                                                </span>
                                            </div>`;
                                        }
                                    }
                                }

                                html += `
                                <div class="timeline-item ${isApproved ? 'completed' : (isPendingProof ? 'pending' : (isNextStop ? 'is-next-stop' : ''))}" style="margin-bottom: 12px;">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content" style="padding:14px; background: rgba(255,255,255,0.12); border: none !important; outline: none !important; border-radius: 16px; display:flex; flex-direction:column; gap:10px;">
                                        <div style="display:flex; gap:12px; align-items:flex-start;">
                                            <div class="saved-trip-spot-thumb" style="width:56px; height:56px; border-radius:14px; overflow:hidden; flex-shrink:0; background:rgba(0,0,0,0.25); position:relative; box-shadow:0 3px 10px rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.18);">
                                                <img src="${spotImgUrl}" alt="${destEscaped}" loading="lazy" style="width:100%; height:100%; object-fit:cover; display:block;" onerror="this.onerror=null; this.src='assets/img/no_image.svg';">
                                            </div>
                                            <div style="flex:1; min-width:0;">
                                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                    <h4 style="margin:0; font-size:15px; font-weight:800; color:#ffffff; line-height:1.25;">${destName}</h4>
                                                    ${isNextStop ? `<span style="padding: 2px 8px; border-radius: 100px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: linear-gradient(135deg, #00f2fe, #0284c7); border: none !important; outline: none !important;"><i class="fa-solid fa-location-dot" style="font-size:8px;"></i> Next Stop</span>` : ''}
                                                    ${(dest && dest.classification_status) ? `<span style="padding: 2px 7px; border-radius: 100px; font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#0284c7' : (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; border: none !important; outline: none !important;">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : ''}
                                                </div>
                                                ${dest && (dest.municipality || dest.location) ? `
                                                    <div style="font-size:11.5px; color:rgba(255,255,255,0.8); display:flex; align-items:center; gap:4px; margin-top:3px;">
                                                        <i class="fa-solid fa-location-dot" style="color:#00f2fe; font-size:10px; flex-shrink:0;"></i>
                                                        <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${dest.municipality ? dest.municipality + ', La Union' : dest.location}</span>
                                                    </div>` : ''}
                                            </div>
                                        </div>
                                        ${(dest && (dest.accessible_by_private_vehicle === 0 || dest.accessible_by_private_vehicle === false)) ? `<div style="background:rgba(239, 68, 68, 0.15); border:none !important; outline:none !important; border-radius:10px; padding:8px 12px; display:flex; gap:8px; align-items:flex-start; margin-top:4px;"><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444; font-size:13px; margin-top:2px;"></i><div><h5 style="margin:0 0 2px 0; font-size:11px; font-weight:800; color:#ef4444; text-transform:uppercase;">Inaccessible by Private Car</h5><p style="margin:0; font-size:10px; color:#ffffff; opacity:0.9; line-height:1.3;">Prepare to hike or use specialized local transport.</p></div></div>` : ''}

                                        ${isApproved ?
                                        `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                                <span style="color:#34c759; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:5px; background:rgba(52,199,89,0.18); padding:4px 10px; border-radius:100px;">
                                                    <i class="fa-solid fa-circle-check"></i> Visit Verified
                                                </span>
                                                <button type="button" data-spot-id="${item.tourist_spot_id || (dest ? dest.id : '')}" data-spot-classification="${sClass}" onclick="event.stopPropagation(); window.openWriteTestimonyModal('${item.tourist_spot_id || (dest ? dest.id : '')}', this)" style="background:rgba(255,255,255,0.16); border:none !important; outline:none !important; color:#ffffff; font-size:11px; font-weight:800; padding:6px 14px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:none; flex-shrink:0;">
                                                    ${(window.userReviewedSpotIds && window.userReviewedSpotIds.has(Number(item.tourist_spot_id || (dest ? dest.id : '')))) ? '<i class="fa-solid fa-check" style="font-size:10px; margin-right:4px;"></i> Reviewed' : '<i class="fa-solid fa-pen" style="font-size:10px;"></i> Review'}
                                                </button>
                                            </div>
                                            ${photosStripHtml}
                                        </div>` :
                                        (isRejected ?
                                            `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                                    <div>
                                                        <span style="color:#ef4444; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:5px;">
                                                            <i class="fa-solid fa-circle-xmark"></i> Photo Proof Rejected
                                                        </span>
                                                        <span style="font-size:10px; color:#fca5a5; display:block; margin-top:2px;">${item.rejection_reason || 'Please upload a clearer photo taken at this destination.'}</span>
                                                    </div>
                                                    <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #ef4444, #dc2626); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')">
                                                        <i class="fa-solid fa-camera" style="margin-right:4px;"></i> Re-upload Proof
                                                    </button>
                                                </div>
                                                ${photosStripHtml}
                                            </div>` :
                                            (isQueued ?
                                                `<div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                                                    ${queuedThumbnailHtml}
                                                    <div>
                                                        <span style="background:rgba(245,158,11,0.25); border:none !important; outline:none !important; color:#fbbf24; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;">
                                                            <i class="fa-solid fa-clock-rotate-left"></i> Queued for Sync (Offline)
                                                        </span>
                                                        <span style="font-size:10px; color:#ffffff; opacity:0.85; display:block; margin-top:4px;"><i class="fa-solid fa-camera" style="color:#fbbf24; margin-right:4px;"></i>Saved on device &bull; Auto-syncs when online</span>
                                                    </div>
                                                </div>` :
                                            (isPhotoApproved && !isLocVerified ?
                                                `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                                        <div>
                                                            <span style="background:rgba(52,199,89,0.18); border:none !important; outline:none !important; color:#34c759; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                                <i class="fa-solid fa-circle-check"></i> Photo Approved &bull; ⚠️ Check-in Required
                                                            </span>
                                                            <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">Photo proof verified! Please check in to complete this stop.</span>
                                                        </div>
                                                        <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${dest?.latitude || 'null'}, ${dest?.longitude || 'null'}, this)">
                                                            <i class="fa-solid fa-location-crosshairs" style="margin-right:4px;"></i> Check In
                                                        </button>
                                                    </div>
                                                    ${photosStripHtml}
                                                </div>` :
                                            (isLocVerified && isPendingProof ?
                                                `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                                    <div>
                                                        <span style="background:rgba(255,149,0,0.22); border:none !important; outline:none !important; color:#ff9f0a; font-size:11.5px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                            <i class="fa-solid fa-hourglass-half"></i> Pending Approval
                                                        </span>
                                                        <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">
                                                            <i class="fa-solid fa-circle-check" style="color:#34c759; margin-right:3px;"></i> Location Verified &bull; Photos submitted
                                                        </span>
                                                    </div>
                                                    ${photosStripHtml}
                                                </div>` :
                                            (!isLocVerified && (isPendingProof || hasProof) ?
                                                `<div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                                        <div>
                                                            <span style="background:rgba(245,158,11,0.22); border:none !important; outline:none !important; color:#fbbf24; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                                <i class="fa-solid fa-camera"></i> Photo Ready &bull; ⚠️ GPS Check-in Required
                                                            </span>
                                                            <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">Please check in on-site to verify location</span>
                                                        </div>
                                                        <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${dest?.latitude || 'null'}, ${dest?.longitude || 'null'}, this)">
                                                            <i class="fa-solid fa-location-crosshairs" style="margin-right:4px;"></i> Check In
                                                        </button>
                                                    </div>
                                                    ${photosStripHtml}
                                                </div>` :
                                            (isLocVerified && !hasProof ?
                                                `<div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:4px; flex-wrap:wrap;">
                                                    <div>
                                                        <span style="background:rgba(52,199,89,0.18); border:none !important; outline:none !important; color:#34c759; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:5px;">
                                                            <i class="fa-solid fa-circle-check"></i> Checked In &bull; ⚠️ Photo Proof Required
                                                        </span>
                                                        <span style="font-size:10px; color:#ffffff; opacity:0.8; display:block; margin-top:4px;">Take or upload photos to complete check-in</span>
                                                    </div>
                                                    <button type="button" class="btn-primary" style="padding:7px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer;" onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')">
                                                        <i class="fa-solid fa-camera" style="margin-right:4px;"></i> Upload Proof
                                                    </button>
                                                </div>` :
                                                `<div style="display:flex; align-items:center; gap:8px; margin-top:6px; flex-wrap:wrap;">
                                                    <button type="button" class="btn-primary" style="padding:8px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:linear-gradient(135deg, #00f2fe, #0284c7); border:none !important; outline:none !important; color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" onclick="event.stopPropagation(); window.executeLocationCheckIn('${item.id}', '${destEscaped}', ${dest?.latitude || 'null'}, ${dest?.longitude || 'null'}, this)">
                                                        <i class="fa-solid fa-location-crosshairs"></i> Check In
                                                    </button>
                                                    <button type="button" class="btn-primary" style="padding:8px 14px; font-size:11.5px; font-weight:800; border-radius:100px; background:rgba(255,255,255,0.18); border:none !important; outline:none !important; color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" onclick="event.stopPropagation(); window.openProofUploadModal('${item.id}', '${destEscaped}')">
                                                        <i class="fa-solid fa-camera"></i> Upload Proof
                                                    </button>
                                                </div>`)))))
                                        }
                                    </div>
                                </div>`;
                            });
                        } else {
                            html += `<p style="font-size:13px; color:#ffffff; opacity:0.85; margin:10px 0;">No destinations in this trip.</p>`;
                        }



                        html += `</div></div></div>`; // Close timeline, timeline-inner, and timeline-collapsible

                        // Action buttons
                        html += `<div style="display:flex; gap:8px; margin-top:16px; flex-wrap:wrap; align-items:center;">`;

                        // View Details button
                        html += `
                        <button class="btn-primary" style="flex:1; min-width:105px; background: rgba(255, 255, 255, 0.22); border: none !important; outline: none !important; color: #ffffff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; transition: all 0.25s ease;" onclick="window.toggleTripDetails('${trip.id}')">
                            <i class="fa-solid fa-chevron-down" id="chevron-${trip.id}" style="margin-right:6px; transition:transform 0.3s ease;"></i> View Details
                        </button>`;

                        // If trip is queued offline, or has queued offline check-ins / photos, show prominent "Sync Now" button
                        if (trip.is_offline_queued || hasQueuedCheckins) {
                            html += `
                            <button type="button" class="btn-primary" id="btn-sync-${trip.id}" onclick="event.stopPropagation(); window.syncSingleOfflineTrip('${trip.id}', this)" style="flex:1; min-width:115px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none !important; outline: none !important; color: #ffffff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Sync Now
                            </button>`;
                        }

                        // Start / Complete button wrapper (always visible)
                        html += `<div class="start-collapsible" id="start-wrapper-${trip.id}" style="flex:1.2; min-width:120px;">`;
                        if (isTripCompleted) {
                            html += `
                            <button class="btn-primary" style="width:100%; white-space:nowrap; background: rgba(16,185,129,0.25); border: none !important; outline: none !important; color: #ffffff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: default;" disabled>
                                <i class="fa-solid fa-circle-check" style="margin-right:6px; color:#34c759;"></i> Completed
                            </button>`;
                        } else if (!trip.is_offline_queued && unvisitedCount === 0 && trip.items && trip.items.length > 0) {
                            html += `
                            <button class="btn-primary" id="btn-complete-trip-${trip.id}" style="width:100%; white-space:nowrap; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none !important; outline: none !important; color: #fff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; box-shadow: none; cursor: pointer;" onclick="window.markTripCompleted('${trip.id}')">
                                <i class="fa-solid fa-flag-checkered" style="margin-right:6px;"></i> Complete
                            </button>`;
                        } else {
                            html += `
                            <button type="button" class="btn-primary" style="width:100%; white-space:nowrap; background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border: none !important; outline: none !important; color: #fff; padding: 13px 14px; border-radius: 14px; font-weight: 800; font-size: 13px; box-shadow: none; cursor: pointer;" onclick="event.stopPropagation(); window.startTrip('${trip.id}', this)">
                                <i class="fa-solid fa-play" style="margin-right:6px;"></i> Start Trip
                            </button>`;
                        }
                        html += `</div>`; // Close start-collapsible
                        html += `</div>`; // Close action buttons flex

                        html += `</div></div>`; // Close trip-swipe-content and trip-swipe-container
                    } catch (tripErr) {
                        console.error("Error rendering saved trip item:", tripErr, trip);
                    }
                });

                list.innerHTML = html || emptyStateHtml;
                updateScrollviewState();
                initSavedTripsSwipe();
                if (typeof window.syncReviewedButtons === 'function') {
                    window.syncReviewedButtons();
                }

                if (typeof window.checkAndManageApprovalPolling === 'function') {
                    window.checkAndManageApprovalPolling(dedupedTrips);
                }

                // If a trip was just saved, auto-expand it smoothly so user sees it instantly
                const justSavedId = sessionStorage.getItem('just_saved_trip_id');
                if (justSavedId) {
                    sessionStorage.removeItem('just_saved_trip_id');
                    setTimeout(() => {
                        const savedContainer = document.querySelector(`.trip-swipe-container[data-trip-id="${justSavedId}"]`);
                        if (savedContainer) {
                            window.toggleTripDetails(justSavedId);
                            savedContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 80);
                }
            } catch (fatalErr) {
                console.error("Fatal error rendering saved trips:", fatalErr);
                const list = document.getElementById('saved-trips-list');
                if (list) {
                    list.innerHTML = `
                    <div class="empty-state-card" style="margin-top: 30px; margin-bottom: 30px; opacity: 1 !important; visibility: visible !important;">
                        <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 8px; border: 1px solid rgba(239, 68, 68, 0.3);">
                            <i class="fa-solid fa-circle-exclamation" style="font-size: 32px; color: #ef4444;"></i>
                        </div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #ffffff;">Unable to Display Trips</h3>
                        <p style="margin: 0; font-size: 13px; color: rgba(148,163,184,0.9); line-height: 1.45; max-width: 260px;">Something went wrong while displaying your trips. Tap below to reload.</p>
                        <button type="button" class="btn-cta-accent-10" onclick="if(typeof window.loadSavedTrips==='function') window.loadSavedTrips(true);" style="margin-top: 10px; padding: 12px 24px; border-radius: 100px; font-size: 14px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <i class="fa-solid fa-rotate"></i> Retry
                        </button>
                    </div>
                `;
                }
            }
        };

        window.toggleTripDetails = function (tripId) {
            const timeline = document.getElementById('timeline-' + tripId);
            const chevron = document.getElementById('chevron-' + tripId);
            const startWrapper = document.getElementById('start-wrapper-' + tripId);

            if (timeline && chevron) {
                if (!timeline.classList.contains('expanded')) {
                    timeline.classList.add('expanded');
                    chevron.style.transform = 'rotate(180deg)';
                    if (startWrapper) startWrapper.classList.add('expanded');
                } else {
                    timeline.classList.remove('expanded');
                    chevron.style.transform = 'rotate(0deg)';
                    if (startWrapper) startWrapper.classList.remove('expanded');
                }
                if (typeof window.updateSavedTripsScrollview === 'function') {
                    requestAnimationFrame(window.updateSavedTripsScrollview);
                    setTimeout(window.updateSavedTripsScrollview, 350);
                }
            }
        };

        window.syncSingleOfflineTrip = async function (tripId, btnElement) {
            if (!navigator.onLine || window.isAppOnline === false) {
                if (typeof showToast === 'function') {
                    showToast("You are currently offline. Connect to the internet to sync to cloud.", "warning", 3500);
                }
                return;
            }

            let origHtml = '';
            if (btnElement) {
                origHtml = btnElement.innerHTML;
                btnElement.disabled = true;
                btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Syncing...';
            }

            try {
                let syncedAny = false;
                // 1. Sync trip if it's queued offline
                if (window.OfflineTripManager && typeof window.OfflineTripManager.syncSingleTrip === 'function') {
                    const ok = await window.OfflineTripManager.syncSingleTrip(tripId);
                    if (ok) syncedAny = true;
                }
                // 2. Sync queued checkins and photo proofs
                if (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.processQueue === 'function') {
                    const chkCount = await window.OfflineCheckinManager.processQueue();
                    if (chkCount && chkCount > 0) syncedAny = true;
                }
                // 3. Sync offline completed trips if any
                if (window.OfflineCompletedTripsManager && typeof window.OfflineCompletedTripsManager.processQueue === 'function') {
                    const compCount = await window.OfflineCompletedTripsManager.processQueue();
                    if (compCount && compCount > 0) syncedAny = true;
                }

                if (typeof showToast === 'function') {
                    showToast(syncedAny ? "Trip data successfully synced to cloud!" : "Trip is up to date with cloud!", "success", 4000);
                }
                window.dispatchEvent(new CustomEvent('tripsSynced'));
                window.dispatchEvent(new CustomEvent('checkins-synced'));
                if (typeof window.fetchSavedTrips === 'function') {
                    await window.fetchSavedTrips(true);
                }
            } catch (err) {
                console.error("Manual trip sync failed:", err);
                if (typeof showToast === 'function') {
                    showToast("Could not sync trip. Please check your internet connection.", "error", 4000);
                }
            } finally {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = origHtml || '<i class="fa-solid fa-cloud-arrow-up"></i> Sync Now';
                }
            }
        };

        window._isStartingTrip = false;

        window.startTrip = function (tripId, btnElement) {
            if (window._isStartingTrip) return;
            window._isStartingTrip = true;

            const btn = btnElement || (typeof event !== 'undefined' && event && event.target ? event.target.closest('button') : null);
            let prevHtml = '';
            if (btn) {
                prevHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Starting...';
            }

            // Automatic failsafe: Re-enable button & reset starting flag after 2.5s
            setTimeout(() => {
                window._isStartingTrip = false;
                if (btn) {
                    btn.disabled = false;
                    if (prevHtml) btn.innerHTML = prevHtml;
                }
            }, 2500);

            let transportParam = '';
            if (window._cachedSavedTrips) {
                const found = window._cachedSavedTrips.find(t => t.id == tripId || t.local_id == tripId);
                if (found) {
                    window.currentTrip = found;
                    if (found.transport_mode) {
                        sessionStorage.setItem('active_trip_transport_' + tripId, found.transport_mode);
                        localStorage.setItem('selected_trip_vehicle_' + tripId, found.transport_mode);
                        transportParam = '&transport=' + encodeURIComponent(found.transport_mode);
                    }
                }
            }

            sessionStorage.setItem('active_trip_id', String(tripId));
            window.currentTripId = String(tripId);

            const curParams = new URLSearchParams(window.location.search);
            const appParam = curParams.get('app') ? '&app=' + encodeURIComponent(curParams.get('app')) : '';

            if (typeof showToast === 'function') showToast("Starting trip navigation...");

            if (window._savedTripsInterval) {
                clearInterval(window._savedTripsInterval);
                window._savedTripsInterval = null;
            }

            setTimeout(() => {
                const target = 'trip_map&trip_id=' + encodeURIComponent(tripId) + transportParam + appParam;
                if (typeof window.navigateTo === 'function') {
                    window.navigateTo(target);
                } else {
                    window.location.href = '?view=trip_map&trip_id=' + encodeURIComponent(tripId) + transportParam + appParam;
                }
                // Release starting lock once navigation has taken place
                setTimeout(() => { window._isStartingTrip = false; }, 300);
            }, 180);
        };

        // window.markTripCompleted is defined below to open confirmation modal and showTripCompletionReviewModal

        // --- MULTI-PHOTO PROOF & INDEPENDENT LOCATION CHECK-IN HANDLERS ---
        window.selectedProofPhotos = [];
        window._currentProofItemId = null;
        window._currentProofSpotName = null;

        window.openProofUploadModal = function (itemId, spotName = '') {
            if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
                window.promptGuestLogin('photo');
                return;
            }

            document.body.classList.add('modal-blurred');
            window._currentProofItemId = itemId;
            window._currentProofSpotName = spotName;
            window.selectedProofPhotos = [];

            const titleEl = document.getElementById('proof-spot-title');
            if (titleEl) {
                titleEl.innerHTML = `<i class="fa-solid fa-camera" style="color:#38bdf8; font-size:17px;"></i> ${spotName || 'Upload Photo Proof'}`;
            }

            const input = document.getElementById('proof-upload-input');
            if (input) input.value = '';

            window.renderProofThumbnailsGrid();

            const modal = document.getElementById('proof-upload-modal');
            if (modal) modal.style.display = 'flex';
        };

        window.closeProofUploadModal = function () {
            document.body.classList.remove('modal-blurred');
            const modal = document.getElementById('proof-upload-modal');
            if (modal) modal.style.display = 'none';
            window.selectedProofPhotos = [];
            window._currentProofItemId = null;
            window._currentProofSpotName = null;

            const btn = document.getElementById('btn-submit-proof-photos');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit';
                btn.disabled = false;
            }
        };

        // Alias for backwards compatibility
        window.openCheckinModal = window.openProofUploadModal;
        window.closeCheckinModal = window.closeProofUploadModal;

        window.compressImageFile = async function (fileOrBlob, maxDimension = 1280, quality = 0.8) {
            return new Promise((resolve) => {
                if (!fileOrBlob || !fileOrBlob.type || !fileOrBlob.type.startsWith('image/')) {
                    return resolve(fileOrBlob);
                }
                const img = new Image();
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.onload = () => {
                        let width = img.width;
                        let height = img.height;

                        if (width > maxDimension || height > maxDimension) {
                            if (width > height) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            } else {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob((blob) => {
                            if (!blob) {
                                resolve(fileOrBlob);
                                return;
                            }
                            const name = (fileOrBlob.name || 'proof_' + Date.now() + '.jpg').replace(/\.[^/.]+$/, "") + ".jpg";
                            const compressedFile = new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
                            resolve(compressedFile);
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = () => resolve(fileOrBlob);
                    img.src = e.target.result;
                };
                reader.onerror = () => resolve(fileOrBlob);
                reader.readAsDataURL(fileOrBlob);
            });
        };

        window.renderProofThumbnailsGrid = function () {
            const emptyPrompt = document.getElementById('proof-empty-prompt');
            const grid = document.getElementById('proof-thumbnails-grid');
            const countBadge = document.getElementById('proof-count-badge');
            const submitBtn = document.getElementById('btn-submit-proof-photos');

            const total = window.selectedProofPhotos.length;
            if (countBadge) {
                countBadge.textContent = `${total} / 5 photos`;
            }

            if (submitBtn) {
                submitBtn.innerHTML = `<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit ${total > 0 ? `(${total})` : ''}`;
            }

            if (total === 0) {
                if (emptyPrompt) emptyPrompt.style.display = 'block';
                if (grid) {
                    grid.style.display = 'none';
                    grid.innerHTML = '';
                }
                return;
            }

            if (emptyPrompt) emptyPrompt.style.display = 'none';
            if (grid) {
                grid.style.display = 'grid';
                let html = '';

                window.selectedProofPhotos.forEach((item, index) => {
                    html += `
                        <div style="position:relative; aspect-ratio:1/1; border-radius:14px; overflow:hidden; background:#0f172a; box-shadow:0 3px 8px rgba(0,0,0,0.15); border:1.5px solid #e2e8f0;">
                            <img src="${item.dataUrl}" alt="Photo ${index + 1}" style="width:100%; height:100%; object-fit:cover; display:block;">
                            <span style="position:absolute; bottom:4px; left:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:9px; font-weight:800; padding:2px 6px; border-radius:6px;">
                                ${index + 1}
                            </span>
                            <button type="button" onclick="event.stopPropagation(); window.removeProofPhotoAtIndex(${index})" title="Remove Photo"
                                style="position:absolute; top:4px; right:4px; width:26px; height:26px; border-radius:50%; background:rgba(239,68,68,0.95); color:#fff; border:none; outline:none; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.25);">
                                <i class="fa-solid fa-xmark" style="font-size:12px;"></i>
                            </button>
                        </div>
                    `;
                });

                if (total < 5) {
                    html += `
                        <div onclick="window.addProofPhotoFromSource('gallery')"
                            style="aspect-ratio:1/1; border:2px dashed #94a3b8; border-radius:14px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                            <i class="fa-solid fa-plus" style="font-size:20px; color:#0284c7; margin-bottom:4px;"></i>
                            <span style="font-size:11px; font-weight:700; color:#475569;">Add Photo</span>
                        </div>
                    `;
                }

                grid.innerHTML = html;
            }
        };

        window.removeProofPhotoAtIndex = function (index) {
            if (index >= 0 && index < window.selectedProofPhotos.length) {
                window.selectedProofPhotos.splice(index, 1);
                window.renderProofThumbnailsGrid();
            }
        };

        window.addProofPhotoFromSource = async function (source) {
            if (window.selectedProofPhotos.length >= 5) {
                if (typeof showToast === 'function') showToast('Maximum 5 photos allowed.');
                return;
            }

            const isCapacitorNative = Boolean(
                window.Capacitor &&
                typeof window.Capacitor.isNativePlatform === 'function' &&
                window.Capacitor.isNativePlatform() &&
                window.Capacitor.Plugins &&
                window.Capacitor.Plugins.Camera
            );

            if (isCapacitorNative) {
                try {
                    const cameraPlugin = window.Capacitor.Plugins.Camera;
                    const image = await cameraPlugin.getPhoto({
                        quality: 80,
                        width: 1280,
                        height: 1280,
                        allowEditing: false,
                        resultType: 'dataUrl',
                        source: source === 'camera' ? 'CAMERA' : 'PHOTOS'
                    });

                    if (image && image.dataUrl) {
                        const res = await fetch(image.dataUrl);
                        const blob = await res.blob();
                        const rawFile = new File([blob], 'proof_' + Date.now() + '.jpg', { type: blob.type || 'image/jpeg' });
                        const compressed = await window.compressImageFile(rawFile, 1280, 0.8);
                        window.selectedProofPhotos.push({
                            file: compressed,
                            dataUrl: image.dataUrl
                        });
                        window.renderProofThumbnailsGrid();
                    }
                } catch (err) {
                    console.warn('Capacitor Camera cancel or error:', err);
                }
            } else {
                const input = document.getElementById('proof-upload-input');
                if (!input) return;

                if (source === 'camera') {
                    input.setAttribute('capture', 'environment');
                    input.removeAttribute('multiple');
                } else {
                    input.removeAttribute('capture');
                    input.setAttribute('multiple', 'multiple');
                }
                input.click();
            }
        };

        window.handleProofPhotosSelected = async function (input) {
            if (!input.files || input.files.length === 0) return;

            const remainingSlots = 5 - window.selectedProofPhotos.length;
            if (remainingSlots <= 0) {
                if (typeof showToast === 'function') showToast('Maximum 5 photos allowed.');
                return;
            }

            const filesToProcess = Array.from(input.files).slice(0, remainingSlots);

            for (const file of filesToProcess) {
                try {
                    const compressed = await window.compressImageFile(file, 1280, 0.8);
                    const dataUrl = await new Promise((res) => {
                        const r = new FileReader();
                        r.onload = (e) => res(e.target.result);
                        r.readAsDataURL(compressed);
                    });
                    window.selectedProofPhotos.push({
                        file: compressed,
                        dataUrl: dataUrl
                    });
                } catch (e) {
                    console.warn('Failed to process file:', e);
                }
            }

            input.value = '';
            window.renderProofThumbnailsGrid();
        };

        window.applyPhotoProofUpdateLocally = function (itemId, proofUrls, proofStatus = 'pending') {
            if (!itemId) return;
            const targetIdStr = String(itemId);
            const urls = Array.isArray(proofUrls) ? proofUrls : (proofUrls ? [proofUrls] : []);
            if (urls.length === 0) return;

            const parentTrip = (window._cachedSavedTrips || []).find(t => (t.items || []).some(i => String(i.id) === targetIdStr));
            const targetItem = parentTrip ? (parentTrip.items || []).find(i => String(i.id) === targetIdStr) : null;
            const spotId = targetItem ? (targetItem.tourist_spot_id || targetItem.destination_id || (targetItem.destination ? targetItem.destination.id : null)) : null;

            // Persist to dedicated override storage so page refresh NEVER loses the uploaded photo
            if (typeof window.savePersistentItemState === 'function') {
                window.savePersistentItemState(itemId, {
                    proof_image: urls[0],
                    proof_images: urls,
                    proof_status: proofStatus,
                    spot_id: spotId
                });
            }

            const updateItemInTrips = (tripsList) => {
                if (!Array.isArray(tripsList)) return tripsList;
                return tripsList.map(trip => {
                    if (!trip || !Array.isArray(trip.items)) return trip;
                    const updatedItems = trip.items.map(it => {
                        if (!it) return it;
                        const itIdStr = String(it.id || '');
                        if (itIdStr === targetIdStr || (targetIdStr && itIdStr && targetIdStr.includes(itIdStr))) {
                            return {
                                ...it,
                                proof_image: urls[0],
                                proof_images: urls,
                                proof_status: proofStatus,
                                rejection_reason: null
                            };
                        }
                        return it;
                    });
                    return {
                        ...trip,
                        items: updatedItems
                    };
                });
            };

            // 1. Update in-memory cached saved trips and re-render cards immediately
            if (Array.isArray(window._cachedSavedTrips)) {
                window._cachedSavedTrips = updateItemInTrips(window._cachedSavedTrips);
                if (typeof renderSavedTrips === 'function') {
                    renderSavedTrips(window._cachedSavedTrips);
                }
            }

            // 2. Update all local persistent storage backups
            ['intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips', 'intan_elyu_saved_trips'].forEach(k => {
                try {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (Array.isArray(parsed)) {
                            localStorage.setItem(k, JSON.stringify(updateItemInTrips(parsed)));
                        } else if (parsed && Array.isArray(parsed.itineraries)) {
                            parsed.itineraries = updateItemInTrips(parsed.itineraries);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        } else if (parsed && Array.isArray(parsed.data)) {
                            parsed.data = updateItemInTrips(parsed.data);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        }
                    }
                } catch (e) { }
            });

            // 3. Update all dynamic token-scoped storage keys
            try {
                for (let i = 0; i < localStorage.length; i++) {
                    const k = localStorage.key(i);
                    if (k && (k.startsWith('saved_trips_') || k.startsWith('dashboard_trips_') || k.startsWith('dashboard_saved_trips_'))) {
                        try {
                            const raw = localStorage.getItem(k);
                            if (raw) {
                                const parsed = JSON.parse(raw);
                                if (Array.isArray(parsed)) {
                                    localStorage.setItem(k, JSON.stringify(updateItemInTrips(parsed)));
                                } else if (parsed && Array.isArray(parsed.itineraries)) {
                                    parsed.itineraries = updateItemInTrips(parsed.itineraries);
                                    localStorage.setItem(k, JSON.stringify(parsed));
                                } else if (parsed && Array.isArray(parsed.data)) {
                                    parsed.data = updateItemInTrips(parsed.data);
                                    localStorage.setItem(k, JSON.stringify(parsed));
                                }
                            }
                        } catch (e) { }
                    }
                }
            } catch (e) { }
        };

        window.applyLocationCheckinLocally = function (itemId) {
            if (!itemId) return;
            const targetIdStr = String(itemId);
            const nowIso = new Date().toISOString();

            // Persist to dedicated override storage so page refresh NEVER resets check-in status
            if (typeof window.savePersistentItemState === 'function') {
                window.savePersistentItemState(itemId, {
                    is_location_verified: true,
                    visited_at: nowIso
                });
            }

            const updateItemInTrips = (tripsList) => {
                if (!Array.isArray(tripsList)) return tripsList;
                return tripsList.map(trip => {
                    if (!trip || !Array.isArray(trip.items)) return trip;
                    const updatedItems = trip.items.map(it => {
                        if (!it) return it;
                        const itIdStr = String(it.id || '');
                        if (itIdStr === targetIdStr || (targetIdStr && itIdStr && targetIdStr.includes(itIdStr))) {
                            return {
                                ...it,
                                is_location_verified: true,
                                visited_at: it.visited_at || nowIso
                            };
                        }
                        return it;
                    });
                    return {
                        ...trip,
                        items: updatedItems
                    };
                });
            };

            if (Array.isArray(window._cachedSavedTrips)) {
                window._cachedSavedTrips = updateItemInTrips(window._cachedSavedTrips);
                if (typeof renderSavedTrips === 'function') {
                    renderSavedTrips(window._cachedSavedTrips);
                }
            }

            ['intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips', 'intan_elyu_saved_trips'].forEach(k => {
                try {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (Array.isArray(parsed)) {
                            localStorage.setItem(k, JSON.stringify(updateItemInTrips(parsed)));
                        } else if (parsed && Array.isArray(parsed.itineraries)) {
                            parsed.itineraries = updateItemInTrips(parsed.itineraries);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        } else if (parsed && Array.isArray(parsed.data)) {
                            parsed.data = updateItemInTrips(parsed.data);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        }
                    }
                } catch (e) { }
            });
        };

        window.submitProofPhotos = async function () {
            if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
                window.closeProofUploadModal();
                window.promptGuestLogin('photo');
                return;
            }

            const itemId = window._currentProofItemId;
            if (!itemId) return;

            if (window.selectedProofPhotos.length === 0) {
                if (typeof showToast === 'function') showToast('Please select or capture at least one photo proof.');
                return;
            }

            const btn = document.getElementById('btn-submit-proof-photos');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Submitting...';
                btn.disabled = true;
            }

            // Extract preview data URLs for zero-latency instant optimistic display
            const previewUrls = window.selectedProofPhotos.map(p => p.dataUrl).filter(Boolean);
            const firstFile = window.selectedProofPhotos[0].file;
            const spotName = window._currentProofSpotName || 'Destination';

            // Optimistically update memory and DOM right now!
            window.applyPhotoProofUpdateLocally(itemId, previewUrls, 'pending');

            const parentTrip = (window._cachedSavedTrips || []).find(t => (t.items || []).some(i => String(i.id) === String(itemId)));
            const targetItem = parentTrip ? (parentTrip.items || []).find(i => String(i.id) === String(itemId)) : null;
            const spotId = targetItem ? (targetItem.tourist_spot_id || targetItem.destination_id || (targetItem.destination ? targetItem.destination.id : null)) : null;
            const tripId = parentTrip ? parentTrip.id : null;

            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const isOfflineItem = !itemId || 
                String(itemId).startsWith('offline_') || 
                !navigator.onLine || 
                window.isAppOnline === false ||
                !token ||
                token === 'guest_token' ||
                token.startsWith('guest_') ||
                token.startsWith('offline_');

            // If item is created offline or app is offline, store in OfflineCheckinManager and finish instantly!
            if (isOfflineItem) {
                if (window.OfflineCheckinManager) {
                    await window.OfflineCheckinManager.queueOfflineCheckin({
                        type: 'itinerary',
                        itemId: itemId,
                        spotId: spotId,
                        itineraryId: tripId,
                        spotName: spotName,
                        lat: window.myLat || 16.6159,
                        lng: window.myLng || 120.3209,
                        image: firstFile,
                        previewUrl: previewUrls[0] || null,
                        capturedAt: new Date().toISOString()
                    });
                }
                window.closeProofUploadModal();
                if (typeof showToast === 'function') {
                    showToast('📍 Photo proof saved on device! Auto-syncs when online.', 'success');
                }
                if (window.confetti) {
                    try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                }
                // Re-render once more with queued status
                if (Array.isArray(window._cachedSavedTrips) && typeof renderSavedTrips === 'function') {
                    renderSavedTrips(window._cachedSavedTrips);
                }
                return;
            }

            // Online upload
            const formData = new FormData();
            window.selectedProofPhotos.forEach((item, idx) => {
                formData.append('images[]', item.file, item.file.name || `proof_${Date.now()}_${idx}.jpg`);
            });
            formData.append('image', firstFile);

            const fallbackOffline = async (reason) => {
                if (window.OfflineCheckinManager) {
                    await window.OfflineCheckinManager.queueOfflineCheckin({
                        type: 'itinerary',
                        itemId: itemId,
                        spotId: spotId,
                        itineraryId: tripId,
                        spotName: spotName,
                        lat: window.myLat || 16.6159,
                        lng: window.myLng || 120.3209,
                        image: firstFile,
                        previewUrl: previewUrls[0] || null,
                        capturedAt: new Date().toISOString()
                    });
                    window.closeProofUploadModal();
                    if (typeof showToast === 'function') {
                        showToast('📍 Saved Offline! Photo proof saved on device. Auto-syncs when online.', 'success');
                    }
                    if (Array.isArray(window._cachedSavedTrips) && typeof renderSavedTrips === 'function') {
                        renderSavedTrips(window._cachedSavedTrips);
                    }
                } else {
                    if (typeof showToast === 'function') showToast(reason || 'Upload failed. Please try again.');
                    if (btn) {
                        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="margin-right:6px;"></i> Submit';
                        btn.disabled = false;
                    }
                }
            };

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 20000);

            try {
                const activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                const response = await fetch(activeBackend + '/api/tourist/itineraries/items/' + itemId + '/proof', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                        'ngrok-skip-browser-warning': 'true'
                    },
                    body: formData,
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                const data = await response.json();

                if (response.ok && data.success) {
                    window.closeProofUploadModal();
                    // Replace preview URLs with permanent server URLs from response
                    const permanentUrls = data.proof_images || (data.proof_image ? [data.proof_image] : previewUrls);
                    window.applyPhotoProofUpdateLocally(itemId, permanentUrls, data.proof_status || 'pending');

                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Photo proof submitted! Awaiting for approval.', 'success');
                    }
                    if (window.confetti) {
                        try { window.confetti({ particleCount: 80, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                    }
                } else {
                    console.warn('Server proof upload failed:', data);
                    await fallbackOffline(data.message || 'Server upload failed; saved offline.');
                }
            } catch (err) {
                clearTimeout(timeoutId);
                console.warn('Network timeout or error during proof upload, falling back offline:', err);
                await fallbackOffline('Network timeout during upload.');
            }
        };

        window.executeLocationCheckIn = function (itemId, spotName = '', spotLat = null, spotLng = null, btnEl = null) {
            if (!itemId) return;

            const originalHtml = btnEl ? btnEl.innerHTML : '';

            if (typeof window.isGuestUser === 'function' && window.isGuestUser()) {
                window.promptGuestLogin('check-in');
                return;
            }

            if (btnEl) {
                btnEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking in...';
                btnEl.disabled = true;
            }

            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const isOfflineItem = !itemId || 
                String(itemId).startsWith('offline_') || 
                !navigator.onLine || 
                window.isAppOnline === false ||
                !token ||
                token === 'guest_token' ||
                token.startsWith('guest_') ||
                token.startsWith('offline_');

            // Haversine formula (meters)
            const getHaversineDist = (lat1, lon1, lat2, lon2) => {
                const R = 6371e3;
                const φ1 = lat1 * Math.PI / 180;
                const φ2 = lat2 * Math.PI / 180;
                const Δφ = (lat2 - lat1) * Math.PI / 180;
                const Δλ = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            };

            const parentTrip = (window._cachedSavedTrips || []).find(t => (t.items || []).some(i => String(i.id) === String(itemId)));
            const targetItem = parentTrip ? (parentTrip.items || []).find(i => String(i.id) === String(itemId)) : null;
            const spotId = targetItem ? (targetItem.tourist_spot_id || targetItem.destination_id || (targetItem.destination ? targetItem.destination.id : null)) : null;
            const tripId = parentTrip ? parentTrip.id : null;
            const tPax = parentTrip ? (parentTrip.total_pax || 1) : 1;
            const cMales = parentTrip ? (parentTrip.companion_males || 0) : 0;
            const cFemales = parentTrip ? (parentTrip.companion_females || 0) : 0;

            // Resolve spot coordinates accurately
            let destLat = (spotLat !== null && !isNaN(spotLat)) ? parseFloat(spotLat) : null;
            let destLng = (spotLng !== null && !isNaN(spotLng)) ? parseFloat(spotLng) : null;
            if ((destLat === null || destLng === null) && targetItem && targetItem.destination) {
                if (targetItem.destination.latitude != null) destLat = parseFloat(targetItem.destination.latitude);
                if (targetItem.destination.longitude != null) destLng = parseFloat(targetItem.destination.longitude);
            }
            if ((destLat === null || destLng === null) && spotId && typeof window.getTouristSpotById === 'function') {
                const sp = window.getTouristSpotById(spotId);
                if (sp) {
                    if (sp.latitude != null) destLat = parseFloat(sp.latitude);
                    if (sp.longitude != null) destLng = parseFloat(sp.longitude);
                }
            }

            const doSubmitCheckin = async (lat, lng) => {
                // Check if user is within destination radius (200m)
                if (destLat !== null && destLng !== null && !isNaN(destLat) && !isNaN(destLng)) {
                    const dist = getHaversineDist(lat, lng, destLat, destLng);
                    if (dist > 200) {
                        const distM = Math.round(dist);
                        const distKm = (dist / 1000).toFixed(1);
                        const displayDist = dist >= 1000 ? `${distKm} km` : `${distM} meters`;
                        if (typeof showToast === 'function') {
                            showToast(`⚠️ You are ${displayDist} away from ${spotName || 'the tourist site'}. You must be within 200m of the tourist site to check in! 📍`, 'warning', 4500);
                        }
                        if (btnEl) {
                            btnEl.innerHTML = originalHtml;
                            btnEl.disabled = false;
                        }
                        return; // Location remains unchecked!
                    }
                }

                if (isOfflineItem) {
                    window.applyLocationCheckinLocally(itemId);

                    if (window.OfflineCheckinManager) {
                        await window.OfflineCheckinManager.queueOfflineCheckin({
                            type: 'itinerary',
                            itemId: itemId,
                            spotId: spotId,
                            itineraryId: tripId,
                            spotName: spotName || 'Destination',
                            lat: lat,
                            lng: lng,
                            total_pax: tPax,
                            companion_males: cMales,
                            companion_females: cFemales,
                            capturedAt: new Date().toISOString()
                        });
                    }
                    if (typeof showToast === 'function') {
                        showToast(`📍 Checked in party of ${tPax} offline at ${spotName}! Auto-syncs when online.`, 'success');
                    }
                    if (window.confetti) {
                        try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                    }
                    if (btnEl) {
                        btnEl.innerHTML = originalHtml;
                        btnEl.disabled = false;
                    }
                    return;
                }

                const formData = new FormData();
                formData.append('lat', lat);
                formData.append('lng', lng);
                formData.append('total_pax', tPax);
                formData.append('companion_males', cMales);
                formData.append('companion_females', cFemales);
                formData.append('captured_at', new Date().toISOString());

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 12000);

                try {
                    const activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                    const response = await fetch(activeBackend + '/api/tourist/itineraries/items/' + itemId + '/checkin', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token,
                            'ngrok-skip-browser-warning': 'true'
                        },
                        body: formData,
                        signal: controller.signal
                    });
                    clearTimeout(timeoutId);
                    const data = await response.json();

                    if (response.ok && (data.success || data.is_location_verified)) {
                        window.applyLocationCheckinLocally(itemId);
                        if (typeof showToast === 'function') {
                            showToast(data.message || `📍 Location verified at ${spotName}! Photo proof is required to earn points.`, 'success');
                        }
                        if (window.confetti) {
                            try { window.confetti({ particleCount: 70, spread: 60, origin: { y: 0.7 } }); } catch (e) { }
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Check-in failed. Please ensure you are within 200m of the destination.', 'error');
                        }
                    }
                    if (btnEl) {
                        btnEl.innerHTML = originalHtml;
                        btnEl.disabled = false;
                    }
                } catch (err) {
                    clearTimeout(timeoutId);
                    console.warn('Location check-in error, queuing offline:', err);
                    if (window.OfflineCheckinManager) {
                        window.applyLocationCheckinLocally(itemId);
                        await window.OfflineCheckinManager.queueOfflineCheckin({
                            type: 'itinerary',
                            itemId: itemId,
                            spotId: spotId,
                            itineraryId: tripId,
                            spotName: spotName || 'Destination',
                            lat: lat,
                            lng: lng,
                            capturedAt: new Date().toISOString()
                        });
                        if (typeof showToast === 'function') {
                            showToast(`📍 Checked in offline at ${spotName}! Auto-syncs when online.`, 'success');
                        }
                    } else {
                        if (typeof showToast === 'function') showToast('Network error during check-in. Please try again.');
                    }
                    if (btnEl) {
                        btnEl.innerHTML = originalHtml;
                        btnEl.disabled = false;
                    }
                }
            };

            const handleGpsFailure = (err) => {
                console.warn('GPS location acquisition error:', err);
                if (typeof showToast === 'function') {
                    showToast('📍 Unable to acquire accurate GPS location. Please turn on Location/GPS and try again.', 'error');
                }
                if (btnEl) {
                    btnEl.innerHTML = originalHtml;
                    btnEl.disabled = false;
                }
            };

            const options = { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 };

            if (typeof window.requestPreciseLocation === 'function') {
                window.requestPreciseLocation(true).then(loc => {
                    if (loc && loc.lat && loc.lng) doSubmitCheckin(loc.lat, loc.lng);
                    else handleGpsFailure("No coords");
                }).catch(() => {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            pos => doSubmitCheckin(pos.coords.latitude, pos.coords.longitude),
                            handleGpsFailure,
                            options
                        );
                    } else {
                        handleGpsFailure("Geolocation unsupported");
                    }
                });
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => doSubmitCheckin(pos.coords.latitude, pos.coords.longitude),
                    handleGpsFailure,
                    options
                );
            } else {
                handleGpsFailure("Geolocation unsupported");
            }
        };

        window.markTripCompleted = function (id) {
            window._pendingCompleteTripId = id;
            const modal = document.getElementById('complete-trip-modal');
            if (modal) {
                modal.style.display = 'flex';
            }
        };

        window.closeCompleteTripModal = function () {
            const modal = document.getElementById('complete-trip-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        };

        window.executeConfirmCompleteTrip = async function () {
            const id = window._pendingCompleteTripId;
            if (!id) return;

            window.closeCompleteTripModal();
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');

            // 1. Immediately mark the trip as completed locally so all caches & pre-hydration filters blacklist it permanently!
            if (typeof window.markTripAsCompletedLocally === 'function') {
                window.markTripAsCompletedLocally(id, 0, null);
            }

            // Immediately animate out and remove completed trip card from DOM
            const tripCard = document.querySelector(`.trip-swipe-container[data-trip-id="${id}"]`);
            if (tripCard) {
                tripCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease, max-height 0.4s ease, margin 0.4s ease';
                tripCard.style.opacity = '0';
                tripCard.style.transform = 'translateY(-10px) scale(0.95)';
                setTimeout(() => {
                    tripCard.remove();
                    const list = document.getElementById('saved-trips-list');
                    if (list && list.children.length === 0) {
                        list.innerHTML = '<p style="text-align:center; color:#ffffff; margin-top: 20px;">No active trips found.</p>';
                    }
                }, 300);
            }

            // Extract trip destinations from local cache in case we need them for review modal fallback
            let localVisitedItems = [];
            try {
                if (Array.isArray(window._cachedSavedTrips)) {
                    const found = window._cachedSavedTrips.find(t => t && (String(t.id) === String(id) || String(t.local_id) === String(id)));
                    if (found && Array.isArray(found.items)) {
                        localVisitedItems = found.items;
                    }
                }
                if (localVisitedItems.length === 0) {
                    const backupRaw = localStorage.getItem('intan_elyu_offline_saved_trips_backup') || localStorage.getItem('intan_elyu_saved_trips');
                    if (backupRaw) {
                        const parsed = JSON.parse(backupRaw);
                        const itins = Array.isArray(parsed) ? parsed : (parsed.itineraries || parsed.data || []);
                        const found = itins.find(t => t && (String(t.id) === String(id) || String(t.local_id) === String(id)));
                        if (found && Array.isArray(found.items)) {
                            localVisitedItems = found.items;
                        }
                    }
                }
            } catch (e) {
                console.warn('Could not extract local trip items:', e);
            }

            if (!navigator.onLine || window.isAppOnline === false) {
                if (window.OfflineCompletedTripsManager) {
                    window.OfflineCompletedTripsManager.queueCompletion(id);
                }
                if (typeof showToast === 'function') {
                    showToast("🎉 Trip completed! Please review your destinations to earn points.", "success", 5000);
                }
                if (window.confetti) {
                    window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
                }
                if (token) {
                    const tokenKey = token.substring(0, 10);
                    localStorage.removeItem('saved_trips_' + tokenKey);
                    localStorage.removeItem('dashboard_trips_' + tokenKey);
                    localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
                }
                window.fetchSavedTrips(true);
                showTripCompletionReviewModal(localVisitedItems);
                return;
            }

            try {
                const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                const response = await fetch(bUrl + '/api/tourist/itineraries/' + id + '/complete', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + token
                    }
                });

                const data = await response.json();
                if (response.ok) {
                    if (typeof showToast === 'function') showToast(data.message || "Trip completed! Please review your destinations to earn points.");
                    if (window.confetti) {
                        window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
                    }

                    if (token) {
                        const tokenKey = token.substring(0, 10);
                        localStorage.removeItem('saved_trips_' + tokenKey);
                        localStorage.removeItem('dashboard_trips_' + tokenKey);
                        localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
                    }
                    window.fetchSavedTrips(true);

                    // Show Trip Completed modal with Review buttons for visited spots
                    let visitedItems = data.visited_items || data.visited_spots || [];
                    if (!visitedItems || visitedItems.length === 0) {
                        visitedItems = data.itinerary?.items || data.items || localVisitedItems;
                    }
                    showTripCompletionReviewModal(visitedItems);
                } else {
                    if (typeof showToast === 'function') showToast(data.message || "Failed to complete trip.");
                }
            } catch (error) {
                console.error("Error completing trip, queuing offline:", error);
                if (window.OfflineCompletedTripsManager) {
                    window.OfflineCompletedTripsManager.queueCompletion(id);
                    if (typeof showToast === 'function') {
                        showToast("🎉 Trip completed! Please review your destinations to earn points.", "success", 5000);
                    }
                    if (window.confetti) {
                        window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
                    }
                    if (token) {
                        const tokenKey = token.substring(0, 10);
                        localStorage.removeItem('saved_trips_' + tokenKey);
                        localStorage.removeItem('dashboard_trips_' + tokenKey);
                        localStorage.removeItem('dashboard_saved_trips_' + tokenKey);
                    }
                    window.fetchSavedTrips(true);
                    showTripCompletionReviewModal(localVisitedItems);
                    return;
                }
                if (typeof showToast === 'function') showToast("Network error.");
            }
        };

        function showTripCompletionReviewModal(visitedItems) {
            // Remove any existing completion modal
            const existing = document.getElementById('trip-completion-review-modal');
            if (existing) existing.remove();

            if (!Array.isArray(visitedItems) || visitedItems.length === 0) {
                return;
            }

            let destListHtml = '';
            visitedItems.forEach((item, idx) => {
                if (!item) return;
                const spotId = item.tourist_spot_id || item.destination_id || (item.destination ? item.destination.id : item.id);
                if (!spotId) return;

                const destName = item.destination_name || (item.destination ? item.destination.name : (item.name || item.spot_name || 'Tourist Destination'));
                const sClass = item.classification_status || (item.destination ? item.destination.classification_status : (item.classification || 'EXIST'));
                const isReviewed = window.userReviewedSpotIds && window.userReviewedSpotIds.has(Number(spotId));

                destListHtml += `
                <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:8px;">
                    <div style="width:30px; height:30px; border-radius:8px; background:linear-gradient(135deg, #1e3a8a, #2563eb); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:12px; color:#fff;">${idx + 1}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13.5px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${destName}</div>
                        <div style="font-size:11px; color:#64748b; font-weight:600; margin-top:1px;">
                            ${isReviewed 
                                ? '<span style="color:#10b981;"><i class="fa-solid fa-check" style="font-size:10px; margin-right:3px;"></i> Reviewed</span>' 
                                : '<span style="color:#64748b;">Leave a review for this stop</span>'}
                        </div>
                    </div>
                    <button type="button" 
                            data-spot-id="${spotId}" 
                            data-spot-classification="${sClass}"
                            onclick="window.startReviewFromCompletion('${spotId}', this)" 
                            style="padding:6px 14px; border-radius:100px; background:${isReviewed ? '#e2e8f0' : 'linear-gradient(135deg, #1e3a8a, #2563eb)'}; border:none; outline:none; color:${isReviewed ? '#64748b' : '#ffffff'}; font-size:11.5px; font-weight:800; cursor:pointer; white-space:nowrap; box-shadow:none; display:inline-flex; align-items:center; gap:4px; flex-shrink:0;">
                        ${isReviewed ? '<i class="fa-solid fa-check" style="font-size:10px; margin-right:4px;"></i> Reviewed' : '<i class="fa-solid fa-pen" style="font-size:10px;"></i> Review'}
                    </button>
                </div>`;
            });

            const modalHtml = `
        <div id="trip-completion-review-modal" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:99998; display:flex; align-items:center; justify-content:center; padding:16px; opacity:0; transition:opacity 0.3s ease;">
            <div style="background:#ffffff; border:none; outline:none; border-radius:24px; width:100%; max-width:390px; box-shadow:0 25px 50px rgba(10, 25, 60, 0.45); overflow:hidden; display:flex; flex-direction:column; transform:scale(0.92) translateY(12px); transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
                <!-- Header (Royal Blue) -->
                <div style="background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; padding:18px 20px; display:flex; justify-content:space-between; align-items:center; color:#ffffff; flex-shrink:0;">
                    <div>
                        <h4 style="margin:0; font-size:16px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-flag-checkered" style="color:#60a5fa;"></i> Trip Completed!
                        </h4>
                        <p style="margin:2px 0 0 0; font-size:11.5px; color:rgba(255,255,255,0.9);">
                            Review destinations you visited
                        </p>
                    </div>
                    <button type="button" onclick="window.closeTripCompletionReviewModal()" style="background:rgba(255,255,255,0.2); border:none !important; outline:none !important; color:#ffffff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px; transition:background 0.2s;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Middle (White) -->
                <div style="background:#ffffff !important; padding:18px 20px 14px 20px; overflow-y:auto; max-height:calc(75vh - 120px); scrollbar-width:none; -ms-overflow-style:none; flex:1; text-align:center;">
                    <div style="width:52px; height:52px; border-radius:50%; background:#eff6ff; display:flex; align-items:center; justify-content:center; margin:0 auto 10px auto; border:none; outline:none;">
                        <i class="fa-solid fa-award" style="font-size:24px; color:#2563eb;"></i>
                    </div>
                    <h3 style="margin:0 0 4px; color:#0f172a; font-size:18px; font-weight:900;">Congratulations!</h3>
                    <p style="font-size:12px; color:#64748b; margin:0 0 16px 0; line-height:1.45;">
                        Share your experience! Review each destination you visited to help fellow travelers.
                    </p>
                    <div style="text-align:left; max-height:220px; overflow-y:auto; margin-bottom:0; padding-right:4px; scrollbar-width:none; -ms-overflow-style:none;">
                        ${destListHtml}
                    </div>
                </div>

                <!-- Footer (Royal Blue) -->
                <div style="background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; padding:14px 20px; flex-shrink:0;">
                    <button type="button" id="btn-trip-completion-close" onclick="window.closeTripCompletionReviewModal()" style="width:100%; padding:12px; border-radius:100px; border:none; outline:none; background:rgba(255,255,255,0.2); color:#ffffff; font-size:13px; font-weight:800; cursor:pointer; transition:all 0.2s ease;">
                        Done
                    </button>
                </div>
            </div>
        </div>`;

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            requestAnimationFrame(() => {
                const modal = document.getElementById('trip-completion-review-modal');
                if (modal) {
                    modal.style.opacity = '1';
                    const card = modal.querySelector('div > div');
                    if (card) {
                        card.style.transform = 'scale(1) translateY(0)';
                    }
                }
            });
        }

        window.closeTripCompletionReviewModal = function () {
            const modal = document.getElementById('trip-completion-review-modal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => modal.remove(), 320);
            }
        };

        window.startReviewFromCompletion = function (spotId, btnEl) {
            if (btnEl) {
                btnEl.dataset.reviewing = "true";
                window._lastReviewedBtn = btnEl;
            }
            const compModal = document.getElementById('trip-completion-review-modal');
            if (compModal) {
                compModal.style.zIndex = '99990';
            }
            if (typeof window.openWriteTestimonyModal === 'function') {
                window.openWriteTestimonyModal(spotId, btnEl);
            }
        };

        window.closeEditTripDetailsModal = function () {
            const modal = document.getElementById('edit-trip-details-modal');
            if (modal) modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            document.body.classList.remove('sheet-open');
        };

        window.setQuickEditTripType = function (type) {
            const hiddenInput = document.getElementById('quick-edit-trip-type');
            if (hiddenInput) hiddenInput.value = type;

            const soloRadio = document.getElementById('quick-edit-radio-solo');
            const groupRadio = document.getElementById('quick-edit-radio-group');
            const soloOpt = document.getElementById('quick-edit-opt-solo');
            const groupOpt = document.getElementById('quick-edit-opt-group');
            const compBox = document.getElementById('quick-edit-companion-box');

            if (type === 'group') {
                if (groupRadio) groupRadio.checked = true;
                if (soloRadio) soloRadio.checked = false;
                if (groupOpt) {
                    groupOpt.style.background = '#eff6ff';
                    groupOpt.style.borderColor = '#2563eb';
                    const span = groupOpt.querySelector('span');
                    if (span) span.style.color = '#1e3a8a';
                }
                if (soloOpt) {
                    soloOpt.style.background = '#f8fafc';
                    soloOpt.style.borderColor = '#e2e8f0';
                    const span = soloOpt.querySelector('span');
                    if (span) span.style.color = '#64748b';
                }
                if (compBox) compBox.style.display = 'block';
            } else {
                if (soloRadio) soloRadio.checked = true;
                if (groupRadio) groupRadio.checked = false;
                if (soloOpt) {
                    soloOpt.style.background = '#eff6ff';
                    soloOpt.style.borderColor = '#2563eb';
                    const span = soloOpt.querySelector('span');
                    if (span) span.style.color = '#1e3a8a';
                }
                if (groupOpt) {
                    groupOpt.style.background = '#f8fafc';
                    groupOpt.style.borderColor = '#e2e8f0';
                    const span = groupOpt.querySelector('span');
                    if (span) span.style.color = '#64748b';
                }
                if (compBox) compBox.style.display = 'none';
            }
            window.updateQuickEditPartySummary();
        };

        window.adjustQuickEditCompanions = function (gender, delta) {
            const elId = gender === 'males' ? 'quick-edit-males-count' : 'quick-edit-females-count';
            const countEl = document.getElementById(elId);
            if (!countEl) return;
            let current = parseInt(countEl.textContent || '0', 10);
            if (isNaN(current)) current = 0;
            current = Math.max(0, current + delta);
            countEl.textContent = String(current);
            window.updateQuickEditPartySummary();
        };

        window.updateQuickEditPartySummary = function () {
            const summaryEl = document.getElementById('quick-edit-party-summary');
            if (!summaryEl) return;
            const type = document.getElementById('quick-edit-trip-type')?.value || 'solo';
            if (type === 'group') {
                const m = parseInt(document.getElementById('quick-edit-males-count')?.textContent || '0', 10) || 0;
                const f = parseInt(document.getElementById('quick-edit-females-count')?.textContent || '0', 10) || 0;
                const total = 1 + m + f;
                summaryEl.textContent = `${total} Pax (You + ${m}M, ${f}F)`;
            } else {
                summaryEl.textContent = '1 Pax (Solo Traveler)';
            }
        };

        window.editSavedTrip = function (tripId) {
            let trip = (window._cachedSavedTrips || []).find(t => String(t.id) === String(tripId));
            if (!trip) {
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                if (token) {
                    try {
                        const rawCached = localStorage.getItem('saved_trips_' + token.substring(0, 10));
                        if (rawCached) {
                            const parsed = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(rawCached, null) : JSON.parse(rawCached);
                            const list = parsed && parsed.data ? parsed.data : parsed;
                            if (Array.isArray(list)) {
                                trip = list.find(t => String(t.id) === String(tripId));
                            }
                        }
                    } catch (e) { }
                }
            }
            if (!trip) {
                if (typeof showToast === 'function') showToast("Could not load trip details for editing.");
                return;
            }

            window._currentEditingTrip = trip;

            let modal = document.getElementById('edit-trip-details-modal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            const idInput = document.getElementById('quick-edit-trip-id');
            const titleInput = document.getElementById('quick-edit-trip-title');
            const dateInput = document.getElementById('quick-edit-trip-date');

            if (idInput) idInput.value = trip.id;
            if (titleInput) titleInput.value = trip.title || '';
            if (dateInput) {
                dateInput.value = trip.trip_date ? String(trip.trip_date).split(/[T ]/)[0] : '';
            }

            const isGroup = (trip.trip_type === 'group' || (trip.total_pax && parseInt(trip.total_pax, 10) > 1));
            window.setQuickEditTripType(isGroup ? 'group' : 'solo');

            const mEl = document.getElementById('quick-edit-males-count');
            const fEl = document.getElementById('quick-edit-females-count');
            if (mEl) mEl.textContent = String(trip.companion_males || 0);
            if (fEl) fEl.textContent = String(trip.companion_females || 0);

            window.updateQuickEditPartySummary();

            if (modal) {
                modal.style.display = 'flex';
                modal.style.pointerEvents = 'auto';
            }
            document.body.classList.add('modal-open');
            document.body.classList.add('sheet-open');
        };

        window.submitQuickEditTripDetails = async function () {
            const idInput = document.getElementById('quick-edit-trip-id');
            const tripId = idInput ? idInput.value : '';
            if (!tripId) return;

            const title = (document.getElementById('quick-edit-trip-title')?.value || '').trim() || 'My Trip';
            const date = document.getElementById('quick-edit-trip-date')?.value || null;
            const tripType = document.getElementById('quick-edit-trip-type')?.value || 'solo';
            const males = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('quick-edit-males-count')?.textContent || '0', 10)) : 0;
            const females = tripType === 'group' ? Math.max(0, parseInt(document.getElementById('quick-edit-females-count')?.textContent || '0', 10)) : 0;
            const totalPax = tripType === 'group' ? (1 + males + females) : 1;

            const saveBtn = document.getElementById('btn-save-quick-edit');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            }

            const activeBackend = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');

            const payload = {
                title: title,
                trip_date: date,
                trip_type: tripType,
                companion_males: males,
                companion_females: females,
                total_pax: totalPax
            };

            let saveSuccess = false;
            let errorMsg = null;
            if (navigator.onLine && token && !String(tripId).startsWith('offline_')) {
                try {
                    const res = await fetch(`${activeBackend}/api/tourist/itineraries/${tripId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        saveSuccess = true;
                    } else {
                        const errData = await res.json().catch(() => ({}));
                        errorMsg = errData.message || `Failed to update trip (${res.status})`;
                    }
                } catch (e) {
                    console.warn("Server update trip error, persisting locally:", e);
                }
            } else {
                saveSuccess = true; // Offline or local trip
            }

            if (navigator.onLine && token && !String(tripId).startsWith('offline_') && !saveSuccess && errorMsg) {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save Changes';
                }
                if (typeof showToast === 'function') {
                    showToast(errorMsg, "error");
                }
                return;
            }

            // Invalidate signature so UI will always re-render cleanly
            window._lastRenderedTripsSig = null;

            // Update in-memory cached trips
            if (Array.isArray(window._cachedSavedTrips)) {
                window._cachedSavedTrips = window._cachedSavedTrips.map(t => {
                    if (String(t.id) === String(tripId) || String(t.local_id) === String(tripId)) {
                        return {
                            ...t,
                            title: title,
                            trip_date: date,
                            trip_type: tripType,
                            companion_males: males,
                            companion_females: females,
                            total_pax: totalPax
                        };
                    }
                    return t;
                });
            }

            // Invalidate/update localStorage caches across ALL keys
            const tokenKey = token ? token.substring(0, 10) : 'default';
            ['saved_trips_' + tokenKey, 'intan_elyu_saved_trips', 'intan_elyu_offline_saved_trips_backup', 'intan_elyu_offline_saved_trips'].forEach(k => {
                try {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        const arr = Array.isArray(parsed) ? parsed : (parsed.data || parsed.itineraries || []);
                        if (Array.isArray(arr)) {
                            const updated = arr.map(t => {
                                if (String(t.id) === String(tripId) || String(t.local_id) === String(tripId) || String(t.update_id) === String(tripId)) {
                                    return {
                                        ...t,
                                        title: title,
                                        trip_date: date,
                                        trip_type: tripType,
                                        companion_males: males,
                                        companion_females: females,
                                        total_pax: totalPax,
                                        updated_at: new Date().toISOString()
                                    };
                                }
                                return t;
                            });
                            localStorage.setItem(k, JSON.stringify(Array.isArray(parsed) ? updated : { ...parsed, data: updated, itineraries: updated, timestamp: Date.now() }));
                        }
                    }
                } catch (e) { }
            });

            // Sync with OfflineTripManager if present
            if (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function') {
                try {
                    const qTrips = window.OfflineTripManager.getQueuedTrips();
                    let qChanged = false;
                    const updatedQ = qTrips.map(t => {
                        if (String(t.id) === String(tripId) || String(t.local_id) === String(tripId) || String(t.update_id) === String(tripId) || String(t.id) === ('offline_edit_' + tripId)) {
                            qChanged = true;
                            return {
                                ...t,
                                title: title,
                                trip_date: date,
                                trip_type: tripType,
                                companion_males: males,
                                companion_females: females,
                                total_pax: totalPax,
                                updated_at: new Date().toISOString()
                            };
                        }
                        return t;
                    });
                    if (qChanged) {
                        localStorage.setItem(window.OfflineTripManager.STORAGE_KEY || 'intan_elyu_offline_saved_trips', JSON.stringify(updatedQ));
                    }
                } catch (e) { }
            }

            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save Changes';
            }

            window.closeEditTripDetailsModal();
            if (typeof showToast === 'function') {
                showToast("Trip updated! 🎉", "success");
            }
            // Immediate optimistic UI update
            if (typeof renderSavedTrips === 'function') {
                renderSavedTrips(window._cachedSavedTrips);
            }
            // Silent asynchronous SWR revalidation (no full page reload)
            if (typeof window.fetchSavedTrips === 'function') {
                window.fetchSavedTrips(true);
            }
        };

        window.openPlannerFromQuickEdit = function () {
            window.closeEditTripDetailsModal();
            const trip = window._currentEditingTrip;
            if (!trip) return;

            // Backup existing draft itinerary spots if user was building a plan before editing
            const existingDraftRaw = localStorage.getItem('intan_elyu_draft_itinerary');
            const isAlreadyEditing = Boolean(sessionStorage.getItem('editing_itinerary_id'));
            if (!isAlreadyEditing) {
                if (existingDraftRaw) {
                    try {
                        const parsed = JSON.parse(existingDraftRaw);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            localStorage.setItem('intan_elyu_pre_edit_backup_draft', existingDraftRaw);
                            sessionStorage.setItem('intan_elyu_pre_edit_backup_draft', existingDraftRaw);
                        } else {
                            localStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                            sessionStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                        }
                    } catch (e) {
                        localStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                        sessionStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                    }
                } else {
                    localStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                    sessionStorage.removeItem('intan_elyu_pre_edit_backup_draft');
                }
            }

            sessionStorage.setItem('editing_itinerary_id', String(trip.id || trip.local_id || ''));
            sessionStorage.setItem('editing_trip_title', trip.title || '');
            sessionStorage.setItem('editing_trip_date', trip.trip_date || '');
            sessionStorage.setItem('editing_trip_budget', (trip.budget !== null && trip.budget !== undefined) ? trip.budget : '');
            sessionStorage.setItem('editing_trip_transport', trip.transport_mode || '');
            sessionStorage.setItem('editing_trip_type', trip.trip_type || 'solo');
            sessionStorage.setItem('editing_companion_males', String(trip.companion_males || 0));
            sessionStorage.setItem('editing_companion_females', String(trip.companion_females || 0));
            sessionStorage.setItem('editing_total_pax', String(trip.total_pax || 1));
            if (trip.transport_mode) {
                localStorage.setItem('intan_elyu_draft_trip_transport', trip.transport_mode);
                const rawModes = trip.transport_mode.split(/[\+,]/).map(s => s.trim().toLowerCase()).filter(Boolean);
                localStorage.setItem('intan_elyu_draft_trip_transports', JSON.stringify(rawModes.length > 0 ? rawModes : [trip.transport_mode]));
            }

            let mapSpotsMap = {};
            try {
                const rawMap = localStorage.getItem('public_map_data');
                if (rawMap) {
                    const parsedMap = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(rawMap, null) : JSON.parse(rawMap);
                    const spotsArr = (parsedMap && parsedMap.data && parsedMap.data.destinations) ? parsedMap.data.destinations : (parsedMap && parsedMap.destinations ? parsedMap.destinations : []);
                    if (Array.isArray(spotsArr)) {
                        spotsArr.forEach(s => { if (s && s.id) mapSpotsMap[String(s.id)] = s; });
                    }
                }
            } catch (e) { }

            const sortedTripItems = [...(trip.items || [])].sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
            const spots = sortedTripItems.map(i => {
                let dest = i.destination;
                if (!dest && (i.tourist_spot_id || i.destination_id)) {
                    const spotId = i.tourist_spot_id || i.destination_id;
                    if (typeof window.getTouristSpotById === 'function') {
                        dest = window.getTouristSpotById(spotId);
                    }
                    if (!dest && mapSpotsMap[String(spotId)]) {
                        dest = mapSpotsMap[String(spotId)];
                    }
                }
                if (!dest && (i.name || i.title)) {
                    dest = i;
                }

                if (dest) {
                    const spotId = i.tourist_spot_id || i.destination_id || (dest && dest !== i ? (dest.tourist_spot_id || dest.id) : null);
                    const finalSpotId = spotId || (dest !== i ? dest.id : null);
                    const mapSpot = (finalSpotId && mapSpotsMap[String(finalSpotId)]) ? mapSpotsMap[String(finalSpotId)] : {};
                    const accVeh = (Array.isArray(dest.accessible_vehicles) && dest.accessible_vehicles.length > 0)
                        ? dest.accessible_vehicles
                        : (Array.isArray(mapSpot.accessible_vehicles) ? mapSpot.accessible_vehicles : (dest.accessible_vehicles || []));

                    const hasVeh = (dest.has_available_vehicles !== undefined)
                        ? Boolean(dest.has_available_vehicles)
                        : (mapSpot.has_available_vehicles !== undefined ? Boolean(mapSpot.has_available_vehicles) : (Array.isArray(accVeh) && accVeh.length > 0));

                    const pubVeh = (Array.isArray(dest.public_vehicles) && dest.public_vehicles.length > 0)
                        ? dest.public_vehicles
                        : (Array.isArray(mapSpot.public_vehicles) ? mapSpot.public_vehicles : (dest.public_vehicles || []));

                    const privVeh = (Array.isArray(dest.private_vehicles) && dest.private_vehicles.length > 0)
                        ? dest.private_vehicles
                        : (Array.isArray(mapSpot.private_vehicles) ? mapSpot.private_vehicles : (dest.private_vehicles || []));

                    const rawMuni = dest.municipality || mapSpot.municipality || '';
                    const muniName = (typeof rawMuni === 'string') ? rawMuni : (rawMuni?.name || '');

                    return {
                        ...dest,
                        id: finalSpotId,
                        tourist_spot_id: finalSpotId,
                        is_maintenance: (dest.is_maintenance !== undefined) ? dest.is_maintenance : mapSpot.is_maintenance,
                        status: dest.status || mapSpot.status,
                        lat: dest.latitude || dest.lat || mapSpot.lat,
                        lng: dest.longitude || dest.lng || mapSpot.lng,
                        photo_url: dest.image || dest.photo_url || mapSpot.photo_url || '',
                        municipality: muniName,
                        accessible_vehicles: accVeh,
                        public_vehicles: pubVeh,
                        private_vehicles: privVeh,
                        has_available_vehicles: Boolean(hasVeh) && Array.isArray(accVeh) && accVeh.length > 0,
                        itinerary_item_id: i.id
                    };
                }
                return null;
            }).filter(Boolean);

            localStorage.setItem('intan_elyu_draft_itinerary', JSON.stringify(spots));

            const legVehicles = sortedTripItems.map(i => ({
                transport_mode: i.transport_mode || null,
                leg_cost: (i.leg_cost !== null && i.leg_cost !== undefined) ? parseFloat(i.leg_cost) : null,
                leg_distance_km: (i.leg_distance_km !== null && i.leg_distance_km !== undefined) ? parseFloat(i.leg_distance_km) : null
            }));
            localStorage.setItem('intan_elyu_draft_leg_vehicles', JSON.stringify(legVehicles));

            if (typeof window.navigateTo === 'function') {
                window.navigateTo('itinerary');
            } else {
                window.location.href = '?view=itinerary';
            }
        };

        window.confirmDeleteSavedTrip = function (id, container, title) {
            window._pendingDeleteTripId = id;
            window._pendingDeleteTripContainer = container || (id ? document.querySelector(`.trip-swipe-container[data-trip-id="${id}"]`) : null);
            window._pendingDeleteTripTitle = title || container?.dataset?.tripTitle || '';

            let modal = document.getElementById('delete-trip-modal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            const textEl = document.getElementById('delete-trip-title-text');
            if (textEl) {
                const displayTitle = window._pendingDeleteTripTitle || 'this trip';
                textEl.textContent = `Are you sure you want to delete "${displayTitle}"? All saved itinerary items will be removed.`;
            }
            if (modal) {
                modal.style.display = 'flex';
            }
        };

        window.closeDeleteTripModal = function () {
            const modal = document.getElementById('delete-trip-modal');
            if (modal) modal.style.display = 'none';

            const container = window._pendingDeleteTripContainer || (window._pendingDeleteTripId ? document.querySelector(`.trip-swipe-container[data-trip-id="${window._pendingDeleteTripId}"]`) : null);
            if (container) {
                const content = container.querySelector('.trip-swipe-content');
                const bg = container.querySelector('.trip-swipe-bg');
                if (content) {
                    content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                    content.style.transform = 'translateX(0px)';
                }
                if (bg) {
                    bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
                    bg.style.width = '95px';
                    bg.style.opacity = '0';
                    bg.style.pointerEvents = 'none';
                    bg.style.transform = 'translateX(95px)';
                    bg.style.zIndex = '1';
                }
            }

            window._pendingDeleteTripId = null;
            window._pendingDeleteTripContainer = null;
            window._pendingDeleteTripTitle = null;
        };

        window.executeConfirmDeleteTrip = async function () {
            const id = window._pendingDeleteTripId;
            let element = window._pendingDeleteTripContainer || (id ? document.querySelector(`.trip-swipe-container[data-trip-id="${id}"]`) : null);
            const deletedTitle = window._pendingDeleteTripTitle || '';
            const modal = document.getElementById('delete-trip-modal');
            if (modal) modal.style.display = 'none';

            if (!id) return;

            // 1. Immediately record in persistent deleted IDs blacklist
            window.markTripAsDeleted(id);

            // If there was an update_id or offline_edit, mark that too
            if (String(id).startsWith('offline_edit_')) {
                window.markTripAsDeleted(String(id).replace('offline_edit_', ''));
            }

            // If deletedTitle is available, also mark any ghost clones with identical title in cached trips
            if (deletedTitle && Array.isArray(window._cachedSavedTrips)) {
                window._cachedSavedTrips.forEach(t => {
                    if (t && t.title && t.title.trim().toLowerCase() === deletedTitle.trim().toLowerCase()) {
                        window.markTripAsDeleted(t.id);
                        if (t.trip_id) window.markTripAsDeleted(t.trip_id);
                        if (t.local_id) window.markTripAsDeleted(t.local_id);
                        if (t.update_id) window.markTripAsDeleted(t.update_id);
                    }
                });
            }

            // Comprehensive local trip pruner so deleted trip never resurrects from offline cache
            const pruneTripFromLocalCaches = (tripId) => {
                if (!tripId) return;
                const targetStr = String(tripId).trim();

                const filterOut = (list) => {
                    if (!Array.isArray(list)) return [];
                    return list.filter(t => {
                        if (!t) return false;
                        if (window.isTripDeleted(t)) return false;
                        const idStr = String(t.id || '');
                        const tripIdStr = String(t.trip_id || '');
                        const localIdStr = String(t.local_id || '');
                        const updateIdStr = String(t.update_id || '');
                        if (idStr === targetStr || tripIdStr === targetStr || localIdStr === targetStr || updateIdStr === targetStr) return false;
                        if (idStr === 'offline_edit_' + targetStr || tripIdStr === 'offline_edit_' + targetStr) return false;
                        if (targetStr.startsWith('offline_edit_')) {
                            const raw = targetStr.replace('offline_edit_', '');
                            if (idStr === raw || tripIdStr === raw || localIdStr === raw || updateIdStr === raw) return false;
                        }
                        if (deletedTitle && t.title && t.title.trim().toLowerCase() === deletedTitle.trim().toLowerCase()) return false;
                        return true;
                    });
                };

                // 1. Prune all fixed known offline and backup storage keys
                ['intan_elyu_offline_saved_trips', 'intan_elyu_offline_saved_trips_backup', 'intan_elyu_saved_trips'].forEach(k => {
                    try {
                        const raw = localStorage.getItem(k);
                        if (raw) {
                            const parsed = JSON.parse(raw);
                            if (Array.isArray(parsed)) {
                                localStorage.setItem(k, JSON.stringify(filterOut(parsed)));
                            } else if (parsed && Array.isArray(parsed.itineraries)) {
                                parsed.itineraries = filterOut(parsed.itineraries);
                                localStorage.setItem(k, JSON.stringify(parsed));
                            } else if (parsed && Array.isArray(parsed.data)) {
                                parsed.data = filterOut(parsed.data);
                                localStorage.setItem(k, JSON.stringify(parsed));
                            }
                        }
                    } catch (e) { }
                });

                // 2. Prune through OfflineTripManager
                if (window.OfflineTripManager && typeof window.OfflineTripManager.removeQueuedTrip === 'function') {
                    try { window.OfflineTripManager.removeQueuedTrip(tripId); } catch(e) {}
                }

                // 3. HARD PURGE ALL dynamic localStorage cache keys (saved_trips_*, dashboard_trips_*, dashboard_saved_trips_*)
                try {
                    for (let i = localStorage.length - 1; i >= 0; i--) {
                        const k = localStorage.key(i);
                        if (k && (k.startsWith('saved_trips_') || k.startsWith('dashboard_trips_') || k.startsWith('dashboard_saved_trips_'))) {
                            localStorage.removeItem(k);
                        }
                    }
                } catch(e) {}

                // 4. Update in-memory cached saved trips so subsequent renders don't bring it back
                if (Array.isArray(window._cachedSavedTrips)) {
                    window._cachedSavedTrips = filterOut(window._cachedSavedTrips);
                }

                // 5. Clean up active editing session if this exact trip was open for editing
                try {
                    const currentEditingId = sessionStorage.getItem('editing_itinerary_id');
                    if (currentEditingId && (String(currentEditingId) === targetStr || targetStr.includes(String(currentEditingId)))) {
                        sessionStorage.removeItem('editing_itinerary_id');
                        sessionStorage.removeItem('editing_trip_title');
                        sessionStorage.removeItem('editing_trip_date');
                        sessionStorage.removeItem('editing_trip_budget');
                        sessionStorage.removeItem('editing_trip_transport');
                        sessionStorage.removeItem('editing_transport_initialized');
                        localStorage.removeItem('intan_elyu_draft_leg_vehicles');
                    }
                } catch (e) { }
            };

            const animateAndRemoveCard = (el) => {
                if (!el) {
                    const remaining = document.querySelectorAll('.trip-swipe-container');
                    if (!remaining || remaining.length === 0) {
                        renderSavedTrips([]);
                    }
                    return;
                }
                el.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
                el.style.opacity = '0';
                el.style.transform = 'scale(0.85) translateX(-100px)';
                el.style.maxHeight = el.offsetHeight + 'px';
                setTimeout(() => {
                    el.style.maxHeight = '0px';
                    el.style.marginBottom = '0px';
                    el.style.padding = '0px';
                    el.style.overflow = 'hidden';
                    setTimeout(() => {
                        el.remove();
                        const remaining = document.querySelectorAll('.trip-swipe-container');
                        if (!remaining || remaining.length === 0) {
                            renderSavedTrips([]);
                        }
                    }, 250);
                }, 50);
            };

            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const isGuestOrOffline = !token || 
                token === 'guest_token' || 
                token === 'offline_guest_token' || 
                token.startsWith('guest_') || 
                token.startsWith('offline_') || 
                String(id).startsWith('offline_') ||
                !navigator.onLine || 
                window.isAppOnline === false;

            if (isGuestOrOffline) {
                if (!String(id).startsWith('offline_')) {
                    window.queuePendingServerDelete(id);
                }
                pruneTripFromLocalCaches(id);
                if (typeof showToast === 'function') showToast("Trip deleted from device.");
                animateAndRemoveCard(element);
                window.dispatchEvent(new CustomEvent('tripDeleted', { detail: { id: id } }));
                window._pendingDeleteTripId = null;
                window._pendingDeleteTripContainer = null;
                return;
            }

            try {
                // Prune local caches immediately so UI is optimistic and instantaneous
                pruneTripFromLocalCaches(id);
                animateAndRemoveCard(element);

                const primaryUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || window.BACKEND_URL || '')).replace(/\/+$/, '');
                const baseCandidates = [];
                if (primaryUrl && !primaryUrl.includes('localhost') && !primaryUrl.includes('127.0.0.1')) {
                    baseCandidates.push(primaryUrl);
                }
                baseCandidates.push('https://api.intan-elyu.online');
                if (primaryUrl && !baseCandidates.includes(primaryUrl)) {
                    baseCandidates.push(primaryUrl);
                }
                baseCandidates.push('');

                let serverDeleted = false;
                for (const bUrl of baseCandidates) {
                    try {
                        const res = await fetch(bUrl + '/api/tourist/itineraries/' + id, {
                            method: 'DELETE',
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Accept': 'application/json',
                                'ngrok-skip-browser-warning': 'true'
                            }
                        });
                        if (res.ok || res.status === 404) {
                            serverDeleted = true;
                            break;
                        }
                    } catch (candErr) {}
                }

                // Extra safety: re-prune in case any concurrent fetch refreshed cache
                pruneTripFromLocalCaches(id);

                if (serverDeleted) {
                    if (typeof showToast === 'function') showToast("Trip deleted successfully.");
                } else {
                    window.queuePendingServerDelete(id);
                    if (typeof showToast === 'function') showToast("Trip deleted locally (sync queued).");
                }

                window.dispatchEvent(new CustomEvent('tripDeleted', { detail: { id: id } }));
            } catch (e) {
                console.warn('Network error during trip deletion; pruned locally:', e);
                window.queuePendingServerDelete(id);
                pruneTripFromLocalCaches(id);
                if (typeof showToast === 'function') showToast("Trip removed from device (offline).");
                window.dispatchEvent(new CustomEvent('tripDeleted', { detail: { id: id } }));
            } finally {
                window._pendingDeleteTripId = null;
                window._pendingDeleteTripContainer = null;
            }
        };

        function initSavedTripsSwipe() {
            const containers = document.querySelectorAll('.trip-swipe-container');
            containers.forEach(container => {
                let startX = 0;
                let startY = 0;
                let currentX = 0;
                let currentY = 0;
                let isSwiping = false;
                let isHorizontalSwipe = false;
                let isGestureDetermined = false;
                let isOpen = false;
                const tripId = container.dataset.tripId;
                const tripTitle = container.dataset.tripTitle || 'this trip';
                const content = container.querySelector('.trip-swipe-content');
                const bg = container.querySelector('.trip-swipe-bg');
                if (!content) return;

                container.style.touchAction = 'pan-y';
                content.style.touchAction = 'pan-y';

                const openSwipe = () => {
                    isOpen = true;
                    content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                    content.style.transform = 'translateX(-95px)';
                    if (bg) {
                        bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
                        bg.style.width = '95px';
                        bg.style.opacity = '1';
                        bg.style.pointerEvents = 'auto';
                        bg.style.transform = 'translateX(0px)';
                        bg.style.zIndex = '10';
                    }
                };

                const closeSwipe = () => {
                    isOpen = false;
                    content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                    content.style.transform = 'translateX(0px)';
                    if (bg) {
                        bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';
                        bg.style.width = '95px';
                        bg.style.opacity = '0';
                        bg.style.pointerEvents = 'none';
                        bg.style.transform = 'translateX(95px)';
                        bg.style.zIndex = '1';
                    }
                };

                const handleStart = (clientX, clientY, target) => {
                    if (target && target.closest('button, .btn-edit-saved-trip, a, input, select, textarea, .start-collapsible, .timeline-collapsible button')) {
                        return;
                    }
                    if (target && target.closest('.trip-swipe-bg')) {
                        return;
                    }

                    startX = clientX;
                    startY = clientY;
                    currentX = startX;
                    currentY = startY;
                    isSwiping = true;
                    isHorizontalSwipe = false;
                    isGestureDetermined = false;
                    content.style.transition = 'none';
                    if (bg) bg.style.transition = 'none';
                };

                const handleMove = (clientX, clientY) => {
                    if (!isSwiping) return;
                    currentX = clientX;
                    currentY = clientY;

                    const diffX = startX - currentX;
                    const diffY = startY - currentY;

                    if (!isGestureDetermined) {
                        if (Math.abs(diffX) > 7 || Math.abs(diffY) > 7) {
                            isGestureDetermined = true;
                            if (Math.abs(diffX) > Math.abs(diffY)) {
                                isHorizontalSwipe = true;
                            } else {
                                isHorizontalSwipe = false;
                                isSwiping = false;
                                return;
                            }
                        } else {
                            return;
                        }
                    }

                    if (!isHorizontalSwipe) return;

                    const baseOffset = isOpen ? 95 : 0;
                    const effectiveDiff = diffX + baseOffset;

                    if (effectiveDiff > 0) {
                        const cardWidth = container.offsetWidth || 340;
                        const dragAmount = Math.min(effectiveDiff, cardWidth * 0.85);
                        content.style.transform = `translateX(-${dragAmount}px)`;
                        if (bg) {
                            bg.style.opacity = '1';
                            bg.style.pointerEvents = 'auto';
                            bg.style.zIndex = '10';
                            if (dragAmount > 95) {
                                bg.style.width = `${dragAmount}px`;
                                bg.style.transform = 'translateX(0px)';
                            } else {
                                bg.style.width = '95px';
                                bg.style.transform = `translateX(${95 - dragAmount}px)`;
                            }
                        }
                    } else {
                        content.style.transform = 'translateX(0px)';
                        if (bg) {
                            bg.style.width = '95px';
                            bg.style.opacity = '0';
                            bg.style.pointerEvents = 'none';
                            bg.style.transform = 'translateX(95px)';
                            bg.style.zIndex = '1';
                        }
                    }
                };

                const handleEnd = () => {
                    if (!isSwiping) return;
                    isSwiping = false;

                    const diffX = startX - currentX;
                    const baseOffset = isOpen ? 95 : 0;
                    const effectiveDiff = diffX + baseOffset;
                    const cardWidth = container.offsetWidth || 340;

                    if (effectiveDiff > 130 || effectiveDiff > cardWidth * 0.38) {
                        // Full swipe across -> Trigger deletion confirmation with card open
                        content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                        content.style.transform = 'translateX(-100px)';
                        if (bg) {
                            bg.style.transition = 'transform 0.25s ease, opacity 0.25s ease';
                            bg.style.width = '100px';
                            bg.style.transform = 'translateX(0px)';
                            bg.style.opacity = '1';
                            bg.style.pointerEvents = 'auto';
                            bg.style.zIndex = '10';
                        }
                        isOpen = true;
                        window.confirmDeleteSavedTrip(tripId, container, tripTitle);
                    } else if (effectiveDiff > 35) {
                        // Partial swipe -> Reveal red delete button
                        openSwipe();
                    } else {
                        // Small tap or drag -> Close
                        closeSwipe();
                    }

                    startX = 0;
                    startY = 0;
                    currentX = 0;
                    currentY = 0;
                    isHorizontalSwipe = false;
                    isGestureDetermined = false;
                };

                const handleCancel = () => {
                    if (!isSwiping) return;
                    isSwiping = false;
                    if (isOpen) {
                        openSwipe();
                    } else {
                        closeSwipe();
                    }
                };

                content.addEventListener('touchstart', (e) => {
                    if (e.touches && e.touches.length === 1) {
                        handleStart(e.touches[0].clientX, e.touches[0].clientY, e.target);
                    }
                }, { passive: true });

                content.addEventListener('touchmove', (e) => {
                    if (e.touches && e.touches.length === 1) {
                        handleMove(e.touches[0].clientX, e.touches[0].clientY);
                    }
                }, { passive: true });

                content.addEventListener('touchend', handleEnd, { passive: true });
                content.addEventListener('touchcancel', handleCancel, { passive: true });

                content.addEventListener('mousedown', (e) => handleStart(e.clientX, e.clientY, e.target));
                window.addEventListener('mousemove', (e) => { if (isSwiping) handleMove(e.clientX, e.clientY); });
                window.addEventListener('mouseup', () => { if (isSwiping) handleEnd(); });
            });
        }

        window.loadSavedTrips = window.fetchSavedTrips;

        // Render immediately from local cache or queued offline trips with 0ms latency
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        let initialTrips = [];
        if (token) {
            const cacheKey = 'saved_trips_' + token.substring(0, 10);
            const rawCached = localStorage.getItem(cacheKey);
            if (rawCached) {
                try {
                    const parsed = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(rawCached, null) : JSON.parse(rawCached);
                    if (parsed && Array.isArray(parsed.data)) {
                        initialTrips = parsed.data;
                    }
                } catch (e) { }
            }
        }
        renderSavedTrips(initialTrips);

        // Fetch and sync in background
        window.fetchSavedTrips();

        // ── Fast Polling Manager for Pending Proof Approvals ──
        window._pendingProofPollingInterval = null;
        window.checkAndManageApprovalPolling = function (trips) {
            const list = document.getElementById('saved-trips-list');
            const currentView = document.body.getAttribute('data-view');
            if (!list || (currentView && currentView !== 'saved_trips')) {
                if (window._pendingProofPollingInterval) {
                    clearInterval(window._pendingProofPollingInterval);
                    window._pendingProofPollingInterval = null;
                }
                return;
            }

            const tripList = trips || window._cachedSavedTrips || [];
            let hasPending = false;
            if (Array.isArray(tripList)) {
                for (const t of tripList) {
                    if (t && Array.isArray(t.items)) {
                        for (const it of t.items) {
                            if (!it) continue;
                            const hasProof = (Array.isArray(it.proof_images) && it.proof_images.length > 0) || it.proof_image;
                            const isApproved = Boolean(it.is_visited || it.proof_status === 'approved');
                            const isRejected = (it.proof_status === 'rejected');
                            if (!isApproved && !isRejected && hasProof && (it.proof_status === 'pending' || !it.proof_status)) {
                                hasPending = true;
                                break;
                            }
                        }
                    }
                    if (hasPending) break;
                }
            }

            window._hasPendingApprovalItems = hasPending;

            if (hasPending) {
                if (!window._pendingProofPollingInterval) {
                    console.log("⚡ Starting fast pending-approval polling (every 3.5s)...");
                    window._pendingProofPollingInterval = setInterval(() => {
                        const curView = document.body.getAttribute('data-view');
                        if (curView && curView !== 'saved_trips') {
                            clearInterval(window._pendingProofPollingInterval);
                            window._pendingProofPollingInterval = null;
                            return;
                        }
                        if (document.visibilityState === 'visible' && !window._isStartingTrip) {
                            window.fetchSavedTrips(true);
                        }
                    }, 3500);
                }
            } else {
                if (window._pendingProofPollingInterval) {
                    clearInterval(window._pendingProofPollingInterval);
                    window._pendingProofPollingInterval = null;
                    console.log("⚡ All proof items verified or none pending; cleared fast polling.");
                }
            }
        };

        // ── Auto-refresh setup for Saved Trips ──
        if (window._savedTripsInterval) {
            clearInterval(window._savedTripsInterval);
            window._savedTripsInterval = null;
        }

        // Periodic background sync every 30 seconds
        window._savedTripsInterval = setInterval(() => {
            const list = document.getElementById('saved-trips-list');
            const currentView = document.body.getAttribute('data-view');
            if (!list || (currentView && currentView !== 'saved_trips')) {
                clearInterval(window._savedTripsInterval);
                window._savedTripsInterval = null;
                return;
            }
            if (document.visibilityState === 'visible' && !window._isStartingTrip) {
                window.fetchSavedTrips(true);
            }
        }, 30000);

        // Event-based auto-refresh (visibility change, window focus, view loaded)
        if (!window._savedTripsVisibilityBound) {
            window._savedTripsVisibilityBound = true;
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && document.body.getAttribute('data-view') === 'saved_trips' && !window._isStartingTrip) {
                    window.fetchSavedTrips(true);
                }
            });
            window.addEventListener('focus', () => {
                if (document.body.getAttribute('data-view') === 'saved_trips' && !window._isStartingTrip) {
                    window.fetchSavedTrips(true);
                }
            });
            window.addEventListener('viewLoaded', (e) => {
                if (e && e.detail && e.detail.view === 'saved_trips') {
                    window._isStartingTrip = false;
                    window.fetchSavedTrips(true);
                }
            });
            window.addEventListener('checkins-synced', () => {
                if (document.body.getAttribute('data-view') === 'saved_trips') {
                    window.fetchSavedTrips(true);
                }
            });

            function updateSavedTripsOfflineStatus() {
                var badge = document.getElementById('saved-trips-offline-badge');
                var isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
                if (badge) {
                    badge.style.setProperty('display', isOffline ? 'inline-flex' : 'none', 'important');
                }
                if (!isOffline) {
                    try {
                        localStorage.removeItem('intan_elyu_offline_mode');
                        localStorage.removeItem('intan_elyu_offline_guest');
                    } catch(e) {}
                }
            }

            async function handleSavedTripsReconnect() {
                if (typeof window.enforceAllOfflineBadgesHidden === 'function') {
                    window.enforceAllOfflineBadgesHidden();
                }
                updateSavedTripsOfflineStatus();
                try {
                    localStorage.removeItem('intan_elyu_offline_mode');
                    localStorage.removeItem('intan_elyu_offline_guest');
                } catch(e) {}
                try {
                    const queuedTrips = (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function')
                        ? window.OfflineTripManager.getQueuedTrips()
                        : [];
                    if (queuedTrips.length > 0 && window.OfflineTripManager && typeof window.OfflineTripManager.syncQueuedTrips === 'function') {
                        if (typeof showToast === 'function') {
                            showToast("Connection restored. Syncing your offline trips...", "info", 3000);
                        }
                        const synced = await window.OfflineTripManager.syncQueuedTrips();
                        if (synced > 0 && typeof showToast === 'function') {
                            showToast(`${synced} offline trip(s) synced to cloud!`, "success", 4000);
                        }
                    }
                } catch (e) {
                    console.warn("Auto-sync error on reconnect:", e);
                }

                // Sync queued offline check-ins & photo proofs
                try {
                    if (window.OfflineCheckinManager && typeof window.OfflineCheckinManager.processQueue === 'function') {
                        await window.OfflineCheckinManager.processQueue();
                    }
                } catch (chkErr) {
                    console.warn("Auto-sync offline checkins error:", chkErr);
                }

                const currentView = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
                if (currentView === 'saved_trips' || !currentView) {
                    if (typeof window.fetchSavedTrips === 'function') {
                        window.fetchSavedTrips(true);
                    }
                }
            }

            window.addEventListener('online', handleSavedTripsReconnect);
            window.addEventListener('offline', updateSavedTripsOfflineStatus);
            window.addEventListener('appOnline', handleSavedTripsReconnect);
            window.addEventListener('appOffline', updateSavedTripsOfflineStatus);
            window.addEventListener('tripsSynced', () => {
                if (typeof window.fetchSavedTrips === 'function') {
                    window.fetchSavedTrips(true);
                }
            });
            window.addEventListener('offlineTripQueued', () => {
                if (typeof window.fetchSavedTrips === 'function') {
                    window.fetchSavedTrips(false);
                }
            });
            window.addEventListener('tripDeleted', (e) => {
                const deletedId = e?.detail?.id;
                if (!deletedId) return;
                const dStr = String(deletedId);
                if (Array.isArray(window._cachedSavedTrips)) {
                    window._cachedSavedTrips = window._cachedSavedTrips.filter(t => {
                        if (!t) return false;
                        const idStr = String(t.id || '');
                        const tripIdStr = String(t.trip_id || '');
                        const localIdStr = String(t.local_id || '');
                        return idStr !== dStr && tripIdStr !== dStr && localIdStr !== dStr;
                    });
                }
                const cards = document.querySelectorAll(`.trip-swipe-container[data-trip-id="${deletedId}"]`);
                cards.forEach(c => c.remove());
                const allCards = document.querySelectorAll('.trip-swipe-container');
                if (allCards.length === 0) {
                    if (typeof renderSavedTrips === 'function') renderSavedTrips([]);
                }
            });
            setTimeout(updateSavedTripsOfflineStatus, 100);
        }

        // Immediately fetch fresh saved trips from network and update cache
        if (typeof window.fetchSavedTrips === 'function') {
            window.fetchSavedTrips(true);
        }

    })();
</script>