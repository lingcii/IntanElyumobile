<!-- Profile View -->
<?php
$pageTitle = 'My Profile';
$activeTab = 'profile';
?>

<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/testimony_modal.php'; ?>

<div class="profile-container has-header has-bottom-nav animate-slide-up">

    <!-- Profile Main Header Card -->
    <div class="profile-header stagger-1"
        style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; border-radius: 24px; padding: 24px 20px; text-align: center; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25); margin-bottom: 20px;">
        <div class="profile-avatar-container" style="position: relative; display: inline-block; margin-bottom: 12px;">
            <img src="https://ui-avatars.com/api/?name=User&background=007AFF&color=fff&rounded=true&bold=true&size=128"
                alt="Profile" class="profile-avatar" id="profile-img"
                style="width: 100px; height: 100px; border-radius: 50%; border: none !important; outline: none !important; object-fit: cover; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);">
            <span id="profile-badge-icon"
                style="position: absolute; bottom: 2px; right: 2px; background: linear-gradient(135deg, #00f2fe, #0284c7); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; border: none !important; outline: none !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);"
                title="Verified Explorer"><i class="fa-solid fa-shield-halved"></i></span>
        </div>

        <h2 class="profile-name" id="profile-name"
            style="margin: 0 0 4px 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">
            Explorer</h2>
        <p class="profile-email" id="profile-email"
            style="margin: 0; font-size: 13px; color: #ffffff; opacity: 0.95; font-weight: 500;"></p>

        <div id="profile-meta"
            style="font-size: 12px; color: #00f2fe; margin: 8px 0 0 0; display: none; flex-wrap: wrap; justify-content: center; gap: 12px; font-weight: 600;">
        </div>
        <p id="profile-bio-text"
            style="font-size: 13px; color: #ffffff; opacity: 0.95; margin: 10px auto 0 auto; max-width: 320px; font-style: italic; line-height: 1.4; display: none; background: rgba(255,255,255,0.12); padding: 8px 14px; border-radius: 12px; border: none !important; outline: none !important;">
        </p>

        <div id="profile-pref-chips"
            style="display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 12px;"></div>

        <!-- 3 Stats Cards inside Profile Card (Sleek & Visible, No Outlines) -->
        <div class="stats-container"
            style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 16px;">
            <div class="stat-card"
                style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                <div style="font-size: 18px; margin-bottom: 3px;"><i class="fa-solid fa-map-location-dot"
                        style="color: #38bdf8;"></i></div>
                <div class="stat-value" id="stat-places"
                    style="font-size: 17px; font-weight: 900; color: #ffffff; margin-bottom: 1px; letter-spacing: -0.4px;">
                    0</div>
                <div class="stat-label"
                    style="font-size: 10px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 0.5px;">
                    Places</div>
            </div>
            <div class="stat-card"
                style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                <div style="font-size: 18px; margin-bottom: 3px;"><i class="fa-solid fa-coins"
                        style="color: #fbbf24;"></i></div>
                <div class="stat-value" id="stat-points"
                    style="font-size: 17px; font-weight: 900; color: #ffffff; margin-bottom: 1px; letter-spacing: -0.4px;">
                    0</div>
                <div class="stat-label"
                    style="font-size: 10px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 0.5px;">
                    Points</div>
            </div>
            <div class="stat-card"
                style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.14) 100%) !important; border: none !important; outline: none !important; border-radius: 16px; padding: 10px 6px; text-align: center; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 4px 14px rgba(10, 25, 60, 0.2) !important;">
                <div style="font-size: 18px; margin-bottom: 3px;"><i class="fa-solid fa-trophy"
                        style="color: #f59e0b;"></i></div>
                <div class="stat-value" id="stat-rank"
                    style="font-size: 17px; font-weight: 900; color: #ffffff; margin-bottom: 1px; letter-spacing: -0.4px;">
                    —</div>
                <div class="stat-label"
                    style="font-size: 10px; font-weight: 800; color: #ffffff; opacity: 0.95; text-transform: uppercase; letter-spacing: 0.5px;">
                    Rank</div>
            </div>
        </div>

    </div>

    <!-- Trip History -->
    <div class="stagger-3"
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; margin-left: 4px;">
        <h3
            style="font-size: 16px; font-weight: 800; color: #0f172a !important; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-route" style="color: #1e3a8a;"></i> Trip History
        </h3>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span id="trip-history-count-badge"
                style="font-size: 11px; font-weight: 800; background: #eff6ff; color: #1e3a8a; padding: 3px 10px; border-radius: 100px; border: none !important; outline: none !important;">0
                Completed</span>
            <button onclick="window.openFullHistoryModal()"
                style="background: #f1f5f9; border: none !important; outline: none !important; color: #1e3a8a; font-size: 11px; font-weight: 800; cursor: pointer; padding: 4px 12px; border-radius: 100px; display: flex; align-items: center; gap: 4px; transition: all 0.2s ease;">
                View All <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #1e3a8a;"></i>
            </button>
        </div>
    </div>
    <div id="trip-history-container" class="stagger-3" style="margin-bottom: 24px;">
        <div id="trip-history-list"></div>
    </div>

    <!-- Points & Rewards Card (Styled like Notification Modal: Blue Banner Header, White Body) -->
    <div class="stagger-3"
        style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 22px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.08);">
        <!-- Points Header Banner (Royal Blue like Notification Modal) -->
        <div
            style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%); padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; border: none !important;">
            <div style="text-align: left;">
                <div
                    style="font-size: 11px; font-weight: 800; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-coins" style="color: #fbbf24;"></i> Available Points Balance
                </div>
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <span id="profile-points-val"
                        style="font-size: 28px; font-weight: 900; color: #ffffff; letter-spacing: -0.8px;">0</span>
                    <span style="font-size: 13px; font-weight: 700; color: rgba(255, 255, 255, 0.85);">Points</span>
                </div>
            </div>
            <button onclick="navigateTo('puzzles')"
                style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; padding: 8px 16px; border-radius: 100px; font-weight: 800; font-size: 12px; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; display: inline-flex; align-items: center; gap: 6px; transition: transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.94)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-gamepad" style="color: #0284c7;"></i> Play & Earn
            </button>
        </div>

        <!-- Card Body Area (Pure White Background like Notification Modal) -->
        <div style="padding: 20px 18px; background: #ffffff;">
            <!-- Catalog list: Redeem Rewards -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h5 style="margin: 0; font-size: 14.5px; font-weight: 800; color: #0f172a; text-align: left;">Redeem
                    Rewards</h5>
                <button type="button" onclick="window.openFullDealsModal()"
                    style="background: #eff6ff !important; border: 1px solid #bfdbfe !important; outline: none !important; color: #1e3a8a !important; font-size: 11px; font-weight: 800; cursor: pointer; padding: 4px 12px; border-radius: 100px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s ease;"
                    onpointerdown="this.style.transform='scale(0.95)'" onpointerup="this.style.transform='scale(1)'">
                    View All Deals <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #1e3a8a;"></i>
                </button>
            </div>
            <div id="profile-rewards-catalog"
                style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;"></div>

            <!-- Divider Line Above Active Vouchers -->
            <div style="height: 1px; background: #e2e8f0; margin-bottom: 20px;"></div>

            <!-- Active Claimed Vouchers -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 6px; flex-wrap: wrap;">
                <h5 style="margin: 0; font-size: 14.5px; font-weight: 800; color: #0f172a; text-align: left;">Active
                    Vouchers</h5>
                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <span id="active-vouchers-count"
                        style="font-size: 11px; font-weight: 800; background: #eff6ff !important; border: 1px solid #bfdbfe !important; color: #1e3a8a !important; padding: 3px 10px; border-radius: 100px;">0
                        Active</span>
                    <button id="view-all-vouchers-header-btn" onclick="window.openFullVouchersModal('active')"
                        style="display: none; background: #f1f5f9 !important; border: 1px solid #e2e8f0 !important; outline: none !important; color: #1e3a8a !important; font-size: 11px; font-weight: 800; cursor: pointer; padding: 4px 12px; border-radius: 100px; align-items: center; gap: 4px; transition: all 0.2s ease;">
                        View All <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #1e3a8a;"></i>
                    </button>
                    <button id="voucher-history-header-btn" onclick="window.openFullVouchersModal('history')"
                        style="display: none; background: #f8fafc !important; border: 1px solid #e2e8f0 !important; outline: none !important; color: #64748b !important; font-size: 11px; font-weight: 800; cursor: pointer; padding: 4px 10px; border-radius: 100px; align-items: center; gap: 4px; transition: all 0.2s ease;">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 9.5px; color: #64748b;"></i> History (<span id="voucher-history-count">0</span>)
                    </button>
                </div>
            </div>
            <div id="vouchers-list" style="display: flex; flex-direction: column; gap: 10px;"></div>
        </div>
    </div>

    <!-- Account Settings -->
    <h3 class="stagger-3"
        style="font-size: 16px; font-weight: 800; color: #0f172a !important; margin-bottom: 12px; margin-left: 4px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-user-gear" style="color: #1e3a8a;"></i> Account Settings
    </h3>

    <div class="settings-group stagger-3"
        style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none; outline: none; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 20px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
        <a href="#" class="settings-item" onclick="navigateTo('settings'); return false;">
            <div class="settings-icon"
                style="background: #ffffff !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;"><i
                    class="fa-solid fa-gear" style="color: #1e3a8a !important;"></i></div>
            <div class="settings-text">App Preferences & Settings</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="navigateTo('help'); return false;">
            <div class="settings-icon"
                style="background: #ffffff !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;"><i
                    class="fa-solid fa-circle-question" style="color: #1e3a8a !important;"></i></div>
            <div class="settings-text">Help & Support Center</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="handleLogout(event)">
            <div class="settings-icon logout"
                style="background: #FF3B30 !important; box-shadow: 0 2px 8px rgba(255, 59, 48, 0.3) !important;"><i
                    class="fa-solid fa-arrow-right-from-bracket" style="color: #ffffff !important;"></i></div>
            <div class="settings-text" style="color: #FF3B30;">Log Out</div>
        </a>
    </div>

</div>

<script>
    var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';

    // ── Dedicated Helper Rendering Functions ──
    window.renderProfileUserMeta = function (u) {
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

    window.renderProfileBio = function (bio) {
        const elBio = document.getElementById('profile-bio-text');
        if (!elBio) return;
        if (bio && typeof bio === 'string' && bio.trim()) {
            elBio.textContent = `"${bio.trim()}"`;
            elBio.style.display = 'block';
        } else {
            elBio.style.display = 'none';
        }
    };

    window.renderProfilePreferences = function (prefStr) {
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

    window.renderProfileData = function (data) {
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
        if (elName && u.name) elName.textContent = u.name;
        if (elEmail && u.email) elEmail.textContent = u.email;
        if (elImg && u.avatar) {
            elImg.src = window.getFullImageUrl ? window.getFullImageUrl(u.avatar) : u.avatar;
        }

        if (u && u.id) {
            try {
                const stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
                Object.assign(stored, u);
                if (data.places_visited !== undefined) stored.places_visited = data.places_visited;
                if (data.my_rank !== undefined) stored.my_rank = data.my_rank;
                localStorage.setItem('auth_user', JSON.stringify(stored));
            } catch (e) { }
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

        window.renderProfileUserMeta(u);
        window.renderProfileBio(u.bio);
        window.renderProfilePreferences(u.travel_preferences);

        // Trip History
        const rawCompletedTrips = data.completed_trips || [];
        const validCompletedTrips = rawCompletedTrips.filter(trip => {
            const items = Array.isArray(trip.items) ? trip.items : [];
            const visitedCount = items.filter(i => i.is_visited === true || i.is_visited === 1 || i.is_visited === '1').length;
            const totalCount = items.length || parseInt(trip.destinations_visited) || 0;
            const effectiveCount = visitedCount > 0 ? visitedCount : totalCount;
            return effectiveCount > 0;
        });
        window._cachedCompletedTrips = validCompletedTrips;

        const historyList = document.getElementById('trip-history-list');
        const historyBadge = document.getElementById('trip-history-count-badge');
        if (historyList) {
            if (validCompletedTrips.length === 0) {
                if (historyBadge) historyBadge.textContent = '0 Completed';
                historyList.innerHTML = '<div style="text-align:center; padding:20px; color:#ffffff; opacity:0.95; font-size:13px; font-weight:600; background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:20px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">No completed trips yet. Start exploring!</div>';
            } else {
                if (historyBadge) historyBadge.textContent = `${validCompletedTrips.length} Completed`;
                let html = '';
                const displayTrips = validCompletedTrips.slice(0, 3);
                displayTrips.forEach(trip => {
                    const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date';
                    const items = Array.isArray(trip.items) ? trip.items : [];
                    const visitedCount = items.filter(i => i.is_visited === true || i.is_visited === 1 || i.is_visited === '1').length;
                    const count = visitedCount > 0 ? visitedCount : (items.length || parseInt(trip.destinations_visited) || 1);
                    const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                    html += `
                    <div onclick="window.showTripDetailsModal('${trip.id}')" role="button" tabindex="0" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none !important; outline: none !important; border-radius: 20px; padding: 16px 18px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; cursor: pointer; user-select: none; touch-action: manipulation; -webkit-tap-highlight-color: transparent; transition: transform 0.15s ease, opacity 0.15s ease; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointerleave="this.style.transform='scale(1)'">
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
    };

    window.renderPointsAndVouchersData = function (d) {
        if (!d) return;
        const pointsBalance = (d.points !== undefined) ? d.points : (d.xp ?? 0);
        window._userPointsBalance = pointsBalance;
        const ptsVal = document.getElementById('profile-points-val');
        if (ptsVal) ptsVal.textContent = pointsBalance.toLocaleString();

        const elPoints = document.getElementById('stat-points') || document.getElementById('stat-xp');
        if (elPoints) elPoints.textContent = pointsBalance.toLocaleString();

        const dealsPts = document.getElementById('full-deals-user-points');
        if (dealsPts) dealsPts.textContent = pointsBalance.toLocaleString();

        // Sync auth_user in localStorage
        try {
            let stored = JSON.parse(localStorage.getItem('auth_user') || '{}');
            stored.points = pointsBalance;
            localStorage.setItem('auth_user', JSON.stringify(stored));
        } catch (e) { }

        // Process Active vs History Vouchers
        const rawVouchers = Array.isArray(d.vouchers) ? d.vouchers : [];
        const activeVouchers = [];
        const historyVouchers = [];

        rawVouchers.forEach(v => {
            const st = (v.status || '').toLowerCase();
            if (st === 'redeemed' || st === 'used' || st === 'completed' || st === 'expired') {
                historyVouchers.push(v);
            } else {
                activeVouchers.push(v);
            }
        });

        window._cachedActiveVouchers = activeVouchers;
        window._cachedHistoryVouchers = historyVouchers;

        // Sync claimed vouchers IDs scoped to this authenticated user
        try {
            const uid = (function() {
                try {
                    const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    if (u && (u.id || u.user_id)) return String(u.id || u.user_id);
                } catch(e) {}
                const tok = localStorage.getItem('intan_elyu_token');
                return (tok && tok.length > 10) ? 'tok_' + tok.substring(0, 12) : 'guest';
            })();
            const storageKey = 'intan_elyu_claimed_vouchers_' + uid;
            let claimedSet = new Set();
            rawVouchers.forEach(v => {
                if (v.voucher_id) {
                    claimedSet.add(v.voucher_id);
                    claimedSet.add('db_' + v.voucher_id);
                }
                if (v.id) claimedSet.add(v.id);
                if (v.voucher_code) claimedSet.add(v.voucher_code);
            });
            localStorage.setItem(storageKey, JSON.stringify(Array.from(claimedSet)));
            localStorage.removeItem('intan_elyu_claimed_vouchers');
        } catch (e) { }

        // Update header badges and buttons
        const list = document.getElementById('vouchers-list');
        const badge = document.getElementById('active-vouchers-count');
        const headerBtn = document.getElementById('view-all-vouchers-header-btn');
        const historyBtn = document.getElementById('voucher-history-header-btn');
        const historyCountSpan = document.getElementById('voucher-history-count');

        if (badge) badge.textContent = `${activeVouchers.length} Active`;
        if (headerBtn) {
            headerBtn.style.display = (activeVouchers.length > 2) ? 'inline-flex' : 'none';
        }
        if (historyBtn && historyCountSpan) {
            historyCountSpan.textContent = historyVouchers.length;
            historyBtn.style.display = (historyVouchers.length > 0) ? 'inline-flex' : 'none';
        }

        // Render Active Vouchers on profile card (ONLY active, NOT redeemed)
        if (list) {
            if (activeVouchers.length > 0) {
                const displayVouchers = activeVouchers.slice(0, 2);
                let html = '';
                displayVouchers.forEach(v => {
                    const voucherTitle = v.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (v.type === 'environmental_fee' ? 'Waived Environmental Fee' : (v.type || 'Tourist Voucher'));
                    const safeCode = (v.voucher_code || '').replace(/'/g, "\\'");
                    html += `
                    <div onclick="window.openActiveVoucherQrModal('${safeCode}')" role="button" tabindex="0" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; padding: 14px 16px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; gap: 10px; box-shadow: 0 4px 14px rgba(10, 25, 60, 0.22); cursor: pointer; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                        <div style="text-align: left; flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 5px;">
                                <i class="fa-solid fa-ticket" style="color: #00f2fe; font-size: 13px;"></i>
                                <span style="font-size: 13.5px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; letter-spacing: -0.2px;">${voucherTitle}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #00f2fe;">
                                <i class="fa-solid fa-qrcode" style="font-size: 11px;"></i> Tap to open Voucher QR Pass
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                            <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #ffffff !important; background: #10b981 !important; border: none !important; outline: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap; box-shadow: 0 2px 6px rgba(0,0,0,0.18);">
                                <i class="fa-solid fa-check" style="margin-right: 4px; color: #ffffff !important;"></i>Claimed
                            </span>
                            <div style="font-size: 10.5px; font-weight: 800; color: #ffffff; background: rgba(0, 242, 254, 0.22); border: 1px solid rgba(0, 242, 254, 0.45); padding: 3px 9px; border-radius: 8px; display: flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-qrcode" style="color: #00f2fe;"></i> Open Pass
                            </div>
                        </div>
                    </div>`;
                });
                list.innerHTML = html;
            } else {
                let emptyHistoryMsg = '';
                if (historyVouchers.length > 0) {
                    emptyHistoryMsg = `<div style="margin-top: 10px;"><button type="button" onclick="window.openFullVouchersModal('history')" style="background: #eff6ff !important; border: 1px solid #bfdbfe !important; color: #1e3a8a !important; padding: 6px 14px; border-radius: 100px; font-size: 11.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;"><i class="fa-solid fa-clock-rotate-left"></i> View ${historyVouchers.length} in Voucher History</button></div>`;
                }
                list.innerHTML = `<div style="font-size:12.5px; color:#64748b; font-weight:600; text-align:center; padding:18px; background:#f8fafc !important; border:1.5px dashed #cbd5e1 !important; border-radius:14px;">No active vouchers right now.${emptyHistoryMsg}</div>`;
            }
        }

        // Re-filter rewards catalog to make sure claimed/redeemed vouchers are excluded
        if (window._rawVouchersCatalog) {
            window.renderCatalogRewards(window._rawVouchersCatalog);
        }
    };

    window.renderCatalogRewards = function (allVouchers) {
        const catalogEl = document.getElementById('profile-rewards-catalog');
        if (!catalogEl || !Array.isArray(allVouchers)) return;
        window._rawVouchersCatalog = allVouchers;

        // Build set of claimed/redeemed voucher keys scoped to current user
        let claimedSet = new Set();
        try {
            const uid = (function() {
                try {
                    const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    if (u && (u.id || u.user_id)) return String(u.id || u.user_id);
                } catch(e) {}
                const tok = localStorage.getItem('intan_elyu_token');
                return (tok && tok.length > 10) ? 'tok_' + tok.substring(0, 12) : 'guest';
            })();
            const arr = JSON.parse(localStorage.getItem('intan_elyu_claimed_vouchers_' + uid) || '[]');
            arr.forEach(id => claimedSet.add(String(id).toLowerCase()));
        } catch (e) { }

        const userRedemptions = (window._cachedActiveVouchers || []).concat(window._cachedHistoryVouchers || []);
        userRedemptions.forEach(v => {
            if (v.voucher_id) {
                claimedSet.add(String(v.voucher_id).toLowerCase());
                claimedSet.add('db_' + String(v.voucher_id).toLowerCase());
            }
            if (v.id) claimedSet.add(String(v.id).toLowerCase());
            if (v.voucher_code) claimedSet.add(String(v.voucher_code).toLowerCase());
            if (v.type) claimedSet.add(String(v.type).toLowerCase());
        });

        // Exclude already claimed or redeemed vouchers from available deals
        const availableVouchers = allVouchers.filter(v => {
            const idStr = String(v.id || '').toLowerCase();
            const dbIdStr = String(v.dbId || '').toLowerCase();
            const titleStr = String(v.title || v.voucher_name || '').toLowerCase();
            const codeStr = String(v.code || v.voucher_code || '').toLowerCase();

            if (claimedSet.has(idStr) || claimedSet.has('db_' + idStr)) return false;
            if (dbIdStr && (claimedSet.has(dbIdStr) || claimedSet.has('db_' + dbIdStr))) return false;
            if (codeStr && claimedSet.has(codeStr)) return false;
            if (titleStr && claimedSet.has(titleStr)) return false;
            if (v.is_expired) return false;
            return true;
        });

        window._profileAvailableVouchers = availableVouchers;
        window._profileVouchersList = availableVouchers.slice(0, 3);

        if (availableVouchers.length === 0) {
            catalogEl.innerHTML = `
            <div style="font-size:12.5px; color:#1e3a8a; font-weight:700; text-align:center; padding:16px; background:#eff6ff !important; border:1px solid #bfdbfe !important; border-radius:16px;">
                <i class="fa-solid fa-circle-check" style="color:#10b981; margin-right:4px;"></i> All available deals claimed! Check your Active Vouchers or History.
            </div>`;
            return;
        }

        const topVouchers = availableVouchers.slice(0, 3);
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
                            <span style="font-size:9.5px; font-weight:800; color:#1e3a8a !important; background:#ffffff !important; border:none !important; padding:2px 7px; border-radius:6px; flex-shrink:0; text-transform:uppercase; box-shadow:0 1px 4px rgba(0,0,0,0.12);">${v.badge || 'PROMO'}</span>
                            ${v.id_needed ? `<span style="font-size:8.5px; font-weight:800; color:#ffffff !important; background:#ef4444 !important; padding:2px 6px; border-radius:4px; flex-shrink:0;"><i class="fa-solid fa-id-card"></i> ID</span>` : ''}
                        </div>
                        <span style="font-size:11.5px; color:rgba(255, 255, 255, 0.88); font-weight:600; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px;">${v.partner || v.merchant || 'La Union Partner'}</span>
                    </div>
                </div>
                <button type="button" onclick="event.stopPropagation(); window.showRewardDetailsModal(${idx})" style="background:#ffffff !important; color:#1e3a8a !important; border:none !important; outline:none !important; padding:7px 14px; border-radius:100px; font-size:11.5px; font-weight:900; cursor:pointer; flex-shrink:0; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; white-space:nowrap; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.94)'" onpointerup="this.style.transform='scale(1)'">
                    ${ptsCost.toLocaleString()} Points
                </button>
            </div>`;
        }).join('');

        const dealsModal = document.getElementById('full-deals-modal');
        if (dealsModal && dealsModal.style.display === 'flex') {
            window.renderFullDealsList(availableVouchers);
        }
    };

    // ── Instant Synchronous Cache Hydration (0ms, Zero Flicker, Zero Latency) ──
    function hydrateProfileFromCache() {
        const token = localStorage.getItem('intan_elyu_token');

        // 1. Hydrate user basic info & points from auth_user
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
            if (_cachedAuth.points !== undefined || _cachedAuth.xp !== undefined) {
                const pts = parseInt(_cachedAuth.points !== undefined ? _cachedAuth.points : _cachedAuth.xp) || 0;
                window._userPointsBalance = pts;
                const elPts = document.getElementById('stat-points') || document.getElementById('stat-xp');
                const ptsVal = document.getElementById('profile-points-val');
                const dealsPts = document.getElementById('full-deals-user-points');
                if (elPts) elPts.textContent = pts.toLocaleString();
                if (ptsVal) ptsVal.textContent = pts.toLocaleString();
                if (dealsPts) dealsPts.textContent = pts.toLocaleString();
            }
            if (_cachedAuth.places_visited !== undefined && document.getElementById('stat-places')) {
                document.getElementById('stat-places').textContent = _cachedAuth.places_visited;
            }
            if (_cachedAuth.my_rank && document.getElementById('stat-rank')) {
                document.getElementById('stat-rank').textContent = '#' + _cachedAuth.my_rank;
            }
            window.renderProfileUserMeta(_cachedAuth);
            window.renderProfileBio(_cachedAuth.bio);
            window.renderProfilePreferences(_cachedAuth.travel_preferences);
        } catch (e) { }

        if (!token) return;

        // 2. Hydrate full profile data (badges, places visited, trips) from profile_data_ cache
        try {
            const profileCacheKey = 'profile_data_' + token.substring(0, 10);
            const cachedProfileRaw = localStorage.getItem(profileCacheKey);
            if (cachedProfileRaw) {
                const parsed = JSON.parse(cachedProfileRaw);
                const data = parsed.data || parsed;
                if (data && typeof window.renderProfileData === 'function') {
                    window.renderProfileData(data);
                }
            }
        } catch (e) { }

        // 3. Hydrate points balance & active vouchers from points_balance_ cache
        try {
            const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
            const cachedPtsRaw = localStorage.getItem(ptsCacheKey);
            if (cachedPtsRaw) {
                const parsed = JSON.parse(cachedPtsRaw);
                const data = parsed.data || parsed;
                if (data && typeof window.renderPointsAndVouchersData === 'function') {
                    window.renderPointsAndVouchersData(data);
                }
            }
        } catch (e) { }

        // 4. Hydrate catalog rewards from intan_elyu_cached_vouchers
        try {
            const cachedVouchersRaw = localStorage.getItem('intan_elyu_cached_vouchers');
            if (cachedVouchersRaw) {
                const list = JSON.parse(cachedVouchersRaw);
                if (Array.isArray(list) && list.length > 0 && typeof window.renderCatalogRewards === 'function') {
                    window.renderCatalogRewards(list);
                }
            }
        } catch (e) { }
    }

    // Immediately hydrate on script execution
    hydrateProfileFromCache();

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
                if (data) window.renderProfileData(data);
            },
            shouldForce,
            60000
        );
    }

    async function fetchPointsAndVouchers() {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
        await window.useCache(
            ptsCacheKey,
            async () => {
                const r = await fetch(backendUrl + '/api/tourist/points/balance', {
                    headers: {
                        'Accept': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (!r.ok) throw new Error("Failed to fetch balance");
                return await r.json();
            },
            (d) => {
                if (d && d.status === 'success') {
                    window.renderPointsAndVouchersData(d);
                }
            },
            false,
            30000
        );

        // Fetch active catalog rewards
        const catalogEl = document.getElementById('profile-rewards-catalog');
        if (catalogEl) {
            try {
                let cachedV = null;
                try { cachedV = JSON.parse(localStorage.getItem('intan_elyu_cached_vouchers')); } catch (e) { }
                if (Array.isArray(cachedV) && cachedV.length > 0) {
                    window.renderCatalogRewards(cachedV);
                }

                const resVouchers = await fetch(backendUrl + '/api/vouchers', {
                    headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
                });
                if (resVouchers.ok) {
                    const vouchersPayload = await resVouchers.json();
                    if (vouchersPayload.status === 'success' && Array.isArray(vouchersPayload.data) && vouchersPayload.data.length > 0) {
                        try {
                            localStorage.setItem('intan_elyu_cached_vouchers', JSON.stringify(vouchersPayload.data));
                        } catch (e) { }
                        window.renderCatalogRewards(vouchersPayload.data);
                    }
                }
            } catch (e) { }
        }
    }

    window.redeemAdminVoucher = async function (voucherId, cost, title) {
        if (!navigator.onLine) {
            if (typeof showToast === 'function') {
                showToast("📍 Voucher claiming requires an active internet connection to generate your live QR claim code.");
            }
            return;
        }

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
                    const uid = (function() {
                        try {
                            const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                            if (u && (u.id || u.user_id)) return String(u.id || u.user_id);
                        } catch(e) {}
                        const tok = localStorage.getItem('intan_elyu_token');
                        return (tok && tok.length > 10) ? 'tok_' + tok.substring(0, 12) : 'guest';
                    })();
                    const storageKey = 'intan_elyu_claimed_vouchers_' + uid;
                    let claimed = JSON.parse(localStorage.getItem(storageKey) || '[]');
                    const dbKey = 'db_' + voucherId;
                    if (!claimed.includes(dbKey)) claimed.push(dbKey);
                    localStorage.setItem(storageKey, JSON.stringify(claimed));
                    localStorage.removeItem('intan_elyu_cached_vouchers');
                    localStorage.removeItem('intan_elyu_claimed_vouchers');

                    for (let i = localStorage.length - 1; i >= 0; i--) {
                        const k = localStorage.key(i);
                        if (k && (k.startsWith('dashboard_data_') || k.startsWith('profile_data_'))) {
                            localStorage.removeItem(k);
                        }
                    }
                } catch (e) { }
                fetchPointsAndVouchers();
            } else {
                if (typeof showToast === 'function') showToast(data.message || "Failed to redeem voucher.");
            }
        } catch (error) {
            console.error("Redemption error:", error);
            if (typeof showToast === 'function') showToast("Network error. Please try again.");
        }
    };

    window.redeemReward = async function (type, cost) {
        if (!navigator.onLine) {
            if (typeof showToast === 'function') {
                showToast("📍 Voucher claiming requires an active internet connection to generate your live QR claim code.");
            }
            return;
        }

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
                } catch (e) { }
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
    window.updateProfilePointsDisplay = function (points) {
        const ptsVal = document.getElementById('profile-points-val');
        if (ptsVal) ptsVal.textContent = points;
    };

    window.showRewardDetailsModal = function (idxOrVoucher) {
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
                redeemBtn.onclick = function () {
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

    window.closeRewardDetailsModal = function () {
        const modal = document.getElementById('reward-details-modal');
        if (!modal) return;
        modal.style.opacity = '0';
        const card = modal.querySelector('div');
        if (card) card.style.transform = 'scale(0.88)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 220);
    };

    window.openFullHistoryModal = function () {
        const modal = document.getElementById('full-history-modal');
        const container = document.getElementById('full-history-list');
        if (!modal || !container) return;

        const allTrips = window._cachedCompletedTrips || [];
        const trips = allTrips.filter(trip => {
            const items = Array.isArray(trip.items) ? trip.items : [];
            const visitedCount = items.filter(i => i.is_visited === true || i.is_visited === 1 || i.is_visited === '1').length;
            const totalCount = items.length || parseInt(trip.destinations_visited) || 0;
            const effectiveCount = visitedCount > 0 ? visitedCount : totalCount;
            return effectiveCount > 0;
        });

        if (trips.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:24px 16px; color:#64748b; font-size:13px; font-weight:600; background:#f8fafc; border:none !important; outline:none !important; box-shadow:none !important; border-radius:16px;">No completed trips found in your history.</div>';
        } else {
            let html = '';
            trips.forEach((trip, idx) => {
                const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set';
                const items = Array.isArray(trip.items) ? trip.items : [];
                const visitedCount = items.filter(i => i.is_visited === true || i.is_visited === 1 || i.is_visited === '1').length;
                const count = visitedCount > 0 ? visitedCount : (items.length || parseInt(trip.destinations_visited) || 1);
                const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                html += `
                <div onclick="window.showTripDetailsModal('${trip.id}')" role="button" tabindex="0" style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; box-shadow: none !important; border-radius: 18px; padding: 16px; margin-bottom: 12px; cursor: pointer; user-select: none; touch-action: manipulation; -webkit-tap-highlight-color: transparent; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'" onpointerleave="this.style.transform='scale(1)'">
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

    window.closeFullHistoryModal = function () {
        const modal = document.getElementById('full-history-modal');
        if (modal) modal.style.display = 'none';
    };

    window.openFullVouchersModal = function (initialTab = 'active') {
        const modal = document.getElementById('full-vouchers-modal');
        if (!modal) return;
        window._currentVouchersTab = (initialTab === 'history') ? 'history' : 'active';
        window.switchFullVouchersTab(window._currentVouchersTab);
        modal.style.display = 'flex';
    };

    window.closeFullVouchersModal = function () {
        const modal = document.getElementById('full-vouchers-modal');
        if (modal) modal.style.display = 'none';
    };

    window.switchFullVouchersTab = function (tab) {
        window._currentVouchersTab = tab;
        const activeTabBtn = document.getElementById('modal-tab-active-vouchers');
        const historyTabBtn = document.getElementById('modal-tab-history-vouchers');
        const container = document.getElementById('full-vouchers-list');
        if (!container) return;

        const activeVouchers = window._cachedActiveVouchers || [];
        const historyVouchers = window._cachedHistoryVouchers || [];

        if (tab === 'history') {
            if (activeTabBtn) {
                activeTabBtn.style.background = 'transparent';
                activeTabBtn.style.color = '#64748b';
                activeTabBtn.style.fontWeight = '700';
            }
            if (historyTabBtn) {
                historyTabBtn.style.background = '#1e3a8a';
                historyTabBtn.style.color = '#ffffff';
                historyTabBtn.style.fontWeight = '800';
            }

            if (historyVouchers.length === 0) {
                container.innerHTML = `
                <div style="text-align:center; padding:38px 16px; background:#f8fafc; border-radius:18px;">
                    <div style="width:52px; height:52px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; color:#64748b; font-size:20px;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <strong style="display:block; color:#1e293b; font-size:15px; margin-bottom:4px;">No Voucher History</strong>
                    <p style="margin:0; font-size:12px; color:#64748b; line-height:1.45;">Once you use your vouchers at partner merchants and staff verify them, they will appear here in your history.</p>
                </div>`;
                return;
            }

            let html = '';
            historyVouchers.forEach((v, idx) => {
                const voucherTitle = v.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (v.type === 'environmental_fee' ? 'Waived Environmental Fee' : (v.type || 'Tourist Voucher'));
                const safeCode = (v.voucher_code || '').replace(/'/g, "\\'");
                const redeemedDate = v.redeemed_at ? new Date(v.redeemed_at).toLocaleDateString() : (v.updated_at ? new Date(v.updated_at).toLocaleDateString() : 'Redeemed');

                html += `
                <div onclick="window.openActiveVoucherQrModal('${safeCode}')" role="button" tabindex="0" style="background:#f8fafc !important; border:1px solid #e2e8f0 !important; border-radius:18px; padding:16px; margin-bottom:12px; cursor:pointer; opacity:0.92; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                        <div>
                            <div style="font-size:10px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">Voucher History #${historyVouchers.length - idx}</div>
                            <strong style="color:#1e293b; font-size:15px; font-weight:800; line-height:1.3;">${voucherTitle}</strong>
                        </div>
                        <span style="color:#ffffff !important; font-weight:800; font-size:11px; background:#dc2626 !important; border:none !important; padding:4px 10px; border-radius:100px; white-space:nowrap; text-transform:uppercase;">
                            <i class="fa-solid fa-check-double" style="margin-right:4px;"></i>Redeemed
                        </span>
                    </div>
                    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:10px 14px; display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:8px;">
                        <div>
                            <div style="font-size:9.5px; font-weight:800; color:#94a3b8; text-transform:uppercase;">Claim Pass</div>
                            <code style="font-size:13.5px; font-weight:900; color:#64748b; letter-spacing:2px; font-family:monospace;">••••••••••••</code>
                        </div>
                        <span style="font-size:11px; color:#15803d; font-weight:700; background:#f0fdf4; padding:4px 8px; border-radius:6px; border:1px solid #bbf7d0;">
                            <i class="fa-solid fa-circle-check"></i> Scanned
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; color:#64748b; font-weight:600;">
                        <span><i class="fa-regular fa-clock" style="margin-right:4px; color:#94a3b8;"></i>Redeemed: ${redeemedDate}</span>
                        <span style="color:#1e3a8a; font-weight:800;">View Details <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i></span>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        } else {
            // Active Vouchers Tab
            if (activeTabBtn) {
                activeTabBtn.style.background = '#1e3a8a';
                activeTabBtn.style.color = '#ffffff';
                activeTabBtn.style.fontWeight = '800';
            }
            if (historyTabBtn) {
                historyTabBtn.style.background = 'transparent';
                historyTabBtn.style.color = '#64748b';
                historyTabBtn.style.fontWeight = '700';
            }

            if (activeVouchers.length === 0) {
                container.innerHTML = `
                <div style="text-align:center; padding:38px 16px; background:#f8fafc; border-radius:18px;">
                    <div style="width:52px; height:52px; border-radius:50%; background:#eff6ff; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; color:#1e3a8a; font-size:20px;">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <strong style="display:block; color:#1e293b; font-size:15px; margin-bottom:4px;">No Active Vouchers</strong>
                    <p style="margin:0 0 14px 0; font-size:12px; color:#64748b; line-height:1.45;">You don't have any unredeemed vouchers right now. Claim deals using your points!</p>
                    <button type="button" onclick="window.closeFullVouchersModal(); window.openFullDealsModal();" style="background:#1e3a8a !important; color:#ffffff !important; border:none !important; padding:9px 18px; border-radius:100px; font-size:12px; font-weight:800; cursor:pointer;">
                        <i class="fa-solid fa-gift" style="margin-right:5px;"></i> Browse Available Deals
                    </button>
                </div>`;
                return;
            }

            let html = '';
            activeVouchers.forEach((v, idx) => {
                const voucherTitle = v.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (v.type === 'environmental_fee' ? 'Waived Environmental Fee' : (v.type || 'Tourist Voucher'));
                const safeCode = (v.voucher_code || '').replace(/'/g, "\\'");
                const createdDate = v.created_at ? new Date(v.created_at).toLocaleDateString() : '';

                html += `
                <div onclick="window.openActiveVoucherQrModal('${safeCode}')" role="button" tabindex="0" style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 18px; padding: 16px; margin-bottom: 12px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.25); cursor: pointer; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: #00f2fe; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Active Voucher #${idx + 1}</div>
                            <strong style="color: #ffffff; font-size: 15px; font-weight: 800; line-height: 1.3;">${voucherTitle}</strong>
                        </div>
                        <span style="color: #ffffff !important; font-weight: 800; font-size: 11px; background: #10b981 !important; border: none !important; outline: none !important; padding: 4px 10px; border-radius: 100px; white-space: nowrap; text-transform: uppercase;">
                            <i class="fa-solid fa-check" style="margin-right: 4px; color: #ffffff !important;"></i>Claimed
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid rgba(255, 255, 255, 0.14); font-size: 11px; color: #e2e8f0; font-weight: 600;">
                        ${createdDate ? `<span><i class="fa-regular fa-calendar" style="color: #00f2fe; margin-right: 4px;"></i>Claimed: ${createdDate}</span>` : '<span></span>'}
                        <span style="color: #00f2fe; font-size: 11.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-qrcode"></i> Tap to open Voucher QR Pass <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i></span>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }
    };

    // ── All Deals & Rewards Modal Handlers (Stays on Profile Page) ──
    window.openFullDealsModal = function () {
        const modal = document.getElementById('full-deals-modal');
        if (!modal) return;

        const pts = window._userPointsBalance || 0;
        const ptsEl = document.getElementById('full-deals-user-points');
        if (ptsEl) ptsEl.textContent = pts.toLocaleString();

        const availableDeals = window._profileAvailableVouchers || [];
        window.renderFullDealsList(availableDeals);

        const searchInput = document.getElementById('deals-search-input');
        if (searchInput) searchInput.value = '';

        modal.style.display = 'flex';
    };

    window.closeFullDealsModal = function () {
        const modal = document.getElementById('full-deals-modal');
        if (modal) modal.style.display = 'none';
    };

    window.handleDealsModalSearch = function (query) {
        const q = (query || '').toLowerCase().trim();
        const availableDeals = window._profileAvailableVouchers || [];
        if (!q) {
            window.renderFullDealsList(availableDeals);
            return;
        }
        const filtered = availableDeals.filter(v => {
            const title = (v.title || v.voucher_name || '').toLowerCase();
            const partner = (v.partner || v.merchant || '').toLowerCase();
            const desc = (v.description || '').toLowerCase();
            return title.includes(q) || partner.includes(q) || desc.includes(q);
        });
        window.renderFullDealsList(filtered);
    };

    window.renderFullDealsList = function (deals) {
        const container = document.getElementById('full-deals-list');
        if (!container) return;

        if (!Array.isArray(deals) || deals.length === 0) {
            container.innerHTML = `
            <div style="text-align:center; padding:38px 16px; background:#f8fafc; border-radius:18px;">
                <div style="width:52px; height:52px; border-radius:50%; background:#eff6ff; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; color:#1e3a8a; font-size:20px;">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <strong style="display:block; color:#1e293b; font-size:15px; margin-bottom:4px;">No Deals Available</strong>
                <p style="margin:0; font-size:12px; color:#64748b; line-height:1.45;">You have claimed all available deals, or no rewards match your search.</p>
            </div>`;
            return;
        }

        let html = '';
        deals.forEach((v, idx) => {
            const ptsCost = parseInt(v.pointsCost || v.required_points || 100);
            const userPts = window._userPointsBalance || 0;
            const canAfford = userPts >= ptsCost;

            html += `
            <div onclick="window.showFullDealsRewardModal(${idx})" role="button" tabindex="0" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; padding:14px; margin-bottom:12px; display:flex; align-items:center; gap:12px; box-shadow:0 2px 8px rgba(0,0,0,0.04); cursor:pointer; transition:transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                <div style="width:48px; height:48px; border-radius:14px; background:#f1f5f9; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                    <img src="${v.image || 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png'}" alt="${v.title}" style="width:100%; height:100%; object-fit:contain; padding:4px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
                </div>
                <div style="flex:1; min-width:0; text-align:left;">
                    <div style="display:flex; align-items:center; gap:6px; margin-bottom:2px;">
                        <strong style="font-size:14px; font-weight:800; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v.title}</strong>
                        <span style="font-size:9px; font-weight:800; color:#1e3a8a; background:#eff6ff; padding:2px 6px; border-radius:4px; flex-shrink:0;">${v.badge || 'PROMO'}</span>
                        ${v.id_needed ? `<span style="font-size:8.5px; font-weight:800; color:#ef4444; background:#fef2f2; padding:2px 5px; border-radius:4px; flex-shrink:0;"><i class="fa-solid fa-id-card"></i> ID</span>` : ''}
                    </div>
                    <div style="font-size:12px; color:#64748b; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${v.partner || v.merchant || 'La Union Merchant'}</div>
                    <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                        <span style="font-size:11.5px; font-weight:800; color:#1e3a8a; display:inline-flex; align-items:center; gap:4px;">
                            <i class="fa-solid fa-coins" style="color:#f59e0b;"></i> ${ptsCost.toLocaleString()} Points
                        </span>
                        <span style="font-size:10.5px; font-weight:700; color:${canAfford ? '#15803d' : '#dc2626'};">
                            ${canAfford ? '✓ Eligible' : 'Need more pts'}
                        </span>
                    </div>
                </div>
                <button type="button" onclick="event.stopPropagation(); window.showFullDealsRewardModal(${idx})" style="background:${canAfford ? '#1e3a8a' : '#94a3b8'} !important; color:#ffffff !important; border:none !important; outline:none !important; padding:8px 14px; border-radius:100px; font-size:11.5px; font-weight:800; cursor:pointer; flex-shrink:0; white-space:nowrap;">
                    View Deal
                </button>
            </div>`;
        });

        container.innerHTML = html;
    };

    window.showFullDealsRewardModal = function (idx) {
        const deals = window._profileAvailableVouchers || [];
        const reward = deals[idx];
        if (!reward) return;
        window.showRewardDetailsModal(reward);
    };

    let activeQrSyncInterval = null;
    let currentActiveQrCode = '';

    window.openActiveVoucherQrModal = function (codeOrVoucher) {
        let voucher = null;
        const vouchers = window._cachedActiveVouchers || [];

        if (typeof codeOrVoucher === 'object' && codeOrVoucher !== null) {
            voucher = codeOrVoucher;
        } else {
            voucher = vouchers.find(v => (v.voucher_code === codeOrVoucher || v.code === codeOrVoucher)) || { voucher_code: codeOrVoucher, code: codeOrVoucher };
        }

        const resolvedCode = (voucher && (voucher.voucher_code || voucher.code)) || (typeof codeOrVoucher === 'string' ? codeOrVoucher : '');
        if (!resolvedCode) return;

        currentActiveQrCode = resolvedCode;
        const modal = document.getElementById('active-voucher-qr-modal');
        if (!modal) return;

        const titleEl = document.getElementById('active-qr-modal-title');
        const partnerEl = document.getElementById('active-qr-modal-partner');
        const imgEl = document.getElementById('active-qr-modal-img');
        const statusBadge = document.getElementById('active-qr-status-badge');
        const noticeEl = document.getElementById('active-qr-notice');

        const vTitle = voucher.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (voucher.type === 'environmental_fee' ? 'Waived Environmental Fee' : (voucher.type || 'Tourist Voucher'));
        if (titleEl) titleEl.textContent = vTitle;
        if (partnerEl) partnerEl.textContent = voucher.partner_establishment || voucher.category || 'Official Partner Merchant';

        if (imgEl) {
            imgEl.src = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(resolvedCode)}`;
        }

        const statusLower = (voucher.status || '').toLowerCase();
        const isRedeemed = statusLower === 'redeemed' || statusLower === 'used';

        if (statusBadge) {
            if (isRedeemed) {
                statusBadge.innerHTML = '<i class="fa-solid fa-ban"></i> Voucher Already Redeemed';
                statusBadge.style.background = '#fee2e2';
                statusBadge.style.color = '#dc2626';
                statusBadge.style.border = '1px solid #fca5a5';
                if (imgEl) {
                    imgEl.style.filter = 'grayscale(1)';
                    imgEl.style.opacity = '0.35';
                }
                if (noticeEl) {
                    noticeEl.textContent = 'This voucher has already been redeemed and verified at checkout. It can no longer be used.';
                    noticeEl.style.color = '#dc2626';
                    noticeEl.style.fontWeight = '700';
                }
            } else {
                statusBadge.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Ready for scan at checkout';
                statusBadge.style.background = '#e0f2fe';
                statusBadge.style.color = '#0369a1';
                statusBadge.style.border = 'none';
                if (imgEl) {
                    imgEl.style.filter = 'none';
                    imgEl.style.opacity = '1';
                }
                if (noticeEl) {
                    noticeEl.textContent = 'Present this QR code directly to merchant or staff at checkout for instant scanning.';
                    noticeEl.style.color = '#64748b';
                    noticeEl.style.fontWeight = '500';
                }
                startActiveQrSync(resolvedCode);
            }
        }

        // Reset mask state to hidden initially
        window._isVoucherCodeUnmasked = false;
        const codeEl = document.getElementById('active-qr-modal-code');
        const iconEl = document.getElementById('active-qr-mask-icon');
        const labelEl = document.getElementById('active-qr-mask-label');
        const maskBtn = document.getElementById('active-qr-mask-btn');
        if (codeEl) {
            codeEl.textContent = '••••••••••••';
            codeEl.style.letterSpacing = '2.5px';
            codeEl.style.color = '#1e3a8a';
        }
        if (iconEl) iconEl.className = 'fa-solid fa-eye';
        if (labelEl) labelEl.textContent = 'Voucher Code (Tap to reveal)';
        if (maskBtn) {
            maskBtn.style.background = '#eff6ff';
            maskBtn.style.borderColor = '#bfdbfe';
        }

        modal.style.display = 'flex';
    };

    window._isVoucherCodeUnmasked = false;
    window.toggleActiveVoucherCodeMask = function () {
        if (!currentActiveQrCode) return;
        window._isVoucherCodeUnmasked = !window._isVoucherCodeUnmasked;
        const codeEl = document.getElementById('active-qr-modal-code');
        const iconEl = document.getElementById('active-qr-mask-icon');
        const labelEl = document.getElementById('active-qr-mask-label');
        const maskBtn = document.getElementById('active-qr-mask-btn');

        if (window._isVoucherCodeUnmasked) {
            if (codeEl) {
                codeEl.textContent = currentActiveQrCode;
                codeEl.style.letterSpacing = '1px';
                codeEl.style.color = '#0284c7';
            }
            if (iconEl) iconEl.className = 'fa-solid fa-eye-slash';
            if (labelEl) labelEl.textContent = 'Voucher Code (Tap to hide)';
            if (maskBtn) {
                maskBtn.style.background = '#dbeafe';
                maskBtn.style.borderColor = '#93c5fd';
            }
        } else {
            if (codeEl) {
                codeEl.textContent = '••••••••••••';
                codeEl.style.letterSpacing = '2.5px';
                codeEl.style.color = '#1e3a8a';
            }
            if (iconEl) iconEl.className = 'fa-solid fa-eye';
            if (labelEl) labelEl.textContent = 'Voucher Code (Tap to reveal)';
            if (maskBtn) {
                maskBtn.style.background = '#eff6ff';
                maskBtn.style.borderColor = '#bfdbfe';
            }
        }
    };

    window.closeActiveVoucherQrModal = function () {
        stopActiveQrSync();
        window._isVoucherCodeUnmasked = false;
        const modal = document.getElementById('active-voucher-qr-modal');
        if (modal) modal.style.display = 'none';
    };

    window.copyCurrentActiveQrCode = function () {
        if (typeof showToast === 'function') {
            showToast("Present your digital QR pass directly to merchant staff.");
        }
    };

    window.copyVoucherCodeToClipboard = function () {
        if (typeof showToast === 'function') {
            showToast("Present your digital QR pass directly to merchant staff.");
        }
    };

    function startActiveQrSync(code) {
        stopActiveQrSync();
        if (!code) return;

        const check = async () => {
            if (!navigator.onLine) return; // Ambient check: pause polling while offline
            try {
                const baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
                const res = await fetch(`${baseUrl}/api/public/redemptions/${encodeURIComponent(code)}/status`, {
                    headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.status === 'success' && data.is_redeemed) {
                        const statusBadge = document.getElementById('active-qr-status-badge');
                        const imgEl = document.getElementById('active-qr-modal-img');
                        const noticeEl = document.getElementById('active-qr-notice');

                        if (statusBadge) {
                            statusBadge.innerHTML = `<i class="fa-solid fa-circle-check" style="color:#15803d; font-size:13px;"></i> Redeemed & Verified at ${data.redeemed_by_partner || 'Partner Merchant'}!`;
                            statusBadge.style.background = '#dcfce7';
                            statusBadge.style.color = '#15803d';
                            statusBadge.style.border = '1px solid #86efac';
                        }
                        if (imgEl) {
                            imgEl.style.filter = 'grayscale(1)';
                            imgEl.style.opacity = '0.35';
                        }
                        if (noticeEl) {
                            noticeEl.textContent = 'Redemption complete! This voucher has been recorded and can no longer be reused.';
                            noticeEl.style.color = '#15803d';
                            noticeEl.style.fontWeight = '700';
                        }
                        if (window.confetti) {
                            window.confetti({ particleCount: 60, spread: 60, origin: { y: 0.6 } });
                        }
                        stopActiveQrSync();
                        // Refresh vouchers cache on profile page
                        fetchPointsAndVouchers();
                    }
                }
            } catch (e) { }
        };

        setTimeout(check, 1200);
        activeQrSyncInterval = setInterval(check, 4000);
    }

    function stopActiveQrSync() {
        if (activeQrSyncInterval) {
            clearInterval(activeQrSyncInterval);
            activeQrSyncInterval = null;
        }
    }

    window.renderTripDetailModalContent = function (trip) {
        if (!trip) return;
        document.getElementById('trip-detail-title').textContent = trip.title || 'Completed Trip';
        document.getElementById('trip-detail-date').innerHTML = `<i class="fa-regular fa-calendar" style="color:#0284c7; margin-right:4px;"></i>${trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date set'}`;
        const cost = parseFloat(trip.total_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('trip-detail-cost').innerHTML = `<i class="fa-solid fa-coins" style="color:#d97706; margin-right:4px;"></i>₱${cost}`;

        const items = trip.items || [];
        const visitedCount = items.filter(i => i.is_visited === true || i.is_visited === 1 || i.is_visited === '1').length || items.length;
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
    };

    window.showTripDetailsModal = function (tripId) {
        const trips = window._cachedCompletedTrips || [];
        let trip = trips.find(t => String(t.id) === String(tripId));
        if (!trip && trips.length > 0) trip = trips[0];

        const modal = document.getElementById('trip-details-modal');
        if (trip) {
            window.renderTripDetailModalContent(trip);
            if (modal) modal.style.display = 'flex';
            return;
        }

        // If not in cache, check connectivity before fetching
        if (!navigator.onLine) {
            if (typeof showToast === 'function') {
                showToast("Trip details are unavailable offline until connection is restored.");
            }
            return;
        }

        const token = localStorage.getItem('intan_elyu_token');
        if (tripId && token && typeof backendUrl !== 'undefined') {
            fetch(`${backendUrl}/api/tourist/itineraries/${tripId}`, {
                headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
            })
                .then(r => r.json())
                .then(res => {
                    const fetched = res.data || res.itinerary || res;
                    if (fetched && fetched.id) {
                        window.renderTripDetailModalContent(fetched);
                        if (modal) modal.style.display = 'flex';
                    }
                })
                .catch(err => console.error("Trip details fetch error:", err));
        }
    };

    window.closeTripDetailsModal = function () {
        const modal = document.getElementById('trip-details-modal');
        if (modal) modal.style.display = 'none';
    };


    // Listen for real-time profile updates from edit_profile
    window.addEventListener('userProfileUpdated', function (e) {
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

    // Ambient automatic reconnect listener - quietly syncs profile & vouchers when network restores
    window.addEventListener('online', function () {
        if (typeof fetchProfileData === 'function') fetchProfileData(true);
        if (typeof fetchPointsAndVouchers === 'function') fetchPointsAndVouchers();
    });

    // Real-time re-hydration when returning to Profile view
    window.addEventListener('viewLoaded', function (e) {
        if (e && e.detail && e.detail.view === 'profile') {
            hydrateProfileFromCache();
            fetchProfileData(true);
            fetchPointsAndVouchers();
        }
    });

    hydrateProfileFromCache();
    fetchProfileData();
    fetchPointsAndVouchers();
</script>

<!-- Full Trip History Modal -->
<div id="full-history-modal" onclick="if(event.target===this)window.closeFullHistoryModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center; padding:20px;">
    <div
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:400px; max-height:82vh; display:flex; flex-direction:column; box-shadow:none !important; overflow:hidden; text-align:left; padding:0;">
        <!-- Trip History Header Banner -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; box-shadow:none !important; flex-shrink:0;">
            <h3
                style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:9px; letter-spacing:-0.2px;">
                <i class="fa-solid fa-clock-rotate-left" style="color:#00f2fe; font-size:17px;"></i> Trip History
            </h3>
            <button onclick="window.closeFullHistoryModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; box-shadow:none !important; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Body Area Below Header (Pure White, No Shadow, No Outlines) -->
        <div id="full-history-list" class="hide-scrollbar"
            style="flex:1; overflow-y:auto; padding:18px 16px; background:#ffffff !important; border:none !important; outline:none !important; box-shadow:none !important;">
            <div style="text-align:center; padding:20px; color:#64748b; font-size:13px; font-weight:600;">Loading
                history...</div>
        </div>
    </div>
</div>

<!-- Full Vouchers & History Modal (Tabbed) -->
<div id="full-vouchers-modal" onclick="if(event.target===this)window.closeFullVouchersModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center; padding:20px;">
    <div
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:400px; max-height:84vh; display:flex; flex-direction:column; box-shadow:none !important; overflow:hidden; text-align:left; padding:0;">
        <!-- Vouchers Header Banner -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; box-shadow:none !important; flex-shrink:0;">
            <h3
                style="margin:0; color:#ffffff; font-size:18px; font-weight:800; display:flex; align-items:center; gap:9px; letter-spacing:-0.2px;">
                <i class="fa-solid fa-ticket" style="color:#00f2fe; font-size:17px;"></i> My Vouchers
            </h3>
            <button onclick="window.closeFullVouchersModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; box-shadow:none !important; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Segmented Tab Switcher (Active Vouchers vs Voucher History) -->
        <div style="display:flex; background:#f1f5f9; padding:4px; margin:12px 16px 4px; border-radius:12px; gap:4px; flex-shrink:0;">
            <button type="button" id="modal-tab-active-vouchers" onclick="window.switchFullVouchersTab('active')"
                style="flex:1; padding:8px 10px; border-radius:9px; border:none; background:#1e3a8a; color:#ffffff; font-size:12px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s ease;">
                <i class="fa-solid fa-ticket"></i> Active Vouchers
            </button>
            <button type="button" id="modal-tab-history-vouchers" onclick="window.switchFullVouchersTab('history')"
                style="flex:1; padding:8px 10px; border-radius:9px; border:none; background:transparent; color:#64748b; font-size:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s ease;">
                <i class="fa-solid fa-clock-rotate-left"></i> Voucher History
            </button>
        </div>

        <!-- Body Area Below Header (Pure White, No Shadow, No Outlines) -->
        <div id="full-vouchers-list" class="hide-scrollbar"
            style="flex:1; overflow-y:auto; padding:14px 16px 18px; background:#ffffff !important; border:none !important; outline:none !important; box-shadow:none !important;">
            <div style="text-align:center; padding:20px; color:#64748b; font-size:13px; font-weight:600;">Loading vouchers...</div>
        </div>
    </div>
</div>

<!-- Full Deals & Rewards Modal (Preserves Profile View and User Account) -->
<div id="full-deals-modal" onclick="if(event.target===this)window.closeFullDealsModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:999999; justify-content:center; align-items:center; padding:20px;">
    <div
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:420px; max-height:86vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,0.3) !important; overflow:hidden; text-align:left; padding:0;">
        <!-- Header Banner -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:9px;">
                <i class="fa-solid fa-gift" style="color:#00f2fe; font-size:18px;"></i>
                <h3 style="margin:0; color:#ffffff; font-size:17.5px; font-weight:800; letter-spacing:-0.2px;">All Deals & Rewards</h3>
            </div>
            <button onclick="window.closeFullDealsModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- User Points Balance Banner inside Deals Modal -->
        <div style="background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); padding:12px 18px; border-bottom:1px solid #bfdbfe; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:32px; height:32px; border-radius:10px; background:#1e3a8a; display:flex; align-items:center; justify-content:center; color:#fbbf24; font-size:14px;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div style="font-size:10px; font-weight:800; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.5px;">Your Available Points</div>
                    <div style="font-size:16px; font-weight:900; color:#1e3a8a; line-height:1.2;">
                        <span id="full-deals-user-points">0</span> <span style="font-size:12px; font-weight:700; color:#64748b;">Points</span>
                    </div>
                </div>
            </div>
            <span style="font-size:11px; font-weight:800; color:#0284c7; background:#ffffff; padding:4px 10px; border-radius:100px; border:1px solid #bfdbfe;">
                Redeem instantly
            </span>
        </div>

        <!-- Search Input -->
        <div style="padding:12px 16px 8px; background:#ffffff; flex-shrink:0;">
            <div style="position:relative; display:flex; align-items:center;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; color:#94a3b8; font-size:13px;"></i>
                <input type="text" id="deals-search-input" oninput="window.handleDealsModalSearch(this.value)" placeholder="Search rewards, merchants..."
                    style="width:100%; padding:9px 12px 9px 34px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:12.5px; font-weight:600; color:#1e293b; background:#f8fafc; outline:none; transition:border-color 0.2s ease;"
                    onfocus="this.style.borderColor='#1e3a8a'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
        </div>

        <!-- Body Area (Scrollable Deals List) -->
        <div id="full-deals-list" class="hide-scrollbar"
            style="flex:1; overflow-y:auto; padding:8px 16px 18px; background:#ffffff !important;">
            <div style="text-align:center; padding:20px; color:#64748b; font-size:13px; font-weight:600;">Loading deals...</div>
        </div>
    </div>
</div>

<!-- Dedicated Active Voucher QR Pass Modal -->
<div id="active-voucher-qr-modal" onclick="if(event.target===this)window.closeActiveVoucherQrModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:2000002; justify-content:center; align-items:center; padding:20px;">
    <div
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:390px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,0.3) !important; overflow:hidden; text-align:center; padding:0;">
        <!-- Header Banner (Royal Blue) -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:9px;">
                <i class="fa-solid fa-qrcode" style="color:#00f2fe; font-size:18px;"></i>
                <h3 style="margin:0; color:#ffffff; font-size:17px; font-weight:800; letter-spacing:-0.2px;">Voucher QR
                    Pass</h3>
            </div>
            <button onclick="window.closeActiveVoucherQrModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="hide-scrollbar" style="flex:1; overflow-y:auto; padding:20px 18px; background:#ffffff !important;">
            <div id="active-qr-modal-title"
                style="font-size:17px; font-weight:900; color:#1e3a8a; margin-bottom:4px; line-height:1.3;">Tourist
                Voucher</div>
            <div id="active-qr-modal-partner"
                style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:14px;">Official Partner Merchant
            </div>

            <!-- QR Code Container -->
            <div
                style="background:#eff6ff; border:1.5px dashed #bfdbfe; border-radius:20px; padding:16px; margin-bottom:14px;">
                <div
                    style="background:#ffffff; border-radius:14px; padding:10px; width:170px; height:170px; margin:0 auto 12px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 14px rgba(0,0,0,0.08); border:1px solid #e2e8f0; position:relative;">
                    <img id="active-qr-modal-img" src="" alt="Voucher QR Code"
                        style="width:100%; height:100%; object-fit:contain;">
                </div>

                <!-- Verified Digital Pass Badge -->
                <div style="display:flex; align-items:center; justify-content:center; margin-bottom:10px;">
                    <span style="display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:100px; font-size:10.5px; font-weight:800; background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; text-transform:uppercase; letter-spacing:0.6px;">
                        <i class="fa-solid fa-shield-halved" style="color:#10b981; font-size:11px;"></i> Verified Digital QR Pass
                    </span>
                </div>

                <!-- Clickable Masked Code Container (Tap to Unmask/Mask) -->
                <div id="active-qr-mask-container" onclick="window.toggleActiveVoucherCodeMask()" role="button" tabindex="0" title="Tap to reveal or hide voucher code"
                    style="background:#ffffff; border:1.5px solid #bfdbfe; border-radius:14px; padding:10px 14px; margin-bottom:12px; box-shadow:0 2px 8px rgba(30,58,138,0.06); display:flex; align-items:center; justify-content:space-between; gap:10px; cursor:pointer; user-select:none; transition:all 0.2s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <div style="text-align:left; min-width:0; flex:1;">
                        <div style="font-size:9.5px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:5px; margin-bottom:2px;">
                            <i class="fa-solid fa-key" style="color:#0284c7; font-size:10px;"></i>
                            <span id="active-qr-mask-label">Voucher Code (Tap to reveal)</span>
                        </div>
                        <div id="active-qr-modal-code" style="font-size:16px; font-weight:900; color:#1e3a8a; letter-spacing:2.5px; font-family:monospace; word-break:break-all;">
                            ••••••••••••
                        </div>
                    </div>
                    <div id="active-qr-mask-btn" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; transition:all 0.15s ease;">
                        <i class="fa-solid fa-eye" id="active-qr-mask-icon" style="color:#1e3a8a;"></i>
                    </div>
                </div>

                <p id="active-qr-notice" style="margin:8px 0 0 0; font-size:11.5px; color:#64748b; line-height:1.45;">
                    Present this QR code directly to merchant or staff at checkout for instant scanning.
                </p>

                <div id="active-qr-status-badge"
                    style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:100px; font-size:11px; font-weight:800; background:#e0f2fe; color:#0369a1; margin-top:10px; transition:all 0.3s ease;">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> Ready for scan at checkout
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Completed Trip Details Modal -->
<div id="trip-details-modal" onclick="if(event.target===this)window.closeTripDetailsModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(6,11,25,0.85); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:2000000; justify-content:center; align-items:center; padding:20px;">
    <div
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:400px; max-height:82vh; display:flex; flex-direction:column; box-shadow:none !important; overflow:hidden; text-align:left; padding:0;">
        <!-- Header Banner -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border:none !important; outline:none !important; box-shadow:none !important; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div
                    style="width:34px; height:34px; border-radius:10px; background:#10b981; border:none !important; outline:none !important; box-shadow:none !important; display:flex; align-items:center; justify-content:center; color:#ffffff; font-size:15px;">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <div>
                    <div
                        style="font-size:10px; font-weight:800; color:#00f2fe; text-transform:uppercase; letter-spacing:0.5px;">
                        Finished Trip Details</div>
                    <h3 id="trip-detail-title"
                        style="margin:0; color:#ffffff; font-size:17px; font-weight:800; letter-spacing:-0.2px;">Trip
                        Details</h3>
                </div>
            </div>
            <button onclick="window.closeTripDetailsModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; box-shadow:none !important; transition:transform 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Body Area Below Header (Pure White, No Shadow, No Outlines) -->
        <div class="hide-scrollbar"
            style="flex:1; overflow-y:auto; padding:18px 16px; background:#ffffff !important; border:none !important; outline:none !important; box-shadow:none !important;">
            <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
                <span id="trip-detail-date"
                    style="font-size:11.5px; color:#1e3a8a; background:#f1f5f9; border:none !important; outline:none !important; box-shadow:none !important; padding:6px 12px; border-radius:100px; font-weight:700;"><i
                        class="fa-regular fa-calendar" style="color:#0284c7; margin-right:5px;"></i>--</span>
                <span id="trip-detail-cost"
                    style="font-size:11.5px; color:#1e3a8a; background:#f1f5f9; border:none !important; outline:none !important; box-shadow:none !important; padding:6px 12px; border-radius:100px; font-weight:700;"><i
                        class="fa-solid fa-coins" style="color:#d97706; margin-right:5px;"></i>₱0.00</span>
                <span id="trip-detail-count"
                    style="font-size:11.5px; color:#ffffff; background:#10b981; border:none !important; outline:none !important; box-shadow:none !important; padding:6px 12px; border-radius:100px; font-weight:800;"><i
                        class="fa-solid fa-location-dot" style="margin-right:5px; color:#ffffff;"></i>0 Visited</span>
            </div>

            <div
                style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
                Destinations Visited</div>
            <div id="trip-detail-destinations-list" style="padding-right:0;">
                <div style="text-align:center; padding:16px; color:#94a3b8; font-size:12px;">Loading destinations...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reward Details Modal -->
<div id="reward-details-modal" onclick="if(event.target===this)window.closeRewardDetailsModal()"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.65); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:2000000; justify-content:center; align-items:center; padding:20px; opacity:0; transition:opacity 0.25s ease;">
    <div
        style="background:#ffffff !important; border:none !important; outline:none !important; border-radius:24px; width:100%; max-width:350px; max-height:86vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 16px 40px rgba(10,25,60,0.45); text-align:center; transform:scale(0.88); transition:transform 0.25s cubic-bezier(0.16,1,0.3,1); position:relative; padding:0;">
        <!-- Header Banner (Royal Blue) -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:14px 18px; flex-shrink:0; display:flex; justify-content:space-between; align-items:center; border:none !important;">
            <div style="text-align:left;">
                <div style="font-size:15px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-gift" style="color:#00f2fe; font-size:14px;"></i> Reward Details
                </div>
                <span id="reward-modal-badge"
                    style="font-size:9.5px; font-weight:800; color:#1e3a8a !important; background:#eff6ff !important; padding:2px 7px; border-radius:6px; margin-top:3px; display:inline-block; text-transform:uppercase;">PROMO</span>
            </div>
            <button type="button" onclick="window.closeRewardDetailsModal()"
                style="background:#ffffff !important; border:none !important; outline:none !important; color:#1e3a8a !important; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px; box-shadow:0 2px 8px rgba(0,0,0,0.18);">
                <i class="fa-solid fa-xmark" style="color:#1e3a8a !important;"></i>
            </button>
        </div>

        <!-- Middle Body (White) -->
        <div class="hide-scrollbar"
            style="background:#ffffff !important; flex:1; min-height:0; overflow-y:auto; padding:18px; color:#0f172a;">
            <div
                style="width:68px; height:68px; border-radius:18px; background:#ffffff; border:1.5px solid #e2e8f0; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <img id="reward-modal-img" src="" alt="Reward Logo"
                    style="width:100%; height:100%; object-fit:contain; padding:6px;"
                    onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
            </div>
            <h4 id="reward-modal-title"
                style="margin:0 0 4px; font-size:17px; font-weight:800; color:#0f172a; line-height:1.3;">Reward Details
            </h4>
            <p
                style="margin:0 0 14px; font-size:12.5px; color:#1e3a8a; font-weight:700; display:flex; align-items:center; justify-content:center; gap:4px;">
                <i class="fa-solid fa-store" style="font-size:11px; color:#0284c7;"></i> <span
                    id="reward-modal-partner-name">Partner</span>
            </p>
            <div id="reward-modal-id-notice"
                style="display:none; background:#fef2f2 !important; border:1px solid #fecaca !important; border-radius:12px; padding:10px 12px; margin-bottom:12px; text-align:left;">
                <div
                    style="display:flex; align-items:center; gap:6px; color:#dc2626 !important; font-size:11.5px; font-weight:800;">
                    <i class="fa-solid fa-id-card"></i> Valid ID Required Upon Redemption
                </div>
            </div>
            <div
                style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px 14px; margin-bottom:6px; text-align:left;">
                <div
                    style="font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:#64748b; margin-bottom:4px;">
                    Description & Terms</div>
                <p id="reward-modal-desc" style="margin:0; font-size:12px; color:#334155; line-height:1.45;"></p>
            </div>
        </div>

        <!-- Footer Banner (Royal Blue) -->
        <div
            style="background:linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding:14px 18px; flex-shrink:0; border:none !important;">
            <button type="button" id="reward-modal-redeem-btn"
                style="width:100%; padding:12px; border:none !important; outline:none !important; border-radius:12px; background:#ffffff !important; color:#1e3a8a !important; font-size:13.5px; font-weight:900; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.18) !important; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fa-solid fa-gift" style="color:#1e3a8a;"></i> <span style="color:#1e3a8a;">Redeem for <strong
                        id="reward-modal-cost-text" style="color:#1e3a8a;">-- Points</strong></span>
            </button>
        </div>
    </div>
</div>