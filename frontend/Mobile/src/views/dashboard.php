<!-- Dashboard View -->
<?php
$pageTitle = 'Discover La Union';
$activeTab = 'dashboard';

// Scan municipality images from local assets
$municipalityImages = [];
$imgDir = __DIR__ . '/../assets/img/MUNICIPALITIES';
if (is_dir($imgDir)) {
    $munis = scandir($imgDir);
    foreach ($munis as $muni) {
        if ($muni === '.' || $muni === '..')
            continue;
        if (is_dir("$imgDir/$muni")) {
            $files = scandir("$imgDir/$muni");
            foreach ($files as $f) {
                $fLower = strtolower($f);
                if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/', $fLower)) {
                    $municipalityImages[strtoupper($muni)][] = $f;
                }
            }
        }
    }
}
?>

<?php include __DIR__ . '/../components/header.php'; ?>




<div class="dashboard-container has-header has-bottom-nav animate-slide-up">

    <!-- Offline Status Banner -->
    <div id="dash-offline-pill" style="display: none; align-items: center; justify-content: center; gap: 8px; margin: 0 0 14px 0; padding: 8px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 12px; font-weight: 800; text-align: center;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
        <span>Offline Mode &bull; Showing Saved Guides & Local GPS</span>
    </div>

    <!-- Dynamic Guard Style for Instant Guest Profile Header Sanitization -->
    <style id="dash-guest-guard-style"></style>

    <!-- Critical Hero Banner, Weather & Component Styles -->
    <style id="dash-critical-styles">
    /* Hero Banner */
    .dash-hero-banner {
        position: relative !important;
        border-radius: 28px !important;
        background: linear-gradient(135deg, #0e2a56 0%, #15386f 50%, #1e4080 100%) !important;
        padding: 22px 18px 18px 18px !important;
        overflow: hidden !important;
        margin-bottom: 14px !important;
        box-shadow: 0 8px 24px rgba(14, 42, 86, 0.25) !important;
        display: flex !important;
        justify-content: space-between !important;
    }
    .dash-hero-content {
        position: relative !important;
        z-index: 2 !important;
        flex: 1 !important;
        min-width: 0 !important;
    }
    .dash-hero-eyebrow {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        letter-spacing: -0.2px !important;
        display: block !important;
    }
    .dash-hero-title {
        font-size: 32px !important;
        font-weight: 900 !important;
        color: #ffffff !important;
        margin: 2px 0 6px 0 !important;
        letter-spacing: -0.8px !important;
        line-height: 1.1 !important;
    }
    .dash-hero-subtitle {
        font-size: 12px !important;
        font-weight: 500 !important;
        color: rgba(255, 255, 255, 0.85) !important;
        margin: 0 0 14px 0 !important;
        line-height: 1.4 !important;
        letter-spacing: -0.1px !important;
    }
    .dash-user-strip {
        background: rgba(255, 255, 255, 0.12) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border-radius: 14px !important;
        padding: 6px 12px !important;
        display: inline-flex !important;
        width: fit-content !important;
        max-width: 100% !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        box-sizing: border-box !important;
        margin-bottom: 10px !important;
    }
    .dash-user-avatar-wrap {
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        overflow: hidden !important;
        flex-shrink: 0 !important;
        border: 1.5px solid rgba(255, 255, 255, 0.6) !important;
    }
    #dash-avatar {
        width: 32px !important;
        height: 32px !important;
        max-width: 32px !important;
        max-height: 32px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        display: block !important;
    }
    .dash-user-details {
        flex: 1 !important;
        min-width: 0 !important;
    }
    .dash-user-name {
        font-size: 13.5px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block !important;
    }
    .dash-user-subrow {
        display: none !important;
    }
    #dash-title-row, #dash-title, #dash-status-badge {
        display: none !important;
    }
    .dash-user-badge {
        display: none !important;
    }
    .dash-mini-stats {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-shrink: 0 !important;
        margin-top: 2px !important;
    }
    .dash-mini-stat {
        background: rgba(255, 255, 255, 0.15) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        border-radius: 12px !important;
        padding: 5px 12px !important;
        text-align: center !important;
        min-width: 52px !important;
        cursor: pointer !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
        transition: transform 0.15s ease, background 0.15s ease !important;
    }
    .dash-mini-stat:active {
        transform: scale(0.96) !important;
        background: rgba(255, 255, 255, 0.22) !important;
    }
    .dash-mini-val {
        font-size: 13px !important;
        font-weight: 900 !important;
        color: #ffffff !important;
        display: block !important;
        line-height: 1.1 !important;
    }
    .dash-mini-lbl {
        font-size: 8.5px !important;
        font-weight: 700 !important;
        color: rgba(255, 255, 255, 0.8) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
        display: block !important;
        margin-top: 2px !important;
    }
    .dash-hero-art {
        position: absolute !important;
        right: -4px !important;
        bottom: -6px !important;
        width: 140px !important;
        height: 160px !important;
        pointer-events: none !important;
        z-index: 1 !important;
    }

    /* Weather Card: Decreased Height & Sleek Compact Proportions */
    .weather-card {
        background: linear-gradient(135deg, #1d4085 0%, #173570 100%) !important;
        border-radius: 22px !important;
        padding: 12px 16px !important;
        box-shadow: 0 6px 20px rgba(23, 53, 112, 0.25) !important;
        border: none !important;
        outline: none !important;
        color: #ffffff !important;
        cursor: pointer !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 12px !important;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .weather-top-row {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: 100% !important;
        margin-bottom: 4px !important;
    }
    .weather-loc-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        white-space: nowrap !important;
    }
    .weather-loc-badge i {
        color: #38bdf8 !important;
        font-size: 11px !important;
    }
    .weather-mid-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        margin: 2px 0 8px 0 !important;
    }
    .weather-left-info {
        flex: 1 !important;
        min-width: 0 !important;
    }
    .weather-temp-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .weather-temp-val {
        font-size: 30px !important;
        font-weight: 900 !important;
        letter-spacing: -0.8px !important;
        line-height: 1 !important;
        color: #ffffff !important;
    }
    .weather-icon-badge {
        font-size: 22px !important;
        line-height: 1 !important;
    }
    .weather-desc-main {
        font-size: 13.5px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        margin-top: 2px !important;
    }
    .weather-tip-msg {
        font-size: 10.5px !important;
        color: rgba(255, 255, 255, 0.85) !important;
        font-weight: 600 !important;
        margin-top: 1px !important;
    }
    .weather-art-wrap {
        flex-shrink: 0 !important;
        margin-left: 10px !important;
    }
    .weather-art-wrap svg {
        width: 78px !important;
        height: 50px !important;
    }
    .weather-bottom-row {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        gap: 6px !important;
        border-top: 1px solid rgba(255, 255, 255, 0.15) !important;
        padding-top: 8px !important;
    }
    .weather-stat-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
    }
    .weather-stat-pill i {
        color: #38bdf8 !important;
        font-size: 10px !important;
    }
    .weather-forecast-pill {
        background: rgba(255, 255, 255, 0.18) !important;
        border-radius: 100px !important;
        padding: 4px 10px !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        cursor: pointer !important;
        margin-left: auto !important;
    }

    /* Category Cards: Minimized Height & Intact Multiline Labels */
    .categories-container {
        display: flex !important;
        gap: 8px !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        padding: 2px 2px 6px 2px !important;
    }
    .category-card {
        background: #ffffff !important;
        border-radius: 16px !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05) !important;
        border: 1.5px solid #f1f5f9 !important;
        padding: 8px 6px !important;
        min-width: 72px !important;
        max-width: 80px !important;
        height: 68px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        gap: 3px !important;
        cursor: pointer !important;
        transition: transform 0.15s, border-color 0.15s, box-shadow 0.15s !important;
        flex-shrink: 0 !important;
        box-sizing: border-box !important;
    }
    .category-card:active {
        transform: scale(0.95) !important;
    }
    .category-card.active {
        border-color: #1e3a8a !important;
        background: #f8fafc !important;
        box-shadow: 0 3px 12px rgba(30, 58, 138, 0.12) !important;
    }
    .category-card .category-icon-box {
        font-size: 18px !important;
        color: #1e3a8a !important;
        line-height: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .category-card .category-card-label {
        font-size: 10px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        white-space: normal !important;
        word-break: normal !important;
        text-align: center !important;
        line-height: 1.15 !important;
        overflow: visible !important;
        text-overflow: clip !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Hide card heart buttons completely */
    .dash-card-heart,
    .spot-card-heart,
    .fav-heart {
        display: none !important;
    }

    /* Hide hero subtitle */
    .dash-hero-subtitle {
        display: none !important;
    }

        /* Compact Height for Empty State Cards */
    .dash-empty-state {
        padding: 12px 14px !important;
        gap: 5px !important;
        border-radius: 16px !important;
    }
    .dash-empty-icon-wrap {
        width: 34px !important;
        height: 34px !important;
        font-size: 14px !important;
        border-radius: 10px !important;
        margin-bottom: 0 !important;
    }
    .dash-empty-title {
        font-size: 13px !important;
        line-height: 1.2 !important;
        margin: 0 !important;
    }
    .dash-empty-desc {
        font-size: 11px !important;
        line-height: 1.3 !important;
        max-width: 320px !important;
        margin: 0 auto !important;
    }
    .dash-empty-btn {
        margin-top: 4px !important;
        padding: 6px 14px !important;
        font-size: 11px !important;
    }

    /* Hide trending carousel dots when empty or single card */
    #trending-container.is-empty ~ #trending-dots,
    #trending-container:empty ~ #trending-dots,
    #trending-container:has(.dash-empty-state) ~ #trending-dots,
    #trending-container:has(.dash-loading-state) ~ #trending-dots,
    #trending-container:has(.dash-filter-empty-state) ~ #trending-dots,
    #trending-container:has(.fav-card:only-child) ~ #trending-dots {
        display: none !important;
    }

@keyframes slideUpSheet {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

    #tourist-sites-filter-modal {
        z-index: 1000005 !important;
    }

    .section-title a {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    /* Filter Chips for Tourist Sites: Outlines & Modern Styling */
    .dash-filter-chips {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        padding: 4px 2px 8px 2px !important;
        margin-bottom: 12px !important;
    }
    .dash-filter-chips::-webkit-scrollbar {
        display: none !important;
    }
    .dash-chip {
        background: #ffffff !important;
        color: #475569 !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 100px !important;
        padding: 6px 14px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
        box-sizing: border-box !important;
        outline: none !important;
    }
    .dash-chip:active {
        transform: scale(0.96) !important;
    }
    .dash-chip.active {
        background: #1e3a8a !important;
        color: #ffffff !important;
        border-color: #1e3a8a !important;
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.28) !important;
    }
    .dash-chip.active i {
        color: #ffffff !important;
    }
    .dash-chip-btn {
        background: #ffffff !important;
        color: #475569 !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 50% !important;
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        min-height: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        flex-shrink: 0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
        box-sizing: border-box !important;
        padding: 0 !important;
        outline: none !important;
    }
    .dash-chip-btn:active {
        transform: scale(0.94) !important;
    }
    .dash-chip-btn.active {
        background: #1e3a8a !important;
        color: #ffffff !important;
        border-color: #1e3a8a !important;
        box-shadow: 0 2px 8px rgba(30, 58, 138, 0.28) !important;
    }
    .dash-chip-btn.active i {
        color: #ffffff !important;
    }
    /* Box-shaped Trending Cards Carousel */
    .fav-card {
        width: 145px !important;
        min-width: 145px !important;
        height: 150px !important;
        border-radius: 18px !important;
    }
    .favorites-row {
        gap: 12px !important;
        padding-top: 8px !important;
        padding-bottom: 14px !important;
        padding-left: calc(50% - 72.5px) !important;
        padding-right: calc(50% - 72.5px) !important;
    }
    .fav-card-overlay {
        padding: 8px 10px !important;
    }
    .fav-card-name {
        font-size: 12px !important;
        line-height: 1.2 !important;
    }

    /* Horizontal Tourist Spot Cards: Fully Scrollable & Responsive */
    #all-spots-container {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow: visible !important;
    }
    .all-spots-grid {
        display: flex !important;
        flex-direction: row !important;
        gap: 12px !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        scroll-snap-type: x mandatory !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        padding: 4px 2px 10px 2px !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        width: 100% !important;
    }
    .all-spots-grid::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .all-spots-grid .all-spot-card-horizontal {
        flex: 0 0 calc(100% - 36px) !important;
        width: calc(100% - 36px) !important;
        min-width: 280px !important;
        max-width: 330px !important;
        scroll-snap-align: start !important;
        margin-bottom: 0 !important;
    }
    .all-spot-card-horizontal {
        background: #ffffff !important;
        border-radius: 18px !important;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06) !important;
        border: 1px solid #e2e8f0 !important;
        padding: 10px !important;
        display: flex !important;
        gap: 10px !important;
        align-items: center !important;
        cursor: pointer !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
        position: relative !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }
    .all-spot-card-horizontal:active {
        transform: scale(0.98) !important;
    }
    .spot-card-thumb-wrap {
        width: 88px !important;
        height: 88px !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        position: relative !important;
        flex-shrink: 0 !important;
        background: #e2e8f0 !important;
    }
    .spot-card-thumb-wrap img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }
    .spot-card-badge {
        position: absolute !important;
        top: 5px !important;
        left: 5px !important;
        z-index: 2 !important;
        padding: 2px 6px !important;
        border-radius: 5px !important;
        font-size: 7.5px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        letter-spacing: 0.4px !important;
        text-transform: uppercase !important;
    }
    .spot-card-body {
        flex: 1 !important;
        min-width: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        height: 88px !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }
    .spot-card-top-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        gap: 6px !important;
        width: 100% !important;
    }
    .spot-card-title {
        margin: 0 !important;
        font-size: 13.5px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        letter-spacing: -0.2px !important;
        flex: 1 !important;
        min-width: 0 !important;
    }
    .spot-card-heart {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #94a3b8 !important;
        font-size: 15px !important;
        cursor: pointer !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
        line-height: 1 !important;
    }
    .spot-card-heart.active,
    .spot-card-heart i.fa-solid {
        color: #ef4444 !important;
    }
    .spot-card-muni {
        font-size: 10.5px !important;
        font-weight: 600 !important;
        color: #64748b !important;
        display: flex !important;
        align-items: center !important;
        gap: 3px !important;
        margin-top: 1px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    .spot-card-muni i {
        color: #1e3a8a !important;
        font-size: 9.5px !important;
    }
    .spot-card-desc {
        font-size: 10px !important;
        color: #64748b !important;
        line-height: 1.3 !important;
        margin: 2px 0 0 0 !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }
    .spot-card-footer {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        gap: 6px !important;
        margin-top: auto !important;
    }
    .spot-card-rating {
        font-size: 10.5px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 3px !important;
        flex-shrink: 0 !important;
    }
    .spot-card-rating i {
        color: #f59e0b !important;
        font-size: 10px !important;
    }
    .spot-card-rating span {
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 9.5px !important;
    }
    .btn-spot-details {
        background: #1e3a8a !important;
        color: #ffffff !important;
        border: none !important;
        outline: none !important;
        border-radius: 100px !important;
        padding: 4px 10px !important;
        font-size: 9.5px !important;
        font-weight: 800 !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25) !important;
    }
    .btn-spot-details:active {
        transform: scale(0.94) !important;
    }
    .mobile-header .header-title {
        transition: opacity 0.32s cubic-bezier(0.16, 1, 0.3, 1), transform 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    </style>

    <script>
    window.getDestImage = function (dest, width) {
        if (!dest) return 'assets/img/no_image.svg';
        if (typeof dest === 'string') {
            if (dest.includes('default-spot') || !dest.trim()) return 'assets/img/no_image.svg';
            return dest;
        }
        var src = dest.photo_url || dest.image || dest.avatar || 'assets/img/no_image.svg';
        if (!src || typeof src !== 'string' || src.includes('default-spot') || !src.trim()) {
            return 'assets/img/no_image.svg';
        }
        return src;
    };

    window.renderFavCard = function(dest) {
        if (!dest) return '';
        const img = window.getDestImage(dest, 600);
        const cStatus = String(dest.classification_status || '').toUpperCase();
        const badgeText = (cStatus === 'EMERGE' || cStatus === 'EMERGING') ? 'EMERGING' : (cStatus === 'POTENTIAL' ? 'POTENTIAL' : 'EXISTING');
        const badgeBg = (cStatus === 'EMERGE' || cStatus === 'EMERGING') ? '#ef4444' : (cStatus === 'POTENTIAL' ? '#10b981' : '#0284c7');
        const encodedDest = encodeURIComponent(JSON.stringify(dest));
        const muni = dest.municipality || dest.location || '';
        const ratingRaw = (dest.rating !== undefined && dest.rating !== null && dest.rating !== '') ? dest.rating : dest.reviews_avg_rating;
        const ratingNum = parseFloat(ratingRaw);
        const hasValidRating = !isNaN(ratingNum) && ratingNum > 0;
        const ratingStr = hasValidRating ? ratingNum.toFixed(1) : '';
        const count = parseInt(dest.reviews_count ?? dest.review_count ?? dest.total_reviews ?? 0, 10);
        const reviewCount = isNaN(count) ? 0 : count;
        const isFav = window.isSpotFavorited ? window.isSpotFavorited(dest.id) : false;

        const ratingSpan = hasValidRating ? `
            <span style="font-size:10px; color:#fbbf24; font-weight:800; display:inline-flex; align-items:center; gap:2px;"><i class="fa-solid fa-star" style="font-size:9px;"></i> ${ratingStr} ${reviewCount > 0 ? `<span style="color:rgba(255,255,255,0.7); font-size:9px;">(${reviewCount})</span>` : ''}</span>
        ` : '<span></span>';

        return `
            <div class="fav-card" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" data-name="${(dest.name || '').replace(/"/g, '&quot;')}" data-municipality="${(dest.municipality || dest.location || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                <span class="spot-card-badge" style="background:${badgeBg}; position:absolute; top:8px; left:8px; z-index:3;">${badgeText}</span>
                <img src="${img}" alt="${(dest.name || '').replace(/"/g, '&quot;')}" loading="lazy" decoding="async" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\'")}', '${(dest.municipality || '').replace(/'/g, "\'")}'); else this.src='assets/img/no_image.svg';">
                <div class="fav-card-overlay">
                    <span class="fav-card-name">${dest.name}</span>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:3px;">
                        ${muni ? `<span style="font-size:10px; color:#38bdf8; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fa-solid fa-location-dot" style="margin-right:3px;"></i>${muni}</span>` : '<span></span>'}
                        ${ratingSpan}
                    </div>
                </div>
            </div>
        `;
    };
    var renderFavCard = window.renderFavCard;
</script>
<script>
        (function() {
            try {
                var t = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                var isReal = Boolean(t && t !== 'guest_token' && t !== 'offline_guest_token' && t.indexOf('guest_') !== 0 && t.indexOf('offline_') !== 0);
                if (isReal) {
                    try {
                        localStorage.removeItem('intan_elyu_guest');
                        localStorage.removeItem('intan_elyu_offline_guest');
                    } catch(err) {}
                }
                var isG = !isReal;
                var st = document.getElementById('dash-guest-guard-style');
                if (st && isG) {
                    st.textContent = '#dash-title-row, #dash-stats-row { display: none !important; } #dash-guest-subtitle { display: flex !important; }';
                }
            } catch(e) {}
        })();
    </script>

        <!-- Scroll Animation Listener for Header Sticky Title -->
    <script>
    (function() {
        function checkDashHeaderScroll() {
            if (!document.body || (document.body.getAttribute('data-view') && document.body.getAttribute('data-view') !== 'dashboard')) return;
            var y = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
            var isScrolled = y > 35;
            document.body.classList.toggle('dash-scrolled', isScrolled);
            var hdr = document.querySelector('.mobile-header');
            if (hdr) hdr.classList.toggle('dash-scrolled', isScrolled);
            var title = document.getElementById('mobile-header-title');
            if (title) {
                if (isScrolled) {
                    title.style.setProperty('opacity', '1', 'important');
                    title.style.setProperty('transform', 'translateY(0) scale(1)', 'important');
                    title.style.setProperty('pointer-events', 'auto', 'important');
                } else {
                    title.style.setProperty('opacity', '0', 'important');
                    title.style.setProperty('transform', 'translateY(12px) scale(0.92)', 'important');
                    title.style.setProperty('pointer-events', 'none', 'important');
                }
            }
        }
        window.addEventListener('scroll', checkDashHeaderScroll, { passive: true });
        checkDashHeaderScroll();
        setTimeout(checkDashHeaderScroll, 60);
        setTimeout(checkDashHeaderScroll, 250);
    })();
    </script>

    <!-- Coastal Hero Banner: Discover La Union -->
    <div class="dash-hero-banner stagger-1" style="position: relative; border-radius: 28px; background: linear-gradient(135deg, #0e2a56 0%, #15386f 50%, #1e4080 100%) !important; padding: 22px 18px 18px 18px; overflow: hidden; margin-bottom: 14px; box-shadow: 0 8px 24px rgba(14, 42, 86, 0.25); display: flex; justify-content: space-between;">
        <div class="dash-hero-content">
            <span class="dash-hero-eyebrow">Discover</span>
            <h1 class="dash-hero-title">La Union</h1>
            
            
            <!-- Compact Glass User Profile Strip -->
            <div class="dash-user-strip" onclick="navigateTo('profile')" title="View Profile">
                <div class="dash-user-avatar-wrap">
                    <img id="dash-avatar" src="https://ui-avatars.com/api/?name=Tourist&background=007AFF&color=fff&rounded=true&bold=true&size=128" alt="Avatar">
                </div>
                <div class="dash-user-details">
                    <span class="dash-user-name" id="dash-name">Hi, Explorer! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:12px; margin-left:2px;"></i></span>
                    <div id="dash-guest-subtitle" style="display:none; align-items:center; gap:4px; font-size:10.5px; color:#93c5fd; font-weight:700;">
                        <i class="fa-solid fa-user-clock"></i> Guest Explorer &bull; Sign in
                    </div>
                </div>
                <!-- Hidden stubs for JS compatibility -->
                <div id="dash-title-row" style="display:none !important;">
                    <span id="dash-title" style="display:none !important;"></span>
                    <span id="dash-status-badge" style="display:none !important;"></span>
                </div>
            </div>
            
            <!-- 3 Stats: Places, Points, Rank (aligned with Discover text) -->
            <div class="dash-mini-stats" id="dash-stats-row">
                <div class="dash-mini-stat" onclick="event.stopPropagation(); navigateTo('itinerary');" title="Places Visited">
                    <span class="dash-mini-val" id="dash-stat-places">—</span>
                    <span class="dash-mini-lbl">Places</span>
                </div>
                <div class="dash-mini-stat" onclick="event.stopPropagation(); navigateTo('leaderboard');" title="Reward Points">
                    <span class="dash-mini-val" id="dash-stat-points">—</span>
                    <span class="dash-mini-lbl">Points</span>
                </div>
                <div class="dash-mini-stat" onclick="event.stopPropagation(); navigateTo('leaderboard');" title="Current Leaderboard Rank">
                    <span class="dash-mini-val" id="dash-stat-rank">—</span>
                    <span class="dash-mini-lbl">Rank</span>
                </div>
            </div>
        </div>
        
        <!-- Coastal Lighthouse Artwork on Right -->
        <div class="dash-hero-art" aria-hidden="true" style="position: absolute; right: -4px; bottom: -6px; width: 140px; height: 160px; pointer-events: none; z-index: 1;">
            <svg class="dash-hero-svg" viewBox="0 0 160 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Distant Mountain silhouette -->
                <path d="M-10 130 L40 65 L90 120 L130 50 L180 140 L180 180 L-10 180 Z" fill="rgba(255, 255, 255, 0.08)" />
                <path d="M20 140 L70 90 L110 135 L145 80 L185 145 L185 180 L20 180 Z" fill="rgba(255, 255, 255, 0.12)" />
                <!-- Seagulls -->
                <path d="M30 45 Q 38 38 46 45 Q 54 38 62 45" stroke="rgba(255,255,255,0.7)" stroke-width="1.8" stroke-linecap="round" fill="none" />
                <path d="M70 30 Q 76 24 82 30 Q 88 24 94 30" stroke="rgba(255,255,255,0.55)" stroke-width="1.5" stroke-linecap="round" fill="none" />
                <!-- Rocky Shoreline -->
                <path d="M60 180 C 80 150 110 165 130 148 C 145 136 160 145 170 180 Z" fill="#172554" />
                <path d="M90 180 C 105 160 125 168 140 155 C 150 146 160 152 170 180 Z" fill="#1e293b" />
                <!-- White Lighthouse -->
                <polygon points="122,154 134,154 131,88 125,88" fill="#f8fafc" />
                <polygon points="123,136 133,136 132,122 124,122" fill="#0284c7" />
                <rect x="123" y="80" width="10" height="8" rx="1.5" fill="#f8fafc" />
                <polygon points="121,80 135,80 128,70" fill="#0284c7" />
                <circle cx="128" cy="69" r="2" fill="#fbbf24" />
                <circle cx="128" cy="84" r="2.5" fill="#fef08a" />
                <!-- Subtle Light Beam -->
                <polygon points="128,84 0,35 0,85" fill="url(#heroLightBeam)" opacity="0.22" />
                <defs>
                    <linearGradient id="heroLightBeam" x1="128" y1="84" x2="0" y2="60" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#fef08a" stop-opacity="0.8"/>
                        <stop offset="1" stop-color="#38bdf8" stop-opacity="0"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
    </div>

    <!-- Live Search Container -->
    <div class="dash-search-container stagger-1" style="position: relative; z-index: 100;">
        <div class="dash-search-box">
            <i class="fa-solid fa-magnifying-glass dash-search-icon"></i>
            <input type="text" id="dash-search-input" class="dash-search-input" placeholder="Search tourist sites, beaches, places..." autocomplete="off" oninput="window.handleDashSearch(this.value)" onfocus="window.handleDashSearch(this.value)">
            <button type="button" id="dash-search-clear" class="dash-search-clear" onclick="window.clearDashSearch()" style="display:none;" aria-label="Clear Search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <!-- Floating Instant Search Results Dropdown -->
        <div id="dash-search-results" class="dash-search-dropdown" style="display:none; position:absolute; top:calc(100% + 8px); left:0; right:0; background:#ffffff; border-radius:18px; border:1.5px solid #e2e8f0; box-shadow:0 12px 30px rgba(15,23,42,0.14); max-height:320px; overflow-y:auto; z-index:1000; padding:6px; -webkit-overflow-scrolling:touch;"></div>
    </div>

    <!-- Weather Widget (Royal Blue Mockup Theme) -->
    <div class="weather-card stagger-2" onclick="window.openWeatherModal()" style="cursor: pointer; position: relative;" title="Click for 5-Day Weather Forecast">
        <div class="weather-top-row">
            <div class="weather-loc-badge">
                <i class="fa-solid fa-location-dot"></i>
                <span id="weather-loc">San Fernando, La Union</span>
            </div>
        </div>
        <div class="weather-mid-row">
            <div class="weather-left-info">
                <div class="weather-temp-wrap">
                    <span class="weather-temp-val" id="weather-temp">31°C</span>
                    <span class="weather-icon-badge" id="weather-icon">☁️</span>
                </div>
                <div class="weather-desc-main" id="weather-desc">Overcast</div>
                <div class="weather-tip-msg">Good for outdoor activities</div>
            </div>
            <div class="weather-art-wrap" aria-hidden="true">
                <div class="weather-3d-cloud">
                    <svg viewBox="0 0 120 80" width="95" height="62" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="45" cy="45" r="24" fill="rgba(255, 255, 255, 0.95)" />
                        <circle cx="72" cy="40" r="30" fill="rgba(255, 255, 255, 0.95)" />
                        <circle cx="92" cy="50" r="18" fill="rgba(255, 255, 255, 0.95)" />
                        <rect x="25" y="44" width="80" height="24" rx="12" fill="rgba(255, 255, 255, 0.95)" />
                        <path d="M32 64 Q 60 70 95 64" stroke="rgba(15, 23, 42, 0.12)" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="weather-bottom-row">
            <div class="weather-stat-pill">
                <i class="fa-solid fa-droplet"></i>
                <span id="weather-humidity">68%</span>
            </div>
            <div class="weather-stat-pill">
                <i class="fa-solid fa-wind"></i>
                <span id="weather-wind">13 km/h</span>
            </div>
            <div class="weather-stat-pill">
                <i class="fa-solid fa-sun"></i>
                <span>UV <strong id="weather-uv">8</strong></span>
            </div>
            <div class="weather-forecast-pill">
                <span>5-Day Forecast</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i>
            </div>
        </div>
    </div>


    <!-- Weather Forecast Open Sheet / Modal -->
    <div id="weather-modal" onclick="if(event.target===this) window.closeWeatherModal()"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.72); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:100005 !important; justify-content:center; align-items:flex-end; box-sizing:border-box;">
        <div class="weather-modal-card"
            style="background: #ffffff !important; border: none !important; outline: none !important; border-radius: 28px 28px 0 0; width: 100%; max-width: 480px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 -12px 40px rgba(10, 25, 60, 0.45); animation: weatherSheetUp 0.32s cubic-bezier(0.16, 1, 0.3, 1); box-sizing: border-box;">
            
            <!-- Modal Top Header Banner (Royal Blue) with Exit Button on Right -->
            <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 16px 20px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
                <div style="text-align: left; min-width: 0;">
                    <h3 style="margin: 0; font-size: 16.5px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px; letter-spacing: -0.2px;">
                        <i class="fa-solid fa-cloud-sun" style="color: #00f2fe; font-size: 16px;"></i> Weather Forecast
                    </h3>
                    <p style="margin: 3px 0 0 0; font-size: 11.5px; color: rgba(255, 255, 255, 0.85); font-weight: 600; display: flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-location-dot" style="color: #38bdf8; font-size: 10px;"></i>
                        <span id="modal-weather-loc">San Fernando, La Union</span>
                    </p>
                </div>
                <button type="button" onclick="window.closeWeatherModal()" aria-label="Close"
                    style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: 12px; transition: transform 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 14px;"></i>
                </button>
            </div>

            <!-- Middle Body Area (Pure White Background) -->
            <div class="hide-scrollbar"
                style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 20px 20px max(24px, env(safe-area-inset-bottom)) 20px; color: #0f172a !important; box-sizing: border-box;">
                
                <!-- Current Detailed Weather Card (Sky Blue Gradient Card in White Middle) -->
                <div
                    style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important; border: 1.5px solid #bae6fd; border-radius: 20px; padding: 18px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.08);">
                    <div>
                        <div style="font-size: 40px; font-weight: 900; color: #0369a1; letter-spacing: -1.5px; line-height: 1;" id="modal-temp">29°C</div>
                        <div style="font-size: 14.5px; font-weight: 800; color: #0284c7; margin-top: 6px;" id="modal-condition">Partly Cloudy</div>
                        <div style="font-size: 11.5px; color: #64748b; font-weight: 600; margin-top: 4px; line-height: 1.4;" id="modal-feels">Feels like 31°C · Humidity 75% · Wind 12km/h</div>
                    </div>
                    <div style="text-align: center; flex-shrink: 0; margin-left: 12px;">
                        <div style="font-size: 54px; line-height: 1;" id="modal-icon">⛅</div>
                    </div>
                </div>

                <!-- 5-Day Forecast Grid Title -->
                <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 7px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-calendar-days" style="color: #0284c7;"></i> 5-Day Forecast
                </h4>

                <!-- 5-Day Forecast Grid -->
                <div id="weather-forecast-container" style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 13px; font-weight: 600;">
                        <i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px; color: #0284c7;"></i> Loading 5-day forecast...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gamification Panel -->
    <div class="gamezone-banner stagger-2" onclick="navigateTo('puzzles')"
        style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 24px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important; margin-top: 16px; cursor: pointer; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; padding: 16px 20px; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0;">
            <div style="font-size: 32px; filter: none; flex-shrink: 0;">🧩</div>
            <div style="flex: 1; min-width: 0;">
                <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -0.2px;">
                    GameZone</h4>
                <p style="margin: 0; font-size: 12px; color: rgba(255, 255, 255, 0.95); font-weight: 600; line-height: 1.3;">Play fun
                    mini-games to earn discount vouchers! 🎁</p>
            </div>
        </div>
        <div
            style="width: 38px; height: 38px; min-width: 38px; border-radius: 50%; background: #ffffff !important; display: flex; align-items: center; justify-content: center; color: #1e3a8a !important; font-size: 14px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; margin-left: auto;">
            <i class="fa-solid fa-play" style="color: #1e3a8a !important; margin-left: 2px;"></i>
        </div>
    </div>

        <!-- Explore Categories Section -->
    <div class="dash-section stagger-2" style="margin-top: 22px;">
        <div class="section-title">
            <h3>Explore Categories</h3>
        </div>
        <div class="categories-container" id="dash-categories-list">
            <div class="category-card active" onclick="window.filterCategoryDash('All', this)">
                <div class="category-icon-box"><i class="fa-solid fa-compass"></i></div>
                <div class="category-card-label">All Spots</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Beach', this)">
                <div class="category-icon-box"><i class="fa-solid fa-water"></i></div>
                <div class="category-card-label">Beaches</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Nature', this)">
                <div class="category-icon-box"><i class="fa-solid fa-mountain"></i></div>
                <div class="category-card-label">Nature &<br>Park</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Heritage', this)">
                <div class="category-icon-box"><i class="fa-solid fa-landmark-dome"></i></div>
                <div class="category-card-label">Cultural &<br>Heritage</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Food & Dining', this)">
                <div class="category-icon-box"><i class="fa-solid fa-utensils"></i></div>
                <div class="category-card-label">Food &<br>Dining</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Mountains', this)">
                <div class="category-icon-box"><i class="fa-solid fa-mountain-sun"></i></div>
                <div class="category-card-label">Mountains</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Lakes', this)">
                <div class="category-icon-box"><i class="fa-solid fa-droplet"></i></div>
                <div class="category-card-label">Lakes &<br>Falls</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Farm', this)">
                <div class="category-icon-box"><i class="fa-solid fa-seedling"></i></div>
                <div class="category-card-label">Farms</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Nightlife', this)">
                <div class="category-icon-box"><i class="fa-solid fa-martini-glass-citrus"></i></div>
                <div class="category-card-label">Nightlife</div>
            </div>
        </div>
    </div>

    <!-- Trending Sites -->
    <div class="dash-section stagger-2">
        <div class="section-title">
            <h3>Trending Sites</h3>
            <a href="javascript:void(0);"
                onclick="localStorage.setItem('intan_elyu_explore_mode', 'trending'); navigateTo('trending');">See
                All</a>
        </div>
        <div class="favorites-row is-empty" id="trending-container">
            <div class="dash-loading-state">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Loading trending sites...</span>
            </div>
        </div>
        <div class="dash-carousel-dots" id="trending-dots" aria-hidden="true">
            <span class="dash-dot active"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
            <span class="dash-dot"></span>
        </div>
    </div>

    <!-- Tourist Sites -->
    <div class="dash-section stagger-2" id="all-spots-section" style="margin-top: 24px;">
        <div class="section-title">
            <h3>Tourist Sites</h3>
            <a href="javascript:void(0);" onclick="window.navigateToTouristSitesPage()" style="white-space:nowrap !important; flex-shrink:0 !important;">See All</a>
        </div>
        <!-- Horizontal Filter Chips Row (Mockup Screen 2) -->
        <div class="dash-filter-chips">
            <button type="button" class="dash-chip active" onclick="window.filterTouristSitesChip('all', this)">All</button>
            <button type="button" class="dash-chip" onclick="window.filterTouristSitesChip('top', this)">Top Rated</button>
            <button type="button" class="dash-chip" onclick="window.filterTouristSitesChip('near', this)"><i class="fa-solid fa-location-dot"></i> Near Me</button>
            <button type="button" class="dash-chip-btn" id="btn-tourist-filter" onclick="window.openTouristSitesFilterModal()" title="Filter Tourist Sites"><i class="fa-solid fa-sliders"></i></button>
        </div>

        <div id="all-spots-container">
            <div class="dash-loading-state">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Loading tourist destinations...</span>
            </div>
        </div>

    </div>



    <!-- My Saved Trips Preview -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>My Saved Trips</h3>
            <a href="javascript:void(0);" onclick="navigateTo('saved_trips')">Open Saved Trips</a>
        </div>

        <div id="saved-trips-container">
            <div class="dash-loading-state">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Loading Saved Trips...</span>
            </div>
        </div>
    </div>

    <!-- Favorites / Saved Places -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>Saved Places</h3>
            <a href="javascript:void(0);" onclick="navigateTo('saved_places')">See All</a>
        </div>
        <div class="favorites-row is-empty" id="saved-places-container">
            <div class="dash-loading-state">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Loading Saved Places...</span>
            </div>
        </div>
    </div>

    <!-- Recommended For You -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>Recommended For You</h3>
        </div>
        <div id="recommended-container">
            <div class="dash-loading-state">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Loading Recommendations...</span>
            </div>
        </div>
    </div>


</div>


<!-- Tourist Sites Filter Bottom Sheet Modal -->
<div id="tourist-sites-filter-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.65); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:1000005 !important; justify-content:center; align-items:flex-end;" onclick="if(event.target===this) window.closeTouristSitesFilterModal();">
    <div style="background:#ffffff; width:100%; max-width:480px; border-radius:24px 24px 0 0; padding:20px 20px max(28px, env(safe-area-inset-bottom, 28px)) 20px; box-shadow:0 -8px 30px rgba(0,0,0,0.25); animation:slideUpSheet 0.25s ease-out; box-sizing:border-box; position:relative; z-index:1000006;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:32px; height:32px; border-radius:10px; background:rgba(30,58,138,0.1); color:#1e3a8a; display:flex; align-items:center; justify-content:center; font-size:14px;">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <h3 style="margin:0; font-size:17px; font-weight:800; color:#0f172a;">Filter Tourist Sites</h3>
            </div>
            <button type="button" onclick="window.closeTouristSitesFilterModal()" style="background:none; border:none; outline:none; font-size:22px; color:#64748b; cursor:pointer; padding:4px 8px; line-height:1;">&times;</button>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-size:11.5px; font-weight:800; color:#334155; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">Municipality / Location</label>
            <select id="dash-filter-muni" style="width:100%; padding:10px 14px; border-radius:12px; border:1.5px solid #cbd5e1; background:#f8fafc; font-size:13.5px; font-weight:600; color:#0f172a; outline:none;">
                <option value="All">All Municipalities in La Union</option>
                <option value="near">📍 Near Me (Within 1km of Location)</option>
                <option value="San Fernando">San Fernando City</option>
                <option value="San Juan">San Juan (Surfing Capital)</option>
                <option value="Bauang">Bauang (Grape Farms)</option>
                <option value="Bacnotan">Bacnotan</option>
                <option value="Balaoan">Balaoan</option>
                <option value="Luna">Luna (Pebble Beach)</option>
                <option value="Bangar">Bangar (Abel Weaving)</option>
                <option value="Sudipen">Sudipen</option>
                <option value="Santol">Santol</option>
                <option value="San Gabriel">San Gabriel (Tangadan Falls)</option>
                <option value="Bagulin">Bagulin</option>
                <option value="Burgos">Burgos</option>
                <option value="Naguilian">Naguilian</option>
                <option value="Caba">Caba</option>
                <option value="Aringay">Aringay</option>
                <option value="Agoo">Agoo (Basilica)</option>
                <option value="Tubao">Tubao</option>
                <option value="Pugo">Pugo (Eco Adventure)</option>
                <option value="Santo Tomas">Santo Tomas</option>
                <option value="Rosario">Rosario</option>
            </select>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-size:11.5px; font-weight:800; color:#334155; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">Classification</label>
            <select id="dash-filter-status" style="width:100%; padding:10px 14px; border-radius:12px; border:1.5px solid #cbd5e1; background:#f8fafc; font-size:13.5px; font-weight:600; color:#0f172a; outline:none;">
                <option value="All">All Classifications</option>
                <option value="EXIST">Existing Sites (Established)</option>
                <option value="EMERGE">Emerging Sites (Popular & Growing)</option>
                <option value="POTENTIAL">Potential Sites (Hidden Gems)</option>
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:11.5px; font-weight:800; color:#334155; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">Sort Order</label>
            <select id="dash-filter-sort" style="width:100%; padding:10px 14px; border-radius:12px; border:1.5px solid #cbd5e1; background:#f8fafc; font-size:13.5px; font-weight:600; color:#0f172a; outline:none;">
                <option value="default">Default (Featured)</option>
                <option value="distance_asc">📍 Nearest First (Within 1km)</option>
                <option value="rating_desc">Highest Rated (★ 5.0 → 1.0)</option>
                <option value="visits_desc">Most Visited / Popular</option>
                <option value="name_asc">Alphabetical (A → Z)</option>
            </select>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="button" onclick="window.resetTouristSitesFilterModal()" style="flex:1; padding:12px; border-radius:12px; border:1.5px solid #cbd5e1; background:#ffffff; color:#475569; font-size:13px; font-weight:700; cursor:pointer;">
                Reset
            </button>
            <button type="button" onclick="window.applyTouristSitesFilterModal()" style="flex:2; padding:12px; border-radius:12px; border:none; background:#1e3a8a; color:#ffffff; font-size:13px; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(30,58,138,0.25);">
                Apply Filters
            </button>
        </div>
    </div>
</div>

<script>
window.openTouristSitesFilterModal = function() {
    const modal = document.getElementById('tourist-sites-filter-modal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            const existing = document.body.querySelector(':scope > #tourist-sites-filter-modal');
            if (existing && existing !== modal) {
                existing.remove();
            }
            document.body.appendChild(modal);
        }

        // Sync modal form with active filter state and active Near Me chip
        const fState = window._currentTouristFilterState || { muni: 'All', status: 'All', sort: 'default' };
        const muniEl = document.getElementById('dash-filter-muni');
        const statusEl = document.getElementById('dash-filter-status');
        const sortEl = document.getElementById('dash-filter-sort');

        if (muniEl) muniEl.value = fState.muni || 'All';
        if (statusEl) statusEl.value = fState.status || 'All';
        if (sortEl) sortEl.value = fState.sort || 'default';

        // Connect Near Me chip to modal form if Near Me chip is active
        if (window._currentTouristSitesChip === 'near') {
            if (muniEl && (fState.muni === 'All' || fState.muni === 'near')) {
                muniEl.value = 'near';
            } else if (sortEl && fState.sort === 'default') {
                sortEl.value = 'distance_asc';
            }
        }

        modal.style.display = 'flex';
        const bottomNav = document.getElementById('bottom-navigation');
        if (bottomNav) bottomNav.classList.add('nav-hidden');
    }
};

window.closeTouristSitesFilterModal = function() {
    const modal = document.getElementById('tourist-sites-filter-modal');
    if (modal) {
        modal.style.display = 'none';
        const bottomNav = document.getElementById('bottom-navigation');
        if (bottomNav) bottomNav.classList.remove('nav-hidden');
    }
};

window.resetTouristSitesFilterModal = function() {
    const muniEl = document.getElementById('dash-filter-muni');
    const statusEl = document.getElementById('dash-filter-status');
    const sortEl = document.getElementById('dash-filter-sort');
    if (muniEl) muniEl.value = 'All';
    if (statusEl) statusEl.value = 'All';
    if (sortEl) sortEl.value = 'default';
    window._currentTouristFilterState = { muni: 'All', status: 'All', sort: 'default' };
    const filterBtn = document.getElementById('btn-tourist-filter');
    if (filterBtn) {
        filterBtn.classList.remove('active');
        filterBtn.style.background = '';
        filterBtn.style.color = '';
        filterBtn.style.borderColor = '';
    }
    window.closeTouristSitesFilterModal();
    window.filterTouristSitesChip('all');
};

window.getFilteredTouristSpots = function() {
    if (!window.allTouristSpots || window.allTouristSpots.length === 0) return [];
    let spots = [...window.allTouristSpots];
    const filterState = window._currentTouristFilterState || {
        muni: document.getElementById('dash-filter-muni')?.value || 'All',
        status: document.getElementById('dash-filter-status')?.value || 'All',
        sort: document.getElementById('dash-filter-sort')?.value || 'default'
    };

    // Filter by Municipality (skip string matching if 'near' or 'All' - distance filtering handles 'near')
    if (filterState.muni && filterState.muni !== 'All' && filterState.muni !== 'near') {
        const needle = filterState.muni.toLowerCase().replace(' city', '').trim();
        spots = spots.filter(s => {
            const m = (s.municipality || s.location || '').toLowerCase();
            return m.includes(needle);
        });
    }

    // Filter by Classification Status
    if (filterState.status && filterState.status !== 'All') {
        const statusVal = filterState.status;
        spots = spots.filter(s => {
            const st = (s.classification_status || '').toUpperCase();
            return st === statusVal || (statusVal === 'EXIST' && st.includes('EXIST')) || (statusVal === 'EMERGE' && st.includes('EMERG')) || (statusVal === 'POTENTIAL' && st.includes('POTENTIAL'));
        });
    }

    // Sort order (distance_asc handled in runNearMeFilter)
    if (filterState.sort === 'rating_desc') {
        spots.sort((a, b) => parseFloat(b.rating || b.reviews_avg_rating || 0) - parseFloat(a.rating || a.reviews_avg_rating || 0));
    } else if (filterState.sort === 'visits_desc') {
        spots.sort((a, b) => parseInt(b.visits || 0, 10) - parseInt(a.visits || 0, 10));
    } else if (filterState.sort === 'name_asc') {
        spots.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    }

    return spots;
};

window.applyTouristSitesFilterModal = function() {
    const muniVal = document.getElementById('dash-filter-muni')?.value || 'All';
    const statusVal = document.getElementById('dash-filter-status')?.value || 'All';
    const sortVal = document.getElementById('dash-filter-sort')?.value || 'default';
    const filterBtn = document.getElementById('btn-tourist-filter');

    window._currentTouristFilterState = { muni: muniVal, status: statusVal, sort: sortVal };

    const isNearSelected = (muniVal === 'near' || sortVal === 'distance_asc');
    const hasFilter = Boolean(
        (muniVal !== 'All' && muniVal !== 'near') ||
        statusVal !== 'All' ||
        (sortVal !== 'default' && sortVal !== 'distance_asc')
    );

    if (filterBtn) {
        filterBtn.classList.toggle('active', hasFilter);
        if (!hasFilter) {
            filterBtn.style.background = '';
            filterBtn.style.color = '';
            filterBtn.style.borderColor = '';
        } else {
            filterBtn.style.background = '#1e3a8a';
            filterBtn.style.color = '#ffffff';
            filterBtn.style.borderColor = '#1e3a8a';
        }
    }

    window.closeTouristSitesFilterModal();

    // If Near Me is selected in the modal, route through Near Me chip
    if (isNearSelected) {
        window.filterTouristSitesChip('near');
        return;
    }

    // If Near Me was previously active, but user selected a non-Near Me municipality (like 'All'), switch to 'all' chip
    const activeChip = window._currentTouristSitesChip || 'all';
    if (activeChip === 'near') {
        window.filterTouristSitesChip('all');
        return;
    }

    if (activeChip === 'top') {
        window.filterTouristSitesChip('top');
        return;
    }

    window.filterTouristSitesChip('all');
};
</script>

<!-- Delete Trip Confirmation Modal -->
<div id="delete-trip-modal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center;">
    <div
        style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%); border: none; outline:none; border-radius:24px; padding:28px 24px; width:90%; max-width:360px; box-shadow: 0 12px 30px rgba(10, 25, 60, 0.4); text-align:center;">
        <i class="fa-solid fa-trash-can" style="font-size:32px; color:#ffffff; margin-bottom:10px; display:block;"></i>
        <h3 style="margin:0 0 8px; color:#ffffff; font-size:20px; font-weight:800;">Delete Saved Trip?</h3>
        <p id="delete-trip-title-text"
            style="font-size:13px; color:rgba(255, 255, 255, 0.95); margin-bottom:22px; line-height:1.5;">Are you sure
            you want to delete this trip? All saved itinerary items will be removed.</p>

        <div style="display:flex; gap:10px;">
            <button type="button"
                style="flex:1; padding:13px; border-radius:14px; border:none; outline:none; background:rgba(255,255,255,0.22); color:#ffffff; font-size:13px; font-weight:700; cursor:pointer;"
                onclick="window.closeDeleteTripModal()">
                Cancel
            </button>
            <button type="button" id="btn-confirm-delete-trip"
                style="flex:1; padding:13px; font-size:14px; font-weight:800; background:#ef4444; border:none; outline:none; color:#ffffff; border-radius:14px; box-shadow:none; cursor:pointer;"
                onclick="window.executeConfirmDeleteTrip()">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
    window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;

    function getDashBackendUrl() {
        if (typeof window.getBackendUrl === 'function') {
            const u = window.getBackendUrl();
            if (u) return u.replace(/\/+$/, '');
        }
        if (window.backendUrl) return window.backendUrl.replace(/\/+$/, '');
        if (window.BACKEND_URL) return window.BACKEND_URL.replace(/\/+$/, '');
        return 'https://api.intan-elyu.online';
    }
    window.getDashBackendUrl = getDashBackendUrl;

    function updateDashboardOfflineStatus() {
        var pill = document.getElementById('dash-offline-pill');
        var isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
        if (pill) {
            pill.style.setProperty('display', isOffline ? 'flex' : 'none', 'important');
            var span = pill.querySelector('span');
            if (span && isOffline) {
                span.innerHTML = 'Offline Mode &bull; Showing Saved Guides & Local GPS';
            }
        }
        if (!isOffline) {
            try {
                localStorage.removeItem('intan_elyu_offline_mode');
                localStorage.removeItem('intan_elyu_offline_guest');
            } catch(e) {}
        }
    }
    window.addEventListener('online', () => {
        updateDashboardOfflineStatus();
        if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
    });
    window.addEventListener('offline', updateDashboardOfflineStatus);
    window.addEventListener('appOnline', () => {
        updateDashboardOfflineStatus();
        if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
    });
    window.addEventListener('appOffline', updateDashboardOfflineStatus);
    setTimeout(updateDashboardOfflineStatus, 100);

    window.filterCategoryDash = function (cat, el) {
        document.querySelectorAll('#dash-categories-list .category-card').forEach(card => card.classList.remove('active'));
        if (el) el.classList.add('active');
        if (cat && cat !== 'All') {
            localStorage.setItem('intan_elyu_filter_category', cat);
        } else {
            localStorage.removeItem('intan_elyu_filter_category');
        }

        const matchesCategory = (cardCategory, cardName, cardMuni, targetCat) => {
            if (!targetCat || targetCat === 'All') return true;
            const c = (cardCategory || '').toLowerCase().trim();
            const n = (cardName || '').toLowerCase().trim();
            const m = (cardMuni || '').toLowerCase().trim();
            const combined = `${c} ${n} ${m}`;
            const t = targetCat.toLowerCase().trim();

            // Direct or token match
            if (c && (c === t || c.includes(t) || (c.length >= 3 && t.includes(c)))) return true;
            if (c && ` ${c} `.includes(` ${t} `)) return true;

            // Beach, Coastal & Surfing
            if (t.includes('beach') || t.includes('surf') || t.includes('coastal') || t.includes('island')) {
                return combined.includes('beach') || combined.includes('surf') || combined.includes('coastal') ||
                    combined.includes('island') || combined.includes('seascape') || combined.includes('water sports');
            }

            // Nature & Parks
            if (t.includes('nature') || t.includes('park')) {
                return c.includes('nature') || c.includes('park') || combined.includes('park') ||
                    combined.includes('plaza') || combined.includes('agro-forestry') || combined.includes('tree') ||
                    combined.includes('mangrove') || combined.includes('lagoon') || combined.includes('baywalk');
            }

            // Mountains & Hiking
            if (t.includes('mountain') || t.includes('hiking') || t.includes('trail') || t.includes('view')) {
                return c.includes('mountain') || c.includes('hiking') || combined.includes('trail') ||
                    combined.includes('peak') || combined.includes('view deck') || combined.includes('viewdeck') ||
                    combined.includes('terrace') || combined.includes('mt.') || combined.includes('mountain');
            }

            // Lakes, Falls & Waterways
            if (t.includes('lake') || t.includes('fall') || t.includes('water') || t.includes('river')) {
                return c.includes('waterfall') || c.includes('river') || c.includes('lake') ||
                    combined.includes('fall') || combined.includes('river') || combined.includes('lake') ||
                    combined.includes('dam') || combined.includes('spring');
            }

            // Heritage, Cultural, Historical & Monuments
            if (t.includes('heritage') || t.includes('cultural') || t.includes('historical') || t.includes('monument') || t.includes('museum')) {
                return c.includes('cultural') || c.includes('heritage') || c.includes('historical') ||
                    c.includes('monument') || c.includes('museum') || combined.includes('watchtower') ||
                    combined.includes('tunnel') || combined.includes('marker') || combined.includes('station') ||
                    combined.includes('memorial') || combined.includes('ancestral') || combined.includes('church') ||
                    combined.includes('shrine') || combined.includes('parish') || combined.includes('basilica');
            }

            // Food & Dining
            if (t.includes('food') || t.includes('dining') || t.includes('restaurant')) {
                return c.includes('food') || combined.includes('restaurant') || combined.includes('seafood') ||
                    combined.includes('dining') || combined.includes('eatery') || combined.includes('cafe') ||
                    combined.includes('bistro') || combined.includes('grill');
            }

            // Arts & Crafts
            if (t.includes('art') || t.includes('craft') || t.includes('weaving')) {
                return c.includes('arts') || combined.includes('weaving') || combined.includes('pottery') ||
                    combined.includes('gallery') || combined.includes('craft') || combined.includes('paper');
            }

            // Farms & Agriculture
            if (t.includes('farm') || t.includes('agro') || t.includes('plant')) {
                return c.includes('farm') || combined.includes('plantation') || combined.includes('grapes') ||
                    combined.includes('mushroom') || combined.includes('fishery') || combined.includes('agri');
            }

            // Nightlife (Strict matching for authentic nightlife spots only)
            if (t === 'nightlife' || t.includes('nightlife')) {
                return c.includes('nightlife') || combined.includes('nightlife') ||
                    /\b(nightclub|night club|pub|pubs|disco|discotheque|restobar|resto-bar|sports bar|comedy bar|karaoke bar|lounge bar)\b/i.test(combined);
            }

            // Recreation & Resorts (Separated from Nightlife so resorts & recreation do not pollute nightlife)
            if (t.includes('resort') || t.includes('recreation')) {
                return c.includes('resort') || c.includes('recreation') || combined.includes('resort') || combined.includes('recreation');
            }

            return false;
        };

        window.matchesCategory = matchesCategory;
        window.currentDashCategory = cat;

        const renderEmpty = (container, emptyMsg) => {
            if (!container) return;
            container.classList.add('is-empty');
            container.style.paddingLeft = '0';
            container.style.paddingRight = '0';
            container.style.marginLeft = '0';
            container.style.marginRight = '0';
            container.innerHTML = `
                <div class="dash-filter-empty-state dash-empty-state">
                    <div class="dash-empty-icon-wrap">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    <div class="dash-empty-title">${emptyMsg}</div>
                    <div class="dash-empty-desc">Try selecting another category or tap All to view all destinations.</div>
                </div>
            `;
            if (container.id === 'trending-container') {
                const dotsWrap = document.getElementById('trending-dots');
                if (dotsWrap) dotsWrap.style.display = 'none';
            }
        };

        const renderFavCard = window.renderFavCard;

        // 1. Filter Trending Section
        const trendingContainer = document.getElementById('trending-container');
        if (trendingContainer && window.dashTrending) {
            let list = [];
            if (cat === 'All') {
                list = window.dashTrending.slice(0, 10);
            } else {
                list = window.dashTrending.filter(d => matchesCategory(d.category, d.name, d.municipality || d.location, cat));
            }
            if (list.length > 0) {
                trendingContainer.classList.remove('is-empty');
                trendingContainer.style.paddingLeft = '';
                trendingContainer.style.paddingRight = '';
                trendingContainer.style.marginLeft = '';
                trendingContainer.style.marginRight = '';
                trendingContainer.innerHTML = list.map(window.renderFavCard || renderFavCard).join('');
                const dotsWrapEl = document.getElementById('trending-dots');
                if (dotsWrapEl) dotsWrapEl.style.display = (list.length > 1) ? 'flex' : 'none';
                window.initLoopingFocusCarousel('trending-container');
            } else {
                renderEmpty(trendingContainer, cat === 'Nightlife' ? 'No Nightlife Tourist Sites Yet' : 'No trending sites in this category.');
            }
        }

        // 2. Filter Recommended Section
        const recContainer = document.getElementById('recommended-container');
        if (recContainer && window.dashRecommended && typeof window.buildRecommendedItem === 'function') {
            if (cat === 'All') {
                recContainer.classList.remove('is-empty');
                recContainer.innerHTML = '';
                const INITIAL_SHOW = 2;
                const allDests = window.dashRecommended;
                allDests.slice(0, INITIAL_SHOW).forEach(dest => {
                    recContainer.innerHTML += window.buildRecommendedItem(dest);
                });
                if (allDests.length > INITIAL_SHOW) {
                    const extras = allDests.slice(INITIAL_SHOW);
                    const extraHtml = extras.map(dest => window.buildRecommendedItem(dest)).join('');
                    recContainer.innerHTML += `
                        <div id="rec-extras" style="overflow:hidden; max-height:0; transition: max-height 0.4s ease;">
                            ${extraHtml}
                        </div>
                        <button id="btn-view-more-rec"
                            onclick="window.toggleRecommendedMore()"
                            style="width:100%; margin-top:10px; padding:12px; border-radius:14px; border: none; outline:none; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%); color:#ffffff; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.25); transition: all 0.2s;">
                            <i class="fa-solid fa-chevron-down" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i>
                            View ${extras.length} More
                        </button>
                    `;
                }
            } else {
                const list = window.dashRecommended.filter(d => matchesCategory(d.category, d.name, d.municipality || d.location, cat));
                if (list.length > 0) {
                    recContainer.classList.remove('is-empty');
                    recContainer.innerHTML = list.map(dest => window.buildRecommendedItem(dest)).join('');
                } else {
                    renderEmpty(recContainer, cat === 'Nightlife' ? 'No Nightlife Tourist Sites Yet' : 'No recommended sites in this category.');
                }
            }
        }

        // 3. Filter Near Me Section
        const nearContainer = document.getElementById('near-me-container');
        if (nearContainer) {
            const sourceSpots = window.allNearSpots || window.lastNearSpots || [];
            if (sourceSpots.length > 0) {
                let list = [];
                if (cat === 'All') {
                    list = window.lastNearSpots || sourceSpots.slice(0, 4);
                } else {
                    list = sourceSpots.filter(d => matchesCategory(d.category, d.name, d.municipality || d.location, cat));
                }
                if (list.length > 0) {
                    nearContainer.classList.remove('is-empty');
                    nearContainer.style.paddingLeft = '';
                    nearContainer.style.paddingRight = '';
                    nearContainer.style.marginLeft = '';
                    nearContainer.style.marginRight = '';
                    nearContainer.innerHTML = list.map(dest => {
                        const img = window.getDestImage(dest, 600);
                        const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#0284c7' : (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                        let distText = '';
                        if (dest.distance !== undefined && dest.distance < 999999) {
                            if (dest.distance < 0.05) distText = `${Math.max(5, Math.round(dest.distance * 1000))}m away`;
                            else if (dest.distance < 1.0) distText = `${Math.round((dest.distance * 1000) / 10) * 10}m away`;
                            else distText = `${dest.distance.toFixed(1)} km away`;
                        }
                        const encodedDest = encodeURIComponent(JSON.stringify(dest));
                        return `
                            <div class="fav-card" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" data-name="${(dest.name || '').replace(/"/g, '&quot;')}" data-municipality="${(dest.municipality || dest.location || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                                ${badgeHtml}
                                <img src="${img}" alt="${dest.name}" loading="lazy" decoding="async" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\\'")}', '${(dest.municipality || '').replace(/'/g, "\\'")}'); else this.src='assets/img/no_image.svg';">
                                <div class="fav-card-overlay">
                                    <span class="fav-card-name">${dest.name}</span>
                                    ${distText ? `<span style="display:block; font-size:10px; color:#38bdf8; margin-top:2px; font-weight:700;"><i class="fa-solid fa-location-arrow"></i> ${distText}</span>` : ''}
                                </div>
                            </div>
                        `;
                    }).join('');
                    window.initLoopingFocusCarousel('near-me-container');
                } else if (cat !== 'All') {
                    renderEmpty(nearContainer, cat === 'Nightlife' ? 'No Nightlife Sites Nearby' : 'No nearby sites found in this category.');
                }
            }
        }

        // 4. Filter All Tourist Sites Section
        if (window.allTouristSpots && typeof window.renderAllTouristSpots === 'function') {
            let allList = [];
            if (cat === 'All') {
                allList = window.allTouristSpots;
            } else {
                allList = window.allTouristSpots.filter(d => matchesCategory(d.category, d.name, d.municipality || d.location, cat));
            }
            window.renderAllTouristSpots(allList, cat);
        }
    };

    window.buildAllSpotCard = function (dest) {
        const img = window.getDestImage(dest, 400);
        const cStatus = String(dest.classification_status || '').toUpperCase();
        const badgeText = (cStatus === 'EMERGE' || cStatus === 'EMERGING') ? 'EMERGING' : (cStatus === 'POTENTIAL' ? 'POTENTIAL' : 'EXISTING');
        const badgeBg = (cStatus === 'EMERGE' || cStatus === 'EMERGING') ? '#ef4444' : (cStatus === 'POTENTIAL' ? '#10b981' : '#0284c7');
        const muni = dest.municipality || dest.location || (typeof window.resolveMunicipalityName === 'function' ? window.resolveMunicipalityName(dest) : (dest.municipality_name || 'La Union'));
        const ratingRaw = (dest.rating !== undefined && dest.rating !== null && dest.rating !== '') ? dest.rating : dest.reviews_avg_rating;
        const ratingNum = parseFloat(ratingRaw);
        const hasValidRating = !isNaN(ratingNum) && ratingNum > 0;
        const ratingStr = hasValidRating ? ratingNum.toFixed(1) : '';
        const count = parseInt(dest.reviews_count ?? dest.review_count ?? dest.total_reviews ?? 0, 10);
        const reviewCount = isNaN(count) ? 0 : count;
        const cleanDesc = dest.description ? dest.description.replace(/<[^>]*>?/gm, '').trim() : '';
        const snippet = cleanDesc.length > 0 ? (cleanDesc.length > 75 ? cleanDesc.substring(0, 75) + '...' : cleanDesc) : 'A hidden gem with crystal clear waters and natural beauty.';
        const encodedDest = encodeURIComponent(JSON.stringify(dest));
        const isFav = window.isSpotFavorited ? window.isSpotFavorited(dest.id) : false;

        const ratingHtml = hasValidRating ? `
            <div class="spot-card-rating">
                <i class="fa-solid fa-star"></i>
                <strong>${ratingStr}</strong>
                ${reviewCount > 0 ? `<span>(${reviewCount})</span>` : ''}
            </div>
        ` : '';

        return `
            <div class="all-spot-card-horizontal" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" data-name="${(dest.name || '').replace(/"/g, '&quot;')}" data-municipality="${(muni || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                <div class="spot-card-thumb-wrap">
                    <span class="spot-card-badge" style="background:${badgeBg};">${badgeText}</span>
                    <img src="${img}" alt="${(dest.name || '').replace(/"/g, '&quot;')}" loading="lazy" decoding="async" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\'")}', '${(dest.municipality || '').replace(/'/g, "\'")}'); else this.src='assets/img/no_image.svg';">
                </div>
                <div class="spot-card-body">
                    <div class="spot-card-top-row">
                        <h4 class="spot-card-title" title="${(dest.name || '').replace(/"/g, '&quot;')}">${dest.name}</h4>
                    </div>
                    <div class="spot-card-muni">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>${muni}</span>
                        ${(dest.distance !== undefined && dest.distance !== null && dest.distance < 999999) ? `<span style="font-size:10px; color:#0284c7; font-weight:700; display:inline-flex; align-items:center; gap:2px; margin-left:6px;"><i class="fa-solid fa-location-arrow" style="font-size:8.5px;"></i> ${dest.distance < 1 ? (dest.distance * 1000).toFixed(0) + 'm' : dest.distance.toFixed(1) + 'km'}</span>` : ''}
                    </div>
                    <p class="spot-card-desc">${snippet}</p>
                    <div class="spot-card-footer">
                        ${ratingHtml}
                        <button type="button" class="btn-spot-details" style="${hasValidRating ? '' : 'margin-left:auto;'}" onclick="event.stopPropagation(); window.viewDestinationOnMap('${encodedDest}')">
                            <span>View Details</span> <i class="fa-solid fa-arrow-right" style="font-size:9px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    };

    // Distance calculator helper
    window.calcDistanceKm = function(lat1, lon1, lat2, lon2) {
        const R = 6371; // km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    };

    // Filter Chips for Tourist Sites (Mockup Screen 2)
    window.filterTouristSitesChip = function(type, btn) {
        window._currentTouristSitesChip = type;

        // Visual chip selection
        const chips = document.querySelectorAll('.dash-filter-chips .dash-chip');
        chips.forEach(c => c.classList.remove('active'));
        if (btn) {
            btn.classList.add('active');
        } else {
            const chipBtn = document.querySelector(`.dash-filter-chips .dash-chip[onclick*="'${type}'"]`);
            if (chipBtn) chipBtn.classList.add('active');
        }

        if (!window._currentTouristFilterState) {
            window._currentTouristFilterState = { muni: 'All', status: 'All', sort: 'default' };
        }

        const fState = window._currentTouristFilterState;
        // The filter sliders button is ONLY active if explicit modal filters are active (not the Near Me chip itself)
        const hasExplicitModalFilters = Boolean(fState && (
            (fState.muni && fState.muni !== 'All' && fState.muni !== 'near') ||
            (fState.status && fState.status !== 'All') ||
            (fState.sort && fState.sort !== 'default' && fState.sort !== 'distance_asc')
        ));
        const filterBtn = document.getElementById('btn-tourist-filter');
        if (filterBtn) {
            filterBtn.classList.toggle('active', hasExplicitModalFilters);
            if (!hasExplicitModalFilters) {
                filterBtn.style.background = '';
                filterBtn.style.color = '';
                filterBtn.style.borderColor = '';
            } else {
                filterBtn.style.background = '#1e3a8a';
                filterBtn.style.color = '#ffffff';
                filterBtn.style.borderColor = '#1e3a8a';
            }
        }

        if (!window.allTouristSpots || window.allTouristSpots.length === 0) return;

        const statusMap = { 'EXIST': 'Existing', 'EMERGE': 'Emerging', 'POTENTIAL': 'Potential' };
        const stLabel = fState && fState.status !== 'All' ? (statusMap[fState.status] || fState.status) : '';
        const hasSpecificMuni = Boolean(fState && fState.muni && fState.muni !== 'All' && fState.muni !== 'near');
        const muniLabel = hasSpecificMuni ? fState.muni : '';

        let spots = typeof window.getFilteredTouristSpots === 'function' ? window.getFilteredTouristSpots() : [...window.allTouristSpots];

        if (type === 'top') {
            spots.sort((a, b) => {
                const rA = parseFloat(b.rating || b.reviews_avg_rating || 0);
                const rB = parseFloat(a.rating || a.reviews_avg_rating || 0);
                return rA - rB;
            });
            let label = 'Top Rated';
            if (stLabel && muniLabel) label = `Top Rated · ${stLabel} in ${muniLabel}`;
            else if (stLabel) label = `Top Rated · ${stLabel}`;
            else if (muniLabel) label = `Top Rated in ${muniLabel}`;

            if (typeof window.renderAllTouristSpots === 'function') {
                window.renderAllTouristSpots(spots, label);
            }
        } else if (type === 'near') {
            const runNearMeFilter = (lat, lng) => {
                const uLat = parseFloat(lat);
                const uLng = parseFloat(lng);
                let baseSpots = typeof window.getFilteredTouristSpots === 'function' ? window.getFilteredTouristSpots() : [...window.allTouristSpots];
                let mapped = baseSpots.map(s => {
                    const copy = { ...s };
                    const sLat = parseFloat(copy.lat || copy.latitude);
                    const sLng = parseFloat(copy.lng || copy.longitude);
                    if (!isNaN(sLat) && !isNaN(sLng) && !isNaN(uLat) && !isNaN(uLng)) {
                        copy.distance = window.calcDistanceKm(uLat, uLng, sLat, sLng);
                    } else {
                        copy.distance = 999999;
                    }
                    return copy;
                });

                // User radius requirement: strictly 1km limit!
                const NEAR_ME_RADIUS_KM = 1.0;
                let nearSpots = mapped.filter(s => {
                    if (s.distance === 999999) return false;
                    return s.distance <= NEAR_ME_RADIUS_KM;
                });

                nearSpots.sort((a, b) => a.distance - b.distance);

                let label = 'Near Me';
                if (stLabel && muniLabel) label = `Near Me · ${stLabel} in ${muniLabel}`;
                else if (stLabel) label = `Near Me · ${stLabel}`;
                else if (muniLabel) label = `Near Me · ${muniLabel}`;

                if (typeof window.renderAllTouristSpots === 'function') {
                    window.renderAllTouristSpots(nearSpots, label);
                }
            };

            const userLat = window.userCurrentCoords?.lat || window.currentGPSLat || window.currentWeatherLocation?.lat;
            const userLng = window.userCurrentCoords?.lng || window.currentGPSLng || window.currentWeatherLocation?.lng;

            if (userLat && userLng) {
                runNearMeFilter(userLat, userLng);
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        window.userCurrentCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                        runNearMeFilter(pos.coords.latitude, pos.coords.longitude);
                    },
                    err => {
                        runNearMeFilter(16.6159, 120.3209);
                    },
                    { enableHighAccuracy: true, timeout: 6000 }
                );
            } else {
                runNearMeFilter(16.6159, 120.3209);
            }
        } else {
            let label = 'All';
            if (stLabel && muniLabel) label = `${muniLabel} · ${stLabel}`;
            else if (stLabel) label = stLabel;
            else if (muniLabel) label = muniLabel;

            if (typeof window.renderAllTouristSpots === 'function') {
                window.renderAllTouristSpots(spots, label);
            }
        }
    };

    // Live Search for Dashboard (Instant Dropdown & Filter)
    window.handleDashSearch = function(query) {
        const clearBtn = document.getElementById('dash-search-clear');
        const resultsEl = document.getElementById('dash-search-results');
        const q = (query || '').toLowerCase().trim();

        if (clearBtn) {
            clearBtn.style.display = q.length > 0 ? 'flex' : 'none';
        }

        // Resolve spots data
        let spots = window.allTouristSpots;
        if (!spots || spots.length === 0) {
            try {
                const cached = localStorage.getItem('public_map_data');
                if (cached) {
                    const parsed = JSON.parse(cached);
                    spots = parsed.destinations || parsed?.data?.destinations || [];
                    window.allTouristSpots = spots;
                }
            } catch (err) {}
        }

        if (!q) {
            if (resultsEl) {
                resultsEl.style.display = 'none';
                resultsEl.innerHTML = '';
            }
            if (typeof window.renderAllTouristSpots === 'function' && spots && spots.length > 0) {
                window.renderAllTouristSpots(spots, 'All');
            }
            return;
        }

        const matched = (spots || []).filter(s => {
            const name = (s.name || '').toLowerCase();
            const muni = (s.municipality || s.location || '').toLowerCase();
            const cat = (s.category || '').toLowerCase();
            const desc = (s.description || '').toLowerCase();
            return name.includes(q) || muni.includes(q) || cat.includes(q) || desc.includes(q);
        });

        if (resultsEl) {
            if (matched.length > 0) {
                let html = '';
                matched.slice(0, 8).forEach(dest => {
                    const img = typeof window.getDestImage === 'function'
                        ? window.getDestImage(dest, 150)
                        : (dest.thumbnail_url || dest.image_url || dest.image || 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=150');
                    const encodedDest = encodeURIComponent(JSON.stringify(dest));
                    const locName = dest.location || dest.municipality || 'La Union';

                    const cls = (dest.classification || '').toLowerCase();
                    let badgeBg = '#0284c7';
                    let badgeLabel = dest.classification || 'Existing';
                    if (cls.includes('potential')) { badgeBg = '#10b981'; }
                    else if (cls.includes('emerging')) { badgeBg = '#ef4444'; }

                    html += `
                        <div onclick="window.viewDestinationOnMap('${encodedDest}'); const r=document.getElementById('dash-search-results'); if(r) r.style.display='none';" 
                             style="display:flex; align-items:center; gap:12px; padding:9px 12px; border-radius:12px; cursor:pointer; transition:background 0.15s ease; border-bottom:1px solid #f1f5f9;"
                             onmouseover="this.style.background='#f8fafc'" 
                             onmouseout="this.style.background='transparent'">
                            <img src="${img}" alt="${(dest.name||'').replace(/"/g, '&quot;')}" style="width:42px; height:42px; border-radius:10px; object-fit:cover; border:1px solid #e2e8f0; flex-shrink:0;">
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:6px; margin-bottom:2px;">
                                    <div style="font-size:13.5px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${dest.name}</div>
                                    <span style="font-size:9.5px; font-weight:700; text-transform:uppercase; color:#ffffff; background:${badgeBg}; padding:1px 6px; border-radius:6px; flex-shrink:0;">${badgeLabel}</span>
                                </div>
                                <div style="font-size:11.5px; font-weight:500; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <i class="fa-solid fa-location-dot" style="color:#0284c7; margin-right:4px;"></i>${locName}
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right" style="color:#cbd5e1; font-size:12px; margin-left:4px; flex-shrink:0;"></i>
                        </div>
                    `;
                });
                resultsEl.innerHTML = html;
                resultsEl.style.display = 'block';
            } else {
                resultsEl.innerHTML = `
                    <div style="padding:22px 14px; text-align:center;">
                        <i class="fa-solid fa-location-dot" style="font-size:22px; color:#cbd5e1; margin-bottom:6px; display:block;"></i>
                        <div style="font-size:13px; font-weight:700; color:#0f172a;">No places found</div>
                        <div style="font-size:11.5px; color:#64748b; margin-top:2px;">We couldn't find any spot matching "${(query||'').replace(/"/g, '&quot;')}"</div>
                    </div>
                `;
                resultsEl.style.display = 'block';
            }
        }

        if (typeof window.renderAllTouristSpots === 'function') {
            window.renderAllTouristSpots(matched, 'Search Results');
        }
    };

    window.clearDashSearch = function() {
        const inp = document.getElementById('dash-search-input');
        if (inp) {
            inp.value = '';
            window.handleDashSearch('');
        }
    };

    // Limit the number of tourist sites on the dashboard to maximize a compact minimum (4 cards)
    // Full catalog with search, filters & sort displays on the dedicated Tourist Sites page
    window.navigateToTouristSitesPage = function () {
        localStorage.setItem('intan_elyu_explore_mode', 'all');
        if (window.currentDashCategory && window.currentDashCategory !== 'All') {
            localStorage.setItem('intan_elyu_filter_category', window.currentDashCategory);
        } else {
            localStorage.removeItem('intan_elyu_filter_category');
        }
        if (typeof window.navigateTo === 'function') {
            window.navigateTo('trending');
        } else {
            window.location.href = '?view=trending';
        }
    };

    window.toggleAllSpotsMore = window.navigateToTouristSitesPage;

    window.renderAllTouristSpots = function (spots, activeCat = 'All') {
        const container = document.getElementById('all-spots-container');
        const countBadge = document.getElementById('all-spots-count-badge');
        const subtitle = document.getElementById('all-spots-subtitle');
        if (!container) return;

        if (!spots || spots.length === 0) {
            if (countBadge) countBadge.textContent = '0 Spots';
            const isNearMe = (activeCat.includes('Near Me') || window._currentTouristSitesChip === 'near');
            if (subtitle) {
                subtitle.textContent = isNearMe
                    ? 'No tourist destinations found within 1km of your location'
                    : `Showing 0 destinations in ${activeCat}`;
            }
            container.innerHTML = `
                <div class="dash-empty-state" style="margin-top: 6px !important;">
                    <div class="dash-empty-icon-wrap" style="${isNearMe ? 'background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;' : ''}">
                        <i class="fa-solid ${isNearMe ? 'fa-location-dot' : 'fa-compass'}" style="${isNearMe ? 'color: #1e3a8a !important;' : ''}"></i>
                    </div>
                    <div class="dash-empty-title">${isNearMe ? 'No Tourist Sites Near You' : `No Spots in ${activeCat}`}</div>
                    <div class="dash-empty-desc">${isNearMe ? 'No tourist sites found within 1km of your current location matching your filters. Try adjusting your filters or tap All to browse all destinations.' : 'No tourist sites found in this category right now. Tap All to view all destinations.'}</div>
                </div>
            `;
            return;
        }

        if (countBadge) {
            countBadge.textContent = `${spots.length} Spot${spots.length === 1 ? '' : 's'}`;
        }

        if (subtitle) {
            if (activeCat === 'All') {
                subtitle.textContent = `Browse verified attractions & hidden gems across La Union`;
            } else {
                subtitle.textContent = `Showing ${spots.length} destination${spots.length === 1 ? '' : 's'} in ${activeCat}`;
            }
        }

        const DASHBOARD_SPOT_LIMIT = 6;
        const initialSpots = spots.slice(0, DASHBOARD_SPOT_LIMIT);

        let html = '<div class="all-spots-grid">';
        initialSpots.forEach(s => {
            html += window.buildAllSpotCard(s);
        });
        html += '</div>';

        container.innerHTML = html;


    };

    window.fetchAllTouristSpots = async function () {
        const backendUrl = getDashBackendUrl();
        const cacheKey = 'public_map_data';

        // Instant synchronous render from cache if available (0ms delay)
        let initialSpots = (window.allTouristSpots && window.allTouristSpots.length > 0) ? window.allTouristSpots : null;
        if (!initialSpots) {
            try {
                const mapCache = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('public_map_data'), null)?.data : null;
                initialSpots = (mapCache && mapCache.destinations) || null;
            } catch(e) {}
        }
        if (!initialSpots) {
            try {
                const fallbackRaw = localStorage.getItem('intan_elyu_cached_spots_map');
                if (fallbackRaw) {
                    const parsed = JSON.parse(fallbackRaw)?.data;
                    if (parsed && (parsed.destinations || parsed.spots)) {
                        initialSpots = parsed.destinations || parsed.spots;
                    }
                }
            } catch(e) {}
        }
        if (initialSpots && initialSpots.length > 0) {
            window.allTouristSpots = initialSpots
                .filter(d => !d.status || !['pending', 'draft', 'rejected'].includes(d.status.toLowerCase()))
                .sort((a, b) => (b.id || 0) - (a.id || 0));
            const activeCat = window.currentDashCategory || 'All';
            let filtered = window.allTouristSpots;
            if (activeCat !== 'All' && typeof window.matchesCategory === 'function') {
                filtered = window.allTouristSpots.filter(d => window.matchesCategory(d.category, d.name, d.municipality || d.location, activeCat));
            }
            if (typeof window.renderAllTouristSpots === 'function') {
                window.renderAllTouristSpots(filtered, activeCat);
            }
        }

        try {
            await window.useCache(
                cacheKey,
                async () => {
                    const res = await fetch(backendUrl + '/api/public/map');
                    if (!res.ok) throw new Error("Failed to fetch all spots");
                    return await res.json();
                },
                (data) => {
                    // Check fallback if data is missing or offline
                    if (!data || !data.destinations) {
                        try {
                            const fallbackRaw = localStorage.getItem('intan_elyu_cached_spots_map');
                            if (fallbackRaw) {
                                const parsed = JSON.parse(fallbackRaw)?.data;
                                if (parsed && (parsed.destinations || parsed.spots)) {
                                    data = { destinations: parsed.destinations || parsed.spots };
                                }
                            }
                        } catch(e) {}
                    }
                    if (data && data.destinations) {
                        window.allTouristSpots = (data.destinations || [])
                            .filter(d => !d.status || !['pending', 'draft', 'rejected'].includes(d.status.toLowerCase()))
                            .sort((a, b) => (b.id || 0) - (a.id || 0));
                        const activeCat = window.currentDashCategory || 'All';
                        let filtered = window.allTouristSpots;
                        if (activeCat !== 'All' && typeof window.matchesCategory === 'function') {
                            filtered = window.allTouristSpots.filter(d => window.matchesCategory(d.category, d.name, d.municipality || d.location, activeCat));
                        }
                        if (typeof window.renderAllTouristSpots === 'function') {
                            window.renderAllTouristSpots(filtered, activeCat);
                        }
                    } else if (typeof handleDashboardEmptyOrError === 'function') {
                        handleDashboardEmptyOrError();
                    }
                },
                false,
                60000
            );
        } catch (e) {
            console.warn('fetchAllTouristSpots handled:', e);
            if ((!initialSpots || initialSpots.length === 0) && typeof handleDashboardEmptyOrError === 'function') {
                handleDashboardEmptyOrError();
            }
        }
    };

    window.initLoopingFocusCarousel = function (containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let isTicking = false;

        function updateCardScales() {
            const cards = container.querySelectorAll('.fav-card');
            if (cards.length === 0) return;

            const containerRect = container.getBoundingClientRect();
            const containerCenter = containerRect.left + containerRect.width / 2;

            let closestCard = null;
            let minDistance = Infinity;

            cards.forEach(card => {
                const cardRect = card.getBoundingClientRect();
                const cardCenter = cardRect.left + cardRect.width / 2;
                const dist = Math.abs(cardCenter - containerCenter);

                if (dist < minDistance) {
                    minDistance = dist;
                    closestCard = card;
                }

                const maxDist = containerRect.width * 0.42;
                const ratio = Math.max(0, 1 - dist / maxDist);
                const scale = 0.88 + (ratio * 0.12);
                const opacity = 0.70 + (ratio * 0.30);

                card.style.transform = `scale(${scale.toFixed(3)}) translate3d(0,0,0)`;
                card.style.opacity = opacity.toFixed(2);
            });

            cards.forEach(c => c.classList.remove('active-card'));
            if (closestCard) {
                closestCard.classList.add('active-card');
            }

            // Dynamically synchronize carousel dots with the centered card, or hide if empty or 1 card only
            if (containerId === 'trending-container') {
                const dotsWrap = document.getElementById('trending-dots');
                if (dotsWrap) {
                    if (cards.length <= 1 || container.classList.contains('is-empty') || container.querySelector('.dash-empty-state')) {
                        dotsWrap.style.display = 'none';
                    } else {
                        dotsWrap.style.display = 'flex';
                        const numDots = Math.min(cards.length, 10);
                        if (dotsWrap.querySelectorAll('.dash-dot').length !== numDots) {
                            dotsWrap.innerHTML = Array.from({ length: numDots }, (_, i) => `<span class="dash-dot ${i === 0 ? 'active' : ''}"></span>`).join('');
                        }
                        if (closestCard) {
                            const dots = dotsWrap.querySelectorAll('.dash-dot');
                            if (dots.length > 0) {
                                const cardList = Array.from(cards);
                                const activeIdx = cardList.indexOf(closestCard);
                                if (activeIdx >= 0) {
                                    const dotIdx = activeIdx % dots.length;
                                    dots.forEach((d, i) => d.classList.toggle('active', i === dotIdx));
                                }
                            }
                        }
                    }
                }
            }

            isTicking = false;
        }

        if (!container._focusCarouselInited) {
            container._focusCarouselInited = true;

            // Click capture: clicking a shrinked card smoothly scrolls it to center
            container.addEventListener('click', (e) => {
                if (e.target.closest('.fav-heart') || e.target.closest('.fav-card-overlay i')) {
                    return; // Allow clicks on buttons to proceed without carousel interception!
                }

                const card = e.target.closest('.fav-card');
                if (!card) return;

                const containerRect = container.getBoundingClientRect();
                const containerCenter = containerRect.left + containerRect.width / 2;
                const cardRect = card.getBoundingClientRect();
                const cardCenter = cardRect.left + cardRect.width / 2;
                const offsetFromCenter = Math.abs(cardCenter - containerCenter);

                if (offsetFromCenter > 35) {
                    e.preventDefault();
                    e.stopPropagation();

                    const scrollDelta = cardCenter - containerCenter;
                    container.scrollBy({ left: scrollDelta, behavior: 'smooth' });
                }
            }, true);

            container.addEventListener('scroll', () => {
                if (!isTicking) {
                    requestAnimationFrame(updateCardScales);
                    isTicking = true;
                }
            }, { passive: true });
            requestAnimationFrame(() => {
                updateCardScales();
                setTimeout(updateCardScales, 150);
            });
        } else {
            requestAnimationFrame(updateCardScales);
        }

        // Tap dots to smoothly scroll the active card to center
        if (containerId === 'trending-container') {
            const dotsWrap = document.getElementById('trending-dots');
            if (dotsWrap && !dotsWrap._dotsListenerInited) {
                dotsWrap._dotsListenerInited = true;
                dotsWrap.addEventListener('click', (e) => {
                    const dot = e.target.closest('.dash-dot');
                    if (!dot) return;
                    const dots = Array.from(dotsWrap.querySelectorAll('.dash-dot'));
                    const dotIdx = dots.indexOf(dot);
                    const currentCards = container.querySelectorAll('.fav-card');
                    if (dotIdx >= 0 && currentCards[dotIdx]) {
                        const targetCard = currentCards[dotIdx];
                        const containerRect = container.getBoundingClientRect();
                        const containerCenter = containerRect.left + containerRect.width / 2;
                        const cardRect = targetCard.getBoundingClientRect();
                        const cardCenter = cardRect.left + cardRect.width / 2;
                        const scrollDelta = cardCenter - containerCenter;
                        container.scrollBy({ left: scrollDelta, behavior: 'smooth' });
                    }
                });
            }
        }
    };

    // ─────────────────────────────────────────────────────────────────────────────
    //  Weather API Integration
    // ─────────────────────────────────────────────────────────────────────────────
    window.currentWeatherLocation = { lat: 16.6159, lng: 120.3209, name: 'San Fernando, La Union', isCurrentLoc: false };

    window.renderWeatherIconHtml = function (iconEmoji, faIcon) {
        if (faIcon) {
            let color = '#fbbf24';
            if (faIcon.includes('sun')) color = '#fbbf24';
            else if (faIcon.includes('moon')) color = '#a78bfa';
            else if (faIcon.includes('rain') || faIcon.includes('shower')) color = '#38bdf8';
            else if (faIcon.includes('bolt')) color = '#f59e0b';
            else if (faIcon.includes('cloud')) color = '#94a3b8';
            else if (faIcon.includes('smog')) color = '#cbd5e1';

            return `<i class="fa-solid ${faIcon}" style="color:${color}; filter:drop-shadow(0 0 10px ${color}80);"></i>`;
        }
        return iconEmoji || '⛅';
    };

    window.updateWeatherModal = function (data) {
        if (!data) return;
        const modalLoc = document.getElementById('modal-weather-loc');
        const modalTemp = document.getElementById('modal-temp');
        const modalCondition = document.getElementById('modal-condition');
        const modalFeels = document.getElementById('modal-feels');
        const modalIcon = document.getElementById('modal-icon');
        const forecastContainer = document.getElementById('weather-forecast-container');

        if (modalLoc) modalLoc.textContent = data.location;
        if (modalTemp) modalTemp.textContent = `${data.temperature}°C`;
        if (modalCondition) modalCondition.textContent = data.condition;
        if (modalFeels) modalFeels.textContent = `Feels like ${data.feels_like}°C · Humidity ${data.humidity}% · Wind ${data.wind_speed}km/h`;
        if (modalIcon) modalIcon.innerHTML = window.renderWeatherIconHtml(data.icon, data.fa_icon);

        if (forecastContainer && data.forecast && data.forecast.length > 0) {
            forecastContainer.innerHTML = data.forecast.map(item => `
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; transition: transform 0.15s ease;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <span style="font-size: 26px; width: 34px; text-align: center; display: inline-block;">${window.renderWeatherIconHtml(item.icon, item.fa_icon)}</span>
                        <div>
                            <div style="font-size: 14px; font-weight: 800; color: #0f172a;">${item.day}</div>
                            <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">${item.condition}</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 14px; font-weight: 800; color: #0f172a;">${item.temp_max}° <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">/ ${item.temp_min}°C</span></div>
                        <div style="font-size: 11px; color: #0284c7; font-weight: 700; margin-top: 2px;"><i class="fa-solid fa-droplet" style="font-size: 9px; color: #0284c7;"></i> ${item.rain_prob}% rain</div>
                    </div>
                </div>
            `).join('');
        }
    };

    window.fetchWeather = async function (lat = 16.6159, lng = 120.3209, locationName = 'San Fernando, La Union', isCurrentLoc = false) {
        window.currentWeatherLocation = { lat, lng, name: locationName, isCurrentLoc };
        const tempEl = document.getElementById('weather-temp');
        const descEl = document.getElementById('weather-desc');
        const locEl = document.getElementById('weather-loc');
        const humidityEl = document.getElementById('weather-humidity');
        const windEl = document.getElementById('weather-wind');
        const uvEl = document.getElementById('weather-uv');
        const iconEl = document.getElementById('weather-icon');

        // Pre-hydrate from cache immediately so "Loading Weather..." disappears in 0ms
        try {
            const rawW = localStorage.getItem('intan_elyu_cached_weather');
            if (rawW) {
                const cw = (window.safeJsonParse ? window.safeJsonParse(rawW, null) : JSON.parse(rawW))?.data;
                if (cw) {
                    if (tempEl) tempEl.textContent = `${cw.temperature}°C`;
                    if (descEl) descEl.textContent = cw.condition;
                    if (locEl) locEl.textContent = cw.location || locationName;
                    if (humidityEl) humidityEl.textContent = `${cw.humidity}%`;
                    if (windEl) windEl.textContent = `${cw.wind_speed} km/h`;
                    if (uvEl) uvEl.textContent = cw.uv_index;
                    if (iconEl) iconEl.innerHTML = window.renderWeatherIconHtml(cw.icon, cw.fa_icon);
                    window.updateWeatherModal(cw);
                }
            } else {
                if (tempEl && (tempEl.textContent.includes('Loading') || tempEl.querySelector('.fa-spinner'))) {
                    tempEl.textContent = '29°C';
                    if (descEl) descEl.textContent = 'Partly Cloudy';
                    if (locEl) locEl.textContent = locationName || 'San Fernando, La Union';
                    if (humidityEl) humidityEl.textContent = '72%';
                    if (windEl) windEl.textContent = '14 km/h';
                    if (uvEl) uvEl.textContent = '6';
                    if (iconEl) iconEl.innerHTML = window.renderWeatherIconHtml('⛅', 'fa-cloud-sun');
                }
            }
        } catch(e) {}

        const isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
        if (isOffline) {
            if (tempEl && tempEl.textContent.includes('Loading')) {
                tempEl.textContent = '29°C';
                if (descEl) descEl.textContent = 'Partly Cloudy';
                if (locEl) locEl.textContent = locationName;
                if (humidityEl) humidityEl.textContent = '72%';
                if (windEl) windEl.textContent = '14 km/h';
                if (uvEl) uvEl.textContent = '6';
                if (iconEl) iconEl.innerHTML = window.renderWeatherIconHtml('⛅', 'fa-cloud-sun');
            }
            return;
        }

        try {
            const apiBase = getDashBackendUrl();
            const isCurrentParam = isCurrentLoc ? '&is_current_location=1' : '';
            const url = `${apiBase}/api/public/weather?lat=${lat}&lng=${lng}&location=${encodeURIComponent(locationName)}${isCurrentParam}`;

            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Weather request failed with status ' + res.status);
            const data = await res.json();

            try { localStorage.setItem('intan_elyu_cached_weather', JSON.stringify({ data, timestamp: Date.now() })); } catch(e) {}

            if (tempEl) tempEl.textContent = `${data.temperature}°C`;
            if (descEl) descEl.textContent = data.condition;
            if (locEl) locEl.textContent = data.location;
            if (humidityEl) humidityEl.textContent = `${data.humidity}%`;
            if (windEl) windEl.textContent = `${data.wind_speed} km/h`;
            if (uvEl) uvEl.textContent = data.uv_index;
            if (iconEl) iconEl.innerHTML = window.renderWeatherIconHtml(data.icon, data.fa_icon);

            window.updateWeatherModal(data);
        } catch (e) {
            console.warn('Weather fetch error, checking cache or fallback:', e);
            let cachedW = null;
            try {
                const rawW = localStorage.getItem('intan_elyu_cached_weather');
                if (rawW) cachedW = (window.safeJsonParse ? window.safeJsonParse(rawW, null) : JSON.parse(rawW))?.data;
            } catch(err) {}

            if (cachedW) {
                if (tempEl) tempEl.textContent = `${cachedW.temperature}°C`;
                if (descEl) descEl.textContent = cachedW.condition;
                if (locEl) locEl.textContent = cachedW.location || locationName;
                if (humidityEl) humidityEl.textContent = `${cachedW.humidity}%`;
                if (windEl) windEl.textContent = `${cachedW.wind_speed} km/h`;
                if (uvEl) uvEl.textContent = cachedW.uv_index;
                if (iconEl) iconEl.innerHTML = window.renderWeatherIconHtml(cachedW.icon, cachedW.fa_icon);
            } else {
                if (tempEl) tempEl.textContent = '29°C';
                if (descEl) descEl.textContent = 'Partly Cloudy';
                if (locEl) locEl.textContent = locationName;
                if (humidityEl) humidityEl.textContent = '72%';
                if (windEl) windEl.textContent = '14 km/h';
                if (uvEl) uvEl.textContent = '6';
                if (iconEl) iconEl.innerHTML = window.renderWeatherIconHtml('⛅', 'fa-cloud-sun');
            }
        }
    };

    window.locateMeForWeather = function () {
        const locEl = document.getElementById('weather-loc');
        const descEl = document.getElementById('weather-desc');
        if (locEl) locEl.textContent = "Detecting location...";
        if (descEl) descEl.textContent = 'Acquiring location...';

        const fallbackToLocal = () => {
            try {
                const manual = localStorage.getItem('intan_elyu_manual_loc');
                if (manual) {
                    const parsed = JSON.parse(manual);
                    if (parsed && parsed.lat && parsed.lng) {
                        window.fetchWeather(parsed.lat, parsed.lng, parsed.name || 'La Union', false);
                        if (typeof window.loadNearMe === 'function') window.loadNearMe(parsed.lat, parsed.lng, parsed.name);
                        return;
                    }
                }
            } catch (e) { }
            window.fetchWeather(16.6159, 120.3209, 'San Fernando, La Union', false);
            if (typeof window.loadNearMe === 'function') window.loadNearMe(16.6159, 120.3209, 'San Fernando City');
        };

        if (typeof window.requestPreciseLocation === 'function') {
            window.requestPreciseLocation(true).then(loc => {
                if (loc && loc.lat && loc.lng) {
                    window.userCurrentCoords = { lat: loc.lat, lng: loc.lng };
                    window.currentGPSLat = loc.lat;
                    window.currentGPSLng = loc.lng;
                    window.currentGPSSource = 'gps';
                    window.fetchWeather(loc.lat, loc.lng, 'My Location 📍', true);
                    if (typeof loadNearMe === 'function') loadNearMe(loc.lat, loc.lng);
                } else {
                    fallbackToLocal();
                }
            }).catch(() => {
                fallbackToLocal();
            });
        } else if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    window.userCurrentCoords = { lat, lng };
                    window.currentGPSLat = lat;
                    window.currentGPSLng = lng;
                    window.currentGPSSource = 'gps';
                    window.fetchWeather(lat, lng, 'My Location 📍', true);
                    if (typeof loadNearMe === 'function') loadNearMe(lat, lng);
                },
                (err) => {
                    console.warn('GPS location failed/denied, using local fallback:', err);
                    fallbackToLocal();
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            fallbackToLocal();
        }
    };

    window.openWeatherModal = function () {
        const modal = document.getElementById('weather-modal');
        if (!modal) return;

        // Port directly to document.body so it is never trapped by any container stacking context
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        // Hide navigation bar and keep it behind/hidden
        document.body.classList.add('sheet-open');
        const bottomNav = document.getElementById('bottom-navigation');
        if (bottomNav) {
            bottomNav.classList.add('nav-hidden');
            bottomNav.style.display = 'none';
        }

        modal.style.display = 'flex';
    };

    window.closeWeatherModal = function () {
        const modal = document.getElementById('weather-modal');
        if (modal) modal.style.display = 'none';

        // Restore navigation bar
        document.body.classList.remove('sheet-open');
        const bottomNav = document.getElementById('bottom-navigation');
        if (bottomNav) {
            bottomNav.classList.remove('nav-hidden');
            bottomNav.style.display = '';
        }
    };

    // Immediately port weather modal to document.body so it stays above bottom navigation
    try {
        const initialWModal = document.getElementById('weather-modal');
        if (initialWModal && initialWModal.parentElement !== document.body) {
            document.body.appendChild(initialWModal);
        }
    } catch (e) { }

    window.changeWeatherLocation = function (valStr) {
        if (valStr === 'GPS_CURRENT') {
            window.locateMeForWeather();
            return;
        }
        const parts = valStr.split(',');
        if (parts.length >= 3) {
            const lat = parseFloat(parts[0]);
            const lng = parseFloat(parts[1]);
            const name = parts[2];
            window.fetchWeather(lat, lng, name + ', La Union', false);
            if (typeof window.loadNearMe === 'function') {
                window.loadNearMe(lat, lng, name);
            }
        }
    };

    // Live continuous GPS sync listener for real-time Near Me distances (debounced to eliminate twitching)
    window._gpsDebounceTimer = window._gpsDebounceTimer || null;
    document.addEventListener('gpsUpdated', function (e) {
        if (e.detail && e.detail.lat && e.detail.lng) {
            const liveLat = parseFloat(e.detail.lat);
            const liveLng = parseFloat(e.detail.lng);
            if (!isNaN(liveLat) && !isNaN(liveLng) && liveLat !== 0 && liveLng !== 0) {
                window.userCurrentCoords = { lat: liveLat, lng: liveLng };
                window.currentGPSLat = liveLat;
                window.currentGPSLng = liveLng;
                if (typeof window.loadNearMe === 'function') {
                    clearTimeout(window._gpsDebounceTimer);
                    window._gpsDebounceTimer = setTimeout(() => {
                        window.loadNearMe(liveLat, liveLng);
                    }, 1500);
                }
            }
        }
    });

    // Auto locate & fetch weather on startup
    setTimeout(() => {
        if (typeof window.fetchWeather === 'function') {
            window.locateMeForWeather();
        }
    }, 100);

    async function dashboardInitOnce(forceRefresh = false) {
        const setTxt = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
        const setHtml = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML = html; };
        const setSrc = (id, src) => { const el = document.getElementById(id); if (el) el.src = src; };

        var backendUrl = getDashBackendUrl();
        const token = localStorage.getItem('intan_elyu_token');
        const user = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('auth_user'), {}) : {};
        const isRealUser = Boolean(token && token !== 'guest_token' && token !== 'offline_guest_token' && token.indexOf('guest_') !== 0 && token.indexOf('offline_') !== 0 && user && user.id && user.id !== 'guest');
        if (isRealUser) {
            try {
                localStorage.removeItem('intan_elyu_guest');
                localStorage.removeItem('intan_elyu_offline_guest');
            } catch(e) {}
        }
        let isGuest = !isRealUser;

        function updateDashboardProfileHeader(isG, u) {
            const titleEl = document.getElementById('dash-title');
            const badgeEl = document.getElementById('dash-status-badge');
            const titleRow = document.getElementById('dash-title-row');
            const statsRow = document.getElementById('dash-stats-row');
            const guestSub = document.getElementById('dash-guest-subtitle');
            const guardStyle = document.getElementById('dash-guest-guard-style');

            if (isG) {
                if (guardStyle) guardStyle.textContent = '#dash-title-row, #dash-stats-row { display: none !important; } #dash-guest-subtitle { display: flex !important; }';
                if (titleEl) titleEl.style.setProperty('display', 'none', 'important');
                if (badgeEl) badgeEl.style.setProperty('display', 'none', 'important');
                if (titleRow) titleRow.style.setProperty('display', 'none', 'important');
                if (statsRow) statsRow.style.setProperty('display', 'none', 'important');
                if (guestSub) guestSub.style.setProperty('display', 'flex', 'important');

                setHtml('dash-name', 'Hi, Guest! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i>');
                setSrc('dash-avatar', 'https://ui-avatars.com/api/?name=Guest&background=1e3a8a&color=fff&rounded=true&bold=true&size=128');
            } else {
                if (guardStyle) guardStyle.textContent = '';
                if (titleEl) titleEl.style.display = 'none';
                if (badgeEl) badgeEl.style.display = 'none';
                if (titleRow) titleRow.style.display = 'none';
                if (statsRow) statsRow.style.display = 'flex';
                if (guestSub) guestSub.style.display = 'none';

                const firstName = (u && u.name ? u.name : 'Explorer').split(' ')[0];
                setHtml('dash-name', 'Hi, ' + firstName + '! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i>');
                setTxt('dash-title', 'Explorer of Elyu');
                if (u && u.avatar) {
                    setSrc('dash-avatar', u.avatar);
                } else if (u && u.name) {
                    setSrc('dash-avatar', `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);
                }
            }
        }
        window.updateDashboardProfileHeader = updateDashboardProfileHeader;

        // Apply instant profile header state
        updateDashboardProfileHeader(isGuest, user);

        // Instant render stats from cache only if NOT guest
        if (!isGuest && user && user.name) {
            const initPts = parseInt(user.points !== undefined ? user.points : (user.xp || 0)) || 0;
            const ptsEl = document.getElementById('dash-stat-points');
            if (ptsEl) ptsEl.textContent = initPts.toLocaleString();

            let initPlaces = parseInt(user.places_visited || user.completed_activities || 0);
            if (!initPlaces) {
                try {
                    const localTrips = JSON.parse(localStorage.getItem('intan_elyu_saved_trips') || localStorage.getItem('intan_elyu_offline_saved_trips_backup') || '[]');
                    const completed = localTrips.filter(t => t.status === 'completed' || t.is_completed);
                    let spotSet = new Set();
                    completed.forEach(t => (t.items || t.spots || []).forEach(s => { if (s.id || s.tourist_spot_id) spotSet.add(s.id || s.tourist_spot_id); }));
                    if (spotSet.size > 0) initPlaces = spotSet.size;
                    else if (completed.length > 0) initPlaces = completed.length;
                } catch(e) {}
            }
            const placesEl = document.getElementById('dash-stat-places');
            if (placesEl) placesEl.textContent = initPlaces.toLocaleString();

            const rankEl = document.getElementById('dash-stat-rank');
            if (rankEl) {
                const initRank = user.my_rank || user.rank || (window._cachedMyRank !== undefined ? window._cachedMyRank : null);
                if (initRank && initRank < 999) {
                    rankEl.textContent = '#' + initRank;
                } else if (initRank) {
                    rankEl.textContent = '#' + initRank;
                }
            }
        }

        // Show offline mode notification banner & toast ONLY if really offline
        if (sessionStorage.getItem('elyu_show_offline_bypass_notice') === 'true') {
            sessionStorage.removeItem('elyu_show_offline_bypass_notice');
            const isReallyOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
            if (isReallyOffline) {
                setTimeout(() => {
                    if (typeof showToast === 'function') {
                        showToast('Running in Offline Mode • Showing saved guides', 'info', 4000);
                    }
                    if (typeof window.showOfflinePill === 'function') {
                        window.showOfflinePill();
                    }
                }, 600);
            }
        }

        // ── Helper: Municipality Resolution ──
        const resolveMunicipalityName = function (dest) {
            if (!dest) return 'La Union';
            const muniMap = {
                1: 'San Fernando City', 2: 'San Juan', 3: 'Bauang', 4: 'Bacnotan',
                5: 'Balaoan', 6: 'Luna', 7: 'Bangar', 8: 'Sudipen', 9: 'Santol',
                10: 'San Gabriel', 11: 'Bagulin', 12: 'Burgos', 13: 'Naguilian',
                14: 'Caba', 15: 'Aringay', 16: 'Agoo', 17: 'Tubao', 18: 'Pugo',
                19: 'Santo Tomas', 20: 'Rosario'
            };
            let val = dest.municipality || dest.location;
            if (!val || !isNaN(Number(val))) {
                val = muniMap[dest.municipality_id] || muniMap[Number(val)] || 'San Juan, La Union';
            }
            return val;
        };
        window.resolveMunicipalityName = resolveMunicipalityName;

        // ── Helper: Recommended Item Card Builder ──
        const buildRecommendedItem = function (dest) {
            if (typeof window.buildAllSpotCard === 'function') {
                return window.buildAllSpotCard(dest);
            }
            return '';
        };
        window.buildRecommendedItem = buildRecommendedItem;

        // ── Helper: Rank Display ──
        function renderRank(rankData) {
            const el = document.getElementById('dash-stat-rank');
            if (!el) return;
            const r = rankData ? (rankData.myRank || rankData.rank || rankData.my_rank || (rankData.stats && (rankData.stats.rank || rankData.stats.myRank))) : null;
            if (r) {
                el.textContent = '#' + r;
                try {
                    localStorage.setItem('my_rank', r);
                    const authU = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    authU.my_rank = r;
                    localStorage.setItem('auth_user', JSON.stringify(authU));
                } catch (e) { }
            } else {
                const u = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('auth_user'), {}) : {};
                el.textContent = (u && (u.is_guest || u.is_offline_guest)) ? '—' : 'Unranked';
            }
        }

        // ── Helper: Saved Trips Display ──
        function renderSavedTrips(itinData) {
            const tripsContainer = document.getElementById('saved-trips-container');
            if (!tripsContainer) return;

            let serverItineraries = [];
            if (Array.isArray(itinData)) {
                serverItineraries = itinData;
            } else if (itinData && Array.isArray(itinData.itineraries)) {
                serverItineraries = itinData.itineraries;
            } else if (itinData && Array.isArray(itinData.data)) {
                serverItineraries = itinData.data;
            } else {
                try {
                    const localSaved = JSON.parse(localStorage.getItem('intan_elyu_saved_trips') || '[]');
                    if (Array.isArray(localSaved) && localSaved.length > 0) {
                        serverItineraries = localSaved;
                    }
                } catch (e) { }
            }

            const queuedTrips = (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function')
                ? window.OfflineTripManager.getQueuedTrips()
                : [];
            const isDeletedFn = (typeof window.isTripDeleted === 'function') ? window.isTripDeleted : (t => false);
            const isCompletedFn = (typeof window.isTripCompletedLocally === 'function') ? window.isTripCompletedLocally : (t => false);
            const allItineraries = [...queuedTrips, ...serverItineraries].filter(t => !isDeletedFn(t) && !isCompletedFn(t));
            const seenSig = {};
            const itineraries = allItineraries.filter(t => {
                if (!t || t.status === 'completed' || isCompletedFn(t)) return false;
                const sig = String(t.title || '').trim().toLowerCase() + '|' + (t.trip_date || '');
                if (seenSig[sig]) return false;
                seenSig[sig] = true;
                return true;
            });
            if (itineraries.length > 0) {
                let tripsHtml = '';
                itineraries.slice(0, 3).forEach(trip => {
                    let destinationsHtml = '';
                    if (trip.items && trip.items.length > 0) {
                        trip.items.forEach((item, index) => {
                            const destName = item.destination ? item.destination.name : 'Unknown Destination';
                            const queuedItem = window.OfflineCheckinManager ? window.OfflineCheckinManager.getQueuedItem(item.id) : null;
                            const isQueued = Boolean(queuedItem);

                            let proofImgHtml = '';
                            let proofImages = [];
                            if (Array.isArray(item.proof_images) && item.proof_images.length > 0) {
                                proofImages = item.proof_images;
                            } else if (item.proof_image) {
                                try {
                                    const parsed = JSON.parse(item.proof_image);
                                    if (Array.isArray(parsed)) proofImages = parsed;
                                    else proofImages = [item.proof_image];
                                } catch (e) {
                                    proofImages = [item.proof_image];
                                }
                            }
                            const spotThumb = (typeof window.getDestImage === 'function') 
                                ? window.getDestImage(item.destination, 120) 
                                : ((item.destination && (item.destination.image || item.destination.photo_url)) || 'assets/img/no_image.svg');

                            if (proofImages.length > 0) {
                                let pUrl = (typeof window.resolveProofImageUrl === 'function') ? window.resolveProofImageUrl(proofImages[0]) : proofImages[0];
                                let fallbackUrl = (typeof window.getProofImageFallbackUrl === 'function') ? window.getProofImageFallbackUrl(proofImages[0]) : 'assets/img/no_image.svg';
                                const destEscaped = destName.replace(/'/g, "\\'");
                                const resolvedAll = proofImages.map(u => (typeof window.resolveProofImageUrl === 'function' ? window.resolveProofImageUrl(u) : u));
                                const jsonUrlsAttr = JSON.stringify(resolvedAll).replace(/"/g, '&quot;');
                                proofImgHtml = `<div style="position:relative; flex-shrink:0;">
                                    <img src="${pUrl}" onerror="if(this.dataset.triedFallback!=='1'){this.dataset.triedFallback='1';this.src='${fallbackUrl}';}else{this.onerror=null;this.src='assets/img/no_image.svg';}" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal(${jsonUrlsAttr}, '${destEscaped}', '${item.is_visited ? 'Verified Visit Proof' : 'Pending Verification'}');" alt="Proof" title="Tap to view proof photo" style="width:28px; height:28px; border-radius:8px; object-fit:cover; border:1px solid ${item.is_visited ? 'rgba(52,199,89,0.5)' : (item.proof_status === 'rejected' ? 'rgba(239,68,68,0.5)' : 'rgba(255,149,0,0.5)')}; flex-shrink:0; cursor:pointer;">
                                    ${proofImages.length > 1 ? `<span style="position:absolute; bottom:0; right:0; background:rgba(0,0,0,0.75); color:#fff; font-size:6.5px; font-weight:800; padding:0 2px; border-radius:2px;">+${proofImages.length - 1}</span>` : ''}
                                </div>`;
                            } else if (isQueued && queuedItem && queuedItem.previewUrl) {
                                const destEscaped = destName.replace(/'/g, "\\'");
                                proofImgHtml = `<img src="${queuedItem.previewUrl}" onerror="this.onerror=null; this.src='assets/img/no_image.svg';" onclick="event.stopPropagation(); if(typeof window.viewProofPhotoModal==='function') window.viewProofPhotoModal('${queuedItem.previewUrl}', '${destEscaped}', 'Offline Proof (Queued on Device)');" alt="Proof" title="Tap to view offline proof photo" style="width:28px; height:28px; border-radius:8px; object-fit:cover; border:1.5px solid #f59e0b; flex-shrink:0; cursor:pointer;">`;
                            }

                            let proofBadge = '';
                            const isLocVerified = Boolean(item.is_location_verified || item.visited_at);
                            const hasProof = Boolean(proofImages.length > 0);
                            const isPhotoApproved = (item.proof_status === 'approved');
                            const isApproved = Boolean(item.is_visited || isPhotoApproved || item.proof_status === 'approved' || (item.is_visited && isLocVerified));

                            if (isQueued) {
                                proofBadge = `<span style="font-size:10px; font-weight:800; color:#fbbf24; background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.35); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-cloud-arrow-up"></i> Queued</span>`;
                            } else if (isApproved) {
                                proofBadge = `<span style="font-size:10px; font-weight:800; color:#34c759; background:rgba(52,199,89,0.15); border:1px solid rgba(52,199,89,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Verified</span>`;
                            } else if (isPhotoApproved && !isLocVerified) {
                                proofBadge = `<span style="font-size:10px; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-camera"></i> Photo Approved</span>`;
                            } else if (item.proof_status === 'rejected') {
                                proofBadge = `<span style="font-size:10px; font-weight:800; color:#ef4444; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>`;
                            } else if (isLocVerified && hasProof) {
                                proofBadge = `<span style="font-size:10px; font-weight:800; color:#FF9500; background:rgba(255,149,0,0.15); border:1px solid rgba(255,149,0,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-clock"></i> Pending</span>`;
                            } else if (isLocVerified && !hasProof) {
                                proofBadge = `<span style="font-size:10px; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-location-dot"></i> Checked In</span>`;
                            } else if (!isLocVerified && hasProof) {
                                proofBadge = `<span style="font-size:10px; font-weight:800; color:#a78bfa; background:rgba(167,139,250,0.15); border:1px solid rgba(167,139,250,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-camera"></i> Photo Ready</span>`;
                            }

                            destinationsHtml += `
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; padding:8px 12px; background:rgba(0,0,0,0.12); border:none; border-radius:12px;">
                                <div style="width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg, #38bdf8, #2563eb); color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:900; flex-shrink:0; box-shadow:none;">${index + 1}</div>
                                <img src="${spotThumb}" alt="${destName}" style="width:28px; height:28px; border-radius:8px; object-fit:cover; border:1px solid rgba(255,255,255,0.2); flex-shrink:0;" onerror="this.onerror=null; this.src='assets/img/no_image.svg';">
                                <div style="flex:1; font-size:13px; color:#ffffff; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${destName}</div>
                                ${proofImgHtml}
                                ${proofBadge}
                            </div>
                        `;
                        });
                    } else {
                        destinationsHtml = '<div style="font-size:12px; color:rgba(255,255,255,0.7); font-style:italic; text-align:center; padding:10px 0;">No destinations added yet.</div>';
                    }

                    const safeTitle = (trip.title || 'Trip').replace(/'/g, "\\'");
                    tripsHtml += `
                    <div class="trip-swipe-container" data-trip-id="${trip.id}" data-trip-title="${safeTitle}" style="margin-bottom: 14px; position: relative; overflow: hidden; border-radius: 20px; -webkit-mask-image: -webkit-radial-gradient(white, black); mask-image: radial-gradient(white, black); isolation: isolate; contain: paint;">
                        <div class="trip-swipe-bg" onclick="window.confirmDeleteSavedTrip('${trip.id}', this.closest('.trip-swipe-container'), '${safeTitle}')" style="position: absolute; top: 0; right: 0; bottom: 0; width: 85px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 0 20px 20px 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 800; gap: 4px; z-index: 1; opacity: 0; pointer-events: none; transform: translateX(85px); transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease; cursor: pointer;">
                            <i class="fa-solid fa-trash-can"></i> Delete
                        </div>
                        <div class="trip-swipe-content" style="position: relative; z-index: 2; transition: transform 0.2s ease, border-radius 0.2s ease; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;">
                            <div onclick="const content = this.nextElementSibling; const icon = this.querySelector('.toggle-icon'); if(content.style.maxHeight === '0px' || !content.style.maxHeight){ content.style.paddingTop = '14px'; content.style.paddingBottom = '16px'; content.style.maxHeight = (content.scrollHeight + 50) + 'px'; content.style.opacity = '1'; icon.style.transform = 'rotate(90deg)'; } else { content.style.maxHeight = '0px'; content.style.opacity = '0'; content.style.paddingTop = '0'; content.style.paddingBottom = '0'; icon.style.transform = 'rotate(0deg)'; }" style="cursor:pointer; display:flex; align-items:center; gap: 14px; padding: 16px; transition: background 0.15s;" onpointerdown="this.style.background='rgba(255,255,255,0.08)'" onpointerup="this.style.background=''" onpointercancel="this.style.background=''">
                                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255, 255, 255, 0.22); border: none; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: none;">
                                    <i class="fa-solid fa-map-location-dot" style="color: #ffffff; font-size: 20px;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <span style="display:block; font-size:16px; font-weight:800; color:#ffffff; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.3px;">${trip.title}</span>
                                    <span style="display:flex; align-items:center; gap:8px; font-size:12px; color:rgba(255,255,255,0.9); font-weight:600; flex-wrap:wrap;">
                                        <span><i class="fa-solid fa-location-dot" style="margin-right:4px; color:#ffffff;"></i>${trip.items ? trip.items.length : 0} Stops</span>
                                        <span style="color:rgba(255,255,255,0.4);">&bull;</span>
                                        <span><i class="fa-regular fa-calendar" style="margin-right:4px; color:#ffffff;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No Date'}</span>
                                        ${trip.transport_mode ? (() => {
                                            const parts = String(trip.transport_mode).split(/[,+]|\band\b/i).map(s => s.trim().toLowerCase().replace(/[- ]/g, '_')).filter(Boolean);
                                            if (parts.length === 0 || parts[0].includes('no_vehicle') || parts[0].includes('no vehicle')) {
                                                return `<span style="color:rgba(255,255,255,0.4);">&bull;</span><span><i class="fa-solid fa-ban" style="margin-right:4px; color:#f87171;"></i>No Vehicle Selected</span>`;
                                            }
                                            if (parts.length > 1) {
                                                return `<span style="color:rgba(255,255,255,0.4);">&bull;</span><span><i class="fa-solid fa-route" style="margin-right:4px; color:#38bdf8;"></i>${parts.length} Vehicles</span>`;
                                            }
                                            const rawT = parts[0];
                                            const tMap = {
                                                'own_car': 'Own Car', 'mpuj': 'MPUJ', 'tpuj': 'TPUJ', 'pub_aircon': 'PUB Aircon',
                                                'pub_ordinary': 'PUB Ordinary', 'pub_regular': 'PUB Regular', 'uve': 'UV Express',
                                                'jeepney': 'Jeepney', 'tricycle': 'Tricycle', 'bus': 'Bus', 'private_bus': 'PUB Aircon',
                                                'mini_bus': 'Mini Bus', 'lutrampco': 'LUTRAMPCO', 'taxi': 'Taxi', 'motorcycle': 'Motorcycle', 'walking': 'Walking'
                                            };
                                            const tIco = {
                                                'own_car': 'fa-car', 'mpuj': 'fa-van-shuttle', 'tpuj': 'fa-van-shuttle', 'pub_aircon': 'fa-bus',
                                                'pub_ordinary': 'fa-bus-simple', 'pub_regular': 'fa-bus-simple', 'uve': 'fa-van-shuttle',
                                                'jeepney': 'fa-van-shuttle', 'tricycle': 'fa-motorcycle', 'bus': 'fa-bus', 'private_bus': 'fa-bus',
                                                'mini_bus': 'fa-bus-simple', 'lutrampco': 'fa-van-shuttle', 'taxi': 'fa-taxi', 'motorcycle': 'fa-motorcycle', 'walking': 'fa-person-walking'
                                            };
                                            const displayName = tMap[rawT] || rawT.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                            return `<span style="color:rgba(255,255,255,0.4);">&bull;</span><span><i class="fa-solid ${tIco[rawT] || 'fa-car'}" style="margin-right:4px; color:#38bdf8;"></i>${displayName}</span>`;
                                        })() : ''}
                                        ${trip.trip_type === 'group' || (trip.total_pax && trip.total_pax > 1) 
                                            ? `<span style="color:rgba(255,255,255,0.4);">&bull;</span><span><i class="fa-solid fa-users" style="margin-right:4px; color:#38bdf8;"></i>${trip.total_pax} Pax${(trip.companion_males || trip.companion_females) ? ` (${trip.companion_males || 0}M, ${trip.companion_females || 0}F)` : ''}</span>`
                                            : `<span style="color:rgba(255,255,255,0.4);">&bull;</span><span><i class="fa-solid fa-user" style="margin-right:4px; color:#38bdf8;"></i>Solo</span>`}
                                    </span>
                                </div>
                                <i class="fa-solid fa-chevron-right toggle-icon" style="color: rgba(255,255,255,0.7); font-size: 14px; margin-right:4px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></i>
                            </div>
                            <div style="max-height: 0px; opacity: 0; padding: 0 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; background: transparent; border-top: none;">
                                ${destinationsHtml}
                                <div style="display:flex; gap:10px; margin-top:12px; margin-bottom:2px;">
                                    <button onclick="if(window.sessionStorage){sessionStorage.setItem('active_trip_transport_${trip.id}', '${trip.transport_mode || ''}'); localStorage.setItem('selected_trip_vehicle_${trip.id}', '${trip.transport_mode || ''}');} window.location.href='?view=trip_map&trip_id=${trip.id}${trip.transport_mode ? '&transport=' + encodeURIComponent(trip.transport_mode) : ''}'" style="flex:1; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none; outline:none; color:#ffffff; padding:12px; border-radius:14px; font-weight:800; font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:none;">
                                        <i class="fa-solid fa-compass"></i> Start Trip
                                    </button>
                                    <button onclick="navigateTo('saved_trips')" style="flex:1; background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; padding:12px; border-radius:14px; font-weight:800; font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 2px 8px rgba(0,0,0,0.15) !important;">
                                        <i class="fa-solid fa-route" style="color:#1e3a8a !important;"></i> View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                });
                tripsContainer.innerHTML = tripsHtml;
                if (typeof window.setupDashboardSwipeToDelete === 'function') {
                    window.setupDashboardSwipeToDelete();
                }
            } else {
                tripsContainer.innerHTML = `
                <div class="dash-empty-state">
                    <div class="dash-empty-icon-wrap" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                        <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                    </div>
                    <div class="dash-empty-title">No Saved Trips Yet</div>
                    <div class="dash-empty-desc">Create custom itineraries and explore recommended routes for your La Union adventure.</div>
                    <button type="button" onclick="navigateTo('itinerary')" class="dash-empty-btn">
                        <i class="fa-solid fa-plus"></i> Plan a Trip
                    </button>
                </div>
            `;
            }
        }

        // ── Helper: Graceful Empty/Error Clearer ──
        function handleDashboardEmptyOrError() {
            const trendEl = document.getElementById('trending-container');
            if (trendEl && trendEl.querySelector('.dash-loading-state')) {
                if (window.allTouristSpots && window.allTouristSpots.length > 0) {
                    const topSpots = [...window.allTouristSpots].sort((a,b) => (b.visits || 0) - (a.visits || 0)).slice(0, 10);
                    window.dashTrending = topSpots;
                    trendEl.classList.remove('is-empty');
                    trendEl.style.paddingLeft = '';
                    trendEl.style.paddingRight = '';
                    trendEl.style.marginLeft = '';
                    trendEl.style.marginRight = '';
                    trendEl.innerHTML = '';
                    topSpots.forEach(dest => {
                        const img = window.getDestImage(dest, 600);
                        const encodedDest = encodeURIComponent(JSON.stringify(dest));
                        const muni = dest.municipality || dest.location || '';
                        trendEl.innerHTML += `
                        <div class="fav-card" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                            <img src="${img}" alt="${dest.name}" loading="lazy" decoding="async">
                            <div class="fav-card-overlay">
                                <span class="fav-card-name">${dest.name}</span>
                                ${muni ? `<span style="display:block; font-size:10px; color:#38bdf8; margin-top:2px; font-weight:700;"><i class="fa-solid fa-location-dot"></i> ${muni}</span>` : ''}
                            </div>
                            </div>`;
                    });
                    window.initLoopingFocusCarousel('trending-container');
                } else {
                    trendEl.classList.add('is-empty');
                    trendEl.innerHTML = `
                    <div class="dash-empty-state">
                        <div class="dash-empty-icon-wrap"><i class="fa-solid fa-fire-flame-curved"></i></div>
                        <div class="dash-empty-title">No Trending Spots</div>
                        <div class="dash-empty-desc">Check back soon for popular attractions and trending activities in La Union.</div>
                    </div>`;
                }
            }

            const savedEl = document.getElementById('saved-places-container');
            if (savedEl && savedEl.querySelector('.dash-loading-state')) {
                savedEl.classList.add('is-empty');
                savedEl.innerHTML = `
                <div class="dash-empty-state">
                    <div class="dash-empty-icon-wrap"><i class="fa-solid fa-map-location-dot"></i></div>
                    <div class="dash-empty-title">No Saved Places Yet</div>
                    <div class="dash-empty-desc">Discover destinations on the map and tap the heart icon to save your favorite spots.</div>
                    <button type="button" onclick="navigateTo('map')" class="dash-empty-btn"><i class="fa-solid fa-location-arrow"></i> Open Map</button>
                </div>`;
            }

            const recEl = document.getElementById('recommended-container');
            if (recEl && recEl.querySelector('.dash-loading-state')) {
                if (window.allTouristSpots && window.allTouristSpots.length > 0) {
                    const allDests = [...window.allTouristSpots].sort((a,b) => parseFloat(b.rating || 0) - parseFloat(a.rating || 0)).slice(0, 5);
                    window.dashRecommended = allDests;
                    const INITIAL_SHOW = 2;
                    let html = allDests.slice(0, INITIAL_SHOW).map(dest => buildRecommendedItem(dest)).join('');
                    if (allDests.length > INITIAL_SHOW) {
                        const extras = allDests.slice(INITIAL_SHOW);
                        html += `
                        <div id="rec-extras" style="overflow:hidden; max-height:0; transition: max-height 0.4s ease;">
                            ${extras.map(dest => buildRecommendedItem(dest)).join('')}
                        </div>
                        <button id="btn-view-more-rec"
                            onclick="window.toggleRecommendedMore()"
                            style="width:100%; margin-top:10px; padding:12px; border-radius:14px; border: none; outline:none; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%); color:#ffffff; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.25); transition: all 0.2s;">
                            <i class="fa-solid fa-chevron-down" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i>
                            View ${extras.length} More
                        </button>`;
                    }
                    recEl.innerHTML = html;
                } else {
                    recEl.innerHTML = `
                    <div class="dash-empty-state">
                        <div class="dash-empty-icon-wrap"><i class="fa-solid fa-compass"></i></div>
                        <div class="dash-empty-title">No Recommendations</div>
                        <div class="dash-empty-desc">Explore more tourist spots to receive customized recommendations.</div>
                    </div>`;
                }
            }

            const tripsEl = document.getElementById('saved-trips-container');
            if (tripsEl && tripsEl.querySelector('.dash-loading-state')) {
                tripsEl.innerHTML = `
                <div class="dash-empty-state">
                    <div class="dash-empty-icon-wrap" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                        <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                    </div>
                    <div class="dash-empty-title">No Saved Trips Yet</div>
                    <div class="dash-empty-desc">Create custom itineraries and explore recommended routes for your La Union adventure.</div>
                    <button type="button" onclick="navigateTo('itinerary')" class="dash-empty-btn">
                        <i class="fa-solid fa-plus"></i> Plan a Trip
                    </button>
                </div>`;
            }

            const allSpotsEl = document.getElementById('all-spots-container');
            if (allSpotsEl && allSpotsEl.querySelector('.dash-loading-state')) {
                if (window.allTouristSpots && window.allTouristSpots.length > 0) {
                    if (typeof window.renderAllTouristSpots === 'function') {
                        window.renderAllTouristSpots(window.allTouristSpots);
                    }
                } else {
                    allSpotsEl.innerHTML = `
                    <div class="dash-empty-state" style="margin-top: 6px !important;">
                        <div class="dash-empty-icon-wrap"><i class="fa-solid fa-compass"></i></div>
                        <div class="dash-empty-title">Tourist Sites</div>
                        <div class="dash-empty-desc">Explore destinations across La Union on the interactive map.</div>
                        <button type="button" onclick="navigateTo('map')" class="dash-empty-btn"><i class="fa-solid fa-map-location-dot"></i> Open Map</button>
                    </div>`;
                }
            }
        }

        // Pre-hydrate Rank and Saved Trips from local caches if present
        if (token) {
            const rankCacheKey = 'dashboard_rank_' + token.substring(0, 10);
            try {
                const cachedRank = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem(rankCacheKey), null)?.data : null;
                if (cachedRank) renderRank(cachedRank);
            } catch (e) {}

            const tripsCacheKey = 'dashboard_trips_' + token.substring(0, 10);
            try {
                const cachedTrips = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem(tripsCacheKey), null)?.data : null;
                if (cachedTrips) renderSavedTrips(cachedTrips);
            } catch (e) {}
        }

        // Early fetch for tourist spots
        if (typeof window.fetchAllTouristSpots === 'function') {
            window.fetchAllTouristSpots();
        }

        // Non-blocking coordinate resolution
        let currentCoords = window.userCurrentCoords || (window.currentGPSLat && window.currentGPSLng ? { lat: window.currentGPSLat, lng: window.currentGPSLng } : null);
        let lat = currentCoords ? currentCoords.lat : null;
        let lng = currentCoords ? currentCoords.lng : null;

        if ((!lat || !lng) && typeof window.requestPreciseLocation === 'function') {
            window.requestPreciseLocation(false).then(loc => {
                if (loc && loc.lat && loc.lng) {
                    window.userCurrentCoords = { lat: loc.lat, lng: loc.lng };
                    window.currentGPSLat = loc.lat;
                    window.currentGPSLng = loc.lng;
                    window.currentGPSSource = 'gps';
                    if (typeof window.loadNearMe === 'function') window.loadNearMe(loc.lat, loc.lng);
                    if (typeof window.fetchWeather === 'function') window.fetchWeather(loc.lat, loc.lng, 'My Location', true);
                }
            }).catch(() => {});
        }

        let apiUrl = backendUrl + '/api/tourist/dashboard?limit=50';
        if (lat && lng) apiUrl += `&lat=${lat}&lng=${lng}`;

        // Stable User-Centric Cache Key
        const cacheKey = 'dashboard_data_v4_' + (user.id || (token ? token.substring(0, 10) : 'default'));

        function renderDashboard(data) {
            const rawTrending = (data.trending || []).filter(d => !d.status || d.status.toLowerCase() !== 'pending');
            const validTrending = rawTrending.filter(d => (parseFloat(d.rating || 0) > 0) || (parseInt(d.visits || 0, 10) > 0));
            window.dashTrending = validTrending.length > 0 ? validTrending : rawTrending;
            window.dashRecommended = (data.recommended || []).filter(d => !d.status || d.status.toLowerCase() !== 'pending');
            window.dashSaved = (data.savedPlaces || []).filter(d => !d.status || d.status.toLowerCase() !== 'pending');

            // Update notification badge
            if (data.stats && typeof window.updateUnreadBadge === 'function') {
                window.updateUnreadBadge(data.stats.unread_notifications || 0);
            }

            const u = data.user || {};
            const isGuestSession = !token || 
                token === 'guest_token' || 
                token === 'offline_guest_token' || 
                token.indexOf('guest_') === 0 || 
                token.indexOf('offline_') === 0 || 
                Boolean(u && (u.is_guest || u.is_offline_guest || u.id === 'guest'));

            if (typeof updateDashboardProfileHeader === 'function') {
                updateDashboardProfileHeader(isGuestSession, u);
            }

            if (!isGuestSession) {
                // Profile header
                const firstName = (u.name || 'Explorer').split(' ')[0];
                setHtml('dash-name', 'Hi, ' + firstName + '! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i>');
                setTxt('dash-title', 'Explorer of Elyu');

                const touristNum = u.tourist_number || u.tourist_id || (u.role === 'tourist' ? 1 : (u.id || u.user_id || ''));
                if (touristNum) {
                    try {
                        const storedUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                        storedUser.tourist_number = touristNum;
                        storedUser.tourist_id = touristNum;
                        localStorage.setItem('auth_user', JSON.stringify(storedUser));
                    } catch (e) { }
                }

                if (u.avatar) {
                    let avatarUrl = u.avatar;
                    if (avatarUrl.includes('localhost:3000') || avatarUrl.includes('127.0.0.1:3000')) {
                        avatarUrl = avatarUrl.replace(/http:\/\/(localhost|127\.0\.0\.1):3000/, window.backendUrl || 'http://localhost:8000');
                    }
                    if (!avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:') && !avatarUrl.startsWith('blob:')) {
                        let b = (window.backendUrl || '').replace(/\/+$/, '');
                        avatarUrl = b + '/' + avatarUrl.replace(/^\//, '');
                    }

                    const dashAvatarEl = document.getElementById('dash-avatar');
                    if (dashAvatarEl) {
                        let fallbackAvatar = (window.backendUrl || '').replace(/\/+$/, '') + '/api/image/' + u.avatar.replace(/^\//, '');
                        dashAvatarEl.onerror = function () {
                            if (this.src !== fallbackAvatar) {
                                this.src = fallbackAvatar;
                            } else {
                                this.onerror = null;
                                this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                            }
                        };
                        dashAvatarEl.src = avatarUrl;
                    }
                } else {
                    setSrc('dash-avatar', `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);
                }

                // Stats
                let visitedCount = 0;
                if (data.stats && (data.stats.placesVisited !== undefined && data.stats.placesVisited !== null)) {
                    visitedCount = parseInt(data.stats.placesVisited, 10);
                }
                if (visitedCount === 0 && u) {
                    visitedCount = parseInt(u.places_visited || u.completed_activities || 0, 10);
                }
                if (visitedCount === 0) {
                    try {
                        const localTrips = JSON.parse(localStorage.getItem('intan_elyu_saved_trips') || localStorage.getItem('intan_elyu_offline_saved_trips_backup') || '[]');
                        const completed = localTrips.filter(t => t.status === 'completed' || t.is_completed);
                        let spotSet = new Set();
                        completed.forEach(t => (t.items || t.spots || []).forEach(s => { if (s.id || s.tourist_spot_id) spotSet.add(s.id || s.tourist_spot_id); }));
                        if (spotSet.size > 0) visitedCount = spotSet.size;
                        else if (completed.length > 0) visitedCount = completed.length;
                    } catch(e) {}
                }
                const points = parseInt((u && (u.points !== undefined ? u.points : u.xp)) || (data && data.stats && (data.stats.points !== undefined ? data.stats.points : data.stats.xp)) || 0, 10);
                if (document.getElementById('dash-stat-places')) document.getElementById('dash-stat-places').textContent = visitedCount.toLocaleString();
                if (document.getElementById('dash-stat-points')) document.getElementById('dash-stat-points').textContent = points.toLocaleString();

                const rVal = (data && (data.myRank || data.my_rank || (data.stats && (data.stats.rank || data.stats.myRank))));
                const rankEl = document.getElementById('dash-stat-rank');
                if (rankEl) {
                    if (rVal && rVal < 999) {
                        rankEl.textContent = '#' + rVal;
                        try {
                            localStorage.setItem('my_rank', rVal);
                            const authU = JSON.parse(localStorage.getItem('auth_user') || '{}');
                            authU.my_rank = rVal;
                            localStorage.setItem('auth_user', JSON.stringify(authU));
                        } catch (e) { }
                    } else if (rVal) {
                        rankEl.textContent = '#' + rVal;
                    }
                }
            }

            // Populate Trending Spots (Limit to top 5, "See All" opens full list)
            const trendingContainer = document.getElementById('trending-container');
            if (trendingContainer) {
                trendingContainer.innerHTML = '';
                if (data.trending && data.trending.length > 0) {
                    trendingContainer.classList.remove('is-empty');
                    trendingContainer.style.paddingLeft = '';
                    trendingContainer.style.paddingRight = '';
                    trendingContainer.style.marginLeft = '';
                    trendingContainer.style.marginRight = '';
                    const trendingList = data.trending.slice(0, 10);
                    trendingContainer.innerHTML = trendingList.map(window.renderFavCard || renderFavCard).join('');
                    window.initLoopingFocusCarousel('trending-container');
                } else {
                    trendingContainer.classList.add('is-empty');
                    trendingContainer.style.paddingLeft = '0';
                    trendingContainer.style.paddingRight = '0';
                    trendingContainer.style.marginLeft = '0';
                    trendingContainer.style.marginRight = '0';
                    trendingContainer.innerHTML = `
                    <div class="dash-empty-state">
                        <div class="dash-empty-icon-wrap">
                            <i class="fa-solid fa-fire-flame-curved"></i>
                        </div>
                        <div class="dash-empty-title">No Trending Spots</div>
                        <div class="dash-empty-desc">Check back soon for popular attractions and trending activities in La Union.</div>
                    </div>
                `;
                }
            }

            // Populate Saved Places (Limit to top 5, "See All" opens full list)
            const savedContainer = document.getElementById('saved-places-container');
            if (savedContainer) {
                savedContainer.innerHTML = '';
                if (data.savedPlaces && data.savedPlaces.length > 0) {
                    try {
                        const savedIds = data.savedPlaces.map(p => p.id);
                        localStorage.setItem('intan_elyu_saved_place_ids', JSON.stringify(savedIds));
                    } catch (e) { }

                    savedContainer.classList.remove('is-empty');
                    savedContainer.style.paddingLeft = '';
                    savedContainer.style.paddingRight = '';
                    savedContainer.style.marginLeft = '';
                    savedContainer.style.marginRight = '';
                    const savedList = data.savedPlaces.slice(0, 5);
                    savedList.forEach(dest => {
                        const img = window.getDestImage(dest, 600);
                        const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#0284c7' : (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                        const encodedDest = encodeURIComponent(JSON.stringify(dest));
                        savedContainer.innerHTML += `
                        <div class="fav-card" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" data-name="${(dest.name || '').replace(/"/g, '&quot;')}" data-municipality="${(dest.municipality || dest.location || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                            ${badgeHtml}
                            <img src="${img}" alt="${dest.name}" loading="lazy" decoding="async" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\\'")}', '${(dest.municipality || '').replace(/'/g, "\\'")}'); else this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                            <div class="fav-card-overlay"><span class="fav-card-name">${dest.name}</span></div>
                            </div>
                    `;
                    });
                    window.initLoopingFocusCarousel('saved-places-container');
                } else {
                    savedContainer.classList.add('is-empty');
                    savedContainer.style.paddingLeft = '0';
                    savedContainer.style.paddingRight = '0';
                    savedContainer.style.marginLeft = '0';
                    savedContainer.style.marginRight = '0';
                    savedContainer.innerHTML = `
                    <div class="dash-empty-state">
                        <div class="dash-empty-icon-wrap">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div class="dash-empty-title">No Saved Places Yet</div>
                        <div class="dash-empty-desc">Discover destinations on the map and tap the heart icon to save your favorite spots.</div>
                        <button type="button" onclick="navigateTo('map')" class="dash-empty-btn">
                            <i class="fa-solid fa-location-arrow"></i> Open Map
                        </button>
                    </div>
                `;
                }
            }

            const recContainer = document.getElementById('recommended-container');
            if (recContainer && data.recommended) {
                recContainer.innerHTML = '';
                if (data.timeLabel) {
                    setTxt('recommended-title', data.timeLabel);
                }

                const INITIAL_SHOW = 2;
                const allDests = data.recommended;

                // Render first 2
                allDests.slice(0, INITIAL_SHOW).forEach(dest => {
                    recContainer.innerHTML += buildRecommendedItem(dest);
                });

                // Hidden extras container
                if (allDests.length > INITIAL_SHOW) {
                    const extras = allDests.slice(INITIAL_SHOW);
                    const extraHtml = extras.map(dest => buildRecommendedItem(dest)).join('');

                    recContainer.innerHTML += `
                    <div id="rec-extras" style="overflow:hidden; max-height:0; transition: max-height 0.4s ease;">
                        ${extraHtml}
                    </div>
                    <button id="btn-view-more-rec"
                        onclick="window.toggleRecommendedMore()"
                        style="width:100%; margin-top:10px; padding:12px; border-radius:14px; border: none; outline:none; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%); color:#ffffff; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow: 0 4px 12px rgba(32, 63, 141, 0.25); transition: all 0.2s;">
                        <i class="fa-solid fa-chevron-down" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i>
                        View ${extras.length} More
                    </button>
                `;
                }
            }

            // Cache updated user
            localStorage.setItem('auth_user', JSON.stringify(u));
        }
        window.renderDashboard = renderDashboard;

        // ── Instant Synchronous Pre-Hydration of Sections (0ms, zero spinner delay!) ──
        const cachedPayload = window._lastDashboardPayload ||
            (window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('dashboard_cached_payload'), null) : null) ||
            (window.safeJsonParse ? window.safeJsonParse(localStorage.getItem(cacheKey), null)?.data : null);

        if (cachedPayload && (cachedPayload.trending || cachedPayload.recommended || cachedPayload.savedPlaces) && (!cachedPayload.trending || cachedPayload.trending.length >= 10)) {
            renderDashboard(cachedPayload);
        } else {
            // Invalidate any old cache with 5 or fewer trending spots
            try { localStorage.removeItem('dashboard_cached_payload'); } catch(e) {}
            // Check fallback from public_map_data or cached spots map
            try {
                const mapCache = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('public_map_data'), null)?.data : null;
                const spots = (mapCache && mapCache.destinations) || (window.allTouristSpots && window.allTouristSpots.length > 0 ? window.allTouristSpots : null);
                if (spots && spots.length > 0) {
                    const activeSpots = spots.filter(d => !d.status || !['pending', 'draft', 'rejected'].includes(d.status.toLowerCase()));
                    renderDashboard({
                        user: user,
                        stats: { placesVisited: 0, rank: 1, unread_notifications: 0 },
                        trending: (() => {
                            const candidates = [...activeSpots].filter(d => (parseFloat(d.rating || 0) > 0) || ((d.visits || 0) > 0));
                            const pool = candidates.length > 0 ? candidates : activeSpots;
                            return [...pool].sort((a, b) => ((b.visits || 0) + (parseFloat(b.rating || 0) * 10)) - ((a.visits || 0) + (parseFloat(a.rating || 0) * 10))).slice(0, 10);
                        })(),
                        savedPlaces: [],
                        recommended: [...activeSpots].sort((a, b) => (parseFloat(b.rating || 0)) - (parseFloat(a.rating || 0))).slice(0, 10),
                        timeLabel: 'Popular in La Union'
                    });
                }
            } catch (e) {
                console.warn('Fallback pre-render handled:', e);
            }
        }

        // Helper functions used inside render/build
        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        let _lastNearMeLat = null;
        let _lastNearMeLng = null;
        let _renderedNearMuniHeader = null;

        async function loadNearMe(userLat, userLng, preferredLocationName) {
            const nearContainer = document.getElementById('near-me-container');
            if (!nearContainer) return;

            const uLat = parseFloat(userLat);
            const uLng = parseFloat(userLng);

            if (isNaN(uLat) || isNaN(uLng) || uLat === 0 || uLng === 0) {
                if (nearContainer.children.length === 0 || nearContainer.classList.contains('is-empty')) {
                    nearContainer.classList.add('is-empty');
                    nearContainer.style.paddingLeft = '0';
                    nearContainer.style.paddingRight = '0';
                    nearContainer.style.marginLeft = '0';
                    nearContainer.style.marginRight = '0';
                    nearContainer.innerHTML = `
                    <div class="dash-empty-state">
                        <div class="dash-empty-icon-wrap">
                            <i class="fa-solid fa-location-crosshairs"></i>
                        </div>
                        <div class="dash-empty-title">Location Access Needed</div>
                        <div class="dash-empty-desc">Enable location access to discover tourist spots in your location.</div>
                        <button type="button" onclick="if(window.locateMeForWeather) window.locateMeForWeather();" class="dash-empty-btn">
                            <i class="fa-solid fa-location-arrow"></i> Enable Location
                        </button>
                    </div>
                `;
                }
                return;
            }

            // Identify user's municipality in La Union
            const munis = [
                { name: 'San Fernando City', lat: 16.6159, lng: 120.3167 },
                { name: 'San Juan', lat: 16.671123, lng: 120.338487 },
                { name: 'Bauang', lat: 16.5319, lng: 120.3298 },
                { name: 'Bacnotan', lat: 16.7202, lng: 120.3353 },
                { name: 'Balaoan', lat: 16.8228, lng: 120.4005 },
                { name: 'Luna', lat: 16.8554, lng: 120.3758 },
                { name: 'Bangar', lat: 16.8942, lng: 120.4245 },
                { name: 'Sudipen', lat: 16.9031, lng: 120.4700 },
                { name: 'Santol', lat: 16.7686, lng: 120.4578 },
                { name: 'San Gabriel', lat: 16.6711, lng: 120.4050 },
                { name: 'Bagulin', lat: 16.6072, lng: 120.4422 },
                { name: 'Burgos', lat: 16.5183, lng: 120.4578 },
                { name: 'Naguilian', lat: 16.5366, lng: 120.3926 },
                { name: 'Caba', lat: 16.4292, lng: 120.3344 },
                { name: 'Aringay', lat: 16.3958, lng: 120.3325 },
                { name: 'Agoo', lat: 16.3217, lng: 120.3667 },
                { name: 'Tubao', lat: 16.3470, lng: 120.4126 },
                { name: 'Pugo', lat: 16.3167, lng: 120.4667 },
                { name: 'Santo Tomas', lat: 16.2842, lng: 120.3861 },
                { name: 'Rosario', lat: 16.2286, lng: 120.4850 }
            ];

            let detectedMuni = preferredLocationName || null;
            let closestMuni = null;
            let minMuniDistance = 999999;
            munis.forEach(m => {
                const dist = getDistance(uLat, uLng, m.lat, m.lng);
                if (dist < minMuniDistance) {
                    minMuniDistance = dist;
                    closestMuni = m;
                }
            });

            if (!detectedMuni && closestMuni) {
                detectedMuni = closestMuni.name;
            }
            window.userCurrentMunicipality = detectedMuni;

            // Update Section Header only if value changed (avoids reflow & twitching)
            const cleanName = detectedMuni ? detectedMuni.replace(' City', '').replace(', La Union', '') : '';
            if (cleanName && _renderedNearMuniHeader !== cleanName) {
                _renderedNearMuniHeader = cleanName;
                const nearSectionHeader = document.querySelector('#near-me-container')?.closest('.dash-section')?.querySelector('.section-title h3');
                if (nearSectionHeader) {
                    nearSectionHeader.innerHTML = `Near Me <span class="dash-count-pill" style="font-size:11px; font-weight:800; color:#1e3a8a !important; margin-left:6px; background:#ffffff !important; border:none !important; outline:none !important; padding:3px 10px; border-radius:100px; box-shadow:0 1px 4px rgba(0,0,0,0.14); display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-location-dot" style="color:#0284c7 !important; font-size:9.5px;"></i> ${cleanName}</span>`;
                }
            }

            const cacheKey = 'public_map_data';
            await window.useCache(
                cacheKey,
                async () => {
                    const res = await fetch(backendUrl + '/api/public/map');
                    if (!res.ok) throw new Error("Failed to fetch map data");
                    return await res.json();
                },
                (data) => {
                    // Check fallback if data is missing or offline
                    if (!data || !data.destinations) {
                        try {
                            const fallbackRaw = localStorage.getItem('intan_elyu_cached_spots_map');
                            if (fallbackRaw) {
                                const parsed = JSON.parse(fallbackRaw)?.data;
                                if (parsed && (parsed.destinations || parsed.spots)) {
                                    data = { destinations: parsed.destinations || parsed.spots };
                                }
                            }
                        } catch(e) {}
                    }

                    if (!data || !data.destinations) {
                        nearContainer.classList.add('is-empty');
                        nearContainer.style.paddingLeft = '0';
                        nearContainer.style.paddingRight = '0';
                        nearContainer.style.marginLeft = '0';
                        nearContainer.style.marginRight = '0';
                        nearContainer.innerHTML = `
                        <div class="dash-empty-state">
                            <div class="dash-empty-icon-wrap">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div class="dash-empty-title">Unable to Load Nearby Spots</div>
                            <div class="dash-empty-desc">Could not load spots at this moment. Please check your connection.</div>
                            <button type="button" onclick="loadNearMe(window.userCurrentCoords?.lat || window.currentGPSLat, window.userCurrentCoords?.lng || window.currentGPSLng)" class="dash-empty-btn">
                                <i class="fa-solid fa-rotate-right"></i> Retry
                            </button>
                        </div>
                    `;
                        return;
                    }

                    // Clone spots array so we do not mutate cached data in-place
                    let rawSpots = (data.destinations || []).filter(s => !s.status || s.status.toLowerCase() !== 'pending');
                    let spots = rawSpots.map(s => {
                        const copy = { ...s };
                        const sLat = parseFloat(copy.lat || copy.latitude);
                        const sLng = parseFloat(copy.lng || copy.longitude);
                        if (!isNaN(sLat) && !isNaN(sLng)) {
                            copy.distance = getDistance(uLat, uLng, sLat, sLng);
                        } else {
                            copy.distance = 999999;
                        }
                        return copy;
                    });

                    // Sort by nearest distance first
                    spots.sort((a, b) => a.distance - b.distance);

                    // User radius requirement: strictly 1km limit!
                    let nearSpots = spots.filter(s => {
                        if (s.distance === 999999) return false;
                        return s.distance <= 1.0;
                    });

                    window.allNearSpots = nearSpots;
                    window.lastNearSpots = nearSpots;

                    if (nearSpots.length > 0) {
                        // Check if exact same spots are already rendered in container
                        const existingCards = nearContainer.querySelectorAll('.fav-card');
                        const existingIds = Array.from(existingCards).map(c => c.getAttribute('data-id')).filter(Boolean).join(',');
                        const newIds = nearSpots.map(s => s.id).join(',');

                        if (existingIds === newIds && existingIds !== '') {
                            // Exact same list of spots: Update distance labels in-place with NO twitching!
                            nearSpots.forEach(dest => {
                                const card = nearContainer.querySelector(`.fav-card[data-id="${dest.id}"]`);
                                if (card) {
                                    let distText = '';
                                    if (dest.distance < 0.05) {
                                        distText = `${Math.max(5, Math.round(dest.distance * 1000))}m away`;
                                    } else if (dest.distance < 1.0) {
                                        distText = `${Math.round((dest.distance * 1000) / 10) * 10}m away`;
                                    } else {
                                        distText = `${dest.distance.toFixed(1)} km away`;
                                    }
                                    const distSpan = card.querySelector('.near-dist-text');
                                    if (distSpan && distSpan.textContent !== distText) {
                                        distSpan.textContent = distText;
                                    }
                                }
                            });
                            return;
                        }

                        nearContainer.classList.remove('is-empty');
                        nearContainer.style.paddingLeft = '';
                        nearContainer.style.paddingRight = '';
                        nearContainer.style.marginLeft = '';
                        nearContainer.style.marginRight = '';
                        nearContainer.innerHTML = '';

                        nearSpots.forEach(dest => {
                            const img = window.getDestImage(dest, 600);
                            const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#0284c7' : (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';

                            let distText = '';
                            if (dest.distance < 0.05) {
                                const meters = Math.max(5, Math.round(dest.distance * 1000));
                                distText = `${meters}m away`;
                            } else if (dest.distance < 1.0) {
                                const meters = Math.round((dest.distance * 1000) / 10) * 10;
                                distText = `${meters}m away`;
                            } else {
                                distText = `${dest.distance.toFixed(1)} km away`;
                            }

                            const encodedDest = encodeURIComponent(JSON.stringify(dest));
                            nearContainer.innerHTML += `
                            <div class="fav-card" data-id="${dest.id}" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" data-name="${(dest.name || '').replace(/"/g, '&quot;')}" data-municipality="${(dest.municipality || dest.location || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                                ${badgeHtml}
                                <img src="${img}" alt="${dest.name}" loading="lazy" decoding="async" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\\'")}', '${(dest.municipality || '').replace(/'/g, "\\'")}'); else this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                                <div class="fav-card-overlay">
                                    <span class="fav-card-name">${dest.name}</span>
                                    <span style="display:block; font-size:10px; color:#38bdf8; margin-top:2px; font-weight:700;"><i class="fa-solid fa-location-arrow"></i> <span class="near-dist-text">${distText}</span></span>
                                </div>
                            </div>
                        `;
                        });
                        window.initLoopingFocusCarousel('near-me-container');
                    } else {
                        nearContainer.classList.add('is-empty');
                        nearContainer.style.paddingLeft = '0';
                        nearContainer.style.paddingRight = '0';
                        nearContainer.style.marginLeft = '0';
                        nearContainer.style.marginRight = '0';
                        const locLabel = detectedMuni ? detectedMuni.replace(', La Union', '') : 'your location';
                        nearContainer.innerHTML = `
                        <div class="dash-empty-state">
                            <div class="dash-empty-icon-wrap">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="dash-empty-title">No Tourist Sites in ${locLabel}</div>
                            <div class="dash-empty-desc">There are no tourist spots recorded in your immediate location yet. Discover attractions across La Union on the map.</div>
                            <button type="button" onclick="navigateTo('map')" class="dash-empty-btn">
                                <i class="fa-solid fa-map-location-dot"></i> Explore Map
                            </button>
                        </div>
                    `;
                    }
                },
                false,
                60000 // 1 minute
            );
        }

        const isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
        const isRealUserSession = Boolean(token && token !== 'guest_token' && token !== 'offline_guest_token' && !token.startsWith('guest_') && !token.startsWith('offline_') && user && user.id && user.id !== 'guest');
        if (isRealUserSession) {
            try {
                localStorage.removeItem('intan_elyu_guest');
                localStorage.removeItem('intan_elyu_offline_guest');
            } catch(e) {}
        }
        isGuest = !isRealUserSession;

        if (isGuest) {
            // Check if guest has local or queued trips on this device
            try {
                const localTrips = JSON.parse(localStorage.getItem('intan_elyu_saved_trips') || '[]');
                const queued = (window.OfflineTripManager && typeof window.OfflineTripManager.getQueuedTrips === 'function')
                    ? window.OfflineTripManager.getQueuedTrips()
                    : [];
                if ((Array.isArray(localTrips) && localTrips.length > 0) || queued.length > 0) {
                    renderSavedTrips(localTrips);
                } else {
                    const guestTripsEl = document.getElementById('saved-trips-container');
                    if (guestTripsEl) {
                        guestTripsEl.innerHTML = `
                            <div class="dash-empty-state">
                                <div class="dash-empty-icon-wrap" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                                    <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                                </div>
                                <div class="dash-empty-title">No Saved Trips Yet</div>
                                <div class="dash-empty-desc">Create custom itineraries and explore recommended routes for your La Union adventure.</div>
                                <button type="button" onclick="navigateTo('itinerary')" class="dash-empty-btn">
                                    <i class="fa-solid fa-plus"></i> Plan a Trip
                                </button>
                            </div>
                        `;
                    }
                }
            } catch (e) {
                const guestTripsEl = document.getElementById('saved-trips-container');
                if (guestTripsEl) {
                    guestTripsEl.innerHTML = `
                        <div class="dash-empty-state">
                            <div class="dash-empty-icon-wrap" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                                <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                            </div>
                            <div class="dash-empty-title">No Saved Trips Yet</div>
                            <div class="dash-empty-desc">Create custom itineraries and explore recommended routes for your La Union adventure.</div>
                            <button type="button" onclick="navigateTo('itinerary')" class="dash-empty-btn">
                                <i class="fa-solid fa-plus"></i> Plan a Trip
                            </button>
                        </div>
                    `;
                }
            }

            // Instantly clear saved places loading state for guest
            const guestSavedEl = document.getElementById('saved-places-container');
            if (guestSavedEl) {
                guestSavedEl.classList.add('is-empty');
                guestSavedEl.innerHTML = `
                    <div class="dash-empty-state">
                        <div class="dash-empty-icon-wrap">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div class="dash-empty-title">No Saved Places Yet</div>
                        <div class="dash-empty-desc">Discover destinations on the map and tap the heart icon to save your favorite spots.</div>
                        <button type="button" onclick="navigateTo('map')" class="dash-empty-btn">
                            <i class="fa-solid fa-location-arrow"></i> Open Map
                        </button>
                    </div>
                `;
            }

            // Guest Mode: Populate dashboard using public map data without requiring private bearer token
            const renderGuestDashboard = (spots) => {
                if (spots && spots.length > 0) {
                    const activeSpots = spots.filter(d => !d.status || !['pending', 'draft', 'rejected'].includes(d.status.toLowerCase()));
                    window.allTouristSpots = activeSpots;
                    renderDashboard({
                        user: { ...user, is_guest: true, name: 'Guest' },
                        stats: { placesVisited: 0, rank: null, unread_notifications: 0 },
                        trending: (() => {
                            const candidates = [...activeSpots].filter(d => (parseFloat(d.rating || 0) > 0) || ((d.visits || 0) > 0));
                            const pool = candidates.length > 0 ? candidates : activeSpots;
                            return [...pool].sort((a, b) => ((b.visits || 0) + (parseFloat(b.rating || 0) * 10)) - ((a.visits || 0) + (parseFloat(a.rating || 0) * 10))).slice(0, 10);
                        })(),
                        savedPlaces: [],
                        recommended: [...activeSpots].sort((a, b) => (parseFloat(b.rating || 0)) - (parseFloat(a.rating || 0))).slice(0, 10),
                        timeLabel: 'Popular in La Union'
                    });
                    if (typeof window.renderAllTouristSpots === 'function') {
                        window.renderAllTouristSpots(activeSpots);
                    }
                } else {
                    handleDashboardEmptyOrError();
                }
            };

            let spots = (window.allTouristSpots && window.allTouristSpots.length > 0) ? window.allTouristSpots : null;
            if (!spots) {
                try {
                    const mapCache = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('public_map_data'), null)?.data : null;
                    spots = (mapCache && mapCache.destinations) || null;
                } catch(e) {}
            }
            if (!spots) {
                try {
                    const fallbackRaw = localStorage.getItem('intan_elyu_cached_spots_map');
                    if (fallbackRaw) {
                        const parsed = JSON.parse(fallbackRaw)?.data;
                        if (parsed && (parsed.destinations || parsed.spots)) {
                            spots = parsed.destinations || parsed.spots;
                        }
                    }
                } catch(e) {}
            }

            if (spots && spots.length > 0) {
                renderGuestDashboard(spots);
            }

            if (!isOffline) {
                const bUrl = getDashBackendUrl();
                fetch(`${bUrl}/api/public/map`, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.ok ? r.json() : null)
                    .then(d => {
                        const fetchedSpots = d ? (d.destinations || d.spots || d.data || []) : [];
                        if (fetchedSpots.length > 0) {
                            window.allTouristSpots = fetchedSpots;
                            try { localStorage.setItem('public_map_data', JSON.stringify({ data: { destinations: fetchedSpots }, timestamp: Date.now() })); } catch(e) {}
                            renderGuestDashboard(fetchedSpots);
                        } else if (!spots || spots.length === 0) {
                            handleDashboardEmptyOrError();
                        }
                        if (typeof window.fetchAllTouristSpots === 'function') {
                            window.fetchAllTouristSpots();
                        }
                    })
                    .catch(() => {
                        if (typeof window.fetchAllTouristSpots === 'function') {
                            window.fetchAllTouristSpots();
                        } else if (!spots || spots.length === 0) {
                            handleDashboardEmptyOrError();
                        }
                    });
            } else if (!spots || spots.length === 0) {
                handleDashboardEmptyOrError();
            }

            if (lat && lng) {
                loadNearMe(lat, lng);
                if (typeof window.fetchWeather === 'function') {
                    window.fetchWeather(lat, lng, 'My Location', true);
                }
            } else {
                loadNearMe(16.6159, 120.3209, 'San Fernando City');
                if (typeof window.fetchWeather === 'function') {
                    window.fetchWeather(16.6159, 120.3209, 'San Fernando, La Union', false);
                }
            }
            return;
        }

        try {
            await window.useCache(
                cacheKey,
                async () => {
                    const res = await fetch(apiUrl, {
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/json',
                            'ngrok-skip-browser-warning': 'true',
                            'Authorization': 'Bearer ' + token
                        }
                    });
                    if (!res.ok) {
                        if (res.status === 401) {
                            const isOfflineGuest = localStorage.getItem('intan_elyu_offline_guest') === 'true' || localStorage.getItem('intan_elyu_offline_mode') === 'true';
                            const isGuestToken = isOfflineGuest || !token || token === 'guest_token' || token === 'offline_guest_token' || token.indexOf('guest_') === 0 || token.indexOf('offline_') === 0 || localStorage.getItem('intan_elyu_guest') === 'true';
                            const isNetOffline = (typeof navigator.onLine === 'boolean' && !navigator.onLine) || window.isAppOnline === false || localStorage.getItem('intan_elyu_offline_mode') === 'true';
                            if (isGuestToken || isNetOffline) {
                                console.warn("Guest mode or offline active; suppressing auth redirect.");
                                throw new Error("Guest/Offline session");
                            } else {
                                if (typeof window.clearExpiredAuthSession === 'function') {
                                    window.clearExpiredAuthSession();
                                } else {
                                    localStorage.removeItem('intan_elyu_token');
                                    localStorage.removeItem('auth_user');
                                }
                                window.location.href = '?view=auth';
                            }
                        }
                        throw new Error("Dashboard fetch failed");
                    }
                    return await res.json();
                },
                (data) => {
                    if (data) {
                        try {
                            window._lastDashboardPayload = data;
                            localStorage.setItem('dashboard_cached_payload', JSON.stringify(data));
                        } catch (e) { }
                        renderDashboard(data);
                    } else {
                        handleDashboardEmptyOrError();
                    }
                },
                Boolean(forceRefresh || window.dashboardNeedsRefresh),
                30000 // 30 seconds TTL for dashboard
            );
            window.dashboardNeedsRefresh = false;

            if (lat && lng) {
                loadNearMe(lat, lng);
                if (typeof window.fetchWeather === 'function') {
                    window.fetchWeather(lat, lng, 'My Location', true);
                }
            } else {
                loadNearMe(16.6159, 120.3209, 'San Fernando City');
                if (typeof window.fetchWeather === 'function') {
                    window.fetchWeather(16.6159, 120.3209, 'San Fernando, La Union', false);
                }
            }

            // Fetch Rank concurrently
            const rankCacheKey = 'dashboard_rank_' + token.substring(0, 10);
            window.useCache(
                rankCacheKey,
                async () => {
                    const rankRes = await fetch(backendUrl + '/api/tourist/leaderboard', {
                        headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token, 'ngrok-skip-browser-warning': 'true' }
                    });
                    if (rankRes.ok) return await rankRes.json();
                    throw new Error("Rank fetch failed");
                },
                (rankData) => {
                    if (rankData) renderRank(rankData);
                },
                Boolean(forceRefresh || window.dashboardNeedsRefresh),
                60000 // 1 minute TTL
            ).catch(e => console.warn('Rank fetch handled:', e));

            // Fetch Saved Trips concurrently
            const tripsContainer = document.getElementById('saved-trips-container');
            if (tripsContainer) {
                const tripsCacheKey = 'dashboard_trips_' + token.substring(0, 10);
                window.useCache(
                    tripsCacheKey,
                    async () => {
                        const itinRes = await fetch(backendUrl + '/api/tourist/itineraries', {
                            headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
                        });
                        if (itinRes.ok) return await itinRes.json();
                        throw new Error("Trips fetch failed");
                    },
                    (itinData) => {
                        if (itinData) renderSavedTrips(itinData);
                    },
                    Boolean(forceRefresh || window.dashboardNeedsRefresh),
                    60000 // 1 minute TTL
                ).catch(e => console.warn('Trips fetch handled:', e));
            }
        } catch (e) {
            console.error('dashboardInit error:', e);
            handleDashboardEmptyOrError();
        }
    }
    function dashboardInit(forceRefresh = false) {
        if (window._dashboardInitPromise) return window._dashboardInitPromise;

        const now = Date.now();
        if (!forceRefresh && window._dashboardLastInitCompletedAt && now - window._dashboardLastInitCompletedAt < 1500) {
            return Promise.resolve(window._lastDashboardPayload || null);
        }

        const request = dashboardInitOnce(forceRefresh).finally(() => {
            if (window._dashboardInitPromise === request) {
                window._dashboardInitPromise = null;
                window._dashboardLastInitCompletedAt = Date.now();
            }
        });
        window._dashboardInitPromise = request;
        return request;
    }
    window.dashboardInit = dashboardInit;

    // Ambient automatic reconnect & lifecycle listeners (bound once)
    if (!window._dashboardLifecycleListenersBound) {
        window._dashboardLifecycleListenersBound = true;

        const refreshDashboardAfterReconnect = function () {
            const now = Date.now();
            if (window._dashboardLastReconnectRefreshAt && now - window._dashboardLastReconnectRefreshAt < 2000) return;
            window._dashboardLastReconnectRefreshAt = now;
            const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
            if (current !== 'dashboard') {
                window.dashboardNeedsRefresh = true;
                return;
            }
            if (typeof window.dashboardInit === 'function') window.dashboardInit(true);
        };
        window.addEventListener('online', refreshDashboardAfterReconnect);
        window.addEventListener('appOnline', refreshDashboardAfterReconnect);
        window.addEventListener('checkins-synced', function () {
            const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
            if (current !== 'dashboard') {
                window.dashboardNeedsRefresh = true;
                return;
            }
            if (typeof window.dashboardInit === 'function') window.dashboardInit(true);
        });
        document.addEventListener('visibilitychange', function () {
            const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
            if (current !== 'dashboard') return;
            if (!document.hidden && window.dashboardNeedsRefresh) {
                if (typeof window.dashboardInit === 'function') window.dashboardInit(true);
            }
        });
        window.addEventListener('viewLoaded', function (e) {
            if (e && e.detail && e.detail.view === 'dashboard') {
                if (typeof window.dashboardInit === 'function') {
                    window.dashboardInit(Boolean(window.dashboardNeedsRefresh));
                }
            }
        });
    }

    dashboardInit();

    window.toggleFavorite = function (destId, element) {
        const token = localStorage.getItem('intan_elyu_token');
        var backendUrl = getDashBackendUrl();

        const card = element.closest('.fav-card');
        const isSavedContainer = card && (card.closest('#saved-places-container') !== null);

        // Check if currently favorited
        const isSolid = element.classList.contains('fa-solid');
        const colorStyle = element.style.color || '';
        const isRed = isSolid && (colorStyle === '#ff3b30' || colorStyle.includes('255, 59, 48') || colorStyle.includes('255,59,48'));
        const isRemoving = isSavedContainer || isRed;

        // 1. INSTANT OPTIMISTIC UPDATE
        element.classList.remove('heart-pop-anim');
        void element.offsetWidth; // trigger reflow
        element.classList.add('heart-pop-anim');

        if (isRemoving) {
            element.style.color = 'rgba(255,255,255,0.4)';
            element.classList.remove('fa-solid');
            element.classList.add('fa-regular');
            if (typeof showToast === 'function') showToast('Removed from Saved Places');

            if (isSavedContainer && card) {
                card.style.transition = 'opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), width 0.3s ease, padding 0.3s ease, margin 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                card.style.width = '0px';
                card.style.minWidth = '0px';
                card.style.marginRight = '0px';
                card.style.padding = '0px';
                card.style.pointerEvents = 'none';

                setTimeout(() => {
                    card.remove();
                    const container = document.getElementById('saved-places-container');
                    if (container) {
                        const remainingCards = Array.from(container.children).filter(c => c.classList.contains('fav-card') && c.style.pointerEvents !== 'none');
                        if (remainingCards.length === 0) {
                            container.classList.add('is-empty');
                            container.style.paddingLeft = '0';
                            container.style.paddingRight = '0';
                            container.style.marginLeft = '0';
                            container.style.marginRight = '0';
                            container.innerHTML = `
                            <div class="dash-empty-state">
                                <div class="dash-empty-icon-wrap">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>
                                <div class="dash-empty-title">No Saved Places Yet</div>
                                <div class="dash-empty-desc">Discover destinations on the map and tap the heart icon to save your favorite spots.</div>
                                <button type="button" onclick="navigateTo('map')" class="dash-empty-btn">
                                    <i class="fa-solid fa-location-arrow"></i> Open Map
                                </button>
                            </div>
                        `;
                        }
                    }
                }, 300);
            }
        } else {
            element.style.color = '#ff3b30';
            element.classList.remove('fa-regular');
            element.classList.add('fa-solid');
            if (typeof showToast === 'function') showToast('Added to Saved Places');
        }

        // Update local cached dashboard and saved places state
        try {
            const tokenPrefix = token ? token.substring(0, 10) : '';
            localStorage.removeItem('saved_places_' + tokenPrefix);

            let savedIds = JSON.parse(localStorage.getItem('intan_elyu_saved_place_ids') || '[]');
            if (isRemoving) {
                savedIds = savedIds.filter(id => id != destId);
            } else {
                if (!savedIds.some(id => id == destId)) savedIds.push(destId);
            }
            localStorage.setItem('intan_elyu_saved_place_ids', JSON.stringify(savedIds));

            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && k.startsWith('dashboard_data_')) {
                    const cached = JSON.parse(localStorage.getItem(k) || '{}');
                    if (cached.savedPlaces && Array.isArray(cached.savedPlaces)) {
                        if (isRemoving) {
                            cached.savedPlaces = cached.savedPlaces.filter(p => p.id != destId);
                        }
                        localStorage.setItem(k, JSON.stringify(cached));
                    }
                }
            }
        } catch (e) { }

        // 2. BACKGROUND NETWORK REQUEST OR OFFLINE QUEUE
        const isOfflineFavoriteAction = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
        if (isOfflineFavoriteAction) {
            if (window.OfflineFavoriteManager && typeof window.OfflineFavoriteManager.queueToggle === 'function') {
                window.OfflineFavoriteManager.queueToggle(destId, isRemoving ? 'remove' : 'add');
            }
        } else {
            fetch(backendUrl + '/api/tourist/destinations/' + destId + '/favorite', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            }).catch(e => {
                console.warn('Background favorite sync error, queuing locally:', e);
                if (window.OfflineFavoriteManager && typeof window.OfflineFavoriteManager.queueToggle === 'function') {
                    window.OfflineFavoriteManager.queueToggle(destId, isRemoving ? 'remove' : 'add');
                }
            });
        }
    };

    window.viewDestinationOnMap = function (encodedDest) {
        try {
            const dest = JSON.parse(decodeURIComponent(encodedDest));
            localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
            if (typeof window.navigateTo === 'function') {
                window.navigateTo('map');
            } else {
                window.location.href = '?view=map';
            }
        } catch (e) { console.error('Failed to view destination:', e); }
    };

    window.toggleRecommendedCard = function (headerEl) {
        if (!headerEl) return;
        const content = headerEl.nextElementSibling;
        const icon = headerEl.querySelector('.toggle-icon');
        if (!content) return;

        const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
        const extras = headerEl.closest('#rec-extras');

        if (!isOpen) {
            content.style.paddingTop = '14px';
            content.style.paddingBottom = '14px';
            content.style.maxHeight = (content.scrollHeight + 180) + 'px';
            content.style.opacity = '1';
            if (icon) icon.style.transform = 'rotate(90deg)';
            if (extras && extras.classList.contains('open')) {
                extras.style.maxHeight = 'none';
                extras.style.overflow = 'visible';
            }
        } else {
            content.style.maxHeight = '0px';
            content.style.opacity = '0';
            content.style.paddingTop = '0';
            content.style.paddingBottom = '0';
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    };

    window.toggleRecommendedMore = function () {
        const extras = document.getElementById('rec-extras');
        const chevron = document.getElementById('rec-chevron');
        const btn = document.getElementById('btn-view-more-rec');

        if (!extras) return;

        const isOpen = extras.classList.contains('open') || (extras.style.maxHeight !== '0px' && extras.style.maxHeight !== '');

        if (isOpen) {
            extras.style.overflow = 'hidden';
            extras.style.maxHeight = extras.scrollHeight + 'px';
            void extras.offsetHeight; // force reflow
            extras.style.maxHeight = '0px';
            extras.classList.remove('open');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            const count = extras.children.length;
            if (btn) btn.innerHTML = `<i class="fa-solid fa-chevron-down" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i> View ${count} More`;
        } else {
            extras.style.overflow = 'hidden';
            extras.style.maxHeight = (extras.scrollHeight + 300) + 'px';
            extras.classList.add('open');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
            if (btn) btn.innerHTML = `<i class="fa-solid fa-chevron-up" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i> Show Less`;
            setTimeout(() => {
                if (extras.classList.contains('open')) {
                    extras.style.maxHeight = 'none';
                    extras.style.overflow = 'visible';
                }
            }, 420);
        }
    };

    // Dismiss search dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const searchContainer = document.querySelector('.dash-search-container');
        const searchResults = document.getElementById('dash-search-results');
        if (searchResults && searchContainer && !searchContainer.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    window.setupDashboardSwipeToDelete = function () {
        document.querySelectorAll('.trip-swipe-container').forEach(container => {
            const content = container.querySelector('.trip-swipe-content');
            const bg = container.querySelector('.trip-swipe-bg');
            if (!content) return;
            let startX = 0, startY = 0, currentX = 0, currentY = 0;
            let isSwiping = false, isHorizontalSwipe = false, isGestureDetermined = false, isOpen = false;

            container.style.touchAction = 'pan-y';
            content.style.touchAction = 'pan-y';

            const openSwipe = () => {
                isOpen = true;
                content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                content.style.transform = 'translateX(-85px)';
                if (bg) {
                    bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease';
                    bg.style.width = '85px';
                    bg.style.opacity = '1';
                    bg.style.pointerEvents = 'auto';
                    bg.style.transform = 'translateX(0px)';
                    bg.style.zIndex = '10';
                }
            };

            const closeSwipe = () => {
                isOpen = false;
                content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                content.style.transform = 'translateX(0px)';
                if (bg) {
                    bg.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease';
                    bg.style.width = '85px';
                    bg.style.opacity = '0';
                    bg.style.pointerEvents = 'none';
                    bg.style.transform = 'translateX(85px)';
                    bg.style.zIndex = '1';
                }
            };

            const handleStart = (clientX, clientY, target) => {
                if (target && target.closest('button, a, input, select, textarea, .stat-card')) return;
                if (target && target.closest('.trip-swipe-bg')) return;
                startX = clientX;
                startY = clientY;
                currentX = startX;
                currentY = startY;
                isSwiping = true;
                isHorizontalSwipe = false;
                isGestureDetermined = false;
                content.style.transition = 'none';
                if (bg) bg.style.transition = 'none';
            };

            const handleMove = (clientX, clientY) => {
                if (!isSwiping) return;
                currentX = clientX;
                currentY = clientY;
                const diffX = startX - currentX;
                const diffY = startY - currentY;

                if (!isGestureDetermined) {
                    if (Math.abs(diffX) > 7 || Math.abs(diffY) > 7) {
                        isGestureDetermined = true;
                        if (Math.abs(diffX) > Math.abs(diffY)) {
                            isHorizontalSwipe = true;
                        } else {
                            isHorizontalSwipe = false;
                            isSwiping = false;
                            return;
                        }
                    } else {
                        return;
                    }
                }

                if (!isHorizontalSwipe) return;

                const baseOffset = isOpen ? 85 : 0;
                const effectiveDiff = diffX + baseOffset;

                if (effectiveDiff > 0) {
                    const cardWidth = container.offsetWidth || 320;
                    const dragAmount = Math.min(effectiveDiff, cardWidth * 0.85);
                    content.style.transform = `translateX(-${dragAmount}px)`;
                    if (bg) {
                        bg.style.opacity = '1';
                        bg.style.pointerEvents = 'auto';
                        bg.style.zIndex = '10';
                        if (dragAmount > 85) {
                            bg.style.width = `${dragAmount}px`;
                            bg.style.transform = 'translateX(0px)';
                        } else {
                            bg.style.width = '85px';
                            bg.style.transform = `translateX(${85 - dragAmount}px)`;
                        }
                    }
                } else {
                    content.style.transform = 'translateX(0px)';
                    if (bg) {
                        bg.style.width = '85px';
                        bg.style.opacity = '0';
                        bg.style.pointerEvents = 'none';
                        bg.style.transform = 'translateX(85px)';
                        bg.style.zIndex = '1';
                    }
                }
            };

            const handleEnd = () => {
                if (!isSwiping) return;
                isSwiping = false;
                const diffX = startX - currentX;
                const baseOffset = isOpen ? 85 : 0;
                const effectiveDiff = diffX + baseOffset;
                const tripId = container.dataset.tripId;
                const tripTitle = container.dataset.tripTitle || '';

                if (effectiveDiff > 120) {
                    content.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                    content.style.transform = 'translateX(-90px)';
                    if (bg) {
                        bg.style.transition = 'transform 0.25s ease, opacity 0.2s ease';
                        bg.style.width = '90px';
                        bg.style.opacity = '1';
                        bg.style.pointerEvents = 'auto';
                        bg.style.transform = 'translateX(0px)';
                        bg.style.zIndex = '10';
                    }
                    isOpen = true;
                    window.confirmDeleteSavedTrip(tripId, container, tripTitle);
                } else if (effectiveDiff > 30) {
                    openSwipe();
                } else {
                    closeSwipe();
                }

                startX = 0;
                startY = 0;
                currentX = 0;
                currentY = 0;
                isHorizontalSwipe = false;
                isGestureDetermined = false;
            };

            const handleCancel = () => {
                if (!isSwiping) return;
                isSwiping = false;
                if (isOpen) openSwipe(); else closeSwipe();
            };

            content.addEventListener('touchstart', (e) => {
                if (e.touches && e.touches.length === 1) {
                    handleStart(e.touches[0].clientX, e.touches[0].clientY, e.target);
                }
            }, { passive: true });
            content.addEventListener('touchmove', (e) => {
                if (e.touches && e.touches.length === 1) {
                    handleMove(e.touches[0].clientX, e.touches[0].clientY);
                }
            }, { passive: true });
            content.addEventListener('touchend', handleEnd, { passive: true });
            content.addEventListener('touchcancel', handleCancel, { passive: true });

            content.addEventListener('mousedown', (e) => handleStart(e.clientX, e.clientY, e.target));
            window.addEventListener('mousemove', (e) => { if (isSwiping) handleMove(e.clientX, e.clientY); });
            window.addEventListener('mouseup', () => { if (isSwiping) handleEnd(); });
        });
    };

    window.confirmDeleteSavedTrip = function (id, container, title) {
        window._pendingDeleteTripId = id;
        window._pendingDeleteTripContainer = container;
        window._pendingDeleteTripTitle = title || container?.dataset?.tripTitle || '';

        const modal = document.getElementById('delete-trip-modal');
        const textEl = document.getElementById('delete-trip-title-text');
        if (textEl) {
            const displayTitle = window._pendingDeleteTripTitle || 'this trip';
            textEl.textContent = `Are you sure you want to delete "${displayTitle}"? All saved itinerary items will be removed.`;
        }
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeDeleteTripModal = function () {
        const modal = document.getElementById('delete-trip-modal');
        if (modal) modal.style.display = 'none';

        // Snap swiped container back closed
        const container = window._pendingDeleteTripContainer;
        if (container) {
            const content = container.querySelector('.trip-swipe-content');
            const bg = container.querySelector('.trip-swipe-bg');
            if (content) {
                content.style.transform = 'translateX(0px)';
                content.style.borderRadius = '20px';
                content.style.borderRightColor = '';
            }
            if (bg) {
                bg.style.opacity = '0';
                bg.style.pointerEvents = 'none';
                bg.style.transform = 'translateX(85px)';
            }
        }

        window._pendingDeleteTripId = null;
        window._pendingDeleteTripContainer = null;
        window._pendingDeleteTripTitle = null;
    };

    window.executeConfirmDeleteTrip = async function () {
        const id = window._pendingDeleteTripId;
        const element = window._pendingDeleteTripContainer;
        const btn = document.getElementById('btn-confirm-delete-trip');
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!id) {
            window.closeDeleteTripModal();
            return;
        }

        if (typeof window.markTripAsDeleted === 'function') {
            window.markTripAsDeleted(id);
            if (String(id).startsWith('offline_edit_')) {
                window.markTripAsDeleted(String(id).replace('offline_edit_', ''));
            }
        }

        const pruneTripFromLocalCaches = (tripId) => {
            if (!tripId) return;
            const targetStr = String(tripId).trim();

            const filterOut = (list) => {
                if (!Array.isArray(list)) return [];
                const isDel = (typeof window.isTripDeleted === 'function') ? window.isTripDeleted : (t => false);
                return list.filter(t => {
                    if (!t || isDel(t)) return false;
                    const idStr = String(t.id || '');
                    const tripIdStr = String(t.trip_id || '');
                    const localIdStr = String(t.local_id || '');
                    const updateIdStr = String(t.update_id || '');
                    if (idStr === targetStr || tripIdStr === targetStr || localIdStr === targetStr || updateIdStr === targetStr) return false;
                    if (idStr === 'offline_edit_' + targetStr || tripIdStr === 'offline_edit_' + targetStr) return false;
                    if (targetStr.startsWith('offline_edit_')) {
                        const raw = targetStr.replace('offline_edit_', '');
                        if (idStr === raw || tripIdStr === raw || localIdStr === raw || updateIdStr === raw) return false;
                    }
                    return true;
                });
            };

            ['intan_elyu_offline_saved_trips', 'intan_elyu_offline_saved_trips_backup', 'intan_elyu_saved_trips'].forEach(k => {
                try {
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (Array.isArray(parsed)) {
                            localStorage.setItem(k, JSON.stringify(filterOut(parsed)));
                        } else if (parsed && Array.isArray(parsed.itineraries)) {
                            parsed.itineraries = filterOut(parsed.itineraries);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        } else if (parsed && Array.isArray(parsed.data)) {
                            parsed.data = filterOut(parsed.data);
                            localStorage.setItem(k, JSON.stringify(parsed));
                        }
                    }
                } catch (e) { }
            });

            if (window.OfflineTripManager && typeof window.OfflineTripManager.removeQueuedTrip === 'function') {
                try { window.OfflineTripManager.removeQueuedTrip(tripId); } catch(e) {}
            }

            try {
                for (let i = localStorage.length - 1; i >= 0; i--) {
                    const k = localStorage.key(i);
                    if (k && (k.startsWith('saved_trips_') || k.startsWith('dashboard_trips_') || k.startsWith('dashboard_saved_trips_'))) {
                        localStorage.removeItem(k);
                    }
                }
            } catch(e) {}

            if (Array.isArray(window._cachedSavedTrips)) {
                window._cachedSavedTrips = filterOut(window._cachedSavedTrips);
            }
        };

        const isGuestOrOffline = !token || 
            token === 'guest_token' || 
            token === 'offline_guest_token' || 
            token.startsWith('guest_') || 
            token.startsWith('offline_') || 
            String(id).startsWith('offline_') ||
            ((typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false));

        pruneTripFromLocalCaches(id);

        if (element) {
            element.style.transition = 'all 0.3s ease';
            element.style.opacity = '0';
            element.style.transform = 'scale(0.9)';
            setTimeout(() => {
                element.remove();
                const tripsContainer = document.getElementById('saved-trips-container');
                if (tripsContainer && (!tripsContainer.querySelectorAll('.trip-swipe-container') || tripsContainer.querySelectorAll('.trip-swipe-container').length === 0)) {
                    tripsContainer.innerHTML = `
                        <div class="dash-empty-state">
                            <div class="dash-empty-icon-wrap" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                                <i class="fa-solid fa-route" style="color: #1e3a8a !important;"></i>
                            </div>
                            <div class="dash-empty-title">No Saved Trips Yet</div>
                            <div class="dash-empty-desc">Create custom itineraries and explore recommended routes for your La Union adventure.</div>
                            <button type="button" onclick="navigateTo('itinerary')" class="dash-empty-btn">
                                <i class="fa-solid fa-plus"></i> Plan a Trip
                            </button>
                        </div>
                    `;
                }
            }, 300);
        }

        window.closeDeleteTripModal();

        if (isGuestOrOffline) {
            if (typeof showToast === 'function') showToast("Trip deleted from device.");
            window.dispatchEvent(new CustomEvent('tripDeleted', { detail: { id: id } }));
            return;
        }

        try {
            const bUrl = (typeof window.getDashBackendUrl === 'function' ? window.getDashBackendUrl() : (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online'))).replace(/\/+$/, '');
            const res = await fetch(bUrl + '/api/tourist/itineraries/' + id, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true'
                }
            });

            pruneTripFromLocalCaches(id);

            if (res.ok || res.status === 404) {
                if (typeof showToast === 'function') showToast("Trip deleted successfully.");
            } else if (res.status === 403) {
                if (typeof showToast === 'function') showToast("Unauthorized to delete this trip.", "error");
            } else {
                if (typeof showToast === 'function') showToast("Trip deleted locally.");
            }

            window.dispatchEvent(new CustomEvent('tripDeleted', { detail: { id: id } }));
        } catch (e) {
            console.error('Error deleting itinerary', e);
            pruneTripFromLocalCaches(id);
            if (typeof showToast === 'function') showToast("Trip removed from device (offline).");
            window.dispatchEvent(new CustomEvent('tripDeleted', { detail: { id: id } }));
        }
    };

    // =========================================================================
    // ONBOARDING MODAL CONTROLLER (Complete Profile)
    // =========================================================================
    window.initDashboardOnboarding = function () {
        const showOb = sessionStorage.getItem('show_onboarding') || sessionStorage.getItem('onboarding_active');
        if (!showOb) return;

        try {
            const user = JSON.parse(localStorage.getItem('auth_user'));
            const avatarImg = document.getElementById('onboard-avatar-preview');
            if (user && avatarImg) {
                if (user.avatar) {
                    avatarImg.src = user.avatar;
                } else if (user.name) {
                    avatarImg.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=38bdf8&color=fff`;
                }
            }
        } catch (e) { }

        const profileModal = document.getElementById('onboard-profile-modal');
        if (profileModal) profileModal.style.display = 'flex';
    };

    window.previewOnboardAvatar = function (event) {
        const file = event.target?.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById('onboard-avatar-preview');
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };

    window.onboardSaveProfile = async function () {
        const btn = document.getElementById('btn-onboard-save');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...';
            btn.disabled = true;
        }

        const phone = document.getElementById('onboard-phone')?.value || '';
        const home = document.getElementById('onboard-home')?.value || '';
        const bio = document.getElementById('onboard-bio')?.value || '';

        const activeChips = Array.from(document.querySelectorAll('#onboard-pref-chips .pref-chip.active')).map(c => c.textContent.trim());
        const prefs = activeChips.join(', ');

        const avatarInput = document.getElementById('onboard-avatar-input');
        const token = localStorage.getItem('intan_elyu_token');

        const formData = new FormData();
        formData.append('phone', phone);
        formData.append('home_location', home);
        formData.append('bio', bio);
        formData.append('travel_preferences', prefs);

        if (avatarInput && avatarInput.files && avatarInput.files[0]) {
            formData.append('avatar', avatarInput.files[0]);
        }

        if (token) {
            try {
                const res = await fetch(getDashBackendUrl() + '/api/tourist/profile', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token },
                    body: formData
                });
                const data = await res.json();
                if (data.user) {
                    localStorage.setItem('auth_user', JSON.stringify(data.user));
                    const headerAvatar = document.querySelector('.user-avatar, #user-avatar, .header-avatar');
                    if (headerAvatar && data.user.avatar) headerAvatar.src = data.user.avatar;
                }
            } catch (e) { }
        }

        sessionStorage.removeItem('show_onboarding');
        sessionStorage.removeItem('onboarding_active');
        sessionStorage.removeItem('onboarding_step');
        sessionStorage.removeItem('pending_reg_email');
        const profileModal = document.getElementById('onboard-profile-modal');
        if (profileModal) profileModal.style.display = 'none';
        if (typeof showToast === 'function') showToast('Profile updated! Welcome to Intan Elyu!');
    };

    window.onboardSkipProfile = function () {
        sessionStorage.removeItem('show_onboarding');
        sessionStorage.removeItem('onboarding_active');
        sessionStorage.removeItem('onboarding_step');
        sessionStorage.removeItem('pending_reg_email');
        const profileModal = document.getElementById('onboard-profile-modal');
        if (profileModal) profileModal.style.display = 'none';
        if (typeof showToast === 'function') showToast('Welcome to your Dashboard!');
    };

    setTimeout(() => {
        if (typeof window.initDashboardOnboarding === 'function') {
            window.initDashboardOnboarding();
        }
    }, 200);
</script>

<!-- Complete Profile Modal -->
<div id="onboard-profile-modal"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(12px); z-index:999999; justify-content:center; align-items:center; padding:16px;">
    <div
        style="background:#0f172a; border:1px solid rgba(56,189,248,0.3); border-radius:24px; width:100%; max-width:480px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.8); animation:slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
        <div
            style="padding:20px 24px; background:linear-gradient(135deg, rgba(30,41,59,0.8), rgba(15,23,42,0.95)); border-bottom:1px solid rgba(255,255,255,0.08);">
            <h3
                style="margin:0 0 4px 0; font-size:17px; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-user-gear" style="color:#fbbf24;"></i> Complete Your Profile
            </h3>
            <p style="margin:0; font-size:12px; color:rgba(148,163,184,0.9);">Tell us a bit about yourself to
                personalize recommendations.</p>
        </div>
        <div style="padding:20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:14px;">
            <!-- Profile Avatar Picker -->
            <div
                style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; margin-bottom:4px;">
                <div style="position:relative; width:84px; height:84px;">
                    <img id="onboard-avatar-preview"
                        src="https://ui-avatars.com/api/?name=User&background=38bdf8&color=fff"
                        style="width:84px; height:84px; border-radius:50%; object-fit:cover; border:2.5px solid #38bdf8; box-shadow:0 6px 20px rgba(56,189,248,0.35);">
                    <label for="onboard-avatar-input"
                        style="position:absolute; bottom:2px; right:2px; width:28px; height:28px; background:#38bdf8; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.5); border:2px solid #0f172a; transition:transform 0.2s;">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                    <input type="file" id="onboard-avatar-input" accept="image/*" style="display:none;"
                        onchange="window.previewOnboardAvatar(event)">
                </div>
                <span style="font-size:11px; font-weight:700; color:#38bdf8; cursor:pointer;"
                    onclick="document.getElementById('onboard-avatar-input').click()">
                    Add Profile Picture
                </span>
            </div>

            <div>
                <label
                    style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:6px;">Mobile
                    Phone Number</label>
                <input type="text" id="onboard-phone" placeholder="0912 345 6789"
                    style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:10px 14px; color:#fff; font-size:13px; outline:none;">
            </div>
            <div>
                <label
                    style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:6px;">Home
                    Town / City</label>
                <input type="text" id="onboard-home" placeholder="e.g. Manila, La Union, Baguio"
                    style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:10px 14px; color:#fff; font-size:13px; outline:none;">
            </div>
            <div>
                <label
                    style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:6px;">Short
                    Bio</label>
                <textarea id="onboard-bio" rows="2" placeholder="Share your travel interests..."
                    style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:10px 14px; color:#fff; font-size:13px; outline:none; resize:none;"></textarea>
            </div>
            <div>
                <label
                    style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:8px;">Travel
                    Preferences</label>
                <div style="display:flex; flex-wrap:wrap; gap:8px;" id="onboard-pref-chips">
                    <span onclick="this.classList.toggle('active')" class="pref-chip"
                        style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🏄
                        Surfing</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip"
                        style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🏖️
                        Beach</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip"
                        style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">⛰️
                        Hiking</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip"
                        style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🍜
                        Food & Dining</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip"
                        style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🏛️
                        Heritage</span>
                </div>
            </div>
        </div>
        <div
            style="padding:16px 20px; background:rgba(15,23,42,0.98); border-top:1px solid rgba(255,255,255,0.08); display:flex; gap:10px;">
            <button onclick="window.onboardSkipProfile()"
                style="flex:1; background:transparent; border:1px solid rgba(255,255,255,0.15); color:rgba(255,255,255,0.7); padding:12px; border-radius:12px; font-weight:700; font-size:12px; cursor:pointer;">
                Skip for now
            </button>
            <button id="btn-onboard-save" onclick="window.onboardSaveProfile()"
                style="flex:2; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:white; padding:12px; border-radius:12px; font-weight:800; font-size:13px; cursor:pointer; box-shadow:0 4px 14px rgba(56,189,248,0.3); display:flex; align-items:center; justify-content:center; gap:8px;">
                Save & Continue <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>
