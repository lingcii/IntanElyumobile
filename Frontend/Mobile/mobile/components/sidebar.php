<!-- Sidebar / Drawer Component -->
<style>
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 3000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.4s ease;
    }
    
    .sidebar-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    
    .sidebar-drawer {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 300px;
        background: rgba(255, 255, 255, 0.85); /* Premium glass */
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        z-index: 3001;
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
        display: flex;
        flex-direction: column;
        box-shadow: 10px 0 30px rgba(0,0,0,0.1);
        border-radius: 0 30px 30px 0;
        overflow: hidden;
    }
    
    .sidebar-drawer.active {
        transform: translateX(0);
    }
    
    .sidebar-header {
        padding: 50px 24px 30px 24px;
        background: linear-gradient(135deg, var(--primary-color) 0%, #005ce6 100%);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
    }
    
    /* Decorative circle in header */
    .sidebar-header::after {
        content: '';
        position: absolute;
        top: -30px;
        right: -30px;
        width: 150px;
        height: 150px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    
    .sidebar-logo {
        width: 64px;
        height: 64px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2), inset 0 0 0 1px rgba(255,255,255,0.8);
        padding: 10px;
        z-index: 2;
    }
    
    .sidebar-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .sidebar-title {
        font-size: 22px;
        font-weight: 800;
        margin: 0 0 4px 0;
        letter-spacing: 0.5px;
        z-index: 2;
    }
    
    .sidebar-subtitle {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        font-weight: 500;
        z-index: 2;
    }
    
    .sidebar-menu {
        flex: 1;
        padding: 24px 16px;
        overflow-y: auto;
    }
    
    .sidebar-item {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        color: #333;
        text-decoration: none;
        font-size: 16px;
        font-weight: 600;
        border-radius: 16px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }
    
    .sidebar-item:active {
        background: rgba(0, 122, 255, 0.1);
        transform: scale(0.98);
    }
    
    .sidebar-item i {
        width: 36px;
        height: 36px;
        background: rgba(0, 122, 255, 0.08);
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 16px;
        color: var(--primary-color);
        margin-right: 16px;
        transition: all 0.2s ease;
    }
    
    .sidebar-item:active i {
        background: var(--primary-color);
        color: white;
    }
    
    .sidebar-footer {
        padding: 24px 16px;
    }
    
    .logout-btn {
        background: rgba(255, 59, 48, 0.1);
        color: #FF3B30 !important;
    }
    
    .logout-btn i {
        background: rgba(255, 59, 48, 0.1) !important;
        color: #FF3B30 !important;
    }
    
    .logout-btn:active {
        background: rgba(255, 59, 48, 0.2) !important;
    }
    
    .header-profile-reminder {
        position: absolute;
        top: 24px;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .header-profile-reminder:active {
        transform: scale(0.95);
        background: rgba(255, 255, 255, 0.3);
    }
    
    .header-profile-reminder .pulse-dot {
        width: 8px;
        height: 8px;
        background: #FF3B30;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.7);
        animation: pulse-red 2s infinite;
    }
    
    @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 59, 48, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 59, 48, 0); }
    }
    
    /* Dark Mode Overrides */
    body.dark-theme .sidebar-drawer {
        background: rgba(28, 28, 30, 0.85); /* Dark premium glass */
        box-shadow: 10px 0 30px rgba(0,0,0,0.5);
    }
    
    body.dark-theme .sidebar-item {
        color: #E5E5EA;
    }
    
    body.dark-theme .sidebar-item:active {
        background: rgba(255, 255, 255, 0.1);
    }
    
    body.dark-theme .sidebar-header {
        background: linear-gradient(135deg, #1C1C1E 0%, #121214 100%);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    
    body.dark-theme .sidebar-logo {
        background: rgba(44, 44, 46, 0.95);
        box-shadow: 0 8px 24px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(255,255,255,0.1);
    }
</style>

<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="sidebar-drawer" id="sidebar-drawer">
    <div class="sidebar-header">
        <a href="#" class="header-profile-reminder" onclick="toggleSidebar(); navigateTo('setup_profile'); return false;">
            <div class="pulse-dot"></div> Complete Profile
        </a>
        <div class="sidebar-logo">
            <img src="assets/img/logo.png" alt="Logo" onerror="this.src=''">
        </div>
        <h2 class="sidebar-title">Intan Elyu</h2>
        <p class="sidebar-subtitle">Tourist Account</p>
    </div>
    
    <div class="sidebar-menu">
        <a href="#" class="sidebar-item" onclick="toggleSidebar(); navigateTo('settings'); return false;">
            <i class="fa-solid fa-gear"></i> Settings
        </a>
        <a href="#" class="sidebar-item" onclick="toggleSidebar(); navigateTo('help'); return false;">
            <i class="fa-solid fa-circle-question"></i> Help Center
        </a>
        <a href="#" class="sidebar-item" onclick="toggleSidebar(); navigateTo('terms'); return false;">
            <i class="fa-solid fa-shield-halved"></i> Terms & Privacy
        </a>
        <a href="#" class="sidebar-item" onclick="toggleSidebar(); navigateTo('about'); return false;">
            <i class="fa-solid fa-circle-info"></i> About Intan Elyu
        </a>
    </div>
    
    <div class="sidebar-footer">
        <a href="#" class="sidebar-item logout-btn" onclick="toggleSidebar(); handleLogout(); return false;">
            <i class="fa-solid fa-right-from-bracket"></i> Log Out
        </a>
    </div>
</div>
