<!-- Top App Header Component -->
<style>
    .mobile-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--glass-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px; /* 10px top/bottom, 20px sides */
        z-index: 100;
        /* Ensure Android gets safe padding since safe-area-inset-top is sometimes 0 on Android WebViews */
        padding-top: max(env(safe-area-inset-top), 40px);
    }
    
    .header-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }
    
    .header-icon {
        color: var(--text-dark);
        font-size: 20px;
        cursor: pointer;
        padding: 8px;
    }
    
    /* Ensure content below header has padding */
    .has-header {
        padding-top: calc(60px + max(env(safe-area-inset-top), 40px));
    }
</style>

<div class="mobile-header">
    <div class="header-icon" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </div>
    <h1 class="header-title"><?php echo isset($pageTitle) ? $pageTitle : 'Intan Elyu'; ?></h1>
    <div class="header-icon" onclick="toggleNotifications()">
        <i class="fa-regular fa-bell"></i>
    </div>
</div>

<!-- Notifications Dropdown -->
<div id="notifications-dropdown" style="display: none; position: fixed; top: max(env(safe-area-inset-top, 0px), 60px); right: 20px; width: 300px; background: var(--glass-bg); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid var(--glass-border); border-radius: 12px; z-index: 101; box-shadow: 0 10px 25px rgba(0,0,0,0.5); padding: 15px; max-height: 400px; overflow-y: auto;">
    <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #ffffff; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
        Notifications
        <i class="fa-solid fa-xmark" style="font-size: 14px; color: #94a3b8; cursor: pointer;" onclick="toggleNotifications()"></i>
    </h3>
    <div id="notifications-list">
        <div style="color: #94a3b8; font-size: 13px; text-align: center; padding: 15px 0;">No new notifications.</div>
    </div>
</div>

<script>
    function toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }

    function updateNotificationsDropdown(announcements) {
        const list = document.getElementById('notifications-list');
        if (!list) return;
        
        if (announcements && announcements.length > 0) {
            let html = '';
            announcements.forEach(item => {
                let icon = 'fa-bell';
                let color = '#38bdf8'; // Default blue
                
                if (item.type === 'low_investment' || item.type === 'missing_data') {
                    icon = 'fa-circle-exclamation';
                    color = '#f59e0b'; // Amber
                } else if (item.type === 'delayed_program') {
                    icon = 'fa-clock';
                    color = '#ef4444'; // Red
                }

                html += `
                    <div style="display: flex; gap: 12px; margin-bottom: 12px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 10px; align-items: flex-start;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: ${color}20; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid ${icon}" style="color: ${color}; font-size: 14px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <p style="margin: 0 0 6px 0; font-size: 13px; color: #e2e8f0; line-height: 1.4;">${item.message}</p>
                            <span style="font-size: 11px; color: #64748b; font-weight: 500;">${new Date(item.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                    </div>
                `;
            });
            list.innerHTML = html;
            
            // Add a red dot to the bell icon to indicate unread (simulated)
            const bellIcon = document.querySelector('.fa-bell');
            if (bellIcon) {
                bellIcon.parentElement.style.position = 'relative';
                if (!document.getElementById('bell-dot')) {
                    bellIcon.parentElement.innerHTML += '<div id="bell-dot" style="position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; box-shadow: 0 0 0 2px var(--glass-bg);"></div>';
                }
            }
        } else {
            list.innerHTML = '<div style="color: #94a3b8; font-size: 13px; text-align: center; padding: 15px 0;">No new notifications.</div>';
        }
    }
</script>
