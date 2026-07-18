<?php
$pageTitle = 'Settings';
?>

<?php include __DIR__ . '/../components/header.php'; ?>




<div class="settings-container has-header has-bottom-nav animate-slide-up">
    
    <h3 class="settings-header stagger-1">Preferences</h3>
    <div class="settings-group stagger-1">
        <div class="settings-item">
            <div class="settings-label">
                <div class="icon-box red"><i class="fa-solid fa-bell"></i></div> 
                Push Notifications
            </div>
            <label class="switch">
                <input type="checkbox" id="push-notif-toggle" onchange="window.togglePushNotifications(this.checked)">
                <span class="slider"></span>
            </label>
        </div>
        <div class="settings-item">
            <div class="settings-label">
                <div class="icon-box green"><i class="fa-solid fa-location-dot"></i></div> 
                Location Services
            </div>
            <label class="switch">
                <input type="checkbox" id="location-service-toggle" onchange="window.toggleLocationServices(this.checked)">
                <span class="slider"></span>
            </label>
        </div>
        <div class="settings-item">
            <div class="settings-label">
                <div class="icon-box blue"><i class="fa-solid fa-moon"></i></div> 
                Dark Theme
            </div>
            <label class="switch">
                <input type="checkbox" id="dark-mode-toggle">
                <span class="slider"></span>
            </label>
        </div>
        <div class="settings-item">
            <div class="settings-label">
                <div class="icon-box red" style="background:#FF9500;"><i class="fa-solid fa-user-shield"></i></div> 
                Anonymous Leaderboard
            </div>
            <label class="switch">
                <input type="checkbox" id="leaderboard-privacy-toggle" onchange="window.toggleLeaderboardPrivacy(this.checked)">
                <span class="slider"></span>
            </label>
        </div>
    </div>
    
    <h3 class="settings-header stagger-2">Account Security</h3>
    <div class="settings-group stagger-2">
        <div class="settings-item clickable" onclick="showToast('Change Password')">
            <div class="settings-label">
                <div class="icon-box"><i class="fa-solid fa-lock"></i></div> 
                Change Password
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(0,0,0,0.2);"></i>
        </div>
        <div class="settings-item clickable" onclick="showToast('Two-Factor Auth')">
            <div class="settings-label">
                <div class="icon-box"><i class="fa-solid fa-shield-check"></i></div> 
                Two-Factor Auth
            </div>
            <i class="fa-solid fa-chevron-right" style="color: rgba(0,0,0,0.2);"></i>
        </div>
    </div>
</div>

<script>
(function() {
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}');
    const pushEnabled = localStorage.getItem('Intan_Elyu_push_enabled') === 'true';
    const locEnabled = localStorage.getItem('Intan_Elyu_loc_enabled') === 'true';
    
    const pushToggle = document.getElementById('push-notif-toggle');
    const locToggle = document.getElementById('location-service-toggle');
    const privToggle = document.getElementById('leaderboard-privacy-toggle');
    
    if (pushToggle) pushToggle.checked = pushEnabled;
    if (locToggle) locToggle.checked = locEnabled;
    if (privToggle) privToggle.checked = user.is_leaderboard_private === 1 || user.is_leaderboard_private === true;

    window.togglePushNotifications = function(checked) {
        localStorage.setItem('Intan_Elyu_push_enabled', checked);
        if (typeof showToast === 'function') showToast(checked ? 'Push notifications enabled' : 'Push notifications disabled');
    };

    window.toggleLocationServices = function(checked) {
        localStorage.setItem('Intan_Elyu_loc_enabled', checked);
        if (typeof showToast === 'function') showToast(checked ? 'Location services enabled' : 'Location services disabled');
    };

    window.toggleLeaderboardPrivacy = async function(checked) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;
        
        try {
            const backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';
            const res = await fetch(backendUrl + '/api/tourist/profile', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ is_leaderboard_private: checked ? 1 : 0 })
            });
            const data = await res.json();
            if (res.ok) {
                if (data.user) {
                    const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    stored.is_leaderboard_private = data.user.is_leaderboard_private;
                    localStorage.setItem('auth_user', JSON.stringify(stored));
                }
                
                // Flush leaderboard UI cache
                const tokenStr = token.substring(0, 10);
                localStorage.removeItem('leaderboard_data_public');
                localStorage.removeItem('leaderboard_data_' + tokenStr);
                
                if (typeof showToast === 'function') showToast(checked ? 'Anonymous mode enabled' : 'Anonymous mode disabled');
            } else {
                throw new Error(data.message || 'Failed to update privacy settings');
            }
        } catch (e) {
            if (typeof showToast === 'function') showToast(e.message);
            if (privToggle) privToggle.checked = !checked;
        }
    };
})();
</script>
