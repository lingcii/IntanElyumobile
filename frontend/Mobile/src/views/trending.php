<?php
$pageTitle = 'Tourist Sites';
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



<div class="trending-page-container has-header animate-slide-up">

    <!-- Offline Status Banner -->
    <div id="trending-offline-pill" style="display: none; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 14px auto; padding: 7px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 11.5px; font-weight: 800; text-align: center; width: fit-content;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
        <span>Offline Mode &bull; Cached Destination Catalog</span>
    </div>

    <!-- Segmented Tab Switcher: All Tourist Sites on Left, Trending Sites on Right -->
    <div class="trending-segmented-wrap" id="trending-segmented-wrap">
        <div class="trending-seg-slider" id="trending-seg-slider"></div>
        <button type="button" class="trending-seg-tab active" id="tab-all-spots"
            onclick="window.switchTrendingMode('all')">
            <i class="fa-solid fa-compass"></i> All Tourist Sites
        </button>
        <button type="button" class="trending-seg-tab" id="tab-trending"
            onclick="window.switchTrendingMode('trending')">
            <i class="fa-solid fa-fire"></i> Trending Sites
        </button>
    </div>

    <!-- Search Bar -->
    <div class="trending-search-wrap">
        <i class="fa-solid fa-magnifying-glass trending-search-icon"></i>
        <input type="text" id="trending-search-input" class="trending-search-input"
            placeholder="Search tourist sites, municipality..." autocomplete="off">
        <button type="button" id="trending-search-clear" class="trending-search-clear" style="display:none;"
            onclick="window.clearTrendingSearch()">
            <i class="fa-solid fa-circle-xmark"></i>
        </button>
    </div>

    <!-- Dual Filter Droplists Row: Sort on Left, Categories on Right -->
    <div class="trending-filter-dropdowns-row">
        <!-- Sort Droplist (Left) -->
        <div class="trending-dropdown-wrap">
            <i class="fa-solid fa-arrow-down-wide-short dropdown-lead-icon"></i>
            <select id="trending-sort-select" class="trending-dropdown-select" onchange="window.setSortTrending(this.value)">
                <option value="popular">Most Visited</option>
                <option value="rating">Top Rated</option>
                <option value="alpha">Name A-Z</option>
            </select>
            <i class="fa-solid fa-chevron-down dropdown-arrow-icon"></i>
        </div>

        <!-- Categories Droplist (Right) -->
        <div class="trending-dropdown-wrap">
            <i class="fa-solid fa-layer-group dropdown-lead-icon"></i>
            <select id="trending-cat-select" class="trending-dropdown-select" onchange="window.filterTrendingCat(this.value)">
                <option value="All">All Categories</option>
                <option value="Beach">Beach</option>
                <option value="Mountain">Mountain</option>
                <option value="Waterfalls">Waterfalls</option>
                <option value="River">River</option>
                <option value="Lake">Lake</option>
                <option value="Island">Island</option>
                <option value="Cave">Cave</option>
                <option value="Volcano">Volcano</option>
                <option value="Forest">Forest</option>
                <option value="Nature Park">Nature Park</option>
                <option value="Marine Sanctuary">Marine Sanctuary</option>
                <option value="Wildlife Sanctuary">Wildlife Sanctuary</option>
                <option value="Historical">Historical</option>
                <option value="Cultural Heritage">Cultural Heritage</option>
                <option value="Religious">Religious</option>
                <option value="Museum">Museum</option>
                <option value="Monument">Monument</option>
                <option value="Landmark">Landmark</option>
                <option value="Viewpoint">Viewpoint</option>
                <option value="Adventure">Adventure</option>
                <option value="Hiking">Hiking</option>
                <option value="Camping">Camping</option>
                <option value="Farm">Farm</option>
                <option value="Eco-Tourism">Eco-Tourism</option>
                <option value="Garden">Garden</option>
                <option value="Park">Park</option>
                <option value="Recreation">Recreation</option>
                <option value="Hot Spring">Hot Spring</option>
                <option value="Cold Spring">Cold Spring</option>
                <option value="Food Destination">Food Destination</option>
                <option value="Shopping">Shopping</option>
                <option value="Festival Venue">Festival Venue</option>
                <option value="Resort">Resort</option>
                <option value="Other">Other</option>
            </select>
            <i class="fa-solid fa-chevron-down dropdown-arrow-icon"></i>
        </div>
    </div>

    <!-- Meta Info Bar -->
    <div id="trending-meta-bar"
        style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; font-size:11.5px; color:#64748b; font-weight:700;">
        <span id="trending-count-label">Loading destinations...</span>
        <span id="trending-filter-active" style="display:none; color:#2563eb; font-weight:700; cursor:pointer;"
            onclick="window.resetTrendingFilters()">
            <i class="fa-solid fa-rotate-left" style="margin-right:3px;"></i> Reset
        </span>
    </div>

    <!-- Trending Grid / List -->
    <div id="trending-list">
        <p style="text-align:center; color:#64748b; margin-top:40px; font-weight:600;">
            <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px; color:#1e3a8a;"></i> Loading destinations...
        </p>
    </div>
</div>

<script>
    (function () {
        var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
        window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;

        let trendingSpots = [];
        let allDestinations = [];
        let currentMode = localStorage.getItem('intan_elyu_explore_mode') || 'all';
        let currentCategory = 'All';
        let currentSearch = '';
        let currentSort = 'popular';

        const matchesCategory = (cardCategory, cardName, cardMuni, targetCat) => {
            if (!targetCat || targetCat === 'All') return true;
            const c = (cardCategory || '').toLowerCase().trim();
            const n = (cardName || '').toLowerCase().trim();
            const m = (cardMuni || '').toLowerCase().trim();
            const combined = `${c} ${n} ${m}`;
            const t = targetCat.toLowerCase().trim();

            // 1. Direct or substring match on category field
            if (c) {
                if (c === t || c.includes(t) || t.includes(c)) return true;
                const cClean = c.replace(/[^a-z0-9]/g, '');
                const tClean = t.replace(/[^a-z0-9]/g, '');
                if (cClean && tClean && (cClean === tClean || cClean.includes(tClean) || tClean.includes(cClean))) return true;
            }

            // 2. Beach, Coastal & Surfing
            if (t === 'beach') {
                return combined.includes('beach') || combined.includes('surf') || combined.includes('coastal') || combined.includes('seascape');
            }

            // 3. Mountain & Hiking / Trail
            if (t === 'mountain' || t === 'hiking') {
                return combined.includes('mountain') || combined.includes('hiking') || combined.includes('trail') || combined.includes('peak') || combined.includes('mt.') || combined.includes('trek');
            }

            // 4. Waterfalls
            if (t === 'waterfalls') {
                return combined.includes('waterfall') || combined.includes('falls') || combined.includes('fall');
            }

            // 5. River
            if (t === 'river') {
                return combined.includes('river');
            }

            // 6. Lake
            if (t === 'lake') {
                return combined.includes('lake') || combined.includes('lagoon') || combined.includes('dam');
            }

            // 7. Island
            if (t === 'island') {
                return combined.includes('island') || combined.includes('isle') || combined.includes('islet');
            }

            // 8. Cave
            if (t === 'cave') {
                return combined.includes('cave') || combined.includes('cavern');
            }

            // 9. Volcano
            if (t === 'volcano') {
                return combined.includes('volcano');
            }

            // 10. Forest
            if (t === 'forest') {
                return combined.includes('forest') || combined.includes('woods') || combined.includes('agro-forestry') || combined.includes('tree');
            }

            // 11. Nature Park & Park & Garden
            if (t === 'nature park' || t === 'park' || t === 'garden') {
                return combined.includes('nature') || combined.includes('park') || combined.includes('garden') || combined.includes('plaza') || combined.includes('baywalk') || combined.includes('mangrove');
            }

            // 12. Marine Sanctuary & Wildlife Sanctuary
            if (t === 'marine sanctuary' || t === 'wildlife sanctuary') {
                return combined.includes('sanctuary') || combined.includes('marine') || combined.includes('wildlife') || combined.includes('turtle') || combined.includes('pawikan');
            }

            // 13. Historical, Cultural Heritage, Monument, Landmark
            if (t === 'historical' || t === 'cultural heritage' || t === 'monument' || t === 'landmark') {
                return combined.includes('historic') || combined.includes('cultural') || combined.includes('heritage') || combined.includes('monument') || combined.includes('landmark') || combined.includes('watchtower') || combined.includes('tunnel') || combined.includes('marker') || combined.includes('memorial') || combined.includes('ancestral');
            }

            // 14. Religious
            if (t === 'religious') {
                return combined.includes('religious') || combined.includes('church') || combined.includes('shrine') || combined.includes('parish') || combined.includes('basilica') || combined.includes('cathedral') || combined.includes('chapel');
            }

            // 15. Museum
            if (t === 'museum') {
                return combined.includes('museum') || combined.includes('gallery');
            }

            // 16. Viewpoint
            if (t === 'viewpoint') {
                return combined.includes('viewpoint') || combined.includes('view deck') || combined.includes('viewdeck') || combined.includes('overlook') || combined.includes('terrace');
            }

            // 17. Adventure & Camping
            if (t === 'adventure' || t === 'camping') {
                return combined.includes('adventure') || combined.includes('camp') || combined.includes('glamping');
            }

            // 18. Farm
            if (t === 'farm') {
                return combined.includes('farm') || combined.includes('plantation') || combined.includes('grapes') || combined.includes('mushroom') || combined.includes('agri');
            }

            // 19. Eco-Tourism
            if (t === 'eco-tourism') {
                return combined.includes('eco') || combined.includes('ecology');
            }

            // 20. Recreation
            if (t === 'recreation') {
                return combined.includes('recreation') || combined.includes('activity');
            }

            // 21. Hot Spring & Cold Spring
            if (t === 'hot spring' || t === 'cold spring') {
                return combined.includes('spring');
            }

            // 22. Food Destination
            if (t === 'food destination') {
                return combined.includes('food') || combined.includes('restaurant') || combined.includes('dining') || combined.includes('eatery') || combined.includes('cafe') || combined.includes('bistro') || combined.includes('grill') || combined.includes('seafood');
            }

            // 23. Shopping
            if (t === 'shopping') {
                return combined.includes('shop') || combined.includes('market') || combined.includes('mall') || combined.includes('pasalubong') || combined.includes('souvenir') || combined.includes('weaving') || combined.includes('pottery');
            }

            // 24. Festival Venue
            if (t === 'festival venue') {
                return combined.includes('festival') || combined.includes('events') || combined.includes('arena') || combined.includes('coliseum') || combined.includes('stadium');
            }

            // 25. Resort
            if (t === 'resort') {
                return combined.includes('resort');
            }

            // 26. Other
            if (t === 'other') {
                return true;
            }

            return false;
        };

        window.switchTrendingMode = function (mode) {
            currentMode = mode;
            localStorage.setItem('intan_elyu_explore_mode', mode);

            const wrap = document.getElementById('trending-segmented-wrap');
            const tabTrending = document.getElementById('tab-trending');
            const tabAll = document.getElementById('tab-all-spots');
            const input = document.getElementById('trending-search-input');

            if (mode === 'trending') {
                if (wrap) wrap.classList.add('mode-trending');
                if (tabTrending) tabTrending.classList.add('active');
                if (tabAll) tabAll.classList.remove('active');
                if (input) input.placeholder = "Search trending sites, municipality...";
                if (currentSort === 'popular') {
                    currentSort = 'rating';
                    const sortSelect = document.getElementById('trending-sort-select');
                    if (sortSelect) sortSelect.value = 'rating';
                }
            } else {
                if (wrap) wrap.classList.remove('mode-trending');
                if (tabAll) tabAll.classList.add('active');
                if (tabTrending) tabTrending.classList.remove('active');
                if (input) input.placeholder = "Search tourist sites, municipality...";
            }

            applyFilters();
        };

        window.setSortTrending = function (sortKey) {
            currentSort = sortKey;
            const sortSelect = document.getElementById('trending-sort-select');
            if (sortSelect && sortSelect.value !== sortKey) sortSelect.value = sortKey;
            applyFilters();
        };

        async function fetchAllData(forceRefresh = false) {
            const token = localStorage.getItem('intan_elyu_token');

            // 1. Fetch Trending Spots
            const trendCacheKey = 'trending_spots_' + (token ? token.substring(0, 10) : 'guest');
            const p1 = window.useCache(
                trendCacheKey,
                async () => {
                    const headers = { 'Accept': 'application/json' };
                    if (token) headers['Authorization'] = 'Bearer ' + token;
                    const res = await fetch(backendUrl + '/api/tourist/dashboard?limit=50', { headers });
                    if (!res.ok) throw new Error("Failed to fetch trending spots");
                    const data = await res.json();
                    return data.trending || [];
                },
                (spots, isCached) => {
                    if (spots) {
                        trendingSpots = (spots || []).filter(d => !d.status || !['pending', 'draft', 'rejected'].includes(d.status.toLowerCase()));
                        if (!isCached) applyFilters();
                    }
                },
                Boolean(forceRefresh),
                60000
            );

            // 2. Fetch All Destinations from Map Data
            const mapCacheKey = 'public_map_data';
            const p2 = window.useCache(
                mapCacheKey,
                async () => {
                    const res = await fetch(backendUrl + '/api/public/map');
                    if (!res.ok) throw new Error("Failed to fetch map destinations");
                    return await res.json();
                },
                (data, isCached) => {
                    if (data && data.destinations) {
                        allDestinations = (data.destinations || []).filter(d => !d.status || !['pending', 'draft', 'rejected'].includes(d.status.toLowerCase()));
                        if (!isCached) applyFilters();
                    }
                },
                Boolean(forceRefresh),
                60000
            );

            try {
                await Promise.allSettled([p1, p2]);
            } catch (e) {
                console.warn("Data loading partial failure:", e);
            }

            // Ambient offline resilience: Fall back to dashboard cached spots map if destinations list is empty
            if (!allDestinations || !allDestinations.length) {
                try {
                    const fallbackSpotsRaw = localStorage.getItem('intan_elyu_cached_spots_map');
                    if (fallbackSpotsRaw) {
                        const fallbackList = JSON.parse(fallbackSpotsRaw);
                        if (Array.isArray(fallbackList) && fallbackList.length > 0) {
                            allDestinations = fallbackList;
                            if (!trendingSpots || !trendingSpots.length) {
                                trendingSpots = fallbackList.slice(0, 10);
                            }
                        }
                    }
                } catch (e) { }
            }

            // Sync active mode and category on initial load
            const savedMode = localStorage.getItem('intan_elyu_explore_mode');
            if (savedMode) {
                currentMode = savedMode;
            }
            const savedCat = localStorage.getItem('intan_elyu_filter_category');
            if (savedCat) {
                currentCategory = savedCat;
                const catSelect = document.getElementById('trending-cat-select');
                if (catSelect) catSelect.value = savedCat;
            }
            window.switchTrendingMode(currentMode);
        }

        function applyFilters() {
            let sourceList = [];
            if (currentMode === 'trending') {
                sourceList = (trendingSpots && trendingSpots.length > 0) ? [...trendingSpots] : [];
                // If API trending spots is empty or has few items, augment from all destinations with ratings >= 3.0 or top rated
                if (sourceList.length < 6 && allDestinations && allDestinations.length > 0) {
                    const highRated = allDestinations.filter(d => {
                        const r = parseFloat(d.rating || d.reviews_avg_rating || 0);
                        return r >= 3.0 || (parseInt(d.visits) || 0) > 0;
                    }).sort((a, b) => {
                        const rDiff = (parseFloat(b.rating || b.reviews_avg_rating || 0)) - (parseFloat(a.rating || a.reviews_avg_rating || 0));
                        if (Math.abs(rDiff) > 0.01) return rDiff;
                        return (parseInt(b.visits) || 0) - (parseInt(a.visits) || 0);
                    });
                    highRated.forEach(spot => {
                        if (!sourceList.some(existing => String(existing.id) === String(spot.id))) {
                            sourceList.push(spot);
                        }
                    });
                }
                // If still empty, fall back to top destinations
                if (!sourceList.length && allDestinations && allDestinations.length > 0) {
                    sourceList = [...allDestinations].slice(0, 10);
                }
            } else {
                sourceList = allDestinations || [];
            }

            if (!sourceList || !sourceList.length) {
                if (currentMode === 'trending') {
                    renderEmptyState('No Trending Sites', 'Check back soon for newly popular attractions and trending destinations in La Union.');
                } else {
                    renderEmptyState('No Destinations Available', 'Unable to load destinations right now. Please check your internet connection.');
                }
                updateMetaLabel(0);
                return;
            }

            let filtered = sourceList.filter(dest => {
                // Category filter
                if (!matchesCategory(dest.category, dest.name, dest.municipality || dest.location, currentCategory)) {
                    return false;
                }

                // Search filter
                if (currentSearch.trim()) {
                    const q = currentSearch.trim().toLowerCase();
                    const n = (dest.name || '').toLowerCase();
                    const m = (dest.municipality || dest.location || '').toLowerCase();
                    const c = (dest.category || '').toLowerCase();
                    const d = (dest.description || '').toLowerCase();
                    if (!n.includes(q) && !m.includes(q) && !c.includes(q) && !d.includes(q)) {
                        return false;
                    }
                }

                return true;
            });

            // Sorting
            filtered.sort((a, b) => {
                if (currentSort === 'popular') {
                    const vDiff = (parseInt(b.visits) || 0) - (parseInt(a.visits) || 0);
                    if (vDiff !== 0) return vDiff;
                    return (parseFloat(b.rating || b.reviews_avg_rating || 0)) - (parseFloat(a.rating || a.reviews_avg_rating || 0));
                } else if (currentSort === 'rating') {
                    const rDiff = (parseFloat(b.rating || b.reviews_avg_rating || 0)) - (parseFloat(a.rating || a.reviews_avg_rating || 0));
                    if (Math.abs(rDiff) > 0.01) return rDiff;
                    return (parseInt(b.visits) || 0) - (parseInt(a.visits) || 0);
                } else if (currentSort === 'alpha') {
                    return (a.name || '').localeCompare(b.name || '');
                }
                return 0;
            });

            updateMetaLabel(filtered.length);

            if (!filtered.length) {
                renderFilterEmptyState();
            } else {
                renderGrid(filtered);
            }
        }

        function updateMetaLabel(count) {
            const countLabel = document.getElementById('trending-count-label');
            const resetBtn = document.getElementById('trending-filter-active');
            const hasActiveFilter = (currentCategory !== 'All' || currentSearch.trim().length > 0 || currentSort !== 'popular');

            if (countLabel) {
                if (count === 0) {
                    countLabel.textContent = 'No matching tourist sites';
                } else if (currentMode === 'trending') {
                    countLabel.textContent = count === 1 ? 'Showing 1 trending site' : `Showing ${count} trending sites`;
                } else {
                    countLabel.textContent = count === 1 ? 'Showing 1 tourist site in La Union' : `Showing ${count} tourist sites in La Union`;
                }
            }

            if (resetBtn) {
                resetBtn.style.display = hasActiveFilter ? 'inline-flex' : 'none';
            }
        }

        function renderGrid(spots) {
            const list = document.getElementById('trending-list');
            if (!list) return;

            let html = '<div class="trending-grid">';
            spots.forEach((dest, i) => {
                const img = window.getDestImage(dest, 400);
                const badgeColor = dest.classification_status === 'EXIST' ? '#0284c7' :
                    (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981');
                const badgeLabel = dest.classification_status === 'EXIST' ? 'EXISTING' :
                    (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL');

                const visits = parseInt(dest.visits) || 0;
                const muni = dest.municipality || dest.location || 'La Union';
                const numRating = (dest.rating !== undefined && dest.rating !== null && !isNaN(parseFloat(dest.rating)))
                    ? parseFloat(dest.rating)
                    : (dest.reviews_avg_rating ? parseFloat(dest.reviews_avg_rating) : (dest.rating_star ? parseFloat(dest.rating_star) : 0));
                const displayRating = (numRating > 0 ? numRating.toFixed(1) : '3.0');
                const fee = (dest.entrance_fee && parseFloat(dest.entrance_fee) > 0) ? `₱${parseFloat(dest.entrance_fee).toFixed(0)}` : 'Free';
                const cat = dest.category || 'Spot';

                const iconHtml = currentMode === 'trending'
                    ? `<div class="fire-icon"><i class="fa-solid fa-fire"></i></div>`
                    : `<div class="fire-icon" style="color:#2563eb; box-shadow:0 2px 8px rgba(37,99,235,0.25);"><i class="fa-solid fa-compass"></i></div>`;

                const metaBottomHtml = `
                    <div class="card-bottom-tags" style="display:flex; align-items:center; gap:5px; flex-wrap:wrap; margin-top:5px;">
                        <span class="card-rating" style="background:rgba(245,158,11,0.22); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); padding:2px 7px; border-radius:100px; font-size:10px; font-weight:800; display:inline-flex; align-items:center; gap:3px;">
                            <i class="fa-solid fa-star" style="font-size:9px; color:#f59e0b;"></i> ${displayRating}
                        </span>
                        ${currentMode === 'trending'
                            ? `<span style="font-size:9.5px; color:#fed7aa; font-weight:800; margin-left:auto; display:inline-flex; align-items:center; gap:3px;"><i class="fa-solid fa-fire" style="color:#f97316; font-size:9px;"></i> Trending</span>`
                            : `<span class="card-cat-pill">${cat}</span><span class="card-fee">${fee}</span>`}
                    </div>`;

                html += `
                <div class="trending-card" style="animation-delay:${Math.min(i * 0.04, 0.35)}s" onclick="window.viewTrendingDest(${dest.id}, '${dest.name.replace(/'/g, "\\'")}', '${encodeURIComponent(JSON.stringify(dest))}')">
                    ${dest.classification_status ? `<div class="badge" style="background:${badgeColor};">${badgeLabel}</div>` : ''}
                    ${iconHtml}
                    <img src="${img}" alt="" loading="lazy" decoding="async" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\\'")}', '${(dest.municipality || '').replace(/'/g, "\\'")}'); else this.src='assets/img/no_image.svg';">
                    <div class="overlay">
                        <div class="name">${dest.name}</div>
                        <div class="meta-muni"><i class="fa-solid fa-location-dot" style="font-size:8px; margin-right:3px;"></i>${muni}</div>
                        ${metaBottomHtml}
                    </div>
                </div>
            `;
            });
            html += '</div>';
            list.innerHTML = html;
        }

        function renderEmptyState(title, desc) {
            const list = document.getElementById('trending-list');
            if (!list) return;
            list.innerHTML = `
            <div class="dash-empty-state">
                <div class="dash-empty-icon-wrap">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div class="dash-empty-title">${title}</div>
                <div class="dash-empty-desc">${desc}</div>
                <button type="button" onclick="if(typeof navigateTo==='function') navigateTo('map'); else window.location.href='?view=map';" class="dash-empty-btn">
                    <i class="fa-solid fa-location-arrow"></i> Open Map
                </button>
            </div>
        `;
        }

        function renderFilterEmptyState() {
            const list = document.getElementById('trending-list');
            if (!list) return;
            list.innerHTML = `
            <div class="dash-empty-state">
                <div class="dash-empty-icon-wrap">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div class="dash-empty-title">No Destinations Found</div>
                <div class="dash-empty-desc">No destinations match your search or selected category. Try selecting another category or reset your filter.</div>
                <button type="button" onclick="window.resetTrendingFilters()" class="dash-empty-btn">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filters
                </button>
            </div>
        `;
        }

        window.filterTrendingCat = function (category) {
            currentCategory = category || 'All';
            const catSelect = document.getElementById('trending-cat-select');
            if (catSelect && catSelect.value !== currentCategory) catSelect.value = currentCategory;
            applyFilters();
        };

        window.clearTrendingSearch = function () {
            const input = document.getElementById('trending-search-input');
            const clearBtn = document.getElementById('trending-search-clear');
            if (input) input.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            currentSearch = '';
            applyFilters();
        };

        window.resetTrendingFilters = function () {
            currentCategory = 'All';
            currentSearch = '';
            currentSort = 'popular';

            const input = document.getElementById('trending-search-input');
            const clearBtn = document.getElementById('trending-search-clear');
            if (input) input.value = '';
            if (clearBtn) clearBtn.style.display = 'none';

            const sortSelect = document.getElementById('trending-sort-select');
            if (sortSelect) sortSelect.value = 'popular';
            const catSelect = document.getElementById('trending-cat-select');
            if (catSelect) catSelect.value = 'All';

            applyFilters();
        };

        // Live search event handling
        const searchInput = document.getElementById('trending-search-input');
        const searchClearBtn = document.getElementById('trending-search-clear');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                currentSearch = e.target.value;
                if (searchClearBtn) {
                    searchClearBtn.style.display = currentSearch.length > 0 ? 'flex' : 'none';
                }
                applyFilters();
            });
        }

        window.viewTrendingDest = function (id, name, encodedDest) {
            try {
                const dest = JSON.parse(decodeURIComponent(encodedDest));
                localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
                if (typeof window.navigateTo === 'function') {
                    window.navigateTo('map');
                } else {
                    window.location.href = '?view=map';
                }
            } catch (e) {
                console.error('Failed to view destination:', e);
            }
        };

        window.fetchTrendingSpots = fetchAllData;
        window.fetchAllTouristData = fetchAllData;
        window.fetchAllData = fetchAllData;

        // Ambient automatic reconnect & lifecycle listeners
        window.addEventListener('online', function () {
            const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
            if (current !== 'trending') {
                window.trendingNeedsRefresh = true;
                return;
            }
            fetchAllData(true);
        });
        window.addEventListener('appOnline', function () {
            const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
            if (current !== 'trending') {
                window.trendingNeedsRefresh = true;
                return;
            }
            fetchAllData(true);
        });
        document.addEventListener('visibilitychange', function () {
            const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
            if (current !== 'trending') return;
            if (!document.hidden && (!allDestinations || allDestinations.length === 0)) {
                fetchAllData(true);
            }
        });
        window.addEventListener('viewLoaded', function (e) {
            if (e && e.detail && e.detail.view === 'trending') {
                const mustForce = (!allDestinations || allDestinations.length === 0);
                fetchAllData(mustForce);
            }
        });

        function updateTrendingOfflineStatus() {
            var pill = document.getElementById('trending-offline-pill');
            var isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
            if (pill) {
                pill.style.setProperty('display', isOffline ? 'inline-flex' : 'none', 'important');
            }
            if (!isOffline) {
                try {
                    localStorage.removeItem('intan_elyu_offline_mode');
                    localStorage.removeItem('intan_elyu_offline_guest');
                } catch(e) {}
            }
        }
        window.addEventListener('online', () => {
            updateTrendingOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('offline', updateTrendingOfflineStatus);
        window.addEventListener('appOnline', () => {
            updateTrendingOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('appOffline', updateTrendingOfflineStatus);
        setTimeout(updateTrendingOfflineStatus, 100);

        fetchAllData();
    })();
</script>