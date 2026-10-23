<!-- Top App Header Component -->
<div class="mobile-header">
    <?php if (isset($backRoute) && $backRoute): ?>
        <div class="header-icon" onclick="typeof window.navigateBack === 'function' ? window.navigateBack('<?php echo htmlspecialchars($backRoute); ?>') : navigateTo('<?php echo htmlspecialchars($backRoute); ?>')" title="Back">
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

<!-- Notifications Modal Backdrop with Soft Blur -->
<div id="notifications-backdrop" onclick="toggleNotifications()" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; background: rgba(10, 25, 60, 0.45); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 999990; opacity: 0; transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1); pointer-events: none;"></div>

<div id="notifications-dropdown" class="hide-scrollbar" style="position: fixed; top: calc(max(env(safe-area-inset-top, 0px), 36px) + 54px); right: 12px; left: 12px; max-width: 360px; margin: 0 auto; background: #ffffff; border: none !important; outline: none !important; border-radius: 22px; z-index: 999999; box-shadow: 0 16px 40px rgba(10, 25, 60, 0.45); padding: 0; max-height: 75vh; display: flex; flex-direction: column; overflow: hidden; opacity: 0; pointer-events: none; transform-origin: top right; transform: scale(0.4) translate(35px, -35px); transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1), transform 0.34s cubic-bezier(0.175, 0.885, 0.32, 1.15) !important;">
    <!-- Notifications Header Banner -->
    <div style="padding: 14px 16px; border-bottom: none !important; background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%); flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
        <span style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">
            <i class="fa-solid fa-bell" style="color: #00f2fe; font-size: 15px;"></i> Notifications
        </span>
        <button type="button" onclick="toggleNotifications()" style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 14px;"></i>
        </button>
    </div>

    <!-- Scrollable Middle Body (White Background) -->
    <div id="notifications-list" class="hide-scrollbar" style="flex: 1; min-height: 0; padding: 14px 12px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; scrollbar-width: none !important; -ms-overflow-style: none !important; background: #ffffff; border: none !important; outline: none !important;">
        <div class="empty-state-card notif-empty-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 28px 18px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important; border-radius: 16px; margin: 4px 0; border: none !important; outline: none !important;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #ffffff !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);">
                <i class="fa-solid fa-bell-slash" style="color: #1e3a8a !important; font-size: 22px;"></i>
            </div>
            <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.2px;">No Notifications Yet</h4>
            <p style="margin: 0; font-size: 12px; color: rgba(255, 255, 255, 0.85); line-height: 1.45; max-width: 230px;">
                You're all caught up! Updates, trip reminders, and vouchers will appear here.
            </p>
        </div>
    </div>

    <!-- Locked Bottom Footer Banner -->
    <div id="notifications-footer" style="flex-shrink: 0; padding: 12px 14px; background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%); border-top: none !important; border: none !important; outline: none !important; display: none; align-items: center; justify-content: space-between; gap: 10px;">
        <button type="button" id="notif-mark-all-btn" onclick="markAllNotifRead()" style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; font-size: 11.5px; font-weight: 800; cursor: pointer; padding: 6px 14px; border-radius: 100px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-check-double" style="font-size: 11px; color: #1e3a8a !important;"></i> Mark all read
        </button>
        <button type="button" onclick="window.clearAllNotifications()" style="background: #ffffff !important; border: none !important; outline: none !important; color: #ef4444 !important; font-size: 11.5px; font-weight: 800; cursor: pointer; padding: 6px 14px; border-radius: 100px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important; display: inline-flex; align-items: center; gap: 6px; margin-left: auto; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
            <i class="fa-solid fa-trash-can" style="font-size: 11px; color: #ef4444 !important;"></i> Clear all
        </button>
    </div>
</div>

<div id="sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 100002 !important; transition: opacity 0.3s ease; pointer-events: none;" onclick="toggleSidebar()"></div>
<div id="sidebar-menu" style="position: fixed; top: 0; left: -280px; width: 260px; bottom: 0; background: #ffffff; z-index: 100003 !important; transition: left 0.32s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; box-shadow: 10px 0 35px rgba(0,0,0,0.12); border: none !important; border-right: none !important; outline: none !important; overflow: hidden;">
    
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
            <div style="display: flex; flex-direction: column; gap: 7px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('saved_places'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-heart" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    Saved Places
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('saved_trips'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-route" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    Saved Trips
                </a>
            </div>
        </div>

        <!-- Section: Discover -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; padding-left: 4px;">Discover & Explore</div>
            <div style="display: flex; flex-direction: column; gap: 7px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('trending'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-compass" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    Tourist Sites
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('puzzles'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-gamepad" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    GameZone
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('discount'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-tags" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    Discounts & Vouchers
                </a>
            </div>
        </div>

        <!-- Section: Support -->
        <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; padding-left: 4px;">Support & System</div>
            <div style="display: flex; flex-direction: column; gap: 7px;">
                <a href="#" onclick="toggleSidebar(); navigateTo('settings'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-gear" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    Settings
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('help'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-circle-question" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    Help & FAQ
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('terms'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-shield-halved" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    Terms & Privacy
                </a>
                <a href="#" onclick="toggleSidebar(); navigateTo('about'); return false;" style="color: #ffffff !important; text-decoration: none; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <span style="width: 32px; height: 32px; border-radius: 9px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);"><i class="fa-solid fa-circle-info" style="color: #1e3a8a !important; font-size: 13px;"></i></span>
                    About Us
                </a>
            </div>
        </div>

    </div>

    <!-- Locked Bottom Corner: Log Out Button -->
    <div style="flex-shrink: 0; padding: 12px 14px max(calc(env(safe-area-inset-bottom, 0px) + 12px), 14px) 14px; background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%); border-top: 1px solid rgba(255, 255, 255, 0.12);">
        <a href="#" onclick="logoutUser(); return false;" id="sidebar-logout-btn" style="color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 14px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important; border: none !important; outline: none !important; border-radius: 12px; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
            <i class="fa-solid fa-right-from-bracket" style="color: #ffffff !important; font-size: 14px;"></i> Log Out
        </a>
    </div>
</div>

<!-- Universal Logout Confirmation Modal -->
<div id="logout-confirm-modal" onclick="if(event.target===this) window.closeLogoutModal();"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 25, 60, 0.72); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 24px; width: 100%; max-width: 340px; padding: 24px 20px; text-align: center; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28); position: relative; animation: popInLogoutModal 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        
        <!-- Icon -->
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </div>

        <!-- Question Title -->
        <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.3;">
            Are you sure you want to log out?
        </h3>
        
        <!-- Subtitle Note -->
        <p id="logout-modal-subtext" style="margin: 0 0 20px 0; font-size: 13px; color: #64748b; line-height: 1.45;">
            You will be signed out of your tourist explorer session.
        </p>

        <!-- No & Yes Action Buttons -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <button type="button" id="logout-btn-no" onclick="window.closeLogoutModal()"
                style="padding: 12px 16px; border-radius: 14px; background: #f1f5f9; border: 1.5px solid #e2e8f0; color: #475569; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                No
            </button>
            <button type="button" id="logout-btn-yes" onclick="window.confirmExecuteLogout()"
                style="padding: 12px 16px; border-radius: 14px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; color: #ffffff; font-size: 14px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35); transition: all 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                Yes
            </button>
        </div>
    </div>
</div>
<style>
@keyframes popInLogoutModal {
    0% { opacity: 0; transform: scale(0.92); }
    100% { opacity: 1; transform: scale(1); }
}
</style>

<script>
    function updateSidebarUserProfile() {
        try {
            const user = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('auth_user'), {}) : (JSON.parse(localStorage.getItem('auth_user') || '{}'));
            const avatarEl = document.getElementById('sidebar-avatar');
            const nameEl = document.getElementById('sidebar-user-name');
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const isGuest = !token || token === 'guest_token' || token === 'offline_guest_token' || token.startsWith('guest_') || token.startsWith('offline_');
            if (user) {
                const fullName = (user.first_name || user.name || '').trim();
                const firstName = fullName ? fullName.split(/\s+/)[0] : 'Explorer';
                if (nameEl && firstName) nameEl.textContent = firstName;
                if (avatarEl && firstName) {
                    avatarEl.src = user.avatar ? (window.getFullImageUrl ? window.getFullImageUrl(user.avatar) : user.avatar) : `https://ui-avatars.com/api/?name=${encodeURIComponent(firstName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                }
            }
            const logoutBtn = document.getElementById('sidebar-logout-btn');
            if (logoutBtn) {
                if (isGuest) {
                    logoutBtn.innerHTML = '<i class="fa-solid fa-user-plus" style="color: #ffffff !important; font-size: 14px;"></i> Register / Sign In';
                    logoutBtn.style.setProperty('background', 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)', 'important');
                    logoutBtn.onclick = function() { 
                        closeSidebarInstantly(); 
                        navigateTo('auth&mode=register&from_sidebar=1'); 
                        return false; 
                    };
                } else {
                    logoutBtn.innerHTML = '<i class="fa-solid fa-right-from-bracket" style="color: #ffffff !important; font-size: 14px;"></i> Log Out';
                    logoutBtn.style.setProperty('background', 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)', 'important');
                    logoutBtn.onclick = function() { logoutUser(); return false; };
                }
            }
        } catch (e) {}
    }

    function closeSidebarInstantly() {
        const sidebar = document.getElementById('sidebar-menu');
        const overlay = document.getElementById('sidebar-overlay');
        const bottomNav = document.getElementById('bottom-navigation');
        if (sidebar) sidebar.style.left = '-280px';
        if (overlay) {
            overlay.style.display = 'none';
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
        }
        if (bottomNav) bottomNav.classList.remove('nav-hidden');
    }
    window.closeSidebarInstantly = closeSidebarInstantly;

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar-menu');
        const overlay = document.getElementById('sidebar-overlay');
        const bottomNav = document.getElementById('bottom-navigation');
        if (sidebar && overlay) {
            const isClosed = sidebar.style.left === '-280px' || !sidebar.style.left || sidebar.style.left === '' || sidebar.style.left.startsWith('-');
            if (isClosed) {
                updateSidebarUserProfile();
                sidebar.style.left = '0px';
                overlay.style.display = 'block';
                overlay.style.opacity = '1';
                overlay.style.pointerEvents = 'auto';
                if (bottomNav) bottomNav.classList.add('nav-hidden');
            } else {
                sidebar.style.left = '-280px';
                overlay.style.opacity = '0';
                overlay.style.pointerEvents = 'none';
                if (bottomNav) bottomNav.classList.remove('nav-hidden');
                setTimeout(() => { overlay.style.display = 'none'; }, 250);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateSidebarUserProfile);
    } else {
        updateSidebarUserProfile();
    }

    function logoutUser() {
        const sidebar = document.getElementById('sidebar-menu');
        const overlay = document.getElementById('sidebar-overlay');
        const bottomNav = document.getElementById('bottom-navigation');
        if (sidebar && overlay) {
            sidebar.style.left = '-280px';
            overlay.style.opacity = '0';
            if (bottomNav) bottomNav.classList.remove('nav-hidden');
            setTimeout(() => { overlay.style.display = 'none'; }, 300);
        }
        if (typeof window.showLogoutConfirmationModal === 'function') {
            window.showLogoutConfirmationModal();
        } else if (typeof handleLogout === 'function') {
            handleLogout();
        } else {
            if (confirm("Are you sure you want to log out?")) {
                if (typeof window.purgeAllUserSessionCaches === 'function') {
                    window.purgeAllUserSessionCaches();
                } else {
                    localStorage.removeItem('intan_elyu_token');
                    localStorage.removeItem('Intan_Elyu_Token');
                    localStorage.removeItem('api_token');
                    localStorage.removeItem('auth_user');
                    localStorage.removeItem('auth_user_backup');
                    localStorage.removeItem('intan_elyu_user');
                }
                window.location.href = '?view=auth';
            }
        }
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        const backdrop = document.getElementById('notifications-backdrop');
        if (!dropdown) return;
        const isOpen = dropdown.style.opacity === '1';
        if (isOpen) {
            document.body.classList.remove('notif-open');
            dropdown.style.opacity = '0';
            dropdown.style.pointerEvents = 'none';
            dropdown.style.transform = 'scale(0.4) translate(35px, -35px)';
            if (backdrop) {
                backdrop.style.opacity = '0';
                backdrop.style.pointerEvents = 'none';
                setTimeout(() => {
                    if (dropdown.style.opacity === '0') {
                        backdrop.style.display = 'none';
                    }
                }, 300);
            }
        } else {
            document.body.classList.add('notif-open');
            if (backdrop) {
                backdrop.style.display = 'block';
                void backdrop.offsetHeight; // trigger reflow for smooth animation
                backdrop.style.opacity = '1';
                backdrop.style.pointerEvents = 'auto';
            }
            dropdown.style.opacity = '1';
            dropdown.style.pointerEvents = 'auto';
            dropdown.style.transform = 'scale(1) translate(0, 0)';
            const bell = document.getElementById('bell-icon');
            if (bell) { bell.classList.remove('bell-ring'); void bell.offsetWidth; bell.classList.add('bell-ring'); }
            fetchNotifications();
        }
    }

    // Close notifications dropdown on click outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notifications-dropdown');
        if (dropdown && dropdown.style.opacity === '1') {
            if (!dropdown.contains(e.target) && !e.target.closest('.header-icon') && !e.target.closest('#notifications-backdrop')) {
                toggleNotifications();
            }
        }
    });

    async function fetchNotifications() {
        const list = document.getElementById('notifications-list');
        const footer = document.getElementById('notifications-footer');
        if (!list) return;
        if (footer) footer.style.display = 'none';

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token || token === 'offline_guest_token' || token === 'guest_token' || token.startsWith('guest_') || token.startsWith('offline_')) {
            list.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 36px 16px; gap: 10px; color: #64748b;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-regular fa-user" style="font-size: 20px; color: #94a3b8;"></i>
                    </div>
                    <span style="font-size: 13px; font-weight: 600; color: #64748b;">Please sign in to view notifications.</span>
                </div>
            `;
            return;
        }

        const notifCacheKey = 'intan_elyu_cached_notifs_' + token.substring(0, 10);
        let cachedNotifs = getCachedNotifs();

        // Fast offline check: immediately render cached notifications if offline
        if (!navigator.onLine || window.isAppOnline === false) {
            if (Array.isArray(cachedNotifs) && cachedNotifs.length > 0) {
                renderNotifications(cachedNotifs);
            } else {
                list.innerHTML = getNotifEmptyStateHtml();
            }
            return;
        }

        // Pre-hydrate from cache while fetching live
        if (Array.isArray(cachedNotifs) && cachedNotifs.length > 0) {
            renderNotifications(cachedNotifs);
        } else {
            list.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 36px 16px; gap: 10px; color: #64748b;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 22px; color: #1e3a8a;"></i>
                    <span style="font-size: 13px; font-weight: 600; color: #64748b;">Loading notifications...</span>
                </div>
            `;
        }

        try {
            const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
            const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
            const timeoutId = controller ? setTimeout(() => controller.abort(), 8000) : null;
            try {
                const res = await fetch(backendUrl + '/api/tourist/notifications', {
                    signal: controller ? controller.signal : undefined,
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    const deletedIds = getDeletedNotifIds();
                    const rawNotifs = data.notifications || [];
                    const notifs = rawNotifs.filter(n => n && n.id && !deletedIds.includes(String(n.id)));
                    // Anchor fixed timestamps on fresh items before caching
                    notifs.forEach(n => {
                        n._fixed_timestamp = window.getNotifTimestamp(n);
                    });
                    saveCachedNotifs(notifs);
                    renderNotifications(notifs);
                    return;
                }
            } finally {
                if (timeoutId) clearTimeout(timeoutId);
            }
            // If fetch did not succeed but we have cache
            cachedNotifs = getCachedNotifs();
            if (Array.isArray(cachedNotifs) && cachedNotifs.length > 0) {
                renderNotifications(cachedNotifs);
            } else {
                list.innerHTML = getNotifEmptyStateHtml();
            }
        } catch (e) {
            // Gracefully fallback to cache or empty state - never display error exclamation
            cachedNotifs = getCachedNotifs();
            if (Array.isArray(cachedNotifs) && cachedNotifs.length > 0) {
                renderNotifications(cachedNotifs);
            } else {
                list.innerHTML = getNotifEmptyStateHtml();
            }
        }
    }
</script>

<!-- Interactive Push Notification Popup Modal (Header, White Middle, Button Footer, No View Details) -->
<div id="push-notification-modal" class="push-notif-backdrop" style="display: none;" onclick="if(event.target===this) closePushNotificationModal()">
    <div class="push-notif-card">
        <!-- Top Header -->
        <div class="push-notif-header">
            <div style="flex: 1; min-width: 0;">
                <div id="push-notif-badge" class="push-notif-badge">
                    <span id="push-notif-category">PUSH NOTIFICATION</span>
                </div>
                <div id="push-notif-time" class="push-notif-time" data-time="">Just now</div>
            </div>
            <button type="button" class="push-notif-close-btn" onclick="closePushNotificationModal()" aria-label="Close">&times;</button>
        </div>

        <!-- White Middle Body -->
        <div class="push-notif-body-container">
            <h3 id="push-notif-title" class="push-notif-title">Welcome to Intan Elyu</h3>
            <p id="push-notif-body" class="push-notif-body">Notification details will appear here.</p>

            <div id="push-notif-footer-extra" style="display: none; margin-top: 14px; padding: 10px 14px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; font-size: 12px; color: #0284c7; font-weight: 600;">
                <span id="push-notif-spot-name"></span>
            </div>
        </div>

        <!-- Footer Buttons (Dismiss & Delete only; View Details removed) -->
        <div class="push-notif-footer">
            <button type="button" class="push-notif-btn-secondary" onclick="closePushNotificationModal()">Dismiss</button>
            <button id="push-notif-delete-btn" type="button" class="push-notif-btn-delete" onclick="handlePushNotificationDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
    var _currentPushNotifTargetUrl = window._currentPushNotifTargetUrl || null;
    var _currentPushNotifId = window._currentPushNotifId || null;
    window._notifTimerInterval = window._notifTimerInterval || null;
    window._notifItemsMap = window._notifItemsMap || {};

    function getDeletedNotifIds() {
        try {
            const raw = localStorage.getItem('intan_elyu_deleted_notif_ids');
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed.map(String) : [];
        } catch (e) { return []; }
    }

    function markNotifIdDeleted(id) {
        if (!id) return;
        try {
            const ids = getDeletedNotifIds();
            const strId = String(id);
            if (!ids.includes(strId)) {
                ids.push(strId);
                localStorage.setItem('intan_elyu_deleted_notif_ids', JSON.stringify(ids));
            }
        } catch (e) {}
    }

    function getNotifCacheKey() {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token || token === 'offline_guest_token' || token === 'guest_token' || token.startsWith('guest_') || token.startsWith('offline_')) {
            return null;
        }
        return 'intan_elyu_cached_notifs_' + token.substring(0, 10);
    }

    function getCachedNotifs() {
        const key = getNotifCacheKey();
        if (!key) return [];
        try {
            const raw = localStorage.getItem(key);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    const deletedIds = getDeletedNotifIds();
                    return parsed.filter(n => n && n.id && !deletedIds.includes(String(n.id)));
                }
            }
        } catch (e) { }
        return [];
    }

    function saveCachedNotifs(notifs) {
        const key = getNotifCacheKey();
        if (!key) return;
        try {
            const deletedIds = getDeletedNotifIds();
            const cleanList = (notifs || []).filter(n => n && n.id && !deletedIds.includes(String(n.id)));
            localStorage.setItem(key, JSON.stringify(cleanList));
        } catch (e) { }
    }

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

    /**
     * Anchors notification timestamp to an immutable point in time.
     * Prevents the timer from resetting to 'Just now' when opening modals or re-rendering.
     */
    window.getNotifTimestamp = function(item) {
        if (!item) return Date.now();
        if (typeof item._fixed_timestamp === 'number' && item._fixed_timestamp > 0) {
            return item._fixed_timestamp;
        }
        if (item.created_at) {
            let str = String(item.created_at).trim();
            if (/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}/.test(str)) {
                str = str.replace(/\s+/, 'T');
                if (!/[Z+\-]\d*$/.test(str)) {
                    str = str + 'Z';
                }
            }
            const t = new Date(str).getTime();
            if (!isNaN(t) && t > 0) {
                item._fixed_timestamp = t;
                return t;
            }
        }
        if (typeof item.server_time === 'number' && typeof item.elapsed_seconds === 'number') {
            const t = (item.server_time - item.elapsed_seconds) * 1000;
            if (!isNaN(t) && t > 0) {
                item._fixed_timestamp = t;
                return t;
            }
        }
        if (typeof item.elapsed_seconds === 'number' && !isNaN(item.elapsed_seconds) && item.elapsed_seconds >= 0) {
            const t = Date.now() - (item.elapsed_seconds * 1000);
            item._fixed_timestamp = t;
            return t;
        }
        item._fixed_timestamp = Date.now();
        return item._fixed_timestamp;
    };

    window.formatReverseTimer = function(timestamp) {
        if (!timestamp || isNaN(timestamp)) return 'Just now';
        const now = Date.now();
        const diffSec = Math.max(0, Math.floor((now - timestamp) / 1000));
        if (isNaN(diffSec) || diffSec < 2) return 'Just now';
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

    function getNotifEmptyStateHtml() {
        return `
            <div class="empty-state-card notif-empty-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 28px 18px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important; border-radius: 16px; margin: 4px 0; border: none !important; outline: none !important;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #ffffff !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);">
                    <i class="fa-solid fa-bell-slash" style="color: #1e3a8a !important; font-size: 22px;"></i>
                </div>
                <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.2px;">No Notifications Yet</h4>
                <p style="margin: 0; font-size: 12px; color: rgba(255, 255, 255, 0.85); line-height: 1.45; max-width: 230px;">
                    You're all caught up! Updates, trip reminders, and vouchers will appear here.
                </p>
            </div>
        `;
    }

    function renderNotifications(notifications) {
        const list = document.getElementById('notifications-list');
        const footer = document.getElementById('notifications-footer');
        const dot = document.getElementById('bell-dot');
        if (!list) return;

        window._notifItemsMap = window._notifItemsMap || {};

        const deletedIds = getDeletedNotifIds();
        const activeNotifs = (Array.isArray(notifications) ? notifications : []).filter(n => n && n.id && !deletedIds.includes(String(n.id)));

        if (activeNotifs.length > 0) {
            let html = '';
            const unread = activeNotifs.filter(n => !n.is_read);
            activeNotifs.forEach(item => {
                if (!item || !item.id) return;
                window._notifItemsMap[item.id] = item;
                const isWelcome = item.type === 'welcome';
                const isUnread = !item.is_read;
                const itemTime = window.getNotifTimestamp(item);
                const timerStr = window.formatReverseTimer(itemTime);
                const formattedDate = new Date(itemTime).toLocaleDateString(undefined, {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
                const displayTitle = window.cleanNotifTitle(item.title, isWelcome);
                const displayMsg = window.cleanNotifMessage(item.message);

                html += `
                    <div class="notif-card-item" id="notif-item-${item.id}" style="display: block; padding: 12px 14px; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.28) !important; border: none !important; outline: none !important; border-radius: 14px; cursor: pointer; transition: transform 0.2s ease, opacity 0.2s ease, background 0.2s;" onclick="handleNotifClick('${item.id}', this)" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 5px;">
                            <div style="display: flex; align-items: center; gap: 6px; min-width: 0; flex: 1;">
                                ${isUnread ? '<span class="unread-dot" style="width: 7px; height: 7px; border-radius: 50%; background: #00f2fe; flex-shrink: 0; box-shadow: 0 0 8px #00f2fe; display: inline-block;"></span>' : ''}
                                ${isWelcome ? '<span style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(0, 242, 254, 0.25); color: #00f2fe; padding: 2px 6px; border-radius: 4px; border: none !important; outline: none !important; flex-shrink: 0;">Welcome</span>' : ''}
                                <span style="font-size: 13.5px; color: #ffffff; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${displayTitle}</span>
                            </div>
                            <button type="button" onclick="event.stopPropagation(); window.deleteNotification('${item.id}', this.closest('.notif-card-item'))" style="background: #ffffff !important; border: none !important; outline: none !important; color: #ef4444 !important; font-size: 11px; font-weight: 800; cursor: pointer; padding: 3px 10px; border-radius: 8px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important; display: inline-flex; align-items: center; justify-content: center; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">Delete</button>
                        </div>
                        <p style="margin: 0 0 6px 0; font-size: 12px; color: ${isUnread ? '#ffffff' : 'rgba(255, 255, 255, 0.85)'}; line-height: 1.45; font-weight: ${isUnread ? '600' : '400'};">${displayMsg}</p>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span class="notif-reverse-timer" data-time="${itemTime}" style="font-size: 10px; font-weight: 700; color: #00f2fe; display: inline-flex; align-items: center; background: rgba(0, 242, 254, 0.18); padding: 2px 8px; border-radius: 100px; border: none !important; outline: none !important;">
                                <span class="timer-text">${timerStr}</span>
                            </span>
                            <span style="font-size: 10.5px; color: rgba(255, 255, 255, 0.65); font-weight: 500;">${formattedDate}</span>
                        </div>
                    </div>
                `;
            });
            list.innerHTML = html;
            if (footer) {
                footer.style.display = 'flex';
                const markBtn = document.getElementById('notif-mark-all-btn');
                if (markBtn) markBtn.style.display = unread.length > 0 ? 'inline-flex' : 'none';
            }
            if (dot) {
                if (unread.length > 0) dot.classList.add('show');
                else dot.classList.remove('show');
            }
            startNotifTimerTicker();
        } else {
            list.innerHTML = getNotifEmptyStateHtml();
            if (footer) footer.style.display = 'none';
            if (dot) dot.classList.remove('show');
        }
    }

    window.handleNotifClick = function(id, el) {
        try {
            const item = (window._notifItemsMap && window._notifItemsMap[id]) ? window._notifItemsMap[id] : null;
            if (!item) return;
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
        const spotName = opts.spot_name || opts.spot || (opts.data && opts.data.spot_name) || null;
        const notifTimestamp = window.getNotifTimestamp ? window.getNotifTimestamp(opts) : Date.now();
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
        const spotContainer = document.getElementById('push-notif-footer-extra');
        const spotNameEl = document.getElementById('push-notif-spot-name');

        if (titleEl) titleEl.textContent = title;
        if (bodyEl) bodyEl.textContent = body;
        if (catEl) catEl.textContent = category;
        if (timeEl) {
            timeEl.setAttribute('data-time', notifTimestamp);
            timeEl.textContent = timeStr;
        }

        if (spotName && spotContainer && spotNameEl) {
            spotNameEl.textContent = spotName;
            spotContainer.style.display = 'block';
        } else if (spotContainer) {
            spotContainer.style.display = 'none';
        }

        document.body.classList.add('notif-open');
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    };

    window.closePushNotificationModal = function() {
        document.body.classList.remove('notif-open');
        const modal = document.getElementById('push-notification-modal');
        if (!modal) return;
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    };

    window.handlePushNotificationAction = function() {
        // Fallback action handler
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

        // Immediate persistent deletion: record in deleted list so it NEVER resurfaces
        markNotifIdDeleted(id);
        delete window._notifItemsMap[id];

        // Immediate cache synchronization
        try {
            let cached = getCachedNotifs();
            cached = cached.filter(n => String(n.id) !== String(id));
            saveCachedNotifs(cached);
        } catch(e) {}

        if (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateX(20px)';
            setTimeout(() => {
                el.remove();
                const list = document.getElementById('notifications-list');
                const remaining = list ? list.querySelectorAll('.notif-card-item') : [];
                if (remaining.length === 0 && list) {
                    list.innerHTML = getNotifEmptyStateHtml();
                    const dot = document.getElementById('bell-dot');
                    if (dot) dot.classList.remove('show');
                    const footer = document.getElementById('notifications-footer');
                    if (footer) footer.style.display = 'none';
                } else {
                    const unreadRemaining = list ? list.querySelectorAll('.unread-dot') : [];
                    if (unreadRemaining.length === 0) {
                        const dot = document.getElementById('bell-dot');
                        if (dot) dot.classList.remove('show');
                        const markBtn = document.getElementById('notif-mark-all-btn');
                        if (markBtn) markBtn.style.display = 'none';
                    }
                }
            }, 200);
        }

        if (!token) return;

        try {
            const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
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

        // Mark all current cached items as deleted permanently
        try {
            const cached = getCachedNotifs();
            cached.forEach(n => { if (n && n.id) markNotifIdDeleted(n.id); });
            saveCachedNotifs([]);
        } catch(e) {}

        window._notifItemsMap = {};

        const list = document.getElementById('notifications-list');
        if (list) {
            list.innerHTML = getNotifEmptyStateHtml();
        }
        const dot = document.getElementById('bell-dot');
        if (dot) dot.classList.remove('show');
        const footer = document.getElementById('notifications-footer');
        if (footer) footer.style.display = 'none';

        if (!token) return;

        try {
            const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
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

        // Immediate cache synchronization: set is_read = true in localStorage
        try {
            let cached = getCachedNotifs();
            let changed = false;
            cached.forEach(n => {
                if (String(n.id) === String(id)) {
                    n.is_read = true;
                    changed = true;
                }
            });
            if (changed) saveCachedNotifs(cached);
        } catch(e) {}

        try {
            const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
            await fetch(backendUrl + '/api/tourist/notifications/' + id + '/read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            if (el) {
                const dot = el.querySelector('.unread-dot');
                if (dot) dot.remove();
                const msg = el.querySelector('p');
                if (msg) {
                    msg.style.fontWeight = '400';
                    msg.style.color = 'rgba(255, 255, 255, 0.85)';
                }
            }
            const dot = document.getElementById('bell-dot');
            const remaining = document.querySelectorAll('#notifications-list .unread-dot');
            if (remaining.length === 0) {
                if (dot) dot.classList.remove('show');
                const markBtn = document.getElementById('notif-mark-all-btn');
                if (markBtn) markBtn.style.display = 'none';
            }
        } catch (e) {}
    }

    async function markAllNotifRead() {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('tourist_token');
        if (!token) return;

        // Immediate cache synchronization: set all is_read = true in localStorage
        try {
            let cached = getCachedNotifs();
            cached.forEach(n => { n.is_read = true; });
            saveCachedNotifs(cached);
        } catch(e) {}

        try {
            const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
            await fetch(backendUrl + '/api/tourist/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const items = document.querySelectorAll('#notifications-list .notif-card-item');
            items.forEach(el => {
                const dot = el.querySelector('.unread-dot');
                if (dot) dot.remove();
                const msg = el.querySelector('p');
                if (msg) {
                    msg.style.fontWeight = '400';
                    msg.style.color = 'rgba(255, 255, 255, 0.85)';
                }
            });
            const dot = document.getElementById('bell-dot');
            if (dot) dot.classList.remove('show');
            const markBtn = document.getElementById('notif-mark-all-btn');
            if (markBtn) markBtn.style.display = 'none';
        } catch (e) {}
    }

    window.updateUnreadBadge = function(count) {
        const dot = document.getElementById('bell-dot');
        if (dot) {
            if (count > 0) dot.classList.add('show');
            else dot.classList.remove('show');
        }
    };

    // Immediate badge check on page load from cached notifications
    (function initBellBadgeFromCache() {
        try {
            const cached = getCachedNotifs();
            if (Array.isArray(cached) && cached.length > 0) {
                const unread = cached.filter(n => !n.is_read);
                window.updateUnreadBadge(unread.length);
            }
        } catch(e) {}
    })();
</script>
