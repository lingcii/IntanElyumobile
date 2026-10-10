<!-- Leaderboard View -->
<?php
$pageTitle = 'Leaderboards';
$activeTab = 'leaderboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>


<div class="leaderboard-container has-header has-bottom-nav animate-fade-in">

    <!-- Title & Season Header -->
    <div class="leaderboard-title stagger-0">
        <h2>
            <i class="fa-solid fa-trophy" style="color: #fbbf24;"></i> La Union Top Explorers
        </h2>
        <p>Earn Points across Elyu</p>
    </div>

    <!-- Your Current Standing Banner -->
    <div id="my-standing-banner" class="standing-banner-card stagger-1" style="display: none;"
        onclick="if(window.myUserData) showUserProfile(window.myUserData.name, window.myUserData.avatar, window.myUserData.pts, window.myUserData.rank, window.myUserData.activities, window.myUserData.location, window.myUserData.bio, true)">
        <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
            <div class="standing-rank-avatar-wrap">
                <img id="my-standing-avatar" class="standing-rank-avatar"
                    src="https://ui-avatars.com/api/?name=You&background=007AFF&color=fff&rounded=true&bold=true&size=128"
                    alt="You">
                <div id="my-rank-circle" class="standing-rank-badge">#--</div>
            </div>
            <div class="standing-info">
                <div class="standing-name-row">
                    <span id="my-explorer-title"
                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Your Standing</span>
                    <span
                        style="font-size: 9.5px; background: rgba(0, 242, 254, 0.2); color: #00f2fe; padding: 2px 7px; border-radius: 100px; font-weight: 800; border: none !important; outline: none !important;">YOU</span>
                </div>
                <div class="standing-subtext" id="my-standing-subtext">Loading rank details...</div>
            </div>
        </div>
        <button class="standing-action-btn" onclick="event.stopPropagation(); navigateTo('map');">
            <i class="fa-solid fa-compass"></i> Explore
        </button>
    </div>

    <!-- Sort Filter Tabs -->
    <div class="leaderboard-tabs-wrapper mode-points stagger-1">
        <div class="leaderboard-tab-glider" id="tab-glider"></div>
        <button class="leaderboard-tab-btn active" id="tab-sort-points" onclick="setLeaderboardSort('points')">
            Points
        </button>
        <button class="leaderboard-tab-btn" id="tab-sort-visited" onclick="setLeaderboardSort('visited')">
            Visited
        </button>
    </div>

    <!-- Podium (Top 3) -->
    <div class="podium-container stagger-1" id="podium-container">
        <!-- Injected via JS -->
    </div>

    <!-- Rank List Section -->
    <div id="rank-list-section">
        <div class="stagger-2"
            style="display: flex; justify-content: space-between; align-items: center; margin: 20px 4px 12px 4px;">
            <h3
                style="font-size: 15px; font-weight: 800; color: #0f172a !important; margin: 0; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-list-ol" style="color: #1e3a8a;"></i> Explorer Leaderboard
            </h3>
            <span style="font-size: 11px; font-weight: 700; color: #64748b !important;" id="explorers-count-badge">Top
                Ranks</span>
        </div>

        <!-- Rank List -->
        <div class="rank-list-wrapper stagger-2" id="rank-list-wrapper">
            <div class="rank-list" id="rank-list-container">
                <!-- Injected via JS -->
            </div>
            <button class="btn-invite-friends"
                onclick="if(navigator.share){navigator.share({title:'La Union Top Explorers',url:window.location.href});}else if(typeof showToast==='function'){showToast('Leaderboard link copied to clipboard!');}">
                <i class="fa-solid fa-user-plus" style="color:#ffffff;"></i> Invite Friends & Compete
            </button>
        </div>
    </div>

</div>

<!-- User Profile Modal (Notification Design) -->
<div id="user-profile-modal" class="profile-modal-overlay" onclick="if(event.target===this) closeUserProfile();">
    <div class="profile-modal-card">
        <!-- Notification Header -->
        <div class="profile-modal-header">
            <div style="flex: 1; min-width: 0;">
                <div class="profile-modal-badge">
                    <i class="fa-solid fa-trophy" style="font-size: 10px;"></i>
                    <span id="modal-header-badge">EXPLORER PROFILE</span>
                </div>
                <div id="modal-header-status" class="profile-modal-time">Leaderboard Standing</div>
            </div>
            <button type="button" class="profile-modal-close" onclick="closeUserProfile()" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Explorer Identity -->
        <div class="profile-modal-body">
            <div class="profile-avatar-wrapper">
                <img id="modal-avatar" src="" alt="Avatar">
                <div id="modal-rank-badge" class="modal-rank-badge">1</div>
            </div>
            <h2 id="modal-name">Explorer</h2>
            <div class="profile-tags-row">
                <span id="modal-rank-pill" class="profile-rank-pill">#1 Ranked Explorer</span>
                <div id="modal-location" class="profile-location-pill" style="display: none;">
                    <i class="fa-solid fa-location-dot"></i><span>Hometown</span>
                </div>
            </div>
            <p id="modal-bio" class="profile-bio-box" style="display: none;"></p>
        </div>

        <!-- 3 Stats Boxes Grid -->
        <div class="modal-stats">
            <div class="modal-stat-box">
                <i class="fa-solid fa-coins" style="color:#fbbf24;"></i>
                <span id="modal-pts" style="color:#fbbf24;">0</span>
                <small>Points</small>
            </div>
            <div class="modal-stat-box">
                <i class="fa-solid fa-map-location-dot" style="color:#34c759;"></i>
                <span id="modal-activities">0</span>
                <small>Spots</small>
            </div>
            <div class="modal-stat-box">
                <i class="fa-solid fa-trophy" style="color:#00f2fe;"></i>
                <span id="modal-rank-num" style="color:#00f2fe;">#1</span>
                <small>Rank</small>
            </div>
        </div>

        <!-- Action Buttons (Notification Style) -->
        <div class="profile-modal-actions">
            <button id="modal-cheer-btn" type="button" class="profile-modal-btn-white"
                onclick="if(typeof showToast==='function'){showToast('Sent High Five! 🎉');} closeUserProfile();">
                <i class="fa-solid fa-hand-peace"></i> Send High Five
            </button>
            <button id="modal-dismiss-btn" type="button" class="profile-modal-btn-dismiss" onclick="closeUserProfile()">
                Dismiss
            </button>
        </div>
    </div>
</div>

<script>
    var currentSortMode = 'points';
    var rawLeadersList = [];
    var cachedMeData = null;
    var cachedMyRank = 999;

    function setLeaderboardSort(mode) {
        if (currentSortMode === mode && arguments[1] !== true) return;
        currentSortMode = mode;

        const tabsWrapper = document.querySelector('.leaderboard-tabs-wrapper');
        if (tabsWrapper) {
            tabsWrapper.classList.remove('mode-points', 'mode-visited');
            tabsWrapper.classList.add(`mode-${mode}`);
        }

        const tabPoints = document.getElementById('tab-sort-points');
        const tabVisited = document.getElementById('tab-sort-visited');

        if (tabPoints) tabPoints.classList.toggle('active', mode === 'points');
        if (tabVisited) tabVisited.classList.toggle('active', mode === 'visited');

        renderLeaderboardUI();
    }
    window.setLeaderboardSort = setLeaderboardSort;

    function renderLeaderboardUI() {
        const podiumContainer = document.getElementById('podium-container');
        const rankListContainer = document.getElementById('rank-list-container');
        if (!podiumContainer && !rankListContainer) return;
        if (!rawLeadersList) return;

        // Filter out users based on active sort mode
        let leaders = (rawLeadersList || []).filter(u => {
            const pts = parseInt(u.points || u.pts || u.total_points || u.claimable_points || 0);
            const act = parseInt(u.completed_activities || u.places_visited || 0);
            if (currentSortMode === 'visited') return act > 0;
            return pts > 0;
        });

        // Sort items based on current sort mode
        if (currentSortMode === 'visited') {
            leaders.sort((a, b) => {
                const actA = parseInt(a.completed_activities || a.places_visited || 0);
                const actB = parseInt(b.completed_activities || b.places_visited || 0);
                if (actB !== actA) return actB - actA;
                const ptsA = parseInt(a.points || a.pts || a.total_points || a.claimable_points || 0);
                const ptsB = parseInt(b.points || b.pts || b.total_points || b.claimable_points || 0);
                return ptsB - ptsA;
            });
        } else {
            leaders.sort((a, b) => {
                const ptsA = parseInt(a.points || a.pts || a.total_points || a.claimable_points || 0);
                const ptsB = parseInt(b.points || b.pts || b.total_points || b.claimable_points || 0);
                if (ptsB !== ptsA) return ptsB - ptsA;
                const actA = parseInt(a.completed_activities || a.places_visited || 0);
                const actB = parseInt(b.completed_activities || b.places_visited || 0);
                return actB - actA;
            });
        }

        const countBadge = document.getElementById('explorers-count-badge');
        if (countBadge) {
            countBadge.textContent = leaders.length > 0 ? `Top ${Math.min(leaders.length, 10)} Explorers` : '0 Explorers';
        }

        // Render Standing Banner
        const banner = document.getElementById('my-standing-banner');
        const rankCircle = document.getElementById('my-rank-circle');
        const subtext = document.getElementById('my-standing-subtext');
        const titleEl = document.getElementById('my-explorer-title');
        const avatarEl = document.getElementById('my-standing-avatar');

        if (banner) {
            banner.style.display = 'flex';
            const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
            const myPts = cachedMeData ? parseInt(cachedMeData.points ?? cachedMeData.pts ?? cachedMeData.total_points ?? cachedMeData.claimable_points ?? 0) : (authUser.points || 0);
            const myActivities = cachedMeData ? parseInt(cachedMeData.completed_activities ?? cachedMeData.places_visited ?? 0) : 0;
            const isUnranked = (myPts === 0 && myActivities === 0);
            const myRankNum = (!isUnranked && cachedMyRank && cachedMyRank < 999) ? cachedMyRank : (isUnranked ? '—' : 1);
            const myDisplayName = isUnranked ? 'Unranked Explorer' : `${myRankNum}# Explorer`;
            const myRawName = (cachedMeData ? (cachedMeData.name || cachedMeData.full_name) : (authUser.name || authUser.full_name || 'Explorer')).replace(/[^a-zA-Z\s]/g, '').trim() || 'Explorer';
            const myAvatar = cachedMeData && cachedMeData.avatar ? cachedMeData.avatar : (authUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(myRawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);

            window.myUserData = {
                name: myDisplayName,
                avatar: myAvatar,
                pts: myPts,
                rank: myRankNum,
                activities: myActivities,
                location: cachedMeData ? (cachedMeData.home_location || '') : '',
                bio: cachedMeData ? (cachedMeData.bio || '') : ''
            };

            if (avatarEl) avatarEl.src = myAvatar;
            if (titleEl) titleEl.textContent = myDisplayName;
            if (rankCircle) {
                if (isUnranked) {
                    rankCircle.textContent = '—';
                } else if (cachedMyRank && cachedMyRank < 999) {
                    rankCircle.textContent = '#' + cachedMyRank;
                } else {
                    rankCircle.textContent = '★';
                }
            }
            if (subtext) {
                if (currentSortMode === 'visited') {
                    subtext.textContent = `${myActivities} Spots Visited • ${myPts.toLocaleString()} Points`;
                } else {
                    subtext.textContent = `${myPts.toLocaleString()} Points • ${myActivities} Spots Visited`;
                }
            }
        }

        // Render Podium (1st, 2nd, 3rd)
        let podiumHTML = '';
        if (leaders.length === 0) {
            let emptySubtext = 'Visit attractions and play games to earn Points and claim the #1 spot on the leaderboard!';
            if (currentSortMode === 'visited') {
                emptySubtext = 'Visit attractions across La Union and check in to claim the #1 spot on the leaderboard!';
            }
            if (podiumContainer) {
                podiumContainer.innerHTML = `
                        <div style="grid-column: 1 / -1; width: 100%; text-align: center; padding: 24px 16px; background: transparent; border:none !important; outline:none !important;">
                            <div style="width: 52px; height: 52px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #d97706; font-size: 22px; border:none !important;">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                            <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 5px;">No Ranked Explorers Yet</div>
                            <div style="font-size: 12px; color: #64748b; max-width: 270px; margin: 0 auto; line-height: 1.4;">
                                ${emptySubtext}
                            </div>
                        </div>
                    `;
            }
        } else {
            if (leaders[1]) podiumHTML += generatePodiumPlace(leaders[1], 2);
            if (leaders[0]) podiumHTML += generatePodiumPlace(leaders[0], 1);
            if (leaders[2]) podiumHTML += generatePodiumPlace(leaders[2], 3);
            if (podiumContainer) podiumContainer.innerHTML = podiumHTML;
        }

        // Render Rank List (Ranks 4 to 10 - capped strictly at 10 total)
        let rankListHTML = '';
        if (leaders.length > 3) {
            for (let i = 3; i < Math.min(leaders.length, 10); i++) {
                const user = leaders[i];
                const isMe = cachedMeData && (user.id === cachedMeData.id || user.user_id === cachedMeData.id);
                rankListHTML += generateRankItem(user, i + 1, isMe);
            }
        }

        if (cachedMeData && cachedMyRank > 10 && cachedMyRank <= 999 && !isUnranked) {
            rankListHTML += generateRankItem(cachedMeData, cachedMyRank, true);
        }

        const rankListSec = document.getElementById('rank-list-section');
        if (rankListHTML && rankListHTML.trim() !== '') {
            if (rankListContainer) rankListContainer.innerHTML = rankListHTML;
        } else {
            if (rankListContainer) {
                rankListContainer.innerHTML = '<div style="text-align:center; padding: 18px 12px; color: rgba(148,163,184,0.7); font-size: 13px; font-weight: 600;"><i class="fa-solid fa-users-slash" style="margin-right:6px;"></i> No additional ranked explorers yet.</div>';
            }
        }
        if (rankListSec) rankListSec.style.display = 'block';
    }

    function getUserDisplayName(user, rank) {
        if (rank) {
            return `${rank}# Explorer`;
        }
        const idToUse = user.rank || user.user_id || user.id || 1;
        return `${idToUse}# Explorer`;
    }

    function generatePodiumPlace(user, rank) {
        const displayName = getUserDisplayName(user, rank);
        const rawName = (user.name || user.full_name || user.real_name || 'Explorer').replace(/[^a-zA-Z\s]/g, '').trim() || 'Explorer';
        let avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(rawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        if (avatarUrl && !avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:')) {
            avatarUrl = (window.backendUrl || '') + '/' + avatarUrl.replace(/^\//, '');
        }

        let medalIcon = '';
        let stepHeight = 'clamp(50px, 7vh, 70px)';
        let badgeLabel = '3RD';

        if (rank === 1) {
            medalIcon = `<div class="podium-crown-icon"><i class="fa-solid fa-crown" style="color:#FFD700; font-size:26px;"></i></div>`;
            stepHeight = 'clamp(80px, 11vh, 112px)';
            badgeLabel = '1ST';
        } else if (rank === 2) {
            medalIcon = `<div class="podium-medal-icon"><i class="fa-solid fa-medal" style="color:#e2e8f0; font-size:20px;"></i></div>`;
            stepHeight = 'clamp(64px, 9vh, 88px)';
            badgeLabel = '2ND';
        } else if (rank === 3) {
            medalIcon = `<div class="podium-medal-icon"><i class="fa-solid fa-award" style="color:#fb923c; font-size:20px;"></i></div>`;
            stepHeight = 'clamp(50px, 7vh, 70px)';
            badgeLabel = '3RD';
        }

        const isMe = Boolean(cachedMeData && (user.id === cachedMeData.id || user.user_id === cachedMeData.id));
        const safeName = displayName.replace(/'/g, "\\'");
        const pts = parseInt(user.points ?? user.pts ?? user.total_points ?? user.claimable_points ?? 0);
        const activities = parseInt(user.completed_activities ?? user.places_visited ?? 0);
        const safeLocation = (user.home_location || '').replace(/'/g, "\\'");
        const safeBio = (user.bio || '').replace(/'/g, "\\'");

        let iconHtml = '';
        let textMetric = '';

        if (currentSortMode === 'visited') {
            iconHtml = '<i class="fa-solid fa-map-location-dot" style="font-size:10px;"></i>';
            textMetric = `${activities} Visited`;
        } else {
            iconHtml = '<i class="fa-solid fa-coins" style="font-size:10px; color:#fbbf24;"></i>';
            textMetric = `${pts.toLocaleString()} PTS`;
        }

        const metricPillHtml = `
            <div class="podium-metric-pill podium-metric-${rank}" style="margin-bottom:10px;">
                ${iconHtml} ${textMetric}
            </div>`;

        return `
        <div class="podium-place rank-${rank}" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${pts}, ${rank}, ${activities}, '${safeLocation}', '${safeBio}', ${isMe})">
            <div class="podium-avatar-wrap">
                ${medalIcon}
                <img src="${avatarUrl}" alt="${displayName}" class="podium-avatar" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(rawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128';">
                <div class="podium-rank-badge rank-badge-${rank}">${rank}</div>
            </div>
            <div class="podium-name">${displayName}</div>
            ${metricPillHtml}
            <div class="podium-block block-${rank}" style="height:${stepHeight};">
                <span class="block-label">${badgeLabel}</span>
            </div>
        </div>`;
    }

    function generateRankItem(user, rank, isMe) {
        const displayName = getUserDisplayName(user, rank);
        const rawName = (user.name || user.full_name || user.real_name || 'Explorer').replace(/[^a-zA-Z\s]/g, '').trim() || 'Explorer';
        let avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(rawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        if (avatarUrl && !avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:')) {
            avatarUrl = (window.backendUrl || '') + '/' + avatarUrl.replace(/^\//, '');
        }

        const activeClass = isMe ? 'is-me' : '';
        const youTag = isMe ? `<span style="font-size:9px; background:linear-gradient(135deg, #38bdf8, #2563eb); color:white; padding:2px 7px; border-radius:100px; font-weight:800; margin-left:6px;">YOU</span>` : '';
        const delay = 0.15 + ((rank - 4) * 0.03);

        const safeName = displayName.replace(/'/g, "\\'");
        const pts = parseInt(user.points ?? user.pts ?? user.total_points ?? user.claimable_points ?? 0);
        const activities = parseInt(user.completed_activities ?? user.places_visited ?? 0);
        const safeLocation = (user.home_location || '').replace(/'/g, "\\'");
        const safeBio = (user.bio || '').replace(/'/g, "\\'");

        let rightBadgeHtml = '';
        let subMetaText = `<span>${activities} Spots Visited</span>`;

        if (currentSortMode === 'visited') {
            rightBadgeHtml = `
            <div class="rank-metric-badge" style="color:#38bdf8;">
                ${activities} <small style="font-size:10px; font-weight:700; color:rgba(56,189,248,0.85); margin-left:3px;">VISITED</small>
            </div>`;
        } else {
            rightBadgeHtml = `
            <div class="rank-metric-badge" style="color:#fbbf24;">
                <i class="fa-solid fa-coins" style="font-size:11px; margin-right:3px;"></i>${pts.toLocaleString()} <small style="font-size:10px; font-weight:700; color:rgba(251,191,36,0.85); margin-left:3px;">PTS</small>
            </div>`;
        }

        return `
        <div class="rank-item ${activeClass}" style="animation-delay: ${Math.max(0, delay)}s;" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${pts}, ${rank}, ${activities}, '${safeLocation}', '${safeBio}', ${Boolean(isMe)})">
            <div style="display: flex; align-items: center; min-width: 0; flex: 1;">
                <img src="${avatarUrl}" alt="${displayName}" class="rank-avatar" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(rawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128';">
                <div class="rank-info">
                    <div class="rank-user-name">
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${displayName}</span>
                        ${youTag}
                    </div>
                    <div class="rank-user-meta">
                        ${subMetaText}
                    </div>
                </div>
            </div>
            ${rightBadgeHtml}
        </div>`;
    }

    window.showUserProfile = function (name, avatar, pts, rank, activities, location, bio, isMe = false) {
        // Determine if the viewed profile is the current logged-in user
        const isSelf = Boolean(
            isMe === true ||
            (window.myUserData && window.myUserData.name && name === window.myUserData.name) ||
            (window.myUserData && name === 'Unranked Explorer') ||
            (name && (name.includes('(You)') || name.includes('Your Standing'))) ||
            (cachedMeData && (
                (cachedMeData.name && name === cachedMeData.name) ||
                (cachedMeData.full_name && name === cachedMeData.full_name) ||
                (cachedMeData.username && name === cachedMeData.username) ||
                (cachedMeData.id && String(rank).includes(String(cachedMyRank)))
            ))
        );

        let displayPts = Number(pts || 0);
        if (isSelf) {
            const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
            const knownPts = cachedMeData ? parseInt(cachedMeData.points ?? cachedMeData.pts ?? cachedMeData.total_points ?? 0) : parseInt(authUser.points || 0);
            if (knownPts > displayPts) {
                displayPts = knownPts;
            }
            if (window.myUserData && window.myUserData.pts && window.myUserData.pts > displayPts) {
                displayPts = window.myUserData.pts;
            }
        }

        document.getElementById('modal-avatar').src = avatar;
        document.getElementById('modal-name').innerText = name;
        document.getElementById('modal-pts').innerText = displayPts.toLocaleString();
        document.getElementById('modal-rank-badge').innerText = rank;
        const rankNumEl = document.getElementById('modal-rank-num');
        if (rankNumEl) rankNumEl.innerText = rank ? (String(rank).startsWith('#') ? rank : '#' + rank) : '—';
        document.getElementById('modal-activities').innerText = activities ? Number(activities).toLocaleString() : '0';

        // When viewing self, fetch fresh live balance to guarantee accuracy
        if (isSelf) {
            const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            if (token) {
                const backendUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
                fetch(backendUrl + '/api/tourist/points/balance', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                }).then(r => r.json()).then(d => {
                    if (d && d.status === 'success' && d.points !== undefined) {
                        const livePts = parseInt(d.points);
                        const ptsEl = document.getElementById('modal-pts');
                        if (ptsEl) ptsEl.innerText = livePts.toLocaleString();
                        if (window.myUserData) window.myUserData.pts = livePts;
                        const subtext = document.getElementById('my-standing-subtext');
                        if (subtext && window.myUserData) {
                            subtext.textContent = `${livePts.toLocaleString()} Points • ${window.myUserData.activities || 0} Spots Visited`;
                        }
                        try {
                            const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                            authUser.points = livePts;
                            localStorage.setItem('auth_user', JSON.stringify(authUser));
                        } catch (e) { }
                    }
                }).catch(() => { });
            }
        }

        const rankPill = document.getElementById('modal-rank-pill');
        if (rankPill) {
            if (isSelf) {
                rankPill.innerText = (rank && rank !== '—') ? `#${rank} Ranked Explorer (You)` : `Your Explorer Profile`;
            } else {
                rankPill.innerText = `#${rank} Ranked Explorer`;
            }
        }

        // Notification Header Badge & Status
        const headerBadge = document.getElementById('modal-header-badge');
        if (headerBadge) {
            headerBadge.textContent = isSelf ? 'YOUR PROFILE' : 'EXPLORER PROFILE';
        }
        const headerStatus = document.getElementById('modal-header-status');
        if (headerStatus) {
            headerStatus.textContent = isSelf ? 'Your Standing' : 'Leaderboard Standing';
        }

        // Yourself should not be sending high fives to yourself
        const cheerBtn = document.getElementById('modal-cheer-btn');
        if (cheerBtn) {
            cheerBtn.style.display = isSelf ? 'none' : 'flex';
        }
        const dismissBtn = document.getElementById('modal-dismiss-btn');
        if (dismissBtn) {
            if (isSelf) {
                dismissBtn.classList.add('is-self');
                dismissBtn.textContent = 'Close';
            } else {
                dismissBtn.classList.remove('is-self');
                dismissBtn.textContent = 'Dismiss';
            }
        }

        const elLoc = document.getElementById('modal-location');
        if (elLoc) {
            if (location && location.trim()) {
                elLoc.querySelector('span').innerText = location;
                elLoc.style.display = 'inline-flex';
            } else {
                elLoc.style.display = 'none';
            }
        }

        const elBio = document.getElementById('modal-bio');
        if (elBio) {
            if (bio && bio.trim()) {
                elBio.innerText = `"${bio}"`;
                elBio.style.display = 'block';
            } else {
                elBio.style.display = 'none';
            }
        }

        document.getElementById('user-profile-modal').classList.add('active');
    };

    window.closeUserProfile = function () {
        document.getElementById('user-profile-modal').classList.remove('active');
    };

    window.initLeaderboardView = async function () {
        const podiumContainer = document.getElementById('podium-container');
        const rankListContainer = document.getElementById('rank-list-container');

        // 1. If we already have leaders in memory, render immediately!
        if (rawLeadersList && rawLeadersList.length > 0) {
            renderLeaderboardUI();
        }

        try {
            const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const headers = { 'Accept': 'application/json' };

            var backendUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
            let url = backendUrl + '/api/public/leaderboard?limit=50';
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
                url = backendUrl + '/api/tourist/leaderboard?limit=50';
            }

            // In parallel, fetch live points balance if user is authenticated
            if (token) {
                fetch(backendUrl + '/api/tourist/points/balance', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                }).then(r => r.json()).then(d => {
                    if (d && d.status === 'success' && d.points !== undefined) {
                        const ptsVal = parseInt(d.points);
                        if (window.myUserData) {
                            window.myUserData.pts = ptsVal;
                            const subtext = document.getElementById('my-standing-subtext');
                            if (subtext) {
                                if (currentSortMode === 'visited') {
                                    subtext.textContent = `${window.myUserData.activities || 0} Spots Visited • ${ptsVal.toLocaleString()} Points`;
                                } else {
                                    subtext.textContent = `${ptsVal.toLocaleString()} Points • ${window.myUserData.activities || 0} Spots Visited`;
                                }
                            }
                        }
                    }
                }).catch(() => { });
            }

            const cacheKey = 'leaderboard_data_v15_' + (token ? token.substring(0, 10) : 'public');
            const fetchCache = window.useCache || (async (key, fetcher, renderer) => { const d = await fetcher(); if (renderer) renderer(d); return d; });

            // Ambient offline check: Pre-hydrate from cache if available
            try {
                const storedRaw = localStorage.getItem(cacheKey);
                if (storedRaw) {
                    const parsed = JSON.parse(storedRaw);
                    const d = parsed.data || parsed;
                    if (d && (d.users || d.leaders)) {
                        rawLeadersList = d.users || d.leaders || [];
                        cachedMeData = d.me || null;
                        cachedMyRank = d.my_rank || 999;
                        renderLeaderboardUI();
                    }
                }
            } catch (e) { }

            // If offline and still no leaders list, render user personal standing and ambient offline message
            if (!navigator.onLine && (!rawLeadersList || rawLeadersList.length === 0)) {
                renderLeaderboardUI();
                if (podiumContainer) {
                    podiumContainer.innerHTML = `
                        <div style="grid-column: 1 / -1; width: 100%; text-align: center; padding: 28px 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #f59e0b; font-size: 20px;">
                                <i class="fa-solid fa-cloud-slash"></i>
                            </div>
                            <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Rankings Offline</div>
                            <div style="font-size: 12px; color: #64748b; max-width: 260px; margin: 0 auto; line-height: 1.4;">
                                Full provincial rankings will update automatically when reconnected.
                            </div>
                        </div>`;
                }
                if (rankListContainer) {
                    rankListContainer.innerHTML = '<div style="text-align:center; padding: 18px 12px; color: #94a3b8; font-size: 12px; font-weight: 600;"><i class="fa-solid fa-cloud-slash" style="margin-right:6px; color:#f59e0b;"></i> Live standings will sync once your connection is restored.</div>';
                }
                return;
            }

            await fetchCache(
                cacheKey,
                async () => {
                    let res = await fetch(url, { headers: { ...headers } });
                    if (res.status === 401 && token) {
                        localStorage.removeItem('intan_elyu_token');
                        localStorage.removeItem('Intan_Elyu_Token');
                        localStorage.removeItem('api_token');
                        res = await fetch(backendUrl + '/api/public/leaderboard?limit=50', { headers: { 'Accept': 'application/json' } });
                    }
                    if (!res.ok) throw new Error("Failed to fetch leaderboard");
                    return await res.json();
                },
                (data) => {
                    if (!data) return;
                    rawLeadersList = data.users || data.leaders || [];
                    cachedMeData = data.me || null;
                    cachedMyRank = data.my_rank || 999;
                    renderLeaderboardUI();
                },
                Boolean(window.leaderboardNeedsRefresh),
                30000 // 30 seconds TTL
            );
            window.leaderboardNeedsRefresh = false;

            // 2. Re-render after fetch to ensure latest data is displayed
            if (rawLeadersList && rawLeadersList.length > 0) {
                renderLeaderboardUI();
            }

        } catch (e) {
            console.warn("Leaderboard fetch error:", e);
            if (!navigator.onLine) {
                renderLeaderboardUI();
                if (podiumContainer && (!rawLeadersList || rawLeadersList.length === 0)) {
                    podiumContainer.innerHTML = `
                        <div style="grid-column: 1 / -1; width: 100%; text-align: center; padding: 28px 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #f59e0b; font-size: 20px;">
                                <i class="fa-solid fa-cloud-slash"></i>
                            </div>
                            <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Rankings Offline</div>
                            <div style="font-size: 12px; color: #64748b; max-width: 260px; margin: 0 auto; line-height: 1.4;">
                                Full provincial rankings will update automatically when reconnected.
                            </div>
                        </div>`;
                }
            } else {
                if (podiumContainer && (!rawLeadersList || rawLeadersList.length === 0)) {
                    podiumContainer.innerHTML = "<div style='color:rgba(239,68,68,0.8); text-align:center; width:100%; padding:20px; font-size:14px;'>Failed to load leaderboard.</div>";
                }
            }
        }
    };

    // Initialize immediately
    window.initLeaderboardView();

    // Ambient automatic reconnect listener - quietly syncs rankings without user buttons or reloads
    window.addEventListener('online', function () {
        if (typeof window.initLeaderboardView === 'function') {
            window.leaderboardNeedsRefresh = true;
            window.initLeaderboardView();
        }
    });

    // Also listen for SPA viewLoaded event when navigating back to leaderboard
    document.addEventListener('viewLoaded', function (e) {
        if (e.detail && e.detail.view === 'leaderboard') {
            if (typeof window.initLeaderboardView === 'function') {
                window.initLeaderboardView();
            }
        }
    });
</script>