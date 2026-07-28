<!-- Leaderboard View -->
<?php
$pageTitle = 'Leaderboards';
$activeTab = 'leaderboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>
<link rel="stylesheet" href="assets/css/views/leaderboard.css?v=<?= time() ?>">

<div class="leaderboard-container has-header has-bottom-nav animate-fade-in" style="padding-bottom: 90px;">
    
    <!-- Title & Season Header -->
    <div class="leaderboard-title stagger-0" style="text-align: center; margin-bottom: 16px;">
        <h2 style="margin: 0 0 4px 0; font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-trophy" style="color: #fbbf24;"></i> La Union Top Explorers
        </h2>
        <p style="margin: 0; font-size: 12px; color: rgba(148, 163, 184, 0.8); font-weight: 600;">Earn XP by checking in at destinations across Elyu</p>
    </div>

    <!-- Your Current Standing Banner -->
    <div id="my-standing-banner" class="stagger-1" style="display: none; background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(37, 99, 235, 0.2)); border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 20px; padding: 12px 14px; margin-bottom: 20px; backdrop-filter: blur(16px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); align-items: center; justify-content: space-between; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
            <div id="my-rank-circle" style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #38bdf8, #2563eb); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; color: #fff; box-shadow: 0 4px 14px rgba(56,189,248,0.4); flex-shrink: 0;">
                #--
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 13px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 6px;">
                    <span id="my-explorer-title">Explorer #--</span>
                    <span style="font-size: 9px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 2px 6px; border-radius: 100px; font-weight: 800;">YOU</span>
                </div>
                <div style="font-size: 11px; color: rgba(226, 232, 240, 0.85); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="my-standing-subtext">Loading rank details...</div>
            </div>
        </div>
        <button onclick="navigateTo('map')" style="background: linear-gradient(135deg, #38bdf8, #2563eb); color: #ffffff; border: none; padding: 8px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; cursor: pointer; flex-shrink: 0; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 12px rgba(56,189,248,0.3); white-space: nowrap;">
            <i class="fa-solid fa-compass"></i> Explore
        </button>
    </div>
    
    <!-- Podium (Top 3) -->
    <div class="podium-container stagger-1" id="podium-container" style="margin-top: 36px;">
        <!-- Injected via JS -->
    </div>
    
    <!-- Rank List Section -->
    <div id="rank-list-section">
        <div class="stagger-2" style="display: flex; justify-content: space-between; align-items: center; margin: 20px 4px 12px 4px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-list-ol" style="color: #38bdf8;"></i> Explorer Leaderboard
            </h3>
            <span style="font-size: 11px; font-weight: 700; color: rgba(148, 163, 184, 0.7);" id="explorers-count-badge">Top Ranks</span>
        </div>

        <!-- Rank List -->
        <div class="rank-list-wrapper stagger-2" id="rank-list-wrapper" style="background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; padding: 12px 16px;">
            <div class="rank-list" id="rank-list-container">
                <!-- Injected via JS -->
            </div>
            <button onclick="if(navigator.share){navigator.share({title:'La Union Top Explorers',url:window.location.href});}else if(typeof showToast==='function'){showToast('Leaderboard link copied to clipboard!');}" style="width: 100%; margin-top: 14px; padding: 12px; border-radius: 100px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #ffffff; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: background 0.2s;">
                <i class="fa-solid fa-user-plus"></i> Invite Friends & Compete
            </button>
        </div>
    </div>

</div>

<!-- User Profile Modal -->
<div id="user-profile-modal" class="profile-modal-overlay">
    <div class="profile-modal-card" style="background: rgba(15, 23, 42, 0.95); border: 1.5px solid rgba(56, 189, 248, 0.4); border-radius: 28px; backdrop-filter: blur(24px); box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
        <button class="profile-modal-close" onclick="closeUserProfile()"><i class="fa-solid fa-xmark"></i></button>
        <div class="profile-modal-header" style="margin-top: 10px;">
            <img id="modal-avatar" src="" alt="Avatar" style="width: 90px; height: 90px; border-radius: 50%; border: 3px solid #38bdf8; object-fit: cover; box-shadow: 0 0 20px rgba(56,189,248,0.4);">
            <div id="modal-rank-badge" class="modal-rank-badge" style="background: linear-gradient(135deg, #38bdf8, #2563eb); font-weight: 900;">1</div>
        </div>
        <h2 id="modal-name" style="font-size: 20px; font-weight: 800; color: #ffffff; margin: 8px 0 2px 0;">Name</h2>
        <div id="modal-location" style="font-size: 12px; color: #38bdf8; font-weight: 600; margin-bottom: 12px; display: none;"><i class="fa-solid fa-location-dot"></i> <span>Hometown</span></div>
        <p id="modal-bio" style="font-size: 12px; color: rgba(226, 232, 240, 0.8); font-style: italic; margin: 0 0 16px 0; display: none; line-height: 1.4; background: rgba(255,255,255,0.03); padding: 8px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);"></p>

        <div class="modal-stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
            <div class="modal-stat-box" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 12px 6px;">
                <i class="fa-solid fa-bolt" style="color:#fbbf24; font-size: 16px; margin-bottom: 4px;"></i>
                <span id="modal-xp" style="font-size: 16px; font-weight: 800; color: #ffffff; display: block;">0</span>
                <small style="font-size: 10px; color: rgba(148, 163, 184, 0.7); text-transform: uppercase;">Total XP</small>
            </div>
            <div class="modal-stat-box" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 12px 6px;">
                <i class="fa-solid fa-map-location-dot" style="color:#34c759; font-size: 16px; margin-bottom: 4px;"></i>
                <span id="modal-activities" style="font-size: 16px; font-weight: 800; color: #ffffff; display: block;">0</span>
                <small style="font-size: 10px; color: rgba(148, 163, 184, 0.7); text-transform: uppercase;">Visited</small>
            </div>
            <div class="modal-stat-box" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 12px 6px;">
                <i class="fa-solid fa-shield-halved" style="color:#38bdf8; font-size: 16px; margin-bottom: 4px;"></i>
                <span id="modal-level" style="font-size: 16px; font-weight: 800; color: #ffffff; display: block;">Lvl 1</span>
                <small style="font-size: 10px; color: rgba(148, 163, 184, 0.7); text-transform: uppercase;">Explorer</small>
            </div>
        </div>
    </div>
</div>

<script>
(async function() {
    const podiumContainer = document.getElementById('podium-container');
    const rankListContainer = document.getElementById('rank-list-container');
    
    try {
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        const headers = { 'Accept': 'application/json' };
        
        var backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
        let url = backendUrl + '/api/public/leaderboard';
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
            url = backendUrl + '/api/tourist/leaderboard';
        }

        const cacheKey = 'leaderboard_data_v7_' + (token ? token.substring(0, 10) : 'public');
        const fetchCache = window.useCache || (async (key, fetcher, renderer) => { const d = await fetcher(); if(renderer) renderer(d); return d; });

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
                const leaders = data.users || data.leaders || [];
                const myRank = data.myRank || 999;
                const me = data.me || null;

                const countBadge = document.getElementById('explorers-count-badge');
                if (countBadge) {
                    countBadge.textContent = `${leaders.length} Explorers Listed`;
                }

                // Render Standing Banner
                const banner = document.getElementById('my-standing-banner');
                const rankCircle = document.getElementById('my-rank-circle');
                const subtext = document.getElementById('my-standing-subtext');
                const titleEl = document.getElementById('my-explorer-title');
                if (banner) {
                    banner.style.display = 'flex';
                    const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    const myId = me ? (me.id || me.user_id) : (authUser.id || authUser.user_id || '');
                    if (titleEl) {
                        titleEl.textContent = myId ? `Explorer #${myId}` : 'Your Standing';
                    }
                    if (rankCircle) {
                        if (myRank && myRank < 999) {
                            rankCircle.textContent = '#' + myRank;
                            rankCircle.style.fontSize = '13px';
                        } else {
                            rankCircle.textContent = 'NEW';
                            rankCircle.style.fontSize = '11px';
                        }
                    }
                    if (subtext) {
                        const meXp = me ? parseInt(me.total_points || me.total_xp || me.xp || 0) : (authUser.xp || 0);
                        subtext.textContent = `${meXp.toLocaleString()} XP • Check in to rank up!`;
                    }
                }

                // Render Podium
                let podiumHTML = '';
                if (leaders[1]) podiumHTML += generatePodiumPlace(leaders[1], 2);
                if (leaders[0]) podiumHTML += generatePodiumPlace(leaders[0], 1);
                if (leaders[2]) podiumHTML += generatePodiumPlace(leaders[2], 3);
                if (podiumContainer) podiumContainer.innerHTML = podiumHTML;

                // Render Rank List (Ranks 4+)
                let rankListHTML = '';
                if (leaders.length > 3) {
                    for (let i = 3; i < Math.min(leaders.length, 20); i++) {
                        const user = leaders[i];
                        const isMe = me && (user.id === me.id || user.user_id === me.id);
                        rankListHTML += generateRankItem(user, i + 1, isMe);
                    }
                }

                if (me && myRank > 20 && myRank <= 999) {
                    rankListHTML += generateRankItem(me, myRank, true);
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
            },
            Boolean(window.leaderboardNeedsRefresh),
            60000 // 1 minute TTL
        );
        window.leaderboardNeedsRefresh = false;

    } catch(e) {
        console.error("Leaderboard error:", e);
        if (podiumContainer) podiumContainer.innerHTML = "<div style='color:rgba(239,68,68,0.8); text-align:center; width:100%; padding:20px; font-size:14px;'>Failed to load leaderboard.</div>";
    }

    function getUserDisplayName(user) {
        if (user.is_leaderboard_private) return 'Private Explorer';
        const idToUse = user.user_id || user.id || 0;
        return `Explorer #${idToUse}`;
    }

    function generatePodiumPlace(user, rank) {
        const displayName = getUserDisplayName(user);
        const avatarName = displayName.replace(/#/g, '');
        let avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(avatarName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        if (avatarUrl && !avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:')) {
            avatarUrl = (window.backendUrl || '') + '/' + avatarUrl.replace(/^\//, '');
        }
        
        let medalIcon = '';
        let stepHeight = '70px';
        let badgeLabel = '3RD';

        if (rank === 1) {
            medalIcon = `<div style="position:absolute; top:-36px; left:50%; transform:translateX(-50%); animation: pulseCrown 2s infinite; z-index:10;"><i class="fa-solid fa-crown" style="color:#FFD700; font-size:28px; filter:drop-shadow(0 0 12px rgba(255,215,0,0.8));"></i></div>`;
            stepHeight = '110px';
            badgeLabel = '1ST';
        } else if (rank === 2) {
            medalIcon = `<div style="position:absolute; top:-30px; left:50%; transform:translateX(-50%); z-index:10;"><i class="fa-solid fa-medal" style="color:#e2e8f0; font-size:22px; filter:drop-shadow(0 0 8px rgba(226,232,240,0.6));"></i></div>`;
            stepHeight = '85px';
            badgeLabel = '2ND';
        } else if (rank === 3) {
            medalIcon = `<div style="position:absolute; top:-30px; left:50%; transform:translateX(-50%); z-index:10;"><i class="fa-solid fa-award" style="color:#f97316; font-size:22px; filter:drop-shadow(0 0 8px rgba(249,115,22,0.6));"></i></div>`;
            stepHeight = '68px';
            badgeLabel = '3RD';
        }
        
        const safeName = displayName.replace(/'/g, "\\'");
        const xp = parseInt(user.total_points || user.total_xp || user.xp || 0);
        const level = user.level || (Math.floor(xp / 1000) + 1);
        const activities = parseInt(user.completed_activities || user.places_visited || 0);
        const safeLocation = (user.home_location || '').replace(/'/g, "\\'");
        const safeBio = (user.bio || '').replace(/'/g, "\\'");
        
        return `
        <div class="podium-place place-${rank} rank-${rank}" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${xp}, ${rank}, ${level}, ${activities}, '${safeLocation}', '${safeBio}')">
            <div style="position:relative; margin-bottom:8px;">
                ${medalIcon}
                <img src="${avatarUrl}" alt="${displayName}" class="podium-avatar" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(avatarName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128';">
                <div class="podium-rank-badge rank-badge-${rank}">${rank}</div>
            </div>
            <div class="podium-name" style="font-weight:800; font-size:12px; line-height:1.2; text-align:center; max-width:100px; word-break:break-word;">${displayName}</div>
            <div class="podium-xp" style="margin-bottom:6px; margin-top:2px;"><i class="fa-solid fa-bolt" style="font-size:10px; color:#fbbf24;"></i> ${xp.toLocaleString()} XP</div>
            <div style="font-size:10px; color:rgba(226,232,240,0.7); font-weight:700; margin-bottom:8px;"><i class="fa-solid fa-map-location-dot" style="color:#34c759; font-size:9px;"></i> ${activities} visited</div>
            <div class="podium-block block-${rank}" style="height:${stepHeight};">
                <span class="block-label">${badgeLabel}</span>
            </div>
        </div>`;
    }

    function generateRankItem(user, rank, isMe) {
        const displayName = getUserDisplayName(user);
        const avatarName = displayName.replace(/#/g, '');
        let avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(avatarName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
        if (avatarUrl && !avatarUrl.startsWith('http') && !avatarUrl.startsWith('data:')) {
            avatarUrl = (window.backendUrl || '') + '/' + avatarUrl.replace(/^\//, '');
        }
        
        const activeClass = isMe ? 'is-me' : '';
        const youTag = isMe ? `<span style="font-size:9px; background:linear-gradient(135deg, #38bdf8, #2563eb); color:white; padding:2px 7px; border-radius:100px; font-weight:800; margin-left:6px; vertical-align:middle;">YOU</span>` : '';
        const delay = 0.5 + ((rank - 4) * 0.04);
        
        const safeName = displayName.replace(/'/g, "\\'");
        const xp = parseInt(user.total_points || user.total_xp || user.xp || 0);
        const level = user.level || (Math.floor(xp / 1000) + 1);
        const activities = parseInt(user.completed_activities || user.places_visited || 0);
        const safeLocation = (user.home_location || '').replace(/'/g, "\\'");
        const safeBio = (user.bio || '').replace(/'/g, "\\'");
        
        return `
        <div class="rank-item ${activeClass}" style="animation-delay: ${Math.max(0, delay)}s; display: flex; align-items: center; justify-content: space-between; padding: 12px 6px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); cursor: pointer; transition: background 0.15s;" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${xp}, ${rank}, ${level}, ${activities}, '${safeLocation}', '${safeBio}')">
            <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                <span style="font-size: 15px; font-weight: 800; color: ${isMe ? '#38bdf8' : 'rgba(255,255,255,0.6)'}; min-width: 22px; text-align: center; flex-shrink: 0;">${rank}</span>
                <img src="${avatarUrl}" alt="${displayName}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 1.5px solid ${isMe ? '#38bdf8' : 'rgba(255,255,255,0.15)'}; flex-shrink: 0;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(avatarName)}&background=007AFF&color=fff&rounded=true&bold=true&size=128';">
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 14px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center;">
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${displayName}</span>
                        ${youTag}
                    </div>
                    <div style="font-size: 11px; color: rgba(148, 163, 184, 0.75); font-weight: 600; margin-top: 1px;">
                        <span>Lvl ${level} Explorer</span> &bull; <span>${activities} Visited</span>
                    </div>
                </div>
            </div>
            <div style="font-size: 15px; font-weight: 800; color: #ffffff; flex-shrink: 0; text-align: right; margin-left: 12px;">
                ${xp.toLocaleString()} <span style="font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.5);">XP</span>
            </div>
        </div>`;
    }

    window.showUserProfile = function(name, avatar, xp, rank, level, activities, location, bio) {
        document.getElementById('modal-avatar').src = avatar;
        document.getElementById('modal-name').innerText = name;
        document.getElementById('modal-xp').innerText = xp.toLocaleString();
        document.getElementById('modal-rank-badge').innerText = rank;
        document.getElementById('modal-level').innerText = 'Lvl ' + level;
        document.getElementById('modal-activities').innerText = activities ? activities.toLocaleString() : '0';
        
        const elLoc = document.getElementById('modal-location');
        if (elLoc) {
            if (location && location.trim()) {
                elLoc.querySelector('span').innerText = location;
                elLoc.style.display = 'block';
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
    
    window.closeUserProfile = function() {
        document.getElementById('user-profile-modal').classList.remove('active');
    };
})();
</script>
