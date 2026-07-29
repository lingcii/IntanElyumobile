<?php
$pageTitle = 'My Saved Trips';
$backRoute = 'itinerary';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/testimony_modal.php'; ?>
<link rel="stylesheet" href="assets/css/views/saved_trips.css?v=<?= time() ?>">

<style>
.timeline-collapsible {
    display: grid;
    grid-template-rows: 0fr;
    opacity: 0;
    transition: grid-template-rows 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.3s ease, margin-top 0.4s ease;
}
.timeline-collapsible.expanded {
    grid-template-rows: 1fr;
    opacity: 1;
    margin-top: 16px;
}
.timeline-inner {
    overflow: hidden;
}
.start-collapsible {
    max-width: 0;
    opacity: 0;
    overflow: hidden;
    transition: max-width 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.3s ease;
    display: flex;
}
.start-collapsible.expanded {
    max-width: 200px;
    opacity: 1;
}
</style>

<!-- Saved Trips Container -->
<div class="saved-trips-page-container has-header animate-slide-up">
    <div id="saved-trips-list" style="margin-top: 16px;">
        <!-- Fetched saved trips will be injected here -->
        <p style="text-align:center; color:#999; margin-top: 20px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading saved trips...
        </p>
    </div>
</div>

<!-- Check-in Verification Modal (GPS and Photo Proof) -->
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.78); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:99999; justify-content:center; align-items:center;">
    <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:1px solid rgba(56, 189, 248, 0.3); border-radius:24px; padding:28px 24px; width:90%; max-width:380px; box-shadow:0 24px 60px rgba(0,0,0,0.6), 0 0 30px rgba(56,189,248,0.15); text-align:center;">
        <i class="fa-solid fa-camera" style="font-size:32px; color:#38bdf8; margin-bottom:10px; display:block;"></i>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Claim Your Reward</h3>
        <p style="font-size:13px; color:rgba(226, 232, 240, 0.85); margin-bottom:20px; line-height:1.5;">Take a selfie or capture a photo at this destination to verify your visit and earn <strong style="color:#38bdf8; font-weight:800;">+50 XP</strong> & <strong style="color:#38bdf8; font-weight:800;">+50 Points</strong>.</p>

        <input type="hidden" id="checkin-item-id">
        
        <!-- Step 1: Photo Proof -->
        <div style="margin-bottom: 16px; text-align: left;">
            <label style="font-size:11px; font-weight:800; color:#38bdf8; margin-bottom:6px; display:block; text-transform:uppercase; letter-spacing:0.5px;">Step 1: Photo Proof (Required)</label>
            <input type="file" id="checkin-proof-image" accept="image/*" style="display:none;" onchange="window.handlePhotoSelected(this)">
            <button type="button" onclick="window.openCheckinImagePickerModal()" id="btn-select-photo" style="width:100%; padding:14px; background:rgba(56,189,248,0.1); border:1.5px dashed rgba(56,189,248,0.4); border-radius:14px; color:#38bdf8; font-weight:800; font-size:13px; display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer; transition:all 0.2s ease;">
                <i class="fa-solid fa-camera" style="font-size:16px;"></i> <span id="photo-status-text">Take or Choose Photo</span>
            </button>
            
            <!-- Picture Preview Container (Displays actual picture preview instead of filename string) -->
            <div id="checkin-photo-preview-container" style="display:none; margin-top:12px; position:relative; border-radius:16px; overflow:hidden; border:1.5px solid rgba(56,189,248,0.4); background:rgba(15,23,42,0.8); box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                <img id="checkin-photo-preview-img" src="" alt="Proof Preview" style="width:100%; max-height:180px; object-fit:cover; display:block;">
                <div style="position:absolute; top:8px; right:8px; display:flex; gap:6px;">
                    <button type="button" onclick="window.openCheckinImagePickerModal()" title="Change Picture" style="background:rgba(15,23,42,0.85); color:#38bdf8; border:1px solid rgba(56,189,248,0.4); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; backdrop-filter:blur(6px);">
                        <i class="fa-solid fa-arrows-rotate" style="font-size:13px;"></i>
                    </button>
                    <button type="button" onclick="window.removeCheckinPhoto()" title="Remove Picture" style="background:rgba(239,68,68,0.85); color:#ffffff; border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; backdrop-filter:blur(6px);">
                        <i class="fa-solid fa-xmark" style="font-size:14px;"></i>
                    </button>
                </div>
                <div style="padding:6px 10px; background:rgba(15,23,42,0.9); font-size:10px; font-weight:700; color:#38bdf8; text-transform:uppercase; text-align:center; border-top:1px solid rgba(255,255,255,0.08);">
                    <i class="fa-solid fa-circle-check" style="margin-right:4px; color:#34c759;"></i> Picture Proof Attached
                </div>
            </div>
        </div>

        <!-- Step 2: Location Verification -->
        <div style="margin-bottom: 12px; text-align: left;">
            <label style="font-size:11px; font-weight:800; color:#38bdf8; margin-bottom:6px; display:block; text-transform:uppercase; letter-spacing:0.5px;">Step 2: Location Check-in</label>
            <button class="btn-primary" id="btn-verify-gps" style="width:100%; padding:14px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #38bdf8 0%, #2563eb 100%); border:1px solid rgba(255,255,255,0.25); color:#ffffff; border-radius:14px; box-shadow:0 4px 16px rgba(56,189,248,0.4); cursor:pointer;" onclick="verifyGpsCheckIn()">
                <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Submit
            </button>
        </div>

        <button style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#e2e8f0; font-size:13px; font-weight:700; cursor:pointer;" onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<!-- Check-in Image Picker Choice Modal -->
<div id="checkin-image-picker-modal" onclick="if(event.target===this) window.closeCheckinImagePickerModal()" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:999999; align-items:flex-end; justify-content:center; padding:0; margin:0; box-sizing:border-box;">
    <div style="background:linear-gradient(135deg, rgba(30,41,59,0.98) 0%, rgba(15,23,42,1) 100%); border-top:1px solid rgba(56,189,248,0.3); border-radius:28px 28px 0 0; width:100%; max-width:500px; padding:26px 22px; box-shadow:0 -10px 45px rgba(0,0,0,0.8); animation:slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); box-sizing:border-box;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:17px; font-weight:800; color:#f8fafc; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-camera" style="color:#38bdf8; font-size:18px;"></i> Attach Proof Photo
            </h3>
            <button type="button" onclick="window.closeCheckinImagePickerModal()" style="background:rgba(255,255,255,0.08); border:none; color:#94a3b8; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="font-size:15px;"></i>
            </button>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <button type="button" onclick="window.selectCheckinImageSource('camera')" style="width:100%; padding:15px; background:linear-gradient(135deg, rgba(56,189,248,0.18) 0%, rgba(37,99,235,0.22) 100%); border:1px solid rgba(56,189,248,0.35); border-radius:18px; color:#38bdf8; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                <i class="fa-solid fa-camera" style="font-size:17px;"></i> Take Photo with Camera
            </button>
            <button type="button" onclick="window.selectCheckinImageSource('gallery')" style="width:100%; padding:15px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:18px; color:#f8fafc; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer; transition:transform 0.15s ease, background 0.15s ease;">
                <i class="fa-solid fa-images" style="font-size:17px; color:#38bdf8;"></i> Choose from Photo Gallery
            </button>
            <button type="button" onclick="window.closeCheckinImagePickerModal()" style="width:100%; padding:12px; background:transparent; border:none; color:#94a3b8; font-size:13px; font-weight:600; cursor:pointer; margin-top:4px;">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Complete Trip Confirmation Modal -->
<div id="complete-trip-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.8); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center;">
    <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); border:1px solid rgba(16, 185, 129, 0.35); border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 24px 60px rgba(0,0,0,0.6), 0 0 30px rgba(16, 185, 129, 0.2); text-align:center;">
        <i class="fa-solid fa-flag-checkered" style="font-size:32px; color:#10b981; margin-bottom:10px; display:block;"></i>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Complete Trip?</h3>
        <p style="font-size:13px; color:rgba(226, 232, 240, 0.85); margin-bottom:22px; line-height:1.5;">Are you sure you want to mark this trip as completed? It will be moved to your History.</p>

        <div style="display:flex; gap:10px;">
            <button type="button" style="flex:1; padding:13px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#e2e8f0; font-size:13px; font-weight:700; cursor:pointer;" onclick="window.closeCompleteTripModal()">
                Cancel
            </button>
            <button type="button" id="btn-confirm-complete-trip" style="flex:1; padding:13px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:1px solid rgba(255,255,255,0.2); color:#ffffff; border-radius:14px; box-shadow:0 4px 16px rgba(16,185,129,0.4); cursor:pointer;" onclick="window.executeConfirmCompleteTrip()">
                Complete
            </button>
        </div>
    </div>
</div>

<!-- Delete Trip Confirmation Modal -->
<div id="delete-trip-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.8); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center;">
    <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); border:1px solid rgba(239, 68, 68, 0.35); border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow:0 24px 60px rgba(0,0,0,0.6), 0 0 30px rgba(239, 68, 68, 0.2); text-align:center;">
        <i class="fa-solid fa-trash-can" style="font-size:32px; color:#ef4444; margin-bottom:10px; display:block;"></i>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Delete Saved Trip?</h3>
        <p id="delete-trip-title-text" style="font-size:13px; color:rgba(226, 232, 240, 0.8); margin-bottom:22px; line-height:1.5;">Are you sure you want to delete this trip? All saved itinerary items will be removed.</p>

        <div style="display:flex; gap:10px;">
            <button type="button" style="flex:1; padding:13px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#e2e8f0; font-size:13px; font-weight:700; cursor:pointer;" onclick="window.closeDeleteTripModal()">
                Cancel
            </button>
            <button type="button" id="btn-confirm-delete-trip" style="flex:1; padding:13px; font-size:14px; font-weight:800; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border:1px solid rgba(255,255,255,0.2); color:#ffffff; border-radius:14px; box-shadow:0 4px 16px rgba(239,68,68,0.4); cursor:pointer;" onclick="window.executeConfirmDeleteTrip()">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

    window.fetchSavedTrips = async function(forceRefresh = false) {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) {
            if (typeof window.navigateTo === 'function') window.navigateTo('auth');
            return;
        }

        const cacheKey = 'saved_trips_' + token.substring(0, 10);

        await window.useCache(
            cacheKey,
            async () => {
                const response = await fetch(backendUrl + '/api/tourist/itineraries', {
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
                if (!response.ok) throw new Error("Failed to fetch saved trips");
                const data = await response.json();
                return data.itineraries || [];
            },
            (itineraries) => {
                if (itineraries) {
                    renderSavedTrips(itineraries);
                } else {
                    const list = document.getElementById('saved-trips-list');
                    if(list) list.innerHTML = '<p style="text-align:center; color:#999; margin-top: 20px;">Failed to load saved trips.</p>';
                }
            },
            forceRefresh,
            60000 // 1 minute TTL
        );
    };

    function renderSavedTrips(itineraries) {
        const list = document.getElementById('saved-trips-list');
        
        if (!list) return;

        if (!itineraries || itineraries.length === 0) {
            list.innerHTML = '<p style="text-align:center; color:#999; margin-top: 20px;">No saved trips found.</p>';
            return;
        }

        const activeItineraries = itineraries.filter(trip => trip.status !== 'completed');

        if (activeItineraries.length === 0) {
            list.innerHTML = '<p style="text-align:center; color:#999; margin-top: 20px;">No active trips found.</p>';
            return;
        }

        let html = '';

        activeItineraries.forEach(trip => {
            let budgetIndicator = '';
            if (trip.budget && trip.budget > 0) {
                const cost = parseFloat(trip.total_cost || 0);
                const budget = parseFloat(trip.budget);
                const pct = cost / budget;
                
                let color = '#34C759'; // Green (Safe)
                if (pct >= 1.0) color = '#FF3B30'; // Red (Over/Warning)
                else if (pct >= 0.8) color = '#FF9500'; // Orange (Near)
                
                budgetIndicator = `<span style="display:inline-block; width:10px; height:10px; border-radius:50%; background-color:${color}; margin-left:6px; box-shadow:0 0 4px ${color}80;" title="Estimated Cost: ₱${cost.toFixed(2)}"></span>`;
            }

            const safeTitle = trip.title ? trip.title.replace(/"/g, '&quot;').replace(/'/g, "\\'") : 'Saved Trip';
            html += `
            <div class="trip-swipe-container" data-trip-id="${trip.id}" data-trip-title="${safeTitle}" style="position:relative; overflow:hidden; border-radius:24px; margin-bottom:20px;">
                <!-- Red Delete Action Button (Slides smoothly in tandem from right wall) -->
                <div class="trip-swipe-bg" onclick="window.confirmDeleteSavedTrip('${trip.id}', this.closest('.trip-swipe-container'), '${safeTitle}')" style="position:absolute; top:0; right:0; bottom:0; width:95px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius:0 24px 24px 0; display:flex; align-items:center; justify-content:center; color:#ffffff; font-size:13px; font-weight:800; gap:6px; z-index:1; cursor:pointer; transform:translateX(95px); transition:transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                    <i class="fa-solid fa-trash-can"></i> Delete
                </div>
                
                <!-- Rich Glassmorphic Front Card Content (Overlays z-index 2) -->
                <div class="trip-swipe-content" style="position:relative; z-index:2; background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.85) 100%); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 24px; padding: 22px; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-radius 0.25s ease; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(56, 189, 248, 0.08);">
                    <h3 style="margin: 0 0 6px 0; font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">${trip.title}</h3>
                    <p style="font-size: 13px; color: rgba(226, 232, 240, 0.85); margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span><i class="fa-regular fa-calendar" style="color: #38bdf8; margin-right: 4px;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set'}</span> 
                        ${trip.budget ? '&bull; <span style="background: rgba(56,189,248,0.12); border: 1px solid rgba(56,189,248,0.25); color: #38bdf8; padding: 3px 10px; border-radius: 100px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-coins" style="font-size:10px;"></i>Budget: ₱' + parseFloat(trip.budget).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + budgetIndicator + '</span>' : ''}
                    </p>
                    <div class="timeline-collapsible" id="timeline-${trip.id}">
                        <div class="timeline-inner">
                            <div class="timeline">`;
                    
                let unvisitedCount = 0;
                if (trip.items && trip.items.length) {
                    trip.items.forEach((item, index) => {
                        const dest = item.destination;
                        const isVisited = item.is_visited;
                        if (!isVisited) unvisitedCount++;

                        let proofImgHtml = '';
                        if (item.proof_image) {
                            let pUrl = item.proof_image;
                            if (!pUrl.startsWith('http') && !pUrl.startsWith('data:') && !pUrl.startsWith('blob:')) {
                                let b = (window.backendUrl || '').replace(/\/+$/, '');
                                pUrl = b + '/' + pUrl.replace(/^\//, '');
                            }
                            let fallbackUrl = (window.backendUrl || '').replace(/\/+$/, '') + '/api/image/' + item.proof_image.replace(/^\//, '');
                            proofImgHtml = `<img src="${pUrl}" onerror="if(this.src!=='${fallbackUrl}'){this.src='${fallbackUrl}';}" alt="Proof" style="width:52px; height:52px; border-radius:10px; object-fit:cover; border:1.5px solid ${isVisited ? 'rgba(52,199,89,0.5)' : 'rgba(255,149,0,0.5)'}; box-shadow:0 4px 12px rgba(0,0,0,0.3); flex-shrink:0;">`;
                        }

                        html += `
                        <div class="timeline-item ${isVisited ? 'completed' : ''}" style="margin-bottom: 12px;">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content" style="padding:14px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; display:flex; flex-direction:column; gap:8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h4 style="margin:0; font-size:15px; font-weight:800; color:#ffffff;">${dest ? dest.name : 'Unknown Destination'}</h4>
                                    ${(dest && dest.classification_status) ? `<span style="padding: 2px 8px; border-radius: 100px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')};">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : ''}
                                </div>
                                ${(dest && (dest.accessible_by_private_vehicle === 0 || dest.accessible_by_private_vehicle === false)) ? `<div style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); border-radius:10px; padding:8px 12px; display:flex; gap:8px; align-items:flex-start; margin-top:4px;"><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444; font-size:13px; margin-top:2px;"></i><div><h5 style="margin:0 0 2px 0; font-size:11px; font-weight:800; color:#ef4444; text-transform:uppercase;">Inaccessible by Private Car</h5><p style="margin:0; font-size:10px; color:rgba(226,232,240,0.8); line-height:1.3;">Prepare to hike or use specialized local transport.</p></div></div>` : ''}

                                ${isVisited || item.proof_status === 'approved' ? 
                                    `<div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                                        ${proofImgHtml}
                                        <div>
                                            <span style="color:#34c759; font-size:12px; font-weight:800; display:block;">
                                                <i class="fa-solid fa-circle-check" style="margin-right:4px;"></i> Visited & Verified
                                            </span>
                                            <span style="font-size:10px; color:rgba(226,232,240,0.6);">Confirmed in Database</span>
                                        </div>
                                    </div>` : 
                                    (item.proof_status === 'rejected' ? 
                                        `<div style="display:flex; flex-direction:column; gap:6px; margin-top:4px;">
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                ${proofImgHtml}
                                                <div>
                                                    <span style="color:#ef4444; font-size:12px; font-weight:800; display:block;">
                                                        <i class="fa-solid fa-circle-xmark" style="margin-right:4px;"></i> Proof Rejected
                                                    </span>
                                                    <span style="font-size:10px; color:rgba(239,68,68,0.8);">${item.rejection_reason || 'Please upload a clearer photo taken at the destination.'}</span>
                                                </div>
                                            </div>
                                            <button class="btn-primary" style="padding: 8px 14px; font-size:12px; font-weight:700; width:max-content; border-radius:100px; background: linear-gradient(135deg, #ef4444, #dc2626); border:none; box-shadow: 0 4px 12px rgba(239,68,68,0.3); color:#fff; cursor:pointer;" onclick="window.openCheckinModal('${item.id}')">
                                                <i class="fa-solid fa-camera" style="margin-right:4px;"></i> Re-upload Photo Proof
                                            </button>
                                        </div>` : 
                                        (item.proof_image ? 
                                            `<div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
                                                ${proofImgHtml}
                                                <div>
                                                     <span style="background:rgba(255,149,0,0.15); border:1px solid rgba(255,149,0,0.35); color:#FF9500; font-size:11px; font-weight:800; padding:3px 10px; border-radius:100px; display:inline-flex; align-items:center; gap:4px;">
                                                         <i class="fa-solid fa-clock"></i> Pending Confirmation
                                                     </span>
                                                    <span style="font-size:10px; color:rgba(226,232,240,0.6); display:block; margin-top:4px;">Awaiting Validation</span>
                                                </div>
                                            </div>` : 
                                            `<button class="btn-primary" style="padding: 8px 14px; font-size:12px; font-weight:700; width:max-content; border-radius:100px; background: linear-gradient(135deg, #38bdf8, #2563eb); border:none; box-shadow: 0 4px 12px rgba(56,189,248,0.3); color:#fff; cursor:pointer;" onclick="window.openCheckinModal('${item.id}')">
                                                <i class="fa-solid fa-location-arrow" style="margin-right:4px;"></i> Check In (+50 XP)
                                             </button>`))
                                }
                            </div>
                        </div>`;
                    });
                } else {
                    html += `<p style="font-size:13px; color:rgba(226,232,240,0.7); margin:10px 0;">No destinations in this trip.</p>`;
                }
                    
                html += `</div></div></div>`; // Close timeline, timeline-inner, and timeline-collapsible

                // Action buttons
                html += `<div style="display:flex; gap:10px; margin-top:16px;">`;

                // View Details button
                html += `
                <button class="btn-primary" style="flex:1; background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; padding: 14px; border-radius: 14px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.25s ease;" onclick="window.toggleTripDetails('${trip.id}')">
                    <i class="fa-solid fa-chevron-down" id="chevron-${trip.id}" style="margin-right:8px; transition:transform 0.3s ease;"></i> View Details
                </button>`;

                // Start / Complete button wrapper
                html += `<div class="start-collapsible" id="start-wrapper-${trip.id}">`;
                if (unvisitedCount === 0 && trip.items && trip.items.length > 0) {
                    html += `
                    <button class="btn-primary" style="width:100%; white-space:nowrap; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 14px; border-radius: 14px; font-weight: 800; font-size: 14px; box-shadow: 0 4px 16px rgba(16,185,129,0.35); cursor: pointer;" onclick="window.markTripCompleted('${trip.id}')">
                        <i class="fa-solid fa-flag-checkered" style="margin-right:6px;"></i> Complete
                    </button>`;
                } else {
                    html += `
                    <button class="btn-primary" style="width:100%; white-space:nowrap; background: linear-gradient(135deg, #38bdf8 0%, #2563eb 100%); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 14px; border-radius: 14px; font-weight: 800; font-size: 14px; box-shadow: 0 4px 16px rgba(56,189,248,0.35); cursor: pointer;" onclick="window.startTrip('${trip.id}')">
                        <i class="fa-solid fa-play" style="margin-right:6px;"></i> Start
                    </button>`;
                }
                html += `</div>`; // Close start-collapsible
                
                html += `</div></div></div>`; // Close trip-swipe-content and trip-swipe-container
            });

            list.innerHTML = html;
            initSavedTripsSwipe();
    }

    window.toggleTripDetails = function(tripId) {
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
        }
    };

    window.startTrip = function(tripId) {
        if (typeof showToast === 'function') showToast("Starting trip preview...");
        setTimeout(() => {
            window.location.href = '?view=trip_map&trip_id=' + tripId;
        }, 1000);
    };

    window.openCheckinModal = function(itemId) {
        document.getElementById('checkin-item-id').value = itemId;
        document.getElementById('checkin-modal').style.display = 'flex';
    };

    window.selectedCheckinImageFile = null;

    window.openCheckinImagePickerModal = function() {
        const modal = document.getElementById('checkin-image-picker-modal');
        if (modal) modal.style.display = 'flex';
    };

    window.closeCheckinImagePickerModal = function() {
        const modal = document.getElementById('checkin-image-picker-modal');
        if (modal) modal.style.display = 'none';
    };

    window.selectCheckinImageSource = async function(mode) {
        window.closeCheckinImagePickerModal();
        const input = document.getElementById('checkin-proof-image');

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
                    quality: 85,
                    allowEditing: false,
                    resultType: 'dataUrl',
                    source: mode === 'camera' ? 'CAMERA' : 'PHOTOS'
                });

                if (image && image.dataUrl) {
                    const res = await fetch(image.dataUrl);
                    const blob = await res.blob();
                    const file = new File([blob], 'proof_' + Date.now() + '.jpg', { type: blob.type || 'image/jpeg' });
                    window.selectedCheckinImageFile = file;
                    window.updateCheckinPhotoPreview(image.dataUrl);
                }
            } catch (err) {
                console.warn('Capacitor Camera cancel or error:', err);
            }
        } else {
            if (!input) return;
            if (mode === 'camera') {
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }
            input.click();
        }
    };

    window.handlePhotoSelected = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            window.selectedCheckinImageFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                window.updateCheckinPhotoPreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    };

    window.updateCheckinPhotoPreview = function(dataUrl) {
        const previewContainer = document.getElementById('checkin-photo-preview-container');
        const previewImg = document.getElementById('checkin-photo-preview-img');
        const btnText = document.getElementById('photo-status-text');
        const btn = document.getElementById('btn-select-photo');

        if (previewContainer && previewImg) {
            previewImg.src = dataUrl;
            previewContainer.style.display = 'block';
        }

        if (btnText) btnText.textContent = 'Change Photo 📸';
        if (btn) {
            btn.style.background = 'rgba(52, 199, 89, 0.15)';
            btn.style.borderColor = 'rgba(52, 199, 89, 0.5)';
            btn.style.color = '#34c759';
        }
    };

    window.removeCheckinPhoto = function() {
        window.selectedCheckinImageFile = null;
        const imgInput = document.getElementById('checkin-proof-image');
        if (imgInput) imgInput.value = '';

        const previewContainer = document.getElementById('checkin-photo-preview-container');
        const previewImg = document.getElementById('checkin-photo-preview-img');
        if (previewContainer) previewContainer.style.display = 'none';
        if (previewImg) previewImg.src = '';

        const photoBtn = document.getElementById('btn-select-photo');
        const photoText = document.getElementById('photo-status-text');
        if (photoText) photoText.textContent = 'Take or Choose Photo';
        if (photoBtn) {
            photoBtn.style.background = 'rgba(56,189,248,0.1)';
            photoBtn.style.borderColor = 'rgba(56,189,248,0.4)';
            photoBtn.style.color = '#38bdf8';
        }
    };

    window.closeCheckinModal = function() {
        document.getElementById('checkin-modal').style.display = 'none';
        document.getElementById('checkin-item-id').value = '';
        window.removeCheckinPhoto();

        const btn = document.getElementById('btn-verify-gps');
        if (btn) { btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Submit'; btn.disabled = false; }
    };

    window.verifyGpsCheckIn = function() {
        const imageFile = window.selectedCheckinImageFile || (document.getElementById('checkin-proof-image') ? document.getElementById('checkin-proof-image').files[0] : null);
        if (!imageFile) {
            if (typeof showToast === 'function') showToast('Please select or capture a photo proof first! 📸');
            return;
        }

        if (!navigator.geolocation) {
            if (typeof showToast === 'function') showToast('Geolocation is not supported by your browser.');
            return;
        }

        const btn = document.getElementById('btn-verify-gps');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Getting your location...';
        btn.disabled = true;

        const options = {
            enableHighAccuracy: false,
            timeout: 15000,
            maximumAge: 60000
        };

        const doSubmitCheckin = async (lat, lng) => {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Verifying...';
            const itemId = document.getElementById('checkin-item-id').value;
            if (!itemId) return;

            const formData = new FormData();
            formData.append('lat', lat);
            formData.append('lng', lng);
            formData.append('image', imageFile);

            try {
                const response = await fetch(backendUrl + '/api/tourist/itineraries/items/' + itemId + '/visit', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + (localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('intan_elyu_token'))
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    if (typeof showToast === 'function') showToast(result.message || 'Checked in! 🌟 Earned +50 XP & Points');
                    closeCheckinModal();
                    window.fetchSavedTrips(true);
                } else {
                    if (typeof showToast === 'function') showToast(result.message || 'Check-in failed.');
                    btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo';
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Check-in error:', error);
                if (typeof showToast === 'function') showToast('Network error. Please try again.');
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo';
                btn.disabled = false;
            }
        };

        navigator.geolocation.getCurrentPosition(
            (position) => {
                doSubmitCheckin(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                console.warn('GPS error, using fallback location:', error);
                if (typeof showToast === 'function') showToast('GPS timeout. Using approximate location...');
                // Fallback to La Union default coordinates if GPS times out
                doSubmitCheckin(16.6159, 120.3209);
            },
            options
        );
    };

    window.markTripCompleted = function(id) {
        window._pendingCompleteTripId = id;
        const modal = document.getElementById('complete-trip-modal');
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeCompleteTripModal = function() {
        const modal = document.getElementById('complete-trip-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.executeConfirmCompleteTrip = async function() {
        const id = window._pendingCompleteTripId;
        if (!id) return;

        window.closeCompleteTripModal();
        
        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries/' + id + '/complete', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + (localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('intan_elyu_token'))
                }
            });
            
            const data = await response.json();
            if (response.ok) {
                if (typeof showToast === 'function') showToast(data.message || "Trip completed!");
                
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
                            list.innerHTML = '<p style="text-align:center; color:#999; margin-top: 20px;">No active trips found.</p>';
                        }
                    }, 300);
                }

                // Invalidate cache and refetch saved trips
                const token = localStorage.getItem('intan_elyu_token');
                if (token) localStorage.removeItem('saved_trips_' + token.substring(0, 10));
                window.fetchSavedTrips(true);

                // Show Trip Completed modal with Review buttons for visited spots
                const visitedItems = data.visited_items || [];
                if (visitedItems.length > 0) {
                    showTripCompletionReviewModal(visitedItems);
                }
            } else {
                if (typeof showToast === 'function') showToast(data.message || "Failed to complete trip.");
            }
        } catch (error) {
            console.error("Error completing trip:", error);
            if (typeof showToast === 'function') showToast("Network error.");
        }
    };

    function showTripCompletionReviewModal(visitedItems) {
        // Remove any existing completion modal
        const existing = document.getElementById('trip-completion-review-modal');
        if (existing) existing.remove();

        let destListHtml = '';
        visitedItems.forEach((item, idx) => {
            const spotId = item.tourist_spot_id || item.id;
            destListHtml += `
                <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:14px; margin-bottom:8px;">
                    <div style="width:32px; height:32px; border-radius:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:900; font-size:13px; color:#fff;">${idx + 1}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:14px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${item.destination_name || 'Destination'}</div>
                        <div style="font-size:11px; color:rgba(148,163,184,0.8); font-weight:600;">Tap below to leave a review</div>
                    </div>
                    <button type="button" onclick="window.startReviewFromCompletion('${spotId}', this)" style="padding:6px 14px; border-radius:100px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; font-size:12px; font-weight:800; cursor:pointer; white-space:nowrap; box-shadow:0 2px 8px rgba(56,189,248,0.3); display:inline-flex; align-items:center; gap:4px;">
                        <i class="fa-solid fa-pen" style="font-size:10px;"></i> Review
                    </button>
                </div>`;
        });

        const modalHtml = `
        <div id="trip-completion-review-modal" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:99998; display:flex; align-items:center; justify-content:center; padding:20px; opacity:0; transition:opacity 0.3s ease;">
            <div style="background:linear-gradient(145deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); border:1.5px solid rgba(16, 185, 129, 0.4); border-radius:24px; padding:28px 22px; width:100%; max-width:380px; box-shadow:0 24px 60px rgba(0,0,0,0.6), 0 0 30px rgba(16,185,129,0.15); text-align:center; transform:scale(0.92) translateY(12px); transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="width:60px; height:60px; border-radius:50%; background:rgba(16, 185, 129, 0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 14px auto; box-shadow:0 0 20px rgba(16,185,129,0.3);">
                    <i class="fa-solid fa-flag-checkered" style="font-size:28px; color:#10b981;"></i>
                </div>
                <h3 style="margin:0 0 4px; color:#fff; font-size:20px; font-weight:900;">Trip Completed!</h3>
                <p style="font-size:12px; color:rgba(148,163,184,0.85); margin:0 0 18px 0; line-height:1.5;">
                    Share your experience! Review the destinations you visited to help fellow travelers.
                </p>
                <div style="text-align:left; max-height:220px; overflow-y:auto; margin-bottom:16px; padding-right:4px;">
                    ${destListHtml}
                </div>
                <button type="button" id="btn-trip-completion-close" onclick="window.closeTripCompletionReviewModal()" style="width:100%; padding:13px; border-radius:100px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#e2e8f0; font-size:13px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                    Maybe Later
                </button>
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

    window.closeTripCompletionReviewModal = function() {
        const modal = document.getElementById('trip-completion-review-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 320);
        }
    };

    window.startReviewFromCompletion = function(spotId, btnEl) {
        if (btnEl) {
            btnEl.dataset.reviewing = "true";
            window._lastReviewedBtn = btnEl;
        }
        if (typeof window.openWriteTestimonyModal === 'function') {
            window.openWriteTestimonyModal(spotId);
        }
    };

    window.confirmDeleteSavedTrip = function(id, container, title) {
        window._pendingDeleteTripId = id;
        window._pendingDeleteTripContainer = container;
        window._pendingDeleteTripTitle = title || container?.dataset?.tripTitle || '';

        const modal = document.getElementById('delete-trip-modal');
        const textEl = document.getElementById('delete-trip-title-text');
        if (textEl) {
            const displayTitle = window._pendingDeleteTripTitle || 'this trip';
            textEl.textContent = `Are you sure you want to delete "${displayTitle}"? All saved itinerary items will be removed.`;
        }
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeDeleteTripModal = function() {
        const modal = document.getElementById('delete-trip-modal');
        if (modal) modal.style.display = 'none';

        const container = window._pendingDeleteTripContainer;
        if (container) {
            const content = container.querySelector('.trip-swipe-content');
            if (content) { content.style.transform = 'translateX(0px)'; content.style.borderRadius = '24px'; }
        }

        window._pendingDeleteTripId = null;
        window._pendingDeleteTripContainer = null;
        window._pendingDeleteTripTitle = null;
    };

    window.executeConfirmDeleteTrip = async function() {
        const id = window._pendingDeleteTripId;
        const element = window._pendingDeleteTripContainer;
        const deletedTitle = window._pendingDeleteTripTitle || '';
        const modal = document.getElementById('delete-trip-modal');
        if (modal) modal.style.display = 'none';

        if (!id) return;
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return;

        try {
            const res = await fetch(backendUrl + '/api/tourist/itineraries/' + id, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                // Deactivate any quest timer matching the deleted trip title
                const activeTimers = JSON.parse(localStorage.getItem('active_quests_timers') || '{}');
                let updated = false;
                Object.keys(activeTimers).forEach(qId => {
                    if (deletedTitle.toLowerCase().includes((activeTimers[qId].questName || '').toLowerCase())) {
                        delete activeTimers[qId];
                        updated = true;
                    }
                });
                if (updated) {
                    localStorage.setItem('active_quests_timers', JSON.stringify(activeTimers));
                }

                if (typeof showToast === 'function') showToast("Trip deleted successfully.");
                if (element) {
                    element.style.transition = 'all 0.35s cubic-bezier(0.16, 1, 0.3, 1)';
                    element.style.opacity = '0';
                    element.style.transform = 'scale(0.85) translateY(-12px)';
                    setTimeout(() => {
                        element.remove();
                        window.fetchSavedTrips(true);
                    }, 350);
                } else {
                    window.fetchSavedTrips(true);
                }
            } else {
                if (typeof showToast === 'function') showToast("Failed to delete trip.");
                if (element) {
                    const content = element.querySelector('.trip-swipe-content');
                    if (content) { content.style.transform = 'translateX(0px)'; content.style.borderRadius = '24px'; }
                }
            }
        } catch (e) {
            console.error('Delete trip error:', e);
            if (typeof showToast === 'function') showToast("Network error.");
        } finally {
            window._pendingDeleteTripId = null;
            window._pendingDeleteTripContainer = null;
        }
    };

    function initSavedTripsSwipe() {
        const containers = document.querySelectorAll('.trip-swipe-container');
        containers.forEach(container => {
            let startX = 0;
            let currentX = 0;
            let isSwiping = false;
            let moved = false;
            const tripId = container.dataset.tripId;
            const tripTitle = container.dataset.tripTitle || 'this trip';
            const content = container.querySelector('.trip-swipe-content');
            const bg = container.querySelector('.trip-swipe-bg');
            if (!content) return;

            const handleStart = (clientX) => {
                startX = clientX;
                currentX = startX;
                isSwiping = true;
                moved = false;
                content.style.transition = 'none';
                if (bg) bg.style.transition = 'none';
            };

            const handleMove = (clientX) => {
                if (!isSwiping) return;
                currentX = clientX;
                const diff = startX - currentX;
                if (Math.abs(diff) > 5) moved = true;

                if (diff > 0) {
                    const moveX = Math.min(diff, 95);
                    content.style.transform = `translateX(-${moveX}px)`;
                    content.style.borderRadius = moveX > 5 ? '24px 0 0 24px' : '24px';
                    if (bg) bg.style.transform = `translateX(${95 - moveX}px)`;
                } else if (diff < -5) {
                    content.style.transform = 'translateX(0px)';
                    content.style.borderRadius = '24px';
                    if (bg) bg.style.transform = 'translateX(95px)';
                }
            };

            const handleEnd = () => {
                if (!isSwiping) return;
                content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-radius 0.25s ease';
                if (bg) bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';

                const diff = startX - currentX;
                if (moved && diff > 90) {
                    // Full swipe across -> Trigger confirmation modal directly!
                    content.style.transform = 'translateX(-95px)';
                    content.style.borderRadius = '24px 0 0 24px';
                    if (bg) bg.style.transform = 'translateX(0px)';
                    window.confirmDeleteSavedTrip(tripId, container, tripTitle);
                } else if (moved && diff > 35) {
                    // Partial swipe -> Reveal red delete action button
                    content.style.transform = 'translateX(-95px)';
                    content.style.borderRadius = '24px 0 0 24px';
                    if (bg) bg.style.transform = 'translateX(0px)';
                } else {
                    // Slight drag or tap -> Snap closed
                    content.style.transform = 'translateX(0px)';
                    content.style.borderRadius = '24px';
                    if (bg) bg.style.transform = 'translateX(95px)';
                }

                startX = 0;
                currentX = 0;
                isSwiping = false;
                moved = false;
            };

            content.addEventListener('touchstart', (e) => handleStart(e.touches[0].clientX), { passive: true });
            content.addEventListener('touchmove', (e) => handleMove(e.touches[0].clientX), { passive: true });
            content.addEventListener('touchend', handleEnd, { passive: true });

            content.addEventListener('mousedown', (e) => handleStart(e.clientX));
            window.addEventListener('mousemove', (e) => { if (isSwiping) handleMove(e.clientX); });
            window.addEventListener('mouseup', () => { if (isSwiping) handleEnd(); });
        });
    }

    // Render immediately on view load
    window.fetchSavedTrips();

})();
</script>


