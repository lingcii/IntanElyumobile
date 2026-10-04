<!-- Profile View -->
<?php
$pageTitle = 'My Profile';
$activeTab = 'profile';
?>

<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/testimony_modal.php'; ?>

<div class="profile-container has-header has-bottom-nav animate-slide-up">
    
    <!-- Profile Main Header Card -->
    <div class="profile-header stagger-1" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; border-radius: 24px; padding: 24px 20px; text-align: center; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25); margin-bottom: 20px;">
        <div class="profile-avatar-container" style="position: relative; display: inline-block; margin-bottom: 12px;">
            <img src="https://ui-avatars.com/api/?name=User&background=007AFF&color=fff&rounded=true&bold=true&size=128" alt="Profile" class="profile-avatar" id="profile-img" style="width: 100px; height: 100px; border-radius: 50%; border: none !important; outline: none !important; object-fit: cover; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);">
            <span id="profile-badge-icon" style="position: absolute; bottom: 2px; right: 2px; background: linear-gradient(135deg, #00f2fe, #0284c7); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; border: none !important; outline: none !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);" title="Verified Explorer"><i class="fa-solid fa-shield-halved"></i></span>
        </div>

        <h2 class="profile-name" id="profile-name" style="margin: 0 0 4px 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">Loading...</h2>
        <p class="profile-email" id="profile-email" style="margin: 0; font-size: 13px; color: #ffffff; opacity: 0.95; font-weight: 500;">loading@example.com</p>
        
        <div id="profile-meta" style="font-size: 12px; color: #00f2fe; margin: 8px 0 0 0; display: none; flex-wrap: wrap; justify-content: center; gap: 12px; font-weight: 600;"></div>
        <p id="profile-bio-text" style="font-size: 13px; color: #ffffff; opacity: 0.95; margin: 10px auto 0 auto; max-width: 320px; font-style: italic; line-height: 1.4; display: none; background: rgba(255,255,255,0.12); padding: 8px 14px; border-radius: 12px; border: none !important; outline: none !important;"></p>
        
        <div id="profile-pref-chips" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 12px;"></div>

        <!-- 3 Stats Cards inside Profile Card (Sleek & Visible, No Outlines) -->
        <div class="stats-container" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 16px;">
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                <div style="font-size: 18px; margin-bottom: 3px;"><i class="fa-solid fa-map-location-dot" style="color: #38bdf8;"></i></div>
                <div class="stat-value" id="stat-places" style="font-size: 17px; font-weight: 900; color: #ffffff; margin-bottom: 1px; letter-spacing: -0.4px;">0</div>
                <div class="stat-label" style="font-size: 10px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 0.5px;">Places</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                <div style="font-size: 18px; margin-bottom: 3px;"><i class="fa-solid fa-coins" style="color: #fbbf24;"></i></div>
                <div class="stat-value" id="stat-points" style="font-size: 17px; font-weight: 900; color: #ffffff; margin-bottom: 1px; letter-spacing: -0.4px;">0</div>
                <div class="stat-label" style="font-size: 10px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 0.5px;">Points</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                <div style="font-size: 18px; margin-bottom: 3px;"><i class="fa-solid fa-trophy" style="color: #f59e0b;"></i></div>
                <div class="stat-value" id="stat-rank" style="font-size: 17px; font-weight: 900; color: #ffffff; margin-bottom: 1px; letter-spacing: -0.4px;">—</div>
                <div class="stat-label" style="font-size: 10px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 0.5px;">Rank</div>
            </div>
        </div>

    </div>

    <!-- Trip History -->
    <div class="stagger-3" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; margin-left: 4px;">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a !important; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-route" style="color: #1e3a8a;"></i> Trip History
        </h3>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span id="trip-history-count-badge" style="font-size: 11px; font-weight: 800; background: #eff6ff; color: #1e3a8a; padding: 3px 10px; border-radius: 100px; border: none !important; outline: none !important;">0 Completed</span>
            <button onclick="window.openFullHistoryModal()" style="background: #f1f5f9; border: none !important; outline: none !important; color: #1e3a8a; font-size: 11px; font-weight: 800; cursor: pointer; padding: 4px 12px; border-radius: 100px; display: flex; align-items: center; gap: 4px; transition: all 0.2s ease;">
                View All <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #1e3a8a;"></i>
            </button>
        </div>
    </div>
    <div id="trip-history-container" class="stagger-3" style="margin-bottom: 24px;">
        <div id="trip-history-list">
            <div style="text-align:center; padding:20px; color:#ffffff; opacity:0.95; font-size:13px; font-weight:600; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:20px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">Loading history...</div>
        </div>
    </div>
    
    <!-- Points & Rewards Card (Styled like Notification Modal: Blue Banner Header, White Body) -->
    <div class="stagger-3" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 22px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.08);">
        <!-- Points Header Banner (Royal Blue like Notification Modal) -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%); padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; border: none !important;">
            <div style="text-align: left;">
                <div style="font-size: 11px; font-weight: 800; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-coins" style="color: #fbbf24;"></i> Available Points Balance
                </div>
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <span id="profile-points-val" style="font-size: 28px; font-weight: 900; color: #ffffff; letter-spacing: -0.8px;">--</span>
                    <span style="font-size: 13px; font-weight: 700; color: rgba(255, 255, 255, 0.85);">Points</span>
                </div>
            </div>
            <button onclick="navigateTo('puzzles')" style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; padding: 8px 16px; border-radius: 100px; font-weight: 800; font-size: 12px; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.94)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-gamepad" style="color: #0284c7;"></i> Play & Earn
            </button>
        </div>

        <!-- Card Body Area (Pure White Background like Notification Modal) -->
        <div style="padding: 20px 18px; background: #ffffff;">
            <!-- Catalog list: Redeem Rewards -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h5 style="margin: 0; font-size: 14.5px; font-weight: 800; color: #0f172a; text-align: left;">Redeem Rewards</h5>
                <a href="#" onclick="navigateTo('discount'); return false;" style="font-size: 11.5px; font-weight: 800; color: #1e3a8a; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                    View All Deals <i class="fa-solid fa-arrow-right" style="font-size: 9px; color: #1e3a8a;"></i>
                </a>
            </div>
            <div id="profile-rewards-catalog" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <div style="text-align: center; padding: 16px; color: #64748b; font-size: 12px; font-weight: 600;">
                    <i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px; color: #1e3a8a;"></i> Loading available rewards...
                </div>
            </div>

            <!-- Divider Line Above Active Vouchers -->
            <div style="height: 1px; background: #e2e8f0; margin-bottom: 20px;"></div>

            <!-- Active Claimed Vouchers -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h5 style="margin: 0; font-size: 14.5px; font-weight: 800; color: #0f172a; text-align: left;">Active Vouchers</h5>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="active-vouchers-count" style="font-size: 11px; font-weight: 800; background: #eff6ff !important; border: 1px solid #bfdbfe !important; color: #1e3a8a !important; padding: 3px 10px; border-radius: 100px;">0 Active</span>
                    <button id="view-all-vouchers-header-btn" onclick="window.openFullVouchersModal()" style="display: none; background: #f1f5f9 !important; border: 1px solid #e2e8f0 !important; outline: none !important; color: #1e3a8a !important; font-size: 11px; font-weight: 800; cursor: pointer; padding: 4px 12px; border-radius: 100px; align-items: center; gap: 4px; transition: all 0.2s ease;">
                        View All <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #1e3a8a;"></i>
                    </button>
                </div>
            </div>
            <div id="vouchers-list" style="display: flex; flex-direction: column; gap: 10px;">
                <div style="font-size: 12.5px; color: #64748b; font-weight: 600; text-align: center; padding: 18px; background: #f8fafc !important; border: 1.5px dashed #cbd5e1 !important; border-radius: 14px;">No redeemed vouchers yet.</div>
            </div>
        </div>
    </div>
    
    <!-- Account Settings -->
    <h3 class="stagger-3" style="font-size: 16px; font-weight: 800; color: #0f172a !important; margin-bottom: 12px; margin-left: 4px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-user-gear" style="color: #1e3a8a;"></i> Account Settings
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
                const elPoints = document.getElementById('stat-points') || document.getElementById('stat-xp');
                const elPlaces = document.getElementById('stat-places');
                const elRank = document.getElementById('stat-rank');
                const elName = document.getElementById('profile-name');
                const elEmail = document.getElementById('profile-email');
                const elImg = document.getElementById('profile-img');

                const points = parseInt(u.points !== undefined ? u.points : (u.xp || 0)) || 0;

                if (elPoints) elPoints.textContent = points.toLocaleString();
                if (elPlaces) elPlaces.textContent = data.places_visited || 0;
                if (elRank && data.my_rank) elRank.textContent = '#' + data.my_rank;
                if (elName) elName.textContent = u.name || 'Explorer';
                if (elEmail) elEmail.textContent = u.email || '';
                if (elImg && u.avatar) {
                    elImg.src = window.getFullImageUrl(u.avatar);
                }

                if (u && u.id) {
                    try {
                        const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                        Object.assign(stored, u);
                        localStorage.setItem('auth_user', JSON.stringify(stored));
                    } catch (e) {}
                }

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

                // Extra Meta (Tourist ID, Age, Gender, Phone & Home Location)
                window.renderProfileUserMeta(u);

                // Bio
                window.renderProfileBio(u.bio);

                // Preferences Chips
                window.renderProfilePreferences(u.travel_preferences);

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
                                <span style="color: #ffffff !important; font-weight: 800; font-size: 11px; background: #10b981 !important; border: none !important; outline: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap; flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                    <i class="fa-solid fa-check" style="color: #ffffff !important; font-size: 11px;"></i>Done
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
                const pointsBalance = (d.points !== undefined) ? d.points : (d.xp ?? 0);
                window._userPointsBalance = pointsBalance;
                const ptsVal = document.getElementById('profile-points-val');
                if (ptsVal) ptsVal.textContent = pointsBalance.toLocaleString();

                const elPoints = document.getElementById('stat-points') || document.getElementById('stat-xp');
                if (elPoints) elPoints.textContent = pointsBalance.toLocaleString();

                // Sync auth_user in localStorage
                try {
                    let stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    stored.points = pointsBalance;
                    localStorage.setItem('auth_user', JSON.stringify(stored));
                } catch(e) {}
                
                // Render Active Vouchers
                const list = document.getElementById('vouchers-list');
                const badge = document.getElementById('active-vouchers-count');
                const headerBtn = document.getElementById('view-all-vouchers-header-btn');
                if (list) {
                    if (d.vouchers && d.vouchers.length > 0) {
                        window._cachedActiveVouchers = d.vouchers;
                        if (badge) badge.textContent = `${d.vouchers.length} Active`;
                        if (headerBtn) {
                            headerBtn.style.display = (d.vouchers.length > 2) ? 'inline-flex' : 'none';
                        }
                        
                        // Limit displayed vouchers on profile card to max 2
                        const displayVouchers = d.vouchers.slice(0, 2);
                        let html = '';
                        displayVouchers.forEach(v => {
                            const voucherTitle = v.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (v.type === 'environmental_fee' ? 'Waived Environmental Fee' : (v.type || 'Tourist Voucher'));
                            const safeCode = (v.voucher_code || '').replace(/'/g, "\\'");
                            const isAct = (v.status || '').toLowerCase() === 'active';
                            
                            html += `
                            <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; padding: 14px 16px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; gap: 10px; box-shadow: 0 4px 14px rgba(10, 25, 60, 0.22);">
                                <div style="text-align: left; flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                        <i class="fa-solid fa-ticket" style="color: #00f2fe; font-size: 13px;"></i>
                                        <span style="font-size: 13.5px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; letter-spacing: -0.2px;">${voucherTitle}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <code style="font-size: 12.5px; font-weight: 900; color: #1e3a8a; letter-spacing: 0.5px; background: #ffffff !important; border: none !important; padding: 4px 10px; border-radius: 8px; font-family: monospace; box-shadow: 0 1px 4px rgba(0,0,0,0.12);">${v.voucher_code}</code>
                                        <button type="button" onclick="navigator.clipboard.writeText('${safeCode}'); if(typeof showToast==='function') showToast('Voucher code copied!');" style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a; padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.12); transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                                            <i class="fa-solid fa-copy" style="color: #1e3a8a;"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #ffffff !important; background: ${isAct ? '#10b981' : '#64748b'} !important; border: none !important; outline: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap; box-shadow: 0 2px 6px rgba(0,0,0,0.18);">
                                    ${v.status || 'Active'}
                                </span>
                            </div>`;
                        });
                        list.innerHTML = html;
                    } else {
                        window._cachedActiveVouchers = [];
                        if (badge) badge.textContent = '0 Active';
                        if (headerBtn) headerBtn.style.display = 'none';
                        list.innerHTML = '<div style="font-size:12.5px; color:#64748b; font-weight:600; text-align:center; padding:18px; background:#f8fafc !important; border:1.5px dashed #cbd5e1 !important; border-radius:14px;">No redeemed vouchers yet.</div>';
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
                            const ptsCost = parseInt(v.pointsCost || v.required_points || 100);
                            return `
                            <div onclick="window.showRewardDetailsModal(${idx})" style="display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; padding:12px 14px; border-radius:18px; gap:12px; transition:transform 0.15s ease, box-shadow 0.15s ease; cursor:pointer; box-shadow:0 6px 18px rgba(10, 25, 60, 0.22);" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                                <div style="display:flex; align-items:center; gap:10px; min-width:0; text-align:left; flex:1;">
                                    <div style="width:38px; height:38px; border-radius:12px; background:#ffffff; border:none; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                                        <img src="${v.image || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png'}" alt="${v.title}" style="width:100%; height:100%; object-fit:contain; padding:3px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
                                    </div>
                                    <div style="min-width:0; flex:1;">
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <strong style="display:block; font-size:13.5px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.2px;">${v.title}</strong>
                                            <span style="font-size:9.5px; font-weight:800; color:#1e3a8a !important; background:#ffffff !important; border:none !important; padding:2px 7px; border-radius:6px; flex-shrink:0; text-transform:uppercase; box-shadow:0 1px 4px rgba(0,0,0,0.12);">${v.badge}</span>
                                            ${v.id_needed ? `<span style="font-size:8.5px; font-weight:800; color:#ffffff !important; background:#ef4444 !important; padding:2px 6px; border-radius:4px; flex-shrink:0;"><i class="fa-solid fa-id-card"></i> ID</span>` : ''}
                                        </div>
                                        <span style="font-size:11.5px; color:rgba(255, 255, 255, 0.88); font-weight:600; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px;">${v.partner}</span>
                                    </div>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); window.showRewardDetailsModal(${idx})" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; padding:7px 14px; border-radius:100px; font-size:11.5px; font-weight:900; cursor:pointer; flex-shrink:0; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; white-space:nowrap; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.94)'" onpointerup="this.style.transform='scale(1)'">
                                    ${ptsCost.toLocaleString()} Points
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
                        catalogEl.innerHTML = fallbackVouchers.map((v, idx) => {
                            const ptsCost = parseInt(v.pointsCost || v.required_points || 100);
                            return `
                            <div onclick="window.showRewardDetailsModal(${idx})" style="display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; padding:12px 14px; border-radius:18px; margin-bottom:8px; cursor:pointer; transition:transform 0.15s ease, box-shadow 0.15s ease; box-shadow:0 6px 18px rgba(10, 25, 60, 0.22);" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                                <div style="display:flex; align-items:center; gap:10px; min-width:0; text-align:left; flex:1;">
                                    <div style="width:38px; height:38px; border-radius:12px; background:#ffffff; border:none; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                                        <img src="${v.image || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png'}" alt="${v.title}" style="width:100%; height:100%; object-fit:contain; padding:3px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
                                    </div>
                                    <div style="min-width:0; flex:1;">
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <strong style="display:block; font-size:13.5px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.2px;">${v.title}</strong>
                                            <span style="font-size:9.5px; font-weight:800; color:#1e3a8a !important; background:#ffffff !important; border:none !important; padding:2px 7px; border-radius:6px; flex-shrink:0; text-transform:uppercase; box-shadow:0 1px 4px rgba(0,0,0,0.12);">${v.badge}</span>
                                        </div>
                                        <span style="font-size:11.5px; color:rgba(255, 255, 255, 0.88); font-weight:600; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px;">${v.partner}</span>
                                    </div>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); window.showRewardDetailsModal(${idx})" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; padding:7px 14px; border-radius:100px; font-size:11.5px; font-weight:900; cursor:pointer; flex-shrink:0; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; white-space:nowrap; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.94)'" onpointerup="this.style.transform='scale(1)'">
                                    ${ptsCost.toLocaleString()} Points
                                </button>
                            </div>`;
                        }).join('');
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
            if (typeof showToast === 'function') showToast(`Insufficient Points. You need ${cost} Points (Balance: ${currentPts} Points).`);
            return;
        }

        if (!confirm(`Redeem '${title}' for ${cost} Points?`)) return;

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
                window.dashboardNeedsRefresh = true;
                try {
                    let claimed = JSON.parse(localStorage.getItem('intan_elyu_claimed_vouchers') || '[]');
                    const dbKey = 'db_' + voucherId;
                    if (!claimed.includes(dbKey)) claimed.push(dbKey);
                    localStorage.setItem('intan_elyu_claimed_vouchers', JSON.stringify(claimed));
                    localStorage.removeItem('intan_elyu_cached_vouchers');

                    for (let i = localStorage.length - 1; i >= 0; i--) {
                        const k = localStorage.key(i);
                        if (k && (k.startsWith('dashboard_data_') || k.startsWith('profile_data_'))) {
                            localStorage.removeItem(k);
                        }
                    }
                } catch(e) {}
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
            if (typeof showToast === 'function') showToast(`Insufficient Points. You need ${cost} Points (Balance: ${currentPts} Points).`);
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
                window.dashboardNeedsRefresh = true;
                try {
                    for (let i = localStorage.length - 1; i >= 0; i--) {
                        const k = localStorage.key(i);
                        if (k && (k.startsWith('dashboard_data_') || k.startsWith('profile_data_'))) {
                            localStorage.removeItem(k);
                        }
                    }
                } catch(e) {}
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
        const idNotice = document.getElementById('reward-modal-id-notice');

        if (badge) badge.textContent = voucher.badge || 'PROMO';
        if (img) img.src = voucher.image || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png';
        if (title) title.textContent = voucher.title || 'Exclusive Reward';
        if (partner) partner.textContent = voucher.partner || voucher.merchant || 'La Union Partner';
        if (idNotice) idNotice.style.display = voucher.id_needed ? 'block' : 'none';

        if (desc) {
            let descText = voucher.description || 'Redeem this voucher with your available Points to enjoy discounts at this partner establishment.';
            if (voucher.terms_and_conditions) {
                descText += '\n\n• Terms & Conditions:\n' + voucher.terms_and_conditions;
            }
            if (voucher.expires_formatted) {
                descText += '\n\n• Validity: ' + voucher.expires_formatted;
            }
            desc.textContent = descText;
        }
        if (costText) costText.textContent = `${voucher.pointsCost || voucher.required_points || 100} Points`;

        if (redeemBtn) {
            if (voucher.is_upcoming) {
                redeemBtn.disabled = true;
                redeemBtn.style.cursor = 'not-allowed';
                redeemBtn.style.opacity = '0.7';
                redeemBtn.textContent = `Starts on ${voucher.valid_from_formatted || 'Soon'}`;
                redeemBtn.onclick = null;
            } else {
                redeemBtn.disabled = false;
                redeemBtn.style.cursor = 'pointer';
                redeemBtn.style.opacity = '1';
                redeemBtn.textContent = `Redeem for ${voucher.pointsCost || voucher.required_points || 100} Points`;
                redeemBtn.onclick = function() {
                    window.closeRewardDetailsModal();
                    if (typeof voucher.id === 'string' && voucher.id.includes('_')) {
                        redeemReward(voucher.id, voucher.pointsCost);
                    } else {
                        window.redeemAdminVoucher(voucher.id, voucher.pointsCost, voucher.title);
                    }
                };
            }
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
            container.innerHTML = '<div style="text-align:center; padding:24px 16px; color:#64748b; font-size:13px; font-weight:600; background:#f8fafc; border:none !important; outline:none !important; box-shadow:none !important; border-radius:16px;">No completed trips found in your history.</div>';
        } else {
            let html = '';
            trips.forEach((trip, idx) => {
                const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set';
                const count = Array.isArray(trip.items) ? trip.items.length : (parseInt(trip.destinations_visited) || parseInt(trip.items) || 0);
                const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

                html += `
                <div onclick="window.showTripDetailsModal('${trip.id}')" style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: none !important; border-radius: 18px; padding: 16px; margin-bottom: 12px; cursor: pointer; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointercancel="this.style.transform='scale(1)'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div>
                            <div style="font-size:10px; font-weight:800; color:#00f2fe; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">Trip #${trips.length - idx}</div>
                            <strong style="color: #ffffff; font-size: 16px; font-weight: 800; line-height: 1.3;">${trip.title || 'Completed Trip'}</strong>
                        </div>
                        <span style="color: #ffffff; font-weight: 800; font-size: 11px; background: #10b981; border: none !important; outline: none !important; box-shadow: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap;">
                            <i class="fa-solid fa-circle-check" style="margin-right: 4px; color: #ffffff;"></i>Completed
                        </span>
                    </div>
                    <div style="font-size: 12px; color: rgba(255, 255, 255, 0.95); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; background: rgba(255,255,255,0.14); border: none !important; outline: none !important; box-shadow: none !important; padding: 8px 12px; border-radius: 12px;">
                        <span><i class="fa-regular fa-calendar" style="color: #00f2fe; margin-right: 4px;"></i>${date}</span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-coins" style="color: #fbbf24; margin-right: 4px;"></i>₱${cost}</span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-location-dot" style="color: #34d399; margin-right: 4px;"></i>${count} Destinations Visited</span>
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

    window.openFullVouchersModal = function() {
        const modal = document.getElementById('full-vouchers-modal');
        const container = document.getElementById('full-vouchers-list');
        if (!modal || !container) return;

        const vouchers = window._cachedActiveVouchers || [];
        if (vouchers.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:28px 16px; color:#64748b; font-size:13px; font-weight:700; background:#f8fafc; border-radius:16px;">No active vouchers found.</div>';
        } else {
            let html = '';
            vouchers.forEach((v, idx) => {
                const voucherTitle = v.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (v.type === 'environmental_fee' ? 'Waived Environmental Fee' : (v.type || 'Tourist Voucher'));
                const safeCode = (v.voucher_code || '').replace(/'/g, "\\'");
                const isAct = (v.status || '').toLowerCase() === 'active';
                const createdDate = v.created_at ? new Date(v.created_at).toLocaleDateString() : '';

                html += `
                <div style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 18px; padding: 16px; margin-bottom: 12px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.25);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: #00f2fe; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Voucher #${idx + 1}</div>
                            <strong style="color: #ffffff; font-size: 15px; font-weight: 800; line-height: 1.3;">${voucherTitle}</strong>
                        </div>
                        <span style="color: #ffffff !important; font-weight: 800; font-size: 11px; background: ${isAct ? '#10b981' : '#64748b'} !important; border: none !important; outline: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap; text-transform: uppercase;">
                            <i class="fa-solid ${isAct ? 'fa-check' : 'fa-clock'}" style="margin-right: 4px; color: #ffffff !important;"></i>${v.status || 'Active'}
                        </span>
                    </div>

                    <div style="background: #ffffff !important; border-radius: 12px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <div>
                            <div style="font-size: 9.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Claim Code</div>
                            <code style="font-size: 14px; font-weight: 900; color: #1e3a8a; letter-spacing: 1px; font-family: monospace;">${v.voucher_code}</code>
                        </div>
                        <button type="button" onclick="navigator.clipboard.writeText('${safeCode}'); if(typeof showToast==='function') showToast('Voucher code copied!');" style="background: #1e3a8a !important; color: #ffffff !important; border: none !important; outline: none !important; padding: 7px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-copy" style="color: #ffffff;"></i> Copy
                        </button>
                    </div>

                    ${createdDate ? `<div style="font-size: 11px; color: #e2e8f0; font-weight: 600;"><i class="fa-regular fa-calendar" style="color: #00f2fe; margin-right: 4px;"></i>Claimed: ${createdDate}</div>` : ''}
                </div>`;
            });
            container.innerHTML = html;
        }

        modal.style.display = 'flex';
    };

    window.closeFullVouchersModal = function() {
        const modal = document.getElementById('full-vouchers-modal');
        if (modal) modal.style.display = 'none';
    };

    window.showTripDetailsModal = function(tripId) {
        const trips = window._cachedCompletedTrips || [];
        const trip = trips.find(t => t.id == tripId) || trips[0];
        if (!trip) return;

        document.getElementById('trip-detail-title').textContent = trip.title || 'Completed Trip';
        document.getElementById('trip-detail-date').innerHTML = `<i class="fa-regular fa-calendar" style="color:#0284c7; margin-right:4px;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set'}`;
        const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('trip-detail-cost').innerHTML = `<i class="fa-solid fa-coins" style="color:#d97706; margin-right:4px;"></i>₱${cost}`;
        
        const items = trip.items || [];
        const visitedCount = items.filter(i => i.is_visited).length || items.length;
        document.getElementById('trip-detail-count').innerHTML = `<i class="fa-solid fa-location-dot" style="margin-right:4px; color:#ffffff;"></i>${visitedCount} Visited`;

        let destHtml = '';
        if (items.length === 0) {
            destHtml = '<div style="text-align:center; padding:16px; color:#94a3b8; font-size:12px;">No destination details found for this trip.</div>';
        } else {
            items.forEach((item, idx) => {
                const dest = item.destination;
                const destName = dest ? dest.name : (item.destination_name || 'Destination ' + (idx + 1));
                const fee = dest ? (dest.entrance_fee && parseFloat(dest.entrance_fee) > 0 ? '₱' + parseFloat(dest.entrance_fee).toFixed(2) : 'Free Entrance') : 'Visited';
                const spotId = item.tourist_spot_id || (dest ? dest.id : item.id);
                const isReviewed = spotId && window.userReviewedSpotIds && window.userReviewedSpotIds.has(Number(spotId));
                const sClass = (dest && dest.classification_status) ? dest.classification_status : '';
                const sMeta = (typeof window.getRewardPointsForClassification === 'function') ? window.getRewardPointsForClassification(sClass) : { points: 50 };

                destHtml += `
                <div style="display:flex; align-items:center; gap:10px; padding:12px 14px; background:linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border:none !important; outline:none !important; box-shadow:none !important; border-radius:14px; margin-bottom:8px;">
                    <div style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.2); border:none !important; outline:none !important; box-shadow:none !important; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:900; font-size:14px; color:#ffffff;">${idx + 1}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13.5px; font-weight:800; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${destName}</div>
                        <div style="font-size:11.5px; color:rgba(255,255,255,0.85); font-weight:600; margin-top:2px;">${fee}</div>
                    </div>
                    ${spotId ? `<button type="button" data-spot-id="${spotId}" data-spot-classification="${sClass}" onclick="event.stopPropagation(); window.openWriteTestimonyModal('${spotId}', this)" style="background: ${isReviewed ? 'rgba(255,255,255,0.22)' : 'linear-gradient(135deg, #00f2fe 0%, #0284c7 100%)'}; border: none !important; outline: none !important; box-shadow: none !important; color: #ffffff; padding: 7px 14px; border-radius: 100px; font-weight: 800; font-size: 11.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;">${isReviewed ? '<i class="fa-solid fa-check" style="font-size: 10px; margin-right: 4px;"></i> Reviewed' : `<i class="fa-solid fa-pen" style="font-size: 10px;"></i> Review (+${sMeta.points} PTS)`}</button>` : ''}
                </div>`;
            });
        }
        document.getElementById('trip-detail-destinations-list').innerHTML = destHtml;
        if (typeof window.syncReviewedButtons === 'function') {
            window.syncReviewedButtons();
        }

        const modal = document.getElementById('trip-details-modal');
        if (modal) modal.style.display = 'flex';
    };

    window.closeTripDetailsModal = function() {
        const modal = document.getElementById('trip-details-modal');
        if (modal) modal.style.display = 'none';
    };

    window.renderProfileUserMeta = function(u) {
        const elMeta = document.getElementById('profile-meta');
        if (!elMeta || !u) return;
        let metaParts = [];
        const tNum = u.tourist_number || u.tourist_id || (u.role === 'tourist' ? 1 : '');
        if (tNum) metaParts.push(`<span style="background:#2563eb; color:#ffffff; padding:3px 10px; border-radius:100px; font-size:11px; font-weight:800; border:none !important; outline:none !important; box-shadow:0 2px 6px rgba(0,0,0,0.2);">ID: #${tNum}</span>`);
        if (u.age) metaParts.push(`<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-cake-candles" style="color:#00f2fe;"></i> ${u.age} yrs old</span>`);
        if (u.gender) {
            const gLow = String(u.gender).toLowerCase();
            let gIcon = 'fa-venus-mars';
            if (gLow === 'male') gIcon = 'fa-mars';
            else if (gLow === 'female') gIcon = 'fa-venus';
            metaParts.push(`<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid ${gIcon}" style="color:#00f2fe;"></i> ${u.gender}</span>`);
        }
        if (u.home_location) metaParts.push(`<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-location-dot" style="color:#00f2fe;"></i> ${u.home_location}</span>`);
        if (u.phone) metaParts.push(`<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-phone" style="color:#00f2fe;"></i> ${u.phone}</span>`);
        if (metaParts.length > 0) {
            elMeta.innerHTML = metaParts.join(' &nbsp;•&nbsp; ');
            elMeta.style.display = 'flex';
        } else {
            elMeta.style.display = 'none';
        }
    };

    window.renderProfileBio = function(bio) {
        const elBio = document.getElementById('profile-bio-text');
        if (!elBio) return;
        if (bio && typeof bio === 'string' && bio.trim()) {
            elBio.textContent = `"${bio.trim()}"`;
            elBio.style.display = 'block';
        } else {
            elBio.style.display = 'none';
        }
    };

    window.renderProfilePreferences = function(prefStr) {
        const elChips = document.getElementById('profile-pref-chips');
        if (!elChips) return;
        if (!prefStr || typeof prefStr !== 'string' || !prefStr.trim()) {
            elChips.innerHTML = '';
            elChips.style.display = 'none';
            return;
        }
        const iconMap = {
            'surfing': '🏄‍♂️',
            'beach': '🏄‍♂️',
            'nature': '🏔️',
            'falls': '🏔️',
            'heritage': '🏛️',
            'culture': '🏛️',
            'food': '🍲',
            'dining': '🍲',
            'sunset': '🌅',
            'nightlife': '🌅'
        };
        const prefs = prefStr.split(',').map(s => s.trim()).filter(Boolean);
        if (!prefs.length) {
            elChips.innerHTML = '';
            elChips.style.display = 'none';
            return;
        }
        elChips.innerHTML = prefs.map(p => {
            let icon = '';
            const pLower = p.toLowerCase();
            if (!/[\u{1F300}-\u{1F9FF}]/u.test(p)) {
                for (const [key, ic] of Object.entries(iconMap)) {
                    if (pLower.includes(key)) {
                        icon = ic + ' ';
                        break;
                    }
                }
            }
            return `<span style="background:rgba(255,255,255,0.16); border:none !important; outline:none !important; color:#ffffff; padding:5px 14px; border-radius:100px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">${icon}${p}</span>`;
        }).join('');
        elChips.style.display = 'flex';
    };

    // Instant local cache render for zero-latency UI
    try {
        const _cachedAuth = JSON.parse(localStorage.getItem('auth_user') || '{}');
        if (_cachedAuth.name && document.getElementById('profile-name')) {
            document.getElementById('profile-name').textContent = _cachedAuth.name;
        }
        if (_cachedAuth.email && document.getElementById('profile-email')) {
            document.getElementById('profile-email').textContent = _cachedAuth.email;
        }
        if (_cachedAuth.avatar && document.getElementById('profile-img')) {
            document.getElementById('profile-img').src = window.getFullImageUrl ? window.getFullImageUrl(_cachedAuth.avatar) : _cachedAuth.avatar;
        }
        window.renderProfileUserMeta(_cachedAuth);
        window.renderProfileBio(_cachedAuth.bio);
        window.renderProfilePreferences(_cachedAuth.travel_preferences);
    } catch(e) {}

    // Listen for real-time profile updates from edit_profile
    window.addEventListener('userProfileUpdated', function(e) {
        if (e.detail) {
            const u = e.detail;
            if (u.name && document.getElementById('profile-name')) document.getElementById('profile-name').textContent = u.name;
            if (u.email && document.getElementById('profile-email')) document.getElementById('profile-email').textContent = u.email;
            if (u.avatar && document.getElementById('profile-img')) {
                document.getElementById('profile-img').src = window.getFullImageUrl ? window.getFullImageUrl(u.avatar) : u.avatar;
            }
            window.renderProfileUserMeta(u);
            window.renderProfileBio(u.bio);
            window.renderProfilePreferences(u.travel_preferences);
        }
    });

    fetchProfileData();
    fetchPointsAndVouchers();
</script>

<!-- Full Trip History Modal -->
<div id="full-history-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center; padding:20px;">
    <div style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:400px; max-height:82vh; display:flex; flex-direction:column; box-shadow:none !important; overflow:hidden; text-align:left; padding:0;">
        <!-- Trip History Header Banner -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; box-shadow:none !important; flex-shrink:0;">
            <h3 style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:9px; letter-spacing:-0.2px;">
                <i class="fa-solid fa-clock-rotate-left" style="color:#00f2fe; font-size:17px;"></i> Trip History
            </h3>
            <button onclick="window.closeFullHistoryModal()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; box-shadow:none !important; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Body Area Below Header (Pure White, No Shadow, No Outlines) -->
        <div id="full-history-list" class="hide-scrollbar" style="flex:1; overflow-y:auto; padding:18px 16px; background:#ffffff !important; border:none !important; outline:none !important; box-shadow:none !important;">
            <div style="text-align:center; padding:20px; color:#64748b; font-size:13px; font-weight:600;">Loading history...</div>
        </div>
    </div>
</div>

<!-- Full Active Vouchers Modal -->
<div id="full-vouchers-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center; padding:20px;">
    <div style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:400px; max-height:82vh; display:flex; flex-direction:column; box-shadow:none !important; overflow:hidden; text-align:left; padding:0;">
        <!-- Vouchers Header Banner -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; box-shadow:none !important; flex-shrink:0;">
            <h3 style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:9px; letter-spacing:-0.2px;">
                <i class="fa-solid fa-ticket" style="color:#00f2fe; font-size:17px;"></i> Active Vouchers
            </h3>
            <button onclick="window.closeFullVouchersModal()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; box-shadow:none !important; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Body Area Below Header (Pure White, No Shadow, No Outlines) -->
        <div id="full-vouchers-list" class="hide-scrollbar" style="flex:1; overflow-y:auto; padding:18px 16px; background:#ffffff !important; border:none !important; outline:none !important; box-shadow:none !important;">
            <div style="text-align:center; padding:20px; color:#64748b; font-size:13px; font-weight:600;">Loading vouchers...</div>
        </div>
    </div>
</div>

<!-- Completed Trip Details Modal -->
<div id="trip-details-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:2000000; justify-content:center; align-items:center; padding:20px;">
    <div style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:400px; max-height:82vh; display:flex; flex-direction:column; box-shadow:none !important; overflow:hidden; text-align:left; padding:0;">
        <!-- Header Banner -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; box-shadow:none !important; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:34px; height:34px; border-radius:10px; background:#10b981; border:none !important; outline:none !important; box-shadow:none !important; display:flex; align-items:center; justify-content:center; color:#ffffff; font-size:15px;">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <div>
                    <div style="font-size:10px; font-weight:800; color:#00f2fe; text-transform:uppercase; letter-spacing:0.5px;">Finished Trip Details</div>
                    <h3 id="trip-detail-title" style="margin:0; color:#ffffff; font-size:17px; font-weight:800; letter-spacing:-0.2px;">Trip Details</h3>
                </div>
            </div>
            <button onclick="window.closeTripDetailsModal()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; box-shadow:none !important; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Body Area Below Header (Pure White, No Shadow, No Outlines) -->
        <div class="hide-scrollbar" style="flex:1; overflow-y:auto; padding:18px 16px; background:#ffffff !important; border:none !important; outline:none !important; box-shadow:none !important;">
            <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
                <span id="trip-detail-date" style="font-size:11.5px; color:#1e3a8a; background:#f1f5f9; border:none !important; outline:none !important; box-shadow:none !important; padding:6px 12px; border-radius:100px; font-weight:700;"><i class="fa-regular fa-calendar" style="color:#0284c7; margin-right:5px;"></i>--</span>
                <span id="trip-detail-cost" style="font-size:11.5px; color:#1e3a8a; background:#f1f5f9; border:none !important; outline:none !important; box-shadow:none !important; padding:6px 12px; border-radius:100px; font-weight:700;"><i class="fa-solid fa-coins" style="color:#d97706; margin-right:5px;"></i>₱0.00</span>
                <span id="trip-detail-count" style="font-size:11.5px; color:#ffffff; background:#10b981; border:none !important; outline:none !important; box-shadow:none !important; padding:6px 12px; border-radius:100px; font-weight:800;"><i class="fa-solid fa-location-dot" style="margin-right:5px; color:#ffffff;"></i>0 Visited</span>
            </div>

            <div style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Destinations Visited</div>
            <div id="trip-detail-destinations-list" style="padding-right:0;">
                <div style="text-align:center; padding:16px; color:#94a3b8; font-size:12px;">Loading destinations...</div>
            </div>
        </div>
    </div>
</div>

<!-- Reward Details Modal -->
<div id="reward-details-modal" onclick="if(event.target===this)window.closeRewardDetailsModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.65); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:2000000; justify-content:center; align-items:center; padding:20px; opacity:0; transition:opacity 0.25s ease;">
    <div style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:350px; max-height:86vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 16px 40px rgba(10,25,60,0.45); text-align:center; transform:scale(0.88); transition:transform 0.25s cubic-bezier(0.16,1,0.3,1); position:relative; padding:0;">
        <!-- Header Banner (Royal Blue) -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:14px 18px; flex-shrink:0; display:flex; justify-content:space-between; align-items:center; border:none !important;">
            <div style="text-align:left;">
                <div style="font-size:15px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-gift" style="color:#00f2fe; font-size:14px;"></i> Reward Details
                </div>
                <span id="reward-modal-badge" style="font-size:9.5px; font-weight:800; color:#1e3a8a !important; background:#eff6ff !important; padding:2px 7px; border-radius:6px; margin-top:3px; display:inline-block; text-transform:uppercase;">PROMO</span>
            </div>
            <button type="button" onclick="window.closeRewardDetailsModal()" style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px; box-shadow:0 2px 8px rgba(0,0,0,0.18);">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Middle Body (White) -->
        <div class="hide-scrollbar" style="background:#ffffff !important; flex:1; min-height:0; overflow-y:auto; padding:18px; color:#0f172a;">
            <div style="width:68px; height:68px; border-radius:18px; background:#ffffff; border:1.5px solid #e2e8f0; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <img id="reward-modal-img" src="" alt="Reward Logo" style="width:100%; height:100%; object-fit:contain; padding:6px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
            </div>
            <h4 id="reward-modal-title" style="margin:0 0 4px; font-size:17px; font-weight:800; color:#0f172a; line-height:1.3;">Reward Details</h4>
            <p style="margin:0 0 14px; font-size:12.5px; color:#1e3a8a; font-weight:700; display:flex; align-items:center; justify-content:center; gap:4px;">
                <i class="fa-solid fa-store" style="font-size:11px; color:#0284c7;"></i> <span id="reward-modal-partner-name">Partner</span>
            </p>
            <div id="reward-modal-id-notice" style="display:none; background:#fef2f2 !important; border:1px solid #fecaca !important; border-radius:12px; padding:10px 12px; margin-bottom:12px; text-align:left;">
                <div style="display:flex; align-items:center; gap:6px; color:#dc2626 !important; font-size:11.5px; font-weight:800;">
                    <i class="fa-solid fa-id-card"></i> Valid ID Required Upon Redemption
                </div>
            </div>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px 14px; margin-bottom:6px; text-align:left;">
                <div style="font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748b; margin-bottom:4px;">Description & Terms</div>
                <p id="reward-modal-desc" style="margin:0; font-size:12px; color:#334155; line-height:1.45;"></p>
            </div>
        </div>

        <!-- Footer Banner (Royal Blue) -->
        <div style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:14px 18px; flex-shrink:0; border:none !important;">
            <button type="button" id="reward-modal-redeem-btn" style="width:100%; padding:12px; border:none !important; outline:none !important; border-radius:12px; background:#ffffff !important; color:#1e3a8a !important; font-size:13.5px; font-weight:900; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fa-solid fa-gift" style="color:#1e3a8a;"></i> <span style="color:#1e3a8a;">Redeem for <strong id="reward-modal-cost-text" style="color:#1e3a8a;">-- Points</strong></span>
            </button>
        </div>
    </div>
</div>
