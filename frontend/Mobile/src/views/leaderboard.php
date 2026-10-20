<!-- Leaderboard View -->
<?php
$pageTitle = 'Leaderboards';
$activeTab = 'leaderboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>


<div class="leaderboard-container has-header has-bottom-nav animate-fade-in">

    <!-- Offline status badge -->
    <div id="leaderboard-offline-badge" style="display: none; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; padding: 7px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-bottom: 12px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <i class="fa-solid fa-cloud-arrow-down" style="margin-right: 5px;"></i> Showing Cached Season Standings (Offline)
    </div>

    <!-- Title & Season Header -->
    <div class="leaderboard-title stagger-0" style="position: relative;">
        <h2>
            <i class="fa-solid fa-trophy" style="color: #fbbf24;"></i> La Union Top Explorers
        </h2>
        <p>Earn Points across Elyu</p>
        <button id="leaderboard-refresh-btn" type="button" onclick="window.refreshLeaderboard(true)" aria-label="Refresh Leaderboard"
            style="position: absolute; right: 4px; top: 2px; background: rgba(30, 58, 138, 0.08); border: none; color: #1e3a8a; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; cursor: pointer; transition: transform 0.25s ease;"
            onpointerdown="this.style.transform='rotate(180deg) scale(0.9)'" onpointerup="this.style.transform='rotate(0deg) scale(1)'">
            <i class="fa-solid fa-rotate-right" id="leaderboard-refresh-icon"></i>
        </button>
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
            <span style="font-size: 11px; font-weight: 700; color: #64748b !important;" id="explorers-count-badge">Top 10</span>
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
    var currentSortMode = window._cachedLeaderboardSortMode || 'points';
    var rawLeadersList = window._cachedRawLeadersList || [];
    var cachedMeData = window._cachedMeData || null;
    var cachedMyRank = (window._cachedMyRank !== undefined && window._cachedMyRank !== null) ? window._cachedMyRank : 999;

    function setLeaderboardSort(mode) {
        if (currentSortMode === mode && arguments[1] !== true) return;
        currentSortMode = mode;
        window._cachedLeaderboardSortMode = mode;

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

    function renderLeaderboardLoading() {
        const podiumContainer = document.getElementById('podium-container');
        const rankListContainer = document.getElementById('rank-list-container');
        const banner = document.getElementById('my-standing-banner');

        if (banner) {
            banner.style.display = 'flex';
            const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
            const myRawName = String((cachedMeData && (cachedMeData.name || cachedMeData.full_name)) || (authUser && (authUser.name || authUser.full_name)) || 'Explorer').trim() || 'Explorer';
            const titleEl = document.getElementById('my-explorer-title');
            const avatarEl = document.getElementById('my-standing-avatar');
            const subtext = document.getElementById('my-standing-subtext');
            const rankCircle = document.getElementById('my-rank-circle');
            if (titleEl) titleEl.textContent = myRawName;
            if (avatarEl) avatarEl.src = (cachedMeData && cachedMeData.avatar) ? cachedMeData.avatar : (authUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(myRawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);
            if (rankCircle) rankCircle.textContent = (cachedMyRank && cachedMyRank < 999) ? '#' + cachedMyRank : '★';
            if (subtext) subtext.textContent = 'Syncing rankings...';
        }

        if (podiumContainer && (!rawLeadersList || rawLeadersList.length === 0)) {
            podiumContainer.innerHTML = `
                <div style="grid-column: 1 / -1; width: 100%; text-align: center; padding: 28px 16px; background: transparent; border: none !important;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(30, 58, 138, 0.08); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #1e3a8a; font-size: 20px;">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </div>
                    <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">Loading Provincial Rankings...</div>
                    <div style="font-size: 12px; color: #64748b; max-width: 270px; margin: 0 auto; line-height: 1.4;">Syncing explorer standings across La Union</div>
                </div>`;
        }

        if (rankListContainer && (!rawLeadersList || rawLeadersList.length === 0)) {
            rankListContainer.innerHTML = `
                <div style="text-align: center; padding: 20px 12px; color: #94a3b8; font-size: 12.5px; font-weight: 600;">
                    <i class="fa-solid fa-circle-notch fa-spin" style="margin-right: 6px; color: #1e3a8a;"></i> Retrieving ranked explorers...
                </div>`;
        }
    }

    function renderLeaderboardUI() {
        try {
            const podiumContainer = document.getElementById('podium-container');
            const rankListContainer = document.getElementById('rank-list-container');
            if (!podiumContainer && !rankListContainer) return;
            if (!rawLeadersList) return;

            // Display all ranked explorers without omitting newcomers
            let leaders = Array.isArray(rawLeadersList) ? [...rawLeadersList] : [];

            // Sort items based on current sort mode
            if (currentSortMode === 'visited') {
                leaders.sort((a, b) => {
                    const actA = parseInt(a.completed_activities ?? a.activities ?? a.total_activities ?? a.places_visited ?? 0);
                    const actB = parseInt(b.completed_activities ?? b.activities ?? b.total_activities ?? b.places_visited ?? 0);
                    if (actB !== actA) return actB - actA;
                    const ptsA = parseInt(a.points ?? a.pts ?? a.total_points ?? a.claimable_points ?? a.xp ?? 0);
                    const ptsB = parseInt(b.points ?? b.pts ?? b.total_points ?? b.claimable_points ?? b.xp ?? 0);
                    if (ptsB !== ptsA) return ptsB - ptsA;
                    const rankA = parseInt(a.rank ?? a.position ?? 9999);
                    const rankB = parseInt(b.rank ?? b.position ?? 9999);
                    return rankA - rankB;
                });
            } else {
                leaders.sort((a, b) => {
                    const ptsA = parseInt(a.points ?? a.pts ?? a.total_points ?? a.claimable_points ?? a.xp ?? 0);
                    const ptsB = parseInt(b.points ?? b.pts ?? b.total_points ?? b.claimable_points ?? b.xp ?? 0);
                    if (ptsB !== ptsA) return ptsB - ptsA;
                    const actA = parseInt(a.completed_activities ?? a.activities ?? a.total_activities ?? a.places_visited ?? 0);
                    const actB = parseInt(b.completed_activities ?? b.activities ?? b.total_activities ?? b.places_visited ?? 0);
                    if (actB !== actA) return actB - actA;
                    const rankA = parseInt(a.rank ?? a.position ?? 9999);
                    const rankB = parseInt(b.rank ?? b.position ?? 9999);
                    return rankA - rankB;
                });
            }

            // Maximum of Tourist users in the leaderboard is strictly 10
            leaders = leaders.slice(0, 10);

            const countBadge = document.getElementById('explorers-count-badge');
            if (countBadge) {
                countBadge.textContent = leaders.length > 0 ? `Top ${leaders.length}` : 'Top 10';
            }

            // Prepare current logged-in user standing information (function-scoped for ranking comparisons)
            const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
            const myRealId = Number(authUser.id || authUser.user_id || 0);
            const myRealEmail = String(authUser.email || '').trim().toLowerCase();

            if (!cachedMeData && authUser && (authUser.id || authUser.name || authUser.email)) {
                const userPts = parseInt(authUser.points || authUser.xp || 0);
                const userActs = parseInt(authUser.completed_activities || authUser.places_visited || 0);
                cachedMeData = {
                    id: myRealId,
                    user_id: myRealId,
                    name: authUser.name || authUser.full_name || 'You',
                    full_name: authUser.name || authUser.full_name || 'You',
                    email: authUser.email || '',
                    avatar: authUser.avatar || '',
                    home_location: authUser.home_location || authUser.municipality || 'La Union',
                    municipality: authUser.municipality || authUser.home_location || 'La Union',
                    points: userPts,
                    total_points: userPts,
                    pts: userPts,
                    xp: parseInt(authUser.xp || 0),
                    total_xp: parseInt(authUser.xp || 0),
                    completed_activities: userActs,
                    places_visited: userActs,
                    rank: (cachedMyRank && cachedMyRank < 999) ? cachedMyRank : ((userPts > 0 || userActs > 0) ? (rawLeadersList ? (rawLeadersList.length + 1) : 11) : null)
                };
                window._cachedMeData = cachedMeData;
            }
            const myPts = cachedMeData ? parseInt(cachedMeData.points ?? cachedMeData.pts ?? cachedMeData.total_points ?? cachedMeData.claimable_points ?? 0) : parseInt(authUser.points || 0);
            const myActivities = cachedMeData ? parseInt(cachedMeData.completed_activities ?? cachedMeData.activities ?? cachedMeData.total_activities ?? cachedMeData.places_visited ?? 0) : parseInt(authUser.completed_activities || authUser.places_visited || 0);
            const hasEarnedRank = Boolean((cachedMyRank && cachedMyRank < 999) || myPts > 0 || myActivities > 0);
            const myRankNum = (cachedMyRank && cachedMyRank < 999) ? cachedMyRank : (hasEarnedRank ? (rawLeadersList.length > 0 ? (rawLeadersList.length + '+') : '★') : '—');
            const myRawName = String((cachedMeData && (cachedMeData.name || cachedMeData.full_name)) || (authUser && (authUser.name || authUser.full_name)) || 'Explorer').trim() || 'Explorer';
            const myDisplayName = (!hasEarnedRank) ? `${myRawName} (Unranked)` : myRawName;
            const myAvatar = cachedMeData && cachedMeData.avatar ? cachedMeData.avatar : (authUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(myRawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);

            window.myUserData = {
                name: myRawName,
                avatar: myAvatar,
                pts: myPts,
                rank: myRankNum,
                activities: myActivities,
                location: cachedMeData ? (cachedMeData.home_location || cachedMeData.municipality || '') : '',
                bio: cachedMeData ? (cachedMeData.bio || '') : ''
            };

            // Render Standing Banner
            const banner = document.getElementById('my-standing-banner');
            const rankCircle = document.getElementById('my-rank-circle');
            const subtext = document.getElementById('my-standing-subtext');
            const titleEl = document.getElementById('my-explorer-title');
            const avatarEl = document.getElementById('my-standing-avatar');

            if (banner) {
                banner.style.display = 'flex';
                if (avatarEl) avatarEl.src = myAvatar;
                if (titleEl) titleEl.textContent = myDisplayName;
                if (rankCircle) {
                    if (cachedMyRank && cachedMyRank < 999) {
                        rankCircle.textContent = '#' + cachedMyRank;
                    } else if (hasEarnedRank && myRankNum !== '—') {
                        rankCircle.textContent = String(myRankNum).startsWith('#') ? myRankNum : '#' + myRankNum;
                    } else {
                        rankCircle.textContent = '—';
                    }
                }
                if (subtext) {
                    if (cachedMyRank && cachedMyRank < 999) {
                        if (currentSortMode === 'visited') {
                            subtext.textContent = `Rank #${cachedMyRank} • ${myActivities} Spots Visited • ${myPts.toLocaleString()} Points`;
                        } else {
                            subtext.textContent = `Rank #${cachedMyRank} • ${myPts.toLocaleString()} Points • ${myActivities} Spots Visited`;
                        }
                    } else if (myPts > 0 || myActivities > 0) {
                        if (currentSortMode === 'visited') {
                            subtext.textContent = `${myActivities} Spots Visited • ${myPts.toLocaleString()} Points`;
                        } else {
                            subtext.textContent = `${myPts.toLocaleString()} Points • ${myActivities} Spots Visited`;
                        }
                    } else {
                        subtext.textContent = 'Check in at spots or play games to earn Points and rank!';
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

            // Render Rank List (Ranks 4 onwards - all ranked explorers)
            let rankListHTML = '';
            if (leaders.length > 3) {
                for (let i = 3; i < leaders.length; i++) {
                    const user = leaders[i];
                    const isMe = Boolean(
                        (myRealId > 0 && (Number(user.id) === myRealId || Number(user.user_id) === myRealId)) ||
                        (myRealEmail && myRealEmail.includes('@') && String(user.email || '').trim().toLowerCase() === myRealEmail)
                    );
                    rankListHTML += generateRankItem(user, i + 1, isMe);
                }
            }

            // Append current user ONLY if they have earned points/activity and are outside the top 10
            const isMeAlreadyInList = Boolean(
                (myRealId > 0 && leaders.some(u => Number(u.id || u.user_id) === myRealId)) ||
                (myRealEmail && myRealEmail.includes('@') && leaders.some(u => String(u.email || '').trim().toLowerCase() === myRealEmail))
            );
            if (cachedMeData && !isMeAlreadyInList && hasEarnedRank) {
                const displayRank = (cachedMyRank && cachedMyRank < 999) ? cachedMyRank : (leaders.length > 0 ? (leaders.length + '+') : 11);
                rankListHTML += generateRankItem(cachedMeData, displayRank, true, true);
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
        } catch (err) {
            console.error("renderLeaderboardUI error:", err);
        }
    }

    function getUserDisplayName(user, rank) {
        if (!user) return rank ? `${rank}# Explorer` : 'Explorer';
        const name = String(user.name || user.full_name || user.real_name || '').trim();
        if (name && !name.match(/^Explorer\s*#?\d*$/i) && name.toLowerCase() !== 'explorer') {
            return name;
        }
        if (rank) {
            return `${rank}# Explorer`;
        }
        const idToUse = user.rank || user.user_id || user.id || 1;
        return `${idToUse}# Explorer`;
    }

    function generatePodiumPlace(user, rank) {
        const displayName = getUserDisplayName(user, rank);
        const rawName = String(user.name || user.full_name || user.real_name || 'Explorer').replace(/[^a-zA-Z\s]/g, '').trim() || 'Explorer';
        const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || '')).replace(/\/+$/, '');
        let avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(rawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        if (avatarUrl && !avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:')) {
            avatarUrl = bUrl ? (bUrl + '/' + avatarUrl.replace(/^\//, '')) : avatarUrl;
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
        <div class="podium-place rank-${rank}" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${pts}, '${rank}', ${activities}, '${safeLocation}', '${safeBio}', ${Boolean(isMe)})">
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

    function generateRankItem(user, rank, isMe, isSticky = false) {
        const displayName = getUserDisplayName(user, rank);
        const rawName = (user.name || user.full_name || user.real_name || 'Explorer').replace(/[^a-zA-Z\s]/g, '').trim() || 'Explorer';
        const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || '')).replace(/\/+$/, '');
        let avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(rawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        if (avatarUrl && !avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:')) {
            avatarUrl = bUrl ? (bUrl + '/' + avatarUrl.replace(/^\//, '')) : avatarUrl;
        }

        const activeClass = [
            isMe ? 'is-me' : '',
            isSticky ? 'sticky-you-card is-sticky-bottom' : ''
        ].filter(Boolean).join(' ');
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
        <div class="rank-item ${activeClass}" style="animation-delay: ${Math.max(0, delay)}s;" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${pts}, '${rank}', ${activities}, '${safeLocation}', '${safeBio}', ${Boolean(isMe)})">
            <div style="display: flex; align-items: center; min-width: 0; flex: 1; gap: 10px;">
                <div class="rank-number-box">#${rank}</div>
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

        const cleanRank = String(rank || '').replace(/^#/, '');
        document.getElementById('modal-avatar').src = avatar;
        document.getElementById('modal-name').innerText = name;
        document.getElementById('modal-pts').innerText = displayPts.toLocaleString();
        document.getElementById('modal-rank-badge').innerText = cleanRank || '★';
        const rankNumEl = document.getElementById('modal-rank-num');
        if (rankNumEl) rankNumEl.innerText = cleanRank ? (cleanRank !== '—' ? '#' + cleanRank : '—') : '—';
        document.getElementById('modal-activities').innerText = activities ? Number(activities).toLocaleString() : '0';

        // When viewing self, fetch fresh live balance to guarantee accuracy
        if (isSelf) {
            const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            if (token) {
                const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
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
                rankPill.innerText = (cleanRank && cleanRank !== '—') ? `#${cleanRank} Ranked Explorer (You)` : `Your Explorer Profile`;
            } else {
                rankPill.innerText = cleanRank ? `#${cleanRank} Ranked Explorer` : 'Ranked Explorer';
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

    function applyLeaderboardData(data) {
        if (!data) return;
        const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
        const list = data.users || data.leaders || data.tourists || data.data || data.leaderboard || (Array.isArray(data) ? data : []);
        if (Array.isArray(list) && list.length > 0) {
            rawLeadersList = list.slice(0, 10);
            window._cachedRawLeadersList = rawLeadersList;
        }
        if (data.me) {
            cachedMeData = data.me;
            window._cachedMeData = data.me;
        }
        if (data.my_rank !== undefined && data.my_rank !== null) {
            cachedMyRank = parseInt(data.my_rank);
            window._cachedMyRank = cachedMyRank;
        } else if (data.myRank !== undefined && data.myRank !== null) {
            cachedMyRank = parseInt(data.myRank);
            window._cachedMyRank = cachedMyRank;
        }

        // Safe self-matching: strictly match by valid positive ID or real email (never generic name)
        const myId = Number(authUser.id || authUser.user_id || 0);
        const myEmail = String(authUser.email || '').trim().toLowerCase();
        const myName = String(authUser.name || authUser.full_name || '').trim().toLowerCase();
        const isGenericName = ['tourist', 'explorer', 'guest', 'guest explorer', 'you'].includes(myName);

        let userFoundInLeaders = false;
        if (rawLeadersList && rawLeadersList.length > 0) {
            const foundIndex = rawLeadersList.findIndex((u, idx) => {
                const uId = Number(u.id || u.user_id || 0);
                const uEmail = String(u.email || '').trim().toLowerCase();
                const uName = String(u.name || u.full_name || u.real_name || '').trim().toLowerCase();
                if (myId > 0 && uId > 0 && myId === uId) return true;
                if (myEmail && myEmail.includes('@') && uEmail && myEmail === uEmail) return true;
                if (!isGenericName && myName && uName && myName === uName && myName.length > 3) return true;
                return false;
            });
            if (foundIndex !== -1) {
                userFoundInLeaders = true;
                const found = rawLeadersList[foundIndex];
                cachedMeData = found;
                window._cachedMeData = found;
                cachedMyRank = Number(found.rank || found.position || (foundIndex + 1));
                window._cachedMyRank = cachedMyRank;
            } else {
                if (!data.me && (!data.my_rank || data.my_rank >= 999)) {
                    cachedMeData = null;
                    cachedMyRank = null;
                    window._cachedMyRank = null;
                }
            }
        }

        // Resilient fallback: If current user is authenticated but outside top 10, synthesize cachedMeData from authUser
        if (!cachedMeData && authUser && (authUser.id || authUser.name || authUser.email)) {
            const myUserPts = parseInt(authUser.points || authUser.xp || 0);
            const myUserActs = parseInt(authUser.completed_activities || authUser.places_visited || 0);
            const hasActivity = (myUserPts > 0 || myUserActs > 0);

            cachedMeData = {
                id: myId,
                user_id: myId,
                name: authUser.name || authUser.full_name || 'You',
                full_name: authUser.name || authUser.full_name || 'You',
                email: authUser.email || '',
                avatar: authUser.avatar || '',
                home_location: authUser.home_location || authUser.municipality || 'La Union',
                municipality: authUser.municipality || authUser.home_location || 'La Union',
                points: myUserPts,
                total_points: myUserPts,
                pts: myUserPts,
                xp: parseInt(authUser.xp || 0),
                total_xp: parseInt(authUser.xp || 0),
                completed_activities: myUserActs,
                places_visited: myUserActs,
                rank: (cachedMyRank && cachedMyRank < 999) ? cachedMyRank : (hasActivity ? 11 : null)
            };
            window._cachedMeData = cachedMeData;
            if (cachedMyRank && cachedMyRank >= 999) {
                cachedMyRank = null;
                window._cachedMyRank = null;
            }
        }
        renderLeaderboardUI();
    }

    // Immediate hydration from real cached standings, then fetch real live data if online
    (function hydrateAndFetchLeaderboard() {
        try {
            const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const cacheKey = 'leaderboard_data_v26_' + (token ? token.substring(0, 10) : 'public');
            const storedRaw = localStorage.getItem(cacheKey) || localStorage.getItem('leaderboard_data_offline_fallback');
            if (storedRaw) {
                const parsed = JSON.parse(storedRaw);
                const d = parsed.data || parsed;
                if (d) {
                    applyLeaderboardData(d);
                }
            }
        } catch (e) { }

        // Render cached rankings (or empty state if not yet cached)
        renderLeaderboardUI();

        // Fetch fresh real rankings from live server
        if (navigator.onLine && window.isAppOnline !== false) {
            setTimeout(() => {
                if (typeof window.refreshLeaderboard === 'function') {
                    window.refreshLeaderboard(false);
                }
            }, 60);
        }
    })();

    let _isFetchingLeaderboard = false;

    // Explicit manual refresh: Triggered when user taps the refresh button at the top right
    window.refreshLeaderboard = async function (force = true) {
        if (_isFetchingLeaderboard) return;
        _isFetchingLeaderboard = true;

        const refreshIcon = document.getElementById('leaderboard-refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        if (!navigator.onLine || window.isAppOnline === false) {
            if (force && typeof showToast === 'function') {
                showToast('Currently offline. Showing cached standings.', 'info', 3000);
            }
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            _isFetchingLeaderboard = false;
            return;
        }

        try {
            const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const reqHeaders = { 'Accept': 'application/json' };
            if (token) reqHeaders['Authorization'] = 'Bearer ' + token;

            // Direct Cloudflare production endpoint first (works universally on mobile APK without tethering)
            const candidates = [
                'https://api.intan-elyu.online/api/public/leaderboard?limit=10'
            ];
            if (token) {
                candidates.unshift('https://api.intan-elyu.online/api/tourist/leaderboard?limit=10');
            }
            candidates.push('/api/public/leaderboard?limit=10');

            let fetchedData = null;
            for (const url of candidates) {
                const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                const timeoutId = controller ? setTimeout(() => controller.abort(), 8000) : null;
                try {
                    const res = await fetch(url, { headers: reqHeaders, signal: controller ? controller.signal : undefined });
                    if (timeoutId) clearTimeout(timeoutId);
                    if (res.ok) {
                        const json = await res.json();
                        const users = json.users || json.leaders || json.data || json.tourists || (Array.isArray(json) ? json : []);
                        if (Array.isArray(users) && users.length > 0) {
                            fetchedData = json;
                            break;
                        }
                    }
                } catch (err) {
                    if (timeoutId) clearTimeout(timeoutId);
                }
            }

            if (fetchedData) {
                applyLeaderboardData(fetchedData);
                try {
                    const cacheKey = 'leaderboard_data_v26_' + (token ? token.substring(0, 10) : 'public');
                    localStorage.setItem(cacheKey, JSON.stringify(fetchedData));
                    localStorage.setItem('leaderboard_data_offline_fallback', JSON.stringify(fetchedData));
                } catch (e) { }
                if (force && typeof showToast === 'function') {
                    showToast('Leaderboard rankings synced!', 'success', 3000);
                }
            } else {
                if (force && typeof showToast === 'function') {
                    showToast('Could not reach leaderboard server. Showing cached standings.', 'info', 3500);
                }
            }

            // Also refresh user's live points balance if authenticated
            if (token) {
                try {
                    const bUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                    const ptsRes = await fetch(bUrl + '/api/tourist/points/balance', {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });
                    if (ptsRes.ok) {
                        const d = await ptsRes.json();
                        if (d && d.status === 'success' && d.points !== undefined) {
                            const ptsVal = parseInt(d.points);
                            if (window.myUserData) {
                                window.myUserData.pts = ptsVal;
                                const subtext = document.getElementById('my-standing-subtext');
                                if (subtext) {
                                    const currentActivities = window.myUserData.activities || (cachedMeData ? parseInt(cachedMeData.completed_activities ?? cachedMeData.activities ?? cachedMeData.places_visited ?? 0) : 0);
                                    if (currentSortMode === 'visited') {
                                        subtext.textContent = `${currentActivities} Spots Visited • ${ptsVal.toLocaleString()} Points`;
                                    } else {
                                        subtext.textContent = `${ptsVal.toLocaleString()} Points • ${currentActivities} Spots Visited`;
                                    }
                                }
                            }
                        }
                    }
                } catch (e) { }
            }

        } catch (err) {
            console.warn('Leaderboard refresh error:', err);
            if (force && typeof showToast === 'function') {
                showToast('Sync error. Retaining cached rankings.', 'warning', 3000);
            }
        } finally {
            _isFetchingLeaderboard = false;
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
        }
    };

    window.initLeaderboardView = async function (forceRefresh = false) {
        if (forceRefresh) {
            await window.refreshLeaderboard(true);
        } else {
            renderLeaderboardUI();
        }
    };

    // Offline status badge updater only (pure visual status, no auto-fetching)
    function updateLeaderboardOfflineStatus() {
        const badge = document.getElementById('leaderboard-offline-badge');
        const isOff = window.isAppOnline === false || (typeof navigator.onLine === 'boolean' ? !navigator.onLine : false);
        if (badge) badge.style.setProperty('display', isOff ? 'block' : 'none', 'important');
    }
    window.addEventListener('online', updateLeaderboardOfflineStatus);
    window.addEventListener('offline', updateLeaderboardOfflineStatus);
    window.addEventListener('appOnline', updateLeaderboardOfflineStatus);
    window.addEventListener('appOffline', updateLeaderboardOfflineStatus);
    window.addEventListener('app-connection-changed', updateLeaderboardOfflineStatus);
    setTimeout(updateLeaderboardOfflineStatus, 150);

    // Re-render UI when switching back to leaderboard tab (no network fetch)
    function handleLeaderboardViewLoaded(e) {
        if (e && e.detail && e.detail.view === 'leaderboard') {
            updateLeaderboardOfflineStatus();
            renderLeaderboardUI();
        }
    }
    window.addEventListener('viewLoaded', handleLeaderboardViewLoaded);
    document.addEventListener('viewLoaded', handleLeaderboardViewLoaded);
</script>