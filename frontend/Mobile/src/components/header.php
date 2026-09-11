<!-- Top App Header Component -->
<style>
    .mobile-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: rgba(30, 58, 138, 0.92) !important;
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: none !important;
        outline: none !important;
        border-bottom: none !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 18px;
        z-index: 9000 !important;
        /* Ensure Android gets safe padding since safe-area-inset-top is sometimes 0 on Android WebViews */
        padding-top: max(env(safe-area-inset-top), 40px);
        box-shadow: none;
    }
    
    .header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        letter-spacing: -0.3px;
        text-align: center;
        flex: 1;
    }
    
    .header-icon {
        color: #1e3a8a !important;
        font-size: 16px;
        cursor: pointer;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border: none !important;
        outline: none !important;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
        transition: transform 0.15s ease, background 0.15s ease;
    }
    
    .header-icon i {
        color: #1e3a8a !important;
    }
    
    .header-icon:active {
        transform: scale(0.92);
        background: #f1f5f9 !important;
    }
    
    .header-icon .bell-dot {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 9px;
        height: 9px;
        background: #0284c7;
        border-radius: 50%;
        box-shadow: 0 0 0 2px #ffffff;
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

    /* Hide scrollbars for Notifications Dropdown and List */
    #notifications-dropdown,
    #notifications-list {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    #notifications-dropdown::-webkit-scrollbar,
    #notifications-list::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>

<div class="mobile-header">
    <?php if (isset($backRoute) && $backRoute): ?>
        <div class="header-icon" onclick="navigateTo('<?php echo htmlspecialchars($backRoute); ?>')" title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </div>
    <?php else: ?>
        <div class="header-icon" onclick="toggleSidebar()" title="Menu">
            <i class="fa-solid fa-bars"></i>
        </div>
    <?php endif; ?>
    <h1 class="header-title"><?php echo isset($pageTitle) ? $pageTitle : 'Intan Elyu'; ?></h1>
    <div class="header-icon" onclick="toggleNotifications()" title="Notifications">
        <i class="fa-solid fa-bell" id="bell-icon"></i>
        <div class="bell-dot" id="bell-dot"></div>
    </div>
</div>

<div id="notifications-dropdown" class="hide-scrollbar" style="position: fixed; top: max(env(safe-area-inset-top, 0px), 65px); right: 12px; left: 12px; max-width: 360px; margin: 0 auto; background: linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: none !important; outline: none !important; border-radius: 22px; z-index: 999999; box-shadow: 0 16px 40px rgba(10, 25, 60, 0.45); padding: 18px; max-height: 75vh; overflow-y: auto; scrollbar-width: none !important; -ms-overflow-style: none !important; opacity: 0; pointer-events: none; transform-origin: top right; transform: scale(0.4) translate(35px, -35px); transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1), transform 0.34s cubic-bezier(0.175, 0.885, 0.32, 1.15) !important;">
    <h3 style="margin: 0 0 14px 0; font-size: 16px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; border: none !important; outline: none !important; padding-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
        <span style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-bell" style="color: #00f2fe; font-size: 15px;"></i> Notifications
        </span>
        <button type="button" onclick="toggleNotifications()" style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; transition: transform 0.15s ease;" onactive="this.style.transform='scale(0.92)'">
            <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 14px;"></i>
        </button>
    </h3>
    <div id="notifications-list">
        <div style="color: #ffffff; opacity: 0.85; font-size: 13px; text-align: center; padding: 24px 0;">No new notifications.</div>
    </div>
</div>

<!-- Sidebar Menu Drawer -->
<div id="sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 99990; transition: opacity 0.3s ease;" onclick="toggleSidebar()"></div>
<div id="sidebar-menu" style="position: fixed; top: 0; left: -280px; width: 260px; bottom: 0; background: #ffffff; z-index: 99991; transition: left 0.32s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; box-shadow: 10px 0 35px rgba(0,0,0,0.12); border-right: 1px solid #f1f5f9; outline: none !important; overflow: hidden;">
    
    <!-- User Profile Header Banner -->
    <div style="padding: max(calc(env(safe-area-inset-top, 0px) + 16px), 20px) 16px 14px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.12); background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%); margin-top: 0; position: relative; flex-shrink: 0;">
        <button onclick="toggleSidebar()" style="position: absolute; top: max(calc(env(safe-area-inset-top, 0px) + 14px), 18px); right: 14px; background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.18); transition: all 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 13px;"></i>
        </button>
        
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 2px;">
            <div style="width: 46px; height: 46px; border-radius: 50%; overflow: hidden; border: 2px solid rgba(255, 255, 255, 0.35); box-shadow: 0 2px 8px rgba(0,0,0,0.2); flex-shrink: 0;">
                <img id="sidebar-avatar" src="https://ui-avatars.com/api/?name=Explorer&background=007AFF&color=fff&rounded=true&bold=true&size=128" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div style="flex: 1; min-width: 0; padding-right: 28px;">
                <h3 id="sidebar-user-name" style="margin: 0 0 3px 0; font-size: 16px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; letter-spacing: -0.2px;">Explorer</h3>
                <span style="font-size: 10.5px; font-weight: 800; color: #00f2fe; background: rgba(0, 242, 254, 0.18); padding: 2px 8px; border-radius: 100px; border: none !important; outline: none !important; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-compass" style="font-size: 9.5px; color: #00f2fe;"></i> Elyu Tourist
                </span>
            </div>
        </div>
    </div>

    <!-- Scrollable Navigation Items -->
    <div style="flex: 1; min-height: 0; padding: 14px 12px; display: flex; flex-direction: column; gap: 16px; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none; background: #ffffff;">
        
        <!-- Section: Your Stuff -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; padding-left: 4px;">Your Stuff</div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('saved_places'); return false;" style="color: #1e293b; text-decoration: none; font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 11px; padding: 9px 12px; border-radius: 13px; background: #f8fafc; border: 1px solid #edf2f7; transition: all 0.15s ease;" onpointerdown="this.style.background='#e2e8f0'" onpointerup="this.style.background='#f8fafc'" onpointercancel="this.style.background='#f8fafc'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ff3b30 !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(255, 59, 48, 0.28);"><i class="fa-solid fa-heart" style="color: #ffffff !important; font-size: 13px;"></i></span>
                    Saved Places
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('saved_trips'); return false;" style="color: #1e293b; text-decoration: none; font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 11px; padding: 9px 12px; border-radius: 13px; background: #f8fafc; border: 1px solid #edf2f7; transition: all 0.15s ease;" onpointerdown="this.style.background='#e2e8f0'" onpointerup="this.style.background='#f8fafc'" onpointercancel="this.style.background='#f8fafc'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #34c759 !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(52, 199, 89, 0.28);"><i class="fa-solid fa-route" style="color: #ffffff !important; font-size: 13px;"></i></span>
                    Saved Trips
                </a>
            </div>
        </div>

        <!-- Section: Discover -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; padding-left: 4px;">Discover & Explore</div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('trending'); return false;" style="color: #1e293b; text-decoration: none; font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 11px; padding: 9px 12px; border-radius: 13px; background: #f8fafc; border: 1px solid #edf2f7; transition: all 0.15s ease;" onpointerdown="this.style.background='#e2e8f0'" onpointerup="this.style.background='#f8fafc'" onpointercancel="this.style.background='#f8fafc'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ff9500 !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(255, 149, 0, 0.28);"><i class="fa-solid fa-compass" style="color: #ffffff !important; font-size: 13px;"></i></span>
                    Tourist Sites
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('puzzles'); return false;" style="color: #1e293b; text-decoration: none; font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 11px; padding: 9px 12px; border-radius: 13px; background: #f8fafc; border: 1px solid #edf2f7; transition: all 0.15s ease;" onpointerdown="this.style.background='#e2e8f0'" onpointerup="this.style.background='#f8fafc'" onpointercancel="this.style.background='#f8fafc'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #0284c7 !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.28);"><i class="fa-solid fa-gamepad" style="color: #ffffff !important; font-size: 13px;"></i></span>
                    GameZone
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('discount'); return false;" style="color: #1e293b; text-decoration: none; font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 11px; padding: 9px 12px; border-radius: 13px; background: #f8fafc; border: 1px solid #edf2f7; transition: all 0.15s ease;" onpointerdown="this.style.background='#e2e8f0'" onpointerup="this.style.background='#f8fafc'" onpointercancel="this.style.background='#f8fafc'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ec4899 !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(236, 72, 153, 0.28);"><i class="fa-solid fa-tags" style="color: #ffffff !important; font-size: 13px;"></i></span>
                    Discounts & Vouchers
                </a>
            </div>
        </div>

        <!-- Section: Support -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; padding-left: 4px;">Support & System</div>
            <div style="display: flex; flex-direction: column; gap: 3px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('settings'); return false;" style="color: #334155; text-decoration: none; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 11px; padding: 8px 10px; border-radius: 10px; transition: background 0.15s ease;" onpointerdown="this.style.background='#f1f5f9'" onpointerup="this.style.background='transparent'" onpointercancel="this.style.background='transparent'">
                    <i class="fa-solid fa-gear" style="color: #64748b; width: 18px; text-align: center; font-size: 14px;"></i> Settings
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('help'); return false;" style="color: #334155; text-decoration: none; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 11px; padding: 8px 10px; border-radius: 10px; transition: background 0.15s ease;" onpointerdown="this.style.background='#f1f5f9'" onpointerup="this.style.background='transparent'" onpointercancel="this.style.background='transparent'">
                    <i class="fa-solid fa-circle-question" style="color: #64748b; width: 18px; text-align: center; font-size: 14px;"></i> Help & FAQ
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('terms'); return false;" style="color: #334155; text-decoration: none; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 11px; padding: 8px 10px; border-radius: 10px; transition: background 0.15s ease;" onpointerdown="this.style.background='#f1f5f9'" onpointerup="this.style.background='transparent'" onpointercancel="this.style.background='transparent'">
                    <i class="fa-solid fa-shield-halved" style="color: #64748b; width: 18px; text-align: center; font-size: 14px;"></i> Terms & Privacy
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('about'); return false;" style="color: #334155; text-decoration: none; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 11px; padding: 8px 10px; border-radius: 10px; transition: background 0.15s ease;" onpointerdown="this.style.background='#f1f5f9'" onpointerup="this.style.background='transparent'" onpointercancel="this.style.background='transparent'">
                    <i class="fa-solid fa-circle-info" style="color: #64748b; width: 18px; text-align: center; font-size: 14px;"></i> About Us
                </a>
            </div>
        </div>

    </div>

    <!-- Locked Bottom Corner: Log Out Button -->
    <div style="flex-shrink: 0; padding: 12px 14px max(calc(env(safe-area-inset-bottom, 0px) + 12px), 14px) 14px; background: #ffffff; border-top: 1px solid #f1f5f9;">
        <a href="#" onclick="logoutUser(); return false;" id="sidebar-logout-btn" style="color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 14px; background: linear-gradient(135deg, #1e3a8a 0%, #193375 100%) !important; border: none !important; outline: none !important; border-radius: 12px; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.35); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
            <i class="fa-solid fa-right-from-bracket" style="color: #ffffff !important; font-size: 14px;"></i> Log Out
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

    function updateSidebarUserProfile() {
        try {
            const user = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('auth_user'), {}) : (JSON.parse(localStorage.getItem('auth_user') || '{}'));
            const avatarEl = document.getElementById('sidebar-avatar');
            const nameEl = document.getElementById('sidebar-user-name');
            if (user) {
                const fullName = (user.first_name || user.name || '').trim();
                const firstName = fullName ? fullName.split(/\s+/)[0] : 'Explorer';
                if (nameEl && firstName) nameEl.textContent = firstName;
                if (avatarEl && firstName) {
                    avatarEl.src = user.avatar ? (window.getFullImageUrl ? window.getFullImageUrl(user.avatar) : user.avatar) : `https://ui-avatars.com/api/?name=${encodeURIComponent(firstName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                }
            }
        } catch (e) {}
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar-menu');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar && overlay) {
            const isClosed = sidebar.style.left === '-280px' || !sidebar.style.left || sidebar.style.left === '' || sidebar.style.left.startsWith('-');
            if (isClosed) {
                updateSidebarUserProfile();
                sidebar.style.left = '0px';
                overlay.style.display = 'block';
                overlay.style.opacity = '1';
            } else {
                sidebar.style.left = '-280px';
                overlay.style.opacity = '0';
                setTimeout(() => { overlay.style.display = 'none'; }, 300);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateSidebarUserProfile);
    } else {
        updateSidebarUserProfile();
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
            dropdown.style.transform = 'scale(0.4) translate(35px, -35px)';
        } else {
            dropdown.style.opacity = '1';
            dropdown.style.pointerEvents = 'all';
            dropdown.style.transform = 'scale(1) translate(0, 0)';
            const bell = document.getElementById('bell-icon');
            if (bell) { bell.classList.remove('bell-ring'); void bell.offsetWidth; bell.classList.add('bell-ring'); }
            const dot = document.getElementById('bell-dot');
            if (dot) dot.classList.remove('show');
            fetchNotifications();
        }
    }

    // Close notifications dropdown on click outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notifications-dropdown');
        if (dropdown && dropdown.style.opacity === '1') {
            if (!dropdown.contains(e.target) && !e.target.closest('.header-icon')) {
                toggleNotifications();
            }
        }
    });

    async function fetchNotifications() {
        const list = document.getElementById('notifications-list');
        if (!list) return;
        list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;">Loading...</div>';

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token) {
            list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;">Please sign in to view notifications.</div>';
            return;
        }

        try {
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
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
</script>

<!-- Interactive Push Notification Popup Modal -->
<div id="push-notification-modal" class="push-notif-backdrop" style="display: none;" onclick="if(event.target===this) closePushNotificationModal()">
    <div class="push-notif-card">
        <div class="push-notif-header">
            <div style="flex: 1; min-width: 0;">
                <div id="push-notif-badge" class="push-notif-badge">
                    <span id="push-notif-category">PUSH NOTIFICATION</span>
                </div>
                <div id="push-notif-time" class="push-notif-time">Just now</div>
            </div>
            <button type="button" class="push-notif-close-btn" onclick="closePushNotificationModal()">&times;</button>
        </div>

        <h3 id="push-notif-title" class="push-notif-title">Push Notification Alert</h3>
        <p id="push-notif-body" class="push-notif-body">Notification details will appear here.</p>

        <div id="push-notif-footer-extra" style="display: none; margin-bottom: 16px; padding: 10px 14px; background: rgba(255, 255, 255, 0.12); border-radius: 12px; border: none !important; outline: none !important; font-size: 12px; color: #00f2fe;">
            <span id="push-notif-spot-name"></span>
        </div>

        <div class="push-notif-actions">
            <button type="button" class="push-notif-btn-secondary" onclick="closePushNotificationModal()">Dismiss</button>
            <button id="push-notif-delete-btn" type="button" class="push-notif-btn-delete" onclick="handlePushNotificationDelete()">Delete</button>
        </div>
    </div>
</div>

<style>
.push-notif-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1000005;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.push-notif-backdrop.active {
    opacity: 1;
    pointer-events: auto;
}
.push-notif-card {
    background: linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%) !important;
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: none !important;
    outline: none !important;
    border-radius: 26px;
    width: 100%;
    max-width: 380px;
    padding: 24px 22px;
    box-shadow: 0 25px 60px rgba(10, 25, 60, 0.55);
    transform: scale(0.85) translateY(20px);
    transition: transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1);
    text-align: left;
}
.push-notif-backdrop.active .push-notif-card {
    transform: scale(1) translateY(0);
}
.push-notif-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}
.push-notif-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    background: rgba(0, 242, 254, 0.22) !important;
    border: none !important;
    outline: none !important;
    color: #00f2fe !important;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.push-notif-time {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75) !important;
    font-weight: 600;
}
.push-notif-close-btn {
    background: #ffffff !important;
    border: none !important;
    outline: none !important;
    color: #1e3a8a !important;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
}
.push-notif-close-btn:hover {
    background: #f1f5f9 !important;
}
.push-notif-close-btn:active {
    transform: scale(0.95);
}
.push-notif-title {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 800;
    color: #ffffff !important;
    line-height: 1.35;
    letter-spacing: -0.3px;
    border: none !important;
    outline: none !important;
}
.push-notif-body {
    margin: 0 0 20px;
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.92) !important;
    line-height: 1.55;
    font-weight: 400;
    border: none !important;
    outline: none !important;
}
.push-notif-actions {
    display: flex;
    gap: 12px;
}
.push-notif-btn-secondary {
    flex: 1;
    border: none !important;
    outline: none !important;
    background: #ffffff !important;
    color: #1e3a8a !important;
    padding: 13px 18px;
    border-radius: 14px;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    text-align: center;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
}
.push-notif-btn-secondary:hover {
    background: #f1f5f9 !important;
}
.push-notif-btn-secondary:active {
    transform: scale(0.97);
}
.push-notif-btn-delete {
    flex: 1;
    border: none !important;
    outline: none !important;
    background: #ef4444 !important;
    color: #ffffff !important;
    padding: 13px 18px;
    border-radius: 14px;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    text-align: center;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
    transition: all 0.2s ease;
}
.push-notif-btn-delete:hover {
    background: #dc2626 !important;
}
.push-notif-btn-delete:active {
    transform: scale(0.97);
}
</style>

<script>
    var _currentPushNotifTargetUrl = window._currentPushNotifTargetUrl || null;
    var _currentPushNotifId = window._currentPushNotifId || null;
    window._notifTimerInterval = window._notifTimerInterval || null;

    window.cleanNotifTitle = function(title, isWelcome) {
        if (!title) return isWelcome ? 'Welcome to Intan Elyu' : 'Notification';
        return title
            .replace(/[\u{1F300}-\u{1F9FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|[\u{1F600}-\u{1F64F}]|[\u{1F680}-\u{1F6FF}]/gu, '')
            .trim();
    };

    window.cleanNotifMessage = function(msg) {
        if (!msg) return '';
        return msg
            .replace(/\s*was confirmed by admin/gi, ' was confirmed')
            .replace(/\s*(?:&|and|,)\s*\+?\d+\s*Points\.?/gi, '.')
            .replace(/\s*\+?\d+\s*Points\.?/gi, '')
            .replace(/\.\.+/g, '.')
            .replace(/\s+/g, ' ')
            .trim();
    };

    window.formatReverseTimer = function(timestamp) {
        if (!timestamp) return 'Just now';
        const now = Date.now();
        const diffSec = Math.max(0, Math.floor((now - timestamp) / 1000));
        if (diffSec < 1) return '1s ago';
        if (diffSec < 60) return `${diffSec}s ago`;
        const mins = Math.floor(diffSec / 60);
        const secs = diffSec % 60;
        if (mins < 60) return `${mins}m ${secs}s ago`;
        const hours = Math.floor(diffSec / 3600);
        const remMins = Math.floor((diffSec % 3600) / 60);
        if (hours < 24) return `${hours}h ${remMins}m ago`;
        const days = Math.floor(diffSec / 86400);
        return `${days}d ago`;
    };

    function startNotifTimerTicker() {
        if (window._notifTimerInterval) {
            clearInterval(window._notifTimerInterval);
        }
        window._notifTimerInterval = setInterval(() => {
            const timerEls = document.querySelectorAll('.notif-reverse-timer');
            timerEls.forEach(el => {
                const time = parseInt(el.getAttribute('data-time'), 10);
                if (time) {
                    const txt = el.querySelector('.timer-text');
                    if (txt) txt.textContent = window.formatReverseTimer(time);
                }
            });

            const modalTime = document.getElementById('push-notif-time');
            if (modalTime && modalTime.getAttribute('data-time')) {
                const t = parseInt(modalTime.getAttribute('data-time'), 10);
                if (t) {
                    modalTime.textContent = window.formatReverseTimer(t);
                }
            }
        }, 1000);
    }

    function renderNotifications(notifications) {
        const list = document.getElementById('notifications-list');
        const dot = document.getElementById('bell-dot');
        if (!list) return;

        if (notifications.length > 0) {
            let html = '';
            const unread = notifications.filter(n => !n.is_read);
            notifications.forEach(item => {
                const isWelcome = item.type === 'welcome';
                const isUnread = !item.is_read;
                const encodedItem = encodeURIComponent(JSON.stringify(item));
                const itemTime = item.created_at ? new Date(item.created_at).getTime() : Date.now();
                const timerStr = window.formatReverseTimer(itemTime);
                const formattedDate = new Date(itemTime).toLocaleDateString(undefined, {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
                const displayTitle = window.cleanNotifTitle(item.title, isWelcome);
                const displayMsg = window.cleanNotifMessage(item.message);

                html += `
                    <div class="notif-card-item" id="notif-item-${item.id}" style="display: block; margin-bottom: 10px; padding: 12px 14px; background: ${isWelcome ? 'linear-gradient(135deg, rgba(0, 242, 254, 0.14) 0%, rgba(2, 132, 199, 0.08) 100%)' : (isUnread ? 'rgba(56,189,248,0.08)' : 'rgba(255,255,255,0.03)')}; border: none !important; outline: none !important; border-radius: 14px; cursor: pointer; transition: transform 0.2s ease, opacity 0.2s ease, background 0.2s;" onclick="handleNotifClick('${encodedItem}', this)">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 5px;">
                            <div style="display: flex; align-items: center; gap: 6px; min-width: 0; flex: 1;">
                                ${isUnread ? '<span class="unread-dot" style="width: 7px; height: 7px; border-radius: 50%; background: #00f2fe; flex-shrink: 0; box-shadow: 0 0 8px #00f2fe; display: inline-block;"></span>' : ''}
                                ${isWelcome ? '<span style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(0, 242, 254, 0.25); color: #00f2fe; padding: 2px 6px; border-radius: 4px; border: none !important; outline: none !important; flex-shrink: 0;">Welcome</span>' : ''}
                                <span style="font-size: 13px; color: #ffffff; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${displayTitle}</span>
                            </div>
                            <button type="button" onclick="event.stopPropagation(); window.deleteNotification('${item.id}', this.closest('.notif-card-item'))" style="background: #ffffff !important; border: none !important; outline: none !important; color: #ef4444 !important; font-size: 11px; font-weight: 800; cursor: pointer; padding: 3px 10px; border-radius: 8px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important; display: inline-flex; align-items: center; justify-content: center; transition: transform 0.15s ease;" onactive="this.style.transform='scale(0.92)'">Delete</button>
                        </div>
                        <p style="margin: 0 0 6px 0; font-size: 12px; color: rgba(226, 232, 240, 0.9); line-height: 1.45; font-weight: ${isUnread ? '500' : '400'};">${displayMsg}</p>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span class="notif-reverse-timer" data-time="${itemTime}" style="font-size: 10px; font-weight: 700; color: #00f2fe; display: inline-flex; align-items: center; background: rgba(0, 242, 254, 0.12); padding: 2px 8px; border-radius: 100px; border: none !important; outline: none !important;">
                                <span class="timer-text">${timerStr}</span>
                            </span>
                            <span style="font-size: 10.5px; color: rgba(148,163,184,0.6); font-weight: 500;">${formattedDate}</span>
                        </div>
                    </div>
                `;
            });
            html += `<div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; margin-top: 6px; border-top: 1px solid rgba(255, 255, 255, 0.12) !important; outline: none !important; gap: 10px;">
                ${unread.length > 0 ? `<button type="button" onclick="markAllNotifRead()" style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; font-size: 11.5px; font-weight: 800; cursor: pointer; padding: 6px 14px; border-radius: 100px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.15s ease;" onactive="this.style.transform='scale(0.92)'">
                    <i class="fa-solid fa-check-double" style="font-size: 11px; color: #1e3a8a !important;"></i> Mark all read
                </button>` : '<span></span>'}
                <button type="button" onclick="window.clearAllNotifications()" style="background: #ffffff !important; border: none !important; outline: none !important; color: #ef4444 !important; font-size: 11.5px; font-weight: 800; cursor: pointer; padding: 6px 14px; border-radius: 100px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.15s ease;" onactive="this.style.transform='scale(0.92)'">
                    <i class="fa-solid fa-trash-can" style="font-size: 11px; color: #ef4444 !important;"></i> Clear all
                </button>
            </div>`;
            list.innerHTML = html;
            if (unread.length > 0 && dot) dot.classList.add('show');
            startNotifTimerTicker();
        } else {
            list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;">No new notifications.</div>';
            if (dot) dot.classList.remove('show');
        }
    }

    window.handleNotifClick = function(encodedItem, el) {
        try {
            const item = JSON.parse(decodeURIComponent(encodedItem));
            if (!item.is_read) {
                markNotifRead(item.id, el);
            }
            toggleNotifications(); // close dropdown
            showNotificationModal(item);
        } catch (e) {
            console.error("Error handling notification click:", e);
        }
    };

    window.showNotificationModal = function(opts) {
        if (!opts) return;
        const modal = document.getElementById('push-notification-modal');
        if (!modal) return;

        _currentPushNotifId = opts.id || null;
        const rawTitle = opts.title || (opts.type ? opts.type.replace(/_/g, ' ').toUpperCase() : 'Notification');
        const title = window.cleanNotifTitle(rawTitle, opts.type === 'welcome');
        const rawBody = opts.message || opts.body || 'You have a new update.';
        const body = window.cleanNotifMessage(rawBody);
        const type = opts.type || 'general';
        const actionUrl = opts.action_url || opts.url || null;
        const spotName = opts.spot_name || opts.spot || null;
        const notifTimestamp = opts.created_at ? new Date(opts.created_at).getTime() : Date.now();
        const timeStr = window.formatReverseTimer ? window.formatReverseTimer(notifTimestamp) : 'Just now';

        let category = 'PUSH ALERT';

        if (type === 'new_spot' || type === 'spot_added') {
            category = 'NEW SPOT';
        } else if (type === 'favorite_update' || type === 'spot_updated') {
            category = 'FAVORITE UPDATE';
        } else if (type === 'itinerary_reminder' || type === 'trip') {
            category = 'TRIP REMINDER';
        } else if (type === 'spot_maintenance' || type === 'alert') {
            category = 'SPOT ALERT';
        } else if (type === 'reward' || type === 'quest' || type === 'points') {
            category = 'REWARD UNLOCKED';
        } else if (type === 'welcome') {
            category = 'WELCOME TO ELYU';
        }

        // Apply dynamic DOM values
        const titleEl = document.getElementById('push-notif-title');
        const bodyEl = document.getElementById('push-notif-body');
        const catEl = document.getElementById('push-notif-category');
        const timeEl = document.getElementById('push-notif-time');
        const badgeEl = document.getElementById('push-notif-badge');
        const spotContainer = document.getElementById('push-notif-footer-extra');
        const spotNameEl = document.getElementById('push-notif-spot-name');

        if (titleEl) titleEl.textContent = title;
        if (bodyEl) bodyEl.textContent = body;
        if (catEl) catEl.textContent = category;
        if (timeEl) {
            timeEl.setAttribute('data-time', notifTimestamp);
            timeEl.textContent = timeStr;
        }

        if (badgeEl) {
            badgeEl.style.border = 'none';
            badgeEl.style.outline = 'none';
            badgeEl.style.background = 'rgba(0, 242, 254, 0.22)';
            badgeEl.style.color = '#00f2fe';
        }

        if (spotName && spotContainer && spotNameEl) {
            spotNameEl.textContent = spotName;
            spotContainer.style.display = 'block';
        } else if (spotContainer) {
            spotContainer.style.display = 'none';
        }

        _currentPushNotifTargetUrl = actionUrl;

        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    };

    window.closePushNotificationModal = function() {
        const modal = document.getElementById('push-notification-modal');
        if (!modal) return;
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    };

    window.handlePushNotificationAction = function() {
        closePushNotificationModal();
    };

    window.handlePushNotificationDelete = async function() {
        if (_currentPushNotifId) {
            const id = _currentPushNotifId;
            closePushNotificationModal();
            const cardEl = document.getElementById('notif-item-' + id);
            await window.deleteNotification(id, cardEl);
        } else {
            closePushNotificationModal();
        }
    };

    window.deleteNotification = async function(id, el) {
        if (!id) return;
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token) return;

        if (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateX(20px)';
            setTimeout(() => {
                el.remove();
                const list = document.getElementById('notifications-list');
                const remaining = list ? list.querySelectorAll('.notif-card-item') : [];
                if (remaining.length === 0 && list) {
                    list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;">No new notifications.</div>';
                    const dot = document.getElementById('bell-dot');
                    if (dot) dot.classList.remove('show');
                }
            }, 200);
        }

        try {
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
            const res = await fetch(backendUrl + '/api/tourist/notifications/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (typeof data.unread_count !== 'undefined') {
                    window.updateUnreadBadge(data.unread_count);
                }
            }
        } catch (e) {
            console.error("Error deleting notification:", e);
        }
    };

    window.clearAllNotifications = async function() {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token) return;

        const list = document.getElementById('notifications-list');
        if (list) {
            list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;">No new notifications.</div>';
        }
        const dot = document.getElementById('bell-dot');
        if (dot) dot.classList.remove('show');

        try {
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
            await fetch(backendUrl + '/api/tourist/notifications/clear-all', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            window.updateUnreadBadge(0);
        } catch (e) {
            console.error("Error clearing notifications:", e);
        }
    };

    async function markNotifRead(id, el) {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token) return;
        try {
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
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
                const dot = el.querySelector('.unread-dot');
                if (dot) dot.remove();
            }
            const dot = document.getElementById('bell-dot');
            const remaining = document.querySelectorAll('#notifications-list .unread-dot');
            if (remaining.length === 0 && dot) dot.classList.remove('show');
        } catch (e) {}
    }

    async function markAllNotifRead() {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token) return;
        try {
            const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
            await fetch(backendUrl + '/api/tourist/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const items = document.querySelectorAll('#notifications-list .notif-card-item');
            items.forEach(el => {
                el.style.opacity = '0.5';
                el.onclick = null;
                const dot = el.querySelector('.unread-dot');
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
