<!-- Top App Header Component -->
<style>
    .mobile-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: rgba(22, 42, 102, 0.85); /* Dimmed navy (#162a66) to match background */
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
    <?php if (isset($backRoute) && $backRoute): ?>
        <div class="header-icon" style="width: 36px; padding: 8px 0; text-align: left;" onclick="navigateTo('<?php echo htmlspecialchars($backRoute); ?>')">
            <i class="fa-solid fa-arrow-left"></i>
        </div>
    <?php else: ?>
        <div class="header-icon" style="width: 36px; padding: 8px 0; text-align: left;" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </div>
    <?php endif; ?>
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

<!-- Sidebar Menu -->
<div id="sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;" onclick="toggleSidebar()"></div>
<div id="sidebar-menu" style="position: fixed; top: 0; left: -280px; width: 280px; bottom: 0; background: #0f172a; z-index: 1001; transition: left 0.3s ease; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.5);">
    <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-top: max(env(safe-area-inset-top), 40px);">
        <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #fff;">Menu</h2>
    </div>
    <div style="flex: 1; padding: 20px; display: flex; flex-direction: column; gap: 30px;">
        <a href="#" onclick="toggleSidebar(); navigateTo('merch'); return false;" style="color: #e2e8f0; text-decoration: none; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-shirt" style="color: #38bdf8; width: 20px; text-align: center;"></i> Merch Page
        </a>
        <a href="#" onclick="toggleSidebar(); showToast('Coming soon: Terms & Privacy'); return false;" style="color: #e2e8f0; text-decoration: none; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-shield-halved" style="color: #38bdf8; width: 20px; text-align: center;"></i> Terms & Privacy
        </a>
        <a href="#" onclick="toggleSidebar(); showToast('Coming soon: About Us'); return false;" style="color: #e2e8f0; text-decoration: none; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-info" style="color: #38bdf8; width: 20px; text-align: center;"></i> About Us
        </a>
    </div>
    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <a href="#" onclick="logoutUser(); return false;" style="color: #ef4444; text-decoration: none; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-right-from-bracket" style="width: 20px; text-align: center;"></i> Log Out
        </a>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar-menu');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar && overlay) {
            if (sidebar.style.left === '0px') {
                sidebar.style.left = '-280px';
                overlay.style.display = 'none';
            } else {
                sidebar.style.left = '0px';
                overlay.style.display = 'block';
            }
        }
    }

    function logoutUser() {
        localStorage.removeItem('intan_elyu_token');
        localStorage.removeItem('auth_user');
        window.location.href = '?view=auth';
    }

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
