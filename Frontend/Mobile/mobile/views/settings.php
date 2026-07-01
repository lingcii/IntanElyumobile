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
    document.addEventListener('DOMContentLoaded', () => {
        const pushEnabled = localStorage.getItem('Intan_Elyu_push_enabled') === 'true';
        const locEnabled = localStorage.getItem('Intan_Elyu_loc_enabled') === 'true';
        document.getElementById('push-notif-toggle').checked = pushEnabled;
        document.getElementById('location-service-toggle').checked = locEnabled;
    });
</script>
