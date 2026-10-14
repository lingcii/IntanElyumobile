<?php
$pageTitle = 'Saved Places';
$backRoute = 'dashboard';

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



<div id="saved-places-container" class="saved-places-page-container has-header animate-slide-up">
    <!-- Offline Status Badge -->
    <div id="saved-places-offline-badge" style="display: none; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 12px auto; padding: 7px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 11.5px; font-weight: 800; text-align: center; width: fit-content;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
        <span>Offline Mode &bull; Cached Saved Places</span>
    </div>

    <div id="saved-places-list" style="margin-top: 16px;">
        <p style="text-align:center; color:#64748b; font-size: 14px; font-weight: 600; margin-top:40px;">
            <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px; color:#0284c7;"></i> Loading saved places...
        </p>
    </div>
</div>

<script>
    (function () {
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
                        if (list) {
                            if (!navigator.onLine) {
                                list.innerHTML = `
                                <div class="dash-empty-state" style="margin-top:24px;">
                                    <div class="dash-empty-icon-wrap" style="background:#ffffff; color:#f59e0b;"><i class="fa-solid fa-cloud-slash"></i></div>
                                    <div class="dash-empty-title">Saved Places Offline</div>
                                    <div class="dash-empty-desc">Your bookmarked destinations will update automatically once reconnected.</div>
                                </div>`;
                            } else {
                                list.innerHTML = '<p style="text-align:center; color:#94a3b8; margin-top:20px;">Failed to load saved places.</p>';
                            }
                        }
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
                const badgeColor = dest.classification_status === 'EXIST' ? '#0284c7' :
                    (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981');
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

        window.toggleFavLocal = async function (id, btn) {
            const token = localStorage.getItem('intan_elyu_token');
            if (!token) return;

            const isOffline = !navigator.onLine || window.isAppOnline === false;

            // 1. Instant Optimistic UI Removal
            const card = btn.closest('.trending-card');
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.remove();
                    updateScrollviewState();
                    if (document.querySelectorAll('.trending-card').length === 0) {
                        renderSavedPlaces([]);
                    }
                }, 300);
            }

            // 2. Update local state and cached lists
            try {
                let savedIds = JSON.parse(localStorage.getItem('intan_elyu_saved_place_ids') || '[]');
                savedIds = savedIds.filter(item => item != id);
                localStorage.setItem('intan_elyu_saved_place_ids', JSON.stringify(savedIds));

                const tokenPrefix = token ? token.substring(0, 10) : '';
                const cacheKey = 'saved_places_' + tokenPrefix;
                const cachedRaw = localStorage.getItem(cacheKey);
                if (cachedRaw) {
                    const parsed = JSON.parse(cachedRaw);
                    if (parsed && Array.isArray(parsed.data)) {
                        parsed.data = parsed.data.filter(p => p.id != id);
                        localStorage.setItem(cacheKey, JSON.stringify(parsed));
                    }
                }

                // Also update dashboard data caches
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && key.startsWith('dashboard_data_')) {
                        const cached = JSON.parse(localStorage.getItem(key) || '{}');
                        if (cached.savedPlaces && Array.isArray(cached.savedPlaces)) {
                            cached.savedPlaces = cached.savedPlaces.filter(p => p.id != id);
                            localStorage.setItem(key, JSON.stringify(cached));
                        }
                    }
                }
            } catch (e) { }

            // 3. Offline handling or Network Request
            if (isOffline) {
                if (window.OfflineFavoriteManager && typeof window.OfflineFavoriteManager.queueToggle === 'function') {
                    window.OfflineFavoriteManager.queueToggle(id, 'remove');
                }
                if (typeof showToast === 'function') {
                    showToast('Removed from saved places (will sync when online)', 'info');
                }
                return;
            }

            try {
                const res = await fetch(backendUrl + '/api/tourist/destinations/' + id + '/favorite', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
                });
                if (!res.ok) {
                    if (window.OfflineFavoriteManager && typeof window.OfflineFavoriteManager.queueToggle === 'function') {
                        window.OfflineFavoriteManager.queueToggle(id, 'remove');
                    }
                }
            } catch (e) {
                console.error('Error toggling favorite, queueing locally:', e);
                if (window.OfflineFavoriteManager && typeof window.OfflineFavoriteManager.queueToggle === 'function') {
                    window.OfflineFavoriteManager.queueToggle(id, 'remove');
                }
            }
        };

        window.viewTrendingDest = function (id, name, encodedDest) {
            try {
                const dest = JSON.parse(decodeURIComponent(encodedDest));
                localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
                window.location.href = '?view=map';
            } catch (e) {
                console.error('Failed to view destination:', e);
            }
        };

        function updateSavedPlacesOfflineStatus() {
            var badge = document.getElementById('saved-places-offline-badge');
            var isOffline = !navigator.onLine || window.isAppOnline === false;
            if (badge) {
                badge.style.display = isOffline ? 'inline-flex' : 'none';
            }
        }
        window.addEventListener('online', updateSavedPlacesOfflineStatus);
        window.addEventListener('offline', updateSavedPlacesOfflineStatus);
        window.addEventListener('appOnline', updateSavedPlacesOfflineStatus);
        window.addEventListener('appOffline', updateSavedPlacesOfflineStatus);
        setTimeout(updateSavedPlacesOfflineStatus, 100);

        window.addEventListener('online', function () {
            fetchSavedPlaces(true);
        });

        fetchSavedPlaces();
    })();
</script>