<?php
$pageTitle = 'My Saved Trips';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<!-- Saved Trips Container -->
<div class="saved-trips-page-container has-header has-bottom-nav animate-slide-up" style="padding: 20px;">
    <h2 style="margin:0 0 16px 0;">My Saved Trips</h2>
    <div id="saved-trips-list">
        <!-- Fetched saved trips will be injected here -->
        <p style="text-align:center; color:#999; margin-top: 20px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading saved trips...
        </p>
    </div>
</div>

<!-- Check-in Verification Modal (GPS only) -->
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:28px 24px; width:90%; max-width:380px; box-shadow:0 20px 40px rgba(0,0,0,0.3); text-align:center;">
        <div style="font-size:48px; margin-bottom:12px;">📍</div>
        <h3 style="margin:0 0 8px;">Claim Your Reward</h3>
        <p style="font-size:14px; color:#8E8E93; margin-bottom:24px; line-height:1.5;">Your GPS location will be verified. Make sure you are physically at this spot to earn <strong>+50 XP</strong>.</p>

        <input type="hidden" id="checkin-item-id">

        <button class="btn-primary" id="btn-verify-gps" style="width:100%; padding:16px; margin-bottom:12px; font-size:15px;" onclick="verifyGpsCheckIn()">
            <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> I'm Here — Claim Reward
        </button>

        <button style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.15); background:transparent; color:rgba(255,255,255,0.6); font-size:13px; font-weight:600; cursor:pointer;" onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<script>
(function() {
    let backendUrl = 'http://localhost:8000';

    window.fetchSavedTrips = async function() {
        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('intan_elyu_token')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                renderSavedTrips(data.itineraries || []);
            }
        } catch (error) {
            console.error("Error fetching saved trips:", error);
            const list = document.getElementById('saved-trips-list');
            if(list) list.innerHTML = '<p style="text-align:center; color:#999; margin-top: 20px;">Failed to load saved trips.</p>';
        }
    };

    function renderSavedTrips(itineraries) {
        const list = document.getElementById('saved-trips-list');
        
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
                <div class="timeline" style="margin-top:0;">`;
                
            let unvisitedCount = 0;
            if (trip.items && trip.items.length) {
                trip.items.forEach((item, index) => {
                    const dest = item.destination;
                    const isVisited = item.is_visited;
                    if (!isVisited) unvisitedCount++;
                    
                    html += `
                    <div class="timeline-item ${isVisited ? 'completed' : ''}">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content" style="padding:12px;">
                            <h4 style="margin:0 0 4px 0; font-size:15px;">${dest ? dest.name : 'Unknown Destination'}</h4>
                            ${isVisited ? 
                                '<span style="color:var(--primary-color); font-size:12px; font-weight:600;"><i class="fa-solid fa-check-circle"></i> Visited</span>' : 
                                item.proof_image ? 
                                '<span style="color:#FF9500; font-size:12px; font-weight:600;"><i class="fa-solid fa-clock"></i> Pending Approval</span>' :
                                `<button class="btn-primary" style="padding: 6px 12px; font-size:11px; width:auto; border-radius:100px; margin-top:8px;" onclick="window.openCheckinModal('${item.id}')">
                                    <i class="fa-solid fa-location-arrow"></i> Check In (Earn XP)
                                 </button>`
                            }
                        </div>
                    </div>`;
                });
            } else {
                html += `<p style="font-size:13px; color:#999;">No destinations in this trip.</p>`;
            }
                
            html += `</div>`; // Close timeline

            // Action buttons
            html += `<div style="display:flex; gap:8px; margin-top:16px;">`;
            
            if (unvisitedCount === 0 && trip.items && trip.items.length > 0) {
                html += `
                <button class="btn-primary" style="flex:1; background:#34C759; border:none; padding:12px;" onclick="window.markTripCompleted('${trip.id}')">
                    <i class="fa-solid fa-flag-checkered" style="margin-right:8px;"></i> Mark Trip as Completed
                </button>`;
            } else {
                html += `
                <button class="btn-primary" style="flex:1; background:transparent; border:1px solid var(--primary-color); color:var(--primary-color); padding:12px;" onclick="navigateTo('map')">
                    <i class="fa-solid fa-plus" style="margin-right:8px;"></i> Add more destinations
                </button>`;
            }
            
            html += `</div></div>`; // Close card
        });

        list.innerHTML = html;
    }

    window.openCheckinModal = function(itemId) {
        document.getElementById('checkin-item-id').value = itemId;
        document.getElementById('checkin-modal').style.display = 'flex';
    };

    window.closeCheckinModal = function() {
        document.getElementById('checkin-modal').style.display = 'none';
        document.getElementById('checkin-item-id').value = '';
        const btn = document.getElementById('btn-verify-gps');
        if (btn) { btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> I\'m Here — Claim Reward'; btn.disabled = false; }
    };

    window.verifyGpsCheckIn = function() {
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

                try {
                    const response = await fetch(backendUrl + '/api/tourist/itineraries/items/' + itemId + '/visit', {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'ngrok-skip-browser-warning': 'true',
                            'Authorization': 'Bearer ' + (localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('intan_elyu_token'))
                        },
                        body: JSON.stringify({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        if (typeof showToast === 'function') showToast(result.message || 'Checked in! 🌟');
                        closeCheckinModal();
                        window.fetchSavedTrips();
                    } else {
                        if (typeof showToast === 'function') showToast(result.message || 'Check-in failed.');
                        btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> I\'m Here — Claim Reward';
                        btn.disabled = false;
                    }
                } catch (error) {
                    console.error('Check-in error:', error);
                    if (typeof showToast === 'function') showToast('Network error. Please try again.');
                    btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> I\'m Here — Claim Reward';
                    btn.disabled = false;
                }
            },
            (error) => {
                console.error('GPS error:', error);
                if (typeof showToast === 'function') showToast('Could not get your location. Please enable GPS.');
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> I\'m Here — Claim Reward';
                btn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000 }
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
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                }
            });
            
            const data = await response.json();
            if (response.ok) {
                if (typeof showToast === 'function') showToast(data.message || "Trip completed!");
                window.fetchSavedTrips(); // Refresh the list
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
