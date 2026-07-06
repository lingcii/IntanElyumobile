<!-- Dashboard View -->
<?php
$pageTitle = 'Discover La Union';
$activeTab = 'dashboard';

// Scan local municipality images
$municipalityImages = [];
$imgDir = __DIR__ . '/../assets/img/municipalities';
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

    <!-- Search -->
    <div class="search-wrapper stagger-1" style="margin-bottom: 14px; position: relative;">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="dash-search-input" class="search-input" placeholder="Search beaches, cafes, falls..." autocomplete="off">
        <div id="dash-search-results" style="position: absolute; top: 60px; left: 0; right: 0; background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; max-height: 300px; overflow-y: auto; z-index: 100; box-shadow: 0 10px 30px rgba(0,0,0,0.8); display: none; padding: 8px;">
            <!-- Results injected here -->
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

    <!-- Trending Spots -->
    <div class="dash-section stagger-2">
        <div class="section-title">
            <h3>Trending Spots</h3>
            <a href="#" onclick="showToast('Loading all places...')">See All</a>
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
            <a href="#" onclick="navigateTo('itinerary')">Open Saved Trips</a>
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
            <a href="#" onclick="showToast('View all saved')">See All</a>
        </div>
        <div class="favorites-row" id="saved-places-container">
            <div style="padding: 20px; width: 100%; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading Saved Places...
            </div>
        </div>  
    </div>

    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3 id="recommended-title">Recommended For You</h3>
        </div>
        <div id="recommended-container">
            <div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading Recommendations...
            </div>
        </div>
    </div>

</div>

<script>
window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;
window.getDestImage = function(dest, width = 600) {
    if (window.AVAILABLE_MUNI_IMAGES && dest.name) {
        let munisToCheck = dest.municipality ? [dest.municipality.toUpperCase()] : Object.keys(window.AVAILABLE_MUNI_IMAGES);
        
        const dNorm = dest.name.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').trim();
        const dWords = dNorm.split(/\s+/).filter(w => w.length > 2);

        let bestMatch = null;
        let bestScore = 0;
        let bestMuni = null;

        for (let muni of munisToCheck) {
            const images = window.AVAILABLE_MUNI_IMAGES[muni];
            if (images && images.length > 0) {
                for (let img of images) {
                    const iNorm = img.replace(/\.(jpg|jpeg|png|webp|gif)$/i, '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ').trim();
                    
                    const dStr = dNorm.replace(/\s+/g, '');
                    const iStr = iNorm.replace(/\s+/g, '').replace(/[0-9]+$/, ''); 
                    
                    if (dStr === iStr) {
                        return encodeURI(`assets/img/municipalities/${muni}/${img}`);
                    }
                    
                    let score = 0;
                    if (dStr.includes(iStr) || iStr.includes(dStr)) {
                        score += 100; 
                    }
                    
                    const iWords = iNorm.split(/\s+/).filter(w => w.length > 2);
                    let common = 0;
                    for (let w of dWords) {
                        if (iWords.includes(w)) {
                            // Give less weight to municipality name to avoid false positives
                            if (w === muni.toLowerCase()) {
                                score += 1;
                            } else {
                                score += 10;
                            }
                            common++;
                        }
                    }
                    
                    if (common > 0) {
                        score += (common / Math.max(dWords.length, iWords.length)) * 5;
                    }

                    // Require at least one meaningful matching word or substring match
                    if (score > bestScore && score >= 10) { 
                        bestScore = score;
                        bestMatch = img;
                        bestMuni = muni;
                    } else if (score === bestScore && score >= 10) {
                        // Tie breaker: prefer the primary photo (usually has '1' in the filename)
                        // If current bestMatch has '3' or '2', and this one has '1', it will override it.
                        if (img.includes('1') || img.toLowerCase().includes('one')) {
                            bestMatch = img;
                            bestMuni = muni;
                        }
                    }
                }
            }
        }
        
        if (bestMatch) {
            return encodeURI(`assets/img/municipalities/${bestMuni}/${bestMatch}`);
        }
    }
    
    if (dest.image) {
        const backendUrl = window.backendUrl || 'http://localhost:8000';
        if (dest.image.startsWith('http')) return dest.image;
        if (dest.image.startsWith('uploads/')) return backendUrl + '/' + dest.image;
        return backendUrl + '/storage/' + dest.image;
    }
    return `https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=${width}`;
};

(async function dashboardInit() {
    const setTxt = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
    const setSrc = (id, src) => { const el = document.getElementById(id); if (el) el.src = src; };

    let backendUrl = 'http://localhost:8000';
    const token = localStorage.getItem('intan_elyu_token');
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}');

    // Instant render from cache
    if (user && user.name) {
        setTxt('dash-name', 'Hi, ' + user.name.split(' ')[0] + '! 👋');
        setSrc('dash-avatar', user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);
    }

    if (!token) return;

    try {
        const res = await fetch(backendUrl + '/api/tourist/dashboard', {
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
            return;
        }
        const data = await res.json();
        
        // Update global notifications if function exists
        if (typeof updateNotificationsDropdown === 'function') {
            updateNotificationsDropdown(data.announcements);
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
        if (data.myRank && document.getElementById('dash-stat-rank')) document.getElementById('dash-stat-rank').textContent = '#' + data.myRank;

        // Populate Saved Trips Preview
        const tripsContainer = document.getElementById('saved-trips-container');
        if (tripsContainer) {
            try {
                const itinRes = await fetch(backendUrl + '/api/tourist/itineraries', {
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
                });
                if (itinRes.ok) {
                    const itinData = await itinRes.json();
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
                                <div style="margin-bottom: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
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
                            `;
                        });
                        tripsContainer.innerHTML = tripsHtml;
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
                }
            } catch(e) {
                console.error('Failed to fetch itineraries for dashboard', e);
            }
        }


        // Populate Trending Spots (Top 3)
        const trendingContainer = document.getElementById('trending-container');
        if (trendingContainer) {
            trendingContainer.innerHTML = '';
            if (data.trending && data.trending.length > 0) {
                data.trending.forEach(dest => {
                    const img = window.getDestImage(dest, 600);
                    trendingContainer.innerHTML += `
                        <div class="fav-card" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
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
                    savedContainer.innerHTML += `
                        <div class="fav-card" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
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
                                <div style="display:flex; align-items:center; gap:5px;">
                                    <i class="fa-solid fa-star" style="color:#fbbf24; font-size:11px;"></i>
                                    <span style="font-size:12px; font-weight:700; color:#f8fafc;">${rating}</span>
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
    } catch(e) {
        console.error(e);
    }
})();

window.toggleFavorite = function(destId, element) {
    const token = localStorage.getItem('intan_elyu_token');
    let backendUrl = 'http://localhost:8000';
    
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
                        const backendUrl = 'http://localhost:8000';
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

</script>
