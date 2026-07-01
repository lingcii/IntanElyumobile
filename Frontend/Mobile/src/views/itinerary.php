<!-- Itinerary View -->
<?php
$pageTitle = 'My Itinerary';
$activeTab = 'itinerary';
?>



<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="itinerary-container has-header has-bottom-nav animate-slide-up">
    
    <div style="display: flex; justify-content: space-between; align-items: center;" class="stagger-1">
        <h2 style="margin:0;">Draft Plan</h2>
        <span style="background: #E5E5EA; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight:600;"><span id="itinerary-count">0</span> Places</span>
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
    <div id="itinerary-empty-state">
        <i class="fa-solid fa-route" style="font-size: 54px; margin-bottom: 16px; color:var(--primary-color);"></i>
        <h3 style="margin-bottom:8px;">No plans yet</h3>
        <p style="font-size:14px;">Go to the Map and tap "Add to Itinerary" on a place to start building your trip!</p>
        <button class="btn-primary" style="margin-top: 20px; width:auto; padding:12px 24px;" onclick="navigateTo('map')">Open Map</button>
    </div>
    
    <!-- Saved Trips Container -->
    <div id="saved-trips-container" style="margin-top: 40px; display: none;" class="stagger-3">
        <h2 style="margin:0 0 16px 0;">My Saved Trips</h2>
        <div id="saved-trips-list"></div>
    </div>
    
</div>

<!-- Save Trip Modal -->
<div id="save-trip-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:90%; max-width:400px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">Save Your Trip</h3>
        <p style="font-size:14px; color:#666; margin-bottom:20px;">Give your awesome adventure a name so you can pull it up later!</p>
        
        <input type="text" id="trip-title" placeholder="e.g. La Union Weekend" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid #ddd; margin-bottom:16px; font-family:inherit; font-size:16px;">
        
        <label style="font-size:12px; color:#666; margin-bottom:4px; display:block;">Trip Date (Optional)</label>
        <input type="date" id="trip-date" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid #ddd; margin-bottom:16px; font-family:inherit; font-size:16px;">

        <label style="font-size:12px; color:#666; margin-bottom:4px; display:block;">Mode of Transport</label>
        <select id="trip-transport" style="width:100%; padding:12px 16px; border-radius:12px; border:1px solid #ddd; margin-bottom:16px; font-family:inherit; font-size:16px; background:white; appearance:none;" onchange="window.calculateModalBudget()">
            <option value="">Select Primary Transport</option>
            <option value="own_car">Own Car</option>
            <option value="taxi">Taxi / Ride-hailing</option>
            <option value="private_bus">Private Bus</option>
            <option value="mini_bus">Mini Bus</option>
            <option value="lutrampco">LUTRAMPCO</option>
            <option value="jeepney">Jeepney</option>
        </select>

        <label style="font-size:12px; color:#666; margin-bottom:4px; display:flex; justify-content:space-between;">
            <span>Overall Budget (Optional)</span>
            <span id="save-budget-indicator"></span>
        </label>
        <div style="position:relative; margin-bottom:12px;">
            <span style="position:absolute; left:16px; top:14px; color:#666; font-weight:600;">₱</span>
            <input type="number" id="trip-budget" placeholder="0.00" oninput="window.calculateModalBudget()" style="width:100%; padding:12px 16px 12px 32px; border-radius:12px; border:1px solid #ddd; font-family:inherit; font-size:16px;">
        </div>
        
        <div id="save-budget-details" style="display:none; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); padding:12px; border-radius:12px; margin-bottom:24px; font-size:13px; color:#666; text-align:left;">
            Estimated Trip Cost: <strong id="save-estimated-cost" style="color:var(--text-dark); font-size:15px; float:right;">₱0.00</strong>
        </div>
        
        <div style="display:flex; gap:12px;">
            <button class="btn-primary" style="flex:1; background:transparent; border:1px solid #E5E5EA; color:#333;" onclick="closeSaveModal()">Cancel</button>
            <button class="btn-primary" style="flex:1;" onclick="submitItinerary()" id="btn-submit-trip">Save Trip</button>
        </div>
    </div>
</div>

<!-- Check-in Verification Modal -->
<div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:24px; width:90%; max-width:400px; box-shadow:0 20px 40px rgba(0,0,0,0.2); text-align:center;">
        <h3 style="margin-top:0;">Verify Check-In</h3>
        <p style="font-size:14px; color:#666; margin-bottom:20px;">Prove you are here to earn your XP! Select a verification method.</p>
        
        <input type="hidden" id="checkin-item-id">

        <button class="btn-primary" style="width:100%; margin-bottom:12px; padding:16px;" onclick="verifyGpsCheckIn()" id="btn-verify-gps">
            <i class="fa-solid fa-location-crosshairs" style="margin-right:8px;"></i> Verify via GPS
        </button>
        
        <div style="position:relative;">
            <button class="btn-primary" style="width:100%; background:transparent; border:2px dashed var(--primary-color); color:var(--primary-color); padding:16px; margin-bottom:12px;" onclick="document.getElementById('proof-photo').click()" id="btn-verify-photo">
                <i class="fa-solid fa-camera" style="margin-right:8px;"></i> Upload Photo Proof
            </button>
            <input type="file" id="proof-photo" accept="image/*" style="display:none;" onchange="verifyPhotoCheckIn(this)">
        </div>
        
        <button class="btn-primary" style="width:100%; background:#FF9500; border:none; padding:16px; margin-bottom:24px;" onclick="verifyTestCheckIn()" id="btn-verify-test">
            <i class="fa-solid fa-check-double" style="margin-right:8px;"></i> Mark as Completed (Bypass)
        </button>

        <button class="btn-primary" style="width:100%; background:transparent; border:1px solid #E5E5EA; color:#333;" onclick="closeCheckinModal()">Cancel</button>
    </div>
</div>

<script>
(function() {
    const backendUrl = "https://boc-cornell-rolled-delicious.trycloudflare.com";

    window.renderItinerary = function() {
        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        const timeline = document.getElementById('itinerary-timeline');
        const emptyState = document.getElementById('itinerary-empty-state');
        const fab = document.getElementById('btn-save-itinerary');
        
        document.getElementById('itinerary-count').innerText = draft.length;

        if (draft.length === 0) {
            timeline.innerHTML = '';
            emptyState.style.display = 'block';
            fab.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        fab.style.display = 'flex';
        
        let html = '';
        draft.forEach((place, index) => {
            // Calculate a mock time just for visuals (starting at 9 AM, 1.5 hours per stop)
            const hour = 9 + Math.floor((index * 90) / 60);
            const min = (index * 90) % 60;
            const timeStr = `${hour > 12 ? hour - 12 : hour}:${min === 0 ? '00' : min} ${hour >= 12 ? 'PM' : 'AM'}`;

            html += `
            <div class="timeline-item" style="animation-delay: ${index * 0.1}s">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="time-label">Stop ${index + 1} &bull; Approx ${timeStr}</span>
                    <h3 class="place-name">${place.name}</h3>
                    <div class="place-details">
                        <i class="fa-solid fa-location-dot"></i>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${place.location}</span>
                    </div>
                    <div style="margin-top:16px; display:flex; gap:8px;">
                        <button style="padding: 8px 16px; font-size:12px; width:auto; border-radius: 100px; border:1px solid #E5E5EA; background:transparent; font-weight:600;" onclick="window.removeItineraryItem('${place.id}')">
                            <i class="fa-solid fa-trash" style="margin-right:4px;"></i> Remove
                        </button>
                        <button class="btn-primary" style="padding: 8px 16px; font-size:12px; width:auto; flex:1;" onclick="window.routeToPlace('${place.id}')">
                            <i class="fa-solid fa-diamond-turn-right" style="margin-right:4px;"></i> Directions
                        </button>
                    </div>
                </div>
            </div>`;
        });
        
        timeline.innerHTML = html;
    };

    window.removeItineraryItem = function(id) {
        let draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        draft = draft.filter(item => item.id.toString() !== id.toString());
        localStorage.setItem('Intan_Elyu_draft_itinerary', JSON.stringify(draft));
        window.renderItinerary();
    };

    window.routeToPlace = function(id) {
        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        const place = draft.find(item => item.id.toString() === id.toString());
        if (place) {
            // Save the routing target so map.php knows what to do
            localStorage.setItem('Intan_Elyu_pending_route', JSON.stringify(place));
            navigateTo('map');
        }
    };

    window.calculateModalBudget = function() {
        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        if (draft.length === 0) return;

        let estimatedCost = 0;
        draft.forEach(item => {
            estimatedCost += parseFloat(item.entrance_fee) || 50;
            estimatedCost += parseFloat(item.avg_food_cost) || 150;
            estimatedCost += parseFloat(item.avg_transport_cost) || 30;
        });

        const transport = document.getElementById('trip-transport').value;
        if (transport === 'own_car') estimatedCost += 300;
        else if (transport === 'taxi') estimatedCost += 250;
        else if (transport === 'private_bus') estimatedCost += 800;
        else if (transport === 'mini_bus') estimatedCost += 500;
        else if (transport === 'lutrampco') estimatedCost += 50;
        else if (transport === 'jeepney') estimatedCost += 30;

        const budgetInput = document.getElementById('trip-budget').value;
        const budget = parseFloat(budgetInput);

        const detailsDiv = document.getElementById('save-budget-details');
        const costEl = document.getElementById('save-estimated-cost');
        const indicatorEl = document.getElementById('save-budget-indicator');

        detailsDiv.style.display = 'block';
        costEl.textContent = '₱' + estimatedCost.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

        if (!budgetInput || isNaN(budget) || budget <= 0) {
            indicatorEl.innerHTML = '';
            return;
        }

        const percentage = (estimatedCost / budget) * 100;
        let color = '#34C759'; // Green
        let statusText = 'Safe';
        
        if (percentage >= 100) {
            color = '#FF3B30'; // Red
            statusText = 'Over Budget!';
        } else if (percentage >= 80) {
            color = '#FF9500'; // Orange
            statusText = 'Near Limit';
        }
        
        let displayPct = Math.round(percentage);
        if (displayPct > 100) displayPct = 100;
        
        indicatorEl.innerHTML = `<span style="color:${color}; font-weight:700;"><i class="fa-solid fa-circle" style="font-size:8px; vertical-align:middle;"></i> ${displayPct}% (${statusText})</span>`;
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
        document.getElementById('save-budget-indicator').innerHTML = '';
        document.getElementById('save-budget-details').style.display = 'none';
    };

    window.submitItinerary = async function() {
        const title = document.getElementById('trip-title').value.trim();
        const date = document.getElementById('trip-date').value;
        const budgetStr = document.getElementById('trip-budget').value;
        const budget = budgetStr ? parseFloat(budgetStr) : null;
        if (!title) return showToast("Please enter a trip name");

        const draft = JSON.parse(localStorage.getItem('Intan_Elyu_draft_itinerary') || '[]');
        if (draft.length === 0) return showToast("Your itinerary is empty!");

        const btn = document.getElementById('btn-submit-trip');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const items = draft.map(place => ({
            destination_id: place.id,
            transport_cost: parseFloat(place.avg_transport_cost || 0),
            activity_cost: parseFloat(place.entrance_fee || 0),
            food_cost: parseFloat(place.avg_food_cost || 0),
            accommodation_cost: 0
        }));

        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                },
                body: JSON.stringify({ title: title, trip_date: date, budget: budget, items: items })
            });

            const data = await response.json();

            if (response.ok) {
                showToast("Trip saved successfully!");
                localStorage.removeItem('Intan_Elyu_draft_itinerary');
                closeSaveModal();
                window.renderItinerary();
                window.fetchSavedTrips();
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

    window.fetchSavedTrips = async function() {
        try {
            const response = await fetch(backendUrl + '/api/tourist/itineraries', {
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                renderSavedTrips(data.itineraries || []);
            }
        } catch (error) {
            console.error("Error fetching saved trips:", error);
        }
    };

    function renderSavedTrips(itineraries) {
        const container = document.getElementById('saved-trips-container');
        const list = document.getElementById('saved-trips-list');
        
        if (!itineraries || itineraries.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
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
        document.getElementById('proof-photo').value = '';
    };

    window.verifyGpsCheckIn = function() {
        if (!navigator.geolocation) {
            showToast("Geolocation is not supported by your browser.");
            return;
        }

        const btn = document.getElementById('btn-verify-gps');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(async (position) => {
            await submitVerification({
                verification_method: 'gps',
                lat: position.coords.latitude,
                lng: position.coords.longitude
            });
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, (error) => {
            console.error(error);
            showToast("Failed to get location. Make sure GPS is enabled.");
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, { enableHighAccuracy: true });
    };

    window.verifyPhotoCheckIn = async function(input) {
        if (!input.files || input.files.length === 0) return;
        
        const btn = document.getElementById('btn-verify-photo');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('verification_method', 'photo');
        formData.append('proof_photo', input.files[0]);

        await submitVerification(formData, true);

        btn.innerHTML = originalText;
        btn.disabled = false;
        input.value = ''; // Reset
    };

    window.verifyTestCheckIn = async function() {
        if (!confirm('Are you sure you want to bypass GPS check-in? This is for testing only.')) return;

        const btn = document.getElementById('btn-verify-test');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Bypassing...';
        btn.disabled = true;

        await submitVerification({
            verification_method: 'test'
        });

        btn.innerHTML = originalText;
        btn.disabled = false;
    };

    async function submitVerification(data, isFormData = false) {
        const itemId = document.getElementById('checkin-item-id').value;
        if (!itemId) return;

        try {
            const options = {
                method: 'POST', // Use POST with _method=PATCH for multipart/form-data support in Laravel
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('Intan_Elyu_Token')
                }
            };

            if (isFormData) {
                data.append('_method', 'PATCH');
                options.body = data;
            } else {
                data._method = 'PATCH';
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }

            const response = await fetch(backendUrl + '/api/tourist/itineraries/items/' + itemId + '/visit', options);
            const result = await response.json();

            if (response.ok) {
                showToast(result.message || "Checked In! 🌟");
                closeCheckinModal();
                window.fetchSavedTrips(); // Refresh the list
            } else {
                showToast(result.message || "Verification failed.");
            }
        } catch (error) {
            console.error("Verification Error:", error);
            showToast("Failed to check in. Check connection.");
        }
    }

    // Render immediately on view load
    window.renderItinerary();
    window.fetchSavedTrips();
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
                showToast(data.message || "Trip completed!");
                window.fetchSavedTrips(); // Refresh the list
            } else {
                showToast(data.message || "Failed to complete trip.");
            }
        } catch (error) {
            console.error("Error completing trip:", error);
            showToast("Network error.");
        }
    };

})();
</script>

