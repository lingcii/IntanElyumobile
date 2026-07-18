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
        position: relative;
    }
    
    .header-icon .bell-dot {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(22, 42, 102, 0.85);
        display: none;
    }
    
    .header-icon .bell-dot.show {
        display: block;
    }
    
    .bell-ring {
        animation: bell-shake 0.4s ease;
    }
    
    @keyframes bell-shake {
        0%, 100% { transform: rotate(0deg); }
        20% { transform: rotate(15deg); }
        40% { transform: rotate(-15deg); }
        60% { transform: rotate(10deg); }
        80% { transform: rotate(-10deg); }
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
        <i class="fa-regular fa-bell" id="bell-icon"></i>
        <div class="bell-dot" id="bell-dot"></div>
    </div>
</div>

<!-- Notifications Dropdown -->
<div id="notifications-dropdown" style="position: fixed; top: max(env(safe-area-inset-top, 0px), 60px); right: 12px; left: 12px; max-width: 360px; margin: 0 auto; background: #0f172a; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; z-index: 101; box-shadow: 0 10px 40px rgba(0,0,0,0.5); padding: 16px; max-height: 70vh; overflow-y: auto; opacity: 0; pointer-events: none; transform: translateY(-8px); transition: opacity 0.25s ease, transform 0.25s ease;">
    <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 800; color: #f8fafc; letter-spacing: -0.3px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="fa-regular fa-bell" style="margin-right: 8px; color: #38bdf8;"></i>Notifications</span>
        <i class="fa-solid fa-xmark" style="font-size: 16px; color: rgba(148,163,184,0.6); cursor: pointer; padding: 4px; transition: color 0.2s;" onclick="toggleNotifications()"></i>
    </h3>
    <div id="notifications-list">
        <div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;"><i class="fa-regular fa-bell-slash" style="margin-right: 6px;"></i>No new notifications.</div>
    </div>
</div>

<!-- Sidebar Menu -->
<div id="sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;" onclick="toggleSidebar()"></div>
<div id="sidebar-menu" style="position: fixed; top: 0; left: -280px; width: 280px; bottom: 0; background: #0f172a; z-index: 1001; transition: left 0.3s ease; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.5);">
    <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-top: max(env(safe-area-inset-top), 40px);">
        <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #fff;">Menu</h2>
    </div>
    <div style="flex: 1; padding: 20px; display: flex; flex-direction: column; gap: 30px;">
        <!-- Merch page hidden until after pre-defense -->
        <a href="#" onclick="toggleSidebar(); navigateTo('terms'); return false;" style="color: #e2e8f0; text-decoration: none; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-shield-halved" style="color: #38bdf8; width: 20px; text-align: center;"></i> Terms & Privacy
        </a>
        <a href="#" onclick="toggleSidebar(); navigateTo('about'); return false;" style="color: #e2e8f0; text-decoration: none; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
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
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notifications-dropdown');
        const bell = document.querySelector('.header-icon .fa-bell');
        if (dropdown && dropdown.style.opacity === '1' && bell && !e.target.closest('.header-icon') && !e.target.closest('#notifications-dropdown')) {
            dropdown.style.opacity = '0';
            dropdown.style.pointerEvents = 'none';
            dropdown.style.transform = 'translateY(-8px)';
        }
    });

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
        const isOpen = dropdown.style.opacity === '1';
        if (isOpen) {
            dropdown.style.opacity = '0';
            dropdown.style.pointerEvents = 'none';
            dropdown.style.transform = 'translateY(-8px)';
        } else {
            dropdown.style.opacity = '1';
            dropdown.style.pointerEvents = 'all';
            dropdown.style.transform = 'translateY(0)';
            const bell = document.getElementById('bell-icon');
            if (bell) { bell.classList.remove('bell-ring'); void bell.offsetWidth; bell.classList.add('bell-ring'); }
            const dot = document.getElementById('bell-dot');
            if (dot) dot.classList.remove('show');
            fetchNotifications();
        }
    }

    async function fetchNotifications() {
        const list = document.getElementById('notifications-list');
        if (!list) return;
        list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;"><i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i>Loading...</div>';

        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        try {
            const backendUrl = window.backendUrl || 'https://intanelyu-production.up.railway.app';
            const res = await fetch(backendUrl + '/api/tourist/notifications', {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            if (!res.ok) return;
            const data = await res.json();
            renderNotifications(data.notifications || []);
        } catch (e) {
            list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;">Failed to load notifications.</div>';
        }
    }

    function renderNotifications(notifications) {
        const list = document.getElementById('notifications-list');
        const dot = document.getElementById('bell-dot');
        if (!list) return;

        if (notifications.length > 0) {
            let html = '';
            const unread = notifications.filter(n => !n.is_read);
            notifications.forEach(item => {
                let icon = 'fa-bell';
                let color = '#38bdf8';

                if (item.type === 'new_spot' || item.type === 'spot_added') {
                    icon = 'fa-map-pin';
                    color = '#34c759';
                } else if (item.type === 'favorite_update' || item.type === 'spot_updated') {
                    icon = 'fa-pen-to-square';
                    color = '#f59e0b';
                } else if (item.type === 'itinerary_reminder') {
                    icon = 'fa-calendar-day';
                    color = '#8b5cf6';
                } else if (item.type === 'spot_maintenance') {
                    icon = 'fa-triangle-exclamation';
                    color = '#ef4444';
                }

                const isUnread = !item.is_read;
                html += `
                    <div style="display: flex; gap: 12px; margin-bottom: 10px; padding: 12px; background: ${isUnread ? 'rgba(56,189,248,0.06)' : 'rgba(255,255,255,0.02)'}; border: 1px solid ${isUnread ? 'rgba(56,189,248,0.12)' : 'rgba(255,255,255,0.04)'}; border-radius: 12px; align-items: flex-start; cursor: ${isUnread ? 'pointer' : 'default'}; transition: background 0.2s;" onclick="${isUnread ? `markNotifRead(${item.id}, this)` : ''}">
                        <div style="width: 34px; height: 34px; border-radius: 50%; background: ${color}15; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid ${color}30;">
                            <i class="fa-solid ${icon}" style="color: ${color}; font-size: 14px;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #e2e8f0; line-height: 1.4; font-weight: ${isUnread ? '600' : '400'};">${item.message || item.title}</p>
                            <span style="font-size: 11px; color: rgba(148,163,184,0.5); font-weight: 500;">${new Date(item.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                        ${isUnread ? '<i class="fa-solid fa-circle" style="font-size: 8px; color: #38bdf8; margin-top: 6px; flex-shrink: 0;"></i>' : ''}
                    </div>
                `;
            });
            if (unread.length > 0) {
                html += `<div style="text-align: center; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.06);">
                    <button onclick="markAllNotifRead()" style="background: none; border: none; color: #38bdf8; font-size: 12px; font-weight: 600; cursor: pointer; padding: 6px 12px;">Mark all as read</button>
                </div>`;
            }
            list.innerHTML = html;
            if (unread.length > 0 && dot) dot.classList.add('show');
        } else {
            list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;"><i class="fa-regular fa-bell-slash" style="margin-right: 6px;"></i>No new notifications.</div>';
            if (dot) dot.classList.remove('show');
        }
    }

    async function markNotifRead(id, el) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;
        try {
            const backendUrl = window.backendUrl || 'https://intanelyu-production.up.railway.app';
            await fetch(backendUrl + '/api/tourist/notifications/' + id + '/read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            if (el) {
                el.style.opacity = '0.5';
                el.onclick = null;
                const dot = el.querySelector('.fa-circle');
                if (dot) dot.remove();
            }
            const dot = document.getElementById('bell-dot');
            const remaining = document.querySelectorAll('#notifications-list .fa-circle');
            if (remaining.length === 0 && dot) dot.classList.remove('show');
        } catch (e) {}
    }

    async function markAllNotifRead() {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;
        try {
            const backendUrl = window.backendUrl || 'https://intanelyu-production.up.railway.app';
            await fetch(backendUrl + '/api/tourist/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const items = document.querySelectorAll('#notifications-list > div[style*="cursor: pointer"]');
            items.forEach(el => {
                el.style.opacity = '0.5';
                el.onclick = null;
                const dot = el.querySelector('.fa-circle');
                if (dot) dot.remove();
            });
            const dot = document.getElementById('bell-dot');
            if (dot) dot.classList.remove('show');
        } catch (e) {}
    }

    window.updateUnreadBadge = function(count) {
        const dot = document.getElementById('bell-dot');
        if (dot) {
            if (count > 0) dot.classList.add('show');
            else dot.classList.remove('show');
        }
    };
</script>
