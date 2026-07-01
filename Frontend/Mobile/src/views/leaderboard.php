<!-- Leaderboard View -->
<?php
$pageTitle = 'Top Explorers';
$activeTab = 'leaderboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="leaderboard-container has-header has-bottom-nav animate-fade-in">
    
    <!-- Title -->
    <div class="leaderboard-title stagger-0">
        <h1>Top Explorers</h1>
        <p>Climb the ranks by exploring places!</p>
    </div>
    
    <!-- Podium -->
    <div class="podium-container stagger-1" id="podium-container">
        <!-- Injected via JS -->
    </div>
    
    <!-- Rank List -->
    <div class="rank-list stagger-2" id="rank-list-container">
        <!-- Injected via JS -->
    </div>
    
</div>

<script>
(async function() {
    const podiumContainer = document.getElementById('podium-container');
    const rankListContainer = document.getElementById('rank-list-container');
    
    try {
        const token = localStorage.getItem('Intan_Elyu_Token');
        const headers = { 'Accept': 'application/json' };
        
        let url = '/api/public/leaderboard';
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
            url = '/api/tourist/leaderboard'; // Will be prefixed by proxy or relative if on same domain
        }

        // Fix absolute URL for mobile webview
        if (window.location.protocol === 'file:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            url = '../api' + url.replace('/api', '');
        } else {
            url = '../api' + url.replace('/api', '');
        }

        const res = await fetch(url, { headers });
        if (!res.ok) throw new Error("Failed to fetch leaderboard");
        const data = await res.json();
        
        const leaders = data.leaders || [];
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
        for (let i = 3; i < leaders.length; i++) {
            const user = leaders[i];
            const isMe = me && user.id === me.id;
            rankListHTML += generateRankItem(user, i + 1, isMe);
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
        const displayName = `Explorer #${user.id}`;
        const avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=random&color=fff`;
        const crown = rank === 1 ? `<div style="position:absolute; top:-35px; left:50%; width:30px; height:30px; margin-left:-15px; text-align:center; animation: pulseCrown 2s infinite; z-index:10;"><i class="fa-solid fa-crown" style="color:#FFD700; font-size:24px;"></i></div>` : '';
        return `
        <div class="podium-place place-${rank}">
            <div style="position:relative;">
                ${crown}
                <img src="${avatarUrl}" alt="${displayName}" class="podium-avatar">
                <div class="rank-badge">${rank}</div>
            </div>
            <div class="podium-name">${displayName}</div>
            <div class="podium-xp">${parseInt(user.total_xp).toLocaleString()} XP</div>
            <div class="podium-block"></div>
        </div>`;
    }

    function generateRankItem(user, rank, isMe) {
        const displayName = `Explorer #${user.id}`;
        const avatarUrl = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=random&color=fff`;
        const activeStyle = isMe ? 'border-color: var(--primary-color); background: rgba(0, 122, 255, 0.05); box-shadow: 0 4px 15px rgba(0, 122, 255, 0.15);' : '';
        const numBadgeClass = rank <= 10 ? 'rank-badge-top10' : 'rank-badge-normal';
        const numBadgeStyle = isMe ? 'background: var(--primary-color); color: white; border-color: var(--primary-color);' : '';
        const imgStyle = isMe ? 'border: 2px solid var(--primary-color); padding: 2px;' : '';
        const youTag = isMe ? `<span style="font-size:10px; background:var(--primary-color); color:white; padding:2px 6px; border-radius:10px; margin-left:6px; vertical-align:middle;">You</span>` : '';
        
        return `
        <div class="rank-item" style="${activeStyle}">
            <div class="rank-number-container">
                <div class="rank-number-badge ${numBadgeClass}" style="${numBadgeStyle}">${rank}</div>
            </div>
            <img src="${avatarUrl}" alt="${displayName}" class="rank-item-avatar" style="${imgStyle}">
            <div class="rank-item-info">
                <h4 class="rank-item-name">${displayName} ${youTag}</h4>
                <p class="rank-item-level"><i class="fa-solid fa-medal" style="color:#CD7F32; margin-right:4px;"></i> Lvl ${user.level} Explorer</p>
            </div>
            <div class="rank-item-xp"><i class="fa-solid fa-bolt" style="color:#FFD700; margin-right:4px;"></i> ${parseInt(user.total_xp).toLocaleString()}</div>
        </div>`;
    }
})();
</script>

<!-- Include Bottom Navigation Component -->

