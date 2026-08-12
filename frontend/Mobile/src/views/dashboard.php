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

<link rel="stylesheet" href="assets/css/views/dashboard.css?v=<?= time() ?>">
<style>
@keyframes filterFadeIn {
    0% { opacity: 0; transform: translateY(10px) scale(0.95); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}
.categories-container {
    display: flex !important;
    flex-direction: row !important;
    gap: 12px !important;
    overflow-x: auto !important;
    padding: 6px 4px 14px 4px !important;
    scroll-snap-type: x mandatory !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
    box-sizing: border-box !important;
    width: 100% !important;
}
.categories-container::-webkit-scrollbar { display: none !important; }
.category-card {
    flex: 0 0 98px !important;
    width: 98px !important;
    height: 102px !important;
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border-radius: 22px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 10px 6px !important;
    box-sizing: border-box !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
    cursor: pointer !important;
    user-select: none !important;
    border: 1px solid rgba(56, 189, 248, 0.2) !important;
}
.category-card.active {
    background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%) !important;
    border-color: #38bdf8 !important;
}
.category-icon-box {
    width: 44px !important;
    height: 44px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 22px !important;
    color: #38bdf8 !important;
    margin-bottom: 6px !important;
}
.category-card.active .category-icon-box { color: #ffffff !important; }
.category-card-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #f8fafc !important;
    text-align: center !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    width: 100% !important;
}
.category-card.active .category-card-label { color: #ffffff !important; }
</style>

<?php include __DIR__ . '/../components/header.php'; ?>




<div class="dashboard-container has-header has-bottom-nav animate-slide-up">

    <!-- Profile + EXP Card -->
    <div class="profile-header stagger-1" onclick="navigateTo('profile')">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
            <div class="profile-info-row" style="margin-bottom:0; flex:1;">
                <div class="profile-avatar">
                    <img id="dash-avatar" src="https://ui-avatars.com/api/?name=Tourist&amp;background=007AFF&amp;color=fff&amp;rounded=true&amp;bold=true&amp;size=128" alt="Avatar">
                </div>
                <div class="profile-text">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <h2 class="profile-name" id="dash-name" style="margin:0;">Hi, there! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i></h2>
                        <span id="dash-explorer-id" style="font-size:10px; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.12); padding:2px 8px; border-radius:100px; border:1px solid rgba(56,189,248,0.25);">ID: #--</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; margin-top:4px; flex-wrap:wrap;">
                        <span class="profile-title" id="dash-title"><i class="fa-solid fa-compass" style="color:#38bdf8; font-size:10px;"></i> Explorer of Elyu</span>
                        <span id="dash-status-badge" style="font-size:10px; font-weight:800; color:#34c759; background:rgba(52,199,89,0.12); padding:2px 8px; border-radius:100px; border:1px solid rgba(52,199,89,0.25);"><i class="fa-solid fa-circle" style="font-size:6px; margin-right:4px;"></i> Active Tourist</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Level XP Progress Section -->
        <div class="exp-container">
            <div class="exp-header">
                <span class="exp-label" id="dash-level-label"><i class="fa-solid fa-award" style="color:#38bdf8; margin-right:4px;"></i> Level Progress</span>
                <span class="exp-value" id="dash-xp-value"><i class="fa-solid fa-bolt" style="color:#fbbf24; margin-right:4px;"></i>— XP</span>
            </div>
            <div class="exp-bar-bg"><div class="exp-bar-fill" id="dash-xp-bar" style="width:0%;"></div></div>
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; color:rgba(148,163,184,0.8); font-weight:600; margin-top:2px;">
                <span id="dash-xp-needed">1,000 XP to next level</span>
                <span id="dash-xp-pct" style="color:#38bdf8; font-weight:800;">0%</span>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row stagger-1">
        <div class="stat-card" onclick="navigateTo('itinerary')">
            <div class="stat-icon"><i class="fa-solid fa-map-location-dot" style="color:#38bdf8;"></i></div>
            <div class="stat-value" id="dash-stat-places">—</div>
            <div class="stat-label">Places</div>
        </div>
        <div class="stat-card" onclick="navigateTo('leaderboard')">
            <div class="stat-icon"><i class="fa-solid fa-bolt" style="color:#fbbf24;"></i></div>
            <div class="stat-value" id="dash-stat-xp">—</div>
            <div class="stat-label">XP</div>
        </div>
        <div class="stat-card" onclick="navigateTo('leaderboard')">
            <div class="stat-icon"><i class="fa-solid fa-trophy" style="color:#f59e0b;"></i></div>
            <div class="stat-value" id="dash-stat-rank">—</div>
            <div class="stat-label">Rank</div>
        </div>
    </div>

    <!-- Weather Widget -->
    <div class="weather-card stagger-2" onclick="window.openWeatherModal()" style="cursor: pointer; position: relative;" title="Click for 5-Day Weather Forecast">
        <div class="weather-left">
            <div class="weather-temp" id="weather-temp"><i class="fa-solid fa-spinner fa-spin" style="font-size:22px; color:rgba(255,255,255,0.4);"></i></div>
            <div class="weather-desc" id="weather-desc">Loading Weather...</div>
            <div class="weather-loc" id="weather-loc"><i class="fa-solid fa-location-dot" style="color:#38bdf8; margin-right:4px;"></i> San Fernando, La Union</div>
            <div class="weather-details">
                <span class="weather-detail"><i class="fa-solid fa-droplet" style="color:#38bdf8;"></i> <span id="weather-humidity">--%</span></span>
                <span class="weather-detail"><i class="fa-solid fa-wind" style="color:#a78bfa;"></i> <span id="weather-wind">-- km/h</span></span>
                <span class="weather-detail"><i class="fa-solid fa-sun" style="color:#fbbf24;"></i> UV <span id="weather-uv">--</span></span>
            </div>
        </div>
        <div style="text-align: right;">
            <div class="weather-icon" id="weather-icon">⛅</div>
            <span style="font-size: 10px; color: rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: flex-end; gap: 4px; margin-top: 4px; font-weight: 600;">
                5-Day Forecast <i class="fa-solid fa-chevron-right" style="font-size: 8px; color: #38bdf8;"></i>
            </span>
        </div>
    </div>

    <!-- Weather Forecast Modal -->
    <div id="weather-modal" onclick="if(event.target===this) window.closeWeatherModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); backdrop-filter:blur(10px); z-index:99999; justify-content:center; align-items:flex-end;">
        <div style="background:#0f172a; border-top:1px solid rgba(255,255,255,0.15); border-radius:28px 28px 0 0; width:100%; max-width:500px; max-height:85vh; overflow-y:auto; padding:24px 20px; box-shadow:0 -10px 40px rgba(0,0,0,0.6); animation:slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div>
                    <h3 style="margin:0; font-size:18px; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px;">
                        <span>🌤️ Weather Forecast</span>
                    </h3>
                    <p style="margin:2px 0 0 0; font-size:12px; color:rgba(255,255,255,0.6);" id="modal-weather-loc">San Fernando, La Union</p>
                </div>
                <button onclick="window.closeWeatherModal()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Current Detailed Weather Card -->
            <div style="background:linear-gradient(135deg, rgba(56,189,248,0.2) 0%, rgba(99,102,241,0.2) 100%); border:1px solid rgba(56,189,248,0.3); border-radius:22px; padding:18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <div style="font-size:38px; font-weight:900; color:#fff; letter-spacing:-1px;" id="modal-temp">29°C</div>
                    <div style="font-size:14px; font-weight:800; color:#38bdf8; margin-top:2px;" id="modal-condition">Partly Cloudy</div>
                    <div style="font-size:12px; color:rgba(255,255,255,0.7); margin-top:6px;" id="modal-feels">Feels like 31°C</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:52px;" id="modal-icon">⛅</div>
                </div>
            </div>

            <!-- 5-Day Forecast Grid -->
            <h4 style="margin:0 0 12px 0; font-size:14px; font-weight:800; color:#f8fafc; display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-calendar-days" style="color:#38bdf8;"></i> 5-Day Forecast
            </h4>
            <div id="weather-forecast-container" style="display:flex; flex-direction:column; gap:10px;">
                <div style="padding:20px; text-align:center; color:rgba(255,255,255,0.5); font-size:13px;">
                    <i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Loading 5-day forecast...
                </div>
            </div>
        </div>
    </div>

    <!-- Gamification Panel -->
    <div class="weather-card stagger-2" onclick="navigateTo('puzzles')" style="background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%); border: 1px solid rgba(139, 92, 246, 0.3); box-shadow: 0 8px 32px rgba(139, 92, 246, 0.15); margin-top: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; padding: 16px 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 32px; filter: drop-shadow(0 0 10px rgba(167, 139, 250, 0.6));">🧩</div>
            <div>
                <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -0.2px;">GameZone</h4>
                <p style="margin: 0; font-size: 12px; color: #e9d5ff; font-weight: 600;">Play fun mini-games to earn discount vouchers! 🎁</p>
            </div>
        </div>
        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 14px;"><i class="fa-solid fa-play"></i></div>
    </div>

    <!-- Categories Section -->
    <div class="dash-section stagger-2" style="margin-top: 20px;">
        <div class="section-title">
            <h3>Categories</h3>
            <a href="javascript:void(0);" onclick="navigateTo('map')">Explore Map</a>
        </div>
        <div class="categories-container" id="dash-categories-list">
            <div class="category-card active" onclick="window.filterCategoryDash('All', this)">
                <div class="category-icon-box"><i class="fa-solid fa-compass"></i></div>
                <div class="category-card-label">All Spots</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Beach', this)">
                <div class="category-icon-box"><i class="fa-solid fa-umbrella-beach"></i></div>
                <div class="category-card-label">Beach</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Mountains', this)">
                <div class="category-icon-box"><i class="fa-solid fa-mountain-sun"></i></div>
                <div class="category-card-label">Mountains</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Lakes', this)">
                <div class="category-icon-box"><i class="fa-solid fa-water"></i></div>
                <div class="category-card-label">Lakes & Falls</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Heritage', this)">
                <div class="category-icon-box"><i class="fa-solid fa-landmark"></i></div>
                <div class="category-card-label">Heritage</div>
            </div>
            <div class="category-card" onclick="window.filterCategoryDash('Food & Dining', this)">
                <div class="category-icon-box"><i class="fa-solid fa-utensils"></i></div>
                <div class="category-card-label">Food</div>
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
            <a href="javascript:void(0);" onclick="navigateTo('trending')">See All</a>
        </div>
        <div class="favorites-row" id="trending-container">
            <div style="padding: 20px; width: 100%; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading trending sites...
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
            <div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px; background: rgba(255,255,255,0.02); border-radius: 15px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading Saved Trips...
            </div>
        </div>
    </div>
    
    <!-- Favorites / Saved Places -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>Saved Places</h3>
            <a href="javascript:void(0);" onclick="navigateTo('saved_places')">See All</a>
        </div>
        <div class="favorites-row" id="saved-places-container">
            <div style="padding: 20px; width: 100%; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading Saved Places...
            </div>
        </div>  
    </div>

    <!-- Recommended For You -->
    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3>Recommended For You</h3>
        </div>
        <div id="recommended-container">
            <div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading Recommendations...
            </div>
        </div>
    </div>

    <!-- Near Me Spots -->
    <div class="dash-section stagger-2">
        <div class="section-title">
            <h3>Near Me</h3>
            <a href="javascript:void(0);" onclick="navigateTo('map')">See Map</a>
        </div>
        <div class="favorites-row" id="near-me-container">
            <div style="padding: 20px; width: 100%; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Finding spots near you...
            </div>
        </div>
    </div>
</div>

<script>
    window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;
    window.filterCategoryDash = function(cat, el) {
        document.querySelectorAll('#dash-categories-list .category-card').forEach(card => card.classList.remove('active'));
        if (el) el.classList.add('active');

        const matchesCategory = (cardCategory, cardName, targetCat) => {
            if (targetCat === 'All') return true;
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

        const filterContainer = (containerId, emptyMsg) => {
            const container = document.getElementById(containerId);
            if (!container) return;

            const oldEmpty = container.querySelector('.dash-filter-empty-state');
            if (oldEmpty) oldEmpty.remove();

            const children = Array.from(container.children).filter(child => !child.classList.contains('dash-filter-empty-state'));
            let visibleCount = 0;

            for (let i = 0; i < children.length; i++) {
                const child = children[i];
                if (child.innerText && (child.innerText.includes('Loading') || child.innerText.includes('Enable location') || child.innerText.includes('Go to the map'))) {
                    continue;
                }

                if (child.id === 'btn-view-more-rec' || child.id === 'rec-extras') {
                    if (child.id === 'rec-extras') {
                        Array.from(child.children).forEach(subChild => {
                            const subCat = subChild.getAttribute('data-category') || '';
                            const subName = subChild.querySelector('.fav-card-name, h4')?.innerText || '';
                            if (matchesCategory(subCat, subName, cat)) {
                                subChild.style.display = '';
                                visibleCount++;
                            } else {
                                subChild.style.display = 'none';
                            }
                        });
                    }
                    continue;
                }

                const cardCat = child.getAttribute('data-category') || '';
                const cardName = child.querySelector('.fav-card-name, h4')?.innerText || '';

                if (matchesCategory(cardCat, cardName, cat)) {
                    child.style.display = '';
                    child.style.animation = 'none';
                    void child.offsetWidth;
                    child.style.animation = 'filterFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                    visibleCount++;
                } else {
                    child.style.display = 'none';
                }
            }

            if (cat !== 'All' && visibleCount === 0) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'dash-filter-empty-state';
                emptyDiv.style.cssText = 'padding: 24px 16px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: rgba(15,23,42,0.6); border: 1px solid rgba(56,189,248,0.15); border-radius: 20px; margin: 4px 0; font-size: 13px; color: rgba(148,163,184,0.9); font-weight: 600; box-sizing: border-box; animation: filterFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;';
                emptyDiv.innerHTML = `<i class="fa-solid fa-compass" style="font-size: 24px; color: #38bdf8; margin-bottom: 2px;"></i><span>${emptyMsg}</span>`;
                container.appendChild(emptyDiv);
            }
        };

        filterContainer('trending-container', 'No trending sites in this category.');
        filterContainer('recommended-container', 'No recommended sites in this category.');
        filterContainer('near-me-container', 'No nearby sites found in this category.');
    };

    window.initLoopingFocusCarousel = function(containerId) {
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
                const scale = 0.84 + (ratio * 0.22);
                const opacity = 0.60 + (ratio * 0.40);

                card.style.transform = `scale(${scale.toFixed(3)}) translate3d(0,0,0)`;
                card.style.opacity = opacity.toFixed(2);
            });

            cards.forEach(c => c.classList.remove('active-card'));
            if (closestCard) {
                closestCard.classList.add('active-card');
            }

            // Infinite loop scroll wrapping
            const maxScroll = container.scrollWidth - container.clientWidth;
            if (maxScroll > 100) {
                if (container.scrollLeft <= 5) {
                    container.scrollLeft = Math.floor(maxScroll * 0.5);
                } else if (container.scrollLeft >= maxScroll - 5) {
                    container.scrollLeft = Math.floor(maxScroll * 0.25);
                }
            }

            isTicking = false;
        }

        if (!container._focusCarouselInited) {
            container._focusCarouselInited = true;
            container.addEventListener('scroll', () => {
                if (!isTicking) {
                    requestAnimationFrame(updateCardScales);
                    isTicking = true;
                }
            }, { passive: true });
        }

        requestAnimationFrame(() => {
            updateCardScales();
            setTimeout(updateCardScales, 150);
            setTimeout(updateCardScales, 500);
        });
    };

(async function dashboardInit() {
    const setTxt = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
    const setHtml = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML = html; };
    const setSrc = (id, src) => { const el = document.getElementById(id); if (el) el.src = src; };

    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
    const token = localStorage.getItem('intan_elyu_token');
    const user = window.safeJsonParse ? window.safeJsonParse(localStorage.getItem('auth_user'), {}) : {};

    // Instant render from cache
    if (user && user.name) {
        setHtml('dash-name', 'Hi, ' + user.name.split(' ')[0] + '! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i>');
        setSrc('dash-avatar', user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);
    }

    if (!token) return;

    let lat = null, lng = null;
    try {
        if ("geolocation" in navigator) {
            const pos = await new Promise((res, rej) => {
                // Use cached location (maximumAge: 1 hour) and give it 5s instead of 3s
                navigator.geolocation.getCurrentPosition(res, rej, { enableHighAccuracy: false, timeout: 5000, maximumAge: 3600000 });
            });
            if (pos && pos.coords) {
                lat = pos.coords.latitude;
                lng = pos.coords.longitude;
            }
        }
    } catch(e) {
        // Suppress timeout error (code 3) as it is a safe fallback scenario
        if (e && e.code !== 3) {
            console.log("Location access issue (handled):", e.message);
        }
    }

    let apiUrl = backendUrl + '/api/tourist/dashboard';
    if (lat && lng) apiUrl += `?lat=${lat}&lng=${lng}`;

    const cacheKey = 'dashboard_data_' + (lat && lng ? `${lat.toFixed(3)}_${lng.toFixed(3)}` : 'default');

    function renderDashboard(data) {
        // Update notification badge
        if (data.stats && typeof window.updateUnreadBadge === 'function') {
            window.updateUnreadBadge(data.stats.unread_notifications || 0);
        }

        const u = data.user || {};

        // Profile header
        const firstName = (u.name || 'Explorer').split(' ')[0];
        setHtml('dash-name', 'Hi, ' + firstName + '! <i class="fa-solid fa-hand" style="color:#fbbf24; font-size:18px; margin-left:4px;"></i>');
        setTxt('dash-title', 'Level ' + (u.level || 1) + ' Explorer');
        
        const userId = u.id || u.user_id || '';
        if (userId) setTxt('dash-explorer-id', 'ID: #' + userId);
        
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
                dashAvatarEl.onerror = function() {
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

        // XP Bar
        const xp = parseInt(u.xp) || 0;
        const level = parseInt(u.level) || 1;
        const xpPerLevel = 1000;
        const xpInLevel = xp % xpPerLevel;
        const xpPct = Math.min((xpInLevel / xpPerLevel) * 100, 100);
        const xpNeeded = xpPerLevel - xpInLevel;

        const lvlLabel = document.getElementById('dash-level-label');
        if (lvlLabel) lvlLabel.innerHTML = `<i class="fa-solid fa-award" style="color:#38bdf8; margin-right:4px;"></i> Level ${level} Progress`;
        
        const xpVal = document.getElementById('dash-xp-value');
        if (xpVal) xpVal.innerHTML = `<i class="fa-solid fa-bolt" style="color:#fbbf24; margin-right:4px;"></i>${xpInLevel.toLocaleString()} / ${xpPerLevel.toLocaleString()} XP`;
        
        const xpNeedEl = document.getElementById('dash-xp-needed');
        if (xpNeedEl) xpNeedEl.textContent = `${xpNeeded.toLocaleString()} XP to Level ${level + 1}`;
        
        const xpPctEl = document.getElementById('dash-xp-pct');
        if (xpPctEl) xpPctEl.textContent = `${Math.round(xpPct)}%`;

        if (document.getElementById('dash-xp-bar')) document.getElementById('dash-xp-bar').style.width = xpPct + '%';

        // Stats
        if (document.getElementById('dash-stat-places')) document.getElementById('dash-stat-places').textContent = (data.stats && data.stats.placesVisited) ? data.stats.placesVisited : 0;
        if (document.getElementById('dash-stat-xp')) document.getElementById('dash-stat-xp').textContent = xp.toLocaleString();

        // Populate Trending Spots (Top 3 with Infinite Carousel Loop)
        const trendingContainer = document.getElementById('trending-container');
        if (trendingContainer) {
            trendingContainer.innerHTML = '';
            if (data.trending && data.trending.length > 0) {
                const trendingList = (data.trending.length > 1) 
                    ? [...data.trending, ...data.trending, ...data.trending] 
                    : data.trending;
                trendingList.forEach(dest => {
                    const img = window.getDestImage(dest, 600);
                    const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                    const encodedDest = encodeURIComponent(JSON.stringify(dest));
                    trendingContainer.innerHTML += `
                        <div class="fav-card" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                            ${badgeHtml}
                            <img src="${img}" alt="${dest.name}" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\\'")}', '${(dest.municipality || '').replace(/'/g, "\\'")}'); else this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                            <div class="fav-card-overlay"><span class="fav-card-name">${dest.name}</span></div>
                            <i class="fa-solid fa-fire fav-heart" style="color: #ff9500; font-size: 14px;"></i>
                        </div>
                    `;
                });
                window.initLoopingFocusCarousel('trending-container');
            } else {
                trendingContainer.innerHTML = `
                    <div style="padding: 28px 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; margin: 0 16px;">
                        <i class="fa-solid fa-fire-flame-curved" style="font-size: 32px; color: rgba(56,189,248,0.4);"></i>
                        <div style="color: rgba(148,163,184,0.8); font-size: 14px; line-height: 1.4;">No trending spots right now.</div>
                    </div>
                `;
            }
        }

        // Populate Saved Places
        const savedContainer = document.getElementById('saved-places-container');
        if (savedContainer) {
            savedContainer.innerHTML = '';
            if (data.savedPlaces && data.savedPlaces.length > 0) {
                const savedList = (data.savedPlaces.length > 1) 
                    ? [...data.savedPlaces, ...data.savedPlaces, ...data.savedPlaces] 
                    : data.savedPlaces;
                savedList.forEach(dest => {
                    const img = window.getDestImage(dest, 600);
                    const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                    const encodedDest = encodeURIComponent(JSON.stringify(dest));
                    savedContainer.innerHTML += `
                        <div class="fav-card" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                            ${badgeHtml}
                            <img src="${img}" alt="${dest.name}" onerror="if (window.handleImgError) window.handleImgError(this, '${(dest.name || '').replace(/'/g, "\\'")}', '${(dest.municipality || '').replace(/'/g, "\\'")}'); else this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                            <div class="fav-card-overlay"><span class="fav-card-name">${dest.name}</span></div>
                            <i class="fa-solid fa-heart fav-heart" style="color: #ff3b30;" onclick="event.stopPropagation(); window.toggleFavorite(${dest.id}, this)"></i>
                        </div>
                    `;
                });
                window.initLoopingFocusCarousel('saved-places-container');
            } else {
                savedContainer.innerHTML = `
                    <div style="padding: 28px 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; margin: 0 16px;">
                        <i class="fa-solid fa-map-location-dot" style="font-size: 32px; color: rgba(56,189,248,0.4);"></i>
                        <div style="color: rgba(148,163,184,0.8); font-size: 14px; line-height: 1.4;">Go to the map to save some places!</div>
                        <button onclick="window.location.href='?view=map'" style="background: linear-gradient(135deg, #38bdf8, #2563eb); color: white; border: none; padding: 11px 22px; border-radius: 100px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(56,189,248,0.3);">
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
                        style="width:100%; margin-top:10px; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.8); font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition: background 0.2s;">
                        <i class="fa-solid fa-chevron-down" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i>
                        View ${extras.length} More
                    </button>
                `;
            }
        }

        // Cache updated user
        localStorage.setItem('auth_user', JSON.stringify(u));
    }

    // Helper functions used inside render/build
    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radius of the earth in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    async function loadNearMe(userLat, userLng) {
        const nearContainer = document.getElementById('near-me-container');
        if (!nearContainer) return;

        if (!userLat || !userLng) {
            nearContainer.innerHTML = `
                <div style="padding: 28px 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; margin: 0 16px;">
                    <i class="fa-solid fa-location-crosshairs" style="font-size: 32px; color: rgba(56,189,248,0.4);"></i>
                    <div style="color: rgba(148,163,184,0.8); font-size: 14px; line-height: 1.4;">Enable location access to see spots near you.</div>
                </div>
            `;
            return;
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
                if (!data) {
                    nearContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">Error loading nearby spots.</div>';
                    return;
                }
                let spots = data.destinations || [];
                spots.forEach(spot => {
                    var sLat = spot.latitude || spot.lat;
                    var sLng = spot.longitude || spot.lng;
                    if (sLat && sLng) {
                        spot.distance = getDistance(userLat, userLng, sLat, sLng);
                    } else {
                        spot.distance = 999999;
                    }
                });

                spots.sort((a, b) => a.distance - b.distance);
                const nearSpots = spots.filter(s => s.distance < 2);

                if (nearSpots.length > 0) {
                    nearContainer.innerHTML = '';
                    const nearList = (nearSpots.length > 1) 
                        ? [...nearSpots.slice(0, 5), ...nearSpots.slice(0, 5), ...nearSpots.slice(0, 5)] 
                        : nearSpots.slice(0, 5);
                    nearList.forEach(dest => {
                        const img = window.getDestImage(dest, 600);
                        const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                        const distText = dest.distance < 1 ? '< 1 km' : dest.distance.toFixed(1) + ' km';
                        const encodedDest = encodeURIComponent(JSON.stringify(dest));
                        nearContainer.innerHTML += `
                            <div class="fav-card" data-category="${(dest.category || '').replace(/"/g, '&quot;')}" onclick="window.viewDestinationOnMap('${encodedDest}')">
                                ${badgeHtml}
                                <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                                <div class="fav-card-overlay">
                                    <span class="fav-card-name">${dest.name}</span>
                                    <span style="display:block; font-size:10px; color:#38bdf8; margin-top:2px; font-weight:700;"><i class="fa-solid fa-location-arrow"></i> ${distText} away</span>
                                </div>
                            </div>
                        `;
                    });
                    window.initLoopingFocusCarousel('near-me-container');
                } else {
                    nearContainer.innerHTML = `
                        <div style="padding: 28px 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; margin: 0 16px;">
                            <i class="fa-solid fa-location-dot" style="font-size: 32px; color: rgba(56,189,248,0.4);"></i>
                            <div style="color: rgba(148,163,184,0.8); font-size: 14px; line-height: 1.4;">There are no spots near you right now.</div>
                        </div>
                    `;
                }
            },
            false,
            300000 // 5 minutes TTL
        );
    }

    function buildRecommendedItem(dest) {
        const img = window.getDestImage(dest, 300);
        const rating = dest.rating ? parseFloat(dest.rating).toFixed(1) : (dest.reviews_avg_rating ? parseFloat(dest.reviews_avg_rating).toFixed(1) : 'New');
        const desc = dest.description ? dest.description.substring(0, 150) + (dest.description.length > 150 ? '...' : '') : 'A beautiful destination waiting to be explored.';
        
                    const encodedDest = encodeURIComponent(JSON.stringify(dest));
                    return `
            <div data-category="${(dest.category || '').replace(/"/g, '&quot;')}" style="margin-bottom: 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 18px; overflow: hidden; transition: all 0.3s ease;">
                <div onclick="const content = this.nextElementSibling; const icon = this.querySelector('.toggle-icon'); if(content.style.maxHeight === '0px' || !content.style.maxHeight){ content.style.paddingTop = '14px'; content.style.paddingBottom = '14px'; content.style.maxHeight = (content.scrollHeight + 150) + 'px'; content.style.opacity = '1'; icon.style.transform = 'rotate(90deg)'; } else { content.style.maxHeight = '0px'; content.style.opacity = '0'; content.style.paddingTop = '0'; content.style.paddingBottom = '0'; icon.style.transform = 'rotate(0deg)'; }" style="cursor:pointer; display:flex; align-items:center; gap: 12px; padding: 12px; transition: background 0.15s;" onpointerdown="this.style.background='rgba(255,255,255,0.05)'" onpointerup="this.style.background=''" onpointercancel="this.style.background=''">
                    <img src="${img}" alt="${dest.name}" style="width:60px; height:60px; border-radius:12px; object-fit:cover;" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=150';">
                    <div style="flex:1; min-width:0;">
                        <h4 style="margin:0 0 5px; font-size:15px; font-weight:800; letter-spacing:-0.3px; color:#f8fafc; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${dest.name}</h4>
                        <p style="margin:0 0 8px; font-size:12px; color:rgba(148,163,184,0.8);"><i class="fa-solid fa-location-dot" style="margin-right:4px; color:#38bdf8;"></i>${dest.location || dest.municipality_id || 'La Union'}</p>
                        <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                            <i class="fa-solid fa-star" style="color:#fbbf24; font-size:11px;"></i>
                            <span style="font-size:12px; font-weight:700; color:#f8fafc; margin-right:4px;">${rating}</span>
                            ${dest.classification_status ? `<span style="padding: 2px 6px; border-radius: 6px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')};">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</span>` : ''}
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right toggle-icon" style="color:rgba(148,163,184,0.4); font-size:13px; padding:4px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></i>
                </div>
                
                <div style="max-height: 0px; opacity: 0; padding: 0 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.03);">
                    <div style="font-size:12px; color:rgba(255,255,255,0.7); line-height:1.5; margin-bottom:12px;">
                        ${desc}
                    </div>
                    <div style="display:flex; gap:8px; font-size:11px; margin-bottom:12px; flex-wrap:wrap;">
                        ${dest.category ? `<span style="background:rgba(255,255,255,0.1); color:#fff; padding:4px 8px; border-radius:100px;">${dest.category}</span>` : ''}
                        ${dest.entrance_fee ? `<span style="background:rgba(56,189,248,0.1); color:#38bdf8; padding:4px 8px; border-radius:100px;">₱${dest.entrance_fee}</span>` : '<span style="background:rgba(52,199,89,0.1); color:#34c759; padding:4px 8px; border-radius:100px;">Free</span>'}
                    </div>
                    <button onclick="window.viewDestinationOnMap('${encodedDest}')" style="width:100%; margin-top:4px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:white; padding:10px; border-radius:12px; font-weight:700; font-size:13px; cursor:pointer; box-shadow:0 4px 14px rgba(56,189,248,0.3); display:flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fa-solid fa-map-location-dot"></i> View Details on Map
                    </button>
                </div>
            </div>
        `;
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
                        localStorage.removeItem('intan_elyu_token');
                        localStorage.removeItem('auth_user');
                        window.location.href = '?view=auth';
                    }
                    throw new Error("Dashboard fetch failed");
                }
                return await res.json();
            },
            (data) => {
                if (data) {
                    renderDashboard(data);
                }
            },
            Boolean(window.dashboardNeedsRefresh),
            30000 // 30 seconds TTL for dashboard
        );
        window.dashboardNeedsRefresh = false;

        loadNearMe(lat, lng);
        if (typeof window.fetchWeather === 'function') {
            if (lat && lng) {
                window.fetchWeather(lat, lng, 'My Location', true);
            } else {
                window.fetchWeather(16.6159, 120.3209, 'San Fernando, La Union', false);
            }
        }

        // Fetch Rank and Cache it
        const rankCacheKey = 'dashboard_rank_' + token.substring(0, 10);
        await window.useCache(
            rankCacheKey,
            async () => {
                const rankRes = await fetch(backendUrl + '/api/tourist/leaderboard', {
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token, 'ngrok-skip-browser-warning': 'true' }
                });
                if (rankRes.ok) return await rankRes.json();
                throw new Error("Rank fetch failed");
            },
            (rankData) => {
                if (rankData && rankData.myRank) {
                    const el = document.getElementById('dash-stat-rank');
                    if (el) el.textContent = '#' + rankData.myRank;
                } else {
                    const el = document.getElementById('dash-stat-rank');
                    if (el) el.textContent = 'Unranked';
                }
            },
            false,
            60000 // 1 minute TTL for rank
        );

        // Fetch Saved Trips and Cache it
        const tripsContainer = document.getElementById('saved-trips-container');
        if (tripsContainer) {
            const tripsCacheKey = 'dashboard_trips_' + token.substring(0, 10);
            await window.useCache(
                tripsCacheKey,
                async () => {
                    const itinRes = await fetch(backendUrl + '/api/tourist/itineraries', {
                        headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
                    });
                    if (itinRes.ok) return await itinRes.json();
                    throw new Error("Trips fetch failed");
                },
                (itinData) => {
                    if (!itinData) return;
                    const allItineraries = itinData.itineraries || [];
                    const itineraries = allItineraries.filter(t => t.status !== 'completed');
                    if (itineraries.length > 0) {
                        let tripsHtml = '';
                        // Show up to 3 most recent trips
                        itineraries.slice(0, 3).forEach(trip => {
                            let destinationsHtml = '';
                            if (trip.items && trip.items.length > 0) {
                                trip.items.forEach((item, index) => {
                                    const destName = item.destination ? item.destination.name : 'Unknown Destination';
                                    let proofImgHtml = '';
                                    if (item.proof_image) {
                                        let pUrl = item.proof_image;
                                        if (!pUrl.startsWith('http') && !pUrl.startsWith('data:') && !pUrl.startsWith('blob:')) {
                                            let b = (window.backendUrl || '').replace(/\/+$/, '');
                                            pUrl = b + '/' + pUrl.replace(/^\//, '');
                                        }
                                        let fallbackUrl = (window.backendUrl || '').replace(/\/+$/, '') + '/api/image/' + item.proof_image.replace(/^\//, '');
                                        proofImgHtml = `<img src="${pUrl}" onerror="if(this.src!=='${fallbackUrl}'){this.src='${fallbackUrl}';}" alt="Proof" style="width:28px; height:28px; border-radius:8px; object-fit:cover; border:1px solid ${item.is_visited ? 'rgba(52,199,89,0.5)' : (item.proof_status === 'rejected' ? 'rgba(239,68,68,0.5)' : 'rgba(255,149,0,0.5)')}; flex-shrink:0;">`;
                                    }

                                    let proofBadge = '';
                                    if (item.is_visited || item.proof_status === 'approved') {
                                        proofBadge = `<span style="font-size:10px; font-weight:800; color:#34c759; background:rgba(52,199,89,0.15); border:1px solid rgba(52,199,89,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Verified</span>`;
                                    } else if (item.proof_status === 'rejected') {
                                        proofBadge = `<span style="font-size:10px; font-weight:800; color:#ef4444; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>`;
                                    } else if (item.proof_image && (item.proof_status === 'pending' || !item.proof_status)) {
                                        proofBadge = `<span style="font-size:10px; font-weight:800; color:#FF9500; background:rgba(255,149,0,0.15); border:1px solid rgba(255,149,0,0.3); padding:2px 8px; border-radius:100px; margin-left:auto; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-clock"></i> Pending</span>`;
                                    }

                                    destinationsHtml += `
                                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; padding:8px 12px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:12px;">
                                            <div style="width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg, #38bdf8, #2563eb); color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:900; flex-shrink:0; box-shadow:0 2px 6px rgba(56,189,248,0.3);">${index+1}</div>
                                            ${proofImgHtml}
                                            <div style="flex:1; font-size:13px; color:#ffffff; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${destName}</div>
                                            ${proofBadge}
                                        </div>
                                    `;
                                });
                            } else {
                                destinationsHtml = '<div style="font-size:12px; color:rgba(255,255,255,0.5); font-style:italic; text-align:center; padding:10px 0;">No destinations added yet.</div>';
                            }

                            tripsHtml += `
                                <div class="trip-swipe-container" data-trip-id="${trip.id}" style="margin-bottom: 14px; position: relative; overflow: hidden; border-radius: 20px;">
                                    <div class="trip-swipe-bg" style="position: absolute; top: 0; right: 0; bottom: 0; width: 85px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 0 20px 20px 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 800; gap: 4px; z-index: 1; transform: translateX(85px); transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </div>
                                    <div class="trip-swipe-content" style="position: relative; z-index: 2; transition: transform 0.2s ease, border-radius 0.2s ease; background: linear-gradient(135deg, rgba(30, 41, 59, 0.65) 0%, rgba(15, 23, 42, 0.88) 100%); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
                                        <div onclick="const content = this.nextElementSibling; const icon = this.querySelector('.toggle-icon'); if(content.style.maxHeight === '0px' || !content.style.maxHeight){ content.style.paddingTop = '14px'; content.style.paddingBottom = '16px'; content.style.maxHeight = (content.scrollHeight + 50) + 'px'; content.style.opacity = '1'; icon.style.transform = 'rotate(90deg)'; } else { content.style.maxHeight = '0px'; content.style.opacity = '0'; content.style.paddingTop = '0'; content.style.paddingBottom = '0'; icon.style.transform = 'rotate(0deg)'; }" style="cursor:pointer; display:flex; align-items:center; gap: 14px; padding: 16px; transition: background 0.15s;" onpointerdown="this.style.background='rgba(255,255,255,0.05)'" onpointerup="this.style.background=''" onpointercancel="this.style.background=''">
                                            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(56, 189, 248, 0.14); border: 1px solid rgba(56, 189, 248, 0.3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 14px rgba(56,189,248,0.25);">
                                                <i class="fa-solid fa-map-location-dot" style="color: #38bdf8; font-size: 20px;"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <span style="display:block; font-size:16px; font-weight:800; color:#ffffff; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.3px;">${trip.title}</span>
                                                <span style="display:flex; align-items:center; gap:8px; font-size:12px; color:rgba(226,232,240,0.8); font-weight:600;">
                                                    <span><i class="fa-solid fa-location-dot" style="margin-right:4px; color:#38bdf8;"></i>${trip.items ? trip.items.length : 0} Stops</span>
                                                    <span style="color:rgba(255,255,255,0.15);">&bull;</span>
                                                    <span><i class="fa-regular fa-calendar" style="margin-right:4px; color:#38bdf8;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No Date'}</span>
                                                </span>
                                            </div>
                                            <i class="fa-solid fa-chevron-right toggle-icon" style="color: rgba(255,255,255,0.4); font-size: 14px; margin-right:4px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></i>
                                        </div>
                                        <div style="max-height: 0px; opacity: 0; padding: 0 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; background: transparent; border-top: 1px solid rgba(56,189,248,0.15);">
                                            ${destinationsHtml}
                                            <div style="display:flex; gap:10px; margin-top:12px; margin-bottom:2px;">
                                                <button onclick="window.location.href='?view=trip_map&trip_id=${trip.id}'" style="flex:1; background:linear-gradient(135deg, #38bdf8 0%, #2563eb 100%); border:1px solid rgba(255,255,255,0.2); color:#ffffff; padding:12px; border-radius:14px; font-weight:800; font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 4px 16px rgba(56,189,248,0.35);">
                                                    <i class="fa-solid fa-compass"></i> Start Trip
                                                </button>
                                                <button onclick="navigateTo('saved_trips')" style="flex:1; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; padding:12px; border-radius:14px; font-weight:800; font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                                                    <i class="fa-solid fa-route"></i> View Details
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
                            <div style="padding: 28px 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px;">
                                <i class="fa-solid fa-route" style="font-size: 32px; color: rgba(56,189,248,0.4);"></i>
                                <div style="color: rgba(148,163,184,0.8); font-size: 14px; line-height: 1.4;">No saved trips yet.</div>
                                <button onclick="navigateTo('itinerary')" style="background: linear-gradient(135deg, #38bdf8, #2563eb); color: white; border: none; padding: 11px 22px; border-radius: 100px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(56,189,248,0.3);">
                                    <i class="fa-solid fa-plus"></i> Plan a Trip
                                </button>
                            </div>
                        `;
                    }
                },
                false,
                60000 // 1 minute TTL for trips
            );
        }
    } catch(e) {
        console.error(e);
    }
})();

window.toggleFavorite = function(destId, element) {
    const token = localStorage.getItem('intan_elyu_token');
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
    
    const card = element.closest('.fav-card');
    const isSavedContainer = card && card.parentElement && card.parentElement.id === 'saved-places-container';
    
    // Save original state for reverting
    const originalColor = element.style.color;
    const wasRed = originalColor === 'rgb(255, 59, 48)' || originalColor === '#ff3b30';
    
    // 1. INSTANT OPTIMISTIC UPDATE (Zero Delay)
    // Trigger pop animation
    element.classList.remove('heart-pop-anim');
    void element.offsetWidth; // trigger reflow
    element.classList.add('heart-pop-anim');

    if (wasRed) {
        element.style.color = 'rgba(255,255,255,0.4)';
        element.classList.remove('fa-solid');
        element.classList.add('fa-regular');
        if (typeof showToast === 'function') showToast('Removed from Saved Places');
        
        if (isSavedContainer) {
            card.style.transition = 'opacity 0.3s, transform 0.3s, width 0.3s, padding 0.3s, margin 0.3s';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.8)';
            card.style.width = '0px';
            card.style.marginRight = '0px';
            card.style.padding = '0px';
            card.style.pointerEvents = 'none'; // prevent clicks while shrinking
            
            // Remove from DOM immediately after animation
            setTimeout(() => {
                card.remove();
                const container = document.getElementById('saved-places-container');
                if (container) {
                    let hasCards = Array.from(container.children).some(c => c.classList.contains('fav-card') && c.style.pointerEvents !== 'none');
                    if (!hasCards) {
                        container.innerHTML = `
                            <style>
                            @keyframes popInEmptyState {
                                0% { opacity: 0; transform: scale(0.9); }
                                100% { opacity: 1; transform: scale(1); }
                            }
                            </style>
                            <div style="animation: popInEmptyState 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; padding: 28px 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; margin: 0 16px;">
                                <i class="fa-solid fa-map-location-dot" style="font-size: 32px; color: rgba(56,189,248,0.4);"></i>
                                <div style="color: rgba(148,163,184,0.8); font-size: 14px; line-height: 1.4;">Go to the map to save some places!</div>
                                <button onclick="window.location.href='?view=map'" style="background: linear-gradient(135deg, #38bdf8, #2563eb); color: white; border: none; padding: 11px 22px; border-radius: 100px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(56,189,248,0.3);">
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

    // 2. BACKGROUND NETWORK REQUEST
    fetch(backendUrl + '/api/tourist/destinations/' + destId + '/favorite', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
        }
    }).then(r => {
        // Invalidate caches so saved_places view reflects the change
        const tokenPrefix = token ? token.substring(0, 10) : '';
        localStorage.removeItem('saved_places_' + tokenPrefix);
    }).catch(e => {
        // Revert on error
        if (typeof showToast === 'function') showToast('Error updating favorite');
        element.style.color = originalColor;
        
        if (wasRed) {
            element.classList.remove('fa-regular');
            element.classList.add('fa-solid');
        } else {
            element.classList.remove('fa-solid');
            element.classList.add('fa-regular');
        }
        
        if (isSavedContainer && wasRed) {
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
            card.style.width = '';
            card.style.marginRight = '';
            card.style.padding = '';
            card.style.pointerEvents = 'auto';
        }
    });
};

window.viewDestinationOnMap = function(encodedDest) {
    try {
        const dest = JSON.parse(decodeURIComponent(encodedDest));
        localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
        window.location.href = '?view=map';
    } catch(e) { console.error('Failed to view destination:', e); }
};

window.toggleRecommendedMore = function() {
    const extras = document.getElementById('rec-extras');
    const chevron = document.getElementById('rec-chevron');
    const btn = document.getElementById('btn-view-more-rec');

    if (!extras) return;

    const isOpen = extras.style.maxHeight !== '0px' && extras.style.maxHeight !== '';

    if (isOpen) {
        extras.style.maxHeight = '0';
        chevron.style.transform = 'rotate(0deg)';
        // Update button text (preserve icon)
        btn.innerHTML = `<i class="fa-solid fa-chevron-down" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i> View More`;
    } else {
        extras.style.maxHeight = extras.scrollHeight + 'px';
        chevron.style.transform = 'rotate(180deg)';
        btn.innerHTML = `<i class="fa-solid fa-chevron-up" id="rec-chevron" style="font-size:11px; transition: transform 0.3s;"></i> Show Less`;
    }
};

    // Initialize Search Functionality
    setTimeout(() => {
        const searchInput = document.getElementById('dash-search-input');
        const searchResults = document.getElementById('dash-search-results');
        const searchWrapper = document.querySelector('.search-wrapper');
        if (searchWrapper) {
            searchWrapper.style.zIndex = '50';
        }

        let allDestinationsForSearch = null;
        let isFetching = false;

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', async (e) => {
                const query = e.target.value.toLowerCase().trim();
                if (query.length === 0) {
                    searchResults.style.display = 'none';
                    return;
                }

                if (!allDestinationsForSearch && !isFetching) {
                    isFetching = true;
                    try {
                        const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
                        const res = await fetch(backendUrl + '/api/public/map');
                        const data = await res.json();
                        allDestinationsForSearch = data.destinations || [];
                    } catch (err) {
                        console.error('Failed to fetch destinations for search', err);
                        allDestinationsForSearch = [];
                    }
                    isFetching = false;
                }

                if (!allDestinationsForSearch) return;

                // Re-evaluate query in case the user typed more while fetching
                const currentQuery = searchInput.value.toLowerCase().trim();
                if (currentQuery.length === 0) {
                    searchResults.style.display = 'none';
                    return;
                }

                const matches = allDestinationsForSearch.filter(dest => 
                    dest.name.toLowerCase().includes(currentQuery) || 
                    (dest.category && dest.category.toLowerCase().includes(currentQuery)) ||
                    (dest.location && dest.location.toLowerCase().includes(currentQuery)) ||
                    (dest.municipality && dest.municipality.toLowerCase().includes(currentQuery))
                ).slice(0, 6); // Limit to top 6 results

                if (matches.length > 0) {
                    let html = '';
                    matches.forEach(dest => {
                        const img = window.getDestImage(dest, 150);
                        const encodedDest = encodeURIComponent(JSON.stringify(dest));
                        const locName = dest.location || dest.municipality || 'La Union';
                        html += `
                            <div onclick="window.viewDestinationOnMap('${encodedDest}')" style="display:flex; align-items:center; gap:12px; padding:12px; border-bottom:1px solid rgba(255,255,255,0.05); cursor:pointer; transition:background 0.2s; border-radius:12px;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background=''">
                                <img src="${img}" style="width:44px; height:44px; border-radius:10px; object-fit:cover; border:1px solid rgba(255,255,255,0.1);">
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:13px; font-weight:800; color:#f8fafc; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${dest.name}</div>
                                    <div style="font-size:11px; font-weight:500; color:rgba(148,163,184,0.9); margin-top:2px;"><i class="fa-solid fa-location-dot" style="color:#38bdf8; margin-right:4px;"></i>${locName}</div>
                                </div>
                                <i class="fa-solid fa-chevron-right" style="color:rgba(148,163,184,0.3); font-size:12px; margin-right:4px;"></i>
                            </div>
                        `;
                    });
                    searchResults.innerHTML = html;
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = `
                        <div style="padding:20px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:8px;">
                            <i class="fa-solid fa-location-crosshairs" style="font-size:24px; color:rgba(255,255,255,0.2);"></i>
                            <div style="font-size:12px; font-weight:600; color:rgba(255,255,255,0.5);">Location not found</div>
                        </div>
                    `;
                    searchResults.style.display = 'block';
                }
            });

            // Hide when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
            
            // Show again when focusing input if there is text
            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length > 0 && searchResults.innerHTML.trim() !== '') {
                    searchResults.style.display = 'block';
                }
            });
        }
    }, 500);

    window.setupDashboardSwipeToDelete = function() {
        document.querySelectorAll('.trip-swipe-container').forEach(container => {
            const content = container.querySelector('.trip-swipe-content');
            const bg = container.querySelector('.trip-swipe-bg');
            if (!content) return;
            let startX = 0, currentX = 0, isSwiping = false, moved = false;

            const handleStart = (clientX) => {
                startX = clientX;
                currentX = startX;
                isSwiping = true;
                moved = false;
                content.style.transition = 'none';
                if (bg) bg.style.transition = 'none';
            };

            const handleMove = (clientX) => {
                if (!isSwiping) return;
                currentX = clientX;
                let diff = startX - currentX;
                if (Math.abs(diff) > 5) moved = true;
                if (diff > 0) {
                    const translate = Math.min(diff, 85);
                    content.style.transform = `translateX(-${translate}px)`;
                    content.style.borderRadius = translate > 5 ? '20px 0 0 20px' : '20px';
                    if (bg) bg.style.transform = `translateX(${85 - translate}px)`;
                } else if (diff < -5) {
                    content.style.transform = 'translateX(0px)';
                    content.style.borderRadius = '20px';
                    if (bg) bg.style.transform = 'translateX(85px)';
                }
            };

            const handleEnd = () => {
                if (!isSwiping) return;
                content.style.transition = 'transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-radius 0.2s ease';
                if (bg) bg.style.transition = 'transform 0.2s cubic-bezier(0.16, 1, 0.3, 1)';
                const diff = startX - currentX;
                if (diff > 60 && moved) {
                    const id = container.dataset.tripId;
                    if (id) window.deleteSavedTrip(id, container);
                } else {
                    content.style.transform = 'translateX(0px)';
                    content.style.borderRadius = '20px';
                    if (bg) bg.style.transform = 'translateX(85px)';
                }
                startX = 0;
                currentX = 0;
                isSwiping = false;
                moved = false;
            };

            content.addEventListener('touchstart', (e) => handleStart(e.touches[0].clientX), { passive: true });
            content.addEventListener('touchmove', (e) => handleMove(e.touches[0].clientX), { passive: true });
            content.addEventListener('touchend', handleEnd, { passive: true });

            content.addEventListener('mousedown', (e) => handleStart(e.clientX));
            window.addEventListener('mousemove', (e) => { if (isSwiping) handleMove(e.clientX); });
            window.addEventListener('mouseup', () => { if (isSwiping) handleEnd(); });
        });
    };

    window.deleteSavedTrip = async function(id, element) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;
        
        try {
            const res = await fetch(window.backendUrl + '/api/tourist/itineraries/' + id, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            // Clear local cached saved trips
            const cacheKey = 'saved_trips_' + token.substring(0, 10);
            localStorage.removeItem(cacheKey);

            if (res.ok || res.status === 404) {
                if (element) {
                    element.style.transition = 'all 0.3s ease';
                    element.style.opacity = '0';
                    element.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        element.remove();
                        const tripsContainer = document.getElementById('saved-trips-container');
                        if (tripsContainer && tripsContainer.children.length === 0) {
                            tripsContainer.innerHTML = `
                                <div style="padding: 28px 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px;">
                                    <i class="fa-solid fa-route" style="font-size: 32px; color: rgba(56,189,248,0.4);"></i>
                                    <div style="color: rgba(148,163,184,0.8); font-size: 14px; line-height: 1.4;">No saved trips yet.</div>
                                    <button onclick="navigateTo('itinerary')" style="background: linear-gradient(135deg, #38bdf8, #2563eb); color: white; border: none; padding: 11px 22px; border-radius: 100px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(56,189,248,0.3);">
                                        <i class="fa-solid fa-plus"></i> Plan a Trip
                                    </button>
                                </div>
                            `;
                        }
                    }, 300);
                }
            } else {
                const content = element ? element.querySelector('.trip-swipe-content') : null;
                if (content) { content.style.transform = 'translateX(0px)'; content.style.borderRadius = '20px'; }
            }
        } catch (e) {
            console.error('Error deleting itinerary', e);
        }
    };

    // ─────────────────────────────────────────────────────────────────────────────
    //  Weather API Integration
    // ─────────────────────────────────────────────────────────────────────────────
    window.currentWeatherLocation = { lat: 16.6159, lng: 120.3209, name: 'San Fernando, La Union', isCurrentLoc: false };

    window.fetchWeather = async function(lat = 16.6159, lng = 120.3209, locationName = 'San Fernando, La Union', isCurrentLoc = false) {
        window.currentWeatherLocation = { lat, lng, name: locationName, isCurrentLoc };
        const tempEl = document.getElementById('weather-temp');
        const descEl = document.getElementById('weather-desc');
        const locEl = document.getElementById('weather-loc');
        const humidityEl = document.getElementById('weather-humidity');
        const windEl = document.getElementById('weather-wind');
        const uvEl = document.getElementById('weather-uv');
        const iconEl = document.getElementById('weather-icon');

        try {
            const apiBase = window.backendUrl || '';
            const isCurrentParam = isCurrentLoc ? '&is_current_location=1' : '';
            const url = `${apiBase}/api/public/weather?lat=${lat}&lng=${lng}&location=${encodeURIComponent(locationName)}${isCurrentParam}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Weather request failed with status ' + res.status);
            const data = await res.json();
            
            if (tempEl) tempEl.textContent = `${data.temperature}°C`;
            if (descEl) descEl.textContent = data.condition;
            if (locEl) locEl.innerHTML = `<i class="fa-solid fa-location-dot" style="color:#38bdf8; margin-right:4px;"></i> ${data.location}`;
            if (humidityEl) humidityEl.textContent = `${data.humidity}%`;
            if (windEl) windEl.textContent = `${data.wind_speed} km/h`;
            if (uvEl) uvEl.textContent = data.uv_index;
            if (iconEl) iconEl.innerHTML = window.renderWeatherIconHtml(data.icon, data.fa_icon);

            window.updateWeatherModal(data);
        } catch (e) {
            console.warn('Weather fetch error, falling back:', e);
            if (tempEl) tempEl.textContent = '29°C';
            if (descEl) descEl.textContent = 'Partly Cloudy';
            if (humidityEl) humidityEl.textContent = '72%';
            if (windEl) windEl.textContent = '14 km/h';
            if (uvEl) uvEl.textContent = '6';
        }
    };

    window.renderWeatherIconHtml = function(iconEmoji, faIcon) {
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

    window.locateMeForWeather = function() {
        const locEl = document.getElementById('weather-loc');
        const descEl = document.getElementById('weather-desc');
        if (locEl) locEl.innerHTML = `<i class="fa-solid fa-location-dot fa-spin" style="color:#38bdf8; margin-right:4px;"></i> Detecting location...`;
        if (descEl) descEl.textContent = 'Acquiring location...';

        const fallbackToIp = () => {
            fetch('https://ipapi.co/json/')
                .then(r => r.json())
                .then(ipData => {
                    if (ipData && ipData.latitude && ipData.longitude) {
                        const locName = (ipData.city || ipData.region || 'La Union') + ', ' + (ipData.region || 'Philippines');
                        window.fetchWeather(ipData.latitude, ipData.longitude, locName, true);
                    } else {
                        window.fetchWeather(16.6159, 120.3209, 'San Fernando, La Union', false);
                    }
                })
                .catch(() => {
                    window.fetchWeather(16.6159, 120.3209, 'San Fernando, La Union', false);
                });
        };

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    window.userCurrentCoords = { lat, lng };
                    window.fetchWeather(lat, lng, 'My Location', true);
                },
                (err) => {
                    console.warn('GPS location failed, using IP fallback:', err);
                    fallbackToIp();
                },
                { enableHighAccuracy: false, timeout: 5000, maximumAge: 300000 }
            );
        } else {
            fallbackToIp();
        }
    };

    window.updateWeatherModal = function(data) {
        const modalLoc = document.getElementById('modal-weather-loc');
        const modalTemp = document.getElementById('modal-temp');
        const modalCondition = document.getElementById('modal-condition');
        const modalFeels = document.getElementById('modal-feels');
        const modalIcon = document.getElementById('modal-icon');
        const forecastContainer = document.getElementById('weather-forecast-container');
        const muniSelect = document.getElementById('weather-muni-select');

        if (modalLoc) modalLoc.textContent = data.location;
        if (modalTemp) modalTemp.textContent = `${data.temperature}°C`;
        if (modalCondition) modalCondition.textContent = data.condition;
        if (modalFeels) modalFeels.textContent = `Feels like ${data.feels_like}°C · Humidity ${data.humidity}% · Wind ${data.wind_speed}km/h`;
        if (modalIcon) modalIcon.innerHTML = window.renderWeatherIconHtml(data.icon, data.fa_icon);

        if (forecastContainer && data.forecast && data.forecast.length > 0) {
            forecastContainer.innerHTML = data.forecast.map(item => `
                <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <span style="font-size:26px; width:32px; text-align:center; display:inline-block;">${window.renderWeatherIconHtml(item.icon, item.fa_icon)}</span>
                        <div>
                            <div style="font-size:14px; font-weight:800; color:#fff;">${item.day}</div>
                            <div style="font-size:11px; color:rgba(255,255,255,0.6);">${item.condition}</div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:14px; font-weight:800; color:#fff;">${item.temp_max}° <span style="font-size:12px; color:rgba(255,255,255,0.5); font-weight:500;">/ ${item.temp_min}°C</span></div>
                        <div style="font-size:10px; color:#38bdf8; font-weight:700; margin-top:2px;"><i class="fa-solid fa-droplet" style="font-size:9px;"></i> ${item.rain_prob}% rain</div>
                    </div>
                </div>
            `).join('');
        }
    };

    window.openWeatherModal = function() {
        const modal = document.getElementById('weather-modal');
        if (modal) modal.style.display = 'flex';
    };

    window.closeWeatherModal = function() {
        const modal = document.getElementById('weather-modal');
        if (modal) modal.style.display = 'none';
    };

    window.changeWeatherLocation = function(valStr) {
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
        }
    };

    // Auto locate & fetch weather on startup
    setTimeout(() => {
        if (typeof window.fetchWeather === 'function') {
            window.locateMeForWeather();
        }
    }, 100);

    // =========================================================================
    // ONBOARDING MODAL CONTROLLER (Complete Profile)
    // =========================================================================
    window.initDashboardOnboarding = function() {
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
        } catch (e) {}

        const profileModal = document.getElementById('onboard-profile-modal');
        if (profileModal) profileModal.style.display = 'flex';
    };

    window.previewOnboardAvatar = function(event) {
        const file = event.target?.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('onboard-avatar-preview');
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };

    window.onboardSaveProfile = async function() {
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
                const res = await fetch((window.backendUrl || window.location.origin) + '/api/tourist/profile', {
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
            } catch (e) {}
        }
        
        sessionStorage.removeItem('show_onboarding');
        sessionStorage.removeItem('onboarding_active');
        sessionStorage.removeItem('onboarding_step');
        sessionStorage.removeItem('pending_reg_email');
        const profileModal = document.getElementById('onboard-profile-modal');
        if (profileModal) profileModal.style.display = 'none';
        if (typeof showToast === 'function') showToast('Profile updated! Welcome to Intan Elyu!');
    };

    window.onboardSkipProfile = function() {
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
<div id="onboard-profile-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(12px); z-index:999999; justify-content:center; align-items:center; padding:16px;">
    <div style="background:#0f172a; border:1px solid rgba(56,189,248,0.3); border-radius:24px; width:100%; max-width:480px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.8); animation:slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
        <div style="padding:20px 24px; background:linear-gradient(135deg, rgba(30,41,59,0.8), rgba(15,23,42,0.95)); border-bottom:1px solid rgba(255,255,255,0.08);">
            <h3 style="margin:0 0 4px 0; font-size:17px; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-user-gear" style="color:#fbbf24;"></i> Complete Your Profile
            </h3>
            <p style="margin:0; font-size:12px; color:rgba(148,163,184,0.9);">Tell us a bit about yourself to personalize recommendations.</p>
        </div>
        <div style="padding:20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:14px;">
            <!-- Profile Avatar Picker -->
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; margin-bottom:4px;">
                <div style="position:relative; width:84px; height:84px;">
                    <img id="onboard-avatar-preview" src="https://ui-avatars.com/api/?name=User&background=38bdf8&color=fff" style="width:84px; height:84px; border-radius:50%; object-fit:cover; border:2.5px solid #38bdf8; box-shadow:0 6px 20px rgba(56,189,248,0.35);">
                    <label for="onboard-avatar-input" style="position:absolute; bottom:2px; right:2px; width:28px; height:28px; background:#38bdf8; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.5); border:2px solid #0f172a; transition:transform 0.2s;">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                    <input type="file" id="onboard-avatar-input" accept="image/*" style="display:none;" onchange="window.previewOnboardAvatar(event)">
                </div>
                <span style="font-size:11px; font-weight:700; color:#38bdf8; cursor:pointer;" onclick="document.getElementById('onboard-avatar-input').click()">
                    Add Profile Picture
                </span>
            </div>

            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:6px;">Mobile Phone Number</label>
                <input type="text" id="onboard-phone" placeholder="0912 345 6789" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:10px 14px; color:#fff; font-size:13px; outline:none;">
            </div>
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:6px;">Home Town / City</label>
                <input type="text" id="onboard-home" placeholder="e.g. Manila, La Union, Baguio" style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:10px 14px; color:#fff; font-size:13px; outline:none;">
            </div>
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:6px;">Short Bio</label>
                <textarea id="onboard-bio" rows="2" placeholder="Share your travel interests..." style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:10px 14px; color:#fff; font-size:13px; outline:none; resize:none;"></textarea>
            </div>
            <div>
                <label style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); margin-bottom:8px;">Travel Preferences</label>
                <div style="display:flex; flex-wrap:wrap; gap:8px;" id="onboard-pref-chips">
                    <span onclick="this.classList.toggle('active')" class="pref-chip" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🏄 Surfing</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🏖️ Beach</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">⛰️ Hiking</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🍜 Food & Dining</span>
                    <span onclick="this.classList.toggle('active')" class="pref-chip" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; padding:6px 12px; border-radius:100px; font-size:11px; font-weight:600; cursor:pointer;">🏛️ Heritage</span>
                </div>
            </div>
        </div>
        <div style="padding:16px 20px; background:rgba(15,23,42,0.98); border-top:1px solid rgba(255,255,255,0.08); display:flex; gap:10px;">
            <button onclick="window.onboardSkipProfile()" style="flex:1; background:transparent; border:1px solid rgba(255,255,255,0.15); color:rgba(255,255,255,0.7); padding:12px; border-radius:12px; font-weight:700; font-size:12px; cursor:pointer;">
                Skip for now
            </button>
            <button id="btn-onboard-save" onclick="window.onboardSaveProfile()" style="flex:2; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:white; padding:12px; border-radius:12px; font-weight:800; font-size:13px; cursor:pointer; box-shadow:0 4px 14px rgba(56,189,248,0.3); display:flex; align-items:center; justify-content:center; gap:8px;">
                Save & Continue <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

