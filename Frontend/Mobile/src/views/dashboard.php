<!-- Dashboard View -->
<?php
$pageTitle = 'Discover La Union';
$activeTab = 'dashboard';
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
                <h2 class="profile-name" id="dash-name">Hi, there! 👋</h2>
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

    <!-- Search -->
    <div class="search-wrapper stagger-2">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" class="search-input" placeholder="Search beaches, cafes, falls...">
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
            <h3>My saved trips</h3>
            <a href="#" onclick="navigateTo('itinerary')">Open saved trips</a>
        </div>
        
        <div id="saved-trips-container">
            <div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px; background: rgba(255,255,255,0.02); border-radius: 15px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading saved trips...
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
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading saved places...
            </div>
        </div>  
    </div>

    <div class="dash-section stagger-3">
        <div class="section-title">
            <h3 id="recommended-title">Recommended For You</h3>
        </div>
        <div id="recommended-container">
            <div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.5); font-size: 14px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading recommendations...
            </div>
        </div>
    </div>

</div>

<script>
(async function dashboardInit() {
    let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';
    const token = localStorage.getItem('intan_elyu_token');
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}');

    // Instant render from cache
    if (user && user.name) {
        document.getElementById('dash-name').textContent = 'Hi, ' + user.name.split(' ')[0] + '! 👋';
        document.getElementById('dash-avatar').src =
            user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
    }

    if (!token) return;

    try {
        const res = await fetch(backendUrl + '/api/tourist/dashboard', {
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
        document.getElementById('dash-name').textContent = 'Hi, ' + firstName + '! 👋';
        document.getElementById('dash-title').textContent = 'Level ' + (u.level || 1) + ' Explorer';
        if (u.avatar) {
            document.getElementById('dash-avatar').src = u.avatar;
        } else {
            document.getElementById('dash-avatar').src =
                `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        }

        // XP Bar
        const xp = parseInt(u.xp) || 0;
        const level = parseInt(u.level) || 1;
        const xpPerLevel = 1000;
        const xpInLevel = xp % xpPerLevel;
        const xpPct = Math.min((xpInLevel / xpPerLevel) * 100, 100);
        document.getElementById('dash-level-label').textContent = 'Level ' + level + ' Progress';
        document.getElementById('dash-xp-value').textContent = xpInLevel + ' / ' + xpPerLevel + ' XP';
        document.getElementById('dash-xp-bar').style.width = xpPct + '%';

        // Stats
        document.getElementById('dash-stat-places').textContent = (data.stats && data.stats.placesVisited) ? data.stats.placesVisited : 0;
        document.getElementById('dash-stat-xp').textContent = xp.toLocaleString();
        if (data.myRank) document.getElementById('dash-stat-rank').textContent = '#' + data.myRank;

        // Populate Saved Trips Preview
        const tripsContainer = document.getElementById('saved-trips-container');
        if (tripsContainer) {
            const itinerary = data.activeItinerary || data.draftItinerary;
            if (itinerary) {
                let stopsHtml = '';
                if (itinerary.items && itinerary.items.length > 0) {
                    itinerary.items.forEach((item, index) => {
                        let dotClass = '';
                        let iconClass = '';
                        if (item.status === 'visited') {
                            dotClass = ''; iconClass = 'fa-check';
                        } else if (item.status === 'pending' && (!itinerary.items[index-1] || itinerary.items[index-1].status === 'visited')) { 
                            dotClass = 'active'; iconClass = 'fa-location-dot';
                        } else {
                            dotClass = ''; iconClass = 'fa-flag';
                        }
                        stopsHtml += `
                            <div class="itinerary-stop">
                                <div class="stop-dot ${dotClass}"><i class="fa-solid ${iconClass}"></i></div>
                                <div class="stop-info">
                                    <p class="stop-name">${item.destination ? item.destination.name : 'Unknown Location'}</p>
                                    <p class="stop-time">${item.destination ? (item.destination.location || 'La Union') : ''}</p>
                                </div>
                            </div>
                        `;
                    });
                }
                
                tripsContainer.innerHTML = `
                    <div class="itinerary-preview trip-card-dropdown" onclick="this.classList.toggle('expanded')">
                        <div class="itinerary-header" style="margin-bottom:0;">
                            <div style="display:flex; align-items:center; gap: 12px;">
                                <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-map-location-dot" style="color: #38bdf8; font-size: 20px;"></i>
                                </div>
                                <div>
                                    <span style="display:block; font-size:16px; font-weight:800; color:#ffffff; margin-bottom:2px;">${itinerary.title || 'My Itinerary'}</span>
                                    <span style="display:block; font-size:12px; color:#94a3b8; font-weight:500;">${itinerary.items ? itinerary.items.length : 0} Stops</span>
                                </div>
                            </div>
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-chevron-down toggle-icon" style="color: #94a3b8; font-size: 14px; transition: transform 0.3s;"></i>
                            </div>
                        </div>
                        
                        <div class="trip-locations-list" style="display: none; padding-top: 16px; margin-top: 16px; border-top: 1px solid rgba(255,255,255,0.08);">
                            ${stopsHtml || '<div style="color: #94a3b8; font-size: 13px;">No stops added yet.</div>'}
                        </div>
                    </div>
                `;
            } else {
                tripsContainer.innerHTML = `
                    <div style="padding: 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px; background: rgba(255,255,255,0.05); border-radius: 15px; margin: 0;">
                        <i class="fa-solid fa-route" style="font-size: 32px; color: rgba(255,255,255,0.3);"></i>
                        <div style="color: rgba(255,255,255,0.7); font-size: 14px; line-height: 1.4;">No active trips right now.</div>
                        <button onclick="navigateTo('itinerary')" style="background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 20px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; margin-top: 5px;">
                            <i class="fa-solid fa-plus"></i> Plan a Trip
                        </button>
                    </div>
                `;
            }
        }


        // Populate Trending Spots (Top 3)
        const trendingContainer = document.getElementById('trending-container');
        if (trendingContainer) {
            trendingContainer.innerHTML = '';
            if (data.trending && data.trending.length > 0) {
                data.trending.forEach(dest => {
                    const img = dest.image ? (dest.image.startsWith('http') ? dest.image : (dest.image.startsWith('uploads/') ? backendUrl + '/' + dest.image : backendUrl + '/storage/' + dest.image)) : 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?w=600';
                    trendingContainer.innerHTML += `
                        <div class="fav-card" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
                            <img src="${img}" alt="${dest.name}" onerror="this.src='https://images.unsplash.com/photo-1544644181-1484b3fdfc62?w=600'">
                            <div class="fav-card-overlay"><span class="fav-card-name">${dest.name}</span></div>
                            <i class="fa-solid fa-fire fav-heart" style="color: #ff9500; font-size: 14px;"></i>
                        </div>
                    `;
                });
            } else {
                trendingContainer.innerHTML = `
                    <div style="padding: 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px; background: rgba(255,255,255,0.05); border-radius: 15px; margin: 0 20px;">
                        <i class="fa-solid fa-fire-flame-curved" style="font-size: 32px; color: rgba(255,255,255,0.3);"></i>
                        <div style="color: rgba(255,255,255,0.7); font-size: 14px; line-height: 1.4;">No trending spots right now.</div>
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
                    const img = dest.image ? (dest.image.startsWith('http') ? dest.image : (dest.image.startsWith('uploads/') ? backendUrl + '/' + dest.image : backendUrl + '/storage/' + dest.image)) : 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?w=600';
                    savedContainer.innerHTML += `
                        <div class="fav-card" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
                            <img src="${img}" alt="${dest.name}" onerror="this.src='https://images.unsplash.com/photo-1544644181-1484b3fdfc62?w=600'">
                            <div class="fav-card-overlay"><span class="fav-card-name">${dest.name}</span></div>
                            <i class="fa-solid fa-heart fav-heart" style="color: #ff3b30;" onclick="event.stopPropagation(); window.toggleFavorite(${dest.id}, this)"></i>
                        </div>
                    `;
                });
            } else {
                savedContainer.innerHTML = `
                    <div style="padding: 20px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px; background: rgba(255,255,255,0.05); border-radius: 15px; margin: 0 20px;">
                        <i class="fa-solid fa-map-location-dot" style="font-size: 32px; color: rgba(255,255,255,0.3);"></i>
                        <div style="color: rgba(255,255,255,0.7); font-size: 14px; line-height: 1.4;">Go to the map to save some places!</div>
                        <button onclick="window.location.href='?view=map'" style="background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 20px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px; margin-top: 5px;">
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
                document.getElementById('recommended-title').textContent = data.timeLabel;
            }
            data.recommended.forEach(dest => {
                const img = dest.image ? (dest.image.startsWith('http') ? dest.image : (dest.image.startsWith('uploads/') ? backendUrl + '/' + dest.image : backendUrl + '/storage/' + dest.image)) : 'https://images.unsplash.com/photo-1622313628787-8e6f1f4ab986?w=150';
                const rating = dest.reviews_avg_rating ? parseFloat(dest.reviews_avg_rating).toFixed(1) : 'New';
                recContainer.innerHTML += `
                    <div class="recommended-item" onclick="window.viewDestinationOnMap(encodeURIComponent(JSON.stringify(${JSON.stringify(dest).replace(/"/g, '&quot;')})))">
                        <img src="${img}" alt="${dest.name}" class="recommended-img" onerror="this.src='https://images.unsplash.com/photo-1622313628787-8e6f1f4ab986?w=150'">
                        <div style="flex:1;">
                            <h4 style="margin:0 0 5px; font-size:16px; font-weight:800; letter-spacing:-0.3px;">${dest.name}</h4>
                            <p style="margin:0 0 7px; font-size:13px; color:#8E8E93;"><i class="fa-solid fa-location-dot" style="margin-right:4px; color:var(--accent-color);"></i>${dest.location || 'La Union'}</p>
                            <div style="display:flex; align-items:center; gap:5px;">
                                <i class="fa-solid fa-star" style="color:#FFD700; font-size:12px;"></i>
                                <span style="font-size:13px; font-weight:700;">${rating}</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:#C7C7CC;"></i>
                    </div>
                `;
            });
        }

        // Cache updated user
        localStorage.setItem('auth_user', JSON.stringify(u));
    } catch(e) {
        console.error(e);
    }
})();

window.toggleFavorite = async function(destId, element) {
    const token = localStorage.getItem('intan_elyu_token');
    let backendUrl = 'https://boc-cornell-rolled-delicious.trycloudflare.com';
    try {
        const res = await fetch(backendUrl + '/api/tourist/destinations/' + destId + '/favorite', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });
        const data = await res.json();
        if (data.status === 'added') {
            element.style.color = '#ff3b30';
            showToast('Added to Saved Places');
        } else {
            element.style.color = 'rgba(255,255,255,0.4)';
            showToast('Removed from Saved Places');
        }
    } catch(e) {
        showToast('Error updating favorite');
    }
};

window.viewDestinationOnMap = function(encodedDest) {
    try {
        const dest = JSON.parse(decodeURIComponent(encodedDest));
        localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
        window.location.href = '?view=map';
    } catch(e) { console.error('Failed to view destination:', e); }
};
</script>
