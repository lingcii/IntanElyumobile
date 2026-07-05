/**
 * MUNICIPAL Analytics Dashboard API
 * Scoped to the user's own municipality only.
 * Fetches real-time data from Laravel API.
 */
'use strict';

const MA_API = window.API_CONFIG?.MUNICIPAL || 'http://localhost:8000/api/municipal';

function maActionToUrl(action, params = {}) {
    const map = {
        'summary':          `${MA_API}/analytics/summary`,
        'top_municipalities':`${MA_API}/analytics/top-municipalities`,
        'top_spots':         `${MA_API}/analytics/top-spots`,
        'chart_data':        `${MA_API}/analytics/chart-data`,
        'monthly_trend':     `${MA_API}/analytics/monthly-trend`,
        'filter_options':    `${MA_API}/analytics/filter-options`,
        'full':              `${MA_API}/analytics/full`,
        'export':            `${MA_API}/analytics/export`,
    };
    const base = map[action] || `${MA_API}/analytics/${action.replace(/_/g, '-')}`;
    const qs   = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
    return base + qs;
}

let _spotSort = 'visits';
let _charts   = {};
let _autoRefreshTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    refreshAll();
    startAutoRefresh();
});

async function apiFetch(action, params = {}) {
    const url = maActionToUrl(action, params);
    try {
        return await window.API_CONFIG.fetch(url);
    } catch (e) {
        throw new Error('Network error: ' + e.message);
    }
}

async function refreshAll() {
    const icon = document.getElementById('refreshIcon');
    if (icon) icon.classList.add('fa-spin');

    await Promise.all([
        loadSummary(),
        loadCharts(),
        loadTrendChart(),
        loadTopSpotsTable(),
    ]);

    if (icon) icon.classList.remove('fa-spin');
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) modal.style.display = 'none';
}

function exportData(format) {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.setAttribute('data-format', format);
    } else {
        triggerExport(format, 'full');
    }
}

function triggerExport(format, type) {
    closeExportModal();
    const year = document.getElementById('filterYear')?.value || new Date().getFullYear();
    const url = maActionToUrl('export', { format, type, year });
    window.open(url, '_blank');
}

function startAutoRefresh() {
    if (_autoRefreshTimer) clearInterval(_autoRefreshTimer);
    _autoRefreshTimer = setInterval(refreshAll, 30000);
}

function stopAutoRefresh() {
    if (_autoRefreshTimer) { clearInterval(_autoRefreshTimer); _autoRefreshTimer = null; }
}

function toggleAutoRefresh() {
    const toggle = document.getElementById('autoRefreshToggle');
    if (toggle && toggle.checked) startAutoRefresh();
    else stopAutoRefresh();
}

// ── KPI Summary
async function loadSummary() {
    try {
        const data = await apiFetch('summary');
        const s = data.summary;

        setText('kpiTotalSpots', fmtNum(s.total_spots));
        setText('kpiApproved',   fmtNum(s.approved_spots));
        setText('kpiVisits',     fmtNum(s.total_visits));
        setText('kpiAnalytics',  fmtNum(s.total_analytics_visits));
        setText('kpiTopCat',     s.most_visited_spot || '—');

        const muniName = s.most_visited_muni || 'Your Municipality';
        setText('muniName', muniName);
        const munLabel = document.querySelector('#muniBadge span');
        if (munLabel) munLabel.textContent = muniName;

        const topSpots = await getTopSpotsData();
        if (topSpots.length > 0) {
            const avgRating = (topSpots.reduce((sum, s) => sum + parseFloat(s.rating || 0), 0) / topSpots.length).toFixed(1);
            setText('kpiAvgRating', avgRating + ' ★');
        } else {
            setText('kpiAvgRating', '—');
        }
    } catch (err) {
        console.error('[MA] loadSummary:', err);
    }
}

async function getTopSpotsData() {
    try {
        const data = await apiFetch('top_spots', { sort: _spotSort, limit: 10 });
        return data.spots || [];
    } catch (_) {
        return [];
    }
}

// ── Top Spots Table
async function loadTopSpotsTable() {
    const tbody = document.getElementById('spotTableBody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="pa-loading"><i class="fas fa-spinner fa-spin"></i></td></tr>';

    try {
        const spots = await getTopSpotsData();
        const maxVisits = spots.reduce((m, s) => Math.max(m, s.visits), 1);

        if (tbody) tbody.innerHTML = spots.length
            ? spots.map(s => buildSpotRow(s, maxVisits)).join('')
            : '<tr><td colspan="7" class="pa-empty"><p>No tourist spots found in your municipality.</p></td></tr>';
    } catch (err) {
        console.error('[MA] loadTopSpotsTable:', err);
        if (tbody) tbody.innerHTML = `<tr><td colspan="7" class="pa-empty"><p>${escHtml(err.message)}</p></td></tr>`;
    }
}

function buildSpotRow(s, maxVisits) {
    const pct  = maxVisits > 0 ? Math.round((s.visits / maxVisits) * 100) : 0;
    const clsSt = s.classification_status || '';
    const badge = clsSt
        ? `<span class="pa-status-badge pa-status-${clsSt}">${clsSt}</span>`
        : `<span class="pa-status-badge pa-status-${s.status}">${s.status}</span>`;
    const fee = s.entrance_fee ? '₱' + Number(s.entrance_fee).toFixed(2) : 'Free';

    return `
    <tr>
        <td class="pa-rank-num">#${s.rank}</td>
        <td><strong>${escHtml(s.name)}</strong></td>
        <td><span class="pa-cat-badge pa-cat-${s.category}">${escHtml(s.category)}</span></td>
        <td>${badge}</td>
        <td><div class="pa-progress-wrap"><div class="pa-progress-track"><div class="pa-progress-fill" style="width:${pct}%;"></div></div><span class="pa-progress-val">${fmtNum(s.visits)}</span></div></td>
        <td>${parseFloat(s.rating).toFixed(1)} ★</td>
        <td style="font-size:13px; color:var(--text-secondary);">${fee}</td>
    </tr>`;
}

function setSpotSort(btn, sort) {
    _spotSort = sort;
    document.querySelectorAll('#spotSortTabs .pa-sort-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    loadTopSpotsTable();
}

// ── Charts
const CHART_COLORS = ['#185FA5','#22c55e','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899','#84cc16','#f97316','#64748b'];
const DONUT_COLORS = ['#185FA5','#22c55e','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899','#84cc16','#f97316','#94a3b8'];

function destroyChart(id) { if (_charts[id]) { _charts[id].destroy(); delete _charts[id]; } }

async function loadCharts() {
    try {
        const year = document.getElementById('filterYear')?.value || new Date().getFullYear();
        const data = await apiFetch('chart_data', { year });

        buildDonutChart('catDistChart',   (data.cat_dist   || []).map(r => r.category), (data.cat_dist   || []).map(r => r.cnt));
        buildDonutChart('classDistChart', (data.class_dist || []).map(r => r.cls || 'Unknown'), (data.class_dist || []).map(r => r.cnt));

        const t = data.transport || {};
        const total = Math.max(parseInt(t.total, 10) || 1, 1);
        const tCar = parseInt(t.car, 10) || 0, tBus = parseInt(t.bus, 10) || 0, tVan = parseInt(t.van, 10) || 0, tOther = parseInt(t.other, 10) || 0;
        buildDonutChart('transportChart', ['Private Cars', 'Tour Buses', 'Vans', 'Others'], [tCar, tBus, tVan, tOther]);

        const pct = v => total > 0 ? Math.round((v / total) * 100) + '%' : '0%';
        setText('tCar', pct(tCar)); setText('tBus', pct(tBus)); setText('tVan', pct(tVan)); setText('tOther', pct(tOther));

        const spots = await getTopSpotsData();
        if (spots.length > 0) {
            buildBarChart('topSpotsChart',
                spots.map(s => s.name).slice(0, 8),
                spots.map(s => s.visits).slice(0, 8),
                'Visits'
            );
        }
    } catch (err) { console.error('[MA] loadCharts:', err); }
}

function buildBarChart(id, labels, values, label) {
    destroyChart(id);
    const ctx = document.getElementById(id);
    if (!ctx || !labels.length) return;
    _charts[id] = new Chart(ctx, {
        type: 'bar', data: { labels, datasets: [{ label, data: values, backgroundColor: CHART_COLORS.map(c => c + 'CC'), borderColor: CHART_COLORS, borderWidth: 1, borderRadius: 4 }] },
        options: {
            indexAxis: labels.length > 5 ? 'y' : 'x',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }, x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 35 } } }
        }
    });
}

function buildDonutChart(id, labels, values) {
    destroyChart(id);
    const ctx = document.getElementById(id);
    if (!ctx) return;
    _charts[id] = new Chart(ctx, {
        type: 'doughnut', data: { labels, datasets: [{ data: values, backgroundColor: DONUT_COLORS, borderWidth: 2, borderColor: '#fff' }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 14, padding: 10 } } } }
    });
}

async function loadTrendChart() {
    const year   = parseInt(document.getElementById('trendYearSelect')?.value || new Date().getFullYear(), 10);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    destroyChart('trendChart');
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    try {
        const data = await apiFetch('monthly_trend', { year });
        const curVisits  = Array(12).fill(0);
        const prevVisits = Array(12).fill(0);
        (data.current  || []).forEach(r => { curVisits[r.month  - 1] = parseInt(r.visits, 10); });
        (data.previous || []).forEach(r => { prevVisits[r.month - 1] = parseInt(r.visits, 10); });

        _charts['trendChart'] = new Chart(ctx, {
            type: 'line', data: {
                labels: months,
                datasets: [
                    { label: `${year} Visits`,   data: curVisits,  borderColor: '#185FA5', backgroundColor: 'rgba(24,95,165,0.12)', borderWidth: 2.5, tension: 0.35, fill: true,  pointRadius: 4, pointHoverRadius: 6 },
                    { label: `${year-1} Visits`, data: prevVisits, borderColor: '#94a3b8', backgroundColor: 'rgba(148,163,184,0.05)', borderWidth: 1.5, borderDash: [5,5], tension: 0.35, fill: false, pointRadius: 3 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 12 }, boxWidth: 20 } },
                    tooltip: { callbacks: { label: c => ` ${c.dataset.label}: ${fmtNum(c.parsed.y)} visits` } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, callback: v => fmtNum(v) } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    } catch (err) { console.error('[MA] loadTrendChart:', err); }
}

// ── Utilities
function fmtNum(n) { if (n === null || n === undefined) return '0'; return Number(n).toLocaleString('en-PH'); }
function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val ?? '—'; }
function escHtml(str) { if (str == null) return ''; const d = document.createElement('div'); d.textContent = String(str); return d.innerHTML; }
