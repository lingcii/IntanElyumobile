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

<div id="notifications-dropdown" style="position: fixed; top: max(env(safe-area-inset-top, 0px), 65px); right: 12px; left: 12px; max-width: 360px; margin: 0 auto; background: linear-gradient(135deg, rgba(30, 58, 138, 0.98) 0%, rgba(63, 125, 183, 0.96) 60%, rgba(2, 132, 199, 0.96) 100%); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: none !important; outline: none !important; border-radius: 20px; z-index: 999999; box-shadow: none; padding: 18px; max-height: 75vh; overflow-y: auto; opacity: 0; pointer-events: none; transform: translateY(-10px) scale(0.96); transition: opacity 0.25s ease, transform 0.25s ease;">
    <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; border: none !important; outline: none !important; padding-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
        <span>Notifications</span>
        <i class="fa-solid fa-xmark" style="font-size: 16px; color: #ffffff; opacity: 0.85; cursor: pointer; padding: 4px; transition: color 0.2s;" onclick="toggleNotifications()"></i>
    </h3>
    <div id="notifications-list">
        <div style="color: #ffffff; opacity: 0.85; font-size: 13px; text-align: center; padding: 24px 0;">No new notifications.</div>
    </div>
</div>

<!-- Sidebar Menu Drawer -->
<div id="sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 99990; transition: opacity 0.3s ease;" onclick="toggleSidebar()"></div>
<div id="sidebar-menu" style="position: fixed; top: 0; left: -310px; width: 300px; bottom: 0; background: radial-gradient(ellipse at 90% 10%, rgba(0, 242, 254, 0.3) 0%, transparent 60%), radial-gradient(ellipse at 10% 50%, rgba(56, 189, 248, 0.25) 0%, transparent 60%), linear-gradient(180deg, #1e3a8a 0%, #2b5c9e 30%, #0284c7 70%, #06b6d4 100%); backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px); z-index: 99991; transition: left 0.35s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; box-shadow: 15px 0 50px rgba(0,0,0,0.5); border: none !important; outline: none !important; overflow: hidden;">
    
    <!-- User Profile Header Banner -->
    <div style="padding: max(calc(env(safe-area-inset-top, 0px) + 20px), 24px) 20px 18px 20px; border-bottom: none; background: rgba(30, 75, 135, 0.58); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); margin-top: 0; position: relative;">
        <button onclick="toggleSidebar()" style="position: absolute; top: max(calc(env(safe-area-inset-top, 0px) + 16px), 20px); right: 16px; background: rgba(255,255,255,0.16); border: none !important; outline: none !important; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;">
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
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: #ff3b30 !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);"><i class="fa-solid fa-heart" style="color: #ffffff !important; font-size: 14px;"></i></span>
                    Saved Places
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('saved_trips'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: #34c759 !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);"><i class="fa-solid fa-route" style="color: #ffffff !important; font-size: 14px;"></i></span>
                    Saved Trips
                </a>
            </div>
        </div>

        <!-- Section: Discover -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; padding-left: 6px;">Discover & Explore</div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('trending'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: #ff9500 !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);"><i class="fa-solid fa-compass" style="color: #ffffff !important; font-size: 14px;"></i></span>
                    Tourist Sites
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('puzzles'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: #0284c7 !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);"><i class="fa-solid fa-gamepad" style="color: #ffffff !important; font-size: 14px;"></i></span>
                    GameZone
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('discount'); return false;" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 16px; background: rgba(30, 75, 135, 0.58); border: none !important; outline: none !important; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: all 0.2s;">
                    <span style="width: 32px; height: 32px; border-radius: 10px; background: #ec4899 !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);"><i class="fa-solid fa-tags" style="color: #ffffff !important; font-size: 14px;"></i></span>
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

        <!-- Log Out Button -->
        <div style="margin-top: 10px; padding-bottom: max(calc(env(safe-area-inset-bottom, 0px) + 12px), 16px);">
            <a href="#" onclick="logoutUser(); return false;" id="sidebar-logout-btn" style="color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important; border: none !important; outline: none !important; border-radius: 14px; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4); transition: transform 0.15s ease, opacity 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                <i class="fa-solid fa-right-from-bracket" style="color: #ffffff !important; font-size: 16px;"></i> Log Out
            </a>
        </div>

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
                    <div class="notif-card-item" id="notif-item-${item.id}" style="display: flex; gap: 12px; margin-bottom: 10px; padding: 12px 14px; background: ${isWelcome ? 'linear-gradient(135deg, rgba(0, 242, 254, 0.14) 0%, rgba(2, 132, 199, 0.08) 100%)' : (isUnread ? 'rgba(56,189,248,0.08)' : 'rgba(255,255,255,0.03)')}; border: none !important; outline: none !important; border-radius: 14px; align-items: flex-start; cursor: pointer; transition: transform 0.2s ease, opacity 0.2s ease, background 0.2s;" onclick="handleNotifClick('${encodedItem}', this)">
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 3px;">
                                <div style="display: flex; align-items: center; gap: 6px; min-width: 0; flex: 1;">
                                    ${isWelcome ? '<span style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(0, 242, 254, 0.25); color: #00f2fe; padding: 2px 6px; border-radius: 4px; border: none !important; outline: none !important; flex-shrink: 0;">Welcome</span>' : ''}
                                    <span style="font-size: 13px; color: #ffffff; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${displayTitle}</span>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); window.deleteNotification('${item.id}', this.closest('.notif-card-item'))" style="background: none; border: none !important; outline: none !important; color: rgba(255, 255, 255, 0.45); font-size: 11px; font-weight: 700; cursor: pointer; padding: 2px 4px; border-radius: 4px; flex-shrink: 0; transition: color 0.15s;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='rgba(255,255,255,0.45)'">Delete</button>
                            </div>
                            <p style="margin: 0 0 6px 0; font-size: 12px; color: rgba(226, 232, 240, 0.9); line-height: 1.45; font-weight: ${isUnread ? '500' : '400'};">${displayMsg}</p>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                <span class="notif-reverse-timer" data-time="${itemTime}" style="font-size: 10px; font-weight: 700; color: #00f2fe; display: inline-flex; align-items: center; background: rgba(0, 242, 254, 0.12); padding: 2px 8px; border-radius: 100px; border: none !important; outline: none !important;">
                                    <span class="timer-text">${timerStr}</span>
                                </span>
                                <span style="font-size: 10.5px; color: rgba(148,163,184,0.6); font-weight: 500;">${formattedDate}</span>
                            </div>
                        </div>
                        ${isUnread ? '<span class="unread-dot" style="width: 7px; height: 7px; border-radius: 50%; background: #00f2fe; margin-top: 6px; flex-shrink: 0; box-shadow: 0 0 8px #00f2fe; border: none !important; outline: none !important;"></span>' : ''}
                    </div>
                `;
            });
            html += `<div style="display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: none !important; outline: none !important;">
                ${unread.length > 0 ? '<button onclick="markAllNotifRead()" style="background: none; border: none !important; outline: none !important; color: #38bdf8; font-size: 12px; font-weight: 700; cursor: pointer; padding: 4px 6px;">Mark all read</button>' : '<span></span>'}
                <button onclick="window.clearAllNotifications()" style="background: none; border: none !important; outline: none !important; color: #f87171; font-size: 12px; font-weight: 700; cursor: pointer; padding: 4px 6px;">Clear all</button>
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
