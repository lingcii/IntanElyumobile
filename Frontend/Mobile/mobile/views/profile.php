<!-- Profile View -->
<?php
$pageTitle = 'My Profile';
$activeTab = 'profile';
?>




<div class="profile-container has-bottom-nav animate-slide-up" style="padding-top: env(safe-area-inset-top, 40px);">
    
    <div class="profile-header stagger-1">
        <div class="profile-avatar-container">
            <img src="https://i.pravatar.cc/150?img=11" alt="Profile" class="profile-avatar" id="profile-img">
        </div>
        <h2 class="profile-name" id="profile-name">Loading...</h2>
        <p class="profile-email" id="profile-email">loading@example.com</p>
    </div>
    
    <div class="stats-container stagger-2">
        <div class="stat-card">
            <div class="stat-value" id="stat-xp">0</div>
            <div class="stat-label">Total XP</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-places">0</div>
            <div class="stat-label">Places Visited</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-rank">—</div>
            <div class="stat-label">Rank</div>
        </div>
    </div>
    
    <h3 class="stagger-3" style="font-size: 18px; margin-bottom: 12px; margin-left: 8px;">Trip History</h3>
    <div id="trip-history-list" class="stagger-3" style="margin-bottom: 24px;">
        <div style="text-align:center; padding:20px; color:#8E8E93; font-size:14px; background:var(--glass-bg); border-radius:16px;">Loading history...</div>
    </div>
    

    
    <h3 class="stagger-3" style="font-size: 18px; margin-bottom: 12px; margin-left: 8px;">Account</h3>
    
    <div class="settings-group stagger-3">
        <a href="#" class="settings-item" onclick="navigateTo('settings'); return false;">
            <div class="settings-icon" style="background: #8E8E93;"><i class="fa-solid fa-gear"></i></div>
            <div class="settings-text">Settings</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="showToast('Edit Profile coming soon!')">
            <div class="settings-icon" style="background: #007AFF;"><i class="fa-solid fa-user-pen"></i></div>
            <div class="settings-text">Edit Profile</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="showToast('Help center coming soon!')">
            <div class="settings-icon" style="background: #34C759;"><i class="fa-solid fa-circle-question"></i></div>
            <div class="settings-text">Help & Support</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="handleLogout(event)">
            <div class="settings-icon" style="background: #FF3B30;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="settings-text" style="color: #FF3B30;">Log Out</div>
        </a>
    </div>

    
</div>

<!-- Include Bottom Navigation Component -->


<script>
    const backendUrl = "https://boc-cornell-rolled-delicious.trycloudflare.com";

    async function fetchProfileData() {
        try {
            const response = await fetch(backendUrl + '/api/tourist/profile', {
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('intan_elyu_token')
                }
            });
            const data = await response.json();
            
            if (response.ok) {
                const u = data.user || {};
                document.getElementById('stat-xp').textContent = u.xp || 0;
                document.getElementById('stat-places').textContent = data.places_visited || 0;
                if (data.my_rank) document.getElementById('stat-rank').textContent = '#' + data.my_rank;

                document.getElementById('profile-name').textContent = u.name || 'Explorer';
                document.getElementById('profile-email').textContent = u.email || '';

                // Avatar
                if (document.getElementById('profile-img')) {
                    document.getElementById('profile-img').src = u.avatar ||
                        `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                }

                const historyList = document.getElementById('trip-history-list');
                if (!data.completed_trips || data.completed_trips.length === 0) {
                    historyList.innerHTML = '<div style="text-align:center; padding:20px; color:#8E8E93; font-size:14px; background:var(--glass-bg); border-radius:16px;">No completed trips yet. Start exploring!</div>';
                    return;
                }

                let html = '';
                data.completed_trips.forEach(trip => {
                    const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date';
                    html += `
                    <div style="background:var(--glass-bg); backdrop-filter:blur(16px); border:1px solid var(--glass-border); border-radius:16px; padding:16px; margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <strong style="color:var(--text-dark); font-size:16px;">${trip.title}</strong>
                            <span style="color:#34C759; font-weight:700; font-size:14px;">Completed</span>
                        </div>
                        <div style="font-size:13px; color:#8E8E93; margin-bottom:12px;">
                            <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> ${date}
                            <span style="margin:0 8px;">&bull;</span>
                            <i class="fa-solid fa-coins" style="margin-right:4px;"></i> ₱${trip.total_cost || 0}
                        </div>
                        <div style="font-size:12px; color:#666;">
                            ${trip.items.length} Destinations Visited
                        </div>
                    </div>`;
                });
                historyList.innerHTML = html;
            }
        } catch (e) {
            console.error(e);
        }
    }

    fetchProfileData();
</script>
