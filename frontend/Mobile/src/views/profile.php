<!-- Profile View -->
<?php
$pageTitle = 'My Profile';
$activeTab = 'profile';
?>

<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/testimony_modal.php'; ?>

<div class="profile-container has-header has-bottom-nav animate-slide-up" style="padding-bottom: 90px;">
    
    <!-- Profile Main Header Card -->
    <div class="profile-header stagger-1" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; border-radius: 24px; padding: 24px 20px; text-align: center; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25); margin-bottom: 20px;">
        <div class="profile-avatar-container" style="position: relative; display: inline-block; margin-bottom: 12px;">
            <img src="https://ui-avatars.com/api/?name=User&background=007AFF&color=fff&rounded=true&bold=true&size=128" alt="Profile" class="profile-avatar" id="profile-img" style="width: 100px; height: 100px; border-radius: 50%; border: none !important; outline: none !important; object-fit: cover; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);">
            <span id="profile-badge-icon" style="position: absolute; bottom: 2px; right: 2px; background: linear-gradient(135deg, #00f2fe, #0284c7); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; border: none !important; outline: none !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);" title="Explorer Level"><i class="fa-solid fa-shield-halved"></i></span>
        </div>

        <h2 class="profile-name" id="profile-name" style="margin: 0 0 4px 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">Loading...</h2>
        <p class="profile-email" id="profile-email" style="margin: 0; font-size: 13px; color: #ffffff; opacity: 0.95; font-weight: 500;">loading@example.com</p>
        
        <div id="profile-meta" style="font-size: 12px; color: #00f2fe; margin: 8px 0 0 0; display: none; flex-wrap: wrap; justify-content: center; gap: 12px; font-weight: 600;"></div>
        <p id="profile-bio-text" style="font-size: 13px; color: #ffffff; opacity: 0.95; margin: 10px auto 0 auto; max-width: 320px; font-style: italic; line-height: 1.4; display: none; background: rgba(255,255,255,0.12); padding: 8px 14px; border-radius: 12px; border: none !important; outline: none !important;"></p>
        
        <div id="profile-pref-chips" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 12px;"></div>

    </div>
    
    <!-- Stats Cards (XP, Visited, Rank) -->
    <div class="stats-container stagger-2" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px;">
        <div class="stat-card" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 18px; padding: 14px 10px; text-align: center; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
            <div class="stat-value" id="stat-xp" style="font-size: 20px; font-weight: 800; color: #00f2fe;">0</div>
            <div class="stat-label" style="font-size: 11px; font-weight: 700; color: #ffffff; opacity: 0.95; text-transform: uppercase; margin-top: 2px;">Total XP</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 18px; padding: 14px 10px; text-align: center; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
            <div class="stat-value" id="stat-places" style="font-size: 20px; font-weight: 800; color: #ffffff;">0</div>
            <div class="stat-label" style="font-size: 11px; font-weight: 700; color: #ffffff; opacity: 0.95; text-transform: uppercase; margin-top: 2px;">Visited</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 18px; padding: 14px 10px; text-align: center; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
            <div class="stat-value" id="stat-rank" style="font-size: 20px; font-weight: 800; color: #fbbf24;">—</div>
            <div class="stat-label" style="font-size: 11px; font-weight: 700; color: #ffffff; opacity: 0.95; text-transform: uppercase; margin-top: 2px;">Leaderboard</div>
        </div>
    </div>

    <!-- Explorer Level Progress Card -->
    <div class="stagger-2" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; border-radius: 20px; padding: 18px; margin-bottom: 20px; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-compass" style="color: #00f2fe; font-size: 16px;"></i>
                <span id="explorer-level-title" style="font-size: 14px; font-weight: 800; color: #ffffff;">Level 1 Explorer</span>
            </div>
            <span id="explorer-xp-text" style="font-size: 12px; font-weight: 800; color: #00f2fe;">0 / 1000 XP</span>
        </div>
        <div style="background: rgba(255,255,255,0.14); height: 10px; border-radius: 100px; overflow: hidden; position: relative;">
            <div id="explorer-xp-bar" style="background: linear-gradient(90deg, #00f2fe, #0284c7); height: 100%; width: 0%; border-radius: 100px; transition: width 0.5s ease;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; color: #ffffff; opacity: 0.9; font-weight: 600;">
            <span>Next Level Goal</span>
            <span id="explorer-xp-pct">0%</span>
        </div>
    </div>

    <!-- Trip History -->
    <div class="stagger-3" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; margin-left: 4px;">
        <h3 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-route" style="color: #00f2fe;"></i> Trip History
        </h3>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span id="trip-history-count-badge" style="font-size: 11px; font-weight: 800; background: rgba(0, 242, 254, 0.2); color: #00f2fe; padding: 3px 10px; border-radius: 100px; border: none !important; outline: none !important;">0 Completed</span>
            <button onclick="window.openFullHistoryModal()" style="background: rgba(255, 255, 255, 0.16); border: none !important; outline: none !important; color: #ffffff; font-size: 11px; font-weight: 800; cursor: pointer; padding: 4px 12px; border-radius: 100px; display: flex; align-items: center; gap: 4px; transition: all 0.2s ease;">
                View All <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i>
            </button>
        </div>
    </div>
    <div id="trip-history-container" class="stagger-3" style="margin-bottom: 24px;">
        <div id="trip-history-list">
            <div style="text-align:center; padding:20px; color:#ffffff; opacity:0.95; font-size:13px; font-weight:600; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:20px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">Loading history...</div>
        </div>
    </div>
    
    <!-- Points & Rewards -->
    <h3 class="stagger-3" style="font-size: 16px; font-weight: 800; color: #ffffff; margin-bottom: 12px; margin-left: 4px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-gift" style="color: #00f2fe;"></i> Points & Rewards
    </h3>
    
    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:20px; padding:20px; margin-bottom:24px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);" class="stagger-3">
        <!-- Display balance -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.15); padding-bottom:16px;">
            <div style="text-align: left;">
                <h4 style="margin:0 0 4px 0; font-size:12px; color:#ffffff; opacity:0.9; text-transform:uppercase; letter-spacing:0.5px;">Claimable Points</h4>
                <div style="display:flex; align-items:baseline; gap:6px;">
                    <span id="profile-points-val" style="font-size:32px; font-weight:800; color:#ffffff; letter-spacing:-1px;">--</span>
                    <span style="font-size:14px; font-weight:700; color:#ffffff; opacity:0.8;">PTS</span>
                </div>
            </div>
            <button onclick="navigateTo('puzzles')" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; color:#ffffff; padding:8px 16px; border-radius:12px; font-weight:800; font-size:12px; cursor:pointer; box-shadow: none !important;">
                <i class="fa-solid fa-gamepad"></i> Play & Earn
            </button>
        </div>

        <!-- Catalog list -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h5 style="margin:0; font-size:14px; font-weight:800; color:#fff; text-align: left;">Redeem Rewards</h5>
            <a href="#" onclick="navigateTo('discount'); return false;" style="font-size:11px; font-weight:800; color:#00f2fe; text-decoration:none; display:flex; align-items:center; gap:4px;">
                View All Deals <i class="fa-solid fa-arrow-right" style="font-size:9px;"></i>
            </a>
        </div>
        <div id="profile-rewards-catalog" style="display:flex; flex-direction:column; gap:10px; margin-bottom:24px;">
            <div style="text-align:center; padding:12px; color:#ffffff; opacity:0.75; font-size:12px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Loading available rewards...
            </div>
        </div>

        <!-- Active Claimed Vouchers -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h5 style="margin:0; font-size:14px; font-weight:800; color:#fff; text-align: left;">Active Vouchers</h5>
            <span id="active-vouchers-count" style="font-size:10px; font-weight:800; background:rgba(0, 242, 254, 0.2); border:none !important; outline:none !important; color:#00f2fe; padding:3px 10px; border-radius:100px;">0 Active</span>
        </div>
        <div id="vouchers-list" style="display:flex; flex-direction:column; gap:10px;">
            <div style="font-size:12px; color:#ffffff; opacity:0.85; text-align:center; padding:16px; background:rgba(255,255,255,0.08); border:none !important; outline:none !important; border-radius:14px;">No redeemed vouchers yet.</div>
        </div>
    </div>
    
    <!-- Account Settings -->
    <h3 class="stagger-3" style="font-size: 16px; font-weight: 800; color: #ffffff; margin-bottom: 12px; margin-left: 4px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-user-gear" style="color: #00f2fe;"></i> Account Settings
    </h3>
    
    <div class="settings-group stagger-3" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 20px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
        <a href="javascript:void(0);" class="settings-item" onclick="event.preventDefault(); window.scrollTo(0, 0); navigateTo('edit_profile'); return false;">
            <div class="settings-icon" style="background: #007AFF;"><i class="fa-solid fa-user-pen"></i></div>
            <div class="settings-text">Edit Personal Information</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="navigateTo('settings'); return false;">
            <div class="settings-icon" style="background: #8e8e93;"><i class="fa-solid fa-gear"></i></div>
            <div class="settings-text">App Preferences & Settings</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="navigateTo('help'); return false;">
            <div class="settings-icon" style="background: #34C759;"><i class="fa-solid fa-circle-question"></i></div>
            <div class="settings-text">Help & Support Center</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="handleLogout(event)">
            <div class="settings-icon" style="background: #FF3B30;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="settings-text" style="color: #FF3B30;">Log Out</div>
        </a>
    </div>

</div>

<script>
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

    async function fetchProfileData(force = false) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const cacheKey = 'profile_data_' + token.substring(0, 10);
        const shouldForce = force || Boolean(window.profileNeedsRefresh);
        if (window.profileNeedsRefresh) {
            window.profileNeedsRefresh = false;
            localStorage.removeItem(cacheKey);
        }

        await window.useCache(
            cacheKey,
            async () => {
                const response = await fetch(backendUrl + '/api/tourist/profile', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (!response.ok) throw new Error("Failed to fetch profile");
                return await response.json();
            },
            (data) => {
                if (!data) return;
                const u = data.user || {};
                const elXp = document.getElementById('stat-xp');
                const elPlaces = document.getElementById('stat-places');
                const elRank = document.getElementById('stat-rank');
                const elName = document.getElementById('profile-name');
                const elEmail = document.getElementById('profile-email');
                const elImg = document.getElementById('profile-img');

                const xp = parseInt(u.xp) || 0;
                const level = Math.floor(xp / 1000) + 1;
                const xpInLevel = xp % 1000;
                const xpPct = Math.min(Math.round((xpInLevel / 1000) * 100), 100);

                if (elXp) elXp.textContent = xp.toLocaleString();
                if (elPlaces) elPlaces.textContent = data.places_visited || 0;
                if (elRank && data.my_rank) elRank.textContent = '#' + data.my_rank;
                if (elName) elName.textContent = u.name || 'Explorer';
                if (elEmail) elEmail.textContent = u.email || '';
                if (elImg && u.avatar) {
                    elImg.src = window.getFullImageUrl(u.avatar);
                }

                // Level Progress
                const elLevelTitle = document.getElementById('explorer-level-title');
                const elXpText = document.getElementById('explorer-xp-text');
                const elXpBar = document.getElementById('explorer-xp-bar');
                const elXpPct = document.getElementById('explorer-xp-pct');

                if (elLevelTitle) elLevelTitle.textContent = `Level ${level} Explorer`;
                if (elXpText) elXpText.textContent = `${xpInLevel} / 1000 XP`;
                if (elXpBar) elXpBar.style.width = `${xpPct}%`;
                if (elXpPct) elXpPct.textContent = `${xpPct}%`;

                // Render Badges (Unlocked & Locked)
                const badges = data.badges || [];
                window._cachedMasterBadges = badges;
                const badgesGrid = document.getElementById('profile-badges-grid');
                const badgeLabel = document.getElementById('profile-badge-count-label');

                if (badgeLabel) {
                    badgeLabel.textContent = `${data.unlocked_badge_count || 0} / ${data.total_badge_count || badges.length} Unlocked`;
                }

                if (badgesGrid && badges.length) {
                    // Show locked badges first so user sees available upcoming badges
                    const lockedFirst = [...badges].sort((a, b) => (a.is_unlocked ? 1 : 0) - (b.is_unlocked ? 1 : 0));
                    const featuredLocked = lockedFirst.slice(0, 3);
                    badgesGrid.innerHTML = featuredLocked.map(b => {
                        const safeName = (b.name || '').replace(/'/g, "\\'");
                        const safeDesc = (b.description || 'Complete activities in La Union to unlock this badge.').replace(/'/g, "\\'");
                        const clickAction = `onclick="openBadgeModal('${safeName}', '${safeDesc}', ${b.is_unlocked ? 'true' : 'false'}, '${b.category || 'Badge'}', '${b.icon || '🏅'}')"`;

                        const badgeIcons = {
                            'Beach Chiller': '<i class="fa-solid fa-umbrella-beach"></i>',
                            'City Express': '<i class="fa-solid fa-city"></i>',
                            'Sunset Chaser': '<i class="fa-solid fa-sun"></i>',
                            'Foodie Explorer': '<i class="fa-solid fa-utensils"></i>',
                            'Adrenaline Chaser': '<i class="fa-solid fa-person-hiking"></i>',
                            'Heritage Guardian': '<i class="fa-solid fa-landmark"></i>',
                            'Nature Seeker': '<i class="fa-solid fa-water"></i>',
                            'Wave Rider': '<i class="fa-solid fa-water-ladder"></i>',
                            'First Step': '<i class="fa-solid fa-star"></i>',
                            'Globe Trotter': '<i class="fa-solid fa-globe"></i>',
                            'Master Voyager': '<i class="fa-solid fa-crown"></i>',
                            'Pioneer Explorer': '<i class="fa-solid fa-flag"></i>',
                            'Local Voice': '<i class="fa-solid fa-comments"></i>',
                        };
                        const displayIcon = badgeIcons[b.name] || (b.is_unlocked ? '<i class="fa-solid fa-award"></i>' : '<i class="fa-solid fa-lock"></i>');

                        if (b.is_unlocked) {
                            return `
                            <div ${clickAction} style="background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.3); border-radius: 16px; padding: 12px 8px; text-align: center; cursor: pointer; transition: transform 0.2s;" title="${b.description}">
                                <div style="font-size: 22px; margin-bottom: 4px; color: #fbbf24;">${displayIcon}</div>
                                <div style="font-size: 11px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${b.name}</div>
                                <div style="font-size: 9px; color: #fbbf24; margin-top: 2px; font-weight: 700;"><i class="fa-solid fa-circle-check" style="font-size:8.5px; margin-right:2px;"></i> Unlocked</div>
                            </div>`;
                        } else {
                            return `
                            <div ${clickAction} style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1); border-radius: 16px; padding: 12px 8px; text-align: center; opacity: 0.7; cursor: pointer; transition: transform 0.2s;" title="${b.description}">
                                <div style="font-size: 20px; margin-bottom: 4px; color: rgba(255,255,255,0.4);"><i class="fa-solid fa-lock"></i></div>
                                <div style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${b.name}</div>
                                <div style="font-size: 9px; color: #f87171; margin-top: 2px; font-weight: 700;"><i class="fa-solid fa-lock" style="font-size:8.5px; margin-right:2px;"></i> Locked</div>
                            </div>`;
                        }
                    }).join('');
                }

                // Extra Meta (Phone & Home Location)
                const elMeta = document.getElementById('profile-meta');
                if (elMeta) {
                    let metaParts = [];
                    if (u.home_location) metaParts.push(`<i class="fa-solid fa-location-dot"></i> ${u.home_location}`);
                    if (u.phone) metaParts.push(`<i class="fa-solid fa-phone"></i> ${u.phone}`);
                    if (metaParts.length > 0) {
                        elMeta.innerHTML = metaParts.join(' &nbsp;•&nbsp; ');
                        elMeta.style.display = 'flex';
                    } else {
                        elMeta.style.display = 'none';
                    }
                }

                // Bio
                const elBio = document.getElementById('profile-bio-text');
                if (elBio) {
                    if (u.bio) {
                        elBio.textContent = `"${u.bio}"`;
                        elBio.style.display = 'block';
                    } else {
                        elBio.style.display = 'none';
                    }
                }

                // Preferences Chips
                const elChips = document.getElementById('profile-pref-chips');
                if (elChips) {
                    if (u.travel_preferences) {
                        const prefs = u.travel_preferences.split(',').map(s => s.trim()).filter(Boolean);
                        elChips.innerHTML = prefs.map(p => `
                            <span style="background:rgba(255,255,255,0.16); border:none !important; outline:none !important; color:#ffffff; padding:5px 14px; border-radius:100px; font-size:11px; font-weight:700;">${p}</span>
                        `).join('');
                        elChips.style.display = 'flex';
                    } else {
                        elChips.innerHTML = '';
                        elChips.style.display = 'none';
                    }
                }

                // Avatar
                if (elImg) {
                    let avatarUrl = u.avatar;
                    if (avatarUrl) {
                        if (avatarUrl.includes('localhost:3000') || avatarUrl.includes('127.0.0.1:3000')) {
                            avatarUrl = avatarUrl.replace(/http:\/\/(localhost|127\.0\.0\.1):3000/, window.backendUrl || 'http://localhost:8000');
                        }
                        if (!avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:') && !avatarUrl.startsWith('blob:')) {
                            let b = (window.backendUrl || '').replace(/\/+$/, '');
                            avatarUrl = b + '/' + avatarUrl.replace(/^\//, '');
                        }
                    } else {
                        avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                    }

                    let fallbackAvatar = (window.backendUrl || '').replace(/\/+$/, '') + '/api/image/' + (u.avatar ? u.avatar.replace(/^\//, '') : '');
                    elImg.onerror = function() {
                        if (u.avatar && this.src !== fallbackAvatar) {
                            this.src = fallbackAvatar;
                        } else {
                            this.onerror = null;
                            this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                        }
                    };

                    elImg.src = avatarUrl;
                }

                // Trip History
                window._cachedCompletedTrips = data.completed_trips || [];
                const historyList = document.getElementById('trip-history-list');
                const historyBadge = document.getElementById('trip-history-count-badge');
                if (historyList) {
                    if (!data.completed_trips || data.completed_trips.length === 0) {
                        if (historyBadge) historyBadge.textContent = '0 Completed';
                        historyList.innerHTML = '<div style="text-align:center; padding:20px; color:#ffffff; opacity:0.95; font-size:13px; font-weight:600; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:20px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">No completed trips yet. Start exploring!</div>';
                    } else {
                        if (historyBadge) historyBadge.textContent = `${data.completed_trips.length} Completed`;
                        let html = '';
                        // Limit main profile view to maximum 3 completed trips
                        const displayTrips = data.completed_trips.slice(0, 3);
                        displayTrips.forEach(trip => {
                            const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date';
                            const count = Array.isArray(trip.items) ? trip.items.length : (parseInt(trip.destinations_visited) || parseInt(trip.items) || 0);
                            const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

                            html += `
                            <div onclick="window.showTripDetailsModal('${trip.id}')" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none !important; outline: none !important; border-radius: 20px; padding: 16px 18px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                                        <strong style="color: #ffffff; font-size: 14px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${trip.title || 'Completed Trip'}</strong>
                                    </div>
                                    <div style="font-size: 11px; color: #ffffff; opacity: 0.95; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        <span><i class="fa-regular fa-calendar" style="color: #00f2fe; margin-right: 3px;"></i>${date}</span>
                                        <span>&bull;</span>
                                        <span><i class="fa-solid fa-coins" style="color: #fbbf24; margin-right: 3px;"></i>₱${cost}</span>
                                        <span>&bull;</span>
                                        <span><i class="fa-solid fa-location-dot" style="color: #00f2fe; margin-right: 3px;"></i>${count} Visited</span>
                                    </div>
                                </div>
                                <span style="color: #ffffff; font-weight: 800; font-size: 11px; background: rgba(52, 199, 89, 0.25); border: none !important; outline: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap; flex-shrink: 0;">
                                    <i class="fa-solid fa-check" style="margin-right: 3px; color:#34c759;"></i>Done
                                </span>
                            </div>`;
                        });
                        historyList.innerHTML = html;
                    }
                }
            },
            shouldForce,
            60000 // 1 minute TTL
        );
    }

    // Fetch Points & Dynamic Vouchers Catalog & Active Redemptions
    async function fetchPointsAndVouchers() {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        try {
            // 1. Fetch balance & claimed vouchers
            const r = await fetch(backendUrl + '/api/tourist/points/balance', {
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + token
                }
            });
            const d = await r.json();
            if (d.status === 'success') {
                window._userPointsBalance = d.points || 0;
                const ptsVal = document.getElementById('profile-points-val');
                if (ptsVal) ptsVal.textContent = (d.points || 0).toLocaleString();
                
                // Render Active Vouchers
                const list = document.getElementById('vouchers-list');
                const badge = document.getElementById('active-vouchers-count');
                if (list) {
                    if (d.vouchers && d.vouchers.length > 0) {
                        if (badge) badge.textContent = `${d.vouchers.length} Active`;
                        let html = '';
                        d.vouchers.forEach(v => {
                            const badgeColor = v.status === 'active' ? '#34c759' : '#8e8e93';
                            const voucherTitle = v.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (v.type === 'environmental_fee' ? 'Waived Environmental Fee' : (v.type || 'Tourist Voucher'));
                            const safeCode = (v.voucher_code || '').replace(/'/g, "\\'");
                            
                            html += `
                            <div style="background: rgba(255, 255, 255, 0.08); border: none !important; outline: none !important; padding: 14px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                <div style="text-align: left; flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                        <i class="fa-solid fa-ticket" style="color: #38bdf8; font-size: 13px;"></i>
                                        <span style="font-size: 13px; font-weight: 800; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${voucherTitle}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <code style="font-size: 13px; font-weight: 900; color: #38bdf8; letter-spacing: 0.5px; background: rgba(56,189,248,0.15); border: none !important; outline: none !important; padding: 3px 8px; border-radius: 8px;">${v.voucher_code}</code>
                                        <button type="button" onclick="navigator.clipboard.writeText('${safeCode}'); if(typeof showToast==='function') showToast('Voucher code copied!');" style="background: rgba(255,255,255,0.12); border: none !important; outline: none !important; color: #ffffff; padding: 4px 8px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer;">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: ${badgeColor}; background: ${v.status === 'active' ? 'rgba(52,199,89,0.25)' : 'rgba(255,255,255,0.1)'}; border: none !important; outline: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap;">
                                    ${v.status || 'Active'}
                                </span>
                            </div>`;
                        });
                        list.innerHTML = html;
                    } else {
                        if (badge) badge.textContent = '0 Active';
                        list.innerHTML = '<div style="font-size:12px; color:rgba(255,255,255,0.7); text-align:center; padding:16px; background:rgba(255,255,255,0.08); border:none !important; outline:none !important; border-radius:14px;">No redeemed vouchers yet.</div>';
                    }
                }
            }

            // 2. Fetch active catalog rewards
            const catalogEl = document.getElementById('profile-rewards-catalog');
            if (catalogEl) {
                const resVouchers = await fetch(backendUrl + '/api/vouchers', {
                    headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
                });
                if (resVouchers.ok) {
                    const vouchersPayload = await resVouchers.json();
                    if (vouchersPayload.status === 'success' && Array.isArray(vouchersPayload.data) && vouchersPayload.data.length > 0) {
                        const topVouchers = vouchersPayload.data.slice(0, 3);
                        window._profileVouchersList = topVouchers;
                        catalogEl.innerHTML = topVouchers.map((v, idx) => {
                            return `
                            <div onclick="window.showRewardDetailsModal(${idx})" style="display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.08); border:none !important; outline:none !important; padding:12px 14px; border-radius:16px; gap:12px; transition:transform 0.15s ease, background 0.15s ease; cursor:pointer;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                                <div style="display:flex; align-items:center; gap:10px; min-width:0; text-align:left; flex:1;">
                                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.14); border:none !important; outline:none !important; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; box-shadow:none !important;">
                                        <img src="${v.image || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png'}" alt="${v.title}" style="width:100%; height:100%; object-fit:contain; padding:3px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
                                    </div>
                                    <div style="min-width:0; flex:1;">
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <strong style="display:block; font-size:13px; font-weight:800; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v.title}</strong>
                                            <span style="font-size:9px; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.2); padding:2px 7px; border-radius:6px; border:none !important; flex-shrink:0;">${v.badge}</span>
                                        </div>
                                        <span style="font-size:11px; color:rgba(226,232,240,0.7); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v.partner}</span>
                                    </div>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); window.showRewardDetailsModal(${idx})" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; border:none !important; outline:none !important; padding:8px 14px; border-radius:10px; font-size:11px; font-weight:800; cursor:pointer; flex-shrink:0; box-shadow:none !important; white-space:nowrap;">
                                    ${v.pointsCost} PTS
                                </button>
                            </div>`;
                        }).join('');
                    } else {
                        // Default built-in rewards fallback
                        const fallbackVouchers = [
                            {
                                id: 'pasalubong_discount',
                                title: '₱50 Pasalubong Discount',
                                partner: 'Pasalubong Center, La Union',
                                badge: '₱50 OFF',
                                description: 'Enjoy a ₱50 discount on authentic local pasalubong and souvenirs made by Elyu artisans.',
                                pointsCost: 100,
                                image: 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png'
                            },
                            {
                                id: 'environmental_fee',
                                title: 'Waived Environmental Fee',
                                partner: 'Municipality of La Union',
                                badge: 'FREE ENTRY',
                                description: 'Waive standard municipality environmental entrance fee on your next eco-tourism visit.',
                                pointsCost: 150,
                                image: 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png'
                            }
                        ];
                        window._profileVouchersList = fallbackVouchers;
                        catalogEl.innerHTML = fallbackVouchers.map((v, idx) => `
                            <div onclick="window.showRewardDetailsModal(${idx})" style="display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.08); border:none !important; outline:none !important; padding:12px 14px; border-radius:16px; margin-bottom:8px; cursor:pointer; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                                <div style="text-align: left;">
                                    <strong style="display:block; font-size:13px; color:#fff;">${v.title}</strong>
                                    <span style="font-size:11px; color:rgba(255,255,255,0.7);">${v.partner}</span>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); window.showRewardDetailsModal(${idx})" style="background:linear-gradient(135deg, #00f2fe, #0284c7); color:#fff; border:none !important; outline:none !important; padding:8px 12px; border-radius:10px; font-size:11px; font-weight:800; cursor:pointer; box-shadow:none !important;">
                                    ${v.pointsCost} PTS
                                </button>
                            </div>
                        `).join('');
                    }
                }
            }
        } catch (e) {
            console.error("Points fetch error:", e);
        }
    }

    window.redeemAdminVoucher = async function(voucherId, cost, title) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const currentPts = window._userPointsBalance || 0;
        if (currentPts < cost) {
            if (typeof showToast === 'function') showToast(`Insufficient points. You need ${cost} PTS (Balance: ${currentPts} PTS).`);
            return;
        }

        if (!confirm(`Redeem '${title}' for ${cost} PTS?`)) return;

        try {
            const response = await fetch(backendUrl + '/api/tourist/points/redeem-voucher', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ voucher_id: voucherId })
            });

            const data = await response.json();
            if (response.ok && data.status === 'success') {
                if (typeof showToast === 'function') showToast("Voucher claimed successfully!");
                if (window.confetti) {
                    window.confetti({ particleCount: 80, spread: 60, origin: { y: 0.6 } });
                }
                fetchPointsAndVouchers();
            } else {
                if (typeof showToast === 'function') showToast(data.message || "Failed to redeem voucher.");
            }
        } catch (error) {
            console.error("Redemption error:", error);
            if (typeof showToast === 'function') showToast("Network error. Please try again.");
        }
    };

    window.redeemReward = async function(type, cost) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const currentPts = window._userPointsBalance || 0;
        if (currentPts < cost) {
            if (typeof showToast === 'function') showToast(`Insufficient points. You need ${cost} PTS (Balance: ${currentPts} PTS).`);
            return;
        }

        if (!confirm(`Are you sure you want to redeem this reward for ${cost} Points?`)) return;

        try {
            const response = await fetch(backendUrl + '/api/tourist/points/redeem', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ type: type })
            });

            const data = await response.json();
            if (response.ok) {
                if (typeof showToast === 'function') showToast("Reward redeemed successfully!");
                if (window.confetti) {
                    window.confetti({ particleCount: 80, spread: 60, origin: { y: 0.6 } });
                }
                fetchPointsAndVouchers();
            } else {
                if (typeof showToast === 'function') showToast(data.message || "Failed to redeem reward.");
            }
        } catch (error) {
            console.error("Redemption error:", error);
            if (typeof showToast === 'function') showToast("Network error.");
        }
    };

    // Export function to update points display globally
    window.updateProfilePointsDisplay = function(points) {
        const ptsVal = document.getElementById('profile-points-val');
        if (ptsVal) ptsVal.textContent = points;
    };

    window.showRewardDetailsModal = function(idxOrVoucher) {
        let voucher = null;
        if (typeof idxOrVoucher === 'number' && window._profileVouchersList) {
            voucher = window._profileVouchersList[idxOrVoucher];
        } else if (typeof idxOrVoucher === 'object') {
            voucher = idxOrVoucher;
        }
        if (!voucher) return;

        const modal = document.getElementById('reward-details-modal');
        if (!modal) return;

        const badge = document.getElementById('reward-modal-badge');
        const img = document.getElementById('reward-modal-img');
        const title = document.getElementById('reward-modal-title');
        const partner = document.getElementById('reward-modal-partner-name');
        const desc = document.getElementById('reward-modal-desc');
        const costText = document.getElementById('reward-modal-cost-text');
        const redeemBtn = document.getElementById('reward-modal-redeem-btn');

        if (badge) badge.textContent = voucher.badge || 'PROMO';
        if (img) img.src = voucher.image || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png';
        if (title) title.textContent = voucher.title || 'Exclusive Reward';
        if (partner) partner.textContent = voucher.partner || voucher.merchant || 'La Union Partner';
        if (desc) desc.textContent = voucher.description || 'Redeem this voucher with your claimable points to enjoy discounts at this partner establishment.';
        if (costText) costText.textContent = `${voucher.pointsCost} PTS`;

        if (redeemBtn) {
            redeemBtn.onclick = function() {
                window.closeRewardDetailsModal();
                if (typeof voucher.id === 'string' && voucher.id.includes('_')) {
                    redeemReward(voucher.id, voucher.pointsCost);
                } else {
                    window.redeemAdminVoucher(voucher.id, voucher.pointsCost, voucher.title);
                }
            };
        }

        modal.style.display = 'flex';
        void modal.offsetHeight;
        modal.style.opacity = '1';
        const card = modal.querySelector('div');
        if (card) card.style.transform = 'scale(1)';
    };

    window.closeRewardDetailsModal = function() {
        const modal = document.getElementById('reward-details-modal');
        if (!modal) return;
        modal.style.opacity = '0';
        const card = modal.querySelector('div');
        if (card) card.style.transform = 'scale(0.88)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 220);
    };

    window.openFullHistoryModal = function() {
        const modal = document.getElementById('full-history-modal');
        const container = document.getElementById('full-history-list');
        if (!modal || !container) return;

        const trips = window._cachedCompletedTrips || [];
        if (trips.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:24px; color:rgba(148, 163, 184, 0.8); font-size:13px; background:rgba(37, 99, 235, 0.1); border:1px solid rgba(56, 189, 248, 0.2); border-radius:16px;">No completed trips found in your history.</div>';
        } else {
            let html = '';
            trips.forEach((trip, idx) => {
                const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set';
                const count = Array.isArray(trip.items) ? trip.items.length : (parseInt(trip.destinations_visited) || parseInt(trip.items) || 0);
                const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

                html += `
                <div onclick="window.showTripDetailsModal('${trip.id}')" style="background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 18px; padding: 16px; margin-bottom: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.borderColor='rgba(56,189,248,0.5)'" onmouseout="this.style.borderColor='rgba(56,189,248,0.25)'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div>
                            <div style="font-size:10px; font-weight:800; color:#38bdf8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">Trip #${trips.length - idx}</div>
                            <strong style="color: #f8fafc; font-size: 16px; font-weight: 800;">${trip.title || 'Completed Trip'}</strong>
                        </div>
                        <span style="color: #34c759; font-weight: 800; font-size: 11px; background: rgba(52, 199, 89, 0.15); border: 1px solid rgba(52, 199, 89, 0.3); padding: 4px 10px; border-radius: 100px; white-space: nowrap;">
                            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i>Completed
                        </span>
                    </div>
                    <div style="font-size: 12px; color: rgba(226, 232, 240, 0.85); display: flex; align-items: center; gap: 10px; flex-wrap: wrap; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); padding: 8px 12px; border-radius: 12px;">
                        <span><i class="fa-regular fa-calendar" style="color: #38bdf8; margin-right: 4px;"></i>${date}</span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-coins" style="color: #f59e0b; margin-right: 4px;"></i>₱${cost}</span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-location-dot" style="color: #34c759; margin-right: 4px;"></i>${count} Destinations Visited</span>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        modal.style.display = 'flex';
    };

    window.closeFullHistoryModal = function() {
        const modal = document.getElementById('full-history-modal');
        if (modal) modal.style.display = 'none';
    };

    window.showTripDetailsModal = function(tripId) {
        const trips = window._cachedCompletedTrips || [];
        const trip = trips.find(t => t.id == tripId) || trips[0];
        if (!trip) return;

        document.getElementById('trip-detail-title').textContent = trip.title || 'Completed Trip';
        document.getElementById('trip-detail-date').innerHTML = `<i class="fa-regular fa-calendar" style="color:#38bdf8; margin-right:4px;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set'}`;
        const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('trip-detail-cost').innerHTML = `<i class="fa-solid fa-coins" style="color:#f59e0b; margin-right:4px;"></i>₱${cost}`;
        
        const items = trip.items || [];
        const visitedCount = items.filter(i => i.is_visited).length || items.length;
        document.getElementById('trip-detail-count').innerHTML = `<i class="fa-solid fa-location-dot" style="margin-right:4px;"></i>${visitedCount} Visited`;

        let destHtml = '';
        if (items.length === 0) {
            destHtml = '<div style="text-align:center; padding:16px; color:rgba(148,163,184,0.8); font-size:12px;">No destination details found for this trip.</div>';
        } else {
            items.forEach((item, idx) => {
                const dest = item.destination;
                const destName = dest ? dest.name : (item.destination_name || 'Destination ' + (idx + 1));
                const fee = dest ? (dest.entrance_fee && parseFloat(dest.entrance_fee) > 0 ? '₱' + parseFloat(dest.entrance_fee).toFixed(2) : 'Free Entrance') : 'Visited';
                const spotId = item.tourist_spot_id || (dest ? dest.id : item.id);

                destHtml += `
                <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:14px; margin-bottom:8px;">
                    <div style="width:32px; height:32px; border-radius:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:900; font-size:13px; color:#fff;">${idx + 1}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${destName}</div>
                        <div style="font-size:11px; color:rgba(148,163,184,0.8); font-weight:600;">${fee}</div>
                    </div>
                    ${spotId ? `<button type="button" onclick="event.stopPropagation(); window.openWriteTestimonyModal('${spotId}', this)" style="background: linear-gradient(135deg, #38bdf8, #2563eb); border: none; color: #ffffff; padding: 6px 14px; border-radius: 100px; font-weight: 800; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(56,189,248,0.3); flex-shrink: 0;"><i class="fa-solid fa-pen" style="font-size: 10px;"></i> Review</button>` : ''}
                </div>`;
            });
        }
        document.getElementById('trip-detail-destinations-list').innerHTML = destHtml;

        const modal = document.getElementById('trip-details-modal');
        if (modal) modal.style.display = 'flex';
    };

    window.closeTripDetailsModal = function() {
        const modal = document.getElementById('trip-details-modal');
        if (modal) modal.style.display = 'none';
    };

    fetchProfileData();
    fetchPointsAndVouchers();
</script>

<!-- Full Trip History Modal -->
<div id="full-history-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center; padding:20px;">
    <div style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:1.5px solid rgba(255, 255, 255, 0.25); border-radius:24px; padding:24px 20px; width:100%; max-width:400px; max-height:82vh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(10,25,60,0.6); text-align:left;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.12);">
            <h3 style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-clock-rotate-left" style="color:#38bdf8;"></i> Trip History
            </h3>
            <button onclick="window.closeFullHistoryModal()" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.18); color:#e2e8f0; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="full-history-list" style="flex:1; overflow-y:auto; padding-right:4px;">
            <div style="text-align:center; padding:20px; color:rgba(148, 163, 184, 0.8); font-size:13px;">Loading history...</div>
        </div>
    </div>
</div>

<!-- Completed Trip Details Modal -->
<div id="trip-details-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:2000000; justify-content:center; align-items:center; padding:20px;">
    <div style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:1.5px solid rgba(255, 255, 255, 0.25); border-radius:24px; padding:24px 20px; width:100%; max-width:400px; max-height:82vh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(10,25,60,0.6); text-align:left;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.08);">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:12px; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); display:flex; align-items:center; justify-content:center; color:#10b981; font-size:16px;">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <div>
                    <div style="font-size:10px; font-weight:800; color:#34c759; text-transform:uppercase; letter-spacing:0.5px;">Finished Trip Details</div>
                    <h3 id="trip-detail-title" style="margin:0; color:#ffffff; font-size:17px; font-weight:800;">Trip Details</h3>
                </div>
            </div>
            <button onclick="window.closeTripDetailsModal()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); color:#e2e8f0; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
            <span id="trip-detail-date" style="font-size:11px; color:rgba(226,232,240,0.85); background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); padding:5px 10px; border-radius:100px; font-weight:600;"><i class="fa-regular fa-calendar" style="color:#38bdf8; margin-right:4px;"></i>--</span>
            <span id="trip-detail-cost" style="font-size:11px; color:rgba(226,232,240,0.85); background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); padding:5px 10px; border-radius:100px; font-weight:600;"><i class="fa-solid fa-coins" style="color:#f59e0b; margin-right:4px;"></i>₱0.00</span>
            <span id="trip-detail-count" style="font-size:11px; color:#34c759; background:rgba(52,199,89,0.12); border:1px solid rgba(52,199,89,0.25); padding:5px 10px; border-radius:100px; font-weight:700;"><i class="fa-solid fa-location-dot" style="margin-right:4px;"></i>0 Visited</span>
        </div>

        <div style="font-size:11px; font-weight:800; color:#38bdf8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Destinations Visited</div>
        <div id="trip-detail-destinations-list" style="flex:1; overflow-y:auto; padding-right:4px;">
            <div style="text-align:center; padding:16px; color:rgba(148,163,184,0.8); font-size:12px;">Loading destinations...</div>
        </div>
    </div>
</div>

<!-- Reward Details Modal -->
<div id="reward-details-modal" onclick="if(event.target===this)window.closeRewardDetailsModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.65); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:2000000; justify-content:center; align-items:center; padding:20px; opacity:0; transition:opacity 0.25s ease;">
    <div style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:24px; padding:24px; width:100%; max-width:340px; box-shadow:0 20px 50px rgba(10,25,60,0.5); text-align:center; transform:scale(0.88); transition:transform 0.25s cubic-bezier(0.16,1,0.3,1); position:relative;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <span id="reward-modal-badge" style="font-size:10px; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.22); padding:3px 9px; border-radius:8px; border:none !important;">PROMO</span>
            <button type="button" onclick="window.closeRewardDetailsModal()" style="background:rgba(255,255,255,0.14); border:none !important; outline:none !important; color:#ffffff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div style="width:70px; height:70px; border-radius:18px; background:rgba(255,255,255,0.15); border:none !important; outline:none !important; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; overflow:hidden; box-shadow:none !important;">
            <img id="reward-modal-img" src="" alt="Reward Logo" style="width:100%; height:100%; object-fit:contain; padding:8px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
        </div>
        <h4 id="reward-modal-title" style="margin:0 0 4px; font-size:17px; font-weight:800; color:#ffffff; line-height:1.3;">Reward Details</h4>
        <p style="margin:0 0 14px; font-size:12px; color:rgba(226,232,240,0.85); display:flex; align-items:center; justify-content:center; gap:4px;">
            <i class="fa-solid fa-store" style="font-size:11px; color:#38bdf8;"></i> <span id="reward-modal-partner-name">Partner</span>
        </p>
        <div style="background:rgba(255,255,255,0.1); border-radius:14px; padding:12px; margin-bottom:16px; text-align:left;">
            <div style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:rgba(255,255,255,0.65); margin-bottom:4px;">Description & Terms</div>
            <p id="reward-modal-desc" style="margin:0; font-size:12px; color:rgba(255,255,255,0.92); line-height:1.45;"></p>
        </div>
        <button type="button" id="reward-modal-redeem-btn" style="width:100%; padding:13px; border:none !important; outline:none !important; border-radius:14px; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#ffffff; font-size:13px; font-weight:800; cursor:pointer; box-shadow:none !important; display:flex; align-items:center; justify-content:center; gap:8px;">
            <i class="fa-solid fa-gift"></i> <span>Redeem for <strong id="reward-modal-cost-text">-- PTS</strong></span>
        </button>
    </div>
</div>
