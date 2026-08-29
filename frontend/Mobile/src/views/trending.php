<?php
$pageTitle = 'Trending Sites';
$backRoute = 'dashboard';

?>
<?php include __DIR__ . '/../components/header.php'; ?>

<link rel="stylesheet" href="assets/css/views/trending.css">
<div class="saved-trips-page-container has-header animate-slide-up" style="padding-left: 16px; padding-right: 16px;">
    
    <!-- Search Bar -->
    <div class="trending-search-wrap">
        <i class="fa-solid fa-magnifying-glass trending-search-icon"></i>
        <input type="text" id="trending-search-input" class="trending-search-input" placeholder="Search trending spots, municipality..." autocomplete="off">
        <button type="button" id="trending-search-clear" class="trending-search-clear" style="display:none;" onclick="window.clearTrendingSearch()">
            <i class="fa-solid fa-circle-xmark"></i>
        </button>
    </div>

    <!-- Category Filter Bar -->
    <div class="trending-categories-bar" id="trending-cat-bar">
        <button type="button" class="trending-cat-pill active" onclick="window.filterTrendingCat('All', this)">
            <i class="fa-solid fa-fire"></i> All
        </button>
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Beach', this)">
            <i class="fa-solid fa-umbrella-beach"></i> Beach
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
        <button type="button" class="trending-cat-pill" onclick="window.filterTrendingCat('Nightlife', this)">
            <i class="fa-solid fa-martini-glass-citrus"></i> Nightlife
        </button>
    </div>

    <!-- Meta Info Bar -->
    <div id="trending-meta-bar" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; font-size:11.5px; color:rgba(226,232,240,0.65); font-weight:600;">
        <span id="trending-count-label">Loading trending sites...</span>
        <span id="trending-filter-active" style="display:none; color:#38bdf8; cursor:pointer;" onclick="window.resetTrendingFilters()">
            <i class="fa-solid fa-rotate-left" style="margin-right:3px;"></i> Reset
        </span>
    </div>

    <!-- Trending Grid / List -->
    <div id="trending-list">
        <p style="text-align:center; color:rgba(255,255,255,0.5); margin-top:40px;">
            <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Loading trending sites...
        </p>
    </div>
</div>

<script>
(function() {
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

    let allSpots = [];
    let currentCategory = 'All';
    let currentSearch = '';

    const matchesCategory = (cardCategory, cardName, targetCat) => {
        if (!targetCat || targetCat === 'All') return true;
        const c = (cardCategory || '').toLowerCase();
        const n = (cardName || '').toLowerCase();
        const combined = c + ' ' + n;
        const t = targetCat.toLowerCase();

        if (combined.includes(t)) return true;

        if (t === 'beach' && (combined.includes('beach') || combined.includes('island') || combined.includes('coastal') || combined.includes('surf'))) return true;
        if (t === 'mountains' && (combined.includes('mountain') || combined.includes('hiking') || combined.includes('hill') || combined.includes('peak') || combined.includes('viewpoint') || combined.includes('nature'))) return true;
        if (t === 'lakes' && (combined.includes('lake') || combined.includes('fall') || combined.includes('waterfall') || combined.includes('river') || combined.includes('spring') || combined.includes('water'))) return true;
        if (t === 'heritage' && (combined.includes('heritage') || combined.includes('historical') || combined.includes('church') || combined.includes('monument') || combined.includes('landmark') || combined.includes('museum') || combined.includes('cultural') || combined.includes('religious') || combined.includes('parish') || combined.includes('shrine'))) return true;
        if (t.includes('food') && (combined.includes('food') || combined.includes('dining') || combined.includes('restaurant') || combined.includes('cafe') || combined.includes('bistro') || combined.includes('grill'))) return true;
        if (t === 'nightlife' && (combined.includes('nightlife') || combined.includes('bar') || combined.includes('resort') || combined.includes('shopping') || combined.includes('festival') || combined.includes('club'))) return true;

        return false;
    };

    async function fetchTrending() {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const cacheKey = 'trending_spots_' + (token ? token.substring(0, 10) : '');

        await window.useCache(
            cacheKey,
            async () => {
                const res = await fetch(backendUrl + '/api/tourist/dashboard?limit=50', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (!res.ok) throw new Error("Failed to fetch trending sites");
                const data = await res.json();
                return data.trending || [];
            },
            (spots) => {
                if (spots) {
                    allSpots = spots;
                    applyFilters();
                } else {
                    const list = document.getElementById('trending-list');
                    if (list) list.innerHTML = '<p style="text-align:center; color:#999; margin-top:20px;">Failed to load trending sites.</p>';
                    updateMetaLabel(0);
                }
            },
            false,
            60000 // 1 minute TTL
        );
    }

    function applyFilters() {
        if (!allSpots || !allSpots.length) {
            renderEmptyState('No Trending Sites', 'Check back soon for newly popular attractions and trending destinations in La Union.');
            updateMetaLabel(0);
            return;
        }

        const filtered = allSpots.filter(dest => {
            // Category filter
            if (!matchesCategory(dest.category, dest.name, currentCategory)) {
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
        const hasActiveFilter = (currentCategory !== 'All' || currentSearch.trim().length > 0);

        if (countLabel) {
            if (count === 0) {
                countLabel.textContent = 'No matching destinations';
            } else if (count === 1) {
                countLabel.textContent = 'Showing 1 trending destination';
            } else {
                countLabel.textContent = `Showing ${count} trending destinations`;
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
            const img = window.getDestImage(dest);
            const badgeColor = dest.classification_status === 'EXIST' ? '#34c759' :
                (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b');
            const badgeLabel = dest.classification_status === 'EXIST' ? 'EXISTING' :
                (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL');
            
            const visits = parseInt(dest.visits) || 0;
            const visitorText = window.formatVisitorCount ? window.formatVisitorCount(visits) : (visits < 100 ? 'Less than 100 this month' : `${visits.toLocaleString()} visits`);
            const muni = dest.municipality || dest.location || '';

            html += `
                <div class="trending-card" style="animation-delay:${Math.min(i * 0.05, 0.4)}s" onclick="window.viewTrendingDest(${dest.id}, '${dest.name.replace(/'/g, "\\'")}', '${encodeURIComponent(JSON.stringify(dest))}')">
                    ${dest.classification_status ? `<div class="badge" style="background:${badgeColor};">${badgeLabel}</div>` : ''}
                    <div class="fire-icon"><i class="fa-solid fa-fire"></i></div>
                    <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.src=window.noImageFallback;">
                    <div class="overlay">
                        <div class="name">${dest.name}</div>
                        ${muni ? `<div class="meta-muni"><i class="fa-solid fa-location-dot" style="font-size:8px; margin-right:3px;"></i>${muni}</div>` : ''}
                        <div class="meta"><i class="fa-solid fa-users" style="font-size:9px;"></i>${visitorText}</div>
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
                    <i class="fa-solid fa-fire-flame-curved"></i>
                </div>
                <div class="dash-empty-title">${title}</div>
                <div class="dash-empty-desc">${desc}</div>
                <button type="button" onclick="navigateTo('map')" class="dash-empty-btn">
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
                <div class="dash-empty-title">No Spots Match Filter</div>
                <div class="dash-empty-desc">No trending destinations match your search or category selection. Try searching another keyword or reset your filter.</div>
                <button type="button" onclick="window.resetTrendingFilters()" class="dash-empty-btn">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filters
                </button>
            </div>
        `;
    }

    window.filterTrendingCat = function(category, element) {
        currentCategory = category;
        document.querySelectorAll('#trending-cat-bar .trending-cat-pill').forEach(pill => pill.classList.remove('active'));
        if (element) element.classList.add('active');
        applyFilters();
    };

    window.clearTrendingSearch = function() {
        const input = document.getElementById('trending-search-input');
        const clearBtn = document.getElementById('trending-search-clear');
        if (input) input.value = '';
        if (clearBtn) clearBtn.style.display = 'none';
        currentSearch = '';
        applyFilters();
    };

    window.resetTrendingFilters = function() {
        currentCategory = 'All';
        currentSearch = '';
        const input = document.getElementById('trending-search-input');
        const clearBtn = document.getElementById('trending-search-clear');
        if (input) input.value = '';
        if (clearBtn) clearBtn.style.display = 'none';

        const pills = document.querySelectorAll('#trending-cat-bar .trending-cat-pill');
        pills.forEach((p, idx) => {
            if (idx === 0) p.classList.add('active');
            else p.classList.remove('active');
        });

        applyFilters();
    };

    // Live search event handling
    const searchInput = document.getElementById('trending-search-input');
    const searchClearBtn = document.getElementById('trending-search-clear');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            currentSearch = e.target.value;
            if (searchClearBtn) {
                searchClearBtn.style.display = currentSearch.length > 0 ? 'flex' : 'none';
            }
            applyFilters();
        });
    }

    window.viewTrendingDest = function(id, name, encodedDest) {
        try {
            const dest = JSON.parse(decodeURIComponent(encodedDest));
            localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
            window.location.href = '?view=map';
        } catch (e) {
            console.error('Failed to view destination:', e);
        }
    };

    fetchTrending();
})();
</script>
