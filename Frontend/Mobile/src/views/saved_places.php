<?php
$pageTitle = 'Saved Places';
$backRoute = 'dashboard';

$municipalityImages = [];
$imgDir = __DIR__ . '/../../../../backend/storage/app/public/municipalities';
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

<div class="saved-trips-page-container has-header animate-slide-up" style="padding-left: 16px; padding-right: 16px;">
    <div id="saved-places-list" style="margin-top: 16px;">
        <p style="text-align:center; color:rgba(255,255,255,0.5); margin-top:40px;">
            <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Loading saved places...
        </p>
    </div>
</div>

<script>
(function() {
    var backendUrl = window.backendUrl || 'https://intanelyu-production.up.railway.app';

    window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;

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
                    renderSavedPlaces(spots);
                } else {
                    const list = document.getElementById('saved-places-list');
                    if (list) list.innerHTML = '<p style="text-align:center; color:#999; margin-top:20px;">Failed to load saved places.</p>';
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
                <div style="padding:40px 20px; text-align:center; color:rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-map-location-dot" style="font-size:40px; margin-bottom:12px; display:block;"></i>
                    No saved places yet.
                    <br><br>
                    <button onclick="navigateTo('map')" style="background: linear-gradient(135deg, #38bdf8, #2563eb); color: white; border: none; padding: 11px 22px; border-radius: 100px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(56,189,248,0.3);">
                        <i class="fa-solid fa-location-arrow"></i> Go to Map
                    </button>
                </div>
            `;
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
            const encodedDest = encodeURIComponent(JSON.stringify(dest).replace(/"/g, '&quot;'));
            html += `
                <div class="trending-card" style="animation-delay:${i * 0.08}s" onclick="window.viewTrendingDest(${dest.id}, '${dest.name.replace(/'/g, "\\'")}', '${encodedDest}')">
                    ${dest.classification_status ? `<div class="badge" style="background:${badgeColor};">${badgeLabel}</div>` : ''}
                    <i class="fa-solid fa-heart fire-icon" style="color: #ff3b30;" onclick="event.stopPropagation(); window.toggleFavLocal(${dest.id}, this)"></i>
                    <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.style.display='none';">
                    <div class="overlay">
                        <div class="name">${dest.name}</div>
                        <div class="meta" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><i class="fa-solid fa-location-dot" style="margin-right:3px;"></i>${dest.location || dest.municipality || 'La Union'}</div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        list.innerHTML = html;
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
