<!-- Quests View -->
<?php
$pageTitle = 'Quests';
$backRoute = 'dashboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<style>
    body { background: var(--bg-primary) !important; }
    #bottom-navigation { display: none !important; opacity: 0 !important; pointer-events: none !important; visibility: hidden !important; }

    .quest-hero {
        position: relative;
        padding: 20px 0 8px;
        text-align: center;
        overflow: hidden;
    }
    .quest-hero-bg {
        position: absolute; inset: 0;
        background: radial-gradient(ellipse at 50% 0%, rgba(99,102,241,0.25) 0%, transparent 70%);
        pointer-events: none;
    }
    .quest-hero h1 { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: -0.8px; }
    .quest-hero p  { margin: 4px 0 0; font-size: 13px; color: rgba(255,255,255,0.55); }

    /* Time filter pills */
    .time-filter-row {
        display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin: 16px 0;
    }
    .time-filter-row::-webkit-scrollbar { display: none; }
    .time-pill {
        flex-shrink: 0; padding: 8px 18px; border-radius: 20px;
        font-size: 12px; font-weight: 700; cursor: pointer; border: none;
        background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
        color: rgba(255,255,255,0.6); transition: all 0.2s;
    }
    .time-pill.active {
        background: linear-gradient(135deg,rgba(99,102,241,0.3),rgba(56,189,248,0.3));
        border-color: rgba(99,102,241,0.5); color: #fff;
    }

    /* Quest Cards */
    .quest-cards-grid { display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px; }
    .quest-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 20px; padding: 18px 16px;
        cursor: pointer; transition: all 0.25s;
        position: relative; overflow: hidden;
    }
    .quest-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--quest-color, #38bdf8);
        opacity: 0.7; border-radius: 20px 20px 0 0;
    }
    .quest-card:active { transform: scale(0.98); }
    .quest-card.completed { opacity: 0.65; }
    .quest-card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
    .quest-icon-box {
        width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
        background: var(--quest-color-bg, rgba(56,189,248,0.12));
        border: 1px solid var(--quest-color-border, rgba(56,189,248,0.2));
    }
    .quest-title { font-size: 15px; font-weight: 800; color: #fff; margin: 0 0 2px; }
    .quest-subtitle { font-size: 11px; color: rgba(255,255,255,0.5); margin: 0; }
    .quest-meta-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
    .quest-tag {
        display: flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 10px; font-size: 11px; font-weight: 600;
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.65);
    }
    .quest-xp-badge {
        margin-left: auto; font-size: 12px; font-weight: 800;
        color: #fbbf24; display: flex; align-items: center; gap: 4px;
        background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.2);
        padding: 4px 10px; border-radius: 10px;
    }
    .quest-badge-preview {
        display: flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,0.03); border-radius: 12px;
        padding: 8px 12px; font-size: 11px; color: rgba(255,255,255,0.55);
    }
    .quest-badge-preview strong { color: rgba(255,255,255,0.85); }
    .quest-completed-stamp {
        position: absolute; top: 14px; right: 14px;
        background: rgba(52,211,153,0.15); border: 1px solid rgba(52,211,153,0.3);
        color: #34d399; border-radius: 10px; padding: 3px 10px;
        font-size: 10px; font-weight: 800; letter-spacing: 0.5px;
    }
    .btn-start-quest {
        width: 100%; padding: 12px; border-radius: 14px; font-weight: 700; font-size: 13px;
        border: none; cursor: pointer; margin-top: 12px;
        background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(56,189,248,0.2));
        border: 1px solid rgba(99,102,241,0.35); color: #a5b4fc;
        transition: all 0.2s;
    }
    .btn-start-quest:active { transform: scale(0.97); }

    /* Quest Route Modal */
    .quest-modal-overlay {
        position: fixed; inset: 0; z-index: 9000;
        background: rgba(0,0,0,0.8); backdrop-filter: blur(8px);
        display: flex; align-items: flex-end;
        opacity: 0; pointer-events: none; transition: opacity 0.3s;
    }
    .quest-modal-overlay.active { opacity: 1; pointer-events: all; }
    .quest-modal-sheet {
        width: 100%; max-height: 90vh; overflow-y: auto;
        background: #0f172a; border-radius: 28px 28px 0 0;
        padding: 24px 20px 40px; border-top: 1px solid rgba(255,255,255,0.1);
        transform: translateY(100%); transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
    }
    .quest-modal-overlay.active .quest-modal-sheet { transform: translateY(0); }
    .quest-modal-drag-handle {
        width: 40px; height: 4px; background: rgba(255,255,255,0.2);
        border-radius: 2px; margin: 0 auto 20px; display: block;
    }
    .quest-stop-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 14px; border-radius: 16px;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);
        margin-bottom: 8px;
    }
    .quest-stop-num {
        width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 800; color: #fff;
    }
    .quest-stop-info { flex: 1; }
    .quest-stop-name { font-size: 13px; font-weight: 700; color: #fff; }
    .quest-stop-meta { font-size: 11px; color: rgba(255,255,255,0.45); margin-top: 2px; }
    .quest-stop-connector {
        width: 2px; height: 16px; background: rgba(255,255,255,0.1);
        margin: 0 auto 0 27px; display: block;
    }

    /* Badge shelf */
    .badge-shelf { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 20px; }
    .badge-shelf::-webkit-scrollbar { display: none; }
    .badge-card {
        flex-shrink: 0; width: 90px; background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;
        padding: 12px 8px; text-align: center;
    }
    .badge-card .icon { font-size: 26px; margin-bottom: 6px; }
    .badge-card .name { font-size: 9px; font-weight: 700; color: rgba(255,255,255,0.7); line-height: 1.3; }
    .badge-empty {
        text-align: center; padding: 20px; color: rgba(255,255,255,0.3);
        font-size: 13px; border: 1px dashed rgba(255,255,255,0.1); border-radius: 16px;
    }

    /* Loading spinner */
    .quest-spinner { text-align: center; padding: 30px 0; color: rgba(255,255,255,0.4); font-size: 13px; }
    .spinning { animation: spin 1s linear infinite; display: inline-block; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<div class="itinerary-container has-header animate-fade-in" style="padding-top: 88px; padding-bottom: 40px;">

    <!-- Hero -->
    <div class="quest-hero stagger-0">
        <div class="quest-hero-bg"></div>
        <h1 style="display:flex; align-items:center; justify-content:center; gap:8px;"><i class="fa-solid fa-compass" style="color:#38bdf8;"></i> Quests & Expeditions</h1>
        <p>Choose an expedition and explore La Union's top destinations</p>
    </div>

    <!-- My Badges -->
    <div class="stagger-1" style="margin-top: 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
            <h3 style="margin:0; font-size:14px; font-weight:800; display:flex; align-items:center; gap:6px;"><i class="fa-solid fa-medal" style="color:#f59e0b;"></i> My Badges & Trophies</h3>
            <div style="display:flex; align-items:center; gap:8px;">
                <span id="badge-count-label" style="font-size:11px; color:rgba(255,255,255,0.4);">Loading...</span>
                <button onclick="window.openAllBadgesModal()" style="background: rgba(251, 191, 36, 0.12); border: 1px solid rgba(251, 191, 36, 0.3); color: #fbbf24; font-size: 11px; font-weight: 800; cursor: pointer; padding: 3px 10px; border-radius: 100px; display: flex; align-items: center; gap: 4px; transition: all 0.2s ease;">
                    View All <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i>
                </button>
            </div>
        </div>
        <div class="badge-shelf" id="badge-shelf">
            <div class="badge-empty" style="width:100%;"><i class="fa-solid fa-trophy" style="color:#f59e0b; margin-right:6px;"></i> Complete quests to earn badges & rewards!</div>
        </div>
    </div>

    <!-- Time Filter -->
    <div class="stagger-2" style="margin-top: 4px;">
        <p style="margin:0 0 8px; font-size:11px; color:rgba(148,163,184,0.7); font-weight:800; text-transform:uppercase; letter-spacing:0.5px;"><i class="fa-solid fa-clock" style="color:#38bdf8; margin-right:4px;"></i> Available Time</p>
        <div class="time-filter-row">
            <button class="time-pill active" onclick="setTimeFilter(99, this)">All</button>
            <button class="time-pill" onclick="setTimeFilter(1, this)">1 hr</button>
            <button class="time-pill" onclick="setTimeFilter(2, this)">2 hrs</button>
            <button class="time-pill" onclick="setTimeFilter(3, this)">3 hrs</button>
            <button class="time-pill" onclick="setTimeFilter(4, this)">4 hrs</button>
            <button class="time-pill" onclick="setTimeFilter(99, this)">Full Day</button>
        </div>
    </div>

    <!-- Quest Cards -->
    <div class="stagger-3">
        <h3 style="margin:0 0 12px; font-size:14px; font-weight:800; display:flex; align-items:center; gap:6px;"><i class="fa-solid fa-map-location-dot" style="color:#34c759;"></i> Available Quests</h3>
        <div class="quest-cards-grid" id="quest-cards-container">
            <div class="quest-spinner"><span class="spinning">⟳</span> Loading quests...</div>
        </div>
    </div>

</div>

<!-- Quest Route Modal -->
<div class="quest-modal-overlay" id="quest-modal-overlay">
    <div class="quest-modal-sheet" id="quest-modal-sheet">
        <span class="quest-modal-drag-handle"></span>

        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
            <div id="modal-quest-icon-box" class="quest-icon-box" style="width:52px;height:52px;font-size:26px;"></div>
            <div>
                <div id="modal-quest-title" style="font-size:18px; font-weight:900; color:#fff;"></div>
                <div id="modal-quest-subtitle" style="font-size:12px; color:rgba(255,255,255,0.5);"></div>
            </div>
        </div>

        <!-- Quest Description Details Box -->
        <div id="modal-quest-description-box" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:14px; padding:12px 14px; margin-bottom:16px;">
            <p id="modal-quest-description" style="margin:0; font-size:12.5px; color:rgba(255,255,255,0.75); line-height:1.5; font-weight:500;"></p>
        </div>

        <!-- Quest Stats Row -->
        <div style="display:flex; gap:10px; margin-bottom:18px;">
            <div style="flex:1; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-radius:14px; padding:12px; text-align:center;">
                <div id="modal-stop-count" style="font-size:20px; font-weight:900; color:#38bdf8;">—</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.45);">Stops</div>
            </div>
            <div style="flex:1; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-radius:14px; padding:12px; text-align:center;">
                <div id="modal-est-hours" style="font-size:20px; font-weight:900; color:#a78bfa;">—</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.45);">Est. Hours</div>
            </div>
            <div style="flex:1; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-radius:14px; padding:12px; text-align:center;">
                <div id="modal-distance" style="font-size:20px; font-weight:900; color:#34d399;">—</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.45);">km</div>
            </div>
            <div style="flex:1; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-radius:14px; padding:12px; text-align:center;">
                <div id="modal-xp-reward" style="font-size:20px; font-weight:900; color:#fbbf24;">—</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.45);">XP</div>
            </div>
        </div>

        <!-- Stop List -->
        <h4 style="margin:0 0 12px; font-size:13px; font-weight:800; color:rgba(255,255,255,0.7); display:flex; align-items:center; gap:6px;"><i class="fa-solid fa-location-crosshairs" style="color:#38bdf8;"></i> Quest Destinations & Route</h4>
        <div id="modal-stop-list">
            <div class="quest-spinner"><span class="spinning">⟳</span> Generating your route...</div>
        </div>

        <!-- Badge reward preview -->
        <div id="modal-badge-preview" style="display:none; margin-top:16px; background:linear-gradient(135deg,rgba(251,191,36,0.08),rgba(245,158,11,0.04)); border:1px solid rgba(251,191,36,0.2); border-radius:16px; padding:14px; display:flex; align-items:center; gap:12px;">
            <div style="font-size:28px;" id="modal-badge-icon">🏅</div>
            <div>
                <div style="font-size:12px; color:rgba(255,255,255,0.5);">Complete this quest to earn</div>
                <div id="modal-badge-name" style="font-size:14px; font-weight:800; color:#fbbf24;"></div>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:16px;">
            <button onclick="closeQuestModal()" style="flex:1; padding:14px; border-radius:16px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.7); font-weight:700; font-size:14px; cursor:pointer;">Close</button>
            <button id="btn-open-in-map" onclick="openQuestInMap()" style="flex:2; padding:14px; border-radius:16px; background:linear-gradient(135deg,#6366f1,#38bdf8); border:none; color:#fff; font-weight:800; font-size:14px; cursor:pointer;">
                <i class="fa-solid fa-flag-checkered" style="margin-right:8px;"></i>Start Quest
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const backendUrl = window.backendUrl || 'https://api.intan-elyu.online';
    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    const headers = { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token };

    let allQuests = [];
    let currentTimeFilter = 99;
    let activeQuestData = null; // holds generate() result for navigation

    // ── Load badges ───────────────────────────────────────────────────────────
    async function loadBadges() {
        try {
            const res = await fetch(backendUrl + '/api/tourist/quests/my-completions', { headers });
            const data = await res.json();
            const shelf = document.getElementById('badge-shelf');
            const count = document.getElementById('badge-count-label');

            const badges = data.badges || [];
            window._cachedMasterBadges = badges;
            if (!badges.length) {
                shelf.innerHTML = '<div class="badge-empty" style="width:100%;"><i class="fa-solid fa-trophy" style="color:#f59e0b; margin-right:6px;"></i> Complete quests to earn badges & rewards!</div>';
                count.textContent = '0 badges';
                return;
            }

            const unlockedCount = data.badge_count || 0;
            const totalCount = data.total_badge_count || badges.length;
            count.textContent = `${unlockedCount} / ${totalCount} badges`;

            const sortedBadges = [...badges].sort((a, b) => (b.is_unlocked ? 1 : 0) - (a.is_unlocked ? 1 : 0));

            shelf.innerHTML = sortedBadges.map(b => {
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
                    <div class="badge-card" ${clickAction} style="background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.3); border-radius:16px; padding:10px 14px; display:inline-flex; align-items:center; gap:10px; min-width:135px; flex-shrink:0; cursor:pointer; transition:all 0.2s ease;">
                        <div class="icon" style="font-size:22px; color:#fbbf24;">${displayIcon}</div>
                        <div>
                            <div class="name" style="font-size:12px; font-weight:800; color:#fbbf24;">${b.name}</div>
                            <div style="font-size:9.5px; color:rgba(255,255,255,0.6);"><i class="fa-solid fa-circle-check" style="font-size:8.5px; margin-right:2px;"></i> Unlocked</div>
                        </div>
                    </div>`;
                } else {
                    return `
                    <div class="badge-card locked" ${clickAction} style="background:rgba(255,255,255,0.02); border:1px dashed rgba(255,255,255,0.12); border-radius:16px; padding:10px 14px; display:inline-flex; align-items:center; gap:10px; min-width:135px; flex-shrink:0; opacity:0.6; filter:grayscale(1); cursor:pointer; transition:all 0.2s ease;" title="${b.description}">
                        <div class="icon" style="font-size:20px; color:rgba(255,255,255,0.4);"><i class="fa-solid fa-lock"></i></div>
                        <div>
                            <div class="name" style="font-size:12px; font-weight:700; color:rgba(255,255,255,0.6);">${b.name}</div>
                            <div style="font-size:9.5px; color:#f87171;"><i class="fa-solid fa-lock" style="font-size:8.5px; margin-right:2px;"></i> Locked</div>
                        </div>
                    </div>`;
                }
            }).join('');
        } catch(e) {
            console.warn('Badge load error:', e);
        }
    }

    // ── Load quests ───────────────────────────────────────────────────────────
    async function loadQuests(hours = 99) {
        const container = document.getElementById('quest-cards-container');
        container.innerHTML = '<div class="quest-spinner"><span class="spinning">⟳</span> Loading quests...</div>';

        try {
            // Sync active quest timers against real active itineraries
            try {
                const itinRes = await fetch(backendUrl + '/api/tourist/itineraries', { headers });
                const itinData = await itinRes.json();
                const activeItins = itinData.itineraries || [];
                const activeTimers = JSON.parse(localStorage.getItem('active_quests_timers') || '{}');
                let timerChanged = false;

                Object.keys(activeTimers).forEach(qId => {
                    const questName = (activeTimers[qId].questName || '').toLowerCase();
                    const exists = activeItins.some(itin => 
                        (itin.status === 'pending' || itin.status === 'in_progress') && 
                        itin.title.toLowerCase().includes(questName)
                    );
                    if (!exists) {
                        delete activeTimers[qId];
                        timerChanged = true;
                    }
                });

                if (timerChanged) {
                    localStorage.setItem('active_quests_timers', JSON.stringify(activeTimers));
                }
            } catch(err) {
                console.warn('Itinerary sync warning:', err);
            }

            const url = `${backendUrl}/api/tourist/quests?hours=${hours}`;
            const res = await fetch(url, { headers });
            const data = await res.json();

            allQuests = data.quests || [];
            renderQuestCards(allQuests);
        } catch(e) {
            container.innerHTML = '<div class="quest-spinner">⚠️ Failed to load quests. Please try again.</div>';
        }
    }

    function renderQuestCards(quests) {
        const container = document.getElementById('quest-cards-container');
        if (!quests.length) {
            container.innerHTML = '<div class="quest-spinner">No quests available for this time window.</div>';
            return;
        }

        const activeTimers = JSON.parse(localStorage.getItem('active_quests_timers') || '{}');
        const now = Date.now();

        container.innerHTML = quests.map(q => {
            const colorBg   = hexToRgba(q.theme_color, 0.12);
            const colorBdr  = hexToRgba(q.theme_color, 0.25);
            const completedStamp = q.is_completed
                ? '<span class="quest-completed-stamp"><i class="fa-solid fa-check-double" style="margin-right:3px;"></i> DONE</span>'
                : '';

            const timer = activeTimers[q.id];
            const isActive = timer && (now < timer.expiresAt);
            const hrsLeft = isActive ? Math.max(1, Math.ceil((timer.expiresAt - now) / (1000 * 60 * 60))) : 0;

            const iconMap = {
                'Historical': '<i class="fa-solid fa-landmark" style="color:#f59e0b; font-size:22px;"></i>',
                'Food Destination': '<i class="fa-solid fa-utensils" style="color:#ef4444; font-size:22px;"></i>',
                'Beach': '<i class="fa-solid fa-umbrella-beach" style="color:#06b6d4; font-size:22px;"></i>',
                'Waterfalls': '<i class="fa-solid fa-water" style="color:#10b981; font-size:22px;"></i>',
                'Adventure': '<i class="fa-solid fa-person-hiking" style="color:#8b5cf6; font-size:22px;"></i>',
            };
            const displayIcon = iconMap[q.category] || `<span style="font-size:22px;">${q.theme_icon || '🧭'}</span>`;

            const activeTag = isActive ? `<span class="quest-tag" style="background:rgba(239,68,68,0.2); border:1px solid rgba(239,68,68,0.4); color:#f87171; font-weight:800;"><i class="fa-solid fa-fire" style="margin-right:3px;"></i> Active (${hrsLeft}h left)</span>` : '';
            const activeBorderStyle = isActive ? 'border:1.5px solid rgba(16, 185, 129, 0.6); box-shadow:0 0 20px rgba(16,185,129,0.15);' : '';
            const cardClickHandler = isActive 
                ? "if (typeof navigateTo === 'function') navigateTo('saved_trips');" 
                : `openQuestModal(${q.id}, ${q.required_hours})`;

            let actionBtn = '';
            if (!q.is_completed) {
                if (isActive) {
                    actionBtn = `<button class="btn-start-quest" onclick="event.stopPropagation(); if (typeof navigateTo === 'function') navigateTo('saved_trips');" style="background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                        <i class="fa-solid fa-route"></i> View in Saved Trips <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                    </button>`;
                } else {
                    actionBtn = `<button class="btn-start-quest" style="display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                        <i class="fa-solid fa-eye"></i> View Quest <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                    </button>`;
                }
            }

            return `
            <div class="quest-card ${q.is_completed ? 'completed' : ''}"
                 style="--quest-color: ${q.theme_color}; --quest-color-bg: ${colorBg}; --quest-color-border: ${colorBdr}; ${activeBorderStyle}"
                 onclick="${cardClickHandler}">
                ${completedStamp}
                <div class="quest-card-header">
                    <div class="quest-icon-box"
                         style="background:${colorBg}; border-color:${colorBdr}; display:flex; align-items:center; justify-content:center;">
                        ${displayIcon}
                    </div>
                    <div>
                        <p class="quest-title">${q.name}</p>
                        <p class="quest-subtitle">${q.description ? q.description.substring(0,65) + '…' : ''}</p>
                    </div>
                </div>
                <div class="quest-meta-row" style="display:flex; align-items:center; justify-content:space-between; width:100%; margin-bottom:12px;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        ${activeTag}
                        <span class="quest-tag"><i class="fa-solid fa-clock" style="font-size:9px; color:#38bdf8; margin-right:3px;"></i> ${q.required_hours}h</span>
                        <span class="quest-tag"><i class="fa-solid fa-location-dot" style="font-size:9px; color:#34c759; margin-right:3px;"></i> ${q.spot_count} ${q.spot_count === 1 ? 'stop' : 'stops'}</span>
                    </div>
                    <span class="quest-xp-badge" style="margin-left:0; white-space:nowrap;"><i class="fa-solid fa-bolt" style="color:#fbbf24; margin-right:3px;"></i> +${q.xp_reward} XP</span>
                </div>
                <div class="quest-badge-preview" style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-award" style="color:#fbbf24; font-size:16px;"></i>
                    <span>Earn badge: <strong>${q.badge_name || 'Explorer Badge'}</strong></span>
                </div>
                ${actionBtn}
            </div>`;
        }).join('');
    }

    // ── Time filter ───────────────────────────────────────────────────────────
    window.setTimeFilter = function(hours, el) {
        document.querySelectorAll('.time-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        currentTimeFilter = hours;
        loadQuests(hours);
    };

    // ── Quest Route Modal ─────────────────────────────────────────────────────
    window.openQuestModal = async function(questId, requiredHours) {
        const overlay = document.getElementById('quest-modal-overlay');
        overlay.classList.add('active');

        // Reset
        window.setHtml('modal-stop-list', '<div class="quest-spinner"><span class="spinning">⟳</span> Generating your route...</div>');
        window.setTxt('modal-stop-count', '—');
        window.setTxt('modal-est-hours', '—');
        window.setTxt('modal-distance', '—');
        window.setTxt('modal-xp-reward', '—');

        const quest = allQuests.find(q => q.id === questId);
        if (quest) {
            const iconMap = {
                'Historical': '<i class="fa-solid fa-landmark" style="color:#f59e0b; font-size:26px;"></i>',
                'Food Destination': '<i class="fa-solid fa-utensils" style="color:#ef4444; font-size:26px;"></i>',
                'Beach': '<i class="fa-solid fa-umbrella-beach" style="color:#06b6d4; font-size:26px;"></i>',
                'Waterfalls': '<i class="fa-solid fa-water" style="color:#10b981; font-size:26px;"></i>',
                'Adventure': '<i class="fa-solid fa-person-hiking" style="color:#8b5cf6; font-size:26px;"></i>',
            };
            window.setHtml('modal-quest-icon-box', iconMap[quest.category] || quest.theme_icon || '🧭');
            window.setTxt('modal-quest-title', quest.name);
            window.setTxt('modal-quest-subtitle', `${quest.required_hours}h · ${quest.spot_count} ${quest.spot_count === 1 ? 'stop' : 'stops'}`);
            window.setTxt('modal-quest-description', quest.description || 'Explore top hand-picked destinations on this quest across La Union.');
            window.setTxt('modal-xp-reward', quest.xp_reward);
            window.setHtml('modal-badge-icon', '<i class="fa-solid fa-award" style="color:#fbbf24; font-size:28px;"></i>');
            window.setTxt('modal-badge-name', quest.badge_name || 'Explorer Badge');
            const badgePrev = document.getElementById('modal-badge-preview');
            if (badgePrev) badgePrev.style.display = 'flex';

            const activeTimers = JSON.parse(localStorage.getItem('active_quests_timers') || '{}');
            const isQuestActive = activeTimers[questId] && (Date.now() < activeTimers[questId].expiresAt);
            const btn = document.getElementById('btn-open-in-map');
            if (btn) {
                if (isQuestActive) {
                    btn.innerHTML = '<i class="fa-solid fa-route" style="margin-right:8px;"></i>View in Saved Trips →';
                    btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                    btn.onclick = function() {
                        closeQuestModal();
                        if (typeof navigateTo === 'function') navigateTo('saved_trips');
                    };
                } else {
                    btn.innerHTML = '<i class="fa-solid fa-flag-checkered" style="margin-right:8px;"></i>Start Quest';
                    btn.style.background = 'linear-gradient(135deg,#6366f1,#38bdf8)';
                    btn.onclick = function() { openQuestInMap(); };
                }
            }
        }

        try {
            // Get user GPS for smarter ordering
            let params = `hours=${currentTimeFilter < 99 ? currentTimeFilter : requiredHours}`;
            if (navigator.geolocation) {
                await new Promise(resolve => {
                    navigator.geolocation.getCurrentPosition(
                        pos => {
                            params += `&start_lat=${pos.coords.latitude}&start_lng=${pos.coords.longitude}`;
                            resolve();
                        },
                        () => resolve(), { timeout: 3000 }
                    );
                });
            }

            const res  = await fetch(`${backendUrl}/api/tourist/quests/${questId}/generate?${params}`, { headers });
            const data = await res.json();

            if (data.status !== 'success') throw new Error(data.message || 'Generation failed');

            activeQuestData = data;
            window.setTxt('modal-stop-count', data.spot_count);
            window.setTxt('modal-est-hours', data.estimated_hours);
            window.setTxt('modal-distance', data.total_distance_km);

            if (!data.spots || data.spots.length === 0) {
                document.getElementById('modal-stop-list').innerHTML =
                    '<div style="text-align:center;padding:20px;color:rgba(255,255,255,0.4);">No spots found for this quest yet. The admin is configuring spots!</div>';
                return;
            }

            let stopsHTML = '';
            data.spots.forEach((spot, i) => {
                const isLast = i === data.spots.length - 1;
                const colors = ['#38bdf8','#a78bfa','#34d399','#fb923c','#f472b6','#fbbf24'];
                const color  = colors[i % colors.length];
                stopsHTML += `
                    <div class="quest-stop-item">
                        <div class="quest-stop-num" style="background:${color}20;color:${color};border:1px solid ${color}40;">${i+1}</div>
                        <div class="quest-stop-info">
                            <div class="quest-stop-name">${spot.name}</div>
                            <div class="quest-stop-meta">${spot.category || 'Attraction'} · ₱${spot.entrance_fee || 0}</div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:rgba(255,255,255,0.2);font-size:12px;"></i>
                    </div>
                    ${!isLast ? '<div class="quest-stop-connector"></div>' : ''}
                `;
            });
            document.getElementById('modal-stop-list').innerHTML = stopsHTML;

        } catch(e) {
            document.getElementById('modal-stop-list').innerHTML =
                `<div class="quest-spinner">⚠️ ${e.message || 'Failed to generate route.'}</div>`;
        }
    };

    window.closeQuestModal = function() {
        document.getElementById('quest-modal-overlay').classList.remove('active');
    };

    window.openQuestInMap = async function() {
        if (!activeQuestData || !activeQuestData.spots || !activeQuestData.spots.length) return;

        const quest     = activeQuestData.quest || {};
        const questId   = quest.id;
        const questName = quest.name || 'Quest';
        const spotIds   = activeQuestData.optimized_ids || activeQuestData.spots.map(s => s.id);

        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
        if (!token) return;

        try {
            // 1. Post to backend startQuest endpoint
            await fetch(`${backendUrl}/api/tourist/quests/${questId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ spot_ids: spotIds })
            });

            // 2. Save active 24h quest timer in localStorage
            const expiresAt    = Date.now() + (24 * 60 * 60 * 1000);
            const activeTimers = JSON.parse(localStorage.getItem('active_quests_timers') || '{}');
            activeTimers[questId] = {
                expiresAt: expiresAt,
                questName: questName,
                spotIds: spotIds
            };
            localStorage.setItem('active_quests_timers', JSON.stringify(activeTimers));

            // Invalidate saved trips cache
            localStorage.removeItem('saved_trips_' + token.substring(0, 10));

            if (typeof showToast === 'function') {
                showToast(`Quest "${questName}" activated! Added to Saved Trips. 🚀`);
            }

            closeQuestModal();

            // 3. Navigate directly to My Saved Trips
            setTimeout(() => {
                if (typeof navigateTo === 'function') navigateTo('saved_trips');
            }, 250);

        } catch (e) {
            console.error("Start quest error:", e);
            closeQuestModal();
            if (typeof navigateTo === 'function') navigateTo('saved_trips');
        }
    };

    // Close on overlay click
    const overlay = document.getElementById('quest-modal-overlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) closeQuestModal();
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function hexToRgba(hex, alpha) {
        if (!hex) return `rgba(99,102,241,${alpha})`;
        const r = parseInt(hex.slice(1,3),16) || 99;
        const g = parseInt(hex.slice(3,5),16) || 102;
        const b = parseInt(hex.slice(5,7),16) || 241;
        return `rgba(${r},${g},${b},${alpha})`;
    }

    // Export filter helper to window
    window.filterQuests = function(btn, hours) {
        document.querySelectorAll('.time-pill').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        currentTimeFilter = hours;
        loadQuests(hours);
    };

    // ── Init ──────────────────────────────────────────────────────────────────
    setTimeout(() => {
        loadBadges();
        loadQuests(99);
    }, 50);
})();
</script>
