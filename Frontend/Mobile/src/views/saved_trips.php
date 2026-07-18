<?php
$pageTitle = 'My Saved Trips';
$backRoute = 'itinerary';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

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
<div class="saved-trips-page-container has-header animate-slide-up" style="padding-left: 20px; padding-right: 20px; padding-bottom: 20px;">
    <div id="saved-trips-list" style="margin-top: 16px;">
        <!-- Fetched saved trips will be injected here -->
        <p style="text-align:center; color:#999; margin-top: 20px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading saved trips...
        </p>
    </div>
</div>

<!-- Check-in Verification Modal (GPS and Photo Proof) -->
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:28px 24px; width:90%; max-width:380px; box-shadow:0 20px 40px rgba(0,0,0,0.3); text-align:center;">
        <div style="font-size:48px; margin-bottom:12px;">📸</div>
        <h3 style="margin:0 0 8px;">Claim Your Reward</h3>
        <p style="font-size:13px; color:#8E8E93; margin-bottom:20px; line-height:1.5;">Take a selfie or capture a photo at this destination to verify your visit and earn <strong>+50 XP</strong> & <strong>+50 Points</strong>.</p>

        <input type="hidden" id="checkin-item-id">
        
        <!-- File Input for Image Proof -->
        <div style="margin-bottom: 20px; text-align: left;">
            <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.75); margin-bottom:6px; display:block; text-transform:uppercase;">Photo Proof (Required):</label>
            <input type="file" id="checkin-proof-image" accept="image/*" capture="environment" style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:10px; color:#fff; font-size:12px;">
        </div>

        <button class="btn-primary" id="btn-verify-gps" style="width:100%; padding:16px; margin-bottom:12px; font-size:15px;" onclick="verifyGpsCheckIn()">
            <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo
        </button>

        <button style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:transparent; color:rgba(255,255,255,0.6); font-size:13px; font-weight:600; cursor:pointer;" onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<script>
(function() {
    var backendUrl = window.backendUrl || 'https://intanelyu-production.up.railway.app';

    window.fetchSavedTrips = async function(forceRefresh = false) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

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

        let html = '';

        itineraries.forEach(trip => {
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

            html += `
            <div style="background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:16px; padding:20px; margin-bottom:20px;">
                <h3 style="margin:0 0 4px 0;">${trip.title}</h3>
                <p style="font-size:12px; color:#666; margin:0 0 16px 0;">
                    ${trip.trip_date ? 'Date: ' + new Date(trip.trip_date).toLocaleDateString() : 'No date set'} 
                    ${trip.budget ? '&nbsp;&bull;&nbsp; <span style="color:var(--primary-color); font-weight:700;">Budget: ₱' + parseFloat(trip.budget).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</span>' + budgetIndicator : ''}
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
                    
                    html += `
                    <div class="timeline-item ${isVisited ? 'completed' : ''}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content" style="padding:12px; display:flex; flex-direction:column; gap:8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h4 style="margin:0; font-size:15px;">${dest ? dest.name : 'Unknown Destination'}</h4>
                                ${(dest && dest.classification_status) ? `<span style="padding: 2px 6px; border-radius: 6px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')};">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : ''}
                            </div>
                            ${(dest && (dest.accessible_by_private_vehicle === 0 || dest.accessible_by_private_vehicle === false)) ? `<div style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); border-radius:8px; padding:8px; display:flex; gap:6px; align-items:flex-start; margin-top:4px;"><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444; font-size:12px; margin-top:1px;"></i><div><h5 style="margin:0 0 2px 0; font-size:11px; font-weight:700; color:#ef4444; text-transform:uppercase;">Inaccessible by Private Car</h5><p style="margin:0; font-size:10px; color:#999; line-height:1.3;">Prepare to hike or use specialized local transport.</p></div></div>` : ''}

                            ${isVisited ? 
                                '<span style="color:var(--primary-color); font-size:12px; font-weight:600;"><i class="fa-solid fa-check-circle"></i> Visited</span>' : 
                                item.proof_image ? 
                                '<span style="color:#FF9500; font-size:12px; font-weight:600;"><i class="fa-solid fa-clock"></i> Pending Approval</span>' :
                                `<button class="btn-primary" style="padding: 6px 12px; font-size:11px; width:max-content; border-radius:100px;" onclick="window.openCheckinModal('${item.id}')">
                                    <i class="fa-solid fa-location-arrow"></i> Check In (Earn XP)
                                 </button>`
                            }
                        </div>
                    </div>`;
                });
            } else {
                html += `<p style="font-size:13px; color:#999;">No destinations in this trip.</p>`;
            }
                
            html += `</div></div></div>`; // Close timeline, timeline-inner, and timeline-collapsible

            // Action buttons
            html += `<div style="display:flex; gap:8px; margin-top:16px;">`;

            // View Details button
            html += `
            <button class="btn-primary" style="flex:1; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:12px;" onclick="window.toggleTripDetails('${trip.id}')">
                <i class="fa-solid fa-chevron-down" id="chevron-${trip.id}" style="margin-right:8px; transition:transform 0.3s ease;"></i> View Details
            </button>`;

            // Start / Complete button wrapper
            html += `<div class="start-collapsible" id="start-wrapper-${trip.id}">`;
            if (unvisitedCount === 0 && trip.items && trip.items.length > 0) {
                html += `
                <button class="btn-primary" style="width:100%; white-space:nowrap; background:#34C759; border:none; padding:12px;" onclick="window.markTripCompleted('${trip.id}')">
                    <i class="fa-solid fa-flag-checkered" style="margin-right:4px;"></i> Complete
                </button>`;
            } else {
                html += `
                <button class="btn-primary" style="width:100%; white-space:nowrap; background:#007AFF; border:none; padding:12px;" onclick="window.startTrip('${trip.id}')">
                    <i class="fa-solid fa-play" style="margin-right:4px;"></i> Start
                </button>`;
            }
            html += `</div>`; // Close start-collapsible
            
            html += `</div></div>`; // Close card
        });

        list.innerHTML = html;
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

    window.closeCheckinModal = function() {
        document.getElementById('checkin-modal').style.display = 'none';
        document.getElementById('checkin-item-id').value = '';
        const imgInput = document.getElementById('checkin-proof-image');
        if (imgInput) imgInput.value = '';
        const btn = document.getElementById('btn-verify-gps');
        if (btn) { btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo'; btn.disabled = false; }
    };

    window.verifyGpsCheckIn = function() {
        const imageFile = document.getElementById('checkin-proof-image').files[0];
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

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Verifying...';

                const itemId = document.getElementById('checkin-item-id').value;
                if (!itemId) return;

                const formData = new FormData();
                formData.append('lat', position.coords.latitude);
                formData.append('lng', position.coords.longitude);
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
                        if (typeof showToast === 'function') showToast(result.message || 'Checked in! 🌟');
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
            },
            (error) => {
                console.error('GPS error:', error);
                if (typeof showToast === 'function') showToast('Could not get your location. Please enable GPS.');
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify Location & Photo';
                btn.disabled = false;
            },
            { enableHighAccuracy: false, timeout: 10000 }
        );
    };

    window.markTripCompleted = async function(id) {
        if (!confirm("Are you sure you want to mark this trip as completed? It will be moved to your History.")) return;
        
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
                window.fetchSavedTrips(true); // Refresh the list
            } else {
                if (typeof showToast === 'function') showToast(data.message || "Failed to complete trip.");
            }
        } catch (error) {
            console.error("Error completing trip:", error);
            if (typeof showToast === 'function') showToast("Network error.");
        }
    };

    // Render immediately on view load
    window.fetchSavedTrips();

})();
</script>


