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
        color: #ffffff !important;
        font-size: 16px;
        cursor: pointer;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border: none !important;
        outline: none !important;
        flex-shrink: 0;
        transition: transform 0.15s ease, background 0.15s ease;
    }
    
    .header-icon:active {
        transform: scale(0.92);
        background: rgba(255, 255, 255, 0.24);
    }
    
    .header-icon .bell-dot {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #00f2fe;
        border-radius: 50%;
        box-shadow: 0 0 0 2px #1e3a8a;
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
        <i class="fa-regular fa-bell" id="bell-icon"></i>
        <div class="bell-dot" id="bell-dot"></div>
    </div>
</div>

<!-- Notifications Dropdown (In Front of All Elements) -->
<div id="notifications-dropdown" style="position: fixed; top: max(env(safe-area-inset-top, 0px), 65px); right: 12px; left: 12px; max-width: 360px; margin: 0 auto; background: linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: none !important; outline: none !important; border-radius: 20px; z-index: 999999; box-shadow: none; padding: 18px; max-height: 75vh; overflow-y: auto; opacity: 0; pointer-events: none; transform: translateY(-10px) scale(0.96); transition: opacity 0.25s ease, transform 0.25s ease;">
    <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="fa-regular fa-bell" style="margin-right: 8px; color: #00f2fe;"></i>Notifications</span>
        <i class="fa-solid fa-xmark" style="font-size: 16px; color: #ffffff; opacity: 0.85; cursor: pointer; padding: 4px; transition: color 0.2s;" onclick="toggleNotifications()"></i>
    </h3>
    <div id="notifications-list">
        <div style="color: #ffffff; opacity: 0.85; font-size: 13px; text-align: center; padding: 24px 0;"><i class="fa-regular fa-bell-slash" style="margin-right: 6px;"></i>No new notifications.</div>
    </div>
</div>

<!-- Sidebar Menu Drawer -->
<div id="sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 99990; transition: opacity 0.3s ease;" onclick="toggleSidebar()"></div>
<div id="sidebar-menu" style="position: fixed; top: 0; left: -310px; width: 300px; bottom: 0; background: radial-gradient(ellipse at 90% 10%, rgba(0, 242, 254, 0.3) 0%, transparent 60%), radial-gradient(ellipse at 10% 50%, rgba(56, 189, 248, 0.25) 0%, transparent 60%), linear-gradient(180deg, #1e3a8a 0%, #2b5c9e 30%, #0284c7 70%, #06b6d4 100%); backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px); z-index: 99991; transition: left 0.35s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; box-shadow: 15px 0 50px rgba(0,0,0,0.5); border: none !important; outline: none !important; overflow: hidden;">
    
    <!-- User Profile Header Banner -->
    <div style="padding: 24px 20px 18px 20px; border-bottom: none; background: rgba(30, 75, 135, 0.58); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); margin-top: max(env(safe-area-inset-top), 20px); position: relative;">
        <button onclick="toggleSidebar()" style="position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.16); border: none !important; outline: none !important; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;">
            <i class="fa-solid fa-xmark"></i>
        </button>
        
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 4px;">
            <div style="width: 52px; height: 52px; border-radius: 50%; overflow: hidden; border: none !important; outline: none !important; box-shadow: none; flex-shrink: 0;">
                <img id="sidebar-avatar" src="https://ui-avatars.com/api/?name=Tourist&background=007AFF&color=fff&rounded=true&bold=true&size=128" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div style="flex: 1; min-width: 0;">
                <h3 id="sidebar-user-name" style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Hi, Explorer!</h3>
                <span style="font-size: 11px; font-weight: 800; color: #00f2fe; background: rgba(0, 242, 254, 0.2); padding: 3px 10px; border-radius: 100px; border: none !important; outline: none !important; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-compass" style="font-size: 10px;"></i> Elyu Tourist
                </span>
            </div>
        </div>
    </div>

    <!-- Scrollable Navigation Items -->
    <div style="flex: 1; padding: 18px 16px; display: flex; flex-direction: column; gap: 16px; overflow-y: auto;">
        
        <!-- Section: Your Stuff -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; padding-left: 6px;">Your Stuff</div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('saved_places'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: rgba(255,59,48,0.25); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-heart" style="color: #ff453a; font-size: 14px;"></i></span>
                    Saved Places
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('saved_trips'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: rgba(52,199,89,0.25); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-route" style="color: #30d158; font-size: 14px;"></i></span>
                    Saved Trips
                </a>
            </div>
        </div>

        <!-- Section: Discover -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; padding-left: 6px;">Discover & Explore</div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('trending'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: rgba(239,68,68,0.25); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-fire" style="color: #ff453a; font-size: 14px;"></i></span>
                    Trending Sites
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('puzzles'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: rgba(0,242,254,0.25); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-gamepad" style="color: #00f2fe; font-size: 14px;"></i></span>
                    GameZone
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('discount'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: rgba(236,72,153,0.25); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-tags" style="color: #ff375f; font-size: 14px;"></i></span>
                    Discounts & Vouchers
                </a>
            </div>
        </div>

        <!-- Section: Support -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; padding-left: 6px;">Support & System</div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('settings'); return false;" style="color: #ffffff; opacity: 0.95; text-decoration: none; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; padding: 9px 12px; border-radius: 12px; transition: all 0.2s;">
                    <i class="fa-solid fa-gear" style="color: #ffffff; width: 20px; text-align: center;"></i> Settings
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('help'); return false;" style="color: #ffffff; opacity: 0.95; text-decoration: none; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; padding: 9px 12px; border-radius: 12px; transition: all 0.2s;">
                    <i class="fa-solid fa-circle-question" style="color: #ffffff; width: 20px; text-align: center;"></i> Help & FAQ
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('terms'); return false;" style="color: #ffffff; opacity: 0.95; text-decoration: none; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; padding: 9px 12px; border-radius: 12px; transition: all 0.2s;">
                    <i class="fa-solid fa-shield-halved" style="color: #ffffff; width: 20px; text-align: center;"></i> Terms & Privacy
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('about'); return false;" style="color: #ffffff; opacity: 0.95; text-decoration: none; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; padding: 9px 12px; border-radius: 12px; transition: all 0.2s;">
                    <i class="fa-solid fa-circle-info" style="color: #ffffff; width: 20px; text-align: center;"></i> About Us
                </a>
            </div>
        </div>

    </div>

    <!-- Sidebar Bottom Footer -->
    <div style="padding: 16px 20px; border-top: none; background: rgba(15, 23, 42, 0.45);">
        <a href="#" onclick="logoutUser(); return false;" id="sidebar-logout-btn" style="color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important; border: none !important; outline: none !important; border-radius: 14px; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4); transition: transform 0.15s ease, opacity 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
            <i class="fa-solid fa-right-from-bracket" style="color: #ffffff !important; font-size: 16px;"></i> Log Out
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
            const isClosed = sidebar.style.left === '-310px' || !sidebar.style.left || sidebar.style.left === '';
            if (isClosed) {
                // Populate user profile info in sidebar
                const user = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('auth_user'), {}) : {};
                const avatarEl = document.getElementById('sidebar-avatar');
                const nameEl = document.getElementById('sidebar-user-name');
                if (user && user.name) {
                    if (nameEl) nameEl.textContent = user.name;
                    if (avatarEl) {
                        avatarEl.src = user.avatar ? (window.getFullImageUrl ? window.getFullImageUrl(user.avatar) : user.avatar) : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                    }
                }

                sidebar.style.left = '0px';
                overlay.style.display = 'block';
                overlay.style.opacity = '1';
            } else {
                sidebar.style.left = '-310px';
                overlay.style.opacity = '0';
                setTimeout(() => { overlay.style.display = 'none'; }, 300);
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
            dropdown.style.transform = 'translateY(-10px) scale(0.96)';
        } else {
            dropdown.style.opacity = '1';
            dropdown.style.pointerEvents = 'all';
            dropdown.style.transform = 'translateY(0) scale(1)';
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

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token) {
            list.innerHTML = '<div style="color: rgba(148,163,184,0.6); font-size: 13px; text-align: center; padding: 24px 0;"><i class="fa-regular fa-bell-slash" style="margin-right: 6px;"></i>Please sign in to view notifications.</div>';
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
            <div id="push-notif-icon-ring" class="push-notif-icon-ring">
                <i id="push-notif-icon" class="fa-solid fa-bell"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div id="push-notif-badge" class="push-notif-badge">
                    <i id="push-notif-badge-icon" class="fa-solid fa-bolt"></i>
                    <span id="push-notif-category">PUSH NOTIFICATION</span>
                </div>
                <div id="push-notif-time" class="push-notif-time">Just now</div>
            </div>
            <button type="button" class="push-notif-close-btn" onclick="closePushNotificationModal()">&times;</button>
        </div>

        <h3 id="push-notif-title" class="push-notif-title">Push Notification Alert</h3>
        <p id="push-notif-body" class="push-notif-body">Notification details will appear here.</p>

        <div id="push-notif-footer-extra" style="display: none; margin-bottom: 16px; padding: 10px 14px; background: rgba(56,189,248,0.08); border-radius: 12px; border: 1px dashed rgba(56,189,248,0.3); font-size: 12px; color: #38bdf8;">
            <i class="fa-solid fa-location-dot" style="margin-right: 6px;"></i><span id="push-notif-spot-name"></span>
        </div>

        <div class="push-notif-actions">
            <button id="push-notif-action-btn" type="button" class="push-notif-btn-primary" onclick="handlePushNotificationAction()">
                <i class="fa-solid fa-compass" style="margin-right: 6px;"></i><span id="push-notif-action-text">View Details</span>
            </button>
            <button type="button" class="push-notif-btn-secondary" onclick="closePushNotificationModal()">Dismiss</button>
        </div>
    </div>
</div>

<style>
.push-notif-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1000005;
    background: rgba(15, 23, 42, 0.82);
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
    background: linear-gradient(145deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.99));
    border: 1px solid rgba(56, 189, 248, 0.35);
    border-radius: 28px;
    width: 100%;
    max-width: 380px;
    padding: 24px;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.8), 0 0 50px rgba(56, 189, 248, 0.18);
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
.push-notif-icon-ring {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: rgba(56, 189, 248, 0.15);
    border: 2px solid #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #38bdf8;
    flex-shrink: 0;
    box-shadow: 0 0 20px rgba(56, 189, 248, 0.3);
    animation: pushPulse 2s infinite;
}
@keyframes pushPulse {
    0% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.4); }
    70% { box-shadow: 0 0 0 14px rgba(56, 189, 248, 0); }
    100% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
}
.push-notif-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    background: rgba(56, 189, 248, 0.15);
    border: 1px solid rgba(56, 189, 248, 0.3);
    color: #38bdf8;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.push-notif-time {
    font-size: 11px;
    color: rgba(148, 163, 184, 0.6);
    font-weight: 500;
}
.push-notif-close-btn {
    background: rgba(255, 255, 255, 0.08);
    border: none;
    color: rgba(248, 250, 252, 0.7);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.push-notif-close-btn:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
}
.push-notif-title {
    margin: 0 0 8px;
    font-size: 19px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.35;
    letter-spacing: -0.3px;
}
.push-notif-body {
    margin: 0 0 20px;
    font-size: 14px;
    color: rgba(203, 213, 225, 0.9);
    line-height: 1.55;
    font-weight: 400;
}
.push-notif-actions {
    display: flex;
    gap: 10px;
}
.push-notif-btn-primary {
    flex: 1;
    border: none;
    background: linear-gradient(135deg, #38bdf8, #2563eb);
    color: #ffffff;
    padding: 13px;
    border-radius: 14px;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.push-notif-btn-primary:active {
    transform: scale(0.97);
}
.push-notif-btn-secondary {
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.06);
    color: rgba(248, 250, 252, 0.8);
    padding: 13px 18px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.push-notif-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.12);
}
</style>

<script>
    var _currentPushNotifTargetUrl = window._currentPushNotifTargetUrl || null;

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
                } else if (item.type === 'welcome') {
                    icon = 'fa-compass';
                    color = '#38bdf8';
                }

                const isUnread = !item.is_read;
                const encodedItem = encodeURIComponent(JSON.stringify(item));
                html += `
                    <div style="display: flex; gap: 12px; margin-bottom: 10px; padding: 12px; background: ${isUnread ? 'rgba(56,189,248,0.06)' : 'rgba(255,255,255,0.02)'}; border: 1px solid ${isUnread ? 'rgba(56,189,248,0.12)' : 'rgba(255,255,255,0.04)'}; border-radius: 12px; align-items: flex-start; cursor: pointer; transition: background 0.2s;" onclick="handleNotifClick('${encodedItem}', this)">
                        <div style="width: 34px; height: 34px; border-radius: 50%; background: ${color}15; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid ${color}30;">
                            <i class="fa-solid ${icon}" style="color: ${color}; font-size: 14px;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #e2e8f0; line-height: 1.4; font-weight: ${isUnread ? '600' : '400'};">${item.message || item.title}</p>
                            <span style="font-size: 11px; color: rgba(148,163,184,0.5); font-weight: 500;">${new Date(item.created_at || Date.now()).toLocaleDateString(undefined, {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})}</span>
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

        const title = opts.title || (opts.type ? opts.type.replace(/_/g, ' ').toUpperCase() : 'Notification');
        const body = opts.message || opts.body || 'You have a new update.';
        const type = opts.type || 'general';
        const actionUrl = opts.action_url || opts.url || null;
        const spotName = opts.spot_name || opts.spot || null;
        const timeStr = opts.created_at ? new Date(opts.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Just now';

        let icon = 'fa-bell';
        let color = '#38bdf8';
        let category = 'PUSH ALERT';
        let badgeIcon = 'fa-bolt';

        if (type === 'new_spot' || type === 'spot_added') {
            icon = 'fa-map-pin';
            color = '#34c759';
            category = 'NEW SPOT';
            badgeIcon = 'fa-location-dot';
        } else if (type === 'favorite_update' || type === 'spot_updated') {
            icon = 'fa-star';
            color = '#f59e0b';
            category = 'FAVORITE UPDATE';
            badgeIcon = 'fa-star';
        } else if (type === 'itinerary_reminder' || type === 'trip') {
            icon = 'fa-calendar-day';
            color = '#8b5cf6';
            category = 'TRIP REMINDER';
            badgeIcon = 'fa-clock';
        } else if (type === 'spot_maintenance' || type === 'alert') {
            icon = 'fa-triangle-exclamation';
            color = '#ef4444';
            category = 'SPOT ALERT';
            badgeIcon = 'fa-triangle-exclamation';
        } else if (type === 'reward' || type === 'quest' || type === 'points') {
            icon = 'fa-trophy';
            color = '#facc15';
            category = 'REWARD UNLOCKED';
            badgeIcon = 'fa-gift';
        } else if (type === 'welcome') {
            icon = 'fa-compass';
            color = '#38bdf8';
            category = 'SYSTEM NOTICE';
            badgeIcon = 'fa-compass';
        }

        // Apply dynamic DOM values
        const titleEl = document.getElementById('push-notif-title');
        const bodyEl = document.getElementById('push-notif-body');
        const catEl = document.getElementById('push-notif-category');
        const timeEl = document.getElementById('push-notif-time');
        const iconEl = document.getElementById('push-notif-icon');
        const ringEl = document.getElementById('push-notif-icon-ring');
        const badgeEl = document.getElementById('push-notif-badge');
        const badgeIconEl = document.getElementById('push-notif-badge-icon');
        const spotContainer = document.getElementById('push-notif-footer-extra');
        const spotNameEl = document.getElementById('push-notif-spot-name');
        const actionBtn = document.getElementById('push-notif-action-btn');

        if (titleEl) titleEl.textContent = title;
        if (bodyEl) bodyEl.textContent = body;
        if (catEl) catEl.textContent = category;
        if (timeEl) timeEl.textContent = timeStr;

        if (ringEl) {
            ringEl.style.borderColor = color;
            ringEl.style.background = color + '20';
            ringEl.style.boxShadow = `0 0 24px ${color}40`;
        }
        if (iconEl) {
            iconEl.className = `fa-solid ${icon}`;
            iconEl.style.color = color;
        }
        if (badgeEl) {
            badgeEl.style.borderColor = color + '50';
            badgeEl.style.background = color + '20';
            badgeEl.style.color = color;
        }
        if (badgeIconEl) {
            badgeIconEl.className = `fa-solid ${badgeIcon}`;
        }

        if (spotName && spotContainer && spotNameEl) {
            spotNameEl.textContent = spotName;
            spotContainer.style.display = 'block';
        } else if (spotContainer) {
            spotContainer.style.display = 'none';
        }

        _currentPushNotifTargetUrl = actionUrl;
        if (actionBtn) {
            if (actionUrl) {
                actionBtn.style.display = 'flex';
            } else {
                actionBtn.style.display = 'none';
            }
        }

        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
        document.body.style.overflow = 'hidden';
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
        if (_currentPushNotifTargetUrl) {
            const target = _currentPushNotifTargetUrl;
            closePushNotificationModal();
            setTimeout(() => {
                window.location.href = target;
            }, 150);
        } else {
            closePushNotificationModal();
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
                const dot = el.querySelector('.fa-circle');
                if (dot) dot.remove();
            }
            const dot = document.getElementById('bell-dot');
            const remaining = document.querySelectorAll('#notifications-list .fa-circle');
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
