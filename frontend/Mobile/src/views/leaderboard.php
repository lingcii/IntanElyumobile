<!-- Leaderboard View -->
<?php
$pageTitle = 'Leaderboards';
$activeTab = 'leaderboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>
<link rel="stylesheet" href="assets/css/views/leaderboard.css?v=<?= time() ?>">

<div class="leaderboard-container has-header has-bottom-nav animate-fade-in">

    <!-- Title & Season Header -->
    <div class="leaderboard-title stagger-0">
        <h2>
            <i class="fa-solid fa-trophy" style="color: #fbbf24;"></i> La Union Top Explorers
        </h2>
        <p>Earn XP and unlock achievements across Elyu</p>
    </div>

    <!-- Your Current Standing Banner -->
    <div id="my-standing-banner" class="standing-banner-card stagger-1" style="display: none;"
        onclick="if(window.myUserData) showUserProfile(window.myUserData.name, window.myUserData.avatar, window.myUserData.xp, window.myUserData.pts, window.myUserData.rank, window.myUserData.level, window.myUserData.activities, window.myUserData.location, window.myUserData.bio)">
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
                        style="font-size: 9.5px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 2px 7px; border-radius: 100px; font-weight: 800; border: 1px solid rgba(56, 189, 248, 0.3);">YOU</span>
                </div>
                <div class="standing-subtext" id="my-standing-subtext">Loading rank details...</div>
            </div>
        </div>
        <button class="standing-action-btn" onclick="event.stopPropagation(); navigateTo('map');">
            <i class="fa-solid fa-compass"></i> Explore
        </button>
    </div>

    <!-- Sort Filter Tabs -->
    <div class="leaderboard-tabs-wrapper stagger-1">
        <button class="leaderboard-tab-btn active" id="tab-sort-xp" onclick="setLeaderboardSort('xp')">
            <i class="fa-solid fa-bolt" style="color:#fbbf24;"></i> Top XP
        </button>
        <button class="leaderboard-tab-btn" id="tab-sort-points" onclick="setLeaderboardSort('points')">
            <i class="fa-solid fa-coins" style="color:#f59e0b;"></i> Highest Points
        </button>
        <button class="leaderboard-tab-btn" id="tab-sort-visited" onclick="setLeaderboardSort('visited')">
            <i class="fa-solid fa-map-location-dot" style="color:#38bdf8;"></i> Most Visited
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
                style="font-size: 15px; font-weight: 800; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-list-ol" style="color: #38bdf8;"></i> Explorer Leaderboard
            </h3>
            <span style="font-size: 11px; font-weight: 700; color: rgba(148, 163, 184, 0.7);"
                id="explorers-count-badge">Top Ranks</span>
        </div>

        <!-- Rank List -->
        <div class="rank-list-wrapper stagger-2" id="rank-list-wrapper">
            <div class="rank-list" id="rank-list-container">
                <!-- Injected via JS -->
            </div>
            <button class="btn-invite-friends"
                onclick="if(navigator.share){navigator.share({title:'La Union Top Explorers',url:window.location.href});}else if(typeof showToast==='function'){showToast('Leaderboard link copied to clipboard!');}">
                <i class="fa-solid fa-user-plus" style="color:#38bdf8;"></i> Invite Friends & Compete
            </button>
        </div>
    </div>

</div>

<!-- User Profile Modal -->
<div id="user-profile-modal" class="profile-modal-overlay" onclick="if(event.target===this) closeUserProfile();">
    <div class="profile-modal-card">
        <button class="profile-modal-close" onclick="closeUserProfile()"><i class="fa-solid fa-xmark"></i></button>
        <div class="profile-modal-header" style="margin-top: 6px;">
            <img id="modal-avatar" src="" alt="Avatar">
            <div id="modal-rank-badge" class="modal-rank-badge">1</div>
        </div>
        <h2 id="modal-name">Explorer</h2>
        <div
            style="display:flex; justify-content:center; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
            <span id="modal-rank-pill"
                style="font-size:11px; font-weight:800; color:#38bdf8; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.25); padding:2px 10px; border-radius:100px;">#1
                Ranked Explorer</span>
            <div id="modal-location"
                style="font-size: 11.5px; color: rgba(226,232,240,0.85); font-weight: 600; display: none;"><i
                    class="fa-solid fa-location-dot" style="color:#38bdf8; margin-right:3px;"></i><span>Hometown</span>
            </div>
        </div>
        <p id="modal-bio"
            style="font-size: 12px; color: rgba(226, 232, 240, 0.85); font-style: italic; margin: 0 0 12px 0; display: none; line-height: 1.4; background: rgba(255,255,255,0.03); padding: 8px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
        </p>

        <!-- 4 Stats Boxes Grid -->
        <div class="modal-stats" style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px;">
            <div class="modal-stat-box">
                <i class="fa-solid fa-bolt" style="color:#fbbf24; font-size:16px;"></i>
                <span id="modal-xp" style="font-size:15px; font-weight:900; color:#fff;">0</span>
                <small
                    style="font-size:10px; color:rgba(226,232,240,0.6); text-transform:uppercase; font-weight:700;">Total
                    XP</small>
            </div>
            <div class="modal-stat-box">
                <i class="fa-solid fa-coins" style="color:#f59e0b; font-size:16px;"></i>
                <span id="modal-pts" style="font-size:15px; font-weight:900; color:#f59e0b;">0</span>
                <small
                    style="font-size:10px; color:rgba(226,232,240,0.6); text-transform:uppercase; font-weight:700;">Points
                    (PTS)</small>
            </div>
            <div class="modal-stat-box">
                <i class="fa-solid fa-map-location-dot" style="color:#34c759; font-size:16px;"></i>
                <span id="modal-activities" style="font-size:15px; font-weight:900; color:#fff;">0</span>
                <small
                    style="font-size:10px; color:rgba(226,232,240,0.6); text-transform:uppercase; font-weight:700;">Visited
                    Spots</small>
            </div>
            <div class="modal-stat-box">
                <i class="fa-solid fa-shield-halved" style="color:#38bdf8; font-size:16px;"></i>
                <span id="modal-level" style="font-size:15px; font-weight:900; color:#38bdf8;">Lvl 1</span>
                <small
                    style="font-size:10px; color:rgba(226,232,240,0.6); text-transform:uppercase; font-weight:700;">Explorer
                    Tier</small>
            </div>
        </div>

        <!-- Explorer Badges / Milestones Section -->
        <div id="modal-explorer-milestones"
            style="margin-top:12px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:14px; padding:10px 12px; text-align:left;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span
                    style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; color:rgba(148,163,184,0.8);">Achievements
                    & Status</span>
                <span id="modal-status-tag"
                    style="font-size:9.5px; font-weight:800; color:#34c759; background:rgba(52,199,89,0.12); padding:1px 6px; border-radius:6px;">Verified
                    Explorer</span>
            </div>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <span
                    style="font-size:10px; font-weight:700; color:#fff; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.25); padding:3px 8px; border-radius:8px;">🌊
                    Elyu Surfer</span>
                <span
                    style="font-size:10px; font-weight:700; color:#fff; background:rgba(251,191,36,0.12); border:1px solid rgba(251,191,36,0.25); padding:3px 8px; border-radius:8px;">⚡
                    XP Pioneer</span>
                <span
                    style="font-size:10px; font-weight:700; color:#fff; background:rgba(52,199,89,0.12); border:1px solid rgba(52,199,89,0.25); padding:3px 8px; border-radius:8px;">🌿
                    Eco Spot Check-in</span>
            </div>
        </div>

        <button onclick="if(typeof showToast==='function'){showToast('Cheered explorer! 🎉');} closeUserProfile();"
            style="width: 100%; margin-top: 14px; padding: 12px; border-radius: 100px; background: linear-gradient(135deg, #38bdf8, #2563eb); border: none; color: #ffffff; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: transform 0.15s ease; box-shadow:0 4px 15px rgba(56,189,248,0.3);">
            <i class="fa-solid fa-hand-peace"></i> Send High Five
        </button>
    </div>
</div>

<script>
    (async function () {
        const podiumContainer = document.getElementById('podium-container');
        const rankListContainer = document.getElementById('rank-list-container');
        let rawLeadersList = [];
        let currentSortMode = 'xp';
        let cachedMeData = null;
        let cachedMyRank = 999;

        try {
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
            const headers = { 'Accept': 'application/json' };

            var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
            let url = backendUrl + '/api/public/leaderboard';
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
                url = backendUrl + '/api/tourist/leaderboard';
            }

            const cacheKey = 'leaderboard_data_v13_' + (token ? token.substring(0, 10) : 'public');
            const fetchCache = window.useCache || (async (key, fetcher, renderer) => { const d = await fetcher(); if (renderer) renderer(d); return d; });

            await fetchCache(
                cacheKey,
                async () => {
                    let res = await fetch(url, { headers: { ...headers } });
                    if (res.status === 401 && token) {
                        localStorage.removeItem('intan_elyu_token');
                        localStorage.removeItem('Intan_Elyu_Token');
                        res = await fetch(backendUrl + '/api/public/leaderboard', { headers: { 'Accept': 'application/json' } });
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
                60000 // 1 minute TTL
            );
            window.leaderboardNeedsRefresh = false;

        } catch (e) {
            console.error("Leaderboard error:", e);
            if (podiumContainer) podiumContainer.innerHTML = "<div style='color:rgba(239,68,68,0.8); text-align:center; width:100%; padding:20px; font-size:14px;'>Failed to load leaderboard.</div>";
        }

        function renderLeaderboardUI() {
            if (!rawLeadersList) return;

            // Sort items based on current sort mode
            let leaders = [...rawLeadersList];
            if (currentSortMode === 'points') {
                leaders.sort((a, b) => {
                    const ptsA = parseInt(a.claimable_points || a.points || 0);
                    const ptsB = parseInt(b.claimable_points || b.points || 0);
                    if (ptsB !== ptsA) return ptsB - ptsA;
                    const xpA = parseInt(a.total_points || a.total_xp || a.xp || 0);
                    const xpB = parseInt(b.total_points || b.total_xp || b.xp || 0);
                    return xpB - xpA;
                });
            } else if (currentSortMode === 'visited') {
                leaders.sort((a, b) => {
                    const actA = parseInt(a.completed_activities || a.places_visited || 0);
                    const actB = parseInt(b.completed_activities || b.places_visited || 0);
                    if (actB !== actA) return actB - actA;
                    const xpA = parseInt(a.total_points || a.total_xp || a.xp || 0);
                    const xpB = parseInt(b.total_points || b.total_xp || b.xp || 0);
                    return xpB - xpA;
                });
            } else {
                leaders.sort((a, b) => {
                    const xpA = parseInt(a.total_points || a.total_xp || a.xp || 0);
                    const xpB = parseInt(b.total_points || b.total_xp || b.xp || 0);
                    if (xpB !== xpA) return xpB - xpA;
                    const actA = parseInt(a.completed_activities || a.places_visited || 0);
                    const actB = parseInt(b.completed_activities || b.places_visited || 0);
                    return actB - actA;
                });
            }

            const countBadge = document.getElementById('explorers-count-badge');
            if (countBadge) {
                countBadge.textContent = `Top ${Math.min(leaders.length, 10)} Explorers`;
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
                const myRankNum = cachedMyRank && cachedMyRank < 999 ? cachedMyRank : 1;
                const myDisplayName = `${myRankNum}# Explorer`;
                const myXp = cachedMeData ? parseInt(cachedMeData.xp ?? cachedMeData.total_xp ?? 0) : (authUser.xp || 0);
                const myPts = cachedMeData ? parseInt(cachedMeData.points ?? cachedMeData.pts ?? cachedMeData.total_points ?? cachedMeData.claimable_points ?? 0) : (authUser.points || 0);
                const myActivities = cachedMeData ? parseInt(cachedMeData.completed_activities ?? cachedMeData.places_visited ?? 0) : 0;
                const myLevel = Math.floor(myXp / 1000) + 1;
                const myRawName = (cachedMeData ? (cachedMeData.name || cachedMeData.full_name) : (authUser.name || authUser.full_name || 'Explorer')).replace(/[^a-zA-Z\s]/g, '').trim() || 'Explorer';
                const myAvatar = cachedMeData && cachedMeData.avatar ? cachedMeData.avatar : (authUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(myRawName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`);

                window.myUserData = {
                    name: myDisplayName,
                    avatar: myAvatar,
                    xp: myXp,
                    rank: myRankNum,
                    level: myLevel,
                    activities: myActivities,
                    location: cachedMeData ? (cachedMeData.home_location || '') : '',
                    bio: cachedMeData ? (cachedMeData.bio || '') : ''
                };

                if (avatarEl) avatarEl.src = myAvatar;
                if (titleEl) titleEl.textContent = myDisplayName;
                if (rankCircle) {
                    if (cachedMyRank && cachedMyRank < 999) {
                        rankCircle.textContent = '#' + cachedMyRank;
                    } else {
                        rankCircle.textContent = '★';
                    }
                }
                if (subtext) {
                    subtext.textContent = `${myXp.toLocaleString()} XP • ${myPts.toLocaleString()} PTS • ${myActivities} Visited`;
                }
            }

            // Render Podium (1st, 2nd, 3rd)
            let podiumHTML = '';
            if (leaders[1]) podiumHTML += generatePodiumPlace(leaders[1], 2);
            if (leaders[0]) podiumHTML += generatePodiumPlace(leaders[0], 1);
            if (leaders[2]) podiumHTML += generatePodiumPlace(leaders[2], 3);
            if (podiumContainer) podiumContainer.innerHTML = podiumHTML;

            // Render Rank List (Ranks 4 to 10 - capped strictly at 10 total)
            let rankListHTML = '';
            if (leaders.length > 3) {
                for (let i = 3; i < Math.min(leaders.length, 10); i++) {
                    const user = leaders[i];
                    const isMe = cachedMeData && (user.id === cachedMeData.id || user.user_id === cachedMeData.id);
                    rankListHTML += generateRankItem(user, i + 1, isMe);
                }
            }

            if (cachedMeData && cachedMyRank > 10 && cachedMyRank <= 999) {
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

        window.setLeaderboardSort = function (mode) {
            if (currentSortMode === mode) return;
            currentSortMode = mode;

            const tabXp = document.getElementById('tab-sort-xp');
            const tabPoints = document.getElementById('tab-sort-points');
            const tabVisited = document.getElementById('tab-sort-visited');

            if (tabXp) tabXp.classList.toggle('active', mode === 'xp');
            if (tabPoints) tabPoints.classList.toggle('active', mode === 'points');
            if (tabVisited) tabVisited.classList.toggle('active', mode === 'visited');

            renderLeaderboardUI();
        };

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
            let stepHeight = '72px';
            let badgeLabel = '3RD';

            if (rank === 1) {
                medalIcon = `<div class="podium-crown-icon"><i class="fa-solid fa-crown" style="color:#FFD700; font-size:26px;"></i></div>`;
                stepHeight = '112px';
                badgeLabel = '1ST';
            } else if (rank === 2) {
                medalIcon = `<div class="podium-medal-icon"><i class="fa-solid fa-medal" style="color:#e2e8f0; font-size:20px;"></i></div>`;
                stepHeight = '88px';
                badgeLabel = '2ND';
            } else if (rank === 3) {
                medalIcon = `<div class="podium-medal-icon"><i class="fa-solid fa-award" style="color:#fb923c; font-size:20px;"></i></div>`;
                stepHeight = '70px';
                badgeLabel = '3RD';
            }

            const safeName = displayName.replace(/'/g, "\\'");
            const xp = parseInt(user.xp ?? user.total_xp ?? 0);
            const pts = parseInt(user.points ?? user.pts ?? user.total_points ?? user.claimable_points ?? 0);
            const level = user.level || (Math.floor(xp / 1000) + 1);
            const activities = parseInt(user.completed_activities ?? user.places_visited ?? 0);
            const safeLocation = (user.home_location || '').replace(/'/g, "\\'");
            const safeBio = (user.bio || '').replace(/'/g, "\\'");

            let iconHtml = '';
            let textMetric = '';

            if (currentSortMode === 'points') {
                iconHtml = '<i class="fa-solid fa-coins" style="font-size:10px;"></i>';
                textMetric = `${pts.toLocaleString()} PTS`;
            } else if (currentSortMode === 'visited') {
                iconHtml = '<i class="fa-solid fa-map-location-dot" style="font-size:10px;"></i>';
                textMetric = `${activities} Visited`;
            } else {
                iconHtml = '<i class="fa-solid fa-bolt" style="font-size:10px;"></i>';
                textMetric = `${xp.toLocaleString()} XP`;
            }

            const metricPillHtml = `
            <div class="podium-xp-pill podium-xp-${rank}" style="margin-bottom:10px;">
                ${iconHtml} ${textMetric}
            </div>`;

            return `
        <div class="podium-place rank-${rank}" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${xp}, ${pts}, ${rank}, ${level}, ${activities}, '${safeLocation}', '${safeBio}')">
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
            const xp = parseInt(user.xp ?? user.total_xp ?? 0);
            const pts = parseInt(user.points ?? user.pts ?? user.total_points ?? user.claimable_points ?? 0);
            const level = user.level || (Math.floor(xp / 1000) + 1);
            const activities = parseInt(user.completed_activities ?? user.places_visited ?? 0);
            const safeLocation = (user.home_location || '').replace(/'/g, "\\'");
            const safeBio = (user.bio || '').replace(/'/g, "\\'");

            let rightBadgeHtml = '';
            let subMetaText = `<span>Lvl ${level} Explorer</span>`;

            if (currentSortMode === 'points') {
                rightBadgeHtml = `
            <div class="rank-xp-badge" style="color:#f59e0b;">
                ${pts.toLocaleString()} <small style="font-size:10px; font-weight:700; color:rgba(245,158,11,0.85); margin-left:3px;">PTS</small>
            </div>`;
            } else if (currentSortMode === 'visited') {
                rightBadgeHtml = `
            <div class="rank-xp-badge" style="color:#38bdf8;">
                ${activities} <small style="font-size:10px; font-weight:700; color:rgba(56,189,248,0.85); margin-left:3px;">VISITED</small>
            </div>`;
            } else {
                rightBadgeHtml = `
            <div class="rank-xp-badge" style="color:#ffffff;">
                ${xp.toLocaleString()} <small style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.6); margin-left:3px;">XP</small>
            </div>`;
            }

            return `
        <div class="rank-item ${activeClass}" style="animation-delay: ${Math.max(0, delay)}s;" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${xp}, ${pts}, ${rank}, ${level}, ${activities}, '${safeLocation}', '${safeBio}')">
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

        window.showUserProfile = function (name, avatar, xp, pts, rank, level, activities, location, bio) {
            document.getElementById('modal-avatar').src = avatar;
            document.getElementById('modal-name').innerText = name;
            document.getElementById('modal-xp').innerText = Number(xp || 0).toLocaleString();
            document.getElementById('modal-pts').innerText = Number(pts || 0).toLocaleString();
            document.getElementById('modal-rank-badge').innerText = rank;
            document.getElementById('modal-level').innerText = 'Lvl ' + (level || 1);
            document.getElementById('modal-activities').innerText = activities ? Number(activities).toLocaleString() : '0';

            const rankPill = document.getElementById('modal-rank-pill');
            if (rankPill) {
                rankPill.innerText = `#${rank} Ranked Explorer`;
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
    })();
</script>