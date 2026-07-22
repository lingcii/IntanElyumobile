/**
 * LUPTO Tourist Leaderboard API
 * Role: lupto (read-only)
 * Uses centralized API_CONFIG for real-time database fetching
 */

'use strict';

const LB_API = window.API_CONFIG?.LUPTO + '/leaderboard' || 'http://localhost:8000/api/lupto/leaderboard';

// Map legacy action to Laravel routes
function lbActionToUrl(action, params = {}) {
    const map = {
        'get_kpis':        `${LB_API}/kpis`,
        'get_top3':        `${LB_API}/top3`,
        'get_leaderboard': `${LB_API}`,
    };
    const base = map[action] || `${LB_API}/${action.replace(/_/g,'-')}`;
    const qs   = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
    return base + qs;
}

// ── State ─────────────────────────────────────────────────────────────────────
let _maxPoints   = 0;   // highest points across all loaded users (for progress-bar scaling)
let _currentPage = 1;
let _totalRows   = 0;
const PAGE_SIZE  = 20;

// Debounce timer
let _searchTimer = null;

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    refreshAll();

    // Real-time auto-refresh every 30 seconds
    setInterval(refreshAll, 30000);
});

// ── Public: full refresh (called by Refresh button & on load) ─────────────────
async function refreshAll() {
    const icon = document.getElementById('refreshIcon');
    if (icon) icon.classList.add('fa-spin');

    _currentPage = 1;

    await Promise.all([
        loadKpis(),
        loadTop3(),
        loadTable(),
    ]);

    if (icon) icon.classList.remove('fa-spin');
}

// ── Core fetch helper using API_CONFIG
async function apiFetch(action, params = {}) {
    const url = lbActionToUrl(action, params);

    try {
        const data = await window.API_CONFIG.fetch(url);
        return data;
    } catch (netErr) {
        throw new Error('Network error: ' + netErr.message);
    }
}

// ── KPI Strip ─────────────────────────────────────────────────────────────────
async function loadKpis() {
    try {
        const { kpis } = await apiFetch('get_kpis');

        setText('kpiUsers',       formatNum(kpis.total_users));
        setText('kpiHighest',     formatNum(kpis.highest_points) + ' pts');
        setText('kpiActivities',  formatNum(kpis.total_activities));
        setText('kpiGrandPoints', formatNum(kpis.grand_points) + ' pts');

        _maxPoints = kpis.highest_points || 1;

    } catch (err) {
        console.error('[LB] loadKpis:', err);
        ['kpiUsers','kpiHighest','kpiActivities','kpiGrandPoints']
            .forEach(id => setText(id, '—'));
    }
}

// ── Top-3 Podium ─────────────────────────────────────────────────────────────
async function loadTop3() {
    const wrapper = document.getElementById('podiumWrapper');
    if (!wrapper) return;

    wrapper.innerHTML = '<div class="lb-empty" style="color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i><p>Loading champions…</p></div>';

    try {
        const { top3 } = await apiFetch('get_top3');

        if (!top3 || top3.length === 0) {
            wrapper.innerHTML = '<div class="lb-empty" style="color:#94a3b8;"><i class="fas fa-users-slash"></i><p>No tourist users yet.</p></div>';
            return;
        }

        // Build ordered array: position 0 = 2nd place (left), 1 = 1st (centre), 2 = 3rd (right)
        // Cards use CSS order property to achieve visual podium layout
        wrapper.innerHTML = top3.map(u => buildPodiumCard(u)).join('');

    } catch (err) {
        console.error('[LB] loadTop3:', err);
        wrapper.innerHTML = `<div class="lb-empty" style="color:#ef4444;"><i class="fas fa-exclamation-circle"></i><p>${escHtml(err.message)}</p></div>`;
    }
}

function buildPodiumCard(u) {
    const medals  = { 1: '🥇', 2: '🥈', 3: '🥉' };
    const crowns  = { 1: '👑', 2: '🥈', 3: '🥉' };
    const medal   = medals[u.rank] || '';
    const crown   = crowns[u.rank] || '';
    const initials = getInitials(u.full_name);
    const avatarColor = getAvatarColor(u.user_id);

    const lastAct = u.last_activity_date
        ? formatDateShort(u.last_activity_date)
        : 'No activity';

    return `
    <div class="lb-podium-card rank-${u.rank}" role="region" aria-label="Rank ${u.rank}: ${escHtml(u.full_name)}">
        <div class="lb-rank-crown">${crown}</div>

        <div class="lb-rank-badge">${u.rank}</div>

        <div class="lb-avatar" style="background:${avatarColor};" aria-hidden="true">
            ${initials}
        </div>

        <div class="lb-player-name" title="${escHtml(u.full_name)}">${escHtml(u.full_name)}</div>
        <div class="lb-player-id">ID #${u.user_id}</div>

        <div class="lb-score-value">${formatNum(u.total_points)}</div>
        <div class="lb-score-label">Total Points</div>

        <div class="lb-stat-row">
            <div class="lb-stat-item">
                <span class="lb-stat-val">${formatNum(u.completed_activities)}</span>
                <span class="lb-stat-key">Activities</span>
            </div>
            <div class="lb-stat-item">
                <span class="lb-stat-val">${escHtml(lastAct)}</span>
                <span class="lb-stat-key">Last Active</span>
            </div>
        </div>
    </div>`;
}

// ── Leaderboard Table ─────────────────────────────────────────────────────────
async function loadTable() {
    const tbody = document.getElementById('leaderboardBody');
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="lb-empty">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading leaderboard…</p>
            </td>
        </tr>`;

    const search  = (document.getElementById('searchInput')?.value  || '').trim();
    const sortBy  = document.getElementById('sortSelect')?.value     || 'points_desc';
    const offset  = (_currentPage - 1) * PAGE_SIZE;

    try {
        const data = await apiFetch('get_leaderboard', {
            search,
            sort:   sortBy,
            limit:  PAGE_SIZE,
            offset,
        });

        _totalRows = data.total || 0;

        // Update max for progress bars if first load
        if (data.users && data.users.length > 0 && _maxPoints === 0) {
            _maxPoints = data.users[0].total_points || 1;
        }

        renderTable(data.users || []);
        renderPagination(_totalRows, offset);
        updateTableCount(data.users?.length || 0, _totalRows, search);

    } catch (err) {
        console.error('[LB] loadTable:', err);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="lb-empty">
                    <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
                    <p>${escHtml(err.message)}</p>
                </td>
            </tr>`;
    }
}

function renderTable(users) {
    const tbody = document.getElementById('leaderboardBody');
    if (!tbody) return;

    if (!users.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="lb-empty">
                    <i class="fas fa-user-slash"></i>
                    <p>No users found matching your search.</p>
                </td>
            </tr>`;
        return;
    }

    const max = _maxPoints || 1;

    tbody.innerHTML = users.map(u => {
        const isTop3   = u.rank <= 3;
        const medal    = isTop3 ? medalEmoji(u.rank) : '';
        const pct      = Math.round((u.total_points / max) * 100);
        const initials = getInitials(u.full_name);
        const color    = getAvatarColor(u.user_id);
        const lastAct  = u.last_activity_date ? formatDateShort(u.last_activity_date) : '—';

        const rankCell = isTop3
            ? `<td class="lb-rank-cell top3" style="text-align:center;">
                   <span class="lb-rank-medal">${medal}</span>
               </td>`
            : `<td class="lb-rank-cell" style="text-align:center;">#${u.rank}</td>`;

        return `
        <tr
            onclick="selectRow(this)"
            style="cursor:pointer;"
            title="Click to highlight"
            data-user-id="${u.user_id}">
            ${rankCell}
            <td style="text-align:center; font-weight:600; color:var(--text-secondary);">${u.user_id}</td>
            <td>
                <div class="lb-user-cell">
                    <div class="lb-user-avatar" style="background:${color};" aria-hidden="true">${initials}</div>
                    <div>
                        <div class="lb-user-cell-name">${escHtml(u.full_name)}</div>
                        <div class="lb-user-cell-id">User ID: ${u.user_id}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="lb-points-wrap">
                    <div class="lb-points-track" title="${formatNum(u.total_points)} pts">
                        <div class="lb-points-fill" style="width:${pct}%;"></div>
                    </div>
                    <span class="lb-points-val">${formatNum(u.total_points)}</span>
                </div>
            </td>
            <td style="text-align:center; font-weight:600;">${formatNum(u.completed_activities)}</td>
            <td style="color:var(--text-secondary); font-size:13px;">${escHtml(lastAct)}</td>
        </tr>`;
    }).join('');
}

// ── Row highlight ─────────────────────────────────────────────────────────────
function selectRow(row) {
    // Deselect current
    const prev = document.querySelector('.lb-row-selected');
    if (prev && prev !== row) prev.classList.remove('lb-row-selected');
    row.classList.toggle('lb-row-selected');
}

// ── Pagination ────────────────────────────────────────────────────────────────
function renderPagination(total, offset) {
    const info  = document.getElementById('paginationInfo');
    const btns  = document.getElementById('paginationBtns');
    if (!info || !btns) return;

    const from  = total === 0 ? 0 : offset + 1;
    const to    = Math.min(offset + PAGE_SIZE, total);
    info.textContent = total === 0
        ? 'No results'
        : `Showing ${from}–${to} of ${total}`;

    const totalPages = Math.ceil(total / PAGE_SIZE);

    let html = '';
    html += `<button class="lb-page-btn" onclick="goPage(${_currentPage - 1})" ${_currentPage === 1 ? 'disabled' : ''}>‹ Prev</button>`;

    // Show at most 7 page buttons
    const start = Math.max(1, _currentPage - 3);
    const end   = Math.min(totalPages, start + 6);

    for (let p = start; p <= end; p++) {
        html += `<button class="lb-page-btn ${p === _currentPage ? 'active' : ''}" onclick="goPage(${p})">${p}</button>`;
    }

    html += `<button class="lb-page-btn" onclick="goPage(${_currentPage + 1})" ${_currentPage >= totalPages ? 'disabled' : ''}>Next ›</button>`;
    btns.innerHTML = html;
}

function goPage(p) {
    const totalPages = Math.ceil(_totalRows / PAGE_SIZE);
    if (p < 1 || p > totalPages) return;
    _currentPage = p;
    loadTable();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Search / Filter ───────────────────────────────────────────────────────────
function debouncedSearch() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => {
        _currentPage = 1;
        loadTable();
    }, 320);
}

function applyFilters() {
    _currentPage = 1;
    loadTable();
}

function clearSearch() {
    const input = document.getElementById('searchInput');
    const sort  = document.getElementById('sortSelect');
    if (input) input.value = '';
    if (sort)  sort.value  = 'points_desc';
    _currentPage = 1;
    loadTable();
}

// ── Table count label ─────────────────────────────────────────────────────────
function updateTableCount(shown, total, search) {
    const el = document.getElementById('tableCountLabel');
    if (!el) return;

    if (search) {
        el.textContent = `Found ${total} result(s) for "${search}"`;
    } else {
        el.textContent = `Showing top 100 of ${total} tourist(s)`;
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function medalEmoji(rank) {
    return rank === 1 ? '🥇' : rank === 2 ? '🥈' : '🥉';
}

function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0][0].toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

// Deterministic color based on user ID
function getAvatarColor(id) {
    const palette = [
        '#1a5276','#1e8449','#b7950b','#7d3c98','#1a6688',
        '#a04000','#1f618d','#196f3d','#6e2f8c','#2e86c1',
    ];
    return palette[(id || 0) % palette.length];
}

function formatNum(n) {
    if (n === null || n === undefined) return '0';
    return Number(n).toLocaleString('en-PH');
}

function formatDateShort(dt) {
    if (!dt) return '—';
    try {
        return new Date(dt).toLocaleDateString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric'
        });
    } catch (_) {
        return dt;
    }
}

function escHtml(str) {
    if (str === null || str === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}
