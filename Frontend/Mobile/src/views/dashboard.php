<!-- Dashboard View -->
<?php
$pageTitle = 'Discover La Union';
$activeTab = 'dashboard';

// Scan municipality images from backend storage
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




<div class="dashboard-container has-header has-bottom-nav animate-slide-up">

    <!-- Profile + EXP -->
    <div class="profile-header stagger-1" onclick="navigateTo('profile')">
        <div class="profile-info-row">
            <div class="profile-avatar">
                <img id="dash-avatar" src="https://ui-avatars.com/api/?name=Tourist&amp;background=007AFF&amp;color=fff&amp;rounded=true&amp;bold=true&amp;size=128" alt="Avatar">
            </div>
            <div class="profile-text">
                <h2 class="profile-name" id="dash-name">Hi, there!</h2>
                <p class="profile-title" id="dash-title">Explorer of Elyu</p>
            </div>
        </div>
        <div class="exp-container">
            <div class="exp-header">
                <span class="exp-label" id="dash-level-label">Level Progress</span>
                <span class="exp-value" id="dash-xp-value">— XP</span>
            </div>
            <div class="exp-bar-bg"><div class="exp-bar-fill" id="dash-xp-bar" style="width:0%;"></div></div>
        </div>
    </div>



    <!-- Stats Row -->
    <div class="stats-row stagger-1">
        <div class="stat-card" onclick="navigateTo('itinerary')">
            <div class="stat-icon"><i class="fa-solid fa-map-location-dot" style="color:#007AFF;"></i></div>
            <div class="stat-value" id="dash-stat-places">—</div>
            <div class="stat-label">Places</div>
        </div>
        <div class="stat-card" onclick="navigateTo('leaderboard')">
            <div class="stat-icon"><i class="fa-solid fa-bolt" style="color:#FFD700;"></i></div>
            <div class="stat-value" id="dash-stat-xp">—</div>
            <div class="stat-label">XP</div>
        </div>
        <div class="stat-card" onclick="navigateTo('leaderboard')">
            <div class="stat-icon"><i class="fa-solid fa-trophy" style="color:#FF9500;"></i></div>
            <div class="stat-value" id="dash-stat-rank">—</div>
            <div class="stat-label">Rank</div>
        </div>
    </div>

    <!-- Weather Widget -->
    <div class="weather-card stagger-2">
        <div class="weather-left">
            <div class="weather-temp">29°C</div>
            <div class="weather-desc">Partly Cloudy</div>
            <div class="weather-loc">📍 San Fernando, La Union</div>
            <div class="weather-details">
                <span class="weather-detail"><i class="fa-solid fa-droplet"></i> 72%</span>
                <span class="weather-detail"><i class="fa-solid fa-wind"></i> 14 km/h</span>
                <span class="weather-detail"><i class="fa-solid fa-sun"></i> UV 6</span>
            </div>
        </div>
        <div class="weather-icon">⛅</div>
    </div>

    <!-- Gamification Panel -->
    <div class="weather-card stagger-2" onclick="navigateTo('puzzles')" style="background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%); border: 1px solid rgba(139, 92, 246, 0.3); box-shadow: 0 8px 32px rgba(139, 92, 246, 0.15); margin-top: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; padding: 16px 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 32px; filter: drop-shadow(0 0 10px rgba(167, 139, 250, 0.6));">🧩</div>
            <div>
                <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -0.2px;">Gamification Zone</h4>
                <p style="margin: 0; font-size: 12px; color: #e9d5ff; font-weight: 600;">Play puzzles & trivia to earn discount vouchers! 🎁</p>
            </div>
        </div>
        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 14px;"><i class="fa-solid fa-play"></i></div>
    </div>

    <!-- Trending Spots -->
    <div class="dash-section stagger-2">
        <div class="section-title">
            <h3>Trending Spots</h3>
            <a href="javascript:void(0);" onclick="navigateTo('trending')">See All</a>
        </div>
        <div class="favorites-row" id="trending-container">
            <div style="padding: 20px; width: 100%; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading trending spots...
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
(async function dashboardInit() {
    const setTxt = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
    const setSrc = (id, src) => { const el = document.getElementById(id); if (el) el.src = src; };

    var backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';
    const token = localStorage.getItem('intan_elyu_token');
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}');

    // Instant render from cache
    if (user && user.name) {
        setTxt('dash-name', 'Hi, ' + user.name.split(' ')[0] + '! 👋');
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
        setTxt('dash-name', 'Hi, ' + firstName + '! 👋');
        setTxt('dash-title', 'Level ' + (u.level || 1) + ' Explorer');
        
        if (u.avatar) {
            setSrc('dash-avatar', u.avatar);
        } else {
            setSrc('dash-avatar', `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);
        }

        // XP Bar
        const xp = parseInt(u.xp) || 0;
        const level = parseInt(u.level) || 1;
        const xpPerLevel = 1000;
        const xpInLevel = xp % xpPerLevel;
        const xpPct = Math.min((xpInLevel / xpPerLevel) * 100, 100);
        document.getElementById('dash-level-label')?.textContent && (document.getElementById('dash-level-label').textContent = 'Level ' + level + ' Progress');
        document.getElementById('dash-xp-value')?.textContent && (document.getElementById('dash-xp-value').textContent = xpInLevel + ' / ' + xpPerLevel + ' XP');
        if (document.getElementById('dash-xp-bar')) document.getElementById('dash-xp-bar').style.width = xpPct + '%';

        // Stats
        if (document.getElementById('dash-stat-places')) document.getElementById('dash-stat-places').textContent = (data.stats && data.stats.placesVisited) ? data.stats.placesVisited : 0;
        if (document.getElementById('dash-stat-xp')) document.getElementById('dash-stat-xp').textContent = xp.toLocaleString();

        // Populate Trending Spots (Top 3)
        const trendingContainer = document.getElementById('trending-container');
        if (trendingContainer) {
            trendingContainer.innerHTML = '';
            if (data.trending && data.trending.length > 0) {
                data.trending.forEach(dest => {
                    const img = window.getDestImage(dest, 600);
                    const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                    trendingContainer.innerHTML += `
                        <div class="fav-card" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
                            ${badgeHtml}
                            <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                            <div class="fav-card-overlay"><span class="fav-card-name">${dest.name}</span></div>
                            <i class="fa-solid fa-fire fav-heart" style="color: #ff9500; font-size: 14px;"></i>
                        </div>
                    `;
                });
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
                data.savedPlaces.forEach(dest => {
                    const img = window.getDestImage(dest, 600);
                    const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                    savedContainer.innerHTML += `
                        <div class="fav-card" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
                            ${badgeHtml}
                            <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                            <div class="fav-card-overlay"><span class="fav-card-name">${dest.name}</span></div>
                            <i class="fa-solid fa-heart fav-heart" style="color: #ff3b30;" onclick="event.stopPropagation(); window.toggleFavorite(${dest.id}, this)"></i>
                        </div>
                    `;
                });
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
                    nearSpots.slice(0, 5).forEach(dest => {
                        const img = window.getDestImage(dest, 600);
                        const badgeHtml = dest.classification_status ? `<div style="position: absolute; top: 8px; left: 8px; z-index: 10; padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; background: ${dest.classification_status === 'EXIST' ? '#34c759' : (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b')}; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${dest.classification_status === 'EXIST' ? 'EXISTING' : (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL')}</div>` : '';
                        const distText = dest.distance < 1 ? '< 1 km' : dest.distance.toFixed(1) + ' km';
                        nearContainer.innerHTML += `
                            <div class="fav-card" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
                                ${badgeHtml}
                                <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';">
                                <div class="fav-card-overlay">
                                    <span class="fav-card-name">${dest.name}</span>
                                    <span style="display:block; font-size:10px; color:#38bdf8; margin-top:2px; font-weight:700;"><i class="fa-solid fa-location-arrow"></i> ${distText} away</span>
                                </div>
                            </div>
                        `;
                    });
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
        
        return `
            <div style="margin-bottom: 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 18px; overflow: hidden; transition: all 0.3s ease;">
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
                    <button onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))" style="width:100%; margin-top:4px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:white; padding:10px; border-radius:12px; font-weight:700; font-size:13px; cursor:pointer; box-shadow:0 4px 14px rgba(56,189,248,0.3); display:flex; align-items:center; justify-content:center; gap:8px;">
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
            false,
            30000 // 30 seconds TTL for dashboard
        );

        loadNearMe(lat, lng);

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
                    const itineraries = itinData.itineraries || [];
                    if (itineraries.length > 0) {
                        let tripsHtml = '';
                        // Show up to 3 most recent trips
                        itineraries.slice(0, 3).forEach(trip => {
                            let destinationsHtml = '';
                            if (trip.items && trip.items.length > 0) {
                                trip.items.forEach((item, index) => {
                                    const destName = item.destination ? item.destination.name : 'Unknown Destination';
                                    destinationsHtml += `
                                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                                            <div style="width:24px; height:24px; border-radius:50%; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.2); color:#38bdf8; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0;">${index+1}</div>
                                            <div style="flex:1; font-size:13px; color:rgba(248,250,252,0.85); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${destName}</div>
                                        </div>
                                    `;
                                });
                            } else {
                                destinationsHtml = '<div style="font-size:12px; color:rgba(255,255,255,0.4); font-style:italic; text-align:center; padding:10px 0;">No destinations added yet.</div>';
                            }

                            tripsHtml += `
                                <div class="trip-swipe-container" data-trip-id="${trip.id}" style="margin-bottom: 12px; position: relative; overflow: hidden; border-radius: 16px;">
                                    <div class="trip-swipe-bg" style="position: absolute; top: 0; right: 0; bottom: 0; width: 80px; background: #ef4444; border-radius: 0 16px 16px 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 700; gap: 4px; transform: translateX(100%);">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </div>
                                    <div class="trip-swipe-content" style="position: relative; z-index: 1; transition: transform 0.2s ease; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden;">
                                        <div onclick="const content = this.nextElementSibling; const icon = this.querySelector('.toggle-icon'); if(content.style.maxHeight === '0px' || !content.style.maxHeight){ content.style.paddingTop = '14px'; content.style.paddingBottom = '14px'; content.style.maxHeight = (content.scrollHeight + 50) + 'px'; content.style.opacity = '1'; icon.style.transform = 'rotate(90deg)'; } else { content.style.maxHeight = '0px'; content.style.opacity = '0'; content.style.paddingTop = '0'; content.style.paddingBottom = '0'; icon.style.transform = 'rotate(0deg)'; }" style="cursor:pointer; display:flex; align-items:center; gap: 14px; padding: 14px; transition: background 0.15s;" onpointerdown="this.style.background='rgba(255,255,255,0.05)'" onpointerup="this.style.background=''" onpointercancel="this.style.background=''">
                                            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                                <i class="fa-solid fa-map-location-dot" style="color: #38bdf8; font-size: 20px;"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <span style="display:block; font-size:15px; font-weight:800; color:#f8fafc; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.2px;">${trip.title}</span>
                                                <span style="display:block; font-size:12px; color:rgba(148,163,184,0.9); font-weight:500;">
                                                    <i class="fa-solid fa-location-dot" style="margin-right:3px; color:rgba(148,163,184,0.6);"></i>${trip.items ? trip.items.length : 0} Stops
                                                    <span style="margin:0 4px; color:rgba(255,255,255,0.1);">|</span>
                                                    <i class="fa-regular fa-calendar" style="margin-right:3px; color:rgba(148,163,184,0.6);"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No Date'}
                                                </span>
                                            </div>
                                            <i class="fa-solid fa-chevron-right toggle-icon" style="color: rgba(255,255,255,0.3); font-size: 14px; margin-right:4px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></i>
                                        </div>
                                        <div style="max-height: 0px; opacity: 0; padding: 0 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.03);">
                                            ${destinationsHtml}
                                            <button onclick="navigateTo('itinerary')" style="width:100%; margin-top:6px; background:rgba(56,189,248,0.1); border:1px dashed rgba(56,189,248,0.3); color:#38bdf8; padding:10px; border-radius:12px; font-weight:700; font-size:13px; cursor:pointer; transition:background 0.2s;">
                                                Open Full Itinerary
                                            </button>
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
    var backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';
    
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
                        const backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';
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
                        const encodedDest = encodeURIComponent(JSON.stringify(dest).replace(/"/g, '&quot;'));
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
            let startX = 0, currentX = 0, isSwiping = false;

            content.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                isSwiping = false;
                content.style.transition = 'none';
            }, { passive: true });

            content.addEventListener('touchmove', (e) => {
                if (startX === 0) return;
                currentX = e.touches[0].clientX;
                let diff = startX - currentX;
                if (Math.abs(diff) > 5) isSwiping = true;
                if (diff < 0) diff = 0;
                const translate = Math.min(diff, 80);
                content.style.transform = `translateX(-${translate}px)`;
                content.style.borderRadius = translate > 5 ? '16px 0 0 16px' : '16px';
                if (bg) bg.style.transform = `translateX(${80 - translate}px)`;
            }, { passive: true });

            content.addEventListener('touchend', (e) => {
                content.style.transition = 'transform 0.2s ease, border-radius 0.2s ease';
                if (bg) bg.style.transition = 'transform 0.2s ease';
                const diff = startX - currentX;
                if (diff > 60 && isSwiping) {
                    const id = container.dataset.tripId;
                    if (id) window.deleteSavedTrip(id, container);
                } else {
                    content.style.transform = '';
                    content.style.borderRadius = '16px';
                    if (bg) bg.style.transform = 'translateX(100%)';
                }
                startX = 0;
                currentX = 0;
                isSwiping = false;
            }, { passive: true });
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
            if (res.ok) {
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
            } else {
                console.error('Failed to delete itinerary');
                const content = element.querySelector('.trip-swipe-content');
                const bg = element.querySelector('.trip-swipe-bg');
                if (content) { content.style.transform = ''; content.style.borderRadius = '16px'; }
                if (bg) bg.style.transform = 'translateX(100%)';
            }
        } catch (e) {
            console.error('Error deleting itinerary', e);
        }
    };
</script>

