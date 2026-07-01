/**
 * LUPTO Fare Data API
 * Role: lupto
 * Permissions: View-only — search, filter, view matrix, export CSV
 */

const API_BASE = window.API_CONFIG?.LUPTO ?? 'http://localhost:8000/api/lupto' + '/fare-data';
let _allFareGuides = [];
let _currentMatrixData = [];
let refreshInterval = null;

function fareActionToUrl(action, params = {}) {
    const map = {
        'get_fare_guides':      `${window.API_CONFIG.LUPTO}/fare-data/guides`,
        'get_fare_matrices':    `${window.API_CONFIG.LUPTO}/fare-data/matrices`,
    };
    const base = map[action] || `${window.API_CONFIG.LUPTO}/fare-data/${action.replace(/_/g, '-')}`;
    const allParams = { ...params, _t: Date.now() };
    const qs   = '?' + new URLSearchParams(allParams).toString();
    return base + qs;
}

// ── Boot ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    loadFareGuides();

    // Real-time auto-refresh every 30 seconds
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(loadFareGuides, 30_000);
});

// ── Core fetch helper using window.API_CONFIG
async function apiFetch(action, extraParams = {}) {
    const url = fareActionToUrl(action, extraParams);
    
    try {
        const data = await window.API_CONFIG.fetch(url);
        return data;
    } catch (e) {
        console.error('[LUPTO Fare] API fetch failed:', e);
        throw e;
    }
}

// ── Fare Guides ───────────────────────────────────────────────────────────────

async function loadFareGuides() {
    // Reset to loading state
    const tbody = document.getElementById('fareGuidesBody');
    if (tbody) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);">
            <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i> Loading...
        </td></tr>`;
    }

    try {
        const data = await apiFetch('get_fare_guides');
        _allFareGuides = data.fare_guides || [];
        renderFareGuides(_allFareGuides);

        const countEl = document.getElementById('fareGuidesCount');
        if (countEl) countEl.textContent = `${_allFareGuides.length} guide(s) found`;

    } catch (err) {
        console.error('[LUPTO Fare] loadFareGuides failed:', err);
        showTableError('fareGuidesBody', 6, 'Failed to load fare data: ' + err.message);
    }
}

function showTableError(tbodyId, colspan, message) {
    const tbody = document.getElementById(tbodyId);
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" style="text-align:center;padding:32px;color:#dc3545;">
                    <i class="fas fa-exclamation-circle" style="font-size:24px;margin-bottom:8px;display:block;"></i>
                    ${escapeHtml(message)}
                </td>
            </tr>`;
    }
}

function filterFareGuides() {
    const search  = (document.getElementById('searchInput')?.value  || '').toLowerCase().trim();
    const status  = (document.getElementById('statusFilter')?.value  || '');
    const vehicle = (document.getElementById('vehicleFilter')?.value || '');

    const filtered = _allFareGuides.filter(guide => {
        const matchSearch  = !search  ||
            (guide.title || '').toLowerCase().includes(search) ||
            (guide.region || '').toLowerCase().includes(search) ||
            (guide.created_by_name || '').toLowerCase().includes(search);
        const matchStatus  = !status  || guide.status === status;
        const matchVehicle = !vehicle || guide.vehicle_type === vehicle;
        return matchSearch && matchStatus && matchVehicle;
    });

    renderFareGuides(filtered);

    const countEl = document.getElementById('fareGuidesCount');
    if (countEl) {
        countEl.textContent = filtered.length === _allFareGuides.length
            ? `${_allFareGuides.length} guide(s) found`
            : `Showing ${filtered.length} of ${_allFareGuides.length} guide(s)`;
    }
}

function renderFareGuides(guides) {
    const tbody = document.getElementById('fareGuidesBody');
    if (!tbody) return;

    if (!guides || guides.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);">
                    <i class="fas fa-inbox" style="font-size:24px;margin-bottom:8px;display:block;"></i>
                    No active fare guides found.
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = guides.map(guide => {
        const vehicleLabel = (guide.vehicle_type || '').replace(/_/g, ' ');

        return `
            <tr>
                <td>${escapeHtml(guide.title)}</td>
                <td>${escapeHtml(vehicleLabel)}</td>
                <td>${escapeHtml(guide.region)}</td>
                <td>${formatDate(guide.effective_date)}</td>
                <td>${escapeHtml(guide.created_by_name)}</td>
                <td>
                    <button class="btn-gov btn-gov-secondary"
                        style="padding:4px 10px;font-size:12px;"
                        onclick="viewFareMatrix(${guide.id}, ${JSON.stringify(guide.title)})">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>`;
    }).join('');
}

// ── Fare Matrix ───────────────────────────────────────────────────────────────

async function viewFareMatrix(guideId, title) {
    const section = document.getElementById('fareMatrixSection');
    const tbody   = document.getElementById('fareMatrixBody');
    const titleEl = document.getElementById('fareMatrixTitle');

    // Show the section with a loading state
    if (section) section.style.display = 'block';
    if (titleEl) titleEl.innerHTML = `<i class="fas fa-table"></i> Fare Matrix — ${escapeHtml(title || '')}`;
    if (tbody) tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;padding:24px;color:var(--text-muted);">
        <i class="fas fa-spinner fa-spin"></i> Loading...
    </td></tr>`;

    if (section) section.scrollIntoView({ behavior: 'smooth' });

    try {
        const data = await apiFetch('get_fare_matrices', { guide_id: guideId });
        _currentMatrixData = data.fare_matrices || [];

        if (_currentMatrixData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;padding:32px;color:var(--text-muted);">
                No fare matrix rows found for this guide.
            </td></tr>`;
        } else {
            tbody.innerHTML = _currentMatrixData.map(m => `
                <tr>
                    <td>${parseFloat(m.distance_km).toFixed(1)} km</td>
                    <td>₱${parseFloat(m.regular_fare).toFixed(2)}</td>
                    <td>₱${parseFloat(m.discounted_fare).toFixed(2)}</td>
                </tr>`).join('');
        }
    } catch (err) {
        console.error('[LUPTO Fare] viewFareMatrix failed:', err);
        if (tbody) tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;padding:24px;color:#dc3545;">
            Failed to load matrix: ${escapeHtml(err.message)}
        </td></tr>`;
    }
}

// ── CSV Export ────────────────────────────────────────────────────────────────

function exportFareMatrix() {
    if (!_currentMatrixData || _currentMatrixData.length === 0) {
        alert('No fare matrix data to export. Click "View" on a fare guide first.');
        return;
    }

    const titleEl = document.getElementById('fareMatrixTitle');
    const title   = (titleEl?.textContent || 'fare_matrix').replace(/[^a-z0-9]/gi, '_').toLowerCase();

    const headers = ['Distance (km)', 'Regular Fare (PHP)', 'Discounted Fare (PHP)'];
    const rows    = _currentMatrixData.map(m => [
        parseFloat(m.distance_km).toFixed(1),
        parseFloat(m.regular_fare).toFixed(2),
        parseFloat(m.discounted_fare).toFixed(2)
    ]);

    const csv  = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `${title}_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ── Utilities ─────────────────────────────────────────────────────────────────

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    try {
        return new Date(dateStr).toLocaleDateString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric'
        });
    } catch (_) {
        return dateStr;
    }
}
