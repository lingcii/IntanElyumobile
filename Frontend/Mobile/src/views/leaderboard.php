<!-- Leaderboard View -->
<?php
$pageTitle = 'Top Explorers';
$activeTab = 'leaderboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="leaderboard-container has-header has-bottom-nav animate-fade-in">
    
    <!-- Title -->
    <div class="leaderboard-title stagger-0">
        <p>Climb the ranks by exploring places!</p>
    </div>
    
    <!-- Podium -->
    <div class="podium-container stagger-1" id="podium-container">
        <!-- Injected via JS -->
    </div>
    
    <!-- Rank List -->
    <div class="rank-list-wrapper stagger-2">
        <div class="rank-list" id="rank-list-container">
            <!-- Injected via JS -->
        </div>
    </div>
    
    </div>
</div>

<!-- User Profile Modal (Moved outside container to fix z-index stacking issues) -->
<div id="user-profile-modal" class="profile-modal-overlay">
    <div class="profile-modal-card">
        <button class="profile-modal-close" onclick="closeUserProfile()"><i class="fa-solid fa-xmark"></i></button>
        <div class="profile-modal-header">
            <img id="modal-avatar" src="" alt="Avatar">
            <div id="modal-rank-badge" class="modal-rank-badge">1</div>
        </div>
        <h2 id="modal-name">Name</h2>
        <div class="modal-stats">
            <div class="modal-stat-box">
                <i class="fa-solid fa-bolt" style="color:#FFD700;"></i>
                <span id="modal-xp">0</span>
                <small>Total XP</small>
            </div>
            <div class="modal-stat-box">
                <i class="fa-solid fa-map-location-dot" style="color:#10b981;"></i>
                <span id="modal-activities">0</span>
                <small>Visited</small>
            </div>
            <div class="modal-stat-box">
                <i class="fa-solid fa-medal" style="color:#38bdf8;"></i>
                <span id="modal-level">Lvl 1</span>
                <small>Explorer</small>
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
        
        var backendUrl = window.backendUrl || 'http://localhost:8000';
        let url = backendUrl + '/api/public/leaderboard';
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
            url = backendUrl + '/api/tourist/leaderboard';
        }

        const res = await fetch(url, { headers });
        if (!res.ok) throw new Error("Failed to fetch leaderboard");
        const data = await res.json();
        
        const leaders = data.users || data.leaders || [];
        const myRank = data.myRank || 999;
        const me = data.me || null;

        // Render Podium
        let podiumHTML = '';
        if (leaders[1]) podiumHTML += generatePodiumPlace(leaders[1], 2);
        if (leaders[0]) podiumHTML += generatePodiumPlace(leaders[0], 1);
        if (leaders[2]) podiumHTML += generatePodiumPlace(leaders[2], 3);
        podiumContainer.innerHTML = podiumHTML;

        // Render Rank List (Ranks 4-20)
        let rankListHTML = '';
        if (leaders.length === 0) {
            rankListHTML = '<div style="text-align:center; padding: 20px; color: var(--text-muted); font-size: 14px;">No explorers found yet. Start exploring to claim the #1 spot!</div>';
        } else {
            for (let i = 3; i < leaders.length; i++) {
                const user = leaders[i];
                const isMe = me && user.id === me.id;
                rankListHTML += generateRankItem(user, i + 1, isMe);
            }
        }

        if (me && myRank > 20) {
            rankListHTML += generateRankItem(me, myRank, true);
        }

        rankListContainer.innerHTML = rankListHTML;

    } catch(e) {
        console.error("Leaderboard error:", e);
        podiumContainer.innerHTML = "<div style='color:var(--text-dark); text-align:center; width:100%; margin-bottom:20px;'>Failed to load leaderboard.</div>";
    }

    function generatePodiumPlace(user, rank) {
        const idToUse = user.user_id || user.id;
        const displayName = `Explorer #${idToUse}`;
        const avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=random&color=fff`;
        const crown = rank === 1 ? `<div style="position:absolute; top:-35px; left:50%; width:30px; height:30px; margin-left:-15px; text-align:center; animation: pulseCrown 2s infinite; z-index:10;"><i class="fa-solid fa-crown" style="color:#FFD700; font-size:24px;"></i></div>` : '';
        
        const safeName = displayName.replace(/'/g, "\\'");
        const xp = parseInt(user.total_points || user.total_xp || 0);
        const level = user.level || 1;
        const activities = parseInt(user.completed_activities || 0);
        
        return `
        <div class="podium-place place-${rank}" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${xp}, ${rank}, ${level}, ${activities})">
            <div style="position:relative;">
                ${crown}
                <img src="${avatarUrl}" alt="${displayName}" class="podium-avatar">
            </div>
            <div class="podium-name">${displayName}</div>
            <div class="podium-xp"><i class="fa-solid fa-bolt" style="color:#38bdf8; font-size:10px;"></i> ${xp.toLocaleString()}</div>
            <div class="podium-block"></div>
        </div>`;
    }

    function generateRankItem(user, rank, isMe) {
        const idToUse = user.user_id || user.id;
        const displayName = `Explorer #${idToUse}`;
        const avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=random&color=fff`;
        const activeClass = isMe ? 'is-me' : '';
        const youTag = isMe ? `<span style="font-size:10px; background:var(--primary-color); color:white; padding:2px 6px; border-radius:10px; margin-left:6px; vertical-align:middle;">You</span>` : '';
        const delay = 0.5 + ((rank - 4) * 0.05);
        
        const safeName = displayName.replace(/'/g, "\\'");
        const xp = parseInt(user.total_points || user.total_xp || 0);
        const level = user.level || 1;
        const activities = parseInt(user.completed_activities || 0);
        
        return `
        <div class="rank-item ${activeClass}" style="animation-delay: ${Math.max(0, delay)}s;" onclick="showUserProfile('${safeName}', '${avatarUrl}', ${xp}, ${rank}, ${level}, ${activities})">
            <div class="rank-number-container">
                <div class="rank-number-badge">${rank}</div>
            </div>
            <img src="${avatarUrl}" alt="${displayName}" class="rank-item-avatar">
            <div class="rank-item-info">
                <h4 class="rank-item-name">${displayName} ${youTag}</h4>
                <div class="rank-item-xp-inline"><i class="fa-solid fa-bolt" style="color:#38bdf8; font-size:10px;"></i> ${xp.toLocaleString()}</div>
            </div>
        </div>`;
    }

    window.showUserProfile = function(name, avatar, xp, rank, level, activities) {
        document.getElementById('modal-avatar').src = avatar;
        document.getElementById('modal-name').innerText = name;
        document.getElementById('modal-xp').innerText = xp.toLocaleString();
        document.getElementById('modal-rank-badge').innerText = rank;
        document.getElementById('modal-level').innerText = 'Lvl ' + level;
        document.getElementById('modal-activities').innerText = activities ? activities.toLocaleString() : '0';
        document.getElementById('user-profile-modal').classList.add('active');
    };
    
    window.closeUserProfile = function() {
        document.getElementById('user-profile-modal').classList.remove('active');
    };
})();
</script>

<!-- Include Bottom Navigation Component -->


