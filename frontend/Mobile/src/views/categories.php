<?php
$pageTitle = 'Explore Categories';
$backRoute = 'dashboard';
?>
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="categories-page-container has-header animate-slide-up">

    <!-- Offline Status Pill -->
    <div id="categories-offline-pill" style="display: none; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 12px auto; padding: 6px 14px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 11px; font-weight: 800; text-align: center; width: fit-content;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
        <span>Offline Mode &bull; Cached Category Catalog</span>
    </div>

    <!-- Search Bar -->
    <div class="categories-search-wrap">
        <i class="fa-solid fa-magnifying-glass categories-search-icon"></i>
        <input type="text" id="categories-search-input" class="categories-search-input"
            placeholder="Search spots by name or municipality..." autocomplete="off">
        <button type="button" id="categories-search-clear" class="categories-search-clear" style="display:none;"
            onclick="window.clearCategorySearch()">
            <i class="fa-solid fa-circle-xmark"></i>
        </button>
    </div>

    <!-- Scrollable Category Tabs Bar -->
    <div class="categories-tabs-scroll-wrap" id="categories-tabs-scroll-wrap">
        <div class="categories-tabs-list" id="categories-tabs-list">
            <button type="button" class="category-tab-btn active" data-cat="All" onclick="window.switchCategoryTab('All', this)">
                <i class="fa-solid fa-compass"></i> All Spots
            </button>
            <button type="button" class="category-tab-btn" data-cat="Beach" onclick="window.switchCategoryTab('Beach', this)">
                <i class="fa-solid fa-water"></i> Beaches
            </button>
            <button type="button" class="category-tab-btn" data-cat="Nature" onclick="window.switchCategoryTab('Nature', this)">
                <i class="fa-solid fa-mountain"></i> Nature & Park
            </button>
            <button type="button" class="category-tab-btn" data-cat="Heritage" onclick="window.switchCategoryTab('Heritage', this)">
                <i class="fa-solid fa-landmark-dome"></i> Cultural & Heritage
            </button>
            <button type="button" class="category-tab-btn" data-cat="Food & Dining" onclick="window.switchCategoryTab('Food & Dining', this)">
                <i class="fa-solid fa-utensils"></i> Food & Dining
            </button>
            <button type="button" class="category-tab-btn" data-cat="Mountains" onclick="window.switchCategoryTab('Mountains', this)">
                <i class="fa-solid fa-mountain-sun"></i> Mountains
            </button>
            <button type="button" class="category-tab-btn" data-cat="Lakes" onclick="window.switchCategoryTab('Lakes', this)">
                <i class="fa-solid fa-droplet"></i> Lakes & Falls
            </button>
            <button type="button" class="category-tab-btn" data-cat="Farm" onclick="window.switchCategoryTab('Farm', this)">
                <i class="fa-solid fa-seedling"></i> Farms
            </button>
            <button type="button" class="category-tab-btn" data-cat="Museum" onclick="window.switchCategoryTab('Museum', this)">
                <i class="fa-solid fa-building-columns"></i> Museums
            </button>
            <button type="button" class="category-tab-btn" data-cat="Religious" onclick="window.switchCategoryTab('Religious', this)">
                <i class="fa-solid fa-church"></i> Churches
            </button>
            <button type="button" class="category-tab-btn" data-cat="Adventure" onclick="window.switchCategoryTab('Adventure', this)">
                <i class="fa-solid fa-campground"></i> Adventure
            </button>
            <button type="button" class="category-tab-btn" data-cat="Nightlife" onclick="window.switchCategoryTab('Nightlife', this)">
                <i class="fa-solid fa-martini-glass-citrus"></i> Nightlife
            </button>
        </div>
    </div>

    <!-- Weather Advisory Banner if applicable -->
    <div id="category-weather-badge" class="category-weather-badge">
        <i class="fa-solid fa-cloud-sun"></i>
        <span id="category-weather-text">Weather Recommendation</span>
    </div>

    <!-- Active Category Meta Bar (Count on Left, Sort on Right) -->
    <div class="categories-meta-bar">
        <span class="categories-count-text" id="categories-count-text">Loading spots...</span>
        <select id="categories-sort-select" class="categories-sort-select" onchange="window.setCategorySort(this.value)">
            <option value="popular">Most Visited</option>
            <option value="rating">Top Rated</option>
            <option value="alpha">Name A-Z</option>
        </select>
    </div>

    <!-- Spots Grid -->
    <div id="categories-list-container">
        <div class="dash-loading-state" style="padding: 40px 0; text-align: center; color: #64748b;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: #1e3a8a; margin-bottom: 8px;"></i>
            <div style="font-size: 13px; font-weight: 700;">Loading tourist sites...</div>
        </div>
    </div>

</div>

<script>
    (function () {
        let allDestinations = [];
        let currentCategory = 'All';
        let currentSearch = '';
        let currentSort = 'popular';

        // Canonical mapping to reconcile weather recommendation filter keys with tab data-cat values
        function canonicalizeCategory(cat) {
            if (!cat) return 'All';
            const raw = String(cat).trim();
            const lower = raw.toLowerCase();
            if (lower === 'all' || lower === 'all spots') return 'All';
            if (lower.includes('beach') || lower.includes('surf') || lower.includes('coastal')) return 'Beach';
            if (lower.includes('museum') || lower.includes('gallery')) return 'Museum';
            if (lower.includes('church') || lower.includes('religious') || lower.includes('shrine') || lower.includes('cathedral')) return 'Religious';
            if (lower.includes('heritage') || lower.includes('cultural') || lower.includes('historic')) return 'Heritage';
            if (lower.includes('food') || lower.includes('dining') || lower.includes('cafe') || lower.includes('restaurant')) return 'Food & Dining';
            if (lower.includes('mountain') || lower.includes('hiking') || lower.includes('trail') || lower.includes('peak')) return 'Mountains';
            if (lower.includes('lake') || lower.includes('fall') || lower.includes('waterfall') || lower.includes('river')) return 'Lakes';
            if (lower.includes('farm') || lower.includes('agri') || lower.includes('vineyard') || lower.includes('plantation')) return 'Farm';
            if (lower.includes('nightlife') || lower.includes('bar') || lower.includes('club')) return 'Nightlife';
            if (lower.includes('adventure') || lower.includes('camping') || lower.includes('camp')) return 'Adventure';
            if (lower.includes('nature') || lower.includes('park') || lower.includes('garden')) return 'Nature';
            return raw;
        }

        // Match individual spot against selected category
        function matchesCategory(cardCategory, cardName, cardMuni, targetCat) {
            if (!targetCat || targetCat === 'All') return true;
            const c = (cardCategory || '').toLowerCase().trim();
            const n = (cardName || '').toLowerCase().trim();
            const m = (cardMuni || '').toLowerCase().trim();
            const combined = `${c} ${n} ${m}`;
            const t = targetCat.toLowerCase().trim();

            if (c && (c === t || c.includes(t) || t.includes(c))) return true;

            if (t === 'beach') {
                return combined.includes('beach') || combined.includes('surf') || combined.includes('coastal') || combined.includes('seascape') || combined.includes('island');
            }
            if (t === 'nature') {
                return combined.includes('nature') || combined.includes('park') || combined.includes('garden') || combined.includes('plaza') || combined.includes('mangrove') || combined.includes('baywalk');
            }
            if (t === 'heritage') {
                return combined.includes('heritage') || combined.includes('historic') || combined.includes('cultural') || combined.includes('landmark') || combined.includes('monument') || combined.includes('museum') || combined.includes('church') || combined.includes('shrine') || combined.includes('watchtower');
            }
            if (t === 'food & dining' || t === 'food') {
                return combined.includes('food') || combined.includes('dining') || combined.includes('restaurant') || combined.includes('cafe') || combined.includes('bistro') || combined.includes('eatery') || combined.includes('kitchen');
            }
            if (t === 'mountains' || t === 'mountain') {
                return combined.includes('mountain') || combined.includes('peak') || combined.includes('hiking') || combined.includes('trail') || combined.includes('view deck') || combined.includes('viewdeck') || combined.includes('mt.');
            }
            if (t === 'lakes' || t === 'lake') {
                return combined.includes('lake') || combined.includes('fall') || combined.includes('waterfall') || combined.includes('river') || combined.includes('dam') || combined.includes('spring');
            }
            if (t === 'farm') {
                return combined.includes('farm') || combined.includes('grapes') || combined.includes('plantation') || combined.includes('agri') || combined.includes('vineyard');
            }
            if (t === 'museum') {
                return combined.includes('museum') || combined.includes('gallery') || combined.includes('cultural') || combined.includes('pottery') || combined.includes('weaving');
            }
            if (t === 'religious') {
                return combined.includes('church') || combined.includes('shrine') || combined.includes('basilica') || combined.includes('cathedral') || combined.includes('chapel') || combined.includes('parish') || combined.includes('religious');
            }
            if (t === 'adventure') {
                return combined.includes('adventure') || combined.includes('camp') || combined.includes('glamping') || combined.includes('trek');
            }
            if (t === 'nightlife') {
                return combined.includes('nightlife') || combined.includes('bar') || combined.includes('pub') || combined.includes('club') || combined.includes('lounge');
            }

            return false;
        }

        // Switch active Category Tab
        window.switchCategoryTab = function (cat, btnEl) {
            const canonicalCat = canonicalizeCategory(cat);
            currentCategory = canonicalCat;
            localStorage.setItem('intan_elyu_filter_category', canonicalCat);

            // Update tab UI
            const tabs = document.querySelectorAll('#categories-tabs-list .category-tab-btn');
            let activeFound = false;
            tabs.forEach(tab => {
                const tabCat = tab.getAttribute('data-cat');
                if (tabCat === canonicalCat || (canonicalCat === 'All' && tabCat === 'All')) {
                    tab.classList.add('active');
                    activeFound = true;
                    try {
                        tab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    } catch (e) {}
                } else {
                    tab.classList.remove('active');
                }
            });

            if (!activeFound && btnEl) {
                btnEl.classList.add('active');
            }

            checkWeatherAdvisory(canonicalCat);
            applyFilters();
        };

        function checkWeatherAdvisory(cat) {
            const badgeEl = document.getElementById('category-weather-badge');
            const textEl = document.getElementById('category-weather-text');
            if (!badgeEl || !textEl) return;

            const wData = window._lastWeatherData || null;
            if (!wData || !wData.condition) {
                badgeEl.style.display = 'none';
                return;
            }

            const cond = (wData.condition || '').toLowerCase();
            const isRainy = cond.includes('rain') || cond.includes('drizzle') || cond.includes('thunder') || cond.includes('storm');
            const isSunny = cond.includes('sunny') || cond.includes('clear');

            if (isRainy && (cat === 'Museum' || cat === 'Heritage' || cat === 'Food & Dining' || cat === 'Religious')) {
                textEl.textContent = `Recommended for today's weather (${wData.condition}): Sheltered indoor & cultural spots`;
                badgeEl.style.display = 'flex';
                badgeEl.style.background = '#dbeafe';
                badgeEl.style.color = '#1d4ed8';
                badgeEl.style.borderColor = '#bfdbfe';
            } else if (isSunny && (cat === 'Beach' || cat === 'Nature' || cat === 'Mountains' || cat === 'Adventure')) {
                textEl.textContent = `Recommended for today's weather (${wData.condition}): Great skies for outdoor & coastal exploration`;
                badgeEl.style.display = 'flex';
                badgeEl.style.background = '#fef3c7';
                badgeEl.style.color = '#b45309';
                badgeEl.style.borderColor = '#fde68a';
            } else {
                badgeEl.style.display = 'none';
            }
        }

        window.setCategorySort = function (sortKey) {
            currentSort = sortKey;
            applyFilters();
        };

        window.clearCategorySearch = function () {
            const input = document.getElementById('categories-search-input');
            const clearBtn = document.getElementById('categories-search-clear');
            if (input) input.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            currentSearch = '';
            applyFilters();
        };

        function applyFilters() {
            const container = document.getElementById('categories-list-container');
            const countText = document.getElementById('categories-count-text');
            if (!container) return;

            if (!allDestinations || !allDestinations.length) {
                container.innerHTML = `
                    <div class="dash-empty-state" style="padding:40px 16px; text-align:center;">
                        <div class="dash-empty-icon-wrap" style="width:52px; height:52px; border-radius:50%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; margin:0 auto 12px auto; color:#64748b; font-size:20px;">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div style="font-size:15px; font-weight:800; color:#0f172a; margin-bottom:4px;">No Spots Available</div>
                        <div style="font-size:12px; color:#64748b; max-width:260px; margin:0 auto;">Unable to load tourist sites. Please check your connection.</div>
                    </div>
                `;
                if (countText) countText.textContent = 'No spots available';
                return;
            }

            let filtered = allDestinations.filter(dest => {
                if (!matchesCategory(dest.category, dest.name, dest.municipality || dest.location, currentCategory)) {
                    return false;
                }
                if (currentSearch.trim()) {
                    const q = currentSearch.trim().toLowerCase();
                    const n = (dest.name || '').toLowerCase();
                    const m = (dest.municipality || dest.location || '').toLowerCase();
                    const c = (dest.category || '').toLowerCase();
                    if (!n.includes(q) && !m.includes(q) && !c.includes(q)) return false;
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

            if (countText) {
                const label = currentCategory === 'All' ? 'All Spots' : currentCategory;
                countText.textContent = filtered.length === 1 
                    ? `Showing 1 spot in ${label}` 
                    : `Showing ${filtered.length} spots in ${label}`;
            }

            if (!filtered.length) {
                container.innerHTML = `
                    <div class="dash-empty-state" style="padding:40px 16px; text-align:center;">
                        <div class="dash-empty-icon-wrap" style="width:52px; height:52px; border-radius:50%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; margin:0 auto 12px auto; color:#64748b; font-size:20px;">
                            <i class="fa-solid fa-compass"></i>
                        </div>
                        <div style="font-size:15px; font-weight:800; color:#0f172a; margin-bottom:4px;">No Spots in this Category</div>
                        <div style="font-size:12px; color:#64748b; max-width:260px; margin:0 auto 14px auto;">No destinations match your search or filter under "${currentCategory}".</div>
                        <button type="button" onclick="window.switchCategoryTab('All')" style="background:#1e3a8a; color:#ffffff; border:none; border-radius:100px; padding:8px 18px; font-size:12px; font-weight:700; cursor:pointer;">
                            View All Spots
                        </button>
                    </div>
                `;
                return;
            }

            let html = '<div class="categories-grid">';
            filtered.forEach((dest, i) => {
                const img = window.getDestImage ? window.getDestImage(dest, 400) : (dest.image_url || 'assets/img/no_image.svg');
                const badgeColor = dest.classification_status === 'EXIST' ? '#0284c7' :
                    (dest.classification_status === 'EMERGE' ? '#ef4444' : '#10b981');
                const badgeLabel = dest.classification_status === 'EXIST' ? 'EXISTING' :
                    (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL');
                const muni = dest.municipality || dest.location || 'La Union';
                const fee = (dest.entrance_fee && parseFloat(dest.entrance_fee) > 0) ? `₱${parseFloat(dest.entrance_fee).toFixed(0)}` : 'Free';
                const cat = dest.category || 'Spot';

                const numRating = (dest.rating !== undefined && dest.rating !== null && !isNaN(parseFloat(dest.rating)))
                    ? parseFloat(dest.rating)
                    : (dest.reviews_avg_rating ? parseFloat(dest.reviews_avg_rating) : 0);

                const ratingHtml = numRating > 0 ? `
                    <span style="background:rgba(245,158,11,0.25); color:#fbbf24; border:1px solid rgba(245,158,11,0.45); padding:1.5px 6px; border-radius:100px; font-size:9.5px; font-weight:800; display:inline-flex; align-items:center; gap:3px;">
                        <i class="fa-solid fa-star" style="font-size:8.5px; color:#f59e0b;"></i> ${numRating.toFixed(1)}
                    </span>
                ` : '';

                html += `
                    <div class="categories-spot-card" onclick="window.viewCategorySpot(${dest.id}, '${(dest.name || '').replace(/'/g, "\\'")}', '${encodeURIComponent(JSON.stringify(dest)).replace(/'/g, '%27')}')">
                        ${dest.classification_status ? `<div class="badge" style="background:${badgeColor};">${badgeLabel}</div>` : ''}
                        <div class="top-cat-icon"><i class="fa-solid fa-compass"></i></div>
                        <img src="${img}" alt="" loading="lazy" decoding="async" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\\'")}', '${(dest.municipality || '').replace(/'/g, "\\'")}'); else this.src='assets/img/no_image.svg';">
                        <div class="overlay">
                            <div class="name">${dest.name}</div>
                            <div class="meta-muni"><i class="fa-solid fa-location-dot" style="font-size:8px; margin-right:3px;"></i>${muni}</div>
                            <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap; margin-top:4px;">
                                ${ratingHtml}
                                <span class="card-cat-pill">${cat}</span>
                                <span class="card-fee">${fee}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        window.viewCategorySpot = function (id, name, encodedDest) {
            try {
                const dest = (typeof encodedDest === 'object' && encodedDest !== null) 
                    ? encodedDest 
                    : (encodedDest ? JSON.parse(decodeURIComponent(encodedDest)) : (allDestinations.find(s => Number(s.id) === Number(id))));
                if (!dest) return;
                localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
                if (typeof window.navigateTo === 'function') {
                    window.navigateTo('map');
                } else {
                    window.location.href = '?view=map';
                }
            } catch (e) {
                console.error('Failed to view spot:', e);
            }
        };

        // Live search event handling
        const searchInput = document.getElementById('categories-search-input');
        const searchClearBtn = document.getElementById('categories-search-clear');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                currentSearch = e.target.value;
                if (searchClearBtn) searchClearBtn.style.display = currentSearch.length > 0 ? 'flex' : 'none';
                applyFilters();
            });
        }

        async function fetchCategoryData(forceRefresh = false) {
            const mapCacheKey = 'public_map_data';
            const loadFn = window.useCache || (async (k, fn, cb) => { const d = await fn(); cb(d); return d; });
            
            await loadFn(
                mapCacheKey,
                async () => {
                    const res = await fetch(backendUrl + '/api/public/map');
                    if (!res.ok) throw new Error("Failed to fetch destinations");
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

            // Ambient offline resilience
            if (!allDestinations || !allDestinations.length) {
                try {
                    const fallbackSpotsRaw = localStorage.getItem('intan_elyu_cached_spots_map');
                    if (fallbackSpotsRaw) {
                        const parsed = JSON.parse(fallbackSpotsRaw);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            allDestinations = parsed;
                        }
                    }
                } catch (e) {}
            }

            // Sync initial category tab from localStorage or query param
            const savedCat = localStorage.getItem('intan_elyu_filter_category');
            if (savedCat) {
                window.switchCategoryTab(savedCat);
            } else {
                applyFilters();
            }
        }

        // Offline Pill sync
        function updateOfflineStatus() {
            const pill = document.getElementById('categories-offline-pill');
            const isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
            if (pill) pill.style.display = isOffline ? 'inline-flex' : 'none';
        }
        window.addEventListener('online', updateOfflineStatus);
        window.addEventListener('offline', updateOfflineStatus);
        setTimeout(updateOfflineStatus, 100);

        fetchCategoryData();
    })();
</script>

