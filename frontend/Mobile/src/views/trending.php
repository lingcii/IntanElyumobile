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

<link rel="stylesheet" href="assets/css/views/trending.css">

<style>
/* Guaranteed High Visibility Outlines for Mobile */
body[data-view="trending"] .mobile-header,
.mobile-header {
    background: #1e3a8a !important;
    backdrop-filter: blur(24px) !important;
    -webkit-backdrop-filter: blur(24px) !important;
    border: none !important;
    outline: none !important;
    border-bottom: none !important;
    box-shadow: none !important;
}

/* 1. Segmented Switcher Capsule */
.trending-segmented-wrap {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    position: relative !important;
    background: #e2e8f0 !important;
    border: 2px solid #94a3b8 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    padding: 4px !important;
    border-radius: 100px !important;
    outline: none !important;
    margin-top: 0 !important;
    margin-bottom: 14px !important;
    user-select: none !important;
    -webkit-tap-highlight-color: transparent !important;
    overflow: hidden !important;
}

.trending-seg-slider {
    position: absolute !important;
    top: 4px !important;
    bottom: 4px !important;
    left: 4px !important;
    width: calc(50% - 4px) !important;
    border-radius: 100px !important;
    background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
    box-shadow: 0 4px 14px rgba(32, 63, 141, 0.4) !important;
    transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1) !important;
    z-index: 1 !important;
    pointer-events: none !important;
    transform: translateX(0) !important;
}

.trending-segmented-wrap.mode-trending .trending-seg-slider {
    transform: translateX(100%) !important;
}

.trending-seg-tab {
    position: relative !important;
    z-index: 2 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    padding: 10px 14px !important;
    border-radius: 100px !important;
    border: none !important;
    outline: none !important;
    background: transparent !important;
    color: #1e293b !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    cursor: pointer !important;
    transition: color 0.25s ease, transform 0.2s cubic-bezier(0.22, 1, 0.36, 1) !important;
    -webkit-tap-highlight-color: transparent !important;
}

.trending-seg-tab:active {
    transform: scale(0.96) !important;
}

.trending-seg-tab.active {
    color: #ffffff !important;
    font-weight: 800 !important;
}

.trending-seg-tab i {
    font-size: 13px !important;
}

/* 2. Search Bar */
.trending-search-wrap {
    position: relative !important;
    margin-top: 0 !important;
    margin-bottom: 14px !important;
    display: flex !important;
    align-items: center !important;
}

.trending-search-input {
    width: 100% !important;
    height: 48px !important;
    background: #ffffff !important;
    border: 2px solid #94a3b8 !important;
    outline: none !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
    border-radius: 100px !important;
    padding: 0 40px 0 42px !important;
    color: #0f172a !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    font-family: inherit !important;
    transition: all 0.25s ease !important;
}

.trending-search-input:focus {
    background: #ffffff !important;
    border-color: #203f8d !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(32, 63, 141, 0.2) !important;
}

.trending-search-input::placeholder {
    color: #64748b !important;
    font-weight: 600 !important;
}

.trending-search-icon {
    position: absolute !important;
    left: 15px !important;
    color: #334155 !important;
    font-size: 15px !important;
    pointer-events: none !important;
}

.trending-search-clear {
    position: absolute !important;
    right: 12px !important;
    background: none !important;
    border: none !important;
    color: #64748b !important;
    font-size: 15px !important;
    cursor: pointer !important;
    padding: 4px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: color 0.2s ease !important;
}

.trending-search-clear:active {
    color: #1e3a8a !important;
}

/* 3. Category Filter Pills */
.trending-categories-bar {
    display: flex !important;
    gap: 8px !important;
    overflow-x: auto !important;
    padding-bottom: 8px !important;
    margin-bottom: 12px !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

.trending-categories-bar::-webkit-scrollbar {
    display: none !important;
}

.trending-cat-pill {
    padding: 8px 16px !important;
    border-radius: 100px !important;
    font-size: 12.5px !important;
    font-weight: 800 !important;
    color: #1e293b !important;
    background: #f8fafc !important;
    border: 2px solid #94a3b8 !important;
    outline: none !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
    flex-shrink: 0 !important;
}

.trending-cat-pill i {
    font-size: 12px !important;
    color: #203f8d !important;
}

.trending-cat-pill:not(.active):active {
    transform: scale(0.96) !important;
    background: #e2e8f0 !important;
    border-color: #64748b !important;
}

.trending-cat-pill.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
    border: 2px solid #203f8d !important;
    outline: none !important;
    box-shadow: 0 4px 14px rgba(32, 63, 141, 0.4) !important;
}

.trending-cat-pill.active i {
    color: #ffffff !important;
}

/* 4. Sort Chips Bar */
.trending-sort-bar {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    overflow-x: auto !important;
    padding-bottom: 8px !important;
    margin-bottom: 12px !important;
    scrollbar-width: none !important;
}

.trending-sort-bar::-webkit-scrollbar {
    display: none !important;
}

.sort-label {
    font-size: 12px !important;
    font-weight: 800 !important;
    color: #1e293b !important;
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
    flex-shrink: 0 !important;
    margin-right: 4px !important;
}

.sort-label i {
    color: #203f8d !important;
}

.sort-chip {
    padding: 7px 15px !important;
    border-radius: 100px !important;
    font-size: 11.5px !important;
    font-weight: 800 !important;
    background: #f8fafc !important;
    border: 2px solid #94a3b8 !important;
    outline: none !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
    color: #1e293b !important;
    cursor: pointer !important;
    white-space: nowrap !important;
    transition: all 0.2s ease !important;
    flex-shrink: 0 !important;
}

.sort-chip:not(.active):active {
    transform: scale(0.96) !important;
    background: #e2e8f0 !important;
    border-color: #64748b !important;
}

.sort-chip.active {
    background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important;
    color: #ffffff !important;
    border: 2px solid #203f8d !important;
    outline: none !important;
    box-shadow: 0 2px 8px rgba(32, 63, 141, 0.35) !important;
}

/* 5. Destination Cards */
.trending-card {
    position: relative !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    background: #f8fafc !important;
    border: 2px solid #cbd5e1 !important;
    outline: none !important;
    cursor: pointer !important;
    aspect-ratio: 1 / 1 !important;
    animation: trendFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.1) !important;
}
</style>

<div class="trending-page-container has-header animate-slide-up">

    <!-- Segmented Tab Switcher: All Tourist Sites on Left, Trending Sites on Right -->
    <div class="trending-segmented-wrap" id="trending-segmented-wrap">
        <div class="trending-seg-slider" id="trending-seg-slider"></div>
        <button type="button" class="trending-seg-tab active" id="tab-all-spots" onclick="window.switchTrendingMode('all')">
            <i class="fa-solid fa-compass"></i> All Tourist Sites
        </button>
        <button type="button" class="trending-seg-tab" id="tab-trending" onclick="window.switchTrendingMode('trending')">
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

    <!-- Category Filter Bar -->
    <div class="trending-categories-bar" id="trending-cat-bar">
        <button type="button" class="trending-cat-pill active" onclick="window.filterTrendingCat('All', this)">
            <i class="fa-solid fa-layer-group"></i> All
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Beach', this)">
            <i class="fa-solid fa-umbrella-beach"></i> Beach
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Nature', this)">
            <i class="fa-solid fa-tree"></i> Nature & Parks
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Mountains', this)">
            <i class="fa-solid fa-mountain-sun"></i> Mountains
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Lakes', this)">
            <i class="fa-solid fa-water"></i> Lakes & Falls
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Heritage', this)">
            <i class="fa-solid fa-landmark"></i> Heritage
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Food & Dining', this)">
            <i class="fa-solid fa-utensils"></i> Food
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Farm', this)">
            <i class="fa-solid fa-seedling"></i> Farms
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Arts & craft', this)">
            <i class="fa-solid fa-palette"></i> Arts & Crafts
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Nightlife', this)">
            <i class="fa-solid fa-martini-glass-citrus"></i> Nightlife
        </button>
    </div>

    <!-- Sort Bar -->
    <div class="trending-sort-bar" id="trending-sort-bar">
        <span class="sort-label"><i class="fa-solid fa-arrow-down-wide-short"></i> Sort:</span>
        <button type="button" class="sort-chip active" data-sort="popular" onclick="window.setSortTrending('popular', this)">Most Visited</button>
        <button type="button" class="sort-chip" data-sort="rating" onclick="window.setSortTrending('rating', this)">Top Rated</button>
        <button type="button" class="sort-chip" data-sort="alpha" onclick="window.setSortTrending('alpha', this)">Name A-Z</button>
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

            if (c === t || c.includes(t) || t.includes(c)) return true;
            if (` ${c} `.includes(` ${t} `)) return true;

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

            // Nightlife & Recreation
            if (t.includes('nightlife') || t.includes('bar') || t.includes('resort') || t.includes('recreation')) {
                return combined.includes('nightlife') || combined.includes('bar') || combined.includes('resort') || 
                       combined.includes('shopping') || combined.includes('festival') || combined.includes('club') || combined.includes('recreation');
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
            } else {
                if (wrap) wrap.classList.remove('mode-trending');
                if (tabAll) tabAll.classList.add('active');
                if (tabTrending) tabTrending.classList.remove('active');
                if (input) input.placeholder = "Search tourist sites, municipality...";
            }

            applyFilters();
        };

        window.setSortTrending = function (sortKey, btn) {
            currentSort = sortKey;
            document.querySelectorAll('#trending-sort-bar .sort-chip').forEach(c => c.classList.remove('active'));
            if (btn) btn.classList.add('active');
            applyFilters();
        };

        async function fetchAllData() {
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
                (spots) => {
                    if (spots) trendingSpots = (spots || []).filter(d => !d.status || d.status.toLowerCase() !== 'pending');
                },
                false,
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
                (data) => {
                    if (data && data.destinations) {
                        allDestinations = (data.destinations || []).filter(d => !d.status || d.status.toLowerCase() !== 'pending');
                    }
                },
                false,
                60000
            );

            try {
                await Promise.allSettled([p1, p2]);
            } catch (e) {
                console.warn("Data loading partial failure:", e);
            }

            // Sync active mode and category on initial load
            const savedMode = localStorage.getItem('intan_elyu_explore_mode');
            if (savedMode) {
                currentMode = savedMode;
            }
            const savedCat = localStorage.getItem('intan_elyu_filter_category');
            if (savedCat) {
                currentCategory = savedCat;
                document.querySelectorAll('#trending-cat-bar .trending-cat-pill').forEach(pill => {
                    const text = (pill.textContent || '').trim();
                    if (text.toLowerCase().includes(savedCat.toLowerCase())) {
                        pill.classList.add('active');
                    } else {
                        pill.classList.remove('active');
                    }
                });
            }
            window.switchTrendingMode(currentMode);
        }

        function applyFilters() {
            const sourceList = (currentMode === 'trending') ? (trendingSpots || []) : (allDestinations || []);

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
                    return (parseInt(b.visits) || 0) - (parseInt(a.visits) || 0);
                } else if (currentSort === 'rating') {
                    return (parseFloat(b.rating) || 0) - (parseFloat(a.rating) || 0);
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
                const badgeColor = dest.classification_status === 'EXIST' ? '#34c759' :
                    (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b');
                const badgeLabel = dest.classification_status === 'EXIST' ? 'EXISTING' :
                    (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL');

                const visits = parseInt(dest.visits) || 0;
                const visitorText = window.formatVisitorCount ? window.formatVisitorCount(visits) : (visits < 100 ? 'Popular spot' : `${visits.toLocaleString()} visits`);
                const muni = dest.municipality || dest.location || 'La Union';
                const rating = (dest.rating !== undefined && dest.rating !== null && !isNaN(parseFloat(dest.rating))) ? parseFloat(dest.rating).toFixed(1) : (dest.reviews_avg_rating ? parseFloat(dest.reviews_avg_rating).toFixed(1) : '0.0');
                const fee = (dest.entrance_fee && parseFloat(dest.entrance_fee) > 0) ? `₱${parseFloat(dest.entrance_fee).toFixed(0)}` : 'Free';
                const cat = dest.category || 'Spot';

                const iconHtml = currentMode === 'trending'
                    ? `<div class="fire-icon"><i class="fa-solid fa-fire"></i></div>`
                    : `<div class="fire-icon" style="color:#2563eb; box-shadow:0 2px 8px rgba(37,99,235,0.25);"><i class="fa-solid fa-compass"></i></div>`;

                const metaBottomHtml = currentMode === 'trending'
                    ? `<div class="meta"><i class="fa-solid fa-users" style="font-size:9px;"></i>${visitorText}</div>`
                    : `<div class="card-bottom-tags">
                            <span class="card-cat-pill">${cat}</span>
                            <span class="card-rating"><i class="fa-solid fa-star" style="font-size:8px;"></i> ${rating}</span>
                            <span class="card-fee">${fee}</span>
                       </div>`;

                html += `
                <div class="trending-card" style="animation-delay:${Math.min(i * 0.04, 0.35)}s" onclick="window.viewTrendingDest(${dest.id}, '${dest.name.replace(/'/g, "\\'")}', '${encodeURIComponent(JSON.stringify(dest))}')">
                    ${dest.classification_status ? `<div class="badge" style="background:${badgeColor};">${badgeLabel}</div>` : ''}
                    ${iconHtml}
                    <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.src=window.noImageFallback || 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400';">
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
            <div class="dash-empty-state" style="margin-top: 24px !important;">
                <div class="dash-empty-icon-wrap">
                    <i class="fa-solid fa-compass"></i>
                </div>
                <div class="dash-empty-title">${title}</div>
                <div class="dash-empty-desc">${desc}</div>
                <button type="button" onclick="if(typeof navigateTo==='function') navigateTo('map'); else window.location.href='?view=map';" class="dash-empty-btn">
                    <i class="fa-solid fa-location-arrow"></i> Explore Map
                </button>
            </div>
        `;
        }

        function renderFilterEmptyState() {
            const list = document.getElementById('trending-list');
            if (!list) return;
            list.innerHTML = `
            <div class="dash-empty-state" style="margin-top: 24px !important;">
                <div class="dash-empty-icon-wrap">
                    <i class="fa-solid fa-compass"></i>
                </div>
                <div class="dash-empty-title">No Sites Match Filter</div>
                <div class="dash-empty-desc">No destinations match your search or category selection. Try searching another keyword or reset your filter.</div>
                <button type="button" onclick="window.resetTrendingFilters()" class="dash-empty-btn">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filters
                </button>
            </div>
        `;
        }

        window.filterTrendingCat = function (category, element) {
            currentCategory = category;
            document.querySelectorAll('#trending-cat-bar .trending-cat-pill').forEach(pill => pill.classList.remove('active'));
            if (element) element.classList.add('active');
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

            const pills = document.querySelectorAll('#trending-cat-bar .trending-cat-pill');
            pills.forEach((p, idx) => {
                if (idx === 0) p.classList.add('active');
                else p.classList.remove('active');
            });

            const sortChips = document.querySelectorAll('#trending-sort-bar .sort-chip');
            sortChips.forEach((c, idx) => {
                if (idx === 0) c.classList.add('active');
                else c.classList.remove('active');
            });

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

        fetchAllData();
    })();
</script>