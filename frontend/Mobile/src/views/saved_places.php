<?php
$pageTitle = 'Saved Places';
$backRoute = 'dashboard';

$municipalityImages = [];
$imgDir = __DIR__ . '/../assets/img/MUNICIPALITIES';
if (is_dir($imgDir)) {
    $munis = scandir($imgDir);
    foreach ($munis as $muni) {
        if ($muni === '.' || $muni === '..') continue;
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

<link rel="stylesheet" href="assets/css/views/trending.css">

<style>
/* Saved Places View Scoped Styles — Smooth White Theme & Dynamic Scrollview */
html:has(body[data-view="saved_places"]),
body[data-view="saved_places"] {
    background: #ffffff !important;
    background-color: #ffffff !important;
    height: 100vh !important;
    height: 100dvh !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
}

body[data-view="saved_places"] #app-container,
body[data-view="saved_places"] #main-content {
    background: #ffffff !important;
    background-color: #ffffff !important;
    height: 100vh !important;
    height: 100dvh !important;
    overflow: hidden !important;
}

/* Page container specifically for saved places */
.saved-places-page-container {
    width: 100%;
    height: 100vh;
    height: 100dvh;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    padding-top: calc(56px + max(env(safe-area-inset-top, 0px), 40px)) !important;
    padding-bottom: max(env(safe-area-inset-bottom, 0px), 24px) !important;
    padding-left: 16px !important;
    padding-right: 16px !important;
    background: #ffffff !important;
    overflow-x: hidden !important;
    overflow-y: hidden; /* Default: NO scrollview when not many tourist sites */
}

/* Only enable scrollview when too many tourist sites are saved */
.saved-places-page-container.is-scrollable {
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.saved-places-page-container.is-scrollable::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

/* Header styling matching header.php */
body[data-view="saved_places"] .mobile-header,
.mobile-header {
    background: #1e3a8a !important;
    backdrop-filter: blur(24px) !important;
    -webkit-backdrop-filter: blur(24px) !important;
    border: none !important;
    outline: none !important;
    border-bottom: none !important;
    box-shadow: none !important;
}

body[data-view="saved_places"] .mobile-header .header-title {
    color: #ffffff !important;
    font-weight: 800 !important;
}

body[data-view="saved_places"] .mobile-header .header-icon {
    color: #1e3a8a !important;
    background: #ffffff !important;
    border-radius: 12px;
}

body[data-view="saved_places"] .mobile-header .header-icon i {
    color: #1e3a8a !important;
}

/* Card Styling on White Background */
body[data-view="saved_places"] .trending-card {
    background: #f8fafc !important;
    border: 1.5px solid #cbd5e1 !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06) !important;
    border-radius: 18px !important;
}
</style>

<div id="saved-places-container" class="saved-places-page-container has-header animate-slide-up">
    <div id="saved-places-list" style="margin-top: 16px;">
        <p style="text-align:center; color:#64748b; font-size: 14px; font-weight: 600; margin-top:40px;">
            <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px; color:#0284c7;"></i> Loading saved places...
        </p>
    </div>
</div>

<script>
(function() {
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

    window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;

    function updateScrollviewState() {
        const container = document.getElementById('saved-places-container');
        const list = document.getElementById('saved-places-list');
        if (!container || !list) return;

        const cards = list.querySelectorAll('.trending-card');
        const hasEmptyState = list.querySelector('.dash-empty-state');

        // If empty state or 0 cards, remove scrollview completely
        if (hasEmptyState || cards.length === 0) {
            container.classList.remove('is-scrollable');
            container.scrollTop = 0;
            return;
        }

        // Only show scrollview if too many tourist sites are saved
        requestAnimationFrame(() => {
            const isOverflowing = cards.length > 4 || list.scrollHeight > (container.clientHeight - 30);
            if (isOverflowing) {
                container.classList.add('is-scrollable');
            } else {
                container.classList.remove('is-scrollable');
                container.scrollTop = 0;
            }
        });
    }

    window.addEventListener('resize', updateScrollviewState);

    async function fetchSavedPlaces(forceRefresh = false) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const cacheKey = 'saved_places_' + (token ? token.substring(0, 10) : '');

        await window.useCache(
            cacheKey,
            async () => {
                const res = await fetch(backendUrl + '/api/tourist/dashboard', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (!res.ok) throw new Error("Failed to fetch saved places");
                const data = await res.json();
                return data.savedPlaces || [];
            },
            (spots) => {
                if (spots) {
                    renderSavedPlaces((spots || []).filter(d => !d.status || d.status.toLowerCase() !== 'pending'));
                } else {
                    const list = document.getElementById('saved-places-list');
                    if (list) list.innerHTML = '<p style="text-align:center; color:#94a3b8; margin-top:20px;">Failed to load saved places.</p>';
                    updateScrollviewState();
                }
            },
            forceRefresh,
            60000 // 1 minute TTL
        );
    }

    function renderSavedPlaces(spots) {
        const list = document.getElementById('saved-places-list');
        if (!list) return;
        if (!spots.length) {
            list.innerHTML = `
                <div class="dash-empty-state">
                    <div class="dash-empty-icon-wrap" style="background: #ffffff !important; color: #1e3a8a !important; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15) !important;">
                        <i class="fa-solid fa-map-location-dot" style="color: #1e3a8a !important;"></i>
                    </div>
                    <div class="dash-empty-title">No Saved Places Yet</div>
                    <div class="dash-empty-desc">Discover destinations on the map and tap the heart icon to save your favorite spots.</div>
                    <button type="button" onclick="navigateTo('map')" class="dash-empty-btn">
                        <i class="fa-solid fa-location-arrow"></i> Open Map
                    </button>
                </div>
            `;
            updateScrollviewState();
            return;
        }
        let html = '<div class="trending-grid">';
        spots.forEach((dest, i) => {
            const img = window.getDestImage(dest);
            const badgeColor = dest.classification_status === 'EXIST' ? '#34c759' :
                (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b');
            const badgeLabel = dest.classification_status === 'EXIST' ? 'EXISTING' :
                (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL');
            
            // Add a toggle favorite function inline for removal handling
            const encodedDest = encodeURIComponent(JSON.stringify(dest));
            html += `
                <div class="trending-card" style="animation-delay:${i * 0.08}s" onclick="window.viewTrendingDest(${dest.id}, '${dest.name.replace(/'/g, "\\'")}', '${encodedDest}')">
                    ${dest.classification_status ? `<div class="badge" style="background:${badgeColor};">${badgeLabel}</div>` : ''}
                    <div class="fire-icon" style="position: absolute; top: 8px; right: 8px; z-index: 5; width: 30px; height: 30px; border-radius: 50%; background: #ffffff !important; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.18); cursor: pointer;" onclick="event.stopPropagation(); window.toggleFavLocal(${dest.id}, this)">
                        <i class="fa-solid fa-heart" style="color: #ff3b30 !important; font-size: 13px;"></i>
                    </div>
                    <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.src=window.noImageFallback;">
                    <div class="overlay">
                        <div class="name">${dest.name}</div>
                        <div class="meta" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fa-solid fa-location-dot" style="margin-right:3px;"></i>${dest.location || dest.municipality || 'La Union'}</div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        list.innerHTML = html;
        updateScrollviewState();
    }

    window.toggleFavLocal = async function(id, btn) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;
        try {
            const res = await fetch(backendUrl + '/api/tourist/destinations/' + id + '/favorite', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
            });
            if (res.ok) {
                // Invalidate caches
                const cacheKey = 'saved_places_' + (token ? token.substring(0, 10) : '');
                localStorage.removeItem(cacheKey);
                
                try {
                    let savedIds = JSON.parse(localStorage.getItem('intan_elyu_saved_place_ids') || '[]');
                    savedIds = savedIds.filter(item => item != id);
                    localStorage.setItem('intan_elyu_saved_place_ids', JSON.stringify(savedIds));
                } catch(e) {}

                // Also clear dashboard data caches
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && key.startsWith('dashboard_data_')) {
                        localStorage.removeItem(key);
                        i--;
                    }
                }

                // If toggled from this screen, it means it's removed
                const card = btn.closest('.trending-card');
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => { 
                        card.remove();
                        updateScrollviewState();
                        if (document.querySelectorAll('.trending-card').length === 0) {
                            fetchSavedPlaces(true); // refresh with forceRefresh to show empty state
                        }
                    }, 300);
                }
            }
        } catch (e) { console.error('Error toggling favorite', e); }
    };

    window.viewTrendingDest = function(id, name, encodedDest) {
        try {
            const dest = JSON.parse(decodeURIComponent(encodedDest));
            localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
            window.location.href = '?view=map';
        } catch (e) {
            console.error('Failed to view destination:', e);
        }
    };

    fetchSavedPlaces();
})();
</script>
