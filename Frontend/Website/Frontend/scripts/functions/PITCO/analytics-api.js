/**
 * PICTO Analytics Dashboard API
 * Role: picto
 * Uses centralized API_CONFIG for real-time database fetching
 */

'use strict';

const PA_API = window.API_CONFIG?.PITCO || 'http://localhost:8000/api/pitco';

// Map legacy action strings to Laravel REST paths
function paActionToUrl(action, params = {}) {
    const map = {
        'get_summary':           `${PA_API}/summary`,
        'get_top_municipalities':`${PA_API}/top-municipalities`,
        'get_top_spots':         `${PA_API}/top-spots`,
        'get_chart_data':        `${PA_API}/chart-data`,
        'get_monthly_trend':     `${PA_API}/monthly-trend`,
        'get_filter_options':    `${PA_API}/filter-options`,
        'get_full':              `${PA_API}/full`,
    };
    const base = map[action] || `${PA_API}/${action.replace(/_/g, '-')}`;
    const qs   = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
    return base + qs;
}

// ── State 
let _muniSort = 'total_visits';
let _spotSort = 'visits';
let _charts   = {};   // chart instances keyed by canvas id

// ── Boot ─
document.addEventListener('DOMContentLoaded', () => {
    loadFilterOptions();
    refreshAll();
});

// ── Core fetch using API_CONFIG
async function apiFetch(action, params = {}) {
    const url = paActionToUrl(action, params);

    try {
        const data = await window.API_CONFIG.fetch(url);
        return data;
    } catch (e) {
        throw new Error('Network error: ' + e.message);
    }
}

// ── Refresh all sections 
async function refreshAll() {
    const icon = document.getElementById('refreshIcon');
    if (icon) icon.classList.add('fa-spin');

    await Promise.all([
        loadSummary(),
        loadTopMunicipalities(),
        loadTopSpots(),
        loadChartData(),
        loadTrendChart(),
    ]);

    if (icon) icon.classList.remove('fa-spin');
}

// Refresh just the rankings (filter changes)
async function refreshRankings() {
    await Promise.all([
        loadTopMunicipalities(),
        loadTopSpots(),
    ]);
}

// ── Load filter dropdown options 
async function loadFilterOptions() {
    try {
        const data = await apiFetch('get_filter_options');
        const sel  = document.getElementById('filterMuni');
        if (sel && data.municipalities) {
            data.municipalities.forEach(m => {
                const opt = document.createElement('option');
                opt.value       = m.id;
                opt.textContent = m.name;
                sel.appendChild(opt);
            });
        }
    } catch (_) { /* non-critical */ }
}

function clearFilters() {
    ['filterMuni','filterCategory','filterStatus'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    refreshRankings();
}

// ── KPI Summary 
async function loadSummary() {
    try {
        const data = await apiFetch('get_summary');
        const s    = data.summary;

        setText('kpiMunis',    fmtNum(s.total_municipalities));
        setText('kpiSpots',    fmtNum(s.total_spots));
        setText('kpiVisits',   fmtNum(s.total_visits));
        setText('kpiUsers',    fmtNum(s.total_users));
        setText('kpiApproved', fmtNum(s.approved_spots));
        setText('kpiAnalytics',fmtNum(s.total_analytics_visits));
        setText('kpiTopMuni',  s.most_visited_muni);
        setText('kpiTopSpot',  s.most_visited_spot);

    } catch (err) {
        console.error('[PA] loadSummary:', err);
    }
}

// ── Top Municipalities 
async function loadTopMunicipalities() {
    const podium = document.getElementById('muniPodium');
    const tbody  = document.getElementById('muniTableBody');

    if (podium) podium.innerHTML = '<div class="pa-loading"><i class="fas fa-spinner fa-spin"></i></div>';
    if (tbody)  tbody.innerHTML  = '<tr><td colspan="6" class="pa-loading"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    try {
        const data = await apiFetch('get_top_municipalities', {
            sort:        _muniSort,
            category:    document.getElementById('filterCategory')?.value || '',
            spot_status: document.getElementById('filterStatus')?.value   || '',
            limit:       10,
        });

        const munis = data.municipalities || [];
        const top3  = munis.slice(0, 3);
        const rest  = munis.slice(3);

        // Find max value for progress bars
        const keyMap = {
            total_visits: 'total_visits', total_spots: 'total_spots',
            approved_spots: 'approved_spots', avg_rating: 'avg_rating'
        };
        const valKey = keyMap[_muniSort] || 'total_visits';
        const maxVal = munis.reduce((m, r) => Math.max(m, parseFloat(r[valKey])||0), 1);

        // Podium
        if (podium) podium.innerHTML = top3.length
            ? top3.map(m => buildMuniPodiumCard(m, valKey)).join('')
            : '<div class="pa-empty"><i class="fas fa-city"></i><p>No data available.</p></div>';

        // Table rows 4-10
        if (tbody) tbody.innerHTML = rest.length
            ? rest.map(m => buildMuniRow(m, valKey, maxVal)).join('')
            : '<tr><td colspan="6" class="pa-empty"><p>No more entries.</p></td></tr>';

    } catch (err) {
        console.error('[PA] loadTopMunicipalities:', err);
        if (podium) podium.innerHTML = `<div class="pa-empty"><i class="fas fa-exclamation-circle" style="color:#ef4444;"></i><p>${escHtml(err.message)}</p></div>`;
        if (tbody)  tbody.innerHTML  = `<tr><td colspan="6" class="pa-empty"><p>${escHtml(err.message)}</p></td></tr>`;
    }
}

function buildMuniPodiumCard(m, valKey) {
    const medals  = {1:'🥇', 2:'🥈', 3:'🥉'};
    const initials = getInitials(m.name);
    const mainVal  = valKey === 'avg_rating'
        ? parseFloat(m[valKey]).toFixed(1) + ' ★'
        : fmtNum(m[valKey]);
    const unitLabels = { total_visits:'Visits', total_spots:'Spots', approved_spots:'Approved', avg_rating:'Rating' };
    const unit = unitLabels[valKey] || '';

    return `
    <div class="pa-podium-card rank-${m.rank}">
        <div class="pa-medal">${medals[m.rank]||''}</div>
        <div class="pa-podium-rank">${m.rank}</div>
        <div class="pa-podium-avatar">${initials}</div>
        <div class="pa-podium-name" title="${escHtml(m.name)}">${escHtml(m.name)}</div>
        <div class="pa-podium-value">${mainVal}</div>
        <div class="pa-podium-unit">${unit}</div>
        <div class="pa-podium-stats">
            <div class="pa-podium-stat-item">
                <span class="pa-podium-stat-val">${fmtNum(m.total_spots)}</span>
                <span class="pa-podium-stat-key">Spots</span>
            </div>
            <div class="pa-podium-stat-item">
                <span class="pa-podium-stat-val">${fmtNum(m.total_visits)}</span>
                <span class="pa-podium-stat-key">Visits</span>
            </div>
            <div class="pa-podium-stat-item">
                <span class="pa-podium-stat-val">${parseFloat(m.avg_rating).toFixed(1)}</span>
                <span class="pa-podium-stat-key">Rating</span>
            </div>
        </div>
    </div>`;
}

function buildMuniRow(m, valKey, maxVal) {
    const val = parseFloat(m[valKey]) || 0;
    const pct = maxVal > 0 ? Math.round((val / maxVal) * 100) : 0;
    const initials = getInitials(m.name);
    const color    = getAvatarColor(m.id);

    return `
    <tr>
        <td class="pa-rank-num">#${m.rank}</td>
        <td>
            <span class="pa-rank-avatar" style="background:${color};">${initials}</span>
            <strong>${escHtml(m.name)}</strong>
        </td>
        <td>${fmtNum(m.total_spots)}</td>
        <td>${fmtNum(m.approved_spots)}</td>
        <td>
            <div class="pa-progress-wrap">
                <div class="pa-progress-track">
                    <div class="pa-progress-fill" style="width:${pct}%;"></div>
                </div>
                <span class="pa-progress-val">${fmtNum(m.total_visits)}</span>
            </div>
        </td>
        <td>${parseFloat(m.avg_rating).toFixed(1)} ★</td>
    </tr>`;
}

function setMuniSort(btn, sort) {
    _muniSort = sort;
    document.querySelectorAll('#muniSortTabs .pa-sort-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    loadTopMunicipalities();
}

// ── Top Tourist Spots 
async function loadTopSpots() {
    const podium = document.getElementById('spotPodium');
    const tbody  = document.getElementById('spotTableBody');

    if (podium) podium.innerHTML = '<div class="pa-loading"><i class="fas fa-spinner fa-spin"></i></div>';
    if (tbody)  tbody.innerHTML  = '<tr><td colspan="7" class="pa-loading"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    try {
        const data = await apiFetch('get_top_spots', {
            sort:            _spotSort,
            municipality_id: document.getElementById('filterMuni')?.value     || '',
            category:        document.getElementById('filterCategory')?.value || '',
            spot_status:     document.getElementById('filterStatus')?.value   || '',
            limit: 10,
        });

        const spots = data.spots || [];
        const top3  = spots.slice(0, 3);
        const rest  = spots.slice(3);
        const maxVisits = spots.reduce((m, s) => Math.max(m, s.visits), 1);

        if (podium) podium.innerHTML = top3.length
            ? top3.map(s => buildSpotPodiumCard(s)).join('')
            : '<div class="pa-empty"><i class="fas fa-map-pin"></i><p>No spots found.</p></div>';

        if (tbody) tbody.innerHTML = rest.length
            ? rest.map(s => buildSpotRow(s, maxVisits)).join('')
            : '<tr><td colspan="7" class="pa-empty"><p>No more entries.</p></td></tr>';

    } catch (err) {
        console.error('[PA] loadTopSpots:', err);
        if (podium) podium.innerHTML = `<div class="pa-empty"><i class="fas fa-exclamation-circle" style="color:#ef4444;"></i><p>${escHtml(err.message)}</p></div>`;
        if (tbody)  tbody.innerHTML  = `<tr><td colspan="7" class="pa-empty"><p>${escHtml(err.message)}</p></td></tr>`;
    }
}

function buildSpotPodiumCard(s) {
    const medals = {1:'🥇', 2:'🥈', 3:'🥉'};
    const photo  = s.photo_url
        ? `<img src="${escHtml(s.photo_url)}" alt="${escHtml(s.name)}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin:0 auto 10px;display:block;border:3px solid rgba(255,255,255,0.8);" onerror="this.style.display='none'">`
        : `<div class="pa-podium-avatar">${getCatIcon(s.category)}</div>`;

    const mainVal = _spotSort === 'rating'
        ? `${parseFloat(s.rating).toFixed(1)} ★`
        : fmtNum(s.visits);
    const unit = _spotSort === 'rating' ? 'Rating' : 'Visits';

    return `
    <div class="pa-podium-card rank-${s.rank}">
        <div class="pa-medal">${medals[s.rank]||''}</div>
        <div class="pa-podium-rank">${s.rank}</div>
        ${photo}
        <div class="pa-podium-name" title="${escHtml(s.name)}">${escHtml(s.name)}</div>
        <div class="pa-podium-meta">${escHtml(s.municipality_name)}</div>
        <div class="pa-podium-value">${mainVal}</div>
        <div class="pa-podium-unit">${unit}</div>
        <div class="pa-podium-stats">
            <div class="pa-podium-stat-item">
                <span class="pa-podium-stat-val"><span class="pa-cat-badge pa-cat-${s.category}">${escHtml(s.category)}</span></span>
                <span class="pa-podium-stat-key">Category</span>
            </div>
            <div class="pa-podium-stat-item">
                <span class="pa-podium-stat-val">${parseFloat(s.rating).toFixed(1)} ★</span>
                <span class="pa-podium-stat-key">Rating</span>
            </div>
        </div>
    </div>`;
}

function buildSpotRow(s, maxVisits) {
    const pct  = maxVisits > 0 ? Math.round((s.visits / maxVisits) * 100) : 0;
    const photo = s.photo_url
        ? `<img src="${escHtml(s.photo_url)}" alt="" style="width:32px;height:32px;border-radius:6px;object-fit:cover;vertical-align:middle;margin-right:8px;" onerror="this.style.display='none'">`
        : `<span class="pa-spot-photo">${getCatIcon(s.category)}</span>`;

    const clsSt = s.classification_status || '';
    const statusBadge = clsSt
        ? `<span class="pa-status-badge pa-status-${clsSt}">${clsSt}</span>`
        : `<span class="pa-status-badge pa-status-${s.status}">${s.status}</span>`;

    return `
    <tr>
        <td class="pa-rank-num">#${s.rank}</td>
        <td>
            ${photo}
            <strong>${escHtml(s.name)}</strong>
        </td>
        <td style="font-size:13px; color:var(--text-secondary);">${escHtml(s.municipality_name)}</td>
        <td><span class="pa-cat-badge pa-cat-${s.category}">${escHtml(s.category)}</span></td>
        <td>${statusBadge}</td>
        <td>
            <div class="pa-progress-wrap">
                <div class="pa-progress-track">
                    <div class="pa-progress-fill" style="width:${pct}%;"></div>
                </div>
                <span class="pa-progress-val">${fmtNum(s.visits)}</span>
            </div>
        </td>
        <td>${parseFloat(s.rating).toFixed(1)} ★</td>
    </tr>`;
}

function setSpotSort(btn, sort) {
    _spotSort = sort;
    document.querySelectorAll('#spotSortTabs .pa-sort-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    loadTopSpots();
}

// ── Charts =
const CHART_COLORS = [
    '#185FA5','#22c55e','#f59e0b','#8b5cf6','#ef4444',
    '#06b6d4','#ec4899','#84cc16','#f97316','#64748b',
];
const DONUT_COLORS = [
    '#185FA5','#22c55e','#f59e0b','#8b5cf6','#ef4444',
    '#06b6d4','#ec4899','#84cc16','#f97316','#94a3b8',
];

function destroyChart(id) {
    if (_charts[id]) { _charts[id].destroy(); delete _charts[id]; }
}

function buildBarChart(id, labels, values, label, color = '#185FA5') {
    destroyChart(id);
    const ctx = document.getElementById(id);
    if (!ctx) return;
    _charts[id] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label,
                data: values,
                backgroundColor: CHART_COLORS.map(c => c + 'CC'),
                borderColor:     CHART_COLORS,
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' },
                     ticks: { font: { size: 11 } } },
                x: { grid: { display: false },
                     ticks: { font: { size: 10 }, maxRotation: 35 } }
            }
        }
    });
}

function buildDonutChart(id, labels, values, title) {
    destroyChart(id);
    const ctx = document.getElementById(id);
    if (!ctx) return;
    _charts[id] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{ data: values, backgroundColor: DONUT_COLORS, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 14, padding: 10 } },
                title:  { display: false }
            }
        }
    });
}

async function loadChartData() {
    try {
        const year = document.getElementById('filterYear')?.value || new Date().getFullYear();
        const data = await apiFetch('get_chart_data', { year });

        // Bar: spots by municipality
        buildBarChart(
            'spotsByMuniChart',
            (data.spots_by_muni || []).map(r => r.name),
            (data.spots_by_muni || []).map(r => r.spot_count),
            'Tourist Spots'
        );

        // Bar: visits by municipality
        buildBarChart(
            'visitsByMuniChart',
            (data.visits_by_muni || []).map(r => r.name),
            (data.visits_by_muni || []).map(r => r.total_visits),
            'Total Visits'
        );

        // Donut: category distribution
        buildDonutChart(
            'catDistChart',
            (data.cat_dist || []).map(r => r.category),
            (data.cat_dist || []).map(r => r.cnt),
            'Categories'
        );

        // Donut: classification status
        buildDonutChart(
            'classDistChart',
            (data.class_dist || []).map(r => r.cls || 'Unknown'),
            (data.class_dist || []).map(r => r.cnt),
            'Classification'
        );

    } catch (err) {
        console.error('[PA] loadChartData:', err);
    }
}

async function loadTrendChart() {
    const year = parseInt(document.getElementById('trendYearSelect')?.value || new Date().getFullYear(), 10);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    destroyChart('trendChart');
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    try {
        const data = await apiFetch('get_monthly_trend', { year });

        // Build full 12-month arrays
        const curVisits  = Array(12).fill(0);
        const prevVisits = Array(12).fill(0);
        (data.current  || []).forEach(r => { curVisits[r.month  - 1] = parseInt(r.visits, 10); });
        (data.previous || []).forEach(r => { prevVisits[r.month - 1] = parseInt(r.visits, 10); });

        _charts['trendChart'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: `${year} Visits`,
                        data: curVisits,
                        borderColor: '#185FA5',
                        backgroundColor: 'rgba(24,95,165,0.1)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: `${year - 1} Visits`,
                        data: prevVisits,
                        borderColor: '#94a3b8',
                        backgroundColor: 'rgba(148,163,184,0.05)',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 12 }, boxWidth: 20 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${fmtNum(ctx.parsed.y)} visits`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' },
                         ticks: { font: { size: 11 }, callback: v => fmtNum(v) } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

    } catch (err) {
        console.error('[PA] loadTrendChart:', err);
    }
}

// ── Utilities 
function getInitials(name) {
    if (!name) return '?';
    const p = name.trim().split(/\s+/);
    return p.length === 1 ? p[0][0].toUpperCase() : (p[0][0] + p[p.length-1][0]).toUpperCase();
}

function getAvatarColor(id) {
    const palette = ['#185FA5','#1e8449','#b7950b','#7d3c98','#1a6688','#a04000','#1f618d','#196f3d','#6e2f8c','#2e86c1'];
    return palette[(id || 0) % palette.length];
}

function getCatIcon(cat) {
    const icons = {
        Beach:'🏖', Mountain:'⛰', Historical:'🏛', Waterfalls:'💧',
        Adventure:'🏕', Farm:'🌾', Religious:'⛪', Other:'📍'
    };
    return icons[cat] || '📍';
}

function fmtNum(n) {
    if (n === null || n === undefined) return '0';
    return Number(n).toLocaleString('en-PH');
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val ?? '—';
}

function escHtml(str) {
    if (str == null) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}
